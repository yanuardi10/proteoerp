<?php
class Lcierre extends Controller {
	var $mModulo = 'LCIERRE';
	var $titp    = 'Modulo de cierre de producci&oacute;n';
	var $tits    = 'Modulo de cierre de producci&oacute;n';
	var $url     = 'leche/lcierre/';

	function Lcierre(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'LCIERRE', $ventana=0 );
	}

	function index(){

		//$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
		$this->datasis->creaintramenu(array('modulo'=>'224','titulo'=>'Cierre de Producción','mensaje'=>'Cierre de Producción','panel'=>'LECHE','ejecutar'=>'leche/lcierre','target'=>'popu','visible'=>'S','pertenece'=>'2','ancho'=>900,'alto'=>600));
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$grid->setHeight('185');
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
		$param['listados']     = $this->datasis->listados('LCIERRE', 'JQ');
		$param['otros']        = $this->datasis->otros('LCIERRE', 'JQ');
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
		function lcierreadd() {
			$.post("'.site_url('leche/lcierre/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function lcierreedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('leche/lcierre/dataedit/modify').'/"+id, function(data){
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
							'.$this->datasis->jwinopen(site_url('formatos/ver/LCIERRE').'/\'+res.id+\'/id\'').';
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


		$grid->addField('dia');
		$grid->label('Dia');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));


		$grid->addField('recepcion');
		$grid->label('Recepcion');
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


		$grid->addField('enfriamiento');
		$grid->label('Enfriamiento');
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


		$grid->addField('requeson');
		$grid->label('Requeson');
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


		$grid->addField('requesonteorico');
		$grid->label('Requesonteorico');
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


		$grid->addField('requesonreal');
		$grid->label('Requesonreal');
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
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
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
		$grid->setAdd(    $this->datasis->sidapuede('LCIERRE','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('LCIERRE','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('LCIERRE','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('LCIERRE','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: lcierreadd,\n\t\teditfunc: lcierreedit");

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
	function getdata(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('lcierre');

		$response   = $grid->getData('lcierre', array(array()), array(), false, $mWHERE, 'id','desc' );
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
				$check = $this->datasis->dameval("SELECT count(*) FROM lcierre WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('lcierre', $data);
					echo "Registro Agregado";

					logusu('LCIERRE',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM lcierre WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM lcierre WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE lcierre SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("lcierre", $data);
				logusu('LCIERRE',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('lcierre', $data);
				logusu('LCIERRE',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM lcierre WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM lcierre WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM lcierre WHERE id=$id ");
				logusu('LCIERRE',"Registro ????? ELIMINADO");
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

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));


		$grid->addField('id_lcierre');
		$grid->label('Id_lcierre');
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


		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('descrip');
		$grid->label('Descrip');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:45, maxlength: 45 }',
		));


		$grid->addField('unidades');
		$grid->label('Unidades');
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


		$grid->addField('cestas');
		$grid->label('Cestas');
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
			$id = $this->datasis->dameval("SELECT MAX(id) FROM lcierre");
		}
		if(empty($id)) return "";
		$dbid    = $this->db->escape($id);
		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT * FROM itlcierre WHERE id_lcierre=$dbid";

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

	function dataedit($urlfecha=null){
		$semana=array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES');

		if(preg_match('/(?P<anio>\d{4})\-(?P<mes>\d{2})\-(?P<dia>\d{2})/', $urlfecha, $matches)>0){
			$fecha = date('Y-m-d', mktime(0, 0, 0, $matches['mes'], $matches['dia'], $matches['anio']));
			$dia   = $semana[date('w', mktime(0, 0, 0, $matches['mes'], $matches['dia'], $matches['anio']))];
		}else{
			$fecha= date('Y-m-d');
			$dia   = $semana[date('w')];
		}

		$this->rapyd->load('datadetails','dataobject');

		$do = new DataObject('lcierre');
		//$do->pointer('scli' ,'scli.cliente=rivc.cod_cli','sprv.tipo AS sprvtipo, sprv.reteiva AS sprvreteiva','left');
		$do->rel_one_to_many('itlcierre' ,'itlcierre' ,array('id'=>'id_lcierre'));
		$do->order_rel_one_to_many('itlcierre','codigo');

		$edit = new DataDetails($this->tits, $do);
		$edit->on_save_redirect=false;

		$edit->post_process('insert','_post_insert_lcierre');
		$edit->post_process('update','_post_update_lcierre');
		$edit->post_process('delete','_post_delete_lcierre');
		$edit->pre_process('insert' ,'_pre_insert_lcierre');
		//$edit->pre_process('update' ,'_pre_update_lcierre');
		//$edit->pre_process('delete' ,'_pre_delete_lcierre');

		$edit->requeson = new inputField('Requeson','requeson');
		$edit->requeson->rule='required';
		$edit->requeson->size =12;
		$edit->requeson->maxlength =10;

		$edit->dia = new inputField('D&iacute;a','dia');
		$edit->dia->size =12;
		$edit->dia->maxlength =10;
		$edit->dia->type='inputhidden';
		$edit->dia->insertValue=$dia;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha|required';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;
		$edit->fecha->type='inputhidden';
		$edit->fecha->insertValue=$fecha;
		$edit->fecha->calendar=false;

		$edit->usuario = new autoUpdateField('usuario', $this->secu->usuario(), $this->secu->usuario());

		//Inicio del detalle
		$i  = 0;
		$rel= 'itlcierre';
		$id = $edit->get_from_dataobjetct('id');

		if($id){
			$sel=array('a.codigo','a.descrip');
			$this->db->select($sel);
			$this->db->from('itlcierre AS a');
			$this->db->where('a.id_lcierre',$id);
			$this->db->order_by('a.codigo');
		}else{
			$sel=array('a.codigo','a.descrip');
			$this->db->select($sel);
			$this->db->from('lprod AS a');
			$this->db->where('a.fecha',$fecha);
			$this->db->group_by('a.codigo');
			$this->db->order_by('a.codigo');
		}

		$query = $this->db->get();
		$edit->detail_expand_except($rel);
		foreach ($query->result() as $row){
			$obj='itcodigo_'.$i;
			$edit->$obj = new inputField('Codigo',$obj);
			$edit->$obj->db_name = 'codigo';
			$edit->$obj->rule='max_length[15]|required';
			$edit->$obj->size =7;
			$edit->$obj->insertValue=$row->codigo;
			$edit->$obj->type='inputhidden';
			$edit->$obj->maxlength =4;
			$edit->$obj->rel_id = $rel;
			$edit->$obj->ind    = $i;

			$obj='itdescrip_'.$i;
			$edit->$obj = new inputField('',$obj);
			$edit->$obj->db_name = 'descrip';
			$edit->$obj->type='inputhidden';
			$edit->$obj->insertValue=$row->descrip;
			$edit->$obj->rel_id = $rel;
			$edit->$obj->ind    = $i;

			$obj='itcestas_'.$i;
			$edit->$obj = new inputField('Cestas',$obj);
			$edit->$obj->db_name = 'cestas';
			$edit->$obj->rule='max_length[12]|numeric|required';
			$edit->$obj->css_class='inputnum';
			$edit->$obj->size =14;
			$edit->$obj->maxlength =12;
			$edit->$obj->onkeyup='totalizar();';
			$edit->$obj->rel_id = $rel;
			$edit->$obj->ind    = $i;

			$obj='itunidades_'.$i;
			$edit->$obj = new inputField('Unidades',$obj);
			$edit->$obj->db_name = 'unidades';
			$edit->$obj->rule='max_length[12]|numeric|required';
			$edit->$obj->css_class='inputnum';
			$edit->$obj->size =14;
			$edit->$obj->maxlength =12;
			$edit->$obj->onkeyup='totalizar();';
			$edit->$obj->rel_id = $rel;
			$edit->$obj->ind    = $i;

			$obj='itpeso_'.$i;
			$edit->$obj = new inputField('Producido (Peso)',$obj);
			$edit->$obj->db_name = 'peso';
			$edit->$obj->rule='max_length[12]|numeric';
			$edit->$obj->css_class='inputnum';
			$edit->$obj->size =14;
			$edit->$obj->maxlength =12;
			$edit->$obj->rel_id = $rel;
			$edit->$obj->ind    = $i;
			$edit->$obj->when   = array('modify','show');

			$i++;
		}
		$max_rel_count = $i;
		//Fin del detalle

		$edit->buttons('add_rel');
		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			//echo $edit->output;
			$conten['max_rel_count']=$max_rel_count;
			$conten['form']  =& $edit;
			$this->load->view('view_lcierre', $conten);
		}
	}

	function _pre_insert_lcierre($do){
		$fecha  = $do->get('fecha');
		$dbfecha= $this->db->escape($fecha);
		$cana   = $this->datasis->dameval("SELECT COUNT(*) FROM lcierre WHERE fecha=".$dbfecha);

		if($cana>0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert'] = 'Ya existe un cierre para el d&iacute;a '.dbdate_to_human($fecha).' no puede realizar otro.';
			return false;
		}
		return true;
	}

	function _pre_update_lcierre($do){
		return true;
	}

	function _pre_delete_lcierre($do){
		return true;
	}

	function _post_insert_lcierre($do){
		$fecha    = $do->get('fecha');
		$dbfecha  = $this->escape($fecha);

		$recibido = $this->datasis->dameval("SELECT SUM(lista)  AS reci FROM lrece WHERE fecha=$dbfecha");
		$producido= $this->datasis->dameval("SELECT SUM(litros) AS prod FROM lprod WHERE fecha=$dbfecha");

		$enfria = $recibido-$producido;
		//Suma en el inventario
		if($enfria>0){

		}

		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update_lcierre($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete_lcierre($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}
}
