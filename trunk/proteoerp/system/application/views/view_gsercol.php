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
$campos.=' <td class="littletablerow"><a href=\'#\' onclick="del_gitser(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);

foreach($form->detail_fields['gereten'] AS $ind=>$data){
	if(!empty($data['field'])){
		$ggereten[]=$data['field'];
	}
}
$cgereten='<tr id="tr_gereten_<#i#>"><td class="littletablerow">'.join('</td><td>',$ggereten).'</td>';
$cgereten.=' <td class="littletablerow"><a href=\'#\' onclick="del_gereten(<#i#>);return false;">Eliminar</a></td></tr>';
$cgereten=$form->js_escape($cgereten);

$rete=array();
$mSQL='SELECT TRIM(codigo) AS codigo,TRIM(CONCAT_WS("-",codigo,activida)) AS activida ,base1,tari1,pama1,TRIM(tipo) AS tipo FROM rete ORDER BY codigo';
$query = $this->db->query($mSQL);
if ($query->num_rows() > 0){
	foreach ($query->result() as $row){
		$ind='_'.$row->codigo;
		$rete[$ind]=array($row->activida,$row->base1,$row->tari1,$row->pama1,$row->tipo);
	}
}
$json_rete=json_encode($rete);

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
var gitser_cont =<?php echo $form->max_rel_count['gitser']; ?>;
var gereten_cont=<?php echo $form->max_rel_count['gereten'];?>;

var departa  = '';
var sucursal = '';
var comis    = <?php echo $json_comis; ?>;
var rete     = <?php echo $json_rete;  ?>;

