<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Libros extends Controller {

	function Libros() {
		parent::Controller();
	}
	function index() {
		$mSQL='
		SELECT YEAR(curdate())-4, YEAR(curdate())-4 UNION
		SELECT YEAR(curdate())-3, YEAR(curdate())-3 UNION
		SELECT YEAR(curdate())-2, YEAR(curdate())-2 UNION
		SELECT YEAR(curdate())-1, YEAR(curdate())-1 UNION
		SELECT YEAR(curdate()), YEAR(curdate()) ';
		$anhos=$this->datasis->consularray($mSQL);
		$direccion= "'".base_url()."finanzas/libros/'+this.form.tarch.value+'/'+this.form.year.value+this.form.mes.value";
		$redi=$this->datasis->form2uri('finanzas','libros',array('tarch', 'year', 'mes'));
		$tarch=array(
//		'wlvexcelpdv' =>'Libro de Ventas PDV',
		'wlvexcel'    =>'Libro de Ventas ',
		'wlcexcel'    =>'Libro de Compras',
//		'wlvexcele'   =>'Libro de Ventas  ESPECIAL',
//		'wlcexcele'   =>'Libro de Compras ESPECIAL',
		'prorrata'    =>'Prorrata',
		'invresu'     =>'Libro de Inventario',
		'genecompras' =>'Generar Libro de compras COMPRAS',
		'genegastos'  =>'Generar Libro de compras GASTOS',
		'genecxp'     =>'Generar Libro de compras CXP',
//		'genesfac'    =>'Generar Libro de ventas Facturas',
		'generest'    =>'Generar Libro de ventas Restaurante',
		'genehotel'   =>'Generar Libro de ventas Hotel',
		'genesmov'    =>'Generar Libro de ventas CXC',
		'geneotin'    =>'Generar Libro de ventas O.Ingresos' );
		
		$data['titulo']  = "<H1>OBLIGACIONES TRIBUTARIAS</H1><br><br>";
		$data['titulo'] .= form_open(); 
		$data['titulo'] .= "<table width='200'><tr><td>";
		$data['titulo'] .= form_dropdown('tarch',$tarch,'','size=18');
		$data['titulo'] .= "</td><td>";
		$data['titulo'] .= form_dropdown('year',$anhos,'','size=18');
		$data['titulo'] .= "</td><td>";
		$data['titulo'] .= form_dropdown('mes',$this->datasis->ames(),'','size=18');
		$data['titulo'] .= "</td><td>";
//		$data['titulo'] .= '<input type="button"  name="pasa" value="Ejecutar" onClick="redireccionar('.$direccion.');" />';
		$data['titulo'] .= '<input type="button"  name="pasatest" value="Ejecutar" onClick="'.$redi.'" />';
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


//
//   LIBRO DE COMPRAS CONTRIBUYENTE NORMAL
//
//  
	function wlcexcel()
	{
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
		$ano = $this->uri->segment(3);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');

		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		$this->db->simple_query($mSQL);
		
		$aa = $this->datasis->ivaplica($mes.'02');
		$tasa      = $aa['tasa'];
		$redutasa  = $aa['redutasa'];
		$sobretasa = $aa['sobretasa'];
		

		$mSQL = "SELECT DISTINCT a.sucursal, a.fecha, a.rif,
	    if(e.proveed IS NULL, if(d.nombre IS NULL or d.nombre='', a.nombre, d.nombre ), if(e.nombre IS NULL or e.nombre='', a.nombre, if(e.nomfis='',e.nombre,e.nomfis)) ) nombre, 
	    a.contribu,
        a.referen,
        a.planilla,
        '     ' nose,
        IF(a.tipo='FC',a.numero,'        ') numero,
	    a.nfiscal,
        IF(a.tipo='ND',a.numero,'        ') numnd,
        IF(a.tipo='NC',a.numero,'        ') numnc,
	    a.registro oper, 
	    '        ' compla, 
	    sum(a.gtotal   *IF(a.tipo='NC',-1,1)) gtotal,
        sum(a.exento   *IF(a.tipo='NC',-1,1)) exento, 
        sum(a.general  *IF(a.tipo='NC',-1,1)) general,
        sum(a.geneimpu *IF(a.tipo='NC',-1,1)) geneimpu,
        sum(a.adicional*IF(a.tipo='NC',-1,1)) adicional,
        sum(a.adicimpu *IF(a.tipo='NC',-1,1)) adicimpu, 
        sum(a.reducida *IF(a.tipo='NC',-1,1)) reducida,
        sum(a.reduimpu *IF(a.tipo='NC',-1,1)) reduimpu, 
        sum(b.reiva    *IF(a.tipo='NC',-1,1)) reiva,
        CONCAT(EXTRACT(YEAR_MONTH FROM fechal),b.nrocomp) nrocomp,
	    b.emision, a.numero numo, a.tipo tipo_doc
        FROM siva AS a LEFT JOIN riva AS b ON a.numero=b.numero and a.clipro=b.clipro AND a.tipo=b.tipo_doc AND MID(b.transac,1,1)<>'_' 
	                   LEFT JOIN provoca AS d ON a.rif=d.rif 
	                   LEFT JOIN sprv AS e ON a.clipro=e.proveed 
        WHERE libro='C' AND EXTRACT(YEAR_MONTH FROM fechal) ='$mes' AND a.fecha>0 
	    GROUP BY a.fecha,a.tipo,numo,a.rif 
	    UNION ALL 
	    SELECT DISTINCT a.sucursal, 
        a.fecha, 
	    d.rif,
	    d.nomfis nombre, 
	    a.contribu,
        a.referen,
        a.planilla,'  ' aaa,
        '*       ' numero,
	    a.nfiscal,
        '        ' numnd,
        '        ' numnc,
	    a.registro oper, 
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
	    b.emision, a.numero numo, a.tipo
        FROM siva AS a JOIN riva AS b ON a.numero=b.numero and a.clipro!=b.clipro AND a.tipo=b.tipo_doc AND MID(b.transac,1,1)<>'_' AND a.reiva=b.reiva 
	                   LEFT JOIN sprv AS d ON b.clipro=d.proveed 
        WHERE libro='C' AND EXTRACT(YEAR_MONTH FROM fechal) ='$mes' AND a.fecha>0 AND a.reiva>0 
	    GROUP BY a.fecha,a.tipo,numo,a.rif
	    ORDER BY fecha,numo ";

//		$export = $this->db->query($mSQL);

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
	$ws->set_column('B:B',10);
	$ws->set_column('C:C',6);
	$ws->set_column('D:E',10);
	
	$ws->set_column('F:F',37);
	$ws->set_column('G:G',14);
	$ws->set_column('H:W',12);

	$ws->set_column('Z:Z',12);

	// FORMATOS
	$h       =& $wb->addformat(array( "bold" => 1, "size" => 16, "merge" => 1));
	$h1      =& $wb->addformat(array( "bold" => 1, "size" => 11, "align" => 'center'));
	$h2      =& $wb->addformat(array( "bold" => 1, "size" => 14, "align" => 'left', "fg_color" => 'silver'  ));
	$h3      =& $wb->addformat(array( "bold" => 1, "size" => 11, "align" => 'right'));
	
	
	$titulo  =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 0, "fg_color" => 'silver' ));
	$cuerpo  =& $wb->addformat(array( "size" => 9 ));
	$numero  =& $wb->addformat(array(  "num_format" => '#,##0.00' , "size" => 9 ));
	$Tnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "fg_color" => 'silver' ));
	$Rnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "align" => 'right' ));

	// COMIENZA A ESCRIBIR
	$nomes = substr($mes,4,2);
	$nomes1 = $anomeses[$nomes];
	$hs = "LIBRO DE COMPRAS CORRESPONDIENTE AL MES DE ".$anomeses[$nomes]." DEL ".substr($mes,0,4)."   ".$mes;

	$ws->write(1, 0, $this->datasis->traevalor('TITULO1') , $h1 );
	$ws->write(2, 0, "RIF: ".$this->datasis->traevalor('RIF') , $h1 );

	$ws->write(4,0, $hs, $h );
	for ( $i=1; $i<20; $i++ ) {
		$ws->write_blank(4, $i,  $h );
	};

	// TITULOS

	$mm=6;
	$ws->write_string( $mm,   0, "", $titulo );
	$ws->write_string( $mm+1, 0, "Oper.", $titulo );
	$ws->write_string( $mm+2, 0, "Nro.", $titulo );

	$ws->write_string( $mm,   1, "", $titulo );
	$ws->write_string( $mm+1, 1, "Fecha", $titulo );
	$ws->write_string( $mm+2, 1, "", $titulo );

	$ws->write_string( $mm,   2, "Documento", $titulo );
	$ws->write_string( $mm+1, 2, "Tipo",      $titulo );
	$ws->write_string( $mm+2, 2, "", $titulo );

	$ws->write_blank( $mm,    3,  $titulo );
	$ws->write_string( $mm+1, 3, "Numero",      $titulo );
	$ws->write_string( $mm+2, 3, "", $titulo );
	
	$ws->write_string( $mm,   4, "Numero de",    $titulo );
	$ws->write_string( $mm+1, 4, "Declaracion",  $titulo );
	$ws->write_string( $mm+2, 4, "de Aduanas",      $titulo );

	$ws->write_string( $mm,    5, "Nombre, Razon Social", $titulo );
	$ws->write_string( $mm+1,  5, "o", $titulo );
	$ws->write_string( $mm+2,  5, "Denominacion del Proveedor", $titulo );
	
	$ws->write_string( $mm,    6, "Numero", $titulo );
	$ws->write_string( $mm+1,  6, "de", $titulo );
	$ws->write_string( $mm+2,  6, "R.I.F.", $titulo );

	$ws->write_string( $mm,    7, "Total Compras", $titulo );
	$ws->write_string( $mm+1,  7, "incluyendo", $titulo );
	$ws->write_string( $mm+2,  7, "el I.V.A.", $titulo );
	
	$ws->write_string( $mm,    8, "Compras No", $titulo );
	$ws->write_string( $mm+1,  8, "Gravadas o sin", $titulo );
	$ws->write_string( $mm+2,  8, "derecho a C.F.", $titulo );

	$ws->write_string( $mm,    9, "Compras de Importacion Gravadas", $titulo );
	$ws->write_string( $mm+1,  9, "Alicuota General", $titulo );
	$ws->write_string( $mm+2,  9, "Base", $titulo );
	
	$ws->write_blank( $mm,    10, $titulo );
	$ws->write_blank( $mm+1,  10, $titulo );
	$ws->write_string( $mm+2, 10, "Impuesto", $titulo );

	$ws->write_blank( $mm,    11, $titulo );
	$ws->write_string( $mm+1, 11, "Alicuota Adicional",$titulo );
	$ws->write_string( $mm+2, 11, "Base", $titulo );

	$ws->write_blank( $mm,    12, $titulo );
	$ws->write_blank( $mm+1,  12, $titulo );
	$ws->write_string( $mm+2, 12, "Impuesto", $titulo );
	

	$ws->write_blank( $mm,    13, $titulo );
	$ws->write_string( $mm+1, 13, "Alicuota Reducida",$titulo );
	$ws->write_string( $mm+2, 13, "Base", $titulo );

	$ws->write_blank( $mm,    14, $titulo );
	$ws->write_blank( $mm+1,  14, $titulo );
	$ws->write_string( $mm+2, 14, "Impuesto", $titulo );
	
	$ws->write_string( $mm,   15, "Compras Internas Gravadas o con derecho a Credito Fiscal", $titulo );
	$ws->write_string( $mm+1, 15, "Alicuota General", $titulo );
	$ws->write_string( $mm+2, 15, "Base", $titulo );
	
	$ws->write_blank( $mm,    16, $titulo );
	$ws->write_blank( $mm+1,  16, $titulo );
	$ws->write_string( $mm+2, 16, "Impuesto", $titulo );

	$ws->write_blank( $mm,    17, $titulo );
	$ws->write_string( $mm+1, 17, "Alicuota Adicional",$titulo );
	$ws->write_string( $mm+2, 17, "Base", $titulo );

	$ws->write_blank( $mm,    18, $titulo );
	$ws->write_blank( $mm+1,  18, $titulo );
	$ws->write_string( $mm+2, 18, "Impuesto", $titulo );
	

	$ws->write_blank( $mm,    19, $titulo );
	$ws->write_string( $mm+1, 19, "Alicuota Reducida",$titulo );
	$ws->write_string( $mm+2, 19, "Base", $titulo );

	$ws->write_blank( $mm,    20, $titulo );
	$ws->write_blank( $mm+1,  20, $titulo );
	$ws->write_string( $mm+2, 20, "Impuesto", $titulo );

	$ws->write_string( $mm,   21, "Ajuste a los", $titulo );
	$ws->write_string( $mm+1, 21, "Creditos F.", $titulo );
	$ws->write_string( $mm+2, 21, "P. Anteriores", $titulo );
	
	$ws->write_string( $mm,   22, "I.V.A.", $titulo );
	$ws->write_string( $mm+1, 22, "Retenido al", $titulo );
	$ws->write_string( $mm+2, 22, "Vendedor", $titulo );
	
	$ws->write_string( $mm,   23, "Numero", $titulo );
	$ws->write_string( $mm+1, 23, "de", $titulo );
	$ws->write_string( $mm+2, 23, "Comprobante", $titulo );
	
	$mm++;
	$mm++;
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

	//die($mSQL);
	$mc = $this->db->query($mSQL);
	if ( $mc->num_rows() > 0 ) 
	{
		foreach( $mc->result() as $row ) 
		{

			$ws->write_string( $mm,  0, $ii, $cuerpo );
			$ws->write_string( $mm,  1, substr($row->fecha,8,2)."/".substr($row->fecha,5,2)."/".substr($row->fecha,0,4), $cuerpo );
			$ws->write_string( $mm,  2, $row->tipo_doc,  $cuerpo ); 
			$ws->write_string( $mm,  3, $row->numo,  $cuerpo ); 

			$ws->write_string( $mm,  5, $row->nombre,  $cuerpo ); 
			$ws->write_string( $mm,  6, $row->rif,  $cuerpo );
			if ($row->oper != '04' )
			{
			    $ws->write_number( $mm,  7, $row->gtotal, $numero );
			    $ws->write_number( $mm,  8, $row->exento, $numero );

			    $ws->write_number( $mm, 15, $row->general, $numero );
			    $ws->write_number( $mm, 16, $row->geneimpu, $numero );
			    $ws->write_number( $mm, 17, $row->adicional, $numero );
			    $ws->write_number( $mm, 18, $row->adicimpu, $numero );
			    $ws->write_number( $mm, 19, $row->reducida, $numero );
			    $ws->write_number( $mm, 20, $row->reduimpu, $numero );
			} else {
			    $ws->write_number( $mm,  7, 0, $numero );
			    $ws->write_number( $mm,  8, 0, $numero );

			    $ws->write_number( $mm, 15, 0, $numero );
			    $ws->write_number( $mm, 16, 0, $numero );
			    $ws->write_number( $mm, 17, 0, $numero );
			    $ws->write_number( $mm, 18, 0, $numero );
			    $ws->write_number( $mm, 19, 0, $numero );
			    $ws->write_number( $mm, 20, 0, $numero );
			    $ws->write_number( $mm, 21, $row->reduimpu+$row->geneimpu+$row->adicimpu, $numero );
			}

			$ws->write_number( $mm, 22, $row->reiva, $numero );
			$ws->write_string( $mm, 23, $row->nrocomp, $cuerpo );

			$mm++;
			$ii++;
		}
	}


	$celda = $mm+1;
	$fventas = "=G$celda";   // VENTAS
	$fexenta = "=H$celda";   // VENTAS EXENTAS
	$fbase   = "=P$celda";   // BASE IMPONIBLE
	$fiva    = "=Q$celda";   // I.V.A. 

	$ws->write( $mm, 0,"Totales...",  $Tnumero );
	$ws->write_blank( $mm,  1,  $Tnumero );
	$ws->write_blank( $mm,  2,  $Tnumero );
	$ws->write_blank( $mm,  3,  $Tnumero );
	$ws->write_blank( $mm,  4,  $Tnumero );
	$ws->write_blank( $mm,  5,  $Tnumero );
	$ws->write_blank( $mm,  6,  $Tnumero );

	$ws->write_formula( $mm,  7, "=SUM(H$dd:H$mm)", $Tnumero );   //"VENTAS EXENTAS" 
	$ws->write_formula( $mm,  8, "=SUM(I$dd:I$mm)", $Tnumero );   //"BASE IMPONIBLE" 
	$ws->write_formula( $mm,  9, "=SUM(J$dd:J$mm)", $Tnumero );   //"VENTAS + IVA" 
	$ws->write_formula( $mm, 10, "=SUM(K$dd:K$mm)", $Tnumero );   //"VENTAS EXENTAS" 
	$ws->write_formula( $mm, 11, "=SUM(L$dd:L$mm)", $Tnumero );   //"BASE IMPONIBLE" 
	$ws->write_formula( $mm, 12, "=SUM(M$dd:M$mm)", $Tnumero );   //"VENTAS + IVA" 
	$ws->write_formula( $mm, 13, "=SUM(N$dd:N$mm)", $Tnumero );   //"VENTAS EXENTAS" 
	$ws->write_formula( $mm, 14, "=SUM(O$dd:O$mm)", $Tnumero );   //"VENTAS EXENTAS" 
	$ws->write_formula( $mm, 15, "=SUM(P$dd:P$mm)", $Tnumero );   //"VENTAS + IVA" 
	$ws->write_formula( $mm, 16, "=SUM(Q$dd:Q$mm)", $Tnumero );   //"VENTAS EXENTAS" 
	$ws->write_formula( $mm, 17, "=SUM(R$dd:R$mm)", $Tnumero );   //"VENTAS EXENTAS" 
	$ws->write_formula( $mm, 18, "=SUM(S$dd:S$mm)", $Tnumero );   //"VENTAS + IVA" 
	$ws->write_formula( $mm, 19, "=SUM(T$dd:T$mm)", $Tnumero );   //"VENTAS EXENTAS" 
	$ws->write_formula( $mm, 20, "=SUM(U$dd:U$mm)", $Tnumero );   //"VENTAS EXENTAS" 
	$ws->write_formula( $mm, 21, "=SUM(V$dd:V$mm)", $Tnumero );   //"VENTAS + IVA" 
	$ws->write_formula( $mm, 22, "=SUM(W$dd:W$mm)", $Tnumero );   //"VENTAS EXENTAS" 


	$mm ++;
	$mm ++;
	$ws->write_string($mm, 1, 'RESUMEN DE COMPRAS Y CREDITOS', $h2 );
	$ws->write_blank($mm, 2, $titulo );	
	$ws->write_blank($mm, 3, $titulo );	
	$ws->write_blank($mm, 4, $titulo );	
	$ws->write_blank($mm, 5, $titulo );	
	$ws->write($mm, 6, 'Items', $titulo );
	$ws->write($mm, 7, 'Base Imponible', $titulo );
	$ws->write($mm, 8, 'Items', $titulo );
	$ws->write($mm, 9, 'Credito Fiscal', $titulo );

	$mm ++;
	$ws->write($mm, 5, 'Total Compras no Gravadas o sin Derecho a Credito Fiscal', $h3 );
	$ws->write_blank($mm, 2, $h1 );	
	$ws->write_blank($mm, 3, $h1 );	
	$ws->write_blank($mm, 4, $h1 );	
	//$ws->write_blank($mm, 5, $h1 );	
	$ws->write($mm, 6, '30', $h1 );
	$ws->write_formula($mm, 7, "=I$celda" , $Rnumero );
