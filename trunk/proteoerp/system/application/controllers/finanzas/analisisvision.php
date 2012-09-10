<?php
class Analisisvision extends Controller {

	function Analisisvision(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->rapyd->config->set_item("theme","repo");
		$this->datasis->modulo_id('50E',1);
	}
	
	function index(){
		$this->datasis->modintramenu( 900, 600, "finanzas/analisisvision" );
		redirect("/finanzas/analisisvision/ver");
	}
	
	function ver(){
		
		//$this->rapyd->load("datagrid2");
		//$this->load->library('table');
		
		$MANO = substr(date("Y"),0,4)+0;
		$mmfecha = mktime( 0, 0, 0,1, 1, $MANO );
		$qfecha = date( "Ymd", mktime( 0, 0, 0, date("m",$mmfecha), date("d",$mmfecha), date("Y",$mmfecha) ));
		$qfechaf=date("Ymd");
          

   
		$mSQL = "
SELECT fecha, ventas, compras, util, round(100*(ventas-compras)/ventas,2) putil ,gastos, round(100*(gastos)/ventas,2) pgastos, inversion, nutil, ingreso, deposito,
ROUND((SELECT valor FROM valores WHERE nombre='EST_PERDIDA')*ventas/100,2)    perdida,
ROUND((SELECT valor FROM valores WHERE nombre='EST_CMUNICIPAL')*ventas/100,2) municipal,
ROUND((SELECT valor FROM valores WHERE nombre='EST_ISLR')*(nutil)/100,2)      islr
FROM (
SELECT fecha, sum(ventas) ventas, sum(compras) compras, sum(ventas-compras) util ,sum(gastos) gastos, sum(inversion)  inversion,
sum(ventas-compras-gastos-inversion) nutil, sum(ingreso ) ingreso, sum(deposito) deposito
FROM (
SELECT EXTRACT(YEAR_MONTH FROM recep) fecha, 0 ventas, sum(montotot*(fecha<=actuali)*IF(tipo_doc='FC',1,-1)) compras, 0 gastos, 0 inversion, 0 ingreso, 0 deposito  
FROM scst WHERE YEAR(recep) = YEAR(CURDATE()) 
GROUP BY EXTRACT(YEAR_MONTH FROM fecha) 
UNION ALL 
SELECT  EXTRACT(YEAR_MONTH FROM fecha) AS fecha, 0 ventas, 0 compras, 
sum(a.precio*(b.tipo<>'A')) AS gastos, sum(a.precio*(b.tipo='A')) AS inversion, 0 ingreso, 0 deposito 
FROM gitser AS a JOIN mgas AS b ON a.codigo=b.codigo
WHERE YEAR(a.fecha) = YEAR(CURDATE()) 
GROUP BY EXTRACT(YEAR_MONTH FROM a.fecha) 
UNION ALL
SELECT EXTRACT(YEAR_MONTH FROM fecha) fecha, sum( totals*if(tipo_doc='F',1,-1) ) ventas, 0 compras, 0 gastos, 0 inversion, 0 ingreso, 0 deposito 
FROM sfac WHERE tipo_doc<>'X' AND referen<>'P' AND YEAR(fecha) = YEAR(CURDATE()) 
GROUP BY EXTRACT(YEAR_MONTH FROM fecha)
UNION ALL
SELECT EXTRACT(YEAR_MONTH FROM f_factura) fecha, 0 ventas, 0 compras, 0 AS gastos, 0 AS inversion, sum(monto) ingreso, 0 deposito
FROM sfpa 
WHERE YEAR(f_factura) = YEAR(curdate())
GROUP BY EXTRACT(YEAR_MONTH FROM f_factura)
UNION ALL
SELECT 
EXTRACT(YEAR_MONTH FROM fecha) fecha, 0 ventas, 0 compras, 0 AS gastos, 0 AS inversion, 0 ingreso, sum(monto) deposito
FROM bmov 
WHERE YEAR(fecha) = YEAR(curdate()) AND tipo_op='ND'  AND codbanc='99' AND codbanc='99' AND clipro='O' AND codcp='CAJAS'
GROUP BY EXTRACT(YEAR_MONTH FROM fecha) 
) MECO
GROUP BY fecha
) pico
";

		$atts = array(
			'width'     =>'800',
			'height'    =>'600',
			'scrollbars'=>'yes',
			'status'    =>'yes',
			'resizable' =>'yes',
			'screenx'   =>'5',
			'screeny'   =>'5');    

			$ladata     = '';
			$tventas    = 0;
			$tcompras   = 0;
			$tutil      = 0;
			$tputil     = 0;
			$tgastos    = 0;
			$tpgastos   = 0;
			$tinversion = 0;
			$tnutil     = 0;
			$tingreso   = 0;
			$tdeposito  = 0;
			$tperdida   = 0;
			$tmunicipal = 0;
			$tislr      = 0;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$ladata .= "{ fecha:'".  $row->fecha.    "', ";
					$ladata .= "ventas:'".   $row->ventas.   "', ";
					$ladata .= "compras:'".  $row->compras.  "', ";
					$ladata .= "util:'".     $row->util.     "', ";
					$ladata .= "putil:'".    $row->putil.    "', ";
					$ladata .= "gastos:'".   $row->gastos.   "', ";
					$ladata .= "pgastos:'".  $row->pgastos.  "', ";
					$ladata .= "inversion:'".$row->inversion."', ";
					$ladata .= "nutil:'".    $row->nutil.    "', ";
					$ladata .= "ingreso:'".  $row->ingreso.  "', ";
					$ladata .= "deposito:'". $row->deposito. "', ";
					$ladata .= "perdida:'".  $row->perdida.   "', ";
					$ladata .= "municipal:'".$row->municipal."',";
					$ladata .= "islr:'".     $row->islr.        "'},\n ";

					$tventas    += $row->ventas;
					$tcompras   += $row->compras;
					$tutil      += $row->util;
					$tputil     += $row->putil;
					$tgastos    += $row->gastos;
					$tpgastos   += $row->pgastos;
					$tinversion += $row->inversion;
					$tnutil     += $row->nutil;
					$tingreso   += $row->ingreso;
					$tdeposito  += $row->deposito;
					$tperdida   += $row->perdida;
					$tmunicipal += $row->municipal;
					$tislr      += $row->islr;
				}
			}

			$grid = '
