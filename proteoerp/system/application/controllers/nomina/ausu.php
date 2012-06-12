<?php
class Ausu extends Controller {
	var $mModulo='AUSU';
	var $titp='Modulo AUSU';
	var $tits='Modulo AUSU';
	var $url ='nomina/ausu/';

	function Ausu(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_id('NNN',1);
	}

	function index(){
		if ( !$this->datasis->iscampo('ausu','id') ) {
			$this->db->simple_query('ALTER TABLE ausu DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE ausu ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE ausu ADD UNIQUE INDEX codigo (codigo, fecha)');
		}
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
		window.open(\''.base_url().'formatos/ver/AUSU/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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
		$param['listados'] = $this->datasis->listados('AUSU', 'JQ');
		$param['otros']    = $this->datasis->otros('AUSU', 'JQ');
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

		$link = site_url('ajax/buscapers');
		$despues =
'				$("input#nombre").val(ui.item.nombre);
				$("input#sueldoa").val(ui.item.sueldo);';

		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true }',
			'editoptions'   => '{'.$grid->autocomplete($link, 'codigo','aaaaaa','<div id=\"aaaaaa\"></div>',$despues).'}',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30, readonly: true }',
		));


		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('sueldoa');
		$grid->label('Sueldo Ant.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, readonly:true, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('sueldo');
		$grid->label('Nuevo Sueldo');
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


		$grid->addField('observ1');
		$grid->label('Observaciones');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 46 }',
		));


		$grid->addField('oberv2');
		$grid->label('Obervaciones');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 46 }',
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
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
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
		$mWHERE = $grid->geneTopWhere('ausu');

		$response   = $grid->getData('ausu', array(array()), array(), false, $mWHERE );
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

		if ($oper <> 'del'){
			$codigo  = $this->input->post('codigo');
			$check = $this->datasis->dameval('SELECT count(*) FROM pers WHERE codigo='.$this->db->escape($codigo));
			if ( $check == 0 ){
				echo "No se encontro esa persona en los registros ";
				return;
			}
			$sueldo  = $this->input->post('sueldo');
			if ( $sueldo <= 0  ) {
				echo "El sueldo debe ser mayor que 0";
				return;
			}
		}

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$this->db->insert('ausu', $data);
				echo "Registro Agregado";
				logusu('AUSU',"Registro ".$codigo." INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$this->db->where('id', $id);
			$this->db->update('ausu', $data);
			logusu('AUSU',"Registro ".$codigo." MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			$codigo =  $this->datasis->dameval("SELECT codigo FROM ausu WHERE id='$id' ");
			$this->db->simple_query("DELETE FROM ausu WHERE id=$id ");
			logusu('AUSU',"Registro ".$codigo." ELIMINADO");
			echo "Registro Eliminado";

		};
	}
}

/*

class ausu extends Controller {
	
	function ausu(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		if ( !$this->datasis->iscampo('ausu','id') ) {
			$this->db->simple_query('ALTER TABLE ausu DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE ausu ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE ausu ADD UNIQUE INDEX codigo (codigo, fecha)');
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

		$where = $this->datasis->extjsfiltro($filters);

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('ausu');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();

		$results = $this->db->count_all('ausu');
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
		$codigo = $campos['codigo'];
		$fecha  = $campos['fecha'];

		$campos['nombre']  = $this->datasis->dameval("SELECT CONCAT(TRIM(nombre),' ',TRIM(apellido)) nombre FROM pers WHERE codigo='$codigo'");

		unset($campos['codigo']);
		unset($campos['fecha']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("ausu", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('ausu',"DESCUENTO DE PRESTAMOS POR NOMINA ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Prestamo de nomina Modificado '}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos= $data['data'];

		// VERIFICAR SI PUEDE
		$chek =  0; //$this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE depto='$departa'");

		if ($chek > 0){
			echo "{ success: false, message: 'Prestamo de nomina, no puede ser Borrado'}";
		} else {
			$this->db->simple_query("UPDATE FROM ausu SET tipo_doc='X' WHERE departa=".$data['data']['id']."");
			logusu('ausu',"PRESTAMO POR NOMINA ".$data['data']['id']." ELIMINADO");
			echo "{ success: true, message: 'Prestamo de nomina Eliminado'}";
		}
	}



//****************************************************************
//
//
//
//****************************************************************
	function ausuextjs(){
		$encabeza='AUMENTO DE SUELDOS';
		$listados= $this->datasis->listados('ausu');
		$otros=$this->datasis->otros('ausu', 'ausu');

		$urlajax = 'nomina/ausu/';
		$variables = "var mcodigo = '';";

		$funciones = "";
		
		$valida = "
		{ type: 'length', field: 'codigo', min:  1 }
		//{ type: 'length', field: 'nombre', min:  1 }
		";

		$columnas = "
		{ header: 'Codigo',      width:  60, sortable: true, dataIndex: 'codigo',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Nombre',      width: 220, sortable: true, dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fecha',       width:  70, sortable: true, dataIndex: 'fecha',    field: { type: 'date'      }, filter: { type: 'date'   }},
		{ header: 'Anterior',    width: 120, sortable: true, dataIndex: 'sueldoa',  field: { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Nuevo',       width: 120, sortable: true, dataIndex: 'sueldo',   field: { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Observacion', width: 220, sortable: true, dataIndex: 'observ1',  field: { type: 'textfield' }, filter: { type: 'string' }},
	";

		$campos = "'id', 'codigo', 'nombre', 'fecha','sueldoa', 'sueldo', 'observ1'";
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
								width: 400,
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
									//alert('pers '+msueldo);
									Ext.getCmp('sueldoa').setValue(msueldo);
									Ext.getCmp('sueldo').setValue(msueldo);
									
									
								}}
							},
							{ xtype: 'numberfield', fieldLabel: 'Sueldo',     name: 'sueldoa',  hideTrigger: true, fieldStyle: 'text-align: right', columnWidth:0.45, renderer : Ext.util.Format.numberRenderer('0,000.00'), id: 'sueldoa', readOnly: true }
						]
				},{
						layout: 'column',
						frame: false,
						border: false,
						labelAlign: 'right',
						defaults: {xtype:'fieldset', labelWidth: 80  },
						style:'padding:4px',
						items: [
							{ xtype: 'datefield',   fieldLabel: 'Fecha',       name: 'fecha',    width:200, format: 'd/m/Y', submitFormat: 'Y-m-d' },
							{ xtype: 'numberfield', fieldLabel: 'Sueldo',      name: 'sueldo',   width:200, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), id: 'sueldo' },
							{ xtype: 'textfield',   fieldLabel: 'Observacion', name: 'observ1',  width:400, allowBlank: true   }
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
 */
?>