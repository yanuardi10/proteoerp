<?php require_once(APPPATH.'/controllers/crm/contenedor.php');

class Callcenter extends Controller {

	var $url='crm/callcenter/';
	var $error_string='';

	function callcenter(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id('205',1);
		$this->prefijo='crm_';
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Clientes', 'scli');

		$filter->cliente = new inputField('C&oacute;digo', 'cliente');
		$filter->cliente->size=6;
		$filter->cliente->group = "CLIENTE";

		$filter->nombre= new inputField('Nombre','nombre');
		$filter->nombre->size=30;
		$filter->nombre->group = "CLIENTE";

		$filter->grupo = new dropdownField('Grupo', 'grupo');
		$filter->grupo->option('','Todos');
		$filter->grupo->options('SELECT grupo, gr_desc FROM grcl ORDER BY gr_desc');
		$filter->grupo->style = 'width:140px';
		$filter->grupo->group = "CLIENTE";

		$filter->rifci= new inputField('Rif/CI','rifci');
		$filter->rifci->size=15;
		$filter->rifci->group = "VALORES";

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$uri = anchor($this->url.'dataedit/show/<#id#>','<#cliente#>');

		$grid = new DataGrid('Lista de Clientes');
		$grid->order_by('nombre','asc');
		$grid->per_page=20;

		$uri_2  = anchor('ventas/scli/dataedit/show/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12','title'=>'Editar')));
		$uri_2 .= anchor('ventas/scli/consulta/<#id#>',img(array('src'=>'images/estadistica.jpeg','border'=>'0','alt'=>'Consultar','height'=>'12','title'=>'Consultar')));
		$uri_2 .= img(array('src'=>'images/<siinulo><#tipo#>|N|S</siinulo>.gif','border'=>'0','alt'=>'Estado','title'=>'Estado'));

		//$grid->column('Acci&oacute;n',$uri_2);
		$grid->column_orderby('Cliente',$uri,'cliente');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Rif/CI','rifci','rifci');
		$grid->column_orderby('Tipo'  ,'tiva','tiva','align=\'center\'');
		$grid->column_orderby('Telefono','telefono','telefono');

		$grid->add('ventas/scli/dataedit/create','Agregar');
		$grid->build();


		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		$data['title']   = heading('Modulo de Clientes');
		$data['script']  = script('jquery.js');
		//$data["script"] .= script('superTables.js');
		$this->load->view('view_ventanas', $data);


	}

	function dataedit($sta,$id){
		$this->rapyd->load('dataedit','datagrid');

		$edit = new DataEdit('Ficha del cliente','scli');
		$edit->back_url = site_url($this->url.'filteredgrid');

		/*$edit->pre_process( 'insert','_pre_insert');
		$edit->pre_process( 'delete','_pre_delete');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');*/

		$edit->cliente = new inputField('Cliente', 'cliente');
		$edit->cliente->rule     ='trim|required|existesprv';
		$edit->cliente->maxlength=5;
		$edit->cliente->size     =7;
		$edit->cliente->mode = 'autohide';

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rule     ='trim';
		$edit->nombre->maxlength=40;
		$edit->nombre->size     =40;
		$edit->nombre->mode = 'autohide';
		$edit->nombre->in='cliente';

		$edit->rifci = new inputField('RIF/CI', 'rifci');
		$edit->rifci->rule     ='trim';
		$edit->rifci->maxlength=40;
		$edit->rifci->size     =40;
		$edit->rifci->mode = 'autohide';

		$edit->telefono = new inputField('Tel&eacute;fono', 'telefono');
		$edit->telefono->rule     ='trim';
		$edit->telefono->maxlength=40;
		$edit->telefono->size     =40;
		$edit->telefono->mode = 'autohide';

		//$accion="javascript:window.location='".site_url('import/limport/liqui/'.$edit->_dataobject->pk['numero'])."'";
		//$edit->button_status('btn_liqui','Descargar Caldeco',$accion,'BR','show');

		$edit->buttons('undo','back');
		$edit->build();


		$data['content'] =  $edit->output;
		$crm=$edit->_dataobject->get('crm');
		if(empty($crm)){
			$dbdata['usuario']    = $this->secu->usuario();
			$dbdata['status']     = 'A';
			$dbdata['fecha']      = date('Ymd');
			$dbdata['titulo']     = 'Callcenter';
			$dbdata['cliente']    = $edit->_dataobject->get('cliente');
			$dbdata['descripcion']= '';
			$mSQL= $this->db->insert_string('crm_contenedor', $dbdata);
			$this->db->simple_query($mSQL);
			$crm = $this->db->insert_id();
			$mSQL= "UPDATE scli SET crm = $crm WHERE id=$id";
			$this->db->simple_query($mSQL);
		}

		$this->crm_back=site_url("crm/callcenter/dataedit/$sta/$id");
		$adici=array($edit->_dataobject->pk['id']);

		$this->prefijo='crm_';
		$data['content'] .= Contenedor::_showAdjuntos($crm   ,'crm/callcenter/adjuntos'   ,$adici);
		$data['content'] .= Contenedor::_showEventos($crm    ,'crm/callcenter/eventos'    ,$adici);
		$data['content'] .= Contenedor::_showComentarios($crm,'crm/callcenter/comentarios',$adici);

		$data['style']   = style('redmond/jquery-ui.css');

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$data['script'] .= phpscript('nformat.js');
		$data['head']    = $this->rapyd->get_head();
		$data['title']   =  heading('Callcenter');
		$this->load->view('view_ventanas', $data);
	}

	function adjuntos($id,$ordi){
		$this->crm_back=site_url('crm/callcenter/dataedit/show/'.$ordi);
		$this->prefijo='crm_';
		contenedor::adjuntos($id);
	}

	function comentarios($id,$ordi){
		$this->crm_back=site_url('crm/callcenter/dataedit/show/'.$ordi);
		$this->prefijo='crm_';
		contenedor::comentario($id);
	}

	function eventos($id,$ordi){
		$this->crm_back=site_url('crm/callcenter/dataedit/show/'.$ordi);
		$this->prefijo='crm_';
		contenedor::eventos($id);
	}


	//crea un contenedor para asociarlo
	//con el crm
	function contenedor($id){

	}


	function instalar(){
		if(!$this->db->field_exists('crm', 'scli')){
			$mSQL='ALTER TABLE `scli`  ADD COLUMN `crm` INT(15) UNSIGNED NULL DEFAULT NULL';
			var_dump($this->db->simple_query($mSQL));
		}
		$this->prefijo='crm_';
		contenedor::instalar();
	}
}