jQuery("#resumen").jqGrid({
	datatype: "local",
	shrinkToFit: false,
	autowidth: true,
	height: "200",
	colNames:["Fecha", "Ventas", "Compras", "Utilidad","%","Gastos", "%", "Inversion", "Neto","Ingreso", "Deposito","Perdida","I.Mun.","ISLR"],
	colModel:[
		{name:"fecha",     index:"fecha",     width:50, align:"center",sorttype:"text" },
		{name:"ventas",    index:"ventas",    width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"compras",   index:"compras",   width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"util",      index:"util",      width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"putil",     index:"putil",     width:50, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"gastos",    index:"gastos",    width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"pgastos",   index:"pgastos",   width:50, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"inversion", index:"inversion", width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"nutil",     index:"nutil",     width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"ingreso",   index:"ingreso",   width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"deposito",  index:"deposito",  width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"perdida",   index:"perdida",   width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"municipal", index:"municipal", width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"islr",      index:"islr",      width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }}
	],
	multiselect: false,
	footerrow: true,
	loadComplete: function () {
		$(this).jqGrid(\'footerData\',\'set\',
		{fecha:"TOTALES", ventas:"'.$tventas.'", compras:"'.$tcompras.'", util:"'.$tutil.'", putil:"0", gastos:"'.$tgastos.'", pgastos:"0", inversion:"'.$tinversion.'", nutil:"'.$tnutil.'", ingreso:"'.$tingreso.'", deposito:"'.$tdeposito.'", perdida:"'.$tperdida.'", municipal:"'.$tmunicipal.'", islr:"'.$tislr.'"});
	},
	caption: "Resumen de Gesti&oacute;n"
});
var mydata = [ '."\n$ladata];\n";
			
			$grid .= ' for(var i=0;i<=mydata.length;i++) jQuery("#resumen").jqGrid(\'addRowData\',i+1,mydata[i]);'."\n";

			$mSQL = '
SELECT fecha, caja, cajero, usuario, sum(recibido) recibido, sum(ingreso) ingreso, sum(diferen) diferen, sum(recaudado) recaudado, sum(ingreso-recaudado) difcierre
FROM (
SELECT EXTRACT(YEAR_MONTH FROM fecha) fecha, caja, cajero, usuario, sum(recibido) recibido, sum(ingreso) ingreso, sum(ingreso-recibido) diferen, 0 recaudado
FROM rcaj
WHERE year(fecha)>=year(curdate())
GROUP BY  EXTRACT(YEAR_MONTH FROM fecha), caja, cajero
UNION ALL
SELECT EXTRACT(YEAR_MONTH FROM f_factura) fecha, "99" caja, cobrador, usuario, 0, 0, 0, sum(monto) recaudado
FROM sfpa 
WHERE YEAR(f_factura)>=YEAR(curdate()) 
GROUP BY  EXTRACT(YEAR_MONTH FROM f_factura),  cobrador, usuario 
) AAA 
GROUP BY fecha, caja, cajero 
';

			$ladata     = '';
			$trecibido  = 0;
			$tingreso   = 0;
			$tdiferen   = 0;
			$tdifcierre = 0;
			$trecaudado = 0;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$ladata .= "{ fecha:'".   $row->fecha.     "', ";
					$ladata .= "caja:'".      $row->caja.      "', ";
					$ladata .= "cajero:'".    $row->cajero.    "', ";
					$ladata .= "usuario:'".   $row->usuario.   "', ";
					$ladata .= "recibido:'".  $row->recibido.  "', ";
					$ladata .= "ingreso:'".   $row->ingreso.   "', ";
					$ladata .= "diferen:'".   $row->diferen.   "', ";
					$ladata .= "recaudado:'". $row->recaudado. "', ";
					$ladata .= "difcierre:'". $row->difcierre. "'},\n";

					$trecibido  += $row->recibido;
					$tingreso   += $row->ingreso;
					$tdiferen   += $row->diferen;
					$tdifcierre += $row->difcierre;
					$trecaudado += $row->recaudado;
				}
			} 			


			$grid1 = '
