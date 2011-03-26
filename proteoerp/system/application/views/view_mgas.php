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
		<td valign='center'><a href='<?php echo base_url()."finanzas/mgas/mgasconsulta/".$form->codigo->output; ?>'>
		<?php
			$propiedad = array('src' => 'images/consultar.gif', 'alt' => 'Consultar Movimiento', 'title' => 'Consultas','border'=>'0');
			echo img($propiedad);
		?>
		</a></td>
		<td align='right' ><?php echo $container_tr; ?></td>

	</tr>
	<tr>
		<td colspan='2'>
			<fieldset style='border: 2px outset  #9AC8DA;background: #EFEFFF;'>
			<legend class="subtitulotabla" style='color: #114411;'>Gasto</legend>
			<table border=0 width="100%">
			<tr>
				<td width="140" class="littletableheaderc"><?=$form->codigo->label  ?></td>
				<td class="littletablerow" ><?=$form->codigo->output ?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->descrip->label ?></td>
				<td  class="littletablerow"><?=$form->descrip->output ?></td>
			</tr>	
			<tr>
				<td class="littletableheaderc"><?=$form->tipo->label ?></td>
				<td  class="littletablerow"><?=$form->tipo->output?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->grupo->label    ?></td>
				<td  class="littletablerow"><?=$form->grupo->output?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->medida->label  ?></td>
				<td class="littletablerow"><?=$form->medida->output ?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->cuenta->label ?></td>
				<td  class="littletablerow" ><?=$form->cuenta->output." "; ?>
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
	</tr>
</table>
<table  width="100%" border='0'>
	<tr>
		<td valign='top'>
			<fieldset style='border: 2px outset #81BEF7;background: #E0ECF8;'>
			<legend class="subtitulotabla" style='color: #114411;'>Costos</legend>
			<table style="height: 100%;width: 100%" >
				<tr>
					<td width="120" class="littletableheaderc"><?=$form->iva->label   ?></td>
					<td class="littletablerow"><?=$form->iva->output  ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->ultimo->label ?></td>
					<td class="littletablerow"><?=$form->ultimo->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->promedio->label ?></td>
					<td class="littletablerow"><?=$form->promedio->output ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td  valign="top">	
			<fieldset style='border: 3px outset #81BEF7;background: #E0ECF8;'>
			<legend class="subtitulotabla" style='color: #114411;'>Existencias</legend>
			<table style="height: 100%;width: 100%">
				<tr>
					<td class="littletableheaderc"><?=$form->fraxuni->label  ?></td>
					<td class="littletablerow"><?=$form->fraxuni->output ?></td>
				</tr>
				<tr>
					<td width="160" class="littletableheaderc"> <?=$form->minimo->label ?> </td>
					<td class="littletablerow"> <?=$form->minimo->output ?> </td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->maximo->label  ?></td>
					<td class="littletablerow"><?=$form->maximo->output ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td colspan='2'>
			<fieldset style='border: 2px outset #9AC8DA;background: #EFEFFF;'>
			<legend class="subtitulotabla" style='color: #114411;'>Cantidad Actual</legend>
			<table style="height: 100%;width: 100%" >
				<tr>
					<td class="littletableheaderc"><?=$form->unidades->label  ?></td>
					<td class="littletablerow"><?=$form->unidades->output ?></td>
					<td class="littletableheaderc"> <?=$form->fraccion->label ?> </td>
					<td class="littletablerow"> <?=$form->fraccion->output ?> </td>
				</tr>
			</table>
		</td>
	</tr>
        <?php if ( $this->datasis->traevalor('PAIS') == 'COLOMBIA' ) { ?>
	<tr>
		<td colspan='2'>
			<fieldset style='border: 1px solid #9AC8DA;background: #EFEFFF;' >
			<legend class="subtitulotabla" style='color: #114411;' >Retencion</legend>
			<table style="height: 100%;width: 100%" >
				<tr>
					<td class="littletableheaderc"><?=$form->rica->label   ?></td>
					<td class="littletablerow"><?=$form->rica->output  ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
	<?php }; ?>
	<tr>
		<td valign="top" colspan='2'><?=$form->almacenes->output ?>	</td>
	</tr>
</table>
<?php echo $container_bl.$container_br; ?>
<?php echo $form_end?>
