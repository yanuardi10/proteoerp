<?php
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$tipo_rete=$this->datasis->traevalor('CONTRIBUYENTE');
foreach($form->detail_fields['gitser'] AS $ind=>$data) $campos[]=$data['field'];
$campos='<tr id="tr_gitser_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';
$campos=str_replace("\n",'',$campos);
$campos.=' <td class="littletablerow"><a href=# onclick="del_gitser(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);
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
var departa='';
var sucursal='';
$(document).ready(function() {
	$(".inputnum").numeric(".");
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

function gdeparta(val){
	departa=val;
}

function gsucursal(val){
	sucursal=val;
}

function lleva(i){
	pr=$("#proveed").val();
	$("#proveed_"+i.toString()).val(pr);
}

function islr(){
	totneto=roundNumber(numberval($("#totbruto").val())-numberval($("#reteiva").val()),2);
	$("#totneto").val(totneto);
}

function importe(i){
	ind    = i.toString();
	precio = Number($("#precio_"+ind).val());
	iva    = Number($("#tasaiva_"+ind).val());
	miva   = precio*iva/100;
	$("#iva_"+ind).val(miva);
	$("#importe_"+ind).val(precio+miva);
	totalizar();
}

function totalizar(){
	tp=tb=ti=ite=0;
	for(j=0;j<gitser_cont;j++){
		ind=j.toString();
		tp1=Number($("#precio_"+ind).val());
		ite=Number($("#importe_"+ind).val());
		tp=tp+tp1;
		tb=tb+ite;
	}

	$("#totpre").val(roundNumber(tp,2));
	$("#totbruto").val(roundNumber(tb,2));
	totiva=roundNumber(tb-tp,2);
	$("#totiva").val(totiva);
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
	totneto=roundNumber(tb-numberval($("#reteiva").val()),2);
	$("#totneto").val(totneto);
	monto1=numberval($("#monto1").val());
	$("#credito").val(totneto-monto1);
}

function ccredito(){
	credito =Number($("#credito").val());
	montonet=Number($("#totneto").val());
	$("#monto1").val(montonet-credito);
}

function contado(){
	monto1  =Number($("#monto1").val());
	montonet=Number($("#totneto").val());
	$("#credito").val(roundNumber(montonet-monto1,2));
}

function add_gitser(){
	var htm = <?=$campos ?>;
	can = gitser_cont.toString();
	con = (gitser_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#departa_"+can).val(departa);
	$("#sucursal_"+can).val(sucursal);
	gitser_cont=gitser_cont+1;
}

function del_gitser(id){
	id = id.toString();
	$('#tr_gitser_'+id).remove();
	totalizar();
}
</script>
<?php } ?>

<table align='center' width="80%">
	<tr>
		<td align='right'><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td><div class="alert"> <?php if(isset($form->error_string)) echo $form->error_string; ?></div></td>
	</tr>
	<tr>
		<td>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<th class="littletableheader" colspan='6'>Datos del gasto</th>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->tipo_doc->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->tipo_doc->output ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->ffactura->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->ffactura->output ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->proveed->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->proveed->output  ?>&nbsp; </td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->numero->label  ?>*</td>
				<td class="littletablerow">   <?php echo $form->numero->output ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->fecha->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output  ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->nombre->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->nombre->output ?>&nbsp; </td>

			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->nfiscal->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->nfiscal->output ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->vence->label    ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->vence->output   ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->compra->label   ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->compra->output  ?>&nbsp;</td>
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
				<th class="littletableheader" colspan='9'>Detalle del gasto </th>
			</tr>
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
					<td class="littletableheader">Acci&oacute;n&nbsp;</td>
				<?php } ?>
			</tr>
			<?php for($i=0;$i<$form->max_rel_count['gitser'];$i++) {
				$obj1 ="codigo_$i";
				$obj2 ="descrip_$i";
				$obj3 ="precio_$i";
				$obj4 ="iva_$i";
				$obj5 ="importe_$i";
				$obj7 ="departa_$i";
				$obj8 ="sucursal_$i";
				$obj11="tasaiva_$i";
			?>
			<tr id='tr_gitser_<?=$i ?>'>
				<td class="littletablerow" nowrap><?php echo $form->$obj1->output ?></td>
				<td class="littletablerow">       <?php echo $form->$obj2->output ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj3->output  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj11->output ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj4->output  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj5->output  ?></td>
				<td class="littletablerow"><?php echo $form->$obj7->output  ?></td>
				<td class="littletablerow"><?php echo $form->$obj8->output  ?></td>
				<?php if($form->_status!='show') {?>
					<td class="littletablerow"><a href=#onclick='del_gitser(<?=$i ?>);return false;'>Eliminar</a></td>
				<?php } ?>
			</tr>
			<?php } ?>

			<tr id='__UTPL__'>
				<td></td>
			</tr>

			<?php if ($form->_status =='show'){?>
			
			<?php }?>
		</table>

		<?php echo $form_end     ?>
		<?php echo $container_bl ?>
		<?php echo $container_br ?>
		</td>
	</tr>
	<tr>
		<td class="littletablefooterb" align="right">&nbsp;</td>
	</tr>
	<tr>
		<td align='center'>
			<table width='100%'>
				<tr>
					<th class="littletableheader" colspan='4'>Informaci&oacute;n Financiera</th>
					<th class="littletableheader" colspan='4'>Res&uacute;men de montos totales</th>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->codb1->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->codb1->output ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->tipo1->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->tipo1->output ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->totpre->label    ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->totpre->output   ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->totbruto->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->totbruto->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->cheque1->label ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->cheque1->output?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->benefi->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->benefi->output ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->totiva->label   ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->totiva->output  ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->reteiva->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->reteiva->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->monto1->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->monto1->output ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->credito->label ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->credito->output?>&nbsp;</td>
					<td class="littletableheader">&nbsp;</td>
					<td class="littletablerow">   &nbsp;</td>
					<td class="littletableheader"><?php echo $form->totneto->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->totneto->output ?>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php endif; ?>