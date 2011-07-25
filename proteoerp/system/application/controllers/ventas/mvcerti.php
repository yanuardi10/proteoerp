<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//crucecuentas
class Mvcerti extends validaciones {
	var $data_type = null;
	var $data = null;
	 
	function mvcerti(){
		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->load->library("rapyd");
		//define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
	}
	function index(){
		$this->datasis->modulo_id(506,1);
		redirect("ventas/mvcerti/filteredgrid");
	}
	
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Certificados Mision Vivienda", 'mvcerti');
		
		$filter->tipo = new inputField("N&uacute;mero", "numero");
		$filter->tipo->size=32;
		
		$filter->cliente = new inputField("Cliente", "cliente");
		$filter->cliente->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/mvcerti/dataedit/show/<#id#>','<#numero#>');

		$grid = new DataGrid("Certificados Mision Vivienda");
		$grid->order_by("id","DESC");
		$grid->per_page = 20;

		$grid->column("Registro",'id');
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Status","status");
		$grid->column("Cliente","cliente");
		$grid->column("Obra","obra");
									
		$grid->add("ventas/mvcerti/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Certificados de Mision Vivienda</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit()
 	{
		$this->rapyd->load("dataedit","dataobject");	
		$link=site_url('ventas/mvcerti/');
		$script ='';
		
		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre', 
		'cirepre'=>'Rif/Cedula',
		'dire11'=>'Direcci&oacute;n'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cliente','nombre'=>'nomcli'),
		'titulo'  =>'Buscar Cliente');
		$boton3 =$this->datasis->modbus($mSCLId,'mSCLId');
		
		$do = new DataObject("mvcerti");
		$do->pointer('scli','scli.cliente = mvcerti.cliente','scli.nombre as nomcli' ,'LEFT');
		
		$edit = new DataEdit("Certificados Mision Vivienda",$do);
		$edit->back_url = site_url("ventas/mvcerti/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		//$lnum='<a href="javascript:ultimo();" title="Consultar ultimo cruce de cuentas ingresado" onclick="">Consultar ultimo cruce de cuentas</a>';	

/*
		$edit->id =   new inputField("Registro", "id");
		$edit->id->mode="autohide";
		$edit->id->size = 10;
		$edit->id->maxlength=10;
		$edit->id->when = array('show', 'modify');
*/
		$edit->numero =   new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 42;
		$edit->numero->maxlength=32;
		$edit->numero->rule="trim|required|callback_chexiste";
		
		$edit->fecha =new DateField("Fecha", "fecha");
		$edit->fecha->size = 12;
		
		$edit->tipo = new dropdownField("Status", "status");
		$edit->tipo->option("A","Activo");
		$edit->tipo->option("C","Cerrador");
		$edit->tipo->style="width:110px";
	
		$edit->cliente =  new inputField("Cliente", "cliente");
		$edit->cliente->db->name="cliente";
		$edit->cliente->rule="trim";
		$edit->cliente->size =12;
		$edit->cliente->readonly=true;
		$edit->cliente->append($boton3);

		$edit->nomcli =   new inputField("Nombre",'nomcli');
		$edit->nomcli->size =42;
		$edit->nomcli->maxlength=40;
		$edit->nomcli->pointer = true;
		$edit->nomcli->readonly = true;

		$edit->obra = new TextareaField("Obra","obra");
		$edit->obra->cols = 50;
		$edit->obra->rows = 4;
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$smenu['link']   = barra_menu('506');
		
		$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['content'] = $edit->output;
		$data['title']   = "<h1>Certificados Mision Vivienda</h1>";
		
		$data["script"]  = script("jquery.pack.js");
		$data['script'] .= script("plugins/jquery.numeric.pack.js");
		$data['script'] .= script("plugins/jquery.floatnumber.js");
		
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('cruc',"CRUCE DE CUENTA $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('cruc',"CRUCE DE CUENTA $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('cruc',"CRUCE DE CUENTA $codigo ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('numero');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM mvcerti WHERE numero='$codigo'");
		if ($chek > 0){
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}

}
?>