<?php
class conec extends Controller {
	 	
	function conec(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(307,1);
	}
 
 	function index(){
		//$this->datasis->modulo_id(307,1);
		redirect('supervisor/conec/filteredgrid');
	}
  
	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();
		
		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
				'cliente' =>'C&oacute;digo Socio',
				'nombre'=>'Nombre', 
				'cirepre'=>'Rif/Cedula',
				'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Socio','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cliente'),
			'titulo'  =>'Buscar Socio');
			
		$boton =$this->datasis->modbus($mSCLId);

		$filter = new DataFilter('Filtro por Conexi&oacute;n con Clientes');
		$filter->db->select=array("a.id","a.cliente","a.ubicacion","a.url","a.basededato","a.puerto","a.usuario","a.clave","a.observacion","b.nombre"); 
		$filter->db->from('tiketconec AS a');   
		$filter->db->join('scli AS b','a.cliente=b.cliente');

		$filter->cliente = new inputField('Cliente','cliente');
		$filter->cliente->db_name="a.cliente";
		$filter->cliente->size=20;
		$filter->cliente->append($boton);

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('supervisor/conec/dataedit/show/<#id#>','<#cliente#>');

		$grid = new DataGrid('Lista de Conexi&oacute;n con clientes');
		$grid->order_by('a.cliente','asc');
		$grid->per_page = 20;

		$grid->column_orderby('Cliente',$uri,'cliente');
		$grid->column_orderby('Nombre','nombre','nombre');
		//$grid->column_orderby("Ubicacion","ubicacion",'ubicacion');
		$grid->column_orderby('html','phtml','phtml');
		$grid->column_orderby('Proteo','sistema','sistema');
		//$grid->column_orderby("DB","basededato",'basededato');
		//$grid->column_orderby("Puerto","puerto",'puerto');
								
		$grid->add('supervisor/conec/dataedit/create');
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Conexi&oacute;n con Clientes</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){
		$this->rapyd->load('dataedit');
		$this->rapyd->uri->keep_persistence();

		$edit = new DataEdit('Conexi&oacute;n con clientes', 'tiketconec');
		$edit->back_url = site_url('supervisor/conec/filteredgrid');
				
		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
				'cliente' =>'C&oacute;digo Socio',
				'nombre'=>'Nombre', 
				'cirepre'=>'Rif/Cedula',
				'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Socio','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cliente'),
			'titulo'  =>'Buscar Socio');
			
		$boton =$this->datasis->modbus($mSCLId);
		
		$edit->cliente = new inputField("Cliente","cliente");
		$edit->cliente->size=20;
		$edit->cliente->rule='required';
		//$edit->cliente->mode = "autohide";
		$edit->cliente->append($boton);

		$edit->url = new inputField("URL","url");
		$edit->url->rule='required';
		$edit->url->size=50;

		$edit->phtml = new inputField('Puerto html','phtml');
		$edit->phtml->rule='numeric';
		$edit->phtml->append('Solo necesario cuando el puerto en el cliente es redireccionado');
		$edit->phtml->size=10;

		$edit->sistema = new inputField("Ruta a Proteo","sistema");
		$edit->sistema->size=20;
		
		$edit->basededato = new inputField("Nombre","basededato");
		$edit->basededato->size=20;
		$edit->basededato->group='Datos de la base de datos';
		
		$edit->puerto = new inputField("Puerto","puerto");
		$edit->puerto->size=10;
		$edit->puerto->group='Datos de la base de datos';
		
		$edit->usuario = new inputField("Usuario","usuario");
		$edit->usuario->group='Datos de la base de datos';
		$edit->usuario->size=20;
		
		$edit->clave = new inputField("Clave","clave");
		$edit->clave->group='Datos de la base de datos';
		$edit->clave->size=20;
    
		$edit->buttons("modify", "save", "undo","delete","back");
		$edit->build();
 
		$data['content'] = $edit->output;
		$data['title']   = "<h1>Conexi&oacute;n con clientes</h1>";
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		$mSQL="CREATE TABLE `tiketconec` (
		  `id` int(11) NOT NULL auto_increment,
		  `cliente` char(5) default NULL,
		  `phtml` int(20) default NULL,
		  `url` varchar(100) default NULL,
		  `sistema` varchar(50) default NULL,
		  `basededato` varchar(20) default NULL,
		  `puerto` int(3) default NULL,
		  `usuario` varchar(20) default NULL,
		  `clave` varchar(20) default NULL,
		  `observacion` text,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}

}
