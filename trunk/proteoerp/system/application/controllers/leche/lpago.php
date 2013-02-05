<?php
class Lpago extends Controller {
	var $mModulo = 'LPAGO';
	var $titp    = 'Modulo de pagos';
	var $tits    = 'Modulo de pagos';
	var $url     = 'leche/lpago/';
	var $table   = 'lpago';

	function Lpago(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'LPAGO', $ventana=0 );
	}

	function index(){
		$this->instalar();
		/*if ( !$this->datasis->iscampo('lpago','id') ) {
			$this->db->simple_query('ALTER TABLE lpago DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE lpago ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE lpago ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/

		$this->datasis->creaintramenu(array('modulo'=>'227','titulo'=>'Pagos de Producción','mensaje'=>'Pagos de Producción','panel'=>'LECHE','ejecutar'=>'leche/lpago','target'=>'popu','visible'=>'S','pertenece'=>'2','ancho'=>900,'alto'=>600));
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

		//Botones Panel Izq
		$grid->wbotonadd(array('id'=>'bimpri', 'img'=>'assets/default/images/print.png', 'alt' => 'Imprimir Documento', 'label'=>'Imprimir recibo' ));
		$WestPanel = $grid->deploywestp();

		$adic = array(
			array('id'=>'fedita', 'title'=>'Agregar/Editar Registro'),
			array('id'=>'fshow' , 'title'=>'Ver Registro')
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel'] = $EastPanel;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('LPAGO', 'JQ');
		$param['otros']       = $this->datasis->otros('LPAGO', 'JQ');
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
		function lpagoadd() {
			$.post("'.site_url($this->url.'dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function lpagoedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url($this->url.'dataedit/modify').'/"+id, function(data){
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
		jQuery("#bimpri").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				'.$this->datasis->jwinopen(site_url('formatos/ver/LPAGO').'/\'+id+\'/id\'').';
			} else {
				$.prompt("<h1>Por favor Seleccione un registro</h1>");
			}
		});';

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
								'.$this->datasis->jwinopen(site_url('formatos/ver/LPAGO').'/\'+res.id+\'/id\'').';
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

		$bodyscript .= "</script>";

		return $bodyscript;
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		/*$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));*/


		$grid->addField('numero');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
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


		$grid->addField('proveed');
		$grid->label('Proveed');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'text'",
			'formatoptions' => '{size:10, maxlength: 5 }'
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:100, maxlength: 100 }',
		));


		$grid->addField('banco');
		$grid->label('Banco');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));


		$grid->addField('numche');
		$grid->label('N.Cheque');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:100, maxlength: 100 }',
		));


		$grid->addField('monto');
		$grid->label('Monto');
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


		$grid->addField('deduc');
		$grid->label('Deducciones');
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


		$grid->addField('montopago');
		$grid->label('Monto final');
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


		$grid->addField('benefi');
		$grid->label('Beneficiario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:100, maxlength: 100 }',
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
		$grid->setAdd(    $this->datasis->sidapuede('LPAGO','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('LPAGO','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('LPAGO','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('LPAGO','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions('addfunc: lpagoadd,teditfunc: lpagoedit');

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
		$mWHERE = $grid->geneTopWhere('lpago');

		$response   = $grid->getData('lpago', array(array()), array(), false, $mWHERE );
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
				$check = $this->datasis->dameval("SELECT count(*) FROM lpago WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('lpago', $data);
					echo "Registro Agregado";

					logusu('LPAGO',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM lpago WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM lpago WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE lpago SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("lpago", $data);
				logusu('LPAGO',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('lpago', $data);
				logusu('LPAGO',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM lpago WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM lpago WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM lpago WHERE id=$id ");
				logusu('LPAGO',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$script="
		$(function(){
			$('.inputnum').numeric('.');
			$('#fecha').datepicker({   dateFormat: 'dd/mm/yy' });
			$('#proveed').autocomplete({
				source: function( req, add){
					$.ajax({
						url:  '".site_url('ajax/buscasprv')."',
						type: 'POST',
						dataType: 'json',
						data: 'q='+req.term,
						success:
							function(data){
								if(data.length==0){
									$('#nombre').val('');
									$('#nombre_val').text('');
									$('#proveed').val('');
								}else{
									var sugiere = [];
									$.each(data,
										function(i, val){
											sugiere.push( val );
										}
									);
									add(sugiere);
								}
							},
					})
				},
				minLength: 2,
				select: function( event, ui ) {
					$('#proveed').attr('readonly', 'readonly');
					$('#nombre').val(ui.item.nombre);
					$('#nombre_val').text(ui.item.nombre);
					$('#proveed').val(ui.item.proveed);
					setTimeout(function() { $('#proveed').removeAttr('readonly'); }, 1500);

					$.ajax({
						url:  '".site_url($this->url.'ajaxmonto')."',
						type: 'POST',
						dataType: 'json',
						data: 'proveed='+ui.item.proveed,
						success:
							function(ddata){
								var monto = roundNumber(ddata.monto+ddata.tmonto,2);
								var diff  = roundNumber(monto-ddata.deduc,2);
								$('#monto').val(monto);
								$('#deduc').val(ddata.deduc);

								$('#montopago').val(diff);
								$('#montopago_val').text(nformat(diff,2));
							},
					});
				}
			});
		});

		function totaliza(){
			var monto = Number($('#monto').val());
			var deduc = Number($('#deduc').val());
			var diff  = roundNumber(monto-deduc,2);

			$('#montopago').val(diff);
			$('#montopago_val').text(nformat(diff,2));
		}
		";

		$edit = new DataEdit($this->tits, 'lpago');
		$edit->script($script,'create');
		$edit->script($script,'modify');

		$edit->on_save_redirect=false;

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->proveed = new inputField('Proveedor','proveed');
		$edit->proveed->rule='max_length[5]|required';
		$edit->proveed->size =7;

		/*$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;*/

		/*$edit->tipo = new inputField('Tipo','tipo');
		$edit->tipo->rule='max_length[1]';
		$edit->tipo->size =3;
		$edit->tipo->maxlength =1;*/

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->insertValue=date('Y-m-d');
		$edit->fecha->calendar=false;
		$edit->fecha->maxlength =8;

		$edit->nombre = new inputField('','nombre');
		$edit->nombre->rule='max_length[100]';
		$edit->nombre->type='inputhidden';
		$edit->nombre->in  ='proveed';

		$edit->banco = new dropdownField('Pagar desde','banco');
		$edit->banco->option('','Seleccionar');
		$edit->banco->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE activo='S' AND tipocta<>'Q' ORDER BY codbanc");
		//$edit->banco->onchange='desactivacampo(this.value)';
		$edit->banco->rule='max_length[50]|required';
		$edit->banco->group='Detalles de pago';

		$edit->numche = new inputField('N&uacute;mero','numche');
		$edit->numche->rule='max_length[100]';
		$edit->numche->rule='condi_required|callback_chobligaban';
		$edit->numche->size =52;
		$edit->numche->maxlength =100;
		$edit->numche->group='Detalles de pago';
		//$edit->numche->append('Aplica si repone desde un Banco');

		$edit->benefi = new inputField('Beneficiario','benefi');
		$edit->benefi->rule='max_length[100]|strtoupper';
		$edit->benefi->size =52;
		$edit->benefi->maxlength =100;
		$edit->benefi->group='Detalles de pago';

		$edit->monto = new inputField('Monto','monto');
		$edit->monto->rule='max_length[12]|numeric|mayorcero';
		$edit->monto->css_class='inputnum';
		$edit->monto->size =14;
		$edit->monto->onkeyup='totaliza()';
		$edit->monto->maxlength =12;

		$edit->deduc = new inputField('Deducciones','deduc');
		$edit->deduc->rule='max_length[12]|numeric';
		$edit->deduc->css_class='inputnum';
		$edit->deduc->onkeyup='totaliza()';
		$edit->deduc->size =14;
		$edit->deduc->maxlength =12;

		$edit->montopago = new inputField('Monto del pago','montopago');
		$edit->montopago->rule='max_length[12]|numeric|mayorcero';
		$edit->montopago->css_class='inputnum';
		$edit->montopago->size =14;
		$edit->montopago->maxlength =12;

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

	function chobligaban($val){
		$banc=$this->input->post('banco');
		$sql='SELECT tbanco FROM banc WHERE codbanc='.$this->db->escape($banc);
		$tipo=$this->datasis->dameval($sql);

		if($tipo=='CAJ'){
			return true;
		}elseif(empty($val)){
			$this->validation->set_message('chobligaban', 'El campo %s se necesario cuando se paga por banco.');
			return false;
		}else{
			return true;
		}
	}

	function ajaxmonto(){
		$proveed=$this->input->post('proveed');

		if($proveed!==false){
			$this->db->_escape_char='';
			$this->db->_protect_identifiers=false;
			//$this->fcorte = date('Y-m-d',mktime(0, 0, 0, date('n'),date('j')-1*date('w')));
			$fcorte = date('Y-m-d',mktime(0, 0, 0, date('n'),date('j')-1*date('w')));

			$rt = array();
			//Deducciones
			$rt['deduc'] = 0;
			$sel=array('SUM(a.total) AS val');
			$this->db->select($sel);
			$this->db->from('lgasto AS a');
			$this->db->where('(a.pago IS NULL OR a.pago=0)');
			$this->db->where('a.proveed',$proveed);
			$this->db->where('a.fecha <=',$fcorte);
			$query = $this->db->get();
			if ($query->num_rows() > 0){
				$row = $query->row();
				if(!empty($row->val)) $rt['deduc'] = round(floatval($row->val),2);
			}

			//Productores
			$rt['monto'] = 0;

			$sel=array('SUM(ROUND(a.lista*if(c.tipolec="F",k.ultimo,e.ultimo),2)+ROUND(a.lista*(f.ultimo+g.ultimo+h.ultimo)*(c.tipolec="F")+ROUND(a.lista*IF(c.animal="B",if(c.tipolec="F",i.ultimo,j.ultimo), 0 ),2),2))  AS total');
			$this->db->select($sel);
			$this->db->from('itlrece AS a');
			$this->db->join('lrece   AS b','a.id_lrece=b.id');
			$this->db->join('lvaca   AS c','a.id_lvaca=c.id');
			$this->db->join('sprv    AS d','c.codprv=d.proveed'   ,'LEFT');
			$this->db->join('sinv    AS e','e.codigo="ZLCALIENTE"','LEFT');
			$this->db->join('sinv    AS f','f.codigo="ZMANFRIO"'  ,'LEFT');
			$this->db->join('sinv    AS g','g.codigo="ZPGRASA"'   ,'LEFT');
			$this->db->join('sinv    AS h','h.codigo="ZBACTE"'    ,'LEFT');
			$this->db->join('sinv    AS i','i.codigo="ZBUFALA"'   ,'LEFT');
			$this->db->join('sinv    AS j','j.codigo="ZBUFALAC"'  ,'LEFT');
			$this->db->join('sinv    AS k','k.codigo="ZLFRIA"'    ,'LEFT');
			$this->db->where('a.lista >',0);
			$this->db->where('(a.pago IS NULL OR a.pago=0)');
			$this->db->where('b.fecha <=',$fcorte);
			$this->db->where('MID(b.ruta,1,1) <>','G');
			$this->db->where('c.codprv',$proveed);
			$query = $this->db->get();
			if ($query->num_rows() > 0){
				$row = $query->row();
				if(!empty($row->total)) $rt['monto'] = round(floatval($row->total),2);
			}

			//Transportista
			$rt['tmonto'] = 0;
			$sel=array('SUM(IF(a.litros=0,a.lista,a.litros)*b.tarifa) AS monto');
			$this->db->select($sel);
			$this->db->from('lrece AS a');
			$this->db->join('lruta AS b','a.ruta=b.codigo');
			$this->db->join('sprv  AS c','b.codprv=c.proveed');
			$this->db->where('a.lista >',0);
			$this->db->where('(a.pago IS NULL OR a.pago=0)');
			$this->db->where('MID(a.ruta,1,1) <>','G');
			$this->db->where('a.fecha <=',$fcorte);
			$this->db->where('b.codprv',$proveed);
			$query = $this->db->get();
			if ($query->num_rows() > 0){
				$row = $query->row();
				if(!empty($row->monto)) $rt['tmonto'] = round(floatval($row->monto),2);
			}

			echo json_encode($rt);
		}else{
			echo '[]';
		}
	}

	function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('nlpago');
		$do->set('numero',$numero);
		$proveed=$do->get('proveed');
		$benefi =$do->get('benefi');

		if(empty($benefi)){
			$nombre = $this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($proveed));
			$do->set('benefi',$nombre);
		}

		$fcorte=date('Y-m-d',mktime(0, 0, 0, date('n'),date('j')-1*date('w')));
		$this->fcorte=$fcorte;

		//Calcula los montos a pagar
		$rt = array();
		//Deducciones
		$rt['deduc'] = 0;
		$sel=array('SUM(a.total) AS val');
		$this->db->select($sel);
		$this->db->from('lgasto AS a');
		$this->db->where('(a.pago IS NULL OR a.pago=0)');
		$this->db->where('a.proveed',$proveed);
		$this->db->where('a.fecha <=',$fcorte);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row = $query->row();
			if(!empty($row->val)) $rt['deduc'] = floatval($row->val);
		}

		//Productores
		$escape_char = $this->db->_escape_char;
		$protect_ide = $this->db->_protect_identifiers;
		$this->db->_escape_char        ='';
		$this->db->_protect_identifiers=false;
		$rt['monto'] = 0;
		$sel=array('SUM(ROUND(a.lista*if(c.tipolec="F",k.ultimo,e.ultimo),2)+ROUND(a.lista*(f.ultimo+g.ultimo+h.ultimo)*(c.tipolec="F")+ROUND(a.lista*IF(c.animal="B",if(c.tipolec="F",i.ultimo,j.ultimo), 0 ),2),2)) AS total');
		$this->db->select($sel);
		$this->db->from('itlrece AS a');
		$this->db->join('lrece   AS b','a.id_lrece=b.id');
		$this->db->join('lvaca   AS c','a.id_lvaca=c.id');
		$this->db->join('sprv    AS d','c.codprv=d.proveed'   ,'LEFT');
		$this->db->join('sinv    AS e','e.codigo="ZLCALIENTE"','LEFT');
		$this->db->join('sinv    AS f','f.codigo="ZMANFRIO"'  ,'LEFT');
		$this->db->join('sinv    AS g','g.codigo="ZPGRASA"'   ,'LEFT');
		$this->db->join('sinv    AS h','h.codigo="ZBACTE"'    ,'LEFT');
		$this->db->join('sinv    AS i','i.codigo="ZBUFALA"'   ,'LEFT');
		$this->db->join('sinv    AS j','j.codigo="ZBUFALAC"'  ,'LEFT');
		$this->db->join('sinv    AS k','k.codigo="ZLFRIA"'    ,'LEFT');

		$this->db->where('a.lista >',0);
		$this->db->where('b.fecha <=',$fcorte);
		$this->db->where('(a.pago IS NULL OR a.pago=0)');
		$this->db->where('MID(b.ruta,1,1) <>','G');
		$this->db->where('c.codprv',$proveed);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row = $query->row();
			if(!empty($row->total)) $rt['monto'] = floatval($row->total);
		}
		$this->db->_escape_char        = $escape_char;
		$this->db->_protect_identifiers= $protect_ide;

		//Transportista
		$rt['tmonto'] = 0;
		$sel=array('SUM(IF(a.litros=0,a.lista,a.litros)*b.tarifa) AS monto');
		$this->db->select($sel);
		$this->db->from('lrece AS a');
		$this->db->join('lruta AS b','a.ruta=b.codigo');
		$this->db->join('sprv  AS c','b.codprv=c.proveed');
		$this->db->where('a.lista >',0);
		$this->db->where('(a.pago IS NULL OR a.pago=0)');
		$this->db->where('MID(a.ruta,1,1) <>','G');
		$this->db->where('b.codprv',$proveed);
		$this->db->where('a.fecha <=',$fcorte);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row = $query->row();
			if(!empty($row->monto)) $rt['tmonto'] = floatval($row->monto);
		}

		//Determina el tipo, si es transportista, productor o ambos
		if($rt['tmonto']*$rt['monto']>0){
			$do->set('tipo','A');
		}elseif($rt['tmonto']>0){
			$do->set('tipo','T');
		}else{
			$do->set('tipo','P');
		}
		//Fin del tipo

		return true;
	}

	function _pre_update($do){
		return false;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$tipo     = $do->get('tipo');
		$proveed  = $do->get('proveed');
		$id       = $do->get('id');
		$dbid     = $this->db->escape($id);
		$dbproveed= $this->db->escape($proveed);
		$fcorte   = $this->fcorte;
		$dbfcorte = $this->db->escape($fcorte);

		//Marca los pagos por transporte
		if($tipo=='T' || $tipo=='A'){
			$mSQL="UPDATE
				lrece AS a
				JOIN lruta AS b ON a.ruta=b.codigo
			SET a.pago=${dbid} WHERE b.codprv=${dbproveed} AND (a.pago IS NULL OR a.pago=0) AND a.fecha <=${dbfcorte} AND MID(a.ruta,1,1)<>'G'";
			$this->db->query($mSQL);
		}

		//Marca los pagos por productor
		if($tipo=='P' || $tipo=='A'){
			$mSQL="UPDATE
			itlrece AS a
			JOIN lrece AS b ON a.id_lrece=b.id
			JOIN lvaca AS c ON a.id_lvaca=c.id
			SET a.pago=${dbid} WHERE c.codprv=${dbproveed} AND (a.pago IS NULL OR a.pago=0) AND b.fecha <=${dbfcorte} AND MID(b.ruta,1,1)<>'G'";
			$this->db->query($mSQL);
		}

		//Marca la deducciones
		$mSQL="UPDATE lgasto SET pago=${dbid} WHERE proveed=${dbproveed} AND fecha <=${dbfcorte} AND (pago IS NULL OR pago=0)";
		$this->db->query($mSQL);

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
		if (!$this->db->table_exists('lpago')) {
			$mSQL="CREATE TABLE `lpago` (
				`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				`numero` VARCHAR(8) NULL DEFAULT NULL,
				`tipo` CHAR(1) NULL DEFAULT NULL COMMENT 'Transportista y Productor',
				`fecha` DATE NULL DEFAULT NULL,
				`proveed` VARCHAR(10) NULL DEFAULT NULL,
				`nombre` VARCHAR(100) NULL DEFAULT NULL,
				`banco` VARCHAR(50) NULL DEFAULT NULL,
				`numche` VARCHAR(100) NULL DEFAULT NULL,
				`benefi` VARCHAR(200) NULL DEFAULT NULL,
				`monto` DECIMAL(12,2) NULL DEFAULT NULL,
				`deduc` DECIMAL(12,2) NULL DEFAULT NULL,
				`montopago` DECIMAL(12,2) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX `proveed` (`proveed`),
				INDEX `numero` (`numero`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}
	}
}
