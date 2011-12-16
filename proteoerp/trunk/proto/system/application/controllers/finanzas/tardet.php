<?php
class tardet extends Controller {

    function tardet() {
      parent::Controller();
      $this->load->library('rapyd');
   }
   
   function index() {
      $this->datasis->modulo_id(512,1);
    	redirect("finanzas/tardet/filteredgrid");
   }
  
   function filteredgrid() {
		
		$this->rapyd->load("datafilter","datagrid");
		$filter = new DataFilter("Filtro de Tardet", "tardet");
		
		$filter->concepto = new inputField("Concepto", "concepto");
		$filter->concepto->size=20;
		
		$filter->descripcion = new inputField("Descripci&oacute;n", "descrip");                    
    $filter->descripcion->size=35;                      
 	
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('finanzas/tardet/dataedit/show/<#concepto#>/<#tarjeta#>','<#concepto#>');

		$grid = new DataGrid("Lista de Tardet");
		$grid->per_page = 10;
		
		$grid->column("Concepto",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Tarjeta","tarjeta");
		
		$grid->add("finanzas/tardet/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Tardet</h1>";
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
		
		$edit = new DataEdit("filtro", "tardet");
		$edit->back_url = site_url("finanzas/tardet/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->concepto = new inputField("Concepto", "concepto");
		$edit->concepto->rule = "required|callback_chexiste";
		$edit->concepto->mode="autohide";
		$edit->concepto->size =5;
		$edit->concepto->maxlength=3;
		
		$edit->descripcion = new inputField("Descripci&oacute;n","descrip");
		$edit->descripcion->size =30;
		$edit->descripcion->maxlength=20;
		$edit->descripcion->rule = "required|strtoupper";
		
		$edit->tarjeta = new dropdownField("Tarjeta","tarjeta");
		$edit->tarjeta->options("SELECT tipo, nombre FROM tarjeta");
		$edit->tarjeta->style="width:180px";
		$edit->tarjeta->rule = "required";
		
		$edit->pdcoform = new inputField("Pdcoform","pdcoform");
		$edit->pdcoform->size =14;
		$edit->pdcoform->maxlength=11; 
		$edit->pdcoform->css_class='inputnum';
		$edit->pdcoform->rule='integer';
		     		
		$edit->pdmoncje = new inputField("Pdmoncje","pdmoncje");
		$edit->pdmoncje->size =14;
		$edit->pdmoncje->maxlength=11;
		$edit->pdmoncje->css_class='inputnum';
		$edit->pdmoncje->rule='integer';
			
		$edit->pdcancje = new inputField("Pdcancje","pdcancje");
		$edit->pdcancje->size =14;
		$edit->pdcancje->maxlength=11;
		$edit->pdcancje->css_class='inputnum';
		$edit->pdcancje->rule='integer';    
		
		$edit->inicial = new inputField("Inicial","inicial");
		$edit->inicial->size = 20;
		$edit->inicial->maxlength=17;
		$edit->inicial->css_class='inputnum';
		$edit->inicial->rule='numeric';
		
		$edit->saldo = new inputField("Saldo","saldo");
		$edit->saldo->size = 20;
    $edit->saldo->maxlength=17;
    $edit->saldo->css_class='inputnum';
		$edit->saldo->rule='numeric';
    
    $edit->enlace = new inputField("Enlace","enlace");
		$edit->enlace->size =7;
		$edit->enlace->maxlength=5;
		
		$edit->fechaini = new DateonlyField("Fecha","fechaini");
		$edit->fechaini->size = 12;
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Variaciones de la Forma de Pago</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('tardet',"CODIGO $codigo $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('tardet',"CODIGO $codigo $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('tardet',"CODIGO $codigo $nombre  ELIMINADO");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('concepto');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM tardet WHERE concepto='$codigo'");
		if ($chek > 0){
			$tardet=$this->datasis->dameval("SELECT descrip FROM tardet WHERE concepto='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para  $tardet");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>