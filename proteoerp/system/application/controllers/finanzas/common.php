<?php
class Common extends controller {

	function _traetipo($codigo){
		$sql='SELECT tbanco FROM banc WHERE codbanc='.$this->db->escape($codigo);
		return $this->datasis->dameval($sql);
	}

	function _traemoneda($codigo){
		$sql='SELECT moneda FROM banc WHERE codbanc='.$this->db->escape($codigo);
		return $this->datasis->dameval($sql);
	}

	function _traebandata($codigo){
		$sql='SELECT tbanco,moneda,banco,saldo,depto,numcuent FROM banc WHERE codbanc='.$this->db->escape($codigo);
		return $this->datasis->damerow($sql);
	}

	function _traedatausr(){
		$usr=$this->session->userdata('usuario');
		$sql='SELECT vendedor,cajero,sucursal,almacen FROM usuario WHERE us_codigo='.$this->db->escape($usr);
		return $this->datasis->damerow($sql);
	}

	//Para el autocomplete
	function _automgas(){
	}
}