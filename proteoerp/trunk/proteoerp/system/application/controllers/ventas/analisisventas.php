<?php
class Analisisventas extends Controller {

	function Analisisventas(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->load->library('calendar');		
		//$this->datasis->modulo_id('50C',1);
		$this->rapyd->config->set_item("theme","repo");
	}

	function index() {
		redirect("ventas/analisisventas/dias");
	}
	function dias() {
		$this->rapyd->load("datagrid2","dataform");
		
		$fechad=$this->uri->segment(4);		
		if (empty($fechad)){
			$fechad=date("Y-m-d");
			$date = new DateTime();
			$date->setDate(substr($fechad, 0, 4),substr($fechad, 5, 2),substr($fechad, 8,2));
			$date->modify("-6 month");
			$fechad=$date->format("j-n-Y");			
		}
		$fechah=$this->uri->segment(5);
		if (empty($fechah))$fechah=date("d-m-Y");

		$filter = new DataForm();
		$filter->title('Filtro de Analisis de Gastos');
		$filter->fechad = new dateonlyField("Desde", "fechad",'d-m-Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d-m-Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue=$fechad;
		$filter->fechah->insertValue=$fechah;
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('/ventas/analisisventas/dias'),array('fechad','fechah')), $position="BL");
		$filter->build_form();
		
		$link="ventas/analisisventas/dia/<#fecha#>";
		$grid = new DataGrid2("Ventas Por D&iacute;as");
		$grid->column("Fecha", anchor($link,"<dbdate_to_human><#fecha#></dbdate_to_human>"),"align=center");		
		$grid->column("Ventas", "<number_format><#ventas#>|2|,|.</number_format>",'align=right');
		$grid->column("Anulaciones", "<number_format><#anulaciones#>|2|,|.</number_format>",'align=right');
		$select=array("fecha", "sum(tota*(tipoa<>'X')) as ventas","sum(tota*(tipoa='X')) as anulaciones");
		$grid->db->select($select);
		$grid->db->from('sitems');
		$fechad2=substr($fechad,6,4).substr($fechad,3,2).substr($fechad,0,2);
		$fechah2=substr($fechah,6,4).substr($fechah,3,2).substr($fechah,0,2);
		$grid->db->where('fecha >= ', $fechad2);  
		$grid->db->where('fecha <= ',$fechah2);  
		$grid->db->groupby("fecha");
		$grid->build();
		
		//echo $grid->db->last_query();
		
		$data['content'] = $filter->output.'<div style="overflow: auto; width: 100%;">'.$grid->output.'</div>';
		$data['title']   = "<h1>Analisis de Ventas</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function dia(){
		$this->rapyd->load("datagrid2");

		$fecha=$this->uri->segment(4);
		if (empty($fecha))$fecha=date("d-m-Y");
		
		$grid = new DataGrid2("Ventas del D&iacute;a $fecha");
		$grid->column("Tipo", "<#tipo_doc#>","align=left");
		$grid->column("Numero", "<#numero#>","align=left");
		$grid->column("Cliente", "<#cod_cli#>","align=left");
		$grid->column("Nombre", "<#nombre#>","align=left");		
		$grid->column("Sub-Total", "<number_format><#totals#>|2|,|.</number_format>",'align=right');		
		$grid->column("I.V.A.", "<number_format><#iva#>|2|,|.</number_format>",'align=right');
		$grid->column("Total", "<number_format><#totalg#>|2|,|.</number_format>",'align=right');
		
		$select=array("tipo_doc","numero","cod_cli","nombre","totals","iva","totalg");
		$grid->db->select($select);
		$grid->db->from('sfac');
		echo $fecha;
		//$fecha2=substr($fecha,8,2).substr($fecha,6,2).substr($fecha,0,2);
		$grid->db->where('fecha',$fecha);		
		//$grid->db->order('hora');
		$grid->build();
		
		echo $grid->db->last_query();
		
		$data['content'] = '<div style="overflow: auto; width: 100%;">'.$grid->output.'</div>';
		$data['title']   = "<h1>Analisis de Ventas</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}