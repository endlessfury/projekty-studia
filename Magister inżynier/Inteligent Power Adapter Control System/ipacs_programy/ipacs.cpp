#include <stdio.h>
#include <stdlib.h>
#include <iostream>
#include <mysql/mysql.h>
#include <sstream>
#include <string>
#include <dirent.h>
#include <unistd.h>
#include "./RF_lib/RF24.h"
#include <stdio.h>
#include <fcntl.h>
#include <ctime>
#include <chrono>
#include <thread>
#include <iomanip>
#include <vector>
#include <array>
#include "Task.h"
using namespace std;
using namespace std::chrono;
using namespace std::this_thread;

RF24 radio(5,1);            // inicjalizacja i ustawienia pinów RPi

MYSQL mysql;                // tworzenie obiektu MYSQL do zestawienia polaczenia
MYSQL_ROW row;              // tworzenie obiektu ROW do przechowania kolumn wiersza
MYSQL_ROW row2;              // tworzenie obiektu ROW do przechowania kolumn wiersza
MYSQL_RES *result;          // tworzenie wskaźnika do obiektu typu result do przetrzymania calego wyniku zapytania
MYSQL_RES *result2;          // tworzenie wskaźnika do obiektu typu result do przetrzymania calego wyniku zapytania

vector <Task> taskQueue; // kontener typu vector do kolejkowania adapterow do odpytania
vector <int> removeList;    // kontener typu vector do przechowania listy adapterow do usuniecia z kolejki taskQueue

bool logs = false;         // zmienna do wyswietlania logow, uzywana w parametrze maina, delay 2 sekundowy
bool logs_rx = false;         // zmienna do wyswietlania logow, uzywana w parametrze maina, delay 2 sekundowy
bool button = false;       // zmienna do wyswietlania logow, uzywana w parametrze maina, do wlaczenia potwierdzenia petli enterem
void    checkParameters(int argc, char* argv[]);
void    getSocketStates(int);                   // funkcja odczytujaca stany gniazd z bazy danych; id listwy jako argument
void    addDataToDB(char*, string, unsigned int, unsigned int);      // funkcja dodajaca dane recievedMessage z listwy do bazy danych; argumenty kolejno to dane, typ czujnika, id listwy
bool    checkSensorState(string);                     // funkcja sprawdzajaca czy czujnik jest wlaczony; BRAK IMPLEMENTACJI
string  checkSensor(unsigned int, string);              // funkcja zwracajaca id sensora danego typu w odpowiedniej listwie; argumenty kolejno to id listwy, typ czujnika; MOŻNA BY DODAC WERYFIKACJE CZY CZUJNIK JEST WLACZONY?!
char*   prepareMessageToSend (int);             // funkcja wykonujaca przygotowanie wiadomosci do wyslania; argumentem id listwy
void    taskQueueCheck();                    // funkcja sprawdzajaca czy kolejka jest pusta i jesli tak to wypelniajaca ja adapterami, w przeciwnym razie sprawdza czy adapter ma odpowiednia liczbe listw w kolejce, jesli nie to dodaje odpowiednia, sprawdza wartosc licznika zadania w kolejce i jesli jest mniejsze od maxRepeat to wysyla wiadomosc do listwy, usuwa wykonane zadania z kolejki
void    taskQueueSend(int);                  // funkcja wykonujaca zadanie wyslania wiadomosci; argumentem id zadania
bool    waitForAnswer(int,int);                 // funkcja czekajaca na odpowiedz (stan czujnikow) po wyslaniu zapytania (wiadomosci ze stanami gniazd) odpowiedni czas, odczytujaca dane z wiadomosci recievedMessagej, ustawiajaca stany polczanie, inkrementujaca licznik; argumenty kolejno to id listwy i id zadania
bool    checkExist(unsigned int);               // funkcja sprawdzajaca czy listwa widnieje w kolejce taskQueue, argumentem id listwy
void    removeFromQueue();                      // funkcja usuwajaca listwy z kolejki taskQueue na podstawie listy removeList
void    resetTxDelays();                        // funkcja resetujaca opoznienia wzgledem siebie przy transmisji
bool    waitForAnswers();                       // funkcja czekajaca na wiadomosci z opowiednimi opoznieniami
void    taskExecute();                          // funkcja zlecajaca wyslanei do adapterow danych
int     findTask(unsigned int);                 // funkcja wyszukujaca task w kolejce dla danego adresu listwy
bool    checkRecieved(unsigned int);            // funkcja sprawdzajaca czy pakiet odebrany od danego adaptera został już odebrany (prewencja echa)
void    checkRxFlags();                         // funkcja sprawdzajaca flagi po skończonym czasie pętli odpowednich listw są true, jeśli false to odpowiednio wykunuje czynności
void    checkJobList();                         // funkcja przeszukująca bazę job_list, żeby sprawdzić czy zlecono jakieś zmiany w ustawieniach systemu
bool    checkSockets(unsigned int);             // funkcja sprawdzająca czy są zdefniniowane 4 gniazda w systemie
void    setControl(char, unsigned int, unsigned int);                       // funkcja ustawiająca tryb kontroli - strona (0) lub manualny (1)
unsigned int     checkWaitNumber(unsigned int);                   // funkcja zwracajaca waitNumber z bazy
unsigned int     checkWeakSignalIndicatorLevel(unsigned int);                   // funkcja zwracajaca weak_signal z bazy
void showTaskQueue();
// stałe
const int       weakSignalRepeat = 5;     // 5 razy
const int       startDelay = 10;          // ms
const int       delayDiff = 5;            // roznica czasu pomiedzy listwami
const int       noAdapterDelay = 3000;    // 3 sek odczekania przy przeszukiwaniu adapterów
const int       answerDelay = 250;        // czas odczekania zanim będziemy sprawdzać pakiety
const int       maxWaitTimeFactor = 380;   // ile sekund dodajemy do maksymalnego czasu oczekiwania na pakiet
char            socketStates[4];     
char            messageToSend[32]{};  
unsigned int    statTime = 0;               // zmiena do restartu statystyki

