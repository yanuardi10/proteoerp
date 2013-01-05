<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Sprv extends Controller {
	var $genesal = true;
	var $mModulo='SPRV';
	var $titp='Proveedores';
	var $tits='Proveedores';
	var $url ='compras/sprv/';

	function Sprv(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('sprv','id') ) {
			$this->db->simple_query('ALTER TABLE sprv DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE sprv ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE sprv ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
		$this->db->simple_query('ALTER TABLE sprv CHANGE COLUMN telefono telefono TEXT NULL DEFAULT NULL AFTER direc3');

		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){
		$consulrif=trim($this->datasis->traevalor('CONSULRIF'));

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname']);

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"edocta",   "img"=>"images/pdf_logo.gif",  "alt" => 'Formato PDF', "label"=>"Estado de Cuenta"));
		$grid->wbotonadd(array("id"=>"pagweb",   "img"=>"images/html_icon.gif",  "alt" => 'Formato PDF', "label"=>"Pagina Web"));
		$WestPanel = $grid->deploywestp();

		$adic = array(
		array("id"=>"fedita",  "title"=>"Agregar/Editar Registro")
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$funciones = '
		function consulrif1(campo){
			vrif=$("#"+campo).val();
			if(vrif.length==0){
				alert("Debe introducir primero un RIF");
			}else{
				vrif=vrif.toUpperCase();
				$("#"+campo).val(vrif);
				window.open("'.$consulrif.'"+"?p_rif="+vrif,"CONSULRIF","height=350,width=410");
			}
		}

		function iraurl(){
			vurl=$("#url").val();
			if(vrif.length==0){
				alert("Debe introducir primero un URL");
			}else{
				vurl=vurl.toLowerCase();
				window.open("http://"+vurl,"PROVEEDOR","height=600,width=800");
			}
		}
		';


		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		//$param['funciones']   = $funciones;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('SPRV', 'JQ');
		$param['otros']       = $this->datasis->otros('SPRV', 'JQ');
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
		$bodyscript = '		<script type="text/javascript">'."\n";

		$bodyscript .= '
		jQuery("#edocta").click( function(){
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				'.$this->datasis->jwinopen(site_url('reportes/ver/SPRMECU/SPRM/').'/\'+ret.proveed').';
			} else { $.prompt("<h1>Por favor Seleccione un Proveedor</h1>");}
		});
		';

		// Pagina Web
		$bodyscript .= '
		jQuery("#pagweb").click( function(){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret  = $("#newapi'.$grid0.'").getRowData(id);
				if ( ret.url.length > 10 )
					window.open(ret.url);
			} else {
				$.prompt("<h1>Por favor Seleccione un Proveedor</h1>");
			}
		});
		';


		$bodyscript .= '
		function sprvadd() {
			$.post("'.site_url('compras/sprv/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function sprvedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('compras/sprv/dataedit/modify').'/"+id, function(data){
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
			autoOpen: false, height: 470, width: 700, modal: true,
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
			"Cancelar": function() { $( this ).dialog( "close" ); },
			"SENIAT":   function() { consulrif("rifci"); },
			"URL":   function() { iraurl(); },
			},
			close: function() { allFields.val( "" ).removeClass( "ui-state-error" );}
		});';
		$bodyscript .= '});'."\n";

		$bodyscript .= "\n\t</script>\n";
		$bodyscript .= "";
		return $bodyscript;
	}


	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i       = 1;
		$linea   = 1;
		$editar  = "true";

		$mSQL = "SELECT grupo, CONCAT(grupo, ' ', gr_desc) descrip FROM grpr ORDER BY grupo ";
		$agrupo  = $this->datasis->llenajqselect($mSQL, false );

		$mSQL  = "SELECT cod_banc, CONCAT( nomb_banc) nombre FROM tban ORDER BY nomb_banc ";
		$banco = $this->datasis->llenajqselect($mSQL, true );

		$link   = site_url('ajax/buscacpla');

		$grid  = new $this->jqdatagrid;

		$grid->addField('proveed');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'));


		$grid->addField('grupo');
		$grid->label('Grupo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'select'",
			'editoptions'   => '{ value: '.$agrupo.',  style:"width:250px"}',
			'stype'         => "'text'",
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));

		$linea = $linea + 1;
		$grid->addField('rif');
		$grid->label('RIF');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 12 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));

		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));

		$linea = $linea + 1;
		$grid->addField('nomfis');
		$grid->label('Razon');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 80 }',
			'formoptions'   => '{ label:"Razon Social", rowpos:'.$linea.', colpos:2 }'
		));

		$linea = $linea + 1;
		$grid->addField('tipo');
		$grid->label('Persona');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'select'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{value: {"1":"Juridico Domiciliado","2":"Natural Residente","3":"Juridico no Domiciliado","4":"Natural no Residente", "5":"Excluido del LC", "0":"Inactivo" }, style:"width:180px" }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('contacto');
		$grid->label('Contacto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


/*
		$grid->addField('gr_desc');
		$grid->label('Gr_desc');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 25 }',
		));
*/

		$linea = $linea + 1;
		$grid->addField('tiva');
		$grid->label('Cont.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'select'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{value: {"N":"Nacional","I":"Internacional","O":"Otros" }, style:"width:120px" }',
			'formoptions'   => '{ label:"Contribuyente", rowpos:'.$linea.', colpos:1 }'
		));

		$grid->addField('direc1');
		$grid->label('Direccion 1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));

		$linea = $linea + 1;
		$grid->addField('reteiva');
		$grid->label('Retencion %');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('direc2');
		$grid->label('Direccion 2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('codigo');
		$grid->label('Cod. Prov.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:10, maxlength: 10 }',
			'formoptions'   => '{ label:"Cod. en el Prov.", rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('direc3');
		$grid->label('Direccion 3');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));

		$linea = $linea + 1;
		$grid->addField('telefono');
		$grid->label('Telefono');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'editrules'     => '{ required:false}',
			'edittype'      => "'textarea'",
			'editoptions'   => "{rows:2, cols:28}",
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('cuenta');
		$grid->label('Cuenta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{'.$grid->autocomplete($link, 'cuenta','cucucu','<div id=\"cucucu\"><b>"+ui.item.descrip+"</b></div>').'}',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));

