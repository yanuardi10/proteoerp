<?php
class Bcaj extends Controller {
	function bcaj(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->config->load('datasis');

		$this->guitipo=array('DE'=>'Deposito','TR'=>'Transferencia');
		$this->datasis->modulo_id('51D',1);
		$this->bcajnumero='';
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');
		$smenu['link']=barra_menu('51D');

		$atts = array(
			'width'      => '800',
			'height'     => '600',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '0',
			'screeny'    => '0'
		);

		$filter = new DataFilter('Filtro','bcaj');
		$filter->fecha = new dateonlyField('Fecha','fecha');
		$filter->fecha->size=10;
		$filter->fecha->operator='=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=20;

		//$filter->nombre = new inputField('Nombre', 'nombre');
		//$filter->nombre->size=40;

		//$filter->banco = new dropdownField('Banco', 'codbanc');
		//$filter->banco->option('','');
		//$filter->banco->options('SELECT codbanc,banco FROM banc where tbanco<>\'CAJ\' ORDER BY codbanc');

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/bcaj/dataedit/show/<#numero#>','<#numero#>');
		$uri1 = anchor('formatos/ver/BANCAJA/<#numero#>','<#numero#>');

		$grid = new DataGrid('Lista');
		$grid->order_by('numero','desc');
		$grid->per_page = 15;

		$uri2 = anchor_popup('finanzas/bcaj/formato/<#numero#>/<#tipo#>','PDF',$atts);

		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha'        ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha');
		$grid->column_orderby('Env&iacute;a' ,'<#envia#>-<#bancoe#>','bancoe');
		$grid->column_orderby('Recibe'       ,'<#recibe#>-<#bancor#>','bancor');
		$grid->column_orderby('Monto'        ,'<nformat><#monto#></nformat>' ,'monto','align=right');
		$grid->column_orderby('Concepto'     ,'concepto','concepto');
		$grid->column('Vista',$uri2,"align='center'");

