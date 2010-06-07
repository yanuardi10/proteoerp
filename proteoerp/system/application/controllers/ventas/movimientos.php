<?php
class movimientos extends Controller {  
  
	function movimientos() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');

	}  
	function index(){
		redirect ('ventas/movimientos/diarios');
	}
	function diarios(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->load("datatable");
		$this->load->library('msql');
		
		$base_process_uri   = $this->rapyd->uri->implode_uri("base_uri","gfid","orderby");
		
		$filter = new DataForm('');
		$filter->_process_uri  = $this->rapyd->uri->add_clause($base_process_uri, "search");
		$filter->attributes=array('onsubmit'=>'is_loaded()');
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";
		$filter->fechah->group = "Fecha";
		$filter->fechad->group = "Fecha";
		$filter->fechad->rule = "required";
		$filter->fechah->rule = "required";
		
		$filter->submit("btnsubmit","Buscar");
		$filter->build_form();

		if($this->rapyd->uri->is_set("search")){

  		$fechad=$filter->fechad->newValue;
  		$fechah=$filter->fechah->newValue;
  				  								
			$grid = new DataGrid("Resultados de Clientes");
			$select=array("fecha","tipo_doc","numero","cod_cli as codigo","nombre","monto","impuesto","abonos","banco","tipo_ref as tipo","num_ref as numche");  
			$grid->db->select($select);
			$grid->db->from("smov");
			$grid->db->where('fecha >= ',$fechad);  
			$grid->db->where('fecha <= ',$fechah);  
    	
    	$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
			$grid->column("Tipo","tipo_doc");
			$grid->column("Numero","numero");
			$grid->column("Codigo","codigo");
			$grid->column("Nombre","nombre");
			$grid->column("Monto","<nformat><#monto#></nformat>","align=right");
			$grid->column("Impuesto","<nformat><#impuesto#></nformat>","align=right");
			$grid->column("Abonos","<nformat><#abonos#></nformat>","align=right");
			$grid->column("Banco","banco");
			$grid->column("Doc.","tipo");
			$grid->column("Num.Doc.","numche");

			$grid->build();		
			$cliente=$grid->output;
			//echo $grid->db->last_query(); 
			
			$grid2 = new DataGrid("Resultados de Proveedores");
			$select=array("fecha","tipo_doc","numero","cod_prv as codigo","nombre","monto","impuesto","abonos","banco","tipo_op as tipo","numche");  
			$grid2->db->select($select);
			$grid2->db->from("sprm");
			$grid2->db->where('fecha >= ',$fechad);  
			$grid2->db->where('fecha <= ',$fechah);  
    	
    	$grid2->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
			$grid2->column("Tipo","tipo_doc");
			$grid2->column("Numero","numero");
			$grid2->column("Codigo","codigo");
			$grid2->column("Nombre","nombre");
			$grid2->column("Monto","<nformat><#monto#></nformat>","align=right");
			$grid2->column("Impuesto","<nformat><#impuesto#></nformat>","align=right");
			$grid2->column("Abonos","<nformat><#abonos#></nformat>","align=right");
			$grid2->column("Banco","banco");
			$grid2->column("Doc.","tipo");
			$grid2->column("Num. Doc.","numche");

			$grid2->build();		
			$proveedor=$grid2->output;							
 		}else{ 
 			$cliente='';
 			$proveedor='';	
 		}
	
		$data['content'] = $filter->output.$cliente.$proveedor;
  	$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").phpscript('nformat.js').$this->rapyd->get_head();		
		$data['title']   = $this->rapyd->get_head()."<h1>Movimientos</h1>";
		$this->load->view('view_ventanas', $data);
	} 
}   
?>  