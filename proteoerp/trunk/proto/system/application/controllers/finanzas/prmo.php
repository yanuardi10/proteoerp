<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Prmo extends validaciones {
	function prmo(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(206,1);
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		//define ("THISFILE",   APPPATH."controllers/finanzas/". $this->uri->segment(2).EXT);
	}
	function index(){
		redirect("finanzas/prmo/filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
		$filter = new DataFilter("Filtro de Otros Movimientos de Caja y Bancos", "prmo");
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->codban = new dropdownField("Caja/Banco", "codban");
		$filter->codban->option("","");
		$filter->codban->options("SELECT codbanc, banco FROM bmov ORDER BY banco ");
		
		$filter->banco = new dropdownField("Tipo", "tipo");
		$filter->banco->option("","");
		$filter->banco->option("1","Prestamo Otorgado");		
		$filter->banco->option("2","Prestamo Recibido");
		$filter->banco->option("3","Cheque Devuelto Cliente");
		$filter->banco->option("4","Cheque Devuelto Proveedor");
		$filter->banco->option("5","Deposito por Analizar");
		$filter->banco->option("6","Cargos Indevidos por el Banco");
		$filter->banco->option("7","Todos");
		
		$filter->clipro = new inputField("Cli/Prv", "monto");
		$filter->clipro->size=12;
		
		$filter->monto = new inputField("Monto", "monto");
		$filter->monto->size=12;
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('finanzas/prmo/dataedit/show/<#numero#>','<#numero#>');
		
		$grid = new DataGrid("Lista de Otros Movimientos de Caja y Bancos");
		$grid->order_by("numero","asc");
		$grid->per_page = 10;
		
		$grid->column("Numero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Banco","banco");
		$grid->column("Cli/Prv","clipro");
		$grid->column("Monto","monto","align='right'");
		
		$grid->add("finanzas/prmo/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Otros Movimientos de Caja y Bancos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}