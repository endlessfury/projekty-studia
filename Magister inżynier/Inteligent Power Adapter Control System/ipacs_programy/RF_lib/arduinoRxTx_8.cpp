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
using namespace std;
using namespace std::chrono;
using namespace std::this_thread;

RF24 radio(5,1);            // inicjalizacja i ustawienia pinów RPi

MYSQL mysql;                // tworzenie obiektu MYSQL do zestawienia polaczenia
MYSQL_ROW row;              // tworzenie obiektu ROW do przechowania kolumn wiersza
MYSQL_RES *result;          // tworzenie wskaźnika do obiektu typu result do przetrzymania calego wyniku zapytania

vector <unsigned int*> adapterQueue; // kontener typu vector do kolejkowania adapterow do odpytania
vector <unsigned int> removeList;    // kontener typu vector do przechowania listy adapterow do usuniecia z kolejki adapterQueue

bool logs = false;         // zmienna do wyswietlania logow, uzywana w parametrze maina, delay 2 sekundowy
bool button = false;       // zmienna do wyswietlania logow, uzywana w parametrze maina, do wlaczenia potwierdzenia petli enterem
char*   recieveTemp(void);
void    prepareTextToSend();
char    socketStates[4];       
void    getSocketStates(int);                   // funkcja odczytujaca stany gniazd z bazy danych; id listwy jako argument
void    addToMySqlDB(char*, string, char);      // funkcja dodajaca dane odebrane z listwy do bazy danych; argumenty kolejno to dane, typ czujnika, id listwy
bool    checkSensorState();                     // funkcja sprawdzajaca czy czujnik jest wlaczony; BRAK IMPLEMENTACJI
string  checkSensor(char, string);              // funckja zwracajaca id sensora danego typu w odpowiedniej listwie; argumenty kolejno to id listwy, typ czujnika; MOŻNA BY DODAC WERYFIKACJE CZY CZUJNIK JEST WLACZONY?!
char*   prepareMessageToSend (int);             // funkcja wykonujaca przygotowanie wiadomosci do wyslania; argumentem id listwy
void    adapterQueueCheck();                    // funckja sprawdzajaca czy kolejka jest pusta i jesli tak to wypelniajaca ja adapterami, w przeciwnym razie sprawdza czy adapter ma odpowiednia liczbe listw w kolejce, jesli nie to dodaje odpowiednia, sprawdza wartosc licznika zadania w kolejce i jesli jest mniejsze od maxRepeat to wysyla wiadomosc do listwy, usuwa wykonane zadania z kolejki
void    adapterQueueSend(int);                  // funkcja wykonujaca zadanie wyslania wiadomosci; argumentem id zadania
bool    waitForAnswer(int,int);                 // funkcja czekajaca na odpowiedz (stan czujnikow) po wyslaniu zapytania (wiadomosci ze stanami gniazd) odpowiedni czas, odczytujaca dane z wiadomosci odebranej, ustawiajaca stany polczanie, inkrementujaca licznik; argumenty kolejno to id listwy i id zadania
bool    checkExist(unsigned int);               // funckja sprawdzajaca czy listwa widnieje w kolejce adapterQueue, argumentem id listwy
void    removeFromQueue();                      // funckja usuwajaca listwy z kolejki adapterQueue na podstawie listy removeList
void    resetTxDelays();                        // funckja resetujaca opoznienia wzgledem siebie przy transmisji
bool    waitForAnswers();                       // funkcja czekajaca na wiadomosci z opowiednimi opoznieniami
void    prepareSend();                          // funckja zlecajaca wyslanei do adapterow danych
int     findTask(unsigned int);                 // funkcja wyszukujaca task w kolejce dla danego adresu listwy
bool    checkRecieved(unsigned int);            // funkcja sprawdzajaca czy pakiet odebrany od danego adaptera został już odebrany (prewencja echa)
void    checkRxFlags();                         // funkcja sprawdzajaca flagi po skończonym czasie pętli odpowednich listw są true, jeśli false to odpowiednio wykunuje czynności
// stałe
const int       maxRepeat = 30;           // 30 znaczy +- 10 sekund
const int       weakSignalRepeat = 5;     // 5 razy
const int       holdOn = 290;             // time we wait for respond
unsigned int    holdOnTime = 0;
const int       startDelay = 10;          // ms
const int       delayDiff = 5;            // roznica czasu pomiedzy listwami

// struktura tasku ponizej
// [0] "adres listwy", [1] "licznik nieudanych prób", [2] "opóźnienie transmisji", [3] "czas wysłania", [4] "flaga odebrania pakietu (prewencja echa)", [5] "pierwotny taskID"

