<?php
class Dashboard extends Controller {
	var $cant;

	function dashboard(){
		parent::Controller();
		$this->cant=10;
	}
	
	function index(){
		$this->session->set_userdata('panel', $this->uri->segment(3));
		$data['titulo1']  = '';
		if ($this->datasis->login())
			$data['titulo1']  = 'aqui va el dashboard';
		$this->layout->buildPage('bienvenido/home', $data);
	}

	function ufactura(){
		$mSQL="SELECT fecha,nombre,totals FROM sfac ORDER BY fecha DESC LIMIT $this->cant ";
		$row=array();
		$query = $this->db->query($mSQL);
		foreach ($query->result_array() as $row)
			$retorna[]=$row;
		echo json_encode($retorna);
		$query->free_result();
	}

	function ucompra() { //scst
		$mSQL="SELECT fecha,recep,numero,nombre,montonet,vence,credito FROM scst ORDER BY fecha DESC LIMIT $this->cant ";
		$row=array();
		$query= $this->db->query($mSQL);
		foreach($query->result_array() as $row)
			$retorna[]=$row;
		echo json_encode($retorna);
		$query->free_result();
	}

	function ulogusu(){ //logusu
		$mSQL="SELECT usuario,fecha,hora,modulo FROM logusu ORDER BY fecha DESC LIMIT $this->cant ";
		$row=array();
		$query= $this->db->query($mSQL);
		foreach($query->result_array() as $row)
			$retorna[]=$row;
		echo json_encode($retorna);
		$query->free_result();
	}

	function ustra(){  //stra
		$mSQL="SELECT numero,fecha,envia,recibe,observ1,observ2,totalg,usuario,hora,transac FROM stra ORDER BY fecha DESC LIMIT $this->cant ";
		$row=array();
		$query= $this->db->query($mSQL);
		foreach($query->result_array() as $row)
			$retorna[]=$row;
		echo json_encode($retorna);
		$query->free_result();
	}
	
	function uscliente(){ //scli
		$mSQL="SELECT cliente,nombre,contacto,grupo,gr_desc,formap,tipo,dire11,dire12,ciudad1,dire21,ciudad2,telefono,telefon2,zona,cuenta,repre,comisio,rifci,tiva,nomfis,riffis FROM scli ORDER BY cliente DESC LIMIT $this->cant ";
		$row=array();
		$query= $this->db->query($mSQL);
		foreach($query->result_array() as $row)
			$retorna[]=$row;
		echo json_encode($retorna);
		$query->free_result();
	}
}
?>