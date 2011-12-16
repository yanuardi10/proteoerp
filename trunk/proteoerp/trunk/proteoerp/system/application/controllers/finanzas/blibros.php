<?php
class BLibros extends Controller {

	function BLibros() {
		parent::Controller();
		$this->load->library("rapyd");         
	}

	function index() {
	
	redirect('finanzas/blibros/filteredgrid');
	}

	function filteredgrid($mes='', $anio='') {
		$this->rapyd->load("datagrid","datafilter");
		
		$filter = new DataFilter("Filtro de Libros","siva");
	  
		$uri = anchor('ventas/cliente/dataedit/show/<#clipro#>','<#clipro#>');
    
		$grid = new DataGrid();
		$grid->order_by("fecha","desc");
		$grid->per_page = 15;  
		
		//$grid->column("Id",$uri);
    $grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Nº Caja","caja");
    $grid->column("Tipo Doc.","tipo","align=center");
    $grid->column("Fuente","fuente","align=center");
    $grid->column("Sucursal","sucursal");
    $grid->column("Contribuyente N&uacute;mero","numero","align='center'");
    $grid->column("Numhasta","numhasta");
    $grid->column("Codigo",$uri);
    $grid->column("Nombre o Razon social","nombre");
    $grid->column("Rif","rif");
    $grid->column("Total Ventas","<number_format><#gtotal#>|2</number_format>","align=right");
    $grid->column("Ventas Exentas","<number_format><#exento#>|2</number_format>","align=right");
    $grid->column("Alicuota General 9.00% Base","<number_format><#stotal#>|2</number_format>","align=right");
    $grid->column("Impuesto","<number_format><#impuesto#>|2</number_format>","align=right");
    $grid->column("Base","<number_format><#general#>|2</number_format>","align=right");
    $grid->column("Impuesto","<number_format><#geneimpu#>|2</number_format>","align=right");
    $grid->column("Base","<number_format><#adicional#>|2</number_format>","align=right");
    $grid->column("Impuesto","<number_format><#adicimpu#>|2</number_format>","align=right");
   	$grid->column("Retenido I.V.A","<number_format><#reiva#>|2</number_format>","align=right");
  	$grid->column("N&uacute;mero de Comp.","comprobante");
  	$grid->column("Fecha de Recepci&oacute;n","<dbdate_to_human><#fecharece#></dbdate_to_human>","align='center'");

		$grid->build();
		
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		//$data['title']   ='<h1>Libros</h1>';
		$this->load->view('view_ventanas_sola', $data);
	}
}
?>