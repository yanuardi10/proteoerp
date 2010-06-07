<html>
<head>
<title>Consulta de Caja</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="estilos.css">
<style>P {page-break-before: always}</style>
</head>
<body>
<CENTER>
<TABLE width="90%"><tr>
<TD align=left><img src="images/logo.jpg" height="40" ></TD>
<TD align=center>
<TABLE cellspacing=0 border=0 cellpadding=0>
<?php
echo "<TR><TD><B>".traevalor("TITULO1")."</B></TD></TR>" ;
echo "<TR><TD align=center><FONT SIZE=-1>".traevalor("SUCURSAL")."</FONT></TD></TR>" ;
echo "<TR><TD align=center><FONT SIZE=-1>".traevalor("TITULO7")."</FONT></TD></TR>" ;
?>
</TR></TABLE>
</TD>
<TD align=right><img src="images/hiperdata.jpg" height="40" ></TD>
</TR>
</TABLE>
<b class="style3">ARQUEO DE CAJAS AL <?php echo $fecha ?> </b>
<br>
<TABLE width=90% border=1><tr><td><B>CIERRE DE CAJEROS</B></TD></TR></TABLE>
<?php
$query = "SELECT a.caja, b.nombre, a.trecibe, a.computa,
                 if(a.trecibe-a.computa<0,a.trecibe-a.computa,0),
                 if(a.trecibe-a.computa>0,a.trecibe-a.computa,0)
          FROM dine AS a LEFT JOIN scaj AS b ON a.cajero=b.cajero
          WHERE a.fecha=$qfecha AND MID(a.caja,1,1)='0'  ORDER BY a.caja ";

$query_result_handle = damecur($query);
$num_of_rows = mysql_num_rows ($query_result_handle);
?>
<table valign="center" class="tabla3" width="90%" cellspacing=0>
 <tr class=tablatd>
  <td align=center><b>Caja     </b></td>
  <td align=center><b>Cajero   </b></td>
  <td align=center><b>Arqueo   </b></td>
  <td align=center><b>Sistema  </b></td>
  <td align=center><b>Faltantes</b></td>
  <td align=center><b>Sobrantes</b></td>
</tr>
<?php
for ($count = 1; $row = mysql_fetch_row ($query_result_handle); ++$count)
{
   print "<td align=center> $row[0] </td>";
   print "<td align=left> $row[1] </td>";
   print "<td align=right>".number_format($row[2],2)." </td>";
   print "<td align=right> ".number_format($row[3],2)." </td>";
   print "<td align=right> ".number_format($row[4],2)." </td>";
   print "<td align=right> ".number_format($row[5],2)." </td>";
   print "</tr>\n";
};

$query = "SELECT caja, cajero, sum(trecibe), sum(computa), sum(if(trecibe-computa<0,trecibe-computa,0)) , sum(if(trecibe-computa>0,trecibe-computa,0))
          FROM dine WHERE fecha=$qfecha  AND MID(caja,1,1)='0' GROUP BY fecha ";

$query_result_handle = damecur($query);
$row = mysql_fetch_row ($query_result_handle);

print "<tr class=tablatd >";
print "<td align=right colspan=2 ><b><font point-size=12 color=white> Totales....</font></td>";
print "<td align=right><b>".number_format($row[2],2)."</b></td>";
print "<td align=right><b>".number_format($row[3],2)."</b></td>";
print "<td align=right><b>".number_format($row[4],2)."</b></td>";
print "<td align=right><b>".number_format($row[5],2)."</b></td>";
print "</tr>\n";
print "</table>";

// FORMA DE PAGO
$query = "SELECT a.tipo, b.nombre, sum(a.total),count(*)
          FROM itdine AS a LEFT JOIN tarjeta AS b ON a.tipo=b.tipo, dine AS c
          WHERE c.fecha=$qfecha AND a.numero=c.numero  AND MID(caja,1,1)='0'
          GROUP BY a.tipo ";

$query_result_handle = damecur($query);
$venta = dameval("SELECT sum(a.total) FROM itdine AS a, dine AS b WHERE b.fecha=$qfecha AND a.numero=b.numero AND MID(caja,1,1)='0'");
?>


<table width=90% border=1><tr><td><B>RECAUDACION POR TIPO DE PAGO</B></TD></TR></TABLE>
<table valign="center" class="tabla4" width="90%" cellspacing=0>
 <tr class=tablatd >
  <td colspan=2 align=center>FORMA DE PAGO</td>
  <td align=center>MONTO</td>
  <td align=center>#TRANS</td>
  <td align=center>%</td>
 </tr>
<?php
//$venta=0;
for ($count = 1; $row = mysql_fetch_row ($query_result_handle); ++$count)
{
print "<tr>";
print "<td align=center> $row[0] </td>";
print "<td align=left> $row[1] </td>";
print "<td align=right>".number_format($row[2],2)." </td>";
print "<td align=right> ".number_format($row[3],0)." </td>";
print "<td align=right> ".number_format($row[2]*100/$venta,2)." </td>";
print "</tr>";
//$venta += $row[2];
};
print "<tr class=tablatd>";
print "<td align=right colspan=2> Total....</td>";
print "<td align=right>".number_format($venta,2)." </td>";
print "<td align=right> </td>";
print "<td align=right> </td>";
print "</tr>\n";
print "</table>\n";

