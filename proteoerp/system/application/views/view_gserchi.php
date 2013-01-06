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
				<td class="littletableheaderc"><?php echo $form->numfac->label;    ?></td>
				<td class="littletablerow"    ><?php echo $form->numfac->output;   ?></td>

				<td class="littletableheaderc"><?php echo $form->codbanc->label;   ?></td>
				<td class="littletablerow"    ><?php echo $form->codbanc->output;  ?></td>
			</tr><tr>
				<td class="littletableheaderc"><?php echo $form->fechafac->label;  ?></td>
				<td class="littletablerow"    ><?php echo $form->fechafac->output; ?></td>
				
				<td class="littletableheaderc"><?php echo $form->sucursal->label;   ?></td>
				<td class="littletablerow"    ><?php echo $form->sucursal->output;  ?></td>

			</tr><tr>
				<td class="littletableheaderc"><?php echo $form->nfiscal->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->nfiscal->output;?></td>
				<td class="littletableheaderc"><?php echo $form->departa->label;   ?></td>
				<td class="littletablerow"    ><?php echo $form->departa->output;  ?></td>

			</tr>

			</table>
			</fieldset>
		</td>
	</tr><tr>
		<td>
			<fieldset style='border: 1px outset #9AC8DA;background: #FFFDE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Proveedor</legend>
			<table border=0 width="100%">
			<tr>
				<td class="littletableheaderc"><?php echo $form->rif->label;  ?></td>
				<td class="littletablerow"    ><?php echo $form->rif->output; ?></td>
				<td class="littletablerow"    ><?php echo $form->proveedor->output; ?></td>
			</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>

<table  width="100%" border='0'>
	<tr>
		<td valign='top'>
			<fieldset style='border: 1px outset #9AC8DA;background: #E0ECF8;'>
			<legend class="titulofieldset" style='color: #114411;'>Gasto</legend>
			<table width= "100%" >
				<tr>
					<td class="littletableheaderc"><?php echo $form->codigo->label; ?></td>
					<td class="littletablerow">    <?php echo $form->codigo->output; ?>&nbsp;</td>
					<td class="littletablerow">    <?php echo $form->descrip->output; ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>

<table  width="100%" border='0'>
	<tr>
		<td valign='top'>
			<fieldset style='border: 2px outset #9AC8DA;background: #E0ECF8;'>
			<legend class="titulofieldset" style='color: #114411;'>Valores</legend>
			<table width= "100%" >
				<tr>
					<td class="littletableheaderc"><?php echo $form->exento->label; ?></td>
					<td class="littletablerow">    <?php echo $form->exento->output; ?>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					
				</tr>
				<tr>
					<td class="littletableheaderc"><?php echo $form->montasa->label;  ?></td>
					<td class="littletablerow">    <?php echo $form->montasa->output; ?>&nbsp;</td>
					<td class="littletableheaderc"><?php echo $form->tasa->label;  ?></td>
					<td class="littletablerow">    <?php echo $form->tasa->output; ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?php echo $form->monredu->label;  ?></td>
					<td class="littletablerow">    <?php echo $form->monredu->output; ?>&nbsp;</td>
					<td class="littletableheaderc"><?php echo $form->reducida->label;   ?></td>
					<td class="littletablerow">    <?php echo $form->reducida->output;  ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?php echo $form->monadic->label;   ?></td>
					<td class="littletablerow">    <?php echo $form->monadic->output;  ?>&nbsp;</td>
					<td class="littletableheaderc"><?php echo $form->sobretasa->label;   ?></td>
					<td class="littletablerow">    <?php echo $form->sobretasa->output;  ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td valign='top'>
			<table width= "100%" >
			<tr>
				<td width='50%' align='right' class="littletableheaderc"><?php echo $form->importe->label; ?>: </td>
				<td class="littletablerow"><?php echo $form->importe->output; ?>&nbsp;</td>
			</tr>
			</table>
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
