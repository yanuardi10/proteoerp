<?php
class Lresumen extends Controller {

	var $cargo=0;
	
	function Lresumen() {
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index() {

		$this->rapyd->load("dataform");
		
		$filter = new DataForm('supermercado/lresumen/resumen');
		$filter->fecha = new dateonlyField("Fecha", "fecha");
		$filter->fecha->insertValue = date("Y-m-d");
		$filter->fecha->size=10;
		$filter->submit("btnsubmit","Aceptar");
		$filter->build_form();
	
		$data["filtro"] = $filter->output;
		$data["titulo"] = '<h2 class="mainheader">Resumen de caja<h2>';
		$data["head"]   = $this->rapyd->get_head();
		$this->load->view('view_freportes', $data);
	}
	

	function indext() {

		$this->rapyd->load("dataform");
		
		$filter = new DataForm('supermercado/lresumen/resument');
		$filter->fecha = new dateonlyField("Fecha", "fecha");
		$filter->fecha->insertValue = date("Y-m-d");
		$filter->fecha->size=10;
		$filter->submit("btnsubmit","Aceptar");
		$filter->build_form();
	
		$data["filtro"] = $filter->output;
		$data["titulo"] = '<h2 class="mainheader">Resumen de Todas las cajas<h2>';
		$data["head"]   = $this->rapyd->get_head();
		$this->load->view('view_freportes', $data);
	}

	function resumen() {
		$fecha  = $this->input->post('fecha');
		if(!$fecha) redirec('contabilidad/lresumen');
		$nrosucu = $this->datasis->traevalor('NROSUCU');
		
		if(empty($nrosucu)) $nrosucu='0';
		
		$titulo = $this->datasis->traevalor('TITULO1');
		$rif    = $this->datasis->traevalor('RIF');
		$qfecha = date("Ymd",timestampFromInputDate($fecha));
		
		$this->load->library('fpdf');
		$esta = & $this->fpdf;
		$esta->AddPage();
		$esta->SetFillColor(52,186,114);
		$esta->image($_SERVER['DOCUMENT_ROOT'].base_url().'images/logotipo.jpg',10,8,40);
		$esta->ln(5);
		$esta->SetFont('Arial','B',16);
		$esta->cell(0,10,"RESUMEN DE CAJA AL DIA $fecha", 0, 2, 'C');
		
		$esta->ln(1);
		$esta->SetFont('Arial','',10);
		$esta->cell(0,5,"    RIF: ".$rif, 0, 2, 'L');
		$esta->ln(1);
		
		$esta->SetFont('Arial','I',14);
		$esta->cell(0,7,"CIERRE DE CAJEROS", 1, 2, 'L');
		$esta->ln(1);
		
		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(14,5,"Caja", 0, 0, 'C',1);
		$esta->cell(32,5,"Cajero", 0, 0, 'C',1);
		$esta->cell(40,5,"Arqueo", 0, 0, 'C',1);
		$esta->cell(40,5,"Sistema", 0, 0, 'C',1);
		$esta->cell(32,5,"Faltantes", 0, 0, 'C',1);
		$esta->cell(32,5,"Sobrantes", 0, 0, 'C',1);
		$esta->ln(5);
		$esta->SetTextColor(0);
		$prefijo = $this->datasis->dameval("SELECT prefijo FROM sucu WHERE codigo = LPAD($nrosucu,2,'0')");

		$nrosucu = $this->datasis->dameval("SELECT prefijo FROM sucu WHERE codigo = LPAD($nrosucu,2,'0')");		

		$mSQL = "SELECT a.caja, b.nombre, a.trecibe, a.computa, 
		               if(a.trecibe-a.computa<0,a.trecibe-a.computa,0) faltante,
		               if(a.trecibe-a.computa>0,a.trecibe-a.computa,0) sobrante
		        FROM dine AS a LEFT JOIN scaj AS b ON a.cajero=b.cajero
		        WHERE a.fecha=$qfecha AND MID(a.caja,1,1)='$nrosucu'  ORDER BY a.caja ";
		
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$esta->SetFont('Arial','',9);
				$esta->cell(14,5,$row->caja,0,0, 'L');
				$esta->cell(32,5,substr($row->nombre,1,25), 0, 0, 'L');
				$esta->cell(40,5,number_format($row->trecibe,2), 0, 0, 'R');
				$esta->cell(40,5,number_format($row->computa,2), 0, 0, 'R');
				$esta->cell(32,5,number_format($row->faltante,2), 0, 0, 'R');
				$esta->cell(32,5,number_format($row->sobrante,2), 0, 0, 'R');
				$esta->ln(5);
			}
		}$query->free_result();
		
