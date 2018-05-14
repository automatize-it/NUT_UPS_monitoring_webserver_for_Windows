<?php
	
	//echo "alive<br>";
	
	$nwups = $_GET['newups'];
	
	//echo "$nwups";
	
	$mndb2 = new mysqli('localhost', 'root', 'password', 'ups_list');

	if (!$mndb2) {
		die('Could not connect: ' . mysqli_error());
	}
		
	if ($result = $mndb2->query("CREATE TABLE IF NOT EXISTS `$nwups` LIKE main")){
		
		echo "$result";
		echo "Network UPS added";
	} else {
		echo "error"; 
	}
	
?>