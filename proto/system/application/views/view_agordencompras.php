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
				<td width="100" class="littletablerowth">Fecha</td>
				<td width="100" class="littletablerow"><?=$fecha?></td>
				<td width="119" class="littletablerowth">Proveedor</td>
				<td colspan="3" class="littletablerow"><?=$proveed ?></td>
			</tr>
			<tr>
				<td class="littletablerowth">Numero</td>
				<td class="littletablerow"><?=$numero ?></td>
				<td class="littletablerowth">Nombre</td>
				<td colspan="3" class="littletablerow"><?=$nombre ?></td>
			</tr>
			<tr>
				<td class="littletablerowth">Status</td>
				<td class="littletablerow"><?=$status ?></td>
				<td class="littletablerowth">Arribo</td>
				<td class="littletablerow"><?=$arribo ?></td>
			</tr>
		</table>
		<?php echo $items ?> <?php //echo $detalle ?>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td colspan=10 class="littletableheader">Totales</td>
			</tr>
			<tr>
				<td width="131" class="littletablerowth">Anticipo</td>
				<td width="122" class="littletablerow"><?=$anticipo ?></td>
				<td width="125" class="littletablerowth">Monto</td>
				<td width="125" class="littletablerow"><?=$mdolar ?></td>
				<td width="111" class="littletablerowth">Subtotal</td>
				<td width="139" class="littletablerow"><?=$montonet ?></td>
			</tr>
			<tr>
				<td class="littletablerowth">Banco/Cj</td>
				<td class="littletablerow"><?=$codban ?></td>
				<td class="littletablerowth">Tipo</td>
				<td class="littletablerow"><?=$tipo_op ?></td>
				<td class="littletablerowth">Impuesto</td>
				<td class="littletablerow"><?=$montoiva ?></td>
			</tr>
			<tr>
				<td class="littletablerowth">Numero</td>
				<td class="littletablerow"><?=$numero ?></td>
				<td class="littletablerowth">Peso</td>
				<td class="littletablerow"><?=$peso ?></td>
				<td class="littletablerowth">Total</td>
				<td class="littletablerow"><?=$montotot ?></td>
			</tr>
		</table>
		</td>
	</tr>
</table>
