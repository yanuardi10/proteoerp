<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//crucecuentas
class Mvcerti extends validaciones {
	var $data_type = null;
	var $data = null;
	 
	function mvcerti(){
		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->load->library("rapyd");
		if (!$this->datasis->istabla('mvcerti')) {
			$mSQL = "
				CREATE TABLE mvcerti (
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					cliente CHAR(5) NULL DEFAULT NULL COMMENT 'Codigo del Cliente',
					numero CHAR(32) NULL DEFAULT NULL COMMENT 'Numero de Certificado',
					fecha DATE NULL DEFAULT NULL COMMENT 'Fecha del certificado',
					obra VARCHAR(200) NULL DEFAULT NULL COMMENT 'Nombre de la Obra',
					status CHAR(1) NULL DEFAULT 'A' COMMENT 'Activo Cerrado',
					PRIMARY KEY (id),
					UNIQUE INDEX numero (numero),
					INDEX cliente (cliente)
				)
				COLLATE='latin1_swedish_ci'
				ENGINE=MyISAM
				ROW_FORMAT=DEFAULT
";
			$this->db->simple_query($mSQL);
			
		}
		
	}
	function index(){
		$this->datasis->modulo_id(506,1);
		//redirect("ventas/mvcerti/filteredgrid");
		$this->mvcertiextjs();
	}
	
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Certificados Mision Vivienda", 'mvcerti');
		
		$filter->tipo = new inputField("N&uacute;mero", "numero");
		$filter->tipo->size=32;
		
		$filter->cliente = new inputField("Cliente", "cliente");
		$filter->cliente->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/mvcerti/dataedit/show/<#id#>','<#numero#>');

		$grid = new DataGrid("Certificados Mision Vivienda");
		$grid->order_by("id","DESC");
		$grid->per_page = 20;

		$grid->column("Registro",'id');
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Status","status");
		$grid->column("Cliente","cliente");
		$grid->column("Obra","obra");

		$grid->add("ventas/mvcerti/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Certificados de Mision Vivienda');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load("dataedit","dataobject");
		$link=site_url('ventas/mvcerti/');
		$script ='';

		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre', 
		'cirepre'=>'Rif/Cedula',
		'dire11'=>'Direcci&oacute;n'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cliente','nombre'=>'nomcli'),
		'titulo'  =>'Buscar Cliente');
		$boton3 =$this->datasis->modbus($mSCLId,'mSCLId');

		$do = new DataObject("mvcerti");
		$do->pointer('scli','scli.cliente = mvcerti.cliente','scli.nombre as nomcli' ,'LEFT');

