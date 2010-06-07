<?php
class vpresupuesto extends Controller {
	
	function vpresupuesto(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->load->database();
	}
	function ver($numero='00042617'){
		$this->load->helper('string');
		$protocolo=explode('/',$_SERVER['SERVER_PROTOCOL']);
		$_direccion=$protocolo[0].'://'.$_SERVER['SERVER_NAME'].'/'.trim_slashes($this->config->item('base_url'));
		
		$mSQL_1 = $this->db->query("SELECT fecha,numero,cod_cli,nombre,impuesto,gtotal,stotal FROM pmay  WHERE numero=$numero");
		$mSQL_2 = $this->db->query("SELECT codigo,descrip,cantidad,fraccion,precio,importe from itpmay WHERE numero=$numero");
		$row = $mSQL_1->row();

				
		$data['fecha']=$row->fecha;
		$data['numero'] =$row->numero;
		$data['cod_cli']=$row->cod_cli;
		$data['nombre']=$row->nombre;
		$data['stotal']=$row->stotal;
		$data['gtotal']=$row->gtotal;
		$data['impuesto']=$row->impuesto;
		$data['detalle']=$mSQL_2->result();	
		$data['_direccion']=$_direccion;

		$this->load->plugin('html2pdf');
		$html = $this->load->view('view_vpresupuesto', $data, true);
		pdf_create($html,'nombrepdf');
		//echo $html;
		//http://192.168.0.99/proteoerp/ventas/vpresupuesto/ver/00042617
		//$this->load->view('view_vpresupuesto', $data);
	} 
}
?>
