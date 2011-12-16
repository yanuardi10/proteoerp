<table align='center'>
	<tr>
		<td align=right></td>
	</tr>
	<tr>
		<td>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td colspan=11 class="littletableheader">Encabezado</td>
			</tr>
			<tr>
				<td width="100" class="littletablerowth">Orden</td>
				<td width="100" class="littletablerowth">Fecha</td>
				<td width="100" class="littletablerow"><?=$fecha?></td>
				<td width="119" class="littletablerowth">Proveedor</td>
				<td colspan="3" class="littletablerow"><?=$proveed ?></td>
				<td class="littletablerowth">Vencimiento</td>
				<td class="littletablerow"><?=$vence ?></td>
			</tr>
			<tr>
				<td class="littletablerow"><?=$orden ?></td>
				<td class="littletablerowth">Numero</td>
				<td class="littletablerow"><?=$numero ?></td>
				<td class="littletablerowth">Nombre</td>
				<td colspan="3" class="littletablerow"><?=$nombre ?></td>
			</tr>
		</table>
		<?php echo $items ?> <?php //echo $detalle ?>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td colspan=10 class="littletableheader">Totales</td>
			</tr>
			<tr>
				<td width="131" class="littletablerowth">Banco</td>
				<td width="122" class="littletablerow"><?=$codb1 ?></td>
				<td width="125" class="littletablerowth">Tipo</td>
				<td width="125" class="littletablerow"><?=$tipo1 ?></td>
				<td width="139" class="littletablerow"><?=$totpre ?></td>
				<td width="139" class="littletablerow"><?=$totiva ?></td>
				<td width="139" class="littletablerow"><?=$totbruto ?></td>
			</tr>
			<tr>
				<td class="littletablerowth">Numero</td>
				<td class="littletablerow"><?=$cheque1 ?></td>
				<td class="littletablerowth">REtencion de IVA</td>
				<td class="littletablerow"><?=$reteiva?></td>
			</tr>
			<tr>
				<td class="littletablerowth">Contado</td>
				<td class="littletablerow"><?=$contado ?></td>
				<td class="littletablerowth">Anticipos</td>
				<td class="littletablerow"><?=$anticipo ?></td>
			</tr>
			<tr>
				<td class="littletablerowth">Credito</td>
				<td class="littletablerow"><?=$credito ?></td>
				<td class="littletablerowth">Tipo</td>
				<td class="littletablerow"><?=$tipo_doc ?></td>
			</tr>
		</table>
	</td>
	</tr>
</table>