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
		<td align='right' colspan='2' ><?php echo $container_tr; ?></td>
	</tr>
	<tr>
		<td valign='top'>
			<fieldset style='border: 3px outset #81BEF7;background: #E0ECF8;'>
			<legend class="subtitulotabla" >Identificacion del Producto </legend>
			<table border=0 width="100%">
				<tr>
					<td width="100" class="littletableheader"><?=$form->codigo->label ?></td>
					<?php if( $form->_status == "modify" ) { ?>
					<td class="littletablerow">
					<input readonly value="<?=$form->codigo->output ?>" class='input' size='20' style='background: #F5F6CE;'  />
					<?php } else { ?>
					<td class="littletablerow"><?=$form->codigo->output ?>
					<?php } ?>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->alterno->label ?></td>
					<td class="littletablerow"><?=$form->alterno->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->enlace->label ?></td>
					<td class="littletablerow"><?=$form->enlace->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->barras->label ?></td>
					<td class="littletablerow"><?=$form->barras->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->descrip->label ?></td>
					<td class="littletablerow"><?=$form->descrip->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?php //=$form->descrip2->label ?>&nbsp;</td>
					<td class="littletablerow"><?=$form->descrip2->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->marca->label ?></td>
					<td class="littletablerow"><?=$form->marca->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->modelo->label ?></td>
					<td class="littletablerow"><?=$form->modelo->output   ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td valign='top'>
			<fieldset style='border: 3px outset #9AC8DA;background: #EFEFFF;'>
			<legend class="subtitulotabla" >Caracteristicas</legend>
			<table border=0 width="100%">
				<tr>
					<td width="100" class="littletableheader"><?=$form->tipo->label    ?></td>
					<td class="littletablerow"><?=$form->tipo->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->activo->label ?></td>
					<td class="littletablerow"><?=$form->activo->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->tdecimal->label ?></td>
					<td class="littletablerow"><?=$form->tdecimal->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->serial->label ?></td>
					<td class="littletablerow"><?=$form->serial->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->clave->label ?></td>
					<td class="littletablerow"><?=$form->clave->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->peso->label ?></td>
					<td class="littletablerow"><?=$form->peso->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->unidad->label ?></td>
					<td class="littletablerow"><?=$form->unidad->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->fracci->label ?></td>
					<td class="littletablerow"><?=$form->fracci->output   ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
	</tr>
</table>
<table border=0 width="100%">
	<tr>
		<td valign='top'>
			<fieldset style='border: 3px outset #0B610B;background: #E0F8E0;'>
			<legend class="subtitulotabla" >Organizacion</legend>
			<table border=0 width="100%">
				<tr>
					<td width='50px' class='littletableheader'><?=$form->depto->label ?></td>
					<td class="littletablerow"><?=$form->depto->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->linea->label ?></td>
					<td class="littletablerow" id='td_linea'><?=$form->linea->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->grupo->label ?></td>
					<td class="littletablerow" id='td_grupo'><?=$form->grupo->output   ?></td>
				</tr>
			</table>
		<td valign='top'>
			<fieldset style='border: 3px outset #0B610B;background: #E0F8E0;'>
			<legend class="subtitulotabla" >Clasificacion</legend>
			<table border=0 width="100%">
				<tr>
					<td class='littletableheader'><?=$form->clase->label ?></td>
					<td class="littletablerow"><?=$form->clase->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->garantia->label ?></td>
					<td class="littletablerow"><?=$form->garantia->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheader'><?=$form->comision->label ?></td>
					<td class="littletablerow"><?=$form->comision->output   ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td valign='top'>
			<fieldset style='border: 3px outset #0B610B;background: #E0F8E0;'>
			<legend class="subtitulotabla" >Impuesto</legend>
			<table border=0 width="100%">
				<tr>
					<td class="littletableheader"><?=$form->iva->label   ?></td>
					<td class="littletablerow" align='right'><?=$form->iva->output  ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->formcal->label ?></td>
					<td class="littletablerow"><?=$form->formcal->output?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->redecen->label ?></td>
					<td class="littletablerow"><?=$form->redecen->output?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>

