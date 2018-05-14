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
		width: 5%;
		background: #f0f0f0;
	}
	
	
</style>
	<h1 align="center">UPS web server (NUT based)</h1>
	<div align="center">
	<form action="ups_add.php" method="get">
		<input name="newups"/>
		<input type="submit" value="ADD NEW"/>
	</form>
	</div>
	<table class="maintbl">
	<tr>
		<th class="maintblhdr">NAME</th>
		<th class="maintblhdr">BATTERY %</th>
		<th class="maintblhdr">IN VOLTAGE</th>
		<th class="maintblhdr">IN FREQ</th>
		<th class="maintblhdr">BATT DATE</th>
		<th class="maintblhdr">CURR LOAD</th>
		<th class="maintblhdr">STATUS</th>
		<th class="maintblhdr">SLF-TST STAT</th>
		<th class="maintblhdr">LAST UPDATE</th>
		<th class="maintblhdr">slftst</th>
	</tr>
	
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
			
			//echo "<br>$upsnm"; 'battery.charge', 'input.voltage', 'input.frequency', 'battery.date', 'ups.load',
			//'ups.status', 'ups.test.result'
			$result = $mndb->query("SELECT * FROM `$upsnm` ORDER BY id DESC LIMIT 1");
			echo "<tr><td class=\"maincll\"><b>$upsnm</b></td>";
			
			$vlkeys = array('battery.charge', 'input.voltage', 'input.frequency', 'battery.date', 'ups.load', 'ups.status', 'ups.test.result','ts');
			
			$keyarr = $result->fetch_assoc();
				
			foreach ($vlkeys as $vlkey){
				echo "<td class=\"maincll\">$keyarr[$vlkey]</td>";
			}
			
				
			/* free result set */
			$result->free();
			$result->close();
			
			echo "<td class=\"maincll\"><button>SELF-TEST</td></tr>";
			echo "<tr><td colspan=\"10\"><hr width=\"85%\"></td></tr>";
		}
		
		mysqli_close($mndb);
	?>
	
	</table>
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