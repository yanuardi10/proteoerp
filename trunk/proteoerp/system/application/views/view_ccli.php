<?php
ob_start('comprimir_pagina');

$container_bl=join("&nbsp;", $form->_button_container['BL']);
$container_br=join("&nbsp;", $form->_button_container['BR']);
$container_tr=join("&nbsp;", $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_begin;
$dbcliente=$this->db->escape($form->cod_cli->value);
$nomcli=$this->datasis->dameval("SELECT nombre FROM scli WHERE cliente=$dbcliente");

if($form->getstatus()!='show'){

	$sfpa_campos=$form->template_details('sfpa');
	$sfpa_scampos  ='<tr id="tr_sfpa_<#i#>">';
	$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['tipo']['field'].  '</td>';
	$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['numref']['field'].'</td>';
	$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['banco']['field']. '</td>';
	$sfpa_scampos .='<td class="littletablerow" align="right">'.$sfpa_campos['itmonto']['field'].'</td>';
	$sfpa_scampos .='<td class="littletablerow"><a href=# onclick="del_sfpa(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
	$sfpa_campos=$form->js_escape($sfpa_scampos);
?>
<script type="text/javascript">
var sfpa_cont=<?php echo $form->max_rel_count['sfpa'];?>;
$(function() {
	$(".inputnum").numeric(".");
	$('input[name^="abono_"]').keyup(function(){
		totaliza();
	});
	$('input[name^="abono_"]').focusout(function(){
		totaliza();
	});
	totaliza();
});

function totaliza(){
	var stota =0;
	var arr  = $('input[name^="abono_"]');

	jQuery.each(arr, function(){
		nom=this.name;
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind    = this.name.substring(pos+1);
			num    = Number(this.value);
			if(!isNaN(num)){
				stota += num;
			}else{
				this.value='0';
			}
		}
	});
	$('#monto').val(roundNumber(stota,2));
	$('#monto_val').text(nformat(stota,2));

	resto=faltante();
	utmo =$('input[id^="itmonto_"]').first();
	num  =Number(utmo.val());
	if(!isNaN(num)){
		hay = num
	}else{
		hay = 0;
		utmo.val('0');
	}

	utmo.val(roundNumber(hay+resto,2));
}

function add_sfpa(){
	var htm = <?php echo $sfpa_campos; ?>;
	can = sfpa_cont.toString();
	con = (sfpa_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__ITPL__sfpa").after(htm);
	falta = faltante();
	$("#itmonto_"+can).val(falta);
	sfpa_cont=sfpa_cont+1;
	return can;
}

function del_sfpa(id){
	id = id.toString();
	$('#tr_sfpa_'+id).remove();
	totaliza();
	var arr = $('input[id^="itmonto_"]');
	if(arr.length<=0){
		add_sfpa();
	}
}

//Totaliza el monto por pagar
function apagar(){
	var pago=0;
	jQuery.each($('input[id^="itmonto_"]'), function() {
		pago+=Number($(this).val());
	});
	if(isNaN(pago)) return 0; else return pago;
}

//Determina lo que falta por pagar
function faltante(){
	totalg=Number($("#monto").val());
	if(isNaN(totalg)){
		$("#monto").val('0');
		totalg=0;
	}
	paga  = apagar();
	resto = totalg-paga;
	return resto;
}
</script>
<?php } ?>
<table align='center' width="100%">
	<tr>
		<td colspan=3><?php echo $form->numero->value.$form->cod_cli->output ?></td>
		<td align=right><?php echo $container_tr;?></td>
	</tr>
	<tr>
		<td><?php echo $form->tipo_doc->label; ?></td>
		<td><?php echo $form->tipo_doc->output; ?></td>
		<td><?php echo $form->fecha->label; ?></td>
		<td><?php echo $form->fecha->output; ?></td>
	</tr>
</table>

<table width='100%' align='center'>
	<col>
	<col class="colbg1">
	<col class="colbg1">
	<col class="colbg1">
	<col class="colbg2">
	<thead>
		<tr>
			<td class="littletableheaderdet"><b>Documento</b></td>
			<td align="center" class="littletableheaderdet"><b>Fecha</b></td>
			<td align="right"  class="littletableheaderdet"><b>Monto</b></td>
			<td align="center" class="littletableheaderdet"><b>Saldo</b></td>
			<td align="right"  class="littletableheaderdet"><b>Abonar</b></td>
		</tr>
	</thead>
	<tbody>
	<?php
	$pmarcat='';
	for($i=0;$i<$cana;$i++) {
		$it_tipo_doc = "tipo_doc_$i";
		$it_numero   = "numero_$i";
		$it_fecha    = "fecha_$i";
		$it_monto    = "monto_$i";
		$it_abono    = "abono_$i";
		$it_saldo    = "saldo_$i";
	?>
	<tr id='tr_itccli_<?php echo $i; ?>' <?php echo ($i%2 == 0) ? 'class="odd"' : '';?> >
		<td><?php echo $form->$it_tipo_doc->output;?>-<?php echo $form->$it_numero->output;?></td>
		<td align="center"><?php echo $form->$it_fecha->output; ?></td>
		<td align="right"><?php echo $form->$it_monto->output; ?></td>
		<td align="right"><?php echo $form->$it_saldo->output; ?></td>
		<td align="right"><?php echo $form->$it_abono->output; ?></td>
	</tr>
	<?php } ?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan=4 align="right"><b><?php echo $form->monto->label; ?></b></td>
		<td align="right"><?php echo $form->monto->output; ?></td>
	</tr>
	</tfoot>
</table>

<?php echo $container_br.$container_bl;?>
<table width='100%'>
	<tr id='__ITPL__sfpa'>
		<td class="littletableheaderdet">Tipo<ds/td>
		<td class="littletableheaderdet">N&uacute;mero</td>
		<td class="littletableheaderdet">Banco</td>
		<td class="littletableheaderdet">Monto</td>
		<?php if($form->_status!='show') {?>
			<td class="littletableheaderdet"></td>
		<?php } ?>
	</tr>
	<?php

	for($i=0; $i < $form->max_rel_count['sfpa']; $i++) {
		$tipo   = "tipo_$i";
		$numref = "numref_$i";
		$monto  = "itmonto_$i";
		$banco  = "banco_$i";
	?>
	<tr id='tr_sfpa_<?php echo $i; ?>'>
		<td class="littletablerow" nowrap><?php echo $form->$tipo->output   ?></td>
		<td class="littletablerow">       <?php echo $form->$numref->output ?></td>
		<td class="littletablerow">       <?php echo $form->$banco->output  ?></td>
		<td class="littletablerow" align="right"><?php echo $form->$monto->output ?></td>
		<?php if($form->_status!='show') {?>
			<td class="littletablerow"><a href=# onclick="del_sfpa(<?php echo $i; ?>);return false;"><?php echo img("images/delete.jpg"); ?></a></td></tr>
		<?php } ?>
	</tr>
	<?php } ?>
	<tr id='__UTPL__sfpa'>
		<td colspan='9' class="littletableheaderdet">&nbsp;</td>
	</tr>
</table>
<?php echo $form_end; ?>

<?php endif; ?>
<?php
ob_end_flush();

// Función para eliminar todos los espacios en blanco
function comprimir_pagina($buffer) {
    $busca = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
    $reemplaza = array('>','<','\\1');
    return preg_replace($busca, $reemplaza, $buffer);
}
?>