int main(int argc, char* argv[])
{
    if (argv[1] != NULL)
    {
        string logging = argv[1];
        if(logging.find("logs") != string::npos)
        {
            logs = true;     // wlaczanie logowania
        }
        else
        {
            logs = false;
        }
    }
    if (argv[2] != NULL)
    {
        string logging_button = argv[2];
        if(logging_button.find("button") != string::npos)
        {
            button = true;     // wlaczanie logowania
        }
        else
        {
            button = false;
        }
    }

    long count = 0;     // petla programu
    // inicjalizacja parametrow modulu radiowego
    radio.begin();
    radio.powerUp();
    const uint64_t address_odbiorczy = 0xF0F0F0F0D2;
    const uint64_t address_nadawczy = 0xF0F0F0F0E1;
    radio.openReadingPipe(1,address_odbiorczy);
    radio.openWritingPipe(address_nadawczy);
    radio.setPALevel(RF24_PA_HIGH);
    radio.setDataRate(RF24_250KBPS);
    radio.setCRCLength(RF24_CRC_16);
    radio.setRetries(15,15);
    radio.setChannel(0x01);
    //radio.printDetails();

    mysql_init(&mysql); // inicjalizacja
    if (mysql_real_connect(&mysql, "127.0.0.1", "pi", "pi2018pi", "Home_control_and_survey", 0, NULL, 0)) // ustanowienie polaczenia z baza danych
    {
        while(1)
        {
            if (logs == true) if (logs == true) cout << endl << "\033[1;97m\t\t\tLoop: " << count << ", time: " << millis() << "\033[0m" << endl;
            adapterQueueCheck();
            prepareSend();
            waitForAnswers();
            checkRxFlags();
            removeFromQueue();
            count++;
            if (logs == true && button == true) cin.ignore();
            else if (logs == true) delay(1);
        }
    }
    else
        printf("Błąd połączenia z bazą &mysql: %d, %s\n", mysql_errno(&mysql), mysql_error(&mysql)); 
     
}

char* prepareMessageToSend(int taskID)
{
    getSocketStates(adapterQueue[taskID][0]);
    char* messageToSend = new char[32]{};
    char address_char[1];       // adres 1 cyfrowy
    char delay[2];      // delay 2 cyfrowy
    //oneness = adapterQueue[taskID][2] % 10;
    sprintf(address_char,"%d",adapterQueue[taskID][0]);     // funkcja konwertujaca adres intowy na adres charowy
    sprintf(delay,"%d",adapterQueue[taskID][2]);     // funkcja konwertujaca delay intowy na delay charowy
    messageToSend[0] = 't';
    messageToSend[1] = ':';
    messageToSend[2] = '0';
    messageToSend[3] = 'a';
    messageToSend[4] = ':';
    messageToSend[5] = address_char[0];
    messageToSend[6] = 'd';
    messageToSend[7] = ':';

    for (unsigned int i = 8;i < 12;i++)
    {
        messageToSend[i] = socketStates[i-8];
    }
    messageToSend[12] = 't';
    messageToSend[13] = ':';
    messageToSend[14] = delay[0];
    messageToSend[15] = delay[1];

    return messageToSend;
}

