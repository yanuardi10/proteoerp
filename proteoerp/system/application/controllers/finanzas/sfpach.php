<?php
class sfpach extends Controller {
	var $titp='Modificacion de Medios de Pago';
	var $tits='Modificacion de Medios de Pago';
	var $url ='finanzas/sfpach/';

	function sfpach(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_id('A00',1);
		//$this->instalar();
	}

	function index(){
		//redirect($this->url.'filteredgrid');
		 if ( !$this->datasis->iscampo('sfpa','deposito') ) {
			$this->db->simple_query('ALTER TABLE sfpa ADD COLUMN deposito CHAR(12) NULL DEFAULT NULL ');
		};
		 if ( !$this->datasis->iscampo('sfpa','id') ) {
			$this->db->simple_query('ALTER TABLE sfpa ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};
		redirect($this->url.'jqdatag');
	}

	function jqdatag(){

		$grid = $this->defgrid();
		$param['grid'] = $grid->deploy();

		$bodyscript = '
<script type="text/javascript">
var montotal = 0;

function probar( o, n ) {
	if ( o.val().length < 1 ) {
		o.addClass( "ui-state-error" );
		updateTips( "Seleccion un " + n + "." );
		return false;
	} else {
		return true;
	}
};

$(function() {
	$( "input:submit, a, button", ".otros" ).button();

	var 	envia = $( "#envia" ),
		recibe = $( "#recibe" ),
		allFields = $( [] ).add( envia ).add( recibe );

	var grid = jQuery("#newapi'.$param['grid']['gridname'].'");
	var s;
	s = grid.getGridParam(\'selarrrow\'); 

	$( "#deposito-form" ).dialog({
		autoOpen: false,
		height: 300,
		width: 420,
		modal: true,
		buttons: {
			"Guardar": function() {
				var bValid = true;
				allFields.removeClass( "ui-state-error" );
				bValid = bValid && probar( envia, "Caja" );
				bValid = bValid && probar( recibe, "Banco" );
				if ( bValid ) {
                                        $.ajax({
                                                type: "POST",
                                                url:"'.site_url("finanzas/sfpach/depositos").'",
                                                processData: true,
                                                data: "envia="+escape(envia.val())+"&recibe="+escape(recibe.val())+"&monto="+escape(montotal)+"&ids="+escape(s),
                                                success: function(a){
							var res = $.parseJSON(a);
							$.prompt(res.mensaje,
								{ submit: function(e,v,m,f){
									window.open(\''.base_url().'formatos/ver/BANCAJA/\'+res.numero, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
									}
								}
							);
							grid.trigger("reloadGrid");
							sumamonto();
							return [true, a ];
						}
					})
					$( this ).dialog( "close" );
				}
			},
			Cancelar: function() {$( this ).dialog( "close" );}
		},
		close: function() {allFields.val( "" ).removeClass( "ui-state-error" );}
	});
	$( "#depositar" ).click(function() {
		sumamonto();
		$( "#deposito-form" ).dialog( "open" );
	});
});

jQuery("#a1").click( function(){
	var id = jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		//var ret = jQuery("#newapi'.$param['grid']['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\''.base_url().'formatos/ver/SFPACH/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');		
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});


	function sumamonto()
        { 
		var grid = jQuery("#newapi'.$param['grid']['gridname'].'");
		var s;
		var total = 0;
		var rowcells=new Array();
		s = grid.getGridParam(\'selarrrow\'); 
		$("#totaldep").html("");

		if(s.length)
		{
			for(var i=0;i<s.length;i++)
			{
				var entirerow = grid.jqGrid(\'getRowData\',s[i]);
				total += Number(entirerow["monto"]);
			}
		total = Math.round(total*100)/100;	
		$("#totaldep").html("Bs. "+nformat(total,2));
		$("#montoform").html("Monto: "+nformat(total,2));
		montotal = total;
		}
	};
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));
		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
	<div class="otros">
	<table id="west-grid">
	<tr><td>
		<a style="width:190px" href="#" id="a1">Imprimir Copia</a>
	</td></tr>
	<tr><td>
		<a style="width:190px" href="#" id="depositar">DEPOSITAR</a>
	</td></tr>
	</table>
	</div>
	<div id="totaldep" style="font-size:20px;text-align:center;"></div>
</div> <!-- #LeftPane -->
';

		$mSQL  = "SELECT codbanc, CONCAT(codbanc, ' ', TRIM(banco), IF(tbanco='CAJ',' ',numcuent) ) banco FROM banc WHERE tbanco='CAJ' AND activo='S' ORDER BY codbanc ";
		$cajas = $this->datasis->llenaopciones($mSQL, true, 'envia');

		$mSQL   = "SELECT codbanc, CONCAT(codbanc, ' ', TRIM(banco),' ', IF(tbanco='CAJ',' ',numcuent) ) banco FROM banc WHERE tbanco<>'CAJ' AND activo='S' ORDER BY codbanc ";
		$bancos = $this->datasis->llenaopciones($mSQL, true, 'recibe');


		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->

<div id="deposito-form" title="Enviar a Depositar">
	<p class="validateTips" style="font-size:18px">Indique la caja que envia y la cuenta de banco que recibe.</p>
	<form>
	<fieldset style="border:none;font-size:12px;">
		<label for="caj">Caja</label>
		'.$cajas.'<br><br>
		<label for="banc">Banco</label>
		'.$bancos.'<br><br>
		<div id="montoform" style="font-size:20px;text-align:center"></div>
		
	</fieldset>
	</form>
</div>
';

		$param['WestPanel']  = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;

		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud',$param);
	}	
	
	function defgrid( $deployed = false ){
		$i = 1;

		$grid  = new $this->jqdatagrid;

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array('align'    => "'center'",
							'frozen'   => 'true',
							'width'    => 60,
							'editable' => 'false',
							'search'   => 'false'
			)
		);

		$grid->addField('nombre');
		$grid->label('Nombre Cliente');
		$grid->params(array('width'    => 180,
							'editable' => 'false',
							'edittype' => "'text'"
			)
		);

		$grid->addField('tipo_doc');
		$grid->label('Doc.');
		$grid->params(array(
					'width'    => 30,
					'align'    => "'center'",
					'editable' => 'false',
					'edittype' => "'text'"
			)
		);

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array('align'    => "'center'",
							'width'    => 70,
							'editable' => 'false',
							'edittype' => "'text'"
			)
		);

		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
					'width'       => 80,
					'search'      => 'true',
					'editable'    => 'false',
					'edittype'    => "'text'",
					'editrules'   => '{ required:true,date:true}',
					'formoptions' => '{ label:"Fecha" }'
			)
		);

		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array(
					'align'    => "'center'",
					'width'         => 30,
					'editable'      => 'true',
					'edittype'      => "'select'",
					'editrules'   => '{ required:true }',
					'editoptions'   => '{ dataUrl: "ddtarjeta"}',
					'stype'         => "'text'"
			)
		);