		$mSQL = "SELECT caja, cajero, sum(trecibe) tr, sum(computa) tc, sum(if(trecibe-computa<0,trecibe-computa,0))tf , sum(if(trecibe-computa>0,trecibe-computa,0)) ts 
			FROM dine 
			WHERE fecha=$qfecha  AND MID(caja,1,1)='$nrosucu' 
			GROUP BY fecha ";
		$query = $this->db->query($mSQL);
		$esta->SetTextColor(255,255,255);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$esta->SetFont('Arial','B',9);
				$esta->cell(46,5,"Totales..",0,0, 'R',1);
				$esta->cell(40,5,number_format($row->tr,2), 0, 0, 'R',1);
				$esta->cell(40,5,number_format($row->tc,2), 0, 0, 'R',1);
				$esta->cell(32,5,number_format($row->tf,2), 0, 0, 'R',1);
				$esta->cell(32,5,number_format($row->ts,2), 0, 0, 'R',1);
				$esta->ln(4);
			}
		}
		$query->free_result();
		$esta->SetTextColor(0);
		
		$esta->ln(2);
		$esta->SetFont('Arial','I',14);
		$esta->cell(0,7,"RECAUDACION POR TIPO DE PAGO", 1, 2, 'L');
		$esta->ln(1);
		
		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(100,5,"FORMA DE PAGO", 0, 0, 'C',1);
		$esta->cell(50,5,"MONTO", 0, 0, 'C',1);
		$esta->cell(20,5,"#TRANS", 0, 0, 'C',1);
		$esta->cell(20,5,"%", 0, 0, 'C',1);
		$esta->SetTextColor(0);
		$esta->ln(5);
		
		$venta = $this->datasis->dameval("SELECT sum(a.total) FROM itdine AS a, dine AS b WHERE b.fecha=$qfecha AND a.numero=b.numero AND MID(caja,1,1)='$nrosucu'");
		
		$mSQL = "SELECT a.tipo, b.nombre, sum(a.total) tot,count(*) tra
        	FROM itdine AS a 
        	LEFT JOIN tarjeta AS b ON a.tipo=b.tipo
        	JOIN dine AS c ON a.numero=c.numero
        	WHERE c.fecha=$qfecha AND MID(c.caja,1,1)='$nrosucu'
        	GROUP BY a.tipo ";

		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$esta->SetFont('Arial','',9);
				$esta->cell(20,5,$row->tipo,0,0, 'C');
				$esta->cell(80,5,$row->nombre, 0, 0, 'L');
				$esta->cell(50,5,number_format($row->tot,2), 0, 0, 'R');
				$esta->cell(20,5,number_format($row->tra,0), 0, 0, 'R');
				$esta->cell(20,5,number_format($row->tot*100/$venta,2), 0, 0, 'R');
				$esta->ln(4);
			}
		}
		$query->free_result();

		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(100,5,"Total Bs...", 0, 0, 'R',1);
		$esta->cell(50,5,number_format($venta,2), 0, 0, 'R',1);
		$esta->cell(40,5,'', 0, 0, 'R',1);
		$esta->SetTextColor(0);
		$esta->ln(5);

		$esta->ln(2);
		$esta->SetFont('Arial','I',14);
		$esta->cell(0,7,"CUADRE DE CAJA POR DIA", 1, 2, 'L');
		$esta->ln(1);
		$qqfecha="ADDDATE($qfecha, INTERVAL 1 DAY)";
		
		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(140,5,"NOMBRE", 0, 0, 'C',1);
		$esta->cell(50,5,"MONTO", 0, 0, 'C',1);
		$esta->SetTextColor(0);
		$esta->ln(5);

