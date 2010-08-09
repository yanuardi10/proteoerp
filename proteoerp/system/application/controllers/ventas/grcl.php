<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//grupocli
class Grcl extends validaciones {

	var $data_type = null;
	var $data = null;

	function grcl()
	{
		parent::Controller(); 

		$this->load->helper('url');
		$this->load->helper('text');
		$this->datasis->modulo_id(132,1);
		$this->load->library("rapyd");

		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/ventas/". $this->uri->segment(2).EXT);
	}

	function index()
	{
		redirect("ventas/grcl/filteredgrid");
	}
	function test($id,$const)
	{
		return $id*$const;
	}
	function filteredgrid()
	{

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Buscar", 'grcl');
		
		$filter->grupo = new inputField("Grupo", "grupo");
		$filter->grupo->size=10;
		
		$filter->gr_desc = new inputField("Nombre","gr_desc");
		$filter->gr_desc->size=45;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/grcl/dataedit/show/<#grupo#>','<#grupo#>');

		$grid = new DataGrid("Lista de Grupos de Clientes");
		$grid->order_by("gr_desc","asc");
		$grid->per_page = 7;
		$grid->column_orderby("Grupo",$uri,'grupo');
		$grid->column_orderby("Nombre","gr_desc","gr_desc");
		$grid->column_orderby("Clase","clase",'clase');
		$grid->column_orderby("Cuenta","cuenta",'cuenta');
		$grid->add("ventas/grcl/dataedit/create",'Agregar nuevo grupo');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Grupos de Clientes</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
    }
	function dataedit()
	{ 
		$this->rapyd->load("dataedit");
		
		$qformato=$this->qformato=$this->datasis->formato_cpla();

		$mCPLA=array(
		'tabla'   =>'cpla',
		'columnas'=>array(
		'codigo' =>'C&oacute;digo',
		'descrip'=>'Descripci&oacute;n'),
		'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
		'retornar'=>array('codigo'=>'cuenta'),
		'titulo'  =>'Buscar Cuenta',
		'where'=>"codigo LIKE \"$qformato\"",
		);
				
		$bcpla =$this->datasis->modbus($mCPLA);
				
		$edit = new DataEdit("Grupo de clientes", "grcl");
		$edit->back_url = site_url("ventas/grcl/filteredgrid");
		
		$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process("update",'_post_update');
		$edit->post_process("delete",'_post_delete');
		
		$edit->grupo = new inputField("Grupo", "grupo");
		$edit->grupo->mode ="autohide";
		$edit->grupo->rule ="trim|required|max_length[4]|callback_chexiste";
		$edit->grupo->size =5;
		$edit->grupo->maxlength =4;
		
		$edit->clase = new dropdownField("Clase", "clase");
		$edit->clase->option("","");
		$edit->clase->options(array("C"=> "Cliente","O"=>"Otros","I"=>"Internos"));
		$edit->clase->rule= "required";
		$edit->clase->style='width:100px;';
		
		$edit->gr_desc = new inputField("Descripci&oacute;n", "gr_desc");
		$edit->gr_desc->size =30;
		$edit->gr_desc->maxlength =25;
		$edit->gr_desc->rule= "required|strtoupper";
		
		$edit->cuenta = new inputField("Cta. Contable", "cuenta");
		$edit->cuenta->rule= "callback_chcuentac";
		$edit->cuenta->size =20;
		$edit->cuenta->maxlength =15;
		$edit->cuenta->append($bcpla);
				
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
	
	  $data['content'] = $edit->output;           
    $data['title']   = "<h1>Grupos de Clientes</h1>";        
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
		$codigo=$do->get('grupo');
		logusu('grcl',"GRUPO $codigo CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('grupo');
		$limite=$do->get('limite');
		logusu('grcl',"GRUPO $codigo MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('grupo');
		$limite=$do->get('limite');
		logusu('grcl',"GRUPO $codigo ELIMINADO");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('grupo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM grcl WHERE grupo='$codigo'");
		if ($chek > 0){
			$grupo=$this->datasis->dameval("SELECT gr_desc FROM grcl WHERE grupo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el grupo $grupo");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>