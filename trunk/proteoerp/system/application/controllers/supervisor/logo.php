<?php
class logo extends Controller {
	var $upload_path;
	function logo(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('download');
		$this->load->library("path");
		$path=new Path();
		$path->setPath($this->config->item('base_url'));
		$path->append('images');
		$this->upload_path =$path->getPath().'/';

	}

	function index(){
		$this->datasis->modulo_id(310,1);
		redirect("supervisor/logo/carga");
	}

	function carga(){
		$this->rapyd->load('dataform');
		 $img="";
		$form = new DataForm('supervisor/logo/carga/ver');
 		$form->archivo = new uploadField("Logo de la Empresa","logo");
 		$form->archivo->upload_path   = $this->upload_path;
 		$form->archivo->allowed_types = "jpg";
 		$form->archivo->overwrite   =true;
 		$form->archivo->append('Solo imagenes JPG');
 		$form->archivo->file_name = url_title("logo.jpg");

 		$form->submit("btnsubmit","Subir");
 		$form->build_form();
 		
 			$img="<table align='center'>
 					<tr><td>LOGO ACTUAL</td></tr>
 					<tr><td><img src='".$this->upload_path."/logo.jpg'  width=150 border=0></a></td></tr>
 				  </table>";
	 		
 		$data['content'] = $form->output."<br>".$img;
	 
		$data['title']   = "<h1>Subir Archivo</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function resp(){
		echo 'hola';
	}

	function ver($file='logo.logo'){
		//$file = $this->input->post('archivo');
		//echo 'Archivo='.$file;
		if(empty($file)) return FALSE;
		$this->load->helper('file');
		$string = read_file("./uploads/archivos/$file");
		$string = $string;
		echo $string;
	}

	
	
}
?>
