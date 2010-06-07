<?php
class Test extends Controller {
	function Test(){
		parent::Controller();
		$this->load->library("rapyd");
	}
  function index(){
    redirect("inventario/test/dataedit");
  }

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$edit = new DataDetails("Transferencia", "stra");
		
		$edit->numero   = new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size =10;
		$edit->numero->rule = "required";
		
		$edit->fecha    = new  dateonlyField("Fecha",  "fecha");
		$edit->fecha->size =12;
		
		$edit->build();
		
		$data['content'] = $edit->output;
		$data['title']   = "<h1>Transferencias de inventario</h1>";
		$data["head"]    = $this->rapyd->get_head().script('jquery.js');
		$this->load->view('view_ventanas', $data); 
  }
}
?>