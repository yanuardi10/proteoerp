<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Scaj extends validaciones {

	function scaj(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(135,1);
	}
	
	function index(){
		$this->db->simple_query('UPDATE scaj SET cajero=TRIM(cajero)');
		if ( !$this->datasis->iscampo('scaj','id') ) {
			$this->db->simple_query('ALTER TABLE scaj DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE scaj ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE scaj ADD UNIQUE INDEX cajero (cajero)');
		}
		$this->scajextjs();
	}
	
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Buscar", 'scaj');
		
		$filter->cajero = new inputField('Cajero','cajero');
		$filter->cajero->size=10;
		
		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->size=30;
		
		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor("ventas/scaj/dataedit/show/<#cajero#>",'<#cajero#>');

		$grid = new DataGrid('Lista de Cajeros');
		$grid->order_by('nombre','asc');
		$grid->per_page = 10;

		//$grid->column_detail("C&oacute;digo","cajero", $uri, "size=14");
		$grid->column_orderby('C&oacute;digo',$uri,'cajero');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Estado','status','status');
		$grid->column_orderby('Almacen','almacen','almacen');
		$grid->column_orderby('Caja','caja','caja');

		$grid->add('ventas/scaj/dataedit/create','Agregar un cajero');
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Cajeros</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit('Cajeros', 'scaj');
		$edit->pre_process('delete','_pre_del');
		$edit->back_url = site_url('ventas/scaj/filteredgrid');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');
		
		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->codigo = new inputField('C&oacute;digo', 'cajero');
		$edit->codigo->rule = 'trim|strtoupper|required|callback_chexiste';
		$edit->codigo->mode = 'autohide';
		$edit->codigo->maxlength=5;
		$edit->codigo->size = 8;
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->maxlength=30;
		$edit->nombre->rule="trim|strtoupper|required";
		$edit->nombre->size =40;

		$edit->clave = new inputField('Clave', 'clave');
		$edit->clave->maxlength=6;
		$edit->clave->rule="trim";
		$edit->clave->size = 7;

		$edit->status = new dropdownField('Status', 'status');
		$edit->status->rule = 'required';
		$edit->status->options(array('C'=> 'Cerrado','A'=>'Abierto'));
		$edit->status->style='width:110px';

		$edit->almacen = new dropdownField("Almac&eacute;n", "almacen");
		$edit->almacen->option('','Seleccionar');
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");
		$edit->almacen->rule='required';
		$edit->almacen->style="width:150px";

		$edit->caja = new inputField("Caja", "caja");
		$edit->caja->size=4;
		$edit->caja->maxlength=2;
		$edit->caja->rule='trim|callback_ccaja';

		$edit->directo = new inputField('Directorio','directo');
		$edit->directo->size=70;
		$edit->directo->rule='trim';
		$edit->directo->maxlength=60;

		$edit->mesai = new inputField("Mesa desde", "mesai");
		$edit->mesai->maxlength=4;
		$edit->mesai->size=6;
		$edit->mesai->rule="trim";
		$edit->mesai->group="Mesas";

		$edit->mesaf  = new inputField("Mesa hasta", "mesaf");
		$edit->mesaf->maxlength=4;
		$edit->mesaf->size=6;
		$edit->mesaf->rule="trim";
		$edit->mesaf->group="Mesas";

		$edit->horai  = new inputField("Desde", "horai");
		$edit->horai->maxlength=8;
		$edit->horai->size=10;
		$edit->horai->rule='trim|callback_chhora';
		$edit->horai->append('hh:mm:ss');
		$edit->horai->group="Hora feliz";

		$edit->horaf  = new inputField("Hasta", "horaf");
		$edit->horaf->maxlength=8;
		$edit->horaf->size=10;
		$edit->horaf->rule='trim|callback_chhora';
		$edit->horaf->append('hh:mm:ss');
		$edit->horaf->group="Hora feliz";

		$edit->fechaa = new dateonlyfield("Fecha apertura", "fechaa");
		$edit->fechaa->maxlength=12;
		$edit->fechaa->size=15;
		$edit->fechaa->rule='chfecha';
		$edit->fechaa->group="Apertura";

		$edit->horaa  = new inputField("Hora apertura", "horaa");
		$edit->horaa->maxlength=12;
		$edit->horaa->size=15;
		$edit->horaa->rule='trim|callback_chhora';
		$edit->horaa->append('hh:mm:ss');
		$edit->horaa->group="Apertura";

		$edit->apertura =new inputField("Monto apertura", "apertura");
		$edit->apertura->maxlength=12;
		$edit->apertura->size=14;
		$edit->apertura->group="Apertura";
		$edit->apertura->css_class='inputnum';
		$edit->apertura->rule='numeric';

		$edit->fechac = new dateonlyfield('Fecha cierre', 'fechac');
		$edit->fechac->maxlength=12;
		$edit->fechac->size=14;
		$edit->fechac->rule='chfecha';
		$edit->fechac->group='Apertura';

		$edit->horac  = new inputField('Hora cierre', 'horac');
		$edit->horac->maxlength=8;
		$edit->horac->size=10;
		$edit->horac->rule='trim|callback_chhora';
		$edit->horac->append('hh:mm:ss');
		$edit->horac->group="Apertura";

		$edit->cierre   =new inputField("Monto Cierre", "cierre");
		$edit->cierre->maxlength=12;
		$edit->cierre->size=15;
		$edit->cierre->group='Apertura';
		$edit->cierre->css_class='inputnum';
		$edit->cierre->rule='trim|numeric';

		$edit->buttons('modify','save','undo','delete','back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Cajeros</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _pre_del($do) {
		$codigo=$this->db->escape($do->get('cajero'));
		$tables = $this->db->list_tables();
		$sum=0;
		if(in_array('vieite',$tables)) $sum+=$this->datasis->dameval("SELECT COUNT(*) FROM vieite WHERE cajero=$codigo");
		if(in_array('fmay'  ,$tables)) $sum+=$this->datasis->dameval("SELECT COUNT(*) FROM fmay   WHERE cajero=$codigo");
		if(in_array('sfac'  ,$tables)) $sum+=$this->datasis->dameval("SELECT COUNT(*) FROM sfac   WHERE cajero=$codigo");

		if($sum != 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='No se puede borrar un cajero con ventas';
			return False;
		}else
			return True;
	}
	function _post_insert($do){
		$codigo=$do->get('cajero');
		$nombre=$do->get('nombre');
		$status=$do->get('status');
		logusu('scaj',"CAJERO $codigo NOMBRE $nombre STATUS $status CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('cajero');
		$nombre=$do->get('nombre');
		$status=$do->get('status');
		logusu('scaj',"CAJERO $codigo NOMBRE $nombre STATUS $status MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('cajero');
		$nombre=$do->get('nombre');
		$status=$do->get('status');
		logusu('scaj',"CAJERO $codigo NOMBRE $nombre STATUS $status ELIMINADO");
	}
	
	//VALIDACIONES
	function chexiste($codigo){
		$codigo=$this->input->post('cajero');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM scaj WHERE cajero='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM scaj WHERE cajero='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el cajero $nombre");
			return FALSE;
		}else {
		return TRUE;
		}
	}
	
	function ccaja($caja){
		$cant=$this->datasis->dameval("SELECT COUNT(*) FROM banc WHERE codbanc='$caja'");
		//$link=anchor('','aqui');
		if($cant==0){
			$this->validation->set_message('ccaja',"El codigo de caja '$caja' no existe");
			return FALSE;
		}
		return TRUE;
	}
	
	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `vieite` (
			`numero` char(8) default NULL,
			`fecha` date default '0000-00-00',
			`codigo` char(15) default NULL,
			`precio` decimal(10,2) default '0.00',
			`monto` decimal(18,2) default '0.00',
			`cantidad` decimal(12,3) default NULL,
			`impuesto` decimal(6,2) default '0.00',
			`costo` decimal(18,2) default '0.00',
			`almacen` char(4) default NULL,
			`cajero` char(5) default NULL,
			`caja` char(5) NOT NULL default '',
			`referen` char(15) default NULL,
			KEY `fecha` (`fecha`),
			KEY `codigo` (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='ventas por articulo'";
		$this->db->simple_query($mSQL);
		$mSQL="CREATE TABLE IF NOT EXISTS `fmay` (
			`fecha` date default NULL,
			`numero` varchar(8) NOT NULL default '',
			`presup` varchar(8) default NULL,
			`almacen` varchar(4) default NULL,
			`cod_cli` varchar(5) default NULL,
			`nombre` varchar(40) default NULL,
			`vence` date default NULL,
			`vende` varchar(5) default NULL,
			`stotal` decimal(17,2) default '0.00',
			`impuesto` decimal(17,2) default '0.00',
			`gtotal` decimal(17,2) default '0.00',
			`tipo` char(1) default NULL,
			`observa1` varchar(40) default NULL,
			`observa2` varchar(40) default NULL,
			`observa3` varchar(40) default NULL,
			`porcenta` decimal(17,2) default '0.00',
			`descuento` decimal(17,2) default '0.00',
			`cajero` varchar(5) default NULL,
			`dire1` varchar(30) default NULL,
			`dire2` varchar(30) default NULL,
			`rif` varchar(15) default NULL,
			`nit` varchar(15) default NULL,
			`exento` decimal(17,2) default '0.00',
			`transac` varchar(8) default NULL,
			`estampa` date default NULL,
			`hora` varchar(5) default NULL,
			`usuario` varchar(12) default NULL,
			`nfiscal` varchar(12) NOT NULL default '0',
			`tasa` decimal(19,2) default NULL,
			`reducida` decimal(19,2) default NULL,
			`sobretasa` decimal(17,2) default NULL,
			`montasa` decimal(17,2) default NULL,
			`monredu` decimal(17,2) default NULL,
			`monadic` decimal(17,2) default NULL,
			`cedula` varchar(13) default NULL,
			`dirent1` varchar(40) default NULL,
			`dirent2` varchar(40) default NULL,
			`dirent3` varchar(40) default NULL,
			PRIMARY KEY  (`numero`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}
	
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"cajero","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('scaj');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('scaj');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$cajero = $campos['cajero'];

		if ( !empty($cajero) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM scaj WHERE cajero='$cajero'") == 0)
			{
				$mSQL = $this->db->insert_string("scaj", $campos );
				$this->db->simple_query($mSQL);
				logusu('scaj',"CAJERO $cajero CREADO");
				echo "{ success: true, message: 'Cajero Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un cajero con ese Codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe un cajero con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$cajero = $campos['cajero'];
		unset($campos['cajero']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("scaj", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('scaj',"CAJERO $cajero ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Cajero Modificado -> ".$data['data']['cajero']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$cajero = $campos['cajero'];
		$chek  =  $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE cajero='$cajero'");
		$chek +=  $this->datasis->dameval("SELECT COUNT(*) FROM sfpa WHERE cobrador='$cajero'");

		if ($chek > 0){
			echo "{ success: false, message: 'Cajero no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM scaj WHERE cajero='$cajero'");
			logusu('scaj',"CAJERO $cajero ELIMINADO");
			echo "{ success: true, message: 'Cajero Eliminado'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function scajextjs(){
		$encabeza='CAJEROS';
		$listados= $this->datasis->listados('scaj');
		$otros=$this->datasis->otros('scaj', 'scaj');

		$mSQL = "SELECT ubica, CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ORDER BY ubica";
		$alma = $this->datasis->llenacombo($mSQL);

		$mSQL  = "SELECT codbanc, CONCAT(codbanc,' ',banco) banco FROM banc WHERE tbanco='CAJ' ORDER BY codbanc";
		$cajas = $this->datasis->llenacombo($mSQL);

		$mSQL  = "SELECT vendedor, CONCAT(vendedor,' ',nombre) nombre FROM vend ORDER BY vendedor";
		$vende = $this->datasis->llenacombo($mSQL);

		$urlajax = 'ventas/scaj/';
		$variables = "";

		$funciones = "
function estado(val){
	if ( val == 'A'){ return 'Abierto';}
	else if ( val == 'C'){return  'Cerrado';}
}
";

		$valida = "
		{ type: 'length', field: 'cajero', min: 1 },
		{ type: 'length', field: 'nombre', min: 1 }
		";
		
		$columnas = "
		{ header: 'Codigo',    width:  50, sortable: true, dataIndex: 'cajero',   field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Nombre',    width: 180, sortable: true, dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Status',    width:  70, sortable: true, dataIndex: 'status',   field: { type: 'textfield' }, filter: { type: 'string'  }, renderer: estado },
		{ header: 'Vendedor',  width:  60, sortable: true, dataIndex: 'vendedor', field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Caja',      width:  40, sortable: true, dataIndex: 'caja',     field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Apertura',  width:  80, sortable: true, dataIndex: 'fechaa',   field: { type: 'datefield' }, filter: { type: 'date'    } }, 
		{ header: 'Hora',      width:  50, sortable: true, dataIndex: 'horaa',    field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Cierre',    width:  80, sortable: true, dataIndex: 'fechaa',   field: { type: 'datefield' }, filter: { type: 'date'    } }, 
		{ header: 'Hora',      width:  50, sortable: true, dataIndex: 'horac',    field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Fondo',     width:  90, sortable: true, dataIndex: 'apertura', field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Alamcen',   width:  60, sortable: true, dataIndex: 'almacen',  field: { type: 'testfield' }, filter: { type: 'string'  } }, 
		{ header: 'Carpeta',   width: 200, sortable: true, dataIndex: 'directo',  field: { type: 'textfield' }, filter: { type: 'string'  } }, 
	";

		$campos = "'id', 'cajero', 'nombre', 'clave', 'fechaa', 'horaa', 'apertura', 'fechac', 'horac', 'cierre', 'status', 'directo', 'mesai', 'mesaf', 'horai', 'horaf', 'caja', 'almacen', 'vendedor'";
		
		$camposforma = "
							{
							xtype:'fieldset',
							//title: 'REGISTRO',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield', fieldLabel: 'Codigo',  name: 'cajero',  allowBlank: false,  width: 120, id: 'codigo' },
									{ xtype: 'combo',     fieldLabel: 'Status',  name: 'status',                      width: 130,  store: [['A','Abierto'],['C','Cerrado']], labelWidth:50},
									{ xtype: 'textfield', fieldLabel: 'Clave',   name: 'clave', allowBlank: true, width: 150, inputType: 'password', labelWidth:50 },
									{ xtype: 'textfield', fieldLabel: 'Nombre',  name: 'nombre',  allowBlank: false,  width: 400 },
									{ xtype: 'combo',     fieldLabel: 'Caja',    name: 'caja',    store: [".$cajas."], width: 400 },
									{ xtype: 'combo',     fieldLabel: 'Almacen', name: 'almacen', store: [".$alma."], width: 300 },
									{ xtype: 'combo',     fieldLabel: 'Vendedor', name: 'vendedor', store: [".$vende."], width: 400 },
									{ xtype: 'textfield', fieldLabel: 'Carpeta', name: 'directo', allowBlank: true,   width: 400 }
								]
							},{
							xtype:'fieldset',
							title: 'APERTURA/CIERRE',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { labelWidth:60 },
							style:'padding:4px',
							layout: 'column',
							items: [
								{ xtype: 'datefield',   fieldLabel: 'Apertura', name: 'fechaa',   width:160, labelWidth:60, format: 'd/m/Y', submitFormat: 'Y-m-d' },
								{ xtype: 'textfield',   fieldLabel: 'Hora',     name: 'horaa',    width:100, labelWidth:40 },
								{ xtype: 'numberfield', fieldLabel: 'Monto',    name: 'apertura', width:140, labelWidth:50, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'datefield',   fieldLabel: 'Cierre',   name: 'fechac',   width:160, labelWidth:60, format: 'd/m/Y', submitFormat: 'Y-m-d' },
								{ xtype: 'textfield',   fieldLabel: 'Hora',     name: 'horac',    width:100, labelWidth:40 },
								{ xtype: 'numberfield', fieldLabel: 'Monto',    name: 'cierre',   width:140, labelWidth:50, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
							]
							},{
							xtype:'fieldset',
							title: 'RESTAURANTE',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype: 'textfield', allowBlank: true },
							style:'padding:4px',
							layout: 'column',
							items: [
								{ fieldLabel: 'Mesas Validas', name: 'mesai',  width:200, labelWidth:120 },
								{ fieldLabel: 'Hasta',           name: 'mesaf',  width:140, labelWidth: 70 },
								{ fieldLabel: 'Hora Feliz',      name: 'horai',  width:200, labelWidth:120 },
								{ fieldLabel: 'Hasta',           name: 'horaf',  width:140, labelWidth: 70 },
							]
							}
		";

		$titulow = 'Cajeros';

		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 460,
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
		
		$data['title']  = heading('Cajeros');
		$this->load->view('extjs/extjsven',$data);
		
	}
	
}
?>