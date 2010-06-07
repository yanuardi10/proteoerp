<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Rcaj extends validaciones {

	function Rcaj(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->load->library("menues");
		$this->datasis->modulo_id('12A',1);
		$this->load->database();
	}

	function index(){
		redirect("ventas/rcaj/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro de cierres de cajas", 'rcaj');
		
		$filter->fecha = new dateonlyField("Fecha", "fecha");
		$filter->fecha->clause="where";
		$filter->fecha->operator="=";
		$filter->fecha->size =8;
		
		$filter->cajero = new dropdownField("Cajero", "cajero");
		$filter->cajero->option("","Todos");
		$filter->cajero->options("SELECT cajero, nombre FROM scaj ORDER BY nombre");
		
		$filter->buttons("reset","search");
		$filter->build();

		$urih = anchor('formatos/verhtml/RECAJA/<#numero#>','Descargar html');
		$urip = anchor('formatos/ver/RECAJA/<#numero#>'    ,'Descargar pdf');

		$grid = new DataGrid("Lista de Cierres de caja");
		$grid->order_by("fecha","desc");
		$grid->per_page=15;

		$grid->column("Fecha"    ,'<dbdate_to_human><#fecha#></dbdate_to_human>');
		$grid->column("Cajero"   ,"cajero"  ,"align='center'");
		$grid->column("Recibido" ,"recibido","align='right'");
		$grid->column("Ingreso"  ,"ingreso" ,"align='right'");
		//$grid->column("&nbsp;"   ,$urih);
		$grid->column("&nbsp;"   ,$urip);
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Recepci&oacute;n de cajas</h1>";
		$data["head"]    = $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}
}
?>