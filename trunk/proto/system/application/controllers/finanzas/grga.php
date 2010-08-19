<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Grga extends validaciones {
	
	function grga(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(510,1);
		redirect("finanzas/grga/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro por Grupo', 'grga');
		$filter->grupo = new inputField('Grupo', 'grupo');
		$filter->grupo->size=15;

		$filter->nom_grup = new inputField('Descripci&oacute;n','nom_grup');

		$filter->cu_inve = new inputField('Cuenta','cu_inve');
		$filter->cu_inve->like_side='after';

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/grga/dataedit/show/<#grupo#>','<#grupo#>');

		$grid = new DataGrid('Lista de Grupos de Gastos');
		$grid->order_by('grupo','asc');
		$grid->per_page = 20;

		$grid->column('Grupo',$uri,'grupo');
		$grid->column('Nombre del Grupo','nom_grup','nom_grup');
		$grid->column('Cuenta Contable','cu_inve','cu_inve');

		$grid->add('finanzas/grga/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Grupos de Gastos</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load("dataedit");

		$edit = new DataEdit('Grupos de Gastos', 'grga');
		$edit->back_url = site_url('finanzas/grga/filteredgrid');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->grupo =     new inputField("Grupo", "grupo");
		$edit->grupo->mode="autohide";
		$edit->grupo->size = 6;
		$edit->grupo->rule = "trim|required|callback_chexiste";
		$edit->grupo->maxlength=5;

		$edit->nom_grup =  new inputField("Nombre del Grupo", "nom_grup");
		$edit->nom_grup->size = 35;
		$edit->nom_grup->rule = "trim|required";
		$edit->nom_grup->maxlength=25;

		$edit->cu_inve =   new inputField("Cuenta Contable", "cu_inve");
		$edit->cu_inve->size = 18;
		$edit->cu_inve->maxlength=15;
		$edit->cu_inve->rule = "trim|callback_chcuentac";

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
 
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Grupos de Gastos</h1>";        
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}

	function _post_insert($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grga',"GRUPO DE GASTOS $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grga',"GRUPO DE GASTOS $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grga',"GRUPO DE GASTOS $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('grupo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM grga WHERE grupo='$codigo'");
		if ($chek > 0){
			$grupo=$this->datasis->dameval("SELECT nom_grup FROM grga WHERE grupo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el grupo $grupo");
			return FALSE;
		}else {
		return TRUE;
		}
	}
}