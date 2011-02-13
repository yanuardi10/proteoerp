<?php
class gastosycxp{

	function genegastos($mes){
		$udia=days_in_month(substr($mes,4),substr($mes,0,4));
		$fdesde=$mes.'01';
		$fhasta=$mes.$udia;

		//Procesando Compras gser
		$this->db->simple_query("UPDATE gser SET cajachi='N' WHERE cajachi='' or cajachi IS NULL");
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='GS' ");
		
		// REVISA GSER A VER SI HAY PROBLEMAS
		$this->db->simple_query("UPDATE gser SET exento=totbruto WHERE exento<>totbruto and totiva=0");

		$fciva=$this->datasis->dameval("SELECT MAX(fecha) FROM civa");
		$fciva = str_replace('-', '', $fciva);
		// Procesando Gastos
		$mSQL = "INSERT INTO siva  
			(id, libro, tipo, fuente, sucursal, fecha, numero, numhasta,  caja, nfiscal,  nhfiscal, 
			referen, planilla, clipro, nombre, contribu, rif, registro,
			nacional, exento, general, geneimpu, 
			adicional, adicimpu,  reducida,  reduimpu, stotal, impuesto, 
			gtotal, reiva, fechal, fafecta) 
			SELECT 0 AS id,
			'C' AS libro, 
			a.tipo_doc AS tipo, 
			'GS' AS fuente, 
			'00' AS sucursal, 
			a.ffactura, 
			COALESCE(a.serie,a.numero),
			' ' AS numhasta, 
			' ' AS caja, 
			a.nfiscal, 
			'  ' AS nhfiscal, 
			IF(a.tipo_doc='ND', a.afecta,'  ') AS referen, 
			'  ' AS planilla, 
			a.proveed AS clipro, 
			a.nombre, 
			'CO' AS contribu, 
			c.rif, 
			IF(a.ffactura >= $fciva  ,'01','05') AS registro, 
			'S' AS nacional, 
			a.exento  AS exento, 
			a.montasa AS general,   a.tasa      AS geneimpu, 
			a.monadic AS adicional, a.sobretasa  AS adicimpu, 
			a.monredu AS reducida,  a.reducida AS reduimpu, 
			a.totpre   AS stotal,
			a.totiva   AS impuesto, 
			a.totbruto AS gtotal, 
			a.reteiva  AS reiva, 
			".$mes."01 AS fechal, 
			a.fafecta AS fafecta 
			FROM gser AS a  
			LEFT JOIN sprv AS c ON a.proveed=c.proveed 
			WHERE a.fecha BETWEEN $fdesde AND $fhasta
			AND a.cajachi='N' 
			AND (c.tipo NOT IN ('5') OR a.totiva<>0 ) 
			ORDER BY a.fecha, a.proveed, a.numero ";
		$flag=$this->db->simple_query($mSQL);
		if(!$flag) memowrite($mSQL,'genegastos');

		// GASTOS DE  CAJACHICA
		$mATASAS = $this->datasis->ivaplica($mes.'02');
		$tolerancia=0.03;
		$mSQL = "INSERT INTO siva  
			(id, libro, tipo, fuente, sucursal, fecha, numero, numhasta,  caja, nfiscal,  nhfiscal, 
			referen, planilla, clipro, nombre, contribu, rif, registro,
			nacional, exento, general, geneimpu, 
			adicional, adicimpu,  reducida,  reduimpu, stotal, impuesto, 
			gtotal, reiva, fechal, fafecta) 
			SELECT 0 AS id,
			'C' AS libro, 
			'FC' AS tipo, 
			'GS' AS fuente, 
			'00' AS sucursal, 
			a.fechafac, 
			a.numfac, 
			' ' AS numhasta, 
			' ' AS caja, 
			a.nfiscal, 
			'  ' AS nhfiscal, 
			'  ' AS referen, 
			'  ' AS planilla, 
			'  ' AS clipro, 
			a.proveedor, 
			'CO' AS contribu, 
			a.rif, 
			IF(a.iva=0,'01',IF((abs((a.iva*100/a.precio)-".$mATASAS['tasa'].")<".$mATASAS['tasa']*$tolerancia.") or (abs((a.iva*100/a.precio)-".$mATASAS['sobretasa'].")<".$mATASAS['sobretasa']*$tolerancia.") or (abs((a.iva*100/a.precio)-".$mATASAS['redutasa'].")<".$mATASAS['redutasa']*$tolerancia."),'01','05')) AS registro, 
			'S' AS nacional, 
			IF(a.iva=0,1,0)*a.importe AS exento, 
			IF(abs((a.iva*100/a.precio)-".$mATASAS['tasa']     .")<".$mATASAS['tasa']*$tolerancia     .",1,0)*a.precio AS general, 
			IF(abs((a.iva*100/a.precio)-".$mATASAS['tasa']     .")<".$mATASAS['tasa']*$tolerancia     .",1,0)*a.iva    AS geneimpu, 
			IF(abs((a.iva*100/a.precio)-".$mATASAS['sobretasa'].")<".$mATASAS['sobretasa']*$tolerancia.",1,0)*a.precio AS adicional, 
			IF(abs((a.iva*100/a.precio)-".$mATASAS['sobretasa'].")<".$mATASAS['sobretasa']*$tolerancia.",1,0)*a.iva    AS adicimpu, 
			IF(abs((a.iva*100/a.precio)-".$mATASAS['redutasa'] .")<".$mATASAS['redutasa']*$tolerancia .",1,0)*a.precio AS reducida,
			IF(abs((a.iva*100/a.precio)-".$mATASAS['redutasa'] .")<".$mATASAS['redutasa']*$tolerancia .",1,0)*a.iva    AS reduimpu,
			a.precio AS stotal,
			a.iva AS impuesto, 
			a.importe AS gtotal, 
			0 AS reiva,
			".$mes."01 AS fechal, 
			0 AS fafecta 
			FROM gitser AS a JOIN gser AS b ON 
			a.fecha=b.fecha AND a.proveed=b.proveed AND a.numero=b.numero 
			WHERE b.fecha BETWEEN $fdesde AND $fhasta
			AND b.tipo_doc='FC' AND b.cajachi='S' 
			ORDER BY a.fecha";
		$flag=$this->db->simple_query($mSQL);    
		if(!$flag) memowrite($mSQL,'genegastoscchi');
    
		$mSQL = "UPDATE siva SET gtotal=exento+general+geneimpu+adicional+reduimpu+reducida+adicimpu 
			WHERE fuente='GS' AND libro='C' AND registro!='05'";
		$this->db->simple_query($mSQL);
	}

