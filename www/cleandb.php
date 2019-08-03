

<?php
$upsnm = $_GET['db'];

//clean data

ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

//$upsnm = "apc_bu_es700@localhost_tmp";
$mndb = new mysqli('localhost', 'root', 'mypass', 'ups_list');

if (!$mndb) {
	die('Could not connect: ' . mysqli_error());
}

	
	$today = date('y-m-j,h-i-s');
	$today = str_replace(',','_',$today);
	$tmpnm = $upsnm."_".$today;
	echo $tmpnm;
	$result = $mndb->query("CREATE TABLE IF NOT EXISTS bckp.`$tmpnm` LIKE srvc.main"); 
	$result = $mndb->query("INSERT bckp.`$tmpnm` SELECT * FROM ups_list.`$upsnm`");
	if ($result){
		echo "$result";
		echo "Backup created";
	} else {
		echo "$result";
		echo "error2"; 
	}
	
	mysqli_free_result($result);

	$result = $mndb->query("SELECT `ups.status`, `battery.charge`,`input.voltage`,`input.voltage`,`ups.load`,`ups.test.result`,`id`,`ts` FROM `$upsnm` ORDER BY id");
	//$keyarr = $result->fetch_assoc();
	//$result->free();

	$cnt = 1;
	$valdt = 0;
	$ids = array();
	$dt = 0;

	while ($row = $result->fetch_assoc()){ 
	
		$tmptime = strtotime($row['ts']);
		if ($tmptime > ($dt+3200) ) { $dt = $tmptime; $cnt = 1; }
		
		if ($row['ups.status'] == 'OL' && 	
			$row['battery.charge'] == 100 &&
			$row['input.voltage'] > 209 &&
			$row['input.voltage'] < 235 &&
			$row['ups.load'] < 55 &&
			( strpos ($row['ups.test.result'],"passed") ||
			$row['ups.test.result'] == NULL ||
			$row['ups.test.result'] == ""
			)
			) 
		{
			if ($valdt == 1){ $valdt = 0; continue; }
			if ($cnt > 0) {$cnt = 0; continue;}
			$ids[] = $mndb->real_escape_string($row['id']);
			//echo $tmpid;
			//echo "<br>";
			//$resultmp = $mndb->query("DELETE FROM `$upsnm` WHERE id = `$tmpid`");
			//$resultmp->free();
			//$cnt++;
		}
		else {
			//debug
			
			echo $row['ups.status'];
			echo " ";
			echo $row['battery.charge'];
			echo " ";
			echo $row['input.voltage'];
			echo " ";
			echo $row['ups.load'];
			echo " ";
			echo $row['ups.test.result'];
			echo "<br>";
					
			$valdt = 1; $dt = $tmptime; $cnt = 1;
			continue;		
		}
}
	
$result->free();

$qstring = "`id`=";
$cnt = 0;
foreach ($ids as $cid){
	
	$qstring .= "$cid";
	if ($cnt > 1000){
		
		$mndb->query("DELETE FROM `$upsnm` WHERE $qstring");
		$qstring = "`id`="; $cnt = 0; continue;
	}
	if ( next($ids) ){
		$qstring .= " or `id`=";
	}
	else {
		
		$mndb->query("DELETE FROM `$upsnm` WHERE $qstring");
		break;
	}
	$cnt++;
}

$result = $mndb->query("OPTIMIZE TABLE ups_list.`$upsnm`");

mysqli_close($mndb);

ini_set('max_execution_time', 180);
ini_set('memory_limit', '128M');

echo $upsnm;
echo "<br>done<br>";
?>