<?php

//almacenes
class actlocali extends Controller {

	var $url  ='supermercado/actlocali';
	var $tits ='Actualizar Localizaciones';
	
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
		$form->locali->size      =10;
		
		$form->oper = new dropdownField("Toman en cuenta : ","oper");
		$form->oper->option("2","Todos");
		$form->oper->option("1","Solo los que fraccion sea mayor a cero (0)");
		$form->oper->option("0","Solo los que fraccion son igual a cero (0)");
		
		$form->submit('btnsubmit','Actualizar');
		$form->build_form();

		if ($form->on_success()){
			$numero=$form->numero->newValue;
			$locali=$form->locali->newValue;
			$oper  =$form->oper->newValue;
			redirect($this->url."/actualiza/".$numero.'/'.$oper."/".raencode($locali));
		}

		$data['content'] = $form->output;
		$data['title']   = '<h1>'.$this->tits.'</h1>';
		$data['script']  = '';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
		
	}

	function actualiza($numero,$oper,$locali=''){
		$locali=radecode($locali);
		$numero=$this->db->escape($numero);
		$locali=$this->db->escape($locali);
		
		$cant = $this->datasis->dameval("SELECT COUNT(*) FROM maesfisico WHERE numero=$numero");
		if($cant>0){
			$bool=$this->db->query("CALL sp_maes_actlocali($numero,$locali,$oper)");
			
			if($bool)$salida="Se actualizo correctamente</br>";
			else $salida="<div class='alert'>No se pudo actualizar la localizacion</div>";
		}else{
			$salida="<div class='alert'>No existe el numero de inventario indicado $numero</div>";
		}
		$data['content'] = $salida.anchor($this->url.'/index','Regresar');
		$data['title']   = '<h1>'.$this->tits.'</h1>';
		$data['script']  = '';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		$mSQL="
		CREATE PROCEDURE `sp_maes_actlocali`(IN `Numero` VARCHAR(50), IN `Locali` VARCHAR(50), IN `Oper` INT)  LANGUAGE SQL  NOT DETERMINISTIC  CONTAINS SQL  SQL SECURITY DEFINER  COMMENT '' BEGIN UPDATE maesfisico a JOIN ubic b ON a.codigo = b.codigo AND a.ubica=b.ubica SET b.locali=Locali WHERE a.numero=Numero AND ((a.fraccion>0)*(Oper=1)+(a.fraccion=0)*(Oper=0)+(a.fraccion>=0)*(Oper=2)); END;
		";
		var_dump($this->db->simple_query($mSQL));
	}
}
?>

