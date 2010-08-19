<?php
class Invgasto extends Controller {
    var $data_type = null;
    var $data = null;

    function Invgasto()
    {
		parent::Controller(); 

		//required helpers for samples
		$this->load->helper('url');
		$this->load->helper('text');

		$this->load->library("rapyd");

		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/finanzas/". $this->uri->segment(2).EXT);
    }
    function index(){
    	$this->datasis->modulo_id(501,1);
		redirect("finanzas/invgasto/filteredgrid");
    }
  	function filteredgrid()
	  {
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Inventario de Gastos", 'mgas');
	
		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=15;
	
		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->size=35;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/invgasto/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Inventario de Gastos");
		$grid->order_by("descrip","asc");
		$grid->per_page = 7;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","descrip","descrip");
		$grid->column("Tipo","tipo");
		$grid->column("Grupo","grupo");
		$grid->column("Nombre","nom_grup");

		$grid->add("finanzas/invgasto/dataedit/create");
		$grid->build();

		$data["crud"] = $filter->output . $grid->output;

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Inventario de Gastos</h1>";
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
		
		$edit = new DataEdit("Inventario de Gastos", "mgas");
		$edit->back_url = site_url("finanzas/invgasto/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

    $edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->rule = "trim|required|callback_chexiste";
		$edit->codigo->mode="autohide";
		$edit->codigo->size =8;
		$edit->codigo->maxlength=6;
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("G","Gasto");
		$edit->tipo->option("I","Inventario");
		$edit->tipo->option("S","Suministro");
		$edit->tipo->option("A","Activo");
		$edit->tipo->style="width:150px";
				
		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size =50;
		$edit->descrip->maxlength=40;
		$edit->descrip->rule="trim|strtoupper";
		
		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->option("","");
		$edit->grupo->options("SELECT grupo, nom_grup FROM grga ORDER BY nom_grup");
		$edit->grupo->style="width:230px";

 		$edit->nom_grup = new inputField("Nombre del grupo", "nom_grup");
 		$edit->nom_grup->size =30;
		$edit->nom_grup->maxlength=20;
 		$edit->nom_grup->rule='trim';
 		
		$edit->iva = new inputField("I.V.A", "iva");
    $edit->iva->size=7;		
		$edit->iva->maxlength=5;
		$edit->iva->css_class='inputnum';
		$edit->iva->rule='trim|numeric';
		
		$edit->medida = new inputField("Medida", "medida");
    $edit->medida->size=7;
		$edit->medida->maxlength=5;
		$edit->medida->rule='trim';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Inventario de Gastos</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('descrip');
		logusu('mgas',"INVENTARIO DE GASTOS $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('descrip');
		logusu('mgas',"INVENTARIO DE GASTOS $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('descrip');
		logusu('mgas',"INVENTARIO DE GASTOS $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM mgas WHERE codigo='$codigo'");
		if ($chek > 0){
			$gasto=$this->datasis->dameval("SELECT descrip FROM mgas WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el gasto $gasto");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
 }       
?>