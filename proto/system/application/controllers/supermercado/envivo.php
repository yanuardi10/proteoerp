<?php
class Envivo extends Controller {
	function Envivo(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id('123',1);
		$this->rapyd->load("fields","datatable");

		$atts = array(
			  'width'      => '530',
			  'height'     => '600',
			  'scrollbars' => 'yes',
			  'status'     => 'yes',
			  'resizable'  => 'yes',
			  'screenx'    => '0',
			  'screeny'    => '0');

		$table = new DataTable(null);
		$table->cell_attributes = 'style="vertical-align:middle;"';

		$table->db->select(array('caja','ubica'));
		$table->db->from("caja");
		$table->db->where("ubica REGEXP  '^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\\.([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])){3}$' ");
		$table->db->orderby('caja');

		$table->per_row  = 5;
		$table->per_page = 15;

		//$table->cell_template = "<a href='".site_url('/supermercado/envivo/caja/<#ubica#>')."' target='vencaja' >". image('caja_abierta.gif',"Caja  <#caja#>", array('border'=>0,'align'=>'center')).'</a>'.'<br>Caja <#caja#>';
		$table->cell_template = anchor_popup('/supermercado/envivo/caja/<#ubica#>',image('caja_abierta.gif',"Caja  <#caja#>", array('border'=>0,'align'=>'center')),$atts).'<br>Caja <#caja#>';
		$table->build();

		$data['content'] = '<center>'.$table->output.'</center>';
		$data['title']   = "<h1>Ventas en vivo</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function caja($ip=NULL){
		$this->datasis->modulo_id(123,1);
		echo '<HTML>
		<META HTTP-EQUIV="Refresh" CONTENT="1">
		<BODY>';
		
		if (empty($ip))
			$salida='<center>Seleccione una caja</center>';
		else
			$salida=@file_get_contents("http://$ip/venta.html");
		if (empty($salida))
			$salida='<center>Caja apagada o no <b>actualizada</b><br> Favor comunicarse con el departamento de computaci&oacute;n.</center>';
		echo $salida;
		
		echo '</BODY>	
		</HTML>';
	}
	
	//function caja($ip=NULL){
	//	$this->datasis->modulo_id(123,1);
	//	if (empty($ip))
	//		$salida='<center>Seleccione una caja</center>';
	//	else
	//		$salida=@file_get_contents("http://$ip/venta.html");
	//	if (empty($salida))
	//		$salida='<center>Caja apagada o no <b>actualizada</b><br> Favor comunicarse con el departamento de computaci&oacute;n.</center>';
	//	echo $salida;
	//}
}
?>