string logDelay = " ";

// struktura tasku ponizej
// [0] "adres listwy", [1] "licznik nieudanych prób", [2] "opóźnienie transmisji", [3] "czas wysłania", [4] "flaga odebrania pakietu (prewencja echa)", [5] "pierwotny taskID"

int main(int argc, char* argv[])
{
    if (argc > 0)
        checkParameters(argc, argv);      // sprawdzenie parametrów uruchomienia systemu

    long count = 0;     // petla programu
    // inicjalizacja parametrow modulu radiowego
    radio.begin();
    radio.powerUp();
    const uint64_t address_odbiorczy = 0xF0F0F0F0D2;
    const uint64_t address_nadawczy = 0xF0F0F0F0E1;
    radio.openReadingPipe(1,address_odbiorczy);
    radio.openWritingPipe(address_nadawczy);
    radio.setPALevel(RF24_PA_LOW);
    radio.setDataRate(RF24_250KBPS);
    radio.setCRCLength(RF24_CRC_16);
    radio.setRetries(15,15);
    radio.setChannel(25);
    //radio.printDetails();

    statTime = millis();

    mysql_init(&mysql); // inicjalizacja
    if (mysql_real_connect(&mysql, "127.0.0.1", "pi", "rpiipacs", "ipacs_database", 0, NULL, 0)) // ustanowienie polaczenia z baza danych
    {
        while(1)
        {
            // restart statystyk
            if (statTime + 300000 + 50 <= millis() && statTime + 300000 - 50 >= millis())
            {
                ostringstream strstr;
                strstr << "UPDATE `stats` A INNER JOIN `adapters` RA ON A.`adapter_id` = RA.`adapter_id` SET    A.`packets_recieved` = 0, A.`packets_sent` = 1, A.`last_recieved_time` = '', A.`last_recieved_date` = ''";
                string str = strstr.str();
                mysql_query(&mysql, str.c_str());
                statTime = millis();
            }

            if (logs == true) if (logs == true) cout << endl << "\033[1;97m\t\tChecking jobs: \033[0m" << endl << endl;
            checkJobList();
            if (logs == true) if (logs == true) cout << endl << "\033[1;97m\t\tChecking taskQueue: \033[0m" << endl << endl;
            taskQueueCheck();
            if (logs == true) if (logs == true) cout << endl << "\033[1;97m\t\tExecuting tasks: \033[0m" << endl;
            taskExecute();
            waitForAnswers();
            checkRxFlags();
            if (logs == true) if (logs == true) cout << endl << "\033[1;97m\t\tRemoving tasks: \033[0m" << endl;
            removeFromQueue();
            count++;

            if (logs == true) if (logs == true) cout << endl << "\033[1;97m\t\tLoop: " << count << ", time: " << millis() << "\033[0m" << endl ;
            if (logs == true) if (logs == true) cout << "\t\t________________________" << endl << endl;

            if (logs == true && button == true) 
                cin.ignore();
            else if (logs == true)
            {    
                if(logDelay == "slow") 
                    delay(3000);
            }
        }
    }
    else
        printf("Błąd połączenia z bazą &mysql: %d, %s\n", mysql_errno(&mysql), mysql_error(&mysql)); 
     
}

// funkcje 

