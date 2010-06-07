<?php
class  tventas extends Controller {  
  
  var $from;
  
	function tventas() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
		$this->datasis->modulo_id("10D",1);
	}  
	
	function index(){
		redirect ('supermercado/tventas/departamento');
	}
	
	function departamento(){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		   
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';	
		                            
		if($this->uri->segment(4))$anio=$this->uri->segment(4);	elseif(isset($_POST['anio']))$anio=$_POST['anio']; else $anio=date("Y");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		                        
		$filter = new DataForm('supermercado/tventas/departamento');
		$filter->title('Filtro de ventas por Departamento');
		$filter->script($script, "create");
		$filter->script($script, "modify");	
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum';
		 	
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/tventas/departamento'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.fecha","a.depto","b.descrip as nombre",
		"SUM(a.monto) AS grantotal",
		"SUM(a.costo) AS costo",  
		"SUM(a.cantidad) AS cantidad",
		"SUM(a.monto)-SUM(a.costo) AS ganancia",
		"(SUM(a.monto)-SUM(a.costo))*100/SUM(a.monto) AS pganancia");
					
		$grid->db->select($select);  
		$grid->db->from("est_item AS a");
		$grid->db->join("dpto AS b","a.depto=b.depto");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);   
		$grid->db->groupby("a.depto");

		$grid->column("Departamento" , "nombre","align='left'");          
		$grid->column("Total"        , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Ganancia"     , "<number_format><#ganancia#>|2|,|.</number_format>" ,'align=right');
		$grid->column("% Ganancia"     , "<number_format><#pganancia#>|2|,|.</number_format> %" ,'align=right');
    $grid->column("Cantidad"     , "cantidad","align='right'");                        
		
		$grid->totalizar('grantotal','ganancia');
		$grid->build();
		//echo $grid->db->last_query(); 
		$grafico = open_flash_chart_object(720,450, site_url("supermercado/tventas/gdepartamento/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = "<h1>Ventas por Departamento</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function familia($anio='',$depto=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');

    $script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';			    
		                            
		if($this->uri->segment(4))$anio=$this->uri->segment(4);	elseif(isset($_POST['anio']))$anio=$_POST['anio']; else $anio=date("Y");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		                        
		$filter = new DataForm('supermercado/tventas/familia');
		$filter->title('Filtro de ventas por Familia');
		$filter->script($script, "create");
		$filter->script($script, "modify");	
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum';
		
		$filter->depto = new  dropdownField("Departamento", "depto");
		$filter->depto->options("SELECT depto, descrip FROM dpto WHERE tipo<>'G' ORDER BY depto");
		$filter->depto->insertValue=$depto;
		$filter->depto->style='width:200px;';
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/tventas/familia'),array('anio','depto')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.depto","a.familia","b.descrip",
		"SUM(a.monto) AS grantotal",
		"SUM(a.costo) AS costo",  
		"SUM(a.cantidad) AS cantidad",
		"SUM(a.monto)-SUM(a.costo) AS ganancia",
		"(SUM(a.monto)-SUM(a.costo))*100/SUM(a.monto) AS pganancia");
					
		$grid->db->select($select);  
		$grid->db->from("est_item AS a");
		$grid->db->join("fami AS b","a.familia=b.familia","a.depto=b.depto");
		$grid->db->where('fecha >= ', $fechai);
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->where('a.depto',$depto); 
		$grid->db->groupby("a.familia");
		    
		$grid->column("Familia"      , "descrip","align='left'");          
		$grid->column("Total"        , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Ganancia"     , "<number_format><#ganancia#>|2|,|.</number_format>" ,'align=right');
		$grid->column("% Ganancia"     , "<number_format><#pganancia#>|2|,|.</number_format> %" ,'align=right');
    $grid->column("Cantidad"     , "cantidad","align='right'");                        
		
		$grid->totalizar('grantotal','ganancia');
		$grid->build();
		
		$grafico = open_flash_chart_object(720,450, site_url("supermercado/tventas/gfamilia/$anio/$depto/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = "<h1>Ventas por Familia</h1>";
		$this->load->view('view_ventanas', $data);
	}

	function grupo($anio='',$depto='',$fami=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		                            
		if($this->uri->segment(4))$anio=$this->uri->segment(4);	elseif(isset($_POST['anio']))$anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		                        
		$filter = new DataForm('supermercado/tventas/grupo');
		$filter->title('Filtro de ventas por Grupo');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		
		$filter->depto = new  dropdownField("Departamento", "depto");
		$filter->depto->options("SELECT depto, descrip FROM dpto GROUP BY depto ORDER BY depto");
		$filter->depto->insertValue=$depto;
		$filter->depto->style='width:200px;';
		
		$filter->fami = new  dropdownField("Familia","familia");
		$filter->fami->options("SELECT familia,descrip FROM fami GROUP BY familia ORDER BY familia");
		$filter->fami->insertValue=$fami;
		$filter->fami->style='width:200px;';
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/tventas/grupo'),array('anio','depto','familia')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.depto","a.familia","a.grupo","b.nom_grup",
		"SUM(a.monto) AS grantotal",
		"SUM(a.costo) AS costo",  
		"SUM(a.cantidad) AS cantidad",
		"SUM(a.monto)-SUM(a.costo) AS ganancia",
		"(SUM(a.monto)-SUM(a.costo))*100/SUM(a.monto) AS pganancia");
						
		$grid->db->select($select);  
		$grid->db->from("est_item AS a");
		$grid->db->join("grup AS b","a.grupo=b.grupo AND a.familia=b.familia AND a.depto=b.depto");
		$grid->db->where('fecha >= ',$fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->where('a.depto',$depto); 
		$grid->db->where('a.familia',$fami); 
		$grid->db->groupby("a.grupo");
		    
		$grid->column("Grupo"     , "nom_grup","align='left'");          
		$grid->column("Total"     , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Ganancia"  , "<number_format><#ganancia#>|2|,|.</number_format>" ,'align=right');
		$grid->column("% Ganancia"     , "<number_format><#pganancia#>|2|,|.</number_format> %" ,'align=right');
    $grid->column("Cantidad"  , "cantidad","align='right'");                        
		
		$grid->totalizar('grantotal','ganancia');
		$grid->build();
		//echo $grid->db->last_query();
		
		$grafico = open_flash_chart_object(720,450, site_url("supermercado/tventas/ggrupo/$anio/$depto/$fami"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = "<h1>Ventas por Grupo</h1>";
		$this->load->view('view_ventanas', $data);
	}

	function producto($anio='',$depto='',$fami='',$grupo=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		                            
		if($this->uri->segment(4))$anio=$this->uri->segment(4);	elseif(isset($_POST['anio']))$anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		$ajax_onchange = '
			function get_familias(){
			  var url = "'.site_url('supermercado/maes/maesfamilias').'";
			  var pars = "dpto="+$F("depto");
			  var myAjax = new Ajax.Updater("td_familia", url, { method: "post", parameters: pars });
			
			  var url = "'.site_url('supermercado/maes/maesgrupos').'";
			  var gmyAjax = new Ajax.Updater("td_grupo", url);
			}
			
			function get_grupo(){
			  var url = "'.site_url('supermercado/maes/maesgrupos').'";
			  var pars = "dpto="+$F("depto")+"&fami="+$F("familia");
			  var myAjax = new Ajax.Updater("td_grupo", url, { method: "post", parameters: pars });
			}';
		
		                        
		$filter = new DataForm('supermercado/tventas/producto');
		$filter->script($ajax_onchange);
		$filter->title('Filtro de ventas por Producto');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		
		$filter->depto = new  dropdownField("Departamento", "depto");
		$filter->depto->options("SELECT depto, descrip FROM dpto GROUP BY depto ORDER BY depto");
		$filter->depto->insertValue=$depto;
		$filter->depto->style='width:200px;';
		
		
		$filter->fami = new  dropdownField("Familia","familia");
		$filter->fami->options("SELECT familia,descrip FROM fami GROUP BY familia ORDER BY familia");
		$filter->fami->insertValue=$fami;
		$filter->fami->style='width:200px;';
		
		$filter->grupo = new  dropdownField("Grupo","grupo");
		$filter->grupo->options("SELECT grupo,nom_grup FROM grup GROUP BY grupo ORDER BY grupo");
		$filter->grupo->insertValue=$grupo;
		$filter->grupo->style='width:200px;';
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/tventas/producto'),array('anio','depto','familia','grupo')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.depto","a.familia","a.grupo","b.descrip",
		"SUM(a.monto) AS grantotal",
		"SUM(a.costo) AS costo",  
		"SUM(a.cantidad) AS cantidad",
		"SUM(a.monto)-SUM(a.costo) AS ganancia",
		"(SUM(a.monto)-SUM(a.costo))*100/SUM(a.monto) AS pganancia");
								
		$grid->db->select($select);  
		$grid->db->from("est_item AS a");
		$grid->db->join("maes AS b","a.codigo=b.codigo");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->where('a.depto',$depto); 
		$grid->db->where('a.familia',$fami); 
		$grid->db->where('a.grupo',$grupo);
		$grid->db->groupby("a.codigo");
		//$grid->db->limit=10;
		  
		$grid->column("Producto"  , "descrip","align='left'");          
		$grid->column("Total"     , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Ganancia"  , "<number_format><#ganancia#>|2|,|.</number_format>" ,'align=right');
    $grid->column("% Ganancia"     , "<number_format><#pganancia#>|2|,|.</number_format> %" ,'align=right');
    $grid->column("Cantidad"  , "cantidad","align='right'");                        
		
		$grid->totalizar('grantotal','ganancia');
		$grid->build();
		 //echo $grid->db->last_query(); 
		$grafico = open_flash_chart_object(720,450, site_url("supermercado/tventas/gproducto/$anio/$depto/$fami/$grupo"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = "<h1>Ventas por Producto</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function gdepartamento($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
			
		$mSQL = "SELECT a.fecha, a.depto, b.descrip as nombre, 
 		SUM(a.monto) AS grantotal, SUM(a.costo) AS costo,SUM(a.cantidad) AS cantidad,
 		SUM(a.monto)-SUM(a.costo) AS ganancia 
 		FROM (est_item as a) 
 		JOIN dpto AS b ON a.depto=b.depto 
 		WHERE a.fecha>='$fechai' AND a.fecha<='$fechaf' GROUP BY a.depto";                                                 
  	//echo $mSQL; 
		
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$nombre[]= $row->nombre;
			$depto[]= $row->depto;
			$data_1[] = $row->grantotal;
			$data_2[] = $row->ganancia;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar_fade( 55, '#C31812' );
		$bar_2 = new bar_fade( 55, '#424581' );

		$bar_1->key('Total',10);
		$bar_2->key('Ganancias',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_2->add_data_tip($data_2[$i]/$om, graph::esc( number_format($data_2[$i],2,',','.')));
			
			//$mes=$i+1;
			$bar_1->links[]= site_url("/supermercado/tventas/familia/$anio/$depto[$i]");
			$bar_2->links[]= site_url("/supermercado/tventas/familia/$anio/$depto[$i]");


		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		$g->data_sets[] = $bar_2;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend( 'Departamento', 14,'#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Departamento: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else                                                                                           
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	}
	function gfamilia($anio='',$depto=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
			
		$mSQL = "SELECT a.depto,a.familia, b.descrip,c.descrip AS dpto,
		SUM(a.monto) AS grantotal,  
		SUM(a.costo) AS costo,      
		SUM(a.cantidad) AS cantidad,
		SUM(a.monto)-SUM(a.costo) AS ganancia
		FROM  est_item AS a
		JOIN  fami  AS b ON a.familia= b.familia AND a.depto=b.depto
		JOIN  dpto  AS c ON a.depto= c.depto
		WHERE a.fecha>='$fechai' AND a.fecha<='$fechaf' AND a.depto='$depto'
		GROUP BY a.familia";                                                 
  	//echo $mSQL; 
		
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$nombre[] = $row->descrip;
			$fami[]   = $row->familia;
			$dpto  = $row->dpto;
			$data_1[] = $row->grantotal;
			$data_2[] = $row->ganancia;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar_fade( 55, '#C31812' );
		$bar_2 = new bar_fade( 55, '#424581' );

		$bar_1->key('Total',10);
		$bar_2->key('Ganancias',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_2->add_data_tip($data_2[$i]/$om, graph::esc( number_format($data_2[$i],2,',','.')));
			
			//$mes=$i+1;
			$bar_1->links[]= site_url("/supermercado/tventas/grupo/$anio/$depto/$fami[$i]");
			$bar_2->links[]= site_url("/supermercado/tventas/grupo/$anio/$depto/$fami[$i]");
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de el a&ntilde;o '.$anio.' Departamento de '.$dpto,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		$g->data_sets[] = $bar_2;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend( 'Familia', 14,'#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Familia: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else                                                                                           
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	} 
	function ggrupo($anio='',$depto='',$fami=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
			
		$mSQL = "SELECT a.depto,a.familia,a.grupo, b.nom_grup,c.descrip as famili,d.descrip as dpto,
		SUM(a.monto) AS grantotal,  
		SUM(a.costo) AS costo,      
		SUM(a.cantidad) AS cantidad,
		SUM(a.monto)-SUM(a.costo) AS ganancia
		FROM  est_item AS a
		JOIN  grup AS b ON a.grupo= b.grupo AND a.familia=b.familia AND a.depto=b.depto
		JOIN  fami AS c ON a.familia= c.familia AND a.depto=c.depto
		JOIN  dpto AS d ON a.depto= d.depto
		WHERE a.fecha>='$fechai' AND a.fecha<='$fechaf' AND a.depto='$depto' AND a.familia='$fami'
		GROUP BY a.grupo";                                                 
  	//echo $mSQL; 
		
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$nombre[] = $row->nom_grup;
			$grupo[]  = $row->grupo;
			$famili   = $row->famili;
			$dpto     = $row->dpto;
			$data_1[] = $row->grantotal;
			$data_2[] = $row->ganancia;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar_fade( 55, '#C31812' );
		$bar_2 = new bar_fade( 55, '#424581' );

		$bar_1->key('Total',10);
		$bar_2->key('Ganancias',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_2->add_data_tip($data_2[$i]/$om, graph::esc( number_format($data_2[$i],2,',','.')));
			
			//$mes=$i+1;
			$bar_1->links[]= site_url("/supermercado/tventas/producto/$anio/$depto/$fami/$grupo[$i]");
			$bar_2->links[]= site_url("/supermercado/tventas/producto/$anio/$depto/$fami/$grupo[$i]");


		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de el a&ntilde;o '.$anio.' Departamento de '.$dpto.' Familia '.$famili,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		$g->data_sets[] = $bar_2;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend( 'Grupo', 14,'#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Grupo: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else                                                                                           
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	}
	function gproducto($anio='',$depto='',$fami='',$grupo=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
			
		$mSQL = "SELECT a.codigo,a.depto,a.familia,a.grupo,e.descrip as nombre,
		SUM(a.monto) AS grantotal,  
		SUM(a.costo) AS costo,      
		SUM(a.cantidad) AS cantidad,
		SUM(a.monto)-SUM(a.costo) AS ganancia
		FROM  est_item AS a
		JOIN  maes AS e ON a.codigo= e.codigo
		WHERE a.fecha>='$fechai' AND a.fecha<='$fechaf' AND a.depto='$depto' AND a.familia='$fami' AND a.grupo='$grupo'
		GROUP BY a.codigo LIMIT 10";                                                 
  	//echo $mSQL; 

		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$nombre[] = $row->nombre;
			$famili   = $row->famili;
			$dpto     = $row->dpto;
			$grup     = $row->grup;
			$data_1[] = $row->grantotal;
			$data_2[] = $row->ganancia;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar_fade( 55, '#C31812' );
		$bar_2 = new bar_fade( 55, '#424581' );

		$bar_1->key('Total',10);
		$bar_2->key('Ganancias',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_2->add_data_tip($data_2[$i]/$om, graph::esc( number_format($data_2[$i],2,',','.')));
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		$g->data_sets[] = $bar_2;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend( 'Productos', 14,'#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Producto: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else                                                                                           
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	}  
}
?>