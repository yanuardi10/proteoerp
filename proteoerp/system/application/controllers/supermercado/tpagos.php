<?php
class  Tpagos extends Controller {  
  
	function Tpagos() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
		$this->datasis->modulo_id("10E",1);
	}  
	function index(){
		redirect ('supermercado/tpagos/anuales');
	}
	function anuales(){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		   
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';	
		                            
		if($this->uri->segment(4))$anio=$this->uri->segment(4);	elseif(isset($_POST['anio']))$anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		                        
		$filter = new DataForm('supermercado/tpagos/anuales');
		$filter->title('Filtro de Tipo de pagos');
		$filter->script($script, "create");
		$filter->script($script, "modify");
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum';
		  
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/tpagos/anuales'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.fecha","a.tipo","b.nombre",
		"SUM(a.monto)AS grantotal",
		"SUM(a.transac)AS transacciones");
		
		$grid->db->select($select);  
		$grid->db->from("est_pago as a");
		$grid->db->join("tarjeta as b","a.tipo=b.tipo");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->groupby("a.tipo");
    $grid->db->orderby("grantotal DESC");
    
		$grid->column("Tipo"       	  , "nombre","align='left'");          
		$grid->column("Total"  				, "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac"  , "transacciones",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
		
		$grafico = open_flash_chart_object(720,450, site_url("supermercado/tpagos/ganuales/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>-Tipos de pago</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function mensuales($anio='',$tipo=''){
	  $this->rapyd->load("datagrid2");  
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
				
		//if(empty($anio)OR ($tipo))redirect("supermercado/tpagos/anuales/$anio");	
    
    $fechai=$anio.'0101';
    $fechaf=$anio.'1231';
						                 
		$filter = new DataForm('supermercado/tpagos/mensuales');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Ventas Mensuales');
		$filter->script($script, "create");
		$filter->script($script, "modify");
		
		$filter->anio = new inputField("A&ntilde;o", "anio");         
		//$filter->anio->in='tipo';
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum';
		 
		$filter->tipo = new dropdownField("Tipo","tipo");
		$filter->tipo->options("SELECT tipo,nombre FROM tarjeta GROUP BY tipo ORDER BY tipo");                                 
		$filter->tipo->insertValue=$tipo;
		$filter->tipo->style="width:200px";
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/tpagos/mensuales'),array('anio','tipo')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.fecha","a.tipo","b.nombre","DATE_FORMAT(fecha,'%m') AS mes",
		"SUM(a.monto)AS grantotal",
		"SUM(a.transac)AS transacciones");
		
		$grid->db->select($select);  
		$grid->db->from("est_pago as a");
		$grid->db->join("tarjeta as b","a.tipo=b.tipo");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->where('a.tipo',$tipo);  
		$grid->db->groupby("MONTH(fecha)");
    $grid->db->orderby("mes ASC");
   	//$grid->db->limit(15,0);
   	    
		$grid->column("Mes"       	  , "mes","align='left'");          
		$grid->column("Total"  				, "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac"  , "transacciones",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
   	   	
		$grafico = open_flash_chart_object(680,350, site_url("supermercado/tpagos/gmensuales/$anio/$tipo"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
	  $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas de Mensuales</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function diarias($anio='',$tipo='',$mes=''){
	  $this->rapyd->load("datagrid2");  
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
				
    $fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
    $fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
						                 
		$filter = new DataForm('supermercado/tpagos/diarias');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Ventas Diarias');
		$filter->script($script, "create");
		$filter->script($script, "modify");
		$filter->mes = new dropdownField("Mes/A&ntilde;o", "mes");  
		
		for($i=1;$i<13;$i++) 
		$filter->mes->option(str_pad($i, 2, '0', STR_PAD_LEFT),str_pad($i, 2, '0', STR_PAD_LEFT));  
		$filter->mes->size=2;
		$filter->mes->style='';
		$filter->mes->insertValue=$mes;	
		
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->in='mes';
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum'; 
		 
		$filter->tipo = new dropdownField("Tipo","tipo");
		$filter->tipo->options("SELECT tipo,nombre FROM tarjeta GROUP BY tipo ORDER BY tipo");                                 
		$filter->tipo->insertValue=$tipo;
		$filter->tipo->style="width:200px";
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/tpagos/diarias'),array('anio','tipo','mes')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.fecha","a.tipo","b.nombre","DATE_FORMAT(fecha,'%d') AS dia",
		"SUM(a.monto)AS grantotal",
		"SUM(a.transac)AS transacciones");
		
		$grid->db->select($select);  
		$grid->db->from("est_pago as a");
		$grid->db->join("tarjeta as b","a.tipo=b.tipo");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->where('a.tipo',$tipo);   
		$grid->db->groupby("fecha");
    $grid->db->orderby("dia ASC");
   	//$grid->db->limit(15,0);
   	    
		$grid->column("Dia"          , "dia","align='left'");          
		$grid->column("Total"  			 , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac" , "transacciones",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
   	   	
		$grafico = open_flash_chart_object(680,350, site_url("supermercado/tpagos/gdiarias/$anio/$tipo/$mes"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas Diarias</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function ganuales($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
			
		$mSQL = "SELECT a.fecha,a.tipo,SUM(a.monto)AS grantotal,b.nombre 
		FROM est_pago  as a JOIN tarjeta as b ON a.tipo=b.tipo
		WHERE fecha>='$fechai' AND fecha<='$fechaf'
		GROUP BY a.tipo ORDER BY grantotal DESC";    
		
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$tipo[]= $row->tipo;
			$nombre[]= $row->nombre;
			$data_1[] = $row->grantotal;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar( 55, '#424581' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			//$mes=$i+1;
			$bar_1->links[]= site_url("/supermercado/tpagos/mensuales/$anio/$tipo[$i]");

		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend( 'Tipos de Pago', 14,'#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Tipo: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else                                                                                           
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	} 
	function gmensuales($anio='',$tipo=''){
	  $this->load->library('Graph');
		$this->lang->load('calendar');
		
		if (empty($anio) or empty($tipo)) return;
		
    $fechai=$anio.'0101';
    $fechaf=$anio.'1231';
    
    $mSQL = "SELECT a.fecha,a.tipo,SUM(a.monto)AS grantotal,b.nombre,DATE_FORMAT(fecha,'%m') AS mes
		FROM est_pago  as a JOIN tarjeta as b ON a.tipo=b.tipo
		WHERE fecha>='$fechai' AND fecha<='$fechaf' AND a.tipo='$tipo'
		GROUP BY MONTH(fecha) ORDER BY mes";    
    //echo $mSQL;                                                                                    
    
    $maxval=0;
		$query = $this->db->query($mSQL);
    
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$mes[]=$row->mes;
			$nombre=$row->nombre;
			$data_1[]=$row->grantotal;  
		}
		
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar( 55, '#424581' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
		 	$bar_1->links[]= site_url("/supermercado/tpagos/diarias/$anio/$tipo/$mes[$i]");                                                                                  
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		 $g->title( 'Ventas en el a&ntilde;o'.$anio.' del tipo de pago '.$nombre,'{font-size: 16px; color:##00264A}' );
		 $g->data_sets[] = $bar_1;
	               
		 $g->x_axis_colour( '#A6A6A6', '#ADB5C7' );
		 $g->set_x_labels($mes);
		 $g->set_x_label_style( 10, '#000000', 3, 1 );
		 $g->set_x_axis_steps( 10 );
		 $g->set_x_legend('Meses', 14, '#004381' ); 
		 
		 $g->bg_colour = '#FFFFFF';
		 $g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Monto: #tip#' );
		 $g->set_y_max(ceil($maxval/$om));
		 $g->y_label_steps(5);
		 $g->y_axis_colour( '#A6A6A6', '#ADB5C7' );
		 $g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else 
		 $g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		 $g->bg_colour='#FFFFFF';                                                                 
		 echo utf8_encode($g->render());
	}
	function gdiarias($anio='',$tipo='',$mes=''){
	  $this->load->library('Graph');
		$this->lang->load('calendar');
		
		if (empty($anio) or empty($tipo)) return;
		
    $fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
    $fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
    
    $mSQL = "SELECT a.fecha,a.tipo,SUM(a.monto)AS grantotal,b.nombre,DATE_FORMAT(fecha,'%d') AS dia
		FROM est_pago  as a JOIN tarjeta as b ON a.tipo=b.tipo
		WHERE fecha>='$fechai' AND fecha<='$fechaf' AND a.tipo='$tipo'
		GROUP BY fecha ORDER BY fecha ASC";   
    //echo $mSQL;                                                                                    
    
    $maxval=0;
		$query = $this->db->query($mSQL);
    
	foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$dia[]=$row->dia;
			$data_1[]=$row->grantotal;  
		}
		
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar( 55, '#424581' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
		$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.'))); 
		}                                                                                 
		 			 
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		 $g->title( 'Ventas de el'.$mes.'/'.$anio,'Tipo de pago'.$tipo,'{font-size: 16px; color:##00264A}' );
		 $g->data_sets[] = $bar_1;
	               
		 $g->x_axis_colour( '#A6A6A6', '#ADB5C7' );
		 $g->set_x_labels($dia);
		 $g->set_x_label_style( 10, '#000000', 3, 1 );
		 $g->set_x_axis_steps( 10 );
		 $g->set_x_legend('Dias', 14, '#004381' ); 
		 
		 $g->bg_colour = '#FFFFFF';
		 $g->set_tool_tip( '#key#<br>Dia: #x_label# <br>Monto: #tip#' );
		 $g->set_y_max(ceil($maxval/$om));
		 $g->y_label_steps(5);
		 $g->y_axis_colour( '#A6A6A6', '#ADB5C7' );
		 $g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else 
		 $g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		 $g->bg_colour='#FFFFFF';                                                                 
		 echo utf8_encode($g->render());
	}
}
?>