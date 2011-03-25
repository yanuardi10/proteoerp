<?php

$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itconv');
$scampos  ='<tr id="tr_itconv_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['codigo']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['descrip']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['entrada']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['salida']['field'];
$scampos .= $campos['costo']['field'].'</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_itconv(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var itconv_cont=<?php echo $form->max_rel_count['itconv']; ?>;

$(function(){
	$(".inputnum").numeric(".");
});

function validaEnt(i){
	var entrada = Number($("#entrada_"+i).val());
	if(entrada>0)
		 $("#salida_"+i).val('0');
}

function validaSalida(i){
	var salida =Number($("#salida_"+i).val());
	if(salida>0)
		$("#entrada_"+i).val('0');
}

function add_itconv(){
	var htm = <?php echo $campos; ?>;
	can = itconv_cont.toString();
	con = (itconv_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#entrada_"+can).numeric(".");
	$("#salida_"+can).numeric(".");
	$("#costo_"+can).numeric(".");
	itconv_cont=itconv_cont+1;
}

function post_modbus_sinv(nind){
	ind=nind.toString();
}

function del_itconv(id){
	id = id.toString();
	$('#tr_itconv_'+id).remove();
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
				<th colspan='5' class="littletableheader">Conversiones <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->numero->output,8,0,0); ?></b></th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->fecha->label;    ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output;   ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->almacen->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->almacen->output  ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->observa1->label;  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->observa1->output; ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->observa2->label;  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->observa2->output; ?>&nbsp;</td>
			</tr>
		</table><br>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr>
				<th colspan='6' class="littletableheader">Lista de Art&iacute;culos</th>
			</tr>
			<tr>
				<td class="littletableheader">C&oacute;digo</td>
				<td class="littletableheader">Descripci&oacute;n</td>
				<td class="littletableheader">Entrada</td>
				<td class="littletableheader">Salida</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheader">&nbsp;</td>
				<?php } ?>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itconv'];$i++) {
				$it_codigo   = "codigo_$i";
				$it_descrip  = "descrip_$i";
				$it_salida   = "salida_$i";
				$it_entrada  = "entrada_$i";
				$it_costo    = "costo_$i";

				$pprecios = $form->$it_costo->output;
			?>

			<tr id='tr_itconv_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" ><?php echo $form->$it_codigo->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_descrip->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_entrada->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_salida->output.$pprecios;  ?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itconv(<?=$i ?>);return false;'>Eliminar</a>
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
	<?php echo $form_end; ?>
</table>
<?php endif; ?>