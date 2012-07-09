<?php
/**********************************************************************
* VIEW DE JQGRID CON LAYOUT UI, JQGRID, BLOCKOUT E IMPROMPTU
*
*  $encabeza     => Titulo de la ventana y modulo
*  $temas        => Arreglo con los temas UI a cargar
*  $jquerys      => Pluggins de JQuery que se quieran cargar
*  $LayoutStyle  => Scripts de Style adicionales
*  
*  
*  
**********************************************************************/

//Por compatibilidad con version anterior
if( isset($tema)  == false) $tema    = 'proteo';
if( isset($temas) == false) $temas[] = $tema;
if( isset($tema1))  $temas[] = $tema1;
if( isset($anexos)) $temas[] = $anexos;

$cintu = '
<table width="100%" bgcolor="#2067B5">
	<tr>
		<td align="left" width="80px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">'.$encabeza.'</h1></td><td align="left" width="100px" nowrap="nowrap"><font style="color:#FFFFFF;font-size:12px">Usuario: '.$this->secu->usuario().'<br/>'.$this->secu->getnombre().'</font></td><td align="right" width="28px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td>
	</tr>
</table>
';

if ( isset($LayoutStyle) == false ){
	$LayoutStyle = '
html, body {margin: 0; padding: 0; overflow: hidden; font-size: 75%;}
/*Splitter style */
#LeftPane  {padding: 2px; overflow: auto;}
#RightPane {padding: 2px; overflow: auto;}
.ui-layout-west .ui-jqgrid tr.jqgrow td { border-bottom: 1px solid;}
';
}

if ( isset($readyscript) == false ) $readyscript = '';

if( isset($WestSize) == false)  $WestSize = 212;
if( isset($onclick)  == false)  $onclick = '';

