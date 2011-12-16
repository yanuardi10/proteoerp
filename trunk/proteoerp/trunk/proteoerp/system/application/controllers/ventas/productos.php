<?php
class Productos extends Controller {

	function Productos() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
	}  
		function index(){
		redirect('/ventas/productos/anuales');
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

		if($this->uri->segment(4)) $anio=$this->uri->segment(4); elseif(isset($_POST['anio']))$anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$filter = new DataForm('ventas/productos/anuales');
		$filter->title('Filtro de Ventas Anuales');
		$filter->script($script, "create");
		$filter->script($script, "modify");
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->rule = "max_length[4]";
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum'; 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('ventas/productos/anuales/'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.codigo","b.descrip AS nombre","a.cantidad AS cantidad",
    "SUM(a.venta)AS grantotal",  
    "COUNT(*) AS numfac");  
		     		
		$grid->db->select($select);  
		$grid->db->from("costos AS a");
		$grid->db->join("sinv AS b" ,"a.codigo=b.codigo");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->groupby("a.codigo");
		$grid->db->orderby("grantotal DESC");
		$grid->per_page=15;
		
		$grid->column("Producto"   , "nombre","align='left'");          
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Cantidad"   , "<number_format><#cantidad#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
  
		$grafico = open_flash_chart_object(680,450, site_url("ventas/productos/ganuales/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas Anuales</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function mensuales($anio='',$producto=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		$producto=radecode($producto);
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['codigo']) AND empty($producto)) $producto=$_POST['codigo'];		
		
		if(empty($anio) OR empty($producto)) redirect("ventas/productos/anuales/$anio");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$sinv=array(
		'tabla'   =>'sinv',
		'columnas'=>array(
		'codigo' =>'C&oacute;digo Producto',
		'descrip'  =>'Nombre'),
		'filtro'  =>array('codigo'=>'C&oacute;digo Producto','descrip'=>'Nombre'),
		'retornar'=>array('codigo'=>'codigo'),
		'titulo'  =>'Buscar Vendedor');
			  
		$cboton=$this->datasis->modbus($sinv);
		
		$filter = new DataForm('ventas/productos/mensuales');
		$filter->title('Filtro de Ventas Mensuales ');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->rule = "max_length[4]"; 
		
		$filter->producto = new inputField("Producto", "codigo");
		$filter->producto->size=10;
		$filter->producto->insertValue=$producto;
		//$filter->producto->rule = "max_length[4]"; 
		$filter->producto->append($cboton); 
		
		//$filter->button("btnsubmit", "Buscar", form2uri(site_url('ventas/productos/mensuales/'),array('anio','codigo')), $position="BL");
		$filter->submit("btnsubmit","Buscar");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.codigo AS codigo","b.descrip AS nombre","a.cantidad AS cantidad","DATE_FORMAT(fecha,'%m/%Y')AS mes",
    "SUM(a.venta)AS grantotal", 
		"COUNT(*) AS numfac");  
		    		
		$grid->db->select($select);  
		$grid->db->from("costos AS a");
		$grid->db->join("sinv AS b" ,"a.codigo=b.codigo");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->where('a.codigo ', $producto);  
		$grid->db->groupby("mes");
		$grid->db->orderby("fecha");
		
		$grid->column("Fecha"      , "mes","align='left'");          
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Cantidad"   , "<number_format><#cantidad#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
		
		$producto=raencode($producto);
		
		$grafico = open_flash_chart_object(680,350, site_url("ventas/productos/gmensuales/$anio/$producto"));
		$data['content']  =$grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas Mensuales</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function diarias ($anio='',$producto='',$mes=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		$producto=radecode($producto);
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['mes']) AND empty($mes)) $mes=$_POST['mes'];
		if(isset($_POST['codigo']) AND empty($producto)) $producto=$_POST['codigo'];		
		
		//if(empty($anio) OR ($producto)) redirect("ventas/productosanuales/index/$anio");
		if(empty($mes))redirect("ventas/productos/mensuales/$anio/$producto");
		
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
				
		$sinv=array(
		'tabla'   =>'sinv',
		'columnas'=>array(
		'codigo' =>'C&oacute;digo Producto',
		'descrip'  =>'Nombre'),
		'filtro'  =>array('codigo'=>'C&oacute;digo Producto','descrip'=>'Nombre'),
		'retornar'=>array('codigo'=>'codigo'),
		'titulo'  =>'Buscar Vendedor');
		
		$cboton=$this->datasis->modbus($sinv);
		               
		$filter = new DataForm('ventas/productos/diarias');
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
		$filter->anio->rule = "max_length[4]"; 
		
		$filter->producto = new inputField("Producto", "codigo");
		$filter->producto->size=10;
		$filter->producto->insertValue=$producto;
		//$filter->producto->rule = "max_length[4]"; 
		$filter->producto->append($cboton); 
				
		//$filter->button("btnsubmit", "Buscar", form2uri(site_url('ventas/productos/diarias/'),array('anio','codigo','mes')), $position="BL");
		$filter->submit("btnsubmit","Buscar");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.codigo AS codigo","b.descrip AS nombre","a.cantidad AS cantidad","DATE_FORMAT(fecha,'%m/%Y')AS mes",
    "SUM(a.venta)AS grantotal", 
		"COUNT(*) AS numfac");  
		    		
		$grid->db->select($select);  
		$grid->db->from("costos AS a");
		$grid->db->join("sinv AS b" ,"a.codigo=b.codigo");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->where('a.codigo ', $producto);   
		$grid->db->groupby("fecha");
		$grid->db->orderby("fecha");
		
		$grid->column("Fecha"      , "mes","align='left'");          
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Cantidad"   , "<number_format><#cantidad#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
		
		$producto=raencode($producto);
		
		$grafico = open_flash_chart_object(680,350, site_url("ventas/productos/gdiarias/$anio/$producto/$mes"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas Diarias</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function ganuales($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT a.codigo AS codigo,b.descrip AS nombre,LEFT(b.descrip,20)AS nombre2,a.cantidad AS cantidad,
    SUM(a.venta)AS grantotal 
    FROM  costos AS a 
    JOIN  sinv AS b ON a.codigo=b.codigo
    WHERE a.fecha >='$fechai' AND a.fecha <='$fechaf' 
    GROUP BY a.codigo ORDER BY grantotal DESC LIMIT 10";
	  echo $mSQL;
		$maxval=0;
		
		$query=$this->db->query($mSQL);
		
		$data_1=$producto=array(); 
		foreach($query->result() as $row ){
		if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		 $nombre[]=$row->nombre2;
		 $producto[]=$row->codigo;
		 $data_1[]=$row->grantotal;
		}
						
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar (75, '#0F235F'); 
		$bar_1->key('Ventas',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));  
		  //$bar_1->links[]= site_url("/ventas/productos/mensuales/$anio/".str_replace('/',':slash:',$producto[$i]));
		  $bar_1->links[]= site_url("/ventas/productos/mensuales/$anio/".raencode($producto[$i]));
		} 			 
			$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title('Los 10 productos con los indice de ventas m&aacute;s altos en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' );
		$g->data_sets[] = $bar_1;

		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend('Productos', 14, '#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Producto: #x_label# <br>Monto: #tip#');
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';
		 echo utf8_encode($g->render());
	}
	function gmensuales($anio='',$producto=''){
		$this->load->library('Graph');
		$this->lang->load('calendar');
		
		$producto=radecode($producto);
		
		if (empty($anio) or empty($producto)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT a.codigo AS codigo,b.descrip AS nombre,LEFT(b.descrip,20)AS nombre2,a.cantidad AS cantidad,MONTHNAME(a.fecha) AS mes,
    SUM(a.venta)AS grantotal 
    FROM  costos AS a 
    JOIN  sinv AS b ON a.codigo=b.codigo
    WHERE a.fecha >='$fechai' AND a.fecha <='$fechaf' AND a.codigo='$producto'
    GROUP BY mes ORDER BY fecha";
		//echo $mSQL;
		
		$maxval=0; $query = $this->db->query($mSQL);
		  
	 foreach($query->result() as $row ){ if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		  $nmes[]  =$this->lang->line('cal_'.strtolower($row->mes));	
			$data_1[]=$row->grantotal;
			
		}$nombre  =$row->nombre2;
		
		$om=1;while($maxval/$om>100) $om=$om*10;
			
		$bar_1 = new bar (75, '#0F235F'); 
		$bar_1->key('Ventas',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$mes=$i+1;
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));  
		  $bar_1->links[]= site_url("/ventas/productos/diarias/$anio/".raencode($producto)."/$mes");			 	                                                                                  
		} //$bar_1->links[]= site_url("/ventas/productos/mensuales/$anio/".$producto[$i]);				 
		
		$g = new graph();  
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de '.$nombre.' en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' );
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
		$g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                            
		  echo utf8_encode($g->render());
	}
	function gdiarias($anio='',$producto='',$mes=''){
		$this->load->library('Graph');
		
		$producto=radecode($producto);
		  
		if (empty($mes) or empty($anio)or empty($producto)) return;
		  
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31'; 
		   
		$mSQL = "SELECT a.codigo AS codigo,b.descrip AS nombre,DAYOFMONTH(a.fecha) as dia,a.cantidad AS cantidad,
    SUM(a.venta)AS grantotal 
    FROM  costos AS a 
    JOIN  sinv AS b ON a.codigo=b.codigo
    WHERE a.fecha >='$fechai' AND a.fecha <='$fechaf' AND a.codigo='$producto'
    GROUP BY fecha ORDER BY grantotal DESC";                          
	  //echo $mSQL;
		 
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){
		 if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		 $fecha[]=$row->dia;
		 $data_1[]=$row->grantotal;
		}$nombre=$row->nombre; 
		 
		$om=1;while($maxval/$om>100) $om=$om*10;
		 
		$bar_1 = new bar (75, '#0F235F'); 
		$bar_1->key('Ventas',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));  	 	                                                                                  
		} 	
		 
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Ventas de '.$nombre.'  en el mes '.$mes.'/'.$anio,'{font-size: 16px; color:#0F3054}' );   
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
		$g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                             
		echo utf8_encode($g->render());
	}
}
?>