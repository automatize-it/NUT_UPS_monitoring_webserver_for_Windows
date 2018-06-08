<meta http-equiv="refresh" content="30">

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
		<th class="maintblhdr">BATT V curr/min</th>
		<th class="maintblhdr">BATT % curr/min</th>
		<th class="maintblhdr">IN VOLTAGE</th>
		<th class="maintblhdr">IN FREQ</th>
		<th class="maintblhdr">BATT DATE</th>
		<th class="maintblhdr">LOAD curr/max</th>
		<th class="maintblhdr">STATUS</th>
		<th class="maintblhdr">PWR LOSS last</th>
		<th class="maintblhdr">PWR LOSS max</th>
		<th class="maintblhdr">SLF-TST STAT</th>
		<th class="maintblhdr">LAST UPDATE</th>
		<th class="maintblhdr">slftst</th>
	</tr></thead>
	<tbody>
	<?php
		$mndb = new mysqli('localhost', 'root', 'password', 'ups_list');

		if (!$mndb) {
			die('Could not connect: ' . mysqli_error());
		}
		//echo 'Connected successfully';

		$qry_ups_nms = "'ups_list'";
		
		$upsnms = array();

		if ($result = $mndb->query("SELECT table_name FROM information_schema.tables WHERE table_schema='ups_list'")){

			while ($row = $result->fetch_array()){ 
				if ($row[0] != "main") {
					
					$upsnms[] = $mndb->real_escape_string($row[0]);
				}
			}
			
			/* free result set */
			$result->free();
			$result->close();
		}

		echo "<br>";

		foreach ($upsnms as $upsnm) {
			
			//echo "<br>$upsnm"; 'battery.charge','battery.voltage', 'input.voltage', 'input.frequency', 'battery.date', 'ups.load',
			//'ups.status', 'ups.test.result'
			$result = $mndb->query("SELECT * FROM `$upsnm` ORDER BY id DESC LIMIT 1");
			$keyarr = $result->fetch_assoc();
			$result->free();
			$result->close();
			
			$result = $mndb->query("SELECT MIN(NULLIF(`battery.voltage`,0)) FROM `$upsnm`");
			$minv = $result->fetch_array();
			$minv[0] = round($minv[0], 1); 
			$result->free();
			$result->close();
			
			$result = $mndb->query("SELECT MIN(NULLIF(`battery.charge`,0)) FROM `$upsnm`");
			$minbch = $result->fetch_array();
			$result->free();
			$result->close();
			
			$result = $mndb->query("SELECT MAX(`ups.load`) FROM `$upsnm`");
			$maxl = $result->fetch_array();
			$result->free();
			$result->close();
			
			
			echo "<tr class=\"border_bottom\"><td class=\"maincll\"><b>$upsnm</b></td>";
			
			$vlkeys = array('battery.voltage','battery.charge', 'input.voltage', 'input.frequency', 'battery.date', 'ups.load', 'ups.status', 'ups.test.result','ts');
			
			//if ( $keyarr['battery.mfr.date'] != NULL) { $vlkeys[4] = 'battery.mfr.date'; }
				
			foreach ($vlkeys as $vlkey){
				
				if ($vlkey == "battery.voltage" || $vlkey == "battery.charge" || $vlkey == "ups.load" || $vlkey == "ups.status"){
					
					if ($vlkey == "battery.voltage") {
						echo "<td class=\"maincll\">";
						if ($keyarr[$vlkey] != NULL ){
							echo "$keyarr[$vlkey] / $minv[0]";
						}
						echo "</td>";;
					} 
					if ($vlkey == "battery.charge") {echo "<td class=\"maincll\">$keyarr[$vlkey] / $minbch[0]</td>";}
					if ($vlkey == "ups.load") {
						echo "<td class=\"maincll\">$keyarr[$vlkey] / ";
						if ($maxl[0] >= 80) {echo "<span style=\"background-color:#ff0000\">$maxl[0]</span>";}
						else {echo "$maxl[0]";}
						if ( $keyarr[$vlkey] != $maxl[0] && $maxl[0] > 50 ){
							$result = $mndb->query("SELECT `$upsnm`.`ups.load`,COUNT(*) FROM `$upsnm` WHERE `$upsnm`.`ups.load`=$maxl[0]");
							$maxlcnt = $result->fetch_array();
							$result->free();
							$result->close();
							
							echo " ($maxlcnt[1])";
						}
						echo "</td>";
					}
					
					if ($vlkey == "ups.status") {
						
						echo "<td class=\"maincll\">$keyarr[$vlkey]</td>";
						
						echo "<td class=\"maincll smlfnt\">";
						
						
						$result = $mndb->query("SELECT `ups.status`,`ts`,`ups.test.result`  FROM `$upsnm` WHERE `ups.status` LIKE 'OB%' ORDER BY ts DESC LIMIT 1");
						$tmparr = $result->fetch_array();
						$result->free();
						$result->close();
						
						if ($tmparr['ups.status'] != NULL){
							$upsstat = $tmparr['ups.status'];
							$ts = $tmparr['ts'];
							$tstres = $tmparr['ups.test.result'];
							
							//unset($tmparr);
							$result = $mndb->query("SELECT `ts` FROM `$upsnm` WHERE `ts` > '$ts' AND `ups.status` LIKE 'OL%' ORDER BY ts ASC LIMIT 1");
							
							$tmparr = $result->fetch_array();
							$result->free();
							$result->close();
							$tmpss =  strtotime($tmparr[0])- strtotime($ts);
							$tmstmpstr = gmdate('i:s', $tmpss);
							
							echo "$ts $tmstmpstr<br>slftst: $tstres"; //$upsstat<br>
						}
						echo "</td>";
						echo "<td class=\"maincll smlfnt\">";
						if ($upsstat != NULL){
							
							$result = $mndb->query("SELECT t1.ts,ABS(TIMESTAMPDIFF(SECOND,t1.ts,t2.ts)) AS 'secdiff' FROM `$upsnm` AS t1 JOIN `$upsnm` AS t2 on t1.id=t2.id+1 WHERE t1.`ups.status` LIKE 'OL%' AND t2.`ups.status` LIKE 'OB%' ORDER BY 2 DESC");
							
							$tmparr = $result->fetch_array();
							$result->free();
							$result->close();
							$tsmax = $tmparr['ts']; $onbattmax = gmdate('i:s', $tmparr['secdiff']);
							
							echo "$tsmax $onbattmax";
							
							$upsstat = NULL;
							//print_r ($tmparr);
						}
						
						echo "</td>";
					}
				}
				else {
					
					echo "<td class=\"maincll\">$keyarr[$vlkey]</td>";
				}
			}
			
			/* free result set */
			$result->free();
			$result->close();
			
			if ($keyarr['ups.test.result'] != NULL) {echo "<td class=\"maincll\"><form method=\"get\"><button type=\"submit\" formaction=\"ups_self_test.php\" name=\"tstups\" value=\"$upsnm\">SELF-TEST</form></td></tr>";}
			else {echo "<td class=\"maincll\">N/A</td></tr>";}
			//echo "<tr><td colspan=\"10\"><hr width=\"85%\"></td></tr>";
		}
		
		mysqli_close($mndb);
	?>
	</tbody>
	
	</table>
	
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