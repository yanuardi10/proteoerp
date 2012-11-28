<?php 
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	$meco = $form->output;
	$meco = str_replace('class="tablerow"','class="tablerow" style="font-size:20px; align:center;" ',$meco);
	echo $meco."</td><td align='center'>".img("images/borrar.jpg");
else:
?>
<?php echo $script ?>

<?php echo $form_scripts?>

<?php echo $form_begin?>

<?php if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<?php
/*
<table border='0' width='100%' style='background: #EEEEEE'>
	<tr>
		<td width='40' align='center'>
			<?php if($form->_status=='show'){ ?>
			<a href='<?php echo base_url()."ventas/scli/consulta/".$form->_dataobject->get('id'); ?>'>
			<?php
				$propiedad = array('src' => 'images/ojos.png', 'alt' => 'Consultar Movimiento', 'title' => 'Consultar Detalles','border'=>'0','height'=>'25');
				echo img($propiedad);
			?>
			</a>
		</td>
		<td width='40' align='center'>
			<a href='javascript:fusionar("<?php echo $form->_dataobject->get('cliente'); ?>")'>
			<?php
				$propiedad = array('src' => 'images/fusionar.png', 'alt' => 'Cambio de Codigo', 'title' => 'Cambio de codigo','border'=>'0','height'=>'30','width'=>'32');
				echo img($propiedad);
			?>
			</a>
		</td>

		</td>

		<td align='center' valign='middle' width='40'>
			<?php } ?>

			<a href='<?php echo base_url()."reportes/index/scli" ?>'>
			<?php
				$propiedad = array('src' => 'images/reportes.gif', 'alt' => 'Reportes', 'title' => 'Reportes','border'=>'0','height'=>'20');
				echo img($propiedad);
			?>
			</a>
		</td>
		<td align='center' valign='middle'>
			<?php  if ($form->tipo->value=='0') echo "<div style='font-size:14px;font-weight:bold;color: #B40404'>***DESACTIVADO***</div>"; ?>&nbsp;
		</td>
		<td align='right'><?php echo $container_tr; ?></td>
	</tr>
</table>
<legend class="titulofieldset" style='color: #114411;'>Identificacion</legend>
#FFFDE9
*/
?>

<fieldset style='border: 1px outset #9AC8DA;background: #F6F8FF;'>
<table border='0' width="100%">
	<tr>
		<td>
			<table border='0' width="100%">
				<tr>
					<td width="100" class="littletableheaderc"><?=$form->cliente->label  ?></td>
					<td width="100" class="littletablerow" ><?=$form->cliente->output ?></td>
					<td class="littletableheaderc"><?=$form->rifci->label ?></td>
					<td class="littletablerow"><?php echo $form->rifci->output ?>
					<td class="littletablerow" align="right"><?=$form->tiva->output ?></td>
					</td>
				</tr>	
				<tr>
					<td class="littletableheaderc"><?=$form->nombre->label ?></td>
					<td colspan='4' class="littletablerow"><?=$form->nombre->output?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->nomfis->label ?></td>
					<td colspan='4' class="littletablerow"><?=$form->nomfis->output?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->contacto->label  ?></td>
					<td colspan='4' class="littletablerow"><?=$form->contacto->output ?></td>
				</tr>
			</table>
		</td>
		<td valign='top' width='25%'>
			<table border=0 width="100%">
				<tr>
					<td class="littletableheaderc"> <?=$form->tipo->label ?></td>
					<td class="littletablerow"> <?=$form->tipo->output ?></td>
				</tr><tr>
					<td colspan='2'>
					<fieldset style='border: 1px dotted #8AF8F8;background: #FAFAFF;'>
					<table width= '100%' >
						<tr>
							<td class="littletableheaderc"><?php echo $form->mmargen->label; ?></td>
							<td class="littletablerow"    ><?php echo $form->mmargen->output; ?></td>
						</tr><tr>
							<td colspan="2">&nbsp;</td>
						</tr><tr>
							<td colspan="2" class="littletableheaderc"><?=$form->zona->label  ?></td>
						</tr><tr>
							<td colspan="2" class="littletablerow"><?=$form->zona->output ?></td>
						</tr>
					</table>
					</fieldset>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table width='100%' style='border: 1px dotted #8AF8F8;background: #FFFBE9;'>
	<tr>
		<td class="littletableheaderc"><?=$form->grupo->label ?></td>
		<td class="littletablerow"><?=$form->grupo->output?></td>
		<td class="littletableheaderc"><?php echo $form->socio->label ?></td>
		<td class="littletablerow"><?php echo $form->socio->output ?></td>
<?php if (!empty($form->socio->value)) { ?>
		<td class="littletablerow"><?php echo $this->datasis->dameval("SELECT nombre FROM scli WHERE cliente='".$form->socio->value."'") ?></td>
<?php }; ?>
	</tr>
</table>
</fieldset>

