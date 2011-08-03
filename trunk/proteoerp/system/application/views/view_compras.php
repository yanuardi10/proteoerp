<?php
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itscst');
$scampos  ='<tr id="tr_itscst_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['codigo']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['descrip']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['cantidad']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['costo']['field']. '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['importe']['field'].'</td>';
$scampos .='<td class="littletablerow"><a href=# onclick="del_itscst(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$campos=$form->js_escape($scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var itscst_cont=<?php echo $form->max_rel_count['itscst']; ?>;

$(function(){
	$(".inputnum").numeric(".");
	totalizar();
	for(var i=0;i < <?php echo $form->max_rel_count['itscst']; ?>;i++){
		cdropdown(i);
		autocod(i.toString());
	}
});

function importe(id){
	var ind     = id.toString();
	var cana    = Number($("#cantidad_"+ind).val());
	var precio  = Number($("#precio_"+ind).val());
	var importe = roundNumber(cana*precio,2);
	$("#importe_"+ind).val(importe);
	totalizar();
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
	var arr=$('input[name^="importe_"]');
	jQuery.each(arr, function() {
		nom=this.name
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind     = this.name.substring(pos+1);
			cana    = Number($("#cantidad_"+ind).val());
			itiva   = Number($("#itiva_"+ind).val());
			importe = Number(this.value);
			itpeso  = Number($("#sinvpeso_"+ind).val());

			peso    = peso+(itpeso*cana);
			iva     = iva+importe*(itiva/100);
			totals  = totals+importe;
		}
	});
	$("#peso").val(roundNumber(peso,2));
	$("#gtotal").val(roundNumber(totals+iva,2));
	$("#stotal").val(roundNumber(totals,2));
	$("#impuesto").val(roundNumber(iva,2));
	
	$("#gtotal_val").text(nformat(totals+iva,2));
	$("#stotal_val").text(nformat(totals,2));
	$("#impuesto_val").text(nformat(iva,2));
}

function add_itscst(){
	var htm = <?php echo $campos; ?>;
	can = itscst_cont.toString();
	con = (itscst_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__INPL__").after(htm);
	$("#cantidad_"+can).numeric(".");
	$("#costo_"+can).numeric(".");
	cdropdown(itscst_cont);
	autocod(can);
	$('#codigo_'+can).focus();

	itscst_cont=itscst_cont+1;
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
	//var cambio=confirm('?Deseas cambiar los precios por los que tenga asginado el cliente?');

	var arr=$('select[name^="precio_"]');
	jQuery.each(arr, function() {
		nom=this.name;
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind = this.name.substring(pos+1);
			id  = Number(ind);
			this.selectedIndex=tipo;
			importe(id);
		}
	});
	totalizar();
}

function post_modbus_sinv(nind){
	ind=nind.toString();
	var tipo =Number($("#sclitipo").val()); if(tipo>0) tipo=tipo-1;
	$("#precio_"+ind).empty();
	var arr=$('#precio_'+ind);
	cdropdown(nind);
	cdescrip(nind);
	jQuery.each(arr, function() { this.selectedIndex=tipo; });
	importe(nind);
	totalizar();
}


function del_itscst(id){
	id = id.toString();
	$('#tr_itscst_'+id).remove();
	totalizar();
}

//Agrega el autocomplete
function autocod(id){
	$('#codigo_'+id).autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ventas/spre/buscasinv'); ?>",
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
			$('#itcosto_'+id).val(ui.item.pond);
			$('#itpvp_'+id).val(ui.item.ultimo);
			$('#cana_'+id).val('1');
			$('#cana_'+id).focus();
			$('#cana_'+id).select();

			var arr  = $('#preca_'+ind);
			var tipo = Number($("#sclitipo").val()); if(tipo>0) tipo=tipo-1;
			cdropdown(id);
			jQuery.each(arr, function() { this.selectedIndex=tipo; });
			importe(id);
			totalizar();
		}
	});
}
</script>
<?php } ?>

