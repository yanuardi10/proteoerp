<?php
class Analisisvision extends Controller {

	function Analisisvision(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->rapyd->config->set_item("theme","repo");
		//$this->datasis->modulo_id('50E',1);
	}
	
	function index(){
		$this->datasis->modintramenu( 900, 600, "finanzas/analisisvision" );
		redirect("/finanzas/analisisvision/ver");
	}
	
	function ver(){
		
		$data['funciones'] = "";
	 	$data['title']     = "Resumen de Gestion";
	 	$data['encabeza']  = "Resumen de Gestion";
	 	$data["head"]      = ""; //script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
	 	$this->load->view('consulta', $data);
	}
	
	function general(){
		$MANO = substr(date("Y"),0,4)+0;
		$mmfecha = mktime( 0, 0, 0,1, 1, $MANO );
		$qfecha = date( "Ymd", mktime( 0, 0, 0, date("m",$mmfecha), date("d",$mmfecha), date("Y",$mmfecha) ));
		$qfechaf=date("Ymd");

		$perdida   = $this->datasis->traevalor('EST_PERDIDA');
		$municipal = $this->datasis->traevalor('EST_CMUNICIPAL');
		$islr      = $this->datasis->traevalor('EST_ISLR');

          
		$mSQL = "
SELECT CONCAT(MID(fecha,1,4),'-',MID(fecha,5,2)) fecha, ventas, compras, util, round(100*(ventas-compras)/ventas,2) putil ,gastos, round(100*(gastos)/ventas,2) pgastos, inversion, nutil, ingreso, deposito,
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
sum(a.precio) AS gastos, sum(a.precio*(b.tipo='A'))*0 AS inversion, 0 ingreso, 0 deposito 
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
	height: "270",
	colNames:["Mes", "Ventas", "Compras", "Utilidad","%","Gastos", "%", "Inversion", "Neto","Ingreso", "Deposito","Perdida '.$perdida.'%","I.Mun.'.$municipal.'%","ISLR '.$islr.'%"],
	colModel:[
		{name:"fecha",     index:"fecha",     width:50, align:"center",sorttype:"text" },
		{name:"ventas",    index:"ventas",    width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"compras",   index:"compras",   width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"util",      index:"util",      width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"putil",     index:"putil",     width:50, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"gastos",    index:"gastos",    width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"pgastos",   index:"pgastos",   width:50, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"inversion", index:"inversion", width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }, hidden:true},
		{name:"nutil",     index:"nutil",     width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"ingreso",   index:"ingreso",   width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }, hidden:true},
		{name:"deposito",  index:"deposito",  width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }, hidden:true},
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
			$centerpanel = 	"
<table width='99%' border='0'>
	<tr>
		<td valign='top'>
			<table id=\"resumen\"></table>
		</td>
	</tr>
