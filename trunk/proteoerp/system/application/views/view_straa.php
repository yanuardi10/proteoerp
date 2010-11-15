<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' OR $form->_action=='delete'):
echo $form->output;
else:
//$link=site_url('presupuesto/requisicion/getadmin');

foreach($form->detail_fields['itstra'] AS $ind=>$data)
	$campos[]=$data['field'];

$campos='<tr id="tr_itstra_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';
$campos=str_replace("\n",'',$campos);
$campos.=' <td class="littletablerow"><a href=# onclick="del_itstra(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);
if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){
	?>

<script language="javascript" type="text/javascript">
itstra_cont=<?=$form->max_rel_count['itstra'] ?>;

function add_itstra(){
	var htm = <?=$campos ?>;
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

<table align='center' width="80%">
	<tr>
		<td align=right><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td colspan=6 class="littletablerow">Traslado Nro. <?php  echo str_pad(trim($form->numero->output),8,0,STR_PAD_LEFT) ?></td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->envia->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->envia->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->recibe->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->recibe->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->fecha->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->fecha->output ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->observ1->label  ?>&nbsp;</td>
				<td class="littletablerow"><?=$form->observ1->output ?>&nbsp;</td>
				<?php if($form->_status=='show'){?>
				<td class="littletableheader"><?=$form->totalg->label  ?>&nbsp;</td>
				<td class="littletablerow" align="right"><?=$form->totalg->output ?>&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<?php }else{?>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<?php }?>
			</tr>

		</table>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>	
		<table width='100%'>
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
				<td class="littletablerow"><?=$form->$obj1->output ?></td>
				<td class="littletablerow"><?=$form->$obj2->output ?></td>
				<td class="littletablerow"align="right"><?=$form->$obj3->output ?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow"><a href=#
					onclick='del_itstra(<?=$i ?>);return false;'>Eliminar</a></td>
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
		
		<?php echo $form_end     ?> <?php echo $container_bl ?> <?php echo $container_br ?>
		</td>
	</tr>
		
</table>

			<?php endif; ?>