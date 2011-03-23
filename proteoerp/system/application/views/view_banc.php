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
		<td align='right' colspan='2'><?php echo $container_tr; ?></td>
	</tr>
	<tr>
		<td>
			<fieldset style='border: 1px solid #9AC8DA;background: #EFEFFF;'>
			<legend class="subtitulotabla" style='color: #114411;'>Banco</legend>
			<table border=0 width="100%">
			<tr>
				<td width="100" class="littletableheader"><?=$form->codbanc->label  ?></td>
				<td class="littletablerow" ><?=$form->codbanc->output ?></td>
			</tr>	
			<tr>
				<td class="littletableheader"><?=$form->tbanco->label    ?></td>
				<td  class="littletablerow"><?=$form->tbanco->output?></td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->sucur->label  ?></td>
				<td class="littletablerow"><?=$form->sucur->output ?></td>
			</tr>
			</table>
			</fieldset>
		</td>
		<td>
			<fieldset style='border: 1px solid #9AC8DA;background: #EFEFFF;'>
			<legend class="subtitulotabla" style='color: #114411;'>&nbsp;</legend>
			<table border=0 width="100%">
			<tr>
				<td width='100' class="littletableheader"><?=$form->activo->label ?></td>
				<td  class="littletablerow"><?=$form->activo->output?></td>
			</tr>	
			<tr>
				<td class="littletableheader"><?=$form->banco->label ?></td>
				<td  class="littletablerow"><?=$form->banco->output ?></td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->numcuent->label  ?></td>
				<td class="littletablerow"><?=$form->numcuent->output ?></td>
			</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>
<table  width="100%" border='0'>
	<tr>
		<td valign='top'>
			<fieldset style='border: 1px solid #9AC8DA;background: #EFEFFF;'>
			<legend class="subtitulotabla" style='color: #114411;'>Direccion</legend>
			<table width= "100%" >
				<tr>
					<td width='60px' class="littletableheader"><?=$form->nombre->label ?></td>
					<td class="littletablerow"><?=$form->nombre->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->telefono->label ?></td>
					<td class="littletablerow"><?=$form->telefono->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->dire1->label   ?></td>
					<td class="littletablerow" style='font-size:11;'><?=$form->dire1->output  ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->dire2->label ?></td>
					<td class="littletablerow" style='font-size:11;'><?=$form->dire2->output ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	
	
		<td  valign="top">	
			<fieldset style='border: 1px solid #9AC8DA;background: #EFEFFF;'>
			<legend class="subtitulotabla" style='color: #114411;'>Cuenta</legend>
			<table style="height: 100%;width: 100%">
				<tr>
					<td  width="95" class="littletableheader"><?=$form->moneda->label  ?></td>
					<td class="littletablerow"><?=$form->moneda->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"> <?=$form->tipocta->label ?> </td>
					<td class="littletablerow"> <?=$form->tipocta->output ?> </td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->proxch->label  ?></td>
					<td class="littletablerow"><?=$form->proxch->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->dbporcen->label  ?></td>
					<td class="littletablerow"><?=$form->dbporcen->output ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>
<table  width="100%" border='0'>
	<tr>
		<td>
			<fieldset style='border: 1px solid #9AC8DA;background: #EFEFFF;'>
			<legend class="subtitulotabla" style='color: #114411;'>Enlaces</legend>
			<table width= '100%' >
				<tr>
					<td width='120px' class="littletableheader"><?=$form->codprv->label  ?></td>
					<td class="littletablerow"><?=$form->codprv->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"> <?=$form->depto->label ?> </td>
					<td class="littletablerow"> <?=$form->depto->output ?> </td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->gastocom->label  ?></td>
					<td class="littletablerow"><?=$form->gastocom->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->gastoidb->label  ?></td>
					<td class="littletablerow"><?=$form->gastoidb->output ?></td>
				</tr>				
				<tr>
				<td class="littletableheader"><?=$form->cuenta->label ?></td>
				<td  class="littletablerow" colspan='2'><?=$form->cuenta->output." "; ?>
				<?php
					if ( $form->_status == 'show' ) {
						$mSQL = "SELECT descrip FROM cpla WHERE codigo='".trim($form->cuenta->output)."'";
						echo $this->datasis->dameval($mSQL);
					}
				?>
				</td>
			</tr>
			</table>
		</td>
		<?php if( $form->_status == 'show') { ?>
		<td valign='top'>
			<fieldset style='border: 1px solid #9AC8DA;background: #EFEFFF;'>
			<legend class="subtitulotabla" style='color: #114411;'>Saldo</legend>
			<table width= '100%' >
				<tr>
					<td align='center' style='font-size:14;font-weight: bold'>SALDO ACTUAL</td>
				</tr><tr>
					<td>&nbsp;</td>
				</tr><tr>
					<td align='center' style='font-size:16;font-weight: bold'><?=nformat($form->saldo->output); ?></td>
				</tr><tr>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
		<?php } ?>
	</tr>
</table>
<?php echo $container_bl.$container_br; ?>
<?php echo $form_end?>
<?php 
	//foreach (get_object_vars($form) as $label=>$tiene)
	//	echo "$label => $tiene <br>";
	//echo '<pre>';print_r($form->grupo->request);echo '</pre>'
?>