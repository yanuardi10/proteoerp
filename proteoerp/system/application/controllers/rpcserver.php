<?php
class Rpcserver extends Controller {

	function index(){
		$this->load->library('xmlrpc');
		$this->xmlrpc->xmlrpc_defencoding=$this->config->item('charset');
		$this->load->library('xmlrpcs');
		$this->xmlrpcs->xmlrpc_defencoding=$this->config->item('charset');

		$config['functions']['sprecios'] = array('function' => 'Rpcserver.precio_supermer');
		$config['functions']['ttiket']   = array('function' => 'Rpcserver.traer_tiket');
		$config['functions']['cea']      = array('function' => 'Rpcserver.ComprasEmpresasAsociadas');

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
				foreach($row AS $ind=>$val){
					$row[$ind]=base64_encode($val);
				}
				$tiket[] = serialize($row);
			}
		}else{
			$response = array(array(),'struct');
		}
		$response = array($tiket,'struct');
		return $this->xmlrpc->send_response($response);
	}

	function ComprasEmpresasAsociadas($request){
		$parameters = $request->output_parameters();

		$ult_ref=$parameters['0'];
		$cod_cli=$parameters['1'];
		$usr    =$parameters['1'];
		$pwd    =$parameters['2'];

		$mSQL="SELECT numero,fecha,TRIM(nfiscal) AS nfiscal FROM sfac WHERE cod_cli=? AND numero > ? AND tipo_doc='F' LIMIT 5";
		$query = $this->db->query($mSQL,array($cod_cli,$ult_ref));

		$compras=array();
		if ($query->num_rows() > 0){ 
			foreach ($query->result_array() as $row){
				$mmSQL="SELECT TRIM(codigoa) AS codigoa,TRIM(desca) AS desca,cana,preca,tota,iva FROM sitems WHERE numa=? AND tipoa='F'";
				$qquery = $this->db->query($mmSQL,array($row['numero']));

				foreach($row AS $ind=>$val){
					$row[$ind]=base64_encode($val);
				}
				$compras[] = serialize($row);

				$it=array();
				foreach ($qquery->result_array() as $rrow){
					foreach($rrow AS $ind=>$val){
						$rrow[$ind]=base64_encode($val);
					}
					$it[]=$rrow;
				}

				$compras[] = serialize($it);
			}
		}

		$response = array($compras,'struct');
		return $this->xmlrpc->send_response($response);
	}
}