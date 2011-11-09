<?php
class edres extends Controller {
	var $titp='Reservaciones';
	var $tits='Reservaciones';
	var $url ='construccion/edres/';

	function edres(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('A05',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'edres');

		$filter->numero = new inputField('N&uacute;mero','numero');
		$filter->numero->rule      ='max_length[8]';
		$filter->numero->size      =10;
		$filter->numero->maxlength =8;

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->cliente = new inputField('Cliente','cliente');
		$filter->cliente->rule      ='max_length[5]';
		$filter->cliente->size      =7;
		$filter->cliente->maxlength =5;

		$filter->edificacion = new dropdownField('Edificaci&oacute;n','edificacion');
		$filter->edificacion->option('','Seleccionar');
		$filter->edificacion->options('SELECT id,TRIM(nombre) AS nombre FROM `edif` ORDER BY nombre');

		$filter->inmueble = new dropdownField('Inmueble','inmueble');
		$filter->inmueble->option('','Seleccionar');
		$filter->inmueble->options('SELECT id,TRIM(descripcion) AS nombre FROM `edinmue` ORDER BY descripcion');

		$filter->reserva = new inputField('Reservaci&oacute;n','reserva');
		$filter->reserva->rule      ='max_length[17]|numeric';
		$filter->reserva->css_class ='inputnum';
		$filter->reserva->size      =19;
		$filter->reserva->maxlength =17;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Id',$uri,'id','align="left"');
		$grid->column_orderby('N&uacute;mero','numero','numero','align="left"');
		$grid->column_orderby('Fecha'   ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align="center"');
		$grid->column_orderby('Cliente' ,'cliente','cliente','align="left"');
		$grid->column_orderby('Edificaci&oacute;n','<nformat><#edificacion#></nformat>','edificacion','align="right"');
		$grid->column_orderby('Inmueble','<nformat><#inmueble#></nformat>','inmueble','align="right"');
		$grid->column_orderby('Reservaci&oacute;n','<nformat><#reserva#></nformat>','reserva','align="right"');

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

		$edit = new DataEdit($this->tits, 'edres');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->cliente = new inputField('Cliente','cliente');
		$edit->cliente->rule='max_length[5]|existescli';
		$edit->cliente->size =7;
		$edit->cliente->maxlength =5;
		$edit->cliente->append($boton);

		$edit->edificacion = new dropdownField('Edificaci&oacute;n','edificacion');
		$edit->edificacion->option('','Seleccionar');
		$edit->edificacion->options('SELECT id,TRIM(nombre) AS nombre FROM `edif` ORDER BY nombre');
		$edit->edificacion->rule='max_length[11]';

		$edit->inmueble = new dropdownField('Inmueble','inmueble');
		$edit->inmueble->option('','Seleccionar');
		$edit->inmueble->options('SELECT id,TRIM(descripcion) AS nombre FROM `edinmue` ORDER BY descripcion');
		$edit->inmueble->rule='max_length[11]';

		$edit->reserva = new inputField('Reservaci&oacute;n','reserva');
		$edit->reserva->rule='max_length[17]|numeric';
		$edit->reserva->css_class='inputnum';
		$edit->reserva->size =19;
		$edit->reserva->maxlength =17;

		$mSQL="SELECT cod_banc, nomb_banc FROM tban WHERE cod_banc<>'CAJ'";
		$query = $this->db->query($mSQL);
		$bancos=array();
		foreach ($query->result() as $row){
			$bancos[$row->cod_banc]=$row->nomb_banc;
		}

		for($i=1;$i<4;$i++){
			$group='Formas de pago '.$i;

			$obj1='formap'.$i;
			$edit->$obj1 =  new dropdownField('Pago '.$i, $obj1);
			$edit->$obj1->option('','Seleccionar');
			$edit->$obj1->option('CH','Cheque'       );
			$edit->$obj1->option('DE','Deposito'     );
			$edit->$obj1->option('NC','Transferencia');
			$edit->$obj1->group=$group;
			$edit->$obj1->style='width:140px';
			$edit->$obj1->rule ='max_length[2]';

			$obj2='banco'.$i;
			$edit->$obj2 =  new dropdownField('Banco '.$i, $obj2);
			$edit->$obj2->option('','Seleccionar banco');
			$edit->$obj2->options($bancos);
			$edit->$obj2->group=$group;
			$edit->$obj2->rule='max_length[2]';
			$edit->$obj2->in=$obj1;

			$obj3='nummp'.$i;
			$edit->$obj3 = new inputField('N&uacute;mero referencia',$obj3);
			$edit->$obj3->rule='max_length[3]';
			$edit->$obj3->size =5;
			$edit->$obj3->maxlength =3;
			$edit->$obj3->group=$group;

			$obj4='monto'.$i;
			$edit->$obj4 = new inputField('Monto',$obj4);
			$edit->$obj4->rule='max_length[17]|numeric';
			$edit->$obj4->css_class='inputnum';
			$edit->$obj4->size =19;
			$edit->$obj4->maxlength =17;
			$edit->$obj4->group=$group;
		}

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
		if (!$this->db->table_exists('edres')) {
			$mSQL="CREATE TABLE `edres` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `numero` char(8) DEFAULT NULL,
			  `fecha` date DEFAULT NULL,
			  `cliente` char(5) DEFAULT NULL,
			  `edificacion` int(11) DEFAULT '0',
			  `inmueble` int(11) DEFAULT '0',
			  `reserva` decimal(17,2) DEFAULT '0.00',
			  `formap1` char(2) DEFAULT '0',
			  `banco1` char(3) DEFAULT '0',
			  `nummp1` char(3) DEFAULT '0',
			  `monto1` decimal(17,2) DEFAULT '0.00',
			  `formap2` char(2) DEFAULT '0',
			  `banco2` char(3) DEFAULT '0',
			  `nummp2` varchar(20) DEFAULT '0',
			  `monto2` decimal(17,2) DEFAULT '0.00',
			  `formap3` char(2) DEFAULT '0',
			  `banco3` char(3) DEFAULT '0',
			  `nummp3` varchar(20) DEFAULT '0',
			  `monto3` decimal(17,2) DEFAULT '0.00',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Reserva de Inmuebles'";
			$this->db->simple_query($mSQL);
		}
	}
}
