<?php
class Ordi extends Controller {

	function Ordi(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(302,1);
	}

	function index(){
		redirect('import/ordi/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&acute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de Transferencias','ordi');

		$filter->numero = new inputField('N&uacute;mero','numero');
		$filter->numero->size=15;

		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->size=12;

		$filter->proveed = new inputField('Proveedor', 'proveed');
		$filter->proveed->size=12;
		$filter->proveed->append($boton);

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('inventario/straa/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid('Listas de ordi');
		$grid->order_by('numero','desc');
		$grid->per_page = 5;

		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha'        ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Proveedor'    ,'proveed','proveed');
		$grid->column_orderby('Monto Fact.'  ,'montofac','montofac');
		$grid->column_orderby('Monto total'  ,'montotot','montotot');

		$grid->add('import/ordi/dataedit/create');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Ordi</h1>';
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
			'filtro'  =>array('codigo' =>'C&oacutedigo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Producto en inventario');
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$sprv=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor');
		$boton=$this->datasis->modbus($sprv);

		$asprv=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'agente'),
			'titulo'  =>'Buscar Proveedor');
		$aboton=$this->datasis->modbus($asprv,'agsprv');

		$script="
		function post_add_itstra(id){
			$('#cantidad_'+id).numeric(".");
			return true;
		}";

		$do = new DataObject('ordi');
		$do->rel_one_to_many('itordi', 'itordi', 'numero');

		$edit = new DataDetails('ordi', $do);
		$edit->back_url = site_url('import/ordi/filteredgrid');
		$edit->set_rel_title('itstra','Producto <#o#>');
		$edit->script($script,'create');
		$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->numero= new inputField('N&uacute;mero', 'numero');
		$edit->numero->mode='autohide';
		$edit->numero->size=10;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->fecha = new  dateonlyField('Fecha','fecha');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->maxlength=8;
		$edit->fecha->size =12;

		$edit->status = new dropdownField('Estatus', 'status');
		$edit->status->option('A','Abierto');
		$edit->status->option('C','Cerrado');
		$edit->status->option('E','Eliminado');
		$edit->status->rule = 'required';
		$edit->status->style  = 'width:120px';

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->rule     ='trim';
		$edit->proveed->maxlength=5;
		$edit->proveed->size     =7;
		$edit->proveed->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rule     ='trim';
		$edit->nombre->maxlength=40;
		$edit->nombre->size     =40;

		$edit->agente = new inputField('Agente aduanal', 'agente');
		$edit->agente->rule     ='trim';
		$edit->agente->maxlength=5;
		$edit->agente->size     =7;
		$edit->agente->append($aboton);

		$arr=array(
			'montofob' =>'Total factura extrangera',
			'gastosi'  =>'Gastos Internacionales (Fletes, Seguros, etc)',
			'montocif' =>'Monto FOB+gastos Internacionales',
			'aranceles'=>'Suma del Impuesto Arancelario',
			'gastosn'  =>'Gastos Nacionales',
			'montotot' =>'Monto CIF + Gastos Nacionales');

		foreach($arr as $obj => $etiq){
			$edit->$obj = new inputField($etiq, $obj);
			$edit->$obj->rule     ='trim';
			$edit->$obj->maxlength=20;
			$edit->$obj->size     =20;
			$edit->$obj->css_class= 'inputnum';
		}

		$edit->arribo = new dateonlyField('Arribo', 'arribo');
		$edit->arribo->rule     ='trim';
		$edit->arribo->maxlength=8;
		$edit->arribo->size     =12;

		$edit->factura = new inputField('Factura', 'factura');
		$edit->factura->rule     ='trim';
		$edit->factura->maxlength=20;
		$edit->factura->size     =20;

		$edit->cambioofi = new inputField('Cambio Oficial', 'cambioofi');
		$edit->cambioofi->css_class= 'inputnum';
		$edit->cambioofi->rule     ='trim';
		$edit->cambioofi->maxlength=5;
		$edit->cambioofi->size     =7;

		$edit->cambioreal = new inputField('Cambio real', 'cambioreal');
		$edit->cambioreal->css_class= 'inputnum';
		$edit->cambioreal->rule     ='trim';
		$edit->cambioreal->maxlength=17;
		$edit->cambioreal->size     =17;

		$edit->peso = new inputField('Peso Total', 'peso');
		$edit->peso->css_class= 'inputnum';
		$edit->peso->rule     ='trim';
		$edit->peso->maxlength=12;
		$edit->peso->size     =12;

		$edit->condicion = new inputField('Condici&oacute;n', 'condicion');
		$edit->condicion->rule     ='trim';
		$edit->condicion->size     =40;

		//comienza el detalle
		$edit->codigo = new inputField('C&oacute;digo <#o#>','codigo_<#i#>');
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rule     = 'trim|required';
		$edit->codigo->rel_id   = 'itordi';
		$edit->codigo->maxlength= 15;
		$edit->codigo->size     = 15;
		$edit->codigo->append($btn);

		$edit->fecha = new  dateonlyField('Fecha <#o#>','fecha_<#i#>');
		$edit->fecha->db_name     ='fecha';
		$edit->fecha->rel_id      = 'itordi';
		$edit->fecha->size        = 12;
		//$edit->fecha->insertValue = date('Y-m-d');

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>','descrip_<#i#>');
		$edit->descrip->db_name  ='descrip';
		$edit->descrip->rel_id   ='itordi';
		$edit->descrip->maxlength=35;
		$edit->descrip->size     =35;

		$edit->cantidad = new inputField('Cantidad <#o#>','cantidad_<#i#>');
		$edit->cantidad->db_name  = 'cantidad';
		$edit->cantidad->css_class= 'inputnum';
		$edit->cantidad->rel_id   = 'itordi';
		$edit->cantidad->rule     = 'numeric';
		$edit->cantidad->maxlength= 10;
		$edit->cantidad->size     = 10;

		$arr=array('costofob','importefob','gastosi','costocif','importecif','montoaran','gastosn','costofinal','importefinal','montoiva','ultimo');
		foreach($arr as $obj){
			$edit->$obj = new inputField(ucfirst("$obj <#o#>"), "${obj}_<#i#>");
			$edit->$obj->db_name  = $obj;
			$edit->$obj->css_class= 'inputnum';
			$edit->$obj->rel_id   = 'itordi';
			$edit->$obj->rule     ='trim';
			$edit->$obj->maxlength=20;
			$edit->$obj->size     =20;
		}

		$edit->codaran = new inputField('Codaran <#o#>', 'codaran_<#i#>');
		$edit->codaran->db_name  = 'codaran';
		$edit->codaran->rel_id   = 'itordi';
		$edit->codaran->rule     ='trim';
		$edit->codaran->maxlength=15;
		$edit->codaran->size     =15;

		$arr=array('arancel','participan','participao');
		foreach($arr as $obj){
			$edit->$obj = new inputField(ucfirst("$obj <#o#>"), "${obj}_<#i#>");
			$edit->$obj->db_name  = $obj;
			$edit->$obj->css_class= 'inputnum';
			$edit->$obj->rel_id   = 'itordi';
			$edit->$obj->rule     ='trim';
			$edit->$obj->maxlength=7;
			$edit->$obj->size     =9;
		}

		$arr=array('precio1','precio2','precio3','precio4');
		foreach($arr as $obj){
			$edit->$obj = new inputField(ucfirst("$obj <#o#>"), "${obj}_<#i#>");
			$edit->$obj->db_name  = $obj;
			$edit->$obj->css_class= 'inputnum';
			$edit->$obj->rel_id   = 'itordi';
			$edit->$obj->rule     ='trim';
			$edit->$obj->maxlength=15;
			$edit->$obj->size     =15;
		}
		//Termina el detalle

		$edit->buttons('modify','save','undo','delete','back','add_rel');
		$edit->build();

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $edit->output;
		//$conten['form']  =&  $edit;
		//$data['content'] = $this->load->view('view_ordi',$conten,true);
		$data['title']   = '<h1>Ordi</h1>';
		$data['head']    = $this->rapyd->get_head();//.script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data); 
  }
  
  function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('nstra');
		$do->set('numero',$numero);
		$do->pk['numero'] = $numero; //Necesario cuando la clave primara se calcula por secuencia
		return true;
	}
	
	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('stra',"ORDI $codigo CREADO");
	}
	
	function _post_update($do){
		$codigo=$do->get('numero');

		logusu('stra',"ORDI $codigo MODIFICADO");
	}
	
	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('stra',"ORDI $codigo ELIMINADO");
	}

	function dataeditpre(){
		$this->rapyd->load('dataobject','datadetails');
		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo' =>'C&oacutedigo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Producto en inventario');
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');
		
		$script="
		function post_add_itstra(id){
			$('#cantidad_'+id).numeric(".");
			return true;
		}";
		
		$do = new DataObject("stra");
		$do->rel_one_to_many('itstra', 'itstra', 'numero');
		
		$edit = new DataDetails("Transferencia", $do);
		$edit->back_url = site_url("inventario/straa/filteredgrid");
		$edit->set_rel_title('itstra','Producto <#o#>');
		$edit->script($script,'create');
		$edit->script($script,'modify');
		
		$edit->pre_process('insert' ,'_pre_insert');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->numero= new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size=10;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');
		
		$edit->fecha = new  dateonlyField("Fecha",  "fecha");
		$edit->fecha->insertValue = date("Y-m-d");  
		$edit->fecha->size =12;
		
		$edit->envia = new dropdownField("Env&iacute;a", "envia");  
		$edit->envia->option("","Seleccionar");
		$edit->envia->mode="autohide";
		$edit->envia->options("SELECT ubica,ubides FROM caub ORDER BY ubica");
		$edit->envia->rule ="required";
		
		$edit->recibe = new dropdownField("Recibe", "recibe");  
		$edit->recibe->option("","Seleccionar");  
		$edit->recibe->mode="autohide";
		$edit->recibe->options("SELECT ubica,ubides FROM caub ORDER BY ubica");
		$edit->recibe->rule ="required" ;
		
		$edit->observ1 = new inputField("Observaci&oacute;n 1", "observ1");
		$edit->observ1->rule     ="trim";
		$edit->observ1->mode="autohide";
		$edit->observ1->maxlength=35;
		$edit->observ1->size     =35;
		
		$edit->observ2 = new inputField("..", "observ2");
		$edit->observ2->rule = "trim";
		$edit->observ2->mode="autohide";
		$edit->observ2->size = 35;
		
		$edit->totalg = new inputField("Total gr.", "totalg");
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->mode="autohide";
		$edit->totalg->when=array('show');
		$edit->totalg->size      = 17;

		//comienza el detalle
		$edit->codigo = new inputField("C&oacute;digo <#o#>", "codigo_<#i#>");
		$edit->codigo->db_name='codigo';
		$edit->codigo->mode="autohide";
		$edit->codigo->append($btn);
		$edit->codigo->rule = "trim|required";
		$edit->codigo->rel_id='itstra';

		$edit->descrip = new inputField("Descripci&oacute;n", "descrip_<#i#>");
		$edit->descrip->db_name = 'descrip';
		$edit->descrip->mode    = 'autohide';
		$edit->descrip->rel_id  = 'itstra';

		$edit->cantidad = new inputField("Cantidad", "cantidad_<#i#>");
		$edit->cantidad->db_name   ='cantidad';
		$edit->cantidad->mode      ='autohide';
		$edit->cantidad->css_class ='inputnum';
		$edit->cantidad->rel_id    ='itstra';
		$edit->cantidad->rule      ="numeric";
		//Termina el detalle

		$edit->buttons("modify", "save", "undo", "delete", "back","add_rel"); 
		$edit->build();
		
		$data['content'] = $edit->output;
		$data['title']   = "<h1>Transferencias de inventario</h1>";
		$data["head"]    = $this->rapyd->get_head().script('jquery.js').script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$this->load->view('view_ventanas', $data); 
	}

	function prueba(){
		$this->rapyd->load('dataobject');

		$do = new DataObject("stra");
		$do->rel_one_to_many('itstra', 'itstra', 'numero');
		$do->pointer('caub' ,'caub.ubica=stra.envia','ubides as descrip');
		$do->rel_pointer('itstra','sinv','itstra.codigo=sinv.codigo','sinv.descrip as sinvdescrip');
		$do->load('00000006');

		/*$do->set('envia','0001');
		$do->set_rel('itstra', 'descrip', 'Que bonita es esta vida 1',0);
		$do->set_rel('itstra', 'descrip', 'Que bonita es esta vida 2',1);
		$do->set_rel('itstra', 'descrip', 'Que bonita es esta vida 3',2);*/

		print_r($do->_pointer_data);
		print_r($do->_rel_pointer_data);
		print_r($do->get_all());
		//$do->save();
		
	}
}
?>