//	$ws->write($mm, 8, '30', $h1 );
//	$ws->write_formula($mm, 7, "=H$celda" , $Rnumero );


	$mm ++;
	$mTot = $mm;
	$ws->write($mm, 5, 'Total Importaciones Gravadas por Alicuota General', $h3 );
	$ws->write_blank($mm, 2, $h1 );	
	$ws->write_blank($mm, 3, $h1 );	
	$ws->write_blank($mm, 4, $h1 );	
//	$ws->write_blank($mm, 5, $h1 );	
	$ws->write($mm, 6, '31', $h1 );
	$ws->write_formula($mm, 7, "=J$celda" , $Rnumero );
	$ws->write($mm, 8, '32', $h1 );
	$ws->write_formula($mm, 9, "=K$celda" , $Rnumero );

	$mm ++;
	$ws->write($mm, 5, 'Total Importaciones Gravadas por Alicuota General mas Adicional', $h3 );
	$ws->write_blank($mm, 2, $h1 );	
	$ws->write_blank($mm, 3, $h1 );	
	$ws->write_blank($mm, 4, $h1 );	
	//$ws->write_blank($mm, 5, $h1 );	
	$ws->write($mm, 6, '312', $h1 );
	$ws->write_formula($mm, 7, "=L$celda" , $Rnumero );
	$ws->write($mm, 8, '322', $h1 );
	$ws->write_formula($mm, 9, "=M$celda" , $Rnumero );

	$mm ++;
	$ws->write($mm, 5, 'Total Importaciones Gravadas por Alicuota Reducida', $h3 );
	$ws->write_blank($mm, 2, $h1 );	
	$ws->write_blank($mm, 3, $h1 );	
	$ws->write_blank($mm, 4, $h1 );	
	//$ws->write_blank($mm, 5, $h1 );	
	$ws->write($mm, 6, '313', $h1 );
	$ws->write_formula($mm, 7, "=N$celda" , $Rnumero );
	$ws->write($mm, 8, '323', $h1 );
	$ws->write_formula($mm, 9, "=O$celda" , $Rnumero );

	$mm ++;
	$ws->write($mm, 5, 'Total Compras Internas Gravadas por Alicuota General', $h3 );
	$ws->write_blank($mm, 2, $h1 );	
	$ws->write_blank($mm, 3, $h1 );	
	$ws->write_blank($mm, 4, $h1 );	
	//$ws->write_blank($mm, 5, $h1 );	
	$ws->write($mm, 6, '33', $h1 );
	$ws->write_formula($mm, 7, "=P$celda" , $Rnumero );
	$ws->write($mm, 8, '34', $h1 );
	$ws->write_formula($mm, 9, "=Q$celda" , $Rnumero );

	$mm ++;
	$ws->write($mm, 5, 'Total Compras Internas Gravadas por Alicuota General mas Adicional', $h3 );
	$ws->write_blank($mm, 2, $h1 );	
	$ws->write_blank($mm, 3, $h1 );	
	$ws->write_blank($mm, 4, $h1 );	
	//$ws->write_blank($mm, 5, $h1 );	
	$ws->write($mm, 6, '332', $h1 );
	$ws->write_formula($mm, 7, "=R$celda" , $Rnumero );
	$ws->write($mm, 8, '342', $h1 );
	$ws->write_formula($mm, 9, "=S$celda" , $Rnumero );

	$mm ++;
	$ws->write($mm, 5, 'Total Compras Internas Gravadas por Alicuota Reducida', $h3 );
	$ws->write_blank($mm, 2, $h1 );	
	$ws->write_blank($mm, 3, $h1 );	
	$ws->write_blank($mm, 4, $h1 );	
	//$ws->write_blank($mm, 5, $h1 );	
	$ws->write($mm, 6, '333', $h1 );
	$ws->write_formula($mm, 7, "=T$celda" , $Rnumero );
	$ws->write($mm, 8, '343', $h1 );
	$ws->write_formula($mm, 9, "=U$celda" , $Rnumero );

	$mm1=$mm;
	$mm ++;
	$ws->write($mm, 1, 'Total Compras y Creditos para efectos de determinacion', $h2 );
	$ws->write_blank($mm, 2, $h2 );	
	$ws->write_blank($mm, 3, $h2 );	
	$ws->write_blank($mm, 4, $h2 );	
	$ws->write_blank($mm, 5, $h2 );	
	$ws->write($mm, 6, '35', $titulo );
	$ws->write_formula($mm, 7, "=SUM(H$mTot:H$mm)" , $Tnumero );
	$ws->write($mm, 8, '36', $titulo );
	$ws->write_formula($mm, 9, "=SUM(J$mTot:J$mm)" , $Tnumero );
	
	
