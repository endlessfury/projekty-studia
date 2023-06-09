#include <stdio.h>
#include <stdlib.h>
#include <iostream>
#include <mysql/mysql.h>
#include "./RF_lib/RF24.h"
#include <vector>
#include "Task.h"
using namespace std;

MYSQL mysql;                // tworzenie obiektu MYSQL do zestawienia polaczenia
MYSQL_ROW row;              // tworzenie obiektu ROW do przechowania kolumn wiersza
MYSQL_ROW row2;              // tworzenie obiektu ROW do przechowania kolumn wiersza
MYSQL_RES *result;          // tworzenie wskaźnika do obiektu typu result do przetrzymania calego wyniku zapytania
MYSQL_RES *result2;          // tworzenie wskaźnika do obiektu typu result do przetrzymania calego wyniku zapytania

vector <Task*> taskQueue; // kontener typu vector do kolejkowania adapterow do odpytania
vector <Task*> removeList;    // kontener typu vector do przechowania listy adapterow do usuniecia z kolejki taskQueue

int main()
{

    mysql_init(&mysql); // inicjalizacja
    if (mysql_real_connect(&mysql, "127.0.0.1", "pi", "pi2018pi", "Home_control_and_survey", 0, NULL, 0)) // ustanowienie polaczenia z baza danych
    {
        Task* newTask = new Task();
        /*newTask->setAdapterID(1);
        newTask->setWaitNumberCounter(0);
        newTask->setMaxWaitNumber(87);
        newTask->setRxFlag(false);
        taskQueue.push_back(newTask); // dodanie do kolejki*/
        cout << sizeof(*newTask) << endl;
        
        //cout << taskQueue[0]->readAdapterID() << ", " << taskQueue[0]->readWaitNumberCounter() << ", " << taskQueue[0]->readMaxWaitNumber() << ", " << taskQueue[0]->readRxFlag() << endl; 
    }
    else
        printf("Błąd połączenia z bazą &mysql: %d, %s\n", mysql_errno(&mysql), mysql_error(&mysql)); 
     
}

