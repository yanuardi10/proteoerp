<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//crucecuentas
class ccruc extends validaciones {
	 
	function ccruc(){
		parent::Controller(); 
		$this->load->library("rapyd");
   	}
   	function index(){
    	redirect("finanzas/ccruc/filteredgrid");
   	}
    
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Cruce de Cuentas", 'cruc');
		
		$filter->tipo = new inputField("N&uacute;mero", "numero");
		$filter->tipo->size=15;
		
		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->size=15;
		
		$filter->cliente = new inputField("Cliente", "cliente");
		$filter->cliente->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/ccruc/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid("Lista de Cruce de Cuentas");
		$grid->order_by("numero","asc");
		$grid->per_page = 20;

		$grid->column_orderby("N&uacute;mero",$uri,'numero');
		$grid->column_orderby("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
		$grid->column_orderby("Tipo","tipo",'tipo');
		$grid->column_orderby("Proveedor","proveed",'proveed');
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column_orderby("Cliente","cliente",'cliente');
		$grid->column_orderby("Nombre del Cliente","nomcli",'nomcli');
		$grid->column_orderby("Concepto","concept1",'concep1');
									
		//$grid->add("finanzas/cruc/dataedit/create");
		$grid->build();
		
    	$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cruce de Cuentas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	
	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
		'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
		'titulo'  =>'Buscar Proveedor');
		$boton1=$this->datasis->modbus($modbus,'modbus');
		
		$modbus2=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre', 
		'cirepre'=>'Rif/Cedula',
		'dire11'=>'Direcci&oacute;n'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'proveed2','nombre'=>'nombre'),
		'titulo'  =>'Buscar Cliente');
		$boton2=$this->datasis->modbus($modbus2,'modbus2');
		
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
		
		$mSCLId2=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
		'retornar'=>array('proveed'=>'cliente2','nombre'=>'nomcli'),
		'titulo'  =>'Buscar Proveedor');
		$boton4 =$this->datasis->modbus($mSCLId2,'mSCLId2'); 

		$do = new DataObject("cruc");
		$do->rel_one_to_many('itcruc', 'itcruc', 'numero');
		
		$edit = new DataDetails('Cruce de cuentas', $do);
		$edit->back_url = site_url('finanzas/ccruc/filteredgrid');
		$edit->set_rel_title('itcruc','Cuentas <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("C-C","Clientes");
		$edit->tipo->option("C-P","Cliente  - Proveedor");
		$edit->tipo->option("P-C","Proveedor - Cliente");
		$edit->tipo->option("P-P","Proveedores");
		$edit->tipo->style="width:185px";
		$edit->tipo->group="Datos de cruce";

		$edit->proveed =  new inputField("Proveedor", "proveed");
		$edit->proveed->db->name="proveed";
		$edit->proveed->size =12;		
		$edit->proveed->rule="trim";
		$edit->proveed->readonly=true;
		$edit->proveed->append($boton1);
		
		$edit->nombre =   new inputField("Nombre", "nombre");
		$edit->nombre->rule="trim";
		$edit->nombre->size =25;
		$edit->nombre->maxlength=40;
		
		$edit->saldoa =   new inputField("Saldo Anterior", "saldoa");
		$edit->saldoa->size=25;
		$edit->saldoa->maxlength=16;
		$edit->saldoa->css_class='inputnum';
		$edit->saldoa->rule='trim|numeric';
		
		$edit->cliente =  new inputField("Cliente", "cliente");
		$edit->cliente->db->name="cliente";
		$edit->cliente->rule="trim";
		$edit->cliente->size =12;
		$edit->cliente->readonly=true;
		$edit->cliente->append($boton3);
		
		$edit->nomcli =   new inputField("Nombre", "nomcli");		
		$edit->nomcli->rule="trim";
	  	$edit->nomcli->size =25;
	  	$edit->nomcli->maxlength=40;
	  
		$edit->saldod =   new inputField("Saldo Deudor", "saldod");
		$edit->saldod->size =25;
		$edit->saldod->maxlength=16;
		$edit->saldod->css_class='inputnum';
		$edit->saldod->rule='trim|numeric';
		
		$edit->codbanc =  new dropdownField("C&oacute;digo de banco", "codbanc");		
		$edit->codbanc->options("select codbanc,banco from banc order by codbanc");
		$edit->codbanc->style="width:185px";
		
		$edit->monto =    new inputField("Monto","monto");
		$edit->monto->size =25;
		$edit->monto->maxlength= 16;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric'; 
		
		$edit->concept1 = new inputField("Concepto","concept1");
		$edit->concept1->size =41;
		$edit->concept1->maxlength=40;
		$edit->concept1->rule="trim";
		
		$edit->concept2 = new inputField(".","concept2");
		$edit->concept2->rule="trim";
		$edit->concept2->size =41;
		$edit->concept2->maxlength=40;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->ittipo = new inputField('Tipo <#o#>', 'ittipo_<#i#>');
		$edit->ittipo->size     = 12;
		$edit->ittipo->db_name  = 'tipo';
		$edit->ittipo->readonly = true;
		$edit->ittipo->rel_id   = 'itcruc';
		$edit->ittipo->rule     = 'required';
		
		$edit->onumero = new inputField('O.Numero <#o#>', 'onumero_<#i#>');
		$edit->onumero->size=36;
		$edit->onumero->db_name='onumero';
		$edit->onumero->maxlength=50;
		$edit->onumero->readonly  = true;
		$edit->onumero->rel_id='itcruc';
		
		$edit->ofecha = new DateonlyField('Fecha<#o#>', 'ofecha_<#i#>','d/m/Y');
		$edit->ofecha->db_name='ofecha';
		$edit->ofecha->insertValue = date('Y-m-d');
		$edit->ofecha->rule = 'required';
		$edit->ofecha->mode = 'autohide';
		$edit->ofecha->size = 10;
		$edit->ofecha->rel_id='itcruc';
		
		$edit->oregist = new inputField('O.regist <#o#>', 'oregist_<#i#>');
		$edit->oregist->size=36;
		$edit->oregist->db_name='oregist';
		$edit->oregist->maxlength=50;
		$edit->oregist->readonly  = true;
		$edit->oregist->rel_id='itcruc';
		
		$edit->itmonto = new inputField('Monto <#o#>', 'itmonto_<#i#>');
		$edit->itmonto->size=36;
		$edit->itmonto->db_name='monto';
		$edit->itmonto->maxlength=50;
		$edit->itmonto->readonly  = true;
		$edit->itmonto->rel_id='itcruc';
		
		//**************************
		//fin de campos para detalle
		//**************************
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_cruc', $conten,true);
		$data['title']   = heading('Cruce de Cuentas');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}
	
	function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('cruc');
		$do->set('numero',$numero);
		return false;
	}

	function _pre_update($do){
		return false;
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
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM cruc WHERE numero='$codigo'");
		if ($chek > 0){
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>