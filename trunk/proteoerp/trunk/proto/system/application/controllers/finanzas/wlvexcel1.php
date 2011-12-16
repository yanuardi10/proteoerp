<?php
set_time_limit(300);
$mes=$_GET['mes'];
$farma=$_GET['farma'];

$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');

require "mysql.php";
$link=myconnect();
// BUSCA EN CIVA EL IVA QUE CORRESPONDE
$mc = damecur("SELECT tasa, redutasa, sobretasa FROM civa WHERE fecha < $mes"."01"." ORDER BY fecha DESC LIMIT 1");

$row = mysql_fetch_row($mc);
$tasa      = $row[0];
$redutasa  = $row[1];
$sobretasa = $row[2];



if ($mes>=200703)
{
   $tasa=11;  //traevalor('TASA');
} else {
   $tasa=14;  //traevalor('TASA');
};
@mysql_select_db("$farma") or die ("No se pudo conectar a $farma");

require_once "../wexcel/class.writeexcel_workbook.inc.php";
require_once "../wexcel/class.writeexcel_worksheet.inc.php";

// ARREGLA SIVA PORSIA
$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
ejecutasql($mSQL);

$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
ejecutasql($mSQL);

$mSQL  = "
SELECT 
a.fecha AS fecha,
a.numero AS NUMERO,
a.nfiscal AS FINAL,
a.rif AS RIF,
IF( MID(a.rif,1,1) NOT IN ('J','G') AND b.contacto IS NOT NULL AND b.contacto!='',b.contacto,a.nombre) AS NOMBRE,
IF(a.tipo='NC',a.numero,'        ') AS NUMNC,
IF(a.tipo='ND',a.numero,'        ') AS NUMND,
a.tipo AS TIPO, 
IF(a.referen=a.numero,'        ',a.referen) AS AFECTA,
a.gtotal*IF(a.tipo='NC',-1,1) VENTATOTAL,
a.exento*IF(a.tipo='NC',-1,1)  EXENTO,
a.general*IF(a.tipo='NC',-1,1) BASE,
'$tasa%' AS ALICUOTA,
a.impuesto*IF(a.tipo='NC',-1,1) AS Cgimpu,
a.reiva*IF(a.tipo='NC',-1,1),
'              ' COMPROBANTE,
'            ' FECHACOMP,
'            ' IMPERCIBIDO,
'            ' IMPORTACION,
'SI' tiva, 
a.tipo, 
a.numero numa,
a.general,
a.geneimpu,
a.adicional,
a.adicimpu,
a.reducida,
a.reduimpu,
a.contribu c2
FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente
WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.tipo<>'FA' 
ORDER BY a.fecha, IF(a.tipo IN ('FC','XE','XC'),1,2), numa ";
//echo $mSQL;
//die;
$export = damecur($mSQL);


################################################################
#
#  Encabezado
#
$fname = tempnam("/tmp","lventas.xls");
$wb =& new writeexcel_workbook($fname);
$ws =& $wb->addworksheet($mes);

# ANCHO DE LAS COLUMNAS
$ws->set_column('A:A',11);
$ws->set_column('B:B',37);
$ws->set_column('C:C',11);
$ws->set_column('D:D',6);
$ws->set_column('E:E',11);
$ws->set_column('F:F',6);

$ws->set_column('G:G',11);

$ws->set_column('H:H',6);
$ws->set_column('I:U',12);

# FORMATOS
$h      =& $wb->addformat(array( "bold" => 1, "size" => 16, "merge" => 1));
$h1     =& $wb->addformat(array( "bold" => 1, "size" => 11, "align" => 'left'));

$titulo =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 0, "fg_color" => 'silver' ));

$cuerpo  =& $wb->addformat(array( "size" => 9 ));
$cuerpoc =& $wb->addformat(array( "size" => 9, "align" => 'center', "merge" => 1 ));
$cuerpob =& $wb->addformat(array( "size" => 9, "align" => 'center', "bold" => 1, "merge" => 1 ));

$numero  =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9 ));
$Tnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "fg_color" => 'silver' ));
$Rnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "align" => 'right' ));

