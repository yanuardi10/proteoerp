<?php
class Tecn extends Controller {
	var $titp='Tecnicos';
	var $tits='Tecnico';
	var $url ='taller/tecn/';

	function Tecn(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(216,1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'tecn');

		$filter->codigo = new inputField('C&oacute;digo','codigo');
		$filter->codigo->rule      ='max_length[5]';
		$filter->codigo->size      =7;
		$filter->codigo->maxlength =5;

		$filter->clave = new inputField('Clave','clave');
		$filter->clave->rule      ='max_length[5]';
		$filter->clave->size      =7;
		$filter->clave->maxlength =5;

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->rule      ='max_length[30]';
		$filter->nombre->size      =32;
		$filter->nombre->maxlength =30;

		$filter->telefono = new inputField('Tel&eacute;fono','telefono');
		$filter->telefono->rule      ='max_length[13]';
		$filter->telefono->size      =15;
		$filter->telefono->maxlength =13;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('C&oacute;digo'     ,$uri      ,'codigo'   ,'align="left"');
		$grid->column_orderby('Clave'             ,'clave'   ,'clave'    ,'align="left"');
		$grid->column_orderby('Nombre'            ,'nombre'  ,'nombre'   ,'align="left"');
		$grid->column_orderby('Direcci&oacute;n'  ,'direc1'  ,'direc1'   ,'align="left"');
		$grid->column_orderby('Tel&eacute;fono'          ,'telefono','telefono' ,'align="left"');
		$grid->column_orderby('Almacen'           ,'almacen' ,'almacen'  ,'align="left"');

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

		$edit = new DataEdit($this->tits, 'tecn');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->codigo = new inputField('C&oacute;digo','codigo');
		$edit->codigo->size =7;
		$edit->codigo->maxlength =5;
		$edit->codigo->rule      ='trim|required|unique';
		$edit->codigo->mode      ='autohide';

		$edit->clave = new inputField('Clave','clave');
		$edit->clave->rule='max_length[5]';
		$edit->clave->size =7;
		$edit->clave->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[30]';
		$edit->nombre->size =32;
		$edit->nombre->maxlength =30;

		$edit->direc1 = new inputField('Direcc&oacute;n','direc1');
		$edit->direc1->rule='max_length[35]';
		$edit->direc1->size =37;
		$edit->direc1->maxlength =35;

		$edit->direc2 = new inputField('Direcci&oacute;n 2','direc2');
		$edit->direc2->rule='max_length[35]';
		$edit->direc2->size =37;
		$edit->direc2->maxlength =35;

		$edit->telefono = new inputField('Tel&eacute;fono','telefono');
		$edit->telefono->rule='max_length[13]';
		$edit->telefono->size =15;
		$edit->telefono->maxlength =13;

		$edit->recargo = new inputField('Recargo','recargo');
		$edit->recargo->rule='max_length[5]|numeric';
		$edit->recargo->css_class='inputnum';
		$edit->recargo->size =7;
		$edit->recargo->maxlength =5;

		$edit->almacen = new inputField('Almacen','almacen');
		$edit->almacen->rule='max_length[4]';
		$edit->almacen->size =6;
		$edit->almacen->maxlength =4;

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
		if (!$this->db->table_exists('tecn')) {
			$mSQL="CREATE TABLE `tecn` (
			  `codigo` varchar(5) NOT NULL DEFAULT '',
			  `clave` varchar(5) DEFAULT NULL,
			  `nombre` varchar(30) DEFAULT NULL,
			  `direc1` varchar(35) DEFAULT NULL,
			  `direc2` varchar(35) DEFAULT NULL,
			  `telefono` varchar(13) DEFAULT NULL,
			  `comive` decimal(5,2) DEFAULT NULL,
			  `comicob` decimal(5,2) DEFAULT NULL,
			  `recargo` decimal(5,2) DEFAULT NULL,
			  `tipo` char(1) DEFAULT NULL,
			  `almacen` varchar(4) DEFAULT NULL,
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `codigo` (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}
	}
}
?>
