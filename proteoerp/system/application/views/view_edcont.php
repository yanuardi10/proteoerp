<?php
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itedcont');
$scampos  ='<tr id="tr_itedcont_<#i#>">';
$scampos .='<td class="littletablerow" align="center"><b id=\'giro_num_<#i#>\'></b></td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['it_vencimiento']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right" >'.$campos['it_monto']['field'].'</td>';
$scampos .='<td class="littletablerow"><a href=# onclick="del_itedcont(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$campos=$form->js_escape($scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var itedcont_cont=<?php echo $form->max_rel_count['itedcont'];?>;

$(function(){
	$("#edificacion").change(function(){ edif_change(''); });
	$("#inmueble").change(function(){ inmu_change(this.value); });
	$("#mt2,#precioxmt2").keypress(function(){ totalizar(); cmontos(); });
	$('#inicial').keyup(function(){ pagofinal(); });
	$('#financiable').keyup(function(){ pagofinal(); distrib(); });
	$('input[id^="it_monto"]').keyup(function(){ totagiro(); });

	$(".inputnum").numeric(".");
	totalizar();
	//for(var i=0;i < <?php echo $form->max_rel_count['itedcont']; ?>;i++){
	//	autocod(i.toString());
	//}

	$('#cliente').autocomplete({
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

			$('#cliente').val(ui.item.cliente);

			$('#rifci').val(ui.item.rifci);
			$('#rifci_val').text(ui.item.rifci);

			$('#sclitipo').val(ui.item.tipo);

			$('#direc').val(ui.item.direc);
			$('#direc_val').text(ui.item.direc);
		}
	});

	$('#numero_edres').autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscares'); ?>",
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

			$('#cliente').val(ui.item.cliente);
			$('#sclitipo').val(ui.item.tipo);

			$('#direc').val(ui.item.direc);
			$('#direc_val').text(ui.item.direc);

			$('#edificacion').val(ui.item.edifi);
			edif_change(ui.item.inmue);
			
			//$('#inmueble').val(ui.item.inmue);
		}
	});
});

function enumeragiro(){
	enu=0;
	arr=$('[id^="giro_num_"]');
	jQuery.each(arr, function() {
		enu+=1;
		$(this).text(enu);
	});
}

function pagofinal(){
	monto=Number($('#monto').val());
	inici=Number($('#inicial').val());
	finan=Number($('#financiable').val());

	firma=monto-inici-finan
	$('#firma').val(roundNumber(firma,2));
	$('#firma_val').text(nformat(firma,2));
}

function inmu_change(val){
	$.ajax({
		type: 'POST',
		url: "<?php echo site_url('construccion/common/get_dinmue'); ?>",
		dataType: 'json',
		data: "inmueble="+val,
		success: function(data){
			$('#mt2').val(data.area);
			$('#precioxmt2').val(data.preciomt2);

			totalizar();
			cmontos();
		}
	});
}

function edif_change(par){
	$.post("<?php echo site_url('construccion/common/get_inmue'); ?>",{ edif:$("#edificacion").val() },
		function(data){
			$("#inmueble").html(data);
			if(par.length>0){
				$("#inmueble").val(par);
				inmu_change(par);
			}
		})
}

function truncate(){
	$('tr[id^="tr_edcont_"]').remove();
	itedcont_cont=0;
}

//Determina lo que falta por pagar
function faltante(){
	totalg=Number($("#totalg").val());
	paga  = apagar();
	resto = totalg-paga;
	return resto;
}

function totalizar(){
	mts    = Number($('#mt2').val());
	precio = Number($('#precioxmt2').val());
	$('#monto').val(roundNumber(mts*precio,2));
	$('#monto_val').text(nformat(mts*precio,2));
}

//Calcula los montos iniciales,financiables y finales
function cmontos(){
	var monto=Number($('#monto').val());
	var inici=monto*0.2;
	var finan=monto*0.3;
	var firma=monto*0.5;

	$('#inicial').val(roundNumber(inici,2));
	$('#financiable').val(roundNumber(finan,2));
	$('#firma').val(roundNumber(firma,2));
	$('#firma_val').text(nformat(firma,2));
	distrib();
}

//Distribuye el monto financiable entre los giros
function distrib(){
	var arr=$('input[id^="it_vencimiento_"]');
	var finan=Number($('#financiable').val());
	var giros=$('input[id^="it_monto"]');
	cgiro=giros.length;
	giros.val(roundNumber(finan/cgiro,2));

	inicio=arr.first().val();

	year =Number(inicio.slice(-4));
	month=Number(inicio.slice(3,5))-1;
	day  =Number(inicio.slice(0,2));

	var i=0;
	jQuery.each(arr, function(){
		f = new Date(year, month+i, day);
		//d=f.getDay();
		//m=f.getMonth();
		//a=f.getFullYear();
		//$(this).val(d.toString()+'/'+m.toString()+'/'+a.toString());
		$(this).val(f.toLocaleDateString());
		i+=1;
	});
}

//Calcula el total de los giros
function totagiro(){
	var giros=$('input[id^="it_monto"]');
	var mgiros=0;

	jQuery.each(giros, function(){
		mgiros+=Number($(this).val());
	});
	$('#financiable').val(roundNumber(mgiros,2));
	pagofinal();
	
}

function add_itedcont(){
	var htm = <?php echo $campos; ?>;
	can = itedcont_cont.toString();
	con = (itedcont_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	itedcont_cont += 1;
	distrib();
	enumeragiro();
	$('#it_monto_'+can).keyup(function(){ totagiro(); });
	$('#it_monto_'+can).numeric(".");
	return can;
}

function del_itedcont(id){
	id=id.toString();
	$('#tr_itedcont_'+id).remove();
	distrib();
	var arr = $('input[id^="it_monto_"]');
	if(arr.length<=0){
		add_itedcont();
	}
	enumeragiro();
}
</script>
<?php } ?>
<table align='center' width="95%">
	<tr>
		<td align=right><?php echo $container_tr?></td>
	</tr>
