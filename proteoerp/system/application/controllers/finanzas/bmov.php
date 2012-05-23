<?php
class Bmov extends Controller {
	var $mModulo='BMOV';
	var $titp='Modulo BMOV';
	var $tits='Modulo BMOV';
	var $url ='finanzas/bmov/';

	function Bmov(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_id('NNN',1);
	}

	function index(){
		if ( !$this->datasis->iscampo('bmov','id') ) {
		$this->db->simple_query('ALTER TABLE bmov DROP PRIMARY KEY');
		$this->db->simple_query('ALTER TABLE bmov ADD UNIQUE INDEX idunico (codbanc, tipo_op, numero)');
		$this->db->simple_query('ALTER TABLE bmov ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
	};
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
		window.open(\'/proteoerp/formatos/ver/BMOV/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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
	<tr><td>
		<td><div class="tema1"><table id="otros"></table></div></td>
	</td></tr>
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
		$param['listados'] = $this->datasis->listados('BMOV', 'JQ');
		$param['otros']    = $this->datasis->otros('BMOV', 'JQ');
		$param['tema1'] = 'darkness';
		$param['anexos']       = 'anexos1';
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

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'    => "'center'",
			'frozen'   => 'true',
			'width'    => 60,
			'editable' => 'false',
			'search'   => 'false'
		));

		$grid->addField('codbanc');
		$grid->label('Codbanc');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));

/*
		$grid->addField('moneda');
		$grid->label('Moneda');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));
*/

		$grid->addField('numcuent');
		$grid->label('Numcuent');
		$grid->params(array(
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 150,
			'edittype'   => "'text'",
		));


		$grid->addField('banco');
		$grid->label('Banco');
		$grid->params(array(
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 120,
			'edittype'   => "'text'",
		));

/*
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
*/

		$grid->addField('tipo_op');
		$grid->label('Tipo');
		$grid->params(array(
			'align'      => "'center'",
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 40,
			'edittype'   => "'text'",
		));


		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 90,
			'edittype'   => "'text'",
		));


		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'      => 'true',
			'editable'    => $editar,
			'width'       => 80,
			'align'       => "'center'",
			'edittype'    => "'text'",
			'editrules'   => '{ required:true,date:true}',
			'formoptions' => '{ label:"Fecha" }'
		));


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


		$grid->addField('benefi');
		$grid->label('Beneficiario');
		$grid->params(array(
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 200,
			'edittype'   => "'text'",
		));


		$grid->addField('concepto');
		$grid->label('Concepto');
		$grid->params(array(
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 200,
			'edittype'   => "'text'",
		));


		$grid->addField('concep2');
		$grid->label('Concep2');
		$grid->params(array(
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 200,
			'edittype'   => "'text'",
		));


		$grid->addField('concep3');
		$grid->label('Concep3');
		$grid->params(array(
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 200,
			'edittype'   => "'text'",
		));



		$grid->addField('clipro');
		$grid->label('Clipro');
		$grid->params(array(
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 40,
			'edittype'   => "'text'",
		));


		$grid->addField('codcp');
		$grid->label('Codcp');
		$grid->params(array(
			'search'     => 'true',
			'editable'   => $editar,
			'width'      => 50,
			'edittype'   => "'text'",
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
		));


/*
		$grid->addField('documen');
		$grid->label('Documento');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
		));
*/

		$grid->addField('comprob');
		$grid->label('Comprob');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
		));


		$grid->addField('status');
		$grid->label('Status');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));

/*
		$grid->addField('cuenta');
		$grid->label('Cuenta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
		));


		$grid->addField('enlace');
		$grid->label('Enlace');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
		));
*/

		$grid->addField('bruto');
		$grid->label('Bruto');
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


		$grid->addField('comision');
		$grid->label('Comision');
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


		$grid->addField('impuesto');
		$grid->label('Impuesto');
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


		$grid->addField('registro');
		$grid->label('Registro');
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


		$grid->addField('concilia');
		$grid->label('Concilia');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

/*
		$grid->addField('posdata');
		$grid->label('Posdata');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));
*/

		$grid->addField('abanco');
		$grid->label('Abanco');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));


		$grid->addField('liable');
		$grid->label('Liable');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));

		$grid->addField('anulado');
		$grid->label('Nulo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));


		$grid->addField('negreso');
		$grid->label('Negreso');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
		));

