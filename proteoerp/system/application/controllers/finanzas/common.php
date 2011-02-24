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

}