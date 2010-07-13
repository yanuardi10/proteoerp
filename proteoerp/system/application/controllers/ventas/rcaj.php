<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Rcaj extends validaciones {

	function Rcaj(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->load->library("menues");
		$this->datasis->modulo_id('12A',1);
		$this->url='ventas/rcaj/';
	}

	function index(){
		redirect("ventas/rcaj/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro de cierres de cajas", 'rcaj');

		$filter->fecha = new dateonlyField("Fecha", "fecha");
		$filter->fecha->clause="where";
		$filter->fecha->operator="=";
		$filter->fecha->size =8;

		$filter->cajero = new dropdownField("Cajero", "cajero");
		$filter->cajero->option("","Todos");
		$filter->cajero->options("SELECT cajero, nombre FROM scaj ORDER BY nombre");

		$filter->buttons("reset","search");
		$filter->build();

		$urih = anchor('formatos/verhtml/RECAJA/<#numero#>','Descargar html');
		$urip = anchor('formatos/ver/RECAJA/<#numero#>'    ,'Descargar pdf');

		$grid = new DataGrid("Lista de Cierres de caja");
		$grid->order_by("fecha","desc");
		$grid->per_page=15;

		$grid->column("Fecha"    ,'<dbdate_to_human><#fecha#></dbdate_to_human>');
		$grid->column("Cajero"   ,"cajero"  ,"align='center'");
		$grid->column("Recibido" ,"recibido","align='right'");
		$grid->column("Ingreso"  ,"ingreso" ,"align='right'");
		$grid->column("&nbsp;"   ,$urip);
		$grid->add($this->url.'selecaja','Cerrar caja');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Recepci&oacute;n de cajas</h1>";
		$data["head"]    = $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}

	function selecaja(){
		$this->rapyd->load('datagrid','datafilter');

		$filter = new DataFilter('titulo');
		$filter->fecha = new dateonlyField("Fecha","fecha","d/m/Y");
		$filter->fecha->size =11;
		$filter->fecha->clause="where";
		$filter->fecha->operator="=";
		$filter->fecha->insertValue=date("Y-m-d");

		$filter->buttons("reset","search");
		$filter->build();
		$data['content'] = $filter->output;

		function iconcaja($caja,$cajero,$fecha){
			$CI =& get_instance();
			$cerrado = $CI->datasis->dameval("SELECT numero FROM dine WHERE caja='$caja' AND cajero='$cajero' AND fecha='$fecha' ");
			$atts=array('align'=>'LEFT','border'=>'0');
			$fecha=str_replace('-','',$fecha);
			$atRI = array(
              'width'     => '800','height' => '600',
              'scrollbars'=> 'yes','status' => 'yes',
              'resizable' => 'yes','screenx'=> '0',
              'screeny'   => '0');
			if (!empty($cerrado))
				return image('caja_cerrada.gif',"Caja Cerrada: $caja",$atts)."<h3>Caja: $caja</h3><br><center>".anchor_popup("/supermercado/cierre/doccierre/$cerrado",'Ver Cuadre',$atRI).' '.anchor_popup("/supermercado/cierre/ventasdia/$fecha/$caja",'Detalle de Ventas',$atRI).'</center>';
			else
				return image('caja_abierta.gif',"Caja Abierta: $caja",$atts)."<h3>Caja: $caja</h3>".'<center><a href='.site_url("supermercado/cierre/forcierre/$caja/$cajero/$fecha").'>Cerrar Cajero</a></center>';
		}

		$data['forma'] ='';

		if($this->rapyd->uri->is_set("search") AND !empty($filter->fecha->value)){
			$fecha=$filter->fecha->value;

			$grid = new DataGrid('Cierre de cajas para la fecha: '.$filter->fecha->value);
			$select=array('viefac.caja', 'viefac.cajero', 'fecha as qfecha' ,'SUM(viefac.gtotal) monto', 'scaj.nombre as name', 'sum(TRUNCATE(gtotal/60000,0)) cupon');
			$grid->db->select($select);
			$grid->db->from('viefac');
			$grid->db->join('scaj','scaj.cajero=viefac.cajero','LEFT');
			$grid->db->groupby("viefac.caja,viefac.cajero");
			$grid->use_function('iconcaja','number_format');
			$grid->column("Status/Caja"     ,"<iconcaja><#caja#>|<#cajero#>|<#qfecha#></iconcaja>"  ,'align="RIGHT"');
			$grid->column("Cajero"   ,"cajero",'align="RIGHT"');
			$grid->column("Nombre"   ,"name");
			$grid->column("Ventas Bs","<number_format><#monto#>|2|,|.</number_format>",'align="RIGHT"');
			$grid->column("Cupones"  ,"<number_format><#cupon#>|0|,|.</number_format>",'align="RIGHT"');
			$grid->build();
			$data['content'] .= $grid->output;
			//echo $grid->db->last_query();
		}

		$data['title']   = "<h1>Cierre de Caja</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	function cierre(){



	}
}
?>