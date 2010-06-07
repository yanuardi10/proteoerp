<?php
class Lsfacdesp extends Controller {
	
	function Lsfacdesp() {
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index() {
		$this->load->library('PDFReporte');
		$this->rapyd->load("datafilter");

$filter = new DataFilter("Filtro");
$select=array("b.fecha","b.tipo_doc","b.numero","b.cod_cli","b.nombre","b.totalg","IF(c.factura IS NULL,if(b.fdespacha=0 OR b.fdespacha IS NULL, 'Pendiente', 'Entregada' ),'Parcial') despacha","if(b.referen='C','Credito','Contado') referen","b.fdespacha");
$filter->db->select($select);
$filter->db->from("sfac b");
$filter->db->join("snot c" ,"b.numero=c.factura","LEFT");
$filter->db->where('b.tipo_doc',"F");
$filter->db->where('referen <> ',"P");
$filter->db->orderby("b.numero");

if(!isset($_POST['bfe'])) $_POST['bfe']='b.fecha';

$filter->fechad = new dateField("Desde", "fechad",'d/m/Y');
$filter->fechah = new dateField("Hasta", "fechah",'d/m/Y');
$filter->fechad->clause  =$filter->fechah->clause="where";
$filter->fechad->db_name =$filter->fechah->db_name=$_POST['bfe'];
$filter->fechad->insertValue = date("Y-m-d");
//,mktime(0, 0, 0, date("m"), date("d")-30,   date("Y")));
$filter->fechah->insertValue = date("Y-m-d");  
$filter->fechad->operator=">=";
$filter->fechah->operator="<=";

$filter->tipo = new dropdownField("Tipo de Despacho", "tipo");
$filter->tipo->db_name="IF(c.factura IS NULL, if(b.fdespacha=0 OR b.fdespacha IS NULL, 1, 2 ),3)";
$filter->tipo->clause="where";
$filter->tipo->operator="=";
$filter->tipo->option("","");
$filter->tipo->options(array('1'=>'Pendiente','2'=>'Entregada','3'=>'Parcial'));

$filter->referen = new dropdownField("Forma de pago", "referen");
$filter->referen->clause="where";
$filter->referen->operator="=";
$filter->referen->option("","");
$filter->referen->options(array('C'=>'Cr&eacute;dito','E'=>'Contado'));

$filter->buscaen = new dropdownField("Buscar por", "bfe");
$filter->buscaen->clause="";
$filter->buscaen->option("b.fecha"    ,"Fecha Emisi&oacute;n");
$filter->buscaen->option("b.fdespacha","Fecha Despacho");

$filter->buttons("reset","search");     
$filter->build();

if($this->rapyd->uri->is_set("search")){
	$mSQL=$this->rapyd->db->_compile_select();
	//echo $mSQL;
	
	$pdf = new PDFReporte($mSQL);
	$pdf->setHeadValores('TITULO1');
	$pdf->setSubHeadValores('TITULO2','TITULO3');
	$pdf->setTitulo("Facturas despachadas del ".$_POST['fechad'].' Hasta '.$_POST['fechah']);
	$pdf->AddPage();
	$pdf->setTableTitu(8,'Times');
  
	$pdf->AddCol('fecha'   ,13 ,'Fecha'   ,'L',6);
	$pdf->AddCol('numero'  ,12 ,'Numero'  ,'C',6);
	$pdf->AddCol('cod_cli' ,10 ,'Cliente' ,'L',6);
	$pdf->AddCol('nombre'  ,70 ,'Nombre'  ,'L',6);
	$pdf->AddCol('totalg'  ,16 ,'Total'   ,'R',6);
	$pdf->AddCol('referen' ,14 ,'Tipo'    ,'L',6);
	$pdf->AddCol('despacha',14 ,'Despacho','L',6);
	$pdf->AddCol('fdespacha',14 ,'Fecha Desp.','L',6);
	
	$pdf->setTotalizar('totalg');
	$pdf->Table();
	$pdf->Output();
			
}else{
	$data["filtro"] = $filter->output;
	$data["titulo"] = '<h2 class="mainheader">Estatus de despacho en Facturas<h2>';
	$data["head"]   = $this->rapyd->get_head();
	$this->load->view('view_freportes', $data);
}
	}
}
?>