# COMIENZA A ESCRIBIR
$nomes = substr($mes,4,2);
$nomes1 = $anomeses[$nomes];
$hs = "LIBRO DE VENTAS CORRESPONDIENTE AL MES DE ".$anomeses[$nomes]." DEL ".substr($mes,0,4);

$ws->write(1, 0, traevalor('TITULO1') , $h1 );
$ws->write(2, 0, "RIF: ".traevalor('RIF') , $h1 );

$ws->write(4,0, $hs, $h );
for ( $i=1; $i<20; $i++ ) {
   $ws->write_blank(4, $i,  $h );
};

//$ws->write_string( $mm, $ii , $mvalor );

$mm=6;

// TITULOS
$ws->write_string( $mm,   0, "", $titulo );
$ws->write_string( $mm+1, 0, "Fecha", $titulo );
$ws->write_string( $mm+2, 0, "", $titulo );

$ws->write_string( $mm,   1, "Nombre, Razon Social", $titulo );
$ws->write_string( $mm+1, 1, "o Denominacion del ", $titulo );
$ws->write_string( $mm+2, 1, "Comprador", $titulo );

$ws->write_string( $mm,   2, "", $titulo );
$ws->write_string( $mm+1, 2, "R.I.F. o", $titulo );
$ws->write_string( $mm+2, 2, "Cedula", $titulo );

$ws->write_string( $mm,   3, "Tipo", $titulo );
$ws->write_string( $mm+1, 3, "de", $titulo );
$ws->write_string( $mm+2, 3, "Doc.", $titulo );

$ws->write_string( $mm,   4, "Numero", $titulo );
$ws->write_string( $mm+1, 4, "del", $titulo );
$ws->write_string( $mm+2, 4, "Doc.", $titulo );

$ws->write_string( $mm,   5, "Tipo", $titulo );
$ws->write_string( $mm+1, 5, "de", $titulo );
$ws->write_string( $mm+2, 5, "Trans", $titulo );

$ws->write_string( $mm,   6, "Numero", $titulo );
$ws->write_string( $mm+1, 6, "del Doc.", $titulo );
$ws->write_string( $mm+2, 6, "Afectado", $titulo );

$ws->write_string( $mm,   7, "Tipo ", $titulo );
$ws->write_string( $mm+1, 7, "de", $titulo );
$ws->write_string( $mm+2, 7, "Cont.", $titulo );

$ws->write_string( $mm,   8, "Total Ventas", $titulo );
$ws->write_string( $mm+1, 8, "Incluyendo el", $titulo );
$ws->write_string( $mm+2, 8, "I.V.A.", $titulo );

$ws->write_string( $mm,   9, "Ventas", $titulo );
$ws->write_string( $mm+1, 9, "Exentas o", $titulo );
$ws->write_string( $mm+2, 9, "no Sujetas", $titulo );

$ws->write_string( $mm,  10, "Valor", $titulo );
$ws->write_string( $mm+1,10, "FOB Op.", $titulo );
$ws->write_string( $mm+2,10, "Export", $titulo );


$ws->write_string( $mm,  11, "VENTAS GRAVADAS", $titulo );
$ws->write_string( $mm+1,11, "ALICUOTA GENERAL $tasa%", $titulo );
$ws->write_string( $mm+2,11, "Base", $titulo );

$ws->write_blank(  $mm,  12, $titulo );
$ws->write_blank(  $mm+1,12, $titulo );
$ws->write_string( $mm+2,12, "Impuesto", $titulo );

$ws->write_blank(  $mm,  13, $titulo );
$ws->write_string( $mm+1,13, "ALICUOTA ADICIONAL $sobretasa%", $titulo );
$ws->write_string( $mm+2,13, "Base", $titulo );

$ws->write_blank(  $mm,  14, $titulo );
$ws->write_blank(  $mm+1,14, $titulo );
$ws->write_string( $mm+2,14, "Impuesto", $titulo );

