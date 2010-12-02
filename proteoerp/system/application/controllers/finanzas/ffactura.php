<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class ffactura extends validaciones{

	function ffactura(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->load->library("menues");
		$this->load->database();
	}

	function index(){
		redirect("finanzas/ffactura/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro de Facura de Gastos", 'gser');
	
		$filter->numero = new inputField("Numero","numero");
		$filter->numero->size=30;
							
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/ffactura/dataedit/modify/<#fecha#>/<#numero#>/<#proveed#>','<#numero#>');

		$grid = new DataGrid("Lista de Facura de Gastos");
		$grid->order_by("numero","desc");
		$grid->per_page=15;
		
		$grid->column("Numero",$uri);
		$grid->column("Fecha de Factura","ffactura");
		
		$grid->add("finanzas/ffactura/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Fecha de Facura de Gasto</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
	
		$edit = new DataEdit("Modificar", "gser");
		$edit->back_url = site_url("finanzas/ffactura/filteredgrid");
	
		$edit->numero = new inputField("Numero","numero");
		$edit->numero->size = 20;
		$edit->numero->mode = "autohide";   
					
		$edit->ffactura = new dateonlyField("Fecha de factura","ffactura");
		$edit->ffactura->size = 12;     
		  				    
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();
		
		//$smenu['link']=barra_menu('912');		
		$data['content'] = $edit->output;
		//$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['title']   = "<h1>Fecha de Facura de Gasto</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function instalar(){
		$mSQL="CREATE TABLE `matbar`.`ffactura` (`id` TINYINT AUTO_INCREMENT, `estampa` TIMESTAMP, `fecha` DATE, `t1horae` VARCHAR (8),`t2horae` VARCHAR (8), `t1horas` VARCHAR (8),`t2horas` VARCHAR (8),`empresa` VARCHAR (50), `tecnico1` VARCHAR (50), `tecnico2` VARCHAR (50), `tecnico3` VARCHAR (50), `informe` TEXT,`observa` TEXT, `t1tipos` VARCHAR(10),`t2tipos` VARCHAR(10),`t1tipoe` VARCHAR(10),`t2tipoe` VARCHAR(10),`nombre` VARCHAR(60),PRIMARY KEY(`id`)) TYPE = MyISAM";
		var_dum($this->db->simple_query($mSQL));
	}
}
?>