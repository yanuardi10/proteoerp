<?php
class estados extends Controller {

	function estados(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
	function index()
	{
		redirect("ventas/estados/filteredgrid");
	}
	function filteredgrid(){
			
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro");
		$select=array("a.codigo","a.nombre","b.nombre as nombrez");		
		$filter->db->select($select);
		$filter->db->from("estado AS a");
		$filter->db->join("zona AS b","b.codigo=a.zona");
		
		$filter->codigo = new inputField("Codigo", "codigo");
		$filter->codigo->size=15;
			
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=25;
		
		$filter->pais = new dropdownField("Pa&iacute;s","pais");
		$filter->pais->style = "width:150px";
		$filter->pais->option("","Seleccionar");
		$filter->pais->options("SELECT codigo, nombre FROM pais ORDER BY codigo");
		$filter->pais->group = "Ubicaci&oacute;n";
		$filter->pais->onchange = "get_zona();";
		
		$filter->zona = new dropdownField("Zona", "zona");
		$filter->zona->style = "width:150px";
		$filter->zona->option("","Seleccionar");
		$filter->zona->options("SELECT codigo, nombre FROM zona ORDER BY nombre");
		$filter->zona->group = "Ubicaci&oacute;n";
					
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/estados/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Estados");
		$grid->order_by("a.nombre","asc");
		$grid->per_page = 15;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre");
		$grid->column("Zona","nombrez");

		$grid->add("ventas/estados/dataedit/create");
		$grid->build();
		
		$link2=site_url('ventas/sclifyco/get_zona');
		$data['script']  =<<<script
		<script type="text/javascript" charset="utf-8">
		function get_zona(){
			var pais=$("#pais").val();
			$.ajax({
				url: "$link2"+'/'+pais,
				success: function(msg){
					$("#td_zona").html(msg);
					//alert(pais);
				}
			});
			get_estados();
		}
		</script>
script;
		

	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Estados</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["head"]   .= $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function get_zona($pais=null){
		$this->rapyd->load("fields");
		
		$zona = new dropdownField("Zona","zona");
		$zona->option("","Seleccione una Zona");
		$zona->status = "modify";
		$zona->options("SELECT codigo, nombre FROM zona WHERE pais='$pais' ORDER BY codigo");
		$zona->style="width:350px";
		$zona->build();
		
		echo $zona->output;
	}
	function dataedit()
	{ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Agregar", "estado");
		$edit->back_url = site_url("ventas/estados/filteredgrid");
		
		$edit->pais = new dropdownField("Pa&iacute;s","pais");
		$edit->pais->style = "width:150px";
		$edit->pais->option("","Seleccionar");
		$edit->pais->options("SELECT codigo, nombre FROM pais ORDER BY codigo");
		$edit->pais->group = "Ubicaci&oacute;n";
		$edit->pais->onchange = "get_zona();";
		$edit->pais->when=array('create','modify');
		
		$edit->zona = new dropdownField("Zona", "zona");
		$edit->zona->style = "width:150px";
		if($edit->_status=='modify'){ 
			//$pais =$edit->_dataobject->get("pais");
			$edit->zona->options("SELECT codigo, nombre FROM estado ORDER BY codigo");
		}else{
			$edit->zona->option("","Seleccione una Zona");
		}	
		$edit->zona->option("","Seleccionar");
		$edit->zona->options("SELECT codigo, nombre FROM zona ORDER BY nombre");
		$edit->zona->group = "Ubicaci&oacute;n";
					
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size=38;
		$edit->nombre->rule="trim|required|strtoupper";
		$edit->nombre->maxlength=30;
		  
		$edit->buttons("modify", "save", "undo", "delete", "back");
  	$edit->build();
  	
  	$link2=site_url('ventas/sclifyco/get_zona');
		$data['script']  =<<<script
		<script type="text/javascript" charset="utf-8">
		function get_zona(){
			var pais=$("#pais").val();
			$.ajax({
				url: "$link2"+'/'+pais,
				success: function(msg){
					$("#td_zona").html(msg);
					//alert(pais);
				}
			});
			get_estados();
		}
		</script>
script;
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Estados</h1>";        
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["head"]   .= $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}
?>