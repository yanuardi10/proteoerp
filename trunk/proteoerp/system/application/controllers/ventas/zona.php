<?php
//zonas
class Zona extends Controller {
	
	var $data_type = null;
	var $data = null;

	function Zona()
	{
		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->datasis->modulo_id(137,1);
		$this->load->library("rapyd");

	}

	function index()
	{
		if ( !$this->datasis->iscampo('zona','id') ) {
			$this->db->simple_query('ALTER TABLE zona DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE zona ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE zona ADD UNIQUE INDEX codigo (codigo)');
		}
		$this->datasis->modulo_id(137,1);
		$this->zonaextjs();
		//redirect("ventas/zona/filteredgrid");
	}

		function test($id,$const)
	{
		return $id*$const;
	}
		function filteredgrid()
	{
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por Zonas", 'zona');
		
		$filter->codigo = new inputField("Codigo", "codigo");
		$filter->codigo->size=15;
			
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=25;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/zona/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Zonas");
		$grid->order_by("nombre","asc");
		$grid->per_page = 7;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre","nombre");
		$grid->column("Descripci&oacute;n","descrip");

		$grid->add("ventas/zona/dataedit/create");
		$grid->build();

	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Zonas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
    }

	function dataedit()
	{ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Zona", "zona");
		$edit->back_url = site_url("ventas/zona/filteredgrid");
		
		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
    
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size=10;
		$edit->codigo->rule= "trim|required|callback_chexiste";
		$edit->codigo->maxlength=8;
		$edit->codigo->mode = "autohide";
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size=38;
		$edit->nombre->rule="trim|required|strtoupper";
		$edit->nombre->maxlength=30;
		
		$edit->descrip = new textareafield("Descripci&oacute;n", "descrip");
		$edit->descrip->cols=70;
		$edit->descrip->rows=4;
		$edit->descrip->rule="trim";
	  $edit->descrip->maxlength=90;
	  
		$edit->buttons("modify", "save", "undo", "delete", "back");
  	$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Zonas</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('zona',"ZONA $codigo NOMBRE $nombre CREADA");
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('zona',"ZONA $codigo NOMBRE $nombre MODIFICADA");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('zona',"ZONA $codigo NOMBRE $nombre ELIMINADA");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM zona WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM zona WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para la zona de $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"codigo","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('zona');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('zona');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$codigo = $campos['codigo'];

		if ( !empty($codigo) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM zona WHERE codigo='$codigo'") == 0)
			{
				$mSQL = $this->db->insert_string("zona", $campos );
				$this->db->simple_query($mSQL);
				logusu('grcl',"ZONA $codigo CREADO");
				echo "{ success: true, message: 'Zoan Agregada'}";
			} else {
				echo "{ success: false, message: 'Ya existe una zona con ese Codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe una zona con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		unset($campos['codigo']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("zona", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('zona',"ZONA $codigo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Zoan Modificada -> ".$data['data']['codigo']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM scli WHERE zona='$codigo'");

		if ($chek > 0){
			echo "{ success: false, message: 'Zona no puede ser Borrada'}";
		} else {
			$this->db->simple_query("DELETE FROM zona WHERE codigo='$codigo'");
			logusu('zona',"ZONA $codigo ELIMINADO");
			echo "{ success: true, message: 'Zona Eliminada'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function zonaextjs(){
		$encabeza='ZONAS';
		$listados= $this->datasis->listados('zona');
		$otros=$this->datasis->otros('zona', 'zona');

		$urlajax = 'ventas/zona/';
		$variables = "";
		$funciones = "";
		$valida = "
		{ type: 'length', field: 'codigo', min: 1 },
		{ type: 'length', field: 'nombre', min: 1 }
		";
		
		$columnas = "
		{ header: 'Codigo',      width:  50, sortable: true, dataIndex: 'codigo',  field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Nombre',      width: 180, sortable: true, dataIndex: 'nombre',  field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Descripcion', width: 400, sortable: true, dataIndex: 'descrip', field: { type: 'textfield' }, filter: { type: 'string' } }, 
	";

		$campos = "'id', 'codigo', 'nombre', 'descrip'";
		
		$camposforma = "
							{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							items: [
									{ xtype: 'textfield',     fieldLabel: 'Codigo',      name: 'codigo', allowBlank: false,  width: 120, id: 'codigo' },
									{ xtype: 'textfield',     fieldLabel: 'Nombre',      name: 'nombre', allowBlank: false,  width: 400, },
									{ xtype: 'textareafield', fieldLabel: 'Descripcion', name: 'descrip', allowBlank: false, width: 400 }
								]
							}
		";

		$titulow = 'Zonas';

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
							form.loadRecord(registro);
						} 
					}
				}
";

		$stores = "";

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
		
		$data['title']  = heading('Zonas');
		$this->load->view('extjs/extjsven',$data);
		
	}

}
?>