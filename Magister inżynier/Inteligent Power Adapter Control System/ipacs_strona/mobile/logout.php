<?php
	require_once 'session_login.php';
	
	session_unset();
	
	header('Location: login.php');

?>