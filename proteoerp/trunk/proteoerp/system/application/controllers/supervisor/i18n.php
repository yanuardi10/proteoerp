<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class i18n extends validaciones {

	function i18n(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('pi18n');
		//$this->datasis->modulo_id('',1);
	}

	function index(){
		redirect('supervisor/i18n/filteredgrid');
	}

	function filteredgrid(){
		$this->pi18n->cargar('i18n','dataedit');
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();
$this->pi18n->msj('rifci','RIF o C.I.');
		$filter = new DataFilter('Filtro','i18n');

		$filter->modulo = new inputField('M&oacute;dulo','modulo');
		$filter->modulo->size=20;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('supervisor/i18n/dataedit/modify/<#id#>','<#id#>');

		$grid = new DataGrid('Internacionalizaci&oacute;n');
		$grid->order_by('id','asc');
		$grid->per_page = 20;

		$grid->column_orderby('ID'           , $uri     ,'id'     );
		$grid->column_orderby('Modulo'       , 'modulo' ,'modulo' );
		$grid->column_orderby('M&eacute;todo', 'metodo' ,'metodo' );
		$grid->column_orderby('Pa&iacute;s'  , 'pais'   ,'pais'   );
		$grid->column_orderby('Campo'        , 'campo'  ,'campo'  );

		$grid->add('supervisor/i18n/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['content'].= $this->pi18n->fallas();
		$data['title']   = heading('Internacionalizaci&oacute;n');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','dataedit');

		$edit = new DataEdit('Lista','i18n');
		$edit->back_url = site_url('supervisor/i18n/filteredgrid');

		$edit->modulo =  new inputField('Modulo', 'modulo');
		$edit->modulo->mode='autohide';
		$edit->modulo->rule ='trim|strtoupper|required';
		$edit->modulo->size = '20';
		$edit->modulo->maxlength=15;

		$edit->metodo =  new inputField('M&eacute;todo', 'metodo');
		$edit->metodo->rule ='trim|strtoupper|required';

		$edit->pais =  new inputField('Pa&iacute;s', 'pais');
		$edit->pais->rule ='trim|strtoupper|required';

		$edit->campo = new inputField('Campo', 'campo');
		$edit->campo->rule='required';

		$edit->mensaje = new textareaField('Mensaje', 'mensaje');
		$edit->mensaje->rule='required';

		$edit->buttons('modify','save','undo','delete','back');
		$edit->build();

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $edit->output;
		$data['title']   = '';
		$data['head']    = script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	function arapido($modulo,$metodo,$campo){
		$this->rapyd->load('dataobject','dataedit');
		$pais=$this->datasis->traevalor('PAIS');

		$edit = new DataEdit('Lista','i18n');
		$edit->back_url = site_url('supervisor/i18n/filteredgrid');

		$edit->modulo = new autoUpdateField('modulo',$modulo);
		$edit->metodo = new autoUpdateField('metodo',$metodo);
		$edit->pais   = new autoUpdateField('pais'  ,$pais  );
		$edit->campo  = new autoUpdateField('campo' ,$campo );

		$edit->mensaje = new textareaField('Mensaje', 'mensaje');
		$edit->mensaje->rule='required';

		$edit->buttons('modify','save','undo','delete','back');
		$edit->build();

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $edit->output;
		$data['title']   = '';
		$data['head']    = script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		$mSQL="";
		$this->db->simple_query($mSQL);
	}
}