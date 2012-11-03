<?php 
include('common.php');
class Sinv extends Controller {
	var $mModulo = 'SINV';
	var $titp    = 'Inventario de Productos';
	var $tits    = 'Inventario de Productos';
	var $url     = 'inventario/sinv/';

	function Sinv(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'SINV', $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('sinv','id') ) {
			$this->db->simple_query('ALTER TABLE sinv DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE sinv ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE sinv ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
		$this->datasis->modintramenu( 950, 700, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname']);
		$readyLayout = $grid->readyLayout2( 212, 140, $param['grids'][0]['gridname']);

		#Set url
		//$grid->setUrlput(site_url($this->url.'setdata/'));

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"gmarcas",  "img"=>"images/brand.png",  "alt" => 'Crear Marcas',             "label"=>"Crear Marcas"));
		$grid->wbotonadd(array("id"=>"gunidad",  "img"=>"images/scale.png",  "alt" => 'Unidades de Medida',       "label"=>"Unidades y Empaques"));
		$grid->wbotonadd(array("id"=>"kardex",   "img"=>"images/scale.png",  "alt" => 'Kardex de Inventario',     "label"=>"Kardex de Inventario"));
		$grid->wbotonadd(array("id"=>"hinactivo","img"=>"images/basura.png", "alt" => 'Oculta/Muestra Inactivos', "label"=>"Ocultar/Mostrar"));
		$WestPanel = $grid->deploywestp();

		$adic = array(
		array("id"=>"fedita",  "title"=>"Agregar/Editar Registro")
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$link2  =site_url('inventario/sinv/recalcular');

		$funciones = $this->funciones( $param['grids'][0]['gridname']);

		//Panel Central y Sur
		$centerpanel = $grid->centerpanel( $id = "radicional", $param['grids'][0]['gridname'] );

		$param['script']      = script('sinvmaes.js');
		$param['WestPanel']   = $WestPanel;
		$param['funciones']   = $funciones;

		$param['readyLayout']  = $readyLayout;
		$param['centerpanel']  = $centerpanel;

		//$param['EastPanel'] = $EastPanel;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('SINV', 'JQ');
		$param['otros']       = $this->datasis->otros('SINV', 'JQ');
		$param['temas']       = array('proteo','darkness','anexos1');
		$param['bodyscript']  = $bodyscript;
		$param['tabs']        = false;
		$param['encabeza']    = $this->titp;

		$this->load->view('jqgrid/crud2',$param);
	}

	//*******************************************
	//   Funciones
	//*******************************************
	function funciones($grid0){

		//Coloca la Basura a los Productos Inactivos
		$funciones = '
		function factivo(el, val, opts){
			var meco=\'<div><img src="'.base_url().'images/blank.png" width="20" height="18" border="0" /></div>\';
			if ( el == "N" ){
				meco=\'<div><img src="'.base_url().'images/basura.png" width="20" height="20" border="0" /></div>\';
			}
			return meco;
		};
		';

		//Recalcular Precios
		$funciones .= '
		function recalcular(){
			var seguro = true;
			var mtipo="M";
			$.prompt( "<h1>Recalcular Precios de Inventario</h1><p><b>Margenes:</b> Recalcula los margenes dejando fijos los precios</p><p><b>Precios:</b> Recalcula los preios segun los margenes</p>", {
				buttons: { Margenes: 1, Precios: 2, Cancelar: 0 },
				submit: function(e,v,m,f){
					if (v == 1){
						$.ajax({ url: "'.site_url('inventario/sinv/recalcular/M').'",
							complete: function(){ alert(("Recalculo Finalizado")) }})
					} else if( v == 2) {
						$.ajax({ url: "'.site_url('inventario/sinv/recalcular/P').'",
							complete: function(){ alert(("Recalculo Finalizado")) }})
					}
				}
			})
		}
		';

		// Funciones de los Botones
		$funciones .= '
		jQuery("#gmarcas").click( function(){
			window.open(\''.site_url("inventario/marc").'\', \'_blank\', \'width=420,height=450,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-210), screeny=((screen.availWidth/2)-225)\');
		});

		jQuery("#gunidad").click( function(){
			window.open(\''.site_url("inventario/unidad").'\', \'_blank\', \'width=420,height=450,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-225), screeny=((screen.availWidth/2)-250)\');
		});

		jQuery("#kardex").click( function(){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				window.open(\''.site_url("inventario/kardex/kardexpres").'/\'+id, \'_blank\', \'width=420,height=450,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-225), screeny=((screen.availWidth/2)-250)\');
			} else { 
				$.prompt("<h1>Por favor Seleccione un Producto</h1>");
			}
		});
		';

		// Detalle del Registro
		$funciones .= '
		function detalle(mid){
			var ret = $("#newapi'.$grid0.'").getRowData(mid);
			var mSalida = "<table width=\'100%\' cellpadding=1 cellspacing=0>"
			mSalida += "<tr><td width=\'255\'>";

			mSalida += "<table class=\'bordetabla\' cellpadding=1 cellspacing=0 width=\'250\'>";
			mSalida += "<tr class=\'tableheader\'><th>%</th><th>Base</th><th>Precio</th></tr>";
			mSalida += "<tr class=\'littletablerow\'><td align=\'right\'>"+ret.margen1+"</td><td align=\'right\'>"+ret.base1+"</td><td align=\'right\'>"+ret.precio1+"</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td align=\'right\'>"+ret.margen2+"</td><td align=\'right\'>"+ret.base2+"</td><td align=\'right\'>"+ret.precio2+"</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td align=\'right\'>"+ret.margen3+"</td><td align=\'right\'>"+ret.base3+"</td><td align=\'right\'>"+ret.precio3+"</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td align=\'right\'>"+ret.margen4+"</td><td align=\'right\'>"+ret.base4+"</td><td align=\'right\'>"+ret.precio4+"</td></tr>";
			mSalida += "<tr class=\'littletableheaderdet\'><td colspan=3 align=\'center\'>Margen al Mayor: "+ret.mmargen+"</td></tr>";
			mSalida += "</table>";

			mSalida += "</td><td width=\'205\'>";
			mSalida += "<table class=\'bordetabla\' cellpadding=1 cellspacing=0 width=\'200\'>";
			mSalida += "<tr class=\'tableheader\'><th colspan=\'2\'>Codigos Asociados</th></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>Barras         </td><td>"+ret.barras+ "</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>Alterno        </td><td>"+ret.alterno+"</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>Caja           </td><td>"+ret.enlace+ "</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>Nr. Sanitario </td><td>"+ret.mpps+   "</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>C.P.E.</td><td>"+ret.cpe+    "</td></tr>";
			mSalida += "</table>";

			mSalida += "</td><td>";
			mSalida += "<table class=\'bordetabla\' cellpadding=1 cellspacing=0 width=\'120\'>";
			mSalida += "<tr class=\'tableheader\'><th colspan=\'2\'>Medidas</th></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>Peso   </td><td align=\'right\'>"+ret.peso+ "</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>Alto   </td><td align=\'right\'>"+ret.alto+ "</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>Ancho  </td><td align=\'right\'>"+ret.encho+"</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>Largo  </td><td align=\'right\'>"+ret.largo+"</td></tr>";
			mSalida += "<tr class=\'littletablerow\'><td>Unidad </td><td>"+ret.unidad+"</td></tr>";
			mSalida += "</table>";


			mSalida += "</td></tr>";
			mSalida += "</table>";
			return mSalida;
		}


		';

		// Etiquetas
		$funciones .= '
		function etiquetas(){ 
			window.open(\''.site_url("inventario/etiqueta_sinv/menu").'\', \'_blank\', \'width=800, height=600, scrollbars=yes, status=yes, resizable=yes,screenx=((screen.availHeight/2)-300), screeny=((screen.availWidth/2)-400)\');
		};


		';

		// Consulta de Movimiento
		$funciones .= '
		function consulta(){ 
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				window.open(\''.site_url("inventario/sinv/consulta/").'/\'+ret.id, \'_blank\', \'width=800, height=600, scrollbars=yes, status=yes, resizable=yes,screenx=((screen.availHeight/2)-300), screeny=((screen.availWidth/2)-400)\');
			} else { 
				$.prompt("<h1>Por favor Seleccione un Producto</h1>");
			}
		};
		';


		// Redondear Precios
		$funciones .= '
		function redondear(){
			$.prompt( "<h1>Redondear solo cuando el precio sea mayor a:</h1><center><input class=\'inputnum\' type=\'text\' id=\'mayor\' name=\'mayor\' value=\'0.00\' maxlengh=\'10\' size=\'10\' ></center><br/>", {
				buttons: { Redondear: true, Cancelar: false },
				submit: function(e,v,m,f){
					if (v) {
						if( f.mayor > 0 ) {
							$.ajax({ url: "'.site_url('inventario/sinv/redondear').'/"+f.mayor,
							complete: function(){ alert(("Redondeo Finalizado")) }
							});
						} else {
							alert("Debe colocar un numero mayor que 0");
						}
					}
				}
			});
		};
		';


		//Aumento de Precios
		$funciones .= ' 
		function auprec(){
			$.prompt( "<h1>Porcentaje de Aumento o Disminucion (-):</h1><center><input class=\'inputnum\' type=\'text\' id=\'porcen\' name=\'porcen\' value=\'0.00\' maxlengh=\'10\' size=\'10\' ></center><br/>", {
				buttons: { Aplicar: true, Cancelar: false },
				submit: function(e,v,m,f){
					if (v) {
						if( f.porcen > 0 ) {
							$.ajax({ url: "'.site_url('inventario/sinv/auprec').'/"+f.porcen,
							complete: function(){ alert(("Aumento Finalizado")) }
							});
						} else {
							alert("Debe colocar un porcentaje mayor que 0");
						}
					}
				}
			});
		};
		';


		//Aumento de Precios al Mayor
		$funciones .= '
		function auprecm(){
			$.prompt( "<h1>Porcentaje de Aumento o Disminucion (-) Precios al Mayor:</h1><center><input class=\'inputnum\' type=\'text\' id=\'porcen\' name=\'porcen\' value=\'0.00\' maxlengh=\'10\' size=\'10\' ></center><br/>", {
				buttons: { Aplicar: true, Cancelar: false },
				submit: function(e,v,m,f){
					if (v) {
						if( f.porcen > 0 ) {
							$.ajax({ url: "'.site_url('inventario/sinv/auprecm').'/"+f.porcen,
							complete: function(){ alert(("Aumento Finalizado")) }
							});
						} else {
							alert("Debe colocar un porcentaje mayor que 0");
						}
					}
				}
			});
		};
		';


		// Cambiar Ubicaciones
		$funciones .= ' 
		function cambiaubica(){
			$.prompt( "<h1>Cambiar Ubicacion de los productos filtrados):</h1><br/><center><input  type=\'text\' id=\'mubica\' name=\'mubica\' value=\'\' maxlengh=\'9\' size=\'10\' ></center><br/>", {
				buttons: { Aplicar: true, Cancelar: false },
				submit: function(e,v,m,f){
					if (v) {
						$.ajax({ 
							url: "'.site_url('inventario/sinv/cambiaubica').'/"+f.mubica,
							complete: function(){ alert(("Cambio Finalizado")) }
						});
					}
				}
			});
		};
		';


		//Cambia y fusiona codigo
		$funciones .= '
		function sinvcodigo(mviejo){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				var yurl = "";
				$.prompt("<h1>Cambiar el codigo ("+ret.codigo+") por:</h1><center><input type=\'text\' id=\'mcodigo\' name=\'mcodigo\' value=\'"+$.trim(ret.codigo)+"\' maxlengh=\'10\' size=\'15\' ></center><br/>", { 
					buttons: { Cambiar: true, Cancelar: false },
					submit: function(e,v,m,f){
						if (v) {
							if( f.mcodigo == null ){
								alert("Cancelado por el usuario");
							} else if( f.mcodigo == "" ) {
								alert("Cancelado,  Codigo vacio");
							} else if( $.trim(f.mcodigo) == $.trim(ret.codigo) ) {
								alert("No registro ningun cambio");
							} else {
								yurl = encodeURIComponent(mcodigo);
								$.ajax({
									url: \''.site_url('inventario/sinv/sinvcodigoexiste').'\',
									global: false,
									type: "POST",
									data: ({ codigo : encodeURIComponent(mcodigo) }),
									dataType: "text",
									async: false,
									success: function(sino) {
										if (sino.substring(0,1)=="S"){
											confirm(
												"Ya existe el codigo <div style=\"font-size: 200%;font-weight: bold \">"+mcodigo+"</"+"div>"+sino.substring(1)+"<p>si prosigue se eliminara el producto anterior y<br/> todo el movimiento de este, pasara al codigo "+mcodigo+"</"+"p> <p style=\"align: center;\">Desea <strong>Fusionarlos?</"+"strong></"+"p>",
												"Confirmar Fusion",
												function(r){
													if (r) { sinvcodigocambia("S", $.trim(ret.codigo), f.mcodigo ); }
												}
											);
										} else {
											apprise(
												"<h1>Sustitur el codigo actual Por:</h1> <center><h2 style=\"background: #ddeedd\">"+f.mcodigo+"</"+"h2></"+"center> <p>Al cambiar de codigo el producto, todos los<br/> movimientos y estadisticas se cambiaran<br/> correspondientemente.</"+"p> ",
												{"verify":true,"textYes":"Aceptar","textNo":"Cancelar"},
												function(r) {
													if (r) { sinvcodigocambia("N", $.trim(ret.codigo), f.mcodigo); }
												}
											)
										}
									},
									error: function(h,t,e) { alert("Error..codigo="+yurl+" ",e) }
								});
							}
						}
					}
				})

			} else { 
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};

		function sinvcodigocambia( mtipo, mviejo, mcodigo ) {
			var id   = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			var ret  = $("#newapi'.$grid0.'").getRowData(id);
			$.ajax({
				url: \''.site_url('inventario/sinv/sinvcodigo').'\',
				global: false,
				type: "POST",
				data: ({ tipo:  mtipo,
					viejo: encodeURIComponent(mviejo),
					codigo: encodeURIComponent(mcodigo) }),
				dataType: "text",
				async: false,
				success: function(sino) {
					alert("Cambio finalizado "+sino);
					$("#newapi'.$grid0.'").trigger("reloadGrid");
				},
				error: function(h,t,e) {alert("Error.." )}
			});
		};
		';

		
		return $funciones;


	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0 ){
		$bodyscript = '		<script type="text/javascript">';

		$bodyscript .= '
		function sinvadd() {
			$.post("'.site_url('inventario/sinv/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function sinvedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('inventario/sinv/dataedit/modify').'/"+id, function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});
			} else { $.prompt("<h1>Por favor Seleccione un Registro</h1>");}
		};';

		//Wraper de javascript
		$bodyscript .= '
		$(function() {
			$("#dialog:ui-dialog").dialog( "destroy" );
			var mId = 0;
			var montotal = 0;
			var ffecha = $("#ffecha");
			var grid = jQuery("#newapi'.$grid0.'");
			var s;
			var allFields = $( [] ).add( ffecha );
			var tips = $( ".validateTips" );
			s = grid.getGridParam(\'selarrrow\');
			';

		$bodyscript .= '
		$("#fedita").dialog({
			autoOpen: false, height: 600, width: 800, modal: true,
			buttons: {
			"Guardar": function() {
				var bValid = true;
				var murl = $("#df1").attr("action");
				allFields.removeClass( "ui-state-error" );
				$.ajax({
					type: "POST", dataType: "html", async: false,
					url: murl,
					data: $("#df1").serialize(),
					success: function(r,s,x){
						if ( r.length == 0 ) {
							apprise("Registro Guardado");
							$( "#fedita" ).dialog( "close" );
							grid.trigger("reloadGrid");
							return true;
						} else { 
							$("#fedita").html(r);
						}
					}
			})},
			"Cancelar": function() { $( this ).dialog( "close" ); }
			},
			close: function() { allFields.val( "" ).removeClass( "ui-state-error" );}
		});';
		$bodyscript .= '});'."\n";

		$bodyscript .= "\n</script>\n";
		$bodyscript .= "";
		return $bodyscript;
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('activo');
		$grid->label('*');
		$grid->params(array(
			'align'        => '"center"',
			'search'        => 'false',
			'editable'      => $editar,
			'width'         => 20,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
			'formatter'     => 'factivo'
		));

		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 110,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('descrip');
		$grid->label('Descrip');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 260,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:45, maxlength: 45 }',
		));

		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));

		$grid->addField('grupo');
		$grid->label('Grupo');
		$grid->params(array(
			'align'         => '"center"',
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));

		$grid->addField('existen');
		$grid->label('Existencia');
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


		$grid->addField('precio1');
		$grid->label('Precio1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('precio2');
		$grid->label('Precio2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('marca');
		$grid->label('Marca');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:22, maxlength: 22 }',
		));

