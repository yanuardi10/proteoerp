<?php
class edinmue extends Controller {
	var $titp='Inmuebles';
	var $tits='Inmuebles';
	var $url ='construccion/edinmue/';

	function edinmue(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('A03',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'edinmue');

		$filter->codigo = new inputField('C&oacute;digo','codigo');
		$filter->codigo->rule      ='max_length[15]';
		$filter->codigo->size      =17;
		$filter->codigo->maxlength =15;

		$filter->descripcion = new inputField('Descripci&oacute;n','descripcion');
		$filter->descripcion->rule      ='max_length[100]';
		$filter->descripcion->maxlength =100;

		$filter->edificacion = new dropdownField('Edificaci&oacute;n','edificacion');
		$filter->edificacion->option('','Seleccionar');
		$filter->edificacion->options('SELECT id,TRIM(nombre) AS nombre FROM edif ORDER BY nombre');

		$filter->uso = new dropdownField('Uso','uso');
		$filter->uso->option('','Todos');
		$filter->uso->options('SELECT id,uso FROM `eduso` ORDER BY uso');

		$filter->ubicacion = new inputField('Ubicaci&oacute;n','ubicacion');
		$filter->ubicacion->rule      ='max_length[11]';
		$filter->ubicacion->size      =13;
		$filter->ubicacion->maxlength =11;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Id',$uri,'id','align="left"');
		$grid->column_orderby('C&oacute;digo','codigo','codigo','align="left"');
		$grid->column_orderby('Descripci&oacute;n','descripcion','descripcion','align="left"');
		$grid->column_orderby('Edificaci&oacute;n','<nformat><#edificacion#></nformat>','edificacion','align="right"');
		$grid->column_orderby('Uso','<nformat><#uso#></nformat>','uso','align="right"');
		$grid->column_orderby('Uso alterno','<nformat><#usoalter#></nformat>','usoalter','align="right"');
		$grid->column_orderby('Ubicaci&oacute;n','<nformat><#ubicacion#></nformat>','ubicacion','align="right"');
		$grid->column_orderby('&Aacute;rea','<nformat><#area#></nformat>','area','align="right"');
		$grid->column_orderby('Estacionamiento','<nformat><#estaciona#></nformat>','estaciona','align="right"');

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

		$edit = new DataEdit($this->tits, 'edinmue');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->codigo = new inputField('C&oacute;digo','codigo');
		$edit->codigo->rule='max_length[15]|unique';
		$edit->codigo->size =17;
		$edit->codigo->maxlength =15;

		$edit->descripcion = new inputField('Descripci&oacute;n','descripcion');
		$edit->descripcion->rule='max_length[100]';
		$edit->descripcion->maxlength =100;

		$edit->edificacion = new dropdownField('Edificaci&oacute;n','edificacion');
		$edit->edificacion->option('','Seleccionar');
		$edit->edificacion->options('SELECT id,TRIM(nombre) AS nombre FROM edif ORDER BY nombre');
		$edit->edificacion->rule='max_length[11]';

		$edit->uso = new dropdownField('Uso','uso');
		$edit->uso->option('','Seleccionar');
		$edit->uso->options('SELECT id,uso FROM `eduso` ORDER BY uso');
		$edit->uso->rule='max_length[11]|required';

		$edit->usoalter = new dropdownField('Uso Alternativo','usoalter');
		$edit->usoalter->option('','Seleccionar');
		$edit->usoalter->options('SELECT id,uso FROM `eduso` ORDER BY uso');
		$edit->usoalter->rule='max_length[11]|required';

		$edit->ubicacion = new dropdownField('Ubicaci&oacute;n','ubicacion');
		$edit->ubicacion->option('','Seleccionar');
		$edit->ubicacion->options('SELECT id,descripcion FROM `edifubica` ORDER BY descripcion');
		$edit->ubicacion->rule='max_length[11]|integer';

		$edit->caracteristicas = new textareaField('Caracter&iacute;sticas','caracteristicas');
		//$edit->caracteristicas->rule='max_length[8]';
		$edit->caracteristicas->cols = 70;
		$edit->caracteristicas->rows = 4;

		$edit->area = new inputField('&Aacute;rea','area');
		$edit->area->rule='max_length[15]|numeric';
		$edit->area->css_class='inputnum';
		$edit->area->size =10;
		//$edit->area->maxlength =15;

		$edit->estaciona = new inputField('Estacionamiento','estaciona');
		$edit->estaciona->rule='max_length[10]|integer';
		$edit->estaciona->size =10;
		$edit->estaciona->css_class='inputonlynum';
		$edit->estaciona->maxlength =10;

		$edit->deposito = new inputField('Deposito','deposito');
		$edit->deposito->rule='max_length[11]|integer';
		$edit->deposito->size =10;
		$edit->deposito->maxlength =11;
		$edit->deposito->css_class='inputonlynum';

		$edit->preciomt2 = new inputField('Precio x mt2','preciomt2');
		$edit->preciomt2->rule='max_length[15]|numeric';
		$edit->preciomt2->css_class='inputnum';
		$edit->preciomt2->size =10;
		$edit->preciomt2->maxlength =15;

		$script ='<script type="text/javascript" >
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});
		</script>';

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();
		$data['content'] = $edit->output;
		$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').$script;
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
		if (!$this->db->table_exists('edinmue')) {
			$mSQL="CREATE TABLE `edinmue` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `codigo` CHAR(15) NULL DEFAULT NULL,
			  `descripcion` CHAR(100) NULL DEFAULT NULL,
			  `edificacion` INT(11) NULL DEFAULT NULL,
			  `uso` INT(11) NULL DEFAULT NULL,
			  `usoalter` INT(11) NULL DEFAULT NULL,
			  `ubicacion` INT(11) NULL DEFAULT NULL,
			  `caracteristicas` TEXT NULL,
			  `area` DECIMAL(15,2) NULL DEFAULT NULL,
			  `estaciona` INT(10) NULL DEFAULT NULL,
			  `deposito` INT(11) NULL DEFAULT NULL,
			  `preciomt2` DECIMAL(15,2) NULL DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `codigo` (`codigo`)
			)
			COMMENT='Inmuebles'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}
	}

}