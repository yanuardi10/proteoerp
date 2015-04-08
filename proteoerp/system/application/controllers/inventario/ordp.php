<?php
/** 
 * ProteoERP 
 * 
 * @autor    Andres Hocevar 
 * @license  GNU GPL v3
*/
class Ordp extends Controller {
	var $mModulo = 'ORDP';
	var $titp    = 'ORDEN DE PEDIDO';
	var $tits    = 'ORDEN DE PEDIDO';
	var $url     = 'inventario/ordp/';

	function Ordp(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( 'ORDP', $ventana=0, $this->titp  );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('ordp','id') ) {
			$this->db->query('ALTER TABLE ordp DROP PRIMARY KEY');
			$this->db->query('ALTER TABLE ordp ADD UNIQUE INDEX numero (numero)');
			$this->db->query('ALTER TABLE ordp ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
		//$this->datasis->creaintramenu(array('modulo'=>'000','titulo'=>'<#titulo#>','mensaje'=>'<#mensaje#>','panel'=>'<#panal#>','ejecutar'=>'<#ejecuta#>','target'=>'popu','visible'=>'S','pertenece'=>'<#pertenece#>','ancho'=>900,'alto'=>600));
		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//******************************************************************
	// Layout en la Ventana
	//
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
			array('id'=>'fedita',  'title'=>'Agregar/Editar Registro'),
			array('id'=>'fshow' ,  'title'=>'Mostrar Registro'),
			array('id'=>'fborra',  'title'=>'Eliminar Registro')
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']    = $WestPanel;
		$param['script']       = script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('ORDP', 'JQ');
		$param['otros']        = $this->datasis->otros('ORDP', 'JQ');
		$param['centerpanel']  = $centerpanel;
		$param['temas']        = array('proteo','darkness','anexos1');
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;
		$param['tamano']       = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);

	}

