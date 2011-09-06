<?php
class ingresos{
	function genesmov($mes) {
		$udia=days_in_month(substr($mes,4),substr($mes,0,4));
		$fdesde=$mes.'01';
		$fhasta=$mes.$udia;

		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='MC' ");

		$mSQL= "SELECT a.*,b.rifci, c.numero AS afecta, c.fecha AS fafecta 
				FROM smov AS a LEFT JOIN scli AS b ON a.cod_cli=b.cliente 
				LEFT JOIN itccli AS c ON a.numero=c.numccli AND a.tipo_doc=c.tipoccli 
				LEFT JOIN grcl AS d ON b.grupo=d.grupo 
				WHERE a.fecha BETWEEN $fdesde AND $fhasta 
				AND a.tipo_doc IN ('NC')  
				AND d.clase!='I' 
				AND a.observa1 NOT LIKE '%DEVOLUCION%' 
				AND a.codigo!='NOCON' 
				AND a.codigo!='' AND a.cod_cli<>'REIVA'";

		//Procesando CxC smov    "
		$query = $this->db->query($mSQL);
		$mNUMERO  = 'ASDFGHJK';
		$mTIPO_DOC = "XX";
		
		foreach ( $query->result() as $row ){
			if ( $row->tipo_doc == 'NC' ){
				if ($mTIPO_DOC == $row->tipo_doc AND $mNUMERO == $row->numero ) continue;
				$mNUMERO = $row->numero;
				$mTIPO_DOC = $row->tipo_doc;
			}
			$referen = $row->num_ref;
			$registro = '01';
			if ( !empty($row->afecta) ) {
				$referen = $row->afecta;
				$aaa = $this->datasis->ivaplica($row->fafecta);
				$bbb = $this->datasis->ivaplica($row->fecha);
				if ( $aaa != $bbb )  $registro='04';
			}

			$stotal = $row->monto - $row->impuesto;
			$mSQL = "INSERT INTO siva SET 
				libro    = 'V',
				tipo     = '".$row->tipo_doc."',
				fuente   = 'MC',
				sucursal = '00',
				fecha    = '".$row->fecha."',
				numero   = '".$row->numero."',
				clipro   = '".$row->cod_cli."',
				nombre   =".$this->db->escape($row->nombre).",
				contribu ='CO',
				rif      = '".$row->rifci."', 
				registro = '$registro',
				nacional ='S',
				referen  = '$referen',
				general  = $row->montasa,
				geneimpu = $row->tasa, 
				reducida = $row->monredu, 
				reduimpu = $row->reducida,
				adicional= $row->monadic,
				adicimpu = $row->sobretasa,
				exento   = $row->exento, 
				impuesto = $row->impuesto, 
				gtotal   = $row->monto, 
				stotal   = $stotal,
				reiva    = ".$row->reteiva.",
				fechal   = ".$mes."01,
				fafecta  ='".$row->fafecta."'";
			$flag=$this->db->simple_query($mSQL);
			if(!$flag) memowrite($mSQL,'genesmov');
		}

		// RETENCIONES DE IVA DEL MISMO MES
		/*$mSQL = "SELECT b.fecha, a.numero, c.nombre, c.rifci, a.cod_cli,
						a.numero AS afecta, a.fecha AS fafecta, a.reteiva, a.transac, a.nroriva 
				FROM itccli AS a JOIN smov AS b ON a.transac=b.transac 
					 LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				WHERE b.fecha BETWEEN $fdesde AND $fhasta AND b.cod_cli='REIVA' 
					AND a.reteiva>0 AND b.monto>b.abonos 
					AND EXTRACT(YEAR_MONTH FROM a.fecha)=EXTRACT(YEAR_MONTH FROM b.fecha) ";

		$query = $this->db->query($mSQL);
		foreach ( $query->result() as $row ){
			$mSQL = "UPDATE siva SET reiva=$row->reteiva, comprobante=$row->nroriva WHERE tipo='FC' AND numero='$row->numero' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
			$flag=$this->db->simple_query($mSQL);    
			if(!$flag) memowrite($mSQL,'genesmov');
		}*/

		// RETENCIONES DE IVA
		$mSQL = "SELECT b.fecha, a.numero, c.nombre, c.rifci, COALESCE(c.cliente,a.cod_cli) AS cod_cli ,
					a.numero AS afecta, a.fecha AS fafecta, (a.reteiva) reteiva, a.transac, a.nroriva, a.emiriva, 
					if(a.recriva IS NULL, a.estampa, a.recriva) recriva, d.nfiscal
				FROM itccli AS a 
				JOIN smov AS b ON a.transac=b.transac 
				LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				JOIN sfac AS d ON a.numero=d.numero AND d.tipo_doc='F'
				WHERE  b.fecha<=$fhasta AND b.cod_cli='REIVA' 
					AND a.reteiva>0 
					AND b.fecha BETWEEN $fdesde AND $fhasta AND a.nroriva IS NOT NULL
				UNION 
				SELECT b.fecha, a.numero, IF(LENGTH(TRIM(e.nomfis))>0,e.nomfis,e.nombre) AS nombre, e.rifci, a.clipro,
					a.factura AS afecta, d.fecha AS fafecta, b.monto-b.abonos, a.transac, a.retencion, a.fecha, a.fecha, d.nfiscal
				FROM smov AS b JOIN prmo AS a ON a.transac=b.transac 
				JOIN sfac AS d ON a.factura=d.numero AND d.tipo_doc='F'
				JOIN scli AS e ON d.cod_cli=e.cliente
				WHERE b.fecha BETWEEN $fdesde AND $fhasta AND b.cod_cli='REIVA'";
		$query = $this->db->query($mSQL);

		foreach ( $query->result() as $row ){
			$mSQL = "SELECT monto-abonos FROM smov WHERE cod_cli='REIVA' AND transac='$row->transac'";
			$mSQL = "INSERT INTO siva SET 
					libro = 'V',
					tipo  = 'CR',
					fuente =  'MC',
					sucursal = '99', 
					fecha = '".$row->emiriva."',
					numero ='$row->nroriva',
					clipro = ".$this->db->escape($row->cod_cli).",
					nombre = ".$this->db->escape($row->nombre).",
					contribu = 'CO', 
					rif = ".$this->db->escape($row->rifci).",
					registro = '01',
					nacional ='S',
					referen ='',
					exento = 0, 
					general = 0, 
					geneimpu = 0, 
					reducida =  0, 
					reduimpu = 0, 
					adicional = 0, 
					adicimpu =  0, 
					impuesto = 0, 
					gtotal = 0, 
					stotal = 0, 
					reiva = '".$row->reteiva."', 
					comprobante = '',
					fecharece = '".$row->recriva."',
					fechal  = ".$mes."01, 
					nfiscal = ".$this->db->escape($row->nfiscal).",
					fafecta = ".$this->db->escape($row->fafecta).",
					afecta  = ".$this->db->escape($row->afecta);

			$flag=$this->db->simple_query($mSQL);
			if(!$flag) memowrite($mSQL,'genesmov');
		}

		//RETENCIONES ANTERIORES PENDIENTES
		$mSQL = "SELECT * FROM smov WHERE fecha<".$mes."01 AND cod_cli='REIVA' 
				 AND control IS NULL AND monto>abonos AND (tipo_ref<>'PR' OR tipo_ref IS NULL) ";
		$query = $this->db->query($mSQL);
		
		foreach ( $query->result() as $row ){
			$mSQL = "SELECT COUNT(*) FROM sfpa WHERE tipo_doc='FE' AND tipo='RI' 
					AND fecha='".$row->fecha."' AND '$row->observa1' LIKE CONCAT('%',numero,'%')";
			if ( $this->datasis->dameval($mSQL) <= 0 ) continue;
			$mSQL = "SELECT numero, cod_cli, transac 
					FROM sfpa 
					WHERE tipo_doc='FE' AND tipo='RI' AND fecha='".$row->fecha."' AND 
					'".$row->observa1."' LIKE CONCAT('%',numero,'%')";
			
			$query1 = $this->db->query($mSQL);
			$mREG   = $query1->result();
			$transac = $mREG->transac;
			$nombre = dameval("select nombre from sfac where transac='$transac'");
			$rif    = dameval("select rifci  from sfac where transac='$transac'");
			$mSQL = "INSERT INTO siva SET 
						libro = 'V', 
						tipo = 'RI',
						fuente = 'FA', 
						sucursal = '00', 
						fecha = '$row->fecha', 
						numero  = 'mREG->numero',  
						referen = 'mREG->numero',
						clipro  = 'mREG->cod_cli',  
						nombre = '$nombre',  
						contribu, 'CO', 
						rif = '$rif',  
						registro = '01',
						nacional = 'S',
						exento = 0,  
						fafecta  = '$row->fecha',
						general = 0, 
						geneimpu = 0, 
						reducida = 0, 
						reduimpu = 0, 
						adicional = 0, 
						adicimpu = 0,
						impuesto = 0, 
						gtotal = 0, 
						stotal = 0, 
						reiva = ".$row->monto.",
						fechal = ".$mes."01 ";
			$flag=$this->db->simple_query($mSQL);    
			if(!$flag) memowrite($mSQL,'genesmov');
		}
	}

