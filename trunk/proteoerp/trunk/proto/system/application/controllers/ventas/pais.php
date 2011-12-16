<?php
class pais extends Controller {
	
	function pais()
	{
		parent::Controller(); 
		$this->load->library("rapyd");

	}

	function index()
	{
		redirect("ventas/pais/filteredgrid");
	}
		function filteredgrid(){
			
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro", 'pais');
		
		$filter->codigo = new inputField("Codigo", "codigo");
		$filter->codigo->size=15;
			
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=25;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/pais/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Paises");
		$grid->order_by("nombre","asc");
		$grid->per_page = 7;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre ");

		$grid->add("ventas/pais/dataedit/create");
		$grid->build();

	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Paises</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
	{ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Agregar", "pais");
		$edit->back_url = site_url("ventas/pais/filteredgrid");
				
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size=38;
		$edit->nombre->rule="trim|required|strtoupper";
		$edit->nombre->maxlength=30;
		  
		$edit->buttons("modify", "save", "undo", "delete", "back");
  	$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Pais</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}
?>