<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class directorio extends validaciones {

	function directorio(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->load->library("menues");
		//$this->datasis->modulo_id(131,1);
		$this->load->database();
	}

	function index(){
		redirect("supervisor/directorio/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
		$cmodbus=array(
			'tabla'   =>'scli',
			'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'contacto'=>'Contacto'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cliente'),
			'titulo'  =>'Buscar Cliente');
		
		$pmodbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

    $pboton=$this->datasis->modbus($pmodbus);			
		$cboton=$this->datasis->modbus($cmodbus);

		$filter = new DataFilter("Filtro de Directorio", 'directorio');
		
		$filter->cliente = new inputField("Cliente", "cliente");
		$filter->cliente->size=10;
		$filter->cliente->append($cboton);

		$filter->proveed = new inputField("Proveedor","proveed");
		$filter->proveed->size=10;
		$filter->proveed->append($pboton);
		
		$filter->nombres = new inputField("Nombres","nombres");
		$filter->nombres->size=30;

		$filter->apellidos= new inputField("Apellidos","apellidos");
		$filter->apellidos->size=30;	
				
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('supervisor/directorio/dataedit/show/<#id#>','<#id#>');

		$grid = new DataGrid("Lista de Directorio");
		$grid->order_by("id","asc");
		$grid->per_page=15;
		
		$grid->column("Codigo",$uri);
		$grid->column("Nombres","nombres");
		$grid->column("Apellidos","apellidos");
		$grid->column("Cliente","cliente");
		$grid->column("Proveedor","proveed");
		$grid->column("Empleado","empleado");
		$grid->column("Telefono","telefono1");
		$grid->column("Cargo","cargo");
		
		$grid->add("supervisor/directorio/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Directorio</h1>";
		$data["head"]    = $this->rapyd->get_head();
		
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Agregar", "directorio");
		$edit->back_url = site_url("supervisor/directorio/filteredgrid");
		
		$cmodbus=array(
			'tabla'   =>'scli',
			'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'contacto'=>'Contacto'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cliente'),
			'titulo'  =>'Buscar Cliente');
		
		$pmodbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
			
		$pers=array(
			'tabla'   =>'pers',
			'columnas'=>array(
			'codigo'  =>'Codigo',
			'cedula'  =>'Cedula',
			'nombre'  =>'Nombre',
			'apellido' =>'Apellido'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','cedula'=>'Cedula'),
			'retornar'=>array('codigo'=>'empleado'),
			'titulo'  =>'Buscar Personal');
					  
		$eboton=$this->datasis->modbus($pers);
               $pboton=$this->datasis->modbus($pmodbus);			
		$cboton=$this->datasis->modbus($cmodbus);
	  
		
		$edit->cliente = new inputField("Cliente","cliente");
		$edit->cliente->rule = "trim";
		$edit->cliente->size =30;
		$edit->cliente->maxlength =30;
		$edit->cliente->append($cboton);
		
		$edit->proveed = new inputField("Proveedor","proveed");
		$edit->proveed->rule = "trim";
		$edit->proveed->size =30;
		$edit->proveed->maxlength =30;
		$edit->proveed->append($pboton);
		
		$edit->empleado = new inputField("Empleado","empleado");
		$edit->empleado->rule = "trim";
		$edit->empleado->size =30;
		$edit->empleado->maxlength =30;
		$edit->empleado->append($eboton);
		
                $edit->cedula = new inputField("Cedula", "cedula");
		$edit->cedula->rule = "trim|strtoupper|required|callback_chci";
		$edit->cedula->maxlength =13;
		$edit->cedula->size =18;
			
		$edit->nombres = new inputField("Nombres", "nombres");
		$edit->nombres->rule = "trim|strtoupper|required";
		$edit->nombres->size = 60;
		$edit->nombres->maxlength = 50;
		
		$edit->nombres = new inputField("Nombres", "nombres");
		$edit->nombres->rule = "trim|strtoupper|required";
		$edit->nombres->size = 60;
		$edit->nombres->maxlength = 50;
		
		$edit->apellidos = new inputField("Apellidos","apellidos");
		$edit->apellidos->rule = "trim|strtoupper|required";
		$edit->apellidos->size = 60;
		$edit->apellidos->maxlength = 50;
		
		$edit->edad = new inputField("Edad","edad");
		$edit->edad->rule = "trim";
		$edit->edad->size = 3;
		$edit->edad->maxlength = 2;
		$edit->edad->css_class='inputnum';
		
		$edit->fnacimiento = new DateonlyField("Fecha de Nacimiento","fnacimiento");
		$edit->fnacimiento->rule = "trim";
		$edit->fnacimiento->size = 12;
		
		$edit->sexo = new dropdownField("Sexo","sexo");
		$edit->sexo->option("F", "F");
		$edit->sexo->option("M", "M");
		$edit->sexo->style='width:60px';
			
		$edit->telefono1 = new inputField("Telefono Oficina", "telefono1");
		$edit->telefono1->rule = "trim";
		$edit->telefono1->size = 20;
		$edit->telefono1->maxlength = 20;
		
		$edit->telefono2 = new inputField("Telefono Personal", "telefono2");
		$edit->telefono2->rule = "trim";
		$edit->telefono2->size = 20;                            
		$edit->telefono2->maxlength = 20;                            

		$edit->telefono3 = new inputField("Otro Telefono", "telefono3");
		$edit->telefono3->rule = "trim";
		$edit->telefono3->size = 20;                            
		$edit->telefono3->maxlength = 20; 

 		$edit->dire1 = new textareaField("Direcci&oacute;n Oficina", "direc1");  
 		$edit->dire1->cols = 60;                                   
 		$edit->dire1->rows = 3;                                    
		
		$edit->dire2 = new textareaField("Direcci&oacute;n Habitaci&oacute;n", "direc2");  
 		$edit->dire2->cols = 60;                                   
 		$edit->dire2->rows = 3; 		
		
		$edit->email  = new inputField("E-mail-Hotmail", "email");
		$edit->email->rule = "trim";
		$edit->email->rule = "valid_email";
		$edit->email->size =50;
		$edit->email->maxlength =50;

		$edit->email1  = new inputField("E-mail-Yahoo", "email2");
		$edit->email1->rule = "trim";
		$edit->email1->rule = "valid_email";
		$edit->email1->size =50;
		$edit->email1->maxlength =50;
		
		$edit->email2  = new inputField("E-mail-Otros", "email3");
		$edit->email2->rule = "trim";
		$edit->email2->rule = "valid_email";
		$edit->email2->size =50;
		$edit->email2->maxlength =50;
				                        
		$edit->profesion = new inputField("Profesiòn", "profesion");
		$edit->profesion->rule = "trim|strtoupper";
		$edit->profesion->size = 40;                            
		$edit->profesion->maxlength =30; 
		
		$edit->cargo = new inputField("Cargo", "cargo");
		$edit->cargo->rule = "trim|strtoupper";
		$edit->cargo->size =40;                            
		$edit->cargo->maxlength =30;
		                            
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$smenu['link']=barra_menu('911');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$data['content'] = $edit->output;
		$data['title']   = "<h1>Directorio</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
		
	}	
	function instalar(){
		$mSQL="CREATE TABLE `datasis`.`directorio` (`id` INT AUTO_INCREMENT, `cedula` VARCHAR (13), `cliente` VARCHAR (30), `proveed` VARCHAR (30),`empleado` VARCHAR (30), `nombres` VARCHAR (50), `apellidos` VARCHAR (50), `edad` VARCHAR (2), `sexo` VARCHAR (1), `telefono1` VARCHAR (20), `telefono2` VARCHAR (20), `telefono3` VARCHAR (20),`direc1` VARCHAR (70), `direc2` VARCHAR (70), `profesion` VARCHAR (30), `cargo` VARCHAR (30), `fnacimiento` VARCHAR (20),`email` VARCHAR (50),`email2` VARCHAR (50),`email3` VARCHAR (50), PRIMARY KEY(`id`)) TYPE = MyISAM"; 
		$this->db->simple_query($mSQL);
	}
}
?>