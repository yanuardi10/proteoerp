<?php
class Habita extends Controller {

	function Habita(){
		parent::Controller(); 
		$this->load->library("rapyd");

	}
	
	function index(){
		$this->datasis->modulo_id(811,1);
		redirect("hospitalidad/habita/filteredgrid");
	}
 
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Habitaciones", 'habi');
		
		$filter->habit = new inputField("Habitacion","habit");
		$filter->habit->size=7;
		
		$filter->descrip = new inputField("Descripcion","descrip");
		$filter->descrip->size=40;
				
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('hospitalidad/habita/dataedit/show/<#habit#>', '<#habit#>');

		$grid = new DataGrid("Lista de Habitaciones");
		$grid->order_by("habit","asc");
		$grid->per_page = 10;

		$grid->column("Habitaci&oacute;n", $uri);
		$grid->column("Descripci&oacute;n","descrip","descrip");
		$grid->column("Tipo","tipo");
		$grid->column("Piso","piso");
		$grid->column("Tel&eacute;fono Ext.","telefono");
		$grid->column("Status","status");
		$grid->column("Ocupada","ocupada");
		$grid->column("Estado","estado");
		
		$grid->add("hospitalidad/habita/dataedit/create");
		$grid->build();

    $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Habitaciones</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Habitaci&oacute;n", "habi");
		$edit->back_url = site_url("hospitalidad/habita/filteredgrid");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->habit = new inputField("Habitacion","habit");
		$edit->habit->rule = "trim|required|callback_chexiste";
		$edit->habit->mode="autohide";
		$edit->habit->maxlength =4;
		$edit->habit->size =7;	

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("","");
		$edit->tipo->options("SELECT tipo,descrip FROM thab ORDER BY tipo");
		$edit->tipo->style="width:150px";
	
		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->rule = 'trim';
		$edit->descrip->maxlength =40;
		$edit->descrip->size =50;	
		
		$edit->telefono = new inputField("Tel&eacute;fono Ext.", "telefono");
		$edit->telefono->rule = 'trim';
		$edit->telefono->size=10;
		$edit->telefono->maxlength =7;
			
		$edit->servicio= new inputField("Servicios","servicio");
		$edit->servicio->rule = 'trim';
		$edit->servicio->size=50;
		$edit->servicio->maxlength =40;
				
		$edit->status = new dropdownField("Status", "status");
		$edit->status->option("","");
		$edit->status->options(array("A"=> "Activa","B"=>"Bloqueada","M"=>"Mantenimiento","D"=>"Da&ntilde;ana","S"=>"Salon","C"=>"Comodin"));
		$edit->status->style="width:130px";
		
		$edit->estado = new dropdownField("Estado", "estado");
		$edit->estado->option("","");
		$edit->estado->options(array("S"=> "SUCIA","L"=>"LIMPIA","A"=>"ARREGLO"));
		$edit->estado->style="width:130px";
				
		$edit->ocupada = new dropdownField("Ocupada", "ocupada");
		$edit->ocupada->option("","");
		$edit->ocupada->options(array("S"=> "SI","N"=>"NO"));
		$edit->ocupada->style="width:60px";
		
		$edit->fecha= new dateonlyField("Fecha","ultima");
		$edit->fecha->size=12;
		
		$edit->quejas= new inputField("Quejas","quejas");
		$edit->quejas->rule = "trim";
		$edit->quejas->size=50;

		
		$edit->buttons("modify", "save", "undo", "back","delete");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Habitaciones</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data); 
	}
	function _post_insert($do){
		$codigo=$do->get('habit');
		$nombre=$do->get('descrip');
		logusu('habi',"HABITACION $codigo DESCRIPCION  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('habit');
		$nombre=$do->get('descrip');
		logusu('habi',"HABITACION $codigo DESCRIPCION  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('habit');
		$nombre=$do->get('descrip');
		logusu('habi',"HABITACION $codigo DESCRIPCION $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('habit');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM habi WHERE habit='$codigo'");
		if ($chek > 0){
			$this->validation->set_message('chexiste',"La habitacion $codigo ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>