void taskQueueCheck()
{
    if (!taskQueue.empty()) // jeśli kolejka nie jest pusta
    {   
        for (unsigned int i = 0;i < taskQueue.size();i++)
        {
        //cout << taskQueue[i].readAdapterID() << endl;
           // sprawdzenie czy w międzyczasie niewyłączono obsługi listwy
           ostringstream strstr2;
           strstr2 << "SELECT adapter_beacon FROM `adapters` WHERE `adapter_id` = '" << taskQueue[i].readAdapterID() << "'";
           string str = strstr2.str();
           mysql_query(&mysql, str.c_str());
           result = mysql_store_result(&mysql);
           row = mysql_fetch_row(result);
           string adapter_beacon = row[0];
           if (adapter_beacon == "0")
           {
               if (logs == true) cout << "\033[1;31mTaskAddress: " << &taskQueue[i] << " \t AdapterID: " << taskQueue[i].readAdapterID() << " has changed its beacon state to 0\033[0m" << endl;
               removeList.push_back(i);
               removeFromQueue();
               if (logs == true) cout << endl;
           }
           
            mysql_free_result(result);
        }
        // sprawdzenie czy są jakieś listwy do dodania do kolejki
        mysql_query(&mysql, "SELECT adapter_id, adapter_connection FROM `adapters` WHERE `adapter_beacon` = '1' AND `adapter_removed` = '0'");
        result = mysql_store_result(&mysql);
        unsigned int howMany = mysql_num_rows(result);      // ile mamy takich adapterów
        
        if (taskQueue.size() < howMany)      // jesli trzeba dodac adaptery po usunieciu (otrzymaniu odpowiedzi w poprzedniej petli) do kolejki
        {
            while ((row = mysql_fetch_row(result)) != NULL)
            {
                string adapterID_str;               // konwersja z char* na int
                unsigned int adapterID;             // konwersja z char* na int
                adapterID_str = row[0];             // konwersja z char* na int
                adapterID = stoi(adapterID_str);    // konwersja z char* na int
                if (adapterID <= 99)
                {
                    if (!checkExist(adapterID))         // jeśli adapter nie istnieje w kolejce
                    {
                        if (checkSockets(adapterID))
                        {
                            /*unsigned int* queueTable = new unsigned int[7];       // tablica 2 intów adapterID oraz licznika

                            queueTable[0] = adapterID;
                            queueTable[1] = 0;      // wyzerowanie licznika
                            queueTable[4] = false;      
                            queueTable[5] = 0;
                            queueTable[6] = checkWaitNumber(adapterID);*/

                            Task newTask = Task();
                            newTask.setAdapterID(adapterID);
                            newTask.setWaitNumberCounter(0);
                            newTask.setMaxWaitNumber(checkWaitNumber(adapterID));
                            newTask.setWeakSignalIndicator(checkWeakSignalIndicatorLevel(adapterID));
                            newTask.setRxFlag(false);
                            taskQueue.push_back(newTask); // dodanie do kolejki

                            if (logs == true) cout << "TaskAddress: " << "----------------------" << " \t Added: aID: " << newTask.readAdapterID() << ", counter: " << newTask.readWaitNumberCounter() << ", delay: " << newTask.readTxDelay() << ", waitNumber: " << newTask.readMaxWaitNumber() << endl;

                            //if (logs == true) cout << "TaskID: ? \t Added adapterID: " << adapterID << " to the queue" << endl;
                            //delete newTask;
                        }
                        else
                            if (logs == true) cout << "\033[1;31m---------------------- \t Found adapterID: " << adapterID << ", but there are not enough sockets defined! Can't send a message.\033[0m" << endl;   
                    }
                }
                else
                    if (logs == true) cout << "\033[1;31m---------------------- \t Found adapterID: " << adapterID <<", but maximum limit of adapters has been exeeded (99)!\033[0m" << endl;       
            }
            resetTxDelays();
        }
        
        mysql_free_result(result);
        
    }
    else // jeśli kolejka jest pusta znajdz wszystkie dostępne adaptery w bazie danych
    {
        int txDelay = startDelay;  // opoznienie wzgledem innych listw
        bool empty = false;
        mysql_query(&mysql, "SELECT adapter_id, adapter_connection FROM `adapters` WHERE `adapter_beacon` = '1' AND `adapter_removed` = '0'");
        result = mysql_store_result(&mysql);
        //if (logs == true) cout << "---------------------- \t taskQueue is empty, looking for adapter to add" << endl;
        {
            if (logs == true && mysql_num_rows(result) != 0) cout << "---------------------- \t Found those adapters:" << endl;
            {
                while ((row = mysql_fetch_row(result)) != NULL)
                {
                    empty = true;
                    string adapterID_str;               // konwersja z char* na int
                    unsigned int adapterID;                   // konwersja z char* na int
                    adapterID_str = row[0];             // konwersja z char* na int
                    adapterID = stoi(adapterID_str);   // konwersja z char* na int
                    if (adapterID <= 99)
                    {
                        if (checkSockets(adapterID))
                        {
                            /*unsigned int* queueTable = new unsigned int[7];       // tablica 2 intów adapterID oraz licznika

                            queueTable[0] = adapterID;     // adres listwy
                            queueTable[1] = 0;       // licznik
                            queueTable[2] = txDelay;
                            queueTable[4] = false;      // wyzerowanie licznika
                            queueTable[5] = 0;
                            queueTable[6] = checkWaitNumber(adapterID);*/

                            Task newTask = Task();
                            newTask.setAdapterID(adapterID);
                            newTask.setWaitNumberCounter(0);
                            newTask.setMaxWaitNumber(checkWaitNumber(adapterID));
                            newTask.setRxFlag(false);
                            newTask.setTxDelay(txDelay);
                            newTask.setWeakSignalIndicator(checkWeakSignalIndicatorLevel(adapterID));
                            taskQueue.push_back(newTask); // dodanie do kolejki
                            
                            if (logs == true) cout << "TaskAddress: " << "----------------------" << " \t aID: " << newTask.readAdapterID() << ", counter: " << newTask.readWaitNumberCounter() << ", delay: " << newTask.readTxDelay() << ", waitNumber: " << newTask.readMaxWaitNumber() << ", weakSignalLevel: " << newTask.readWeakSignalIndicator() << endl;
                            //taskQueue.push_back(queueTable);     // dodanie do kolejki
                            txDelay = txDelay + delayDiff;      // delay + delayDiff ms
                            
                            //delete newTask;
                        }
                        else if (logs == true) cout << "\033[1;31m---------------------- \t Found adapterID: " << adapterID << ", but there are not enough sockets defined! Can't send a message.\033[0m" << endl;   
                    }
                    else if (logs == true) cout << "\033[1;31m---------------------- \t Maximum limit of adapters has been exeeded (99)!\033[0m" << endl;       
                }
            }
            if (logs == true && mysql_num_rows(result) == 0) cout << "No adapters have been found" << endl;
        }
        
        //if (logs == true) cout << "---------------------- \t Proceeding to program" << endl;
        mysql_free_result(result);
        if (!empty) delay(noAdapterDelay);    // jesli nie ma listw, do ktorych mozna wyslac wiadomosc to odczekaj 1 sekunde i sprawdz ponownie
    }
}

void taskExecute()
{
    for (unsigned int taskID = 0;taskID < taskQueue.size();taskID++)
    {
        if (logs == true) cout << endl << "TaskAddress: " << &taskQueue[taskID] << " \t aID: " << taskQueue[taskID].readAdapterID() << ", counter: " << taskQueue[taskID].readWaitNumberCounter() << ", delay: " << taskQueue[taskID].readTxDelay() << ", waitNumber: " << taskQueue[taskID].readMaxWaitNumber() << endl;
        unsigned int counter = taskQueue[taskID].readWaitNumberCounter();
        //if (counter <= taskQueue[taskID].readMaxWaitNumber())
        {
            prepareMessageToSend(taskID);  // get prepared data for the adapter from queue, we have to send object, not pointer to object in radio.write(*)
            
            radio.write(messageToSend, 32);     // send prepared message
            taskQueue[taskID].setSendingTime(millis());
            if (logs == true) cout << "\033[1;92mTaskAddress: " << &taskQueue[taskID] << " \t Message has been sent at time: \033[0m" << millis() << " -- > " << "\033[1;92m" << messageToSend << "\033[0m" << endl;

        }
    }
    if (taskQueue.size() == 0)
    {
        if (logs == true) cout << endl << "No tasks have been found" << endl;
    }
}