//Layout por defecto
if ( isset($readyLayout) == false ){
	$readyLayout = '
	$(\'body\').layout({
		minSize: 30,
		north__size: 60,
		resizerClass: \'ui-state-default\',
		west__size: '.$WestSize.',
		west__onresize: function (pane, $Pane){jQuery("#west-grid").jqGrid(\'setGridWidth\',$Pane.innerWidth()-2);},
		center__onresize: function (pane, $Pane) {
			jQuery("#newapi'.$grids[0]['gridname'].'").jqGrid(\'setGridWidth\',$Pane.innerWidth()-6);
			jQuery("#newapi'.$grids[0]['gridname'].'").jqGrid(\'setGridHeight\',$Pane.innerHeight()-'.$grids[0]['menosalto'].');
		}
	});
';
}

// Procesa los grids
if ( isset($grids) == false ) $grids = array();

$depgrids = '';
if ( count($grids) > 0 ){
	$i = 1;
	foreach ($grids as $gridi){
		$depgrids .= '

	var gridId'.$i.' = "#newapi'.$gridi['gridname'].'";
	var grid'.$i.'   = jQuery(gridId'.$i.').jqGrid({
		ajaxGridOptions : {type:"POST"},
			jsonReader : {
				root:"data",
				repeatitems: false
			}
			'.$gridi['onClick'].'
			'.$gridi['ondblClickRow'].'
		'.$gridi['table'].'
	})
	'.$gridi['pager'].'
';
		$i++;
	}
}


if (isset($listados)) {
	if (!empty($listados)) {

		$ListGrid ='
	//Listados del Modulo
	jQuery("#listados").jqGrid({
		datatype: "local",
		height: \'200\',
		colNames:["","Reporte", "Nombre"],
		colModel:[
			{name:"id",    index:"id",     width: 15},
			{name:"titulo",index:"titulo", width:165},
			{name:"nombre",index:"proteo", hidden:true}
		],
		multiselect: false,
		hiddengrid: true,
		width: 190,
		caption: "Reportes",
		ondblClickRow: function(id, row, col, e){ 
			var ret = $("#listados").getRowData(id); 
			window.open("<?php echo base_url() ?>reportes/ver/"+ret.nombre, "_blank", "width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400)),screeny=((screen.availWidth/2)-300)");
			}
	});
	'.$listados.'
	for(var i=0;i<=datalis.length;i++) jQuery("#listados").jqGrid(\'addRowData\',i+1,datalis[i]);
';
	} else $ListGrid = '';
} else $ListGrid = '';

if (isset($otros)) {
	if (!empty($otros)) {
		$OtrGrid='
	//Otras Funciones
	jQuery("#otros").jqGrid({
		datatype: "local",
		height: 100,
		colNames:["","Funciones","Nombre"],
		colModel:[
			{name:"id",    index:"id",     width:15},
			{name:"titulo",index:"titulo", width:165},
			{name:"proteo",index:"proteo", hidden:true}
		],
		multiselect: false,
		hiddengrid: true,
		width: 190,
		caption: "Funciones",
		ondblClickRow: function(id, row, col, e){ 
			var ret = $("#otros").getRowData(id); 
			window.open("<?php echo base_url() ?>reportes/ver/"+ret.nombre, "_blank", "width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400)),screeny=((screen.availWidth/2)-300)");
			}
	});
	'.$otros.'
	for(var i=0;i<=dataotr.length;i++) jQuery("#otros").jqGrid(\'addRowData\',i+1,dataotr[i]);
';
	} else $OtrGrid  = '';
} else $OtrGrid  = '';

/* ***************************************************************************************************************** */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $encabeza ?></title>

<!-- ESTILOS -->
<?php
//Array de Temas Adicionales
if ( isset($temas) ) {
	foreach( $temas as $temaco ){echo style('themes/'.$temaco.'/'.$temaco.'.css');}
}

echo "<!-- JQUERY -->";

echo phpscript('nformat.js');
echo script('jquery-min.js');
echo script('plugins/jquery.numeric.pack.js');
echo script('jquery-ui.custom.min.js');
echo script('jquery.ui.selectmenu.js');
echo style('jquery.ui.selectmenu.css');

if ( isset($jquerys) ) {
	foreach( $jquerys as $jq ){ echo script($jq); }
}


echo "<!-- Block Out -->";
echo script('plugins/jquery.blockUI.js'); 

echo "<!-- Impromptu -->";
echo script('jquery-impromptu.js');
echo style('impromptu/default.css');

echo "<!-- JQGRID -->";
echo style('themes/ui.jqgrid.css');
echo script('i18n/grid.locale-sp.js');
echo script('jquery.jqGrid.min.js');

echo "<!-- DATAGRID -->";
echo script('datagrid/datagrid.js');
echo style('../datagrid/datagrid.css');

echo "<!-- LAYOUT -->";
echo script('jquery.layout.js');

?>
<style>
<?php echo $LayoutStyle; ?>
</style>

<script type="text/javascript">
var base_url = '<?php echo base_url() ?>';
var site_url = '<?php echo site_url() ?>';
var url = '';

$(document).ready(function() {
	var lastsel2=0;
	var _cargo = "";
<?php
	echo $readyscript;

	//Layout por defecto
	echo $readyLayout;
	//Grids
	echo $depgrids;

	echo $ListGrid;
	echo $OtrGrid;

if (isset($funciones))  echo $funciones;

?>

});

<?php if (isset($postready))  echo $postready; ?>

//Funcion para bloquear y esperar
function esperar(url){
	$.blockUI({
		message: $('#displayBox'), 
		css: { 
			top:  ($(window).height() - 400) /2 + 'px', 
			left: ($(window).width()  - 400) /2 + 'px', 
			width: '300px' 
		}
	});
	$.get(url, function(data) {
		setTimeout($.unblockUI, 2); 
		$.prompt(data);
	});
	return false;
};

</script>
</head>

<body id="dt_proteo">

<div class="ui-layout-north" ><?php echo $cintu ?></div>

<?php echo (isset($WestPanel) == true)? $WestPanel:''; ?>

<?php 
if(isset($centerpanel) == true) {
	echo $centerpanel;
} else{?>
<div id="RightPane" class="ui-layout-center ui-helper-reset ui-widget-content" align="center"><!-- Tabs pane -->
	<table id="newapi<?php echo $grids[0]['gridname'];?>"></table> 
	<div   id="pnewapi<?php echo $grids[0]['gridname'];?>"></div>
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

<div id="displayBox" style="display:none" ><p>Disculpe por la espere.....</p><img  src="<?php echo base_url() ?>images/doggydig.gif" width="131px" height="79px"/></div>

</body>
</html>