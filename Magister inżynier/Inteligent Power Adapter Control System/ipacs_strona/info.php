<?php
    require_once 'session_login.php';
?>
<body>
    <div id="container">
        <!--Listwa 1 szczegóły-->
        <div class="panel">
            <div class="subPanelTop">
                <div style="float:left; margin-left: 10px; position: fixed;"><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>" class="goBack" style="line-height: 20px">Powrót</a></div>
                Tutaj dowiesz się trochę o projekcie
            </div>
        </div>
        <div class="panel">
            <div class="subPanelTop">
                Autor
            </div>
            <div class="subPanelDetails">
                <center><div style="float: left; width: 70%;"><br><br>Autorem projektu jest:<br>
                inż. Wojciech Olszewski<br>
                Absolwent wydziału Elektroniki i Telekomunikacji</div><br>
                <div style="background-image: url('PP.gif');display:block;float: right; background-size: 100%; background-repeat: no-repeat;width: 240px; height: 300px;"></div>
                <div style="float: left; width: 70%;"><br><br>Skontaktuj się ze mną!<br><a href="http://facebook.com/wojtusolszewski" style="background-image:url('fb.png');width: 100px; height: 50px;display: block;"></a><a title="eriksonekxd@gmail.com" href="mailto:eriksonekxd@gmail.com" style="background-image:url('gmail.png');width: 50px; height: 50px;display: block;"></a></div></center>
            </div>
        </div>
        <div class="panel">
            <div class="subPanelTop">
                Strzeszczenie i cel projektu
            </div>
            <div class="subPanelDetails">
            Tematem pracy jest System Inteligentnego Sterowania Listwami Zasilania. Określenie inteligentny należy rozumieć tutaj poprzez automatyzację procesu działania systemu i interakcji systemu z użytkownikiem. Projekt polega na zbudowaniu systemu do zdalnej kontroli gniazd w listwach przedłużaczy 230V. Dodatkowo w każdym z gniazd użyte będą czujniki, aby można było gniazda sterować za ich pomocą. Sterowanie odbywać się będzie poprzez stronę WWW zabezpieczoną domową siecią lokalną oraz panelem logowania z uprawnieniami. Na stronie będzie można załączać i wyłączać gniazda, ustawiać automatyczne zadania oraz podejrzeć zapisy z dziennika zmian stanów. Dostępny będzie panel konfiguracyjny do wprowadzania zmian w systemie oraz szczegółowe dane z czujników w postaci czasowych wykresów z wyborem przedziału czasu. 
            <br><br>        
            Celem projektu natomiast jest wprowadzenie do domu możliwości zarządzania zdalnego pewnymi urządzeniami, oznacza to jego automatyzację. System pozwoli także na kontrolę temperatury w okolicach listw zasilających oraz sprawdzenie jak zmieniało się natężenie światła w ciągu np. ostatniej doby. Zadania automatyczne mają zwolnić użytkownika z włączania światłą w momencie, gdy się robi ciemno w pomieszczeniu bądź poza lub uruchamiania grzejnika elektrycznego w momencie, gdy temperatura spadnie poniżej zadanego poziomu.  
            </div>
        </div>
        <div class="panel">
            <div class="subPanelTop">
                Opis systemu
            </div>
            <div class="subPanelDetails">
                Sama strona bazuja na szkielecie napisanym w języku HTML. Szkielet jest wzbogacony o interpretowany skryptowy język programowania PHP, który pozwala na dynamiczne wyświetlanie danych z bazy danych. Do obsługi zapytań do bazy danych używany jest język SQL, baza z kolei to MariaDB (odmiana MySQL). Wyglądem strony zarządza język CSS. Dodatkowo wymagana było dodanie paru skryptów, które zostały napisane w języku JavaScript. 
                <br><br>
                Program do obsługi systemu napisany został w całości w języku C++. Używane w nim są biblioteki do obłusig protokołu transmisji SPI (komunikacja z modułem bezprzewodowym NRF24) oraz obsługi bazy danych MariaDB, a za tym idzie język SQL. 
                <br><br>
                System oferuje kilka funkcji:
                <ul>
                    <li><a href="#gniazda" class="infoLink">Sterowanie każdym gniazdem zasilania na listwie z osobna</a></li>
                    <li><a href="#podglad" class="infoLink">Podgląd kto, kiedy włączył/wyłączył dane gniazdo</a></li>
                    <li><a href="#zadania" class="infoLink">Menadźer zadań automatycznych dla każdej listwy z osobna</a></li>
                    <li><a href="#dane" class="infoLink">Wizualizacja danych z czujników</a></li>
                    <li><a href="#panel" class="infoLink">Panel konfiguracyjny do wporwadzania zmian w systemie</a></li>
                    <li><a href="#logowanie" class="infoLink">Panel logowania z uprawnieniami</a></li>
                </ul>
            </div>
        </div>
        <div class="panel">
            <div class="subPanelTop">
                Obsługa funkcji systemu
            </div>
            <div class="subPanelDetails" style="max-height: none;">
                <h2 id="gniazda">Sterowanie każdym gniazdem zasilania na listwie z osobna</h2>
                <p style="margin-left: 20px;">
                    Gniazda mogą być sterowane <b>ręcznie</b> lub <b>poprzez stronę</b>.<br><br>
                    Sterowanie ręczne polega na tym, że gniazda są uruchomone w trybie ręcznym poprzez fizyczny przełącznik na listwie. 
                    Funkcja ta jest używana w przypadku braku połączenia lub problemów w działaniu systemu. 
                    Symuluje to działanie zwykłej przewodowej listwy zasilania. <br>
                    Gdy przełącznik zostanie przełączony w momencie, gdy połączenie nie zostało zerwane, adekwatna informacja pojawi się na stronie w trzech miejscach:<br>
                    <img src="info/manual_2.jpg" width="30%" height="30%" style="margin-top: 5px;margin-bottom: 5px;""/>&nbsp;&nbsp;&nbsp;
                    <img src="info/manual_1.jpg" width="30%" height="30%"/>&nbsp;&nbsp;&nbsp;
                    <img src="info/manual_3.jpg" width="30%" height="30%"/><br>
                    W przeciwnym razie, gdy będzie problem z transmisją listwa zostanie automatycznie wyłączona z poziomu strony i informację czy listwa była przed wyłączeniem sterowana ręcznie wyświetlona zostaie w panelu konfiguracyjnym. <br><br>
                    Sterowanie z poziomu strony polega na klikaniu na nazwy gniazd poprzez duży przycisk z jej nazwą. <br>
                    Lista wszystkich (4) gniazd, które mogą być przełączone oraz ich możliwą konfigurację (w zależności od uprawnień) przedstawiają poniższe zrzuty:<br>
                    <img src="info/sockets_1.jpg" width="20%" height="20%"/>&nbsp;&nbsp;&nbsp;
                    <img src="info/sockets_2.jpg" width="20%" height="20%" style="margin-top: 5px;margin-bottom: 5px;""/>&nbsp;&nbsp;&nbsp;
                </p>
                <h2 id="podglad">Podgląd kto, kiedy włączył/wyłączył dane gniazdo</h2>
                <p style="margin-left: 20px;">
                    Każda zmiana stanu gniazda, czy to przez użytownika czy system (automatyczna) powoduje zapisanie w rejestrze informacji na ten temat.<br><br>
                    <img src="info/logs_1.jpg" width="98%" height="98%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                    Funkcja ta została zaimplementowana, by dać użytkownikowi wiedzę, o tym kto i kiedy włączył gniazdo oraz czy zadanie automatyczne wykonało się.
                </p>
                <h2 id="zadania">Menadźer zadań automatycznych dla każdej listwy z osobna</h2>
                <p style="margin-left: 20px;">
                    Menadżer zadań jest narzędziem do automatyzacji pracy systemu.<br><br>
                    Użytkownik ma możliwość zlecenia zadania w formie jednokrotnego lub powtarzalnego wykonania. 
                    <img src="info/tasks_1.jpg" width="95%" height="95%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                    Każde zadanie może być zatrzymane, aby zahibernować je do późniejszego użytkowania lub usuniętę na stałe. <br>
                    Z powodów niekomplikowania projektu, nie zaimplementowano tutaj edycji dodanego już zadania.<br>
                    W momencie, gdy użytkownik doda zadanie z warunkiem, który nie zgadza się z szablonem, zadanie zostanie zatrzymane oraz zostanie wpisany błąd do tabeli.<br>
                    <img src="info/tasks_2.jpg" width="95%" height="95%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                    Użytkownik ma wiedzę, o tym kto i kiedy zlecił zadanie oraz czy zadanie zostało dodane poprawnie.
                </p>
                <h2 id="dane">Wizualizacja danych z czujników</h2>
                <p style="margin-left: 20px;">
                    Funkcja polega na wyświetlenia danych z czujników gromadzonych w bazie danych co 15 minut na podstawie bieżących danych dostępnych aktualnie w bazie.<br><br>
                    Użytkownik ma możliwość wyboru okresu, z którego dane mają być wyświetlone.<br>
                    Dodatkowo po wyrysowaniu obliczone zostają minimalna, maksymalna oraz średnia wartość z danego okresu.<br>
                    Przykład dla temperatury:<br>
                    <img src="info/graphs_1.jpg" width="98%" height="98%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                    Szara pogrubiona linia wskazuje wartość średnią.
                </p>
                <h2 id="panel">Panel konfiguracyjny do wporwadzania zmian w systemie</h2>
                <p style="margin-left: 20px;">
                    Panel konfiguracyjny posiada możliwość zmiany podstawowych ustawień bez potrzeby ingerencji w program bądź bazę danych. <br><br>
                    Podstawową funkcją panelu jest wyświetelnie danych wprost z bazy w sposób przejrzysty.<br>
                    Kolejną funkcją dostępną dla użytkownika jest zmiana nazw poszczególnych elementów systemu oraz restart listwy.<br>
                    <img src="info/conf_1.jpg" width="98%" height="98%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                    Następnie mamy możliwość ręcznego ustawienia czasu i daty systemu oraz mocy stacji matki. 
                    Wynika to, z tego, że system może działać bez dostępu do internetu.
                    Wymaga to wtedy umożliwieniu użytkownikowi skonfigurowania tych parametrów.<br>
                    Zmiana mocy stacji matki powoduje zmiane zasięgu całego systemu. Użytkownikowi odebrano uprawnienia do sterowania mocą każdą listwą z osobna. <br>
                    <img src="info/conf_2.jpg" width="98%" height="98%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                    Ostatnim elementem panelu jest zmiana hasła użytkownika oraz czasu odświeżania strony.<br>
                    <img src="info/conf_3.jpg" width="98%" height="98%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                    
                </p>
                <h2 id="logowanie">Panel logowania z uprawnieniami</h2>
                <p style="margin-left: 20px;">
                    System przed wprowadzaniem zmian jest chroniony poprzez sieć Wi-Fi oraz panel logowanie bezpośrednio na stronie.
                    Logowanie polega na sesjach, a hasła są hashowane (kodowane) algorytmem SHA256. 
                    <img src="info/login_1.jpg" width="50%" height="50%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                    Dostęp do każdej strony jest weryfikowany poprzez sprawdzenie czy sesja zalogowania użytkownika istnieje.<br><br>
                    W systemiem zdefiniowana 3 poziomy uprawnień, różnice w uprawnieniach widać na poniższych zrzutach:
                </p>
                    <ul>
                        <li>
                            Przypadek dla "gościa":<br>
                            <img src="info/login_2.jpg" width="98%" height="98%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                        </li>
                        <li>
                            Przypadek dla "użytkownika":<br>
                            <img src="info/login_3.jpg" width="98%" height="98%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                        </li>
                        <li>
                            Przypadek dla "administratora":<br>
                            <img src="info/login_4.jpg" width="98%" height="98%" style="margin-top: 5px;margin-bottom: 5px;"/><br>
                        </li>
                    </ul>
                <p style="margin-left: 20px;"> Konto gościa ma dodatkowe ograniczenia w postaci braku możliwości przełączania gniazd.</p>
            </div>
        </div>
    </div>
</body>
</html>