//echo "SELECT sum(efectivo) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ";
		$esta->SetFont('Arial','',10);
		$cajapos=$this->datasis->dameval("SELECT valor FROM valores WHERE nombre='CAJAPOS'");
		$defe=$this->datasis->dameval("SELECT sum(efectivo) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
		$esta->cell(140,5,"DEPOSITOS EN EFECTIVO EN TRANSITO",0,0,"L");
		$esta->cell(50,5,number_format($defe,2),0,0,"R");
		$esta->ln(5);
		
		$dech=$this->datasis->dameval("SELECT sum(cheques) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
		$esta->cell(140,5,"DEPOSITOS EN CHEQUE EN TRANSITO",0,0,"L");
		$esta->cell(50,5,number_format($dech,2),0,0,"R");
		$esta->ln(5);
		
		$detc=$this->datasis->dameval("SELECT sum(tarjeta) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
		$esta->cell(140,5,"DEPOSITOS EN TARJETA DE CREDITO",0,0,"L");
		$esta->cell(50,5,number_format($detc,2),0,0,"R");
		$esta->ln(5);
		
		$detd=$this->datasis->dameval("SELECT sum(tdebito) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
		$esta->cell(140,5,"DEPOSITOS EN TARJETA DE DEBITO",0,0,"L");
		$esta->cell(50,5,number_format($detd,2),0,0,"R");
		$esta->ln(5);
		
		$pago=$this->datasis->dameval("SELECT sum(monto) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' AND tipo='TR' ");
		$esta->cell(140,5,"TRASFERIDO A ADMINISTRACION",0,0,"L");
		$esta->cell( 50,5,number_format($pago,2),0,0,"R");
		$esta->ln(5);
		
		$cesta=$this->datasis->dameval("SELECT sum(monto) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' AND tipo='RM' ");
		$esta->cell(140,5,"CESTA TICKET Y VALORES AL COBRO",0,0,"L");
		$esta->cell( 50,5,number_format($cesta,2),0,0,"R");
		$esta->ln(5);
		
		$esta->SetTextColor(255,255,255);
		$esta->cell(140,5, "RESUMEN DISTRIBUCION POS",0,0,"L",1);
		$esta->cell(50,5,number_format($defe+$dech+$detc+$detd+$pago+$cesta,2),0,0,"R",1);
		$esta->ln(8);
		$esta->SetTextColor(0);
		$pago=$this->datasis->dameval("SELECT saldo FROM banc WHERE codbanc='$cajapos' ");

		$esta->SetFont('Arial','B',18);
		$esta->SetFillColor(0,102,204);
		$esta->SetTextColor(255,255,255);
		
		$esta->cell(190,10,"SALDO CAJA POS: ".number_format($pago,2),0,0,"C",1);
		$esta->ln(12);
		
		$esta->SetFillColor(52,186,114);
		$esta->SetTextColor(0);
		
		$esta->ln(2);
		$esta->SetFont('Arial','I',14);
		$esta->cell(0,7,"VALORES EN CUSTODIA", 1, 2, 'L');
		$esta->ln(1);

		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(50,5,"Descripcion", 0, 0, 'C',1);
		$esta->cell(35,5,"Anterior", 0, 0, 'C',1);
		$esta->cell(35,5,"Ingresos", 0, 0, 'C',1);
		$esta->cell(35,5,"Egresos", 0, 0, 'C',1);
		$esta->cell(35,5,"Saldo", 0, 0, 'C',1);
		$esta->SetTextColor(0);
		$esta->ln(5);
		
		$mSQL = "SELECT tarjeta, concepto, enlace, saldo, descrip FROM tardet WHERE enlace IS NOT NULL AND enlace!='' GROUP BY enlace";
		$manterior = $mingresos = $megresos  = $msaldos   = 0;
		
		$menlaces = $this->db->query($mSQL);
		$esta->SetFont('Arial','',10);
		
		if ($menlaces->num_rows() > 0){
			foreach ($menlaces->result() as $row){
				$mSQL = "SELECT sum(a.total) FROM itdine AS a, dine AS b WHERE a.tipo='".$row->tarjeta."' AND a.concepto='".$row->concepto."' AND a.numero=b.numero AND b.fecha=$qfecha GROUP BY fecha ";
				$ingreso = $this->datasis->dameval($mSQL);
				$mSQL = "SELECT sum(a.monto) FROM itbcaj AS a, bcaj AS b WHERE a.tipo='".$row->tarjeta."' AND a.concep='".$row->concepto."' AND a.numero=b.numero AND b.estampa=$qqfecha GROUP BY fecha ";
				$egreso = $this->datasis->dameval($mSQL);
				$esta->cell(50,5,$row->descrip, 0, 0, 'L');
				$esta->cell(35,5,number_format($row->saldo-$ingreso+$egreso,2), 0, 0, 'R');
				$manterior += $row->saldo-$ingreso+$egreso ;
				$esta->cell(35,5,number_format($ingreso,2), 0, 0, 'R');
				$mingresos += $ingreso ;
				$esta->cell(35,5,number_format($egreso,2), 0, 0, 'R');
				$megresos += $egreso ;
				$esta->cell(35,5,number_format($row->saldo,2), 0, 0, 'R');
				$msaldos += $row->saldo;
				$esta->ln(5);
			}
		}
		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(50,5, "Totales...",0,0,'R',1);
		$esta->cell(35,5,number_format($manterior,2),0,0,'R',1);
		$esta->cell(35,5,number_format($mingresos,2),0,0,'R',1);
		$esta->cell(35,5,number_format($megresos,2), 0,0,'R',1);
		$esta->cell(35,5,number_format($msaldos,2),  0,0,'R',1);
		$esta->SetTextColor(0);
		$esta->Output();
	}
	



	function resument() {
		$fecha  = $this->input->post('fecha');
		if(!$fecha) redirec('contabilidad/lresument');
		
		$titulo = $this->datasis->traevalor('TITULO1');
		$rif    = $this->datasis->traevalor('RIF');
		$qfecha = date("Ymd",timestampFromInputDate($fecha));
		
		$this->load->library('fpdf');
		$esta = & $this->fpdf;
		$esta->AddPage();
		$esta->SetFillColor(52,186,114);
		$esta->image($_SERVER['DOCUMENT_ROOT'].base_url().'images/logotipo.jpg',10,8,40);
		$esta->ln(5);
		$esta->SetFont('Arial','B',16);
		$esta->cell(0,10,"TOTAL SUCURSALES AL DIA $fecha", 0, 2, 'C');
		
		$esta->ln(1);
		$esta->SetFont('Arial','',10);
		$esta->cell(0,5,"    RIF: ".$rif, 0, 2, 'L');
		$esta->ln(1);
		
		$esta->SetFont('Arial','I',14);
		$esta->cell(0,7,"CIERRE DE SUCURSAL", 1, 2, 'L');
		$esta->ln(1);
		
		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(14,5,"Caja", 0, 0, 'C',1);
		$esta->cell(32,5,"Cajero", 0, 0, 'C',1);
		$esta->cell(40,5,"Arqueo", 0, 0, 'C',1);
		$esta->cell(40,5,"Sistema", 0, 0, 'C',1);
		$esta->cell(32,5,"Faltantes", 0, 0, 'C',1);
		$esta->cell(32,5,"Sobrantes", 0, 0, 'C',1);
		$esta->ln(5);
		$esta->SetTextColor(0);
		
		$mSQL = "SELECT MID(a.caja,1,1) caja, CONCAT('.Sucursal ',MID(a.caja,1,1)) nombre, SUM(a.trecibe) trecibe, SUM(a.computa) computa,
		               SUM(if(a.trecibe-a.computa<0,a.trecibe-a.computa,0)) faltante,
		               SUM(if(a.trecibe-a.computa>0,a.trecibe-a.computa,0)) sobrante 
		        FROM dine AS a LEFT JOIN scaj AS b ON a.cajero=b.cajero 
		        WHERE a.fecha=$qfecha GROUP BY MID(a.caja,1,1) ";
		
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$esta->SetFont('Arial','',9);
				$esta->cell(14,5,$row->caja,0,0, 'L');
				$esta->cell(32,5,substr($row->nombre,1,25), 0, 0, 'L');
				$esta->cell(40,5,number_format($row->trecibe,2), 0, 0, 'R');
				$esta->cell(40,5,number_format($row->computa,2), 0, 0, 'R');
				$esta->cell(32,5,number_format($row->faltante,2), 0, 0, 'R');
				$esta->cell(32,5,number_format($row->sobrante,2), 0, 0, 'R');
				$esta->ln(5);
			}
		}$query->free_result();
		
		$mSQL = "SELECT caja, cajero, sum(trecibe) tr, sum(computa) tc, sum(if(trecibe-computa<0,trecibe-computa,0))tf , sum(if(trecibe-computa>0,trecibe-computa,0)) ts FROM dine WHERE fecha=$qfecha GROUP BY fecha ";
		$query = $this->db->query($mSQL);
		$esta->SetTextColor(255,255,255);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$esta->SetFont('Arial','B',9);
				$esta->cell(46,5,"Totales..",0,0, 'R',1);
				$esta->cell(40,5,number_format($row->tr,2), 0, 0, 'R',1);
				$esta->cell(40,5,number_format($row->tc,2), 0, 0, 'R',1);
				$esta->cell(32,5,number_format($row->tf,2), 0, 0, 'R',1);
				$esta->cell(32,5,number_format($row->ts,2), 0, 0, 'R',1);
				$esta->ln(4);
			}
		}
		$query->free_result();
		$esta->SetTextColor(0);
		
		$esta->ln(2);
		$esta->SetFont('Arial','I',14);
		$esta->cell(0,7,"RECAUDACION POR TIPO DE PAGO", 1, 2, 'L');
		$esta->ln(1);
		
		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(100,5,"FORMA DE PAGO", 0, 0, 'C',1);
		$esta->cell(50,5,"MONTO", 0, 0, 'C',1);
		$esta->cell(20,5,"#TRANS", 0, 0, 'C',1);
		$esta->cell(20,5,"%", 0, 0, 'C',1);
		$esta->SetTextColor(0);
		$esta->ln(5);
		
		$venta = $this->datasis->dameval("SELECT sum(a.total) FROM itdine AS a, dine AS b WHERE b.fecha=$qfecha AND a.numero=b.numero ");
		
		$mSQL = "SELECT a.tipo, b.nombre, sum(a.total) tot,count(*) tra
        	FROM itdine AS a 
        	LEFT JOIN tarjeta AS b ON a.tipo=b.tipo
        	JOIN dine AS c ON a.numero=c.numero
        	WHERE c.fecha=$qfecha 
        	GROUP BY a.tipo ";

		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$esta->SetFont('Arial','',9);
				$esta->cell(20,5,$row->tipo,0,0, 'C');
				$esta->cell(80,5,$row->nombre, 0, 0, 'L');
				$esta->cell(50,5,number_format($row->tot,2), 0, 0, 'R');
				$esta->cell(20,5,number_format($row->tra,0), 0, 0, 'R');
				$esta->cell(20,5,number_format($row->tot*100/$venta,2), 0, 0, 'R');
				$esta->ln(4);
			}
		}
		$query->free_result();

		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(100,5,"Total Bs...", 0, 0, 'R',1);
		$esta->cell(50,5,number_format($venta,2), 0, 0, 'R',1);
		$esta->cell(40,5,'', 0, 0, 'R',1);
		$esta->SetTextColor(0);
		$esta->ln(15);


		$esta->SetFont('Arial','B',18);
		$esta->SetFillColor(0,102,204);
		$esta->SetTextColor(255,255,255);
		$esta->cell(190,10,"RESUMEN DEL MES HASTA LA FECHA",0,0,"C",1);

		$esta->ln(10);
		$esta->SetTextColor(0);
		$esta->SetFont('Arial','I',14);
		$esta->cell(0,7,"TOTALES DEL MES", 1, 2, 'L');
		$esta->ln(1);

		
		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(14,5,"Caja", 0, 0, 'C',1);
		$esta->cell(32,5,"Cajero", 0, 0, 'C',1);
		$esta->cell(40,5,"Arqueo", 0, 0, 'C',1);
		$esta->cell(40,5,"Sistema", 0, 0, 'C',1);
		$esta->cell(32,5,"Faltantes", 0, 0, 'C',1);
		$esta->cell(32,5,"Sobrantes", 0, 0, 'C',1);
		$esta->ln(5);
		$esta->SetTextColor(0);

		$mSQL = "SELECT MID(a.caja,1,1) caja, CONCAT('.Sucursal ',MID(a.caja,1,1)) nombre, SUM(a.trecibe) trecibe, SUM(a.computa) computa,
		               SUM(if(a.trecibe-a.computa<0,a.trecibe-a.computa,0)) faltante,
		               SUM(if(a.trecibe-a.computa>0,a.trecibe-a.computa,0)) sobrante 
		        FROM dine AS a LEFT JOIN scaj AS b ON a.cajero=b.cajero 
		        WHERE a.fecha<=$qfecha AND EXTRACT(YEAR_MONTH FROM a.fecha)=".substr($qfecha,0,6)." GROUP BY MID(a.caja,1,1) ";
		
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$esta->SetFont('Arial','',9);
				$esta->cell(14,5,$row->caja,0,0, 'L');
				$esta->cell(32,5,substr($row->nombre,1,25), 0, 0, 'L');
				$esta->cell(40,5,number_format($row->trecibe,2), 0, 0, 'R');
				$esta->cell(40,5,number_format($row->computa,2), 0, 0, 'R');
				$esta->cell(32,5,number_format($row->faltante,2), 0, 0, 'R');
				$esta->cell(32,5,number_format($row->sobrante,2), 0, 0, 'R');
				$esta->ln(5);
			}
		}$query->free_result();


		$mSQL = "SELECT sum(trecibe) tr, sum(computa) tc, sum(if(trecibe-computa<0,trecibe-computa,0))tf , sum(if(trecibe-computa>0,trecibe-computa,0)) ts FROM dine WHERE fecha<=$qfecha AND EXTRACT(YEAR_MONTH FROM fecha)=".substr($qfecha,0,6)."  ";

		$query = $this->db->query($mSQL);
		$esta->SetTextColor(255,255,255);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$esta->SetFont('Arial','B',9);
				$esta->cell(46,5,"Totales..".substr($qfecha,0,6),0,0, 'R',1);
				$esta->cell(40,5,number_format($row->tr,2), 0, 0, 'R',1);
				$esta->cell(40,5,number_format($row->tc,2), 0, 0, 'R',1);
				$esta->cell(32,5,number_format($row->tf,2), 0, 0, 'R',1);
				$esta->cell(32,5,number_format($row->ts,2), 0, 0, 'R',1);
				$esta->ln(4);
			}
		}
		$query->free_result();
		$esta->SetTextColor(0);


		$esta->ln(2);
		$esta->SetFont('Arial','I',14);
		$esta->cell(0,7,"RECAUDACION POR TIPO DE PAGO DEL MES", 1, 2, 'L');
		$esta->ln(1);
		
		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(100,5,"FORMA DE PAGO", 0, 0, 'C',1);
		$esta->cell(50,5,"MONTO", 0, 0, 'C',1);
		$esta->cell(20,5,"#TRANS", 0, 0, 'C',1);
		$esta->cell(20,5,"%", 0, 0, 'C',1);
		$esta->SetTextColor(0);
		$esta->ln(5);
		
		$venta = $this->datasis->dameval("SELECT sum(a.total) FROM itdine AS a, dine AS b WHERE b.fecha<=$qfecha AND a.numero=b.numero AND EXTRACT(YEAR_MONTH FROM b.fecha)=".substr($qfecha,0,6)."");
		
		$mSQL = "SELECT a.tipo, b.nombre, sum(a.total) tot,count(*) tra
        	FROM itdine AS a 
        	LEFT JOIN tarjeta AS b ON a.tipo=b.tipo
        	JOIN dine AS c ON a.numero=c.numero
        	WHERE c.fecha<=$qfecha AND EXTRACT(YEAR_MONTH FROM c.fecha)=".substr($qfecha,0,6)."
        	GROUP BY a.tipo ";

		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$esta->SetFont('Arial','',9);
				$esta->cell(20,5,$row->tipo,0,0, 'C');
				$esta->cell(80,5,$row->nombre, 0, 0, 'L');
				$esta->cell(50,5,number_format($row->tot,2), 0, 0, 'R');
				$esta->cell(20,5,number_format($row->tra,0), 0, 0, 'R');
				$esta->cell(20,5,number_format($row->tot*100/$venta,2), 0, 0, 'R');
				$esta->ln(4);
			}
		}
		$query->free_result();

		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(100,5,"Total Ventas por POS Bs...", 0, 0, 'R',1);
		$esta->cell(50,5,number_format($venta,2), 0, 0, 'R',1);
		$esta->cell(40,5,'', 0, 0, 'R',1);
		$esta->SetTextColor(0);
		$esta->ln(6);


		$dech=$this->datasis->dameval("SELECT sum(gtotal*IF(tipo='D',-1,1)) FROM fmay WHERE fecha<=$qfecha AND EXTRACT(YEAR_MONTH FROM fecha)=".substr($qfecha,0,6)." AND tipo<>'A' ");
		$esta->SetFont('Arial','B',10);
		$esta->SetFillColor(0,0,120);
		$esta->SetTextColor(255,255,255);
		$esta->cell(100,5,"Total Ventas al Mayor Bs...", 0, 0, 'R',1);
		$esta->cell(50,5,number_format($dech,2), 0, 0, 'R',1);
		$esta->cell(40,5,'', 0, 0, 'R',1);
		$esta->SetTextColor(0);
		$esta->ln(10);


		$esta->SetFillColor(0,130,0);
		$esta->SetFont('Arial','B',14);
		$esta->SetTextColor(255,255,255);
		$esta->cell(100,7,"Total Ventas al Mayor y POS Bs...", 0, 0, 'R',1);
		$esta->cell(50,7,number_format($dech+$venta,2), 0, 0, 'R',1);
		$esta->cell(40,7,'', 0, 0, 'R',1);
		$esta->SetTextColor(0);
		$esta->ln(5);



