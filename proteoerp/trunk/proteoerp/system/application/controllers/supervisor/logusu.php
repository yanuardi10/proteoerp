<?php
class logusu extends Controller {
	
	function logusu(){
		parent::Controller(); 
		$this->load->library("rapyd");
		
	}
	
	function index(){
		redirect("supervisor/logusu/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro de Busqueda",'logusu');
		//$filter->db->select(array());
    
		$filter->usuario = new  dropdownField("Usuario","usuario");
		$filter->usuario->option('','Todos');
		$filter->usuario->options("Select usuario, usuario as value from logusu group by usuario");    
		$filter->usuario->style='width:150px;';
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->size=$filter->fechah->size=12;
		$filter->fechad->insertValue = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-30,   date("Y")));
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<="; 

		$filter->modulo = new inputField("M&oacute;dulo","modulo");
		$filter->modulo->size=6;
		
		$filter->referencia = new inputField("Referencia","comenta");
		$filter->referencia->size=60;
		
		$filter->buttons("reset","search");
		$filter->build();
    
		$tabla='';
		
		if($this->rapyd->uri->is_set("search") AND $filter->is_valid()){
			
			$grid = new DataGrid("Resultados");                       
			$grid->per_page = 15;
    	
			$grid->column_orderby("Usuario","usuario",'usuario' );
			$grid->column_orderby("Fecha","<b><dbdate_to_human><#fecha#></dbdate_to_human></b>",'fecha',"align='center'");
			$grid->column_orderby("Hora","<#hora#>",'hora',"align='center'");
			$grid->column_orderby("M&oacute;dulo","modulo",'modulo');
			$grid->column_orderby("Acci&oacute;n","comenta",'comenta');
    	    		
			$grid->build();
 			//echo $grid->db->last_query();
			
			$SQL=$grid->db->last_query();
			$mSQL_1=$this->db->query($SQL);
			$row = $mSQL_1->row();
			
			IF($mSQL_1->num_rows()>0){
				$tabla=$grid->output;	
			}else{
				$tabla='No existen registros';
			}
		}
		
		$data['content'] = $filter->output.$tabla;
		$data['title']   = "<h1>Log de Usuarios</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
?>
