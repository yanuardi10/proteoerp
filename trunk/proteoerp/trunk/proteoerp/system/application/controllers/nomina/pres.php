<?php
//adelantoprestamos
class Pres extends Controller {
	
	function pres(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
	
	function index(){
		if ( !$this->datasis->iscampo('pres','id') ) {
			$this->db->simple_query('ALTER TABLE pres DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE pres ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE pres ADD UNIQUE INDEX cliente (cod_cli, tipo_doc, numero )');
		}
		$this->datasis->modulo_id(710,1);
		$this->presextjs();
	}
 
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por N&uacute;mero", 'pres');
		
		$filter->numero = new inputField("N&uacute;mero", "numero");
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('nomina/pres/dataedit/show/<#cod_cli#>/<#tipo_doc#>/<#numero#>','<#numero#>');

		$grid = new DataGrid("Lista de Adelantos de Prestaciones");
		$grid->order_by("numero","asc");
		$grid->per_page = 20;

		$grid->column("N&uacute;mero",$uri);
		$grid->column("Nombre","nombre");
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Tipo","tipo_doc");
		$grid->column("Monto","monto");
				
		$grid->add("nomina/pres/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Adelantos de Prestaciones</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit()	{
		$this->rapyd->load("dataedit");
 		$edit = new DataEdit("Adelanto de Prestamos", "pres");
		$edit->back_url = site_url("nomina/pres/filteredgrid");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
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
		'apellido'=>'Apellido'),
		'filtro'  =>array('codigo'=>'C&oacute;digo','cedula'=>'Cedula'),
		'retornar'=>array('codigo'=>'codigo','nombre'=>'nombre'),
		'titulo'  =>'Buscar Personal');
					  
		$boton1=$this->datasis->modbus($pers);

 		$scli=array(
	  'tabla'   =>'scli',
	  'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'contacto'=>'Contacto'),
	  'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
	  'retornar'=>array('cliente'=>'cod_cli'),
	  'titulo'  =>'Buscar Cliente');
		
		$boton=$this->datasis->modbus($scli);
		
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->enlase =  new inputField("Enlase","cod_cli");
		$edit->enlase->mode="autohide";
		$edit->enlase->size =7;
		$edit->enlase->maxlength=5;
		$edit->enlase->rule = "required";
		$edit->enlase->append($boton);
		
		$edit->tipo = new dropdownField("Tipo", "tipo_doc");  
		$edit->tipo->option("ND","ND");
		$edit->tipo->option("NC","NC");
		$edit->tipo->style='width:60px';
		$edit->tipo->mode="autohide";
		$edit->tipo->rule="required";
		
		$edit->numero =  new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size =10;
		$edit->numero->maxlength=8;
		$edit->numero->rule = "required|callback_chexiste";
		
		$edit->fecha = new DateField("Fecha","fecha");
		$edit->fecha->size = 12;
		
		$edit->codigo =  new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size = 15;
		$edit->codigo->maxlength=15;
		$edit->codigo->append($boton1);
		$edit->codigo->rule="required";
		$edit->codigo->group="Trabajador";
		
		$edit->nombre = new inputField("Nombre","nombre");
		$edit->nombre->size =45;
		$edit->nombre->maxlength=35;
		$edit->nombre->group="Trabajador";
	  	
		$edit->monto = new inputField("Saldo","monto");
		$edit->monto->size =17;
		$edit->monto->maxlength=14;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='numeric';
		$edit->monto->group="Datos de Prestamo";

		$edit->nroctas = new inputField("Nº Cuota","nroctas");
		$edit->nroctas->size =4;
		$edit->nroctas->maxlength=2;
		$edit->nroctas->css_class='inputnum';
		$edit->nroctas->rule='integer';
		$edit->nroctas->group="Datos de Prestamo";

		$edit->cuota = new inputField("Cuota","cuota");
		$edit->cuota->size = 17;
		$edit->cuota->maxlength=14;
		$edit->cuota->css_class='inputnum';
		$edit->cuota->rule='numeric';
		$edit->cuota->group="Datos de Prestamo";
	  	  
