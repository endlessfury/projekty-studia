<?php
	require_once 'session_login.php';

	require_once "mysql_connect.php";

	$polaczenie = @new mysqli($host, $db_user, $db_password, $db_name);
	
	if ($polaczenie->connect_errno!=0)
	{
		echo "Error: ".$polaczenie->connect_errno;
	}
	else
	{
		$login = $_POST['username'];
		$haslo = hash( 'sha256', $_POST['userpass']);
		
	
		if ($result = @$polaczenie->query(
		sprintf('SELECT * FROM users WHERE `user_login`="%s" AND `user_password`="%s" AND `user_blocked`="0"',
		mysqli_real_escape_string($polaczenie,$login),
		mysqli_real_escape_string($polaczenie,$haslo))))
		{
			$users = $result->num_rows;
			if($users>0)
			{
				$_SESSION['logged'] = true;
				
				$row = $result->fetch_assoc();
				$_SESSION['user_id'] = $row['user_id'];
				$_SESSION['user_login'] = $row['user_login'];
				$_SESSION['user_permission'] = $row['user_permission'];
				
				unset($_SESSION['error']);
				$result->free_result();
				session_set_cookie_params(604800,"/");
				$result = @$polaczenie->query('UPDATE `users` SET `user_last_login` = "'.date('d-m-Y').', '.date('H:i').'" WHERE `users`.`user_id` = '.$_SESSION['user_id']);
				header('Location: index.php');
				
			} else {
				//if ($row['user_new'] == '0')
					$_SESSION['error'] = '<span style="color:darkorange; font-size: 12px;" >Nieprawidłowy login lub hasło!</span><br><br>';
				/*else if ($row['user_new'] == '1')
					$_SESSION['error'] = '<span style="color:darkorange; font-size: 12px;" >Nieprawidłowy login lub hasło!<br>Konto musi zostać zatwierdzone!</span><br><br>';*/
				header('Location: login.php');
				
			}
			
		}
		
		$polaczenie->close();
	}
	
?>