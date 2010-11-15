<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Definiciones extends validaciones {

	function Definiciones(){
		parent::Controller(); 
		$this->load->library('rapyd');
		$this->prefijo='crm_';
		//$this->datasis->modulo_id(136,1);
	}

	function index(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('titulo', $this->prefijo.'definiciones');

		$filter->nombre     = new inputField('Nombre', 'nombre');

		$filter->estructura = new inputField('Estructura', 'estructura');

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('crm/definiciones/dataedit/show/<#id#>','<#id#>');

		$grid = new DataGrid('Lista de Cajas');
		//$grid->order_by('caja','asc');
		$grid->per_page = 7;

		$grid->column('id',$uri);
		$grid->column('Nombre'    ,'nombre');
		$grid->column('Estructura','estructura');

		$grid->add('crm/definiciones/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Definiciones</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Definiciones', $this->prefijo.'definiciones');

		/*$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');*/

		$edit->nombre =  new inputField('Nombre', 'nombre');
		$edit->nombre->size = 15;
		$edit->nombre->maxlength=30;
		$edit->nombre->rule = 'trim|strtoupper|required';

		$edit->estructura =  new textareaField('Estructura', 'estructura');
		$edit->estructura->cols = 70;
		$edit->estructura->rows = 4;
		$edit->estructura->rule = "trim|required";

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Definiciones</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instala(){
	
	}


}
