<?php
//1- Ruta, lleno (peso),chofer
//2- llenar vaqueras
//3- Valores de las analisis
//4- Peso Vacio
class Lrece extends Controller {
	var $mModulo = 'LRECE';
	var $titp    = 'Recepcion de Leche';
	var $tits    = 'Recepcion de Leche';
	var $url     = 'leche/lrece/';

	function Lrece(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'LRECE', $ventana=0 );
	}

	function index(){
		$this->datasis->modintramenu( 900, 600, substr($this->url,0,-1) );
		$this->datasis->creaintramenu( $data = array('modulo'=>'220','titulo'=>'Recepcion de Leche','mensaje'=>'Recepcion de Leche','panel'=>'LECHE','ejecutar'=>'leche/lrece','target'=>'popu','visible'=>'S','pertenece'=>'2','ancho'=>900,'alto'=>600));

		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$grid->setHeight('140');
		$param['grids'][] = $grid->deploy();

		$grid1   = $this->defgridit();
		$grid1->setHeight('190');
		$param['grids'][] = $grid1->deploy();

		// Configura los Paneles
		$readyLayout = $grid->readyLayout2( 212, 220, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"imprime",  "img"=>"assets/default/images/print.png","alt" => 'Reimprimir', "label"=>"Reimprimir Documento"));

		$grid->wbotonadd(array("id"=>"bvaqueras", "img"=>"images/star.png","alt" => 'Vaqueras', "label"=>"Vaqueras"));
		$grid->wbotonadd(array("id"=>"banalisis", "img"=>"images/star.png","alt" => 'Analisis', "label"=>"Analisis"));
		$grid->wbotonadd(array("id"=>"bcierre"  , "img"=>"images/star.png","alt" => 'Cierre'  , "label"=>"Cierre"  ));
		$WestPanel = $grid->deploywestp();

		//Panel Central
		$centerpanel = $grid->centerpanel( $id = "radicional", $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		$adic = array(
		array("id"=>"fedita" , "title"=>"Agregar/Editar Pedido"),
		);

		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']    = $WestPanel;
		$param['script']       = script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('LRECE', 'JQ');
		$param['otros']        = $this->datasis->otros('LRECE', 'JQ');
		$param['centerpanel']  = $centerpanel;
		$param['temas']        = array('proteo','darkness','anexos1');
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;
		$param['tamano']       = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);

	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0, $grid1 ){
		$bodyscript = '		<script type="text/javascript">';

		$bodyscript .= '
		function lreceadd() {
			$.post("'.site_url('leche/lrece/apertura/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			});
		};';

		$bodyscript .= '
		function lreceedit() {
			//var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			//if (id)	{
			//	var ret    = $("#newapi'.$grid0.'").getRowData(id);
			//	mId = id;
			//	$.post("'.site_url('leche/lrece/dataedit/modify').'/"+id, function(data){
			//		$("#fedita").html(data);
			//		$("#fedita").dialog( "open" );
			//	});
			//} else { $.prompt("<h1>Por favor Seleccione un Registro</h1>");}
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
		jQuery("#imprime").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				'.$this->datasis->jwinopen(site_url('formatos/ver/LRECE').'/\'+id+\'/id\'').';
			} else {
				$.prompt("<h1>Por favor Seleccione un registro</h1>");
			}
		});';

		$bodyscript .= '
		jQuery("#bvaqueras").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				$.post("'.site_url('leche/lrece/vaqueras/modify').'/"+id,
				function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});
			} else {
				$.prompt("<h1>Por favor Seleccione un registro</h1>");
			}
		});';

		$bodyscript .= '
		jQuery("#banalisis").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				$.post("'.site_url('leche/lrece/analisis/modify').'/"+id,
				function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});
			} else {
				$.prompt("<h1>Por favor Seleccione un registro</h1>");
			}
		});';

		$bodyscript .= '
		jQuery("#bcierre").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				$.post("'.site_url('leche/lrece/apertura/create').'",
				function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});
			} else {
				$.prompt("<h1>Por favor Seleccione un registro</h1>");
			}
		});';

		$bodyscript .= '
		$("#fedita").dialog({
			autoOpen: false, height: 400, width: 700, modal: true,
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
						try{
							var json = JSON.parse(r);
							if (json.status == "A"){
								apprise("Registro Guardado");
								$( "#fedita" ).dialog( "close" );
								grid.trigger("reloadGrid");
								//'.$this->datasis->jwinopen(site_url('formatos/ver/LRECE').'/\'+res.id+\'/id\'').';
								return true;
							} else {
								apprise(json.mensaje);
							}
						}catch(e){
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

		//$grid->addField('numero');
		//$grid->label('Numero');
		//$grid->params(array(
		//	'search'        => 'true',
		//	'editable'      => $editar,
		//	'width'         => 80,
		//	'edittype'      => "'text'",
		//	'editrules'     => '{ required:true}',
		//	'editoptions'   => '{ size:8, maxlength: 8 }',
		//));

		$grid->addField('id');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
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


		$grid->addField('ruta');
		$grid->label('Ruta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('chofer');
		$grid->label('Chofer');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:45, maxlength: 45 }',
		));


		$grid->addField('lleno');
		$grid->label('Lleno');
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


		$grid->addField('vacio');
		$grid->label('Vacio');
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


		$grid->addField('neto');
		$grid->label('Neto');
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


		$grid->addField('densidad');
		$grid->label('Densidad');
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


		$grid->addField('litros');
		$grid->label('Litros');
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


		$grid->addField('lista');
		$grid->label('Lista');
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


		$grid->addField('diferen');
		$grid->label('Diferen');
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


		$grid->addField('animal');
		$grid->label('Animal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('crios');
		$grid->label('Crios');
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


		$grid->addField('h2o');
		$grid->label('H2o');
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


		$grid->addField('temp');
		$grid->label('Temp');
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


		$grid->addField('brix');
		$grid->label('Brix');
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


		$grid->addField('grasa');
		$grid->label('Grasa');
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


		$grid->addField('acidez');
		$grid->label('Acidez');
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


		$grid->addField('cloruros');
		$grid->label('Cloruros');
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


		$grid->addField('dtoagua');
		$grid->label('Dtoagua');
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

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
				if (id){
					jQuery(gridId2).jqGrid("setGridParam",{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					jQuery(gridId2).trigger("reloadGrid");
				}
			}'
		);
		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('LRECE','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('LRECE','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('LRECE','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('LRECE','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: lreceadd,\n\t\teditfunc: lreceedit");

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
		$mWHERE = $grid->geneTopWhere('lrece');

		$response   = $grid->getData('lrece', array(array()), array(), false, $mWHERE, 'id','desc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$mcodp  = "??????";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM lrece WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('lrece', $data);
					echo "Registro Agregado";

					logusu('LRECE',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM lrece WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM lrece WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE lrece SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("lrece", $data);
				logusu('LRECE',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('lrece', $data);
				logusu('LRECE',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM lrece WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM lrece WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM lrece WHERE id=$id ");
				logusu('LRECE',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		//$grid->addField('numero');
		//$grid->label('Numero');
		//$grid->params(array(
		//	'search'        => 'true',
		//	'editable'      => $editar,
		//	'width'         => 80,
		//	'edittype'      => "'text'",
		//	'editrules'     => '{ required:true}',
		//	'editoptions'   => '{ size:8, maxlength: 8 }',
		//));

		$grid->addField('id');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));


		$grid->addField('productor');
		$grid->label('Productor');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:45, maxlength: 45 }',
		));


		$grid->addField('litros');
		$grid->label('Litros');
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


		$grid->addField('animal');
		$grid->label('Animal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('crios');
		$grid->label('Crios');
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


		$grid->addField('h2o');
		$grid->label('H2o');
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


		$grid->addField('temp');
		$grid->label('Temp');
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


		$grid->addField('brix');
		$grid->label('Brix');
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


		$grid->addField('grasa');
		$grid->label('Grasa');
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


		$grid->addField('acidez');
		$grid->label('Acidez');
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


		$grid->addField('cloruros');
		$grid->label('Cloruros');
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


		$grid->addField('dtoagua');
		$grid->label('Dtoagua');
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


		$grid->addField('id_lrece');
		$grid->label('Id_lrece');
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
	function getdatait( $id = 0 ){
		if ($id === 0 ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM lrece");
		}
		if(empty($id)) return "";
		$grid    = $this->jqdatagrid;
		$mSQL    = 'SELECT * FROM itlrece WHERE id_lrece='.$this->db->escape($id);
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setDatait(){
	}

	//***********************************
	// DataEdit
	//***********************************

	function apertura(){
		$this->rapyd->load('dataedit');

		$script='
		$(function(){
			$(".inputnum").numeric(".");
		});
		';

		$edit = new DataEdit($this->tits, 'lrece');
		$edit->script($script,'create');
		$edit->script($script,'modify');
		$edit->on_save_redirect=false;
		$edit->back_url = site_url($this->url.'filteredgrid');

		//$edit->post_process('insert','_post_insert');
		//$edit->post_process('update','_post_update');
		//$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert' ,'_pre_apertura_insert');
		//$edit->pre_process('update' ,'_pre_apertura_update');
		//$edit->pre_process('delete' ,'_pre_apertura_delete');

		$edit->ruta = new dropdownField('Ruta', 'ruta');
		$edit->ruta->rule = 'trim|max_length[4]|required';
		$edit->ruta->option('','Seleccionar');
		$edit->ruta->options('SELECT codigo, CONCAT(codigo," ", nombre) nombre FROM lruta ORDER BY nombre');
		$edit->ruta->style = 'width:166px';

		$edit->lleno = new inputField('Peso','lleno');
		$edit->lleno->rule='max_length[16]|numeric|required';
		$edit->lleno->css_class='inputnum';
		$edit->lleno->size =12;
		$edit->lleno->mode='autohide';
		$edit->lleno->maxlength =16;

		$edit->nombre = new inputField('Nombre del chofer','nombre');
		$edit->nombre->rule='max_length[45]|strtoupper|required';
		$edit->nombre->size =40;
		$edit->nombre->maxlength =45;
		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			echo $edit->output;
		}
	}

	function _pre_apertura_insert($do){
		$do->set('fecha',date('Y-m-d'));
	}

	function analisis(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'lrece');
		$edit->on_save_redirect=false;
		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		//$edit->numero = new inputField('Numero','numero');
		//$edit->numero->rule='max_length[8]';
		//$edit->numero->size =9;
		//$edit->numero->mode='autohide';
		//$edit->numero->maxlength =8;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->mode='autohide';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->ruta = new inputField('Ruta','ruta');
		$edit->ruta->rule='max_length[4]';
		$edit->ruta->mode='autohide';
		$edit->ruta->size =6;
		$edit->ruta->maxlength =4;

		$edit->nombre = new inputField('Nombre del chofer','nombre');
		$edit->nombre->rule='max_length[45]';
		$edit->nombre->mode='autohide';
		$edit->nombre->size =40;
		$edit->nombre->maxlength =45;

		$edit->lleno = new inputField('Lleno','lleno');
		$edit->lleno->rule='max_length[16]|numeric';
		$edit->lleno->css_class='inputnum';
		$edit->lleno->size =12;
		$edit->lleno->mode='autohide';
		$edit->lleno->maxlength =16;

		$edit->vacio = new inputField('Vacio','vacio');
		$edit->vacio->rule='max_length[16]|numeric';
		$edit->vacio->css_class='inputnum';
		$edit->vacio->size =12;
		$edit->vacio->maxlength =16;

		$edit->neto = new inputField('Neto','neto');
		$edit->neto->rule='max_length[16]|numeric';
		$edit->neto->css_class='inputnum';
		$edit->neto->size =12;
		$edit->neto->maxlength =16;

		$edit->densidad = new inputField('Densidad','densidad');
		$edit->densidad->rule='max_length[10]|numeric';
		$edit->densidad->css_class='inputnum';
		$edit->densidad->size =12;
		$edit->densidad->maxlength =10;

		$edit->litros = new inputField('Litros','litros');
		$edit->litros->rule='max_length[16]|numeric';
		$edit->litros->css_class='inputnum';
		$edit->litros->size =12;
		$edit->litros->maxlength =16;

		$edit->lista = new inputField('Lista','lista');
		$edit->lista->rule='max_length[16]|numeric';
		$edit->lista->css_class='inputnum';
		$edit->lista->size =12;
		$edit->lista->maxlength =16;

		$edit->diferen = new inputField('Diferen','diferen');
		$edit->diferen->rule='max_length[16]|numeric';
		$edit->diferen->css_class='inputnum';
		$edit->diferen->size =12;
		$edit->diferen->maxlength =16;

		$edit->animal = new inputField('Animal','animal');
		$edit->animal->rule='max_length[1]';
		$edit->animal->size =3;
		$edit->animal->maxlength =1;

		$edit->crios = new inputField('Crios','crios');
		$edit->crios->rule='max_length[10]|numeric';
		$edit->crios->css_class='inputnum';
		$edit->crios->size =12;
		$edit->crios->maxlength =10;

		$edit->h2o = new inputField('H2o','h2o');
		$edit->h2o->rule='max_length[10]|numeric';
		$edit->h2o->css_class='inputnum';
		$edit->h2o->size =12;
		$edit->h2o->maxlength =10;

		$edit->temp = new inputField('Temp','temp');
		$edit->temp->rule='max_length[10]|numeric';
		$edit->temp->css_class='inputnum';
		$edit->temp->size =12;
		$edit->temp->maxlength =10;

		$edit->brix = new inputField('Brix','brix');
		$edit->brix->rule='max_length[10]|numeric';
		$edit->brix->css_class='inputnum';
		$edit->brix->size =12;
		$edit->brix->maxlength =10;

		$edit->grasa = new inputField('Grasa','grasa');
		$edit->grasa->rule='max_length[10]|numeric';
		$edit->grasa->css_class='inputnum';
		$edit->grasa->size =12;
		$edit->grasa->maxlength =10;

		$edit->acidez = new inputField('Acidez','acidez');
		$edit->acidez->rule='max_length[10]|numeric';
		$edit->acidez->css_class='inputnum';
		$edit->acidez->size =12;
		$edit->acidez->maxlength =10;

		$edit->cloruros = new inputField('Cloruros','cloruros');
		$edit->cloruros->rule='max_length[10]|numeric';
		$edit->cloruros->css_class='inputnum';
		$edit->cloruros->size =12;
		$edit->cloruros->maxlength =10;

		$edit->dtoagua = new inputField('Dtoagua','dtoagua');
		$edit->dtoagua->rule='max_length[10]|numeric';
		$edit->dtoagua->css_class='inputnum';
		$edit->dtoagua->size =12;
		$edit->dtoagua->maxlength =10;

		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			echo $edit->output;
		}
	}

	function vaqueras($id){
		//Cheque que tenga vehiculos
		$dbid=$this->db->escape($id);
		$cana=$this->datasis->dameval("SELECT COUNT(*) AS cana FROM itlrece WHERE id_scst=$dbid");
		if(empty($cana)){
			$mSQL="SELECT c.codigo,c.descrip,c.peso,b.cantidad AS cana
				FROM lrece   AS a
				JOIN lvaca AS b ON a.ruta=b.ruta
				WHERE a.id=$dbid";
			$query = $this->db->query($mSQL);

			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					for($i=0;$i<$row->cana;$i++){
						$data=array();
						$data['densidad']   ='';
						$data['litros']     ='';
						$data['lista']      ='';
						$data['animal']     ='';
						$data['crios']      ='';
						$data['h2o']        ='';
						$data['temp']       ='';
						$data['brix']       ='';
						$data['grasa']      ='';
						$data['acidez']     ='';
						$data['cloruros']   ='';
						$data['dtoagua']    ='';
						$data['id_lrece']   ='';
						$data['id']         ='';

						$sql = $this->db->insert_string('sinvehiculo', $data);
						$this->db->simple_query($sql);
					}
				}
			}else{
				echo 'Compra no tiene Veh&iacute;culos.';
				exit();
			}
		}




	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'lrece');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =9;
		$edit->numero->maxlength =8;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->ruta = new inputField('Ruta','ruta');
		$edit->ruta->rule='max_length[4]';
		$edit->ruta->size =6;
		$edit->ruta->maxlength =4;

		$edit->chofer = new inputField('Chofer','chofer');
		$edit->chofer->rule='max_length[5]';
		$edit->chofer->size =6;
		$edit->chofer->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[45]';
		$edit->nombre->size =40;
		$edit->nombre->maxlength =45;

		$edit->lleno = new inputField('Lleno','lleno');
		$edit->lleno->rule='max_length[16]|numeric';
		$edit->lleno->css_class='inputnum';
		$edit->lleno->size =12;
		$edit->lleno->maxlength =16;

		$edit->vacio = new inputField('Vacio','vacio');
		$edit->vacio->rule='max_length[16]|numeric';
		$edit->vacio->css_class='inputnum';
		$edit->vacio->size =12;
		$edit->vacio->maxlength =16;

		$edit->neto = new inputField('Neto','neto');
		$edit->neto->rule='max_length[16]|numeric';
		$edit->neto->css_class='inputnum';
		$edit->neto->size =12;
		$edit->neto->maxlength =16;

		$edit->densidad = new inputField('Densidad','densidad');
		$edit->densidad->rule='max_length[10]|numeric';
		$edit->densidad->css_class='inputnum';
		$edit->densidad->size =12;
		$edit->densidad->maxlength =10;

		$edit->litros = new inputField('Litros','litros');
		$edit->litros->rule='max_length[16]|numeric';
		$edit->litros->css_class='inputnum';
		$edit->litros->size =12;
		$edit->litros->maxlength =16;

		$edit->lista = new inputField('Lista','lista');
		$edit->lista->rule='max_length[16]|numeric';
		$edit->lista->css_class='inputnum';
		$edit->lista->size =12;
		$edit->lista->maxlength =16;

		$edit->diferen = new inputField('Diferen','diferen');
		$edit->diferen->rule='max_length[16]|numeric';
		$edit->diferen->css_class='inputnum';
		$edit->diferen->size =12;
		$edit->diferen->maxlength =16;

		$edit->animal = new inputField('Animal','animal');
		$edit->animal->rule='max_length[1]';
		$edit->animal->size =3;
		$edit->animal->maxlength =1;

		$edit->crios = new inputField('Crios','crios');
		$edit->crios->rule='max_length[10]|numeric';
		$edit->crios->css_class='inputnum';
		$edit->crios->size =12;
		$edit->crios->maxlength =10;

		$edit->h2o = new inputField('H2o','h2o');
		$edit->h2o->rule='max_length[10]|numeric';
		$edit->h2o->css_class='inputnum';
		$edit->h2o->size =12;
		$edit->h2o->maxlength =10;

		$edit->temp = new inputField('Temp','temp');
		$edit->temp->rule='max_length[10]|numeric';
		$edit->temp->css_class='inputnum';
		$edit->temp->size =12;
		$edit->temp->maxlength =10;

		$edit->brix = new inputField('Brix','brix');
		$edit->brix->rule='max_length[10]|numeric';
		$edit->brix->css_class='inputnum';
		$edit->brix->size =12;
		$edit->brix->maxlength =10;

		$edit->grasa = new inputField('Grasa','grasa');
		$edit->grasa->rule='max_length[10]|numeric';
		$edit->grasa->css_class='inputnum';
		$edit->grasa->size =12;
		$edit->grasa->maxlength =10;

		$edit->acidez = new inputField('Acidez','acidez');
		$edit->acidez->rule='max_length[10]|numeric';
		$edit->acidez->css_class='inputnum';
		$edit->acidez->size =12;
		$edit->acidez->maxlength =10;

		$edit->cloruros = new inputField('Cloruros','cloruros');
		$edit->cloruros->rule='max_length[10]|numeric';
		$edit->cloruros->css_class='inputnum';
		$edit->cloruros->size =12;
		$edit->cloruros->maxlength =10;

		$edit->dtoagua = new inputField('Dtoagua','dtoagua');
		$edit->dtoagua->rule='max_length[10]|numeric';
		$edit->dtoagua->css_class='inputnum';
		$edit->dtoagua->size =12;
		$edit->dtoagua->maxlength =10;

		$edit->build();

		$script= '';

		$conten["form"] =&  $edit;
		$conten['script'] = $script;
		$this->load->view('view_lrece', $conten);

	}

	function _pre_insert($do){
		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}
}

?>
