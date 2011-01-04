<?php
class Bcaj extends Controller {
	function bcaj(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->guitipo=array('DE'=>'Deposito','TR'=>'Transferencia','RM'=>'Remesa');
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter('Filtro de cheques');
		$select=array('fecha','numero','nombre','CONCAT_WS(\'-\',banco ,numcuent) AS banco','tipo_op','codbanc','LEFT(concepto,20)AS concepto','anulado');
		$filter->db->select($select);
		$filter->db->from('bmov');
		$filter->db->where('tipo_op','CH');

		$filter->fecha = new dateonlyField('Fecha','fecha');
		$filter->fecha->size=10;
		$filter->fecha->operator='=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=20;

		$filter->nombre = new inputField('Nombre', 'nombre');
		$filter->nombre->size=40;

		$filter->banco = new dropdownField('Banco', 'codbanc');
		$filter->banco->option('','');
		$filter->banco->options('SELECT codbanc,banco FROM banc where tbanco<>\'CAJ\' ORDER BY codbanc');

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/bmov/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');

		$grid = new DataGrid('Lista de cheques');
		$grid->order_by('numero','desc');
		$grid->per_page = 15;

		$grid->column('N&uacute;mero',$uri );
		$grid->column('Nombre'       ,'nombre');
		$grid->column('Banco'        ,'banco');
		$grid->column('Monto'        ,'<nformat><#monto#></nformat>' ,'align=right');
		$grid->column('Concepto'     ,'concepto');
		$grid->column('Anulado'      ,'anulado','align=center');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Cheques</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit($uriattr='paso1'){
		$this->rapyd->load('dataform');

		$edit = new DataForm('finanzas/bcaj/dataedit/'.$uriattr);
		$edit->title='Deposito en caja';

		$edit->back_url = site_url('finanzas/bcaj/index');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'chfecha|required';

		$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->options($this->guitipo);
		$edit->tipo->rule = 'required';
		$edit->tipo->style = 'width:180px';

		$edit->submit('btnsubmit','Siguiente');
		$edit->build_form();

		if ($edit->on_success()){
			$arr['fecha'] = 'fecha';
			$salida=$this->_dataedit2($arr);
		}else{
			$edit->_process_uri='finanzas/bcaj/dataedit/paso1';
			$edit->build_form();
			$salida=$edit->output;
		}

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $salida;
		$data['title']   = '<h1>Depositos,transferencias y remesas</h1>';
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _dataedit2($arr){
		$tipo=$this->input->post('tipo');
		if($tipo===FALSE) return 'Error de secuencia';

		foreach($arr AS $obj=>$titulo) $arr[$obj]=$this->input->post($obj);
		$arr['tipo']=$tipo;

		$edit = new DataForm('finanzas/bcaj/dataedit/paso2');
		$edit->title($this->guitipo[$tipo].' para la fecha '.$arr['fecha']);

		$edit->envia = new dropdownField('Envia','envia');
		$edit->envia->option('','Seleccionar');

		$edit->recibe = new dropdownField('Recibe','recibe');
		$edit->recibe->option('','Seleccionar');

		$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';
		if($tipo=='DE'){  //Depositos

			$sql='SELECT TRIM(a.codbanc) AS codbanc,b.comitc, b.comitd, b.impuesto FROM banc AS a JOIN tban AS b ON a.tbanco=b.cod_banc AND b.cod_banc<>\'CAJ\'';
			$query = $this->db->query($sql);
			$comis=array();
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$ind='_'.$row->codbanc;
					$comis[$ind]['comitc']  =$row->comitc;
					$comis[$ind]['comitd']  =$row->comitd;
					$comis[$ind]['impuesto']=$row->impuesto;
				}
			}
			$json_comis=json_encode($comis);
			$script='
				comis=eval('.$json_comis.');
				function calcomis(){
					if($("#recibe").val().length>0){
						tasa='.$this->datasis->traevalor('tasa').';
						banco="_"+$("#recibe").val();
						eval("td=comis."+banco+".comitd;"  );
						eval("tc=comis."+banco+".comitc;"  );
						eval("im=comis."+banco+".impuesto;");

						if($("#tarjeta").val().length>0)  tarjeta=parseFloat($("#tarjeta").val());   else tarjeta =0;
						if($("#tdebito").val().length>0)  tdebito =parseFloat($("#tdebito").val());  else tdebito =0;
						if($("#efectivo").val().length>0) efectivo=parseFloat($("#efectivo").val()); else efectivo=0;
						if($("#cheques").val().length>0)  cheques =parseFloat($("#cheques").val());  else cheques =0;

						islr    =tarjeta*10/(100+tasa);
						islr    =islr*(im/10);
						comision=tarjeta*(tc/100)+tdebito*(td/100);
						monto   =tarjeta+tdebito+efectivo+cheques-comision-islr;

						$("#monto").val(roundNumber(monto,2));
						$("#comision").val(roundNumber(comision,2));
						$("#islr").val(roundNumber(islr,2));
					}
				}

				function totaliza(){
					if($("#tarjeta").val().length>0)  tarjeta =parseFloat($("#tarjeta").val());  else tarjeta =0;
					if($("#tdebito").val().length>0)  tdebito =parseFloat($("#tdebito").val());  else tdebito =0;
					if($("#efectivo").val().length>0) efectivo=parseFloat($("#efectivo").val()); else efectivo=0;
					if($("#cheques").val().length>0)  cheques =parseFloat($("#cheques").val());  else cheques =0;
					if($("#comision").val().length>0) comision=parseFloat($("#comision").val()); else comision=0;
					if($("#islr").val().length>0)     islr    =parseFloat($("#islr").val());     else     islr=0;
					monto   =tarjeta+tdebito+efectivo+cheques-comision-islr;
					$("#monto").val(roundNumber(monto,2));
				}';

			$this->rapyd->jquery[]='$("#tarjeta,#tdebito,#cheques,#efectivo").bind("keyup",function() { calcomis(); });';
			$this->rapyd->jquery[]='$("#comision,#islr").bind("keyup",function() { totaliza(); });';
			$this->rapyd->jquery[]='$("#recibe").change(function() { calcomis(); });';
			$edit->script($script);

			$edit->envia->options( "SELECT TRIM(codbanc) AS codbanc,$desca FROM banc WHERE tbanco='CAJ'");

			$edit->recibe->options("SELECT TRIM(codbanc) AS codbanc,$desca FROM banc WHERE tbanco<>'CAJ'");
			$edit->recibe->rule='callback_chtr|required';

			$campos=array(
					'tarjeta' =>'Tarjeta de Cr&eacute;dito',
					'tdebito' =>'Tarjeta de D&eacute;bito',
					'comision'=>'Comisi&oacute;n',
					'cheques' =>'Cheques',
					'efectivo'=>'Efectivo',
					'islr'    =>'I.S.L.R.',
					'monto'   =>'Monto total');
			foreach($campos AS $obj=>$titulo){
				$edit->$obj = new inputField($titulo, $obj);
				$edit->$obj->css_class='inputnum';
				$edit->$obj->rule='trim|numeric';
				$edit->$obj->maxlength =15;
				$edit->$obj->size = 20;
				$edit->$obj->group = 'Montos';
				$edit->$obj->autocomplete=false;
			}
			$edit->$obj->readonly=true;

		}elseif($tipo=='TR'){ //Transferencias
			$link  = site_url('finanzas/bcaj/get_trrecibe');
			$script='
			function get_trrecibe(){
				$.post("'.$link.'",{ envia: $("#envia").val()}, function(data){
					//alert(data);
					$("#recibe").html(data);
				});
			}';

			$edit->script($script);

			$edit->envia->options("SELECT codbanc,$desca FROM banc ORDER BY banco");
			$edit->envia->onchange = 'get_trrecibe();';
			$edit->envia->rule     = 'required';

			$codigo=$this->input->post('envia');
			if($codigo!==false){
				$tipo= $this->_traetipo($codigo);
				$ww=($tipo=='CAJ') ? 'tbanco="CAJ"' : 'tbanco<>"CAJ"';
				$edit->recibe->options("SELECT codbanc,$desca FROM banc WHERE $ww AND codbanc<>".$this->db->escape($codigo)." ORDER BY banco");
			}else{
				$edit->recibe->option('','Seleccione una caja de envio');
			}
			$edit->recibe->rule  = 'required';

			$edit->monto = new inputField('Monto', 'monto');
			$edit->monto->css_class='inputnum';
			$edit->monto->rule='trim|numeric|required';
			$edit->monto->maxlength =15;
			$edit->monto->size = 20;
			$edit->monto->autocomplete=false;

		}elseif($tipo=='RM'){ //Remesas
			$edit->recibe->options("SELECT codbanc,$desca FROM banc WHERE tbanco<>'CAJ'");
			$edit->recibe->rule  = 'required';

			$edit->envia->options("SELECT  codbanc,$desca FROM banc WHERE tbanco='CAJ'");

			$edit->monto = new inputField('Monto', 'monto');
			$edit->monto->css_class='inputnum';
			$edit->monto->rule='trim|numeric|required';
			$edit->monto->maxlength =15;
			$edit->monto->size = 20;
			$edit->monto->autocomplete=false;
		}

		$edit->envia->rule   = 'required';
		$edit->envia->style  = 'width:180px';
		$edit->recibe->style = 'width:180px';

		$edit->container = new containerField('alert',form_hidden($arr));

		$back_url = site_url('finanzas/bcaj/dataedit/');
		$edit->button('btn_undo', 'Regresar', "javascript:window.location='${back_url}'", 'TR');

		$edit->submit('btnsubmit','Guardar');
		$edit->build_form();

		//**********************
		//  Guarda el efecto
		//**********************
		if ($edit->on_success()){
			$numero = $this->datasis->fprox_numero('nbcaj');
			$transac= $this->datasis->fprox_numero('transac');
			$fecha  = human_to_dbdate($this->input->post('fecha'));
			$monto  = $edit->tarjeta->newValue+ $edit->tdebito->newValue+$edit->cheques->newValue-$edit->comision->newValue-$edit->islr->newValue;

			$data=array(
				'tipo'    => $this->input->post('tipo'),
				'fecha'   => $fecha,
				'numero'  => $numero,
				'transac' => $transac,
				'usuario' => $this->session->userdata('usuario'),
				'envia'   => $edit->envia->newValue,
				'recibe'  => $edit->recibe->newValue,
				'tarjeta' => $edit->tarjeta->newValue,
				'tdebito' => $edit->tdebito->newValue,
				'cheques' => $edit->cheques->newValue,
				'efectivo'=> $edit->efectivo->newValue,
				'comision'=> $edit->comision->newValue,
				'islr'    => $edit->islr->newValue,
				'monto'   => $monto,
			);
			$sql = $this->db->insert_string('bcaj', $data);
			echo $sql;

			/*$mSQL="CALL sp_actusal('$edit->envia->newValue','$fecha',-$monto)";
			$ban=$this->db->simple_query($mSQL);
			if($ban==false) memowrite($mSQL,'rcaj');

			$mSQL="CALL sp_actusal('$edit->recibe->newValue','$fecha',$monto)";
			$ban=$this->db->simple_query($mSQL);
			if($ban==false) memowrite($mSQL,'rcaj');*/

		}
		return $edit->output;
	}