void adapterQueueCheck()
{

    if (!adapterQueue.empty()) // if not empty
    {
        if (logs == true) if (logs == true) cout << "___________________________________________________________________________________________________________________________________________" << endl << endl;
        
        mysql_query(&mysql, "SELECT adapter_id, adapter_connection FROM `adapters` WHERE `adapter_connection` > '0' AND `adapter_state` = '1'");
        result = mysql_store_result(&mysql);
        unsigned int howMany = mysql_num_rows(result);
        
        if (adapterQueue.size() < howMany)      // jesli trzeba dodac adaptery po usunieciu do kolejki
        {
            while ((row = mysql_fetch_row(result)) != NULL)
            {
                string adapterID_str;               // konwersja z char* na int
                unsigned int adapterID;                     // konwersja z char* na int
                adapterID_str = row[0];             // konwersja z char* na int
                adapterID = stoi(adapterID_str);   // konwersja z char* na int
                if (adapterID < 99)
                if (!checkExist(adapterID))         // jeśli nie istnieje
                {
                    unsigned int* queueTable = new unsigned int[6];       // tablica 2 intów adapterID oraz licznika

                    queueTable[0] = adapterID;
                    queueTable[1] = 0;      // wyzerowanie licznika
                    queueTable[4] = false;      // wyzerowanie licznika
                    queueTable[5] = 0;
                    adapterQueue.push_back(queueTable); // dodanie do kolejki
                    if (logs == true) cout << "TaskID: ?, address: " << &adapterQueue[adapterQueue.size()-1] << "\t\t Added adapterID: " << adapterID << " to the queue" << endl;

                }
            }
            resetTxDelays();
        }
        
        mysql_free_result(result);
        
    }
    else // if empty find all available adapters in db and add them to queue
    {
        if (logs == true) if (logs == true) cout << "___________________________________________________________________________________________________________________________________________" << endl << endl;
        int txDelay = startDelay;  // opoznienie wzgledem innych listw
        bool empty = false;
        mysql_query(&mysql, "SELECT adapter_id, adapter_connection FROM `adapters` WHERE `adapter_connection` > '0' AND `adapter_state` = '1'");
        result = mysql_store_result(&mysql);
        if (logs == true) cout << "----------------------------\t\t adapterQueue is empty, looking for adapter to add" << endl;
        if (logs == true && mysql_num_rows(result) != 0) cout << "----------------------------\t\t Found those adapters:" << endl;
        while ((row = mysql_fetch_row(result)) != NULL)
        {
            empty = true;
            string adapterID_str;               // konwersja z char* na int
            int adapter_ID;                   // konwersja z char* na int
            adapterID_str = row[0];             // konwersja z char* na int
            adapter_ID = stoi(adapterID_str);   // konwersja z char* na int

            unsigned int* queueTable = new unsigned int[6];       // tablica 2 intów adapterID oraz licznika

            queueTable[0] = adapter_ID;     // adres listwy
            queueTable[1] = 0;       // licznik
            queueTable[2] = txDelay;
            queueTable[4] = false;      // wyzerowanie licznika
            queueTable[5] = 0;
            if (logs == true) cout << "----------------------------\t\t aID: " << queueTable[0] << ", counter: " << queueTable[1] << ", delay: " << queueTable[2] << endl;
            adapterQueue.push_back(queueTable);     // dodanie do kolejki
            txDelay = txDelay + delayDiff;      // delay + delayDiff ms
        }
        if (logs == true && mysql_num_rows(result) != 0) cout << "----------------------------\t\t Proceeding to program" << endl;
        
        mysql_free_result(result);
        if (!empty) delay(3000);    // jesli nie ma listw, do ktorych mozna wyslac wiadomosc to odczekaj 1 sekunde i sprawdz ponownie
    }
}

void prepareSend()
{
    for (unsigned int i = 0;i < adapterQueue.size();i++)
    {
        if (logs == true) cout << endl << "TaskID: " << i << ", address: " << &adapterQueue[i] << "\t\t aID: " << adapterQueue[i][0] << ", counter: " << adapterQueue[i][1] << ", delay: " << adapterQueue[i][2] << endl;
        int counter = adapterQueue[i][1];
        if (counter <= maxRepeat)
        {
            adapterQueueSend(i);    
        }
    }
}

void adapterQueueSend(int taskID)
{
    char* ptr = prepareMessageToSend(taskID);  // get prepared data for the adapter from queue, we have to send object, not pointer to object in radio.write(*)
    //if (logs == true) cout << "\033[1;35m" << ptr << "\033[0m" << endl;
    char tempMessageTable[32]{};                        // we need to send an object not a ptr

    for (unsigned int i = 0;i < 32;i++)                          // rewriting object
    {
        tempMessageTable[i] = ptr[i];
    }
    
    radio.write(tempMessageTable, 32);     // send prepared message
    //if (millis() + holdOn + 10 + adapterQueue[taskID][2] + delayDiff >= 4294967296)
        //adapterQueue[taskID][3] = holdOn + 10 + adapterQueue[taskID][2] + delayDiff;
    //else
        adapterQueue[taskID][3] = millis();
    //cout << millis() << ", " << holdOn << ", " << adapterQueue[taskID][2] << ", " << millis() + holdOn + adapterQueue[taskID][2] << ", " << adapterQueue[taskID][3] << endl;
    if (logs == true) cout << "\033[1;92mTaskID: " << taskID << ", address: " << &adapterQueue[taskID] << "\t\t Message has been sent at time: \033[0m" << millis() << " -- > " << "\033[1;92m" << ptr << "\033[0m" << endl;
    //if (logs == true) cout << "\033[1;35mTaskID: " << taskID << ", address: " << &adapterQueue[taskID] << "\t\t Maximum wait time for message is set to: \033[0m" << adapterQueue[taskID][3] << endl;
    //waitForAnswer(adapterQueue[taskID][0], taskID);
}

