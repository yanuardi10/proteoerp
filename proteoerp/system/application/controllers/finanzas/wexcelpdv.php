	function wlvexcelpdv($mes){
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		
		$tasas = $this->_tasas($mes);
		$mivag = $tasas['general'];
		$mivar = $tasas['reducida'];
		$mivaa = $tasas['adicional'];
		
		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		var_dum($this->db->simple_query($mSQL));
		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		var_dum($this->db->simple_query($mSQL));
		$mSQL = "UPDATE club SET cedula=CONCAT('V',cedula) WHERE MID(cedula,1,1) IN ('0','1','2','3','4','5','6','7','8','9')  ";
		var_dum($this->db->simple_query($mSQL));
		
		// TRATA DE PONER FACTURA AFECTADA DESDE FMAY
		$mSQL = "UPDATE siva a JOIN fmay b ON a.numero=b.numero AND a.fuente='FA' AND b.tipo='D' AND a.fecha=b.fecha SET a.referen=presup WHERE  EXTRACT(YEAR_MONTH FROM a.fechal)=$mes  ";
		var_dum($this->db->simple_query($mSQL));
		
		// PONE NRO FISCAL EN SIVA
		//$mSQL = "UPDATE siva a JOIN fmay b ON a.numero=b.numero AND a.fuente='FA' AND b.tipo='D' AND a.fecha=b.fecha SET a.referen=presup WHERE  EXTRACT(YEAR_MONTH FROM a.fechal)=$mes  ";
		//var_dum($this->db->simple_query($mSQL));	
		
		// PARA HACERLO MENSUAL
		$mSQL ="SELECT 
		 a.fecha  fecha, 
		 a.numero numero, 
		 a.numero final,
		 c.cedula rif, 
		 CONCAT(c.nombres,' ', c.apellidos) AS nombre, 
		 ' ' AS numnc, 
		 ' ' AS numnd, 
		 'FC' AS tipo_doc, 
		 '        ' AS afecta, 
		 SUM(a.monto) ventatotal, 
		 SUM(a.monto*(a.impuesto=0)) exento, 
		 ROUND(SUM(a.monto*(a.impuesto=".$mivag.")*100/(100+a.impuesto)),2) baseg, 
		 ROUND(SUM(a.monto*(a.impuesto=".$mivar.")*100/(100+a.impuesto)),2) baser, 
		 ROUND(SUM(a.monto*(a.impuesto=".$mivaa.")*100/(100+a.impuesto)),2) basea, 
		 a.impuesto AS alicuota, 
		 ROUND(SUM(a.monto*(a.impuesto=".$mivag.") - a.monto*(a.impuesto=".$mivag.")*100/(100+a.impuesto)),2) AS impug, 
		 ROUND(SUM(a.monto*(a.impuesto=".$mivar.") - a.monto*(a.impuesto=".$mivar.")*100/(100+a.impuesto)),2) AS impur, 
		 ROUND(SUM(a.monto*(a.impuesto=".$mivaa.") - a.monto*(a.impuesto=".$mivaa.")*100/(100+a.impuesto)),2) AS impua, 
		 0 AS reiva, 
		 ' ' comprobante, 
		 ' ' fechacomp, 
		 ' ' impercibido, 
		 ' ' importacion, 
		 IF(c.cedula IS NOT NULL,IF(MID(c.cedula,1,1) IN ('V','E'), IF(CHAR_LENGTH(MID(c.cedula,2,10))=9,'SI','NO'), 'SI' ), 'NO') tiva, 
		 b.tipo, 
		 a.numero numa, 
		 a.caja, d.nfiscal
		 FROM vieite a 
		 LEFT JOIN viefac b ON a.numero=b.numero and a.caja=b.caja 
		 LEFT JOIN club c ON b.cliente=c.cod_tar 
		 LEFT JOIN dine d ON a.fecha=d.fecha AND a.caja=d.caja AND a.cajero=d.cajero
		 WHERE a.fecha >=".$mes."01 AND a.fecha<=".$mes."30  
		 GROUP BY a.fecha, a.caja, numa
		 UNION ALL
		 SELECT 
		 a.fecha AS fecha,
		 a.numero NUMERO,
		 '        ' AS FINAL,
		 a.rif AS RIF,
		 IF( MID(a.rif,1,1) NOT IN ('J','G') AND b.contacto IS NOT NULL AND b.contacto!='',b.contacto,a.nombre) AS NOMBRE,
		 '        ' NUMNC,
		 '        ' NUMND,
		 a.tipo AS TIPO_DOC, 
		 IF(a.referen=a.numero,'        ',a.referen) AS afecta,
		 a.gtotal*IF(a.tipo='NC',-1,1)  ventatotal,
		 a.exento*IF(a.tipo='NC',-1,1)  exento,
		 a.general*IF(a.tipo='NC',-1,1) baseg,
		 a.reducida*IF(a.tipo='NC',-1,1) baser,
		 a.adicional*IF(a.tipo='NC',-1,1) basea,
		 '$mivag%' AS ALICUOTA,
		 a.geneimpu*IF(a.tipo='NC',-1,1) AS impug,
		 a.reduimpu*IF(a.tipo='NC',-1,1) AS impur,
		 a.adicimpu*IF(a.tipo='NC',-1,1) AS impua,
		 a.reiva*IF(a.tipo='NC',-1,1),
		 '            ' comprobante,
		 '            ' fechacomp,
		 '            ' impercibido,
		 '            ' importacion,
		 'SI' tiva, 
		 a.tipo, 
		 a.numero numa, 
		 'MAYO' caja, nfiscal
		 FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente
		 WHERE a.fechal = ".$mes."01 AND a.fecha between ".$mes."01  AND ".$mes."30  AND a.libro='V' AND a.fuente<>'PV' AND a.tipo<>'RI' 
		 UNION ALL 
		 SELECT 
		 a.fecha AS fecha,
		 '        ' NUMERO,
		 a.numero AS FINAL,
		 a.rif AS RIF,
		 IF(MID(a.rif,1,1) NOT IN ('J','G') AND b.contacto IS NOT NULL AND b.contacto!='',b.contacto,a.nombre) AS NOMBRE,
		 '        ' NUMNC,
		 '        ' NUMND,
		 a.tipo AS TIPO_DOC, 
		 IF(a.referen=a.numero,'        ',a.referen) AS afecta,
		 a.gtotal*IF(a.tipo='NC',-1,1)  ventatotal,
		 a.exento*IF(a.tipo='NC',-1,1)  exento,
		 a.general*IF(a.tipo='NC',-1,1) baseg,
		 a.reducida*IF(a.tipo='NC',-1,1) baser,
		 a.adicional*IF(a.tipo='NC',-1,1) basea,
		 '$mivag%' AS ALICUOTA,
		 a.geneimpu*IF(a.tipo='NC',-1,1) AS impug,
		 a.reduimpu*IF(a.tipo='NC',-1,1) AS impur,
		 a.adicimpu*IF(a.tipo='NC',-1,1) AS impua,
		 a.reiva*IF(a.tipo='NC',-1,1),
		 c.nroriva COMPROBANTE,
		 c.emiriva FECHACOMP,
		 '            ' IMPERCIBIDO,
		 '            ' IMPORTACION,
		 'SI' tiva, a.tipo, a.numero numa, 'MAYO' caja, nfiscal
		 FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente LEFT JOIN itccli c ON a.numero=c.numero AND a.clipro=c.cod_cli 
		 WHERE a.fechal >= ".$mes."01 AND a.fecha<=".$mes."30  AND a.libro='V' AND a.fuente<>'PV' AND a.tipo='RI' 
		 UNION ALL
		 SELECT 
		 a.fecha   fecha,
		 a.numero  NUMERO,
		 '        ' AS FINAL,
		 '  ---***---' AS RIF,
		 'FACTURA ANULADA***FACTURA ANULADA' NOMBRE,
		 '        ' NUMNC,
		 '        ' NUMND,
		 'FC' TIPO_DOC, 
		 '        ' AFECTA,
		 0 VENTATOTAL,
		 0 EXENTO,
		 0 baseg,
		 0 baser,
		 0 basea,
		 '$mivag%' AS ALICUOTA,
		 0 impug,
		 0 impur,
		 0 impua,
		 0 reiva,
		 '   ' COMPROBANTE,
		 '   ' FECHACOMP,
		 '   ' IMPERCIBIDO,
		 '   ' IMPORTACION,
		 'SI' tiva, 
		 'FC', 
		 a.numero numa, 
		 'MAYO' caja, nfiscal
		 FROM fmay a 
		 WHERE a.fecha=".$mes."01 AND a.fecha<=".$mes."30 AND a.tipo='A' 
		 ORDER BY fecha, caja, numa ";
		
		//echo $mSQL; die;
		
		$export = $this->db->query($mSQL);
		
		$fname = tempnam("/tmp","lventas.xls");
		$this->load->library("workbook",array("fname" => $fname));
		$wb =& $this->workbook;
		$ws =& $wb->addworksheet($mes);
		
		// ANCHO DE LAS COLUMNAS
		$ws->set_column('A:A',6);
		$ws->set_column('B:B',8);
		$ws->set_column('C:C',40);
		$ws->set_column('D:E',10);
		$ws->set_column('F:F',6);
		$ws->set_column('G:K',11);
		$ws->set_column('L:U',16);
		
		// FORMATOS
		$h      =& $wb->addformat(array( "bold" => 1, "size" => 16, "merge" => 1));
		$h1     =& $wb->addformat(array( "bold" => 1, "size" => 11, "align" => 'left'));
		$titulo =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 0, "fg_color" => 'silver' ));
		$cuerpo =& $wb->addformat(array( "size" => 9 ));
		$numero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9 ));
		$Tnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "fg_color" => 'silver' ));
		$Rnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "align" => 'right' ));
		$tm =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 1, "fg_color" => 'silver' ));
		
		// COMIENZA A ESCRIBIR
		$nomes = substr($mes,4,2);
		$nomes1 = $anomeses[$nomes];
		$hs = "LIBRO DE VENTAS CORRESPONDIENTE AL MES DE ".$anomeses[$nomes]." DEL ".substr($mes,0,4);
		
		$ws->write(1, 0, $this->datasis->traevalor('TITULO1') , $h1 );
		$ws->write(2, 0, "RIF: ".$this->datasis->traevalor('RIF') , $h1 );
		
		$ws->write(4,0, $hs, $h );
		for ( $i=1; $i<20; $i++ ) {
			$ws->write_blank(4, $i,  $h );
		};
		$mm=6;
		
		// TITULOS
		$ws->write_string( $mm, 0, "", $titulo );
		$ws->write_string( $mm, 1, "", $titulo );
		$ws->write_string( $mm, 2, "", $titulo );
		$ws->write_string( $mm, 3, "", $titulo );
		$ws->write_string( $mm, 4, "", $titulo );
		$ws->write_string( $mm, 5, "Tipo", $titulo );
		$ws->write_string( $mm, 6, "Numero de", $titulo );
		$ws->write_string( $mm, 7, "Numero de", $titulo );
		$ws->write_string( $mm, 8, "Control", $titulo );
		$ws->write_string( $mm, 9, "Control", $titulo );
		$ws->write_string( $mm,10, "Numero de", $titulo );
		$ws->write_string( $mm,11, "Ventas", $titulo );
		$ws->write_string( $mm,12, "Total Ventas", $titulo );
		$ws->write_string( $mm,13, "Ventas Exentas", $titulo );
		$ws->write_string( $mm,14, "Valor FOB", $titulo );
		$ws->write_string( $mm,15, "VENTAS GRAVADAS", $tm );
		$ws->write_blank( $mm,16,  $tm );
		$ws->write_blank( $mm,17,  $tm );
		$ws->write_blank( $mm,18,  $tm );
		$ws->write_blank( $mm,19,  $tm );
		$ws->write_blank( $mm,20,  $tm );
		$ws->write_string( $mm,21, "IVA", $titulo );
		$ws->write_string( $mm,22, "Numero", $titulo );
		$ws->write_string( $mm,23, "Fecha", $titulo );
		$ws->write_string( $mm,24, "", $titulo );
		
		$mm++;
		$ws->write_string( $mm, 0, "Num.", $titulo );
		$ws->write_string( $mm, 1, "Fecha", $titulo );
		$ws->write_string( $mm, 2, "Nombre, Razon Social o Denominacion ", $titulo );
		$ws->write_string( $mm, 3, "RIF o", $titulo );
		$ws->write_string( $mm, 4, "Numero", $titulo );
		$ws->write_string( $mm, 5, "de", $titulo );
		$ws->write_string( $mm, 6, "Documento", $titulo );
		$ws->write_string( $mm, 7, "Documento.", $titulo );
		$ws->write_string( $mm, 8, "Fiscal", $titulo );
		$ws->write_string( $mm, 9, "Fiscal", $titulo );
		$ws->write_string( $mm,10, "Documento", $titulo );
		$ws->write_string( $mm,11, "A", $titulo );
		$ws->write_string( $mm,12, "Incluyendo", $titulo );
		$ws->write_string( $mm,13, "o no sujetas", $titulo );
		$ws->write_string( $mm,14, "Operacion", $titulo );
		
		$ws->write_string( $mm,15, "Alicuota General $mivag", $tm );
		$ws->write_blank( $mm,16,  $tm );
		$ws->write_string( $mm,17, "Alicuota Adicional $mivaa", $tm );
		$ws->write_blank( $mm,18,  $tm );
		$ws->write_string( $mm,19, "Alicuota Reducida $mivar", $tm );
		$ws->write_blank( $mm,20,  $tm );
		$ws->write_string( $mm,21, "Retenido", $titulo );
		$ws->write_string( $mm,22, "de", $titulo );
		$ws->write_string( $mm,23, "de", $titulo );
		$ws->write_string( $mm,24, "I.V.A.", $titulo );
		$mm++;
		$ws->write_string( $mm, 0, "Oper.", $titulo );
		$ws->write_string( $mm, 1, "", $titulo );
		$ws->write_string( $mm, 2, "del Comprador", $titulo );
		$ws->write_string( $mm, 3, "Cedula", $titulo );
		$ws->write_string( $mm, 4, "Caja", $titulo );
		$ws->write_string( $mm, 5, "Doc.", $titulo );
		$ws->write_string( $mm, 6, "Inicial", $titulo );
		$ws->write_string( $mm, 7, "Final", $titulo );
		$ws->write_string( $mm, 8, "Inicial", $titulo );
		$ws->write_string( $mm, 9, "Final", $titulo );
		$ws->write_string( $mm,10, "Afectado", $titulo );
		$ws->write_string( $mm,11, "Contrib.", $titulo );
		$ws->write_string( $mm,12, "el I.V.A.", $titulo );
		$ws->write_string( $mm,13, "a Impuesto", $titulo );
		$ws->write_string( $mm,13, "a Impuesto", $titulo );
		$ws->write_string( $mm,14, "Exportacion", $titulo );
		
		$ws->write_string( $mm,15, "Base", $titulo );
		$ws->write_string( $mm,16, "Impuesto", $titulo );
		$ws->write_string( $mm,17, "Base", $titulo );
		$ws->write_string( $mm,18, "Impuesto", $titulo );
		$ws->write_string( $mm,19, "Base", $titulo );
		$ws->write_string( $mm,20, "Impuesto", $titulo );
		$ws->write_string( $mm,21, "Comprador", $titulo );
		$ws->write_string( $mm,22, "Comprobante", $titulo );
		$ws->write_string( $mm,23, "Emision", $titulo );
		$ws->write_string( $mm,24, "Percibido", $titulo );
		
		$mm++;
		$ii = $mm;
		$mtiva = 'X';
		$mfecha = '2000-00-00';
		$tventas = $texenta = $tbaseg  = $tbaser  = $tbasea  = $timpug  = $timpur  = $timpua  = $treiva  = $tperci  = $mforza = $contri =0 ;
		$finicial = '99999999';
		$ffinal   = '00000000';
		$fiscali  = $fiscalf  = '';
		$caja = 'zytrdsefg';
		
		foreach( $export->result() as  $row ) {
			if ( empty($nfiscali) ) $nfiscali = $row->nfiscal;
			if ($caja == 'zytrdsefg') $caja=$row->caja;
			// chequea la fecha
			if ( $mfecha == $row->fecha ) {
				// Dentro del dia
				if($caja == $row->caja) {
					if($row->tiva == 'SI') {
						$mforza = $contri = 1;
					} else {
						if ( $row->tipo == 'NC' ) {
							$mforza = $contri = 1;
						} else {
							$mforza = $contri = 0;
							if ($finicial == '99999999') $finicial=$row->numero;
						};
					};
				}else {
					if ($finicial == '99999999') $finicial=$row->numero;
					$mforza = 1;
					if ( $row->tiva == 'SI' ) {
						$contri = 1;
					} else {
						$contri = 0;
					}
				};
			} else {
				// Imprime todo
				if ($finicial == '99999999') $finicial=$row->numero;
				$mforza = 1;
				if ( $row->tiva == 'SI' ) {
					$contri = 1;
				} else {
					$contri = 0;
				};
				if ( $row->tipo == 'NC' ) {
					$contri = 1;
				} ; 
			};
			
			if ( $mforza ) {
				// si tventas > 0 imprime totales
				if ( $tventas <> 0 ) {
					if ( $finicial == '99999999' ) $finicial = $ffinal;
					$ws->write_string( $mm,  0, $mm-8, $cuerpo );
					$fecha = substr($mfecha,8,2)."-".$ameses[substr($mfecha,5,2)-1]."-".substr($mfecha,0,4);
					$ws->write_string( $mm, 1, $fecha,  $cuerpo );		// Fecha
					$ws->write_string( $mm, 2, 'VENTAS A NO CONTRIBUYENTES', $cuerpo );    // Nombre
					$ws->write_string( $mm, 3, '  ******  ', $cuerpo );   // RIF/CEDULA
					$ws->write_string( $mm, 4, $caja,  $cuerpo );		      // Fecha
					$ws->write_string( $mm, 5, 'FC', $cuerpo );    		    // TIPO
					
					$ws->write_string( $mm, 6, $finicial, $cuerpo );      // Factura Inicial
					$ws->write_string( $mm, 7, $ffinal,   $cuerpo );      // Factura Final
					$ws->write_string( $mm, 8, $nfiscali, $cuerpo );      // Nro. N.C.
					$ws->write_string( $mm, 9, $nfiscali, $cuerpo );    	// Nro. N.D.
					
					$ws->write_string( $mm,10, $row->afecta, $cuerpo );   // DOC. AFECTADO
					$ws->write_string( $mm,11, 'NO',      $cuerpo );      // CONTRIBUYENTE
					$ws->write_number( $mm,12, $tventas,  $numero );      // VENTAS + IVA
					$ws->write_number( $mm,13, $texenta,  $numero );      // EXENTAS
					$ws->write_number( $mm,14, 0,         $numero );      // FOB
					
					$ws->write_number( $mm,15, $tbaseg, $numero );       // IVA RETENIDO
					$ws->write_number( $mm,16, $timpug, $numero );       // NRO COMPROBANTE
					$ws->write_number( $mm,17, $tbasea, $numero );       // ECHA COMPROB
					$ws->write_number( $mm,18, $timpua, $numero );   	   // IMPUESTO PERCIBIDO
					$ws->write_number( $mm,19, $tbaser, $numero );       // IMPORTACION
					$ws->write_number( $mm,20, $timpur, $numero );       // IMPORTACION
					
					$ws->write_number( $mm,21, $treiva,     $numero );   // IVA RETENIDO
					$ws->write_string( $mm,22, '',		$cuerpo );         // NRO COMPROBANTE
					$ws->write_string( $mm,23, '',		$cuerpo );         // FECHA COMPROB
					$ws->write_number( $mm,24, $tperci,	$numero );       // IMPUESTO PERCIBIDO
					
					$tventas = $texenta = $tbaseg  = $tbaser  = $tbasea  = $timpug  = $timpur  = $timpua  = $treiva  = $tperci  = 0;
					if ( $row->tipo_doc == 'FC' ) {
						$finicial = $row->numero;
					} else {
						$finicial = '99999999';
					};
					$mm++;
					$nfiscali = '';
					$caja = $row->caja;
				};
			};
			if ( $contri ) {
				// imprime contribuyente
				$fecha = $row->fecha;
				$ws->write_string( $mm,  0, $mm-8, $cuerpo );
				$fecha = substr($fecha,8,2)."-".$ameses[substr($fecha,5,2)-1]."-".substr($fecha,0,4);
				$ws->write_string( $mm, 1, $fecha,           $cuerpo );        // Fecha
				$ws->write_string( $mm, 2, $row->nombre,     $cuerpo );   // Nombre
				$ws->write_string( $mm, 3, $row->rif,        $cuerpo );   // RIF/CEDULA
				$ws->write_string( $mm, 4, $row->caja,       $cuerpo );   // Caja
				$ws->write_string( $mm, 5, $row->tipo_doc,   $cuerpo );   // Tipo_doc
				$ws->write_string( $mm, 6, $row->numero,     $cuerpo );   // Factura Inicial
				$ws->write_string( $mm, 7, '',               $cuerpo );   // Factura Final
				$ws->write_string( $mm, 8, substr($row->nfiscal,5,8),    $cuerpo );   // Fiscal Inicial
				$ws->write_string( $mm, 9, '',               $cuerpo );   // Fiscal Final
				$ws->write_string( $mm,10, $row->afecta,     $cuerpo );   // DOC. AFECTADO
				$ws->write_string( $mm,11, $row->tiva,       $cuerpo );   // CONTRIBUYENTE
				$ws->write_number( $mm,12, $row->ventatotal, $numero );   // VENTAS + IVA
				$ws->write_number( $mm,13, $row->exento,     $numero );   // VENTAS EXENTAS
				$ws->write_number( $mm,14, 0,                $numero );   // EXPORTACION FOB
				$ws->write_number( $mm,15, $row->baseg,      $numero );   // Base G
				$ws->write_number( $mm,16, $row->impug,      $numero );   // IVA G
				$ws->write_number( $mm,17, $row->basea,      $numero );   // Base A
				$ws->write_number( $mm,18, $row->impua,      $numero );   // IVA A
				$ws->write_number( $mm,19, $row->baser,      $numero );   // BASE R
				$ws->write_number( $mm,20, $row->impur,      $numero );   // IVA R
				$num = $mm+1;
				//$ws->write_formula( $mm,14,"=M$num*N$num/100" , $numero );   //I.V.A.
				$ws->write_number( $mm,21, $row->reiva,         $numero );   // IVA RETENIDO
				$ws->write_string( $mm,22, $row->comprobante,   $cuerpo );   // NRO COMPROBANTE
				$ws->write_string( $mm,23, $row->fechacomp,     $cuerpo );   // FECHA COMPROB
				$ws->write_number( $mm,24, $row->impercibido,   $numero );   // IMPUESTO PERCIBIDO
				//$ws->write_string( $mm,19, $row->importacion,   $cuerpo );   // IMPORTACION
				$finicial = '99999999';
				$mm++;
			} else {
				// Totaliza
				$tventas += $row->ventatotal ;
				$texenta += $row->exento ;
				$tbaseg  += $row->baseg  ;
				$tbaser  += $row->baser  ;
				$tbasea  += $row->basea  ;
				$timpug  += $row->impug ;
				$timpur  += $row->impur ;
				$timpua  += $row->impua ;
				$treiva  += $row->reiva  ;
				$tperci  += $row->impercibido ;
				if ( $finicial == '99999999' ) $finicial=$row->numero;
				if ( substr($row->final,0,2)!='NC')	$ffinal=$row->final;
			};
			$mfecha = $row->fecha;
			$caja = $row->caja;
			$nfiscali = $row->nfiscal;
		}
			//Imprime el Ultimo
		
		if ( $tventas <> 0 ) {
			if ( $finicial == '99999999' ) $finicial = $ffinal;
			$ws->write_string( $mm,  0, $mm-8, $cuerpo );
			$fecha = substr($mfecha,8,2)."-".$ameses[substr($mfecha,5,2)-1]."-".substr($mfecha,0,4);
			$ws->write_string( $mm, 1, $fecha,  $cuerpo );		// Fecha
			$ws->write_string( $mm, 2, 'VENTAS A NO CONTRIBUYENTES', $cuerpo );    // Nombre
			$ws->write_string( $mm, 3, '  ******  ', $cuerpo );    	// RIF/CEDULA
			$ws->write_string( $mm, 4, $caja,  $cuerpo );		  // Fecha
			$ws->write_string( $mm, 5, 'FC', $cuerpo );    		// TIPO
			
			$ws->write_string( $mm, 6, $finicial, $cuerpo );      // Factura Inicial
			$ws->write_string( $mm, 7, $ffinal,   $cuerpo );      // Factura Final
			$ws->write_string( $mm, 8, '',        $cuerpo );      // Nro. N.C.
			$ws->write_string( $mm, 9, '',        $cuerpo );    	// Nro. N.D.
			
			$ws->write_string( $mm,10, $row->afecta, $cuerpo );   // DOC. AFECTADO
			$ws->write_string( $mm,11, 'NO',      $cuerpo );      // CONTRIBUYENTE
			$ws->write_number( $mm,12, $tventas,  $numero );    	// VENTAS + IVA
			$ws->write_number( $mm,13, $texenta, $numero );    	  // EXENTAS
			$ws->write_number( $mm,14, 0,         $numero );    	// FOB
			
			$ws->write_number( $mm,15, $tbaseg, $numero );    // IVA RETENIDO
			$ws->write_number( $mm,16, $timpug, $numero );    // NRO COMPROBANTE
			$ws->write_number( $mm,17, $tbasea, $numero );    // ECHA COMPROB
			$ws->write_number( $mm,18, $timpua, $numero );   	// IMPUESTO PERCIBIDO
			$ws->write_number( $mm,19, $tbaser, $numero );    // IMPORTACION
			$ws->write_number( $mm,20, $timpur, $numero );    // IMPORTACION
			
			$ws->write_number( $mm,21, $treiva, $numero );   // IVA RETENIDO
			$ws->write_string( $mm,22, '',	$cuerpo );       // NRO COMPROBANTE
			$ws->write_string( $mm,23, '',	$cuerpo );       // FECHA COMPROB
			$ws->write_number( $mm,24, $tperci,	$numero );   // IMPUESTO PERCIBIDO
		};
		
		$celda = $mm+1;
		$fventas = "=M$celda";   // VENTAS
		$fexenta = "=N$celda";   // VENTAS EXENTAS
		$fbase   = "=M$celda";   // BASE IMPONIBLE
		$fiva    = "=O$celda";   // I.V.A. 
		
		$ws->write_string( $mm, 0,"Totales...",  $tm );
		$ws->write_blank( $mm, 1,  $tm );
		$ws->write_blank( $mm, 2,  $tm );
		$ws->write_blank( $mm, 3,  $tm );
		$ws->write_blank( $mm, 4,  $tm );
		$ws->write_blank( $mm, 5,  $tm );
		$ws->write_blank( $mm, 6,  $tm );
		$ws->write_blank( $mm, 7,  $tm );
		$ws->write_blank( $mm, 8,  $tm );
		$ws->write_blank( $mm, 9,  $tm );
		$ws->write_blank( $mm,10,  $tm );
		$ws->write_blank( $mm,11,  $tm );
		
		$ws->write_formula( $mm,12, "=SUM(M$ii:M$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula( $mm,13, "=SUM(N$ii:N$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula( $mm,14, "=SUM(O$ii:O$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_formula( $mm,15, "=SUM(P$ii:P$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_formula( $mm,16, "=SUM(Q$ii:Q$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_formula( $mm,17, "=SUM(R$ii:R$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_formula( $mm,18, "=SUM(S$ii:S$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_formula( $mm,19, "=SUM(T$ii:T$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_formula( $mm,20, "=SUM(U$ii:U$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_formula( $mm,21, "=SUM(V$ii:V$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_blank( $mm, 22,  $Tnumero );
		$ws->write_blank( $mm, 23,  $Tnumero );
		$ws->write_formula( $mm,24, "=SUM(Y$ii:Y$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		
		$mm ++;
		$mm ++;
		$ws->write($mm, 4, 'RESUMEN DE VENTAS Y DEBITOS', $tm );
		$ws->write_blank( $mm, 5,  $tm );
		$ws->write_blank( $mm, 6,  $tm );
		$ws->write_blank( $mm, 7,  $tm );
		$ws->write_blank( $mm, 8,  $tm );
		$ws->write_blank( $mm, 9,  $tm );
		$ws->write_blank( $mm,10,  $tm );
		$ws->write_blank( $mm,11,  $tm );
		
		$ws->write_string($mm, 12, 'Base Imponible', $titulo );
		$ws->write_string($mm, 13, 'Debito Fiscal',  $titulo );
		
		//$ws->write_string($mm, 15, 'IVA Retenido', $titulo );
		//$ws->write_string($mm, 16, 'IVA Percibido',$titulo );
		
		$mm++;
		$ws->write($mm, 4, 'Total Ventas Internas no Gravadas:', $h1 );
		$ws->write_formula($mm, 12, "=N$celda" , $Rnumero );
		$mm ++;
		$ws->write($mm, 4, 'Total Ventas de Exportacion:', $h1 );
		$ws->write_formula($mm, 12, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 13, "=0+0" , $Rnumero );
		$mm ++;
		$ws->write($mm,4, 'Total Ventas Internas Gravadas Alicuota General:', $h1 );
		$ws->write_formula($mm, 12, "=P$celda" , $Rnumero );
		$ws->write_formula($mm, 13, "=Q$celda" , $Rnumero );
		$mm ++;
		$ws->write($mm,4, 'Total Ventas Internas Gravadas Alicuota General + Adicional:', $h1 );
		$ws->write_formula($mm, 12, "=R$celda" , $Rnumero );
		$ws->write_formula($mm, 13, "=S$celda" , $Rnumero );
		$mm ++;
		$ws->write($mm,4, 'Total Ventas InternasGravadas Alicuota Reducida:', $h1 );
		$ws->write_formula($mm, 12, "=T$celda" , $Rnumero );
		$ws->write_formula($mm, 13, "=U$celda" , $Rnumero );
		$mm ++;
		$wb->close();
		header("Content-type: application/x-msexcel; name=\"lventas.xls\"");
		header("Content-Disposition: inline; filename=\"lventas.xls\"");
		$fh=fopen($fname,"rb");
		fpassthru($fh);
		unlink($fname);
		print "$header\n$data";
	}