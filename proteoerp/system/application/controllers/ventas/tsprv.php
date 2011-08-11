<?php
class tsprv extends Controller {
	var $titp='Empresas de Cesta Tickets';
	var $tits='Empresa';
	var $url ='ventas/tsprv/';
	
	function tsprv(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(216,1);
	}
	function index(){
		redirect($this->url."filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'tsprv');

		$filter->codigo = new inputField('codigo','codigo');
		$filter->codigo->rule      ='max_length[5]';
		$filter->codigo->size      =7;
		$filter->codigo->maxlength =5;

		$filter->nombre = new textareaField('nombre','nombre');
		$filter->nombre->rule      ='max_length[8]';
		$filter->nombre->cols = 70;
		$filter->nombre->rows = 4;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#codigo#></raencode>','<#codigo#>');

		$grid = new DataGrid('');
		$grid->order_by('codigo');
		$grid->per_page = 40;

		$grid->column_orderby('C&oacute;digo',"$uri"  ,'codigo','align="left"');
		$grid->column_orderby('Nombre'       ,"nombre",'nombre','align="left"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = $this->titp;
		$this->load->view('view_ventanas', $data);

	}
	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'tsprv');

		$edit->back_url = site_url($this->url."filteredgrid");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->codigo = new inputField('codigo','codigo');
		$edit->codigo->rule='max_length[5]|trim|unique';
		$edit->codigo->size =7;
		$edit->codigo->maxlength =5;

		$edit->nombre = new textareaField('nombre','nombre');
		$edit->nombre->cols = 70;
		$edit->nombre->rows = 4;

		$edit->buttons('add','modify', 'save', 'undo', 'delete', 'back');
		$edit->build();
		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = $this->tits;
		$this->load->view('view_ventanas', $data);

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
		$mSQL="CREATE TABLE `tsprv` (
		`codigo` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		`nombre` text COLLATE utf8_unicode_ci,
		PRIMARY KEY (`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$this->db->simple_query($mSQL);
	}

}
?>
