<?php
	ini_set('display-errors',1);
	echo 'here in index.php';
	$host = 'localhost';
	$user = 'root';#'mysql_dba';
	$password = '';// '7~6%{ipu[n1w';
	$db = 'onec_user_info';
	echo 'before connection';
	$conn = new mysqli($host,$user,$password,$db);
	echo 'after connection';
	if($conn->connect_error){
		echo 'Connection failed'. $conn->connect_error;
	}
	
	echo 'YES! Successfully connected to MYSQL: PWD is: '.realpath(__DIR__);
?>
