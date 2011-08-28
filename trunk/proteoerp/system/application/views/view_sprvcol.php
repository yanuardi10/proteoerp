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
			<a href='<?php echo base_url()."compras/sprvcol/consulta/".$form->_dataobject->get('id'); ?>'>
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
	<tr>
		<td colspan='2'>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Identificacion</legend>
			<table border=0 width="100%">
			<tr>
				<td width="70" class="littletableheaderc"><?=$form->proveed->label  ?></td>
				<td width='95' class="littletablerow" >   <?=$form->proveed->output ?></td>
				<td class="littletableheaderc"><?php echo $form->rif->label; ?> </td>
				<td class="littletablerow">
					<?php echo $form->docui->output; ?>
					<?php echo $form->rif->output;   ?>
					<?php echo $form->crc->output;   ?>
				</td>
			</tr>
			<?php if($form->_status!='show' ){ ?>
			<tr>
				<td class="littletableheaderc">        <?=$form->nombre1->label ?></td>
				<td colspan='3' class="littletablerow"><?=$form->nombre1->output?></td>
			</tr>

			<tr id='tr_nombre2' >
				<td class="littletableheaderc">        <?=$form->nombre2->label ?></td>
				<td colspan='3' class="littletablerow"><?=$form->nombre2->output?></td>
			</tr>

			<tr id='tr_apellido1' >
				<td class="littletableheaderc">        <?=$form->apellido1->label ?></td>
				<td colspan='3' class="littletablerow"><?=$form->apellido1->output?></td>
			</tr>

			<tr id='tr_apellido2' >
				<td class="littletableheaderc">        <?=$form->apellido2->label ?></td>
				<td colspan='3' class="littletablerow"><?=$form->apellido2->output?></td>
			</tr>

			<?php }else{?>

			<tr id='tr_nombre' >
				<td class="littletableheaderc">        <?=$form->nombre->label ?></td>
				<td colspan='3' class="littletablerow"><?=$form->nombre->output?></td>
			</tr>

			<?php } ?>

			<tr>
				<td class="littletableheaderc"><?=$form->contacto->label  ?></td>
				<td colspan='3' class="littletablerow"><?=$form->contacto->output ?></td>
			</tr>
			</table>
			</fieldset>
		</td>
		<td>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
			<legend>&nbsp;</legend>
			<table border=0 width="100%">
			<tr>
				<td width='50' class="littletableheaderc"><?php echo $form->grupo->label ?></td>
				<td  class="littletablerow">              <?php echo $form->grupo->output?></td>
			</tr></tr>
				<td class="littletableheaderc"> <?php echo $form->tipo->label ?></td>
				<td class="littletablerow">     <?php echo $form->tipo->output ?></td>
			</tr><tr>
				<td class="littletableheaderc"><?php echo $form->tiva->label  ?></td>
				<td class="littletablerow">    <?php echo $form->tiva->output ?></td>
			</tr><tr>
				<td class="littletableheaderc"><?php echo $form->origen->label  ?></td>
				<td class="littletablerow">    <?php echo $form->origen->output ?></td>
			</tr>
			
			</table>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Nombre Fiscal/Completo</legend>
			<table border=0 width="100%">
			<tr>
				<td colspan='3' class="littletablerow"><?=$form->nomfis->output?></td>
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
					<td class="littletablerow"><?=$form->direc1->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletablerow"><?=$form->direc2->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:11;'><?=$form->direc3->output  ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td valign='top'>
			<fieldset style='border: 2px outset #9AC8DA;background: #E0ECF8;'>
			<legend class="titulofieldset" style='color: #114411;'>Contacto</legend>
			<table width= "100%" >
				<tr>
					<td class="littletableheaderc"><?=$form->telefono->label ?></td>
					<td class="littletablerow"><?=$form->telefono->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->email->label  ?></td>
					<td class="littletablerow"    ><?=$form->email->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->url->label  ?></td>
					<td class="littletablerow"    ><?=$form->url->output ?></td>
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
					<td class="littletableheaderc">Banco</td>
					<td class="littletableheaderc">Cuenta</td>
				</tr>				
				<tr>
					<td class="littletablerow"><?=$form->banco1->output ?></td>
					<td class="littletablerow"><?=$form->cuenta1->output ?></td>
				</tr>				
				<tr>
					<td class="littletablerow"><?=$form->banco2->output ?></td>
					<td class="littletablerow"><?=$form->cuenta2->output ?></td>
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
					<td class="littletableheaderc"><?=$form->cliente->label  ?></td>
					<td class="littletablerow"><?=$form->cliente->output ?></td>
				</tr>
			</tr>
			</table>
			</fieldset>
			<fieldset style='border: 5px outset #3FCF3F;background: #3FCF3F;'>
			<table width= '100%' >
				<tr>
					<td>SALDO</td>
					<td style='font-size:18px;color:#0;font-weight: bold'><? echo nformat($this->datasis->dameval("SELECT SUM(monto*IF(tipo_doc IN ('FC','ND','GI'),1,-1)) monto FROM sprm WHERE cod_prv='".$form->_dataobject->get('proveed')."'")) ?></td>
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