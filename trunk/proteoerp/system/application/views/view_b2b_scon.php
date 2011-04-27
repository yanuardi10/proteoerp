<?php
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itpsinv');
$scampos  ='<tr id="tr_itpsinv_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['codigo']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['desca']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['cana']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['precio']['field']. '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['importe']['field'];
for($o=1;$o<5;$o++){
	$it_obj   = "precio${o}";
	$scampos .= $campos[$it_obj]['field'];
}
$scampos .= $campos['itiva']['field'];
$scampos .= $campos['sinvpeso']['field'];
$scampos .= $campos['sinvtipo']['field'].'</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_itpsinv(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var itpsinv_cont=<?php echo $form->max_rel_count['itpsinv']; ?>;
var invent = (<?php echo $inven; ?>);
$(function(){
	$(document).keydown(function(e){
		if (e.which == 13) return false;
	});

	$(".inputnum").numeric(".");
});
</script>
<?php } ?>

<table align='center' width="95%">
	<tr>
		<td align=right><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<th colspan='5' class="littletableheader">Pr&eacute;stamo de inventario <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->numero->output,8,0,0); ?></b></th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->fecha->label;   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output;  ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->clipro->label;  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->clipro->output,$form->nombre->output; ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->orden->label;  ?>&nbsp;</td>
				<td class="littletablerow" align="left"><?php echo $form->orden->output; ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->observa->label; ?>&nbsp;</td>
				<td class="littletablerow"   ><?php echo $form->observa->output;?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->peso->label;  ?>&nbsp;</td>
				<td class="littletablerow" align="left"><?php echo $form->peso->output; ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->almacen->label;     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->almacen->output;    ?>&nbsp;</td>
			</tr>
		</table><br>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr>
				<th colspan='6' class="littletableheader">Lista de Art&iacute;culos</th>
			</tr>
			<tr>
				<td class="littletableheader">C&oacute;digo</td>
				<td class="littletableheader">Descripci&oacute;n</td>
				<td class="littletableheader">Cantidad</td>
				<td class="littletableheader">Recibido</td>
				<td class="littletableheader">Precio</td>
				<td class="littletableheader">Importe</td>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itpsinv'];$i++) {
				$it_codigo  = "codigo_$i";
				$it_desca   = "desca_$i";
				$it_cana    = "cana_$i";
				$it_canareci= "recibido_$i";
				$it_precio  = "precio_$i";
				$it_importe = "importe_$i";
				$it_iva     = "itiva_$i";
				$it_tipo    = "sinvtipo_$i";
				$it_peso    = "sinvpeso_$i";

				$pprecios='';
				for($o=1;$o<5;$o++){
					$it_obj   = "precio${o}_${i}";
					$pprecios.= $form->$it_obj->output;
				}
				$pprecios .= $form->$it_iva->output;
				$pprecios .= $form->$it_tipo->output;
				$pprecios .= $form->$it_peso->output;
			?>

			<tr id='tr_itpsinv_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" nowrap><?php echo $form->$it_codigo->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_desca->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_cana->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_canareci->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_precio->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_importe->output.$pprecios;?></td>

			</tr>
			<?php } ?>

			<tr id='__UTPL__'>
				<td id='cueca'></td>
			</tr>
		</table>
		<?php echo $container_bl ?>
		<?php echo $container_br ?>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr>
				<th colspan='6' class="littletableheader">Res&uacute;men Financiero</th>
			</tr>
			<tr>
				<td class="littletableheader">           <?php echo $form->impuesto->label;    ?></td>
				<td class="littletablerow" align='right'><?php echo $form->impuesto->output;   ?></td>
				<td class="littletableheader">           <?php echo $form->stotal->label;  ?></td>
				<td class="littletablerow" align='right'><?php echo $form->stotal->output; ?></td>
				<td class="littletableheader">           <?php echo $form->gtotal->label;  ?></td>
				<td class="littletablerow" align='right'><?php echo $form->gtotal->output; ?></td>
			</tr>
		</table>
		<?php echo $form_end; ?>
		</td>
	</tr>
</table>
<?php endif; ?>