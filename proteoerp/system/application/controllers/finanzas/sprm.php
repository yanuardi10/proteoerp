<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Sprm extends Controller {
	var $mModulo='SPRM';
	var $titp='Movimiento de Proveedor';
	var $tits='Movimiento de Proveedor';
	var $url ='finanzas/sprm/';

	function Sprm(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_id(500,1);
	}

	function index(){
		if ( !$this->datasis->iscampo('sprm','id') ) {
		$this->db->simple_query('ALTER TABLE sprm DROP PRIMARY KEY');
		$this->db->simple_query('ALTER TABLE sprm ADD UNIQUE INDEX numpri (cod_prv, tipo_doc, numero)');
		$this->db->simple_query('ALTER TABLE sprm ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
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

		$readyLayout = '
	$(\'body\').layout({
		minSize: 30,
		north__size: 60,
		resizerClass: \'ui-state-default\',
		west__size: 212,
		west__onresize: function (pane, $Pane){jQuery("#west-grid").jqGrid(\'setGridWidth\',$Pane.innerWidth()-2);},
	});
	
	$(\'div.ui-layout-center\').layout({
		minSize: 30,
		resizerClass: "ui-state-default",
		center__paneSelector: ".centro-centro",
		south__paneSelector:  ".centro-sur",
		south__size: 140,
		center__onresize: function (pane, $Pane) {
			jQuery("#newapi'.$param['grid']['gridname'].'").jqGrid(\'setGridWidth\',$Pane.innerWidth()-6);
			jQuery("#newapi'.$param['grid']['gridname'].'").jqGrid(\'setGridHeight\',$Pane.innerHeight()-110);
		}
	});
	';

		$centerpanel = '
<div id="RightPane" class="ui-layout-center">
	<div class="centro-centro">
		<table id="newapi'.$param['grid']['gridname'].'"></table>
		<div id="pnewapi'.$param['grid']['gridname'].'"></div>
	</div>
	<div class="centro-sur" id="adicional" style="overflow:auto;">
	</div>
</div> <!-- #RightPane -->
';


		$bodyscript = '
<script type="text/javascript">
$(function() {
	$( "input:submit, a, button", ".boton1" ).button();
});

jQuery("#boton1").click( function(){
	var id = jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\'/proteoerp/formatos/ver/SPRM/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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
	<tr><td><div class="tema1"><table id="listados"></table></div></td></tr>
	<tr><td><div class="tema1"><table id="otros"></table></div></td></td>
	<tr><td><div class="boton1"><a style="width:190px" href="#" id="boton1">Reimprimir Documento</a></div></td></tr>
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

		$funciones = '';

		$param['WestPanel']    = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('SPRM', 'JQ');
		$param['otros']        = $this->datasis->otros('SPRM', 'JQ');
		
		$param['centerpanel']  = $centerpanel;
		$param['funciones']    = $funciones;
		$param['tema1']        = 'darkness';
		$param['anexos']       = 'anexos1';
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;

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
			'editable' => $editar,
			'search'   => 'false'
		));


		$grid->addField('cod_prv');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
		));


		$grid->addField('tipo_doc');
		$grid->label('Tipo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));


		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
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


		$grid->addField('abonos');
		$grid->label('Abonos');
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


		$grid->addField('vence');
		$grid->label('Vence');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('tipo_ref');
		$grid->label('Tipo_ref');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));


		$grid->addField('num_ref');
		$grid->label('Num_ref');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
		));


		$grid->addField('observa1');
		$grid->label('Observa1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
		));


		$grid->addField('observa2');
		$grid->label('Observa2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
		));


		$grid->addField('banco');
		$grid->label('Banco');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));


		$grid->addField('tipo_op');
		$grid->label('Tipo_op');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));


		$grid->addField('comprob');
		$grid->label('Comprob');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
		));


		$grid->addField('numche');
		$grid->label('Numche');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
		));


		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
		));


		$grid->addField('descrip');
		$grid->label('Descrip');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
		));


		$grid->addField('ppago');
		$grid->label('Ppago');
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


		$grid->addField('nppago');
		$grid->label('Nppago');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
		));


		$grid->addField('reten');
		$grid->label('Reten');
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


		$grid->addField('nreten');
		$grid->label('Nreten');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
		));


		$grid->addField('mora');
		$grid->label('Mora');
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


		$grid->addField('benefi');
		$grid->label('Benefi');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
		));


		$grid->addField('control');
		$grid->label('Control');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
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


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
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


		$grid->addField('pmora');
		$grid->label('Pmora');
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


		$grid->addField('reteiva');
		$grid->label('Reteiva');
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


		$grid->addField('nfiscal');
		$grid->label('Nfiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
		));


		$grid->addField('montasa');
		$grid->label('Montasa');
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


		$grid->addField('monredu');
		$grid->label('Monredu');
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


		$grid->addField('monadic');
		$grid->label('Monadic');
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


		$grid->addField('tasa');
		$grid->label('Tasa');
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


		$grid->addField('reducida');
		$grid->label('Reducida');
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


		$grid->addField('sobretasa');
		$grid->label('Sobretasa');
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


		$grid->addField('exento');
		$grid->label('Exento');
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


		$grid->addField('fecdoc');
		$grid->label('Fecdoc');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('afecta');
		$grid->label('Afecta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
		));


		$grid->addField('fecapl');
		$grid->label('Fecapl');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('serie');
		$grid->label('Serie');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
		));


		$grid->addField('depto');
		$grid->label('Depto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));


		$grid->addField('modificado');
		$grid->label('Modificado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('negreso');
		$grid->label('Negreso');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
		));


		$grid->addField('ndebito');
		$grid->label('Ndebito');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
		));


		$grid->addField('causado');
		$grid->label('Causado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
		));


		$grid->addField('causado');
		$grid->label('Causado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('240');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		$grid->setonSelectRow('
			function(id){
				$.ajax({
					url: "'.base_url().$this->url.'tabla/"+id,
					success: function(msg){
						//alert( "El ultimo codigo ingresado fue: " + msg );
						$("#adicional").html(msg);
					}
				});
			}
		');


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
		$mWHERE = $grid->geneTopWhere('sprm');

		$response   = $grid->getData('sprm', array(array()), array(), false, $mWHERE, 'id', 'desc' );
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
				$this->db->insert('sprm', $data);
			}
			return "Registro Agregado";

		} elseif($oper == 'edit') {
			//unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('sprm', $data);
			return "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sprm WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM sprm WHERE id=$id ");
				logusu('sprm',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}


	function tabla() {
		$id = $this->uri->segment($this->uri->total_segments());
		
		$row = $this->datasis->damereg("SELECT cod_prv, tipo_doc, numero, estampa, transac FROM sprm WHERE id=$id");

		$transac  = $row['transac'];
		$cod_prv  = $row['cod_prv'];
		$numero   = $row['numero'];
		$tipo_doc = $row['tipo_doc'];
		$estampa  = $row['estampa'];
		$salida   = '';

		if (!empty($transac)){

		$td1  = "<td style='border-style:solid;border-width:1px;border-color:#78FFFF;' valign='top' align='center'>\n";
		$td1 .= "<table width='98%'>\n<caption style='background-color:#5E352B;color:#FFFFFF;font-style:bold'>";

		// Movimientos Relacionados en Proveedores SPRM
		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos
			FROM sprm WHERE transac='$transac' AND id<>$id ORDER BY cod_prv ";
		$query = $this->db->query($mSQL);
		$salida = '<table width="100%"><tr>';
		$saldo  = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= $td1;
			$salida .= "Movimiento en Proveedores</caption>";
			$salida .= "<tr bgcolor='#E7E3E7'><td>Nombre</td><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['cod_prv'].'-'.$row['nombre']."</td>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			if ($saldo <> 0)
				$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table></td>";
		}

		// Movimientos Relacionados en SMOV
		$mSQL = "SELECT cod_cli, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos
			FROM smov WHERE transac='$transac' ORDER BY cod_cli ";
		$query = $this->db->query($mSQL);
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= $td1;
			$salida .= "Movimiento en Clientes</caption>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Nombre</td><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['cod_cli'].'-'.$row['nombre']."</td>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table></td>";
		}

		//Retencion de IVA RIVA
		$mSQL = "
			SELECT periodo, nrocomp, reiva FROM riva WHERE tipo_doc='$tipo_doc' AND numero='$numero' AND MID(transac,1,1)<>'_'";
			"UNION ALL
			SELECT periodo, nrocomp, reiva FROM riva WHERE transac='$transac' AND MID(transac,1,1)<>'_'
			";
		$query = $this->db->query($mSQL);
		if ( $query->num_rows() > 0 ){
			$salida .= $td1;
			$salida .= "Retenciones de IVA</caption>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Periodo</td><td align='center'>Numero</td><td align='center'>Monto</tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['periodo']."</td>";
				$salida .= "<td>".$row['nrocomp'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['reiva']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table></td>";
		}


		//if ( $tipo_doc <> 'FC' ){
			$mSQL = "
				SELECT tipo_doc, numero, montonet FROM scst a WHERE a.transac='$transac'
				UNION ALL
				SELECT tipo_doc, numero, totneto  FROM gser a WHERE a.transac='$transac'
				";
			$query = $this->db->query($mSQL);
			if ( $query->num_rows() > 0 ){
				$salida .= $td1;
				$salida .= "Gasto/Compra</caption>";
				$salida .= "<tr bgcolor='#e7e3e7'><td>Tipo</td><td align='center'>Numero</td><td align='center'>Monto</tr>";
				foreach ($query->result_array() as $row)
				{
					$salida .= "<tr>";
					$salida .= "<td>".$row['tipo_doc']."</td>";
					$salida .= "<td>".$row['numero'].  "</td>";
					$salida .= "<td align='right'>".nformat($row['montonet']).   "</td>";
					$salida .= "</tr>";
				}
				$salida .= "</table></td>";
			}
		//}

		// Movimientos Relacionados ITPPRO
		$mSQL = "SELECT tipo_doc, numero, monto, abono FROM itppro WHERE transac='$transac' ";
		$query = $this->db->query($mSQL);
		if ( $query->num_rows() == 0 ){
			$mSQL = "SELECT tipoppro tipo_doc, numppro numero, monto, abono FROM itppro WHERE tipo_doc='$tipo_doc' AND numero='$numero'";
			$query = $this->db->query($mSQL);
		}
		//$salida .= "<td>$mSQL</td>";
		if ( $query->num_rows() > 0 ){
			$saldo = 0;
			$salida .= $td1;
			$salida .= "Movimientos Relacionados</caption>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td><td align='center'>Abono</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$saldo += $row['abono'];
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "<td align='right'>".nformat($row['abono']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'><b>Saldo : ".nformat($saldo). "</b></td></tr>";
			$salida .= "</table></td>";
		}

		// Movimiento en Caja/Bancos
		$mSQL = "SELECT codbanc, tipo_op, numero, monto FROM bmov WHERE transac='$transac' AND monto<>0";
		$query = $this->db->query($mSQL);
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= $td1;
			$salida .= "Movimiento en Caja y/o Bancos</caption>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Bco</td><td>Tipo</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['codbanc']. "</td>";
				$salida .= "<td>".$row['tipo_op']."</td>";
				$salida .= "<td>".$row['numero']."</td>";
				$salida .= "<td align='right'>".nformat($row['monto'])."</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table></td>";
		}

		// Prestamos PRMO
		$mSQL = "SELECT tipop, codban, if(observa2='',observa1,observa2) observa, monto FROM prmo WHERE transac='$transac' AND clipro='$cod_prv' AND monto<>0";
		$query = $this->db->query($mSQL);
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= $td1;
			$salida .= "Prestamos</caption>";
			$salida .= "<tr bgcolor='#e7e3e7'><td></td><td>Bco</td><td>Observacion</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipop']."</td>";
				$salida .= "<td>".$row['codban']."</td>";
				$salida .= "<td>".$row['observa']."</td>";
				$salida .= "<td align='right'>".nformat($row['monto'])."</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table></td>";
		}

		//Cruce de Cuentas
		$mSQL = "
			SELECT b.tipo tipo, b.proveed codcp, MID(b.nombre,1,25) nombre, a.onumero, a.monto, b.numero, b.fecha
			FROM itcruc AS a JOIN cruc AS b ON a.numero=b.numero
			WHERE b.proveed='$cod_prv' AND b.transac='$transac' AND a.onumero!='$tipo_doc$numero'
			UNION ALL
			SELECT b.tipo tipo, b.cliente codcp, MID(b.nomcli,1,25) nombre, a.onumero, a.monto, b.numero, b.fecha
			FROM itcruc AS a JOIN cruc AS b ON a.numero=b.numero
			WHERE b.cliente='$cod_prv' AND b.transac='$transac' ORDER BY onumero
			"; 

		$query = $this->db->query($mSQL);
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= $td1;
			$salida .= "Cruce de Cuentas</caption>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Nombre</td><td>Codigo</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>(".$row['tipo'].') '.$row['nombre']."</td>";
				$salida .= "<td>".$row['codcp']."</td>";
				$salida .= "<td>".$row['onumero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table></td>";
		}
		}
		echo $salida.'</tr></table>';
	}



