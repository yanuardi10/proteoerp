<?php echo $form_scripts?>
<?php echo $form_begin?>
<?php
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$mod=true;

if($form->_status!='show'){
?>

<script language="javascript" type="text/javascript">

$(function(){
	$("#fecha").datepicker({ dateFormat: "dd/mm/yy" });
	$(".inputnum").numeric(".");
});

function totalizar(){
	var litros = Number($('#inventario').val());

	var arr=$('input[name^="itlitros_"]');
	jQuery.each(arr, function() {
		nom=this.name
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			litros = litros+Number(Math.abs(this.value));
		}
	});

	$("#litros").val(roundNumber(litros,2));
	$("#litros_val").text(nformat(litros,2));
}

</script>
<?php } ?>


<?php if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<table width='100%' style='font-size:11pt;background:#F2E69D;'>
	<tr>
		<td width='60'><b><?php echo $form->dia->label;     ?></b></td>
		<td width='70'>   <?php echo $form->dia->output;    ?></td>
		<td width='50' colspan='2'></td>
	</tr>
	<tr>
		<td width='60'><b><?php echo $form->fecha->label;     ?></b></td>
		<td width='70'>   <?php echo $form->fecha->output;    ?></td>
		<td width='90'><b><?php echo $form->requeson->label; ?></b></td>
		<td width='50'>   <?php echo $form->requeson->output; ?></td>
	</tr>
</table>
<div style='border: 1px solid #9AC8DA;background: #FAFAFA'>
<table width='100%' cellspacing='0' cellpadding='0'>
	<tr style='background:#030B7A;color:#FDFDFD;font-size:10pt;'>
		<th align="center">C&oacute;digo</th>
		<th align="center">Descripci&oacute;n</th>
		<th align="center">Unidades</th>
		<th align="center">Cestas</th>
		<th align="center"><?php
			$it_peso = 'itpeso_0';
			echo $form->$it_peso->label;
		?></th>
	</tr>

<?php
	for($i=0;$i<$max_rel_count;$i++) {
		$it_codigo   ='itcodigo_'.$i;
		$it_descrip  ='itdescrip_'.$i;
		$it_cestas   ='itcestas_'.$i;
		$it_unidades ='itunidades_'.$i;
		$it_peso     ='itpeso_'.$i;
?>
	<tr id='tr_itlprod_<?php echo $i; ?>'>
		<td class="littletablerow" align="center"><?php echo $form->$it_codigo->output;    ?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_descrip->output;   ?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_unidades->output;  ?></td>
		<td class="littletablerow" align="center"><?php echo $form->$it_cestas->output;    ?></td>
		<td class="littletablerow" align="center"><?php if(isset($form->$it_peso)) echo $form->$it_peso->output;      ?></td>
	</tr>
	<?php
	$mod=!$mod;
	} ?>
	<tr id='__UTPL__lcierre'>
		<td colspan='5' class="littletableheaderdet">&nbsp;</td>
	</tr>
</table>
</div>

<?php echo $container_bl.$container_br; ?>
<?php echo $form_end; ?>
