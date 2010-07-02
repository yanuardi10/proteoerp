<html>
<head>
<meta http-equiv="Content-type"
	content="text/html; charset=<?=$this->config->item('charset'); ?>" />
<title><?=$this->datasis->traevalor("SISTEMA") ?></title>
<?=style("ventanas.css");?>
<?php if (isset($style))  echo $style;   ?>
<style type="text/css">
<?php if($tabla=="") {?>
#caja {
	width: 100%;
	display: none;
	padding: 5px;
	border: 2px solid #D0D0D0;
	background-color: #FDF4E1;
}
<?php } else{?>
	#caja {
	width: 100%;
	display: block;
	padding: 5px;
	border: 2px solid #D0D0D0;
	background-color: #FDF4E1;
}	
<?php }?>
#mostrar {
	display: block;
	width: 100%;
	padding: 5px;
	border: 2px solid #D0D0D0;
	background-color: #F0F0F0;
}
</style>
<?php if (isset($head))   echo $head;   ?>
<?php if (isset($script)) echo $script; ?>

<script type="text/javascript">
$(function()
{	
	
	$("#mostrar").click(function(event) {
		estado = $("#caja").css("display");
		
		//abre la ventana
		if(estado == "none"){
			$("#caja").show();
		}else{
			$("#caja").hide();
		}
		
		
		});
//$("#mostrar").click(function(event) {event.preventDefault();$("#caja").slideToggle();});
//$("#caja a").click(function(event) {event.preventDefault();$("#caja").slideUp();});
});
</script>
</head>
<body>
<div id='encabe'>
<center><?php if (isset($title)) echo $title; ?></center>
</div>
<?php if(isset($smenu)) echo '<div id="smenu">'.$smenu.'</div>'; ?>

<div id='contenido'>

<table width="95%" border=0 align="center">
	<tr>
		<td></td>
		<td><?php if (isset($filtro)) { ?>
		<div><a href="#" id="mostrar">Filtro<?=image("", "#", array("border"=>"none")); ?></a>
		</div>
		<div id="caja"><?=$filtro.$tabla; ?></div>
		<?php } ?></td>
	</tr>
	<tr>
		<td valign=top><?php if (isset($lista)) echo $lista; ?></td>
		<td><?php if (isset($content)) echo $content; ?></td>
	</tr>
	<tr>
		<td colspan=4 align="center">
		<div class="footer">


		<p style='font-size: 10'>Tiempo de la consulta seg | Tortuga |<a
			href="#" onClick="window.close()">Cerrar</a></p>


		</div>
		</td>
	</tr>
</table>
		<?php if (isset($extras)) echo $extras; ?></div>
</body>
</html>