/*

		$grid->addField('observa');
		$grid->label('Observaciones');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:2, cols:60}'",
		));

		$grid->addField('nit');
		$grid->label('Nit');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));

*/

		$linea = $linea + 1;
		$grid->addField('email');
		$grid->label('Email');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'textarea'",
			'editrules'     => '{ required:false}',
			'editoptions'   => "{rows:2, cols:28}",
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));

		$grid->addField('url');
		$grid->label('Url');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('banco1');
		$grid->label('Banco1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'select'",
			'editoptions'   => '{ value: '.$banco.',  style:"width:180px"}',
			'stype'         => "'text'",
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));

		$grid->addField('cuenta1');
		$grid->label('Cuenta1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 25 }',
			'formoptions'   => '{ label:"Nro. Cuente 1", rowpos:'.$linea.', colpos:2 }'
		));

		$linea = $linea + 1;
		$grid->addField('banco2');
		$grid->label('Banco2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'select'",
			'editoptions'   => '{ value: '.$banco.',  style:"width:180px"}',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));

		$grid->addField('cuenta2');
		$grid->label('Cuenta2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 25 }',
			'formoptions'   => '{ label:"Nro. Cuenta 2", rowpos:'.$linea.', colpos:2 }'
		));

/*
		$grid->addField('canticipo');
		$grid->label('Canticipo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 15 }',
		));
*/

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'editable'      => "'false'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->addField('modificado');
		$grid->label('Modificado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => "'false'",
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

		$grid->setOnSelectRow(' function(id){
			if (id){
				var ret = jQuery(gridId1).jqGrid(\'getRowData\',id);
				$(gridId1).jqGrid("setCaption", ret.nombre);
				$.ajax({
					url: "'.site_url($this->url).'/resumen/"+id,
					success: function(msg){
						$("#ladicional").html(msg);
					}
				});
			}
		}');

		$grid->setFormOptionsE('
			closeAfterEdit:true, mtype: "POST", width: 720, height:410, closeOnEscape: true, top: 50, left:20, recreateForm:true,
			afterSubmit: function(a,b){
				if (a.responseText.length > 0) $.prompt(a.responseText);
					return [true, a ];
			},
			beforeShowForm: function(frm){
					$(\'#proveed\').attr(\'readonly\',\'readonly\');
					$(\'<a href="#">SENIAT<span class="ui-icon ui-icon-disk"></span></a>\').click(function(){
						consulrif("rif");
					}).addClass("fm-button ui-state-default ui-corner-all fm-button-icon-left").prependTo("#Act_Buttons>td.EditButton");
				},
			afterShowForm: function(frm){$("select").selectmenu({style:"popup"});}'
		);

		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 720, height:410, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},
			beforeShowForm: function(frm){
					$(\'<a href="#">SENIAT<span class="ui-icon ui-icon-disk"></span></a>\').click(function(){
						consulrif("rif");
					}).addClass("fm-button ui-state-default ui-corner-all fm-button-icon-left").prependTo("#Act_Buttons>td.EditButton");
				},
			afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} '
		);

		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(true);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: sprvadd,\n\t\teditfunc: sprvedit");

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
		$mWHERE = $grid->geneTopWhere('sprv');

		$response   = $grid->getData('sprv', array(array()), array(), false, $mWHERE );
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
		$mcodp  = "proveed";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);

		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM sprv WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('sprv', $data);
					echo "Registro Agregado";

					logusu('SPRV',"Proveedor   INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM sprv WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				/*
				$this->db->query("DELETE FROM sprv WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE sprv SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("sprv", $data);
				logusu('SPRV',strtoupper($mcodp)." Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				*/
				echo "Proveedor ".$nuevo." ya existe, Proveedor no modificado";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('sprv', $data);
				logusu('SPRV',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "Proveedor Modificado";
			}

		} elseif($oper == 'del') {
			$codigo = $this->datasis->dameval("SELECT $mcodp FROM sprv WHERE id=$id");
			$check =  $this->datasis->dameval("SELECT count(*) FROM sprm WHERE cod_prv='$codigo'");
			$check += $this->datasis->dameval("SELECT count(*) FROM scst WHERE proveed='$codigo'");
			$check += $this->datasis->dameval("SELECT count(*) FROM gser WHERE proveed='$codigo'");
			$check += $this->datasis->dameval("SELECT count(*) FROM ords WHERE proveed='$codigo'");
			$check += $this->datasis->dameval("SELECT count(*) FROM bmov WHERE clipro='P' AND codcp='$codigo'");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM sprv WHERE proveed=".$this->db->escape($codigo));
				logusu('SPRV',"Proveedor ".$codigo." ELIMINADO");
				echo "Proveedor Eliminado";
			}
		};
	}


	//Resumen rapido
	function resumen() {
		$id = $this->uri->segment($this->uri->total_segments());
		$row = $this->datasis->damereg("SELECT proveed FROM sprv WHERE id=$id");
		$proveed  = $row['proveed'];
		$salida = '';

		$saldo  = 0;
		$saldo  = $this->datasis->dameval("SELECT sum(monto*IF(tipo_doc IN ('FC','ND','GI'),1,-1)) saldo FROM sprm WHERE cod_prv=".$this->db->escape($proveed));

		$salida  = '<table width="90%" cellspacing="0" align="center">';
		if ( $saldo > 0 )
			$salida .= "<tr style='background-color:#8BB381;font-size:14px;' align='right'><td><b>Saldo: </b> </td><td align='left'><b>".nformat($saldo)."</b></td></tr>\n";
		elseif ( $saldo < 0 )
			$salida .= "<tr style='background-color:#C11B17;font-size:14px;color:white;' align='right'><td><b>Saldo: </b> </td><td align='left'><b>".nformat($saldo)."</b></td></tr>\n";
		else
			$salida .= "<tr style='background-color:#8BB381;font-size:14px;' align='right'><td><b>Saldo: </b> </td><td align='left'><b>0.00</b></td></tr>\n";

		$salida .= "</table>\n";

		echo $salida;
	}





