<?php
class extimpor extends Controller {

	function extimpor(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->titulo='Tabla importada';
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');
		$id_tabla = 1;
		$tabla    = 'impor_data';
		$select   = $titu=array();

		//Se trae la cantidad de columnas
		$dbide_tabla = $this->db->escape($id_tabla);
		$ccolum=$this->datasis->dameval("SELECT MAX(aa.cana) FROM (SELECT COUNT(*) AS cana FROM $tabla WHERE id_tabla=$dbide_tabla GROUP BY fila) AS aa");

		//Se trae la definicion de la tabla
		$query = $this->db->query("SELECT columna,valor,tipo FROM `impor_data` WHERE fila=0 AND id_tabla=$dbide_tabla");
		foreach ($query->result() as $row) $titu[$row->columna]=$row->valor;

		$uri = anchor('finanzas/bmov/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');
		$grid = new DataGrid('Tabla importada');		
		$grid->db->from($tabla);
		$grid->db->where('id_tabla',$id_tabla);
		$grid->db->where('fila >',0);
		$grid->db->groupby('fila');
		$grid->per_page = 15;

		for($i=0;$i<$ccolum;$i++){
			$select[]="GROUP_CONCAT(IF(columna=$i,valor,NULL)) AS c$i";
			$titulo=(isset($titu[$i]))? $titu[$i]: 'Columna '.$i+1;
			$grid->column_orderby($titulo,"c$i","c$i");
		}
		$grid->db->select($select);
		$grid->build();

		$data['content'] = $grid->output;
		$data['title']   = heading('Tabla importada');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);		
	}

	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXIST `impor_data` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`id_tabla` int(10) DEFAULT '0',
			`fila` int(10) DEFAULT NULL,
			`columna` int(10) DEFAULT NULL,
			`valor` varchar(200) DEFAULT NULL,
			`tipo` varchar(20) DEFAULT NULL,
			`destino` varchar(100) DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `id_tabla` (`id_tabla`,`fila`,`columna`)
		) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COMMENT='Contenido de las tablas a importar'";

		var_dump($this->db->simple_query($mSQL));
	}
}