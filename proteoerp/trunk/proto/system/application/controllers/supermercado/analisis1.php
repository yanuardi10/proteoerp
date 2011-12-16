<?php
class Analisis extends Controller {

	function Analisis(){
		parent::Controller();
		$this->load->helper('text');
		$this->load->library("rapyd");
		$this->load->library('calendar');
		$this->datasis->modulo_id(101,1);	
		$this->rapyd->config->set_item("theme","repo");
	}
	function index() {
		$this->rapyd->load("datagrid","dataform");
		$this->load->helper('openflash');
		
		$anio=$this->uri->segment(4);
		if (empty($anio))$anio=date("Y");
    
		$script ='
			$(function() {
				$(".inputnum").numeric(".");
			});
			';
			
		
		$filter = new DataForm();
		$filter->title('Filtro de An&aacute;lisis de Ventas');
		$filter->script($script, "create");
		$filter->script($script, "modify");
		
		$filter->anio = new inputField("A&ntilde;o", "anio");
		$filter->anio->size=4;
		$filter->anio->insertValue=$anio;
		$filter->anio->maxlength=4;	
		$filter->anio->rule = "trim";
		$filter->anio->css_class='inputnum';
				
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('/supermercado/analisis/index'),array('anio')), $position="BL");
		$filter->build_form(); 
    
		$link="supermercado/analisis/departamento/<#depto#>/$anio";
		$select=array('a.fecha','a.depto','a.familia','a.grupo','a.codigo','a.impuesto','a.monto','a.cantidad','a.transac','a.tipo','a.costo','b.descrip');
    
		$grid = new DataGrid('Ventas por Departamentos');
		$grid->column("Departamento", anchor($link, '<#descrip#>'),'nowrap=yes');
    
		for($i=1;$i<=12;$i++){
			$nmes=$this->calendar->get_month_name(str_pad($i, 2, "0", STR_PAD_LEFT));
			$grid->column($nmes, "<nformat><#m$i#></nformat>" ,'align=right');
			$select[]="sum(a.monto*(month(a.fecha)=$i))  AS m$i";
		}
    
		$grid->db->select($select);
		$grid->db->from('est_item  AS a');
		$grid->db->join('dpto  AS b','a.depto=b.depto');
		$grid->db->where('YEAR(a.fecha)',$anio);
		$grid->db->where("a.tipo",'I');	
		$grid->db->groupby('a.depto');
		$grid->build();
		//echo  $grid->db->last_query();
     
		$data['content'] = $filter->output.'<div style="overflow: auto; width: 100%;">'.$grid->output.'</div>';
		$data['title']   = "<h1>An&aacute;lisis de Ventas</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function departamento() {
		$this->rapyd->load("datagrid","dataform");
		$this->load->helper('openflash');

		$depto=$this->uri->segment(4);
		if ($depto===FALSE) redirect("supermercado/analisis");                    
		$anio=$this->uri->segment(5);
		if ($anio===FALSE)$anio=date("Y");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
   
		$link="supermercado/analisis/familia/$depto/<#familia#>/$anio";
		$select="a.fecha,a.depto,a.familia,a.grupo,a.codigo,a.impuesto,a.monto,a.cantidad,a.transac,a.tipo,a.costo,b.descrip";
		$depto2=$this->datasis->dameval("SELECT  descrip FROM dpto WHERE depto='$depto'");		
		
		$grid = new DataGrid("Ventas por Familia del departamento $depto2 del a&ntilde;o $anio");
		$grid->column("Familia", anchor($link, '<#descrip#>'),'nowrap=yes');
		
		for($i=1;$i<=12;$i++){
			$nmes=$this->calendar->get_month_name(str_pad($i, 2, "0", STR_PAD_LEFT));
			$grid->column($nmes, "<nformat><#m$i#></nformat>",'align=right');
			$select.=",sum(a.monto*(month(a.fecha)=$i)) AS m$i ";
		}
		
		$grid->db->select($select);
		$grid->db->from('est_item  AS a');
		$grid->db->join('fami  AS b','a.familia=b.familia');
		$grid->db->where('YEAR(a.fecha)',$anio);
		$grid->db->where("a.tipo",'I');
		$grid->db->groupby('a.familia');
		$grid->build();
			
		$at=array('title'=>"Regresar a Ventas por Departamentos del a&ntilde;o $anio");
		$salida=anchor("supermercado/analisis/index/$anio","$anio",$at);
		
		$data['content'] = '<div style="overflow: auto; width: 100%;">'.$salida.$grid->output.'</div>';
		$data['title']   = "<h1>An&aacute;lisis de ventas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
  function familia(){
		$this->rapyd->load("datagrid","dataform");
		$this->load->helper('openflash');
		//$this->rapyd->uri->keep_persistence();

		$depto=$this->uri->segment(4);
		if ($depto===FALSE) redirect("supermercado/analisis");
		$familia=$this->uri->segment(5);
		if ($familia===FALSE) redirect("supermercado/analisis/departamento/$depto");                   
		$anio=$this->uri->segment(6);
		if ($anio===FALSE)$anio=date("Y");
				
		$depto2=$this->datasis->dameval("SELECT descrip dpto FROM dpto WHERE depto='$depto'");
		$familia2=$this->datasis->dameval("SELECT descrip linea FROM fami WHERE familia='$familia'");
   
		$link="ventas/analisis/grupo/$depto/$familia/<#grupo#>/$anio";
		$select="a.fecha,a.depto,a.familia,a.grupo,a.codigo,a.impuesto,a.monto,a.cantidad,a.transac,a.tipo,a.costo,b.descrip";
		
		$grid = new DataGrid("Ventas por grupos de el departamento $depto2 y familia $familia2");
		$grid->use_function('number_format');
		$grid->column("Grupo", anchor($link, '<#nom_grup#>'),'nowrap=yes');
		for($i=1;$i<=12;$i++){
			$nmes=$this->calendar->get_month_name(str_pad($i, 2, "0", STR_PAD_LEFT));
			$grid->column($nmes, "<nformat><#m$i#></nformat>",'align=right');
			$select.=",sum(a.monto*(month(a.fecha)=$i)) AS m$i ";
		}
		
		$grid->db->select($select);
		$grid->db->from('est_item  AS a');
		$grid->db->join('grup  AS b','a.grupo=b.grupo');
		$grid->db->where('YEAR(a.fecha)',$anio);
		$grid->db->where("a.tipo",'I');
		$grid->db->groupby('a.grupo');
		$grid->build();
		
		$at=array('title'=>"Regresar a Ventas por Departamento del a&ntilde;o $anio");
		$salida=anchor("ventas/analisis/index/$anio","$anio",$at);
		$salida.=" ";
		$at=array('title'=>"Regresar a Ventas por familia del departamento $depto2");
		$salida.=anchor("supermercado/analisis/departamento/$depto/$anio","$depto2",$at);
		$data['content'] = '<div style="overflow: auto; width: 100%;">'.$salida.$grid->output.'</div>';
		$data['title']   = "<h1>An&aacute;lisis de ventas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function grupo() {
		$this->rapyd->load("datagrid","dataform");
		$this->load->helper('openflash');
		//$this->rapyd->uri->keep_persistence();
		$atts = array(
              'width'     =>'800',
              'height'    =>'600',
              'scrollbars'=>'yes',
              'status'    =>'yes',
              'resizable' =>'yes',
              'screenx'   =>'5',
              'screeny'   =>'5');

		$depto=$this->uri->segment(4);
		if ($depto===FALSE) redirect("ventas/analisis");
		$linea=$this->uri->segment(5);
		if ($linea===FALSE) redirect("ventas/analisis/departamento/$depto"); 
		$grupo=$this->uri->segment(6);
		if ($grupo===FALSE) redirect("ventas/analisis/departamento/$depto/$linea");                   
		$anio=$this->uri->segment(7);
		if ($anio===FALSE)$anio=date("Y");
		
		//$data['lista'] = open_flash_chart_object(800,300, site_url("ventas/mensuales/grafico/$mes/$anio")); 
   
		//$link="ventas/analisis/grupo/$depto/$linea/<#grupo#>/$anio";
		
		$depto2=$this->datasis->dameval("SELECT IFNULL(descrip, depto) dpto FROM dpto WHERE depto='$depto'");
		$linea2=$this->datasis->dameval("SELECT IFNULL(descrip, linea) linea FROM line WHERE linea='$linea'");
		$grup2=$this->datasis->dameval("SELECT IFNULL(nom_grup, grupo) grupo FROM grup WHERE grupo='$grupo'");
		
		$select='a.depto, a.linea, a.grupo, a.codigo,b.descrip';
		
		$link='inventario/sinvshow/dataedit/show/<#id#>';
		
		$grid = new DataGrid("Ventas por c&oacute;digo de el departamento $depto2, l&iacute;nea $linea2 y grupo $grup2");
		$grid->use_function('number_format');
		$grid->column("C&oacute;digo",anchor_popup($link,'<#codigo#>',$atts),'nowrap=yes');
		$grid->column("Descripci&oacute;n",'descrip','nowrap=yes');
		for($i=1;$i<=12;$i++){
			$nmes=$this->calendar->get_month_name(str_pad($i, 2, "0", STR_PAD_LEFT));
			$grid->column($nmes, "<number_format><#m$i#>|0|,|.</number_format>" ,'align=right');
			$grid->column('Cant.', "<number_format><#c$i#>|0|,|.</number_format>" ,'align=right');
			$select.=",sum(a.monto*(month(a.fecha)=$i)) AS m$i,sum(a.cana*(month(a.fecha)=$i)) AS c$i ";
		}
		$select.=",b.id AS id";
		$grid->db->select($select);  
		$grid->db->from('eventas a');
		$grid->db->join('sinv b','a.codigo=b.codigo','LEFT');
		$grid->db->where('year(a.fecha)',$anio);
		$grid->db->where('a.depto',$depto);
		$grid->db->where('a.linea',$linea);
		$grid->db->where('a.grupo',$grupo);
		$grid->db->groupby('codigo');
		$grid->build();
		
		$at=array('title'=>"Regresar a Ventas por Departamento del a&ntilde;o $anio");
		$salida=anchor("ventas/analisis/index/$anio","$anio",$at);
		$salida.=" ";
		$at=array('title'=>"Regresar a Ventas por linea del departamento $depto2");
		$salida.=anchor("ventas/analisis/departamento/$depto/$anio","$depto2",$at);
		$salida.=" ";
		$at=array('title'=>"Regresar a Ventas por grupo del departamento $depto2 y linea $linea2");
		$salida.=anchor("ventas/analisis/linea/$depto/$linea/$anio","$linea2",$at);
		
		$data['content'] = $salida.'<div style="overflow: auto; width: 850px; height: 400px;">'.$grid->output.'</div>';// 
		$data['title']   = "<h1>An&aacute;lisis de ventas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
?>