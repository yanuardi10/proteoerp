<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//cuencoecuentas
class cuenco extends validaciones {
	 
	function cuenco(){
		parent::Controller(); 
		$this->load->library("rapyd");
   }
   function index(){
    	//$this->datasis->modulo_id(506,1);
    	redirect("finanzas/cuenco/filteredgrid");
    }
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
    $scli=array(
	  'tabla'   =>'scli',
	  'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'contacto'=>'Contacto'),
	  'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
	  'retornar'=>array('cliente'=>'cliente'),
	  'titulo'  =>'Buscar Cliente');
		
		$boton=$this->datasis->modbus($scli);

		$filter = new DataFilter("Filtro de Cuentas por Cobrar", 'cuenco');
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d"); 
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->cliente = new inputField("Cliente", "cliente");
		$filter->cliente->size=10;
		$filter->cliente->append($boton);
		
		$filter->numero = new inputField("Numero", "numero");
		$filter->numero->size=15;
				
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/cuenco/dataedit/show/<#cliente#>','<#cliente#>');

		$grid = new DataGrid("Lista de Cuentas por Cobrar");
		$grid->order_by("id","desc");
		$grid->per_page = 20;

		$grid->column("Cliente",$uri);
		$grid->column("Tipo","tipo");
		$grid->column("Numero","numero");
		$grid->column("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Vence"   ,"<dbdate_to_human><#vence#></dbdate_to_human>","align='center'");
		$grid->column("Monto","monto");
									
		$grid->add("finanzas/cuenco/dataedit/create");
		$grid->build();
		
    $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cuentas por Cobrar</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		$this->rapyd->load("dataedit");	
		
	  $scli=array(
	  'tabla'   =>'scli',
	  'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'contacto'=>'Contacto'),
	  'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
	  'retornar'=>array('cliente'=>'cliente'),
	  'titulo'  =>'Buscar Cliente');
		
		$boton=$this->datasis->modbus($scli);
	
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
				
		$edit = new DataEdit("Cuentas por Cobrar","cuenco");
		$edit->back_url = site_url("finanzas/cuenco/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->cliente = new inputField("Cliente", "cliente");
		$edit->cliente->size = 12;
		$edit->cliente->maxlength=10;
		$edit->cliente->append($boton);
		$edit->cliente->rule ="required|callback_chexiste";
		
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
		logusu('cuenco',"CUENTA POR COBRAR NUMERO $numero FECHA $fecha CREADO");
	}
	function _post_update($do){
		$numero=$do->get('numero');
		$fecha=$do->get('fecha');	
		logusu('cuenco',"CUENTA POR COBRAR NUMERO $numero FECHA $fecha MODIFICADO");
	}
	function _post_delete($do){
		$numero=$do->get('numero');
		$fecha=$do->get('fecha');	
		logusu('cuenco',"CUENTA POR COBRAR NUMERO $numero FECHA $fecha ELIMINADO");
	}
	function instalar(){
		$mSQL="CREATE TABLE `cuenco` (`id` BIGINT AUTO_INCREMENT, `cliente` VARCHAR (20), `tipo` VARCHAR (10), `numero` VARCHAR (12), `fecha` DATE, `vence` DATE, `monto` DECIMAL (17), PRIMARY KEY(`id`))";
		var_dum($this->db->simple_query($mSQL));
	}
	function chexiste(){
		$cliente=$this->input->post('cliente');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM scli WHERE cliente='$cliente'");
		if ($chek > 0){
			return TRUE;
		}else {
			$this->validation->set_message('chexiste',"El codigo $cliente no existe para ningun cliente");
  		return FALSE;
		}	
	}
}
?>