</table>
<script type=\"text/javascript\">
$(function () {
	tableToGrid(\"#consulta\", { height: \"auto\",width:500, pager:\"#mypager\", caption:\"Resumen Mensual\"});
});
</script>
";


		echo  "<script type=\"text/javascript\">".$grid."</script>".$centerpanel;
		
	}


	function ganancia(){
		$MANO = substr(date("Y"),0,4)+0;
		$mmfecha = mktime( 0, 0, 0,1, 1, $MANO );
		$qfecha = date( "Ymd", mktime( 0, 0, 0, date("m",$mmfecha), date("d",$mmfecha), date("Y",$mmfecha) ));
		$qfechaf=date("Ymd");

          
		$mSQL = '
SELECT CONCAT(year(a.fecha), "-", LPAD(month(a.fecha),2,"0")) AS mes, b.grupo, c.nom_grup, a.codigo, b.descrip,  sum(a.cantidad) AS cantidad, round(sum((a.promedio * a.cantidad)),2) AS costo,sum(a.venta) AS venta,round(sum((`a`.`venta` - (`a`.`promedio` * `a`.`cantidad`))),2) AS `util`,round(((sum((`a`.`venta` - (`a`.`promedio` * `a`.`cantidad`))) * 100) / sum(`a`.`venta`)),2) AS `margen` 
FROM costos a LEFT JOIN sinv b ON a.codigo = b.codigo LEFT JOIN grup c ON b.grupo=c.grupo
WHERE YEAR(a.fecha) = YEAR(curdate()) AND MID(b.tipo,1,1) <> "S" AND a.origen = "3I" 
GROUP BY  year(a.fecha), month(a.fecha) 
HAVING cantidad <> 0 
UNION ALL 
SELECT CONCAT(year(a.fecha), "-", LPAD(month(a.fecha),2,"0")), month(a.fecha)) AS mes, "SERV" grupo, "SERVICIOS" nom_grup, a.codigoa, "SERVICIOS VARIOS" descrip,  sum(a.cana) AS cantidad, 0 AS costo, sum(a.tota) AS venta, sum(a.tota) AS util, 100 AS margen 
FROM sitems a LEFT JOIN sinv b ON a.codigoa = b.codigo LEFT JOIN grup c ON b.grupo=c.grupo 
WHERE YEAR(a.fecha) = YEAR(curdate()) AND substr(b.tipo,1,1) = "S"
GROUP BY year(a.fecha), month(a.fecha) 
HAVING cantidad <> 0
';

		$atts = array(
			'width'     =>'800',
			'height'    =>'600',
			'scrollbars'=>'yes',
			'status'    =>'yes',
			'resizable' =>'yes',
			'screenx'   =>'5',
			'screeny'   =>'5');    

			$ladata     = '';
			$tcantidad  = 0;
			$tcosto     = 0;
			$tventa     = 0;
			$tutil      = 0;
			$margen     = 0;

// mes, grupo, nom_grup, codigo, descrip, cantidad, costo, venta, util, margen

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$ladata .= "{ mes:'".    $row->mes.      "', ";
					$ladata .= "nom_grup:'". $row->nom_grup. "', ";
					$ladata .= "cantidad:'". $row->cantidad. "', ";
					$ladata .= "costo:'".    $row->costo.    "', ";
					$ladata .= "venta:'".    $row->venta.    "', ";
					$ladata .= "util:'".     $row->util.     "', ";
					$ladata .= "margen:'".   $row->margen.   "'},\n ";

					$tcantidad  += $row->cantidad;
					$tcosto     += $row->costo;
					$tventa     += $row->venta;
					$tutil      += $row->util;
					$margen     += $row->margen;
				}
			}

			$grid = '
