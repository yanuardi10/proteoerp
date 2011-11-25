<?php
class eduso extends Controller {
	var $titp='Usos de los inmuebles';
	var $tits='Usos de los inmuebles';
	var $url ='construccion/eduso/';

	function eduso(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('A04',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'eduso');

		$filter->uso = new inputField('Uso','uso');
		$filter->uso->rule      ='max_length[80]';
		$filter->uso->maxlength =80;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Id',$uri,'id','align="left"');
		$grid->column_orderby('Uso','uso','uso','align="left"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);

	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'eduso');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->uso = new inputField('Uso','uso');
		$edit->uso->rule='max_length[80]|required';
		$edit->uso->maxlength =80;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add');
		$edit->build();
		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

	}

	function _pre_insert($do){
		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}

	function instalar(){
		if (!$this->db->table_exists('eduso')) {
			$mSQL="CREATE TABLE `eduso` (
				  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  `uso` CHAR(80) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				  PRIMARY KEY (`id`)
				  )
				  COMMENT='Usos de los inmuebles'
				  COLLATE='latin1_swedish_ci'
				  ENGINE=MyISAM
				  ROW_FORMAT=DEFAULT
				  AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}
	}

}