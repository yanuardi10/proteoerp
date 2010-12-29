<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' OR $form->_action=='delete'):
echo $form->output;
else:


foreach($form->detail_fields['itspre'] AS $ind=>$data)
$campos[]=$data['field'];

$campos='<tr id="tr_itspre_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';

$campos=str_replace("\n",'',$campos);
$campos.=' <td class="littletablerow"><a href=# onclick="del_itspre(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);
if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){
	?>
<input type="hidden" name="__p1"
	id="__p1" value="">
<input type="hidden" name="__p2"
	id="__p2" value="">
<input type="hidden" name="__p3"
	id="__p3" value="">
<input type="hidden" name="__p4"
	id="__p4" value="">
<input type="hidden"
	name="t_cli" id="t_cli" value="">


<script language="javascript" type="text/javascript">
itspre_cont=<?=$form->max_rel_count['itspre']?>;
function ejecuta(i){
	tipo=$("#t_cli").val();
	if(tipo=='' || tipo=='0')tipo='1';
	precio=$("#__p"+tipo.toString()).val();
	$("#preca_"+i.toString()).val(precio);
	p4=$("#__p4").val();
	$("#precio4_"+i.toString()).val(p4);
}
function totalizar(i){
	c=roundNumber($("#cana_"+i.toString()).val(),2);
	p=roundNumber($("#preca_"+i.toString()).val(),2);
	iva=$("#iva_"+i.toString()).val();
	t=roundNumber(c*p*(1+iva/100),2);
	$("#totaorg_"+i.toString()).val(t);	
}

function v_preca(i){
	precio=$("#preca_"+i.toString()).val();
	precio4=$("#precio4_"+i.toString()).val();
	if (precio < precio4){
		alert("El precio minimo es precio4="+precio4);
		$("#preca_"+i.toString()).val(precio4);
	}
	totalizar(i);
}

function add_itspre(){
	var htm = <?=$campos ?>;
	can = itspre_cont.toString();
	con = (itspre_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#cana_"+can).numeric(".");
	itspre_cont=itspre_cont+1;
}

$(function(){
	$(".inputnum").numeric(".");
});
					
function del_itspre(id){
	id = id.toString();
	$('#tr_itspre_'+id).remove();
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
				<td class="littletableheader">N&uacute;mero</td>
				<td class="littletablerow"><?php  echo str_pad(trim($form->numero->output),8,0,STR_PAD_LEFT) ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->cliente->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->cliente->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->rifci->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->rifci->output ?>&nbsp;</td>
			</tr>
			<tr>

				<td class="littletableheader"><?=$form->fecha->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->fecha->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->nombre->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->nombre->output ?>&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->vd->label  ?>&nbsp;</td>
				<td class="littletablerow"><?=$form->vd->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->direc->label  ?>&nbsp;</td>
				<td class="littletablerow"><?=$form->direc->output ?>&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
			</tr>

			<?php if($form->_status=='show'){?>
			<tr>
				<td class="littletableheader"><?=$form->peso->label  ?>&nbsp;</td>
				<td class="littletablerow" align="left"><?=$form->peso->output ?>&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
			</tr>
			<?php }?>




		</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr>
				<td class="littletableheader">C&oacute;digo</td>
				<td class="littletableheader">Descripci&oacute;n</td>
				<td class="littletableheader">Cantidad</td>
				<td class="littletableheader">Precio</td>
				<td class="littletableheader">Importe</td>


				<?php if($form->_status!='show') {?>
				<!--				<td class="littletableheader">&nbsp;</td>-->
				<?php } ?>
			</tr>
			<?php for($i=0;$i<$form->max_rel_count['itspre'];$i++) {
				$obj1="codigo_$i";
				$obj2="desca_$i";
				$obj3="cana_$i";
				$obj4="preca_$i";
				$obj5="totaorg_$i";
				$obj6="precio4_$i";
				$obj7="iva_$i";
				$obj8="ultimo_$i";
				$obj9="pond_$i";

				?>
			<tr id='tr_itspre_<?=$i ?>'>
				<td class="littletablerow"><?=$form->$obj1->output ?></td>
				<td class="littletablerow"><?=$form->$obj2->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj3->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj4->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj5->output ?></td>
				<td class="littletablerow"><?=$form->$obj6->output ?></td>
				<td class="littletablerow"><?=$form->$obj7->output ?></td>
				<td class="littletablerow"><?=$form->$obj8->output ?></td>
				<td class="littletablerow"><?=$form->$obj9->output ?></td>


				<?php if($form->_status!='show') {?>
				<td class="littletablerow"><a href=#
					onclick='del_itspre(<?=$i ?>);return false;'>Eliminar</a></td>
					<?php } ?>
				
			</tr>
			<?php } ?>

			<tr id='__UTPL__'>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>


				<?php if($form->_status!='show') {?>
				<!--				<td class="littletablefooterb" align="right">&nbsp;</td>-->
				<?php } ?>
			</tr>
			<?php if ($form->_status =='show'){?>
			<tr>
				<td class="littletableheader"><?=$form->ivat->label  ?></td>
				<td class="littletablerow"><?=$form->ivat->output?></td>
				<td class="littletableheader"><?=$form->totals->label  ?></td>
				<td class="littletablerow"><?=$form->totals->output ?></td>
				<td class="littletableheader"><?=$form->totalg->label  ?></td>
				<td class="littletablerow"><?=$form->totalg->output ?></td>
			</tr>
			<?php }?>
		</table>

		<?php echo $form_end     ?> <?php echo $container_bl ?> <?php echo $container_br ?>
		</td>
	</tr>

</table>

		<?php endif; ?>