	function genecxp($mes) {
		$udia=days_in_month(substr($mes,4),substr($mes,0,4));
		$fdesde=$mes.'01';
		$fhasta=$mes.$udia;
		
		//Procesando Compras scst
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='MP' ");
		$this->db->simple_query("UPDATE sprv SET nomfis=nombre WHERE nomfis='' OR nomfis IS NULL ");
		$mFECHAF = $this->datasis->dameval("SELECT max(fecha) FROM civa WHERE fecha<=$mes"."01");

		$mSQL = "SELECT a.*,b.rif, b.nomfis,c.numero as afecta FROM sprm AS a 
		LEFT JOIN sprv AS b ON a.cod_prv=b.proveed
		JOIN itppro  AS c ON a.numero=c.numppro AND a.tipo_doc=c.tipoppro
		WHERE a.fecha BETWEEN $fdesde AND $fhasta AND b.tipo<>'5'
		AND a.tipo_doc='NC' AND a.codigo NOT IN ('NOCON','') ";
		$query = $this->db->query($mSQL);

		if ( $query->num_rows() > 0 ){
			foreach( $query->result() as $row ) {
				if ($row->impuesto == 0 and empty($row->codigo) ) continue;
				$referen = $this->datasis->dameval("SELECT numero FROM itppro WHERE transac=".$row->transac." LIMIT 1") ;
				$fafecta = $this->datasis->dameval("SELECT fecha  FROM itppro WHERE transac=".$row->transac." LIMIT 1") ;
				$stotal = $row->monto-$row->impuesto;
				$fecha  = ($row->fecapl==null) ? $row->fecha : $row->fecapl;
				$mSQL = "INSERT INTO siva SET 
					libro='C', 
					tipo='".$row->tipo_doc."', 
					fuente='MP', 
					sucursal='00', 
					fecha='${fecha}',
					numero='".$row->numero."', 
					clipro='".$row->cod_prv."', 
					nombre='".$row->nomfis."', 
					contribu='CO', 
					rif='".$row->rif."',
					registro=if('${fecha}'<'${mFECHAF}','04', '01'), 
					nacional='S', 
					nfiscal='".$row->nfiscal."', 
					general=".$row->montasa.", 
					geneimpu=".$row->tasa.", 
					reducida=".$row->monredu.", 
					reduimpu=".$row->reducida.", 
					adicional=".$row->monadic.", 
					adicimpu=".$row->sobretasa.", 
					exento=".$row->exento.", 
					impuesto=".$row->impuesto.", 
					gtotal=".$row->monto.", 
					stotal=".$stotal.", 
					fechal=".$mes."01, 
					referen='$referen', 
					afecta=".$row->afecta.",
					fafecta='$fafecta'";
				$flag=$this->db->simple_query($mSQL);    
				if(!$flag) memowrite($mSQL,'genecxp');
			}
		}
		// Procesando Compras scst
		$mSQL = "UPDATE siva SET gtotal=exento+general+geneimpu+adicional+reduimpu+reducida+adicimpu 
				WHERE fuente='MP' AND libro='C' ";
		$this->db->simple_query($mSQL);
	}
}