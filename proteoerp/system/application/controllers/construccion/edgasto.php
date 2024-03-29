<?php
/**
* ProteoERP
*
* @autor    Andres Hocevar
* @license  GNU GPL v3
*/
class Edgasto extends Controller {
	var $mModulo = 'EDGASTO';
	var $titp    = 'GASTOS DE CONDOMINIO';
	var $tits    = 'GASTOS DE CONDOMINIO';
	var $url     = 'construccion/edgasto/';

	function Edgasto(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'EDGASTO', $ventana=0 );
	}

	function index(){
		//$this->datasis->creaintramenu(array('modulo'=>'000','titulo'=>'<#titulo#>','mensaje'=>'<#mensaje#>','panel'=>'<#panal#>','ejecutar'=>'<#ejecuta#>','target'=>'popu','visible'=>'S','pertenece'=>'<#pertenece#>','ancho'=>900,'alto'=>600));
		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//******************************************************************
	// Layout en la Ventana
	//
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname']);

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"gmedidor",  "img"=>"images/engrana.png",  "alt" => "Gastos por Medidor", "label"=>"Gastos por Medidor"));
		$grid->wbotonadd(array("id"=>"edtraegas", "img"=>"images/engrana.png",  "alt" => "Traer Gastos", "label"=>"Traer Gastos"));
		$WestPanel = $grid->deploywestp();

		$adic = array(
			array('id'=>'fedita',  'title'=>'Agregar/Editar Registro'),
			array('id'=>'fshow' ,  'title'=>'Mostrar Registro'),
			array('id'=>'fborra',  'title'=>'Eliminar Registro'),
			array('id'=>'fmedidor','title'=>'Gastos por Medidor')
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel'] = $EastPanel;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('EDGASTO', 'JQ');
		$param['otros']       = $this->datasis->otros('EDGASTO', 'JQ');
		$param['temas']       = array('proteo','darkness','anexos1');
		$param['bodyscript']  = $bodyscript;
		$param['tabs']        = false;
		$param['encabeza']    = $this->titp;
		$param['tamano']      = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);
	}

	//******************************************************************
	// Funciones de los Botones
	//
	function bodyscript( $grid0 ){
		$bodyscript = '<script type="text/javascript">';
		$ngrid = '#newapi'.$grid0;

		$ano1 = date('Y',mktime(0,0,0,date('m'),date('d'),date('Y')));
		$ano2 = date('Y',mktime(0,0,0,date('m'),date('d'),date('Y')-1));
		$ano3 = date('Y',mktime(0,0,0,date('m'),date('d'),date('Y')-2));
		$mano = '<select id=\'mano\' name=\'mano\'><option value=\''.$ano1.'\'>'.$ano1.'</option><option value=\''.$ano2.'\'>'.$ano2.'</option><option value=\''.$ano3.'\'>'.$ano3.'</option></select>';
		$mes  = '<select id=\'mmes\' name=\'mmes\'><option value=\'01\'>01</option><option value=\'02\'>02</option><option value=\'03\'>03</option><option value=\'04\'>04</option><option value=\'05\'>05</option><option value=\'06\'>06</option><option value=\'07\'>07</option><option value=\'08\'>08</option><option value=\'09\'>09</option><option value=\'10\'>10</option><option value=\'11\'>11</option><option value=\'12\'>12</option></select>';

		$bodyscript .= '
		$("#edtraegas").click(function(){
			var meco = "";
			var mgene = {
			state0: {
				html:"<h1>Traer Gastos: </h1><br/><center>Fecha: '.$mano.'&nbsp; Mes: '.$mes.'</center><br/>",
				buttons: { Cancelar: false, Aceptar: true },
				focus: 1,
				submit:function(e,v,m,f){
					if(v){
						e.preventDefault();
						$.ajax({
							url: \''.site_url('construccion/edgasto/edtraegas').'\',
							global: false,
							type: "POST",
							data: ({ anomes : encodeURIComponent(f.mano+f.mmes) }),
							dataType: "text",
							async: false,
							success: function(sino) {
								meco = " sino="+sino;
								if (sino.substring(0,1)=="S"){
									$.prompt.goToState("state1");
								} else {
									$.prompt.close();
								}
							},
							error: function(h,t,e) { alert("Error.. ",e) }
						});
						return false;
					}
				}
			},
			state1: {
				html:"Gastos transferidos!"+meco,
				buttons: { Regresar: -1, Salir: 0 },
				focus: 1,
				submit:function(e,v,m,f){
					e.preventDefault();
					if(v==0)
						$.prompt.close();
					else if(v==-1)
						$.prompt.goToState("state0");
				}
			}
			};
			$.prompt(mgene);
		});';


		$mSQL  = 'SELECT id, descrip FROM edgrupo WHERE activo="S" ORDER BY descrip'; 
		$grupo = $this->datasis->llenaopciones($mSQL, true, 'mgrupo');
		$grupo = str_replace('"',"'",$grupo);

		$bodyscript .= '
		$("#gmedidor").click(function(){
			$.prompt("<center>Fecha: '.$mano.'&nbsp; Mes: '.$mes.' <b> Medidor: </b> <input type=\'checkbox\' id=\'ali\' name=\'ali\' value=\'S\'><br/><b>Grupo: </b>'.$grupo.' </center>",{
				buttons: { Aceptar: 1, Salir: 0},
				submit: function(e,v,m,f){
					if ( v == 1 ){
						if ( !f.ali ) { f.ali="N" };
						$.post("'.site_url($this->url.'gfmedidor').'",{ anomes : encodeURIComponent(f.mano+f.mmes), grupo: f.mgrupo, medidor: f.ali },
						function(data){
							$("#fedita").dialog( {height: 500, width: 620, title: "Cargo por Medidor"} );
							$("#fedita").html(data);
							$("#fedita").dialog( "open" );
						})
					}
				}
			});
		});
		';
		
		$bodyscript .= '
		function quitagasto(idgasto){
			$.prompt("<h1>Eliminar Distribucion de Gasto?</h1>",{
				buttons: { Aceptar: 1, Salir: 0},
				submit: function(e,v,m,f){
					if ( v == 1 ){
						$.post("'.site_url($this->url.'quitamed').'",{ id : idgasto },
						function(data){
							alert(data);
						})
					}
				}
			});
		};
		';

		$bodyscript .= '
		function gcargarec(idgr){
			$.prompt("<h1>Cargar gasto a Recibos?</h1>",{
				buttons: { Cargar: 1, Salir: 0},
				submit: function(e,v,m,f){
					if ( v == 1 ){
						$.post("'.site_url($this->url.'gcargarec').'",{ id: idgr },
						function(data){
							alert(data);
						})
					}
				}
			});
		
		};
		';

		$bodyscript .= $this->jqdatagrid->bsshow('edgasto', $ngrid, $this->url );
		$bodyscript .= $this->jqdatagrid->bsadd( 'edgasto', $this->url );
		$bodyscript .= $this->jqdatagrid->bsdel( 'edgasto', $ngrid, $this->url );
		$bodyscript .= $this->jqdatagrid->bsedit('edgasto', $ngrid, $this->url );

		//Wraper de javascript
		$bodyscript .= $this->jqdatagrid->bswrapper($ngrid);

		$bodyscript .= $this->jqdatagrid->bsfedita( $ngrid, '350', '500' );
		$bodyscript .= $this->jqdatagrid->bsfshow( '300', '400' );
		$bodyscript .= $this->jqdatagrid->bsfborra( $ngrid, '300', '400' );

		$bodyscript .= '});';

		$bodyscript .= '</script>';

		return $bodyscript;
	}

	//******************************************************************
	//  Trae Gastos
	//
	function edtraegas($anomes = 0){
		if ( $anomes == 0 ) $anomes = $this->input->post('anomes');
		if ( $anomes <= 0  ) {
			echo 'No se Guardo '.$anomes;
			return false;
		}
		$dbanomes = $this->db->escape($anomes);
		//Genera los recibos
		$mSQL = "CALL sp_edgasto(${dbanomes})";
		$this->db->query($mSQL);
		echo "Si Ejecutado CALL sp_edgasto(${dbanomes})";

	}


	//******************************************************************
	// Definicion del Grid o Tabla 
	//
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'hidden'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->addField('aplicacion');
		$grid->label('Aplic');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));

		$grid->addField('tipo_doc');
		$grid->label('Tipo');
		$grid->params(array(
			'search'        => 'true',
			'align'         => "'center'",
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:2, maxlength: 2 }',
		));


		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
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


		$grid->addField('causado');
		$grid->label('Causado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('proveed');
		$grid->label('Proveed');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('detalle');
		$grid->label('Detalle');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:2, cols:60}'",
		));


		$grid->addField('base');
		$grid->label('Base');
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


		$grid->addField('total');
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

		$grid->addField('partida');
		$grid->label('Partida');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 40,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 0 }'
		));

		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'align'         => "'center'",
			'width'         => 50,
			'edittype'      => "'textarea'",
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
				$.ajax({
					url: "'.site_url('construccion/edgasto').'/gpendiente",
					success: function(msg){
						$("#ladicional").html(msg);
					}
				});
			}}
		');


		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		$grid->setOndblClickRow('');		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('EDGASTO','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('EDGASTO','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('EDGASTO','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('EDGASTO','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("addfunc: edgastoadd, editfunc: edgastoedit, delfunc: edgastodel, viewfunc: edgastoshow");

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

	//******************************************************************
	// Busca la data en el Servidor por json
	//
	function getdata(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('edgasto');

		$response   = $grid->getData('edgasto', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	//******************************************************************
	// Guarda la Informacion del Grid o Tabla
	//
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
				$check = $this->datasis->dameval("SELECT count(*) FROM edgasto WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('edgasto', $data);
					echo "Registro Agregado";
					logusu('EDGASTO',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM edgasto WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM edgasto WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE edgasto SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("edgasto", $data);
				logusu('EDGASTO',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('edgasto', $data);
				logusu('EDGASTO',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM edgasto WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM edgasto WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->query("DELETE FROM edgasto WHERE id=$id ");
				logusu('EDGASTO',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	//******************************************************************
	// Edicion 
	function dataedit(){
		$this->rapyd->load('dataedit');
		$script = '
		$(function() {
			$("#fecha").datepicker(  {dateFormat:"dd/mm/yy"});
			$("#causado").datepicker({dateFormat:"dd/mm/yy"});
			$(".inputnum").numeric(".");
		});
		';

		$script .= '
		$("#proveed").autocomplete({
			delay: 600,
			autoFocus: true,
			source: function( req, add){
			$.ajax({
				url: "'.site_url('ajax/buscasprv').'",
				type: "POST",
				dataType: "json",
				data: {"q":req.term},
				success:
					function(data){
						var sugiere = [];
						if(data.length==0){
							$("#proveedor").val("");
							$("#proveedor_val").text("");
							$("#proveed").val("");
							$("#sprvreteiva").val("75");
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
				$("#proveed").attr("readonly", "readonly");
				$("#proveedor").val(ui.item.nombre);
				$("#rif").val(ui.item.rif);
				
				setTimeout(function(){ $("#proveed").removeAttr("readonly"); }, 1500);
				$("#serie").change();
				//ajaxsanncprov();
			}
		});
		';

		$script .= '
		$("#base").keyup(function(){
			totaliza();
		})

		$("#iva").keyup(function(){
			totaliza();
		})

		function totaliza(){
			var total = 0;
			var iva   = 0;
			var base  = 0;
			base = Number($("#base").val());
			if ( $("#iva").val() == "" ){
				$("#iva").val( 0 ); 
				$("#iva_val").text(nformat(0,2));
				
			}
			total = base + Number($("#iva").val());
			$("#total").val(roundNumber(total,2));
			$("#total_val").text(nformat(total,2));
		}
		';

		$edit = new DataEdit('', 'edgasto');

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

		$edit->aplicacion = new dropdownField('Aplicacion','aplicacion');
		$edit->aplicacion->options('SELECT depto, CONCAT(depto," ",descrip) descrip FROM dpto WHERE tipo="G" AND depto<>"GP" ORDER BY depto');
		$edit->aplicacion->rule = 'required';
		$edit->aplicacion->style='width:150px;';

		$edit->tipo_doc = new dropdownField('Tipo', 'tipo_doc');
		$edit->tipo_doc->option('FC','Factura');
		$edit->tipo_doc->option('RB','Recibo de Pago');
		$edit->tipo_doc->option('OT','Otro');
		$edit->tipo_doc->rule = 'required';
		$edit->tipo_doc->style='width:150px;';

		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule      = '';
		$edit->numero->size      = 15;
		$edit->numero->maxlength = 20;
		$edit->numero->rule      = 'required';

		$edit->fecha = new dateonlyField('Fecha','fecha');
		$edit->fecha->rule        = 'chfecha';
		$edit->fecha->calendar    = false;
		$edit->fecha->size        = 10;
		$edit->fecha->maxlength   = 8;
		$edit->fecha->insertValue = date('Y-m-d');

		$edit->causado = new dateonlyField('F. Causado','causado');
		$edit->causado->rule      = 'chfecha';
		$edit->causado->calendar  = false;
		$edit->causado->size      = 10;
		$edit->causado->maxlength = 8;
		$edit->causado->insertValue = date('Y-m-d');

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->size     = 10;
		$edit->proveed->autocomplete=false;

		$edit->partida = new  dropdownField('Partida', 'partida');
		$edit->partida->options('SELECT id, CONCAT(codigo," ",descrip) descrip FROM mgas ORDER BY descrip');
		$edit->partida->rule = 'required';
		$edit->partida->style='width:250px;';

		$edit->detalle = new textareaField('Detalle','detalle');
		$edit->detalle->rule='';
		$edit->detalle->cols = 50;
		$edit->detalle->rows = 2;

		$edit->base = new inputField('Base','base');
		$edit->base->rule='numeric';
		$edit->base->css_class='inputnum';
		$edit->base->size =12;
		$edit->base->maxlength =10;

		$edit->iva = new inputField('Iva','iva');
		$edit->iva->rule='numeric';
		$edit->iva->css_class='inputnum';
		$edit->iva->size =12;
		$edit->iva->maxlength =10;

		$edit->total = new inputField('Total','total');
		$edit->total->rule='numeric';
		$edit->total->css_class='inputnum';
		$edit->total->size =12;
		$edit->total->maxlength =10;
		$edit->total->readonly  = true;

		$edit->rif = new inputField('RIF','rif');
		$edit->rif->rule='';
		$edit->rif->size =13;
		$edit->rif->maxlength =13;

		$edit->proveedor = new inputField('Nombre','proveedor');
		$edit->proveedor->rule='';
		$edit->proveedor->size =42;
		$edit->proveedor->maxlength =80;

		$edit->tipo = new hiddenField('Tipo','tipo');
		$edit->tipo->insertValue = 'M';
		$edit->tipo->updateValue = 'M';

		//$edit->tipo = new autoUpdateField('Tipo','M', 'M');


		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			$conten['form']  =&  $edit;
			$data['content']  =  $this->load->view('view_edgasto', $conten, false);
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

	//******************************************************************
	// Carga la vaina
	//
	function gcargarec(){
		$id    = intval($this->input->post('id'));
		$reg   = $this->datasis->damereg("SELECT grupo, gasto, EXTRACT(YEAR_MONTH FROM fecha) anomes FROM edgasmed WHERE id=".$id);
		$cargo = $this->datasis->dameval("SELECT cargo FROM edgrupo WHERE id=".$reg['grupo']);
		
		$ides  = $this->datasis->dameval('SELECT GROUP_CONCAT(id) FROM gitser WHERE EXTRACT(YEAR_MONTH FROM fecha)='.$reg['anomes'].' AND gcargo = '.$cargo.' ');

		$mSQL = '
		INSERT INTO edgasto (aplicacion,tipo_doc,numero,fecha,causado,proveed,partida,detalle,base,iva,total,rif,proveedor)
		SELECT 	a.departa aplicacion, b.tipo_doc, a.numero, b.ffactura fecha, b.fecha causado, 
				a.proveed, c.id partida, a.descrip detalle, a.precio base, a.iva, a.importe total, 
				IF(a.rif="" OR a.rif IS NULL, d.rif, a.rif) rif, 
				IF(a.rif="" OR a.rif IS NULL, d.nombre, a.proveedor) proveedor
		FROM gitser a 
		JOIN gser   b ON a.transac=b.transac AND a.fecha=b.fecha AND a.proveed=b.proveed
		JOIN mgas   c ON a.codigo=c.codigo
		JOIN sprv   d ON a.proveed=d.proveed
		WHERE a.id IN ('.$ides.')';

		echo $mSQL; 
	}

	//******************************************************************
	// Elimina los gastos pendientes
	//
	function quitamed(){
		$id = intval($this->input->post('id'));
		$meco = 'Registros eliminados';
		$reg = $this->datasis->damereg('SELECT grupo, gasto FROM edgasmed WHERE id='.$id);
		if ( $reg )
			$this->db->delete('edgasmed', array('grupo' => $reg['grupo'], 'gasto' => $reg['gasto'] )); 
		
		echo 'Gasto Eliminado';
	}


	//******************************************************************
	// Muestra los gastos pendientes
	//
	function gpendiente(){
		$meco = '';
		$mSQL = '
		SELECT c.descrip grupo, b.descrip gasto, sum(a.monto) monto, a.id
		FROM edgasmed a
		JOIN gitser   b ON a.gasto=b.id 
		JOIN edgrupo  c ON a.grupo = c.id
		WHERE a.status="P"
		GROUP BY a.grupo, a.gasto';
		$query=$this->db->query($mSQL);
		if($query->num_rows() > 0){
			$meco  = '<table width="100%" bgcolor="#F3D669">';
			$meco .= '<tr bgcolor="#008000"><th colspan="3" style="color:#FFFFFF;">Gastos Pendientes</th>  </tr>';
			foreach($query->result() AS $row){
				$meco .= '<tr><td><div onclick="gcargarec(\''.$row->id.'\')" id="gcarga">'.$row->grupo.'</div></td><td>'.$row->monto.'</td><td>';
				$meco .= '<div><a onclick="quitagasto(\''.$row->id.'\')">'.img(array('src'=>"images/elimina4.png", 'height'=> 15, 'alt'=>'Elimina el cliente de la ruta', 'title'=>'Elimina el cliente de la ruta', 'border'=>'0'))."</a></div></td></tr>\n";
				$meco .= '<tr><td colspan="3">'.$row->gasto."</td></tr>\n";
			}
			$meco .= '</table>';
		}
		echo $meco;
	}




	//******************************************************************
	// Medidor
	//
	function gfmedidor(){
		$grupo   = intval($_POST['grupo']);
		$anomes  = intval($_POST['anomes']);
		$medidor = intval($_POST['medidor']);
		
		$nomgru = $this->datasis->dameval("SELECT descrip FROM edgrupo WHERE id=${grupo} AND activo='S'");
		$gcargo = $this->datasis->dameval("SELECT cargo   FROM edgrupo WHERE id=${grupo} AND activo='S'");
	
		$this->rapyd->load('dataform');  
		$edit = new DataForm("construccion/edgasto/gfmedidor/process");  

		$query  = $this->db->query('SELECT MAX(id) id, sum(importe) monto FROM gitser WHERE EXTRACT(YEAR_MONTH FROM fecha)='.$anomes.' AND gcargo = '.$gcargo);
		$montos = array();
		
		foreach ($query->result() AS $row ){
			$monto[$row->id] = $row->monto;
		}
	
		$script = '
		var montocargo = '.json_encode($monto).';
		$(function() {
			$("#fecha").datepicker({dateFormat:"dd/mm/yy"});
			$("#vence").datepicker({dateFormat:"dd/mm/yy"});
			$(".inputnum").numeric(".");
		});
		
		$("#gasto").change(function(){
			//alert( montocargo[$("#gasto").val()] );
			totaliza();
		});

		function totaliza(){
			var montocal = 0;
			var lectucal = 0;
			$("input[name^=\'monto_\']").each( 
			function( index ){
				montocal += Number($(this).val());
			})
			$("input[name^=\'lectura_\']").each( 
			function( index ){
				lectucal += Number($(this).val());
			})
			//$("#sumamont").html(montocargo[$("#gasto").val()] );
			$("#sumamonto").html(montocal.toFixed(2));
			$("#sumalectu").html(lectucal.toFixed(2));
		}

		function calcular(){
			var montocal = 0;
			var lectucal = 0;
			var meco     = 0;
			var mtotal   = montocargo[$("#gasto").val()];
			// Calcula el Total
			$("input[name^=\'lectura_\']").each( 
			function( index ){
				lectucal += Number($(this).val());
			})
			// Coloca cada monto
			$("input[name^=\'lectura_\']").each( 
			function( index ){
				meco = Number($(this).val());
				meco = meco*mtotal/lectucal;
				$("#monto_"+index).val(meco.toFixed(2));
			})
			totaliza();
		}
		
		';

		$edit->script($script);

		$edit->grupo = new hiddenField('Grupo','grupo');
		$edit->grupo->insertValue = $grupo;
		$edit->grupo->type        = 'inputhidden';

		$edit->anomes = new dropDownField('Ano Mes','anomes');
		$edit->anomes->insertValue = $anomes;
		$edit->anomes->type='inputhidden';

		$edit->gasto = new dropDownField('Gasto','gasto');
		$edit->gasto->option('', 'Seleccione un gasto');
		$edit->gasto->options('SELECT MAX(id) id, CONCAT(descrip," ", sum(importe)) FROM gitser WHERE EXTRACT(YEAR_MONTH FROM fecha)='.$anomes.' AND gcargo = '.$gcargo);

		$mSQL = "
		SELECT b.codigo, b.descripcion, a.inmueble 
		FROM editgrupo a 
		JOIN edinmue   b ON a.inmueble = b.id
		WHERE grupo = ${grupo}
		ORDER BY b.codigo";
		$query = $this->db->query($mSQL);
		$i = 0;
		foreach ($query->result() as $row){
			$obj = "inmueble_".$i;
			$edit->$obj = new hiddenField('Inmueble '.$i,'inmueble_'.$i);
			$edit->$obj->rule      = 'integer';
			$edit->$obj->css_class = 'inputonlynum';
			$edit->$obj->size      = 13;
			$edit->$obj->maxlength = 11;
			$edit->$obj->insertValue = $row->inmueble;

			$obj = "descrip_".$i;
			$edit->$obj = new inputField('descrip '.$i,'descrip_'.$i);
			$edit->$obj->size      = 13;
			$edit->$obj->maxlength = 11;
			$edit->$obj->insertValue = $row->descripcion;
			$edit->$obj->type='inputhidden';

			$alicuota = 0.00;
			if ( $medidor != 'S' ){
				$mSQL = "
				SELECT alicuota 
				FROM edalicuota 
				WHERE inmueble=".$row->inmueble." AND EXTRACT(YEAR_MONTH FROM fecha) <= ${anomes}
				ORDER BY fecha DESC 
				LIMIT 1
				";
				$alicuota = $this->datasis->dameval($mSQL)+0;
			}

			$obj = "lectura_".$i;
			$edit->$obj = new inputField('Lectura '.$i,'lectura_'.$i);
			$edit->$obj->rule      = 'numeric';
			$edit->$obj->css_class = 'inputnum';
			$edit->$obj->size      = 12;
			$edit->$obj->maxlength = 10;
			$edit->$obj->onkeyup  = 'totaliza()';
			$edit->$obj->insertValue = $alicuota;

			$obj = "monto_".$i;
			$edit->$obj = new inputField('Monto','monto_'.$i);
			$edit->$obj->rule      = 'numeric';
			$edit->$obj->css_class = 'inputnum';
			$edit->$obj->size      = 12;
			$edit->$obj->maxlength = 10;
			$edit->$obj->onkeyup  = 'totaliza()';
			$i++;
		}
		//$i--;
		$edit->fecha = new dateonlyField('Fecha','fecha');
		$edit->fecha->rule      = 'chfecha';
		$edit->fecha->calendar  = false;
		$edit->fecha->size      = 10;
		$edit->fecha->maxlength = 8;

		$edit->longi = new dropDownField('Longitud','longi');
		$edit->longi->insertValue = $i;
		$edit->longi->type='inputhidden';

/*
		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
*/

		$edit->build_form();

		if($edit->on_success()){
			// Guarda la vaina
			$longi   = $this->input->post('longi');
			$anomes  = $this->input->post('anomes');
			$gasto   = $this->input->post('gasto');
			$grupo   = $this->input->post('grupo');

			$this->db->delete('edgasmed',array('grupo'=>$grupo,'fecha'=>$anomes.'01','gasto'=>$gasto));
			
			for ( $i=0; $i < $longi; $i++ ){
				$data = array();
				$data['fecha']    = $anomes.'01';
				$data['grupo']    = $grupo;
				$data['gasto']    = $gasto;
				$data['inmueble'] = $this->input->post('inmueble_'.$i);
				$data['lectura']  = $this->input->post('lectura_'.$i);
				$data['monto']    = $this->input->post('monto_'.$i);
				$this->db->insert('edgasmed',$data);
			}
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>''
			);
			echo json_encode($rt);
		} else {
			//echo $edit->output;
			$conten['form']    =&  $edit;
			$conten['grupo']  = $grupo;
			$conten['nomgru'] = $nomgru;
			$conten['longi']  = $i;
			$this->load->view('view_edmedidor', $conten);
		}
	}


	function instalar(){
		if (!$this->db->table_exists('edgasto')) {
			$mSQL="CREATE TABLE `edgasto` (
			  id       int(11)  NOT NULL AUTO_INCREMENT,
			  tipo_doc char(2)  NOT NULL DEFAULT 'FC',
			  numero   char(20) DEFAULT NULL,
			  fecha    date     DEFAULT NULL,
			  causado  date     DEFAULT NULL,
			  proveed  char(5)  DEFAULT NULL,
			  partida  int(11)  DEFAULT '0',
			  detalle  text,
			  base     decimal(10,2) DEFAULT '0.00',
			  iva      decimal(10,2) DEFAULT '0.00',
			  total    decimal(10,2) DEFAULT '0.00',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `numero` (`numero`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC COMMENT='Gastos de Condominio'";
			$this->db->query($mSQL);
		}

		if (!$this->db->table_exists('edgasmed')) {
			$mSQL="
			CREATE TABLE edgasmed (
				id       INT(11) NOT   NULL AUTO_INCREMENT,
				grupo    INT(11) NOT   NULL DEFAULT '0',
				gasto    INT(11)       NULL DEFAULT NULL,
				inmueble INT(11)       NULL DEFAULT NULL,
				lectura  VARCHAR(20)   NULL DEFAULT NULL,
				monto    DECIMAL(10,2) NULL DEFAULT NULL,
				fecha    DATE          NULL DEFAULT NULL,
				status   CHAR(1) NULL DEFAULT 'P',
				PRIMARY  KEY (id)
			)
			COMMENT='Gastos por medidor' CHARSET=latin1 ENGINE=MyISAM ROW_FORMAT=DYNAMIC";
			$this->db->query($mSQL);
		}
	}
}

?>
