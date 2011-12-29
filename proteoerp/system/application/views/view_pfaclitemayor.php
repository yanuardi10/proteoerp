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
$dbcliente=$this->db->escape($form->cliente->value);
$nomcli=$this->datasis->dameval("SELECT nombre FROM scli WHERE cliente=$dbcliente");
?>
<?php echo $form->numero->value;
if($form->getstatus()!='show'){
?>
<script type="text/javascript">
$(function() {
	$('input[name^="preca_"]').each(function(){
		$("#"+this.name+'_val').text(nformat(this.value,2));
	});
	totaliza();
});

function cprecio(ind){
	var arr    = $('input:checkbox[name$="_'+ind+'"]');
	var costo  = Number($('#costo_'+ind).val());
	var mmargen= Number($('#sinvmmargen_'+ind).val());
	var precio = costo*(100+mmargen)/100;

	jQuery.each(arr, function() {
		if(this.checked){
			precio=precio*(1-Number(this.value)/100);
		}
	});
	precio=roundNumber(precio,2);
	$('#preca_'+ind).val(precio);
	$('#preca_'+ind+'_val').text(nformat(precio,2));
	importe(ind);
}

function cescala(ind){
	var escala1 = Number($('#escala1_'+ind).val());
	var escala2 = Number($('#escala2_'+ind).val());
	var escala3 = Number($('#escala3_'+ind).val());
	var pescala1= Number($('#pescala1_'+ind).val());
	var pescala2= Number($('#pescala2_'+ind).val());
	var pescala3= Number($('#pescala3_'+ind).val());
	var cana    = Number($('#cana_'+ind).val());
	var dxe     = $('#dxe_'+ind);

	if(cana >= escala3 && escala3>0){
		dxe.val(pescala3);
	}else if(cana >= escala2 && escala2>0){
		dxe.val(pescala2);
	}else if(cana >= escala1 && escala1>0){
		dxe.val(pescala1);
	}else{
		dxe.val(0);
	}
	cprecio(ind);
}

function importe(ind){
	var cana    = Number($('#cana_'+ind).val());
	var preca   = Number($('#preca_'+ind).val());
	var tota    = cana*preca;
	var escala1 = Number($('#escala1_'+ind).val());
	var escala2 = Number($('#escala2_'+ind).val());
	var escala3 = Number($('#escala3_'+ind).val());
	var dxe     = $('#escala_'+ind);

	if(cana < escala1 && escala1>0){
		dxe.text('+'+escala1.toString());
	}else if(cana < escala2 && escala2>0){
		dxe.text('+'+escala2.toString());
	}else if(cana < escala3 && escala3>0){
		dxe.text('+'+escala3.toString());
	}

	$('#tota_'+ind).val(tota);
	totaliza();
}

function totaliza(){
	var stota=0;
	var iva  =0;
	var piva =0;
	var ind  =0;
	var tota =0;
	var arr  = $('input[name^="tota_"]');

	jQuery.each(arr, function(){
		nom=this.name;
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind  = this.name.substring(pos+1);
			piva = Number($('#itiva_'+ind).val())/100;

			stota += Number(this.value);
			iva   += Number(this.value)*piva;
		}
	});
	$('#totals').val(roundNumber(stota,2));
	$('#iva').val(roundNumber(iva,2));
	$('#totalg').val(stota+iva);

	$('#totals_val').text(nformat(stota,2));
	$('#iva_val').text(nformat(iva,2));
	$('#totalg_val').text(nformat(stota+iva,2));
}
</script>
<?php } ?>
<table align='center' width="100%">
	<tr>
		<td><?php echo ucwords(strtolower($nomcli)); ?></td>
		<td align=right><?php echo $container_tr;?></td>
	</tr>
</table>

