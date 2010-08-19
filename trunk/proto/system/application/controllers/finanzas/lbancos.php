<?php
class Lbancos extends Controller {

	function Lbancos() {
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index() {
		$this->load->library('PDFReporte');
		$this->rapyd->load("datafilter");
		
		$filter = new DataFilter("Filtro de listado de bancos");
		$filter->db->select("codbanc,numcuent,tbanco,CONCAT_WS(' ',dire1,dire2) direccion, banco, saldo");
		$filter->db->from('banc');
		$filter->db->orderby('tbanco');
		$filter->banco = new inputField("Banco", "banco");
		$filter->buttons("search");
		$filter->build();
		
		if($this->rapyd->uri->is_set("search")){
			$pdf = new PDFReporte($this->rapyd->db->_compile_select());
			$pdf->setHeadValores('TITULO1');
			$pdf->setSubHeadValores('TITULO2','TITULO3');
			$pdf->setTitulo("Listado de Bancos");
			$pdf->setSubTitulo($_POST['banco']);
			$pdf->setHeadGrupo($label='Codigo de Banco: ');
			$pdf->AddPage();
			$pdf->setTableTitu(8,'Times');
			$pdf->AddCol('codbanc'  ,10,'Cod.'     ,'C',5);
			$pdf->AddCol('numcuent' ,30,'N.Cuenta', 'C',5);
			$pdf->AddCol('tbanco'   ,15,'C.Banco'  ,'C',5);
			$pdf->AddCol('direccion',80,'Direccion','L',5);
			$pdf->AddCol('banco'    ,30,'Banco'    ,'L',5);
			$pdf->AddCol('saldo'    ,20,'Saldo'    ,'R',5);
			$pdf->setTotalizar('saldo');
			$pdf->setGrupo('tbanco');
			$pdf->Table();
			
			$template='hola pecueca <#Campo#> fin de la pecueca';
			echo substr($template,strpos($template,"<#")+2,strpos($template,"#>")-strpos($template,"<#")-2);
			//$pdf->Output();
		}else{
			$data["crud"]   = $filter->output;
			$data["titulo"] = '';
    	
			$content["content"]   = $this->load->view('rapyd/crud', $data, true);
			$content["rapyd_head"] = $this->rapyd->get_head();
			$content["code"] = '';
			$content["lista"] = "
				<div class='line'></div>
				<a href='#' onclick='window.close()'>Cerrar</a>
				<div class='line'></div>\n<br><br><br>\n";
			$this->load->view('rapyd/tmpsolo', $content);
		}
	}
}
?>