$ws->write_blank(  $mm,  15, $titulo );
$ws->write_string( $mm+1,15, "ALICUOTA REDUCIDA $redutasa%", $titulo );
$ws->write_string( $mm+2,15, "Base", $titulo );

$ws->write_blank(  $mm,  16, $titulo );
$ws->write_blank(  $mm+1,16, $titulo );
$ws->write_string( $mm+2,16, "Impuesto", $titulo );

$ws->write_string( $mm,  17, "I.V.A. ", $titulo );
$ws->write_string( $mm+1,17, "Retenido", $titulo );
$ws->write_string( $mm+2,17, "Comprador", $titulo );

$ws->write_string( $mm,  18, "Numero ", $titulo );
$ws->write_string( $mm+1,18, "de", $titulo );
$ws->write_string( $mm+2,18, "Comp.", $titulo );

$ws->write_string( $mm,  19, "Fecha ", $titulo );
$ws->write_string( $mm+1,19, "de", $titulo );
$ws->write_string( $mm+2,19, "Recepcion", $titulo );


$ws->write_string( $mm,  20, "", $titulo );
$ws->write_string( $mm+1,20, "I.V.A.", $titulo );
$ws->write_string( $mm+2,20, "Percibido", $titulo );

$mm++;
$mm++;
$mm++;

$ii = $mm;
$mtiva = 'X';
$mfecha = '2000-00-00';
$tventas = 0 ;
$texenta = 0 ;
$tbase   = 0 ;
$timpue  = 0 ;
$treiva  = 0 ;
$tperci  = 0 ;
$finicial = '99999999';
$ffinal   = '00000000';
$mforza = 0;
$contri = 0;

while($row = mysql_fetch_row($export)) {
    // imprime contribuyente
    $fecha = $row[0];
    $fecha = substr($row[0],8,2)."/".$ameses[substr($row[0],5,2)-1]."/".substr($row[0],0,4);
    $ws->write_string( $mm, 0, $fecha,  $cuerpo );    // Fecha
    $ws->write_string( $mm, 1, $row[ 4], $cuerpo );   // Nombre
    $ws->write_string( $mm, 2, $row[ 3], $cuerpo );   // RIF/CEDULA
    if ( $row[7] == "XE" ) 
	$ws->write_string( $mm, 3, "FC", $cuerpoc );   // TIPO
    else
	$ws->write_string( $mm, 3, $row[ 7], $cuerpoc );   // TIPO
    
    $ws->write_string( $mm, 4, $row[ 1], $cuerpo );   // Nro. Documento
    if ( $row[7] == "XE" ) 
	$ws->write_string( $mm, 5, "03-Anu", $cuerpoc );   // TIPO Transac
    else
	$ws->write_string( $mm, 5, "01-Reg", $cuerpoc );   // TIPO Transac
    
    $ws->write_string( $mm, 6, $row[ 8], $cuerpo );   // DOC. AFECTADO
    $ws->write_string( $mm, 7, $row[28], $cuerpo );   // CONTRIBUYENTE
    $ws->write_number( $mm, 8, $row[ 9], $numero );   // VENTAS + IVA
    $ws->write_number( $mm, 9, $row[10], $numero );   // VENTAS EXENTAS
    $ws->write_number( $mm,10, 0, $cuerpo );   		// EXPORTACION

    $ws->write_number( $mm,11, $row[22], $numero );   // GENERAL
    $ws->write_number( $mm,12, $row[23], $numero );   // GENEIMPU
    $ws->write_number( $mm,13, $row[24], $numero );   // ADICIONAL
    $ws->write_number( $mm,14, $row[25], $numero );   // ADICIMPU
    $ws->write_number( $mm,15, $row[26], $numero );   // REDUCIDA
    $ws->write_number( $mm,16, $row[27], $numero );   // REDUIMPU

    $ws->write_number( $mm,17, $row[14], $numero );   // IVA RETENIDO
    $ws->write_string( $mm,18, $row[15], $cuerpo );   // NRO COMPROBANTE
    $ws->write_string( $mm,19, $row[16], $cuerpo );   // FECHA COMPROB
    $ws->write_number( $mm,20, $row[17], $numero );   // IMPUESTO PERCIBIDO
    $mm++;
}

