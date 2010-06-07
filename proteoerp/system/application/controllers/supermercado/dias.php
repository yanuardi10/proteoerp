<?php
class  Dias extends Controller {  
  
	function Dias() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
		$this->datasis->modulo_id("10C",1);
	}  
	function index(){
		redirect ('supermercado/dias/anuales');
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
		                        
		$filter = new DataForm('supermercado/dias/anuales');
		$filter->title('Filtro de ventas Anuales');
		$filter->script($script, "create");
		$filter->script($script, "modify");	
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum';
		
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/dias/anuales'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("IF(WEEKDAY(fecha)=0,'Lunes',IF(WEEKDAY(fecha)=1,'Martes',IF(WEEKDAY(fecha)=2,'Miercoles',IF(WEEKDAY(fecha)=3,'Jueves',IF(WEEKDAY(fecha)=4,'Viernes',IF(WEEKDAY(fecha)=5,'Sabado','Domingo'))))))AS dia","fecha",
		"SUM(monto)AS grantotal",
		"SUM(impuesto)AS iva",
		"SUM(transac)as transacciones");     
			
		$grid->db->select($select);  
		$grid->db->from("est_fecha");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->groupby("WEEKDAY(fecha)");
		//$grid->db->orderby("fecha ASC");
		//$grid->db->limit(10,0);

		$grid->column("Dia"          , "dia","align='left'");          
		$grid->column("Total"        , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"     , "<number_format><#iva#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac" , "transacciones",'align=right');
		
		$grid->totalizar('iva','grantotal');
		$grid->build();
		
		$grafico = open_flash_chart_object(720,450, site_url("supermercado/dias/ganuales/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas por Dia de la Semana</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function mensuales($anio='',$dia=''){
	  $this->rapyd->load("datagrid2");  
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['mes']) AND empty($mes)) $mes=$_POST['mes'];
		
		//if(empty($mes))redirect("supermercado/ventas/anuales/$anio");	
    
    $script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
    
    $fechai=$anio.'0101';
    $fechaf=$anio.'1231';
						                 
		$filter = new DataForm('supermercado/dias/mensuales');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Ventas Mensuales');
		$filter->script($script, "create");
		$filter->script($script, "modify");
		
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum';
		
		$filter->dia = new dropdownField("Dia","dia");
		$filter->dia->option("0","Lunes");
		$filter->dia->option("1","Martes");
		$filter->dia->option("2","Miercoles");
		$filter->dia->option("3","Jueves");
		$filter->dia->option("4","Viernes");
		$filter->dia->option("5","Sabado");
		$filter->dia->option("6","Domingo");
		$filter->dia->style="width:130px";
		$filter->dia->insertValue=$dia;	
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/dias/mensuales'),array('anio','dia')), $position="BL");
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
		$grid->db->where('WEEKDAY(fecha)',$dia); 
		$grid->db->groupby("MONTH(fecha)");
		//$grid->db->orderby("fecha ASC");
   	//$grid->db->limit(15,0); 
    
		$grid->column("Mes"          , "mes","align='left'");          
		$grid->column("Total"  			 , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"  	 , "<number_format><#iva#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac" , "transacciones",'align=right');
		
		$grid->totalizar('iva','grantotal');
		$grid->build();
   	   	
		$grafico = open_flash_chart_object(680,350, site_url("supermercado/dias/gmensuales/$anio/$dia"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
	  $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas Mensuales</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function ganuales($anio=''){
		$this->load->library('Graph');
		$this->lang->load('calendar');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
			
		$mSQL = "select WEEKDAY(fecha)AS tdia,IF(WEEKDAY(fecha)=0,'Lunes',IF(WEEKDAY(fecha)=1,'Martes',IF(WEEKDAY(fecha)=2,'Miercoles',IF(WEEKDAY(fecha)=3,'Jueves',IF(WEEKDAY(fecha)=4,'Viernes',IF(WEEKDAY(fecha)=5,'Sabado','Domingo'))))))AS dia,fecha,
		SUM(monto)AS grantotal,SUM(impuesto)AS IVA,SUM(transac)as transacciones 
		from est_fecha where fecha>='$fechai' AND fecha<='$fechaf'
		GROUP BY  WEEKDAY(fecha)";                                                 
  	echo $mSQL; 
		
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$tdia[]= $row->tdia;
			$dia[]= $row->dia;
			$data_1[] = $row->grantotal;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar_outline( 50, '#56AC8B', '#3F7E66' );
		//$bar_1 = new bar_outline( 50, '#9933CC', '#8010A0' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			//$mes=$i+1;
			$bar_1->links[]= site_url("/supermercado/dias/mensuales/$anio/$tdia[$i]");

		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		
		$g->set_x_labels($dia);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend( 'Mes ', 14,'#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else                                                                                           
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	} 
	function gmensuales($anio='',$dia=''){
	  $this->load->library('Graph');
		//$this->lang->load('calendar');
		
		//if (empty($mes) or empty($dia)) return;
		
    $fechai=$anio.'0101'; 
    $fechaf=$anio.'1231';
    
    $mSQL = "SELECT fecha,IF(WEEKDAY(fecha)=0,'Lunes',IF(WEEKDAY(fecha)=1,'Martes',IF(WEEKDAY(fecha)=2,'Miercoles',IF(WEEKDAY(fecha)=3,'Jueves',IF(WEEKDAY(fecha)=4,'Viernes',IF(WEEKDAY(fecha)=5,'Sabado','Domingo'))))))AS tdia,
    DATE_FORMAT(fecha,'%m')AS mes,SUM(monto)AS grantotal,SUM(impuesto)AS IVA,SUM(transac)as transacciones 
		FROM est_fecha WHERE fecha>='$fechai' AND fecha<='$fechaf'AND WEEKDAY(fecha)='$dia'
		GROUP BY  MONTH(fecha)
		ORDER BY fecha ASC";    
    //echo $mSQL;                                                                                    
    
    $maxval=0;
		$query = $this->db->query($mSQL);
    
	foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$mes[]=$row->mes;
			$tdia=$row->tdia;
			$data_1[]=$row->grantotal;  
		}
		
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar_outline(50, '#56AC8B', '#3F7E66' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
		 	                                                                                  
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		 $g->title( 'Ventas en el a&ntilde;o '.$anio.' del dia '.$tdia,'{font-size: 16px; color:##00264A}' );
		 $g->data_sets[] = $bar_1;
	               
		 $g->x_axis_colour( '#A6A6A6', '#ADB5C7' );
		 $g->set_x_labels($mes);
		 $g->set_x_label_style( 10, '#000000', 3, 1 );
		 $g->set_x_axis_steps( 10 );
		 $g->set_x_legend('Mes', 14, '#004381' ); 
		 
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
}
?>