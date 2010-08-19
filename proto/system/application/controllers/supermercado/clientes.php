<?php
class Clientes extends Controller {  
  
	function Clientes() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
		$this->datasis->modulo_id(131,1);
	}  
	function index(){
		redirect ('supermercado/clientes/anuales');
	}
	function anuales(){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		                            
		if($this->uri->segment(4))$anio=$this->uri->segment(4);	elseif(isset($_POST['anio']))$anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		                        
		$filter = new DataForm('supermercado/clientes/anuales');
		$filter->title('Filtro de Ventas Anuales');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/clientes/anuales'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("cliente,nombres,
		SUM(gtotal)AS grantotal,
		SUM(impuesto)AS impuesto");
 			
		$grid->db->select($select);  
		$grid->db->from("viefac");
		$grid->db->where('tipo<>','X');
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->groupby("cliente");
		$grid->db->orderby("grantotal DESC");
		$grid->db->limit(10,0);

		$grid->column("Cliente"    , "nombres","align='left'");          
		$grid->column("Impuesto"   , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		
		$grid->totalizar('impuesto','grantotal');
		$grid->build();
		//echo $grid->db->last_query();
		$grafico = open_flash_chart_object(720,450, site_url("supermercado/clientes/ganuales/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Clientes</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function mensuales($anio='',$cliente=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['cod_cli']) AND empty($cliente)) $cliente=$_POST['cod_cli'];		
		
		if (empty($anio) OR empty($cliente)) redirect("supermercado/clientes/anuales/$anio");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');
		
		$cboton=$this->datasis->modbus($scli);
		
		$filter = new DataForm('supermercado/clientes/mensuales');
		$filter->title('Filtro de ventas Mensuales');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 
		
		$filter->cliente = new inputField("Cliente", "cod_cli");
		$filter->cliente->size=10;
		$filter->cliente->insertValue=$cliente;
		$filter->cliente->rule = "max_length[4]"; 
		$filter->cliente->append($cboton); 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/clientes/mensuales'),array('anio','cod_cli')), $position="BL");	
		$filter->build_form();
		
		$grid = new DataGrid2();
			
		$select=array("cliente,nombres,DATE_FORMAT(fecha,'%m')AS mes,
		SUM(gtotal)AS grantotal,
		SUM(impuesto)AS impuesto");
 			
		$grid->db->select($select);  
		$grid->db->from("viefac");
		$grid->db->where('tipo<>','X');
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf); 
		$grid->db->where('cliente',$cliente);
		$grid->db->groupby("MONTH(fecha)");
		$grid->db->orderby("mes");
		
		$grid->column("Mes"      , "mes","align='left'");          
		$grid->column("Impuesto" , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Total"    , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		
		$grid->totalizar('impuesto','grantotal');
		$grid->build();
		//echo $grid->db->last_query();
		$grafico = open_flash_chart_object(750,350, site_url("supermercado/clientes/gmensuales/$anio/$cliente"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Clientes</h1>";
		$this->load->view('view_ventanas', $data);
	}
	//function diarias($anio='',$cliente='',$mes=''){
	//	$this->rapyd->load("datagrid2");
	//	$this->rapyd->load("dataform");
	//	$this->load->helper('openflash');
	//	
	//	if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
	//	if(isset($_POST['mes']) AND empty($mes)) $mes=$_POST['mes'];
	//	if(isset($_POST['cod_cli']) AND empty($cliente)) $cliente=$_POST['cod_cli'];		
	//	
	//	if(empty($mes))redirect("supermercado/clientes/mensuales/$anio/$cliente");
	//	
	//	$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
	//	$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
	//					
	//	$scli=array(
	//	'tabla'   =>'scli',
	//	'columnas'=>array(
	//	'cliente' =>'C&oacute;digo Cliente',
	//	'nombre'=>'Nombre',
	//	'contacto'=>'Contacto'),
	//	'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
	//	'retornar'=>array('cliente'=>'cod_cli'),
	//	'titulo'  =>'Buscar Cliente');
	//	
	//	$cboton=$this->datasis->modbus($scli);
	//	               
	//	$filter = new DataForm('supermercado/clientesdiarias');
	//	$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
	//	$filter->title('Filtro de supermercado Diarias');
	//	$filter->mes = new dropdownField("Mes/A&ntilde;o", "mes");  
	//	
	//	for($i=1;$i<13;$i++) 
	//	$filter->mes->option(str_pad($i, 2, '0', STR_PAD_LEFT),str_pad($i, 2, '0', STR_PAD_LEFT));  
	//	$filter->mes->size=2;
	//	$filter->mes->style='';
	//	$filter->mes->insertValue=$mes;	
	//	
	//	$filter->anio = new inputField("A&ntilde;o", "anio");
	//	$filter->anio->in='mes';
	//	$filter->anio->size=4;
	//	$filter->anio->insertValue=$anio;
	//	$filter->anio->maxlength=4; 
	//	
	//	$filter->cliente = new inputField("Cliente", "cod_cli");
	//	$filter->cliente->size=10;
	//	$filter->cliente->insertValue=$cliente;
	//	$filter->cliente->rule = "max_length[4]"; 
	//	$filter->cliente->append($cboton);
	//	
	//	$filter->button("btnsubmit", "Buscar", form2uri(site_url('supermercado/clientes/diarias/'),array('anio','cod_cli','mes')), $position="BL");
	//	$filter->build_form();
	//	
	//	$grid = new DataGrid2();
	//	$select=array("cliente,nombres,DATE_FORMAT(fecha,'%m')AS mes,
	//	SUM(gtotal)AS grantotal,
	//	SUM(impuesto)AS impuesto");
 	//		
	//	$grid->db->select($select);  
	//	$grid->db->from("viefac");
	//	$grid->db->where("tipo<>'X' AND fecha>='$fechai' AND fecha<='$fechaf'AND cliente=$cliente");
	//	$grid->db->groupby("fecha");
	//	$grid->db->orderby("fecha");
	//	
	//	$grid->column("Mes"      , "mes","align='left'");          
	//	$grid->column("Impuesto" , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
	//	$grid->column("Total"    , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
	//	
	//	$grid->totalizar('impuesto','grantotal');
	//	$grid->build();
	//	
	//	$grafico = open_flash_chart_object(720,350, site_url("supermercado/clientes/gdiarias/$anio/$cliente/$mes"));
	//	$data['content']  =$grafico;
	//	$data['content'] .=  $filter->output.$grid->output;
	//	$data["head"]     = $this->rapyd->get_head();
	//	$data['title']    = $this->rapyd->get_head()."<h1>Clientes</h1>";
	//	$this->load->view('view_ventanas', $data);
	//}
	function ganuales($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT cliente,nombres, SUM(gtotal)AS grantotal, SUM(impuesto)AS impuesto 
		FROM viefac
		WHERE tipo<>'X' AND fecha>='$fechai' AND fecha<='$fechaf' 
		GROUP BY cliente ORDER BY grantotal DESC LIMIT 10"; 
		
		$maxval=0;
		$query = $this->db->query($mSQL);
		 
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$cliente[] = $row->cliente;
			$nombre[]  = $row->nombres;
			$data_1[]  = $row->grantotal;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar(75, '#8E4B97');	
		$bar_1->key('Total',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));		
			$bar_1->links[]= site_url("/supermercado/clientes/mensuales/$anio/".$cliente[$i]);

		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title( 'Los 10 clientes con mas compras en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend( 'Clientes ', 14,'#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Cliente: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('supermercado x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else                                                                                           
		$g->title('No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	} 
	function gmensuales($anio='',$cliente=''){
		$this->load->library('Graph');
		
		if (empty($anio) or empty($cliente)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		    
		$mSQL = "SELECT cliente,nombres,DATE_FORMAT(fecha,'%m')AS mes, SUM(gtotal)AS grantotal, SUM(impuesto)AS impuesto 
		FROM viefac                                                                    
		WHERE tipo<>'X' AND fecha>='$fechai' AND fecha<='$fechaf' AND cliente='$cliente'                 
		GROUP BY MONTH(fecha) ORDER BY fecha";   
		//echo $mSQL;
		
		$maxval=0; $query = $this->db->query($mSQL);
		
		foreach($query->result() as $row ){ if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$nombre=$row->nombres;
			$mes[]=$row->mes;
			$data_1[]=$row->grantotal;
		}
		
		$om=1;while($maxval/$om>100) $om=$om*10;
			
		$bar_1 = new bar(75, '#8E4B97');	
		$bar_1->key('Total',10);
		  
		for($i=0;$i<count($data_1);$i++ ){
		  	
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
		  //$bar_1->links[]= site_url("supermercado/clientes/diarias/$anio/$cliente/$mes[$i]");

		} 			 
		
		$g = new graph();  
		$g->set_is_decimal_separator_comma(1);
		
		if($maxval>0){
		$g->title( 'Compras de el cliente '.$nombre.' en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' ); 
		$g->data_sets[] = $bar_1;
		
		$g->set_x_labels($mes);
		$g->set_x_label_style( 10, '#000000', 3, 1 );
		$g->set_x_axis_steps( 10 ); 
		$g->set_x_legend('Meses', 14, '#004381' );                      
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('supermercado x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	}
	
	//function gdiarias($anio='',$cliente='',$mes=''){
	//	$this->load->library('Graph');
	//	
	//	if (empty($mes) or empty($anio)or empty($cliente)) return;
	//	
	//	$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
	//	$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
	//	
	//	$mSQL = "SELECT cod_cli,nombre,fecha,DAYOFMONTH(fecha) as dia,                                             
	//	sum(totalg*IF(tipo_doc='D', -1, 1)) AS grantotal,                              
	//	sum(totalg*(referen IN ('E', 'M'))*IF(tipo_doc='D', -1, 1)) AS contado,        
	//	sum(totalg*(referen NOT IN ('E', 'M'))*IF(tipo_doc='D', -1, 1)) AS credito     
	//	FROM sfac                                                                      
	//	WHERE tipo_doc<>'X' AND fecha>='$fechai' AND fecha<='$fechaf' AND cod_cli='$cliente'                 
	//	GROUP BY fecha ORDER BY fecha,grantotal DESC LIMIT 31";    
	//	//echo $mSQL;
	//	
	//	$maxval=0;
	//	$query = $this->db->query($mSQL);
	//	
	//	foreach($query->result() as $row ){
	//		if ($row->grantotal>$maxval) $maxval=$row->grantotal;
	//		$fecha[]=$row->dia;
	//		$data_1[]=$row->contado;
	//		$data_2[]=$row->credito;
	//		$data_3[]=$row->grantotal;
	//	}$nombre=$row->nombre;
	//	
	//	$om=1;while($maxval/$om>100) $om=$om*10;
	//	
	//	$bar_1 = new bar(75, '#8E4B97');
	//	$bar_2 = new bar(75, '#9933CC');
	//	$bar_3 = new bar(75, '#639F45');
	//	
	//	$bar_1->key('Contado',10);
	//	$bar_2->key('Credito',10);
	//	$bar_3->key('Total'  ,10);
	//	
	//	for($i=0;$i<count($data_1);$i++ ){
	//		$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
	//		$bar_2->add_data_tip($data_2[$i]/$om, graph::esc( number_format($data_2[$i],2,',','.')));
	//		$bar_3->add_data_tip($data_3[$i]/$om, graph::esc( number_format($data_3[$i],2,',','.')));
	//                                                                            
	//} 			 
	//	$g = new graph();
	//	$g->set_is_decimal_separator_comma(1);
	//	if($maxval>0){
	//	$g->title( 'Compras de el cliente '.$nombre.'  en el mes '.$mes.'/'.$anio,'{font-size: 16px; color:#0F3054}' );   
	//	$g->data_sets[] = $bar_1;
	//	$g->data_sets[] = $bar_2;
	//	$g->data_sets[] = $bar_3;
	//	
	//	$g->set_x_labels($fecha);
	//	$g->set_x_label_style( 10, '#000000', 3, 1 );
	//	$g->set_x_axis_steps( 10 );
	//	$g->set_x_legend('Dias', 14, '#004381' ); 
	//	
	//	$g->bg_colour = '#FFFFFF';
	//	$g->set_tool_tip( '#key#<br>Dia: #x_label# <br>Monto: #tip#' );
	//	$g->set_y_max(ceil($maxval/$om));
	//	$g->y_label_steps(5);
	//	$g->set_y_legend('supermercado x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
	//	}else                                                                                            
	//	$g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
	//	$g->bg_colour='#FFFFFF';                                                                 
	//	echo utf8_encode($g->render());
	//} 
}
?>