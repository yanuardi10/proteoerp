<?php
class Nomina extends Controller {

	function Nomina(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(712,1);
		//redirect("nomina/nomina/filteredgrid");
		redirect("nomina/nomina/extgrid");
	}

	function extgrid(){
		$script = $this->nomiextjs();
		$data["script"] = $script;
		$data['title']  = heading('Nomina');
		$this->load->view('extjs/noco',$data);
	}


	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Nomina", 'nomina');
		
		$filter->nombre = new inputField("Nombre", "nombre");
		
		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('nomina/nomina/dataedit/show/<#id#>','<#numero#>');

		$grid = new DataGrid("Lista de Nomina");
		$grid->order_by("numero","asc");
		$grid->per_page = 10;
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Nombre","nombre");
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Formula","formula");
		$grid->column("Fecha","fecha");
		$grid->add("nomina/nomina/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Nomina</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit(){
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("clientes", "nomina");
		$edit->back_url = site_url("nomina/nomina/filteredgrid");
		//$edit->script($script, "create");
		//$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		$edit->numero =  new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		$edit->numero->size =10;
		$edit->numero->rule ="required";
		
		$edit->frecuencia = new dropdownField("Tipo de N&oacute;mina", "frecuencia");
		$edit->frecuencia->option("","");
		$edit->frecuencia->options(array("Q"=> "Quincenal","M"=>"Mensual","S"=>"Semanal"));
		$edit->frecuencia->style = "width:100px;";
		
		$edit->contrato = new dropdownField("Contrato", "contrato");
		$edit->contrato->option("","");
		$edit->contrato->options('SELECT codigo, nombre FROM noco');
		$edit->contrato->style = "width:300px;";

		$edit->depto = new dropdownField("Departamento", "depto");
		$edit->depto->option("","");
		$edit->depto->options('SELECT departa,descrip FROM depa');
		$edit->depto->style = "width:200px;";

		$edit->codigo = new dropdownField("C&oacute;digo", "codigo"); 
		//$edit->codigo->_dataobject->db_name="trim(codigo)";  
		$edit->codigo->option("","");
		$edit->codigo->options("SELECT codigo,concat(trim(apellido),' ',trim(nombre)) nombre FROM pers ORDER BY apellido");
		$edit->codigo->style = "width:100px;";
		$edit->codigo->mode="autohide";
		
		$edit->nombre =  new inputField("Nombre", "nombre");
		$edit->nombre->mode="autohide";
		$edit->nombre->maxlength=30;
		$edit->nombre->size=40;
		
		$edit->concepto = new dropdownField("Concepto", "concepto");
		$edit->concepto->option("","");
		$edit->concepto->options('SELECT concepto,descrip FROM conc ORDER BY descrip');
		$edit->concepto->style = "width:200px;";
		
		$edit->tipo =  new inputField("Tipo","tipo");
		$edit->tipo->option("A","A");
		$edit->tipo->option("D","D");
		$edit->tipo->mode="autohide";
		$edit->tipo->style = "width:50px;";
  
		$edit->descrip =  new inputField("T. Descripci&oacute;n", "descrip");
		$edit->descrip->mode="autohide";
		$edit->descrip->maxlength=35;
		$edit->descrip->size =45;
		
		$edit->grupo =  new inputField("Grupo", "grupo");
		$edit->grupo->maxlength=4;
		$edit->grupo->size =6;
		
		$edit->formula =  new inputField("Formula", "formula");
		$edit->formula->maxlength=120;
		$edit->formula->size =80;
		
		$edit->monto = new inputField("Monto","monto");
		$edit->monto->size =17;
		$edit->monto->maxlength=14;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='numeric';
			
		$edit->fecha =  new DateonlyField("fecha", "fecha","d/m/Y");
		$edit->fecha->size = 12;
	
		$edit->cuota =  new inputField("Cuota", "cuota");
		$edit->cuota->maxlength=11;
		$edit->cuota->size =13;
		$edit->cuota->css_class='inputnum';
		$edit->cuota->rule='integer';
		
		$edit->cuotat =  new inputField("Cuota Total", "cuotat");
		$edit->cuotat->maxlength=11;
		$edit->cuotat->size =13;
		$edit->cuotat->css_class='inputnum';
		$edit->cuotat->rule='integer';
		
		$edit->valor =  new inputField("Valor", "valor");
		$edit->valor->maxlength=17;
		$edit->valor->size =20;
		$edit->valor->css_class='inputnum';
		$edit->valor->rule='numeric';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;
		$data['title']   = "<h1>Nomina</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function grid() {
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"fecha","direction":"DESC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;
		
		$where = $this->datasis->extjsfiltro($filters);

		$this->db->_protect_identifiers=false;
		$this->db->select('a.numero, a.fecha, a.contrato, sum(a.valor*(a.valor>0)) asigna, ABS(sum(a.valor*(a.valor<0))) deduc, b.nombre noconom');

		$this->db->from('nomina a');
		$this->db->join('noco b', 'a.contrato=b.codigo');
		$this->db->groupby('a.numero');
		if (strlen($where)>1){$this->db->where($where);	}

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results =  $this->datasis->dameval("SELECT COUNT(*) FROM (SELECT numero FROM nomina GROUP BY numero) aaa");
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function gridtraba(){
		$nomina   = isset($_REQUEST['nomina'])  ? $_REQUEST['nomina']   :  0;
		if ($nomina == 0 ) $nomina = $this->datasis->dameval("SELECT MAX(numero) FROM nomina")  ;
		$mSQL = "SELECT codigo, nombre, sum(valor*(valor>0)*(MID(concepto,1,1)<>9)) asigna,  sum(valor*(valor<0)*(MID(concepto,1,1)<>9)) deduc, sum(valor*(MID(concepto,1,1)<>9)) saldo FROM nomina WHERE numero='$nomina' GROUP BY codigo";
		$query = $this->db->query($mSQL);
		$results =  $this->datasis->dameval("SELECT COUNT(*) FROM (SELECT codigo FROM nomina WHERE numero='$nomina' GROUP BY codigo) aaa");
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function gridconc(){
		$nomina   = isset($_REQUEST['nomina'])  ? $_REQUEST['nomina']   :  0;
		$codigo   = isset($_REQUEST['codigo'])  ? $_REQUEST['codigo']   :  0;
		if ($nomina == 0 ) $nomina = $this->datasis->dameval("SELECT MAX(numero) FROM nomina")  ;
		if ($codigo == 0 ) $codigo = $this->datasis->dameval("SELECT MIN(codigo) FROM nomina WHERE numero='$nomina'")  ;
		$mSQL = "SELECT concepto, descrip, valor*(valor>0) asigna, valor*(valor<0) deduc FROM nomina WHERE numero='$nomina' AND trim(codigo)='$codigo' ORDER BY concepto ";
		$query = $this->db->query($mSQL);
		$results =  $this->datasis->dameval("SELECT COUNT(*) FROM (SELECT codigo FROM nomina WHERE numero='$nomina' AND codigo='$codigo' GROUP BY concepto) aaa");
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}



//****************************************************************
//
//
//
//****************************************************************
	function nomiextjs(){
		$encabeza='<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">NOMINAS GUARDADAS</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';

		$listados= $this->datasis->listados('nomi');
		$otros=$this->datasis->otros('nomi', 'nomina/nomina');

		$script = "
<script type=\"text/javascript\">
var BASE_URL   = '".base_url()."';
var BASE_PATH  = '".base_url()."';
var BASE_ICONS = '".base_url()."assets/icons/';
var BASE_UX    = '".base_url()."assets/js/ext/ux';

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

var urlApp = '".base_url()."';
var numeroactual = '';

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

var mxs = ((screen.availWidth/2)-400);
var mys = ((screen.availHeight/2)-300);

var tipos = new Ext.data.SimpleStore({
    fields: ['abre', 'todo'],
    data : [ ['Q','Quincenal'],['S','Semanal'],['B','Bisemanal'],['M','Mensual'],['O','Otros'] ]
});

//Column Model
var NomiCol = 
	[
		{ header: 'Numero',       width:  60, sortable: true,  dataIndex: 'numero',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Fecha',        width:  70, sortable: true,  dataIndex: 'fecha',    field: { type: 'datefield' }, filter: { type: 'date' }}, 
		{ header: 'Contrato',     width:  70, sortable: true,  dataIndex: 'contrato', field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Asignaciones', width:  80, sortable: true,  dataIndex: 'asigna',   field: { type: 'textfield' }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Deducciones',  width:  80, sortable: true,  dataIndex: 'deduc',    field: { type: 'textfield' }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Nombre',       width: 250, sortable: true,  dataIndex: 'noconom',  field: { type: 'textfield' }, filter: { type: 'string' }}
	];

//Column Model
var TrabaCol = 
	[
		{ header: 'Codigo',     width:  50, sortable: true,  dataIndex: 'codigo',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Nombre',     width: 170, sortable: true,  dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Asignacion', width:  80, sortable: true,  dataIndex: 'asigna',   field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Deduccion',  width:  60, sortable: true,  dataIndex: 'deduc',    field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Saldo',      width:  80, sortable: true,  dataIndex: 'saldo',    field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}
	];

//Column Model
var ConcCol = 
	[
		{ header: 'Conc.',       width:  40, sortable: true,  dataIndex: 'concepto', field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Descripcion', width: 150, sortable: true,  dataIndex: 'descrip',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Asignacion',  width:  80, sortable: true,  dataIndex: 'asigna',   field: { type: 'textfield' }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Deduccion',   width:  60, sortable: true,  dataIndex: 'deduc',    field: { type: 'textfield' }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}
	];

var nomina = '';
Ext.onReady(function(){
	/////////////////////////////////////////////////
	// Define los data model
	// Contratos
	Ext.define('Nomi', {
		extend: 'Ext.data.Model',
		fields: ['numero', 'fecha','contrato','noconom','asigna','deduc'],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'nomina/nomina/grid',
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
	var storeNomi = Ext.create('Ext.data.Store', {
		model: 'Nomi',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	/////////////////////////////////////////////////
	// Define los data model
	// Contratos
	Ext.define('Traba', {
		extend: 'Ext.data.Model',
		fields: ['codigo', 'nombre', 'saldo', 'asigna', 'deduc' ],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'nomina/nomina/gridtraba',
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
	var storeTraba = Ext.create('Ext.data.Store', {
		model: 'Traba',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridTraba = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeTraba,
		title: 'Trabajadores',
		iconCls: 'icon-grid',
		frame: true,
		columns: TrabaCol
	});

	/////////////////////////////////////////////////
	// Define los data model
	// Contratos
	Ext.define('Conc', {
		extend: 'Ext.data.Model',
		fields: ['concepto', 'descrip', 'asigna', 'deduc' ],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'nomina/nomina/gridconc',
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
	var storeConc = Ext.create('Ext.data.Store', {
		model: 'Conc',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});


	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridConc = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeConc,
		title: 'Conceptos',
		iconCls: 'icon-grid',
		frame: true,
		columns: ConcCol
	});

	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridNomi = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeNomi,
		title: 'Nominas Guardadas',
		iconCls: 'icon-grid',
		frame: false,
		columns: NomiCol,
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		dockedItems: [{
			xtype: 'toolbar',
			items: [{iconCls: 'icon-delete', text: 'Eliminar',  disabled: true, itemId: 'delete', scope: this, handler: this.onDeleteClick } ]
		}],
		// paging bar on the bottom
		bbar: Ext.create('Ext.PagingToolbar', {
			store: storeNomi,
			displayInfo: false,
			displayMsg: 'Pag No. {0} - Reg. {1} de {2}',
			emptyMsg: 'No se encontraron Registros.'
		}),
		onSelectChange: function(selModel, selections){	down('#delete').setDisabled(selections.length === 0); },
		onDeleteClick: function() {
			var selection = this.getView().getSelectionModel().getSelection()[0];
			Ext.MessageBox.show({
				title: 'Confirme', 
				msg: 'Esta seguro?', 
				buttons: Ext.MessageBox.YESNO, 
				fn: function(btn){ 
					if (btn == 'yes') { 
						if (selection) {
							//storeNomi.remove(selection);
						}
						storeNomi.load();
					} 
				}, 
				icon: Ext.MessageBox.QUESTION 
			});  
		},
	});

	// Al cambiar seleccion de Nomina
	gridNomi.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridNomi.down('#delete').setDisabled(selectedRecord.length === 0);
			nomina = selectedRecord[0].data.numero;
			gridTraba.setTitle(nomina+' '+selectedRecord[0].data.noconom);
			storeTraba.load({ params: { nomina: nomina }});
			storeConc.load({ params: { nomina: nomina }});
		}
	});

	// update panel body on selection change
	gridTraba.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			storeConc.load({ params: { nomina: nomina, codigo: selectedRecord[0].data.codigo }});
			gridConc.setTitle(selectedRecord[0].data.nombre);
		}
	});


//////************ MENU DE ADICIONALES /////////////////
".$listados."

//////************ FIN DE ADICIONALES /////////////////

	var viewport = new Ext.Viewport({id:'simplevp', layout:'border', border:false, items:[{region: 'north',preventHeader: true,height: 40,minHeight: 40,html: '".$encabeza."'},{region:'west',width:190,border:false,autoScroll:true,title:'Lista de Opciones',	collapsible:true,split:true,collapseMode:'mini',layoutConfig:{animate:true},layout: 'accordion',items: [{title:'Listados',border:false,layout: 'fit',items: gridListado},{title:'Otras Funciones',border:false,layout: 'fit',html: '".$otros."'}]},{region:'east',	id: 'este',width:340,items: gridConc,border:false,preventHeader: true,collapsible:true	},{cls: 'irm-column irm-center-column irm-master-detail', region: 'center', title:  'center-title', layout: 'border', preventHeader: true, border: false, items: [{itemId: 'viewport-center-master',cls: 'irm-master',region: 'center',items: gridNomi},{itemId: 'viewport-center-detail',preventHeader: true,region: 'south',height: '40%',split: true, title: 'center-detail-title',margins: '0 0 0 0',items: gridTraba}]}]});
	storeNomi.load();
	storeTraba.load();
	storeConc.load();
});

</script>
";
		return $script;	

	}




	function _post_insert($do){
		$codigo=$do->get('numero');
		$nombre=$do->get('nombre');
		logusu('nomina',"NOMINA $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		$nombre=$do->get('nombre');
		logusu('nomina',"NOMINA $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		$nombre=$do->get('nombre');
		logusu('nomina',"NOMINA $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM pers WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"Personal con el codigo $codigo nombre $nombre ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>