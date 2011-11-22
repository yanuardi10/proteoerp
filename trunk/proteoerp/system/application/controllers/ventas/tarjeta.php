<?php  require_once(BASEPATH.'application/controllers/validaciones.php');
class Tarjeta extends validaciones {
	function tarjeta(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(133,1);
	}

	function index(){
		if ( !$this->datasis->iscampo('tarjeta','id') ) {
			$this->db->simple_query('ALTER TABLE tarjeta DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE tarjeta ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE tarjeta ADD UNIQUE INDEX tipo (tipo)');
		}
		$this->tarjetaextjs();
	}

	function filteredgrid(){

		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Buscar', 'tarjeta');

		$filter->tipo = new dropdownField('Tipo', 'tipo');
		$filter->tipo->options('SELECT tipo, nombre from tarjeta ORDER BY tipo');
		$filter->tipo->style='width:180px';

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('ventas/tarjeta/dataedit/show/<#tipo#>','<#tipo#>');

		$grid = new DataGrid('Lista de Formas de Pago');
		$grid->order_by('nombre','asc');
		$grid->per_page = 10;

		$grid->column_orderby('Tipo'   ,$uri    ,'tipo'  );
		$grid->column_orderby('Nombre' ,'nombre','nombre');
		$grid->column('Comisi&oacute;n','comision','align=\'right\'');
		$grid->column('Impuesto' ,'impuesto','align=\'right\'');
		$grid->column('Mensaje'  ,'mensaje' );
		$grid->column_orderby('Activo','activo','activo');

		$grid->add('ventas/tarjeta/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Formas de Pago');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit('Formas de Pago', 'tarjeta');
		$edit->back_url = site_url('ventas/tarjeta/filteredgrid');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');

		//$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->tipo = new inputField('Tipo', 'tipo');
		$edit->tipo->maxlength=2;
		$edit->tipo->size= 3;
		$edit->tipo->mode= 'autohide';
		$edit->tipo->rule= 'strtoupper|required|callback_chexiste';

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->maxlength=20;
		$edit->nombre->size=25;
		$edit->nombre->rule = 'strtoupper|required';

		$edit->comision = new inputField('Comisi&oacute;n', 'comision');
		$edit->comision->maxlength=8;
		$edit->comision->size=10;
		$edit->comision->css_class='inputnum';
		$edit->comision->rule='numeric';

		$edit->impuesto = new inputField('Impuesto', 'impuesto');
		$edit->impuesto->maxlength=8;
		$edit->impuesto->size=10;
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->rule='numeric';

		$edit->mensaje  = new inputField('Mensaje', 'mensaje');
		$edit->mensaje->maxlength=60;
		$edit->mensaje->size=65;

		$edit->activo = new dropdownField('Activo', 'activo');
		$edit->activo->option('S','Si');
		$edit->activo->option('N','No');

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = heading('Formas de Pago');
		$data['head']    = script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _pre_del($do) {
		$grupo=$do->get('grupo');
		$chek = $this->datasis->dameval("SELECT count(*) FROM scli WHERE grupo='$grupo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}else{
			return True;
		}
	}

	function _post_insert($do){
		$codigo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('tarjeta',"FORMA DE PAGO $codigo NOMBRE $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('tarjeta',"FORMA DE PAGO $codigo NOMBRE $nombre MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('tarjeta',"FORMA DE PAGO $codigo NOMBRE $nombre ELIMINADO");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('tipo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM tarjeta WHERE tipo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM tarjeta WHERE tipo='$codigo'");
			$this->validation->set_message('chexiste',"El tipo $codigo ya existe para la forma de pago $nombre");
			return FALSE;
		}else {
		return TRUE;
		}
	}

	function instala(){
		$mSQL="ALTER TABLE `tarjeta` 
			ADD COLUMN `activo` 
			CHAR(1) NULL DEFAULT NULL AFTER `mensaje`";
		$this->db->query($mSQL);
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"tipo","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('tarjeta');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('tarjeta');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$tipo = $campos['tipo'];

		if ( !empty($tipo) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM tarjeta WHERE tipo='$tipo'") == 0)
			{
				$mSQL = $this->db->insert_string("tarjeta", $campos );
				$this->db->simple_query($mSQL);
				logusu('tarjeta',"TARJETA $tipo CREADO");
				echo "{ success: true, message: 'Tarjeta Agregada'}";
			} else {
				echo "{ success: false, message: 'Ya existe una tarjeta con ese Tipo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Falta el campo tipo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$tipo = $campos['tipo'];
		unset($campos['tipo']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("tarjeta", $campos,"id=".$data['data']['id'] );
		$this->db->simple_query($mSQL);
		logusu('tarjeta',"tarjeta $tipo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Tarjeta Modificada -> ".$data['data']['tipo']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$tipo = $campos['tipo'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM sfpa WHERE tipo='$tipo'");

		if ($chek > 0){
			echo "{ success: false, message: 'tarjeta no puede ser Borrada'}";
		} else {
			$this->db->simple_query("DELETE FROM tarjeta WHERE tipo='$tipo'");
			logusu('tarjeta',"TARJETA $tipo ELIMINADO");
			echo "{ success: true, message: 'Tarjeta Eliminada'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function tarjetaextjs(){
		$encabeza='FORMAS DE PAGO';
		$listados= $this->datasis->listados('tarjeta');
		$otros=$this->datasis->otros('tarjeta', 'ventas/tarjeta');

		$urlajax = 'ventas/tarjeta/';
		$variables = "";
		
		$funciones = "function estado(val){if ( val == 'S'){ return 'Activo';} else {return  'Inactivo';}}";
		
		$valida = "
		{ type: 'length', field: 'tipo',   min: 1 },
		{ type: 'length', field: 'nombre', min: 1 }
		";
		
		$columnas = "
		{ header: 'Tipo',     width:  50, sortable: true, dataIndex: 'tipo',     field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Status',   width:  80, sortable: true, dataIndex: 'activo',   field: { type: 'textfield' }, filter: { type: 'string'  }, renderer: estado },
		{ header: 'Nombre',   width: 120, sortable: true, dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Mensaje',  width: 180, sortable: true, dataIndex: 'mensaje',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Comison',  width:  70, sortable: true, dataIndex: 'comision', field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Impuesto', width:  70, sortable: true, dataIndex: 'impuesto', field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
	";

		$campos = "'id', 'tipo','nombre','comision','impuesto','mensaje','activo'";
		
		$camposforma = "
							{
							xtype:'fieldset',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield',     fieldLabel: 'Tipo',     name: 'tipo',     width:110, allowBlank: false, id: 'tipo' },
									{ xtype: 'combo',         fieldLabel: 'Status',   name: 'activo',   width:210,  store: [['S','Activo'],['N','Inactivo']], labelWidth:110},
									{ xtype: 'textfield',     fieldLabel: 'Nombre',   name: 'nombre',   width:320, allowBlank: false },
									{ xtype: 'numberfield',   fieldLabel: 'Comision', name: 'comision', width:120, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
									{ xtype: 'numberfield',   fieldLabel: 'Impuesto', name: 'impuesto', width:200, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), labelWidth: 130  },
									{ xtype: 'textareafield', fieldLabel: 'Mensaje',  name: 'mensaje',  width:320, allowBlank: true },
								]
							}
		";

		$titulow = 'Formas de Pago';

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 380,
				height: 300,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						if (registro) {
							form.loadRecord(registro);
							form.findField('tipo').setReadOnly(true);
						} else {
							form.findField('tipo').setReadOnly(false);
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
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		
		$data['title']  = heading('tarjetas');
		$this->load->view('extjs/extjsven',$data);
		
	}
}
?>