<?php  require_once(BASEPATH.'application/controllers/validaciones.php');
//vende
class Vend extends Controller {
	var $mModulo='VEND';
	var $titp='Vendedores';
	var $tits='Vendedores';
	var $url ='ventas/vend/';

	function Vend(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('vend','id') ) {
			$this->db->simple_query('ALTER TABLE vend DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE vend ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE vend ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
		$this->datasis->modintramenu( 800, 505, substr($this->url,0,-1) );
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
		window.open(\''.base_url().'formatos/ver/VEND/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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
		$param['listados'] = $this->datasis->listados('VEND', 'JQ');
		$param['otros']    = $this->datasis->otros('VEND', 'JQ');
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

		$grid->addField('vendedor');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));

/*
		$grid->addField('clave');
		$grid->label('Clave');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));
*/

		$grid->addField('nombre');
		$grid->label('Vendedor');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 180,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));

		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'select'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{value: {"V":"Vendedor","C":"Cobrador","A":"Ambos","I":"Inactivo" }, style:"width:100px" }'
		));

/*
		$grid->addField('direc1');
		$grid->label('Direc1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 35 }',
		));


		$grid->addField('direc2');
		$grid->label('Direc2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 35 }',
		));
*/

		$grid->addField('telefono');
		$grid->label('Telefono');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 130,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:15, maxlength: 13 }',
		));


		$grid->addField('comive');
		$grid->label('Com/Venta%');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ label:"Comision Venta %" }'
		));


		$grid->addField('comicob');
		$grid->label('Cobranza%');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ label:"Comision Cobro %" }'
		));


		$grid->addField('recargo');
		$grid->label('Recargo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ label:"Recargo %" }'
		));


		$mSQL = "SELECT ubica, CONCAT(ubica, ' ', ubides) ubides FROM caub ORDER BY ubica ";
		$link = $this->datasis->llenajqselect($mSQL, true);

		$grid->addField('almacen');
		$grid->label('Almacen');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'select'",
			'editoptions'   => '{ value: '.$link.', style:"width:250px;" }',
		));

/*
		$grid->addField('pmargen');
		$grid->label('Pmargen');
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

		$grid->setFormOptionsE('
			closeAfterEdit:true, mtype: "POST", width: 420, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true,
			afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},
			beforeShowForm: function(frm){ $(\'#vendedor\').attr(\'readonly\',\'readonly\');},
			afterShowForm: function(frm){$("select").selectmenu({style:"popup"});}
		');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 420, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
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
		$mWHERE = $grid->geneTopWhere('vend');

		$response   = $grid->getData('vend', array(array()), array(), false, $mWHERE );
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
		$mcodp  = "vendedor";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM vend WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('vend', $data);
					echo "Registro Agregado";

					logusu('VEND',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM vend WHERE id=$id");
			/*
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM vend WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE vend SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("vend", $data);
				logusu('VEND',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
			*/
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('vend', $data);
				logusu('VEND',"Vendedor  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			//}

		} elseif($oper == 'del') {
			$vendedor = $this->db->escape($this->datasis->dameval("SELECT $mcodp FROM vend WHERE id=$id"));
			$check    = $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE vd=".$vendedor);
			$check   += $this->datasis->dameval("SELECT COUNT(*) FROM scli WHERE vendedor=".$vendedor." OR cobrador=".$vendedor);
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM vend WHERE id=$id ");
				logusu('VEND',"Vendedor ".$vendedor." ELIMINADO");
				echo "Vendedor Eliminado";
			}
		};
	}


