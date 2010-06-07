<?php
class Prueba extends Controller {
	var $join;

	function Prueba(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->load->plugin('numletra');
	}

	function index($firma,$vend){

		if($firma=='firma'){
		//echo 'Hola '.$vend;
		$mSQL='SELECT * FROM sinv LIMIT 10';
		$query = $this->db->query($mSQL);
		foreach ($query->result() as $row){
			echo $row->codigo.','.$row->descrip.','.$row->precio1."\n";
		}
		}

		/*$this->load->library('encrypt');

		$msg = '4c2c1784194c0665ea951520544bdec1';

		$encrypted_string = $this->encrypt->encode($msg);
echo  $encrypted_string;*/
}
		//echo $encrypted_string;
	//function index(){
	//	$validar='29-02-2009';
	//	$formato='d-m-Y';
	//	$formato=preg_quote($formato,'/');
	//
	//	//ver si cuadra con el formato
	//	$search[] = "d"; $replace[] = "(0[1-9]|[1-2][0-9]|3[0-1])";
	//	$search[] = "j"; $replace[] = "([1-9]|[1-2][0-9]|3[0-1])";
	//	$search[] = "m"; $replace[] = "(0[1-9]|1[0-2])";
	//	$search[] = "n"; $replace[] = "([1-9]|1[0-2])";
	//	$search[] = "Y"; $replace[] = "([0-9]{4})";
	//	$search[] = "y"; $replace[] = "([0-9]{2})";
	//	$search[] = "H"; $replace[] = "([0-1][0-9]|2[0-4])";
	//	$search[] = "i"; $replace[] = "(0[0-9]|[1-5][0-9]|60)";
	//	$search[] = "s"; $replace[] = "(0[0-9]|[1-5][0-9]|60)";
	//	$pattern = '/'.$pattern.'/';
	//	$pattern = str_replace($search, $replace, $formato);
	//	if(preg_match($pattern,$validar)>0){
	//		//ver si la fecha es valida
	//		$search[] = "j"; $replace[] = "(?P<i>\d+)";
	//		$search[] = "d"; $replace[] = "(?P<i>\d+)";
	//		$search[] = "m"; $replace[] = "(?P<e>\d+)";
	//		$search[] = "n"; $replace[] = "(?P<e>\d+)";
	//		$search[] = "Y"; $replace[] = "(?P<a>\d+)";
	//		$search[] = "y"; $replace[] = "(?P<a>\d+)";
  //
	//		$pattern = str_replace($search, $replace, $formato);
	//		$pattern = '/'.$pattern.'/';
	//		preg_match($pattern,$validar,$matches);
	//		$mes =(isset($matches['e']) ? $matches['e'] : 1;
	//		$dia = (isset($matches['i']) ? $matches['i'] : 1;
	//		$anio= (isset($matches['a']) ? $matches['a'] : 1;
	//
	//		if(checkdate($matches['e'],$matches['i'],$matches['a'])){
	//
	//		}
	//	}else{
	//
	//	}
	//
	//	echo "$matches[i] / $matches[e] / $matches[a] \n";
	//	var_dump($rt);
	//	//$phpCodes = array('d', 'm', 'n', 'Y', 'y', 'j', 'H', 'i', 's');
	//}

	function xml(){
		$this->load->library("xmlinex");
		$this->xmlinex->import('test2.xml');

	}

	function xmlexport(){
		$this->load->helper('download');

		$this->load->library("xmlinex");
		$data[]=array('table'  =>'sinv');

		/*$data[]=array('select'=>array('articulo','nombre','precio'),
									'distinc'=>false,
									'table'  =>'tabla2',
									'where'  =>'precio = 100',
		);*/

		$data=$this->xmlinex->export($data);
		$name = 'xmlinex.xml';
		force_download($name, $data);
	}


	function nletras(){
		$this->load->plugin('numletra');
		echo numletra(115000.5);
	}

	function reportes(){
		$this->load->library("XLSReporte");
		$mSQL='SELECT * FROM muro order by envia,recibe,codigo';
		$xls = new XLSReporte($mSQL);
		$xls->setTitulo("TARJETA");
		$xls->setSubTitulo("Sub Titulo de Tarjeta");
		$xls->setSobreTabla("Este es el titulo d la tabla");
		$xls->setHeadValores('TITULO1');
		$xls->setSubHeadValores('TITULO2','TITULO3');

		//$xls->AddCol('estampa','Estampa' );
		$xls->AddCol('envia'  ,5,'Envia'   ,'L');
		$xls->AddCol('codigo' ,50,'Codigo'  ,'L');
		$xls->AddCol('recibe' ,20,'Recibe'  ,'L');
		$xls->AddCol('mensaje',100,'Mensaje' ,'L');
		//$xls->AddCol('',100,'Mensaje' ,'L');

		$xls->setTotalizar('codigo','envia','recibe');
		$xls->setGrupoLabel('Agrupado por la persona que envia:<#envia#>','y tamnien por la que recibe:<#recibe#>');
		$xls->setGrupo('envia','recibe');
		$xls->Table();
		$xls->Output();
	}
//[0-9]+
	function consola(){
		//echo ord(urldecode('%26'));
		$mk=mktime(0, 0 , 0, date("n")-12,date("j"), date("Y"));
		echo date('d/m/Y',$mk);
	}
	function expresion(){
		$string = 'The quick brown fox jumped over the lazy dog.';
		$patterns = array();
		$patterns[0] = '/quick/';
		$patterns[1] = '/brown/';
		$patterns[2] = '/fox/';
		$replacements = array();
		$replacements[2] = 'bear';
		$replacements[1] = 'black';
		$replacements[0] = 'slow';
		echo preg_replace($patterns, $replacements, $string);
	}
}
?>
