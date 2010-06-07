<?php
//almacenes
class conec extends Controller {
	 	
	function conec(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->load->helper('url');
		//$this->load->helper('text');
		//$this->datasis->modulo_id(307,1);
		//$this->load->library("rapyd");
		//define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
   }
 
    function index(){
    	//$this->datasis->modulo_id(307,1);
    	redirect("supervisor/conec/filteredgrid");
    }
  
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por Conexion con Clientes");
		$filter->db->select=array("a.id","a.cliente","a.ubicacion","a.url","a.basededato","a.puerto","a.usuario","a.clave","a.observacion","b.nombre"); 
		$filter->db->from("tiketconec AS a");   
		$filter->db->join("scli AS b","a.cliente=b.cliente");
				
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
		
		$filter->cliente = new inputField("Cliente","a.cliente");
		$filter->cliente->size=20;
		//$filter->cliente->mode = "autohide";
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('supervisor/conec/dataedit/modify/<#id#>','<#cliente#>');

		$grid = new DataGrid("Lista de Conexion con clientes");
		$grid->order_by("a.cliente","asc");
		$grid->per_page = 20;

		$grid->column("Cliente",$uri);
		$grid->column("Nombre","nombre");
		$grid->column("Ubicacion","ubicacion");
		$grid->column("Url","url");
		$grid->column("Dase de dato","basededato");
		$grid->column("Puerto","puerto");
								
		$grid->add("supervisor/conec/dataedit/create");
		$grid->build();
		
    $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Conexion con Clientes</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit()
 	{
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("Conexion con clientes", "tiketconec");
		$edit->back_url = site_url("supervisor/conec/filteredgrid");
		
		//$edit->post_process('update','_post_update');
		
		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
				'cliente' =>'C&oacute;digo Socio',
				'nombre'=>'Nombre', 
				'cirepre'=>'Rif/Cedula',
				'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Socio','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cliente','dire11'=>'ubicacion'),
			'titulo'  =>'Buscar Socio');
			
			$boton =$this->datasis->modbus($mSCLId);
		
		$edit->cliente = new inputField("Cliente","cliente");
		$edit->cliente->size=20;
		//$edit->cliente->mode = "autohide";
		$edit->cliente->append($boton);

		$edit->ubicacion = new inputField("Ubicacion","ubicacion");
		$edit->ubicacion->size=50;
		
		$edit->url = new inputField("URL","url");
		$edit->url->size=50;
		
		$edit->basededato = new inputField("Dase de Dato","basededato");
		$edit->basededato->size=20;
		
		$edit->puerto = new inputField("Puerto","puerto");
		$edit->puerto->size=10;
		
		$edit->usuario = new inputField("Usuario","usuario");
		$edit->usuario->size=20;
		
		$edit->clave = new inputField("Clave","clave");
		$edit->clave->size=20;
    
		$edit->buttons("modify", "save", "undo","delete","back");
		$edit->build();
 
		$data['content'] = $edit->output;
    $data['title']   = "<h1>Conexion con clientes</h1>";
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$descrip=$do->get('descrip');
	}
}
?>