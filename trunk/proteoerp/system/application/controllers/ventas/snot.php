<?php //require_once(BASEPATH.'application/controllers/validaciones.php');
class snot extends Controller {

	function snot(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->back_dataedit='ventas/snot/filteredgrid';
	}

	function index() {
		redirect('ventas/snot/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Notas de entrega','snot');
		
		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->size=15;
		$filter->fecha->maxlength=15;
		$filter->fecha->rule='trim';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=15;

		$filter->factura = new inputField('Factura', 'factura');
		$filter->factura->size=15;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('ventas/snot/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid('Lista de Notas de Entrega');
		$grid->use_function('dbdate_to_human');
		$grid->order_by('numero','desc');
		$grid->per_page = 10;

		$grid->column_orderby('N&uacute;mero', $uri,'numero');
		$grid->column_orderby('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>','fecha');
		$grid->column_orderby('Factura','factura','factura');
		$grid->column_orderby('Fecha F.','<dbdate_to_human><#fechafa#></dbdate_to_human>','fechafa');
		$grid->column_orderby('Nombre', 'nombre','nombre');

		//$grid->add('ventas/ssnot/dataedit/create');
		$grid->build();

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		$data['title']   = heading('Notas de Entrega');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		
		$modbusSinv=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'descrip_<#i#>',
			),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Articulo',
			'where'   => '`activo` = "S" AND `tipo` = "Articulo"'
		);
		$boton=$this->datasis->p_modbus($modbusSinv,'<#i#>');

		$modbus=array(
			'tabla'   =>'sfac',
			'columnas'=>array(
				'numero'  =>'N&uacute;mero',
				'fecha'   =>'Fecha',
				'cod_cli' =>'Cliente',
				'rifci'   =>'Rif',
				'nombre'  =>'Nombre',
				'tipo_doc' =>'Tipo',
				),
			'filtro'  =>array('numero'=>'N&uacute;mero','cod_cli'=>'Cliente','rifci'=>'Rif','nombre'=>'Nombre'),
			'where'=>'tipo_doc = "F" and mid(numero,1,1) <> "_"',
			'retornar'=>array(
				'numero' =>'factura',
				'fecha'  =>'fechafa',
				'cod_cli'=>'cod_cli',
				'nombre' =>'nombre'
			),
			'titulo'  => 'Buscar Factura',
		);
		$btn=$this->datasis->modbus($modbus);

		$do = new DataObject('snot');
		$do->rel_one_to_many('itsnot', 'itsnot', 'numero');
		$do->rel_pointer('itsnot','sinv','itsnot.codigo=sinv.codigo','sinv.descrip AS sinvdescrip','sinv.ultimo AS sinvultimo');

		$edit = new DataDetails('Nota de Entrega', $do);
		$edit->back_url = $this->back_dataedit;
		$edit->set_rel_title('itsnot','Producto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');
		
		$edit->fechafa = new DateonlyField('Fecha Factura', 'fechafa','d/m/Y');
		$edit->fechafa->insertValue = date('Y-m-d');
		$edit->fechafa->rule = 'required';
		$edit->fechafa->mode = 'autohide';
		$edit->fechafa->size = 10;

		$edit->factura = new inputField('Factura', 'factura');
		$edit->factura->size = 10;
		$edit->factura->mode='autohide';
		$edit->factura->maxlength=8;
		
		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->append($btn);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';
		
		$edit->observa1 = new inputField('Observaciones', 'observ1');
		$edit->observa1->size      = 40;
		$edit->observa1->maxlength = 80;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itsnot';
		$edit->codigo->rule     = 'required|callback_chrepetidos';
		$edit->codigo->append($boton);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->readonly  = true;
		$edit->descrip->rel_id='itsnot';

		$edit->cant = new inputField('Cantidad <#o#>', 'cant_<#i#>');
		$edit->cant->db_name  = 'cant';
		$edit->cant->css_class= 'inputnum';
		$edit->cant->rel_id   = 'itsnot';
		$edit->cant->maxlength= 10;
		$edit->cant->size     = 6;
		$edit->cant->rule     = 'required|positive';
		$edit->cant->autocomplete=false;
		
		$edit->saldo = new inputField('Saldo <#o#>', 'saldo_<#i#>');
		$edit->saldo->db_name  = 'saldo';
		$edit->saldo->css_class= 'inputnum';
		$edit->saldo->rel_id   = 'itsnot';
		$edit->saldo->maxlength= 10;
		$edit->saldo->size     = 6;
		$edit->saldo->rule     = 'required|positive';
		$edit->saldo->autocomplete=false;
		
		$edit->entrega = new inputField('Entrega <#o#>', 'entrega_<#i#>');
		$edit->entrega->db_name  = 'entrega';
		$edit->entrega->css_class= 'inputnum';
		$edit->entrega->rel_id   = 'itsnot';
		$edit->entrega->maxlength= 10;
		$edit->entrega->size     = 6;
		$edit->entrega->rule     = 'required|positive';
		$edit->entrega->autocomplete=false;
		
		$edit->itfactura = new inputField('Factura <#o#>', 'itfactura_<#i#>');
		$edit->itfactura->size     = 12;
		$edit->itfactura->db_name  = 'factura';
		$edit->itfactura->readonly = true;
		$edit->itfactura->rel_id   = 'itsnot';
		$edit->itfactura->append($boton);
		//**************************
		//fin de campos para detalle
		//**************************
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_snot', $conten,true);
		$data['title']   = heading('Notas De Entrega');
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
		//trafrac ittrafrac
		$codigo=$do->get('numero');
		logusu('snot',"Nota Entrega $codigo CREADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('snot',"Nota Entrga $codigo ELIMINADO");
	}

}