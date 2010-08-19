<?php
class Clientesp extends Controller {

    var $data_type = null;
    var $data = null;
 
    function Clientesp()
    {
	parent::Controller(); 

	$this->load->helper('url');
	$this->load->helper('text');

	$this->load->library("rapyd");

	//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
	define ("THISFILE",   APPPATH."controllers/hotel/". $this->uri->segment(2).EXT);
    }

       function index(){
    	  $this->datasis->modulo_id(812,1);
	      redirect("hospitalidad/clientesp/filteredgrid");
    }

    ##### callback test (for DataFilter + DataGrid) #####
    function test($id,$const)
    {
	return $id*$const;
    }

 function filteredgrid()
	{
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Clientes", 'scli');
		$filter->gr_desc = new inputField("C&oacute;digo", "cliente");
		$filter->gr_desc->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('hospitalidad/clientesp/dataedit/show/<#cliente#>','<#cliente#>');

		$grid = new DataGrid("Lista de Clientes/Empresas");
		$grid->order_by("cliente","asc");
		$grid->per_page = 7;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre","nombre");
		$grid->column("R.I.F","rifci");
		$grid->column("Des. Grupo","gr_desc");
		$grid->column("Cr&eacute;dito/Dias","formap");
		$grid->column("L&iacute;mite","limite");
		$grid->column("Pa&iacute;s","pais");
		
		$grid->add("hospitalidad/clientesp/dataedit/create");
		$grid->build();

	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Clientes</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
		
	}

	function dataedit()
	{ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Datos del Cliente", "scli");
		$edit->back_url = site_url("hospitalidad/clientesp/filteredgrid");
	
		$edit->cliente = new inputField("C&oacute;digo", "cliente");
		$edit->cliente->size=15;
		
		$edit->rifci = new inputField("R.I.F", "rifci");
		$edit->rifci->size=15;
	
		$edit->nombre = new inputField("Nombre", "nombre");
		
		$edit->contacto = new inputField("Contacto","contacto");
		$edit->contacto->size=35;
		
		$edit->gr_desc = new dropdownField("Grupo", "gr_desc");
		$edit->gr_desc->option("","");
		$edit->gr_desc->options("SELECT grupo,gr_desc FROM grcl where grupo>='' ORDER BY gr_desc  ");

		$edit->dire11 = new inputField("Direcci&oacute;n", "dire11");
		$edit->dire12 = new inputField("", "dire12");
		$edit->dire12->in = "dire11";
		
		$edit->ciud = new inputField("Ciudad", "ciudad1");
		$edit->ciud->size=25;
		//$edit->ciud->option("","");
		//$edit->ciud->options("SELECT ciudad FROM ciud WHERE ciudad>='' ORDER BY ciudad");

		$edit->zona = new dropdownField("Zona", "zona");                                           
		$edit->zona->option("","");                                                                
		$edit->zona->options("SELECT codigo, nombre FROM zona where codigo>='' ORDER BY nombre  ");
		$edit->zona->style="width:200px";
		
		$edit->pais = new inputField("Pa&iacute;s", "pais");
		$edit->pais->size=25;
		
		$edit->email = new inputField("Correo electronico", "email");
	
		$edit->cuenta = new inputField("Cuenta", "cuenta");  
		$edit->cuenta->size=25;
				
		$edit->telefono = new inputField("Tel&eacute;fono1", "telefono");
		$edit->telefono->size=25;
		                                                          		
		$edit->telefon2 = new inputField("Tel&eacute;fono2", "telefon2");
		$edit->telefon2->size=25;
		
		$edit->nit = new inputField("N.I.T", "nit");
		$edit->nit->size=25;	                                                
		
		$edit->tipo = new dropdownField("Tipo", "tipo");            
		$edit->tipo->option("","");                                 
		$edit->tipo->options(array("P"=> "Precio","I"=>"Inactivo"));

		$edit->formap = new inputField("Cr&eacute;dito/Dias", "formap");	
				
		$edit->limite = new inputField("L&iacute;mite", "limite");
		
		$edit->tiva = new dropdownField("Tipo Iva", "tiva");                                                                                                                                                                                                                                                                                                                           
		$edit->tiva->option("","");                                                                                                                                                                                                                                                                                                                                                    
		$edit->tiva->options(array("C"=> "Contribuyente","E"=>"Especial","N"=>"No contribuyente","R"=>"Registro Exento","O"=>"Otro"));                                                                                                                                                                                                                                                 
		$edit->tiva->style="width:200px";
		                                                                                                                                                                                                                                                                                                                                                                               			
		$edit->represen = new inputField("Vendedor", "vendedor");
		$edit->represen->size=25;	

		$edit->vendedor = new inputField("L&iacute;mite", "limite");
		$edit->vendedor->size=25;	
		
		$edit->cobrador = new inputField("Cobrador", "cobrador");
		$edit->cobrador->size=25;	
		
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Clientes</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
		
	}
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('descri1');
		logusu('menu',"CARTA $codigo DESCRIPCION  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('codigo'); 
		$nombre=$do->get('descri1');
		logusu('menu',"CARTA $codigo DESCRIPCION  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo'); 
		$nombre=$do->get('descri1');
		logusu('menu',"CARTA $codigo DESCRIPCION $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM menu WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT descri1 FROM menu WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para la carta $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
/*
Field	Type	Null	Key	Default	Extra
habit	varchar(4)		PRI		
tipo	varchar(6)	YES		NULL	
descrip	varchar(40)	YES		NULL	
piso	varchar(5)	YES		NULL	
telefono	varchar(7)	YES		NULL	
servicio	varchar(40)	YES		NULL	
status	char(1)	YES		NULL	
montoa	decimal(17,2)	YES		0.00	
montob	decimal(17,2)	YES		0.00	
montoc	decimal(17,2)	YES		0.00	
ocupada	char(1)	YES		NULL	
num_fac	varchar(8)	YES		NULL	
dia1	int(11)	YES		NULL	
desc1	decimal(17,2)	YES		0.00	
dia2	int(11)	YES		NULL	
desc2	decimal(17,2)	YES		0.00	
dia3	int(11)	YES		NULL	
desc3	decimal(17,2)	YES		0.00	
dia4	int(11)	YES		NULL	
desc4	decimal(17,2)	YES		0.00	
monto	decimal(17,2)	YES		0.00	
estado	varchar(10)	YES		NULL	
ultima	date	YES		NULL	
quejas	mediumtext	YES		NULL	
*/
?>