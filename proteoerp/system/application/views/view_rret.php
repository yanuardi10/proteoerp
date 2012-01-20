<?php reset($form->_fields); ?>
<?php if(strlen($form->error_string)>0){ ?>
<div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;">
<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span>
	<?php echo $form->error_string; ?>
</p>
</div>
<?php } ?>

<?php echo $form->form_open; ?>
<table align='center'>
	<tr>
		<td valign='top'><p>
			<table class="ui-widget ui-widget-content ui-corner-all">
				<tr>
					<th colspan=3 class="ui-widget-header">Efectivo</th>
				</tr>
				<?php
				$obj=current($form->_fields);
				for($i=1;$i<=$c_efe;$i++){
					echo ($i % 2!=0) ? '<tr>' : '';
					echo ($i % 2!=0) ? '<td>'.$obj->label.'</td>' : '';
					echo '<td>'.$obj->output.'</td>';
					echo ($i % 2==0) ? '</tr>' : '';
					$obj=next($form->_fields);
				}
				$obj=current($form->_fields);
				for($i=0;$i<1;$i++){
					echo '<tr><td>'.$obj->label.'</td><td>&nbsp;</td><td>'.$obj->output.'</td></tr>';
					$obj=next($form->_fields);
				}
				?>
			</table></p>
		</td>
		<td valign='top'>
			<p><table class="ui-widget ui-widget-content ui-corner-all" style="width:350px;">
				<tr>
					<th colspan=3 class="ui-widget-header">Otras formas de pago</th>
				</tr>
				<?php
				/*$obj=current($form->_fields);
				for($i=0;$i< $c_otrp;$i++){
					echo ($i % 2==0) ? '<tr>' : '';
					echo ($i % 2==0) ? '<td>'.$obj->label.'</td>' : '';
					echo '<td>'.$obj->output.'</td>';
					echo ($i % 2!=0) ? '</tr>' : '';
					$obj=next($form->_fields);
				}*/
				?>

				<?php
				$obj=current($form->_fields);
				for($i=0;$i< $c_otrp;$i++){
					echo '<tr>';
					echo '<td>'.$obj->label. '</td>';
					echo '<td>'.$obj->output.'</td>';
					echo '</tr>';
					$obj=next($form->_fields);
				}
				?>
			</table></p>

			<table class="ui-widget ui-widget-content ui-corner-all" style="width:350px;">
				<tr>
					<th colspan=3 class="ui-widget-header">Resumen global</th>
				</tr>
				<?php
				$obj=current($form->_fields);
				for($i=0;$i<3;$i++){
					echo '<tr>';
					echo '<td>'.$obj->label.'</td>';
					echo '<td>'.$obj->output.'</td>';
					echo '</tr>';
					$obj=next($form->_fields);
				}?>
				<tr>
					<td><?php echo $regresa; ?></td>
					<td><?php
					$attr=array(
						'name' =>'submitform',
						'value'=>'Hacer retiro',
						'class'=>'fg-buttons ui-state-default ui-corner-all');
					echo form_submit($attr);
					?></td>
				</tr>
			</table>
		</td>
	</tr>
</tabla>
<?php echo $form->form_close; ?>
