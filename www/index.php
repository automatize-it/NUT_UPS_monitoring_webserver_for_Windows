<link rel="shortcut icon" type="image/png" href="nutmon16.ico"/>

<meta http-equiv="refresh" content="120">

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
		src: local('Ubuntu Mono'), local('UbuntuMono-Regular'), url('ubuntumono.woff') format('woff'), url('ubuntumono.ttf') format('truetype');
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
	
	a:link {
	  color: black;
	  text-decoration: none;
	}

	a:visited {
	  color: black;
	}

	a:hover {
	  color: blue;
	}
	
</style>

	<h1 align="center">UPS web server (NUT based)</h1>
	<div align="center">
	<form action="upsAdd.php" method="get">
		<input name="newups"/>
		<input type="submit" value="ADD NEW"/>
	</form>
	</div>
	<table class="maintbl tablesorter" id="tbl">
	<thead><tr>
		<th class="maintblhdr">UPS ID</th>
		<th class="maintblhdr">curr HOST</th>
		<th class="maintblhdr">MFR & MOD</th>
		<!-- <th class="maintblhdr">BATT V curr/min</th>-->
		<th class="maintblhdr">BATT curr/min</th>
		<!-- <th class="maintblhdr" title="On battery time predicion (minimal registered)">OBP(mr)</th> -->
		<th class="maintblhdr">IN V</th>
		<!-- <th class="maintblhdr">IN FREQ</th> -->
		<th class="maintblhdr">BATT DATE</th>
		<th class="maintblhdr">LOAD curr/max</th>
		<!-- <th class="maintblhdr">STATUS</th> -->
		<th class="maintblhdr">PWR LOSS last</th>
		<th class="maintblhdr">PWR LOSS max</th>
		<th class="maintblhdr" title="On battery last start value">OB LSV</th>
		<th class="maintblhdr" title="Last discharge speed">LDS</th>
		<!-- <th class="maintblhdr">SLF-TST STAT</th> -->
		<th class="maintblhdr">LAST UPDATE</th>
		<!-- <th class="maintblhdr">slftst</th>
		<th class="maintblhdr">cmnds</th> -->
	</tr></thead>
	<tbody>
	<br>
	
	<?php
		
		include 'nutMonSqlAuth.php';
		
		//debug. Yes, lame
		function dtc( $data ) {
			$output = $data;
			if ( is_array( $output ) )
				$output = implode( ',', $output);

			echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
		}
		
		//to pass unavl hosts for "backend" ping
		session_start();
		
		$unavlarr = array();
		$lngarr = array();
		
		$mndb = nutMonSqlAuth('R');

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
		
		foreach ($upsnms as $upsnm) {
			
			//dtc($upsnm);
			$before = microtime(true);
			
			$upsNmShrt = (explode('@',$upsnm))[0];

			//echo "<br>$upsnm"; 'battery.charge','battery.voltage', 'input.voltage', 'input.frequency', 'battery.date', 'ups.load',
			//'ups.status', 'ups.test.result'
			$result = $mndb->query("SELECT * FROM `$upsnm` ORDER BY id DESC LIMIT 1");
			$keyarr = $result->fetch_assoc();
			$result->free();
			
			//keys order is important!
			$vlkeys = array('device.type','device.mfr','battery.charge', 'input.voltage', 'battery.date', 'ups.load', 'ups.status', 'ts'); //'input.frequency', , 'ups.test.result', 'battery.runtime','battery.voltage',
			
			//handling battery date
			$srvcbattstr = "battery.date";
			$srvcbattval = "";
			
			//getting battery date that might be set manually in battery.mfr.date
			/*
			if ( $keyarr['battery.mfr.date'] == NULL ){
					
				$result = $mndb->query("SELECT `battery.mfr.date` FROM `$upsnm` WHERE `battery.mfr.date` IS NOT NULL ORDER BY `battery.mfr.date` DESC LIMIT 1");
				$manbattdate = $result->fetch_assoc();
				$result->free();
				
				if ($manbattdate != NULL ) { $keyarr['battery.mfr.date'] = $manbattdate['battery.mfr.date']; }
			}
			*/
			
			if ( strtotime($keyarr['battery.mfr.date']) > strtotime($keyarr['battery.date']) ) { 
				
				$keyarr['battery.date'] = $keyarr['battery.mfr.date']; 
				$srvcbattstr = "battery.mfr.date";
			}
			
			$battYears = 0;
			if ( strtotime('-5 years') > strtotime($keyarr['battery.date']) ) {
				
				$keyarr['battery.date'] = "pbna";
				$battYears = 6;
			}
			else {
				
				$battYears = round((strtotime('now') - strtotime($keyarr['battery.mfr.date']))/31536000,1);
			}
			
			//handling ups status
			$tmpclr = "red"; if ($keyarr['ups.status'] == "OL") {$tmpclr = "green";}
			$tmpstts = $keyarr['ups.status'];
			
			if ( $keyarr['ups.status'] == "OB DISCHRG")	{$tmpclr = "#FFA500";}
			if ( $keyarr['ups.status'] == "OL CHRG")	{$tmpclr = "lime";}
			
			/*
			dtc ($upsnm); dtc(strtotime($keyarr['ts'])); dtc(strtotime('now'));
			dtc(strtotime('now') - strtotime($keyarr['ts']));
			*/
			
			if ( strtotime($keyarr['ts']) < strtotime('-3 min')){ $tmpclr = "grey"; $tmpstts .= " (not actual)";}

			$srvcbattval = $keyarr[$srvcbattstr];
			
			//finding min-maxes with respect to last battery change
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
			
			if ($keyarr['battery.date'] != "pbna"){
				
				$result = $mndb->query("SELECT MIN((NULLIF(`battery.runtime`,0))) AS qtmpmin FROM (SELECT `battery.runtime` FROM `$upsnm` WHERE `$srvcbattstr`='$srvcbattval') AS tmpq");	
			}
			else {
				$result = $mndb->query("SELECT MIN((NULLIF(`battery.runtime`,0))) FROM `$upsnm`");
			}
			
			$minIntBr = $result->fetch_array();
			$result->free();
			
			//ups load is not connected to battery condition i guess
			$result = $mndb->query("SELECT MAX(`ups.load`) FROM `$upsnm`");
			$maxl = $result->fetch_array();
			$result->free();
			
			//get host status if UPS data is out of date
			$hstonln = false;
			
			/*	
				lets do some sick stuff. Freak the backend!
				Case is, we need to ping our host to see is it unavaliable completely or its
				ups driver problem. But doing it here takes hell of time and js cannot do it at all.
				So lets...
			*/
			
			if ($tmpclr == "grey"){
				
				$unavlarr[] = $keyarr['device.type'];
			}
			
			
			//table begin
			$tmp = $keyarr['battery.date'];
			$tmpid = $upsnm . "_1st";
			echo "<tr class=\"border_bottom\"><td class=\"maincll mncll_tar\" id=\"$tmpid\"><b><a href=\"upsAdditional.php?upsnm=$upsnm&btrdt=$tmp\" title=\"&#9881;&#65039; UPS additional data, commands and analytics\">";
			echo strtoupper($upsNmShrt);
			echo "</a><span title=\"$tmpstts\" style=\"color: $tmpclr;\"\">&nbsp;&#x23FC;</span></b></td>";
				
			foreach ($vlkeys as $vlkey){
				
				if ($vlkey == "battery.voltage" || $vlkey == "battery.charge" || $vlkey == "ups.load" || $vlkey == "battery.date" || $vlkey == "ups.status" || $vlkey == "ups.test.result" || $vlkey == "battery.runtime" || $vlkey == "device.type" || $vlkey == "device.mfr"){
					
					//in device.type current ups host is stored manually
					if ($vlkey == "device.type") {
						
						$tmp = $keyarr['device.type'];
						echo "<td class=\"maincll maincll_l\" id=\"$tmp\">&nbsp;$keyarr[$vlkey] ";
						echo "</td>";
					}
					
					if ($vlkey == "device.mfr") {
						
						echo "<td class=\"maincll smlfnt\">$keyarr[$vlkey]";
						$vlkey = 'device.model';
						echo "<br>$keyarr[$vlkey]";
						echo "</td>";
					}
					
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
					
					if ($vlkey == "battery.charge") {
						echo "<td class=\"maincll\">$keyarr[$vlkey]&nbsp;/&nbsp;$minbch[0]";
						$vlkey = 'battery.voltage';
						if ($keyarr[$vlkey] != NULL && $keyarr[$vlkey] != "0"){
							echo "<br><small>$keyarr[$vlkey]&nbsp;/&nbsp;$minv[0]</small></td>";
						}
						echo "</td>";
					}
					
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
					
					/*
					if ($vlkey == "battery.runtime") {
						
						if (isset($minIntBr)) { $keyarr[$vlkey] = $minIntBr[0]; }
						
						//UPSs internal time prognose is VERY optimistic, must divide by two at least
						$tmp = round(($keyarr[$vlkey]/60) / 2);
						if ($tmp > 15){
							echo "<td class=\"maincll\"><span style=\"color:red;\" title=\"Internal UPSs OBP time is inadequate. Common value is 10-15 min MAX. Check UPS load and power loss max.\">~$tmp m</td>";
						}
						else {echo "<td class=\"maincll\">~$tmp m</td>";}
						
					}
					*/
					
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
						
						if ($keyarr[$vlkey] != NULL){
							if ( $keyarr[$vlkey] == "Done and passed" ) { echo "<span title=\"$keyarr[$vlkey]\">OK</span>"; }
							else { echo "<span style=\"color:red;\">$keyarr[$vlkey]</span>"; }
						}
						else {echo $nastr;}
						echo "</td>";
					}
					
					//here we do some that must be do in backend actually. Working on it
					//we want to see last and longest powerlosses, just FOA
					
					if ($vlkey == "ups.status") {
						
						$tsdiff = 0;
												
						$result = $mndb->query("SELECT `id`,`ts`,`battery.charge` FROM `$upsnm` WHERE `ups.status` LIKE 'OB%' ORDER BY ts DESC");
						
						$i = 0;
						$tmparr = NULL;
						$tmparr = array();
												
						while ($row = $result->fetch_array()){ 
							
							$tmparr[]=$row;
						}
						$result->free();
						
						$zf = 0;
						
						if (!empty($tmparr)){
							
							//count last OB duration
							$i = 0;
							$zf = sizeof($tmparr);
							
							if ($zf > 2){
								for ($i = 0; $i < $zf; $i++){
									if (($i+1) == $zf || (($tmparr[$i][0])-1) > $tmparr[$i+1][0]) {break;}
								}
							}
							
							$tmptm = $tmparr[$i][0];
							$tmpObLstBatt = $tmparr[0][2];
							$result = $mndb->query("SELECT `ts`,`battery.charge` FROM `$upsnm` WHERE `ups.status` LIKE 'OL%' AND `id` > $tmptm LIMIT 1");
							$nxtoltm = $result->fetch_array();
							$result->free();
							
							if ($nxtoltm[1] > $tmpObLstBatt) { $lastObEndBatt = $tmpObLstBatt; }
							else { $lastObEndBatt = $nxtoltm[1]; }
							
							$tsdiff = (strtotime($nxtoltm[0]) - strtotime($tmparr[$i][1]));
							
							//do some homebrew battery discharge time calculation
							$battDischPrc = ($tmparr[$i][2]) - $lastObEndBatt;
							$battDischTime = strtotime($tmparr[0][1]) - strtotime($tmparr[$i][1]);
							
							//get first battery charge on last powerloss, very informative
							$result = $mndb->query("SELECT `battery.charge` FROM `$upsnm` WHERE `id` LIKE $tmptm");
							$tmp = $result->fetch_array();
							$result->free();
							
							$lastObStartBatt = $tmp[0];
							
							//we dont know frequency of local data and there could be only one 
							//one record indicating OB, so we must add some sec and 30 looks reasonable
							
							if ($tsdiff <= 1) {$tsdiff += 30;}
							
							$tsdiff = gmdate('i:s',$tsdiff);
							$tmpts = $tmparr[$i][1];
							
							echo "<td class=\"maincll smlfnt\" title=\"$tsdiff\">";
							echo "$tmpts";
							
						}
						else{
							
							echo "<span style=\"color:#ccc\" title=\"power losses not registered\">N/R</span>";
						}
						echo "</td>";
						
						
						echo "<td class=\"maincll smlfnt\" title=\"please note that time accuracy depends on local data cycle and is +-30 sec at best\">";
						
						//do max OB
						//if array has two or less elements means our last OB is one of max OBs for sure
						if ( !empty($tmparr) && $zf > 2 ){
							
							//count max OB duration
							$i = 0;
							$max=$sti=0;
							$z=$maxz=0;
							$tsdiff = 0;
							
							//count longest OB
							for ($i = 0; $i < $zf; $i++){
								
								//check if end of array
								if (($i+1) == $zf){
									
									$tsdiff = strtotime($tmparr[$i-$z][1]) - strtotime($tmparr[$i][1]);
									if ($tsdiff > $max) {
										
										$max = $tsdiff;
										$sti = $i;
										$maxz = $z;
									}
									break;
								}
								
								if ( ($tmparr[$i][0])-1 > $tmparr[$i+1][0] ) {
									
									$tsdiff = strtotime($tmparr[$i-$z][1]) - strtotime($tmparr[$i][1]);
									
									if ($tsdiff > $max) {
									
										$max = $tsdiff;
										$sti = $i;
										$maxz = $z;
									}
									$z = 0;
									continue;
								}
								$z++;
							}
								
							$tmptm = $tmparr[($sti)][0];
							$result = $mndb->query("SELECT `ts` FROM `$upsnm` WHERE `ups.status` LIKE 'OL%' AND `id` > $tmptm LIMIT 1");
							$nxtoltm = $result->fetch_array();
							$result->free();
						
							$max = strtotime($nxtoltm[0]) - strtotime($tmparr[$sti][1]);
							
							if ($max <= 1) {$max += 30;}
							
							$tsdiff = gmdate('i:s',$max);
							$tmpts = $tmparr[$sti][1];
							
							if ($max > 900){
								
								echo "<span style=\"color:red;\" title=\"On $tmpts MAX OB time is loo long. It may indicate several problems. Check it!\">$tsdiff</span>";
							}
							else {
								
								echo "<span title=\"$tmpts\">$tsdiff</span>";
							}							
						}
						else{
							
							if(empty($tmparr)) {echo "<span style=\"color:#ccc\" title=\"power losses not registered\">N/R</span>";}
							else {echo "<span title=\"$tmpts\">$tsdiff</span>";}
						}
						
						$tmparr['ups.status'] = NULL;
						echo "</td>";
						
						//do last start battery charge value on OB
						if ($lastObStartBatt < 70) {$tmpClr = "#b3b300";}
						if ($lastObStartBatt < 41) {$tmpClr = "red";}
						
						echo "<td class=\"maincll\">";
						
						if ( isset($tmpClr) ) {echo "<span style=\"color:$tmpClr\" title=\"start OB battery charge value is low. It may indicate that battery is old\">$lastObStartBatt</span>"; unset($tmpClr);}
						else {echo "$lastObStartBatt";} 
						echo "</td>";
						
						if ($battDischTime > 90){
							$predictedTime = round($battDischPrc / ($battDischTime/60), 2);
							if ($predictedTime > 0) {
								$prt2 = round( $lastObStartBatt / $predictedTime,0);
								if ($battYears > 1){$prt2 = round( $prt2 / $battYears,0);}
								echo "<td class=\"maincll smlfnt\">$predictedTime %/m<br>(~$prt2 m)</td>";
							}
							else { echo "<td class=\"maincll smlfnt\">N/A</td>"; }
						}
						else { echo "<td class=\"maincll smlfnt\">N/A</td>"; }	
					}
				}
				else {
					
					echo "<td class=\"maincll\">$keyarr[$vlkey]</td>";
				}
			}
			
			/*******************************************************************************************************
			bad idea, do drop table and other sensitive stuff from html with only standard warning. 
			Go do it manually you lazy human
			********************************************************************************************************/
			
			/*			
			if ($keyarr['ups.test.result'] != NULL) {echo "<td class=\"maincll\"><form title=\"Run UPS self-test immidiately\" style=\"margin: auto;\" method=\"get\" onsubmit=\"return confirm('Self-test can also FAIL. Do anyway?');\"><button type=\"submit\" formaction=\"ups_self_test.php\" name=\"tstups\" value=\"$upsnm\">SLFTST</button></form>";}
			else {echo "<td class=\"maincll\"><span style=\"color:#ccc\" title=\"self-test on demand not available\">N/A</span>";}
			echo ("</td>");
			
			echo ("<td class=\"maincll\">");
			echo ("<a href=\"ups_sspnd.php?upsnm=$upsnm\" onClick=\"return confirm('Sure?')\" title=\"suspend ups monitoring\"><span style=\"color:#0033cc\">[SZ]</span></a>&nbsp;");
			echo ("<a href=\"ups_cldb_dlg.php?upsdb=$upsnm\" title=\"optimize ups database\"><span style=\"color:#ff9900\">[DBO]</span></a>&nbsp;");	
			
			//echo ("<a href=\"\" title=\"DELETE UPS MONITORING AND DATA\"><span style=\"color:red\">[X]</span></a>");
			echo("</td></tr>");
			*/
						
			//echo "<tr><td colspan=\"10\"><hr width=\"85%\"></td></tr>";
			
			$after = microtime(true);
			
			if (($after-$before) > 0.3 ){ $lngarr[] = $tmpid; }
			//dtc(($after-$before));
		}
		
		mysqli_close($mndb);
		
		//sickstuff step 2
		$_SESSION['hstarr'] = $unavlarr;
	?>
	</tbody>
	
	</table>
	<br>
	<div align="center" id="hststt">Now checking hosts availability...</div>
	<div align="center"><a href="upsSspndd.php">suspended UPSs</a></div>
	
	<!-- aaaaaand there is sickstuff step 3 -->
	<iframe style="display:none; visibility:hidden;" src="ping.php"></iframe>
	
	<script type="text/javascript">
		$(document).ready(function() 
		{ 
		   $("table").tablesorter({ 
			// sort on the first column and third column, order asc 
			sortList: [[11,1],[9,1]] 
		});
		} 
		);
	</script>
	
	<script type="text/javascript">
		
		var lng_amnt = <?php echo sizeof($lngarr); ?>;
		if (lng_amnt > 0){
			
			let alrtstr = "<span style=\"color:orange;\" title=\"handling this UPS DB takes a long time. May want to optimize it\"> ! </span>"
			let arr = "<?php echo implode(',',$lngarr); ?>";
			arr = arr.split(',');
			
			arr.forEach(function (id){
				
				document.getElementById(id).innerHTML = alrtstr + document.getElementById(id).innerHTML;
			});
		}
		
	</script>