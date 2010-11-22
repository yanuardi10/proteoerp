<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' OR $form->_action=='delete'):
	echo $form->output;
else:
 
if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_scripts;
echo $form_begin?>
<table align='center' style="margin:0;width:98%;">
	<tr>
		<td align=right>
			<?php echo $container_tr?>
		</td>
	</tr>
	<tr>
		<td>
<table style="margin:0;width:98%;">
	<tr>
		<td colspan=5 class="littletableheader">Encabezado</td>
	</tr>
	<tr>
		<td class="littletablerowth"><?=$form->codigo->label ?></td>
		<td class="littletablerow"><?=$form->codigo->output ?></td>
		<td class="littletablerowth" align='rigth'><center><?=$form->tipo->label ?></center></td>
		<td class="littletablerow" ><?=$form->tipo->output ?></td>
	</tr>
	<tr>	
		<td class="littletablerowth"><?=$form->nombre->label ?></td>
		<td class="littletablerow" colspan='3'><?=$form->nombre->output ?></td>
	</tr>
</table>
<?php echo $form->detalle->output ?>
<table style="margin:0;width:98%;">
	<tr>
		<td class="littletableheader"><?=$form->observa1->label ?></td>
	</tr>
	<tr>
		<td class="littletablerow"  ><?=$form->observa1->output ?></td>
	</tr>
	<tr>	
		<td class="littletablerow"  ><?=$form->observa2->output ?></td>
	</tr>
</table>


<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
		<td>
	<tr>
<table>
<?php endif; ?>