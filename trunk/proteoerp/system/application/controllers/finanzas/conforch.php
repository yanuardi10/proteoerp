<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Conforch extends Validaciones {
	function Conforch() {
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index() {
		$this->datasis->modulo_id('51B',1);
		redirect('finanzas/conforch/filteredgrid');
	}

	function filteredgrid() {
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Cheques', 'bmov');
		$filter->db->where('tipo_op','CH');
		$filter->db->orderby('fecha','desc');

		$filter->numero = new inputField('N&uacute;mero de cheque', 'numero');
		$filter->numero->size=15;
		$filter->numero->maxsize=12;

		$filter->banco = new dropdownField('Banco', 'codbanc');
		$filter->banco->option('','Todos');
		$filter->banco->options('SELECT codbanc,CONCAT_WS(\' \',banco,numcuent) AS val FROM banc where tbanco<>\'CAJ\' ORDER BY banco');
		$filter->banco->style  = 'width:400px';

		$filter->benefi = new inputField('Beneficiario', 'benefi');
		$filter->benefi->maxsize=40;

		$filter->anulado = new dropdownField('Anulado', 'anulado');
		$filter->anulado->option('','Todos');
		$filter->anulado->option('S','Si');
		$filter->anulado->option('N','No');
		$filter->anulado->style  = 'width:80px';

		$filter->buttons('reset','search');
		$filter->build();

		$grid = new DataGrid('Cheques emitidos');
		$grid->per_page = 10;

		$uri = anchor('finanzas/conforch/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');
		//$grid->column("Nombre"      ,'nombre'  );
		$grid->column_orderby('Fecha'        ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha');
		$grid->column_orderby('N&uacute;mero',$uri  ,'numero');
		$grid->column_orderby('Beneficiario' ,'benefi'  ,'benefi');
		$grid->column_orderby('Monto'        ,'<nformat><#monto#></nformat>','monto','align=\'right\'');
		$grid->column_orderby('Banco'        ,'banco'   ,'banco');
		$grid->column_orderby('Concepto'     ,'concepto','concepto');
		$grid->column_orderby('Anulado'      ,'anulado'  ,'anulado','align=\'center\'');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Conformaci&oacute;n de Cheques');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');
		$this->rapyd->uri->keep_persistence();

		$edit = new DataEdit('Cheque emitido', 'bmov');
		$status=$edit->_status;
		if($status!='show') show_error('Error de par&aacute;metros');
		$edit->back_url = site_url('finanzas/conforch/filteredgrid');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');

		$edit->benefi   = new inputField('A nombre de', "benefi");
		$edit->benefi->group='Detalle del cheque';
		$edit->banco    = new inputField("Del Banco", "banco");
		$edit->banco->group='Detalle del cheque';
		$edit->numcuent = new inputField("N&uacute;mero Cuenta", "numcuent");
		$edit->numcuent->in = 'banco';
		$edit->numcuent->group='Detalle del cheque';
		$edit->numero   = new inputField("N&uacute;mero de cheque", "numero");
		$edit->numero->group='Detalle del cheque';
		$edit->monto    = new inputField("Por un Monto de", "monto");
		$edit->monto->group='Detalle del cheque';
		
		$edit->nombre   = new inputField('Proveedor', 'nombre');
		$edit->concepto = new inputField("Por Concepto", "concepto");
		$edit->anulado  = new inputField("Anulado", "anulado");

		$edit->buttons('back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = heading('Consulta de Cheques');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}
}