jQuery("#cierres").jqGrid({
	datatype: "local",
	height: "160",
	colNames:["Fecha", "Caja", "Cajero", "Usuario","Recibido","Sistema", "Faltante","Recaudado","Dif.Rec."],
	colModel:[
		{name:"fecha",      index:"fecha",     width:50, align:"center", sorttype:"text" },
		{name:"caja",       index:"caja",      width:40, align:"center", sorttype:"text" },
		{name:"cajero",     index:"cajero",    width:50, align:"center", sorttype:"text" },
		{name:"usuario",    index:"usuario",   width:60, align:"left", sorttype:"text" },
		{name:"recibido",   index:"recibido",  width:80, align:"right",  sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"ingreso",    index:"gastos",    width:80, align:"right",  sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"diferen",    index:"diferen",   width:60, align:"right",  sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"recaudado",  index:"recaudado", width:60, align:"right",  sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"difcierre",  index:"difcierre", width:60, align:"right",  sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }}
	],
	multiselect: false,
	footerrow: true,
	loadComplete: function () {
		$(this).jqGrid(\'footerData\',\'set\',
		{fecha:\'TOTALES\', caja:"", cajero:"", usuario:"", recibido:"'.$trecibido.'", ingreso:"'.$tingreso.'", difer:"'.$tdiferen.'", ingreso:"'.$tingreso.'", diferen:"'.$tdiferen.'", recaudado:"'.$trecaudado.'", difcierre:"'.$tdifcierre.'"});
	},
	caption: "Resumen de Cierres de Caja"
});
var mydata1 = [ '."\n$ladata];\n";
			
			$grid1 .= ' for(var i=0;i<=mydata1.length;i++) jQuery("#cierres").jqGrid(\'addRowData\',i+1,mydata1[i]);'."\n";



/*
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

		$grid->db->_protect_identifiers=false;
		$grid->db->from($mSQL);
		//$grid->db->from('vresumen');
		//$grid->db->groupby('fecha');
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
*/		
		$data['centerpanel'] = 
	"<table width='95%' border='0'>
  <tr>
	<td valign='top'>
		<table id=\"resumen\"></table>
	</td>
  </tr><tr>
	<td valign='top'>
		<table id=\"cierres\"></table>
	</td>
"

/*
    <td valign='top'><div style='overflow: auto; width: 100%;'>$grid2->output</div></td>
  </tr>
  <tr>
    <td valign='top'><div style='overflow: auto; width: 100%;'>$grid3->output</div></td>
    <td valign='top'><div style='overflow: auto; width: 100%;'>$grid4->output</div></td>
*/
."  </tr>
</table>
<script type=\"text/javascript\">
$(function () {
	tableToGrid(\"#consulta\", { height: \"auto\",width:500, pager:\"#mypager\", caption:\"Resumen Mensual\"});
});
</script>
	 	";

/*
		$data['centerpanel'] = 
'<table id="consulta">
    <tr>
        <th>header 1</th>
        <th>header 2</th>
    </tr>
    <tbody>
        <tr>
            <td>data 1</td>
            <td>data 1</td>
        </tr>
        <tr>
            <td>data 2</td>
            <td>data 2</td>
        </tr>
    </tbody>
</table>
<script type="text/javascript">
$(function () {
	tableToGrid("#consulta", {});
});
</script>
';*/
		$data['funciones'] = $grid.$grid1;
	 	$data['title']     = "Visi&oacute;n General";
	 	$data['encabeza']  = "Visi&oacute;n General";
	 	$data["head"]      = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
	 	$this->load->view('/jqgrid/ventanas', $data);
		
		
	}
}	
	
	
	?>