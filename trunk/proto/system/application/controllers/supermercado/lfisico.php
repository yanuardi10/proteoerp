<?php
class Lfisico extends Controller {
 
	var $cargo=0;
	
	function Lfisico(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		$this->load->library('PDFReporte');
		$this->rapyd->load("datafilter2");
		$repo =$this->uri->segment(3);
		$esta =$this->uri->segment(4);


//**************************
$modbus=array(
	'tabla'   =>'sinv',
	'columnas'=>array(
		'codigo' =>'C&oacute;digo',
		'descrip'=>'Descripci&oacute;n',
		'precio1'=>'Precio 1',
		'precio2'=>'Precio 2',
		'precio3'=>'Precio 3',
		'precio4'=>'Precio 4'),
	'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
	'retornar'=>array('codigo'=>'codigo'),
	'titulo'  =>'Buscar en inventario');
$boton=$this->datasis->modbus($modbus);

$opciones=array();
$mSQL='SELECT SQL_BIG_RESULT fecha FROM maesfisico GROUP BY fecha ORDER BY fecha DESC LIMIT 5';
$query = $this->db->query($mSQL);
foreach ($query->result() as $row)
	$opciones[$row->fecha]= dbdate_to_human($row->fecha);

$filter = new DataFilter2("Filtro del Reporte");
$filter->attributes=array('onsubmit'=>'is_loaded()');

$select=array("a.ubica" , "b.descrip","a.venta", "a.cantidad","a.anteri" ,"a.saldo", "a.monto", "a.salcant", "a.codigo", "a.origen", "a.promedio","a.cantidad-a.anteri AS diferencia"," (a.cantidad*b.fracxuni+b.fracci)*b.ultimo AS costo");
$filter->db->select($select);
$filter->db->from('costos AS a');  
$filter->db->join('maes AS b','a.codigo=b.codigo');

$filter->ubica = new dropdownField("Almacen", "ubica");  
$filter->ubica->option("","Todos");
$filter->ubica->db_name='ubica';
$filter->ubica->options("SELECT ubica,CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ");
$filter->ubica->operator="=";
$filter->ubica->clause="where";

$filter->fecha = new dropdownField("Fecha", "fecha");  
$filter->fecha->db_name='fecha';
$filter->fecha->options($opciones);
$filter->fecha->operator="=";
$filter->fecha->clause="where";

$filter->dif =new checkboxField("Solo diferencia distinta a cero", 'dif', "0");
$filter->dif->db_name='a.cantidad-a.anteri';
$filter->dif->operator="!=";
$filter->dif->clause="where";

$filter->buttons("search");
$filter->build();

if($this->rapyd->uri->is_set("search")  AND $filter->is_valid()){
	$fecha=dbdate_to_human($filter->fecha->value);
	$mSQL=$filter->db->_compile_select();

	//echo $mSQL;
	
	$pdf = new PDFReporte($mSQL,'L');
	$pdf->setType('cfecha','date');
	$pdf->setHeadValores('TITULO1');
	$pdf->setSubHeadValores('TITULO2','TITULO3');
	$pdf->setTitulo("Resumen de inventario f&iacute;sico del $fecha");
	$pdf->AddPage();
	$pdf->setTableTitu(12,'Times');

	$pdf->AddCol('codigo'    ,35,array('C&oacute;digo'          ,'')  ,'L',10);
	$pdf->AddCol('descrip'   ,80,array('Descripci&oacute;n'     ,'')  ,'L',10);
	$pdf->AddCol('cantidad'  ,30,array('Cantidad', 'Contada')  ,'R',10);
	$pdf->AddCol('anteri'    ,30,array('Cantidad','anterior')  ,'R',10);
	$pdf->AddCol('diferencia',30,array('Diferencia'      ,'')  ,'R',10);
	$pdf->AddCol('costo'     ,40,array('Costo'           ,'')  ,'R',10);

	$pdf->Table();
	$pdf->Output();
	
}else{
	if (strlen($filter->error_string)) $data["error"]=$filter->error_string;
	$data["filtro"] = $filter->output;
	$data["titulo"] = '<h2 class="mainheader">Resumen de inventario<h2>';
	$data["head"] = $this->rapyd->get_head();
	$this->load->view('view_freportes', $data);
}
//**************************
//**************************
	}
}
?>
