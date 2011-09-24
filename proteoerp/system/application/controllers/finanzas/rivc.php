<?php
include('common.php');
//require_once(APPPATH.'/controllers/finanzas/gser.php');
class rivc extends Controller {
	var $titp='Retenciones de Clientes';
	var $tits='Retenciones de Clientes';
	var $url ='finanzas/rivc/';

	function rivc(){
		parent::Controller();
		$this->load->library('rapyd');
//		$this->datasis->modulo_id('511',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'rivc');

		$filter->nrocomp = new inputField('Comprobante','nrocomp');
		$filter->nrocomp->rule      ='max_length[8]';
		$filter->nrocomp->size      =10;
		$filter->nrocomp->maxlength =8;

		$filter->emision = new dateField('Emisi&oacute;n','emision');
		$filter->emision->rule      ='chfecha';
		$filter->emision->size      =10;
		$filter->emision->maxlength =8;

		$filter->periodo = new inputField('Per&iacute;odo','periodo');
		$filter->periodo->rule      ='max_length[8]';
		$filter->periodo->size      =10;
		$filter->periodo->maxlength =8;

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->rif = new inputField('RIF','rif');
		$filter->rif->rule      ='max_length[14]';
		$filter->rif->size      =16;
		$filter->rif->maxlength =14;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<#id#>','<#nrocomp#>');

		$grid = new DataGrid('');
		$grid->order_by('nrocomp','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Comprobante'   ,$uri,'nrocomp','align="left"');
		$grid->column_orderby('Emisi&oacute;n','<dbdate_to_human><#emision#></dbdate_to_human>','emision','align="center"');
		$grid->column_orderby('fecha'         ,'<dbdate_to_human><#fecha#></dbdate_to_human>'  ,'fecha','align="center"');
		$grid->column_orderby('Cliente'       ,'cod_cli','cod_cli','align="left"');
		$grid->column_orderby('Nombre'        ,'nombre' ,'nombre','align="left"');
		$grid->column_orderby('RIF'           ,'rif'    ,'rif'   ,'align="left"');
		$grid->column_orderby('Impuesto'      ,'<nformat><#impuesto#></nformat>','impuesto','align="right"');
		$grid->column_orderby('Total'         ,'<nformat><#gtotal#></nformat>'  ,'gtotal','align="right"');
		$grid->column_orderby('Monto Ret.'    ,'<nformat><#reiva#></nformat>'   ,'reiva','align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function convcxp($id){
		$error    = 0;

		$estampa  = date('Y-m-d');
		$hora     = date('H:i:s');
		$sp_fecha = date('Ymd');
		$transac  = $this->datasis->fprox_numero('ntransa');
		//$negreso  = $this->datasis->fprox_numero('negreso');
		$cod_cli  = $this->datasis->dameval("SELECT cod_cli FROM rivc WHERE id=".$this->db->escape($id));
		$itnumero = $this->datasis->dameval("SELECT nrocomp FROM rivc WHERE id=".$this->db->escape($id));
		$fecha    = $this->datasis->dameval("SELECT fecha   FROM rivc WHERE id=".$this->db->escape($id));
		$ttransac = $this->datasis->dameval("SELECT transac FROM rivc WHERE id=".$this->db->escape($id));
		$nombre   = $this->datasis->dameval("SELECT nombre  FROM scli WHERE cliente=".$this->db->escape($cod_cli));
		$totneto  = $this->datasis->dameval("SELECT SUM(monto*IF('ND',-1,1)) AS monto FROM smov WHERE transac='$ttransac' AND tipo_doc IN ('AN','ND') AND cod_cli=".$this->db->escape($cod_cli));
		$usuario  = $this->session->userdata('usuario');


		//Crea la ND al cliente con el monto de los anticipos
		$mnumnd = $this->datasis->fprox_numero('ndcli');
		$data=array();
		$data['cod_cli']    = $cod_cli;
		$data['nombre']     = $nombre;
		$data['tipo_doc']   = 'ND';
		$data['numero']     = $mnumnd;
		$data['fecha']      = $estampa;
		$data['monto']      = $totneto;
		$data['impuesto']   = 0;
		$data['abonos']     = 0;
		$data['vence']      = $fecha;
		$data['tipo_ref']   = 'RT';
		$data['num_ref']    = $itnumero;
		$data['observa1']   = 'NOTA DEBITO A '.$cod_cli.' POR RET. '.$itnumero;
		$data['estampa']    = $estampa;
		$data['hora']       = $hora;
		$data['transac']    = $transac;
		$data['usuario']    = $usuario;
		$data['codigo']     = 'NOCON';
		$data['descrip']    = 'NOTA DE CONTABILIDAD';

		$mSQL = $this->db->insert_string('smov', $data);
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'RIVC'); }

		//Crea la cuenta por pagar
		$causado = $this->datasis->fprox_numero('ncausado');
		$error   = 0;

		$mnsprm = $this->datasis->fprox_numero('num_nd');
		$data=array();
		$data['cod_prv']    = 'REINT';
		$data['nombre']     = 'REINTEGROS CLIENTES';
		$data['tipo_doc']   = 'ND';
		$data['numero']     = $mnsprm ;
		$data['fecha']      = $fecha;
		$data['monto']      = $totneto;
		$data['impuesto']   = 0;
		$data['abonos']     = 0;
		$data['vence']      = $fecha;
		$data['observa1']   = 'REINTEGRO POR RETENCION A RETENCION '.$itnumero;
		$data['tipo_ref']   = 'RT';
		$data['num_ref']    = $itnumero;
		$data['transac']    = $transac;
		$data['estampa']    = $estampa;
		$data['hora']       = $hora;
		$data['usuario']    = $usuario;
		$data['reteiva']    = 0;
		$data['montasa']    = 0;
		$data['monredu']    = 0;
		$data['monadic']    = 0;
		$data['tasa']       = 0;
		$data['reducida']   = 0;
		$data['sobretasa']  = 0;
		$data['exento']     = 0;
		$data['causado']    = $causado;
		$data['codigo']     = 'NOCON';
		$data['descrip']    = 'NOTA DE CONTABILIDAD';

