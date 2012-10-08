<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
class Sfac extends Controller {
	var $mModulo='SFAC';
	var $titp='Facturacion ';
	var $tits='Facturacion';
	var $url ='ventas/sfac/';

	function Sfac(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		$this->datasis->modintramenu( 1000, 650, 'ventas/sfac' );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$grid1   = $this->defgridit();
		$param['grids'][] = $grid1->deploy();

		// Configura los Paneles
		$readyLayout = $grid->readyLayout2( 212, 220, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"boton1",  "img"=>"images/pdf_logo.gif","alt" => 'Formato PDF',      "label"=>"Reimprimir Documento"));
		$grid->wbotonadd(array("id"=>"boton2",  "img"=>"images/agrega4.png", "alt" => 'Agregar',          "label"=>"Agregar Venta"));
		$grid->wbotonadd(array("id"=>"cobroser","img"=>"images/agrega4.png", "alt" => 'Cobro de Servicio',"label"=>"Cobro de Servicio"));
		$WestPanel = $grid->deploywestp();

		//Panel Central
		$centerpanel = $grid->centerpanel( $id = "radicional", $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		$adic = array(
			array("id"=>"fcobroser", "title"=>"Cobro de servicio")
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']    = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('SFAC', 'JQ');
		$param['otros']        = $this->datasis->otros('SFAC', 'JQ');
		$param['centerpanel']  = $centerpanel;
		//$param['funciones']    = $funciones;
		$param['temas']        = array('proteo','darkness','anexos1');
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;

		$this->load->view('jqgrid/crud2',$param);

	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0, $grid1 ){
		$bodyscript = '
		<script type="text/javascript">
		jQuery("#boton1").click( function(){
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				window.open(\''.site_url('ventas/sfac_add/dataprint/modify').'/\'+id, \'_blank\', \'width=900,height=800,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-450), screeny=((screen.availWidth/2)-400)\');
			} else { $.prompt("<h1>Por favor Seleccione una Factura</h1>");}
		});';

		$bodyscript .= '
		jQuery("#boton2").click( function(){
			window.open(\''.site_url('ventas/sfac_add/dataedit/create').'\', \'_blank\', \'width=900,height=700,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-450), screeny=((screen.availWidth/2)-350)\');
		});';

		//Prepara Pago o Abono
		$bodyscript .= '
			$( "#cobroser" ).click(function() {
				$.post("'.site_url('ventas/sfac/fcobroser').'", function(data){
					$("#fcobroser").html(data);
				});
				$( "#fcobroser" ).dialog( "open" );

			});
			$( "#fcobroser" ).dialog({
				autoOpen: false, height: 400, width: 540, modal: true,
				buttons: {
					"Guardar": function() {
						$.post("'.site_url('ventas/mensualidad/servxmes/insert').'", {cod_cli: $("#fcliente").val(),cana_0: $("#fmespaga").val(),tipo_0: $("#fcodigo").val(),num_ref_0: $("#fcomprob").val(),preca_0: $("#ftarifa").val() },
							function(data) {
								if(data=="Venta Guardada"){
									$("#fcobroser").dialog( "close" );
								}else{
									alert(data);
								}
							}
						);
					},
					Cancel: function() { $( this ).dialog( "close" ); }
				},
				close: function() {
					//allFields.val( "" ).removeClass( "ui-state-error" );
					//alert("Cerrado");
				}
			});
		';
		$bodyscript .= "\n</script>\n";

		return $bodyscript;
	}


	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('tipo_doc');
		$grid->label('Tipo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:3, maxlength: 1 }',
		));


		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 65,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 75,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('vence');
		$grid->label('Vence');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 75,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('vd');
		$grid->label('Vend');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('cod_cli');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('rifci');
		$grid->label('RIF/CI');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 13 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 170,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));

		$grid->addField('referen');
		$grid->label('Ref.');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));

		$grid->addField('totals');
		$grid->label('Sub Total');
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

		$grid->addField('iva');
		$grid->label('I.V.A.');
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

		$grid->addField('totalg');
		$grid->label('Total');
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

/*
		$grid->addField('direc');
		$grid->label('Direc');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));


		$grid->addField('dire1');
		$grid->label('Dire1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));
*/

		$grid->addField('orden');
		$grid->label('Orden');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 10 }',
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



		$grid->addField('status');
		$grid->label('Status');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));

/*
		$grid->addField('observa');
		$grid->label('Observa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 50 }',
		));


		$grid->addField('observ1');
		$grid->label('Observ1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 50 }',
		));
*/

		$grid->addField('devolu');
		$grid->label('Devolu');
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


		$grid->addField('cajero');
		$grid->label('Cajero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('almacen');
		$grid->label('Almacen');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 4 }',
		));


		$grid->addField('peso');
		$grid->label('Peso');
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


		$grid->addField('factura');
		$grid->label('Factura');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

/*
		$grid->addField('pedido');
		$grid->label('Pedido');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));
*/

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
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('transac');
		$grid->label('Transaccion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('nfiscal');
		$grid->label('No Fiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('zona');
		$grid->label('Zona');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('ciudad');
		$grid->label('Ciudad');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
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


		$grid->addField('pagada');
		$grid->label('Pagada');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('sepago');
		$grid->label('Sepago');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));


		$grid->addField('dias');
		$grid->label('Dias');
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

/*
		$grid->addField('fpago');
		$grid->label('Fpago');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('comical');
		$grid->label('Comical');
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


		$grid->addField('notcred');
		$grid->label('Notcred');
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


		$grid->addField('fentrega');
		$grid->label('Fentrega');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('fpagom');
		$grid->label('Fpagom');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('fdespacha');
		$grid->label('Fdespacha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('udespacha');
		$grid->label('Udespacha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('numarma');
		$grid->label('Numarma');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));
*/

		$grid->addField('maqfiscal');
		$grid->label('Maq. Fiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 20 }',
		));


		$grid->addField('dmaqfiscal');
		$grid->label('Dmaqfiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 20 }',
		));

/*
		$grid->addField('nromanual');
		$grid->label('Nromanual');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 14 }',
		));


		$grid->addField('fmanual');
		$grid->label('Fmanual');
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

/*
		$grid->addField('reiva');
		$grid->label('Reiva');
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


		$grid->addField('creiva');
		$grid->label('Creiva');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 20 }',
		));


		$grid->addField('freiva');
		$grid->label('Freiva');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('ereiva');
		$grid->label('Ereiva');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('vexenta');
		$grid->label('Vexenta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
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

/*
		$grid->addField('certificado');
		$grid->label('Certificado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 32 }',
		));


		$grid->addField('sprv');
		$grid->label('Sprv');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('maestra');
		$grid->label('Maestra');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));
*/

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('200');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
				if (id){
					var ret = $("#titulos").getRowData(id);
					jQuery(gridId2).jqGrid(\'setGridParam\',{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					jQuery(gridId2).trigger("reloadGrid");
					$.ajax({
						url: "'.base_url().$this->url.'tabla/"+id,
						success: function(msg){
							$("#ladicional").html(msg);
						}
					});
				}
			}'
		);

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 450, height:150, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 450, height:150, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(false);
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
		$mWHERE = $grid->geneTopWhere('sfac');

		$response   = $grid->getData('sfac', array(array()), array(), false, $mWHERE, 'id', 'desc' );
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
			/*
			if(false == empty($data)){
				$this->db->insert('sfac', $data);
				echo "Registro Agregado";

				logusu('SFAC',"Registro ????? INCLUIDO");
			} else*/
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$this->db->where('id', $id);
			$this->db->update('sfac', $data);
			logusu('SFAC',"Registro $id MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			/*
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM sfac WHERE id=$id ");
				logusu('SFAC',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
			*/
		};
	}


	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

