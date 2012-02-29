<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>ProteoERP <?php if(isset($title)) echo ': '.preg_replace('/<[^>]*>/', '', $title); ?></title>
<?php if (isset($head))   echo $head;   ?>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/normal.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/ext/resources/css/ext-all.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/ext/ux/css/CheckHeader.css"/>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/ext/ux/grid/css/GridFilters.css"/> 
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/ext/ux/grid/css/RangeMenu.css" /> 

<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ext/ext-debug.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ext/locale/ext-lang-es.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ext/bootstrap.js"></script>

<?php
	$encabeza = '<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">'.$encabeza.'</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';
	if (!isset($dockedItems)){
		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";
	}
?>

<script type="text/javascript">
var BASE_URL   = '<?php echo base_url(); ?>';
var BASE_PATH  = '<?php echo base_url(); ?>';
var BASE_ICONS = '<?php echo base_url(); ?>assets/icons/';
var BASE_UX    = '<?php echo base_url(); ?>assets/js/ext/ux';

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

Ext.require([
	'Ext.grid.*',
	'Ext.ux.grid.FiltersFeature',
	//'Ext.data.*',
	//'Ext.util.*',
	//'Ext.state.*',
	//'Ext.form.*',
	//'Ext.window.MessageBox',
	//'Ext.tip.*',
	//'Ext.ux.CheckColumn',
	//'Ext.toolbar.Paging'
]);

var registro;
var urlApp  = '<?php echo base_url(); ?>';
var urlAjax = '<?php echo base_url().$urlajax; ?>';

var mxs = ((screen.availWidth/2)-400);
var mys = ((screen.availHeight/2)-300);

//Column Model Maestro
var MaestCol = [<?php echo $columnas ?>]

//Column Model Detalle de Presupuesto
<?php echo $coldeta ?>;

<?php echo $funciones ?>

// application main entry point
Ext.onReady(function() {
	Ext.QuickTips.init();
	/////////////////////////////////////////////////
	// Define los data model
	Ext.define('MaestMod', {
		extend: 'Ext.data.Model',
		fields: [<?php echo $campos ?>],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlAjax + 'grid',
				update : urlAjax + 'modificar',
				method: 'POST'
			},
			reader: {
				type: 'json',
				root: 'data',
				successProperty: 'success',
				messageProperty: 'message',
				totalProperty: 'results'
			}
		}
	});	

	//////////////////////////////////////////////////////////
	// create the Data Store
	var storeMaest = Ext.create('Ext.data.Store', {
		model: 'MaestMod',
		pageSize: 30,
		remoteSort: true,
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridMaest = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeMaest,
		title: '<?php echo $title ?>',
		iconCls: 'icon-grid',
		frame: true,
		columns: MaestCol,
		dockedItems:[<?php echo $dockedItems ?>],
		<?php echo $features ?>
		// paging bar on the bottom
		bbar: Ext.create('Ext.PagingToolbar', {
			store: storeMaest,
			displayInfo: false,
			displayMsg: 'Pag No. {0} - Reg. {1} de {2}',
			emptyMsg: 'No se encontraron Registros.'
		}),
	});

//////************ MENU DE ADICIONALES /////////////////
<?php echo $listados ?>
//////************ FIN DE ADICIONALES /////////////////

<?php echo $stores ?>

	var viewport = new Ext.Viewport({
		id:'simplevp',
		layout:'border',
		border:false,
		items:[{
			region: 'north',
			preventHeader: true,
			height: 40,
			minHeight: 40,
			html: '<?php echo $encabeza ?>'
		},{
			region:'west',
			width:200,
			border:false,
			autoScroll:true,
			title:'Lista de Opciones',
			collapsible:true,
			split:true,
			collapseMode:'mini',
			layoutConfig:{animate:true},
			layout: 'accordion',
			items: [
				<?php if (isset($acordioni)) echo $acordioni; ?>
				{
					title:'Listados',
					border:false,
					layout: 'fit',
					items: gridListado
				},
				{
					title:'Otras Funciones',
					border:false,
					layout: 'fit',
					html: '<?php echo $otros ?>'
				}
				<?php if (isset($acordionf)) echo $acordionf; ?>

			]
		},{
			cls: 'irm-column irm-center-column irm-master-detail',
			region: 'center',
			title:  'center-title',
			layout: 'border',
			preventHeader: true,
			border: false,
			items: [{
				itemId: 'viewport-center-master',
				cls: 'irm-master',
				region: 'center',
				items: gridMaest
			}
			<?php if (isset($grid2)) echo $grid2; ?>
			]	
		}]
	});
	storeMaest.load();
	<?php if (isset($final)) echo $final; ?>

});

</script>

<style type="text/css">
#divgrid1 {
	background: #e9e9e9;
	border: 1px solid #d3d3d3;
	margin: 20px;
	padding: 20px;
}
	.icon-user     { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user.png') !important;}
	.icon-user-add { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user_add.gif') !important;}
	.icon-save     { background-image: url('<?php echo base_url(); ?>assets/icons/fam/save.gif') !important;}
	.icon-reset    { background-image: url('<?php echo base_url(); ?>assets/icons/fam/stop.png') !important;}
	.icon-grid     { background-image: url('<?php echo base_url(); ?>assets/icons/fam/grid.png') !important;}
	.icon-add      { background-image: url('<?php echo base_url(); ?>assets/icons/fam/add.png') !important;}
	.icon-delete   { background-image: url('<?php echo base_url(); ?>assets/icons/fam/delete.png') !important;}
	.icon-update   { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user_gray.png') !important;}
	.icon-accept   { background-image: url('<?php echo base_url(); ?>assets/icons/fam/accept.png') !important;}
	.icon-cross    { background-image: url('<?php echo base_url(); ?>assets/icons/fam/cross.gif') !important;}
</style>

</head>
<body>
<?php if (isset($content)) echo $content; ?>
    
</body>

</html>
