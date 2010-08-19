<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class metas extends Controller{
	
	function metas(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		redirect("ventas/metas/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
		$modbus=array(
		'tabla'   =>'vend',
		'columnas'=>array(
		'vendedor' =>'C&oacute;digo Vendedor',
		'nombre'=>'Nombre'),
		'filtro'  =>array('vendedor'=>'C&oacute;digo Vendedor','nombre'=>'Nombre'),
		'retornar'=>array('vendedor'=>'vendedor'),
		'titulo'  =>'Buscar Vendedor');
		
		$boton=$this->datasis->modbus($modbus);
		
    
		$filter = new DataFilter("Metas","metas"); 
		
		$filter->fecha = new dateonlyField("Fecha", "fecha",'m/Y');
		$filter->fecha->clause  ='where';
		$filter->fecha->db_name ="EXTRACT(YEAR_MONTH FROM fecha)";
		$filter->fecha->insertValue = date("Y-m-d");
		$filter->fecha->operator="=";
		$filter->fecha->dbformat='Ym';
		$filter->fecha->size=7;
		$filter->fecha->append(' mes/año');
		$filter->fecha->rule = "required";
		
		$filter->codigo = new dropdownField("Codigo", "manual");
		$filter->codigo->option("","");
		$filter->codigo->options("SELECT clave,clave as valor FROM sinv GROUP BY clave");
		$filter->codigo->style = "width:150px";	
		
		$filter->vendedor = new inputField("Vendedor","vendedor");
		$filter->vendedor->size = 10;
		$filter->vendedor->maxlength=10;
		$filter->vendedor->append($boton);
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/metas/dataedit/show/<#codigo#>/<#fecha#>/<#vendedor#>','<#fecha#>');
		
		$grid = new DataGrid("Lista de Metas");
		$grid->per_page=15;
	
		$grid->column("Fecha",$uri);
		$grid->column("Codigo","codigo");
		$grid->column("Cantidad","cantidad");
		$grid->column("Vendedor","vendedor");
		
		$grid->add("ventas/metas/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Metas</h1>";
		$data["head"]    = $this->rapyd->get_head();	
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$modbus=array(
		'tabla'   =>'vend',
		'columnas'=>array(
		'vendedor' =>'C&oacute;digo Vendedor',
		'nombre'=>'Nombre'),
		'filtro'  =>array('vendedor'=>'C&oacute;digo Vendedor','nombre'=>'Nombre'),
		'retornar'=>array('vendedor'=>'vendedor'),
		'titulo'  =>'Buscar Vendedor');
		
		$boton=$this->datasis->modbus($modbus);
		
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';
					
		$edit = new DataEdit("Metas","metas"); 
		
		$edit->back_url = site_url("ventas/metas/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->fecha = new dateonlyField("Fecha", "fecha",'m/Y');
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->dbformat='Ym';
		$edit->fecha->size=7;
		$edit->fecha->append('mes/año');
		$edit->fecha->rule = "required";
    
		$edit->codigo = new dropdownField("Codigo", "codigo");
		$edit->codigo->option("","");
		$edit->codigo->options("SELECT clave,clave as valor FROM sinv GROUP BY clave");
		$edit->codigo->style = "width:150px";	
		$edit->codigo->rule = "required";
		
		$edit->cantidad = new inputField("Cantidad","cantidad");
		$edit->cantidad->size =12;
		$edit->cantidad->maxlength =12;
		$edit->cantidad->rule="trim";
		$edit->cantidad->css_class='inputnum';
		
		$edit->vendedor = new inputField("Vendedor","vendedor");
		$edit->vendedor->size = 10;
		$edit->vendedor->maxlength=10;
		$edit->vendedor->append($boton);
				
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output; 		
		$data['title']   = "<h1>Metas</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function compara(){
		
		$this->rapyd->load("datafilter","datagrid");
		$this->db->_escape_char='';
		$this->db->_protect_identifiers=false;
		
		function colum($diferen){
			if ($diferen<0)
				return ('<b style="color:red;">'.$diferen.'</b>');
			else
				return ('<b style="color:green;">'.$diferen.'</b>');
		}
		
		function dif($a,$b){
			return number_format($a-$b,2,',','.');
		}

		$modbus=array(
		'tabla'   =>'vend',
		'columnas'=>array(
		'vendedor' =>'C&oacute;digo Vendedor',
		'nombre'=>'Nombre'),
		'filtro'  =>array('vendedor'=>'C&oacute;digo Vendedor','nombre'=>'Nombre'),
		'retornar'=>array('vendedor'=>'vendedor'),
		'titulo'  =>'Buscar Vendedor');
		
		$boton=$this->datasis->modbus($modbus);
						    
		$base_process_uri= $this->rapyd->uri->implode_uri("base_uri","gfid","orderby");
		$filter = new DataForm($this->rapyd->uri->add_clause($base_process_uri, "search"));
		$filter->title('Filtro');
		$filter->attributes=array('onsubmit'=>'is_loaded()');
		
		$filter->fecha = new dateonlyField("Fecha", "d.fecha",'m/Y');
		$filter->fecha->clause  ='where';
		$filter->fecha->insertValue = date("mY");
		$filter->fecha->operator="=";
		$filter->fecha->dbformat='Ym';
		$filter->fecha->size=7;
		$filter->fecha->append('mes/año');
		$filter->fecha->rule = "required";
		
	  $filter->public = new checkboxField("Agrupado por Vendedor", "public", "1","0");			
	  
		$filter->vendedor = new inputField("Vendedor","vendedor");
		$filter->vendedor->size = 10;
		$filter->vendedor->maxlength=10;
		$filter->vendedor->append($boton);
						
		$filter->submit("btnsubmit","Descargar");
    $filter->build_form();                   
    
		if($this->rapyd->uri->is_set("search") AND $filter->is_valid()){
		
			$fecha=$filter->fecha->newValue;	
			$vendedor=$filter->vendedor->newValue;	
			$agrupar=$filter->public->newValue;
			//ECHO 'AQUIII: '.$agrupar;
			 			
			$fechai=$fecha.'01';  
    	$fechaf=$fecha.'31';	
    	
    	$mSQL="(SELECT
			d.cantidad as metasv, b.clave, c.vd as vendedor, e.nombre as nombrev, 
			SUM(a.cana*b.peso*(d.tipo='P'))+ a.cana*(d.tipo='C')+ a.cana*(d.tipo='P')* (b.peso=0 OR b.peso IS NULL) AS ventas 
			FROM (sitems AS a) JOIN sinv AS b ON a.codigoa=b.codigo 
			JOIN sfac AS c ON a.tipoa=c.tipo_doc AND a.numa=c.numero 
			JOIN metas AS d ON b.clave=d.codigo AND d.fecha='201004' 
			AND c.vd=d.vendedor JOIN vend AS e ON d.vendedor=e.vendedor 
			WHERE a.fecha >= '20100401' AND a.fecha <= '20100431' 
			GROUP BY b.clave,c.vd) as h";
	
			$grid = new DataGrid("Resultados");
			$grid->use_function('colum');
			$select=array("h.clave as codigo","sum(h.metasv)as metas","sum(h.ventas)as ventas","sum(h.ventas)-sum(h.metasv)as diferen","h.vendedor","h.nombrev");
			$grid->db->select($select);
			$grid->db->from("$mSQL");   

			if(!empty($vendedor)){
				$grid->db->where("h.vendedor",$vendedor);
			}else{
				$grid->db->orderby("h.vendedor");
			}
			

			if($agrupar=='0'){
				$grid->db->groupby("h.clave");
			}else{
				$grid->db->groupby("h.clave,h.vendedor");
			}

		if($agrupar=='0'){
					$grid->column("Codigo","codigo" );
					$grid->column("Venta"  ,"<nformat><#ventas#></nformat>"      ,"align='right'");		
					$grid->column("Metas"  ,"<#metas#>","align='right'");	
					$grid->column("Diferencia" ,"<colum><nformat><#diferen#></nformat></colum>" ,"align='right'");
			}else{
				$grid->column("Codigo","codigo" );
				$grid->column("Vendedor"   ,"<#vendedor#>","align='center'");	
				$grid->column("Nombre"     ,"<#nombrev#>","align='left'");	
				$grid->column("Venta"      ,"<nformat><#ventas#></nformat>","align='right'");		
				$grid->column("Metas"      ,"<nformat><#metas#></nformat>","align='right'");	 	
				$grid->column("Diferencia" ,"<colum><nformat><#diferen#></nformat></colum>" ,"align='right'");		
			}
 		
			$grid->build();
			$tabla=$grid->output;
			//echo $grid->db->last_query();
		}else{
			$tabla='';
		}
		
		$data['content'] = $filter->output.$tabla;
		$data['title']   = "<h1>Estado de Metas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `metas` (
		  `codigo` varchar(15),
		  `cantidad` decimal(12,3),
		  `fecha` int(10)test,
		  `vendedor` varchar(5),
		  `tipo` CHAR(2),
		  PRIMARY KEY  (`fecha`,`codigo`,`vendedor`)
		)";
		$rt=$this->db->simple_query($mSQL);
		var_dump($rt);
	}
}
?>