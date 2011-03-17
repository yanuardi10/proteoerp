<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

foreach($form->detail_fields['itspre'] AS $ind=>$data)
$campos[]=$data['field'];

$campos='<tr id="tr_itspre_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';

$campos=str_replace("\n",'',$campos);
$campos.=' <td class="littletablerow"><a href=# onclick="del_itspre(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);
if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>
<input type="hidden" name="t_cli" id="t_cli" value="">

<script language="javascript" type="text/javascript">
itspre_cont=<?php echo $form->max_rel_count['itspre']; ?>;

function ejecuta(i){
	tipo=$("#t_cli").val();
	if(tipo=='' || tipo=='0')tipo='1';
	precio=$("#__p"+tipo.toString()).val();
	$("#preca_"+i.toString()).val(precio);
	p4=$("#__p4").val();
	p1=$("#__p1").val();
	$("#precio4_"+i.toString()).val(p4);
	$("#precio1_"+i.toString()).val(p1);
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
	for(var i=0;i < <?php echo $form->max_rel_count['itspre']; ?>;i++){
		cdropdown(i);
	}
});

function cdropdown(nind){
	var ind=nind.toString();
	var preca=$("#preca_"+ind).val();
	var pprecio  = document.createElement("select");
	pprecio.setAttribute("id"    , "preca_"+ind);
	pprecio.setAttribute("name"  , "preca_"+ind);
	pprecio.setAttribute("class" , "select");
	pprecio.setAttribute("style" , "width: 100px");

	var ban=0;
	var ii=0;
	var id='';
	
	for(ii=1;ii<5;ii++){
		id =ii.toString();
		val=$("#precio"+id+"_"+ind).val();
		opt=document.createElement("option");
		opt.text =nformat(val);
		opt.value=val;
		pprecio.add(opt,null);
		if(val==preca){
			ban=1;
			pprecio.selectedIndex=ii-1;
		}
	}
	if(ban==0){
		opt=document.createElement("option");
		opt.text = nformat(preca);
		opt.value= preca;
		pprecio.add(opt,null);
		pprecio.selectedIndex=4;
	}

	opt=document.createElement("option");
	opt.text = 'Otro';
	opt.value= 'otro';
	pprecio.add(opt,null);

	$("#preca_"+ind).replaceWith(pprecio);
}

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
				<th colspan='5' class="littletableheader">Presupuesto <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->numero->output,8,0,0); ?></b></th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->fecha->label;    ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output;   ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->cliente->label;  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->cliente->output; ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->nombre->output;  ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->vd->label     ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->vd->output    ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->rifci->label; ?>*&nbsp;</td>
				<td class="littletablerow" colspan='2'><?php echo $form->rifci->output;   ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->peso->label  ?>&nbsp;</td>
				<td class="littletablerow" align="left"><?=$form->peso->output ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->direc->label  ?>&nbsp;</td>
				<td class="littletablerow" colspan='2'><?php echo $form->direc->output ?>&nbsp;</td>
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
				<td class="littletableheader">Precio</td>
				<td class="littletableheader">Importe</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheader">&nbsp;</td>
				<?php } ?>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itspre'];$i++) {
				$it_codigo  = "codigo_$i";
				$it_desca   = "desca_$i";
				$it_cana    = "cana_$i";
				$it_preca   = "preca_$i";
				$it_totaorg = "totaorg_$i";
				$it_iva     = "iva_$i";
				$it_ultimo  = "ultimo_$i";
				$it_pond    = "pond_$i";

				$pprecios='';
				for($o=1;$o<5;$o++){
					$it_obj   = "precio${o}_${i}";
					$pprecios.="<input type='hidden' name='$it_obj' id='$it_obj' value='".floatval($form->$it_obj->output)."'>";
					//$pprecios.=form_hidden($it_obj, floatval($form->$it_obj->output));
				}
			?>

			<tr id='tr_itspre_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" ><?php echo $form->$it_codigo->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_desca->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_cana->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_preca->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_totaorg->output.$pprecios;?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itspre(<?=$i ?>);return false;'>Eliminar</a>
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
				<th colspan='6' class="littletableheader">Res&uacute;men Financiero</th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->ivat->label;    ?></td>
				<td class="littletablerow">   <?php echo $form->ivat->output;   ?></td>
				<td class="littletableheader"><?php echo $form->totals->label;  ?></td>
				<td class="littletablerow">   <?php echo $form->totals->output; ?></td>
				<td class="littletableheader"><?php echo $form->totalg->label;  ?></td>
				<td class="littletablerow">   <?php echo $form->totalg->output; ?></td>
			</tr>
		</table>
		<?php echo $form_end; ?>
		</td>
	</tr>
</table>
<?php endif; ?>