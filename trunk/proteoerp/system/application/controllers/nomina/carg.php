<?php
//cargos
class Carg extends Controller {
	
	function carg(){
		parent::Controller(); 
		$this->load->library("rapyd");
  }

   function index(){
  	$this->datasis->modulo_id(701,1);
  	redirect("nomina/carg/filteredgrid");
  }

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter2("Filtro por Cargo",'carg');
		
		$filter->cargo   = new inputField("C&oacute;digo", "cargo");
		$filter->cargo->size=3;
		$filter->cargo->clause = "likerigth";
		
		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->cargo->clause = "likerigth";
		
		$filter->buttons("reset","search");
		$filter->build();
 
		$uri = anchor('nomina/carg/dataedit/show/<#cargo#>','<#cargo#>');

		$grid = new DataGrid("Lista de Cargos");
		$grid->order_by("cargo","asc");
		$grid->per_page = 10;
		
		$grid->column("Cargo",$uri                                                                             );
		$grid->column("Descripci&oacute;n"                                                     ,"descrip"      );
		$grid->column("Sueldo"               ,"<number_format><#sueldo#>|2|,|.</number_format>","align='right'");
		
		$grid->add("nomina/carg/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cargos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	

	}
		function dataedit(){
 		$this->rapyd->load("dataedit");
  	
  	$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
  	
		$edit = new DataEdit("Cargos","carg");
		$edit->back_url = "nomina/carg/filteredgrid";
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->cargo = new inputField("Cargo", "cargo");
		$edit->cargo->rule= "required|callback_chexiste";
		$edit->cargo->mode="autohide";
		$edit->cargo->maxlength=8;
		$edit->cargo->size=10;
		
		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size=40;
		$edit->descrip->maxlength=30;
		$edit->descrip->rule= "strtoupper|required";
		
		$edit->sueldo  = new inputField("Sueldo", "sueldo");
		$edit->sueldo->size=20;
		$edit->sueldo->rule= "required|callback_positivo";
		$edit->sueldo->css_class='inputnum';
		
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Cargos</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	
	function _pre_del($do) {
		$codigo=$do->get('cargo');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE cargo='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}
		return True;
	}
	function _post_insert($do){
		$codigo=$do->get('cargo');
		$nombre=$do->get('descrip');
		logusu('carg',"CARGO $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('cargo');
		$nombre=$do->get('descrip');
		logusu('carg',"CARGO $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('cargo');
		$nombre=$do->get('descrip');
		logusu('carg',"CARGO $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('cargo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM carg WHERE cargo='$codigo'");
		if ($chek > 0){
			$cargo=$this->datasis->dameval("SELECT descrip FROM carg WHERE cargo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el cargo $cargo");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	function instalar(){
		$mSQL="ALTER TABLE carg ADD PRIMARY KEY (cargo);";
		$this->db->simple_query($mSQL);	
	}
	
	function positivo($valor){
		if ($valor <= 0){
			$this->validation->set_message('positivo',"El campo Sueldo debe ser positivo");
			return FALSE;
		}
  	return TRUE;
	}
}
?>