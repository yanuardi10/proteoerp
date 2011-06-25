<?php
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

foreach($form->detail_fields['sitems'] AS $ind=>$data) $campos[]=$data['field'];
$campos='<tr id="tr_sitems_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';
$campos.=' <td class="littletablerow"><a href=\'#\' onclick="del_sitems(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);


foreach($form->detail_fields['sitems'] AS $ind=>$data) $campos[]=$data['field'];
$campos='<tr id="tr_sitems_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';
$campos.=' <td class="littletablerow"><a href=\'#\' onclick="del_sitems(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);

foreach($form->detail_fields['sfpa'] AS $ind=>$data){ if(!empty($data['field'])) $campossfpa[]=$data['field']; }
$campossfpa='<tr id="tr_sfpa_<#i#>"><td class="littletablerow">'.join('</td><td>',$campossfpa).'</td>';
$campossfpa.=' <td class="littletablerow"><a href=\'#\' onclick="del_sfpa(<#i#>);return false;">Eliminar</a></td></tr>';
$campossfpa=$form->js_escape($campossfpa);

//echo $form_scripts;
//echo $form_begin;
if($form->_status!='show'){

?>

<script language="javascript" type="text/javascript">
var sitems_cont =<?php echo $form->max_rel_count['sitems']; ?>;
var sfpa_cont=<?php echo $form->max_rel_count['sfpa'];?>;


$(document).ready(function() {
	$(".inputnum").numeric(".");
});

function add_sitems(){
	var htm = <?php echo $campos; ?>;
	can = sitems_cont.toString();
	con = (sitems_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	sitems_cont=sitems_cont+1;
}

function add_sfpa(){
	var htm = <?php echo $campossfpa; ?>;
	var can = sfpa_cont.toString();
	var con = (sfpa_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__sfpa").before(htm);
	sfpa_cont=sfpa_cont+1;
}

function del_sfpa(id){
	id = id.toString();
	obj='#tr_sfpa_'+id;
	$(obj).remove();
}

function del_sitems(id){
	id = id.toString();
	obj='#tr_sitems_'+id;
	$(obj).remove();
}
</script>
<?php } ?>
	
<table align='center' width="99%">
	<tr>
		<td align='right'><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td><div class="alert"> <?php if(isset($form->error_string)) echo $form->error_string; ?></div></td>
	</tr>
	<tr>
			<th colspan='5' class="littletableheader">Factura <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->numero->output,8,0,0); ?></b></th>
			</tr>
	<tr>
		<td>
		<fieldset style='border: 1px solid #9AC8DA;background: #FFFDE9;'>
		<legend class="subtitulotabla" style='color: #114411;'>Documento</legend>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td class="littletableheader"><?php echo $form->tipo_doc->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->tipo_doc->output ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->cliente->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->cliente->output.$form->nombre->output ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->rifci->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->rifci->output ?>&nbsp; </td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->vd->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->vd->output ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->fecha->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output  ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->peso->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->peso->output ?>&nbsp; </td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->direc->label  ?>&nbsp;</td>
				<td class="littletablerow"collspan=5>   <?php echo $form->direc->output ?>&nbsp;</td>
			</tr>
		</table>
		</fieldset>
		</td>
	</tr>
	<tr>
		<td>
		<fieldset style='border: 1px solid #9AC8DA;background: #EFEFFF;'>
		<legend class="subtitulotabla" style='color: #114411;'>Detalle</legend>
		<table width='100%'>
			<tr>
				<td class="littletableheaderdet">C&oacute;digo</td>
				<td class="littletableheaderdet">Descripci&oacute;n</td>
				<td class="littletableheaderdet" align="right">Cantidad</td>
				<td class="littletableheaderdet" align="right">Precio</td>
				<td class="littletableheaderdet" align="right">Importe</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheaderdet">Acci&oacute;n&nbsp;</td>
				<?php } ?>
			</tr>
			<?php 
			for($i=0; $i < $form->max_rel_count['sitems']; $i++) {
				$codigoa ="codigoa_$i";
				$desca ="desca_$i";
				$cana ="cana_$i";
				$precio ="precio_$i";
				$importe="tota_$i";
			?>
			<tr id='tr_sitems_<?=$i ?>'>
				<td class="littletablerow" nowrap><?php echo $form->$codigoa->output ?></td>
				<td class="littletablerow">       <?php echo $form->$desca->output ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$cana->output  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$precio->output ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$importe->output  ?></td>
				<?php if($form->_status!='show') {?>
					<td class="littletablerow"><a href='#' onclick='del_sitems(<?php echo $i; ?>);return false;'>Eliminar</a></td>
				<?php } ?>
			</tr>
			<?php if( $form->_status == 'show') {?>
				
			<?php } // SHOW ?>
			<?php } ?>
			<tr id='__UTPL__'>
				<td colspan='9' class="littletableheaderdet">&nbsp;</td>
			</tr>
		</table>
		</fieldset>
		<?php if( $form->_status != 'show') {?>
			<input name="btn_add_sitems" value="Agregar Gasto" onclick="add_sitems()" class="button" type="button">
		<?php } ?>
		</td>
	</tr>
	<tr>
		<td>
		<fieldset style='border: 1px solid #9AC8DA;background: #EFEFFF;'>
		<legend class="subtitulotabla" style='color: #114411;'>Detalle2</legend>
		<table width='100%'>
			<tr>
				<td class="littletableheaderdet">Tipo</td>
				<td class="littletableheaderdet">Monto</td>
				<td class="littletableheaderdet">Banco</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheaderdet">Acci&oacute;n&nbsp;</td>
				<?php } ?>
			</tr>
			<?php
			
			for($i=0; $i < $form->max_rel_count['sfpa']; $i++) {
				$tipo= "tipo_$i";
				$monto      = "monto_$i";
				$banco    = "banco_$i";
			?>
			<tr id='tr_sfpa_<?php echo $i; ?>'>
				<td class="littletablerow" nowrap><?php echo $form->$tipo->output ?></td>
				<td class="littletablerow"><?php echo $form->$monto->output      ?></td>
				<td class="littletablerow"><?php echo $form->$banco->output    ?></td>
				<?php if($form->_status!='show') {?>
					<td class="littletablerow"><a href='#' onclick='del_sfpa(<?php echo $i; ?>);return false;'>Eliminar</a></td>
				<?php }
			}?>
			</tr>
			<tr id='__UTPL__sfpa'>
				<td colspan='9' class="littletableheaderdet">&nbsp;</td>
			</tr>
		</table>
		</fieldset>
		<?php if( $form->_status != 'show') {?>
			<input name="btn_add_sfpa" value="Agregar Retenciones " onclick="add_sfpa()" class="button" type="button">
		<?php } ?>
		//<?php echo $form_end     ?>
		<?php //echo $container_bl ?>
		<?php //echo $container_br ?>
		</td>



	</tr>
	<tr>
		<td align='center'>
			<table width='100%'><tr>
			<td valign='top'>
			<fieldset style='border: 1px solid #9AC8DA;background: #FFFBE9;'>
			<legend class="subtitulotabla" style='color: #114411;'>Totales</legend>
			<table width='100%'>
				<tr>
					<td class="littletableheader"><?php echo $form->totals->label    ?>&nbsp;</td>
					<td class="littletablerow" align='right'>   <?php echo $form->totals->output   ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->ivat->label   ?>&nbsp;</td>
					<td class="littletablerow" align='right'>   <?php echo $form->ivat->output  ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->totalg->label  ?>&nbsp;</td>
					<td class="littletablerow" align='right'>   <?php echo $form->totalg->output ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
			</td></tr></table>
		</td>
	</tr>
	
	
	<?php if($form->_status == 'show'){ ?>
	<tr>
		<td>
			<fieldset style='border: 1px solid ##8A0808;background: #FFFBE9;'>
			<legend class="subtitulotabla" style='color: #114411;'>Informacion del Registro</legend>
			<table width='100%' cellspacing='1' >
				<tr style='font-size:12px;color:#0B3B0B;background-color: #F7BE81;'>
					<td align='center' >Usuario</td>
					<td align='center' >Nombre </td>
					<td align='center' >Fecha  </td>
					<td align='center' >Hora   </td>
					<td align='center' >Transacci&oacute;n</td>
				</tr>
				<tr>
					<?php
						$mSQL="SELECT us_nombre FROM usuario WHERE us_codigo='".trim($form->_dataobject->get('usuario'))."'";
						$us_nombre = $this->datasis->dameval($mSQL);
					
					?>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('usuario'); ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $us_nombre ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('estampa'); ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('hora'); ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('transac'); ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
	<?php } ?>
</table>
<?php endif; ?>