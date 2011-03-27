<?php
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itcasi');
$scampos  ='<tr id="tr_itcasi_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['cuenta']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['referen']['field']. '</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['concepto']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['itdebe']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['ithaber']['field']. '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['itccosto']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['itsucursal']['field'];
//$scampos .= $campos['itccosto']['field'];
//$scampos .= $campos['itsucursal']['field'];
$scampos .= $campos['cpladeparta']['field'];
$scampos .= $campos['cplaccosto']['field'].'</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_itcasi(<#i#>);return false;">Eliminar</a></td></tr>';
$jscampos=$form->js_escape($scampos);
$jscosto =$form->js_escape($campos['itccosto']['field']);
$jssucus =$form->js_escape($campos['itsucursal']['field']);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type='text/javascript'>
var itcasi_cont=<?php echo $form->max_rel_count['itcasi']; ?>;

$(function(){
	$(".inputnum").numeric(".");
	totaliza();
	
	
	var arr=$('input[name^="cpladeparta_"]');
	jQuery.each(arr, function() {
		var departa=this.value;
		nom=this.name
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind     = this.name.substring(pos+1);
			if(departa!='S'){
				$("#itccosto_"+ind).empty();
				$("#itsucursal_"+ind).empty();
				$("#itccosto_"+ind).append($('<option></option>').val('').html('No aplica'));
				$("#itsucursal_"+ind).append($('<option></option>').val('').html('No aplica'));
			}
		}
	});
	
});

function validaDebe(i){
	var debe = Number($("#itdebe_"+i).val());
	if(debe>0)
		 $("#ithaber_"+i).val('0');
	totaliza();
}

function validaHaber(i){
	var haber =Number($("#ithaber_"+i).val());
	if(haber>0)
		$("#itdebe_"+i).val('0');
	totaliza();
}

function add_itcasi(){
	var htm = <?php echo $jscampos; ?>;
	can = itcasi_cont.toString();
	con = (itcasi_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#itdebe_"+can).numeric(".");
	$("#ithaber_"+can).numeric(".");
	post_modbus(itcasi_cont);
	itcasi_cont=itcasi_cont+1;
}

function totaliza(){
	var debe=0;
	var haber=0;
	var arr=$('input[name^="itdebe_"]');
	jQuery.each(arr, function() {
		nom=this.name
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind     = this.name.substring(pos+1);
			ithaber = Number($("#ithaber_"+ind).val());
			itdebe  = Number(this.value);

			haber = haber + ithaber;
			debe  = debe + itdebe;
			//post_modbus(Number(ind));
		}
	});
	$("#debe").val(roundNumber(debe,2));
	$("#haber").val(roundNumber(haber,2));
	$("#total").val(roundNumber(debe-haber,2));
}

function del_itcasi(id){
	id = id.toString();
	$('#tr_itcasi_'+id).remove();
	totaliza();
}

function post_modbus(nind){
	var ind=nind.toString();
	var departa=$("#cpladeparta_"+ind).val();
	if(departa=='S'){
		var jscosto=<?php echo $jscosto; ?>;
		var jssucus=<?php echo $jssucus; ?>;
		$("#itccosto_"+ind).replaceWith(jscosto.replace(/<#i#>/g,ind));
		$("#itsucursal_"+ind).replaceWith(jssucus.replace(/<#i#>/g,ind));
	}else{
		$("#itccosto_"+ind).empty();
		$("#itsucursal_"+ind).empty();
		$("#itccosto_"+ind).append($('<option></option>').val('').html('No aplica'));
		$("#itsucursal_"+ind).append($('<option></option>').val('').html('No aplica'));
	}
}

</script>
<?php } ?>

<table align='center' width="100%">
	<tr>
		<td align=right><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<th colspan='5' class="littletableheader">Asientos <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->comprob->output,8,0,0); ?></b></th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->fecha->label;   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output;  ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->status->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->status->output  ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->descrip->label;  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->descrip->output; ?>&nbsp;</td>
				<td class="littletableheader">&nbsp;</td>
				<td class="littletablerow">   &nbsp;</td>
			</tr>
		</table><br>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr>
				<th colspan='8' class="littletableheader">Lista de Asientos</th>
			</tr>
			<tr>
				<td class="littletableheader">Cuenta</td>
				<td class="littletableheader">Referencia</td>
				<td class="littletableheader">Concepto</td>
				<td class="littletableheader">Debe</td>
				<td class="littletableheader">Haber</td>
				<td class="littletableheader">Centro de Costo</td>
				<td class="littletableheader">Sucursal</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheader">&nbsp;</td>
				<?php } ?>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itcasi'];$i++) {
				$it_cuenta     = "cuenta_$i";
				$it_concepto   = "concepto_$i";
				$it_referen    = "referen_$i";
				$it_debe       = "itdebe_$i";
				$it_haber      = "ithaber_$i";
				$it_ccosto     = "itccosto_$i";
				$it_sucursal   = "itsucursal_$i";
				$it_cplaccosto = "cplaccosto_$i";
				$it_cpladeparta= "cpladeparta_$i";

				$pprecios = $form->$it_cplaccosto->output;
				$pprecios .= $form->$it_cpladeparta->output;
			?>

			<tr id='tr_itcasi_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" nowrap><?php echo $form->$it_cuenta->output;  ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_referen->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_concepto->output;?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_debe->output;    ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_haber->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_ccosto->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_sucursal->output; echo $pprecios; ?></td>
				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itcasi(<?php echo $i ?>);return false;'>Eliminar</a>
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
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr>
				<th colspan='6' class="littletableheader">Res&uacute;men</th>
			</tr>
			<tr>
				<td class="littletableheader">           <?php echo $form->debe->label;   ?></td>
				<td class="littletablerow" align='right'><?php echo $form->debe->output;  ?></td>
				<td class="littletableheader">           <?php echo $form->haber->label;  ?></td>
				<td class="littletablerow" align='right'><?php echo $form->haber->output; ?></td>
				<td class="littletableheader">           <?php echo $form->total->label;  ?></td>
				<td class="littletablerow" align='right'><?php echo $form->total->output; ?></td>
			</tr>
		</table>
		<?php echo $form_end; ?>
		</td>
	</tr>
</table>
<?php endif; ?>