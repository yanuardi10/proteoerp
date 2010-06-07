<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//crucecuentas
class Cruc extends validaciones {
	var $data_type = null;
	var $data = null;
	 
	function cruc(){
		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
   }
   function index(){
    	$this->datasis->modulo_id(506,1);
    	redirect("finanzas/cruc/filteredgrid");
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

		$uri = anchor('finanzas/cruc/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid("Lista de Cruce de Cuentas");
		$grid->order_by("numero","asc");
		$grid->per_page = 20;

		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Tipo","tipo");
		$grid->column("Proveedor","proveed");
		$grid->column("Nombre","nombre");
		$grid->column("Cliente","cliente");
		$grid->column("Nombre del Cliente","nomcli");
		$grid->column("Concepto","concept1");
									
		$grid->add("finanzas/cruc/dataedit/create");
		$grid->build();
		
    $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cruce de Cuentas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		//proveed .value="";
		//proveed2.value="";
		//nombre  .value="";
		//saldoa  .value="";
		//cliente .value="";
		//cliente2.value="";
		//nomcli  .value="";
		//saldod  .value="";
 		
		$this->rapyd->load("dataedit");		
		$link=site_url('finanzas/cruc/ucruce');
		$script ='
		function sellupa(){			
			$("#tr_proveed ").hide();
			$("#tr_proveed2").hide();		
			$("#tr_cliente ").hide();
			$("#tr_cliente2").hide();
			
			vtipo=$("#tipo").val();
			
			if(vtipo.length>0){
				if(vtipo=="C-C"){					
					$("#tr_proveed2").show();
					$("#tr_cliente ").show();
				}                   
				if(vtipo=="C-P"){   
					$("#tr_proveed2").show();
					$("#tr_cliente2").show();
				}                   
				if(vtipo=="P-C"){   
					$("#tr_proveed ").show();
					$("#tr_cliente ").show();
				}                   
				if(vtipo=="P-P"){   
					$("#tr_proveed ").show();
					$("#tr_cliente2").show();
				}               				
			}else{

			}
		}
		
		function ultimo(){						
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo numero ingresado fue: " + msg );
				}
			});			
		}
		$(function() {
			$(".inputnum").numeric(".");
			$("#tipo").change(function () { sellupa(); }).change();	
		}		
		);
		';
		
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
		
		$edit = new DataEdit("Cruce de Cuentas","cruc");
		$edit->back_url = site_url("finanzas/cruc/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$lnum='<a href="javascript:ultimo();" title="Consultar ultimo cruce de cuentas ingresado" onclick="">Consultar ultimo cruce de cuentas</a>';	
		$edit->numero =   new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size = 12;
		$edit->numero->maxlength=8;
		$edit->numero->rule="trim|required|callback_chexiste";
		$edit->numero->append($lnum);
		$edit->numero->group="Datos de cruce";
		
		$edit->fecha =new DateField("Fecha", "fecha");
		$edit->fecha->size = 12;
		$edit->fecha->group="Datos de cruce";
		
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
		$edit->proveed->group="cuenta 1";
				
		$edit->proveed2 =  new inputField("Cliente", "proveed2");
		$edit->proveed2->db->name="proveed";
		$edit->proveed2->size =12;		
		$edit->proveed2->rule="trim";
		$edit->proveed2->readonly=true;
		$edit->proveed2->append($boton2);
		$edit->proveed2->group="cuenta 1";
		
		$edit->nombre =   new inputField("Nombre", "nombre");
		$edit->nombre->rule="trim";
		$edit->nombre->size =25;
		$edit->nombre->maxlength=40;
		$edit->nombre->group="cuenta 1";
		
		$edit->saldoa =   new inputField("Saldo Anterior", "saldoa");
		$edit->saldoa->size=25;
		$edit->saldoa->maxlength=16;
		$edit->saldoa->css_class='inputnum';
		$edit->saldoa->rule='trim|numeric';
		$edit->saldoa->group="cuenta 1";
		
		$edit->cliente =  new inputField("Cliente", "cliente");
		$edit->cliente->db->name="cliente";
		$edit->cliente->rule="trim";
		$edit->cliente->size =12;
		$edit->cliente->readonly=true;
		$edit->cliente->append($boton3);
		$edit->cliente->group="cuenta 2";
		
		$edit->cliente2 =  new inputField("Proveedor", "cliente2");
		$edit->cliente2->db->name="cliente";
		$edit->cliente2->rule="trim";
		$edit->cliente2->size =12;
		$edit->cliente2->readonly=true;
		$edit->cliente2->append($boton4);
		$edit->cliente2->group="cuenta 2";
		
		$edit->nomcli =   new inputField("Nombre", "nomcli");
		//$edit->nomcli->db->name="nomcli";		
		$edit->nomcli->rule="trim";
	  $edit->nomcli->size =25;
	  $edit->nomcli->maxlength=40;
	  $edit->nomcli->group="cuenta 2";
	  
		$edit->saldod =   new inputField("Saldo Deudor", "saldod");
		$edit->saldod->size =25;
		$edit->saldod->maxlength=16;
		$edit->saldod->css_class='inputnum';
		$edit->saldod->rule='trim|numeric';
		$edit->saldod->group="cuenta 2";
		
		$edit->codbanc =  new dropdownField("C&oacute;digo de banco", "codbanc");		
		$edit->codbanc->options("select codbanc,banco from banc order by codbanc");
		$edit->codbanc->style="width:185px";
		$edit->codbanc->group="Datos de banco";
		
		$edit->monto =    new inputField("Monto","monto");
		$edit->monto->size =25;
		$edit->monto->maxlength= 16;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric'; 
		$edit->monto->group="Datos de banco";
		
		$edit->concept1 = new inputField("Concepto","concept1");
		$edit->concept1->size =41;
		$edit->concept1->maxlength=40;
		$edit->concept1->rule="trim";
		$edit->concept1->group="Datos de banco";
		
		$edit->concept2 = new inputField(".","concept2");
		$edit->concept2->rule="trim";
		$edit->concept2->size =41;
		$edit->concept2->maxlength=40;
		$edit->concept2->group="Datos de banco";
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$smenu['link']=barra_menu('506');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
    $data['content'] = $edit->output;           
    $data['title']   = "<h1>Cruce de Cuentas</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
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
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM cruc WHERE numero='$codigo'");
		if ($chek > 0){
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	function ucruce(){
		$consulcruce=$this->datasis->dameval("SELECT numero FROM cruc ORDER BY numero DESC");
		echo $consulcruce;
	}
}
?>