//Imprime el Ultimo

$celda = $mm+1;

$fventas = "=I$celda";   // VENTAS
$fexenta = "=J$celda";   // VENTAS EXENTAS

$ffob    = "=K$celda";   // BASE IMPONIBLE

$fgeneral  = "=L$celda";   // general
$fgeneimpu = "=M$celda";   // general

$fadicional = "=N$celda";   // general
$fadicimpu  = "=O$celda";   // general

$freducida = "=P$celda";   // general
$freduimpu = "=Q$celda";   // general

$fivaret   = "=R$celda";   // general
$fivaperu  = "=U$celda";   // general


$ws->write( $mm, 0,"Totales...",  $titulo );
$ws->write_blank( $mm, 1,  $titulo );
$ws->write_blank( $mm, 2,  $titulo );
$ws->write_blank( $mm, 3,  $titulo );
$ws->write_blank( $mm, 4,  $titulo );
$ws->write_blank( $mm, 5,  $titulo );
$ws->write_blank( $mm, 6,  $titulo );
$ws->write_blank( $mm, 7,  $titulo );

$ws->write_formula( $mm, 8, "=SUM(I$ii:I$mm)", $Tnumero );   //"VENTAS + IVA" 
$ws->write_formula( $mm, 9, "=SUM(J$ii:J$mm)", $Tnumero );   //"VENTAS EXENTAS" 
$ws->write_formula( $mm,10, "=SUM(K$ii:K$mm)", $Tnumero );   //"BASE IMPONIBLE" 

$ws->write_formula( $mm,11, "=SUM(L$ii:L$mm)", $Tnumero );   //"VENTAS + IVA" 
$ws->write_formula( $mm,12, "=SUM(M$ii:M$mm)", $Tnumero );   //"VENTAS EXENTAS" 
$ws->write_formula( $mm,13, "=SUM(N$ii:N$mm)", $Tnumero );   //"BASE IMPONIBLE" 

$ws->write_formula( $mm,14, "=SUM(O$ii:O$mm)", $Tnumero );   //"VENTAS + IVA" 
$ws->write_formula( $mm,15, "=SUM(P$ii:P$mm)", $Tnumero );   //"VENTAS EXENTAS" 
$ws->write_formula( $mm,16, "=SUM(Q$ii:Q$mm)", $Tnumero );   //"BASE IMPONIBLE" 

$ws->write_formula( $mm,17, "=SUM(R$ii:R$mm)", $Tnumero );   //"BASE IMPONIBLE" 
$ws->write_blank( $mm, 18,  $Tnumero );
$ws->write_blank( $mm, 19,  $Tnumero );
$ws->write_formula( $mm,20, "=SUM(U$ii:U$mm)", $Tnumero );   //IMPUESTO PERCIBIDO 

$mm ++;
$mm ++;

$ws->write($mm, 1, 'RESUMEN DE VENTAS Y DEBITOS:', $titulo );
$ws->write_blank( $mm+1, 1,  $titulo );

$ws->write_blank( $mm, 2,  $titulo );
$ws->write_blank( $mm+1, 2,  $titulo );

$ws->write_blank( $mm, 3,  $titulo );
$ws->write_blank( $mm+1, 3,  $titulo );

$ws->write_blank( $mm, 4,  $titulo );
$ws->write_blank( $mm+1, 4,  $titulo );

$ws->write_blank( $mm, 5,  $titulo );
$ws->write_blank( $mm+1, 5,  $titulo );

$ws->write_blank( $mm, 6,  $titulo );
$ws->write_blank( $mm+1, 6,  $titulo );

$ws->write($mm, 7, 'Items', $titulo );
$ws->write_blank( $mm+1, 7,  $titulo );

$ws->write($mm,   8, 'Base', $titulo );
$ws->write($mm+1, 8, 'Imponible', $titulo );

$ws->write($mm, 9, 'Items', $titulo );
$ws->write_blank( $mm+1, 9,  $titulo );