char* prepareMessageToSend(int taskID)
{
    getSocketStates(taskQueue[taskID].readAdapterID());

    char delay[2];      // delay 2 cyfrowy
    sprintf(delay,"%d",taskQueue[taskID].readTxDelay());     // funkcja konwertujaca delay intowy na delay charowy
    messageToSend[0] = '0';
    messageToSend[1] = '_';
    unsigned int address = taskQueue[taskID].readAdapterID();
    if (address < 10)
    {
        messageToSend[2] = '0';
        char address_2[1];
        sprintf(address_2,"%d",address);
        messageToSend[3] = address_2[0];
    }
    else
    {
        char address_2[2];
        sprintf(address_2,"%d",address);
        messageToSend[2] = address_2[0];
        messageToSend[3] = address_2[1];
    }
    messageToSend[4] = '_';

    for (unsigned int i = 5;i < 9;i++)
    {
        messageToSend[i] = socketStates[i-5];
    }
    messageToSend[9] = '_';
    messageToSend[10] = delay[0];
    messageToSend[11] = delay[1];

    return messageToSend;
                
    // messageToSend "0_AA_SSSS_DD"
}

bool checkSockets(unsigned int adapterID)
{
    ostringstream strstr;
    strstr << "SELECT `socket_id` FROM `sockets` WHERE `adapter_id` = '" << adapterID << "'";
    string querry = strstr.str();
    mysql_query(&mysql, querry.c_str());
    result2 = mysql_store_result(&mysql);
    int socketNumber = mysql_num_rows(result2);
    mysql_free_result(result2);
    if (socketNumber < 4) 
        return false;
    else 
        return true;
}

bool waitForAnswers()
{
    if (taskQueue.size() > 0)
    {
        radio.flush_rx();
        delay(answerDelay);

        unsigned int maxWaitTime = taskQueue[taskQueue.size() - 1].readSendingTime() + maxWaitTimeFactor + taskQueue[taskQueue.size() - 1].readTxDelay();
        if (logs == true) cout << "\n\033[1;35m---------------------- \t Maximal wait time is set to: \033[0m" << maxWaitTime << "\033[0m" << endl << endl;

        radio.startListening();

        while(millis() <= maxWaitTime)
        {
            if (radio.available())
            {
                char recievedMessage[32]{}, data[6]{};
                radio.read( &recievedMessage, 32 );
                if (logs == true && logs_rx == true) cout << "---------------------- \t recieved: " << recievedMessage << " time: " << millis() <<  endl;

                if (recievedMessage[0] == '1')
                {
                    if (recievedMessage[1] == '_' && recievedMessage[4] == '_' && recievedMessage[10] == '_' && recievedMessage[16] == '_') 
                    {
                        char adapterID_char[2];
                        adapterID_char[0] = recievedMessage[2];
                        adapterID_char[1] = recievedMessage[3];
                        unsigned int adapterID_int[2], adapterID = 0;
                        adapterID_int[0] = adapterID_char[0] - '0';
                        adapterID_int[1] = adapterID_char[1] - '0';
                        adapterID = adapterID_int[0] * 10 + adapterID_int[1];

                        int taskID = findTask(adapterID);

                        if (checkRecieved(adapterID))   // jeśli pakiet został już odebrany pomiń pętlę
                            continue;
                        if (!checkExist(adapterID))     // jeśli adapter nie widnieje w kolejce pomiń pętlę
                            continue;

                        {
                            ostringstream strstr;
                            strstr << "SELECT `adapter_connection` FROM `adapters` WHERE `adapter_id` = '" << adapterID << "'";
                            string querry = strstr.str();
                            mysql_query(&mysql, querry.c_str());
                            result = mysql_store_result(&mysql);
                            row = mysql_fetch_row(result);
                            string adapter_connection = row[0];
                            mysql_free_result(result);

                            if (adapter_connection == "0")
                            {
                                // pobierz czas i date
                                chrono::time_point<chrono::system_clock> time_now = chrono::system_clock::now();
                                time_t time_now_t = chrono::system_clock::to_time_t(time_now);
                                tm now_tm = *localtime(&time_now_t);
                                char date[512];
                                strftime(date, 512, "%d-%m-%Y", &now_tm);
                                char time[512];
                                strftime(time, 512, "%H:%M:%S", &now_tm);

                                ostringstream strstr2;
                                strstr2 << "INSERT INTO `job_list`(`job_id`, `job_type`, `job_setting`, `job_setting2`,`job_time`,`job_date`) VALUES ('','adapterConChange','ON SERVICE','aID: " << adapterID << "','" << time << "','" << date << "')";
                                string str2 = strstr2.str();
                                mysql_query(&mysql, str2.c_str());
                                cout << "adapterConChange" << endl;
                            }
                        }

                        // sprawdzenie i zaktualizowanie stanu polaczenia
                        if (taskQueue[taskID].readWaitNumberCounter() == 0)      // jeśli nie było problemów w transmisji
                        {
                            ostringstream strstr2;
                            strstr2 << "UPDATE `adapters` SET `adapter_connection` = '2' WHERE `adapter_id` = '"  << adapterID << "'";
                            string str2 = strstr2.str();
                            mysql_query(&mysql, str2.c_str());
                        }
                        else if (taskQueue[taskID].readWaitNumberCounter() >= checkWeakSignalIndicatorLevel(adapterID))
                        {
                            ostringstream strstr;
                            strstr << "UPDATE `adapters` SET `adapter_connection` = '1' WHERE `adapter_id` = '"  << adapterID << "'";
                            string str = strstr.str();
                            mysql_query(&mysql, str.c_str());
                        }

                        if (logs == true) cout << "\033[1;36mTaskAddress: " << &taskQueue[taskID] << " \t Message has been recieved at time: \033[0m" << millis() << " -- > \033[1;36m" << recievedMessage << "\033[0m" << endl;

                        for (int i = 0;i < 5;i++)
                        {
                            data[i] = recievedMessage[5+i];
                        }
                        addDataToDB(&data[0], "temperature", adapterID, taskID);

                        for (int i = 0;i < 5;i++)
                        {
                            data[i] = recievedMessage[11+i];
                        }
                        addDataToDB(&data[0], "light", adapterID, taskID);
                        
                        taskQueue[taskID].setRxFlag(true);
                        //taskQueue[taskID][5] = taskID;   // do celów prawidłowego logowania usunięcia z listy removeList

                        setControl(recievedMessage[17], taskQueue[taskID].readAdapterID(), taskID);       // ustawienie typu kontroli
                        
                        //if (logs == true) cout << "\033[1;33mTaskAddress: " << &taskQueue[taskID] << " \t Recieve flag has been set\033[0m" << endl;
                    }
                }

                radio.flush_rx();
                //this_thread::sleep_for(nanoseconds(500000));
                delay(1);
            }
        }
        
        radio.stopListening();
        
        return true;
    }
    return false;
}

