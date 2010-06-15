<?php
class Vendedores extends Controller {  
	
	function Vendedores() {
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('openflash');
		$this->datasis->modulo_id(134,1);
	}  
		function index(){
		redirect('/ventas/vendedores/anuales');
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
		
		$filter = new DataForm('ventas/vendedores/anuales');
		$filter->title('Filtro de Ventas Anuales');
		$filter->script($script, "create");
		$filter->script($script, "modify");
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum'; 
		 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('ventas/vendedores/anuales/'),array('anio')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("a.vd",                                            
		"SUM(a.totalg*IF(a.tipo_doc='D', -1, 1)) AS grantotal",  
		"SUM(a.totalg*IF(a.tipo_doc='D', -1, 1))/COUNT(*)AS porcentaje",                            
		"SUM(a.totalg*(a.referen IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) as contado",        
		"SUM(a.totalg*(a.referen NOT IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) as credito", 
		"SUM(a.totals*IF(a.tipo_doc='D',-1,1))AS subtotal", 
		"SUM(a.iva*IF(a.tipo_doc='D',-1,1)) AS impuesto","b.nombre AS vendedor", 
		"COUNT(*) AS numfac");  
		     		
		$grid->db->select($select);  
		$grid->db->from("sfac AS a");
		$grid->db->join("vend AS b" ,"vd=vendedor");
		$grid->db->where('tipo_doc <>', "X");
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);  
		$grid->db->groupby("vd");
		$grid->db->orderby("grantotal DESC");
		//$grid->per_page = 15;
		
