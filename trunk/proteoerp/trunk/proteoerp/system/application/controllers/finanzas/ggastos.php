<?php
class GGastos extends Controller {  
	
	function GGastos() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
	}  
	
	function index(){
		redirect('/finanzas/ggastos/anuales');
	}
	
	function anuales(){
		$this->rapyd->load("datagrid2");	  
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		                            
		if($this->uri->segment(4)) $anio=$this->uri->segment(4); elseif(isset($_POST['anio']))$anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$filter = new DataForm('finanzas/ggastos/anuales');
		$filter->title('Filtro de Gastos Anuales');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('finanzas/ggastos/anuales/'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
    $select=array("fecha","proveed","nombre",
    "SUM(totneto)AS grantotal",
    "COUNT(*) AS numfac");  
		     		
		$grid->db->select($select);  
		$grid->db->from("gser");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->groupby("proveed");
		$grid->db->orderby("grantotal DESC");
		
		$grid->column("Gasto"      , "nombre","align='left'");          
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
  
		$grafico = open_flash_chart_object(680,450, site_url("finanzas/ggastos/ganuales/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Gastos Anuales</h1>";
		$this->load->view('view_ventanas', $data);
	}
	
	function mensuales($anio='',$proveed=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		$proveed=radecode($proveed);
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['proveed']) AND empty($proveed)) $proveed=$_POST['proveed'];		
		
		if(empty($anio) OR empty($proveed)) redirect("finanzas/ggastos/anuales/$anio");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$sprv=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'  =>'Nombre'),
		'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
		'retornar'=>array('proveed'=>'proveed'),
		'titulo'  =>'Buscar Proveedor');
			  
		$cboton=$this->datasis->modbus($sprv);
		
		$filter = new DataForm('finanzas/ggastos/mensuales');
		$filter->title('Filtro de Gastos Mensuales ');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 
		
		$filter->proveedor = new inputField("Proveedor", "proveed");
		$filter->proveedor->size=10;
		$filter->proveedor->insertValue=$proveed;
		$filter->proveedor->rule = "max_length[4]"; 
		$filter->proveedor->append($cboton); 
		
		//$filter->button("btnsubmit", "Buscar", form2uri(site_url('finanzas/ggastos/mensuales/'),array('anio','proveed')), $position="BL");
		$filter->submit("btnsubmit","Buscar");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("fecha","proveed","nombre","DATE_FORMAT(fecha,'%m/%Y') AS mes",
    "SUM(totneto)AS grantotal",
    "COUNT(*) AS numfac");
   		    		
		$grid->db->select($select);  
		$grid->db->from("gser");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->where('proveed',$proveed);
		$grid->db->groupby("MONTH(fecha)");
		$grid->db->orderby("fecha,grantotal DESC");
			
		$grid->column("Mes"      ,"mes","align='left'");          
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
		
		$proveed=raencode($proveed);

		$grafico = open_flash_chart_object(750,350, site_url("/finanzas/ggastos/gmensuales/$anio/$proveed"));
		$data['content']  =$grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Gastos Mensuales</h1>";
		$this->load->view('view_ventanas', $data);
	}
	
	function diarias ($anio='',$proveed='',$mes=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		$proveed=radecode($proveed);
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['mes']) AND empty($mes)) $mes=$_POST['mes'];
		if(isset($_POST['proveed']) AND empty($proveed)) $proveed=$_POST['proveed'];		
		
		//if(empty($anio) OR ($proveed)) redirect("ventas/vendedoresanuales/index/$anio");
		if(empty($mes))redirect("finanzas/ggastos/mensuales/$anio/$proveed");
		
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
				
		$sprv=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'  =>'Nombre'),
		'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
		'retornar'=>array('proveed'=>'proveed'),
		'titulo'  =>'Buscar Proveedor');
		 	  
		$cboton=$this->datasis->modbus($sprv);
			               
		$filter = new DataForm('finanzas/ggastos/diarias');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Gastos Diarios');
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
		
		$filter->proveedor = new inputField("Proveedor", "proveed");
		$filter->proveedor->size=10;
		$filter->proveedor->insertValue=$proveed;
		$filter->proveedor->rule = "max_length[4]"; 
		$filter->proveedor->append($cboton);
				
		//$filter->button("btnsubmit", "Buscar", form2uri(site_url('finanzas/ggastos/diarias/'),array('anio','proveed','mes')), $position="BL");
		$filter->submit("btnsubmit","Buscar");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("fecha","proveed","nombre","DAYOFMONTH(fecha) as dia",
    "SUM(totneto)AS grantotal",
    "COUNT(*) AS numfac");
   		    		
		$grid->db->select($select);  
		$grid->db->from("gser");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->where('proveed',$proveed);  
		$grid->db->groupby("fecha");
		$grid->db->orderby("fecha,grantotal DESC");
		
		$grid->column("Dia"      ,"dia","align='center'");          
		$grid->column("Total"      ,"<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Cant. Fact" ,"numfac",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
		
		$proveed=raencode($proveed);
		
		$grafico = open_flash_chart_object(680,350, site_url("finanzas/ggastos/gdiarias/$anio/$proveed/$mes"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Gastos Diarios</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function ganuales($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
	
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';

		$mSQL = "SELECT fecha,proveed,LEFT(nombre,20) AS nombre,
    SUM(totneto)AS grantotal
    FROM gser
    WHERE fecha>='$fechai' AND fecha<='$fechaf'
    GROUP BY proveed ORDER BY grantotal DESC LIMIT 10";
   	
		$maxval=0;
		$query=$this->db->query($mSQL);
		
		$data_1=$data_2=$data_3=$proveed=array(); 
		foreach($query->result() as $row ){
		if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		 $nombre[]=$row->nombre;
		 $proveed[]=$row->proveed;
		 $data_1[]=$row->grantotal;

		}
						
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar (75, '#033F0A');
		$bar_1->key('Gastos',10);
    
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));  
		  //$bar_1->links[]= site_url("/finanzas/ggastos/mensuales/$anio/".str_replace('/',':slach:',$proveed[$i]));
		  $bar_1->links[]= site_url("/finanzas/ggastos/mensuales/$anio/".raencode($proveed[$i]));
		}
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title('Gastos en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' );
		$g->data_sets[] = $bar_1;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend('Gastos', 14, '#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Gasto: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen gastos en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';
		 echo utf8_encode($g->render());
	}
	function gmensuales($anio='',$proveed=''){
		$this->load->library('Graph');
		$this->lang->load('calendar');
		
		$proveed=radecode($proveed);
		
		if (empty($anio) or empty($proveed)) return;
	
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT fecha,proveed,nombre,MONTHNAME(fecha) AS mes,MONTH(fecha) AS mmes,
		SUM(totneto)AS grantotal
		FROM gser
		WHERE fecha>='$fechai' AND fecha<='$fechaf'AND proveed='$proveed'
		GROUP BY MONTH(fecha) ORDER BY fecha,grantotal DESC";
		//echo $mSQL;
		
		$maxval=0; 
		$query = $this->db->query($mSQL);
		  
		foreach($query->result() as $row ){ if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		  $nmes[]  =$this->lang->line('cal_'.strtolower($row->mes));	
			$data_1[]=$row->grantotal;
			$nombre=$row->nombre;
			$mmes[]  =$row->mmes;
		} 
 			
		$om=1;while($maxval/$om>100) $om=$om*10;
			
		$bar_1 = new bar(75, '#033F0A');
		$bar_1->key('Gastos',10);
	  
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$mes=$mmes[$i];
		  $bar_1->links[]= site_url("/finanzas/ggastos/diarias/$anio/".raencode($proveed)."/$mes");

		} 			 
		//echo 'Valor maxval:'.$maxval;
		$g = new graph();  
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Gastos de '.$nombre.' en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' );
		$g->data_sets[] = $bar_1;
				
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
		$g->title( 'No existen gastos con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                            
		echo utf8_encode($g->render());
	}
	function gdiarias($anio='',$proveed='',$mes=''){
		$this->load->library('Graph');
		
		$proveed=radecode($proveed);

		if (empty($mes) or empty($anio)or empty($proveed)) return;
		  
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
		   
		$mSQL = "SELECT fecha,proveed,nombre,DAYOFMONTH(fecha) as dia,
    SUM(totneto)AS grantotal
    FROM gser
    WHERE fecha>='$fechai' AND fecha<='$fechaf' AND proveed='$proveed'
    GROUP BY fecha ORDER BY fecha,grantotal DESC";                          
	  echo $mSQL;
		 
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
		 if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		 $fecha[]=$row->dia;
		 $data_1[]=$row->grantotal;
		 $nombre=$row->nombre;
		} 
		 
		$om=1;while($maxval/$om>100) $om=$om*10;
		 
		$bar_1 = new bar(75, '#033F0A');
		$bar_1->key('Gastos',10);

		for($i=0;$i<count($data_1);$i++ ){
		 	$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
  	                                                                                  
		} 			 
		 
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Gastos de '.$nombre.'  en el mes '.$mes.'/'.$anio,'{font-size: 16px; color:#0F3054}' );   
		$g->data_sets[] = $bar_1;
		
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
		$g->title( 'No existen gastos con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                             
		echo utf8_encode($g->render());
	}
}
?>