		$grid->add('finanzas/bcaj/agregar');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);

		$data['title']   = '<h1>Movimientos de Caja</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function formato($numero){
		$formato=$this->_formato($numero);
		$url='formatos/ver/'.$formato.'/'.$numero;
		redirect($url);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Deposito en caja', 'bcaj');
		$edit->back_url = site_url('finanzas/bcaj/index');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'chfecha|required';

		$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->options($this->guitipo);
		$edit->tipo->rule = 'required';
		$edit->tipo->style = 'width:180px';

		$edit->envia = new dropdownField('Envia','envia');
		$edit->envia->option('','Seleccionar');

		$edit->recibe = new dropdownField('Recibe','recibe');
		$edit->recibe->option('','Seleccionar');

		//Poner los campos que faltan

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Depositos,transferencias y remesas</h1>';
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function agregar(){
		$data['content'] = '<table align="center">';

		$data['content'].= '<tr><td><img src="'.base_url().'images/dinero.jpg'.'" height="100px"></td><td bgcolor="#ddeedd">';
		$data['content'].= '<p>'.anchor('finanzas/bcaj/depositoefe'  ,'Deposito de efectivo: ');
		$data['content'].= 'Esta opci&oacute;n se utiliza para depositar lo recaudado en efectivo desde 
		                    las cajas para los bancos, debe tener a mano el n&uacute;mero del deposito.</p>';

		$data['content'].= '</td></tr><tr><td><img src="'.base_url().'images/tarjetas.jpg'.'" height="100px"></td><td>';
		$data['content'].= '<p>'.anchor('finanzas/bcaj/depositotar'  ,'Deposito de tarjetas: ');
		$data['content'].= 'Para registrar lo recaudado mediante tarjetas electr&oacute;nicas (Cr&eacute;dito, Debito, Cesta Ticket) 
		                    seg&uacute;n los valores impresos en los cierres diarios de los puntos de venta electr&oacute;nicos.</p>';

		$data['content'].= '</td></tr><tr><td><img src="'.base_url().'images/transfer.jpg'.'" height="100px" ></td><td bgcolor="#ddeedd">';
		$data['content'].= '<p>'.anchor('finanzas/bcaj/transferencia','Transferencias: ');
		$data['content'].= 'Puede hacer transferencias entre cajas o entre cuentas bancarias, las que correspondan a
		                    cuentas bancarias pueden realizarce mediante cheque-deposito (manual) o NC-ND por transferencia 
		                    electr&oacute;nica, en cualquier caso debe tener los n&uacute;meros de documentos correspondientes.</p>';

		$data['content'].= '</td></tr><tr><td><img src="'.base_url().'images/caja_activa.gif'.'" height="100px" ></td><td>';
		$data['content'].= '<p>'.anchor('finanzas/bcaj/autotranfer','Transferencia de Cierre de Caja: ');
		$data['content'].= 'Si por pol&iacute;tica de la empresa se quiere descargar la caja de recaudaci&oacute;n todos los d&iacute;as, esta
		                    opci&oacute;n facilita el proceso ya que puede hacer varias transferencias en una sola operaci&oacute;n, por lo
		                    que se recomienda hacerla despues de cerrar todas la cajas.</p>';

		$data['content'].= '</td></tr><tr><td><img src="'.base_url().'images/blindado.gif'.'" height="60px" ></td><td bgcolor="#ddeedd">';
		$data['content'].= '<p>'.anchor('finanzas/bcaj/remesa','Remesas: ');
		$data['content'].= 'Cuando se entrega la relaci&oacute;n de cesta tickets a la empresa de valores de parte del Banco.</p>';

		$data['content'].= '</td></tr><tr><td colspan=2 align="center">'.anchor('finanzas/bcaj/index'        ,'Regresar').br();
		$data['content'].= '</td></tr></table>'.br();
		
		$data['title']   = heading('Selecciona la operaci&oacute;n que desea realizar');
		$data['head']    = $this->rapyd->get_head();  //.phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function remesa(){
		$this->rapyd->load('dataform');

		$edit = new DataForm('finanzas/bcaj/depositoefe/process');
		$edit->title('Remesa de Valores');

		$edit->numero = new inputField2('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->readonly=TRUE;

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size = 10;
		$edit->fecha->rule = 'chfecha|required';

		$edit->envia = new dropdownField('Caja','envia');
		$edit->envia->option('','Seleccionar');
		$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';
		$edit->envia->options( "SELECT TRIM(codbanc) AS codbanc,$desca FROM banc WHERE tbanco='CAJ'");
		$edit->envia->rule   = 'required';
		$edit->envia->style  = 'width:180px';

		$edit->boleta = new inputField('Nro de Boleta', 'boleta');
		$edit->boleta->rule='required';
		$edit->boleta->size=20;

		$edit->precinto = new inputField('Nro de Precinto', 'precinto');
		$edit->precinto->rule='required';
		$edit->precinto->size=20;

		$edit->comprob = new inputField('Comp. de Servicio', 'comprob');
		$edit->comprob->rule='required';
		$edit->comprob->size=20;

		$script='
			function totaliza(){
				if($("#efectivo").val().length>0) efectivo=parseFloat($("#efectivo").val()); else efectivo=0;
				if($("#cheques").val().length>0)  cheques =parseFloat($("#cheques").val());  else cheques =0;
				monto   =efectivo+cheques;
				$("#monto").val(roundNumber(monto,2));
			}';

		$script='';

		//$this->rapyd->jquery[]='$("#cheques,#efectivo").bind("keyup",function() { totaliza(); });';
		//$edit->script($script);

		$obj = 'monto';
		$edit->$obj = new inputField("Monto Bruto: ", $obj);
		$edit->$obj->css_class='inputnum';
		$edit->$obj->rule='trim|numeric';
		$edit->$obj->maxlength =15;
		$edit->$obj->size = 20;
		$edit->$obj->group = 'Montos';
		$edit->$obj->autocomplete=false;


		//$edit->$obj->readonly=true;
		//$edit->recibe->style = 'width:180px';

		$numero=$edit->_dataobject->get('numero');
		$detalle = new DataDetalle($edit->_status);

			//Campos para el detalle
			$detalle->db->select('numero,tipo,concep, denomi, cantidad, monto ');
			$detalle->db->from('itbcaj');
			$detalle->db->where("numero='$numero'");

			$detalle->codigo = new inputField2("Tipo", "tipo<#i#>");
			$detalle->codigo->size=3;
			$detalle->codigo->db_name='tipo';
			$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
			$detalle->codigo->readonly=TRUE;

			$detalle->concep = new inputField("Conc.", "concep<#i#>");
			$detalle->concep->size=15;
			$detalle->concep->db_name='concep';
			$detalle->concep->maxlength=12;

			$detalle->denomi = new inputField("Denom", "denomi<#i#>");
			$detalle->denomi->css_class='inputnum';
			$detalle->denomi->size=20;
			$detalle->denomi->db_name='denomi';

			$detalle->cantidad = new inputField("Cant", "cantidad<#i#>");
			$detalle->cantidad->css_class='inputnum';
			$detalle->cantidad->size=20;
			$detalle->cantidad->db_name='cantidad';

			$detalle->monto = new inputField("Monto", "monto<#i#>");
			$detalle->monto->css_class='inputnum';
			$detalle->monto->size=20;
			$detalle->monto->db_name='monto';
			//fin de campos para detalle

			//Columnas del detalle
			$detalle->column("Tipo","<#tipo#><#concep#><#denomi#><#cantidad#><#monto#>");
			//$detalle->column("Descripci&oacute;n","<#descrip#>");
			//$detalle->column("Cantidad"          ,"<#cantidad#>");
			$detalle->build();

		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$back_url = site_url('finanzas/bcaj/agregar');
		$edit->button('btn_undo', 'Regresar', "javascript:window.location='${back_url}'", 'TR');

		$edit->submit('btnsubmit','Guardar');
		$edit->build_form();

		//**********************
		//  Guarda el efecto
		//**********************
		if ($edit->on_success()){
			$fecha   = $edit->fecha->newValue;
			$envia   = $edit->envia->newValue;
			$recibe  = $edit->recibe->newValue;
			$numeror = $edit->numeror->newValue;
			$efectivo= $edit->efectivo->newValue;
			$cheque  = $edit->cheques->newValue;

			$rt=$this->_transferendepefe($fecha,$efectivo,$cheque,$envia,$recibe,$numeror);
			if($rt){
				redirect('finanzas/bcaj/listo/n/'.$this->bcajnumero);
			}else{
				redirect('finanzas/bcaj/listo/s');
			}
		}

		$data['content'] = $edit->output;
		$data['title']   = heading('Deposito');
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function transferencia(){
		$this->rapyd->load('dataform');

		$edit = new DataForm('finanzas/bcaj/transferencia/process');
		$edit->title='Deposito en caja';
		$link  = site_url('finanzas/bcaj/get_trrecibe');
		$script='
		function get_trrecibe(){
			if($("#envia").val().length>0){
				$.post("'.$link.'",{ envia: $("#envia").val()}, function(data){
					//alert(data);
					$("#recibe").html(data);
				});
			}
		}';
		$edit->script($script);

		$edit->back_url = site_url('finanzas/bcaj/index');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'chfecha|required';

		$edit->envia = new dropdownField('Envia y N&uacute;mero','envia');
		$edit->envia->option('','Seleccionar');

		$edit->numeroe = new inputField('N&uacute;mero de envio', 'numeroe');
		$edit->numeroe->in='envia';
		$edit->numeroe->rule='condi_required|callback_chnumeroe';
		$edit->numeroe->size=20;
		$edit->numeroe->append('Solo si el que env&iacute;a es un banco');

			/*if($row->tipo=='DE'){
				$formato='BANCAJA';
			}if($row->tipo=='TR' && $row->recibe=='CAJ' && $row->envia=='CAJ'){
				$formato='BTRANCJ';
			}if($row->recibe!='CAJ' && $row->envia!='CAJ' && $row->tipoe=='ND' && $row->tipor=='NC'){
				$formato='BTRANND';
			}if($row->recibe!='CAJ' && $row->envia!='CAJ' && $row->tipoe=='CH' && $row->tipor=='DE'){
				$formato='BTRANCH';
			}*/

		$env=$this->input->post('envia');
		$edit->recibe = new dropdownField('Recibe y N&uacute;mero','recibe');
		$edit->recibe->option('','Seleccionar');
		if($env!==false){
			$tipo  = $this->_traetipo($env);
			$ww    = ($tipo=='CAJ') ? 'AND tbanco="CAJ"' : '';
			$desca = 'CONCAT_WS(\'-\',codbanc,banco) AS desca';
			$edit->recibe->options("SELECT codbanc,$desca FROM banc WHERE codbanc<>".$this->db->escape($env)." $ww ORDER BY banco");
		}

		$edit->numeror = new inputField('N&uacute;mero de envio', 'numeror');
		$edit->numeror->in='recibe';
		$edit->numeror->rule='condi_required|callback_chnumeror';
		$edit->numeror->size=20;
		$edit->numeror->append('Solo si el que recibe es un banco');

		$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';
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

		$edit->tipoe = new dropdownField('Documento de emisi&oacute;n','tipoe');
		$edit->tipoe->option('ND','Nota de debito');
		$edit->tipoe->option('CH','Cheque');
		$edit->tipoe->rule='condi_required|callback_chtipoe';
		$edit->tipoe->style  = 'width:180px';

		$edit->moneda = new dropdownField('Moneda','moneda');
		$edit->moneda->options('SELECT moneda,descrip FROM mone ORDER BY descrip');
		$edit->moneda->style  = 'width:180px';

		$edit->monto = new inputField('Monto', 'monto');
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric|required';
		$edit->monto->maxlength =15;
		$edit->monto->size = 20;
		$edit->monto->autocomplete=false;

		$edit->envia->rule   = 'required';
		$edit->envia->style  = 'width:180px';
		$edit->recibe->style = 'width:180px';

		$back_url = site_url('finanzas/bcaj/agregar');
		$edit->button('btn_undo', 'Regresar', "javascript:window.location='${back_url}'", 'BL');

		$edit->submit('btnsubmit','Guardar');
		$edit->build_form();

		if ($edit->on_success()){
			$fecha  = $edit->fecha->newValue;
			$monto  = $edit->monto->newValue;
			$envia  = $edit->envia->newValue;
			$recibe = $edit->recibe->newValue;
			$numeror= $edit->numeror->newValue;
			$numeroe= $edit->numeroe->newValue;
			$tipoe  = $edit->tipoe->newValue;
			$moneda = $edit->moneda->newValue;
			$rt=$this->_transferencaj($fecha,$monto,$envia,$recibe,false,$numeror,$numeroe,$tipoe,$moneda);
			if($rt){
				redirect('/finanzas/bcaj/listo/n/'.$this->bcajnumero);
			}else{
				redirect('/finanzas/bcaj/listo/s');
			}
		}

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		//$this->rapyd->jquery[]='get_trrecibe();';
		$data['content'] = $edit->output;
		$data['title']   = heading('Transferencias');
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function depositoefe(){
		$this->rapyd->load('dataform');

		$edit = new DataForm('finanzas/bcaj/depositoefe/process');
		$edit->title('Deposito de efectivo');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size = 10;
		$edit->fecha->rule = 'chfecha|required';

		$edit->envia = new dropdownField('Envia','envia');
		$edit->envia->option('','Seleccionar');

		$edit->recibe = new dropdownField('Recibe','recibe');
		$edit->recibe->option('','Seleccionar');


		$edit->numeror = new inputField('N&uacute;mero de deposito', 'numeror');
		$edit->numeror->rule='required';
		$edit->numeror->size=20;

		$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';
		$script='
			function totaliza(){
				if($("#efectivo").val().length>0) efectivo=parseFloat($("#efectivo").val()); else efectivo=0;
				if($("#cheques").val().length>0)  cheques =parseFloat($("#cheques").val());  else cheques =0;
				monto   =efectivo+cheques;
				$("#monto").val(roundNumber(monto,2));
			}';

		$this->rapyd->jquery[]='$("#cheques,#efectivo").bind("keyup",function() { totaliza(); });';
		$edit->script($script);

		$edit->envia->options( "SELECT TRIM(codbanc) AS codbanc,$desca FROM banc WHERE tbanco='CAJ'");

		$edit->recibe->options("SELECT TRIM(codbanc) AS codbanc,$desca FROM banc WHERE tbanco<>'CAJ'");
		$edit->recibe->rule='callback_chtr|required';

		$campos=array(
				'cheques' =>'Cheques',
				'efectivo'=>'Efectivo',
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

		$edit->envia->rule   = 'required';
		$edit->envia->style  = 'width:180px';
		$edit->recibe->style = 'width:180px';

		$back_url = site_url('finanzas/bcaj/agregar');
		$edit->button('btn_undo', 'Regresar', "javascript:window.location='${back_url}'", 'TR');

		$edit->submit('btnsubmit','Guardar');
		$edit->build_form();

		//**********************
		//  Guarda el efecto
		//**********************
		if ($edit->on_success()){
			$fecha   = $edit->fecha->newValue;
			$envia   = $edit->envia->newValue;
			$recibe  = $edit->recibe->newValue;
			$numeror = $edit->numeror->newValue;
			$efectivo= $edit->efectivo->newValue;
			$cheque  = $edit->cheques->newValue;

			$rt=$this->_transferendepefe($fecha,$efectivo,$cheque,$envia,$recibe,$numeror);
			if($rt){
				redirect('finanzas/bcaj/listo/n/'.$this->bcajnumero);
			}else{
				redirect('finanzas/bcaj/listo/s');
			}
		}

		$data['content'] = $edit->output;
		$data['title']   = heading('Deposito');
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function depositotar(){
		$this->rapyd->load('dataform');

		$edit = new DataForm('finanzas/bcaj/depositotar/process');
		$edit->title('Deposito en caja de tarjetas');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size = 10;
		$edit->fecha->rule = 'chfecha|required';

		$edit->envia = new dropdownField('Envia','envia');
		$edit->envia->option('','Seleccionar');

		$edit->recibe = new dropdownField('Recibe','recibe');
		$edit->recibe->option('','Seleccionar');

		$edit->tipo = new dropdownField('Tipo','tipo');
		$edit->tipo->option('NC','Nota de credito');
		$edit->tipo->option('DE','Deposito');

		$edit->numero = new inputField('N&uacute;mero de deposito', 'numero');
		$edit->numero->rule='required';
		$edit->numero->size=20;

		$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';
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

					islr    =tarjeta*10/(100+tasa);
					islr    =islr*(im/10);
					comision=tarjeta*(tc/100)+tdebito*(td/100);
					monto   =tarjeta+tdebito-comision-islr;

					$("#monto").val(roundNumber(monto,2));
					$("#comision").val(roundNumber(comision,2));
					$("#islr").val(roundNumber(islr,2));
				}
			}

			function totaliza(){
				if($("#tarjeta").val().length>0)  tarjeta =parseFloat($("#tarjeta").val());  else tarjeta =0;
				if($("#tdebito").val().length>0)  tdebito =parseFloat($("#tdebito").val());  else tdebito =0;
				if($("#comision").val().length>0) comision=parseFloat($("#comision").val()); else comision=0;
				if($("#islr").val().length>0)     islr    =parseFloat($("#islr").val());     else     islr=0;
				monto   =tarjeta+tdebito-comision-islr;
				$("#monto").val(roundNumber(monto,2));
			}';

		$this->rapyd->jquery[]='$("#tarjeta,#tdebito").bind("keyup",function() { calcomis(); });';
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

		$edit->envia->rule   = 'required';
		$edit->envia->style  = 'width:180px';
		$edit->recibe->style = 'width:180px';

		$back_url = site_url('finanzas/bcaj/agregar');
		$edit->button('btn_undo', 'Regresar', "javascript:window.location='${back_url}'", 'TR');

		$edit->submit('btnsubmit','Guardar');
		$edit->build_form();

		//**********************
		//  Guarda el efecto
		//**********************
		if ($edit->on_success()){
			$fecha   =$edit->fecha->newValue;
			$envia   =$edit->envia->newValue;
			$recibe  =$edit->recibe->newValue;
			$tarjeta =$edit->tarjeta->newValue;
			$tdebito =$edit->tdebito->newValue;
			$comision=$edit->comision->newValue;
			$islr    =$edit->islr->newValue;
			$numeror =$edit->numero->newValue;
			$tipo    =$edit->tipo->newValue;

			$rt=$this->_transferendeptar($fecha,$tarjeta,$tdebito,$comision,$islr,$envia,$recibe,$numeror,$tipo);
			if($rt){
				redirect('/finanzas/bcaj/listo/n/'.$this->bcajnumero);
			}else{
				redirect('/finanzas/bcaj/listo/s');
			}
		}

		$data['content'] = $edit->output;
		$data['title']   = heading('Deposito');
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}


	//Auto transferencia
	function autotranfer(){
		$this->rapyd->load('dataform');
		$edit = new DataForm('finanzas/bcaj/autotranfer/process');
		$edit->title='Transferencia automatica entre cajas';

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'chfecha|required';
		$edit->fecha->dbformat='Y-m-d';
		$edit->fecha->size=10;
		
		$back_url=site_url('finanzas/bcaj/agregar');
		$edit->button('btn_undo','Regresar',"javascript:window.location='$back_url'",'BL');
		$edit->submit('btnsubmit','Siguiente');
		$edit->build_form();
		if ($edit->on_success()){
			$fecha=$edit->fecha->newValue;
			redirect('finanzas/bcaj/autotranfer2/'.$fecha);
		}

		$data['content'] = $edit->output;
		$data['title']   = heading('Conciliaci&oacute;n de cierre');
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function autotranfer2($fecha=null){
		//***************************
		$this->cajas=$this->config->item('cajas');
		foreach($this->cajas AS $inv=>$val){
			$codban=$this->db->escape($val);
			$cana=$this->datasis->dameval("SELECT COUNT(*) AS cana FROM banc WHERE codbanc=$codban");
			if($cana==0){
				show_error('La caja '.$val.' no esta registrada en el sistema, debe registrarla por el modulo de bancos o ajustar la configuracion en config/datasis.php');
			}
		}
		//***************************

		$this->rapyd->load('dataform');
		$this->load->library('validation');
		$val=$this->validation->chfecha($fecha,'Y-m-d');
		if($val){
			$montosis=$this->_montoautotranf($this->cajas['cobranzas'],$fecha);
			if($montosis>0){

				$script='
					function totaliza(){
						if($("#tarjeta").val().length>0)  tarjeta =parseFloat($("#tarjeta").val());  else tarjeta =0;
						if($("#efectivo").val().length>0) efectivo=parseFloat($("#efectivo").val()); else efectivo=0;
						if($("#gastos").val().length>0)   gastos  =parseFloat($("#gastos").val());   else gastos  =0;
						if($("#valores").val().length>0)  valores =parseFloat($("#valores").val());  else valores =0;
						monto=tarjeta+gastos+efectivo+valores;
						$("#monto").val(roundNumber(monto,2));
					}';

				$edit = new DataForm("finanzas/bcaj/autotranfer2/$fecha/process");
				$edit->title='Transferencia automatica entre cajas';
				$edit->script($script);

				//$edit->back_url = site_url('finanzas/bcaj/index');

				/*$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
				$edit->fecha->insertValue = date('Y-m-d');
				$edit->fecha->rule = 'chfecha|required';
				$edit->fecha->dbformat='Y-m-d';
				$edit->fecha->append(HTML::button('traesaldo', 'Consultar monto', '', 'button', 'button'));
				$edit->fecha->size=10;*/

				$campos=array(
					'efectivo'=>'Efectivo caja: '.$this->cajas['efectivo'],
					'tarjeta' =>'Tarjeta de D&eacute;bito y Cr&eacute;dito caja: '.$this->cajas['tarjetas'],
					'gastos'  =>'Gastos por Justificar caja: '.$this->cajas['gastos'],
					'valores' =>'Valores, Cesta Tickes y Cheques caja: '.$this->cajas['valores'],
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
				$edit->$obj->rule='trim|numeric|callback_chtotal|required';
				$edit->$obj->readonly=true;

				$back_url=site_url('finanzas/bcaj/index');
				$edit->button('btn_undo','Regresar',"javascript:window.location='$back_url'",'BL');
				$edit->submit('btnsubmit','Guardar');
				$edit->build_form();

				$salida  = 'El monto total a tranferir para la fecha <b id="ffecha">'.dbdate_to_human($fecha).'</b> debe ser de: <b id="mmonto">'.nformat($montosis).'</b>';
				if ($edit->on_success()){
					//$fecha=$edit->fecha->newValue;
					foreach($campos AS $obj=>$titulo){
						$$obj=$edit->$obj->newValue;
					}
					if( round($montosis,2) == round($efectivo+$tarjeta+$gastos+$valores,2)) {
						$rt=$this->_autotranfer($fecha,$efectivo,$tarjeta,$gastos,$valores);
						if($rt){
							redirect('/finanzas/bcaj/listo/n/'.$this->bcajnumero);
						}else{
							redirect('/finanzas/bcaj/listo/s');
						}
					}else{
						$edit->error_string='El monto total a transferir debe ser de :<b>'.nformat($montosis).'</b>, faltan '.nformat($montosis-$efectivo-$tarjeta-$gastos-$valores);
						$edit->build_form();
						//$salida .= $edit->output;
					}
				}
				$salida .= $edit->output;

				$url=site_url('finanzas/bcaj/ajaxmonto');
				$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
				$this->rapyd->jquery[]='$(".inputnum").bind("keyup",function() { totaliza(); });';
				$this->rapyd->jquery[]='$("td").removeAttr("style");';
				$this->rapyd->jquery[]='$("input[name=\'traesaldo\']").click(function() {
					fecha=$("#fecha").val();
					if(fecha.length > 0){
						$.post("'.$url.'", { fecha: $("#fecha").val() },
							function(data){
								$("#mmonto").html(nformat(data));
								$("#ffecha").html($("#fecha").val());
								$(".alert").hide("slow");
							});
					}else{
						alert("Debe introducir una fecha");
					}
					});';

			}else{
				$dbfecha=$this->db->escape($fecha);
				$mSQL = "SELECT COUNT(*) AS cana FROM bcaj WHERE concep2='AUTOTRANFER' AND fecha=$dbfecha";
				$cana = $this->datasis->dameval($mSQL);
				if($cana>0){
					$salida = 'Ya fue hecha una tranferencias para la fecha dada, si desea puede reversarla haciendo click '.anchor('finanzas/bcaj/reverautotranfer/'.$fecha,'aqui').' ';
					$salida.= ' o puede '.anchor('finanzas/bcaj/index','regresar').' al inicio.';
				}else{
					$salida = 'No hay monto disponible para transferir '.anchor('finanzas/bcaj/autotranfer','Regresar');
				}
			}
		}else{
			show_error('Falta el parametro fecha');
		}

		$data['content'] = $salida;
		$data['title']   = '<h1>Conciliaci&oacute;n de cierre </h1>';
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function ajaxmonto(){
		$fecha=$this->input->post('fecha');
		if($fecha!==false){
			$fecha=human_to_dbdate($fecha);
			$monto=$this->_montoautotranf($this->cajas['cobranzas'],$fecha);
		}else{
			$monto=0;
		}
		echo $monto;
	}

	//Metodo que reversa las tranferencias automaticas
	function reverautotranfer($fecha){
		$this->load->library('validation');
		$val  = $this->validation->chfecha($fecha,'Y-m-d');
		$error= 0;
		if($val){
			$rt=$this->_reverautotranfer($fecha);
			if($rt)
				redirect('finanzas/bcaj/listo/n');
			else
				redirect('finanzas/bcaj/listo/s');
		}
	}

	function _reverautotranfer($fecha){
		$dbfecha=$this->db->escape($fecha);
		$sp_fecha= str_replace('-','',$fecha);
		$mSQL="SELECT transac,monto,envia,recibe FROM bcaj WHERE fecha=$dbfecha AND concep2='AUTOTRANFER'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$transac=$this->db->escape($row->transac);
				$sql="DELETE FROM bmov WHERE transac=$transac";
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'bcaj'); $error++; }
				
				$monto=$row->monto;
				$sql='CALL sp_actusal('.$this->db->escape($row->envia).",'$sp_fecha',$monto)";
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'bcaj'); $error++; }

				$sql='CALL sp_actusal('.$this->db->escape($row->recibe).",'$sp_fecha',-$monto)";
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'bcaj'); $error++; }
			}
		}
		$sql="DELETE FROM bcaj WHERE fecha=$dbfecha AND concep2='AUTOTRANFER'";
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }
		return ($error==0)? true : false;
	}

	function _autotranfer($fecha,$efectivo=0,$tarjeta=0,$gastos=0,$valores=0){
		//$cajas=$this->config->item('cajas');
		$envia=$this->cajas['cobranzas'];
		$arr=array(
			'efectivo'=>$this->cajas['efectivo'],
			'tarjeta' =>$this->cajas['tarjetas'],
			'gastos'  =>$this->cajas['gastos'],
			'valores' =>$this->cajas['valores']
		);
		$rt=true;
		foreach($arr as $monto=>$recibe){
			if(!$this->_transferencaj($fecha,$$monto,$envia,$recibe,true))
				$rt=false;
		}
		return $rt;
	}


	function _transferencaj($fecha,$monto,$envia,$recibe,$auto=false,$numeror=null,$numeroe=null,$tipoe='ND',$moneda='Bs'){
		if($monto<=0) return true;
		$numero  = $this->datasis->fprox_numero('nbcaj');
		$transac = $this->datasis->fprox_numero('ntransa');
		$_numeroe= $this->datasis->banprox($envia);
		$_numeror= $this->datasis->banprox($recibe);
		$numeroe = ($_numeroe===false)? str_pad($numeroe, 12, '0', STR_PAD_LEFT): $_numeroe;
		$numeror = ($_numeror===false)? str_pad($numeror, 12, '0', STR_PAD_LEFT): $_numeror;
		$sp_fecha= str_replace('-','',$fecha);
		$tipor   = ($tipoe=='ND') ? 'NC': 'DE';
		$error  = 0;
		$this->bcajnumero=$numero;

		$mSQL='SELECT codbanc,numcuent,tbanco,banco,saldo FROM banc WHERE codbanc IN ('.$this->db->escape($envia).','.$this->db->escape($recibe).')';
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
			'envia'   => $envia,
			'tipoe'   => $tipoe,
			'numeroe' => $numeroe,
			'bancoe'  => $infbanc[$envia]['banco'],
			'recibe'  => $recibe,
			'tipor'   => $tipor,
			'numeror' => $numeror,
			'bancor'  => $infbanc[$recibe]['banco'],
			'concepto'=> 'TRANSFERENCIA ENTRE CAJA '.$envia.' A '.$recibe,
			'concep2' => ($auto)? 'AUTOTRANFER' : '',
			'benefi'  => '',
			'boleta'  => '',
			'precinto'=> '',
			'comprob' => '',
			'totcant' => '',
			'status'  => '',
			'estampa' => date('Ymd'),
			'hora'    => date('H:i:s'),
			'deldia'  => $fecha,
			'tarjeta' => 0,
			'tdebito' => 0,
			'cheques' => 0,
			'efectivo'=> $monto,
			'comision'=> 0,
			'islr'    => 0,
			'monto'   => $monto,
		);
		$sql=$this->db->insert_string('bcaj', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }

		//Crea el egreso en el banco
		$mSQL='CALL sp_actusal('.$this->db->escape($envia).",'$sp_fecha',-$monto)";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'bcaj'); $error++; }

		$data=array();
		$data['codbanc']  = $envia;
		$data['numcuent'] = $infbanc[$envia]['numcuent'];
		$data['banco']    = $infbanc[$envia]['banco'];
		$data['saldo']    = $infbanc[$envia]['saldo'];
		$data['tipo_op']  = $tipoe;
		$data['numero']   = $numeroe;
		$data['fecha']    = $fecha;
		$data['clipro']   = 'O';
		$data['codcp']    = 'TRANS';
		$data['monto']    = $monto;
		$data['concepto'] = 'TRANSFERENCIAS ENTRE CAJA '.$envia.' A '.$recibe;
		$data['concep2']  = '';
		$data['transac']  = $transac;
		$data['usuario']  = $this->session->userdata('usuario');
		$data['estampa']  = date('Ymd');
		$data['hora']     = date('H:i:s');
		$data['benefi']   = '-';
		$data['moneda']   = $moneda;
		$sql=$this->db->insert_string('bmov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }

		//Crea el ingreso la otra caja
		$mSQL='CALL sp_actusal('.$this->db->escape($recibe).",'$sp_fecha',$monto)";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'bcaj'); $error++; }

		$data=array();
		$data['codbanc']  = $recibe;
		$data['numcuent'] = $infbanc[$recibe]['numcuent'];
		$data['banco']    = $infbanc[$recibe]['banco'];
		$data['saldo']    = $infbanc[$recibe]['saldo'];
		$data['tipo_op']  = $tipor;
		$data['numero']   = $numeror;
		$data['fecha']    = $fecha;
		$data['clipro']   = 'O';
		$data['codcp']    = 'TRANS';
		$data['monto']    = $monto;
		$data['concepto'] = 'TRANSFERENCIAS ENTRE CAJA '.$envia.' A '.$recibe;
		$data['concep2']  = '';
		$data['transac']  = $transac;
		$data['usuario']  = $this->session->userdata('usuario');
		$data['estampa']  = date('Ymd');
		$data['hora']     = date('H:i:s');
		$data['benefi']   = '-';
		$data['moneda']   = $moneda;
		$sql=$this->db->insert_string('bmov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }
		logusu('bcaj',"Transferencia de caja $numero creada");

		return ($error==0) ? true : false;
	}

	function _transferendepefe($fecha, $efectivo, $cheque, $envia, $recibe, $numeror, $moneda='Bs'){
		$monto=$efectivo+$cheque;
		if($monto<=0) return true;
		$numero = $this->datasis->fprox_numero('nbcaj');
		$transac= $this->datasis->fprox_numero('ntransa');
		$numeroe= $this->datasis->banprox($envia);
		$numeroe = str_pad($numeroe, 12, '0', STR_PAD_LEFT);
		//$numeror = ($_numeror===false)? str_pad($numeror, 12, '0', STR_PAD_LEFT): $_numeror;


		//$numeror= $this->datasis->banprox($recibe);
		$sp_fecha= str_replace('-','',$fecha);
		$error  = 0;
		$this->bcajnumero=$numero;

		$mSQL='SELECT codbanc,numcuent,tbanco,banco,saldo FROM banc WHERE codbanc IN ('.$this->db->escape($envia).','.$this->db->escape($recibe).')';
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
			'tipo'    => 'DE',
			'fecha'   => $fecha,
			'numero'  => $numero,
			'transac' => $transac,
			'usuario' => $this->session->userdata('usuario'),
			'envia'   => $envia,
			'tipoe'   => 'ND',
			'numeroe' => $numeroe,
			'bancoe'  => $infbanc[$envia]['banco'],
			'recibe'  => $recibe,
			'tipor'   => 'DE',
			'numeror' => $numeror,
			'bancor'  => $infbanc[$recibe]['banco'],
			'concepto'=> 'DEPOSITO DE CAJA '.$envia.' A BANCO '.$recibe,
			'concep2' => ($auto)? 'AUTOTRANFER' : '',
			'benefi'  => '',
			'boleta'  => '',
			'precinto'=> '',
			'comprob' => '',
			'totcant' => '',
			'status'  => '',
			'estampa' => date('Ymd'),
			'hora'    => date('H:i:s'),
			'deldia'  => $fecha,
			'tarjeta' => 0,
			'tdebito' => 0,
			'cheques' => $cheque,
			'efectivo'=> $efectivo,
			'comision'=> 0,
			'islr'    => 0,
			'monto'   => $monto,
		);

		$sql=$this->db->insert_string('bcaj', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }

		//Crea el egreso en el banco
		$mSQL='CALL sp_actusal('.$this->db->escape($envia).",'$sp_fecha',-$monto)";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'bcaj'); $error++; }

		$data=array();
			$data['codbanc']  = $envia;
			$data['numcuent'] = $infbanc[$envia]['numcuent'];
			$data['banco']    = $infbanc[$envia]['banco'];
			$data['saldo']    = $infbanc[$envia]['saldo'];
			$data['tipo_op']  = 'ND';
			$data['numero']   = $numeroe;
			$data['fecha']    = $fecha;
			$data['clipro']   = 'O';
			$data['codcp']    = 'TRANS';
			$data['monto']    = $monto;
			$data['concepto'] = 'DEPOSITO DE CAJA '.$envia.' A BANCO '.$recibe;
			$data['concep2']  = '';
			$data['transac']  = $transac;
			$data['usuario']  = $this->session->userdata('usuario');
			$data['estampa']  = date('Ymd');
			$data['hora']     = date('H:i:s');
			$data['benefi']   = '-';
			$data['moneda']   = $moneda;

		$sql=$this->db->insert_string('bmov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }

		//Crea el ingreso la otra caja
		$mSQL='CALL sp_actusal('.$this->db->escape($recibe).",'$sp_fecha',$monto)";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'bcaj'); $error++; }

		$data=array();
		$data['codbanc']  = $recibe;
		$data['numcuent'] = $infbanc[$recibe]['numcuent'];
		$data['banco']    = $infbanc[$recibe]['banco'];
		$data['saldo']    = $infbanc[$recibe]['saldo'];
		$data['tipo_op']  = 'NC';
		$data['numero']   = $numeror;
		$data['fecha']    = $fecha;
		$data['clipro']   = 'O';
		$data['codcp']    = 'TRANS';
		$data['monto']    = $monto;
		$data['concepto'] = 'DEPOSITO DE CAJA '.$envia.' A BANCO '.$recibe;
		$data['concep2']  = '';
		$data['transac']  = $transac;
		$data['usuario']  = $this->session->userdata('usuario');
		$data['estampa']  = date('Ymd');
		$data['hora']     = date('H:i:s');
		$data['benefi']   = '-';
		$data['moneda']   = $moneda;
		$sql=$this->db->insert_string('bmov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }
		logusu("Transferencia de caja $numero creada");
		return ($error==0) ? true : false;
	}

	function _transferendeptar($fecha,$tarjeta,$tdebito,$comision,$islr,$envia,$recibe,$numeror,$tipo,$moneda='Bs'){
		$monto=$tarjeta+$tdebito;
		if($monto<=0) return true;
		$numero  = $this->datasis->fprox_numero('nbcaj');
		$transac = $this->datasis->fprox_numero('ntransa');
		$numeroe = $this->datasis->banprox($envia);
		$dbrecibe= $this->db->escape($recibe);
		$sp_fecha= str_replace('-','',$fecha);
		$error   = 0;
		$this->bcajnumero=$numero;

		$mSQL="SELECT a.tipotra ,a.formaca FROM tban AS a JOIN banc AS b ON a.cod_banc=b.tbanco WHERE a.cod_banc=$dbrecibe";
		$parr=$this->datasis->damerow($mSQL);
		$formaca=(empty($parr['formaca']) OR $parr['formaca']=='NETA')? 'NETA': 'BRUTA';

		$mSQL='SELECT codbanc,numcuent,tbanco,banco,saldo,codprv,gastocom,depto FROM banc WHERE codbanc IN ('.$this->db->escape($envia).','.$this->db->escape($recibe).')';
		$query = $this->db->query($mSQL);
		$infbanc=array();
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$infbanc[$row->codbanc]['numcuent' ]=$row->numcuent;
				$infbanc[$row->codbanc]['tbanco']   =$row->tbanco;
				$infbanc[$row->codbanc]['banco']    =$row->banco;
				$infbanc[$row->codbanc]['saldo']    =$row->banco;
				$infbanc[$row->codbanc]['codprv']   =$row->codprv;
				$infbanc[$row->codbanc]['gastocom'] =$row->gastocom;
				$infbanc[$row->codbanc]['depto']    =$row->depto;
			}
		}

		$data=array(
			'tipo'    => 'DE',
			'fecha'   => $fecha,
			'numero'  => $numero,
			'transac' => $transac,
			'usuario' => $this->session->userdata('usuario'),
			'envia'   => $envia,
			'tipoe'   => 'ND',
			'numeroe' => $numeroe,
			'bancoe'  => $infbanc[$envia]['banco'],
			'recibe'  => $recibe,
			'tipor'   => $tipo,
			'numeror' => $numeror,
			'bancor'  => $infbanc[$recibe]['banco'],
			'concepto'=> 'DEP/TARJETAS DE CAJA '.$envia.' A BANCO '.$recibe,
			'concep2' => '',
			'benefi'  => $this->datasis->traevalor('TITULO1'),
			'boleta'  => '',
			'precinto'=> '',
			'comprob' => '',
			'totcant' => 0,
			'status'  => '',
			'estampa' => date('Ymd'),
			'hora'    => date('H:i:s'),
			'deldia'  => $fecha,
			'tarjeta' => $tarjeta,
			'tdebito' => $tdebito,
			'cheques' => 0,
			'efectivo'=> 0,
			'comision'=> $comision,
			'islr'    => $islr,
			'monto'   => $tarjeta+$tdebito-$comision-$islr,
		);
		$sql=$this->db->insert_string('bcaj', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }

		//Crea el egreso en el banco
		$data=array();
		$data['codbanc']  = $envia;
		$data['numcuent'] = $infbanc[$envia]['numcuent'];
		$data['banco']    = $infbanc[$envia]['banco'];
		$data['saldo']    = $infbanc[$envia]['saldo'];
		$data['tipo_op']  = 'ND';
		$data['numero']   = $numeroe;
		$data['fecha']    = $fecha;
		$data['clipro']   = 'O';
		$data['codcp']    = 'CAJAS';
		$data['nombre']   = 'DEPOSITO DESDE CAJA';
		$data['monto']    = $tarjeta+$tdebito;
		$data['concepto'] = 'DEP/TARJETAS DE CAJA '.$envia.' A BANCO '.$recibe;
		$data['concep2']  = '';
		$data['comprob']  = $numero;
		$data['transac']  = $transac;
		$data['usuario']  = $this->session->userdata('usuario');
		$data['estampa']  = date('Ymd');
		$data['hora']     = date('H:i:s');
		$data['moneda']   = $moneda;
		$sql=$this->db->insert_string('bmov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }
		$mSQL='CALL sp_actusal('.$this->db->escape($envia).",'$sp_fecha',-$data[monto])";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'bcaj'); $error++; }

		//Crea el ingreso la otra caja

		$data=array();
		$data['codbanc']  = $recibe;
		$data['numcuent'] = $infbanc[$recibe]['numcuent'];
		$data['banco']    = $infbanc[$recibe]['banco'];
		$data['saldo']    = $infbanc[$recibe]['saldo'];
		$data['tipo_op']  = 'NC';
		$data['numero']   = $numeror;
		$data['fecha']    = $fecha;
		$data['clipro']   = 'O';
		$data['codcp']    = 'CAJAS';
		$data['comision'] = $comision;
		$data['impuesto'] = $islr;
		$data['monto']    = ($formaca=='NETA')?  $tarjeta+$tdebito-$islr-$comision : $tarjeta+$tdebito ;
		$data['nombre']   = 'DEPOSITO DESDE CAJA';
		$data['concepto'] = 'DEP/TARJETAS DE CAJA '.$envia.' A BANCO '.$recibe;;
		$data['concep2']  = '';
		$data['bruto']    = $tarjeta;
		$data['comprob']  = $numero;
		$data['documen']  = $numero;
		$data['transac']  = $transac;
		$data['usuario']  = $this->session->userdata('usuario');
		$data['estampa']  = date('Ymd');
		$data['hora']     = date('H:i:s');
		$data['moneda']   = $moneda;
		$sql=$this->db->insert_string('bmov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }

		$mSQL='CALL sp_actusal('.$this->db->escape($recibe).",'$sp_fecha',$data[monto])";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'bcaj'); $error++; }

		if($comision>0){
			if($formaca=='BRUTA'){
				$data=array();
				$data['codbanc']  = $recibe;
				$data['numcuent'] = $infbanc[$recibe]['numcuent'];
				$data['banco']    = $infbanc[$recibe]['banco'];
				$data['saldo']    = $infbanc[$recibe]['saldo'];
				$data['tipo_op']  = 'ND';
				$data['numero']   = 'C'.substr($numeror,1);
				$data['fecha']    = $fecha;
				$data['clipro']   = 'O';
				$data['codcp']    = 'CAJAS';
				$data['comision'] = $comision;
				$data['impuesto'] = $islr;
				$data['monto']    = $comision;
				$data['nombre']   = 'COMISION POR TC/TD';
				$data['concepto'] = 'COMISION POR TC/TD';
				$data['concep2']  = '';
				$data['bruto']    = $tarjeta;
				$data['comprob']  = $numero;
				$data['documen']  = $numero;
				$data['transac']  = $transac;
				$data['usuario']  = $this->session->userdata('usuario');
				$data['estampa']  = date('Ymd');
				$data['hora']     = date('H:i:s');
				$data['moneda']   = $moneda;
				$sql=$this->db->insert_string('bmov', $data);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'bcaj'); $error++; }
			}

			$data=array();
			$data['fecha']    = $fecha;
			$data['numero']   = 'CTC'.substr($numero,-5);
			$data['proveed']  = $infbanc[$recibe]['codprv'];
			$data['nombre']   = $this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($infbanc[$recibe]['codprv']));
			$data['vence']    = $fecha;
			$data['totpre']   = $comision;
			$data['totiva']   = 0;
			$data['totbruto'] = $comision;
			$data['reten']    = 0;
			$data['totneto']  = $comision;
			$data['codb1']    = $envia;
			$data['cheque1']  = $numeroe;
			$data['tipo1']    = 'D';
			$data['monto1']   = $comision;
			$data['codb2']    = '';
			$data['tipo2']    = '';
			$data['cheque2']  = '';
			$data['comprob2'] = '';
			$data['monto2']   = 0;
			$data['codb3']    = '';
			$data['tipo3']    = '';
			$data['cheque3']  = '';
			$data['comprob3'] = '';
			$data['monto3']   = 0;
			$data['credito']  = 0;
			$data['anticipo'] = 0;
			$data['orden']    = '';
			$data['tipo_doc'] = 'FC';
			$data['transac']  = $transac;
			$data['usuario']  = $this->session->userdata('usuario');
			$data['estampa']  = date('Ymd');
			$data['hora']     = date('H:i:s');
			$sql=$this->db->insert_string('gser', $data);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'bcaj'); $error++; }

			$data=array();
			$data['fecha']    = $fecha;
			$data['numero']   = 'CTC'.substr($numero,-5);
			$data['proveed']  = $infbanc[$recibe]['codprv'];
			$data['codigo']   = $infbanc[$recibe]['gastocom'];
			$data['descrip']  = 'COMISION POR TARJETAS '.$infbanc[$recibe]['banco'];
			$data['precio']   = $comision;
			$data['iva']      = 0;
			$data['importe']  = $comision;
			$data['unidades'] = 0;
			$data['fraccion'] = 0;
			$data['almacen']  = '';
			$data['departa']  = $infbanc[$recibe]['depto'];
			$data['sucursal'] = ' ';
			$data['transac']  = $transac;
			$data['usuario']  = $this->session->userdata('usuario');
			$data['estampa']  = date('Ymd');
			$data['hora']     = date('H:i:s');
			$sql=$this->db->insert_string('gitser', $data);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'bcaj'); $error++; }
		}

		if($islr>0){
			if($formaca=='BRUTA'){
				$data=array();
				$data['codbanc']  = $recibe;
				$data['numcuent'] = $infbanc[$recibe]['numcuent'];
				$data['banco']    = $infbanc[$recibe]['banco'];
				$data['saldo']    = $infbanc[$recibe]['saldo'];
				$data['tipo_op']  = 'ND';
				$data['numero']   = 'R'.substr($numeror,1);
				$data['fecha']    = $fecha;
				$data['clipro']   = 'O';
				$data['codcp']    = 'CAJAS';
				$data['comision'] = $comision;
				$data['impuesto'] = $islr;
				$data['monto']    = $islr;
				$data['nombre']   = 'RETENCION DE ISLR POR TC';
				$data['concepto'] = 'RETENCION DE ISLR POR TC';
				$data['concep2']  = '';
				$data['bruto']    = $tarjeta;
				$data['comprob']  = $numero;
				$data['documen']  = $numero;
				$data['transac']  = $transac;
				$data['usuario']  = $this->session->userdata('usuario');
				$data['estampa']  = date('Ymd');
				$data['hora']     = date('H:i:s');
				$data['moneda']   = $moneda;
				$sql=$this->db->insert_string('bmov', $data);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'bcaj'); $error++; }
			}
			$nccli = $this->datasis->fprox_numero('nccli');
			$nsmov = $this->datasis->fprox_numero('nsmov');
			$ff    = str_replace('-','',$fecha);
			$udia  = days_in_month(substr($ff,0,4),substr($ff,4,2));

			$data=array();
			$data['cod_cli']  = 'RETED';
			$data['nombre']   = 'RETENCION I.S.L.R. TDC/BANCOS';
			$data['tipo_doc'] = 'ND';
			$data['numero']   = $nccli;
			$data['fecha']    = $fecha;
			$data['monto']    = $islr;
			$data['impuesto'] = 0;
			$data['vence']    = substr($ff,0,6).$udia;
			$data['tipo_ref'] = 'DC';
			$data['num_ref']  = '';
			$data['observa1'] = 'RET/ISLR TC POR DEP '.$infbanc[$recibe]['banco'];
			$data['observa2'] = '';
			$data['control']  = $nsmov;
			$data['transac']  = $transac;
			$data['usuario']  = $this->session->userdata('usuario');
			$data['estampa']  = date('Ymd');
			$data['hora']     = date('H:i:s');
			$sql=$this->db->insert_string('smov', $data);
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'bcaj'); $error++; }
		}

		logusu('bcaj',"Transferencia de caja $numero creada");
		return ($error==0) ? true : false;
	}


	//Metodo para las tranferencias por deposito
	function _transferendep($fecha,$tarjeta,$tdebito,$cheque,$efectivo,$comision,$islr,$envia,$recibe,$moneda='Bs'){
		if($monto<=0) return true;
		$numero  = $this->datasis->fprox_numero('nbcaj');
		$transac = $this->datasis->fprox_numero('ntransa');
		$numeroe = $this->datasis->banprox($envia);
		$numeror = $this->datasis->banprox($recibe);
		$sp_fecha= str_replace('-','',$fecha);
		$error   = 0;
		$this->bcajnumero=$numero;

		$mSQL='SELECT codbanc,numcuent,tbanco,banco,saldo FROM banc WHERE codbanc IN ('.$this->db->escape($envia).','.$this->db->escape($recibe).')';
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

		$monto = $tarjeta+$tdebito+$cheques-$comision-$islr;
		$data=array(
			'tipo'    => 'DE',
			'fecha'   => $fecha,
			'numero'  => $numero,
			'transac' => $transac,
			'usuario' => $this->session->userdata('usuario'),
			'envia'   => $edit->envia->newValue,
			'tipoe'   => 'ND',
			'numeroe' => $numeroe,
			'bancoe'  => $infbanc[$envia]['banco'],
			'recibe'  => $edit->recibe->newValue,
			'tipor'   => 'NC',
			'numeror' => $numeror,
			'bancor'  => $infbanc[$recibe]['banco'],
			'concepto'=> 'DEPOSITO ENTRE '.$envia.' A '.$recibe,
			'concep2' => '',
			'benefi'  => '',
			'boleta'  => '',
			'precinto'=> '',
			'comprob' => '',
			'totcant' => '',
			'status'  => '',
			'deldia'  => $fecha,
			'tarjeta' => $edit->tarjeta->newValue,
			'tdebito' => $edit->tdebito->newValue,
			'cheques' => $edit->cheques->newValue,
			'efectivo'=> $edit->efectivo->newValue,
			'comision'=> $edit->comision->newValue,
			'islr'    => $edit->islr->newValue,
			'monto'   => $monto,
			'estampa' => date('Ymd'),
			'hora'    => date('H:i:s'),
		);

		$sql=$this->db->insert_string('bcaj', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }

		//Crea el egreso en el banco
		$mSQL='CALL sp_actusal('.$this->db->escape($envia).",'$sp_fecha',-$monto)";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'bcaj'); $error++; }

		$data=array();
		$data['codbanc']  = $envia;
		$data['numcuent'] = $infbanc[$envia]['numcuent'];
		$data['banco']    = $infbanc[$envia]['banco'];
		$data['saldo']    = $infbanc[$envia]['saldo'];
		$data['tipo_op']  = 'ND';
		$data['numero']   = $numeroe;
		$data['fecha']    = $fecha;
		$data['clipro']   = 'O';
		$data['codcp']    = 'TRANS';
		$data['monto']    = $monto;
		$data['concepto'] = 'DEPOSITO ENTRE CAJA '.$envia.' A '.$recibe;
		$data['concep2']  = '';
		$data['transac']  = $transac;
		$data['usuario']  = $this->session->userdata('usuario');
		$data['estampa']  = date('Ymd');
		$data['hora']     = date('H:i:s');
		$data['benefi']   = '-';
		$data['moneda']   = $moneda;
		$sql=$this->db->insert_string('bmov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }

		//Crea el ingreso la otra caja
		$mSQL='CALL sp_actusal('.$this->db->escape($recibe).",'$sp_fecha',$monto)";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'bcaj'); $error++; }

		$data=array();
		$data['codbanc']  = $recibe;
		$data['numcuent'] = $infbanc[$recibe]['numcuent'];
		$data['banco']    = $infbanc[$recibe]['banco'];
		$data['saldo']    = $infbanc[$recibe]['saldo'];
		$data['tipo_op']  = 'NC';
		$data['numero']   = $numeror;
		$data['fecha']    = $fecha;
		$data['clipro']   = 'O';
		$data['codcp']    = 'TRANS';
		$data['monto']    = $monto;
		$data['concepto'] = 'TRANSFERENCIAS ENTRE CAJA '.$envia.' A '.$recibe;
		$data['concep2']  = '';
		$data['transac']  = $transac;
		$data['usuario']  = $this->session->userdata('usuario');
		$data['estampa']  = date('Ymd');
		$data['hora']     = date('H:i:s');
		$data['benefi']   = '-';
		$data['moneda']   = $moneda;
		$sql=$this->db->insert_string('bmov', $data);
		$ban=$this->db->simple_query($sql);
		if($ban==false){ memowrite($sql,'bcaj'); $error++; }
		return ($error==0) ? true : false;
	}

	function _montoautotranf($caja,$fecha){
		$dbfecha=$this->db->escape($fecha);
		$dbcaja =$this->db->escape($caja);
		$mSQL="SELECT SUM(if(tipo_op IN ('NC','DE'),1,-1)*monto) AS monto FROM bmov WHERE codbanc=$dbcaja AND fecha=$dbfecha";
		$monto=$this->datasis->dameval($mSQL);
		return (empty($monto))? 0 : $monto;
	}

	function chnumeror($numero){
		$dbcodban=$this->db->escape($this->input->post('recibe'));
		$tipo=$this->datasis->dameval("SELECT tbanco FROM banc WHERE codbanc=$dbcodban");

		if($tipo!='CAJ' && empty($numero)){
			$this->validation->set_message('chnumeror', 'Cuando el que recibe es un banco es obligatorio el n&uacute;mero de deposito');
			return false;
		}else{
			return true;
		}
	}

	function chnumeroe($numero){
		$dbcodban=$this->db->escape($this->input->post('envia'));
		$tipo=$this->datasis->dameval("SELECT tbanco FROM banc WHERE codbanc=$dbcodban");

		if($tipo!='CAJ' && empty($numero)){
			$this->validation->set_message('chnumeroe', 'Cuando el que env&iacute;a es un banco es obligatorio el n&uacute;mero de deposito');
			return false;
		}else{
			return true;
		}
	}

	function chtipoe($tipoe){
		$eenvia = $this->input->post('envia');
		$envia  = $this->_traetipo($eenvia);

		if($envia=='CAJ' && $tipoe!='ND'){
			$this->validation->set_message('chtipoe', 'Cuando el que env&iacute;a es una caja la emisi&oacute;n debe ser por nota de d&eacute;bito');
			return false;
		}else{
			return true;
		}
	}

	function chtotal($monto){
		$monto =0;
		$monto+=floatval($this->input->post('efectivo'));
		$monto+=floatval($this->input->post('tarjeta' ));
		$monto+=floatval($this->input->post('gastos'  ));
		$monto+=floatval($this->input->post('valores' ));

		if($monto>0){
			return true;
		}else{
			$this->validation->set_message('chtotal', 'No puede guardar una transferencia en 0');
			return false;
		}
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
			$fecha  = $edit->fecha->newValue;
			$monto  = $edit->monto->newValue;
			$envia  = $edit->envia->newValue;
			$recibe = $edit->recibe->newValue;
			$rt=$this->_transferencaj($fecha,$monto,$envia,$recibe);
			if($rt){
				redirect('finanzas/bcaj/listo/n/'.$this->bcajnumero);
			}else{
				redirect('finanzas/bcaj/listo/s');
			}
		}

		$data=array();
		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $salida;
		$data['title']   = '<h1>Transferencias entre cajas</h1>';
		$data['head']    = $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _formato($numero){
		$dbnumero=$this->db->escape($numero);
		$mSQL  = "SELECT a.tipo,a.tipoe,a.tipor,TRIM(b.tbanco) AS envia, TRIM(c.tbanco) AS recibe FROM bcaj AS a JOIN banc AS b ON a.envia=b.codbanc JOIN banc AS c ON a.recibe=c.codbanc WHERE a.numero = $dbnumero";

		$query = $this->db->query($mSQL);
		$row   = $query->first_row();
		if ($query->num_rows() > 0){
			if($row->tipo=='DE'){
				$formato='BANCAJA';
			}elseif($row->tipo=='TR' && $row->recibe=='CAJ' && $row->envia=='CAJ'){
				$formato='BTRANCJ';
			}elseif($row->recibe!='CAJ' && $row->envia!='CAJ' && $row->tipoe=='ND' && $row->tipor=='NC'){
				$formato='BTRANND';
			}elseif($row->recibe!='CAJ' && $row->envia!='CAJ' && $row->tipoe=='CH' && $row->tipor=='DE'){
				$formato='BTRANCH';
			}
			return $formato;
		}
		return '';
	}

	function _imprimir($numero,$tipo){
		//Deposito BANCAJA
		//Transferencia entre cajas BTRANCJ
		//Transferencia con ND BTRANND
		//Transferencia con cheque BTRANCH
		$formato=$this->_formato($numero);
		return (!empty($formato))? site_url('formatos/'.$tipo.'/'.$formato.'/'.$numero) : '';
	}

	function listo($error, $numero=null){
		if($error=='n'){
			$data['content'] = 'Transacci&oacute;n completada ';
			if(!empty($numero)){
				$url=$this->_imprimir($numero,'ver');
				//$data['content'] .= ', puede <a href="#" onclick="fordi.print();">imprimirla</a>';
				$data['content'] .= ' '.anchor('finanzas/bcaj/agregar','Regresar');
				$data['content'] .= br()."<iframe name='fordi' src ='$url' width='10' height='10' style='display:none;'><p>Tu navegador no soporta iframes.</p></iframe>";
			}else{
				$data['content'] .= anchor('finanzas/bcaj/index','Regresar');
			}
		}else{
			$data['content'] = 'Lo siento pero hubo alg&uacute;n error en la transacci&oacute;n, se genero un centinela '.anchor('finanzas/bcaj/index','Regresar');
		}
		$data['title']   = '<h1>Transferencias entre cajas</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function get_trrecibe(){
		$codigo=$this->input->post('envia');
		echo "<option value=''>Seleccionar</option>";

		if($codigo!==false){
			$tipo= $this->_traetipo($codigo);

			if(!empty($tipo)){
				$ww=($tipo=='CAJ') ? 'AND tbanco="CAJ"' : '';
				$desca='CONCAT_WS(\'-\',codbanc,banco) AS desca';
				$mSQL=$this->db->query("SELECT codbanc,$desca FROM banc WHERE codbanc<>".$this->db->escape($codigo)." $ww ORDER BY banco");
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
}