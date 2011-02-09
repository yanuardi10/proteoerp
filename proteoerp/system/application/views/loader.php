<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php // <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> ?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?=$this->config->item('charset'); ?>" />
<meta http-equiv="Content-Language" content="en-us" />
<meta name="ROBOTS" content="NONE" />
<meta name="MSSmartTagsPreventParsing" content="true" />
<meta name="Keywords" content="<?=property('app_keywords')?>" />
<meta name="Description" content="<?=property('app_description')?>" />
<meta name="Copyright" content="<?=property('app_copyright')?>" />
<title><?php echo property('app_title')?></title>
<?php echo style("estilos.css");  ?>
<?php echo style("menutab.css");  ?>
<?php echo style("acordeon.css"); ?>
<?php echo style("masonry.css"); ?>

<?php echo script("jquery.js"); ?>
<?php echo script("plugins/myAccordion.js"); ?>
<?php echo script("plugins/interface.js"); ?>
<?php echo script("jquery.masonry.min.js"); ?>


<script type="text/javascript" charset=<?=$this->config->item('charset'); ?>">
$(document).ready(function() {
	$("#accordion").myAccordion({
		speed: "fast", // @param : low, medium, fast
		defautContent: 0 // @param : number
	});
	
	$("a[name='_mp']").click(function () {
		$("a[name='_mp']").removeClass("current");
		url=this.href;
		pos=url.lastIndexOf('/');
		carga=url.substring(pos);
		$('#accordion').load('<?php echo site_url('bienvenido/accordion') ?>'+carga,'',function() {
		$('#accordion').myAccordion({ speed: 'fast', defautContent: 0 });
		});
		$(this).addClass("current");
		$('#tumblelog').load('<?php echo site_url('bienvenido/cargapanel') ?>'+carga );
		return false;
	});

	//$('a').ToolTip(
	//	{
	//	className: 'inputsTooltip',
	//	position: 'mouse',
	//	alpha: 0.80,
	//	delay: 200
	//	}
	//);
});

</script>

</head>
<body>
	<div id="container">
	<?php $this->load->view($data['settings']['default'].'/'.$data['settings']['commons']."header", $data); ?>

		<table border='0' cellpadding='0' cellspacing='0' width='100%'>
			<tr>
				<td  valign='top' id='tablemenu'>
					<div id='micelanias'>
					<?php echo $smenu ?>
					</div>
				</td>
				<td  valign='top'>
				
					<?php $this->load->view($data['settings']['default'].'/'.$data['settings']['content'].$view,  $data); ?>
				
				</td>
			</tr>
		</table>

		<table width="100%" border=0 cellspacing=0 cellpadding=0>
		    <tr>
			<td width='178px'><div id="pielateral"><p style="font-size:10px"><?php echo "Conectado a: ".$this->db->database; ?></p></div></td>
			<td>
			    <div id="pie"><p style="font-size:8px"><?php echo $copyright ?></p><?php echo image('codeigniter.gif'); ?>
				<?=image('php-power-micro.png')?>
				<?=image('jquery-icon.png')?>
				<?=image('mysqlpowered.png')?>
				<?=image('buttongnugpl.png')?>
			    </div>
			</td>
			<td width='150px' ><div id="pielateral"><p style="font-color:white">
			<a href='javascript:void(0);' onclick="window.open('/proteoerp/chat', 'wchat', 'width=580,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+((screen.availWidth/2)-290)+',screeny='+((screen.availHeight/2)-300)+'');" style="font-color:white;">Chat</a></p></div></td>
		    </tr>
		</table>
	</div>
</body>
</html>
