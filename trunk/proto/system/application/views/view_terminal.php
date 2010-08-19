<html>
	<head>
	<title><?=$titu; ?></title>
	<?=$rapyd_head?>
	<script type="text/javascript" language="javascript">
		function cargar() {
			document.getElementById('numero').focus();
			document.getElementById('numero').value='';
		}
	</script>
	<style type="text/css">
	#tabledespa {
	 font-family: Lucida Grande, Verdana, Sans-serif;
	 font-size: 18px;
	 background:url(<?=site_url('images/despacha.png')?>) center center no-repeat;

	}
	</style>	
	</head>
	<body marginheight="0" marginheight="0" topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0" onload="cargar()">
		<?php echo $body?>
	</body>
</html> 