/*
//pagoproveed
class Sprm extends validaciones {

	function sprm(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(500,1);
	}

	function index(){
		if ( !$this->datasis->iscampo('sprm','id') ) {
			$this->db->simple_query('ALTER TABLE sprm DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE sprm ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE sprm ADD UNIQUE INDEX cod_prv (cod_prv, tipo_doc, numero, fecha)');
			echo "Indice ID Creado";
		}
		//redirect($this->url.'filteredgrid');
		$this->sprmextjs();
		//redirect("finanzas/sprm/filteredgrid");
	}

	function filteredgrid(){

		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Pago a Proveedores", "sprm");

		$filter->numero = new inputField("Numero", "numero");
		$filter->numero->size=12;
		$filter->numero->maxlength=8;

		$filter->cod_prv = new inputField("C&oacute;digo Proveedor", "cod_prv");
		$filter->cod_prv->size=12;
		$filter->cod_prv->maxlength=5;

		$filter->tipo_doc = new dropdownField("Tipo de Documento", "tipo_doc");
		$filter->tipo_doc->option('','');
		$filter->tipo_doc->option("AB","Abono");
		$filter->tipo_doc->option("AN","Anticipo");
		$filter->tipo_doc->option("NC","Nota de Cr&eacute;dito");
		$filter->tipo_doc->style ="width:100px";

		$filter->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$filter->fecha->size=12;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/sprm/dataedit/show/<#cod_prv#>/<#tipo_doc#>/<#numero#>/<#fecha#>','<#numero#>');

		$grid = new DataGrid("Lista de Pago a Proveedores");
		$grid->order_by("numero","asc");
		$grid->per_page = 10;

		$grid->column("Numero",$uri);
		$grid->column("Cod. Proveedor","cod_prv");
		$grid->column("Tipo Documento","tipo_doc");
		$grid->column("Fecha","fecha");

		$grid->add("finanzas/sprm/dataedit/create");
		$grid->build();

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		$data['title']   = "<h1>Pago a Proveedores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit','datadetalle','fields','datagrid');

		$mSPRV=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed' =>'C&oacute;digo Proveedor','nombre'=>'Nombre','rif'=>'RIF'),
		'retornar'=>array('proveed'=>'cod_prv','nombre'=>'nombre'),
		'titulo'  =>'Buscar Proveedor');
		$bsprv =$this->datasis->modbus($mSPRV);

		$script ='$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit("Pago Proveedores", "sprm");
		$edit->back_url = "finanzas/sprm";
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->pre_process('insert','_guarda');
		$edit->pre_process('update','_guarda');

		$edit->numero = new inputField('Numero', 'numero');
		$edit->numero->size=12;
		$edit->numero->maxlength=8;
		$edit->numero->rule='trim|required';

		//$edit->numero = new dropdownField("Numero", "numero");
		//$edit->numero->size=30;
		//$edit->numero->option('','');
		//$edit->numero->options("SELECT codbanc, banco FROM bmov ORDER BY banco");

		$edit->cod_prv = new inputField('C&oacute;digo Proveedor', 'cod_prv');
		$edit->cod_prv->size=12;
		$edit->cod_prv->maxlength=5;
		$edit->cod_prv->rule='trim|required';
		$edit->cod_prv->append($bsprv);
		$edit->cod_prv->readonly=true;

		$edit->nombre = new inputField('Nombre Proveedor', 'nombre');
		$edit->nombre->size=30;
		$edit->nombre->maxlength=40;
		$edit->nombre->rule='trim';


		$edit->tipo_doc = new dropdownField('Tipo de Documento', 'tipo_doc');
		$edit->tipo_doc->option('AB','Abono');
		$edit->tipo_doc->option('AN','Anticipo');
		//$edit->tipo_doc->option('FC','Factura');
		//$edit->tipo_doc->option('JD','JD');
		$edit->tipo_doc->option('NC','Nota de Cr&eacute;dito');
		//$edit->tipo_doc->option('ND','Nota de D&eacute;bito');
		$edit->tipo_doc->style ='width:100px';
		$edit->tipo_doc->rule='required';

		$edit->fecha = new DateonlyField('Fecha', 'fecha','Y/m/d');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size=12;
		$edit->fecha->rule='required|chfecha';

		$edit->monto =new inputField('Monto','monto');
		$edit->monto->size = 12;
		$edit->monto->maxlength = 17;
		$edit->monto->rule = 'trim|required|numeric';
		$edit->monto->css_class='inputnum';

		$edit->impuesto =new inputField('Impuesto','impuesto');
		$edit->impuesto->size = 12;
		$edit->impuesto->maxlength = 17;
		$edit->impuesto->rule = 'trim|required|numeric';
		$edit->impuesto->css_class='inputnum';

		$edit->abonos =new inputField('Abonos','abonos');
		$edit->abonos->size = 12;
		$edit->abonos->maxlength = 17;
		$edit->abonos->rule = 'trim|required|numeric';
		$edit->abonos->css_class='inputnum';

		$edit->vence = new DateonlyField('Vence', 'vence','Y/m/d');
		$edit->vence->size=12;

		$edit->tipo_ref = new dropdownField('Tipo de Referencia', 'tipo_ref');
		$edit->tipo_ref->option('OS','OS');
		$edit->tipo_ref->option('AB','AB');
		$edit->tipo_ref->option('AC','AC');
		$edit->tipo_ref->option('AP','AP');
		$edit->tipo_ref->option('CR','CR');
		$edit->tipo_ref->style ='width:100px';

		$edit->num_ref = new inputField('Num. Referencia', 'num_ref');
		$edit->num_ref->size=12;
		$edit->num_ref->maxlength=8;
		$edit->num_ref->rule='trim';

		$edit->observa1 = new inputField('Observaciones', 'observa1');
		$edit->observa1->size=50;
		$edit->observa1->maxlength=50;
		$edit->observa1->rule='trim';

		$edit->observa2 = new inputField('', 'observa2');
		$edit->observa2->size=50;
		$edit->observa2->maxlength=50;
		$edit->observa2->rule='trim';

		$edit->banco = new dropdownField('Caja/Banco', 'banco');
		$edit->banco->size=30;
		$edit->banco->option('','');
		$edit->banco->options('SELECT codbanc, banco FROM bmov ORDER BY banco');

		$edit->tipo_op = new inputField('Tipo de Operacion', 'tipo_op');
		$edit->tipo_op->size=12;
		$edit->tipo_op->maxlength=2;
		$edit->tipo_op->rule='trim';

		$edit->comprob = new dropdownField('Comprobante', 'comprob');
		$edit->comprob->option('AJUST','REGUALRIZAR F. MAL PROCESADA');
		$edit->comprob->option('BONIP','BONIFICACION DE PROVEEDORES');
		$edit->comprob->option('DECOM','DEVOLUCION EN COMPRAS');
		$edit->comprob->option('DESOP','OTROS DESCUENTOS PROVEEDORES');
		$edit->comprob->option('DESPP','DESCUENTO PRONTO PAGO PROVEEDO');
		$edit->comprob->option('DEVOP','DESCUENTO X VOLUMEN PROVEEDOR');
		$edit->comprob->option('DFCAP','DIFERENCIA /CAMBIO PROVEEDOR');
		$edit->comprob->option('DIFPP','DIFERENCIA /PRECIOS PROVEEDOR');
		$edit->comprob->size=30;

		$edit->numche = new inputField('Numche', 'numche');
		$edit->numche->size=12;
		$edit->numche->maxlength=12;
		$edit->numche->rule='trim';

		$edit->codigo = new inputField('Codigo', 'codigo');
		$edit->codigo->size=12;
		$edit->codigo->maxlength=50;
		$edit->codigo->rule='trim';

		$edit->descrip = new inputField('Descripcion', 'descrip');
		$edit->descrip->size=30;
		$edit->descrip->maxlength=30;
		$edit->descrip->rule='trim';

		$edit->ppago =new inputField('Ppago','ppago');
		$edit->ppago->size = 12;
		$edit->ppago->maxlength = 17;
		$edit->ppago->rule = 'trim|numeric';
		$edit->ppago->css_class='inputnum';

		$edit->nppago = new inputField('NPpago', 'nppago');
		$edit->nppago->size=12;
		$edit->nppago->maxlength=8;
		$edit->nppago->rule='trim';

		$edit->reten =new inputField('Retenci&oacute;n','reten');
		$edit->reten->size = 12;
		$edit->reten->maxlength = 17;
		$edit->reten->rule = 'trim|numeric';
		$edit->reten->css_class='inputnum';

		$edit->nreten = new inputField('Nreten', 'nreten');
		$edit->nreten->size=12;
		$edit->nreten->maxlength=8;
		$edit->nreten->rule='trim';

		$edit->mora =new inputField('Mora','mora');
		$edit->mora->size = 12;
		$edit->mora->maxlength = 17;
		$edit->mora->rule = 'trim|numeric';
		$edit->mora->css_class='inputnum';

		$edit->posdata = new DateonlyField('Posdata', 'posdata','Y/m/d');
		$edit->posdata->size=12;

		$edit->benefi = new inputField('Beneficiario', 'benefi');
		$edit->benefi->size=30;
		$edit->benefi->maxlength=40;
		$edit->benefi->rule='trim';

		$edit->control = new inputField('Control', 'control');
		$edit->control->size=12;
		$edit->control->maxlength=8;
		$edit->control->rule='trim';

		//$edit->transac = new inputField('Transaccion', 'transac');
		//$edit->transac->size=12;
		//$edit->transac->maxlength=8;
		//$edit->transac->rule='trim';

		$edit->cambio =new inputField('Cambio','cambio');
		$edit->cambio->size = 12;
		$edit->cambio->maxlength = 17;
		$edit->cambio->rule = 'trim|numeric';
		$edit->cambio->css_class='inputnum';

		$edit->pmora =new inputField('Pmora','pmora');
		$edit->pmora->size = 12;
		$edit->pmora->maxlength = 6;
		$edit->pmora->rule = 'trim|numeric';
		$edit->pmora->css_class='inputnum';

		$edit->reteiva =new inputField('Retenci&oacute;n de IVA','reteiva');
		$edit->reteiva->size = 12;
		$edit->reteiva->maxlength = 18;
		$edit->reteiva->rule = 'trim|numeric';
		$edit->reteiva->css_class='inputnum';

		$edit->id =new inputField('id','id');//entero
		$edit->id->size = 12;

		$edit->nfiscal = new inputField('Nfiscal', 'nfiscal');
		$edit->nfiscal->size=12;
		$edit->nfiscal->maxlength=8;
		$edit->nfiscal->rule='trim';

		$edit->montasa =new inputField('montasa','montasa');
		$edit->montasa->size = 12;
		$edit->montasa->maxlength = 17;
		$edit->montasa->rule = 'trim|numeric';
		$edit->montasa->css_class='inputnum';

		$edit->monredu =new inputField('monredu','monredu');
		$edit->monredu->size = 12;
		$edit->monredumonredu->maxlength = 17;
		$edit->monredu->rule = 'trim|numeric';
		$edit->monredu->css_class='inputnum';

		$edit->monadic =new inputField('monadic','monadic');
		$edit->monadic->size = 12;
		$edit->monadic->maxlength = 17;
		$edit->monadic->rule = 'trim|numeric';
		$edit->monadic->css_class='inputnum';

		$edit->tasa =new inputField('tasa','tasa');
		$edit->tasa->size = 12;
		$edit->tasa->maxlength = 17;
		$edit->tasa->rule = 'trim|numeric';
		$edit->tasa->css_class='inputnum';

		$edit->reducida =new inputField('reducida','reducida');
		$edit->reducida->size = 12;
		$edit->reducida->maxlength = 17;
		$edit->reducida->rule = 'trim|numeric';
		$edit->reducida->css_class='inputnum';

		$edit->sobretasa =new inputField('sobretasa','sobretasa');
		$edit->sobretasa->size = 12;
		$edit->sobretasa->maxlength = 17;
		$edit->sobretasa->rule = 'trim|numeric';
		$edit->sobretasa->css_class='inputnum';

		$edit->exento =new inputField('exento','exento');
		$edit->exento->size = 12;
		$edit->exento->maxlength = 17;
		$edit->exento->rule = 'trim|numeric';
		$edit->exento->css_class='inputnum';

		$edit->fecdoc = new DateonlyField('Fecha de Doc', 'fecdoc','Y/m/d');
		$edit->fecdoc->size=12;

		$edit->afecta = new inputField('afecta', 'afecta');
		$edit->afecta->size=12;
		$edit->afecta->maxlength=10;
		$edit->afecta->rule='trim';

		$edit->fecapl = new DateonlyField('Fecapl', 'fecapl','Y/m/d');
		$edit->fecapl->size=12;

		$edit->serie = new inputField('serie', 'serie');
		$edit->serie->size=12;
		$edit->serie->maxlength=8;
		$edit->serie->rule='trim';

		$edit->depto = new inputField('serie', 'depto');
		$edit->depto->size=12;
		$edit->depto->maxlength=3;
		$edit->depto->rule='trim';

		$edit->buttons('modify', 'save', 'undo', 'back');
		$edit->build();

		//$smenu['link']=barra_menu('230');
		$data['content'] = $edit->output;
		//$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['title']   = "<h1>Pago a Proveedores</h1>";
		$data['head']    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _guarda($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
		$query  =$this->db->query($sql);

		//$transac=$do->get('transc');
		//$do->db->set('transac', $transac);
		$do->db->set('estampa', 'CURDATE()', FALSE);
		$do->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->db->set('usuario', $this->session->userdata('usuario'));
	}


	function sclibu(){
		$control = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM sprm a JOIN sprv b ON a.proveed=b.proveed WHERE control='$control'");
		redirect('finanzas/sprv/dataedit/show/'.$id);
	}

	function tabla() {
		$transac  = isset($_REQUEST['transac'])  ? $_REQUEST['transac']  :  0;
		$cod_prv  = isset($_REQUEST['cod_prv'])  ? $_REQUEST['cod_prv']  :  0;
		$numero   = isset($_REQUEST['numero'])   ? $_REQUEST['numero']   :  0;
		$tipo_doc = isset($_REQUEST['tipo_doc']) ? $_REQUEST['tipo_doc'] :  0;

		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos
			FROM sprm WHERE transac='$transac' ORDER BY cod_prv ";
		$query = $this->db->query($mSQL);
		$codcli = 'XXXXXXXXXXXXXXXX';
		$salida = '';
		$saldo  = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				if ( $codcli != $row['cod_prv']){
					$codcli = $row['cod_prv'];
					$salida .= "<tr bgcolor='#c7d3c7'>";
					$salida .= "<td colspan=4>".trim($row['nombre']). "</td>";
					$salida .= "</tr>";
				}
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}

		$mSQL = "SELECT cod_cli, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos
			FROM smov WHERE transac='$transac' ORDER BY cod_cli ";
		$query = $this->db->query($mSQL);
		$codcli = 'XXXXXXXXXXXXXXXX';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				if ( $codcli != $row['cod_prv']){
					$codcli = $row['cod_prv'];
					$salida .= "<tr bgcolor='#c7d3c7'>";
					$salida .= "<td colspan=4>".trim($row['nombre']). "</td>";
					$salida .= "</tr>";
				}
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}

		//cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos
		//Cruce de Cuentas
		$mSQL = "SELECT b.proveed cod_prv, MID(b.nombre,1,25) nombre, a.monto, b.numero, b.fecha
			FROM itcruc AS a JOIN cruc AS b ON a.numero=b.numero
			WHERE b.cliente='$cod_prv' AND a.onumero='$tipo_doc$numero'
			UNION ALL
			SELECT b.cliente cod_prv, MID(b.nomcli,1,25) nombre, -a.monto, b.numero, b.fecha
			FROM itcruc AS a JOIN cruc AS b ON a.numero=b.numero
			WHERE b.cliente='$cod_prv' AND a.onumero='$tipo_doc$numero'
			ORDER BY numero
			";
		$query = $this->db->query($mSQL);
		$codcli = 'XXXXXXXXXXXXXXXX';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<td colspan=4>Cruce de Cuentas</td>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Codigo</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['cod_prv']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table>";
		}
		echo $salida;
	}

*/
}
?>