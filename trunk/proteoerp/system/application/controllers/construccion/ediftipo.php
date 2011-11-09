<?php
class ediftipo extends Controller {
	var $titp='Tipos de Edificaciones';
	var $tits='Tipos de Edificaciones';
	var $url ='construccion/ediftipo/';

	function ediftipo(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('A01',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'ediftipo');

		$filter->descrip = new inputField('Descripci&oacute;n','descrip');
		$filter->descrip->rule      ='max_length[50]';
		$filter->descrip->size      =52;
		$filter->descrip->maxlength =50;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('descrip');
		$grid->per_page = 40;

		$grid->column_orderby('Id',$uri,'id','align="left"');
		$grid->column_orderby('Descripci&oacute;n','descrip','descrip','align="left"');

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

		$edit = new DataEdit($this->tits, 'ediftipo');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->rule='max_length[50]|required';
		$edit->descrip->size =52;
		$edit->descrip->maxlength =50;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
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
		if (!$this->db->table_exists('ediftipo')) {
			$mSQL="CREATE TABLE `ediftipo` (
				  `id` INT(11) NOT NULL AUTO_INCREMENT,
				  `descrip` CHAR(50) NULL DEFAULT NULL,
				  PRIMARY KEY (`id`)
				  )
				  COMMENT='Tipos de Edificaciones'
				  COLLATE='latin1_swedish_ci'
				  ENGINE=MyISAM
				  ROW_FORMAT=DEFAULT
				  AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}
	}

}