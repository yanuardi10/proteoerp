<table align='center'>
	<tr>
		<td>
          <table width="100%"  style="margin:0;width:100%;">
            <tr>
              <td colspan=11 class="littletableheader">Encabezado</td>
            </tr>
            <tr>
              <td width="100" class="littletablerowth">Tipo</td>
              <td width="100" class="littletablerow">  <?=$tipo_doc?></td>
              <td width="100" class="littletablerowth">Cliente</td>
              <td width="100" class="littletablerow">  <?=$cod_cli ?></td>
              <td width="50" class="littletablerowth">Rif</td>
              <td width="100" class="littletablerow">  <?=$rifci ?></td>
            </tr>
            <tr>
              <td class="littletablerowth">Numero</td>
              <td class="littletablerow"><?=$numero ?></td>
              <td class="littletablerowth">Nombre</td>
              <td colspan="3" class="littletablerow"><?=$nombre ?></td>
            </tr>
            <tr>
              <td class="littletablerowth">Fecha</td>
              <td class="littletablerow">  <?=$fecha ?></td>
              <td class="littletablerowth">Dirección</td>
              <td colspan="3" class="littletablerow">  <?=$direc ?></td>
            </tr>
            <tr>
              <td class="littletablerowth">Vendedor</td>
              <td class="littletablerow">  <?=$vd ?></td>
              <td class="littletablerowth"></td>
              <td  colspan="3" class="littletablerow">  <?=$dire1 ?></td>
            </tr>
          </table>
          <?php echo $items ?>
          <?php //echo $detalle ?>
<table  width="100%" style="margin:0;width:100%;" > 
	  <tr>                                                           
	  	<td colspan=10 class="littletableheader">Totales</td>      
	 </tr>                                                          
	 <tr>                                                 
	  <td width="131" class="littletablerowth">Orden</td>
		<td width="122" class="littletablerow" ><?=$orden ?> </td>
    <td width="125" class="littletablerowth">I.V.A </td>
		<td width="125" class="littletablerow"><?=$iva?> </td>
		<td width="111" class="littletablerowth" >Sub-Total</td>
		<td width="139" class="littletablerow" ><?=$totals?> </td>
   </tr>
   <tr>
    <td class="littletablerowth">Forma de Pago</td>
		<td class="littletablerow" > <?=$fpago ?></td>
    <td class="littletablerowth">Inicial</td>
		<td class="littletablerow" > <?=$inicial ?></td>
		<td class="littletablerowth">Total</td>
		<td class="littletablerow" > <?=$totalg ?></td>
   </tr>
</table>
	  <td>
	<tr>
<table>