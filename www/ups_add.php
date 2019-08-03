<?php
	
	//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	
	//echo "alive<br>";
	
	$nwups = $_GET['newups'];
	
	//echo "$nwups";
	
	$mndb2 = new mysqli('localhost', 'root', 'mypass', 'ups_list'); //

	if (!$mndb2) {
		die('Could not connect: ' . mysqli_error());
	}
	
	if ($result = $mndb2->query("CREATE TABLE IF NOT EXISTS `$nwups` LIKE srvc.main", MYSQLI_USE_RESULT)){ //`
		
		echo "$result";
		echo "Network UPS added";
	} else {
		echo "$result";
		echo "error2"; 
	}
	
	if ($result == 1) {echo "<meta http-equiv=\"refresh\" content=\"2; url=http://localhost/index_ups.php\">";}
	
	mysqli_free_result($result);
	mysqli_close($mndb2);
	
?>