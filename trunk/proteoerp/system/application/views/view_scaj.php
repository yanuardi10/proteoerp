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
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
			<table border=0 width="100%">
			<tr>
				<td class="littletableheaderc"><?=$form->cajero->label  ?></td>
				<td class="littletablerow" ><?=$form->cajero->output ?></td>
			</tr>	
			<tr>
				<td class="littletableheaderc"><?=$form->nombre->label    ?></td>
				<td class="littletablerow"><?=$form->nombre->output?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->caja->label  ?></td>
				<td class="littletablerow"><?=$form->caja->output ?></td>
			</tr>
			</table>
			</fieldset>
		</td>
		<td>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
			<legend>&nbsp;</legend>
			<table border=0 width="100%">
			<tr>
				<td class="littletableheaderc"><?=$form->status->label ?></td>
				<td class="littletablerow"><?=$form->status->output?></td>
			</tr>	
			<tr>
				<td class="littletableheaderc"><?=$form->almacen->label ?></td>
				<td class="littletablerow"><?=$form->almacen->output ?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->clave->label  ?></td>
				<td class="littletablerow"><?=$form->clave->output ?></td>
			</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>
<table  width="100%" border='0'>
	<tr>
		<td valign='top' width='50%'>
			<fieldset style='border: 2px outset #9AC8DA;background: #E0ECF8;'>
			<legend class="titulofieldset" style='color: #114411;'>Apertura</legend>
			<table width= "100%" >
				<tr>
					<td class="littletableheaderc"><?=$form->fechaa->label ?></td>
					<td class="littletablerow"><?=$form->fechaa->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->horaa->label ?></td>
					<td class="littletablerow"><?=$form->horaa->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->apertura->label   ?></td>
					<td class="littletablerow" style='font-size:11;'><?=$form->apertura->output  ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td  valign="top">	
			<fieldset style='border: 2px outset #9AC8DA;background: #E0ECF8;'>
			<legend class="titulofieldset" style='color: #114411;'>Cierre</legend>
			<table style="height: 100%;width: 100%">
				<tr>
					<td class="littletableheaderc"><?=$form->fechac->label  ?></td>
					<td class="littletablerow"><?=$form->fechac->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"> <?=$form->horac->label ?> </td>
					<td class="littletablerow"> <?=$form->horac->output ?> </td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->cierre->label  ?></td>
					<td class="littletablerow"><?=$form->cierre->output ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>
<table  width="100%" border='0'>
	<tr>
		<td width='50%'>
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Mesas Restaurant</legend>
			<table width= '100%' >
				<tr>
					<td class="littletableheaderc"><?=$form->mesai->label  ?></td>
					<td class="littletablerow"><?=$form->mesai->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"> <?=$form->mesaf->label ?> </td>
					<td class="littletablerow"> <?=$form->mesaf->output ?> </td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td>
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Hora Feliz Restaurant</legend>
			<table width= '100%' >
				<tr>
					<td class="littletableheaderc"><?=$form->horai->label  ?></td>
					<td class="littletablerow"><?=$form->horai->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->horaf->label  ?></td>
					<td class="littletablerow"><?=$form->horaf->output ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr><tr>
		<td colspan='2' class="littletableheaderc" align='center'>
		<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			Directorio de trabajo: 
		<span class="littletablerow"> <?php echo $form->directo->output ?></span>
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