<table width='100%' align='center'>
	<tr>
		<td align=right>
			<?php echo $container_tr?>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%"  style="margin:0;width:100%;" cellspacing='2' cellpadding='2'>
				<tr>
					<td colspan=11 class="littletableheader">Encabezado</td>
				</tr>
				<tr>
					<td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
						<table>
							<tr>
								<td class="littletablerowth"><?=$form->fecha->label  ?></td>
								<td class="littletablerow">  <?=$form->fecha->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->numero->label  ?></td>
								<td class="littletablerow">  <?=$form->numero->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->tipo->label  ?></td>
								<td class="littletablerow">  <?=$form->tipo->output ?></td>
							</tr>
						</table>
					</fieldset>
					</td><td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
						<table>
							<tr>
								<td class="littletablerowth"><?=$form->orden->label  ?></td>
								<td class="littletablerow">  <?=$form->orden->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->cfis->label  ?></td>
								<td class="littletablerow">  <?=$form->cfis->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->almacen->label  ?></td>
								<td class="littletablerow">  <?=$form->almacen->output ?></td>
							</tr>
						</table>
					</fieldset>
					</td><td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
						<table>
							<tr>
								<td class="littletablerowth">          <?=$form->proveed->label  ?></td>
								<td colspan="3" class="littletablerow"><?=$form->proveed->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->nombre->label ?></td>
								<td colspan="3" class="littletablerow"><?=$form->nombre->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?=$form->vence->label ?></td>
								<td width="99" class="littletablerow"><?=$form->vence->output ?></td>
								<td width="44" class="littletablerow"><span class="littletablerowth">
								<? echo $form->peso->label ?></span></td>
								<td width="99" class="littletablerow" align='right'><?=$form->peso->output ?></td>
							</tr>
						</table>
					</fieldset>
					</td>
				</tr>
			</table>
		</tr>
	<tr>
</table>

		<div style='overflow:auto;border: 1px solid #9AC8DA;background: #FAFAFA;height:200px'>
		<table width='100%'>
			<tr id='__INPL__'>
				<th bgcolor='#7098D0'>C&oacute;digo     </th>
				<th bgcolor='#7098D0'>Descripci&oacute;n</th>
				<th bgcolor='#7098D0'>Cantidad          </th>
				<th bgcolor='#7098D0'>Precio            </th>
				<th bgcolor='#7098D0'>Importe           </th>
				<?php if($form->_status!='show') {?>
					<th bgcolor='#7098D0'>&nbsp;</th>
				<?php } ?>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itscst'];$i++) {
				$it_codigo  = "codigo_$i";
				$it_desca   = "descrip_$i";
				$it_cana    = "cantidad_$i";
				$it_precio  = "costo_$i";
				$it_importe = "importe_$i";
				//$it_iva     = "itiva_$i";
				//$it_tipo    = "sinvtipo_$i";
				//$it_peso    = "sinvpeso_$i";
			?>

			<tr id='tr_itsnte_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" ><?php echo $form->$it_codigo->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_desca->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_cana->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_precio->output; ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_importe->output;?></td>
				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itscst(<?=$i ?>);return false;'><?php echo img("images/delete.jpg");?></a>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</table>
		</div>
		<?php echo $container_bl ?>
		<?php echo $container_br ?>
		<br>

<table  width="100%" style="margin:0;width:100%;" > 
	  <tr>                                                           
	  	<td colspan=10 class="littletableheader">Totales</td>      
	 </tr>                                                          
	 <tr>
	  <td width="131" class="littletablerowth"><?=$form->rislr->label ?> </td>
		<td width="122" class="littletablerow" align='right'><?=$form->rislr->output    ?></td>
		<td width="125" class="littletablerowth">            <?=$form->anticipo->label  ?></td>
		<td width="125" class="littletablerow" align='right'><?=$form->anticipo->output ?></td>
		<td width="111" class="littletablerowth" >           <?=$form->montotot->label  ?></td>
		<td width="139" class="littletablerow" align='right'><?=$form->montotot->output ?></td>
	</tr><tr>
		<td class="littletablerowth">            <?=$form->montoiva->label ?></td>
		<td class="littletablerow" align='right'><?=$form->montoiva->output ?></td>
		<td class="littletablerowth">            <?=$form->inicial->label ?></td>
		<td class="littletablerow" align='right'><?=$form->inicial->output ?></td>
		<td class="littletablerowth">            <?=$form->montoiva->label ?></td>
		<td class="littletablerow" align='right'><?=$form->montoiva->output ?></td>
      </tr>
      <tr>
    <td class="littletablerowth" >               <?=$form->monto->label ?></td>
		<td class="littletablerow" align='right'><?=$form->monto->output ?></td>
    <td class="littletablerowth">                <?=$form->credito->label ?></td>
		<td class="littletablerow" align='right'><?=$form->credito->output ?></td>
		<td class="littletablerowth">            <?=$form->montonet->label ?></td>
		<td class="littletablerow" align='right'><?=$form->montonet->output ?></td>
      </tr>
</table>

<?php echo $form_end?>
	  <td>
	<tr>
<table>
<?php endif; ?>