//		$esta->cell(140,5,"VENTAS TOTALES AL MAYOR",0,0,"L");
//		$esta->cell(50,5,number_format($dech,2),0,0,"R");
//		$esta->ln(5);



/*

		$esta->SetFont('Arial','',10);
		$cajapos=$this->datasis->dameval("SELECT valor FROM valores WHERE nombre='CAJAPOS'");
		$defe=$this->datasis->dameval("SELECT sum(efectivo) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
		$esta->cell(140,5,"DEPOSITOS EN EFECTIVO EN TRANSITO",0,0,"L");
		$esta->cell(50,5,number_format($defe,2),0,0,"R");
		$esta->ln(5);
		
		$dech=$this->datasis->dameval("SELECT sum(cheques) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
		$esta->cell(140,5,"DEPOSITOS EN CHEQUE EN TRANSITO",0,0,"L");
		$esta->cell(50,5,number_format($dech,2),0,0,"R");
		$esta->ln(5);
		
		$detc=$this->datasis->dameval("SELECT sum(tarjeta) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
		$esta->cell(140,5,"DEPOSITOS EN TARJETA DE CREDITO",0,0,"L");
		$esta->cell(50,5,number_format($detc,2),0,0,"R");
		$esta->ln(5);
		
		$detd=$this->datasis->dameval("SELECT sum(tdebito) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
		$esta->cell(140,5,"DEPOSITOS EN TARJETA DE DEBITO",0,0,"L");
		$esta->cell(50,5,number_format($detd,2),0,0,"R");
		$esta->ln(5);
		
		$pago=$this->datasis->dameval("SELECT sum(monto) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' AND tipo='TR' ");
		$esta->cell(140,5,"TRASFERIDO A ADMINISTRACION",0,0,"L");
		$esta->cell( 50,5,number_format($pago,2),0,0,"R");
		$esta->ln(5);
		
		$cesta=$this->datasis->dameval("SELECT sum(monto) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' AND tipo='RM' ");
		$esta->cell(140,5,"CESTA TICKET Y VALORES AL COBRO",0,0,"L");
		$esta->cell( 50,5,number_format($cesta,2),0,0,"R");
		$esta->ln(5);
		
		$esta->SetTextColor(255,255,255);
		$esta->cell(140,5, "RESUMEN DISTRIBUCION POS",0,0,"L",1);
		$esta->cell(50,5,number_format($defe+$dech+$detc+$detd+$pago+$cesta,2),0,0,"R",1);
		$esta->ln(8);
		$esta->SetTextColor(0);
		$pago=$this->datasis->dameval("SELECT saldo FROM banc WHERE codbanc='$cajapos' ");

		$esta->SetFont('Arial','B',18);
		$esta->SetFillColor(0,102,204);
		$esta->SetTextColor(255,255,255);
		$esta->cell(190,10,"SALDO CAJA POS: ".number_format($pago,2),0,0,"C",1);
		$esta->ln(12);
		
		$esta->SetFillColor(52,186,114);
		$esta->SetTextColor(0);
		
		$esta->ln(2);
		$esta->SetFont('Arial','I',14);
		$esta->cell(0,7,"VALORES EN CUSTODIA", 1, 2, 'L');
		$esta->ln(1);

		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(50,5,"Descripcion", 0, 0, 'C',1);
		$esta->cell(35,5,"Anterior", 0, 0, 'C',1);
		$esta->cell(35,5,"Ingresos", 0, 0, 'C',1);
		$esta->cell(35,5,"Egresos", 0, 0, 'C',1);
		$esta->cell(35,5,"Saldo", 0, 0, 'C',1);
		$esta->SetTextColor(0);
		$esta->ln(5);
		
		$mSQL = "SELECT tarjeta, concepto, enlace, saldo, descrip FROM tardet WHERE enlace IS NOT NULL AND enlace!='' GROUP BY enlace";
		$manterior = $mingresos = $megresos  = $msaldos   = 0;
		
		$menlaces = $this->db->query($mSQL);
		$esta->SetFont('Arial','',10);
		
		if ($menlaces->num_rows() > 0){
			foreach ($menlaces->result() as $row){
				$mSQL = "SELECT sum(a.total) FROM itdine AS a, dine AS b WHERE a.tipo='".$row->tarjeta."' AND a.concepto='".$row->concepto."' AND a.numero=b.numero AND b.fecha=$qfecha GROUP BY fecha ";
				$ingreso = $this->datasis->dameval($mSQL);
				$mSQL = "SELECT sum(a.monto) FROM itbcaj AS a, bcaj AS b WHERE a.tipo='".$row->tarjeta."' AND a.concep='".$row->concepto."' AND a.numero=b.numero AND b.estampa=$qqfecha GROUP BY fecha ";
				$egreso = $this->datasis->dameval($mSQL);
				$esta->cell(50,5,$row->descrip, 0, 0, 'L');
				$esta->cell(35,5,number_format($row->saldo-$ingreso+$egreso,2), 0, 0, 'R');
				$manterior += $row->saldo-$ingreso+$egreso ;
				$esta->cell(35,5,number_format($ingreso,2), 0, 0, 'R');
				$mingresos += $ingreso ;
				$esta->cell(35,5,number_format($egreso,2), 0, 0, 'R');
				$megresos += $egreso ;
				$esta->cell(35,5,number_format($row->saldo,2), 0, 0, 'R');
				$msaldos += $row->saldo;
				$esta->ln(5);
			}
		}
		$esta->SetFont('Arial','B',10);
		$esta->SetTextColor(255,255,255);
		$esta->cell(50,5, "Totales...",0,0,'R',1);
		$esta->cell(35,5,number_format($manterior,2),0,0,'R',1);
		$esta->cell(35,5,number_format($mingresos,2),0,0,'R',1);
		$esta->cell(35,5,number_format($megresos,2), 0,0,'R',1);
		$esta->cell(35,5,number_format($msaldos,2),  0,0,'R',1);
		$esta->SetTextColor(0);
*/		
		$esta->Output();
	}

}
?>
