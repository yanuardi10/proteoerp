<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=<?=$this->config->item('charset'); ?>" />
<title>Sistemas DataSIS</title>
<?=style("ventanas.css");?>
<style type="text/css">
#caja   {width:785px;display: none;padding:5px;border:2px solid #D0D0D0;background-color:#FDF4E1;}
#mostrar{display:block;width:785px;padding:5px;border:2px solid #D0D0D0;background-color:#F0F0F0;}
</style>
<?php if (isset($head))   echo $head;   ?>
<?php if (isset($script)) echo $script; ?>

<script type="text/javascript">
$(function()
{
	$("#mostrar").click(function(event) {
		Event.preventDefault();
		$("#caja").slideToggle();
	});
	$("#caja a").click(function(event) {
		Event.preventDefault();
		$("#caja").slideUp();
	});
});
</script>

</head>
<body>
<div id='encabe'></div>
<?php if(isset($smenu)) echo '<div id="smenu">'.$smenu.'</div>'; ?>
<div id='contenido'>
	<?php if (isset($title)) echo $title; ?>
	
	<table width="95%" border=0 align="center">
	<?php if (isset($filtro)){  ?>
		<tr>
			<td></td>
			<td>
				<div class="littletableheader"><a href="#" id="mostrar" >Busqueda Avanzada</a></div>
				<div style="display: none;" id="caja">
					<?php echo $filtro; ?>
				</div>
			</td>				
		</tr>
	<?php }; ?>
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