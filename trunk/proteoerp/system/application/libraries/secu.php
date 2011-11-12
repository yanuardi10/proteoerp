<?php
class secu{
	var $ci;
	var $db;
	var $dbindex='default';

	function secu(){
		$this->ci =& get_instance();
		$this->db =$this->ci->load->database($this->dbindex,TRUE);
	}

	function es_logeado(){
		if($this->ci->session->userdata('logged_in')){
			return TRUE;
		}
		return FALSE;
	}

	function usuario(){
		return $this->ci->session->userdata('usuario');
	}

	function essuper(){
		if ($this->es_logeado()){
			$usuario = $this->db->escape($this->ci->session->userdata('usuario'));
			$query = $this->db->query("SELECT COUNT(*) AS cana FROM usuario WHERE us_codigo=$usuario AND supervisor='S'");
			if ($query->num_rows() > 0){
				$row = $query->row();
				return ($row->cana>0) ? TRUE : FALSE;
			}
		}
		return FALSE;
	}

	function puede($id){
		if ($this->es_logeado()){
			$usuario = $this->db->escape($this->ci->session->userdata('usuario'));
			$id      = $this->db->escape($id);
			//$query = $this->db->query("SELECT COUNT(*) AS cana FROM intrasida WHERE usuario=$usuario AND id=$id"); //Tortuga
			$query = $this->db->query("SELECT COUNT(*) AS cana FROM intrasida WHERE usuario=$usuario AND modulo=$id"); //Proteo
			if ($query->num_rows() > 0){
				$row = $query->row();
				return ($row->cana>0) ? TRUE : FALSE;
			}
		}
		return FALSE;
	}

	function es_shell(){
		return (isset($_SERVER['argv']) && !isset($_SERVER['SERVER_NAME'])) ? TRUE : FALSE;
	}


	function es_interno(){
		return $this->ip_interno($_SERVER['REMOTE_ADDR']);
	}

	function ip_interno($ip){
		//10.0.0.0    - 10.255.255.255  | 10.0.0.0/8
		//172.16.0.0  - 172.31.255.255  | 172.16.0.0/12
		//192.168.0.0 - 192.168.255.255 | 192.168.0.0/16
		return (preg_match("/^(10\\..+|192\\.168\\..+|172\\.(1[6-9]|2[0-9]|3[01])\\..+)$/", $ip)>0) ? TRUE : FALSE;
	}

	function cliente($codigo,$pws){
		$codigo = $this->db->escape($codigo);
		$pws    = $this->db->escape($pws);
		$query = $this->db->query("SELECT COUNT(*) AS cana FROM scli WHERE cliente=$codigo AND clave=$pws");
		if ($query->num_rows() > 0){
			$row = $query->row();
			return ($row->cana>0) ? TRUE : FALSE;
		}
		return FALSE;
	}
}