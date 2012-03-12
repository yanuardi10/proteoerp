<?php

$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itordc');
$scampos  ='<tr id="tr_itordc_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['codigo']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['descrip']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['cantidad']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['costo']['field']. '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['importe']['field'];
for($o=1;$o<5;$o++){
	$it_obj   = "precio${o}";
	$scampos .= $campos[$it_obj]['field'];
}
$scampos .= $campos['itiva']['field'];
$scampos .= $campos['ultimo']['field'];
$scampos .= $campos['pond']['field'];
$scampos .= $campos['sinvpeso']['field'].'</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_itordc(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var itordc_cont=<?php echo $form->max_rel_count['itordc']; ?>;

$(function(){
	$(".inputnum").numeric(".");
	totalizar();
});

function importe(id){
	var ind     = id.toString();
	var cana    = Number($("#cantidad_"+ind).val());
	var preca   = Number($("#costo_"+ind).val());
	var importe = roundNumber(cana*preca,2);
	$("#importe_"+ind).val(importe);

	totalizar();
}

function totalizar(){
	var iva    =0;
	var totalg =0;
	var itiva  =0;
	var itpeso =0;
	var totals =0;
	var importe=0;
	var peso   =0;
	var cana   =0;
	var arr=$('input[name^="importe_"]');
	jQuery.each(arr, function() {
		nom=this.name
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind     = this.name.substring(pos+1);
			cana    = Number($("#cantidad_"+ind).val());
			itiva   = Number($("#itiva_"+ind).val());
			itpeso  = Number($("#sinvpeso_"+ind).val());
			importe = Number(this.value);

			peso    = peso+(itpeso*cana);
			iva     = iva+importe*(itiva/100);
			totals  = totals+importe;
		}
	});
	$("#peso").val(roundNumber(peso,2));
	$("#montotot").val(roundNumber(totals+iva,2));
	$("#montonet").val(roundNumber(totals,2));
	$("#montoiva").val(roundNumber(iva,2));
}

function add_itordc(){
	var htm = <?php echo $campos; ?>;
	can = itordc_cont.toString();
	con = (itordc_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#cantidad_"+can).numeric(".");
	itordc_cont=itordc_cont+1;
}


function del_itordc(id){
	id = id.toString();
	$('#tr_itordc_'+id).remove();
	totalizar();
}
</script>
<?php } ?>

<table align='center' width="95%">
	<tr>
		<td align=right>
		<?php if ($form->_status=='show') { $id=$form->get_from_dataobjetct('numero'); ?>
		<a href="#" onclick="window.open('<?php echo base_url() ?>formatos/ver/ORDC/<?php echo $id ?>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');" heigth="600" >
		<img src='<?php echo base_url() ?>images/pdf_logo.gif'></a>
		<a href="#" onclick="window.open('<?php echo base_url() ?>formatos/verhtml/ORDC/<?php echo $id ?>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');" heigth="600" >
		<img src='<?php echo base_url() ?>images/html_icon.gif'></a>
		<?php } ?>

		<?php echo $container_tr?></td>
	</tr>
	<tr>
		<td>
		<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
		<legend class="titulofieldset" style='color: #114411;'>Orden de Compra<?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->numero->output,8,0,0); ?></legend>
		<table width="100%" style="margin: 0; width: 100%;">

			<tr >
				<td class="littletableheader"><?php echo $form->fecha->label;    ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output;   ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->proveed->label;  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->proveed->output; ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->nombre->output;  ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->arribo->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->arribo->output    ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->status->label; ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->status->output;   ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->fechafac->label  ?>&nbsp;</td>
				<td class="littletablerow" >  <?php echo $form->fechafac->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->peso->label  ?>&nbsp;</td>
				<td class="littletablerow" align="left"><?=$form->peso->output ?>&nbsp;</td>

			</tr>
		</table>
		</fieldset>
		<br>
		</td>
	</tr>
	<tr>
		<td>
		<fieldset style='border: 2px outset #9AC8DA;background: #EFEFFF;'>
		<legend class="titulofieldset" style='color: #114411;'>Lista de Art&iacute;culos</legend>
		<table width='100%'>

			<tr>
				<td class="littletableheaderdet">C&oacute;digo</td>
				<td class="littletableheaderdet">Descripci&oacute;n</td>
				<td class="littletableheaderdet">Cantidad</td>
				<td class="littletableheaderdet">Precio</td>
				<td class="littletableheaderdet">Importe</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheaderdet">&nbsp;</td>
				<?php } ?>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itordc'];$i++) {
				$it_codigo  = "codigo_$i";
				$it_descrip   = "descrip_$i";
				$it_cantidad    = "cantidad_$i";
				$it_costo   = "costo_$i";
				$it_importe = "importe_$i";
				$it_iva     = "itiva_$i";
				$it_ultimo  = "ultimo_$i";
				$it_pond    = "pond_$i";
				$it_peso    = "sinvpeso_$i";
				$it_tipo    = "sinvtipo_$i";

				$pprecios='';
				for($o=1;$o<5;$o++){
					$it_obj   = "precio${o}_${i}";
					$pprecios.= $form->$it_obj->output;
				}
				$pprecios .= $form->$it_ultimo->output;
				$pprecios .= $form->$it_pond->output;
				$pprecios .= $form->$it_iva->output;
				$pprecios .= $form->$it_peso->output;
			?>

			<tr id='tr_itordc_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" ><?php echo $form->$it_codigo->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_descrip->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_cantidad->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_costo->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_importe->output.$pprecios;?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itordc(<?=$i ?>);return false;'>Eliminar</a>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>

			<tr id='__UTPL__'>

				<td id='cueca'colspan='6' class="littletableheaderdet">&nbsp;</td>
			</tr>
		</table>
		</fieldset>
		<?php echo $container_bl ?>
		<?php echo $container_br ?>
		</td>
	</tr>
	<tr>
		<td>
		<fieldset style='border: 2px outset #9AC8DA;background: #EFEFFF;'>
		<legend class="titulofieldset" style='color: #114411;'>Totales</legend>
		<table width='100%'>
			<tr>
				<td class="littletableheaderdet"align='center'><?php echo $form->montoiva->label;    ?></td>
				<td class="littletableheaderdet"align='center'><?php echo $form->montonet->label;  ?></td>
				<td class="littletableheaderdet"align='center'><?php echo $form->montotot->label;  ?></td>
			</tr>
			<tr>
				<td class="littletablerow" align='center'><?php echo $form->montoiva->output;   ?></td>
				<td class="littletablerow" align='center'><?php echo $form->montonet->output; ?></td>
				<td class="littletablerow" align='center'><?php echo $form->montotot->output; ?></td>
			</tr>
			<tr>
				<td colspan='3' class="littletableheaderdet">&nbsp;</td>
			</tr>
		</table>
		</fieldset>
		<?php echo $form_end; ?>
		</td>
	</tr>
	<?php if($form->_status == 'show'){ ?>
	<tr>
		<td>
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Informacion del Registro</legend>
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
				<tr style='font-size:12px;color:#0B3B0B;background-color: #F7BE81;'>
					<td colspan='5' >&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
	<?php } ?>
</table>
<?php endif; ?>
