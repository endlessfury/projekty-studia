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
		if (isset($_POST['username']) and isset($_POST['password']) and isset($_POST['password_2']) and $_POST['password'] == $_POST['password_2'] and isset($_POST['addUser']) and isset($_POST['permissions']))
		{
			$login = $_POST['username'];
			$haslo = hash( 'sha256', $_POST['password']);
			$permissions = $_POST['permissions'];

			if ($result = @$polaczenie->query(
			sprintf('SELECT * FROM users WHERE `user_login`="%s"',
			mysqli_real_escape_string($polaczenie,$login))))
			{
				$users = $result->num_rows;
				if($users == 0)
				{
					$result = @$polaczenie->query('INSERT INTO `users` (`user_id`, `user_login`, `user_password`, `user_permission`, `user_last_login`) VALUES (NULL, "'.$login.'", "'.$haslo.'","'.$permissions.'","never")');
					$_SESSION['error'] = '<span style="color:darkorange; font-size: 17px;" >Dodano użytkownika!</span><br><br>';
				} 
				else
					$_SESSION['error'] = '<span style="color:darkorange; font-size: 17px;" >Użytkownik istnieje!</span><br><br>';
				
			}
		}
		else if (isset($_POST['username']) and isset($_POST['password']) and isset($_POST['password_2']) and $_POST['password'] != $_POST['password_2'] and isset($_POST['addUser']) and isset($_POST['permissions']))
			$_SESSION['error'] = '<span style="color:darkorange; font-size: 17px;" >Hasła nie są takie same!</span><br><br>';
		else if (isset($_POST['username']) and isset($_POST['blockUser']))
		{
			$login = $_POST['username'];

			if ($result = @$polaczenie->query(
			sprintf('SELECT * FROM users WHERE `user_login`="%s" AND `user_blocked` = "0"',
			mysqli_real_escape_string($polaczenie,$login))))
			{
				$users = $result->num_rows;
				if($users == 1)
				{
					unset($_SESSION['error']);
					$result = @$polaczenie->query('UPDATE `users` SET `user_blocked` = "1" WHERE `users`.`user_login` = "'.$login.'"');
					$_SESSION['info'] = '<span style="color:darkorange; font-size: 17px;" >Zablokowano użytkownika!</span><br><br>';
					
				} 
			}
		}
		else if (isset($_POST['username']) and isset($_POST['changePassword']) and isset($_POST['password']) and isset($_POST['password_2']) and $_POST['password'] == $_POST['password_2'])
		{
			$login = $_POST['username'];
			$haslo = hash( 'sha256', $_POST['password']);

			if ($result = @$polaczenie->query(
			sprintf('SELECT * FROM users WHERE `user_login`="%s" AND `user_blocked` = "0"',
			mysqli_real_escape_string($polaczenie,$login))))
			{
				$users = $result->num_rows;
				if($users == 1)
				{
					unset($_SESSION['error']);
					$result = @$polaczenie->query('UPDATE `users` SET `user_password` = "'.$haslo.'" WHERE `users`.`user_login` = "'.$login.'"');
					$_SESSION['error'] = '<span style="color:darkorange; font-size: 17px;" >Zmieniono hasło!</span><br><br>';
				} 
			}
		}
		else if (isset($_POST['username']) and isset($_POST['password']) and isset($_POST['password_2']) and $_POST['password'] != $_POST['password_2'] and isset($_POST['changePassword']))
			$_SESSION['error'] = '<span style="color:darkorange; font-size: 17px;" >Hasła nie są takie same!</span><br><br>';
		else if (isset($_POST['username']) and isset($_POST['permissions']))
		{
			echo isset($_POST['username']) . isset($_POST['permissions']);
			$login = $_POST['username'];
			$permission = $_POST['permissions'];
			$result = @$polaczenie->query('UPDATE `users` SET `user_blocked` = "0", `user_permission` = "'.$permission.'" WHERE `users`.`user_login` = "'.$login.'"');
			$_SESSION['info'] = '<span style="color:darkorange; font-size: 17px;" >Zmieniono uprawnienia / odblokowano użytkownika!</span><br><br>';
		}

		$polaczenie->close();
	}
	header('Location: config.php#userMgmt');
	
?>