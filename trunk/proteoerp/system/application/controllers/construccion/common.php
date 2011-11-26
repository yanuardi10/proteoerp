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

	//Trae los datos del inmueble
	function get_dinmue(){
		$inmue=$this->input->post('inmueble');
		if($inmue!==false){
			$dbinmue=$this->db->escape($inmue);
			$mSQL='SELECT area,GREATEST(IFNULL(preciomt2e,0),IFNULL(preciomt2c,0),IFNULL(preciomt2a,0)) AS preciomt2 FROM edinmue WHERE id='.$dbinmue;
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$row = $query->row();
				$data=json_encode($row);
				echo $data;
			}
			
		}
	}
}