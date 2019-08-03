<?php

$upsdb = $_GET['upsdb'];

$mndbtmp = new mysqli('localhost', 'root', 'mypass', 'ups_list');

if (!$mndbtmp) {
	die('Could not connect: ' . mysqli_error());
}

//$result = $mndbtmp->query("SELECT table_schema `$upsdb`, ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) \"DB Size in MB\" FROM information_schema.tables GROUP BY table_schema;");

$result = $mndbtmp->query("SELECT table_name AS `tbl`, round(((data_length + index_length) / 1024 / 1024), 2) `Size in MB` FROM information_schema.TABLES WHERE table_schema='ups_list' AND table_name='$upsdb'");
$dbsize = $result->fetch_array();

?>

<h2>UPS database optimization</h2>
This will optimize UPS database.<br> 
<?php
echo "$upsdb database size is $dbsize[1] MB";
if ($dbsize[1] > 10) {echo ", it needs optimization.";}
?>
<br>
Backup will be created in "bckp" section of database.<br>
Will take time. <br>
Data can be lost absolutely no warranty blah blah<br>
<?php
echo "<a href=\"cleandb.php?db=$upsdb\" name=\"optupsdb\" onclick=\"return confirm('Sure?');\" target=\"optprcs\">Optimize</a>";
?>
<br><a href="index_ups.php" target="_parent">back</a>
<br>
<iframe src="" name="optprcs" style="position: absolute; height: 60%; width: 100%; border: none"></iframe>