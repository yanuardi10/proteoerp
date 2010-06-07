<?php  require_once(BASEPATH.'application/controllers/validaciones.php');
//vende
class Vend extends validaciones {

	var $data_type = null;
	var $data = null;

	function Vend()
	{
		parent::Controller(); 

		$this->load->helper('url');
		$this->load->helper('text');
		$this->load->library("rapyd");
	}
	function index()
	{
		redirect("ventas/vend/filteredgrid");
	}
	function test($id,$const)
	{
		return $id*$const;
	}

	function filteredgrid()
	{
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de  Vendedores Y Cobradores", 'vend');
		
		$filter->vendedor = new inputField("Codigo","vendedor");
		$filter->vendedor->size=5;
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=25;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/vend/dataedit/show/<#vendedor#>','<#vendedor#>');

		$grid = new DataGrid("Lista de Vendedores y Cobradores");
		$grid->order_by("nombre","asc");
		$grid->per_page = 10;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre","nombre");
		$grid->column("Tipo","tipo");
		$grid->column("Direcci&oacute;n","direc1");
		$grid->column("Tel&eacute;fono","telefono");

		$grid->add("ventas/vend/dataedit/create");
		$grid->build();

  	$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Vendedores y Cobradores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
    }

	function dataedit()
	{ 
		$this->rapyd->load("dataedit");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';

		$edit = new DataEdit("Vendedores y Cobradores", "vend");
		$edit->back_url = site_url("ventas/vend/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process("update",'_post_update');
		$edit->post_process("delete",'_post_delete');
		
		$edit->vendedor = new inputField("C&oacute;digo", "vendedor");
		$edit->vendedor->size=5;
		$edit->vendedor->maxlength=5;
		$edit->vendedor->rule = "trim|required|callback_chexiste";
		$edit->vendedor->mode ="autohide";
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("","");
		$edit->tipo->options(array("V"=> "Vendedor","C"=>"Cobrador", "A"=>"Ambos","I"=>"Inactivo"));
    $edit->tipo->style="width:120px";
		$edit->tipo->rule = "required";
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->rule = "trim|strtoupper|required";
		$edit->nombre->size=35;
    $edit->nombre->maxlength=30;
		
		$edit->direc1 = new inputField("Direcci&oacute;n", "direc1");
		$edit->direc1->size=40;
		$edit->direc1->rule="trim";
    $edit->direc1->maxlength=35;
    
		$edit->direc2 = new inputField(" ", "direc2");
		$edit->direc2->size=40;
		$edit->direc2->rule="trim";
    $edit->direc2->maxlength=35;
		
		$edit->telefono = new inputField("Tel&eacute;fono", "telefono");
    $edit->telefono->size=16;
    $edit->telefono->maxlength=13;
    $edit->telefono->rule = "trim|required";
    
		$edit->almacen = new dropdownField("Almacen", "almacen");
		$edit->almacen->option("","");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");
    $edit->almacen->style="width:150px";
		
		$edit->clave = new inputField("Clave", "clave");
		$edit->clave->size=7;
		$edit->clave->rule="trim";
		$edit->clave->maxlength=5;
		$edit->clave->type="password";
		
		$edit->comive  = new inputField("Com. de Venta %", "comive");
		$edit->comive->size=7;
		$edit->comive->maxlength=5;
		$edit->comive->css_class='inputnum';
		$edit->comive->rule='trim|numeric';
		
		$edit->comicob = new inputField("Com. Cobranza %", "comicob");
    $edit->comicob->size=7;
    $edit->comicob->maxlength=5;
		$edit->comicob->css_class='inputnum';
		$edit->comicob->rule='trim|numeric';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;
    $data['title']   = "<h1>Vendedores y Cobradores</h1>";
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _pre_del($do) {
		$codigo=$do->get('vendedor');
		$chek = $this->datasis->dameval("SELECT count(*) FROM sfac WHERE vd='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Vendedor relacionado con una o mas facturas no puede ser eliminado';
			return False;
		}else	{
			return True;
  	}
	}
	function _post_insert($do){
		$codigo=$do->get('vendedor');
		$tipo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('vend',"CODIGO $codigo NOMBRE $nombre TIPO $tipo CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('vendedor');
		$tipo=$do->get('tipo');
		$nombre=$do->get('nombre');
		logusu('vend',"CODIGO $codigo NOMBRE $nombre TIPO $tipo MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('vendedor');
		$nombre=$do->get('nombre');
		$tipo=$do->get('tipo');
		logusu('vend',"CODIGO $codigo NOMBRE $nombre TIPO $tipo ELIMINADO");
		
	}
	function chexiste($codigo){
		$codigo=$this->input->post('vendedor');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM vend WHERE vendedor='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM vend WHERE vendedor='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el vendedor $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>