		$edit->apartir = new DateonlyField("Cobrar A partir de:","apartir");
		$edit->apartir->size = 12;
		$edit->apartir->group="Datos de Prestamo";
    
		$edit->cadano = new inputField("Frecuencia","cadano");
		$edit->cadano->size =2;
		$edit->cadano->maxlength=1;
		$edit->cadano->group="Datos de Prestamo";
		
		$edit->observ1 = new inputField("Observaciones","observ1");
		$edit->observ1->size =45;
		$edit->observ1->maxlength=46;
		$edit->observ1->group="Datos de Prestamo";		
		
		$edit->observ2 = new inputField("","oberv2");
		$edit->observ2->size = 45;
		$edit->observ2->maxlength=46;
		$edit->observ2->group="Datos de Prestamo";
		
		//$edit->pagado = new inputField("Pagado","pagado");
		//$edit->pagado->size = 1;
									
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Adelanto Prestamos</h1>";        
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function _post_insert($do){
		$codigo=$do->get('numero');
		$nombre=$do->get('nombre');
		logusu('pres',"ADELANTO DE PRESTACIONES PARA  $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('numero');
		$nombre=$do->get('nombre');
		logusu('pres',"ADELANTO DE PRESTACIONES PARA  $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('numero');
		$nombre=$do->get('nombre');
		logusu('pres',"ADELANTO DE PRESTACIONES PARA  $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($tipo_doc){
		$tipo_doc=$this->input->post('tipo_doc');
		$codigo=$this->input->post('cod_cli');
		$numero=$this->input->post('numero');
		//echo 'numero'.$numero.'codigo'.$codigo.'tipo'.$tipo_doc;
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM pres WHERE tipo_doc='$tipo_doc' AND numero='$numero' AND cod_cli='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM pres WHERE cod_cli='$codigo'");
			$this->validation->set_message('chexiste',"Adelanto de Prestamo para $nombre ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}

	
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"fecha","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('pres');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();

		$results = $this->db->count_all('pres');
		$arr = $this->datasis->codificautf8($query->result_array());
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
		$mHay = $this->datasis->dameval("SELECT count(*) FROM pres WHERE codigo='$codigo' AND fecha='$fecha' ");
		if  ( $mHay > 0 ){
			echo "{ success: false, message: 'Ya existe un registro igual para ese trabajador $codigo fecha $fecha'}";
		} else {
			$mSQL = $this->db->insert_string("pres", $campos );
			$this->db->simple_query($mSQL);
			logusu('pres',"PRESTAMO DE NOMINA $codigo/$fecha CREADO");
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
		logusu('pres',"PRESTAMO DE NOMINA ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Prestamo de nomina Modificado '}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos= $data['data'];

		$departa = $data['data']['departa'];
		
		// VERIFICAR SI PUEDE
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE depto='$departa'");

		if ($chek > 0){
			echo "{ success: false, message: 'Prestamo de nomina, no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM depa WHERE departa='$departa'");
			logusu('pres',"PRESTAMO DE NOMINA $departa ELIMINADO");
			echo "{ success: true, message: 'Prestamo de nomina Eliminado'}";
		}
	}



//****************************************************************
//
//
//
//****************************************************************
	function presextjs(){
		$encabeza='DESCUENTO DE PRESTAMOS POR NOMINA';
		$listados= $this->datasis->listados('pres');
		$otros=$this->datasis->otros('pres', 'pres');

		$urlajax = 'nomina/pres/';
		$variables = "var mcodigo = '';";

		$funciones = "";
		
		$valida = "
		{ type: 'length', field: 'codigo', min:  1 }
		";

		$columnas = "
		{ header: 'Codigo',      width:  60, sortable: true, dataIndex: 'codigo',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Nombre',      width: 220, sortable: true, dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fecha',       width:  70, sortable: true, dataIndex: 'fecha',    field: { type: 'date'      }, filter: { type: 'date'   }},
		{ header: 'Cliente',     width:  60, sortable: true, dataIndex: 'cod_cli',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Tipo',        width:  40, sortable: true, dataIndex: 'tipo_doc', field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Numero',      width:  60, sortable: true, dataIndex: 'numero',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Monto',       width: 120, sortable: true, dataIndex: 'monto',    field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'A partir',    width:  70, sortable: true, dataIndex: 'apartir',  field: { type: 'date'      }, filter: { type: 'date'   }},
		{ header: 'Cuota',       width: 120, sortable: true, dataIndex: 'cuota',    field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Observacion', width: 220, sortable: true, dataIndex: 'observ1',  field: { type: 'textfield' }, filter: { type: 'string' }},
	";

		$campos = "'id', 'codigo', 'nombre', 'fecha','cod_cli', 'tipo_doc', 'numero', 'monto','apartir','cuota','nroctas','cadano','observ1', 'oberv2'";
		$filtros = "var filters = { ftype: 'filters',encode: 'json', local: false }; ";
		
		$camposforma = "
				{
						frame: false,
						border: false,
						labelAlign: 'right',
						defaults: { xtype:'fieldset', labelWidth:70 },
						style:'padding:4px',
						items:[	
							{
								xtype: 'combo',
								fieldLabel: 'Trabajador',
								name: 'codigo',
								mode: 'remote',
								hideTrigger: true,
								typeAhead: true,
								forceSelection: true,
								valueField: 'item',
								displayField: 'valor',
								store: persStore,
								width: 410,
								id: 'codigo',
								listeners: { select: function(combo, record, index){
									var sele   = combo.getValue();
									var i = 0;
									var msueldo = 0;
									for ( i=0; i < combo.store.count();i=i+1 ){
										if ( combo.store.getAt(i).get('item') == sele ){
											msueldo=combo.store.getAt(i).get('sueldo');
										}
									}
									Ext.getCmp('sueldoa').setValue(msueldo);
									Ext.getCmp('sueldo').setValue(msueldo);
									
									
								}}
							}
						]
				},{
						layout: 'column',
						frame: false,
						border: false,
						labelAlign: 'right',
						defaults: {xtype:'fieldset', labelWidth: 80  },
						style:'padding:4px',
						items: [
							{ xtype: 'datefield',   fieldLabel: 'Fecha',       name: 'fecha', width:180, format: 'd/m/Y', submitFormat: 'Y-m-d', labelWidth: 40 },
							{ xtype: 'textfield',   fieldLabel: 'Enlace Administrativo',     name: 'cod_cli',   width:230, allowBlank: true, labelWidth: 140 },
							{ xtype: 'textfield',   fieldLabel: 'Tipo',        name: 'tipo_doc',  width: 80, allowBlank: true, labelWidth: 40 },
							{ xtype: 'textfield',   fieldLabel: 'Numero',      name: 'numero',    width:150, allowBlank: true, labelWidth: 60 },
							{ xtype: 'numberfield', fieldLabel: 'Monto',       name: 'monto',     width:180, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), id: 'monto', labelWidth: 60  },
							{ xtype: 'numberfield', fieldLabel: 'Cuota',       name: 'cuota',     width:140, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), labelWidth: 40  },
							{ xtype: 'datefield',   fieldLabel: 'A partir de', name: 'apartir',   width:160, format: 'd/m/Y', submitFormat: 'Y-m-d', labelWidth: 70 },
							{ xtype: 'numberfield', fieldLabel: 'Frec.',  name: 'cadano',    width: 90, hideTrigger: false, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), labelWidth: 40  },
							{ xtype: 'textfield',   fieldLabel: 'Observacion', name: 'observ1',   width:410, allowBlank: true }
						]
				}
		";


		$stores = "
var persStore = new Ext.data.Store({
	fields: [ 'item', 'valor', 'sueldo'],
	autoLoad: false,
	autoSync: false,
	name: 'Pers',
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
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 260,
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
						} else {
							form.findField('codigo').setReadOnly(false);
							mcodigo  = '';
						}
					}
				}
";
		$features = "features: [ filters],";

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
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		
		$data['title']  = heading('Departamentos de Nomina');
		$this->load->view('extjs/extjsven',$data);
	}
}
?>