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
</style>

<h1 align="center">UPS web server (NUT based) suspended UPSs list</h1>
	<table class="maintbl tablesorter" id="tbl">
	<thead><tr>
		<th class="maintblhdr">NAME</th>
		<th class="maintblhdr">last HOST</th>
		<th class="maintblhdr">BATT V last/min</th>
		<th class="maintblhdr">BATT % last/min</th>
		<th class="maintblhdr">BATT DATE</th>
		<th class="maintblhdr">LOAD last/max</th>
		<th class="maintblhdr" title="On battery last start value">OB LSV</th>
		<th class="maintblhdr">LAST UPDATE</th>
	</tr></thead>
	<tbody>

	<?php
		
		include 'nutMonSqlAuth.php';
		
		//debug. Yes, lame
		function dtc( $data ) {
			$output = $data;
			if ( is_array( $output ) )
				$output = implode( ',', $output);

			echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
		}
		
		$mndb = nutMonSqlAuthEnh('W','suspndd_upss');

		if (!$mndb) {
			die('Could not connect: ' . mysqli_error());
		}
				
		$upsnms = array();

		if ($result = $mndb->query("SELECT table_name FROM information_schema.tables WHERE table_schema='suspndd_upss'")){

			while ($row = $result->fetch_array()){ 
					
				$upsnms[] = $mndb->real_escape_string($row[0]);
			}
			
			/* free result set */
			$result->free();
		}
		
		dtc($upsnms);
		
		foreach ($upsnms as $upsnm) {
			
			//SQL part
			$result = $mndb->query("SELECT * FROM `$upsnm` ORDER BY id DESC LIMIT 1");
			$keyarr = $result->fetch_assoc();
			$result->free();
			
			//keys order is important!
			$vlkeys = array('device.type','battery.voltage','battery.charge', 'input.voltage', 'battery.date', 'ups.load', 'ups.status', 'ts'); //'input.frequency', , 'ups.test.result', 'battery.runtime',
			
			//handling battery date
			$srvcbattstr = "battery.date";
			$srvcbattval = "";
			
			if ( array_key_exists('battery.mfr.date', $keyarr) && strtotime($keyarr['battery.mfr.date']) > strtotime($keyarr['battery.date']) ) { 
				
				$keyarr['battery.date'] = $keyarr['battery.mfr.date']; 
				$srvcbattstr = "battery.mfr.date";
			}
			
			$minv = NULL;
			$srvcbattval = $keyarr[$srvcbattstr];
			
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
			
			//$result = $mndb->query("SELECT MAX(`battery.charge`) AS obmax FROM (SELECT `battery.charge` FROM `$upsnm` WHERE `ups.status` LIKE 'OB%') AS tmp");
			$result = $mndb->query("SELECT `id`,`battery.charge` FROM `$upsnm` WHERE `ups.status` LIKE 'OB%' ORDER BY `id` DESC");
			
			$maxLastOB = 0;
			
			$i = 0;
			$tmparr = NULL;
			$tmparr = array();
			$zf = 0;
			
			while ($row = $result->fetch_array()){ 
				
				$tmparr[]=$row;
			}
			$result->free();
			
			if (!empty($tmparr)){
				
				//count last OB duration
				$i = 0;
				$zf = sizeof($tmparr);
								
				for ($i = 0; $i < $zf; $i++){
					if (($i+1) == $zf || (($tmparr[$i][0])-1) > $tmparr[$i+1][0]) {break;}
				}
				
				$tmptm = $tmparr[$i][0];
			}
			
			$result = $mndb->query("SELECT `battery.charge` FROM `$upsnm` WHERE `id` LIKE $tmptm");
			$tmp = $result->fetch_array();
			$result->free();
							
			$lastObStartBatt = $tmp[0];
			
			//HTML part
			
			echo "<tr class=\"border_bottom\">";
			
			echo "<td class=\"maincll mncll_tar\"><a href=\"upsUnsspnd.php?upsnm=$upsnm\">$upsnm</a></td>";
			echo "<td class=\"maincll\">{$keyarr['device.type']}</td>"; //
			echo "<td class=\"maincll\">{$keyarr['battery.voltage']} / $minv[0]</td>";
			echo "<td class=\"maincll\">{$keyarr['battery.charge']} / $minbch[0]</td>";
			echo "<td class=\"maincll\">{$keyarr['battery.date']}</td>";
			echo "<td class=\"maincll\">{$keyarr['ups.load']} / $maxl[0]</td>";
			echo "<td class=\"maincll\">$lastObStartBatt</td>";
			echo "<td class=\"maincll\">{$keyarr['ts']}</td>";
			
			echo "</tr>";
		}
	?>
	</tbody>