/*
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
*/
	$wb->close();
	header("Content-type: application/x-msexcel; name=\"lcompras.xls\"");
	header("Content-Disposition: inline; filename=\"lcompras.xls\"");
	$fh=fopen($fname,"rb");
	fpassthru($fh);
	unlink($fname);
	print "$header\n$data";
	}

	
//
//   LIBRO DE VENTAS CON PTO DE VENTAS CONTRIBUYENTE NORMAL
//
//
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


		$mSQL ="SELECT a.fecha AS fecha, a.numero AS numero, a.numero AS final, c.cedula AS rif, 
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
		    ' ' fecharece, 
	    	' ' impercibido, 
		    ' ' importacion, 
		    IF(c.cedula IS NOT NULL,IF(MID(c.cedula,1,1) IN ('V','E'), IF(CHAR_LENGTH(MID(c.cedula,2,10))=9,'SI','NO'), 'SI' ), 'NO') tiva, 
	    	b.tipo, 
		    a.numero numa, 
		    a.caja
	    	FROM vieite a 
		    LEFT JOIN viefac b ON a.numero=b.numero and a.caja=b.caja 
		    LEFT JOIN club c ON b.cliente=c.cod_tar 
	    	WHERE EXTRACT(YEAR_MONTH FROM a.fecha)=$year$mes
		    GROUP BY a.fecha, a.caja, numa
		    UNION ALL
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
		    WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$year$mes AND a.libro='V' AND a.fuente<>'PV' AND a.tipo<>'RI' 
		    UNION ALL 
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
		    WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$year$mes AND a.libro='V' AND a.fuente<>'PV' AND a.tipo='RI' 
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
	    //$nomes = substr($mes,4,2);
	    $nomes1 = $anomeses[$mes];
	    $hs = "LIBRO DE VENTAS CORRESPONDIENTE AL MES DE ".$anomeses[$nomes]." DEL $year";

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

	    foreach( $export->result() as  $row ) 
	    {
			if ($caja == 'zytrdsefg') $caja=$row->caja;

		    // chequea la fecha
		    if ( $mfecha == $row->fecha ) 
		    {
				// Dentro del dia
				if ($caja == $row->caja) 
				{
			    	if ( $row->tiva == 'SI' ) {
					$mforza = 1;
					$contri = 1;
			    	} else {
						if ( $row->tipo == 'NC' ) 
						{
				    		$mforza = 1;
				    		$contri = 1;
						} else {
				    		$mforza = 0;
				    		$contri = 0;
				    		if ($finicial == '99999999') $finicial=$row->numero;
						}
			    	}
				} else {
			    	if ($finicial == '99999999') $finicial=$row->numero;
			    	$mforza = 1;
			    	if ( $row->tiva == 'SI' ) 
						$contri = 1;
			     	else 
						$contri = 0;
				}
	
			} else {
				// Imprime todo
				if ($finicial == '99999999') $finicial=$row->numero;
				$mforza = 1;
				if ( $row->tiva == 'SI' ) 
			    	$contri = 1;
				else 
			    	$contri = 0;
				if ( $row->tipo == 'NC' ) $contri = 1;  
			}

		    if ( ($finicial == '99999999' or empty($finicial)) and !empty($row->numero) ) 
		    { 
			}


			if ( $mforza ) 
			{
				// si tventas > 0 imprime totales
				if ( $tventas <> 0 ) 
				{
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
					if ( $row->tipo_doc == 'FC' ) 
						$finicial = $row->numero;
					else 
						$finicial = '99999999';
					$mm++;
					$caja = $row->caja;
				}
			}
			if ( $contri ) 
			{
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
				$ws->write_string( $mm,17, $row->fecharece,     $cuerpo );   // FECHA COMPROB
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
				if ( substr($row->final,0,2)!='NC' ) $ffinal=$row->final;
			};

			$mfecha = $row->fecha;
			$caja = $row->caja;
		}

	    //Imprime el Ultimo

		if ( $tventas <> 0 ) 
		{
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
		}

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
	function _ajustainv($mes, $cambia)
	{
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

	function _saldofinal($mes){
		// Calcula Saldo Final
		$this->db->simple_query("UPDATE invresu SET final=inicial+compras-ventas-notas+trans+fisico, mfinal=minicial+mcompras-mventas-mnotas+mtrans+mfisico WHERE mes=$mes ");
  }
	function _restames($mes){
		$ano = substr($mes,0,4);
		$mes = substr($mes,5,2);
		$mes = $mes-1;
		if ( $mes == 0 ){ 
			$mes = '12';
			$ano = $ano - 1 ;
		}
		return "$ano$mes";
	}


//***********************************************
//
//  LIBRO DE VENTAS NORMAL
//
//***********************************************
	function wlvexcel() 
	{
		$mes = $this->uri->segment(4).$this->uri->segment(5);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');

		$aaa = $this->datasis->ivaplica($mes."02");
		$tasa      = $aaa['tasa'];
		$redutasa  = $aaa['redutasa'];
		$sobretasa = $aaa['sobretasa'];

		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		$this->db->simple_query($mSQL);
		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE siva SET tipo='FC' WHERE tipo IN ('FE','FF') ";
		$this->db->simple_query($mSQL);

		$mSQL  = "SELECT 
				a.fecha,
				a.numero, '' inicial, ' ' final,
				a.nfiscal,
				a.rif,
				IF( b.nomfis IS NOT NULL AND b.nomfis!='',b.nomfis,b.nombre) AS nombre,
				a.tipo, 
				IF(a.referen=a.numero,'        ',a.referen) afecta,
				a.gtotal*IF(a.tipo='NC',-1,1)    ventatotal,
				a.exento*IF(a.tipo='NC',-1,1)    exento,
				a.general*IF(a.tipo='NC',-1,1)   base,
				a.impuesto*IF(a.tipo='NC',-1,1)  impuesto,
				a.reiva*IF(a.tipo='NC',-1,1)     reiva,
				a.general*IF(a.tipo='NC',-1,1)	 general,
				a.geneimpu*IF(a.tipo='NC',-1,1)  geneimpu,
				a.adicional*IF(a.tipo='NC',-1,1) adicional,
				a.adicimpu*IF(a.tipo='NC',-1,1)  adicimpu,
				a.reducida*IF(a.tipo='NC',-1,1)  reducida,
				a.reduimpu*IF(a.tipo='NC',-1,1)  reduimpu,
				a.contribu, a.registro, a.comprobante, a.fecharece 
			FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente
			WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.tipo<>'FA' AND a.contribu='CO' 
			UNION ALL
			SELECT 
				a.fecha,
				' ' numero, min(a.numero) inicial, max(a.numero) final,
				' ' nfiscal,
				' ' rif,
				'****TOTAL DIA A NO CONTRIBUYENTES' nombre,
				a.tipo, 
				' ' afecta,
				sum(a.gtotal*IF(a.tipo='NC',-1,1))    ventatotal,
				sum(a.exento*IF(a.tipo='NC',-1,1))    exento,
				sum(a.general*IF(a.tipo='NC',-1,1))   base,
				sum(a.impuesto*IF(a.tipo='NC',-1,1))  impuesto,
				sum(a.reiva*IF(a.tipo='NC',-1,1))     reiva,
				sum(a.general*IF(a.tipo='NC',-1,1))	  general,
				sum(a.geneimpu*IF(a.tipo='NC',-1,1))  geneimpu,
				sum(a.adicional*IF(a.tipo='NC',-1,1)) adicional,
				sum(a.adicimpu*IF(a.tipo='NC',-1,1))  adicimpu,
				sum(a.reducida*IF(a.tipo='NC',-1,1))  reducida,
				sum(a.reduimpu*IF(a.tipo='NC',-1,1))  reduimpu,
				'NO' contribu, '01' registro, ' ' comprobante, null fecharece
			FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente
			WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.tipo<>'FA' AND a.contribu='NO' AND a.tipo IN ('FC','NC')
			GROUP BY a.fecha, a.tipo
			ORDER BY fecha, IF(MID(tipo,1,1) IN ('F','X'),1,2), numero ";
//diee($mSQL);
		$export = $this->db->query($mSQL);

/*		
		'              ' comprobante,
		'            ' fechacomp,
		'            ' impercibido,
		'            ' importacion,
*/
		
		################################################################
		#
		#  Encabezado
		#
	    $fname = tempnam("/tmp","lventas.xls");
	    $this->load->library("workbook",array("fname" => $fname));;
	    $wb =& $this->workbook;
	    $ws =& $wb->addworksheet($mes);

		// ANCHO DE LAS COLUMNAS
		$ws->set_column('A:F',11);
		$ws->set_column('G:G',37);
		$ws->set_column('H:U',11);

		// FORMATOS
		$h      =& $wb->addformat(array( "bold" => 1, "size" => 16, "merge" => 1));
		$h1     =& $wb->addformat(array( "bold" => 1, "size" => 11, "align" => 'left'));

		$titulo =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 0, "fg_color" => 'silver' ));
		$tt =& $wb->addformat(array( "size" => 9, "merge" => 1, "fg_color" => 'silver' ));

		$cuerpo  =& $wb->addformat(array( "size" => 9 ));
		$cuerpoc =& $wb->addformat(array( "size" => 9, "align" => 'center', "merge" => 1 ));
		$cuerpob =& $wb->addformat(array( "size" => 9, "align" => 'center', "bold" => 1, "merge" => 1 ));

		$numero  =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9 ));
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

		//$ws->write_string( $mm, $ii , $mvalor );

		$mm=6;
		$mcel = 0;
		// TITULOS
		$ws->write_string( $mm,   $mcel, "", $titulo );
		$ws->write_string( $mm+1, $mcel, "Fecha", $titulo );
		$ws->write_string( $mm+2, $mcel, "", $titulo );
		$mcel++;
		
		$ws->write_string( $mm,   $mcel, "Identificacion del Documento", $titulo );
		$ws->write_string( $mm+1, $mcel, "Nro.", $titulo );
		$ws->write_string( $mm+2, $mcel, "Caja", $titulo );
		$mcel++;
		
		$ws->write_blank( $mm,   $mcel,  $titulo );
		$ws->write_string( $mm+1, $mcel, "Tipo", $titulo );
		$ws->write_string( $mm+2, $mcel, "Doc.", $titulo );
		$mcel++;
		
		$ws->write_blank( $mm,   $mcel, $titulo );
		$ws->write_string( $mm+1, $mcel, "Contribuyentes", $titulo );
		$ws->write_string( $mm+2, $mcel, "Numero", $titulo );
		$mcel++;
		
		$ws->write_blank( $mm,   $mcel, $titulo );
		$ws->write_string( $mm+1, $mcel, "No Contribuyentes", $titulo );
		$ws->write_string( $mm+2, $mcel, "Inicial", $titulo );
		$mcel++;

		$ws->write_blank( $mm,   $mcel, $titulo );
		$ws->write_blank( $mm+1, $mcel, $titulo );
		$ws->write_string( $mm+2, $mcel, "Final", $titulo );
		$mcel++;
		
		$ws->write_string( $mm,   $mcel, "Nombre, Razon Social", $titulo );
		$ws->write_string( $mm+1, $mcel, "o Denominacion del ", $titulo );
		$ws->write_string( $mm+2, $mcel, "Comprador", $titulo );
		$mcel++;

		$ws->write_string( $mm,   $mcel, "Numero", $titulo );
		$ws->write_string( $mm+1, $mcel, "del", $titulo );
		$ws->write_string( $mm+2, $mcel, "R.I.F.", $titulo );
		$mcel++;

		$ws->write_string( $mm,   $mcel, "Total Ventas", $titulo );
		$ws->write_string( $mm+1, $mcel, "Incluyendo el", $titulo );
		$ws->write_string( $mm+2, $mcel, "I.V.A.", $titulo );
		$mcel++;

		$ws->write_string( $mm,   $mcel, "Ventas", $titulo );
		$ws->write_string( $mm+1, $mcel, "Exentas o", $titulo );
		$ws->write_string( $mm+2, $mcel, "no Sujetas", $titulo );
		$mcel++;

		$ws->write_string( $mm,  $mcel, "Valor", $titulo );
		$ws->write_string( $mm+1,$mcel, "FOB Op.", $titulo );
		$ws->write_string( $mm+2,$mcel, "Export", $titulo );
		$mcel++;
		
		$ws->write_string( $mm,  $mcel, "VENTAS GRAVADAS", $titulo );
		$ws->write_string( $mm+1,$mcel, "ALICUOTA GENERAL $tasa%", $titulo );
		$ws->write_string( $mm+2,$mcel, "Base", $titulo );
		$mcel++;
		
		$ws->write_blank(  $mm,  $mcel, $titulo );
		$ws->write_blank(  $mm+1,$mcel, $titulo );
		$ws->write_string( $mm+2,$mcel, "Impuesto", $titulo );
		$mcel++;
		
		$ws->write_blank(  $mm,  $mcel, $titulo );
		$ws->write_string( $mm+1,$mcel, "ALICUOTA ADICIONAL $sobretasa%", $titulo );
		$ws->write_string( $mm+2,$mcel, "Base", $titulo );
		$mcel++;
		
		$ws->write_blank(  $mm,  $mcel, $titulo );
		$ws->write_blank(  $mm+1,$mcel, $titulo );
		$ws->write_string( $mm+2,$mcel, "Impuesto", $titulo );
		$mcel++;
		
		$ws->write_blank(  $mm,  $mcel, $titulo );
		$ws->write_string( $mm+1,$mcel, "ALICUOTA REDUCIDA $redutasa%", $titulo );
		$ws->write_string( $mm+2,$mcel, "Base", $titulo );
		$mcel++;
		
		$ws->write_blank(  $mm,  $mcel, $titulo );
		$ws->write_blank(  $mm+1,$mcel, $titulo );
		$ws->write_string( $mm+2,$mcel, "Impuesto", $titulo );
		$mcel++;

		$ws->write_string( $mm,  $mcel, "Ajuste a los", $titulo );
		$ws->write_string( $mm+1,$mcel, "DB Fiscales", $titulo );
		$ws->write_string( $mm+2,$mcel, "Per. Anterior", $titulo );
		$mcel++;
		
		$ws->write_string( $mm,  $mcel, "I.V.A. ", $titulo );
		$ws->write_string( $mm+1,$mcel, "Retenido", $titulo );
		$ws->write_string( $mm+2,$mcel, "Comprador", $titulo );
		$mcel++;
		
		$ws->write_string( $mm,  $mcel, "Numero ", $titulo );
		$ws->write_string( $mm+1,$mcel, "de", $titulo );
		$ws->write_string( $mm+2,$mcel, "Comp.", $titulo );
		$mcel++;
		
		$ws->write_string( $mm,  $mcel, "Fecha ", $titulo );
		$ws->write_string( $mm+1,$mcel, "de", $titulo );
		$ws->write_string( $mm+2,$mcel, "Recepcion", $titulo );
		$mcel++;

		$mm +=3;
		$ii = $mm+1;
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

		if ( $export->num_rows() > 0 )
		{
			foreach( $export->result() as $row ) 
			{
			
				// imprime contribuyente
				$fecha = substr($row->fecha,8,2)."-".substr($row->fecha,5,2)."-".substr($row->fecha,0,4);

				$ws->write_string( $mm, 0, $fecha,  $cuerpo );				// Fecha
				$ws->write_string( $mm, 1, ' ', $cuerpo );			// Numero de Caja
				
				if ( $row->tipo == "XE" ) 
					$ws->write_string( $mm, 2, "FC", $cuerpoc );		// TIPO
				elseif ( $row->tipo == "XC" ) 
					$ws->write_string( $mm, 2, "NC", $cuerpoc );		// TIPO
				elseif ( $row->tipo == "FE" ) 
					$ws->write_string( $mm, 2, "FC", $cuerpoc );		// TIPO
				else
					$ws->write_string( $mm, 2, $row->tipo, $cuerpoc );	// TIPO

				$ws->write_string( $mm, 3, $row->numero, $cuerpo );		// Nro. Documento
				$ws->write_string( $mm, 4, $row->inicial, $cuerpo );	// INICIAL
				$ws->write_string( $mm, 5, $row->final, $cuerpo );		// FINAL
				
/*    			if ( substr($row->tipo,0,1) == "X" ) 
					$ws->write_string( $mm, 5, "03", $cuerpoc );		// TIPO Transac
    			else
					$ws->write_string( $mm, 5, $row->registro, $cuerpoc );		// TIPO Transac
*/
				$ws->write_string( $mm, 6, $row->nombre, $cuerpo );			// NOMBRE
				$ws->write_string( $mm, 7, $row->rif, $cuerpo );			// CONTRIBUYENTE

				if ( $row->registro=='04' ) {
				    $ws->write_number( $mm, 8, 0, $numero );		// VENTAS + IVA
				    $ws->write_number( $mm, 9, 0, $numero );    // VENTAS EXENTAS
				    $ws->write_number( $mm,10, 0, $cuerpo );   	// EXPORTACION
				    $ws->write_number( $mm,11, 0, $numero );		// GENERAL
				    $ws->write_number( $mm,12, 0, $numero );		// GENEIMPU
				    $ws->write_number( $mm,13, 0, $numero );		// ADICIONAL
				    $ws->write_number( $mm,14, 0, $numero );		// ADICIMPU
				    $ws->write_number( $mm,15, 0, $numero );		// REDUCIDA
				    $ws->write_number( $mm,16, 0, $numero );		// REDUIMPU
				    $ws->write_number( $mm,17, $row->geneimpu + $row->adicimpu+$row->reduimpu, $numero );		// REDUIMPU
				} else {
				    $ws->write_number( $mm, 8, $row->ventatotal, $numero );	// VENTAS + IVA
				    $ws->write_number( $mm, 9, $row->exento, $numero );   	// VENTAS EXENTAS
				    $ws->write_number( $mm,10, 0, $cuerpo );   					    // EXPORTACION
				    $ws->write_number( $mm,11, $row->general, $numero );		// GENERAL
				    $ws->write_number( $mm,12, $row->geneimpu, $numero );		// GENEIMPU
				    $ws->write_number( $mm,13, $row->adicional, $numero );	// ADICIONAL
				    $ws->write_number( $mm,14, $row->adicimpu, $numero );		// ADICIMPU
				    $ws->write_number( $mm,15, $row->reducida, $numero );		// REDUCIDA
				    $ws->write_number( $mm,16, $row->reduimpu, $numero );		// REDUIMPU
				    $ws->write_number( $mm,17, 0, $numero );		// REDUIMPU
				}

				$ws->write_number( $mm,18, $row->reiva, $numero );		    // IVA RETENIDO
				$ws->write_string( $mm,19, $row->comprobante, $cuerpo );	// NRO COMPROBANTE
				$fecharece = '';
				if ( !empty($row->fecharece) )
				$fecharece = substr($row->fecharece,8,2)."/".substr($row->fecharece,5,2)."/".substr($row->fecharece,0,4);
				$ws->write_string( $mm,20, $fecharece, $cuerpo );	// FECHA COMPROB
    			$mm++;
			}
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
//		$fivaperu  = "=U$celda";   // general
		
		$fivajuste = "=S$celda";   // general
		


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
		$ws->write_formula( $mm,18, "=SUM(S$ii:S$mm)", $Tnumero );   //"BASE IMPONIBLE" 

//		$ws->write_blank( $mm, 18,  $Tnumero );
		$ws->write_blank( $mm, 19,  $Tnumero );
		$ws->write_blank( $mm, 20,  $Tnumero );
//		$ws->write_formula( $mm,20, "=SUM(U$ii:U$mm)", $Tnumero );   //IMPUESTO PERCIBIDO 

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
		$ws->write( $mm+1, 9, "  ", $titulo );

		$ws->write($mm,  10, 'Debito', $titulo );
		$ws->write($mm+1,10, 'Fiscal', $titulo );

//		$ws->write($mm,11, 'Items', $titulo );
//		$ws->write( $mm+1, 11, "  ",  $titulo );

//		$ws->write($mm,12, 'IVA Ret.', $titulo );
//		$ws->write($mm+1,12, 'Retetenido', $titulo );

//		$ws->write($mm,13, 'Items', $titulo );
//		$ws->write( $mm+1, 13, "  ",  $titulo );

//		$ws->write($mm,  14, 'IVA', $titulo );
//		$ws->write($mm+1,14, 'Percibido', $titulo );

		$ws->write($mm, 16, 'LEYENDAS:', $titulo );
		$ws->write_blank( $mm+1, 16,  $titulo );

		$ws->write_blank( $mm,   17,  $titulo );
		$ws->write_blank( $mm+1, 17,  $titulo );

		$ws->write($mm,   18, 'Tipo de Contribuyente',  $titulo );
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

		$ws->write($mm, 18, 'Tipo de Transaccion', $titulo );
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

//		$ws->write_number($mm, 12, "0", $Rnumero );
//		$ws->write_number($mm, 14, "0", $Rnumero );
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
//		$ws->write_number($mm, 12, "0", $Rnumero );
//		$ws->write_number($mm, 14, "0", $Rnumero );

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

//		$ws->write_number($mm, 12, "0", $Rnumero );
//		$ws->write_number($mm, 14, "0", $Rnumero );

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

//		$ws->write($mm, 11, "66" , $cuerpoc );
//		$ws->write_number($mm, 12, "0", $Rnumero );
//		$ws->write($mm, 13, "68" , $cuerpoc );
//		$ws->write_number($mm, 14, "0", $Rnumero );

		$ws->write($mm, 16, 'CR -Comprobante Ret.', $cuerpo );
		$ws->write_blank( $mm+1, 16,  $cuerpo );
		$ws->write($mm, 18, '04 -Ajuste', $cuerpo );
		$ws->write_blank( $mm, 19,  $cuerpo );
		$mm ++;

		$mm ++;

		$ws->write($mm, 1, 'Total IVA retenido por el Comprador:', $titulo );
		$ws->write_blank( $mm, 2,  $titulo );
		$ws->write_blank( $mm, 3,  $titulo );
		$ws->write_blank( $mm, 4,  $titulo );
		$ws->write_blank( $mm, 5,  $titulo );
		$ws->write_blank( $mm, 6,  $titulo );
		$ws->write( $mm, 7, ' ', $Rnumero );
		$ws->write_blank( $mm, 8,  $Rnumero );
		$ws->write($mm, 9, "66" , $cuerpoc );
		$ws->write_formula($mm, 10, "=S$celda", $Rnumero );


		$mm ++;
		$mm ++;
		$ws->write($mm, 1, 'Total Ajustes a los debitos fiscales de periodos Anteriores:', $titulo );
		$ws->write_blank( $mm, 2,  $titulo );
		$ws->write_blank( $mm, 3,  $titulo );
		$ws->write_blank( $mm, 4,  $titulo );
		$ws->write_blank( $mm, 5,  $titulo );
		$ws->write_blank( $mm, 6,  $titulo );
		$ws->write( $mm, 7, ' ', $Rnumero );
		$ws->write_blank( $mm, 8,  $Rnumero );
		$ws->write($mm, 9, "48" , $cuerpoc );
		$ws->write_formula($mm, 10, "=R$celda", $Rnumero );


		$wb->close();
		
		header("Content-type: application/x-msexcel; name=\"lventas.xls\"");
		header("Content-Disposition: inline; filename=\"lventas.xls\"");
		$fh=fopen($fname,"rb");
		fpassthru($fh);
		unlink($fname);
		print "$header\n$data";
		
    }


