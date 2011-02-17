<?php
//egresos
class mgser extends Controller {

	function mgser(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(518,1);
	}
	function dataedit(){
		$this->rapyd->load('dataedit');
		
		$sprv=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
		'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
		'titulo'  =>'Buscar Proveedor');

		$bsprv=$this->datasis->modbus($sprv);

		$edit = new DataEdit("Modificar Egreso","gser");
		$edit->post_process("update","_actualiza");
		$edit->back_url = "finanzas/gser";
		
		$edit->transac = new inputField("Transacción","transac");
		$edit->transac->size = 15;
		$edit->transac->when = array("show");
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","Y-m-d");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->size = 10;
		$edit->fecha->rule= "required";
					
		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->rule= "required";
		$edit->numero->maxlength=8;

		$edit->codigo = new inputField("C&oacute;digo", "proveed");
		$edit->codigo->size =8;        
		$edit->codigo->maxlength=5;
		$edit->codigo->append($bsprv);
		$edit->codigo->rule= "required";
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size =  50;
		$edit->nombre->maxlength=40; 
		$edit->nombre->rule= "required";  
		
		$edit->buttons("save","undo","modify","back");
		$edit->build();
		
		$data['content'] = $edit->output;
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Egresos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function _actualiza(){
		$fecha=$this->input->post("fecha");
		$proveed=$this->input->post("proveed");
		$nombre=$this->input->post("nombre");
		$numero=$this->input->post("numero");
		$transac=$this->input->post("transac");

		$update="UPDATE gser SET serie='$numero' WHERE transac='$transac'";
		$this->db->query($update);
		
		$update2="UPDATE gitser SET fecha='$fecha', proveed='$proveed',numero='$numero' WHERE transac='$transac'";
		$this->db->query($update2);
		
		//MODIFICA SPRM
		$update3="UPDATE sprm SET fecha='$fecha', numero='$numero', cod_prv='$proveed',nombre='$nombre' WHERE tipo_doc='FC'AND transac='$transac'";
		$this->db->query($update3);
		
		//MODIFICA BMOV
		$update4="UPDATE bmov SET fecha='$fecha', numero='$numero', codcp='$proveed',nombre='$nombre' WHERE clipro='P' AND transac='$transac'";
		$this->db->query($update4);
		
		//MODIFICA RIVA
		$update5="UPDATE riva SET fecha='$fecha', numero='$numero',clipro='$proveed',nombre='$nombre' WHERE transac='$transac'";
		$this->db->query($update5);
		
		}
}
?>