<meta http-equiv="refresh" content="60">

<script type="text/javascript" src="jq/jquery.js"></script> 
<script type="text/javascript" src="jq/jquery.tablesorter.js"></script> 

<style type="text/css">
	
	.upsstr {
		background: #f0f0f0;
		width: 750px;
		vertical-align: middle;
		min-height: 30pt;
	}

	.hdr {
		
		background: #CCC;
		font-weight: bold;
		font-size: 8pt;
		vertical-align: middle;
	}

	.upsstrblck {
		
		margin-left: 10px;
		margin-right: 10px;
		display: inline-block;
		overflow: hidden;
		vertical-align: middle;
	}
	
	body {
		font-family: 'Ubuntu Mono';
	}
	
	@font-face {
		font-family: 'Ubuntu Mono';
		src: local('Ubuntu Mono'), local('UbuntuMono-Regular'), url('ubuntumono.woff2') format('woff2'), url('ubuntumono.woff') format('woff'), url('ubuntumono.ttf') format('truetype');
		font-weight: 400;
		font-style: normal;
	}
	
	.wrapper {
		
		margin: 0 auto; 
		width: 90%;
	}
	
	.maintbl{
		
		width: 90%;
		border: 0;
		margin-left:auto; 
		margin-right:auto;
		border-spacing: 0;
		padding: 0;
	}
	
	.maintblhdr{
		
		background: #CCC;
		font-weight: bold;
		font-size: 8pt;
		vertical-align: middle;
		min-height: 20pt;
	}
	
	.maincll{
		
		margin-left: 10px;
		margin-right: 10px;
		overflow: hidden;
		valign: middle;
		text-align: center;
		min-height: 30pt;
		min-width: 2%;
		background: #f0f0f0;
	}
	
	.maincll_l{
		
		text-align: left;
	}
	
	.mncll_tar{
		
		text-align: right;
	}
	
	.smlfnt{
		
		font-size: 9pt;
	}
	
	tr.border_bottom td {
		border-bottom: 3pt solid white;
	}
