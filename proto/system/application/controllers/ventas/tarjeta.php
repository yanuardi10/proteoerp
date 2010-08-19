<?php  require_once(BASEPATH.'application/controllers/validaciones.php');
//Formapa
class Tarjeta extends validaciones {
	function tarjeta(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(133,1);
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/ventas/". $this->uri->segment(2).EXT);
	}
	function index(){
		redirect("ventas/tarjeta/filteredgrid");
	}
	function filteredgrid(){
	
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Buscar", 'tarjeta');
		
		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->options("SELECT tipo, nombre from tarjeta ORDER BY tipo");
    $filter->tipo->style="width:180px";
  
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/tarjeta/dataedit/show/<#tipo#>','<#tipo#>');

		$grid = new DataGrid("Lista de Formas de Pago");
		$grid->order_by("nombre","asc");
		$grid->per_page = 10;
		
		$grid->column("Tipo",$uri);
		$grid->column("Nombre","nombre","nombre");
		$grid->column("Comisi&oacute;n","comision");
		$grid->column("Impuesto","impuesto");
		$grid->column("Mensaje","mensaje");
		
		$grid->add("ventas/tarjeta/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Formas de Pago</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		$edit = new DataEdit("Formas de Pago", "tarjeta");
		$edit->back_url = site_url("ventas/tarjeta/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process("update",'_post_update');
		$edit->post_process("delete",'_post_delete');
		
		$edit->tipo = new inputField("Tipo", "tipo");
		$edit->tipo->maxlength=2;
		$edit->tipo->size=3;
		$edit->tipo->mode="autohide";
		$edit->tipo->rule= "strtoupper|required|callback_chexiste";
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->maxlength=20;
		$edit->nombre->size=25;
		$edit->nombre->rule = "strtoupper|required";
		
		$edit->comision = new inputField("Comisi&oacute;n", "comision");	
		$edit->comision->maxlength=8;
		$edit->comision->size=10;
		$edit->comision->css_class='inputnum';
		$edit->comision->rule='numeric';
		
		$edit->impuesto = new inputField("Impuesto", "impuesto");
		$edit->impuesto->maxlength=8;
		$edit->impuesto->size=10;
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->rule='numeric';		
		
		$edit->mensaje  = new inputField("Mensaje",  "mensaje");
		$edit->mensaje->maxlength=60;
		$edit->mensaje->size=65;
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Formas de Pago</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _pre_del($do) {
		$grupo=$do->get('grupo');
		$chek = $this->datasis->dameval("SELECT count(*) FROM scli WHERE grupo='$grupo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}else	{
			return True;
  	}
	}
	function _post_insert($do){
		$codigo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('tarjeta',"FORMA DE PAGO $codigo NOMBRE $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('tarjeta',"FORMA DE PAGO $codigo NOMBRE $nombre MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('tarjeta',"FORMA DE PAGO $codigo NOMBRE $nombre ELIMINADO");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('tipo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM tarjeta WHERE tipo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM tarjeta WHERE tipo='$codigo'");
			$this->validation->set_message('chexiste',"El tipo $codigo ya existe para la forma de pago $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>