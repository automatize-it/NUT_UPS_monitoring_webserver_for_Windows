<style type="text/css">
	
	body {
		font-family: 'Ubuntu Mono';
	}
	
	@font-face {
		font-family: 'Ubuntu Mono';
		src: local('Ubuntu Mono'), local('UbuntuMono-Regular'), url('ubuntumono.woff2') format('woff2'), url('ubuntumono.woff') format('woff'), url('ubuntumono.ttf') format('truetype');
		font-weight: 400;
		font-style: normal;
	}
</style>

<div style="width:60%;">
<?php
/*
Keys: upsnm,btrdt
*/
	$ups = $_GET['upsnm'];
	$btrdt = $_GET['btrdt'];
	
	$mndb = new mysqli('localhost', 'root', 'mypass', 'ups_list');

	if (!$mndb) {
		die('Could not connect: ' . mysqli_error());
	}
	
	$result = $mndb->query("SELECT `id`,`device.mfr`,`device.model` FROM `$ups` ORDER BY id DESC LIMIT 1");
	$upsnmdt = $result->fetch_array();
	$result->free();
	
	$result = $mndb->query("SELECT `$ups`.`ups.load`,COUNT(*) FROM `$ups` WHERE `$ups`.`ups.load`>50");
	$maxlcnt = $result->fetch_array();
	$result->free();
	
	$result = $mndb->query("SELECT `ups.status`,`ts`,`ups.test.result` FROM `$ups` WHERE `ups.status` LIKE 'OB%' ORDER BY ts DESC LIMIT 1");
	$plarr = $result->fetch_array();
	$result->free();
	
	$result = $mndb->query("SELECT `ups.test.result` FROM `$ups` WHERE `ups.test.result` NOT LIKE 'Done and passed' AND `ups.test.result` NOT LIKE 'In progress' AND `ups.test.result` NOT LIKE ''");
	$utarr = $result->fetch_array();
	$result->free();
	
	$result = $mndb->query("SELECT `ups.test.result` FROM `$ups` WHERE `ups.test.result` LIKE 'Done and passed'");
	$tstarr = $result->fetch_array();
	$result->free();
	
	$result = $mndb->query("SELECT `$ups`.`ups.load`,COUNT(*) FROM `$ups`");
	$uld = $result->fetch_array();
	$result->free();
	
	$result = $mndb->query("SELECT `$ups`.`ups.load`,COUNT(*) FROM `$ups` WHERE `$ups`.`ups.load`=0");
	$uldz = $result->fetch_array();
	$result->free();
	
	//echo $uld[1]; echo $uldz[1];
	$uldzamnt = $uld[1]/$uldz[1];

	$result = $mndb->query("SELECT `$ups`.`input.voltage` FROM `$ups` WHERE `$ups`.`input.voltage`>240 AND `$ups`.`input.voltage`<205");
	$ivarr = $result->fetch_array();
	$result->free();
	
	$ivamnt = 11;
	if ($ivarr != NULL) {$ivamnt = $uld[1]/sizeof($ivarr);}
	
	$btrchng = 0;
	if ($btrdt != "pbna" ){
		if ( strtotime($btrdt) < strtotime('-2 years') && strtotime($btrdt) > strtotime('-3 years')){
			
			$btrchng = 1;
		} else {
			
			if ( strtotime($btrdt) < strtotime('-22 months')){
				
				$btrchng = 2;
			} 
		}
	}
	
	$result = $mndb->query("SELECT `ts` FROM `$ups` ORDER BY ts ASC LIMIT 1");
	$sdate = $result->fetch_array();
	$result->free();
	
	$dtamnt = 1;
	if (strtotime($sdate[0]) > strtotime('-10 days') &&
		$uld[1] > (480)
	) 
	{
		$dtamnt = 0;
	}
	
	$result = $mndb->query("SELECT `id`,`ts`,`ups.status`,`battery.charge` FROM `$ups` WHERE `ups.status` LIKE 'OB%' ORDER BY id ASC");
	$obarr = $result->fetch_array();
	$result->free();
	
	$i = 0;
	$tmpi = 0;
	$dschg = 0;
	//$tmpld = 100;
	//$tmpid = $obarr[$i]['id'];
	while($obarr[$i][0]){
		
		if ($obarr[$i+1][0] > ($obarr[$i][0]+1) ){
			
			$tmptm = strtotime($obarr[$i][1]) - strtotime($obarr[$tmpi][1]);
			$tmptm = round($tmptm / 60);
			$tmpchg = 100 - $obarr[$i][3];
			if ( ($tmpchg / $tmptm) > 29) {$dschg = 1;} 
			//$tmpid = $obarr[$i++]['id'];
			$i++; $tmpi = $i; 
			continue;
		}
		else {
			
			$i++;
		}
		
	}
	echo $dschg;
	
	//debug
	//$ivamnt = 2;
	//$btrchng = 2;
	//$dtamnt = 0;
	
	echo "<h1>UPS $upsnmdt[1] $upsnmdt[2] $ups</h1>";
	
	echo "<h2>";
	if ($tstarr) {
		/*echo "<form title=\"Run UPS self-test immidiately\" style=\"margin: auto;\" method=\"get\" onsubmit=\"return confirm('Self-test can also FAIL. Do anyway?');\"><button type=\"submit\" formaction=\"ups_self_test.php\" name=\"tstups\" value=\"$ups\">DO SELFTEST</button></form>";*/
		echo ("<a href=\"ups_self_test.php?tstups=$ups\" onClick=\"return confirm('Self-test can also FAIL. Do anyway?')\" title=\"\"><span style=\"color:brown\">[Do Self-Test]</span></a>&nbsp;");
	}
	echo ("<a href=\"ups_sspnd.php?upsnm=$ups\" onClick=\"return confirm('Sure?')\" title=\"suspend ups monitoring\"><span style=\"color:#0033cc\">[Suspend UPS monitoring]</span></a>&nbsp;");
	echo ("<a href=\"ups_cldb_dlg.php?upsdb=$ups\" title=\"optimize ups database\"><span style=\"color:#ff9900\">[Optimize UPS database]</span></a>&nbsp;");	
	//bad idea, to do drop table from html with only standard warning. Go do it manually you lazy human
	//echo ("<a href=\"\" title=\"DELETE UPS MONITORING AND DATA\"><span style=\"color:red\">[X]</span></a>");
	echo "</h2>";
			
	echo "<br><br>";
	echo "<h2>Automatic analysis for UPS $upsnmdt[1] $upsnmdt[2] $ups</h2>";
