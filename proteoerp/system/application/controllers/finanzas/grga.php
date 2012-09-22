<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Grga extends Controller {
	var $mModulo='GRGA';
	var $titp='Grupo de Gastos';
	var $tits='Grupo de Gastos';
	var $url ='finanzas/grga/';

	function Grga(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_id('NNN',1);
	}

	function index(){
		if ( !$this->datasis->iscampo('grga','id') ) {
			$this->db->simple_query('ALTER TABLE grga DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE grga ADD UNIQUE INDEX grupo (grupo)');
			$this->db->simple_query('ALTER TABLE grga ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};
		$this->datasis->modintramenu( 680, 450, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$bodyscript = '
<script type="text/javascript">
$(function() {
	$( "input:submit, a, button", ".otros" ).button();
});

jQuery("#a1").click( function(){
	var id = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\'/proteoerp/formatos/ver/GRGA/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
<div class="anexos">

<table id="west-grid" align="center">
	<tr>
		<td><div class="tema1"><table id="listados"></table></div></td>
	</tr>
	<tr>
		<td><div class="tema1"><table id="otros"></table></div></td>
	</tr>
</table>

<table id="west-grid" align="center">
	<tr>
		<td></td>
	</tr>
</table>
</div>
'.
//		<td><a style="width:190px" href="#" id="a1">Imprimir Copia</a></td>
'</div> <!-- #LeftPane -->
';

		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->
';
		$param['WestPanel']  = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados'] = $this->datasis->listados('GRGA', 'JQ');
		$param['otros']    = $this->datasis->otros('GRGA', 'JQ');
		$param['tema1']     = 'darkness';
		$param['anexos']    = 'anexos1';
		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "true";
		$link  = site_url('ajax/buscacpla');

		$grid  = new $this->jqdatagrid;

		$grid->addField('grupo');
		$grid->label('Grupo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 4 }',
		));


		$grid->addField('nom_grup');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 25 }',
		));


		$grid->addField('cu_inve');
		$grid->label('Cta. Contable');
		$grid->params(array(
			'width'         => 120,
			'frozen'        => 'true',
			'editable'      => 'true',
			'edittype'      => "'text'",
			'editoptions'   => '{'.$grid->autocomplete($link, 'cu_inve','cucucu','<div id=\"cucucu\"><b>"+ui.item.descrip+"</b></div>').'}',
			'search'        => 'false'
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'    => "'center'",
			'frozen'   => 'true',
			'width'    => 60,
			'editable' => $editar,
			'search'   => 'false',
			'hidden'   => 'true'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('235');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 400, height:180, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 400, height:180, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(true);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(false);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdata()
	{
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('grga');

		$response   = $grid->getData('grga', array(array()), array(), false, $mWHERE, 'grupo' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$grupo = $this->input->post('grupo');
				$this->db->insert('grga', $data);
				echo "Registro Agregado";

				logusu('GRGA',"Registro ".$grupo." INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$grupo = $this->input->post('grupo');
			unset($data['grupo']);
			$this->db->where('id', $id);
			$this->db->update('grga', $data);
			logusu('GRGA',"Registro ".$grupo." MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			$grupo = $this->input->post('grupo');
			$check =  $this->datasis->dameval("SELECT COUNT(*) FROM mgas WHERE grupo='$grupo'");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM grga WHERE id=$id ");
				logusu('GRGA',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}
}

/*
class Grga extends validaciones {
	
	function grga(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		if ( !$this->datasis->iscampo('grga','id') ) {
			$this->db->simple_query('ALTER TABLE grga DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE grga ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE grga ADD UNIQUE INDEX grupo (grupo)');
		}
		$this->datasis->modulo_id(510,1);
		$this->grgaextjs();

	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro por Grupo', 'grga');
		$filter->grupo = new inputField('Grupo', 'grupo');
		$filter->grupo->size=6;

		$filter->nom_grup = new inputField('Descripci&oacute;n','nom_grup');

		$filter->cu_inve = new inputField('Cuenta','cu_inve');
		$filter->cu_inve->size=15;
		$filter->cu_inve->like_side='after';

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$uri = anchor('finanzas/grga/dataedit/show/<#grupo#>','<#grupo#>');

		$grid = new DataGrid('Lista de Grupos de Gastos');
		$grid->order_by('grupo','asc');
		$grid->per_page = 20;

		$grid->column_orderby('Grupo',$uri,'grupo');
		$grid->column_orderby('Nombre del Grupo','nom_grup','nom_grup');
		$grid->column_orderby('Cuenta Contable','cu_inve','cu_inve');

		$grid->add('finanzas/grga/dataedit/create');
		$grid->build();

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		$data['title']   = '<h1>Grupos de Gastos</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load("dataedit");

		$edit = new DataEdit('Grupos de Gastos', 'grga');
		$edit->back_url = site_url('finanzas/grga/filteredgrid');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->grupo =     new inputField("Grupo", "grupo");
		$edit->grupo->mode="autohide";
		$edit->grupo->size = 6;
		$edit->grupo->rule = "trim|required|callback_chexiste";
		$edit->grupo->maxlength=5;

		$edit->nom_grup =  new inputField("Nombre del Grupo", "nom_grup");
		$edit->nom_grup->size = 35;
		$edit->nom_grup->rule = "trim|required";
		$edit->nom_grup->maxlength=25;

		$edit->cu_inve =   new inputField("Cuenta Contable", "cu_inve");
		$edit->cu_inve->size = 18;
		$edit->cu_inve->maxlength=15;
		$edit->cu_inve->rule = "trim|callback_chcuentac";

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
 
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Grupos de Gastos</h1>";        
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}

	function _post_insert($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grga',"GRUPO DE GASTOS $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grga',"GRUPO DE GASTOS $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grga',"GRUPO DE GASTOS $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('grupo');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM grga WHERE grupo='$codigo'");
		if ($check > 0){
			$grupo=$this->datasis->dameval("SELECT nom_grup FROM grga WHERE grupo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el grupo $grupo");
			return FALSE;
		}else {
		return TRUE;
		}
	}
	
	
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"grupo","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('grga');

		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);

		if ( count($sort) == 0 ) $this->db->order_by( 'grupo', 'asc' );
		
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $this->db->count_all('grga');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$grupo = $campos['grupo'];

		if ( !empty($grupo) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM grga WHERE grupo='$grupo'") == 0)
			{
				$mSQL = $this->db->insert_string("grga", $campos );
				$this->db->simple_query($mSQL);
				logusu('grga',"GRUPO DE GASTOS $grupo CREADO");
				echo "{ success: true, message: 'Grupo Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un grupo con ese Codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe un grupo con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$grupo = $campos['grupo'];
		unset($campos['grupo']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("grga", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('grga',"GRUPO DE GASTOS $grupo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Grupo Modificado -> ".$data['data']['grupo']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$grupo = $campos['grupo'];
		$check =  $this->datasis->dameval("SELECT COUNT(*) FROM mgas WHERE grupo='$grupo'");

		if ($check > 0){
			echo "{ success: false, message: 'Grupo de Gasto no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM grga WHERE grupo='$grupo'");
			logusu('grga',"GRUPO $grupo ELIMINADO");
			echo "{ success: true, message: 'Grupo de cliente Eliminado'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function grgaextjs(){
		$encabeza='GRUPOS DE GASTOS';
		$listados= $this->datasis->listados('grga');
		$otros=$this->datasis->otros('grga', 'grga');

		$urlajax = 'finanzas/grga/';
		$variables = "var mcuenta = ''";
		$funciones = "";
		$valida = "
		{ type: 'length', field: 'grupo',   min: 1 },
		{ type: 'length', field: 'nom_grup', min: 1 }
		";
		
		$columnas = "
		{ header: 'id',          width:  30, sortable: true, dataIndex: 'id' }, 
		{ header: 'Grupo',       width:  50, sortable: true, dataIndex: 'grupo',    field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Descripcion', width: 200, sortable: true, dataIndex: 'nom_grup', field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Cuenta',      width:  90, sortable: true, dataIndex: 'cu_inve',  field: { type: 'textfield' }, filter: { type: 'string' } }
	";

		$campos = "'id', 'grupo', 'nom_grup', 'cu_inve'";
		
		$camposforma = "
							{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							items: [
									{ xtype: 'textfield', fieldLabel: 'Grupo',       name: 'grupo',   allowBlank: false, width: 120, id: 'grupo' },
									{ xtype: 'textfield', fieldLabel: 'Descripcion', name: 'nom_grup', allowBlank: false, width: 400, }
								]
							},{
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [{
										xtype: 'combo',
										fieldLabel: 'Cuenta Contable',
										labelWidth:100,
										name: 'cu_inve',
										id:   'cu_inve',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: cplaStore,
										width: 400
									}
								]
							}
		";

		$titulow = 'Grupo de Gastos';

		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 250,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						
						if (registro) {
							mcuenta  = registro.data.cu_inve;
							cplaStore.proxy.extraParams.cuenta   = mcuenta ;
							cplaStore.load({ params: { 'cliente': registro.data.cliente, 'origen': 'beforeform' } });
							form.loadRecord(registro);
						} else {
							mcuenta  = '';
						}
					}
				}
";

		$stores = "
var cplaStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,
	autoSync: false,
	pageSize: 50,
	pruneModifiedRecords: true,
	totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'contabilidad/cpla/cplabusca',
		extraParams: {  'cuenta': mcuenta, 'origen': 'store' },
		reader: {
			type: 'json',
			totalProperty: 'results',
			root: 'data'
		}
	},
	method: 'POST'
});
		";
		$features = "features: [ filters],";
		$filtros = "var filters = { ftype: 'filters', encode: 'json', local: false }; ";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['stores']      = $stores;
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		
		$data['title']  = heading('Tabla de Bancos');
		$this->load->view('extjs/extjsven',$data);
		
	}
}
*/
?>