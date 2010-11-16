<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class status extends validaciones {

	function status(){
		parent::Controller(); 
		$this->load->library('rapyd');
		$this->prefijo='crm_';
		//$this->datasis->modulo_id(136,1);
	}

	function index(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro');
		$select=array("a.definicion","b.nombre","a.descrip","a.id");
		$filter->db->select($select);
		$filter->db->from('crm_status AS a');
		$filter->db->join('crm_definiciones AS b','a.definicion=b.id');

		$filter->definicion = new dropdownField("Definici&oacute;n","definicion");
		$filter->definicion->option('',"Seleccione");
		$filter->definicion->options("SELECT id,nombre  FROM crm_definiciones ORDER BY nombre");
		$filter->definicion->rule = 'required';

		$filter->buttons("reset","search");
		$filter->build();

		$uri  = anchor('crm/status/dataedit/show/<#id#>','<#id#>');

		$grid = new DataGrid('Lista');
		$grid->order_by('a.id','asc');
		$grid->per_page = 7;

		$grid->column('id',$uri);
		$grid->column_orderby('Definici&acute;n' ,'nombre'     ,'nombre');
		$grid->column('Descripci&oacute;n'        ,'descrip');
		
		$grid->add('crm/status/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Estatus</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit','datagrid');

		$edit = new DataEdit(" ",'crm_status');
		$edit->back_url = site_url('crm/status/index');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->usuario  = new autoUpdateField('usuario', $this->session->userdata('usuario'), $this->session->userdata('usuario'));

		$edit->definicion = new dropdownField("Definición","definicion");
		$edit->definicion->option("","Seleccione");
		$edit->definicion->options("SELECT id,nombre  FROM crm_definiciones ORDER BY nombre");
		$edit->definicion->rule = 'required';

		$edit->descrip =  new inputField('Descripción','descrip');
		$edit->descrip->size = 50;
		$edit->descrip->maxlength=200;
		$edit->descrip->rule = 'trim|strtoupper|required';

		$edit->buttons("modify", "save", "undo", "delete", "back"); 
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Estatus</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_insert($do){
		$fecha=$do->get('fecha');
		$definicion=$do->get('definicion');
		$contenedor=$do->get('contenedor');
		logusu('crm_status',"ESTATUS $fecha $contenedor $definicion CREADO");
	}
	function _post_update($do){
		$fecha=$do->get('fecha');
		$definicion=$do->get('definicion');
		$contenedor=$do->get('contenedor');
		logusu('crm_status',"ESTATUS $fecha $contenedor $definicion MODIFICADO");
	}
	function _post_delete($do){
		$fecha=$do->get('fecha');
		$definicion=$do->get('definicion');
		$contenedor=$do->get('contenedor');
		logusu('crm_status',"ESTATUS $fecha $contenedor $definicion ELIMINADO");
	}
}