<?php 
//require_once(BASEPATH.'application/controllers/validaciones.php');
class Club extends Controller {
	var $mModulo = 'CLUB';
	var $titp    = 'Modulo CLUB';
	var $tits    = 'Modulo CLUB';
	var $url     = 'supermercado/club/';

	function Club(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'CLUB', $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('club','id') ) {
			$this->db->simple_query('ALTER TABLE club DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE club ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE club ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
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
		//$grid->wbotonadd(array("id"=>"edocta",   "img"=>"images/pdf_logo.gif",  "alt" => "Formato PDF", "label"=>"Ejemplo"));
		$WestPanel = $grid->deploywestp();

		$adic = array(
		array("id"=>"fedita",  "title"=>"Agregar/Editar Registro")
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel'] = $EastPanel;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('CLUB', 'JQ');
		$param['otros']       = $this->datasis->otros('CLUB', 'JQ');
		$param['temas']       = array('proteo','darkness','anexos1');
		$param['bodyscript']  = $bodyscript;
		$param['tabs']        = false;
		$param['encabeza']    = $this->titp;
		$param['tamano']      = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0 ){
		$bodyscript = '		<script type="text/javascript">';

		$bodyscript .= '
		function clubadd() {
			$.post("'.site_url('supermercado/club/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function clubedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('supermercado/club/dataedit/modify').'/"+ret.cod_tar, function(data){
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
			autoOpen: false, height: 500, width: 700, modal: true,
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
							'.$this->datasis->jwinopen(site_url('formatos/ver/CLUB').'/\'+res.id+\'/id\'').';
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

		$grid->addField('cedula');
		$grid->label('Cedula');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));


		$grid->addField('cod_tar');
		$grid->label('Tarjeta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));

/*
		$grid->addField('nit');
		$grid->label('Nit');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 110,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:11, maxlength: 11 }',
		));
*/

		$grid->addField('fec_nac');
		$grid->label('Nacimiento');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('fec_ing');
		$grid->label('Ingreo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('nombres');
		$grid->label('Nombres');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:45, maxlength: 45 }',
		));


		$grid->addField('apellidos');
		$grid->label('Apellidos');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:45, maxlength: 45 }',
		));


		$grid->addField('empresa');
		$grid->label('Empresa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('direc1');
		$grid->label('Direccion 1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('direc2');
		$grid->label('Direccion 2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('telefono1');
		$grid->label('Telefono1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));


		$grid->addField('telefono2');
		$grid->label('Telefono2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));


		$grid->addField('telefono3');
		$grid->label('Telefono3');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 110,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:11, maxlength: 11 }',
		));


		$grid->addField('telefono4');
		$grid->label('Telefono4');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 110,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:11, maxlength: 11 }',
		));

