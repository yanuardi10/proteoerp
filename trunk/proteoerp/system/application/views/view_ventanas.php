<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=<?=$this->config->item('charset'); ?>" />
<title>Sistemas DataSIS</title>
<?=style("ventanas.css");?>
<?php if (isset($head))   echo $head;   ?>
<?php if (isset($script)) echo $script; ?>
</head>
<body>
<div id='encabe'></div>
<?php if(isset($smenu)) echo '<div id="smenu">'.$smenu.'</div>'; ?>
<div id='contenido'>
	<?php if (isset($title)) echo $title; ?>
	
	<table width="95%" border=0 align="center">
		<tr>
			<td valign=top><?php if (isset($lista)) echo $lista; ?></td>
			<td><?php if (isset($content)) echo $content; ?></td>
		</tr>
	</table>
	
	<div class="footer">
		<a href="#" onClick="window.close()">Cerrar</a>
		<p>Tiempo de la consulta {elapsed_time} seg | Proteo ERP </p>
	</div>
</div>
<?php if (isset($extras)) echo $extras; ?>
</body>
</html>