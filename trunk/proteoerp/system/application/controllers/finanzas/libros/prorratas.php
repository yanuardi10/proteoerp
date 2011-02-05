<?php
class prorratas{
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


}