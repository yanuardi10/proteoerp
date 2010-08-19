<?php
class gmesoneros extends Controller {  
	
	function gmesoneros() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
		$this->datasis->modulo_id(134,1);
	}  
		function index(){
		redirect('/hospitalidad/gmesoneros/anuales');
	}
		function anuales(){
		$this->rapyd->load("datagrid2");	  
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		                            
		if($this->uri->segment(4)) $anio=$this->uri->segment(4); elseif(isset($_POST['anio']))$anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$filter = new DataForm('hospitalidad/gmesoneros/anuales');
		$filter->title('Filtro de Mesonero Anuales');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('hospitalidad/gmesoneros/anuales/'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.mesonero", "b.nombre as meso",                                           
		"SUM(a.gtotal*IF(a.tipo='D', -1, 1)) AS grantotal",  
		"SUM(a.gtotal*IF(a.tipo='D', -1, 1))/COUNT(*)AS porcentaje",                            
		"SUM(a.gtotal*IF(a.tipo='D', -1, 1)) as contado",        
		"SUM(a.gtotal*IF(a.tipo='D', -1, 1)) as credito", 
		"SUM(a.stotal*IF(a.tipo='D',-1,1))AS subtotal", 
		"SUM(a.impuesto*IF(a.tipo='D',-1,1)) AS impuesto","b.nombre AS vendedor", 
		"COUNT(*) AS numfac");  
		     		
		$grid->db->select($select);  
		$grid->db->from("rfac AS a");
		$grid->db->join("meso AS b" ,"a.mesonero=b.mesonero");
		$grid->db->where('a.tipo<>','X');
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->groupby("a.mesonero");
		$grid->db->orderby("grantotal DESC");
		
		$grid->column("Mesonero"   , "meso","align='left'");          
		$grid->column("Sub-Total"  , "<number_format><#subtotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"   , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Contado"    , "<number_format><#contado#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cr&eacute;dito"    , "<number_format><#credito#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Participaci&oacute;n","<number_format><#porcentaje#>|2|,|.</number_format>",'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('subtotal','impuesto','grantotal','contado','credito');
		$grid->build();
  
		$grafico = open_flash_chart_object(680,450, site_url("hospitalidad/gmesoneros/ganuales/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery-1.2.6.pack.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Mesoneros</h1>";
		$this->load->view('view_ventanas', $data);
	}
		function mensuales($anio='',$mesonero=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['mesonero']) AND empty($mesonero)) $mesonero=$_POST['mesonero'];		
		
		if(empty($anio) OR empty($mesonero)) redirect("hospitalidad/gmesoneros/anuales/$anio");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$meso=array(
		'tabla'   =>'meso',
		'columnas'=>array(
		'mesonero' =>'C&oacute;digo Mesonero',
		'nombre'=>'Nombre'),
		'filtro'  =>array('mesonero'=>'C&oacute;digo Mesonero','nombre'=>'Nombre'),
		'retornar'=>array('mesonero'=>'mesonero'),
		'titulo'  =>'Buscar Mesonero');
		
		$cboton=$this->datasis->modbus($meso);
		
		$filter = new DataForm('hospitalidad/gmesoneros/mensuales');
		$filter->title('Filtro de Mesoneros Mensuales ');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;  
		
		$filter->mesoneros = new inputField("Mesoneros", "mesonero");
		$filter->mesoneros->size=10;
		$filter->mesoneros->insertValue=$mesonero;
		$filter->mesoneros->rule = "max_length[4]"; 
		$filter->mesoneros->append($cboton);
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('hospitalidad/gmesoneros/mensuales/'),array('anio','mesonero')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("mesonero","fecha","DATE_FORMAT(fecha,'%m')AS mes",
		"SUM(gtotal*IF(tipo='D', -1, 1)) AS grantotal", 
		"SUM(gtotal*IF(tipo='D', -1, 1))/COUNT(*) AS porcentaje",                             
		"SUM(gtotal*IF(tipo='D', -1, 1)) as contado",        
		"SUM(gtotal*IF(tipo='D', -1, 1)) as credito", 
		"SUM(stotal*IF(tipo='D',-1,1))AS subtotal", 
		"SUM(impuesto*IF(tipo='D',-1,1)) AS impuesto", 
		"COUNT(*) AS numfac");  
		    		
		$grid->db->select($select);  
		$grid->db->from("rfac");
		$grid->db->where('tipo<>','X');
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->where('mesonero',$mesonero); 
		$grid->db->groupby("mes");
		$grid->db->orderby("fecha");
			
		$grid->column("Mes"          ,"mes","align='left'");          
		$grid->column("Sub-Total"    , "<number_format><#subtotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"     , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Total"        , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Contado"      , "<number_format><#contado#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cr&eacute;dito"      , "<number_format><#credito#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Participaci&oacute;n", "<number_format><#porcentaje#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cant. Fact"   , "numfac",'align=right');
		
		$grid->totalizar('subtotal','impuesto','grantotal','contado','credito');
		$grid->build();
		
		$grafico = open_flash_chart_object(750,350, site_url("hospitalidad/gmesoneros/gmensuales/$anio/$mesonero"));
		$data['content']  =$grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Mesoneros</h1>";
		$this->load->view('view_ventanas', $data);
		
		//$script ='<script type="text/javascript">
		//$(document).ready(function() {
		//	$("#todos").click(function() { $("#adespacha").checkCheckboxes();   });
		//	$("#nada").click(function()  { $("#adespacha").unCheckCheckboxes(); });
		//	$("#alter").click(function() { $("#adespacha").toggleCheckboxes();  });
		//});
		//</script>';
    //
		//$attributes = array('id' => 'adespacha');
    //
		//if($grid->recordCount>0)
		//$data['content'] .=form_open('hospitalidad/sfacdesp/procesar',$attributes).$grid->output.form_submit('mysubmit', 'Aceptar').form_close().$script;
		//$data["head"]    =  script("jquery-1.2.6.pack.js");
		//$data["head"]    .= script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		//$this->load->view('view_ventanas', $data);
			
	}
	
	function diarias ($anio='',$mesonero='',$mes=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['mes']) AND empty($mes)) $mes=$_POST['mes'];
		if(isset($_POST['mesonero']) AND empty($mesonero)) $mesonero=$_POST['mesonero'];		
		
		//if(empty($anio) OR ($mesonero)) redirect("hospitalidad/gmesonerosanuales/index/$anio");
		if(empty($mes))redirect("hospitalidad/gmesoneros/mensuales/$anio/$mesonero");
		
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
				
		$meso=array(
		'tabla'   =>'meso',
		'columnas'=>array(
		'mesonero' =>'C&oacute;digo Mesonero',
		'nombre'=>'Nombre'),
		'filtro'  =>array('mesonero'=>'C&oacute;digo Mesonero','nombre'=>'Nombre'),
		'retornar'=>array('mesonero'=>'mesonero'),
		'titulo'  =>'Buscar Mesonero');
		
		$cboton=$this->datasis->modbus($meso);
		               
		$filter = new DataForm('hospitalidad/gmesoneros/diarias');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Mesoneros Diarios');
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
		
		$filter->mesoneros = new inputField("Mesoneros", "mesonero");
		$filter->mesoneros->size=10;
		$filter->mesoneros->insertValue=$mesonero;
		$filter->mesoneros->rule = "max_length[4]"; 
		$filter->mesoneros->append($cboton);
				
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('hospitalidad/gmesoneros/diarias/'),array('anio','mesonero','mes')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("mesonero", "DATE_FORMAT(fecha,'%d') AS dia",                                            
		"SUM(gtotal*IF(tipo='D', -1, 1)) AS grantotal", 
		"SUM(gtotal*IF(tipo='D', -1, 1))/ COUNT(*) AS porcentaje",                               
		"SUM(gtotal*IF(tipo='D', -1, 1)) as contado",        
		"SUM(gtotal*IF(tipo='D', -1, 1)) as credito", 
		"FORMAT(sum(stotal*IF(tipo='D',-1,1)),2) AS subtotal", 
		"FORMAT(sum(impuesto*IF(tipo='D',-1,1)),2) AS impuesto", 
		"COUNT(*) AS numfac");  
		
		$grid->db->select($select);  
		$grid->db->from("rfac");
		$grid->db->where('tipo<>','X');
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->where('mesonero',$mesonero);  
		$grid->db->groupby("fecha");
		
		$grid->column("Dia"        ,"dia","align='center'");          
		$grid->column("Sub-Total"  , "<number_format><#subtotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"   , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Contado"    , "<number_format><#contado#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cr&eacute;dito"    , "<number_format><#credito#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Participaci&oacute;n", "<number_format><#porcentaje#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('subtotal','impuesto','grantotal','contado','credito');
		$grid->build();
		
		$grafico = open_flash_chart_object(680,350, site_url("hospitalidad/gmesoneros/gdiarias/$anio/$mesonero/$mes"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Mesoneros</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function ganuales($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT a.mesonero, b.nombre AS nombre,                                            
		sum(a.gtotal*IF(a.tipo='D', -1, 1)) AS grantotal,                              
		sum(a.gtotal*IF(a.tipo='D', -1, 1)) AS contado,        
		sum(a.gtotal*IF(a.tipo='D', -1, 1)) AS credito     
		FROM rfac AS a 
		JOIN meso AS b  ON a.mesonero=b.mesonero                                                                   
		WHERE a.tipo<>'X' AND a.fecha>='$fechai' AND a.fecha<='$fechaf'                  
		GROUP BY a.mesonero ORDER BY grantotal DESC LIMIT 10";
		echo $mSQL;
		
		$maxval=0;
		
		$query=$this->db->query($mSQL);
		
		$data_1=$data_2=$data_3=$mesonero=array(); 
		foreach($query->result() as $row ){
		if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		 $nombre[]=$row->nombre;
		 $mesonero[]=$row->mesonero;
		 $data_1[]=$row->contado;
		 $data_2[]=$row->credito;
		 $data_3[]=$row->grantotal;
		}
						
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar (75, '#0053A4');
		$bar_2 = new bar (75, '#9933CC');
		$bar_3 = new bar (75, '#639F45');
		 
		$bar_1->key('Contado',10);
		$bar_2->key('Credito',10);
		$bar_3->key('Total'  ,10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_2->add_data_tip($data_2[$i]/$om, graph::esc( number_format($data_2[$i],2,',','.')));
			$bar_3->add_data_tip($data_3[$i]/$om, graph::esc( number_format($data_3[$i],2,',','.')));
		  
		  $bar_1->links[]= site_url("/hospitalidad/gmesoneros/mensuales/$anio/".$mesonero[$i]);
			$bar_2->links[]= site_url("/hospitalidad/gmesoneros/mensuales/$anio/".$mesonero[$i]);
			$bar_3->links[]= site_url("/hospitalidad/gmesoneros/mensuales/$anio/".$mesonero[$i]);
	                                                                           
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title('Los 10 mesoneros con los indice de ventas m&aacute;s altos en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' );
		$g->data_sets[] = $bar_1;
		$g->data_sets[] = $bar_2;
		$g->data_sets[] = $bar_3;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend('Mesoneros', 14, '#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Vendedor: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('hospitalidad x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ventas con los datos seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';
		 echo utf8_encode($g->render());
	}
	function gmensuales($anio='',$mesonero=''){
		$this->load->library('Graph');
		$this->lang->load('calendar');
		
		if (empty($anio) or empty($mesonero)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT a.mesonero, b.nombre as nombre,MONTHNAME(a.fecha) AS mes,
		sum(a.gtotal*IF(a.tipo='D', -1, 1)) AS grantotal,
		sum(a.gtotal*IF(a.tipo='D', -1, 1)) as contado,
		sum(a.gtotal*IF(a.tipo='D', -1, 1)) as credito
		FROM rfac AS a 
		JOIN meso AS b  ON a.mesonero=b.mesonero
		WHERE a.tipo<>'X' AND a.fecha>='$fechai' AND a.fecha<='$fechaf' AND a.mesonero='$mesonero'                  
		GROUP BY MONTH(a.fecha) ORDER BY a.fecha,grantotal DESC";
		
		echo $mSQL;
		
		$maxval=0; 
		$query = $this->db->query($mSQL);
		  
	 foreach($query->result() as $row ){ if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		  $nmes[]  =$this->lang->line('cal_'.strtolower($row->mes));	
			$data_1[]=$row->contado;
			$data_2[]=$row->credito;
			$data_3[]=$row->grantotal;
		}$nombre  =$row->nombre;
		
		$om=1;while($maxval/$om>100) $om=$om*10;
			
		$bar_1 = new bar(75, '#0053A4');
		$bar_2 = new bar(75, '#9933CC');
		$bar_3 = new bar(75, '#639F45');
		
		$bar_1->key('Contado',10);
		$bar_2->key('Credito',10);
		$bar_3->key('Total'  ,10);
		  
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_2->add_data_tip($data_2[$i]/$om, graph::esc( number_format($data_2[$i],2,',','.')));
			$bar_3->add_data_tip($data_3[$i]/$om, graph::esc( number_format($data_3[$i],2,',','.')));
			
			$mes=$i+1;
		  $bar_1->links[]= site_url("/hospitalidad/gmesoneros/diarias/$anio/$mesonero/$mes");
			$bar_2->links[]= site_url("/hospitalidad/gmesoneros/diarias/$anio/$mesonero/$mes");
			$bar_3->links[]= site_url("/hospitalidad/gmesoneros/diarias/$anio/$mesonero/$mes");
		} 			 
		$g = new graph();  
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de '.$nombre.' en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' );
		$g->data_sets[] = $bar_1;
		$g->data_sets[] = $bar_2;
		$g->data_sets[] = $bar_3;
				
		$g->set_x_labels($nmes);
		$g->set_x_label_style( 10, '#000000', 3, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend('Meses', 16, '#004381' );
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                            
		  echo utf8_encode($g->render());
	}
	function gdiarias($anio='',$mesonero='',$mes=''){
		$this->load->library('Graph');
		  
		if (empty($mes) or empty($anio)or empty($mesonero)) return;
		  
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
		   
		$mSQL = "SELECT a.mesonero as vende, b.nombre as nombre,DAYOFMONTH(a.fecha) as dia,                              
		sum(a.gtotal*IF(a.tipo='D', -1, 1)) AS grantotal,                                    
		sum(a.gtotal*IF(a.tipo='D', -1, 1)) as contado,            
		sum(a.gtotal*IF(a.tipo='D', -1, 1)) as credito         
		FROM rfac AS a                                                                           
		JOIN meso AS b  ON a.mesonero=b.mesonero                                                       
		WHERE a.tipo<>'X' AND a.fecha>='$fechai' AND a.fecha<='$fechaf' AND a.mesonero='$mesonero'    
		GROUP BY a.fecha ORDER BY a.fecha,grantotal DESC";                          
	  //echo $mSQL;
		 
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
		 if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		 $fecha[]=$row->dia;
		 $data_1[]=$row->contado;  
		 $data_2[]=$row->credito;  
		 $data_3[]=$row->grantotal;
		}$nombre=$row->nombre; 
		 
		$om=1;while($maxval/$om>100) $om=$om*10;
		 
		$bar_1 = new bar(75, '#0053A4');
		$bar_2 = new bar(75, '#9933CC');
		$bar_3 = new bar(75, '#639F45');
		
		$bar_1->key('Contado',10);
		$bar_2->key('Credito',10);
		$bar_3->key('Total'  ,10);
		 
		for($i=0;$i<count($data_1);$i++ ){
		 	$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
		 	$bar_2->add_data_tip($data_2[$i]/$om, graph::esc( number_format($data_2[$i],2,',','.')));
		 	$bar_3->add_data_tip($data_3[$i]/$om, graph::esc( number_format($data_3[$i],2,',','.')));
                                                                                
		} 			 
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Mesonero '.$nombre.'  en el mes '.$mes.'/'.$anio,'{font-size: 16px; color:#0F3054}' );   
		$g->data_sets[] = $bar_1;
		$g->data_sets[] = $bar_2;
		$g->data_sets[] = $bar_3;
		
		$g->set_x_labels($fecha);
		$g->set_x_label_style( 10, '#000000', 3, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend('Dias', 14, '#004381' ); 
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Dia: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                             
		echo utf8_encode($g->render());
	}
}
?>