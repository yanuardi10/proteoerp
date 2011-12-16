<?php
class distrito extends Controller {

	function distrito(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
	function index()
	{
		redirect("ventas/distrito/filteredgrid");
	}
		function filteredgrid(){
			
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro");
		$select=array("a.codigo","a.nombre","e.nombre as mestado","c.pais as pais","b.zona as zona","a.municipio as estado");		
		$filter->db->select($select);
		$filter->db->from("distrito AS a");
		$filter->db->join("municipio AS e","a.municipio=e.codigo");
		$filter->db->join("estado AS b","b.codigo=e.estado");
		$filter->db->join("zona AS c","c.codigo=b.zona");
		$filter->db->join("pais AS d","d.codigo=c.pais");
		
		$filter->codigo = new inputField("Codigo", "a.codigo");
		$filter->codigo->size=15;
			
		$filter->nombre = new inputField("Nombre", "a.nombre");
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
		$filter->zona->onchange = "get_estados();";
	
		$filter->estado = new dropdownField("Departamento","estado");
		$filter->estado->style = "width:150px";
		$filter->estado->option("","Seleccione una Zona");
		$filter->estado->options("SELECT codigo, nombre FROM estado ORDER BY codigo");
		$filter->estado->group = "Ubicaci&oacute;n";
		$filter->estado->onchange = "get_municipios();";
	
		$filter->municipios = new dropdownField("Provincia","municipio");                                
		$filter->municipios->style = "width:180px";                                               
		$filter->municipios->option("","Seleccione un Departamento");                                    
		$filter->municipios->options("SELECT codigo, nombre FROM municipio ORDER BY codigo");            
		$filter->municipios->group = "Ubicaci&oacute;n";                                               
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/distrito/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Distrito");
		$grid->order_by("nombre","asc");
		$grid->per_page = 15;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre");
		$grid->column("Provincia","mestado");

		$grid->add("ventas/distrito/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();	
		
		$link=site_url('ventas/scliperu/get_municipios');
		$link1=site_url('ventas/scliperu/get_estados');
		$link2=site_url('ventas/scliperu/get_zona');
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
		function get_estados(){
			var zona=$("#zona").val();
			$.ajax({
				url: "$link1"+'/'+zona,
				success: function(msg){
					$("#td_estado").html(msg);
					//alert(zona);
				}
			});
			get_municipios();
		}
		function get_municipios(){
			var estado=$("#estado").val();
			$.ajax({
				url: "$link"+'/'+estado,
				success: function(msg){
					$("#td_municipio").html(msg);
					//alert(estado);
				}
			});
		}
		</script>
script;
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Distrito</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["head"]   .= $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function dataedit()
	{ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Agregar", "distrito");
		$edit->back_url = site_url("ventas/distrito/filteredgrid");
		
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
			$edit->zona->options("SELECT codigo, nombre FROM estado ORDER BY codigo");
		}else{
			$edit->zona->option("","Seleccione una Zona");
		}	
		$edit->zona->option("","Seleccionar");
		$edit->zona->options("SELECT codigo, nombre FROM zona ORDER BY nombre");
		$edit->zona->group = "Ubicaci&oacute;n";
		$edit->zona->onchange = "get_estados();";
		$edit->zona->when=array('create','modify');
		
		$edit->estado = new dropdownField("Departamento","estado");
		$edit->estado->style = "width:150px";
		if($edit->_status=='modify'){
			$edit->estado->options("SELECT codigo, nombre FROM estado ORDER BY codigo");
		}else{
			$edit->estado->option("","Seleccione una Zona");
		}	
		$edit->estado->group = "Ubicaci&oacute;n";
		$edit->estado->when=array('create','modify');
		
		$edit->municipio = new dropdownField("Provincia","municipio");
		$edit->municipio->style = "width:150px";
		$edit->municipio->option("","Seleccione un Departamento");
		$edit->municipio->options("SELECT codigo, nombre FROM municipio ORDER BY nombre");
		$edit->municipio->group = "Ubicaci&oacute;n";
				
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size=60;
		$edit->nombre->rule="trim|required|strtoupper";
		$edit->nombre->maxlength=90;
		  
		$edit->buttons("modify", "save", "undo", "delete", "back");
  	$edit->build();
  	
		$link1=site_url('ventas/scliperu/get_estados');
		$link2=site_url('ventas/scliperu/get_zona');
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
		function get_estados(){
			var zona=$("#zona").val();
			$.ajax({
				url: "$link1"+'/'+zona,
				success: function(msg){
					$("#td_estado").html(msg);
					//alert(zona);
				}
			});
		}
		</script>
script;
		
		$data['content'] = $edit->output;
    $data['title']   = "<h1>Distrito</h1>";        
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["head"]   .= $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}
?>