/*
class Vend extends validaciones {

	var $data_type = null;
	var $data = null;

	function Vend(){
		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->load->library("rapyd");
	}
	function index(){
		if ( !$this->datasis->iscampo('vend','id') ) {
			$this->db->simple_query('ALTER TABLE vend DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE vend ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE vend ADD UNIQUE INDEX vendedor (vendedor)');
		}
		//$this->datasis->modulo_id(206,1);
		$this->vendextjs();
		//redirect("ventas/vend/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de  Vendedores Y Cobradores", 'vend');
		
		$filter->vendedor = new inputField("Codigo","vendedor");
		$filter->vendedor->size=5;
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=25;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/vend/dataedit/show/<#vendedor#>','<#vendedor#>');

		$grid = new DataGrid("Lista de Vendedores y Cobradores");
		$grid->order_by("nombre","asc");
		$grid->per_page = 10;

		$grid->column_orderby('C&oacute;digo',$uri,'vendedor');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Tipo','tipo','tipo');
		$grid->column_orderby('Direcci&oacute;n','direc1','direc1');
		$grid->column_orderby('Tel&eacute;fono','telefono','telefono');

		$grid->add('ventas/vend/dataedit/create','Agregar Vendedor o Cobrador');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Vendedores y Cobradores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';

		$edit = new DataEdit('Vendedores y Cobradores', 'vend');
		$edit->back_url = site_url("ventas/vend/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process("update",'_post_update');
		$edit->post_process("delete",'_post_delete');
		
		$edit->vendedor = new inputField('C&oacute;digo', 'vendedor');
		$edit->vendedor->size=5;
		$edit->vendedor->maxlength=5;
		$edit->vendedor->rule = 'trim|required|callback_chexiste';
		$edit->vendedor->mode ='autohide';
		
		$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->option('','Seleccionar');
		$edit->tipo->options(array('V'=> 'Vendedor','C'=>'Cobrador', 'A'=>'Vendedor y Cobrador','I'=>'Inactivo'));
		$edit->tipo->style='width:180px';
		$edit->tipo->rule ='required';
		
		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rule = 'trim|strtoupper|required';
		$edit->nombre->size=35;
		$edit->nombre->maxlength=30;

		$edit->direc1 = new inputField('Direcci&oacute;n', 'direc1');
		$edit->direc1->size=40;
		$edit->direc1->rule='trim';
		$edit->direc1->maxlength=35;

		$edit->direc2 = new inputField("&nbsp;&nbsp;Continuaci&oacute;n", "direc2");
		$edit->direc2->size=40;
		$edit->direc2->rule='trim';
		$edit->direc2->maxlength=35;
		
		$edit->telefono = new inputField('Tel&eacute;fono', 'telefono');
		$edit->telefono->size=16;
		$edit->telefono->maxlength=13;
		$edit->telefono->rule = 'trim|required';

		$edit->almacen = new dropdownField('Almac&eacute;n', 'almacen');
		$edit->almacen->option('','Seleccionar');
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");
		$edit->almacen->style='width:150px';
		
		$edit->clave = new inputField('Clave','clave');
		$edit->clave->size=7;
		$edit->clave->rule='trim';
		$edit->clave->maxlength=5;
		//$edit->clave->type='password';
		$edit->clave->when =array('create','modify');
		
		$edit->comive  = new inputField("% por ventas ", "comive");
		$edit->comive->size=7;
		$edit->comive->maxlength=5;
		$edit->comive->css_class='inputnum';
		$edit->comive->rule='trim|numeric';
		$edit->comive->group='Comisiones';
		
		$edit->comicob = new inputField('% por cobranzas', 'comicob');
		$edit->comicob->size=7;
		$edit->comicob->maxlength=5;
		$edit->comicob->css_class='inputnum';
		$edit->comicob->rule='trim|numeric';
		$edit->comicob->group='Comisiones';
		
		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();
		
		$data['content'] = $edit->output;
		$data['title']   = '<h1>Vendedores y Cobradores</h1>';
		$data['head']    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}

	function _pre_del($do) {
		$codigo=$do->get('vendedor');
		$chek = $this->datasis->dameval("SELECT count(*) FROM sfac WHERE vd='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Vendedor relacionado con una o mas facturas no puede ser eliminado';
			return False;
		}else	{
			return True;
		}
	}
	function _post_insert($do){
		$codigo=$do->get('vendedor');
		$tipo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('vend',"CODIGO $codigo NOMBRE $nombre TIPO $tipo CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('vendedor');
		$tipo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('vend',"CODIGO $codigo NOMBRE $nombre TIPO $tipo MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('vendedor');
		$nombre=$do->get('nombre');
		$tipo=$do->get('tipo');
		logusu('vend',"CODIGO $codigo NOMBRE $nombre TIPO $tipo ELIMINADO");
		
	}
	function chexiste($codigo){
		$codigo=$this->input->post('vendedor');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM vend WHERE vendedor='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM vend WHERE vendedor='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el vendedor $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"vendedor","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('vend');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('vend');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$vendedor = $campos['vendedor'];

		if ( !empty($vendedor) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM vend WHERE vendedor='$vendedor'") == 0)
			{
				$mSQL = $this->db->insert_string("vend", $campos );
				$this->db->simple_query($mSQL);
				logusu('vend',"VENDEDOR $vendedor CREADO");
				echo "{ success: true, message: 'Vendedor Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un vendedor con ese Codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe un vendedor con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$vendedor = $campos['vendedor'];
		unset($campos['vendedor']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("vend", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('vend',"VENDEDOR $vendedor ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Vendedor Modificado -> ".$data['data']['vendedor']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$vendedor = $campos['vendedor'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM scli WHERE vendedor='$vendedor' OR cobrador='$vendedor'");

		if ($chek > 0){
			echo "{ success: false, message: 'Vendedor no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM vend WHERE vendedor='$vendedor'");
			logusu('vend',"VENDEDOR $vendedor ELIMINADO");
			echo "{ success: true, message: 'Vendedor Eliminado'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function vendextjs(){
		$encabeza='VENDEDORES';
		$listados= $this->datasis->listados('vend');
		$otros=$this->datasis->otros('vend', 'vend');

		$mSQL = "SELECT ubica, CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ORDER BY ubica";
		$alma = $this->datasis->llenacombo($mSQL);

		$urlajax = 'ventas/vend/';
		$variables = "";

		$funciones = "
function tipos(val){
	if ( val == 'V'){
		return 'Vendedor';
	} else if ( val == 'C'){
		return  'Cobrador';
	} else if ( val == 'A'){
		return  'Vende/Cobra';
	}
}
";

		$valida = "
		{ type: 'length', field: 'vendedor', min: 1 },
		{ type: 'length', field: 'nombre',   min: 1 }
		";
		
		$columnas = "
		{ header: 'Codigo',      width:  50, sortable: true, dataIndex: 'vendedor', field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Nombre',      width: 180, sortable: true, dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Tipo',        width:  90, sortable: true, dataIndex: 'tipo',     field: { type: 'textfield' }, filter: { type: 'string' }, renderer: tipos },
		{ header: 'Telefono',    width: 160, sortable: true, dataIndex: 'telefono', field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Alamcen',     width:  80, sortable: true, dataIndex: 'almacen',  field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Direccion',   width: 300, sortable: true, dataIndex: 'direc1',   field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Com. Ventas', width:  90, sortable: true, dataIndex: 'comive',   field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Com. Cobros', width:  90, sortable: true, dataIndex: 'comicob',  field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') }
	";

		$campos = "'id', 'vendedor', 'clave', 'nombre', 'direc1', 'direc2', 'telefono', 'comive', 'comicob', 'recargo', 'tipo', 'almacen'";
		
		$camposforma = "
							{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield',  fieldLabel: 'Codigo',    name: 'vendedor', allowBlank: false,  width: 120, id: 'codigo' },
									{ xtype: 'combo',      fieldLabel: 'Tipo',      name: 'tipo',   store: [['V','Vendedor'],['C','Cobrador'],['A','Vende/Cobra']], width: 280, labelWidth:170 },
									{ xtype: 'textfield',  fieldLabel: 'Nombre',    name: 'nombre',   allowBlank: false,  width: 400, },
									{ xtype: 'textfield',  fieldLabel: 'Direccion', name: 'direc1',   allowBlank: true,  width: 400, },
									{ xtype: 'textfield',  fieldLabel: '.',         name: 'direc2',   allowBlank: true,  width: 400, },
									{ xtype: 'textfield',  fieldLabel: 'Telefono',  name: 'telefono', allowBlank: true,  width: 180, },
									{ xtype: 'combo',      fieldLabel: 'Almacen',   name: 'almacen',  store: [".$alma."], width : 220 },
								]
							},{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'numberfield', fieldLabel: 'Comision Ventas', name: 'comive',  width:180, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), labelWidth:100 },
									{ xtype: 'numberfield', fieldLabel: 'Comision Cobros', name: 'comicob', width:200, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), labelWidth:110 },
									{ xtype: 'numberfield', fieldLabel: 'Recargo',         name: 'recargo', width:180, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), labelWidth:100 },
								]
							},{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield', fieldLabel: 'Clave', name: 'clave', allowBlank: true, width: 250, inputType: 'password' },
							]
							}
		";

		$titulow = 'Vendedores';

		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 350,
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
		
		$data['title']  = heading('Vendedores');
		$this->load->view('extjs/extjsven',$data);
		
	}
 */
}

?>