$(document).ready(function() {
	$(".inputnum").numeric(".");
	totalizar();
	codb1=$('#codb1').val();
	desactivacampo(codb1);
	cdropdowncodigorete(0);
	autocod(0);
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

//Calcula la retencion del iva
function reteiva(){
	<?php if($tipo_rete=='GRAN'){ ?>
	totiva=Number($("#totiva").val());
	preten=Number($("#sprvreteiva").val());
	preten=totiva*(preten/100);
	$("#reteiva").val(roundNumber(preten,2));
	<?php }else{?>
	$("#reteiva").val(roundNumber(0,2));
	<?php } ?>
}

function importe(i){
	ind    = i.toString();
	precio = Number($("#precio_"+ind).val());
	iva    = Number($("#tasaiva_"+ind).val());
	miva   = precio*iva/100;
	$("#iva_"+ind).val(miva);
	$("#importe_"+ind).val(roundNumber(precio+miva,2));
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
	var reten=totalrete();
	totneto=roundNumber(tb-Number($("#reteiva").val())-reten,2);
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
	var htm = <?php echo $campos; ?>;
	can = gitser_cont.toString();
	con = (gitser_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	$("#departa_"+can).val(departa);
	$("#sucursal_"+can).val(sucursal);
	autocod(gitser_cont);
	gitser_cont=gitser_cont+1;
}

//Hace el dropdown para las retenciones
function cdropdowncodigorete(nind){
	/*var ind=nind.toString();
	var codigorete  = $("#codigorete_"+ind).val();
	var ccodigorete = document.createElement("select");

	ccodigorete.setAttribute("id"      , "codigorete_"+ind);
	ccodigorete.setAttribute("name"    , "codigorete_"+ind);
	ccodigorete.setAttribute("class"   , "select");
	ccodigorete.setAttribute("style"   , "width: 350px");
	ccodigorete.setAttribute("onchange" , "post_codigoreteselec("+ind+",this.value)");

	var opt=document.createElement("option");
	opt.text ='Seleccionar';
	opt.value='';
	ccodigorete.add(opt,null);

	$.each(rete, function(key, arreglo) {
		val=$("#codigorete_"+ind).val();
		opt=document.createElement("option");
		opt.text =arreglo[0];
		opt.value=key.substring(1);
		ccodigorete.add(opt,null);
	});

	$("#codigorete_"+ind).replaceWith(ccodigorete);*/
}

function importerete(nind){
	var ind=nind.toString();
	var codigo  = $("#codigorete_"+ind).val();
	if(codigo.length>0){
		//var tari1   = Number($("#porcen_"+ind).val());
		var importe = Number($("#base_"+ind).val());
		var base1   = Number(eval('rete._'+codigo+'[1]'));
		var tari1   = Number(eval('rete._'+codigo+'[2]'));
		var pama1   = Number(eval('rete._'+codigo+'[3]'));
		
		var tt=codigo.substring(0,1);
		if(tt=='1')
			monto=(importe*base1*tari1)/10000;
		else if(importe>pama1)
			monto=((importe-pama1)*base1)/10000;
		else
			monto=0;

		$("#monto_"+ind).val(roundNumber(monto,2));
	}
	totalizar();
}

function totalrete(){
	monto=0;
	arr  =$('input[name^="monto_"]');
	jQuery.each(arr, function() {
		monto=monto+Number(this.value);
	});
	return monto;
}

function post_codigoreteselec(nind,cod){
	var ind=nind.toString();
	var porcen=eval('rete._'+cod+'[2]');
	var base1 =eval('rete._'+cod+'[1]');
	$("#porcen_"+ind).val(porcen);
	importerete(nind);
}

function add_gereten(){
	var htm = <?php echo $cgereten; ?>;
	var can = gereten_cont.toString();
	var con = (gereten_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__gereten").before(htm);
	gereten_cont=gereten_cont+1;
}

function del_gereten(id){
	id = id.toString();
	obj='#tr_gereten_'+id;
	$(obj).remove();
	totalizar();
}

function del_gitser(id){
	id = id.toString();
	obj='#tr_gitser_'+id;
	$(obj).remove();
	totalizar();
}

//Agrega el autocomplete
function autocod(id){
	$('#codigo_'+id).autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('finanzas/gsercol/automgas'); ?>",
				type: "POST",
				dataType: "json",
				data: "q="+req.term,
				success:
					function(data){
						var sugiere = [];
						$.each(data,
							function(i, val){
								sugiere.push( val );
							}
						);
						add(sugiere);
					},
			})
		},
		minLength: 1,
		select: function( event, ui ) {
			//id='0';
			$('#codigo_'+id).val(ui.item.codigo);
			$('#descrip_'+id).val(ui.item.descrip);
		}
	});
}
</script>
<?php } else { ?>
<script language="javascript" type="text/javascript">
function toggle() {
	var ele = document.getElementById("asociados");
	var text = document.getElementById("mostrasocio");
	if(ele.style.display == "block") {
		ele.style.display = "none";
		text.innerHTML = "Mostrar Complementos ";
	}
	else {
		ele.style.display = "block";
		text.innerHTML = "Ocultar Complementos";
	}
} 
</script>
<?php } ?>
	
