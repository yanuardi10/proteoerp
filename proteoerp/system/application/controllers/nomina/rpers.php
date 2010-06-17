<?php
class  rpers extends Controller {
	function rpers(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
	function index(){

		$this->rapyd->load("dataform","datatable",'datagrid');
		$this->load->library('table');
		
		$base_process_uri= $this->rapyd->uri->implode_uri("base_uri","gfid","orderby");
		
		$filter = new DataForm($this->rapyd->uri->add_clause($base_process_uri, "search"));
		$filter->title('Elija una tabla');
		$filter->attributes=array('onsubmit'=>'is_loaded()');
				
		$filter->tabla=new dropdownField("Tabla","tabla");
		$filter->tabla->option("sinv","Inventario");
		$filter->tabla->option("scli","Clientes");
		$filter->tabla->option("sprv","Proveedores");
		$filter->tabla->clause="";
		
		//$filter->obra = new dropdownField("Obra", "depto"); 
		//$filter->obra->db_name='depto';
		//$filter->obra->clause="where"; 
		//$filter->obra->option(" ","Todos");  
		//$filter->obra->options("SELECT depto, descrip FROM dpto ORDER BY depto ");  
		//$filter->obra->operator="=";
		
		//$filter->status = new dropdownField("Status","status");
		//$filter->status->option("","Todos");
		//$filter->status->option("A","Activos");
		//$filter->status->option("I","Inactivos");
		//$filter->status->style='width:100px';
			
		$filter->submit("btnsubmit","Descargar");
		$filter->build_form();

		if($this->rapyd->uri->is_set("search")){
				    
			   $table=$filter->tabla->newValue; 
			
					$mSQL="DESCRIBE $table";			
					$query = $this->db->query($mSQL);		            
					if ($query->num_rows() > 0){                    
						foreach ($query->result_array() as $row){     
							$data[]=$row;                               
						}                                             
					}
					
					function ractivo($field){
						$data2 = array(
						    'name'        => 'campos[]',
						    'id'          => 'c'.$field,
						    'value'       => $field,
						    'checked'     => FALSE,
						    'style'       => 'margin:5px',
						   );
						
						$retorna = form_checkbox($data2);
						return $retorna ;
					}                                            
												
					$grid = new DataGrid("Resultados",$data);
					$grid->use_function('ractivo');                       
		    	
					$grid->column("Campos"    , 'Field'); 
					$grid->column("Mostrar", "<ractivo><#Field#></ractivo>",'align="center"');      

		$grid->build();
		$tabla=$grid->output;		
		$filter->build_form();	
		$conten=$filter->output.form_open("nomina/rpers/crear/$table").$tabla.form_submit('mysubmit', 'Generar').form_close();			
	}else{
		$conten=$filter->output;			
	}
	$data['content'] = $conten;
	$data['title']   = "<h1>Reporte</h1>";
	$data["head"]    = script("jquery.js").$this->rapyd->get_head();
	$this->load->view('view_ventanas', $data);	
}	
	function crear($table=''){
		//echo $obra;
		$this->load->library('encrypt');
		
		$campos=$this->input->post('campos');
			
		$line='SELECT ';
		foreach ($campos as $key => $val) {
			$line.=$val.",";
		} 
		$line=substr($line,0,-1);
		$line.=" FROM $table";		
		//echo $obra;          
		//echo $line;
		                 
		$mSQL = $this->encrypt->encode($line);
		  
		$generar="<form action='/../../proteoerp/xlsauto/repoauto2/'; method='post'>
 		<input size='100' type='hidden' name='mSQL' value='$mSQL'>
 		<input type='submit' value='Descargar a Excel' name='boton'/>
 		</form>";
 		  
 		$data['content'] = $generar;
		$data['title']   = "<h1>Reporte</h1>";
		$data["head"]    = script("jquery.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
		  
	}	   
}     
?>