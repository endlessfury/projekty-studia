class Task {
    private:
        unsigned int adapterID;
        unsigned int waitNumberCounter;
        unsigned int maxWaitNumber;
        unsigned int weakSignalIndicator;
        unsigned int txDelay;
        unsigned int sendingTime;
        bool rxFlag;
    public:
        Task();
        ~Task();

        void setAdapterID(unsigned int);
        unsigned int readAdapterID();

        void setWaitNumberCounter(unsigned int);
        unsigned int readWaitNumberCounter();
        
        void setMaxWaitNumber(unsigned int);
        unsigned int readMaxWaitNumber();

        void setWeakSignalIndicator(unsigned int);
        unsigned int readWeakSignalIndicator();

        void setTxDelay(unsigned int);
        unsigned int readTxDelay();

        void setSendingTime(unsigned int);
        unsigned int readSendingTime();

        void setRxFlag(bool);
        bool readRxFlag();
};