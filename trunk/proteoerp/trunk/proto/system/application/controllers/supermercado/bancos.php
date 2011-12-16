<?php
class  bancos extends Controller {  
  
	function bancos() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
		$this->datasis->modulo_id("10F",1);
	}  
	function index(){
		redirect ('supermercado/bancos/anuales');
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
		                        
		$filter = new DataForm('supermercado/bancos/anuales');
		$filter->title('Filtro de Bancos');
		$filter->script($script, "create");
		$filter->script($script, "modify");
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum'; 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/bancos/anuales'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.fecha","a.concep","b.banco",
		"SUM(a.monto)AS grantotal",
		"SUM(a.transac)AS transacciones");
		
		$grid->db->select($select);
		$grid->db->from("est_pago as a");
		$grid->db->join("banc as b","a.concep=b.codbanc");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->groupby("a.concep");
    $grid->db->orderby("grantotal DESC");
    
		$grid->column("Banco"         , "banco","align='left'");
		$grid->column("Total"  				, "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac"  , "transacciones",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
		//echo $grid->db->last_query();
		$grafico = open_flash_chart_object(720,450, site_url("supermercado/bancos/ganuales/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Tipos de Bancos</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function mensuales($anio='',$concep=''){
	  $this->rapyd->load("datagrid2");  
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
				
		//if(empty($anio)OR ($concep))redirect("supermercado/bancos/anuales/$anio");	
    
    $fechai=$anio.'0101';
    $fechaf=$anio.'1231';
						                 
		$filter = new DataForm('supermercado/bancos/mensuales');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Ventas Mensuales');
		
		$filter->anio = new inputField("A&ntilde;o", "anio");         
		//$filter->anio->in='concep';
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		 
		$filter->concep = new dropdownField("Banco","concep");
		$filter->concep->options("SELECT codbanc, banco FROM banc GROUP BY codbanc ORDER BY codbanc");                                 
		$filter->concep->insertValue=$concep;
		$filter->concep->style="width:200px";
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/bancos/mensuales'),array('anio','concep')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.fecha","a.concep","b.nombre","DATE_FORMAT(fecha,'%m') AS mes",
		"SUM(a.monto)AS grantotal",
		"SUM(a.transac)AS transacciones");
		
		$grid->db->select($select);  
		$grid->db->from("est_pago as a");
		$grid->db->join("banc as b","a.concep=b.codbanc");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->where('a.concep',$concep);  
		$grid->db->groupby("MONTH(fecha)");
    $grid->db->orderby("mes ASC");
   	$grid->db->limit(15,0);
   	    
		$grid->column("Mes"       	  , "mes","align='left'");          
		$grid->column("Total"  				, "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac"  , "transacciones",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
   	   	
		$grafico = open_flash_chart_object(680,350, site_url("supermercado/bancos/gmensuales/$anio/$concep"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas de Mensuales</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function diarias($anio='',$concep='',$mes=''){
	  $this->rapyd->load("datagrid2");  
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
				
    $fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
    $fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
						                 
		$filter = new DataForm('supermercado/bancos/diarias');
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
		 
		$filter->concep = new dropdownField("Banco","concep");
		$filter->concep->options("SELECT codbanc, banco FROM banc GROUP BY codbanc ORDER BY codbanc");                                 
		$filter->concep->insertValue=$concep;
		$filter->concep->style="width:200px";
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/bancos/diarias'),array('anio','concep','mes')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.fecha","a.concep","b.nombre","DATE_FORMAT(fecha,'%d') AS dia",
		"SUM(a.monto)AS grantotal",
		"SUM(a.transac)AS transacciones");
		
		$grid->db->select($select);  
		$grid->db->from("est_pago as a");
		$grid->db->join("banc as b","a.concep=b.codbanc");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->where('a.concep',$concep);  
		$grid->db->groupby("fecha");
    $grid->db->orderby("dia ASC");
   	//$grid->db->limit(15,0);
   	    
		$grid->column("Dia"          , "dia","align='left'");          
		$grid->column("Total"  			 , "<number_format><#grantotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Cant.Transac" , "transacciones",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
   	   	
		$grafico = open_flash_chart_object(680,350, site_url("supermercado/bancos/gdiarias/$anio/$concep/$mes"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas Diarias</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function ganuales($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
			
		$mSQL = "SELECT a.fecha,a.concep,SUM(a.monto)AS grantotal,b.banco
		FROM est_pago  as a JOIN banc as b ON a.concep=b.codbanc
		WHERE fecha>='$fechai' AND fecha<='$fechaf'
		GROUP BY a.concep ORDER BY grantotal DESC";    
		echo $mSQL; 
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$concep[]= $row->concep;
			$nombre[]= $row->banco;
			$data_1[] = $row->grantotal;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar( 55, '#07A8E7' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			//$mes=$i+1;
			$bar_1->links[]= site_url("/supermercado/bancos/mensuales/$anio/$concep[$i]");

		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend( 'Tipos de Bancos', 14,'#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>concep: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else                                                                                           
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	} 
	function gmensuales($anio='',$concep=''){
	  $this->load->library('Graph');
		$this->lang->load('calendar');
		
		if (empty($anio) or empty($concep)) return;
		
    $fechai=$anio.'0101';
    $fechaf=$anio.'1231';
    
    $mSQL = "SELECT a.fecha,a.concep,SUM(a.monto)AS grantotal,b.banco,DATE_FORMAT(fecha,'%m') AS mes
		FROM est_pago  as a JOIN banc as b ON a.concep=b.codbanc
		WHERE fecha>='$fechai' AND fecha<='$fechaf' AND a.concep='$concep'
		GROUP BY MONTH(fecha) ORDER BY mes";    
    //echo $mSQL;                                                                                    
    
    $maxval=0;
		$query = $this->db->query($mSQL);
    
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$mes[]=$row->mes;
			$nombre=$row->banco;
			$data_1[]=$row->grantotal;  
		}
		
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar( 55, '#07A8E7' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
		 	$bar_1->links[]= site_url("/supermercado/bancos/diarias/$anio/$concep/$mes[$i]");                                                                                  
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		 $g->title( 'Ventas en el a&ntilde;o '.$anio.' Banco '.$nombre,'{font-size: 16px; color:##00264A}' );
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
	function gdiarias($anio='',$concep='',$mes=''){
	  $this->load->library('Graph');
		$this->lang->load('calendar');
		
		if (empty($anio) or empty($concep)) return;
		
    $fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
    $fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
    
    $mSQL = "SELECT a.fecha,a.concep,SUM(a.monto)AS grantotal,b.nombre,DATE_FORMAT(fecha,'%d') AS dia
		FROM est_pago  as a JOIN banc as b ON a.concep=b.codbanc
		WHERE fecha>='$fechai' AND fecha<='$fechaf' AND a.concep='$concep'
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
		
		$bar_1 = new bar( 55, '#07A8E7' );
		$bar_1->key('Monto',10);
		
		for($i=0;$i<count($data_1);$i++ ){
		$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.'))); 
		}                                                                                 
		 			 
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		 $g->title( 'Ventas de el'.$mes.'/'.$anio,'concep de pago'.$concep,'{font-size: 16px; color:##00264A}' );
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