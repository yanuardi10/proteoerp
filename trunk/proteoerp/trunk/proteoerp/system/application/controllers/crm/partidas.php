<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class partidas extends validaciones {

	function partidas(){
		parent::Controller(); 
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(136,1);
	}
	function index(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter('Filtro','crm_partidas');

		$filter->codigo =  new inputField('C&oacute;digo', 'codigo');
    $filter->codigo->size = 10;
   
		$filter->descripcion =  new inputField('Descripci&oacute;n', 'descripcion');
		$filter->descripcion->size = 20;
		
		$filter->enlace =  new inputField('Enlace Administrativo', 'enlace');
		$filter->enlace->size = 7;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri  = anchor('crm/partidas/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid('Lista');
		$grid->order_by('codigo','asc');
		$grid->per_page = 7;

		$grid->column('codigo',$uri);
		$grid->column('Descripci&oacute;n' ,'descripcion');
		$grid->column('Enlace Administrativo','enlace');
		$grid->column('Medida'      ,'medida');
		$grid->column('Iva'         ,'iva');
	
		$grid->add('crm/partidas/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Partidas de Contratos</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Partidas','crm_partidas');

		$edit->back_url = site_url('crm/partidas/');

		$edit->codigo =  new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->rule = 'trim|required|max_length[15]';

		$edit->descripcion =  new inputField('Descripci&oacute;n', 'descripcion');
		$edit->descripcion->rule = 'trim|required|max_length[100]';

		$edit->enlace =  new inputField('Enlace Administrativo', 'enlace');
		$edit->enlace->rule = 'trim|required|max_length[6]';
		$edit->enlace->size = 7;

		$edit->medida =  new inputField('Medida', 'medida');
		$edit->medida->rule = 'trim|required|max_length[5]';
		$edit->medida->size = 6;
		$edit->medida->max_size = 5;

		$edit->iva =  new inputField('Iva', 'iva');
		$edit->iva->rule = 'trim|required|numeric';

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Partidas de Contratos</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}