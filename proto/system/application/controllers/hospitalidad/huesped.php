<?php
class Huesped extends Controller {

	function Huesped(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/hotel/". $this->uri->segment(2).EXT);
	}

	function index(){
		$this->datasis->modulo_id(808,1);
		redirect("hospitalidad/huesped/filteredgrid");
 	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Huespedes", 'hchk');
		
		$filter->cedula = new inputField("C&eacute;dula", "cedula");
		$filter->cedula->size=15;
		
		$filter->nombres = new inputField("Nombres", "nombre");
		$filter->nombres->size=30;
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('hospitalidad/huesped/dataedit/show/<#cedula#>','<#cedula#>');
		
		$grid = new DataGrid("Lista de Huespedes");
		$grid->order_by("cedula","asc");
		$grid->per_page = 10;
		
		$grid->column("C&eacute;dula", $uri);
		$grid->column("Nombres","nombre","nombre");
		$grid->column("Apellidos","apellido");
		$grid->column("Nacionalidad","nacional","align='center'");
		$grid->column("Edad","edad");
		$grid->column("Tel&eacute;fono","telefono");
		$grid->column("Estado Civil","civil","align='center'");
		
		$grid->add("hospitalidad/huesped/dataedit/create");
		$grid->build();
		
	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Huespedes</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Datos del Huesped", "hchk");
		$edit->back_url = site_url("hospitalidad/huesped/filteredgrid");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
				
		$edit->cedula = new inputField("Cedula","cedula");
		$edit->cedula->rule = "trim|strtoupper|required|callback_chrif|callback_chexiste";
		$edit->cedula->mode="autohide";
		$edit->cedula->maxlength =15;
		$edit->cedula->size =17;	
		
		$edit->nombres = new inputField("Nombres", "nombre");
		$edit->nombres->rule = "trim";
		$edit->nombres->size=30;
		$edit->nombres->maxlength=20;
				
		$edit->apellidos = new inputField("Apeliidos", "apellido");
		$edit->apellidos->rule = "trim";
		$edit->apellidos->size=30;
		$edit->apellidos->maxlength=20;
		
		$edit->dire1 = new inputField("Direcci&oacute;n", "dire1");
		$edit->dire1->rule = "trim";
		$edit->dire1->size=50;
		$edit->dire1->maxlength=40;
		
		$edit->telefono = new inputField("Tel&eacute;fono", "telefono");
		$edit->telefono->rule = "trim";
		$edit->telefono->size=23;
		$edit->telefono->maxlength=20;
				
		$edit->edad = new inputField("Edad", "edad");
		$edit->edad->rule = "trim";
		$edit->edad->size=4;
		$edit->edad->maxlength=2;
		
		$edit->visa = new dropdownField("Tipo de Visa", "visa");
		$edit->visa->option("","");
		$edit->visa->options(array("T"=> "Turista","R"=>"Residente","O"=>"Otros"));
		$edit->visa->style="width:100px";
		
		$edit->civil = new dropdownField("Estado Civil", "civil");
		$edit->civil->option("","");
		$edit->civil->options(array("S"=> "SOLTERO(A)","C"=>"CASADO(A)","D"=>"DIVORCIADO(A)","V"=>"VIUDO(A)"));
		$edit->civil->style="width:130px";
		
		$edit->profesion = new inputField("Profesi&oacute;n", "profesion");
		$edit->profesion->rule = "trim";
		$edit->profesion->size=30;     
		$edit->profesion->maxlength=20;
		
		$edit->email = new inputField("Correo Electronico", "email");
		$edit->email->size=40;     
		$edit->email->maxlength=40;
		$edit->email->rule = "valid_email|trim";

		$edit->nacional = new dropdownField("Nacionalidad", "nacional");
		$edit->nacional->option("","");
		$edit->nacional->options("SELECT codigo, pais FROM nacion where pais>'' ORDER BY codigo ");
		$edit->nacional->style="width:180px";
		
		$edit->buttons("modify", "save", "undo", "back","delete");		
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Huespedes</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('cedula');
		$nombre=$do->get('nombre');
		logusu('hchk',"HUESPED $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('cedula');
		$nombre=$do->get('nombre');
		logusu('hchk',"HUESPED $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('cedula');
		$nombre=$do->get('nombre');
		logusu('hchk',"HUESPED $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('cedula');
		//if (ereg("(^[VEJG][0-9]+$)|(^[P][A-Z0-9]+$)", $codigo)){
			$chek=$this->datasis->dameval("SELECT COUNT(*) FROM hchk WHERE cedula='$codigo'");
			if ($chek > 0){
				$nombre=$this->datasis->dameval("SELECT nombre FROM hchk WHERE cedula='$codigo'");
				$this->validation->set_message('chexiste',"La cedula $codigo ya existe para el huesped $nombre");
				return FALSE;
			}else {
  			return TRUE;
			}
		//}else{
		//	$this->validation->set_message('chexiste', "Debe introducir rif o cedula con el siguiente formato V=Venezolano(a), E=Extranjero(a), G=Gobierno, P=Pasaporte o J=Juridico Como primer caracter seguido del n&uacute;mero de documento. Ej: V123456, J5555555, P56H454");
		//	return FALSE;
		//	
		//}
	}
}
?>