<?php

include 'nutMonSqlAuth.php';

$upsnm = $_GET['upsdb'];

$mndb = nutMonSqlAuth('R');

if (!$mndb) {
	die('Could not connect: ' . mysqli_error());
}

$result = $mndb->query("SELECT `id`,`device.mfr`,`device.model` FROM `$upsnm` ORDER BY id DESC LIMIT 1");
$upsnmdt = $result->fetch_array();
$result->free();

	
$today = date('y-m-j');

echo "<h2>UPS $upsnm $upsnmdt[1] $upsnmdt[2]</h2>";
?>

<h3>Set battery date manually</h3>

This provides functionality setting new battery date installed manually, in local database.<br>
Not all UPSs support battery date setting in internal memory, and even if it is supported,<br>
it may requirie using special drivers and software.<br><br>
Here battery date can be set manually. It will be stored in local DB only, NOT in UPS.<br>
Field used for storing is "battery.mfr.date".<br><br>

<form action="setBattDate.php" method="get">
<?php
	echo "<input type=\"hidden\" name=\"ups\" value=\"$upsnm\"/>";
	echo "<input title=\"Y-M-D\" name=\"battdate\" value=\"$today\"/>";
	//ALTER TABLE `40051981606@s31` CHANGE `battery.mfr.date` `battery.mfr.date` DATE NULL DEFAULT '2020-11-11';
?>
	<input type="submit" value="SET"/>
</form>