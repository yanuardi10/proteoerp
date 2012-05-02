<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<?php 
if( isset($tema) == false) {
	$tema = 'proteo';
} 
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $encabeza ?></title>


<!-- JQUERY -->
<?php echo style('themes/'.$tema.'/'.$tema.'.css'); ?>

<?php 
	if ( isset($tema1) ) 
	echo style('themes/'.$tema1.'/'.$tema1.'.css'); 
?>

<?php echo script('jquery-1.7.1.min.js') ?>
<?php echo script('jquery-ui-1.8.18.custom.min.js') ?>



<!-- Impromptu -->

<?php echo script('jquery-impromptu.js') ?>
<?php echo style('impromptu/default.css') ?>

<!-- JQGRID -->
<?php echo style('themes/ui.jqgrid.css') ?>
<?php echo script('i18n/grid.locale-sp.js') ?>
<?php echo script('jquery.jqGrid.min.js')  ?>

<!-- DATAGRID -->
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/default/datagrid/datagrid.css" />
<?php echo script('datagrid/datagrid.js')  ?>

<!-- LAYOUT -->
<?php echo script('jquery.layout.js') ?>

<style>
html, body {margin: 0; padding: 0; overflow: hidden; font-size: 75%;}
/*Splitter style */
#LeftPane {overflow: auto;}
#RightPane {padding: 2px;overflow: auto;}
.ui-tabs-nav li {position: relative;}
.ui-tabs-selected a span {padding-right: 10px;}
.ui-tabs-close {display: none;position: absolute;top: 3px;right: 0px;z-index: 800;width: 16px;height: 14px;font-size: 10px; font-style: normal;cursor: pointer;}
.ui-tabs-selected .ui-tabs-close {display: block;}
.ui-layout-west .ui-jqgrid tr.jqgrow td { border-bottom: 0px none;}
.ui-datepicker {z-index:1200;}
</style>

<script type="text/javascript">
var base_url = '<?php echo base_url() ?>';
var site_url = '<?php echo site_url() ?>';
var url = '';
var mGrid = '<?php echo $grid['gridname'] ?>';

$(document).ready(function() {
	$('body').layout({
		resizerClass: 'ui-state-default',
		<?php echo (isset($WestSize) == true)? $WestSize:''; ?>
		west__onresize: function (pane, $Pane) {
			jQuery("#west-grid").jqGrid('setGridWidth',$Pane.innerWidth()-2);
		},
		center__onresize: function (pane, $Pane) {
			jQuery("#newapi<?php echo $grid['gridname'];?>").jqGrid('setGridWidth',$Pane.innerWidth()-6);
			jQuery("#newapi<?php echo $grid['gridname'];?>").jqGrid('setGridHeight',$Pane.innerHeight()-<?php echo $grid['menosalto']?>);
		}
	});

	var maintab =jQuery('#tabs','#RightPane').tabs({
		add: function(e, ui) {
			// append close thingy
			$(ui.tab).parents('li:first')
				.append('<span class="ui-tabs-close ui-icon ui-icon-close" title="Close Tab"></span>')
				.find('span.ui-tabs-close')
				.click(function() {
					maintab.tabs('remove', $('li', maintab).index($(this).parents('li:first')[0]));
				});
				// select just added tab
			maintab.tabs('select', '#' + ui.panel.id);
		}
	});

	dtgLoadButton();

	var grid = jQuery("#newapi<?php echo $grid['gridname'];?>").jqGrid({
		ajaxGridOptions : {type:"POST"},
			jsonReader : {
				root:"data",
				repeatitems: false
			},
			ondblClickRow: function(id){
				var gridwidth = jQuery("#newapi<?php echo $grid['gridname'];?>").width();
				gridwidth = gridwidth/2;
				grid.editGridRow(id, {closeAfterEdit:true,mtype:'POST'});
				return;
			}

<?php echo $grid['table'];?>


	})
<?php echo $grid['pager'];?>;
<?php if (isset($grid1)) { ?>
	var grid1 = jQuery("#newapi<?php echo $grid1['gridname'];?>").jqGrid({
		ajaxGridOptions : {type:"POST"},
			jsonReader : {
				root:"data",
				repeatitems: false
			},
			ondblClickRow: function(id){
				var gridwidth = jQuery("#newapi<?php echo $grid1['gridname'];?>").width();
				gridwidth = gridwidth/2;
				grid1.editGridRow(id, {closeAfterEdit:true,mtype:'POST'});
				return;
			}
			<?php echo $grid1['table'];?>
	})
<?php echo $grid1['pager'];?>;
<?php }; ?>
<?php if (isset($grid2)) { ?>
	var grid2 = jQuery("#newapi<?php echo $grid2['gridname'];?>").jqGrid({
		ajaxGridOptions : {type:"POST"},
			jsonReader : {
				root:"data",
				repeatitems: false
			},
			ondblClickRow: function(id){
				var gridwidth = jQuery("#newapi<?php echo $grid2['gridname'];?>").width();
				gridwidth = gridwidth/2;
				grid2.editGridRow(id, {closeAfterEdit:true,mtype:'POST'});
				return;
			}
			<?php echo $grid2['table'];?>
	})
<?php echo $grid2['pager'];?>;
<?php }; ?>
<?php if (isset($funciones))  echo $funciones; ?>

<?php if (isset($listados)) { ?>
	jQuery("#listados").jqGrid({
		datatype: "local",
		height: 'auto',
		colNames:['','Reporte','Nombre'],
		colModel:[{name:'id',index:'id', width:10},{name:'titulo',index:'titulo', width:165}, {name:'nombre',index:'nombre', hidden:true}],
		multiselect: false,
		hiddengrid: true,
		width: 190,
		caption: "Reportes",
		ondblClickRow: function(id, row, col, e){ 
			var ret = $("#listados").getRowData(id); 
			window.open("<?php echo base_url() ?>reportes/ver/"+ret.nombre, "_blank", "width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400)),screeny=((screen.availWidth/2)-300)");
//window.open("/proteoerp/reportes/ver/PERSONA",                "_blank", "width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400)),screeny=((screen.availWidth/2)-300)");
			//alert("id="+id+" "+ret.nombre)
			}
	});

	<?php echo $listados ?>
	
for(var i=0;i<=datalis.length;i++)
	jQuery("#listados").jqGrid('addRowData',i+1,datalis[i]);

<?php } ?>



<?php if (isset($otros))  echo $otros; ?>


});
</script>

