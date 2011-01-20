<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' OR $form->_action=='delete'):
echo $form->output;
else:

$tipo_rete=$this->datasis->traevalor('CONTRIBUYENTE');

foreach($form->detail_fields['gitser'] AS $ind=>$data)
$campos[]=$data['field'];

$campos='<tr id="tr_gitser_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';


$campos=str_replace("\n",'',$campos);
$campos.=' <td class="littletablerow"><a href=# onclick="del_gitser(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);
if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){
	?>
<input type="hidden" name="__reteiva" id="__reteiva" value="">
<input type="hidden" name="__pama" id="__pama" value="">
<input type="hidden" name="__tar" id="__tar" value="">
<input type="hidden" name="__base" id="__base" value="">


<script language="javascript" type="text/javascript">
gitser_cont=<?=$form->max_rel_count['gitser']?>;

$(document).ready(function() {
	//alert(itpfac_cont);
	pr=$("#proveed").val();
	
	
	for(i=0;i<gitser_cont;i++){
		$("#proveed_"+i.toString()).val(pr);
		iva=$("#tasaiva_"+i.toString()).val();
		p=roundNumber($("#precio_"+i.toString()).val(),2);
		miva=roundNumber(p*iva/100,2);
		$("#iva_"+i.toString()).val(miva);
	}
});

function valida(i){
	alert("Este monto no puede ser modificado manualmente");
	totalizar(i);
}


function lleva(i){
	pr=$("#proveed").val();
	
	$("#proveed_"+i.toString()).val(pr);
	
}

function islr(){
	base=$("#__base").val();
	pama=$("#__pama").val();
	tar=$("#__tar").val();
	tp=$("#totpre").val();
	br=roundNumber(tp*base/100,2);
	$("#breten").val(br);
	if(tp > pama){
		
		reten=roundNumber(br*tar/100,2);
		
	}else{reten=0.00;}
	$("#reten").val(reten);	
	totneto=roundNumber($("#totbruto").val()-$("#reteiva").val()-$("#reten").val(),2);
	$("#totneto").val(totneto);
}

function totalizar(i){
	p=roundNumber($("#precio_"+i.toString()).val(),2);
	iva=$("#tasaiva_"+i.toString()).val();
	piva=roundNumber(p*(1+iva/100),2);
	miva=roundNumber(p*iva/100,2);
	$("#importe_"+i.toString()).val(piva);
	$("#iva_"+i.toString()).val(miva);
	tp=0;tb=0;ti=0;
	for(j=0;j<gitser_cont;j++){
		tp1=$("#precio_"+j.toString()).val();
		tp=Number(tp)+Number(tp1);
		tb=Number(tb)+Number($("#importe_"+j.toString()).val());
	}
	tb=roundNumber(tb,2);
	tp=roundNumber(tp,2);
	$("#totpre").val(tp);
	$("#totbruto").val(tb);
	totiva=roundNumber(tb-tp,2);
	$("#totiva").val(totiva);
	if($("#breten").val()!= "" ){
		islr();
		totneto=roundNumber($("#totbruto").val()-$("#reteiva").val()-$("#reten").val(),2);
		$("#totneto").val(totneto);
	}
	<?php if ($tipo_rete=="ESPECIAL"){ ?>
			valor=0;
			if ($("#__reteiva").val()==0 || $("#__reteiva").val()==75){
				valor=roundNumber(totiva*75/100,2);
				$("#reteiva").val(valor);
			}else{
				valor=roundNumber(totiva,2);
				$("#reteiva").val(valor);
			}
	<?php }elseif($tipo_rete=="NORMAL"){?>
			
			valor=roundNumber(0,2);
			
			$("#reteiva").val(valor);
	<?php }?>
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
				<td class="littletableheader"><?=$form->tipo_doc->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->tipo_doc->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->ffactura->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->ffactura->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->proveedg->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->proveedg->output ?>&nbsp;</td>
			</tr>

			<tr>
				<td class="littletableheader">N&uacute;mero</td>
				<td class="littletablerow"><?php  echo str_pad(trim($form->numero->output),8,0,STR_PAD_LEFT) ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->fecha->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->fecha->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->nombre->label  ?>*&nbsp;</td>
				<td class="littletablerow"><?=$form->nombre->output ?>&nbsp;</td>

			</tr>
			<tr>
				<td class="littletableheader"><?=$form->nfiscal->label  ?>&nbsp;</td>
				<td class="littletablerow"><?=$form->nfiscal->output ?>&nbsp;</td>
				<td class="littletableheader"><?=$form->vence->label  ?>&nbsp;</td>
				<td class="littletablerow"><?=$form->vence->output ?>&nbsp;</td>
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
				<td class="littletableheader" align="right">Precio</td>
				<td class="littletableheader" align="right">Tasa</td>
				<td class="littletableheader" align="right">IVA</td>
				<td class="littletableheader" align="right">Importe</td>
				<td class="littletableheader">Departamento</td>
				<td class="littletableheader">Sucursal</td>

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
				$obj7="departa_$i";
				$obj8="sucursal_$i";
				$obj9="fechad_$i";
				$obj10="numerod_$i";
				$obj6="proveed_$i";
				$obj11="tasaiva_$i";

				?>
			<tr id='tr_gitser_<?=$i ?>'>
				<td class="littletablerow"><?=$form->$obj1->output ?></td>
				<td class="littletablerow"><?=$form->$obj2->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj3->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj11->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj4->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj5->output ?></td>
				<td class="littletablerow"><?=$form->$obj7->output ?></td>
				<td class="littletablerow"><?=$form->$obj8->output ?></td>
				<td class="littletablerow"><?=$form->$obj6->output ?></td>
				<td class="littletablerow"><?=$form->$obj9->output ?></td>
				<td class="littletablerow"><?=$form->$obj10->output ?></td>


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

			<?php }?>
		</table>

		<?php echo $form_end     ?> <?php echo $container_bl ?> <?php echo $container_br ?>
		</td>
	</tr>
	<tr>
		<td class="littletablefooterb" align="right">&nbsp;</td>

	</tr>
	<tr>
		<td>
		<table>
			<tr>
				<td class="littletableheader"><?=$form->totiva->label  ?></td>
				<td class="littletablerow"><?=$form->totiva->output?></td>
				<td class="littletableheader"><?=$form->totpre->label  ?></td>
				<td class="littletablerow"><?=$form->totpre->output ?></td>
				<td class="littletableheader"><?=$form->totbruto->label  ?></td>
				<td class="littletablerow"><?=$form->totbruto->output ?></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td>
		<table>
			<tr>
				<td>
				<table>
					<tr>
						<td class="littletableheader">FORMA DE PAGO</td>
					</tr>
					<tr>
						<td class="littletableheader"><?=$form->codb1->label  ?></td>
						<td class="littletablerow"><?=$form->codb1->output?></td>
					</tr>
					<tr>
						<td class="littletableheader"><?=$form->tipo1->label  ?></td>
						<td class="littletablerow"><?=$form->tipo1->output?></td>
						<td class="littletableheader"><?=$form->cheque1->label  ?></td>
						<td class="littletablerow"><?=$form->cheque1->output?></td>
					</tr>
					<tr>
						<td class="littletableheader"><?=$form->benefi->label  ?></td>
						<td class="littletablerow"><?=$form->benefi->output?></td>
					</tr>
					<tr>
						<td class="littletableheader"><?=$form->monto1->label  ?></td>
						<td class="littletablerow"><?=$form->monto1->output?></td>
						<td class="littletableheader"><?=$form->credito->label  ?></td>
						<td class="littletablerow"><?=$form->credito->output?></td>
					</tr>
					<tr>
						<td class="littletableheader"><?=$form->comprob1->label  ?></td>
						<td class="littletablerow"><?=$form->comprob1->output?></td>
						<td class="littletableheader"><?=$form->transac->label  ?></td>
						<td class="littletablerow"><?=$form->transac->output?></td>
					</tr>
				</table>
				</td>
				<td>
				<table>
					<tr>
						<td>
						<table>
							<tr>
								<td class="littletableheader">RETENCI&Oacute;N ISLR</td>
							</tr>
							<tr>
								<td class="littletableheader"><?=$form->creten->label  ?></td>
								<td class="littletablerow"><?=$form->creten->output?></td>
							</tr>
							<tr>
								<td class="littletableheader"><?=$form->breten->label  ?></td>
								<td class="littletablerow"><?=$form->breten->output?></td>
							</tr>
							<tr>
								<td class="littletableheader"><?=$form->reten->label  ?></td>
								<td class="littletablerow"><?=$form->reten->output?></td>
							</tr>
						</table>
						</td>
					</tr>
				</table>
				</td>
				<td>
				<table>
					<tr>
						<td>
						<table>
							<tr>
								<td class="littletableheader">TOTALES</td>
							</tr>
							<tr>
								<td class="littletableheader"><?=$form->reteiva->label  ?></td>
								<td class="littletablerow"><?=$form->reteiva->output?></td>
							</tr>
							<!--<tr>
								<td class="littletableheader"><?=$form->anticipo->label  ?></td>
								<td class="littletablerow"><?=$form->anticipo->output?></td>
							</tr>
							--><tr>
								<td class="littletableheader"><?=$form->totneto->label  ?></td>
								<td class="littletablerow"><?=$form->totneto->output?></td>
							</tr>
						</table>
						</td>
					</tr>
				</table>
				</td>
			</tr>
		</table>

		</td>

	</tr>
</table>

		<?php endif; ?>