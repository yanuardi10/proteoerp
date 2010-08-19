<table align='center'>
	<tr>
		<td align=right></td>
	</tr>
	<tr>
		<td>
          <table width="100%"  style="margin:0;width:100%;">
            <tr>
              <td colspan=11 class="littletableheader">Encabezado</td>
            </tr>
            <tr>
              <td width="100" class="littletablerowth">Numero</td>
              <td width="100" class="littletablerow">  <?=$numero?></td>
              <td width="100" class="littletablerowth">Cliente</td>
              <td width="100" class="littletablerow"><?=$cod_cli ?></td>
              <td width="100" class="littletablerowth">Almacen</td>
              <td width="100" class="littletablerow"><?=$almacen ?></td>
            </tr>
            <tr>
              <td class="littletablerowth">Fecha</td>
              <td class="littletablerow"><?=$fecha ?></td>
              <td class="littletablerowth">Nombre</td>
              <td colspan="3" class="littletablerow"><?=$nombre ?></td>
            </tr>
            <tr>
              <td class="littletablerowth">Vendedor</td>
              <td class="littletablerow"><?=$vende?></td>
              <td class="littletablerowth">Dirección</td>
              <td colspan="3" class="littletablerow"><?=$dir_cli ?></td>
            </tr>
            <tr>
              <td class="littletablerowth">Factura</td>
              <td class="littletablerow"><?=$factura?></td>
              <td class="littletablerowth"></td>
              <td colspan="3" class="littletablerow"><?=$dir_cl1?></td>
            </tr>
          </table>
          <?php echo $items ?>
          <?php //echo $detalle ?>
<table  width="100%" style="margin:0;width:100%;" > 
	 <tr>                                                           
	  <td colspan=10 class="littletableheader">Totales</td>      
	 </tr>
	 <tr>
     <td class="littletablerowth">Orden</td>
     <td class="littletablerow"><?=$orden ?></td>
     <td class="littletablerowth">I.V.A</td>
     <td class="littletablerowth">Sub-Total</td>                       
     <td colspan="3" class="littletablerow"><?=$stotal ?></td>
   </tr>
   <tr>
     <td class="littletablerowth">Observacion</td>
     <td class="littletablerow"><?=$observa ?></td>
     <td class="littletablerow"><?=$impuesto ?></td>
     <td class="littletablerowth">Total</td>                       
     <td colspan="3" class="littletablerow"><?=$gtotal ?></td>
   </tr>                                                         
</table>
	  <td>
	<tr>
<table>