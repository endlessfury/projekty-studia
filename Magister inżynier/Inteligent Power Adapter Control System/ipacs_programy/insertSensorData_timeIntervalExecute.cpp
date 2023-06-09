#pragma once
#include <iostream>
#include <thread>
#include <chrono>
#include <ctime>
#include <cstdlib>
using namespace std::chrono;
using namespace std::this_thread;
using namespace std;
#include "timeArray.cpp"

int main(int argc, char* argv[])
{
    bool logs = false;
    if (argv[1] != NULL)
    {
        string logging = argv[1];
        if(logging.find("logs") != string::npos)
        {
            logs = true;     // wlaczanie logowania
        }
    }

    bool flaga = false;
    int ktoryCzas = 0;
    while(1)
    {
        // pobierz czas i date
        chrono::time_point<chrono::system_clock> time_now = chrono::system_clock::now();
        time_t time_now_t = chrono::system_clock::to_time_t(time_now);
        tm now_tm = *localtime(&time_now_t);
        char czas[512];
        strftime(czas, 512, "%H:%M", &now_tm);

        for (int i = 0;i < 96;i++)
        {
            if (timeArray[i] == czas && i != ktoryCzas)
            {
                if (logs) cout << "Sensor Data Insertion" << endl;
                flaga = true;
                ktoryCzas = i;
                std::system("./insertSensorData");
                break;
            }
            
        }
            sleep_until(system_clock::now() + 60000ms);
            if (logs) cout << "Time is: " << czas << endl;
    }
}

