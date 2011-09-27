<?php
class Noco extends Controller {
	
	function noco(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		//$this->datasis->modulo_id(707,1);
		redirect("nomina/noco/extgrid");
	}

	function extgrid(){
		//$this->datasis->modulo_id(707,1);
		$script = $this->nocoextjs();
		$data["script"] = $script;
		$data['title']  = heading('Personal');
		//$data['head']   = $this->rapyd->get_head();
		//$data['content'] = '';
		$this->load->view('extjs/pers',$data);
	}
	
	function filtergrid() {		
		$this->rapyd->load("datagrid","datafilter");
		
		$filter = new DataFilter("Filtro de Contrato de Nomina",'noco');
		
		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=10;
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=40;
		
		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');
    
		$uri = anchor('nomina/noco/dataedit/show/<#codigo#>','<#codigo#>');
		$uri_2  = anchor('nomina/noco/dataedit/modify/<#codigo#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
    
		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."nomina/noco/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";
		
		$grid = new DataGrid($mtool);
		$grid->order_by("codigo","asc");
		$grid->per_page = 50;
		
		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("C&oacute;digo",$uri,'codigo');
		$grid->column_orderby("Tipo","tipo",'tipo');
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column_orderby("Observaci&oacute;n","observa1",'observa1');
		$grid->column_orderby("Observaci&oacute;n","observa2",'observa2');
		
		//$grid->add("nomina/noco/dataedit/create");
		$grid->build('datagridST');
		
		//************ SUPER TABLE ************* 
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
	headerRows : 1,
	onStart : function () {	this.start = new Date();},
	onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
	});
})();
//]]>
</script>
';
		$style ='
