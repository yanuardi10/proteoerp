<?php
class Gpartida extends Controller {

	function Gpartida(){
		parent::Controller(); 
		$this->load->helper('text');
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(516,1);
		redirect("construccion/gpartida/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por Grupos de partidas", "obgp");

		$filter->codigo = new inputField("Grupo", "grupo");
		$filter->codigo->size=10;

		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=20;

		$filter->buttons("reset","search");
		$filter->build();

		$uri  = anchor('construccion/gpartida/dataedit/show/<#grupo#>','<#grupo#>');
		$grid = new DataGrid("Grupos de partida");
		$grid->per_page = 10;

		$grid->column("Grupo",$uri);
		$grid->column("Nombre","nombre");

		$grid->add("construccion/gpartida/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Grupos de Partida</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("Grupo", "obgp");
		$edit->back_url = site_url("construccion/gpartida/filteredgrid");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->grupo = new inputField("Grupo", "grupo");
		$edit->grupo->mode="autohide";
		$edit->grupo->rule = "required|callback_chexiste";
		$edit->grupo->size =7;
		$edit->grupo->maxlength =4 ;

		$edit->nombre =  new inputField("Nombre", "nombre");
		$edit->nombre->rule = "require|strtoupper";
		$edit->nombre->size =50 ;
		$edit->nombre->maxlength =30 ;

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Grupos de Partida</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_insert($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nombre');
		logusu('obgp',"GRUPO DE PARTIDA $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nombre');
		logusu('obgp',"GRUPO DE PARTIDA $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nombre');
		logusu('obgp',"GRUPO DE PARTIDA $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('grupo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM obgp WHERE grupo='$codigo'");
		if ($chek > 0){
			$grupo=$this->datasis->dameval("SELECT nombre FROM obgp WHERE grupo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el grupo $grupo");
			return FALSE;
		}else {
		return TRUE;
		}
	}
}