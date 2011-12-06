<?php
class Bmov extends Controller {
	function bmov(){
		parent::Controller(); 
		$this->load->library('rapyd');
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de movimientos de bancos');
		$select=array('fecha','numero','fecha','nombre','monto',"CONCAT_WS(' ',banco ,numcuent) AS banco",'tipo_op','codbanc','concepto','anulado');
		$filter->db->select($select);
		$filter->db->from('bmov');

		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->size     = 10;
		$filter->fecha->operator = '=';
		$filter->fecha->clause   = 'where';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=20;

		$filter->nombre = new inputField('Nombre', 'nombre');
		$filter->nombre->size=40;

		$filter->banco = new dropdownField('Banco', 'codbanc');
		$filter->banco->option('','Todos');
		$filter->banco->options("SELECT codbanc,banco FROM banc where tbanco<>'CAJ' ORDER BY codbanc");

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/bmov/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');
		$grid = new DataGrid('Lista');
		$grid->order_by('fecha','desc');
		$grid->per_page = 15;

		$grid->column_orderby('N&uacute;mero',$uri ,'numero');
		$grid->column_orderby('Fecha'        ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha');
		$grid->column_orderby('Nombre'       ,'nombre','nombre');
		$grid->column_orderby('Banco'        ,'banco','banco');
		$grid->column_orderby('Monto'        ,'<nformat><#monto#></nformat>' ,'monto','align=\'right\'');
		$grid->column_orderby('Concepto'     ,'concepto','concepto');
		$grid->column_orderby('Anulado'      ,'anulado','anulado','align=center');

		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Movimientos de bancos');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');
		$this->rapyd->uri->keep_persistence();

		$edit = new DataEdit('Movimiento', 'bmov');
		$edit->back_url = 'finanzas/bmov/index';
		$status=$edit->_status;
		if($status!='show') show_error('Error de par&aacute;metros');

		$edit->codbanc = new inputField('C&oacute;digo del Banco','codbanc');
		$edit->codbanc->rule='max_length[2]';
		$edit->codbanc->size =4;
		$edit->codbanc->maxlength =2;

		$edit->moneda = new inputField('Moneda','moneda');
		$edit->moneda->rule='max_length[2]';
		$edit->moneda->size =4;
		$edit->moneda->maxlength =2;

		$edit->numcuent = new inputField('N&uacute;mero de cuenta','numcuent');
		$edit->numcuent->rule='max_length[18]';
		$edit->numcuent->size =20;
		$edit->numcuent->maxlength =18;

		$edit->banco = new inputField('Banco','banco');
		$edit->banco->rule='max_length[30]';
		$edit->banco->size =32;
		$edit->banco->maxlength =30;

		$edit->saldo = new inputField('Saldo','saldo');
		$edit->saldo->rule='max_length[17]|numeric';
		$edit->saldo->css_class='inputnum';
		$edit->saldo->size =19;
		$edit->saldo->maxlength =17;

		$edit->tipo_op = new dropdownField('Tipo de operaci&oacute;n', 'tipo_op');
		$edit->tipo_op->option('NC','Nota de cr&eacute;dito');
		$edit->tipo_op->option('ND','Nota de d&eacute;bito');
		$edit->tipo_op->option('CH','Cheque');
		$edit->tipo_op->option('DE','Deposito');
		$edit->tipo_op->mode='autohide';
		$edit->tipo_op->rule='max_length[2]';

		$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->rule='max_length[12]';
		$edit->numero->size =14;
		$edit->numero->maxlength =12;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->clipro = new inputField('Clte/Prv','clipro');
		$edit->clipro->rule='max_length[1]';
		$edit->clipro->size =3;
		$edit->clipro->maxlength =1;

		$edit->codcp = new inputField('codcp','codcp');
		$edit->codcp->rule='max_length[5]';
		$edit->codcp->size =7;
		$edit->codcp->maxlength =5;
		$edit->codcp->in='clipro';

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[30]';
		$edit->nombre->size =32;
		$edit->nombre->maxlength =30;
		$edit->nombre->in='clipro';

		$edit->monto = new inputField('Monto','monto');
		$edit->monto->rule='max_length[17]|numeric';
		$edit->monto->css_class='inputnum';
		$edit->monto->size =19;
		$edit->monto->maxlength =17;

		$edit->concepto = new inputField('Concepto','concepto');
		$edit->concepto->rule='max_length[50]';
		$edit->concepto->size =52;
		$edit->concepto->maxlength =50;
		$edit->concepto->group='Conceptos';

		$edit->concep2 = new inputField('...','concep2');
		$edit->concep2->rule='max_length[50]';
		$edit->concep2->size =52;
		$edit->concep2->maxlength =50;
		$edit->concep2->group='Conceptos';

		$edit->concep3 = new inputField('...','concep3');
		$edit->concep3->rule='max_length[50]';
		$edit->concep3->size =52;
		$edit->concep3->maxlength =50;
		$edit->concep3->group='Conceptos';

		$edit->documen = new inputField('Documento','documen');
		$edit->documen->rule='max_length[8]';
		$edit->documen->size =10;
		$edit->documen->maxlength =8;

		$edit->comprob = new inputField('Comprobante','comprob');
		$edit->comprob->rule='max_length[6]';
		$edit->comprob->size =8;
		$edit->comprob->maxlength =6;

		$edit->status = new inputField('Estatus','status');
		$edit->status->rule='max_length[1]';
		$edit->status->size =3;
		$edit->status->maxlength =1;

		$edit->cuenta = new inputField('Cuenta','cuenta');
		$edit->cuenta->rule='max_length[15]';
		$edit->cuenta->size =17;
		$edit->cuenta->maxlength =15;

		$edit->enlace = new inputField('Enlace','enlace');
		$edit->enlace->rule='max_length[15]';
		$edit->enlace->size =17;
		$edit->enlace->maxlength =15;

		$edit->bruto = new inputField('bruto','bruto');
		$edit->bruto->rule='max_length[17]|numeric';
		$edit->bruto->css_class='inputnum';
		$edit->bruto->size =19;
		$edit->bruto->maxlength =17;

		$edit->comision = new inputField('Comisi&oacute;n','comision');
		$edit->comision->rule='max_length[17]|numeric';
		$edit->comision->css_class='inputnum';
		$edit->comision->size =19;
		$edit->comision->maxlength =17;

		$edit->impuesto = new inputField('Impuesto','impuesto');
		$edit->impuesto->rule='max_length[17]|numeric';
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->size =19;
		$edit->impuesto->maxlength =17;

		$edit->registro = new inputField('Registro','registro');
		$edit->registro->rule='max_length[10]';
		$edit->registro->size =12;
		$edit->registro->maxlength =10;

		$edit->concilia = new dateField('Conciliado','concilia');
		$edit->concilia->rule='chfecha';
		$edit->concilia->size =10;
		$edit->concilia->maxlength =8;

		$edit->benefi = new inputField('Beneficiario','benefi');
		$edit->benefi->rule='max_length[40]';
		$edit->benefi->size =42;
		$edit->benefi->maxlength =40;

		$edit->posdata = new dateField('Posdata','posdata');
		$edit->posdata->rule='chfecha';
		$edit->posdata->size =10;
		$edit->posdata->maxlength =8;

		$edit->abanco = new inputField('abanco','abanco');
		$edit->abanco->rule='max_length[1]';
		$edit->abanco->size =3;
		$edit->abanco->maxlength =1;

		$edit->liable = new dropdownField('Liable','liable');
		$edit->liable->option('S','S&iacute;');
		$edit->liable->option('N','No;');
		$edit->liable->rule='max_length[1]';

		$edit->tipo_op->option('ND','Nota de d&eacute;bito');
		$edit->transac = new inputField('Transacci&oacute;n','transac');
		$edit->transac->rule='max_length[8]';
		$edit->transac->size =10;
		$edit->transac->maxlength =8;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));

		$edit->hora    = new autoUpdateField('hora',date('H:m:s'), date('H:m:s'));

		$edit->anulado = new inputField('Anulado','anulado');
		$edit->anulado->rule='max_length[1]';
		$edit->anulado->size =3;
		$edit->anulado->maxlength =1;

		$edit->negreso = new inputField('N&uacute;mmero de egreso','negreso');
		$edit->negreso->rule='max_length[8]';
		$edit->negreso->size =10;
		$edit->negreso->maxlength =8;

		$edit->ndebito = new inputField('N&uacute;mmero d&eacute;bito','ndebito');
		$edit->ndebito->rule='max_length[8]';
		$edit->ndebito->size =10;
		$edit->ndebito->maxlength =8;

		$edit->ncausado = new inputField('N&uacute;mmero causado','ncausado');
		$edit->ncausado->rule='max_length[8]';
		$edit->ncausado->size =10;
		$edit->ncausado->maxlength =8;

		$edit->ncredito = new inputField('N&uacute;mmero cr&eacute;dito','ncredito');
		$edit->ncredito->rule='max_length[8]';
		$edit->ncredito->size =10;
		$edit->ncredito->maxlength =8;

		$edit->buttons('undo', 'back');
		$edit->build();
		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Movimientos de bancos');
		$this->load->view('view_ventanas', $data);
	}
}