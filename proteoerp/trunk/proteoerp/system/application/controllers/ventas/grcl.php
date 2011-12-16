<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//grupocli
class Grcl extends validaciones {

	var $data_type = null;
	var $data = null;

	function grcl()	{
		parent::Controller(); 

		$this->load->helper('url');
		$this->load->helper('text');
		$this->datasis->modulo_id(132,1);
		$this->load->library("rapyd");

		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/ventas/". $this->uri->segment(2).EXT);
	}

	function index(){
		if ( !$this->datasis->iscampo('grcl','id') ) {
			$this->db->simple_query('ALTER TABLE grcl DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE grcl ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE grcl ADD UNIQUE INDEX grupo (grupo)');
		}
		$this->datasis->modulo_id(206,1);
		$this->grclextjs();
	}

	function test($id,$const)
	{
		return $id*$const;
	}
	function filteredgrid()
	{

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Buscar", 'grcl');
		
		$filter->grupo = new inputField("Grupo", "grupo");
		$filter->grupo->size=10;
		
		$filter->gr_desc = new inputField("Nombre","gr_desc");
		$filter->gr_desc->size=45;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/grcl/dataedit/show/<#grupo#>','<#grupo#>');

		$grid = new DataGrid("Lista de Grupos de Clientes");
		$grid->order_by("gr_desc","asc");
		$grid->per_page = 7;
		$grid->column_orderby("Grupo",$uri,'grupo');
		$grid->column_orderby("Nombre","gr_desc","gr_desc");
		$grid->column_orderby("Clase","clase",'clase');
		$grid->column_orderby("Cuenta","cuenta",'cuenta');
		$grid->add("ventas/grcl/dataedit/create",'Agregar nuevo grupo');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Grupos de Clientes</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
    }
	function dataedit()
	{ 
		$this->rapyd->load("dataedit");
		
		$qformato=$this->qformato=$this->datasis->formato_cpla();

		$mCPLA=array(
		'tabla'   =>'cpla',
		'columnas'=>array(
		'codigo' =>'C&oacute;digo',
		'descrip'=>'Descripci&oacute;n'),
		'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
		'retornar'=>array('codigo'=>'cuenta'),
		'titulo'  =>'Buscar Cuenta',
		'where'=>"codigo LIKE \"$qformato\"",
		);
				
		$bcpla =$this->datasis->modbus($mCPLA);
				
		$edit = new DataEdit("Grupo de clientes", "grcl");
		$edit->back_url = site_url("ventas/grcl/filteredgrid");
		
		$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process("update",'_post_update');
		$edit->post_process("delete",'_post_delete');
		
		$edit->grupo = new inputField("Grupo", "grupo");
		$edit->grupo->mode ="autohide";
		$edit->grupo->rule ="trim|required|max_length[4]|callback_chexiste";
		$edit->grupo->size =5;
		$edit->grupo->maxlength =4;
		
		$edit->clase = new dropdownField("Clase", "clase");
		$edit->clase->option("","");
		$edit->clase->options(array("C"=> "Cliente","O"=>"Otros","I"=>"Internos"));
		$edit->clase->rule= "required";
		$edit->clase->style='width:100px;';
		
		$edit->gr_desc = new inputField("Descripci&oacute;n", "gr_desc");
		$edit->gr_desc->size =30;
		$edit->gr_desc->maxlength =25;
		$edit->gr_desc->rule= "required|strtoupper";
		
		$edit->cuenta = new inputField("Cta. Contable", "cuenta");
		$edit->cuenta->rule= "callback_chcuentac";
		$edit->cuenta->size =20;
		$edit->cuenta->maxlength =15;
		$edit->cuenta->append($bcpla);
				
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
	
	  $data['content'] = $edit->output;           
    $data['title']   = "<h1>Grupos de Clientes</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _pre_del($do) {
		$grupo=$do->get('grupo');
		$chek = $this->datasis->dameval("SELECT count(*) FROM scli WHERE grupo='$grupo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}else	{
			return True;
		}
	}
	function _post_insert($do){
		$codigo=$do->get('grupo');
		logusu('grcl',"GRUPO $codigo CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('grupo');
		$limite=$do->get('limite');
		logusu('grcl',"GRUPO $codigo MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('grupo');
		$limite=$do->get('limite');
		logusu('grcl',"GRUPO $codigo ELIMINADO");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('grupo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM grcl WHERE grupo='$codigo'");
		if ($chek > 0){
			$grupo=$this->datasis->dameval("SELECT gr_desc FROM grcl WHERE grupo='$codigo'");
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
		$this->db->from('grcl');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		if ( count($sort) == 0 ) $this->db->order_by( 'grupo', 'asc' );
		
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $this->db->count_all('grcl');

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
			if ($this->datasis->dameval("SELECT COUNT(*) FROM grcl WHERE grupo='$grupo'") == 0)
			{
				$mSQL = $this->db->insert_string("grcl", $campos );
				$this->db->simple_query($mSQL);
				logusu('grcl',"GRUPO DE CLIENTES $grupo CREADO");
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

		$mSQL = $this->db->update_string("grcl", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('grcl',"GRUPO DE CLIENTES $grupo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Grupo Modificado -> ".$data['data']['grupo']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$grupo = $campos['grupo'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM scli WHERE grupo='$grupo'");

		if ($chek > 0){
			echo "{ success: false, message: 'Grupo de Cliente no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM grcl WHERE grupo='$grupo'");
			logusu('grcl',"GRUPO $grupo ELIMINADO");
			echo "{ success: true, message: 'Grupo de cliente Eliminado'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function grclextjs(){
		$encabeza='GRUPOS DE CLIENTES';
		$listados= $this->datasis->listados('grcl');
		$otros=$this->datasis->otros('grpr', 'grcl');

		$urlajax = 'ventas/grcl/';
		$variables = "var mcuenta = ''";
		$funciones = "
function clase(val){
	if ( val == 'C'){
		return 'Cliente';
	} else if ( val == 'I'){
		return  'Interno';
	} else if ( val == 'O'){
		return  'Otro';
	}
}
		";
		$valida = "
		{ type: 'length', field: 'grupo',   min: 1 },
		{ type: 'length', field: 'gr_desc', min: 1 }
		";
		
		$columnas = "
		{ header: 'id',          width:  30, sortable: true, dataIndex: 'id' }, 
		{ header: 'Grupo',       width:  50, sortable: true, dataIndex: 'grupo',   field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Descripcion', width: 200, sortable: true, dataIndex: 'gr_desc', field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Clase',       width:  90, sortable: true, dataIndex: 'clase',   field: { type: 'textfield' }, filter: { type: 'string' }, renderer: clase },
		{ header: 'Cuenta',      width:  90, sortable: true, dataIndex: 'cuenta',  field: { type: 'textfield' }, filter: { type: 'string' } }
	";

		$campos = "'id', 'grupo', 'gr_desc', 'clase', 'cuenta'";
		
		$camposforma = "
							{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							items: [
									{ xtype: 'textfield', fieldLabel: 'Grupo',       name: 'grupo',   allowBlank: false, width: 120, id: 'grupo' },
									{ xtype: 'textfield', fieldLabel: 'Descripcion', name: 'gr_desc', allowBlank: false, width: 400, },
									{ xtype: 'combo',     fieldLabel: 'Clase',       name: 'clase',   store: [['C','Cliente'],['I','Interno'],['O','Otro']], width: 160 }
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
										name: 'cuenta',
										id:   'cuenta',
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

		$titulow = 'Grupo de Proveedores';

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
							mcuenta  = registro.data.cuenta;
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
		
		$data['title']  = heading('Grupo de Clientes');
		$this->load->view('extjs/extjsven',$data);
		
	}

}
?>