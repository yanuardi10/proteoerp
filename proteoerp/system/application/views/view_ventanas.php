<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?=$this->config->item('charset'); ?>" >
<title>ProteoERP<?php if(isset($title)) echo ':'.preg_replace('/<[^>]*>/', '', $title); ?></title>
<?=style("ventanas.css");?>
<?php if( !isset($tabla) )   $tabla=''; ?>
<style type="text/css">
<?php if($tabla=="") {?>
#cajafiltro {width: 100%;display: block;padding: 5px;border-bottom: 1px solid #2067B5;background-color: #D7DEF0;}
<?php } else{?>
#cajafiltro {width: 100%;display: block;padding: 5px;border-bottom: 1px solid #2067B5;background-color: #D7DEF0;}	
<?php }?>
#mostrafiltro {display: block;width: 100%;padding: 5px;border-bottom: 1px solid #2067B5;background-color: #F5F5F5;background:url(<?=base_url();?>images/huellaazul.gif);}
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
<div id='encabe'><?php if (isset($title)) echo '<table width="98%"><tr><td>'.$title.'</td><td align="right" width="40">'.image('cerrar.png','Cerrar Ventana',array('onclick'=>'window.close()','height'=>'20px')).'</td></tr></table>'; ?></div>
<?php if(isset($smenu)) echo '<div id="smenu">'.$smenu.'</div>'; ?>
<div id='contenido'>
	<table width="95%" border=0 align="center">
		<tr>
			<td></td>
			<td background="<?=base_url();?>images/huellaazul.gif"  ><?php if (isset($filtro)) { ?>
			<div><a style="text-decoration:none;color:#4F1010;font: bold 18px Verdana;" href="#" id="mostrafiltro"><?=image("huella.jpg", "#", array("border"=>"none")); ?> Buscar y Filtrar </a>
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
		<p>Tiempo de la consulta {elapsed_time} seg | Proteo ERP </p>
	</div>
</div>

<?php if (isset($extras)) echo $extras; ?>
</body>
</html>