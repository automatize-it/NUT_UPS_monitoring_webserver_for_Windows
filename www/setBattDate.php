<?php
include 'nutMonSqlAuth.php';

$upsnm = $_GET['ups'];
$bttdt = $_GET['battdate'];

$mndb = nutMonSqlAuth('W');

if (!$mndb) {
	die('Could not connect: ' . mysqli_error());
}

echo "<h2>$upsnm $bttdt</h2>";

if (!$mndb) {
		die('Could not connect: ' . mysqli_error());
	}
	
	//ALTER TABLE `40051981606@s31` CHANGE `battery.mfr.date` `battery.mfr.date` DATE NULL DEFAULT NULL;
	
	if ($result = $mndb->query("ALTER TABLE `$upsnm` CHANGE `battery.mfr.date` `battery.mfr.date` DATE NULL DEFAULT '$bttdt'", MYSQLI_USE_RESULT)){
		
		echo "$result";
		echo "Battery date stored in local DB";
	} else {
		echo "$result";
		echo "Some error occured"; 
	}
	
	if ($result == 1) {echo "<meta http-equiv=\"refresh\" content=\"8; url=http://localhost/nutupsmon/index.php\">";}
	
	//mysqli_free_result($result);
	mysqli_close($mndb);

?>

<a href="index.php">back to main page</a>

