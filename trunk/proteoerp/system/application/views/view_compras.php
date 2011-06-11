<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:
 
if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_scripts;
echo $form_begin?>
<table width='100%' align='center'>
	<tr>
		<td align=right>
			<?php echo $container_tr?>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%"  style="margin:0;width:100%;" cellspacing='2' cellpadding='2'>
				<tr>
					<td colspan=11 class="littletableheader">Encabezado</td>
				</tr>
				<tr>
					<td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
						<table>
							<tr>
								<td class="littletablerowth"><?=$form->fecha->label ?></td>
								<td class="littletablerow"><?=$form->fecha->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->numero->label ?></td>
								<td class="littletablerow"><?=$form->numero->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->tipo->label ?></td>
								<td class="littletablerow"><?=$form->tipo->output ?></td>
							</tr>
						</table>
					</fieldset>
					</td><td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
						<table>
							<tr>
								<td class="littletablerowth"><?=$form->orden->label ?></td>
								<td class="littletablerow"><?=$form->orden->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->cfis->label ?></td>
								<td class="littletablerow"><?=$form->cfis->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->almacen->label ?></td>
								<td class="littletablerow"><?=$form->almacen->output ?></td>
							</tr>
						</table>
					</fieldset>
					</td><td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
						<table>
							<tr>
								<td class="littletablerowth"><?=$form->proveedor->label ?></td>
								<td colspan="3" class="littletablerow"><?=$form->proveedor->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->nombre->label ?></td>
								<td colspan="3" class="littletablerow"><?=$form->nombre->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->vence->label ?></td>
								<td width="99" class="littletablerow"><?=$form->vence->output ?></td>
								<td width="44" class="littletablerow"><span class="littletablerowth">
								<? echo $form->peso->label ?></span></td>
								<td width="99" class="littletablerow" align='right'><?=$form->peso->output ?></td>
							</tr>
						</table>
					</fieldset>
					</td>
				</tr>
			</table>
		</tr>
	<tr>
</table>

          <?php echo $form->detalle->output ?>
          <?php //echo $detalle ?>
<table  width="100%" style="margin:0;width:100%;" > 
	  <tr>                                                           
	  	<td colspan=10 class="littletableheader">Totales</td>      
	 </tr>                                                          
	 <tr>
	  <td width="131" class="littletablerowth"><?=$form->rislr->label ?> </td>
		<td width="122" class="littletablerow" align='right'><?=$form->rislr->output ?> </td>
		<td width="125" class="littletablerowth"><?=$form->anticipo->label ?> </td>
		<td width="125" class="littletablerow" align='right'><?=$form->anticipo->output ?> </td>
		<td width="111" class="littletablerowth" ><?=$form->subt->label ?> </td>
		<td width="139" class="littletablerow" align='right'><?=$form->subt->output ?> </td>
	</tr><tr>
		<td class="littletablerowth"><?=$form->riva->label ?></td>
		<td class="littletablerow" align='right'><?=$form->riva->output ?></td>
		<td class="littletablerowth"><?=$form->contado->label ?></td>
		<td class="littletablerow" align='right'><?=$form->contado->output ?></td>
		<td class="littletablerowth"><?=$form->iva->label ?></td>
		<td class="littletablerow" align='right'><?=$form->iva->output ?></td>
      </tr>
      <tr>
    <td class="littletablerowth" ><?=$form->monto->label ?></td>
		<td class="littletablerow" align='right'><?=$form->monto->output ?></td>
    <td class="littletablerowth"><?=$form->credito->label ?></td>
		<td class="littletablerow" align='right'><?=$form->credito->output ?></td>
		<td class="littletablerowth"><?=$form->total->label ?></td>
		<td class="littletablerow" align='right'><?=$form->total->output ?></td>
      </tr>
</table>

<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
	  <td>
	<tr>
<table>
<?php endif; ?>
