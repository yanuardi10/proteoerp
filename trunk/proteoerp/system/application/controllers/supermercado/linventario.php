<?php
class Linventario extends Controller {
 
	var $cargo=0;
	
	function Linventario(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		$this->load->library('PDFReporte');
		$this->rapyd->load("datafilter2");
		$repo =$this->uri->segment(3);
		$esta =$this->uri->segment(4);


//**************************
$mSPRV=array(
	'tabla'   =>'sprv',
	'columnas'=>array(
		'proveed' =>'C&oacute;digo',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
	'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
	'retornar'=>array('proveed'=>'proveed'),
	'titulo'  =>'Buscar Proveedor');

$bSPRV=$this->datasis->modbus($mSPRV);

$mGRUP=array(
	'tabla'   =>'grup',
	'columnas'=>array(
		'grupo' =>'C&oacute;digo',
		'nom_grup'=>'Nombre'),
		'filtro'  =>array('grupo'=>'C&oacute;digo','nom_grup'=>'Nombre'),
	'retornar'=>array('grupo'=>'grupo'),
	'titulo'  =>'Buscar Grupo');

$bGRUP=$this->datasis->modbus($mGRUP);

$filter = new DataFilter2("Filtro del Reporte");
$filter->attributes=array('onsubmit'=>'is_loaded()');

$select=array("codigo","descrip", "fechav","IF(pfecha1>pfecha2,IF(pfecha1>pfecha3,pfecha1,pfecha3),IF(pfecha2>pfecha3,pfecha2,pfecha3)) AS cfecha","existen","ultimo*existen as monto","grupo");
$filter->db->select($select);
$filter->db->from('sinv as a');
$filter->db->where('existen>','0');

$filter->dias = new inputField("Estancados desde los ultimos", "dias");
$filter->dias->clause  ='';
$filter->dias->append("d&iacute;as");
$filter->dias->rule = "required";
$filter->dias->insertValue=150;
$filter->dias->size=5;


$filter->proveed = new inputField("Proveedor", "proveed");
$filter->proveed->clause  ="where";
$filter->proveed->db_name='(prov1,prov2,prov3)';
$filter->proveed->append($bSPRV);
$filter->proveed->clause='in';
		
$filter->grupo = new inputField("Grupo","grupo");
$filter->grupo->db_name="grupo";
$filter->grupo->append($bGRUP);
$filter->grupo->clause  ="where";
$filter->grupo->operator="=";

$filter->buttons("search");
$filter->build();

if($this->rapyd->uri->is_set("search")  AND $filter->is_valid()){
	$dias=$filter->dias->value;
	$fecha=date("Ymd",mktime(0,0,0,date('m'),date('d')-$dias,date('Y')));
	$filter->db->where('fechav <',$fecha);
	$filter->db->where('IF(pfecha1>pfecha2,IF(pfecha1>pfecha3,pfecha1,pfecha3),IF(pfecha2>pfecha3,pfecha2,pfecha3))<',$fecha);
	$mSQL=$filter->db->_compile_select();

	//echo $mSQL;

	$sobretabla='';
	if (!empty($filter->proveed->newValue))  $sobretabla.='                       Proveedor: ('.$filter->proveed->newValue.') '.$this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed="'.$filter->proveed->newValue.'"');
	if (!empty($filter->grupo->newValue))    $sobretabla.='    Grupo: ('.$filter->grupo->newValue.') '.$this->datasis->dameval('SELECT nom_grup FROM grup WHERE grupo="'.$filter->grupo->newValue.'"');
	
	$pdf = new PDFReporte($mSQL,'L');
	$pdf->setType('cfecha','date');
	$pdf->setHeadValores('TITULO1');
	$pdf->setSubHeadValores('TITULO2','TITULO3');
	$pdf->setTitulo("Articulos sin Ventas");
	$pdf->setSobreTabla($sobretabla,10);
	$pdf->AddPage();
	$pdf->setTableTitu(12,'Times');

	$pdf->AddCol('codigo'  ,35,array('C&oacute;digo'      ,'')  ,'L',10);
	$pdf->AddCol('descrip' ,90,array('Descripci&oacute;n' ,'')  ,'L',10);
	$pdf->AddCol('fechav'  ,25,array('Ultima' ,'Venta')  ,'C',10);
	$pdf->AddCol('cfecha'  ,25,array('Ultima','Compra')  ,'C',10);
	$pdf->AddCol('existen' ,25,array('Cantidad'    ,'')  ,'R',10);
	$pdf->AddCol('monto'   ,30,array('Monto'       ,'')  ,'R',10);

	$pdf->Table();
	$pdf->Output();
	
}else{
	if (strlen($filter->error_string)) $data["error"]=$filter->error_string;
	$data["filtro"] = $filter->output;
	$data["titulo"] = '<h2 class="mainheader">Articulos sin Ventas<h2>';
	$data["head"] = $this->rapyd->get_head();
	$this->load->view('view_freportes', $data);
}
//**************************
//**************************
	}
}
?>
