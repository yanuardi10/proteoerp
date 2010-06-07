<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Reserva extends validaciones{
	function Reserva(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
 
	function index(){
		$this->datasis->modulo_id(801,1);
		redirect("hospitalidad/reserva/filteredgrid");
	}

	function filteredgrid(){

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Reservaciones", 'hres');
		
		$filter->fecha_in = new dateonlyField("Fecha", "fecha_in");	
		$filter->fecha_in->size=15;		
		
		$filter->cedula = new inputField("C&eacute;dula", "cedula");
		$filter->cedula->size=15;

		$filter->nombre = new inputField("Nombre ", "nombre");
		$filter->nombre->db_name="CONCAT_WS('',nombre,apellido)";
		$filter->nombre->size=35;

		$filter->nom_cli = new inputField("Agencia", "nom_cli");
		$filter->nom_cli->size=35;

		$filter->habit= new inputField("Habitaci&oacute;n", "habit");
		$filter->habit->size=4;

		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('hospitalidad/reserva/dataedit/show/<#localiza#>','<#localiza#>');
		
		$grid = new DataGrid("Lista de Reservaciones");
		$grid->use_function('dbdate_to_human');
		$grid->order_by("habit","asc");
		$grid->per_page = 10;

		$grid->column("Localizador", $uri);
		$grid->column("Inicio ","<dbdate_to_human><#fecha_in#></dbdate_to_human>","align='center'");
		$grid->column("Salida" ,"<dbdate_to_human><#fecha_ou#></dbdate_to_human>","align='center'");
		$grid->column("C&eacute;dula","cedula");
		$grid->column("Nombres", "nombre");
		$grid->column("Apellidos", "apellido");
		$grid->column("Nacionalidad","nacional","align='center'");
		$grid->column("Tel&eacute;fono","telefono");
		$grid->column("Habitaci&oacute;n","habit","align='center'");
		$grid->column("Agencia","nom_cli");
		
		
		$grid->add("hospitalidad/reserva/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Reservaciones</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
		
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Datos de la Reservaci&oacute;n", "hres");
		$edit->back_url = site_url("hospitalidad/reserva/filteredgrid");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->codigo = new inputField("Localizador", "localiza");
		$edit->codigo->size=11;
		$edit->codigo->maxlength=8;
		$edit->codigo->rule ="trim|required|callback_chexiste";
		$edit->codigo->mode="autohide";
		
		$edit->fecha_in = new dateonlyField("Fecha Inicio", "fecha_in");
		$edit->fecha_in->size=12;		
		$edit->fecha_in->insertValue = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-30, date("Y")));
		$edit->fecha_in->rule = "required";
		
		$edit->fecha_ou = new dateonlyField("Fecha Salida", "fecha_ou");
		$edit->fecha_ou->size=12;
		//$edit->noches = new inputField("Noches", "noches");
		$edit->fecha_ou->rule = "required";
		
		$edit->vence = new dateonlyField("Vence","vence");
		$edit->vence->size=12;
		
		$edit->habit = new inputField("Habitaci&oacute;n", "habit");
		$edit->habit->size=6;
		$edit->habit->maxlength=4;
		$edit->habit->rule = "trim|required";
		
		$edit->noches = new inputField("Noches","dias");
		$edit->noches->maxlength =3;
		$edit->noches->size =5;
		$edit->noches->rule ="trim";
		
		$edit->cedula = new inputField("Cedula","cedula");
		$edit->cedula->rule = "trim|strtoupper|callback_chci";
		$edit->cedula->maxlength =15;
		$edit->cedula->size =17;	
		
		$edit->nombres = new inputField("Nombres", "nombre");
		$edit->nombres->size=30;
		$edit->nombres->maxlength=20;
		$edit->nombres->rule="trim";
				
		$edit->apellidos = new inputField("Apeliidos", "nombre");
		$edit->apellidos->size=30;
		$edit->apellidos->maxlength=20;
		$edit->apellidos->rule="trim";
		
		$edit->nacional = new dropdownField("Nacionalidad", "nacional");
		$edit->nacional->option("","");
		$edit->nacional->options("SELECT codigo, pais FROM nacion where pais>'' ORDER BY codigo ");
		$edit->nacional->style="width:180px";
		
		$edit->telefono = new inputField("Tel&eacute;fono", "telefono");
		$edit->telefono->size=23;
		$edit->telefono->maxlength=20;
		$edit->telefono->rule="trim";
		
		$edit->preceden = new inputField("Procedencia","proceden");
		$edit->preceden->size=23;     
		$edit->preceden->maxlength=20;
		$edit->preceden->rule="trim";
		
		$edit->nom_cli = new inputField("Agencia", "nom_cli");
		$edit->nom_cli->size=40;     
		$edit->nom_cli->maxlength=30;
		$edit->nom_cli->rule="trim";

		$edit->confirma= new dropdownField("Confirma","confirma");
		$edit->confirma->option("S","S");
		$edit->confirma->option("N","N");
		$edit->confirma->style="width:60px";
		
		$edit->observa1 =new inputField("Observaci&oacute;n","observa1");
		$edit->observa1->size=65;     
		$edit->observa1->maxlength=55;
		$edit->observa1->rule="trim";

		$edit->observa2 =new inputField("","observa2");
		$edit->observa2->size=65;     
		$edit->observa2->maxlength=55;
		$edit->observa2->rule="trim";
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Reservaciones</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('localiza');
		$habit=$do->get('habit');
		logusu('hres',"RESERVACION $codigo HABITACION  $habit CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('localiza');
		$habit=$do->get('habit');
		logusu('hres',"RESERVACION $codigo HABITACION  $habit  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('localiza');
		$habit=$do->get('habit');
		logusu('hres',"RESERVACION $codigo HABITACION  $habit  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('localiza');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM hres WHERE localiza='$codigo'");
		if ($chek > 0){
			$this->validation->set_message('chexiste',"La reservacion con el localizador  $codigo ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>