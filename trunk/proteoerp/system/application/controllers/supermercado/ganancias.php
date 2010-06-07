<?php
class Ganancias extends Controller {  
    var $from;
    var $join; 
 
 function Ganancias() {
	  parent::Controller();
	  $this->load->library("rapyd");
	  $this->load->helper('openflash');
	  $this->datasis->modulo_id(124,1);
	  $this->from="costos AS a";
	  $this->join[0]=array('maes AS b','a.codigo=b.codigo','');
    $this->join[1]=array('grup AS c','b.grupo=c.grupo','');
    $this->join[2]=array('fami AS d','d.familia=c.familia','');
    $this->join[3]=array('dpto AS e','e.depto=d.depto','');
	}  
	function index(){
		redirect('/supermercado/ganancias/departamento');
	}
	function departamento(){
		$this->rapyd->load("datagrid2");	  
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');

		if($this->uri->segment(4)) $anio=$this->uri->segment(4); elseif(isset($_POST['anio']))$anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");

		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';

		$filter = new DataForm('supermercado/ganancias/departamento');
		$filter->title('Filtro de Ganancias');
		
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/ganancias/departamento'),array('anio')), $position="BL");
		$filter->build_form();

		$grid = new DataGrid2();
		$select=array("a.fecha","e.depto","e.descrip as nombre",
		"SUM(a.promedio*a.cantidad) AS costo",
		"SUM(a.venta) AS ventas",
		"SUM(a.venta)/COUNT(*)AS porcentaje",
		"SUM(a.venta)-SUM(a.promedio*a.cantidad) AS ganancias",
		"COUNT(*) AS numfac");

		$grid->db->select($select);  
		$grid->db->from($this->from);
		foreach ($this->join as $valor)
		$grid->db->join($valor[0],$valor[1],$valor[2]);
		$grid->db->where('e.tipo','I');
		$grid->db->where("a.origen IN ('3I','3M')");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);   
		$grid->db->groupby("d.depto");
		$grid->db->orderby("ganancias DESC");

