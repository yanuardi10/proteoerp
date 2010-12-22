<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Efisico extends Controller{
	
	function Efisico(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		redirect("supermercado/efisico/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro inventario fisico", 'maesfisico');
		$filter->db->select(array("TRIM(codigo) codigo","ubica","cantidad","fecha","usuario"));
			
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechad->clause  ="where";
		$filter->fechad->db_name ="fecha";
		$filter->fechad->insertValue = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-30, date("Y")));
		$filter->fechad->operator=">=";
 		$filter->fechad->group='Fecha';
		
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechah->clause="where";
		$filter->fechah->db_name="fecha";
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechah->operator="<=";
		$filter->fechah->group='Fecha';
		
		$filter->codigo = new inputField("C&oacute;digo","codigo");
		$filter->codigo->size=10;
			
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('supermercado/efisico/dataedit/show/<#id#>','<#codigo#>');

		$grid = new DataGrid("Lista de productos inventariados");
		//$grid->order_by("serial","asc");
		$grid->per_page=15;
		
		$grid->column("Codigo",$uri);
		$grid->column("Ubicaci&oacute;n","ubica");
		$grid->column("Cantidad","cantidad");
		$grid->column("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Usuario","usuario");		
		$grid->add("supermercado/efisico/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Inventario F&iacute;sico Express</h1>";
		$data["head"]    = $this->rapyd->get_head();	
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
	
		$this->rapyd->load("dataedit");
		
		$modbus=array(
			'tabla'   =>'maes',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'Descripci&oacute;n',
			'precio1'=>'Precio 1',
			'precio2'=>'Precio 2',
			'precio3'=>'Precio 3',
			'precio4'=>'Precio 4',
			'precio5'=>'Precio 5'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar en inventario');
		$boton=$this->datasis->modbus($modbus);
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';
		
		$edit = new DataEdit("Inventario Express","maesfisico");
		$edit->pre_process( 'insert','_pre_insert');
		$edit->back_url = site_url("supermercado/efisico/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
    
		$edit->id = new inputField("ID", "id");
		$edit->id->mode = "autohide";
		$edit->id->when =array('show');
    
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size = 20;
		$edit->codigo->append($boton);
		$edit->codigo->mode = "autohide"; 
		$edit->codigo->maxlength =15;
		$edit->codigo->rule="trim|required";

		$edit->ubica = new dropdownField("Almacen", "ubica");  
		$edit->ubica->option("","Seleccionar");
		$edit->ubica->options("SELECT ubica,CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ");
		$edit->ubica->rule="trim|required";

		$edit->locali = new inputField("Localizaci&oacute;n","locali");
		$edit->locali->size =9;
		$edit->locali->maxlength =5;
		$edit->locali->rule="trim";

		$edit->cantidad = new inputField("Cantidad","cantidad");
		$edit->cantidad->size = 15;
		$edit->cantidad->css_class='inputnum';
		$edit->cantidad->maxlength =11;
		$edit->cantidad->rule="numeric|callback_ccana";

		$edit->fraccion = new inputField("Fracci&oacute;n","fraccion");
		$edit->fraccion->size = 15;
		$edit->fraccion->css_class='inputnum';
		$edit->fraccion->maxlength =11;
		$edit->fraccion->rule="numeric|callback_ccana";
		
		$edit->fecha = new DateonlyField("Fecha","fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->rule="required";
		$edit->fecha->size = 12;
		
		//$edit->hora = new inputField("Hora","hora");
		//$edit->hora->size =8;
		//$edit->hora->rule='callback_chhora|trim';
		//$edit->hora->append('hh:mm:ss');
		//$edit->hora->rule="trim";
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output; 		
		$data['title']   = "<h1>Inventario Express</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function ccana($cana){
		if($cana >= 0) return TRUE;
		$this->validation->set_message('ccana', "El campo %s debe tener una cantidad positiva");
		return FALSE;	
	}

	function _pre_insert($do){
		$numero=$this->datasis->prox_numero('ninvfis');
		$numero=str_pad($numero,8, "0", STR_PAD_LEFT);
		$fraccion=$do->get('fraccion');
		$cantidad=$do->get('cantidad');
		if(empty($fraccion)) $do->set('fraccion', 0);
		if(empty($cantidad)) $do->set('cantidad', 0);
		
    $do->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->db->set('estampa', 'CURDATE()', FALSE);
		$do->set('usuario', $this->session->userdata('usuario'));
		$do->set('numero', $numero);
	}
	
	function instalar(){
	    $mSQL="ALTER TABLE `maesfisico` ADD COLUMN `id` BIGINT NOT NULL AUTO_INCREMENT  FIRST , ADD PRIMARY KEY (`id`) ";
	    $this->db->simple_query($mSQL);
	}
}
?>