/*
		$grid->addField('banco1');
		$grid->label('Banco1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));


		$grid->addField('cuenta1');
		$grid->label('Cuenta1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('banco2');
		$grid->label('Banco2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));


		$grid->addField('cuenta2');
		$grid->label('Cuenta2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));
*/

		$grid->addField('ing_mes');
		$grid->label('Ing_mes');
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


		$grid->addField('limite');
		$grid->label('Limite');
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


		$grid->addField('status');
		$grid->label('Status');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('cod_cli');
		$grid->label('Cod_cli');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('modifi');
		$grid->label('Modifi');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('creditos');
		$grid->label('Creditos');
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


		$grid->addField('zona');
		$grid->label('Zona');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:6, maxlength: 6 }',
		));


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('CLUB','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('CLUB','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('CLUB','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('CLUB','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: clubadd,\n\t\teditfunc: clubedit");

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
		$mWHERE = $grid->geneTopWhere('club');

		$response   = $grid->getData('club', array(array()), array(), false, $mWHERE );
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
		$mcodp  = "??????";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM club WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('club', $data);
					echo "Registro Agregado";

					logusu('CLUB',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM club WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM club WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE club SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("club", $data);
				logusu('CLUB',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('club', $data);
				logusu('CLUB',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM club WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM club WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM club WHERE id=$id ");
				logusu('CLUB',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

/*
	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'club');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->cedula = new inputField('Cedula','cedula');
		$edit->cedula->rule='max_length[12]';
		$edit->cedula->size =14;
		$edit->cedula->maxlength =12;

		$edit->cod_tar = new inputField('Cod_tar','cod_tar');
		$edit->cod_tar->rule='max_length[12]';
		$edit->cod_tar->size =14;
		$edit->cod_tar->maxlength =12;

		$edit->nit = new inputField('Nit','nit');
		$edit->nit->rule='max_length[11]';
		$edit->nit->size =13;
		$edit->nit->maxlength =11;

		$edit->fec_nac = new dateField('Fec_nac','fec_nac');
		$edit->fec_nac->rule='chfecha';
		$edit->fec_nac->size =10;
		$edit->fec_nac->maxlength =8;

		$edit->fec_ing = new dateField('Fec_ing','fec_ing');
		$edit->fec_ing->rule='chfecha';
		$edit->fec_ing->size =10;
		$edit->fec_ing->maxlength =8;

		$edit->nombres = new inputField('Nombres','nombres');
		$edit->nombres->rule='max_length[45]';
		$edit->nombres->size =47;
		$edit->nombres->maxlength =45;

		$edit->apellidos = new inputField('Apellidos','apellidos');
		$edit->apellidos->rule='max_length[45]';
		$edit->apellidos->size =47;
		$edit->apellidos->maxlength =45;

		$edit->empresa = new inputField('Empresa','empresa');
		$edit->empresa->rule='max_length[30]';
		$edit->empresa->size =32;
		$edit->empresa->maxlength =30;

		$edit->direc1 = new inputField('Direc1','direc1');
		$edit->direc1->rule='max_length[30]';
		$edit->direc1->size =32;
		$edit->direc1->maxlength =30;

		$edit->direc2 = new inputField('Direc2','direc2');
		$edit->direc2->rule='max_length[30]';
		$edit->direc2->size =32;
		$edit->direc2->maxlength =30;

		$edit->telefono1 = new inputField('Telefono1','telefono1');
		$edit->telefono1->rule='max_length[12]';
		$edit->telefono1->size =14;
		$edit->telefono1->maxlength =12;

		$edit->telefono2 = new inputField('Telefono2','telefono2');
		$edit->telefono2->rule='max_length[12]';
		$edit->telefono2->size =14;
		$edit->telefono2->maxlength =12;

		$edit->telefono3 = new inputField('Telefono3','telefono3');
		$edit->telefono3->rule='max_length[11]';
		$edit->telefono3->size =13;
		$edit->telefono3->maxlength =11;

		$edit->telefono4 = new inputField('Telefono4','telefono4');
		$edit->telefono4->rule='max_length[11]';
		$edit->telefono4->size =13;
		$edit->telefono4->maxlength =11;

		$edit->banco1 = new inputField('Banco1','banco1');
		$edit->banco1->rule='max_length[20]';
		$edit->banco1->size =22;
		$edit->banco1->maxlength =20;

		$edit->cuenta1 = new inputField('Cuenta1','cuenta1');
		$edit->cuenta1->rule='max_length[15]';
		$edit->cuenta1->size =17;
		$edit->cuenta1->maxlength =15;

		$edit->banco2 = new inputField('Banco2','banco2');
		$edit->banco2->rule='max_length[20]';
		$edit->banco2->size =22;
		$edit->banco2->maxlength =20;

		$edit->cuenta2 = new inputField('Cuenta2','cuenta2');
		$edit->cuenta2->rule='max_length[15]';
		$edit->cuenta2->size =17;
		$edit->cuenta2->maxlength =15;

		$edit->ing_mes = new inputField('Ing_mes','ing_mes');
		$edit->ing_mes->rule='max_length[11]|integer';
		$edit->ing_mes->css_class='inputonlynum';
		$edit->ing_mes->size =13;
		$edit->ing_mes->maxlength =11;

		$edit->limite = new inputField('Limite','limite');
		$edit->limite->rule='max_length[11]|integer';
		$edit->limite->css_class='inputonlynum';
		$edit->limite->size =13;
		$edit->limite->maxlength =11;

		$edit->status = new inputField('Status','status');
		$edit->status->rule='max_length[1]';
		$edit->status->size =3;
		$edit->status->maxlength =1;

		$edit->tipo = new inputField('Tipo','tipo');
		$edit->tipo->rule='max_length[1]';
		$edit->tipo->size =3;
		$edit->tipo->maxlength =1;

		$edit->cod_cli = new inputField('Cod_cli','cod_cli');
		$edit->cod_cli->rule='max_length[5]';
		$edit->cod_cli->size =7;
		$edit->cod_cli->maxlength =5;

		$edit->modifi = new dateField('Modifi','modifi');
		$edit->modifi->rule='chfecha';
		$edit->modifi->size =10;
		$edit->modifi->maxlength =8;

		$edit->creditos = new inputField('Creditos','creditos');
		$edit->creditos->rule='max_length[17]|numeric';
		$edit->creditos->css_class='inputnum';
		$edit->creditos->size =19;
		$edit->creditos->maxlength =17;

		$edit->zona = new inputField('Zona','zona');
		$edit->zona->rule='max_length[6]';
		$edit->zona->size =8;
		$edit->zona->maxlength =6;

		$edit->build();

		$script= '';

		$data['content'] = $edit->output;
		$data['script'] = $script;
		$this->load->view('jqgrid/ventanajq', $data);

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



class Club extends validaciones {
 
	function Club(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->load->library("menues");
		$this->datasis->modulo_id('121',1);
	}
	function index(){
		redirect("supermercado/club/filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro de Club de Compras");
		
		$select=array("cedula","cod_tar","CONCAT_WS(' ',nombres,apellidos) AS nombre","direc1");
		$filter->db->select($select);
		$filter->db->from('club');
			
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=30;
		$filter->nombre->db_name="CONCAT_WS(' ',nombres,apellidos)";

		$filter->cod_tar = new inputField("Tarjeta", "cod_tar");
		$filter->cod_tar->size=30;

		$filter->cedula = new inputField("C&eacute;dula", "cedula");
		$filter->cedula->size=15;

		$filter->buttons("reset","search");
		$filter->build();
		$link = anchor("supermercado/club/dataedit/show/<#cod_tar#>",'<#nombre#>');

		$grid = new DataGrid("Lista de Usuarios");
		$grid->order_by("nombre","asc");
		$grid->per_page = 10;

		$grid->column_orderby("Nombres",$link,"nombres");
		$grid->column("C&eacute;dula","cedula");
		$grid->column("Tarjeta","cod_tar");
		$grid->column("Direcci&oacute;n","direc1");
		
		$grid->add("supermercado/club/dataedit/create");
		$grid->build();

		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Modulo de Club de Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}
*/

	function dataedit(){
		$modbus=array(
			'tabla'   =>'scli',
			'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'contacto'=>'Contacto'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cod_cli'),
			'titulo'  =>'Buscar Cliente');
		$boton=$this->datasis->modbus($modbus);
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
			$("#banco1").change(function () { acuenta(); }).change();
			$("#banco2").change(function () { acuenta(); }).change();
		});
		
		function acuenta(){
			for(i=1;i<=2;i++){
				vbanco=$("#banco"+i).val();
				if(vbanco.length>0){
					$("#tr_cuenta"+i).show();
				}else{
					$("#cuenta"+i).val("");
					$("#tr_cuenta"+i).hide();
				}
			}		
		}
		';

		$this->rapyd->load("dataedit");
		$edit = new DataEdit("Club de Compras", "club");

		$edit->script($script, "create");
		$edit->script($script, "modify");
		//$edit->back_url = site_url("supermercado/club/filteredgrid");
		
		$edit->pre_process('delete','_pre_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_insert');	
		$edit->post_process('insert','_post_insert');
		$edit->post_process("update","_post_update");
		$edit->post_process("delete","_post_delete");
	
		$edit->cod_tar = new inputField("C&oacute;digo de Tarjeta", "cod_tar");
		$edit->cod_tar->rule = "required|callback_chexiste";
		$edit->cod_tar->mode = "autohide";
		$edit->cod_tar->size = 15;
		$edit->cod_tar->maxlength = 12;
		
		$edit->fec_nac = new DateonlyField("Fecha de Nacimiento", "fec_nac");
		$edit->fec_nac->size = 11;
		$edit->fec_nac->group = "Datos del Cliente";

		$edit->fec_ing = new DateonlyField("Fecha de Ingreso", "fec_ing");
		$edit->fec_ing->size = 11;
		$edit->fec_ing->group = "Datos del Cliente";
		
		$edit->cedula =  new inputField("C&eacute;dula", "cedula");
		$edit->cedula->rule = "required|callback_chci";
		$edit->cedula->size = 12;
		$edit->cedula->group = "Datos del Cliente";
		
		$edit->nombre = new inputField("Nombres", "nombres");
		$edit->nombre->rule = "strtoupper|required";
		$edit->nombre->size = 45;
		$edit->nombre->maxlength = 25;
		$edit->nombre->group = "Datos del Cliente";

		$edit->apellidos = new inputField("Apellidos", "apellidos");
		$edit->apellidos->rule = "strtoupper|required";
		$edit->apellidos->size = 45;
		$edit->apellidos->maxlength = 25;
		$edit->apellidos->group = "Datos del Cliente";

		$edit->direc1 = new inputField("Direcci&oacute;n", "direc1");
		$edit->direc1->group = "Datos del Cliente";
		$edit->direc1->maxlength = 30;
		$edit->direc1->size = 50;
		
		$edit->direc2 = new inputField("&nbsp;", "direc2");
		$edit->direc2->size = 50;
		$edit->direc2->maxlength = 30;
		$edit->direc2->group = "Datos del Cliente";
		
		$edit->zona = new dropdownField("Zona", "zona");
		$edit->zona->option("","No Asignado");
		$edit->zona->options("SELECT codigo, nombre FROM zona ORDER BY nombre");
		$edit->zona->group = "Datos del Cliente";

		for($i=1;$i<=4;$i++){
			$obj="telefono$i";
			$edit->$obj = new inputField("Tel&eacute;fono $i",$obj);
			$edit->$obj->size = 31;
			$edit->$obj->maxlength = 11;
			$edit->$obj->group = "Datos del Cliente";
		}
		for($i=1;$i<=2;$i++){
			$obj="banco$i";
			$edit->$obj = new dropdownField("Banco $i", $obj);
			$edit->$obj->clause="where";
			$edit->$obj->option("","Ninguno");  
			$edit->$obj->options("SELECT cod_banc,nomb_banc FROM tban ORDER BY nomb_banc"); 
			$edit->$obj->operator="=";
			$edit->$obj->group = "Informaci&oacute;n financiera";
			
			$obj="cuenta$i";
			$edit->$obj = new inputField("Cuenta $i",$obj);
			$edit->$obj->size = 31;
			$edit->$obj->maxlength = 15;
			$edit->$obj->group = "Informaci&oacute;n financiera";
			//$edit->$obj->in="banco$i";
		}

		$edit->tipo = new dropdownField("Precio", 'tipo');
		$edit->tipo->option("1","Precio 1");
		$edit->tipo->option("2","Precio 2");
		$edit->tipo->option("3","Precio 3");
		$edit->tipo->option("4","Precio 4");
		$edit->tipo->style="width: 150px;";
		$edit->tipo->group = "Informaci&oacute;n financiera";

		$edit->status = new dropdownField("Status", 'status');
		$edit->status->option("","Ninguno");
		$edit->status->rule = "required";
		$edit->status->option("C","Conformar");
		$edit->status->option("N","No conformar");
		$edit->status->option("R","Retirado");
		$edit->status->group="Informaci&oacute;n financiera";
		$edit->status->style="width: 150px;";

		$edit->ing_mes =  new inputField("Ingreso Mensual", "ing_mes");
		$edit->ing_mes->css_class='inputnum';
		$edit->ing_mes->rule='numeric';
		$edit->ing_mes->size = 12;
		$edit->ing_mes->maxlength = 11;
		$edit->ing_mes->group = "Informaci&oacute;n financiera";

		$edit->empresa =  new inputField("Empresa", "empresa");
		$edit->empresa->size = 35;
		$edit->empresa->maxlength = 30;
		$edit->empresa->group = "Informaci&oacute;n financiera";
		
		$edit->cod_cli = new inputField("Enlace finaciero", "cod_cli");
		$edit->cod_cli->append($boton);
		$edit->cod_cli->group = "Informaci&oacute;n financiera";

		//$edit->buttons("modify", "save", "undo", "delete", "back");
		//$edit->buttons("modify","save","undo","back");
		$edit->build();
		
		//$data['content'] = $edit->output;
		//$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		//$data['title']   ='<h1>Modulo de Club de Compras</h1>';

		$script = '';

		$data['content'] = $edit->output;
		$data['script']  = $script;
		$this->load->view('jqgrid/ventanajq', $data);


		//$this->load->view('view_ventanas', $data);
	}
	
	function _pre_delete($do){
		return FALSE;
	}

	function _pre_insert($do){
		$do->set('modifi', date('Ymd'));
	}

	function _post_insert($do){
		$codigo=$do->get('cedula');
		$cod_tar=$do->get('cod_tar');
		logusu('club',"CLUB DE COMPRAS $cod_tar $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('cedula');
		$cod_tar=$do->get('cod_tar');
		logusu('club',"CLUB DE COMPRAS $cod_tar $codigo  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('cedula');
		$cod_tar=$do->get('cod_tar');
		logusu('club',"CLUB DE COMPRAS $cod_tar $codigo ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('cod_tar');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM club WHERE cod_tar='$codigo'");
		if ($check > 0){
			$mSQL_1=$this->db->query("SELECT cedula, nombres, apellidos FROM club WHERE cod_tar='$codigo'");
			$row = $mSQL_1->row();
			$nombre =$row->nombres;
			$apellido =$row->apellidos;
			$cedula =$row->cedula;
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el cliente $nombre $apellido  $cedula ");
			return FALSE;
		}else {
  		return TRUE;
		}
	}
}

?>
