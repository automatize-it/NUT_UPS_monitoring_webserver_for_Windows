<!-- REMEMBA THE SICK STUFF PROMISED? LETS CONTINUE!-->
<?php
	
	session_start();
	
	$unavhsts = $_SESSION['hstarr'];
	$avhsts = array();
		
	foreach($unavhsts as $hst){
		
		$fp = @fsockopen($hst,139,$errCode,$errStr,1);
		
		if($fp){ $avhsts[] = $hst; fclose($fp);	}
	}
?>

<script type="text/javascript">
	
	var unavl_amnt = <?php echo sizeof($avhsts); ?>;
	if (unavl_amnt > 0){
		
		let arr = "<?php echo implode(',',$avhsts); ?>";
		arr = arr.split(',');
		
		for (let i = 0; i < arr.length; i++){
				
			parent.document.getElementById(arr[i]).innerText += " ON!";
		}
	}
	parent.document.getElementById("hststt").innerText = "";
</script>
<!-- AND IT WORKS 10 SEC+ FASTER THAN PHP IN MAIN PHP SECTION -->