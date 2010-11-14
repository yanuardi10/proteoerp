<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Contenedor extends validaciones {

	function Contenedor(){
		parent::Controller(); 
		$this->load->library('rapyd');
		$this->prefijo='crm_';
		//$this->datasis->modulo_id(136,1);
	}

	function index(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('titulo', $this->prefijo.'contenedor');

		$filter->descripcion = new inputField('Descripcion', 'descripcion');
		$filter->descripcion->size=5;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('crm/contenedor/dataedit/show/<#id#>','<#id#>');

		$grid = new DataGrid('Lista de Cajas');
		//$grid->order_by('caja','asc');
		$grid->per_page = 7;

		$grid->column('id',$uri);
		$grid->column('Derivado','derivado');
		$grid->column('Tipo', 'tipo');
		$grid->column('Status', 'status');
		$grid->column('Fecha','fecha');
		$grid->column('Cierre','cierre');
		$grid->column('Titulo','titulo');
		$grid->column('Cliente','cliente');
		$grid->column('Proveed','proveed');
		$grid->column('Descripcion','descripcion');
		$grid->column('Condiciones','condiciones');

		$grid->add('crm/contenedor/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Cajas</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Contenedor', $this->prefijo.'contenedor');

		$sprv=array(
			'tabla'   => $this->prefijo.'contenedor',
			'columnas'=> array('id' =>'C&oacute;digo','descripcion'=>'Descripci&oacute;n'),
			'filtro'  => array('descripcion'=>'Descripci&oacute;n'),
			'retornar'=> array('id'=>'derivado'),
			'titulo'  => 'Buscar Contrato');
		$boton3=$this->datasis->modbus($sprv);

		$sprv=array(
			'tabla'   => 'sprv',
			'columnas'=> array('proveed' =>'C&oacute;digo Proveedor','nombre'=>'Nombre','rif'=>'RIF'),
			'filtro'  => array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=> array('proveed'=>'proveedor'),
			'titulo'  => 'Buscar Proveedor');
		$boton2=$this->datasis->modbus($sprv);

		$scli=array(
			'tabla'   =>'scli',
			'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'contacto'=>'Contacto'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cliente'),
			'titulo'  =>'Buscar Cliente');
		$boton=$this->datasis->modbus($scli);

		/*$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');*/

		$edit->back_url = site_url('crm/contenedor/index');

		$edit->usuario  = new autoUpdateField("usuario", $this->session->userdata('usuario'), $this->session->userdata('usuario'));

		$edit->titulo =  new inputField('Titulo', 'titulo');
		$edit->titulo->size = 15;
		$edit->titulo->maxlength=30;
		$edit->titulo->rule = 'trim|strtoupper|required';

		$edit->derivado =  new inputField('Derivado', 'derivado');
		$edit->derivado->size = 15;
		$edit->derivado->maxlength=30;
		$edit->derivado->rule = 'trim|strtoupper|required';
		$edit->derivado->append($boton3. 'Si es sub-contrato');

		$edit->proveedor =  new inputField('Proveedor', 'proveedor');
		$edit->proveedor->size = 15;
		$edit->proveedor->maxlength=30;
		$edit->proveedor->rule = 'trim|strtoupper|required';
		$edit->proveedor->append($boton2);

		$edit->cliente =  new inputField('Cliente', 'cliente');
		$edit->cliente->size = 15;
		$edit->cliente->maxlength=30;
		$edit->cliente->rule = 'trim|strtoupper|required';
		$edit->cliente->append($boton);

		$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->rule = 'required';
		$edit->tipo->option('','Seleccionar');
		$edit->tipo->options('SELECT id,contenedor FROM '.$this->prefijo.'tipos ');

		$edit->status = new dropdownField('Status', 'status');
		$edit->status->rule = 'required';
		$edit->status->option('','Seleccionar');
		$edit->status->options('SELECT id,contenedor FROM '.$this->prefijo.'status ');

		$edit->fecha = new dateField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->rule = 'required';
		$edit->fecha->size = 10;
		$edit->fecha->maxlength=8;
		$edit->fecha->insertValue = date('Y-m-d');

		$edit->cierre = new dateField('Cierre', 'cierre','d/m/Y');
		$edit->cierre->rule = 'required';
		$edit->cierre->size = 10;
		$edit->cierre->maxlength=8;
		$edit->cierre->insertValue = date('Y-m-d');

		$edit->descripcion =  new textareaField('Descripci&oacute;n', 'descripcion');
		$edit->descripcion->cols = 70;
		$edit->descripcion->rows = 4;
		$edit->descripcion->rule = "trim|required";

		$edit->condiciones =  new textareaField('Condiciones', 'condiciones');
		$edit->condiciones->cols = 70;
		$edit->condiciones->rows = 4;
		$edit->condiciones->rule = "trim|required";


		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Marca</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}

	function instala(){
		$prefijo=$this->prefijo;

		$mSQL="CREATE TABLE `${prefijo}comentarios` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `usuario` varchar(50) DEFAULT NULL,
		  `contenedor` int(11) NOT NULL DEFAULT '0',
		  `fecha` date DEFAULT NULL,
		  `motivo` varchar(200) DEFAULT NULL,
		  `cuerpo` text,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
		var_dump($this->db->simple_query($mSQL));

		$mSQL="CREATE TABLE `${prefijo}definiciones` (
		  `id` int(7) NOT NULL AUTO_INCREMENT,
		  `nombre` varchar(50) DEFAULT '0',
		  `estructura` text,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
		var_dump($this->db->simple_query($mSQL));


		$mSQL="CREATE TABLE `${prefijo}contenedor` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `usuario` varchar(50) DEFAULT NULL,
		  `derivado` int(11) DEFAULT '0',
		  `tipo` int(7) DEFAULT '0',
		  `status` int(7) DEFAULT '0',
		  `fecha` date DEFAULT NULL,
		  `cierre` date DEFAULT NULL,
		  `titulo` varchar(200) DEFAULT NULL,
		  `cliente` varchar(5) DEFAULT NULL,
		  `proveed` varchar(5) DEFAULT NULL,
		  `descripcion` text,
		  `condiciones` text,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set." COMMENT='contenedor'";
		var_dump($this->db->simple_query($mSQL));
		
		$mSQL="CREATE TABLE `${prefijo}eventos` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `usuario` varchar(50) DEFAULT NULL,
		  `contenedor` int(11) NOT NULL DEFAULT '0',
		  `fecha` date DEFAULT NULL,
		  `evento` varchar(200) DEFAULT NULL,
		  `vence` date DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
		var_dump($this->db->simple_query($mSQL));
		
		$mSQL="CREATE TABLE `${prefijo}imagenes` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `usuario` varchar(50) DEFAULT NULL,
		  `contenedor` int(11) NOT NULL DEFAULT '0',
		  `fecha` date DEFAULT NULL,
		  `nombre` varchar(200) DEFAULT NULL,
		  `descripcion` text,
		  `url` varchar(200) DEFAULT NULL,
		  `imagen` blob,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
		var_dump($this->db->simple_query($mSQL));

		$mSQL="CREATE TABLE `${prefijo}status` (
		  `id` int(7) NOT NULL AUTO_INCREMENT,
		  `usuario` varchar(50) DEFAULT NULL,
		  `contenedor` int(11) DEFAULT '0',
		  `descrip` varchar(50) DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="CREATE TABLE `${prefijo}tipos` (
		  `id` int(7) NOT NULL AUTO_INCREMENT,
		  `usuario` varchar(50) DEFAULT NULL,
		  `contenedor` int(11) DEFAULT '0',
		  `descrip` varchar(50) DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
		var_dump($this->db->simple_query($mSQL));

	}


}
