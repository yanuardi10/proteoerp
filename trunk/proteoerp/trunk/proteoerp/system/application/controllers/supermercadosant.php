<?php
class Supermercado extends Controller {

	function Supermercado(){
		parent::Controller();
		$this->load->helper('text');
		$this->load->library("rapyd");
	}

	function index() {
		$this->load->view('view_ventas');
	}

	function posfact(){
		$this->rapyd->load('datagrid');  
		$this->rapyd->load('dataform');
		$this->load->helper('openflash');
		
		$diai =$this->uri->segment(3);
		$mesi =$this->uri->segment(4);
		$anoi =$this->uri->segment(5);
		$diaf =$this->uri->segment(6);
    $mesf =$this->uri->segment(7);
		$anof =$this->uri->segment(8);
		
		if($diai===FALSE or $mesi===FALSE or $anoi===FALSE or $diaf===FALSE or $mesf===FALSE or $anof===FALSE){
			$usema   = mktime(0, 0, 0, date("m"), date("d"),  date("Y"));
			$fechai  = $fechaf  =date('Y/m/d',$usema);
			$qfechai = $qfechaf =date('Ymd',$usema);
		}else{
			$qfechai = $anoi.$mesi.$diai ;
			$qfechaf = $anof.$mesf.$diaf ;
			$fechai  ="$anoi/$mesi/$diai";
			$fechaf  ="$anof/$mesf/$diaf"; 
		}
		
		$data['lista'] = open_flash_chart_object(600,250, site_url("supermercado/grafico/$qfechai/$qfechaf"));
		$select="date_format(fecha,'%d/%m/%Y') FECHA,
		         FORMAT(sum((gtotal-impuesto)*(SUBSTRING(numero,1,1)<>'X')),2) SUBTOTAL,
		         FORMAT(sum(impuesto*(SUBSTRING(numero,1,1)<>'X')),2) IVA, 
		         FORMAT(sum(gtotal*(SUBSTRING(numero,1,1)<>'X')),2) TOTAL,
		         FORMAT(sum(gtotal*(SUBSTRING(numero,1,1)<>'X' AND gtotal<0) ),2) DEVOLU, 
		         FORMAT(sum(gtotal*(SUBSTRING(numero,1,1)='X')),2) NULAS, 
		         FORMAT(count(*),0) TRANS";
		 
		 $union="UNION (SELECT 'Totales....' FECHA,
		                FORMAT(sum((gtotal-impuesto)*(SUBSTRING(numero,1,1)<>'X')),2) SUBTOTAL,
		                FORMAT(sum(impuesto*(SUBSTRING(numero,1,1)<>'X')),2) IVA, 
		                FORMAT(sum(gtotal*(SUBSTRING(numero,1,1)<>'X')),2) TOTAL,
		                FORMAT(sum(gtotal*(SUBSTRING(numero,1,1)<>'X' AND gtotal<0) ),2) DEVOLU, 
		                FORMAT(sum(gtotal*(SUBSTRING(numero,1,1)='X')),2) NULAS, 
		                FORMAT(count(*),0) TRANS
		         FROM viefac WHERE fecha BETWEEN $qfechai AND $qfechaf GROUP BY 'A') ";
		
		$grid = new DataGrid();
		$grid->db->select($select);  
		$grid->db->from("posfact");
		$grid->db->where("fecha=NOW()");
		$grid->db->groupby("fecha");
		$grid->column("Fecha", "FECHA");
		$grid->column("Sub-Total",  "SUBTOTAL");
		$grid->column("I.V.A.", "IVA");
		$grid->column("Total", "TOTAL");
		$grid->column("Devoluciones", "DEVOLU");
		$grid->column("Anuladas", "NULAS");
		$grid->column("Transferencias", "TRANS");
		$grid->build();
		
		$data['forma'] = "<h3>VENTAS EN CURSO</h3>\n".$grid->output;
		$data['forma'] .= "<h3>VENTAS YA CERRADAS</h3>";
		
		$filter = new DataForm('/venta/posfact');
		$filter->title('Filtro de ventas cerradas');
		$filter->fechai = new dateField("Desde","fechai","d/m/Y");
		$filter->fechai->insertValue=$fechai;
		$filter->fechaf = new dateField("Hasta","fechaf","d/m/Y");
		$filter->fechaf->insertValue=$fechaf;
		$filter->fechai->size=$filter->fechaf->size=10;
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/posfact'),array('fechai','fechaf')), $position="BL");
		$filter->build_form();
		
