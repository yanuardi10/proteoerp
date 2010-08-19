<?php

class Poscuadre extends Controller {
	
	function Poscuadre(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->rapyd->set_connection('supermer');
		//$this->datasis->modulo_id(108);
		$this->load->database('supermer',TRUE);
	}
	
	function index() {
		
	}
	
}
?>