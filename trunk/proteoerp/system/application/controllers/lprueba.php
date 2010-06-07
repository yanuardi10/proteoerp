<?php
class Lprueba extends Controller {
 
	function Lprueba(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('download');
	}
	function crear(){
		$this->load->dbutil();
		$query = $this->db->query("select * FROM gitser where numero='000002' and proveed='FLORO'");
		$cvs=$this->dbutil->xml_from_result($query);
		$name = 'Archivo.xml';
		force_download($name, $cvs);
		
	}
	function xml($filename='Archivo.xml',$table='scaj'){
		$this->load->library("xml2sql");	
		$xml= new xml2sql($filename);
		$db=$xml->analizador();
		foreach ($db as $key => $val) {
			  //echo "<pre>";
		    //print_r($val);
		    //echo "</pre>";
		    $this->db->insert($table, $val);
		    echo 'insertado';
			
		}
		}
}   
?>
