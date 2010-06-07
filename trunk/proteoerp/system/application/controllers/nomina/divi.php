<?php
//division
class Divi extends Controller {

	function divi(){
		parent::Controller(); 
		$this->load->library("rapyd");
   }

	function index(){
    $this->datasis->modulo_id(705,1);
		redirect("nomina/divi/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por Divisi&oacute;n", 'divi');
		
		$filter->division = new inputField("Divisi&oacute;n", "division");
		$filter->division->size=8;

		$filter->descrip = new inputField("Descripcion","descrip");
		$filter->descrip->size=30;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('nomina/divi/dataedit/show/<#division#>','<#division#>');

		$grid = new DataGrid("Lista de Divisiones");
		$grid->order_by("division","asc");
		$grid->per_page = 10;
		$grid->column("Divisi&oacute;n",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->add("nomina/divi/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Divisiones</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit()
 	{
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("Divisi&oacute;n", "divi");
		$edit->back_url = site_url("nomina/divi/filteredgrid");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
	
		$edit->division =  new inputField("Divisi&oacute;n", "division");
		$edit->division->rule="required|callback_chexiste";
		$edit->division->mode="autohide";
		$edit->division->maxlength=8;
		$edit->division->size=9;
		
		$edit->descrip =  new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->maxlength=30;
		$edit->descrip->size =35;
		$edit->descrip->rule="strtoupper|required";
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Divisiones</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _pre_del($do) {
		$codigo=$do->get('division');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE divi='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM depa WHERE divi='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}
		return True;
	}
	function _post_insert($do){
		$codigo=$do->get('division');
		$nombre=$do->get('descrip');
		logusu('divi',"DIVISION $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('division');
		$nombre=$do->get('descrip');
		logusu('divi',"DIVISION $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('division');
		$nombre=$do->get('descrip');
		logusu('divi',"DIVISION $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('division');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM divi WHERE division='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT descrip FROM divi WHERE division='$codigo'");
			$this->validation->set_message('chexiste',"La division $codigo nombre $nombre ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>