		$grid->column("Departamento"    ,"nombre","align='left'");          
		$grid->column("Costo"           , "<number_format><#costo#>|2|,|.</number_format>",'align=right');
		$grid->column("supermercado"    , "<number_format><#ventas#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Ganancias"       , "<number_format><#ganancias#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("% supermercado"  , "<number_format><#porcentaje#>|2|,|.</number_format>",'align=right');
		$grid->column("Cant. Fact"      , "numfac",'align=right');

		$grid->totalizar('supermercado','ganancias','costo');
		$grid->build();

		$grafico = open_flash_chart_object(680,400, site_url("supermercado/ganancias/gdepartamento/$anio"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ganancias por Departamentos</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function familia($anio='',$departamento=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');

		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['depto']) AND empty($departamento)) $departamento=$_POST['depto'];		

		if(empty($anio) OR empty($departamento)) redirect("supermercado/ganancias/departamento/$anio");

		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';

		$dept=array(
		'tabla'   =>'dpto',
		'columnas'=>array(
		'depto' =>'C&oacute;digo Departamento',
		'descrip'  =>'Nombre'),
		'filtro'  =>array('depto'=>'C&oacute;digo Departamento','descrip'=>'Nombre'),
		'retornar'=>array('depto'=>'depto'),
		'titulo'  =>'Buscar Departamento');

		$dboton=$this->datasis->modbus($dept);

		$filter = new DataForm('supermercado/ganancias/familia');
		$filter->title('Filtro de Ganancias');

		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;

		$filter->departamento = new inputField("Departamento", "depto");
		$filter->departamento->size=10;
		$filter->departamento->insertValue=$departamento;
		$filter->departamento->rule = "max_length[4]"; 
		$filter->departamento->append($dboton); 

		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/ganancias/familia/'),array('anio','depto')), $position="BL");
		$filter->build_form();

		$grid = new DataGrid2();
		$select=array("a.fecha","e.depto","e.descrip","d.familia","d.descrip as nombre","c.grupo","c.nom_grup",
		"SUM(a.promedio*a.cantidad) AS costo",
		"SUM(a.venta) AS ventas",
		"SUM(a.venta)/COUNT(*)AS porcentaje",
		"SUM(a.venta)-SUM(a.promedio*a.cantidad) AS ganancias",
		"COUNT(*) AS numfac");

		$grid->db->select($select);  
		$grid->db->from($this->from);
		foreach ($this->join as $valor) 
 		$grid->db->join($valor[0],$valor[1],$valor[2]);				
		$grid->db->where("a.origen IN ('3I','3M')");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->where('e.depto',$departamento); 
		$grid->db->groupby("d.familia");
		$grid->db->orderby("ganancias DESC");

		$grid->column("familias"     	, "nombre","align='left'");          
		$grid->column("Costo"        	, "<number_format><#costo#>|2|,|.</number_format>",'align=right');
		$grid->column("supermercado" 	, "<number_format><#ventas#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Ganancias"    	, "<number_format><#ganancias#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("% supermercado", "<number_format><#porcentaje#>|2|,|.</number_format>"  ,'align=right');		
		$grid->column("Cant. Fact" 		, "numfac",'align=right');

		$grid->totalizar('supermercado','ganancias','costo');
		$grid->build();

		$grafico = open_flash_chart_object(680,450, site_url("supermercado/ganancias/gfamilia/$anio/$departamento"));
		$data['content']  =$grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ganancias por familias</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function grupo($anio='',$departamento='',$familia=''){
	  $this->rapyd->load("datagrid2");
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');

		rapydlib("prototype");
    $ajax_onchange = '
	  function get_familia(){
	    var url = "'.site_url('reportes/sinvfamilias').'";
	    var pars = "dpto="+$F("depto");
	    var myAjax = new Ajax.Updater("td_familia", url, { method: "post", parameters: pars });
	    get_grupo();
	  }
	  function get_grupo(){
	    var url = "'.site_url('reportes/sinvgrupos').'";
	    var pars = "dpto="+$F("depto")+"&familia="+$F("familia");
	    var myAjax = new Ajax.Updater("td_grupo", url, { method: "post", parameters: pars });
	  }
	  ';

		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['depto']) AND empty($departamento)) $departamento=$_POST['depto'];		
		if(isset($_POST['familia']) AND empty($familia)) $familia=$_POST['familia'];

		if(empty($anio) AND empty($departamento)) redirect('supermercado/ganancias/departamento/');
    if(empty($familia)) redirect("supermercado/ganancias/familia/$anio/$departamento");

		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';

		$filter = new DataForm('supermercado/ganancias/grupo');
		$filter->script($ajax_onchange);
		$filter->title('Filtro de Ganancias');

		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 

		$filter->departamento = new dropdownField("Departamento", "depto");
		$filter->departamento->insertValue=$departamento;     
		$filter->departamento->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto"); 
    $filter->departamento->onchange = "get_familia();";

		$filter->familia = new dropdownField("familia", "familia");
		$filter->familia->insertValue=$familia;
		$filter->familia->options("SELECT familia, descrip FROM line WHERE depto='$departamento'");     

		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/ganancias/grupo/'),array('anio','depto','familia')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.fecha","e.depto","e.descrip AS ndepartamento","d.familia","d.descrip AS nfamilia","b.grupo AS grupo","b.descrip","c.nom_grup as nombre",
		"SUM(a.promedio*a.cantidad) AS costo",
		"SUM(a.venta) AS ventas",
		"SUM(a.venta)/COUNT(*) AS porcentaje",
		"SUM(a.venta)-SUM(a.promedio*a.cantidad) AS ganancias",
		"COUNT(*) AS numfac");
  	
  	$grid->db->select($select);  
		$grid->db->from($this->from);
		foreach ($this->join as $valor) 
 		$grid->db->join($valor[0],$valor[1],$valor[2]);
		$grid->db->where("a.origen IN ('3I','3M')");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->where('e.depto',$departamento); 
		$grid->db->where('d.familia',$familia);  
		$grid->db->groupby("b.grupo");
		$grid->db->orderby("ganancias DESC");
  	
  	$grid->column("Grupo"     		 , "nombre","align='left'");
		$grid->column("Costo"     		 , "<number_format><#costo#>|2|,|.</number_format>",'align=right');
		$grid->column("supermercado"   , "<number_format><#ventas#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Ganancias"  		 , "<number_format><#ganancias#>|2|,|.</number_format>"  ,'align=right'); 
		$grid->column("% supermercado" , "<number_format><#porcentaje#>|2|,|.</number_format>"  ,'align=right');		
		$grid->column("Cant. Fact"     , "numfac"   ,'align=right');

		$grid->totalizar('ganancias','ganancias','supermercado');
		$grid->build();

		$grafico = open_flash_chart_object(680,450, site_url("supermercado/ganancias/ggrupo/$anio/$departamento/$familia"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ganancias por Grupo</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function producto ($anio='',$departamento='',$familia='',$grupo=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');

		rapydlib("prototype");
    $ajax_onchange = '
	  function get_familia(){
	    var url = "'.site_url('reportes/sinvfamilias').'";
	    var pars = "dpto="+$F("depto");
	    var myAjax = new Ajax.Updater("td_familia", url, { method: "post", parameters: pars });
	    get_grupo();
	  }
	  function get_grupo(){
	    var url = "'.site_url('reportes/sinvgrupos').'";
	    var pars = "dpto="+$F("depto")+"&familia="+$F("familia");
	    var myAjax = new Ajax.Updater("td_grupo", url, { method: "post", parameters: pars });
	  }
	  ';
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['depto']) AND empty($departamento)) $departamento=$_POST['depto'];		
		if(isset($_POST['familia']) AND empty($familia)) $familia=$_POST['familia'];
		if(isset($_POST['grupo']) AND empty($grupo)) $grupo=$_POST['grupo'];
		
		if(empty($anio) or empty($departamento)) redirect("supermercado/ganancias/departamento");
		if(empty($familia)) redirect("supermercado/ganancias/familia/$anio/$departamento");
		if(empty($grupo)) redirect("supermercado/ganancias/grupo/$anio/$departamento/$familia");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$filter = new DataForm('supermercado/ganancias/producto');
		$filter->script($ajax_onchange);
		$filter->title('Filtro de Ganancias');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->rule = "max_length[4]"; 
		$filter->anio->maxlength=4; 
		
		$filter->departamento = new dropdownField("Departamento", "depto");
		$filter->departamento->insertValue=$departamento;     
		$filter->departamento->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto"); 
    $filter->departamento->onchange = "get_familia();";
		
		$filter->familia = new dropdownField("familia", "familia");
		$filter->familia->insertValue=$familia;
		$filter->familia->options("SELECT familia, descrip FROM line WHERE depto='$departamento'");     
    $filter->familia->onchange = "get_grupo();";
		
		$filter->grupo = new dropdownField("Grupo","grupo");
		$filter->grupo->insertValue=$grupo;
		$filter->grupo->options("SELECT grupo, nom_grup FROM grup WHERE depto='$departamento' AND familia='$familia'"); 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/ganancias/producto/'),array('anio','depto','familia','grupo')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.fecha","b.codigo","b.descrip AS nombre","e.depto","e.descrip","d.familia","d.descrip","b.grupo","b.descrip","c.nom_grup as ngrupo",
    "SUM(a.promedio*a.cantidad) AS costo",
    "SUM(a.venta) AS ventas",
    "SUM(a.venta)/COUNT(*)AS porcentaje",
    "SUM(a.venta)-SUM(a.promedio*a.cantidad) AS ganancias",  
		"COUNT(*) AS numfac");     
			
	  $grid->db->select($select);  
		$grid->db->from($this->from);
		foreach ($this->join as $valor) 
 		$grid->db->join($valor[0],$valor[1],$valor[2]);
		$grid->db->where("a.origen IN ('3I','3M')");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->where('e.depto',$departamento); 
		$grid->db->where('d.familia',$familia);  
		$grid->db->where('b.grupo',$grupo);  
		$grid->db->groupby("a.codigo");
		$grid->db->orderby("ganancias DESC");
  	
  	$grid->column("Producto"   			, "nombre","align='left'");
		$grid->column("Costo"      			, "<number_format><#costo#>|2|,|.</number_format>",'align=right');
		$grid->column("supermercado"  	, "<number_format><#ventas#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Ganancias"     	, "<number_format><#ganancias#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("% supermercado"  , "<number_format><#porcentaje#>|2|,|.</number_format>"  ,'align=right');		
		$grid->column("Cant. Fact" 			, "numfac"   ,'align=right');
		
		$grid->totalizar('costo','ganancias','supermercado');
		$grid->build();
		
		$grafico = open_flash_chart_object(680,500, site_url("supermercado/ganancias/gproducto/$anio/$departamento/$familia/$grupo"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ganancias por Producto</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function gdepartamento($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT a.fecha,e.depto,e.descrip as nombre,d.familia,d.descrip,c.grupo,b.descrip,c.nom_grup,
		SUM(a.promedio*a.cantidad) AS costo,
		SUM(a.venta) AS ventas,
		SUM(a.venta)-SUM(a.promedio*a.cantidad) AS ganancias
		FROM $this->from ";
		
		foreach($this->join AS $valor) 
			$mSQL.="$valor[2] JOIN $valor[0] ON $valor[1]";
		
		$mSQL.="WHERE a.origen IN ('3I','3M') AND a.fecha>='$fechai' AND a.fecha<='$fechaf' 
		GROUP BY d.depto ORDER BY ganancias DESC LIMIT 15";    
		
		$maxval=0;
		$query = $this->db->query($mSQL);
		$data_1=$departamento=array(); 
		foreach($query->result() as $row ){
		if ($row->ganancias>$maxval) $maxval=$row->ganancias;
			$nombre[]=$row->nombre;
			$departamento[]=$row->depto;
			$data_1[]=$row->ganancias;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar(75, '#639F45');
		$bar_1->key('Ganancias',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_1->links[]= site_url("/supermercado/ganancias/familia/$anio/".$departamento[$i]);		 	                                                                                  
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ganancia por departamento en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' );
		$g->data_sets[] = $bar_1;

		$g->set_x_axis_3d( 5 );
		$g->x_axis_colour( '#909090', '#ADB5C7' );
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		//$g->set_x_axis_steps( 10 );
		
		$g->set_x_legend('Departamentos', 14, '#004381' );        
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Departamento: #x_label# <br>Monto: #tip#' );
		$g->y_axis_colour( '#909090', '#ADB5C7' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ganancia x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ganancias en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';
		echo utf8_encode($g->render());
		}
	function gfamilia($anio='',$departamento=''){
		$this->load->library('Graph');
			
		if (empty($anio) or empty($departamento)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		    
		$mSQL = "SELECT a.fecha,e.depto AS departamento ,e.descrip AS ndepartamento,d.familia as familia,d.descrip AS nombre,b.grupo,b.descrip,c.nom_grup,
		SUM(a.promedio*a.cantidad) AS costo,
		SUM(a.venta) AS ventas,
		SUM(a.venta)-SUM(a.promedio*a.cantidad) AS ganancias
		FROM $this->from";
		
		foreach($this->join AS $valor) 
			$mSQL.="$valor[2] JOIN $valor[0] ON $valor[1]";
			
		$mSQL.=" WHERE a.origen IN ('3I','3M') AND a.fecha>='$fechai' AND a.fecha<='$fechaf' AND e.depto='$departamento'
		GROUP BY d.familia ORDER BY ganancias DESC LIMIT 15";   
		//echo $mSQL;
		
		$maxval=0; $query = $this->db->query($mSQL);
		foreach($query->result() as $row ){ if ($row->ganancias>$maxval) $maxval=$row->ganancias;
			$nombre[]=$row->nombre;
			$data_1[]=$row->ganancias;
			$familia[]=$row->familia;
		} $ndepartamento=$row->ndepartamento;

		$om=1;while($maxval/$om>100) $om=$om*10;
			
		$bar_1 = new bar(75, '#639F45');
		$bar_1->key('Ganancias',10);
		  
		for($i=0;$i<count($data_1);$i++ ){
		  	
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_1->links[]= site_url("supermercado/ganancias/grupo/$anio/$departamento/".$familia[$i]);
  	
		} 			 
		
		$g = new graph();  
		$g->set_is_decimal_separator_comma(1);
		
		if($maxval>0){
		$g->title( 'Ganancias por familias del departamento de '.$ndepartamento.' en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		
		$g->set_x_axis_3d( 5 );
		$g->x_axis_colour( '#909090', '#ADB5C7' );
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 ); 
		$g->set_x_legend('familias', 14, '#004381' );                      
		
		$g->bg_colour = '#FFFFFF';
		$g->y_axis_colour( '#909090', '#ADB5C7' );
		$g->set_tool_tip( '#key#<br>familia: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ganancia x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ganancias con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
		}
	
	function ggrupo($anio='',$departamento='',$familia=''){
		$this->load->library('Graph');
		$this->load->library('calendar');
		//$this->calendar->generate();
		
		if (empty($anio) or empty($departamento)or empty($familia)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		    
		$mSQL = "SELECT a.fecha,e.depto,e.descrip AS ndepartamento,d.familia,d.descrip AS nfamilia,b.grupo AS grupo,b.descrip,c.nom_grup AS nombre,
		SUM(a.promedio*a.cantidad) AS costo,
		SUM(a.venta) AS ventas,
		SUM(a.venta)-SUM(a.promedio*a.cantidad) AS ganancias
		FROM $this->from";
		
		foreach($this->join AS $valor) 
			$mSQL.="$valor[2] JOIN $valor[0] ON $valor[1]";
		
    $mSQL.=" WHERE a.origen IN ('3I','3M') AND a.fecha>='$fechai' AND a.fecha<='$fechaf'AND e.depto='$departamento'AND d.familia='$familia'
		GROUP BY b.grupo ORDER BY ganancias DESC LIMIT 15";   
		//echo $mSQL;
		
		$maxval=0; $query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){ if ($row->ganancias>$maxval) $maxval=$row->ganancias;
		  $grupo[]=$row->grupo;
		  $nombre[]=$row->nombre;
		 	$data_1[]=$row->ganancias;
		} $nfamilia=$row->nfamilia;
		
		$om=1;while($maxval/$om>100) $om=$om*10;
			
		$bar_1 = new bar(75, '#639F45');
		$bar_1->key('Ganancias',10);
	  
		for($i=0;$i<count($data_1);$i++ ){
		 	
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_1->links[]= site_url("supermercado/ganancias/producto/$anio/$departamento/$familia/".$grupo[$i]);
		} 			 
		
		$g = new graph();  
		$g->set_is_decimal_separator_comma(1);
		
		if($maxval>0){
		$g->title('Ganancias por grupo de la familia '.$nfamilia.' en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		
		$g->set_x_axis_3d( 5 );
		$g->x_axis_colour( '#909090', '#ADB5C7' );
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 ); 
		$g->set_x_legend('Grupos', 14, '#004381' );                      
		
		$g->y_axis_colour( '#909090', '#ADB5C7' );
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Grupo: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ganancia x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ganancias con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
		}
	
	function gproducto($anio='',$departamento='',$familia='',$grupo=''){
		$this->load->library('Graph');
		$this->load->library('calendar');
		//$this->calendar->generate();
		
		if (empty($anio) or empty($departamento)or empty($familia)or empty($grupo)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		    
		$mSQL = "SELECT a.fecha,b.codigo,b.descrip AS nnombre,LEFT(b.descrip,20)AS nombre,e.depto,e.descrip,d.familia,d.descrip,b.grupo,b.descrip,c.nom_grup as ngrupo,
    SUM(a.promedio*a.cantidad) AS costo,
    SUM(a.venta) AS ventas,
    SUM(a.venta)-SUM(a.promedio*a.cantidad) AS ganancias
    FROM $this->from ";
    
    foreach($this->join AS $valor)
    	$mSQL.="$valor[2] JOIN $valor[0] ON $valor[1]";

    $mSQL.=" WHERE a.origen IN ('3I','3M') AND a.fecha>='$fechai' AND a.fecha<='$fechaf' AND e.depto='$departamento' AND d.familia='$familia' AND b.grupo='$grupo'
    GROUP BY a.codigo ORDER BY ganancias DESC LIMIT 15";   
		//echo $mSQL;
		
		$maxval=0; $query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){ if ($row->ganancias>$maxval) $maxval=$row->ganancias;
		  $nombre[]=$row->nombre;
		 	$data_1[]=$row->ganancias;
		} $ngrupo=$row->ngrupo;
		
		$om=1;while($maxval/$om>100) $om=$om*10;
			
		$bar_1 = new bar(75, '#639F45');
		$bar_1->key('Ganancias',10);
		
		for($i=0;$i<count($data_1);$i++ ){
		 	
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			//$bar_1->links[]= site_url("supermercado/gananciasproducto/index/$anio/$departamento/".$familia[$i]);
		} 			 
		
		$g = new graph();  
		$g->set_is_decimal_separator_comma(1);
		
		if($maxval>0){
		$g->title('Ganancias por producto de el grupo '.$ngrupo.' en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		
		$g->set_x_axis_3d( 5 );
		$g->x_axis_colour( '#909090', '#ADB5C7' );
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 ); 
		$g->set_x_legend('Productos', 14, '#004381' );                      
		
		$g->y_axis_colour( '#909090', '#ADB5C7' );
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Producto: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ganancia x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ganancias con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
		}
}
?>