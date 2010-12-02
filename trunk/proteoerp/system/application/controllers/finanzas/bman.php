<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//bmanecuentas
class bman extends validaciones {
	 
	function bman(){
		parent::Controller(); 
		$this->load->library("rapyd");
   }
   function index(){
    	//$this->datasis->modulo_id(506,1);
    	redirect("finanzas/bman/filteredgrid");
    }
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$modbus=array(
		'tabla'   =>'tban',
		'columnas'=>array(
		'cod_banc' =>'C&oacute;digo',
		'nomb_banc'=>'Nombre'),
		'filtro'  =>array('cod_banc' =>'C&oacute;digo','nomb_banc'=>'Nombre'),
		'retornar'=>array('cod_banc'=>'codbanc'),
		'titulo'  =>'Buscar Banco');
		
		$boton=$this->datasis->modbus($modbus);	

		$filter = new DataFilter("Filtro de Movimientos en Mansivo", 'bman');
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d"); 
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->codbanc = new inputField("Banco", "codbanc");
		$filter->codbanc->size=10;
		$filter->codbanc->append($boton);
		
		$filter->numero = new inputField("Numero", "numero");
		$filter->numero->size=15;
				
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/bman/dataedit/show/<#id#>','<#codbanc#>');

		$grid = new DataGrid("Lista de Movimientos en Mansivo");
		$grid->order_by("id","desc");
		$grid->per_page = 20;

		$grid->column("Banco",$uri);
		$grid->column("Tipo","tipo");
		$grid->column("Numero","numero");
		$grid->column("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Beneficiario","beneficiario");
		$grid->column("Monto","monto");
									
		$grid->add("finanzas/bman/dataedit/create");
		$grid->build();
		
    $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Movimientos en Mansivo</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		$this->rapyd->load("dataedit");	
		
	 $modbus=array(
		'tabla'   =>'tban',
		'columnas'=>array(
		'cod_banc' =>'C&oacute;digo',
		'nomb_banc'=>'Nombre'),
		'filtro'  =>array('cod_banc' =>'C&oacute;digo','nomb_banc'=>'Nombre'),
		'retornar'=>array('cod_banc'=>'codbanc'),
		'titulo'  =>'Buscar Banco');
		
		$boton=$this->datasis->modbus($modbus);	
	
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
				
		$edit = new DataEdit("Movimientos en Mansivo","bman");
		$edit->back_url = site_url("finanzas/bman/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->codbanc = new inputField("Banco", "codbanc");
		$edit->codbanc->size = 12;
		$edit->codbanc->maxlength=10;
		$edit->codbanc->append($boton);
		$edit->codbanc->rule ="required|callback_chexiste";
		
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
		
		$edit->beneficiario =   new inputField("Beneficiario","beneficiario");
		$edit->beneficiario->size = 50;
		$edit->beneficiario->maxlength=50;
		$edit->beneficiario->rule='trim|required';	
			  
		$edit->monto =   new inputField("Monto","monto");
		$edit->monto->size =20;
		$edit->monto->maxlength=17;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
    $data['content'] = $edit->output;           
    $data['title']   = "<h1>Movimientos en Mansivo</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$numero=$do->get('numero');
		$fecha=$do->get('fecha');	
		logusu('bman',"MOVIMIENTO MASIVO NUMERO $numero FECHA $fecha CREADO");
	}
	function _post_update($do){
		$numero=$do->get('numero');
		$fecha=$do->get('fecha');	
		logusu('bman',"MOVIMIENTO MASIVO NUMERO $numero FECHA $fecha MODIFICADO");
	}
	function _post_delete($do){
		$numero=$do->get('numero');
		$fecha=$do->get('fecha');	
		logusu('bman',"MOVIMIENTO MASIVO NUMERO $numero FECHA $fecha ELIMINADO");
	}
	function instalar(){
		$mSQL="CREATE TABLE `bman` (`id` BIGINT AUTO_INCREMENT, `codbanc` VARCHAR (10), `tipo` VARCHAR (10), `numero` VARCHAR (12), `fecha` DATE, `beneficiario` VARCHAR (50), `monto` DECIMAL (17) , PRIMARY KEY(`id`))";
		$this->db->simple_query($mSQL);
	}
	function chexiste(){
		$codbanc=$this->input->post('codbanc');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM tban WHERE cod_banc='$codbanc'");
		if ($chek > 0){
			return TRUE;
		}else {
			$this->validation->set_message('chexiste',"El $codbanc no existe para ningun banco");
  		return FALSE;
		}	
	}
}
?>