</style>

	<h1 align="center">UPS web server (NUT based)</h1>
	<div align="center">
	<form action="ups_add.php" method="get">
		<input name="newups"/>
		<input type="submit" value="ADD NEW"/>
	</form>
	</div>
	<table class="maintbl tablesorter" id="tbl">
	<thead><tr>
		<th class="maintblhdr">NAME</th>
		<th class="maintblhdr">curr HOST</th>
		<th class="maintblhdr">BATT V curr/min</th>
		<th class="maintblhdr">BATT % curr/min</th>
		<th class="maintblhdr" title="On battery time predicion">OBP</th>
		<th class="maintblhdr">IN V</th>
		<!-- <th class="maintblhdr">IN FREQ</th> -->
		<th class="maintblhdr">BATT DATE</th>
		<th class="maintblhdr">LOAD curr/max</th>
		<!-- <th class="maintblhdr">STATUS</th> -->
		<th class="maintblhdr">PWR LOSS last</th>
		<th class="maintblhdr">PWR LOSS max</th>
		<th class="maintblhdr">SLF-TST STAT</th>
		<th class="maintblhdr">LAST UPDATE</th>
		<!-- <th class="maintblhdr">slftst</th>
		<th class="maintblhdr">cmnds</th> -->
	</tr></thead>
	<tbody>
	<?php
		
		
		function dtc( $data ) {
			$output = $data;
			if ( is_array( $output ) )
				$output = implode( ',', $output);

			echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
		}
		
		$mndb = new mysqli('localhost', 'root', 'mypass', 'ups_list');

		if (!$mndb) {
			die('Could not connect: ' . mysqli_error());
		}
				
		$upsnms = array();
		
		$nastr = "<span style=\"color:#ccc\" title=\"data type not provided\">N/A</span>";

		if ($result = $mndb->query("SELECT table_name FROM information_schema.tables WHERE table_schema='ups_list'")){

			while ($row = $result->fetch_array()){ 
					
				$upsnms[] = $mndb->real_escape_string($row[0]);
			}
			
			/* free result set */
			$result->free();
		}

		echo "<br>";
		
		foreach ($upsnms as $upsnm) {
			
			//echo "<br>$upsnm"; 'battery.charge','battery.voltage', 'input.voltage', 'input.frequency', 'battery.date', 'ups.load',
			//'ups.status', 'ups.test.result'
			$result = $mndb->query("SELECT * FROM `$upsnm` ORDER BY id DESC LIMIT 1");
			$keyarr = $result->fetch_assoc();
			$result->free();
			
			//keys order is important!
			$vlkeys = array('device.type','battery.voltage','battery.charge','battery.runtime', 'input.voltage', 'battery.date', 'ups.load', 'ups.status', 'ups.test.result','ts'); //'input.frequency', ,
			
			$srvcbattstr = "battery.date";
			$srvcbattval = "";
			if ( strtotime($keyarr['battery.mfr.date']) > strtotime($keyarr['battery.date']) ) { $keyarr['battery.date'] = $keyarr['battery.mfr.date']; $srvcbattstr = "battery.mfr.date"; }
			
			if ( strtotime('-3 years') > strtotime($keyarr['battery.date']) ) {$keyarr['battery.date'] = "pbna";}
			
			$tmpclr = "red"; if ($keyarr['ups.status'] == "OL") {$tmpclr = "green";}
			$tmpstts = $keyarr['ups.status'];
			
			if ( $keyarr['ups.status'] == "OB DISCHRG")	{$tmpclr = "#FFA500";}
			if ( $keyarr['ups.status'] == "OL CHRG")	{$tmpclr = "lime";}
			if (strtotime($keyarr['ts']) < strtotime('-10 min')){ $tmpclr = "grey"; $tmpstts .= " (not actual)";}

			$srvcbattval = $keyarr[$srvcbattstr];
			
			//finding min-maxes
			if ($keyarr['battery.voltage'] != NULL && $keyarr['battery.voltage'] != "0"){
				
				if ($keyarr['battery.date'] != "pbna"){
					$result = $mndb->query("SELECT MIN((NULLIF(`battery.voltage`,0))) AS qtmpmin FROM (SELECT `battery.voltage` FROM `$upsnm` WHERE `$srvcbattstr`='$srvcbattval') AS tmpq");
				}
				else{
					$result = $mndb->query("SELECT MIN((NULLIF(`battery.voltage`,0))) AS qtmpmin FROM `$upsnm`");
				}
				$minv = $result->fetch_array();
				$minv[0] = round($minv[0], 1); 
				$result->free();
			}
			if ($keyarr['battery.date'] != "pbna"){
				
				$result = $mndb->query("SELECT MIN((NULLIF(`battery.charge`,0))) AS qtmpmin FROM (SELECT `battery.charge` FROM `$upsnm` WHERE `$srvcbattstr`='$srvcbattval') AS tmpq");
			}
			else{
				
				$result = $mndb->query("SELECT MIN(NULLIF(`battery.charge`,0)) FROM `$upsnm`");
			}
			$minbch = $result->fetch_array();
			$result->free();
			
			//ups load is not connected to battery condition i guess
			$result = $mndb->query("SELECT MAX(`ups.load`) FROM `$upsnm`");
			$maxl = $result->fetch_array();
			$result->free();
						
			//table begin
			$tmp = $keyarr['battery.date'];
			echo "<tr class=\"border_bottom\"><td class=\"maincll mncll_tar\"><b>$upsnm<a href=\"ups_additional.php?upsnm=$upsnm&btrdt=$tmp\" title=\"UPS additional data, commands and analytics\">[?]</a><span title=\"$tmpstts\" style=\"color: $tmpclr;\"\">&nbsp;&#x23FC;</span></b></td>";
				
			foreach ($vlkeys as $vlkey){
				
				//
				if ($vlkey == "battery.voltage" || $vlkey == "battery.charge" || $vlkey == "ups.load" || $vlkey == "battery.date" || $vlkey == "ups.status" || $vlkey == "ups.test.result" || $vlkey == "battery.runtime" || $vlkey == "device.type"){
					
					if ($vlkey == "device.type") {echo "<td class=\"maincll maincll_l\">&nbsp;$keyarr[$vlkey]</td>";}
					
					if ($vlkey == "battery.voltage") {
						echo "<td class=\"maincll\">";
						if ($keyarr[$vlkey] != NULL && $keyarr[$vlkey] != "0"){
							echo "$keyarr[$vlkey]&nbsp;/&nbsp;$minv[0]";
						}
						else {
							
							echo $nastr;
						}
						echo "</td>";
					} 
					if ($vlkey == "battery.charge") {echo "<td class=\"maincll\">$keyarr[$vlkey]&nbsp;/&nbsp;$minbch[0]</td>";}
					if ($vlkey == "ups.load") {
						echo "<td class=\"maincll\">$keyarr[$vlkey]&nbsp;/&nbsp;";
						if ($maxl[0] >= 80) {echo "<span style=\"background-color:#ff0000\">$maxl[0]</span>";}
						else {echo "$maxl[0]";}
						if ( $keyarr[$vlkey] != $maxl[0] && $maxl[0] > 50 ){
							$result = $mndb->query("SELECT `$upsnm`.`ups.load`,COUNT(*) FROM `$upsnm` WHERE `$upsnm`.`ups.load`=$maxl[0]");
							$maxlcnt = $result->fetch_array();
							$result->free();
							
							echo "&nbsp;($maxlcnt[1])";
						}
						echo "</td>";
					}
					
					 if ($vlkey == "battery.runtime") {
						
						$tmp = round(($keyarr[$vlkey]/60)/2);
						echo "<td class=\"maincll\">~$tmp m</td>"; 
					 }
					
					if ($vlkey == "battery.date") {
						
						echo "<td class=\"maincll\">";
						if ($keyarr[$vlkey] == "pbna") {
							echo "<span style=\"color:#ccc\" title=\"present but not actual\">$keyarr[$vlkey]</span>";
						}
						else{
							echo "$keyarr[$vlkey]";
						}
						echo "</td>";
					}
					
					if ($vlkey == "ups.test.result") {
						
						echo "<td class=\"maincll\">";
						//echo "test";
						if ($keyarr[$vlkey] != NULL){
							if ( $keyarr[$vlkey] == "Done and passed" ) { echo "<span title=\"$keyarr[$vlkey]\">OK</span>"; }
							else { echo "<span style=\"color:red;\">$keyarr[$vlkey]</span>"; }
						}
						else {echo $nastr;}
						echo "</td>";
					}
					
					if ($vlkey == "ups.status") {
						
						//echo "<td class=\"maincll\">$keyarr[$vlkey]</td>";
						
						echo "<td class=\"maincll smlfnt\">";
						
						//get last AC power loss and count its duration
						
						//get last OB state
						$result = $mndb->query("SELECT `ups.status`,`ts`,`ups.test.result` FROM `$upsnm` WHERE `ups.status` LIKE 'OB%' ORDER BY ts DESC LIMIT 1");
						$tmparr = $result->fetch_array();
						$result->free();
						
						if ($tmparr['ups.status'] != NULL){
							
							$upsstat = $tmparr['ups.status'];
							$ts = $tmparr['ts'];
							
							$tstres = $tmparr['ups.test.result'];
							
							/*there may be way to do all of this by one monstrous sql query but I dunno ¯\_(ツ)_/¯*/
							
							//get timestamp of last OL state before last OB state
							$result = $mndb->query("SELECT `ups.status`,`ts` FROM `$upsnm` WHERE `ups.status` LIKE 'OL%' AND `ts` < '$ts' ORDER BY ts DESC LIMIT 1");
							$tmparr = $result->fetch_array();
							$result->free();
							
							$ts = $tmparr['ts'];
							
							//get timestamp of first OB state
							//there may be only ONE OB state so ts can be more OR EQUAL
							$result = $mndb->query("SELECT `ups.status`,`ts` FROM `$upsnm` WHERE `ups.status` LIKE 'OB%' AND `ts` > '$ts' ORDER BY ts ASC LIMIT 1");
							$tmparr = $result->fetch_array();
							$result->free();
							
							$ts = $tmparr['ts'];
							
							//get timestamp of first OL state after OB state
							$result = $mndb->query("SELECT `ups.status`,`ts` FROM `$upsnm` WHERE `ts` > '$ts' AND `ups.status` LIKE 'OL%' ORDER BY ts ASC LIMIT 1");
							
							$tmparr = $result->fetch_array();
							$result->free();
							
							//count time between first OB state and first OL state
							//of last AC power loss
							$tmpss = (strtotime($tmparr['ts'])- strtotime($ts));
							$tmstmpstr = gmdate('i:s', $tmpss);
							
							echo "$ts $tmstmpstr";
							if (strpos($tstres,"progress")) {echo "<br>on slftst";}
							//<br>slftst: $tstres"; //$upsstat<br>
						}
						else{
							echo "<span style=\"color:#ccc\" title=\"power losses not registered\">N/R</span>";
						}
						echo "</td>";
						echo "<td class=\"maincll smlfnt\">";
						
						//debug 
						//$upsstat = NULL;
						
						if ($upsstat != NULL){
							
							/*
							$result = $mndb->query("SELECT t1.ts,ABS(TIMESTAMPDIFF(SECOND,t1.ts,t2.ts)) AS 'secdiff' FROM `$upsnm` AS t1 JOIN `$upsnm` AS t2 on t1.id=t2.id+1 WHERE t1.`ups.status` LIKE 'OL%' AND t2.`ups.status` LIKE 'OB%' ORDER BY 2 DESC");
							
							$tmparr = $result->fetch_array();
							$result->free();
							$tsmax = $tmparr['ts']; $onbattmax = gmdate('i:s', $tmparr['secdiff']);
							*/
							
							$query = $mndb->query("SELECT `id`,`ts`,`ups.status` FROM `$upsnm` WHERE `ups.status` LIKE 'OB%' ORDER BY id ASC");
							
							$arrdim = 0;
							$tmpobarr = array();
							while($line = mysqli_fetch_array($query)){
								
								$tmpobarr[] = $line; $arrdim++;
							}
							
							//$tmpobarr = $results->fetch_array();
							$query->free();
							
							//one dimensional array case means that our previously found AC loss was only one registered
							//so just copy previous info
							if ( $arrdim < 2 ){
								
								echo "$ts $tmstmpstr";
								if (strpos($tstres,"progress")) {echo "<br>on slftst";}
							}
							else {
								
								$i = 0;
								$tsdiff = 0;
								$lngst = 0;
								
								$tmpts = $tmpobarr[$i][1];
									
								$result = $mndb->query("SELECT `ts` FROM `$upsnm` WHERE `ups.status` LIKE 'OL%' AND `ts`>'$tmpts' ORDER BY ts ASC LIMIT 1");
								$nxtts = $result->fetch_array();
								$result->free();
								
								if ( (strtotime($nxtts[0]) - strtotime($tmpts)) > $tsdiff ) { 
									
									$tsdiff = strtotime($nxtts[0]) - strtotime($tmpts);
									$lngst = $tmpts;
								}
								
								$i++;

								while( !empty($tmpobarr[$i]) ){
									
									if (strtotime($tmpobarr[$i][1]) < strtotime($nxtts[0])){ 
										
										$i++; continue;
									}
									
									$tmpts = $tmpobarr[$i][1];
									
									$result = $mndb->query("SELECT `ts` FROM `$upsnm` WHERE `ups.status` LIKE 'OL%' AND `ts`>'$tmpts' ORDER BY ts ASC LIMIT 1");
									$nxtts = $result->fetch_array();
									$result->free();
									
									if ( (strtotime($nxtts[0]) - strtotime($tmpts)) > $tsdiff ) { 
										
										$tsdiff = strtotime($nxtts[0]) - strtotime($tmpts);
										$lngst = $tmpts;
									}
									
									$i++;
									
								}
								
								$tsdiff = gmdate('i:s', $tsdiff);
								
								echo "$lngst $tsdiff";
							}
							
							$upsstat = NULL;
						}
						else{
							echo "<span style=\"color:#ccc\" title=\"power losses not registered\">N/R</span>";
						}
						
						echo "</td>";
					}
				}
				else {
					
					echo "<td class=\"maincll\">$keyarr[$vlkey]</td>";
				}
			}
			
			/*			
			if ($keyarr['ups.test.result'] != NULL) {echo "<td class=\"maincll\"><form title=\"Run UPS self-test immidiately\" style=\"margin: auto;\" method=\"get\" onsubmit=\"return confirm('Self-test can also FAIL. Do anyway?');\"><button type=\"submit\" formaction=\"ups_self_test.php\" name=\"tstups\" value=\"$upsnm\">SLFTST</button></form>";}
			else {echo "<td class=\"maincll\"><span style=\"color:#ccc\" title=\"self-test on demand not available\">N/A</span>";}
			echo ("</td>");
			
			echo ("<td class=\"maincll\">");
			echo ("<a href=\"ups_sspnd.php?upsnm=$upsnm\" onClick=\"return confirm('Sure?')\" title=\"suspend ups monitoring\"><span style=\"color:#0033cc\">[SZ]</span></a>&nbsp;");
			echo ("<a href=\"ups_cldb_dlg.php?upsdb=$upsnm\" title=\"optimize ups database\"><span style=\"color:#ff9900\">[DBO]</span></a>&nbsp;");	
			//bad idea, do drop table from html with only standard warning. Go do it manually you lazy human
			//echo ("<a href=\"\" title=\"DELETE UPS MONITORING AND DATA\"><span style=\"color:red\">[X]</span></a>");
			echo("</td></tr>");
			*/
						
			//echo "<tr><td colspan=\"10\"><hr width=\"85%\"></td></tr>";
		}
		
		mysqli_close($mndb);
	?>
	</tbody>
	
	</table>
	<br>
	<div align="center"><a href="http://localhost/ups_sspndd_lst.php">suspended UPSs</a></div>
	
	<script type="text/javascript">
	$(document).ready(function() 
    { 
       $("table").tablesorter({ 
        // sort on the first column and third column, order asc 
        sortList: [[11,1],[0,0]] 
    });
    } 
	);
	</script>
	
	<!--
	<div class="wrapper">
		<div class="upsstr hdr">
			<div class="upsstrblck">NAME</div>
			<div class="upsstrblck">VOLTAGE</div>
			<div class="upsstrblck">STATUS</div>
			<div class="upsstrblck">CURR LOAD</div>
			<div class="upsstrblck">BATTERY %</div>
			<div class="upsstrblck">SLF-TST STAT</div>
		</div>
		
		<div class="upsstr">
			<div class="upsstrblck"><b>S4 Powercom 535</b></div>
			<div class="upsstrblck">220V</div>
			<div class="upsstrblck">ON-LINE</div>
			<div class="upsstrblck">5 %</div>
			<div class="upsstrblck">100 %</div>
			<div class="upsstrblck">PASSED</div>			
			<div class="upsstrblck"><button>SELF-TEST</div>
		</div>
		
		<hr width="720">
		<div class="upsstr">
		<div class="upsstrblck"><b>UPS2</b></div><div class="upsstrblck">221V</div><div class="upsstrblck">ON-BATTvfdsfdsfasdf</div>
		<div class="upsstrblck"><button>SELF-TEST</div>
		</div>
	</div>
	-->