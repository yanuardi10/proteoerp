<?php
class  Cajas extends Controller {  
  
	function Cajas() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
		$this->datasis->modulo_id('10B',1);
	}  
	function index(){
		redirect ('supermercado/cajas/anuales');
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
		                        
		$filter = new DataForm('supermercado/cajas/anuales');
		$filter->title('Filtro de ventas Anuales');
		$filter->script($script, "create");
		$filter->script($script, "modify");
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum'; 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/cajas/anuales'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("caja",
		"SUM(monto)AS grantotal",
		"SUM(impuesto)AS iva",
		"SUM(transac)as transacciones"); 
			
		$grid->db->select($select);  
		$grid->db->from("est_fecha");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->groupby("caja");
		$grid->db->orderby("caja");
		//$grid->db->limit(10,0);

		$grid->column("Caja"       	  , "caja","align='left'");          
		$grid->column("Total"  				, "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"   		, "<number_format><#iva#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac"  , "transacciones",'align=right');
		
		$grid->totalizar('iva','grantotal');
		$grid->build();
		
		$grafico = open_flash_chart_object(720,450, site_url("supermercado/cajas/ganuales/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas de Cajas</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function mensuales($anio='',$caja=''){
	  $this->rapyd->load("datagrid2");  
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
				
		//if(empty($anio)OR ($caja))redirect("supermercado/cajas/anuales/$anio");	
    
    $fechai=$anio.'0101';
    $fechaf=$anio.'1231';
						                 
		$filter = new DataForm('supermercado/ventas/mensuales');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Ventas Mensuales');
		
		$filter->anio = new inputField("A&ntilde;o", "anio");         
		//$filter->anio->in='caja';
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		 
		$filter->caja = new dropdownField("Caja","caja");
		$filter->caja->options("SELECT caja, caja as value FROM est_fecha GROUP BY caja ORDER BY caja");                                 
		$filter->caja->insertValue=$caja;
		$filter->caja->style="width:80px";
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/ventas/mensuales'),array('anio','caja')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array( "fecha","DATE_FORMAT(fecha,'%m') AS mes",
		"SUM(monto)AS grantotal",
		"SUM(impuesto)AS iva",
		"SUM(transac)as transacciones");     
			
		$grid->db->select($select);  
		$grid->db->from("est_fecha");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->where('caja=',$caja);  
		$grid->db->groupby("MONTH(fecha)");
		$grid->db->orderby("fecha ASC");
   	//$grid->db->limit(15,0);
   	    
		$grid->column("Mes"          , "mes","align='left'");          
		$grid->column("Total"  			 , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"  	 , "<number_format><#iva#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac" , "transacciones",'align=right');
		
		$grid->totalizar('iva','grantotal');
		$grid->build();
   	   	
		$grafico = open_flash_chart_object(680,350, site_url("supermercado/cajas/gmensuales/$anio/$caja"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas de Mensuales</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function diarias($anio='',$caja='',$mes=''){
	  $this->rapyd->load("datagrid2");  
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
				
    $fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
    $fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
						                 
		$filter = new DataForm('supermercado/ventas/diarias');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Ventas Diarias');
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
		 
		$filter->caja = new dropdownField("Caja","caja");
		$filter->caja->options("SELECT caja, caja as value FROM est_fecha GROUP BY caja ORDER BY caja");                                 
		$filter->caja->insertValue=$caja;
		$filter->caja->style="width:80px";
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/ventas/diarias'),array('anio','caja','mes')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array( "fecha","DATE_FORMAT(fecha,'%d') AS dia",
		"SUM(monto)AS grantotal",
		"SUM(impuesto)AS iva",
		"SUM(transac)as transacciones");     
			
		$grid->db->select($select);  
		$grid->db->from("est_fecha");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);   
		$grid->db->where('caja',$caja);  
		$grid->db->groupby("fecha");
		$grid->db->orderby("fecha ASC");
   	//$grid->db->limit(15,0);
   	    
		$grid->column("Dia"          , "dia","align='left'");          
		$grid->column("Total"  			 , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"  	 , "<number_format><#iva#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac" , "transacciones",'align=right');
		
		$grid->totalizar('iva','grantotal');
		$grid->build();
   	   	
		$grafico = open_flash_chart_object(680,350, site_url("supermercado/cajas/gdiarias/$anio/$caja/$mes"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas Diarias</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function ganuales($anio=''){
		$this->load->library('Graph');
		$this->lang->load('calendar');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
			
		$mSQL = "SELECT caja,SUM(monto)AS grantotal,SUM(impuesto)AS iva,SUM(transac)as transacciones 
		FROM (est_fecha) WHERE fecha>='$fechai' AND fecha<='$fechaf' 
		GROUP BY caja  ORDER BY caja  ";    
		
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			//$nmes[]  =$this->lang->line('cal_'.strtolower($row->mes));
			$caja[]= $row->caja;
			$data_1[] = $row->grantotal;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar_fade( 55, '#424581' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			//$mes=$i+1;
			$bar_1->links[]= site_url("/supermercado/cajas/mensuales/$anio/$caja[$i]");

		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		
		$g->set_x_labels($caja);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend( 'caja', 14,'#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>caja: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else                                                                                           
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	} 
	function gmensuales($anio='',$caja=''){
	  $this->load->library('Graph');
		$this->lang->load('calendar');
		
		if (empty($anio) or empty($caja)) return;
		
    $fechai=$anio.'0101';
    $fechaf=$anio.'1231';
    
    $mSQL = "SELECT DATE_FORMAT(fecha,'%m')AS mes, fecha,
		SUM(monto)AS grantotal,SUM(impuesto)AS IVA,SUM(transac)as transacciones 
		from est_fecha where fecha>='$fechai' AND fecha<='$fechaf' AND caja='$caja'
		GROUP BY  MONTH(fecha)
		ORDER BY fecha ASC";    
    //echo $mSQL;                                                                                    
    
    $maxval=0;
		$query = $this->db->query($mSQL);
    
	foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$mes[]=$row->mes;
			$data_1[]=$row->grantotal;  
		}
		
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar_fade( 55, '#424581' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
		 	$bar_1->links[]= site_url("/supermercado/cajas/diarias/$anio/$caja/$mes[$i]");                                                                                  
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		 $g->title( 'Ventas en el a&ntilde;o'.$anio,'Cajero'.$caja,'{font-size: 16px; color:##00264A}' );
		 $g->data_sets[] = $bar_1;
	               
		 $g->x_axis_colour( '#A6A6A6', '#ADB5C7' );
		 $g->set_x_labels($mes);
		 $g->set_x_label_style( 10, '#000000', 3, 1 );
		 $g->set_x_axis_steps( 10 );
		 $g->set_x_legend('Dias', 14, '#004381' ); 
		 
		 $g->bg_colour = '#FFFFFF';
		 $g->set_tool_tip( '#key#<br>Meses: #x_label# <br>Monto: #tip#' );
		 $g->set_y_max(ceil($maxval/$om));
		 $g->y_label_steps(5);
		 $g->y_axis_colour( '#A6A6A6', '#ADB5C7' );
		 $g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else 
		 $g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		 $g->bg_colour='#FFFFFF';                                                                 
		 echo utf8_encode($g->render());
	}
	function gdiarias($anio='',$caja='',$mes=''){
	  $this->load->library('Graph');
		$this->lang->load('calendar');
		
		if (empty($anio) or empty($caja)) return;
		
    $fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
    $fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
    
    $mSQL = "SELECT DATE_FORMAT(fecha,'%d')AS dia, fecha,
		SUM(monto)AS grantotal,SUM(impuesto)AS IVA,SUM(transac)as transacciones 
		from est_fecha where fecha>='$fechai' AND fecha<='$fechaf' AND caja='$caja'
		GROUP BY  fecha
		ORDER BY fecha ASC";    
    //echo $mSQL;                                                                                    
    
    $maxval=0;
		$query = $this->db->query($mSQL);
    
	foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$dia[]=$row->dia;
			$data_1[]=$row->grantotal;  
		}
		
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar_fade( 55, '#424581' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
		$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));                                                                                  
		 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		 $g->title( 'Ventas de el'.$mes.'/'.$anio,'Cajero'.$caja,'{font-size: 16px; color:##00264A}' );
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
}
?>