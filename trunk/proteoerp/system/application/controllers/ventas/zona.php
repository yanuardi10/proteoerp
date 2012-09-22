<?php
class Zona extends Controller {
	var $mModulo='ZONA';
	var $titp='ZONAS DE VENTAS';
	var $tits='ZONAS DE VENTAS';
	var $url ='ventas/zona/';

	function Zona(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_id('NNN',1);
	}

	function index(){
		/*if ( !$this->datasis->iscampo('zona','id') ) {
		$this->db->simple_query('ALTER TABLE zona DROP PRIMARY KEY');
		$this->db->simple_query('ALTER TABLE zona ADD UNIQUE INDEX numero (numero)');
		$this->db->simple_query('ALTER TABLE zona ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
	};*/
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grid'] = $grid->deploy();

		$bodyscript = '
<script type="text/javascript">
$(function() {
	$( "input:submit, a, button", ".otros" ).button();
});

jQuery("#a1").click( function(){
	var id = jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\'/proteoerp/formatos/ver/ZONA/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
<div class="otros">

<table id="west-grid" align="center">
	<tr><td><div class="tema1">
		<table id="listados"></table>
		</div>
	</td></tr>
	<tr><td>
		<table id="otros"></table>
	</td></tr>
</table>

<table id="west-grid" align="center">
	<tr>
		<td><a style="width:190px" href="#" id="a1">Imprimir Copia</a></td>
	</tr>
</table>
</div>
</div> <!-- #LeftPane -->
';

		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->
';
		$param['WestPanel']  = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados'] = $this->datasis->listados('ZONA', 'JQ');
		$param['otros']    = $this->datasis->otros('ZONA', 'JQ');
		$param['tema1'] = 'darkness';
		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i = 1;

		$grid  = new $this->jqdatagrid;


		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'    => "'center'",
			'frozen'   => 'true',
			'width'    => 40,
			'editable' => 'false',
			'search'   => 'false'
		));


		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'align'    => "'center'",
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 60,
			'edittype'      => "'text'",
			'editoptions'   => '{ size:5, maxlength:4 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 200,
			'edittype'      => "'text'",
			'editoptions'   => '{ size:30, maxlength:30 }',
		));


		$grid->addField('descrip');
		$grid->label('Descrip.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 300,
			'edittype'      => "'text'",
			'editoptions'   => '{ size:60, maxlength:90 }',
			'formoptions'   => '{ label:"Descripcion" }'
		));

		$grid->addField('margen');
		$grid->label('Margen');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true, align:"right" }',
			'editoptions'   => '{ size:10, maxlength:10, dataInit: function(elem){ $(elem).numeric(); } }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 540, height:210, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 540, height:210, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
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
		$mWHERE = $grid->geneTopWhere('zona');

		$response   = $grid->getData('zona', array(array()), array(), false, $mWHERE );
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
		$codigo = $this->input->post('codigo');
		$data   = $_POST;
		$check  = 0;
		$mRet   = "";
		
		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$this->db->insert('zona', $data);
				logusu('ZONA',"Registro ".$data['codigo']." ".$data['nombre']." INCLUIDO");
			}
			$mRet = "Registro Agregado";

		} elseif($oper == 'edit') {
			$zonav  = $this->datasis->dameval("SELECT codigo FROM zona WHERE id=$id");
			if ( $codigo == $zonav){
				// Cuando la Zona es Igual el resto lo cambia sin problema
				unset($data['codigo']);
				$this->db->where( 'id',   $id);
				$this->db->update('zona', $data);
				logusu('ZONA',"Registro ".$data['codigo']." ".$data['nombre']." EDITADO");
				$mRet = "Registro Modificado";

			} else {
				// Busca si esta repetida
				$check = $this->datasis->dameval("SELECT COUNT(*) FROM zona WHERE codigo=".$this->db->escape($codigo));
				if ( $check == 0 ) {
					//No esta repetida modifica en scli y sfac
					$this->db->where('id', $id);
					$this->db->update('zona', $data);
					$this->db->simple_query("UPDATE scli SET zona=".$this->db->escape($codigo)." WHERE zona=".$this->db->escape($zonav));
					$this->db->simple_query("UPDATE sfac SET zona=".$this->db->escape($codigo)." WHERE zona=".$this->db->escape($zonav));
					logusu('ZONA',"Registro ".$data['codigo']." ".$data['nombre']." INCLUIDO");
					$mRet = "Zona modificada y actualizada en clientes";
				} else {
					// Aqui deberia Fusionar
					$mRet  = "No se puede cambiar la zona a una que ya existe, debe fusionarlas<br>";
				}
			}
			echo $mRet;

		} elseif($oper == 'del') {
			$codigo = $this->datasis->dameval("SELECT codigo FROM zona WHERE id=$id");
			$check  = $this->datasis->dameval("SELECT COUNT(*) FROM scli WHERE zona=".$this->db->escape($codigo)." ");
			$check += $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE zona=".$this->db->escape($codigo)." ");
			if ( $check > 0 ){
				echo " Esta Zona esta asociada a clientes y facturas; No se puede Eliminar!!! ";
			} else {
				$this->db->simple_query("DELETE FROM zona WHERE id=$id ");
				logusu('ZONA',"Registro zona=".$this->db->escape($codigo)."  ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}
}


/*
class Zona extends Controller {
	
	var $data_type = null;
	var $data = null;

	function Zona()
	{
		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->datasis->modulo_id(137,1);
		$this->load->library("rapyd");

	}

	function index()
	{
		if ( !$this->datasis->iscampo('zona','id') ) {
			$this->db->simple_query('ALTER TABLE zona DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE zona ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE zona ADD UNIQUE INDEX codigo (codigo)');
		}
		$this->datasis->modulo_id(137,1);
		$this->zonaextjs();
		//redirect("ventas/zona/filteredgrid");
	}

		function test($id,$const)
	{
		return $id*$const;
	}
		function filteredgrid()
	{
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por Zonas", 'zona');
		
		$filter->codigo = new inputField("Codigo", "codigo");
		$filter->codigo->size=15;
			
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=25;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/zona/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Zonas");
		$grid->order_by("nombre","asc");
		$grid->per_page = 7;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre","nombre");
		$grid->column("Descripci&oacute;n","descrip");

		$grid->add("ventas/zona/dataedit/create");
		$grid->build();

	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Zonas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
    }

	function dataedit()
	{ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Zona", "zona");
		$edit->back_url = site_url("ventas/zona/filteredgrid");
		
		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
    
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size=10;
		$edit->codigo->rule= "trim|required|callback_chexiste";
		$edit->codigo->maxlength=8;
		$edit->codigo->mode = "autohide";
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size=38;
		$edit->nombre->rule="trim|required|strtoupper";
		$edit->nombre->maxlength=30;
		
		$edit->descrip = new textareafield("Descripci&oacute;n", "descrip");
		$edit->descrip->cols=70;
		$edit->descrip->rows=4;
		$edit->descrip->rule="trim";
	  $edit->descrip->maxlength=90;
	  
		$edit->buttons("modify", "save", "undo", "delete", "back");
  	$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Zonas</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('zona',"ZONA $codigo NOMBRE $nombre CREADA");
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('zona',"ZONA $codigo NOMBRE $nombre MODIFICADA");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('zona',"ZONA $codigo NOMBRE $nombre ELIMINADA");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM zona WHERE codigo='$codigo'");
		if ($check > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM zona WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para la zona de $nombre");
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
		$this->db->from('zona');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('zona');

		$arr = $this->datasis->codificautf8($query->result_array());
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
			if ($this->datasis->dameval("SELECT COUNT(*) FROM zona WHERE codigo='$codigo'") == 0)
			{
				$mSQL = $this->db->insert_string("zona", $campos );
				$this->db->simple_query($mSQL);
				logusu('zona',"ZONA $codigo CREADO");
				echo "{ success: true, message: 'Zona Agregada'}";
			} else {
				echo "{ success: false, message: 'Ya existe una zona con ese Codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe una zona con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		unset($campos['codigo']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("zona", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('zona',"ZONA $codigo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Zona Modificada -> ".$data['data']['codigo']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		$check =  $this->datasis->dameval("SELECT COUNT(*) FROM scli WHERE zona='$codigo'");

		if ($check > 0){
			echo "{ success: false, message: 'Zona no puede ser Borrada'}";
		} else {
			$this->db->simple_query("DELETE FROM zona WHERE codigo='$codigo'");
			logusu('zona',"ZONA $codigo ELIMINADO");
			echo "{ success: true, message: 'Zona Eliminada'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function zonaextjs(){
		$encabeza='ZONAS';
		$listados= $this->datasis->listados('zona');
		$otros=$this->datasis->otros('zona', 'ventas/zona');

		$urlajax = 'ventas/zona/';
		$variables = "";
		$funciones = "";
		$valida = "
		{ type: 'length', field: 'codigo', min: 1 },
		{ type: 'length', field: 'nombre', min: 1 }
		";
		
		$columnas = "
		{ header: 'Codigo',      width:  50, sortable: true, dataIndex: 'codigo',  field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Nombre',      width: 180, sortable: true, dataIndex: 'nombre',  field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Descripcion', width: 400, sortable: true, dataIndex: 'descrip', field: { type: 'textfield' }, filter: { type: 'string' } }, 
	";

		$campos = "'id', 'codigo', 'nombre', 'descrip'";
		
		$camposforma = "
							{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							items: [
									{ xtype: 'textfield',     fieldLabel: 'Codigo',      name: 'codigo', allowBlank: false,  width: 120, id: 'codigo' },
									{ xtype: 'textfield',     fieldLabel: 'Nombre',      name: 'nombre', allowBlank: false,  width: 400, },
									{ xtype: 'textareafield', fieldLabel: 'Descripcion', name: 'descrip', allowBlank: false, width: 400 }
								]
							}
		";

		$titulow = 'Zonas';

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
		
		$data['title']  = heading('Zonas');
		$this->load->view('extjs/extjsven',$data);
		
	}
	
	function instala(){
		$query="ALTER TABLE `zona` ADD COLUMN `margen` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0.00'";
		$this->db->simple_query();	
	}

}
 */
?>