void checkRxFlags()
{
    // pobierz czas i date
    chrono::time_point<chrono::system_clock> time_now = chrono::system_clock::now();
    time_t time_now_t = chrono::system_clock::to_time_t(time_now);
    tm now_tm = *localtime(&time_now_t);
    char date[512];
    strftime(date, 512, "%d-%m-%Y", &now_tm);
    char time[512];
    strftime(time, 512, "%H:%M:%S", &now_tm);

    for (unsigned int i = 0; i < taskQueue.size();i++)
    {
        if (taskQueue[i].readRxFlag() == true)
        {
            removeList.push_back(i);

            ostringstream strstr;
            strstr << "UPDATE `stats` SET `packets_recieved`=`packets_recieved` + 1,`packets_sent`= `packets_sent` + 1, `last_recieved_time` = '" << time << "', `last_recieved_date` = '" << date << "' WHERE `adapter_id` = '" << taskQueue[i].readAdapterID() << "'";
            string str = strstr.str();
            mysql_query(&mysql, str.c_str());

            ostringstream strstr2;
            strstr2 << "UPDATE `adapters` SET `adapter_state`=1 WHERE `adapter_id` = '" << taskQueue[i].readAdapterID() << "'";
            string str2 = strstr2.str();
            mysql_query(&mysql, str2.c_str());
            
        }
        else
        {
            ostringstream strstr;
            strstr << "UPDATE `stats` SET `packets_sent`= `packets_sent` + 1, `last_recieved_time` = '" << time << "', `last_recieved_date` = '" << date << "' WHERE `adapter_id` = '" << taskQueue[i].readAdapterID() << "'";
            string str = strstr.str();
            mysql_query(&mysql, str.c_str());
            
            if (logs == true) cout << "\033[1;31mTaskAddress: " << &taskQueue[i] << " \t Message hasn`t been recieved at time: \033[0m" << millis() << "\033[1;31m skipping and setting counter to: " << taskQueue[i].readWaitNumberCounter() + 1 << "\033[0m" << endl;
            
            if (!(taskQueue[i].readWaitNumberCounter() > taskQueue[i].readMaxWaitNumber()))
                taskQueue[i].setWaitNumberCounter(taskQueue[i].readWaitNumberCounter() + 1);
            
            if (taskQueue[i].readWaitNumberCounter() == checkWeakSignalIndicatorLevel(taskQueue[i].readAdapterID()))      // jeśli nie było problemów w transmisji
            {
                ostringstream strstr;
                strstr << "UPDATE `adapters` SET `adapter_connection` = '1' WHERE `adapter_id` = '"  << taskQueue[i].readAdapterID() << "'";
                string str = strstr.str();
                mysql_query(&mysql, str.c_str());
                if (logs == true) cout << "\033[94mTaskAddress: " << &taskQueue[i] << " \t Weak signal detected\033[0m" << endl;
            }
            else if (taskQueue[i].readWaitNumberCounter() == taskQueue[i].readMaxWaitNumber())
            {
                ostringstream strstr;
                strstr << "UPDATE `adapters` SET `adapter_connection` = '0', `adapter_state` = '0' WHERE `adapter_id` = '"  << taskQueue[i].readAdapterID() << "'";
                string str = strstr.str();
                mysql_query(&mysql, str.c_str());
                ostringstream strstr2;
                strstr2 << "INSERT INTO `job_list`(`job_id`, `job_type`, `job_setting`, `job_setting2`,`job_time`,`job_date`) VALUES ('','adapterConChange','OFF SERVICE','aID: " << taskQueue[i].readAdapterID() << "','" << time << "','" << date << "')";
                string str2 = strstr.str();
                mysql_query(&mysql, str2.c_str());
                //removeList.push_back(i);
                //taskQueue.erase(taskQueue.front()+i);           // remove task from queue 
                if (logs == true) cout << "\033[1;33mTaskAddress: " << &taskQueue[i] << " \t Adapter state changed to 0 (adapter not maintained)\033[0m" << endl;
                //if (logs == true) cout << "\033[1;33mTaskAddress: " << &taskQueue[i] << " \t Task has been added to remove list due to no response\033[0m" << endl;
            } 

        }
                
    }
}

void setControl(char controlType, unsigned int adapterID, unsigned int taskID)
{
    ostringstream strstr;
    strstr << "UPDATE `adapters` SET `adapter_website_control` = '" << controlType << "' WHERE `adapter_id` = '"  << adapterID << "'";
    string str = strstr.str();
    mysql_query(&mysql, str.c_str());
    if (controlType == '0')
        if (logs == true) cout << "\033[1;97mTaskAddress " << &taskQueue[taskID] << " \t Control has been set to manual\033[0m" << endl;
}

bool checkExist(unsigned int adapterID)
{
    for (unsigned int i = 0;i < taskQueue.size();i++)
    {
        if (taskQueue[i].readAdapterID() == adapterID)
            return true;
    }
    return false;
}

