<?php
	
	$ups = $_GET['tstups'];
	$cmdout = array();
	
	echo $ups;
	
	$cmdstr = "C:\NUT\bin\upscmd -u admin -p mypass $ups test.battery.start.quick";
	
	exec($cmdstr, $cmdout); //.escapeshellarg($dir) 
	
	foreach ($cmdout as $str) {
	
		echo "<br>";
		echo $str;
	} 
	
	sleep(1);
	$cmdstr = "C:\NUT\get_remote_ups_data.cmd $ups";
	exec($cmdstr, $cmdout);
	
	//sleep(10);
	
	//$cmdstr = "C:\NUT\get_remote_ups_data.cmd $ups";
	//exec($cmdstr, $cmdout);
	
	echo "<meta http-equiv=\"refresh\" content=\"1; url=index.php\">";
?>