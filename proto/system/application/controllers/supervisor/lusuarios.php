<?php
class Lusuarios extends Controller {

	var $cargo=0;
	
	function Lusuarios() {
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index() {
		$this->load->library('PDFReporte');
		//$this->rapyd->load("datafilter");

//*********************		
$this->rapyd->load("datafilter");
$this->rapyd->load("datatable");


$filter = new DataFilter("Filtro de Usuarios");
$filter->attributes=array('onsubmit'=>'is_loaded()');

$filter->db->select('us_codigo, us_nombre, vendedor, cajero,supervisor');
$filter->db->from('usuario');
$filter->db->orderby('supervisor,us_codigo');

$filter->codigo = new inputField("Nombre de usuario", "us_nombre");

$filter->buttons("search");
$filter->build();

if($this->rapyd->uri->is_set("search")){

	$mSQL=$this->rapyd->db->_compile_select();
	//echo $mSQL;


	$pdf = new PDFReporte($mSQL);
	$pdf->setHeadValores('TITULO1');
	$pdf->setSubHeadValores('TITULO2','TITULO3');
	$pdf->setTitulo("Reporte de Usuarios");
	//$pdf->setSubTitulo($subtitu);
	//$pdf->setSobreTabla($sobretabla);
	$pdf->AddPage();
	$pdf->setTableTitu(8,'Times');

	$pdf->AddCol('us_codigo' ,20 ,'Codigo'  ,'L',8);
	$pdf->AddCol('us_nombre' ,60 ,'Nombre'  ,'L',8);
	$pdf->AddCol('vendedor'  ,20 ,'Verdedor','C',8);
	$pdf->AddCol('cajero'    ,15 ,'Cajero'  ,'C',8);
	$pdf->setGrupoLabel('Supervisor: <#supervisor#>');
	$pdf->setGrupo('supervisor');
	$pdf->Table();
	$pdf->Output();

}else{
	$data["filtro"] = $filter->output;
	$data["titulo"] = '<h2 class="mainheader">Diario General<h2>';
	$data["head"]   = $this->rapyd->get_head();
	$this->load->view('view_freportes', $data);
}
//*********************			

	}
	function consulstatus(){
		return 	$this->cargo;
	}
	
}
?>
