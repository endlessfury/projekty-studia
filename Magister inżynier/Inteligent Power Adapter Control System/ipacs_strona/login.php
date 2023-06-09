<?php

    require_once "mysql_connect.php";
    session_start();

    $polaczenie = @new mysqli($host, $db_user, $db_password, $db_name);

?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="styl.css">
    <title>IPACS © PP 2019</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="apple-touch-icon" sizes="57x57" href="/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>

<body>
    <div id="loginDiv">
        <div class="panel" style="width: 922px;margin-top: 0;padding: left: 10px;">
            <div class="subPanelTop" style="width: 900px;">
                Praca dyplomowa magisterska pt.<br>
                <font size="6">SYSTEM INTELIGENTNEGO STEROWANIA LISTWAMI ZASILANIA<br></font>
            </div>
            <div class="subPanelDetails" style="width: 706px; float: left;text-align: justify; font-size: 16px;">
                    Tematem pracy jest System Inteligentnego Sterowania Listwami Zasilania. Określenie inteligentny należy rozumieć tutaj jako automatyzację działania systemu, a więc ograniczenia interakcji systemu z użytkownikiem do minimum po pełnej automatyzacji procesu. Projekt polega na opracowaniu systemu modułów dołączanych do listw zasilania przedłużaczy 230V oraz zapewnieniu jego działania od strony programowej. W każdym z gniazd użyte będą czujniki, aby można było gniazda sterować za ich pomocą. Sterowanie odbywać się będzie poprzez stronę WWW w wersji komputerowej i mobilnej, na smartfonie, z zabezpieczoną domową siecią lokalną oraz panelem logowania z uprawnieniami. Na stronie będzie można załączać i wyłączać gniazda, ustawiać automatyczne zadania oraz sprawdzić zapisy z dziennika zmian stanów. Dostępny będzie panel konfiguracyjny do wprowadzania zmian w systemie oraz szczegółowe dane z czujników w postaci czasowych wykresów z wyborem przedziału czasu. 
                    <br><br>        
                    Celem projektu jest wprowadzenie do domu możliwości zdalnego zarządzania wybranymi urządzeniami poprzez ich automatyzację lub ręczną kontrolę. System pozwoli także na kontrolę temperatury w okolicach listw zasilających oraz sprawdzenie, jak zmieniało się natężenie światła w ciągu np. ostatniej doby. Zadania automatyczne mają zwolnić użytkownika z włączania oświetlenia w momencie, gdy w pomieszczeniu bądź na zewnątrz robi się lub uruchamiania grzejnika elektrycznego, gdy temperatura spadnie poniżej zadanego poziomu.  
            </div>
            <div id="loginDivInner">
                <center>
                <form action="logging.php" method="post">
                Login: <br /> <input placeholder="login" class="logIn" type="text" name="username"/> <br />
                Hasło: <br /> <input placeholder="hasło" class="logIn" type="password" name="userpass" /> <br />
                <?php
                    if(isset($_SESSION['error']))	
                    {
                        echo $_SESSION['error'];
                        session_unset();
                    }
                ?>
                <input type="submit" class="changeName" value="Zaloguj się" />
                <br>
                <br>
                <br>
                <a class="goBack" href="/mobile/index.php">WERSJA MOBILNA STRONY</a>
                </form>
                </center>
            </div>
        </div>
        
    
    </div>
</body>

</html>

<?php
    $mysqli->close();
?>