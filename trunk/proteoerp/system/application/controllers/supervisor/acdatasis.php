<?php
class acdatasis extends Controller {
	
	function acdatasis(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
	
	function index(){
		redirect("supervisor/acdatasis/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
	 $pers=array(
		'tabla'   =>'usuario',
		'columnas'=>array(
		'us_codigo'  =>'Codigo',
		'us_nombre'  =>'Nombre'),
		'filtro'  =>array('us_codigo'=>'C&oacute;digo'),
		'retornar'=>array('us_codigo'=>'value'),
		'titulo'  =>'Buscar Usuario');
					  
		$boton=$this->datasis->modbus($pers);
	
		function ractivo($acceso,$codigo){
		 if($acceso=='S'){
				$retorna = form_checkbox($codigo, 'accept', TRUE);
			}else{
				$retorna = form_checkbox($codigo, 'accept',FALSE);
			}
			return $retorna ;
		}
	
		$filter = new DataFilter("Filtro de Busqueda");
		$filter->db->select(array("b.modulo",'b.codigo',"a.usuario","a.usuario as value","a.acceso","b.titulo")); 
		$filter->db->from("sida AS a");   
		$filter->db->join("tmenus AS b","a.modulo=b.codigo");
		$filter->db->orderby("b.modulo");
		
		$filter->usuario = new  dropdownField("Usuario","usuario");
		$filter->usuario->options("Select us_codigo as value,CONCAT_WS('  - ', us_codigo, us_nombre) as codigo from usuario group by us_codigo ORDER BY us_nombre");    
		$filter->usuario->style='width:250px;';
		
		//$filter->copiar = new inputField("Copiar de","value");
		//$filter->copiar->append($boton);
		//$filter->copiar->size=15;
		//$filter->copiar->db_name ="a.usuario";
		//$filter->copiar->operator="=";
		//$filter->copiar->option("","Ninguno");
		//$filter->copiar->options("Select us_codigo as value,CONCAT_WS('  - ', us_codigo, us_nombre) as codigo from usuario group by us_codigo");    
		//$filter->copiar->style='width:250px;';
			
		$filter->buttons("reset","search");
		$filter->build();
		  
		if($this->rapyd->uri->is_set("search") AND $filter->is_valid()){
     
      $usr=$filter->usuario->newValue;  
			$mSQL="INSERT IGNORE INTO sida SELECT '$usr',b.codigo,'N'  FROM sida AS a RIGHT JOIN tmenus AS b ON a.modulo=b.codigo AND a.usuario='$usr' WHERE a.modulo IS NULL";
			$this->db->simple_query($mSQL);
			$copiar=anchor("supervisor/acdatasis/copia/$usr/",'Copiar Acceso de otro Usuario');
			
			$grid = new Datagrid("Resultados");
		  $grid->use_function('ractivo');
			$link=site_url('/supervisor/acdatasis/activar');
			//$grid->per_page = 15;
    	
			$grid->column("M&oacute;dulo","modulo");
			$grid->column("Nombre","titulo");
		  $grid->column("Acceso", "<ractivo><#acceso#>|<#codigo#>|</ractivo>",'align="center"');
			$grid->build(); 			
			$tabla=$copiar.$grid->output;
			//echo $grid->db->last_query();
			
			$url=site_url('supervisor/acdatasis/activar');
			$data['script']='<script type="text/javascript">
			$(document).ready(function() {
				$("form :checkbox").click(function () {
    	       usr=$("#usuario").attr("value");
    	       $.ajax({
						  type: "POST",
						  url: "'.$url.'",
						  data: "codigo="+this.name+"&usuario="+usr,
						  success: function(msg){
						  	if (msg==0)
						    	alert("Ocurrio un problema");
						  }
						});
    	    }).change();
			});
			</script>';
		}else{
			$tabla='';
		}
		
		$data['content'] = $filter->output.form_open('').$tabla.form_close();
		$data['title']   = "<h1>Acceso de Usuario en DataSIS</h1>";
		$data["head"]    = script("jquery.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function activar(){
		$usuario=$this->input->post('usuario');
		$codigo=$this->input->post('codigo');
		$mSQL = "UPDATE sida SET acceso=IF(acceso='S','N','S') WHERE modulo=$codigo AND usuario = '$usuario'";
		echo $this->db->simple_query($mSQL);
	}
	function copia($usua=null){
		$this->rapyd->load("datafilter","datagrid");
		$usuario=$usua;
		
		function ractivo($acceso,$codigo){
		 if($acceso=='S'){
				$retorna = form_checkbox($codigo, 'accept', TRUE);
			}else{
				$retorna = form_checkbox($codigo, 'accept',FALSE);
			}
			return $retorna ;
		}
	
		$filter = new DataFilter("");
		$filter->db->select(array("b.modulo",'b.codigo',"a.usuario","a.usuario as value","a.acceso","b.titulo")); 
		$filter->db->from("sida AS a");   
		$filter->db->join("tmenus AS b","a.modulo=b.codigo");
		$filter->db->orderby("b.modulo");
		
		$filter->usuario = new  dropdownField("Copiar de","usuario");
		$filter->usuario->options("Select us_codigo as value,CONCAT_WS('  - ', us_codigo, us_nombre) as codigo from usuario group by us_codigo");    
		$filter->usuario->style='width:250px;';
				
		$filter->buttons("reset","search");
		$filter->build();
    
		if($this->rapyd->uri->is_set("search") AND $filter->is_valid()){
      $usr=$filter->usuario->newValue;
			$mSQL="INSERT IGNORE INTO sida SELECT '$usr',b.codigo,'N'  FROM sida AS a RIGHT JOIN tmenus AS b ON a.modulo=b.codigo AND a.usuario='$usr' WHERE a.modulo IS NULL";
			$this->db->simple_query($mSQL);
			$copiar=anchor("supervisor/acdatasis/copiar/$usr/$usuario",'Copiar');
			
			$grid = new Datagrid("Resultados");
		  $grid->use_function('ractivo');
			$link=site_url('/supervisor/acdatasis/activar');
			//$grid->per_page = 15;
    	
			$grid->column("M&oacute;dulo","modulo");
			$grid->column("Nombre","titulo");
		  $grid->column("Acceso", "<ractivo><#acceso#>|<#codigo#>|</ractivo>",'align="center"');
			$grid->build(); 			
			$tabla=$copiar.$grid->output;              
			//echo $grid->db->last_query();
			
			$url=site_url('supervisor/acdatasis/activar');
			$data['script']='<script type="text/javascript">
			$(document).ready(function() {
				$("form :checkbox").click(function () {
    	       usr=$("#usuario").attr("value");
    	       $.ajax({type: "POST",
    	       url: "'.$url.'",
    	       data: "codigo="+this.name+"&usuario="+usr,
    	       success: function(msg){
						  	if (msg==0)
						    	alert("Ocurrio un problema");
						  }
						});
    	    }).change();
			});
			</script>';
		}else{
			$tabla='';
		}

		$data['content'] = ' Usuario:'.$usuario.$filter->output.form_open('').$tabla.form_close();
		$data['title']   = "<h1>Copiar Accesos de Usuario en DataSIS</h1>";
		$data["head"]    = script("jquery.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function copiar($usr,$usuario){
		$mSQL_1 = "DELETE FROM sida WHERE usuario = '$usuario'";
		$this->db->simple_query($mSQL_1);
		$mSQL_2 = "INSERT INTO `sida` (`usuario`,`modulo`,`acceso`) SELECT usuario='OTRO', modulo, acceso from sida WHERE usuario = '$usr'";
		$this->db->simple_query($mSQL_2);
		$mSQL_3 = "UPDATE sida SET usuario='$usuario' WHERE usuario='0'";
		$this->db->simple_query($mSQL_3);
    redirect("supervisor/acdatasis/filteredgrid/");
	}	
}
?>