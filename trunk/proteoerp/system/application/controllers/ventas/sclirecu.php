<?php 
class Sclirecu extends Controller {
	var $mModulo = 'SCLIRECU';
	var $titp    = 'Clientes con Medicamentos Recurrentes';
	var $tits    = 'Clientes con Medicamentos Recurrentes';
	var $url     = 'ventas/sclirecu/';

	function Sclirecu(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'SCLIRECU', 0 );
	}

	function index(){
		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
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

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		//Botones Panel Izq
		//$grid->wbotonadd(array("id"=>"edocta",   "img"=>"images/pdf_logo.gif",  "alt" => "Formato PDF", "label"=>"Estado de Cuenta"));
		$WestPanel = $grid->deploywestp();

		//Panel Central
		$centerpanel = $grid->centerpanel( $id = "radicional", $param['grids'][0]['gridname'] );

		// Configura los Paneles
		$readyLayout = $grid->readyLayout2( 212, 110, $param['grids'][0]['gridname']);

		$adic = array(
		array("id"=>"fedita",  "title"=>"Agregar/Editar Registro")
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		//$param['EastPanel'] = $EastPanel;

		$param['WestPanel']   = $WestPanel;
		$param['readyLayout'] = $readyLayout;
		$param['SouthPanel']  = $SouthPanel;
		$param['centerpanel'] = $centerpanel;
		$param['listados']    = $this->datasis->listados('SCLIRECU', 'JQ');
		$param['otros']       = $this->datasis->otros('SCLIRECU', 'JQ');
		$param['temas']       = array('proteo','darkness','anexos1');
		$param['bodyscript']  = $bodyscript;
		$param['tabs']        = false;
		$param['encabeza']    = $this->titp;
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0 ){
		$bodyscript = '		<script type="text/javascript">';

		$bodyscript .= '
		function sclirecuadd() {
			$.post("'.site_url('ventas/sclirecu/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function sclirecuedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('ventas/sclirecu/dataedit/modify').'/"+id, function(data){
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
			autoOpen: false, height: 400, width: 720, modal: true,
			buttons: {
			"Guardar": function() {
				var bValid = true;
				var murl = $("#df1").attr("action");
				allFields.removeClass( "ui-state-error" );
				$.ajax({
					type: "POST", dataType: "html", async: false,
					url: murl,
					data: $("#df1").serialize(),
					success: function(r,s,x) {
						if ( r.length == 0 ) {
							apprise("Registro Guardado");
							$( "#fcompra" ).dialog( "close" );
							grid.trigger("reloadGrid");
							'.$this->datasis->jwinopen(site_url('formatos/ver/SCLIRECU').'/\'+res.id+\'/id\'').';
							return true;
						} else { $("#fedita").html(r); }
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

		$grid->addField('cedula');
		$grid->label('Cedula');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 90,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:13, maxlength: 13 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 230,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:45, maxlength: 45 }',
		));


		$grid->addField('direccion');
		$grid->label('Direccion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:2, cols:60}'",
		));


		$grid->addField('tcelular');
		$grid->label('Tcelular');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:25, maxlength: 25 }',
		));


		$grid->addField('toficina');
		$grid->label('Toficina');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:25, maxlength: 25 }',
		));


		$grid->addField('tcasa');
		$grid->label('Tcasa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:25, maxlength: 25 }',
		));


		$grid->addField('email');
		$grid->label('Email');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:100, maxlength: 100 }',
		));

