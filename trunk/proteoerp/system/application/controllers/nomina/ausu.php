<?php
class ausu extends Controller {
	
	function ausu(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		if ( !$this->datasis->iscampo('asig','id') ) {
			$this->db->simple_query('ALTER TABLE ausu DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE ausu ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE ausu ADD UNIQUE INDEX codigo (codigo, concepto, fecha)');
		}
		$this->datasis->modulo_id(703,1);
		$this->ausuextjs();
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");
		$this->rapyd->uri->keep_persistence();

		$pers=array(
		'tabla'   =>'pers',
		'columnas'=>array(
		'codigo'  =>'Codigo',
		'cedula'  =>'Cedula',
		'nombre'  =>'Nombre',
		'apellido' =>'Apellido'),
		'filtro'  =>array('codigo'=>'C&oacute;digo','cedula'=>'Cedula'),
		'retornar'=>array('codigo'=>'codigo'),
		'titulo'  =>'Buscar Personal');
					  
		$boton=$this->datasis->modbus($pers);


		$filter = new DataFilter2("Filtro por C&oacute;digo", 'ausu');
		
		$filter->codigo = new inputField("Codigo","codigo");
		$filter->codigo->size=15;
		$filter->codigo->append($boton);
		$filter->codigo->clause = "likerigth";
		
		$filter->fecha = new DateonlyField("Fecha","fecha");
		$filter->fecha->size=12;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('nomina/aumentosueldo/dataedit/show/<#codigo#>/<raencode><#fecha#></raencode>','<#codigo#>');

		$grid = new DataGrid("Lista de Aumentos de Sueldo");
		$grid->order_by("codigo","asc");
		$grid->per_page = 20;

		$grid->column("C&oacute;digo"  ,$uri);
		$grid->column("Nombre"         ,"nombre");
		$grid->column("Fecha"          ,"<dbdate_to_human><#fecha#></dbdate_to_human>"           ,"align='center'");
		$grid->column("Sueldo anterior","<number_format><#sueldoa#>|2|,|.</number_format>"       ,"align='right'");
		$grid->column("Sueldo nuevo"   ,"<number_format><#sueldo#>|2|,|.</number_format>"        ,"align='right'");
		$grid->column("Observaciones"  ,"observ1");
		$grid->column("..","oberv2");
			
		$grid->add("nomina/aumentosueldo/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Aumentos de Sueldo</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}

	function dataedit()
 	{
		$this->rapyd->load("dataedit");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		$pers=array(                         
			'tabla'   =>'pers',                         
			'columnas'=>array(                         
			'codigo'  =>'Codigo',                         
			'cedula'  =>'Cedula',                         
			'nombre'  =>'Nombre',                         
			'apellido' =>'Apellido'),                         
			'filtro'  =>array('codigo'=>'C&oacute;digo','cedula'=>'Cedula'),
			'retornar'=>array('codigo'=>'codigo','nombre'=>'nombre'),     
			'titulo'  =>'Buscar Personal');                         
					                           
		$boton=$this->datasis->modbus($pers);                         
		
		$edit = new DataEdit("Aumentos de Sueldo", "ausu");
		$edit->back_url = site_url("nomina/aumentosueldo/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
	  		
		$edit->codigo =   new inputField("Codigo","codigo");
		$edit->codigo->size = 15;
		$edit->codigo->append($boton);
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength=15;
		$edit->codigo->rule="required|callback_chexiste";
		$edit->codigo->group="Trabajador";
		
		$edit->nombre =  new inputField("Nombre", "nombre");
		$edit->nombre->size =40;
		$edit->nombre->maxlength=30;
		$edit->nombre->group="Trabajador";		
		
		$edit->fecha = new dateField("Apartir de la nomina", "fecha","d/m/Y");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 12;
		$edit->fecha->dbformat    = 'Ymd';
		$edit->fecha->rule ="required|callback_fpositiva";
		
		$edit->sueldoa =   new inputField("Sueldo anterior", "sueldoa");
		$edit->sueldoa->size = 14;
		$edit->sueldoa->css_class='inputnum';
		$edit->sueldoa->rule='callback_positivoa';
		$edit->sueldoa->maxlength=11;
		
		$edit->sueldo =   new inputField("Sueldo nuevo", "sueldo");
		$edit->sueldo->size = 14;
		$edit->sueldo->css_class='inputnum';
		$edit->sueldo->rule='callback_positivo';
		$edit->sueldo->maxlength=11;
		
		$edit->observ1 =   new inputField("Observaciones", "observ1");
		$edit->observ1->size = 51;
		$edit->observ1->maxlength=46;
		
		$edit->oberv2 = new inputField("", "oberv2");
		$edit->oberv2->size =51;
		$edit->oberv2->maxlength=46;
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Aumentos de Sueldo</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		$fecha=$do->get('fecha');
		logusu('ausu',"AUMENTO DE SUELDO A $codigo NOMBRE  $nombre FECHA $fecha CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		$fecha=$do->get('fecha');
		logusu('ausu',"AUMENTO DE SUELDO A $codigo NOMBRE  $nombre FECHA $fecha MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		$fecha=$do->get('fecha');
		logusu('ausu',"AUMENTO DE SUELDO A $codigo NOMBRE  $nombre FECHA $fecha ELIMINADO ");
	}
	function chexiste($fecha){
		$fecha=$this->input->post('fecha');
		$codigo=$this->input->post('codigo');
		
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM ausu WHERE codigo='$codigo' AND fecha='$fecha'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM ausu WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El aumento para $codigo $nombre fecha $fecha ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	
	function positivo($valor){
		if ($valor <= 0){
			$this->validation->set_message('positivo',"El campo Sueldo Nuevo debe ser positivo");
			return FALSE;
		}
  	return TRUE;
	}
	
	function positivoa($valor){
		if ($valor <= 0){
			$this->validation->set_message('positivoa',"El campo Sueldo Anterior debe ser positivo");
			return FALSE;
		}
  	return TRUE;
	}
	
	function fpositiva($valor){
		if ($valor < date('Ymd')){
			$this->validation->set_message('fpositiva',"El campo Apartir de la nomina, Debe ser una nomina futura");
			return FALSE;
		}
  	return TRUE;
	}
	
	function _post($do){
	
		$codigo=$do->get('codigo');
		$fecha =$do->get('fecha');
		redirect('nomina/aumentosueldo/dataedit/show/'.$codigo.'/'.raencode($fecha));
		echo 'nomina/aumentosueldo/dataedit/show/'.$codigo.'/'.raencode($fecha);
		exit;
	}
	
	function instalar(){
		$mSQL="ALTER TABLE ausu ADD PRIMARY KEY (codigo,fecha);";
		$this->db->simple_query($mSQL);	
	}
	
	
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"fecha","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = "";

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('ausu');

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
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

		$results = $this->db->count_all('ausu');
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
		$codigo   = $data['data']['codigo'];
		$fecha    = $data['data']['fecha'];
		
		$campos['nombre']  = $this->datasis->dameval("SELECT CONCAT(TRIM(nombre),' ',TRIM(apellido)) nombre FROM pers WHERE codigo='$codigo'");
		
		unset($campos['id']);
		$mHay = $this->datasis->dameval("SELECT count(*) FROM ausu WHERE codigo='$codigo' AND fecha='$fecha' ");
		if  ( $mHay > 0 ){
			echo "{ success: false, message: 'Ya existe un registro igual para ese trabajador $codigo fecha $fecha'}";
		} else {
			$mSQL = $this->db->insert_string("ausu", $campos );
			$this->db->simple_query($mSQL);
			logusu('ausu',"AUMENTO DE SUELDO $codigo/$fecha CREADO");
			echo "{ success: true, message: ".$data['data']['codigo']."}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$codigo   = $campos['codigo'];
		$concepto = $campos['concepto'];

		$campos['nombre']  = $this->datasis->dameval("SELECT CONCAT(TRIM(nombre),' ',TRIM(apellido)) nombre FROM pers WHERE codigo='$codigo'");
		$campos['descrip'] = $this->datasis->dameval("SELECT descrip FROM conc WHERE concepto='$concepto'");

		unset($campos['codigo']);
		unset($campos['concepto']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("asig", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('asig',"ASIGNACIONES DE NOMINA ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Departamento de nomina Modificado '}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos= $data['data'];

		$departa = $data['data']['departa'];
		
		// VERIFICAR SI PUEDE
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE depto='$departa'");

		if ($chek > 0){
			echo "{ success: false, message: 'Departamento de nomina, no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM depa WHERE departa='$departa'");
			logusu('depa',"DIVISION DE NOMINA $departa ELIMINADO");
			echo "{ success: true, message: 'Departamento de nomina Eliminado'}";
		}
	}



//****************************************************************
//
//
//
//****************************************************************
	function asigextjs(){
		$encabeza='ASIGNACIONES DE NOMINA';
		$listados= $this->datasis->listados('asig');
		$otros=$this->datasis->otros('depa', 'asig');

		$mSQL = "SELECT concepto, CONCAT(concepto,' ',descrip) descrip FROM conc ORDER BY concepto ";
		$conc = $this->datasis->llenacombo($mSQL);

		$urlajax = 'nomina/asig/';
		$variables = "var mcliente = '', mcodigo='';";

		$funciones = "";
		
		$valida = "
		{ type: 'length', field: 'codigo',   min:  1 },
		{ type: 'length', field: 'concepto', min:  1 }
		";

		$columnas = "
		{ header: 'Codigo',      width:  60, sortable: true, dataIndex: 'codigo',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Nombre',      width: 220, sortable: true, dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Concepto',    width:  50, sortable: true, dataIndex: 'concepto', field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Tipo',        width:  50, sortable: true, dataIndex: 'tipo',     field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Descripcion', width: 220, sortable: true, dataIndex: 'descrip',  field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Formula',     width: 220, sortable: true, dataIndex: 'formula',  field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Monto',       width: 120, sortable: true, dataIndex: 'monto',    field: { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Cuotas',      width: 120, sortable: true, dataIndex: 'cuotat',   field: { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'valor',       width: 120, sortable: true, dataIndex: 'valor',    field: { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Fecha',       width:  70, sortable: true, dataIndex: 'fecha',    field: { type: 'date'      }, filter: { type: 'date'   }},
		{ header: 'Pagadas ',    width: 120, sortable: true, dataIndex: 'cuota',    field: { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') }
	";

		$campos = "'id', 'codigo', 'nombre', 'concepto','tipo', 'descrip', 'formula','monto','cuotat','valor','fecha','cuota'";
		$filtros = "\tvar filters = {	ftype: 'filters',encode: 'json', local: false }; ";
		
		$camposforma = "
				{
						frame: false,
						border: false,
						labelAlign: 'right',
						defaults: { xtype:'fieldset', labelWidth:70 },
						style:'padding:4px',
						items:[	
								{ xtype: 'combo',         fieldLabel: 'Trabajador', name: 'codigo',   mode: 'remote', hideTrigger: true, typeAhead: true, forceSelection: true, valueField: 'item', displayField: 'valor', store: persStore, width: 400, id: 'codigo'},
								{ xtype: 'combo',         fieldLabel: 'Concepto',   name: 'concepto', store: [".$conc."], width: 400 },
								{ xtype: 'textareafield', fieldLabel: 'Formula',    name: 'formula',  allowBlank: false,   width: 400 }
						]
				},{
						layout: 'column',
						frame: false,
						border: false,
						labelAlign: 'right',
						defaults: {xtype:'fieldset'  },
						style:'padding:4px',
						items: [
								{ xtype: 'datefield',   fieldLabel: 'Fecha Inicio',   name: 'fecha',  labelWidth: 90, format: 'd/m/Y', submitFormat: 'Y-m-d', value: new Date(),columnWidth:0.45 },
								{ xtype: 'numberfield', fieldLabel: 'Total Cuotas',   name: 'cuotat', labelWidth:110, hideTrigger: true, fieldStyle: 'text-align: right', columnWidth:0.45, renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Monto',          name: 'monto',  labelWidth: 90, hideTrigger: true, fieldStyle: 'text-align: right', columnWidth:0.45, renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Cuotas Pagadas', name: 'cuota',  labelWidth:110, hideTrigger: true, fieldStyle: 'text-align: right', columnWidth:0.45, renderer : Ext.util.Format.numberRenderer('0,000.00') }
						]
				}
		";


		$stores = "
var persStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,
	autoSync: false,
	pageSize: 50,
	pruneModifiedRecords: true,
	totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'nomina/pers/persbusca',
		extraParams: {  'codigo': mcodigo, 'origen': 'store' },
		reader: {
			type: 'json',
			totalProperty: 'results',
			root: 'data'
		}
	},
	method: 'POST'
});
		";

		$titulow = 'Asignaciones de Nomina';

		$dockedItems = "
				\t\t\t\t{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				\t\t\t\t{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 320,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						if (registro) {
							mcodigo  = registro.data.codigo;
							persStore.proxy.extraParams.codigo = mcodigo ;
							persStore.load({ params: { 'codigo':  registro.data.cliente, 'origen': 'beforeform' } });
							form.loadRecord(registro);
							form.findField('codigo').setReadOnly(true);
							form.findField('concepto').setReadOnly(true);
						} else {
							form.findField('codigo').setReadOnly(false);
							form.findField('concepto').setReadOnly(false);
							mcodigo  = '';
						}						
					}
				}
";

		$data['encabeza']    = $encabeza;
		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['urlajax']     = $urlajax;

		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['stores']      = $stores;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['winwidget']   = $winwidget;
		$data['filtros']     = $filtros;
		//$data['winmethod']   = $winmethod;
		
		$data['title']  = heading('Departamentos de Nomina');
		$this->load->view('extjs/extjsven',$data);
	}

	
}
?>