void removeFromQueue()
{
    if (!removeList.empty())
    {
        if (logs == true) cout << endl;

        for (unsigned int j = 0; j < removeList.size();j++)
        {
            int index = removeList[j];
            if (logs == true) cout << "\033[1;93mTaskAddress: " << &taskQueue[index] << " \t Task has been sucessfully removed from the queue\033[0m" << endl;

            if (taskQueue[index].readWaitNumberCounter() < checkWeakSignalIndicatorLevel(taskQueue[index].readAdapterID()))
            {
                ostringstream strstr;
                strstr << "UPDATE `adapters` SET `adapter_connection` = '2' WHERE `adapter_id` = '"  << taskQueue[index].readAdapterID() << "'";
                string str = strstr.str();
                mysql_query(&mysql, str.c_str());
            }
            else if (taskQueue[index].readWaitNumberCounter() >= checkWeakSignalIndicatorLevel(taskQueue[index].readAdapterID()) && taskQueue[index].readWaitNumberCounter() < taskQueue[index].readMaxWaitNumber())
            {
                ostringstream strstr;
                strstr << "UPDATE `adapters` SET `adapter_connection` = '1' WHERE `adapter_id` = '"  << taskQueue[index].readAdapterID() << "'";
                string str = strstr.str();
                mysql_query(&mysql, str.c_str());
            }
            
            taskQueue.erase(taskQueue.begin()+index);
            
        }
        removeList.clear();
    }
    else
    {
        if (logs == true) cout << endl << "No tasks have been found" << endl;
    }
    
}

void addDataToDB(char* data, string type, unsigned int adapter_id, unsigned int taskID)
{
    // pobierz czas i date
    chrono::time_point<chrono::system_clock> time_now = chrono::system_clock::now();
    time_t time_now_t = chrono::system_clock::to_time_t(time_now);
    tm now_tm = *localtime(&time_now_t);
    char date[512];
    strftime(date, 512, "%d-%m-%Y", &now_tm);
    char time[512];
    strftime(time, 512, "%H:%M:%S", &now_tm);

    string sensorID = checkSensor(adapter_id, type);
    if (checkSensorState(sensorID))
    {
        // wysli temperature do bazy danych czujnikow dla 1 czujnika
        ostringstream strstr;
        strstr << "UPDATE `sensors` SET `sensor_data`='" << data << "',`sensor_data_date`='" << date << "',`sensor_data_time`='" << time << "' WHERE sensor_id= " << sensorID;
        string str = strstr.str();
        mysql_query(&mysql, str.c_str());
    }
    else
    {
        if (logs == true) cout << "\033[1;31mTaskAddress: " << &taskQueue[taskID] << " \t Can't insert data, because " << type << " sensor is disabled! \033[0m" << endl;
    }
}

bool checkSensorState(string sensor_id)
{
    ostringstream strstr;
    strstr << "SELECT `sensor_state` FROM `sensors` WHERE `sensor_id` = " << sensor_id;
    string querry = strstr.str();
    mysql_query(&mysql, querry.c_str());
    result = mysql_store_result(&mysql);
    row = mysql_fetch_row(result);
    mysql_free_result(result);

    string state_row = row[0];
    bool state = false;
    if (state_row == "1")
        state = true;

    if (state)
        return true;
    else
        return false;
}

string checkSensor(unsigned int adapter_id, string type)
{
    ostringstream strstr;
    strstr << "SELECT `sensor_id`, sensor_type FROM `sensors` WHERE `adapter_id` = " << adapter_id << " AND `sensor_type` = '" << type << "'";
    string querry = strstr.str();
    mysql_query(&mysql, querry.c_str());
    result = mysql_store_result(&mysql);
    row = mysql_fetch_row(result);
    mysql_free_result(result);
    string sensorID = string(row[0],2);
    return sensorID;
}

void getSocketStates(int adapterID)
{
    ostringstream strstr;
    strstr << "SELECT `socket_state` FROM `sockets` WHERE `adapter_id` = '" << adapterID << "'";
    string querry = strstr.str();
    mysql_query(&mysql, querry.c_str());
    result = mysql_store_result(&mysql);

    int socketCounter = 0;                  // counter defining which socket

    while ((row = mysql_fetch_row(result)) != NULL)
    {
        socketStates[socketCounter] = string(row[0],1)[0];
        socketCounter++;
    }
    mysql_free_result(result);  
}

void resetTxDelays()
{
    int txDelay = startDelay;
    for (unsigned int i = 0; i < taskQueue.size();i++)
    {
        taskQueue[i].setTxDelay(txDelay);
        txDelay = txDelay + delayDiff;
    }
    if (logs == true) cout << "\033[1;37m---------------------- \t New delays assigned\033[0m" << endl;
}

int findTask(unsigned int adapterID)
{
    for (unsigned int i = 0; i < taskQueue.size();i++)
    {
        if (taskQueue[i].readAdapterID() == adapterID)
            return i;
    }
    return 0;
}

bool checkRecieved(unsigned int adapterID)
{
    for (unsigned int i = 0; i < taskQueue.size();i++)
    {
        if (taskQueue[i].readAdapterID() == adapterID)
            if (taskQueue[i].readRxFlag() == true)
                return true;
    }
    return false;
}