		$grid2 = new DataGrid("Resultados");
		$grid2->db->select($select);  
		$grid2->db->from("viefac");
		$grid2->db->where('fecha >= ',$fechai);  
		$grid2->db->where('fecha <= ',$fechaf);  
		$grid2->db->groupby("fecha $union");
		$grid2->column_orderby("Fecha"         , "FECHA"   );
		$grid2->column_orderby("Sub-Total"     , "SUBTOTAL");
		$grid2->column_orderby("I.V.A."        , "IVA"     );
		$grid2->column_orderby("Total"         , "TOTAL"   );
		$grid2->column_orderby("Devoluciones"  , "DEVOLU"  );
		$grid2->column_orderby("Anuladas"      , "NULAS"   );
		$grid2->column_orderby("Transferencias", "TRANS"   );
		$grid2->build();
		
		$data['forma'] .= $filter->output.$grid2->output;
		//echo $grid2->db->last_query();
		$data['titulo'] = $this->rapyd->get_head()."<center><h2>ANALISIS DE VENTAS</h2></center>";
		$this->layout->buildPage('ventas/view_ventas', $data);
	}
	
	function grafico(){
		$this->load->library('Graph');
		$data = array();
		$dia  = array();
		
		$mSQL = "SELECT if(b.tipo IS NULL,a.tipo,b.nombre) nombre, sum(a.monto*(SUBSTRING(a.numero,1,1)<>'X')) monto
			 FROM viepag a LEFT JOIN tarjeta b ON a.tipo=b.tipo
			 WHERE fecha BETWEEN 20070101 AND 20070131 
			 GROUP BY a.tipo ORDER BY monto DESC";
		
		$res = $this->db->query($mSQL) or die("Bad SQL 1");
		$total = 0;
		foreach( $res->result() as $row )
			if ($row->monto<0) $total += $row->monto*-1; else $total += $row->monto;
		$res->first_row();
		foreach( $res->result() as $row ) {
			$titu[] = substr($row->nombre,0,20);
			if ($row->monto<0) $data[] = round( ($row->monto*100/$total)*-1,0); else $data[] = round( $row->monto*100/$total,0);
		}

		// use the chart class to build the chart:
		$g = new Graph();
		$g->title( 'DISTRIBUCION DE LA COBRANZA ','{font-size:18px; color: #d01f3c}');
		$g->set_data( $data );
		$g->bar_filled( 80, '#9933CC', '#8010A0', '', 10 );
		$g->set_y_max( 100 );
		$g->bg_colour='#FFFFFF';
		$g->y_label_steps( 5 );
		$g->set_x_labels($titu);
		$g->set_y_legend( 'Porcentaje de Venta', 14,'0x639F45' );
		$g->set_x_legend( 'Forma de Pago', 14,'0x639F45' );
		$g->set_bg_image(site_url('/images/ventafon.png'), 'center', 'middle' );
		$g->set_tool_tip( '#val#%25' );
		echo $g->render();
		$res->free_result();
	}
	
	function poscuadre() {
		$this->rapyd->load("datagrid");
		$this->rapyd->load("datafilter");
		
		$diai =$this->uri->segment(3);
		$mesi =$this->uri->segment(4);
		$anoi =$this->uri->segment(5);
		
		if($diai===FALSE or $mesi===FALSE or $anoi===FALSE){
			$fechai = date('Y/m/d');
			$qfechai= date('Ymd');
		}else{
			$fechai ="$anoi/$mesi/$diai";
			$qfechai=$anoi.$mesi.$diai;
		}
  	
 		$filter = new DataForm();
 		$filter->title('Filtro de cajas');
		$filter->fechai = new dateField("Fecha","fechai","d/m/Y");
		$filter->fechai->insertValue=$fechai;
		$filter->fechai->size=10;
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/poscuadre'),'fechai'), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid();
		$grid->db->select("a.caja caja,IFNULL(b.nombre,'N/A') nombre, a.cajero cajero,FORMAT(SUM(a.gtotal),2) monto, sum(TRUNCATE(a.gtotal/50000,0)) cupones ");  
		$grid->db->from("posfact a");
		$grid->db->where('substring(numero,1,1)!=','X');
		$grid->db->where('fecha',$qfechai);
		$grid->db->join("scaj b","a.cajero=b.cajero","LEFT");   
		$grid->db->groupby('a.caja,a.cajero');
		$grid->column_detail("Caja","caja", site_url("supermercado/concaja/<#caja#>/<#cajero#>/$qfechai"));
		$grid->column("Nombre" , "nombre" );
		$grid->column("Cajero" , "cajero" );
		$grid->column("Cupones", "cupones");
		$grid->column("Monto"  , "monto"  );
		$grid->build();
		
		//echo $grid->db->last_query();

		$consul = new DataForm('supermercado/buscafac/search/osp');
 		$consul->title('Buscar Factura');
		$consul->fechad = new dateField("Desde","fechad","d/m/Y");
		$consul->fechah = new dateField("Hasta","fechah","d/m/Y");
		$consul->nombre = new inputField("Nombre", "nombre"); 
		$consul->cedula = new inputField("C&eacute;dula/RIF", "cedula");
		$consul->fechad->insertValue = $consul->fechah->insertValue=date("Y/m/d");
		$consul->fechah->size=$consul->fechad->size=10;
		$consul->submit("btn_submit","Buscar");
		$consul->build_form();
		
		$data['lista'] =$filter->output.$grid->output.$consul->output;
		$data['forma'] = '<br><br>';
		$data['titulo'] = $this->rapyd->get_head()."<h2>CONSULTA DE CAJAS</h2>";
		
		$this->layout->buildPage('ventas/view_ventas', $data);
  }
  
  //****************************************
	//****************************************
	//****************************************

	function concaja() {
		$this->load->library('table');
		$this->rapyd->load("datagrid");
		$caja   = $this->uri->segment(3) ;
		$cajero = $this->uri->segment(4);
		$fecha  = $this->uri->segment(5); 
		$menvia="$caja/$cajero/$fecha";
		
		
		/*$data['lista']  = "<table class='bordetabla' width='100%'>\n<tr><td class=mininegro>ENLACES</td></tr>\n";
		$data['lista'] .= "<tr><td><A href='".base_url()."index.php/ventas/detfact/$menvia'>Facturas</a></td></tr>\n";
		$data['lista'] .= "<tr><td><A href='".base_url()."index.php/ventas/detsfpa/$menvia'>Pagos</a></td></tr>\n";
		$data['lista'] .= "<tr><td><A href='".base_url()."index.php/ventas/detitfact/$menvia'>Articulo</a></td></tr>\n</table>\n";
		$data['lista'] .= "<p><A href='".base_url()."index.php/ventas/poscuadre'>Regresar</a></p>\n"; */
           
		$grid = new DataGrid('Resumen de caja');
		$grid->db->select("sum((gtotal-impuesto)*(SUBSTRING(numero,1,1)<>'X') ) base,
		                   sum(impuesto*(SUBSTRING(numero,1,1)<>'X')) impuesto,
		                   sum(gtotal*(SUBSTRING(numero,1,1)<>'X')) total,
		                   sum(gtotal*(SUBSTRING(numero,1,1)<>'X' AND gtotal<0) ) devol,
		                   sum(1*(SUBSTRING(numero,1,1)='X')) nulos , 
		                   sum(gtotal*(SUBSTRING(numero,1,1)='X')) nulas, 
		                   count(*) trans, 
		                   sum((SUBSTRING(numero,1,1)<>'X' AND gtotal<0)) nose,
		                   max(SUBSTRING(numero,2,7)) final, 
		                   min(SUBSTRING(numero,2,7)) inicial");
		$grid->db->from("posfact");
		$grid->db->where('fecha',$fecha);
		$grid->db->where('cajero',$cajero);
		$grid->db->where('caja',$caja);  
		$grid->db->groupby('caja');
		$grid->column("Sub Total","base"     );
		$grid->column("Impuesto" ,"impuesto" );
		$grid->column("Total"    ,"total"    );
		$grid->column("Devuelto" ,"devol"    );
		$grid->column("Nulo"     ,"nulas"    );
		$grid->column("#Trans"   ,"trans"    );
		$grid->build();
		$arreglo=$grid->recordSet[0];
		$data['lista'] = $grid->output.'<b class="mainheader">Factura Inicial: '.$arreglo['inicial'].' Factura Final: '.$arreglo['final'].'</b>';

		$mSQL = "SELECT sum((gtotal-impuesto)*(SUBSTRING(numero,1,1)<>'X') ) base,
		                sum(impuesto*(SUBSTRING(numero,1,1)<>'X')) impuesto,
		              sum(gtotal*(SUBSTRING(numero,1,1)<>'X')) total,
		              sum(gtotal*(SUBSTRING(numero,1,1)<>'X' AND gtotal<0) ) devol,
		                sum(1*(SUBSTRING(numero,1,1)='X')) nulos , 
		              sum(gtotal*(SUBSTRING(numero,1,1)='X')) nulas, 
		                count(*) trans, 
		                sum((SUBSTRING(numero,1,1)<>'X' AND gtotal<0)) ,
		                max(SUBSTRING(numero,2,7)) final, 
		                min(SUBSTRING(numero,2,7)) inicial
		         FROM posfact 
		        WHERE fecha=$fecha AND cajero='$cajero' AND caja='$caja'
		        GROUP BY caja ";
		
		$data['forma'] = '';
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$data['forma'] .= "<h1>RESUMEN DE CAJA</h1>\n";
			$data['forma'] .= "<table border = 1 valign=center width=100%>\n";
			$data['forma'] .= "<tr bgcolor=\"#c0c0c0\">\n";
			$data['forma'] .= "<td><center>Sub Total</center></td>\n";
			$data['forma'] .= "<td><center>Impuesto</center></td>\n";
			$data['forma'] .= "<td><center>Total</center></td>\n";
			$data['forma'] .= "<td><center>Devuelto</center></td>\n";
			$data['forma'] .= "<td><center>Nulo</center>\n";
			$data['forma'] .= "</td><td><center>#Trans</center></td>\n";
			$data['forma'] .= "</tr>\n";
			
			$row = $query->row();
			
			$tempo   = $this->db->query("SELECT COUNT(*) aa FROM positfact WHERE cantidad<0 AND fecha=$fecha AND cajero='$cajero' AND caja='$caja'");
			$devoluc = $tempo->row();
			$tempo   = $this->db->query("SELECT abs(sum(monto)) aa FROM positfact WHERE cantidad<0 AND fecha=$fecha AND cajero='$cajero' AND caja='$caja'");
			$devolum = $tempo->row();
			
			$data['forma'] .= "<tr>\n";
			$data['forma'] .= "<td align=right>".number_format($row->base,2)." </td>\n";
			$data['forma'] .= "<td align=right>".number_format($row->impuesto,2)." </td>\n";
			$data['forma'] .= "<td align=right>".number_format($row->total,2)." </td>\n";
			$data['forma'] .= "<td align=right>".$devoluc->aa." x ".number_format(abs($devolum->aa),2)." </td>\n";
			$data['forma'] .= "<td align=right>".$row->nulos." x ".number_format(abs($row->nulos),2)." </td>\n";
			$data['forma'] .= "<td align=right>".$row->trans ."</td>\n";
			$data['forma'] .= "</tr>\n";
			$data['forma'] .= "<tr><td>Factura Inicial</td><td colspan=2>".$row->inicial."</td><td>Final</td><td colspan=2>".$row->final."<td></tr>\n";
			$data['forma'] .= "</table>\n";
			
			$mSQL = "SELECT impuesto tasa,  
			                sum(ROUND(monto*100/(impuesto+100),2)) AS base, 
			                sum(monto-ROUND(monto*100/(impuesto+100),2)) AS iva, 
			                sum(monto) AS total
			         FROM `positfact` 
			         WHERE fecha=$fecha AND caja='$caja' AND cajero='$cajero' AND SUBSTRING(numero,1,1)!='X' 
			         GROUP BY impuesto";
			
			$data['forma'] .= "<H1>DETALLE DEL IMPUESTO</H1>\n";
			$data['forma'] .= "<table border = 1 valign=center width=100%>\n";
			$data['forma'] .= " <tr bgcolor=\"#c0c0c0\">\n";
			$data['forma'] .= "  <td>Tasa%</td>\n";
			$data['forma'] .= "  <td><center>Base Imponible</center></td>\n";
			$data['forma'] .= "  <td><center>Impuesto</center></td>\n";
			$data['forma'] .= "  <td><center>Total</center></td>\n";
			$data['forma'] .= " </tr>\n";
			
			$query = $this->db->query($mSQL);
			
			foreach ($query->result() as $row){
				$data['forma'] .= "<tr>\n";
				$data['forma'] .= "<td>".number_format($row->tasa,2)."</td>\n";
				$data['forma'] .= "<td>".number_format($row->base,2)."</td>\n";
				$data['forma'] .= "<td>".number_format($row->iva,2)."</td>\n";
				$data['forma'] .= "<td>".number_format($row->total,2)."</td>\n";
				$data['forma'] .= "</tr>\n";
			}
			$data['forma'] .= "</table>\n";
			
			// FORMAS DE PAGO
			
			$mSQL_p = "SELECT a.tipo,
			                a.banco,
			                count(*) tran,
			                sum((a.monto)*(SUBSTRING(a.numero,1,1)<>'X') ) monto, 
			                b.nombre, 
			                c.descrip
			         FROM possfpa a JOIN tarjeta b ON a.tipo=b.tipo LEFT JOIN tardet c ON a.banco=c.concepto AND a.tipo=c.tarjeta
			         WHERE a.fecha=$fecha AND a.caja='$caja' AND a.cajero='$cajero'";
			$mSQL=$mSQL_p.' GROUP BY a.tipo';
			$data['forma'] .= "<H1>RESUMEN DE FORMAS DE PAGO</H1>\n";
			$data['forma'] .= "<table border = 1 valign=center width=100%>\n";
			$data['forma'] .= " <tr bgcolor=\"#c0c0c0\">\n";
			$data['forma'] .= "  <td>Tipo</td>\n";
			$data['forma'] .= "  <td><center>Cantidad</center></td>\n";
			$data['forma'] .= "  <td><center>Monto</center></td>\n";
			$data['forma'] .= " </tr>\n";
			
			$query = $this->db->query($mSQL);
			foreach ($query->result() as $row){
				$data['forma'] .= "<tr>\n";
				$data['forma'] .= "<td>".$row->tipo." ".$row->nombre."</td>";
				$data['forma'] .= "<td align=right>".number_format($row->tran,0)." </td>";
				$data['forma'] .= "<td align=right> ".number_format($row->monto,2)." </td>";
				$data['forma'] .= "</tr>\n";
			}$data['forma'] .= "</table>\n";
			
			$mSQL=$mSQL_p.' GROUP BY a.tipo,a.banco';
			
			$data['forma'] .= "<H1> DETALLE DE CAJA </H1>\n";
			$data['forma'] .= "<table border = 1 valign=center width=100%>\n";
			$data['forma'] .= " <tr bgcolor=\"#c0c0c0\">\n";
			$data['forma'] .= "  <td>Tipo</td>\n";
			$data['forma'] .= "  <td><center>Concepto<center></td>\n";
			$data['forma'] .= "  <td><center>Cantidad</center></td>\n";
			$data['forma'] .= "  <td><center>Monto</center></td>\n";
			$data['forma'] .= " </tr>\n";
			
			$query = $this->db->query($mSQL);
			foreach ($query->result() as $row){
				$data['forma'] .= "<tr>\n";
				$data['forma'] .= "<td>".$row->tipo." ".$row->nombre."</td>\n";
				$data['forma'] .= "<td>";
				if (empty($row->banco)) $data['forma'] .= "-";
				$data['forma'] .= $row->banco." ".$row->descrip."</td>\n";
				$data['forma'] .= "<td align=right>".number_format($row->tran,0)." </td>\n";
				$data['forma'] .= "<td align=right> ".number_format($row->monto,2)." </td>\n";
				$data['forma'] .= "</tr>\n";
			}
			$data['forma'] .= "</table>\n<br>\n";
		}else{
			$data['forma'] .= "<center><h1>No hay registros en la consulta para este momento</h1></center>";
		}
		
		$data['titulo'] = $this->rapyd->get_head()."<center><h2>RESULTADO CAJERO ".$cajero." CAJA ".$caja." FECHA ".$fecha."</h2></center>\n";
		$this->layout->buildPage('ventas/view_ventas', $data);
	}

	function detfact() {
		$caja   = $this->uri->segment(3) ;
		$cajero = $this->uri->segment(4);
		$fecha  = $this->uri->segment(5); 
		$menvia=base_url()."index.php/ventas/factura/$caja/$cajero/$fecha";
		$data['titulo'] = "<center><h2>FACTURAS CAJERO ".$cajero." CAJA ".$caja." FECHA ".$fecha."</h2></center>\n";
		$data['script'] = '';
		$data['lista'] = '';
		$mSQL = "SELECT a.tipo, a.numero, a.fecha, if(b.nombres IS NULL,a.nombres,concat(b.nombres,' ',b.apellidos)) nombres, a.impuesto, a.gtotal, a.hora 
			FROM posfact a LEFT JOIN club b ON a.cliente=b.cod_tar
			WHERE fecha=$fecha AND cajero='$cajero' AND caja='$caja' 
			ORDER BY numero ";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0)
		{
			$data['forma']  = "<h3>VENTA POR FACTURAS</H3>\n";
			$data['forma'] .= "<table border = 1 valign=center width=100%>\n";
			$data['forma'] .= " <tr bgcolor=\"#c0c0c0\">\n";
			$data['forma'] .= "  <td><center>Tipo</center></td>\n";
			$data['forma'] .= "  <td><center>Numero</center></td>\n";
			$data['forma'] .= "  <td><center>Fecha</center></td>\n";
			$data['forma'] .= "  <td><center>Nombres</center></td>\n";
			$data['forma'] .= "  <td><center>Impuesto</center>\n";
			$data['forma'] .= "  <td><center>Total</center>\n";
			$data['forma'] .= " </tr>\n";
			
			foreach ($query->result() as $row){
				$data['forma'] .= "<tr>";
				if ( $row->gtotal > 0) {
					$data['forma'] .= "<td align=center >".$row->tipo."</td>";
				} else {
					$data['forma'] .= "<td align=center bgcolor=red >".$row->tipo."</td>";
				};
				$data['forma'] .= "<td align=left><a href='$menvia/".$row->numero."'>".$row->numero."</a></td>";
				$data['forma'] .= "<td align=left>".$row->fecha."</td>";
				$data['forma'] .= "<td align=left>".$row->nombres."</td>";
				$data['forma'] .= "<td align=right>".number_format($row->impuesto,2)." </td>";
				$data['forma'] .= "<td align=right>".number_format($row->gtotal,2)." </td>";
				$data['forma'] .= "</tr>";
			}
			$data['forma'] .= "</table>\n<br>\n";
		} else {
			$data['forma'] = "<center><h2>NO SE ENCONTRARON REGISTROS</h2></center>";
		}
		$this->load->view('view_ventas',$data);
	}

	function factura() {
		$caja   = $this->uri->segment(3) ;
		$cajero = $this->uri->segment(4);
		$fecha  = $this->uri->segment(5); 
		$numero = $this->uri->segment(6); 
		$menvia=base_url()."index.php/ventas/detfact/$caja/$cajero/$fecha";
		
		$data['titulo'] = "<center><h2>FACTURA ".$numero." CAJA ".$caja." FECHA ".$fecha."</h2></center>\n";
		$data['script'] = '';
		$data['lista'] = '';
		$data['forma'] = '';
		    
		$mSQL = "SELECT cliente, cedula, nombres, direc1, direc2 
		         FROM posfact 
		         WHERE fecha=$fecha AND cajero='$cajero' AND caja='$caja' AND numero='$numero'";
		$query = $this->db->query($mSQL);
		
		$row = $query->row();
		$data['forma'] .= "NOMBRE: ".$row->nombres."<br>\n";
		$data['forma'] .= "CEDULA: ".$row->cedula."<br>\n";
		$data['forma'] .= "TARJETA: ".$row->cliente."<BR>";
		$data['forma'] .= "DIRECCION: ".$row->direc1." ". $row->direc2."<br>";
		
		$mSQL = "SELECT if(referen='',codigo,referen) codigo, descrip, cantidad, precio, monto, impuesto 
		         FROM positfact 
		         WHERE fecha=$fecha AND cajero='$cajero' AND caja='$caja' AND numero='$numero'";
		
		$query = $this->db->query($mSQL);
		
		$data['forma'] .= "<table border = 1 valign=center width=100%>\n";
		$data['forma'] .= " <tr bgcolor=\"#c0c0c0\">\n";
		$data['forma'] .= "  <td><center>Codigo</center></td>\n";
		$data['forma'] .= "  <td><center>Descripcion</center></td>\n";
		$data['forma'] .= "  <td><center>Cantidad</center></td>\n";
		$data['forma'] .= "  <td><center>Precio</center>\n";
		$data['forma'] .= "  <td><center>Total</center>\n";
		$data['forma'] .= "  <td><center>IVA</center>\n";
		$data['forma'] .= " </tr>\n";
		
		foreach ($query->result() as $row){
			$data['forma'] .= "<tr>";
			$data['forma'] .= "<td>".$row->codigo."</td>";
			$data['forma'] .= "<td>".$row->descrip."</td>";
			$data['forma'] .= "<td align=right>".$row->cantidad."</td>";
			$data['forma'] .= "<td align=right>".number_format($row->precio,2)." </td>";
			$data['forma'] .= "<td align=right>".number_format($row->monto,2)." </td>";
			$data['forma'] .= "<td align=right>".$row->impuesto."</td>";
			$data['forma'] .= "</tr>";
		}
		$data['forma'] .= "</table>\n<br>\n";
		
		$mSQL = "SELECT a.tipo, 
		                a.fecha, 
		                a.monto, 
		                b.descrip 
		         FROM possfpa AS a LEFT JOIN tardet AS b ON a.banco=b.concepto AND a.tipo=b.tarjeta
		         WHERE a.fecha=$fecha AND a.cajero='$cajero' AND a.caja='$caja' AND a.numero=$numero 
		         ORDER BY a.tipo";
		
		$query = $this->db->query($mSQL);
		
		$data['forma'] .= "<h3>FORMA DE PAGO</H3>\n";
		$data['forma'] .= "<table border = 1 valign=center width=100%>\n";
		$data['forma'] .= "  <tr bgcolor=\"#c0c0c0\">\n";
		$data['forma'] .= "  <td><center>Tipo</center></td>\n";
		$data['forma'] .= "  <td><center>Fecha</center></td>\n";
		$data['forma'] .= "  <td><center>Monto</center>\n";
		$data['forma'] .= "  <td><center>Referencia</center></td>\n";
		$data['forma'] .= "</tr>\n";
		
		foreach ($query->result() as $row){
			$data['forma'] .= "<tr>";
			$data['forma'] .= "<td align=center >".$row->tipo."</td>\n";
			$data['forma'] .= "<td align=left>".$row->fecha."</td>\n";
			$data['forma'] .= "<td align=right>".number_format($row->monto,2)." </td>\n";
			$data['forma'] .= "<td align=left>".$row->descrip."</td>\n";
			$data['forma'] .= "</tr>\n";
		}
		$data['forma'] .= "</table>\n<br>\n";
		$data['forma'] .= "<a href='$menvia'>Regresar</a><br><br><br>"; 
		$this->load->view('view_ventas',$data);
	}

	function detsfpa() {
		$caja   = $this->uri->segment(3) ;
		$cajero = $this->uri->segment(4);
		$fecha  = $this->uri->segment(5); 
		$menvia=base_url()."index.php/ventas/concaja/$caja/$cajero/$fecha";
		$data['titulo'] = "<center><h2>FACTURAS CAJERO ".$cajero." CAJA ".$caja." FECHA ".$fecha."</h2></center>\n";
		$data['script'] = '';
		$data['lista'] = '';
		$data['forma'] = '';
		
		$mSQL = "SELECT a.tipo, 
		                a.numero, 
		                a.fecha, 
		                a.num_ref, 
		                a.monto, 
		                b.descrip 
		         FROM possfpa AS a LEFT JOIN tardet AS b ON a.banco=b.concepto AND a.tipo=b.tarjeta
		         WHERE a.fecha=$fecha AND a.cajero='$cajero' AND a.caja='$caja' 
		         ORDER BY a.tipo";
		
		$query = $this->db->query($mSQL);
		
		$data['forma'] .= "<h3>FORMAS DE PAGO</H3>\n";
		$data['forma'] .= "<table border = 1 valign=center width=100%>";
		$data['forma'] .= "  <tr bgcolor=\"#c0c0c0\">\n";
		$data['forma'] .= "  <td><center>Tipo</center></td>\n";
		$data['forma'] .= "  <td><center>Numero</center></td>\n";
		$data['forma'] .= "  <td><center>Fecha</center></td>\n";
		$data['forma'] .= "  <td><center>Monto</center>\n";
		$data['forma'] .= "  <td><center>Referencia</center></td>\n";
		$data['forma'] .= "</tr>\n";
		
		foreach ($query->result() as $row){
			$data['forma'] .= "<tr>\n";
			if ($row->monto>0) {
				$data['forma'] .= "<td align=center >".$row->tipo."</td>\n";
			}else {
				$data['forma'] .= "<td align=center bgcolor=red >".$row->tipo."</td>\n";
			};
			$data['forma'] .= "<td align=left>".$row->numero."</td>\n";
			$data['forma'] .= "<td align=left>".$row->fecha."</td>\n";
			$data['forma'] .= "<td align=right>".number_format($row->monto,2)."</td>\n";
			$data['forma'] .= "<td align=left>".$row->descrip."</td>\n";
			$data['forma'] .= "</tr>\n";
		}
		$data['forma'] .= "</table>\n<br>\n";
		$data['forma'] .= "<a href='$menvia'>Regresar</a><br><br><br>";
		
		$this->load->view('view_ventas',$data);
	}

	function detitfact() {
		$caja   = $this->uri->segment(3) ;
		$cajero = $this->uri->segment(4);
		$fecha  = $this->uri->segment(5); 
		$menvia=base_url()."index.php/ventas/concaja/$caja/$cajero/$fecha";
		$data['titulo'] = "<center><h2>VENTAS POR ARTICULO ".$cajero." CAJA ".$caja." FECHA ".$fecha."</h2></center>\n";
		$data['script'] = '';
		$data['lista'] = '';
		$data['forma'] = '';
		
		$mSQL = "SELECT codigo, descrip, SUM(cantidad) cantidad, sum(monto) monto, sum(impuesto) impuesto, referen 
		             FROM positfact 
		             WHERE fecha=$fecha AND cajero='$cajero' AND caja='$caja' 
		             GROUP BY codigo";
		
		$query = $this->db->query($mSQL);
		
		$data['forma'] .= "<H3>VENTAS POR ARTICULOS</H3>\n";
		$data['forma'] .= "<table border = 1 valign=center width=100%>";
		$data['forma'] .= "  <tr bgcolor=\"#c0c0c0\">\n";
		$data['forma'] .= "  <td><center>Codigo</center></td>\n";
		$data['forma'] .= "  <td><center>Descripcion</center></td>\n";
		$data['forma'] .= "  <td><center>Cantidad</center></td>\n";
		$data['forma'] .= "  <td><center>Monto</center>\n";
		$data['forma'] .= "  <td><center>Referencia</center></td>\n";
		$data['forma'] .= "</tr>\n";
		 
		foreach ($query->result() as $row){
			$data['forma'] .= "<tr>\n";
			$data['forma'] .= "<td align=left>".$row->codigo."</td>\n";
			$data['forma'] .= "<td align=left>".$row->descrip."</td>\n";
			$data['forma'] .= "<td align=right>".number_format($row->cantidad,2)."</td>\n";
			$data['forma'] .= "<td align=right>".number_format($row->monto,2)."</td>\n";
			$data['forma'] .= "<td align=left>".$row->referen."</td>\n";
			$data['forma'] .= "</tr>\n";
		}
		$data['forma'] .= "</table>\n<br>\n";
		$data['forma'] .= "<a href='$menvia'>Regresar</a><br><br><br>";
		    
		$this->load->view('view_ventas',$data);
	}

	function buscafac(){
		$this->rapyd->load("datagrid");
		$this->rapyd->load("datafilter");
		$control=array(false , false);

		$filter = new DataFilter("Filtro de Facturas");  
		$filter->fechad = new dateField("Desde","fechad","d/m/Y");
		$filter->fechad->operator=">=";  
		$filter->fechah = new dateField("Hasta","fechah","d/m/Y");
		$filter->fechah->operator="<="; 
		$filter->fechah->clause =$filter->fechad->clause ="where";
		$filter->fechah->db_name=$filter->fechad->db_name="fecha";
		$filter->fechah->size   =$filter->fechad->size   =10;
		$filter->fechah->insertValue=$filter->fechad->insertValue=date('Y/m/d');
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->db_name="concat(b.nombres,' ',b.apellidos)";
		$filter->cedula = new inputField("C&eacute;dula/RIF", "cedula");
		$filter->buttons("reset","search");      
		$filter->build(); 
		
		if(empty($filter->fechah->value) or $filter->fechah->value==date('Y/m/d')) $control[0]=true;
		if(empty($filter->fechah->value) or $filter->fechah->value==date('Y/m/d')) $control[1]=true;
		
		$grid = new DataGrid();
		$grid->per_page = 10;  
		$grid->db->select("b.cedula, DATE_FORMAT('a.fecha', '%d/%m/%Y') fecha, a.cajero, a.caja, a.tipo, a.numero, a.fecha, if(b.nombres IS NULL,a.nombres,concat(b.nombres,' ',b.apellidos)) nombres, a.impuesto, a.gtotal, a.hora ");  
		$grid->db->from("viefac a");
		$grid->db->join("club b","a.cliente=b.cod_tar","LEFT"); 
		if($control[0]) $grid->db->where('a.fecha>=NOW()');
		if($control[1]) $grid->db->where('a.fecha<=NOW()');
		$grid->db->orderby('a.fecha, a.caja, a.numero');
		$grid->column_detail("Caja"    ,"caja", site_url("supermercado/factura/<#caja#>/<#cajero#>/<#fecha#>/<#numero#>"));
		$grid->column("Cajero"  ,"cajero"  );
		$grid->column("Tipo"    ,"tipo"    );
		$grid->column("Numero"  ,"numero"  );
		$grid->column("Fecha"   ,"fecha"   );
		$grid->column("Cedula"  ,"cedula"  );
		$grid->column("Nombres" ,"nombres" );
		$grid->column("Impuesto","impuesto");
		$grid->column("Total"   ,"gtotal"  );
		$grid->build();

		//echo $grid->db->last_query();
	
		$data['lista']='';
		$data['forma'] = $filter->output.$grid->output;
		$data['titulo'] = $this->rapyd->get_head().'<center><h2>CONSULTA DE FACTURAS DESDE LA FECHA '.$filter->fechad->value.' HASTA '.$filter->fechah->value.'</h2></center>';
		$this->layout->buildPage('ventas/view_ventas', $data);
	}
}
?>
