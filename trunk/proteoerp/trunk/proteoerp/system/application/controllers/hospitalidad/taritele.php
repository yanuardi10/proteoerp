<?php
class Taritele extends Controller {

	function Taritele(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//define ("THISFILE",   APPPATH."controllers/hotel/". $this->uri->segment(2).EXT);
		//$this->rapyd->set_connection('hotel');
	}

	function index(){
		$this->datasis->modulo_id(809,1);
		redirect("hospitalidad/taritele/filteredgrid");
	}

	function filteredgrid(){

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Tarifas Telef&oacute;nicas", 'tari');

		$filter->gr_desc = new inputField("C&oacute;digo", "serial");
		$filter->gr_desc->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('hospitalidad/taritele/dataedit/show/<#serial#>','<#serial#>');
		
		$grid = new DataGrid("Lista de Tarifas Telef&oacute;nicas");
		$grid->order_by("serial","asc");
		$grid->per_page = 7;
		
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","descrip","descrip");
		$grid->column("Banda","banda");
		$grid->column("Pulso","pulso");
		$grid->column("Tarifa 1","tarifa1");
		$grid->column("Tarifa2","tarifa2");
		
		$grid->add("hospitalidad/taritele/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Tarifas Telef&oacute;nicas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Tarifa Telef&oacute;nica", "tari");
		$edit->back_url = site_url("hospitalidad/taritele/filteredgrid");
		
		$edit->serial  = new inputField("C&oacute;digo", "serial");
		$edit->serial->size=15;
		$edit->serial->maxlength=20;
		$edit->serial->rule="trim";
				
		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size=50;
		$edit->descrip->maxlength=30;
		$edit->descrip->rule="trim";
		
		$edit->banda   = new inputField("Banda", "banda");
		$edit->banda->size=15;
		$edit->banda->maxlength=2;
		$edit->banda->rule="trim";
		
		$edit->pulso   = new inputField("Pulso","pulso");
		$edit->pulso->size=15;
		$edit->pulso->maxlength=5;
		$edit->pulso->rule="trim";
		
		$edit->tarifa1 = new inputField("Tarifa1", "tarifa1");
		$edit->tarifa1->size=15;
		$edit->tarifa1->maxlength=11;
		$edit->tarifa1->rule="trim";
		
		$edit->tarifa2 = new inputField("Tarifa2", "tarifa2");
		$edit->tarifa2->size=15;
		$edit->tarifa2->maxlength=11;
		$edit->tarifa2->rule="trim";
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Tarifas Telef&oacute;nicas</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}
?>