		$grid->addField('num_ref');
		$grid->label('Nro. Cheque');
		$grid->params(array('width'       => 90,
							'editable'    => 'true',
							'edittype'    => "'text'",
							'editrules'   => '{required:true}',
							'editoptions' => '{ size:20, maxlength: 12 }',
			)
		);

		$grid->addField('monto');
		$grid->label('Monto');
		$grid->params(array('width'         => 100,
							'editable'      => 'true',
							'align'         => "'right'",
							'edittype'      => "'text'",
							'search'        => 'true',
							'editrules'     => '{ required:true }',
							'editoptions'   => '{ size:10, maxlength: 10 }',
							'formatter'     => "'number'",
							'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
			)
		);

		$grid->addField('banco');
		$grid->label('Banco');
		$grid->params(array('width'         => 40,
							'hidden'        => 'true',
							'editable'      => 'true',
							'edittype'      => "'select'",
							'editrules'     => '{ edithidden:true, required:true }',
							'editoptions'   => '{ dataUrl: "ddbanco"}',
							'stype'         => "'tsxt'",
			)
		);

		$grid->addField('nombanc');
		$grid->label('Nombre del Banco');
		$grid->params(array('width'         => 140,
							'editable'      => 'false',
							'edittype'      => "'text'",
							'search'        => 'true'
			)
		);

		$grid->addField('cajero');
		$grid->label('Cajero');
		$grid->params(array('width'         => 120,
							'hidden'        => 'true',
							'editable'      => 'false',
							'edittype'      => "'select'",
							'editrules'     => '{ edithidden: true, required:true }',
							'editoptions'   => '{ dataUrl: "ddcajero"}',
							'stype'         => "'select'",
							'searchoptions' => '{ dataUrl: "ddcajero", sopt: ["eq", "ne"]}'
			)
		);

		$grid->addField('nomcajero');
		$grid->label('Nombre Cajero');
		$grid->params(array('width'         => 120,
							'editable'      => 'false',
							'edittype'      => "'text'"
			)
		);

