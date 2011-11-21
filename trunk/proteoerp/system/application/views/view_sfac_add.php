<?php

$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('sitems');
$scampos  ='<tr id="tr_sitems_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['codigoa']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['desca']['field'].$campos['detalle']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['cana']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['preca']['field']. '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['tota']['field'];
for($o=1;$o<5;$o++){
	$it_obj   = "precio${o}";
	$scampos .= $campos[$it_obj]['field'];
}
$scampos .= $campos['itiva']['field'];
$scampos .= $campos['sinvtipo']['field'];
$scampos .= $campos['sinvpeso']['field'].'</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_sitems(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$campos=$form->js_escape($scampos);

$sfpa_campos=$form->template_details('sfpa');
$sfpa_scampos  ='<tr id="tr_sfpa_<#i#>">';
$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['tipo']['field'].  '</td>';
$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['numref']['field'].'</td>';
$sfpa_scampos .='<td class="littletablerow" align="left" >'.$sfpa_campos['banco']['field']. '</td>';
$sfpa_scampos .='<td class="littletablerow" align="right">'.$sfpa_campos['monto']['field']. '</td>';
$sfpa_scampos .='<td class="littletablerow"><a href=# onclick="del_sfpa(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$sfpa_campos=$form->js_escape($sfpa_scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var sitems_cont=<?php echo $form->max_rel_count['sitems']; ?>;
var sfpa_cont=<?php echo $form->max_rel_count['sfpa'];?>;

$(function(){
	$(".inputnum").numeric(".");
	totalizar();
	for(var i=0;i < <?php echo $form->max_rel_count['sitems']; ?>;i++){
		cdropdown(i);
		cdescrip(i);
		autocod(i.toString());
	}

	$('#cod_cli').autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscascli'); ?>",
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
		minLength: 2,
		select: function( event, ui ) {
			$('#nombre').val(ui.item.nombre);
			$('#nombre_val').text(ui.item.nombre);

			$('#rifci').val(ui.item.rifci);
			$('#rifci_val').text(ui.item.rifci);

			$('#cod_cli').val(ui.item.cod_cli);
			$('#sclitipo').val(ui.item.tipo);

			$('#direc').val(ui.item.direc);
			$('#direc_val').text(ui.item.direc);
		}
	});

	$('#factura').autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscasfacdev'); ?>",
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
		minLength: 2,
		select: function( event, ui ) {
			$('#nombre').val(ui.item.nombre);
			$('#nombre_val').text(ui.item.nombre);

			$('#rifci').val(ui.item.rifci);
			$('#rifci_val').text(ui.item.rifci);

			$('#cod_cli').val(ui.item.cod_cli);
			$('#sclitipo').val(ui.item.tipo);

			$('#direc').val(ui.item.direc);
			$('#direc_val').text(ui.item.direc);

			$.ajax({
				url: "<?php echo site_url('ajax/buscasinvdev'); ?>",
				dataType: 'json',
				type: 'POST',
				data: "q="+ui.item.value,
				success: function(data){
						$("#tipo_doc").val('D');
						truncate();
						$.each(data,
							function(id, val){
								add_sitems();
								$('#codigoa_'+id).val(val.codigo);
								$('#desca_'+id).val(val.descrip);
								$('#precio1_'+id).val(val.base1);
								$('#precio2_'+id).val(val.base2);
								$('#precio3_'+id).val(val.base3);
								$('#precio4_'+id).val(val.base4);
								$('#itiva_'+id).val(val.iva);
								$('#sinvtipo_'+id).val(val.tipo);
								$('#sinvpeso_'+id).val(val.peso);
								$('#pond_'+id).val(val.pond);
								$('#ultimo_'+id).val(val.ultimo);
								$('#cana_'+id).val(val.cana);
								post_modbus_sinv(id);
							}
						);
					},
			});
		}
	});
});

function truncate(){
	$('tr[id^="tr_sitems_"]').remove();
	$('tr[id^="tr_sfpa_"]').remove();
	sitems_cont=sfpa_cont=0;
}

