<?php
class edifubica extends Controller {
	var $titp='Ubicaciones dentro de una edificaci&oacute;n';
	var $tits='Ubicaciones dentro de una edificaci&oacute;n';
	var $url ='construccion/edifubica/';

	function edifubica(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('A02',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'edifubica');

		$filter->descripcion = new inputField('Descripci&oacute;n','descripcion');
		$filter->descripcion->rule      ='max_length[50]';
		$filter->descripcion->size      =52;
		$filter->descripcion->maxlength =50;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Id',$uri,'id','align="left"');
		$grid->column_orderby('Id_edif','<nformat><#id_edif#></nformat>','id_edif','align="right"');
		$grid->column_orderby('Descripcion','descripcion','descripcion','align="left"');

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

		$edit = new DataEdit($this->tits, 'edifubica');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->id_edif = new inputField('Id_edif','id_edif');
		$edit->id_edif->rule='max_length[11]';
		$edit->id_edif->size =13;
		$edit->id_edif->maxlength =11;

		$edit->descripcion = new inputField('Descripcion','descripcion');
		$edit->descripcion->rule='max_length[50]';
		$edit->descripcion->size =52;
		$edit->descripcion->maxlength =50;

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
		if (!$this->db->table_exists('edifubica')) {
			$mSQL="CREATE TABLE `edifubica` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `id_edif` INT(11) NULL DEFAULT NULL,
			  `descripcion` CHAR(50) NULL DEFAULT NULL,
			PRIMARY KEY (`id`)
			)
			COMMENT='Ubicaciones dentro de una edificacion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}
	}

}