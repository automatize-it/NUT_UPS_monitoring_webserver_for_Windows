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

<h2>Suspended UPSs list</h2>
<?php
		
	$mndbTmp = new mysqli('localhost', 'root', 'gfhjkm', 'suspndd_upss');

	if (!$mndbTmp) {
		die('Could not connect: ' . mysqli_error());
	}
			
	$upsnms = array();

	if ($result = $mndbTmp->query("SELECT table_name FROM information_schema.tables WHERE table_schema='suspndd_upss'")){

		while ($row = $result->fetch_array()){ 
				
			$upsnms[] = $mndbTmp->real_escape_string($row[0]);
		}
		
		/* free result set */
		$result->free();
	}

	echo "click to unsuspend<br><br>";
	
	foreach ($upsnms as $upsnm) {
		
		echo "<a href=\"ups_unsspnd.php?upsnm=$upsnm\">$upsnm</a><br>";
		echo "<br>";
	}
?>