function importe(id){
	var ind     = id.toString();
	var cana    = Number($("#cana_"+ind).val());
	var preca   = Number($("#preca_"+ind).val());
	var importe = roundNumber(cana*preca,2);
	$("#tota_"+ind).val(importe);
	$("#tota_"+ind+"_val").text(nformat(importe,2));

	totalizar();
}

//Totaliza el monto por pagar
function apagar(){
	var pago=0;
	jQuery.each($('input[id^="monto_"]'), function() {
		pago+=Number($(this).val());
	});
	return pago;
}

//Determina lo que falta por pagar
function faltante(){
	totalg=Number($("#totalg").val());
	paga  = apagar();
	resto = totalg-paga;
	return resto;
}

function totalizar(){
	var iva    =0;
	var totalg =0;
	var itiva  =0;
	var itpeso =0;
	var totals =0;
	var importe=0;
	var peso   =0;
	var cana   =0;
	var arr=$('input[name^="tota_"]');
	jQuery.each(arr, function() {
		nom=this.name;
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind     = this.name.substring(pos+1);
			cana    = Number($("#cana_"+ind).val());
			itiva   = Number($("#itiva_"+ind).val());
			itpeso  = Number($("#sinvpeso_"+ind).val());
			importe = Number(this.value);

			peso    = peso+(itpeso*cana);
			iva     = iva+importe*(itiva/100);
			totals  = totals+importe;
		}
	});
	totalg=totals+iva;
	$("#peso").val(roundNumber(peso,2));
	$("#totalg").val(roundNumber(totals+iva,2));
	$("#totals").val(roundNumber(totals,2));
	$("#iva").val(roundNumber(iva,2));
	$("#totalg_val").text(nformat(totalg,2));
	$("#totals_val").text(nformat(totals,2));
	$("#ivat_val").text(nformat(iva,2));

	resto=faltante();
	utmo =$('input[id^="monto_"]').first();
	hay  =Number(utmo.val());

	utmo.val(roundNumber(hay+resto,2));
}