</table>
<table align='center' width="100%">
	<tr>
		<td>
		<table width='100%'>
		<tr><td style="width:50%">
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9; min-height:105px;'>
			<legend class="titulofieldset" style='color: #114411;'>Documento</legend>
			<table style="margin: 0;">
			<tr>
				<td class="littletableheader"><?php echo $form->fecha->label;     ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->fecha->output;    ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->numero->label;    ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->numero->output;   ?>&nbsp; </td>
			</tr><tr>
				<td class="littletableheader"><?php echo $form->edificacion->label;   ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->edificacion->output;  ?>&nbsp; </td>
				<td class="littletableheader"><?php echo $form->mt2->label;    ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->mt2->output;   ?>&nbsp; </td>
			</tr><tr>
				<td class="littletableheader"><?php echo $form->inmueble->label;  ?>&nbsp;</td>
				<td class="littletablerow"   ><?php echo $form->inmueble->output; ?>&nbsp;</td>
				<td class="littletableheader"><?php echo $form->precioxmt2->label;   ?>&nbsp;</td>
				<td class="littletablerow"   ><?php echo $form->precioxmt2->output;  ?>&nbsp;</td>
			</tr>
			<!--<tr>
				<td class="littletableheader"><?php echo $form->notas->label;  ?>&nbsp; </td>
				<td class="littletablerow">   <?php echo $form->notas->output; ?>&nbsp; </td>
			</tr>-->
			</table>
			</fieldset>
		</td><td style="width:50%">
			<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9; min-height:105px;'>
			<legend class="titulofieldset" style='color: #114411;'>Cliente</legend>
			<table style="margin: 0;">
			<tr>
				<td class="littletableheader"><?php echo $form->cliente->label;  ?>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->cliente->output.$form->sclitipo->output; ?>&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->nombre->output;  ?>&nbsp;</td>
			</tr><tr>
				<td class="littletableheader">         <?php echo $form->rifci->label; ?>&nbsp;</td>
				<td class="littletablerow" colspan='2'><?php echo $form->rifci->output;   ?>&nbsp;</td>
			</tr><tr>
				<td class="littletableheader">         <?php echo $form->direc->label  ?>&nbsp;</td>
				<td class="littletablerow" colspan='2'><?php echo $form->direc->output ?>&nbsp;</td>
			</tr><tr>
				<td class="littletableheader">         <?php echo $form->numero_edres->label;    ?>&nbsp;</td>
				<td class="littletablerow" colspan='2'><?php echo $form->numero_edres->output;   ?>&nbsp; </td>
			</tr>
			</table>
			</fieldset>
		</td></tr>
		</table>
		</td>
	</tr><tr>
		<td>
		<!--<div style='overflow:auto;border: 1px solid #9AC8DA;background: #FAFAFA;height:200px'>-->
		<table width='100%' border='0'>
			<tr id='__INPL__'>
				<td class="littletableheaderdet"><b>Giros</b></td>
				<td class="littletableheaderdet"><b>Vencimiento</b></td>
				<td class="littletableheaderdet"><b>Monto</b></td>
				<?php if($form->_status!='show') {?>
					<td bgcolor='#7098D0'>&nbsp;</td>
				<?php } ?>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itedcont'];$i++) {
				$it_vencimiento  = "it_vencimiento_$i";
				$it_monton       = "it_monto_$i";
			?>

			<tr id='tr_itedcont_<?php echo $i; ?>'>
				<td class="littletablerow" align="center"><b id='giro_num_<?php echo $i; ?>'><?php echo $i+1; ?></b></td>
				<td class="littletablerow" align="left"  ><?php echo $form->$it_vencimiento->output; ?></td>
				<td class="littletablerow" align="right" ><?php echo $form->$it_monton->output;   ?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow">
					<a href='#' onclick='del_itedcont(<?php echo $i ?>);return false;'><?php echo img("images/delete.jpg"); ?></a>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
			<tr id='__UTPL__'>
				<td id='cueca'></td>
			</tr>
		</table>
		<!-- </div>-->
		<?php echo $container_bl ?>
		<?php echo $container_br ?>
		</td>
	</tr><tr>
		<td>
		<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
		<table width='100%'>
			<tr>
				<td class="littletableheader" width='100'></td>
				<td class="littletablerow"    width='350'></td>
				<td class="littletableheader">           <?php echo $form->inicial->label;  ?></td>
				<td class="littletablerow" align='right'><?php echo $form->inicial->output; ?></td>
			<tr>
			<tr>
				<td class="littletableheader" width='100'></td>
				<td class="littletablerow"    width='350'></td>
				<td class="littletableheader">           <?php echo $form->financiable->label;  ?></td>
				<td class="littletablerow" align='right'><?php echo $form->financiable->output; ?></td>
			<tr>
			<tr>
				<td class="littletableheader" width='100'></td>
				<td class="littletablerow"    width='350'></td>
				<td class="littletableheader">           <?php echo $form->firma->label;  ?></td>
				<td class="littletablerow" align='right'><?php echo $form->firma->output; ?></td>
			<tr>
			<tr>
				<td class="littletableheader" width='100'></td>
				<td class="littletablerow"    width='350'></td>
				<td class="littletableheader">           <?php echo $form->monto->label;  ?></td>
				<td class="littletablerow" align='right'><?php echo $form->monto->output; ?></td>
			<tr>
		</table>
		</fieldset>
		</td>
	</tr>
</table>
<?php echo $form_end; ?>
<?php endif; ?>