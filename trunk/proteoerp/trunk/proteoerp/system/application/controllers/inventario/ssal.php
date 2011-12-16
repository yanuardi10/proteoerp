<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class ssal extends validaciones {

	function ssal(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(104,1);
		$this->back_dataedit='inventario/ssal/index';
	}

	function index() {
		redirect('inventario/ssal/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datagrid','datafilter');

		$caub=array(
			'tabla'   =>'caub',
			'columnas'=>array(
			'ubica' =>'C&oacute;digo',
			'ubides'=>'Nombre',
		),

		'filtro'  =>array('ubica'=>'C&oacute;digo','ubides'=>'Nombre'),
		'retornar'=>array('ubica'=>'almacen'),
		'titulo'  =>'Buscar Almacen');
		$boton=$this->datasis->modbus($caub);

		$filter = new DataFilter('Filtro de Salidad y Entradas');
		$filter->db->select(array('a.fecha','a.numero','a.tipo','a.almacen','a.cargo','a.motivo','a.descrip','b.ubica as ubica','b.ubides as ubides'));
		$filter->db->from('ssal as a');
		$filter->db->join('caub as b','a.almacen=b.ubica');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';

		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 10;

		$filter->tipo = new  dropdownField ('Tipo', 'tipo');
		$filter->tipo->option('','');
		$filter->tipo->option('S','Salida');
		$filter->tipo->option('E','Entrada');
		$filter->tipo->style='width:80px;';
		$filter->tipo->size = 5;

		$filter->alamcen = new inputField('Alamcen', 'almacen');
		$filter->alamcen->size = 5;
		$filter->alamcen->append($boton);

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('inventario/ssal/dataedit/show/<#numero#>','<#numero#>');

		function tipo($t){
			if($t=='S')return 'Salida';
			if($t=='E')return 'Entrada';
		}

		$grid = new DataGrid();
		$grid->order_by('numero','desc');
		$grid->per_page = 15;
		$grid->use_function('tipo');

		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha'    ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Tipo'   ,'<tipo><#tipo#></tipo>','tipo');
		$grid->column_orderby('Almacen','ubides','ubides');
		$grid->column_orderby('Descripci&ocaute;n'   ,'descrip','descrip');
		$grid->column_orderby('Motivo'   ,'motivo','motivo');

		$grid->add('inventario/ssal/dataedit/create');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Entrada y Salidas');
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'ultimo' =>'Costo',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'itdescrip_<#i#>',
				'ultimo' =>'costo_<#i#>'
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$mCAUB=array(
		'tabla'   =>'caub',
		'columnas'=>array(
				'ubica' =>'C&oacute;digo',
				'ubides'=>'Nombre', 
				),
		'filtro'  =>array('ubica'=>'C&oacute;digo','ubides'=>'ubides'),
		'retornar'=>array('ubica'=>'almacen','ubides'=>'caububides'),
		'titulo'  =>'Buscar Almacem',
		);
		$boton =$this->datasis->modbus($mCAUB);

		$do = new DataObject('ssal');
		$do->rel_one_to_many('itssal', 'itssal', 'numero');
		$do->pointer('caub' ,'caub.ubica=ssal.almacen','ubides AS caububides','left');
		$do->rel_pointer('itssal','sinv','itssal.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Entradas y Salidas', $do);
		$edit->back_url = $this->back_dataedit;
		$edit->set_rel_title('itssal','Producto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

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

		$edit->tipo = new  dropdownField ('Tipo', 'tipo');
		$edit->tipo->option('S','Salida');
		$edit->tipo->option('E','Entrada');
		$edit->tipo->style='width:80px;';
		$edit->tipo->size = 5;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->almacen = new inputField('Almacen','almacen');
		$edit->almacen->size = 6;
		$edit->almacen->maxlength=5;
		$edit->almacen->append($boton);
		
		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->size = 50;
		$edit->descrip->maxlength=50;
		
		$edit->motivo = new inputField('Motivo','motivo');
		$edit->motivo->size = 50;
		$edit->motivo->maxlength=50;

		$edit->cargo = new inputField('Cargo','cargo');
		$edit->cargo->size = 10;
		$edit->cargo->maxlength=10;
		//Para saber que precio se le va a dar al cliente
		$edit->caububides = new hiddenField('', 'caububides');
		$edit->caububides->db_name     = 'caububides';
		$edit->caububides->pointer     = true;
		$edit->caububides->insertValue = 1;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itssal';
		$edit->codigo->rule     = 'required';
		$edit->codigo->append($btn);

		$edit->itdescrip = new inputField('Descripci&oacute;n <#o#>', 'itdescrip_<#i#>');
		$edit->itdescrip->size=36;
		$edit->itdescrip->db_name='descrip';
		$edit->itdescrip->maxlength=50;
		$edit->itdescrip->readonly  = true;
		$edit->itdescrip->rel_id='itssal';

		$edit->cantidad = new inputField('Cantidad <#o#>', 'cantidad_<#i#>');
		$edit->cantidad->db_name  = 'cantidad';
		$edit->cantidad->css_class= 'inputnum';
		$edit->cantidad->rel_id   = 'itssal';
		$edit->cantidad->maxlength= 10;
		$edit->cantidad->size     = 6;
		$edit->cantidad->rule     = 'required|positive';
		$edit->cantidad->autocomplete=false;

		$edit->costo = new inputField('Costo <#o#>', 'costo_<#i#>');
		$edit->costo->db_name   = 'costo';
		$edit->costo->css_class = 'inputnum';
		$edit->costo->rel_id    = 'itssal';
		$edit->costo->size      = 10;
		$edit->costo->rule      = 'required|positive';
		$edit->costo->readonly  = true;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_ssal', $conten,true);
		$data['title']   = heading('Entradas y Salidas');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		$numero =$this->datasis->fprox_numero('nssal');
		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date('H:i:s');

		$cana=$do->count_rel('itssal');
		for($i=0;$i<$cana;$i++){
			$do->set_rel('itssal','estampa',$estampa  ,$i);
			$do->set_rel('itssal','usuario',$usuario  ,$i);
			$do->set_rel('itssal','hora'   ,$hora     ,$i);
			$do->set_rel('itssal','transac',$transac  ,$i);
		}
		$do->set('numero',$numero);
		$do->set('estampa',$estampa);
		$do->set('hora'   ,$hora);
		$do->set('numero' ,$numero);
		$do->set('transac',$transac);
		//print_r($do->get_all()); return false;

		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('ssal',"Entradas y Salidas $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('ssal',"Entradas y Salidas $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('ssal',"Entradas y Salidas $codigo ELIMINADO");
	}
}