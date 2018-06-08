<?php
	
	$ups = $_GET['tstups'];
	$cmdout = array();
	
	echo $ups;
	
	$cmdstr = "C:\NUT\bin\upscmd -u admin -p password $ups test.battery.start.quick";
	
	exec($cmdstr, $cmdout); //.escapeshellarg($dir) 
	
	foreach ($cmdout as $str) {
	
		echo "<br>";
		echo $str;
	} 
	
?>