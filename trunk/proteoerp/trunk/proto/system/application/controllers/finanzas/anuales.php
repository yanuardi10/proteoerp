<?php
class vendedoresanuales extends Controller {  
  
	function vendedoresanuales() {
	  parent::Controller();
	  $this->load->library("rapyd");
	  $this->load->helper('openflash');
	}  
	  function index(){
	  $this->rapyd->load("dataform");
		$this->load->helper('openflash');
		                            
		if($this->uri->segment(4)) $anio=$this->uri->segment(4); elseif(isset($_POST['anio']))$anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");
		
		$filter = new DataForm('ventas/vendedoresanuales');
		$filter->title('Filtro de Ventas Anuales');
				
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->rule = "max_length[4]"; 
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('ventas/vendedoresanuales /index'),array('anio')), $position="BL");
		$filter->build_form();
   	
		$grafico = open_flash_chart_object(760,300, site_url("ventas/vendedoresanuales/grafico/$anio/"));
		$data['content']  = $grafico;
		$data['content'] .= $filter->output;
		$data["head"]     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head()."<h1>Ventas</h1>";
		$this->load->view('view_ventanas', $data);
	}
	
	function grafico($anio=''){
		$this->load->library('Graph');
		if (empty($anio)) return;
		
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$mSQL = "SELECT a.vd, LEFT(b.nombre, 8)as nombre2,                                            
		 sum(a.totalg*IF(a.tipo_doc='D', -1, 1)) AS grantotal,                              
		 sum(a.totalg*(a.referen IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) AS contado,        
		 sum(a.totalg*(a.referen NOT IN ('E', 'M'))*IF(a.tipo_doc='D', -1, 1)) AS credito     
		 FROM sfac AS a 
		 JOIN vend AS b  ON a.vd=b.vendedor                                                                   
		 WHERE a.tipo_doc<>'X' AND a.fecha>='$fechai' AND a.fecha<='$fechaf'                  
		 GROUP BY a.vd ORDER BY a.vd,grantotal DESC LIMIT 10";    
		
		$maxval=0;
		$query = $this->db->query($mSQL);
		$data_1=$data_2=$data_3=$vendedor=array(); 
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$nombre[]=$row->nombre2;
			$vendedor[]=$row->vd;
			$data_1[]=$row->contado;
			$data_2[]=$row->credito;
			$data_3[]=$row->grantotal;
		}
		
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
      
      $bar_1->links[]= site_url("/ventas/vendedoresmensuales/index/$anio/".$vendedor[$i]);
			$bar_2->links[]= site_url("/ventas/vendedoresmensuales/index/$anio/".$vendedor[$i]);
			$bar_3->links[]= site_url("/ventas/vendedoresmensuales/index/$anio/".$vendedor[$i]);
				 	                                                                                  
		} 			 
		
		$g = new graph();
		$g->title( 'Los 10 vendedores con los indice de ventas m&aacute;s altos en el a&ntilde;o'.$anio,'{font-size: 22px; color:##00264A}' );
		$g->set_is_decimal_separator_comma(1);
		
		$g->data_sets[] = $bar_1;
		$g->data_sets[] = $bar_2;
		$g->data_sets[] = $bar_3;
		
		$g->set_x_labels($nombre);
		$g->set_x_label_style( 10, '#000000', 3, 1 );
		$g->set_x_axis_steps( 10 );
		$g->set_x_legend('Vendedores', 14, '#004381' );        
		
		$g->bg_colour = '#FFFFFF';
		$g->set_tool_tip( '#key#<br>Vendedor: #x_label# <br>Monto: #tip#' );
		$g->set_y_max(ceil($maxval/$om));
		$g->y_label_steps(5);
		$g->set_y_legend('Ventas x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		
		echo utf8_encode($g->render());
	}
	}
?>