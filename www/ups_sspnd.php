<?php
		
	$upsnm = $_GET['upsnm'];
	
	$mndb2 = new mysqli('localhost', 'root', 'mypass', 'ups_list'); //

	if (!$mndb2) {
		die('Could not connect: ' . mysqli_error());
	}
	
	if ($result = $mndb2->query("RENAME TABLE `ups_list`.`$upsnm` TO `suspndd_upss`.`$upsnm`",  MYSQLI_USE_RESULT)){ //`
		
		echo "Operation code: $result <br>";
		echo "UPS \"$upsnm\" monitoring and data suspended";
		echo "<br>Going back to main page...";
	} else {
		echo "Operation code: $result <br>";
		echo "error"; 
	}
		
	//mysqli_free_result($result);
	mysqli_close($mndb2);
	
	if ($result == 1) {echo "<meta http-equiv=\"refresh\" content=\"2; url=http://localhost/index_ups.php\">";}
	
?>