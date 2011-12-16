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
              <td width="100" class="littletablerowth">Fecha</td>
              <td width="100" class="littletablerow"><?=$fecha ?></td>
            </tr>
            <tr>
              <td class="littletablerowth">Fecha de Factura</td>
              <td class="littletablerow"><?=$fechafa ?></td>
              <td class="littletablerowth">Nombre</td>
              <td colspan="3" class="littletablerow"><?=$nombre ?></td>
            </tr>
            <tr>
              <td class="littletablerowth">Factura</td>
              <td class="littletablerow"><?=$factura?></td>
            </tr>
          </table>
          <?php echo $items ?>