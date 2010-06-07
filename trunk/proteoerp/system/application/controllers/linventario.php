<?php
class Linventario extends Controller {

	function Linventario() {
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index() {
		$this->load->library('PDFReporte');
		$this->rapyd->load("datafilter");
		
		//filter 
		$filter = new DataFilter("Filtro de listado de Inventario");
		$filter->db->select("a.codigo, a.descrip pdescrip, a.grupo, b.linea,c.depto,a.precio1,a.precio2,d.descrip, c.descrip dlinea,b.nom_grup");
		$filter->db->from('sinv a');
		$filter->db->join("grup b","a.grupo=b.grupo","LEFT");
		$filter->db->join("line c","b.linea=c.linea","LEFT");
		$filter->db->join("dpto d","d.depto=c.depto","LEFT");
		$filter->db->where('d.tipo','I');
		$filter->db->orderby('c.depto, b.linea,a.grupo');
		$filter->db->limit(500,0);
		$filter->descrip = new inputField("Descripcion", "descrip");
		$filter->descrip->db_name="a.descrip";
		$filter->buttons("search");
		$filter->build();
		
		if($this->rapyd->uri->is_set("search")){
			$mSQL=$this->rapyd->db->_compile_select();
			$pdf = new PDFReporte($mSQL);
			$pdf->setHeadValores('TITULO1');
			$pdf->setSubHeadValores('TITULO2','TITULO3');
			$pdf->setTitulo("Listado de Inventario");
			$pdf->setSubTitulo($_POST['descrip']);
			$pdf->setHeadGrupo('Departameto: ');
			$pdf->AddPage();
			$pdf->setTableTitu(8,'Times');
			$pdf->AddCol('codigo'  ,20,'Cod.'        ,'C',5);
			$pdf->AddCol('pdescrip',80,'Descripcion' ,'L',5);
			$pdf->AddCol('precio1' ,15,'Precio 1'    ,'R',5);
			$pdf->AddCol('precio2' ,15,'Precio 2'    ,'R',5);
			$pdf->AddCol('grupo'   ,10,'Grupo'       ,'R',5);
			$pdf->AddCol('linea'   ,10,'Linea'       ,'R',5);
			$pdf->AddCol('depto'   ,10,'Depto'       ,'R',5);
			$pdf->setTotalizar('precio1','precio2');
			$pdf->setGrupoLabel('Departamento: (<#depto#>) <#descrip#> ','Linea: (<#linea#>) <#dlinea#>','Grupo (<#grupo#>) <#nom_grup#>');
			$pdf->setGrupo('depto','linea','grupo');
			$pdf->Table();
			$pdf->Output();
			
		}else{
			$data["filtro"] = $filter->output;
			$data["titulo"] = '';
			$data["head"]   = $this->rapyd->get_head();
			$this->load->view('view_freportes', $data);
		}
	}
}
?>
