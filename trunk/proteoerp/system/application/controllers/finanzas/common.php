<?php
class Common extends controller {

	function _traetipo($codigo){
		$sql='SELECT tbanco FROM banc WHERE codbanc='.$this->db->escape($codigo);
		return $this->datasis->dameval($sql);
	}

}
