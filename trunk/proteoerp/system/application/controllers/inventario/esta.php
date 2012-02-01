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

		$filter = new DataFilter($this->titp);
		$sel=array('a.id','a.estacion','a.nombre','a.descrip','b.nombre AS jefe');
		$filter->db->select($sel);
		$filter->db->from('esta AS a');
		$filter->db->join('estajefe AS b','a.jefe=b.codigo');

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->db_name   = 'a.nombre';
		$filter->nombre->rule      = 'max_length[30]';
		$filter->nombre->size      = 32;
		$filter->nombre->maxlength = 30;

		$filter->descrip = new textareaField('Descripci&oacute;n','descrip');
		$filter->descrip->db_name   = 'a.descrip';
		$filter->descrip->rule      ='max_length[8]';
		$filter->descrip->cols = 70;
		$filter->descrip->rows = 4;

		$filter->jefe = new dropdownField('Encargado','jefe');
		$filter->jefe->option('','Todos');
		$filter->jefe->options('SELECT codigo,nombre FROM estajefe ORDER BY nombre');

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Estaci&oacute;n',$uri,'estacion','align="left"');
		$grid->column_orderby('Nombre','nombre','nombre','align="left"');
		$grid->column_orderby('Descripci&oacute;n','descrip','descrip','align="left"');
		$grid->column_orderby('Encargado','jefe','jefe','align="left"');

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
		$edit->estacion->rule = 'max_length[5]|unique|required';
		$edit->estacion->size = 7;
		$edit->estacion->mode = 'autohide';
		$edit->estacion->maxlength = 5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[30]|required';
		$edit->nombre->size =32;
		$edit->nombre->maxlength =30;

		$edit->descrip = new textareaField('Descripci&oacute;n','descrip');
		$edit->descrip->cols = 70;
		$edit->descrip->rows = 4;

		$edit->ubica = new textareaField('Ubicaci&oacute;n F&iacute;sica','ubica');
		$edit->ubica->cols = 70;
		$edit->ubica->rows = 4;

		$edit->jefe = new dropdownField('Jefe','jefe');
		$edit->jefe->option('','Seleccionar');
		$edit->jefe->options('SELECT codigo,nombre FROM estajefe ORDER BY nombre');
		$edit->jefe->rule='required';

		$edit->buttons('modify', 'save', 'add','undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['script']  = script('jquery.js');
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
		$codigo  = $do->get('codigo');

		$this->db->like('estacion', $codigo);
		$this->db->from('sinvplabor');
		$cana = $this->db->count_all_results();
		if($cana!=0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Estacion con relaciones en inventario, no puede ser Borrado';
			return false;
		}
		return true;
	}

	function _post_insert($do){
		$codigo  = $do->get('codigo');
		$primary = implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary, codigo $codigo");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary, codigo $codigo");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);

		logusu($do->table,"Elimino $this->tits $primary ");
	}

	function instalar(){
		if (!$this->db->table_exists('esta')) {
			$mSQL="CREATE TABLE `esta` (
				`estacion` VARCHAR(5) NOT NULL DEFAULT '',
				`nombre` VARCHAR(30) NULL DEFAULT NULL,
				`descrip` TEXT NULL,
				`ubica` TEXT NULL,
				`jefe` VARCHAR(5) NULL DEFAULT NULL COMMENT 'tecnico',
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `vendedor` (`estacion`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}
	}
}
