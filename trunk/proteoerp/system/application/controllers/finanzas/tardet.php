<?php
class Tardet extends Controller {
	var $mModulo='TARDET';
	var $titp='Detalle de Formas de Pago';
	var $tits='Detalle de Formas de Pago';
	var $url ='finanzas/tardet/';

	function Tardet(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('tardet','id') ) {
			$this->db->simple_query('ALTER TABLE tardet DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE tardet ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE tardet ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
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
		window.open(\''.base_url().'formatos/ver/TARDET/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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
		$param['listados'] = $this->datasis->listados('TARDET', 'JQ');
		$param['otros']    = $this->datasis->otros('TARDET', 'JQ');
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
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('concepto');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 3 }',
		));


		$grid->addField('descrip');
		$grid->label('Descripcion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 20 }',
		));


		$grid->addField('pdmoncja');
		$grid->label('Pdmoncja');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('pdcancja');
		$grid->label('Pdcancja');
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


		$grid->addField('pdmoncje');
		$grid->label('Pdmoncje');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('pdcancje');
		$grid->label('Pdcancje');
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


		$grid->addField('pdcoform');
		$grid->label('Pdcoform');
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

		$grid->addField('tarjeta');
		$grid->label('Tarjeta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));

		$grid->addField('inicial');
		$grid->label('Inicial');
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

		$grid->addField('saldo');
		$grid->label('Saldo');
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

		$grid->addField('enlace');
		$grid->label('Enlace');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));

		$grid->addField('fechaini');
		$grid->label('Fechaini');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
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
		$mWHERE = $grid->geneTopWhere('tardet');

		$response   = $grid->getData('tardet', array(array()), array(), false, $mWHERE );
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
				$this->db->insert('tardet', $data);
				echo "Registro Agregado";

				logusu('TARDET',"Registro ????? INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			//unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('tardet', $data);
			logusu('TARDET',"Registro ????? MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM tardet WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM tardet WHERE id=$id ");
				logusu('TARDET',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}
}

/*
class tardet extends Controller {

    function tardet() {
      parent::Controller();
      $this->load->library('rapyd');
   }
   
   function index() {
      $this->datasis->modulo_id(512,1);
    	redirect("finanzas/tardet/filteredgrid");
   }
  
   function filteredgrid() {
		
		$this->rapyd->load("datafilter","datagrid");
		$filter = new DataFilter("Filtro de Tardet", "tardet");
		
		$filter->concepto = new inputField("Concepto", "concepto");
		$filter->concepto->size=20;
		
		$filter->descripcion = new inputField("Descripci&oacute;n", "descrip");                    
    $filter->descripcion->size=35;                      
 	
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('finanzas/tardet/dataedit/show/<#concepto#>/<#tarjeta#>','<#concepto#>');

		$grid = new DataGrid("Lista de Tardet");
		$grid->per_page = 10;
		
		$grid->column("Concepto",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Tarjeta","tarjeta");
		
		$grid->add("finanzas/tardet/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Tardet</h1>";
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
		
		$edit = new DataEdit("filtro", "tardet");
		$edit->back_url = site_url("finanzas/tardet/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->concepto = new inputField("Concepto", "concepto");
		$edit->concepto->rule = "required|callback_chexiste";
		$edit->concepto->mode="autohide";
		$edit->concepto->size =5;
		$edit->concepto->maxlength=3;
		
		$edit->descripcion = new inputField("Descripci&oacute;n","descrip");
		$edit->descripcion->size =30;
		$edit->descripcion->maxlength=20;
		$edit->descripcion->rule = "required|strtoupper";
		
		$edit->tarjeta = new dropdownField("Tarjeta","tarjeta");
		$edit->tarjeta->options("SELECT tipo, nombre FROM tarjeta");
		$edit->tarjeta->style="width:180px";
		$edit->tarjeta->rule = "required";
		
		$edit->pdcoform = new inputField("Pdcoform","pdcoform");
		$edit->pdcoform->size =14;
		$edit->pdcoform->maxlength=11; 
		$edit->pdcoform->css_class='inputnum';
		$edit->pdcoform->rule='integer';
		     		
		$edit->pdmoncje = new inputField("Pdmoncje","pdmoncje");
		$edit->pdmoncje->size =14;
		$edit->pdmoncje->maxlength=11;
		$edit->pdmoncje->css_class='inputnum';
		$edit->pdmoncje->rule='integer';
			
		$edit->pdcancje = new inputField("Pdcancje","pdcancje");
		$edit->pdcancje->size =14;
		$edit->pdcancje->maxlength=11;
		$edit->pdcancje->css_class='inputnum';
		$edit->pdcancje->rule='integer';    
		
		$edit->inicial = new inputField("Inicial","inicial");
		$edit->inicial->size = 20;
		$edit->inicial->maxlength=17;
		$edit->inicial->css_class='inputnum';
		$edit->inicial->rule='numeric';
		
		$edit->saldo = new inputField("Saldo","saldo");
		$edit->saldo->size = 20;
    $edit->saldo->maxlength=17;
    $edit->saldo->css_class='inputnum';
		$edit->saldo->rule='numeric';
    
    $edit->enlace = new inputField("Enlace","enlace");
		$edit->enlace->size =7;
		$edit->enlace->maxlength=5;
		
		$edit->fechaini = new DateonlyField("Fecha","fechaini");
		$edit->fechaini->size = 12;
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Variaciones de la Forma de Pago</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('tardet',"CODIGO $codigo $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('tardet',"CODIGO $codigo $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('tardet',"CODIGO $codigo $nombre  ELIMINADO");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('concepto');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM tardet WHERE concepto='$codigo'");
		if ($check > 0){
			$tardet=$this->datasis->dameval("SELECT descrip FROM tardet WHERE concepto='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para  $tardet");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}*/
?>