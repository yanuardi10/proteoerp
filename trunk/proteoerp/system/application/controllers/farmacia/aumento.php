<?php

class Aumento extends Controller{
	function Aumento(){
		parent::Controller();
	}

	function index(){
		$this->load->helper('form');
		$data['titulo1'] = "<h1>Aumento de precio</h1>\n";
		$data['cuerpo']  = form_open('farmacia/aumento/procesa');
		$data['cuerpo'] .= form_input('aumenta').'%';
		$data['cuerpo'] .= form_submit('pasa','Aceptar');
		$data['cuerpo'] .= form_close();
		$this->load->view('view_rapido', $data);
	}

	function procesa(){
		$paso=false;
		$auporcent=($_POST['aumenta']+100)/100;
		
		$msql="UPDATE into sinv SET 
		dolar=ROUND(dolar*$auporcent,2), 
		base1=ROUND(dolar*100/100-margen1), 
		base2=ROUND((dolar*100/100-margen1)*(100-margen2)/100,2),
		base3=ROUND((dolar*100/100-margen1)*(100-margen3)/100,2),
		base4=ROUND((dolar*100/100-margen1)*(100-margen4)/100,2),
		precio1=ROUND(base1*1.09,2),
		precio2=ROUND(base2*1.09,2),
		precio3=ROUND(base3*1.09,2),
		precio4=ROUND(base4*1.09,2),
		WHERE clave!='REGULADO'";
		//$paso=$this->db->simple_query($msql);
		
		if($paso)
			$data['titulo1'] = "<h1>Aumento realizado Completada</h1>\n";
		else
			$data['titulo1'] = "<h1>Error en el aumento</h1>\n";
		$data['vaina'] = '';

		$this->load->view('view_rapido', $data);
	}
}
?>