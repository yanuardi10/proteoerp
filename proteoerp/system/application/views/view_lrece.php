<?php echo $form_scripts?>
<?php echo $form_begin?>
<?php
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$mod=true;
?>
<?php if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<table border=0 width="100%">
	<tr>
		<td colspan='2'>
			<fieldset style='border: 1px outset #9AC8DA;background: #FFFDE9;'>
			<table border=0 width="100%">
			<tr>
				<td class="littletableheaderc"><?php echo $form->id->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->id->output;?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?php echo $form->fecha->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->fecha->output;?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?php echo $form->ruta->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->ruta->output;?></td>
			</tr>
			</table>
			</fieldset>
		</td>
		<td>
			<fieldset style='border: 1px outset #9AC8DA;background: #FFFDE9;'>
			<legend>&nbsp;</legend>
			<table border=0 width="100%">
			<tr>
				<td class="littletableheaderc"><?php echo $form->chofer->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->chofer->output;?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?php echo $form->nombre->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->nombre->output;?></td>
			</tr>
			<tr>
				<td class="littletableheaderc"><?php echo $form->lleno->label; ?></td>
				<td class="littletablerow"    ><?php echo $form->lleno->output;?></td>
			</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>
<div style='border: 1px solid #9AC8DA;background: #FAFAFA'>
<table width='100%'>

	<?php for($i=0;$i<$form->max_rel_count['itlrece'];$i++) {

		$it_densidad   ="itdensidad_${i}";
		$it_lista      ="itlista_${i}";
		$it_animal     ="itanimal_${i}";
		$it_crios      ="itcrios_${i}";
		$it_h2o        ="ith2o_${i}";
		$it_temp       ="ittemp_${i}";
		$it_brix       ="itbrix_${i}";
		$it_grasa      ="itgrasa_${i}";
		$it_acidez     ="itacidez_${i}";
		$it_cloruros   ="itcloruros_${i}";
		$it_dtoagua    ="itdtoagua_${i}";
		$it_id_lvaca   ="itid_lvaca_${i}";
		$it_id_lrece   ="itid_lrece_${i}";
		$it_id         ="itid_${i}";
	?>

	<tr style="<?php if(!$mod) echo 'background:#E4E4E4'; else  echo ''; ?>">
		<td class="littletablerow" align="right"><?php echo $form->$it_id->output; ?>
			<label for='<?php echo $it_acidez;   ?>'><?php echo $form->$it_acidez->label;   ?></label><?php echo $form->$it_acidez->output;   ?><br>
			<label for='<?php echo $it_crios;    ?>'><?php echo $form->$it_crios->label;    ?></label><?php echo $form->$it_crios->output;    ?><br>
		</td>
		<td class="littletablerow" align="right">
			<label for='<?php echo $it_densidad; ?>'><?php echo $form->$it_densidad->label; ?></label><?php echo $form->$it_densidad->output; ?><br>
			<label for='<?php echo $it_lista;    ?>'><?php echo $form->$it_lista->label;    ?></label><?php echo $form->$it_lista->output;    ?><br>
		</td>
		<td class="littletablerow" align="right">
			<label for='<?php echo $it_h20;      ?>'><?php echo $form->$it_h2o->label;      ?></label><?php echo $form->$it_h2o->output;      ?><br>
			<label for='<?php echo $it_temp;     ?>'><?php echo $form->$it_temp->label;     ?></label><?php echo $form->$it_temp->output;     ?><br>
		</td>
		<td class="littletablerow" align="right">
			<label for='<?php echo $it_brix;     ?>'><?php echo $form->$it_brix->label;     ?></label><?php echo $form->$it_brix->output;     ?><br>
			<label for='<?php echo $it_grasa;    ?>'><?php echo $form->$it_grasa->label;    ?></label><?php echo $form->$it_grasa->output;    ?><br>
		</td>
		<td class="littletablerow" align="right">
			<label for='<?php echo $it_cloruros; ?>'><?php echo $form->$it_cloruros->label; ?></label><?php echo $form->$it_cloruros->output; ?><br>
			<label for='<?php echo $it_dtoagua;  ?>'><?php echo $form->$it_dtoagua->label;  ?></label><?php echo $form->$it_dtoagua->output;  ?><br>
		</td>
	</tr>
	<?php
	$mod=!$mod;
	} ?>
</table>
</div>

<?php echo $container_bl.$container_br; ?>
<?php echo $form_end; ?>
