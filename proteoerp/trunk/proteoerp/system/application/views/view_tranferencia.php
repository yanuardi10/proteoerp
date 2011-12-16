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
<table align='center'>
	<tr>
		<td align=right><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td>
<table style="margin:0;width:98%;">
	<tr>
		<td colspan=5 class="littletableheader">Encabezado</td>
	</tr>
	<tr>
		<td class="littletablerowth" ><?=$form->numero->label ?></td>
		<td class="littletablerow"   nowrap><?=$form->numero->output ?></td>
		<td class="littletablerowth" align='center' ><?=$form->observ1->label ?></td>
		<td class="littletablerowth" nowrap><?=$form->envia->label  ?></td>
		<td class="littletablerow"   nowrap><?=$form->envia->output ?></td>
	</tr>
	<tr>	
		<td class="littletablerowth"><?=$form->fecha->label ?></td>
		<td class="littletablerow" nowrap><?=$form->fecha->output ?>&nbsp;&nbsp;</td>
		<td class="littletablerow"  ><?=$form->observ1->output ?></td>
		<td class="littletablerowth"><?=$form->recibe->label ?></td>
		<td class="littletablerow"  ><?=$form->recibe->output ?></td>
	</tr>
	<tr>	
		<td class="littletablerowth">&nbsp;</td>
		<td class="littletablerow"  >&nbsp;</td>
		<td class="littletablerow"  ></td>
		<td class="littletablerowth">&nbsp;</td>
		<td class="littletablerow"  >&nbsp;</td>
	</tr>
</table>
<?php echo $form->detalle->output ?> <?php //echo $detalle ?>
<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
		<td>
	<tr>
<table>
<?php endif; ?>