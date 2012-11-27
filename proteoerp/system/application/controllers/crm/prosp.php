<?php
class Prosp extends Controller {
	var $mModulo = 'PROSP';
	var $titp    = 'Modulo Prospectos';
	var $tits    = 'Modulo Prospectos';
	var $url     = 'crm/prosp/';

	function Prosp(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'PROSP', $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('prosp','id') ) {
			$this->db->simple_query('ALTER TABLE prosp DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE prosp ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE prosp ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
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
		$grid->wbotonadd(array('id'=>'btn_map', 'img'=>'images/pdf_logo.gif', 'alt' => 'Ver en mapa', 'label'=>'Ver en el mapa'));
		$WestPanel = $grid->deploywestp();

		$adic = array(
		array("id"=>"fedita",  "title"=>"Agregar/Editar Registro")
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel'] = $EastPanel;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('PROSP', 'JQ');
		$param['otros']       = $this->datasis->otros('PROSP', 'JQ');
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
		jQuery("#btn_map").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				'.$this->datasis->jwinopen("http://maps.google.es/?source=friendlink&q='+ret.lat+','+ret.lon").';
			} else { $.prompt("<h1>Por favor Seleccione un registro</h1>");}
		});';

		$bodyscript .= '
		function prospadd() {
			$.post("'.site_url('crm/prosp/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function prospedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('crm/prosp/dataedit/modify').'/"+id, function(data){
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
							'.$this->datasis->jwinopen(site_url('formatos/ver/PROSP').'/\'+res.id+\'/id\'').';
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

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));


		$grid->addField('categoria');
		$grid->label('Categoria');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:100, maxlength: 100 }',
		));


		$grid->addField('estado');
		$grid->label('Estado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:100, maxlength: 100 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:200, maxlength: 200 }',
		));


		$grid->addField('rif');
		$grid->label('Rif');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('ciudad');
		$grid->label('Ciudad');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:100, maxlength: 100 }',
		));


		$grid->addField('direc');
		$grid->label('Direccion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:200, maxlength: 200 }',
		));


		$grid->addField('telef');
		$grid->label('Telefono');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:100, maxlength: 100 }',
		));


		$grid->addField('lat');
		$grid->label('Latitud');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('lon');
		$grid->label('Longitud');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('tservicio');
		$grid->label('Tipo serv.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));


		$grid->addField('envios');
		$grid->label('Envios');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:200, maxlength: 200 }',
		));


		$grid->addField('email');
		$grid->label('Email');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:200, maxlength: 200 }',
		));


		$grid->addField('twitter');
		$grid->label('Twitter');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:200, maxlength: 200 }',
		));


		$grid->addField('facebook');
		$grid->label('Facebook');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:200, maxlength: 200 }',
		));


		$grid->addField('pin');
		$grid->label('Pin');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:200, maxlength: 200 }',
		));


		/*$grid->addField('id_pac');
		$grid->label('Id_pac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));*/


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('390');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('PROSP','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('PROSP','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('PROSP','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('PROSP','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: prospadd,\n\t\teditfunc: prospedit");

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
		$grid = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('prosp');

		$response   = $grid->getData('prosp', array(array()), array(), false, $mWHERE );
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
				$check = $this->datasis->dameval("SELECT count(*) FROM prosp WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('prosp', $data);
					echo "Registro Agregado";

					logusu('PROSP',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM prosp WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM prosp WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE prosp SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("prosp", $data);
				logusu('PROSP',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('prosp', $data);
				logusu('PROSP',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM prosp WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM prosp WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM prosp WHERE id=$id ");
				logusu('PROSP',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'prosp');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->categoria = new dropdownField('Categor&iacute;a','categoria');
		$edit->categoria->option('','Todos');
		$edit->categoria->options('SELECT categoria,categoria AS val FROM prosp GROUP BY categoria ');
		$edit->categoria->style = 'width:140px';

		$edit->estado = new inputField('Estado','estado');
		$edit->estado->rule='max_length[100]';
		$edit->estado->size =20;
		$edit->estado->maxlength =100;

		$edit->ciudad = new inputField('Ciudad','ciudad');
		$edit->ciudad->rule='max_length[100]';
		$edit->ciudad->size =80;
		$edit->ciudad->maxlength =100;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[200]';
		$edit->nombre->size =80;
		$edit->nombre->maxlength =200;

		$edit->rif = new inputField('Rif','rif');
		$edit->rif->rule='max_length[15]';
		$edit->rif->size =17;
		$edit->rif->maxlength =15;

		$edit->direc = new inputField('Direcci&oacute;n','direc');
		$edit->direc->rule='max_length[200]';
		$edit->direc->size =80;
		$edit->direc->maxlength =200;

		$edit->telef = new inputField('Tel&eacute;fonos','telef');
		$edit->telef->rule='max_length[100]';
		$edit->telef->size =80;
		$edit->telef->maxlength =100;

		$edit->lat = new inputField('Latitud','lat');
		$edit->lat->rule='max_length[8]';
		$edit->lat->size =10;
		$edit->lat->maxlength =8;

		$edit->lon = new inputField('Longitud','lon');
		$edit->lon->rule='max_length[8]';
		$edit->lon->size =10;
		$edit->lon->maxlength =8;

		$edit->tservicio = new inputField('Tipo de servicio','tservicio');
		$edit->tservicio->rule='max_length[20]';
		$edit->tservicio->size =22;
		$edit->tservicio->maxlength =20;

		$edit->envios = new inputField('Empresa de Env&iacute;os','envios');
		$edit->envios->rule='max_length[200]';
		$edit->envios->size =80;
		$edit->envios->maxlength =200;

		$edit->email = new inputField('Email','email');
		$edit->email->rule='max_length[200]';
		$edit->email->size =80;
		$edit->email->maxlength =200;

		$edit->twitter = new inputField('Twitter','twitter');
		$edit->twitter->rule='max_length[200]';
		$edit->twitter->size =80;
		$edit->twitter->maxlength =200;

		$edit->facebook = new inputField('Facebook','facebook');
		$edit->facebook->rule='max_length[200]';
		$edit->facebook->size =80;
		$edit->facebook->maxlength =200;

		$edit->pin = new inputField('Pin','pin');
		$edit->pin->rule='max_length[200]';
		$edit->pin->size =80;
		$edit->pin->maxlength =200;

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