	//Transferencia entre cajas
	function tranferencaj(){
		$this->rapyd->load('dataform');
		$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';

		$edit = new DataForm('finanzas/bcaj/tranferencaj/process');
		$edit->title='Transferencia entre cajas';

		$edit->back_url = site_url('finanzas/bcaj/index');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'chfecha|required';
		$edit->fecha->dbformat='Y-m-d';
		$edit->fecha->size=10;

		$edit->envia = new dropdownField('Envia','envia');
		$edit->envia->option('','Seleccionar');
		$edit->envia->options("SELECT  codbanc,$desca FROM banc WHERE tbanco='CAJ'");
		$edit->envia->style = 'width:180px';
		$edit->envia->rule  = 'required';

		$edit->recibe = new dropdownField('Recibe','recibe');
		$edit->recibe->option('','Seleccionar');
		$edit->recibe->options("SELECT  codbanc,$desca FROM banc WHERE tbanco='CAJ'");
		$edit->recibe->style = 'width:180px';
		$edit->recibe->rule  = 'required';

		$edit->monto = new inputField('Monto', 'monto');
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric|required';
		$edit->monto->maxlength =15;
		$edit->monto->size = 20;
		$edit->monto->autocomplete=false;

		$edit->submit('btnsubmit','Guardar');
		$edit->build_form();
		$salida=$edit->output;

		if ($edit->on_success()){
			$arrcajas=$edit->envia->options;

			$numero = $this->datasis->fprox_numero('nbcaj');
			$transac= $this->datasis->fprox_numero('transac');
			$fecha  = $edit->fecha->newValue;
			$monto  = $edit->monto->newValue;
			$numeroe=$this->datasis->banprox($edit->envia->newValue);
			$numeror=$this->datasis->banprox($edit->recibe->newValue);

			$mSQL='SELECT codbanc,numcuent,tbanco,banco,saldo FROM banc WHERE codbanc IN ('.$this->db->escape($edit->envia->newValue).','.$this->db->escape($edit->recibe->newValue).')';
			$query = $this->db->query($mSQL);
			$infbanc=array();
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$infbanc[$row->codbanc]['numcuent']=$row->numcuent;
					$infbanc[$row->codbanc]['tbanco']  =$row->tbanco;
					$infbanc[$row->codbanc]['banco']   =$row->banco;
					$infbanc[$row->codbanc]['saldo']   =$row->banco;
				}
			}

