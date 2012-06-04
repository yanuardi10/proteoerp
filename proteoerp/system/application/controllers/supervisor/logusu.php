<?php
class Logusu extends Controller {
	var $mModulo='LOGUSU';
	var $titp='Registro de Actividad de Usuarios';
	var $tits='Registro de Actividad de Usuarios';
	var $url ='supervisor/logusu/';

	function Logusu(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_id('NNN',1);
	}

	function index(){
		/*if ( !$this->datasis->iscampo('logusu','id') ) {
			$this->db->simple_query('ALTER TABLE logusu DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE logusu ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE logusu ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
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
		window.open(\'/proteoerp/formatos/ver/LOGUSU/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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
		//$param['WestPanel']  = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados'] = $this->datasis->listados('LOGUSU', 'JQ');
		$param['otros']    = $this->datasis->otros('LOGUSU', 'JQ');
		$param['tema1']     = 'darkness';
		$param['anexos']    = 'anexos1';
		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 90,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('us_nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'"
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


		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
		));


		$grid->addField('modulo');
		$grid->label('Modulo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 20 }',
		));


		$grid->addField('comenta');
		$grid->label('Comenta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 350,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:\"2\", cols:\"60\"}'",
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 60,
			'editable'      => $editar,
			'search'        => 'false'
		));


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		//$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		//$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		//$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(false);
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
		$mWHERE = $grid->geneTopWhere('logusu');

		$response   = $grid->getData('logusu', array(array("table" => "usuario", "join" => "logusu.usuario=usuario.us_codigo", "fields" => array("us_nombre"))), array(), false, $mWHERE, 'id', 'desc' );
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
		/*
		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$this->db->insert('logusu', $data);
				echo "Registro Agregado";

				logusu('LOGUSU',"Registro ????? INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			//unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('logusu', $data);
			logusu('LOGUSU',"Registro ????? MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM logusu WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM logusu WHERE id=$id ");
				logusu('LOGUSU',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
		*/
	}
}

/*
class logusu extends Controller {
	
	function logusu(){
		parent::Controller(); 
		$this->load->library("rapyd");
		
	}
	
	function index(){
		redirect("supervisor/logusu/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro de Busqueda",'logusu');
		//$filter->db->select(array());
    
		$filter->usuario = new  dropdownField("Usuario","usuario");
		$filter->usuario->option('','Todos');
		$filter->usuario->options("Select usuario, usuario as value from logusu group by usuario");    
		$filter->usuario->style='width:150px;';
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->size=$filter->fechah->size=12;
		$filter->fechad->insertValue = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-30,   date("Y")));
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<="; 

		$filter->modulo = new inputField("M&oacute;dulo","modulo");
		$filter->modulo->size=6;
		
		$filter->referencia = new inputField("Referencia","comenta");
		$filter->referencia->size=60;
		
		$filter->buttons("reset","search");
		$filter->build();
    
		$tabla='';
		
		if($this->rapyd->uri->is_set("search") AND $filter->is_valid()){
			
			$grid = new DataGrid("Resultados");                       
			$grid->per_page = 15;
    	
			$grid->column_orderby("Usuario","usuario",'usuario' );
			$grid->column_orderby("Fecha","<b><dbdate_to_human><#fecha#></dbdate_to_human></b>",'fecha',"align='center'");
			$grid->column_orderby("Hora","<#hora#>",'hora',"align='center'");
			$grid->column_orderby("M&oacute;dulo","modulo",'modulo');
			$grid->column_orderby("Acci&oacute;n","comenta",'comenta');
    	    		
			$grid->build();
 			//echo $grid->db->last_query();
			
			$SQL=$grid->db->last_query();
			$mSQL_1=$this->db->query($SQL);
			$row = $mSQL_1->row();
			
			IF($mSQL_1->num_rows()>0){
				$tabla=$grid->output;	
			}else{
				$tabla='No existen registros';
			}
		}
		
		$data['content'] = $filter->output.$tabla;
		$data['title']   = "<h1>Log de Usuarios</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
*/
?>