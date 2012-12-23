<?php 
class Caja extends Controller {
	var $mModulo = 'CAJA';
	var $titp    = 'Configuracio Fisica de la CAJA';
	var $tits    = 'Configuracio Fisica de la CAJA';
	var $url     = 'ventas/caja/';

	function Caja(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'CAJA', $ventana=0 );
	}


	function index(){
		if ( !$this->datasis->iscampo('caja','id') ) {
			$this->db->simple_query('ALTER TABLE caja DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE caja ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE caja ADD UNIQUE INDEX caja (caja)');
		}
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
		$param['listados']    = $this->datasis->listados('CAJA', 'JQ');
		$param['otros']       = $this->datasis->otros('CAJA', 'JQ');
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
		function cajaadd() {
			$.post("'.site_url('ventas/caja/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function cajaedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('ventas/caja/dataedit/modify').'/"+id, function(data){
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
			autoOpen: false, height: 440, width: 700, modal: true,
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
							'.$this->datasis->jwinopen(site_url('formatos/ver/CAJA').'/\'+res.id+\'/id\'').';
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

		$grid->addField('caja');
		$grid->label('Caja');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:2, maxlength: 2 }',
		));


		$grid->addField('ubica');
		$grid->label('Ubica');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
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


		$grid->addField('factura');
		$grid->label('Factura');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:6, maxlength: 6 }',
		));


		$grid->addField('egreso');
		$grid->label('Egreso');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:6, maxlength: 6 }',
		));


		$grid->addField('ingreso');
		$grid->label('Ingreso');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:6, maxlength: 6 }',
		));


		$grid->addField('almacen');
		$grid->label('Almacen');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('impre');
		$grid->label('Impre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('ibaud');
		$grid->label('Ibaud');
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


		$grid->addField('iparid');
		$grid->label('Iparid');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('ilong');
		$grid->label('Ilong');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('istop');
		$grid->label('Istop');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('lector');
		$grid->label('Lector');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('lbaud');
		$grid->label('Lbaud');
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


		$grid->addField('lparid');
		$grid->label('Lparid');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('llong');
		$grid->label('Llong');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('lstop');
		$grid->label('Lstop');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('gaveta');
		$grid->label('Gaveta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('gbaud');
		$grid->label('Gbaud');
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


		$grid->addField('gparid');
		$grid->label('Gparid');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('glong');
		$grid->label('Glong');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('gstop');
		$grid->label('Gstop');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('cont1');
		$grid->label('Cont1');
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


		$grid->addField('cont2');
		$grid->label('Cont2');
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


		$grid->addField('cont3');
		$grid->label('Cont3');
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


		$grid->addField('cont4');
		$grid->label('Cont4');
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


		$grid->addField('cont5');
		$grid->label('Cont5');
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


		$grid->addField('display');
		$grid->label('Display');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('dbaud');
		$grid->label('Dbaud');
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


		$grid->addField('dparid');
		$grid->label('Dparid');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('dlong');
		$grid->label('Dlong');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('dstop');
		$grid->label('Dstop');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
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
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('CAJA','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('CAJA','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('CAJA','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('CAJA','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: cajaadd,\n\t\teditfunc: cajaedit");

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
		$mWHERE = $grid->geneTopWhere('caja');

		$response   = $grid->getData('caja', array(array()), array(), false, $mWHERE );
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
				$check = $this->datasis->dameval("SELECT count(*) FROM caja WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('caja', $data);
					echo "Registro Agregado";

					logusu('CAJA',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM caja WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM caja WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE caja SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("caja", $data);
				logusu('CAJA',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('caja', $data);
				logusu('CAJA',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM caja WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM caja WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM caja WHERE id=$id ");
				logusu('CAJA',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}


	function dataedit(){ 
		$this->rapyd->load("dataedit");
		$this->rapyd->uri->keep_persistence();
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit('Edici&oacute;n de caja','caja');
		$edit->back_url = site_url('ventas/caja/filteredgrid');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');

		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->caja = new inputField('Caja', 'caja');
		$edit->caja->rule = 'trim|required|callback_chexiste';
		$edit->caja->mode = 'autohide';
		$edit->caja->maxlength=3;
		$edit->caja->size = 4;
		$edit->caja->css_class='inputnum';

		$edit->ubica = new inputField('Ubicaci&oacute;n', 'ubica');
		//$edit->ubica->append('Puede colocar la direcci&oacute;n IP de la caja');
		$edit->ubica->maxlength=30;
		$edit->ubica->size = 20;
		$edit->ubica->rule='trim|strtoupper|callback_ip_caja';

		$edit->status = new dropdownField("Status", "status");
		$edit->status->option("C","Cerrado");
		$edit->status->option("A","Abierto");
		$edit->status->rule='required';
		$edit->status->style="width:150";

		$edit->factura = new inputField("Prox. Factura","factura");
		$edit->factura->rule = "trim";
		$edit->factura->maxlength=6;
		$edit->factura->size = 7;

		$edit->egreso  = new inputField("Prox. Retiro","egreso");
		$edit->egreso->rule = "trim";
		$edit->egreso->maxlength=6;
		$edit->egreso->size = 7;

		$edit->ingreso = new inputField("Prox. Egreso","ingreso");
		$edit->ingreso->rule = "trim";
		$edit->ingreso->maxlength=6;
		$edit->ingreso->size = 7;

		$edit->almacen = new dropdownField("Almacen", "almacen");
		$edit->almacen->option('','Seleccionar');
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");
		$edit->almacen->style="width:150px";

		
		$opt=array('impre'=>'Impresora','lector'=>'Lector','gaveta'=>'Gaveta','display'=>'Display');
		$ancho = "width:110px";
		foreach($opt AS $qu=>$grupo){
			$obj=$qu;
			$edit->$obj = new dropdownField("Puerto", $obj);
			$edit->$obj->options(array("NO/C"=>"NO CONECTADO","LP1"=> "LPT1","LP2"=>"LPT2","COM1"=>"COM1","COM2"=>"COM2"));
			$edit->$obj->group=$grupo;
			$edit->$obj->style=$ancho;

			$obj=$qu{0}.'baud';
			$edit->$obj = new inputField("Baud Rate",$obj);
			$edit->$obj->size = 5;
			$edit->$obj->maxlength=5;
			$edit->$obj->css_class='inputnum';
			$edit->$obj->rule = "trim";
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'parid';
			$edit->$obj = new dropdownField("Paridad", $obj);
			$edit->$obj->options(array("N"=>"NONE","E"=> "EVEN","O"=>"ODD"));
			$edit->$obj->maxlength=1;
			$edit->$obj->size = 2;
			$edit->$obj->style=$ancho;
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'long';
			$edit->$obj = new dropdownField("Longitud", $obj);
			$edit->$obj->options(array("8"=> "8 BITS","7"=>"7 BITS"));
			$edit->$obj->maxlength=1;
			$edit->$obj->size = 2;
			$edit->$obj->style=$ancho;
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'stop';
			$edit->$obj = new dropdownField("Bit de parada", $obj);
			$edit->$obj->options(array("1"=> "1 BIT","2"=>"2 BIT"));
			$edit->$obj->maxlength=1;
			$edit->$obj->size = 2;
			$edit->$obj->style=$ancho;
			$edit->$obj->group=$grupo;
		}
		for ($i=1;$i<=5;$i++){
			$obj='cont'.$i;
			$edit->$obj = new inputField("Codigo ASCII",$obj);
			$edit->$obj->css_class='inputnum';
			$edit->$obj->maxlength=3;
			$edit->$obj->size=4;
			if ($i!=1) $edit->$obj->in='cont1';
		}

		//$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_caja', $conten );


		//$data['content'] = $edit->output;
		//$data['title']   = "<h1>Cajas</h1>";
		//$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		//$this->load->view('view_ventanas_sola', $data);
		//$this->load->view('view_caja', $data);

	}
	
	function _post_insert($do){
		$codigo=$do->get('caja');
		$ubica=$do->get('ubica');
		$status=$do->get('status');
		logusu('caja',"CAJA $codigo UBICACION $ubica STATUS $status CREADA");
	}
	function _post_update($do){
		$codigo =$do->get('caja');
		$ubica  =$do->get('ubica');
		$status =$do->get('status');
		logusu('caja',"CAJA $codigo UBICACION $ubica STATUS $status MODIFICADA");
	}
	function _post_delete($do){
		$codigo=$do->get('caja');
		$ubica=$do->get('ubica');
		$status=$do->get('status');
		logusu('caja',"CAJA $codigo UBICACION $ubica STATUS $status ELIMINADA");
	}
	
	//VALIDACIONES
	function chexiste($codigo){
		$codigo=$this->input->post('caja');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM caja WHERE caja='$codigo'");
		if ($check > 0){
			$ubica=$this->datasis->dameval("SELECT ubica FROM caja WHERE caja='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para la caja $ubica");
			return FALSE;
		}else {
			return TRUE;
		}
	}

	function ip_caja($ubica){
		$numero=$this->rapyd->uri->get_edited_id();
		if($this->rapyd->uri->is_set('update'))
			return $this->_ipval($ubica,$numero);
		else
			return $this->_ipval($ubica);
	}

	function _ipval($ubica,$numero=null){
		if(preg_match("/^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\\.([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])){3}$/", $ubica)>0){
			if(!empty($numero)) $where = " AND caja!=".$this->db->escape($numero); else $where='';
			$mSQL="SELECT COUNT(*) FROM caja WHERE ubica='$ubica' $where";
			$cant=$this->datasis->dameval($mSQL);
			if($cant>0){
				$this->validation->set_message('ip_caja', "La ip dada en el campo <b>%s</b> ya fue asignada a otro registro");
				return FALSE;
			}
		}
		return TRUE;
	}

	function limpia_ip($ip){
		$ip=trim($ip);
		$ip=preg_replace('/\.0+/','.',$ip);
		return str_replace('..','.0.',$ip);
	}

/*
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"caja","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('caja');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('caja');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$caja = $campos['caja'];

		if ( !empty($caja) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM caja WHERE caja='$caja'") == 0)
			{
				$mSQL = $this->db->insert_string("caja", $campos );
				$this->db->simple_query($mSQL);
				logusu('caja',"CAJA $caja CREADO");
				echo "{ success: true, message: 'Caja Agregada'}";
			} else {
				echo "{ success: false, message: 'Ya existe una caja con ese codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Falta el campo codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$caja = $campos['caja'];
		unset($campos['caja']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("caja", $campos,"id=".$data['data']['id'] );
		$this->db->simple_query($mSQL);
		logusu('caja',"caja $caja ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'caja Modificada -> ".$data['data']['caja']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$caja  = $campos['caja'];
		$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sfpa WHERE caja='$caja'");

		if ($check > 0){
			echo "{ success: false, message: 'Caja no puede ser Borrada'}";
		} else {
			$this->db->simple_query("DELETE FROM caja WHERE caja='$caja'");
			logusu('caja',"CAJA $caja ELIMINADO");
			echo "{ success: true, message: 'caja Eliminada'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function cajaextjs(){
		$encabeza='CONFIGURACION DE CAJAS';
		$listados= $this->datasis->listados('caja');
		$otros=$this->datasis->otros('caja', 'ventas/caja');

		$urlajax = 'ventas/caja/';
		$variables = "";

		$mSQL = "SELECT ubica, CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ORDER BY ubica";
		$alma = $this->datasis->llenacombo($mSQL);

		$puertos = '["NO/C","NO CONECTADO"],["LP1","PUERTO PARALELO 1"],["LP2","PUERTO PARALELO 2"],["COM1","PUERTO SERIAL 1"],["COM2","PUERTO SERIAL 2"]';
		$paridad = '["N","NONE"],["E","EVEN"],["O","ODD"]';
		$long    = '["8","8 BITS"],["7","7 BITS"]';
		
		$funciones = "function estado(val){if ( val == 'A'){ return 'Abierto';} else {return  'Cerrado';}}";
		
		$valida = "
		{ type: 'length', field: 'caja',   min: 1 },
		{ type: 'length', field: 'nombre', min: 1 }
		";
		
		$columnas = "
		{ header: 'Codigo',      dataIndex: 'caja',    width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Descripcion', dataIndex: 'ubica',   width: 120, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Status',      dataIndex: 'status',  width:  60, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }, renderer: estado}, 
		{ header: 'Factura',     dataIndex: 'factura', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Egreso',      dataIndex: 'egreso',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Ingreso',     dataIndex: 'ingreso', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Almacen',     dataIndex: 'almacen', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ text: 'Impresora', columns: [
			{ header: 'Puerto',   dataIndex: 'impre',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'BaudRate', dataIndex: 'ibaud',  width:  90, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Paridad',  dataIndex: 'ipari',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'Longitud', dataIndex: 'ilong',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'StopBit',  dataIndex: 'istop',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		]},
		{ text: 'Lector de Barras', columns: [
			{ header: 'Puerto',   dataIndex: 'lector', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'BaudRate', dataIndex: 'lbaud',  width:  90, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Paridad',  dataIndex: 'lpari',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'Longitud', dataIndex: 'llong',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'StopBit',  dataIndex: 'lstop',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		]},
		{ text: 'Gaveta de Dinero', columns: [
			{ header: 'Puerto',   dataIndex: 'gaveta',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'BaudRate', dataIndex: 'gbaud',  width:  90, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Paridad',  dataIndex: 'gpari',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'Longitud', dataIndex: 'glong',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'StopBit',  dataIndex: 'gstop',  width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		]},
		{ text: 'Visor de Caja', columns: [
			{ header: 'Puerto',   dataIndex: 'display', width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'BaudRate', dataIndex: 'dbaud',   width:  90, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Paridad',  dataIndex: 'dpari',   width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'Longitud', dataIndex: 'dlong',   width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
			{ header: 'StopBit',  dataIndex: 'dstop',   width:  50, sortable: true, field: { type: 'textfield' }, filter: { type: 'string' }}, 
		]},
		{ text: 'Comando de Apertura de Gaveta', columns: [
			{ header: 'Byte 1', dataIndex: 'cont1',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Byte 2', dataIndex: 'cont2',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Byte 3', dataIndex: 'cont3',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Byte 4', dataIndex: 'cont4',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
			{ header: 'Byte 5', dataIndex: 'cont5',  width:  50, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000') },
		]}

		//{ header: 'Egreso',   dataIndex: 'egreso',  width:  70, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		//{ header: 'Ingreso',  dataIndex: 'ingreso', width:  70, sortable: true, field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
	";

		$campos = "'id','caja','ubica','status','factura','egreso','ingreso','almacen','impre','ibaud','iparid','ilong','istop','lector','lbaud','lparid','llong','lstop','gaveta','gbaud','gparid','glong','gstop','cont1','cont2','cont3','cont4','cont5','display','dbaud','dparid','dlong','dstop'";
		
		$camposforma = "
							{
							xtype:'fieldset',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:50 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield',     fieldLabel: 'Codigo',      name: 'caja',    width:110, allowBlank: false, id: 'caja', labelWidth:70 },
									{ xtype: 'combo',         fieldLabel: 'Status',      name: 'tipo',    width:250,  store: [['A','Abierto'],['C','Cerrado']], labelWidth:110},
									{ xtype: 'textfield',     fieldLabel: 'Descripcion', name: 'ubica',   width:360, allowBlank: false, labelWidth:70 },
								]
							},{
							xtype:'fieldset',
							title: 'Contadores',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:50 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield',     fieldLabel: 'Factura',     name: 'ubica',   width:120, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Egreso',      name: 'ubica',   width:120, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Ingreso',     name: 'ubica',   width:120, allowBlank: false },
								]
							},{
							xtype:'fieldset',
							title: 'Impresora',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:50 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'combo',         fieldLabel: 'Puerto',      name: 'impre',   width:200,  store: [".$puertos."]},
									{ xtype: 'numberfield',   fieldLabel: 'Baud Rate',   name: 'ibaud',   width:160, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000'), labelWidth:80 },
									{ xtype: 'combo',         fieldLabel: 'Paridad',     name: 'iparid',  width:140,  store: [".$paridad."]},
									{ xtype: 'combo',         fieldLabel: 'Longitud',    name: 'ilong',   width:220,  store: [".$long."], labelWidth:140 },

/*
									{ xtype: 'textfield',     fieldLabel: 'Descripcion', name: 'ubica',   width:320, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Factura',     name: 'ubica',   width:320, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Egreso',      name: 'ubica',   width:320, allowBlank: false },
									{ xtype: 'textfield',     fieldLabel: 'Ingreso',     name: 'ubica',   width:320, allowBlank: false },

//									{ xtype: 'numberfield',   fieldLabel: 'Comision', name: 'comision', width:120, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
//									{ xtype: 'numberfield',   fieldLabel: 'Impuesto', name: 'impuesto', width:200, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), labelWidth: 130  },
//									{ xtype: 'textareafield', fieldLabel: 'Mensaje',  name: 'mensaje',  width:320, allowBlank: true },
* /
								]
							}
							
							
		";

		$titulow = 'Configuracion de Cajas';

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 400,
				height: 400,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						if (registro) {
							form.loadRecord(registro);
							form.findField('caja').setReadOnly(true);
						} else {
							form.findField('caja').setReadOnly(false);
						}
					}
				}
";

		$stores = "";

		$features = "features: [ filters],";
		$filtros = "var filters = { ftype: 'filters', encode: 'json', local: false }; ";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['stores']      = $stores;
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		
		$data['title']  = heading('Configuracion de Cajas');
		$this->load->view('extjs/extjsven',$data);
		
	}

*/
}
?>
