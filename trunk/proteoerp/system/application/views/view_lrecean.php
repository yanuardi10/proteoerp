<?php echo $form_scripts?>
<?php echo $form_begin?>
<?php
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$mod=true;
?>
<?php 
	if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; 
?>
<table width='100%' style='font-size:11pt;background:#F2E69D;'>
	<tr>
		<td                           width='60'>Numero:</td>
		<td style='font-weight:bold;' width='70'><?php echo str_pad(trim($form->numero->output),7,'0',STR_PAD_LEFT);    ?></td>
		<td                           width='60' align='right'>Fecha:</td>
		<td style='font-weight:bold;' width='90'><?php echo $form->fecha->output; ?></td>
	</tr>
	<tr>
		<td>Ruta:</td>
		<td colspan='3' style='font-weight:bold;' width='50' align='left'><?php echo $form->ruta->output;  ?> 
		<span style='font-weight:bold;'><?php echo $this->datasis->dameval("SELECT nombre FROM lruta WHERE codigo='".$form->ruta->value."'");  ?></span></td>
	</tr>
</table>
<br />
<table width='100%' align='center'>
	<tr>
		<td class="littletableheaderc"><?php echo $form->lleno->label;  ?></td>
		<td class="littletablerow"    ><?php echo $form->lleno->output; ?></td>
		<td class="littletableheaderc"><?php echo $form->vacio->label;  ?></td>
		<td class="littletablerow"    ><?php echo $form->vacio->output; ?></td>
	</tr><tr>
		<td class="littletableheaderc"><?php echo $form->densidad->label; ?></td>
		<td class="littletablerow"    ><?php echo $form->densidad->output;?></td>
		<td class="littletableheaderc"><?php echo $form->neto->label;     ?></td>
		<td class="littletablerow"    ><?php echo $form->neto->output;    ?></td>
	</tr><tr>
		<td class="littletableheaderc"><?php echo $form->temp->label;    ?></td>
		<td class="littletablerow"    ><?php echo $form->temp->output;   ?></td>
		<td class="littletableheaderc"><?php echo $form->crios->label;   ?></td>
		<td class="littletablerow"    ><?php echo $form->crios->output;  ?></td>
	</tr>
</table>

<div style='border: 1px solid #9AC8DA;background: #FAFAFA'>
<table width='100%' align='center'>
	<tr>
		<td class="littletableheaderc"><?php echo $form->animal->label;  ?></td>
		<td class="littletablerow"    ><?php echo $form->animal->output; ?></td>
		<td class="littletableheaderc"><?php echo $form->acidez->label;  ?></td>
		<td class="littletablerow"    ><?php echo $form->acidez->output; ?></td>
	</tr><tr>
		<td class="littletableheaderc"><?php echo $form->h2o->label;     ?></td>
		<td class="littletablerow"    ><?php echo $form->h2o->output;    ?></td>
		<td class="littletableheaderc"><?php echo $form->crios->label;   ?></td>
		<td class="littletablerow"    ><?php echo $form->crios->output;  ?></td>
	</tr><tr>
		<td class="littletableheaderc"><?php echo $form->brix->label;    ?></td>
		<td class="littletablerow"    ><?php echo $form->brix->output;   ?></td>
		<td class="littletableheaderc"><?php echo $form->grasa->label;   ?></td>
		<td class="littletablerow"    ><?php echo $form->grasa->output;  ?></td>
	</tr><tr>
		<td class="littletableheaderc"><?php echo $form->cloruros->label;?></td>
		<td class="littletablerow"    ><?php echo $form->cloruros->output;?></td>
		<td class="littletableheaderc"><?php echo $form->dtoagua->label; ?></td>
		<td class="littletablerow"    ><?php echo $form->dtoagua->output;?></td>
	</tr>
</table>
</div>

<?php echo $container_bl.$container_br; ?>
<?php echo $form_end; ?>
