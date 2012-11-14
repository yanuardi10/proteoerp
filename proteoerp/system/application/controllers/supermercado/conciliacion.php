<?php
class  conciliacion extends Controller {

	function conciliacion() {
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->helper('openflash');
		$this->datasis->modulo_id('11C',1);
	}

	function index(){
		$this->rapyd->load('dataform');
		$this->rapyd->load('datagrid2');
		$this->load->library('encrypt');

		function dif($a,$b){
			return number_format($a-$b,2,',','.');
		}

		$form = new DataForm('supermercado/conciliacion/index/process');

		$form->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$form->fechad->insertValue = date('Y-m-d',mktime(0, 0, 0, date("m"), date("d")-30, date("Y")));
		$form->fechad->rule = 'required';

		$form->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$form->fechah->insertValue = date('Y-m-d');
		$form->fechah->rule = 'required';

		$form->submit('btnsubmit','Buscar');
		$form->build_form();

		$tabla='';
		if ($form->on_success()){
			$fechad=$form->fechad->newValue;
			$fechah=$form->fechah->newValue;

			//$mSQL="SELECT 'PV' ,caja,SUM(gtotal*IF(MID(numero,1,2)='NC',-1,1)) FROM viefac WHERE fecha BETWEEN '$fechad' AND '$fechah' GROUP BY caja UNION
			//SELECT 'PV' ,'MAYO',SUM(gtotal*IF(tipo='D',-1,1)) FROM fmay WHERE fecha BETWEEN '$fechad' AND '$fechah' UNION
			//SELECT 'CZ' ,caja,SUM(exento+base+iva+base1+iva1+base2+iva2-ncexento-ncbase-nciva-ncbase1-nciva1-ncbase2-nciva2) FROM fiscalz WHERE fecha BETWEEN '$fechad' AND '$fechah' GROUP BY caja";

			$mSQL="SELECT a.fecha, a.caja, sum(a.gtotal*if(MID(a.numero,1,2)='NC',-1,1)) AS factura,
			(SELECT sum(exento+base+iva-ncexento-ncbase-nciva) venta FROM fiscalz c WHERE c.fecha=a.fecha AND c.caja=a.caja ) AS  cierrez,
			(SELECT sum(exento+base+iva-ncexento-ncbase-nciva) venta FROM fiscalz c WHERE c.fecha=a.fecha AND c.caja=a.caja )-
			sum(a.gtotal*if(MID(a.numero,1,2)='NC',-1,1)) AS dife
			FROM viefac a
			WHERE a.fecha BETWEEN '$fechad' AND '$fechah' GROUP BY a.fecha, a.caja ORDER BY a.caja,a.fecha";

			$grid = new DataGrid2('Res&uacute;men');
			$grid->db->_escape_char='';
			$grid->db->_protect_identifiers=false;
			//$grid->per_page = 20;
			$grid->use_function('dif');
			$select=array('a.fecha', 'b.caja',
				'(SELECT sum(exento+base+iva+base1+iva1+base2+iva2-ncexento-ncbase-nciva-ncbase1-nciva1-ncbase2-nciva2) FROM fiscalz c WHERE c.fecha=a.fecha AND c.caja=b.caja) AS cierrez',
				'(SELECT sum(d.gtotal) FROM viefac AS d WHERE  d.fecha=a.fecha AND d.caja=b.caja) AS factura');
			$grid->db->select($select);
			$grid->db->from('tiempo AS a');
			$grid->db->join("caja AS b","a.fecha BETWEEN '$fechad' AND '$fechah'");
			$grid->db->orderby("caja,fecha");
			$grid->db->having("cierrez IS NOT null OR factura IS NOT null");

			$grid->column("Fecha"      , "<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
			$grid->column("Caja"       , "caja" ,'align=center');
			$grid->column("Factura"    , "<nformat><#factura#></nformat>" ,'align=right');
			$grid->column("Cierre Z"   , "<nformat><#cierrez#></nformat>" ,'align=right');
			$grid->column("Diferencia" , "<dif><#factura#>|<#cierrez#></dif>",'align=right');

			$grid->totalizar('factura','cierrez');
			$grid->build();
			//echo $grid->db->last_query();

			$tabla=$grid->output;
		}
		$reporte= anchor('reportes/ver/CONCILIACI','Imprimir');
		$data['content'] = $form->output.$reporte.$tabla;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = heading('Concialiaciones de Cierre Z');
		$this->load->view('view_ventanas', $data);
	}

	function tiempo(){
		$mSQL="CREATE TABLE IF NOT EXISTS `tiempo` (
		   `id` int(11) unsigned NOT NULL auto_increment,
		  `fecha` date default NULL,
		  `anio` int(4) unsigned default NULL,
		  `mes` int(2) unsigned default NULL,
		  `dia` int(2) unsigned default NULL,
		  `semana` int(1) unsigned default NULL,
		  `aniomes` int(5) unsigned default NULL,
		  `trimestre` int(1) unsigned default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `id` (`id`),
		  KEY `id_2` (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		$this->db->query($mSQL);

		for($i=1 ; $i<3650 ; $i++){
			$tempo =mktime(0, 0, 0, 1, $i, 2009);

			$fecha  = date("Y-m-d", $tempo);
			$semana = date("N", $tempo);
			$aniomes= date("mY", $tempo);
			$mes    = date("n", $tempo);
			$dia    = date("j", $tempo);
			$anio   = date("Y", $tempo);

			$trimestre=1;
			if     ($mes > 3 AND $mes <= 6) $trimestre=2;
			elseif ($mes > 6 AND $mes <= 9) $trimestre=3;
			else $trimestre=4;

			$data = array('fecha'    => $fecha    ,
				'anio'     => $anio     ,
				'mes'      => $mes      ,
				'dia'      => $dia      ,
				'semana'   => $semana   ,
				'aniomes'  => $aniomes  ,
				'trimestre'=> $trimestre);

			$mSQL=$this->db->insert_string('tiempo', $data);
			$this->db->query($mSQL);
		}
	}
}
?>
