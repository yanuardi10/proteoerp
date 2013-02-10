<?php
$nombre = strtoupper($nombre);
$dia    = date('d'    ,$mkt);
$mes    = date('j'    ,$mkt);
$anio   = date('Y'    ,$mkt);
$nmes   = $meses[$mes];
$tmonto = '#'.htmlnformat($monto);
$smonto = '##'.htmlnformat($monto).'##';
$smonto = strtoupper(numletra($monto));

$ffecha = strtoupper("${ciudad} ${dia} de ${nmes}");
?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>Cheque</title>
<style type="text/css">
body {font-family: sans-serif; font-size: 10pt;margin:0px 0px 0px 0px; padding:0px 0px 0px 0px; }
div.absolute {
	#border: 2px dotted green;
	position: absolute;
	padding: 0px;
	text-align: center;
	vertical-align: middle;
}
</style>
</head>
<body>
<!--@size_paper 176x80-->

<div class="absolute" style="top: -20px; right: 20px; font-weight:bold; font-size: 12pt"><?php echo $tmonto; ?></div>

<div class="absolute" style="top: 30px; left: 60px; right: 5px;"><?php echo $nombre; ?></div>
<div class="absolute" style="top: 60px; left: 0px;  right: 5px; text-align:left;text-indent: 50px;font-size: 0.8em;line-height: 1.8em"><?php echo $smonto; ?></div>

<div class="absolute" style="top: 100px; left: 0px; right: 350px text-align: right;"><?php echo $ffecha; ?></div>
<div class="absolute" style="top: 100px; left: 265px;"><?php echo $anio; ?></div>

<?php if($endosable){ ?>
<div class="absolute" style="top: 170px; right:30px;font-size: 1.3em;">NO ENDOSABLE</div>
<?php } ?>
</body>
</html>
