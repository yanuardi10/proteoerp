<?php
class tablas extends Controller {
	
	function tablas(){
		parent::Controller(); 
		$this->load->library("rapyd");
		
	}
	
	#### index #####
	function index()
	{
		redirect("doc/tablas/filteredgrid");
	}

	 ##### DataFilter + DataGrid #####
	 
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
 
		$filter = new DataFilter("Filtro de Tablas", 'doc_tablas');
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=20;
		
		$filter->buttons("reset","search");
		$filter->build();
    
    $uri = anchor('doc/tablas/dataedit/show/<#nombre#>','<#nombre#>');

		$grid = new DataGrid("Lista de Tablas");
		$grid->order_by("nombre","asc");                          
		$grid->per_page = 15;

		$grid->column("Nombre",$uri );
		$grid->column("Descripci&oacute;n","referen");
        		
		$grid->add("doc/tablas/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Documentaci&oacute;n de Tablas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	##### dataedit ##### 

	function dataedit()
 	{
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("Tablas", "doc_tablas");
		$edit->back_url = site_url("doc/tablas/filteredgrid");
		
		$edit->nombre= new inputField("Nombre", "nombre");
		$edit->nombre->mode="autohide";
		$edit->nombre->size = 15;
		
		$edit->descrip   = new textareaField("Descripci&oacute;n", "referen");
		$edit->descrip->size = 50;
		$edit->descrip->rows = 6;
		$edit->descrip->cols=90;
	
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();
		
    $data['content'] = $edit->output;           
    $data['title']   = "<h1>Documentaci&oacute;n de Tablas</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  

	}  
  
}
?>


