<?php

//almacenes
class actlocali extends Controller {

	var $url  ='supermercado/actlocali';
	var $tits ='Actuzalizar Localizaciones';
	
	function actlocali(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(276,1);
	}

	function index(){
		$this->rapyd->load("dataform");
		
		$form = new DataForm($this->url.'/index/process');
		
		$form->numero = new inputField('Numero de Inventario Fisico', 'numero');
		$form->numero->rule      ='required';
		$form->numero->size      =10;
		$form->numero->maxlength = 8;
		$form->numero->minlength = 8;
		
		$form->locali = new inputField('Localizacion a colocar', 'locali');
		$form->locali->rule      ='required';
		$form->locali->size      =10;
		
		$form->submit('btnsubmit','Actualizar');
		$form->build_form();

		if ($form->on_success()){
			$numero=$form->numero->newValue;
			$locali=$form->locali->newValue;
			redirect($this->url."/actualiza/".$numero."/".raencode($locali));
		}

		$data['content'] = $form->output;
		$data['title']   = '<h1>'.$this->tits.'</h1>';
		$data['script']  = '';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
		
	}

	function actualiza($numero,$locali){
		$locali=radecode($locali);
		$numero=$this->db->escape($numero);
		$locali=$this->db->escape($locali);
		
		$bool=$this->db->query("CALL sp_maes_actlocali($numero,$locali)");
		
		if($bool)$salida="Se actuzalizo correctamente</br>";
		else $salida="<div class='alert'>No se pudo actualizar la localizacion</div>";
		
		$data['content'] = $salida.anchor($this->url.'/index','Regresar');
		$data['title']   = '<h1>'.$this->tits.'</h1>';
		$data['script']  = '';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		$mSQL="
		CREATE PROCEDURE `sp_maes_actlocali`(IN `Numero` VARCHAR(50), IN `Locali` VARCHAR(50))
		BEGIN
		UPDATE maesfisico a JOIN ubic b ON a.codigo = b.codigo AND a.ubica=b.ubica  SET b.locali=Locali WHERE a.numero=Numero;
		END
		";
		$this->db->query($mSQL);
	}
}
?>