/*
		$grid->addField('susti');
		$grid->label('Susti');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 160,
			'edittype'      => "'text'",
		));
*/

		$grid->addField('ndebito');
		$grid->label('Ndebito');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
		));


		$grid->addField('ncausado');
		$grid->label('Ncausado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
		));


		$grid->addField('ncredito');
		$grid->label('Ncredito');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
		));


		$grid->addField('ncredito');
		$grid->label('Ncredito');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
		));


		$grid->addField('transac');
		$grid->label('Transac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
		));

		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));


		$grid->addField('estampa');
		$grid->label('Estampa');
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
			'width'         => 80,
			'edittype'      => "'text'",
		));

		$grid->addField('modificado');
		$grid->label('Modificado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
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
		$mWHERE = $grid->geneTopWhere('bmov');

		$response   = $grid->getData('bmov', array(array()), array(), false, $mWHERE, 'id', 'desc' );
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
				$this->db->insert('bmov', $data);
			}
			return "Registro Agregado";

		} elseif($oper == 'edit') {
			//unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('bmov', $data);
			return "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM bmov WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM bmov WHERE id=$id ");
				logusu('bmov',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}
}
/*
class Bmov extends Controller {
	function bmov(){
		parent::Controller(); 
		$this->load->library('rapyd');
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de movimientos de bancos');
		$select=array('fecha','numero','fecha','nombre','monto',"CONCAT_WS(' ',banco ,numcuent) AS banco",'tipo_op','codbanc','concepto','anulado');
		$filter->db->select($select);
		$filter->db->from('bmov');

		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->size     = 10;
		$filter->fecha->operator = '=';
		$filter->fecha->clause   = 'where';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=20;

		$filter->nombre = new inputField('Nombre', 'nombre');
		$filter->nombre->size=40;

		$filter->banco = new dropdownField('Banco', 'codbanc');
		$filter->banco->option('','Todos');
		$filter->banco->options("SELECT codbanc,banco FROM banc where tbanco<>'CAJ' ORDER BY codbanc");

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/bmov/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');
		$grid = new DataGrid('Lista');
		$grid->order_by('fecha','desc');
		$grid->per_page = 15;

		$grid->column_orderby('N&uacute;mero',$uri ,'numero');
		$grid->column_orderby('Fecha'        ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha');
		$grid->column_orderby('Nombre'       ,'nombre','nombre');
		$grid->column_orderby('Banco'        ,'banco','banco');
		$grid->column_orderby('Monto'        ,'<nformat><#monto#></nformat>' ,'monto','align=\'right\'');
		$grid->column_orderby('Concepto'     ,'concepto','concepto');
		$grid->column_orderby('Anulado'      ,'anulado','anulado','align=center');

		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Movimientos de bancos');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');
		$this->rapyd->uri->keep_persistence();

		$edit = new DataEdit('Movimiento', 'bmov');
		$edit->back_url = 'finanzas/bmov/index';
		$status=$edit->_status;
		if($status!='show') show_error('Error de par&aacute;metros');

		$edit->codbanc = new inputField('C&oacute;digo del Banco','codbanc');
		$edit->codbanc->rule='max_length[2]';
		$edit->codbanc->size =4;
		$edit->codbanc->maxlength =2;

		$edit->moneda = new inputField('Moneda','moneda');
		$edit->moneda->rule='max_length[2]';
		$edit->moneda->size =4;
		$edit->moneda->maxlength =2;

		$edit->numcuent = new inputField('N&uacute;mero de cuenta','numcuent');
		$edit->numcuent->rule='max_length[18]';
		$edit->numcuent->size =20;
		$edit->numcuent->maxlength =18;

		$edit->banco = new inputField('Banco','banco');
		$edit->banco->rule='max_length[30]';
		$edit->banco->size =32;
		$edit->banco->maxlength =30;

		$edit->saldo = new inputField('Saldo','saldo');
		$edit->saldo->rule='max_length[17]|numeric';
		$edit->saldo->css_class='inputnum';
		$edit->saldo->size =19;
		$edit->saldo->maxlength =17;

		$edit->tipo_op = new dropdownField('Tipo de operaci&oacute;n', 'tipo_op');
		$edit->tipo_op->option('NC','Nota de cr&eacute;dito');
		$edit->tipo_op->option('ND','Nota de d&eacute;bito');
		$edit->tipo_op->option('CH','Cheque');
		$edit->tipo_op->option('DE','Deposito');
		$edit->tipo_op->mode='autohide';
		$edit->tipo_op->rule='max_length[2]';

		$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->rule='max_length[12]';
		$edit->numero->size =14;
		$edit->numero->maxlength =12;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->clipro = new inputField('Clte/Prv','clipro');
		$edit->clipro->rule='max_length[1]';
		$edit->clipro->size =3;
		$edit->clipro->maxlength =1;

		$edit->codcp = new inputField('codcp','codcp');
		$edit->codcp->rule='max_length[5]';
		$edit->codcp->size =7;
		$edit->codcp->maxlength =5;
		$edit->codcp->in='clipro';

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[30]';
		$edit->nombre->size =32;
		$edit->nombre->maxlength =30;
		$edit->nombre->in='clipro';

		$edit->monto = new inputField('Monto','monto');
		$edit->monto->rule='max_length[17]|numeric';
		$edit->monto->css_class='inputnum';
		$edit->monto->size =19;
		$edit->monto->maxlength =17;

		$edit->concepto = new inputField('Concepto','concepto');
		$edit->concepto->rule='max_length[50]';
		$edit->concepto->size =52;
		$edit->concepto->maxlength =50;
		$edit->concepto->group='Conceptos';

		$edit->concep2 = new inputField('...','concep2');
		$edit->concep2->rule='max_length[50]';
		$edit->concep2->size =52;
		$edit->concep2->maxlength =50;
		$edit->concep2->group='Conceptos';

		$edit->concep3 = new inputField('...','concep3');
		$edit->concep3->rule='max_length[50]';
		$edit->concep3->size =52;
		$edit->concep3->maxlength =50;
		$edit->concep3->group='Conceptos';

		$edit->documen = new inputField('Documento','documen');
		$edit->documen->rule='max_length[8]';
		$edit->documen->size =10;
		$edit->documen->maxlength =8;

		$edit->comprob = new inputField('Comprobante','comprob');
		$edit->comprob->rule='max_length[6]';
		$edit->comprob->size =8;
		$edit->comprob->maxlength =6;

		$edit->status = new inputField('Estatus','status');
		$edit->status->rule='max_length[1]';
		$edit->status->size =3;
		$edit->status->maxlength =1;

		$edit->cuenta = new inputField('Cuenta','cuenta');
		$edit->cuenta->rule='max_length[15]';
		$edit->cuenta->size =17;
		$edit->cuenta->maxlength =15;

		$edit->enlace = new inputField('Enlace','enlace');
		$edit->enlace->rule='max_length[15]';
		$edit->enlace->size =17;
		$edit->enlace->maxlength =15;

		$edit->bruto = new inputField('bruto','bruto');
		$edit->bruto->rule='max_length[17]|numeric';
		$edit->bruto->css_class='inputnum';
		$edit->bruto->size =19;
		$edit->bruto->maxlength =17;

		$edit->comision = new inputField('Comisi&oacute;n','comision');
		$edit->comision->rule='max_length[17]|numeric';
		$edit->comision->css_class='inputnum';
		$edit->comision->size =19;
		$edit->comision->maxlength =17;

		$edit->impuesto = new inputField('Impuesto','impuesto');
		$edit->impuesto->rule='max_length[17]|numeric';
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->size =19;
		$edit->impuesto->maxlength =17;

		$edit->registro = new inputField('Registro','registro');
		$edit->registro->rule='max_length[10]';
		$edit->registro->size =12;
		$edit->registro->maxlength =10;

		$edit->concilia = new dateField('Conciliado','concilia');
		$edit->concilia->rule='chfecha';
		$edit->concilia->size =10;
		$edit->concilia->maxlength =8;

		$edit->benefi = new inputField('Beneficiario','benefi');
		$edit->benefi->rule='max_length[40]';
		$edit->benefi->size =42;
		$edit->benefi->maxlength =40;

		$edit->posdata = new dateField('Posdata','posdata');
		$edit->posdata->rule='chfecha';
		$edit->posdata->size =10;
		$edit->posdata->maxlength =8;

		$edit->abanco = new inputField('abanco','abanco');
		$edit->abanco->rule='max_length[1]';
		$edit->abanco->size =3;
		$edit->abanco->maxlength =1;

		$edit->liable = new dropdownField('Liable','liable');
		$edit->liable->option('S','S&iacute;');
		$edit->liable->option('N','No;');
		$edit->liable->rule='max_length[1]';

		$edit->tipo_op->option('ND','Nota de d&eacute;bito');
		$edit->transac = new inputField('Transacci&oacute;n','transac');
		$edit->transac->rule='max_length[8]';
		$edit->transac->size =10;
		$edit->transac->maxlength =8;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));

		$edit->hora    = new autoUpdateField('hora',date('H:m:s'), date('H:m:s'));

		$edit->anulado = new inputField('Anulado','anulado');
		$edit->anulado->rule='max_length[1]';
		$edit->anulado->size =3;
		$edit->anulado->maxlength =1;

		$edit->negreso = new inputField('N&uacute;mmero de egreso','negreso');
		$edit->negreso->rule='max_length[8]';
		$edit->negreso->size =10;
		$edit->negreso->maxlength =8;

		$edit->ndebito = new inputField('N&uacute;mmero d&eacute;bito','ndebito');
		$edit->ndebito->rule='max_length[8]';
		$edit->ndebito->size =10;
		$edit->ndebito->maxlength =8;

		$edit->ncausado = new inputField('N&uacute;mmero causado','ncausado');
		$edit->ncausado->rule='max_length[8]';
		$edit->ncausado->size =10;
		$edit->ncausado->maxlength =8;

		$edit->ncredito = new inputField('N&uacute;mmero cr&eacute;dito','ncredito');
		$edit->ncredito->rule='max_length[8]';
		$edit->ncredito->size =10;
		$edit->ncredito->maxlength =8;

		$edit->buttons('undo', 'back');
		$edit->build();
		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Movimientos de bancos');
		$this->load->view('view_ventanas', $data);
	}
}
*/