void checkJobList()
{
    mysql_query(&mysql, "SELECT job_type, job_setting, job_setting2, job_setting3, job_id FROM `job_list` WHERE `job_active` = '1'");
    result = mysql_store_result(&mysql);
    if (mysql_num_rows(result) == 0) 
    {
        if (logs == true) cout << "No jobs have been found" << endl;
    }

    while ((row = mysql_fetch_row(result)) != NULL)
    {
        string jobType = row[0];
        int job = 0;
        if (jobType == "powerLevelChange")
            job = 1;
        else if (jobType == "waitNumberChange")
            job = 2;
        else if (jobType == "motherPowerLevel")
            job = 3;
        else if (jobType == "adapterIdChange")
            job = 4;
        else if (jobType == "timeChange")
            job = 5;
        else if (jobType == "dateChange")
            job = 6;
        else if (jobType == "motherRestart")
            job = 7;

        switch(job)
        {
            case 1:
            {
                char* messageToSend = new char[32]{};
                //char address_char[2];       // adres 1 cyfrowy
                //sprintf(address_char,"%s",row[1]);     // funkcja konwertujaca adres intowy na adres charowy
                messageToSend[0] = '4';
                messageToSend[1] = '_';
                int address = atoi(row[1]);
                if (address < 10)
                {
                    messageToSend[2] = '0';
                    char address_2[1];
                    sprintf(address_2,"%d",address);
                    messageToSend[3] = address_2[0];
                }
                else
                {
                    char address_2[2];
                    sprintf(address_2,"%d",address);
                    messageToSend[2] = address_2[0];
                    messageToSend[3] = address_2[1];
                }
                messageToSend[4] = '_';
                string powerLevel = row[2], adapterID = row[1];

                if (powerLevel == "MIN")
                    messageToSend[5] ='0';
                else if (powerLevel == "LOW")
                    messageToSend[5] ='1';
                else if (powerLevel == "HIGH")
                    messageToSend[5] ='2';
                else if (powerLevel == "MAX")
                    messageToSend[5] ='3';
                
                // messageToSend "4_01_1"

                radio.flush_rx();
                bool rx = false;
                int counter = 0;
                radio.write(messageToSend,32);
                radio.startListening();
                unsigned int maxTime = millis() + 10;
                while(counter < 20)
                {
                    cout << "x";
                    while (millis() <= maxTime)
                    {
                        if(radio.available())   
                        {
                            char recievedMessage[32]{};
                            radio.read( &recievedMessage, 32 );
                            if (recievedMessage[0] == '9' && recievedMessage[1] == '_' && recievedMessage[2] == '4')
                            {
                                if (logs == true) cout << "JobID: " << row[4] << " \t Changing powerLevel of adapterID: " << row[1] << " to: " << powerLevel << endl;

                                ostringstream strstr;
                                strstr << "UPDATE `adapters` SET `adapter_powerLevel` = '" << powerLevel << "' WHERE `adapters`.`adapter_id` = '"  << adapterID << "'";
                                string str = strstr.str();
                                mysql_query(&mysql, str.c_str());
                                ostringstream strstr2;
                                strstr2 << "UPDATE `job_list` SET `job_comment` = 'job finished', `job_active` = '0' WHERE `job_list`.`job_id` = '"  << row[4] << "'";
                                string str2 = strstr2.str();
                                mysql_query(&mysql, str2.c_str());
                                rx = true;
                                break;
                            }
                        }
                        delay(1);
                    }
                    if (rx)     
                        break;
                    else     
                        counter++;
                }
                radio.stopListening();

                if (!rx)
                {
                    ostringstream strstr2;
                    strstr2 << "UPDATE `job_list` SET `job_comment` = 'no ACK', `job_active` = '0' WHERE `job_list`.`job_id` = '"  << row[4] << "'";
                    string str2 = strstr2.str();
                    mysql_query(&mysql, str2.c_str());
                    if (logs == true) cout << "JobID: " << row[4] << " \t PowerLevelChange ACK hasn't been recieved" << endl;   
                }
                
                break;
            }
            case 2:
            {
                string waitNumber = row[2], adapterID = row[1];
                
                if (logs == true) cout << "JobID: " << row[4] << " \t AdapterID: " << adapterID << " waitNumber changed to: " << waitNumber << endl;
                
                ostringstream strstr;
                strstr << "UPDATE `adapters` SET `adapter_waitNumber` = '" << waitNumber << "' WHERE `adapters`.`adapter_id` = '"  << adapterID << "'";
                string str = strstr.str();
                mysql_query(&mysql, str.c_str());
                ostringstream strstr2;
                strstr2 << "UPDATE `job_list` SET `job_active` = '0',`job_comment` = 'job finished' WHERE `job_list`.`job_id` = '"  << row[4] << "'";
                string str2 = strstr2.str();
                mysql_query(&mysql, str2.c_str());
                break;
            }
            case 3:
            {
                string power = row[1];
                radio.powerDown();
                if (power == "MIN")
                    radio.setPALevel(RF24_PA_MIN);
                else if (power == "LOW")
                    radio.setPALevel(RF24_PA_LOW);
                else if (power == "HIGH")
                    radio.setPALevel(RF24_PA_HIGH);
                else if (power == "MAX")
                    radio.setPALevel(RF24_PA_MAX);
                radio.powerUp();

                if (logs == true) cout << "JobID: " << row[4] << " \t Changing motherPowerLevel to: " << power << " and printing details" << endl << endl;

                ostringstream strstr2;
                strstr2 << "UPDATE `job_list` SET `job_active` = '0',`job_comment` = 'job finished' WHERE `job_list`.`job_id` = '"  << row[4] << "'";
                string str2 = strstr2.str();
                mysql_query(&mysql, str2.c_str());

                if (logs == true) radio.printDetails();
                
                break;
            }
            case 4:
            {
                
                
                break;
            }
            case 5:
            {
                string newTime = row[1];
                
                if (logs == true) cout << "JobID: " << row[4] << " \t Changing time to: " << newTime << endl;
                
                ostringstream strstr;
                strstr << newTime;
                string str = strstr.str();

                char* netTimeCommand = new char[100]{};
                netTimeCommand[0] = 'd';
                netTimeCommand[1] = 'a';
                netTimeCommand[2] = 't';
                netTimeCommand[3] = 'e';
                netTimeCommand[4] = ' ';
                netTimeCommand[5] = '+';
                netTimeCommand[6] = '%';
                netTimeCommand[7] = 'T';
                netTimeCommand[8] = ' ';
                netTimeCommand[9] = '-';
                netTimeCommand[10] = 's';
                netTimeCommand[11] = ' ';
                netTimeCommand[12] = '"';
                for (int i = 0;i < 8;i++)
                {
                    netTimeCommand[13+i] = str[i];
                }
                netTimeCommand[21] = '"';

                system(netTimeCommand);
                //cout << netTimeCommand << endl;

                ostringstream strstr2;
                strstr2 << "UPDATE `job_list` SET `job_active` = '0', `job_comment` = 'job finished' WHERE `job_list`.`job_id` = '"  << row[4] << "'";
                string str2 = strstr2.str();
                mysql_query(&mysql, str2.c_str());
                break;
            }
            case 6:
            {
                string newDate = row[1];
                
                if (logs == true) cout << "JobID: " << row[4] << " \t Changing date to: " << newDate << endl;
                
                ostringstream strstr;
                strstr << newDate;
                string str = strstr.str();

                char* restartCommand = new char[32]{};
                restartCommand[0] = 'd';
                restartCommand[1] = 'a';
                restartCommand[2] = 't';
                restartCommand[3] = 'e';
                restartCommand[4] = ' ';
                restartCommand[5] = '+';
                restartCommand[6] = '%';
                restartCommand[7] = 'Y';
                restartCommand[8] = '-';
                restartCommand[9] = '%';
                restartCommand[10] = 'm';
                restartCommand[11] = '-';
                restartCommand[12] = '%';
                restartCommand[13] = 'd';
                restartCommand[14] = ' ';
                restartCommand[15] = '-';
                restartCommand[16] = 's';
                restartCommand[17] = ' ';
                restartCommand[18] = '"';
                for (int i = 0;i < 10;i++)
                {
                    restartCommand[19+i] = str[i];
                }
                restartCommand[29] = '"';

                system(restartCommand);
                //cout << restartCommand << endl;

                ostringstream strstr2;
                strstr2 << "UPDATE `job_list` SET `job_active` = '0', `job_comment` = 'job finished' WHERE `job_list`.`job_id` = '"  << row[4] << "'";
                string str2 = strstr2.str();
                mysql_query(&mysql, str2.c_str());
                break;
            }
            case 7:
            {
                string newDate = row[1];
                
                if (logs == true) cout << "JobID: " << row[4] << " \t Restarting system in 5 sec!" << endl;
                
                ostringstream strstr;
                strstr << newDate;
                string str = strstr.str();

                char* restartCommand = new char[32]{};
                restartCommand[0] = 's';
                restartCommand[1] = 'u';
                restartCommand[2] = 'd';
                restartCommand[3] = 'o';
                restartCommand[4] = ' ';
                restartCommand[5] = 'r';
                restartCommand[6] = 'e';
                restartCommand[7] = 'b';
                restartCommand[8] = 'o';
                restartCommand[9] = 'o';
                restartCommand[10] = 't';
                restartCommand[11] = ' ';
                restartCommand[12] = 'n';
                restartCommand[13] = 'o';
                restartCommand[14] = 'w';

                ostringstream strstr2;
                strstr2 << "UPDATE `job_list` SET `job_active` = '0', `job_comment` = 'job finished' WHERE `job_list`.`job_id` = '"  << row[4] << "'";
                string str2 = strstr2.str();
                mysql_query(&mysql, str2.c_str());
                delay(5000);
                system(restartCommand);
                break;
            }
        }
        
    }
    
    if (mysql_num_rows(result) > 0)
        if (logs == true) cout << endl;
    mysql_free_result(result);
}

