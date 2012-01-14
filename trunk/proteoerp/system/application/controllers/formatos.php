<?php
class Formatos extends Controller{

	var $_direccion;
	function Formatos(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->load->library('numletra');
		$this->load->plugin('numletra');
		$this->load->helper('string');
	}

	function index(){
		
	}

	function ver1(){
		$parametros= func_get_args();
		$this->_direccion='http://localhost/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_arch_nombre=implode('-',$parametros);
			$_fnombre=array_shift($parametros);
			$_dbfnombre=$this->db->escape($_fnombre);
			$this->load->plugin('html2pdf');
			$query = $this->db->query('SELECT proteo FROM formatos WHERE nombre='.$_dbfnombre);
			if ($query->num_rows() > 0){
				$row = $query->row();
				ob_start();
					echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
					$_html=ob_get_contents();
				@ob_end_clean();
				if(strlen($_html)>0)
					pdf_create($_html, $_arch_nombre);
				else
					echo 'Formato no definido';
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function ver(){
		$parametros= func_get_args();
		$this->_direccion='http://localhost/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_arch_nombre=implode('-',$parametros);
			$_fnombre=array_shift($parametros);
			$_dbfnombre=$this->db->escape($_fnombre);
			$this->load->library('dompdf/cidompdf');
			$query = $this->db->query('SELECT proteo FROM formatos WHERE nombre='.$_dbfnombre);
			if ($query->num_rows() > 0){
				$row = $query->row();
				ob_start();
					echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
					$_html=ob_get_contents();
				@ob_end_clean();
				if(strlen($_html)>0)
					$this->cidompdf->html2pdf($_html,$_arch_nombre);
				else
					echo 'Formato no definido';
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function verhtml(){
		$parametros= func_get_args();
		$this->_direccion='/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_fnombre  = array_shift($parametros);
			$_dbfnombre= $this->db->escape($_fnombre);
			$query = $this->db->query('SELECT proteo FROM formatos WHERE nombre='.$_dbfnombre);
			if ($query->num_rows() > 0){
				$row = $query->row();
				eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function instalar(){
		$campos=$this->db->list_fields('formatos');

		if(!in_array('proteo'  ,$campos)) $this->db->simple_query("ALTER TABLE `formatos` ADD `proteo` TEXT NULL AFTER `forma`");
		if(!in_array('harbour' ,$campos)) $this->db->simple_query("ALTER TABLE `formatos` ADD `harbour` TEXT NULL AFTER `proteo`");
		if(!in_array('tcpdf'   ,$campos)) $this->db->simple_query("ALTER TABLE `formatos`  ADD COLUMN `tcpdf` TEXT NULL AFTER `forma`");
	}
}