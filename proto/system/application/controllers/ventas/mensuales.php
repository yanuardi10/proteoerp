<?php
class Mensuales extends Controller {

	var $maxval;
	
	function Mensuales(){
		parent::Controller();
		$this->load->helper('text');
		$this->load->library("rapyd");
	}

	function index() {
		$this->rapyd->load("datagrid2");
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		if($this->uri->segment(4)) $mes =$this->uri->segment(4); elseif(isset($_POST['mes'] )) $mes =$_POST['mes'] ; else $mes =date('m');
		if($this->uri->segment(5)) $anio=$this->uri->segment(5); elseif(isset($_POST['anio'])) $anio=$_POST['anio']; else $anio=date('Y');

		$script ='
			$(function() {
				$(".inputnum").numeric(".");
			});
			';

		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
		
		$filter = new DataForm('ventas/mensuales');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de ventas mensuales');
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
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('ventas/mensuales/index'),array('mes','anio')), $position="BL");
		$filter->build_form();
		
		$data=$this->_sincrodata($fechai,$fechaf);
		
		$grid = new DataGrid2('',$data);
		$grid->totalizar('impuesto','cobrado','contado','credito','subtotal','numfac');
		$grid->column("Dia"        ,"dia");
		$grid->column("Sub-Total"  , "<number_format><#subtotal#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Impuesto"   , "<number_format><#impuesto#>|2|,|.</number_format>" ,'align=right');
		$grid->column("Contado"    , "<number_format><#contado#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Credito"    , "<number_format><#credito#>|2|,|.</number_format>"  ,'align=right');
		$grid->column("Cobrado"    , "<number_format><#cobrado#>|2|,|.</number_format>",'align=right');
		$grid->column("% Ventas"   , "<number_format><#porcentaje#>|2|,|.</number_format>",'align=right');
		$grid->column("Cant. Fact" , "numfac"   ,'align=right');
		$grid->build();
		
		$data['content'] =  open_flash_chart_object(680,300, site_url("ventas/mensuales/grafico/$mes/$anio"));
		$data['content'] .= $filter->output.$grid->output;
		$data['title'] = "<h1>Ventas Mensuales</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function grafico($mes=NULL,$anio=NULL){
		if (empty($mes) and empty($anio)) return; 
		$this->load->library('Graph');
		$titulo=$data_1=$data_2=$data_3=$data_tips_1=$data_tips_2=$data_tips_3=array();
		
		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';
		
		$data=$this->_sincrodata($fechai,$fechaf);
		$om=1; while($this->maxval/$om>100) $om=$om*10;
		
		foreach($data AS $row){
			$titulo[]=$row['dia'];
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
		
		$odata_3 = new line_hollow( 2, 3, '0x9933CC');
		$odata_3->key( 'Cobranzas a Cr&eacute;dito', 10 );
		$odata_3->data = $data_3;
		$odata_3->tips = $data_tips_3;
		
		$g = new Graph();
		if($this->maxval>0){
			$g->title( 'Ventas del mes '.$mes.'/'.$anio,'{font-size:18px; color: #d01f3c}');
			$g->set_is_decimal_separator_comma(1);
			         
			$g->data_sets[] = $odata_3;
			$g->data_sets[] = $odata_1;
			$g->data_sets[] = $odata_2;
			
			$g->set_y_max(ceil($this->maxval/$om));
			$g->y_label_steps( 5 );
			$g->set_x_labels($titulo);
			$ejey=number_format($om,0,'','.');
			$g->set_y_legend( 'Venta X '.$ejey.' (Bs)',14,'0x639F45' );
			$g->set_x_legend( 'D&iacute;as del mes '.$mes, 14,'0x639F45' );
			$g->set_tool_tip( '#key#<br>D&iacute;a: #x_label# <br>Monto: #tip#' );
		}else
			$g->title( 'No existen ventas en la Fecha Seleccionada','{font-size:18px; color: #d01f3c}');
		  $g->bg_colour='#FFFFFF';
		//$g->set_bg_image(site_url('/images/ventafon.png'), 'center', 'middle' );
		echo utf8_encode($g->render());
	}
	
	function _sincrodata($fechai,$fechaf){
		$maxval=0;
		
		$mSQL_1="SELECT fecha, DATE_FORMAT(fecha,'%d') AS dia,
		SUM(totals*IF(tipo_doc='D',-1,1)) AS subtotal,
		SUM(iva*IF(tipo_doc='D',-1,1)) AS impuesto,
		SUM(totalg*(referen IN ('E','M'))*IF(tipo_doc='D',-1,1)) AS contado,
		SUM(totalg*(referen NOT IN ('E','M'))*IF(tipo_doc='D',-1,1)) AS credito,
		SUM(totalg*IF(tipo_doc='D', -1, 1))/COUNT(*)AS porcentaje, 
		COUNT(*) AS numfac FROM sfac WHERE tipo_doc<>'X' AND fecha>='$fechai' AND fecha<='$fechaf' GROUP BY fecha";
		
		$mSQL_2="SELECT fecha, DATE_FORMAT(fecha,'%d') AS dia , SUM(monto) AS cobrado FROM sfpa 
		WHERE tipo_doc<>'X' AND fecha>='$fechai' AND fecha<='$fechaf' AND tipo_doc='AB'
		GROUP BY fecha ORDER BY fecha";
		
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
			if($cant_1==0) $row_1['dia']=32;
			if($cant_2==0) $row_2['dia']=32;

			while($i_1<$cant_1 or $i_2<$cant_2){
				if ($row_1['dia']==$row_2['dia']){      //Dias con ventas y cobranzas
					$comodin['dia']        = $row_1['dia'];
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
					if ($comodin['dia']==$row_2['dia']) $row_2['dia']=32;
					if ($comodin['dia']==$row_1['dia']) $row_1['dia']=32;
					$i_1++;
					$i_2++;
				}elseif($row_1['dia'] > $row_2['dia'] ){ //Dias con solo cobranzas
					$comodin['dia']        = $row_2['dia'];
					$comodin['fecha']      =$row_2['fecha'];
					$comodin['subtotal']   =0;
					$comodin['impuesto']   =0;
					$comodin['contado']    =0; 
					$comodin['credito']    =0; 
					$comodin['cobrado']    =$row_2['cobrado']; 
					$comodin['numfac']     =0; 
					$comodin['porcentaje'] =0;
					$row_2 = $query_2->next_row('array');
					if ($comodin['dia']==$row_2['dia']) $row_2['dia']=32;
					$i_2++;
				} else {                                 //Dias con solo ventas
					$comodin['dia']        = $row_1['dia'];
					$comodin['fecha']      =$row_1['fecha'];
					$comodin['subtotal']   =$row_1['subtotal'];
					$comodin['impuesto']   =$row_1['impuesto'];
					$comodin['contado']    =$row_1['contado'];
					$comodin['credito']    =$row_1['credito'];
					$comodin['cobrado']    =0;
					$comodin['numfac']     =$row_1['numfac'];
					$comodin['porcentaje'] =$row_1['porcentaje'];
					$row_1 = $query_1->next_row('array');
					if ($comodin['dia']==$row_1['dia']) $row_1['dia']=32;
					$i_1++;
				}
				if ($comodin['contado']    > $maxval) $maxval=$comodin['contado'];
				if ($comodin['credito']    > $maxval) $maxval=$comodin['credito'];
				if ($comodin['cobrado']    > $maxval) $maxval=$comodin['cobrado'];
				if ($comodin['porcentaje'] > $maxval) $maxval=$comodin['porcentaje'];
				$data[]=$comodin;
				if(count($data)>=32) break;
			}
		}
		$this->maxval=$maxval;
		return($data);
	}
}
?>