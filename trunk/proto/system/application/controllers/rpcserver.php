<?php
class Rpcserver extends Controller {

	function index(){
		$this->load->library('xmlrpc');
		$this->load->library('xmlrpcs');
		
		$config['functions']['sprecios'] = array('function' => 'Rpcserver.precio_supermer');
		$config['functions']['ttiket']   = array('function' => 'Rpcserver.traer_tiket');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();
	}

	function precio_supermer($request){
		$parameters = $request->output_parameters();

		$codigo=$parameters['0'];
		$query = $this->db->query("SELECT precio1,precio2,precio3,precio4,precio5, descrip, barras FROM maes WHERE codigo=".$this->db->escape($codigo));

		if ($query->num_rows() > 0){
			$row = $query->row();
			$response = array(
					array(
							0 => $row->precio1,
							1 => $row->precio2,
							2 => $row->precio3,
							3 => $row->precio4,
							4 => $row->precio5,
							5 => $row->descrip,
							6 => $row->barras),
					'struct');
		}else{
			$response = array(
					array(),
					'struct');
		}
		return $this->xmlrpc->send_response($response);
	}


	function traer_tiket($request){
		$parameters = $request->output_parameters();
		$fechad=$parameters['0'];
		
		$query = $this->db->query("SELECT id,padre,pertenece,prioridad,usuario,contenido,estampa,actualizado,estado FROM tiket WHERE estampa>'$fechad' AND estampa<=NOW() AND usuario<>'TRANF'");
		//$query = $this->db->query("SELECT id,padre,pertenece,prioridad,usuario,contenido,estampa,actualizado,estado FROM tiket LIMIT 3");
		
		$tiket=array();
		if ($query->num_rows() > 0){ 
			foreach ($query->result_array() as $row){
				$tiket[] = serialize($row);
			}
		}else{
			$response = array(array(),'struct');
		}
		$response = array($tiket,'struct');
		return $this->xmlrpc->send_response($response);
	}
}    
?>