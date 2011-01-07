<?php
class Desarrollo extends Controller{
	function Desarrollo(){
		parent::Controller();
	}

	function index(){

	}
	
	function camposdb(){
		$db=$this->uri->segment(3);
		if($db===false){
			exit('Debe especificar en la uri la tabla');
		}
		$query = $this->db->query("DESCRIBE $db");

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$str='$data[\''.$row->Field."']";
				$str=str_pad($str,20);
				echo $str."='';\n";
			}
		} 
	}
	
	function lcamposdb(){
		$db =$this->uri->segment(3);
		$pre=$this->uri->segment(4);
		if($pre!==FALSE)
			$ant="$pre.";
		else
			$ant='';
		if($db===false){
			exit('Debe especificar en la uri la tabla');
		}
		$query = $this->db->query("DESCRIBE $db");

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$str=$row->Field.",";
				echo $ant.$str;
			}
		} 
	}

	function genecrud(){
		
		
		
	}

}
?>