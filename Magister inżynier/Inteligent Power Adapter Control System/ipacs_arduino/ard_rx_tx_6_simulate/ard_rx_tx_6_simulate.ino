#include <OneWire.h>
#include <DallasTemperature.h>

#include <RF24.h>
#include <SPI.h>
#include <nRF24L01.h>
#define fotoResPIN A4
#define buttonIn 3

// COM4

// Data wire is plugged into port 2 on the Arduino
#define ONE_WIRE_BUS 5
#define TEMPERATURE_PRECISION 10 // Lower resolution, 188ms

// Setup a oneWire instance to communicate with any OneWire devices (not just Maxim/Dallas temperature ICs)
OneWire oneWire(ONE_WIRE_BUS);

// Pass our oneWire reference to Dallas Temperature. 
DallasTemperature sensors(&oneWire);

//DeviceAddress tempDeviceAddress = 28FFAE27861605E5; // We'll use this variable to store a found device address
uint8_t tempDeviceAddress[8] = {0x28,0xFF,0xAE,0x27,0x86,0x16,0x05,0xE5};
 
//
// Hardware configuration
//
// Setup for GPIO 15 CE and CE0 CSN with SPI Speed @ 8Mhz
RF24 radio(7,8);
const uint64_t pipes[2] = { 0xF0F0F0F0E1LL, 0xF0F0F0F0D2LL };
//const byte address[6] = "00001";
const uint64_t address_odbiorczy = 0xF0F0F0F0E1;
const uint64_t address_nadawczy = 0xF0F0F0F0D2;

unsigned long czas;
char adapterAddress[2] = {'1','9'};

void clearMessage();

void setup() {
  Serial.begin(38400);
  pinMode(2,OUTPUT);
      digitalWrite(2,LOW);
  radio.begin();
  radio.openReadingPipe(1, address_odbiorczy);
  radio.openWritingPipe(address_nadawczy);
  radio.setPALevel(RF24_PA_MIN);
  radio.setDataRate(RF24_250KBPS);
  radio.setCRCLength(RF24_CRC_16);
  radio.setRetries(15,15);
  radio.setChannel(25);
  radio.startListening();
  
  pinMode(6,OUTPUT);
  digitalWrite(6,LOW);
  pinMode(fotoResPIN, INPUT);
  

  czas = millis();
  
  // Start up the library
  sensors.begin();
  sensors.setResolution(tempDeviceAddress, TEMPERATURE_PRECISION);
Serial.println("Devices started!");
}

char recievedMessage[32]{};
void loop() {
  // część odbiorcza
  
  radio.startListening();
  
  if (radio.available()) 
  {
    radio.read(&recievedMessage, sizeof(recievedMessage));
    Serial.print("Recieved message is: ");
    Serial.println(recievedMessage);

    if (recievedMessage[0] == '0')
    {
      if (recievedMessage[1] == '_' && recievedMessage[4] == '_' && recievedMessage[9] == '_' && recievedMessage[2] == adapterAddress[0] && recievedMessage[3] == adapterAddress[1])
      {
        Serial.print("<-- Message recieved in: ");
        Serial.println(millis());
        digitalWrite(2,HIGH);
        Serial.print("Recipient address is: ");
        Serial.print(adapterAddress[0]);
        Serial.println(adapterAddress[1]);
        Serial.print("Socket states are: ");
        Serial.print(recievedMessage[5]);
        Serial.print(recievedMessage[6]);
        Serial.print(recievedMessage[7]);
        Serial.println(recievedMessage[8]);
        //if (digitalRead(buttonIn) == 1)
        {
          if (recievedMessage[5] == '1')
          {
            analogWrite(A0, 0);
          }
          else
          {
            analogWrite(A0, 1024);
          }
          if (recievedMessage[6] == '1')
          {
            analogWrite(A1, 0);
          }
          else
          {
            analogWrite(A1, 1024);
          }
          if (recievedMessage[7] == '1')
          {
            analogWrite(A2, 0);
          }
          else
          {
            analogWrite(A2, 1024);
          }
          if (recievedMessage[8] == '1')
          {
            analogWrite(A3, 0);
          }
          else
          {
            analogWrite(A3, 1024);
          }
        }
        
        int first = recievedMessage[10] - '0';
        int second = recievedMessage[11] - '0';
        int txDelay = first * 10 + second;
  
      Serial.print("Send delay is: ");
      Serial.println(txDelay);
      Serial.println();
      
      radio.stopListening();
      radio.flush_rx();
      
      // część nadawcza
      {
        double light = millis()%40+20;
  
        delay(250);
        float tempC = millis()%10+20;
        char temperature[6] = "";
        String commandString = "1_";
        commandString += adapterAddress[0];
        commandString += adapterAddress[1];
        commandString+= "_";
        char data[6] = "";
        dtostrf(tempC, 4, 2, data);  //4 is mininum width, 6 is precision
        //commandString += "temp=";
        
        commandString += data;
        Serial.print("Adapter temperature is: ");
        Serial.println(data);
        
        dtostrf(light, 4, 3, data);  //4 is mininum width, 6 is precision
        commandString += "_";
        if (light >= 10)
          commandString += light;
        else
        {
          commandString += "0";
          commandString += light;
        }
        commandString += "_";
        commandString += 1;
        char command[32];
        strcpy(command, commandString.c_str());
  
        delay(txDelay);
        radio.write(command,strlen(command));
        
        
        Serial.print("Adapter light intensity is: ");
        Serial.print(light);
        Serial.println('%');

        Serial.print("Switch on position: ");
        Serial.println(digitalRead(buttonIn));
  
        Serial.print("Sent message is: ");
        Serial.println(command);
        
        Serial.print("--> Message sent in: ");
        Serial.println(millis());
        Serial.println();
  
        clearMessage();
        digitalWrite(2,LOW);
      }
      }
    }
   else if (recievedMessage[0] == '4')
     if (recievedMessage[1] == '_' && recievedMessage[4] == '_' && recievedMessage[2] == adapterAddress[0] && recievedMessage[3] == adapterAddress[1])
     {
        radio.powerDown();
        if (recievedMessage[5] == "0")
            radio.setPALevel(RF24_PA_MIN);
        else if (recievedMessage[5] == "1")
            radio.setPALevel(RF24_PA_LOW);
        else if (recievedMessage[5] == "2")
            radio.setPALevel(RF24_PA_HIGH);
        else if (recievedMessage[5] == "3")
            radio.setPALevel(RF24_PA_MAX);
        radio.powerUp();
        Serial.print("Changed powerLevel to: ");
        Serial.println(recievedMessage[5]);
  
        radio.flush_rx();
        radio.stopListening();
  
        String commandString = "9_4";
        
        char command[32];
        strcpy(command, commandString.c_str());
        radio.write(command,strlen(command));
  
        clearMessage();
     }
  }
}

void clearMessage()
{
  for (int i = 0;i < 32;i++)
  {
    recievedMessage[i] = ' ';
  }
}











