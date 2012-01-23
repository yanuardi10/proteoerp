<?php
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

	$html='<tr id="tr_itstra_<#i#>">';
	$campos=$form->template_details('itstra');
	foreach($campos as $nom=>$nan){
		$pivot=$nan['field'];
		$align = (strpos($pivot,'inputnum')) ? 'align="right"' : '';
		$html.='<td class="littletablerow" '.$align.'>'.$pivot.'</td>';
	}
	if($form->_status!='show') {
		$html.='<td class="littletablerow"><a href=# onclick=\'del_itordi(<#i#>);return false;\'>Eliminar</a></td>';
	}
	$html.='</tr>';


/*foreach($form->detail_fields['itstra'] AS $ind=>$data)
	$campos[]=$data['field'];
$campos='<tr id="tr_itstra_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';
$campos=str_replace("\n",'',$campos);
$campos.=' <td class="littletablerow"><a href=# onclick="del_itstra(<#i#>);return false;">Eliminar</a></td></tr>';*/
$campos=$form->js_escape($html);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){
?>

<script language="javascript" type="text/javascript">
itstra_cont=<?=$form->max_rel_count['itstra'] ?>;

function add_itstra(){
	var htm = <?php echo $campos; ?>;
	can = itstra_cont.toString();
	con = (itstra_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#cantidad_"+can).numeric(".");
	itstra_cont=itstra_cont+1;
}

$(function(){
	$(".inputnum").numeric(".");
});

function del_itstra(id){
	id = id.toString();
	$('#tr_itstra_'+id).remove();
}
</script>
<?php } ?>

<table align='center' width='100%'>
	<tr>
		<td align=right><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<th colspan='4' class="littletableheader">Transferencia <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->numero->output,8,0,0); ?></b></th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->envia->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->envia->output  ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->fecha->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output  ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->recibe->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->recibe->output ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->observ1->label  ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->observ1->output ?>&nbsp;</td>

			</tr>
		</table>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>
		<table width='100%'>
			<tr>
				<th colspan='4' class="littletableheader">Lista de Art&iacute;culos</th>
			</tr>
			<tr>
				<td class="littletableheader">C&oacute;digo</td>
				<td class="littletableheader">Descripci&oacute;n</td>
				<td class="littletableheader">Cantidad</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheader">&nbsp;</td>
				<?php } ?>
			</tr>
			<?php for($i=0;$i<$form->max_rel_count['itstra'];$i++) {
				$obj1="codigo_$i";
				$obj2="descrip_$i";
				$obj3="cantidad_$i";
			?>
			<tr id='tr_itstra_<?=$i ?>'>
				<td class="littletablerow"><?php echo $form->$obj1->output ?></td>
				<td class="littletablerow"><?php echo $form->$obj2->output ?></td>
				<td class="littletablerow"align="right"><?php echo $form->$obj3->output ?></td>
				<?php if($form->_status!='show') {?>
					<td class="littletablerow"><a href=#onclick='del_itstra(<?php echo $i; ?>);return false;'>Eliminar</a></td>
				<?php } ?>
			</tr>
			<?php } ?>

			<tr id='__UTPL__'>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<?php if($form->_status!='show') {?>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<?php } ?>
			</tr>
		</table>
		<?php echo $form_end ?> <?php echo $container_bl ?> <?php echo $container_br ?>
		</td>
	</tr>
</table>
<?php endif; ?>
