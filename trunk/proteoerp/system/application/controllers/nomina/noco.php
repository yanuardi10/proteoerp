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

		$query = $this->db->query("SELECT id, codigo, tipo, nombre, observa1, observa2 FROM noco ORDER BY codigo");

		$results = $this->db->count_all('noco');
		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			
			// Genera el Detalle
			/*
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
			$meco['detalle'] = $darr;
			*/
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', maestro:'.json_encode($arr).'}';
	}

	function manoco1(){
		$query = $this->db->query("SELECT nombre name, 'meco@gmail.com' email, ingreso start, sueldo salary, true active FROM pers ORDER BY nombre");
		$results = $this->db->count_all('pers');
		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', maestro:'.json_encode($arr).'}';
	}



	function itnoco(){
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : 'contrato';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;
		$codigo = isset($_REQUEST['codigo']) ? $_REQUEST['codigo']  : null;

		if ($codigo == null ) $codigo=$this->datasis->dameval("SELECT codigo FROM noco ORDER BY codigo LIMIT 1");
	
		$mSQL = "SELECT b.id, b.concepto, b.descrip, b.tipo, b.grupo FROM itnoco a JOIN conc b ON a.concepto=b.concepto WHERE a.codigo='$codigo'";
		$query = $this->db->query($mSQL);
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


	function conc(){
		$codigo = isset($_REQUEST['codigo']) ? $_REQUEST['codigo']  : null;
		if ($codigo == null ) $codigo=$this->datasis->dameval("SELECT codigo FROM noco ORDER BY codigo LIMIT 1");
		$mSQL = "SELECT id, concepto, descrip, tipo, grupo FROM conc WHERE concepto NOT IN (SELECT concepto FROM itnoco WHERE codigo='$codigo') ORDER BY concepto";
		$query = $this->db->query($mSQL);
		$results = $this->db->count_all('conc');
		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', conceptos:'.json_encode($arr).'}';
	}

	function modificanoco(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data;

		$codigo = $data['codigo'];
		$nombre = trim($data['nombre']);
		unset($campos['codigo']);

		//print_r($campos);
		$mSQL = $this->db->update_string("noco", $campos,"id='".$data['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('noco',"CONTRATOS $codigo NOMBRE  $nombre MODIFICADO");
		echo "{ success: true, message: 'Contrato Modificado'}";
	}

	function crearnoco(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data;
		$codigo = $data['codigo'];
		$nombre = trim($data['nombre']);

		if ( !empty($codigo) and !empty($nombre) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM noco WHERE codigo='$codigo'") == 0)
			{
				//print_r($campos);
				$mSQL = $this->db->insert_string("noco", $campos );
				$this->db->simple_query($mSQL);
				logusu('noco',"CONTRATOS $codigo NOMBRE  $nombre CREADO");
				echo "{ success: true, message: 'Contrato Modificado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un contrato con ese Codigo!!'}";
			}
			
		} else {
			//echo "{ success: false, message: 'Ya existe un contrato con ese Codigo!!'}";
			// No 
		}
	}

	function eliminarnoco(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		//print_r($data);
		$campos = $data;
		$codigo = $data['codigo'];
		$pers   = $this->datasis->dameval("SELECT COUNT(*) FROM pers   WHERE contrato='$codigo'");
		$nomina = $this->datasis->dameval("SELECT COUNT(*) FROM nomina WHERE contrato='$codigo' ");
		if ($pers == 0 and $nomina==0 ){
			//print_r($campos);
			$mSQL = "DELETE FROM noco WHERE codigo='$codigo'";
			$this->db->simple_query($mSQL);
			$mSQL = "DELETE FROM itnoco WHERE codigo='$codigo'";
			$this->db->simple_query($mSQL);
			logusu('noco',"CONTRATOS $codigo NOMBRE  $nombre ELIMINADO");
			echo "{ success: true, message: 'Contrato Eliminado'}";
		} else {
			echo "{ success: false, message: 'Contrato con Movimiento!!'}";
		}
		//echo "{ success: false, message: 'Contrato con Movimiento!!'}";
	}


	function modifitnoco(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		//$meco = json_encode(array('AAAA','010U','018','017'));

		$codigo = $data[0];
		//echo json_last_error();
		//print_r($data);
		
		// Borramos todo lo que tenga
		
		$mSQL = "DELETE FROM itnoco WHERE codigo='$codigo'";
		$this->db->simple_query($mSQL);
		$meco = "INSERT INTO itnoco (codigo, concepto, descrip, tipo, grupo) ";
		$meco .="SELECT '$codigo' codigo, concepto, descrip, tipo, grupo FROM conc WHERE concepto IN ( ";
		foreach( $data as $id=>$peo ){
			if($id > 0 ) {
				$meco .= "'$peo',";
			}			
		}
		$meco .= " 'XXXXXXXX' )";
		$this->db->simple_query($meco);
		//logusu('noco',"CONTRATOS $codigo NOMBRE  $nombre MODIFICADO");
		echo "{ success: true, message: 'Contrato Modificado' }";
	}


//****************************************************************8
//
//
//
//****************************************************************8
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

var urlApp = '".base_url()."';
var codigoactual = '';

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
	'Ext.toolbar.Paging',
	'Ext.dd.*'
]);

var tipos = new Ext.data.SimpleStore({
    fields: ['abre', 'todo'],
    data : [ ['Q','Quincenal'],['S','Semanal'],['B','Bisemanal'],['M','Mensual'],['O','Otros'] ]
});

//Column Model
var NocoCol = 
	[
		{ header: 'id', dataIndex: 'id', width: 40, hidden: false }, 
		{ header: 'Codigo',   width:  60, sortable: true,  dataIndex: 'codigo', field: { type: 'textfield' }, filter: { type: 'string' }, editor: { allowBlank: false }}, 
		{ header: 'Tipo',     width:  80, sortable: false, dataIndex: 'tipo',
		field: { xtype: 'combobox', triggerAction: 'all', valueField:'abre', displayField:'todo', store: tipos, listClass: 'x-combo-list-small'},	filter: { type: 'string' }, editor: { allowBlank: false }}, 
		{ header: 'Nombre',   width: 250, sortable: true,  dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string' }, editor: { allowBlank: true }}, 
		{ header: 'Observa1', width: 250, sortable: true,  dataIndex: 'observa1', field: { type: 'textfield' }, filter: { type: 'string' }, editor: { allowBlank: true }},
		{ header: 'Observa2', width: 250, sortable: true,  dataIndex: 'observa2', field: { type: 'textfield' }, filter: { type: 'string' }, editor: { allowBlank: true }}
	];


//Column Model
var ItNocoCol = 
	[
		{ header: 'id', dataIndex: 'id', width: 40, hidden: false }, 
		{ header: 'Concepto',    width:  60, sortable: true, dataIndex: 'concepto', field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Descripcion', width: 200, sortable: true, dataIndex: 'descrip',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'tipo',        width:  30, sortable: true, dataIndex: 'tipo',     field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Grupo',       width:  60, sortable: true, dataIndex: 'grupo',    field: { type: 'textfield' }, filter: { type: 'string' }}
	];

//Column Model
var ConcCol = 
	[
		{ header: 'id', dataIndex: 'id', width: 40, hidden: false }, 
		{ header: 'Concepto',      width:  60, sortable: true, dataIndex: 'concepto', field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Descripcion',   width: 200, sortable: true, dataIndex: 'descrip',  field:  { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'tipo',          width:  30, sortable: true, dataIndex: 'tipo',     field:  { type: 'textfield' }, filter: { type: 'string'  } },
		{ header: 'Grupo',         width:  60, sortable: true, dataIndex: 'grupo',    field:  { type: 'textfield' }, filter: { type: 'string'  } }
	];

Ext.onReady(function(){
	// Un poco de hackeo para asinar los textos...
	if (Ext.MessageBox) {
		var mb = Ext.MessageBox;
		mb.bottomTb.items.each(function(b) {
		b.setText(mb.buttonText[b.itemId]);
		});
	}  

	/////////////////////////////////////////////////
	// Define los data model
	// Contratos
	Ext.define('Noco', {
		extend: 'Ext.data.Model',
		fields: ['id', 'codigo','tipo','nombre','observa1','observa2'],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'nomina/noco/manoco',
				create : urlApp + 'nomina/noco/crearnoco',
				update : urlApp + 'nomina/noco/modificanoco' ,
				destroy: urlApp + 'nomina/noco/eliminarnoco',
				method: 'POST'
			},
			reader: {
				type: 'json',
				root: 'maestro'
			}
		}
	});

	Ext.define('ItNoco', {
		extend: 'Ext.data.Model',
		fields: [ 'id', 'codigo', 'concepto', 'descrip','tipo','grupo'],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'nomina/noco/itnoco',
				update : urlApp + 'nomina/noco/modifitnoco',
				create : urlApp + 'nomina/noco/modifitnoco',
				destroy: urlApp + 'nomina/noco/modifinoco',
				method: 'POST'
			},
			reader: {
				type: 'json',
				root: 'detalle'
			}
		}
	});

	Ext.define('conc', {
		extend: 'Ext.data.Model',
		fields: ['id', 'concepto', 'descrip','tipo','grupo'],
		proxy: {
			type: 'rest',
			url : urlApp + 'nomina/noco/conc',
			reader: {
				type: 'json',
				root: 'conceptos'
			}
		}
	});

	//////////////////////////////////////////////////////////
	// create the Data Store
	var storeNoco = Ext.create('Ext.data.Store', {
		model: 'Noco',
		autoLoad: false,
		autoSync: true,
		method: 'POST',
	});

	var storeItNoco = Ext.create('Ext.data.Store', {
		model: 'ItNoco',
		autoLoad: false,
		autoSync: false,
		method: 'POST'
	});

	var storeConc = Ext.create('Ext.data.Store', {
		model: 'conc'
	});

	var rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
		clicksToMoveEditor: 1,
		autoCancel: false
	});

	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridNoco = Ext.create('Ext.grid.Panel', {
		store: storeNoco,
		columns: NocoCol,
		width: '100%',
		height: '100%',
		title: 'Contratos Laborales',
		frame: true,
		tbar: [
			{
				text: 'Agregar',
				iconCls: 'icon-add',
				handler : function() {
					rowEditing.cancelEdit();
					// Create a model instance
					var r = Ext.create('Noco', {
						id: 0,
						codigo: '',
						tipo: 'Q',
						nombre: '',
						observa1: '',
						observa2: ''
					});
					storeNoco.insert(0, r);
					rowEditing.startEdit(0, 0);
				}
			},{
				itemId: 'eliminar',
				text: 'Eliminar',
				iconCls: 'icon-delete',
				handler: function() {
/*
					rowEditing.cancelEdit();
					Ext.Ajax.request({
						scope: this,
						url: '".base_url()."nomina/noco/eliminarnoco',
						params: { codigo: codigoactual },
						success: function () {
							//storeNoco.on('load', function () {
							//	gridNoco.getView().refresh();
							//}, this);
							//storeNoco.load();
							if ( storeNoco.getCount() > 0) {
								sm.select(0);
							};
							alert('exito');
							storeNoco.remove(sm.getSelection());
						},
						failure: function () {
							Ext.MessageBox.show({
								title: deleteFailedTitle,
								msg: deleteFailedMessage,
								buttons: Ext.MessageBox.OK
							});
						}
					});
				},
*/
					var sm = gridNoco.getSelectionModel();
					rowEditing.cancelEdit();

					storeNoco.remove(sm.getSelection());
					if ( storeNoco.getCount() > 0) {
						sm.select(0);
					};
				},
				disabled: true
			}
		],
		plugins: [rowEditing],
			listeners: {
				'selectionchange': function(view, records) {
					if ( records[0] ){
						storeItNoco.load({ params: { codigo: records[0].data.codigo }});
						storeConc.load({ params: { codigo: records[0].data.codigo }});
						gridNoco.down('#eliminar').setDisabled(!records.length);
						codigoactual = records[0].data.codigo;
					}
				}
			}
	});

	// Create Grid 
	var gridItNoco = Ext.create('Ext.grid.Panel', {
		alias: 'widget.witnoco',
		store: storeItNoco,
		//margins: '0 2 0 0',
		//stripeRows : true,
		columns: ItNocoCol,
		width: '100%',
		height: '100%',
		title: 'Conceptos del Contrato',
		iconCls: 'icon-grid',
		frame: true,
		viewConfig: {
			plugins: {
				ptype: 'gridviewdragdrop',
				dragGroup: 'witnocoDDGroup',
				dropGroup: 'wconcDDGroup'
			}
		}
	});

	// Create Grid 
	var gridConc = Ext.create('Ext.grid.Panel', {
		store: storeConc,
		alias: 'widget.wiconc',
		columns: ConcCol,
		width: '100%',
		height: '100%',
		title: 'Conceptos disponibles',
		frame: true,
		iconCls: 'icon-grid',
		multiSelect: true,
		//margins: '0 2 0 0',
		stripeRows : true,
		viewConfig: {
			plugins: {
				ptype: 'gridviewdragdrop',
				dragGroup: 'wconcDDGroup',
				dropGroup: 'witnocoDDGroup'
			}
		}
	});

	var boton = Ext.create('Ext.Button',
		{
		text: 'Guardar',
		autoWidth: false,
		width: 85,
		scale: 'large',
		handler: function() {
			var resultado = [];
			resultado.push(codigoactual);
			if (storeItNoco.count() > 0 ){
				storeItNoco.each( function(registro){
					resultado.push(registro.get('concepto'));
				});
				Ext.Ajax.request({
					url   : urlApp + 'nomina/noco/modifitnoco',
					params: Ext.encode(resultado) 
				});				
			}
			//alert('resultado '+resultado);
		}
	});

	Ext.create('Ext.Viewport', {
		layout: {type: 'border',padding: 5},
		defaults: { split: true	},
		items: [
			{
				region: 'north',
				//collapsible: true,
				preventHeader: true,
				//title: 'North',
				//split: true,
				height: 40,
				minHeight: 40,
				html: '".$encabeza."'
			},{
				region: 'center',
				layout: 'border',
				border: false,
				items: [ 
					{
						region: 'center',
						id:'areaNoco',
						cmargins: '0 0 0 0',
						collapsible: false,
						autoScroll:true,
						xtype: 'panel',
						items: gridNoco
					}
				]
			},{
				region: 'south',
				//collapsible: true,
				//split: true,
				preventHeader: true,
				height: 300,
				//minHeight: 120,
				//title: 'Conceptos asignados y asignables',
				layout: {type: 'border', padding: 5},
				items: [
					{
						//title: 'Pasa',
						preventHeader: true,
						//id:'areaConc',
						region: 'center',
						//html: 'Pasa'
						//layout: fit,
						xtype: 'panel',
						items: [
							{ html: '<p>Arrastre los conceptos de las grillas y luego presione el boton guardar</p>'},
							boton
						]
					},
					{
						//title: 'Detalle ',
						preventHeader: true,
						id:'areaConc',
						width: '450%',
						region: 'east',
						//minWidth: 350,
						//html: 'South Central'
						split: true,
						xtype: 'panel',
						items: gridConc
					},
					{
						preventHeader: true,
						region: 'west',
						id:'areaItNoco',
						width: '450%',
						//minWidth: 350,
						split: true,
						xtype: 'panel',
						items: gridItNoco
					}
				]
			}
		]
	});

	storeNoco.load();
	storeItNoco.load();
	storeConc.load();

});
</script>
";
		return $script;	
	}
}
?>