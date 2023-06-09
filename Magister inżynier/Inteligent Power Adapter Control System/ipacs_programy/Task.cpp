#include "Task.h"
#include <iostream>

void Task::setAdapterID(unsigned int address)
{
    adapterID = address;
}

unsigned int Task::readAdapterID()
{
    return adapterID;
}

void Task::setWaitNumberCounter(unsigned int counter)
{
    waitNumberCounter = counter;
}

unsigned int Task::readWaitNumberCounter()
{
    return waitNumberCounter;
}

void Task::setMaxWaitNumber(unsigned int number)
{
    maxWaitNumber = number;
}
unsigned int Task::readMaxWaitNumber()
{
    return maxWaitNumber;
}

void Task::setWeakSignalIndicator(unsigned int number)
{
    weakSignalIndicator = number;
}
unsigned int Task::readWeakSignalIndicator()
{
    return weakSignalIndicator;
}


void Task::setTxDelay(unsigned int delay)
{
    txDelay = delay;
}
unsigned int Task::readTxDelay()
{
    return txDelay;
}

void Task::setSendingTime(unsigned int time)
{
    sendingTime = time;
}
unsigned int Task::readSendingTime()
{
    return sendingTime;
}

void Task::setRxFlag(bool flag)
{
    rxFlag = flag;
}
bool Task::readRxFlag()
{
    return rxFlag;
}

Task::Task() {}

Task::~Task() {}