$qqfecha="ADDDATE($qfecha, INTERVAL 1 DAY)";
//print $qqfecha;
?>
<table width=90% border=1><tr><td><b>CUADRE DE CAJA POR DIA</B></TD></TR></TABLE>

<table border="0" valign="center" width="90%" class="tabla4" cellspacing=0>
<tr class=tablatd><td>NOMBRE</TD><TD align=center>MONTO</TD></TR>

<?php
$cajapos=dameval("SELECT valor FROM valores WHERE nombre='CAJAPOS'");
$defe=dameval("SELECT sum(efectivo) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
print "<TR><TD>DEPOSITOS EN EFECTIVO EN TRANSITO</TD><TD align=right>".number_format($defe,2)."</TD></TR>\n";
$dech=dameval("SELECT sum(cheques) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
print "<TR><TD>DEPOSITOS EN CHEQUE EN TRANSITO</TD><TD align=right>".number_format($dech,2)."</TD></TR>\n";

$detc=dameval("SELECT sum(tarjeta) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
print "<TR><TD>DEPOSITOS EN TARJETA DE CREDITO </TD><TD align=right>".number_format($detc,2)."</TD></TR>\n";

$detd=dameval("SELECT sum(tdebito) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' ");
print "<TR><TD>DEPOSITOS EN TARJETA DE DEBITO </TD><TD align=right>".number_format($detd,2)."</TD></TR>\n";

$pago=dameval("SELECT sum(monto) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' AND tipo='TR' ");
print "<TR><TD>TRASFERIDO A ADMINISTRACION</TD><TD align=right>".number_format($pago,2)."</TD></TR>\n";

$cesta=dameval("SELECT sum(monto) FROM bcaj WHERE estampa=$qqfecha AND envia='$cajapos' AND tipo='RM' ");
print "<TR><TD>CESTA TICKET Y VALORES AL COBRO</TD><TD align=right>".number_format($cesta,2)."</TD></TR>\n";

print "<TR class=tablatd><TD>RESUMEN DISTRIBUCION POS</TD><TD align=right >".number_format($defe+$dech+$detc+$detd+$pago+$cesta,2)."</TD></TR>\n";
print "</TABLE><br>\n";

$pago=dameval("SELECT saldo FROM banc WHERE codbanc='$cajapos' ");
print "<table valign=\"center\"  cellspacing=5 width=90% class=saldo>\n";
print "<TR><TD align=right><B>SALDO CAJA POS: </B></TD><TD align=left><b>".number_format($pago,2)."</b></TD></TR>\n";
print "</TABLE>";
?>
<br>
<table width=90% border=1><tr><td><B>VALORES EN CUSTODIA</B></TD></TR></TABLE>
<table border="0" valign="center" width="90%" class="tabla4" cellspacing=0>
<TR class=tablatd><TD>Descripcion</TD>
   <TD ALIGN=CENTER>Anterior</TD>
   <TD ALIGN=CENTER>Ingresos</TD>
   <TD ALIGN=CENTER>Egresos</TD>
   <TD ALIGN=CENTER>Saldo</TD>
</TR>
<?PHP
$msql = "SELECT tarjeta, concepto, enlace, saldo, descrip FROM tardet WHERE enlace IS NOT NULL AND enlace!='' GROUP BY enlace";
$menlaces = damecur($msql);
$manterior = 0;
$mingresos = 0;
$megresos  = 0;
$msaldos   = 0;
for ($count = 1; $row = mysql_fetch_row ($menlaces); ++$count)
{
   $msql = "SELECT sum(a.total) FROM itdine AS a, dine AS b
            WHERE a.tipo='$row[0]' AND a.concepto='$row[1]' AND a.numero=b.numero
                  AND b.fecha=$qfecha GROUP BY fecha ";
   $ingreso = dameval($msql);

   $msql = "SELECT sum(a.monto) FROM itbcaj AS a, bcaj AS b
            WHERE a.tipo='$row[0]' AND a.concep='$row[1]' AND a.numero=b.numero
                  AND b.estampa=$qqfecha GROUP BY fecha ";

   $egreso = dameval($msql);
   print "<tr><td>$row[4]</td>";
   print "<td align=right>".number_format($row[3]-$ingreso+$egreso,2)."</td>";
   $manterior += $row[3]-$ingreso+$egreso ;
   print "<td align=right>".number_format($ingreso,2)."</td>";
   $mingresos += $ingreso ;
   print "<td align=right>".number_format($egreso,2)."</td>";
   $megresos += $egreso ;
   print "<td align=right>".number_format($row[3],2)."</td></tr>";
   $msaldos += $row[3];
};

print "<tr class=tablatd ><td>Totales...</td>";
print "<td align=right>".number_format($manterior,2)."</td>";
print "<td align=right>".number_format($mingresos,2)."</td>";
print "<td align=right>".number_format($megresos,2)."</td>";
print "<td align=right>".number_format($msaldos,2)."</td></tr>";
?>
</table>
<br>
<table width='90%' cellspacing=10>
<tr><td align=center><H3>Preparado por:</H3></td>
<td align=center><H3>Revisado por</H3></td></tr>
</table>

</CENTER>
</body>
</html>
