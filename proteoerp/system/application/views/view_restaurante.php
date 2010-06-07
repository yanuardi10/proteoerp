<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=<?=$this->config->item('charset'); ?>" />
<title>Sistemas DataSIS</title>
<?php if (isset($head))   echo $head;   ?>
<?php if (isset($script)) echo $script; ?>
</head>
<body>
<div id='encabe'></div>
<div id='contenido'>
	<?php if (isset($title)) echo $title; ?>
	
	<table width="95%" border=0 align="center">
		<tr>
			<td>
				<?php 
					//print_r($mesas);
					foreach($mesas AS $mesa){
						echo $mesa['mesa']. $mesa['fecha']. $mesa['hora']. $mesa['mesonero'].image('mesa.png').'<br>';
					}
				?>
				</td>
		</tr>
	</table>
	
	<div class="footer">
		<a href="#" onclick="window.close()">Cerrar</a>
		<p>Tiempo de la consulta {elapsed_time} seg | Proteo ERP </p>
	</div>
</div>
<?php if (isset($extras)) echo $extras; ?>
</body>
</html>