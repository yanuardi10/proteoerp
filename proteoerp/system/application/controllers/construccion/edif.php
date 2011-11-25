<?php
include('ediftipo.php');
class edif extends Controller {
	var $titp='Edificaciones';
	var $tits='Edificaciones';
	var $url ='construccion/edif/';

	function edif(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('A00',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->load->helper('text');

		$filter = new DataFilter($this->titp, 'edif');

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->rule      ='max_length[120]';
		$filter->nombre->maxlength =120;

		$filter->tipo = new dropdownField('Tipo','tipo');
		$filter->tipo->option('','Todos');
		$filter->tipo->options('SELECT id,descrip FROM `ediftipo` ORDER BY descrip');

		$filter->direccion = new inputField('Direcci&oacute;n','direccion');
		$filter->direccion->rule      ='max_length[8]';

		$filter->descripcion = new inputField('Descripci&oacute;n','descripcion');
		$filter->descripcion->rule      ='max_length[8]';

		$filter->promotora = new inputField('Promotora','promotora');
		$filter->promotora->rule      ='max_length[5]';
		$filter->promotora->size      =7;
		$filter->promotora->maxlength =5;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->use_function('character_limiter');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('N&uacute;mero'     ,$uri,'id','align="left"');
		$grid->column_orderby('Nombre'            ,'nombre','nombre','align="left"');
		$grid->column_orderby('Tipo'              ,'<nformat><#tipo#></nformat>','tipo','align="right"');
		$grid->column_orderby('Direcci&oacute;n'  ,'<character_limiter><#direccion#></character_limiter>','direccion','align="left"');
		$grid->column_orderby('Descripci&oacute;n','<character_limiter><#descripcion#></character_limiter>','descripcion','align="left"');
		$grid->column_orderby('Promotora'         ,'promotora','promotora','align="left"');

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

		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'promotora'),
		'titulo'  =>'Buscar Cliente');

		$boton=$this->datasis->modbus($scli);

		$edit = new DataEdit($this->tits, 'edif');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[120]|required';
		$edit->nombre->maxlength =120;

		$edit->tipo = new dropdownField('Tipo','tipo');
		$edit->tipo->option('','Seleccionar');
		$edit->tipo->options('SELECT id,descrip FROM `ediftipo` ORDER BY descrip');
		$edit->tipo->rule='max_length[10]|required';

		$edit->direccion = new textareaField('Direcci&oacute;n','direccion');
		//$edit->direccion->rule='max_length[255]';
		$edit->direccion->cols = 70;
		$edit->direccion->rows = 4;

		$edit->descripcion = new textareaField('Descripci&oacute;n','descripcion');
		//$edit->descripcion->rule='max_length[512]';
		$edit->descripcion->cols = 70;
		$edit->descripcion->rows = 4;

		$edit->promotora = new inputField('Promotora','promotora');
		$edit->promotora->rule='max_length[5]|existescli';
		$edit->promotora->size =7;
		$edit->promotora->maxlength =5;
		$edit->promotora->append($boton);

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
		ediftipo::instalar();
		if (!$this->db->table_exists('edif')) {
			$mSQL="CREATE TABLE `edif` (
				  `id` INT(11) NOT NULL AUTO_INCREMENT,
				  `nombre` CHAR(120) NULL DEFAULT NULL,
				  `tipo` INT(10) NULL DEFAULT NULL,
				  `direccion` TEXT NULL,
				  `descripcion` TEXT NULL,
				  `promotora` CHAR(5) NULL DEFAULT NULL,
				  PRIMARY KEY (`id`)
				  )
				  COMMENT='Edificaciones'
				  COLLATE='latin1_swedish_ci'
				  ENGINE=MyISAM
				  ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}
	}

}