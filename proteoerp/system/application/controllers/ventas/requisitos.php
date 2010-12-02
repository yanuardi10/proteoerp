<?php  require_once(BASEPATH.'application/controllers/validaciones.php');
//Formapa
class requisitos extends validaciones {
	function requisitos(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(133,1);
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/ventas/". $this->uri->segment(2).EXT);
	}
	function index(){
		redirect("ventas/requisitos/filteredgrid");
	}
	function filteredgrid(){
	
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Buscar", 'requisitos');
		
		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->size=30;
  
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/requisitos/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Requisitos");
		$grid->order_by("codigo","asc");
		$grid->per_page = 10;
		
		$grid->column("Codigo",$uri);
		$grid->column("Descripci&oacute;n","descrip","nombre");
		
		$grid->add("ventas/requisitos/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Requisitos para Clientes</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Agregar", "requisitos");
		$edit->back_url = site_url("ventas/requisitos/filteredgrid");
		
		$edit->descrip = new inputField("Descripci&oacute;n","descrip");
		$edit->descrip->maxlength=150;
		$edit->descrip->size=50;
		$edit->descrip->rule = "strtoupper|required";
			
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Requisito</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}	
	function instala(){
		$mSQL="CREATE TABLE `requisitos` (`codigo` TINYINT UNSIGNED AUTO_INCREMENT, `descrip` VARCHAR (150), PRIMARY KEY(`codigo`))";
	  var_dum($this->db->simple_query($mSQL));	
	}
}
?>