		$grid->addField('us_nombre');
		$grid->label('Nombre de Uusario');
		$grid->params(array('width'     => 140,
							'editable'  => 'false',
							'edittype'  => "'text'",
							'search'    => 'true'
			)
		);

		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array('width'    => 80,
							'search'   => 'false',
							'editable' => 'false',
							'edittype' => "'text'"
			)
		);

		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array('width'     => 60,
							'editable'  => 'false',
							'edittype'  => "'text'",
							'search'    => 'false'
			)
		);

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setMultiSelect(true);
		$grid->setonSelectRow('sumamonto');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){ if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd: true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){ if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');

		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(20);
            
		$grid->setShrinkToFit('false');

		#export buttons

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

	function ddtarjeta(){
		$mSQL = "SELECT tipo, CONCAT(tipo,' ',nombre) nombre FROM tarjeta WHERE activo!='N' AND tipo NOT IN ('EF', 'DE', 'NC','RI','IR','RP')";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function ddbanco(){
		$mSQL = "SELECT cod_banc, CONCAT(cod_banc, ' ', nomb_banc) banco FROM tban WHERE cod_banc<>'CAJ' ORDER BY nomb_banc ";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function ddusuario(){
		$mSQL = "SELECT us_codigo, CONCAT(us_codigo, ' ', us_nombre) us_nombre FROM usuario ORDER BY us_codigo";
		echo $this->datasis->llenaopciones($mSQL, true);
	}

	function ddcajero(){
		$mSQL = "SELECT cajero, CONCAT(cajero, ' ', nombre) nombre FROM scaj ORDER BY nombre";
		echo $this->datasis->llenaopciones($mSQL, true);
	}


	function depositos(){
		// Genera el deposito pendiente
		$envia   = $this->input->get_post('envia');
		$recibe  = $this->input->get_post('recibe');
		$monto   = $this->input->get_post('monto');
		$cheques = $this->input->get_post('ids');
		$fecha   = date('Ymd');
		
		// Revisamos si el monto coincide con la suma
		$mMonto = $this->datasis->dameval("SELECT SUM(monto) FROM sfpa WHERE id IN ( $cheques )");
		if ($monto <> $mMonto) memowrite("Diferencia de monto $monto <> $mMonto");

		$monto = $mMonto;
	
		$mTRANSAC = $this->datasis->prox_sql("ntransa",8);

		$i = 0;
		while ( $i == 0){
			$XNUMERO  =$this->datasis->prox_sql("nbcaj",8);
			if ($this->datasis->dameval("SELECT count(*) FROM bcaj WHERE numero='".$XNUMERO."'") == 0 ){
				$i = 1;
			};
		}

		$XNUMEROE = $this->datasis->banprox($envia);
		$data = array();
		
		$data['fecha']      = $fecha;
		$data['numero']     = $XNUMERO;
		$data['tipo']       = 'DE';
		$data['tarjeta']    = 0;
		$data['tdebito']    = 0;
		$data['cheques']    = $monto;
		$data['efectivo']   = 0;
		$data['comision']   = 0;
		$data['islr']       = 0;
		$data['monto']      = $monto;
		$data['envia']      = $envia;
		$data['bancoe']     = $this->datasis->dameval("SELECT banco FROM banc WHERE codbanc='$envia'");
		$data['tipoe']      = 'ND';
		$data['numeroe']    = $XNUMEROE;
		$data['recibe']     = $recibe;
		$data['bancor']     = $this->datasis->dameval("SELECT banco FROM banc WHERE codbanc='$recibe'");
		$data['tipor']      = 'DE';
		$data['numeror']    = '********';
		$data['concepto']   = "DEPOSITO DESDE CAJA $envia A BANCO $recibe ";
		$data['concep2']    = "CHEQUES";
		$data['status']     = 'P';  // Pendiente/Cerrado/Anulado
		$data['usuario']    = $this->secu->usuario();
		$data['estampa']    = $fecha;
		$data['hora']       = date('H:i:s');
		$data['transac']    = $mTRANSAC;

		//$data['comprob',  xcomprob })
		//$data['benefi',   xbenefi })
		//$data['totcant',  xtotcant })
		//$data['deldia',   xdeldia })

		//Guarda en BCAJ
		$this->db->insert('bcaj', $data);
		$this->datasis->actusal( $envia, $fecha, -$monto );
		
		$mSQL = "UPDATE sfpa SET deposito='$XNUMERO', status='P' WHERE id IN ($cheques)";
		$this->db->simple_query($mSQL);
	
		//GUARDA EN BMOV LA SALIDA DE CAJA
		$data = array();
		
		$data['codbanc']  = $envia;
		$data['numcuent'] = $this->datasis->dameval("SELECT numcuent FROM banc WHERE codbanc='$envia'");
		$data['banco']    = $this->datasis->dameval("SELECT banco    FROM banc WHERE codbanc='$envia'");
		$data['saldo']    = $this->datasis->dameval("SELECT saldo    FROM banc WHERE codbanc='$envia'");
		$data['tipo_op']  = 'ND';
		$data['numero']   = $XNUMEROE;
		$data['fecha']    = $fecha;
		$data['clipro']   = 'O';
		$data['codcp']    = 'CAJAS';
		$data['nombre']   = 'DEPOSITO DESDE CAJA';
		$data['monto']    = $monto;
		$data['concepto'] = "DEPOSITO DESDE CAJA $envia A BANCO $recibe ";
		$data['concep2']  = "";
		$data['benefi']   = "";
		$this->db->insert('bmov', $data);
		
		logusu('BCAJ',"Deposito de cheques de caja Nro. $XNUMERO creada");
		
		echo "{\"numero\":\"$XNUMERO\",\"mensaje\":\"Registro Agregado\"}";

	}


	function ddcaja($tipo=''){
		$mSQL = "SELECT codbanc, CONCAT(codbanc, ' ', TRIM(banco), IF(tbanco='CAJ','',cuenta) ) banco FROM banc ";
		if ( !empty($tipo) ) $mSQL .= " WHERE tbanco='$tipo' ";
		$mSQL .= " ORDER BY codbanc ";
		echo $this->datasis->llenaopciones($mSQL, true);
	}


	/**
	* Get data result as json
	*/
	function getdata()
	{

		$filters = $this->input->get_post('filters');
		$mWHERE = array();

		$grid       = $this->jqdatagrid;
		
		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO

		$valor = $this->input->get_post('nombre');
		if ($valor) $mWHERE[] = array('like', 'nombre', $valor, 'both' );

		$valor = $this->input->get_post('numero');
		if( !empty($valor) ) $valor = str_pad($valor, 8, "0", STR_PAD_LEFT);
		if ($valor) $mWHERE[] = array('like', 'numero', $valor, 'after' );

		$valor = $this->input->get_post('fecha');
		if ($valor) $mWHERE[] = array('', 'fecha', $valor, '' );
			
		$valor = $this->input->get_post('tipo');
		if ($valor) $mWHERE[] = array('', 'tipo', $valor );

		$valor = $this->input->get_post('num_ref');
		if ($valor) $mWHERE[] = array('', 'num_ref', $valor );

		$valor = $this->input->get_post('monto');
		if ($valor) $mWHERE[] = array('', 'monto', $valor+0 );
			
		$valor = $this->input->get_post('nombanc');
		if ($valor) $mWHERE[] = array('like', 'nombanc', $valor, 'both' );

		$valor = $this->input->get_post('nomcajero');
		if ($valor) $mWHERE[] = array('like', 'nomcajero', $valor, 'both' );

		$valor = $this->input->get_post('us_nombre');
		if ($valor) $mWHERE[] = array('like', 'us_nombre', $valor, 'both' );

		$response   = $grid->getData('view_sfpach', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
		//echo $this->db->last_query();
	}

	/**
	* Put information
	*/
	function setData()
	{
		//$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');

		$data = $_POST;
		unset($data['oper']);
		unset($data['id']);
		unset($data['cajero']);
		
		if($oper == 'add'){
			echo 'De este modulo no se puede Agregado';
			return;

		} elseif($oper == 'edit') {
			//REVISA SI DEBE GENERAR MOVIMIENTO EF
			$montoo = $this->datasis->dameval("SELECT monto FROM sfpa WHERE id=$id");
			$dife =    $montoo - $data['monto'];
			if ( round($dife,2) <> 0 ) {
				$query = $this->db->get_where('sfpa', array('id'=>$id) );
				$row = $query->row_array();
				$row['tipo'] = 'EF';
				$row['monto'] = $dife;
				unset($row['id']);
				$this->db->insert('sfpa', $row);
				logusu('SFPA',"Cambia forma de pago: id=$id  monto=$montoo ");
			} else {
				unset($data['monto']);
			}
			$this->db->where('id', $id);
			$this->db->update('sfpa', $data);
			echo 'Registro Guardado ';
			return;

		} elseif($oper == 'del') {
			$this->db->simple_query("DELETE FROM sfpa WHERE id=$id ");
			logusu('sfpa',"Cambio de Cheque $id ELIMINADO");
			echo "Registro Eliminado";
			return;
		}
	}


	function instalar(){
	
	}

}
