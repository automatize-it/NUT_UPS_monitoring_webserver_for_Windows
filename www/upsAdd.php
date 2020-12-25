<?php
	
	include 'nutMonSqlAuth.php';
	
	$nwups = $_GET['newups'];
	
	$mndb2 = nutMonSqlAuth('W');

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
	
	if ($result == 1) {echo "<meta http-equiv=\"refresh\" content=\"2; url=http://localhost/nutupsmon/index.php\">";}
	
	mysqli_free_result($result);
	mysqli_close($mndb2);
	
?>