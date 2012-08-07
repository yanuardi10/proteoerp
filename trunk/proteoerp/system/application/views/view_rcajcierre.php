<?php reset($form->_fields); ?>
<?php if(strlen($form->error_string)>0){ ?>
<div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;">
<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span>
	<?php echo $form->error_string; ?>
</p>
</div>
<?php } ?>

<?php echo $form->form_open; ?>
<p style='text-align:center'>
	Verifique los montos de cierre, en caso de errores puedes ajustarlos, tome en cuenta de que debe estar
	seguro de los mismos antes de hacer el cierre definitivo ya que no podra cambiarlos despu&eacute;s de que este se realice.
</p>
<table align='center'>
	<tr>
		<td valign='top'>
			<table class="ui-widget ui-widget-content ui-corner-all">
				<tr>
					<th colspan=5 class="ui-widget-header">Cierre definitivo de Caja</th>
				</tr>
				<tr>
					<td class="ui-widget-header">Forma de pago</td>
					<td class="ui-widget-header">Retiros</td>
					<td class="ui-widget-header">Monto recibido por el cajero</td>
					<td class="ui-widget-header">Monto seg&uacute;n Sistema</td>
					<td class="ui-widget-header">Diferencia</td>

					<?php
					$i=0; $to=false;
					foreach($form->_fields as $id=>$obj){
						if(substr($id,0,2)=='x_') continue;
						if($i%4==0){
							$color = ($to)? 'bgcolor="#E4E4E4"' : '';
							echo "</tr><tr $color >";
							echo '<td>'.$obj->label.'</td>';
							$to=!$to;
						}

						echo '<td align="right" >';
						echo  $obj->output.' ';
						echo '</td>';

						$i++;
					}
					?>
				</tr>
				<tr>
					<td>Ventas a cr&eacute;dito:</td>
					<td align='right'><?php echo nformat($credito); ?></td>
					<td colspan=3 align='right' rowspan=2><?php echo implode('',$form->_button_container['BL']); ?></td>
				</tr>
				<tr>
					<td>Cambio de Cheques:</td>
					<td align='right'><?php echo nformat($cc); ?></td>
				</tr>
				<tr>
					<td>Apartado de Retenci&oacute;n:</td>
					<td align='right'><?php echo nformat($rp); ?></td>

				</tr>
			</table>
		</td>
	</tr>
</tabla>
<?php if($b_fiscal=='S'){ ?>
<table align='center'>
	<tr>
		<td valign='top'>
			<table class="ui-widget ui-widget-content ui-corner-all">
				<tr>
					<th colspan=2 class="ui-widget-header">Datos F&iacute;scales</th>
				</tr>
				<tr>
					<td>Total Venta seg&uacute;n cierre fiscal:</td>
					<td align='right'><?php echo $form->x_venta->output; ?></td>
				</tr>
				<tr>
					<td>Total IVA seg&uacute;n cierre fiscal:</td>
					<td align='right'><?php echo $form->x_viva->output; ?></td>
				</tr>
				<tr>
					<td>N&uacute;mero ultima Factura</td>
					<td align='right'><?php echo $form->x_ultimafc->output; ?></td>
				</tr>
				<tr>
					<td>Total de notas de cr&eacute;dito seg&uacute;n cierre fiscal</td>
					<td align='right'><?php echo $form->x_devo->output; ?></td>
				</tr>
				<tr>
					<td>Total de IVA de notas de cr&eacute;dito seg&uacute;n cierre fiscal</td>
					<td align='right'><?php echo $form->x_diva->output; ?></td>
				</tr>
				<tr>
					<td>N&uacute;mero ultima NC</td>
					<td align='right'><?php echo $form->x_ultimanc->output; ?></td>
				</tr>
				<tr>
					<td>Serial Maquina Fiscal</td>
					<td align='right'><?php echo $form->x_maqfiscal->output; ?></td>
				</tr>
			</table>
		</td>
	</tr>
</tabla>
<?php } ?>
<?php echo $form->form_close; ?>
