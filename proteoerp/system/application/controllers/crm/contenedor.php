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

		$filter = new DataFilter('Contenedor', $this->prefijo.'contenedor');

		$filter->descripcion = new inputField('Descripcion', 'descripcion');
		//$filter->descripcion->size=5;

		$filter->titulo = new inputField('Titulo', 'titulo');

		$filter->buttons("reset","search");
		$filter->build();

		$uri  = anchor('crm/contenedor/dataedit/show/<#id#>','<#id#>');
		$curl = anchor('crm/contenedor/comentario/<#id#>/create','Comentario');

		$grid = new DataGrid('Lista de Contenedores');
		//$grid->order_by('caja','asc');
		$grid->per_page = 7;

		$grid->column('id',$uri);
		$grid->column_orderby('Derivado'   ,'derivado'   ,'derivado');
		$grid->column_orderby('Tipo'       ,'tipo'       ,'tipo');
		$grid->column_orderby('Status'     ,'status'     ,'status');
		$grid->column_orderby('Fecha'      ,'<dbdate_to_human><#fecha#></dbdate_to_human>'      ,'fecha');
		$grid->column_orderby('Cierre'     ,'cierre'     ,'cierre');
		$grid->column_orderby('Titulo'     ,'titulo'     ,'titulo');
		$grid->column_orderby('Cliente'    ,'cliente'    ,'cliente');
		$grid->column_orderby('Proveed'    ,'proveed'    ,'proveed');
		$grid->column_orderby('Resumen'    ,'resumen'    ,'resumen');
		$grid->column_orderby('Condiciones','condiciones','condiciones');
		$grid->column('Accion',$curl);

		$grid->add('crm/contenedor/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Contenedor</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit','datagrid');
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

		$edit->usuario  = new autoUpdateField('usuario', $this->session->userdata('usuario'), $this->session->userdata('usuario'));

		$edit->titulo =  new inputField('Titulo', 'titulo');
		$edit->titulo->size = 50;
		$edit->titulo->maxlength=200;
		$edit->titulo->rule = 'trim|strtoupper|required||max_length[200]';

		$edit->derivado =  new inputField('Derivado', 'derivado');
		$edit->derivado->size = 15;
		$edit->derivado->maxlength=30;
		$edit->derivado->rule = 'trim|strtoupper';
		$edit->derivado->append($boton3. 'Si es sub-contrato');

		$edit->proveedor =  new inputField('Proveedor', 'proveedor');
		$edit->proveedor->size = 15;
		$edit->proveedor->maxlength=30;
		$edit->proveedor->rule = 'trim|strtoupper';
		$edit->proveedor->append($boton2);

		$edit->cliente =  new inputField('Cliente', 'cliente');
		$edit->cliente->size = 15;
		$edit->cliente->maxlength=30;
		$edit->cliente->rule = 'trim|strtoupper';
		$edit->cliente->append($boton);

		$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->rule = 'required';
		$edit->tipo->option('','Seleccionar');
		$edit->tipo->options('SELECT id,nombre FROM '.$this->prefijo.'definiciones');

		$edit->status = new dropdownField('Status', 'status');
		$edit->status->rule = 'required';
		$edit->status->option('','Seleccionar');
		$edit->status->options('SELECT id,descrip FROM '.$this->prefijo.'status ');

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

		$edit->resumen =  new textareaField('Resumen', 'resumen');
		$edit->resumen->cols = 87;
		$edit->resumen->rows = 2;
		$edit->resumen->rule = 'trim|required|max_length[200]';

		$edit->descripcion =  new editorField('Descripci&oacute;n', 'descripcion');
		$edit->descripcion->cols = 90;
		$edit->descripcion->rows = 4;
		$edit->descripcion->rule = 'trim|required';

		$edit->condiciones =  new textareaField('Condiciones', 'condiciones');
		$edit->condiciones->cols = 87;
		$edit->condiciones->rows = 4;
		$edit->condiciones->rule = 'trim|required';

		if($edit->_status=='show'){
			$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
			$edit->build();

			$grid = new DataGrid('Comentario',$this->prefijo.'comentarios');
			$grid->order_by('fecha','asc');
			$grid->per_page = 7;

			$grid->column_orderby('Fecha'      ,'<dbdate_to_human><#fecha#></dbdate_to_human>'      ,'fecha');
			$grid->column_orderby('Motivo'     ,'motivo'     ,'motivo');
			$grid->column_orderby('Cuerpo'     ,'cuerpo'     ,'cuerpo');

			$grid->add('crm/contenedor/comentario/create');
			$grid->build();
			$coment=$grid->output;
		}else{
			$coment='';
		}


		$data['content'] = $edit->output.$coment;
		$data['title']   = '<h1>Contenedor</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}

	function comentario($contenedor){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Comentario', $this->prefijo.'comentarios');

		$edit->back_url = site_url('crm/contenedor/index');

		$edit->usuario    = new autoUpdateField('usuario'   , $this->session->userdata('usuario'), $this->session->userdata('usuario'));
		$edit->contenedor = new autoUpdateField('contenedor', $contenedor,$contenedor);

		$edit->fecha = new dateField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->rule = 'required';
		$edit->fecha->size = 10;
		$edit->fecha->maxlength=8;
		$edit->fecha->insertValue = date('Y-m-d');

		$edit->motivo =  new inputField('Motivo', 'motivo');
		$edit->motivo->rule = 'trim|required|max_length[200]';

		$edit->cuerpo =  new editorField('Cuerpo', 'cuerpo');
		$edit->cuerpo->cols = 90;
		$edit->cuerpo->rows = 4;
		$edit->cuerpo->rule = 'trim|required';

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Comentarios</h1>';
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
		  `resumen` varchar(200) DEFAULT NULL,
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
		  `definicion` int(11) DEFAULT '0',
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

		$mSQL="CREATE TABLE `${prefijo}adjuntos` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `usuario` varchar(50) DEFAULT NULL,
		  `contenedor` int(11) NOT NULL DEFAULT '0',
		  `fecha` date DEFAULT NULL,
		  `nombre` varchar(200) DEFAULT NULL,
		  `descripcion` text,
		  `url` varchar(200) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
		var_dump($this->db->simple_query($mSQL));

		$mSQL="CREATE TABLE `${prefijo}montos` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `usuario` varchar(50) DEFAULT NULL,
		  `contenedor` int(11) NOT NULL DEFAULT '0',
		  `fecha` date DEFAULT NULL,
		  `partida` varchar(15) DEFAULT NULL,
		  `descripcion` varchar(200) DEFAULT NULL,
		  `debe` decimal(19,0) DEFAULT '0',
		  `haber` decimal(19,0) DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
		var_dump($this->db->simple_query($mSQL));

		$mSQL="CREATE TABLE `${prefijo}partidas` (
		  `codigo` varchar(15) NOT NULL DEFAULT '',
		  `contenedor` int(7) NOT NULL,
		  `descripcion` varchar(100) DEFAULT NULL,
		  `enlace` varchar(6) DEFAULT NULL,
		  `iva` decimal(5,2) DEFAULT NULL,
		  `medida` varchar(5) DEFAULT NULL,
		  `dacumu` varchar(5) DEFAULT NULL,
		  PRIMARY KEY (`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
		var_dump($this->db->simple_query($mSQL));

		$mSQL="ALTER TABLE `${prefijo}status`  CHANGE COLUMN `contenedor` `definicion` INT(11) NULL DEFAULT '0' AFTER `usuario`";
		var_dump($this->db->simple_query($mSQL));
	}

}