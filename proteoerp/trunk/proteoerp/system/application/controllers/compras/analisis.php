<?php
class Analisis extends Controller {

	function Analisis(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index() {
		$this->rapyd->load("dataform");
		$this->load->helper('openflash');
		
		$mes =$this->uri->segment(4);
		$anio=$this->uri->segment(5);
		if (empty($mes)) $mes =date("m");
		if (empty($anio))$anio=date("Y");

		$fechai=$anio.$mes.'01';
		$fechaf=$anio.$mes.'31';

		$filter = new DataForm();
		$filter->title('Filtro de ventas mensuales');
		$filter->mes = new dropdownField("Mes/A&ntilde;o", "mes");  
		for($i=1;$i<13;$i++) $filter->mes->option(str_pad($i, 2, '0', STR_PAD_LEFT),str_pad($i, 2, '0', STR_PAD_LEFT));  
		$filter->mes->size=2;
		$filter->mes->style='';
		$filter->mes->insertValue=$mes;	
		
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->in='mes';
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->rule = "max_length[4]"; 
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('compras/analisis/index'),array('mes','anio')), $position="BL");
		$filter->build_form();
		
		$data['forma']  = '';
		$data['lista'] =  $filter->output;
		$data['lista'] .= open_flash_chart_object(800,300, site_url("compras/analisis/departa/$mes/$anio"));
		$data['titulo'] = $this->rapyd->get_head()."<center><h2>VENTAS MENSUALES</h2></center>";
		$this->layout->buildPage('ventas/view_ventas', $data);
	
	}
	
	function departa(){
		$this->load->library('Graph');
		$data = $titu =array();
		$mes =$this->uri->segment(4);                             
		$anio=$this->uri->segment(5);
		
		if (empty($mes) and empty($anio)) return;                             
		$fechai=$anio.$mes.'01';
		$fechaf=$anio.$mes.'31';
		
		//8471.49.00.00
		
		$mSQL = "SELECT f.descrip etiqueta, sum(b.importe) total
			FROM itscst b JOIN scst a ON a.control=b.control 
			JOIN sinv c ON b.codigo=c.codigo
			JOIN grup d ON d.grupo=c.grupo
			JOIN line e ON d.linea=e.linea 
			JOIN dpto f ON e.depto=f.depto 
			WHERE a.tipo_doc IN ('FC', 'NC','NE') AND a.fecha>=$fechai AND a.fecha<=$fechaf
			GROUP BY f.depto";
		//echo $mSQL;
		$maxval =0;
		$query = $this->db->query($mSQL);
		foreach($query->result() as $row ){
			if ($row->total>$maxval) $maxval=$row->total;
			$data[]=$row->total;
			$titu[]=$row->etiqueta;
		}
		
		$i=1;
		while(1){
			if($maxval/$i<=100) break;
			$i=$i*10;
		}
		$om=$i;
		$i=0;
		//$om=1;
		for($i=0;$i<count($data);$i++){
			$data[$i]=$data[$i]/$om;
		}
    
		$g = new Graph();
		if($maxval>0){
			$g->title( 'COMPRAS DEL '.$mes.'/'.$anio,'{font-size:18px; color: #d01f3c}');
			$g->set_data( $data ); $g->bar_glass( 55, '#5E83BF', '#424581' ,'Compras', 10 );
			$g->set_y_max(ceil($maxval/$om));
			$g->y_label_steps( 5 );
			$g->set_x_labels($titu);
			$ejey=number_format($om,0,'',' ');
			$g->set_y_legend( 'Compras X '.$ejey.' (Bs)',14,'0x639F45' );
			$g->set_x_legend( 'Grupos '.$mes, 14,'0x639F45' );
			//$g->pie(60,'#505050','#000000');
			//$g->pie_values( $data, $titu );
			//$g->pie_slice_colours( array('#d01f3c','#356aa0','#C79810','#5E83BF'));
			$g->set_tool_tip( '#key#<br>Departamento: #x_label# <br>Monto: #val# x '.$ejey );
		}else
			$g->title( 'NO EXISTEN VENTAS EN LA FECHA SELECCIONADA','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';
		//$g->set_bg_image(site_url('/images/ventafon.png'), 'center', 'middle' );
		echo $g->render();
		$query->free_result();
	} 
}   
?>  
    
    
    
    
    
    
    
    
    
    