	//******************************************************************
	// Funciones de los Botones
	//
	function bodyscript( $grid0, $grid1 ){
		$bodyscript = '		<script type="text/javascript">';

		$bodyscript .= '
		function ordpadd(){
			$.post("'.site_url($this->url.'dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function ordpedit(){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url($this->url.'dataedit/modify').'/"+id, function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});
			} else {
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';

		$bodyscript .= '
		function ordpshow(){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url($this->url.'dataedit/show').'/"+id, function(data){
					$("#fshow").html(data);
					$("#fshow").dialog( "open" );
				});
			} else {
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';

		$bodyscript .= '
		function ordpdel() {
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				if(confirm(" Seguro desea eliminar el registro?")){
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					mId = id;
					$.post("'.site_url($this->url.'dataedit/do_delete').'/"+id, function(data){
						try{
							var json = JSON.parse(data);
							if (json.status == "A"){
								apprise("Registro eliminado");
								jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
							}else{
								apprise("Registro no se puede eliminado");
							}
						}catch(e){
							$("#fborra").html(data);
							$("#fborra").dialog( "open" );
						}
					});
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
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
							try{
								var json = JSON.parse(r);
								if (json.status == "A"){
									apprise("Registro Guardado");
									$( "#fedita" ).dialog( "close" );
									grid.trigger("reloadGrid");
									'.$this->datasis->jwinopen(site_url('formatos/ver/ORDP').'/\'+json.pk.id+\'/id\'').';
									return true;
								} else {
									apprise(json.mensaje);
								}
							}catch(e){
								$("#fedita").html(r);
							}
						}
					})
				},
				"Cancelar": function() {
					$("#fedita").html("");
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				$("#fedita").html("");
				allFields.val( "" ).removeClass( "ui-state-error" );
			}
		});';

		$bodyscript .= '
		$("#fshow").dialog({
			autoOpen: false, height: 500, width: 700, modal: true,
			buttons: {
				"Aceptar": function() {
					$("#fshow").html("");
					$( this ).dialog( "close" );
				},
			},
			close: function() {
				$("#fshow").html("");
			}
		});';

		$bodyscript .= '
		$("#fborra").dialog({
			autoOpen: false, height: 300, width: 400, modal: true,
			buttons: {
				"Aceptar": function() {
					$("#fborra").html("");
					jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
					$( this ).dialog( "close" );
				},
			},
			close: function() {
				jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
				$("#fborra").html("");
			}
		});';

		$bodyscript .= '});'."\n";

		$bodyscript .= "\n</script>\n";
		$bodyscript .= "";
		return $bodyscript;
	}

	//******************************************************************
	// Definicion del Grid y la Forma
	//
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
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


		$grid->addField('cana');
		$grid->label('Cana');
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
			'editoptions'   => '{ size:2, maxlength: 2 }',
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


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 40 }',
		));


		$grid->addField('instrucciones');
		$grid->label('Instrucciones');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:2, cols:60}'",
		));


		$grid->addField('reserva');
		$grid->label('Reserva');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
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


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));


		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
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
		$grid->setAdd(    $this->datasis->sidapuede('ORDP','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('ORDP','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('ORDP','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('ORDP','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("addfunc: ordpadd, editfunc: ordpedit, delfunc: ordpdel, viewfunc: ordpshow");

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
		$mWHERE = $grid->geneTopWhere('ordp');

		$response   = $grid->getData('ordp', array(array()), array(), false, $mWHERE, 'id','desc' );
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
				$check = $this->datasis->dameval("SELECT count(*) FROM ordp WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('ordp', $data);
					echo "Registro Agregado";

					logusu('ORDP',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM ordp WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM ordp WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE ordp SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("ordp", $data);
				logusu('ORDP',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('ordp', $data);
				logusu('ORDP',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM ordp WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM ordp WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->query("DELETE FROM ordp WHERE id=$id ");
				logusu('ORDP',"Registro ????? ELIMINADO");
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

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
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
			'editoptions'   => '{ size:40, maxlength: 40 }',
		));


		$grid->addField('cantidad');
		$grid->label('Cantidad');
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


		$grid->addField('merma');
		$grid->label('Merma');
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


		$grid->addField('costo');
		$grid->label('Costo');
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


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));


		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
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


		$grid->addField('fijo');
		$grid->label('Fijo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('id_ordp');
		$grid->label('Id_ordp');
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
	function getdatait( $id = 0 )
	{
		if ($id === 0 ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM ordp");
		}
		if(empty($id)) return "";
		$numero   = $this->datasis->dameval("SELECT numero FROM ordp WHERE id=$id");
		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT * FROM ordpitem WHERE numero='$numero' ";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setDatait()
	{
	}

	//***********************************
	// DataEdit  
	//***********************************

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		$script= '
		$(function() {
			$("#fecha").datepicker({dateFormat:"dd/mm/yy"});
			$(".inputnum").numeric(".");
		});
		';

		$do = new DataObject('ordp');

		$do->rel_one_to_many('ordpitem','ordpitem','numero');
		$edit = new DataDetails($this->tits, $do );

		$edit->script($script,'modify');
		$edit->script($script,'create');
		$edit->on_save_redirect=false;

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert', '_pre_insert' );
		$edit->pre_process('update', '_pre_update' );
		$edit->pre_process('delete', '_pre_delete' );

		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule='';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;

		$edit->fecha = new dateonlyField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->calendar=false;
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->codigo = new inputField('Codigo','codigo');
		$edit->codigo->rule='';
		$edit->codigo->size =17;
		$edit->codigo->maxlength =15;

		$edit->almacen = new inputField('Almacen','almacen');
		$edit->almacen->rule='';
		$edit->almacen->size =6;
		$edit->almacen->maxlength =4;

		$edit->cana = new inputField('Cana','cana');
		$edit->cana->rule='numeric';
		$edit->cana->css_class='inputnum';
		$edit->cana->size =12;
		$edit->cana->maxlength =10;

		$edit->status = new inputField('Status','status');
		$edit->status->rule='';
		$edit->status->size =4;
		$edit->status->maxlength =2;

		$edit->cliente = new inputField('Cliente','cliente');
		$edit->cliente->rule='';
		$edit->cliente->size =7;
		$edit->cliente->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;

		$edit->instrucciones = new textareaField('Instrucciones','instrucciones');
		$edit->instrucciones->rule='';
		$edit->instrucciones->cols = 70;
		$edit->instrucciones->rows = 4;

		$edit->reserva = new inputField('Reserva','reserva');
		$edit->reserva->rule='';
		$edit->reserva->size =3;
		$edit->reserva->maxlength =1;

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));

		$edit->modificado = new inputField('Modificado','modificado');
		$edit->modificado->rule='';
		$edit->modificado->size =10;
		$edit->modificado->maxlength =8;


		//******************************************************************
		// Detalle 
		$edit->numero = new inputField('Numero','numero_<#i#>');
		$edit->numero->rule='';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;
		$edit->numero->rel_id ='ordpitem';

		$edit->codigo = new inputField('Codigo','codigo_<#i#>');
		$edit->codigo->rule='';
		$edit->codigo->size =17;
		$edit->codigo->maxlength =15;
		$edit->codigo->rel_id ='ordpitem';

		$edit->descrip = new inputField('Descrip','descrip_<#i#>');
		$edit->descrip->rule='';
		$edit->descrip->size =42;
		$edit->descrip->maxlength =40;
		$edit->descrip->rel_id ='ordpitem';

		$edit->cantidad = new inputField('Cantidad','cantidad_<#i#>');
		$edit->cantidad->rule='numeric';
		$edit->cantidad->css_class='inputnum';
		$edit->cantidad->size =16;
		$edit->cantidad->maxlength =14;
		$edit->cantidad->rel_id ='ordpitem';

		$edit->merma = new inputField('Merma','merma_<#i#>');
		$edit->merma->rule='numeric';
		$edit->merma->css_class='inputnum';
		$edit->merma->size =12;
		$edit->merma->maxlength =10;
		$edit->merma->rel_id ='ordpitem';

		$edit->costo = new inputField('Costo','costo_<#i#>');
		$edit->costo->rule='numeric';
		$edit->costo->css_class='inputnum';
		$edit->costo->size =19;
		$edit->costo->maxlength =17;
		$edit->costo->rel_id ='ordpitem';

		$edit->modificado = new inputField('Modificado','modificado_<#i#>');
		$edit->modificado->rule='';
		$edit->modificado->size =10;
		$edit->modificado->maxlength =8;
		$edit->modificado->rel_id ='ordpitem';

		$edit->fijo = new inputField('Fijo','fijo_<#i#>');
		$edit->fijo->rule='';
		$edit->fijo->size =3;
		$edit->fijo->maxlength =1;
		$edit->fijo->rel_id ='ordpitem';

		$edit->id_ordp = new inputField('Id_ordp','id_ordp_<#i#>');
		$edit->id_ordp->rule='integer';
		$edit->id_ordp->css_class='inputonlynum';
		$edit->id_ordp->size =13;
		$edit->id_ordp->maxlength =11;
		$edit->id_ordp->rel_id ='ordpitem';

		//******************************************************************

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
			$conten['form']  =& $edit;
			$this->load->view('view_ordp', $conten);
		}
	}

	function _pre_insert($do){
		$do->error_message_ar['pre_ins']='';
		return true;
	}

	function _pre_update($do){
		$do->error_message_ar['pre_upd']='';
		return true;
	}

	function _pre_delete($do){
		$do->error_message_ar['pre_del']='';
		return false;
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

	function instalar(){
		if (!$this->db->table_exists('ordp')) {
			$mSQL="CREATE TABLE `ordp` (
			  `numero` varchar(8) NOT NULL DEFAULT '',
			  `fecha` date DEFAULT NULL,
			  `codigo` varchar(15) DEFAULT NULL COMMENT 'Codigo de inventario',
			  `almacen` varchar(4) DEFAULT NULL COMMENT 'Almacen de descuento',
			  `cana` decimal(10,2) DEFAULT NULL COMMENT 'Cantidad a producir',
			  `status` char(2) DEFAULT '0' COMMENT 'Activa, Pausada, Finalizada',
			  `cliente` char(5) DEFAULT NULL,
			  `nombre` varchar(40) DEFAULT NULL COMMENT 'Nombre del Cliente',
			  `instrucciones` text,
			  `reserva` char(1) DEFAULT NULL COMMENT 'Si ya se resevo el inventario',
			  `estampa` date NOT NULL DEFAULT '0000-00-00',
			  `usuario` varchar(12) NOT NULL DEFAULT '',
			  `hora` varchar(8) NOT NULL DEFAULT '',
			  `modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `numero` (`numero`),
			  KEY `modificado` (`modificado`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Orden de Produccion'";
			$this->db->query($mSQL);
		}
		//$campos=$this->db->list_fields('ordp');
		//if(!in_array('<#campo#>',$campos)){ }

		if (!$this->db->table_exists('ordpitem')) {
			$mSQL="CREATE TABLE `ordpitem` (
			  `numero` varchar(8) DEFAULT '',
			  `codigo` varchar(15) DEFAULT NULL,
			  `descrip` varchar(40) DEFAULT NULL,
			  `cantidad` decimal(14,3) DEFAULT '0.000',
			  `merma` decimal(10,2) DEFAULT '0.00',
			  `costo` decimal(17,2) DEFAULT '0.00',
			  `estampa` date DEFAULT NULL,
			  `usuario` varchar(12) DEFAULT '',
			  `hora` varchar(8) DEFAULT '',
			  `modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `fijo` char(1) DEFAULT 'N' COMMENT 'N si depende de la cantidad, S si no depende de la cantidad',
			  `id_ordp` int(11) DEFAULT NULL,
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  PRIMARY KEY (`id`),
			  KEY `modificado` (`modificado`),
			  KEY `numero` (`numero`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Insumos de la Orden de Produccion'";
			$this->db->query($mSQL);
		}
		//$campos=$this->db->list_fields('ordpitem');
		//if(!in_array('<#campo#>',$campos)){ }
	}
}

/*
class ordp extends Controller {
	var $titp='Orden de producci&oacute;n';
	var $tits='Orden de producci&oacute;n';
	var $url ='inventario/ordp/';

	function ordp(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id('324',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'ordp');

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->numero = new inputField('N&uacute;mero','numero');
		$filter->numero->rule      ='max_length[8]';
		$filter->numero->size      =10;
		$filter->numero->maxlength =8;

		$filter->status = new dropdownField('Estatus','status');
		$filter->status->option('A','Abierto');
		$filter->status->option('E','Produciendo');
		$filter->status->option('P','Pausado');
		$filter->status->option('C','Cerrado');

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->rule      ='max_length[40]';
		$filter->nombre->size      =42;
		$filter->nombre->maxlength =40;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#numero#>');

		function dicstatus($status){
			if($status=='A'){
				$rt='Abierto';
			}elseif($status=='C'){
				$rt='Cerrado';
			}elseif($status=='P'){
				$rt='Pausado';
			}elseif($status=='E'){
				$rt='Produciendo';
			}elseif($status=='T'){
				$rt='Terminado';
			}else{
				$rt=$status;
			}
			return $rt;
		}

		$grid = new DataGrid('');
		$grid->use_function('dicstatus');
		$grid->order_by('id','desc');
		$grid->per_page = 40;

		$grid->column_orderby('N&uacute;mero',$uri,'numero','align="left"');
		$grid->column_orderby('Fecha'  ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align="left"');
		$grid->column_orderby('Estatus','<dicstatus><#status#></dicstatus>' ,'status','align="center"');
		$grid->column_orderby('Cliente','cliente','cliente','align="left"');
		$grid->column_orderby('Nombre' ,'nombre'  ,'nombre','align="left"');
		//$grid->column_orderby('Instrucciones','instrucciones','instrucciones','align="left"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);

	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$do = new DataObject('ordp');
		$do->pointer('scli' ,'scli.cliente=ordp.cliente','scli.nombre AS sclinombre, scli.rifci AS sclirifci'  ,'left');
		$do->pointer('sinv' ,'sinv.codigo=ordp.codigo'  ,'sinv.descrip AS sinvdescrip','left');
		$do->rel_one_to_many('ordpindi' , 'ordpindi' , array('id'=>'id_ordp'));
		$do->rel_one_to_many('ordpitem' , 'ordpitem' , array('id'=>'id_ordp'));
		$do->rel_one_to_many('ordplabor', 'ordplabor', array('id'=>'id_ordp'));
		$do->order_rel_one_to_many('ordplabor','secuencia');

		$edit = new DataDetails($this->tits, $do);
		$edit->back_url = site_url($this->url.'filteredgrid');
		$edit->set_rel_title('ordpindi' ,'Agregar Gasto');
		$edit->set_rel_title('ordpitem' ,'Agregar Art&iacute;culo');
		$edit->set_rel_title('ordplabor','Agregar Labor');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process( 'insert','_pre_insert');
		$edit->pre_process( 'update','_pre_update');
		$edit->pre_process( 'delete','_pre_delete');

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha|required';
		$edit->fecha->insertValue=date('Y-m-d');
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;
		$edit->numero->mode='autohide';

		$edit->status = new dropdownField('Estatus','status');
		$edit->status->option('A','Abierto');
		$edit->status->option('E','Produciendo');
		$edit->status->option('P','Pausado');
		$edit->status->option('C','Cerrado');
		$edit->status->option('T','Terminado');
		$edit->status->rule='enum[A,P,Es,C]|required';
		$edit->status->style='width:100px';
		$edit->status->mode='autohide';

		$edit->cliente = new inputField('Cliente','cliente');
		$edit->cliente->rule='max_length[5]|required|existescli';
		$edit->cliente->size =7;
		$edit->cliente->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule ='max_length[40]';
		$edit->nombre->type ='inputhidden';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;

		$edit->instrucciones = new textareaField('Instrucciones','instrucciones');
		$edit->instrucciones->style='width:100%';
		$edit->instrucciones->cols = 70;
		$edit->instrucciones->rows = 4;

		$edit->codigo = new inputField('Art&iacute;culo','codigo');
		$edit->codigo->rule ='max_length[15]|existesinv';
		$edit->codigo->size =7;
		$edit->codigo->maxlength =15;
		$edit->codigo->rule='required|existesinv';

		$edit->cana = new inputField('Cantidad a producir','cana');
		$edit->cana->rule='max_length[10]|numeric|required';
		$edit->cana->css_class='inputnum';
		$edit->cana->size =5;
		$edit->cana->autocomplete=false;
		$edit->cana->maxlength =10;
		$edit->cana->insertValue='1';

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca');
		$edit->desca->db_name='sinvdescrip';
		$edit->desca->maxlength=50;
		$edit->desca->type ='inputhidden';
		$edit->desca->pointer=true;

		$edit->almacen= new dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->options('SELECT ubica,ubides FROM caub WHERE ubica NOT IN (\'APRO\',\'PROD\') AND gasto=\'N\' ORDER BY ubides');
		$edit->almacen->rule='required';
		$alma = $this->secu->getalmacen();
		if(strlen($alma)<=0){
			$alma = $this->datasis->traevalor('ALMACEN');
		}
		$edit->almacen->insertValue=$alma;
		$edit->almacen->style='width:200px;';

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->usuario = new autoUpdateField('usuario',$this->secu->usuario(),$this->secu->usuario());
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));

		//**************************************
		//Inicio detalle ordpindi
		//**************************************
		$edit->it1_codigo = new inputField('Codigo','it1codigo_<#i#>');
		$edit->it1_codigo->db_name='codigo';
		$edit->it1_codigo->rule='max_length[6]';
		$edit->it1_codigo->size =8;
		$edit->it1_codigo->maxlength =6;
		$edit->it1_codigo->rel_id = 'ordpindi';

		$edit->it1_descrip = new inputField('Descrip','it1descrip_<#i#>');
		$edit->it1_descrip->db_name='descrip';
		$edit->it1_descrip->rule='max_length[40]';
		$edit->it1_descrip->type ='inputhidden';
		$edit->it1_descrip->size =42;
		$edit->it1_descrip->maxlength =40;
		$edit->it1_descrip->rel_id = 'ordpindi';

		$edit->it1_tipo = new dropdownField('tipo','it1tipo_<#i#>');
		$edit->it1_tipo->option('P','Porcentual');
		$edit->it1_tipo->option('M','Monetario');
		$edit->it1_tipo->insertValue='P';
		$edit->it1_tipo->style='width:100px';
		$edit->it1_tipo->title='Si el costo es por cargo Porcentual al costo de los insumos o monto Monetario fijo';
		$edit->it1_tipo->db_name='tipo';
		$edit->it1_tipo->rule='required|enum[M,P]';
		$edit->it1_tipo->rel_id = 'ordpindi';

		$edit->it1_porcentaje = new inputField('Porcentaje','it1porcentaje_<#i#>');
		$edit->it1_porcentaje->db_name='porcentaje';
		$edit->it1_porcentaje->rule='max_length[14]|numeric';
		$edit->it1_porcentaje->css_class='inputnum';
		$edit->it1_porcentaje->size =10;
		$edit->it1_porcentaje->autocomplete=false;
		$edit->it1_porcentaje->maxlength =14;
		$edit->it1_porcentaje->rel_id = 'ordpindi';

		//**************************************
		//fin detalle ordpindi inicio ordpitem
		//**************************************

		$edit->it2_codigo = new inputField('Codigo','it2codigo_<#i#>');
		$edit->it2_codigo->db_name='codigo';
		$edit->it2_codigo->rule='max_length[15]|required|existesinv';
		$edit->it2_codigo->size =17;
		$edit->it2_codigo->maxlength =15;
		$edit->it2_codigo->rel_id = 'ordpitem';

		$edit->it2_descrip = new inputField('Descripci&oacute;n','it2descrip_<#i#>');
		$edit->it2_descrip->db_name='descrip';
		$edit->it2_descrip->rule='max_length[40]';
		$edit->it2_descrip->size =42;
		$edit->it2_descrip->type ='inputhidden';
		$edit->it2_descrip->maxlength =40;
		$edit->it2_descrip->rel_id = 'ordpitem';

		$edit->it2_fijo = new dropdownField('fijo','it2fijo_<#i#>');
		$edit->it2_fijo->option('N','No');
		$edit->it2_fijo->option('S','Si');
		$edit->it2_fijo->insertValue='N';
		$edit->it2_fijo->style='width:50px';
		$edit->it2_fijo->title='Si depende o no de la cantidad a producir';
		$edit->it2_fijo->db_name='fijo';
		$edit->it2_fijo->rule='required|enum[S,N]';
		$edit->it2_fijo->rel_id = 'ordpitem';

		$edit->it2_cantidad = new inputField('Cantidad','it2cantidad_<#i#>');
		$edit->it2_cantidad->db_name='cantidad';
		$edit->it2_cantidad->rule='max_length[14]|numeric';
		$edit->it2_cantidad->css_class='inputnum';
		$edit->it2_cantidad->size =8;
		$edit->it2_cantidad->maxlength =14;
		$edit->it2_cantidad->rel_id = 'ordpitem';
		$edit->it2_cantidad->autocomplete=false;

		$edit->it2_merma = new inputField('Merma','it2merma_<#i#>');
		$edit->it2_merma->db_name='merma';
		$edit->it2_merma->rule='max_length[10]|numeric';
		$edit->it2_merma->css_class='inputnum';
		$edit->it2_merma->title='Porcentaje de p&eacute;rdida';
		$edit->it2_merma->size =5;
		$edit->it2_merma->maxlength =10;
		$edit->it2_merma->rel_id = 'ordpitem';

		$edit->it2_costo = new inputField('Costo','it2costo_<#i#>');
		$edit->it2_costo->db_name='costo';
		$edit->it2_costo->rule='max_length[17]|numeric';
		$edit->it2_costo->css_class='inputnum';
		$edit->it2_costo->size =10;
		$edit->it2_costo->maxlength =17;
		$edit->it2_costo->rel_id = 'ordpitem';

		//**************************************
		//fin detalle ordpitem inicio ordplabor
		//**************************************

		$edit->it3_id = new hiddenField('id','id_<#i#>');
		$edit->it3_id->db_name='id';
		$edit->it3_id->rel_id = 'ordplabor';

		$edit->it3_secuencia = new inputField('Secuencia','it3secuencia_<#i#>');
		$edit->it3_secuencia->db_name='secuencia';
		$edit->it3_secuencia->rule='max_length[6]|integer';
		$edit->it3_secuencia->css_class='inputonlynum';
		$edit->it3_secuencia->type = 'inputhidden';
		$edit->it3_secuencia->size = 5;
		$edit->it3_secuencia->maxlength =6;
		$edit->it3_secuencia->rel_id = 'ordplabor';

		$edit->it3_estacion = new  dropdownField('Estacion <#o#>', 'it3estacion_<#i#>');
		$edit->it3_estacion->option('','Seleccionar');
		$edit->it3_estacion->options('SELECT estacion,CONCAT(estacion,\'-\',nombre) AS lab FROM esta ORDER BY estacion');
		$edit->it3_estacion->style  = 'width:150px;';
		$edit->it3_estacion->db_name= 'estacion';
		$edit->it3_estacion->rel_id = 'ordplabor';
		$edit->it3_estacion->rule   = 'required';


		$edit->it3_actividad = new inputField('Actividad','it3actividad_<#i#>');
		$edit->it3_actividad->db_name='actividad';
		$edit->it3_actividad->rule='max_length[100]';
		$edit->it3_actividad->size =35;
		$edit->it3_actividad->maxlength =100;
		$edit->it3_actividad->rel_id = 'ordplabor';

		$edit->it3_tunidad = new dropdownField ('', 'it3tunidad_<#i#>');
		$edit->it3_tunidad->option('H','Horas');
		$edit->it3_tunidad->option('D','Dias');
		$edit->it3_tunidad->option('S','Semanas');
		$edit->it3_tunidad->style       = 'width:80px;';
		$edit->it3_tunidad->db_name     = 'tunidad';
		$edit->it3_tunidad->css_class   = 'inputnum';
		$edit->it3_tunidad->rel_id      = 'sinvplabor';
		$edit->it3_tunidad->rule        = 'enum[H,S,D]';
		$edit->it3_tunidad->insertValue = 'H';
		$edit->it3_tunidad->rel_id = 'ordplabor';

		$edit->it3_tiempo = new inputField('', 'it3tiempo_<#i#>');
		$edit->it3_tiempo->db_name      = 'tiempo';
		$edit->it3_tiempo->css_class    = 'inputnum';
		$edit->it3_tiempo->rel_id       = 'sinvplabor';
		$edit->it3_tiempo->maxlength    = 10;
		$edit->it3_tiempo->size         = 5;
		$edit->it3_tiempo->rule         = 'positive';
		$edit->it3_tiempo->autocomplete = false;
		$edit->it3_tiempo->insertValue  = 1;
		$edit->it3_tiempo->rel_id       = 'ordplabor';

		//**************************************
		//fin ordppedi
		//**************************************

		$edit->buttons('save','undo','add','back','add_rel');
		$reser=$edit->_dataobject->get('reserva');
		if($reser!='S'){
			$accion="javascript:window.location='".site_url('inventario/stra/creadordp/'.$edit->_dataobject->pk['id'].'/insert')."'";
			$edit->button_status('btn_reserva','Reservar',$accion,'TR','show');
			$edit->buttons('modify','delete');
		}

		$stat=$edit->_dataobject->get('status');
		if($stat=='C'){
			$accion="javascript:window.location='".site_url('inventario/stra/creadordpt/'.$edit->_dataobject->pk['id'].'/insert')."'";
			$edit->button_status('btn_terminar','Terminar',$accion,'TR','show');
		}

		$edit->build();

		$script= '<script type="text/javascript" >
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});
		</script>';

		$conten['form']  =& $edit;
		$data['content'] =  $this->load->view('view_ordp', $conten,true);

		$data['style']   = style('redmond/jquery-ui.css');

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('jquery-impromptu.js');
		$data['script'] .= script('plugins/jquery.blockUI.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$data['script'] .= phpscript('nformat.js');
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

	}

	function ordpbitaaction($id){
		$sel=array('a.status','fechahora');
		$this->db->select($sel);
		$this->db->from('ordpbita AS a');
		$this->db->where('a.id_ordplabor',$id);
		$this->db->orderby('a.fechahora','desc');
		$this->db->limit(1);
		$query=$this->db->get();

		$rt=array();
		if ($query->num_rows() > 0){
			$row = $query->row_array();

			if($row['status']=='I'){
				$rt['muestra']='T,P,H';
			}elseif($row['status']=='P'){
				$rt['muestra']='I,H';
			}else{
				$rt['muestra']='H';
			}
			$rt['ultimo'] = $row['fechahora'];
		}else{
			$rt['muestra']='I';
		}
		return $rt;
	}

	function ordpbitafilter($ordplabor,$idordp){
		$this->rapyd->load('datafilter','datagrid');

		function dicstatus($status){
			if($status=='I'){
				$rt='Iniciado';
			}elseif($status=='P'){
				$rt='Parado';
			}elseif($status=='T'){
				$rt='Terminado';
			}else{
				$rt=$status;
			}
			return $rt;
		}

		$filter = new DataFilter($this->titp, 'ordpbita');
		$filter->use_function('dicstatus');
		$filter->db->where('id_ordplabor',$ordplabor);

		$filter->fechahora = new dateField('Fechahora','fechahora');
		$filter->fechahora->rule      ='chfecha';
		$filter->fechahora->size      =10;
		$filter->fechahora->maxlength =8;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$action = 'javascript:window.location=\'' . site_url($this->url.'dataedit/show/'.$idordp).'\'';
		$grid->button('btn_back', 'Regresar', $action, 'TR');

		$grid->order_by('id');
		$grid->per_page = 40;

		$link1=anchor_popup('/formatos/ver/ORDPBITA/<#id#>','Comprobante',array());
		$grid->column_orderby('Fecha','<dbdate_to_human><#fechahora#>|d/m/Y H:i</dbdate_to_human>','fechahora','align="center"');
		$grid->column_orderby('Acci&oacute;n'     ,'<dicstatus><#status#></dicstatus>','status','align="center"');
		$grid->column_orderby('Observaci&oacute;n','observacion','observacion','align="left"');
		$grid->column_orderby('Registro','<dbdate_to_human><#estampa#>|d/m/Y H:i:s</dbdate_to_human>','estampa','align="center"');
		$grid->column_orderby('Usuario','usuario','usuario','align="left"');
		$grid->column('',$link1,'align="center"');

		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function ordpbita($ordplabor,$idordp,$status){
		$this->rapyd->load('dataedit');

		if($status=='I'){
			$rt='inicio';
		}elseif($status=='T'){
			$rt='culmminaci&oacute;n';
		}elseif($status=='P'){
			$rt='pausa';
		}else{
			$rt=$status;
		}

		$edit = new DataEdit("Constancia de <b>$rt</b> de la labor en la estaci&oacute;n", 'ordpbita');
		$edit->back_save   = true;
		$edit->back_cancel = true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;

		$edit->post_process('insert','_bita_post_insert');
		$edit->post_process('update','_bita_post_update');
		$edit->post_process('delete','_bita_post_delete');
		$edit->pre_process( 'insert','_bita_pre_insert');
		$edit->pre_process( 'update','_bita_pre_update');
		$edit->pre_process( 'delete','_bita_pre_delete');

		$edit->back_url = site_url($this->url.'dataedit/show/'.raencode($idordp));

		//$edit->status = new dropdownField('Estatus','status');
		//$edit->status->option('I','Iniciado' );
		//$edit->status->option('P','Pausado'  );
		//$edit->status->option('T','Terminado');
		//$edit->status->insertValue=$status;
		//$edit->status->rule='enum[I,P,T]';
		//$edit->status->style='width:100px';

		$edit->fechahora = new dateField('Fecha hora','fechahora', 'd/m/Y H:i');
		$edit->fechahora->rule='required';
		$edit->fechahora->size =18;
		$edit->fechahora->calendar=false;
		$edit->fechahora->insertValue=date('Y-m-d H:i:s');
		$edit->fechahora->maxlength =16;

		$edit->observacion = new textareaField('Observaci&oacute;n','observacion');
		$edit->observacion->rule='max_length[255]';
		$edit->observacion->cols = 70;
		$edit->observacion->rows = 4;

		$edit->id_ordplabor = new autoUpdateField('id_ordplabor',$ordplabor,$ordplabor);
		$edit->status  = new autoUpdateField('status',$status,$status);
		$edit->id_ordp = new autoUpdateField('id_ordp',$idordp,$idordp);
		$edit->estampa = new autoUpdateField('estampa',date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));
		$edit->usuario = new autoUpdateField('usuario',$this->secu->usuario(),$this->secu->usuario());

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$this->rapyd->jquery[]='$(".inputonlynum").numeric();';
		$this->rapyd->jquery[]='$.datepicker.setDefaults($.datepicker.regional[\'es\']);';
		$this->rapyd->jquery[]='$("#fechahora").datetimepicker({});';

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Bit&aacute;cora de labores');
		$this->load->view('view_ventanas', $data);
	}

	function _bita_post_insert($do){
		$ordp   = $do->get('id_ordp');

		$this->db->select(array('a.id'));
		$this->db->from('ordplabor AS a');
		$this->db->where('id_ordp', $ordp);

		$i=0;
		$query = $this->db->get();
		$contador=array('I'=>0,'P'=>0,'T'=>0);
		foreach ($query->result() as $row){
			$id_rel=$row->id;

			$this->db->select(array('a.status'));
			$this->db->from('ordpbita AS a');
			$this->db->where('a.id_ordplabor',$id_rel);
			$this->db->orderby('a.estampa','desc');
			$this->db->limit(1);

			$itquery=$this->db->get();
			if ($itquery->num_rows() > 0){
				$itrow = $itquery->row();
				$contador[$itrow->status]++;
			}
			$i++;
		}

		if($contador['I']>0){ //Esta produciento
			$this->db->where('id', $ordp);
			$this->db->update('ordp', array('status' => 'E'));
		}elseif($contador['T']==$i){ //Esta cerrada
			$this->db->where('id', $ordp);
			$this->db->update('ordp', array('status' => 'C'));
		}else{//Esta pausada
			$this->db->where('id', $ordp);
			$this->db->update('ordp', array('status' => 'P'));
		}
		return true;
	}

	function _bita_post_update($do){
		return true;
	}

	function _bita_post_delete($do){
		return true;
	}

	function _bita_pre_insert($do){
		return true;
	}

	function _bita_pre_update($do){
		return true;
	}

	function _bita_pre_delete($do){
		return true;
	}

	function _pre_insert($do){
		$numero  = $this->datasis->fprox_numero('nordp');
		$do->set('numero' ,$numero);
		$do->set('status' ,'A');
		$do->set('reserva','N');
		$usuario = $do->get('usuario');
		$estampa = $do->get('estampa');
		$hora    = $do->get('hora');
		$cana    = $do->get('cana');

		$rel='ordpitem';
		$cana=$do->count_rel($rel);
		for($i=0;$i<$cana;$i++){
			//$itcosto=$do->get_rel($rel,'costo',$i);
			//$itcana =$do->get_rel($rel,'cantidad',$i);
			$do->set_rel($rel,'numero' ,$numero  ,$i);
			$do->set_rel($rel,'usuario',$usuario ,$i);
			$do->set_rel($rel,'estampa',$estampa ,$i);
			$do->set_rel($rel,'hora'   ,$hora    ,$i);
		}

		$rel='ordplabor';
		$cana=$do->count_rel($rel);
		for($i=0;$i<$cana;$i++){
			$do->set_rel($rel,'usuario',$usuario ,$i);
			$do->set_rel($rel,'estampa',$estampa ,$i);
			$do->set_rel($rel,'hora'   ,$hora    ,$i);
		}

		$rel='ordpindi';
		$cana=$do->count_rel($rel);
		for($i=0;$i<$cana;$i++){
			$do->set_rel($rel,'usuario',$usuario ,$i);
			$do->set_rel($rel,'estampa',$estampa ,$i);
			$do->set_rel($rel,'hora'   ,$hora    ,$i);
		}
		return true;
	}

	function _pre_update($do){
		$status=$do->get('status');
		if($status!='A'){
			$do->error_message_ar['pre_upd']='No se pueden modificar ordenes que ya fueron puestas en marcha';
			return false;
		}
		$numero  = $do->get('numero');
		$usuario = $do->get('usuario');
		$estampa = $do->get('estampa');
		$hora    = $do->get('hora');
		$cana    = $do->get('cana');

		$rel='ordpitem';
		$cana=$do->count_rel($rel);
		for($i=0;$i<$cana;$i++){
			//$itcosto=$do->get_rel($rel,'costo',$i);
			//$itcana =$do->get_rel($rel,'cantidad',$i);
			$do->set_rel($rel,'numero' ,$numero  ,$i);
			$do->set_rel($rel,'usuario',$usuario ,$i);
			$do->set_rel($rel,'estampa',$estampa ,$i);
			$do->set_rel($rel,'hora'   ,$hora    ,$i);
		}

		$rel='ordplabor';
		$cana=$do->count_rel($rel);
		for($i=0;$i<$cana;$i++){
			$do->set_rel($rel,'usuario',$usuario ,$i);
			$do->set_rel($rel,'estampa',$estampa ,$i);
			$do->set_rel($rel,'hora'   ,$hora    ,$i);
		}

		$rel='ordpindi';
		$cana=$do->count_rel($rel);
		for($i=0;$i<$cana;$i++){
			$do->set_rel($rel,'usuario',$usuario ,$i);
			$do->set_rel($rel,'estampa',$estampa ,$i);
			$do->set_rel($rel,'hora'   ,$hora    ,$i);
		}
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$primary = implode(',',$do->pk);
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

	function instalar(){
		if (!$this->db->table_exists('ordp')) {
			$mSQL="CREATE TABLE `ordp` (
				`numero` VARCHAR(8) NOT NULL DEFAULT '',
				`fecha` DATE NULL DEFAULT NULL,
				`codigo` VARCHAR(15) NULL DEFAULT NULL COMMENT 'Codigo de inventario',
				`almacen` VARCHAR(4) NULL DEFAULT NULL COMMENT 'Almacen de descuento',
				`cana` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Cantidad a producir',
				`status` CHAR(2) NULL DEFAULT '0' COMMENT 'Activa, Pausada, Finalizada',
				`cliente` CHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL COMMENT 'Nombre del Cliente',
				`instrucciones` TEXT NULL,
				`reserva` CHAR(1) NULL COMMENT 'Si ya se resevo el inventario',
				`estampa` DATE NOT NULL DEFAULT '0000-00-00',
				`usuario` VARCHAR(12) NOT NULL DEFAULT '',
				`hora` VARCHAR(8) NOT NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `numero` (`numero`),
				INDEX `modificado` (`modificado`)
			)
			COMMENT='Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('reserva', 'ordp')){
			$mSQL="ALTER TABLE `ordp` ADD COLUMN `reserva` CHAR(1) NULL COMMENT 'Si ya se resevo el inventario' AFTER `instrucciones`;";
			$this->db->simple_query($mSQL);
		}

		if ($this->db->field_exists('almacen', 'ordp')){
			$mSQL="ALTER TABLE `ordp` ADD COLUMN `almacen` VARCHAR(4) NULL DEFAULT NULL COMMENT 'Almacen de descuento' AFTER `codigo`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('ordpindi')) {
			$mSQL="CREATE TABLE `ordpindi` (
				`numero` VARCHAR(8) NULL DEFAULT '',
				`codigo` VARCHAR(6) NULL DEFAULT NULL COMMENT 'Codigo de Gasto',
				`descrip` VARCHAR(40) NULL DEFAULT NULL,
				`porcentaje` DECIMAL(14,3) NULL DEFAULT '0.000',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`tipo` CHAR(1) NULL DEFAULT 'P' COMMENT 'P porcentual, M monetario',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id_ordp` INT(11) NULL DEFAULT NULL,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`),
				INDEX `numero` (`numero`)
			)
			COMMENT='Costos indirectos Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('ordpitem')) {
			$mSQL="CREATE TABLE `ordpitem` (
				`numero` VARCHAR(8) NULL DEFAULT '',
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`descrip` VARCHAR(40) NULL DEFAULT NULL,
				`cantidad` DECIMAL(14,3) NULL DEFAULT '0.000',
				`merma` DECIMAL(10,2) NULL DEFAULT '0.00',
				`costo` DECIMAL(17,2) NULL DEFAULT '0.00',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`fijo` CHAR(1) NULL DEFAULT 'N' COMMENT 'N si depende de la cantidad, S si no depende de la cantidad',
				`id_ordp` INT(11) NULL DEFAULT NULL,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`),
				INDEX `numero` (`numero`)
			)
			COMMENT='Insumos de la Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('ordpbita')) {
			$mSQL="CREATE TABLE `ordpbita` (
				`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				`id_ordplabor` INT(11) UNSIGNED NOT NULL,
				`id_ordp` INT(11) UNSIGNED NULL DEFAULT NULL,
				`fechahora` DATETIME NOT NULL,
				`status` CHAR(1) NOT NULL COMMENT 'I iniciado, P pausado, T terminado',
				`observacion` TEXT NOT NULL,
				`estampa` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`hora` VARCHAR(8) NOT NULL,
				`usuario` VARCHAR(50) NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `id_ordplabor` (`id_ordplabor`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('ordplabor')) {
			$mSQL="CREATE TABLE `ordplabor` (
				`producto` VARCHAR(15) NULL DEFAULT '' COMMENT 'Producto Terminado',
				`secuencia` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'Secuencia de las actividades',
				`estacion` VARCHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL,
				`actividad` VARCHAR(100) NOT NULL,
				`tunidad` CHAR(1) NULL DEFAULT 'H',
				`tiempo` DECIMAL(10,2) UNSIGNED NULL DEFAULT '0',
				`tiemporeal` INT(6) UNSIGNED NULL DEFAULT '0',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id_ordp` INT(11) NULL DEFAULT NULL,
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

		if ($this->db->field_exists('minutos', 'ordplabor')){
			$mSQL="ALTER TABLE `ordplabor`
			CHANGE COLUMN `minutos` `tunidad` CHAR(1) NULL DEFAULT 'H' AFTER `actividad`,
			CHANGE COLUMN `segundos` `tiempo` DECIMAL(10,2) UNSIGNED NULL DEFAULT '0' AFTER `tunidad`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('fijo', 'ordpitem')){
			$mSQL="ALTER TABLE `ordpitem` ADD COLUMN `fijo` CHAR(1) NULL DEFAULT 'N' COMMENT 'N si depende de la cantidad, S si no depende de la cantidad' AFTER `modificado`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('tipo', 'ordpindi')){
			$mSQL="ALTER TABLE `ordpindi` ADD COLUMN `tipo` CHAR(1) NULL DEFAULT 'P' COMMENT 'P porcentual, M monetario' AFTER `hora`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('ordp', 'stra')){
			$mSQL="ALTER TABLE `stra`
			ADD COLUMN `ordp` VARCHAR(8) NULL DEFAULT NULL AFTER `numere`,
			ADD COLUMN `esta` VARCHAR(5) NULL DEFAULT NULL AFTER `ordp`";
			$this->db->simple_query($mSQL);
		}
	}

}
*/
?>
