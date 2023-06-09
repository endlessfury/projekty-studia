#include <cstdlib>
#include <iostream>
#include <mysql/mysql.h>
#include <sstream>
#include <string>
#include <dirent.h>
#include <unistd.h>
#include <stdio.h>
#include <fcntl.h>
#include <ctime>
#include <chrono>
#include <thread>
#include <iomanip>
#include <vector>

using namespace std;
using namespace std::chrono;
using namespace std::this_thread;

MYSQL mysql; // tworzymy zmienną typu mysql
MYSQL_ROW row;
MYSQL_RES *result;

vector <long> tasksToExecute;

void checkTasks();
void executeTasks();

int main()
{
    
    while(1)
    {
        mysql_init(&mysql); // inicjalizacja
        if (mysql_real_connect(&mysql, "127.0.0.1", "pi", "pi2018pi", "ipacs_database", 0, NULL, 0))
        {
            while(1)
            {
                checkTasks();
                executeTasks();
                sleep_until(system_clock::now() + 1000ms);
                //cout << endl;
            }
        }
        else
            printf("Błąd połączenia z bazą &mysql: %d, %s\n", mysql_errno(&mysql), mysql_error(&mysql));
    } 
}

void checkTasks()
{
  
    mysql_query(&mysql, "SELECT `task_id`, `task_type`, `task_condition`, `sensor_data`, `sensor_state`, `task_error`, `task_state`, `task_active` FROM `socket_tasks` INNER JOIN sensors ON sensors.sensor_id=socket_tasks.sensor_id WHERE task_state='1'");
    result = mysql_store_result(&mysql);
    while ((row = mysql_fetch_row(result)) != NULL)
    {
        string task_type, task_condition, task_id_str, sensor_data_str, sensor_state_str, task_error, task_state_str, task_active;
        long task_id;
        double sensor_data, task_condition_double;
        int sensor_state, task_state;
        task_id_str = row[0];
        task_id = stol(task_id_str);
        sensor_data_str = row[3];
        sensor_data = stod(sensor_data_str);
        task_type = row[1];
        task_condition = row[2];
        sensor_state_str = row[4];
        sensor_state = stoi(sensor_state_str);
        task_error = row[5];
        task_state_str = row[6];
        task_state = stoi(task_state_str);
        task_active = row[7];
        
        if (sensor_state == 0 and task_error == "" and task_state == 1 and task_active == "1") // sprawdzić czy to działa!
        {
            ostringstream strstr;
            strstr << "UPDATE `socket_tasks` SET `task_error` = 'czujnik wylaczony' WHERE `socket_tasks`.`task_id` = "  << task_id;
            string str = strstr.str();
            //cout  << str  << endl;
            mysql_query(&mysql, str.c_str());
        }
        else if (task_error == "" and task_state == 1 and task_active == "1") 
        {
            // sprawdzanie warunków
            if (task_type == "time_off" || task_type == "time_on")
            {
                // pobranie znaków i ich przetworzenie na int
                char firstPart_chars[2] = {task_condition[0], task_condition[1]};
                char secondPart_chars[2] = {task_condition[3], task_condition[4]};
                int firstPart_digits[2], secondPart_digits[2];
                firstPart_digits[0] = firstPart_chars[0] - '0';
                firstPart_digits[1] = firstPart_chars[1] - '0';
                secondPart_digits[0] = secondPart_chars[0] - '0';
                secondPart_digits[1] = secondPart_chars[1] - '0';
                int firstPart = firstPart_digits[0] * 10 + firstPart_digits[1], secondPart = secondPart_digits[0] * 10 + secondPart_digits[1];
                //cout << firstPart << task_condition[2] << secondPart << endl;

                if (task_condition.length() != 5 || task_condition[2] != ':' || firstPart > 24 || firstPart < 0 || secondPart > 60 || secondPart < 0)
                {
                    //cout <<  " bad" << endl;
                    ostringstream strstr;
                    strstr << "UPDATE `socket_tasks` SET `task_active` = '0', `task_error` = 'bledny warunek' WHERE `socket_tasks`.`task_id` = "  << task_id;
                    string str = strstr.str();
                    mysql_query(&mysql, str.c_str());
                    continue;
                }
            }
            else if (task_type == "temp_up_on" || task_type == "temp_down_on"  || task_type == "temp_up_off" || task_type == "temp_down_off")
            {
                // pobranie znaków i ich przetworzenie na int
                char firstPart_chars[2] = {task_condition[0], task_condition[1]};
                char secondPart_chars[2] = {task_condition[3], task_condition[4]};
                int firstPart_digits[2], secondPart_digits[2];
                firstPart_digits[0] = firstPart_chars[0] - '0';
                firstPart_digits[1] = firstPart_chars[1] - '0';
                secondPart_digits[0] = secondPart_chars[0] - '0';
                secondPart_digits[1] = secondPart_chars[1] - '0';
                int firstPart = firstPart_digits[0] * 10 + firstPart_digits[1], secondPart = secondPart_digits[0] * 10 + secondPart_digits[1];

                if (task_condition.length() > 2)
                {
                    //cout << firstPart << task_condition[2] << secondPart << endl;
                    if (task_condition[2] != ',' || firstPart > 99 || firstPart < 0 || secondPart > 99 || secondPart < 0)
                    {
                        //cout <<  " bad" << endl;
                        ostringstream strstr;
                        strstr << "UPDATE `socket_tasks` SET `task_active` = '0', `task_error` = 'bledny warunek' WHERE `socket_tasks`.`task_id` = "  << task_id;
                        string str = strstr.str();
                        mysql_query(&mysql, str.c_str());
                        continue;
                    }
                }
                else
                {
                    //cout << firstPart << endl;
                    if (firstPart > 99 || firstPart < 0)
                    {
                        //cout <<  " bad" << endl;
                        ostringstream strstr;
                        strstr << "UPDATE `socket_tasks` SET `task_active` = '0', `task_error` = 'bledny warunek' WHERE `socket_tasks`.`task_id` = "  << task_id;
                        string str = strstr.str();
                        mysql_query(&mysql, str.c_str());
                        continue;
                    }
                }
            }
            else if (task_type == "light_up_on" || task_type == "light_down_on"  || task_type == "light_up_off" || task_type == "light_down_off")
            {
                // pobranie znaków i ich przetworzenie na int
                char firstPart_chars[2] = {task_condition[0], task_condition[1]};
                int firstPart_digits[2];
                firstPart_digits[0] = firstPart_chars[0] - '0';
                firstPart_digits[1] = firstPart_chars[1] - '0';
                int firstPart = firstPart_digits[0] * 10 + firstPart_digits[1];
                cout << firstPart << endl;

                if (firstPart > 99 || firstPart < 0 || task_condition.length() > 2)
                {
                    cout <<  " bad" << endl;
                    ostringstream strstr;
                    strstr << "UPDATE `socket_tasks` SET `task_active` = '0', `task_error` = 'bledny warunek' WHERE `socket_tasks`.`task_id` = "  << task_id;
                    string str = strstr.str();
                    mysql_query(&mysql, str.c_str());
                    continue;
                }
            }

            // właściwy program
            if (task_type == "time_off" || task_type == "time_on")
            {
                // pobierz czas i date
                chrono::time_point<chrono::system_clock> time_now = chrono::system_clock::now();
                time_t time_now_t = chrono::system_clock::to_time_t(time_now);
                tm now_tm = *localtime(&time_now_t);
                char czas[512];
                strftime(czas, 512, "%H:%M", &now_tm);

                //cout  << task_type << ", "  << task_condition  << ", "  << czas << endl;
                if (task_condition == czas)
                {
                    tasksToExecute.push_back(task_id);
                }
            }
            else if  (task_type == "temp_up_on" || task_type == "light_up_on"  || task_type == "temp_up_off" || task_type == "light_up_off")
            {
                task_condition_double = stod(task_condition);
                //cout << task_id << ", " << task_state << ", " << task_type << task_condition_double  << ", " << sensor_data  << endl;
                if (sensor_data > task_condition_double)
                {
                    tasksToExecute.push_back(task_id);
                }
            }
            else if  (task_type == "temp_down_on" || task_type == "light_down_on"  || task_type == "temp_down_off" || task_type == "light_down_off")
            {
                task_condition_double = stod(task_condition);
                //cout << task_id << ", " << task_state << ", " << task_type << task_condition_double  << ", " << sensor_data  << endl;
                if (sensor_data < task_condition_double)
                {
                    tasksToExecute.push_back(task_id);
                }
            }
        }
        
    }
    mysql_free_result(result);
}

