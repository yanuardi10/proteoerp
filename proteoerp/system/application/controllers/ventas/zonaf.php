<?php
class zonaf extends Controller {

	function zonaf()
	{
		parent::Controller(); 
		$this->load->library("rapyd");

	}

	function index()
	{
		redirect("ventas/zonaf/filteredgrid");
	}
		function filteredgrid()
	{
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro");
		$select=array("a.codigo","a.nombre","b.nombre as pais");
		$filter->db->select($select);
		$filter->db->from('zona as a');
		$filter->db->join('pais as b','a.pais=b.codigo');
		
		$filter->codigo = new inputField("Codigo", "a.codigo");
		$filter->codigo->size=15;
			
		$filter->nombre = new inputField("Nombre", "a.nombre");
		$filter->nombre->size=25;
		
		$filter->pais = new dropdownField("Pa&iacute;s", "pais");
		$filter->pais->style = "width:150px";
		$filter->pais->option("","Seleccionar");
		$filter->pais->options("SELECT codigo, nombre FROM pais ORDER BY codigo");
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/zonaf/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Zonas");
		$grid->order_by("nombre","asc");
		$grid->per_page = 15;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre","nombre");
		$grid->column("Pais","pais");

		$grid->add("ventas/zonaf/dataedit/create");
		$grid->build();

	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Zonas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
  }
	function dataedit(){
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Zona", "zona");
		$edit->back_url = site_url("ventas/zonaf/filteredgrid");
						
		$edit->codigo = new inputField("codigo","codigo");
		$edit->codigo->size=5;
		$edit->codigo->rule="required";
		$edit->codigo->maxlength=5;
		
		$edit->pais = new dropdownField("Pa&iacute;s", "pais");
		$edit->pais->style = "width:150px";
		$edit->pais->option("","Seleccionar");
		$edit->pais->options("SELECT codigo, nombre FROM pais ORDER BY codigo");
		$edit->pais->group = "Ubicaci&oacute;n";
	  		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size=38;
		$edit->nombre->rule="trim|required|strtoupper";
		$edit->nombre->maxlength=30;
	  
		$edit->buttons("modify", "save", "undo", "delete", "back");
  	$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Zona</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}
?>