<?php
class sitemslog extends Controller {
	
	function sitemslog(){
		parent::Controller(); 
		$this->load->library("rapyd");
		
	}
	function index(){
		redirect("supervisor/sitemslog/filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
		$atts = array(
          'width'      => '800',
          'height'     => '600',
          'scrollbars' => 'yes',
          'status'     => 'yes',
          'resizable'  => 'yes',
          'screenx'    => '0',
          'screeny'    => '0'
        );

		$filter = new DataFilter("Filtro de Busqueda",'sitemslog');
    		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->size=$filter->fechah->size=12;
		$filter->fechad->insertValue = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-30,   date("Y")));
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->cajero = new  dropdownField("Cajero","cajero");
		$filter->cajero->option("",'Todos');
		$filter->cajero->options("Select cajero, nombre as value from scaj ");    
		$filter->cajero->style='width:150px;';
		
		$filter->vendedor = new  dropdownField("Vendedor","vendedor");
		$filter->vendedor->option("",'Todos');
		$filter->vendedor->options("Select vendedor, nombre from vend ");    
		$filter->vendedor->style='width:150px;';
		
		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size = 15;
		$filter->numero->maxlength=15;
		
		$filter->buttons("reset","search");
		$filter->build();
    
		if($this->rapyd->uri->is_set("search") AND $filter->is_valid()){
			$grid = new DataGrid("Resultados");                       
			$grid->per_page = 15;
			
			//$uri = anchor_popup('supervisor/sitemslog/detalle/<#id#>','<#numa#>',$atts);
    	//
			//$grid->column("Tipo","tipo");
			//$grid->column("Numero",$uri);
			//$grid->column("Fecha","<b><dbdate_to_human><#fecha#></dbdate_to_human></b>",'fecha',"align='center'");
			//$grid->column("Cajero","cajero");
			//$grid->column("Vendedor","vendedor");			
			//$grid->column("Cliente","cod_cli");
			//$grid->column("Hora","<#hora#>",'hora',"align='center'");
			
			$grid->column("Tipoa","tipo","align=left");
			$grid->column("Numero","<b><#numa#></b>","align=left");
			$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
			$grid->column("Codigo","codigoa","align=left");
			$grid->column("Descripcion","desca","align=left");
			$grid->column("Cantidad","<number_format><#cana#></number_format>","align=right");
			$grid->column("Precio","<number_format><#preca#>|2</number_format>","align=right");
			$grid->column("Iva","<number_format><#iva#>|2</number_format>","align=right");
			$grid->column("Total","<number_format><#tota#>|2</number_format>","align=right");
			$grid->column("Detalle","detalle","align=right");
			$grid->column("combo","combo","align=right");
			$grid->column("Bonifica","<number_format><#bonifica#>|2</number_format>","align=right");
			$grid->column("Costo","<number_format><#costo#>|2</number_format>","align=right");
			$grid->column("Usuario","usuario");
			$grid->column("Hora","hora");
			$grid->column("Usuario","usuario");
			    	    		
			$grid->build();
			$tabla=$grid->output;
			//echo $grid->db->last_query();
		}else{
			$tabla='';
		} 
		  
		$data['content'] = $filter->output.$tabla;
		$data['title']   = "<h1>Bitacora de Facturación</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function detalle($numero=''){ 
		$this->rapyd->load("datagrid2");

		$grid = new DataGrid2("Detalle");
		$grid->db->select(array('codigoa','desca','cana','preca','iva','tota','detalle','combo','bonifica','costo'));
		$grid->db->from('sitemslog');
		$grid->db->where('id',$numero);	
		$grid->per_page=20;

		$grid->column("Codigo","codigoa","align=left");
		$grid->column("Codigo","codigoa","align=left");
		$grid->column("Descripcion","desca","align=left");
		$grid->column("Cantidad","cana","align=center");
		$grid->column("Precio","preca","align=right");
		$grid->column("Iva","iva","align=right");
		$grid->column("Total","tota","align=right");
		$grid->column("Detalle","detalle","align=right");
		$grid->column("combo","combo","align=right");
		$grid->column("Bonifica","bonifica","align=right");
		$grid->column("Costo","costo","align=right");
	
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $grid->output;
		$data['title']   = "<h1>Detalle</h1>";
		$data["head"]    = $this->rapyd->get_head();	
		$this->load->view('view_ventanas', $data);		
	}
	
}
?>