			$data=array(
				'tipo'    => 'TR',
				'fecha'   => $fecha,
				'numero'  => $numero,
				'transac' => $transac,
				'usuario' => $this->session->userdata('usuario'),
				'envia'   => $edit->envia->newValue,
				'tipoe'   => 'ND',
				'numeroe' => $numeroe,
				'bancoe'  => $arrcajas[$edit->envia->newValue],
				'recibe'  => $edit->recibe->newValue,
				'tipor'   => 'NC',
				'numeror' => $numeror,
				'bancor'  => $arrcajas[$edit->recibe->newValue],
				'concepto'=> 'TRANSFERENCIA ENTRE CAJAS',
				'concep2' => '',
				'benefi'  => '',
				'boleta'  => '',
				'precinto'=> '',
				'comprob' =>'',
				'totcant' =>'',
				'status'  =>'',
				'estampa' =>date('Ymd'),
				'hora'    =>date('H:i:s'),
				'deldia'  =>$fecha,
				'tarjeta' => 0,
				'tdebito' => 0,
				'cheques' => 0,
				'efectivo'=> $monto,
				'comision'=> 0,
				'islr'    => 0,
				'monto'   => $monto,
			);
			$sql = $this->db->insert_string('bcaj', $data);
			echo $sql."\n";

