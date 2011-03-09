<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?=$this->config->item('charset'); ?>" >
<title>ProteoERP<?php if(isset($title)) echo ':'.preg_replace('/<[^>]*>/', '', $title); ?></title>
<?=style("ventanas.css");?>
<?=style("masonry.css"); ?>
<?php if( !isset($tabla) )   $tabla=''; ?>

<?=script("jquery.js") ?>
<?=script("plugins/jquery.masonry.min.js"); ?>

<script type="text/javascript" charset=<?=$this->config->item('charset'); ?>">
$(window).load(function() {
	$('#maso').masonry({ singleMode: true,	itemSelector: '.box'});
});
</script>


<?php if (isset($head))   echo $head;   ?>
<?php if (isset($script)) echo $script; ?>

</head>
<body>
<div id='encabe'><?php if (isset($title)) echo $title; ?></div>
<?php if(isset($smenu)) echo '<div id="smenu">'.$smenu.'</div>'; ?>

<div id='contenido' align="center">
	<?php if (isset($content)) echo $content; ?>
	<div class="footer"></p><?php if (isset($pie)) echo $pie; ?></div>
</div>

<?php if (isset($extras)) echo $extras; ?>
</body>
</html>