		$sql=$this->db->insert_string('sprm', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'RIVC'); $error++;}

		$sql='UPDATE smov SET abonos=monto WHERE tipo_doc IN (\'AN\',\'ND\') AND transac='.$this->db->escape($ttransac);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'rivc'); $error++; }

		redirect('finanzas/rivc/dataedit/show/'.$id);
	}

	function reintegrar($id){
		$nombre=$this->datasis->dameval('SELECT nombre FROM rivc WHERE id='.$this->db->escape($id));

		$sql='SELECT TRIM(a.codbanc) AS codbanc,tbanco FROM banc AS a WHERE tbanco="CAJ"';
		$query = $this->db->query($sql);
		$comis=array();
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$ind='_'.$row->codbanc;
				$comis[$ind]['tbanco']  =$row->tbanco;
			}
		}
		$json_comis=json_encode($comis);

		$script='var comis = '.$json_comis.';
		
		$(document).ready(function() {
			ccargo=$("#cargo").val();
			desactivacampo(ccargo);
		});

		function desactivacampo(codb1){
			if(codb1.length>0){
				eval("tbanco=comis._"+codb1+".tbanco;"  );
				if(tbanco=="CAJ"){
					$("#cheque").attr("disabled","disabled");
					$("#benefi").attr("disabled","disabled");
				}else{
					$("#cheque").removeAttr("disabled");
					$("#benefi").removeAttr("disabled");
				}
			}else{
				$("#cheque").attr("disabled","disabled");
				$("#benefi").attr("disabled","disabled");
			}
		}';

		$this->rapyd->load('dataform');

		$form = new DataForm('finanzas/rivc/reintegrar/'.$id.'/process');
		$form->title(' ');
		$form->script($script);

		$form->cajero = new dropdownField('Cajero','cajero');
		$form->cajero->option('','Seleccionar');
		$form->cajero->options("SELECT cajero, CONCAT_WS('-',cajero,nombre) AS label FROM scaj ORDER BY cajero");
		$form->cajero->rule='max_length[5]|required|callback_chcajero';

		$form->clave = new inputField('Clave', 'clave');
		$form->clave->rule='required|callback_chclave';
		$form->clave->size=5;
		$form->clave->type='password';

		$form->cargo = new dropdownField('Con cargo a','cargo');
		$form->cargo->option('','Seleccionar');
		$form->cargo->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE activo='S' ORDER BY codbanc");
		$form->cargo->onchange='desactivacampo(this.value)';
		$form->cargo->rule='max_length[5]|required|callback_chcaja';

