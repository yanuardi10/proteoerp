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
		echo $this->upload_path =$path->getPath().'/'; 
      
	} 
    
	function carga(){
		
		$this->rapyd->load('dataform');  
		                                 
		$form = new DataForm('supervisor/logo/carga/resp');
		
	  $form->archivo = new uploadField("Logo de la Empresa","logo");          
		$form->archivo->upload_path   = $this->upload_path;    
		$form->archivo->allowed_types = "jpg|png";                 
		$form->archivo->delete_file   =false;
		$form->archivo->thumb = array (63,91);
				         		                      				 
    $form->submit("btnsubmit","Subir");  
    $form->build_form();

    $data['content'] = $form->output;
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
