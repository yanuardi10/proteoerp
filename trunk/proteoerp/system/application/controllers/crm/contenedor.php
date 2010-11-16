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

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		if($edit->_status=='show'){
			$id=$edit->_dataobject->get('id');

			$grid = new DataGrid('Comentario',$this->prefijo.'comentarios');
			$grid->db->where('contenedor',$id);
			$grid->order_by('fecha','asc');
			$grid->per_page = 100;

			$url=anchor('crm/contenedor/comentario/'.$id.'/show/<#id#>','<#id#>');
			$grid->column_orderby('ID'  ,$url      ,'id');
			$grid->column_orderby('Fecha'  ,'<dbdate_to_human><#fecha#></dbdate_to_human>'      ,'fecha');
			$grid->column_orderby('Motivo' ,'motivo'     ,'motivo');
			$grid->column_orderby('Cuerpo' ,'<html_entity_decode><#cuerpo#></html_entity_decode>' ,'cuerpo');

			$grid->add('crm/contenedor/comentario/'.$id.'/create');
			$grid->build();
			$coment=$grid->output;

			$even= new DataGrid('Eventos asociados',$this->prefijo.'eventos');
			$even->db->where('contenedor',$id);
			$even->order_by('fecha','asc');
			$even->per_page = 100;

			$url=anchor('crm/contenedor/eventos/'.$id.'/show/<#id#>','<#id#>');
			$even->column_orderby('ID'  ,$url      ,'id');
			$even->column_orderby('Fecha'  ,'<dbdate_to_human><#fecha#></dbdate_to_human>'      ,'fecha');
			$even->column_orderby('Vence'  ,'<dbdate_to_human><#vence#></dbdate_to_human>'      ,'vence');
			$even->column_orderby('Evento' ,'evento'     ,'evento');

			$even->add('crm/contenedor/eventos/'.$id.'/create');
			$even->build();
			$evento=$even->output;
			
			$parti= new DataGrid('Partidas asociadas',$this->prefijo.'partidas');
			$parti->db->where('contenedor',$id);
			$parti->per_page = 100;

			$url=anchor('crm/contenedor/partidas/'.$id.'/show/<#codigo#>','<#codigo#>');
			$parti->column_orderby('C&oacute;digo' ,$url     ,'codigo');
			$parti->column_orderby('Descripci&oacute;n'  ,'descripcion'      ,'descripcion');
			$parti->column_orderby('Enlace' ,'enlace'     ,'enlace');
			$parti->column_orderby('Medida' ,'medida'     ,'medida');
			$parti->column_orderby('Iva'  ,'<nformat><#iva#></nformat>'      ,'iva','align="right"');

			$parti->add('crm/contenedor/partidas/'.$id.'/create');
			$parti->build();
			$partid=$parti->output;
			
			$monto= new DataGrid('Montos asociados',$this->prefijo.'montos');
			$monto->db->where('contenedor',$id);
			$monto->per_page = 100;

			$url=anchor('crm/contenedor/montos/'.$id.'/show/<#id#>','<#id#>');
			$monto->column_orderby('ID'  ,$url      ,'id');
			$monto->column_orderby('Partida' ,'partida'     ,'partida');
			$monto->column_orderby('Fecha'  ,'<dbdate_to_human><#fecha#></dbdate_to_human>'      ,'fecha');
			$monto->column_orderby('Debe'  ,'<nformat><#debe#></nformat>'      ,'debe','align="right"');
			$monto->column_orderby('Haber'  ,'<nformat><#haber#></nformat>'     ,'haber','align="right"');

			$monto->add('crm/contenedor/montos/'.$id.'/create');
			$monto->build();
			$montos=$monto->output;

			$adjun= new DataGrid('Archivos Ajuntos',$this->prefijo.'adjuntos');
			$adjun->db->where('contenedor',$id);
			$adjun->per_page = 100;

			$url=anchor('crm/contenedor/adjuntos/'.$id.'/show/<#id#>','<#id#>');
			$adjun->column_orderby('ID'  ,$url      ,'id');
			$adjun->column_orderby('Fecha'  ,'<dbdate_to_human><#fecha#></dbdate_to_human>'      ,'fecha');
			$adjun->column_orderby('Nombre' ,'nombre'     ,'nombre');
			$adjun->column_orderby('Descripci&oacute;n'  ,'descripcion'      ,'descripcion');

			$adjun->add('crm/contenedor/adjuntos/'.$id.'/create');
			$adjun->build();
			$adjunt=$adjun->output;

		}else{
			$coment=$evento=$partid=$montos=$adjunt='';
		}

		$data['content'] = $edit->output.$coment.$evento.$partid.$montos.$adjunt;
		$data['title']   = '<h1>Contenedor</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function comentario($contenedor){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Comentario', $this->prefijo.'comentarios');

		$edit->back_url = site_url('crm/contenedor/dataedit/show/'.$contenedor);

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


	function eventos($contenedor){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Eventos', $this->prefijo.'eventos');

		$edit->back_url = site_url('crm/contenedor/dataedit/show/'.$contenedor);

		$edit->usuario    = new autoUpdateField('usuario'   , $this->session->userdata('usuario'), $this->session->userdata('usuario'));
		$edit->contenedor = new autoUpdateField('contenedor', $contenedor,$contenedor);

		$edit->fecha = new dateField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->rule = 'required';
		$edit->fecha->size = 10;
		$edit->fecha->maxlength=8;
		$edit->fecha->insertValue = date('Y-m-d');

		$edit->vence = new dateField('Vence', 'vence','d/m/Y');
		$edit->vence->rule = 'required';
		$edit->vence->size = 10;
		$edit->vence->maxlength=8;
		$edit->vence->insertValue = date('Y-m-d');

		$edit->evento =  new inputField('Evento', 'evento');
		$edit->evento->rule = 'trim|required|max_length[200]';

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Eventos de contratos</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	function partidas($contenedor){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Partidas', $this->prefijo.'partidas');

		$edit->back_url = site_url('crm/contenedor/dataedit/show/'.$contenedor);

		$edit->codigo =  new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->rule = 'trim|required|max_length[15]';

		$edit->descripcion =  new inputField('Descripci&oacute;n', 'descripcion');
		$edit->descripcion->rule = 'trim|required|max_length[100]';

		$edit->enlace =  new inputField('Enlace Administrativo', 'enlace');
		$edit->enlace->rule = 'trim|required|max_length[6]';
		$edit->enlace->size = 7;

		$edit->medida =  new inputField('Medida', 'medida');
		$edit->medida->rule = 'trim|required|max_length[5]';
		$edit->medida->size = 6;
		$edit->medida->max_size = 5;

		$edit->iva =  new inputField('Iva', 'iva');
		$edit->iva->rule = 'trim|required|numeric';

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Partidas de contratos</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	function montos($contenedor){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Montos', $this->prefijo.'montos');
		$edit->back_url = site_url('crm/contenedor/dataedit/show/'.$contenedor);

		$edit->usuario    = new autoUpdateField('usuario'   , $this->session->userdata('usuario'), $this->session->userdata('usuario'));
		$edit->contenedor = new autoUpdateField('contenedor', $contenedor,$contenedor);

		$edit->fecha = new dateField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->rule = 'required';
		$edit->fecha->size = 10;
		$edit->fecha->maxlength=8;
		$edit->fecha->insertValue = date('Y-m-d');

		$edit->partida = new dropdownField('Partida', 'partida');
		$edit->partida->rule = 'required';
		$edit->partida->option('','Seleccionar');
		$edit->partida->options('SELECT codigo,descripcion FROM '.$this->prefijo.'partidas WHERE contenedor='.$this->db->escape($contenedor));

		$edit->descripcion =  new inputField('Descripci&oacute;n', 'descripcion');
		$edit->descripcion->rule = 'trim|required|max_length[200]';

		$edit->debe =  new inputField('Debe', 'debe');
		$edit->debe->rule = 'required|numeric';

		$edit->haber =  new inputField('Haber', 'haber');
		$edit->haber->rule = 'required|numeric';

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Montos de contratos</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	function adjuntos($contenedor){

		$this->load->library("path");
		$path=new Path();
		$path->setPath($this->config->item('uploads_dir'));
		$path->append('/crm');
		$upload_path =$path->getPath().'/';

		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Adjuntos', $this->prefijo.'adjuntos');
		$edit->back_url = site_url('crm/contenedor/dataedit/show/'.$contenedor);

		$edit->usuario    = new autoUpdateField('usuario'   , $this->session->userdata('usuario'), $this->session->userdata('usuario'));
		$edit->contenedor = new autoUpdateField('contenedor', $contenedor,$contenedor);

		$edit->fecha = new dateField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->rule = 'required';
		$edit->fecha->size = 10;
		$edit->fecha->maxlength=8;
		$edit->fecha->insertValue = date('Y-m-d');

		$edit->descripcion =  new inputField('Descripci&oacute;n', 'descripcion');
		$edit->descripcion->rule = 'trim|required|max_length[200]';

		$edit->nombre  = new uploadField("Archivo", "nombre");
		$edit->nombre->upload_path = $upload_path;
		$edit->nombre->rule = 'required';
		$edit->nombre->allowed_types = "pdf|doc|xls|txt";
		//$edit->img->thumb = array (63,91);

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Montos de contratos</h1>';
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