<div id="maintabcontainer">
	<ul>
		<li><a href="#tab1">Direcciones</a></li>
		<li><a href="#tab2">Valores</a></li>
		<li><a href="#tab3">Anexo</a></li>
	</ul>
	<div id="tab1" style='background:#EEFFFF'>
	<table border='0' width="100%">
	<tr>
		<td valign='top' width='50%' style='border-top: 1px dotted; border-right: 1px dotted'>
			<table border='0' width='100%' >
				<tr>
					<td colspan='2' class="littletableheaderc">Direccion de Oficina</td>
				</tr>
				<tr>
					<td colspan='2' class="littletablerow"><?=$form->dire11->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='2' class="littletablerow"><?=$form->dire12->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->ciudad1->label   ?></td>
					<td class="littletablerow" ><?=$form->ciudad1->output  ?>&nbsp;</td>
				</tr>
			</table>
		</td>
		<td valign='top' width='50%' style='border-top: 1px dotted; border-left: 1px dotted'>
			<table border='0'  width='100%'>
				<tr>
					<td colspan='2' class="littletableheaderc">Direccion de Envio</td>
				</tr>
				<tr>
					<td colspan='2' class="littletablerow"><?=$form->dire21->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='2' class="littletablerow"><?=$form->dire22->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->ciudad2->label ?></td>
					<td class="littletablerow" style='font-size:11;'><?=$form->ciudad2->output ?>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	<br />
	<table style='height: 100%;width: 100%;border: 1px dotted;'>
	<tr>
		<td width="70" class="littletableheaderc"><?=$form->telefono->label  ?></td>
		<td class="littletablerow"    ><?=$form->telefono->output ?></td>
		<td class="littletableheaderc"><?=$form->url->label  ?></td>
		<td colspan="3" class="littletablerow"    ><?=$form->url->output ?></td>
	</tr>
	<tr>
		<td class="littletableheaderc"><?=$form->telefon2->label  ?></td>
		<td class="littletablerow"    ><?=$form->telefon2->output ?></td>
		<td class="littletableheaderc"><?=$form->fb->label        ?> </td>
		<td class="littletablerow"    ><?=$form->fb->output       ?> </td>
		<td class="littletableheaderc"><?=$form->pin->label  ?></td>
		<td class="littletablerow"    ><?=$form->pin->output ?></td>
	</tr>
	<tr>
		<td class="littletableheaderc"><?=$form->email->label    ?></td>
		<td class="littletablerow"    ><?=$form->email->output   ?></td>
		<td class="littletableheaderc"><?=$form->twitter->label  ?> </td>
		<td class="littletablerow"    ><?=$form->twitter->output ?> </td>
	</tr>
	</table>
	</div>
        <div id="tab2" style='background:#EEFFFF'>
		<table width="100%" border='0' >
		<tr>
			<td class="littletableheaderc">Representante Legal</td>
			<td class="littletablerow"><?=$form->repre->output ?></td>
			<td class="littletableheaderc">C.I.</td>
			<td class="littletablerow"><?=$form->cirepre->output ?></td>
		</tr>
		</table>
		<table width="100%">
		<tr>	
			<td class="littletableheaderc"><?=$form->vendedor->label  ?></td>
			<td class="littletablerow"><?=$form->vendedor->output ?></td>
			<td class="littletableheaderc">Comision %</td>
			<td class="littletablerow"><?=$form->porvend->output ?></td>
		</tr>				
		<tr>
			<td class="littletableheaderc"><?=$form->cobrador->label  ?></td>
			<td class="littletablerow"><?=$form->cobrador->output ?></td>
			<td class="littletableheaderc">Comision %</td>
			<td class="littletablerow"><?=$form->porcobr->output ?></td>
		</tr>				
		<tr>
			<td class="littletableheaderc">Cuenta Contable</td>
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
	<table width= '100%' >
		<tr>
			<td class="littletableheaderc"><?=$form->mensaje->label  ?></td>
			<td class="littletablerow"><?=$form->mensaje->output ?></td>
		</tr>				
		<tr>
			<td class="littletableheaderc"><?=$form->observa->label  ?></td>
			<td class="littletablerow"><?=$form->observa->output ?></td>
		</tr>				
	</table>
	</fieldset>
        </div>
        <div id="tab3" style='background:#EEFFFF'>
			<fieldset style='border: 2px outset #8AF8F8;background:#EEFFFF;'>
			<table width= '100%' >
			<tr>
				<td class="littletableheaderc"><?=$form->tarifa->label  ?></td>
				<td class="littletablerow"><?php echo $form->tarifa->output.$form->tactividad->output ?></td>
			</tr>				
			<tr>
				<td class="littletableheaderc"><?=$form->upago->label  ?></td>
				<td class="littletablerow"><?=$form->upago->output ?></td>
			</tr>				
		</table>
	</div>
</div>

<?php echo $container_bl.$container_br; ?>
<?php echo $form_end?>
<?php endif; ?>
