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
$scampos .='<td class="littletablerow" align="left" ><b id="it_descrip_val_<#i#>"></b>'.$campos['descrip']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['cantidad']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['costo']['field']. '</td>';
$scampos .='<td class="littletablerow" align="right"><b id="it_importe_val_<#i#>">'.nformat(0).'</b>'.$campos['importe']['field'];
$scampos .= $campos['sinvpeso']['field'].$campos['iva']['field'].'</td>';
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
		autocod(i.toString());
	}
});

function importe(id){
	var ind     = id.toString();
	var cana    = Number($("#cantidad_"+ind).val());
	var precio  = Number($("#costo_"+ind).val());
	var importe = roundNumber(cana*precio,2);
	$("#importe_"+ind).val(importe);
	$("#it_importe_val_"+ind).text(nformat(importe,2));
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
			itiva   = Number($("#iva_"+ind).val());
			importe = Number(this.value);
			itpeso  = Number($("#sinvpeso_"+ind).val());

			peso    = peso+(itpeso*cana);
			iva     = iva+importe*(itiva/100);
			totals  = totals+importe;
		}
	});
	$("#peso").val(roundNumber(peso,2));
	$("#montonet").val(roundNumber(totals+iva,2));
	$("#montotot").val(roundNumber(totals,2));
	$("#montoiva").val(roundNumber(iva,2));

	$("#peso_val").text(nformat(peso,2));
	$("#montonet_val").text(nformat(totals+iva,2));
	$("#montotot_val").text(nformat(totals,2));
	$("#montoiva_val").text(nformat(iva,2));
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
	autocod(can);
	$('#codigo_'+can).focus();

	itscst_cont=itscst_cont+1;
}

function post_modbus_sinv(nind){
	ind=nind.toString();
	var cana=Number($("#cantidad_"+ind).val());
	if(cana<=0) $("#cantidad_"+ind).val(1);
	$('#cantidad_'+ind).focus();
	$('#cantidad_'+ind).select();
	$('#it_descrip_val_'+ind).text($('#descrip_'+ind).val());
	importe(nind);
	totalizar();
}

function post_modbus_sprv(){
	$('#nombre_val').text($('#nombre').val());
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
			var cana=Number($("#cantidad_"+ind).val());
			$('#codigo_'+id).val(ui.item.codigo);
			$('#descrip_'+id).val(ui.item.descrip);
			$('#it_descrip_val_'+id).text(ui.item.descrip);
			$('#iva_'+id).val(ui.item.iva);
			$('#sinvpeso_'+id).val(ui.item.peso);
			$('#costo_'+id).val(ui.item.pond);
			if(cana<=0) $("#cantidad_"+ind).val(1);
			$('#cantidad_'+id).focus();
			$('#cantidad_'+id).select();
			//post_modbus_sinv(parseInt(id));

			importe(parseInt(id));
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
								<td class="littletablerowth"><?php echo $form->fecha->label  ?></td>
								<td class="littletablerow">  <?php echo $form->fecha->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->serie->label  ?></td>
								<td class="littletablerow">  <?php echo $form->serie->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->tipo->label  ?></td>
								<td class="littletablerow">  <?php echo $form->tipo->output ?></td>
							</tr>
						</table>
					</fieldset>
					</td><td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
						<table>
							<tr>
								<td class="littletablerowth"><?php echo $form->orden->label  ?></td>
								<td class="littletablerow">  <?php echo $form->orden->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->cfis->label  ?>*</td>
								<td class="littletablerow">  <?php echo $form->cfis->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->almacen->label  ?>*</td>
								<td class="littletablerow">  <?php echo $form->almacen->output ?></td>
							</tr>
						</table>
					</fieldset>
					</td><td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
						<table>
							<tr>
								<td class="littletablerowth"><?php echo $form->proveed->label  ?>*</td>
								<td class="littletablerow">  <?php echo $form->proveed->output ?><b id='nombre_val'><?php echo $form->nombre->value ?></b><?php echo $form->nombre->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->vence->label ?></td>
								<td class="littletablerow">  <?php echo $form->vence->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><? echo $form->peso->label ?></td>
								<td class="littletablerow" align='right'><b id='peso_val'><?php echo $form->peso->value ?></b><?php echo $form->peso->output ?></td>
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
				$it_peso    = "sinvpeso_$i";
				$it_iva     = "iva_$i";
				//$it_tipo    = "sinvtipo_$i";
			?>

			<tr id='tr_itsnte_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" ><?php echo $form->$it_codigo->output; ?></td>
				<td class="littletablerow" align="left" ><b id='it_descrip_val_<?php echo $i; ?>'><?php echo $form->$it_desca->value; ?></b>
				<?php echo $form->$it_desca->output;  ?>
				</td>
				<td class="littletablerow" align="right"><?php echo $form->$it_cana->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_precio->output; ?></td>
				<td class="littletablerow" align="right"><b id='it_importe_val_<?php echo $i; ?>'><?php echo nformat($form->$it_importe->value);?></b><?php echo $form->$it_importe->output; ?>
				<?php echo $form->$it_peso->output.$form->$it_iva->output; ?>
				</td>
				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itscst(<?php echo $i ?>);return false;'><?php echo img("images/delete.jpg");?></a>
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
		<td width="131" class="littletablerowth" align='right'><?php echo $form->rislr->label     ?></td>
		<td width="122" class="littletablerow"   align='right'><?php echo $form->rislr->output    ?></td>
		<td width="125" class="littletablerowth" align='right'><?php echo $form->anticipo->label  ?></td>
		<td width="125" class="littletablerow"   align='right'><?php echo $form->anticipo->output ?></td>
		<td width="111" class="littletablerowth" align='right'><?php echo $form->montotot->label  ?></td>
		<td width="139" class="littletablerow"   align='right'><b id='montotot_val'><?php echo $form->montotot->value ?></b><?php echo $form->montotot->output ?></td>
	</tr>
	<tr>
		<td class="littletablerowth" align='right'><?php echo $form->riva->label   ?></td>
		<td class="littletablerow"   align='right'><?php echo $form->riva->output  ?></td>
		<td class="littletablerowth" align='right'><?php echo $form->inicial->label   ?></td>
		<td class="littletablerow"   align='right'><?php echo $form->inicial->output  ?></td>
		<td class="littletablerowth" align='right'><?php echo $form->montoiva->label  ?></td>
		<td class="littletablerow"   align='right'><b id='montoiva_val'><?php echo nformat($form->montoiva->value); ?><b><?php echo $form->montoiva->output ?></td>
	</tr>
	<tr>
		<td class="littletablerowth" align='right'><?php echo $form->monto->label    ?></td>
		<td class="littletablerow"   align='right'><?php echo $form->monto->output   ?></td>
		<td class="littletablerowth" align='right'><?php echo $form->credito->label  ?></td>
		<td class="littletablerow"   align='right'><?php echo $form->credito->output ?></td>
		<td class="littletablerowth" align='right'><?php echo $form->montonet->label ?></td>
		<td class="littletablerow"   align='right'><b id='montonet_val' style='font-size:18px;font-weight: bold' ><?php echo nformat($form->montonet->value); ?></b><?php echo $form->montonet->output ?></td>
	</tr>
</table>

<?php echo $form_end?>
	  <td>
	<tr>
<table>
<?php endif; ?>
