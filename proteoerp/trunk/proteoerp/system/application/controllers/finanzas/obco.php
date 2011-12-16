<?php
class Obco extends Controller {
	
	function Obco(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->rapyd->set_connection('construc');
		//define ("THISFILE",   APPPATH."controllers/finanzas". $this->uri->segment(2).EXT);
   }

	function index(){
		redirect("finanzas/obco/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de obco", 'obco');
		
		$filter->numero = new inputField("Numero", "numero");
		$filter->numero->size=15;
		
		$filter->fecha = new dateonlyField("Fecha", "fecha");

		$filter->proveed= new inputField("Proveedor", "Proveed");

		$filter->factura= new inputField("Factura", "factura");
		$filter->factura->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/obco/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid("Filtro de obco");
		//$grid->order_by("nombre","asc");
		$grid->per_page =10;
		$grid->column("numero",$uri);
		$grid->column("fecha","fecha");
		$grid->column("status","status");
		$grid->column("tipo","tipo");
		$grid->column("proveed","proveed");
		$grid->column("nombre","nombre");
		$grid->column("monto","monto");
		$grid->column("factura","factura");
		$grid->add("finanzas/obco/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Obco</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	

	}
	
	function dataedit(){
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("obco", "obco");
		$edit->back_url = site_url("finanzas/obco/filteredgrid");
		//$edit->pre_process('delete','_pre_del');
		
		$edit->numero =  new inputField("Numero", "numero");
		$edit->numero->mode="autohide";
		$edit->rif->rule = "trim|required";
		$edit->rif->maxlength=8;
		$edit->numero->size = 11;
		
		$edit->factura =  new inputField("Factura", "factura");
		$edit->factura->size = 11;
		$edit->factura->rule = "trim";
		$edit->factura->maxlength=8;
		
		$edit->fecha =  new dateonlyField("fecha", "fecha","d/m/Y");
		$edit->fecha->size = 12;
		
		$edit->status =  new inputField("status", "status");
		$edit->status->size=3;
		$edit->status->rule = "trim";
		$edit->status->maxlength=1;
	
		$edit->tipo =  new dropdownField("tipo", "tipo");
		$edit->tipo->option("FE","FE");
		$edit->tipo->option("OT","OT");	
		$edit->tipo->option("RE","RE");
		$edit->tipo->style="width:70px";
		
		$edit->documen =  new inputField("documen", "documen");
		$edit->documen->size = 15;
		
		$edit->fechadoc = new dateonlyField("fechadoc", "fechadoc","d/m/Y");
		
		$edit->proveed =  new inputField("Proveedor", "proveed");
		$edit->proveed->size=15;
		
		$edit->nombre =  new inputField("Nombre", "nombre");
		$edit->nombre->size=35;
		
		$edit->monto =  new inputField("Monto", "monto");
		$edit->monto->size=16;
		
		$edit->partida =  new inputField("Partida", "partida");
		$edit->partida->size=4;
		
		$edit->descrip =  new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->maxlength=30;
		$edit->descrip->rows = 10;
		$edit->descrip->cols = 70;
		
		$edit->obra =  new inputField("Obra", "obra");
		$edit->obra->size=3;
		
		$edit->obradesc =  new inputField("obradesc", "obradesc");
		$edit->obradesc->size=40;
		$edit->obradesc->rows = 10;
		$edit->obradesc->cols = 70;
		
		$edit->observa1 =  new inputField("Observa1", "observa1");
		$edit->observa1->size=40;
		$edit->observa1->rows = 10;
		$edit->observa1->cols = 70;
		
		$edit->observa2 =  new inputField("Observa2", "observa2");
	  $edit->observa2->size=40;
		$edit->observa2->rows = 10;
		$edit->observa2->cols = 70;
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Obco</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}




?>