<table align='center' width="99%">
	<tr>
		<td align='right'><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td><div class="alert"> <?php if(isset($form->error_string)) echo $form->error_string; ?></div></td>
	</tr>
	<tr>
		<td>
		<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
		<legend class="titulofieldset" style='color: #114411;'>Documento</legend>
		<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td class="littletableheader"><?php echo $form->tipo_doc->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->tipo_doc->output ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->ffactura->label  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->ffactura->output ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->proveed->label   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->proveed->output.$form->sprvtipo->output.$form->sprvreteiva->output  ?>&nbsp; </td>
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
		</fieldset>
		</td>
	</tr>
	<tr>
		<td>
		<fieldset style='border: 2px outset #9AC8DA;background: #EFEFFF;'>
		<legend class="titulofieldset" style='color: #114411;'>Detalle</legend>
		<table width='100%'>
			<tr>
				<td class="littletableheaderdet">C&oacute;digo</td>
				<td class="littletableheaderdet">Descripci&oacute;n</td>
				<td class="littletableheaderdet" align="right">Precio</td>
				<td class="littletableheaderdet" align="right">Tasa</td>
				<td class="littletableheaderdet" align="right">IVA</td>
				<td class="littletableheaderdet" align="right">Importe</td>
				<td class="littletableheaderdet">Depto.</td>
				<td class="littletableheaderdet">Sucursal</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheaderdet">Acci&oacute;n&nbsp;</td>
				<?php } ?>
			</tr>
			<?php for($i=0; $i < $form->max_rel_count['gitser']; $i++) {
				$obj1 ="codigo_$i";
				$obj2 ="descrip_$i";
				$obj3 ="precio_$i";
				$obj4 ="iva_$i";
				$obj5 ="importe_$i";
				$obj7 ="departa_$i";
				$obj8 ="sucursal_$i";
				$obj11="tasaiva_$i";

				if($form->_status=='show'){
					$ivaval=nformat(round($form->$obj4->value/$form->$obj3->value,2)*100,2);
				}else{
					$ivaval=$form->$obj11->output;
				}
			?>
			<tr id='tr_gitser_<?=$i ?>'>
				<td class="littletablerow" nowrap><?php echo $form->$obj1->output ?></td>
				<td class="littletablerow">       <?php echo $form->$obj2->output ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj3->output  ?></td>
				<td class="littletablerow" align="right"><?php echo $ivaval ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj4->output  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$obj5->output  ?></td>
				<td class="littletablerow"><?php echo $form->$obj7->output  ?></td>
				<td class="littletablerow"><?php echo $form->$obj8->output  ?></td>
				
				<?php if($form->_status!='show') {?>
					<td class="littletablerow"><a href='#' onclick='del_gitser(<?php echo $i; ?>);return false;'>Eliminar</a></td>
				<?php } ?>
			</tr>
			<?php if( $form->_status == 'show') {?>
				<?php if( $form->_dataobject->get('cajachi') == 'S' ) { ?>
			<tr id='tr_gitser_D<?=$i ?>'>
				<td style='font-size:11px;background: #DFEFFF;font-weight: bold;' colspan=8>
				<?php
					echo '* '.$form->_dataobject->get_rel('gitser','rif',$i);
					echo ' '.$form->_dataobject->get_rel('gitser','proveedor',$i);
					echo ' Factura: '.$form->_dataobject->get_rel('gitser','numfac',$i);
					echo ' Fecha: '.dbdate_to_human($form->_dataobject->get_rel('gitser','fechafac',$i));
				?>
				</td>
			</tr>
				<?php } // caja chica ?>
			<?php } // SHOW ?>
			<?php } ?>
			<tr id='__UTPL__'>
				<td colspan='9' class="littletableheaderdet">&nbsp;</td>
			</tr>
		</table>
		</fieldset>
		<?php //echo $form_end     ?>
		<?php //echo $container_bl ?>
		<?php //echo $container_br ?>
		<?php if( $form->_status != 'show') {?>
			<input name="btn_add_gitser" value="Agregar Gasto" onclick="add_gitser()" class="button" type="button">
		<?php } ?>
		</td>
	</tr>

	<?php if($form->_status!='create'){ ?>

	<?php if ($form->max_rel_count['gereten']>0); ?>
	<tr>
		<td>
		<fieldset style='border: 2px outset #9AC8DA;background: #EFEFFF;'>
		<legend class="titulofieldset" style='color: #114411;'>Retenciones</legend>
		<table width='100%'>
			<tr>
				<td class="littletableheaderdet">C&oacute;digo</td>
				<td class="littletableheaderdet">Base</td>
				<td class="littletableheaderdet" align="right">Porcentaje</td>
				<td class="littletableheaderdet" align="right">Monto</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheaderdet">Acci&oacute;n&nbsp;</td>
				<?php } ?>
			</tr>
			<?php for($i=0; $i < $form->max_rel_count['gereten']; $i++) {
				$it_codigorete= "codigorete_$i";
				//$it_actividad = "actividad_$i";
				$it_base      = "base_$i";
				$it_porcen    = "porcen_$i";
				$it_monto     = "monto_$i";
			?>
			<tr id='tr_gereten_<?php echo $i; ?>'>
				<td class="littletablerow" nowrap><?php echo $form->$it_codigorete->output ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_base->output      ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_porcen->output    ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_monto->output     ?></td>
				<?php if($form->_status!='show') {?>
					<td class="littletablerow"><a href='#' onclick='del_gereten(<?php echo $i; ?>);return false;'>Eliminar</a></td>
				<?php }
			}?>
			</tr>
			<tr id='__UTPL__gereten'>
				<td colspan='3' class="littletableheaderdet">&nbsp;</td>
				<td class="littletableheaderdet"  align="right">&nbsp;<?php echo $form->reten->output  ?></td>
				<?php if($form->_status!='show') {?>
				<td class="littletableheaderdet">&nbsp;</td>
				<?php } ?>
			</tr>
		</table>
		</fieldset>
		<?php if( $form->_status != 'show') {?>
			<input name="btn_add_gereten" value="Agregar Retenciones " onclick="add_gereten()" class="button" type="button">
		<?php } ?>

		<?php } ?>

		<?php echo $form_end     ?>
		<?php //echo $container_bl ?>
		<?php //echo $container_br ?>
		</td>

	</tr>

	<tr>
		<td align='center'>
			<table width='100%'><tr><td valign='top'>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Forma de Pago</legend>
			<table width='100%'>
				<tr>
					<td class="littletableheader"><?php echo $form->codb1->label   ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->codb1->output  ?>&nbsp;</td>
					<td class="littletableheader"><?php echo $form->tipo1->label   ?>&nbsp;</td>
					<td class="littletablerow">   <?php echo $form->tipo1->output  ?>&nbsp;</td>
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
			</fieldset>
			</td><td valign='top'>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Totales</legend>
			<table width='100%'>
				<tr>
					<td class="littletableheader">           <?php echo $form->reteica->label  ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo $form->reteica->output ?>&nbsp;</td>
					<td class="littletableheader">           <?php echo $form->totpre->label   ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo $form->totpre->output  ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader">           &nbsp;</td>
					<td class="littletablerow" align='right'>&nbsp;</td>
					<td class="littletableheader">           <?php echo $form->totbruto->label  ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo $form->totbruto->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader">           <?php echo $form->totiva->label   ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo $form->totiva->output  ?>&nbsp;</td>
					<td class="littletableheader">           <?php echo $form->reteiva->label  ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php
						if($form->_status=='show'){
							if($form->retesimple->value>0){
								echo '<b style=\'color:red;\'>'.$form->retesimple->value.'</b>';
							}else{
								echo $form->reteiva->output;
							}
						}else{
							echo $form->reteiva->output;
						}
					?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheader">           <?php echo $form->credito->label  ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo $form->credito->output ?>&nbsp;</td>
					<td class="littletableheader">           <?php echo $form->totneto->label  ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo $form->totneto->output ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
			</td></tr></table>
		</td>
	</tr>
	
	
	<?php if($form->_status == 'show'){ ?>
	<tr>
		<td>
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Informacion del Registro</legend>
			<table width='100%' cellspacing='1' >
				<tr style='font-size:12px;color:#0B3B0B;background-color: #F7BE81;'>
					<td align='center' >Usuario</td>
					<td align='center' >Nombre </td>
					<td align='center' >Fecha  </td>
					<td align='center' >Hora   </td>
					<td align='center' >Transacci&oacute;n</td>
				</tr>
				<tr>
					<?php
						$mSQL="SELECT us_nombre FROM usuario WHERE us_codigo='".trim($form->_dataobject->get('usuario'))."'";
						$us_nombre = $this->datasis->dameval($mSQL);
					
					?>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('usuario'); ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $us_nombre ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('estampa'); ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('hora'); ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $form->_dataobject->get('transac'); ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>

	<tr>
		<td align='center'>
			<a id="mostrasocio" href="javascript:toggle();">Mostrar Complementos</a> 
			<div id='asociados' style='display: none'>
				<?php
					$mSQL = "SELECT periodo, nrocomp, emision, impuesto, reiva, round(reiva*100/impuesto,0) porcent FROM riva WHERE transac=? LIMIT 1";
					$query = $this->db->query($mSQL, array(TRIM($form->_dataobject->get('transac'))) );
					if ( $query->num_rows() > 0 ) { 
						$row = $query->row();
				?>
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Retencion de Impuesto</legend>
			<table width='100%' cellspacing='1' >
				<tr style='font-size:12px;color:#FFEEFF;background-color: #393B0B;'>
					<td align='center'>Periodo &nbsp;</td>
					<td align='center'>Numero &nbsp;</td>
					<td align='center'>Emision &nbsp;</td>
					<td align='center'>Impuesto &nbsp;</td>
					<td align='center'>Monto &nbsp;</td>
					<td align='center'>% &nbsp;</td>
				</tr>
				<tr>
					<td class="littletablerow" align='center'><?php echo $row->periodo ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $row->nrocomp ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo dbdate_to_human($row->emision) ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo nformat($row->impuesto) ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo nformat($row->reiva) ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo nformat($row->porcent) ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
				<?php }; ?>
				<?php
					$mSQL = "SELECT CONCAT(tipo_op, numero) numero, CONCAT(codbanc,'-', banco) codbanc, monto, concepto FROM bmov WHERE transac=? LIMIT 1";
					$query = $this->db->query($mSQL, array(trim($form->_dataobject->get('transac'))) );
					if ( $query->num_rows() > 0 ) {
						$row = $query->row(); ?>
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Registro en Bancos</legend>
			<table width='100%' cellspacing='1'>
				<tr>
					<td align='center' style='font-size:12px;color:#FFEEFF;background-color: #582314;'>Numero&nbsp;</td>
					<td align='center' style='font-size:12px;color:#FFEEFF;background-color: #582314;'>Caja/Banco&nbsp;</td>
					<td align='center' style='font-size:12px;color:#FFEEFF;background-color: #582314;'>Monto &nbsp;</td>
					<td align='center' style='font-size:12px;color:#FFEEFF;background-color: #582314;'>Concepto &nbsp;</td>
				</tr>
				<tr>
					<td class="littletablerow" align='center'><?php echo $row->numero ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $row->codbanc ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo nformat($row->monto) ?>&nbsp;</td>
					<td class="littletablerow" align='center'><?php echo $row->concepto ?>&nbsp;</td>
				</tr>
			</table>
			</fieldset>
				<?php }; ?>

			<?php
				$mSQL = "SELECT CONCAT(tipo_doc, numero) numero, CONCAT(cod_prv,' ',nombre) cod_prv, monto*(tipo_doc IN ('FC','ND','GI')) debe, monto*(tipo_doc NOT IN ('FC','ND','GI')) haber , monto-abonos saldo FROM sprm WHERE transac=? ";
				$query = $this->db->query($mSQL, array(trim($form->_dataobject->get('transac'))) );
				if ( $query->num_rows() > 0 ) { ?>
			<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Estado de Cuenta</legend>
			<table width='100%' cellspacing='1'>
				<tr style='font-size:12px;color:#FFEEFF;background-color: #61380B;'>
					<td align='center'>Numero &nbsp;</td>
					<td align='center'>Proveedor &nbsp;</td>
					<td align='center'>Debe &nbsp;</td>
					<td align='center'>Haber &nbsp;</td>
					<td align='center'>Saldo &nbsp;</td>
				</tr>
						<?php foreach( $query->result() as $row ){ ?>
				<tr>

					<td class="littletablerow" align='center'><?php echo $row->numero ?>&nbsp;</td>
					<td class="littletablerow" align='left'><?php echo $row->cod_prv ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo nformat($row->debe) ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo nformat($row->haber) ?>&nbsp;</td>
					<td class="littletablerow" align='right'><?php echo nformat($row->saldo) ?>&nbsp;</td>
				</tr>
						<?php }; ?>
			</fieldset>
			</table>
				<?php }; ?>
			</div>
		</td>
	</tr>
	<?php } ?>
</table>
<?php endif; ?>