/*
		$form->cheque = new inputField('N&uacute;mero de cheque', 'cheque');
		$form->cheque->rule='condi_required|callback_chobligaban';
		$form->cheque->append('Aplica  solo si el cargo es a un banco');

		$form->benefi = new inputField('Beneficiario', 'benefi');
		$form->benefi->insertValue=$nombre;
		$form->benefi->rule='condi_required|callback_chobligaban';
		$form->benefi->append('Aplica  solo si el cargo es a un banco');
*/
		$action = "javascript:window.location='".site_url('finanzas/rivc/dataedit/show/'.$id)."'";
		$form->button('btn_regresa', 'Regresar', $action, 'TR');

		$form->submit('btnsubmit','Procesar');
		$form->build_form();

		if ($form->on_success()){
			$error    = 0;
			//$numeroch = str_pad($form->cheque->newValue, 12, '0', STR_PAD_LEFT);
			$codbanc  = $form->cargo->newValue;
			$numeroch = $this->datasis->fprox_numero('ncaja'.$codbanc);
			$datacar  = common::_traebandata($form->cargo->newValue);
			$estampa  = date('Y-m-d');
			$hora     = date('H:i:s');
			$sp_fecha = date('Ymd');
			$ttipo    = $datacar['tbanco'];
			$moneda   = $datacar['moneda'];
			$transac  = $this->datasis->fprox_numero('ntransa');

			$cod_cli  = $this->datasis->dameval("SELECT cod_cli FROM rivc WHERE id=".$this->db->escape($id));
			$ttransac = $this->datasis->dameval("SELECT transac FROM rivc WHERE id=".$this->db->escape($id));
			$totneto  = $this->datasis->dameval("SELECT SUM(monto*IF('ND',-1,1)) AS monto FROM smov WHERE transac='$ttransac' AND tipo_doc IN ('AN','ND') AND cod_cli=".$this->db->escape($cod_cli));
			$itnumero = $this->datasis->dameval("SELECT nrocomp FROM rivc WHERE id=".$this->db->escape($id));
			//$fecha    = $this->datasis->dameval("SELECT fecha   FROM rivc WHERE id=".$this->db->escape($id));
			$nombre   = $this->datasis->dameval("SELECT nombre  FROM scli WHERE cliente=".$this->db->escape($cod_cli));
			$usuario  = $this->session->userdata('usuario');


			//Crea la ND al cliente con el monto de los anticipos
			$mnumnd = $this->datasis->fprox_numero('ndcli');
			$data=array();
			$data['cod_cli']    = $cod_cli;
			$data['nombre']     = $nombre;
			$data['tipo_doc']   = 'ND';
			$data['numero']     = $mnumnd;
			$data['fecha']      = $estampa;
			$data['monto']      = $totneto;
			$data['impuesto']   = 0;
			$data['abonos']     = 0;
			$data['vence']      = $fecha;
			$data['tipo_ref']   = 'RT';
			$data['num_ref']    = $itnumero;
			$data['observa1']   = 'NOTA DEBITO A '.$cod_cli.' POR RET. '.$itnumero;
			$data['estampa']    = $estampa;
			$data['hora']       = $hora;
			$data['transac']    = $transac;
			$data['usuario']    = $usuario;
			$data['codigo']     = 'NOCON';
			$data['descrip']    = 'NOTA DE CONTABILIDAD';

			$mSQL = $this->db->insert_string('smov', $data);
			$ban=$this->db->simple_query($mSQL);
			if($ban==false){ memowrite($mSQL,'RIVC'); }
	
			
			$data=array();
			$data['codbanc']    = $form->cargo->newValue;
			$data['tipo_op']    = 'D';
			$data['numche']     = $numeroch;
			
			$where=array('id'=>$id);

			$mSQL = $this->db->update_string('rivc', $data,$where);
			$ban=$this->db->simple_query($mSQL);
			if($ban==false){ memowrite($mSQL,'RIVC'); }

			$tipo1  = ($ttipo=='CAJ') ? 'D': 'C';
			$negreso= $this->datasis->fprox_numero('negreso');
			$credito= 0;
			$causado='';

			$data=array();
			$data['codbanc']    = $codbanc;
			$data['moneda']     = $moneda;
			$data['numcuent']   = $datacar['numcuent'];
			$data['banco']      = $datacar['banco'];
			$data['saldo']      = $datacar['saldo'];
			$data['tipo_op']    = ($ttipo=='CAJ') ? 'ND': 'CH';
			$data['numero']     = $numeroch;
			$data['fecha']      = date('Y-m-d');
			$data['clipro']     = 'P';
			$data['codcp']      = 'REIVA';
			$data['nombre']     = 'RETENCION DE IVA';
			$data['monto']      = $totneto;
			$data['concepto']   = 'PAGO DE RETENCIONES DE IVA '.$codbanc;
			$data['benefi']     = $form->benefi->newValue;
			$data['posdata']    = '';
			$data['abanco']     = '';
			$data['liable']     = ($ttipo=='CAJ') ? 'S': 'N';;
			$data['transac']    = $transac;
			$data['usuario']    = $usuario;
			$data['estampa']    = $estampa;
			$data['hora']       = $hora;
			$data['anulado']    = 'N';
			$data['susti']      = '';
			$data['negreso']    = $negreso;

			$sql=$this->db->insert_string('bmov', $data);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'rivc'); $error++;}

			$sql='CALL sp_actusal('.$this->db->escape($codbanc).",'$sp_fecha',-$totneto)";
			$ban=$this->db->simple_query($sql);
			//if($ban==false){ memowrite($sql,'rivc'); $error++; }

			$sql='UPDATE smov SET abonos=monto WHERE tipo_doc IN (\'AN\',\'ND\') AND transac='.$this->db->escape($ttransac);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'rivc'); $error++; }

			redirect('finanzas/rivc/dataedit/show/'.$id);
		}

		$data['content'] = $form->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function chcaja($caja){
		$tipo  = common::_traetipo($caja);
		$status='';
		
	}

	function chobligaban($val){
		$ban=$this->input->post('cargo');
		$tipo=common::_traetipo($ban);
		if($tipo!='CAJ'){
			if(empty($val)){
				$this->validation->set_message('chobligaban', 'El campo %s es obligatorio cuando el cargo es a un banco');
				return false;
			}
		}
		return true;
	}

	function chclave($clave){
		$dbclave = $this->db->escape($clave);
		$dbcajero= $this->db->escape($this->input->post('cajero'));
		$ch    = $this->datasis->dameval("SELECT COUNT(*) FROM scaj WHERE cajero=$dbcajero AND clave=$dbclave");
		if($ch>0){
			return true;
		}
		$this->validation->set_message('chclave', 'Clave o cajeo inv&aacute;lido');
		return false;
	}

	function dataedit(){
		$this->rapyd->load('datadetails','dataobject');

		$do = new DataObject('rivc');
		//$do->pointer('scli' ,'scli.cliente=rivc.cod_cli','sprv.tipo AS sprvtipo, sprv.reteiva AS sprvreteiva','left');
		$do->rel_one_to_many('itrivc' ,'itrivc' ,array('id'=>'idrivc'));

		$edit = new DataDetails($this->tits, $do);

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->nrocomp = new inputField('Comprobante','nrocomp');
		$edit->nrocomp->rule='max_length[8]|required';
		$edit->nrocomp->size =10;
		$edit->nrocomp->maxlength =8;
		$edit->nrocomp->autocomplete = false;

		$edit->emision = new dateField('Fecha de Emisi&oacute;n','emision');
		$edit->emision->rule='chfecha|required';
		$edit->emision->size =10;
		$edit->emision->maxlength =8;

		$edit->periodo = new inputField('Per&iacute;odo','periodo');
		$edit->periodo->rule='max_length[6]|required';
		$edit->periodo->size =6;
		$edit->periodo->insertValue=date('Ym');
		$edit->periodo->maxlength =6;

		$edit->fecha = new dateField('Fecha de Recepci&oacute;n','fecha');
		$edit->fecha->rule='chfecha|required';
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size =9;
		$edit->fecha->maxlength =8;

		$edit->cod_cli = new inputField('Cliente','cod_cli');
		$edit->cod_cli->rule='max_length[5]|required|strtoupper';
		$edit->cod_cli->size =10;
		//$edit->cod_cli->maxlength =5;

		$edit->nombre = new hiddenField('Nombre','nombre');
		$edit->nombre->rule='max_length[200]';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =200;

		$edit->rif = new hiddenField('RIF','rif');
		$edit->rif->rule='max_length[14]|strtoupper';
		$edit->rif->size =16;
		$edit->rif->maxlength =14;
		$edit->rif->autocomplete = false;

		/*$edit->reintegro = new radiogroupField('Operaci&oacute;n', 'reintegro', array('R'=>'Reintegrar','A'=>'Crear anticipo','P'=>'Crear CxP'));
		$edit->reintegro->insertValue='A';
		$edit->reintegro->rule='required';*/

		$edit->exento = new inputField('Monto Exento','exento');
		$edit->exento->rule='max_length[15]|numeric';
		$edit->exento->css_class='inputnum';
		$edit->exento->size =17;
		$edit->exento->maxlength =15;

		$edit->tasa = new inputField('tasa','tasa');
		$edit->tasa->rule='max_length[5]|numeric';
		$edit->tasa->css_class='inputnum';
		$edit->tasa->size =7;
		$edit->tasa->maxlength =5;

		$edit->general = new inputField('general','general');
		$edit->general->rule='max_length[15]|numeric';
		$edit->general->css_class='inputnum';
		$edit->general->size =17;
		$edit->general->maxlength =15;

		$edit->geneimpu = new inputField('geneimpu','geneimpu');
		$edit->geneimpu->rule='max_length[15]|numeric';
		$edit->geneimpu->css_class='inputnum';
		$edit->geneimpu->size =17;
		$edit->geneimpu->maxlength =15;

		$edit->tasaadic = new inputField('tasaadic','tasaadic');
		$edit->tasaadic->rule='max_length[5]|numeric';
		$edit->tasaadic->css_class='inputnum';
		$edit->tasaadic->size =7;
		$edit->tasaadic->maxlength =5;

		$edit->adicional = new inputField('adicional','adicional');
		$edit->adicional->rule='max_length[15]|numeric';
		$edit->adicional->css_class='inputnum';
		$edit->adicional->size =17;
		$edit->adicional->maxlength =15;

		$edit->adicimpu = new inputField('adicimpu','adicimpu');
		$edit->adicimpu->rule='max_length[15]|numeric';
		$edit->adicimpu->css_class='inputnum';
		$edit->adicimpu->size =17;
		$edit->adicimpu->maxlength =15;

		$edit->tasaredu = new inputField('tasaredu','tasaredu');
		$edit->tasaredu->rule='max_length[5]|numeric';
		$edit->tasaredu->css_class='inputnum';
		$edit->tasaredu->size =7;
		$edit->tasaredu->maxlength =5;

		$edit->reducida = new inputField('reducida','reducida');
		$edit->reducida->rule='max_length[15]|numeric';
		$edit->reducida->css_class='inputnum';
		$edit->reducida->size =17;
		$edit->reducida->maxlength =15;

		$edit->reduimpu = new inputField('reduimpu','reduimpu');
		$edit->reduimpu->rule='max_length[15]|numeric';
		$edit->reduimpu->css_class='inputnum';
		$edit->reduimpu->size =17;
		$edit->reduimpu->maxlength =15;

		$edit->stotal = new hiddenField('Sub-total','stotal');
		$edit->stotal->rule='max_length[15]|numeric';
		$edit->stotal->css_class='inputnum';
		$edit->stotal->size =17;
		$edit->stotal->maxlength =15;

		$edit->impuesto = new hiddenField('Impuesto','impuesto');
		$edit->impuesto->rule='max_length[15]|numeric';
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->size =17;
		$edit->impuesto->maxlength =15;

		$edit->gtotal = new hiddenField('Total','gtotal');
		$edit->gtotal->rule='max_length[15]|numeric';
		$edit->gtotal->css_class='inputnum';
		$edit->gtotal->size =17;
		$edit->gtotal->maxlength =15;

		$edit->reiva = new hiddenField('Monto Retenido','reiva');
		$edit->reiva->rule='max_length[15]|numeric';
		$edit->reiva->css_class='inputnum';
		$edit->reiva->size =17;
		$edit->reiva->maxlength =15;

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->origen  = new autoUpdateField('origen' ,'R','R');

		$edit->modificado = new inputField('modificado','modificado');
		$edit->modificado->rule='max_length[8]';
		$edit->modificado->size =10;
		$edit->modificado->maxlength =8;

		//****************************
		//Inicio del Detalle
		//****************************
		$edit->it_tipo_doc = new hiddenField('tipo_doc','tipo_doc_<#i#>');
		$edit->it_tipo_doc->db_name='tipo_doc';
		$edit->it_tipo_doc->rule='max_length[2]|required';
		$edit->it_tipo_doc->size =4;
		$edit->it_tipo_doc->maxlength =1;
		$edit->it_tipo_doc->rel_id ='itrivc';

		$edit->it_fecha = new dateonlyField('fecha','fecha_<#i#>');
		$edit->it_fecha->db_name='fecha';
		$edit->it_fecha->rule='required';
		$edit->it_fecha->size =11;
		$edit->it_fecha->maxlength =10;
		$edit->it_fecha->rel_id ='itrivc';

		$edit->it_numero = new inputField('numero','numero_<#i#>');
		$edit->it_numero->db_name='numero';
		$edit->it_numero->rule='max_length[12]|required|callback_chrepetidos|callback_chfac';
		$edit->it_numero->size =14;
		$edit->it_numero->maxlength =12;
		$edit->it_numero->rel_id ='itrivc';
		$edit->it_numero->autocomplete = false;

		$edit->it_stotal = new inputField('stotal','stotal_<#i#>');
		$edit->it_stotal->db_name='stotal';
		$edit->it_stotal->rule='max_length[15]|numeric';
		$edit->it_stotal->css_class='inputnum';
		$edit->it_stotal->size =17;
		$edit->it_stotal->maxlength =15;
		$edit->it_stotal->rel_id ='itrivc';
		$edit->it_stotal->showformat ='decimal';

		$edit->it_impuesto = new hiddenField('impuesto','impuesto_<#i#>');
		$edit->it_impuesto->db_name='impuesto';
		$edit->it_impuesto->rule='max_length[15]|numeric';
		$edit->it_impuesto->css_class='inputnum';
		$edit->it_impuesto->size =17;
		$edit->it_impuesto->maxlength =15;
		$edit->it_impuesto->showformat ='decimal';
		$edit->it_impuesto->rel_id ='itrivc';

		$edit->it_gtotal = new hiddenField('gtotal','gtotal_<#i#>');
		$edit->it_gtotal->db_name='gtotal';
		$edit->it_gtotal->rule='max_length[15]|numeric';
		$edit->it_gtotal->css_class='inputnum';
		$edit->it_gtotal->size =17;
		$edit->it_gtotal->maxlength =15;
		$edit->it_gtotal->rel_id ='itrivc';
		$edit->it_gtotal->showformat ='decimal';
		$edit->it_gtotal->autocomplete = false;

		$edit->it_reiva = new inputField('reiva','reiva_<#i#>');
		$edit->it_reiva->db_name='reiva';
		$edit->it_reiva->rule='max_length[15]|numeric';
		$edit->it_reiva->css_class='inputnum';
		$edit->it_reiva->size =17;
		$edit->it_reiva->maxlength =15;
		$edit->it_reiva->rel_id ='itrivc';
		$edit->it_reiva->onkeyup ='totalizar()';
		$edit->it_reiva->autocomplete = false;
		$edit->it_reiva->showformat ='decimal';
		//****************************
		//Fin del Detalle
		//****************************
		
		//cheque qu pueda aparecer el boton
		
		$ide=$this->db->escape($edit->get_from_dataobjetct('id'));
		$query="
		SELECT SUM(monto-abonos) abonos
		FROM  rivc a
		JOIN itrivc b  ON a.id=b.idrivc
		JOIN smov c ON b.transac=c.transac
		WHERE a.id=$ide AND c.tipo_doc='AN'
		";
		$xabonar=$this->datasis->dameval($query);
		
		if($edit->_status=='show'){
			if($xabonar>0){
				$action = "javascript:window.location='".site_url('finanzas/rivc/reintegrar/'.$edit->get_from_dataobjetct('id'))."'";
				$edit->button('btn_reintegrar', 'Reintegrar', $action, 'TR');
	
				$action = "javascript:window.location='".site_url('finanzas/rivc/convcxp/'.$edit->get_from_dataobjetct('id'))."'";
				$edit->button('btn_convcxp', 'Convertir a CxP', $action, 'TR');
			}
		}

		$edit->buttons('save', 'undo', 'back','add_rel');
		$edit->build();

		//$data['content'] = $edit->output;
		$conten['form'] =& $edit;
		$data['content'] = $this->load->view('view_rivc', $conten,true);
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');
		$data['head']   .= phpscript('nformat.js');
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);
	}

	function chrepetidos($numero){
		if(!isset($this->ch_repetido)) $this->ch_repetido=array();

		if(array_search($numero,$this->ch_repetido)===false){
			$this->ch_repetido[]=$numero;
			return true;
		}else{
			$this->validation->set_message('chrepetidos', 'La factura '.$numero.' esta repetida');
			return false;
		}
	}

	function chfac($numero){
		$cod_cli=$this->input->post('cod_cli');
		$mSQL='SELECT COUNT(*) FROM sfac WHERE numero='.$this->db->escape($numero).' AND cod_cli='.$this->db->escape($cod_cli);
		$cana=$this->datasis->dameval($mSQL);

		if($cana==1){
			return true;
		}else{
			$this->validation->set_message('chfac', 'La factura '.$numero.' no pertenece al cliente '.$cod_cli);
			return false;
		}
	}

	function buscasfac(){
		$mid   = $this->input->post('q');
		$scli  = $this->input->post('scli');
		$qdb   = $this->db->escape('%'.$mid.'%');
		$sclidb= $this->db->escape($scli);
		
		$rete=0.75;
		$data = '{[ ]}';
		if(empty($scli)){
			$retArray[0]['label']   = 'Debe seleccionar un cliente primero';
			$retArray[0]['value']   = '';
			$data = json_encode($retArray);
			echo $data;
			return;
		}
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT a.tipo_doc, a.numero, a.totalg, a.fecha,a.iva, a.iva*$rete AS reiva
				FROM sfac AS a
				LEFT JOIN itrivc AS b ON a.tipo_doc=b.tipo_doc AND a.numero=b.numero
				WHERE a.cod_cli=$sclidb AND CONCAT(a.tipo_doc,'-',a.numero) LIKE $qdb AND b.numero IS NULL AND a.tipo_doc <> 'X'
				ORDER BY numero LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = $row['tipo_doc'].'-'.$row['numero'].' '.$row['totalg'].' Bs.';
					$retArray['value']   = $row['numero'];
					$retArray['gtotal']  = $row['totalg'];
					$retArray['reiva']   = (($row['tipo_doc']=='D')? -1: 1)*round($row['reiva'],2);
					//$retArray['reiva']   = round($row['reiva'],2);
					$retArray['impuesto']= $row['iva'];
					$retArray['fecha']   = dbdate_to_human($row['fecha']);
					$retArray['tipo_doc']= $row['tipo_doc'];

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }else{
				$retArray[0]['label']   = 'No se consiguieron facturas para aplicar';
				$retArray[0]['value']   = '';
				$data = json_encode($retArray);
			}
		}
		echo $data;
	}

	function buscascli(){
		$mid  = $this->input->post('q');
		$qdb  = $this->db->escape('%'.$mid.'%');

		$data = '{[ ]}';
		if($mid !== false){ 
			$retArray = $retorno = array();
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo
				FROM scli WHERE cliente LIKE ${qdb} OR rifci LIKE ${qdb} OR nombre LIKE ${qdb}
				ORDER BY rifci LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['cliente'];
					$retArray['label']   = '('.$row['rifci'].') '.utf8_encode($row['nombre']);
					$retArray['rifci']   = $row['rifci'];
					$retArray['nombre']  = utf8_encode($row['nombre']);
					$retArray['cod_cli'] = $row['cliente'];
					$retArray['tipo']    = $row['tipo'];

					array_push($retorno, $retArray);

				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}

	function _pre_insert($do){
		$transac = $this->datasis->fprox_numero('ntransa');
		$do->set('transac', $transac);
		$estampa = $do->get('estampa');
		$hora    = $do->get('hora');
		$usuario = $do->get('usuario');
		$exento=$general=$geneimpu=$adicional=$adicimpu=$reducida=$reduimpu=$stotal=$impuesto=$gtotal=$reiva=0;


		$rel='itrivc';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$ittipo_doc   = $do->get_rel($rel, 'tipo_doc', $i);
			$itreiva      = abs($do->get_rel($rel, 'reiva', $i));
			$dbitnumero   = $this->db->escape($do->get_rel($rel, 'numero'  , $i));
			$dbittipo_doc = $this->db->escape($ittipo_doc);

			$sql="SELECT exento,tasa,reducida,sobretasa,montasa,monredu,monadic,nfiscal,totals,totalg,iva FROM sfac WHERE numero=$dbitnumero AND tipo_doc=$dbittipo_doc";
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0){
				$row = $query->row();

				$do->set_rel($rel, 'exento'   , $row->exento , $i);

				$do->set_rel($rel, 'tasa'     , ($row->montasa>0)? round($row->tasa *100/$row->montasa,2) : 0, $i);
				$do->set_rel($rel, 'general'  , $row->montasa, $i);
				$do->set_rel($rel, 'geneimpu' , $row->tasa   , $i);

				$do->set_rel($rel, 'tasaadic' , ($row->monadic>0)? round($row->sobretasa*100/$row->monadic,2) : 0, $i);
				$do->set_rel($rel, 'adicional', $row->monadic  , $i);
				$do->set_rel($rel, 'adicimpu' , $row->sobretasa, $i);

				$do->set_rel($rel, 'tasaredu' , ($row->monredu>0)? round($row->reducida*100/ $row->monredu,2) : 0, $i);
				$do->set_rel($rel, 'reducida' , $row->monredu , $i);
				$do->set_rel($rel, 'reduimpu' , $row->reducida, $i);

				$do->set_rel($rel, 'nfiscal' , $row->nfiscal, $i);
				$do->set_rel($rel, 'reiva'    , $itreiva     , $i);

				$exento   =$exento+$row->exento;

				$general  =$general+$row->montasa;
				$geneimpu =$geneimpu+$row->tasa;

				$adicional=$adicional+$row->monadic;
				$adicimpu =$adicimpu+$row->sobretasa;

				$reducida =$reducida+$row->monredu;
				$reduimpu =$reduimpu+$row->reducida;

				//Totales del encabezado
				$fac=($ittipo_doc=='D')? -1:1; //Para restar las devoluciones
				$stotal   =$stotal+($fac*$row->totals);
				$impuesto =$impuesto+($fac*$row->iva);
				$gtotal   =$gtotal+($fac*$row->totalg);
				$reiva    =$reiva+($fac*$itreiva);
			}

			$do->set_rel($rel, 'estampa', $estampa, $i);
			$do->set_rel($rel, 'hora'   , $hora   , $i);
			$do->set_rel($rel, 'usuario', $usuario, $i);
			$do->set_rel($rel, 'transac', $transac, $i);
		}

		$do->set('exento'   ,$exento);
		$do->set('general'  ,$general);
		$do->set('geneimpu' ,$geneimpu);
		$do->set('adicional',$adicional);
		$do->set('adicimpu' ,$adicimpu);
		$do->set('reducida' ,$reducida);
		$do->set('reduimpu' ,$reduimpu);
		$do->set('stotal'   ,$stotal);
		$do->set('impuesto' ,$impuesto);
		$do->set('gtotal'   ,$gtotal);
		$do->set('reiva'    ,$reiva);

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

		$transac = $do->get('transac');
		$estampa = $do->get('estampa');
		$hora    = $do->get('hora');
		$usuario = $do->get('usuario');
		$cod_cli = $do->get('cod_cli');
		$nombre  = $do->get('nombre');
		$estampa = $do->get('estampa');
		$periodo = $do->get('periodo');
		$usuario = $do->get('usuario');
		$hora    = $do->get('hora');

		//$reinte  = $this->uri->segment($this->uri->total_segments());
		$efecha  = $do->get('emision');
		$fecha   = $do->get('fecha');
		$numero  = $do->get('nrocomp');

		$mSQL = "DELETE FROM smov WHERE transac='$transac'";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'RIVC'); }


		$rel='itrivc';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$ittipo_doc  = $do->get_rel($rel, 'tipo_doc', $i);
			$itnumero    = $do->get_rel($rel, 'numero'  , $i);
			$itmonto     = $do->get_rel($rel, 'reiva'  , $i);

			$dbitnumero   = $this->db->escape($itnumero);
			$dbittipo_doc = $this->db->escape($ittipo_doc);

			$sql="SELECT referen,reiva,factura,cod_cli,nombre FROM sfac WHERE numero=$dbitnumero AND tipo_doc=$dbittipo_doc";
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0){
				$row = $query->row();

				$anterior    = $row->reiva;
				$itreferen   = $row->referen;
				$itfactura   = $row->factura;
			}

			if($anterior == 0) {
				$mSQL = "UPDATE sfac SET reiva=${itmonto}, creiva='${periodo}${numero}', freiva='${fecha}', ereiva='${efecha}' WHERE numero=${dbitnumero} AND tipo_doc=${dbittipo_doc}";
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }
			}

			//Chequea si es credito y si tiene saldo
			if($itreferen=='C'){
				$saldo =  $this->datasis->dameval("SELECT monto-abonos FROM smov WHERE tipo_doc='FC' AND numero='$itnumero'");
			}else{
				$saldo = 0;
			}

			//Si es una factura
			if($ittipo_doc == 'F'){
				//Si el saldo es 0  o menor que el monto retenido genera un anticipo
				if($saldo==0 || $itmonto>$saldo){
					$mnumant = $this->datasis->fprox_numero('nancli');

					$data=array();
					$data['cod_cli']    = $cod_cli;
					$data['nombre']     = $nombre;
					$data['tipo_doc']   = 'AN';
					$data['numero']     = $mnumant;
					$data['fecha']      = $fecha;
					$data['monto']      = $itmonto;
					$data['impuesto']   = 0;
					$data['vence']      = $fecha;
					$data['tipo_ref']   = ($ittipo_doc=='F')? 'FC' : 'DV';
					$data['num_ref']    = $itnumero;
					$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A DOC. '.$ittipo_doc.$itnumero;
					$data['usuario']    = $usuario;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['transac']    = $transac;
					$data['nroriva']    = $numero;
					$data['emiriva']    = $efecha;

					$mSQL = $this->db->insert_string('smov', $data); 
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'RIVC'); }
				}else{
				//Si tiene saldo
					//Chequea que el monto de la retencion sea menor al saldo en caso tal crea una NC
					$mnumnc = $this->datasis->fprox_numero('nccli');
					$data=array();
					$data['cod_cli']    = $cod_cli;
					$data['nombre']     = $nombre;
					$data['tipo_doc']   = 'NC';
					$data['numero']     = $mnumnc;
					$data['fecha']      = $fecha;
					$data['monto']      = $itmonto;
					$data['impuesto']   = 0;
					$data['abonos']     = $itmonto;
					$data['vence']      = $fecha;
					$data['tipo_ref']   = ($ittipo_doc=='F')? 'FC' : 'DV';
					$data['num_ref']    = $itnumero;
					$data['observa1']   = 'APLICACION DE RETENCION A DOC. '.$ittipo_doc.$itnumero;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['transac']    = $transac;
					$data['usuario']    = $usuario;
					$data['codigo']     = 'NOCON';
					$data['descrip']    = 'NOTA DE CONTABILIDAD';
					$data['nroriva']    = $numero;
					$data['emiriva']    = $efecha;

					$mSQL = $this->db->insert_string('smov', $data);
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'RIVC'); }

					// Abona la factura
					$tiposfac = ($ittipo_doc=='D')? $tiposfac = 'NC':'FC';
					$mSQL = "UPDATE smov SET abonos=abonos+$itmonto WHERE numero='$itnumero' AND cod_cli='$cod_cli' AND tipo_doc='$tiposfac'";
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'RIVC'); }
				}

				$mnumnd = $this->datasis->fprox_numero('ndcli');
				$data=array();
				$data['cod_cli']    = 'REIVA';
				$data['nombre']     = 'RETENCION DE I.V.A. POR COMPENSAR';
				$data['tipo_doc']   = 'ND';
				$data['numero']     = $mnumnd;
				$data['fecha']      = $fecha;
				$data['monto']      = $itmonto;
				$data['impuesto']   = 0;
				$data['abonos']     = 0;
				$data['vence']      = $fecha;
				$data['tipo_ref']   = ($ittipo_doc=='F')? 'FC' : 'DV';
				$data['num_ref']    = $itnumero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A DOC. '.$ittipo_doc.$itnumero;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['codigo']     = 'NOCON';
				$data['descrip']    = 'NOTA DE CONTABILIDAD';
				$data['nroriva']    = $numero;
				$data['emiriva']    = $efecha;

				$mSQL = $this->db->insert_string('smov', $data);
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }
			}else{
			//Si es una devolucion
				// Devoluciones genera un ND al cliente
				$mnumnd = $this->datasis->fprox_numero('ndcli');
				$data=array();
				$data['cod_cli']    = $cod_cli;
				$data['nombre']     = $nombre;
				$data['tipo_doc']   = 'ND';
				$data['numero']     = $mnumnd;
				$data['fecha']      = $fecha;
				$data['monto']      = $itmonto;
				$data['impuesto']   = 0;
				$data['vence']      = $fecha;
				$data['tipo_ref']   = ($ittipo_doc=='F')? 'FC' : 'DV';
				$data['num_ref']    = $itnumero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A DOC. '.$ittipo_doc.$itnumero;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['nroriva']    = $numero;
				$data['emiriva']    = $efecha;

				$mSQL = $this->db->insert_string('smov', $data); 
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }

				//Devoluciones debe crear un NC si esta en el periodo
				$mnumnc = $this->datasis->fprox_numero("nccli");
				$data=array();
				$data['cod_cli']    = 'REIVA';
				$data['nombre']     = 'RETENCION DE I.V.A. POR COMPENSAR';
				$data['tipo_doc']   = 'NC';
				$data['numero']     = $mnumnc;
				$data['fecha']      = $fecha;
				$data['monto']      = $itmonto;
				$data['impuesto']   = 0;
				$data['abonos']     = 0;
				$data['vence']      = $fecha;
				$data['tipo_ref']   = ($ittipo_doc=='F')? 'FC' : 'DV';
				$data['num_ref']    = $itnumero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A DOC.'.$ittipo_doc.$itnumero;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['codigo']     = 'NOCON';
				$data['descrip']    = 'NOTA DE CONTABILIDAD';
				$data['nroriva']    = $numero;
				$data['emiriva']    = $efecha;

				$mSQL = $this->db->insert_string('smov', $data); 
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }
			}
		}

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
		if (!$this->db->table_exists('rivc')) {
			$mSQL="CREATE TABLE `rivc` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`nrocomp` varchar(8) NOT NULL DEFAULT '',
			`emision` date DEFAULT NULL,
			`periodo` char(8) DEFAULT NULL,
			`fecha` date DEFAULT NULL,
			`cod_cli` varchar(5) DEFAULT NULL,
			`nombre` varchar(200) DEFAULT NULL,
			`rif` varchar(14) DEFAULT NULL,
			`exento` decimal(15,2) DEFAULT NULL,
			`tasa` decimal(5,2) DEFAULT NULL,
			`general` decimal(15,2) DEFAULT NULL,
			`geneimpu` decimal(15,2) DEFAULT NULL,
			`tasaadic` decimal(5,2) DEFAULT NULL,
			`adicional` decimal(15,2) DEFAULT NULL,
			`adicimpu` decimal(15,2) DEFAULT NULL,
			`tasaredu` decimal(5,2) DEFAULT NULL,
			`reducida` decimal(15,2) DEFAULT NULL,
			`reduimpu` decimal(15,2) DEFAULT NULL,
			`stotal` decimal(15,2) DEFAULT NULL,
			`impuesto` decimal(15,2) DEFAULT NULL,
			`gtotal` decimal(15,2) DEFAULT NULL,
			`reiva` decimal(15,2) DEFAULT NULL,
			`estampa` date DEFAULT NULL,
			`hora` char(8) DEFAULT NULL,
			`usuario` varchar(12) DEFAULT NULL,
			`modificado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`transac` varchar(8) DEFAULT NULL,
			`origen` char(1) DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `nrocomp_clipro` (`nrocomp`,`cod_cli`),
			KEY `modificado` (`modificado`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('itrivc')) {
			$mSQL="CREATE TABLE `itrivc` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`idrivc` int(6) DEFAULT NULL,
			`tipo_doc` char(2) DEFAULT NULL,
			`fecha` date DEFAULT NULL,
			`numero` varchar(8) DEFAULT NULL,
			`nfiscal` char(12) DEFAULT NULL,
			`exento` decimal(15,2) DEFAULT NULL,
			`tasa` decimal(5,2) DEFAULT NULL,
			`general` decimal(15,2) DEFAULT NULL,
			`geneimpu` decimal(15,2) DEFAULT NULL,
			`tasaadic` decimal(5,2) DEFAULT NULL,
			`adicional` decimal(15,2) DEFAULT NULL,
			`adicimpu` decimal(15,2) DEFAULT NULL,
			`tasaredu` decimal(5,2) DEFAULT NULL,
			`reducida` decimal(15,2) DEFAULT NULL,
			`reduimpu` decimal(15,2) DEFAULT NULL,
			`stotal` decimal(15,2) DEFAULT NULL,
			`impuesto` decimal(15,2) DEFAULT NULL,
			`gtotal` decimal(15,2) DEFAULT NULL,
			`reiva` decimal(15,2) DEFAULT NULL,
			`transac` char(8) DEFAULT NULL,
			`estampa` date DEFAULT NULL,
			`hora` char(8) DEFAULT NULL,
			`usuario` char(12) DEFAULT NULL,
			`ffactura` date DEFAULT '0000-00-00',
			`modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `tipo_doc_numero` (`tipo_doc`,`numero`),
			KEY `Numero` (`numero`),
			KEY `modificado` (`modificado`),
			KEY `rivatra` (`transac`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}
	}
}
