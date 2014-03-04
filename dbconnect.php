<?php

	require_once("config.php");
	$db = new mysqli($dbHost,  $dbUser,  $dbPassword, $dbName);
	if ($db->connect_error) 
		die('Error connecting to the db: Connect Error (' . $db->connect_errno . ') ' . $db->connect_error);

?>