<style type="text/css">
.fakeContainer { /* The parent container */
    margin: 5px;
    padding: 0px;
    border: none;
    width: 740px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************


		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['extras']  = $extras;		

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;

		$data['title']  = heading('Contratos');
		$data['script'] = script('jquery.js');
		$data["script"].= script('superTables.js');
		$data['head']   = $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit(){
 		$this->rapyd->load('dataobject','datadetails');
 		$modbus=array(
			'tabla'   =>'conc',
			'columnas'=>array(
				'concepto' =>'Concepto',
				'tipo'=>'tipo',
				'descrip'=>'Descripci&oacute;n',
 				'grupo'=>'Grupo'),
			'filtro'  =>array('concepto'=>'C&ocaute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('concepto'=>'concepto_<#i#>','descrip'=>'descrip_<#i#>',
								'tipo'=>'it_tipo_<#i#>','grupo'=>'grupo_<#i#>'),
			'titulo'  =>'Buscar Cconcepto',
			'p_uri'=>array(4=>'<#i#>')
			);
 		$btn=$this->datasis->p_modbus($modbus,'<#i#>');
 		
		$do = new DataObject("noco");
		$do->rel_one_to_many('itnoco', 'itnoco', array('codigo'));
		
		$edit = new DataDetails('Contratos', $do);
		$edit->back_url = site_url('nomina/noco/index');
		$edit->set_rel_title('itnoco','Contratos <#o#>');

		//$edit->pre_process('insert' ,'_pre_insert');
		//$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size = 10;
		$edit->codigo->rule= "required";
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength=8;
		
		$edit->nombre  = new inputField("Nombre", "nombre");
		$edit->nombre->maxlength=40;
		$edit->nombre->rule="required";
		$edit->nombre->size = 30;

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style="width:110px";
		$edit->tipo->option("S","Semanal");
		$edit->tipo->option("Q","Quincenal");
		$edit->tipo->option("M","Mensual");
		$edit->tipo->option("O","Otro");
		
		$edit->observa1  = new inputField("Observaciones", "observa1");
		$edit->observa1->maxlength=60;
		$edit->observa1->size = 60;
		
		$edit->observa2  = new inputField("Observaci&oacute;n", "observa2");
		$edit->observa2->maxlength=60;
		$edit->observa2->size = 60;
		
		
		//Campos para el detalle
		
		$edit->concepto = new inputField("C&oacute;ncepto <#o#>", "concepto_<#i#>");
		$edit->concepto->size=11;
		$edit->concepto->db_name='concepto';
		$edit->concepto->append($btn);
		$edit->concepto->readonly=TRUE;
		$edit->concepto->rel_id = 'itnoco';
		
		$edit->descrip = new inputField("Descripci&oacute;n <#o#>", "descrip_<#i#>");
		$edit->descrip->size=45;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=60;
		$edit->descrip->rel_id = 'itnoco';
		$edit->descrip->readonly=TRUE;
		
		$edit->it_tipo = new inputField("Tipo <#o#>", "it_tipo_<#i#>");
		$edit->it_tipo->size=2;
		$edit->it_tipo->db_name='tipo';
		$edit->it_tipo->rel_id = 'itnoco';
		$edit->it_tipo->readonly=TRUE;
		
		$edit->grupo = new inputField("Grupo <#o#>", "grupo_<#i#>");
		$edit->grupo->size=5;
		$edit->grupo->db_name='grupo';
		$edit->grupo->rel_id = 'itnoco';
		$edit->grupo->readonly=TRUE;

		//fin de campos para detalle

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();
		
		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_noco', $conten,true);
		$data['title']   = heading('Contratos de Nomina');
		$data['script']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$data["head"]   = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function _post_insert($do){
		$codigo=$do->get('codigo');
		logusu('noco',"Contrato de Nomina $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('codigo');
		logusu('noco',"Contrato de Nomina $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		logusu('noco',"Contrato de Nomina $codigo ELIMINADO");
	}
	function instala(){
		$sql="ALTER TABLE `noco`  ADD PRIMARY KEY (`codigo`)";
		$this->db->query($sql);	
	}

	function manoco(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : 'contrato';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;
		$id      = isset($_REQUEST['id'])     ? $_REQUEST['id']      : 1;

		$query = $this->db->query("SELECT id, CONCAT(RPAD(codigo,6,' '),nombre,' (',tipo,')') nombre FROM noco ORDER BY codigo");

		$results = $this->db->count_all('noco');
		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			// Genera el Detalle
			$detalle = $this->db->query("SELECT * FROM itnoco WHERE codigo='".SUBSTR($row['nombre'],0,5)."'");
			$darr = array();
			foreach ($detalle->result_array() as $drow)
			{
				$dmeco = array();
				foreach( $drow as $didd=>$dcampo ) {
					$dmeco[$didd] = utf8_encode($dcampo);
				}
				$dmeco['leaf'] = true;
				$darr[] = $dmeco;
			}
			$meco['children'] = $darr;
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', children:'.json_encode($arr).'}';
	}

	function itnoco(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : 'contrato';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$this->db->select('*');
		$this->db->from('itnoco');
		$this->db->order_by( 'codigo' );
		$query = $this->db->get();
		$results = $this->db->count_all('itnoco');

		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', detalle:'.json_encode($arr).'}';
	}


	function nocoextjs(){

		$encabeza='<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">CONTRATOS DE NOMINA</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';

		$mSQL = "SELECT codigo, CONCAT(codigo,' ',nombre) nombre, tipo FROM noco WHERE tipo<>'O' ORDER BY codigo";
		$contratos = $this->datasis->llenacombo($mSQL);
		
		$script = "
<script type=\"text/javascript\">
var BASE_URL   = '".base_url()."';
var BASE_PATH  = '".base_url()."';
var BASE_ICONS = '".base_url()."assets/icons/';
var BASE_UX    = '".base_url()."assets/js/ext/ux';

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

Ext.require([
	'Ext.grid.*',
	'Ext.ux.grid.FiltersFeature',
	'Ext.data.*',
	'Ext.util.*',
	'Ext.tree.*',
	'Ext.state.*',
	'Ext.form.*',
	'Ext.window.MessageBox',
	'Ext.tip.*',
	'Ext.ux.CheckColumn',
	'Ext.toolbar.Paging'
]);

var registro;
var urlApp = '".base_url()."';

Ext.define('Noco', {
    extend: 'Ext.data.Model',
    fields: ['id', 'codigo','tipo','nombre','observa1','observa2'],
    proxy: {
        type: 'rest',
        url : urlApp + 'nomina/noco/manoco',
        reader: {
            type: 'json',
            root: 'maestro'
        }
    },
    hasMany: { model: 'ItNoco', name: 'itnoco' }
});

Ext.define('ItNoco', {
    extend: 'Ext.data.Model',
    fields: ['id', 'codigo', 'concepto', 'descrip','tipo','grupo'],
    proxy: {
        type: 'rest',
        url : urlApp + 'nomina/noco/itnoco',
        reader: {
            type: 'json',
            root: 'detalle'
        }
    },
    belongsTo: 'Noco'
});

//Column Model
var NocoCol = 
	[
		{ header: 'Codigo',   width:  60, sortable: true, dataIndex: 'codigo',   field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Tipo',     width:  60, sortable: true, dataIndex: 'tipo',     field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Nombre',   width: 250, sortable: true, dataIndex: 'nombre', field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Observa1', width: 250, sortable: true, dataIndex: 'observa1', field:  { type: 'textfield' }, filter: { type: 'string'  } }
	];


//Column Model
var ItNocoCol = 
	[
		{ header: 'Codigo',   width:  60, sortable: true, dataIndex: 'codigo',   field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Concepto', width:  60, sortable: true, dataIndex: 'concepto', field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Descripcion',   width: 250, sortable: true, dataIndex: 'descrip', field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'tipo', width:  60, sortable: true, dataIndex: 'tipo', field:  { type: 'textfield' }, filter: { type: 'string'  } },
		{ header: 'Grupo', width:  60, sortable: true, dataIndex: 'grupo', field:  { type: 'textfield' }, filter: { type: 'string'  } }
	];

//Data Store
var storeNoco = Ext.create('Ext.data.Store', {
	model: 'Noco'
});

var storeItNoco = Ext.create('Ext.data.Store', {
	model: 'ItNoco'
});


//Ext.require('Ext.data.Store');
Ext.onReady(function() {

/*
	// Loads Contrato with ID 1 and related posts and comments using contrato's Proxy
	Noco.load(1, {
		success: function(contrato) {
			console.log('Contrato: ' + contrato.get('codigo'));

			// loop through conceptos and print out the comments
			contrato.posts().each(function(post) {
				console.log('Conceptos: ' + post.get('codigo'));
				post.detalle().each(function(detalle) {
					console.log(detalle.get('nombre'));
				});

				// get the user reference from the post's belongsTo association
					//post.getUser(function(user) {
					console.log('Just got the user reference from the post: ' + user.get('name'))
				});
	
				// try to change the post's user
				//post.setUser(100, {
				//	callback: function(product, operation) {
				//		if (operation.wasSuccessful()) {
				//			console.log('Post\'s user was updated');
				//		} else {
					//			console.log('Post\'s user could not be updated');
				//		}
				//	}
				//});

			});

			// create a new post
			user.posts().add({
				title: 'Ext JS 4.0 MVC Architecture',
				body: 'It\'s a great Idea to structure your Ext JS Applications using the built in MVC Architecture...'
			});
			// save the new post
			user.posts().sync();

		}
	});

*/

	//colocamos cualquier cosa
	// Create Grid 
	Ext.define('NocoGrid', {
		extend: 'Ext.grid.Panel',
		alias: 'widget.wnoco',
		store: storeNoco,
		initComponent: function(){
			Ext.apply(this, {
				iconCls: 'icon-grid',
				frame: true,
				columns: NocoCol,
				// paging bar on the bottom
				bbar: Ext.create('Ext.PagingToolbar', {
					store: storeNoco,
					displayInfo: true,
					displayMsg: 'Pag No. {0} - Registros {1} de {2}',
					emptyMsg: \"No se encontraron Registros.\"
				})
			});
			this.callParent();
			this.getSelectionModel().on('selectionchange', this.onSelectChange, this);
		}
	});

	// Create Grid 
	Ext.define('ItNocoGrid', {
		extend: 'Ext.grid.Panel',
		alias: 'widget.witnoco',
		store: storeItNoco,
		initComponent: function(){
			Ext.apply(this, {
				iconCls: 'icon-grid',
				frame: true,
				columns: ItNocoCol,
				// paging bar on the bottom
				bbar: Ext.create('Ext.PagingToolbar', {
					store: storeNoco,
					displayInfo: true,
					displayMsg: 'Pag No. {0} - Registros {1} de {2}',
					emptyMsg: \"No se encontraron Registros.\"
				})
			});
			this.callParent();
			this.getSelectionModel().on('selectionchange', this.onSelectChange, this);
		}
	});

/*
	Ext.create('Ext.tree.Panel', {
		title: 'Simple Tree',
		alias: 'widget.wnoco',
		//width: 200,
		//height: 150,
		store: storeNoco,
		rootVisible: false
		//renderTo: Ext.getBody()
	});
*/


	//Main Container
	var main = Ext.create('Ext.container.Container', {
		padding: '0 0 0 0',
		width: '100%',
		height: 600,
		renderTo: document.body,
		layout: {
			type: 'vbox',
			align: 'center'
		},
		items: [
			{
				xtype: 'panel',
				preventHeader: true,
				collapsible : true,
				html: '".$encabeza."',
				title: 'Busqueda Avanzada',
				width: '98%',
				layout: 'fit',
				viewConfig: { forceFit: true },
				flex: 1
			}
			,{
				itemId: 'grid1',
				xtype: 'wnoco',
				title: 'Contratos',
				width: '98%',
				align: 'center',
				flex: 9,
				store: storeNoco
			}
/*
			,{
				itemId: 'grid1',
				xtype: 'wnoco',
				title: 'Contratos',
				width: '98%',
				align: 'center',
				flex: 5,
				store: storeNoco
			}
			,{
				itemId: 'grid2',
				xtype: 'witnoco',
				title: 'Conceptos',
				width: '98%',
				align: 'center',
				flex: 7,
				store: storeItNoco
			}
*/
			]
	});
	Ext.EventManager.onWindowResize(main.doLayout, main);
	storeNoco.load({ params: { id:1}});


});
";



$script .="
</script>
";
		return $script;	
	}

}
?>