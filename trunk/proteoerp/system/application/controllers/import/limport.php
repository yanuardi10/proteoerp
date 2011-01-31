<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class limport extends Controller {

	function limport() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(504,1);
		$this->load->helper('date');
	}
	function liqui($lnumero) {
		
		$mSQL_1="SELECT numero, fecha, status, proveed, nombre, agente, nomage, montofob, gastosi, montocif, 
		aranceles, gastosn, montotot, montoiva, montoexc, arribo, factura, cambioofi, cambioreal, peso, 
		LEFT(condicion, 256), transac, estampa, usuario, hora, dua, cargoval, control, crm 
		FROM ordi WHERE numero='$lnumero'";
			
	 $fname = tempnam("/tmp","liquidacion.xls");

		$this->load->library("workbook", array("fname"=>$fname));
		$wb = & $this->workbook ;
		$ws = & $wb->addworksheet($lnumero);

		# ANCHO DE LAS COLUMNAS
		$ws->set_column('A:A',15);
		$ws->set_column('B:B',40);
		$ws->set_column('C:C',19);
		$ws->set_column('D:E',19);
		$ws->set_column('F:F',19);
		$ws->set_column('G:G',19);
		$ws->set_column('H:W',19);
		$ws->set_column('X:X',19);

		// FORMATOS
		$h       =& $wb->addformat(array( "bold" => 1, "size" => 14, "align" => 'left'));
		$h0      =& $wb->addformat(array( "bold" => 1, "size" => 10, "align" => 'left'));
		$h1      =& $wb->addformat(array( "bold" => 1, "size" => 11, "align" => 'center'));
		$h2      =& $wb->addformat(array( "bold" => 1, "size" => 14, "align" => 'left', "fg_color" => 'silver'  ));
		$h3      =& $wb->addformat(array( "bold" => 1, "size" => 10, "align" => 'right'));
		$h4      =& $wb->addformat(array( "bold" => 1, "size" => 9, "align" => 'right'));
		
		$titulo  =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 0, "fg_color" => 'silver' ));
		$cuerpo  =& $wb->addformat(array( "size" => 9 ));
		$numero  =& $wb->addformat(array(  "num_format" => '#,##0.00' , "size" => 9 ));
		$Tnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "fg_color" => 'silver' ));
		$Rnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "align" => 'right' ));
		
		$enc = $this->db->query($mSQL_1);
		$row1 = $enc->row();
				
		$proveed= $row1->proveed; 
		$nombre= $row1->nombre; 
		$agente= $row1->agente;
		$nomage= $row1->nomage;
		$numerol= $row1->numero; 
		$fecha=  dbdate_to_human($row1->fecha);; 
		$arribo=  dbdate_to_human($row1->arribo);; 
		$factura= $row1->factura; 
		$dua= $row1->dua;
		$montofob  =$row1->montofob;  
		$gastosi   =$row1->gastosi;   
		$montocif  =$row1->montocif;  
		$aranceles =$row1->aranceles; 
		$gastosn   =$row1->gastosn;   
		$montotot  =$row1->montotot;  
		$montoiva  =$row1->montoiva;  
		$montoexc  =$row1->montoexc;  
		$cambioofi =$row1->cambioofi; 
		$cambioreal=$row1->cambioreal;

    // COMIENZA A ESCRIBIR
		    
		$ws->write(1, 0, $this->datasis->traevalor('TITULO1') , $h );
		$ws->write(2, 0, $this->datasis->traevalor('TITULO2') , $h0 );
		$ws->write(3, 0, "RIF: ".$this->datasis->traevalor('RIF') , $h0 );
		$ws->write(4, 0, " ",$h1 );
		$ws->write(5, 0, " ",$h1 );
		
		$ws->write(1, 5, "Orden de Importación Nº".$lnumero , $h );
		$ws->write(2, 5, "Fecha: ".$fecha , $h0 );
		$ws->write(3, 5, "Fecha de LLegada :".$arribo, $h0 );
		$ws->write(4, 5, " ",$h1 );
		$ws->write(5, 5, " ",$h1 );
		
		$ws->write(6, 0, "Proveedor:".'('.$proveed.')'.$nombre,$h0);
		for ( $i=1; $i<6; $i++ ) {
			$ws->write_blank(6, $i,  $h0 );
		};
		
		$ws->write(7, 0, "Agente Aduanal:".'('.$agente.')'.$nomage,$h0);
		for ( $i=1; $i<6; $i++ ) {
			$ws->write_blank(7, $i,  $h0 );
		};
				
		$hs = "Liquidación de Importación(PARA USO INTERNO)";
		$ws->write(8,0, $hs, $h );
		for ( $i=1; $i<6; $i++ ) {
			$ws->write_blank(8, $i,  $h );
		};
		
		$ws->write(6, 5, "Nro. de Factura:".$factura , $h0 );
		$ws->write(7, 5, "D.U.A: ".$dua , $h0 );
    
		// TITULOS
		$mm=10;
		$ws->write_string( $mm,   0, "", $titulo );
		$ws->write_string( $mm+1, 0, "Código", $titulo );
		$ws->write_string( $mm+2, 0, "", $titulo );
		$ws->write_string( $mm,   1, "", $titulo );
		$ws->write_string( $mm+1, 1, "Descripción", $titulo );
		$ws->write_string( $mm+2, 1, "", $titulo );
		$ws->write_string( $mm,   2, "", $titulo );
		$ws->write_string( $mm+1, 2, "Cant",$titulo );
		$ws->write_string( $mm+2, 2, "", $titulo );
		$ws->write_blank( $mm,    3, $titulo );
		$ws->write_string( $mm+1, 3, "Precio", $titulo );
		$ws->write_string( $mm+2, 3, "FOB", $titulo );
		$ws->write_string( $mm,   4, "",    $titulo );
		$ws->write_string( $mm+1, 4, "Monto",  $titulo );
		$ws->write_string( $mm+2, 4, "FOB",      $titulo );
		$ws->write_string( $mm,    5, "", $titulo );
		$ws->write_string( $mm+1,  5, "Part%", $titulo );
		$ws->write_string( $mm+2,  5, "", $titulo );
		$ws->write_string( $mm,    6, "", $titulo );
		$ws->write_string( $mm+1,  6, "Gastos", $titulo );
		$ws->write_string( $mm+2,  6, "Exterior", $titulo );
		$ws->write_string( $mm,    7, "", $titulo );
		$ws->write_string( $mm+1,  7, "Monto", $titulo );
		$ws->write_string( $mm+2,  7, "CIF", $titulo );
		$ws->write_string( $mm,    8, "", $titulo );
		$ws->write_string( $mm+1,  8, "Monto CIF $", $titulo );
		$ws->write_string( $mm+2,  8, "Oficial ".$cambioofi, $titulo );
		$ws->write_string( $mm,    9, "", $titulo );
		$ws->write_string( $mm+1,  9, "%", $titulo );
		$ws->write_string( $mm+2,  9, "", $titulo );
		$ws->write_blank( $mm,    10, $titulo );
		$ws->write_string( $mm+1, 10, "Arancel",$titulo );
		$ws->write_string( $mm+2, 10, "", $titulo );
		$ws->write_blank( $mm,    11, $titulo );
		$ws->write_string( $mm+1, 11, "Gastos",$titulo );
		$ws->write_string( $mm+2, 11, "Nacionales", $titulo );
		$ws->write_blank( $mm,    12, $titulo );
		$ws->write_string( $mm+1, 12, "Importe",$titulo );
		$ws->write_string( $mm+2, 12, "Nacional", $titulo );
		$ws->write_blank( $mm,    13, $titulo );
		$ws->write_string( $mm+1, 13, "Monto CIF $",$titulo );
		$ws->write_string( $mm+2, 13, "Real ".$cambioreal, $titulo );
		$ws->write_string( $mm,   14, "Importe", $titulo );
		$ws->write_string( $mm+1, 14, "Nacional", $titulo );
		$ws->write_string( $mm+2, 14, "Cambio Real", $titulo );
		$ws->write_blank( $mm,    15, $titulo );
		$ws->write_string( $mm+1, 15, "Costo",$titulo );
		$ws->write_string( $mm+2, 15, "Unitario", $titulo );
		$ws->write_blank( $mm,    16, $titulo );
		$ws->write_string( $mm+1, 16, "Cargo",$titulo );
		$ws->write_string( $mm+2, 16, "Valor", $titulo );
		

		$mm++;
		$mm++;
		$mm++;
		$ii = 1;
		$mtiva = 'X';
		$mfecha = '2000-00-00';
		$tventas = $texenta = $tbase = $timpue = $treiva = $tperci = 0 ;
		$dd=$mm;  // desde

		//die($mSQL);
		
		$mSQL = "SELECT numero, fecha, codigo, descrip, cantidad, costofob, importefob, gastosi, costocif, 
		importecif, importeciflocal, codaran, arancel, montoaran, gastosn, costofinal, importefinal, participam*100 as part, 
		participao, arancif, iva, precio1, precio2, precio3, precio4, estampa, hora, usuario, id,importecif*'$cambioofi' as importeciofi,
		importecif*'$cambioreal'+montoaran+gastosn as importotal,((importecif*'$cambioreal')+(montoaran+gastosn))/cantidad as cunitario,
		(importecif*'$cambioreal')/cantidad as importecifreal,(importecif*'$cambioreal')as montocifreal,((importecif*'$cambioreal')-(importecif*'$cambioofi')) as cargo
		FROM itordi WHERE numero='$lnumero'"; 
		
		//cambio real importecifreal/cantidad
		//importe nacional importecifreal
		//cargo valor cargo valor es la diferencia entre costo unitario calculado con dolar real menos el costo unitario calculado con el dolar oficial
	
		$mc = $this->db->query($mSQL);
		if ( $mc->num_rows() > 0 ) {
			foreach( $mc->result() as $row ) {
				$ws->write_string( $mm,  0,  $row->codigo,   $cuerpo );
        $ws->write_string( $mm,  1,  $row->descrip,  $cuerpo );
				$ws->write_number( $mm,  2,  $row->cantidad, $numero );
				$ws->write_number( $mm,  3,  $row->costofob,   $numero );
				$ws->write_number( $mm,  4,  $row->importefob,   $numero );
				$ws->write_number( $mm,  5,  $row->part,   $numero );
				$ws->write_number( $mm,  6,  $row->gastosi,   $numero );
				$ws->write_number( $mm,  7,  $row->importecif,   $numero );
				$ws->write_number( $mm,  8,  $row->importeciofi,   $numero );
				$ws->write_number( $mm,  9,  $row->arancel,   $numero );
				$ws->write_number( $mm,  10, $row->montoaran,   $numero );
				$ws->write_number( $mm,  11, $row->gastosn,   $numero );
				$ws->write_number( $mm,  12, $row->importotal,   $numero );
				$ws->write_number( $mm,  13, $row->montocifreal,   $numero );
				$ws->write_number( $mm,  14, $row->importecifreal,   $numero );
				$ws->write_number( $mm,  15, $row->cunitario,   $numero );//*
				$ws->write_number( $mm,  16, $row->cargo,   $numero );//*
				

				$mm++;
				$ii++;
			}
		}

		$celda = $mm+1;

		$ws->write_blank( $mm, 0,  $Tnumero );
		$ws->write( $mm,  1, "Totales...",$Tnumero );
		$ws->write_blank( $mm,  2,  $Tnumero );
		$ws->write_blank( $mm,  3,  $Tnumero );
		$ws->write_formula( $mm,  4,	"=SUM(D$dd:D$mm)", $Tnumero );
		$ws->write_blank( $mm,  5,  $Tnumero );
		$ws->write_formula( $mm,  6,	"=SUM(F$dd:F$mm)", $Tnumero );
		$ws->write_formula( $mm,  7,	"=SUM(G$dd:G$mm)", $Tnumero );
		$ws->write_formula( $mm,  8, "=SUM(H$dd:H$mm)", $Tnumero );  
		$ws->write_blank( $mm,  9,  $Tnumero ); 
		$ws->write_blank( $mm,  10, $Tnumero );   
		$ws->write_formula( $mm, 11, "=SUM(K$dd:K$mm)", $Tnumero );   
		$ws->write_formula( $mm, 12, "=SUM(L$dd:L$mm)", $Tnumero );   
		$ws->write_formula( $mm, 13, "=SUM(M$dd:M$mm)", $Tnumero );   
		$ws->write_formula( $mm, 14, "=SUM(N$dd:N$mm)", $Tnumero );  
		$ws->write_blank( $mm,  15,  $Tnumero ); 
		$ws->write_formula( $mm, 16, "=SUM(Q$dd:Q$mm)",$Tnumero );   

		$mm ++;
		$mm ++; 
		
		$ws->write_string($mm,  2, 'Costo en US$',$titulo);
		$ws->write_blank($mm,   3, $titulo );
		$ws->write_string($mm,  4, 'Costo en Bs',$titulo);
		$ws->write_blank($mm,   5, $titulo);
		$ws->write_string($mm,  6, 'Resumen de Liquidacion',$titulo);
		$ws->write_blank($mm,   7, $titulo);

		$mm ++;
		
		$montobase= $this->datasis->dameval("SELECT SUM(base) FROM ordiva WHERE ordeni='$lnumero' ")   ; 
		$montototal=$montocif*$cambioreal+$gastosn+$aranceles;
		
		$ws->write_string($mm,   2, 'Monto FOB:',$h3 );
		$ws->write_string($mm,   3, nformat($montofob),$h4 );
		$ws->write_string($mm,   4, 'Monto CIF:',$h3 );
		$ws->write_string($mm,   5, nformat($montocif*$cambioreal),$h4 );
		$ws->write_string($mm,   6, 'Monto Exento:',$h3 );
		$ws->write_string($mm,   7, nformat($montototal-$montobase), $h4);
		
		$mm ++;
		
		$ws->write_string($mm,   2, 'Gastos Exterior:',$h3 );
		$ws->write_string($mm,   3, nformat($gastosi),$h4);
		$ws->write_string($mm,   4, 'Gastos Nacionales:',$h3);
		$ws->write_string($mm,   5, nformat($gastosn),$h4 );
		$ws->write_string($mm,   6, 'Base Imponible:',$h3 );
		$ws->write_string($mm,   7, nformat($montobase), $h4);
		
		$mm ++;
		
		$ws->write_string($mm,   2, 'Monto CIF:',$h3 );
		$ws->write_string($mm,   3, nformat($montocif),$h4);
		$ws->write_string($mm,   4, 'ARANCELES:',$h3 );
		$ws->write_string($mm,   5, nformat($aranceles),$h4);
		$ws->write_string($mm,   6, 'Monto IVA:',$h3 );
		$ws->write_string($mm,   7, nformat($montoiva), $h4);
		
		$mm ++;
		
		$ws->write_string($mm,   2, ' ',$h3 );
		$ws->write_string($mm,   3, ' ',$h3 );
		$ws->write_string($mm,   4, 'Monto Final:',$h3);
		$ws->write_string($mm,   5, nformat($montototal),$h4);
		$ws->write_string($mm,   6, 'Monto Total Bs:',$h3 );
		$ws->write_string($mm,   7, nformat($montoiva+$montobase+$montototal-$montobase), $h4);
		
		$wb->close();
		header("Content-type: application/x-msexcel; name=\"lcompras.xls\"");
		header("Content-Disposition: inline; filename=\"lcompras.xls\"");
		$fh=fopen($fname,"rb");
		fpassthru($fh);
		unlink($fname);
		//print "$header\n$data";
	}
}