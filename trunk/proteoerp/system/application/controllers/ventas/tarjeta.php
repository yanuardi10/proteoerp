<?php  require_once(BASEPATH.'application/controllers/validaciones.php');
class Tarjeta extends Controller {
	var $mModulo='TARJETA';
	var $titp='Formas de Pago';
	var $tits='Formas de Pago';
	var $url ='ventas/tarjeta/';

	function Tarjeta(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('tarjeta','id') ) {
			$this->db->simple_query('ALTER TABLE tarjeta DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE tarjeta ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE tarjeta ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
		$this->datasis->modintramenu( 700, 470, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$bodyscript = '
<script type="text/javascript">
$(function() {
	$( "input:submit, a, button", ".otros" ).button();
});

jQuery("#a1").click( function(){
	var id = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\''.base_url().'formatos/ver/TARJETA/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
<div class="anexos">

<table id="west-grid" align="center">
	<tr>
		<td><div class="tema1"><table id="listados"></table></div></td>
	</tr>
	<tr>
		<td><div class="tema1"><table id="otros"></table></div></td>
	</tr>
</table>

<table id="west-grid" align="center">
	<tr>
		<td></td>
	</tr>
</table>
</div>
'.
//		<td><a style="width:190px" href="#" id="a1">Imprimir Copia</a></td>
'</div> <!-- #LeftPane -->
';

		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->
';
		$param['WestPanel']  = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados'] = $this->datasis->listados('TARJETA', 'JQ');
		$param['otros']    = $this->datasis->otros('TARJETA', 'JQ');
		$param['temas']     = array('proteo','darkness','anexos1');
		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "true";

		$grid  = new $this->jqdatagrid;

		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:3, maxlength: 2 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 170,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 20 }',
		));

		$grid->addField('activo');
		$grid->label('Activo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'select'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{value: {"A":"Activo","I":"Inactivo" }, style:"width:100px" }'
		));

		$grid->addField('comision');
		$grid->label('Comision');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('impuesto');
		$grid->label('Impuesto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

/*
		$grid->addField('monto');
		$grid->label('Monto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('cantidad');
		$grid->label('Cantidad');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 0 }'
		));


		$grid->addField('neto');
		$grid->label('Neto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('cambio');
		$grid->label('Cambio');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));
*/

		$grid->addField('mensaje');
		$grid->label('Mensaje');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 60 }',
		));


		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('255');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:250, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:250, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(true);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdata()
	{
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('tarjeta');

		$response   = $grid->getData('tarjeta', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM tarjeta WHERE tipo=".$this->db->escape($data['tipo']));
				if ( $check == 0 ){
					$this->db->insert('tarjeta', $data);
					echo "Registro Agregado";
					logusu('TARJETA',"Forma de Pago ".$data['tipo']." INCLUIDO");
				} else
					echo "Ya existe esa Forma de Pago";

			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$tipo = $data['tipo'];
			unset($data['tipo']);
			$this->db->where('id', $id);
			$this->db->update('tarjeta', $data);
			logusu('TARJETA',"Forma de Pago ".$tipo." MODIFICADO");
			echo "Forma de Pago Modificado";

		} elseif($oper == 'del') {
			$tipo = $this->datasis->dameval("SELECT tipo FROM tarjeta WHERE id=$id");
			$check = $this->datasis->dameval("SELECT COUNT(*) FROM sfpa WHERE tipo=".$this->db->escape($tipo));
			if ($check > 0){
				echo " La Forma de Pago no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM tarjeta WHERE id=$id ");
				logusu('TARJETA',"Registro ".$tipo." ELIMINADO");
				echo "Forma de Pago Eliminado";
			}
		};
	}

/*
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
		$check = $this->datasis->dameval("SELECT count(*) FROM scli WHERE grupo='$grupo'");
		if ($check > 0){
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
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM tarjeta WHERE tipo='$codigo'");
		if ($check > 0){
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
		$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sfpa WHERE tipo='$tipo'");

		if ($check > 0){
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
 */
}

?>