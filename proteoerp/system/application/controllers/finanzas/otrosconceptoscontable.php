<?php
 class otrosconceptoscontable extends Controller {
	
	var $data_type = null;
	var $data = null;
	 
	function otrosconceptoscontable(){
		parent::Controller(); 
		//required helpers for samples
		$this->load->helper('url');
		$this->load->helper('text');
		//rapyd library
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
   }
   function index(){
    	$this->datasis->modulo_id(514,1);
    	redirect("finanzas/otrosconceptoscontable/filteredgrid");
    }
 	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por C&oacute;digo", 'botr');
		
		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/otrosconceptoscontable/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Otros Conceptos Contable");
		$grid->order_by("codigo","asc");
		$grid->per_page = 20;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre");
		$grid->column("Cuenta","cuenta");
		$grid->column("Precio","precio");
		$grid->column("Iva","iva");
		$grid->column("Tipo","tipo");
		$grid->column("Intocable","intocable");
		$grid->column("Clase","clase");
		$grid->column("Nombre","nombre");
		$grid->column("Usacant","usacant");
			  	  						
		$grid->add("finanzas/otrosconceptoscontable/dataedit/create");
		$grid->build();
		
	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Otros Conceptos Contable</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("Otro Concepto Contable", "botr");
		$edit->back_url = site_url("finanzas/otrosconceptoscontable/filteredgrid");
		
		$edit->codigo =           new inputField("C&oacute;digo", "codigo");
		$edit->codigo->mode="autohide";
		
		$edit->nombre =       new inputField("Nombre", "nombre");
		$edit->nombre->size = 40;
		
		$edit->cuenta =       new inputField("Cuenta", "cuenta");
		$edit->cuenta->size = 25;
		
		$edit->precio =       new inputField("Precio", "precio");
		$edit->precio->size = 25;
		
		$edit->iva =          new inputField("Iva", "iva");
		$edit->iva->size = 10;
		
		$edit->tipo =        new inputField("Tipo", "tipo");
		$edit->tipo->size = 5;
		
		$edit->intocable =   new inputField("Intocable", "intocable");
		$edit->intocable->size = 5;
		
		$edit->clase = new inputField("Clase", "clase");
		$edit->clase->size = 5;
		
		$edit->usacant =     new inputField("Usacant", "usacant");
		$edit->usacant->size = 5;
		    
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
 
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Otros Conceptos Contable</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}
?>