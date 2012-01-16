<?php
class esta extends Controller {
	var $titp='Estaciones de producci&oacute;n';
	var $tits='Estaciones de producci&oacute;n';
	var $url ='inventario/esta/';

	function esta(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('323',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'esta');

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->rule      ='max_length[30]';
		$filter->nombre->size      =32;
		$filter->nombre->maxlength =30;

		$filter->descrip = new textareaField('Descripci&oacute;n','descrip');
		$filter->descrip->rule      ='max_length[8]';
		$filter->descrip->cols = 70;
		$filter->descrip->rows = 4;

		$filter->jefe = new inputField('Jefe','jefe');
		$filter->jefe->rule      ='max_length[5]';
		$filter->jefe->size      =7;
		$filter->jefe->maxlength =5;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Estaci&oacute;n',$uri,'estacion','align="left"');
		$grid->column_orderby('Nombre','nombre','nombre','align="left"');
		$grid->column_orderby('Descripci&oacute;n','descrip','descrip','align="left"');
		$grid->column_orderby('Jefe','jefe','jefe','align="left"');

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

		$edit = new DataEdit($this->tits, 'esta');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->estacion = new inputField('Estaci&oacute;n','estacion');
		$edit->estacion->rule='max_length[5]';
		$edit->estacion->size =7;
		$edit->estacion->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[30]';
		$edit->nombre->size =32;
		$edit->nombre->maxlength =30;

		$edit->descrip = new textareaField('Descripci&oacute;n','descrip');
		$edit->descrip->rule='max_length[8]';
		$edit->descrip->cols = 70;
		$edit->descrip->rows = 4;

		$edit->jefe = new inputField('Jefe','jefe');
		$edit->jefe->rule='max_length[5]';
		$edit->jefe->size =7;
		$edit->jefe->maxlength =5;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$script= '<script type="text/javascript" > 
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});
		</script>';

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$data['script'] .= $script;
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
		if (!$this->db->table_exists('esta')) {
			$mSQL="CREATE TABLE `esta` (
			  `estacion` varchar(5) NOT NULL DEFAULT '',
			  `nombre` varchar(30) DEFAULT NULL,
			  `descrip` text,
			  `jefe` char(5) DEFAULT NULL COMMENT 'tecnico',
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `vendedor` (`estacion`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($mSQL);
		}
	}
}