/*
		$grid->addField('tipoa');
		$grid->label('Tipoa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));


		$grid->addField('numa');
		$grid->label('Numa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));
*/

		$grid->addField('codigoa');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 15 }',
		));


		$grid->addField('desca');
		$grid->label('Descripcion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));


		$grid->addField('cana');
		$grid->label('Cantidad');
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


		$grid->addField('preca');
		$grid->label('Precio');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('tota');
		$grid->label('Total');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('iva');
		$grid->label('Iva');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('vendedor');
		$grid->label('Vendedor');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('costo');
		$grid->label('Costo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

/*
		$grid->addField('pos');
		$grid->label('Pos');
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
*/

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


		$grid->addField('cajero');
		$grid->label('Cajero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));

/*
		$grid->addField('mostrado');
		$grid->label('Mostrado');
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
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
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
*/

		$grid->addField('despacha');
		$grid->label('Despacha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));

/*
		$grid->addField('flote');
		$grid->label('Flote');
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

		$grid->addField('pvp');
		$grid->label('Precio 1');
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

		$grid->addField('precio4');
		$grid->label('Precio4');
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

/*
		$grid->addField('detalle');
		$grid->label('Detalle');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:2, cols:60}'",
		));
*/

		$grid->addField('fdespacha');
		$grid->label('Fdespacha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('udespacha');
		$grid->label('Udespacha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));

/*
		$grid->addField('combo');
		$grid->label('Combo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 15 }',
		));


		$grid->addField('descuento');
		$grid->label('Descuento');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('bonifica');
		$grid->label('Bonifica');
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

/*
		$grid->addField('id_sfac');
		$grid->label('Id_sfac');
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
*/

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('190');
		//$grid->setTitle($this->titp);
		$grid->setfilterToolbar(false);
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
		$grid->setUrlput(site_url($this->url.'setdatait/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdatait/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdatait()
	{
		$id = $this->uri->segment(4);
		if ($id === false ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM sfac");
		}
		if(empty($id)) return '';
		$tipo_doc = $this->datasis->dameval("SELECT tipo_doc FROM sfac WHERE id=$id");
		$numero   = $this->datasis->dameval("SELECT numero   FROM sfac WHERE id=$id");

		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT * FROM sitems WHERE tipoa='$tipo_doc' AND numa='$numero' ";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setDatait()
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
				$this->db->insert('sitems', $data);
				echo "Registro Agregado";

				logusu('SITEMS',"Registro ????? INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			//unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('sitems', $data);
			logusu('SITEMS',"Registro ????? MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sitems WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM sitems WHERE id=$id ");
				logusu('SITEMS',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
*/
	}


	//*********************************************************
	// Forma de Abono
	//
	function fcobroser(){
		$mSQL    = "SELECT tipo, CONCAT(tipo, ' ', nombre) descrip FROM tarjeta WHERE tipo NOT IN ('DE','NC','IR') ORDER BY tipo ";
		$tarjeta = $this->datasis->llenaopciones($mSQL, true, 'fcodigo');


		//$id      = $this->uri->segment($this->uri->total_segments());
		//$proveed = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=$id");

		//$reg = $this->datasis->damereg("SELECT proveed, nombre, rif FROM sprv WHERE id=$id");



		$salida = '
<script type="text/javascript">
	$( "#fcliente" ).autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "'.site_url('ajax/buscascliser').'",
				type: "POST",
				dataType: "json",
				data: "q="+req.term,
				success:
					function(data){
						var sugiere = [];
						if(data.length==0){
							$("#fnombre").val("");
							$("#fdire11").val("");
							$("#ftelefono").val("");
							$("#ftarifa").val("");
							$("#fupago").val("");
						}else{
							$.each(data,
								function(i, val){
									sugiere.push( val );
								}
							);
						}
						add(sugiere);
					},
			})
		},
		minLength: 2,
		select: function( event, ui ) {
			$("#fnombre").val(ui.item.nombre);
			$("#ftelefono").val(ui.item.telefono);
			$("#ftarifa").val(ui.item.precio1);
			$("#fcodtar").val(ui.item.codigo);
			$("#fdire11").val(ui.item.direc);
			$("#fupago").val(ui.item.upago);
		}
	});
</script>
	<div style="background-color:#D0D0D0;font-weight:bold;font-size:14px;text-align:center"><table width="100%"><tr><td>Cobro de Servicios Mensuales</td><td></td><td> </td></tr></table></div>
	<p class="validateTips"></p>
	<form id="formcobroser">
	<fieldset style="border: 2px outset #9AC8DA;background: #FFFDE9;">
	<table width="90%" align="center" border="0">
	<tr>
		<td class="CaptionTD" align="right">Cliente: </td>
		<td>&nbsp;<input name="fcliente" id="fcliente" type="text" value="" maxlengh="12" size="12" /></td>
		<td class="CaptionTD" align="right">Telefono: </td>
		<td>&nbsp;<input name="ftelefono" id="ftelefono" type="text" value="" maxlengh="12" size="12" /></td>
	</tr>
	<tr>
		<td class="CaptionTD" align="right">Nombre: </td>
		<td colspan="3">&nbsp;<input name="fnombre" id="fnombre" value="" size="50" ></td>
	</tr>
	<tr>
		<td class="CaptionTD" align="right">Direccion: </td>
		<td colspan="3">&nbsp;<input name="fdire11" id="fdire11" value="" size="50"></td>
	</tr>
	<tr>
		<td class="CaptionTD" align="right">&nbsp;</td>
		<td colspan="3">&nbsp;<input name="fdire12" id="fdire12" value="" size="50"></td>
	</tr>
	</table>

	</fieldset>
	<fieldset style="border: 2px outset #9AC8DA;background: #FFFDE9;">
	<table width="90%" align="center" border="0">
	<tr>
		<td class="CaptionTD" align="right">Ultimo Pago: </td>
		<td>&nbsp;<input name="fupago" id="fupago" type="text" value="201112" maxlengh="12" size="8" /></td>
		<td  class="CaptionTD"  align="right">Tarifa</td>
		<td>&nbsp;<input name="fcodtar" id="fcodtar" type="text" value="" maxlengh="12" size="15"  /></td>
		<td  class="CaptionTD"  align="right">Monto</td>
		<td>&nbsp;<input name="ftarifa" id="ftarifa" type="text" value="" maxlengh="12" size="12"  /></td>
	</tr>
	</table>
	</fieldset>

	</fieldset>
	<fieldset style="border: 2px outset #9AC8DA;background: #FFFDE9;">
	<table width="90%" align="center" border="0">
	<tr>
		<td class="CaptionTD" align="right">Nro de meses que paga: </td>
		<td>&nbsp;<input name="fmespaga" id="fmespaga" type="text" value="12" maxlengh="12" size="8" /></td>
	</tr>
	</table>
	</fieldset>


	<fieldset style="border: 2px outset #9AC8DA;background: #FFFDE9;">
	<table width="90%" align="center" border="0">
	<tr>
		<td class="CaptionTD" align="right">Forma de Pago</td>
		<td>&nbsp;'.$tarjeta.'</td>
		<td  class="CaptionTD"  align="right">Numero</td>
		<td>&nbsp;<input name="fcomprob" id="fcomprob" type="text" value="" maxlengh="12" size="12"  /></td>
	</tr>
	</table>
	</fieldset>

	<input id="fmonto"   name="fmonto"   type="hidden">
	<input id="fsele"    name="fsele"    type="hidden">
	<input id="fid"      name="fid"      type="hidden" value="">
	<input id="fgrid"    name="fgrid"    type="hidden">
	<br>
	<center><table id="abonados"><table></center>
	<table width="100%">
	<tr>
		<td align="center"><div id="grantotal" style="font-size:20px;font-weight:bold">Monto a pagar: 0.00</div></td>
	</tr>
	</table>
	</form>
';


		echo $salida;
	}




/*
class sfac extends validaciones {

	function sfac(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(103,1);
		$this->instalar();
	}

	function index() {
		//redirect('ventas/sfac/filteredgrid');
		$this->sfacextjs();
	}

	function filteredgrid(){
		$this->rapyd->load('datagrid','datafilter');

		$atts = array(
			'width'      => '800',
			'height'     => '600',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '0',
			'screeny'    => '0'
		);

		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');
		$boton=$this->datasis->modbus($scli);

		$filter = new DataFilter('Filtro de Facturas');
		$filter->db->select(array('fecha','numero','cod_cli','nombre','totals','totalg','iva','tipo_doc','exento', 'IF(referen="C","Credito",IF(referen="E","Contado","Pendiente")) referen','IF(tipo_doc="X","N","S") nulo','almacen','vd','usuario', 'hora', 'estampa','nfiscal','cajero', 'transac','maqfiscal', 'factura' ,'id'));
		$filter->db->from('sfac');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechad->clause  = 'where';
		$filter->fechad->db_name = 'fecha';
		//$filter->fechad->insertValue = date('Y-m-d');
		$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechad->group = '1';

		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechah->clause = 'where';
		$filter->fechah->db_name='fecha';
		//$filter->fechah->insertValue = date('Y-m-d');
		$filter->fechah->size=10;
		$filter->fechah->operator='<=';
		$filter->fechah->group = '1';

		$filter->referen = new  dropdownField ('Condici&oacute;n', 'referen');
		$filter->referen->option('' ,'Todos');
		$filter->referen->option('E','Contado');
		$filter->referen->option('C','Cr&eacute;dito');
		$filter->referen->style='width:150px;';
		$filter->referen->operator='=';
		$filter->referen->clause  = 'where';
		$filter->referen->group = '1';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 20;
		$filter->numero->group = '2';

		$filter->cliente = new inputField('Cliente', 'cod_cli');
		$filter->cliente->size = 20;
		$filter->cliente->append($boton);
		$filter->cliente->group = '2';

		$filter->buttons('reset','search');
		$filter->build("dataformfiltro");

		$uri = anchor('ventas/sfac/dataedit/show/<#id#>','<#tipo_doc#><#numero#>');
		$uri2  = anchor('ventas/sfac/dataedit/show/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar')));
		$uri2 .= "&nbsp;";
		$uri2 .= anchor('formatos/ver2/FACTURA/<#tipo_doc#>/<#numero#>',img(array('src'=>'images/pdf_logo.gif','border'=>'0','alt'=>'PDF')));
		$uri2 .= "&nbsp;";
		$uri2 .= anchor('formatos/verhtml/FACTURA/<#tipo_doc#>/<#numero#>',img(array('src'=>'images/html_icon.gif','border'=>'0','alt'=>'HTML')));
		$uri2 .= "&nbsp;";
		$uri2 .= img(array('src'=>'images/<#nulo#>.gif','border'=>'0','alt'=>'Estado','title'=>'Estado'));

		$uri_3  = "<a href='javascript:void(0);' onclick='javascript:nfiscal(\"<#id#>\")'>";
		$propiedad = array('src' => 'images/engrana.png', 'alt' => 'Modifica Nro de Control', 'title' => 'Modifica Nro. de Control','border'=>'0','height'=>'12');
		$uri_3 .= img($propiedad);
		$uri_3 .= "</a>";

		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."ventas/sfac_add/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."reportes/index/sfac', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600" width="900" '.'>';
		$mtool .= img(array('src' => 'images/reportes.gif', 'alt' => 'Reportes', 'title' => 'Reportes','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";


		$grid = new DataGrid($mtool);
		$grid->order_by('fecha','desc');
		$grid->per_page = 50;

		$grid->column('Acciones',$uri2);
		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha',    '<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Cliente',  'cod_cli',  'cod_cli');
		$grid->column_orderby('Nombre',   'nombre',   'nombre');
		$grid->column_orderby('Almacen',  'almacen',  'almacen');
		$grid->column_orderby('Sub.Total','<nformat><#totals#></nformat>','totals','align=\'right\'');
		$grid->column_orderby('IVA',      '<nformat><#iva#></nformat>'   ,'iva',   'align=\'right\'');
		$grid->column_orderby('Total',    '<nformat><#totalg#></nformat>','totalg','align=\'right\'');
		$grid->column_orderby('Exento',   '<nformat><#exento#></nformat>','totalg','align=\'right\'');
		$grid->column_orderby('Tipo',     'referen',  'referen','align=\'left\'');
		$grid->column_orderby('N.Fiscal',  $uri_3.'<#nfiscal#>', 'nfiscal' );
		$grid->column_orderby('M.Fiscal', 'maqfiscal','maqfiscal','align=\'left\'');
		$grid->column_orderby('Vende',    'vd',       'vd');
		$grid->column_orderby('Cajero',   'cajero',   'cajero');
		$grid->column_orderby('Usuario',  'usuario',  'nfiscal','align=\'left\'');
		$grid->column_orderby('Hora',     'hora',     'hora',   'align=\'center\'');
		$grid->column_orderby('Transac',  'transac',  'transac','align=\'left\'');
		$grid->column_orderby('Afecta',   'factura',  'factura','align=\'left\'');
		$grid->column_orderby('I.D.',     'id',       'id',     'align=\'right\'');

		$grid->build('datagridST');
		//echo $grid->db->last_query();

// Para usar SuperTable
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
		headerRows : 1,
		onStart : function () {
		this.start = new Date();
		},
		onFinish : function () {
		document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";
		}
	});
})();
//]]>
</script>
';

		$style ='
<style type="text/css">
.fakeContainer {          // The parent container
	margin: 5px;
	padding: 0px;
	border: none;
	width: 640px;     // Required to set
	height: 320px;    // Required to set
	overflow: hidden; // Required to set
}
</style>
';

$script ='
<script type="text/javascript">
function nfiscal(mid){
	jPrompt("Numero de Serie","" ,"Cambio de Nro.Fiscal", function(mserie){
		if( mserie==null){
			jAlert("Cancelado","Informacion");
		} else {
			$.ajax({ url: "'.site_url().'ventas/sfac/nfiscal/"+mid+"/"+mserie,
				success: function(msg){
					jAlert("Cambio Finalizado "+msg,"Informacion");
					location.reload();
					}
			});
		}
	})
}
</script>';


$sigma = "";

		//$data['content']  = $mtool;
		$data['content'] = $grid->output;

		$data['filtro']  = $filter->output;

		$data['script']  = script('jquery.js');
		$data["script"] .= script("jquery.alerts.js");
		$data['script'] .= script('superTables.js');
		$data['script'] .= $script;

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['style']	.= style("jquery.alerts.css");

		$data['extras']  = $extras;

		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = heading('Facturas');
		$this->load->view('view_ventanas', $data);
	}

	//cambio del Nro Fiscal
	function nfiscal() {
		$nfiscal   = $this->uri->segment($this->uri->total_segments());
		$mid = $this->uri->segment($this->uri->total_segments()-1);
		if (!empty($nfiscal)) {
			$this->db->simple_query("UPDATE sfac SET nfiscal='$nfiscal' WHERE id='$mid'");
			echo " con exito ";
		} else {
			echo " NO se guardo ";
		}
		logusu('SFAC',"Cambia Nro. Fiscal $mid ->  $nfiscal ");

	}
*/

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigoa_<#i#>',
				'descrip'=>'desca_<#i#>',
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n',
			'tipo'=>'Tipo'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','rifci'=>'rifci',
						  'dire11'=>'direc','tipo'=>'sclitipo'),
		'titulo'  =>'Buscar Cliente',
		);
		$boton =$this->datasis->modbus($mSCLId);

		$do = new DataObject('sfac');
		$do->rel_one_to_many('sitems', 'sitems', array('numero'=>'numa','tipo_doc'=>'tipoa'));
		$do->rel_one_to_many('sfpa', 'sfpa', array('numero','transac'));

		$edit = new DataDetails('Facturas', $do);
		$edit->back_url = site_url('ventas/sfac/filteredgrid');
		$edit->set_rel_title('sitems','Producto <#o#>');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->tipo_doc = new  dropdownField ('Documento', 'tipo_doc');
		$edit->tipo_doc->option('F','Factura');
		$edit->tipo_doc->option('D','Devoluci&oacute;n');
		$edit->tipo_doc->style='width:200px;';
		$edit->tipo_doc->size = 5;

		$edit->vd = new  dropdownField ('Vendedor', 'vd');
		$edit->vd->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vd->style='width:200px;';
		$edit->vd->size = 5;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->rifci   = new inputField('RIF/CI','rifci');
		$edit->rifci->autocomplete=false;
		$edit->rifci->size = 15;

		$edit->direc = new inputField('Direcci&oacute;n','direc');
		$edit->direc->size = 40;

		//************************************************
		//  Campos para el detalle 1 sitems
		//************************************************
		$edit->codigoa = new inputField('C&oacute;digo <#o#>', 'codigoa_<#i#>');
		$edit->codigoa->size     = 12;
		$edit->codigoa->db_name  = 'codigoa';
		$edit->codigoa->readonly = true;
		$edit->codigoa->rel_id   = 'sitems';
		$edit->codigoa->rule     = 'required';

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=36;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=50;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='sitems';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'sitems';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive';
		$edit->cana->autocomplete=false;

		$edit->preca = new inputField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'sitems';
		$edit->preca->size      = 10;
		$edit->preca->rule      = 'required|positive';
		$edit->preca->readonly  = true;

		$edit->tota = new inputField('Importe <#o#>', 'tota_<#i#>');
		$edit->tota->db_name='tota';
		$edit->tota->size=10;
		$edit->tota->css_class='inputnum';
		$edit->tota->rel_id   ='sitems';

		$edit->pond = new hiddenField('', "pond_<#i#>");
		$edit->pond->db_name='pond';
		$edit->pond->rel_id   ='sitems';

		//************************************************
		//Fin de campos para detalle,inicio detalle2 sfpa
		//************************************************
		$edit->tipo = new inputField('Tipo <#o#>', 'tipo_<#i#>');
		$edit->tipo->size     = 12;
		$edit->tipo->db_name  = 'tipo';
		$edit->tipo->readonly = true;
		$edit->tipo->rel_id   = 'sfpa';
		$edit->tipo->rule     = 'required';

		$edit->numref = new inputField('Numero <#o#>', 'numero_<#i#>');
		$edit->numref->size     = 12;
		$edit->numref->db_name  = 'numref';
		$edit->numref->readonly = true;
		$edit->numref->rel_id   = 'sfpa';
		$edit->numref->rule     = 'required';


		$edit->monto = new inputField('Monto <#o#>', 'monto_<#i#>');
		$edit->monto->db_name   = 'monto';
		$edit->monto->css_class = 'inputnum';
		$edit->monto->rel_id    = 'sfpa';
		$edit->monto->size      = 10;
		$edit->monto->rule      = 'required|positive';
		$edit->monto->readonly  = true;

		$edit->banco = new inputField('Banco <#o#>', 'banco_<#i#>');
		$edit->banco->size=36;
		$edit->banco->db_name='banco';
		$edit->banco->maxlength=50;
		$edit->banco->readonly  = true;
		$edit->banco->rel_id='sfpa';

		//************************************************
		//Fin detalle 2
		//************************************************

		$edit->ivat = new inputField('I.V.A', 'iva');
		$edit->ivat->css_class ='inputnum';
		$edit->ivat->readonly  =true;
		$edit->ivat->size      = 10;

		$edit->totals = new inputField('Sub-Total', 'totals');
		$edit->totals->css_class ='inputnum';
		$edit->totals->readonly  =true;
		$edit->totals->size      = 10;

		$edit->totalg = new inputField('Total', 'totalg');
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->readonly  =true;
		$edit->totalg->size      = 10;

		$edit->observa   = new inputField('Observacion', 'observa');
		$edit->nfiscal   = new inputField('No.Fiscal', 'nfiscal');
		$edit->observ1   = new inputField('Observacion', 'observ1');
		$edit->zona      = new inputField('Zona', 'zona');
		$edit->ciudad    = new inputField('Ciudad', 'ciudad');
		$edit->exento    = new inputField('Exento', 'exento');
		$edit->maqfiscal = new inputField('Mq.Fiscal', 'maqfiscal');
		$edit->cajero    = new inputField('Cajero', 'cajero');
		$edit->referen   = new inputField('Referencia', 'referen');
		$edit->transac   = new inputField('Transaccion', 'transac');
		$edit->vence     = new inputField('Vence', 'vence');

		$edit->reiva     = new inputField('Retencion de IVA', 'reiva');
		$edit->creiva    = new inputField('Comprobante', 'creiva');
		$edit->freiva    = new inputField('Fecha', 'freiva');
		$edit->ereiva    = new inputField('Emision', 'ereiva');

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('delete', 'back','add_rel');
		$edit->build();

		$style = '
			<style type="text/css">
			.maintabcontainer {width: 780px; margin: 5px auto;}
			div#sfacreiva label { display:block; }
			div#sfacreiva input { display:block; }
			div#sfacreiva input.text { margin-bottom:12px; width:95%; padding: .4em; }
			div#sfacreiva select { display:block; }
			div#sfacreiva select.text { margin-bottom:12px; width:95%; padding: .4em; }
			div#sfacreiva fieldset { padding:0; border:0; margin-top:20px; }
			div#sfacreiva h1 { font-size: 1.2em; margin: .6em 0; }
			.ui-dialog .ui-state-error { padding: .3em; }
			.validateTips { border: 1px solid transparent; padding: 0.3em; }
			</style>
			';

		$mreiva = round($edit->ivat->value*0.75,2);
		if( $edit->_dataobject->get('reiva') > 0 )  $mreiva = $edit->_dataobject->get('reiva');

		$fecha = date('d/m/Y');
		if( $edit->_dataobject->get('freiva') > 0 )  $fecha = dbdate_to_human($edit->_dataobject->get('freiva'));

		$efecha = date('d/m/Y');
		if( $edit->_dataobject->get('ereiva') > 0 )  $efecha = dbdate_to_human($edit->_dataobject->get('ereiva'));

		$nro = date('Ym');
		if( $edit->_dataobject->get('creiva') > 0 )  $nro = $edit->_dataobject->get('creiva');
		$reiva = '';

		$conten['form']  =&  $edit;
		if($edit->_status=='show'){
			$data['content']  = $this->load->view('view_sfac', $conten,true);
		}else{
			$data['content']  = $this->load->view('view_sfac_add', $conten,true);
		}
		//$data['content'] .= $reiva;

		if($edit->tipo_doc->value=='F'){$mDoc = "Factura";}
		elseif( $edit->tipo_doc->value=='D') { $mDoc = "Devolucion";}
		else { $mDoc = "Anulado";}

		$mBancos = '<option>__ Reintegrar en otro momento</option>';
		$query = $this->db->query("SELECT TRIM(CONCAT(codbanc,' ',banco)) banco FROM banc WHERE activo='S' AND codbanc<>'DF' ORDER BY tbanco='CAJ' DESC, codbanc");
		foreach($query->result() as $row ){
			$mBancos .= '<option>'.$row->banco.'</option>';
		}

		$link40 = base_url()."/ventas/sfac/sfacreiva/".$edit->_dataobject->get('id');
		$script = "<script type=\"text/javascript\" >
		$(function() {
			$( \"#maintabcontainer\" ).tabs();
		});

		<!-- All the scripts will go here  -->

		var dsOption= {
			fields :[
				{name : 'codigoa'},
				{name : 'desca'  },
				{name : 'cana',		type: 'float' },
				{name : 'preca',	type: 'float' },
				{name : 'tota',		type: 'float' },
				{name : 'iva',		type: 'float' },
				{name : 'pvp',		type: 'float' },
				{name : 'descuento',	type: 'float' },
				{name : 'precio4',	type: 'float' },
				{name : 'detalle' },
				{name : 'fdespacha',	type: 'date'  },
				{name : 'udespacha' },
				{name : 'bonifica',	type: 'integer' },
				{name : 'url' }
			],
			recordType : 'object'
		}

		function codigoaurl( value, record, columnObj, grid, colNo, rowNo ) {
			var no=  value;
			var url= '';
			url = '<a href=\"#\" onclick=\"window.open(\'".base_url()."inventario/sinv/dataedit/show/'+grid.getCellValue(13,rowNo)+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
			url = url +no+'</a>';
			return url;
		}

		var colsOption = [
			{id: 'codigoa',		header: 'Codigo',	width :100, frozen: true, renderer:codigoaurl },
			{id: 'desca',		header: 'Descripcion',	width :340, align: 'left' },
			{id: 'cana',		header: 'Cant',		width :60, align: 'right' },
			{id: 'preca',		header: 'Precio',	width :90, align: 'right' },
			{id: 'tota',		header: 'Total',	width :90, align: 'right' },
			{id: 'iva',		header: 'IVA',		width :50, align: 'right' },
			{id: 'pvp',		header: 'PVP',		width :80, align: 'right' },
			{id: 'descuento',	header: 'Desc%',	width :80, align: 'right' },
			{id: 'precio4',		header: 'Control',	width :80, align: 'right' },
			{id: 'detalle',		header: 'Detalle',	width :80, align: 'right' },
			{id: 'fdespacha',	header: 'Despacha',	width :80, align: 'center' },
			{id: 'udespacha',	header: 'Usuario D',	width :80, align: 'left' },
			{id: 'bonifica',	header: 'Bonifica',	width :80, align: 'right' },
			{id: 'url',	header: 'Id',	width :80, align: 'right' }
		];

		var gridOption={
			id : 'grid1',
			loadURL : '".base_url()."ventas/sfac/sfacsitems/".$edit->_dataobject->get("tipo_doc")."/".$edit->numero->value."',
			container : 'grid1_container',
			dataset : dsOption ,
			columns : colsOption,
			allowCustomSkin: true,
			skin: 'vista',
			toolbarContent: 'pdf'
		};

		var mygrid=new Sigma.Grid(gridOption);
		Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );
		</script> ";

		if ( $edit->referen->value == 'E') {
			$saldo = 0;
		} else {
			$saldo = $this->datasis->dameval("SELECT monto-abonos FROM smov WHERE tipo_doc IN ('FC','NC') AND transac='".$edit->transac->value."'") ;
		}

		if ( $edit->reiva->value > 0 ) {

		$scriptreiva = "<script type=\"text/javascript\" >
		// Retencin de IVA
		function sfacreiva(mid){
			var pide = '';
			pide += '<h3>RETENCION DE IVA YA APLICADA</h3>';
			pide += '<table>';
			pide += '<tr><td>Comprobante </td><td><input type=\"text\" readonly size=\"20\" value=\"".$nro."\"    name=\"numero\" id=\"numero\" /></td></tr>';
			pide += '<tr><td>Recepcion   </td><td>';
			pide += '<input type=\"text\" size=\"10\" readonly value=\"".$fecha."\"  name=\"fecha\"  id=\"fecha\" /></td></tr>';
			pide += '<tr><td>Emision</td><td><input type=\"text\" size=\"10\" readonly value=\"".$efecha."\" name=\"efecha\" id=\"efecha\" /></td></tr>';
			pide += '<tr><td>Monto </td><td><input readonly type=\"text\" size=\"10\" value=\"".$mreiva."\" name=\"reiva\"  id=\"reiva\" style=\"text-align:right\" /></td></tr>';
			pide += '</table>';
			$.prompt(pide, {prefix:'cleanblue'} );
		};
		</script>";

		} else {
		$scriptreiva = "<script type=\"text/javascript\" >
// Retencin de IVA
function sfacreiva(mid){
	var pide = '';
	pide += '<h3>RETENCION DE IVA</h3>';
	pide += '<table>';
	pide += '<tr><td>Comprobante</td><td><input type=\"text\" size=\"20\" value=\"".$nro."\"    name=\"numero\" id=\"numero\" /></td></tr>';
	pide += '<tr><td>Recepcion   </td><td>';
	pide += '<input type=\"text\" size=\"10\" value=\"".$fecha."\"  name=\"fecha\"  id=\"fecha\"  />';
	pide += '<img src=\"".site_url("system/application/rapyd/libraries/jscalendar/calender_icon.gif")."\" id=\"fecha_button\" style=\"vertical-align: middle;\" border=\"0\">';
	pide += '</td></tr>';
	pide += '<tr><td>Emision</td><td><input type=\"text\" size=\"10\" value=\"".$efecha."\" name=\"efecha\" id=\"efecha\" />';
	pide += '<img src=\"".site_url("system/application/rapyd/libraries/jscalendar/calender_icon.gif")."\" id=\"efecha_button\" style=\"vertical-align: middle;\" border=\"0\"></td></tr>';
	pide += '<tr><td>Monto </td><td><input readonly type=\"text\" size=\"10\" value=\"".$mreiva."\" name=\"reiva\"  id=\"reiva\" style=\"text-align:right\" /></td></tr>';";

		if ( $saldo < $mreiva ) {
			$scriptreiva .= "pide += '<tr><td>Reintegrar</td><td><input type=\"checkbox\"  value=\"reintegrar\" name=\"reinte\"  id=\"reinte\" style=\"text-align:right\" /></td></tr>';";
		} else {
			$scriptreiva .= "pide += '<tr><td colspan=2>Se aplicara una NC a la Factura<input type=\"checkbox\" value=\"reintegrar\" name=\"reinte\"  id=\"reinte\" style=\"text-align:right;visibility:hidden;\" /></td></tr>';";
		}

		$scriptreiva .= "
			pide += '</table>';

			pide1 = '<h3>REINTEGRO DE RETENCION EN EFECTIVO</h3>';
			pide1 += '<table>';
			pide1 += '<tr><td>Caja/Banco</td><td>';
			pide1 += '<select name=\"caja\" id=\"caja\">".$mBancos."</select>';
			pide1 += '</td></tr>';
			pide1 += '<tr><td>Cheque</td><td><input type=\"text\" size=\"20\" value=\"\"    name=\"cheque\" id=\"cheque\" /></td></tr>';
			pide1 += '<tr><td>Beneficiario</td><td><input type=\"text\" size=\"40\" value=\"".$edit->nombre->value."\" name=\"benefi\" id=\"benefi\" /></td></tr>';
			pide1 += '</table>';

			var mfecha  = '';
			var mefecha = '';
			var mnumero = '';
			var mmonto  = '';
			var mbanco  = '';
			var mcaja   = '';
			var mcheque = '';
			var mbenefi = '';

			var temp = {
					state0: {
						html:pide,
						buttons: { Cancelar: 0,  Siguiente: 2 },
						focus: 1,
						submit:function(v,m,f){
							mfecha = f.fecha;
							mfecha = mfecha.replace(/\//g,'-');
							mefecha = f.efecha;
							mefecha = mefecha.replace(/\//g,'-');
							mnumero = f.numero;
							mmonto = f.reiva;
							if( v == 0 ){
								return true;
							} else if ( v == 2) {
								if ( f.reinte == 'reintegrar' )
									$.prompt.goToState('state1');//go forward
								else {
									var mtemp = '';
									mtemp += '<table>';
									mtemp += '<tr><td>Comprobante </td><td>'+mnumero+'</td></tr>';
									mtemp += '<tr><td>Recepcion </td><td>'+mfecha+'</td></tr>';
									mtemp += '<tr><td>Emision   </td><td>'+mefecha+'</td></tr>';
									mtemp += '<tr><td>Monto     </td><td>'+mmonto+'</td></tr>';
									mtemp +='</table>';

									$.prompt(mtemp, {
										buttons: {Guardar: true, Salir: false},
										callback: function(v,m,f) {
											if ( v ) {
												$.ajax({
													url: '".base_url()."ventas/sfac/sfacreiva/'+mid+'/'+mnumero+'/'+mfecha+'/'+mefecha+'/'+mmonto,
													global: false,
													async: false,
													success: function(sino) {
														$.prompt(sino);
													}
												});
											} else {
												$.prompt.close();
											}}
									});
									return true;
								}
							}
							return false;
						}
					},
					state1: {
						html: pide1,
						buttons: { Volver: -1, Salir: 0, Guardar: 1 },
						focus: 2,
						submit:function(v,m,f){
							mcaja   = f.caja;
							mcheque = f.cheque;
							mbenefi = f.benefi;
							if(v==0)
								$.prompt.close()
							else if(v==1) {
								var mtempo = '';
								mtempo += '<table>';
								mtempo += '<tr><td>Comprobante </td><td>'+mnumero+'</td></tr>';
								mtempo += '<tr><td>Recepcion </td><td>'+mfecha+'</td></tr>';
								mtempo += '<tr><td>Emision   </td><td>'+mefecha+'</td></tr>';
								mtempo += '<tr><td>Monto     </td><td>'+mmonto+'</td></tr>';
								mtempo += '<tr><td>Caja     </td><td>'+mcaja+'</td></tr>';
								mtempo += '<tr><td>Cheque     </td><td>'+mcheque+'</td></tr>';
								mtempo += '<tr><td>Beneficiario</td><td>'+mbenefi+'</td></tr>';
								mtempo +='</table>';

								$.prompt(mtempo, {
									buttons: {Guardar: 1, Salir: 0},
									callback: function(v,m,f) {
									if ( v == 1 ) {
										$.ajax({
											url: '".base_url()."ventas/sfac/sfacreivaef/'+mid,
											global: false,
											type: 'POST',
											data: ({ numero : encodeURIComponent(mnumero),
												fecha  : encodeURIComponent(mfecha),
												efecha : encodeURIComponent(mefecha),
												caja   : encodeURIComponent(mcaja),
												cheque : encodeURIComponent(mcheque),
												benefi : encodeURIComponent(mbenefi),
												}),
											dataType: 'text',
											async: false,
											success: function(sino) {
												$.prompt(sino);
											}
										});
									}}
								});
								return true;

							} else if(v=-1)
								$.prompt.goToState('state0');//go back
							return false;
						}
					},
					state2: {
						html:'Desea Guardar los Cambios.....',
						buttons: { Volver: true, Guardar: false },
						submit:function(v,m,f){
							if(!v){
								$.prompt('fecha='+mfecha);
								return true;
								}
							else $.prompt.goToState('state0');//go back
							return false;
						}
					}
			};
			$.prompt(temp);
		};
		</script>";
		}

		$data['title']   = heading($mDoc." Nro. ".$edit->numero->value);

		$data['style']   = style("redmond/jquery-ui.css");
		$data['style']  .= style('gt_grid.css');
		$data['style']	.= style("impromptu.css");
		$data['style']	.= $style;

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data["script"] .= script("jquery-impromptu.js");
		$data["script"] .= script("plugins/jquery.blockUI.js");
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= phpscript('nformat.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script("gt_msg_en.js");
		$data['script'] .= script("gt_grid_all.js");
		$data['script'] .= $script;
		$data['script'] .= $scriptreiva;

		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	//********************************************
	//
	// json para llena la tabla de inventario
	//
	function sfacsitems() {
		$numa  = $this->uri->segment($this->uri->total_segments());
		$tipoa = $this->uri->segment($this->uri->total_segments()-1);

		$mSQL  = 'SELECT a.codigoa, a.desca, a.cana, a.preca, a.tota, a.iva, IF(a.pvp < a.preca, a.preca, a.pvp)  pvp, ROUND(100-a.preca*100/IF(a.pvp<a.preca,a.preca, a.pvp),2) descuento, ROUND(100-ROUND(a.precio4*100/(100+a.iva),2)*100/a.preca,2) precio4, a.detalle, a.fdespacha, a.udespacha, a.bonifica, b.id url ';
		$mSQL .= "FROM sitems a LEFT JOIN sinv b ON a.codigoa=b.codigo WHERE a.tipoa='$tipoa' AND a.numa='$numa' ";
		$mSQL .= "ORDER BY a.codigoa";

		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$retArray = array();
			foreach( $query->result_array() as  $row ) {
				$retArray[] = $row;
			}
			$data = json_encode($retArray);
			$ret = "{data:" . $data .",\n";
			$ret .= "recordType : 'array'}";
		} else {
			$ret = '{data : []}';
		}
		echo $ret;
	}

	//***************************
	//
	// Recibir retencin de IVA
	//
	function sfacreiva(){
		$reinte = $this->uri->segment($this->uri->total_segments());
		$efecha = $this->uri->segment($this->uri->total_segments()-1);
		$fecha  = $this->uri->segment($this->uri->total_segments()-2);
		$numero = $this->uri->segment($this->uri->total_segments()-3);
		$id     = $this->uri->segment($this->uri->total_segments()-4);
		$mdevo  = "Exito";

		//memowrite("efecha=$efecha, fecha=$fecha, numero=$numero, id=$id, reinte=$reinte","sfacreiva");

		// status de la factura
		$fecha  = substr($fecha, 6,4).substr($fecha, 3,2).substr($fecha, 0,2);
		$efecha = substr($efecha,6,4).substr($efecha,3,2).substr($efecha,0,2);

		$tipo_doc = $this->datasis->dameval("SELECT tipo_doc FROM sfac WHERE id=$id");
		$referen  = $this->datasis->dameval("SELECT referen  FROM sfac WHERE id=$id");
		$numfac   = $this->datasis->dameval("SELECT numero   FROM sfac WHERE id=$id");
		$cod_cli  = $this->datasis->dameval("SELECT cod_cli  FROM sfac WHERE id=$id");
		$monto    = $this->datasis->dameval("SELECT ROUND(iva*0.75,2)  FROM sfac WHERE id=$id");
		$factura  = $this->datasis->dameval("SELECT factura  FROM sfac WHERE id=$id");

		$anterior = $this->datasis->dameval("SELECT reiva FROM sfac WHERE id=$id");
		$usuario = addslashes($this->session->userdata('usuario'));

		if ( strlen($numero) == 14 ){
			if (  $anterior == 0 )  {
				$mSQL = "UPDATE sfac SET reiva=round(iva*0.75,2), creiva='$numero', freiva='$fecha', ereiva='$efecha' WHERE id=$id";
				$this->db->simple_query($mSQL);
				//memowrite($mSQL,"sfacreivaSFAC");

				$transac = $this->datasis->prox_sql("ntransa");
				$transac = str_pad($transac, 8, "0", STR_PAD_LEFT);

				if ($referen == 'C') {
					$saldo =  $this->datasis->dameval("SELECT monto-abonos FROM smov WHERE tipo_doc='FC' AND numero='$numfac'");
				}

				if ( $tipo_doc == 'F') {
					if ($referen == 'E') {
						// FACTURA PAGADA AL CONTADO GENERA ANTICIPO
						$mnumant = $this->datasis->prox_sql("nancli");
						$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);

						$mSQL = "INSERT INTO smov  (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario, nroriva, emiriva )
						SELECT cod_cli, nombre, 'AN' tipo_doc, '$mnumant' numero, freiva fecha, reiva monto, 0 impuesto, freiva vence,
							CONCAT('RET/IVA DE ',cod_cli,' A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
							curdate() estampa, curtime() hora, '$transac' transac, '".$usuario."' usuario, creiva, ereiva
						FROM sfac WHERE id=$id";
						$this->db->simple_query($mSQL);
						$mdevo = "<h1 style='color:green;'>EXITO</h1>Retencion Guardada, Anticipo Generado por factura pagada al contado";
					} elseif ($referen == 'C') {
						// Busca si esta cancelada
						$tiposfac = 'FC';
						if ( $tipo_doc == 'D') $tiposfac = 'NC';
						$mSQL = "SELECT monto-abonos saldo FROM smov WHERE numero='$numfac' AND cod_cli='$cod_cli' AND tipo_doc='$tiposfac'";
						$saldo = $this->datasis->dameval($mSQL);
						if ( $saldo < $monto ) {  // crea anticipo
							$mnumant = $this->datasis->prox_sql("nancli");
							$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);
							$mSQL = "INSERT INTO smov  (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario, nroriva, emiriva )
							SELECT cod_cli, nombre, 'AN' tipo_doc, '$mnumant' numero, freiva fecha, reiva monto, 0 impuesto, freiva vence,
								CONCAT('APLICACION DE RETENCION A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
								curdate() estampa, curtime() hora, '$transac' transac, '".$usuario."' usuario, creiva, ereiva
							FROM sfac WHERE id=$id";
							$this->db->simple_query($mSQL);
							$mdevo = "<h1 style='color:green;'>EXITO</h1>Cambios Guardados, Anticipo Generado por factura ya pagada";
							memowrite($mSQL,"sfacreivaAN");
						} else {
							$mnumant = $this->datasis->prox_sql("nccli");
							$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);
							$mSQL = "INSERT INTO smov (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, abonos, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario, codigo, descrip, nroriva, emiriva )
								SELECT cod_cli, nombre, 'NC' tipo_doc, '$mnumant' numero, freiva fecha, reiva monto, 0 impuesto, reiva abonos, freiva vence,
								CONCAT('APLICACION DE RETENCION A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
								curdate() estampa, curtime() hora, '$transac' transac, '".$usuario."' usuario,
								'NOCON 'codigo, 'NOTA DE CONTABILIDAD' descrip, creiva, ereiva
								FROM sfac WHERE id=$id";
							$this->db->simple_query($mSQL);

							// ABONA A LA FACTURA
							$mSQL = "UPDATE smov SET abonos=abonos+$monto WHERE numero='$numfac' AND cod_cli='$cod_cli' AND tipo_doc='$tiposfac'";
							$this->db->simple_query($mSQL);

							//Crea la relacion en ccli
							$mdevo = "<h1 style='color:green;'>EXITO</h1>Cambios Guardados, Nota de Credito generada y aplicada a la factura";
						}
					}
					$mnumant = $this->datasis->prox_sql("ndcli");
					$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);
					$mSQL = "INSERT INTO smov (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, abonos, vence, observa1, tipo_ref, num_ref, estampa, hora, usuario, transac, codigo, descrip, nroriva, emiriva )
						SELECT 'REIVA' cod_cli, 'RETENCION DE I.V.A. POR COMPENSAR' nombre, 'ND' tipo_doc, '$mnumant' numero, freiva fecha,
						reiva monto, 0 impuesto, 0 abonos, freiva vence, CONCAT('RET/IVA DE ',cod_cli,' A ',tipo_doc,numero) observa1,
						IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref, curdate() estampa,
						curtime() hora, '".$usuario."' usuario, '$transac' transac, 'NOCON 'codigo,
						'NOTA DE CONTABILIDAD' descrip, creiva, ereiva
					FROM sfac WHERE id=$id";
					$this->db->simple_query($mSQL);
					memowrite($mSQL,"sfacreivaND");
				} else {
					// DEVOLUCIONES GENERA ND AL CLIENTE
					$mnumant = $this->datasis->prox_sql("ndcli");
					$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);

					$mSQL = "INSERT INTO smov  (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario, nroriva, emiriva )
					SELECT cod_cli, nombre, 'ND' tipo_doc, '$mnumant' numero, freiva fecha, reiva monto, 0 impuesto, freiva vence,
						CONCAT('RET/IVA DE ',cod_cli,' A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
						curdate() estampa, curtime() hora, '$transac' transac, '".$usuario."' usuario, creiva, ereiva
					FROM sfac WHERE id=$id";
					$this->db->simple_query($mSQL);
					$mdevo = "<h1 style='color:green;'>EXITO</h1>Retencion Guardada, Anticipo Generado por factura pagada al contado";

					// Debe abonar la ND si existe un AN
					/*
					if ($referen == 'E') {
						// DEVOLUCIONES PAGADA AL CONTADO GENERA
						$mnumant = $this->datasis->prox_sql("ndcli");
						$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);

						$mSQL = "INSERT INTO smov  (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario, nroriva, emiriva )
						SELECT cod_cli, nombre, 'ND' tipo_doc, '$mnumant' numero, freiva fecha, reiva monto, 0 impuesto, freiva vence,
							CONCAT('RET/IVA DE ',cod_cli,' A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
							curdate() estampa, curtime() hora, '$transac' transac, '".$usuario."' usuario, creiva, ereiva
						FROM sfac WHERE id=$id";
						$this->db->simple_query($mSQL);
						$mdevo = "<h1 style='color:green;'>EXITO</h1>Retencion Guardada, Anticipo Generado por factura pagada al contado";
					} elseif ($referen == 'C') {
						// B
						$tiposfac = 'FC';
						if ( $tipo_doc == 'D') $tiposfac = 'NC';
						$mSQL = "SELECT monto-abonos saldo FROM smov WHERE numero='$numfac' AND cod_cli='$cod_cli' AND tipo_doc='$tiposfac'";
						$saldo = $this->datasis->dameval($mSQL);
						if ( $saldo < $monto ) {  // crea anticipo
							$mnumant = $this->datasis->prox_sql("nancli");
							$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);
							$mSQL = "INSERT INTO smov  (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario, nroriva, emiriva )
							SELECT cod_cli, nombre, 'AN' tipo_doc, '$mnumant' numero, freiva fecha, reiva monto, 0 impuesto, freiva vence,
								CONCAT('APLICACION DE RETENCION A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
								curdate() estampa, curtime() hora, '$transac' transac, '".$usuario."' usuario, creiva, ereiva
							FROM sfac WHERE id=$id";
							$this->db->simple_query($mSQL);
							$mdevo = "<h1 style='color:green;'>EXITO</h1>Cambios Guardados, Anticipo Generado por factura ya pagada";
							memowrite($mSQL,"sfacreivaAN");
						} else {
							$mnumant = $this->datasis->prox_sql("nccli");
							$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);
							$mSQL = "INSERT INTO smov (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, abonos, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario, codigo, descrip, nroriva, emiriva )
								SELECT cod_cli, nombre, 'NC' tipo_doc, '$mnumant' numero, freiva fecha, reiva monto, 0 impuesto, reiva abonos, freiva vence,
								CONCAT('APLICACION DE RETENCION A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
								curdate() estampa, curtime() hora, '$transac' transac, '".$usuario."' usuario,
								'NOCON 'codigo, 'NOTA DE CONTABILIDAD' descrip, creiva, ereiva
								FROM sfac WHERE id=$id";
							$this->db->simple_query($mSQL);

							// ABONA A LA FACTURA
							$mSQL = "UPDATE smov SET abonos=abonos+$monto WHERE numero='$numfac' AND cod_cli='$cod_cli' AND tipo_doc='$tiposfac'";
								$this->db->simple_query($mSQL);

							//Crea la relacion en ccli

							$mdevo = "<h1 style='color:green;'>EXITO</h1>Cambios Guardados, Nota de Credito generada y aplicada a la factura";
						}
					}*/

					//Devoluciones debe crear un NC si esta en el periodo
					$mnumant = $this->datasis->prox_sql("nccli");
					$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);
					$mSQL = "INSERT INTO smov (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, abonos, vence, observa1, tipo_ref, num_ref, estampa, hora, usuario, transac, codigo, descrip, nroriva, emiriva )
						SELECT 'REIVA' cod_cli, 'RETENCION DE I.V.A. POR COMPENSAR' nombre, 'NC' tipo_doc, '$mnumant' numero, freiva fecha,
						reiva monto, 0 impuesto, 0 abonos, freiva vence, CONCAT('RET/IVA DE ',cod_cli,' A ',tipo_doc,numero) observa1,
						IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref, curdate() estampa,
						curtime() hora, '".$usuario."' usuario, '$transac' transac, 'NOCON 'codigo,
						'NOTA DE CONTABILIDAD' descrip, creiva, ereiva
					FROM sfac WHERE id=$id";
					$this->db->simple_query($mSQL);
					memowrite($mSQL,"sfacreivaND");

				}
			}else{
				$mdevo = "<h1 style='color:red;'>ERROR</h1>Retencion ya aplicada";
			}
		}else
			$mdevo = "<h1 style='color:red;'>ERROR</h1>Longitud del comprobante menor a 14 caracteres, corrijalo y vuelva a intentar";
		echo $mdevo;
	}

	//***************************
	//
	// Reintegrar retencion de IVA
	//
	function sfacreivaef(){
		$id     = $this->uri->segment($this->uri->total_segments());
		$reinte = 0;
		$numero = rawurldecode($this->input->post('numero'));
		$fecha  = rawurldecode($this->input->post('fecha'));
		$efecha = rawurldecode($this->input->post('efecha'));
		$caja   = rawurldecode($this->input->post('caja'));
		$cheque = rawurldecode($this->input->post('cheque'));
		$benefi = rawurldecode($this->input->post('benefi'));

		$mdevo  = "Exito";

		memowrite("efecha=$efecha, fecha=$fecha, numero=$numero, id=$id, caja=$caja, cheque=$cheque, benefi=$benefi ","sfacreivaef");

		// status de la factura
		$fecha  = substr($fecha, 6,4).substr($fecha, 3,2).substr($fecha, 0,2);
		$efecha = substr($efecha,6,4).substr($efecha,3,2).substr($efecha,0,2);

		$tipo_doc = $this->datasis->dameval("SELECT tipo_doc FROM sfac WHERE id=$id");
		$referen  = $this->datasis->dameval("SELECT referen  FROM sfac WHERE id=$id");
		$numfac   = $this->datasis->dameval("SELECT numero   FROM sfac WHERE id=$id");
		$cod_cli  = $this->datasis->dameval("SELECT cod_cli  FROM sfac WHERE id=$id");
		$monto    = $this->datasis->dameval("SELECT ROUND(iva*0.75,2)  FROM sfac WHERE id=$id");
		$anterior = $this->datasis->dameval("SELECT reiva FROM sfac WHERE id=$id");

		$usuario  = addslashes($this->session->userdata('usuario'));
		$codbanc = substr($caja,0,2);
		$verla = 0;

		if ($codbanc == '__') {
			$tbanco  = '';
			$cheque  = '';
		} else {
			$tbanco  = $this->datasis->dameval("SELECT tbanco FROM banc WHERE codbanc='$codbanc'");
			$cheque  = str_pad($cheque, 12, "0", STR_PAD_LEFT);
			$query   = "SELECT count(*) FROM bmov WHERE tipo_op='CH' AND codbanc='$codbanc' AND numero='$cheque' ";
			if ( $tbanco != 'CAJ' ) {
				$verla = $this->datasis->dameval($query);
			}
		}

		if ( $verla == 0 ) {
			if ( strlen($numero) == 14 ){
				if (  $anterior == 0 )  {
					$mSQL = "UPDATE sfac SET reiva=round(iva*0.75,2), creiva='$numero', freiva='$fecha', ereiva='$efecha' WHERE id=$id";
					$this->db->simple_query($mSQL);
					memowrite($mSQL,"sfacreivaSFAC");

					$transac = $this->datasis->prox_sql("ntransa");
					$transac = str_pad($transac, 8, "0", STR_PAD_LEFT);

					if ( $codbanc == '__' ) {   // manda a cxp
						if ( $tipo_doc == 'F' ) {
							// crea un registro en sprm
							$this->db->simple_query($mSQL);
							$mnumant = $this->datasis->prox_sql("num_nd");
							$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);
							$mSQL = "INSERT INTO sprm (cod_prv, nombre, tipo_doc, numero, fecha, monto, impuesto, abonos, vence, observa1, tipo_ref, num_ref, estampa, hora, usuario, transac, codigo, descrip )
								SELECT 'REINT' cod_prv, 'REINTEGRO A CLIENTE' nombre, 'ND' tipo_doc, '$mnumant' numero, freiva fecha,
								reiva monto, 0 impuesto, 0 abonos, freiva vence, 'REINTEGRO POR RETENCION A DOCUMENTO $numfac' observa1,
									IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref, curdate() estampa,
								curtime() hora, '".$usuario."' usuario, '$transac' transac, 'NOCON 'codigo,
								'NOTA DE CONTABILIDAD' descrip
							FROM sfac WHERE id=$id";
							$this->db->simple_query($mSQL);
							memowrite($mSQL,"sfacreivaCXP");

/*
							$mSQL  = "INSERT INTO bmov ( codbanc, moneda, numcuent, banco, saldo, tipo_op, numero,fecha, clipro, codcp, nombre, monto, concepto, benefi, posdata, liable, transac, usuario, estampa, hora, negreso ) ";
							$mSQL .= "SELECT '$codbanc' codbanc, b.moneda, b.numcuent, ";
							$mSQL .= "b.banco, b.saldo, IF(b.tbanco='CAJ','ND','CH') tipo_op, '$cheque' numero, ";
							$mSQL .= "a.freiva, 'C' clipro, a.cod_cli codcp, a.nombre, a.reiva monto, ";
							$mSQL .= "'REINTEGRO DE RETENCION APLICADA A FC $numfac' concepto, ";
							$mSQL .= "'$benefi' benefi, a.freiva posdata, 'S' liable, '$transac' transac, ";
							$mSQL .= "'$usuario' usuario, curdate() estampa, curtime() hora, '$negreso' negreso ";
							$mSQL .= "FROM sfac a JOIN banc b ON b.codbanc='$codbanc' ";
							$mSQL .= "WHERE a.id=$id ";
							memowrite($mSQL,"sfacreivaCH");
*/
							$mdevo = "<h1 style='color:green;'>EXITO</h1>Cambios Guardados, Nota de Credito generada y ND en CxP por Reintero (REINT) ";
						} else {
							//Devoluciones
						}


					} else {
						if ( $tbanco == 'CAJ' ) {
							$m = 1;
							while ( $m > 0 ) {
								$cheque = $this->datasis->prox_sql("ncaja$codbanc");
								$cheque = str_pad($cheque, 12, "0", STR_PAD_LEFT);
								$m = $this->datasis->dameval("SELECT COUNT(*) FROM bmov WHERE codbanc='$codbanc' AND tipo_op='ND' AND numero='$cheque' ");
							}
						}

						$negreso = $this->datasis->prox_sql("negreso");
						$negreso = str_pad($negreso, 8, "0", STR_PAD_LEFT);

						//$numero = str_pad($numero, 8, "0", STR_PAD_LEFT);
						$saldo = 0;
						if ($referen == 'C') {
							$saldo =  $this->datasis->dameval("SELECT monto-abonos FROM smov WHERE tipo_doc='FC' AND numero='$numfac'");
						}
						if ( $tipo_doc == 'F' ) {
							// crea un registro en bmov
							$mSQL  = "INSERT INTO bmov ( codbanc, moneda, numcuent, banco, saldo, tipo_op, numero,fecha, clipro, codcp, nombre, monto, concepto, benefi, posdata, liable, transac, usuario, estampa, hora, negreso ) ";
							$mSQL .= "SELECT '$codbanc' codbanc, b.moneda, b.numcuent, ";
							$mSQL .= "b.banco, b.saldo, IF(b.tbanco='CAJ','ND','CH') tipo_op, '$cheque' numero, ";
							$mSQL .= "a.freiva, 'C' clipro, a.cod_cli codcp, a.nombre, a.reiva monto, ";
							$mSQL .= "'REINTEGRO DE RETENCION APLICADA A FC $numfac' concepto, ";
							$mSQL .= "'$benefi' benefi, a.freiva posdata, 'S' liable, '$transac' transac, ";
							$mSQL .= "'$usuario' usuario, curdate() estampa, curtime() hora, '$negreso' negreso ";
							$mSQL .= "FROM sfac a JOIN banc b ON b.codbanc='$codbanc' ";
							$mSQL .= "WHERE a.id=$id ";
							memowrite($mSQL,"sfacreivaCH");
							$this->db->simple_query($mSQL);

							$mdevo = "<h1 style='color:green;'>EXITO</h1>Cambios Guardados, Nota de Credito generada y cargo en caja generado";
						} else {
							//Devoluciones
						}
					}
					if ( $tipo_doc == 'F' ) {
						$this->db->simple_query($mSQL);
						$mnumant = $this->datasis->prox_sql("ndcli");
						$mnumant = str_pad($mnumant, 8, "0", STR_PAD_LEFT);
						$mSQL = "INSERT INTO smov (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, abonos, vence, observa1, tipo_ref, num_ref, estampa, hora, usuario, transac, codigo, descrip, nroriva, emiriva )
							SELECT 'REIVA' cod_cli, 'RETENCION DE IVA POR COMPENSAR' nombre, 'ND' tipo_doc, '$mnumant' numero, freiva fecha,
							reiva monto, 0 impuesto, 0 abonos, freiva vence, 'APLICACION DE RETENCION A DOCUMENTO $numfac' observa1,
								IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref, curdate() estampa,
							curtime() hora, '".$usuario."' usuario, '$transac' transac, 'NOCON 'codigo,
							'NOTA DE CONTABILIDAD' descrip, creiva, ereiva
						FROM sfac WHERE id=$id";
						$this->db->simple_query($mSQL);
						memowrite($mSQL,"sfacreivaND");
					} else {
						//Devoluciones
					}

				} else {
					$mdevo = "<h1 style='color:red;'>ERROR</h1>Retencion ya aplicada";
				}
			} else $mdevo = "<h1 style='color:red;'>ERROR</h1>Longitud del comprobante menor a 14 caracteres, corrijalo y vuelva a intentar";
		} else $mdevo = "<h1 style='color:red;'>ERROR</h1>Un cheque con ese numero ya existe ($cheque) ";
		echo $mdevo;
	}

	// json para llena la tabla de inventario
	function sfacsig() {
		$numa  = $this->uri->segment($this->uri->total_segments());
		$tipoa = $this->uri->segment($this->uri->total_segments()-1);

		$mSQL  = 'SELECT a.codigoa, a.desca, a.cana, a.preca, a.tota, a.iva, IF(a.pvp < a.preca, a.preca, a.pvp)  pvp, ROUND(100-a.preca*100/IF(a.pvp<a.preca,a.preca, a.pvp),2) descuento, ROUND(100-ROUND(a.precio4*100/(100+a.iva),2)*100/a.preca,2) precio4, a.detalle, a.fdespacha, a.udespacha, a.bonifica, b.id url ';
		$mSQL .= "FROM sitems a LEFT JOIN sinv b ON a.codigoa=b.codigo WHERE a.tipoa='$tipoa' AND a.numa='$numa' ";
		$mSQL .= "ORDER BY a.codigoa";


		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$retArray = array();
			foreach( $query->result_array() as  $row ) {
				$retArray[] = $row;
			}
			$data = json_encode($retArray);
			$ret = "{data:" . $data .",\n";
			$ret .= "recordType : 'array'}";
		} else {
			$ret = '{data : []}';
		}
		echo $ret;
	}

	function _pre_insert($do){
		return false;
	}

	function _pre_update($do){
		return false;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('sfac',"Factura $codigo CREADO");
	}

	function chpreca($preca,$ind){
		$codigo  = $this->input->post('codigo_'.$ind);
		$precio4 = $this->datasis->dameval('SELECT base4 FROM sinv WHERE codigo='.$this->db->escape($codigo));
		if($precio4<0) $precio4=0;

		if($preca<$precio4){
			$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' debe contener un precio de al menos '.nformat($precio4));
			return false;
		}else{
			return true;
		}
	}

	function creadpfacf($numero){
		$this->rapyd->load('dataform');

		$form = new DataForm("ventas/sfac/creadpfac/$numero");
		$form->title('Sellecione el Almacen');

		$form->alma = new dropdownField("Almacen", 'alma');
		$form->alma->options("SELECT ubica,ubides FROM caub WHERE invfis='N' AND gasto='N'");

		$form->submit("btnsubmit","Facturar");
		$form->build_form();

		$data['content'] = $form->output;
		$data['title']   = heading("Convertir Pedido en Factura");
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function creadpfac($numero){
		$alma    =$this->input->post('alma');
		$numeroe =$this->db->escape($numero);
		$user    =$this->session->userdata('usuario');
		$nsfac   =$this->datasis->fprox_numero('nsfac');
		$transac =$this->datasis->fprox_numero('transac');
		$almae   =$this->db->escape($alma);

		/*CREA ENCABEZADO DE LA FACTURA SFAC*/
		$query="
		INSERT INTO sfac (`tipo_doc`,`numero`,`fecha`,`vence`,`vd`,`cod_cli`,`rifci`,`nombre`,`direc`,`dire1`,`referen`,`iva`,`inicial`,`totals`,`totalg`,`observa`,`observ1`,`cajero`,`almacen`,`peso`,`pedido`,`usuario`,`estampa`,`hora`,`transac`,`zona`,`ciudad`,`comision`,`exento`,`tasa`,`reducida`,`sobretasa`,`montasa`,`monredu`,`monadic`)
		SELECT 'F','$nsfac',a.fecha,DATE_ADD(a.fecha, INTERVAL (SELECT b.formap FROM scli b WHERE b.cliente=a.cod_cli) DAY) vence,
		a.vd,a.cod_cli,a.rifci,a.nombre,a.direc,a.dire1,'C' referen,a.iva,0 inicial,a.totals,a.totalg,a.observa,a.observ1,
		a.cajero,$almae,a.peso,a.numero,'$user',now() estampa,CURTIME() hora,'$transac',a.zona,a.ciudad,0,SUM(d.tota)*(d.iva=0) exento,
		ROUND(SUM(d.tota*(SELECT tasa FROM civa e ORDER BY fecha desc LIMIT 1)/100)*(d.iva=(SELECT tasa FROM civa e ORDER BY fecha desc LIMIT 1))) tasa,
		ROUND(SUM(d.tota*(SELECT redutasa FROM civa e ORDER BY fecha desc LIMIT 1)/100)*(d.iva=(SELECT redutasa FROM civa e ORDER BY fecha desc LIMIT 1))) redutasa,
		ROUND(SUM(d.tota*(SELECT sobretasa FROM civa e ORDER BY fecha desc LIMIT 1)/100)*(d.iva=(SELECT sobretasa FROM civa e ORDER BY fecha desc LIMIT 1))) sobretasa,
		ROUND(SUM(d.tota)*(d.iva=(SELECT tasa FROM civa e ORDER BY fecha desc LIMIT 1))) montasa,
		ROUND(SUM(d.tota)*(d.iva=(SELECT redutasa FROM civa e ORDER BY fecha desc LIMIT 1))) monredu,
		ROUND(SUM(d.tota)*(d.iva=(SELECT sobretasa FROM civa e ORDER BY fecha desc LIMIT 1))) monadic
		FROM pfac a
		JOIN itpfac d ON a.numero=d.numa
		WHERE a.numero=$numeroe
		";

		$this->db->query($query);
		$id_sfac=$this->db->insert_id();

		/*CREA ENCABEZADO DE LA FACTURA SFAC*/
		$query="
		INSERT INTO sitems (`tipoa`,`numa`,`codigoa`,`desca`,`cana`,`preca`,`tota`,`iva`,`fecha`,`vendedor`,`costo`,`pvp`,`cajero`,`mostrado`,`usuario`,`estampa`,`hora`,`transac`,`precio4`,`id_sfac`)
		SELECT 'F','$nsfac',d.codigoa,d.desca,d.cana,d.preca,d.tota,d.iva,CURDATE(),d.vendedor,d.costo,d.pvp,
		d.cajero,d.mostrado,'$user' usuario,NOW() estampa,CURTIME(),'$transac',c.precio4,$id_sfac idsfac
		FROM pfac a
		JOIN itpfac d ON a.numero=d.numa
		JOIN sinv c ON d.codigoa=c.codigo
		WHERE a.numero=$numeroe
		";

		$this->db->query($query);

		$query="
		INSERT IGNORE INTO smov ( cod_cli, nombre, dire1, dire2, tipo_doc, numero, fecha, monto, impuesto, abonos, vence, observa1, estampa, usuario, hora, transac, tasa, montasa, reducida, monredu, sobretasa, monadic, exento )
		SELECT cod_cli, nombre, direc, dire1, tipo_doc, numero, fecha, totalg, iva,   0 abonos, vence,
		if(tipo_doc='D', 'DEVOLUCION EN VENTAS', 'FACTURA DE CREDITO' ) observa1, estampa, usuario, hora, transac, tasa, montasa, reducida, monredu, sobretasa, monadic, exento
		FROM sfac WHERE transac='$transac' and referen='C'
		LIMIT 1
		";

		$this->db->query($query);

		$query="
		SELECT a.codigoa,b.almacen,-1*a.cana cana
		FROM sitems a
		JOIN sfac b ON a.id_sfac=b.id
		JOIN caub c ON b.almacen=c.ubica
		WHERE b.transac='$transac'
		";

		$query=$this->db->query($query);
		foreach($query->result as $row)
		$this->datasis->sinvcarga($row->codigoa,$row->almacen,$row->cana);

		redirect("ventas/sfac/dataedit/show/$id_sfac");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('sfac',"Factura $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('sfac',"Factura $codigo ELIMINADO");
	}

	function instalar(){
		if(!$this->datasis->iscampo('sfac','ereiva')){
			$mSQL="ALTER TABLE sfac ADD ereiva DATE AFTER freiva";
			$this->db->simple_query($mSQL);
		}
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'sfac');

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('sfac');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'id', 'desc' );

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$sql = $this->db->_compile_select($this->db->_count_string . $this->db->_protect_identifiers('numrows'));
		$results = $this->datasis->dameval($sql);
		$this->db->limit($limit, $start);
		$query = $this->db->get();

		$arr = $this->datasis->codificautf8($query->result_array());

		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}


	function modificar(){
		$js= file_get_contents('php://input');
		$campos = json_decode($js,true);
		//$campos = $data['data'];
		$id        = $campos['id'];
		$nfiscal   = $campos['nfiscal'];
		$maqfiscal = $campos['maqfiscal'];

		//print_r($campos);
		$mSQL = $this->db->update_string("sfac", array('nfiscal'=>$campos['nfiscal'],'maqfiscal'=>$campos['maqfiscal']),"id='$id'" );
		$this->db->simple_query($mSQL);
		logusu('sfac',"FACTURACION ".$campos['id']." MODIFICADO");
		echo "{ success: true, message: 'Factura Modificado '}";
	}


	function tabla() {
		$id = $this->uri->segment($this->uri->total_segments());
		$cliente = $this->datasis->dameval("SELECT cod_cli FROM sfac WHERE id='$id'");
		$transac = $this->datasis->dameval("SELECT transac FROM sfac WHERE id='$id'");
		$salida = '';

		// Revisa formas de pago sfpa
		$mSQL = "SELECT tipo, numero, monto FROM sfpa WHERE transac='$transac' AND monto<>0";
		$query = $this->db->query($mSQL);
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Forma de Pago</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tipo</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table>";
		}

		// Cuentas por Cobrar
		$mSQL = "SELECT cod_cli, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM smov WHERE cod_cli='$cliente' AND abonos<>monto AND tipo_doc<>'AB' ORDER BY fecha DESC ";
		$query = $this->db->query($mSQL);
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento Pendientes en CxC</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			$i = 1;
			foreach ($query->result_array() as $row)
			{
				if ( $i < 6 ) {
					$salida .= "<tr>";
					$salida .= "<td>".$row['tipo_doc']."</td>";
					$salida .= "<td>".$row['numero'].  "</td>";
					$salida .= "<td align='right'>".nformat($row['monto']-$row['abonos']).   "</td>";
					$salida .= "</tr>";
				}
				if ( $i == 6 ) {
					$salida .= "<tr>";
					$salida .= "<td colspan=3>Mas......</td>";
					$salida .= "</tr>";
				}
				if ( $row['tipo_doc'] == 'FC' or $row['tipo_doc'] == 'ND' or $row['tipo_doc'] == 'GI' )
					$saldo += $row['monto']-$row['abonos'];
				else
					$saldo -= $row['monto']-$row['abonos'];
				$i ++;
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		$query->free_result();

		// Revisa movimiento de bancos
		$mSQL = "SELECT codbanc, numero, monto FROM bmov WHERE transac='$transac' ";
		$query = $this->db->query($mSQL);
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Caja o Banco</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Bco</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['codbanc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table>";
		}

		echo $salida;
	}

	function gridsitems(){
		$numero = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  0;
		$id     = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;

		//if ($numero == 0 ) $numero = $this->datasis->dameval("SELECT MAX(nu) FROM sfac");
		if ($id == 0 ) $id = $this->datasis->dameval("SELECT MAX(id) FROM sfac");

		$numero   = $this->datasis->dameval("SELECT numero FROM sfac WHERE id=$id");
		$tipo_doc = $this->datasis->dameval("SELECT tipo_doc FROM sfac WHERE id=$id");


		$mSQL = "SELECT * FROM sitems a JOIN sinv b ON a.codigoa=b.codigo WHERE a.tipoa='$tipo_doc' AND a.numa='$numero' ORDER BY a.codigoa";
		$query = $this->db->query($mSQL);
		$results = $query->num_rows();
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function sclibu(){
		$numero = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM sfac a JOIN scli b ON a.cod_cli=b.cliente WHERE numero='$numero'");
		redirect('ventas/scli/dataedit/show/'.$id);
	}

	function sfacextjs() {
		$encabeza='FACTURACION';

		$modulo = 'sfac';
		$urlajax = 'ventas/sfac/';
		$listados= $this->datasis->listados($modulo);
		$otros=$this->datasis->otros($modulo, $urlajax);

		$columnas = "
		{ header: 'Tipo',       width: 30, sortable: true, dataIndex: 'tipo_doc' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Numero',     width: 60, sortable: true, dataIndex: 'numero' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'N.Fiscal',   width: 70, sortable: true, dataIndex: 'nfiscal' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fecha',      width: 70, sortable: true, dataIndex: 'fecha' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Cliente',    width: 60, sortable: true, dataIndex: 'cod_cli' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'RIF/CI',     width: 90, sortable: true, dataIndex: 'rifci' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Nombre',     width:200, sortable: true, dataIndex: 'nombre' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Base',       width: 80, sortable: true, dataIndex: 'totals' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',        width: 80, sortable: true, dataIndex: 'iva' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Total',      width: 80, sortable: true, dataIndex: 'totalg' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Inicial',    width: 80, sortable: true, dataIndex: 'inicial' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Vence',      width: 70, sortable: true, dataIndex: 'vence' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Maq/Fiscal', width: 90, sortable: true, dataIndex: 'maqfiscal' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Orden',      width: 60, sortable: true, dataIndex: 'orden' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Referen',    width: 60, sortable: true, dataIndex: 'referen' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Vende',      width: 60, sortable: true, dataIndex: 'vd' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Direc',      width: 60, sortable: true, dataIndex: 'direc' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Dire1',      width: 60, sortable: true, dataIndex: 'dire1' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Status',     width: 60, sortable: true, dataIndex: 'status' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'observa',    width: 60, sortable: true, dataIndex: 'observa' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'observ1',    width: 60, sortable: true, dataIndex: 'observ1' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'devolu',     width: 60, sortable: true, dataIndex: 'devolu' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'cajero',     width: 60, sortable: true, dataIndex: 'cajero' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'almacen',    width: 60, sortable: true, dataIndex: 'almacen' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'peso',       width: 60, sortable: true, dataIndex: 'peso' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'factura',    width: 60, sortable: true, dataIndex: 'factura' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'pedido',     width: 60, sortable: true, dataIndex: 'pedido' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'usuario',    width: 60, sortable: true, dataIndex: 'usuario' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'estampa',    width: 60, sortable: true, dataIndex: 'estampa' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'hora',       width: 60, sortable: true, dataIndex: 'hora' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'transac',    width: 60, sortable: true, dataIndex: 'transac' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'zona',       width: 60, sortable: true, dataIndex: 'zona' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'ciudad',     width: 60, sortable: true, dataIndex: 'ciudad' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'comision',   width: 60, sortable: true, dataIndex: 'comision' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'pagada',     width: 60, sortable: true, dataIndex: 'pagada' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'sepago',     width: 60, sortable: true, dataIndex: 'sepago' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'dias',       width: 60, sortable: true, dataIndex: 'dias' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'fpago' , width: 60, sortable: true, dataIndex: 'fpago' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'comical' , width: 60, sortable: true, dataIndex: 'comical' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'exento' , width: 60, sortable: true, dataIndex: 'exento' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'tasa' , width: 60, sortable: true, dataIndex: 'tasa' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'reducida' , width: 60, sortable: true, dataIndex: 'reducida' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'sobretasa' , width: 60, sortable: true, dataIndex: 'sobretasa' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'montasa' , width: 60, sortable: true, dataIndex: 'montasa' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'monredu' , width: 60, sortable: true, dataIndex: 'monredu' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'monadic' , width: 60, sortable: true, dataIndex: 'monadic' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'notcred' , width: 60, sortable: true, dataIndex: 'notcred' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'fentrega' , width: 60, sortable: true, dataIndex: 'fentrega' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'fpagom' , width: 60, sortable: true, dataIndex: 'fpagom' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'fdespacha' , width: 60, sortable: true, dataIndex: 'fdespacha' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'udespacha' , width: 60, sortable: true, dataIndex: 'udespacha' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'numarma' , width: 60, sortable: true, dataIndex: 'numarma' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'dmaqfiscal' , width: 60, sortable: true, dataIndex: 'dmaqfiscal' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'nromanual' , width: 60, sortable: true, dataIndex: 'nromanual' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'fmanual' , width: 60, sortable: true, dataIndex: 'fmanual' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'modificado' , width: 60, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'reiva' , width: 60, sortable: true, dataIndex: 'reiva' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'creiva' , width: 60, sortable: true, dataIndex: 'creiva' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'freiva' , width: 60, sortable: true, dataIndex: 'freiva' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'ereiva' , width: 60, sortable: true, dataIndex: 'ereiva' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Exenta' , width: 60, sortable: true, dataIndex: 'vexenta' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Certificado' , width: 60, sortable: true, dataIndex: 'certificado' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Proveed' , width: 60, sortable: true, dataIndex: 'sprv' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Maestra' , width: 60, sortable: true, dataIndex: 'maestra' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'id' , width: 60, sortable: true, dataIndex: 'id' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0000')},
";

		$coldeta = "
	var Deta1Col = [
		{ header: 'Codigo',      width: 90, sortable: true, dataIndex: 'codigoa' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Descripcion', width:250, sortable: true, dataIndex: 'desca' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cantidad',    width: 60, sortable: true, dataIndex: 'cana' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Precio',      width: 80, sortable: true, dataIndex: 'preca' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Importe',     width: 80, sortable: true, dataIndex: 'tota' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',         width: 50, sortable: true, dataIndex: 'iva' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Costo',       width: 80, sortable: true, dataIndex: 'costo' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'PVP',         width: 80, sortable: true, dataIndex: 'pvp' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Comision',    width: 80, sortable: true, dataIndex: 'comision' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Cajero',      width: 60, sortable: true, dataIndex: 'cajero' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Usuario',     width: 80, sortable: true, dataIndex: 'usuario' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Estampa',     width: 70, sortable: true, dataIndex: 'estampa' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Hora',        width: 60, sortable: true, dataIndex: 'hora' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Transac',     width: 80, sortable: true, dataIndex: 'transac' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Despacha',    width: 60, sortable: true, dataIndex: 'despacha' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Flote',       width: 80, sortable: true, dataIndex: 'flote' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Precio4',     width: 80, sortable: true, dataIndex: 'precio4' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Detalle',     width: 80, sortable: true, dataIndex: 'detalle' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fdespacha',   width: 60, sortable: true, dataIndex: 'fdespacha' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Udespacha',   width: 60, sortable: true, dataIndex: 'udespacha' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Combo',       width: 60, sortable: true, dataIndex: 'combo' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Descuento',   width: 60, sortable: true, dataIndex: 'descuento' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Bonifica',    width: 60, sortable: true, dataIndex: 'bonifica' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Modificado',  width: 60, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'id',          width: 60, sortable: true, dataIndex: 'id' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'id_sfac',     width: 60, sortable: true, dataIndex: 'id_sfac' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
	]";


		$variables='';

		$valida="		{ type: 'length', field: 'numero',  min:  1 }";


		$funciones = "
function renderScli(value, p, record) {
	var mreto='';
	if ( record.data.cod_cli == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlAjax+'sclibu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.numero );
}


function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}

	";

		$campos = $this->datasis->extjscampos($modulo);

		$stores = "
	Ext.define('It".$modulo."', {
		extend: 'Ext.data.Model',
		fields: [".$this->datasis->extjscampos("sitems")."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlAjax + 'gridsitems',
				method: 'POST'
			},
			reader: {
				type: 'json',
				root: 'data',
				successProperty: 'success',
				messageProperty: 'message',
				totalProperty: 'results'
			}
		}
	});

	//////////////////////////////////////////////////////////
	// create the Data Store
	var storeIt".$modulo." = Ext.create('Ext.data.Store', {
		model: 'It".$modulo."',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	//////////////////////////////////////////////////////////
	//
	var gridDeta1 = Ext.create('Ext.grid.Panel', {
		width:   '100%',
		height:  '100%',
		store:   storeIt".$modulo.",
		title:   'Detalle de la NE',
		iconCls: 'icon-grid',
		frame:   true,
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		columns: Deta1Col
	});

	var ".$modulo."TplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR FACTURA</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'ventas/sfac_add/dataprint/modify/{id}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'ventas/sfac_add/dataprint/modify/{id}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',  'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',
		'</table>','nanai'
	];



	// Al cambiar seleccion
	gridMaest.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridMaest.down('#delete').setDisabled(selectedRecord.length === 0);
			gridMaest.down('#update').setDisabled(selectedRecord.length === 0);
			numero = selectedRecord[0].data.numero;
			gridDeta1.setTitle(selectedRecord[0].data.numero+' '+selectedRecord[0].data.nombre);
			storeIt".$modulo.".load({ params: { numero: numero }});
			var meco1 = Ext.getCmp('imprimir');
			Ext.Ajax.request({
				url: urlAjax +'tabla',
				params: { numero: numero, id: selectedRecord[0].data.id },
				success: function(response) {
					var vaina = response.responseText;
					".$modulo."TplMarkup.pop();
					".$modulo."TplMarkup.push(vaina);
					var ".$modulo."Tpl = Ext.create('Ext.Template', ".$modulo."TplMarkup );
					meco1.setTitle('Imprimir Compra');
					".$modulo."Tpl.overwrite(meco1.body, selectedRecord[0].data );
				}
			});
		}
	});
";

		$acordioni = "{
					layout: 'fit',
					items:[
						{
							name: 'imprimir',
							id: 'imprimir',
							border:false,
							html: 'Para imprimir seleccione una Compra '
						}
					]
				},
