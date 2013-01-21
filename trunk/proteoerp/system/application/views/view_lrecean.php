<?php
echo $form_scripts;
echo $form_begin;

$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$mod=true;

if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';
?>
<script type="text/javascript">
$(function(){
	calconeto();
	calcolitro();
	descuagua()
});

function calcolitro(){
	var lleno=<?php echo $form->lleno->value ?>;
	var densidad=Number($("#densidad").val());
	var neto    =Number($("#neto").val());
	var vacio=Number($("#vacio").val());

	if ( densidad == '' ) {
		densidad = 1.016;
		$('#densidad').val(1.016);
		$('#densidad_val').val(1.016);
	}

	if ( vacio == 0 ) {
		$('#litros').val(neto);
		$('#litros_val').text(nformat(neto,2));
	} else {
		$('#litros').val(neto/densidad);
		$('#litros_val').text(nformat(neto/densidad,2));
	}
}

function calconeto(){
	var lleno=<?php echo $form->lleno->value ?>;
	var vacio=Number($("#vacio").val());
	var densidad=Number($("#densidad").val());
	var neto = 0;
	
	neto = lleno-vacio;

	if ( densidad == '' ) densidad = 1.016;

	$('#neto').val(lleno-vacio);
	$('#neto_val').text(nformat(lleno-vacio,2));

	if ( vacio == 0 ) {
		$('#litros').val(neto);
		$('#litros_val').text(nformat(neto,2));
	} else {
		$('#litros').val(neto/densidad);
		$('#litros_val').text(nformat(neto/densidad,2));
	}
}

function descuagua(){
	var h2o  = Number($("#h2o").val());
	var neto = Number($("#neto").val());

	if ( h2o == '' ){
		h2o = 0;
	}

	$('#dtoagua').val(neto*h2o/100);
	$('#dtoagua_val').text(nformat(neto*h2o/100,2));

}
</script>
<table width='100%' style='font-size:11pt;background:#F2E69D;'>
	<tr>
		<td                           width='60'>Numero:</td>
		<td style='font-weight:bold;' width='70'><?php echo str_pad(trim($form->numero->output),7,'0',STR_PAD_LEFT);    ?></td>
		<td                           width='60' align='right'>Fecha:</td>
		<td style='font-weight:bold;' width='90'><?php echo $form->fecha->output; ?></td>
	</tr>
	<tr>
		<td>Ruta:</td>
		<td colspan='3' style='font-weight:bold;' width='50' align='left'><?php echo $form->ruta->output;  ?>
		<span style='font-weight:bold;'><?php echo $this->datasis->dameval("SELECT nombre FROM lruta WHERE codigo='".$form->ruta->value."'");  ?></span></td>
	</tr>
</table>
<br />
<fieldset style='border: 1px outset #407E13;background: #FAFAFA;'>
<legend class='subtitulotabla'>Pesadas del Transporte</legend>
<table width='100%' >
	<tr>
		<td class="littletableheaderc">Lleno</td>
		<td class="littletableheaderc">Vacio</td>
		<td class="littletableheaderc">Neto</td>
		<td class="littletableheaderc">Densidad</td>
		<td class="littletableheaderc">Litros</td>
	</tr>
	<tr>
		<td class="littletablerow"><?php echo $form->lleno->output;?></td>
		<td class="littletablerow"><?php echo $form->vacio->output;?></td>
		<td class="littletablerow"><?php echo $form->neto->output; ?></td>
		<td class="littletablerow"><?php echo $form->densidad->output;?></td>
		<td class="littletablerow"><?php echo $form->litros->output;  ?></td>
	</tr>
</table>
</fieldset>

<div style='border: 1px solid #9AC8DA;background: #FAFAFA'>
<table width='100%' align='center'>
	<tr>
		<td class="littletableheaderc"><?php echo $form->temp->label;    ?></td>
		<td class="littletablerow"    ><?php echo $form->temp->output;   ?></td>
		<td class="littletableheaderc"></td>
		<td class="littletablerow"    ></td>
	</tr>
	<tr>
		<td class="littletableheaderc"><?php echo $form->animal->label;  ?></td>
		<td class="littletablerow"    ><?php echo $form->animal->output; ?></td>
		<td class="littletableheaderc"><?php echo $form->acidez->label;  ?></td>
		<td class="littletablerow"    ><?php echo $form->acidez->output; ?></td>
	</tr><tr>
		<td class="littletableheaderc"><?php echo $form->h2o->label;     ?></td>
		<td class="littletablerow"    ><?php echo $form->h2o->output;    ?></td>
		<td class="littletableheaderc"><?php echo $form->crios->label;   ?></td>
		<td class="littletablerow"    ><?php echo $form->crios->output;  ?></td>
	</tr><tr>
		<td class="littletableheaderc"><?php echo $form->brix->label;    ?></td>
		<td class="littletablerow"    ><?php echo $form->brix->output;   ?></td>
		<td class="littletableheaderc"><?php echo $form->grasa->label;   ?></td>
		<td class="littletablerow"    ><?php echo $form->grasa->output;  ?></td>
	</tr><tr>
		<td class="littletableheaderc"><?php echo $form->cloruros->label;?></td>
		<td class="littletablerow"    ><?php echo $form->cloruros->output;?></td>
		<td class="littletableheaderc"><?php echo $form->dtoagua->label; ?></td>
		<td class="littletablerow"    ><?php echo $form->dtoagua->output;?></td>
	</tr>
</table>
</div>

<?php echo $container_bl.$container_br; ?>
<?php echo $form_end; ?>