<?php
	$cintu = '<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="80px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">'.$encabeza.'</h1></td><td align="left" width="100px" nowrap="nowrap"><font style="color:#FFFFFF;font-size:12px">Usuario: '.$this->secu->usuario().'<br/>'.$this->secu->getnombre().'</font></td><td align="right" width="28px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';
?>

</head>
<body id="dt_proteo">

<div class="ui-layout-north" ><?php echo $cintu ?></div>

<?php echo (isset($WestPanel) == true)? $WestPanel:''; ?>

<?php 
if(isset($centerpanel) ==true) {
	echo $centerpanel;
} else{?>
<div id="RightPane" class="ui-layout-center ui-helper-reset ui-widget-content" align="center"><!-- Tabs pane -->
	<table id="newapi<?php echo $grid['gridname'];?>"></table> 
	<div   id="pnewapi<?php echo $grid['gridname'];?>"></div>
</div> <!-- #RightPane -->

<?php } ?>

<?php echo (isset($SouthPanel) == true)? $SouthPanel:''; ?>


<div id="dtg_dialog" title="Exporta Datos">
    <ul>
       <?php echo (isset($grid['export']['pdf'])   && $grid['export']['pdf']   == true)?"<li><a href='javascript:void(0);' onClick=\"dtgExport('pdf'," . $grid['querystring'] . ");\" >Pdf</a></li>":''; ?>
       <?php echo (isset($grid['export']['csv'])   && $grid['export']['csv']   == true)?"<li><a href='javascript:void(0);' onClick=\"dtgExport('csv'," . $grid['querystring'] . ");\" >Csv</a></li>":''; ?>
       <?php echo (isset($grid['export']['excel']) && $grid['export']['excel'] == true)?"<li><a href=\"#\">Excel</a></li>":''; ?>
       <?php echo (isset($grid['export']['print']) && $grid['export']['print'] == true)?"<li><a href=\"#\">Print</a></li>":''; ?>
       <?php echo (isset($grid['export']['xml'])   && $grid['export']['xml']   == true)?"<li><a href='javascript:void(0);' onClick=\"dtgExport('xml'," . $grid['querystring'] . ");\" >Xml</a></li>":''; ?>
    </ul>
</div>

<?php if(isset($bodyscript)) echo $bodyscript; ?>

</body>
</html>
