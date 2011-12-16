<?php class gcompras extends Controller {  

	function gcompras() {
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->helper('openflash');
	}

	function index(){
		redirect('/compras/gcompras/anuales');
	}

	function anuales(){
		$this->rapyd->load('datagrid2','dataform');
		$this->load->helper('openflash');

		if($this->uri->segment(4))$anio=$this->uri->segment(4); elseif(isset($_POST['anio'])) $anio=$_POST['anio'];
		if (empty($anio))$anio=date('Y');
		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';

		$filter = new DataForm('compras/gcompras/anuales');
		$filter->title('Filtro de Compras Anuales');

		$filter->anio = new inputField('A&ntilde;o','anio');
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4; 

		$filter->button("btnsubmit", "Buscar", form2uri(site_url('compras/gcompras/anuales'),array('anio')), $position="BL");
		$filter->build_form();

		$grid = new DataGrid2();
		$select=array( "fecha","DATE_FORMAT(fecha,'%m/%Y' )as mes",
		"SUM(montonet*IF(tipo_doc='D', -1, 1)) AS grantotal",
		"SUM(credito*IF(tipo_doc='D', -1, 1)) as credito",
		"SUM(inicial*IF(tipo_doc='D', -1, 1)) as contado",
		"SUM(montotot*IF(tipo_doc='D',-1,1)) AS subtotal",
		"SUM(montoiva*IF(tipo_doc='D',-1,1)) AS impuesto",
		"COUNT(*) AS numfac"); 

		$grid->db->select($select);
		$grid->db->from('scst');
		$grid->db->where("tipo_doc <> ",'X');
		$grid->db->where('fecha >= ', $fechai);
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->groupby('mes');

		$grid->column("Mes"         , "mes","align='center'");
		$grid->column("Sub-Total"     , "<nformat><#subtotal#></nformat>" ,'align=right');
		$grid->column("Impuesto"      , "<nformat><#impuesto#></nformat>" ,'align=right');
		$grid->column("Total"         , "<nformat><#grantotal#></nformat>",'align=right');
		$grid->column("Contado"       , "<nformat><#contado#></nformat>"  ,'align=right');
		$grid->column("Credito"       , "<nformat><#credito#></nformat>"  ,'align=right');
		$grid->column("N&uacute;mero" , "numfac"   ,'align=right');

		$grid->totalizar('impuesto','grantotal','contado','credito','subtotal');
		$grid->build();

		$gcompras = open_flash_chart_object(730,350, site_url("compras/gcompras/ganuales/$anio/"));
		$data['content']  = $gcompras;
		$data['content'] .= $filter->output.$grid->output;
		$data['head']     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head().'<h1>Compras Anuales</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function mensuales($anio='',$mes=''){
		$this->rapyd->load('datagrid2','dataform');
		$this->load->helper('openflash');

		if(isset($_POST['anio']) AND empty($anio)) $anio=$_POST['anio'];
		if(isset($_POST['mes']) AND empty($mes)) $mes=$_POST['mes'];

		if(empty($mes))redirect("compras/gcompras/anuales/$anio");

		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';

		$filter = new DataForm('compras/gcompras/mensuales');
		$filter->attributes=array('onsubmit'=>"this.action='index/'+this.form.mes.value+'/'+this.form.anio.value+'/';return FALSE;");
		$filter->title('Filtro de Compras Mensuales');
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

		$filter->button("btnsubmit", "Buscar", form2uri(site_url('compras/gcompras/mensuales'),array('anio','mes')), $position="BL");
		$filter->build_form();

		$grid = new DataGrid2();
		$select=array( "fecha","DAYOFMONTH(fecha) as dia",
		"SUM(montonet*IF(tipo_doc='D', -1, 1)) AS grantotal",
		"SUM(credito*IF(tipo_doc='D', -1, 1)) as contado",
		"SUM(inicial*IF(tipo_doc='D', -1, 1)) as credito",
		"SUM(montotot*IF(tipo_doc='D',-1,1))AS subtotal", 
		"SUM(montoiva*IF(tipo_doc='D',-1,1))AS impuesto", 
		"COUNT(*) AS numfac"); 

		$grid->db->select($select);
		$grid->db->from("scst");
		$grid->db->where("tipo_doc <> ",'X');
		$grid->db->where('fecha >= ', $fechai);
		$grid->db->where('fecha <= ',$fechaf);
		$grid->db->groupby("fecha");

		$grid->column("Dia"         ,"dia");
		$grid->column("Sub-Total"     , "<nformat><#subtotal#></nformat>" ,'align=right');
		$grid->column("Impuesto"      , "<nformat><#impuesto#></nformat>" ,'align=right');
		$grid->column("Total"         , "<nformat><#grantotal#></nformat>",'align=right');
		$grid->column("Contado"       , "<nformat><#contado#></nformat>"  ,'align=right');
		$grid->column("Credito"       , "<nformat><#credito#></nformat>"  ,'align=right');
		$grid->column("N&uacute;mero" , "numfac"   ,'align=right');

		$grid->totalizar('impuesto','grantotal','contado','credito','subtotal');
		$grid->build();

		$grafico = open_flash_chart_object(680,350, site_url("compras/gcompras/gmensuales/$anio/$mes"));
		$data['content']  =$grafico;
		$data['content'] .=  $filter->output.$grid->output;
		$data['head']     = $this->rapyd->get_head();
		$data['title']    = $this->rapyd->get_head().'<h1>Compras Mensuales</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function ganuales($anio=''){
		$this->load->library('Graph');
		$this->lang->load('calendar');
		if (empty($anio)) return;

		$fechai=$anio.'0101';
		$fechaf=$anio.'1231';

		$mSQL = "SELECT MONTHNAME(fecha)AS mes, fecha,MONTH(fecha) AS mmes,
		sum(montonet*IF(tipo_doc='D', -1, 1)) AS grantotal,
		sum(inicial*IF(tipo_doc='D', -1, 1)) AS contado,
		sum(credito*IF(tipo_doc='D', -1, 1)) AS credito
		FROM scst
		WHERE tipo_doc<>'X' AND fecha>='$fechai' AND fecha<='$fechaf'
		GROUP BY mes ORDER BY fecha,grantotal DESC LIMIT 12";
		//echo $mSQL;

		$maxval=0;
		$query = $this->db->query($mSQL);
		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$mmes[]  =$row->mmes;
			$nmes[]  =$this->lang->line('cal_'.strtolower($row->mes));
			$data_1[]=$row->contado;
			$data_2[]=$row->credito;
			$data_3[]=$row->grantotal;
		}

		$om=1;while($maxval/$om>100) $om=$om*10;

		$bar_1 = new bar(75, '#0000FF');
		$bar_2 = new bar(75, '#483D8B');
		$bar_3 = new bar(75, '#ADD8E6');

		$bar_1->key('Contado',10);
		$bar_2->key('Credito',10);
		$bar_3->key('Total'  ,10);

		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_2->add_data_tip($data_2[$i]/$om, graph::esc( number_format($data_2[$i],2,',','.')));
			$bar_3->add_data_tip($data_3[$i]/$om, graph::esc( number_format($data_3[$i],2,',','.')));

			$mes=$mmes[$i];
			$bar_1->links[]= site_url("/compras/gcompras/mensuales/$anio/$mes");
			$bar_2->links[]= site_url("/compras/gcompras/mensuales/$anio/$mes");
			$bar_3->links[]= site_url("/compras/gcompras/mensuales/$anio/$mes");
		}

		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval>0){
			$g->title( 'Compra de en el A&ntilde;o '.$anio,'{font-size: 16px; color:##00264A}' );
			$g->data_sets[] = $bar_1;
			$g->data_sets[] = $bar_2;
			$g->data_sets[] = $bar_3;

			$g->x_axis_colour('#A6A6A6', '#ADB5C7');
			$g->set_x_labels($nmes);
			$g->set_x_label_style( 9, '#000000', 3, 1 );
			$g->set_x_axis_steps( 10 );
			$g->set_x_legend('Meses ', 16, '#004381' );

			$g->bg_colour = '#FFFFFF';
			$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Monto: #tip#' );
			$g->set_y_max(ceil($maxval/$om));
			$g->y_label_steps(5);
			$g->y_axis_colour( '#A6A6A6', '#ADB5C7' );
			$g->set_y_legend('Compras x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else
			$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
			$g->bg_colour='#FFFFFF';
			echo utf8_encode($g->render());
	}

	function gmensuales($anio='',$mes=''){
		$this->load->library('Graph');

		if (empty($mes) or empty($anio)) return;

		$fechai=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'01';
		$fechaf=$anio.str_pad($mes, 2, "0", STR_PAD_LEFT).'31';

		$mSQL = "SELECT DAYOFMONTH(fecha)as dia, fecha,
		sum(montonet*IF(tipo_doc='D', -1, 1)) AS grantotal, 
		sum(inicial*IF(tipo_doc='D', -1, 1)) AS contado,
		sum(credito*IF(tipo_doc='D', -1, 1)) AS credito
		FROM scst
		WHERE tipo_doc<>'X' AND fecha>='$fechai' AND fecha<='$fechaf'
		GROUP BY fecha ORDER BY fecha,grantotal DESC LIMIT 31";
		//echo $mSQL;

		$maxval=0;
		$query = $this->db->query($mSQL);

		foreach($query->result() as $row ){
			if ($row->grantotal>$maxval) $maxval=$row->grantotal;
			$fecha[]=$row->dia;
			$data_1[]=$row->contado;  
			$data_2[]=$row->credito;  
			$data_3[]=$row->grantotal;
		}

		$om=1;while($maxval/$om>100) $om=$om*10;

		$bar_1 = new bar(75, '#0000FF');
		$bar_2 = new bar(75, '#483D8B');
		$bar_3 = new bar(75, '#ADD8E6');

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
			$g->title( 'Compras de en el mes '.$mes.'/'.$anio,'{font-size: 16px; color:##00264A}' );
			$g->data_sets[] = $bar_1;
			$g->data_sets[] = $bar_2;
			$g->data_sets[] = $bar_3;

			$g->x_axis_colour( '#A6A6A6', '#ADB5C7' );
			$g->set_x_labels($fecha);
			 $g->set_x_label_style( 10, '#000000', 3, 1 );
			$g->set_x_axis_steps( 10 );
			$g->set_x_legend('Dias', 14, '#004381' ); 

			$g->bg_colour = '#FFFFFF';
			$g->set_tool_tip( '#key#<br>Dia: #x_label# <br>Monto: #tip#' );
			$g->set_y_max(ceil($maxval/$om));
			$g->y_label_steps(5);
			$g->y_axis_colour( '#A6A6A6', '#ADB5C7' );
			$g->set_y_legend('Compras x '.number_format($om,0,'','.').' (Bs)', 16, '#004381' );
		}else 
			$g->title( 'No existen ventas con los datos seleccionados','{font-size:18px; color: #d01f3c}');
			$g->bg_colour='#FFFFFF';
			echo utf8_encode($g->render());
	}
}