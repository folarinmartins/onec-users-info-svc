<?php
	$host = 'db'; //service name from docker-compose.yml
	$user = 'mysql_dba';
	$password = '7~6%{ipu[n1w';
	$db = 'onec_user_info';
	$conn = new mysqli($host,$user,$password,$db);
	if($conn->connect_error){
		echo 'Connection failed'. $conn->connect_error;
	}
	
	echo 'Successfully connected to MYSQL';
?>