		$grid->addField('descrip2');
		$grid->label('Descrip2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:45, maxlength: 45 }',
		));


		$grid->addField('unidad');
		$grid->label('Unidad');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('ubica');
		$grid->label('Ubica');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 90,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:9, maxlength: 9 }',
		));


		$grid->addField('clave');
		$grid->label('Clave');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
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


		$grid->addField('enlace');
		$grid->label('Enlace');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('prov1');
		$grid->label('Prov1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('prepro1');
		$grid->label('Prepro1');
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


		$grid->addField('pfecha1');
		$grid->label('Pfecha1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('prov2');
		$grid->label('Prov2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('prepro2');
		$grid->label('Prepro2');
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


		$grid->addField('pfecha2');
		$grid->label('Pfecha2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('prov3');
		$grid->label('Prov3');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('prepro3');
		$grid->label('Prepro3');
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


		$grid->addField('pfecha3');
		$grid->label('Pfecha3');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('pond');
		$grid->label('Pond');
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


		$grid->addField('ultimo');
		$grid->label('Ultimo');
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

		$grid->addField('pvp_s');
		$grid->label('Pvp_s');
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


		$grid->addField('pvp_bs');
		$grid->label('Pvp_bs');
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


		$grid->addField('pvpprc');
		$grid->label('Pvpprc');
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


		$grid->addField('contbs');
		$grid->label('Contbs');
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


		$grid->addField('contprc');
		$grid->label('Contprc');
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


		$grid->addField('mayobs');
		$grid->label('Mayobs');
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


		$grid->addField('mayoprc');
		$grid->label('Mayoprc');
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

		$grid->addField('exmin');
		$grid->label('Exmin');
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


		$grid->addField('exord');
		$grid->label('Exord');
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


		$grid->addField('exdes');
		$grid->label('Exdes');
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


		$grid->addField('fechav');
		$grid->label('Fechav');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('fechac');
		$grid->label('Fechac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('iva');
		$grid->label('Iva');
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


		$grid->addField('fracci');
		$grid->label('Fracci');
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


		$grid->addField('codbar');
		$grid->label('Codbar');
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


		$grid->addField('barras');
		$grid->label('Barras');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('exmax');
		$grid->label('Exmax');
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


		$grid->addField('margen1');
		$grid->label('Margen1');
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


		$grid->addField('margen2');
		$grid->label('Margen2');
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


		$grid->addField('margen3');
		$grid->label('Margen3');
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


		$grid->addField('margen4');
		$grid->label('Margen4');
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


		$grid->addField('base1');
		$grid->label('Base1');
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


		$grid->addField('base2');
		$grid->label('Base2');
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


		$grid->addField('base3');
		$grid->label('Base3');
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


		$grid->addField('base4');
		$grid->label('Base4');
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


		$grid->addField('precio3');
		$grid->label('Precio3');
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


		$grid->addField('serial');
		$grid->label('Serial');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('tdecimal');
		$grid->label('Tdecimal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('dolar');
		$grid->label('Dolar');
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


		$grid->addField('redecen');
		$grid->label('Redecen');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('formcal');
		$grid->label('Formcal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('fordeci');
		$grid->label('Fordeci');
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


		$grid->addField('garantia');
		$grid->label('Garantia');
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


		$grid->addField('costotal');
		$grid->label('Costotal');
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


		$grid->addField('fechac2');
		$grid->label('Fechac2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
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


		$grid->addField('pondcal');
		$grid->label('Pondcal');
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


		$grid->addField('alterno');
		$grid->label('Alterno');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('aumento');
		$grid->label('Aumento');
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


		$grid->addField('modelo');
		$grid->label('Modelo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));



		$grid->addField('clase');
		$grid->label('Clase');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('oferta');
		$grid->label('Oferta');
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


		$grid->addField('fdesde');
		$grid->label('Fdesde');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('fhasta');
		$grid->label('Fhasta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('derivado');
		$grid->label('Derivado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('cantderi');
		$grid->label('Cantderi');
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


		$grid->addField('ppos1');
		$grid->label('Ppos1');
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


		$grid->addField('ppos2');
		$grid->label('Ppos2');
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


		$grid->addField('ppos3');
		$grid->label('Ppos3');
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


		$grid->addField('ppos4');
		$grid->label('Ppos4');
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


		$grid->addField('linea');
		$grid->label('Linea');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:2, maxlength: 2 }',
		));


		$grid->addField('depto');
		$grid->label('Depto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:3, maxlength: 3 }',
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


		$grid->addField('gasto');
		$grid->label('Gasto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:6, maxlength: 6 }',
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


		$grid->addField('bonicant');
		$grid->label('Bonicant');
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


		$grid->addField('standard');
		$grid->label('Standard');
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


		$grid->addField('descufijo');
		$grid->label('Descufijo');
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


		$grid->addField('alto');
		$grid->label('Alto');
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


		$grid->addField('ancho');
		$grid->label('Ancho');
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


		$grid->addField('largo');
		$grid->label('Largo');
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


		$grid->addField('forma');
		$grid->label('Forma');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));


		$grid->addField('exento');
		$grid->label('Exento');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('mmargen');
		$grid->label('Mmargen');
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


		$grid->addField('pm');
		$grid->label('Pm');
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


		$grid->addField('pmb');
		$grid->label('Pmb');
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


		$grid->addField('mmargenplus');
		$grid->label('Mmargenplus');
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


		$grid->addField('escala1');
		$grid->label('Escala1');
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


		$grid->addField('pescala1');
		$grid->label('Pescala1');
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


		$grid->addField('escala2');
		$grid->label('Escala2');
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


		$grid->addField('pescala2');
		$grid->label('Pescala2');
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


		$grid->addField('escala3');
		$grid->label('Escala3');
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


		$grid->addField('pescala3');
		$grid->label('Pescala3');
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


		$grid->addField('mpps');
		$grid->label('Mpps');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));


		$grid->addField('cpe');
		$grid->label('Cpe');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('345');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');


		$grid->setOnSelectRow('
			function(id){
				if (id){
					url= "'.site_url("inventario/fotos/obtener").'/"+id;
					$("#ladicional").html("<center><img src=\'"+url+"\' width=\'160\'></center>");
					$("#radicional").html(detalle(id));
				}
			},
			afterInsertRow:
			function( rid, aData, rowe){
				if ( aData.activo == "N" ){
					$(this).jqGrid( "setCell", rid, "activo","", {color:"#FFFFFF", background:"#960F18" });
					$(this).jqGrid( "setCell", rid, "codigo","", {color:"#FFFFFF", background:"#960F18" });
				}
				if ( aData.tipo == "Servicio" ){
					$(this).jqGrid( "setCell", rid, "tipo","", {color:"#FFFFFF", background:"#0488db" });
				}
				if ( aData.existen < 0 ){
					$(this).jqGrid( "setCell", rid, "existen","", {color:"RED", background:"#FFFFFF", "font-weight":"bold" });
				}
			}
		');


		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('SINV','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('SINV','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('SINV','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('SINV','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: sinvadd,\n\t\teditfunc: sinvedit");

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
		$mWHERE = $grid->geneTopWhere('sinv');

		$mWHERE[] = array('like', 'activo', 'S', '' );


		$response   = $grid->getData('sinv', array(array()), array(), false, $mWHERE, 'codigo' );
		$rs = $grid->jsonresult( $response);


		//Guarda en la BD el Where para usarlo luego
		$querydata = array( 'data1' => $this->session->userdata('dtgQuery') );
		$emp = strpos($querydata['data1'],'WHERE ');

		if ( $emp > 0  ){
			$querydata['data1'] = substr( $querydata['data1'], $emp );
			$emp = strpos($querydata['data1'],'ORDER BY ');
			if ( $emp > 0  ){
				$querydata['data1'] = substr( $querydata['data1'], 0, $emp );
			}
		} else 
			$querydata['data1'] = '';
		
		$ids = $this->datasis->guardasesion($querydata); 
		
		//$querydata = array( 'sinvid' => $ids );
		//$this->session->set_userdata($querydata);


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
		$mcodp  = "codigo";
		$check  = 0;
/*
		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM sinv WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('sinv', $data);
					echo "Registro Agregado";

					logusu('SINV',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM sinv WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM sinv WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE sinv SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("sinv", $data);
				logusu('SINV',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('sinv', $data);
				logusu('SINV',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM sinv WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM sinv WHERE id=$id ");
				logusu('SINV',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
*/
	}
/*
	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'sinv');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->codigo = new inputField('Codigo','codigo');
		$edit->codigo->rule='max_length[15]';
		$edit->codigo->size =17;
		$edit->codigo->maxlength =15;

		$edit->grupo = new inputField('Grupo','grupo');
		$edit->grupo->rule='max_length[4]';
		$edit->grupo->size =6;
		$edit->grupo->maxlength =4;

		$edit->descrip = new inputField('Descrip','descrip');
		$edit->descrip->rule='max_length[45]';
		$edit->descrip->size =47;
		$edit->descrip->maxlength =45;

		$edit->descrip2 = new inputField('Descrip2','descrip2');
		$edit->descrip2->rule='max_length[45]';
		$edit->descrip2->size =47;
		$edit->descrip2->maxlength =45;

		$edit->unidad = new inputField('Unidad','unidad');
		$edit->unidad->rule='max_length[8]';
		$edit->unidad->size =10;
		$edit->unidad->maxlength =8;

		$edit->ubica = new inputField('Ubica','ubica');
		$edit->ubica->rule='max_length[9]';
		$edit->ubica->size =11;
		$edit->ubica->maxlength =9;

		$edit->tipo = new inputField('Tipo','tipo');
		$edit->tipo->rule='max_length[8]';
		$edit->tipo->size =10;
		$edit->tipo->maxlength =8;

		$edit->clave = new inputField('Clave','clave');
		$edit->clave->rule='max_length[8]';
		$edit->clave->size =10;
		$edit->clave->maxlength =8;

		$edit->comision = new inputField('Comision','comision');
		$edit->comision->rule='max_length[5]|numeric';
		$edit->comision->css_class='inputnum';
		$edit->comision->size =7;
		$edit->comision->maxlength =5;

		$edit->enlace = new inputField('Enlace','enlace');
		$edit->enlace->rule='max_length[15]';
		$edit->enlace->size =17;
		$edit->enlace->maxlength =15;

		$edit->prov1 = new inputField('Prov1','prov1');
		$edit->prov1->rule='max_length[5]';
		$edit->prov1->size =7;
		$edit->prov1->maxlength =5;

		$edit->prepro1 = new inputField('Prepro1','prepro1');
		$edit->prepro1->rule='max_length[10]|numeric';
		$edit->prepro1->css_class='inputnum';
		$edit->prepro1->size =12;
		$edit->prepro1->maxlength =10;

		$edit->pfecha1 = new dateField('Pfecha1','pfecha1');
		$edit->pfecha1->rule='chfecha';
		$edit->pfecha1->size =10;
		$edit->pfecha1->maxlength =8;

		$edit->prov2 = new inputField('Prov2','prov2');
		$edit->prov2->rule='max_length[5]';
		$edit->prov2->size =7;
		$edit->prov2->maxlength =5;

		$edit->prepro2 = new inputField('Prepro2','prepro2');
		$edit->prepro2->rule='max_length[10]|numeric';
		$edit->prepro2->css_class='inputnum';
		$edit->prepro2->size =12;
		$edit->prepro2->maxlength =10;

		$edit->pfecha2 = new dateField('Pfecha2','pfecha2');
		$edit->pfecha2->rule='chfecha';
		$edit->pfecha2->size =10;
		$edit->pfecha2->maxlength =8;

		$edit->prov3 = new inputField('Prov3','prov3');
		$edit->prov3->rule='max_length[5]';
		$edit->prov3->size =7;
		$edit->prov3->maxlength =5;

		$edit->prepro3 = new inputField('Prepro3','prepro3');
		$edit->prepro3->rule='max_length[10]|numeric';
		$edit->prepro3->css_class='inputnum';
		$edit->prepro3->size =12;
		$edit->prepro3->maxlength =10;

		$edit->pfecha3 = new dateField('Pfecha3','pfecha3');
		$edit->pfecha3->rule='chfecha';
		$edit->pfecha3->size =10;
		$edit->pfecha3->maxlength =8;

		$edit->pond = new inputField('Pond','pond');
		$edit->pond->rule='max_length[13]|numeric';
		$edit->pond->css_class='inputnum';
		$edit->pond->size =15;
		$edit->pond->maxlength =13;

		$edit->ultimo = new inputField('Ultimo','ultimo');
		$edit->ultimo->rule='max_length[13]|numeric';
		$edit->ultimo->css_class='inputnum';
		$edit->ultimo->size =15;
		$edit->ultimo->maxlength =13;

		$edit->pvp_s = new inputField('Pvp_s','pvp_s');
		$edit->pvp_s->rule='max_length[15]|numeric';
		$edit->pvp_s->css_class='inputnum';
		$edit->pvp_s->size =17;
		$edit->pvp_s->maxlength =15;

		$edit->pvp_bs = new inputField('Pvp_bs','pvp_bs');
		$edit->pvp_bs->rule='max_length[10]|numeric';
		$edit->pvp_bs->css_class='inputnum';
		$edit->pvp_bs->size =12;
		$edit->pvp_bs->maxlength =10;

		$edit->pvpprc = new inputField('Pvpprc','pvpprc');
		$edit->pvpprc->rule='max_length[6]|numeric';
		$edit->pvpprc->css_class='inputnum';
		$edit->pvpprc->size =8;
		$edit->pvpprc->maxlength =6;

		$edit->contbs = new inputField('Contbs','contbs');
		$edit->contbs->rule='max_length[10]|numeric';
		$edit->contbs->css_class='inputnum';
		$edit->contbs->size =12;
		$edit->contbs->maxlength =10;

		$edit->contprc = new inputField('Contprc','contprc');
		$edit->contprc->rule='max_length[6]|numeric';
		$edit->contprc->css_class='inputnum';
		$edit->contprc->size =8;
		$edit->contprc->maxlength =6;

		$edit->mayobs = new inputField('Mayobs','mayobs');
		$edit->mayobs->rule='max_length[10]|numeric';
		$edit->mayobs->css_class='inputnum';
		$edit->mayobs->size =12;
		$edit->mayobs->maxlength =10;

		$edit->mayoprc = new inputField('Mayoprc','mayoprc');
		$edit->mayoprc->rule='max_length[6]|numeric';
		$edit->mayoprc->css_class='inputnum';
		$edit->mayoprc->size =8;
		$edit->mayoprc->maxlength =6;

		$edit->exmin = new inputField('Exmin','exmin');
		$edit->exmin->rule='max_length[12]|numeric';
		$edit->exmin->css_class='inputnum';
		$edit->exmin->size =14;
		$edit->exmin->maxlength =12;

		$edit->exord = new inputField('Exord','exord');
		$edit->exord->rule='max_length[12]|numeric';
		$edit->exord->css_class='inputnum';
		$edit->exord->size =14;
		$edit->exord->maxlength =12;

		$edit->exdes = new inputField('Exdes','exdes');
		$edit->exdes->rule='max_length[12]|numeric';
		$edit->exdes->css_class='inputnum';
		$edit->exdes->size =14;
		$edit->exdes->maxlength =12;

		$edit->existen = new inputField('Existen','existen');
		$edit->existen->rule='max_length[12]|numeric';
		$edit->existen->css_class='inputnum';
		$edit->existen->size =14;
		$edit->existen->maxlength =12;

		$edit->fechav = new dateField('Fechav','fechav');
		$edit->fechav->rule='chfecha';
		$edit->fechav->size =10;
		$edit->fechav->maxlength =8;

		$edit->fechac = new dateField('Fechac','fechac');
		$edit->fechac->rule='chfecha';
		$edit->fechac->size =10;
		$edit->fechac->maxlength =8;

		$edit->iva = new inputField('Iva','iva');
		$edit->iva->rule='max_length[6]|numeric';
		$edit->iva->css_class='inputnum';
		$edit->iva->size =8;
		$edit->iva->maxlength =6;

		$edit->fracci = new inputField('Fracci','fracci');
		$edit->fracci->rule='max_length[11]|integer';
		$edit->fracci->css_class='inputonlynum';
		$edit->fracci->size =13;
		$edit->fracci->maxlength =11;

		$edit->codbar = new inputField('Codbar','codbar');
		$edit->codbar->rule='max_length[11]|integer';
		$edit->codbar->css_class='inputonlynum';
		$edit->codbar->size =13;
		$edit->codbar->maxlength =11;

		$edit->barras = new inputField('Barras','barras');
		$edit->barras->rule='max_length[15]';
		$edit->barras->size =17;
		$edit->barras->maxlength =15;

		$edit->exmax = new inputField('Exmax','exmax');
		$edit->exmax->rule='max_length[12]|numeric';
		$edit->exmax->css_class='inputnum';
		$edit->exmax->size =14;
		$edit->exmax->maxlength =12;

		$edit->margen1 = new inputField('Margen1','margen1');
		$edit->margen1->rule='max_length[6]|numeric';
		$edit->margen1->css_class='inputnum';
		$edit->margen1->size =8;
		$edit->margen1->maxlength =6;

		$edit->margen2 = new inputField('Margen2','margen2');
		$edit->margen2->rule='max_length[6]|numeric';
		$edit->margen2->css_class='inputnum';
		$edit->margen2->size =8;
		$edit->margen2->maxlength =6;

		$edit->margen3 = new inputField('Margen3','margen3');
		$edit->margen3->rule='max_length[6]|numeric';
		$edit->margen3->css_class='inputnum';
		$edit->margen3->size =8;
		$edit->margen3->maxlength =6;

		$edit->margen4 = new inputField('Margen4','margen4');
		$edit->margen4->rule='max_length[6]|numeric';
		$edit->margen4->css_class='inputnum';
		$edit->margen4->size =8;
		$edit->margen4->maxlength =6;

		$edit->base1 = new inputField('Base1','base1');
		$edit->base1->rule='max_length[13]|numeric';
		$edit->base1->css_class='inputnum';
		$edit->base1->size =15;
		$edit->base1->maxlength =13;

		$edit->base2 = new inputField('Base2','base2');
		$edit->base2->rule='max_length[13]|numeric';
		$edit->base2->css_class='inputnum';
		$edit->base2->size =15;
		$edit->base2->maxlength =13;

		$edit->base3 = new inputField('Base3','base3');
		$edit->base3->rule='max_length[13]|numeric';
		$edit->base3->css_class='inputnum';
		$edit->base3->size =15;
		$edit->base3->maxlength =13;

		$edit->base4 = new inputField('Base4','base4');
		$edit->base4->rule='max_length[13]|numeric';
		$edit->base4->css_class='inputnum';
		$edit->base4->size =15;
		$edit->base4->maxlength =13;

		$edit->precio1 = new inputField('Precio1','precio1');
		$edit->precio1->rule='max_length[13]|numeric';
		$edit->precio1->css_class='inputnum';
		$edit->precio1->size =15;
		$edit->precio1->maxlength =13;

		$edit->precio2 = new inputField('Precio2','precio2');
		$edit->precio2->rule='max_length[13]|numeric';
		$edit->precio2->css_class='inputnum';
		$edit->precio2->size =15;
		$edit->precio2->maxlength =13;

		$edit->precio3 = new inputField('Precio3','precio3');
		$edit->precio3->rule='max_length[13]|numeric';
		$edit->precio3->css_class='inputnum';
		$edit->precio3->size =15;
		$edit->precio3->maxlength =13;

		$edit->precio4 = new inputField('Precio4','precio4');
		$edit->precio4->rule='max_length[13]|numeric';
		$edit->precio4->css_class='inputnum';
		$edit->precio4->size =15;
		$edit->precio4->maxlength =13;

		$edit->serial = new inputField('Serial','serial');
		$edit->serial->rule='max_length[1]';
		$edit->serial->size =3;
		$edit->serial->maxlength =1;

		$edit->tdecimal = new inputField('Tdecimal','tdecimal');
		$edit->tdecimal->rule='max_length[1]';
		$edit->tdecimal->size =3;
		$edit->tdecimal->maxlength =1;

		$edit->activo = new inputField('Activo','activo');
		$edit->activo->rule='max_length[1]';
		$edit->activo->size =3;
		$edit->activo->maxlength =1;

		$edit->dolar = new inputField('Dolar','dolar');
		$edit->dolar->rule='max_length[13]|numeric';
		$edit->dolar->css_class='inputnum';
		$edit->dolar->size =15;
		$edit->dolar->maxlength =13;

		$edit->redecen = new inputField('Redecen','redecen');
		$edit->redecen->rule='max_length[1]';
		$edit->redecen->size =3;
		$edit->redecen->maxlength =1;

		$edit->formcal = new inputField('Formcal','formcal');
		$edit->formcal->rule='max_length[1]';
		$edit->formcal->size =3;
		$edit->formcal->maxlength =1;

		$edit->fordeci = new inputField('Fordeci','fordeci');
		$edit->fordeci->rule='max_length[11]|integer';
		$edit->fordeci->css_class='inputonlynum';
		$edit->fordeci->size =13;
		$edit->fordeci->maxlength =11;

		$edit->garantia = new inputField('Garantia','garantia');
		$edit->garantia->rule='max_length[11]|integer';
		$edit->garantia->css_class='inputonlynum';
		$edit->garantia->size =13;
		$edit->garantia->maxlength =11;

		$edit->costotal = new inputField('Costotal','costotal');
		$edit->costotal->rule='max_length[19]|numeric';
		$edit->costotal->css_class='inputnum';
		$edit->costotal->size =21;
		$edit->costotal->maxlength =19;

		$edit->fechac2 = new dateField('Fechac2','fechac2');
		$edit->fechac2->rule='chfecha';
		$edit->fechac2->size =10;
		$edit->fechac2->maxlength =8;

		$edit->peso = new inputField('Peso','peso');
		$edit->peso->rule='max_length[12]|numeric';
		$edit->peso->css_class='inputnum';
		$edit->peso->size =14;
		$edit->peso->maxlength =12;

		$edit->pondcal = new inputField('Pondcal','pondcal');
		$edit->pondcal->rule='max_length[12]|numeric';
		$edit->pondcal->css_class='inputnum';
		$edit->pondcal->size =14;
		$edit->pondcal->maxlength =12;

		$edit->alterno = new inputField('Alterno','alterno');
		$edit->alterno->rule='max_length[15]';
		$edit->alterno->size =17;
		$edit->alterno->maxlength =15;

		$edit->aumento = new inputField('Aumento','aumento');
		$edit->aumento->rule='max_length[7]|numeric';
		$edit->aumento->css_class='inputnum';
		$edit->aumento->size =9;
		$edit->aumento->maxlength =7;

		$edit->modelo = new inputField('Modelo','modelo');
		$edit->modelo->rule='max_length[20]';
		$edit->modelo->size =22;
		$edit->modelo->maxlength =20;

		$edit->marca = new inputField('Marca','marca');
		$edit->marca->rule='max_length[22]';
		$edit->marca->size =24;
		$edit->marca->maxlength =22;

		$edit->clase = new inputField('Clase','clase');
		$edit->clase->rule='max_length[1]';
		$edit->clase->size =3;
		$edit->clase->maxlength =1;

		$edit->oferta = new inputField('Oferta','oferta');
		$edit->oferta->rule='max_length[17]|numeric';
		$edit->oferta->css_class='inputnum';
		$edit->oferta->size =19;
		$edit->oferta->maxlength =17;

		$edit->fdesde = new dateField('Fdesde','fdesde');
		$edit->fdesde->rule='chfecha';
		$edit->fdesde->size =10;
		$edit->fdesde->maxlength =8;

		$edit->fhasta = new dateField('Fhasta','fhasta');
		$edit->fhasta->rule='chfecha';
		$edit->fhasta->size =10;
		$edit->fhasta->maxlength =8;

		$edit->derivado = new inputField('Derivado','derivado');
		$edit->derivado->rule='max_length[15]';
		$edit->derivado->size =17;
		$edit->derivado->maxlength =15;

		$edit->cantderi = new inputField('Cantderi','cantderi');
		$edit->cantderi->rule='max_length[10]|numeric';
		$edit->cantderi->css_class='inputnum';
		$edit->cantderi->size =12;
		$edit->cantderi->maxlength =10;

		$edit->ppos1 = new inputField('Ppos1','ppos1');
		$edit->ppos1->rule='max_length[15]|numeric';
		$edit->ppos1->css_class='inputnum';
		$edit->ppos1->size =17;
		$edit->ppos1->maxlength =15;

		$edit->ppos2 = new inputField('Ppos2','ppos2');
		$edit->ppos2->rule='max_length[15]|numeric';
		$edit->ppos2->css_class='inputnum';
		$edit->ppos2->size =17;
		$edit->ppos2->maxlength =15;

		$edit->ppos3 = new inputField('Ppos3','ppos3');
		$edit->ppos3->rule='max_length[15]|numeric';
		$edit->ppos3->css_class='inputnum';
		$edit->ppos3->size =17;
		$edit->ppos3->maxlength =15;

		$edit->ppos4 = new inputField('Ppos4','ppos4');
		$edit->ppos4->rule='max_length[15]|numeric';
		$edit->ppos4->css_class='inputnum';
		$edit->ppos4->size =17;
		$edit->ppos4->maxlength =15;

		$edit->linea = new inputField('Linea','linea');
		$edit->linea->rule='max_length[2]';
		$edit->linea->size =4;
		$edit->linea->maxlength =2;

		$edit->depto = new inputField('Depto','depto');
		$edit->depto->rule='max_length[3]';
		$edit->depto->size =5;
		$edit->depto->maxlength =3;

		$edit->gasto = new inputField('Gasto','gasto');
		$edit->gasto->rule='max_length[6]';
		$edit->gasto->size =8;
		$edit->gasto->maxlength =6;

		$edit->bonifica = new inputField('Bonifica','bonifica');
		$edit->bonifica->rule='max_length[15]|numeric';
		$edit->bonifica->css_class='inputnum';
		$edit->bonifica->size =17;
		$edit->bonifica->maxlength =15;

		$edit->bonicant = new inputField('Bonicant','bonicant');
		$edit->bonicant->rule='max_length[15]|numeric';
		$edit->bonicant->css_class='inputnum';
		$edit->bonicant->size =17;
		$edit->bonicant->maxlength =15;

		$edit->standard = new inputField('Standard','standard');
		$edit->standard->rule='max_length[19]|numeric';
		$edit->standard->css_class='inputnum';
		$edit->standard->size =21;
		$edit->standard->maxlength =19;

		$edit->modificado = new inputField('Modificado','modificado');
		$edit->modificado->rule='max_length[8]';
		$edit->modificado->size =10;
		$edit->modificado->maxlength =8;

		$edit->descufijo = new inputField('Descufijo','descufijo');
		$edit->descufijo->rule='max_length[10]|numeric';
		$edit->descufijo->css_class='inputnum';
		$edit->descufijo->size =12;
		$edit->descufijo->maxlength =10;

		$edit->alto = new inputField('Alto','alto');
		$edit->alto->rule='max_length[10]|numeric';
		$edit->alto->css_class='inputnum';
		$edit->alto->size =12;
		$edit->alto->maxlength =10;

		$edit->ancho = new inputField('Ancho','ancho');
		$edit->ancho->rule='max_length[10]|numeric';
		$edit->ancho->css_class='inputnum';
		$edit->ancho->size =12;
		$edit->ancho->maxlength =10;

		$edit->largo = new inputField('Largo','largo');
		$edit->largo->rule='max_length[10]|numeric';
		$edit->largo->css_class='inputnum';
		$edit->largo->size =12;
		$edit->largo->maxlength =10;

		$edit->forma = new inputField('Forma','forma');
		$edit->forma->rule='max_length[50]';
		$edit->forma->size =52;
		$edit->forma->maxlength =50;

		$edit->exento = new inputField('Exento','exento');
		$edit->exento->rule='max_length[1]';
		$edit->exento->size =3;
		$edit->exento->maxlength =1;

		$edit->mmargen = new inputField('Mmargen','mmargen');
		$edit->mmargen->rule='max_length[7]|numeric';
		$edit->mmargen->css_class='inputnum';
		$edit->mmargen->size =9;
		$edit->mmargen->maxlength =7;

		$edit->pm = new inputField('Pm','pm');
		$edit->pm->rule='max_length[19]|numeric';
		$edit->pm->css_class='inputnum';
		$edit->pm->size =21;
		$edit->pm->maxlength =19;

		$edit->pmb = new inputField('Pmb','pmb');
		$edit->pmb->rule='max_length[19]|numeric';
		$edit->pmb->css_class='inputnum';
		$edit->pmb->size =21;
		$edit->pmb->maxlength =19;

		$edit->mmargenplus = new inputField('Mmargenplus','mmargenplus');
		$edit->mmargenplus->rule='max_length[7]|numeric';
		$edit->mmargenplus->css_class='inputnum';
		$edit->mmargenplus->size =9;
		$edit->mmargenplus->maxlength =7;

		$edit->escala1 = new inputField('Escala1','escala1');
		$edit->escala1->rule='max_length[12]|numeric';
		$edit->escala1->css_class='inputnum';
		$edit->escala1->size =14;
		$edit->escala1->maxlength =12;

		$edit->pescala1 = new inputField('Pescala1','pescala1');
		$edit->pescala1->rule='max_length[5]|numeric';
		$edit->pescala1->css_class='inputnum';
		$edit->pescala1->size =7;
		$edit->pescala1->maxlength =5;

		$edit->escala2 = new inputField('Escala2','escala2');
		$edit->escala2->rule='max_length[12]|numeric';
		$edit->escala2->css_class='inputnum';
		$edit->escala2->size =14;
		$edit->escala2->maxlength =12;

		$edit->pescala2 = new inputField('Pescala2','pescala2');
		$edit->pescala2->rule='max_length[5]|numeric';
		$edit->pescala2->css_class='inputnum';
		$edit->pescala2->size =7;
		$edit->pescala2->maxlength =5;

		$edit->escala3 = new inputField('Escala3','escala3');
		$edit->escala3->rule='max_length[12]|numeric';
		$edit->escala3->css_class='inputnum';
		$edit->escala3->size =14;
		$edit->escala3->maxlength =12;

		$edit->pescala3 = new inputField('Pescala3','pescala3');
		$edit->pescala3->rule='max_length[5]|numeric';
		$edit->pescala3->css_class='inputnum';
		$edit->pescala3->size =7;
		$edit->pescala3->maxlength =5;

		$edit->mpps = new inputField('Mpps','mpps');
		$edit->mpps->rule='max_length[20]';
		$edit->mpps->size =22;
		$edit->mpps->maxlength =20;

		$edit->cpe = new inputField('Cpe','cpe');
		$edit->cpe->rule='max_length[20]';
		$edit->cpe->size =22;
		$edit->cpe->maxlength =20;

		$edit->build();

		$script= '';

		$data['content'] = $edit->output;
		$data['script'] = $script;
		$this->load->view('jqgrid/ventanajq', $data);

	}


class sinv extends Controller {

	function sinv(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index(){
		$this->datasis->modulo_id('301',1);
		$this->instalar();
		redirect('inventario/sinv/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('datafilter2','datagrid');
		$mSPRV=array(
				'tabla'   =>'sprv',
				'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
				'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
				'retornar'=>array('proveed'=>'proveed'),
				'titulo'  =>'Buscar Proveedor');

		$bSPRV=$this->datasis->modbus($mSPRV);

		$mGRUP=array(
				'tabla'   =>'grup',
				'columnas'=>array(
				'grupo'   =>'Grupo',
				'nom_grup'=>'Nombre',
				'linea'=>'Linea',
				'depto'=>'Depto'),
				'filtro'  =>array('grupo'=>'Grupo','nom_grup'=>'Nombre'),
				'retornar'=>array('grupo'=>'popup_prompt'),
				'titulo'  =>'Buscar Grupo');

		$bGRUP=$this->datasis->modbus($mGRUP);

		$mMARC=array(
				'tabla'   =>'marc',
				'columnas'=>array(
				'marca'   =>'Marca'),
				'filtro'  =>array('marca'=>'Marca'),
				'retornar'=>array('marca'=>'popup_prompt'),
				'titulo'  =>'Buscar Marca');

		$bMARC=$this->datasis->modbus($mMARC);


		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');

		$DepoScript='
		$(document).ready(function(){
			$("#depto").change(function(){
				depto();
				$.post("'.$link2.'",{ depto:$(this).val() },function(data){$("#linea").html(data);})
				$.post("'.$link3.'",{ linea:"" },function(data){$("#grupo").html(data);})
			});
			$("#linea").change(function(){
				linea();
				$.post("'.$link3.'",{ linea:$(this).val() },function(data){$("#grupo").html(data);})
			});

			$("#grupo").change(function(){
				grupo();
			});
			depto();
			linea();
			grupo();
		});

		function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}
			else{
				$("#nom_depto").attr("disabled","");
			}
		}

		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}
			else{
				$("#nom_linea").attr("disabled","");
			}
		}

		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}
			else{
				$("#nom_grupo").attr("disabled","");
			}
		}
		';

		$filter = new DataFilter2('Filtro por Producto');
		$filter->db->select("a.existen, a.marca, a.tipo, a.id, a.codigo, a.descrip, a.precio1, a.precio2, a.precio3, a.precio4, b.nom_grup, b.grupo grupoid, c.descrip nom_linea, c.linea linea, d.descrip nom_depto, d.depto, a.activo, a.mmargen ");
		$filter->db->from('sinv AS a');
		$filter->db->join('grup AS b','a.grupo=b.grupo','LEFT');
		$filter->db->join('line AS c','b.linea=c.linea', 'LEFT');
		$filter->db->join('dpto  d','c.depto=d.depto','LEFT');
		//$filter->db->join('sinvfoto  e','e.codigo=a.codigo','LEFT');
		$filter->script($DepoScript);

		$filter->codigo = new inputField('C&oacute;digo', 'codigo');
		$filter->codigo-> size=15;
		$filter->codigo->group = 'Uno';

		$filter->barras = new inputField('C&oacute;digo de barras', 'barras');
		$filter->barras -> size=25;
		$filter->barras->group = 'Uno';

		$filter->descrip = new inputField("Descripci&oacute;n", 'descrip');
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip-> size=30;
		$filter->descrip->group = 'Uno';

		$filter->tipo = new dropdownField('Tipo', 'tipo');
		$filter->tipo->db_name='a.tipo';
		$filter->tipo->option('','Todos');
		$filter->tipo->option('Articulo' ,'Art&iacute;culo');
		$filter->tipo->option('Servicio' ,'Servicio');
		$filter->tipo->option('Descartar','Descartar');
		$filter->tipo->option('Consumo'  ,'Consumo');
		$filter->tipo->option('Fraccion','Fracci&oacute;n');
		$filter->tipo ->style='width:120px;';
		$filter->tipo->group = 'Uno';

		$filter->clave = new inputField('Clave', 'clave');
		$filter->clave ->size=15;
		$filter->clave->group = 'Uno';

		$filter->activo = new dropdownField('Activo', 'activo');
		$filter->activo->option('','Todos');
		$filter->activo->option('S','Si');
		$filter->activo->option('N','No');
		$filter->activo ->style= 'width:120px;';
		$filter->activo->group = 'Uno';

		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		//$filter->proveed->clause ="in";
		$filter->proveed->db_name='CONCAT_WS("-",`a`.`prov1`, `a`.`prov2`, `a`.`prov3`)';
		//$filter->proveed->db_name='( a.prov1, a.prov2, a.prov3 )';
		$filter->proveed -> size=10;
		$filter->proveed->group = "Dos";

		$filter->depto2 = new inputField("Departamento", "nom_depto");
		$filter->depto2->db_name="d.descrip";
		$filter->depto2 -> size=5;
		$filter->depto2->group = "Dos";

		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, CONCAT(depto,'-',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$filter->depto->in="depto2";
		$filter->depto->group = "Dos";
		$filter->depto->style='width:190px;';

		$filter->linea = new inputField("Linea", "nom_linea");
		$filter->linea->db_name="c.descrip";
		$filter->linea -> size=5;
		$filter->linea->group = "Dos";

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name='c.linea';
		$filter->linea2->option('',"Seleccione un Departamento primero");
		$filter->linea2->in='linea';
		$filter->linea2->group = 'Dos';
		$filter->linea2->style='width:190px;';

		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, CONCAT(linea,'-',descrip) descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo2 = new inputField("Grupo", "nom_grupo");
		$filter->grupo2->db_name="b.nom_grup";
		$filter->grupo2 -> size=5;
		$filter->grupo2->group = "Dos";

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$filter->grupo->in="grupo2";
		$filter->grupo->group = "Dos";
		$filter->grupo->style='width:190px;';

		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option("","Seleccione un Departamento primero");
		}

		$filter->marca = new dropdownField("Marca", "marca");
		$filter->marca->option('','Todas');
		$filter->marca->options("SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca");
		$filter->marca->style='width:190px;';
		$filter->marca->group = "Dos";

		$filter->buttons("reset","search");
		$filter->build("dataformfiltro");

		$uri = "inventario/sinv/dataedit/show/<#codigo#>";

		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."inventario/sinv/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:recalcular(\"P\")'>";
		$mtool .= img(array('src' => 'images/recalcular.jpg', 'alt' => 'Recalcular Precios', 'title' => 'Recalcular Precios','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:recalcular(\"M\")'>";
		$mtool .= img(array('src' => 'images/recalcular.png', 'alt' => 'Recalcular Margenes', 'title' => 'Recalcular Margenes','border'=>'0','height'=>'28'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:redondear()'>";
		$mtool .= img(array('src' => 'images/redondear.jpg', 'alt' => 'Redondear Precios', 'title' => 'Redondear Precios','border'=>'0','height'=>'30'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:auprec()'>";
		$mtool .= img(array('src' => 'images/aprecios.gif', 'alt' => 'Aumento de Precios', 'title' => 'Aumento de Precios','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:auprecm()'>";
		$mtool .= img(array('src' => 'images/price-rise.jpg', 'alt' => 'Aumento de Precios Mayor', 'title' => 'Aumento de Precios Mayor','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";


		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."inventario/etiqueta_sinv/menu', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600"'.'>';
		$mtool .= img(array('src' => 'images/etiquetas.jpg', 'alt' => 'Etiquetas', 'title' => 'Etiquetas','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."reportes/index/sinv', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600" width="900" '.'>';
		$mtool .= img(array('src' => 'images/reportes.gif', 'alt' => 'Reportes', 'title' => 'Reportes','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:cambgrupo()'>";
		$mtool .= img(array('src' => 'images/grupo.jpg', 'alt' => 'Cambiar Grupo', 'title' => 'Cambiar Grupo','border'=>'0','height'=>'30'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:cambmarca()'>";
		$mtool .= img(array('src' => 'images/marca.jpg', 'alt' => 'Cambiar Marca', 'title' => 'Cambiar Marca','border'=>'0','height'=>'30'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."inventario/marc', '_blank', 'width=400, height=500, scrollbars=No, status=No, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="500"'.'>';
		$mtool .= img(array('src' => 'images/tux1.png', 'alt' => 'Gestion de Marcas', 'title' => 'Gestion de Marcas','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."inventario/unidad', '_blank', 'width=340, height=430, scrollbars=No, status=No, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" >';
		$mtool .= img(array('src' => 'images/unidad.gif', 'alt' => 'Gestion de Unidades', 'title' => 'Gestion de Unidades','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";

		$grid = new DataGrid($mtool);
		$grid->order_by("codigo","asc");
		$grid->per_page = 50;
		$link=anchor('/inventario/sinv/dataedit/show/<#id#>','<#codigo#>');

		$uri_2  = anchor('inventario/sinv/dataedit/modify/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12','title'=>'Editar')));
		$uri_2 .= "<a href='javascript:void(0);' ";
		$uri_2 .= 'onclick="window.open(\''.base_url()."inventario/sinv/consulta/<#id#>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600"'.'>';
		$uri_2 .= img(array('src'=>'images/estadistica.jpeg','border'=>'0','alt'=>'Consultar','height'=>'12','title'=>'Consultar'));
		$uri_2 .= "</a>";
		$uri_2 .= "<a href='javascript:void(0);' ";
		$uri_2 .= 'onclick="window.open(\''.base_url()."inventario/fotos/dataedit/<#id#>/create', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600"'.'>';
		$uri_2 .= img(array('src' => 'images/foto.gif', 'alt' => 'Foto', 'title' => 'Foto','border'=>'0','height'=>'12'));
		$uri_2 .= "</a>";
		$uri_2 .= img(array('src'=>'images/<#activo#>.gif','border'=>'0','alt'=>'Estado','title'=>'Estado Activo/Inactivo'));
		$uri_2 .= "<input type='checkbox' name='<#id#>' id='<#id#>' style='height: 10px;'> ";

		$grid->column("Acci&oacute;n",$uri_2     ,"align='center'");
		$grid->column_orderby("C&oacute;digo",$link,"codigo");
		$grid->column_orderby("Descripci&oacute;n","descrip","descrip");
		$grid->column_orderby("Precio 1","<nformat><#precio1#></nformat>","precio1",'align=right');
		$grid->column_orderby("Precio 2","<nformat><#precio2#></nformat>","precio2",'align=right');
		$grid->column_orderby("Existencia","<nformat><#existen#></nformat>","existen",'align=right');
		$grid->column_orderby("Tipo","tipo","tipo");
		$grid->column_orderby("Grupo","grupoid","grupoid");
		$grid->column_orderby("Grupo","nom_grup","nom_grup");
		$grid->column_orderby("Linea","nom_linea","nom_linea");
		$grid->column_orderby("Depto","nom_depto","nom_depto");
		$grid->column_orderby("Precio 3","<nformat><#precio3#></nformat>","precio3",'align=right');
		$grid->column_orderby("Marca","marca","marca");
		$grid->column_orderby("Mayor%","mmargen","mmargen");

		//$grid->add('inventario/sinv/dataedit/create');
		$grid->build('datagridST');

		$lastq = $this->db->last_query();
		$where = substr($lastq,stripos($lastq,"WHERE" ));
		$where = substr($where,0,stripos($where,"ORDER BY" ));

		$from = substr($lastq,stripos($lastq,"FROM" ));
		$from = substr($from,4,stripos($from,"WHERE" )-4);
		//echo $from;

		$id = $this->datasis->guardasesion(array("data1"=>$from,"data2"=>$where));

		$mSQL = "UPDATE $from SET a.precio1=a.precio1*, a.precio2=a.precio2*, a.precio3=a.precio3*, a.precio4=a.precio4* $where";
		//echo $from." id=$id  sesion:".$this->session->userdata('session_id');
		$link1  =site_url('inventario/sinv/redondear');
		$link2  =site_url('inventario/sinv/recalcular');
		$link3  =site_url("inventario/sinv/auprec/$id");
		$link4  =site_url("inventario/sinv/sinvcamgrup/");
		$link5  =site_url("inventario/sinv/sinvcammarca/");
		$link6  =site_url("inventario/sinv/auprecm/$id");

		$script = '
		<script type="text/javascript">
		function isNumeric(value) {
		  if (value == null || !value.toString().match(/^[-]?\d*\.?\d*$/)) return false;
		  return true;
		};

		function redondear(){
			var mayor=prompt("Redondear precios Mayores a");
			if( mayor==null){
				alert("Cancelado");
			} else {
				if( isNumeric(mayor) ){
					$.ajax({ url: "'.$link1.'/"+mayor,
					complete: function(){ alert(("Redondeo Finalizado")) }
					});
				} else {
					alert("Entrada no numerica");
				}
			}
		};

		function recalcular(mtipo){
			var seguro = true;
			if(mtipo == "P"){
				seguro = confirm("Recalcular margenes dejando fijos los precios ");
			} else {
				seguro = confirm("Recalcular margenes, dejando fijos los precios ");
			}
			if( seguro){
				$.ajax({ url: "'.$link2.'/"+mtipo,
					complete: function(){ alert(("Recalculo Finalizado")) }
				})
			}
		};

		function auprec(){
			var porcen=prompt("Porcentaje de Aumento?");
			if( porcen ==null){
				alert("Cancelado");
			} else {
				if( isNumeric(porcen) ){
					$.ajax({ url: "'.$link3.'/"+porcen,
					complete: function(){ alert(("Aumento Finalizado")) }
					});
				} else {
					alert("Entrada no numerica");
				}
			}
		};


		function auprecm(){
			var porcen=prompt("Porcentaje de Aumento Mayor?");
			if( porcen ==null){
				alert("Cancelado");
			} else {
				if( isNumeric(porcen) ){
					$.ajax({ url: "'.$link6.'/"+porcen,
					complete: function(){ alert(("Aumento Finalizado")) }
					});
				} else {
					alert("Entrada no numerica");
				}
			}
		};


		function cambgrupo(){
			var yurl = "";
			var n = $("input:checked").length;
			var a = "";
			var mbusca = "'.addslashes($bGRUP).'";

			$("input:checked").each( function() { a += this.id+","; });

			if( n==0) {
				jAlert("No hay productos Seleccionados","Informacion");
			}else{
			jPrompt("Selecciono "+n+" Productos<br>Introduzca el Grupo "+mbusca,"" ,"Cambiar de Grupo", function(mgrupo){
				if( mgrupo==null ){
					jAlert("Cancelado por el usuario","Informacion");
				} else if( mgrupo=="" ) {
					jAlert("Cancelado,  Grupo vacio","Informacion");
				} else {
					yurl = encodeURIComponent(mgrupo);
					$.ajax({
						url: "'.$link4.'",
						global: false,
						type: "POST",
						data: ({ grupo : encodeURIComponent(mgrupo), productos : a }),
						dataType: "text",
						async: false,
						success: function(sino) {
						jAlert(sino,"Informacion");
						jConfirm( "Actualizar","Recargar Tabla y perder los checks?" , function(r){
							if(r) {
								location.reload();
							}
							});
						},
						error: function(h,t,e)  { jAlert("Error..codigo="+yurl+" ",e) }
					});
				}
			})
			}
		};


		function cambmarca(){
			var yurl = "";
			var n = $("input:checked").length;
			var a = "";
			var mbusca = "'.addslashes($bMARC).'";

			$("input:checked").each( function() { a += this.id+","; });

			if( n==0) {
				jAlert("No hay productos Seleccionados","Informacion");
			}else{
			jPrompt("Selecciono "+n+" Productos<br>Introduzca la Marca "+mbusca,"" ,"Cambiar Marca", function(mmarca){
				if( mmarca==null ){
					jAlert("Cancelado por el usuario","Informacion");
				} else if( mmarca=="" ) {
					jAlert("Cancelado, Marca vacia","Informacion");
				} else {
					yurl = encodeURIComponent(mmarca);
					$.ajax({
						url: "'.$link5.'",
						global: false,
						type: "POST",
						data: ({ marca : encodeURIComponent(mmarca), productos : a }),
						dataType: "text",
						async: false,
						success: function(sino) {
						jAlert(sino,"Informacion");
						location.reload();
						},
						error: function(h,t,e)  { jAlert("Error..codigo="+yurl+" ",e) }
					});
				}
			})
			}
		};
		</script>';

		// *************************************
		//
		//       Para usar SuperTable
		//
		// *************************************
		$extras = '<script type="text/javascript">
		//<![CDATA[
		(function() {
			var mySt = new superTable("demoTable", {
			cssSkin : "sSky",
			fixedCols : 1,
			headerRows : 1,
			onStart : function () {	this.start = new Date();},
			onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
			});
		})();
		//]]>
		</script>';

		$style ='<style type="text/css">
		.fakeContainer { // The parent container 
		    margin: 5px;
		    padding: 0px;
		    border: none;
		    width: 740px; // Required to set 
		    height: 320px; // Required to set 
		    overflow: hidden; // Required to set 
		}
		</style>';

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('jquery.alerts.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('superTables.js');
		$data['script'] .= $script;
		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['style']  .= style('jquery.alerts.css');
		$data['extras']  = $extras;
		$data['title']   = heading('Maestro de Inventario ');
		$data['head']    = $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}
*/

	// *********************************************************************************************************
	//
	//   DATAEDIT
	//
	// *********************************************************************************************************
	function dataedit($status='',$id='' ) {
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataedit','datadetails');

		$modbus = array(
			'tabla' => 'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro' => array('codigo' => 'C&oacute;digo'
			,'descrip' => 'Descripci&oacute;n')
			,'retornar'  => array(
				 array('codigo'  => 'itcodigo_<#i#>')
				,array('descrip' => 'itdescrip_<#i#>')
				,array('descrip' => 'itdescrip_<#i#>_val')
				,array('formcal' => 'itformcal_<#i#>')
				,array('ultimo'  => 'itultimo_<#i#>_val')
				,array('ultimo'  => 'itultimo_<#i#>')
				,array('pond'    => 'itpond_<#i#>')
				,array('pond'    => 'itpond_<#i#>_val')
				,array('base1'   => 'itprecio1_<#i#>')
			),
			'p_uri' => array(4 => '<#i#>'),
			'titulo' => 'Buscar Articulo',
			'where' => '`activo` = "S"',
			'script' => array('totalizar()')
		);
		$bSINV_C = $this->datasis->p_modbus($modbus, '<#i#>');

		$modbus = array(
			'tabla' => 'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro' => array('codigo' => 'C&oacute;digo'
			,'descrip' => 'Descripci&oacute;n')
			,'retornar'  => array(
				 array('codigo'  => 'it2codigo_<#i#>')
				,array('descrip' => 'it2descrip_<#i#>')
				,array('descrip' => 'it2descrip_<#i#>_val')
				,array('formcal' => 'it2formcal_<#i#>')
				,array('ultimo'  => 'it2ultimo_<#i#>')
				,array('pond'    => 'it2pond_<#i#>')
				,array('id'      => 'it2id_sinv_<#i#>')

			),
			'p_uri' => array(4 => '<#i#>'),
			'titulo' => 'Buscar Articulo',
			'where' => '`activo` = "S"',
			'script' => array('totalizarpitem()')
		);
		$bSINV_I = $this->datasis->p_modbus($modbus, '<#i#>',800,600,'sinv_i');

		$do = new DataObject('sinv');
		$do->pointer('grup' , 'grup.grupo=sinv.grupo' , 'grup.grupo AS grupgrupo' , 'left');
		$do->pointer('line' , 'line.linea=grup.linea' , 'line.linea AS linelinea' , 'left');
		$do->pointer('dpto' , 'dpto.depto=line.depto' , 'dpto.depto AS dptodepto' , 'left');
		$do->rel_one_to_many('sinvcombo' , 'sinvcombo' , array('codigo' => 'combo'));
		$do->rel_one_to_many('sinvpitem' , 'sinvpitem' , array('codigo' => 'producto'));
		$do->rel_one_to_many('sinvplabor', 'sinvplabor', array('codigo' => 'producto'));
		$do->rel_pointer('sinvcombo'     , 'sinv AS p' , 'p.codigo=sinvcombo.codigo', 'p.descrip AS sinvdescrip,p.pond AS sinvpond,p.ultimo sinvultimo,p.formcal sinvformcal,p.precio1 sinvprecio1');

		if($status=='create' && !empty($id)){
			$do->load($id);
			$do->set('codigo', '');
		}

		$edit = new DataDetails('Maestro de Inventario', $do);
		$edit->pre_process( 'insert','_pre_inserup');
		$edit->pre_process( 'update','_pre_inserup');
		$edit->pre_process( 'delete','_pre_del'    );
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->back_url = site_url('inventario/sinv/filteredgrid');

		$ultimo ='<a href="javascript:ultimo();" title="Consultar ultimo c&oacute;digo ingresado"> Consultar ultimo c&oacute;digo</a>';
		$sugerir='<a href="javascript:sugerir();" title="Sugerir un C&oacute;digo aleatorio">Sugerir C&oacute;digo </a>';

		$edit->codigo = new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->size=15;
		$edit->codigo->maxlength=15;
		$edit->codigo->rule = 'trim|required|strtoupper|callback_chexiste';
		$edit->codigo->mode = 'autohide';
		$edit->codigo->append($sugerir);
		$edit->codigo->append($ultimo);

		$edit->alterno = new inputField('Codigo Alterno', 'alterno');
		$edit->alterno->size=15;
		$edit->alterno->maxlength=15;
		$edit->alterno->rule = 'trim|strtoupper|unique';

		$edit->enlace  = new inputField('Caja', 'enlace');
		$edit->enlace ->size=15;
		$edit->enlace->maxlength=15;
		$edit->enlace->rule = 'trim';
		//$edit->enlace->append('Solo si es fracci&oacute;n');

		$edit->aumento = new inputField('Aumento %', 'aumento');
		$edit->aumento->css_class='inputnum';
		$edit->aumento->size=5;
		$edit->aumento->maxlength=8;
		$edit->aumento->autocomplete=false;
		$edit->aumento->rule='numeric';
		$edit->aumento->autocomplete = false;
		$edit->aumento->append('Solo si es fracci&oacute;n');

		$edit->barras = new inputField('C&oacute;digo Barras', 'barras');
		$edit->barras->size=15;
		$edit->barras->maxlength=15;
		$edit->barras->rule = 'trim';

		$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->style='width:100px;';
		$edit->tipo->option('Articulo' ,'Art&iacute;culo');
		$edit->tipo->option('Servicio' ,'Servicio');
		$edit->tipo->option('Descartar','Descartar');
		$edit->tipo->option('Fraccion' ,'Fracci&oacute;n');
		$edit->tipo->option('Lote'     ,'Lote');
		$edit->tipo->option('Combo'    ,'Combo');
		//$edit->tipo->option('Consumo','Consumo');

		$AddUnidad='<a href="javascript:add_unidad();" title="Haz clic para Agregar una unidad nueva">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->unidad = new dropdownField('Unidad','unidad');
		$edit->unidad->style='width:100px;';
		$edit->unidad->option('','Seleccionar');
		$edit->unidad->options('SELECT unidades, unidades AS valor FROM unidad ORDER BY unidades');
		$edit->unidad->append($AddUnidad);

		$edit->clave = new inputField('Clave', 'clave');
		$edit->clave->size=10;
		$edit->clave->maxlength=8;
		$edit->clave->rule = 'trim|strtoupper';

		$edit->ubica = new inputField('Ubicaci&oacute;n', 'ubica');
		$edit->ubica->size=10;
		$edit->ubica->maxlength=8;
		$edit->ubica->rule = 'trim|strtoupper';

		$AddDepto='<a href="javascript:add_depto();" title="Haz clic para Agregar un nuevo Departamento">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->depto = new dropdownField('Departamento', 'depto');
		$edit->depto->rule ='required';
		$edit->depto->style='width:300px;white-space:nowrap;';
		$edit->depto->option('','Seleccione un Departamento');
		$edit->depto->options('SELECT depto, CONCAT(depto,\'-\',descrip) descrip FROM dpto WHERE tipo=\'I\' ORDER BY depto');
		$edit->depto->append($AddDepto);
		$edit->depto->db_name='dptodepto';
		$edit->depto->pointer=true;

		$AddLinea='<a href="javascript:add_linea();" title="Haz clic para Agregar una nueva Linea;">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->linea = new dropdownField('L&iacute;nea','linea');
		$edit->linea->rule ='required';
		$edit->linea->style='width:300px;';
		$edit->linea->append($AddLinea);
		$edit->linea->db_name='linelinea';
		$edit->linea->pointer=true;
		$depto=$edit->getval('depto');
		if($depto!==FALSE){
			$dbdepto=$this->db->escape($depto);
			$edit->linea->options("SELECT linea, CONCAT(LINEA,'-',descrip) descrip FROM line WHERE depto=$dbdepto ORDER BY descrip");
		}else{
			$edit->linea->option('','Seleccione un Departamento primero');
		}


		$AddGrupo='<a href="javascript:add_grupo();" title="Haz clic para Agregar un nuevo Grupo;">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->grupo = new dropdownField('Grupo', 'grupo');
		$edit->grupo->rule ='required';
		$edit->grupo->style='width:300px;';
		$edit->grupo->append($AddGrupo);

		$linea=$edit->getval('linea');
		if($linea!==FALSE){
			$dblinea=$this->db->escape($linea);
			$edit->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea=$dblinea ORDER BY nom_grup");
		}else{
			$edit->grupo->option('','Seleccione un Departamento primero');
		}

		$edit->comision  = new inputField('Comisi&oacute;n %', 'comision');
		$edit->comision ->size=7;
		$edit->comision->maxlength=5;
		$edit->comision->css_class='inputnum';
		$edit->comision->rule='numeric|callback_positivo|trim';

		$edit->fracci  = new inputField('Fracci&oacute;n x Unid.', 'fracci');
		$edit->fracci ->size=10;
		$edit->fracci->maxlength=4;
		$edit->fracci->css_class='inputnum';
		$edit->fracci->rule='numeric|callback_positivo|trim';

		$edit->activo = new dropdownField('Activo', 'activo');
		$edit->activo->style='width:50px;';
		$edit->activo->option('S','Si');
		$edit->activo->option('N','No');

		$edit->serial2 = new freeField('','free','Serial');
		$edit->serial2->in='activo';

		$edit->serial = new dropdownField ('Usa Seriales', 'serial');
		$edit->serial->style='width:50px;';
		$edit->serial->option('N','No');
		$edit->serial->option('S','Si');
		$edit->serial->in='activo';

		$edit->tdecimal2 = new freeField('','free','Usa Decimales');
		$edit->tdecimal2->in='activo';

		$edit->tdecimal = new dropdownField('Usa Decimales', 'tdecimal');
		$edit->tdecimal->style='width:50px;';
		$edit->tdecimal->option('N','No');
		$edit->tdecimal->option('S','Si');
		$edit->tdecimal->in='activo';

		$edit->descrip = new inputField('Descripci&oacute;n', 'descrip');
		$edit->descrip->size=45;
		$edit->descrip->maxlength=45;
		$edit->descrip->rule = 'trim|required|strtoupper';

		$edit->descrip2 = new inputField('Descripci&oacute;n adicional', 'descrip2');
		$edit->descrip2->size=45;
		$edit->descrip2->maxlength=45;
		$edit->descrip2->rule = 'trim|strtoupper';

		$edit->peso  = new inputField('Peso', 'peso');
		$edit->peso->size=10;
		$edit->peso->maxlength=12;
		$edit->peso->css_class='inputnum';
		$edit->peso->rule='numeric|callback_positivo|trim';

		$edit->alto = new inputField('Alto', 'alto');
		$edit->alto->size=10;
		$edit->alto->maxlength=12;
		$edit->alto->css_class='inputnum';
		$edit->alto->rule='numeric|callback_positivo|trim';

		$edit->ancho = new inputField('Ancho', 'ancho');
		$edit->ancho->size=10;
		$edit->ancho->maxlength=12;
		$edit->ancho->css_class='inputnum';
		$edit->ancho->rule='numeric|callback_positivo|trim';

		$edit->largo = new inputField('Largo', 'largo');
		$edit->largo->size=10;
		$edit->largo->maxlength=12;
		$edit->largo->css_class='inputnum';
		$edit->largo->rule='numeric|callback_positivo|trim';

		$edit->garantia = new inputField('Garantia', 'garantia');
		$edit->garantia->size=5;
		$edit->garantia->maxlength=3;
		$edit->garantia->css_class='inputonlynum';
		$edit->garantia->rule='numeric|callback_positivo|trim';

		$AddMarca='<a href="javascript:add_marca();" title="Haz clic para Agregar una marca nueva">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->marca = new dropdownField('Marca', 'marca');
		$edit->marca->rule = 'required';
		$edit->marca->style='width:180px;';
		$edit->marca->option('','Seleccionar');
		$edit->marca->options('SELECT marca AS codigo, marca FROM marc ORDER BY marca');
		$edit->marca->append($AddMarca);

		$edit->modelo  = new inputField('Modelo', 'modelo');
		$edit->modelo->size=24;
		$edit->modelo->maxlength=20;
		$edit->modelo->rule = 'trim|strtoupper';

		$edit->clase= new dropdownField('Clase', 'clase');
		$edit->clase->style='width:100px;';
		$edit->clase->option('A','Alta Rotacion');
		$edit->clase->option('B','Media Rotacion');
		$edit->clase->option('C','Baja Rotacion');
		$edit->clase->option('I','Importacion Propia');

		$ivas=$this->datasis->ivaplica();
		$edit->iva = new dropdownField('IVA %', 'iva');
		foreach($ivas as $tasa=>$ivamonto){
			$edit->iva->option($ivamonto,nformat($ivamonto));
		}
		$edit->iva->style='width:100px;';
		$edit->iva->insertValue=$ivas['tasa'];
		$edit->iva->onchange='calculos(\'S\');';

		$edit->exento = new dropdownField('Vender Exento', 'exento');
		$edit->exento->style='width:50px;';
		$edit->exento->option('N','No' );
		$edit->exento->option('E','Si' );

		$edit->ultimo = new inputField('Ultimo', 'ultimo');
		$edit->ultimo->css_class='inputnum';
		$edit->ultimo->size=10;
		$edit->ultimo->maxlength=13;
		$edit->ultimo->autocomplete=false;
		$edit->ultimo->onkeyup = 'calculos(\'S\');';
		$edit->ultimo->rule='required|mayorcero';
		$edit->ultimo->autocomplete = false;

		$edit->pond = new inputField('Promedio', 'pond');
		$edit->pond->css_class='inputnum';
		$edit->pond->size=10;
		$edit->pond->maxlength=13;
		$edit->pond->autocomplete=false;
		$edit->pond->onkeyup = 'calculos(\'S\');';
		$edit->pond->rule='required|mayorcero';
		$edit->pond->autocomplete = false;

		$edit->standard = new inputField('Standard', 'standard');
		$edit->standard->css_class='inputnum';
		$edit->standard->autocomplete=false;
		$edit->standard->size=10;
		$edit->standard->maxlength=13;
		$edit->standard->insertValue=0;
		$edit->standard->autocomplete = false;

		$edit->formcal = new dropdownField('Base C&aacute;lculo', 'formcal');
		$edit->formcal->style='width:110px;';
		$edit->formcal->rule='required|enum[U,P,M,S]';
		$edit->formcal->option('U','Ultimo');
		$edit->formcal->option('P','Promedio');
		$edit->formcal->option('M','Mayor');
		$edit->formcal->insertValue='U';
		$edit->formcal->onchange = 'requeridos();calculos(\'S\');';

		$edit->redecen = new dropdownField('Redondear', 'redecen');
		$edit->redecen->style='width:110px;';
		$edit->redecen->option('N','No Cambiar');
		$edit->redecen->option('M','Solo un Decimal');
		$edit->redecen->option('F','Sin Decimales');
		$edit->redecen->option('D','Decenas');
		$edit->redecen->option('C','Centenas');
		$edit->redecen->rule='enum[N,M,F,D,C]';
		$edit->redecen->onchange='calculos(\'S\');';

		for($i=1;$i<=4;$i++){
			$objeto="margen$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=6;
			$edit->$objeto->onkeyup = 'calculos(\'I\');';
			$edit->$objeto->autocomplete=false;
			$edit->$objeto->rule='required|mayorcero';

			$objeto="base$i";
			$edit->$objeto = new inputField("Base $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->autocomplete=false;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onkeyup = 'cambiobase(\'I\');';
			$edit->$objeto->rule='required|mayorcero';

			$objeto="precio$i";
			$edit->$objeto = new inputField("Precio $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->autocomplete=false;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onkeyup = 'cambioprecio(\'I\');';
			$edit->$objeto->rule='required|mayorcero';
		}

		$edit->existen = new inputField('Cantidad Actual','existen');
		$edit->existen->size=10;
		$edit->existen->readonly = true;
		$edit->existen->css_class='inputonlynum';
		$edit->existen->style='background:#F5F6CE;';

		$edit->exmin = new inputField('M&iacute;nimo', 'exmin');
		$edit->exmin->size=10;
		$edit->exmin->maxlength=12;
		$edit->exmin->css_class='inputonlynum';
		$edit->exmin->rule='numeric|callback_positivo|trim';

		$edit->exmax = new inputField('M&aacute;ximo', 'exmax');
		$edit->exmax->size=10;
		$edit->exmax->maxlength=12;
		$edit->exmax->css_class='inputonlynum';
		$edit->exmax->rule='numeric|callback_positivo|trim';

		$edit->exord = new inputField('Orden Proveedor','exord');
		$edit->exord->readonly = true;
		$edit->exord->size=10;
		$edit->exord->css_class='inputonlynum';
		$edit->exord->style='background:#F5F6CE;';

		$edit->exdes = new inputField('Pedidos Cliente','exdes');
		$edit->exdes->readonly = true;
		$edit->exdes->size=10;
		$edit->exdes->css_class='inputonlynum';
		$edit->exdes->style='background:#F5F6CE;';

		$edit->fechav = new dateField('Ultima Venta','fechav','d/m/Y');
		$edit->fechav->readonly = true;
		$edit->fechav->size=10;

		$edit->fdesde = new dateField('Desde','fdesde','d/m/Y');
		$edit->fdesde->size=10;

		$edit->fhasta = new dateField('Desde','fhasta','d/m/Y');
		$edit->fhasta->size=10;

		$edit->bonicant = new inputField('Cant. Bonifica', 'bonicant');
		$edit->bonicant->size=10;
		$edit->bonicant->maxlength=12;
		$edit->bonicant->css_class='inputonlynum';
		$edit->bonicant->rule='numeric|callback_positivo|trim';

		$edit->bonifica = new inputField('Bonifica', 'bonifica');
		$edit->bonifica->size=10;
		$edit->bonifica->maxlength=12;
		$edit->bonifica->css_class='inputonlynum';
		$edit->bonifica->rule='numeric|callback_positivo|trim';

		//descuentos por escala
		for($i=1;$i<=3;$i++){
			$objeto="pescala$i";
			$edit->$objeto = new inputField('Descuento por escala '.$i,$objeto);
			$edit->$objeto->rule='numeric|callback_positivo|trim';
			$edit->$objeto->insertValue=0;
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=5;
			$edit->$objeto->autocomplete=false;

			$objeto="escala$i";
			$edit->$objeto = new inputField('Cantidad m&iacute;nima para la escala '.$i,$objeto);
			$edit->$objeto->rule='numeric|callback_positivo|trim';
			$edit->$objeto->insertValue=0;
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->autocomplete=false;
		}

		for($i=1;$i<=3;$i++){
			$objeto="pfecha$i";
			$edit->$objeto = new dateField("Fecha $i",$objeto,'d/m/Y');
			$edit->$objeto->when =array('show');
			$edit->$objeto->size=10;

			$objeto="Eprepro$i";
			$edit->$objeto = new freeField('','','Precio');
			$edit->$objeto->in="pfecha$i";
			$edit->$objeto->when =array('show');

			$objeto="prepro$i";
			$edit->$objeto = new inputField('',$objeto);
			$edit->$objeto->when =array('show');
			$edit->$objeto->size=10;
			$edit->$objeto->in="pfecha$i";

			$objeto="prov$i";
			$edit->$objeto = new inputField('',$objeto);
			$edit->$objeto->when =array('show');
			$edit->$objeto->size=10;

			$objeto="Eprov$i";
			$edit->$objeto = new freeField('','','Proveedor');
			$edit->$objeto->in="pfecha$i";
			$edit->$objeto->when =array('show');

			if($edit->_status=='show'){
				$prov=$edit->_dataobject->get('prov'.$i);
				$dbprov=$this->db->escape($prov);
				$proveed=$this->datasis->dameval("SELECT nombre FROM sprv WHERE proveed=$dbprov LIMIT 1");
				$objeto="proveed$i";
				$edit->$objeto= new freeField('','',$proveed);
				$edit->$objeto->in="pfecha$i";
			}
		}

		$codigo=$edit->_dataobject->get('codigo');
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array('show','modify');

		$edit->mmargen = new inputField('Margen al Mayor','mmargen');
		$edit->mmargen->css_class='inputnum';
		$edit->mmargen->size=10;
		$edit->mmargen->insertValue=0;
		$edit->mmargen->maxlength=10;

		$edit->mmargenplus = new inputField('Descuento +','mmargenplus');
		$edit->mmargenplus->css_class='inputnum';
		$edit->mmargenplus->insertValue=0;
		$edit->mmargenplus->size=10;
		$edit->mmargenplus->maxlength=10;

		$edit->pm = new inputField('Descuento al Mayor A','pm');
		$edit->pm->css_class='inputnum';
		$edit->pm->size=10;
		$edit->pm->insertValue=0;
		$edit->pm->maxlength=10;

		$edit->pmb = new inputField('Descuento al Mayor B','pmb');
		$edit->pmb->css_class='inputnum';
		$edit->pmb->insertValue=0;
		$edit->pmb->size=10;
		$edit->pmb->maxlength=10;

		/*INICIO SINV COMBO*/
		$edit->itcodigo = new inputField('C&oacute;digo <#o#>', 'itcodigo_<#i#>');
		$edit->itcodigo->size    = 12;
		$edit->itcodigo->db_name = 'codigo';
		$edit->itcodigo->rel_id  = 'sinvcombo';
		$edit->itcodigo->append($bSINV_C);

		$edit->itdescrip = new inputField('Descripci&oacute;n <#o#>', 'itdescrip_<#i#>');
		$edit->itdescrip->size       = 32;
		$edit->itdescrip->db_name    = 'descrip';
		$edit->itdescrip->maxlength  = 50;
		$edit->itdescrip->readonly   = true;
		$edit->itdescrip->rel_id     = 'sinvcombo';
		$edit->itdescrip->type       = 'inputhidden';

		$edit->itcantidad = new inputField('Cantidad <#o#>', 'itcantidad_<#i#>');
		$edit->itcantidad->db_name      = 'cantidad';
		$edit->itcantidad->css_class    = 'inputnum';
		$edit->itcantidad->rel_id       = 'sinvcombo';
		$edit->itcantidad->maxlength    = 10;
		$edit->itcantidad->size         = 5;
		$edit->itcantidad->rule         = 'required|positive';
		$edit->itcantidad->autocomplete = false;
		$edit->itcantidad->onkeyup      = 'totalizar();';
		$edit->itcantidad->value        = '1';

		$edit->itultimo = new inputField('Ultimo <#o#>', 'itultimo_<#i#>');
		$edit->itultimo->size       = 32;
		$edit->itultimo->db_name    = 'ultimo';
		$edit->itultimo->maxlength  = 50;
		$edit->itultimo->readonly   = true;
		$edit->itultimo->rel_id     = 'sinvcombo';
		$edit->itultimo->type       = 'inputhidden';

		$edit->itpond = new inputField('Promedio <#o#>', 'itpond_<#i#>');
		$edit->itpond->size       = 32;
		$edit->itpond->db_name    = 'pond';
		$edit->itpond->maxlength  = 50;
		$edit->itpond->readonly   = true;
		$edit->itpond->rel_id     = 'sinvcombo';
		$edit->itpond->type       = 'inputhidden';

		$ocultos=array('precio1','formcal');
		foreach($ocultos as $obj){
			$obj2='it'.$obj;
			$edit->$obj2 = new hiddenField($obj.' <#o#>', $obj2 . '_<#i#>');
			$edit->$obj2->db_name = 'sinv'.$obj;
			$edit->$obj2->rel_id  = 'sinvcombo';
			$edit->$obj2->pointer = true;
		}

		$edit->itestampa = new autoUpdateField('itestampa' ,date('Ymd'), date('Ymd'));
		$edit->itestampa->db_name = 'estampa';
		$edit->itestampa->rel_id  = 'sinvcombo';

		$edit->ithora    = new autoUpdateField('ithora',date('H:i:s'), date('H:i:s'));
		$edit->ithora->db_name = 'hora';
		$edit->ithora->rel_id  = 'sinvcombo';

		$edit->itusuario = new autoUpdateField('itusuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->itusuario->db_name = 'usuario';
		$edit->itusuario->rel_id  = 'sinvcombo';

		/*INICIO SINV ITEM RECETAS*/
		$edit->it2codigo = new inputField('C&oacute;digo <#o#>', 'it2codigo_<#i#>');
		$edit->it2codigo->size    = 12;
		$edit->it2codigo->db_name = 'codigo';
		$edit->it2codigo->rel_id  = 'sinvpitem';
		$edit->it2codigo->append($bSINV_I);

		$edit->it2descrip = new inputField('Descripci&oacute;n <#o#>', 'it2descrip_<#i#>');
		$edit->it2descrip->size       = 32;
		$edit->it2descrip->db_name    = 'descrip';
		$edit->it2descrip->maxlength  = 50;
		$edit->it2descrip->readonly   = true;
		$edit->it2descrip->rel_id     = 'sinvpitem';
		$edit->it2descrip->type       = 'inputhidden';

		$edit->it2cantidad = new inputField('Cantidad <#o#>', 'it2cantidad_<#i#>');
		$edit->it2cantidad->db_name      = 'cantidad';
		$edit->it2cantidad->css_class    = 'inputnum';
		$edit->it2cantidad->rel_id       = 'sinvpitem';
		$edit->it2cantidad->maxlength    = 10;
		$edit->it2cantidad->size         = 5;
		$edit->it2cantidad->rule         = 'positive';
		$edit->it2cantidad->autocomplete = false;
		$edit->it2cantidad->onkeyup      = 'totalizarpitem(<#i#>)';
		$edit->it2cantidad->insertValue  = 1;

		$edit->it2merma = new inputField('Ultimo <#o#>', 'it2merma_<#i#>');
		$edit->it2merma->size       = 5;
		$edit->it2merma->db_name    = 'merma';
		$edit->it2merma->maxlength  = 15;
		$edit->it2merma->css_class  = 'inputnum';
		$edit->it2merma->rel_id     = 'sinvpitem';
		$edit->it2merma->insertValue= 0;
		$edit->it2merma->autocomplete= false;

		$ocultos=array('ultimo','pond','formcal','id_sinv');
		foreach($ocultos as $obj){
			$obj2='it2'.$obj;
			$edit->$obj2 = new hiddenField($obj.' <#o#>', $obj2 . '_<#i#>');
			$edit->$obj2->db_name = $obj;
			$edit->$obj2->rel_id  = 'sinvpitem';
		}

		$edit->it2estampa = new autoUpdateField('it2estampa' ,date('Ymd'), date('Ymd'));
		$edit->it2estampa->db_name = 'estampa';
		$edit->it2estampa->rel_id = 'sinvpitem';

		$edit->it2hora    = new autoUpdateField('it2hora',date('H:i:s'), date('H:i:s'));
		$edit->it2hora->db_name = 'hora';
		$edit->it2hora->rel_id = 'sinvpitem';

		$edit->it2usuario = new autoUpdateField('it2usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->it2usuario->db_name = 'usuario';
		$edit->it2usuario->rel_id = 'sinvpitem';

		/*INICIO SINV LABOR  ESTACIONES*/
		$edit->it3estacion = new  dropdownField('Estacion <#o#>', 'it3estacion_<#i#>');
		$edit->it3estacion->option('','Seleccionar');
		$edit->it3estacion->options('SELECT estacion,CONCAT(estacion,\'-\',nombre) AS lab FROM esta ORDER BY estacion');
		$edit->it3estacion->style   = 'width:250px;';
		$edit->it3estacion->db_name = 'estacion';
		$edit->it3estacion->rel_id  = 'sinvplabor';

		$edit->it3actividad = new inputField('Actividad <#o#>', 'it3actividad_<#i#>');
		$edit->it3actividad->size       = 32;
		$edit->it3actividad->db_name    = 'actividad';
		$edit->it3actividad->maxlength  = 50;
		$edit->it3actividad->rel_id     = 'sinvplabor';

		$edit->it3tunidad = new dropdownField ('', 'it3tunidad_<#i#>');
		$edit->it3tunidad->option('H','Horas');
		$edit->it3tunidad->option('D','Dias');
		$edit->it3tunidad->option('S','Semanas');
		$edit->it3tunidad->style       = 'width:80px;';
		$edit->it3tunidad->db_name     = 'tunidad';
		$edit->it3tunidad->css_class   = 'inputnum';
		$edit->it3tunidad->rel_id      = 'sinvplabor';
		$edit->it3tunidad->rule        = 'enum[H,S,]';
		$edit->it3tunidad->insertValue = 'H';

		$edit->it3tiempo = new inputField('', 'it3tiempo_<#i#>');
		$edit->it3tiempo->db_name      = 'tiempo';
		$edit->it3tiempo->css_class    = 'inputnum';
		$edit->it3tiempo->rel_id       = 'sinvplabor';
		$edit->it3tiempo->maxlength    = 10;
		$edit->it3tiempo->size         = 5;
		$edit->it3tiempo->rule         = 'positive';
		$edit->it3tiempo->autocomplete = false;
		$edit->it3tiempo->insertValue  = 1;

		$edit->it3estampa = new autoUpdateField('it3estampa' ,date('Ymd'), date('Ymd'));
		$edit->it3estampa->db_name = 'estampa';
		$edit->it3estampa->rel_id  = 'sinvpitem';

		$edit->it3hora    = new autoUpdateField('it3hora',date('H:i:s'), date('H:i:s'));
		$edit->it3hora->db_name = 'hora';
		$edit->it3hora->rel_id  = 'sinvpitem';

		$edit->it3usuario = new autoUpdateField('it3usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->it3usuario->db_name = 'usuario';
		$edit->it3usuario->rel_id  = 'sinvpitem';

		$inven=array();
		$query=$this->db->query('SELECT TRIM(codigo) AS codigo ,TRIM(descrip) AS descrip,tipo,base1,base2,base3,base4,iva,peso,precio1,pond,ultimo FROM sinv WHERE activo=\'S\' AND tipo=\'Articulo\'');
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$ind='_'.$row->codigo;
				$inven[$ind]=array($row->descrip,$row->tipo,$row->base1,$row->base2,$row->base3,$row->base4,$row->iva,$row->peso,$row->precio1,$row->pond);
			}
		}

		$edit->button_status('btn_add_sinvcombo' ,'Agregar','javascript:add_sinvcombo()' ,'CO','modify','button_add_rel');
		$edit->button_status('btn_add_sinvcombo' ,'Agregar','javascript:add_sinvcombo()' ,'CO','create','button_add_rel');
		$edit->button_status('btn_add_sinvpitem' ,'Agregar','javascript:add_sinvpitem()' ,'IT','create','button_add_rel');
		$edit->button_status('btn_add_sinvpitem' ,'Agregar','javascript:add_sinvpitem()' ,'IT','modify','button_add_rel');
		$edit->button_status('btn_add_sinvplabor','Agregar','javascript:add_sinvplabor()','LA','create','button_add_rel');
		$edit->button_status('btn_add_sinvplabor','Agregar','javascript:add_sinvplabor()','LA','modify','button_add_rel');

		//$edit->buttons('modify', 'save', 'undo', 'delete', 'add','back');
		$edit->build();

		$mcodigo = $edit->codigo->value;
		$mfdesde = $this->datasis->dameval("SELECT ADDDATE(MAX(fecha),-30) FROM costos WHERE codigo='".addslashes($mcodigo)."'");
		$mfhasta = $this->datasis->dameval("SELECT MAX(fecha) FROM costos WHERE codigo='".addslashes($mcodigo)."'");


		$conten['form']  =& $edit;
		

		//$data['content'] = 
		$this->load->view('view_sinv', $conten );
		//$data['content'] = $edit->output;
		//$data['script']  = $script;
		//$this->load->view('view_sinv', $data);


/*
		$smenu['link']   = barra_menu('301');
		$conten['form']  =& $edit;

		$data['content'] = $this->load->view('view_sinv', $conten,true);
		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('jquery.alerts.js');
		$data['script'] .= script('plugins/jquery.blockUI.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('sinvmaes.js');
		$data['style']   = style('jquery.alerts.css');
		$data['style']  .= style('redmond/jquery-ui.css');
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading(substr($edit->descrip->value,0,30));
		$this->load->view('view_ventanas', $data);
*/

	}

	function _pre_inserup($do){
		$tipo=$do->get('tipo');

		//SINVCOMBO
		foreach($do->data_rel['sinvcombo'] as $k=>$v){
			if(empty($v['codigo'])) $do->rel_rm('sinvcombo',$k);
		}
		if($tipo!='Combo' && count($do->data_rel['sinvcombo']) >0){
			$error='ERROR. el tipo de Art&acute;iculo debe ser Combo, debido a que tiene varios Art&iacute;culos relacionados';
			$do->error_message_ar['pre_upd']=$do->error_message_ar['pre_ins']=$error;
			return false;
		}
		if($tipo=='Combo' && count($do->data_rel['sinvcombo']) <=0){
			$error='ERROR. El Combo debe tener almenos un art&iacute;culo';
			$do->error_message_ar['pre_upd']=$do->error_message_ar['pre_ins']=$error;
			return false;
		}
		//SINVPITEM
		$borrar=array();
		foreach($do->data_rel['sinvpitem'] as $k=>$v){
			if(empty($v['codigo'])) $do->rel_rm('sinvpitem',$k);
		}
		//SINVPLABOR
		$borrar=array();
		foreach($do->data_rel['sinvplabor'] as $k=>$v){
			if(empty($v['estacion'])) $do->rel_rm('sinvplabor',$k);
		}

		//Valida los precios
		for($i=1;$i<5;$i++){
			$prec='precio'.$i;
			$$prec=round($do->get($prec),2); //optenemos el precio
		}

		if($precio1>=$precio2 && $precio2>=$precio3 && $precio3>=$precio4){
			$formcal= $do->get('formcal');
			$iva= $do->get('iva');
			$costo=($formcal=='U')? $do->get('ultimo'):($formcal=='P')? $do->get('pond'):($do->get('pond')>$do->get('ultimo'))? $do->get('pond') : $do->get('ultimo');

			for($i=1;$i<5;$i++){
				$prec='precio'.$i;
				$base='base'.$i;
				$marg='margen'.$i;

				$$base=$$prec*100/(100+$iva);   //calculamos la base
				$$marg=100-($costo*100/$$base); //calculamos el margen

				$do->set($prec,round($$prec,2));
				$do->set($base,round($$base,2));
				$do->set($marg,round($$marg,2));
			}
		}else{
			$do->error_message_ar['pre_upd'] =$do->error_message_ar['pre_ins'] = 'Los precios deben cumplir con:<br> Precio 1 mayor o igual al Precio 2 mayor o igual al  Precio 3 mayor o igual al Precio 4';
			return false;
		}

		//valida las escalas
		for($i=1;$i<4;$i++){
			$esca='pescala'.$i;
			$$esca=$do->get($esca);
			$esca='escala'.$i;
			$$esca=$do->get($esca);
		}

		if(!($pescala3>=$pescala2 && $pescala2>=$pescala1 && $escala3>=$escala2 && $escala2>=$escala1)){
			$do->error_message_ar['pre_upd'] = 'Las escalas deben cumplir con:<br> Escala 3 mayor o igual a la Escala 2 mayor o igual a la Escala 3, en cantidades y descuentos';
			return false;
		}
	}

	/* REDONDEA LOS PRECIOS DE TODOS LOS PRODUCTOS */
	function redondear($maximo) {
		$maximo = $this->uri->segment($this->uri->total_segments());
		$manterior = $this->datasis->traevalor("SINVREDONDEO");
		if (!empty($manterior)) {
			if ($manterior > $maximo ) {
				$this->db->simple_query("UPDATE sinv SET redecen='F' WHERE precio1<=$anterior");
			}
		}
		$this->datasis->ponevalor("SINVREDONDEO",$maximo);
		$this->db->update_string("sinv", array("redecen"=>'N'), "precio1<=$maximo");
		$this->datasis->sinvredondear();

		//$this->db->call_function("sp_sinv_redondea");
		logusu('SINV',"Redondea Precios $maximo");
	}

	/* RECALCULA LOS PRECIOS DE TODOS LOS PRODUCTOS */
	function recalcular() {
		$mtipo = $this->uri->segment($this->uri->total_segments());
		$this->datasis->sinvrecalcular($mtipo);
		$this->datasis->sinvredondear();
		
		//$this->db->call_function("sp_sinv_recalcular", $mtipo );
		//$this->db->call_function("sp_sinv_redondea");
		logusu('SINV',"Recalcula Precios $mtipo");
	}


	// **************************************
	//
	// -- Aumento de Precios -- //
	//
	// **************************************
	function auprec( $porcent= 0) {
		$data = $this->datasis->damesesion();
		$where = $data['data1'];

		// Respalda los precios anteriores
		$mN = $this->datasis->prox_sql('nsinvplog');
		$ms_codigo = $this->session->userdata('usuario');
		
		$mSQL = "INSERT INTO sinvplog ";
		$mSQL .= "SELECT '".$mN."', '".addslashes($ms_codigo)."', now(), curtime(), a.codigo, a.precio1, a.precio2, a.precio3, a.precio4 ";
		$mSQL .= "FROM sinv a ".$where;
		$this->db->query($mSQL);

		$mSQL = "SET
			a.precio1=ROUND(a.precio1*(100+$porcent)/100,2),
			a.precio2=ROUND(a.precio2*(100+$porcent)/100,2),
			a.precio3=ROUND(a.precio3*(100+$porcent)/100,2),
			a.precio4=ROUND(a.precio4*(100+$porcent)/100,2)";
		
		$this->db->query("UPDATE sinv a ".$mSQL." ".$where);
		$this->datasis->sinvrecalcular("M");
		$this->datasis->sinvredondear();

		echo "Aumento Concluido";
	}

	// **************************************
	//
	// -- Aumento de Precios -- //
	//
	// **************************************
	function auprecm() {
		$data = $this->datasis->damesesion();
		$where = $data['data1'];
		$mSQL = "SET mmargen=mmargen+$porcent ";
		$this->db->simple_query("UPDATE sinv a ".$mSQL." ".$where);
		echo "Aumento Concluido";
	}


	// **************************************
	//
	// -- Cambio de Ubicaciones -- //
	//
	// **************************************
	function cambiaubica($mubica) {
		$data = $this->datasis->damesesion();
		$where = $data['data1'];
		if ( !empty($where)){
			$mSQL = "SET ubica=".$this->db->escape($mubica)." ";
			$this->db->query("UPDATE sinv a ".$mSQL." ".$where);
			echo "Aumento Concluido";
		} else
			echo "No se filtraron los registros";
	}



	//*****************************
	//
	//  Cambia el Grupo
	//
	function sinvcamgrup() {
		$productos  = $this->input->post('productos');
		$mgrupo     = rawurldecode($this->input->post('grupo'));

		if($this->datasis->dameval("SELECT COUNT(*) FROM grup WHERE grupo='$mgrupo'") == 0 ){
			echo "Grupo no existe $mgrupo";
		} else {
			//Busca el Depto y Linea del grupo
			$depto = $this->datasis->dameval("SELECT depto FROM grup WHERE grupo='$mgrupo'");
			$linea = $this->datasis->dameval("SELECT linea FROM grup WHERE grupo='$mgrupo'");
			$productos = substr(trim($productos),0,-1);
			//echo "$mgrupo $productos";
			$mSQL = "UPDATE sinv SET grupo='$mgrupo', linea='$linea', depto='$depto' WHERE id IN ($productos) ";
			$this->db->simple_query($mSQL);
			logusu("SINV","Cambio grupo ".$mgrupo."-->".$productos);
			echo "Cambiado a Depto $depto, linea $linea, grupo $mgrupo Exitosamente";
		}
	}

	//*****************************
	//
	//  Cambia el Marca
	//
	function sinvcammarca() {
		$productos  = $this->input->post('productos');
		$mmarca     = rawurldecode($this->input->post('marca'));

		if($this->datasis->dameval("SELECT COUNT(*) FROM marc WHERE TRIM(marca)='".addslashes($mmarca)."'") == 0 ){
			echo "Marca no existe $mmarca";
		} else {
			//Busca el Depto y Linea del grupo
			$productos = substr(trim($productos),0,-1);
			$mSQL = "UPDATE sinv SET marca='".addslashes($mmarca)."' WHERE id IN ($productos) ";
			$this->db->simple_query($mSQL);
			logusu("SINV","Cambio marca ".$mmarca."-->".$productos);
			echo "Cambiadas las  marcas $mmarca Exitosamente";
		}
	}

	//*****************************
	//
	//  Cambia el Codigo
	//
	function sinvcodigoexiste(){
		$id = rawurldecode($this->input->post('codigo'));
		//$id = $this->uri->segment($this->uri->total_segments());
		$existe = $this->datasis->dameval("SELECT count(*) FROM sinv WHERE codigo='".addslashes($id)."'");
		$devo = 'N '.$id;
		if ($existe > 0 ) {
			$devo  ='S';
			$devo .= $this->datasis->dameval("SELECT descrip FROM sinv WHERE codigo='".addslashes($id)."'");
		}
		echo $devo;
	}

	//*****************************
	//
	// Cambia el codigo
	function sinvcodigo() {
		$mexiste  = $this->input->post('tipo');
		$mmcodigo = rawurldecode($this->input->post('codigo'));
		$mviejoid = rawurldecode($this->input->post('viejo'));

		$mmviejo  = $mviejoid; 
		$mviejoid = $this->datasis->dameval('SELECT id FROM sinv WHERE codigo='.$this->db->escape($mviejoid));
		$mviejo   = $this->db->escape($mmviejo);
		$mcodigo  = $this->db->escape($mmcodigo);

		if($mexiste=='S'){
			$mSQL = "DELETE FROM sinv WHERE codigo=".$mviejo;
			$this->db->simple_query($mSQL);
		} else {
			$mSQL = "UPDATE sinv SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
			$this->db->simple_query($mSQL);
		}

		if ( $mexiste=='S' ) {
			$mSQL  = "SELECT * FROM itsinv WHERE codigo=".$mviejo;
			$query = $this->db->query($mSQL);
			$mexisten = 0;
			if ($query->num_rows() > 0 ) {
				foreach ($query->result() as $row ) {
					$dbalma = $this->db->escape($row->alma);
					$mSQL   = "UPDATE itsinv SET existen=existen+".$row->existen."
						WHERE codigo=$mcodigo AND alma=$dbalma";
					$this->db->simple_query($mSQL);
					$mexisten += $row->existen;
				}
			}

			//Actualiza sinv
			$mSQL = "UPDATE sinv SET existen=exiten+".$mexisten." WHERE codigo=".$mcodigo;

			// Borra los items
			$mSQL = "DELETE FROM itsinv WHERE codigo=".$mviejo;
			$this->db->simple_query($mSQL);
		}else{
			$mSQL = "UPDATE itsinv SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
			$this->db->simple_query($mSQL);
		}

		$mSQL = "UPDATE itstra SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itscst SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE sitems SET codigoa=".$mcodigo." WHERE codigoa=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itsnot SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itsnte SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itspre SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itssal SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itconv SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE seri SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itpfac SET codigoa=".$mcodigo." WHERE codigoa=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itordc SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE invresu SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE invresu SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE barraspos SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE sinvfot SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE sinvpromo SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE costos SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
		$this->db->simple_query($mSQL);
		
		// Inventario invfel
		if(!$this->db->table_exists('invfelr')){
			$m      = 1;
			$mubica = 99;
			$mSQL = "UPDATE IGNORE invfelr SET codigo=".$mcodigo." WHERE codigo=".$mviejo;
			$this->db->simple_query($mSQL);
			$m = $this->datasis->dameval("SELECT COUNT(*) FROM invfelr WHERE codigo=".$mviejo);
			while ( $m > 0) {
				$mSQL = "UPDATE IGNORE invfelr SET codigo=".$mcodigo.", ubica=$mubica WHERE codigo=".$mviejo;
				$this->db->simple_query($mSQL);
				$m = $this->datasis->dameval("SELECT COUNT(*) FROM invfelr WHERE codigo=".$mviejo);
				$mubica = $mubica -1;
			}
		}
		logusu("SINV","Cambio codigo ".$mmviejo."-->".$mmcodigo);
	}

	function _sinvcodig(){
		$mexiste  = $this->input->post('tipo');
		$mmcodigo = rawurldecode($this->input->post('codigo'));
		$mviejoid = $this->input->post('viejo');

		$mmviejo  = $this->datasis->dameval('SELECT codigo FROM sinv WHERE id='.$this->db->escape($mviejoid));
		$mviejo   = $this->db->escape($mmviejo);
		$mcodigo  = $this->db->escape($mmcodigo);
		//echo "$mexiste  $mcodigo  $mviejo ";

		if($mexiste=='S'){
			$mSQL = "DELETE FROM sinv WHERE codigo=$mviejo";
			$this->db->simple_query($mSQL);
		} else {
			$mSQL = "UPDATE sinv SET codigo=$mcodigo WHERE codigo=$mviejo";
			$this->db->simple_query($mSQL);
		}

		if ( $mexiste=='S' ) {
			$mSQL  = "SELECT * FROM itsinv WHERE codigo=$mviejo";
			$query = $this->db->query($mSQL);
			$mexisten = 0;
			if ($query->num_rows() > 0 ) {
				foreach ($query->result() as $row ) {
					$dbalma = $this->db->escape($row->alma);
					$mSQL   = "UPDATE itsinv SET existen=existen+".$row->existen."
						WHERE codigo=$mcodigo AND alma=$dbalma";
					$this->db->simple_query($mSQL);
					$mexisten += $row->existen;
				}
			}
			//Actualiza sinv
			$mSQL = "UPDATE sinv SET existen=exiten+".$mexisten." WHERE codigo=$mcodigo";
			// Borra los items
			$mSQL = "DELETE FROM itsinv WHERE codigo=$mviejo";
			$this->db->simple_query($mSQL);
		}else{
			$mSQL = "UPDATE itsinv SET codigo=$mcodigo WHERE codigo=$mviejo";
			$this->db->simple_query($mSQL);
		}

		$mSQL = "UPDATE itstra SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itscst SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE sitems SET codigoa=$mcodigo WHERE codigoa=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itsnot SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itsnte SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itspre SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itssal SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itconv SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE seri SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itpfac SET codigoa=$mcodigo WHERE codigoa=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itordc SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE invresu SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE invresu SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE barraspos SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE sinvfot SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE sinvpromo SET codigo=$mcodigo WHERE codigo=$mviejo";
		$this->db->simple_query($mSQL);

		logusu("SINV","Cambio codigo ".$mmviejo."-->".$mmcodigo);
	}

	// Codigos de barra suplementarios
	function sinvbarras() {
		$mid      = $this->input->post('id');
		$mbarras  = trim(rawurldecode($this->input->post('codigo')));
		$mcodigo  = trim($this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$mid"));
		$htmlcod  = addslashes($mcodigo);

		//Busca si ya esta
		$check = $this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE codigo='$mbarras' OR barras='$mbarras' OR alterno='$mbarras' ");
		if ($check > 0 && !empty($barras) ) {
			echo "Codigo ya existen en Inventario";
		} else {
			$check = $this->datasis->dameval("SELECT COUNT(*) FROM barraspos WHERE suplemen='$mbarras' ");
			if ($check > 0 ) {
				echo "Codigo ya existen en codigos suplementarios";
			} else {
				$mSQL = "INSERT INTO barraspos SET codigo='$htmlcod', suplemen='$mbarras'";
				$this->db->simple_query($mSQL);
				logusu("SINV","Codigo de Barras Agregado".$mcodigo."-->".$mbarras);
				echo "Registro de Codigo Exitoso";
			}
		}
	}

	// Borra Codigo de barras suplementarios
	function sinvborrasuple() {
		$codigo   = $this->input->post('codigo');
		$mSQL = "DELETE FROM barraspos WHERE suplemen='$codigo'";
		$this->db->simple_query($mSQL);
		logusu("SINV","Eliminado Codigo Suplementario ".$codigo);
		echo "Codigo Eliminado";
	}

	// Borra Codigo de proveedores
	function sinvborraprv() {
		$codigo   = $this->input->post('codigo');
		$proveed  = $this->input->post('proveed');

		$mSQL = "DELETE FROM sinvprov WHERE codigop='$codigo' AND proveed='$proveed'";
		$this->db->simple_query($mSQL);
		logusu("SINV","Eliminado Codigo de proveedor $codigo => $proveed");
		echo "Codigo Eliminado";
	}

	// Busca Proveedor por autocomplete
	function sinvproveed(){
		$mid   = $this->input->post('tecla');
		if (empty($mid)) $mid='AN';
		$mSQL  = "SELECT CONCAT(TRIM(nombre),' (',RPAD(proveed,5,' '),')') nombre, proveed codigo FROM sprv WHERE nombre LIKE '%".$mid."%' ORDER BY nombre LIMIT 10";
		$data = "[]";
		$query = $this->db->query($mSQL);
		$retArray = array();
		$retorno = array();
		if ($query->num_rows() > 0){
			foreach( $query->result_array() as  $row ) {
				$retArray['label'] = $row['nombre'];
				$retArray['codigo'] = $row['codigo'];
				array_push($retorno, $retArray);
			}
			$data = json_encode($retorno);
		} else {
			$ret = '{data : []}';
		}
		echo $data;
	}

	// Busca Cliente por autocomplete
	function sinvcliente(){
		$mid   = $this->input->post('tecla');
		if (empty($mid)) $mid='AN';
		$mSQL  = "SELECT CONCAT(TRIM(nombre),' (',RPAD(cliente,5,' '),')') nombre, cliente codigo FROM scli WHERE nombre LIKE '%".$mid."%' ORDER BY nombre LIMIT 10";
		$data = "[]";
		$query = $this->db->query($mSQL);
		$retArray = array();
		$retorno = array();
		if ($query->num_rows() > 0){
			foreach( $query->result_array() as  $row ) {
				$retArray['label'] = $row['nombre'];
				$retArray['codigo'] = $row['codigo'];
				array_push($retorno, $retArray);
			}
			$data = json_encode($retorno);
		} else {
			$ret = '{data : []}';
		}
		echo $data;
	}

	// Agrega el codigo del producto segun el Proveedor
	function sinvsprv(){
		$codigo  = $this->uri->segment($this->uri->total_segments());
		$cod_prv = $this->uri->segment($this->uri->total_segments()-1);
		$id      = $this->uri->segment($this->uri->total_segments()-2);
		$mSQL = "REPLACE INTO sinvprov SELECT '$cod_prv' proveed, '$codigo' codigop, codigo FROM sinv WHERE id=$id ";
		$this->db->simple_query($mSQL);
		echo " codigo=$codigo guardado al prv $cod_prv " ;
	}

	//*************************
	//
	// Promociones
	//
	function sinvpromo() {
		$mid     = $this->input->post('id');
		$margen  = $this->input->post('margen');
		$mcodigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$mid");
		$htmlcod = addslashes($mcodigo);

		//Busca si ya esta
		$check = $this->datasis->dameval("SELECT count(*) FROM sinvpromo WHERE codigo='".$htmlcod."'");

		if ($check == 0 ) {
			$this->db->simple_query("INSERT INTO sinvpromo SET codigo='"+$htmlcod+"'");
		}

		if ( $margen == 0 ) {
			$mSQL = "DELETE FROM sinvpromo WHERE WHERE codigo='$htmlcod' ";
		} else {
			$mSQL = "UPDATE sinvpromo SET margen=$margen WHERE codigo='$htmlcod' ";
		}
		$this->db->simple_query($mSQL);
		logusu("SINV","Promocion ".$htmlcod."-->".$margen);
		echo "Cambio Exitoso";
	}

	//***************************
	//
	// Promociones a clientes
	function sinvdescu() {
		$tipo     = $this->uri->segment($this->uri->total_segments());
		$porcent  = $this->uri->segment($this->uri->total_segments()-1);
		$cod_cli  = $this->uri->segment($this->uri->total_segments()-2);
		$id       = $this->uri->segment($this->uri->total_segments()-3);

		$codigo   = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$id");
		$htmlcod = addslashes($codigo);

		//Busca si ya esta
		$check = $this->datasis->dameval("SELECT count(*) FROM sinvpromo a JOIN sinv b ON a.codigo=b.codigo WHERE b.id=$id AND cliente='".$cod_cli."'");

		if ($check == 0 ) {
			$this->db->simple_query("INSERT INTO sinvpromo SET codigo='".$htmlcod."', cliente='$cod_cli'");
		}

		if ( $porcent == 0 ) {
			$mSQL = "DELETE FROM sinvpromo WHERE codigo='$htmlcod' AND cliente='$cod_cli'";
		} else {
			$mSQL = "UPDATE sinvpromo SET margen=$porcent, tipo='$tipo' WHERE codigo='$htmlcod' AND cliente='$cod_cli'";
		}
		$this->db->simple_query($mSQL);
		logusu("SINV","Promocion cliente $cod_cli codigo ".$htmlcod."-->".$porcent);

		echo "Descuento Guardado ";
	}

	function cprecios(){
		$this->rapyd->uri->keep_persistence();

		$cpre=$this->input->post('pros');
		if($cpre!==false){
			$msj=$this->_cprecios();
		}else{
			$msj='';
		}

		$this->rapyd->load("datafilter2","datagrid");
		$mSPRV=array(
				'tabla'   =>'sprv',
				'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
				'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
				'retornar'=>array('proveed'=>'proveed'),
				'titulo'  =>'Buscar Proveedor');

		$bSPRV=$this->datasis->modbus($mSPRV);

		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');

		$script='
		$(document).ready(function(){
			$(".inputnum").numeric(".");
			$("#depto").change(function(){
				depto();
				$.post("'.$link2.'",{ depto:$(this).val() },function(data){$("#linea").html(data);})
				$.post("'.$link3.'",{ linea:"" },function(data){$("#grupo").html(data);})
			});
			$("#linea").change(function(){
				linea();
				$.post("'.$link3.'",{ linea:$(this).val() },function(data){$("#grupo").html(data);})
			});
			$("#grupo").change(function(){
				grupo();
			});
			$("#sinvprecioc").submit(function() {
				return confirm("Se van a actualizar todos los precios en pantalla \nEstas seguro de que quieres seguir??");
			});
			depto();
			linea();
			grupo();
		});

		function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}
			else{
				$("#nom_depto").attr("disabled","");
			}
		}

		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}
			else{
				$("#nom_linea").attr("disabled","");
			}
		}

		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}
			else{
				$("#nom_grupo").attr("disabled","");
			}
		}';

		$filter = new DataFilter2('Filtro por Producto');

		$select=array(
			'IF(formcal=\'U\',ultimo,IF(formcal=\'P\',pond,IF(formcal=\'S\',standard,GREATEST(ultimo,pond)))) AS costo',
			'a.existen','a.marca','a.tipo','a.id',
			'TRIM(codigo) AS codigo',
			'a.descrip','precio1','precio2','precio3','precio4','b.nom_grup','b.grupo',
			'c.descrip AS nom_linea','c.linea','d.descrip AS nom_depto','d.depto AS depto',
			'a.base1','a.base2','a.base3','a.base4'
		);

		$filter->db->select($select);
		$filter->db->from('sinv AS a');
		$filter->db->join('grup AS b','a.grupo=b.grupo');
		$filter->db->join('line AS c','b.linea=c.linea');
		$filter->db->join('dpto AS d','c.depto=d.depto');
		$filter->db->where('a.activo','S');
		$filter->script($script);

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo-> size=15;
		$filter->codigo->group = "Uno";

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip-> size=30;
		$filter->descrip->group = "Uno";

		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->db_name=("a.tipo");
		$filter->tipo->option("","Todos");
		$filter->tipo->option("Articulo","Art&iacute;culo");
		$filter->tipo->option("Servicio","Servicio");
		$filter->tipo->option("Descartar","Descartar");
		$filter->tipo->option("Consumo","Consumo");
		$filter->tipo->option("Fraccion","Fracci&oacute;n");
		$filter->tipo->style='width:120px;';
		$filter->tipo->group = "Uno";

		$filter->clave = new inputField("Clave", "clave");
		$filter->clave -> size=15;
		$filter->clave->group = "Uno";

		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		$filter->proveed->db_name='CONCAT_WS("-",`a`.`prov1`, `a`.`prov2`, `a`.`prov3`)';
		$filter->proveed -> size=10;
		$filter->proveed->group = "Dos";

		$filter->depto2 = new inputField("Departamento", "nom_depto");
		$filter->depto2->db_name="d.descrip";
		$filter->depto2 -> size=5;
		$filter->depto2->group = "Dos";

		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, CONCAT(depto,'-',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$filter->depto->in="depto2";
		$filter->depto->group = "Dos";
		$filter->depto->style='width:190px;';

		$filter->linea = new inputField("Linea", "nom_linea");
		$filter->linea->db_name="c.descrip";
		$filter->linea -> size=5;
		$filter->linea->group = "Dos";

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="c.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$filter->linea2->in="linea";
		$filter->linea2->group = "Dos";
		$filter->linea2->style='width:190px;';

		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, CONCAT(linea,'-',descrip) descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo2 = new inputField("Grupo", "nom_grupo");
		$filter->grupo2->db_name="b.nom_grup";
		$filter->grupo2 -> size=5;
		$filter->grupo2->group = "Dos";

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$filter->grupo->in="grupo2";
		$filter->grupo->group = "Dos";
		$filter->grupo->style='width:190px;';

		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option("","Seleccione un Departamento primero");
		}

		$filter->marca = new dropdownField("Marca", "marca");
		$filter->marca->option('','Todas');
		$filter->marca->options("SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca");
		$filter->marca->style='width:220px;';
		$filter->marca->group = "Dos";

		$filter->buttons("reset","search");
		$filter->build("dataformfiltro");

		$ggrid='';
		if($filter->is_valid()){
			$attr=array('id'=>'sinvprecioc');
			$ggrid =form_open(uri_string(),$attr);
			foreach ($filter->_fields as $field_name => $field_copy){
				$ggrid.= form_hidden($field_copy->id, $field_copy->value);
			}

			$grid = new DataGrid("Art&iacute;culos de Inventario");
			$grid->order_by("codigo","asc");
			$grid->per_page = 15;
			$link  = anchor('inventario/sinv/dataedit/show/<#id#>','<#codigo#>');
			$uri_2 = anchor('inventario/sinv/dataedit/create/<#id#>','Duplicar');

			$grid->column_orderby('C&oacute;digo','codigo','codigo');
			$grid->column_orderby('Descripci&oacute;n','descrip','descrip');
			$grid->column_orderby('Marca','marca','marca');
			for($i=1;$i<5;$i++){
				$obj='precio'.$i;
				$$obj = new inputField($obj, $obj);
				$$obj->grid_name=$obj.'[<#id#>]';
				$$obj->status   ='modify';
				$$obj->size     =8;
				$$obj->css_class='inputnum';
				$$obj->autocomplete=false;

				$grid->column("Precio $i",$$obj,'align=right');
			};
			$grid->column('Costo'     ,'<nformat><#costo#></nformat>'  ,'align=right');
			$grid->column('Existencia','<nformat><#existen#></nformat>','align=right');

			$grid->submit('pros', 'Cambiar','BR');
			$grid->build();
			$ggrid.=$grid->output;
			$ggrid.=form_close();
			//echo $this->db->last_query();
		}

		$data['content'] = '<div class="alert">'.$msj.'</div>';
		$data['content'].= $ggrid;
		$data['filtro']  = $filter->output;
		$data['title']   = heading('Cambio de precios');
		$data['head']    = $this->rapyd->get_head().script('jquery.pack.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
	}

	//Segun coicoi cambia los precios
	function _cprecios(){
		$precio1=$this->input->post('precio1');
		$precio2=$this->input->post('precio2');
		$precio3=$this->input->post('precio3');
		$precio4=$this->input->post('precio4');

		$msj=''; $error=0;
		foreach($precio1 as $id => $p1){
			$dbid=$this->db->escape($id);
			$p2=floatval($precio2[$id]);
			$p3=floatval($precio3[$id]);
			$p4=floatval($precio4[$id]);
			$dbcosto=$this->datasis->dameval("SELECT IF(formcal='U',ultimo,IF(formcal='P',pond,IF(formcal='S',standard,GREATEST(ultimo,pond)))) AS costo FROM sinv WHERE id=${dbid}");

			if($p1>=$p2 && $p2>=$p3 && $p4>=$p4 && $p1*$p2*$p3*$p4>0 && $p1>=$dbcosto && $p2>=$dbcosto && $p3>=$dbcosto && $p4>=$dbcosto){
				$sql=array();
				for($i=1;$i<5;$i++){
					$pprecio='p'.$i;
					$precio=round($$pprecio,2);
					$base  = "${precio}*100/(100+iva)";
					$costo = "IF(formcal='U',ultimo,IF(formcal='P',pond,IF(formcal='S',standard,GREATEST(ultimo,pond))))";

					$sql[]="precio${i}=${precio}";
					$sql[]="base${i}  =ROUND(${base},2)";
					$sql[]="margen${i}=ROUND(100-((${costo})*100/(${base})),2)";

				}
				$campos=implode(',',$sql);

				$mSQL="UPDATE `sinv` SET ${campos} WHERE id=${dbid}";
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'sinv'); $error++; }

				$cod=$this->datasis->dameval('SELECT codigo FROM sinv WHERE id='.$dbid);
				logusu('sinv',"Cambio de precios a $cod $p1; $p2; $p3; $p4");
			}else{
				$codigo=$this->datasis->dameval("SELECT codigo FROM sinv WHERE id=${dbid}");
				$msj.='En el art&iacute;culo '.TRIM($codigo).' no se actualizo porque los precios deben tener valores mayores que el costo y en forma decrecientes (Precio 1 >= Precio 2 >= Precio 3 >= Precio 4).'.br();
			}

		}
		if($error>0) $msj.='Hubo alg&uacute;n error, se gener&oacute; un centinela';
		return $msj;
	}

	// Sugiere proximo codigo de inventario
	function sug($tabla=''){
		if($tabla=='dpto'){
			$valor=$this->datasis->dameval("SELECT LPAD(hexa,2,0) FROM serie LEFT JOIN dpto ON LPAD(depto,2,0)=LPAD(hexa,2,0) WHERE valor<255 AND depto IS NULL LIMIT 1");
		}elseif($tabla=='line'){
				$valor=$this->datasis->dameval("SELECT LPAD(hexa,2,0) FROM serie LEFT JOIN line ON LPAD(linea,2,0)=LPAD(hexa,2,0) WHERE valor<255 AND linea IS NULL LIMIT 1");
		}elseif($tabla=='grup'){
				$valor=$this->datasis->dameval("SELECT LPAD(hexa,4,0) FROM serie LEFT JOIN grup ON LPAD(grupo,4,0)=LPAD(hexa,4,0) WHERE valor<65535 AND grupo IS NULL LIMIT 1");
		}
		return $valor;
	}

	// Busca el Ultimo codigo
	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT codigo FROM sinv ORDER BY codigo DESC LIMIT 1");
		echo $ultimo;
	}

	function sugerir(){
		$ultimo=$this->datasis->dameval("SELECT LPAD(hexa,4,0) FROM serie LEFT JOIN sinv ON LPAD(codigo,4,0)=LPAD(hexa,4,0) WHERE valor<65535 AND codigo IS NULL LIMIT 1");
		echo $ultimo;
	}

	function chexiste($codigo){
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE codigo='$codigo'");
		if ($check > 0){
			$descrip=$this->datasis->dameval("SELECT descrip FROM sinv WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el producto $descrip");
			return FALSE;
		}else {
		 return TRUE;
		}
	}

	// Si exsite el codigo Alterno
	function chexiste2($alterno){
		$alterno = trim($alterno);
		if ( empty( $alterno) )
			return true;
		else {
			$codigo = $this->input->post('codigo');
			$check=$this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE alterno='$alterno' AND codigo<>".$this->db->escape($codigo));
			if ($check > 0){
				$descrip=$this->datasis->dameval("SELECT descrip FROM sinv WHERE alterno='$alterno'");
				$this->validation->set_message('chexiste2',"El codigo alterno $alterno ya existe para el producto $descrip");
				return FALSE;
			}else {
				return TRUE;
			}
		}
	}

	//
	function _detalle($codigo){
		$salida='';
		$estilo='';
		if(!empty($codigo)){
			$this->rapyd->load('dataedit','datagrid');
			$grid = new DataGrid('Existencias por Almacen');
			$grid->db->select(array('b.ubides','a.codigo','a.alma','a.existen',"IF(b.ubides IS NULL,'SIN ALMACEN',b.ubides) AS nombre"));
			$grid->db->from('itsinv AS a');
			$grid->db->join('caub as b','a.alma=b.ubica','LEFT');
			$grid->db->where('codigo',$codigo);

			//$link=anchor('/inventario/caub/dataedit/show/<#alma#>','<#alma#>');
			$link  = "<a href=\"javascript:void(0);\" onclick=\"window.open('".base_url();
			$link .= "inventario/caub', '_blank', 'width=800,height=600,scrollbars=Yes,status=Yes,resizable=Yes,screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');\" heigth=\"600\"><#alma#></a>";

			$grid->column('Almacen' ,$link, "style='font-size:12px;font-weight:bold;'");
			$grid->column('Nombre'         ,'nombre',"style='font-size: 10px'");
			$grid->column('Cantidad'       ,'existen','align="right" '."style='font-size: 10px'");

			$grid->build('datagridsimple');

			if($grid->recordCount>0) $salida=$grid->output;
			$salida = html_entity_decode($salida);
			$estilo="
			<style type='text/css'>
			.simplerow  { color: #153D51;border-bottom: 1px solid #ECECEC; font-family: Lucida Grande, Verdana, Geneva, Sans-serif;	font-size: 12px; font-weight: bold;}
			.simplehead { background: #382408; border-bottom: 1px solid #ECECEC;color: #EEFFEE;font-family: Lucida Grande, Verdana, Geneva, Sans-serif; font-size: 12px;padding-left:5px;}
			.simpletabla { width:100%;colspacing:0px; colpadding:0px}
			</style>";
		}
		return $estilo.$salida;
	}

	function _pre_del($do){
		$codigo=$this->db->escape($do->get('codigo'));
		$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sitems WHERE codigoa=$codigo");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM itscst WHERE codigo=$codigo");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM itstra WHERE codigo=$codigo");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM itspre WHERE codigo=$codigo");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM itsnot WHERE codigo=$codigo");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM itsnte WHERE codigo=$codigo");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM itsinv WHERE codigo=$codigo");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM sinvpitem WHERE codigo=$codigo");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM sinvcombo WHERE codigo=$codigo");

		if ($this->db->table_exists('ordpitem'))
			$check += $this->datasis->dameval("SELECT COUNT(*) FROM ordpitem WHERE codigo=$codigo");

		if ($check > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Producto con Movimiento no puede ser Borrado, solo se puede inactivar';
			return false;
		}
		return true;
	}

	// Trae la descripcion de una Barra
	function barratonombre(){
		if($this->input->post('barra')){
			$barra=$this->db->escape($this->input->post('barra'));
			echo $this->datasis->dameval("SELECT descrip FROM sinv WHERE barras=$barra");
		}
	}

	//Consulta rapida
	function consulta(){
		$this->load->helper('openflash');
		$this->rapyd->load("datagrid");
		$fields = $this->db->field_data('sinv');
		$url_pk = $this->uri->segment_array();
		$coun=0; $pk=array();
		foreach ($fields as $field){
			if($field->primary_key==1){
				$coun++;
				$pk[]=$field->name;
			}
		}
		$values=array_slice($url_pk,-$coun);
		$claves=array_combine (array_reverse($pk) ,$values );
		$id = $claves['id'];

		$mCodigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=".$claves['id']."");

		$mSQL  = 'SELECT a.tipoa, MID(a.fecha,1,7) mes, sum(a.cana*(a.tipoa="F")) cventa, sum(a.cana*(a.tipoa="D")) cdevol, sum(a.cana*if(a.tipoa="D",-1,1)) cana, sum(a.tota*(a.tipoa="F")) mventa, sum(a.tota*(a.tipoa="D")) mdevol, sum(a.tota*if(a.tipoa="D",-1,1)) tota ';
		$mSQL .= "FROM sitems a WHERE a.codigoa='".addslashes($mCodigo)."' ";
		$mSQL .= "AND a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01') ";
		$mSQL .= "GROUP BY MID( a.fecha ,1,7)  WITH ROLLUP LIMIT 24";
		$mGrid1 = '';

		$mSQL  = 'SELECT a.usuario, a.fecha, MID(a.hora,1,5) hora, MID(REPLACE(a.comenta,"ARTICULO DE INVENTARIO",""),1,30) comenta, a.modulo ';
		$mSQL .= 'FROM logusu a WHERE a.comenta LIKE "%'.addslashes($mCodigo).'%" ';
		$mSQL .= "ORDER BY a.fecha DESC LIMIT 30";

		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$mGrid2 = '
			<div id="tableDiv_Logusu" class="tableDiv">
			<table id="Open_text_Logusu" class="FixedTables" >
			<thead>
			<tr>
				<th>Fecha</th>
				<th>Usuario</th>
				<th>Hora</th>
				<th>Modulo</th>
				<th>Accion</th>
			</tr>
			</thead>
			<tbody>';

			$m = 1;
			foreach ($query->result() as $row){
				if($m == 1) { $mGrid2.='<tr id="firstTr">'; } else { $mGrid2.='<tr>'; };
				$mGrid2.="
				<tr>
					<td>".$row->fecha."</td>
					<td>".$row->usuario."</td>
					<td>".$row->hora."</td>
					<td>".$row->modulo."</td>
					<td>".$row->comenta."</td>
				</tr>";
				$m++;
			}
			$mGrid2 .= "
			</tbody>
			</table>
			</div>";
		} else {
			$mGrid2 = "NO SE ENCONTRO MOVIMIENTO";
		}

		$descrip = $this->datasis->dameval("SELECT descrip FROM sinv WHERE id=".$claves['id']." ");

		/*mes, cventa, mventa, mpvp, ccompra, mcompra,util, margen, promedio*/
		$script = "
		<script type=\"text/javascript\" >

		<!-- All the scripts will go here  -->
		var dsOption= {
			fields :[
				{name : 'mes'},
				{name : 'cventa',   type: 'float' },
				{name : 'mventa',   type: 'float' },
				{name : 'mpvp' ,    type: 'float' },
				{name : 'ccompra',  type: 'float' },
				{name : 'mcompra',  type: 'float' },
				{name : 'util',     type: 'float' },
				{name : 'margen',   type: 'float' },
				{name : 'promedio', type: 'float' }
			],
			recordType : 'object'
		}

		var colsOption = [
			{id: 'mes',      header: 'Mes',          width :60, frozen: true   },
			{id: 'cventa' ,  header: 'Cant. Venta',  width :80, align: 'right' },
			{id: 'mventa' ,  header: 'Costo Venta',  width :80, align: 'right' },
			{id: 'mpvp' ,    header: 'Precio Venta', width :80, align: 'right' },
			{id: 'ccompra' , header: 'Cant Compra',  width :80, align: 'right' },
			{id: 'mcompra' , header: 'Monto Compra', width :80, align: 'right' },
			{id: 'util' ,    header: 'Utilidad',     width :80, align: 'right' },
			{id: 'margen' ,  header: 'Margen %',     width :80, align: 'right' },
			{id: 'promedio', header: 'Costo Prom.',  width :80, align: 'right' }
		];

		var gridOption={
			id : 'grid1',
			loadURL : '/proteoerp/inventario/sinv/consulta_ventas/".$id."',
			container : 'grid1_container',
			dataset : dsOption ,
			columns : colsOption,
			allowCustomSkin: true,
			skin: 'vista',
			toolbarContent: 'pdf'
		};

		var dsOption1= {
			fields :[
				{name : 'fecha'   },
				{name : 'usuario' },
				{name : 'hora'    },
				{name : 'modulo'  },
				{name : 'comenta' }
			],
			recordType : 'object'
		}

		var colsOption1 = [
			{id: 'fecha',   header: 'Fecha',      width :70, frozen: true },
			{id: 'usuario', header: 'Usuario',    width :60 },
			{id: 'hora' ,   header: 'Hora',       width :60 },
			{id: 'modulo' , header: 'Modulo',     width :60 },
			{id: 'comenta', header: 'Comentario', width :200 }
		];

		var gridOption1={
			id : 'grid2',
			loadURL : '/proteoerp/inventario/sinv/consulta_logusu/".$id."',
			container : 'grid2_container',
			dataset : dsOption1 ,
			columns : colsOption1,
			toolbarContent: 'pdf',
			allowCustomSkin: true,
			skin: 'vista'
		};

		var mygrid=new Sigma.Grid(gridOption);
		Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );

		var mygrid1=new Sigma.Grid(gridOption1);
		Sigma.Util.onLoad( Sigma.Grid.render(mygrid1) );
		</script>";

		$style = '';

		$data['content'] = "
		<table align='center' border='0' cellspacing='2' cellpadding='2' width='98%'>
			<tr>
				<td valign='top'>
					<div style='border: 3px outset #EFEFEF;background: #EFEFFF '>
					<div id='grid1_container' style='width:500px;height:250px'></div>
					</div>
				</td>
				<td>".
				open_flash_chart_object( 250,180, site_url("inventario/sinv/ventas/$id"))."
				</td>
			</tr>
			<tr>
				<td>
					<div style='border: 3px outset #EFEFEF;background: #EFEFFF '>
					<div id='grid2_container' style='width:500px;height:250px'></div>
					</div>

				</td>
				<td>".
				open_flash_chart_object( 250,180, site_url("inventario/sinv/compras/$id"))."
				</td>
			</tr>
		</table>";

		$data['title']    = '<h1>Consulta de Articulo de Inventario</h1>';

		$data['script']   = script("plugins/jquery.numeric.pack.js");
		$data['script']  .= script("plugins/jquery.floatnumber.js");
		$data['script']  .= script("gt_msg_en.js");
		$data['script']  .= script("gt_grid_all.js");
		$data['script']  .= $script;

		$data['style']    = style('gt_grid.css');
		$data["subtitle"] = "
			<div align='center' style='border: 2px outset #EFEFEF;background: #EFEFEF;font-size:18px'>
				<a href='javascript:javascript:history.go(-1)'>(".addslashes($mCodigo).") ".$descrip."</a>
			</div>";

		$data['head']  = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function consulta_ventas() {
		$id = $this->uri->segment($this->uri->total_segments());
		$mCodigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=".$id."");

		$mSQL  = 'SELECT MID(a.fecha,1,7) mes, sum(a.cana*(a.tipoa="F")) cventa, sum(a.cana*(a.tipoa="D")) cdevol, sum(a.cana*if(a.tipoa="D",-1,1)) cana, sum(a.tota*(a.tipoa="F")) mventa, sum(a.tota*(a.tipoa="D")) mdevol, sum(a.tota*if(a.tipoa="D",-1,1)) tota ';
		$mSQL .= "FROM sitems a WHERE a.codigoa='".addslashes($mCodigo)."' ";
		$mSQL .= "AND a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01') ";
		$mSQL .= "GROUP BY MID( a.fecha ,1,7)  WITH ROLLUP LIMIT 60";

		$mSQL  = "
		SELECT
			MID(a.fecha,1,7) mes,
			sum(a.cantidad*(a.origen='3I')) cventa,
			ROUND(sum(a.promedio*a.cantidad*(a.origen='3I')),2) mventa,
			ROUND(sum(a.venta*(a.origen='3I')),2) mpvp,
			sum(a.cantidad*(a.origen='2C')) ccompra,
			sum(a.monto*(a.origen='2C')) mcompra,
			ROUND(sum((a.venta-a.cantidad*a.promedio)*(a.origen='3I')),2) util,
			100- ROUND( sum(a.cantidad*a.promedio*(a.origen='3I'))*100/SUM(a.venta), 2) margen,
			round(avg(promedio),2) promedio
		FROM costos a WHERE a.codigo='".addslashes($mCodigo)."' AND a.origen IN ('3I','2C')
			AND a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01')
		GROUP BY MID( a.fecha ,1,7)  WITH ROLLUP LIMIT 24";

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

	function _post_insert($do){
		$codigo=$do->get('codigo');

		$precio1=$do->get('precio1');
		$precio2=$do->get('precio2');
		$precio3=$do->get('precio3');
		$precio4=$do->get('precio4');
		logusu('sinv',"Creo  $codigo precios: $precio1,$precio2,$precio3, $precio4");
	}

	function _post_update($do){
		$codigo=$do->get('codigo');

		$precio1=$do->get('precio1');
		$precio2=$do->get('precio2');
		$precio3=$do->get('precio3');
		$precio4=$do->get('precio4');
		logusu('sinv',"Modifico $codigo precios: $precio1,$precio2,$precio3, $precio4");
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		logusu('sinv',"Elimino $codigo");
	}

	function consulta_logusu() {
		$id = $this->uri->segment($this->uri->total_segments());
		$mCodigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=".$id."");

		$mSQL  = 'SELECT a.fecha, a.usuario,  MID(a.hora,1,5) hora, a.modulo, MID(REPLACE(a.comenta,"ARTICULO DE INVENTARIO",""),1,30) comenta ';
		$mSQL .= 'FROM logusu a WHERE a.comenta LIKE "%'.addslashes($mCodigo).'%" ';
		$mSQL .= "ORDER BY a.fecha DESC LIMIT 60";
		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$retArray = array();
			foreach( $query->result_array() as  $row ) {
				$retArray[] = $row;
			}
			$data = json_encode($retArray);
			$ret = "{data:" . $data .",\n";
			$ret .= "recordType : 'array'}";
			//$ret .= $mSQL;
		} else {
			$ret = '{data : []}';
		}
		echo $ret;
	}

	function ventas($id=''){
		if (empty($id)) return;
		$this->load->library('Graph');

		$codigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$id");
		$mSQL = "SELECT	a.tipoa,MID(a.fecha,1,7) mes,
			sum(a.cana*(a.tipoa='F')) cventa,
			sum(a.cana*(a.tipoa='D')) cdevol,
			sum(a.cana*if(a.tipoa='D',-1,1)) cana,
			sum(a.tota*(a.tipoa='F')) mventa,
			sum(a.tota*(a.tipoa='D')) mdevol,
			sum(a.tota*if(a.tipoa='D',-1,1)) tota
		FROM sitems a
		WHERE a.codigoa='$codigo' AND a.tipoa IN ('F','D') AND a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01')
		GROUP BY MID( a.fecha, 1,7 )  LIMIT 7";

		$maxval = 0;
		$query = $this->db->query($mSQL);
		$data_1=$data_2=$meses=array();
		foreach($query->result() as $row ){
			if ($row->cana>$maxval) $maxval=$row->cana;
			$meses[]   = $row->mes;
			$data_1[]  = $row->cana;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;

		$bar_1 = new bar(75, '#0053A4');

		$bar_1->key('Venta',10);

		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_1->links[]= site_url("/ventas/clientes/mensuales/$codigo/".$meses[$i]);
		}
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval > 0 ) {
			$g->title( 'Ventas por Mes ','{font-size: 16px; color:#0F3054}' );
			$g->data_sets[] = $bar_1;

			$g->set_x_labels($meses);
			$g->set_x_label_style( 10, '#000000', 2, 1 );
			$g->set_x_axis_steps( 10 );
			$g->set_x_legend( 'Meses ', 14,'#004381' );

			$g->bg_colour = '#FFFFFF';
			$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Cantidad: #tip#' );
			$g->set_y_max(ceil($maxval/$om));
			$g->y_label_steps(5);
			$g->set_y_legend('Ventas x '.number_format($om,0,'','.'), 16, '#004381' );
		} else
			$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';
		echo utf8_encode($g->render());
	}

	function compras($id=''){
		if (empty($id)) return;
		$this->load->library('Graph');

		$codigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$id");
		$mSQL = "SELECT	MID(a.fecha,1,7) mes,
			sum(a.cantidad*(b.tipo_doc='FC')) cventa,
			sum(a.cantidad*(b.tipo_doc='NC')) cdevol,
			sum(a.cantidad*if(b.tipo_doc='NC',-1,1)) cana,
			sum(a.importe*(b.tipo_doc='FC')) mventa,
			sum(a.importe*(b.tipo_doc='NC')) mdevol,
			sum(a.importe*if(b.tipo_doc='NC',-1,1)) tota
		FROM itscst a JOIN scst b ON a.control=b.control
		WHERE a.codigo='$codigo' AND b.tipo_doc IN ('FC','NC') AND b.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01')
				AND  a.fecha <= b.actuali
		GROUP BY MID( b.fecha, 1,7 ) LIMIT 7  ";

		$maxval = 0;
		$query = $this->db->query($mSQL);
		$data_1=$data_2=$meses=array();
		foreach($query->result() as $row ){
			if ($row->cana>$maxval) $maxval=$row->cana;
			$meses[]   = $row->mes;
			$data_1[]  = $row->cana;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;

		$bar_1 = new bar(75, '#9053A4');

		$bar_1->key('Compra',10);

		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_1->links[]= site_url("/ventas/clientes/mensuales/$codigo/".$meses[$i]);
		}
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval > 0 ) {
			$g->title( 'Compras por Mes ','{font-size: 16px; color:#0F3054}' );
			$g->data_sets[] = $bar_1;

			$g->set_x_labels($meses);
			$g->set_x_label_style( 10, '#000000', 2, 1 );
			$g->set_x_axis_steps( 10 );
			$g->set_x_legend( 'Meses ', 14,'#004381' );

			$g->bg_colour = '#FFFFFF';
			$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Cantidad: #tip#' );
			$g->set_y_max(ceil($maxval/$om));
			$g->y_label_steps(5);
			$g->set_y_legend('Compras x '.number_format($om,0,'','.'), 16, '#004381' );
		} else
			$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';
		echo utf8_encode($g->render());
	}

	function instalar(){
		//$mSQL="ALTER TABLE `sinvplabor` ALTER `actividad` DROP DEFAULT";
		//$mSQL="ALTER TABLE `sinvplabor` CHANGE COLUMN `actividad` `actividad` VARCHAR(100) NOT NULL AFTER `nombre`";

		$campos = $this->db->list_fields('sinv');
		if (!in_array('id',$campos)){
			$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
			$this->db->simple_query($mSQL);
		}

		if (!in_array('alto'       ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD alto DECIMAL(10,2)");
		if (!in_array('ancho'      ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD ancho DECIMAL(10,2)");
		if (!in_array('largo'      ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD largo DECIMAL(10,2)");
		if (!in_array('forma'      ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD forma VARCHAR(50)");
		if (!in_array('exento'     ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD exento CHAR(1) DEFAULT 'N'");
		if (!in_array('mmargen'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD mmargen DECIMAL(7,2) DEFAULT 0 COMMENT 'Margen al Mayor'");
		if (!in_array('pm'         ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pm` DECIMAL(19,2) NULL DEFAULT '0.00' COMMENT 'porcentaje mayor'");
		if (!in_array('pmb'        ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pmb` DECIMAL(19,2) NULL DEFAULT '0.00' COMMENT 'porcentaje mayor'");
		if (!in_array('mmargenplus',$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `mmargenplus` DECIMAL(7,2) NULL DEFAULT '0.00' COMMENT 'Margen al Mayor'");
		if (!in_array('escala1'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `escala1` DECIMAL(12,2) NULL DEFAULT '0.00'");
		if (!in_array('pescala1'   ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pescala1` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala1'");
		if (!in_array('escala2'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `escala2` DECIMAL(12,2) NULL DEFAULT '0.00'");
		if (!in_array('pescala2'   ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pescala2` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala2'");
		if (!in_array('escala3'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `escala3` DECIMAL(12,2) NULL DEFAULT '0.00'");
		if (!in_array('pescala3'   ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pescala3` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala3'");
		if (!in_array('mpps',       $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `mpps` VARCHAR(20) NULL  COMMENT 'Numero de Ministerior de Salud'");
		if (!in_array('cpe',        $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `cpe`  VARCHAR(20) NULL  COMMENT 'Otro Numero'");



		if(!$this->db->table_exists('sinvcombo')){
			$mSQL="CREATE TABLE `sinvcombo` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`combo` CHAR(15) NOT NULL,
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				`descrip` CHAR(30) NULL DEFAULT NULL,
				`cantidad` DECIMAL(10,3) NULL DEFAULT NULL,
				`precio` DECIMAL(15,2) NULL DEFAULT NULL,
				`transac` CHAR(8) NULL DEFAULT NULL,
				`estampa` DATE NULL DEFAULT NULL,
				`hora` CHAR(8) NULL DEFAULT NULL,
				`usuario` CHAR(12) NULL DEFAULT NULL,
				`costo` DECIMAL(17,2) NULL DEFAULT '0.00',
				`ultimo` DECIMAL(19,2) NULL DEFAULT '0.00',
				`pond` DECIMAL(19,2) NULL DEFAULT '0.00',
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sinvpitem')){
			$mSQL="CREATE TABLE `sinvpitem` (
				`producto` VARCHAR(15) NULL DEFAULT NULL COMMENT 'codigo del prod terminado (sinv)',
				`codigo` VARCHAR(15) NULL DEFAULT NULL COMMENT 'codigo del Insumo (sinv)',
				`descrip` VARCHAR(40) NULL DEFAULT NULL,
				`cantidad` DECIMAL(14,3) NULL DEFAULT '0.000',
				`merma` DECIMAL(10,2) NULL DEFAULT '0.00' COMMENT 'Porcentaje de merma',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id_sinv` INT(11) NULL DEFAULT NULL,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`ultimo` DECIMAL(19,2) NOT NULL DEFAULT '0.00',
				`pond` DECIMAL(19,2) NOT NULL DEFAULT '0.00',
				`formcal` CHAR(1) NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`)
			)
			COMMENT='Insumos de un producto terminado'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DYNAMIC
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sinvplabor')){
			$mSQL="CREATE TABLE `sinvplabor` (
				`producto` VARCHAR(15) NULL DEFAULT '' COMMENT 'Producto Terminado',
				`estacion` VARCHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL,
				`actividad` VARCHAR(100) NOT NULL,
				`minutos` INT(6) NULL DEFAULT '0',
				`segundos` INT(6) NULL DEFAULT '0',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`)
			)
			COMMENT='Acciones de la Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if ($this->db->field_exists('minutos', 'sinvplabor')){
			$mSQL="ALTER TABLE `sinvplabor`
			ADD COLUMN `tiempo` DECIMAL(10,2) NULL DEFAULT '0' AFTER `actividad`,
			ADD COLUMN `tunidad` CHAR(1) NULL DEFAULT 'H' COMMENT 'Unidad de tiempo Horas Dias Semanas' AFTER `tiempo`,
			DROP COLUMN `minutos`,
			DROP COLUMN `segundos`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('esta')){
			$mSQL="CREATE TABLE `esta` (
				`estacion` VARCHAR(5) NOT NULL DEFAULT '',
				`nombre` VARCHAR(30) NULL DEFAULT NULL,
				`descrip` TEXT NULL,
				`ubica` TEXT NULL,
				`jefe` VARCHAR(5) NULL DEFAULT NULL COMMENT 'tecnico',
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `vendedor` (`estacion`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1;";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sinvprov')){
			$mSQL="CREATE TABLE `sinvprov` (
				`proveed` CHAR(5) NOT NULL DEFAULT '',
				`codigop` CHAR(15) NOT NULL DEFAULT '',
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				PRIMARY KEY (`proveed`, `codigop`, `codigo`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
		}

		if(!$this->db->table_exists('barraspos')){
			$query="CREATE TABLE `barraspos` (
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				`suplemen` CHAR(15) NOT NULL DEFAULT '',
				PRIMARY KEY (`codigo`, `suplemen`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($query);
		}
		if(!$this->db->table_exists('invfelr')){
			$query="CREATE TABLE `invfelr` (
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				`fecha` DATE NOT NULL DEFAULT '0000-00-00',
				`precio` DECIMAL(17,2) NOT NULL DEFAULT '0.00',
				`existen` DECIMAL(17,2) NULL DEFAULT NULL,
				`anterior` DECIMAL(17,2) NULL DEFAULT NULL,
				`parcial` DECIMAL(17,2) NULL DEFAULT NULL,
				`alma` CHAR(4) NOT NULL DEFAULT '',
				`tipo` CHAR(1) NULL DEFAULT NULL,
				`fhora` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
				`usuario` CHAR(12) NULL DEFAULT NULL,
				`ubica` CHAR(10) NOT NULL DEFAULT ''
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
		}
	}

}

?>
