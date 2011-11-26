<?php
class edcont extends Controller {
	var $titp='Contratos';
	var $tits='Contratos';
	var $url ='construccion/edcont/';

	function edcont(){
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

		$filter = new DataFilter($this->titp, 'edcont');

		$filter->id_edres = new inputField('Reservaci&oacute;n','id_edres');
		$filter->id_edres->rule      ='max_length[11]';
		$filter->id_edres->size      =13;
		$filter->id_edres->maxlength =11;

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

		$filter->edificacion = new inputField('Edificaci&oacute;n','edificacion');
		$filter->edificacion->rule      ='max_length[11]';
		$filter->edificacion->size      =13;
		$filter->edificacion->maxlength =11;

		$filter->inmueble = new inputField('Inmueble','inmueble');
		$filter->inmueble->rule      ='max_length[11]';
		$filter->inmueble->size      =13;
		$filter->inmueble->maxlength =11;

		$filter->notas = new textareaField('Notas','notas');
		$filter->notas->rule      ='max_length[8]';
		$filter->notas->cols = 70;
		$filter->notas->rows = 4;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Id',$uri,'id','align="left"');
		$grid->column_orderby('Id_edres','id_edres','id_edres','align="right"');
		$grid->column_orderby('Numero','numero','numero','align="left"');
		$grid->column_orderby('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align="center"');
		$grid->column_orderby('Cliente','cliente','cliente','align="left"');
		$grid->column_orderby('Inicial','<nformat><#inicial#></nformat>','inicial','align="right"');
		$grid->column_orderby('Financiable','<nformat><#financiable#></nformat>','financiable','align="right"');
		$grid->column_orderby('Monto','<nformat><#monto#></nformat>','monto','align="right"');
		$grid->column_orderby('Notas','notas','notas','align="left"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$do = new DataObject('edcont');
		$do->pointer('scli' ,'scli.cliente=edcont.cliente','scli.tipo AS sclitipo, scli.nombre AS nombre, dire11 AS direc, scli.rifci AS rifci','left');
		$do->rel_one_to_many('itedcont', 'itedcont', array('id'=>'id_edcont'));

		$edit = new DataDetails($this->tits, $do);

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->id_edres = new inputField('Id_edres','id_edres');
		$edit->id_edres->rule='max_length[11]|integer';
		$edit->id_edres->css_class='inputonlynum';
		$edit->id_edres->size =13;
		$edit->id_edres->maxlength =11;

		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->insertValue=date('Y-m-d');
		$edit->fecha->maxlength =8;

		$edit->cliente = new inputField('Cliente','cliente');
		$edit->cliente->rule='max_length[5]';
		$edit->cliente->size =7;
		$edit->cliente->maxlength =5;

		$edit->sclitipo = new hiddenField('', 'sclitipo');
		$edit->sclitipo->db_name     = 'sclitipo';
		$edit->sclitipo->pointer     = true;
		$edit->sclitipo->insertValue = 1;

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->readonly =true;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';
		$edit->nombre->type ='inputhidden';
		$edit->nombre->pointer=true;

		$edit->rifci   = new inputField('RIF/CI','rifci');
		$edit->rifci->autocomplete=false;
		$edit->rifci->readonly =true;
		$edit->rifci->size = 15;
		$edit->rifci->type ='inputhidden';
		$edit->rifci->pointer=true;

		$edit->direc = new inputField('Direcci&oacute;n','direc');
		$edit->direc->readonly =true;
		$edit->direc->size = 40;
		$edit->direc->type ='inputhidden';
		$edit->direc->pointer=true;

		$edit->edificacion = new dropdownField('Edificaci&oacute;n','edificacion');
		$edit->edificacion->option('','Seleccionar');
		$edit->edificacion->options('SELECT id,TRIM(nombre) AS nombre FROM `edif` ORDER BY nombre');
		$edit->edificacion->style='width:180px;';
		$edit->edificacion->rule='max_length[11]';

		$edit->inmueble = new dropdownField('Inmueble','inmueble');
		$edit->inmueble->option('','Seleccionar');
		$edif=$edit->getval('edificacion');
		if($edif!==false){
			$dbedif=$this->db->escape($edif);
			$edit->inmueble->option('','Seleccionar');
			$edit->inmueble->options("SELECT id,TRIM(descripcion) AS nombre FROM `edinmue` WHERE status='D' AND edificacion=$dbedif ORDER BY descripcion");
		}else{
			$edit->inmueble->option('','Seleccione una edificacion');
		}
		$edit->inmueble->style='width:180px;';
		$edit->inmueble->rule='max_length[11]';

		$edit->reserva = new inputField('Reserva','reserva');
		$edit->reserva->rule='max_length[17]|numeric';
		$edit->reserva->css_class='inputnum';
		$edit->reserva->size =10;
		$edit->reserva->maxlength =17;

		$edit->precioxmt2 = new inputField('Precioxmt2','precioxmt2');
		$edit->precioxmt2->rule='max_length[17]|numeric';
		$edit->precioxmt2->css_class='inputnum';
		$edit->precioxmt2->size =10;
		$edit->precioxmt2->maxlength =17;

		$edit->mt2 = new inputField('&Aacute;rea Mt2','mt2');
		$edit->mt2->rule='max_length[17]|numeric';
		$edit->mt2->css_class='inputnum';
		$edit->mt2->size =10;
		$edit->mt2->maxlength =17;

		$edit->inicial = new inputField('Inicial','inicial');
		$edit->inicial->rule='max_length[17]|numeric';
		$edit->inicial->css_class='inputnum';
		$edit->inicial->size =19;
		$edit->inicial->maxlength =17;

		$edit->financiable = new inputField('Monto financiable','financiable');
		$edit->financiable->rule='max_length[17]|numeric';
		$edit->financiable->css_class='inputnum';
		$edit->financiable->size =19;
		$edit->financiable->maxlength =17;

		$edit->firma = new inputField('Pago final (firma)','firma');
		$edit->firma->rule='max_length[17]|numeric';
		$edit->firma->css_class='inputnum';
		$edit->firma->size =19;
		$edit->firma->type ='inputhidden';
		$edit->firma->maxlength =17;

		$edit->monto = new inputField('Monto total','monto');
		$edit->monto->rule='max_length[17]|numeric';
		$edit->monto->css_class='inputnum';
		$edit->monto->size =19;
		$edit->monto->type ='inputhidden';
		$edit->monto->maxlength =17;

		$edit->notas = new textareaField('Notas','notas');
		$edit->notas->rule='max_length[8]';
		$edit->notas->cols = 70;
		$edit->notas->rows = 4;

		//*******************************
		// Inicio del detalle
		//*******************************
		$edit->it_vencimiento = new dateField('Vencimiento <#o#>','it_vencimiento_<#i#>');
		$edit->it_vencimiento->rule='chfecha';
		$edit->it_vencimiento->size =10;
		$edit->it_vencimiento->insertValue =date('Y-m-d');
		$edit->it_vencimiento->db_name ='vencimiento';
		$edit->it_vencimiento->rel_id  ='itedcont';
		$edit->it_vencimiento->maxlength =8;

		$edit->it_monto = new inputField('Monto <#o#>','it_monto_<#i#>');
		$edit->it_monto->rule='max_length[10]|numeric';
		$edit->it_monto->db_name   ='monto';
		$edit->it_monto->rel_id    ='itedcont';
		$edit->it_monto->on_keyup  = 'totagiro()';
		$edit->it_monto->css_class ='inputnum';
		$edit->it_monto->size      =12;
		$edit->it_monto->maxlength =10;
		//******************************
		// Fin del detalle
		//******************************

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back', 'add','add_rel');
		$edit->build();

		$script= '<script type="text/javascript" > 
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});
		</script>';

