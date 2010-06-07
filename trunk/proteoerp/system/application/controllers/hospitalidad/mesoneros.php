<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class mesoneros extends validaciones {
	
	function mesoneros(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(816,1);
	}
	
	function index()
	{
		redirect("hospitalidad/mesoneros/filteredgrid");
	}

 	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
    
		$filter = new DataFilter("Filtro de Mesoneros","meso");

		$filter->mesonero = new inputField("Mesonero","mesonero");
		$filter->mesonero->size=4;
	
		$filter->cedula = new inputField("Cedula","cedula");
		$filter->cedula->size=10;
		
		$filter->nombre = new inputField("Nombre","nombre");
		$filter->nombre->size=25;		
   
   	$filter->buttons("reset","search");
		$filter->build();
   
    $uri = anchor('hospitalidad/mesoneros/dataedit/show/<#mesonero#>','<#mesonero#>');

		$grid = new DataGrid("Lista de Mesoneros");
		$grid->order_by("mesonero","asc");                          
		$grid->per_page = 15;

		$grid->column("Mesonero",$uri );
		$grid->column("Cedula","cedula");
		$grid->column("Nombre","nombre");
		$grid->column("Ingreso","<dbdate_to_human><#ingreso#></dbdate_to_human>","align='center'");
		$grid->column("Direcci&oacute;n","direc1");
		$grid->column("Sueldo","sueldo","align=right");
		$grid->column("Puntos","puntos","align=center");
		$grid->column("Usuario","usuario");
          		
		$grid->add("hospitalidad/mesoneros/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Mesoneros</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit()
 	{
		$this->rapyd->load("dataedit");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		$modbus=array(
		'tabla'   =>'usuario',
		'columnas'=>array(
		'us_codigo' =>'C&oacute;digo',
		'us_nombre'=>'Nombre'),
		'filtro'  =>array('us_codigo' =>'C&oacute;digo','us_nombre'=>'Nombre'),
		//'retornar'=>array('codigo'=>'codigo<#i#>','precio1'=>'precio1<#i#>','precio2'=>'precio2<#i#>','precio3'=>'precio3<#i#>','precio4'=>'precio4<#i#>','iva'=>'iva<#i#>','pond'=>'costo<#i#>'),
		'retornar'=>array('us_codigo'=>'usuario<#i#>'),
		'p_uri'=>array(4=>'<#i#>'),
		'titulo'  =>'Buscar Usuario');
			
		$boton=$this->datasis->modbus($modbus);
				
		$edit = new DataEdit("Mesonero", "meso");
		$edit->back_url = site_url("hospitalidad/mesoneros/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->mesonero= new inputField("Mesonero","mesonero");
		$edit->mesonero->size = 7;
		$edit->mesonero->maxlength=5;
    $edit->mesonero->mode="autohide";
    $edit->mesonero->rule = "trim|required|callback_chexiste";
    
		$edit->nombre= new inputField("Nombre","nombre");
		$edit->nombre->size =45;
		$edit->nombre->rule= "trim|required|strtoupper";
		$edit->nombre->maxlength=40;
		
		$edit->cedula= new inputField("Cedula","cedula");
		$edit->cedula->size = 15;
		$edit->cedula->maxlength=12;
		$edit->cedula->rule = "strtoupper|callback_chci|trim";
		
		$edit->direc1= new inputField("Direcci&oacute;n","direc1");
		$edit->direc1->rule = "trim";
		$edit->direc1->size =50;
		$edit->direc1->maxlength=40;
		
		$edit->direc2= new inputField("","direc2");
		$edit->direc2->rule = "trim";
		$edit->direc2->size =50;
		$edit->direc2->maxlength=40;
		
		$edit->ingreso= new dateonlyField("Fecha de Ingreso","ingreso");
		$edit->ingreso->size = 12;
		
		$edit->cargo= new inputField("Cargo","cargo");
		$edit->cargo->rule = "trim";
		$edit->cargo->size =40;
		$edit->cargo->maxlength=30;
		
		$edit->sueldo= new inputField("Sueldo Base","sueldo");
		$edit->sueldo->size = 15;
		$edit->sueldo->css_class='inputnum';
		$edit->sueldo->rule='trim|numeric';

		$edit->puntos= new inputField("Puntos por Servicio","puntos");
		$edit->puntos->size =15;
		$edit->puntos->maxlength=11;
		$edit->puntos->css_class='inputnum';
		$edit->puntos->rule='integer|trim';
		
		$edit->usuario= new inputField("Usuario","usuario");
		$edit->usuario->size =15;
		$edit->usuario->maxlength=12;
		$edit->usuario->append($boton);
		$edit->usuario->rule='trim';
		
		$edit->buttons("modify", "save", "undo", "back","delete");
		$edit->build();
		
    $data['content'] = $edit->output;           
    $data['title']   = "<h1>Mesoneros</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}  
  function _post_insert($do){
		$codigo=$do->get('mesonero');
		$nombre=$do->get('nombre');
		logusu('meso',"MESONERO $codigo  NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('mesonero');
		$nombre=$do->get('nombre');
		logusu('meso',"MESONERO $codigo  NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('mesonero');
		$nombre=$do->get('nombre');
		logusu('meso',"MESONERO $codigo  NOMBRE  $nombre ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('mesonero');
		//echo 'numero'.$numero.'codigo'.$codigo.'tipo'.$tipo_doc;
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM meso WHERE mesonero='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM meso WHERE mesonero='$codigo'");
			$this->validation->set_message('chexiste',"Mesonero $codigo  ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>