function add_sitems(){
	var htm = <?php echo $campos; ?>;
	can = sitems_cont.toString();
	con = (sitems_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__INPL__").after(htm);
	$("#cana_"+can).numeric(".");
	autocod(can);
	$('#codigo_'+can).focus();
	sitems_cont=sitems_cont+1;
}

function add_sfpa(){
	var htm = <?php echo $sfpa_campos; ?>;
	can = sfpa_cont.toString();
	con = (sfpa_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__ITPL__sfpa").after(htm);
	falta =faltante();
	$("#monto_"+can).val(falta);
	sfpa_cont=sfpa_cont+1;
}

function post_precioselec(ind,obj){
	if(obj.value=='o'){
		otro = prompt('Precio nuevo','');
		otro = Number(otro);
		if(otro>0){
			var opt=document.createElement("option");
			opt.text = nformat(otro,2);
			opt.value= otro;
			obj.add(opt,null);
			obj.selectedIndex=obj.length-1;
		}
	}
	importe(ind);
}

function post_modbus_scli(){
	var tipo  =Number($("#sclitipo").val()); if(tipo>0) tipo=tipo-1;
	//var cambio=confirm('¿Deseas cambiar los precios por los que tenga asginado el cliente?');

	var arr=$('select[name^="preca_"]');
	jQuery.each(arr, function() {
		nom=this.name;
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind = this.name.substring(pos+1);
			id  = Number(ind);
			tipo=$('#sinvtipo_'+ind).val();
			if(tipo!='Servicio'){
				this.selectedIndex=tipo;
				importe(id);
			}
		}
	});
	totalizar();
}

function post_modbus_sinv(nind){
	ind=nind.toString();
	var tipo =Number($("#sclitipo").val()); if(tipo>0) tipo=tipo-1;
	$("#preca_"+ind).empty();
	var arr=$('#preca_'+ind);
	cdropdown(nind);
	cdescrip(nind);
	jQuery.each(arr, function() { this.selectedIndex=tipo; });
	importe(nind);
	totalizar();
}

function cdropdown(nind){
	var ind=nind.toString();
	var preca=$("#preca_"+ind).val();
	var pprecio  = document.createElement("select");

	pprecio.setAttribute("id"    , "preca_"+ind);
	pprecio.setAttribute("name"  , "preca_"+ind);
	pprecio.setAttribute("class" , "select");
	pprecio.setAttribute("style" , "width: 100px");
	pprecio.setAttribute("onchange" , "post_precioselec("+ind+",this)");

	var ban=0;
	var ii=0;
	var id='';

	if(preca==null || preca.length==0) ban=1;
	for(ii=1;ii<5;ii++){
		id =ii.toString();
		val=$("#precio"+id+"_"+ind).val();
		opt=document.createElement("option");
		opt.text =nformat(val,2);
		opt.value=val;
		pprecio.add(opt,null);
		if(val==preca){
			ban=1;
			pprecio.selectedIndex=ii-1;
		}
	}
	if(ban==0){
		opt=document.createElement("option");
		opt.text = nformat(preca,2);
		opt.value= preca;
		pprecio.add(opt,null);
		pprecio.selectedIndex=4;
	}

	opt=document.createElement("option");
	opt.text = 'Otro';
	opt.value= 'o';
	pprecio.add(opt,null);

	$("#preca_"+ind).replaceWith(pprecio);
}

//Cambia el campo descripcion en caso ser servicio
function cdescrip(nind){
	var ind=nind.toString();
	var tipo =$("#sinvtipo_"+ind).val();

	if(tipo=='Servicio'){
		var desca  =$("#desca_"+ind).val();
		var detalle=$("#detalle_"+ind).val();
		var ddetalle = document.createElement("textarea");
		ddetalle.setAttribute("id"    , "detalle_"+ind);
		ddetalle.setAttribute("name"  , "detalle_"+ind);
		ddetalle.setAttribute("class" , "textarea");
		ddetalle.setAttribute("cols"  , 34);
		ddetalle.setAttribute("rows"  , 3);
		$("#detalle_"+ind).replaceWith(ddetalle);

		if(detalle.length==0){
			$("#detalle_"+ind).val(desca);
		}else{
			$("#detalle_"+ind).val(detalle);
		}

		var ddesca = document.createElement("input");
		ddesca.setAttribute("type"  , "hidden");
		ddesca.setAttribute("id"    , "desca_"+ind);
		ddesca.setAttribute("name"  , "desca_"+ind);
		ddesca.setAttribute("value" , desca);
		$("#desca_"+ind).replaceWith(ddesca);
	}else{
		var ddetalle = document.createElement("input");
		ddetalle.setAttribute("type", "hidden");
		ddetalle.setAttribute("id"    , "detalle_"+ind);
		ddetalle.setAttribute("name"  , "detalle_"+ind);
		ddetalle.setAttribute("value" , "");
		$("#detalle_"+ind).replaceWith(ddetalle);

		var desca = $("#desca_"+ind).val();
		var ddeca = document.createElement("input");
		ddeca.setAttribute("id"    , "desca_"+ind);
		ddeca.setAttribute("name"  , "desca_"+ind);
		ddeca.setAttribute("class" , "input");
		ddeca.setAttribute("size"  , 36);
		ddeca.setAttribute("maxlength", 50);
		ddeca.setAttribute("readonly" ,"readonly");
		ddeca.setAttribute("value"    ,desca);
		$("#desca_"+ind).replaceWith(ddeca);
	}
}

//Agrega el autocomplete
function autocod(id){
	$('#codigoa_'+id).autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscasinv'); ?>",
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
		minLength: 2,
		select: function( event, ui ) {
			//id='0';
			$('#codigo_'+id).val(ui.item.codigo);
			$('#desca_'+id).val(ui.item.descrip);
			$('#precio1_'+id).val(ui.item.base1);
			$('#precio2_'+id).val(ui.item.base2);
			$('#precio3_'+id).val(ui.item.base3);
			$('#precio4_'+id).val(ui.item.base4);
			$('#itiva_'+id).val(ui.item.iva);
			$('#sinvtipo_'+id).val(ui.item.tipo);
			$('#sinvpeso_'+id).val(ui.item.peso);
			$('#pond_'+id).val(ui.item.pond);
			$('#ultimo_'+id).val(ui.item.ultimo);
			$('#cana_'+id).val('1');
			$('#cana_'+id).focus();
			$('#cana_'+id).select();

			var arr  = $('#preca_'+ind);
			var tipo = Number($("#sclitipo").val()); if(tipo>0) tipo=tipo-1;
			cdropdown(id);
			cdescrip(id);
			jQuery.each(arr, function() { this.selectedIndex=tipo; });
			importe(id);
			totalizar();
		}
	});
}

