#include <iostream>
#include <cstdlib>
#include <thread>
#include <chrono>
using namespace std;
using namespace std::chrono;
using namespace std::this_thread;

int main(void)
{
    cout << "Wait 10 seconds to pretend MySQL won't start too early" << endl;
    sleep_until(system_clock::now() +15000ms);
    system("sudo /home/pi/Desktop/ipacs_programy/insertSensorData_timeIntervalExecute &");
    system("sudo /home/pi/Desktop/ipacs_programy/ipacs &");
    system("sudo /home/pi/Desktop/ipacs_programy/taskManager &");
}