	function geneotin($mes) {
		$udia=days_in_month(substr($mes,4),substr($mes,0,4));
		$fdesde=$mes.'01';
		$fhasta=$mes.$udia;
		
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='OT' ");
		$mSQL = "INSERT INTO siva 
				(id, libro, tipo, fuente, sucursal, fecha, numero, numhasta,  caja, nfiscal,  nhfiscal, 
				referen, planilla, clipro, nombre, contribu, rif, registro,
				nacional, exento, general, geneimpu, 
				adicional, adicimpu,  reducida,  reduimpu, stotal, impuesto, 
				gtotal, reiva, fechal, fafecta ) 
				SELECT 0 AS id,
				'V' AS libro, 
				a.tipo_doc AS tipo, 
				'OT' AS fuente, 
				'00' AS sucursal, 
				b.fecha, 
				b.numero, 
				' ' AS numhasta, 
				' ' AS caja, 
				b.nfiscal AS nfiscal, 
				'  ' AS nhfiscal, 
				b.afecta AS referen, 
				'  ' AS planilla, 
				b.cod_cli AS clipro, 
				b.nombre, 
				'CO' AS contribu, 
				c.rifci, 
				'01' AS registro, 
				'S' AS nacional, 
				b.exento exento, 
				b.montasa general, 
				b.tasa geneimpu, 
				b.monadic adicional, 
				b.sobretasa adicimpu, 
				b.monredu reducida,
				b.reducida reduimpu,
				b.totals stotal,
				b.iva AS impuesto, 
				b.totalg AS gtotal, 
				0 AS reiva, 
				".$mes."01 fechal, 
				b.fafecta fafecta 
				FROM itotin AS a JOIN otin AS b ON a.numero=b.numero AND a.tipo_doc=b.tipo_doc 
				LEFT JOIN scli AS c ON b.cod_cli=c.cliente LEFT JOIN grcl AS d ON c.grupo=d.grupo 
				WHERE d.clase!='I' AND 
				b.fecha BETWEEN $fdesde AND $fhasta
				AND (b.iva > 0 OR b.tipo_doc IN ('FC','ND') ) 
				GROUP BY a.tipo_doc,a.numero ";
		$flag=$this->db->simple_query($mSQL);    
		if(!$flag) memowrite($mSQL,'geneotin');
	}
}
