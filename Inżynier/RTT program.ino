#define DEBUG 
#ifdef DEBUG
 #define DEBUG_PRINTLN(x) Serial.println (x)
 #define DEBUG_PRINT(x)   Serial.print (x)
#else
 #define DEBUG_PRINTLN(x)
 #define DEBUG_PRINT(x)
#endif

#define input1 A0
#define input2 A1
#define input3 A2
#define input4 A3
#define output1 A4
#define output2 A5
#define output3 A6
#define output4 A7
#define brzeczyk 11
#define analogHIGH 1024
#define connection 10

#include <LiquidCrystal.h>
LiquidCrystal lcd(9, 8, 7, 6, 5, 4);

#include <SoftwareSerial.h>
SoftwareSerial mySerial(3, 2); // RX, TX

#include <Timers.h>

//wstepna deklaracja globalna
Timer czas_wduszenia;
Timer wybor_guzika;
Timer status_polaczenia_czas;
Timer rozlaczony;
int czas_wduszenia_ms = 100;
int czas_zatwierdzenia = 700;
char wiadomosc_do_wyslania[18];

// spis dostepnych funkcji
// czesc ogolna
void setup(); // funkcja inicjalizujaca
void loop(); // funkcja glowna programu
void status_polaczenia();
void aktualizuj_ostatnie_wiadomosci(); // funkcja wrzucajaca ostatnia wiadomosc na gore
void wypisz_ostatnie_wiadomosci(bool);
void dzwiek_klawiatury(); // klawiatura wydaje dzwieki
void sprawdz_polaczenie(); // funkcja sprawdzajace polaczenie i dajaca komunikat
void zatwierdz_litere(bool); // funkcja potwierdzajaca wybranie litery
// czesc nadajnika
void rysuj_interfejs(); // funkcja rysujaca glowny interfejs programu
bool wybor_przycisku(); // funkcja sprawdzajaca jaki przycisk zostal wybrany i ktora literka powinna byc wybrana
bool wyczysc_wektory(); // funkcja czyszczaca ostatnie wiadomosci
void guzik_wduszony(); // funkcja dzialania guzika
bool sprawdz_klawiature(); // funkcja sprawdzajaca wcisniety guzik i zwracajaca czy jest guzik wduszony i jaki
void dodaj_wyslana_wiadomosc();
// czesc odbiornika
void odczytaj_serial(); // funkcja odczytujaca z bufora zlacza szeregowego dane do tablicy
void wypisz_odczytana_wiadomosc(); // funkcja wypisujaca w odpowiedni sposob wiadomosc odebrana

void setup() 
{
  // ustawienie pinów:
  pinMode(connection,INPUT);
  pinMode(brzeczyk,OUTPUT);
  digitalWrite(brzeczyk,HIGH);
  pinMode(input1,OUTPUT);
  pinMode(input2,OUTPUT);
  pinMode(input3,OUTPUT);
  pinMode(input4,OUTPUT);
  analogWrite(input1,analogHIGH);
  analogWrite(input2,analogHIGH);
  analogWrite(input3,analogHIGH);
  analogWrite(input4,analogHIGH);
  pinMode(output1,INPUT_PULLUP);
  pinMode(output2,INPUT_PULLUP);
  pinMode(output3,INPUT_PULLUP);
  pinMode(output4,INPUT_PULLUP);

  // inicjalizacja
  Serial.begin(38400);
  czas_wduszenia.begin(czas_wduszenia_ms);
  wybor_guzika.begin(czas_zatwierdzenia); // zmienna czasu odczekania po wduszeniu guzika do jego zablokowania
  status_polaczenia_czas.begin(2000);
  rozlaczony.begin(500);
  mySerial.begin(38400);
  
  // wiadomosc powitalna i narysowanie rysuj_interfejsu
  lcd.begin(20, 4);
  lcd.setCursor(0,0);
  lcd.write("RADIO        RTT v.1");
  lcd.setCursor(0,1);
  lcd.write("TEXT");
  lcd.setCursor(0,2);
  lcd.write("TRANSMITER");
  lcd.setCursor(0,3);
  lcd.write("Wojciech Olszewski");
  delay(3000);
  lcd.clear();
  lcd.blink();

  // wykonania podstawowych funkcji
  rysuj_interfejs();
  wyczysc_wektory(1);

  DEBUG_PRINTLN("---URUCHOMIONO---");
}