void executeTasks()
{
    mysql_options(&mysql, MYSQL_SET_CHARSET_NAME, "utf8");
    mysql_options(&mysql, MYSQL_INIT_COMMAND, "SET NAMES utf8");

    mysql_query(&mysql, "SELECT `socket_tasks`.`socket_id`, `task_id`, `task_type`, `task_cycle`, `socket_tasks`.`adapter_id`, `sockets`.`socket_state`  FROM `socket_tasks`  INNER JOIN sockets ON sockets.socket_id=socket_tasks.socket_id WHERE task_cancel = '' or task_cancel LIKE 'Aktu%' or task_cancel LIKE 'Zat%'");
    result = mysql_store_result(&mysql);

    while ((row = mysql_fetch_row(result)) != NULL)
    {
        string socket_id_str, task_id_str, task_type, socket_cycle_str, adapter_id_str, socket_state;
        long task_id;
        int socket_id, socket_cycle_int, adapter_id;
        socket_id_str = row[0];
        socket_id = stoi(socket_id_str);
        task_id_str = row[1];
        task_id = stol(task_id_str);
        task_type = row[2];
        socket_cycle_str = row[3];
        socket_cycle_int = stoi(socket_cycle_str);
        adapter_id_str = row[4];
        adapter_id = stoi(adapter_id_str);
        socket_state = row[5];
        //cout << socket_state << endl;
        
        for  (unsigned int i = 0;i  < tasksToExecute.size();i++)
        {
            string new_socket_state = "";
            if (tasksToExecute[i] == task_id)
            {
                // pobierz czas i date
                chrono::time_point<chrono::system_clock> time_now = chrono::system_clock::now();
                time_t time_now_t = chrono::system_clock::to_time_t(time_now);
                tm now_tm = *localtime(&time_now_t);
                char data[512];
                strftime(data, 512, "%d-%m-%Y", &now_tm);
                char czas[512];
                strftime(czas, 512, "%H:%M", &now_tm);

                if (task_type == "temp_up_on" || task_type == "temp_down_on" || task_type ==  "time_on"  || task_type == "light_up_on" || task_type == "light_down_on")
                {
                    //cout << "Task id: " << task_id << ", type: " << task_type << ", socket state: " << socket_state << ", new socket state: ";
                    ostringstream strstr;
                    strstr << "UPDATE `sockets` SET `socket_state` = '1' WHERE `sockets`.`socket_id` = "  << socket_id;
                    string str = strstr.str();
                    //cout  << str  << endl;
                    mysql_query(&mysql, str.c_str());
                    new_socket_state = "1";
                    //cout  << new_socket_state << endl;

                    if (new_socket_state != socket_state)
                    {
                        ostringstream strstr3;
                        strstr3 << "INSERT INTO `socket_logs`(`log_id`, `adapter_id`, `socket_id`, `socket_state`, `last_changed`, `log_time`, `log_date`) VALUES('','" << adapter_id << "','" << socket_id << "','" << new_socket_state << "','Menadzer zadan','" << czas << "','" << data << "')";
                        string str3 = strstr3.str();
                        //cout  << str3  << endl;
                        mysql_query(&mysql, str3.c_str());
                    }
                }
                else if (task_type == "temp_up_off" || task_type == "temp_down_off" || task_type ==  "time_off"  || task_type == "light_up_off" || task_type == "light_down_off")
                {
                    ostringstream strstr;
                    strstr << "UPDATE `sockets` SET `socket_state` = '0' WHERE `sockets`.`socket_id` = "  << socket_id;
                    string str = strstr.str();
                    //cout  << str  << endl;
                    mysql_query(&mysql, str.c_str());
                    new_socket_state = "0";
                    //cout << new_socket_state << endl;

                    if (new_socket_state != socket_state)
                    {
                        ostringstream strstr3;
                        strstr3 << "INSERT INTO `socket_logs`(`log_id`, `adapter_id`, `socket_id`, `socket_state`, `last_changed`, `log_time`, `log_date`) VALUES('','" << adapter_id << "','" << socket_id << "','" << new_socket_state << "','Menadzer zadan','" << czas << "','" << data << "')";
                        string str3 = strstr3.str();
                        //cout  << str3  << endl;
                        mysql_query(&mysql, str3.c_str());
                    }
                }

                if (socket_cycle_int == 0)
                {
                    ostringstream strstr;
                    strstr << "UPDATE `socket_tasks` SET `task_cancel` = 'Zakonczono: " << czas  << ", "  << data << "', task_state = 0 WHERE `socket_tasks`.`task_id` = "  << task_id;
                    string str = strstr.str();
                    //cout  << str  << endl;
                    mysql_query(&mysql, str.c_str());
                    ostringstream strstr2;
                    strstr2 << "UPDATE `sockets` SET `socket_task_control` = '0' WHERE `sockets`.`socket_id` = "  << socket_id;
                    string str2 = strstr2.str();
                    //cout  << str2  << endl;
                    mysql_query(&mysql, str2.c_str());
                }
                else
                {
                    ostringstream strstr;
                    strstr << "UPDATE `socket_tasks` SET `task_cancel` = 'Aktualizacja: " << czas  << ", "  << data << "' WHERE `socket_tasks`.`task_id` = "  << task_id;
                    string str = strstr.str();
                    //cout  << str  << endl;
                    mysql_query(&mysql, str.c_str());
                }

                

                tasksToExecute.erase(tasksToExecute.begin()+i);
                
            }
        }
    }
    mysql_free_result(result);
}