/*
class Sprv extends validaciones {

	function sprv(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('pi18n');
		$this->datasis->modulo_id(206,1);
		$this->genesal=true;
	}

	function index(){
		$this->datasis->modulo_id(206,1);
		if ( !$this->datasis->iscampo('sprv','canticipo') ) {
			$this->db->simple_query('ALTER TABLE sprv ADD COLUMN canticipo VARCHAR(15) AFTER nomfis ');
		}
		if($this->pi18n->pais=='COLOMBIA'){
			redirect('compras/sprvcol/filteredgrid');
		}else{
			$this->sprvextjs();
		}
	}

	function filteredgrid(){

		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Proveedores', 'sprv');

		$filter->proveed = new inputField('C&oacute;digo','proveed');
		$filter->proveed->size=13;
		$filter->proveed->group = "UNO";

		$filter->nombre = new inputField('Nombre', 'nombre');
		$filter->nombre->maxlength=30;
		$filter->nombre->group = "UNO";

		$filter->tipo = new dropdownField('Tipo', 'tipo');
		$filter->tipo->option('','Todos');
		$filter->tipo->options(array('1'=> 'Jur&iacute;dico Domiciliado','2'=>'Residente', '3'=>'Jur&iacute;dico No Domiciliado','4'=>'No Residente','5'=>'Excluido del Libro de Compras','0'=>'Inactivo'));
		$filter->tipo->style = 'width:200px';
		$filter->tipo->group = "UNO";

		$filter->rif = new inputField('R.I.F.', 'rif');
		$filter->rif->size=18;
		$filter->rif->maxlength=30;
		$filter->rif->group = "DOS";

		$filter->cuenta = new inputField('Cuenta contable', 'cuenta');
		$filter->cuenta->size=13;
		$filter->cuenta->like_side='after';
		$filter->cuenta->group = "DOS";

		$filter->telefono = new inputField('Telefono', 'telefono');
		$filter->telefono->size=18;
		$filter->telefono->like_side='after';
		$filter->telefono->group = "DOS";

		$filter->cuenta = new inputField('Cuenta contable', 'cuenta');
		$filter->cuenta->size=13;
		$filter->cuenta->like_side='after';
		$filter->cuenta->group = "DOS";

		$filter->buttons('reset','search');
		$filter->build("dataformfiltro");

		$uri = anchor('compras/sprv/dataedit/show/<#id#>','<#proveed#>');

		$grid = new DataGrid('Lista de Proveedores');
		$grid->order_by('proveed','asc');
		$grid->per_page = 50;

		$uri2  = anchor('compras/sprv/dataedit/show/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12px')));
		$uri2 .= img(array('src'=>'images/<siinulo><#tipo#>|N|S</siinulo>.gif','border'=>'0','alt'=>'Estado','title'=>'Estado'));


		$grid->column('Acciones',$uri2,'align=\'center\'');
		$grid->column_orderby('C&oacute;digo',$uri,'codigo');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('R.I.F.','rif','rif');
		$grid->column_orderby('Telefonos','telefono','telefono');
		$grid->column_orderby('Contacto','contacto','contacto');
		$grid->column_orderby('% Ret.','reteiva','reteiva','align=\'right\'');
		$grid->column_orderby('Cuenta','cuenta','cuenta','align=\'right\'');

		$grid->add('compras/sprv/dataedit/create','Agregar un proveedor');
		$grid->build('datagridST');


		//************ SUPER TABLE *************
		$extras = '
		<script type="text/javascript">
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
		</script>
		';
				$style ='
		<style type="text/css">
		.fakeContainer { // The parent container
			margin: 5px;
			padding: 0px;
			border: none;
			width: 740px;     // Required to set
			height: 320px;    // Required to set
			overflow: hidden; // Required to set
		}
		</style>
		';
		//****************************************


		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;

		$data['title']   = '<h1>Proveedores</h1>';

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');

		$data['extras']  = $extras;

		$data['head']    = script('jquery.js');
		$data["head"]   .= script('superTables.js');
		$data['head']   .= $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
*/

	// **************************************
	//     DATAEDIT
	//
	// **************************************
	function dataedit(){
		$this->rapyd->load('dataedit');

		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'  =>'Nombre',
			'contacto'=>'Contacto',
			'nomfis'  =>'Nom. Fiscal'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cliente','nomfis'=>'nomfis'),
			'titulo'  =>'Buscar Cliente');

		$qformato=$this->qformato=$this->datasis->formato_cpla();

		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
			);

		$bsclid =$this->datasis->modbus($mSCLId);
		$bcpla  =$this->datasis->modbus($mCPLA);


		$smenu['link']=barra_menu('131');
		$consulrif=$this->datasis->traevalor('CONSULRIF');
		$link=site_url('compras/sprv/uproveed');
		$script ='
			$(function() {
				$("#tr_gr_desc").hide();
				$("#grupo").change(function(){grupo();}).change();
				$(".inputnum").numeric(".");
				$("#banco1").change(function () { acuenta(); }).change();
				$("#banco2").change(function () { acuenta(); }).change();

				$("#rif").focusout(function(){
					rif=$(this).val().toUpperCase();
					$(this).val(rif);
					if(!chrif(rif)){
						apprise("<b>Al parecer el RIF colocado no es correcto, por favor verifique con el SENIAT.</b>");
					}else{
						$.ajax({
							type: "POST",
							url: "'.site_url('ajax/traerif').'",
							dataType: "json",
							data: {rifci: rif},
							success: function(data){
								if(data.error==0){
									if($("#nombre").val()==""){
										$("#nombre").val(data.nombre);
									}
									if($("#nomfis").val()==""){
										$("#nomfis").val(data.nombre);
									}
								}
							}
						});
					}
				});
			});

			function chrif(rif){
				rif.toUpperCase();
				var patt=/[EJPGV][0-9]{9} */g;
				if(patt.test(rif)){
					var factor= new Array(4,3,2,7,6,5,4,3,2);
					var v=0;
					if(rif[0]=="V"){
						v=1;
					}else if(rif[0]=="E"){
						v=2;
					}else if(rif[0]=="J"){
						v=3;
					}else if(rif[0]=="P"){
						v=4;
					}else if(rif[0]=="G"){
						v=5;
					}
					acum=v*factor[0];
					for(i=1;i<9;i++){
						acum=acum+parseInt(rif[i])*factor[i];
					}
					acum=11-acum%11;
					if(acum>=10 || acum<=0){
						acum=0;
					}
					return (acum==parseInt(rif[9]));
				}else{
					return true;
				}
			}


			function grupo(){
				t=$("#grupo").val();
				a=$("#grupo :selected").text();
				$("#gr_desc").val(a);
			}
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
			function anomfis(){
					vtiva=$("#tiva").val();
					if(vtiva=="C" || vtiva=="E" || vtiva=="R"){
						$("#tr_nomfis").show();
						$("#tr_riff").show();
					}else{
						$("#nomfis").val("");
						$("#rif").val("");
						$("#tr_nomfis").hide();
						$("#tr_rif").hide();
					}
			}

			function consulrif(){
					vrif=$("#rif").val();
					if(vrif.length==0){
						alert("Debe introducir primero un RIF");
					}else{
						vrif=vrif.toUpperCase();
						$("#rif").val(vrif);
						window.open("'.$consulrif.'"+"?p_rif="+vrif,"CONSULRIF","height=350,width=410");
					}
			}
			function ultimo(){
				$.ajax({
					url: "'.$link.'",
					success: function(msg){
					alert( "El ultimo codigo ingresado fue: " + msg );
					}
				});
			}

			function iraurl(){
				vurl=$("#url").val();
				if(vurl.length==0){
					alert("Debe introducir primero un URL");
				}else{
					vurl=vurl.toLowerCase();
					window.open(vurl);
				}
			}

			';

		$edit = new DataEdit('Proveedores', 'sprv');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');
		$edit->back_url = site_url('compras/sprv/filteredgrid');

		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$lproveed='<a href="javascript:ultimo();" title="Ultimo codigo ingresado" onclick="">Ultimo</a>';
		$edit->proveed  = new inputField('C&oacute;digo', 'proveed');
		$edit->proveed->rule = 'trim|required|callback_chexiste';
		$edit->proveed->mode = 'autohide';
		$edit->proveed->size = 6;
		$edit->proveed->maxlength =5;
		$edit->proveed->append($lproveed);
		//$edit->proveed->group = 'Datos del Proveedor';

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rule = 'trim|strtoupper|required';
		$edit->nombre->size = 35;
		$edit->nombre->maxlength =40;

		//$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick="" style="color:red;font-size:9px;border:none;">SENIAT</a>';
		$edit->rif =  new inputField('RIF', 'rif');
		$edit->rif->rule = "trim|strtoupper|required|callback_chci";
		//$edit->rif->append($lriffis);
		$edit->rif->maxlength=13;
		$edit->rif->size =12;

		$edit->contacto = new inputField("Contacto", "contacto");
		$edit->contacto->size =35;
		$edit->contacto->rule ="trim";
		$edit->contacto->maxlength =40;
		//$edit->contacto->group = "Datos del Proveedor";

		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->option("","Seleccionar");
		$edit->grupo->options("SELECT grupo,gr_desc,grupo FROM grpr ORDER BY gr_desc");
		$edit->grupo->style = "width:190px";
		//$edit->grupo->rule = "required";
		$edit->grupo->group = "Datos del Proveedor";
		$edit->gr_desc = new inputField("gr_desc", "gr_desc");

		$edit->tipo = new dropdownField("Persona", "tipo");
		$edit->tipo->option("","Seleccionar");
		$edit->tipo->options(array("1"=> "Jur&iacute;dico Domiciliado","2"=>"Residente", "3"=>"Jur&iacute;dico No Domiciliado","4"=>"No Residente","5"=>"Excluido del Libro de Compras","0"=>"Inactivo"));
		$edit->tipo->style = "width:190px";
		$edit->tipo->rule = "required";
		$edit->tipo->group = "Datos del Proveedor";

		$edit->tiva  = new dropdownField("Origen", "tiva");
		$edit->tiva->option("N","Nacional");
		$edit->tiva->options(array("N"=>"Nacional","I"=>"Internacional","O"=>"Otros"));
		$edit->tiva->style='width:190px;';

		$edit->direc1 = new inputField("Direcci&oacute;n ",'direc1');
		$edit->direc1->size =34;
		$edit->direc1->rule ="trim";
		$edit->direc1->maxlength =40;

		$edit->direc2 = new inputField(" ",'direc2');
		$edit->direc2->size =34;
		$edit->direc2->rule ="trim";
		$edit->direc2->maxlength =40;

		$edit->direc3 = new inputField(" ",'direc3');
		$edit->direc3->size =34;
		$edit->direc3->rule ="trim";
		$edit->direc3->maxlength =40;

		$edit->telefono = new textareaField("Telefono", "telefono");
		$edit->telefono->rule = "trim";
		$edit->telefono->cols = 27;
		$edit->telefono->rows =  2;
		$edit->telefono->maxlength =200;
		//$edit->nomfis->style = 'width:170;';



		$edit->email  = new inputField("Email", "email");
		$edit->email->rule = "trim|valid_email";
		$edit->email->size =29;
		$edit->email->maxlength =30;
		//$edit->email->group = "Datos del Proveedor";

		$edit->url = new inputField("URL", "url");
		$edit->url->group = "Datos del Proveedor";
		$edit->url->rule = "trim";
		$edit->url->size =25;
		$edit->url->maxlength =30;

		$atts = array(
				'width'     =>'800',
				'height'    =>'600',
				'scrollbars'=>'yes',
				'status'    =>'yes',
				'resizable' =>'yes',
				'screenx'   =>'5',
				'screeny'   =>'5');

		$lcli=anchor_popup("/ventas/scli/dataedit/create",image('list_plus.png','Agregar',array("border"=>"0")),$atts);
		//$AddUnidad='<a href="javascript:add_unidad();" title="Haz clic para Agregar una unidad nueva">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';

		$edit->observa  = new inputField("Observaci&oacute;n", "observa");
		$edit->observa->group = "Datos del Proveedor";
		$edit->observa->rule = "trim";
		$edit->observa->size = 41;

		$edit->banco1 = new dropdownField("Cuenta en bco. (1)", "banco1");
		$edit->banco1->clause="where";
		$edit->banco1->option("","Ninguno");
		$edit->banco1->options("SELECT cod_banc,nomb_banc FROM tban ORDER BY nomb_banc");
		$edit->banco1->operator="=";
		$edit->banco1->group = "Cuentas Bancarias";
		$edit->banco1->style='width:140px;';

		$edit->cuenta1 = new inputField("&nbsp;&nbsp;N&uacute;mero (1)","cuenta1");
		$edit->cuenta1->size = 21;
		$edit->cuenta1->rule = "trim";
		$edit->cuenta1->maxlength = 23;
		$edit->cuenta1->group = "Cuentas Bancarias";
		//$edit->cuenta1->in="banco$i";

		$edit->banco2 = new dropdownField("Cuenta en bco. (2)", 'banco2');
		$edit->banco2->clause="where";
		$edit->banco2->option("","Ninguno");
		$edit->banco2->options("SELECT cod_banc,nomb_banc FROM tban ORDER BY nomb_banc");
		$edit->banco2->group = "Cuentas Bancarias";
		$edit->banco2->style='width:140px;';

		$edit->cuenta2 = new inputField("&nbsp;&nbsp;N&uacute;mero (2)",'cuenta2');
		$edit->cuenta2->size = 21;
		$edit->cuenta2->rule = "trim";
		$edit->cuenta2->maxlength = 23;
		$edit->cuenta2->group = "Cuentas Bancarias";

		$edit->cliente  = new inputField("Como Cliente", "cliente");
		$edit->cliente->size =7;
		$edit->cliente->rule ="trim";
		$edit->cliente->readonly=true;
		$edit->cliente->append($bsclid);
		//$edit->cliente->append($lcli);
		//$edit->cliente->group = "Datos del Proveedor";

		$edit->codigo  = new inputField("Cod. en Prov", "codigo");
		$edit->codigo->size =15;
		$edit->codigo->rule ="trim";

		$edit->nomfis = new textareaField('Razon Social', 'nomfis');
		$edit->nomfis->rule = 'trim';
		$edit->nomfis->cols = 33;
		$edit->nomfis->rows =  2;
		$edit->nomfis->maxlength =200;
		$edit->nomfis->style = 'width:170;';


		//$lcuent=anchor_popup('/contabilidad/cpla/dataedit/create','Agregar Cuenta Contable',$atts);
		$edit->cuenta = new inputField('Contable', 'cuenta');
		$edit->cuenta->rule='trim|callback_chcuentac';
		$edit->cuenta->size =15;
		$edit->cuenta->append($bcpla);
		//$edit->cuenta->append($lcuent);

		$edit->reteiva  = new inputField('Retencion','reteiva');
		$edit->reteiva->size = 6;
		$edit->reteiva->css_class='inputnum';
		$edit->reteiva->append("%");

		$edit->buttons('modify','save','undo','delete','add','back');

		if($this->genesal){
			$edit->build();
			$conten['form']  =&  $edit;
			$data['content'] = $this->load->view('view_sprv', $conten);


			//$smenu['link']=barra_menu('230');
			//$data['content'] = $edit->output;
			//$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
			//$data['title'] = heading('Proveedores');

			//$data['head']  = script('jquery.js');
			//$data['head'] .= script('plugins/jquery.numeric.pack.js');
			//$data['head'] .= script('plugins/jquery.floatnumber.js');
			//$data['head'] .= $this->rapyd->get_head();
			//$this->load->view('view_ventanas', $data);
		}else{
			$edit->on_save_redirect=false;
			$edit->build();

			if($edit->on_success()){
				$rt= array(true, 'Proveedor Guardado');
			}elseif($edit->on_error()){
				$rt= array(false,html_entity_decode(preg_replace('/<[^>]*>/', '', $edit->error_string)));
			}
			return $rt;
		}
	}

	function _pre_del($do) {
		$codigo=$do->get('proveed');
		$check =  $this->datasis->dameval("SELECT count(*) FROM sprm WHERE cod_prv='$codigo'");
		$check += $this->datasis->dameval("SELECT count(*) FROM scst WHERE proveed='$codigo'");
		$check += $this->datasis->dameval("SELECT count(*) FROM gser WHERE proveed='$codigo'");
		$check += $this->datasis->dameval("SELECT count(*) FROM ords WHERE proveed='$codigo'");
		$check += $this->datasis->dameval("SELECT count(*) FROM bmov WHERE clipro='P' AND codcp='$codigo'");
		if ($check > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}
		return True;
	}

	function _post_insert($do){
		$codigo=$do->get('proveed');
		$nombre=$do->get('nombre');
		logusu('sprv',"PROVEEDOR $codigo NOMBRE $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('proveed');
		$nombre=$do->get('nombre');
		logusu('sprv',"PROVEEDOR $codigo NOMBRE $nombre MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('proveed');
		$nombre=$do->get('nombre');
		logusu('sprv',"PROVEEDOR $codigo NOMBRE $nombre ELIMINADO");
	}

	function chexiste(){
		$codigo=$this->input->post('proveed');
		$rif=$this->input->post('rif');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE proveed='$codigo'");
		if ($check > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM sprv WHERE proveed='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el proveedor $nombre");
			return FALSE;
		}elseif(strlen($rif)>0){
			$check=$this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE rif='$rif'");
			if ($check > 0){
				$nombre=$this->datasis->dameval("SELECT nombre FROM sprv WHERE rif='$rif'");
				$this->validation->set_message('chexiste',"El rif $rif ya existe para el proveedor $nombre");
				return FALSE;
			}else {
				return TRUE;
			}
		}else{
			return TRUE;
		}
	}

	function _pre_insert($do){
		$rif=$do->get('rif');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE rif='$rif'");
		if($check > 0){
			//$nombre=$this->datasis->dameval("SELECT nombre FROM sprv WHERE rif='$rif'");
			$do->error_message_ar['pre_insert'] = $do->error_message_ar['insert']='';
			return FALSE;
		}else {
		return TRUE;
		}
	}

	function update(){
		$mSQL=$this->db->query('UPDATE sprv SET reteiva=75 WHERE reteiva<>100');
	}

	function uproveed(){
		$consulproveed=$this->datasis->dameval('SELECT MAX(proveed) FROM sprv');
		echo $consulproveed;
	}

	function consulta(){
		$this->load->helper('openflash');
		$this->rapyd->load("datagrid");
		$fields = $this->db->field_data('sprv');
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

		$mCodigo = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=".$claves['id']."");

		$grid = new DataGrid('Ventas por Mes');
		$grid->db->_protect_identifiers=false;
		$grid->db->select( array('a.tipo_doc','a.fecha', 'a.numero', 'a.monto', 'a.abonos', 'a.monto-a.abonos saldo' ) );
		$grid->db->from('sprm a');
		$grid->db->where('a.cod_prv', $mCodigo );
		$grid->db->where('a.monto <> a.abonos');
		$grid->db->where('a.tipo_doc IN ("FC","ND","GI") ' );
		$grid->db->orderby('a.fecha');

		$grid->column("Fecha"   ,"fecha" );
		$grid->column("Tipo", "tipo_doc",'align="CENTER"');
		$grid->column("Numero",  "numero",'align="LEFT"');
		$grid->column("Monto",    "<nformat><#monto#></nformat>",  'align="RIGHT"');
		$grid->column("Abonos",  "<nformat><#abonos#></nformat>",'align="RIGHT"');
		$grid->column("Saldo",  "<nformat><#saldo#></nformat>",'align="RIGHT"');
		$grid->build();

		$nombre = $this->datasis->dameval("SELECT nombre FROM sprv WHERE id=".$claves['id']." ");

		$data['content'] = $grid->output;
		$data["head"]     = script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = '<h1>Consulta de Proveedor</h1>';
		$data["subtitle"] = "
			<div align='center' style='border: 2px outset #EFEFEF;background: #EFEFEF;font-size:18px'>
				<a href='javascript:javascript:history.go(-1)'>(".$mCodigo.") ".$nombre."</a>
			</div>";
		$this->load->view('view_ventanas', $data);
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"nombre","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'sprv');

		$this->db->_protect_identifiers=false;
		$this->db->select('sprv.*, CONCAT("(",sprv.grupo,") ",grpr.gr_desc) nomgrup');
		$this->db->from('sprv');
		$this->db->join('grpr', 'sprv.grupo=grpr.grupo');

		if (strlen($where)>1){ $this->db->where($where);}

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$mSQL = '';
		if ( $filters ) $mSQL = $this->db->last_query();
		$results = $this->db->count_all('sprv');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data " ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear() {
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);

		$_POST = $this->datasis->extultireg($data);
		$_POST['btn_submit']= 'Guardar';
		$this->genesal=false;
		$rt=$this->dataedit();

		if ( $rt[0] ) {
			echo "{ success: true, message: '$rt[1]', data: [{id: '0'}]}";
		} else {
			echo "{ success: false, message: '$rt[1]', data: [{id: '0'}]}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);


		$campos = $data['data'];
		$codigo = $campos['proveed'];
		unset($campos['nomgrup']);
		unset($campos['proveed']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("sprv", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('sprv',"PROVEEDOR ".$data['data']['proveed']." MODIFICADO");
		echo "{ success: true, message: 'Proveedor Modificado ', data: [] }";

	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$proveed = $data['data']['proveed'];

		// VERIFICAR SI PUEDE
		$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sprm WHERE cod_prv='$proveed'");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM scst WHERE proveed='$proveed'");
		$check += $this->datasis->dameval("SELECT COUNT(*) FROM gser WHERE proveed='$proveed'");
		$check += $this->datasis->dameval("SELECT count(*) FROM ordc WHERE proveed='$proveed'");
		$check += $this->datasis->dameval("SELECT count(*) FROM bmov WHERE clipro='P' AND codcp='$proveed'");
		$check += $this->datasis->dameval("SELECT count(*) FROM ords WHERE proveed='$proveed'");
		//$check += $this->datasis->dameval("SELECT count(*) FROM obco WHERE proveed='$proveed'");

		if ($check > 0){
			echo "{ success: false, message: 'Proveedor con Movimiento no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM sprv WHERE proveed='$proveed'");
			logusu('sprv',"PROVEEDOR $proveed ELIMINADO");
			echo "{ success: true, message: 'Proveedor Eliminado'}";
		}
	}

/*
	//****************************************************************
	//
	//
	//
	//****************************************************************
	function sprvextjs(){

		$encabeza='PROVEEDORES';
		$listados= $this->datasis->listados('sprv');
		$otros=$this->datasis->otros('sprv', 'sprv');

		$mSQL = "SELECT cod_banc, CONCAT(cod_banc,' ',nomb_banc) nombre FROM tban ORDER BY cod_banc ";
		$bancos = $this->datasis->llenacombo($mSQL);

		$mSQL = "SELECT grupo, CONCAT(grupo,' ',gr_desc) descrip FROM grpr ORDER BY grupo ";
		$grupo = $this->datasis->llenacombo($mSQL);

		$consulrif=$this->datasis->traevalor('CONSULRIF');

		$urlajax = 'compras/sprv/';
		$variables = "var mcliente = '';var mcuenta  = '';";
		$funciones = '';
		$valida = "
		{ type: 'length', field: 'proveed',  min:  1 },
		{ type: 'length', field: 'nombre',   min:  3 }
		";

		$columnas = "
		{ header: 'Codigo',        width:  60, sortable: true, dataIndex: 'proveed',   field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Tipo',          width:  60, sortable: true, dataIndex: 'tipo',      field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Nombre',        width: 220, sortable: true, dataIndex: 'nombre',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'R.I.F.',        width:  80, sortable: true, dataIndex: 'rif',       field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Grupo',         width:  50, sortable: true, dataIndex: 'grupo',     field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Telefono',      width:  90, sortable: true, dataIndex: 'telefono',  field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Contacto',      width: 120, sortable: true, dataIndex: 'contacto',  field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Cliente',       width:  60, sortable: true, dataIndex: 'cliente',   field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Ret%',          width:  50, sortable: true, dataIndex: 'reteiva',   field:  { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('00.00') },
		{ header: 'Origen',        width:  40, sortable: true, dataIndex: 'tiva',      field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Direccion',     width: 150, sortable: true, dataIndex: 'direc1',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Email',         width: 150, sortable: true, dataIndex: 'email',     field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Url',           width: 150, sortable: true, dataIndex: 'url',       field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Nombre Fiscal', width: 220, sortable: true, dataIndex: 'nomfis',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Cuenta',        width:  80, sortable: true, dataIndex: 'cuenta',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Cta.Anticipo',  width:  80, sortable: true, dataIndex: 'canticipo', field:  { type: 'textfield' }, filter: { type: 'string'  }},
		";

		$campos = "'id','proveed','tipo','nombre','rif','grupo','nomgrup','telefono','contacto', 'direc1', 'direc2', 'direc3','cliente', 'observa', 'nit', 'codigo','tiva', 'email', 'url', 'banco1', 'cuenta1', 'banco2', 'cuenta2', 'nomfis', 'reteiva', 'cuenta', 'canticipo' ";

		$stores = "
		var scliStore = new Ext.data.Store({
			fields: [ 'item', 'valor'],
			autoLoad: false, autoSync: false, pageSize: 30, pruneModifiedRecords: true, totalProperty: 'results',
			proxy: {
				type: 'ajax',
				url : urlApp + 'ventas/scli/sclibusca',
				extraParams: {  'cliente': mcliente, 'origen': 'store' },
				reader: { type: 'json', totalProperty: 'results', root: 'data' }
			},
			method: 'POST'
		});

		var cplaStore = new Ext.data.Store({
			fields: [ 'item', 'valor'],
			autoLoad: false, autoSync: false, pageSize: 30, pruneModifiedRecords: true, totalProperty: 'results',
			proxy: {
				type: 'ajax',
				url : urlApp + 'contabilidad/cpla/cplabusca',
				extraParams: {  'cuenta': mcuenta, 'origen': 'store' },
				reader: { type: 'json', totalProperty: 'results', root: 'data' }
			},
			method: 'POST'
		});";

		$camposforma = "
							{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield', fieldLabel: 'Codigo',   labelWidth:60, name: 'proveed',  allowBlank: false, columnWidth : 0.20, id: 'proveed', maxLength: 5, enforceMaxLength: true  },
									{ xtype: 'textfield', fieldLabel: 'RIF',      labelWidth:40, name: 'rif',      allowBlank: false, columnWidth : 0.25, regex: /((^[VEJG][0-9])|(^[P][A-Z0-9]))/, regexText: 'Debe colocar una letra JVGE y 10 digitos' },
									{ xtype: 'combo',     fieldLabel: 'Grupo',    labelWidth:80, name: 'grupo',    store: [".$grupo."], columnWidth: 0.50 },
									{ xtype: 'textfield', fieldLabel: 'Nombre',   labelWidth:60, name: 'nombre',   allowBlank: false, columnWidth : 0.60 },
									{ xtype: 'combo',     fieldLabel: 'Origen',   labelWidth:65, name: 'tiva',     store: [['N','Nacional'],['I','Internacional'],['O','Otro']], columnWidth: 0.35 },
									{ xtype: 'textfield', fieldLabel: 'Contacto', labelWidth:60, name: 'contacto', allowBlank: true, columnWidth : 0.60 },
									{ xtype: 'combo',     fieldLabel: 'Tipo',     labelWidth:65, name: 'tipo',     store: [['1','1-Jur. Domiciliado'],['2','2-Residente'],['3','3-J. no Domiciliado'],['4','4-No Residente'], ['5','5-Excluido de Libros'], ['0','0-Inactivo']], columnWidth: 0.35 }
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield',   fieldLabel: 'Nombre Fiscal', labelWidth:120, name: 'nomfis', allowBlank: true, columnWidth : 0.90 },
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield',   fieldLabel: 'Direccion', labelWidth:60, name: 'direc1',   allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'numberfield', fieldLabel: 'Retencion', labelWidth:80, name: 'reteiva',  hideTrigger: true, fieldStyle: 'text-align: right', width:130,renderer : Ext.util.Format.numberRenderer('00.00') },
									{ xtype: 'textfield',   fieldLabel: '.',         labelWidth:60, name: 'direc2',   allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'textfield',   fieldLabel: '.',         labelWidth:60, name: 'direc3',   allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'combo',       fieldLabel: 'Banco 1',   labelWidth:60, name: 'banco1',   store: [".$bancos."], columnWidth: 0.45 },
									{ xtype: 'textfield',   fieldLabel: 'Telefono',  labelWidth:60, name: 'telefono', allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'textfield',   fieldLabel: 'Cuenta 1',  labelWidth:60, name: 'cuenta1',  allowBlank: true, columnWidth : 0.45 },
									{ xtype: 'textfield',   fieldLabel: 'Email',     labelWidth:60, name: 'email',    allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'combo',       fieldLabel: 'Banco 2',   labelWidth:60, name: 'banco2',   store: [".$bancos."], columnWidth: 0.45 },
									{ xtype: 'textfield',   fieldLabel: 'Url',       labelWidth:60, name: 'url',      allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'textfield',   fieldLabel: 'Cuenta 2',  labelWidth:60, name: 'cuenta2',  allowBlank: true, columnWidth : 0.45 },
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{
										xtype: 'combo',
										fieldLabel: 'Codigo como Cliente',
										labelWidth:140,
										name: 'cliente',
										id:   'cliente',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,
										valueField: 'item',
										displayField: 'valor',
										store: scliStore,
										columnWidth: 0.80
									},{
										xtype: 'combo',
										fieldLabel: 'Cuenta Contable',
										labelWidth:140,
										name: 'cuenta',
										id:   'cuenta',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,
										valueField: 'item',
										displayField: 'valor',
										store: cplaStore,
										columnWidth: 0.80
									}
								]
							}
		";

		$titulow = 'Proveedores';

		$dockedItems = "
				{ itemId: 'seniat', text: 'SENIAT',   scope: this, handler: this.onSeniat },
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 650,
				height: 470,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;

						if (registro) {
							mcliente = registro.data.cliente;
							mcuenta  = registro.data.cuenta;
							cplaStore.proxy.extraParams.cuenta   = mcuenta ;
							scliStore.proxy.extraParams.cliente = mcliente ;
							cplaStore.load({ params: { 'cliente': registro.data.cliente, 'origen': 'beforeform' } });
							scliStore.load({ params: { 'cuenta':  registro.data.cuenta,  'origen': 'beforeform' } });
							form.loadRecord(registro);
							form.findField('proveed').setReadOnly(true);
						} else {
							form.findField('proveed').setReadOnly(false);
							mcliente = '';
							mcuenta  = '';
						}
					}
				}
";

		$filtros = "var filters = { ftype: 'filters', encode: 'json', local: false }; ";

		$winmethod = "
				onSeniat: function(){
					var form = this.getForm();
					var vrif = form.findField('rif').value;
					if(vrif.length==0){
						alert('Debe introducir primero un RIF');
					}else{
						vrif = vrif.toUpperCase();
						window.open(\"".$consulrif."\"+\"?p_rif=\"+vrif,\"CONSULRIF\",\"height=350,width=410\");
					}
				}
";

		$features = "features: [{ ftype: 'grouping', groupHeaderTpl: '{name} ' }, filters],";


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
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['features']    = $features;
		$data['winwidget']   = $winwidget;
		$data['filtros']     = $filtros;
		$data['winmethod']   = $winmethod;

		$data['title']  = heading('Proveedores');
		$this->load->view('extjs/extjsven',$data);
	}

	function sprvbusca() {
		$start    = isset($_REQUEST['start'])   ? $_REQUEST['start']  :  0;
		$limit    = isset($_REQUEST['limit'])   ? $_REQUEST['limit']  : 25;
		$proveed  = isset($_REQUEST['proveed']) ? $_REQUEST['proveed']: '';
		$semilla  = isset($_REQUEST['query'])   ? $_REQUEST['query']  : '';

		$semilla = trim($semilla);

		$mSQL = "SELECT proveed item, CONCAT(proveed, ' ', nombre) valor FROM sprv WHERE tipo<>'0' ";
		if ( strlen($semilla)>0 ){
			$mSQL .= " AND ( proveed LIKE '$semilla%' OR nombre LIKE '%$semilla%' OR  rif LIKE '%$semilla%') ";
		} else {
			if ( strlen($proveed)>0 ) $mSQL .= " AND proveed = '$proveed' ";
		}
		$mSQL .= "ORDER BY nombre ";
		$results = $this->db->count_all('scli');

		if ( empty($mSQL)) {
			echo '{success:true, message:"mSQL vacio, Loaded data", results: 0, data:'.json_encode(array()).'}';
		} else {
			$mSQL .= " limit $start, $limit ";
			$query = $this->db->query($mSQL);
			$arr = array();
			foreach ($query->result_array() as $row)
			{
				$meco = array();
				foreach( $row as $idd=>$campo ) {
					$meco[$idd] = utf8_encode($campo);
				}
				$arr[] = $meco;
			}
			echo '{success:true, message:"proveedores", results:'. $results.', data:'.json_encode($arr).'}';
		}
	}

*/
	function instalar(){

		$campos=$this->db->list_fields('sprv');
		if (!in_array('id',$campos)){
			$this->db->simple_query('ALTER TABLE `sprv` DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE `sprv` ADD id INT AUTO_INCREMENT PRIMARY KEY');
		}

		if (!in_array('copre'   ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD copre VARCHAR(11) DEFAULT NULL NULL AFTER cuenta');
		if (!in_array('ocompra' ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD ocompra CHAR(1) DEFAULT NULL NULL AFTER copre');
		if (!in_array('dcredito',$campos)) $this->db->simple_query('ALTER TABLE sprv ADD dcredito DECIMAL(3,0) DEFAULT "0" NULL AFTER ocompra');
		if (!in_array('despacho',$campos)) $this->db->simple_query('ALTER TABLE sprv ADD despacho DECIMAL(3,0) DEFAULT NULL NULL AFTER dcredito');
		if (!in_array('visita'  ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD visita VARCHAR(9) DEFAULT NULL NULL AFTER despacho');
		if (!in_array('cate'    ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD cate VARCHAR(20) NULL AFTER visita');
		if (!in_array('reteiva' ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD reteiva DECIMAL(7,2) DEFAULT "0.00" NULL AFTER cate');
		if (!in_array('ncorto'  ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD ncorto VARCHAR(20) DEFAULT NULL NULL AFTER nombre');

		$this->db->simple_query('ALTER TABLE sprv CHANGE direc1 direc1 VARCHAR(105) DEFAULT NULL NULL');
		$this->db->simple_query('ALTER TABLE sprv CHANGE direc2 direc2 VARCHAR(105) DEFAULT NULL NULL');
		$this->db->simple_query('ALTER TABLE sprv CHANGE direc3 direc3 VARCHAR(105) DEFAULT NULL NULL');
		$this->db->simple_query('ALTER TABLE sprv CHANGE nombre nombre VARCHAR(60) DEFAULT NULL NULL');
		$this->db->simple_query('ALTER TABLE sprv CHANGE nomfis nomfis VARCHAR(200) DEFAULT NULL NULL');
	}

}
?>
