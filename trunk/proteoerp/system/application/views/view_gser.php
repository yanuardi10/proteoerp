<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' OR $form->_action=='delete'):
echo $form->output;
else:


foreach($form->detail_fields['gitser'] AS $ind=>$data)
$campos[]=$data['field'];

$campos='<tr id="tr_itpfac_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';


$campos=str_replace("\n",'',$campos);
$campos.=' <td class="littletablerow"><a href=# onclick="del_gitser(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);
if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){
	?>



<script language="javascript" type="text/javascript">
gitser_cont=<?=$form->max_rel_count['gitser']?>;



function valida(i){
	alert("Este monto no puede ser modificado manualmente");
	totalizar(i);
}


function lleva(i){
	pr=$("#proveed").val();
	$("#proveed_"+i.toString()).val(pr);
	
}

function totalizar(i){
	p=roundNumber($("#precio_"+i.toString()).val(),2);
	iva=$("#iva_"+i.toString()).val();
	piva=roundNumber(p*(1+iva/100),2);
	$("#importe_"+i.toString()).val(piva);
	
}

function add_gitser(){
	var htm = <?=$campos ?>;
	can = gitser_cont.toString();
	con = (gitser_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	gitser_cont=gitser_cont+1;
}

$(function(){
	$(".inputnum").numeric(".");
});
					
function del_gitser(id){
	id = id.toString();
	$('#tr_gitser_'+id).remove();
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
				<td class="littletableheader"><?=$form->id->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->id->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->proveedg->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->proveedg->output ?>&nbsp;</td>

			</tr>
			<tr>

				<td class="littletableheader"><?=$form->fecha->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->fecha->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->nombre->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->nombre->output ?>&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
				<td class="littletablerow">&nbsp;</td>
			</tr>
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
				<td class="littletableheader">Precio</td>
				<td class="littletableheader">IVA</td>
				<td class="littletableheader">Importe</td>


				<?php if($form->_status!='show') {?>
				<!--				<td class="littletableheader">&nbsp;</td>-->
				<?php } ?>
			</tr>
			<?php for($i=0;$i<$form->max_rel_count['gitser'];$i++) {
				$obj1="codigo_$i";
				$obj2="descrip_$i";
				$obj3="precio_$i";
				$obj4="iva_$i";
				$obj5="importe_$i";

				?>
			<tr id='tr_gitser_<?=$i ?>'>
				<td class="littletablerow"><?=$form->$obj1->output ?></td>
				<td class="littletablerow"><?=$form->$obj2->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj3->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj4->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj5->output ?></td>


				<?php if($form->_status!='show') {?>
				<td class="littletablerow"><a href=#
					onclick='del_gitser(<?=$i ?>);return false;'>Eliminar</a></td>
					<?php } ?>

			</tr>
			<?php } ?>

			<tr id='__UTPL__'>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>


				<?php if($form->_status!='show') {?>

				<?php } ?>
			</tr>
			<?php if ($form->_status =='show'){?>
			<tr>
				<!--				aqui totales-->
			</tr>
			<?php }?>
		</table>

		<?php echo $form_end     ?> <?php echo $container_bl ?> <?php echo $container_br ?>
		</td>
	</tr>

</table>

		<?php endif; ?>