unsigned int checkWaitNumber(unsigned int adapterID)
{
    ostringstream strstr;
    strstr << "SELECT `adapter_waitNumber` FROM `adapters` WHERE `adapter_ID` = '" << adapterID << "'";
    string str = strstr.str();
    mysql_query(&mysql, str.c_str());
    result2 = mysql_store_result(&mysql);
    
    unsigned int waitNumber = 30;                     // konwersja z char* na int
    if ((row2 = mysql_fetch_row(result2)) != NULL)
    {
        string waitNumber_str;               // konwersja z char* na int
        waitNumber_str = row2[0];             // konwersja z char* na int
        waitNumber = stoi(waitNumber_str);   // konwersja z char* na int
    }
    mysql_free_result(result2);
    return waitNumber;
}

unsigned int checkWeakSignalIndicatorLevel(unsigned int adapterID)
{
    ostringstream strstr;
    strstr << "SELECT `adapter_weak_signal` FROM `adapters` WHERE `adapter_ID` = '" << adapterID << "'";
    string str = strstr.str();
    mysql_query(&mysql, str.c_str());
    result2 = mysql_store_result(&mysql);
    
    unsigned int weakSignal = 5;                     // konwersja z char* na int
    if ((row2 = mysql_fetch_row(result2)) != NULL)
    {
        string weakSignal_str;               // konwersja z char* na int
        weakSignal_str = row2[0];             // konwersja z char* na int
        weakSignal = stoi(weakSignal_str);   // konwersja z char* na int
    }
    mysql_free_result(result2);
    return weakSignal;
}

void checkParameters(int argc, char* argv[])
{
    if (argv[1] != NULL)
    {
        string logging = argv[1];
        if(logging.find("logs") != string::npos)
        {
            logs = true;     // wlaczanie logowania
        }
    }
    if (argv[2] != NULL)
    {
        string logging_button = argv[2];
        if(logging_button.find("button") != string::npos)
        {
            button = true;     // wlaczanie logowania
        }
        else if(logging_button.find("slow") != string::npos)
        {
            logDelay = "slow";
        }
    }
    if (argv[3] != NULL)
    {
        string logging_rx = argv[3];
        if(logging_rx.find("rx") != string::npos)
        {
            logs_rx = true;     // wlaczanie logowania recieved
        }
    }
}

void showTaskQueue()
{
    for (unsigned int i = 0;i < taskQueue.size();i++)
    {
        cout << taskQueue[i].readAdapterID() << endl;
    }
}