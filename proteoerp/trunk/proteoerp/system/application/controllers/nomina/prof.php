<?php
	class prof extends Controller{
		function prof(){
			parent::Controller();
			$this->load->library("rapyd");
			$this->datasis->modulo_id(700,1);
		}
		function index(){
			if ( !$this->datasis->iscampo('prof','id') ) {
				$this->db->simple_query('ALTER TABLE prof DROP PRIMARY KEY');
				$this->db->simple_query('ALTER TABLE prof ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
				$this->db->simple_query('ALTER TABLE prof ADD UNIQUE INDEX codigo (codigo)');
			}
			$this->datasis->modulo_id(700,1);
			$this->profextjs();
			//redirect("nomina/prof/filteredgrid");	
		}
		
		function filteredgrid(){
			$this->rapyd->load("datafilter","datagrid");
			
			$filter = new DataFilter("Filtro de Profesiones",'prof');

			$filter->codigo= new inputField("Codigo","codigo");
			$filter->codigo->size=20;
			$filter->codigo->maxlength=8;
			
			$filter->profesion= new inputField("Profesion","profesion");
			$filter->profesion->size=20;
			$filter->profesion->maxlength=40;
			
			$filter->buttons("reset","search");
			$filter->build();
			
			$uri = anchor('nomina/prof/dataedit/show/<#codigo#>','<#codigo#>');
			
			$grid = new DataGrid("Lista de Profesiones");
			$grid->per_page=15;
			
			$grid->column("Codigo",$uri);
			$grid->column("Profesiones","profesion");
			
			$grid->add("nomina/prof/dataedit/create");
			$grid->build();
			
			$data['content'] = $filter->output.$grid->output;
			$data['title']   = "<h1>Profesiones</h1>";
			$data["head"]    = $this->rapyd->get_head();	
			$this->load->view('view_ventanas', $data);	
		}
		
		function dataedit(){
			$this->rapyd->load("dataedit");
			
			$edit = new DataEdit("Profesiones","prof");
			$edit->back_url = site_url("nomina/prof/filteredgrid");
			
			$edit->post_process('insert','_post_insert');
			$edit->post_process('update','_post_update');
			$edit->post_process('delete','_post_delete');
						
			$edit->codigo = new inputField("Codigo", "codigo");
			$edit->codigo->size =10;
			$edit->codigo->mode="autohide";
			$edit->codigo->rule="strtoupper|required|callback_chexiste";
			$edit->codigo->maxlength =8;

			$edit->profesion = new inputField("Profesion", "profesion");
			$edit->profesion->size =40;
			$edit->profesion->rule="strtoupper|required";
			$edit->profesion->maxlength =40;
					  
			$edit->buttons("modify", "save", "undo", "delete", "back");
			$edit->build();
			
			$data['content'] = $edit->output; 		
			$data['title']   = "<h1>Profesiones</h1>";
			$data["head"]    = $this->rapyd->get_head();
			$this->load->view('view_ventanas', $data);
		}
			
		function _post_insert($do){
			$codigo=$do->get('codigo');
			$nombre=$do->get('profesion');
			logusu('prof',"PROFESION $codigo NOMBRE $nombre CREADA");
		}
		
		function _post_update($do){
			$codigo=$do->get('codigo');
			$nombre=$do->get('profesion');
			logusu('prof',"PROFESION $codigo NOMBRE $nombre MODIFICADA");
		}
		
		function _post_delete($do){
			$codigo=$do->get('codigo');
			$nombre=$do->get('profesion');
			logusu('prof',"PROFESION $codigo NOMBRE $nombre ELIMINADA");
	  	}
		
		function chexiste($codigo){
			$chek=$this->datasis->dameval("SELECT COUNT(*) FROM prof WHERE codigo='$codigo'");
			if ($chek > 0){
				$profesion=$this->datasis->dameval("SELECT profesion FROM prof WHERE codigo='$codigo'");
				$this->validation->set_message('chexiste',"El codigo $codigo ya existe para la profesion $profesion");
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
		$this->db->from('prof');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();

		$results = $this->db->count_all('prof');

		$arr = $this->datasis->codificautf8($query->result_array());

		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear() {
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$codigo = $data['data']['codigo'];
		unset($campos['id']);
		$mHay = $this->datasis->dameval("SELECT count(*) FROM prof WHERE codigo='".$codigo."'");
		if  ( $mHay > 0 ){
			echo "{ success: false, message: 'Ya existe ese proveedor'}";
		} else {
			$mSQL = $this->db->insert_string("prof", $campos );
			$this->db->simple_query($mSQL);
			logusu('prof',"PROFESIONES $codigo CREADO");
			echo "{ success: true, message: ".$data['data']['codigo']."}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$codigo = $campos['codigo'];
		unset($campos['codigo']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("prof", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('prof',"PROFESION ".$data['data']['codigo']." MODIFICADO");
		echo "{ success: true, message: 'Profesion Modificada '}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $data['data']['codigo'];
		
		// VERIFICAR SI PUEDE
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE profes='$codigo'");

		if ($chek > 0){
			echo "{ success: false, message: 'Profesion no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM carg WHERE cargo='$cargo'");
			logusu('carg',"CARGO DE NOMINA $cargo ELIMINADO");
			echo "{ success: true, message: 'Cargo de nomina Eliminado'}";
		}
	}



//****************************************************************
//
//
//
//****************************************************************

	function profextjs(){
		$encabeza='PROFESIONES';
		$listados= $this->datasis->listados('prof');
		$otros=$this->datasis->otros('prof', 'prof');

		$urlajax = 'nomina/prof/';
		$variables = "";
		
		$funciones = "";
		
		$valida = "
		{ type: 'length', field: 'codigo',    min:  1 },
		{ type: 'length', field: 'profesion', min:  1 }
		";
		

		$columnas = "
		{ header: 'Codigo',    width: 100, sortable: true, dataIndex: 'codigo',    field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Profesion', width: 300, sortable: true, dataIndex: 'profesion', field: { type: 'textfield' }, filter: { type: 'string' }}
	";

		$campos = "'id', 'codigo', 'profesion'";
		$camposforma = "
			{
			frame: false,
			tborder: false,
			labelAlign: 'right',
			defaults: { xtype:'fieldset', labelWidth:70 },
			style:'padding:4px',
			items:[	
				{ xtype: 'textfield',   fieldLabel: 'Codigo',    name: 'codigo',    allowBlank: false, width: 200 },
				{ xtype: 'textfield',   fieldLabel: 'Profesion', name: 'profesion', allowBlank: false, width: 400 }
			]}
		";



		$titulow = 'Profesiones';

		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 200,
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

		$features= "features: [ { ftype: 'filters', encode: 'json', local: false } ],";


		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;

		
		$data['title']  = heading('Cargos de Nomina');
		$this->load->view('extjs/extjsven',$data);
		
	}

}
?>