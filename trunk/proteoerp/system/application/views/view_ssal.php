<?php

$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itssal');
$scampos  ='<tr id="tr_itssal_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['codigo']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['itdescrip']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['cantidad']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['costo']['field']. '</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_itssal(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var itssal_cont=<?php echo $form->max_rel_count['itssal']; ?>;

$(function(){
	$(".inputnum").numeric(".");
});
function add_itssal(){
	var htm = <?php echo $campos; ?>;
	can = itssal_cont.toString();
	con = (itssal_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#cantidad_"+can).numeric(".");
	itssal_cont=itssal_cont+1;
}
function del_itssal(id){
	id = id.toString();
	$('#tr_itssal_'+id).remove();
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
				<th colspan='5' class="littletableheader">N&uacute;mero <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->numero->output,8,0,0); ?></b></th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->fecha->label;    ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output;   ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->almacen->label;  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->almacen->output,$form->caububides->output; ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->tipo->label;  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->tipo->output; ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->descrip->label; ?>&nbsp;</td>
				<td class="littletablerow"   ><?php echo $form->descrip->output;   ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->cargo->label;  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->cargo->output; ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->motivo->label; ?>&nbsp;</td>
				<td class="littletablerow"   ><?php echo $form->motivo->output;   ?>&nbsp;</td>
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
				<td class="littletableheader">Cantidad</td>
				<td class="littletableheader">Costo</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheader">&nbsp;</td>
				<?php } ?>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itssal'];$i++) {
				$it_codigo  = "codigo_$i";
				$it_descrip   = "itdescrip_$i";
				$it_cant    = "cantidad_$i";
				$it_costo   = "costo_$i";
			?>

			<tr id='tr_itssal_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" ><?php echo $form->$it_codigo->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_descrip->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_cant->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_costo->output;  ?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itssal(<?=$i ?>);return false;'>Eliminar</a>
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
		<?php echo $form_end; ?>
	</tr>
</table>
<?php endif; ?>
