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
$campos.=' <td class="littletablerow"><a href=\'#\' onclick="del_gitser(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);
//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){

$sql='SELECT TRIM(a.codbanc) AS codbanc,tbanco FROM banc AS a';
$query = $this->db->query($sql);
$comis=array();
if ($query->num_rows() > 0){
	foreach ($query->result() as $row){
		$ind='_'.$row->codbanc;
		$comis[$ind]['tbanco']  =$row->tbanco;
	}
}
$json_comis=json_encode($comis);
?>

<script language="javascript" type="text/javascript">
gitser_cont=<?=$form->max_rel_count['gitser']?>;
var departa  = '';
var sucursal = '';
var comis    = <?php echo $json_comis; ?>;

$(document).ready(function() {
	$(".inputnum").numeric(".");
	/*pr=$("#proveed").val();
	for(i=0;i<gitser_cont;i++){
		$("#proveed_"+i.toString()).val(pr);
		iva=$("#tasaiva_"+i.toString()).val();
		p=roundNumber($("#precio_"+i.toString()).val(),2);
		miva=roundNumber(p*iva/100,2);
		$("#iva_"+i.toString()).val(miva);
	}*/
	totalizar();
	codb1=$('#codb1').val();
	desactivacampo(codb1)
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

function reteiva(){
	totiva=Number($("#totiva").val());
	preten=Number($("#__reteiva").val());
	preten=totiva*(preten/100);

	$("#reteiva").val(roundNumber(preten,2));
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

	arr=$('input[name^="importe_"]');
	jQuery.each(arr, function() {
			nom=this.name
			pos=this.name.lastIndexOf('_');
			if(pos>0){
				ind = this.name.substring(pos+1);
				tp1=Number($("#precio_"+ind).val());
				ite=Number(this.value);

				tp=tp+tp1;
				tb=tb+ite;
			}
	});

	$("#totpre").val(roundNumber(tp,2));
	$("#totbruto").val(roundNumber(tb,2));
	totiva=roundNumber(tb-tp,2);
	$("#totiva").val(totiva);

	totneto=roundNumber(tb-numberval($("#reteiva").val()),2);
	$("#totneto").val(totneto);
	reteiva();
	monto1=Number($("#monto1").val());
	$("#credito").val(roundNumber(totneto-monto1,2));
}

function ccredito(){
	credito =Number($("#credito").val());
	montonet=Number($("#totneto").val());
	$("#monto1").val(roundNumber(montonet-credito,2));
}

function contado(){
	monto1  =Number($("#monto1").val());
	montonet=Number($("#totneto").val());
	$("#credito").val(roundNumber(montonet-monto1,2));
}
function esbancaja(codb1){
	if(codb1.length>0){
		desactivacampo(codb1);
		montonet=Number($("#totneto").val());
		$("#credito").val(0);
		$("#monto1").val(roundNumber(montonet,2));
	}
}

function desactivacampo(codb1){
	if(codb1.length>0){
		eval("tbanco=comis._"+codb1+".tbanco;"  );
		if(tbanco=='CAJ'){
			$("#tipo1").val('D');
			$('#tipo1').attr('readonly','readonly');
			$('#cheque1').attr('disabled','disabled');
		}else{
			$('#tipo1').attr('readonly',false);
			$('#cheque1').removeAttr('disabled');
		}
	}
	
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
	obj='#tr_gitser_'+id;
	$(obj).remove();
	totalizar();
}
</script>
<?php }
$cod_prov=$form->getval('proveed');
if ($tipo_rete=="ESPECIAL"){
	if($cod_prov===false || empty($cod_prov)){
		$_preteiva=75;
	}else{
		$dbcod_prov=$this->db->escape($cod_prov);
		$_preteiva=$this->datasis->dameval('SELECT reteiva FROM sprv WHERE proveed='.$dbcod_prov);
	}
}else{
	$_preteiva=0;
}
?>
<input type="hidden" name="__reteiva" id="__reteiva" value="<?php echo $_preteiva; ?>">
<input type="hidden" name="__pama" id="__pama" value="">
<input type="hidden" name="__tar" id="__tar" value="">
<input type="hidden" name="__base" id="__base" value="">
	
<table align='center' width="99%">
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
				<th align='left' colspan='6' style='font-size:14px;color:#1C1C1C;background-color:#F5D0A9;' >DOCUMENTO</th>
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
				<td class="littletableheader"><?php echo $form->nfiscal->label  ?>&nbsp;</td>
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
			<?php //<tr><th class="littletableheader" colspan='9'>Detalle del gasto </th></tr> ?>
			<tr>
				<td class="littletableheaderdet">C&oacute;digo</td>
				<td class="littletableheaderdet">Descripci&oacute;n</td>
				<td class="littletableheaderdet" align="right">Precio</td>
				<td class="littletableheaderdet" align="right">Tasa</td>
				<td class="littletableheaderdet" align="right">IVA</td>
				<td class="littletableheaderdet" align="right">Importe</td>
				<td class="littletableheaderdet">Departamento</td>
				<td class="littletableheaderdet">Sucursal</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheaderdet">Acci&oacute;n&nbsp;</td>
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
					<td class="littletablerow"><a href='#' onclick='del_gitser(<?php echo $i; ?>);return false;'>Eliminar</a></td>
				<?php } ?>
			</tr>
			<?php } ?>

			<tr id='__UTPL__'>
				<td colspan='9' class="littletableheaderdet">Totales</td>
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
			<table width='100%'><tr><td valign='top'>

			<table width='100%'>
				<tr>
					<th colspan='4' align='left' colspan='6' style='font-size:14px;color:#1C1C1C;background-color:#F5D0A9;'>Forma de Pago</th>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->codb1->label     ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->codb1->output    ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->tipo1->label     ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->tipo1->output    ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->cheque1->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->cheque1->output ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->monto1->label   ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->monto1->output  ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->benefi->label   ?>&nbsp;</td>
					<td colspan='3' class="littletablerow">   <?php echo $form->benefi->output  ?>&nbsp;</td>
				</tr>
			</table>
			</td><td valign='top'>
			<table width='100%'>
				<tr>
					<th colspan='4' align='left' colspan='6' style='font-size:14px;color:#1C1C1C;background-color:#F5D0A9;'>Totales</th>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->totpre->label    ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->totpre->output   ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->totbruto->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->totbruto->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->totiva->label   ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->totiva->output  ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->reteiva->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->reteiva->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->credito->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->credito->output ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->totneto->label  ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->totneto->output ?>&nbsp;</td>
				</tr>
			</table>
			</td></tr></table>
		</td>
	</tr>
	<?php if($form->_status == 'show'){ ?>
	<tr>
		<td align='center'>
			<table>
				<tr>
					<th colspan='4' style='font-size:14px;color:#1C1C1C;background-color:#D5D5D5;'>Informacion de Control</th>
				</tr>
				<tr>
					<td class="littletableheader"><?php echo $form->usuarios->label ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->estampa->label ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->hora->label ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->transac->label ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletablerow"><?php echo $form->usuarios->output ?>&nbsp;</td>
					<td class="littletablerow"><?php echo $form->estampa->output ?>&nbsp;</td>
					<td class="littletablerow"><?php echo $form->hora->output ?>&nbsp;</td>
					<td class="littletablerow"><?php echo $form->transac->output ?>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php } ?>
</table>
<?php endif; ?>