<?php reset($form->_fields); ?>
<?php if(strlen($form->error_string)>0){ ?>
<div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;"> 
<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span>
	<?php echo $form->error_string; ?>
</p>
</div>
<?php } ?>
<?php

//$obj=next($form->_fields);


?>
<?php echo $form->form_open; ?>
<p style='text-align:center'>
	Verifique los montos de cierre, en caso de errores puedes ajustarlos, tome en cuenta de que debe estar
	seguro de los mismos antes de hacer el cierre definitivo ya que no podra cambiarlos despues de que este se realice.
</p>
<table align='center'>
	<tr>
		<td valign='top'>
			<table class="ui-widget ui-widget-content ui-corner-all">
				<tr>
					<th colspan=4 class="ui-widget-header">Cierre definitivo de Caja</th>
				</tr>
				<tr>
					<td class="ui-widget-header">Forma de pago</td>
					<td class="ui-widget-header">Monto recibido por el cajero</td>
					<td class="ui-widget-header">Monto seg&uacute;n Sistema</td>
					<td class="ui-widget-header">Diferencia</td>

					<?php
					$i=0;
					foreach($form->_fields as $id=>$obj){
						if($i%3==0){
							echo '</tr><tr>';
							echo '<td>'.$obj->label.'</td>';
						}

						echo '<td align="right"';
						if($i%2==0) echo 'bgcolor="#E4E4E4"';
						echo '>';
						echo  $obj->output.' ';
						echo '</td>';

						$i++;
					}
					?>
				</tr>
				<tr>
					<td> Monto a cr&eacute;dito:</td>
					<td><?php echo nformat($credito); ?></td>
					<td colspan=2 align='right'><?php echo implode('',$form->_button_container['BL']); ?></td>
				</tr>
			</table>
		</td>
	</tr>
</tabla>
<?php echo $form->form_close; ?>