";

		$dockedItems = "{
			xtype: 'toolbar',
			items: [
				{
					iconCls: 'icon-add',
					text: 'Agregar',
					scope: this,
					handler: function(){
						window.open(urlApp+'ventas/sfac_add/dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},
				{
					iconCls: 'icon-update',
					text: 'Modificar',
					disabled: true,
					itemId: 'update',
					scope: this,
					handler: function(selModel, selections){
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						gridMaest.down('#delete').setDisabled(selections.length === 0);
						window.open(urlApp+'ventas/sfac_add/dataedit/modify/'+selection.data.id, '_blank', 'width=900,height=730,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},{
					iconCls: 'icon-delete',
					text: 'Eliminar',
					disabled: true,
					itemId: 'delete',
					scope: this,
					handler: function() {
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						Ext.MessageBox.show({
							title: 'Confirme',
							msg: 'Seguro que quiere eliminar la compra Nro. '+selection.data.numero,
							buttons: Ext.MessageBox.YESNO,
							fn: function(btn){
								if (btn == 'yes') {
									if (selection) {
										//storeMaest.remove(selection);
									}
									storeMaest.load();
								}
							},
							icon: Ext.MessageBox.QUESTION
						});
					}
				}
			]
		}
		";

		$grid2 = ",{
				itemId: 'viewport-center-detail',
				activeTab: 0,
				region: 'south',
				height: '40%',
				split: true,
				margins: '0 0 0 0',
				preventHeader: true,
				items: gridDeta1
			}";


		$titulow = 'Facturacion';

		$filtros = "";
		$features = "
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		plugins: [Ext.create('Ext.grid.plugin.CellEditing', { clicksToEdit: 2 })],
";

		$final = "storeIt".$modulo.".load();";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['stores']      = $stores;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		$data['grid2']       = $grid2;
		$data['coldeta']     = $coldeta;
		$data['acordioni']   = $acordioni;
		$data['final']       = $final;

		$data['title']  = heading('Facturacion');
		$this->load->view('extjs/extjsvenmd',$data);

	}
}
