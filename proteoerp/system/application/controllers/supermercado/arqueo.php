<?php
class Arqueo extends Controller {
	
	function Arqueo() {
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index($fecha=date('d/m/Y')) {
		$data['fecha']=$fecha;
		$this->load->view('view_arqueo', $data);
	}
}
?>
