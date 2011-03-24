<?php //require_once(BASEPATH.'application/controllers/validaciones.php');
class conv extends Controller {

	function conv(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index() {
		redirect('inventario/conv/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Conversiones', 'conv');

		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->size=15;
		$filter->fecha->maxlength=15;
		$filter->fecha->rule='trim';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=15;

		$filter->almacen = new inputField('Almac&eacute;n', 'almacen');
		$filter->almacen->size=15;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('inventario/conv/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid('Lista de Conversiones');
		$grid->use_function('dbdate_to_human');
		$grid->order_by('numero','desc');
		$grid->per_page = 10;

		$grid->column('N&uacute;mero', $uri);
		$grid->column('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>');
		$grid->column('Almac&eacute;n','almacen');

		$grid->add('inventario/conv/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Conversiones de inventario');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'descrip_<#i#>',
			),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Articulo',
			'where'   => '`activo` = "S"',
			'script'  => array('post_modbus_sinv(<#i#>)')
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$do = new DataObject('conv');
		$do->rel_one_to_many('itconv', 'itconv', 'numero');
		$do->rel_pointer('itconv','sinv','itconv.codigo=sinv.codigo','sinv.descrip AS sinvdescrip');

		$edit = new DataDetails('Conversiones', $do);
		$edit->back_url = site_url('inventario/conv/filteredgrid');
		$edit->set_rel_title('itconv','Producto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->observa1 = new inputField('Observaciones', 'observ1');
		$edit->observa1->size      = 35;
		$edit->observa1->maxlength = 38;

		$edit->observa2 = new inputField('Observaciones', 'observ2');
		$edit->observa2->size      = 35;
		$edit->observa2->maxlength = 38;

		$edit->almacen = new  dropdownField ('Almacen', 'almacen');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub ORDER BY ubica');
		$edit->almacen->style='width:200px;';
		$edit->almacen->size = 5;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itconv';
		$edit->codigo->rule     = 'required';
		$edit->codigo->append($btn);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->readonly  = true;
		$edit->descrip->rel_id='itconv';

		$edit->entrada = new inputField('Entrada <#o#>', 'entrada_<#i#>');
		$edit->entrada->db_name  = 'entrada';
		$edit->entrada->css_class= 'inputnum';
		$edit->entrada->rel_id   = 'itconv';
		$edit->entrada->maxlength= 10;
		$edit->entrada->size     = 6;
		$edit->entrada->rule     = 'required|positive';
		$edit->entrada->autocomplete=false;

		$edit->salida = new inputField('Salida <#o#>', 'salida_<#i#>');
		$edit->salida->db_name  = 'salida';
		$edit->salida->css_class= 'inputnum';
		$edit->salida->rel_id   = 'itconv';
		$edit->salida->maxlength= 10;
		$edit->salida->size     = 6;
		$edit->salida->rule     = 'required|positive';
		$edit->salida->autocomplete=false;

		$edit->costo = new inputField('Costo <#o#>', 'costo_<#i#>');
		$edit->costo->db_name   = 'costo';
		$edit->costo->css_class = 'inputnum';
		$edit->costo->rel_id    = 'itconv';
		$edit->costo->size      = 10;
		$edit->costo->rule      = 'required|positive';
		//**************************
		//fin de campos para detalle
		//**************************

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('save', 'undo', 'delete', 'back','add_rel');
		$edit->build();
		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_conv', $conten,true);
		$data['title']   = heading('Conversiones de inventario');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('nconv');
		$do->set('numero',$numero);
		$transac=$this->datasis->fprox_numero('ntransa');
		$do->set('transac',$transac);
		$do->set('estampa',date('Y-m-d'));
		$do->set('hora',date("H:i:s"));
		$cana=$do->count_rel('itconv');
		for($i=0;$i<$cana;$i++){
			$do->set_rel('itconv','estampa' ,date('Y-m-d'),$i);
			$do->set_rel('itconv','hora' ,date("H:i:s"),$i);
			$do->set_rel('itconv','transac'   ,$transac  ,$i);
			$do->set_rel('itconv','usuario',$do->get('usuario'),$i);

		}
		return true;
	}

	function _pre_update($do){
		return false;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('conv',"Conversion $codigo CREADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('conv',"Conversion $codigo ELIMINADO");
	}
}