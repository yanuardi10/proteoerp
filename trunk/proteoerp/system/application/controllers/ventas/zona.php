<?php
//zonas
class Zona extends Controller {
	
	var $data_type = null;
	var $data = null;

	function Zona()
	{
		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->datasis->modulo_id(137,1);
		$this->load->library("rapyd");

	}

	function index()
	{
		redirect("ventas/zona/filteredgrid");
	}

		function test($id,$const)
	{
		return $id*$const;
	}
		function filteredgrid()
	{
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por Zonas", 'zona');
		
		$filter->codigo = new inputField("Codigo", "codigo");
		$filter->codigo->size=15;
			
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=25;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/zona/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Zonas");
		$grid->order_by("nombre","asc");
		$grid->per_page = 7;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre","nombre");
		$grid->column("Descripci&oacute;n","descrip");

		$grid->add("ventas/zona/dataedit/create");
		$grid->build();

	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Zonas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
    }

	function dataedit()
	{ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Zona", "zona");
		$edit->back_url = site_url("ventas/zona/filteredgrid");
		
		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
    
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size=10;
		$edit->codigo->rule= "trim|required|callback_chexiste";
		$edit->codigo->maxlength=8;
		$edit->codigo->mode = "autohide";
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size=38;
		$edit->nombre->rule="trim|required|strtoupper";
		$edit->nombre->maxlength=30;
		
		$edit->descrip = new textareafield("Descripci&oacute;n", "descrip");
		$edit->descrip->cols=70;
		$edit->descrip->rows=4;
		$edit->descrip->rule="trim";
	  $edit->descrip->maxlength=90;
	  
		$edit->buttons("modify", "save", "undo", "delete", "back");
  	$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Zonas</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('zona',"ZONA $codigo NOMBRE $nombre CREADA");
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('zona',"ZONA $codigo NOMBRE $nombre MODIFICADA");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('zona',"ZONA $codigo NOMBRE $nombre ELIMINADA");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM zona WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM zona WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para la zona de $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}	
}
?>