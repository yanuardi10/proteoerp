<?
class Analisisvision extends Controller {

	function Analisisvision(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->rapyd->config->set_item("theme","repo");
		$this->datasis->modulo_id('50E',1);
	}
	function index(){
		redirect("/finanzas/analisisvision/ver");
	}
	function ver(){
		
		$this->rapyd->load("datagrid2");
		
		$MANO = substr(date("Y"),0,4)+0;
    $mmfecha = mktime( 0, 0, 0,1, 1, $MANO );
    $qfecha = date( "Ymd", mktime( 0, 0, 0, date("m",$mmfecha), date("d",$mmfecha), date("Y",$mmfecha) ));
    $qfechaf=date("Ymd");
    //$qfecha='20050101';
    //$qfechaf='20051231';
          
    $this->db->simple_query("DROP TABLE IF EXISTS vresumen");    
   
		$query = "CREATE TABLE vresumen 
          SELECT 
          EXTRACT(YEAR_MONTH FROM fecha) AS fecha, 
          sum((totalg-iva)*(tipo_doc<>'X')) ventas,
          0 AS inicial,
          0 AS compras, 
          0 AS ifinal,
          0 AS gastos, 
          0 AS inversion 
          FROM sfac WHERE fecha BETWEEN $qfecha AND $qfechaf 
          GROUP BY EXTRACT(YEAR_MONTH FROM fecha) ";
    $this->db->simple_query($query);

		$query = "INSERT INTO vresumen     
          SELECT 
          EXTRACT(YEAR_MONTH FROM fecha) AS fecha, 
          0.00 AS ventas,
          0.00 AS inicial,
          sum(montotot*(fecha<=actuali)) AS compras, 
          0.00 AS ifinal,
          0.00 AS gastos, 
          0.00 AS inversion 
          FROM scst WHERE fecha BETWEEN $qfecha AND $qfechaf 
          GROUP BY EXTRACT(YEAR_MONTH FROM fecha) ";
    $this->db->simple_query($query);
    
		$query = "INSERT INTO vresumen 
          SELECT 
          EXTRACT(YEAR_MONTH FROM fecha) AS fecha, 
          0.00 AS ventas,
          0.00 AS inicial,
          0.00 AS compras, 
          0.00 AS ifinal,
          sum(a.precio*(b.tipo<>'A')) AS gastos, 
          sum(a.precio*(b.tipo='A')) AS inversion 
          FROM gitser AS a JOIN mgas AS b ON a.codigo=b.codigo
          WHERE a.fecha BETWEEN $qfecha AND $qfechaf 
          GROUP BY EXTRACT(YEAR_MONTH FROM a.fecha) ";   
    $this->db->simple_query($query);
    
    $atts = array(
              'width'     =>'800',
              'height'    =>'600',
              'scrollbars'=>'yes',
              'status'    =>'yes',
              'resizable' =>'yes',
              'screenx'   =>'5',
              'screeny'   =>'5');    

		$link="ventas/analisis";
		$grid = new DataGrid2('Resumen de Gesti&oacute;n');
		$grid->column("Fecha",'<#fecha#>');
		$grid->column(anchor_popup($link,"Ventas",$atts), "<number_format><#ventas#>|2|,|.</number_format>" ,"align=right");
		$link="ventas/analisis";
		$grid->column(anchor_popup($link,"Compras",$atts), "<number_format><#compras#>|2|,|.</number_format>" ,"align=right");
		$link="finanzas/analisisgastos";
		$grid->column(anchor_popup($link,"Gastos",$atts), "<number_format><#gastos#>|2|,|.</number_format>" ,"align=right");
		$grid->column("Inversiones", "<number_format><#inversion#>|2|,|.</number_format>" ,"align=right");
		$select=array("fecha","sum(ventas) AS ventas","sum(inicial) AS inicial","sum(compras) AS compras","sum(ifinal) AS ifinal","sum(gastos) AS gastos","sum(inversion) AS inversion");//
		$grid->db->select($select);
		$grid->db->from('vresumen');
		$grid->db->groupby('fecha');
		$grid->build();
		
		$grid2 = new DataGrid2('Disponibilidad');
		$link="finanzas/analisisbanc";
		$grid2->column(anchor_popup($link,"Cajas",$atts),'<number_format><#cajas#>|2|,|.</number_format>',"align=right");
		$link="finanzas/analisisbanc";
		$grid2->column(anchor_popup($link,"Bancos",$atts),'<number_format><#bancos#>|2|,|.</number_format>',"align=right");
		$grid2->column("Total",'<number_format><#total#>|2|,|.</number_format>',"align=right");
		$select=array("SUM(saldo*(tbanco='CAJ')) AS cajas"," SUM(saldo*(tbanco<>'CAJ')) AS bancos"," SUM(saldo) AS total");//
		$grid2->db->select($select);
		$grid2->db->from('banc');
		$grid2->db->where("activo",'S');
		$grid2->build();
		
		$select=array("CONCAT(c.grupo,c.gr_desc) AS grupo","SUM(monto*IF(tipo_doc IN ('FC','GI','ND'),1,-1 )) AS monto");//
		$grid3 = new DataGrid2('Cartera Activa');
		$grid3->column("Grupo",'<#grupo#>');
		$grid3->column("Monto",'<number_format><#monto#>|2|,|.</number_format>',"align=right");		
		$grid3->db->select($select);
		$grid3->db->from('smov AS a');
		$grid3->db->join('scli AS b','a.cod_cli=b.cliente');
		$grid3->db->join('grcl AS c','b.grupo=c.grupo','LEFT');
		$grid3->db->groupby("b.grupo");
		$grid3->db->orderby("c.clase,c.gr_desc");
		$grid3->build();
		
		$select=array("c.gr_desc AS grupo","SUM(monto*IF(tipo_doc IN ('FC','GI','ND'),1,-1 )) AS monto");//
		$grid4 = new DataGrid2('Cartera Pasiva');
		$grid4->column("Grupo",'<#grupo#>');
		$grid4->column("Monto",'<number_format><#monto#>|2|,|.</number_format>',"align=right");		
		$grid4->db->select($select);
		$grid4->db->from('sprm AS a');
		$grid4->db->join('sprv AS b','a.cod_prv=b.proveed');
		$grid4->db->join('grpr AS c','b.grupo=c.grupo','LEFT');
		$grid4->db->groupby("b.grupo");		
		$grid4->build();
		
		$this->db->simple_query("DROP TABLE vresumen");
		
		$data['content'] = 
	"<table width='95%' border='0'>
  <tr>
    <td valign='top'><div style='overflow: auto; width: 100%;'>$grid->output</div></td>
    <td valign='top'><div style='overflow: auto; width: 100%;'>$grid2->output</div></td>
  </tr>
  <tr>
    <td valign='top'><div style='overflow: auto; width: 100%;'>$grid3->output</div></td>
    <td valign='top'><div style='overflow: auto; width: 100%;'>$grid4->output</div></td>
  </tr>
</table>
	 	";
	 	$data['title']   = "<h1>Visi&oacute;n General</h1>";
	 	$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
	 	$this->load->view('view_ventanas', $data);
		
		
	}
}	
	
	
	
	?>