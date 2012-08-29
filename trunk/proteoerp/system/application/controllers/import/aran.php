<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Aran extends Controller {
	var $mModulo='ARAN';
	var $titp='Aranceles de Aduana';
	var $tits='Aranceles de Aduana';
	var $url ='import/aran/';

	function Aran(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		if ( !$this->datasis->iscampo('aran','id') ) {
			$this->db->simple_query('ALTER TABLE aran DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE aran ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE aran ADD UNIQUE INDEX codigo (codigo)');
		}
		$this->datasis->modintramenu( 750, 500, substr($this->url,0,-1) );
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
		window.open(\''.base_url().'formatos/ver/ARAN/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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
		$param['listados']   = $this->datasis->listados('ARAN', 'JQ');
		$param['otros']      = $this->datasis->otros('ARAN', 'JQ');
		$param['temas']      = array('proteo','darkness','anexos1');
		$param['bodyscript'] = $bodyscript;
		$param['tabs']       = false;
		$param['encabeza']   = $this->titp;
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "true";

		$mSQL    = "SELECT unidades, unidades as valor FROM unidad ORDER BY unidades";
		$aunidad = $this->datasis->llenajqselect($mSQL, false );

		$grid  = new $this->jqdatagrid;

		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 15 }',
		));


		$grid->addField('descrip');
		$grid->label('Descripcion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'textarea'",
			'editoptions'   => '{rows:2, cols:40}',
		));


		$grid->addField('tarifa');
		$grid->label('Tarifa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('unidad');
		$grid->label('Unidad');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'select'",
			'editoptions'   => '{ value: '.$aunidad.',  style:"width:150px"}',
			'stype'         => "'text'",
			'editrules'     => '{ required:true}',
		));


		$grid->addField('dolar');
		$grid->label('Cambio $');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'hidden'        => "true",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 430, height:250, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});},beforeShowForm: function(frm){$(\'#proveed\').attr(\'readonly\',\'readonly\');}');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 430, height:250, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
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
		$mWHERE = $grid->geneTopWhere('aran');

		$response   = $grid->getData('aran', array(array()), array(), false, $mWHERE );
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
		$mcodp  = "codigo";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM aran WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('aran', $data);
					echo "Registro Agregado";
					logusu('ARAN',"Codigo Arancelario ".$data[$mcodp]." INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM aran WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				//$this->db->query("DELETE FROM aran WHERE $mcodp=?", array($mcodp));
				//$this->db->query("UPDATE aran SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				//$this->db->where("id", $id);
				$this->db->update("aran", $data);
				//logusu('ARAN',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Arancel no pudo incluirse, ya existe uno con ese codigo";
			} else {
				$codigo = $data[$mcodp];
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('aran', $data);
				logusu('ARAN',"Codigo Arancelario  ".$nuevo." MODIFICADO");
				echo "Arancel  Modificado";
			}

		} elseif($oper == 'del') {
			$codigo = $this->datasis->dameval("SELECT $mcodp FROM aran WHERE id=$id");
			$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM itordi WHERE codaran=$codigo");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM aran WHERE id=$id ");
				logusu('ARAN',"Codigo Arancelario ".$codigo." ELIMINADO");
				echo "Codigo Arancelario $codigo Eliminado";
			}
		};
	}
}

