<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class sfacc extends validaciones {

	function sfacc(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(104,1);
	}

	function index() {
		redirect('ventas/sfacc/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datagrid','datafilter');

		$atts = array(
			'width'      => '800',
			'height'     => '600',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '0',
			'screeny'    => '0'
		);

		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');
		$boton=$this->datasis->modbus($scli);

		$filter = new DataFilter('Filtro de Facturas');
		$filter->db->select(array('fecha','numero','cod_cli','nombre','totals','totalg','iva','tipo_doc'));
		$filter->db->from('sfac');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechad->insertValue = date('Y-m-d');
		$filter->fechah->insertValue = date('Y-m-d');
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 30;

		$filter->cliente = new inputField('Cliente', 'cod_cli');
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('ventas/sfacc/dataedit/show/<#tipo_doc#>/<#numero#>','<#numero#>');
		
		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 15;

		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha'    ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Nombre'   ,'nombre','nombre');
		$grid->column_orderby('Sub.Total','<nformat><#totals#></nformat>','totals','align=\'right\'');
		$grid->column_orderby('IVA'      ,'<nformat><#iva#></nformat>'   ,'iva','align=\'right\'');
		$grid->column_orderby('Total'    ,'<nformat><#totalg#></nformat>','totalg','align=\'right\'');

		//$grid->add('ventas/sfacc/dataedit/create');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = heading('Facturas');
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigoa_<#i#>',
				'descrip'=>'desca_<#i#>',
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		
		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre', 
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n',
			'tipo'=>'Tipo'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','rifci'=>'rifci',
						  'dire11'=>'direc','tipo'=>'sclitipo'),
		'titulo'  =>'Buscar Cliente',
		);
		$boton =$this->datasis->modbus($mSCLId);

		$do = new DataObject('sfac');
		$do->rel_one_to_many('sitems', 'sitems', array('numero'=>'numa','tipo_doc'=>'tipoa'));
		$do->rel_one_to_many('sfpa', 'sfpa', array('numero','tipo_doc'));

		$edit = new DataDetails('Facturas', $do);
		$edit->back_url = site_url('ventas/sfacc/filteredgrid');
		$edit->set_rel_title('sitems','Producto <#o#>');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;
		
		$edit->tipo_doc = new  dropdownField ('Tipo D.', 'tipo_doc');
		$edit->tipo_doc->option('F','Factura');
		$edit->tipo_doc->option('D','Devoluci&oacute;n');
		$edit->tipo_doc->style='width:200px;';
		$edit->tipo_doc->size = 5;

		$edit->vd = new  dropdownField ('Vendedor', 'vd');
		$edit->vd->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vd->style='width:200px;';
		$edit->vd->size = 5;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->rifci   = new inputField('RIF/CI','rifci');
		$edit->rifci->autocomplete=false;
		$edit->rifci->size = 15;

		$edit->direc = new inputField('Direcci&oacute;n','direc');
		$edit->direc->size = 40;

		//**************************
		//  Campos para el detalle 1 sitems
		//**************************
		$edit->codigoa = new inputField('C&oacute;digo <#o#>', 'codigoa_<#i#>');
		$edit->codigoa->size     = 12;
		$edit->codigoa->db_name  = 'codigoa';
		$edit->codigoa->readonly = true;
		$edit->codigoa->rel_id   = 'sitems';
		$edit->codigoa->rule     = 'required';

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=36;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=50;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='sitems';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'sitems';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive';
		$edit->cana->autocomplete=false;

		$edit->preca = new inputField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'sitems';
		$edit->preca->size      = 10;
		$edit->preca->rule      = 'required|positive';
		$edit->preca->readonly  = true;

		$edit->tota = new inputField('Importe <#o#>', 'tota_<#i#>');
		$edit->tota->db_name='tota';
		$edit->tota->size=10;
		$edit->tota->css_class='inputnum';
		$edit->tota->rel_id   ='sitems';

		//**************************
		//fin de campos para detalle,inicio detalle2 sfpa
		//**************************
		$edit->tipo = new inputField('Tipo <#o#>', 'tipo_<#i#>');
		$edit->tipo->size     = 12;
		$edit->tipo->db_name  = 'tipo';
		$edit->tipo->readonly = true;
		$edit->tipo->rel_id   = 'sfpa';
		$edit->tipo->rule     = 'required';
		
		$edit->monto = new inputField('Monto <#o#>', 'monto_<#i#>');
		$edit->monto->db_name   = 'monto';
		$edit->monto->css_class = 'inputnum';
		$edit->monto->rel_id    = 'sfpa';
		$edit->monto->size      = 10;
		$edit->monto->rule      = 'required|positive';
		$edit->monto->readonly  = true;
		
		$edit->banco = new inputField('Banco <#o#>', 'banco_<#i#>');
		$edit->banco->size=36;
		$edit->banco->db_name='banco';
		$edit->banco->maxlength=50;
		$edit->banco->readonly  = true;
		$edit->banco->rel_id='sfpa';
		
		//**************************
		//Fin detalle 2
		//**************************

		$edit->ivat = new inputField('Impuesto', 'iva');
		$edit->ivat->css_class ='inputnum';
		$edit->ivat->readonly  =true;
		$edit->ivat->size      = 10;

		$edit->totals = new inputField('Sub-Total', 'totals');
		$edit->totals->css_class ='inputnum';
		$edit->totals->readonly  =true;
		$edit->totals->size      = 10;

		$edit->totalg = new inputField('Monto Total', 'totalg');
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->readonly  =true;
		$edit->totalg->size      = 10;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_sfac', $conten,true);
		$data['title']   = heading('Facturas');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		return false;
	}

	function _pre_update($do){
		return false;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('sfac',"Factura $codigo CREADO");
	}

	function chpreca($preca,$ind){
		$codigo  = $this->input->post('codigo_'.$ind);
		$precio4 = $this->datasis->dameval('SELECT base4 FROM sinv WHERE codigo='.$this->db->escape($codigo));
		if($precio4<0) $precio4=0;

		if($preca<$precio4){
			$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' debe contener un precio de al menos '.nformat($precio4));
			return false;
		}else{
			return true;
		}
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('sfac',"Factura $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('sfac',"Factura $codigo ELIMINADO");
	}
}