<?php echo $form_scripts?>
<?php echo $form_begin?>
<?php 
//$container_tr=join("&nbsp;", $form->_button_container["TR"]);
//$container_bl=join("&nbsp;", $form->_button_container["BL"]);
//$container_br=join("&nbsp;", $form->_button_container["BR"]);
?>
<?php if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<table border=0 width="100%">
	<tr>
		<td>
			<?php if($form->_status=='show'){ ?>
			<a href='<?php echo base_url()."ventas/scli/consulta/".$form->_dataobject->get('id'); ?>'>
			<?php
				$propiedad = array('src' => 'images/ojos.png', 'alt' => 'Consultar Movimiento', 'title' => 'Consultar Detalles','border'=>'0','height'=>'25');
				echo img($propiedad);
			?>
			</a>
			<?php } ?>
		</td>
		<td align='center' valign='middle'>
			<?php  if ($form->tipo->value=='0') echo "<div style='font-size:14px;font-weight:bold;color: #B40404'>***DESACTIVADO***</div>"; ?>&nbsp;
		</td>
		<td align='right'><?php echo $container_tr; ?></td>
	</tr>
</table>
<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
<legend class="titulofieldset" style='color: #114411;'>Identificacion</legend>
<table border=0 width="100%">
	<tr>
		<td>
			<table border=0 width="100%">
			<tr>
				<td width="100" class="littletableheaderc"><?=$form->cliente->label  ?></td>
				<td width='150' class="littletablerow" ><?=$form->cliente->output ?></td>
				<td width="60"  class="littletableheaderc"><?=$form->rifci->label ?></td>
				<td  class="littletablerow"><?php echo $form->rifci->output ?>
				<?php if($form->_status=='show'){ ?>
				<a href="#" onclick="window.open('<?php echo trim($this->datasis->traevalor("CONSULRIF"))."?p_rif=".trim($form->rifci->value);?>','CONSULRIF','height=350,width=410')" title="SENIAT" style='color:red;font-size:9px;border:none;'>SENIAT</a>
				<?php } ?>
				</td>
			</tr>	
			<tr>
				<td class="littletableheaderc"><?=$form->nombre->label ?></td>
				<td colspan='3' class="littletablerow"><?=$form->nombre->output?></td>
			</tr>
				<td class="littletableheaderc"><?=$form->nomfis->label ?></td>
				<td colspan='3' class="littletablerow"><?=$form->nomfis->output?></td>
			<tr>
				<td class="littletableheaderc"><?=$form->contacto->label  ?></td>
				<td colspan='3' class="littletablerow"><?=$form->contacto->output ?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->repre->label  ?></td>
				<td colspan='3' class="littletablerow"><?=$form->repre->output ?></td>
			</tr>
			</table>
		</td>
		<td>

			<table border=0 width="100%">
			<tr>
				<td width='100' class="littletableheaderc"><?=$form->grupo->label ?></td>
				<td  class="littletablerow"><?=$form->grupo->output?></td>
			</tr>	
				<td class="littletableheaderc"> <?=$form->tipo->label ?></td>
				<td class="littletablerow"> <?=$form->tipo->output ?></td>
			<tr>
				<td class="littletableheaderc"><?=$form->tiva->label  ?></td>
				<td class="littletablerow"><?=$form->tiva->output ?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?=$form->cirepre->label  ?></td>
				<td class="littletablerow"><?=$form->cirepre->output ?></td>
			</tr>
			
			</table>
		</td>
	</tr>
</table>
</fieldset>
<table  width="100%" border='0'>
	<tr>
		<td valign='top'>
			<fieldset style='border: 2px outset #9AC8DA;background: #E0ECF8;'>
			<legend class="titulofieldset" style='color: #114411;'>Direcciones</legend>
			<table width= "100%" >
				<tr>
					<td width='60px' class="littletableheaderc"><?=$form->dire11->label ?></td>
					<td class="littletablerow"><?=$form->dire11->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->dire12->label ?></td>
					<td class="littletablerow"><?=$form->dire12->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->ciudad1->label   ?></td>
					<td class="littletablerow" style='font-size:11;'><?=$form->ciudad1->output  ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->dire21->label ?></td>
					<td class="littletablerow"><?=$form->dire21->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->dire22->label ?></td>
					<td class="littletablerow"><?=$form->dire22->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->ciudad2->label ?></td>
					<td class="littletablerow" style='font-size:11;'><?=$form->ciudad2->output ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td  valign="top">	
			<fieldset style='border: 2px outset #9AC8DA;background: #E0ECF8;'>
			<legend class="titulofieldset" style='color: #114411;'>Ubicacion</legend>
			
			
			<table style="height: 100%;width: 100%">
				<tr>
					<td  width="70" class="littletableheaderc"><?=$form->zona->label  ?></td>
					<td class="littletablerow"><?=$form->zona->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->pais->label  ?> </td>
					<td class="littletablerow"    ><?=$form->pais->output ?> </td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->telefono->label  ?></td>
					<td class="littletablerow"    ><?=$form->telefono->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->telefon2->label  ?></td>
					<td class="littletablerow"    ><?=$form->telefon2->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->email->label  ?></td>
					<td class="littletablerow"    ><?=$form->email->output ?></td>
				</tr>
					<td class="littletableheaderc"><?=$form->cuenta->label;  ?></td>
					<td class="littletablerow"    ><?=$form->cuenta->output; ?>
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
<table  width="100%" border='0' >
	<tr>
		<td>
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Comisiones</legend>
			<table width= '100%' >
				<tr>
					<td class="littletableheaderc"><?=$form->vendedor->label  ?></td>
					<td class="littletablerow"><?=$form->vendedor->output ?></td>
					<td class="littletableheaderc">%</td>
					<td class="littletablerow"><?=$form->porvend->output ?></td>
				</tr>				
				<tr>
					<td class="littletableheaderc"><?=$form->cobrador->label  ?></td>
					<td class="littletablerow"><?=$form->cobrador->output ?></td>
					<td class="littletableheaderc">%</td>
					<td class="littletablerow"><?=$form->porcobr->output ?></td>
				</tr>				
			</tr>
			</table>
			</fieldset>
		</td>
		<td valign='Top'>
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Credito</legend>
			<table width= '100%' >
				<tr>
					<td class="littletableheaderc"><?=$form->formap->label  ?></td>
					<td class="littletablerow"><?=$form->formap->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->limite->label  ?></td>
					<td class="littletablerow"><?=$form->limite->output ?></td>
				</tr>				
			</tr>
			</table>
			</fieldset>
		</td>
		<?php if( $form->_status == 'show') {  ?>
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