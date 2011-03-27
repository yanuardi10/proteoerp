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
		<td>
			<a href='<?php echo base_url()."finanzas/banc/consulta/".$form->codbanc->output; ?>'>
			<?php
				$propiedad = array('src' => 'images/ojos.png', 'alt' => 'Consultar Movimiento', 'title' => 'Consultar Detalles','border'=>'0','height'=>'25');
				echo img($propiedad);
			?>
			</a>
		</td>
		<td align='center' valign='middle'>
			<?php  if ($form->activo->value=='N') echo "<div style='font-size:14px;font-weight:bold;color: #B40404'>***DESACTIVADO***</div>"; ?>&nbsp;
		</td>
		<td align='right'><?php echo $container_tr; ?></td>
	</tr>
	<tr>
		<td colspan='2'>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Banco</legend>
			<table border=0 width="100%">
			<tr>
				<td width="100" class="littletableheaderc"><?=$form->codbanc->label  ?></td>
				<td class="littletablerow" ><?=$form->codbanc->output ?></td>
			</tr>	
			<tr>
				<td class="littletableheaderc"><?=$form->tbanco->label    ?></td>
				<td  class="littletablerow"><?=$form->tbanco->output?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->sucur->label  ?></td>
				<td class="littletablerow"><?=$form->sucur->output ?></td>
			</tr>
			</table>
			</fieldset>
		</td>
		<td>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
			<legend>&nbsp;</legend>
			<table border=0 width="100%">
			<tr>
				<td width='100' class="littletableheaderc"><?=$form->activo->label ?></td>
				<td  class="littletablerow"><?=$form->activo->output?></td>
			</tr>	
			<tr>
				<td class="littletableheaderc"><?=$form->banco->label ?></td>
				<td  class="littletablerow"><?=$form->banco->output ?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->numcuent->label  ?></td>
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
			<fieldset style='border: 2px outset #9AC8DA;background: #E0ECF8;'>
			<legend class="titulofieldset" style='color: #114411;'>Direccion</legend>
			<table width= "100%" >
				<tr>
					<td width='60px' class="littletableheaderc"><?=$form->nombre->label ?></td>
					<td class="littletablerow"><?=$form->nombre->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->telefono->label ?></td>
					<td class="littletablerow"><?=$form->telefono->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->dire1->label   ?></td>
					<td class="littletablerow" style='font-size:11;'><?=$form->dire1->output  ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->dire2->label ?></td>
					<td class="littletablerow" style='font-size:11;'><?=$form->dire2->output ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
	
	
		<td  valign="top">	
			<fieldset style='border: 2px outset #9AC8DA;background: #E0ECF8;'>
			<legend class="titulofieldset" style='color: #114411;'>Cuenta</legend>
			<table style="height: 100%;width: 100%">
				<tr>
					<td  width="95" class="littletableheaderc"><?=$form->moneda->label  ?></td>
					<td class="littletablerow"><?=$form->moneda->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"> <?=$form->tipocta->label ?> </td>
					<td class="littletablerow"> <?=$form->tipocta->output ?> </td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->proxch->label  ?></td>
					<td class="littletablerow"><?=$form->proxch->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->dbporcen->label  ?></td>
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
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Enlaces</legend>
			<table width= '100%' >
				<tr>
					<td width='120px' class="littletableheaderc"><?=$form->codprv->label  ?></td>
					<td class="littletablerow"><?=$form->codprv->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"> <?=$form->depto->label ?> </td>
					<td class="littletablerow"> <?=$form->depto->output ?> </td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->gastocom->label  ?></td>
					<td class="littletablerow"><?=$form->gastocom->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->gastoidb->label  ?></td>
					<td class="littletablerow"><?=$form->gastoidb->output ?></td>
				</tr>				
				<tr>
				<td class="littletableheaderc"><?=$form->cuenta->label ?></td>
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
			</fieldset>
		</td>
		<?php if( $form->_status == 'show') { ?>
		<td valign='top'>
			<table width= '100%' >
				<tr>
					<td>
						<table width= '100%' >
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td align='center' style='font-size:14;font-weight: bold'>SALDO ACTUAL</td>
							</tr>
						</table>
					<td>
				</tr>
				<tr>
					<td>
					<?php if($form->saldo->value >= 0 ) { ?>
					<fieldset style='border: 6px outset #407E13;background: #0B610B;'>
					<?php } else { ?>
					<fieldset style='border: 6px outset #8A0808;background: #B40404;'>
					<?php } ?>
					<table width= '100%' >
						<tr>
							<td align='center' style='font-size:18;font-weight: bold;color:#FFFFFF'><? echo nformat($form->saldo->value); ?></td>
						</tr>
					</table>
					</fieldset>
					</td>
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