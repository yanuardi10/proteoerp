<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?=$this->config->item('charset'); ?>" >
<title>ProteoERP<?php if(isset($title)) echo ':'.preg_replace('/<[^>]*>/', '', $title); ?></title>
<?=style("ventanas.css");?>
<?php if(!isset($tabla))   $tabla=''; ?>
<style type="text/css">
<?php if($tabla=="") {?>
#cajafiltro {width: 100%;display: block;padding: 5px;border: 2px solid #D0D0D0;background-color: #ECFCFC;}
<?php } else{?>
#cajafiltro {width: 100%;display: block;padding: 5px;border: 2px solid #D0D0D1;background-color: #FDFDFD;}	
<?php }?>
#mostrafiltro {display: block;width: 100%;padding: 5px;border: 2px solid #D0D0D0;background-color: #D0D0D0;}
</style>

<?=script("jquery.js") ?>

<?php if (isset($head))   echo $head;   ?>
<?php if (isset($script)) echo $script; ?>

<script type="text/javascript">
$(function(){
	$("#mostrafiltro").click(function(event) {
		estado = $("#cajafiltro").css("display");
		if(estado == "none"){ $("#cajafiltro").show();
		}else{ $("#cajafiltro").hide();		}
	});
	$("#cajafiltro").hide();
});
</script>
</head>
<body>
<div id='encabe'><?php if (isset($title)) echo $title; ?></div>
<?php if(isset($smenu)) echo '<div id="smenu">'.$smenu.'</div>'; ?>
<div id='contenido'>
	<table width="95%" border=0 align="center">
		<tr>
			<td></td>
			<td><?php if (isset($filtro)) { ?>
			<div><a href="#" id="mostrafiltro">Buscar y Filtrar <?=image("visible_16.png", "#", array("border"=>"none")); ?></a>
			</div>
			<div id="cajafiltro"><?=$filtro.$tabla; ?></div>
			<?php } ?></td>
		</tr>
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