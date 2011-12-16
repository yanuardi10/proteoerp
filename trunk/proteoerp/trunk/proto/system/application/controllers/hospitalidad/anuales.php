<?php
class Anuales extends Controller {

	var $maxval;
	
	function Anuales(){
		parent::Controller();
		$this->load->helper('text');
		$this->load->library("rapyd");
		$this->datasis->modulo_id(110,1);
	}
	function index() {
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		                            
		if($this->uri->segment(4)) $anio=$this->uri->segment(4); elseif(isset($_POST['anio'])) $anio=$_POST['anio'];
		if (empty($anio))$anio=date("Y");

		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$filter = new DataForm('hospitalidad/anuales');
		$filter->title('Filtro de ventas Anuales');		
		
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;
		//$filter->anio->onkeypress="return acceptNum(event)";
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('hospitalidad/anuales/index'),array('anio')), $position="BL");
		$filter->build_form();
		
		$data=$this->_sincrodata($fechai,$fechaf);
		
		$grid = new DataGrid2('',$data);
		$grid->totalizar('impuesto','cobrado','contado','credito','subtotal','numfac');
		$grid->column("Mes"        , "mes");
		$grid->column("Sub-Total"  , "<number_format><#subtotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"   , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Contado"    , "<number_format><#contado#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Credito"    , "<number_format><#credito#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cobrado"    , "<number_format><#cobrado#>|2|,|.</number_format>",'align=right');
		$grid->column("% Ventas"   , "<number_format><#porcentaje#>|2|,|.</number_format>",'align=right');
		$grid->column("Cant. Fact" , "numfac"   ,'align=right');
		$grid->build();
				
    $data['content'] = open_flash_chart_object(680,300, site_url("hospitalidad/anuales/grafico/$anio"));
		$data['content'] .= $filter->output.$grid->output;
		$data['title'] = $this->rapyd->get_head()."<h1>Ventas Anuales</h1>";
		$data['script']='<script type="text/javascript">
		$(document).ready(function() {
			$("#anio").keypress(function(event) {
				acceptNum(evt);
				return false;
			});
		});
	  var nav4 = window.Event ? true : false;
	  function acceptNum(evt)
    {	
    		var key = nav4 ? evt.which : evt.keyCode;	
    		return (key <= 13 || (key >= 48 && key <= 57));
    		
    }
		</script>';
		$data["head"]    = script("jquery-1.2.6.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
 function grafico($anio=''){
		if (empty($anio)) return; 
		$this->load->library('Graph');
		$titulo=$data_1=$data_2=$data_3=$data_tips_1=$data_tips_2=$data_tips_3=array();
		                           	                            
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';
		
		$data=$this->_sincrodata($fechai,$fechaf);
		$om=1; while($this->maxval/$om>100) $om=$om*10;
		//print_r($data);
		
		foreach($data AS $row){
			$titulo[]=$row['mes'];
			$data_1[]=$row['contado']/$om;
			$data_2[]=$row['credito']/$om;
			$data_3[]=$row['cobrado']/$om;
			
			$data_tips_1[]=graph::esc(number_format($row['contado'],2,',','.'));
			$data_tips_2[]=graph::esc(number_format($row['credito'],2,',','.'));
			$data_tips_3[]=graph::esc(number_format($row['cobrado'],2,',','.'));
			} 

		$odata_1 = new bar_fade( 50, '0xCC3399' );
		$odata_1->key( 'Contado', 10 );
		$odata_1->data = $data_1;
		$odata_1->tips = $data_tips_1;
		
		$odata_2 = new bar_fade( 50, '0x80a033' );
		$odata_2->key( 'Cr&eacute;dito', 10 );
		$odata_2->data = $data_2;
		$odata_2->tips = $data_tips_2;
		
		$odata_4 = new line_hollow( 2, 3, '0x9933CC');
		$odata_4->key( 'Cobranzas a Cr&eacute;dito', 10 );     
		$odata_4->data = $data_3;
		$odata_4->tips = $data_tips_3;
		
		for($i=0;$i<count($data_1);$i++ ){
			$mes=$i+1;      			
			$odata_1->links[]= site_url("/hospitalidad/mensuales/index/$mes/$anio");
			$odata_2->links[]= site_url("/hospitalidad/mensuales/index/$mes/$anio");
			$odata_3->links[]= site_url("/hospitalidad/mensuales/index/$mes/$anio");		 	                                                                                  
		}
	
		$g = new Graph();
		$g->set_is_decimal_separator_comma(1);

		if($this->maxval>0){
		$g->title( 'Ventas en el a&ntilde;o '.$anio,'{font-size:18px; color: #d01f3c}');
		
		$g->data_sets[] = $odata_1;
		$g->data_sets[] = $odata_2;
		$g->data_sets[] = $odata_4;
		
		$g->set_y_max(ceil($this->maxval/$om));
		$g->y_label_steps( 5 );
		$g->set_x_labels($titulo);
		$ejey=number_format($om,0,'',' ');
		$g->set_y_legend( 'Venta X '.$ejey.' (Bs)',14,'0x639F45' );
		$g->set_x_legend( 'Meses',14,'0x639F45' );
		$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Monto: #tip#');
		}else
		$g->title( 'NO existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';
		echo utf8_encode($g->render());
	}
	function _sincrodata($fechai,$fechaf){
		$maxval=0;
		
		$mSQL_1="SELECT fecha, DATE_FORMAT(fecha,'%m')AS mes,
		SUM(stotal*IF(tipo='D',-1,1)) AS subtotal,
		SUM(impuesto*IF(tipo='D',-1,1)) AS impuesto,
		SUM(gtotal*IF(tipo='D',-1,1)) AS contado,
		SUM(gtotal*IF(tipo='D',-1,1)) AS credito,
		SUM(gtotal*IF(tipo='D', -1, 1))/COUNT(*)AS porcentaje, 
		COUNT(*) AS numfac FROM rfac WHERE tipo<>'X' AND fecha>='$fechai' AND fecha<='$fechaf' GROUP BY MONTH(fecha)";
		
		$mSQL_2="SELECT fecha, DATE_FORMAT(fecha,'%m') AS mes,SUM(monto) AS cobrado FROM sfpa 
		WHERE tipo_doc<>'X' AND fecha>='$fechai' AND fecha<='$fechaf' AND tipo_doc='AB'
		GROUP BY MONTH(fecha) ORDER BY fecha";
	  //echo $mSQL_2;			
		$DB2 = $this->load->database('default', TRUE); 
		
		$query_1=$this->db->query($mSQL_1);
		$query_2=$DB2->query($mSQL_2);
		
		$i_1=$i_2=0;
		$data=$comodin=array();
		$cant_1=$query_1->num_rows();
		$cant_2=$query_2->num_rows();
		if ($cant_1> 0 or  $cant_2> 0)  {
			$row_1 = $query_1->first_row('array');
			$row_2 = $query_2->first_row('array');
			if($cant_1==0) $row_1['mes']=13;
			if($cant_2==0) $row_2['mes']=13;

			while($i_1<$cant_1 or $i_2<$cant_2){
				if ($row_1['mes']==$row_2['mes']){//Meses con ventas y cobranzas
					$comodin['mes']        = $row_1['mes'];
					$comodin['fecha']      =$row_1['fecha'];
					$comodin['subtotal']   =$row_1['subtotal'];
					$comodin['impuesto']   =$row_1['impuesto'];
					$comodin['contado']    =$row_1['contado'];
					$comodin['credito']    =$row_1['credito'];
					$comodin['cobrado']    =$row_2['cobrado'];
					$comodin['numfac']     =$row_1['numfac'];
					$comodin['porcentaje'] =$row_1['porcentaje'];
					$row_1 = $query_1->next_row('array');
					$row_2 = $query_2->next_row('array');
					if ($comodin['mes']==$row_2['mes']) $row_2['mes']=13;
					if ($comodin['mes']==$row_1['mes']) $row_1['mes']=13;
					$i_1++;
					$i_2++;
				}elseif($row_1['mes'] > $row_2['mes'] ){ //Meses con solo cobranzas
					$comodin['mes']     = $row_2['mes'];
					$comodin['fecha']    =$row_2['fecha'];
					$comodin['subtotal'] =0;
					$comodin['impuesto'] =0;
					$comodin['contado']  =0; 
					$comodin['credito']  =0; 
					$comodin['cobrado']  =$row_2['cobrado']; 
					$comodin['numfac']   =0;  
					$comodin['porcentaje'] =0;
					$row_2 = $query_2->next_row('array');
					if ($comodin['mes']==$row_2['mes']) $row_2['mes']=13;
					$i_2++;
				} else {                                //Meses con solo ventas
					$comodin['mes']        = $row_1['mes'];
					$comodin['fecha']      =$row_1['fecha'];
					$comodin['subtotal']   =$row_1['subtotal'];
					$comodin['impuesto']   =$row_1['impuesto'];
					$comodin['contado']    =$row_1['contado'];
					$comodin['credito']    =$row_1['credito'];
					$comodin['cobrado']    =0;
					$comodin['numfac']     =$row_1['numfac'];
					$comodin['porcentaje'] =$row_1['porcentaje'];
					$row_1 = $query_1->next_row('array');
					if ($comodin['mes']==$row_1['mes']) $row_1['mes']=13;
					$i_1++;
				}
				if ($comodin['contado']    > $maxval) $maxval=$comodin['contado'];
				if ($comodin['credito']    > $maxval) $maxval=$comodin['credito'];
				if ($comodin['cobrado']    > $maxval) $maxval=$comodin['cobrado'];
				if ($comodin['porcentaje'] > $maxval) $maxval=$comodin['porcentaje'];
				
				$data[]=$comodin;
				if(count($data)>=13) break;
			}
		}
		$this->maxval=$maxval;
		return($data);
	}
 }
 ?>