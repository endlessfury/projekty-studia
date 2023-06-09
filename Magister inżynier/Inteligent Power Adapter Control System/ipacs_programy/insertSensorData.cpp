#include <mysql/mysql.h>
#include <cstdlib>
#include <iostream>
#include <sstream>
#include <cstring>
#include <dirent.h>
#include <unistd.h>
#include <stdio.h>
#include <fcntl.h>
#include <ctime>
#include <chrono>
#include <iomanip>
using namespace std;

MYSQL mysql; // tworzymy zmienn� typu MYSQL
MYSQL_RES *result;
MYSQL_RES *adapterResult;
MYSQL_ROW row;

void getDataFromSensors(int);

int main(void)
{
    //bool sensor = false, sensor2 = false;

    mysql_init(&mysql); // inicjalizacja
    
    if (mysql_real_connect(&mysql, "127.0.0.1", "pi", "rpiipacs", "ipacs_database", 0, NULL, 0))
    {
        // pobierz dane z czujnika 1
        mysql_query(&mysql, "SELECT adapter_id, adapter_state FROM `adapters` WHERE `adapter_beacon` = 1");
        //SELECT * FROM `adapters` WHERE 1
        //SELECT sensors.sensor_id, sensors.sensor_type, sensors.sensor_data, sensors.adapter_id, adapters.adapter_state, sensors.sensor_state FROM `sensors` INNER JOIN adapters ON sensors.adapter_id=adapters.adapter_id AND sensors.sensor_type != 'time'
        result = mysql_store_result(&mysql);
        while ((row = mysql_fetch_row(result)) != NULL)
        {
            string adapterState = row[1];
            if (adapterState == "1")
            {
                string adapterID_str = row[0];
                int adapterID = stoi(adapterID_str);
                getDataFromSensors(adapterID);
            }
        }
        mysql_free_result(result);
    }
    else
        printf("Blad polaczenia z bazz MySQL: %d, %s\n", mysql_errno(&mysql), mysql_error(&mysql));
    
    mysql_close(&mysql); // zamknij po��czenie
}

void getDataFromSensors(int adapterID)
{
    
    // pobierz czas i date
    chrono::time_point<chrono::system_clock> time_now = chrono::system_clock::now();
    time_t time_now_t = chrono::system_clock::to_time_t(time_now);
    tm now_tm = *localtime(&time_now_t);
    char data[512];
    strftime(data, 512, "%d-%m-%Y", &now_tm);
    char czas[512];
    strftime(czas, 512, "%H:%M", &now_tm);

    ostringstream strstr;
    strstr << "SELECT sensors.sensor_state, sensors.sensor_id, sensors.sensor_type, sensors.sensor_data, sensors.adapter_id FROM `sensors` INNER JOIN adapters ON sensors.adapter_id=adapters.adapter_id AND sensors.sensor_type != 'time' WHERE adapters.adapter_id = " << adapterID;
    string str = strstr.str();
    //cout  << str  << endl;
    mysql_query(&mysql, str.c_str());
    adapterResult = mysql_store_result(&mysql);
    while ((row = mysql_fetch_row(adapterResult)) != NULL)
    {
        string sensorState_str = row[0], sensorData_str = row[3], sensorID_str = row[1];
        short sensorState = stoi(sensorState_str);
        double sensorData = stod(sensorData_str);
        int sensorID = stoi(sensorID_str);
        if (sensorState == 1)
        {
            // wysli dane z czujnika do bazy danych 
            ostringstream strstr;
            strstr << "INSERT INTO `sensor_data` (`data_id`, `sensor_id`, `data_time`, `data_date`, `sensor_data`) VALUES('', '" << sensorID << "','" << czas << "','" << data << "','" << sensorData << "')";
            string str = strstr.str();
            //cout << str.c_str() << endl;
            mysql_query(&mysql, str.c_str());
            //mysql_query(&mysql,"DELETE FROM sensor_data ORDERBY data_id LIMIT 1");
        }
    }
    mysql_free_result(adapterResult);
}