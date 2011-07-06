<?php
class buscador extends Controller {
	function buscador(){
		parent::Controller(); 
		$this->load->library('rapyd');
		if(!$this->datasis->essuper()) show_404();
	}


	function index(){
		$this->rapyd->load('dataform');

		$filter = new dataForm('buscador/index/procesar');
		$filter->valor = new inputField('Valor', 'valor');
		$filter->valor->rule = 'required';
		$filter->valor->size=20;

		$filter->submit('btnsubmit','Buscar');
		$filter->build_form();

		$sal='';
		if ($filter->on_success()){
			$this->load->library('table');
			$this->table->set_heading('Tabla', 'Campo', 'Coincidencias');
			$valor=$this->db->escape($filter->valor->newValue);

			$tables = $this->db->list_tables();
			foreach ($tables as $table){
				$fields = $this->db->list_fields($table);
				foreach ($fields as $field){
					$mSQL="SELECT COUNT(*) AS cana FROM `$table` WHERE `".$field."` = $valor";
					$cana=$this->datasis->dameval($mSQL);
					if($cana>0){
						$this->table->add_row($table,$field,$cana);
					}
				}
			}
			$sal = $this->table->generate();
		}

		$data['content'] = $filter->output.$sal;
		$data['title']   = heading('Busca un valor en toda la base de datos');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}