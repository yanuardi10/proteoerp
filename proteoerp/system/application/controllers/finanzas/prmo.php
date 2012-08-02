<?php
class Prmo extends Controller {
	var $mModulo='PRMO';
	var $titp='Modulo PRMO';
	var $tits='Modulo PRMO';
	var $url ='finanzas/prmo/';

	function Prmo(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
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
		window.open(\''.base_url().'formatos/ver/PRMO/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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
		$param['listados'] = $this->datasis->listados('PRMO', 'JQ');
		$param['otros']    = $this->datasis->otros('PRMO', 'JQ');
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

		$grid->addField('tipop');
		$grid->label('Tipop');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));


		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
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


		$grid->addField('codban');
		$grid->label('Codban');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));


		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));


		$grid->addField('numche');
		$grid->label('Numche');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('benefi');
		$grid->label('Benefi');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('comprob');
		$grid->label('Comprob');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 6 }',
		));


		$grid->addField('clipro');
		$grid->label('Clipro');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('docum');
		$grid->label('Docum');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('banco');
		$grid->label('Banco');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 10 }',
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


		$grid->addField('cuotas');
		$grid->label('Cuotas');
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


		$grid->addField('observa1');
		$grid->label('Observa1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 50 }',
		));


		$grid->addField('observa2');
		$grid->label('Observa2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 50 }',
		));


		$grid->addField('transac');
		$grid->label('Transac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
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
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('cadano');
		$grid->label('Cadano');
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


		$grid->addField('apartir');
		$grid->label('Apartir');
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
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('ningreso');
		$grid->label('Ningreso');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('retencion');
		$grid->label('Retencion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 14 }',
		));


		$grid->addField('factura');
		$grid->label('Factura');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('remision');
		$grid->label('Remision');
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
		$mWHERE = $grid->geneTopWhere('prmo');

		$response   = $grid->getData('prmo', array(array()), array(), false, $mWHERE );
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
				$this->db->insert('prmo', $data);
				echo "Registro Agregado";

				logusu('PRMO',"Registro ????? INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			//unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('prmo', $data);
			logusu('PRMO',"Registro ????? MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM prmo WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				//$this->db->simple_query("DELETE FROM prmo WHERE id=$id ");
				logusu('PRMO',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	//*******************************************
	//
	// Guarda cheques devueltos en PRMO
	//
	function prmochdev(){
		$id = $this->uri->segment($this->uri->total_segments());
		
		$transac = $this->datasis->prox_sql("ntransa");
		//LOCAL aLISTA, mSQL, aVALORES, mREG, mMEG, mTBANCO

		$mSQL = "SELECT a.*, b.recibe codban FROM sfpa a JOIN bcaj b ON a.deposito=b.numero WHERE a.id=$id";
		$reg  = $this->datasis->damereg($mSQL);
		
		if ( $reg['tipo'] <> 'CH' ){
			echo "Cheque ya devuelto";
			return;
		}
		

		$XNEGRESO  = "        ";
		$XNINGRESO = "        ";
		$XTIPO     = "ND";
		$XVENCE    = date('Y/m/d');
		$XFECHA    = date('Y/m/d');
		$XCODBAN   = $reg['codban'];
		$mTBANCO   = $this->datasis->dameval("SELECT tbanco FROM banc WHERE codbanc='$XCODBAN'");
		$XNUMERO   = $this->datasis->prox_sql("nprmo");
		$XNOMBRE   = $this->datasis->dameval("SELECT nombre FROM scli WHERE cliente='".$reg['cod_cli']."'");

		// Guarda en PRMO
		$aLISTA["tipop"]    = "3";
		$aLISTA["numero"]   = $XNUMERO;
		$aLISTA["fecha"]    = $XFECHA;
		$aLISTA["codban"]   = $XCODBAN;
		$aLISTA["tipo"]     = "ND";
		$aLISTA["numche"]   =  $reg['num_ref'];
		$aLISTA["benefi"]   =  "";
		$aLISTA["comprob"]  =  "";
		$aLISTA["clipro"]   =  $reg['cod_cli'];
		$aLISTA["nombre"]   =  $XNOMBRE ;
		$aLISTA["docum"]    =  $reg['deposito'];
		$aLISTA["banco"]    =  $reg['banco'] ;
		$aLISTA["monto"]    =  $reg['monto'] ;
		$aLISTA["cuotas"]   =  1 ;
		$aLISTA["vence"]    = $XVENCE ;
		$aLISTA["observa1"] = "CHEQUE DEVUELTO" ;
		$aLISTA["observa2"] = "" ;
		$aLISTA["cadano"]   = 1 ;
		$aLISTA["apartir"]  = $XFECHA;

		$aLISTA["usuario"]  = $this->secu->usuario() ;
		$aLISTA["transac"]  = $transac ;
		$aLISTA["estampa"]  = date('Y/m/d') ;
		$aLISTA["hora"]     = date('H:i:s') ;
		
		//$aLISTA["negreso"]  =  XNEGRESO ;
		//$aLISTA["ningreso"] = XNINGRESO ;
   
		$this->db->insert('prmo', $aLISTA);

  		// GUARDA EN BANCO

		//ACTUSAL(XCODBAN, XFECHA, XMONTO*IF(XTIPO$'CH,ND',-1,1) )
		$mREG = $this->datasis->damereg("SELECT numcuent, banco, saldo, tbanco FROM banc WHERE codbanc='$XCODBAN'");

		$mCUENTA   = $mREG['numcuent'];
		$mBANCO    = $mREG['banco'];
		$mSALDO    = $mREG['saldo'];
		$mTBANCO   = $mREG['tbanco'];
		$XCOMPROB  = $this->datasis->prox_sql("ncomprob");

		$aLISTA = array();
		$aLISTA["codbanc"]  = $XCODBAN;
		$aLISTA["numcuent"] = $mCUENTA;
		$aLISTA["banco"]    = $mBANCO;
		$aLISTA["saldo"]    = $mSALDO;
		$aLISTA["fecha"]    = $XFECHA;
		$aLISTA["tipo_op"]  = 'ND';
		$aLISTA["numero"]   = $reg['num_ref'];
		$aLISTA["concepto"] = "CHEQUE DEVUELTO CLIENTE ".$XNUMERO;
		$aLISTA["clipro"]   = 'C';
		$aLISTA["liable"]   = 'S';
		$aLISTA["concep2"]  = "CHEQUE DEVUELTO CLIENTE ".$reg['cod_cli'];
		$aLISTA["concep3"]  = "";
		$aLISTA["monto"]    = $reg['monto'];
		$aLISTA["codcp"]    = $reg['cod_cli'];
		$aLISTA["nombre"]   = $XNOMBRE;
		//$aLISTA["benefi"]   = XBENEFI;
		$aLISTA["comprob"]  = $XCOMPROB;
		$aLISTA["posdata"]  = $XFECHA;
		//$aLISTA["negreso"]  = XNEGRESO;

		$aLISTA["usuario"]   = $this->secu->usuario() ;
		$aLISTA["transac"]   = $transac ;
		$aLISTA["estampa"]   = date('Y/m/d') ;
		$aLISTA["hora"]      = date('H:i:s') ;


		$this->db->insert('bmov', $aLISTA);


		$i = 0;
		while ( $i == 0 ){
			$mNUMERO = $this->datasis->prox_sql("ndcli");
			$mSQL    = "SELECT count(*) FROM smov WHERE tipo_doc='ND' AND numero='$mNUMERO' ";
			$i       = $this->datasis->dameval($mSQL);
		}

		$aLISTA = array();
		$aLISTA["COD_CLI"]  = $reg['cod_cli'];
		$aLISTA["NOMBRE"]   = $XNOMBRE ;
		$aLISTA["TIPO_DOC"] = "ND";
		$aLISTA["NUMERO"]   = $mNUMERO;
		$aLISTA["FECHA"]    = $XFECHA;
		$aLISTA["MONTO"]    = $reg['monto'];
		$aLISTA["IMPUESTO"] = 0;
		$aLISTA["VENCE"]    = $XVENCE;
		$aLISTA["TIPO_REF"] = "PR";
		$aLISTA["NUM_REF"]  = $XNUMERO;
		$aLISTA["OBSERVA1"] = "CHEQUE DEVUELTO CLIENTE ".$XNUMERO;
		$aLISTA["OBSERVA2"] = "CHEQUE DEVUELTO CLIENTE ".$reg['cod_cli'];
		$aLISTA["BANCO"]    = $XCODBAN;
		$aLISTA["FECHA_OP"] = $XFECHA;
		$aLISTA["NUM_OP"]   = $reg['num_ref'];
		$aLISTA["TIPO_OP"]  = 'ND';

		$aLISTA["usuario"]   = $this->secu->usuario() ;
		$aLISTA["transac"]   = $transac ;
		$aLISTA["estampa"]   = date('Y/m/d') ;
		$aLISTA["hora"]      = date('H:i:s') ;
		$this->db->insert('smov', $aLISTA);
		$this->db->simple_query("UPDATE sfpa SET tipo='CD' WHERE id=$id");
		echo "Cheque Devuelto ";
	}


}

/*
require_once(BASEPATH.'application/controllers/validaciones.php');

class Prmo extends validaciones {
	function prmo(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(206,1);
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		//define ("THISFILE",   APPPATH."controllers/finanzas/". $this->uri->segment(2).EXT);
	}
	function index(){
		redirect("finanzas/prmo/filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
		$filter = new DataFilter("Filtro de Otros Movimientos de Caja y Bancos", "prmo");
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->codban = new dropdownField("Caja/Banco", "codban");
		$filter->codban->option("","");
		$filter->codban->options("SELECT codbanc, banco FROM bmov ORDER BY banco ");
		
		$filter->banco = new dropdownField("Tipo", "tipo");
		$filter->banco->option("","");
		$filter->banco->option("1","Prestamo Otorgado");		
		$filter->banco->option("2","Prestamo Recibido");
		$filter->banco->option("3","Cheque Devuelto Cliente");
		$filter->banco->option("4","Cheque Devuelto Proveedor");
		$filter->banco->option("5","Deposito por Analizar");
		$filter->banco->option("6","Cargos Indevidos por el Banco");
		$filter->banco->option("7","Todos");
		
		$filter->clipro = new inputField("Cli/Prv", "monto");
		$filter->clipro->size=12;
		
		$filter->monto = new inputField("Monto", "monto");
		$filter->monto->size=12;
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('finanzas/prmo/dataedit/show/<#numero#>','<#numero#>');
		
		$grid = new DataGrid("Lista de Otros Movimientos de Caja y Bancos");
		$grid->order_by("numero","asc");
		$grid->per_page = 10;
		
		$grid->column("Numero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Banco","banco");
		$grid->column("Cli/Prv","clipro");
		$grid->column("Monto","monto","align='right'");
		
		$grid->add("finanzas/prmo/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Otros Movimientos de Caja y Bancos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
*/
?>