//***********************************************
//
//  LIBRO DE VENTAS NORMAL
//	
//***********************************************
	function wlvexcel1() 
	{
		$mes = $this->uri->segment(4).$this->uri->segment(5);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');

		$aaa = $this->datasis->ivaplica($mes."02");
		$tasa      = $aaa['tasa'];
		$redutasa  = $aaa['redutasa'];
		$sobretasa = $aaa['sobretasa'];

		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		$this->db->simple_query($mSQL);
		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		$this->db->simple_query($mSQL);

		$mSQL  = "SELECT 
				a.fecha,
				a.numero,
				a.nfiscal,
				a.rif,
				IF( MID(a.rif,1,1) NOT IN ('J','G') AND b.contacto IS NOT NULL AND b.contacto!='',b.contacto,a.nombre) AS nombre,
				a.tipo, 
				IF(a.referen=a.numero,'        ',a.referen) afecta,
				a.gtotal*IF(a.tipo='NC',-1,1)    ventatotal,
				a.exento*IF(a.tipo='NC',-1,1)    exento,
				a.general*IF(a.tipo='NC',-1,1)   base,
				a.impuesto*IF(a.tipo='NC',-1,1)  impuesto,
				a.reiva*IF(a.tipo='NC',-1,1)     reiva,
				a.general*IF(a.tipo='NC',-1,1)	 general,
				a.geneimpu*IF(a.tipo='NC',-1,1)  geneimpu,
				a.adicional*IF(a.tipo='NC',-1,1) adicional,
				a.adicimpu*IF(a.tipo='NC',-1,1)  adicimpu,
				a.reducida*IF(a.tipo='NC',-1,1)  reducida,
				a.reduimpu*IF(a.tipo='NC',-1,1)  reduimpu,
				a.contribu, a.registro 
			FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente
			WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.tipo<>'FA' 
			ORDER BY a.fecha, IF(a.tipo IN ('FC','XE','XC'),1,2), numero ";

		$export = $this->db->query($mSQL);

/*		
		'              ' comprobante,
		'            ' fechacomp,
		'            ' impercibido,
		'            ' importacion,
*/
		
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
		$tt =& $wb->addformat(array( "size" => 9, "merge" => 1, "fg_color" => 'silver' ));

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


		if ( $export->num_rows() > 0 )
		{
			foreach( $export->result() as $row ) 
			{
				// imprime contribuyente
				$fecha = substr($row->fecha,8,2)."/".$ameses[substr($row->fecha,5,2)-1]."/".substr($row->fecha,0,4);

    			$ws->write_string( $mm, 0, $fecha,  $cuerpo );				// Fecha
    			$ws->write_string( $mm, 1, $row->nombre, $cuerpo );		// Nombre
    			$ws->write_string( $mm, 2, $row->rif, $cuerpo );			// RIF/CEDULA

    			if ( $row->tipo == "XE" ) 
					$ws->write_string( $mm, 3, "FC", $cuerpoc );			// TIPO
    			elseif ( $row->tipo == "XC" ) 
					$ws->write_string( $mm, 3, "NC", $cuerpoc );			// TIPO
    			elseif ( $row->tipo == "FE" ) 
					$ws->write_string( $mm, 3, "FC", $cuerpoc );			// TIPO
    			else
					$ws->write_string( $mm, 3, $row->tipo, $cuerpoc );	// TIPO
					
				$ws->write_string( $mm, 4, $row->numero, $cuerpo );		// Nro. Documento
    			if ( substr($row->tipo,0,1) == "X" ) 
					$ws->write_string( $mm, 5, "03", $cuerpoc );		// TIPO Transac
    			else
					$ws->write_string( $mm, 5, $row->registro, $cuerpoc );		// TIPO Transac

				$ws->write_string( $mm, 6, $row->afecta, $cuerpo );		// DOC. AFECTADO
				$ws->write_string( $mm, 7, $row->contribu, $cuerpo );		// CONTRIBUYENTE
				$ws->write_number( $mm, 8, $row->ventatotal, $numero );	// VENTAS + IVA
				$ws->write_number( $mm, 9, $row->exento, $numero );   	// VENTAS EXENTAS
				$ws->write_number( $mm,10, 0, $cuerpo );   					// EXPORTACION

				$ws->write_number( $mm,11, $row->general, $numero );		// GENERAL
				$ws->write_number( $mm,12, $row->geneimpu, $numero );		// GENEIMPU
				$ws->write_number( $mm,13, $row->adicional, $numero );	// ADICIONAL
				$ws->write_number( $mm,14, $row->adicimpu, $numero );		// ADICIMPU
				$ws->write_number( $mm,15, $row->reducida, $numero );		// REDUCIDA
				$ws->write_number( $mm,16, $row->reduimpu, $numero );		// REDUIMPU

				$ws->write_number( $mm,17, $row->reiva, $numero );		// IVA RETENIDO
//				$ws->write_string( $mm,18, $row['comprobante'], $cuerpo );	// NRO COMPROBANTE
//				$ws->write_string( $mm,19, $row['fechacomp'], $cuerpo );	// FECHA COMPROB
				$ws->write_number( $mm,20, 0, $numero );	// IMPUESTO PERCIBIDO

    			$mm++;
			}
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
		$ws->write( $mm+1, 9, "  ", $titulo );

		$ws->write($mm,  10, 'Debito', $titulo );
		$ws->write($mm+1,10, 'Fiscal', $titulo );

		$ws->write($mm,11, 'Items', $titulo );
		$ws->write( $mm+1, 11, "  ",  $titulo );

		$ws->write($mm,12, 'IVA Ret.', $titulo );
		$ws->write($mm+1,12, 'Retetenido', $titulo );

		$ws->write($mm,13, 'Items', $titulo );
		$ws->write( $mm+1, 13, "  ",  $titulo );

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
		$ws->write_number($mm, 12, "0", $Rnumero );
		$ws->write_number($mm, 14, "0", $Rnumero );
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
		$ws->write_number($mm, 12, "0", $Rnumero );
		$ws->write_number($mm, 14, "0", $Rnumero );

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
		$ws->write_number($mm, 12, "0", $Rnumero );
		$ws->write_number($mm, 14, "0", $Rnumero );
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
		$ws->write_number($mm, 12, "0", $Rnumero );
		$ws->write($mm, 13, "68" , $cuerpoc );
		$ws->write_number($mm, 14, "0", $Rnumero );
		$ws->write($mm, 16, 'TK -Ticket de Caja', $cuerpo );
		$ws->write_blank( $mm+1, 16,  $cuerpo );
		$ws->write($mm, 18, '04 -Ajuste', $cuerpo );
		$ws->write_blank( $mm, 19,  $cuerpo );
		$mm ++;


		$ws->write($mm, 1, 'Total IVA retenido por el Comprador:', $titulo );
		$ws->write_blank( $mm, 2,  $titulo );
		$ws->write_blank( $mm, 3,  $titulo );
		$ws->write_blank( $mm, 4,  $titulo );
		$ws->write_blank( $mm, 5,  $titulo );
		$ws->write_blank( $mm, 6,  $titulo );
		$ws->write_blank( $mm, 7,  $titulo );
		$ws->write_blank( $mm, 8,  $Rnumero );
		$ws->write($mm, 9, "66" , $cuerpoc );
		$ws->write_formula($mm, 10, "=S$celda", $Rnumero );
//		$ws->write($mm, 11, "66" , $cuerpoc );
//		$ws->write_number($mm, 12, "0", $Rnumero );
//		$ws->write($mm, 13, "68" , $cuerpoc );
//		$ws->write_number($mm, 14, "0", $Rnumero );
//		$ws->write($mm, 16, 'TK -Ticket de Caja', $cuerpo );
//		$ws->write_blank( $mm+1, 16,  $cuerpo );
//		$ws->write($mm, 18, '04 -Ajuste', $cuerpo );
//		$ws->write_blank( $mm, 19,  $cuerpo );
		$mm ++;




		$wb->close();
		header("Content-type: application/x-msexcel; name=\"lventas.xls\"");
		header("Content-Disposition: inline; filename=\"lventas.xls\"");
		$fh=fopen($fname,"rb");
		fpassthru($fh);
		unlink($fname);
		print "$header\n$data";
		
    }
    
 
    
    
    
    function genecompras() 
    {
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
    	//Procesando Compras scst
		$this->db->simple_query("UPDATE scst SET montasa=0, tasa =0     WHERE montasa IS NULL ");
		$this->db->simple_query("UPDATE scst SET monredu=0, reducida=0  WHERE monredu IS NULL ");
		$this->db->simple_query("UPDATE scst SET monadic=0, sobretasa=0 WHERE monadic IS NULL ");

		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='CP' ");

		// REVISAR COMPRAS
		$query = $this->db->query("SELECT control FROM scst WHERE abs(exento+montasa+monredu+monadic-montotot)>0.1 AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");

		// Procesando Compra 
		if ($query->num_rows() > 0) 
		    foreach ($query->result() as $row) $this->scstarretasa( $row->control );
		// VER LO DE FACTURAS RECIBIDAS CON FECHA ANTERIOR
		$mFECHAA = $this->datasis->dameval("SELECT fecha FROM civa WHERE fecha < (SELECT MAX(fecha) FROM siva) ORDER BY fecha DESC LIMIT 1");
		$mFECHAF = $this->datasis->dameval("SELECT max(fecha) FROM civa WHERE fecha<$mes"."01");
		$mSQL = "INSERT INTO siva  
				(id, libro, tipo, fuente, sucursal, fecha, numero, numhasta,  caja, nfiscal,  nhfiscal, 
				referen, planilla, clipro, nombre, contribu, rif, registro,
				nacional, exento, general, geneimpu, 
				adicional, adicimpu,  reducida,  reduimpu, stotal, impuesto, 
				gtotal, reiva, fechal, fafecta) 
				SELECT 0 AS id,
				'C' AS libro, 
				a.tipo_doc AS tipo, 
				'CP' AS fuente, 
				'00' AS sucursal, 
				a.fecha, 
				a.numero, 
				' ' AS numhasta, 
				' ' AS caja, 
				a.nfiscal, 
				'  ' AS nhfiscal, 
				'        ' AS referen, 
				'  ' AS planilla, 
				a.proveed AS clipro, 
				a.nombre, 
				'CO' AS contribu, 
				c.rif, 
				if(a.fecha<'$mFECHAA' AND a.recep>='$mFECHAF' ,'04', '01') AS registro, 
				'S' AS nacional, 
				a.exento*((a.montotot-a.descu)/a.montotot)    exento,    
				a.montasa*((a.montotot-a.descu)/a.montotot)   general,   
				a.tasa*((a.montotot-a.descu)/a.montotot)      geneimpu,  
				a.monadic*((a.montotot-a.descu)/a.montotot)   adicional, 
				a.sobretasa*((a.montotot-a.descu)/a.montotot) reduimpu,  
				a.monredu*((a.montotot-a.descu)/a.montotot)   reducida,  
				a.reducida*((a.montotot-a.descu)/a.montotot)  adicimpu,  
				a.montotot-a.descu+a.licor AS stotal,
				a.montoiva AS impuesto, 
				a.montonet-a.descu AS gtotal, 
				a.reteiva AS reiva, 
				".$mes."01 AS fechal, 
				0 fafecta 
				FROM scst AS a 
				LEFT JOIN sprv AS c ON a.proveed=c.proveed 
				WHERE EXTRACT(YEAR_MONTH FROM a.recep)=$mes AND a.actuali >= a.fecha 
				GROUP BY a.control";

		// Procesando Compras scst
		$this->db->simple_query($mSQL);
		$mSQL = "UPDATE siva SET gtotal=exento+general+geneimpu+adicional+reduimpu+reducida+adicimpu 
				WHERE fuente='CP' AND libro='C' ";
		$this->db->simple_query($mSQL);
    }

    //
    // GENERA GASTOS
    //
    function genegastos() 
    {
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
    	//Procesando Compras gser
    	$this->db->simple_query("UPDATE gser SET cajachi='N' WHERE cajachi='' or cajachi IS NULL");
	$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='GS' ");

	// REVISA GSER A VER SI HAY PROBLEMAS
    	$this->db->simple_query("UPDATE gser SET exento=totbruto WHERE exento<>totbruto and totiva=0");
   
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
				a.numero, 
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
				'01' AS registro, 
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
				WHERE EXTRACT(YEAR_MONTH FROM a.fecha)=$mes 
				AND a.cajachi='N' 
				AND (c.tipo NOT IN ('5') OR a.totiva<>0 ) 
				ORDER BY a.fecha, a.proveed, a.numero ";
		$this->db->simple_query($mSQL);
		
		// GASTOS DE  CAJACHICA
		$mATASAS = $this->datasis->ivaplica($mes.'02');

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
				'01' AS registro, 
				'S' AS nacional, 
				IF(a.iva=0,1,0)*a.importe AS exento, 
				IF(round(a.iva*100/a.precio,1)=".$mATASAS['tasa']     .",1,0)*a.precio AS general, 
				IF(round(a.iva*100/a.precio,1)=".$mATASAS['tasa']     .",1,0)*a.iva    AS geneimpu, 
				IF(round(a.iva*100/a.precio,1)=".$mATASAS['sobretasa'].",1,0)*a.precio AS adicional, 
				IF(round(a.iva*100/a.precio,1)=".$mATASAS['sobretasa'].",1,0)*a.iva    AS adicimpu, 
				IF(round(a.iva*100/a.precio,1)=".$mATASAS['redutasa'] .",1,0)*a.precio AS reducida,
				IF(round(a.iva*100/a.precio,1)=".$mATASAS['redutasa'] .",1,0)*a.iva    AS reduimpu,
				a.precio AS stotal,
				a.iva AS impuesto, 
				a.importe AS gtotal, 
				0 AS reiva,
				".$mes."01 AS fechal, 
				0 AS fafecta 
				FROM gitser AS a JOIN gser AS b ON 
				a.fecha=b.fecha AND a.proveed=b.proveed AND a.numero=b.numero 
				WHERE EXTRACT(YEAR_MONTH FROM b.fecha)=$mes 
				AND b.tipo_doc='FC' AND b.cajachi='S' 
				ORDER BY a.fecha ";
		$this->db->simple_query($mSQL);
		$mSQL = "UPDATE siva SET gtotal=exento+general+geneimpu+adicional+reduimpu+reducida+adicimpu 
				WHERE fuente='GS' AND libro='C' ";
		$this->db->simple_query($mSQL);
	
    }
    

    function genecxp() 
    {
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
    	//Procesando Compras scst

		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='MP' ");

		$mSQL = "SELECT a.*,b.rif, b.nomfis FROM sprm AS a LEFT JOIN sprv AS b ON a.cod_prv=b.proveed 
				WHERE EXTRACT(YEAR_MONTH FROM fecha)=$mes AND b.tipo<>'5' 
				AND a.tipo_doc='NC' AND a.codigo NOT IN ('NOCON','') ";

		$query = $this->db->query($mSQL);

		if ( $query->num_rows() > 0 )
		{
			foreach( $query->result() as $row ) 
			{
				if ($row->impuesto == 0 and empty($row->codigo) ) continue;

				$referen = $this->datasis->dameval("SELECT numero FROM itppro WHERE transac=".$row->transac." LIMIT 1") ;
				$fafecta = $this->datasis->dameval("SELECT fecha  FROM itppro WHERE transac=".$row->transac." LIMIT 1") ;
				$stotal = $row->monto-$row->impuesto;
				$mSQL = "INSERT INTO siva SET 
	   					libro='C', 
   						tipo='".$row->tipo_doc."', 
   						fuente='MP', 
   						sucursal='00', 
   						fecha='".$row->fecha."', 
   						numero='".$row->numero."', 
	   					clipro='".$row->cod_prv."', 
   						nombre='".$row->nomfis."', 
   						contribu='CO', 
   						rif='".$row->rif."', 
   						registro='01', 
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
   						fafecta='$fafecta' ";

//				die($mSQL);
				$this->db->simple_query($mSQL);
			}
		}
		// Procesando Compras scst
		$mSQL = "UPDATE siva SET gtotal=exento+general+geneimpu+adicional+reduimpu+reducida+adicimpu 
				WHERE fuente='MP' AND libro='C' ";
		$this->db->simple_query($mSQL);
    }




	// ****************************************
	//
	//    GENERA DE SFAC VENTAS
	//
	function genesfac()
	{
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
		// BORRA LA GENERADA ANTERIOR
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='FA' ");
		// ARREGLA LAS TASAS NULAS EN SFAC
		$this->db->simple_query("UPDATE sfac SET tasa=0, montasa=0, reducida=0, monredu=0, sobretasa=0, monadic=0, exento=0 WHERE (tasa IS NULL OR montasa IS NULL) AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");
		// Arregla las factras malas
		$query = $this->db->query("SELECT transac FROM sfac WHERE abs(exento+montasa+monredu+monadic-totals)>0.2 AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");
		if ($query->num_rows() > 0) 
		    foreach ( $query->result() AS $row ) $this->_arreglatasa($row->transac);

		// ARREGLA LAS QUE TIENEN UNA SOLA TASA
		$mSQL = "UPDATE sfac SET tasa=iva, montasa=totals 
			WHERE reducida=0 AND sobretasa=0 AND exento=0 AND EXTRACT(YEAR_MONTH FROM fecha)=$mes";
		$this->db->simple_query($mSQL);

		$mSQL = "INSERT INTO siva  
				(id, libro, tipo, fuente, sucursal, fecha, numero, numhasta,  caja, nfiscal,  nhfiscal, 
				referen, planilla, clipro, nombre, contribu, rif, registro,
				nacional, exento, general, geneimpu, 
				adicional, adicimpu,  reducida,  reduimpu, stotal, impuesto, 
				gtotal, reiva, fechal, fafecta) 
				SELECT 0 AS id,
				'V' AS libro, 
				IF(a.tipo_doc='D','NC',CONCAT(a.tipo_doc,a.referen)) AS tipo, 
				'FA' AS fuente, 
				'00' AS sucursal, 
				a.fecha, 
				a.numero, 
				' ' AS numhasta, 
				' ' AS caja, 
				a.nfiscal, 
				'  ' AS nhfiscal, 
				IF(a.tipo_doc='F',a.numero,a.factura ) AS referen, 
				'  ' AS planilla, 
				a.cod_cli AS clipro, 
				IF(a.tipo_doc='X','DOCUMENTO ANULADO.......',a.nombre), 
				IF(c.tiva='C','CO','NO') AS contribu, 
				IF(a.rifci='',c.rifci,a.rifci), 
				'01' AS registro, 
				'S' AS nacional, 
				a.exento*(a.tipo_doc<>'X')  AS exento, 
				a.montasa*(a.tipo_doc<>'X') AS general,   
				a.tasa*(a.tipo_doc<>'X') AS geneimpu, 
				a.monadic*(a.tipo_doc<>'X') AS adicional, 
				a.sobretasa*(a.tipo_doc<>'X') AS adicimpu, 
				a.monredu*(a.tipo_doc<>'X') AS reducida,  
				a.reducida*(a.tipo_doc<>'X')  AS reduimpu, 
				a.totals*(a.tipo_doc<>'X') AS stotal,
				a.iva*(a.tipo_doc<>'X')    AS impuesto, 
				a.totalg*(a.tipo_doc<>'X') AS gtotal, 
				0 AS reiva, 
				".$mes."01 AS fechal, 
				0 AS fafecta 
				FROM sfac AS a 
				LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				WHERE EXTRACT(YEAR_MONTH FROM a.fecha)=$mes ";
			$this->db->simple_query($mSQL);

   			// CARGA LAS RETENCIONES DE IVA DE CONTADO
			$mSQL = "SELECT * FROM sfpa WHERE tipo='RI' AND	EXTRACT(YEAR_MONTH FROM f_factura)=$mes AND tipo_doc='FE' ";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0) {
				foreach ( $query->result() AS $row ) 
				{
      				$mSQL = "UPDATE siva SET reiva=".$row->monto.", comprobante='20".$row->num_ref."' WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
      				$this->db->simple_query($mSQL); 
				}
			}

   			// CARGA LAS RETENCIONES DE IVA DESDE SMOV
			$mSQL = "SELECT a.tipo_doc, a.fecha, a.numero, c.nombre, c.rifci, a.cod_cli, b.monto,
					a.numero AS afecta, a.fecha AS fafecta, a.reteiva, a.transac, a.nroriva, a.emiriva, a.recriva 
				FROM itccli AS a JOIN smov AS b ON a.transac=b.transac 
					LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				WHERE EXTRACT(YEAR_MONTH FROM b.fecha) = ".$mes." AND b.cod_cli='REIVA' 
					AND a.reteiva>0 AND b.monto>b.abonos ";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0) {
				foreach ( $query->result() AS $row ) 
				{
      				$mSQL = "UPDATE siva SET reiva=".$row->reteiva.", comprobante='".$row->nroriva."', fecharece='$row->recriva'  WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
      				$this->db->simple_query($mSQL); 
				}
			}

