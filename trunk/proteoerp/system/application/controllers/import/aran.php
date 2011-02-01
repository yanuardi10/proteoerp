<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class aran extends validaciones {

	function aran(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(305,1);
	}

	function index(){
		redirect('import/aran/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro','aran');

		$filter->linea = new inputField('Descripci&oacute;n','descrip');
		$filter->linea->size=20;

		$filter->unidad = new dropdownField('Unidad','unidad');
		$filter->unidad->style='width:180px;';
		$filter->unidad->option('','Seleccionar');
		$filter->unidad->options('SELECT unidades, unidades as valor FROM unidad ORDER BY unidades');

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('import/aran/dataedit/show/<raencode><#codigo#></raencode>','<#codigo#>');

		$grid = new DataGrid('Lista de Arancenles');
		$grid->order_by('codigo','asc');
		$grid->per_page = 20;

		$grid->column_orderby('C&oacute;digo'     ,$uri     ,'codigo' ,'align=\'center\'');
		$grid->column_orderby('Descripci&oacute;n','descrip','descrip','align=\'left\''  );
		$grid->column_orderby('Tarifa'            ,'<nformat><#tarifa#></nformat>' ,'tarifa' ,'align=\'right\'' );
		$grid->column_orderby('Unidad'            ,'unidad' ,'unidad' ,'align=\'right\'' );
		$grid->column_orderby('D&oacute;lar'      ,'<nformat><#dolar#></nformat>' ,'dolar' ,'align=\'right\'' );

		$grid->add('import/aran/dataedit/create','Agregar nuevo arancel');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Aranceles</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','dataedit');

		$edit = new DataEdit('Lista de aranceles','aran');
		$edit->back_url = site_url('import/aran/filteredgrid');

		//$edit->post_process('insert','_post_insert');
		//$edit->post_process('update','_post_update');
		$edit->pre_process( 'delete','_pre_del'    );
		$edit->post_process('delete','_post_delete');

		$edit->codigo =  new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->mode='autohide';
		$edit->codigo->rule ='trim|strtoupper|required';
		$edit->codigo->size = '20';
		$edit->codigo->maxlength=15;

		$edit->descrip =  new inputField('Descripci&oacute;n', 'descrip');
		$edit->descrip->rule ='trim|strtoupper|required';

		$edit->unidad = new dropdownField('Unidad','unidad');
		$edit->unidad->rule ='required';
		$edit->unidad->style='width:180px;';
		$edit->unidad->option('','Seleccionar');
		$edit->unidad->options('SELECT unidades, unidades as valor FROM unidad ORDER BY unidades');

		$edit->tarifa = new inputField('Tarifa', 'tarifa');
		$edit->tarifa->size = 10;
		$edit->tarifa->maxlength=10;
		$edit->tarifa->css_class='inputnum';
		$edit->tarifa->rule='callback_positivo|numeric|required';

		$edit->dolar = new inputField('D&oacute;lar', 'dolar');
		$edit->dolar->size = 10;
		$edit->dolar->maxlength=10;
		$edit->dolar->css_class='inputnum';
		$edit->dolar->rule='callback_positivo|numeric';

		$edit->buttons('modify','save','undo','delete','back');
		$edit->build();

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $edit->output;
		$data['title']   = '<h1>Aranceles</h1>';
		$data['head']    = script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		logusu('grup',"ARANCEL $codigo ELIMINADO");
	}

	function _pre_del($do) {
		$codigo=$this->db->escape($do->get('codigo'));
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM itordi WHERE codaran=$codigo");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='El arancel a borra contiene productos relacionados, por ello no puede ser eliminado.';
			return false;
		}
		return true;
	}

	function instalar(){
		$mSQL="CREATE TABLE `aran` (
		 `codigo` varchar(15) NOT NULL DEFAULT '',
		 `descrip` text,
		 `tarifa` decimal(8,2) DEFAULT '0.00',
		 `unidad` varchar(20) DEFAULT NULL,
		 PRIMARY KEY (`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
		$this->db->simple_query($mSQL);

		$mSQL="ALTER TABLE `aran`  ADD COLUMN `dolar` DECIMAL(8,2) NULL AFTER `unidad`";
		$this->db->simple_query($mSQL);
	}
}