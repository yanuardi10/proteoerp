<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * iaCont Inteligencia artificial para contratos
 *
 * @author		Andres Hocevar
 * @version		0.1
 * @filesource
 **/

class iacont {

	var $data;
	var $soy;

	function iaCont(){
		$this->CI =& get_instance();
	}

	function reconoce($par){
		$posibles=array('partida','certificacion','bitacora');
		foreach($posibles AS $func){
			if ($this->$func($par)){
				$this->soy=$func;
				break;
			}
		}
	}

	function partida($par){
		$pattern="/^(?<partida>\w+) +(?<unidad>\w+) +(?<cantidad>[0-9,\.]+) +(?<precio>[0-9,\.]+)/i";

		$matches=array();
		$con=preg_match_all($pattern,$par,$matches);
		if($con>0){
			$p['partida']  = $matches['partida'][0];
			$p['unidad']   = $matches['unidad'][0];
			$p['cantidad'] = cadAnum($matches['cantidad'][0]);
			$p['precio']   = cadAnum($matches['precio'][0]);
			$p['monto']    = $p['cantidad']*$p['precio'];

			$mSQL='SELECT descrip FROM obpa WHERE codigo='.$this->CI->db->escape($p['partida']);
			$query = $this->CI->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$p['descrip']  =trim($row->descrip);
				}
			}
			$this->data=$p;
			return true;
		}
		return false;
	}

	function certificacion($par){
		$pattern="/^(?<partida>\w+) +(?<monto>[0-9,\.]+)/i";

		$matches=array();
		$con=preg_match_all($pattern,$par,$matches);
		if($con>0){
			$p['partida']  = $matches['partida'][0];
			$p['monto']   = cadAnum($matches['monto'][0]);

			$mSQL='SELECT descrip FROM obpa WHERE codigo='.$this->CI->db->escape($p['partida']);
			$query = $this->CI->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					$p['descrip']  =trim($row->descrip);
				}
			}

			$this->data=$p;
			return true;
		}
		return false;
	}

	function bitacora($par){
		$p['contenido']=$par;
		$this->data=$p;
		return true;
	}

}