//zmienne globalne
int linia = 1; // zmienna aktualnie wybranej lini
int czas_polaczenia = 0; // czas polaczenia
int czas_od_polaczenia = 0; // czas liczony od ostatniego stanu polaczenia
long poprzednia_linia = 1; // zmienna poprzednio wybranej lini
bool wduszony = false; // flaga wduszenia guzika
int wduszony_guzik = 0; // zmienna mowiaca ktory guzik jest wduszony
int poprzedni_wduszony_guzik = 99; // zmienna mowiaca jaki przycisk byl poprzednio wduszony
int ostatnio_wduszony_guzik = 0; // zmienna mowiaca jaki przycisk byl wduszony chwile wczesniej (jesli trzymamy guzik)
char wybrana_litera; // zmienna mowiaca wybrana litere blokowana do wyslania 
int licznik_liter = 0; // zmienna mowiaca ktory raz zostal wduszony i zablokowany jakikolwiek przycisk
int ile_razy_guzik_wduszony = 0; // zmienna mowiaca o tym ile razy zostal wduszony ten sam przycisk, przyciski maja cykl
bool blokada = true; // flaga blokujaca wybranie guzika i inkrementacje licznika
bool usuwanie = false; // flaga pozwalajaca usunac wybrana litere
char ostatnie_wiadomosci[2][21] = // tablica ostatnich 2 wiadomosci
{
  {' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' '},
  {' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' '}
}; // dodatkowy 21 znak ze wzgledu na dziwny znak na koncu po odczytaniu zostajacy w buforze
bool flaga_1_wiadomosci = 0;
bool wysylanie = false;
bool typ_ostatniej_wiadomosci = 0;  // flaga aby potrzeban do wyswietlenia informacji
int pozycja_kursora = 2; // zmienna mowiaca na jakim miejscu w pisanym tekscie jestesmy
char pisany_tekst[20] = {'T','>',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' '};
bool wiadomosc = 0; // flaga sprawdzajaca czy tablice maja zapisane w ogole jakies wiadomosci
bool potwierdzenie_polaczenia = false;
bool stan_polaczenia;
bool poprzedni_stan_polaczenia;
bool duze_litery = false;
bool wybierana_litera = false; // flaga dla wybieranej literki, zeby nie usuwac zlej i nie wysylac bez  wybranej
bool szybki_wybor_guzika = false; // flaga służąca do ignorowania czekania na wybor literki
int poprzednio_wybrany_guzik = 99; // zmienna przechowująca poprzedni wcisniety guzik, do szybkiego pisania
char poprzednio_wybrana_litera;
bool debug_flag = false;

void loop() 
{
  
  sprawdz_polaczenie();
  
  //if (mySerial.available() && !wysylanie) 
  if (mySerial.available() && !debug_flag) 
  {
    DEBUG_PRINTLN(debug_flag);
    blokada = true;
    DEBUG_PRINTLN("Odbieranie danych.....");
    aktualizuj_ostatnie_wiadomosci();
    wypisz_ostatnie_wiadomosci(false);
  
    digitalWrite(brzeczyk,LOW);
    delay(200);
    digitalWrite(brzeczyk,HIGH);
    delay(100);
    digitalWrite(brzeczyk,LOW);
    delay(200);
    digitalWrite(brzeczyk,HIGH);
    delay(100);
    digitalWrite(brzeczyk,LOW);
    delay(200);
    digitalWrite(brzeczyk,HIGH);
  }
  
  if (czas_wduszenia.available())
  {
    wybor_przycisku();
  zatwierdz_litere(false);
  }
  
}

void guzik_wduszony(int ktory_guzik) // funkcja dzialania guzika
{
  wduszony_guzik = ktory_guzik;
  if (poprzedni_wduszony_guzik == wduszony_guzik && !wduszony)
  {
    wduszony = true;
    poprzednia_linia = linia;
  }
  poprzedni_wduszony_guzik = wduszony_guzik;
  czas_wduszenia.restart();
}

bool sprawdz_klawiature()
{
  for (linia = 1;linia < 5;linia++)
  {
    if (linia == 5)
    {
      linia = 1;
    }
      
    if (linia == 1)
    {
    // wybor lini
        analogWrite(input1,LOW);
        analogWrite(input2,analogHIGH);
        analogWrite(input3,analogHIGH);
        analogWrite(input4,analogHIGH);
    
        if (analogRead(output1) < 20)
        {
          guzik_wduszony(1);
          return 1;
        }
        else if (analogRead(output2) < 20)
        {
          guzik_wduszony(2);
          return 1;
        }
        else if (analogRead(output3) < 20)
        {
          guzik_wduszony(3);
          return 1;
        }
        else if (analogRead(output4) < 20)
        {
          guzik_wduszony(4);
          return 1;
        }
        else
        {
          //poprzedni_wduszony_guzik = 0;
        }
      }
    
      else if (linia == 2)
      {
    // wybor lini
        analogWrite(input1,analogHIGH);
        analogWrite(input2,LOW);
        analogWrite(input3,analogHIGH);
        analogWrite(input4,analogHIGH);
        
        if (analogRead(output1) < 20)
        {
          guzik_wduszony(5);
          return 1;
        }
        else if (analogRead(output2) < 20)
        {
          guzik_wduszony(6);
          return 1;
        }
        else if (analogRead(output3) < 20)
        {
          guzik_wduszony(7);
          return 1;
        }
        else if (analogRead(output4) < 20)
        {
          guzik_wduszony(8);
          return 1;
        }
        else
        {
        }
      }
    else if (linia == 3)
    {
    // wybor lini
      analogWrite(input1,analogHIGH);
      analogWrite(input2,analogHIGH);
      analogWrite(input3,LOW);
      analogWrite(input4,analogHIGH);
      
      if (analogRead(output1) < 20)
      {
          guzik_wduszony(9);
        return 1;
      }
      else if (analogRead(output2) < 20)
      {
          guzik_wduszony(10);
        return 1;
      }
      else if (analogRead(output3) < 20)
      {
          guzik_wduszony(11);
        return 1;
      }
      else if (analogRead(output4) < 20)
      {
          guzik_wduszony(12);
        return 1;
      }
      else
      {
        //poprzedni_wduszony_guzik = 0;
      }
    }
    else if (linia == 4)
    {
    // wybor lini
      analogWrite(input1,analogHIGH);
      analogWrite(input2,analogHIGH);
      analogWrite(input3,analogHIGH);
      analogWrite(input4,LOW);
      
      if (analogRead(output1) < 20)
      {
          guzik_wduszony(13);
        return 1;
      }
      else if (analogRead(output2) < 20)
      {
          guzik_wduszony(14);
        return 1;
      }
      else if (analogRead(output3) < 20)
      {
          guzik_wduszony(15);
        return 1;
      }
      else if (analogRead(output4) < 20)
      {
          guzik_wduszony(16);
        return 1;
      }
    }
  }
  // jesli zaden guzik nie zostal wcisniety to:
  poprzedni_wduszony_guzik = 99;
  wduszony = false;
  return 0;
}

void rysuj_interfejs()
{
  lcd.setCursor(0,2);
  lcd.print("____________________");
  lcd.setCursor(0,3);
  lcd.print("T>                  ");
  lcd.setCursor(2,3);
}

bool wybor_przycisku()
{
  if (sprawdz_klawiature() && poprzedni_wduszony_guzik == wduszony_guzik && !wduszony)
    {
      dzwiek_klawiatury();
      blokada = false;
      
      if (!wybierana_litera)
      {
        switch(wduszony_guzik)
        {
          case 4:  // przycisk A - wyslij
            {
      if (licznik_liter > 0)
      {
        mySerial.write(wiadomosc_do_wyslania);
              dodaj_wyslana_wiadomosc();
              //DEBUG_PRINT("Wysylana wiadomosc: \"");
              for (int i = 0;i < 18;i++)
              {
                //DEBUG_PRINT(wiadomosc_do_wyslania[i]);
                wiadomosc_do_wyslania[i] = ' ';
              } 
              //DEBUG_PRINTLN("\"");
              //DEBUG_PRINT(ile_razy_guzik_wduszony);
              licznik_liter = 0;
              rysuj_interfejs();
              //lcd.setCursor(licznik_liter+2,3);
              blokada = true;
              pozycja_kursora = 2;
        poprzednio_wybrany_guzik = 99;
      }
      else
      {
              blokada = true;
        informacja(7);
      }
              return 0;
              break;
            }
            case 8:  // przycisk B
            {  
              
              if (licznik_liter)
              {
                licznik_liter = licznik_liter - 1;
                wiadomosc_do_wyslania[licznik_liter] = ' ';
                usuwanie = true;
                lcd.setCursor(licznik_liter+2,3);
                lcd.print(' ');
              }
              else if (licznik_liter == 0)
              {
                wiadomosc_do_wyslania[licznik_liter] = ' ';
                usuwanie = true;
        poprzednio_wybrany_guzik = 99;
              }
              DEBUG_PRINT('?');
              DEBUG_PRINT(licznik_liter);
              DEBUG_PRINT('?');  
              pozycja_kursora--; 
              return 0;
              break;
            }
          case 12: // przycisk C
          {
            status_polaczenia();
            blokada = true;
            return 0;
            break;
          }
          case 16:  // przycisk D
          {
            blokada = true;
            wyczysc_wektory(0);
            return 0;
            break;
          }
          case 13:  // przycisk *
          {
            if (duze_litery)
            {
              informacja(4);
              duze_litery = false;
            }
            else
            {
              duze_litery = true;
              informacja(5);
            }
            blokada = true;
            return 0;
            break;
          }
          case 15:  // przycisk #
          {
           if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = '.';
              DEBUG_PRINT('.');
              lcd.print('.');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = ',';
                DEBUG_PRINT(',');
              lcd.print(',');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = '!';
              DEBUG_PRINT('!');
              lcd.print('!');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '?';
                DEBUG_PRINT('?');
              lcd.print('?');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
      else if (ile_razy_guzik_wduszony == 4)
            {
              wybrana_litera = '-';
                DEBUG_PRINT('-');
              lcd.print('-');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
      else if (ile_razy_guzik_wduszony == 5)
            {
              wybrana_litera = '(';
                DEBUG_PRINT('(');
              lcd.print('(');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
      else if (ile_razy_guzik_wduszony == 6)
            {
              wybrana_litera = ')';
                DEBUG_PRINT(')');
              lcd.print(')');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
      else if (ile_razy_guzik_wduszony == 7)
            {
              wybrana_litera = ':';
                DEBUG_PRINT(':');
              lcd.print(':');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
      else if (ile_razy_guzik_wduszony == 8)
            {
              wybrana_litera = ';';
                DEBUG_PRINT(';');
              lcd.print(';');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
      else if (ile_razy_guzik_wduszony == 9)
            {
              wybrana_litera = '-';
                DEBUG_PRINT('-');
              lcd.print('-');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 10)
            {
              wybrana_litera = '"';
                DEBUG_PRINT('"');
              lcd.print('"');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
        }
      }
      
     
    if (poprzednio_wybrany_guzik != 99 && poprzednio_wybrany_guzik != wduszony_guzik && wybierana_litera)
        {
          szybki_wybor_guzika = true;
          zatwierdz_litere(true);
        } 
        wybierana_litera = true;
        poprzednio_wybrany_guzik = wduszony_guzik;
        szybki_wybor_guzika = false;
  
      if (!duze_litery)
      {
        if (licznik_liter < 18)
      {
      
        
      switch(wduszony_guzik)
      {
      
      case 11:  // przycisk 9
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'y';
              DEBUG_PRINT('y');
              lcd.print('y');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'z';
                DEBUG_PRINT('z');
              lcd.print('z');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = '9';
                DEBUG_PRINT('9');
              lcd.print('9');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      case 7:  // przycisk 6
          {
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'p';
              DEBUG_PRINT('p');
              lcd.print('p');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'q';
                DEBUG_PRINT('q');
              lcd.print('q');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'r';
                DEBUG_PRINT('r');
              lcd.print('r');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '6';
                DEBUG_PRINT('6');
              lcd.print('6');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            DEBUG_PRINT(licznik_liter);
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          case 3:  // przycisk 3
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'g';
              DEBUG_PRINT('g');
              lcd.print('g');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'h';
                DEBUG_PRINT('h');
              lcd.print('h');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'i';
                DEBUG_PRINT('i');
              lcd.print('i');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '3';
                DEBUG_PRINT('3');
              lcd.print('3');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          case 14:  // przycisk 0
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = ' ';
              DEBUG_PRINT(' ');
              lcd.print(' ');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = '.';
                DEBUG_PRINT('.');
              lcd.print('.');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = ',';
                DEBUG_PRINT(',');
              lcd.print(',');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          case 10:  // przycisk 8
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'w';
              DEBUG_PRINT('w');
              lcd.print('w');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'v';
                DEBUG_PRINT('v');
              lcd.print('v');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'x';
                DEBUG_PRINT('x');
              lcd.print('x');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            } 
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '8';
                DEBUG_PRINT('8');
              lcd.print('8');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      case 6:  // przycisk 5
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'm';
              DEBUG_PRINT('m');
              lcd.print('m');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'n';
                DEBUG_PRINT('n');
              lcd.print('n');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'o';
                DEBUG_PRINT('o');
              lcd.print('o');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '5';
                DEBUG_PRINT('5');
              lcd.print('5');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          case 2:  // przycisk 2
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'd';
              DEBUG_PRINT('d');
              lcd.print('d');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'e';
                DEBUG_PRINT('e');
              lcd.print('e');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'f';
                DEBUG_PRINT('f');
              lcd.print('f');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '2';
                DEBUG_PRINT('2');
              lcd.print('2');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          case 9:  // przycisk 7
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 's';
              DEBUG_PRINT('s');
              lcd.print('s');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 't';
                DEBUG_PRINT('t');
              lcd.print('t');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'u';
                DEBUG_PRINT('u');
              lcd.print('u');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '7';
                DEBUG_PRINT('7');
              lcd.print('7');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      case 5:  // przycisk 4
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'j';
              DEBUG_PRINT('j');
              lcd.print('j');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'k';
                DEBUG_PRINT('k');
              lcd.print('k');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'l';
              DEBUG_PRINT('l');
              lcd.print('l');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '4';
              DEBUG_PRINT('4');
              lcd.print('4');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      case 1:  // przycisk 1
          {
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'a';
              DEBUG_PRINT('a');
              lcd.print('a');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'b';
                DEBUG_PRINT('b');
              lcd.print('b');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'c';
                DEBUG_PRINT('c');
              lcd.print('c');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '1';
                DEBUG_PRINT('1');
              lcd.print('1');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      }
        
    poprzednio_wybrana_litera = wybrana_litera;
        }
        else
          informacja(1);
      }
      else
      {
      if (licznik_liter < 18)
      {
      switch(wduszony_guzik)
      {
      
      case 11:  // przycisk 9
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'Y';
              DEBUG_PRINT('Y');
              lcd.print('Y');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'Z';
                DEBUG_PRINT('Z');
              lcd.print('Z');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = '9';
                DEBUG_PRINT('9');
              lcd.print('9');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      case 7:  // przycisk 6
          {
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'P';
              DEBUG_PRINT('P');
              lcd.print('P');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'Q';
                DEBUG_PRINT('Q');
              lcd.print('Q');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'R';
                DEBUG_PRINT('R');
              lcd.print('R');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '6';
                DEBUG_PRINT('6');
              lcd.print('6');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            DEBUG_PRINT(licznik_liter);
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          case 3:  // przycisk 3
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'G';
              DEBUG_PRINT('G');
              lcd.print('G');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'H';
                DEBUG_PRINT('H');
              lcd.print('H');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'I';
                DEBUG_PRINT('I');
              lcd.print('I');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '3';
                DEBUG_PRINT('3');
              lcd.print('3');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          case 14:  // przycisk 0
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = ' ';
              DEBUG_PRINT(' ');
              lcd.print(' ');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = '.';
                DEBUG_PRINT('.');
              lcd.print('.');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = ',';
                DEBUG_PRINT(',');
              lcd.print(',');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
      else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '0';
                DEBUG_PRINT('0');
              lcd.print('0');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          case 10:  // przycisk 8
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'W';
              DEBUG_PRINT('W');
              lcd.print('W');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'V';
                DEBUG_PRINT('V');
              lcd.print('V');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'X';
                DEBUG_PRINT('X');
              lcd.print('X');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            } 
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '8';
                DEBUG_PRINT('8');
              lcd.print('8');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      case 6:  // przycisk 5
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'M';
              DEBUG_PRINT('M');
              lcd.print('M');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'N';
                DEBUG_PRINT('N');
              lcd.print('N');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'O';
                DEBUG_PRINT('O');
              lcd.print('O');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '5';
                DEBUG_PRINT('5');
              lcd.print('5');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          case 2:  // przycisk 2
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'D';
              DEBUG_PRINT('D');
              lcd.print('D');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'E';
                DEBUG_PRINT('E');
              lcd.print('E');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'F';
                DEBUG_PRINT('F');
              lcd.print('F');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '2';
                DEBUG_PRINT('2');
              lcd.print('2');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
          
          case 9:  // przycisk 7
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'S';
              DEBUG_PRINT('S');
              lcd.print('S');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'T';
                DEBUG_PRINT('T');
              lcd.print('T');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'U';
                DEBUG_PRINT('U');
              lcd.print('U');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '7';
                DEBUG_PRINT('7');
              lcd.print('7');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      case 5:  // przycisk 4
          {
           
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'J';
              DEBUG_PRINT('J');
              lcd.print('J');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'K';
                DEBUG_PRINT('K');
              lcd.print('K');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'L';
              DEBUG_PRINT('L');
              lcd.print('L');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '4';
              DEBUG_PRINT('4');
              lcd.print('4');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      case 1:  // przycisk 1
          {
            if (ile_razy_guzik_wduszony == 0)
            {
              wybrana_litera = 'A';
              DEBUG_PRINT('A');
              lcd.print('A');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 1)
            {
              wybrana_litera = 'B';
                DEBUG_PRINT('B');
              lcd.print('B');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 2)
            {
              wybrana_litera = 'C';
                DEBUG_PRINT('C');
              lcd.print('C');
              ile_razy_guzik_wduszony++;
              wybor_guzika.restart();
            }
            else if (ile_razy_guzik_wduszony == 3)
            {
              wybrana_litera = '1';
                DEBUG_PRINT('1');
              lcd.print('1');
              ile_razy_guzik_wduszony = 0;
              wybor_guzika.restart();
            }
            lcd.setCursor(licznik_liter+2,3); // kursor automatycznie przeskakuje na 1 miejsce dalej
            break;
          }
      }
        
        }
        else
          informacja(1);
      }
    }
}

bool wyczysc_wektory(bool opcja)
{
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("No messages");
  for (int i = 0;i < 21;i++)
  { 
    ostatnie_wiadomosci[0][i] = ' ';
    ostatnie_wiadomosci[1][i] = ' ';
  }
  for (int i = 0;i < 18;i++)
  {
    wiadomosc_do_wyslania[i] = ' ';
  } 
  rysuj_interfejs();
  flaga_1_wiadomosci = false;
  wiadomosc = false;
  if (!opcja)
    informacja(6);
  pozycja_kursora = 2;
  licznik_liter = 0;
}

void wypisz_odczytana_wiadomosc()
{
  if (flaga_1_wiadomosci)
    {
      DEBUG_PRINTLN("Przepisanie wartosci z 1 lini do 2 lini");

      aktualizuj_ostatnie_wiadomosci();
      
      DEBUG_PRINT("1 znak 2 lini: ");
      DEBUG_PRINTLN(ostatnie_wiadomosci[1][0]);

      odczytaj_serial(); // funkcja odczytujaca znaki z seriala
      
      wypisz_ostatnie_wiadomosci(false);
    }
    else
    {
      odczytaj_serial(); // funkcja odczytujaca znaki z seriala
      
      lcd.setCursor(0,0);
      ostatnie_wiadomosci[0][0] = 'R';
      ostatnie_wiadomosci[0][1] = '>';
      
      DEBUG_PRINTLN("Zawartosc 1 lini:");
      for (int i = 0;i < 20;i++)
      {
        DEBUG_PRINT(ostatnie_wiadomosci[0][i]);
        lcd.write(ostatnie_wiadomosci[0][i]);
      }
      
      DEBUG_PRINTLN();
      
      flaga_1_wiadomosci = true;
    }
    rysuj_interfejs(); // funkcja wypisujaca od nowa T>
}

void odczytaj_serial()
{
  DEBUG_PRINTLN("Odczytuje dane z seriala");
  int i = 2;
  while(mySerial.available() && i <= 20) 
  {
    ostatnie_wiadomosci[0][i] = mySerial.read();
    delay(30);
    i++;
  }
  mySerial.read();
}

void aktualizuj_ostatnie_wiadomosci()
{
  DEBUG_PRINTLN();
    
    DEBUG_PRINTLN("Aktualizacja wektorow ostatnie_wiadomosci, zawartosc wektorow: ");
    DEBUG_PRINT("1 wektor: \"");
    for (int i = 0;i < 20;i++)
    {
      DEBUG_PRINT(ostatnie_wiadomosci[0][i]);
    }
    DEBUG_PRINT("\"");
    DEBUG_PRINT(", 2 wektor: \"");
    for (int i = 0;i < 20;i++)
    {
      ostatnie_wiadomosci[1][i] = ostatnie_wiadomosci[0][i];
      DEBUG_PRINT(ostatnie_wiadomosci[1][i]);
    }
    DEBUG_PRINTLN("\"");
  
  
  
  
}

void dodaj_wyslana_wiadomosc()
{
  aktualizuj_ostatnie_wiadomosci();
  
  DEBUG_PRINT("Najnowsza wiadomosc: \"");
  for (int i = 2;i < 20;i++)
  {
    ostatnie_wiadomosci[0][i] = wiadomosc_do_wyslania[i-2];
    DEBUG_PRINT(ostatnie_wiadomosci[0][i]);
  }
  DEBUG_PRINTLN("\"");
  wypisz_ostatnie_wiadomosci(true);
}

void wypisz_ostatnie_wiadomosci(bool wyslana_czy_odebrana) // 0 dla odebranej, 1 dla wyslanej
{
  wiadomosc = true;
  lcd.setCursor(0,0);
  if (wyslana_czy_odebrana)
  {
    ostatnie_wiadomosci[0][0] = 'S';
    DEBUG_PRINTLN("Wiadomosc wyslana");
  }
  else
  {
    ostatnie_wiadomosci[0][0] = 'R';
    DEBUG_PRINTLN("Wiadomosc odebrana");
  }
  if (flaga_1_wiadomosci)
    {
      DEBUG_PRINTLN("Nie pierwsza wiadomosc");
      

      if (!wyslana_czy_odebrana)
      {
        odczytaj_serial(); // funkcja odczytujaca znaki z seriala
      }
      
      ostatnie_wiadomosci[0][1] = '>';
      
      DEBUG_PRINT("Zawartosc 1 lini: \"");
      for (int i = 0;i < 20;i++)
      {
        DEBUG_PRINT(ostatnie_wiadomosci[0][i]);
        lcd.write(ostatnie_wiadomosci[0][i]);
      }
      
      DEBUG_PRINTLN("\"");
      
      lcd.setCursor(0,1);
      ostatnie_wiadomosci[1][1] = '>';
      
      DEBUG_PRINT("Zawartosc 2 lini: \"");
      for (int i = 0;i < 20;i++)
      {
        DEBUG_PRINT(ostatnie_wiadomosci[1][i]);
        lcd.write(ostatnie_wiadomosci[1][i]);
      }
      
      DEBUG_PRINTLN("\"");
    }
    else
    {
      DEBUG_PRINTLN("Pierwsza wiadomosc");
      
      if (!wyslana_czy_odebrana)
         odczytaj_serial(); // funkcja odczytujaca znaki z seriala
      
      lcd.setCursor(0,0);
      ostatnie_wiadomosci[0][1] = '>';
  
      DEBUG_PRINT("Zawartosc 1 lini: \"");
      for (int i = 0;i < 20;i++)
      {
        DEBUG_PRINT(ostatnie_wiadomosci[0][i]);
        lcd.write(ostatnie_wiadomosci[0][i]);
      }
      
      DEBUG_PRINTLN("\"");
      
      flaga_1_wiadomosci = true;
    }
    
    rysuj_interfejs(); // funkcja wypisujaca od nowa T>
    lcd.setCursor(2,3);
      lcd.write(wiadomosc_do_wyslania);
    wypisz();
      lcd.setCursor(pozycja_kursora,3);
    DEBUG_PRINTLN("-------------------------------------------------------------------------------------------");
  if (ostatnie_wiadomosci[0][2] == 'y' && ostatnie_wiadomosci[0][3] == 'y' && ostatnie_wiadomosci[0][4] =='x' && ostatnie_wiadomosci[0][5] =='x')
  {
    wyczysc_wektory(1);
    debug_flag = true;
  }
}
void status_polaczenia()
{
  status_polaczenia_czas.restart();
  while(!status_polaczenia_czas.available())
  {
    if (wybor_guzika.available())
    {
      lcd.clear();
      lcd.setCursor(0,0);
      lcd.write("Connection time :");
      lcd.setCursor(0,2);
      lcd.print(millis()/1000 - czas_od_polaczenia/1000);
      lcd.write(" sec");
      wybor_guzika.restart();
    }
    
  }
  
  DEBUG_PRINT("Czas polaczenia w sekundach: ");
  DEBUG_PRINTLN(millis()/1000 - czas_od_polaczenia/1000);
  lcd.clear();
  rysuj_interfejs();
  lcd.setCursor(2,3);
  lcd.write(wiadomosc_do_wyslania);
  wypisz();
  lcd.setCursor(pozycja_kursora,3);
}

void dzwiek_klawiatury()
{
    digitalWrite(brzeczyk,LOW);
    delay(200);
    digitalWrite(brzeczyk,HIGH);
}

void informacja(int ktora_informacja)
{
  DEBUG_PRINTLN();
  for (int i = 2; i < 20;i++)
  {
    pisany_tekst[i] = wiadomosc_do_wyslania[i-2];
    //DEBUG_PRINTLN(wiadomosc_do_wyslania[i-2]);
  }

  lcd.setCursor(0,0);
  lcd.write("                     ");
  lcd.setCursor(0,2);
  lcd.write("                     ");
  lcd.setCursor(0,3);
  lcd.write("                     ");
  switch(ktora_informacja)
  {
    case 1:
    {
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
    delay(50);
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
      DEBUG_PRINT("[LIMIT ZNAKOW]");
      lcd.setCursor(0,1);
      lcd.write("  CHARACTER LIMIT  ");
      delay(1000);
      lcd.clear();
      rysuj_interfejs();
      lcd.setCursor(2,3);
      lcd.write(wiadomosc_do_wyslania);
      wypisz();
      lcd.setCursor(pozycja_kursora,3);
      wybierana_litera = false;
      break;
    }
    case 2:
    {
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
    delay(50);
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
      DEBUG_PRINTLN("[POLACZONO]");
      lcd.setCursor(0,1);
      lcd.write("      CONNECTED     ");
      delay(500);
      lcd.clear();
      rysuj_interfejs();
      lcd.setCursor(2,3);
      lcd.write(wiadomosc_do_wyslania);
      wypisz();
      lcd.setCursor(pozycja_kursora,3);
      lcd.blink();
      break;
    }
    case 3:
    {
    rozlaczony.restart();
    while(!rozlaczony.available())
    {
      DEBUG_PRINTLN("[ROZLACZONO]");
      lcd.setCursor(0,1);
      lcd.write("  CONNECTION LOST  ");
      lcd.setCursor(0,2);
      lcd.write("DEVICES OUT OF RANGE");
      lcd.noBlink();
      
    }
      break;
    }
    case 4:
    {
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
    delay(50);
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
      DEBUG_PRINTLN("[MALE LITERY]");
      lcd.setCursor(0,1);
      lcd.write("        abc       ");
      delay(500);
      lcd.clear();
      rysuj_interfejs();
      lcd.setCursor(2,3);
      lcd.write(wiadomosc_do_wyslania);
      wypisz();
      lcd.setCursor(pozycja_kursora,3);
      lcd.blink();
      break;
    }
    case 5:
    {
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
    delay(50);
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
      DEBUG_PRINTLN("[DUZE LITERY]");
      lcd.setCursor(0,1);
      lcd.write("        ABC       ");
      delay(500);
      lcd.clear();
      rysuj_interfejs();
      lcd.setCursor(2,3);
      lcd.write(wiadomosc_do_wyslania);
      wypisz();
      lcd.setCursor(pozycja_kursora,3);
      lcd.blink();
      break;
    }
    case 6:
    {
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
    delay(50);
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
      DEBUG_PRINTLN("[WYCZYSZCZONO]");
      lcd.setCursor(0,1);
      lcd.write("    LCD cleared    ");
      delay(1000);
      lcd.clear();
      rysuj_interfejs();
      lcd.setCursor(2,3);
      lcd.write(wiadomosc_do_wyslania);
      wypisz();
      lcd.setCursor(2,3);
      lcd.blink();
      break;
    }
  case 7:
    {
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
    delay(50);
    digitalWrite(brzeczyk,LOW);
    delay(50);
    digitalWrite(brzeczyk,HIGH);
      DEBUG_PRINTLN("[PUSTA WIADOMOSC]");
      lcd.setCursor(0,1);
      lcd.write("   EMPTY MESSAGE    ");
      delay(1000);
      lcd.clear();
      rysuj_interfejs();
      lcd.setCursor(2,3);
      lcd.write(wiadomosc_do_wyslania);
      wypisz();
      lcd.setCursor(2,3);
      lcd.blink();
      break;
    }
  }
}

void wypisz()
{
  lcd.setCursor(0,0);
  if (flaga_1_wiadomosci)
  {
      for (int i = 0;i < 20;i++)
      {
        lcd.write(ostatnie_wiadomosci[0][i]);
      }
      
      lcd.setCursor(0,1);
      
      for (int i = 0;i < 19;i++)
      {
        lcd.write(ostatnie_wiadomosci[1][i]);
      }
  }
  else
  {
    {
      lcd.setCursor(0,0);
  
      for (int i = 0;i < 20;i++)
      {
        lcd.write(ostatnie_wiadomosci[0][i]);
      }
    }
  }
  if (!wiadomosc)
  {
    lcd.setCursor(0,0);
    lcd.write("No messages");
  }
}

void sprawdz_polaczenie()
{
  stan_polaczenia = digitalRead(connection);
  if (stan_polaczenia == poprzedni_stan_polaczenia && stan_polaczenia == 1 && !potwierdzenie_polaczenia)
  {
    informacja(2);
    potwierdzenie_polaczenia = true;
    czas_od_polaczenia = millis();
  }
  else if (stan_polaczenia == poprzedni_stan_polaczenia && stan_polaczenia == 0)
  {
    informacja(3);
    czas_od_polaczenia = 0;
  }
  else if (stan_polaczenia != poprzedni_stan_polaczenia && stan_polaczenia == 1 && poprzedni_stan_polaczenia == 0)
  {
    potwierdzenie_polaczenia = false;
  }
  poprzedni_stan_polaczenia = stan_polaczenia;
}

void zatwierdz_litere(bool tryb)
{
  if (tryb)
  {
    if (!blokada && licznik_liter < 18)
    {
      DEBUG_PRINT(">");
      DEBUG_PRINT(wybrana_litera);
      DEBUG_PRINT("<");
      
      if (!usuwanie)
      {
        if (licznik_liter == 0)
        wiadomosc_do_wyslania[licznik_liter] = wybrana_litera;
        else
        wiadomosc_do_wyslania[licznik_liter] = poprzednio_wybrana_litera;
      licznik_liter++;
      }

      DEBUG_PRINT('|');
      DEBUG_PRINT(licznik_liter);
      DEBUG_PRINT('|');
      ile_razy_guzik_wduszony = 0;
      blokada = true;
      usuwanie = false;

      if (licznik_liter < 18)
      {
      lcd.setCursor(licznik_liter+2,3); // ustawienei kursora na 3 miejsce
      pozycja_kursora = licznik_liter+2;
      }
      
      else
      {
        lcd.setCursor(licznik_liter+1,3); // ustawienei kursora na 3 miejsce
        pozycja_kursora = licznik_liter+1;
      }
      
      DEBUG_PRINTLN();
    wybor_guzika.restart();
    }
    blokada = false;
  }
  else if(wybor_guzika.available() && !blokada && licznik_liter < 18 && !tryb )
    {
      DEBUG_PRINT(">");
      DEBUG_PRINT(wybrana_litera);
      DEBUG_PRINT("<");
      
      if (!usuwanie)
      {
        wiadomosc_do_wyslania[licznik_liter] = wybrana_litera;
        licznik_liter++;
      }

      DEBUG_PRINT('|');
      DEBUG_PRINT(licznik_liter);
      DEBUG_PRINT('|');
      ile_razy_guzik_wduszony = 0;
      blokada = true;
      usuwanie = false;

      if (licznik_liter < 18)
      {
        lcd.setCursor(licznik_liter+2,3); // ustawienei kursora na 3 miejsce
        pozycja_kursora = licznik_liter+2;
      }
        
        else
        {
          lcd.setCursor(licznik_liter+1,3); // ustawienei kursora na 3 miejsce
          pozycja_kursora = licznik_liter+1;
        }
        
        DEBUG_PRINTLN();
    wybierana_litera = false;
  }
}

