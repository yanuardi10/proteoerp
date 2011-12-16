<?php

$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itcruc');
$scampos  ='<tr id="tr_itcruc_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['ittipo']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['onumero']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['ofecha']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['oregist']['field']. '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['itmonto']['field'].'</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_itcruc(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var itcruc_cont=<?php echo $form->max_rel_count['itcruc']; ?>;

$(function(){
	$(".inputnum").numeric(".");
});


function add_itcruc(){
	var htm = <?php echo $campos; ?>;
	can = itcruc_cont.toString();
	con = (itcruc_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	itcruc_cont=itcruc_cont+1;
}
function del_itcruc(id){
	id = id.toString();
	$('#tr_itcruc_'+id).remove();
}
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
				<th colspan='5' class="littletableheader">Cruce de Cuentas: <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->numero->output,8,0,0); ?></b></th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->fecha->label;    ?>*&nbsp;</td>
				<td class="littletablerow"   ><?php echo $form->fecha->output;   ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->tipo->label;  ?>&nbsp;</td>
				<td class="littletablerow"   ><?php echo $form->tipo->output; ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->proveed->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->proveed->output    ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->nombre->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->nombre->output    ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->saldoa->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->saldoa->output    ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->cliente->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->cliente->output    ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->nomcli->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->nomcli->output    ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->saldod->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->saldod->output    ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->codbanc->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->codbanc->output    ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->monto->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->monto->output    ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->concept1->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->concept1->output    ?>&nbsp;</td>
			</tr>
		</table><br>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr>
				<th colspan='6' class="littletableheader">Detalles</th>
			</tr>
			<tr>
				<td class="littletableheader">Tipo</td>
				<td class="littletableheader">O.Numero</td>
				<td class="littletableheader">O.Fecha</td>
				<td class="littletableheader">Monto</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheader">&nbsp;</td>
				<?php } ?>
			</tr>

			<?php
			for($i=0;$i<$form->max_rel_count['itcruc'];$i++) {
				$it_tipo  = "ittipo_$i";
				$onumero   = "onumero_$i";
				$ofecha    = "ofecha_$i";
				$itmonto = "itmonto_$i";
			?>

			<tr id='tr_itcruc_<?php echo $i; ?>'>
				<td class="littletablerow" align="center" ><?php echo $form->$it_tipo->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$onumero->output;  ?></td>
				<td class="littletablerow" align="left"><?php echo $form->$ofecha->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$itmonto->output;?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itcruc(<?=$i ?>);return false;'>Eliminar</a>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>

			<tr id='__UTPL__'>
				<td id='cueca'></td>
			</tr>
		</table>
		<?php echo $container_bl ?>
		<?php echo $container_br ?>
		</td>
		<?php echo $form_end; ?>
	</tr>
</table>
<?php endif; ?>