<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Caja extends validaciones {

	function Caja(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(136,1);
	}

	function index(){
		if ( !$this->datasis->iscampo('caja','id') ) {
			$this->db->simple_query('ALTER TABLE caja DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE caja ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE caja ADD UNIQUE INDEX caja (caja)');
		}
		$this->cajaextjs();
		//redirect('ventas/caja/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro Caja', 'caja');

		$filter->caja = new inputField('Caja', 'caja');
		$filter->caja->size=5;

		$filter->ubica = new inputField("Ubicaci&oacute;n", "ubica");
		$filter->ubica->size=35;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/caja/dataedit/show/<#caja#>','<#caja#>');
		
		$grid = new DataGrid('Lista de Cajas');
		$grid->order_by('caja','asc');
		$grid->per_page = 7;

		$grid->column('Caja',$uri);
		$grid->column('Ubicaci&oacute;n','ubica');
		$grid->column('Status', 'status');
		$grid->column('Descontar de almac&eacute;n', 'almacen');
		$grid->column('Impresora Puerto','impre');
		$grid->column('Lector &Oacute;ptico Puerto','lector');
		$grid->column('Gaveta Puerto','gaveta');
		$grid->column('Display Puerto','display');

		$grid->add("ventas/caja/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cajas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		$this->rapyd->uri->keep_persistence();
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit('Edici&oacute;n de caja','caja');
		$edit->back_url = site_url('ventas/caja/filteredgrid');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');

		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->caja = new inputField('N&uacute;mero de caja', 'caja');
		$edit->caja->rule = 'trim|required|callback_chexiste';
		$edit->caja->mode = 'autohide';
		$edit->caja->maxlength=3;
		$edit->caja->size = 4;
		$edit->caja->css_class='inputnum';

		$edit->ubica = new inputField('Ubicaci&oacute;n', 'ubica');
		$edit->ubica->append('Puede colocar la direcci&oacute;n IP de la caja');
		$edit->ubica->maxlength=30;
		$edit->ubica->rule='trim|strtoupper|callback_ip_caja';

		$edit->status = new dropdownField("Status", "status");
		$edit->status->option("C","Cerrado");
		$edit->status->option("A","Abierto");
		$edit->status->rule='required';
		$edit->status->style="width:150";

		$edit->factura = new inputField("Pr&oacute;xima Factura","factura");
		$edit->factura->rule = "trim";
		$edit->factura->maxlength=6;
		$edit->factura->size = 7;

		$edit->egreso  = new inputField("Pr&oacute;ximo Retiro en caja","egreso");
		$edit->egreso->rule = "trim";
		$edit->egreso->maxlength=6;
		$edit->egreso->size = 7;

		$edit->ingreso = new inputField("Pr&oacute;ximo egreso en caja","ingreso");
		$edit->ingreso->rule = "trim";
		$edit->ingreso->maxlength=6;
		$edit->ingreso->size = 7;

		$edit->almacen = new dropdownField("Almacen", "almacen");
		$edit->almacen->option("","");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides"); 
		$edit->almacen->style="width:150px";
		
		$opt=array('impre'=>'Impresora','lector'=>'Lector','gaveta'=>'Gaveta','display'=>'Display');
		foreach($opt AS $qu=>$grupo){
			$obj=$qu;
			$edit->$obj = new dropdownField("Puerto", $obj);
			$edit->$obj->options(array("NO/C"=>"NO CONECTADO","LP1"=> "LPT1","LP2"=>"LPT2","COM1"=>"COM1","COM2"=>"COM2"));
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'baud';
			$edit->$obj = new inputField("Baud Rate",$obj);
			$edit->$obj->size = 6;
			$edit->$obj->maxlength=5;
			$edit->$obj->css_class='inputnum';
			$edit->$obj->rule = "trim";
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'parid';
			$edit->$obj = new dropdownField("Pariedad", $obj);
			$edit->$obj->options(array("N"=>"NONE","E"=> "EVEN","O"=>"ODD"));
			$edit->$obj->maxlength=1;
			$edit->$obj->size = 2;
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'long';
			$edit->$obj = new dropdownField("Longitud", $obj);
			$edit->$obj->options(array("8"=> "8 BITS","7"=>"7 BITS"));
			$edit->$obj->maxlength=1;
			$edit->$obj->size = 2;
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'stop';
			$edit->$obj = new dropdownField("Bit de parada", $obj);
			$edit->$obj->options(array("1"=> "1 BIT","2"=>"2 BIT"));
			$edit->$obj->maxlength=1;
			$edit->$obj->size = 2;
			$edit->$obj->group=$grupo;
		}
		for ($i=1;$i<=5;$i++){
			$obj='cont'.$i;
			$edit->$obj = new inputField("Codigo ASCII",$obj);
			$edit->$obj->css_class='inputnum';
			$edit->$obj->maxlength=3;
			$edit->$obj->size=4;
			if ($i!=1) $edit->$obj->in='cont1';
		}
		$edit->almacen = new dropdownField("Almac&eacute;n", "almacen");
		$edit->almacen->option("","");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Cajas</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function _post_insert($do){
		$codigo=$do->get('caja');
		$ubica=$do->get('ubica');
		$status=$do->get('status');
		logusu('caja',"CAJA $codigo UBICACION $ubica STATUS $status CREADA");
	}
	function _post_update($do){
		$codigo =$do->get('caja');
		$ubica  =$do->get('ubica');
		$status =$do->get('status');
		logusu('caja',"CAJA $codigo UBICACION $ubica STATUS $status MODIFICADA");
	}
	function _post_delete($do){
		$codigo=$do->get('caja');
		$ubica=$do->get('ubica');
		$status=$do->get('status');
		logusu('caja',"CAJA $codigo UBICACION $ubica STATUS $status ELIMINADA");
	}
	
	//VALIDACIONES
	function chexiste($codigo){
		$codigo=$this->input->post('caja');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM caja WHERE caja='$codigo'");
		if ($chek > 0){
			$ubica=$this->datasis->dameval("SELECT ubica FROM caja WHERE caja='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para la caja $ubica");
			return FALSE;
		}else {
			return TRUE;
		}
	}

	function ip_caja($ubica){
		$numero=$this->rapyd->uri->get_edited_id();
		if($this->rapyd->uri->is_set('update'))
			return $this->_ipval($ubica,$numero);
		else
			return $this->_ipval($ubica);
	}

	function _ipval($ubica,$numero=null){
		if(preg_match("/^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\\.([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])){3}$/", $ubica)>0){
			if(!empty($numero)) $where = " AND caja!=".$this->db->escape($numero); else $where='';
			$mSQL="SELECT COUNT(*) FROM caja WHERE ubica='$ubica' $where";
			$cant=$this->datasis->dameval($mSQL);
			if($cant>0){
				$this->validation->set_message('ip_caja', "La ip dada en el campo <b>%s</b> ya fue asignada a otro registro");
				return FALSE;
			}
		}
		return TRUE;
	}

	function limpia_ip($ip){
		$ip=trim($ip);
		$ip=preg_replace('/\.0+/','.',$ip);
		return str_replace('..','.0.',$ip);
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"caja","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('caja');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('caja');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$caja = $campos['caja'];

		if ( !empty($caja) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM caja WHERE caja='$caja'") == 0)
			{
				$mSQL = $this->db->insert_string("caja", $campos );
				$this->db->simple_query($mSQL);
				logusu('caja',"CAJA $caja CREADO");
				echo "{ success: true, message: 'Caja Agregada'}";
			} else {
				echo "{ success: false, message: 'Ya existe una caja con ese codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Falta el campo codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$caja = $campos['caja'];
		unset($campos['caja']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("caja", $campos,"id=".$data['data']['id'] );
		$this->db->simple_query($mSQL);
		logusu('caja',"caja $caja ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'caja Modificada -> ".$data['data']['caja']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$caja = $campos['caja'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM sfpa WHERE caja='$caja'");

		if ($chek > 0){
			echo "{ success: false, message: 'Caja no puede ser Borrada'}";
		} else {
			$this->db->simple_query("DELETE FROM caja WHERE caja='$caja'");
			logusu('caja',"CAJA $caja ELIMINADO");
			echo "{ success: true, message: 'caja Eliminada'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function cajaextjs(){
		$encabeza='CONFIGURACION DE CAJAS';
		$listados= $this->datasis->listados('caja');
		$otros=$this->datasis->otros('caja', 'ventas/caja');

		$urlajax = 'ventas/caja/';
		$variables = "";

		$mSQL = "SELECT ubica, CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ORDER BY ubica";
		$alma = $this->datasis->llenacombo($mSQL);

		$puertos = '["NO/C","NO CONECTADO"],["LP1","PUERTO PARALELO 1"],["LP2","PUERTO PARALELO 2"],["COM1","PUERTO SERIAL 1"],["COM2","PUERTO SERIAL 2"]';
		$paridad = '["N","NONE"],["E","EVEN"],["O","ODD"]';
		$long    = '["8","8 BITS"],["7","7 BITS"]';
		
		$funciones = "function estado(val){if ( val == 'A'){ return 'Abierto';} else {return  'Cerrado';}}";
		
		$valida = "
		{ type: 'length', field: 'caja',   min: 1 },
		{ type: 'length', field: 'nombre', min: 1 }
		";
		
		$columnas = "
		{ header: 'Codigo',      dataIndex: 'caja',    width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Descripcion', dataIndex: 'ubica',   width: 120, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Status',      dataIndex: 'status',  width:  60, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }, renderer: estado}, 
		{ header: 'Factura',     dataIndex: 'factura', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Egreso',      dataIndex: 'egreso',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Ingreso',     dataIndex: 'ingreso', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Almacen',     dataIndex: 'almacen', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ text: 'Impresora', columns: [
			{ header: 'Puerto',   dataIndex: 'impre',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'BaudRate', dataIndex: 'ibaud',  width:  90, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Paridad',  dataIndex: 'ipari',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'Longitud', dataIndex: 'ilong',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'StopBit',  dataIndex: 'istop',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		]},
		{ text: 'Lector de Barras', columns: [
			{ header: 'Puerto',   dataIndex: 'lector', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'BaudRate', dataIndex: 'lbaud',  width:  90, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Paridad',  dataIndex: 'lpari',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'Longitud', dataIndex: 'llong',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'StopBit',  dataIndex: 'lstop',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		]},
		{ text: 'Gaveta de Dinero', columns: [
			{ header: 'Puerto',   dataIndex: 'gaveta',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'BaudRate', dataIndex: 'gbaud',  width:  90, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Paridad',  dataIndex: 'gpari',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'Longitud', dataIndex: 'glong',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'StopBit',  dataIndex: 'gstop',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		]},
		{ text: 'Visor de Caja', columns: [
			{ header: 'Puerto',   dataIndex: 'display', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'BaudRate', dataIndex: 'dbaud',   width:  90, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Paridad',  dataIndex: 'dpari',   width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'Longitud', dataIndex: 'dlong',   width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'StopBit',  dataIndex: 'dstop',   width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		]},
		{ text: 'Comando de Apertura de Gaveta', columns: [
			{ header: 'Byte 1', dataIndex: 'cont1',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Byte 2', dataIndex: 'cont2',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Byte 3', dataIndex: 'cont3',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Byte 4', dataIndex: 'cont4',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Byte 5', dataIndex: 'cont5',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
		]}

		//{ header: 'Egreso',   dataIndex: 'egreso',  width:  70, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		//{ header: 'Ingreso',  dataIndex: 'ingreso', width:  70, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
	";

		$campos = "'id','caja','ubica','status','factura','egreso','ingreso','almacen','impre','ibaud','iparid','ilong','istop','lector','lbaud','lparid','llong','lstop','gaveta','gbaud','gparid','glong','gstop','cont1','cont2','cont3','cont4','cont5','display','dbaud','dparid','dlong','dstop'";
		
		$camposforma = "
							{
							xtype:'fieldset',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:50 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield',     fieldLabel: 'Codigo',      name: 'caja',    width:110, allowBlank: false, id: 'caja', labelWidth:70 },
									{ xtype: 'combo',         fieldLabel: 'Status',      name: 'tipo',    width:250,  store: [['A','Abierto'],['C','Cerrado']], labelWidth:110},
									{ xtype: 'textfield',     fieldLabel: 'Descripcion', name: 'ubica',   width:360, allowBlank: false, labelWidth:70 },
								]
							},{
							xtype:'fieldset',
							title: 'Contadores',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:50 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield',     fieldLabel: 'Factura',     name: 'ubica',   width:120, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Egreso',      name: 'ubica',   width:120, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Ingreso',     name: 'ubica',   width:120, allowBlank: false },
								]
							},{
							xtype:'fieldset',
							title: 'Impresora',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:50 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'combo',         fieldLabel: 'Puerto',      name: 'impre',   width:200,  store: [".$puertos."]},
									{ xtype: 'numberfield',   fieldLabel: 'Baud Rate',   name: 'ibaud',   width:160, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000'), labelWidth:80 },
									{ xtype: 'combo',         fieldLabel: 'Paridad',     name: 'iparid',  width:140,  store: [".$paridad."]},
									{ xtype: 'combo',         fieldLabel: 'Longitud',    name: 'ilong',   width:220,  store: [".$long."], labelWidth:140 },

/*
									{ xtype: 'textfield',     fieldLabel: 'Descripcion', name: 'ubica',   width:320, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Factura',     name: 'ubica',   width:320, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Egreso',      name: 'ubica',   width:320, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Ingreso',     name: 'ubica',   width:320, allowBlank: false },

//									{ xtype: 'numberfield',   fieldLabel: 'Comision', name: 'comision', width:120, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
//									{ xtype: 'numberfield',   fieldLabel: 'Impuesto', name: 'impuesto', width:200, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), labelWidth: 130  },
//									{ xtype: 'textareafield', fieldLabel: 'Mensaje',  name: 'mensaje',  width:320, allowBlank: true },*/
								]
							}
							
							
		";

		$titulow = 'Configuracion de Cajas';

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 400,
				height: 400,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						if (registro) {
							form.loadRecord(registro);
							form.findField('caja').setReadOnly(true);
						} else {
							form.findField('caja').setReadOnly(false);
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
		
		$data['title']  = heading('Configuracion de Cajas');
		$this->load->view('extjs/extjsven',$data);
		
	}


}
?>