		/*$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$data['script'] .= $script;
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);*/
		
		$conten['form']     =& $edit;

		$data['content'] = $this->load->view('view_edcont', $conten,true);
		$data['title']   = heading($this->tits);
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');
		$data['head']   .= script('plugins/jquery.meiomask.js');
		$data['head']   .= phpscript('nformat.js');
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
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
		if (!$this->db->table_exists('edcont')) {
			$mSQL="CREATE TABLE `edcont` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `id_edres` int(11) DEFAULT '0',
			  `numero` char(8) DEFAULT NULL,
			  `fecha` date DEFAULT NULL,
			  `cliente` char(5) DEFAULT NULL,
			  `edificacion` int(11) DEFAULT '0',
			  `inmueble` int(11) DEFAULT '0',
			  `inicial` decimal(17,2) DEFAULT '0.00',
			  `financiable` decimal(17,2) DEFAULT '0.00',
			  `firma` decimal(17,2) DEFAULT '0.00',
			  `precioxmt2` decimal(17,2) DEFAULT '0.00',
			  `mt2` decimal(17,2) DEFAULT '0.00',
			  `monto` decimal(17,2) DEFAULT '0.00',
			  `notas` text,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COMMENT='Reserva de Inmuebles'";
			$this->db->simple_query($mSQL);
		}
	}

}
