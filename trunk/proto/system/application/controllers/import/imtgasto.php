<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Imtgasto extends validaciones {
	var $data_type = null;   
	var $data = null;

	function Imtgasto() {

		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->load->library("rapyd");
    
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/import/". $this->uri->segment(2).EXT);
	}
   
	function index() {
		redirect("import/imtgasto/tabla");
	}
   
	##### DataFilter + DataGrid #####
	function tabla()
	{
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Buscar Gastos de Importaci&oacute;n","importtgas");
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=30;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('import/imtgasto/dataedit/show/<#codigo#>','<#codigo#>');
		$grid = new DataGrid("");
		//$grid->use_function("callback_test");
		$grid->order_by("nombre","asc");
		$grid->per_page = 10;
		//$grid->use_function("substr");

		$grid->column("C&oacute;digo", $uri);
		$grid->column("Nombre","nombre");
		$grid->column("Tipo","tipo");
		$grid->column("I.V.A.","iva");
		$grid->column("Cuenta Contable","cuenta");

		$grid->add("import/imtgasto/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Codigo de Gastos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	

		//$data["crud"] = $filter->output.$grid->output;
		//$data["titulo"] = "CODIGOS DE GASTOS";
		////$this->_session_dump();
		//$content["content"] = $this->load->view('rapyd/crud', $data, true);
		//$content["rapyd_head"] = $this->rapyd->get_head();
		//$content["code"] = '';
		//$content["modulo"] = 'C&oacute;digo de Gastos';
		//// LISTA IZQUIERDA
		//$content["lista"] = "
    //  <div>&lt; <a href='".base_url()."/wiki/'>Ayuda</a></div>
    //  <div class='line'></div>
    //  <h3>data presentation</h3>
    //  <div>".anchor("rapyd/samples/datagrid","DataGrid")."</div>
    //  <div class='line'></div>
    //  <h3>data editing</h3>
    //  <div>".anchor("rapyd/crudsamples/filteredgrid","DataFilter + DataGrid")."</div>
    //  <br />
    //  <div>".anchor("rapyd/supercrud/dataedit/show/1","DataEdit")." + many-to-many</div>
    //  <br />
    //  <div>".anchor("rapyd/crudworkflow/gridedit/osp/0","DataGrid + DataEdit")."</div>
    //  <div class='line'></div>
    //  <h3>Prototype &amp; Ajax</h3>
    //  <div><?=".anchor("rapyd/ajaxsamples/ajaxsearch","DataFilter + Ajax")."></div>
    //  <div class='line'></div>
    //  <a href='#' onclick='window.close()'>Cerrar</a>
    //  <div class='line'></div>\n";	
		//$this->load->view('rapyd/tmpsolo', $content);
  }

	function dataedit()
	{
		$this->rapyd->load("dataedit");

		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';

		$edit = new DataEdit("C&oacute;digo de Gastos", "importtgas");

		$edit->back_url = site_url("import/imtgasto/tabla");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
    
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->rule = "required";
		$edit->codigo->size=7;
		$edit->codigo->maxlength=5;
		$edit->codigo->mode = "autohide";
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->rule = "strtoupper|required";
		$edit->nombre->size = 40;
		$edit->nombre->maxlength =30;
		
		$edit->cuenta = new inputField("Cta. Contable", "cuenta");
		$edit->cuenta->rule= "callback_chcuentac";
		$edit->cuenta->size =20;
		$edit->cuenta->maxlength =15; 
		
		$edit->tipo = new dropdownField("Tipo de Cuenta", "tipo");
		$edit->tipo->option("","");
		$edit->tipo->options(array("N"=>"Gastos Nacionales","E"=>"Gastos Extranjeros" ));
    $edit->tipo->style="width: 170px;";
    
		$edit->iva = new inputField("I.V.A.", "iva", "align='right'");
		$edit->iva->size=8;
		$edit->iva->maxlength=6;
		$edit->iva->css_class='inputnum';
		$edit->iva->rule='numeric';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Gastos</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('importtgas',"GASTO $codigo NOMBRE $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('importtgas',"GASTO $codigo NOMBRE $nombre MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('importtgas',"GASTO $codigo NOMBRE $nombre ELIMINADO");
	}
 function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM importtgas WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM importtgas WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el gasto $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
} 	
?>