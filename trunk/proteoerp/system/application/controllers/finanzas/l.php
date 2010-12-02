<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Libros extends Controller {

	function Libros() {
		parent::Controller();
		$this->load->helper('url');
		$this->load->helper('text');
	}
	function index() {
		$mSQL='SELECT YEAR(fechal), YEAR(fechal) FROM siva WHERE YEAR(fechal)!=0 GROUP BY YEAR(fechal) ORDER BY YEAR(fechal) DESC LIMIT 10';
		$anhos=$this->datasis->consularray($mSQL);
		$direccion= "'".base_url()."finanzas/libros/'+this.form.tarch.value+'/'+this.form.year.value+this.form.mes.value";

		$tarch=array(
		'wlvexcelpdv'=>'Libro de Ventas PDV',
		'wlvexcel'   =>'Libro de Ventas ',
		'Wlcexcel'   =>'Libro de Compras',
		'wlvexcele'   =>'Libro de Ventas  ESPECIAL',
		'wlcexcele'   =>'Libro de Compras ESPECIAL',
		'prorrata'   =>'Prorrata',
		'invresu'    =>'Libro de Inventario',
		);
		
		$data['titulo']  ="<H1>OBLIGACIONES TRIBUTARIAS</H1><br><br>";
		$data['titulo'] .= form_open(); 
		$data['titulo'] .= "<table width='400'><tr><td>";
		$data['titulo'] .= form_dropdown('tarch',$tarch,'','size=12');
		$data['titulo'] .= "</td><td>";
		$data['titulo'] .= form_dropdown('year',$anhos,'','size=12');
		$data['titulo'] .= "</td><td>";
		$data['titulo'] .= form_dropdown('mes',$this->datasis->ames(),'','size=12');
		$data['titulo'] .= "</td><td>";
		$data['titulo'] .= '<input type="button"  name="pasa" value="Ejecutar" onClick="redireccionar('.$direccion.');" />';
		$data['titulo'] .= "</td></tr></table>";
		$data['titulo'] .= form_close();
		$this->layout->buildPage('finanzas/home', $data);
  }

	function cierre() {
		if ( $this->datasis->puede("1500") ) { 
			$this->load->library('table');
			if (!empty($_POST)) extract($_POST) ;
			$usema = mktime(0, 0, 0, date("m"), date("d"),  date("Y"));
			
			if (empty($diai)) $diai=date('d',$usema) ;
			if (empty($mesi)) $mesi=date('m',$usema) ;
			if (empty($anoi)) $anoi=date('Y',$usema) ;
			
			$qfecha = $anoi.$mesi.$diai  ;
			
			$data['titulo'] = "<center><h2>CIERRE DE CAJA</h2></center>\n";
			$data['forma'] = ""; //site_url()."  ".base_url()." ".$this->config->system_url()." ".getcwd();
			
			$dias = $this->datasis->adia();
			$mes  = $this->datasis->ames();
			$ano  = $this->datasis->aano();
			
			$data['lista'] = form_open("cierrecaja/cierre");
			$data['lista'] .= "\n<table class='bordetabla' width='100%'><tr><td>Fecha:</td></tr>\n<tr><td align=center>";
			$data['lista'] .= form_dropdown("diai",$dias,$diai);
			$data['lista'] .= form_dropdown("mesi",$mes, $mesi);
			$data['lista'] .= form_dropdown("anoi",$ano, $anoi);
			$data['lista'] .= "</td></tr>\n<tr><td align=center>";
			$data['lista'] .= form_submit('enviar','Enviar Consulta');
			$data['lista'] .= "<p><a href='".base_url()."index.php/cierrecaja/resumen/$qfecha'>Resumen de Caja</a></p>";
			
			$mSQL = "SELECT caja, 'Factura' tabla, SUM(gtotal) monto FROM viefac WHERE fecha=$qfecha GROUP BY caja
			   UNION
			         SELECT caja, 'Items' tabla ,SUM(monto) monto FROM vieite WHERE fecha=$qfecha GROUP BY caja
			   UNION
			         SELECT caja, 'Pagos' tabla ,SUM(monto) monto FROM vieite WHERE fecha=$qfecha GROUP BY caja
			   UNION
			         SELECT caja, 'Cierre' tabla ,computa monto FROM dine WHERE fecha=$qfecha GROUP BY caja 
			   ORDER BY caja, tabla";
			
			$query = $this->db->query($mSQL);
			$caja = 'SEDFRDERFGTF';
			if ($query->num_rows() > 0){
				$data['lista'] .= "<table width='100%'>";
				foreach ($query->result() as $row){
					if ( $caja <> $row->caja ) {
						$data['lista'] .= "<tr class='mininegro'><td colspan=2 bgcolor='#ABDDFF'>CAJA ".$row->caja."</td></tr>";
						$caja = $row->caja;
					} 
					$data['lista'] .= "<tr class='mininormal'><td>".$row->tabla."</td><td align=right>".number_format($row->monto,2)."</td></tr>";
				}
					$data['lista'] .= "</table>";
			}	
			$data['lista'] .= "</td></tr>\n</table>\n";
			$data['lista'] .= form_close();

			$mSQL = "SELECT a.caja, a.cajero, SUM(a.gtotal) monto, b.nombre, sum(TRUNCATE(gtotal/60000,0)) cupon 
			  FROM viefac AS a LEFT JOIN scaj AS b ON a.cajero=b.cajero
			  WHERE a.fecha=$qfecha AND substring(a.numero,1,1)!='X'
			  GROUP BY a.caja,a.cajero ";
			
			$query = $this->db->query($mSQL);
			$mcaja = "XCDXXS";
			$count = 1;
			
			if ($query->num_rows() > 0){
				$data['forma'] .= "<table class='bordetabla' width='100%' border=1>\n";
				$caja='XXX' ;
				foreach ($query->result() as $row){
					$cerrado = $this->datasis->dameval("SELECT COUNT(*) cuenta FROM dine WHERE caja='".$row->caja."' AND cajero='".$row->cajero."' AND fecha=$qfecha ");
					if ($mcaja==$row->caja) {
						if ($cerrado==0) {
					  $data['forma'] .= "<a href=\"".base_url()."index.php/cierrecaja/cierre/".$row->caja."/".$row->cajero."/$qfecha\">Cajero ".$row->cajero."</a>\n";
						}else {
							$numero = $this->datasis->dameval("SELECT numero FROM dine WHERE caja='".$row->caja."' AND cajero='".$row->cajero."' AND fecha=$qfecha ");
							$data['forma'] .= "<font color=red >Cajero Cerrado 1 ".$row->cajero." </font>";
							$data['forma'] .= "<a href=\"".base_url()."index.php/cierrecaja/doccierre/".$numero."\">Ver Cuadre</a>";
						};
					$data['forma'] .= "Venta Bs.".number_format($row->monto,2);
					$data['forma'] .= $row->nombre."<br>";
				  } else {
						if ($count > 1) print "</td>\n</tr>\n";
						$count = 4;
						$data['forma'] .= "<tr>\n";
						$data['forma'] .= "<td><img src='".base_url()."images/caja_activa.gif' align=left height=80><h1>".$row->caja."</h1></td>\n";
						$data['forma'] .=  "<td>\n";
						if ($cerrado==0) {
						    $data['forma'] .=  "<a href=\"".base_url()."index.php/cierrecaja/formcierre/".$row->caja."/".$row->cajero."/$qfecha/procesar\">Cajero ".$row->cajero."</a>";
						} else {
					    $numero = $this->datasis->dameval("SELECT numero FROM dine WHERE caja='".$row->caja."' AND cajero='".$row->cajero."' AND fecha=$qfecha ");
					    $data['forma'] .=  "<font color=red>Cajero Cerrado ".$row->cajero." </font>";
					    $data['forma'] .=  "<a href=\"".base_url()."index.php/cierrecaja/doccierre/".$numero."\"> Ver Cuadre </a>";
					$data['forma'] .= "<a href='".base_url()."index.php/cierrecaja/ventasdia/$qfecha/".$row->caja."'>Detalle de Ventas</a>";
				
				    }
				    $data['forma'] .=  "<br>Venta Bs.:".number_format($row->monto,2);
				    $data['forma'] .=  "<br>Nombre: ".$row->nombre;
				    $data['forma'] .=  "<br>Cupones: ".number_format($row->cupon,0);
				    $mcaja=$row->caja;}
				}
				$data['forma'] .= "</table><br>";
				  }
				  $data['forma'] .= "<br><br>";
				  $data['script'] = '';
			} else {
	    $data['titulo'] = "<center><h2>CIERRE DE CAJA</h2></center>\n";
	    $data['forma'] = "<center><h1>DEBE INGRESAR COMO USUARIO</h1></center>";
	    $data['script'] = '';
	    $data['lista'] = '<p>Esta opcion esta reservada para usuarios registrados por favor ingrese con su codigo y contrasena, si no la tiene contacte con el administrador </p>';
		}
		$this->load->view('view_ventas',$data);
  }

	function wlcexcel() 
	{
		$mes = $this->uri->segment(4);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');

		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		$this->db->simple_query($mSQL);
		$tasa = $this->datasis->traevalor('TASA');

		$mSQL = "SELECT DISTINCT 
	    a.sucursal, 
        a.fecha, 
	    a.rif,
	    IF(SUBSTR(a.rif,1,1)='V' AND d.nombre IS NOT NULL,d.nombre,IF(substr(a.rif,1,1)='J' AND d.nombre IS NOT NULL, d.nombre , a.nombre)) AS nombre, 
	    a.contribu,
            a.referen,
            a.planilla,'  ',
            IF(tipo='FC',a.numero,'        ') numero,
	    a.nfiscal,
            IF(a.tipo='ND',a.numero,'        ') numnd,
            IF(a.tipo='NC',a.numero,'        ') numnc,
	    '01-Reg' oper, 
	    '        ' compla, 
	    sum(a.gtotal*IF(a.tipo='NC',-1,1)) gtotal,
            sum(a.exento*IF(a.tipo='NC',-1,1)) exento, 
            sum(a.general*IF(a.tipo='NC',-1,1)) general,
            sum(a.geneimpu*IF(a.tipo='NC',-1,1)) geneimpu,
            sum(a.adicional*IF(a.tipo='NC',-1,1)) adicional,
            sum(a.adicimpu*IF(a.tipo='NC',-1,1)) adicimpu, 
            sum(a.reducida*IF(a.tipo='NC',-1,1)) reducida,
            sum(a.reduimpu*IF(a.tipo='NC',-1,1)) reduimpu, 
            sum(b.reiva*IF(a.tipo='NC',-1,1)) reiva,
            CONCAT(EXTRACT(YEAR_MONTH FROM fechal),b.nrocomp) nrocomp,
	    b.emision, a.numero numo
            FROM siva AS a LEFT JOIN riva AS b ON a.numero=b.numero and a.clipro=b.clipro AND a.tipo=b.tipo_doc AND MID(b.transac,1,1)<>'_' 
	                   LEFT JOIN provoca AS d ON a.rif=d.rif 
            WHERE libro='C' AND EXTRACT(YEAR_MONTH FROM fechal) ='$mes' AND a.fecha>0 
	    GROUP BY a.fecha,a.tipo,numo,a.rif 
	    UNION
	    SELECT DISTINCT a.sucursal, 
            a.fecha, 
	    d.rif,
	    d.nombre, 
	    a.contribu,
            a.referen,
            a.planilla,'  ',
            '*       ' numero,
	    a.nfiscal,
            '        ' numnd,
            '        ' numnc,
	    '01-Reg' oper, 
	    a.referen, 
	    a.gtotal   * 0,
            a.exento   * 0, 
            a.general  * 0,
            a.geneimpu * 0,
            a.adicional* 0,
            a.adicimpu * 0, 
            a.reducida * 0,
            a.reduimpu * 0, 
            sum(b.reiva*IF(a.tipo='NC',-1,1)) reiva,
            CONCAT(EXTRACT(YEAR_MONTH FROM fechal),b.nrocomp) nrocomp,
	    b.emision, a.numero numo
            FROM siva AS a      JOIN riva AS b ON a.numero=b.numero and a.clipro!=b.clipro AND a.tipo=b.tipo_doc AND MID(b.transac,1,1)<>'_' AND a.reiva=b.reiva 
	                   LEFT JOIN sprv AS d ON b.clipro=d.proveed 
            WHERE libro='C' AND EXTRACT(YEAR_MONTH FROM fechal) ='$mes' AND a.fecha>0 AND a.reiva>0 
	    GROUP BY a.fecha,a.tipo,numo,a.rif
	    ORDER BY fecha,numo " ;

		$export = $this->db->query($mSQL);

		//################################################################33
		//
		//  Encabezado
		//
		$fname = tempnam("/tmp","lcompras.xls");

		$this->load->library("workbook", array("fname"=>$fname));
		$wb = & $this->workbook ;
		$ws = & $wb->addworksheet($mes);

		# ANCHO DE LAS COLUMNAS
		$ws->set_column('A:A',6);
		$ws->set_column('B:B',10.5);
		$ws->set_column('D:D',37);
		$ws->set_column('E:E',8);
		$ws->set_column('O:W',12);

		$ws->set_column('Z:Z',12);

		// FORMATOS
		$h       =& $wb->addformat(array( "bold" => 1, "size" => 16, "merge" => 1));
		$h1      =& $wb->addformat(array( "bold" => 1, "size" => 11, "align" => 'left'));
		$titulo  =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 0, "fg_color" => 'silver' ));
		$cuerpo  =& $wb->addformat(array( "size" => 9 ));
		$numero  =& $wb->addformat(array(  "num_format" => '#,##0.00' , "size" => 9 ));
		$Tnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "fg_color" => 'silver' ));
		$Rnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "align" => 'right' ));

		// COMIENZA A ESCRIBIR
		$nomes = substr($mes,4,2);
		$nomes1 = $anomeses[$nomes];
		$hs = "LIBRO DE COMPRAS CORRESPONDIENTE AL MES DE ".$anomeses[$nomes]." DEL ".substr($mes,0,4);

		$ws->write(1, 0, $this->datasis->traevalor('TITULO1') , $h1 );
		$ws->write(2, 0, "RIF: ".$this->datasis->traevalor('RIF') , $h1 );

		$ws->write(4,0, $hs, $h );
		for ( $i=1; $i<20; $i++ ) {
			$ws->write_blank(4, $i,  $h );
		};

		// TITULOS

		$mm=6;
		$ws->write_string( $mm,  0, "", $titulo );
		$ws->write_string( $mm,  1,  "Fecha", $titulo );
		$ws->write_string( $mm,  2,  "", $titulo );
		$ws->write_string( $mm,  3, "", $titulo );
		$ws->write_string( $mm,  4, "", $titulo );
		$ws->write_string( $mm,  5, "Numero", $titulo );
		$ws->write_string( $mm,  6, "Num.Planilla", $titulo );
		$ws->write_string( $mm,  7,  "Num. de", $titulo );
		$ws->write_string( $mm,  8,  "Numero", $titulo );
		$ws->write_string( $mm,  9,  "", $titulo );
		$ws->write_string( $mm, 10,  "Numero", $titulo );
		$ws->write_string( $mm, 11,  "", $titulo );
		$ws->write_string( $mm, 12,  "", $titulo );
		$ws->write_string( $mm, 13,  "Numero de", $titulo );
		$ws->write_string( $mm, 14,  "Total Compras", $titulo );
		$ws->write_string( $mm, 15,  "Compras sin", $titulo );
		$ws->write_string( $mm, 16,  "Compras Internas o Importaciones" , $titulo );
		$ws->write_blank( $mm, 17, $titulo );
		$ws->write_blank( $mm, 18, $titulo );
		$ws->write_blank( $mm, 19, $titulo );
		$ws->write_blank( $mm, 20, $titulo );
		$ws->write_blank( $mm, 21, $titulo );
		$ws->write_string( $mm, 22,  "I.V.A.", $titulo );
		$ws->write_string( $mm, 23,  "Comprobante", $titulo );
		$ws->write_string( $mm, 24,  "Emision" , $titulo );
		$ws->write_string( $mm, 25,  "I.V.A.", $titulo );
		$ws->write_string( $mm, 26,  "Anticipo", $titulo );

		$mm++;
		$ws->write_string( $mm,  0, "Oper.", $titulo );
		$ws->write_string( $mm,  1, "de la", $titulo );
		$ws->write_string( $mm,  2, "R.I.F.", $titulo );
		$ws->write_string( $mm,  3, "", $titulo );
		$ws->write_string( $mm,  4, "Tipo de", $titulo );
		$ws->write_string( $mm,  5, "de", $titulo );
		$ws->write_string( $mm,  6, "de Importacion", $titulo );
		$ws->write_string( $mm,  7, "Expediente", $titulo );
		$ws->write_string( $mm,  8, "de", $titulo );
		$ws->write_string( $mm,  9, "Num. Ctrol.", $titulo );
		$ws->write_string( $mm, 10, "Nota Debit.", $titulo );
		$ws->write_string( $mm, 11, "Numero de", $titulo );
		$ws->write_string( $mm, 12, "Tipo de", $titulo );
		$ws->write_string( $mm, 13, "Factura", $titulo );
		$ws->write_string( $mm, 14, "incluyendo", $titulo );
		$ws->write_string( $mm, 15, "derecho a", $titulo );
		$ws->write_string( $mm, 16, "Alicuota General 14%", $titulo );
		$ws->write_blank( $mm, 17, $titulo );
		$ws->write_string( $mm, 18, "Alicuota General+Adicional 24%", $titulo );
		$ws->write_blank( $mm, 19, $titulo );
		$ws->write_string( $mm, 20, "Alicuota Reducida 8%", $titulo );
		$ws->write_blank( $mm, 21, $titulo );
		$ws->write_string( $mm, 22, "Retenido", $titulo );
		$ws->write_string( $mm, 23, "", $titulo );
		$ws->write_string( $mm, 24, "", $titulo );
		$ws->write_string( $mm, 25, "Retenido", $titulo );
		$ws->write_string( $mm, 26, "de I.V.A.", $titulo );
		$mm++;

		$ws->write_string( $mm,  0, "Nro.", $titulo );
		$ws->write_string( $mm,  1, "Factura", $titulo );
		$ws->write_string( $mm,  2, "Proveedor", $titulo );
		$ws->write_string( $mm,  3, "Nombre o Razon Social", $titulo );
		$ws->write_string( $mm,  4, "Proveed.", $titulo );
		$ws->write_string( $mm,  5, "Comprobt.", $titulo );
		$ws->write_string( $mm,  6, "(C-80 o C-81)", $titulo );
		$ws->write_string( $mm,  7, "Importacion", $titulo );
		$ws->write_string( $mm,  8, "Factura", $titulo );
		$ws->write_string( $mm,  9, "de Factura", $titulo );
		$ws->write_string( $mm, 10, "", $titulo );
		$ws->write_string( $mm, 11, "Nota Crdto.", $titulo );
		$ws->write_string( $mm, 12, "Transacc.", $titulo );
		$ws->write_string( $mm, 13, "Afectada", $titulo );
		$ws->write_string( $mm, 14, "el I.V.A.", $titulo );
		$ws->write_string( $mm, 15, "Credito I.V.A.", $titulo );
		$ws->write_string( $mm, 16, "Base Imponible", $titulo );
		$ws->write_string( $mm, 17, "Imp-I.V.A.", $titulo );
		$ws->write_string( $mm, 18, "Base Imponible", $titulo );
		$ws->write_string( $mm, 19, "Imp-I.V.A.", $titulo );
		$ws->write_string( $mm, 20, "Base Imponible", $titulo );
		$ws->write_string( $mm, 21, "Imp-I.V.A.", $titulo );
		$ws->write_string( $mm, 22, "al vendedor", $titulo );
		$ws->write_string( $mm, 23, "", $titulo );
		$ws->write_string( $mm, 24, "", $titulo );
		$ws->write_string( $mm, 25, "a terceros", $titulo );
		$ws->write_string( $mm, 26, "mportacion", $titulo );
		$mm++;
	
		$ii = 1;
		$mtiva = 'X';
		$mfecha = '2000-00-00';
		$tventas = 0 ;
		$texenta = 0 ;
		$tbase   = 0 ;
		$timpue  = 0 ;
		$treiva  = 0 ;
		$tperci  = 0 ;
		$dd=$mm;  // desde

		foreach( $export->result() as $row ) 
		{
			$ws->write_string( $mm,  0, $ii, $cuerpo );
			$ws->write_string( $mm,  1, substr($row->fecha,8,2)."/".$ameses[substr($row->fecha,5,2)-1]."/".substr($row->fecha,0,4), $cuerpo );
			$ws->write_string( $mm,  2, $row->rif,  $cuerpo );
			$ws->write_string( $mm,  3, $row->nombre,  $cuerpo ); 
			$ws->write_string( $mm,  4, $row->contribu,  $cuerpo ); 
			$ws->write_string( $mm,  5, $row->referen,  $cuerpo ); 
			$ws->write_string( $mm,  6, $row->planilla,  $cuerpo ); 
			$ws->write_string( $mm,  7, $row->compla,  $cuerpo ); 
			$ws->write_string( $mm,  8, $row->numero,  $cuerpo ); 
			$ws->write_string( $mm,  9, $row->nfiscal,  $cuerpo ); 
			$ws->write_string( $mm, 10, $row->numnd, $cuerpo );
			$ws->write_string( $mm, 11, $row->numnc, $cuerpo );
			$ws->write_string( $mm, 12, $row->oper, $cuerpo );
			$ws->write_string( $mm, 13, $row->referen, $cuerpo );
			$ws->write_number( $mm, 14, $row->gtotal, $numero );
			$ws->write_number( $mm, 15, $row->exento, $numero );
			$ws->write_number( $mm, 16, $row->geneimpu/0.14, $numero );
			$ws->write_number( $mm, 17, $row->geneimpu, $numero );
			$ws->write_number( $mm, 18, $row->adicional, $numero );
			$ws->write_number( $mm, 19, $row->adicimpu, $numero );
			$ws->write_number( $mm, 20, $row->reducida, $numero );
			$ws->write_number( $mm, 21, $row->reduimpu, $numero );
			$ws->write_number( $mm, 22, $row->reiva, $numero );
			$ws->write_string( $mm, 23, $row->nrocomp, $cuerpo );
			$ws->write_string( $mm, 24, $row->emision, $cuerpo );
			$ws->write_number( $mm, 25, 0, $numero );
			$ws->write_number( $mm, 26, 0, $numero );
			$mm++;
			$ii++;
		}	

		$celda = $mm+1;
		$fventas = "=J$celda";   // VENTAS
		$fexenta = "=K$celda";   // VENTAS EXENTAS
		$fbase   = "=L$celda";   // BASE IMPONIBLE
		$fiva    = "=N$celda";   // I.V.A. 

		$ws->write( $mm, 0,"Totales...",  $Tnumero );
		$ws->write_blank( $mm,  1,  $Tnumero );
		$ws->write_blank( $mm,  2,  $Tnumero );
		$ws->write_blank( $mm,  3,  $Tnumero );
		$ws->write_blank( $mm,  4,  $Tnumero );
		$ws->write_blank( $mm,  5,  $Tnumero );
		$ws->write_blank( $mm,  6,  $Tnumero );
		$ws->write_blank( $mm,  7,  $Tnumero );
		$ws->write_blank( $mm,  8,  $Tnumero );

		$ws->write_blank( $mm,  9,  $Tnumero );
		$ws->write_blank( $mm, 10,  $Tnumero );
		$ws->write_blank( $mm, 11,  $Tnumero );
		$ws->write_blank( $mm, 12,  $Tnumero );
		$ws->write_blank( $mm, 13,  $Tnumero );

		$ws->write_formula( $mm, 14, "=SUM(O$dd:O$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula( $mm, 15, "=SUM(P$dd:P$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula( $mm, 16, "=SUM(Q$dd:Q$mm)", $Tnumero );   //"BASE IMPONIBLE" 

		$ws->write_formula( $mm, 17, "=SUM(R$dd:R$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula( $mm, 18, "=SUM(S$dd:S$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula( $mm, 19, "=SUM(T$dd:T$mm)", $Tnumero );   //"BASE IMPONIBLE" 

		$ws->write_formula( $mm, 20, "=SUM(U$dd:U$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula( $mm, 21, "=SUM(V$dd:V$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula( $mm, 22, "=SUM(W$dd:W$mm)", $Tnumero );   //"VENTAS EXENTAS" 

		$ws->write_blank( $mm, 23,  $Tnumero );
		$ws->write_blank( $mm, 24,  $Tnumero );

		$ws->write_formula( $mm, 25, "=SUM(Z$dd:Z$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula( $mm, 26, "=SUM(AA$dd:AA$mm)", $Tnumero );   //"VENTAS EXENTAS" 

		$mm ++;
		$mm ++;
		$ws->write($mm, 6, 'RESUMEN:', $h1 );
		$ws->write($mm, 8, 'Compras Internas no Gravadas:', $h1 );
		$ws->write_formula($mm, 15, "=P$celda" , $Rnumero );

		$mm ++;
		$ws->write($mm, 8, 'Compras de Importacion:', $h1 );
		$ws->write_formula($mm, 15, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 17, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 19, "=0+0" , $Rnumero );
		$mm ++;

		$ws->write($mm, 8, 'Compras Internas Alicuota General:', $h1 );
		$ws->write_formula($mm, 15, "=Q$celda+R$celda" , $Rnumero );
		$ws->write_formula($mm, 17, "=Q$celda" , $Rnumero );
		$ws->write_formula($mm, 19, "=R$celda" , $Rnumero );
		$mm ++;

		$ws->write($mm, 8, 'Compras Internas Alicuota General + Adicional:', $h1 );
		$ws->write_formula($mm, 15, "=S$celda+T$celda" , $Rnumero );
		$ws->write_formula($mm, 17, "=S$celda" , $Rnumero );
		$ws->write_formula($mm, 19, "=T$celda" , $Rnumero );
		$mm ++;

		$ws->write($mm, 8, 'Compras Internas Alicuota Reducida:', $h1 );
		$ws->write_formula($mm, 15, "=U$celda+V$celda" , $Rnumero );
		$ws->write_formula($mm, 17, "=U$celda" , $Rnumero );
		$ws->write_formula($mm, 19, "=V$celda" , $Rnumero );
		$mm ++;

		$wb->close();
		header("Content-type: application/x-msexcel; name=\"lcompras.xls\"");
		header("Content-Disposition: inline; filename=\"lcompras.xls\"");
		$fh=fopen($fname,"rb");
		fpassthru($fh);
		unlink($fname);

		print "$header\n$data";
	}

	function wlvexcelpdv() 
	{
        $mes = $this->uri->segment(3);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		$tasa = $this->datasis->traevalor('TASA');

		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE club SET cedula=CONCAT('V',cedula) WHERE MID(cedula,1,1) IN ('0','1','2','3','4','5','6','7','8','9')  ";
		$this->db->simple_query($mSQL);


		$mSQL ="SELECT 
	    a.fecha AS fecha, 
	    a.numero AS numero, 
	    a.numero AS final,
	    c.cedula AS rif, 
	    CONCAT(c.nombres,' ', c.apellidos) AS nombre, 
	    ' ' AS numnc, 
	    ' ' AS numnd, 
	    'FC' AS tipo_doc, 
	    ' ' AS afecta, 
	    SUM(a.monto) ventatotal, 
	    SUM(a.monto*(a.impuesto=0)) exento, 
	    ROUND(SUM(a.monto*(a.impuesto>0)*100/(100+a.impuesto)),2) base, 
	    '14%' AS alicuota, 
	    SUM(a.monto*(a.impuesto>0) - a.monto*(a.impuesto>0)*100/(100+a.impuesto)) AS cgimpu, 
	    0 AS reiva, 
	    ' ' comprobante, 
	    ' ' fechacomp, 
	    ' ' impercibido, 
	    ' ' importacion, 
	    IF(c.cedula IS NOT NULL,IF(MID(c.cedula,1,1) IN ('V','E'), IF(CHAR_LENGTH(MID(c.cedula,2,10))=9,'SI','NO'), 'SI' ), 'NO') tiva, 
	    b.tipo, 
	    a.numero numa, 
	    a.caja
	    FROM vieite a 
	    LEFT JOIN viefac b ON a.numero=b.numero and a.caja=b.caja 
	    LEFT JOIN club c ON b.cliente=c.cod_tar 
	    WHERE EXTRACT(YEAR_MONTH FROM a.fecha)=$mes
	    GROUP BY a.fecha, a.caja, numa
	    UNION
	    SELECT 
	    a.fecha AS fecha,
	    IF(a.tipo='FC', a.numero, '        ' ) AS NUMERO,
	    '        ' AS FINAL,
	    a.rif AS RIF,
	    IF( MID(a.rif,1,1) NOT IN ('J','G') AND b.contacto IS NOT NULL AND b.contacto!='',b.contacto,a.nombre) AS NOMBRE,
	    IF(a.tipo='NC',a.numero,'        ') AS NUMNC,
	    IF(a.tipo='ND',a.numero,'        ') AS NUMND,
	    a.tipo AS TIPO_DOC, 
	    IF(a.referen=a.numero,'        ',a.referen) AS AFECTA,
	    a.gtotal*IF(a.tipo='NC',-1,1) VENTATOTAL,
	    a.exento*IF(a.tipo='NC',-1,1)  EXENTO,
	    a.general*IF(a.tipo='NC',-1,1) BASE,
	    '$tasa%' AS ALICUOTA,
	    a.impuesto*IF(a.tipo='NC',-1,1) AS CGIMPU,
	    a.reiva*IF(a.tipo='NC',-1,1),
	    '              ' COMPROBANTE,
	    '            ' FECHACOMP,
	    '            ' IMPERCIBIDO,
	    '            ' IMPORTACION,
	    'SI' tiva, a.tipo, a.numero numa, 'MAYO' caja
	    FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente
	    WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.fuente<>'PV' AND a.tipo<>'RI' 
	    UNION
	    SELECT 
	    a.fecha AS fecha,
	    IF(a.tipo='FC', a.numero, '        ' ) AS NUMERO,
	    a.numero AS FINAL,
	    a.rif AS RIF,
	    IF(MID(a.rif,1,1) NOT IN ('J','G') AND b.contacto IS NOT NULL AND b.contacto!='',b.contacto,a.nombre) AS NOMBRE,
	    IF(a.tipo='NC',a.numero,'        ') AS NUMNC,
	    IF(a.tipo='ND',a.numero,'        ') AS NUMND,
	    a.tipo AS TIPO_DOC, 
	    IF(a.referen=a.numero,'        ',a.referen) AS AFECTA,
	    a.gtotal*IF(a.tipo='NC',-1,1) VENTATOTAL,
	    a.exento*IF(a.tipo='NC',-1,1)  EXENTO,
	    a.general*IF(a.tipo='NC',-1,1) BASE,
	    '$tasa%' AS ALICUOTA,
	    a.impuesto*IF(a.tipo='NC',-1,1) AS CGIMPU,
	    a.reiva*IF(a.tipo='NC',-1,1),
	    c.nroriva COMPROBANTE,
	    c.emiriva FECHACOMP,
	    '            ' IMPERCIBIDO,
	    '            ' IMPORTACION,
	    'SI' tiva, a.tipo, a.numero numa, 'MAYO' caja
	    FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente LEFT JOIN itccli c ON a.numero=c.numero AND a.clipro=c.cod_cli 
	    WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.fuente<>'PV' AND a.tipo='RI' 
	    ORDER BY fecha, caja, numa ";

	    //echo $mSQL; die;
	    $export = $this->db->query($mSQL);


	    //###############################################################
	    //
	    //  Encabezado
	    //
	    $fname = tempnam("/tmp","lventas.xls");
	    $this->load->library("workbook",array("fname" => $fname));;
	    $wb =& $this->workbook;
	    $ws =& $wb->addworksheet($mes);

	    // ANCHO DE LAS COLUMNAS
	    $ws->set_column('A:A',11);

	    $ws->set_column('B:B',6);

	    $ws->set_column('C:D',8.5);
	    $ws->set_column('E:E',11.5);
	    $ws->set_column('F:F',37);
	    $ws->set_column('K:P',12);

	    // FORMATOS
	    $h      =& $wb->addformat(array( "bold" => 1, "size" => 16, "merge" => 1));
	    $h1     =& $wb->addformat(array( "bold" => 1, "size" => 11, "align" => 'left'));
	    $titulo =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 0, "fg_color" => 'silver' ));
	    $cuerpo =& $wb->addformat(array( "size" => 9 ));
	    $numero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9 ));
	    $Tnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "fg_color" => 'silver' ));
	    $Rnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "align" => 'right' ));

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
	    $ws->write_string( $mm, 2, "Factura", $titulo );
	    $ws->write_string( $mm, 3, "Factura", $titulo );
	    $ws->write_string( $mm, 4, "R.I.F. o", $titulo );
	    $ws->write_string( $mm, 5, "Nombre del", $titulo );
	    $ws->write_string( $mm, 6, "Numero de", $titulo );
	    $ws->write_string( $mm, 7, "Numero de", $titulo );
	    $ws->write_string( $mm, 8, "Tipo", $titulo );
	    $ws->write_string( $mm, 9, "Documento", $titulo );
	    $ws->write_string( $mm,10, "Ventas", $titulo );
	    $ws->write_string( $mm,11, "Ventas", $titulo );
	    $ws->write_string( $mm,12, "Base", $titulo );
	    $ws->write_string( $mm,13, "", $titulo );
	    $ws->write_string( $mm,14, "Monto de", $titulo );
	    $ws->write_string( $mm,15, "I.V.A. ", $titulo );
	    $ws->write_string( $mm,16, "Numero de ", $titulo );
	    $ws->write_string( $mm,17, "Fecha del", $titulo );
	    $ws->write_string( $mm,18, "Impuesto ", $titulo );
	    $ws->write_string( $mm,19, "Numero", $titulo );
	    $ws->write_string( $mm,20, "Contri-", $titulo );
	    $mm++;
	    $ws->write_string( $mm, 0, "Fecha", $titulo );
	    $ws->write_string( $mm, 1, "Caja", $titulo );
	    $ws->write_string( $mm, 2, "Inicial", $titulo );
	    $ws->write_string( $mm, 3, "Final", $titulo );
	    $ws->write_string( $mm, 4, "Cedula", $titulo );
	    $ws->write_string( $mm, 5, "Contribuyente", $titulo );
	    $ws->write_string( $mm, 6, "N.Credito", $titulo );
	    $ws->write_string( $mm, 7, "N.Debito.", $titulo );
	    $ws->write_string( $mm, 8, "Doc.", $titulo );
	    $ws->write_string( $mm, 9, "Afectado", $titulo );
	    $ws->write_string( $mm,10, "Totales", $titulo );
	    $ws->write_string( $mm,11, "Exentas", $titulo );
	    $ws->write_string( $mm,12, "Imponible", $titulo );
	    $ws->write_string( $mm,13, "Alicuota %", $titulo );
	    $ws->write_string( $mm,14, "I.V.A.", $titulo );
	    $ws->write_string( $mm,15, "Retenido", $titulo );
	    $ws->write_string( $mm,16, "Comprobante", $titulo );
	    $ws->write_string( $mm,17, "Comprobante", $titulo );
	    $ws->write_string( $mm,18, "Percibido", $titulo );
	    $ws->write_string( $mm,19, "Importacion", $titulo );
	    $ws->write_string( $mm,20, "buyente", $titulo );
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
	    $caja = 'zytrdsefg';

	    foreach( $export->result() as  $row ) {
		if ($caja == 'zytrdsefg') $caja=$row->caja;

		    // chequea la fecha
		    if ( $mfecha == $row->fecha ) {
			// Dentro del dia
			if ($caja == $row->caja) {
			    if ( $row->tiva == 'SI' ) {
				$mforza = 1;
				$contri = 1;
			    } else {
				if ( $row->tipo == 'NC' ) {
				    $mforza = 1;
				    $contri = 1;
				} else {
				    $mforza = 0;
				    $contri = 0;
				    if ($finicial == '99999999') $finicial=$row->numero;
				};
			    };
			} else {
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

		    if ( ($finicial == '99999999' or empty($finicial)) and !empty($row->numero) ) { 
		};


		if ( $mforza ) {
		// si tventas > 0 imprime totales
		    if ( $tventas <> 0 ) {
    			if ( $finicial == '99999999' ) $finicial = $ffinal;
			$fecha = substr($mfecha,8,2)."/".$ameses[substr($mfecha,5,2)-1]."/".substr($mfecha,0,4);
    			$ws->write_string( $mm, 0, $fecha,  $cuerpo );		// Fecha
    			$ws->write_string( $mm, 1, $caja,  $cuerpo );		// Fecha
    			$ws->write_string( $mm, 2, $finicial, $cuerpo );       	// Factura Inicial
    			$ws->write_string( $mm, 3, $ffinal, $cuerpo );         	// Factura Final
			$ws->write_string( $mm, 4, '  ******  ', $cuerpo );    	// RIF/CEDULA
        		$ws->write_string( $mm, 5, 'VENTAS A NO CONTRIBUYENTES', $cuerpo );    // Nombre
        		$ws->write_string( $mm, 6, '', $cuerpo );          		// Nro. N.C.
        		$ws->write_string( $mm, 7, '', $cuerpo );    		// Nro. N.D.
        		$ws->write_string( $mm, 8, 'RE', $cuerpo );    		// TIPO
			$ws->write_string( $mm, 9, '' , $cuerpo );    		// DOC. AFECTADO
        		$ws->write_number( $mm,10, $tventas, $numero );    		// VENTAS + IVA

			$num = $mm + 1;
			$ws->write_formula( $mm,11,"=K$num - M$num - O$num" , $numero );   // EXENTO

        		$ws->write_number( $mm,12,$timpue/0.14, $numero );   		// BASE IMPONIBLE

        		$ws->write_number( $mm,13, 14, $numero );   		// ALICOUTA %

			$ws->write_number( $mm,14,$timpue , $numero );  // I.V.A." 
	    
        		$ws->write_number( $mm,15, 0, $numero );         	// IVA RETENIDO
        		$ws->write_string( $mm,16, '', $cuerpo );        	// NRO COMPROBANTE
        		$ws->write_string( $mm,17, '', $cuerpo );        	// ECHA COMPROB
        		$ws->write_number( $mm,18, $tperci, $numero );   	// IMPUESTO PERCIBIDO
        		$ws->write_string( $mm,19, 0, $cuerpo );         	// IMPORTACION
        		$ws->write_string( $mm,20, 'NO', $cuerpo );      	// CONTRIBUYENTE
			$tventas = 0 ;
			$texenta = 0 ;
			$tbase   = 0 ;
			$timpue  = 0 ;
			$treiva  = 0 ;
			$tperci  = 0 ;
        		if ( $row->tipo_doc == 'FC' ) {
	    		    $finicial = $row->numero;
			} else {
			    $finicial = '99999999';
			};
        		$mm++;
			$caja = $row->caja;
		    };
		};
		if ( $contri ) {
		// imprime contribuyente
    		    $fecha = $row->fecha;
		    $fecha = substr($fecha,8,2)."/".$ameses[substr($fecha,5,2)-1]."/".substr($fecha,0,4);
    		    $ws->write_string( $mm, 0, $fecha,           $cuerpo );        // Fecha
    		    $ws->write_string( $mm, 1, $row->caja,       $cuerpo );     // Caja
    		    $ws->write_string( $mm, 2, $row->numero,     $cuerpo );   // Factura Inicial
    		    $ws->write_string( $mm, 3, '',               $cuerpo );             // Factura Final
    		    $ws->write_string( $mm, 4, $row->rif,        $cuerpo );   // RIF/CEDULA
    		    $ws->write_string( $mm, 5, $row->nombre,     $cuerpo );   // Nombre
    		    $ws->write_string( $mm, 6, $row->numnc,      $cuerpo );   // Nro. N.C.
    		    $ws->write_string( $mm, 7, $row->numnd,      $cuerpo );   // Nro. N.D.
    		    $ws->write_string( $mm, 8, $row->tipo_doc,   $cuerpo );   // TIPO
    		    $ws->write_string( $mm, 9, $row->afecta,     $cuerpo );   // DOC. AFECTADO
    		    $ws->write_number( $mm,10, $row->ventatotal, $numero );   // VENTAS + IVA
    		    $ws->write_number( $mm,11, $row->exento,     $numero );   // VENTAS EXENTAS
    		    $ws->write_number( $mm,12, $row->base,       $numero );   // BASE IMPONIBLE
    		    $ws->write_number( $mm,13, $row->alicuota,   $numero );   // ALICOUTA %
		    $num = $mm+1;
    		    $ws->write_formula( $mm,14,"=M$num*N$num/100" , $numero );   //I.V.A.
    		    $ws->write_number( $mm,15, $row->reiva,         $numero );   // IVA RETENIDO
    		    $ws->write_string( $mm,16, $row->comprobante,   $cuerpo );   // NRO COMPROBANTE
    		    $ws->write_string( $mm,17, $row->fechacomp,     $cuerpo );   // FECHA COMPROB
    		    $ws->write_number( $mm,18, $row->impercibido,   $numero );   // IMPUESTO PERCIBIDO
    		    $ws->write_string( $mm,19, $row->importacion,   $cuerpo );   // IMPORTACION
    		    $ws->write_string( $mm,20, $row->tiva,          $cuerpo );   // CONTRIBUYENTE
		    $finicial = '99999999';
    		    $mm++;
		} else {
    		// Totaliza
    		    $tventas += $row->ventatotal ;
		    $texenta += $row->exento ;
		    $tbase   += $row->base ;
		    $timpue  += $row->cgimpu ;
		    $treiva  += $row->reiva ;
		    $tperci  += $row->impercibido ;
    		    if ( $finicial == '99999999' ) $finicial=$row->numero;
		    if ( substr($row->final,0,2)!='NC')	$ffinal=$row->final;
		};

		$mfecha = $row->fecha;
		$caja = $row->caja;
	    }

	    //Imprime el Ultimo

	if ( $tventas <> 0 ) {
	    $fecha = substr($mfecha,8,2)."/".$ameses[substr($mfecha,5,2)-1]."/".substr($mfecha,0,4);
	    $ws->write_string( $mm, 0, $fecha,  $cuerpo );         			// Fecha
	    $ws->write_string( $mm, 1, $caja,  $cuerpo );         			// Caja
	    $ws->write_string( $mm, 2, $finicial, $cuerpo );				//"Factura Inicial" 
	    $ws->write_string( $mm, 3, $ffinal, $cuerpo );				//"Factura Final" 
	    $ws->write_string( $mm, 4, '  ******  ', $cuerpo );				//"RIF/CEDULA" 
	    $ws->write_string( $mm, 5, 'VENTAS A NO CONTRIBUYENTES', $cuerpo );		//"Nombre" 
	    $ws->write_string( $mm, 6, '', $cuerpo );					//"Nro. N.C." 
	    $ws->write_string( $mm, 7, '', $cuerpo );					//"Nro. N.D." 
	    $ws->write_string( $mm, 8, 'RE', $cuerpo );					//"TIPO" 
	    $ws->write_string( $mm, 9, '' , $cuerpo );					//"DOC. AFECTADO" 
	    $ws->write_number( $mm,10, $tventas, $numero );				//"VENTAS + IVA" 
	    //    $ws->write_number( $mm,11, $texenta, $numero );				//"VENTAS EXENTAS" 
	    $num = $mm+1;

	    $ws->write_formula( $mm,11,"=K$num - M$num - O$num" , $numero );   // EXENTO
	    //    $ws->write_number( $mm,12, $tbase, $numero );				//"BASE IMPONIBLE" 
	    $ws->write_number( $mm,12, $timpue/0.14, $numero );   		// BASE IMPONIBLE
	    $ws->write_number( $mm,13, 14, $numero );					//"ALICOUTA %" 

	    //    $ws->write_formula( $mm,14,"=L$num*M$num/100" , $numero );			//"I.V.A." 
	    $ws->write_number( $mm,14,$timpue , $numero );  // I.V.A." 

	    //    $ws->write_number( $mm,14, $timpue, $numero );   //"I.V.A." 
	    $ws->write_number( $mm,15, 0, $numero );   //"IVA RETENIDO" 
	    $ws->write_string( $mm,16, '', $cuerpo );   //"NRO COMPROBANTE" 
	    $ws->write_string( $mm,17, '', $cuerpo );   //"FECHA COMPROB" 
	    $ws->write_number( $mm,18, $tperci, $numero );   //"IMPUESTO PERCIBIDO" 
	    $ws->write_string( $mm,19, 0, $cuerpo );   //"IMPORTACION" 
	    $ws->write_string( $mm,20, 'NO', $cuerpo );   //"CONTRIBUYENTE" 
	    $mm++;
	};

	$celda = $mm+1;
	$fventas = "=K$celda";   // VENTAS
	$fexenta = "=L$celda";   // VENTAS EXENTAS
	$fbase   = "=M$celda";   // BASE IMPONIBLE
	$fiva    = "=O$celda";   // I.V.A. 


	$ws->write( $mm, 0,"Totales...",  $Tnumero );
	$ws->write_blank( $mm, 1,  $Tnumero );
	$ws->write_blank( $mm, 2,  $Tnumero );
	$ws->write_blank( $mm, 3,  $Tnumero );
	$ws->write_blank( $mm, 4,  $Tnumero );
	$ws->write_blank( $mm, 5,  $Tnumero );
	$ws->write_blank( $mm, 6,  $Tnumero );
	$ws->write_blank( $mm, 7,  $Tnumero );
	$ws->write_blank( $mm, 8,  $Tnumero );
	$ws->write_blank( $mm, 9,  $Tnumero );

	$ws->write_formula( $mm,10, "=SUM(K$ii:K$mm)", $Tnumero );   //"VENTAS + IVA" 
	$ws->write_formula( $mm,11, "=SUM(L$ii:L$mm)", $Tnumero );   //"VENTAS EXENTAS" 
	$ws->write_formula( $mm,12, "=SUM(M$ii:M$mm)", $Tnumero );   //"BASE IMPONIBLE" 
	//$ws->write_formula( $mm,13, "=SUM(M7:M$mm)", $Tnumero );   //"ALICOUTA %" 
	$ws->write_blank( $mm, 13,  $Tnumero );

	$ws->write_formula( $mm,14, "=SUM(O$ii:O$mm)", $Tnumero );   //"I.V.A." 
	$ws->write_formula( $mm,15, "=SUM(P$ii:P$mm)", $Tnumero );   //"IVA RETENIDO" 

	$ws->write_blank( $mm, 16,  $Tnumero );
	$ws->write_blank( $mm, 17,  $Tnumero );

	$ws->write_formula( $mm,18, "=SUM(S$ii:S$mm)", $Tnumero );   //"IMPUESTO PERCIBIDO" 

	$ws->write_blank( $mm, 19,  $Tnumero );
	$ws->write_blank( $mm, 20,  $Tnumero );

	$mm ++;
	$mm ++;
	$ws->write($mm, 3, 'RESUMEN:', $h1 );
	$ws->write($mm, 5, 'Ventas Internas no Gravadas:', $h1 );
	$ws->write_formula($mm, 10, "=L$celda" , $Rnumero );

	$mm ++;
	$ws->write($mm, 5, 'Ventas de Exportacion:', $h1 );
	$ws->write_formula($mm, 10, "=0+0" , $Rnumero );
	$ws->write_formula($mm, 12, "=0+0" , $Rnumero );
	$ws->write_formula($mm, 14, "=0+0" , $Rnumero );

	$mm ++;
	$ws->write($mm,5, 'Ventas Internas Alicuota General:', $h1 );
	$ws->write_formula($mm, 10, "=M$celda+O$celda" , $Rnumero );
	$ws->write_formula($mm, 12, "=M$celda" , $Rnumero );
	$ws->write_formula($mm, 14, "=O$celda" , $Rnumero );

	$mm ++;

	$ws->write($mm,5, 'Ventas Internas Alicuota General + Adicional:', $h1 );
	$ws->write_formula($mm, 10, "=0+0" , $Rnumero );
	$ws->write_formula($mm, 12, "=0+0" , $Rnumero );
	$ws->write_formula($mm, 14, "=0+0" , $Rnumero );

	$mm ++;

	$ws->write($mm,5, 'Ventas Internas Alicuota Reducida:', $h1 );
	$ws->write_formula($mm, 10, "=0+0" , $Rnumero );
	$ws->write_formula($mm, 12, "=0+0" , $Rnumero );
	$ws->write_formula($mm, 14, "=0+0" , $Rnumero );

	$mm ++;

	$wb->close();
	header("Content-type: application/x-msexcel; name=\"lventas.xls\"");
	header("Content-Disposition: inline; filename=\"lventas.xls\"");
	$fh=fopen($fname,"rb");
	fpassthru($fh);
	unlink($fname);

	print "$header\n$data";

    }
    
	function prorrata(){
		$anomes = $this->uri->segment(3);
		$mes = $anomes;
		if (substr($anomes,4,2) == '01') $nomes = 'ENERO' ;
		if (substr($anomes,4,2) == '02') $nomes = 'FEBRERO' ;
		if (substr($anomes,4,2) == '03') $nomes = 'MARZO' ;
		if (substr($anomes,4,2) == '04') $nomes = 'ABRIL' ;
		if (substr($anomes,4,2) == '05') $nomes = 'MAYO' ;
		if (substr($anomes,4,2) == '06') $nomes = 'JUNIO' ;
		if (substr($anomes,4,2) == '07') $nomes = 'JULIO' ;
		if (substr($anomes,4,2) == '08') $nomes = 'AGOSTO' ;
		if (substr($anomes,4,2) == '09') $nomes = 'SEPTIEMBRE' ;
		if (substr($anomes,4,2) == '10') $nomes = 'OCTUBRE' ;
		if (substr($anomes,4,2) == '11') $nomes = 'NOVIEMBRE' ;
		if (substr($anomes,4,2) == '12') $nomes = 'DICIEMBRE' ;
		
		
		$data['titulo']  ="<TABLE width=\"90%\"><tr>\n";
		$data['titulo'] .="<TD align=left><img src=\"/ci4/images/moderna_logo.jpg\" height=\"60\" ></TD>\n";
		$data['titulo'] .="<TD align=center><H2>".$this->datasis->traevalor('TITULO1')."</H2><CENTER>RIF: ".$this->datasis->traevalor('RIF')."</CENTER></TD>\n";
		$data['titulo'] .="<TD align=right></TD>\n";
		$data['titulo'] .="</TR>\n";
		$data['titulo'] .="</TABLE><br>\n";
		$data['titulo'] .="<b class=\"style3\">INFORME DE PRORRATEO </b><br>\n";
		$data['titulo'] .="<b>PARA EL MES DE $nomes\n";
		$data['titulo'] .=" DEL ".substr($anomes,0,4)."</b><BR>\n";
		
		//DEBITOS
		$mSQL = "SELECT 
			sum((general+reducida+adicional)*IF(tipo IN ('NC','DE'),-1,1 )) gravadas,
			sum(stotal*IF(tipo IN ('NC','DE'),-1,1 )) vtotal,
			sum((geneimpu+reduimpu+adicimpu)*IF(tipo IN ('NC','DE'),-1,1 )) ivatotal
			FROM siva 
			WHERE EXTRACT(YEAR_MONTH FROM fechal)=$anomes AND libro='V' AND tipo!='RI' 
			GROUP BY 'A' ";
		$export = $this->db->query($mSQL);
		
		$data['debitos'] = "<br>\n";
		if ( $export->num_rows() ) 
		{
			$row = $export->row();
			$prorrata=round($row->gravadas*100/$row->vtotal,2);
			
			$data['cuerpo']  = "<BR>\n";
			$data['cuerpo'] .= "<table valign=\"center\" class=\"tabla2\" width=\"90%\">\n";
			$data['cuerpo'] .= "<TD colspan=5 align=left><b>CALCULO DE LA PORCION DEDUCIBLE</b> </TD>\n";
			$data['cuerpo'] .= "<TR bgcolor=\"#c0c0c0\">\n";
			$data['cuerpo'] .= "   <TD align=center>Ventas Gravadas</TD>\n";
			$data['cuerpo'] .= "   <TD align=center> / </TD>\n";
			$data['cuerpo'] .= "   <TD align=center>Ventas Totales</TD>\n";
			$data['cuerpo'] .= "   <TD align=center> = </TD>\n";
			$data['cuerpo'] .= "   <TD align=center>Prorrata%</TD>\n";
			$data['cuerpo'] .= "   <TD align=center>Debito Fiscal</TD>\n";
			$data['cuerpo'] .= "</TR>\n";
			$data['cuerpo'] .= "<TR>";
			$data['cuerpo'] .= "<TD align=center><b> ".number_format($row->gravadas,2)."</b> </TD>";
			$data['cuerpo'] .= "<TD align=center>/</TD>";
			$data['cuerpo'] .= "<TD align=center><b> ".number_format($row->vtotal,2)."</b> </TD>";
			$data['cuerpo'] .= "<TD align=center>=</TD>";
			$data['cuerpo'] .= "<TD align=center><b> ".number_format($prorrata,2)."</b> </TD>";
			$data['cuerpo'] .= "<TD align=center><b> ".number_format($row->ivatotal,2)."</b> </TD>";
			$data['cuerpo'] .= "</TR></TABLE>\n";
		}
		$export->free_result();
		
		// SUMARIO DE CREDITOS FISCALES
		$mSQL = "SELECT 
			sum((geneimpu+reduimpu+adicimpu)*(fuente IN ('CP','MC','MP'))*IF(tipo IN ('NC','DE'),-1,1 )) dedu,
		  		sum((geneimpu+reduimpu+adicimpu)*(fuente NOT IN ('CP','MC','MP'))*IF(tipo IN ('NC','DE'),-1,1 )) nodedu,
			sum((geneimpu+reduimpu+adicimpu)*IF(tipo IN ('NC','DE'),-1,1 )) dtotal
		  		FROM siva WHERE EXTRACT(YEAR_MONTH FROM fecha)=$anomes AND libro='C'  AND tipo<>'RI'
		  		GROUP BY 'A' ";
		
		$export = $this->db->query($mSQL);
		if ( $export->num_rows() ) {
			$row = $export->row();
			
			$data['cuerpo'] .= "</TABLE> <br>\n";
			$data['cuerpo'] .= "<B>AJUSTE Y APLICACION DEL PRORRATEO</B>\n";
			$data['cuerpo'] .= "<TABLE valign=\"center\" class=\"tabla2\" width=\"90%\">\n";
			$data['cuerpo'] .= "  <TR><TD colspan=2 align=left><b>SUMARIO DE CREDITOS FISCALES</b> </TD></TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Creditos Totalmente Deducibles</TD>\n";
			$data['cuerpo'] .= "    <TD align='right' >".number_format($row->dedu,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Creditos no Deducibles</TD>\n";
			$data['cuerpo'] .= "    <TD align='right' >".number_format(0,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Creditos Parcialmente Deducibles</TD>\n";
			$data['cuerpo'] .= "    <TD align='right'>".number_format($row->nodedu,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Total Creditos Fiscales</TD>\n";
			$data['cuerpo'] .= "    <TD align='right'>".number_format($row->dedu+$row->nodedu,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "</TABLE> <br>\n";
			$data['cuerpo'] .= "<BR>\n";
			
			$data['cuerpo'] .= "<TABLE valign=\"center\" class=\"tabla2\" width=\"90%\">\n";
			$data['cuerpo'] .= "  <TR><TD colspan=2 align=left><b>SUMARIO DE CREDITOS SUJETOS A PRORRATEO</b> </TD></TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Creditos Parcialmente Deducibles</TD>\n";
			$data['cuerpo'] .= "    <TD align='right'>".number_format($row->nodedu,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Creditos Deducibles (Aplicacion Prorrata: ".number_format($row->nodedu,2)." * ".number_format($prorrata,2)."%) </TD>\n";
			$data['cuerpo'] .= "    <TD align='right'>".number_format($row->nodedu*$prorrata/100,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Porcion sin derecho a Credito</TD>\n";
			$data['cuerpo'] .= "    <TD align='right'>".number_format($row->nodedu-$row->nodedu*$prorrata/100,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			
			$data['cuerpo'] .= "</TABLE> <br>\n";
			$data['cuerpo'] .= "<BR>\n";
			$data['cuerpo'] .= "<TABLE valign=\"center\" class=\"tabla2\" width=\"90%\">\n";
			$data['cuerpo'] .= "  <TR><TD colspan=2 align=left><b>CREDITOS DEDUCIBLES DESPUES DEL PRORRATEO</b> </TD></TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Total Creditos Fiscales</TD>\n";
			$data['cuerpo'] .= "    <TD align='right'>".number_format($row->dedu+$row->nodedu,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Porcion sin Derecha a Credito</TD>\n";
			$data['cuerpo'] .= "    <TD align='right'>".number_format($row->nodedu-$row->nodedu*$prorrata/100,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left>Creditos no Deducibles</TD>\n";
			$data['cuerpo'] .= "    <TD align='right'>".number_format(0,2)."</TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "</TABLE>\n";
			$data['cuerpo'] .= "<br>\n";
			$data['cuerpo'] .= "<TABLE valign=\"center\" class=\"tabla2\" >\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "    <TD align=left><b>TOTAL CREDITOS FISCALES DEDUCIBLES...</b></TD>\n";
			$data['cuerpo'] .= "    <TD align='right'><B>".number_format($row->dedu+$row->nodedu*$prorrata/100 ,2)."</b></TD>\n";
			$data['cuerpo'] .= "  <TR>\n";
			$data['cuerpo'] .= "</TABLE>\n";
		}
			$this->load->view('view_prorrata',$data);
	}    

	function invresu(){
		if (isset($_POST)) extract($_POST);
		if (!isset($calcular))  $calcular='';
		if (!isset($generar))   $generar='';
		if (!isset($cambiarsf)) $cambiarsf='';
		if (!isset($mes))       $mes=200701;
		if (!empty($generar)) invresum(200701);
		
		if (!empty($cambiarsf)) {
			_ajustainv($mes, $cambia);
			$calcular = "Yes";
		}
		$salida  = "<FORM method='POST' name='forma'>";
		$salida .= "<table width='90%'><tr><td>";
		$salida .= "<table align='center'>";
		$salida .= "<tr><td>Periodo a Calcular </td><td><input type='text' name='mes' value=<?=$mes?> size=7></td></tr>";
		$salida .= "<tr><td><INPUT type='submit' name='calcular' value='Consultar' /></td><td><input type='submit' name='generar' value='Generar' /></td></tr>";
		$salida .= "</table></td><td>";
		
		if (!empty($calcular) ){
			$mSQL = "SELECT sum(minicial), sum(mcompras), sum(mventas), sum(mfinal) FROM invresu WHERE mes=$mes ";
			$mC   = damecur($mSQL);
			$row  = mysql_fetch_row($mC);
			
			echo "<TABLE align='center'>\n";
			echo "<TR><td>SALDO INICIAL </td><TD align='right'>".number_format($row[0],2)."</TD></TR>\n";
			echo "<TR><td>COMPRAS       </td><TD align='right'>".number_format($row[1],2)."</TD></TR>\n";
			echo "<TR><td>VENTAS        </td><TD align='right'>".number_format($row[2],2)."</TD></TR>\n";
			echo "<TR><td>SALDO FINAL   </td><TD align='right'>".number_format($row[3],2)."</TD></TR>\n";
			echo "<TR><td>NUEVO SALDO FINAL   </td><TD align='right'><INPUT type='TEXT' name='cambia' value='".number_format($row[3],2)."'></TD></TR>\n";
			echo "<TR><td></td><TD align='center'><INPUT type='submit' name='cambiarsf' value='Ajustar Saldo'></TD></TR>\n";
			echo "</TABLE'>\n";
		}
		
		//Pone los saldos
		$salida .= "</table>";
		$salida .= "</td></tr></table>";
		$salida .= "</FORM>";
	}
    // Ajusta al valor 
	function _ajustainv($mes, $cambia){
		$cambia = str_replace(",","", $cambia );
		$mSQL = "SELECT sum(minicial), sum(mcompras), sum(mventas), sum(mfinal) FROM invresu WHERE mes=$mes ";
		$mC   = damecur($mSQL);
		$row  = mysql_fetch_row($mC);
		$difer = $row[3]-$cambia;
		$factor = ( $row[2] + $difer ) / $row[2];
		//echo "$cambia  $difer  $factor";
		ejecutasql("UPDATE invresu SET mventas=mventas*".$factor." WHERE mes=$mes");    
		saldofinal($mes);
		  }
		
		
		  // Calcula el Inventario
		  function _invresum($mes)
		  {
		
		$mesa = _restames($mes);
		$this->db->simple_query("DELETE FROM invresu WHERE mes=$mes");
		
		$mSQL = "INSERT INTO invresu
		SELECT 
		EXTRACT(YEAR_MONTH FROM a.fecha) AS mes, 
		a.codigo, b.descrip, 0 AS inicial, 
		sum(a.cantidad*(a.origen IN ('2C','2D'))*IF(a.origen='2D',-1,1)) AS compras, 
		sum(a.cantidad*(a.origen IN ('3I','3M') )) AS ventas, 
		sum(a.cantidad*(a.origen='1T')) AS trans, 
		sum((a.cantidad-a.anteri)*(a.origen IN ('0F','8F'))) AS fisico,  
		sum(a.cantidad*(a.origen='4N')) AS notas,  
		0 AS final,  0 AS minicial,  
		sum(a.monto*(a.origen IN ('2C','2D'))*IF(a.origen='2D',-1,1)) AS mcompras, 
		sum(a.cantidad*a.promedio*(a.origen IN ('3I','3M'))) AS mventas, 
		sum(a.cantidad*a.promedio*(a.origen='1T')) AS mtrans, 
		sum((a.cantidad-a.anteri)*a.promedio*(a.origen IN ('0F','8F'))) AS mfisico, 
		sum(a.cantidad*a.promedio*(a.origen='4N')) AS mnotas, 
		0 AS mfinal, sum(venta)  
		FROM costos AS a LEFT JOIN sinv AS b ON a.codigo=b.codigo  
		WHERE EXTRACT(YEAR_MONTH FROM a.fecha)=$mes  AND MID(b.tipo,1,1)!='S' 
		GROUP BY EXTRACT(YEAR_MONTH FROM a.fecha),a.codigo ";
		$this->db->simple_query($mSQL);
		
		//Insertamos los del mes pasado que no tienen movimiento este mes
		$this->db->simple_query("INSERT IGNORE INTO invresu (mes, codigo,descrip, inicial, compras,ventas, trans, fisico,notas,final, minicial, mcompras, mventas, mtrans, mfisico, mnotas, mfinal ) SELECT $mes, codigo, descrip, 0, 0,0,0,0,0,0,0,0,0,0,0,0,0 FROM invresu WHERE mes=$mesa");
		
		//Eliminar notas y transferencias
		$this->db->simple_query("UPDATE invresu SET ventas=ventas+notas, mventas=mventas+mnotas, notas=0, mnotas=0 WHERE mes=$mes ");
		$this->db->simple_query("UPDATE invresu SET ventas=ventas-trans, mventas=mventas-mtrans, trans=0, mtrans=0 WHERE mes=$mes ");
		$this->db->simple_query("UPDATE invresu SET fisico=0, mfisico=0 WHERE mes=$mes ");
		
		// Busca Saldo Inicial
		$this->db->simple_query("UPDATE invresu a JOIN invresu b ON a.codigo=b.codigo AND a.mes=$mesa AND b.mes=$mes SET b.minicial=a.mfinal, b.inicial=a.final ");
		
		// Calcula Saldo Final
		_saldofinal($mes);
		
		// Elimina Negativos  quita la venta en exeso
		$this->db->simple_query("UPDATE invresu SET ventas=inicial+compras, mventas=minicial+mcompras WHERE mes=$mes AND final<0 ");
		
		_saldofinal($mes);
		
		$calcular='Consultar';
	}

	function wlvexcel() 
	{
		$mes = $this->uri->segment(4);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		$aaa = $this->datasis->ivaplica($mes."01");
		$tasa      = $aaa['tasa'];
		$redutasa  = $aaa['redutasa'];
		$sobretasa = $aaa['sobretasa'];

		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		$this->db->simple_query($mSQL);
		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		$this->db->simple_query($mSQL);

		$mSQL  = "
		SELECT 
		a.fecha fecha,
		a.numero numero,
		a.nfiscal final,
		a.rif rif,
		IF( MID(a.rif,1,1) NOT IN ('J','G') AND b.contacto IS NOT NULL AND b.contacto!='',b.contacto,a.nombre) AS nombre,
		a.tipo tipo, 
		IF(a.referen=a.numero,'        ',a.referen) afecta,
		a.gtotal*IF(a.tipo='NC',-1,1) ventatotal,
		a.exento*IF(a.tipo='NC',-1,1)  exento,
		a.general*IF(a.tipo='NC',-1,1) base,
		a.impuesto*IF(a.tipo='NC',-1,1) impuesto,
		a.reiva*IF(a.tipo='NC',-1,1) reiva,
		'              ' comprobante,
		'            ' fechacomp,
		'            ' impercibido,
		'            ' importacion,
		a.general,
		a.geneimpu,
		a.adicional,
		a.adicimpu,
		a.reducida,
		a.reduimpu,
		a.contribu 
		FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente
		WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.tipo<>'FA' 
		ORDER BY a.fecha, IF(a.tipo IN ('FC','XE','XC'),1,2), numero ";

		$export = $this->db->query($mSQL);

		################################################################
		#
		#  Encabezado
		#
	    $fname = tempnam("/tmp","lventas.xls");
	    $this->load->library("workbook",array("fname" => $fname));;
	    $wb =& $this->workbook;
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

		$ws->write(1, 0, $this->datasis->traevalor('TITULO1') , $h1 );
		$ws->write(2, 0, "RIF: ".$this->datasis->traevalor('RIF') , $h1 );

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

		$mm +=3;

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

		foreach( $export->result() as $row ) 
		{
    		// imprime contribuyente
    		$fecha = substr($row['fecha'],8,2)."/".$ameses[substr($row['fecha'],5,2)-1]."/".substr($row['fecha'],0,4);
    		$ws->write_string( $mm, 0, $fecha,  $cuerpo );				// Fecha
    		$ws->write_string( $mm, 1, $row['nombre'], $cuerpo );		// Nombre
    		$ws->write_string( $mm, 2, $row['rif'], $cuerpo );			// RIF/CEDULA
    		if ( $row[7] == "XE" ) 
				$ws->write_string( $mm, 3, "FC", $cuerpoc );			// TIPO
    		else
				$ws->write_string( $mm, 3, $row['tipo'], $cuerpoc );	// TIPO
    
    		$ws->write_string( $mm, 4, $row['mumero'], $cuerpo );		// Nro. Documento
    		if ( $row[7] == "XE" ) 
				$ws->write_string( $mm, 5, "03-Anu", $cuerpoc );		// TIPO Transac
    		else
				$ws->write_string( $mm, 5, "01-Reg", $cuerpoc );		// TIPO Transac
    
    		$ws->write_string( $mm, 6, $row['afecta'], $cuerpo );		// DOC. AFECTADO
    		$ws->write_string( $mm, 7, $row['contribu'], $cuerpo );		// CONTRIBUYENTE
    		$ws->write_number( $mm, 8, $row['ventatotal'], $numero );	// VENTAS + IVA
    		$ws->write_number( $mm, 9, $row['exento'], $numero );   	// VENTAS EXENTAS
    		$ws->write_number( $mm,10, 0, $cuerpo );   					// EXPORTACION

    		$ws->write_number( $mm,11, $row['general'], $numero );		// GENERAL
    		$ws->write_number( $mm,12, $row['geneimpu'], $numero );		// GENEIMPU
    		$ws->write_number( $mm,13, $row['adicional'], $numero );	// ADICIONAL
    		$ws->write_number( $mm,14, $row['adicimpu'], $numero );		// ADICIMPU
    		$ws->write_number( $mm,15, $row['reducida'], $numero );		// REDUCIDA
    		$ws->write_number( $mm,16, $row['reduimpu'], $numero );		// REDUIMPU

    		$ws->write_number( $mm,17, $row['reiva'], $numero );		// IVA RETENIDO
    		$ws->write_string( $mm,18, $row['comprobante'], $cuerpo );	// NRO COMPROBANTE
    		$ws->write_string( $mm,19, $row['fechacomp'], $cuerpo );	// FECHA COMPROB
    		$ws->write_number( $mm,20, $row['impercibido'], $numero );	// IMPUESTO PERCIBIDO
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
		print "$header\n$data";
		
    }	
	
	function _saldofinal($mes){
		// Calcula Saldo Final
		$this->db->simple_query("UPDATE invresu SET final=inicial+compras-ventas-notas+trans+fisico, mfinal=minicial+mcompras-mventas-mnotas+mtrans+mfisico WHERE mes=$mes ");
	}
	function _restames($mes) {
		$ano = substr($mes,0,4);
		$mes = substr($mes,5,2);
		$mes = $mes-1;
		if ( $mes == 0 ){ 
			$mes = '12';
			$ano = $ano - 1 ;
		}
		return "$ano$mes";
	}
}
?>