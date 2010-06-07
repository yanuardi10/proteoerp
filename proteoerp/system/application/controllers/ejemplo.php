<?php 
class ejemplo extends Controller{

	function ejemplo(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->load->library("menues");
		$this->load->database();
	}

	function index(){
		redirect("/ejemplo/dragdrop");
	}
	function dragdrop(){
		echo 'katy';
	}
	
}
?>