<table width='100%'>
	<tr>
  		<td valign="top">
			<fieldset style='border: 3px outset #B45F04;background: #F8ECE0;'>
			<legend class="subtitulotabla" >Existencias</legend>
			<table width='100%' border=0 >
				<tr>
					<td class="littletableheader"><?=$form->existen->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->existen->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->exmin->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exmin->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->exmax->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exmax->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->exord->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exord->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->exdes->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exdes->output ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td valign='top'>
			<fieldset style='border: 3px outset #B45F04;background: #F8ECE0;'>
			<legend class="subtitulotabla" >Costos</legend>
			<table width='100%'>
				<tr>
					<td class="littletablerow">&nbsp;</td>
					<td class="littletablerow">&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->pond->label    ?></td>
					<td class="littletablerow" align='right'><?=$form->pond->output   ?></td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->ultimo->label   ?></td>
					<td class="littletablerow" align='right'><?=$form->ultimo->output  ?></td>
				</tr>
				<tr>
					<td class="littletableheader">&nbsp;</td>
					<td class="littletablerow">&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?=$form->standard->label    ?></td>
					<td class="littletablerow" align='right'><?=$form->standard->output   ?></td>
				</tr>
			</table>	
			</fieldset>
		</td>
		<td valign='top'>
			<fieldset style='border: 3px outset #B45F04;background: #F8ECE0;'>
			<legend class="subtitulotabla" >Precios</legend>
			<table width='100%' cellspacing='0'>
				<tr>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Precio</td>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Margen</td>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Base  </td>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Precio</td>
				</tr>
			  	<tr>
					<td class="littletableheader">1</td>
					<td class="littletablerow" align='right'><?=$form->margen1->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base1->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio1->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader">2</td>
					<td class="littletablerow" align='right'><?=$form->margen2->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base2->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio2->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader">3</td>
					<td class="littletablerow" align='right'><?=$form->margen3->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base3->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio3->output ?></td>
				</tr>
				<tr>
					<td class="littletableheader">4</td>
					<td class="littletablerow" align='right'><?=$form->margen4->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base4->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio4->output ?></td>
				</tr>
				<tr>
					<td class="littletablerow">&nbsp;</td>
					<td class="littletablerow">&nbsp;</td>
					<td class="littletablerow">&nbsp;</td>
					<td class="littletablerow">&nbsp;</td>
				</tr>
			</table>	
			</fieldset>
		</td>
	</tr>
</table>
<table width='100%'>
	<tr>
		<td valign="top">
			<?=$form->almacenes->output ?>
		</td>
		<td valign='top'>
			<?php if($form->_status=="show"){ ?>
			<fieldset  style='border: 3px outset #AEB404;background: #F5F6CE;'>
			<legend class="subtitulotabla" >Ultimos Movimientos</legend>
			<table width='100%' >
				<tr>
					<td class="littletableheader" style='background:#F5F6CE;color:#112211' >Compras</td>
					<td class="littletableheader" style='background:#F5F6CE;color:#112211' align='right'><?=$form->fechav->label?></td>
					<td class="littletablerow"><?=$form->fechav->output   ?></td>
					
				</tr>
				<tr>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Fecha</td>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Proveedor</td>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Precio</td>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:10px'><?=$form->pfecha1->output?></td>
					<td class="littletablerow" style='font-size:10px'><?=$form->proveed1->output?>
					<td class="littletablerow" style='font-size:10px' align='right'><?=$form->prepro1->output?>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:10px'><?=$form->pfecha2->output?></td>
					<td class="littletablerow" style='font-size:10px'><?=$form->proveed2->output?>
					<td class="littletablerow" style='font-size:10px' align='right'><?=$form->prepro2->output?>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:10px;'><?=$form->pfecha3->output?></td>
					<td class="littletablerow" style='font-size:10px;'><?=$form->proveed3->output?>
					<td class="littletablerow" style='font-size:10px;' align='right'><?=$form->prepro3->output?>
				</tr>
			</table>
			</fieldset>
			<?php };?>
		</td>
	</tr>
</table>
<?php echo $container_bl.$container_br; ?>
</div>
<?php echo $form_end?>
<?php 
	//foreach (get_object_vars($form) as $label=>$tiene)
	//	echo "$label => $tiene <br>";
	//echo '<pre>';print_r($form->grupo->request);echo '</pre>'
?>