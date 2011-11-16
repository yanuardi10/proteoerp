<?php
class common extends Controller {
	var $titp='Inmuebles';
	var $tits='Inmuebles';
	var $url ='construccion/edinmue/';

	function common(){
		parent::Controller();
	}

	function index(){
		
	}

	function get_ubic(){
		$edif=$this->input->post('edif');
		echo "<option value=''>Seleccionar</option>";

		if($edif!==false){
			$dbedif=$this->db->escape($edif);
			$mSQL=$this->db->query("SELECT id,descripcion FROM `edifubica` WHERE id_edif=$dbedif ORDER BY descripcion");

			if($mSQL){
				foreach($mSQL->result() AS $fila ){
					echo "<option value='".$fila->id."'>".$fila->descripcion."</option>";
				}
			}
		}
	}

	function get_inmue(){
		$edif=$this->input->post('edif');
		echo "<option value=''>Seleccionar</option>";

		if($edif!==false){
			$dbedif=$this->db->escape($edif);
			$mSQL=$this->db->query("SELECT id,TRIM(descripcion) AS nombre FROM `edinmue` WHERE status='D' AND edificacion=$dbedif ORDER BY descripcion");

			if($mSQL){
				foreach($mSQL->result() AS $fila ){
					echo "<option value='".$fila->id."'>".$fila->nombre."</option>";
				}
			}
		}
	}
}