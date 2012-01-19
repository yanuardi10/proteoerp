<?php
class estajefe extends Controller {
	var $titp='Jefes de estaci&oacute;nes';
	var $tits='Jefes de estaci&oacute;nes';
	var $url ='inventario/estajefe/';

	function estajefe(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('325',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'estajefe');

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->rule      ='max_length[30]';
		$filter->nombre->size      =32;
		$filter->nombre->maxlength =30;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#codigo#></raencode>','<#codigo#>');

		$grid = new DataGrid('');
		$grid->use_function('character_limiter');
		$grid->order_by('codigo');
		$grid->per_page = 40;

		$grid->column_orderby('C&oacute;digo',$uri,'codigo','align="left"');
		$grid->column_orderby('Nombre','nombre','nombre','align="left"');
		$grid->column_orderby('Direcci&oacute;n','<character_limiter><#direc1#>|15</character_limiter>','direc1','align="left"');
		$grid->column_orderby('Tel&eacute;fono','telefono','telefono','align="left"');
		$grid->column_orderby('Correo','correo','correo','align="left"');

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

		$edit = new DataEdit($this->tits, 'estajefe');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->codigo = new inputField('C&oacute;digo','codigo');
		$edit->codigo->rule='max_length[5]|required';
		$edit->codigo->mode='autohide';
		$edit->codigo->size =7;
		$edit->codigo->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[30]|required';
		$edit->nombre->size =32;
		$edit->nombre->maxlength =30;

		$edit->direc1 = new inputField('Direcci&oacute;n','direc1');
		$edit->direc1->rule='max_length[35]';
		$edit->direc1->size =37;
		$edit->direc1->maxlength =35;

		$edit->telefono = new inputField('Tel&eacute;fono','telefono');
		$edit->telefono->rule='max_length[13]';
		$edit->telefono->size =15;
		$edit->telefono->maxlength =13;

		$edit->correo = new inputField('Correo','correo');
		$edit->correo->rule='max_length[250]';
		$edit->correo->maxlength =250;

		$edit->buttons('modify', 'save', 'undo','add','delete', 'back');
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
		if (!$this->db->table_exists('estajefe')) {
			$mSQL="CREATE TABLE `estajefe` (
			  `codigo` varchar(5) NOT NULL DEFAULT '',
			  `nombre` varchar(30) DEFAULT NULL,
			  `direc1` varchar(35) DEFAULT NULL,
			  `direc2` varchar(35) DEFAULT NULL,
			  `telefono` varchar(13) DEFAULT NULL,
			  `correo` varchar(250) DEFAULT NULL,
			  PRIMARY KEY (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}
	}

}