<table width='100%' align='center'>
	<col>
	<col class="colbg1">
	<col class="colbg1">
	<col class="colbg1">
	<col class="colbg1">
	<col class="colbg1">
	<col class="colbg1">
	<col>
	<col>
	<col class="colbg2">
	<thead>
		<tr>
			<td rowspan=2><b>Art&iacute;culo</b></td>
			<td colspan=6 align="center"><b>Descuentos</b></td>
			<td rowspan=2 align="right" ><b>Exis.</b></td>
			<td rowspan=2 align="center"><b>Cant.</b></td>
			<td rowspan=2 align="right" ><b>Precio</b></td>
		</tr>
		<tr>
			<td>DM</td>
			<td>DG</td>
			<td>DZ</td>
			<td>DC</td>
			<td>D+</td>
			<td>DE</td>
		</tr>
	</thead>
	<tbody>
	<?php
	$pmarcat='';
	for($i=0;$i<$cana;$i++) {
		$it_codigoa  = "codigoa_$i";
		$it_desca    = "desca_$i";
		$it_cana     = "cana_$i";
		$it_preca    = "preca_$i";
		$it_tota     = "tota_$i";
		$it_iva      = "itiva_$i";
		$it_costo    = "costo_$i";
		$it_mmargen  = "sinvmmargen_$i";
		$it_dxapli   = "dxapli_$i";
		$it_pond     = "itpond_$i";
		$it_ultimo   = "itultimo_$i";
		$it_formcal  = "itformcal_$i";
		$it_pm       = "itpm_$i";
		$it_precat   = "precat_$i";
		$it_pexisten = "pexisten_$i";
		$it_pmarca   = "pmarca_$i";
		$it_dxm      = "dxm_$i";
		$it_dxg      = "dxg_$i";
		$it_dxz      = "dxz_$i";
		$it_dxc      = "dxc_$i";
		$it_dxe      = "dxe_$i";
		$it_dxp      = "dxp_$i";
		$it_escala1  = "escala1_$i";
		$it_escala2  = "escala2_$i";
		$it_escala3  = "escala3_$i";
		$it_pescala1 = "pescala1_$i";
		$it_pescala2 = "pescala2_$i";
		$it_pescala3 = "pescala3_$i";

		if($form->getstatus()=='show' && $form->$it_cana->value<=0) continue;
		$pmarca=trim($form->$it_pmarca->value);
		if($pmarcat!=$pmarca){
			$pmarcat=$pmarca;
		?>
		<tr class='rowgroup'>
			<td colspan="11"><?php echo ucwords(strtolower($pmarca)); ?></td>
		</tr>
		<?php } ?>
	<tr id='tr_itpfac_<?php echo $i; ?>' <?php echo ($i%2 == 0) ? 'class="odd"' : '';?> >
		<td><p class='miniblanco'><?php echo $form->$it_codigoa->value;?></p>
			<?php echo $form->$it_desca->output.$form->$it_codigoa->output;   ?></td>
		<td><?php echo $form->$it_dxm->output; ?></td>
		<td><?php echo $form->$it_dxg->output; ?></td>
		<td><?php echo $form->$it_dxz->output; ?></td>
		<td><?php echo $form->$it_dxc->output; ?></td>
		<td><?php echo $form->$it_dxp->output; ?></td>
		<td><?php echo $form->$it_dxe->output; ?><b id='escala_<?php echo $i; ?>'></b></td>
		<td align="right"><?php echo nformat($form->$it_pexisten->value); ?></td>
		<td align="right"><?php echo $form->$it_cana->output; ?></td>
		<td align="right"><?php echo $form->$it_preca->output.$form->$it_mmargen->output.$form->$it_costo->output
			.$form->$it_escala1->output
			.$form->$it_escala2->output
			.$form->$it_escala3->output
			.$form->$it_pescala1->output
			.$form->$it_pescala2->output
			.$form->$it_pescala3->output
			.$form->$it_tota->output
			.$form->$it_iva->output; ?></td>
	</tr>
	<?php } ?>
	</tbody>
</table>

<table width='100%' style='backgroud-color:#FFFDE9' align='center'>
	<tr>
		<td><b><?php echo $form->observa->label;   ?></b></td>
		<td align='right'><b><?php echo $form->totals->label; ?></b></td>
		<td align='right'><?php echo $form->totals->output; ?></td>
	</tr><tr>
		<td><?php echo $form->observa->output;   ?></td>
		<td align='right'><b><?php echo $form->ivat->label;    ?></b></td>
		<td align='right'><?php echo $form->ivat->output; ?></td>
	</tr><tr>
		<td><?php echo $form->observ1->output; ?></td>
		<td align='right' ><b><?php echo $form->totalg->label;  ?></b></td>
		<td align='right' style='font-size:18px;font-weight: bold'><?php echo $form->totalg->output; ?></td>
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
