<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Libros extends Controller {

	function Libros() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(504,1);
		$this->load->helper('date');
	}

	function index() {
		$this->rapyd->load("datagrid");
		$this->load->helper('fecha');

		for($i=1;$i<=12;$i++) $mmes[ str_pad($i, 2, "0", STR_PAD_LEFT)]=mesLetra($i);
		for($i=date('Y'); $i>=date('Y')-4;$i--) $anhos[$i]=$i;

		$descarga=$genera=array();
		$query = $this->db->query("SELECT * FROM libros WHERE activo='S'");
		foreach ($query->result() as $row){
			if($row->tipo=='D')
				$descarga[]=array('accion'=>$row->metodo, 'nombre' => $row->nombre);
			else
				$genera[]=array('accion'=>$row->metodo, 'nombre' => $row->nombre,'estampa'=>$row->estampa,'fgenera'=>$row->fgenera);
		}
		
		$checkbox="<input type='checkbox' name='generar[]' value='<#accion#>' /> ";
		$submit= form_submit('<#accion#>', 'Generar');;
		$sanio= form_dropdown('year',$anhos,date('Y'));
		$smes = form_dropdown('mes',$mmes,date('m'));

		function obser($gene,$estampa,$metodo){
			if (empty($gene) or empty($estampa)) return "<span id='obs_$metodo'>Niguna</span>";
			$hestampa=dbdate_to_human($estampa,'d/m/Y h:i a');
			$hgene=substr($gene,4).'/'.substr($gene,0,4);
			return "<span id='obs_$metodo'>Generado el <b>$hestampa</b> para el mes <b>$hgene</b></span>";
		}
		
		$gene = new DataGrid("Documento para el mes $smes del a&ntilde;o $sanio",$genera);
		$gene->use_function('obser');
		$gene->per_page = count($genera);
		$gene->column("Generar"  ,$checkbox);
		$gene->column("Documento","nombre");
		$gene->column("Observaciones","<obser><#fgenera#>|<#estampa#>|<#accion#></obser>");
		$gene->submit('enviar', 'Generar');
		$gene->build();

		$link='<a href="javascript:void(0);" title="Descargar" onclick="descarga(\'<#accion#>\');">Descargar</a>';
		$desca = new DataGrid("Descarga de documentos",$descarga);
		$desca->per_page = count($descarga);
		$desca->column("Descargar"  ,$link);
		$desca->column("Documento","nombre");
		$desca->build();

		$link=site_url('finanzas/libros/generar');

		$data['script']='<script type="text/javascript">
		$(document).ready(function() {
			$("form").submit(function() {
				geneDoc();
				return false;
			});
		});

		function geneDoc(){
			$("#preloader").fadeIn("slow");
			$("#contenido").fadeOut("slow");
			var url = "'.$link.'";
			$.ajax({
				type: "POST",
				url: url,
				data: $("form").serialize(),
				success: function(msg){
					$("#preloader").fadeOut("slow");
					$("#contenido").fadeIn("slow");
					objs=$(":checked");
					jQuery.each(objs, function() {
						$("#obs_"+this.value).text("Acaba de ser generado");
					});
				}
			});
		}

		function descarga(nombre){
			param=$("select[name=\'year\']").val()+$("select[name=\'mes\']").val();
			window.location="'.site_url('finanzas/libros').'/"+nombre+"/"+param;
			//alert(param);
		}
		</script>';
		$conf=anchor('finanzas/libros/configurar','Configurar');
		$form=  form_open('/finanzas/libros');
		$data['content'] = $form.$gene->output.form_close().$desca->output.$conf;
		$data['title']   = "<h1>Generar libros Contables</h1>";
		$data["head"]    = script("jquery-1.2.6.pack.js").$this->rapyd->get_head();
		$data['extras']  = $this->load->view('view_preloader',array(),true);
		$this->load->view('view_ventanas', $data);
	}
	
	function generar(){
		if(empty($_POST['generar'])) return;
		foreach($_POST['generar'] AS $gene){
			$this->$gene($_POST['year'].$_POST['mes']);
			$mSQL = "UPDATE libros SET estampa=NOW(), fgenera=$_POST[year]$_POST[mes] WHERE metodo = '$gene'";
			var_dum($this->db->simple_query($mSQL));
			echo "Generado $gene";
		}
	}

	//***********************************************
	// LIBROS Excell
	//***********************************************
	//Libro de compras contribuyente normal
	function wlcexcel($mes) {
		$mes  = $this->uri->segment(4).$this->uri->segment(5);
		//$ano = $this->uri->segment(3);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');

		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		var_dum($this->db->simple_query($mSQL));

		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		var_dum($this->db->simple_query($mSQL));
		
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
	    UNION
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

		//$export = $this->db->query($mSQL);
		
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
		$tventas = $texenta = $tbase = $timpue = $treiva = $tperci = 0 ;
		$dd=$mm;  // desde
		
		//die($mSQL);
		$mc = $this->db->query($mSQL);
		if ( $mc->num_rows() > 0 ) {
			foreach( $mc->result() as $row ) {
				$ws->write_string( $mm,  0, $ii, $cuerpo );
				$ws->write_string( $mm,  1, substr($row->fecha,8,2)."/".substr($row->fecha,5,2)."/".substr($row->fecha,0,4), $cuerpo );
				$ws->write_string( $mm,  2, $row->tipo_doc,  $cuerpo ); 
				$ws->write_string( $mm,  3, $row->numo,  $cuerpo ); 
				$ws->write_string( $mm,  5, $row->nombre,  $cuerpo ); 
				$ws->write_string( $mm,  6, $row->rif,  $cuerpo );
				if ($row->oper != '04' ){
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
		//$ws->write($mm, 8, '30', $h1 );
		//$ws->write_formula($mm, 7, "=H$celda" , $Rnumero );
		
		$mm ++;
		$mTot = $mm;
		$ws->write($mm, 5, 'Total Importaciones Gravadas por Alicuota General', $h3 );
		$ws->write_blank($mm, 2, $h1 );	
		$ws->write_blank($mm, 3, $h1 );	
		$ws->write_blank($mm, 4, $h1 );	
		//$ws->write_blank($mm, 5, $h1 );	
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
		//print "$header\n$data";
	}
	
	//Libro de compras para supermercado
	function wlcsexcel($mes) {
		//$mes = $this->uri->segment(3);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		
		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		var_dum($this->db->simple_query($mSQL));
		
		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		var_dum($this->db->simple_query($mSQL));
		$tasa = $this->datasis->traevalor('TASA');
		
		
		$mSQL = "SELECT DISTINCT 
		    a.sucursal, 
		          IF(f.fecdoc IS NOT NULL, IF(a.tipo='NC',f.fecapl,f.fecdoc), a.fecha ) fecha,
		    a.rif,
		    IF(SUBSTR(a.rif,1,1)='V' AND d.nombre IS NOT NULL,d.nombre,IF(substr(a.rif,1,1)='J' AND d.nombre IS NOT NULL, d.nombre , a.nombre)) AS nombre, 
		    a.contribu,
		          a.referen,
		          a.planilla,'  ' meco1,
		          a.numero,
		    a.nfiscal,
		          IF(a.tipo='ND',a.numero,'        ') numnd,
		          IF(a.tipo='NC',a.numero,'        ') numnc,
		    IF(a.tipo='FC','01-Reg','03-Reg') oper, 
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
		    b.emision, a.numero numo, a.tipo
		          FROM siva AS a LEFT JOIN riva AS b ON a.numero=b.numero and a.clipro=b.clipro AND a.tipo=b.tipo_doc AND MID(b.transac,1,1)<>'_' 
		                   LEFT JOIN provoca AS d ON a.rif=d.rif 
				   LEFT JOIN scst e ON a.numero=e.numero AND a.tipo=e.tipo_doc AND a.clipro=e.proveed AND a.fuente='CP' 
		                   LEFT JOIN sprm f ON a.numero=f.numero AND a.clipro=f.cod_prv AND f.tipo_doc='NC'  
		          WHERE libro='C' AND EXTRACT(YEAR_MONTH FROM fechal) ='$mes' AND a.fecha>0 
		    GROUP BY a.fecha,a.tipo,numo,a.rif 
		    UNION ALL
		    SELECT DISTINCT a.sucursal, 
		          IF(e.recep IS NULL,a.fecha, a.fecha ) fecha,
		    d.rif,
		    d.nombre, 
		    a.contribu,
		          a.referen,
		          a.planilla,'  ' meco2,
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
		    b.emision, a.numero numo, a.tipo
		          FROM siva AS a      JOIN riva b ON a.numero=b.numero and a.clipro!=b.clipro AND a.tipo=b.tipo_doc AND MID(b.transac,1,1)<>'_' AND a.reiva=b.reiva 
		                   LEFT JOIN sprv d ON b.clipro=d.proveed 
				   LEFT JOIN scst e ON a.numero=e.numero AND a.tipo=e.tipo_doc AND a.clipro=e.proveed  AND a.fuente='CP' 
		          WHERE libro='C' AND EXTRACT(YEAR_MONTH FROM fechal) ='$mes' AND a.fecha>0 AND a.reiva>0 
		    GROUP BY a.fecha,a.tipo,numo,a.rif
		    ORDER BY fecha,numo " ;
		
		$export = $this->db->query($mSQL);

		$fname = tempnam("/tmp","lcompras.xls");
		
		$this->load->library("workbook", array("fname"=>$fname));
		$wb = & $this->workbook ;
		$ws = & $wb->addworksheet($mes);
		
		# ANCHO DE LAS COLUMNAS
		$ws->set_column('A:A',4);
		$ws->set_column('B:C',11);
		$ws->set_column('D:D',45);
		$ws->set_column('E:F',5);
		$ws->set_column('G:H',11);
		$ws->set_column('I:I',6);
		$ws->set_column('J:J',11);
		$ws->set_column('K:K',6);
		$ws->set_column('L:T',16);
		$ws->set_column('X:X',16);
		$ws->set_column('U:U',18);
		$ws->set_column('V:V',11);
		
		// FORMATOS
		$h       =& $wb->addformat(array( "bold" => 1, "size" => 16, "merge" => 1, "top" => 1));
		$h1      =& $wb->addformat(array( "bold" => 1, "size" => 11, "align" => 'left'));
		$titulo  =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 0, "fg_color" => 'silver' ));
		$cuerpo  =& $wb->addformat(array( "size" => 9 ));
		$numero  =& $wb->addformat(array(  "num_format" => '#,##0.00' , "size" => 9 ));
		$Tnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "fg_color" => 'silver' ));
		$Rnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "align" => 'right' ));
		$tm      =& $wb->addformat(array( "bold" => 1, "size" => 9, "merge" => 1, "fg_color" => 'silver' ));
		
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
		
		// PONE FONDO	
		for ( $i=0; $i<24; $i++ ) {
			$ws->write_blank( $mm,   $i, $titulo );
			$ws->write_blank( $mm+1, $i, $titulo );
			$ws->write_blank( $mm+2, $i, $titulo );
		}
		
		$ws->write_string( $mm,  0, "", $titulo );           
		$ws->write_string( $mm,  1, "Fecha", $titulo );
		$ws->write_string( $mm,  2, "", $titulo );
		$ws->write_string( $mm,  3, "", $titulo );
		$ws->write_string( $mm,  4, "Tipo", $titulo );
		$ws->write_string( $mm,  5, "Tipo", $titulo );
		$ws->write_string( $mm,  6, "Numero", $titulo );
		$ws->write_string( $mm,  7, "Numero", $titulo );
		$ws->write_string( $mm,  8, "Tipo", $titulo );
		$ws->write_string( $mm,  9, "Numero", $titulo );
		$ws->write_string( $mm, 10, "Tipo de", $titulo );
		$ws->write_string( $mm, 11, "Total Compras", $titulo );
		$ws->write_string( $mm, 12, "Compras sin", $titulo );
		$ws->write_string( $mm, 13, "COMPRAS GRAVADAS O CON DERECHO A CREDITO FISCAL", $tm );
		$ws->write_blank(  $mm, 14, $tm);
		$ws->write_blank(  $mm, 15, $tm);
		$ws->write_blank(  $mm, 16, $tm);
		$ws->write_blank(  $mm, 17, $tm);
		$ws->write_blank(  $mm, 18, $tm);
		$ws->write_string( $mm, 19,"I.V.A.", $titulo );
		$ws->write_string( $mm, 20, "Numero",$titulo );
		$ws->write_string( $mm, 21, "Fecha",$titulo );
		$ws->write_string( $mm, 22,  "I.V.A.", $titulo );
		$ws->write_string( $mm, 23,  "Anticipo", $titulo );
		$mm++;
		$ws->write_string( $mm,  0, "Oper.", $titulo );
		$ws->write_string( $mm,  1, "de la", $titulo );
		$ws->write_string( $mm,  2, "R.I.F.", $titulo );
		$ws->write_string( $mm,  3, "Nombre,", $titulo );
		$ws->write_string( $mm,  4, "de", $titulo );
		$ws->write_string( $mm,  5, "de", $titulo );
		$ws->write_string( $mm,  6, "del", $titulo );
		$ws->write_string( $mm,  7, "Control", $titulo );
		$ws->write_string( $mm,  8, "de", $titulo );
		$ws->write_string( $mm,  9, "Documento", $titulo );
		$ws->write_string( $mm, 10, "Compra", $titulo );
		$ws->write_string( $mm, 11, "Incluyendo", $titulo );
		$ws->write_string( $mm, 12, "Derecho a", $titulo );
		$ws->write_string( $mm, 13, "ALICUOTA GENERAL", $tm );
		$ws->write_blank(  $mm, 14, $tm);
		
		$ws->write_string( $mm, 15, "ALICUOTA ADICIONAL", $tm );
		$ws->write_blank(  $mm, 16, $tm);
		
		$ws->write_string( $mm, 17, "ALICUOTA REDUCIDA",$tm );
		$ws->write_blank(  $mm, 18, $tm);
		
		$ws->write_string( $mm, 19, "Retenido",$titulo );
		$ws->write_string( $mm, 20, "de", $titulo );
		$ws->write_string( $mm, 21, "de",$titulo );
		$ws->write_string( $mm, 22, "Retenido", $titulo );
		$ws->write_string( $mm, 23, "de I.V.A. por", $titulo );
		$mm++;
		$ws->write_string( $mm,  0, "Nro.", $titulo );
		$ws->write_string( $mm,  1, "Factura", $titulo );
		$ws->write_string( $mm,  2, "Proveedor", $titulo );
		$ws->write_string( $mm,  3, "Denominacion o Razon Social", $titulo );
		$ws->write_string( $mm,  4, "Prov.", $titulo );
		$ws->write_string( $mm,  5, "Doc.", $titulo );
		$ws->write_string( $mm,  6, "Documento", $titulo );
		$ws->write_string( $mm,  7, "Fiscal", $titulo );
		$ws->write_string( $mm,  8, "Trans.", $titulo );
		$ws->write_string( $mm,  9, "Afectado", $titulo );
		$ws->write_string( $mm, 10, "Nac/Imp.", $titulo );
		$ws->write_string( $mm, 11, "El I.V.A.", $titulo );
		$ws->write_string( $mm, 12, "Cred. Fiscal", $titulo );
		$ws->write_string( $mm, 13, "Base", $titulo );
		$ws->write_string( $mm, 14, "Impuesto", $titulo );
		$ws->write_string( $mm, 15, "Base", $titulo );
		$ws->write_string( $mm, 16, "Impuesto", $titulo );
		$ws->write_string( $mm, 17, "Base", $titulo );
		$ws->write_string( $mm, 18, "Impuesto", $titulo );
		$ws->write_string( $mm, 19, "Al Vendedor", $titulo );
		$ws->write_string( $mm, 20, "Comprobante", $titulo );
		$ws->write_string( $mm, 21, "Emision", $titulo );
		$ws->write_string( $mm, 22, "a Terceros", $titulo );
		$ws->write_string( $mm, 23, "Importacion", $titulo );
		
		$mm++;
		$ii = 1;
		$mtiva = 'X';
		$mfecha = '2000-00-00';
		$tventas = $texenta = $tbase   = $timpue  = $treiva  = $tperci  = 0 ;
		$dd=$mm;  // desde
		
		foreach( $export->result() as $row ) {
			$ws->write_string( $mm,  0, $ii, $cuerpo );
			$ws->write_string( $mm,  1, substr($row->fecha,8,2)."-".$ameses[substr($row->fecha,5,2)-1]."-".substr($row->fecha,0,4), $cuerpo );
			$ws->write_string( $mm,  2, $row->rif,      $cuerpo );
			$ws->write_string( $mm,  3, $row->nombre,   $cuerpo ); 
			$ws->write_string( $mm,  4, $row->contribu, $cuerpo ); 
			$ws->write_string( $mm,  5, $row->tipo,     $cuerpo ); 
			$ws->write_string( $mm,  6, $row->numero,   $cuerpo ); 
			$ws->write_string( $mm,  7, $row->nfiscal,  $cuerpo ); 
			$ws->write_string( $mm,  8, $row->oper,     $cuerpo ); 
			$ws->write_string( $mm,  9, $row->referen,  $cuerpo ); 
			$ws->write_string( $mm, 10, 'Nac.',         $cuerpo );
			
			$ws->write_number( $mm, 11, $row->gtotal,   $numero );
			$ws->write_number( $mm, 12, $row->exento,   $numero );
			$ws->write_number( $mm, 13, $row->general,  $numero );
			$ws->write_number( $mm, 14, $row->geneimpu, $numero );
			$ws->write_number( $mm, 15, $row->adicional,$numero );
			$ws->write_number( $mm, 16, $row->adicimpu, $numero );
			$ws->write_number( $mm, 17, $row->reducida, $numero );
			$ws->write_number( $mm, 18, $row->reduimpu, $numero );
			$ws->write_number( $mm, 19, $row->reiva,    $numero );
			
			$ws->write_string( $mm, 20, $row->nrocomp, $cuerpo );
			if ( !empty($row->emision) ) {
				$ws->write_string( $mm, 21, substr($row->emision,8,2)."-".$ameses[substr($row->emision,5,2)-1]."-".substr($row->emision,0,4), $cuerpo );
			} else {
				$ws->write_string( $mm, 21, $row->emision, $cuerpo );
			}
		  $ws->write_number( $mm, 22, 0, $numero );
		  $ws->write_number( $mm, 23, 0, $numero );
		  $mm++;
		  $ii++;
		}
		
		$celda = $mm+1;
		$fventas = "=J$celda";   // VENTAS
		$fexenta = "=K$celda";   // VENTAS EXENTAS
		$fbase   = "=L$celda";   // BASE IMPONIBLE
		$fiva    = "=N$celda";   // I.V.A. 
		
		$ws->write_string( $mm, 0,"Totales...",  $tm );
		$ws->write_blank( $mm,  1,  $tm );
		$ws->write_blank( $mm,  2,  $tm );
		$ws->write_blank( $mm,  3,  $tm );
		$ws->write_blank( $mm,  4,  $tm );
		$ws->write_blank( $mm,  5,  $tm );
		$ws->write_blank( $mm,  6,  $tm );
		$ws->write_blank( $mm,  7,  $tm );
		$ws->write_blank( $mm,  8,  $tm );
		
		$ws->write_blank( $mm,  9,  $tm );
		$ws->write_blank( $mm, 10,  $tm );
		
		$ws->write_formula( $mm, 11, "=SUM(L$dd:L$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula( $mm, 12, "=SUM(M$dd:M$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula( $mm, 13, "=SUM(N$dd:N$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		
		$ws->write_formula( $mm, 14, "=SUM(O$dd:O$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula( $mm, 15, "=SUM(P$dd:P$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula( $mm, 16, "=SUM(Q$dd:Q$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		
		$ws->write_formula( $mm, 17, "=SUM(R$dd:R$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula( $mm, 18, "=SUM(S$dd:S$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		
		$ws->write_formula( $mm, 19, "=SUM(T$dd:T$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula( $mm, 22, "=SUM(W$dd:W$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		
		$ws->write_formula( $mm, 23, "=SUM(X$dd:X$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		
		$mm ++;
		$mm ++;
		$ws->write_string($mm,  3, 'RESUMEN DE COMPRAS Y CREDITOS:', $tm );
		$ws->write_blank($mm,   4,$tm);
		$ws->write_blank($mm,   5,$tm);
		$ws->write_blank($mm,   6,$tm);
		$ws->write_blank($mm,   7,$tm);
		$ws->write_blank($mm,   8,$tm);
		$ws->write_blank($mm,   9,$tm);
		$ws->write_blank($mm,  10,$tm);
		$ws->write_string($mm, 11, 'Base Imponible', $titulo );
		$ws->write_string($mm, 12, 'Credito Fiscal', $titulo );
		
		$ws->write_string($mm,  14, 'IVA Retenido', $titulo );
		$ws->write_string($mm,  15, 'Anticipo IVA', $titulo );
		
		
		$mm ++;
		$ws->write_string($mm,   3, 'Total Compras no Gravadas o/y sin dereco a Credito', $h1 );
		$ws->write_formula($mm, 11, "=M$celda" , $Rnumero );
		
		$mm ++;
		$ws->write_string($mm,   3, 'Total Importaciones Gravadas por Alicuota General:', $h1 );
		$ws->write_formula($mm, 11, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 12, "=0+0" , $Rnumero );
		
		$ws->write_formula($mm, 14, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 15, "=0+0" , $Rnumero );
		
		$mm ++;
		$ws->write_string($mm,   3, 'Total Importaciones Gravadas por Alicuota General mas Adicional:', $h1 );
		$ws->write_formula($mm, 11, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 12, "=0+0" , $Rnumero );
		
		$ws->write_formula($mm, 14, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 15, "=0+0" , $Rnumero );
		
		$mm ++;
		$ws->write_string($mm,   3, 'Total Importaciones Gravadas por Alicuota Reducida:', $h1 );
		$ws->write_formula($mm, 11, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 12, "=0+0" , $Rnumero );
		
		$ws->write_formula($mm, 14, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 15, "=0+0" , $Rnumero );
		
		$mm ++;
		$ws->write_string($mm,   3, 'Total Compras Internas Gravadas por Alicuota General:', $h1 );
		$ws->write_formula($mm, 11, "=N$celda" , $Rnumero );
		$ws->write_formula($mm, 12, "=O$celda" , $Rnumero );
		
		$ws->write_formula($mm, 14, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 15, "=0+0" , $Rnumero );
		
		$mm ++;
		$ws->write_string($mm,   3, 'Total Compras Internas Gravadas por Alicuota General mas Adicional:', $h1 );
		$ws->write_formula($mm, 11, "=P$celda" , $Rnumero );
		$ws->write_formula($mm, 12, "=Q$celda" , $Rnumero );
		
		$ws->write_formula($mm, 14, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 15, "=0+0" , $Rnumero );
		
		$mm ++;
		$ws->write_string($mm,   3, 'Total Compras Internas Gravadas por Alicuota Reducida:', $h1 );
		$ws->write_formula($mm, 11, "=R$celda" , $Rnumero );
		$ws->write_formula($mm, 12, "=S$celda" , $Rnumero );
		
		$ws->write_formula($mm, 14, "=0+0" , $Rnumero );
		$ws->write_formula($mm, 15, "=0+0" , $Rnumero );
		
		$mm ++;
		$wb->close();
		header("Content-type: application/x-msexcel; name=\"lcompras.xls\"");
		header("Content-Disposition: inline; filename=\"lcompras.xls\"");
		$fh=fopen($fname,"rb");
		fpassthru($fh);
		unlink($fname);
	}


	//Libro de ventas con pto de ventas contribuyente normal
	function wlvexcelpdv1($mes) {
		//$mes = $this->uri->segment(3);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		$tasa = $this->datasis->traevalor('TASA');

		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		var_dum($this->db->simple_query($mSQL));

		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		var_dum($this->db->simple_query($mSQL));

		$mSQL = "UPDATE club SET cedula=CONCAT('V',cedula) WHERE MID(cedula,1,1) IN ('0','1','2','3','4','5','6','7','8','9')  ";
		var_dum($this->db->simple_query($mSQL));

		$mSQL ="SELECT a.fecha AS fecha, a.numero AS numero, a.numero AS final, c.cedula AS rif, 
		    CONCAT(c.nombres,' ', c.apellidos) AS nombre, 
	    	' ' AS numnc, 
		    ' ' AS numnd,
		    IF(MID(a.numero,1,2)='NC','NC','FC') AS tipo_doc,  
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
		    WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$year$mes AND a.libro='V' AND a.fuente<>'PV' AND a.tipo<>'RI' 
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
		    WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$year$mes AND a.libro='V' AND a.fuente<>'PV' AND a.tipo='RI' 
	    	ORDER BY fecha, caja, numa ";

		echo $mSQL; die;
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
			}else {
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
					if ( $row->tipo_doc == 'FC' ) 
						$finicial = $row->numero;
					else 
						$finicial = '99999999';
					$mm++;
					$caja = $row->caja;
				}
			}
			if ($contri) {
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
		//print "$header\n$data";
	}
    
	function prorrata1($anomes){
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
		if ( $export->num_rows() ) {
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

	function invresu($anomes){
		$mes = $anomes;
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		
		set_time_limit(300);
		
		$fname = tempnam("/tmp","invresu.xls");
		$this->load->library("workbook", array("fname"=>$fname));
		$wb =& $this->workbook;
		$ws =& $wb->addworksheet($mes);
		
		# ANCHO DE LAS COLUMNAS
		$ws->set_column('A:A',6);
		$ws->set_column('B:B',15);
		$ws->set_column('C:C',37);
		$ws->set_column('D:I',8);
		$ws->set_column('J:W',14);
		//$ws->set_column('Z:Z',12);
		
		# FORMATOS
		$h       =& $wb->addformat(array( "bold" => "1", "size" => "14", "merge" => "1"));
		$h1      =& $wb->addformat(array( "bold" => "1", "size" => "11", "align" => 'left'));
		$titulo  =& $wb->addformat(array( "bold" => "1", "size" =>  "9", "merge" => "0", "fg_color" => 'silver' ));
		$ht      =& $wb->addformat(array( "bold" => "1", "size" => "11", "merge" => "1", "fg_color" => 'green'  ));
		$cuerpo  =& $wb->addformat(array( "size" => "9" ));
		$numero  =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9 ));
		$Tnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "fg_color" => 'silver' ));
		$Rnumero =& $wb->addformat(array( "num_format" => '#,##0.00' , "size" => 9, "bold" => 1, "align" => 'right' ));
		
		# COMIENZA A ESCRIBIR
		$nomes = substr($mes,4,2);
		$nomes1 = $anomeses[$nomes];
		$hs = "REGISTRO DE DETALLADO DE ENTRADAS Y SALIDAS DE INVENTARIO DE MERCANCIAS, MATERIAS PRIMAS O PRODUCTOS EN PROCESOS ";
		$hs1 = "CORRESPONDIENTE AL MES DE ".$anomeses[$nomes]." DEL ".substr($mes,0,4);
		
		$ws->write(1, 0, $this->datasis->traevalor('TITULO1') , $h1 );
		$ws->write(2, 0, "RIF: ".$this->datasis->traevalor('RIF') , $h1 );
		$ws->write(4,0, $hs, $h );
		for ( $i=1; $i < 15; $i++ ) {
		   $ws->write_blank(4, $i,  $h );
		};
		$ws->write(5, 0, $hs1, $h );
		for ( $i=1; $i < 15; $i++ ) {
		   $ws->write_blank(5, $i,  $h );
		};
		
		$mm=6;
		$ws->write_string($mm  ,0, ""           , $titulo );
		$ws->write_string($mm+1,0, ""           , $titulo );
		$ws->write_string($mm+2,0, "Nro"        , $titulo );
		$ws->write_string($mm  ,1, "Item"       , $titulo );
		$ws->write_string($mm+1,1, "de"         , $titulo );
		$ws->write_string($mm+2,1, "Inventario" , $titulo );
		$ws->write_string($mm  ,2, ""           , $titulo );
		$ws->write_string($mm+1,2, ""           , $titulo );
		$ws->write_string($mm+2,2, "Descripcion", $titulo );
		
		$ws->write($mm,    3, "UNIDADES", $ht );
		$ws->write_string($mm+1,  3, "Existencia", $titulo );
		$ws->write_string($mm+2,  3, "Anterior",   $titulo );
		
		$ws->write_blank($mm,     4, $ht );
		$ws->write_blank($mm+1,   4, $titulo );
		$ws->write_string($mm+2,  4, "Entradas", $titulo );
		
		$ws->write_blank($mm,     5,  $ht );
		$ws->write_blank($mm+1,   5,  $titulo );
		$ws->write_string($mm+2,  5, "Salidas", $titulo );
		
		$ws->write_blank($mm,     6,  $ht );
		$ws->write_blank($mm+1,   6,  $titulo );
		$ws->write_string($mm+2,  6, "Retiros", $titulo );
		
		$ws->write_blank($mm,     7,  $ht );
		$ws->write_string($mm+1,  7,  "Auto", $titulo );
		$ws->write_string($mm+2,  7,  "Consumos", $titulo );
		
		$ws->write_blank($mm,     8,  $ht );
		$ws->write_blank($mm+1,   8,  $titulo );
		$ws->write_string($mm+2,  8,  "Existencias", $titulo );
		
		$ws->write($mm,    9,  "BOLIVARES", $ht );
		$ws->write_string($mm+1,  9,  "Valor", $titulo );
		$ws->write_string($mm+2,  9,  "Anterior", $titulo );
		
		$ws->write_blank($mm,    10,  $ht );
		$ws->write_blank($mm+1,  10,  $titulo );
		$ws->write_string($mm+2, 10,  "Entradas ", $titulo );
		$ws->write_blank($mm,    11,  $ht );
		$ws->write_blank($mm+1,  11,  $titulo );
		$ws->write_string($mm+2, 11,  "Salidas", $titulo );
		$ws->write_blank($mm,    12,  $ht );
		$ws->write_blank($mm+1,  12,  $titulo );
		$ws->write_string($mm+2, 12,  "Retiros", $titulo );
		$ws->write_blank($mm,    13,  $ht );
		$ws->write_string($mm+1, 13,  "Auto", $titulo );
		$ws->write_string($mm+2, 13,  "Consumos", $titulo );
		$ws->write_blank($mm,    14,  $ht );
		$ws->write_string($mm+1, 14,  "Valor de", $titulo );
		$ws->write_string($mm+2, 14,  "Existencia", $titulo );
		
		$mm++;
		$mm++;
		$mm++;
		
		$ii = 1;
		$mtiva = 'X';
		$mfecha = '2000-00-00';
		$tventas = $texenta = $tbase   = $timpue  = $treiva  = $tperci  = 0 ;
		$dd=$mm;  // desde
		
		$mSQL = "SELECT a.codigo, b.descrip, a.inicial, a.compras, a.ventas, a.trans, a.fisico, a.notas, a.final, a.minicial, a.mcompras, a.mventas, a.mtrans, a.mfisico, a.mnotas, a.mfinal FROM invresu a LEFT JOIN sinv b ON a.codigo=b.codigo WHERE mes=$mes "; 
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$ws->write_string($mm,  0, $ii, $cuerpo );
				$ws->write_string($mm,  1, $row->codigo  , $cuerpo );  // codigo
				$ws->write_string($mm,  2, $row->descrip , $cuerpo );  // descrip
				$ws->write_number($mm,  3, $row->inicial , $numero );  // inicial
				$ws->write_number($mm,  4, $row->compras , $numero );  // entradas
				$ws->write_number($mm,  5, $row->ventas  , $numero );  // salidas
				$ws->write_number($mm,  6, $row->trans   , $numero );  // retiros
				$ws->write_number($mm,  7, $row->fisico  , $numero );  // autoconsumos
				$ws->write_number($mm,  8, $row->final   , $numero );  // final
				$ws->write_number($mm,  9, $row->minicial, $numero );  // minicial
				$ws->write_number($mm, 10, $row->mcompras, $numero );  // entradas
				$ws->write_number($mm, 11, $row->mventas , $numero );  // salidas
				$ws->write_number($mm, 12, $row->mtrans  , $numero );  // retiros
				$ws->write_number($mm, 13, $row->mfisico , $numero );  // autoconsumos
				$ws->write_number($mm, 14, $row->mfinal  , $numero );  // final
				$mm++;
				$ii++;
			}
		}
		
		$celda = $mm+1;
		$fventas = "=J$celda";   // VENTAS
		$fexenta = "=K$celda";   // VENTAS EXENTAS
		$fbase   = "=L$celda";   // BASE IMPONIBLE
		$fiva    = "=N$celda";   // I.V.A. 
		
		$ws->write( $mm, 0,"Totales...",  $Tnumero );
		$ws->write_blank( $mm,  1,  $Tnumero );
		$ws->write_blank( $mm,  2,  $Tnumero );
		
		$ws->write_formula($mm,  3, "=SUM(D$dd:D$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula($mm,  4, "=SUM(E$dd:E$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula($mm,  5, "=SUM(F$dd:F$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_formula($mm,  6, "=SUM(G$dd:G$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula($mm,  7, "=SUM(H$dd:H$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula($mm,  8, "=SUM(I$dd:I$mm)", $Tnumero );   //"BASE IMPONIBLE" 
		$ws->write_formula($mm,  9, "=SUM(J$dd:J$mm)", $Tnumero );   //"VENTAS + IVA" 
		$ws->write_formula($mm, 10, "=SUM(K$dd:K$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula($mm, 11, "=SUM(L$dd:L$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula($mm, 12, "=SUM(M$dd:M$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula($mm, 13, "=SUM(N$dd:N$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		$ws->write_formula($mm, 14, "=SUM(O$dd:O$mm)", $Tnumero );   //"VENTAS EXENTAS" 
		
		$wb->close();
		header("Content-type: application/x-msexcel; name=\"invresu.xls\"");
		header("Content-Disposition: inline; filename=\"invresu.xls\"");
		$fh=fopen($fname,"rb");
		fpassthru($fh);
		unlink($fname);		
	}

	function wlvexcel($mes) {
		//$mes = $this->uri->segment(4).$this->uri->segment(5);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		
		$aaa = $this->datasis->ivaplica($mes."02");
		$tasa      = $aaa['tasa'];
		$redutasa  = $aaa['redutasa'];
		$sobretasa = $aaa['sobretasa'];
		
		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		var_dum($this->db->simple_query($mSQL));
		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		var_dum($this->db->simple_query($mSQL));
		
		$mSQL = "UPDATE siva SET tipo='FC' WHERE tipo='FE' ";
		var_dum($this->db->simple_query($mSQL));
		
		if ( $this->datasis->traevalor('LIBROVENTASRESUMEN') =='N' ) {
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
			WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.tipo<>'FA' 
			ORDER BY a.fecha, IF(a.tipo IN ('FE','FC','XE','XC'),1,2), a.numero ";
		} else {
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
			UNION
			SELECT 
				a.fecha,
				' ' numero, min(a.numero) inicial, max(a.numero) final,
				' ' nfiscal,
				' ' rif,
				'A NO CONTRIBUYENTES TOTAL DEL DIA' nombre,
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
			WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.tipo<>'FA' AND a.contribu='NO' AND a.tipo IN ('FE','FC','NC')
			GROUP BY a.fecha, a.tipo
			ORDER BY fecha, IF(tipo IN ('FE','FC','XE','XC'),1,2), numero ";
		}
		
		$export = $this->db->query($mSQL);
		
		/*		
				'            ' comprobante,
				'            ' fechacomp,
				'            ' impercibido,
				'            ' importacion,
		*/
		
		################################################################
		#
		#  Encabezado
		################################################################
		
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
		
		if ( $export->num_rows() > 0 ){
			foreach( $export->result() as $row ){
				// imprime contribuyente
				$fecha = substr($row->fecha,8,2)."/".substr($row->fecha,5,2)."/".substr($row->fecha,0,4);
				
				$ws->write_string( $mm, 0, $fecha,  $cuerpo );				// Fecha
				$ws->write_string( $mm, 1, ' ', $cuerpo );			// Numero de Caja
				
				if ($row->tipo[0] == "X" ) 
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

				if ($row->tipo[0] == "X" ) 
					$ws->write_string( $mm, 6, "Documento Anulado", $cuerpo );			// NOMBRE
				else
					$ws->write_string( $mm, 6, $row->nombre, $cuerpo );			// NOMBRE
				$ws->write_string( $mm, 7, $row->rif, $cuerpo );			// CONTRIBUYENTE
				
				if ( $row->registro=='04' ) {
					$ws->write_number( $mm, 8, 0, $numero );		// VENTAS + IVA
					$ws->write_number( $mm, 9, 0, $numero );		// VENTAS EXENTAS
					$ws->write_number( $mm,10, 0, $cuerpo );		// EXPORTACION
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
		//$fivaperu  = "=U$celda";   // general
		
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
		
		//$ws->write_blank( $mm, 18,  $Tnumero );
		$ws->write_blank( $mm, 19,  $Tnumero );
		$ws->write_blank( $mm, 20,  $Tnumero );
		//$ws->write_formula( $mm,20, "=SUM(U$ii:U$mm)", $Tnumero );   //IMPUESTO PERCIBIDO 
		
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
		
		//$ws->write($mm,11, 'Items', $titulo );
		//$ws->write( $mm+1, 11, "  ",  $titulo );
		
		//$ws->write($mm,12, 'IVA Ret.', $titulo );
		//$ws->write($mm+1,12, 'Retetenido', $titulo );
		
		//$ws->write($mm,13, 'Items', $titulo );
		//$ws->write( $mm+1, 13, "  ",  $titulo );
		
		//$ws->write($mm,  14, 'IVA', $titulo );
		//$ws->write($mm+1,14, 'Percibido', $titulo );
		
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
		
		//$ws->write($mm, 11, "66" , $cuerpoc );
		//$ws->write_number($mm, 12, "0", $Rnumero );
		//$ws->write($mm, 13, "68" , $cuerpoc );
		//$ws->write_number($mm, 14, "0", $Rnumero );
		
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
		//print "$header\n$data";
	}

	//Libro de ventas fiscal	
	function wlvexcelfiscal($mes) {
		//$mes = $this->uri->segment(4).$this->uri->segment(5);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		
		$aaa = $this->datasis->ivaplica($mes."02");
		$tasa      = $aaa['tasa'];
		$redutasa  = $aaa['redutasa'];
		$sobretasa = $aaa['sobretasa'];
		
		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		var_dum($this->db->simple_query($mSQL));
		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		var_dum($this->db->simple_query($mSQL));
		
		$mSQL = "UPDATE siva SET tipo='FC' WHERE tipo='FE' ";
		var_dum($this->db->simple_query($mSQL));
		
	
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
			UNION
			SELECT 
				a.fecha,
				' ' numero, min(a.numero) inicial, max(a.numero) final,
				' ' nfiscal,
				' ' rif,
				'A NO CONTRIBUYENTES TOTAL DEL DIA' nombre,
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
			WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.tipo<>'FA' AND a.contribu='NO' AND a.tipo IN ('FE','FC','NC')
			GROUP BY a.fecha, a.tipo
			ORDER BY fecha, IF(tipo IN ('FE','FC','XE','XC'),1,2), numero ";
		
		$export = $this->db->query($mSQL);
		
		/*		
				'            ' comprobante,
				'            ' fechacomp,
				'            ' impercibido,
				'            ' importacion,
		*/
		
		################################################################
		#
		#  Encabezado
		################################################################
		
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
		
		if ( $export->num_rows() > 0 ){
			foreach( $export->result() as $row ){
				// imprime contribuyente
				$fecha = substr($row->fecha,8,2)."/".substr($row->fecha,5,2)."/".substr($row->fecha,0,4);
				
				$ws->write_string( $mm, 0, $fecha,  $cuerpo );				// Fecha
				$ws->write_string( $mm, 1, ' ', $cuerpo );			// Numero de Caja
				
				if ($row->tipo[0] == "X" ) 
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

				if ($row->tipo[0] == "X" ) 
					$ws->write_string( $mm, 6, "Documento Anulado", $cuerpo );			// NOMBRE
				else
					$ws->write_string( $mm, 6, $row->nombre, $cuerpo );			// NOMBRE
				$ws->write_string( $mm, 7, $row->rif, $cuerpo );			// CONTRIBUYENTE
				
				if ( $row->registro=='04' ) {
					$ws->write_number( $mm, 8, 0, $numero );		// VENTAS + IVA
					$ws->write_number( $mm, 9, 0, $numero );		// VENTAS EXENTAS
					$ws->write_number( $mm,10, 0, $cuerpo );		// EXPORTACION
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
		//$fivaperu  = "=U$celda";   // general
		
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
		
		//$ws->write_blank( $mm, 18,  $Tnumero );
		$ws->write_blank( $mm, 19,  $Tnumero );
		$ws->write_blank( $mm, 20,  $Tnumero );
		//$ws->write_formula( $mm,20, "=SUM(U$ii:U$mm)", $Tnumero );   //IMPUESTO PERCIBIDO 
		
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
		//print "$header\n$data";
	}
	
	//libro de ventas separado por sucursal
	function wlvexcelsucu($mes) {
		//$mes = $this->uri->segment(4).$this->uri->segment(5);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		
		$aaa = $this->datasis->ivaplica($mes."02");
		$tasa      = $aaa['tasa'];
		$redutasa  = $aaa['redutasa'];
		$sobretasa = $aaa['sobretasa'];
		
		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		var_dum($this->db->simple_query($mSQL));
		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		var_dum($this->db->simple_query($mSQL));
		
		$mSQL = "UPDATE siva SET tipo='FC' WHERE tipo='FE' ";
		var_dum($this->db->simple_query($mSQL));
		
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
			UNION
			SELECT 
				a.fecha,
				' ' numero, min(a.numero) inicial, max(a.numero) final,
				' ' nfiscal,
				' ' rif,
				'A NO CONTRIBUYENTES TOTAL DEL DIA' nombre,
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
			WHERE EXTRACT(YEAR_MONTH FROM a.fechal)=$mes AND a.libro='V' AND a.tipo<>'FA' AND a.contribu='NO' AND a.tipo IN ('FE','FC','NC')
			GROUP BY MID(a.numero,1,1),a.fecha, a.tipo
			ORDER BY fecha, IF(tipo IN ('FE','FC','XE','XC'),1,2), numero ";
		
		$export = $this->db->query($mSQL);
		
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
		
		if ( $export->num_rows() > 0 ){
			foreach( $export->result() as $row ){
				// imprime contribuyente
				$fecha = substr($row->fecha,8,2)."/".substr($row->fecha,5,2)."/".substr($row->fecha,0,4);
				
				$ws->write_string( $mm, 0, $fecha,  $cuerpo );				// Fecha
				$ws->write_string( $mm, 1, ' ', $cuerpo );			// Numero de Caja
				
				if ($row->tipo[0] == "X" ) 
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

				if ($row->tipo[0] == "X" ) 
					$ws->write_string( $mm, 6, "Documento Anulado", $cuerpo );			// NOMBRE
				else
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
		//$fivaperu  = "=U$celda";   // general
		
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
		
		//$ws->write_blank( $mm, 18,  $Tnumero );
		$ws->write_blank( $mm, 19,  $Tnumero );
		$ws->write_blank( $mm, 20,  $Tnumero );
		//$ws->write_formula( $mm,20, "=SUM(U$ii:U$mm)", $Tnumero );   //IMPUESTO PERCIBIDO 
		
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
		
		//$ws->write($mm,11, 'Items', $titulo );
		//$ws->write( $mm+1, 11, "  ",  $titulo );
		
		//$ws->write($mm,12, 'IVA Ret.', $titulo );
		//$ws->write($mm+1,12, 'Retetenido', $titulo );
		
		//$ws->write($mm,13, 'Items', $titulo );
		//$ws->write( $mm+1, 13, "  ",  $titulo );
		
		//$ws->write($mm,  14, 'IVA', $titulo );
		//$ws->write($mm+1,14, 'Percibido', $titulo );
		
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
		
		//$ws->write($mm, 11, "66" , $cuerpoc );
		//$ws->write_number($mm, 12, "0", $Rnumero );
		//$ws->write($mm, 13, "68" , $cuerpoc );
		//$ws->write_number($mm, 14, "0", $Rnumero );
		
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
		//print "$header\n$data";
	}

	function wlvexcel1($mes) {
		//$mes = $this->uri->segment(4).$this->uri->segment(5);
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		
		$aaa = $this->datasis->ivaplica($mes."02");
		$tasa      = $aaa['tasa'];
		$redutasa  = $aaa['redutasa'];
		$sobretasa = $aaa['sobretasa'];
		
		// ARREGLA SIVA PORSIA
		$mSQL = "UPDATE siva SET impuesto=0, geneimpu=0, exento=gtotal, stotal=gtotal, general=0 where geneimpu<0 and general>=0 ";
		var_dum($this->db->simple_query($mSQL));
		$mSQL = "UPDATE siva SET geneimpu=0, exento=exento+general, stotal=exento+general, general=0 WHERE geneimpu=0 and general<0  ";
		var_dum($this->db->simple_query($mSQL));
		
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
		################################################################
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
		
		
		if ( $export->num_rows() > 0 ){
			foreach( $export->result() as $row ) {
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
				//$ws->write_string( $mm,18, $row['comprobante'], $cuerpo );	// NRO COMPROBANTE
				//$ws->write_string( $mm,19, $row['fechacomp'], $cuerpo );	// FECHA COMPROB
				$ws->write_number( $mm,20, 0, $numero );	// IMPUESTO PERCIBIDO
				$mm++;
			}
		}
		
		//Imprime el Ultimo
		$celda = $mm+1;
		
		$fventas = "=I$celda";    // VENTAS
		$fexenta = "=J$celda";    // VENTAS EXENTAS
		$ffob    = "=K$celda";    // BASE IMPONIBLE
		$fgeneral  = "=L$celda";  // general
		$fgeneimpu = "=M$celda";  // general
		$fadicional = "=N$celda"; // general
		$fadicimpu  = "=O$celda"; // general
		$freducida = "=P$celda";  // general
		$freduimpu = "=Q$celda";  // general
		$fivaret   = "=R$celda";  // general
		$fivaperu  = "=U$celda";  // general
		
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
		//$ws->write($mm, 11, "66" , $cuerpoc );
		//$ws->write_number($mm, 12, "0", $Rnumero );
		//$ws->write($mm, 13, "68" , $cuerpoc );
		//$ws->write_number($mm, 14, "0", $Rnumero );
		//$ws->write($mm, 16, 'TK -Ticket de Caja', $cuerpo );
		//$ws->write_blank( $mm+1, 16,  $cuerpo );
		//$ws->write($mm, 18, '04 -Ajuste', $cuerpo );
		//$ws->write_blank( $mm, 19,  $cuerpo );
		$mm ++;
		
		$wb->close();
		header("Content-type: application/x-msexcel; name=\"lventas.xls\"");
		header("Content-Disposition: inline; filename=\"lventas.xls\"");
		$fh=fopen($fname,"rb");
		fpassthru($fh);
		unlink($fname);
		//print "$header\n$data";
	}

	//libro de ventas con punto de ventas
	function wlvexcelpdv($mes,$modalidad='M'){
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		$udia=days_in_month(substr($mes,4),substr($mes,0,4));
				
		if($modalidad=='Q1'){
			$fdesde=$mes.'01';
			$fhasta=$mes.'15';
		}elseif($modalidad=='Q2'){
			$fdesde=$mes.'16';
			$fhasta=$mes.$udia;
		}else{
			$fdesde=$mes.'01';
			$fhasta=$mes.$udia;
		}
		
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
		$SQL[] ="SELECT 
		 a.fecha  fecha, 
		 a.numero numero, 
		 a.numero final,
		 c.cedula rif, 
		 CONCAT(c.nombres,' ', c.apellidos) AS nombre, 
		 ' ' AS numnc, 
		 ' ' AS numnd, 
		 IF(MID(a.numero,1,2)='NC','NC','FC') AS tipo_doc,   
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
		 WHERE a.fecha >=$fdesde AND a.fecha<=$fhasta
		 GROUP BY a.fecha, a.caja, numa";
		
		$SQL[]="SELECT 
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
		 WHERE a.fechal = $fdesde AND a.fecha between $fdesde  AND $fhasta  AND a.libro='V' AND a.fuente<>'PV' AND a.tipo<>'RI'";
		 
		//RETENCIONES
		
		$SQL[]="SELECT 
		a.emiriva,
		'    ',
		'    ',
		b.rifci,
		b.nombre,
		'    ',
		'    ',
		'RI',
		a.numero,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		a.reteiva,
		a.nroriva,
		a.recriva frecep ,
		'    ',
		'    ',
		'SI',
		'RI',
		'numa',
		'MAYO',
		'    '
		FROM itccli as a JOIN scli as b on a.cod_cli=b.cliente LEFT JOIN fmay c ON a.numero=c.numero AND c.tipo='C' 
		WHERE a.recriva BETWEEN $fdesde AND $fhasta";
		
		$SQL[]="SELECT
		 a.fecha,
		 '    ',
		 '    ',
		 c.cedula, 
		 CONCAT(c.nombres,' ',c.apellidos), 
		 '    ',
		 '    ',
		 'RI',
		 a.numero,
		 0,
		 0,
		 0,
		 0,
		 0,
		 0,
		 0,
		 0,
		 0,
		 a.monto,
		 CONCAT('20',a.num_ref),
		 a.f_factura ,
		 '    ',
		 '    ',
		 'SI',
		 'RI',
		 'numa',
		 'MAYO',
		 '    ' 
		 FROM viepag a JOIN viefac b ON a.numero=b.numero AND a.f_factura=b.fecha 
		 LEFT JOIN club c ON b.cliente=c.cod_tar 
		 WHERE a.tipo='RI' AND a.f_factura BETWEEN $fdesde AND $fhasta";
		
		//fin de las retenciones
		
		$SQL[]= "SELECT 
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
		 WHERE a.fecha>=$fdesde AND a.fecha<=$fhasta AND a.tipo='A' 
		ORDER BY fecha, caja, numa ";
		$mSQL=implode(" UNION ALL ",$SQL);
		//memowrite($mSQL);
		
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
		// print "$header\n$data";
	}
	
	//libro de ventas con punto de ventas primera quincena
	function wlvexcelpdvq1($mes){
		wlvexcelpdv($mes,'Q1');
	}
	
	//libro de ventas con punto de ventas Segunda quincena
	function wlvexcelpdvq2($mes){
		wlvexcelpdv($mes,'Q2');
	}

	//libro de ventas con punto de ventas fiscal
	function wlvexcelpdvfiscal($mes,$modalidad='M'){
		set_time_limit(300);
		$ameses = array( 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
		$anomeses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		$udia=days_in_month(substr($mes,4),substr($mes,0,4));
				
		if($modalidad=='Q1'){
			$fdesde=$mes.'01';
			$fhasta=$mes.'15';
		}elseif($modalidad=='Q2'){
			$fdesde=$mes.'16';
			$fhasta=$mes.$udia;
		}else{
			$fdesde=$mes.'01';
			$fhasta=$mes.$udia;
		}
		
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
		$SQL[] ="SELECT 
		 a.fecha  fecha, 
		 a.numero numero, 
		 a.numero final,
		 c.cedula rif, 
		 CONCAT(c.nombres,' ', c.apellidos) AS nombre, 
		 ' ' AS numnc, 
		 ' ' AS numnd, 
		 IF(MID(a.numero,1,2)='NC','NC','FC') AS tipo_doc,   
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
		 IF(c.cedula REGEXP '^[VEJG][0-9]{9}$', 'SI' , 'NO') tiva, 
		 b.tipo, 
		 a.numero numa, 
		 a.caja, d.nfiscal,
		 e.serial
		 FROM vieite a 
		 LEFT JOIN viefac AS b ON a.numero=b.numero and a.caja=b.caja 
		 LEFT JOIN fiscalz AS e ON a.caja=e.caja AND a.fecha=e.fecha
		 LEFT JOIN club c ON b.cliente=c.cod_tar 
		 LEFT JOIN dine d ON a.fecha=d.fecha AND a.caja=d.caja AND a.cajero=d.cajero
		 WHERE a.fecha >=$fdesde AND a.fecha<=$fhasta AND c.cedula REGEXP '^[VEJG][0-9]{9}$'
		 GROUP BY a.fecha, a.caja, numa";
		//memowrite($mSQL[0]);
		
		//RETENCIONES
		$SQL[]="SELECT 
		a.emiriva,
		'    ',
		'    ',
		b.rifci,
		b.nombre,
		'    ',
		'    ',
		'RI',
		a.numero,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		a.reteiva,
		a.nroriva,
		a.recriva frecep ,
		'    ',
		'    ',
		'SI',
		'RI',
		'numa',
		'MAYO',
		'    ',
		' ' AS serial
		FROM itccli as a JOIN scli as b on a.cod_cli=b.cliente LEFT JOIN fmay c ON a.numero=c.numero AND c.tipo='C' 
		WHERE a.recriva BETWEEN $fdesde AND $fhasta";
		
		$SQL[]="SELECT
		 a.fecha,
		 '    ',
		 '    ',
		 c.cedula, 
		 CONCAT(c.nombres,' ',c.apellidos), 
		 '    ',
		 '    ',
		 'RI',
		 a.numero,
		 0,
		 0,
		 0,
		 0,
		 0,
		 0,
		 0,
		 0,
		 0,
		 a.monto,
		 CONCAT('20',a.num_ref),
		 a.f_factura ,
		 '    ',
		 '    ',
		 'SI',
		 'RI',
		 'numa',
		 'MAYO',
		 '    ',
		 ' ' AS serial
		 FROM viepag a JOIN viefac b ON a.numero=b.numero AND a.f_factura=b.fecha 
		 LEFT JOIN club c ON b.cliente=c.cod_tar 
		 WHERE a.tipo='RI' AND a.f_factura BETWEEN $fdesde AND $fhasta";
		//fin de las retenciones
		
		//VENTAS AL MAYOR
		$SQL[]="SELECT 
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
		 'MAYO' caja, nfiscal,
		 ' ' AS serial
		 FROM siva a LEFT JOIN scli b ON a.clipro=b.cliente
		 WHERE a.fechal = $fdesde AND a.fecha between $fdesde  AND $fhasta  AND a.libro='V' AND a.fuente<>'PV' AND a.tipo<>'RI'";
		
		//FACTURAS ANULADAS
		$SQL[]= "SELECT 
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
		 'MAYO' caja, nfiscal,
		 'SERIAL' AS serial
		 FROM fmay a 
		 WHERE a.fecha>=$fdesde AND a.fecha<=$fhasta AND a.tipo='A' 
		ORDER BY fecha, caja, numa ";
		$mSQL=implode(" UNION ALL ",$SQL);
		//memowrite($mSQL);
		
		$export = $this->db->query($mSQL);
		
		$acumulador=array(
			'FC'=>array('exento'=>0,
									'base'  =>0,
									'iva'   =>0,
									'base1' =>0,
									'iva1'  =>0,
									'base2' =>0,
									'iva2'  =>0),
			'NC'=>array('exento'=>0,
									'base'  =>0,
									'iva'   =>0,
									'base1' =>0,
									'iva1'  =>0,
									'base2' =>0,
									'iva2'  =>0),
			'NTO'=>0,
			'VTO'=>0);                     
		$fname = tempnam("/tmp","lventas.xls");
		$this->load->library("workbook",array("fname" => $fname));
		$wb =& $this->workbook;
		$ws =& $wb->addworksheet($mes);
		
		// ANCHO DE LAS COLUMNAS
		$ws->set_column('A:A',6);
		$ws->set_column('B:B',8);
		$ws->set_column('C:C',40);
		$ws->set_column('D:E',10);
		$ws->set_column('F:F',6 );
		$ws->set_column('G:K',11);
		$ws->set_column('L:U',16);
		$ws->set_column('Z:Z',12.5);
		
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
		$ws->write_string( $mm,25, "Serial", $titulo );
		
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
		$ws->write_string( $mm,25, "Serial", $titulo );
		
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
		$ws->write_string( $mm,25, "Serial", $titulo );
		
		$mm++;
		$ii = $mm+1;
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
					//Inicio de consultas al cierre z
					$DB1 = $this->load->database('default', TRUE);
					$qquery = $DB1->query("SELECT * FROM fiscalz WHERE caja='$caja' AND fecha='$mfecha' AND serial='$serial'");
					if ($qquery->num_rows() > 0){
						foreach ($qquery->result() as $fila){
							
							$venfc = $fila->exento+$fila->base+$fila->iva+$fila->base1+$fila->iva1+$fila->base2+$fila->iva2;
							$vennc = $fila->ncexento+$fila->ncbase+$fila->nciva+$fila->ncbase1+$fila->nciva1+$fila->ncbase2+$fila->nciva2;
							
							$ventot  = ($venfc         >$acumulador['VTO']         ) ? $venfc       -$acumulador['VTO']           : 0 ;
							$excen   = ($fila->exento  >$acumulador['FC']['exento']) ? $fila->exento-$acumulador['FC']['exento']  : 0 ;
							$base    = ($fila->base    >$acumulador['FC']['base']  ) ? $fila->base  -$acumulador['FC']['base']    : 0 ;
							$iva     = ($fila->iva     >$acumulador['FC']['iva']   ) ? $fila->iva   -$acumulador['FC']['iva']     : 0 ;
							$base1   = ($fila->base1   >$acumulador['FC']['base1'] ) ? $fila->base1 -$acumulador['FC']['base1']   : 0 ;
							$iva1    = ($fila->iva1    >$acumulador['FC']['iva1']  ) ? $fila->iva1  -$acumulador['FC']['iva1']    : 0 ;
							$base2   = ($fila->base2   >$acumulador['FC']['base2'] ) ? $fila->base2 -$acumulador['FC']['base2']   : 0 ;
							$iva2    = ($fila->iva2    >$acumulador['FC']['iva2']  ) ? $fila->iva2  -$acumulador['FC']['iva2']    : 0 ;
							
							$ncventot= ($vennc         >$acumulador['NTO']         ) ? $acumulador['NTO']         -$vennc         : 0 ;
							$ncexcen = ($fila->ncexento>$acumulador['NC']['exento']) ? $acumulador['NC']['exento']-$fila->ncexento: 0 ;
							$ncbase  = ($fila->ncbase  >$acumulador['NC']['base']  ) ? $acumulador['NC']['base']  -$fila->ncbase  : 0 ;
							$nciva   = ($fila->nciva   >$acumulador['NC']['iva']   ) ? $acumulador['NC']['iva']   -$fila->nciva   : 0 ;
							$ncbase1 = ($fila->ncbase1 >$acumulador['NC']['base1'] ) ? $acumulador['NC']['base1'] -$fila->ncbase1 : 0 ;
							$nciva1  = ($fila->nciva1  >$acumulador['NC']['iva1']  ) ? $acumulador['NC']['iva1']  -$fila->nciva1  : 0 ;
							$ncbase2 = ($fila->ncbase2 >$acumulador['NC']['base2'] ) ? $acumulador['NC']['base2'] -$fila->ncbase2 : 0 ;
							$nciva2  = ($fila->nciva2  >$acumulador['NC']['iva2']  ) ? $acumulador['NC']['iva2']  -$fila->nciva2  : 0 ;
							
							$acumulador['VTO']         -=$venfc       ;
							$acumulador['FC']['exento']-=$fila->exento;
							$acumulador['FC']['base']  -=$fila->base  ;
							$acumulador['FC']['iva']   -=$fila->iva   ;
							$acumulador['FC']['base1'] -=$fila->base1 ;
							$acumulador['FC']['iva1']  -=$fila->iva1  ;
							$acumulador['FC']['base2'] -=$fila->base2 ;
							$acumulador['FC']['iva2']  -=$fila->iva2  ;
              
              $acumulador['NTO']         -=$vennc         ;
							$acumulador['NC']['exento']-=$fila->ncexento;
							$acumulador['NC']['base']  -=$fila->ncbase  ;
							$acumulador['NC']['iva']   -=$fila->nciva   ;
							$acumulador['NC']['base1'] -=$fila->ncbase1 ;
							$acumulador['NC']['iva1']  -=$fila->nciva1  ;
							$acumulador['NC']['base2'] -=$fila->ncbase2 ;
							$acumulador['NC']['iva2']  -=$fila->nciva2  ;
							
							// VENTAS
							$fecha = substr($mfecha,8,2)."-".$ameses[substr($mfecha,5,2)-1]."-".substr($mfecha,0,4);
							$ws->write_string( $mm, 1, $fecha,  $cuerpo );		// Fecha
							$ws->write_string( $mm, 2, 'VENTAS A NO CONTRIBUYENTES', $cuerpo );    // Nombre
							$ws->write_string( $mm, 3, '  ******  ', $cuerpo );   // RIF/CEDULA
							$ws->write_string( $mm, 4, $caja,  $cuerpo );		      // Fecha
							$ws->write_string( $mm, 5, 'FC', $cuerpo );    		    // TIPO
							
							$ws->write_string( $mm, 6, $fila->numero, $cuerpo );     // Factura Inicial
							$ws->write_string( $mm, 7, '----', $cuerpo );     // Factura Final
							$ws->write_string( $mm, 8, '----', $cuerpo );     // Nro. N.C.
							$ws->write_string( $mm, 9, '----', $cuerpo );    	// Nro. N.D.
							
							$ws->write_string( $mm,10, '----' , $cuerpo );   // DOC. AFECTADO
							$ws->write_string( $mm,11, '----' , $cuerpo );   // CONTRIBUYENTE
							$ws->write_number( $mm,12, $ventot, $numero );   // VENTAS + IVA
							$ws->write_number( $mm,13, $excen , $numero );   // EXENTAS
							$ws->write_number( $mm,14, 0      , $numero );   // FOB

							$ws->write_number( $mm,15,$base  , $numero );    // BASE ALICUOTA GENERAL
							$ws->write_number( $mm,16,$iva   , $numero );    // IMPUESTO
							$ws->write_number( $mm,17,$base1 , $numero );    // BASE ALICUOTA ADICIONAL
							$ws->write_number( $mm,18,$iva1  , $numero );    // IMPUESTO
							$ws->write_number( $mm,19,$base2 , $numero );    // BASE ALICUOTA REDUCIDA
							$ws->write_number( $mm,20,$iva2  , $numero );    // IMPUESTO
							
							$ws->write_number( $mm,21, 0              , $numero ); // IVA RETENIDO
							$ws->write_string( $mm,22, ' '            , $cuerpo ); // NRO COMPROBANTE
							$ws->write_string( $mm,23, $fila->fecha   , $cuerpo ); // FECHA COMPROB
							$ws->write_number( $mm,24, 0              , $numero ); // IMPUESTO PERCIBIDO
							$ws->write_string( $mm,25, $serial        , $numero ); // SERIAL
							
							//NOTAS DE CREDITO
							$mm++;
							$ws->write_string( $mm, 1, $fecha,  $cuerpo );		// Fecha
							$ws->write_string( $mm, 2, 'NOTAS DE CREDITO A NO CONTRIBUYENTES', $cuerpo );    // Nombre
							$ws->write_string( $mm, 3, '  ******  ', $cuerpo );   // RIF/CEDULA
							$ws->write_string( $mm, 4, $caja,  $cuerpo );		      // Fecha
							$ws->write_string( $mm, 5, 'FC', $cuerpo );    		    // TIPO
							
							$ws->write_string( $mm, 6, $fila->numero, $cuerpo );         // Factura Inicial
							$ws->write_string( $mm, 7, '----', $cuerpo );         // Factura Final
							$ws->write_string( $mm, 8, '----', $cuerpo );         // Nro. N.C.
							$ws->write_string( $mm, 9, '----', $cuerpo );    	    // Nro. N.D.
							
							$ws->write_string( $mm,10, '----'      , $cuerpo );    // DOC. AFECTADO
							$ws->write_string( $mm,11, '----'      , $cuerpo );    // CONTRIBUYENTE
							$ws->write_number( $mm,12, $ncventot   , $numero );     // VENTAS + IVA
							$ws->write_number( $mm,13, $ncexcen, $numero );        // EXENTAS
							$ws->write_number( $mm,14, 0           , $numero );    // FOB

							$ws->write_number( $mm,15, $ncbase  , $numero );       // BASE ALICUOTA GENERAL
							$ws->write_number( $mm,16, $nciva   , $numero );       // IMPUESTO
							$ws->write_number( $mm,17, $ncbase1 , $numero );       // BASE ALICUOTA ADICIONAL
							$ws->write_number( $mm,18, $nciva1  , $numero );       // IMPUESTO
							$ws->write_number( $mm,19, $ncbase2 , $numero );       // BASE ALICUOTA REDUCIDA
							$ws->write_number( $mm,20, $nciva2  , $numero );       // IMPUESTO
							
							$ws->write_number( $mm,21, 0              , $numero ); // IVA RETENIDO
							$ws->write_string( $mm,22, ''   , $cuerpo );           // NRO COMPROBANTE
							$ws->write_string( $mm,23, ''   , $cuerpo );           // FECHA COMPROB
							$ws->write_number( $mm,24, 0              , $numero ); // IMPUESTO PERCIBIDO
							$ws->write_string( $mm,25, $serial        , $numero ); // SERIAL
						}
					}

					$acumulador=array(
						'FC'=>array('exento'=>0,
												'base'  =>0,
												'iva'   =>0,
												'base1' =>0,
												'iva1'  =>0,
												'base2' =>0,
												'iva2'  =>0),
						'NC'=>array('exento'=>0,
												'base'  =>0,
												'iva'   =>0,
												'base1' =>0,
												'iva1'  =>0,
												'base2' =>0,
												'iva2'  =>0),
						'NTO'=>0,
						'VTO'=>0 ); 
					//Fin inicio de conusltas al cierre z
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
			
			//if ( $mforza ) {
			//	// si tventas > 0 imprime totales
			//	if ( $tventas <> 0 ) {
			//		if ( $finicial == '99999999' ) $finicial = $ffinal;
			//		$ws->write_string( $mm,  0, $mm-8, $cuerpo );
			//		$fecha = substr($mfecha,8,2)."-".$ameses[substr($mfecha,5,2)-1]."-".substr($mfecha,0,4);
			//		$ws->write_string( $mm, 1, $fecha,  $cuerpo );		// Fecha
			//		$ws->write_string( $mm, 2, 'VENTAS A NO CONTRIBUYENTES', $cuerpo );    // Nombre
			//		$ws->write_string( $mm, 3, '  ******  ', $cuerpo );   // RIF/CEDULA
			//		$ws->write_string( $mm, 4, $caja,  $cuerpo );		      // Fecha
			//		$ws->write_string( $mm, 5, 'FC', $cuerpo );    		    // TIPO
			//		
			//		$ws->write_string( $mm, 6, $finicial, $cuerpo );      // Factura Inicial
			//		$ws->write_string( $mm, 7, $ffinal,   $cuerpo );      // Factura Final
			//		$ws->write_string( $mm, 8, $nfiscali, $cuerpo );      // Nro. N.C.
			//		$ws->write_string( $mm, 9, $nfiscali, $cuerpo );    	// Nro. N.D.
			//		
			//		$ws->write_string( $mm,10, $row->afecta, $cuerpo );   // DOC. AFECTADO
			//		$ws->write_string( $mm,11, 'NO',      $cuerpo );      // CONTRIBUYENTE
			//		$ws->write_number( $mm,12, $tventas,  $numero );      // VENTAS + IVA
			//		$ws->write_number( $mm,13, $texenta,  $numero );      // EXENTAS
			//		$ws->write_number( $mm,14, 0,         $numero );      // FOB
			//		
			//		$ws->write_number( $mm,15, $tbaseg, $numero );       // IVA RETENIDO
			//		$ws->write_number( $mm,16, $timpug, $numero );       // NRO COMPROBANTE
			//		$ws->write_number( $mm,17, $tbasea, $numero );       // ECHA COMPROB
			//		$ws->write_number( $mm,18, $timpua, $numero );   	   // IMPUESTO PERCIBIDO
			//		$ws->write_number( $mm,19, $tbaser, $numero );       // IMPORTACION
			//		$ws->write_number( $mm,20, $timpur, $numero );       // IMPORTACION
			//		
			//		$ws->write_number( $mm,21, $treiva,     $numero );   // IVA RETENIDO
			//		$ws->write_string( $mm,22, '',		$cuerpo );         // NRO COMPROBANTE
			//		$ws->write_string( $mm,23, '',		$cuerpo );         // FECHA COMPROB
			//		$ws->write_number( $mm,24, $tperci,	$numero );       // IMPUESTO PERCIBIDO
			//		
			//		$tventas = $texenta = $tbaseg  = $tbaser  = $tbasea  = $timpug  = $timpur  = $timpua  = $treiva  = $tperci  = 0;
			//		if ( $row->tipo_doc == 'FC' ) {
			//			$finicial = $row->numero;
			//		} else {
			//			$finicial = '99999999';
			//		};
			//		$mm++;
			//		$nfiscali = '';
			//		$caja = $row->caja;
			//	};
			//};
			if ( $contri ) {

				if($row->tipo_doc == 'NC'){
					$acumulador['NTO']         +=abs($row->ventatotal);
					$acumulador['NC']['exento']+=abs($row->exento);
					$acumulador['NC']['base']  +=abs($row->baseg );
					$acumulador['NC']['base1'] +=abs($row->basea );
					$acumulador['NC']['base2'] +=abs($row->baser );
					$acumulador['NC']['iva']   +=abs($row->impug );
					$acumulador['NC']['iva1']  +=abs($row->impua );
					$acumulador['NC']['iva2']  +=abs($row->impur );
				}elseif($row->tipo_doc == 'FC'){
					$acumulador['VTO']         +=$row->ventatotal;
					$acumulador['FC']['exento']+=$row->exento;
					$acumulador['FC']['base']  +=$row->baseg ;
					$acumulador['FC']['base1'] +=$row->basea ;
					$acumulador['FC']['base2'] +=$row->baser ;
					$acumulador['FC']['iva']   +=$row->impug ;
					$acumulador['FC']['iva1']  +=$row->impua ;
					$acumulador['FC']['iva2']  +=$row->impur ;
				}
				
				// imprime contribuyente
				$fecha = $row->fecha;
				$ws->write_string( $mm,  0, $mm-8, $cuerpo );
				$fecha = substr($fecha,8,2)."-".$ameses[substr($fecha,5,2)-1]."-".substr($fecha,0,4);
				$ws->write_string( $mm, 1, $fecha,           $cuerpo );             // Fecha
				$ws->write_string( $mm, 2, $row->nombre,     $cuerpo );             // Nombre
				$ws->write_string( $mm, 3, $row->rif,        $cuerpo );             // RIF/CEDULA
				$ws->write_string( $mm, 4, $row->caja,       $cuerpo );             // Caja
				$ws->write_string( $mm, 5, $row->tipo_doc,   $cuerpo );             // Tipo_doc
				$ws->write_string( $mm, 6, $row->numero,     $cuerpo );             // Factura Inicial
				$ws->write_string( $mm, 7, '',               $cuerpo );             // Factura Final
				$ws->write_string( $mm, 8, substr($row->nfiscal,5,8),    $cuerpo ); // Fiscal Inicial
				$ws->write_string( $mm, 9, '',               $cuerpo );             // Fiscal Final
				$ws->write_string( $mm,10, $row->afecta,     $cuerpo );             // DOC. AFECTADO
				$ws->write_string( $mm,11, $row->tiva,       $cuerpo );             // CONTRIBUYENTE
				$ws->write_number( $mm,12, $row->ventatotal, $numero );             // VENTAS + IVA
				$ws->write_number( $mm,13, $row->exento,     $numero );             // VENTAS EXENTAS
				$ws->write_number( $mm,14, 0,                $numero );             // EXPORTACION FOB
				$ws->write_number( $mm,15, $row->baseg,      $numero );             // Base G
				$ws->write_number( $mm,16, $row->impug,      $numero );             // IVA G
				$ws->write_number( $mm,17, $row->basea,      $numero );             // Base A
				$ws->write_number( $mm,18, $row->impua,      $numero );             // IVA A
				$ws->write_number( $mm,19, $row->baser,      $numero );             // BASE R
				$ws->write_number( $mm,20, $row->impur,      $numero );             // IVA R
				$num = $mm+1;
				//$ws->write_formula( $mm,14,"=M$num*N$num/100" , $numero );        //I.V.A.
				$ws->write_number( $mm,21, $row->reiva,         $numero );          // IVA RETENIDO
				$ws->write_string( $mm,22, $row->comprobante,   $cuerpo );          // NRO COMPROBANTE
				$ws->write_string( $mm,23, $row->fechacomp,     $cuerpo );          // FECHA COMPROB
				$ws->write_number( $mm,24, $row->impercibido,   $numero );          // IMPUESTO PERCIBIDO
				$ws->write_string( $mm,25, $row->serial,        $cuerpo );          // SERIAL
				//$ws->write_string( $mm,19, $row->importacion,   $cuerpo );        // IMPORTACION
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
			$mfecha   = $row->fecha;
			$caja     = $row->caja;
			$nfiscali = $row->nfiscal;
			$serial   = $row->serial;
		}
			//Imprime el Ultimo
		
		//if ( $tventas <> 0 ) {
		//	if ( $finicial == '99999999' ) $finicial = $ffinal;
		//	$ws->write_string( $mm,  0, $mm-8, $cuerpo );
		//	$fecha = substr($mfecha,8,2)."-".$ameses[substr($mfecha,5,2)-1]."-".substr($mfecha,0,4);
		//	$ws->write_string( $mm, 1, $fecha,  $cuerpo );		// Fecha
		//	$ws->write_string( $mm, 2, 'VENTAS A NO CONTRIBUYENTES', $cuerpo );    // Nombre
		//	$ws->write_string( $mm, 3, '  ******  ', $cuerpo );    	// RIF/CEDULA
		//	$ws->write_string( $mm, 4, $caja,  $cuerpo );		  // Fecha
		//	$ws->write_string( $mm, 5, 'FC', $cuerpo );    		// TIPO
		//	
		//	$ws->write_string( $mm, 6, $finicial, $cuerpo );      // Factura Inicial
		//	$ws->write_string( $mm, 7, $ffinal,   $cuerpo );      // Factura Final
		//	$ws->write_string( $mm, 8, '',        $cuerpo );      // Nro. N.C.
		//	$ws->write_string( $mm, 9, '',        $cuerpo );    	// Nro. N.D.
		//	
		//	$ws->write_string( $mm,10, $row->afecta, $cuerpo );   // DOC. AFECTADO
		//	$ws->write_string( $mm,11, 'NO',      $cuerpo );      // CONTRIBUYENTE
		//	$ws->write_number( $mm,12, $tventas,  $numero );    	// VENTAS + IVA
		//	$ws->write_number( $mm,13, $texenta, $numero );    	  // EXENTAS
		//	$ws->write_number( $mm,14, 0,         $numero );    	// FOB
		//	
		//	$ws->write_number( $mm,15, $tbaseg, $numero );    // IVA RETENIDO
		//	$ws->write_number( $mm,16, $timpug, $numero );    // NRO COMPROBANTE
		//	$ws->write_number( $mm,17, $tbasea, $numero );    // ECHA COMPROB
		//	$ws->write_number( $mm,18, $timpua, $numero );   	// IMPUESTO PERCIBIDO
		//	$ws->write_number( $mm,19, $tbaser, $numero );    // IMPORTACION
		//	$ws->write_number( $mm,20, $timpur, $numero );    // IMPORTACION
		//	
		//	$ws->write_number( $mm,21, $treiva, $numero );   // IVA RETENIDO
		//	$ws->write_string( $mm,22, '',	$cuerpo );       // NRO COMPROBANTE
		//	$ws->write_string( $mm,23, '',	$cuerpo );       // FECHA COMPROB
		//	$ws->write_number( $mm,24, $tperci,	$numero );   // IMPUESTO PERCIBIDO
		//};
		
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
		// print "$header\n$data";
	}
	
	//libro de ventas con punto de ventas primera quincena
	function wlvexcelpdvfiscalq1($mes){
		wlvexcelpdvfiscal($mes,'Q1');
	}
	
	//libro de ventas con punto de ventas Segunda quincena
	function wlvexcelpdvfiscalq2($mes){
		wlvexcelpdvfiscal($mes,'Q2');
	}

	function prorrata($anomes) {
		//$anomes = $this->uri->segment(4).$this->uri->segment(5) ;
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
		if ( $export->num_rows() ) {
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

	//***********************************************
	// GENERACION
	//***********************************************
	function genecompras($mes){
		//$mes  = $this->uri->segment(4).$this->uri->segment(5);
		//Procesando Compras scst
		$this->db->simple_query("UPDATE scst SET montasa=0, tasa =0     WHERE montasa IS NULL ");
		$this->db->simple_query("UPDATE scst SET monredu=0, reducida=0  WHERE monredu IS NULL ");
		$this->db->simple_query("UPDATE scst SET monadic=0, sobretasa=0 WHERE monadic IS NULL ");
		
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='CP' ");
		
		// REVISAR COMPRAS
		$query = $this->db->query("SELECT control FROM scst WHERE abs(exento+montasa+monredu+monadic-montotot)>0.1 AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");
		
		// Procesando Compra 
		if ($query->num_rows() > 0) foreach ($query->result() as $row) $this->scstarretasa( $row->control );
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
				IF(a.montoiva=0,montotot,a.exento*((a.montotot-a.descu)/a.montotot))    exento,    
				a.montasa*((a.montotot-a.descu)/a.montotot)*(a.montoiva!=0)   general,   
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
		var_dum($this->db->simple_query($mSQL));
		$mSQL = "UPDATE siva SET gtotal=exento+general+geneimpu+adicional+reduimpu+reducida+adicimpu 
				WHERE fuente='CP' AND libro='C' ";
		var_dum($this->db->simple_query($mSQL));
	}

	function genegastos($mes){
		//$mes  = $this->uri->segment(4).$this->uri->segment(5);
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
		var_dum($this->db->simple_query($mSQL));

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
				'01' AS registro, 
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
				WHERE EXTRACT(YEAR_MONTH FROM b.fecha)=$mes 
				AND b.tipo_doc='FC' AND b.cajachi='S' 
				ORDER BY a.fecha ";
		var_dum($this->db->simple_query($mSQL));
		$mSQL = "UPDATE siva SET gtotal=exento+general+geneimpu+adicional+reduimpu+reducida+adicimpu 
				WHERE fuente='GS' AND libro='C' ";
		var_dum($this->db->simple_query($mSQL));
	}

	function genecxp($mes){
		//$mes  = $this->uri->segment(4).$this->uri->segment(5);
		//Procesando Compras scst
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='MP' ");
		$this->db->simple_query("UPDATE sprv SET nomfis=nombre WHERE nomfis='' OR nomfis IS NULL ");
		
		$mSQL = "SELECT a.*,b.rif, b.nomfis FROM sprm AS a LEFT JOIN sprv AS b ON a.cod_prv=b.proveed 
				WHERE EXTRACT(YEAR_MONTH FROM fecha)=$mes AND b.tipo<>'5' 
				AND a.tipo_doc='NC' AND a.codigo NOT IN ('NOCON','') ";
		$query = $this->db->query($mSQL);
		
		if ( $query->num_rows() > 0 ){
			foreach( $query->result() as $row ) {
				if ($row->impuesto == 0 and empty($row->codigo) ) continue;
				$referen = $this->datasis->dameval("SELECT numero FROM itppro WHERE transac=".$row->transac." LIMIT 1") ;
				$fafecta = $this->datasis->dameval("SELECT fecha  FROM itppro WHERE transac=".$row->transac." LIMIT 1") ;
				$stotal = $row->monto-$row->impuesto;
				$mSQL = "INSERT INTO siva SET 
							libro='C', 
							tipo='".$row->tipo_doc."', 
							fuente='MP', 
							sucursal='00', 
							fecha='";
							if($row->fecapl==null) $mSQL.=$row->fecha; else $mSQL.=$row->fecapl;
							$mSQL .= "', 
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
				var_dum($this->db->simple_query($mSQL));
			}
		}
		// Procesando Compras scst
		$mSQL = "UPDATE siva SET gtotal=exento+general+geneimpu+adicional+reduimpu+reducida+adicimpu 
				WHERE fuente='MP' AND libro='C' ";
		var_dum($this->db->simple_query($mSQL));
	}

	function genesfac($mes){
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
		var_dum($this->db->simple_query($mSQL));

		$mSQL = "INSERT INTO siva  
				(id, libro, tipo, fuente, sucursal, fecha, numero, numhasta,  caja, nfiscal,  nhfiscal, 
				referen, planilla, clipro, nombre, contribu, rif, registro,
				nacional, exento, general, geneimpu, 
				adicional, adicimpu,  reducida,  reduimpu, stotal, impuesto, 
				gtotal, reiva, fechal, fafecta) 
				SELECT 0 AS id,
				'V' AS libro, 
				IF(a.tipo_doc='D','NC',CONCAT(a.tipo_doc,'C')) AS tipo, 
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
		var_dum($this->db->simple_query($mSQL));

		// CARGA LAS RETENCIONES DE IVA DE CONTADO
		$mSQL = "SELECT * FROM sfpa WHERE tipo='RI' AND	EXTRACT(YEAR_MONTH FROM f_factura)=$mes AND tipo_doc='FE' ";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0) {
			foreach ( $query->result() AS $row ) {
				$mSQL = "UPDATE siva SET reiva=".$row->monto.", comprobante='20".$row->num_ref."' WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
				var_dum($this->db->simple_query($mSQL)); 
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
			foreach ( $query->result() AS $row ) {
				$mSQL = "UPDATE siva SET reiva=".$row->reteiva.", comprobante='".$row->nroriva."', fecharece='$row->recriva'  WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
				var_dum($this->db->simple_query($mSQL)); 
			}
		}		
	}
	
	function genesfmay($mes){
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
		var_dum($this->db->simple_query($mSQL));
		$tasas=$this->_tasas($mes);
		$mTASA=$tasas['general'];
		
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
			b.nombre AS nombre, 
			'CO' AS contribu, 
			c.rifci, 
			'01' AS registro, 
			'S' AS nacional, 
			sum(IF(a.iva=0,1,0)*a.importe) AS exento, 
			sum(IF(a.iva=$mTASA,1,0)*a.importe) AS general, 
			sum(IF(a.iva=$mTASA,1,0)*a.importe*a.iva/100) AS geneimpu, 
			sum(IF(a.iva>$mTASA,1,0)*a.importe)  AS adicional, 
			sum(IF(a.iva>$mTASA,1,0)*a.importe*a.iva/100) AS adicimpu, 
			sum(IF(a.iva<$mTASA AND a.iva>0,1,0)*a.importe) AS reducida,
			sum(IF(a.iva<$mTASA AND a.iva>0,1,0)*a.importe*a.iva/100) AS reduimpu,
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
		var_dum($this->db->simple_query($mSQL));
		
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
			b.nombre AS nombre, 
			'CO' AS contribu, 
			'ANULADA' AS rifci, 
			'01' AS registro, 
			'S' AS nacional, 
			0 AS exento, 
			0 AS general, 
			0 AS geneimpu, 
			0 AS adicional, 
			0 AS adicimpu, 
			0 AS reducida,
			0 AS reduimpu,
			0 AS stotal,
			0 AS impuesto, 
			0 AS gtotal, 
			0 AS reiva, 
			".$mes."01 AS fechal, 
 			0 AS fafecta 
			FROM itfmay AS a JOIN fmay AS b ON a.numero=b.numero AND a.fecha=b.fecha 
			LEFT JOIN scli AS c ON b.cod_cli=c.cliente 
			WHERE EXTRACT(YEAR_MONTH FROM b.fecha)=$mes AND b.tipo='A'
			GROUP BY a.fecha,a.numero ";
		//var_dum($this->db->simple_query($mSQL));
	}

	function genesmov($mes){
		//$mes  = $this->uri->segment(4).$this->uri->segment(5);
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
			var_dum($this->db->simple_query($mSQL));
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
		foreach ( $query->result() as $row ){
			$mSQL = "UPDATE siva SET reiva=$row->reteiva, comprobante=$row->nroriva WHERE tipo='FC' AND numero='$row->numero' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
			var_dum($this->db->simple_query($mSQL));
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
				UNION 
				SELECT b.fecha, a.numero, 'OJO LLENE DATOS', 'OJO', '',
					'' AS afecta, 0 AS fafecta, b.monto-b.abonos, a.transac, a.numero, a.fecha, a.fecha 
				FROM smov AS b JOIN prmo AS a ON a.transac=b.transac 
				WHERE b.fecha<".$mes."01 AND b.cod_cli='REIVA' 
				AND b.monto>b.abonos";

		$query = $this->db->query($mSQL);
		
		foreach ( $query->result() as $row ){
			$mSQL = "SELECT monto-abonos FROM smov WHERE cod_cli='REIVA' AND transac='$row->transac'";
			//if ( $this->datasis->dameval($mSQL) <= 0 ) continue;
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
			
			var_dum($this->db->simple_query($mSQL));
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
			var_dum($this->db->simple_query($mSQL));
		}
	}

	function geneotin($mes){
		//$mes  = $this->uri->segment(4).$this->uri->segment(5);
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
		var_dum($this->db->simple_query($mSQL));
		//echo $mSQL;
		//exit;
	}

	function generest($mes){
		// BORRA LA GENERADA ANTERIOR
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='FR' ");
		// ARREGLA LAS TASAS NULAS EN SFAC
		$this->db->simple_query("UPDATE rfac SET tasa=0, montasa=0, reducida=0, monredu=0, sobretasa=0, monadic=0, exento=0 WHERE (tasa IS NULL OR montasa IS NULL) AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");
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
			FROM rfac AS a 
			LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
			WHERE EXTRACT(YEAR_MONTH FROM a.fecha)=$mes AND a.tipo NOT IN ('P','T')";
		var_dum($this->db->simple_query($mSQL));
		//echo $mSQL;
		
		// CARGA LAS RETENCIONES DE IVA DE CONTADO
		$mSQL = "SELECT * FROM sfpa WHERE tipo='RI' AND	EXTRACT(YEAR_MONTH FROM f_factura)=$mes AND tipo_doc='FE' ";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0) {
			foreach ( $query->result() AS $row ){
				$mSQL = "UPDATE siva SET reiva=".$row->monto.", comprobante='20".$row->num_ref."' WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
				var_dum($this->db->simple_query($mSQL)); 
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
			foreach ( $query->result() AS $row ){
				$mSQL = "UPDATE siva SET reiva=".$row->reteiva.", comprobante='".$row->nroriva."', fecharece='$row->recriva'  WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
				var_dum($this->db->simple_query($mSQL)); 
			}
		}
	}

	function genehotel($mes){
		// BORRA LA GENERADA ANTERIOR
		$this->db->simple_query("DELETE FROM siva WHERE EXTRACT(YEAR_MONTH FROM fechal) = $mes AND fuente='FH' ");
		// ARREGLA LAS TASAS NULAS EN SFAC
		$this->db->simple_query("UPDATE hfac SET tasa=0, montasa=0, reducida=0, monredu=0, sobretasa=0, monadic=0, exento=0 WHERE (tasa IS NULL OR montasa IS NULL) AND EXTRACT(YEAR_MONTH FROM fecha)=$mes ");

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
		var_dum($this->db->simple_query($mSQL));
		//echo $mSQL;
		
		//CARGA LAS RETENCIONES DE IVA DE CONTADO
		$mSQL = "SELECT * FROM sfpa WHERE tipo='RI' AND	EXTRACT(YEAR_MONTH FROM f_factura)=$mes AND tipo_doc='FE' ";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0) {
			foreach ( $query->result() AS $row ) {
				$mSQL = "UPDATE siva SET reiva=".$row->monto.", comprobante='20".$row->num_ref."' WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
				var_dum($this->db->simple_query($mSQL)); 
			}
		}
		
		//CARGA LAS RETENCIONES DE IVA DESDE SMOV
		$mSQL = "SELECT a.tipo_doc, a.fecha, a.numero, c.nombre, c.rifci, a.cod_cli, b.monto,
				a.numero AS afecta, a.fecha AS fafecta, a.reteiva, a.transac, a.nroriva, a.emiriva, a.recriva 
			FROM itccli AS a JOIN smov AS b ON a.transac=b.transac 
				LEFT JOIN scli AS c ON a.cod_cli=c.cliente 
			WHERE EXTRACT(YEAR_MONTH FROM b.fecha) = ".$mes." AND b.cod_cli='REIVA' 
				AND a.reteiva>0 AND b.monto>b.abonos ";
		
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0) {
			foreach ( $query->result() AS $row ){
				$mSQL = "UPDATE siva SET reiva=".$row->reteiva.", comprobante='".$row->nroriva."', fecharece='$row->recriva'  WHERE tipo='".$row->tipo_doc."' AND numero='".$row->numero."' AND libro='V' AND EXTRACT(YEAR_MONTH FROM fechal)=$mes ";
				var_dum($this->db->simple_query($mSQL)); 
			}
		}
	}

	//***********************************************
	// METODOS PRIVADOS
	//***********************************************
	// Arregla montasa en scst
	function scstarretasa($mCONTROL){
		$m         = 1;
		$mTASA     = $mREDUCIDA = $mSOBRETASA=$mMONTASA = $mMONREDU = $mMONADIC = $mATASAS= $mIVA= $mEXENTO = 0;

		$query = $this->db->query("SELECT * FROM itscst WHERE control='$mCONTROL' ");
		foreach ( $query->result() as $row ){
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
		var_dum($this->db->simple_query($mSQL));
	} 

	function _tasas($mes) {
		$msql  = "SELECT tasa, redutasa, sobretasa FROM civa WHERE fecha<=".$mes."01 ORDER BY fecha DESC limit 1";
		$mivas = $this->db->query($msql);
		$mt    = $mivas->row();
		$mtasa['general']   = $mt->tasa;
		$mtasa['reducida']  = $mt->redutasa;
		$mtasa['adicional'] = $mt->sobretasa;
		return $mtasa;
	}

	function _arreglatasa($mTRANSAC){
		$mTASA =$mREDUCIDA=$mSOBRETASA = $mMONTASA =$mMONREDU = $mMONADIC = $mEXENTO= $mIVA= 0; 
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
		var_dum($this->db->simple_query($mSQL));

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

	// Calcula el Inventario
	function _invresum($mes){
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
		var_dum($this->db->simple_query($mSQL));
		
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

	function configurar(){
		$this->rapyd->load("datafilter","datagrid");
		$uri = anchor('finanzas/libros/cedit/show/<#metodo#>','<#nombre#>');
		$link=site_url('/finanzas/libros');
		$grid = new DataGrid("Seleccione las opciones que desea activar para el modulo");
		$grid->use_function('form_checkbox');
		$grid->db->select(array('nombre','IF(tipo="D","Descarga","Generar") AS tipo','activo="S" AS activo','metodo'));
		$grid->db->from('libros');
		$grid->order_by("tipo","asc");
		$grid->column("Activo", "<form_checkbox><#metodo#>|<#metodo#>|<#activo#></form_checkbox>",'align="center"');
		$grid->column("Nombre",$uri);
		$grid->column("Tipo","tipo");
		$grid->add("finanzas/libros/cedit/create");
		$grid->button('back',RAPYD_BUTTON_BACK, "javascript:window.location='$link'", "BL");
		$grid->build();
		//echo $grid->db->last_query();
		
		$link=site_url('/finanzas/libros/activar');
		$data['script']='<script type="text/javascript">
		$(document).ready(function() {
			$(":checkbox").click(function () { 
      	activar($(this).attr("value"));
    	});
		});

		function activar(metodo){
			var url = "'.$link.'"+"/"+metodo;
			$.ajax({
				url: url,
				success: function(msg){
					if(msg=0)
						alert("Error");
				}
			});
		}
		</script>';
		$data['content'] = '<form>'.$grid->output.'</form>';
		$data['title']   = "<h1>Configuracion de libros</h1>";
		$data["head"]    = script("jquery-1.2.6.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function activar($metodo){
		$mSQL = "UPDATE libros SET activo=IF(activo='S','N','S') WHERE metodo = '$metodo'";
		echo var_dum($this->db->simple_query($mSQL));
	}

	function cedit(){ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Edici&oacute;n de caja", "libros");
		$edit->back_url = site_url("finanzas/libros/configurar");
		$edit->metodo = new inputField("Metodo", "metodo");
		$edit->metodo->rule = "required";
		$edit->metodo->mode = "autohide";
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("G","Generar" );
		$edit->tipo->option("D","Descarga");
		$edit->activo = new dropdownField("Activo", "activo");
		$edit->activo->option("S","Si");
		$edit->activo->option("N","No");
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Configuracion de libros</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `libros` (
		  `metodo` varchar(50) NOT NULL default '',
		  `nombre` varchar(150) default NULL,
		  `activo` char(1) default NULL,
		  `tipo` char(1) default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `fgenera` char(6) default NULL,
		  PRIMARY KEY  (`metodo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";   
		var_dum($this->db->simple_query($mSQL));
		
		$data[]=array('metodo'=>'wlvexcelpdv'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV'      );
		$data[]=array('metodo'=>'wlvexcelpdvq1'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1');
		$data[]=array('metodo'=>'wlvexcelpdvq2'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal'  ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq1','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1 Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq2','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2 Fiscal');
		$data[]=array('metodo'=>'wlvexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas'          );
		$data[]=array('metodo'=>'wlvexcelsucu'       ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas por Sucursal');
		$data[]=array('metodo'=>'wlcexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras'         );
		$data[]=array('metodo'=>'wlcsexcel'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras Supermercado');
		$data[]=array('metodo'=>'wlvexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas  ESPECIAL');
		$data[]=array('metodo'=>'wlcexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras ESPECIAL');
		$data[]=array('metodo'=>'prorrata'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Prorrata'                 );
		$data[]=array('metodo'=>'invresu'            ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Inventario'      );
		
		$data[]=array('metodo'=>'genecompras' ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras COMPRAS' );
		$data[]=array('metodo'=>'genegastos'  ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras GASTOS'  );
		$data[]=array('metodo'=>'genecxp'     ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras CXP'     );
		$data[]=array('metodo'=>'genesfac'    ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas' );
		$data[]=array('metodo'=>'genesfmay'   ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas al mayor' );
		$data[]=array('metodo'=>'genesmov'    ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas CXC'      );
		$data[]=array('metodo'=>'geneotin'    ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas O.Ingresos');
		$data[]=array('metodo'=>'generest'    ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Restaurante');
		$data[]=array('metodo'=>'genehotel'   ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Hotel');
		
		foreach($data AS $algo){
			$mSQL = $this->db->insert_string('libros', $algo);
			var_dum($this->db->simple_query($mSQL));
		}
		var_dum($this->db->simple_query($mSQL));
		echo $uri = anchor('finanzas/libros/configurar','Configurar');
	} 
}
?>