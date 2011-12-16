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
		$this->rapyd->load('dataform','datagrid');
		//$this->rapyd->uri->keep_persistence();

		$filter = new DataForm('finanzas/conforch/filteredgrid/process');
		//$filter->title('Filtro de Cheques');

		$filter->valor = new inputField('N&uacute;mero o Beneficiario', 'valor');
		$filter->valor->rule ='required';
		$filter->valor->autocomplete=false;

		$filter->submit('btnsubmit','Buscar');
		$filter->build_form();

		$tabla='';
		if($filter->on_success()){
			$valor=$filter->valor->newValue;

			$grid = new DataGrid('Cheques emitidos','bmov');
			$grid->db->where('tipo_op','CH');
			$grid->db->like('CONCAT(`numero`,`benefi`)', $valor); 
			$grid->db->orderby('fecha','desc');
			$grid->db->limit(10);
			//$grid->per_page = 20;

			$uri = anchor('finanzas/conforch/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');
			//$grid->column("Nombre"      ,'nombre'  );
			$grid->column('Fecha'        ,'<dbdate_to_human><#fecha#></dbdate_to_human>');
			$grid->column('N&uacute;mero',$uri  );
			$grid->column('Beneficiario' ,'benefi'  ,'benefi');
			$grid->column('Monto'        ,'<nformat><#monto#></nformat>','align=\'right\'');
			$grid->column('Banco'        ,'banco'   );
			$grid->column('Concepto'     ,'concepto');
			$grid->column('Anulado'      ,'anulado' ,'align=\'center\'');
			$grid->build();
			//echo $grid->db->last_query();
			$tabla=$grid->output;
		}  

		$data['content'] = $filter->output.$tabla;
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