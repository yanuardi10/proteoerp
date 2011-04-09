<?php

$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
$(function(){
	$("#gastos").hide();
	$("#ingresos").hide();
	$("#gastode").attr("disabled","disabled");
	$("#ingresod").attr("disabled","disabled");
	$("#tipo").change(function(){
		var tipo=$("#tipo").val();
		if(tipo=="I"){
			$("#gastode").attr("disabled","disabled");
			$("#ingresod").attr("disabled","");
			$("#gastos").hide();
			$("#ingresos").show();
			$("#gastode").val("");
			$("#gasto").val("");
		}else if(tipo=="E"){
			$("#gastode").attr("disabled","");
			$("#ingresod").attr("disabled","disabled");
			$("#gastos").show();
			$("#ingresos").hide();
			$("#ingresod").val("");
			$("#ingreso").val("");
		}else{
			$("#gastos").hide();
			$("#ingresos").hide();
			$("#gastode").attr("disabled","disabled");
			$("#ingresod").attr("disabled","disabled");
			$("#ingresod").val("");
			$("#ingreso").val("");
			$("#gastode").val("");
			$("#gasto").val("");
		}
	});

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
				<th colspan='5' class="littletableheader">Conceptos de Inventario <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->codigo->output,8,0,0); ?></b></th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->codigo->label;    ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->codigo->output;   ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->concepto->label;  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->concepto->output; ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->tipo->label;  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->tipo->output; ?>&nbsp;</td>
			</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->gasto->label;  ?>&nbsp;</td>
					<td class="littletablerow"><div id="gastos">   <?php echo $form->gasto->output; ?>&nbsp;</div></td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->gastode->label;  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->gastode->output; ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->ingreso->label;  ?>&nbsp;</td>
					<td class="littletablerow"><div id="ingresos">   <?php echo $form->ingreso->output; ?>&nbsp;</div></td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->ingresod->label;  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->ingresod->output; ?>&nbsp;</td>
				</tr>
			
			
		</table><br>
		</td>
	</tr>
	<tr>
		<td>
		<?php echo $container_bl ?>
		<?php echo $container_br ?>
		</td>
	</tr>
</table>
<?php endif; ?>
