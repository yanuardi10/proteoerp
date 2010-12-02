<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//cuenpaecuentas
class cuenpa extends validaciones {
	 
	function cuenpa(){
		parent::Controller(); 
		$this->load->library("rapyd");
   }
   function index(){
    	//$this->datasis->modulo_id(506,1);
    	redirect("finanzas/cuenpa/filteredgrid");
    }
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		
		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter("Filtro de Cuentas por Cobrar", 'cuenpa');
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d"); 
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->size=10;
		$filter->proveed->append($boton);
		
		$filter->numero = new inputField("Numero", "numero");
		$filter->numero->size=15;
				
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/cuenpa/dataedit/show/<#id#>','<#proveed#>');

		$grid = new DataGrid("Lista de Movimientos en Mansivo");
		$grid->order_by("id","desc");
		$grid->per_page = 20;

		$grid->column("Proveedor",$uri);
		$grid->column("Tipo","tipo");
		$grid->column("Numero","numero");
		$grid->column("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Vence"   ,"<dbdate_to_human><#vence#></dbdate_to_human>","align='center'");
		$grid->column("Monto","monto");
									
		$grid->add("finanzas/cuenpa/dataedit/create");
		$grid->build();
		
    $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cuentas por Cobrar</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		$this->rapyd->load("dataedit");	
		
		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		
		$boton=$this->datasis->modbus($modbus);
	
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
				
		$edit = new DataEdit("Cuentas por Cobrar","cuenpa");
		$edit->back_url = site_url("finanzas/cuenpa/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->proveed = new inputField("Proveedor","proveed");
		$edit->proveed->size = 12;
		$edit->proveed->maxlength=10;
		$edit->proveed->append($boton);
		$edit->proveed->rule ="required|callback_chexiste";
		
	  $edit->tipo = new dropdownField("Tipo","tipo");
	  $edit->tipo->style='width:60px;';
		$edit->tipo->option("ND","ND" );
		$edit->tipo->option("NC","NC" );
		$edit->tipo->option("CH","CH" );
		$edit->tipo->option("DE","DE" );
		
		$edit->numero =   new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 12;
		$edit->numero->maxlength=12;
		$edit->numero->rule='trim';
		
		$edit->fecha =new DateField("Fecha", "fecha");
		$edit->fecha->size = 12;
		$edit->fecha->rule="required";
		
		$edit->vence =new DateField("Vence", "vence");
		$edit->vence->size = 12;
		$edit->vence->rule="required";
			  
		$edit->monto =   new inputField("Monto","monto");
		$edit->monto->size =20;
		$edit->monto->maxlength=17;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
    $data['content'] = $edit->output;           
    $data['title']   = "<h1>Cuentas por Cobrar</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$numero=$do->get('numero');
		$fecha=$do->get('fecha');	
		logusu('cuenpa',"CUENTA POR PAGAR NUMERO $numero FECHA $fecha CREADO");
	}
	function _post_update($do){
		$numero=$do->get('numero');
		$fecha=$do->get('fecha');	
		logusu('cuenpa',"CUENTA POR PAGAR NUMERO $numero FECHA $fecha MODIFICADO");
	}
	function _post_delete($do){
		$numero=$do->get('numero');
		$fecha=$do->get('fecha');	
		logusu('cuenpa',"CUENTA POR PAGAR NUMERO $numero FECHA $fecha ELIMINADO");
	}
	function instalar(){
		$mSQL="CREATE TABLE `cuenpa` (`id` BIGINT AUTO_INCREMENT, `cliente` VARCHAR (20), `tipo` VARCHAR (10), `numero` VARCHAR (12), `fecha` DATE, `vence` DATE, `monto` DECIMAL (17), PRIMARY KEY(`id`))";
		var_dum($this->db->simple_query($mSQL));
	}
	function chexiste($codigo){
		$proveed=$this->input->post('proveed');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE proveed='$proveed'");
		if ($chek > 0){
			return TRUE;
		}else {
			$this->validation->set_message('chexiste',"El codigo $proveed no existe para ningun proveedor");
  		return FALSE;
		}	
	}
}
?>