bool waitForAnswers()
{
    if (adapterQueue.size() != 0)
    {
        radio.flush_rx();
        delay(200);

        unsigned int maxWaitTime = adapterQueue[adapterQueue.size() - 1][3] + 330 + adapterQueue[adapterQueue.size() - 1][2];
        if (logs == true) cout << "\n\033[1;35m----------------------------\t\t Maximal wait time is set to: \033[0m" << maxWaitTime << "\033[0m" << endl << endl;

        radio.startListening();

        while(millis() <= maxWaitTime)
        {
            if (radio.available())
            {
                char odebrane[32]{}, data[6]{};
                radio.read( &odebrane, 32 );
                //cout << "recieved: " << odebrane << " time: " << millis() <<  endl;

                if (odebrane[2] == '1' && odebrane[1] == ':' && odebrane[4] == ':' && odebrane[7] == ':' && odebrane[12] == '=' && odebrane[24] == '=')
                {
                    char adapterID = odebrane[5];
                    unsigned int adapterID_int = adapterID - '0';
                    int taskID = findTask(adapterID_int);

                    if (checkRecieved(adapterID_int))
                        continue;

                    // sprawdzenie i zaktualizowanie stanu polaczenia
                    if (adapterQueue[taskID][1] == 0)      // jeśli nie było problemów w transmisji
                    {
                        ostringstream strstr;
                        strstr << "UPDATE `adapters` SET `adapter_connection` = '2' WHERE `adapter_id` = '"  << adapterID << "'";
                        string str = strstr.str();
                        mysql_query(&mysql, str.c_str());
                    }
                    else if (adapterQueue[taskID][1] >= weakSignalRepeat)
                    {
                        ostringstream strstr;
                        strstr << "UPDATE `adapters` SET `adapter_connection` = '1' WHERE `adapter_id` = '"  << adapterID << "'";
                        string str = strstr.str();
                        mysql_query(&mysql, str.c_str());
                    }

                    if (logs == true) cout << "\033[1;36mTaskID: " << taskID << ", address: " << &adapterQueue[taskID] << "\t\t Message has been recieved at time: \033[0m" << millis() << " -- > \033[1;36m" << odebrane << "\033[0m" << endl;

                    for (int i = 0;i < 6;i++)
                    {
                        data[i] = odebrane[13+i];
                    }
                    addToMySqlDB(&data[0], "temperature", adapterID);

                    for (int i = 0;i < 6;i++)
                    {
                        data[i] = odebrane[25+i];
                    }
                    addToMySqlDB(&data[0], "light", adapterID);
                    
                    adapterQueue[taskID][4] = true;
                    adapterQueue[taskID][5] = taskID;   // do celów prawidłowego logowania usunięcia z listy removeList
                    
                    if (logs == true) cout << "\033[1;33mTaskID: " << taskID << ", address: " << &adapterQueue[taskID] << "\t\t Recieve flag has been set\033[0m" << endl;
                }
            
                radio.flush_rx();
                this_thread::sleep_for(nanoseconds(500));
            }
        }
        
        radio.stopListening();
        
        return true;
    }
    return false;
}

bool checkExist(unsigned int adapterID)
{
    for (unsigned int i = 0;i < adapterQueue.size();i++)
    {
        if (adapterQueue[i][0] == adapterID)
            return true;
    }
    return false;
}

void removeFromQueue()
{
    if (!removeList.empty())
    {
        if (logs == true) cout << endl;
        for (unsigned int i = 0;i < adapterQueue.size();i++)
        {
            for (unsigned int j = 0; j < removeList.size();j++)
            {
                if (removeList[j] == adapterQueue[i][0])
                {
                    if (logs == true) cout << "\033[1;93mTaskID: " << adapterQueue[i][5] << ", address: dontcare" << "\t\t Task has been sucessfully removed from the queue\033[0m" << endl;

                    if (adapterQueue[i][1] < weakSignalRepeat)
                    {
                        ostringstream strstr;
                        strstr << "UPDATE `adapters` SET `adapter_connection` = '2' WHERE `adapter_id` = '"  << adapterQueue[i][0] << "'";
                        string str = strstr.str();
                        mysql_query(&mysql, str.c_str());
                    }
                    else if (adapterQueue[i][1] >= weakSignalRepeat && adapterQueue[i][1] < maxRepeat)
                    {
                        ostringstream strstr;
                        strstr << "UPDATE `adapters` SET `adapter_connection` = '1' WHERE `adapter_id` = '"  << adapterQueue[i][0] << "'";
                        string str = strstr.str();
                        mysql_query(&mysql, str.c_str());
                    }

                    adapterQueue.erase(adapterQueue.begin()+i);
                }
            }
        }
        removeList.clear();
    }
}

