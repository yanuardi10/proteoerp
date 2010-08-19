<?php	//para analisisbanc
class Bmovshow extends Controller {
	function bmovshow(){
		parent::Controller(); 
		$this->load->library("rapyd");
	} 
		function dataedit(){
			$this->rapyd->load("dataedit");
			$edit = new DataEdit("Cheque", "bmov");
			
			$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
			$edit->fecha->insertValue = date("Y-m-d");
			
			$edit->numero   = new textareaField("N&uacute;mero", "numero");
			$edit->nombre   = new textareaField("Nombre", "nombre");
			$edit->banco    = new textareaField("Banco", "banco");
			$edit->numcuent = new textareaField("N&uacute;mero Cuenta", "numcuent");
			$edit->monto    = new textareaField("Monto", "monto");
			$edit->concepto = new textareaField("Concepto", "concepto");
	  	$edit->Benefi   = new textareaField("Beneficiario", "benefi");
			$edit->anulado  = new textareaField("Anulado", "anulado");			
			
			$edit->build();
			
	  	$data['content'] = $edit->output;
	  	$data['title']   = "<h1>Consulta de Cheques</h1>";
	  	$data["head"]    = $this->rapyd->get_head();
	  	$this->load->view('view_ventanas', $data);
		}
		
}