$ws->write($mm,  10, 'Debito', $titulo );
$ws->write($mm+1,10, 'Fiscal', $titulo );

$ws->write($mm,11, 'Items', $titulo );
$ws->write_blank( $mm+1, 11,  $titulo );

$ws->write($mm,12, 'IVA Ret.', $titulo );
$ws->write($mm+1,12, 'Retetenido', $titulo );

$ws->write($mm,13, 'Items', $titulo );
$ws->write_blank( $mm+1, 13,  $titulo );

$ws->write($mm,  14, 'IVA', $titulo );
$ws->write($mm+1,14, 'Percibido', $titulo );

$ws->write($mm, 16, 'LEYENDAS:', $titulo );
$ws->write_blank( $mm+1, 16,  $titulo );

$ws->write_blank( $mm,   17,  $titulo );
$ws->write_blank( $mm+1, 17,  $titulo );

$ws->write($mm,   18, 'Tipo Contribuyente',  $cuerpob );
$ws->write($mm+1, 18, 'CO -Contribuyente',$cuerpo );

$ws->write_blank( $mm,   19, $cuerpob );
$ws->write_blank( $mm+1, 19, $cuerpo );

$mm ++;
$mm ++;

$ws->write($mm, 1, 'Total Ventas Internas no Gravadas:', $h1 );
$ws->write_blank( $mm, 2,  $h1 );
$ws->write_blank( $mm, 3,  $h1 );
$ws->write_blank( $mm, 4,  $h1 );
$ws->write_blank( $mm, 5,  $h1 );
$ws->write_blank( $mm, 6,  $h1 );
$ws->write($mm, 7, "40" , $cuerpoc );
$ws->write_formula($mm, 8, "=J$celda" , $Rnumero );

$ws->write($mm, 16, 'Tipo de Documento', $cuerpob );
$ws->write_blank( $mm, 17,  $cuerpob );

$ws->write($mm, 18, 'NO -No Contribuyente', $cuerpo );

$mm ++;


$ws->write($mm, 1, 'Total Ventas de Exportacion:', $h1 );
$ws->write_blank( $mm, 2,  $h1 );
$ws->write_blank( $mm, 3,  $h1 );
$ws->write_blank( $mm, 4,  $h1 );
$ws->write_blank( $mm, 5,  $h1 );
$ws->write_blank( $mm, 6,  $h1 );
$ws->write($mm, 7, "41" , $cuerpoc );
$ws->write_formula($mm, 8, "=K$celda" , $Rnumero );
$ws->write($mm, 16, 'FC -Factura', $cuerpo );
$ws->write_blank( $mm+1, 16,  $cuerpo );

$ws->write($mm, 18, 'Tipo de Transaccion', $cuerpob );
$ws->write_blank( $mm, 19,  $cuerpob );

$mm ++;

$ws->write($mm, 1, 'Total Ventas Internas Gravadas Alicuota General:', $h1 );
$ws->write_blank( $mm, 2,  $h1 );
$ws->write_blank( $mm, 3,  $h1 );
$ws->write_blank( $mm, 4,  $h1 );
$ws->write_blank( $mm, 5,  $h1 );
$ws->write_blank( $mm, 6,  $h1 );
$ws->write($mm, 7, "42" , $cuerpoc );
$ws->write_formula($mm, 8, "=L$celda" , $Rnumero );
$ws->write($mm, 9, "43" , $cuerpoc );
$ws->write_formula($mm, 10, "=M$celda" , $Rnumero );
$ws->write_formula($mm, 12, "=0", $Rnumero );
$ws->write_formula($mm, 14, "=0", $Rnumero );
$ws->write($mm, 16, 'FE -Factura de Exportacion', $cuerpo );
$ws->write_blank( $mm+1, 16,  $cuerpo );

$ws->write($mm, 18, '01 -Registro', $cuerpo );
$ws->write_blank( $mm, 19,  $cuerpo );

$mm ++;

