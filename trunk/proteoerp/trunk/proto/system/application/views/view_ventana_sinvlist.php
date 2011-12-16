<html>
<head>
<meta http-equiv="Content-type"
	content="text/html; charset=<?=$this->config->item('charset'); ?>" />
<title><?=$this->datasis->traevalor("SISTEMA") ?></title>
<?=style("ventanas.css");?>
<?php if (isset($style))  echo $style;   ?>
<style type="text/css">

#caja {
	position: absolute;
	width: 98%;
	height:100%;
	display: none;
	padding: 5px;
	border: 0px solid #D0D0D0;
	background-color: #FFFFFF;
}
	#caja1 {
	position: absolute;
	width: 100%;
	height:100%;
	display: none;
	padding: 5px;
	border: 0px solid #D0D0D0;
	background-color: #FFFFFF;
}
#mostrar {
	display: block;
	width: 100%;
	height:100%;
	padding: 5px;
	border: 2px solid #D0D0D0;
	background-color: #F0F0F0;
}
#mostrar1{
	display: block;
	width: 100%;
	height:100%;
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
			$("#caja1").hide();
		}else{
			$("#caja").hide();
		}
		
		
		});
	$("#mostrar1").click(function(event) {
		estado1 = $("#caja1").css("display");
		
		//abre la ventana
		if(estado1 == "none"){
			$("#caja1").show();
			$("#caja").hide();
		}else{
			$("#caja1").hide();
			
		}
		
		
		});

});
</script>
</head>
<body>
<div id='encabe'>
<center><?php if (isset($title)) echo $title; ?></center>
</div>
<?php if(isset($smenu)) echo '<div id="smenu">'.$smenu.'</div>'; ?>

<div id='contenido'>

<table width="100%" border=0 align="center">
	<tr>
		<td>
			<div><a href="#" id="mostrar1">Agregar Por Selecci&oacute;n<?=image("", "#", array("border"=>"none")); ?></a>
			</div>
			<div id="caja1">
				<?php if (isset($content)) echo $content; ?>
			</div>
		</td>
	</tr>
	<?php if ($estado=='create'){?>
	<tr>
		<td>
			<div><a href="#" id="mostrar">Agregar Por Filtro<?=image("", "#", array("border"=>"none")); ?></a>
			</div>
			
				<div id="caja" >
					<iframe src="http://localhost/proteoerp/inventario/sinvlist/agregar/" width="100%" height="100%"></iframe>
				</div>
			
		</td>
	</tr>
	<?php }?>
	<tr >
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