/*

class aran extends validaciones {

	function aran(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(204,1);
	}

	function index(){
		if ( !$this->datasis->iscampo('aran','id') ) {
			$this->db->simple_query('ALTER TABLE aran DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE aran ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE aran ADD UNIQUE INDEX codigo (codigo)');
		}
		//redirect('import/aran/filteredgrid');
		redirect('import/aran/extgrid');
	}

	function extgrid(){
		$this->datasis->modulo_id(204,1);
		$this->aranextjs();
	}


	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro','aran');

		$filter->linea = new inputField('Descripci&oacute;n','descrip');
		$filter->linea->size=20;

		$filter->unidad = new dropdownField('Unidad','unidad');
		$filter->unidad->style='width:180px;';
		$filter->unidad->option('','Seleccionar');
		$filter->unidad->options('SELECT unidades, unidades as valor FROM unidad ORDER BY unidades');

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('import/aran/dataedit/show/<raencode><#codigo#></raencode>','<#codigo#>');

		$grid = new DataGrid('Lista de Arancenles');
		$grid->order_by('codigo','asc');
		$grid->per_page = 20;

		$grid->column_orderby('C&oacute;digo'     ,$uri     ,'codigo' ,'align=\'center\'');
		$grid->column_orderby('Descripci&oacute;n','descrip','descrip','align=\'left\''  );
		$grid->column_orderby('Tarifa'            ,'<nformat><#tarifa#></nformat>' ,'tarifa' ,'align=\'right\'' );
		$grid->column_orderby('Unidad'            ,'unidad' ,'unidad' ,'align=\'right\'' );
		$grid->column_orderby('D&oacute;lar'      ,'<nformat><#dolar#></nformat>' ,'dolar' ,'align=\'right\'' );

		$grid->add('import/aran/dataedit/create','Agregar nuevo arancel');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Aranceles</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','dataedit');

		$edit = new DataEdit('Lista de aranceles','aran');
		$edit->back_url = site_url('import/aran/filteredgrid');

		//$edit->post_process('insert','_post_insert');
		//$edit->post_process('update','_post_update');
		$edit->pre_process( 'delete','_pre_del'    );
		$edit->post_process('delete','_post_delete');

		$edit->codigo =  new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->mode='autohide';
		$edit->codigo->rule ='trim|strtoupper|required';
		$edit->codigo->size = '20';
		$edit->codigo->maxlength=15;

		$edit->descrip =  new inputField('Descripci&oacute;n', 'descrip');
		$edit->descrip->rule ='trim|strtoupper|required';

		$edit->unidad = new dropdownField('Unidad','unidad');
		$edit->unidad->rule ='required';
		$edit->unidad->style='width:180px;';
		$edit->unidad->option('','Seleccionar');
		$edit->unidad->options('SELECT unidades, unidades as valor FROM unidad ORDER BY unidades');

		$edit->tarifa = new inputField('Tarifa', 'tarifa');
		$edit->tarifa->size = 10;
		$edit->tarifa->maxlength=10;
		$edit->tarifa->css_class='inputnum';
		$edit->tarifa->rule='callback_positivo|numeric|required';

		$edit->dolar = new inputField('D&oacute;lar', 'dolar');
		$edit->dolar->size = 10;
		$edit->dolar->maxlength=10;
		$edit->dolar->css_class='inputnum';
		$edit->dolar->rule='callback_positivo|numeric';

		$edit->buttons('modify','save','undo','delete','back');
		$edit->build();

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $edit->output;
		$data['title']   = '<h1>Aranceles</h1>';
		$data['head']    = script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		logusu('grup',"ARANCEL $codigo ELIMINADO");
	}

	function _pre_del($do) {
		$codigo=$this->db->escape($do->get('codigo'));
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM itordi WHERE codaran=$codigo");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='El arancel a borra contiene productos relacionados, por ello no puede ser eliminado.';
			return false;
		}
		return true;
	}

	function instalar(){
		$mSQL="CREATE TABLE `aran` (
		 `codigo` varchar(15) NOT NULL DEFAULT '',
		 `descrip` text,
		 `tarifa` decimal(8,2) DEFAULT '0.00',
		 `unidad` varchar(20) DEFAULT NULL,
		 PRIMARY KEY (`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
		$this->db->simple_query($mSQL);

		$mSQL="ALTER TABLE `aran`  ADD COLUMN `dolar` DECIMAL(8,2) NULL AFTER `unidad`";
		$this->db->simple_query($mSQL);
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : 'codigo';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = "codigo IS NOT NULL ";

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
				//Dummy Where.
				$where = " codigo IS NOT NULL ";
				$qs = "";
				for ($i=0;$i<count($filter);$i++){
					switch($filter[$i]['type']){
					case 'string' : $qs .= " AND ".$filter[$i]['field']." LIKE '%".$filter[$i]['value']."%'";
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
			}
		}

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('aran');

		if (strlen($where)>1){
			$this->db->where($where, NULL, FALSE);
		}

		$sort = json_decode($sort, true);

		if ( count($sort) == 0 ) $this->db->order_by( 'codigo', 'asc' );

		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $this->db->count_all('aran');

		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
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
			if ($this->datasis->dameval("SELECT COUNT(*) FROM aran WHERE codigo='$codigo'") == 0)
			{
				$mSQL = $this->db->insert_string("aran", $campos );
				$this->db->simple_query($mSQL);
				logusu('aran',"ARANCEL $codigo CREADO");
				echo "{ success: true, message: 'Arancel Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un Arancel con ese Codigo!!'}";
			}

		} else {
			echo "{ success: false, message: 'Ya existe un Arancel con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		unset($campos['codigo']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("aran", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('aran',"ARANCEL $codigo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Arancel Modificado -> ".$data['data']['codigo']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM itordi WHERE arancel='$codigo'");

		if ($chek > 0){
			echo "{ success: false, message: 'Arancel no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM aran WHERE codigo='$codigo'");
			logusu('aran',"ARANCEL $codigo ELIMINADO");
			echo "{ success: true, message: 'Arancel Eliminado'}";
		}
	}

//****************************************************************
//
//
//
//****************************************************************
	function aranextjs(){

		$encabeza='ARANCELES ADUANEROS';
		$listados= $this->datasis->listados('aran');
		$otros=$this->datasis->otros('aran', 'aran');

		$mSQL     = "SELECT unidades, unidades descrip FROM unidad ORDER BY unidades";
		$unidades = $this->datasis->llenacombo($mSQL);;

		$urlajax = 'import/aran/';
		$variables = "";
		$funciones = "";
		$valida = "
		{ type: 'length', field: 'codigo', min: 1 },
		{ type: 'length', field: 'descrip', min: 1 }
		";


		$columnas = "
		{ header: 'id',          width:  30, sortable: true, dataIndex: 'id' },
		{ header: 'Codigo',      width:  90, sortable: true, dataIndex: 'codigo',  field: { type: 'textfield' }, filter: { type: 'string' } },
		{ header: 'Descripcion', width: 200, sortable: true, dataIndex: 'descrip', field: { type: 'textfield' }, filter: { type: 'string' } },
		{ header: 'Unidad',      width:  90, sortable: true, dataIndex: 'unidad',  field: { type: 'textfield' }, filter: { type: 'string' } },
		{ header: 'Tarifa',      width:  90, sortable: true, dataIndex: 'tarifa',  field: { type: 'numberfield'}, filter: { type: 'number' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Cambio',      width:  90, sortable: true, dataIndex: 'dolar',   field: { type: 'numberfield'}, filter: { type: 'number' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}
	";

		$campos = "'id', 'codigo', 'descrip', 'unidad', 'tarifa', 'dolar'";

		$camposforma = "
							{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							items:[								{ xtype: 'textfield',   fieldLabel: 'Codigo',      name: 'codigo',  allowBlank: false, width: 200, id: 'codigo' },
								{ xtype: 'textfield',   fieldLabel: 'Descripcion', name: 'descrip', allowBlank: false, width: 400, },
								{ xtype: 'combo',       fieldLabel: 'Empaque',     name: 'unidad',  store: [".$unidades."], width: 180 },
								{ xtype: 'numberfield', fieldLabel: 'Tarifa ',     name: 'tarifa',  hideTrigger: true, fieldStyle: 'text-align: right', width:160,renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Cambio ',     name: 'dolar',   hideTrigger: true, fieldStyle: 'text-align: right', width:160,renderer : Ext.util.Format.numberRenderer('0,000.00') },
							]
							}
		";

		$titulow = 'Aranceles';

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
*/
?>