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
		
		$data['funciones'] = "";
	 	$data['title']     = "Resumen de Gestion";
	 	$data['encabeza']  = "Resumen de Gestion";
	 	$data["head"]      = ""; //script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
	 	$this->load->view('consulta', $data);

	}
	
	function general(){
		
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

			$centerpanel = 	"
<table width='95%' border='0'>
	<tr>
		<td valign='top'>
			<table id=\"resumen\"></table>
		</td>
	</tr><tr>
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
		/*
		$data['funciones'] = $grid.$grid1;
	 	$data['title']     = "Resumen de Gestion";
	 	$data['encabeza']  = "Resumen de Gestion";
	 	$data["head"]      = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
	 	$this->load->view('consulta', $data);*/
		
		
	}

	function opciones(){
		header("Content-type: text/xml; charset=utf-8");
		echo "<?xml version='1.0'".' encoding="utf-8"?>
<rows>
    <page>1</page>
    <total>1</total>
    <records>1</records>
    <row><cell>1</cell><cell>Vision General</cell><cell></cell><cell>0</cell><cell>1</cell><cell>10</cell><cell>false</cell><cell>false</cell></row>

    <row><cell>2</cell><cell>Analisis Anual</cell><cell>general    </cell><cell>1</cell><cell>2</cell><cell>3</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>3</cell><cell>Analisis de Bancos</cell><cell>jsonex.html   </cell><cell>1</cell><cell>4</cell><cell>5</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>4</cell><cell>Analisis de Gastos </cell><cell>loadoncex.html</cell><cell>1</cell><cell>6</cell><cell>7</cell><cell>true</cell><cell>true</cell></row>
    <row><cell>5</cell><cell>Analisis Diario</cell><cell>localex.html  </cell><cell>1</cell><cell>8</cell><cell>9</cell><cell>true</cell><cell>true</cell></row>

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