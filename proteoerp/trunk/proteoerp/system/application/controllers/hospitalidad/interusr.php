<?php
class Interusr extends Controller {

	function Interusr(){
		parent::Controller();
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/hotel". $this->uri->segment(2).EXT);
	}
	
	function index(){
		$this->datasis->modulo_id(709,1);
		redirect("hospitalidad/interusr/filteredgrid");
	}
	
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->db->simple_query('DELETE FROM interusr WHERE caduca < DATE(NOW())');

		$filter = new DataFilter("Filtro de usuarios con acceso a internet", 'interusr');
		$filter->codigo = new inputField("Usuario", "usuario");
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri  = anchor('hospitalidad/interusr/dataedit/show/<#usuario#>','<#usuario#>');
	
		$grid = new DataGrid("Lista de Usuarios");
		$grid->db->select=array("usuario","DATE_FORMAT(estampa,'%d/%m/%Y') fecha","DATE_FORMAT(caduca,'%d/%m/%Y') caduca");
		$grid->order_by("usuario","asc");
		$grid->per_page = 7;
		$grid->column("Usuario", $uri);
		$grid->column("Fecha","fecha",'align="center"');
		$grid->column("Caduca","caduca",'align="center"');
		$grid->column("Clave","clave",'align="right"');
		$grid->add("hospitalidad/interusr/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Usuarios Internet</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
		
		function dataedit(){
		$this->rapyd->load("dataedit");
		
		//$edit = new DataEdit("Registrar usuario para internet", 'interusr');
		//if ($this->rapyd->uri->is_set("insert"))
		//$edit->_dataobject->db->set('estampa',date('YmdHis'));
		
		$edit->back_url = site_url("hospitalidad/interusr/filteredgrid");
		
		$edit->usuario  = new inputField("Usuario", "usuario");
		$edit->usuario->rule = "required";
		$edit->usuario->mode = "autohide";		
		
		$edit->clave    = new inputField("Clave", "clave");
		$edit->clave->insertValue = $this->_clave();
		$edit->clave->size = 8;
		$edit->clave->maxlength = 10;
		
	 
	  $edit->fecha    = new DateField("Caduca","caduca","d/m/Y");
	  $edit->fecha->size = 10;	  
	  $edit->fecha->rule = "required";  
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
	
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Usuarios Internet</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
 	}
	
	function _clave(){
		$clave = '';
		$longitud = 6;
		for ($i=1; $i<=$longitud; $i++){
			if (rand(0,10)>5)
				$letra =  chr(rand(97,122));
			else
				$letra =  rand(0,9);
			$clave .= $letra;
		}
		return ($clave);
	}
}
?>