<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Provoca extends validaciones {

	function Provoca(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(206,1);
		define ("THISFILE",   APPPATH."controllers/finanzas". $this->uri->segment(2).EXT);
	}

	function index(){
		if ( !$this->datasis->iscampo('provoca','id') ) {
			$this->db->simple_query('ALTER TABLE provoca DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE provoca DROP INDEX rif');
			$this->db->simple_query('ALTER TABLE provoca ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE provoca ADD UNIQUE INDEX rif (rif)');
		}
		$this->db->simple_query('UPDATE provoca SET rif=TRIM(rif)');
		redirect('finanzas/provoca/extgrid');
	}

	
	function extgrid(){
		$this->datasis->modulo_id(206,1);
		$this->provocaextjs();

	}


	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de proveedores ocasionales', 'provoca');
		
		$filter->rif = new inputField('RIF', 'rif');
		$filter->rif->maxlength=13;
		$filter->rif->size = 14;
		
		$filter->nombre = new inputField('Nombre', 'nombre');
		
		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/provoca/dataedit/show/<#rif#>','<#rif#>');

		$grid = new DataGrid("Filtro de Proveedores Ocasionales");
		//$grid->order_by("nombre","asc");
		$grid->per_page = 10;
		
		$grid->column_orderby('RIF'   ,$uri,'rif');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Fecha' ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha',"align='center'");
		
		$grid->add('finanzas/provoca/dataedit/create','Agregar un proveedor ocasional');
		$grid->build();
	
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Proveedores Ocasionales</h1>";
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load("dataedit");

		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
				'cliente' =>'C&oacute;digo Socio',
				'nombre'=>'Nombre', 
				'cirepre'=>'Rif/Cedula',
				'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Socio','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'socio'),
			'titulo'  =>'Buscar Socio');

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

		$boton =$this->datasis->modbus($mSCLId);
		$bcpla =$this->datasis->modbus($mCPLA);
		
		$smenu['link']=barra_menu('131');
		$consulrif=$this->datasis->traevalor('CONSULRIF');
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		
		function anomfis(){
				vtiva=$("#tiva").val();
				if(vtiva=="C" || vtiva=="E" || vtiva=="R"){
					$("#tr_nomfis").show();
					$("#tr_riff").show();
				}else{
					$("#nomfis").val("");
					$("#rif").val("");
					$("#tr_nomfis").hide();
					$("#tr_rif").hide();
				}
		}
		
		function consulrif(){
				vrif=$("#rif").val();
				if(vrif.length==0){
					alert("Debe introducir primero un RIF");
				}else{
					vrif=vrif.toUpperCase();
					$("#rif").val(vrif);
					window.open("'.$consulrif.'"+"?p_rif="+vrif,"CONSULRIF","height=350,width=410");
				}
		}
		
		';
		$edit = new DataEdit("Proveedor ocacional", "provoca");
		$edit->back_url = site_url("finanzas/provoca/filteredgrid");
		$edit->script($script, 'create');
		$edit->script($script, 'modify');
		
		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->rif =  new inputField('RIF', 'rif');
		$edit->rif->mode='autohide';
		$edit->rif->rule = 'strtoupper|required|callback_chrif';
		$edit->rif->append($lriffis);
		$edit->rif->maxlength=10;
		$edit->rif->size = 14;
		
		$edit->nombre =  new inputField('Nombre', 'nombre');
		$edit->nombre->rule = 'strtoupper|required';
		$edit->nombre->size = 80;
		$edit->nombre->maxlength=80;

		$edit->fecha =  new dateField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue=date('Y-m-d');
		$edit->fecha->size = 10;

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;
		$data['title']   = '<h1>Proveedores Ocasionales</h1>';
		$data['head']    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _pre_del($do) {
		$codigo=$this->db->escape($do->get('rif'));
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM gitser WHERE rif=$codigo");
		
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='No se puede borrar el proveedor porque contiene movimientos';
			return False;
		}
		return True;
	}
	function _post_insert($do){
		$codigo=$do->get('rif');
		$nombre=$do->get('nombre');
		logusu('provoca',"PROVEEDOR OCASIONAL $codigo NOMBRE $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('rif');
		$nombre=$do->get('nombre');
		logusu('provoca',"PROVEEDOR OCASIONAL $codigo NOMBRE $nombre MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('rif');
		$nombre=$do->get('nombre');
		logusu('provoca',"PROVEEDOR OCASIONAL $codigo NOMBRE $nombre ELIMINADO");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM importtgas WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM importtgas WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el gasto $nombre");
			return FALSE;
		}else {
		return TRUE;
		}
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"nombre","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = "";

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('provoca');

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
				//$where = " rif != '' ";
				$qs = "";
				for ($i=0;$i<count($filter);$i++){
					switch($filter[$i]['type']){
					case 'string' : $qs .= " ".$filter[$i]['field']." LIKE '%".$filter[$i]['value']."%'"; 
						Break;
					case 'list' :
						if (strstr($filter[$i]['value'],',')){
							$fi = explode(',',$filter[$i]['value']);
							for ($q=0;$q<count($fi);$q++){
								$fi[$q] = "'".$fi[$q]."'";
							}
							$filter[$i]['value'] = implode(',',$fi);
								$qs .= " AND ".$filter[$i]['field']." IN (".$filter[$i]['value'].")";
						}else{
							$qs .= " AND ".$filter[$i]['field']." = '".$filter[$i]['value']."'";
						}
						Break;
					case 'boolean' : $qs .= " AND ".$filter[$i]['field']." = ".($filter[$i]['value']); 
						Break;
					case 'numeric' :
						switch ($filter[$i]['comparison']) {
							case 'ne' : $qs .= " AND ".$filter[$i]['field']." != ".$filter[$i]['value']; 
								Break;
							case 'eq' : $qs .= " AND ".$filter[$i]['field']." = ".$filter[$i]['value']; 
								Break;
							case 'lt' : $qs .= " AND ".$filter[$i]['field']." < ".$filter[$i]['value']; 
								Break;
							case 'gt' : $qs .= " AND ".$filter[$i]['field']." > ".$filter[$i]['value']; 
								Break;
						}
						Break;
					case 'date' :
						switch ($filter[$i]['comparison']) {
							case 'ne' : $qs .= " AND ".$filter[$i]['field']." != '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'eq' : $qs .= " AND ".$filter[$i]['field']." = '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'lt' : $qs .= " AND ".$filter[$i]['field']." < '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'gt' : $qs .= " AND ".$filter[$i]['field']." > '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
						}
						Break;
					}
				}
				$where .= $qs;
				$this->db->where($where,null, false);
				
			}
		}
		
		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();

		$results = $this->db->count_all('provoca');
		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear() {
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$rif = $data['data']['rif'];
		unset($campos['id']);
		$mHay = $this->datasis->dameval("SELECT count(*) FROM provoca WHERE rif='".$rif."'");
		if  ( $mHay > 0 ){
			echo "{ success: false, message: 'Ya existe ese proveedor'}";
		} else {
			$mSQL = $this->db->insert_string("sprv", $campos );
			$this->db->simple_query($mSQL);
			logusu('provoca',"PROVEEDOR OCACIONAL $rif CREADO");
			echo "{ success: true, message: ".$data['data']['rif']."}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$rif = $campos['rif'];
		unset($campos['rif']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("provoca", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('provoca',"PROVEEDOR OCACIONAL ".$data['data']['rif']." MODIFICADO");
		echo "{ success: true, message: 'Proveedor ocacional Modificado '}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$rif = $data['data']['rif'];
		
		// VERIFICAR SI PUEDE
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM gitser WHERE rif='$rif'");

		if ($chek > 0){
			echo "{ success: false, message: 'Proveedor con Movimiento no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM provoca WHERE rif='$rif'");
			logusu('provoca',"PROVEEDOR OCACIONAL $rif ELIMINADO");
			echo "{ success: true, message: 'Proveedor Eliminado'}";
		}
	}



//****************************************************************
//
//
//
//****************************************************************

	function provocaextjs(){
		$encabeza='PROVEEDORES OCASIONALES';
		$listados= $this->datasis->listados('provoca');
		$otros=$this->datasis->otros('provoca', 'provoca');

		$consulrif=$this->datasis->traevalor('CONSULRIF');

		$urlajax = 'finanzas/provoca/';
		$variables = "";
		$funciones = "";
		$valida = "
		{ type: 'length', field: 'nombre', min:  3 }
		";
		

		$columnas = "
		{ header: 'Nro',    width:  50, sortable: true,  dataIndex: 'id',     field: { type: 'numberfield' }}, 
		{ header: 'R.I.F.', width: 100, sortable: true,  dataIndex: 'rif',    field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Nombre', width: 300, sortable: true,  dataIndex: 'nombre', field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Fecha',  width:  90, sortable: false, dataIndex: 'fecha',  field: { type: 'date'      }, filter: { type: 'date'   }} 
	";

		$campos = "'id', 'rif', 'nombre', 'fecha'";
		
		$camposforma = "
			\t\t\t\t{
			\t\t\t\tframe: false,
			\t\t\t\tborder: false,
			\t\t\t\tlabelAlign: 'right',
			\t\t\t\tdefaults: { xtype:'fieldset', labelWidth:70 },
			\t\t\t\tstyle:'padding:4px',
			\t\t\t\titems:[	
			\t\t\t\t	{ xtype: 'textfield', fieldLabel: 'RIF',    name: 'rif',    allowBlank: false, width: 200 },
			\t\t\t\t	{ xtype: 'textfield', fieldLabel: 'Nombre', name: 'nombre', allowBlank: false, width: 400 },
			\t\t\t\t	{ xtype: 'datefield', fieldLabel: 'Fecha',  name: 'fecha',  format: 'd/m/Y', submitFormat: 'Y-m-d', value: new Date(), }
			\t\t\t\t]}
		";

		$titulow = 'Aranceles';

		$dockedItems = "
				\t\t\t\t{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				\t\t\t\t{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
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
		
		$data['title']  = heading('Aranceles');
		$this->load->view('extjs/extjsven',$data);
		
	}


}
?>