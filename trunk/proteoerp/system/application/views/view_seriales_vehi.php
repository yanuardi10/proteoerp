<?php
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

$max_rel=1;
echo $form_begin;
$ccodigo='';
$mod=false;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">

$(function(){
	$(".inputnum").numeric(".");

	$('input').keypress(function(e) {
		if(e.keyCode == 13) {
			return false;
		}
	});
});

</script>
<?php } ?>

<table width='100%' align='center'>
	<tr>
		<td>
			<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
			<table width="100%"  style="margin:0;width:100%;" cellspacing='2' cellpadding='2'>
				<tr>
					<td colspan="3">
						<table width="100%">
							<tr>
								<td class="littletablerowth" align='right' width='100'><?php echo $form->proveed->label  ?></td>
								<td class="littletablerow">  <?php echo $form->proveed->output ?><b id='nombre_val'><?php echo $form->nombre->value ?></b><?php echo $form->nombre->output ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style='border: 1px solid grey;'>
						<table width='100%'>
							<tr>
								<td class="littletablerowth"><?php echo $form->fecha->label  ?></td>
								<td class="littletablerow">  <?php echo $form->fecha->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->vence->label ?>*</td>
								<td class="littletablerow">  <?php echo $form->vence->output ?></td>
							</tr>
						</table>
					</td><td style='border: 1px solid grey;'>
						<table width='100%'>
							<tr>
								<td class="littletablerowth"><?php echo $form->cfis->label  ?></td>
								<td class="littletablerow">  <?php echo $form->cfis->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->serie->label  ?>*</td>
								<td class="littletablerow">  <?php echo $form->serie->output ?></td>
							</tr>
						</table>
					</td><td style='border: 1px solid grey;'>
						<table width='100%'>
							<tr>
								<td class="littletablerowth"><?php echo $form->almacen->label  ?></td>
								<td class="littletablerow">  <?php echo $form->almacen->output ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</fieldset>
		</tr>
	<tr>
</table>

<div style='border: 1px solid #9AC8DA;background: #FAFAFA'>
<table width='100%'>

	<?php for($i=0;$i<$form->max_rel_count['sinvehiculo'];$i++) {
		$it_codigo      = "codigo_${i}";
		$it_desca       = "modelo_${i}";
		$it_anio        = "anio_${i}";
		$it_color       = "color_${i}";
		$it_motor       = "motor_${i}";
		$it_carroceria  = "carroceria_${i}";
		$it_uso         = "uso_${i}";
		$it_tipo        = "tipo_${i}";
		$it_clase       = "clase_${i}";
		$it_transmision = "transmision_${i}";
		$it_peso        = "peso_${i}";
		$it_placa       = "placa_${i}";
		$it_precioplaca = "precioplaca_${i}";
		$it_base        = "base_${i}";
		$it_iva         = "iva_${i}";
		$it_id          = "idrel_${i}";
		$it_id_sfac     = "id_sfac_${i}";

	if($ccodigo != $form->$it_codigo->value){
		$ccodigo =$form->$it_codigo->value;
	?>
	<tr style='background:#7098D0' >
		<td class="littletablerow" colspan='5'  style="text-align:center;">
			<b><?php echo $form->$it_codigo->value.$form->$it_desca->value.$form->$it_desca->output; ?></b>
		</td>
	</tr>
	<?php } ?>

	<tr style="<?php if(!$mod) echo 'background:#E4E4E4'; else  echo ''; ?>">
		<td class="littletablerow" align="right"><?php echo $form->$it_codigo->output.$form->$it_desca->output.$form->$it_id->output; ?>
			<label for='<?php echo $it_anio;  ?>'>A&ntilde;o:</label><?php echo $form->$it_anio->output.$form->$it_id_sfac->output; ?><br>
			<label for='<?php echo $it_color; ?>'>Color:     </label><?php echo $form->$it_color->output; ?><br>
		</td>
		<td class="littletablerow" align="right">
			<label for='<?php echo $it_uso;   ?>'>Uso:       </label><?php echo $form->$it_uso->output;   ?><br>
			<label for='<?php echo $it_tipo;  ?>'>Tipo:      </label><?php echo $form->$it_tipo->output;  ?><br>
		</td>
		<td class="littletablerow" align="right">
			<label for='<?php echo $it_carroceria;  ?>'>Serial de Carroceria: </label><?php echo $form->$it_carroceria->output; ?><br>
			<label for='<?php echo $it_motor;       ?>'>Serial de Motor:      </label><?php echo $form->$it_motor->output;      ?><br>
		</td>
		<td class="littletablerow" align="right">
			<label for='<?php echo $it_clase;       ?>'>Clase:      </label><?php echo $form->$it_clase->output;      ?><br>
			<label for='<?php echo $it_transmision; ?>'>Transmision:</label><?php echo $form->$it_transmision->output; ?>
		</td>
		<td class="littletablerow" align="right">
			<label for='<?php echo $it_placa;       ?>'>Placa:       </label><?php echo $form->$it_placa->output;       ?><br>
			<label for='<?php echo $it_precioplaca; ?>'>Precio placa:</label><?php echo $form->$it_precioplaca->output; ?><br>
		</td>
	</tr>
	<?php
	$mod=!$mod;
	} ?>
</table>
</div>

<table  width="100%" style="margin:0;width:100%;" border='0'>
	<tr>
		<td class="littletablerowth" align='right'></td>
		<td class="littletablerow"   align='right'></td>
		<td class="littletablerowth" align='right'></td>
		<td class="littletablerow"   align='right'></td>
		<td class="littletablerowth" align='right'><?php echo $form->montonet->label; ?></td>
		<td class="littletablerow"   align='right'><b id='montonet_val' style='font-size:18px;font-weight: bold' ><?php echo nformat($form->montonet->value); ?></b><?php echo $form->montonet->output; ?></td>
	</tr>
</table>
<table width="100%">
	<tr>
		<td class="littletableheader" width="100"><?php echo $form->observa1->label;    ?></td>
		<td><?php echo $form->observa1->output;   ?></td>
	</tr>
</table>

<?php echo $form_end?>
<?php endif; ?>
