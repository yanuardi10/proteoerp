<?php
class valores extends Controller {
	function valores(){
		parent::Controller(); 
		$this->load->library("rapyd");

	}
	function index(){
		redirect("supervisor/valores/filteredgrid");
	}
	function filteredgrid(){

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Valores", 'valores');
				
		$filter->nombre = new inputField("Nombre","nombre");
		$filter->nombre->size=35;

		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('supervisor/valores/dataedit/show/<#nombre#>','<#nombre#>');
		
		$grid = new DataGrid("Lista de Valores");
		$grid->order_by("nombre","asc");

		$grid->column("Nombre",$uri );
		$grid->column("Valor", "valor");		
		$grid->column("Descripci&oacute;n","descrip");

		$grid->add("supervisor/valores/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Valores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Valor","valores");
		$edit->back_url = site_url("supervisor/valores/filteredgrid");
				
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size=35;
		
		$edit->valor = new inputField("Valor", "valor");
		$edit->valor->size=45;
		
		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size=45;                         
		
		$edit->buttons("modify", "save", "undo", "back");		
		$edit->build();
			
    $data['content'] = $edit->output;           
    $data['title']   = "<h1>Valores</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}
?>