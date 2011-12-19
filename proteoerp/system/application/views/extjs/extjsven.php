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
	'Ext.data.*',
	'Ext.util.*',
	'Ext.state.*',
	'Ext.form.*',
	'Ext.window.MessageBox',
	'Ext.tip.*',
	'Ext.ux.CheckColumn',
	'Ext.toolbar.Paging'
]);

var registro;
var urlApp  = '<?php echo base_url(); ?>';
var urlAjax = '<?php echo base_url().$urlajax; ?>';

var mxs = ((screen.availWidth/2)-400);
var mys = ((screen.availHeight/2)-300);

//variables
<?php if (isset($variables)) echo $variables; ?>

//Funciones
<?php if (isset($funciones))   echo $funciones;   ?>

//Columnas del Modelo
var ex_Columnas = [<?php echo $columnas;   ?>];

// Define our data model
var ex_Modelo = Ext.regModel('ex_Modelo', {
	fields: [<?php echo $campos ?>],
	validations: [<?php echo $valida ?>],
	proxy: {
		type: 'ajax',
		noCache: false,
		api: {
			read   : urlAjax + 'grid',
			create : urlAjax + 'crear',
			update : urlAjax + 'modificar',
			destroy: urlAjax + 'eliminar',
			method: 'POST'
		},
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data',
			messageProperty: 'message',
			totalProperty: 'results'
		},
		writer: {
			type: 'json',
			root: 'data',
			writeAllFields: true,
			callback: function( op, suc ) {
				Ext.Msg.Alert('ParaBolas 1','Noooooooo');
			}
		},
		listeners: {
			exception: function( proxy, response, operation) {
				Ext.MessageBox.show({
					title: 'EXCEPCION REMOTA',
					msg: operation.getError(),
					icon: Ext.MessageBox.ERROR,
					buttons: Ext.Msg.OK
				});
			} else {
				if (win) {
					win.form.reset();
					win.this.onReset();
				}
			}
		}
	}
});

//Data Store
var storeData = Ext.create('Ext.data.Store', {
	model: 'ex_Modelo',
	pageSize: 50,
	autoLoad: false,
	autoSync: true,
	<?php if (isset($agrupar)) echo $agrupar; ?>
	method: 'POST',
	listeners: {
		write: function(mr,re, op) {
			Ext.Msg.alert('Aviso','Registro Guardado ');
		}
	}
});

<?php if (isset($stores)) echo $stores; ?>

var win;
// Main 
Ext.onReady(function(){
	function showContactForm() {
		if (!win) {
			// Create Form
			var writeForm = Ext.define('Entra.Form', {
				extend: 'Ext.form.Panel',
				alias:  'widget.writerform',
				result: function(res){ alert('Resultado');},
				requires: ['Ext.form.field.Text'],
				initComponent: function(){
					Ext.apply(this, {
						iconCls: 'icon-user',
						frame: true, 
						title: '<? echo $titulow ?>', 
						bodyPadding: 3,
						fieldDefaults: { labelAlign: 'right' },
						items: [<?php echo $camposforma ?>], 
						dockedItems: [
							{ xtype: 'toolbar', dock: 'bottom', ui: 'footer', 
							items: ['->',<?php echo $dockedItems ?>]
						}]
					});
					this.callParent();
				},
				setActiveRecord: function(record){
					this.activeRecord = record;
				},
				onSave: function(){
					var form = this.getForm();
					if (!registro) {
						if (form.isValid()) {
							storeData.insert(0, form.getValues());
							return;
						} else {
							Ext.Msg.alert('Forma Invalida','Algunos campos no pudieron ser validados<br>los mismos se indican con un cuadro rojo<br> corrijalos y vuelva a intentar');
							return;
						}
					} else {
						var active = win.activeRecord;
						if (!active) {
							Ext.Msg.Alert('Registro Inactivo ');
							return;
						}
						if (form.isValid()) {
							form.updateRecord(active);
							return;
						} else {
							Ext.Msg.alert('Forma Invalida','Algunos campos no pudieron ser validados<br>los mismos se indican con un cuadro rojo<br> corrijalos y vuelva a intentar');
							return;
						}
					}
					//form.reset();
					//this.onReset();
				},
				onReset: function(){
					this.setActiveRecord(null);
					storeData.load();
					win.hide();
				},
				onClose: function(){
					var form = this.getForm();
					form.reset();
					this.onReset();
				}<?php  if (isset($winmethod)) echo ",".$winmethod; ?>
			});

			win = Ext.widget('window', { <?php echo $winwidget ?> });
		}
		win.show();
	}

<?php if (isset($filtros)) echo $filtros; ?>

	// Create Grid 
	Ext.define('DataGrid', {
		extend: 'Ext.grid.Panel',
		alias: 'widget.writergrid',
		store: storeData,
		initComponent: function(){
			Ext.apply(this, {
				iconCls: 'icon-grid',
				frame: true,
				dockedItems: [{
					xtype: 'toolbar',
					items: [
						{iconCls: 'icon-add',    text: 'Agregar',                                     scope: this, handler: this.onAddClick   },
						{iconCls: 'icon-update', text: 'Modificar', disabled: true, itemId: 'update', scope: this, handler: this.onUpdateClick},
						{iconCls: 'icon-delete', text: 'Eliminar',  disabled: true, itemId: 'delete', scope: this, handler: this.onDeleteClick }
					]
				}],
				columns: ex_Columnas,
				// paging bar on the bottom
				bbar: Ext.create('Ext.PagingToolbar', {
					store: storeData,
					displayInfo: true,
					displayMsg: 'Pag No. {0} - Registros {1} de {2}',
					emptyMsg: "No se encontraron Registros."
				})
			});
			this.callParent();
			this.getSelectionModel().on('selectionchange', this.onSelectChange, this);
		},

		<?php if (isset($features)) echo $features; ?>
		onSelectChange: function(selModel, selections){
			this.down('#delete').setDisabled(selections.length === 0);
			this.down('#update').setDisabled(selections.length === 0);
			},
		
		onUpdateClick: function(){
			var selection = this.getView().getSelectionModel().getSelection()[0];
				if (selection) {
					registro = selection;
					showContactForm();
				}
			},
		onDeleteClick: function() {
			var selection = this.getView().getSelectionModel().getSelection()[0];
			Ext.MessageBox.show({
				title: 'Confirme', 
				msg: 'Esta seguro?', 
				buttons: Ext.MessageBox.YESNO, 
				fn: function(btn){ 
					if (btn == 'yes') { 
						if (selection) {
							storeData.remove(selection);
						}
						storeData.load();
					} 
				}, 
				icon: Ext.MessageBox.QUESTION 
			});  
		},
		onAddClick: function(){
			registro = null;
			showContactForm();
			storeData.load();
		}
	});

//////************ MENU DE ADICIONALES /////////////////
<?php echo $listados ?>


//////************ FIN DE ADICIONALES /////////////////

	Ext.create('Ext.Viewport', {
		layout: {type: 'border',padding: 5},
		defaults: { split: true	},
		items: [
			{
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
					},{
						title:'Otras Funciones',
						border:false,
						layout: 'fit',
						html: '<?php echo $otros ?>'
					}
					<?php if (isset($acordionf)) echo $acordionf; ?>
				]
			},{
				region: 'center',
				itemId: 'grid',
				xtype: 'writergrid',
				title: '<?php echo $titulow ?>',
				width: '98%',
				align: 'center'
			}
		]
	});

<?php if (isset($final)) echo $final; ?>

	storeData.load({ params: { start:0, limit: 30}});


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