?>
Alpha version.<br>
ABSOLUTELY NO WARRANTY.<br>
USE AT YOUR OWN RISK.<br>
Information provided here is automatically generated and CAN NOT be considered as professional consultation, guide or even advise.
By using information provided here you acknowledge your understanding and acceptance that:<BR> 
1) any information provided CAN BE WRONG;<br>
2) anyone related to the creation of this software holds no responsibility for any actions, inactions and it's consequences  related to this software.<br>
<hr width="90%">
<?php
	
	if ($dtamnt == 1){
	//high priority problems
	if ($maxlcnt[1] > 10 ||
		$utarr != NULL ||
		$btrchng == 1)
	{
		
		echo "<h3>ALERT</h3>";
		
		if ($utarr != NULL){
			
			echo "<h4>Self-test fail</h4>";
			echo "<p>At least one self-test fail event registered on $ups UPS<br><b>Change $ups battery, then immidiately test UPS again. Change UPS immidiately if test fails again.</b><br>";
			
		}
		
		if ($maxlcnt[1] > 10){
			echo "<h4>High load</h4>";
			echo "<p>There was $maxlcnt[1] times very high load registered on $ups. It may indicate set of<br><b>different problems and not with UPS only.</b><br>First of all,<br><b>check if any inappropriate devices are connected to $ups UPS (like electrical teapots, printers etc.)</b>.<br> This may also indicate <br><b>misfunctioning of UPS electronics, or UPS connected devices internal power supplies problems.</b><br>Try connecting different devices to $ups and different UPS. Check connected PC's components.";
			
		}
		
		if ($btrchng == 1){
			
			echo "<h4>Battery change by date</h4>";
			echo "<p>According to $ups data battery must be changed.<br><b>Change $ups battery if it is possible, or change UPS itself. Remember that if you ignore this alert for 1 year you will not get adequate alerts of this type anymore.</b><br>";
			
		}
		
		echo "<hr width=\"90%\">";
	}

	//medium and possible problems
	if ( $btrdt == "pbna" ||
		$plarr['ups.status'] == NULL ||
		$uldzamnt < 2 ||
		$ivamnt < 10 ){
		
		echo "<h3>Warning</h3>";
		
		if ( $btrdt == "pbna" ){
			
			echo "<h4>Battery date</h4>";
			echo "<p>Battery date is out of range. Not all UPSses and device drivers supporting manual setting of this type of data, or date renewal may be just not done. There is no way implemented to distinguish which case is present. Just remember,<br><b>change batteries every 2-3 years even if everything seems OK.</b></p>";
			
		}
		
		if ($plarr['ups.status'] == NULL ){
			
			echo "<h4>Not ever tested</h4>";
			echo "<p>UPS $ups was never actually tested: no significant AC power losses have been registered, <b>or UPS provides no power at all and host turns off immidiately when AC power is lost</b>. \"Battery voltage\",  \"battery charge\",\"battery runtime\" parameters may not indicate real battery state.<br><b>Plug out AC power even once for even 5 minutes and remember that UPS can fail so choose time when there is no important work on PC present and everything is saved.</b></p>";
			
		}
		
		if ( $uldzamnt < 2 ){
			
			echo "<h4>Zero load</h4>";
			echo "<p>Zero load was registered often on UPS $ups. It can indicate misfunctioning of UPS electronics.<br><b>Try change UPS.</b></p>";
			
		}
		
		if ( $ivamnt < 11 ){
			
			echo "<h4>Unusual AC data</h4>";
			echo "<p>AC out of normal range (220V) often registered on $ups UPS. It can indicate misfunctioning of UPS electronics or AC power supply problems.<br><b>Check AC power, try to change UPS.</b></p>";
			
		}
		
		echo "<hr width=\"90%\">";
	}
	
	if ( $btrchng == 2 || 
		$plarr['ups.test.result'] == NULL
		)
	{
		
		echo "<h3>Note</h3>";
		
		if ( $btrchng == 2 ){
			
			echo "<h4>Battery change time comes</h4>";
			echo "<p>It is better to change batteries regularly and on time</p>";
			echo "<hr width=\"80%\">";
		}
		
		if ($plarr['ups.test.result'] == NULL){
			
			echo "<h4>Test functionality not provided</h4>";
			echo "<p>UPS $ups seems does not provide and/or have internal self-test functionality. Will be wise to test $ups manually on regular basis</p>";
			echo "<hr width=\"80%\">";
		}
	}
	}
	else {
		
		echo "<h2>Not enough data for proper analysis</h2>";
	}
?>

</div>