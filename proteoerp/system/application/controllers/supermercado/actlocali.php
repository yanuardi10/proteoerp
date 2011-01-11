<?php

//almacenes
class actlocali extends Controller {

	var $url  ='supermercado/actlocali';
	var $tits ='Actualizar Localizaciones';
	
	function actlocali(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id('31E',1);
	}
	
	function index(){
		
		$salida =anchor($this->url.'/locali',"Modificar Localizaciones");
		$salida.="</br>";
		$salida.=anchor($this->url.'/mfisicocero','Colocar Productos no contados en Cero(0)');
		
		$data['content'] = $salida;
		$data['title']   = '<h1>Menu</h1>';
		$data['script']  = '';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function locali(){
		$this->rapyd->load("dataform");
		
		$form = new DataForm($this->url.'/locali/process');
		
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

		$salida=anchor($this->url.'/index','Menu');

		$data['content'] = $form->output.$salida;
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
		$data['content'] = $salida.anchor($this->url.'/locali','Regresar');
		$data['title']   = '<h1>'.$this->tits.'</h1>';
		$data['script']  = '';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function mfisicocero(){
		$this->rapyd->load("dataform");
		
		$form = new DataForm($this->url.'/mfisicocero/process');
		
		$form->numero = new inputField('Numero de Inventario Fisico', 'numero');
		$form->numero->rule      ='required';
		$form->numero->size      =10;
		$form->numero->maxlength = 8;
		$form->numero->minlength = 8;
		
		$form->submit('btnsubmit','Actualizar');
		$form->build_form();

		if ($form->on_success()){
			$numero=$form->numero->newValue;
			redirect($this->url."/mfisicoactualiza/".$numero);
		}
		
		$salida=anchor($this->url.'/index','Menu');

		$data['content'] = $form->output.$salida;
		$data['title']   = '<h1>Colocar producto no contados en cero (0)</h1>';
		$data['script']  = '';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
		
	}
	
	function mfisicoactualiza($numero){
		$numero=$this->db->escape($numero);
		
		$cant = $this->datasis->dameval("SELECT COUNT(*) FROM maesfisico WHERE numero=$numero");
		if($cant>0){
			$bool=$this->db->query("CALL sp_maes_maesfis0($numero)");
			
			if($bool)$salida="Se colocaron TODOS los productos de inventario NO CONTADOS en cero (0)</br>";
			else $salida="<div class='alert'>No se pudo actualizar</div>";
		}else{
			$salida="<div class='alert'>No existe el numero de inventario indicado $numero</div>";
		}
		$data['content'] = $salida.anchor($this->url.'/mfisicocero','Regresar');
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
		
		$mSQL="
		BEGIN
		DECLARE mALMA CHAR(4) ;
		DECLARE mFECHA DATE ;
		
		SELECT ubica FROM maesfisico WHERE numero=mNUMERO LIMIT 1  INTO mALMA;
		SELECT fecha FROM maesfisico WHERE numero=mNUMERO LIMIT 1 INTO mFECHA;
		
		INSERT INTO maesfisico 
		SELECT 0 id,codigo,mALMA,'2011',0,0,0,0,mFECHA, mNUMERO, 'BLANCO',CURDATE(),CURTIME() 
		FROM maes a WHERE (SELECT COUNT(*) FROM maesfisico b WHERE a.codigo=b.codigo AND b.numero=mNUMERO)=0;
		
		END
		";
		
		var_dump($this->db->simple_query($mSQL));
		
	}
}
?>