jQuery("#ganancia").jqGrid({
	datatype: "local",
	shrinkToFit: false,
	autowidth: true,
	height: "270",
	colNames:["Mes", "Descripcion", "Cantidad", "Costo","Venta","Utilidad", "%"],
	colModel:[
		{name:"mes",      index:"mes",      width:50, align:"center",sorttype:"text" },
		{name:"nom_grup", index:"nom_grup", width:150, align:"left", sorttype:"text" },
		{name:"cantidad", index:"cantidad", width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"costo",    index:"costo",    width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"venta",    index:"venta",    width:50, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"util",     index:"util",     width:80, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }},
		{name:"margen",   index:"margen",   width:50, align:"right", sorttype:"float", formatter:"number", formatoptions: {decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }}
	],
	multiselect: false,
	footerrow: true,
	loadComplete: function () {
		$(this).jqGrid(\'footerData\',\'set\',
		{mes:"", nom_grup:"TOTALES", costo:"'.$tcosto.'", venta:"'.$tventa.'", util:"'.$tutil.'", margen:"0" });
	},
	caption: "Resumen de Utilidad Bruta"
});
var mydatag = [ '."\n$ladata];\n";
			
			$grid .= ' for(var i=0;i<=mydatag.length;i++) jQuery("#ganancia").jqGrid(\'addRowData\',i+1,mydatag[i]);'."\n";

			$centerpanel = 	"
<table width='99%' border='0'>
	<tr>
		<td valign='top'>
			<table id=\"ganancia\"></table>
		</td>
	</tr>
</table>
<script type=\"text/javascript\">
$(function () {
	tableToGrid(\"#ganancia\", { height: \"auto\",width:500, pager:\"#mypagerg\", caption:\"Resumen de Ganancias\"});
});
</script>
";


		echo  "<script type=\"text/javascript\">".$grid."</script>".$centerpanel;
		
	}




	function meco() {
		$mSQL = '
SELECT year(a.fecha) AS anno, month(a.fecha) AS mes, b.grupo, c.nom_grup, a.codigo, b.descrip,  sum(a.cantidad) AS cantidad, round(sum((a.promedio * a.cantidad)),2) AS costo,sum(a.venta) AS venta,round(sum((`a`.`venta` - (`a`.`promedio` * `a`.`cantidad`))),2) AS `util`,round(((sum((`a`.`venta` - (`a`.`promedio` * `a`.`cantidad`))) * 100) / sum(`a`.`venta`)),2) AS `margen` 
FROM costos a LEFT JOIN sinv b ON a.codigo = b.codigo LEFT JOIN grup c ON b.grupo=c.grupo
WHERE YEAR(a.fecha) = YEAR(curdate()) AND MID(b.tipo,1,1) <> "S" AND a.origen = "3I" 
GROUP BY  year(a.fecha), month(a.fecha) 
HAVING cantidad <> 0 
UNION ALL 
SELECT year(a.fecha) AS anno, month(a.fecha) AS mes, "SERV" grupo, "SERVICIOS" nom_grup, a.codigoa, "SERVICIOS VARIOS" descrip,  sum(a.cana) AS cantidad, 0 AS costo, sum(a.tota) AS venta, sum(a.tota) AS util, 100 AS margen 
FROM sitems a LEFT JOIN sinv b ON a.codigoa = b.codigo LEFT JOIN grup c ON b.grupo=c.grupo 
WHERE YEAR(a.fecha) = YEAR(curdate()) AND a.fecha < 20120901 AND substr(b.tipo,1,1) = "S"
GROUP BY year(a.fecha), month(a.fecha) 
HAVING cantidad <> 0
		';
		
	}



	//***************************************
	//
	// Cobranza
	//
	//***************************************
	function cierres(){
		$MANO = substr(date("Y"),0,4)+0;
		$mmfecha = mktime( 0, 0, 0,1, 1, $MANO );
		$qfecha = date( "Ymd", mktime( 0, 0, 0, date("m",$mmfecha), date("d",$mmfecha), date("Y",$mmfecha) ));
		$qfechaf=date("Ymd");
		$grid = '';

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
	height: "300",
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

			$centerpanel = 	"
<table width='99%' border='0'>
	<tr>
		<td valign='top'>
		<table id=\"cierres\"></table>
	</td>
  </tr>
</table>
<script type=\"text/javascript\">
$(function () {
	tableToGrid(\"#consulta\", { height: \"auto\",width:500, pager:\"#mypager\", caption:\"Resumen Mensual\"});
});
</script>
";

		echo  "<script type=\"text/javascript\">".$grid.$grid1."</script>".$centerpanel;
	}



	//******************************************************************
	//
	//   Menu Izquierdo
	//
	//******************************************************************
	function opciones(){
		header("Content-type: text/xml; charset=utf-8");
		echo "<?xml version='1.0'".' encoding="utf-8"?>
<rows>
    <page>1</page>
    <total>1</total>
    <records>1</records>
    <row><cell>1</cell><cell>Analisis General</cell><cell></cell><cell>0</cell><cell>1</cell><cell>10</cell><cell>false</cell><cell>false</cell></row>

    <row><cell>2</cell><cell>Resumen de gestion</cell><cell>general </cell><cell>1</cell><cell>2</cell><cell>3</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>3</cell><cell>Ingresos por Caja </cell><cell>cierres </cell><cell>1</cell><cell>4</cell><cell>5</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>4</cell><cell>Ganancias         </cell><cell>ganancia</cell><cell>1</cell><cell>6</cell><cell>7</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>5</cell><cell>Analisis Diario   </cell><cell>mensual </cell><cell>1</cell><cell>8</cell><cell>9</cell><cell>true</cell><cell>true</cell></row>

    <row><cell>6</cell><cell>Venta Diaria</cell><cell></cell><cell>0</cell><cell>11</cell><cell>18</cell><cell>false</cell><cell>false</cell></row>

    <row><cell>7</cell><cell>Disponible</cell><cell>manipex.html</cell><cell>1</cell><cell>12</cell><cell>13</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>8</cell><cell>Disponible</cell><cell>getex.html  </cell><cell>1</cell><cell>14</cell><cell>15</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>9</cell><cell>Disponible</cell><cell>setex.html  </cell><cell>1</cell><cell>16</cell><cell>17</cell><cell>true</cell><cell>true</cell></row>

    <row><cell>10</cell><cell>Analisis Inventario</cell><cell></cell><cell>0</cell><cell>19</cell><cell>32</cell><cell>false</cell><cell>false</cell></row>

    <row><cell>11</cell><cell>Disponible</cell><cell>multiex.html     </cell><cell>1</cell><cell>20</cell><cell>21</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>12</cell><cell>Disponible</cell><cell>masterex.html    </cell><cell>1</cell><cell>22</cell><cell>23</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>13</cell><cell>Disponible</cell><cell>subgrid.html     </cell><cell>1</cell><cell>24</cell><cell>25</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>14</cell><cell>Disponible</cell><cell>subgrid_grid.html</cell><cell>1</cell><cell>26</cell><cell>27</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>15</cell><cell>Disponible</cell><cell>resizeex.html    </cell><cell>1</cell><cell>28</cell><cell>28</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>16</cell><cell>Disponible</cell><cell>bigset.html      </cell><cell>1</cell><cell>30</cell><cell>31</cell><cell>true</cell><cell>true</cell></row>

    <row><cell>17</cell><cell>Caja y Bancos</cell><cell></cell><cell>0</cell><cell>33</cell><cell>44</cell><cell>false</cell><cell>false</cell></row>
    <row><cell>18</cell><cell>Disponible</cell><cell>cmultiex.html </cell><cell>1</cell><cell>34</cell><cell>35</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>19</cell><cell>Disponible</cell><cell>jsubgrid.html </cell><cell>1</cell><cell>36</cell><cell>37</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>20</cell><cell>Disponible</cell><cell>loadcml.html  </cell><cell>1</cell><cell>38</cell><cell>39</cell><cell>true</cell><cell>true</cell></row>

</rows>';		
		
	}
	
}	
?>
<?php
/*
class Analisisbanc extends Controller {

	function index() {
		$this->rapyd->load('dataform');

		$script ='
		function filtro(){
			$("#tr_valor").hide();
			$("#tr_oper").hide();
			campo=$("#campo").val();
			valor.value="";
			if(campo.length>0){
				if(campo=="tipo_op"){
					$("#tr_oper").show();
				}else{
					$("#tr_valor").show();
				}
			}
		}
		$(document).ready(function(){
			$("#tr_cod").hide();
			$(".inputnum").numeric(".");
			$("#campo").change(function () { filtro(); }).change();
		});';

		$fechad=date('Y/m/d');
		$date = new DateTime();
		$date->setDate(substr($fechad, 0, 4),substr($fechad, 5, 2),substr($fechad, 8,2));
		$date->modify('-6 month');
		$fechad=$date->format('Y/n/d');

		$filter = new DataForm("finanzas/analisisbanc/movimientos/process");
		$filter->title('Filtro de Caja y Bancos');
		$filter->script($script, "create");
		$filter->script($script, "modify");

		//
		$filter->ano = new inputField("","ano");
		$filter->ano->insertValue=date("Y");
		//

		$filter->submit("btnsubmit","Buscar");
		$filter->build_form();

		$mSQL_1=$this->db->query("SELECT tipocta,CASE (tipocta)
			WHEN 'C' THEN 'CUENTAS CORRIENTES'
			WHEN 'A' THEN 'CUENTAS DE AHORRO'
			WHEN 'P' THEN 'CUENTAS DE PARTICIPACION A PLAZO'
			WHEN 'K' THEN 'CAJAS' END as tipocta2  FROM banc WHERE tbanco<>'CAJ' GROUP BY tipocta");//

		$mSQL=$this->db->query("SELECT tbanco, banco, numcuent, cuenta, saldo, codbanc, tipocta,moneda
            FROM  banc
            WHERE activo='S' AND tbanco<>'CAJ'
            ORDER BY moneda,codbanc");//tbanco='CAJ',

	$mSQL_12=$this->db->query("SELECT tipocta,CASE (tipocta)
			WHEN 'C' THEN 'CUENTAS CORRIENTES'
			WHEN 'A' THEN 'CUENTAS DE AHORRO'
			WHEN 'P' THEN 'CUENTAS DE PARTICIPACION A PLAZO'
			WHEN 'K' THEN 'CAJAS' END as tipocta2  FROM banc WHERE tbanco='CAJ' GROUP BY tipocta");

		$mSQL2=$this->db->query("SELECT tbanco, banco, numcuent, cuenta, saldo, codbanc, tipocta,moneda
          FROM  banc
          WHERE activo='S' AND tbanco='CAJ'
          ORDER BY moneda,codbanc");

		$mSQLmon=$this->db->query("SELECT moneda FROM banc WHERE activo='S' GROUP BY moneda");

		if(isset($_POST['ano']))$ano=$_POST['ano']; else $ano=date("Y");
		//$data2['meses']= $meses;
		$data2['ano']= date("Y");//$ano;
		$data2['monedas']= $mSQLmon->result();
		$data2['grupo2']= $mSQL_12->result();
		$data2['detalle2']= $mSQL2->result();
		$data2['grupo']= $mSQL_1->result();
		$data2['detalle']= $mSQL->result();
		$data['content']= //$filter->output.
		$this->load->view('view_analisisbanc', $data2,TRUE);

		//$data['content'] = $filter->output;
		$data['title']   = "<h1>Relaci&oacute;n de Caja y Bancos</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function movmes(){}

function meses(){
	$this->rapyd->load("datagrid2","dataform");
	$cod=$this->uri->segment(4);
	$ano=$this->uri->segment(5);

	$filter = new DataForm("finanzas/analisisbanc/meses/process");
	$filter->title('Filtro de Caja y Bancos');

	$filter->ano = new dropdownField("A&ntilde;o", "ano");
	$filter->ano->option($ano,$ano);
	$filter->ano->options('SELECT ano,ano as ano2 FROM bsal GROUP BY ano ORDER BY ano');
	$filter->ano->style='width:80px;';


	$filter->cod = new inputField("C&oacute;digo","cod");
	$filter->cod->insertValue=$cod;
	$filter->cod->size=5;
	$filter->cod->maxlength=2;
	$filter->cod->type='hidden';

	$filter->button("btnsubmit", "Buscar", form2uri(site_url("/finanzas/analisisbanc/meses"),array('cod','ano')), $position="BL");//
	$filter->build_form();

	function blanco($num){
		if(empty($num)||$num==0){
		 return '';
		}else{
			return number_format($num,2,',','.');
		}
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////
	$bdata=$this->datasis->damerow("SELECT saldo01,saldo02,saldo03,saldo04,saldo05,saldo06,saldo06,saldo07,saldo08,saldo09,saldo10,saldo11,saldo12 FROM bsal WHERE codbanc='$cod' AND ano='$ano'");
	$d=array();
	//print_r($bdata);
	//if($bdata['saldo01']!=0){
	if(!($bdata==NULL)){
		for($i=0;$i<12;++$i){
			$r='saldo'.str_pad($i+1,2,'0',STR_PAD_LEFT);
			$d[$i]['saldo']=$bdata[$r];
			switch($i+1){
				case 1 :$d[$i]['mes']= 'Enero'     ;$d[$i]['m']=$i+1;break;
				case 2 :$d[$i]['mes']= 'Febrero'   ;$d[$i]['m']=$i+1;break;
				case 3 :$d[$i]['mes']= 'Marzo'     ;$d[$i]['m']=$i+1;break;
				case 4 :$d[$i]['mes']= 'Abril'     ;$d[$i]['m']=$i+1;break;
				case 5 :$d[$i]['mes']= 'Mayo'      ;$d[$i]['m']=$i+1;break;
				case 6 :$d[$i]['mes']= 'Junio'     ;$d[$i]['m']=$i+1;break;
				case 7 :$d[$i]['mes']= 'Julio'     ;$d[$i]['m']=$i+1;break;
				case 8 :$d[$i]['mes']= 'Agosto'    ;$d[$i]['m']=$i+1;break;
				case 9 :$d[$i]['mes']= 'Septiembre';$d[$i]['m']=$i+1;break;
				case 10:$d[$i]['mes']= 'Octubre'   ;$d[$i]['m']=$i+1;break;
				case 11:$d[$i]['mes']= 'Noviembre' ;$d[$i]['m']=$i+1;break;
				case 12:$d[$i]['mes']= 'Diciembre' ;$d[$i]['m']=$i+1;break;
			}
		}
	}
	$link="finanzas/analisisbanc/movimientos/$cod/$ano/<#m#>";
	//print_r($d);
	$grid = new DataGrid2("Movimientos por meses",$d);
	$grid->use_function('blanco');
	$grid->column('Mes'   ,anchor($link, '<#mes#>') ,"align=left");
	$grid->column('Saldo' ,"<blanco><#saldo#></blanco>"   ,"align=right");
	//$grid->column("m","<#m#>","align=left");
	$grid->build();

	//memowrite( $grid->db->last_query());
	$salida= anchor("finanzas/analisisbanc/","Atras");

	$data['content'] = $filter->output.$salida.$grid->output;
	$data['title']   = "<h1>Relaci&oacute;n de Caja y Bancos</h1>";
	$data['head']    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
	$this->load->view('view_ventanas', $data);
	}

	function movimientos(){
		$this->rapyd->load('dataform','datagrid2');

		if((isset($_POST['fechad']))&&($_POST['fechad']!='')){
			$fechad=$_POST['fechad'];
		}else{
			$fechad=date('Y/m/d');
			$date = new DateTime();
			$date->setDate(substr($fechad, 0, 4),substr($fechad, 5, 2),substr($fechad, 8,2));
			$date->modify("-6 month");
			$fechad=$date->format('Y/n/j');
		}

		if((isset($_POST['fechah']))&&($_POST['fechah']!=''))
			$fechah=$_POST['fechah'];
		else
			$fechah=date('Y/m/d');

		if(isset($_POST['cod']))
			$cod=$_POST['cod'];
		elseif($cod=$this->uri->segment(4)){
			}else redirect("finanzas/analisisbanc");
		if(isset($_POST['campo']))
			$campo=$_POST['campo'];
		else
			$campo='';
		if(isset($_POST['oper'])){
			$oper=$_POST['oper'];
			if($oper!='')$valor=$oper;
		}
		if(isset($_POST['valor'])){
			$valor=$_POST['valor'];
			if($valor==''){
				if(isset($_POST['oper'])){
					$oper=$_POST['oper'];
					if($oper!='') $valor=$oper; else $campo='';
				}
			}
		}else{
			$campo='';
		}
		if(isset($_POST['oper'])){
			$oper=$_POST['oper'];
		 if($oper!='')$valor=$oper;
		}
		if(isset($_POST['orden'])){
			$orden=$_POST['orden'];
		 if($orden=='')$orden='fecha';
		}else{
			$orden='fecha';
		}
		if(isset($_POST['desc'])){
			$desc=$_POST['desc'];
		 if($desc=='')$desc='asc';
		 if($desc=='0')$desc='asc';
		 if($desc=='1')$desc='desc';
		}else{
			$desc='asc';
		}

		$cod=$this->uri->segment(4);
		$ano=$this->uri->segment(5);
		$mes=$this->uri->segment(6);

		if($cod=='process'){
			if(isset($_POST['cod'])){
				$cod=$_POST['cod'];
			}else{
				$cod='|!';
				$ano='';
				$mes='';
			}
		}else{
			if((!empty($ano))&&(!(empty($mes)))){
				for ($dia=28;$dia<=31;$dia++) if(checkdate($mes,$dia,$ano)){ $mes=str_pad($mes,2,'0',STR_PAD_LEFT); $fechah="$ano/$mes/$dia";}
				$fechad="$ano/$mes/01";
			}
		}
		//echo $cod.'/'.$ano.'/'.$mes;
		//echo $fechad."---".$fechah;

		$script ='
		function filtro(){
			$("#tr_valor").hide();
			$("#tr_oper").hide();
			campo=$("#campo").val();
			valor.value="";
			if(campo.length>0){
				if(campo=="tipo_op"){
					$("#tr_oper").show();
				}else{
					$("#tr_valor").show();
				}
			}
		}
		$(document).ready(function(){
				$("#tr_cod").hide();
				$(".inputnum").numeric(".");
				$("#campo").change(function () { filtro(); }).change();
			});
			';

		$filter = new DataForm("finanzas/analisisbanc/movimientos/process");
		$filter->title('Filtro de Caja y Bancos');
		$filter->script($script, "create");
		$filter->script($script, "modify");
		$filter->fechad = new dateonlyField("Desde", "fechad",'m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = $fechad;
		$filter->fechah->insertValue = $fechah;
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		$filter->cod = new inputField("","cod");
		$filter->cod->insertValue=$cod;
		$filter->cod->readonly=TRUE;
		$filter->cod->type='hidden';

		$filter->campo = new dropdownField("Filtrar por:", "campo");
		$filter->campo->option("","");
		$filter->campo->option("numero","Numero");
		$filter->campo->option("tipo_op","Tipo Operaci&oacute;n");
		$filter->campo->option("benefi","Beneficiario");
		$filter->campo->style='width:150px;';

		$filter->oper = new dropdownField("Tipo:", "oper");
		$filter->oper->option("","");
		$filter->oper->option('CH',"Cheque");
		$filter->oper->option('DE',"Deposito");
		$filter->oper->option('NC',"Nota de Cr&eacute;dito");
		$filter->oper->option('ND',"Nota de D&eacute;bito");
		$filter->oper->style='width:150px;';

		$filter->valor = new inputField("", "valor");
		$filter->valor->size=20;
		$filter->valor->rule = "trim";

		$filter->orden = new dropdownField("Ordenar por:", "orden");
		$filter->orden->option("fecha","Fecha");
		$filter->orden->option("numero","Numero");
		$filter->orden->option("tipo_op","Tipo Operaci&oacute;n");
		$filter->orden->option("benefi","Beneficiario");
		$filter->orden->style='width:150px;';

		$filter->desc = new radiogroupField("desc", "desc", array("0"=>"Ascendente","1"=>"Descendente"),'0');
		$filter->desc->in='orden';


		$filter->submit("btnsubmit","Buscar");


		$filter->build_form();

		if($cod!='|!')
			$fila=$this->datasis->damerow("SELECT moneda,banco,numcuent FROM banc WHERE codbanc='$cod'");
		else{
			$fila['moneda']='';
			$fila['numcuent']='';
			$fila['banco']='';
		}
		//print_r($fila);
		function blanco($num,$m=''){
			if(empty($num)||$num==0){
			 return '';
			}else{
				return number_format($num,2,',','.').$m;
			}
		}
		$atts = array(
              'width'     =>'800',
              'height'    =>'600',
              'scrollbars'=>'yes',
              'status'    =>'yes',
              'resizable' =>'yes',
              'screenx'   =>'5',
              'screeny'   =>'5');
		$link="finanzas/bmovshow/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>";

		////////////////////////consulta grid//////////////////////////////
		$this->db->select("codbanc,fecha, numero, tipo_op, monto*(tipo_op IN ('DE','NC')) as ingresos,monto*(tipo_op NOT IN ('DE','NC')) as egresos,CONCAT(concepto,' ',concep2,' ',concep3) as concep,
                    benefi,monto*(tipo_op IN ('DE','NC'))-monto*(tipo_op NOT IN ('DE','NC')) as saldo,CASE (tipo_op)
			WHEN 'CH' THEN 'Cheque'
			WHEN 'DE' THEN 'Deposito'
			WHEN 'NC' THEN 'Nota de Cr&eacute;dito'
			WHEN 'ND' THEN 'Nota de D&eacute;bito'
			END
		 AS tipo");
		$this->db->from('bmov as a');
		if($campo&&$valor)$consult="AND $campo LIKE '%$valor%'";
			else
				$consult='';
		if($cod!='|!')
			$b="codbanc='$cod' AND ";
		else
			$b='';

		if(strpos(substr($fechad, 6, 4), '/')==NULL){
			$fechad2 = substr($fechad, 3, 4).substr($fechad, 0, 2);//.substr($fechad, 0,2);
			$fechah2 = substr($fechah, 3, 4).substr($fechah, 0, 2);//.substr($fechah, 0,2));
		}else{
			$fechad2 = substr($fechad, 0,4).substr($fechad, 5,2) ;
			$fechah2 = substr($fechah, 0,4).substr($fechah, 5,2) ;
		}

		$this->db->where("$b EXTRACT(YEAR_MONTH FROM fecha) BETWEEN '$fechad2' AND '$fechah2'$consult");//a.fecha like '%-$mm-%'
		$this->db->orderby($orden.' '.$desc);
		$query = $this->db->get();
		//memowrite($this->db->last_query());
		////////////////////////consulta grid//////////////////////////////
		///////////////////////SALDO ANTERIOR//////////////////////////////
		$ddata=array();
		$banmes='';
		$anterior=0;
		foreach ($query->result_array() as $row){
			if($banmes!=substr($row['fecha'], 5, 2)){
				$ban=$banmes=substr($row['fecha'], 5, 2);
				$ano2=substr($row['fecha'], 0, 4);
				if((1*($banmes))==1){
					$ban=13;
					$ano2=(1*$ano2)-1;
				}

				$campo='saldo'.str_pad(((1*$ban)-1),2,'0',STR_PAD_LEFT);
				$sal=$this->datasis->dameval("SELECT $campo FROM bsal WHERE codbanc='$cod' AND ano='$ano2'");

				if($sal==NULL){
				$sal=0;
				$row['salAnterior']=number_format($sal,2,',','.');
				}else{
					$anterior=$sal;
					$row['salAnterior']=number_format($anterior,2,',','.');
					$anterior=$anterior+($row['saldo']);
				}
			}else{
				$row['salAnterior']=number_format($anterior,2,',','.');
				$anterior=$anterior+($row['saldo']);
				//$campo='';
			}
			//$campo;//
			$ddata[]=$row;
		}
		///////////////////////SALDO ANTERIOR//////////////////////////////
//print_r($ddata);
		if($cod!='|!')
			$o="Movimientos del Banco ".$fila['banco']." cuenta #".$fila['numcuent']." desde $fechad hasta $fechah";
		else
			$o="Todos los Bancos";
		$grid = new DataGrid2($o,$ddata);
		$grid->use_function('blanco');
		if($cod=='|!')
		$grid->column("Banco", "<#codbanc#>" ,'align=left');
		$grid->column("Fecha", "<dbdate_to_human><#fecha#></dbdate_to_human>");
		$grid->column("Numero", anchor_popup($link, '<#numero#>',$atts),'nowrap=yes');
		$grid->column("Tipo Operaci&oacute;n", "<#tipo#>" ,'align=left');
		$grid->column("Ingresos", "<number_format><#ingresos#>|2|,|.</number_format>" ,"align=right");
		$grid->column("Egresos", "<number_format><#egresos#>|2|,|.</number_format>" ,"align=right");
		$grid->column("Saldo", "<number_format><#saldo#>|2|,|.</number_format>" ,"align=right");
		$grid->column("Saldo Anterior", "<#salAnterior#>" ,"align=right");
		$grid->column("Beneficiario", "<#benefi#>" ,'align=left');
		$grid->column("Concepto", "<#concep#>" ,'align=left');
		$grid->totalizar('ingresos','egresos','saldo');

		$grid->build();

		//memowrite( $grid->db->last_query());
		if(!isset($ano2)){
			$ano2=$ano;
			}
		$salida= anchor("finanzas/analisisbanc/meses/$cod/$ano2","Atras");

		$data['content']= //$filter->output.
		$salida.$grid->output;
		$data['title']   = "<h1>Relaci&oacute;n de Caja y Bancos</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
}
*/
?>