$ws->write($mm, 1, 'Total Ventas Internas Alicuota General + Adicional:', $h1 );
$ws->write_blank(  $mm, 2,  $h1 );
$ws->write_blank(  $mm, 3,  $h1 );
$ws->write_blank(  $mm, 4,  $h1 );
$ws->write_blank(  $mm, 5,  $h1 );
$ws->write_blank(  $mm, 6,  $h1 );
$ws->write($mm, 7, "442" , $cuerpoc );
$ws->write_formula($mm, 8, "=N$celda" , $Rnumero );
$ws->write($mm, 9, "452" , $cuerpoc );
$ws->write_formula($mm, 10, "=O$celda" , $Rnumero );
$ws->write_formula($mm, 12, "=0", $Rnumero );
$ws->write_formula($mm, 14, "=0", $Rnumero );

$ws->write($mm, 16, 'NC -Nota de Credito', $cuerpo );
$ws->write_blank( $mm+1, 16,  $cuerpo );

$ws->write($mm, 18, '02 -Complemento', $cuerpo );
$ws->write_blank( $mm, 19,  $cuerpo );

$mm ++;

$ws->write($mm, 1, 'Total Ventas Internas Alicuota Reducida:', $h1 );
$ws->write_blank( $mm, 2,  $h1 );
$ws->write_blank( $mm, 3,  $h1 );
$ws->write_blank( $mm, 4,  $h1 );
$ws->write_blank( $mm, 5,  $h1 );
$ws->write_blank( $mm, 6,  $h1 );
$ws->write($mm, 7, "443" , $cuerpoc );
$ws->write_formula($mm, 8, "=P$celda" , $Rnumero );
$ws->write($mm, 9, "453" , $cuerpoc );
$ws->write_formula($mm, 10, "=Q$celda", $Rnumero );

$ws->write_formula($mm, 12, "=0", $Rnumero );
$ws->write_formula($mm, 14, "=0", $Rnumero );

$ws->write($mm, 16, 'ND -Nota de Debito', $cuerpo );
$ws->write_blank( $mm+1, 16,  $cuerpo );

$ws->write($mm, 18, '03 -Anulacion', $cuerpo );
$ws->write_blank( $mm, 19,  $cuerpo );

$mm ++;

$ws->write($mm, 1, 'Total Ventas y Creditos Fiscales para efectos de determinacion:', $titulo );
$ws->write_blank( $mm, 2,  $titulo );
$ws->write_blank( $mm, 3,  $titulo );
$ws->write_blank( $mm, 4,  $titulo );
$ws->write_blank( $mm, 5,  $titulo );
$ws->write_blank( $mm, 6,  $titulo );
$ws->write($mm, 7, "46" , $cuerpoc );
$ws->write_formula($mm, 8, "=J$celda+K$celda+L$celda+N$celda+P$celda" , $Rnumero );
$ws->write($mm, 9, "47" , $cuerpoc );
$ws->write_formula($mm, 10, "=M$celda+O$celda+Q$celda", $Rnumero );

$ws->write($mm, 11, "66" , $cuerpoc );
$ws->write_formula($mm, 12, "=0", $Rnumero );

$ws->write($mm, 13, "68" , $cuerpoc );
$ws->write_formula($mm, 14, "=0", $Rnumero );

$ws->write($mm, 16, 'TK -Ticket de Caja', $cuerpo );
$ws->write_blank( $mm+1, 16,  $cuerpo );

$ws->write($mm, 18, '04 -Ajuste', $cuerpo );
$ws->write_blank( $mm, 19,  $cuerpo );

$mm ++;

$wb->close();
header("Content-type: application/x-msexcel; name=\"lventas.xls\"");
header("Content-Disposition: inline; filename=\"lventas.xls\"");
$fh=fopen($fname,"rb");
fpassthru($fh);
unlink($fname);

#$data = str_replace("\r","",$data);

#header("Content-type: application/x-msdownload");
#header("Content-Disposition: attachment; filename=lventas.xls");
#header("Pragma: no-cache");
#header("Expires: 0");
/*
$fp = fopen('test.dat', 'w');
fwrite($fp,"$header\n$data");
fclose($fp);
*/
print "$header\n$data";

?> 