		$edit = new DataEdit("Certificados Mision Vivienda",$do);
		$edit->back_url = site_url("ventas/mvcerti/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		//$lnum='<a href="javascript:ultimo();" title="Consultar ultimo cruce de cuentas ingresado" onclick="">Consultar ultimo cruce de cuentas</a>';	


		$edit->id =   new inputField("Registro", "id");
		$edit->id->mode="autohide";
		$edit->id->size = 10;
		$edit->id->maxlength=10;
		$edit->id->when = array('show', 'modify');

		$edit->numero =   new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 42;
		$edit->numero->maxlength=32;
		$edit->numero->rule="trim|required|callback_chexiste";

		$edit->fecha =new DateField("Fecha", "fecha");
		$edit->fecha->size = 12;

		$edit->tipo = new dropdownField("Status", "status");
		$edit->tipo->option("A","Activo");
		$edit->tipo->option("C","Cerrador");
		$edit->tipo->style="width:110px";

		$edit->cliente =  new inputField("Cliente", "cliente");
		$edit->cliente->db->name="cliente";
		$edit->cliente->rule="trim";
		$edit->cliente->size =12;
		$edit->cliente->readonly=true;
		$edit->cliente->append($boton3);

		$edit->nomcli =   new inputField("Nombre",'nomcli');
		$edit->nomcli->size =42;
		$edit->nomcli->maxlength=40;
		$edit->nomcli->pointer = true;
		$edit->nomcli->readonly = true;

		$edit->obra = new TextareaField("Obra","obra");
		$edit->obra->cols = 50;
		$edit->obra->rows = 4;

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$smenu['link']   = barra_menu('506');

		$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['content'] = $edit->output;
		$data['title']   = "<h1>Certificados Mision Vivienda</h1>";

		$data['script']  = script("jquery.pack.js");
		$data['script'] .= script("plugins/jquery.numeric.pack.js");
		$data['script'] .= script("plugins/jquery.floatnumber.js");

		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('cruc',"CRUCE DE CUENTA $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('cruc',"CRUCE DE CUENTA $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('cruc',"CRUCE DE CUENTA $codigo ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('numero');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM mvcerti WHERE numero='$codigo'");
		if ($chek > 0){
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe");
			return FALSE;
		}else {
		return TRUE;
		}
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"cliente","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'mvcerti');
		
		$this->db->_protect_identifiers=false;
		$this->db->select('mvcerti.*, scli.nombre');
		$this->db->from('mvcerti');
		$this->db->join('scli','mvcerti.cliente=scli.cliente');
		
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('mvcerti');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$cliente = $campos['cliente'];
		$numero  = $campos['numero'];

		unset($campos['id']);
		unset($campos['nombre']);

		if ( !empty($cliente) and !empty($numero)  ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM mvcerti WHERE cliente='$cliente' AND numero='$numero'") == 0)
			{
				$mSQL = $this->db->insert_string("mvcerti", $campos );
				$this->db->simple_query($mSQL);
				logusu('mvcerti',"CERTIFICADO MV $cliente numero $numero CREADO");
				echo "{ success: true, message: 'Certificaso Agregado".$mSQL."'}";
			} else {
				echo "{ success: false, message: 'Ya existe un certificado con ese numero!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Falta Cliente o Certificado!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$cliente = $campos['cliente'];
		$numero  = $campos['numero'];

		unset($campos['id']);
		unset($campos['nombre']);
		unset($campos['cliente']);

		//Cambia el Certificado en las facturas
		$mcertifi = $this->datasis->dameval("SELECT numero FROM mvcerti WHERE id=".$data['data']['id']);

		$mSQL = $this->db->update_string("mvcerti", $campos,"id=".$data['data']['id'] );
		$this->db->simple_query($mSQL);

		// Solo si cambioel certificado
		if ( $numero <> $mcertifi) {		
			$mSQL = "UPDATE sfac SET certificado='".$numero."' WHERE certificado='".$mcertifi."' AND cod_cli='".$cliente."'";
			$this->db->simple_query($mSQL);
		}
		
		logusu('mvcerti',"CAJERO $cajero ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Cajero Modificado -> ".$data['data']['cajero']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$cliente = $campos['cliente'];
		$numero  = $campos['numero'];

		$chek  =  $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE certificado='$numero' AND cod_cli='$cliente'");

		if ($chek > 0){
			echo "{ success: false, message: 'Certificado no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM mvcerti WHERE id=".$data['data']['id']);
			logusu('mvcerti',"CERTIFICADO $cliente/$numero ELIMINADO");
			echo "{ success: true, message: 'Certificado Eliminado'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function mvcertiextjs(){
		$encabeza='CERTIFICADOS DE EXONERACION DE IVA';
		$listados= $this->datasis->listados('mvcerti');
		$otros=$this->datasis->otros('mvcerti', 'ventas/mvcerti');
/*
		$mSQL = "SELECT ubica, CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ORDER BY ubica";
		$alma = $this->datasis->llenacombo($mSQL);

		$mSQL  = "SELECT codbanc, CONCAT(codbanc,' ',banco) banco FROM banc WHERE tbanco='CAJ' ORDER BY codbanc";
		$cajas = $this->datasis->llenacombo($mSQL);

		$mSQL  = "SELECT vendedor, CONCAT(vendedor,' ',nombre) nombre FROM vend ORDER BY vendedor";
		$vende = $this->datasis->llenacombo($mSQL);
*/

		$urlajax = 'ventas/mvcerti/';
		$variables = "var mcliente='';";

		$funciones = "function estado(val){if ( val == 'A'){ return 'Activo';} else if ( val == 'C'){return  'Cerrado';}}";

		$valida = "
		{ type: 'length', field: 'cliente', min: 1 }
		//{ type: 'length', field: 'numero',  min: 32 }
		";
		
		$columnas = "
		{ header: 'Cliente',     width:  50, sortable: true, dataIndex: 'cliente', field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Nombre',      width: 210, sortable: true, dataIndex: 'nombre',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Status',      width:  60, sortable: true, dataIndex: 'status',  field: { type: 'textfield' }, filter: { type: 'string' }, renderer: estado },
		{ header: 'Fecha',       width:  70, sortable: true, dataIndex: 'fecha',   field: { type: 'datefield' }, filter: { type: 'date'   }}, 
		{ header: 'Certificado', width: 210, sortable: true, dataIndex: 'numero',  field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Obra',        width: 400, sortable: true, dataIndex: 'obra',    field: { type: 'textfield' }, filter: { type: 'string' }}, 
	";

		$campos = "'id','cliente','numero','fecha','obra','status', 'nombre'";
		
		$camposforma = "
							{
							xtype:'fieldset',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'combo',         fieldLabel: 'Cliente', name: 'cliente', labelWidth: 50,  width: 400,   id: 'cliente',mode: 'remote',hideTrigger: true, typeAhead: true, forceSelection: true,valueField: 'item', displayField: 'valor',store: scliStore},
									{ xtype: 'datefield',     fieldLabel: 'Fecha',   name: 'fecha',   labelWidth: 50,  width: 150,  format: 'd/m/Y', submitFormat: 'Y-m-d' },
									{ xtype: 'combo',         fieldLabel: 'Status',  name: 'status',  labelWidth:150,  width: 250,  store: [['A','Activo'],['C','Cerrado']]},
									{ xtype: 'textfield',     fieldLabel: 'Numero',  name: 'numero',  labelWidth: 50,  width: 300,   allowBlank: false },
									{ xtype: 'textareafield', fieldLabel: 'Obra',    name: 'obra',    labelWidth: 50,  width: 400,  allowBlank: true }
								]
							}
		";

		$titulow = 'Cajeros';

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 300,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						
						if (registro) {
							mcliente = registro.data.cliente;
							scliStore.proxy.extraParams.cliente = mcliente ;
							scliStore.load({ params: { 'cuenta':  registro.data.cliente, 'origen': 'beforeform' } });
							form.loadRecord(registro);
						} else {
							mcliente = '';
						}
					}
				}
";

		$stores = "
var scliStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false, autoSync: false, pageSize: 30, pruneModifiedRecords: true, totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'ventas/scli/sclibusca',
		extraParams: {  'cliente': mcliente, 'origen': 'store' },
		reader: { type: 'json', totalProperty: 'results', root: 'data' }
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
		//$data['dockedItems'] = $dockedItems;
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		
		$data['title']  = heading('Cajeros');
		$this->load->view('extjs/extjsven',$data);
	}

}
?>