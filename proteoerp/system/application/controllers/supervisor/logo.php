<?php
class logo extends Controller {
	var $upload_path;
	function logo(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->helper('download');
		$this->load->library('path');
		$path=new Path();
		$path->setPath($this->config->item('base_url'));
		$path->append('images');
		$this->upload_path =$path->getPath().'/';
	}

	function index(){
		$this->datasis->modulo_id(310,1);
		redirect('supervisor/logo/carga');
	}

	function carga(){
		$this->rapyd->load('dataform');
		$img="";
		$form = new DataForm('supervisor/logo/carga/ver');
		$form->archivo = new uploadField('Logo de la Empresa','logo');
		$form->archivo->upload_path   = $this->upload_path;
		$form->archivo->allowed_types = 'jpg';
		$form->archivo->overwrite     = true;
		$form->archivo->append('Solo imagenes JPG');
		$form->archivo->file_name = url_title('logo.jpg');

		$form->submit('btnsubmit','Subir');
		$form->build_form();
		
		$img="<table align='center'>
				<tr><td>LOGO ACTUAL</td></tr>
				<tr><td><img src='".$this->upload_path."/logo.jpg'  width=150 border=0></a></td></tr>
			</table>";

		$data['content'] = $form->output.br().$img;

		$data['title']   = '<h1>Subir Archivo</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function formato(){
		Header('Content-type: image/png');
		//$titu  = $this->datasis->traevalor('TITULO1');
		$ancho = 127; $alto=100;
		$im    = ImageCreate($ancho, $alto);
		$red   = ImageColorAllocate($im, 255, 0, 0);
		$white = ImageColorAllocate($im, 255, 255, 255);
		$blue  = ImageColorAllocate($im, 0, 0, 255);
		$black = ImageColorAllocate($im, 0, 0, 0);
		$titu  = 'Logotipo';
		$font_width = ImageFontWidth(5);               // para calcular el grosor de la fuente 
		$string_width = $font_width * (strlen($titu)); // y calculamos la lingitud del strig 
		
		ImageFill($im, 0, 0, $red);
		//Escribimos el string en (210,30) en negro 
		ImageString($im, 5, 5, 1, $titu, $black); //El 5 viene a ser el tamaño de la letra 1-5 
		
		$font_width = ImageFontWidth(5);
		// y calculamos la lingitud del strig 
		$string_width = $font_width * (strlen($titu));
		// y añadimos la linia de subrallado en (210,50) en negro 
		//ImageLine($im, 210, 50, (210+$string_width), 50, $black);
		ImagePng($im);
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