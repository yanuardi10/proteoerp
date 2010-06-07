<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=<?=$this->config->item('charset'); ?>" />
<title>Proteo ERP</title>
<link rel="stylesheet" href="<?=base_url()?>assets/default/css/rapyd.css" type="text/css" media="all" />
<?=$rapyd_head?>
</head>
<body>
<div class='encabe'>
<img src="/proteoerp/assets/default/css/templete_01.jpg" width="120" >
</div>
<div id="content">
	<div>Proteo ERP version 0.1  </div>
	<div class="line"></div>
	<div>
		<?=$content?>
		<div class="line"></div>
		<center><a href="#" onclick="window.close();">Cerrar</a></center>
	</div>
	<div class="line"></div>
	<div class="footer">
	<p>Tiempo de la consulta {elapsed_time} seg | Proteo ERP </p>
	</div>
    </div>
</body>
</html>