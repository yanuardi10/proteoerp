<?php 
class Productos extends Controller {  
  function Productos() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
	}  
	function index(){
		redirect('compras/productos/anuales');
	}
	function anuales($anio='',$descrip='') {
	$this->rapyd->load("datagrid2");
	$this->rapyd->load("dataform");
	$this->load->helper('openflash');

	if(empty($anio))
		if(isset($_POST['anio']))
				$anio=$_POST['anio'];
			else
				$anio=date("Y");
				
	if(empty($descrip))
		if(isset($_POST['descrip']))
				$descrip=$_POST['descrip'];
				

	//if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
	//if(empty($anio))
	//if(isset($_POST['descrip']) AND empty($descrip)) $descrip=$_POST['descrip'];
  //
	////if($this->uri->segment(4))$anio=$this->uri->segment(4);	elseif(isset($_POST['anio']))$anio=$_POST['anio'];
	////if (empty($anio))$anio=date("Y");
	
	$fechai=$anio.'0101';
	$fechaf=$anio.'1231';
	
	$filter = new DataForm('compras/productos/anuales');
	$filter->title('Filtro de Compras Anuales');
			
	$filter->anio = new inputField("A&ntilde;o", "anio");
	$filter->anio->size=4;
	$filter->anio->insertValue=$anio;
	$filter->anio->maxlength=4; 
	
	$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
	$filter->descrip->size=30;
	$filter->descrip->insertValue=$descrip;	

	//$filter->button("btnsubmit", "Buscar", form2uri(site_url('compras/productos/anuales /'),array('anio','descrip')), $position="BL");
	$filter->submit("btnsubmit","Buscar"); 
	$filter->build_form();
	
	$grid = new DataGrid2();	
	
	$select=array( "codigo","descrip",
  "SUM(cantidad)AS cantidad",
  "SUM(importe)AS grantotal", 
	"COUNT(*) AS numfac"); 

	$grid->db->select($select);  
	$grid->db->from("itscst");
	$grid->db->where('fecha >= ',$fechai);  
	$grid->db->where('fecha <= ',$fechaf);  
	$grid->db->where("descrip LIKE '%$descrip%'");
	$grid->db->or_where("codigo LIKE '%$descrip%'");
	$grid->db->groupby("codigo");
	$grid->db->orderby("grantotal DESC");
	
	$grid->column("Producto"      , "descrip","align='left'");
	$grid->column("Total"         , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
	$grid->column("Cantidad" , "numfac"   ,'align=right');
	
	$grid->totalizar('grantotal');
	$grid->build();
	//echo $grid->db->last_query();
	
	$descripencode=raencode($descrip);
	
	$grafico = open_flash_chart_object(680,450, site_url("compras/productos/ganuales/$anio/$descripencode"));
	$data['content']  = $grafico;
	$data['content'] .= $filter->output.$grid->output;
	$data["head"]     = $this->rapyd->get_head();
	$data['title']    = $this->rapyd->get_head()."<h1>Compras Anuales</h1>";
	$this->load->view('view_ventanas', $data);
	}
	
 	function mensuales($anio='',$producto=''){
	  $this->rapyd->load("datagrid2");
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');

		$producto=radecode($producto);
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['codigo']) AND empty($producto)) $producto=$_POST['codigo'];

		if(empty($anio) OR empty($producto)) redirect("compras/productos/anuales/$anio");
		
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
		
		$filter = new DataForm('compras/productos/mensuales');
		$filter->title('Filtro de Ventas Mensuales ');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 
		
		$filter->producto = new inputField("Producto", "codigo");
		$filter->producto->size=10;
		$filter->producto->insertValue=$producto;		 
		$filter->producto->append($cboton);  
		
		//$filter->button("btnsubmit", "Buscar", form2uri(site_url('compras/productos/mensuales/'),array('anio','codigo')), $position="BL");
		$filter->submit("btnsubmit","Buscar");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array( "codigo","descrip","DATE_FORMAT(fecha,'%m/%Y' )as mes",
    "SUM(cantidad)AS cantidad",
    "SUM(importe)AS grantotal", 
		"COUNT(*) AS numfac"); 
		         		
		$grid->db->select($select);  
		$grid->db->from("itscst");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);    
		$grid->db->where('codigo ',$producto); 
		$grid->db->groupby("mes");
		$grid->db->orderby("fecha");
		
		$grid->column("Mes"         , "mes","align='center'");
		$grid->column("Total"         , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Cantidad" , "numfac"   ,'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
		
		$producto=raencode($producto);
		
		$att['title']="Regresar al listado de Productos por a&ntilde;o";
		$salida= anchor("compras/productos/anuales/$anio/"," Productos del a&ntilde;o $anio",$att);
		
		$grafico = open_flash_chart_object(680,450, site_url("compras/productos/gmensuales/$anio/$producto"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$salida.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Compras Mensuales</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function diarias($anio='',$producto='',$mes=''){
	  $this->rapyd->load("datagrid2");
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');
				
		$producto=radecode($producto);
		$meses = array( '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL', '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO', '09' => 'SEPTIEMBRE', '10' => 'OCTBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['codigo']) AND empty($producto)) $producto=$_POST['codigo'];
		if(isset($_POST['mes']) AND empty($mes)) $mes=$_POST['mes'];
		
		//$mes=str_pad($mes, 2, "0", STR_PAD_LEFT);
		
		

		if(empty($anio) OR empty($producto)) redirect("compras/productos/anuales/$anio");
		if(empty($mes))redirect("compras/productos/mensuales/$anio/".raencode($producto));
		
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
		
		$filter = new DataForm('compras/productos/diarias');
		$filter->title('Filtro de Ventas Mensuales ');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		
		$filter->mes = new dropdownField("Mes", "mes");
		$filter->mes->option($mes,$meses[str_pad($mes, 2, "0", STR_PAD_LEFT)]);
		$filter->mes->options($meses);		
		$filter->mes->style ="width:110px;";
		
		$filter->producto = new inputField("Producto", "codigo");
		$filter->producto->size=10;
		$filter->producto->insertValue=$producto;
		$filter->producto->append($cboton);

		//$filter->producto2 = new inputField("", "codigoE");
		//$filter->producto2->insertValue=raencode($producto);
		
		#$filter->button("btnsubmit", "Buscar", form2uri(site_url('compras/productos/mensuales/'),array('anio','codigo')), $position="BL");
		$filter->submit("btnsubmit","Buscar");
		$filter->build_form();

		$grid = new DataGrid2();
		$select=array( "codigo","descrip","DAYOFMONTH(fecha) as dia",
    "SUM(cantidad)AS cantidad",
    "SUM(importe)AS grantotal", 
		"COUNT(*) AS numfac"); 
		         		
		$grid->db->select($select);  
		$grid->db->from("itscst");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);    
		$grid->db->where('codigo ',$producto);
		$grid->db->groupby("dia");
		$grid->db->orderby("fecha");
		
		$grid->column("Dia"           , "dia","align='center'");
		$grid->column("Total"         , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Cantidad" , "numfac"   ,'align=right');
		
		$grid->totalizar('grantotal');
		$grid->build();
		
		//echo $grid->db->last_query();
		
		
		$producto=raencode($producto);
		
		$att['title']="Regresar al listado de meses por a&ntilde;o";
		$salida= anchor("compras/productos/mensuales/$anio/$producto","Meses",$att);
		
		$salida.=" del a&ntilde;o";
		
		$att['title']="Regresar al listado de Productos por a&ntilde;o";
		$salida.= anchor("compras/productos/anuales/$anio/"," $anio",$att);
		
		$grafico = open_flash_chart_object(680,450, site_url("compras/productos/gdiarias/$anio/$producto/$mes"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$salida.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Compras Diarias</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function ganuales($anio='',$descripencode=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$descrip=radecode($descripencode);
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		
		
		if(empty($descrip))
			$where="fecha>='$fechai' AND fecha<='$fechaf'";
		else
			$where="(fecha BETWEEN '$fechai' AND '$fechaf') AND descrip LIKE '%$descrip%'";
		
    $mSQL = "SELECT codigo, LEFT(descrip,20)AS nombre,
    SUM(cantidad)AS cantidad,
    SUM(importe)AS grantotal
    FROM itscst  AS a WHERE (fecha BETWEEN '$fechai' AND '$fechaf') AND ((descrip LIKE '%$descrip%')OR(codigo LIKE '%$descrip%')) 
    GROUP BY codigo ORDER BY grantotal DESC
    LIMIT 10";

		$maxval=0;
		$query = $this->db->query($mSQL);
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$nombre[]=$row->nombre;
			$producto[]=$row->codigo;
			$data_1[]=$row->grantotal;
		}
		
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar(75, '#ADD8E6');
   	$bar_1->key('Compras',10);
   	
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
      $bar_1->links[]= site_url("/compras/productos/mensuales/$anio/".raencode($producto[$i]));
					 	                                                                                  
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Compra de productos en el A&ntilde;o '.$anio,'{font-size: 16px; color:##00264A}' );
			
		$g->data_sets[] = $bar_1;
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 9, '#000000', 2, 1 );
		$g->set_x_axis_steps(10);
		$g->x_axis_colour( '#A6A6A6', '#ADB5C7' );
		$g->set_x_legend('Productos ', 16, '#004381' );  
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Producto: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->y_axis_colour( '#A6A6A6', '#ADB5C7' );
		$g->set_y_legend('Compras x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen compras con los datos seleccionados','{font-size:18px; color: #d01f3c}');
	  $g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	}
	
	function gmensuales($anio='',$producto=''){
		$this->load->library('Graph');
    $this->lang->load('calendar');
		if (empty($anio)) return;
		
		$producto=radecode($producto);
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';

 		$mSQL="SELECT codigo, LEFT(descrip,20)AS nombre,MONTHNAME(fecha)AS mes,MONTH(fecha) AS mmes,
    SUM(cantidad)AS cantidad,
    SUM(importe)AS grantotal
    FROM itscst  AS a
    WHERE (fecha BETWEEN '$fechai' AND '$fechaf') AND codigo='$producto'
    GROUP BY mes ORDER BY fecha";
    		   
		$maxval=0;
		$query = $this->db->query($mSQL);
           
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$nmes[]=$this->lang->line('cal_'.strtolower($row->mes));
			$mmes[]  =$row->mmes;
			$nombre=$row->nombre;
			$data_1[]=$row->grantotal;
		}
		
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar(75, '#ADD8E6');
   	$bar_1->key('Compras',10);
   	
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
      $mes=$mmes[$i];
      $bar_1->links[]= site_url("/compras/productos/diarias/$anio/".raencode($producto)."/".$mes);

		}
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Compra de '.$nombre.' en el A&ntilde;o '.$anio,'{font-size: 16px; color:##00264A}' );
			
		$g->data_sets[] = $bar_1;
		$g->set_x_labels($nmes);
		$g->set_x_label_style( 9, '#000000',3, 1 );
		$g->set_x_axis_steps(10);
		$g->x_axis_colour( '#A6A6A6', '#ADB5C7' );
		$g->set_x_legend('Meses', 16, '#004381' );  
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->y_axis_colour( '#A6A6A6', '#ADB5C7' );
		$g->set_y_legend('Compras x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen compras con los datos seleccionados','{font-size:18px; color: #d01f3c}');
	  $g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
		
	}
	function gdiarias($anio='',$producto='',$mes=''){
		$this->load->library('Graph');
    $this->lang->load('calendar');

		if (empty($anio)) return;
		
		$producto=radecode($producto);
		
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
		
		//echo "fechai=".$fechai;
		//echo "fechaf=".$fechaf;
		
		
    $mSQL = "SELECT codigo, LEFT(descrip,20)AS nombre,DAYOFMONTH(a.fecha) as dia,
    SUM(cantidad)AS cantidad,
    SUM(importe)AS grantotal
    FROM itscst  AS a
    WHERE fecha>='$fechai' AND fecha<='$fechaf' AND codigo='$producto'
    GROUP BY dia ORDER BY fecha";
    //echo $mSQL;
    		
		$maxval=0;
		$query = $this->db->query($mSQL);
		
		
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$fecha[]=$row->dia;
			$nombre=$row->nombre;
			$data_1[]=$row->grantotal;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar(75, '#ADD8E6');
   	$bar_1->key('Compras',10);
   	
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
      					 	                                                                                  
		} 			 
				$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Compra de '.$nombre.' en el mes '.$mes.'/'.$anio,'{font-size: 16px; color:##00264A}' );
		
		$g->data_sets[] = $bar_1;
		$g->set_x_labels($fecha);
		$g->set_x_label_style( 9, '#000000',3, 1 );
		$g->set_x_axis_steps(10);
		$g->x_axis_colour( '#A6A6A6', '#ADB5C7' );
		$g->set_x_legend('Dias', 16, '#004381' );  
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Dia: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->y_axis_colour( '#A6A6A6', '#ADB5C7' );
		$g->set_y_legend('Compras x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen compras con los datos seleccionados','{font-size:18px; color: #d01f3c}');
	  $g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	}
}
?>