/*
			//
			// FACTURACION AL MAYOR FMAY
			//
   			$mSQL = "INSERT INTO siva  
					(id, libro, tipo, fuente, sucursal, fecha, numero, numhasta,  caja, nfiscal,  nhfiscal, 
					referen, planilla, clipro, nombre, contribu, rif, registro,
					nacional, exento, general, geneimpu, 
					adicional, adicimpu,  reducida,  reduimpu, stotal, impuesto, 
					gtotal, reiva, fechal, fafecta) 
  					SELECT 0 AS id,
					'V' AS libro, 
					IF(b.tipo='D','NC','FC') AS tipo, 
					'FA' AS fuente, 
					'00' AS sucursal, 
					b.fecha, 
					b.numero, 
					' ' AS numhasta, 
					' ' AS caja, 
					b.nfiscal, 
					'  ' AS nhfiscal, 
					'' AS referen, 
					'  ' AS planilla, 
					b.cod_cli AS clipro, 
					b.nombre, 
					'CO' AS contribu, 
					c.rifci, 
					'01' AS registro, 
					'S' AS nacional, 
					sum(IF(a.iva=0,1,0)*a.importe) AS exento, 
					sum(IF(a.iva="+mTASA+",1,0)*a.importe) AS general, 
					sum(IF(a.iva="+mTASA+",1,0)*a.importe*a.iva/100) AS geneimpu, 
					sum(IF(a.iva>"+mTASA+",1,0)*a.importe)  AS adicional, 
					sum(IF(a.iva>"+mTASA+",1,0)*a.importe*a.iva/100) AS adicimpu, 
					sum(IF(a.iva<"+mTASA+" AND a.iva>0,1,0)*a.importe) AS reducida,
					sum(IF(a.iva<"+mTASA+" AND a.iva>0,1,0)*a.importe*a.iva/100) AS reduimpu,
					b.stotal AS stotal,
					b.impuesto AS impuesto, 
					b.gtotal AS gtotal, 
					0 AS reiva, 
					".$mes."01 AS fechal, 
 					0 AS fafecta 
					FROM itfmay AS a JOIN fmay AS b ON a.numero=b.numero AND a.fecha=b.fecha 
					LEFT JOIN scli AS c ON b.cod_cli=c.cliente 
					WHERE EXTRACT(YEAR_MONTH FROM b.fecha)=$mes AND b.tipo!='A' 
					GROUP BY a.fecha,a.numero ";
			$this->db->simple_query(mSQL);
*/			
	}

	// ****************************************
	//
	//    GENERA DE SFAC RESTAURANTE
	//
	function generest()
	{
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
		// BORRA LA GENERADA ANTERIOR
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='FR' ");
		// ARREGLA LAS TASAS NULAS EN SFAC
		$this->db->simple_query("UPDATE sfac SET tasa=0, montasa=0, reducida=0, monredu=0, sobretasa=0, monadic=0, exento=0 WHERE (tasa IS NULL OR montasa IS NULL) AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");
		// Arregla las factras malas
//		$query = $this->db->query("SELECT transac FROM sfac WHERE abs(exento+montasa+monredu+monadic-totals)>0.2 AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");
//		if ($query->num_rows() > 0) 
//		    foreach ( $query->result() AS $row ) $this->_arreglatasa($row->transac);

		// ARREGLA LAS QUE TIENEN UNA SOLA TASA
//		$mSQL = "UPDATE sfac SET tasa=iva, montasa=totals 
//			WHERE reducida=0 AND sobretasa=0 AND exento=0 AND EXTRACT(YEAR_MONTH FROM fecha)=$mes";
//		$this->db->simple_query($mSQL);

		$mSQL = "INSERT INTO siva  
				(id, libro, tipo, fuente, sucursal, fecha, numero, numhasta,  caja, nfiscal,  nhfiscal, 
				referen, planilla, clipro, nombre, contribu, rif, registro,
				nacional, exento, general, geneimpu, 
				adicional, adicimpu,  reducida,  reduimpu, stotal, impuesto, 
				gtotal, reiva, fechal, fafecta) 
				SELECT 0 AS id,
				'V' AS libro, 
				IF(a.tipo='D','NC',CONCAT('F',a.tipo)) AS tipo, 
				'FR' AS fuente, 
				'00' AS sucursal, 
				a.fecha, 
				a.numero, 
				' ' AS numhasta, 
				' ' AS caja, 
				' ' AS nfiscal, 
				'  ' AS nhfiscal, 
				IF(a.tipo='E',a.numero,a.numero ) AS referen, 
				'  ' AS planilla, 
				a.cod_cli AS clipro, 
				IF(a.tipo='X','DOCUMENTO ANULADO.......',c.nombre), 
				IF(c.tiva='C','CO','NO') AS contribu, 
				IF(c.rifci='',a.rifci,c.rifci), 
				'01' AS registro, 
				'S' AS nacional, 
				a.servicio*(a.tipo<>'X')  AS exento, 
				a.stotal*(a.tipo<>'X') AS general,   
				a.impuesto*(a.tipo<>'X') AS geneimpu, 
				0 AS adicional, 
				0 AS adicimpu, 
				0 AS reducida,  
				0 AS reduimpu, 
				a.stotal*(a.tipo<>'X') AS stotal,
				a.impuesto*(a.tipo<>'X')    AS impuesto, 
				a.gtotal*(a.tipo<>'X') AS gtotal, 
				0 AS reiva, 
				".$mes."01 AS fechal, 
				0 AS fafecta 
				FROM sfac AS a 
				LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				WHERE EXTRACT(YEAR_MONTH FROM a.fecha)=$mes AND a.tipo NOT IN ('P','T')";
			$this->db->simple_query($mSQL);
//			echo $mSQL;

   			// CARGA LAS RETENCIONES DE IVA DE CONTADO
			$mSQL = "SELECT * FROM sfpa WHERE tipo='RI' AND	EXTRACT(YEAR_MONTH FROM f_factura)=$mes AND tipo_doc='FE' ";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0) {
				foreach ( $query->result() AS $row ) 
				{
      				$mSQL = "UPDATE siva SET reiva=".$row->monto.", comprobante='20".$row->num_ref."' WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
      				$this->db->simple_query($mSQL); 
				}
			}

   			// CARGA LAS RETENCIONES DE IVA DESDE SMOV
			$mSQL = "SELECT a.tipo_doc, a.fecha, a.numero, c.nombre, c.rifci, a.cod_cli, b.monto,
					a.numero AS afecta, a.fecha AS fafecta, a.reteiva, a.transac, a.nroriva, a.emiriva, a.recriva 
				FROM itccli AS a JOIN smov AS b ON a.transac=b.transac 
					LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				WHERE EXTRACT(YEAR_MONTH FROM b.fecha) = ".$mes." AND b.cod_cli='REIVA' 
					AND a.reteiva>0 AND b.monto>b.abonos ";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0) {
				foreach ( $query->result() AS $row ) 
				{
      				$mSQL = "UPDATE siva SET reiva=".$row->reteiva.", comprobante='".$row->nroriva."', fecharece='$row->recriva'  WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
      				$this->db->simple_query($mSQL); 
				}
			}

	}


	// ****************************************
	//
	//    GENERA DE SFAC RESTAURANTE
	//
	function genehotel()
	{
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
		// BORRA LA GENERADA ANTERIOR
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='FH' ");
		// ARREGLA LAS TASAS NULAS EN SFAC
		$this->db->simple_query("UPDATE sfac SET tasa=0, montasa=0, reducida=0, monredu=0, sobretasa=0, monadic=0, exento=0 WHERE (tasa IS NULL OR montasa IS NULL) AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");
		// Arregla las factras malas
//		$query = $this->db->query("SELECT transac FROM sfac WHERE abs(exento+montasa+monredu+monadic-totals)>0.2 AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");
//		if ($query->num_rows() > 0) 
//		    foreach ( $query->result() AS $row ) $this->_arreglatasa($row->transac);

		// ARREGLA LAS QUE TIENEN UNA SOLA TASA
//		$mSQL = "UPDATE sfac SET tasa=iva, montasa=totals 
//			WHERE reducida=0 AND sobretasa=0 AND exento=0 AND EXTRACT(YEAR_MONTH FROM fecha)=$mes";
//		$this->db->simple_query($mSQL);

		$mSQL = "INSERT INTO siva  
				(id, libro, tipo, fuente, sucursal, fecha, numero, numhasta,  caja, nfiscal,  nhfiscal, 
				referen, planilla, clipro, nombre, contribu, rif, registro,
				nacional, exento, general, geneimpu, 
				adicional, adicimpu,  reducida,  reduimpu, stotal, impuesto, 
				gtotal, reiva, fechal, fafecta) 
				SELECT 0 AS id,
				'V' AS libro, 
				IF(a.tipo='D','NC',CONCAT('F',a.tipo)) AS tipo, 
				'FH' AS fuente, 
				'00' AS sucursal, 
				a.fecha_ou, 
				a.num_fac, 
				' ' AS numhasta, 
				' ' AS caja, 
				' ' AS nfiscal, 
				'  ' AS nhfiscal, 
				IF(a.tipo='E',a.num_fac,a.num_fac ) AS referen, 
				'  ' AS planilla, 
				a.cod_cli AS clipro, 
				IF(a.tipo='X','DOCUMENTO ANULADO.......',if(c.nombre='',a.nombre,c.nombre)), 
				IF(c.tiva='C','CO','NO') AS contribu, 
				IF(c.rifci='', a.cedula, c.rifci ), 
				'01' AS registro, 
				'S' AS nacional, 
				0   AS exento, 
				a.total*(a.tipo<>'X') AS general,   
				a.iva*(a.tipo<>'X') AS geneimpu, 
				0 AS adicional, 
				0 AS adicimpu, 
				0 AS reducida,  
				0 AS reduimpu, 
				a.total*(a.tipo<>'X') AS stotal,
				a.iva*(a.tipo<>'X')    AS impuesto, 
				a.totalg*(a.tipo<>'X') AS gtotal, 
				0 AS reiva, 
				".$mes."01 AS fechal, 
				0 AS fafecta 
				FROM hfac AS a 
				LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				WHERE EXTRACT(YEAR_MONTH FROM a.fecha_ou)=$mes AND a.tipo NOT IN ('P','T')";
			$this->db->simple_query($mSQL);
//			echo $mSQL;

   			// CARGA LAS RETENCIONES DE IVA DE CONTADO
			$mSQL = "SELECT * FROM sfpa WHERE tipo='RI' AND	EXTRACT(YEAR_MONTH FROM f_factura)=$mes AND tipo_doc='FE' ";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0) {
				foreach ( $query->result() AS $row ) 
				{
      				$mSQL = "UPDATE siva SET reiva=".$row->monto.", comprobante='20".$row->num_ref."' WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
      				$this->db->simple_query($mSQL); 
				}
			}

   			// CARGA LAS RETENCIONES DE IVA DESDE SMOV
			$mSQL = "SELECT a.tipo_doc, a.fecha, a.numero, c.nombre, c.rifci, a.cod_cli, b.monto,
					a.numero AS afecta, a.fecha AS fafecta, a.reteiva, a.transac, a.nroriva, a.emiriva, a.recriva 
				FROM itccli AS a JOIN smov AS b ON a.transac=b.transac 
					LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				WHERE EXTRACT(YEAR_MONTH FROM b.fecha) = ".$mes." AND b.cod_cli='REIVA' 
					AND a.reteiva>0 AND b.monto>b.abonos ";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0) {
				foreach ( $query->result() AS $row ) 
				{
      				$mSQL = "UPDATE siva SET reiva=".$row->reteiva.", comprobante='".$row->nroriva."', fecharece='$row->recriva'  WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
      				$this->db->simple_query($mSQL); 
				}
			}

	}


	function _arreglatasa($mTRANSAC)
	{
		$mTASA      = 0;
		$mREDUCIDA  = 0;
		$mSOBRETASA = 0;
		$mMONTASA   = 0;
		$mMONREDU   = 0;
		$mMONADIC   = 0;
		$mEXENTO    = 0;
		$mIVA       = 0;
		$mATASAS    = '';

		$query = $this->db->query("SELECT * FROM sitems WHERE transac='$mTRANSAC' AND tipoa<>'X'");

		foreach ( $query->result() as $row )
		{
			if (empty($mATASAS)) $mATASAS = $this->datasis->ivaplica($row->fecha);
			$mIVA    = $row->iva; 
			$mTOTA   = $row->tota;
			if ( $mIVA == $mATASAS['tasa']) {
				$mTASA    += round($mTOTA*$mIVA/100,2);
      			$mMONTASA += $mTOTA;
			} elseif ($mIVA == $mATASAS['redutasa']) {
				$mREDUCIDA += round($mTOTA*$mIVA/100,2);
				$mMONREDU  += $mTOTA;
			} elseif ($mIVA == $mATASAS['sobretasa']) {
				$mSOBRETASA += round($mTOTA*$mIVA/100,2);
				$mMONADIC   += $mTOTA;
			} elseif ($mIVA == 0 ) {
				$mEXENTO += $mTOTA;
			}
		}

		$mSQL = "UPDATE sfac SET exento=$mEXENTO, tasa=$mTASA, montasa=$mMONTASA,reducida=$mREDUCIDA, monredu=$mMONREDU, sobretasa=$mSOBRETASA, monadic=$mMONADIC WHERE transac='$mTRANSAC' ";
		$this->db->simple_query($mSQL);

	}
	
	//
	//
	//   GENERA SMOV 
	//
	//
	function genesmov()
	{
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='MC' ");
		
		$mSQL= "SELECT a.*,b.rifci, c.numero AS afecta, c.fecha AS fafecta 
				FROM smov AS a LEFT JOIN scli AS b ON a.cod_cli=b.cliente 
				LEFT JOIN itccli AS c ON a.numero=c.numccli AND a.tipo_doc=c.tipoccli 
				LEFT JOIN grcl AS d ON b.grupo=d.grupo 
				WHERE EXTRACT(YEAR_MONTH FROM a.fecha)=$mes 
				AND a.tipo_doc IN ('NC')  
				AND d.clase!='I' 
				AND a.observa1 NOT LIKE '%DEVOLUCION%' 
				AND a.codigo!='NOCON' 
				AND a.codigo!='' AND a.cod_cli<>'REIVA'";

		//Procesando CxC smov    "
		$query = $this->db->query($mSQL);
		$mNUMERO  = 'ASDFGHJK';
		$mTIPO_DOC = "XX";

		foreach ( $query->result() as $row )
		{
			if ( $row->tipo_doc == 'NC' )
			{
				if ($mTIPO_DOC == $row->tipo_doc AND $mNUMERO == $row->numero ) continue;
				$mNUMERO = $row->numero;
				$mTIPO_DOC = $row->tipo_doc;
			}
			$referen = $row->num_ref;
			$registro = '01';
			if ( !empty($row->afecta) ) 
			{
				$referen = $row->afecta;
				$aaa = $this->datasis->ivaplica($row->fafecta);
				$bbb = $this->datasis->ivaplica($row->fecha);
				if ( $aaa != $bbb )  $registro='04';
			}

			$stotal = $row->monto - $row->impuesto;
			$mSQL = "INSERT INTO siva SET 
						libro = 'V',
						tipo = '".$row->tipo_doc."',
						fuente = 'MC',
						sucursal = '00',
						fecha = '".$row->fecha."',
						numero = '".$row->numero."',
						clipro = '".$row->cod_cli."',
						nombre =".$this->db->escape($row->nombre).",
						contribu='CO',
						rif = '".$row->rifci."', 
						registro = '$registro',
						nacional ='S',
						referen = '$referen',
						general = $row->montasa,
						geneimpu = $row->tasa, 
						reducida = $row->monredu, 
						reduimpu = $row->reducida,
						adicional = $row->monadic,
						adicimpu = $row->sobretasa,
						exento = $row->exento, 
						impuesto = $row->impuesto, 
						gtotal = $row->monto, 
						stotal = $stotal,
						reiva = ".$row->reteiva.",
						fechal = ".$mes."01,
						fafecta ='".$row->fafecta."'";
			$this->db->simple_query($mSQL);
		}

		// RETENCIONES DE IVA DEL MISMO MES
		$mSQL = "SELECT b.fecha, a.numero, c.nombre, c.rifci, a.cod_cli,
						a.numero AS afecta, a.fecha AS fafecta, a.reteiva, a.transac, a.nroriva 
				FROM itccli AS a JOIN smov AS b ON a.transac=b.transac 
					 LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				WHERE EXTRACT(YEAR_MONTH FROM b.fecha)=$mes AND b.cod_cli='REIVA' 
					AND a.reteiva>0 AND b.monto>b.abonos 
					AND EXTRACT(YEAR_MONTH FROM a.fecha)=EXTRACT(YEAR_MONTH FROM b.fecha) ";

		$query = $this->db->query($mSQL);
		foreach ( $query->result() as $row )
		{
			$mSQL = "UPDATE siva SET reiva=$row->reteiva, comprobante=$row->nroriva WHERE tipo='FC' AND numero='$row->numero' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
			$this->db->simple_query($mSQL);
		}

		// RETENCIONES DE IVA
		$mSQL = "SELECT b.fecha, a.numero, c.nombre, c.rifci, a.cod_cli,
					a.numero AS afecta, a.fecha AS fafecta, sum(a.reteiva) reteiva, a.transac, a.nroriva, a.emiriva, if(a.recriva IS NULL, a.estampa, a.recriva) recriva 
				FROM itccli AS a JOIN smov AS b ON a.transac=b.transac 
					LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
				WHERE  b.fecha<=".$mes."31 AND b.cod_cli='REIVA' 
					AND a.reteiva>0 AND b.monto>b.abonos 
					AND EXTRACT(YEAR_MONTH FROM a.fecha)<$mes 
				GROUP BY a.nroriva
				UNION ALL
				SELECT b.fecha, a.numero, 'OJO LLENE DATOS', 'OJO', '',
					'' AS afecta, 0 AS fafecta, b.monto-b.abonos, a.transac, a.numero, a.fecha, a.fecha 
				FROM smov AS b JOIN prmo AS a ON a.transac=b.transac 
				WHERE b.fecha<".$mes."01 AND b.cod_cli='REIVA' 
				AND b.monto>b.abonos";

		$query = $this->db->query($mSQL);
		
		foreach ( $query->result() as $row )
		{
			$mSQL = "SELECT monto-abonos FROM smov WHERE cod_cli='REIVA' AND transac='$row->transac'";
//   			if ( $this->datasis->dameval($mSQL) <= 0 ) continue;
			$mSQL = "INSERT INTO siva SET 
					libro = 'V',
					tipo  = 'CR',
					fuente =  'MC',
					sucursal = '99', 
					fecha = '".$row->emiriva."',
					numero ='',  
					clipro ='".$row->cod_cli."', 
					nombre ='".$row->nombre."',  
					contribu = 'CO', 
					rif = '".$row->rifci."',
					registro = '01',
					nacional ='S',
					referen ='',
					fafecta ='',
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
					comprobante = '$row->nroriva',
					fecharece = '$row->recriva',
					fechal = ".$mes."01 "; 
		
			$this->db->simple_query($mSQL);
		}

		//RETENCIONES ANTERIORES PENDIENTES
		$mSQL = "SELECT * FROM smov WHERE fecha<".$mes."01 AND cod_cli='REIVA' 
				 AND control IS NULL AND monto>abonos AND (tipo_ref<>'PR' OR tipo_ref IS NULL) ";

		$query = $this->db->query($mSQL);
		
		foreach ( $query->result() as $row )
		{
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
			$this->db->simple_query($mSQL);
		}
	}
	
	
	//************************
	//
	// TRAE DE OTIN
	//
	//************************
	// Procesando Otros Ingr. 
	function geneotin()
	{
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
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
				EXTRACT(YEAR_MONTH FROM b.fecha)=$mes
				AND (b.iva > 0 OR b.tipo_doc IN ('FC','ND') ) 
				GROUP BY a.tipo_doc,a.numero ";
			$this->db->simple_query($mSQL);
			//echo $mSQL;
			//exit;

	}




	//***************************
	//
	//  ARREGLA MONTASA EN SCST
	//
	//***************************
	function scstarretasa($mCONTROL)
	{
		$m         = 1;
		$mTASA     = 0;
		$mREDUCIDA = 0;
		$mSOBRETASA = 0;
		$mMONTASA  = 0;
		$mMONREDU  = 0;
		$mMONADIC  = 0;
		$mATASAS   = 0;
		$mIVA      = 0;
		$mEXENTO   = 0;

		$query = $this->db->query("SELECT * FROM itscst WHERE control='$mCONTROL' ");
		foreach ( $query->result() as $row )
		{
			if($mATASAS==0) $mATASAS = $this->datasis->ivaplica($row->fecha);
			$mIVA  = $row->iva;
			$mTOTA = $row->importe;
			if ( $mIVA == $mATASAS['tasa']) {
				$mTASA    += round($mTOTA*$mIVA/100,2);
				$mMONTASA += $mTOTA;

			} elseif ( $mIVA == $mATASAS['redutasa']) {
      			$mREDUCIDA += round($mTOTA*$mIVA/100,2);
				$mMONREDU  += $mTOTA;

			} elseif ( $mIVA == $mATASAS['sobretasa']) {
				$mSOBRETASA += round($mTOTA*$mIVA/100,2);
				$mMONADIC   += $mTOTA;

			} elseif ( $mIVA == 0 ) {
				$mEXENTO += $mTOTA;
			}
		}

		$mSQL = "UPDATE scst SET exento=$mEXENTO, tasa=$mTASA,montasa=$mMONTASA,reducida=$mREDUCIDA,monredu=$mMONREDU,sobretasa=$mSOBRETASA,monadic=$mMONADIC WHERE control=$mCONTROL ";
		$this->db->simple_query($mSQL);
	} 
}
?>