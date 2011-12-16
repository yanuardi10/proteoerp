<?php
class Analisisgastos extends Controller {

	function Analisisgastos(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->library('calendar');
		$this->datasis->modulo_id('50D',1);
		$this->rapyd->config->set_item("theme","repo");
	}

	function index(){
		$this->rapyd->load("datagrid2","dataform");
		$anio=$this->uri->segment(4);
		if (empty($anio))$anio=date("Y");
		$grupo=$this->uri->segment(5);
		$codigo=$this->uri->segment(6);
		$script ='
			$(function() {
				$(".inputnum").numeric(".");
			});
			$("#df1").submit(function() {
					valor=$("#anio").attr("value");
					location.href="'.site_url('finanzas/analisisgastos/index').'/"+valor;
					return false;
				});
			';

		$filter = new DataForm();
		$filter->title('Filtro de An&aacute;lisis de Gastos');
		$filter->script($script, "create");
		$filter->script($script, "modify");		

		//$filter->fechad = new dateonlyField("Desde", "fechad",'m-Y');
		//$filter->fechah = new dateonlyField("Hasta", "fechah",'m-Y');
		//$filter->fechad->clause  =$filter->fechah->clause="where";
		//$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		//$filter->fechad->insertValue='15-'.$fechad;
		//$filter->fechah->insertValue='15-'.$fechah;
		//
		//$filter->fechah->size=$filter->fechad->size=10;
		//$filter->fechad->operator=">=";
		//$filter->fechah->operator="<=";
		
		//$filter->anio = new inputField("A&ntilde;o", "anio");
		//$filter->anio->size=4;
		//$filter->anio->insertValue=$anio;
		//$filter->anio->maxlength=4;	
		//$filter->anio->rule = "trim";
		//$filter->anio->css_class='inputnum';
		
		$filter->anio = new dropdownField("A&ntilde;o", "anio");
		$filter->anio->option($anio,$anio);
		$filter->anio->options('SELECT YEAR(fecha),YEAR(fecha) AS fecha2 FROM gitser GROUP BY fecha2');
		$filter->anio->style='width:80px;';
		
		$filter->button("btnsubmit", "Buscar", form2uri(site_url('/finanzas/analisisgastos/index'),array('anio')), $position="BL");
		$filter->build_form();
		
		$where="YEAR(a.fecha)='$anio' ";
		if (empty($grupo)) {
			$grid = new DataGrid2('Grupos de Gastos');
			$select =array("d.nom_grup as nom_grup","b.grupo AS deptodes","sum(a.importe) AS tgene");
			$group="b.grupo";
			$link="finanzas/analisisgastos/index/$anio/<#deptodes#>";
			$grid->column("Grupos de Gastos", anchor($link, '<#nom_grup#>'),'nowrap=yes');
			$salida='';
		} 
		elseif(empty($codigo)) {
			$nom=$this->datasis->dameval("SELECT nom_grup FROM grga WHERE grupo='$grupo'");
			$grid = new DataGrid2("Gastos del grupo $nom");
			$where.="AND b.grupo='$grupo'";
			$select =array("b.descrip as descrip","b.grupo AS deptodes","sum(a.importe) AS tgene,a.codigo as cod");			
			$group=array("b.grupo","a.codigo");
			$link="finanzas/analisisgastos/index/$anio/<#deptodes#>/<#cod#>/";
			$grid->column("Grupos de Gastos", anchor($link, '<#descrip#>'),'nowrap=yes');
			$salida= anchor("finanzas/analisisgastos/index/$anio","Atras");			
		}else{
			$select=array("fecha","numero","precio","a.iva as iva","importe","p.nombre AS nombre");
			$nom=$this->datasis->dameval("SELECT nom_grup FROM grga WHERE grupo='$grupo'");
			$nom2=$this->datasis->dameval("SELECT descrip FROM mgas WHERE codigo='$codigo'");
			$grid = new DataGrid2("Gastos del grupo $nom del Departamento $nom2");
			$grid->column("Fecha", "<dbdate_to_human><#fecha#></dbdate_to_human>",'align=center');
			$grid->column("Numero", "<#numero#>",'align=left');
			$grid->column("Proveedor", "<#nombre#>",'align=left');
			$grid->column("Precio", "<number_format><#precio#>|2|,|.</number_format>",'align=right');
			$grid->column("I.V.A.", "<number_format><#iva#>|2|,|.</number_format>",'align=right');
			$grid->column("Importe", "<number_format><#importe#>|2|,|.</number_format>",'align=right');
			$group='';
			$att['title']="Regresar al listado de gastos por grupos";
			$salida= anchor("finanzas/analisisgastos/index/$anio","Gastos por Grupos",$att);
			$att['title']="Regresar al listado de gastos del grupo $nom";
			$salida.=' ';
			$salida.= anchor("finanzas/analisisgastos/index/$anio/$grupo","$nom",$att);
			
		}
		if($group!=''){
			$ii=3;
			$grid->column("Total", "<number_format><#tgene#>|0|,|.</number_format>" ,'align=left');
			for ($i = $anio.'01'; $i <= $anio.'12'; $i=$this->agremes($i)){
			  	$select[$ii] ="sum(a.importe*(EXTRACT(YEAR_MONTH FROM a.fecha)=$i)) AS 'm$i' ";
			  	$ii++;
			  	$col=$this->calendar->get_month_name(str_pad(substr($i,4,2), 2, "0", STR_PAD_LEFT));;
			  	$grid->column("$col", "<number_format><#m$i#>|0|,|.</number_format>",'align=right');
			}
		}

		$grid->db->select($select);
		$grid->db->from('gitser as a');		
		$grid->db->join('mgas as b','a.codigo=b.codigo','LEFT');
		$grid->db->join('grga as d','b.grupo=d.grupo'  ,'LEFT');
		$grid->db->join('sprv as p','p.proveed=a.proveed');
		$grid->db->where($where);
		$grid->db->groupby($group);
		$grid->per_page = 15;

		//$grid->db->orderby('d.nom_grup');
		$grid->build();
		
		memowrite ($grid->db->last_query());
		$data['content'] = $filter->output.$salida.'<div style="overflow: auto; width: 100%;">'.$grid->output.'</div>';
		$data['title']   = "<h1>An&aacute;lisis de Gastos</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}	

	function agremes($ano_mes) {
	$mesmas = $ano_mes+1;
	if (substr($mesmas,4,2) > 12 ) {
		$mesmas=substr($mesmas,0,4)+1;
		$mesmas=$mesmas."01";
	}
  return $mesmas;
	}	
}
?>