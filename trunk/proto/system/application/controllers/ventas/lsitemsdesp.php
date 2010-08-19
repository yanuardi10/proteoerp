<?php
class Lsitemsdesp extends Controller {
	
	function Lsitemsdesp() {
		parent::Controller();
		$this->load->library("rapyd");
	}
	function index() {
		$this->load->library('PDFReporte');
		$this->rapyd->load("datafilter");

//*********************		
/*
SELECT fecha, numa, codigoa, desca, cana FROM sitems
WHERE fecha='2007-11-22 00:00:00' AND numa IN (
SELECT b.numero 
FROM sfac b LEFT JOIN snot c ON b.numero=c.factura 
WHERE b.tipo_doc='F' and c.factura IS NULL AND b.fecha = '2007-11-22 00:00:00' )


SELECT fecha, numa, codigoa, desca, sum(cana) FROM sitems
WHERE fecha='2007-11-22 00:00:00' AND numa IN (
SELECT b.numero 
FROM sfac b LEFT JOIN snot c ON b.numero=c.factura 
WHERE b.tipo_doc='F' and c.factura IS NULL AND b.fecha = '2007-11-22 00:00:00' ) GROUP BY codigoa
*/
$filter = new DataFilter("Filtro");
$filter->db->select('b.numero');
$filter->db->from("sfac b");
$filter->db->join("snot c" ,"b.numero=c.factura","LEFT");
$filter->db->where('b.tipo_doc','F');
$filter->db->where('c.factura IS NULL');
//$filter->db->orderby("b.numero");

$filter->fechad = new dateField("Desde", "fechad",'d/m/Y');
$filter->fechah = new dateField("Hasta", "fechah",'d/m/Y');
$filter->fechad->clause  =$filter->fechah->clause="where";
$filter->fechad->db_name =$filter->fechah->db_name='b.fecha';
$filter->fechad->insertValue = date("Y-m-d");
$filter->fechah->insertValue = date("Y-m-d");  
$filter->fechad->operator=">=";
$filter->fechah->operator="<=";


$filter->agrupar = new dropdownField("Agrupar por", "agrupar");
$filter->agrupar->clause="";
$filter->agrupar->option('numa'   ,'N&uacute;mero de Factura');
$filter->agrupar->option('codigoa','C&oacute;digo del Producto');

$filter->buttons("reset","search");     
$filter->build();

if($this->rapyd->uri->is_set("search")){
	$mSQL="SELECT date_format(fecha,'%d/%m/%Y') fecha, numa, codigoa, desca, cana FROM sitems WHERE fecha='2007-11-22 00:00:00' AND numa IN (".$this->rapyd->db->_compile_select().')';
	//echo $mSQL;
	
	$pdf = new PDFReporte($mSQL);
	$pdf->setHeadValores('TITULO1');
	$pdf->setSubHeadValores('TITULO2','TITULO3');
	$pdf->setTitulo("Art&iacute;culos despachados del ".$_POST['fechad'].' Hasta '.$_POST['fechah']);
	$pdf->AddPage();
	$pdf->setTableTitu(8,'Times');
  
	$pdf->AddCol('fecha'  ,30 ,'Fecha'      ,'C',8);
	$pdf->AddCol('numa'   ,25 ,'Factura'    ,'R',8);
	$pdf->AddCol('codigoa',30 ,'Codigo'     ,'R',8);
	$pdf->AddCol('cana'   ,15 ,'Cantidad'   ,'R',8);
	$pdf->AddCol('desca'  ,80 ,'Descripcion','L',8);
	
	if($_POST['agrupar']=='numa'){
		$pdf->setGrupoLabel('Factura <#numa#>');
		$pdf->setGrupo('numa');
	}elseif($_POST['agrupar']=='codigoa'){
		$pdf->setGrupoLabel('C&oacute;digo de Producto <#codigoa#>');
		$pdf->setGrupo('codigoa');
	}
	$pdf->setTotalizar('cana');
	$pdf->Table();
	$pdf->Output();
			
}else{
	$data["filtro"] = $filter->output;
	$data["titulo"] = '<h2 class="mainheader">Estatus de despacho en Facturas<h2>';
	$data["head"]   = $this->rapyd->get_head();
	$this->load->view('view_freportes', $data);
}
//*********************			
	}
	}
?>
