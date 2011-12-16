<?php
class exportar extends Controller {

	function exportar(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		
	}

	function exventas(){
		$this->rapyd->load('dataform');
		$this->load->library('Sqlinex');

		$form = new DataForm("supermercado/exportar/exventas/process");

		$form->fecha = new dateonlyField("Fecha","fecha");
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->rule ="require|chfecha";
		$form->fecha->size =12;


		$form->submit("btnsubmit","Descargar");
		$form->build_form();

		if ($form->on_success()){
			$fecha=$form->fecha->newValue;
			$this->_ventas($fecha);
		} 

		$data['content'] = $form->output;
		$data['title']   = '<h1>Exportar ventas a SQL</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _ventas($fecha=null){
		set_time_limit(600);
		if(empty($fecha)) return 1;
		//$fecha   =$this->db->escape($fecha);
		$sucu=$this->datasis->traevalor('NROSUCU');
		$pre_caja=$this->db->escape($sucu);
		$cant=strlen($sucu);

		$this->load->library("sqlinex");

		$data[]=array('distinc'=>false,
									'table'  =>'viefac',
									'where'  =>"fecha = $fecha AND MID(caja,1,$cant)=$pre_caja");
		$data[]=array('distinc'=>false,
									'table'  =>'vieite',
									'where'  =>"fecha = $fecha AND MID(caja,1,$cant)=$pre_caja");
		$data[]=array('distinc'=>false,
									'table'  =>'viepag',
									'where'  =>"f_factura = $fecha AND MID(caja,1,$cant)=$pre_caja");
		$data[]=array('distinc'=>false,
									'table'  =>'fiscalz',
									'where'  =>"fecha = $fecha AND MID(caja,1,$cant)=$pre_caja");

		$this->sqlinex->export($data,'ve'.$fecha.'_'.$sucu);
	}

	function exmaes(){
		$this->load->library("sqlinex");
		$data[]=array('table'  =>'dpto');
		$data[]=array('table'  =>'fami');
		//$data[]=array('table'  =>'grupo');
		$data[]=array('table'  =>'maes');
		$fecha=date('d-m-Y');
		$this->sqlinex->export($data,'invent'.$fecha);
	}
}   
?>