		$grid->column("Vendedor"   , "vendedor","align='left'");          
		$grid->column("Sub-Total"  , "<number_format><#subtotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"   , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Contado"    , "<number_format><#contado#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cr&eacute;dito"    , "<number_format><#credito#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Participaci&oacute;n"   , "<number_format><#porcentaje#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('subtotal','impuesto','grantotal','contado','credito');
		$grid->build();

		$grafico = open_flash_chart_object(680,450, site_url("ventas/vendedores/ganuales/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Vendedores</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function mensuales($anio='',$vendedor=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['vd']) AND empty($vendedor)) $vendedor=$_POST['vd'];
		
		if(empty($anio) OR empty($vendedor)) redirect("ventas/vendedores/anuales/$anio");
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
			
		$scli=array(
		'tabla'   =>'vend',
		'columnas'=>array(
		'vendedor' =>'C&oacute;digo Vendedor',
		'nombre'  =>'Nombre'),
		'filtro'  =>array('vendedor'=>'C&oacute;digo Vendedor','nombre'=>'Nombre'),
		'retornar'=>array('vendedor'=>'vd'),
		'titulo'  =>'Buscar Vendedor');
			  
		$cboton=$this->datasis->modbus($scli);
		
		$filter = new DataForm('ventas/vendedores/mensuales');
		$filter->title('Filtro de Ventas Mensuales ');
		$filter->script($script, "create");
		$filter->script($script, "modify");
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum';   
		
		$filter->vendedor = new inputField("Vendedor", "vd");
		$filter->vendedor->size=10;
		$filter->vendedor->insertValue=$vendedor;
		$filter->vendedor->rule = "max_length[4]"; 
		$filter->vendedor->append($cboton); 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('ventas/vendedores/mensuales/'),array('anio','vd')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("vd","fecha","DATE_FORMAT(fecha,'%m/%Y')AS mes",
		"SUM(totalg*IF(tipo_doc='D', -1, 1)) AS grantotal", 
		"SUM(totalg*IF(tipo_doc='D', -1, 1))/ COUNT(*) AS porcentaje",                             
		"SUM(totalg*(referen IN ('E', 'M'))*IF(tipo_doc='D', -1, 1)) as contado",        
		"SUM(totalg*(referen NOT IN ('E', 'M'))*IF(tipo_doc='D', -1, 1)) as credito", 
		"SUM(totals*IF(tipo_doc='D',-1,1))AS subtotal", 
		"SUM(iva*IF(tipo_doc='D',-1,1)) AS impuesto", 
		"COUNT(*) AS numfac");  
		    		
		$grid->db->select($select);  
		$grid->db->from("sfac");
		$grid->db->where('tipo_doc <> ','x');
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->where('vd ', $vendedor);  
		$grid->db->groupby("mes");
		$grid->db->orderby("fecha");
			
		$grid->column("Mes"      ,"mes","align='left'");          
		$grid->column("Sub-Total"  , "<number_format><#subtotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"   , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Contado"    , "<number_format><#contado#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cr&eacute;dito"    , "<number_format><#credito#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Participaci&oacute;n"   , "<number_format><#porcentaje#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('subtotal','impuesto','grantotal','contado','credito');
		$grid->build();
		
		$grafico = open_flash_chart_object(750,350, site_url("ventas/vendedores/gmensuales/$anio/$vendedor"));
		$data['content']  =$grafico;
		$data['content'] .= $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Vendedores</h1>";
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
		//$data['content'] .=form_open('ventas/sfacdesp/procesar',$attributes).$grid->output.form_submit('mysubmit', 'Aceptar').form_close().$script;
		//$data["head"]    =  script("jquery-1.2.6.pack.js");
		//$data["head"]    .= script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		//$this->load->view('view_ventanas', $data);
			
	}
	
	function diarias ($anio='',$vendedor='',$mes=''){
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['mes']) AND empty($mes)) $mes=$_POST['mes'];
		if(isset($_POST['vd']) AND empty($vendedor)) $vendedor=$_POST['vd'];		
		
		//if(empty($anio) OR ($vendedor)) redirect("ventas/vendedoresanuales/index/$anio");
		if(empty($mes))redirect("ventas/vendedores/mensuales/$anio/$vendedor");
		
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
				
		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C<p align="right">&oacute;</p>digo Vendedor',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');
		
		$cboton=$this->datasis->modbus($scli);
		               
		$filter = new DataForm('ventas/vendedores/diarias');
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
		
		$filter->vendedor = new inputField("Vendedor","vd");
		$filter->vendedor->size=10;
		$filter->vendedor->insertValue=$vendedor;
		$filter->vendedor->rule = "max_length[4]"; 
		$filter->vendedor->append($cboton);
				
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('ventas/vendedores/diarias/'),array('anio','vd','mes')), $position="BL");
		$filter->build_form();
		
		$grid = new DataGrid2();
		$select=array("vd", "DATE_FORMAT(fecha,'%d/%m/%Y') AS fecha",                                            
		"SUM(totalg*IF(tipo_doc='D', -1, 1)) AS grantotal", 
		"SUM(totalg*IF(tipo_doc='D', -1, 1))/ COUNT(*) AS porcentaje",                               
		"SUM(totalg*(referen IN ('E', 'M'))*IF(tipo_doc='D', -1, 1)) as contado",        
		"SUM(totalg*(referen NOT IN ('E', 'M'))*IF(tipo_doc='D', -1, 1)) as credito", 
		"FORMAT(sum(totals*IF(tipo_doc='D',-1,1)),2) AS subtotal", 
		"FORMAT(sum(iva*IF(tipo_doc='D',-1,1)),2) AS impuesto", 
		"COUNT(*) AS numfac");  
		
		$grid->db->select($select);  
		$grid->db->from("sfac");
		$grid->db->where('tipo_doc <> ','X');
		$grid->db->where('fecha >= ', $fechai);  
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->where('vd ', $vendedor);  
		$grid->db->groupby("fecha");
		
		$grid->column("Dia"      ,"fecha","align='center'");          
		$grid->column("Sub-Total"  , "<number_format><#subtotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"   , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Total"      , "<number_format><#grantotal#>|2|,|.</number_format>",'align=right');
		$grid->column("Contado"    , "<number_format><#contado#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cr&eacute;dito"    , "<number_format><#credito#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Participaci&oacute;n"   , "<number_format><#porcentaje#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cant. Fact" , "numfac",'align=right');
		
		$grid->totalizar('subtotal','impuesto','grantotal','contado','credito');
		$grid->build();
		
		$grafico = open_flash_chart_object(680,350, site_url("ventas/vendedores/gdiarias/$anio/$vendedor/$mes"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Vendedores</h1>";
		$this->load->view('view_ventanas', $data);
	}
	function ganuales($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT a.vd, b.nombre AS nombre,                                            
		sum(a.totalg*IF(a.tipo_doc='D', -1, 1)) AS grantotal,                              
		sum(a.totalg*(a.referen IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) AS contado,        
		sum(a.totalg*(a.referen NOT IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) AS credito     
		FROM sfac AS a 
		JOIN vend AS b  ON a.vd=b.vendedor                                                                   
		WHERE a.tipo_doc<>'X' AND a.fecha>='$fechai' AND a.fecha<='$fechaf'                  
		GROUP BY a.vd ORDER BY grantotal DESC LIMIT 10";
		
		$maxval=0;
		
		$query=$this->db->query($mSQL);
		
		$data_1=$data_2=$data_3=$vendedor=array(); 
		foreach($query->result() as $row ){
		if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		 $nombre[]=$row->nombre;
		 $vendedor[]=$row->vd;
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
		  
		  $bar_1->links[]= site_url("/ventas/vendedores/mensuales/$anio/".$vendedor[$i]);
			$bar_2->links[]= site_url("/ventas/vendedores/mensuales/$anio/".$vendedor[$i]);
			$bar_3->links[]= site_url("/ventas/vendedores/mensuales/$anio/".$vendedor[$i]);
	                                                                           
		} 			 
		
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
		$g->title('Los 10 vendedores con los indice de ventas m&aacute;s altos en el a&ntilde;o '.$anio,'{font-size: 16px; color:#0F3054}' );
		$g->data_sets[] = $bar_1;
		$g->data_sets[] = $bar_2;
		$g->data_sets[] = $bar_3;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 2, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend('Vendedores', 14, '#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Vendedor: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
		$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';
		 echo utf8_encode($g->render());
	}
	function gmensuales($anio='',$vendedor=''){
		$this->load->library('Graph');
		$this->lang->load('calendar');
		
		if (empty($anio) or empty($vendedor)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT a.vd as vende, b.nombre as nombre2,MONTHNAME(a.fecha) AS mes,MONTH(fecha) AS mmes,
		sum(a.totalg*IF(a.tipo_doc='D', -1, 1)) AS grantotal,
		sum(a.totalg*(a.referen IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) as contado,
		sum(a.totalg*(a.referen NOT IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) as credito
		FROM sfac AS a 
		JOIN vend AS b  ON a.vd=b.vendedor
		WHERE a.tipo_doc<>'X' AND a.fecha>='$fechai' AND a.fecha<='$fechaf' AND a.vd='$vendedor'                  
		GROUP BY MONTH(a.fecha) ORDER BY a.fecha,grantotal DESC";
		
		//echo $mSQL;
		
		$maxval=0; 
		$query = $this->db->query($mSQL);
		  
	 foreach($query->result() as $row ){ if ($row->grantotal>$maxval) $maxval=$row->grantotal;
		  $nmes[]  =$this->lang->line('cal_'.strtolower($row->mes));
		  $mmes[]  =$row->mmes;	
			$data_1[]=$row->contado;
			$data_2[]=$row->credito;
			$data_3[]=$row->grantotal;
		}$nombre  =$row->nombre2;
		
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
			
			$mes=$mmes[$i];
		  $bar_1->links[]= site_url("/ventas/vendedores/diarias/$anio/$vendedor/$mes");
			$bar_2->links[]= site_url("/ventas/vendedores/diarias/$anio/$vendedor/$mes");
			$bar_3->links[]= site_url("/ventas/vendedores/diarias/$anio/$vendedor/$mes");
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
	
	function gdiarias($anio='',$vendedor='',$mes=''){
		$this->load->library('Graph');
		  
		if (empty($mes) or empty($anio)or empty($vendedor)) return;
		  
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
		
		$mSQL = "SELECT a.vd as vende, b.nombre as nombre2,DAYOFMONTH(a.fecha) as dia,           
		sum(a.totalg*IF(a.tipo_doc='D', -1, 1)) AS grantotal,                                    
		sum(a.totalg*(a.referen IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) as contado,            
		sum(a.totalg*(a.referen NOT IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) as credito         
		FROM sfac AS a                                                                           
		JOIN vend AS b  ON a.vd=b.vendedor                                                       
		WHERE a.tipo_doc<>'X' AND a.fecha>='$fechai' AND a.fecha<='$fechaf' AND a.vd='$vendedor' 
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
		}$nombre=$row->nombre2; 
		 
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
		$g->title( 'Ventas de '.$nombre.'  en el mes '.$mes.'/'.$anio,'{font-size: 16px; color:#0F3054}' );   
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