void addToMySqlDB(char* data, string type, char adapter_id)
{
    // pobierz czas i date
    chrono::time_point<chrono::system_clock> time_now = chrono::system_clock::now();
    time_t time_now_t = chrono::system_clock::to_time_t(time_now);
    tm now_tm = *localtime(&time_now_t);
    char date[512];
    strftime(date, 512, "%d-%m-%Y", &now_tm);
    char time[512];
    strftime(time, 512, "%H:%M:%S", &now_tm);


    // wysli temperature do bazy danych czujnikow dla 1 czujnika
    ostringstream strstr;
    strstr << "UPDATE `sensors` SET `sensor_data`='" << data << "',`sensor_data_date`='" << date << "',`sensor_data_time`='" << time << "' WHERE sensor_id= " << checkSensor(adapter_id, type);
    string str = strstr.str();
    mysql_query(&mysql, str.c_str());
}

bool checkSensorState()
{
        mysql_query(&mysql, "SELECT `sensor_state` FROM `sensors` WHERE `sensor_id` = 2");
        result = mysql_store_result(&mysql);
        row = mysql_fetch_row(result);
        bool state = row[0] ? true : false;
        mysql_free_result(result);
        if (state)
           return true;
        else
           return false;
}

string checkSensor(char adapter_id, string type)
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
    for (unsigned int i = 0; i < adapterQueue.size();i++)
    {
        adapterQueue[i][2] = txDelay;
        txDelay = txDelay + delayDiff;
    }
    if (logs == true) cout << "\033[1;37m----------------------------\t\t New delays assigned\033[0m" << endl;
}

int findTask(unsigned int adapterID)
{
    for (unsigned int i = 0; i < adapterQueue.size();i++)
    {
        if (adapterQueue[i][0] == adapterID)
            return i;
    }
    return 0;
}

bool checkRecieved(unsigned int adapterID)
{
    for (unsigned int i = 0; i < adapterQueue.size();i++)
    {
        if (adapterQueue[i][0] == adapterID)
            if (adapterQueue[i][4] == true)
                return true;
    }
    return false;
}

void checkRxFlags()
{
    for (unsigned int i = 0; i < adapterQueue.size();i++)
    {
        if (adapterQueue[i][4] == true)
        {
            removeList.push_back(adapterQueue[i][0]);
        }
        else
        {
            if (logs == true) cout << "\033[1;31mTaskID: " << i << ", address: " << &adapterQueue[i] << "\t\t Message hasn`t been recieved at time: \033[0m" << millis() << "\033[1;31m skipping and setting counter to: " << adapterQueue[i][1] + 1 << "\033[0m" << endl;
            if (adapterQueue[i][1] == weakSignalRepeat)      // jeśli nie było problemów w transmisji
            {
                ostringstream strstr;
                strstr << "UPDATE `adapters` SET `adapter_connection` = '1' WHERE `adapter_id` = '"  << adapterQueue[i][0] << "'";
                string str = strstr.str();
                mysql_query(&mysql, str.c_str());
                if (logs == true) cout << "\033[94mTaskID: " << i << ", address: " << &adapterQueue[i] << "\t\t Weak signal detected\033[0m" << endl;
            }
            else if (adapterQueue[i][1] == maxRepeat)
            {
                ostringstream strstr;
                strstr << "UPDATE `adapters` SET `adapter_connection` = '0', `adapter_state` = '0' WHERE `adapter_id` = '"  << adapterQueue[i][0] << "'";
                string str = strstr.str();
                mysql_query(&mysql, str.c_str());
                removeList.push_back(adapterQueue[i][0]);
                //adapterQueue.erase(adapterQueue.front()+i);           // remove task from queue 
                if (logs == true) cout << "\033[1;33mTaskID: " << i << ", address: " << &adapterQueue[i] << "\t\t Adapter state changed to 0 (switched off)\033[0m" << endl;
                if (logs == true) cout << "\033[1;33mTaskID: " << i << ", address: " << &adapterQueue[i] << "\t\t Task has been added to remove list due to no response\033[0m" << endl;
            }

            adapterQueue[i][1]++;
        }
                
    }
}