function del_sitems(id){
	id = id.toString();
	$('#tr_sitems_'+id).remove();
	totalizar();
	var arr = $('input[id^="codigoa_"]');
	if(arr.length<=0){
		add_sitems();
	}
}
function del_sfpa(id){
	id = id.toString();
	$('#tr_sfpa_'+id).remove();
	totalizar();
	var arr = $('input[id^="monto_"]');
	if(arr.length<=0){
		add_sfpa();
	}
}
</script>
<?php } ?>
<table align='center' width="95%">
	<tr>
<?php if ($form->_status=='show') { ?>
		<td>
		<a href="#" onclick="window.open('<?php echo base_url() ?>formatos/verhtml/FACTURATER/<?php echo $form->numero->value.'/'.$form->tipo_doc->value ?>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');" heigth="600" >
		<img src='<?php echo base_url() ?>images/html_icon.gif'></a>
		</td>
<?php } ?>
		<td align=right><?php echo $container_tr?></td>
	</tr>
</table>
<table align='center' width="100%">
	<tr>
		<td>
		<table width='100%'><tr><td>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Documento</legend>
			<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td class="littletableheader"><?php echo $form->fecha->label;    ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output;   ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->cajero->label     ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->cajero->output    ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->vd->label      ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->vd->output     ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->almacen->label ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->almacen->output?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader">          <?php echo $form->tipo_doc->label  ?>&nbsp;</td>
				<td class="littletablerow" align="left"><?php echo $form->tipo_doc->output ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->factura->label  ?>&nbsp;</td>
				<td class="littletablerow"   ><?php echo $form->factura->output ?>&nbsp;</td>
			</tr>
			</table>
			</fieldset>
		</td><td>
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Cliente</legend>
			<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td class="littletableheader"><?php echo $form->cliente->label;  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->cliente->output,$form->sclitipo->output; ?>&nbsp;</td>
				<td class="littletablerow">   <b id='nombre_val'><?php echo $form->nombre->value; ?></b><?php echo $form->nombre->output;  ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->rifci->label; ?>&nbsp;</td>
				<td class="littletablerow" colspan='2'><b id='rifci_val'><?php echo $form->rifci->value; ?></b><?php echo $form->rifci->output;   ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletableheader"><?php echo $form->direc->label  ?>&nbsp;</td>
				<td class="littletablerow" colspan='2'><b id='direc_val'><?php echo $form->direc->value; ?></b><?php echo $form->direc->output ?>&nbsp;</td>
			</tr>
			</table>
			</fieldset>
		</td><tr></table>
		<br>
		</td>
	</tr>
	<tr>
		<td>
		<div style='overflow:auto;border: 1px solid #9AC8DA;background: #FAFAFA;height:200px'>
		<table width='100%' border='0'>
			<tr id='__INPL__'>
				<td class="littletableheaderdet"><strong>C&oacute;digo</strong></td>
				<td class="littletableheaderdet"><strong>Descripci&oacute;n</strong></td>
				<td class="littletableheaderdet"><strong>Cantidad</strong></td>
				<td class="littletableheaderdet"><strong>Precio</strong></td>
				<td class="littletableheaderdet"><strong>Importe</strong></td>
				<?php if($form->_status!='show') {?>
					<td bgcolor='#7098D0'>&nbsp;</td>
				<?php } ?>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['sitems'];$i++) {
				$it_codigo  = "codigoa_$i";
				$it_desca   = "desca_$i";
				$it_cana    = "cana_$i";
				$it_preca   = "preca_$i";
				$it_importe = "tota_$i";
				$it_iva     = "itiva_$i";
				$it_peso    = "sinvpeso_$i";
				$it_tipo    = "sinvtipo_$i";
				$it_ultimo  = "ultimo_$i";
				$it_detalle = "detalle_$i";
				$it_pond    = "pond_$i";
				$pprecios='';
				for($o=1;$o<5;$o++){
					$it_obj   = "precio${o}_${i}";
					$pprecios.= $form->$it_obj->output;
				}

				$pprecios .= $form->$it_iva->output;
				$pprecios .= $form->$it_peso->output;
				$pprecios .= $form->$it_tipo->output;
			?>

			<tr id='tr_sitems_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" ><?php echo $form->$it_codigo->output; ?></td>
				<td class="littletablerow" align="left" ><?php
					if($form->_status=='show' && strlen($form->$it_detalle->value)>0){
						echo  '<pre>'.htmlspecialchars($form->$it_detalle->value).'</pre>';
					}else{
						echo $form->$it_desca->output.$form->$it_detalle->output;
					}
				?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_cana->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_preca->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_importe->output.$pprecios;?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_sitems(<?php echo $i ?>);return false;'><?php echo img("images/delete.jpg"); ?></a>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>

			<tr id='__UTPL__'>
				<td id='cueca'></td>
			</tr>
		</table>
		</div>
		<?php echo $container_bl ?>
		<?php echo $container_br ?>
		</td>
	</tr><tr>
		<table width='100%'>
			<tr id='__ITPL__sfpa'>
				<td class="littletableheaderdet">Tipo</td>
				<td class="littletableheaderdet">N&uacute;mero</td>
				<td class="littletableheaderdet">Banco</td>
				<td class="littletableheaderdet">Monto</td>
				<?php if($form->_status!='show') {?>
					<td class="littletableheaderdet"></td>
				<?php } ?>
			</tr>
			<?php 

			for($i=0; $i < $form->max_rel_count['sfpa']; $i++) {
				$tipo   = "tipo_$i";
				$numref = "numref_$i";
				$monto  = "monto_$i";
				$banco  = "banco_$i";
			?>
			<tr id='tr_sfpa_<?php echo $i; ?>'>
				<td class="littletablerow" nowrap><?php echo $form->$tipo->output ?></td>
				<td class="littletablerow">       <?php echo $form->$numref->output ?></td>
				<td class="littletablerow">       <?php echo $form->$banco->output ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$monto->output ?></td>
				<?php if($form->_status!='show') {?>
					<td class="littletablerow"><a href=# onclick="del_sfpa(<?php echo $i; ?>);return false;"><?php echo img("images/delete.jpg"); ?></a></td></tr>
				<?php } ?>
			<?php } ?>
			</tr>
			<tr id='__UTPL__sfpa'>
				<td colspan='9' class="littletableheaderdet">&nbsp;</td>
			</tr>
		</table>

	</tr><tr>
		<td>
		<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
		<table width='100%'>
			<tr>
				<td class="littletableheader" width='100'></td>
				<td class="littletablerow"    width='350'></td>
				<td class="littletableheader">           <?php echo $form->totals->label;  ?></td>
				<td class="littletablerow" align='right'><b id='totals_val'><?php echo nformat($form->totals->value); ?></b><?php echo $form->totals->output; ?></td>

			<tr></tr>	
				<td class="littletableheader">&nbsp;</td>
				<td class="littletablerow"   ></td>
				<td class="littletableheader"><?php echo $form->ivat->label;    ?></td>
				<td class="littletablerow" align='right'><b id='ivat_val'><?php echo nformat($form->ivat->value); ?></b><?php echo $form->ivat->output; ?></td>
			<tr></tr>
				<td>&nbsp;</td><td>&nbsp;</td>
				<td class="littletableheader">           <?php echo $form->totalg->label;  ?></td>
				<td class="littletablerow" align='right' style='font-size:18px;font-weight: bold'><b id='totalg_val'><?php echo nformat($form->totalg->value); ?></b><?php echo $form->totalg->output; ?></td>
			</tr>
		</table>
		</fieldset>
		</td>
	</tr>
</table>
<?php echo $form_end; ?>
<?php endif; ?>