<?php
class xlspresupuesto extends Controller {
	
	function xlspresupuesto(){
		parent::Controller();
	}
	function index(){
	}
	function ver($numero=''){
		$this->load->library("XLSencabezadodetalle");
		if(!empty($numero)&&($numero!='')){
			$CI = & get_instance();			
			$row = $this->datasis->damerow("SELECT inicial,vd,fecha,numero,cod_cli,rifci,peso,CONCAT(' ',direc,dire1) AS direccion,nombre,iva,totalg,totals FROM spre  WHERE numero='$numero'");
			$mSQL_2 = "SELECT codigo,desca,cana,preca,importe from itspre WHERE numero='$numero'";

			if($row && $mSQL_2){
				$numero    =$row['numero'];
				$fecha     =$row['fecha'];
				$cod_cli   =$row['cod_cli'];
				$rifci     =$row['rifci'];
				$direccion =$row['direccion'];
				$nombre    =$row['nombre'];
				$peso      =$row['peso'];
				$pestotalgo=$row['iva'];
				$totalg    =$row['totalg'];
				$totals    =$row['totals'];
				$vd        =$row['vd'];				
				$vd        =$vd." ".$this->datasis->dameval("SELECT nombre FROM vend WHERE vendedor=$vd");
				$iva       =$row['iva'];
				$inicial   =$row['inicial'];

				$xls	=new XLSencabezadodetalle($mSQL_2);//$row,$detalle
				$xls->setHeadValores('TITULO1');
				$xls->setSubHeadValores('TITULO2','TITULO3');
				$xls->setTitle1("Presupuesto");
				
				$xls->AddCol('codigo'   ,10  ,'C&oacute;digo'       ,'L' ,8);	
				$xls->AddCol('desca'    ,40  ,'Descripci&oacute;n'  ,'L' ,8); 
				$xls->AddCol('cana'     ,10  ,'Cantidad'     ,'C' ,8); 
				$xls->AddCol('preca'    ,10  ,'Precio'       ,'R' ,8); 
				$xls->AddCol('importe'  ,10  ,'Importe'      ,'R' ,8); 
				//$xls->tcols();//escribe todas la columnas

				$xls->Header();

				//$xls->setTitle3("Encabezado");

				$selrow=array(
				"Cliente"      =>"$cod_cli",
				"RIF/CI"       =>"$rifci",
				"Nombre"       =>"$nombre",
				"Direccion"    =>"$direccion"
				);				
				$xls->setTitle2("Encabezado");
				$xls->encabezado($selrow);				
				//$xls->encabezado($row);//escribe todo los campos de la consulta
				
				$selrow=array(
				"Numero"       =>"$numero",
				"Fecha"        =>"$fecha",
				"Vendedor"     =>"$vd",
				"Peso"         =>"$peso",
				);
				$xls->encabezado($selrow,2,3,7);//encabezado(array,ColumnaEtiqueta,ColumnaValor,Fila)

				$xls->setTitle2("Detalle");
				$xls->detalle();

				$selrow=array(
				"IVA"       =>"$iva",
				"Inicial"   =>"$inicial",
				"Subtotal"  =>"$totals",
				"Total"     =>"$totalg"
				);
				$xls->setTitle2("Totales");
				$xls->encabezado($selrow);
					
				$xls->Footer();
			
				$xls->Output();

			}			
		}
	}
}
?>