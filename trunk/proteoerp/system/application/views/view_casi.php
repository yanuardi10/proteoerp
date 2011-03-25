<?php
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itcasi');
$scampos  ='<tr id="tr_itcasi_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['cuenta']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['concepto']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['referen']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['debe']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['haber']['field'];
$scampos .= $campos['ccosto']['field'];
$scampos .= $campos['cplaccosto']['field'].'</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_itcasi(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var itcasi_cont=<?php echo $form->max_rel_count['itcasi']; ?>;

$(function(){
	$(".inputnum").numeric(".");
});

function validaDebe(i){
	var debe = Number($("#debe_"+i).val());
	if(debe>0)
		 $("#haber_"+i).val('0');
	$("#debe_"+i).val(debe);
}

function validaHaber(i){
	var haber =Number($("#haber_"+i).val());
	if(haber>0)
		$("#debe_"+i).val('0');
	$("#haber_"+i).val(haber)
}

function add_itcasi(){
	var htm = <?php echo $campos; ?>;
	can = itcasi_cont.toString();
	con = (itcasi_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#debe_"+can).numeric(".");
	$("#haber_"+can).numeric(".");
	itcasi_cont=itcasi_cont+1;
}


function del_itcasi(id){
	id = id.toString();
	$('#tr_itcasi_'+id).remove();
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
				<th colspan='5' class="littletableheader">Asientos <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->comprob->output,8,0,0); ?></b></th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->fecha->label;    ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output;   ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->status->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->status->output  ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->descrip->label;  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->descrip->output; ?>&nbsp;</td>
			</tr>
		</table><br>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr>
				<th colspan='6' class="littletableheader">Lista de Asientos</th>
			</tr>
			<tr>
				<td class="littletableheader">Cuenta</td>
				<td class="littletableheader">Concepto</td>
				<td class="littletableheader">Referencia</td>
				<td class="littletableheader">Debe</td>
				<td class="littletableheader">Haber</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheader">&nbsp;</td>
				<?php } ?>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itcasi'];$i++) {
				$it_cuenta   = "cuenta_$i";
				$it_cocepto  = "concepto_$i";
				$it_referen  = "referen_$i";
				$it_debe     = "debe_$i";
				$it_haber    = "haber_$i";

				$pprecios = $form->$it_ccosto->output;
				$pprecios = $form->$it_cplaccosto->output;
			?>

			<tr id='tr_itconv_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" ><?php echo $form->$it_cuenta->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_concepto->output;  ?></td>
				<td class="littletablerow" align="left"><?php echo $form->$it_referen->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_debe->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_haber->output.$pprecios;  ?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itcasi(<?=$i ?>);return false;'>Eliminar</a>
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
				<td class="littletableheader">           <?php echo $form->debe->label;    ?></td>
				<td class="littletablerow" align='right'><?php echo $form->debe->output;   ?></td>
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