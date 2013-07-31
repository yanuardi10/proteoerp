<?php
ob_start('comprimir_pagina');

$container_bl = join('&nbsp;', $form->_button_container['BL']);
$container_br = join('&nbsp;', $form->_button_container['BR']);
$container_tr = join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_begin;
$dbproveed=$this->db->escape($form->cod_prv->value);
$nomprv=$this->datasis->dameval("SELECT nombre FROM sprv WHERE proveed=${dbproveed}");

if($form->getstatus()!='show'){

	//$sfpa_campos=$form->template_details('sfpa');
	//$sfpa_scampos  ='<tr id="tr_sfpa_<#i#>">';
	//$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['tipo']['field'].  '</td>';
	//$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['sfpafecha']['field'].  '</td>';
	//$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['numref']['field'].'</td>';
	//$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['banco']['field']. '</td>';
	//$sfpa_scampos .='<td class="littletablerow" align="right">'.$sfpa_campos['itmonto']['field'].'</td>';
	//$sfpa_scampos .='<td class="littletablerow"><a href=# onclick="del_sfpa(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
	//$sfpa_campos=$form->js_escape($sfpa_scampos);
    //
	$sfpade=$sfpach="<option value=''>Ninguno</option>";
	$mSQL="SELECT cod_banc,nomb_banc FROM tban WHERE cod_banc<>'CAJ'";
	$query = $this->db->query($mSQL);
	foreach ($query->result() as $row){
		$sfpach.="<option value='".trim($row->cod_banc)."'>".trim($row->nomb_banc)."</option>";
	}
	$mSQL="SELECT codbanc AS cod_banc,CONCAT_WS(' ',TRIM(banco),numcuent) AS nomb_banc FROM banc WHERE tbanco <> 'CAJ' ORDER BY nomb_banc";
	$query = $this->db->query($mSQL);
	foreach ($query->result() as $row){
		$sfpade.="<option value='".trim($row->cod_banc)."'>".trim($row->nomb_banc)."</option>";
	}
?>
<script type="text/javascript">
var sfpa_cont=0<?php //echo $form->max_rel_count['sfpa'];?>;
$(function() {
	$(".inputnum").numeric(".");
	$('input[name^="abono_"]').keyup(function(){
		totaliza();
	});
	$('input[name^="abono_"]').focusout(function(){
		totaliza();
	});
	totaliza();
	$('form').submit(function() {
		var r=confirm("Confirma guardar las transacciones?");
		return r;
	});

	$('#fecdoc').datepicker({ dateFormat: "dd/mm/yy" });
	$('input[name^="sfpafecha_"]').datepicker({ dateFormat: "dd/mm/yy" });
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
	var htm = ''<?php //echo $sfpa_campos; ?>;
	can = sfpa_cont.toString();
	con = (sfpa_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__ITPL__sfpa").after(htm);
	falta = faltante();
	$("#itmonto_"+can).val(falta);
	$("#sfpafecha_"+can).datepicker({ dateFormat: "dd/mm/yy" });
	sfpa_cont=sfpa_cont+1;
	return can;
}

function itsaldo(obj,saldo){
	if(obj.value.length==0){
		obj.value=saldo;
		totaliza();
	}
}

function itppago(obj,ind){
	var monto=0;
	var valor=Number(obj.value);
	var nval=0;

	if(valor==NaN){
		obj.value='0';
	}else if(valor<0){
		monto=Number($('#abono_'+ind).val());
		nval=monto*valor*-1/100;
		obj.value=roundNumber(nval,2);
		$('#abono_'+ind).val(roundNumber(monto-nval,2));
		totaliza();
	}
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

function sfpatipo(id){
	id     = id.toString();
	tipo   = $("#tipo_"+id).val();
	sfpade = <?php echo $form->js_escape($sfpade); ?>;
	sfpach = <?php echo $form->js_escape($sfpach); ?>;
	banco  = $("#banco_"+id).val();
	if(tipo=='DE' || tipo=='NC'){
		$("#banco_"+id).html(sfpade);
	}else{
		$("#banco_"+id).html(sfpach);
	}
	$("#banco_"+id).val(banco);
	return true;
}
</script>
<?php } ?>
<?php
echo $title;
?>
<table align='center' width="100%">
	<tr>
		<td colspan='3'><?php echo $form->numero->value.$form->cod_prv->output ?></td>
		<td align=right><?php echo $container_tr;?></td>
	</tr>
	<tr>
		<td><?php echo $form->tipo_doc->label;  ?></td>
		<td><?php echo $form->tipo_doc->output; ?></td>
		<td><?php echo $form->fecdoc->label;    ?></td>
		<td><?php echo $form->fecdoc->output;   ?></td>
	</tr>
</table>
<?php if($cana>0){ ?>
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
			<td align="right"  class="littletableheaderdet"><b>P.Pago</b></td>
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
		$it_ppago    = "ppago_$i";
	?>
	<tr id='tr_itccli_<?php echo $i; ?>' <?php echo ($i%2 == 0) ? 'class="odd"' : '';?> >
		<td><?php echo $form->$it_tipo_doc->output;?>-<?php echo $form->$it_numero->output;?></td>
		<td align="center"><?php echo $form->$it_fecha->output; ?></td>
		<td align="right"><?php echo $form->$it_monto->output; ?></td>
		<td align="right"><?php echo $form->$it_saldo->output; ?></td>
		<td align="right"><?php echo $form->$it_abono->output; ?></td>
		<td align="right"><?php echo $form->$it_ppago->output; ?></td>
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
<?php } ?>

<?php echo $container_br.$container_bl;?>

<table align='center' width="100%">
	<tr>
		<td><?php echo $form->banco->label;    ?></td>
		<td><?php echo $form->banco->output;   ?></td>
		<td><?php echo $form->tipo_op->label;  ?></td>
		<td><?php echo $form->tipo_op->output; ?></td>
		<td><?php echo $form->numche->label;   ?></td>
		<td><?php echo $form->numche->output;  ?></td>
		<td><?php echo $form->fecha->label;    ?></td>
		<td><?php echo $form->fecha->output;   ?></td>
	</tr>
	<tr>
		<td><?php echo $form->benefi->label ?></td>
		<td colspan='3'><?php echo $form->benefi->output ?></td>
	</tr>
</table>
<table width='100%'>
	<tr>
		<td colspan='9' align='center'><b>Concepto:</b><br><?php echo $form->observa1->output.$form->observa2->output; ?></td>
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