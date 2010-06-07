<?php
class Grupoprv extends Controller {

    var $data_type = null;
    var $data = null;

    function Grupoprv()
    {
	parent::Controller(); 

	//required helpers for samples
	$this->load->helper('url');
	$this->load->helper('text');

	$this->load->library("rapyd");

	//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
	define ("THISFILE",   APPPATH."controllers/compras/". $this->uri->segment(2).EXT);
    }
    function index()
    {
	  redirect("compras/grupoprv/filteredgrid");
    }
    ##### callback test (for DataFilter + DataGrid) #####
    function test($id,$const)
    {
	return $id*$const;
    }
	function filteredgrid()	{
	
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Grupos de Provedores", 'grpr');
		$filter->gr_desc = new inputField("Grupo", "gr_desc");
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('compras/grupoprv/dataedit/show/<#grupo#>','<#grupo#>');

		$grid = new DataGrid("Lista de Grupos de Provedores");
		$grid->order_by("gr_desc","asc");
		$grid->per_page = 7;

		$grid->column("Grupo",$uri);
		$grid->column("Nombre","gr_desc","gr_desc");
		$grid->column("Cuenta","cuenta");

		$grid->add("compras/grupoprv/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Grupos de Proveedores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
	{ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Grupo de Provedores", "grpr");
		$edit->back_url = site_url("compras/grupoprv/filteredgrid");

		$edit->grupo = new inputField("Grupo", "grupo");
		$edit->gr_desc = new inputField("Descripcion", "gr_desc");
		$edit->cuenta = new inputField("Cta. Contable", "cuenta");

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data["edit"] = $edit->output;
		$data["modulo"]  = "";

		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Grupos de Proveedores</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}
?>