/*
		$grid->addField('observa');
		$grid->label('Observa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:2, cols:60}'",
		));

		$grid->addField('mensaje');
		$grid->label('Mensaje');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 40 }',
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


		$grid->addField('pin');
		$grid->label('Pin');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 10 }',
		));


		$grid->addField('fb');
		$grid->label('Fb');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:120, maxlength: 120 }',
		));


		$grid->addField('twitter');
		$grid->label('Twitter');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:120, maxlength: 120 }',
		));


		$grid->addField('chat');
		$grid->label('Chat');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:120, maxlength: 120 }',
		));


		$grid->addField('cliente');
		$grid->label('Cliente');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('barras1');
		$grid->label('Barras1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('descrip1');
		$grid->label('Descrip1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));


		$grid->addField('fecha1');
		$grid->label('Fecha1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('cant1');
		$grid->label('Cant1');
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


		$grid->addField('dias1');
		$grid->label('Dias1');
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


		$grid->addField('barras2');
		$grid->label('Barras2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('descrip2');
		$grid->label('Descrip2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));


		$grid->addField('fecha2');
		$grid->label('Fecha2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('cant2');
		$grid->label('Cant2');
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

		$grid->addField('dias2');
		$grid->label('Dias2');
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

		$grid->addField('barras3');
		$grid->label('Barras3');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));

		$grid->addField('descrip3');
		$grid->label('Descrip3');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));

		$grid->addField('fecha3');
		$grid->label('Fecha3');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('cant3');
		$grid->label('Cant3');
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

		$grid->addField('dias3');
		$grid->label('Dias3');
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
		$grid->setHeight('270');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('SCLIRECU','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('SCLIRECU','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('SCLIRECU','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('SCLIRECU','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setonSelectRow('
			function(id){
				var ret = $(gridId1).getRowData(id);
				$("#radicional").html("<table width=\'95%\' border=1><tr style=\'background:grey;color:black;\'><td>Cod Barras</td><td>Descripcion</td><td>Fecha</td><td>Cant.</td><td>Dias</td></tr><tr><td>"+ret.barras1+"</td><td>"+ret.descrip1+"</td><td>"+ret.fecha1+"</td><td>"+ret.cant1+"</td><td>"+ret.dias1+"</td></tr><tr><td>"+ret.barras2+"</td><td>"+ret.descrip2+"</td><td>"+ret.fecha2+"</td><td>"+ret.cant2+"</td><td>"+ret.dias2+"</td></tr><tr><td>"+ret.barras3+"</td><td>"+ret.descrip3+"</td><td>"+ret.fecha3+"</td><td>"+ret.cant3+"</td><td>"+ret.dias3+"</td></tr></table>");
			}
		');

		$grid->setBarOptions("\t\taddfunc: sclirecuadd,\n\t\teditfunc: sclirecuedit,\n\t\viewfunc: sclirecuedit");

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
		$mWHERE = $grid->geneTopWhere('sclirecu');

		$response   = $grid->getData('sclirecu', array(array()), array(), false, $mWHERE );
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
		$mcodp  = "cedula";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM sclirecu WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('sclirecu', $data);
					echo "Registro Agregado";

					logusu('SCLIRECU',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM sclirecu WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM sclirecu WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE sclirecu SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("sclirecu", $data);
				logusu('SCLIRECU',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('sclirecu', $data);
				logusu('SCLIRECU',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT concat($mcodp, ' ', nombre) cedula FROM sclirecu WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sclirecu WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM sclirecu WHERE id=$id ");
				logusu('SCLIRECU',"Registro $meco ELIMINADO");
				echo "Registro Eliminado ".$meco;
			}
		};
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'sclirecu');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->cedula = new inputField('Cedula','cedula');
		$edit->cedula->rule='max_length[13]';
		$edit->cedula->size =15;
		$edit->cedula->maxlength =13;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[45]';
		$edit->nombre->size =40;
		$edit->nombre->maxlength =45;

		$edit->direccion = new textareaField('Direccion','direccion');
		//$edit->direccion->rule='max_length[8]';
		$edit->direccion->cols = 75;
		$edit->direccion->rows = 5;

		$edit->tcelular = new inputField('Cel.','tcelular');
		$edit->tcelular->rule='max_length[25]';
		$edit->tcelular->size =17;
		$edit->tcelular->maxlength =25;

		$edit->toficina = new inputField('Ofi.','toficina');
		$edit->toficina->rule='max_length[25]';
		$edit->toficina->size =17;
		$edit->toficina->maxlength =25;

		$edit->tcasa = new inputField('Casa','tcasa');
		$edit->tcasa->rule='max_length[25]';
		$edit->tcasa->size =17;
		$edit->tcasa->maxlength =25;

		$edit->email = new inputField('Email','email');
		$edit->email->rule='max_length[100]';
		$edit->email->size =43;
		$edit->email->maxlength =100;

		$edit->observa = new textareaField('Observaciones','observa');
		//$edit->observa->rule='max_length[8]';
		$edit->observa->cols = 75;
		$edit->observa->rows = 5;

		$edit->pin = new inputField('Pin','pin');
		$edit->pin->rule='max_length[10]';
		$edit->pin->size =17;
		$edit->pin->maxlength =10;

		$edit->fb = new inputField('FaceBook','fb');
		$edit->fb->rule='max_length[120]';
		$edit->fb->size =60;
		$edit->fb->maxlength =120;

		$edit->twitter = new inputField('Twitter','twitter');
		$edit->twitter->rule='max_length[120]';
		$edit->twitter->size =60;
		$edit->twitter->maxlength =120;

		$edit->chat = new inputField('Mensajeria','chat');
		$edit->chat->rule='max_length[120]';
		$edit->chat->size =60;
		$edit->chat->maxlength =120;

		$edit->cliente = new inputField('Cliente','cliente');
		$edit->cliente->rule='max_length[20]';
		$edit->cliente->size =10;
		$edit->cliente->maxlength =20;

		$edit->barras1 = new inputField('Barras1','barras1');
		$edit->barras1->rule='max_length[15]';
		$edit->barras1->size =13;
		$edit->barras1->maxlength =15;

		$edit->descrip1 = new inputField('Descrip1','descrip1');
		$edit->descrip1->rule='max_length[50]';
		$edit->descrip1->size =40;
		$edit->descrip1->maxlength =50;

		$edit->fecha1 = new dateField('Fecha1','fecha1');
		$edit->fecha1->rule='chfecha';
		$edit->fecha1->size =10;
		$edit->fecha1->maxlength =8;
		$edit->fecha1->calendar = false;

		$edit->cant1 = new inputField('Cant1','cant1');
		$edit->cant1->rule='max_length[6]|integer';
		$edit->cant1->css_class='inputonlynum';
		$edit->cant1->size =2;
		$edit->cant1->maxlength =6;

		$edit->dias1 = new inputField('Dias1','dias1');
		$edit->dias1->rule='max_length[6]|integer';
		$edit->dias1->css_class='inputonlynum';
		$edit->dias1->size =2;
		$edit->dias1->maxlength =6;

		$edit->barras2 = new inputField('Barras2','barras2');
		$edit->barras2->rule='max_length[15]';
		$edit->barras2->size =13;
		$edit->barras2->maxlength =15;

		$edit->descrip2 = new inputField('Descrip2','descrip2');
		$edit->descrip2->rule='max_length[50]';
		$edit->descrip2->size =40;
		$edit->descrip2->maxlength =50;

		$edit->fecha2 = new dateField('Fecha2','fecha2');
		$edit->fecha2->rule='chfecha';
		$edit->fecha2->size =10;
		$edit->fecha2->maxlength =8;
		$edit->fecha2->calendar = false;

		$edit->cant2 = new inputField('Cant2','cant2');
		$edit->cant2->rule='max_length[6]|integer';
		$edit->cant2->css_class='inputonlynum';
		$edit->cant2->size =2;
		$edit->cant2->maxlength =6;

		$edit->dias2 = new inputField('Dias2','dias2');
		$edit->dias2->rule='max_length[6]|integer';
		$edit->dias2->css_class='inputonlynum';
		$edit->dias2->size =2;
		$edit->dias2->maxlength =6;

		$edit->barras3 = new inputField('Barras3','barras3');
		$edit->barras3->rule='max_length[15]';
		$edit->barras3->size =13;
		$edit->barras3->maxlength =15;

		$edit->descrip3 = new inputField('Descrip3','descrip3');
		$edit->descrip3->rule='max_length[50]';
		$edit->descrip3->size =40;
		$edit->descrip3->maxlength =50;

		$edit->fecha3 = new dateField('Fecha3','fecha3');
		$edit->fecha3->rule='chfecha';
		$edit->fecha3->size =10;
		$edit->fecha3->maxlength =8;
		$edit->fecha3->calendar = false;

		$edit->cant3 = new inputField('Cant3','cant3');
		$edit->cant3->rule='max_length[6]|integer';
		$edit->cant3->css_class='inputonlynum';
		$edit->cant3->size =2;
		$edit->cant3->maxlength =6;

		$edit->dias3 = new inputField('Dias3','dias3');
		$edit->dias3->rule='max_length[6]|integer';
		$edit->dias3->css_class='inputonlynum';
		$edit->dias3->size =2;
		$edit->dias3->maxlength =6;

		$edit->build();

		$script= '';

		$conten['form'] =& $edit;

		$data['form']  = & $edit;
		//$data['content'] = $edit->output;
		$data['script']  = $script;
		$this->load->view('jqgrid/sclirecu', $data);

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
