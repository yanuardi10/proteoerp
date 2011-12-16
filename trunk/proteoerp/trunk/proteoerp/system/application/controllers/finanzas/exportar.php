<?php
class exportar extends Controller {

	function exportar(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){

	}

	function sclismov(){
		set_time_limit(600);
		$ssucu     = $this->datasis->traevalor('NROSUCU');

		$this->load->library("sqlinex");
		//$this->sqlinex->ignore  = TRUE;
		//$this->sqlinex->limpiar = FALSE;

		$data[]=array('distinc'=>false,
									'table'  =>'scli');
		$data[]=array('distinc'=>false,
									'table'  =>'smov',
									'where'  =>"abonos<monto AND tipo_doc<>'AB'");
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl'; //necesario cuando se baja con curl

		$this->sqlinex->exportzip($data,'sclimov_'.$ssucu,$ssucu);
	}

	//eportacion para vendedores ambulantes
	function vendambul(){
		set_time_limit(600);
		$ssucu = $this->datasis->traevalor('NROSUCU');

		$this->load->library("sqlinex");
		//$this->sqlinex->ignore  = TRUE;
		//$this->sqlinex->limpiar = FALSE;

		$data[]=array('distinc'=>false,
									'table'  =>'scli');
		$data[]=array('distinc'=>false,
									'table'  =>'smov',
									'where'  =>"abonos<monto AND tipo_doc<>'AB'");
		$data[]=array('table'  =>'dpto');
		$data[]=array('table'  =>'line');
		$data[]=array('table'  =>'grup');
		$data[]=array('table'  =>'sinv');
		$data[]=array('table'  =>'marc');
		$data[]=array('table'  =>'itsinv');
		$data[]=array('table'  =>'vend');
		$data[]=array('table'  =>'sinvfot');
									
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT']='curl'; //necesario cuando se baja con curl

		$this->sqlinex->exportzip($data,'vendambul_'.$ssucu,$ssucu);
	}

}
?>