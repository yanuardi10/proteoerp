<?php echo $form_scripts?>
<?php echo $form_begin?>
<?php 
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
?>
<?php if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<table border=0 width="100%">
	<tr>
		<td colspan='2'>
			<fieldset style='border: 1px outset #9AC8DA;background: #FFFDE9;'>
			<table border=0 width="100%">
			<tr>
				<td class="littletableheaderc"><?php echo $form->numero->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->numero->output;?></td>
			</tr>	
			<tr>
				<td class="littletableheaderc"><?php echo $form->fecha->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->fecha->output;?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?php echo $form->ruta->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->ruta->output;?></td>
			</tr>
			</table>
			</fieldset>
		</td>
		<td>
			<fieldset style='border: 1px outset #9AC8DA;background: #FFFDE9;'>
			<legend>&nbsp;</legend>
			<table border=0 width="100%">
			<tr>
				<td class="littletableheaderc"><?php echo $form->chofer->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->chofer->output;?></td>
			</tr>	
			<tr>
				<td class="littletableheaderc"><?php echo $form->nombre->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->nombre->output;?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?php echo $form->lleno->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->lleno->output;?></td>
			</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>
<table  width="100%" border='0'>
	<tr>
		<td  valign="top">	
			<fieldset style='border: 1px outset #9AC8DA;background: #E0ECF8;'>
			<table style="height: 100%;width: 100%">
				<tr>
					<td class="littletableheaderc"><?php echo $form->lleno->label; ?></td>
					<td class="littletablerow"    ><?php echo $form->lleno->output;?></td>
					<td class="littletableheaderc"><?php echo $form->vacio->label; ?></td>
					<td class="littletablerow"    ><?php echo $form->vacio->output;?></td>
					<td class="littletableheaderc"><?php echo $form->neto->label; ?> </td>
					<td class="littletablerow"    ><?php echo $form->neto->output;?> </td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?php echo $form->densidad->label; ?></td>
					<td class="littletablerow"    ><?php echo $form->densidad->output;?></td>
					<td class="littletableheaderc"><?php echo $form->litros->label; ?></td>
					<td class="littletablerow"    ><?php echo $form->litros->output;?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>
<table  width="100%" border='0'>
	<tr>
		<td width='50%'>
			<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
			<table width= '100%' >
				<tr>
					<td class="littletableheaderc"><?php echo $form->litros->label; ?></td>
					<td class="littletablerow"    ><?php echo $form->litros->output;?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?php echo $form->lista->label; ?> </td>
					<td class="littletablerow"    ><?php echo $form->lista->output;?> </td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td>
			<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
			<table width= '100%' >
				<tr>
					<td class="littletableheaderc"><?php echo $form->diferen->label; ?></td>
					<td class="littletablerow"    ><?php echo $form->diferen->output;?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?php echo $form->animal->label; ?></td>
					<td class="littletablerow"    ><?php echo $form->animal->output;?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr><tr>
		<td colspan='2' class="littletableheaderc" align='center'>
		<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
			Directorio de trabajo: 
		<span class="littletablerow"> <?php echo $form->crios->output ?></span>
		</fieldset>
		</td>
	</tr>
</table>
<?php echo $container_bl.$container_br; ?>
<?php echo $form_end?>
<?php 
	//foreach (get_object_vars($form) as $label=>$tiene)
	//	echo "$label => $tiene <br>";
	//echo '<pre>';print_r($form->grupo->request);echo '</pre>'
?>