			//Crea el egreso en el banco
			$mSQL='CALL sp_actusal('.$this->db->escape($edit->envia->newValue).",'$fecha',-$monto)";
			echo $mSQL."\n";
			//$ban=$this->db->simple_query($mSQL);
			//if($ban==false) memowrite($mSQL,'bcaj');

			$data=array();
			$data['codbanc']  = $edit->envia->newValue;
			$data['numcuent'] = $infbanc[$edit->envia->newValue]['numcuent'];
			$data['banco']    = $arrcajas[$edit->envia->newValue];
			$data['saldo']    = $infbanc[$edit->envia->newValue]['saldo'];
			$data['tipo_op']  = 'ND';
			$data['numero']   = $numeroe;
			$data['fecha']    = $fecha;
			$data['clipro']   = 'O';
			$data['codcp']    = 'TRANS';
			$data['nombre']   = 'TRANSFERENCIAS ENTRE CAJAS';
			$data['monto']    = $monto;
			$data['concepto'] = 'TRANSFERENCIAS ENTRE CAJAS';
			$data['concep2']  = '';
			$data['transac']  = $transac;
			$data['usuario']  = $this->session->userdata('usuario');
			$data['estampa']  = date('Ymd');
			$data['hora']     = date('h:i:s');
			$data['benefi']   = '-';
			$sql = $this->db->insert_string('bcaj', $data);
			echo $sql."\n";
			//$ban=$this->db->simple_query($sql);
			//if($ban==false) memowrite($sql,'bcaj');

			//Crea el ingreso la otra caja
			$mSQL='CALL sp_actusal('.$this->db->escape($edit->recibe->newValue).",'$fecha',$monto)";
			echo $mSQL."\n";
			//$ban=$this->db->simple_query($mSQL);
			//if($ban==false) memowrite($mSQL,'bcaj');

			$data=array();
			$data['codbanc']  = $edit->recibe->newValue;
			$data['numcuent'] = $infbanc[$edit->recibe->newValue]['numcuent'];
			$data['banco']    = $arrcajas[$edit->recibe->newValue];
			$data['saldo']    = $infbanc[$edit->recibe->newValue]['saldo'];
			$data['tipo_op']  = 'NC';
			$data['numero']   = $numeror;
			$data['fecha']    = $fecha;
			$data['clipro']   = 'O';
			$data['codcp']    = 'TRANS';
			$data['nombre']   = 'TRANSFERENCIAS ENTRE CAJAS';
			$data['monto']    = $monto;-
			$data['concepto'] = 'TRANSFERENCIAS ENTRE CAJAS';
			$data['concep2']  = '';
			$data['transac']  = $transac;
			$data['usuario']  = $this->session->userdata('usuario');
			$data['estampa']  = date('Ymd');
			$data['hora']     = date('h:i:s');
			$data['benefi']   = '-';
			$sql = $this->db->insert_string('bcaj', $data);
			echo $sql."\n";
			//$ban=$this->db->simple_query($sql);
			//if($ban==false) memowrite($sql,'bcaj');
		}

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $salida;
		$data['title']   = '<h1>Transferencias entre cajas</h1>';
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
		
	}

	function get_trrecibe(){
		$codigo=$this->input->post('envia');
		echo "<option value=''>Seleccionar</option>";

		if($codigo!==false){
			$tipo= $this->_traetipo($codigo);

			if(!empty($tipo)){
				$ww=($tipo=='CAJ') ? 'tbanco="CAJ"' : 'tbanco<>"CAJ"';
				$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';
				$mSQL=$this->db->query("SELECT codbanc,$desca FROM banc WHERE $ww AND codbanc<>".$this->db->escape($codigo)." ORDER BY banco");
				if($mSQL){
					foreach($mSQL->result() AS $fila )
						echo "<option value='".$fila->codbanc."'>".$fila->desca."</option>";
				}
			}
		}
	}

	function _traetipo($codigo){
		$sql='SELECT tbanco FROM banc WHERE codbanc='.$this->db->escape($codigo);
		return $this->datasis->dameval($sql);
	}

	function chtr(){
		$recibe=$this->input->post('recibe');
	}
}