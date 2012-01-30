<?php
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

//print_r($form->_button_container['BL']);
//print_r($form->_button_container['BR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos   = $form->template_details('ordpindi');
$scampos  = '<tr id="tr_ordpindi_<#i#>">';
$scampos .= '<td class="littletablerow" align="left" >'.$campos['it1_codigo']['field'].'</td>';
$scampos .= '<td class="littletablerow" align="left" >'.$campos['it1_descrip']['field'].'</td>';
$scampos .= '<td class="littletablerow" align="right">'.$campos['it1_porcentaje']['field'].'</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_ordpindi(<#i#>);return false;">'.img("images/delete.jpg").'</a></td>';
$scampos .= '</tr>';
$ordpindi_campos=$form->js_escape($scampos);

$campos   = $form->template_details('ordpitem');
$scampos  = '<tr id="tr_ordpitem_<#i#>">';
$scampos .= '<td class="littletablerow" align="left" >'.$campos['it2_codigo']['field'].'</td>';
$scampos .= '<td class="littletablerow" align="left" >'.$campos['it2_descrip']['field'].'</td>';
$scampos .= '<td class="littletablerow" align="right">'.$campos['it2_cantidad']['field'].'</td>';
$scampos .= '<td class="littletablerow" align="right">'.$campos['it2_merma']['field'].'%</td>';
$scampos .= '<td class="littletablerow" align="right">'.$campos['it2_costo']['field'].'</td>';
$scampos .= '<td class="littletablerow"><a href=# onclick="del_ordpitem(<#i#>);return false;">'.img("images/delete.jpg").'</a></td>';
$scampos .= '</tr>';
$ordpitem_campos=$form->js_escape($scampos);

$campos   = $form->template_details('ordplabor');
$scampos  = '<tr id="tr_ordplabor_<#i#>">';
$scampos .= '<td class="littletablerow" align="left" >'.$campos['it3_id']['field'].$campos['it3_secuencia']['field'].'</td>';
$scampos .= '<td class="littletablerow" align="left" >'.$campos['it3_estacion']['field'].'</td>';
$scampos .= '<td class="littletablerow" align="left" >'.$campos['it3_actividad']['field'].'</td>';
$scampos .= '<td class="littletablerow" align="right">'.$campos['it3_tunidad']['field'].'</td>';
$scampos .= '<td class="littletablerow" align="right">'.$campos['it3_tiempo']['field'].'</td>';
$scampos .= '<td class="littletablerow">';
$scampos .= '<table><td><a href=# onclick="updownlabor(<#i#>,-1);return false;"><span class="ui-icon ui-icon-triangle-1-n"/></a>';
$scampos .= '<a href=# onclick="updownlabor(<#i#>, 1);return false;"><span class="ui-icon ui-icon-triangle-1-s"/></a></td><td>';
$scampos .= '<a href=# onclick="del_ordplabor(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></table>';
$scampos .= '</td></tr>';
$ordplabor_campos=$form->js_escape($scampos);

$anulado = 'N';
if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
var ordpindi_cont  = <?php echo $form->max_rel_count['ordpindi'];  ?>;
var ordpitem_cont  = <?php echo $form->max_rel_count['ordpitem'];  ?>;
var ordplabor_cont = <?php echo $form->max_rel_count['ordplabor']; ?>;

$(function(){
	$(".inputnum").numeric(".");
	$(".inputonlynum").numeric();
	for(var i=0;i < <?php echo $form->max_rel_count['ordpitem']; ?>;i++){
		ind= i.toString();
		autocod(ind);
	}
	for(var i=0;i < <?php echo $form->max_rel_count['ordpindi']; ?>;i++){
		ind= i.toString();
		autocodmgas(ind)
	}

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
						if(data.length==0){
							$('#nombre').val('');
							$('#nombre_val').text('');
							//$('#rifci').val(''); $('#rifci_val').text('');
						}else{
							$.each(data,
								function(i, val){
									sugiere.push( val );
								}
							);
						}
						add(sugiere);
					},
			})
		},
		minLength: 1,
		select: function( event, ui ) {
			$('#cliente').val(ui.item.cod_cli);
			$('#nombre').val(ui.item.nombre);
			$('#nombre_val').text(ui.item.nombre);
			//$('#rif').val(ui.item.rifci); $('#rif_val').text(ui.item.rifci);
		}
	});

	$('#codigo').autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscasinv'); ?>",
				type: "POST",
				dataType: "json",
				data: "q="+encodeURIComponent(req.term),
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
			$('#codigo').val(ui.item.codigo);
			$('#desca_val').text(ui.item.descrip);

			truncate();
			$.ajax({
				url: "<?php echo site_url('ajax/buscaordpitem'); ?>",
				dataType: 'json',
				type: 'POST',
				data: "q="+ui.item.value,
				success: function(data){
						$.each(data,
							function(id, val){
								add_ordpitem();
								$('#codigoa_'+id).val(val.codigo);
								$('#it2descrip_'+id).val(val.descrip);
								$('#it2descrip_'+id+'_val').text(val.descrip);
								$('#it2costo_'+id).val(val.ultimo);
								$('#it2merma_'+id).val(val.merma);
								$('#it2cantidad_'+id).val(val.cantidad);
								$('#it2codigo_'+id).val(val.codigo);
								$("#it2_cantidad_"+id).focus();
							}
						);
					},
			});

			$.ajax({
				url: "<?php echo site_url('ajax/buscaordplabor'); ?>",
				dataType: 'json',
				type: 'POST',
				data: "q="+ui.item.value,
				success: function(data){
						$.each(data,
							function(id, val){
								add_ordplabor();
								$("#it3estacion_"+id).val(val.estacion);
								$("#it3nombre_"+id).val(val.nombre);
								$("#it3actividad_"+id).val(val.actividad);
								$("#it3tunidad_"+id).val(val.tunidad);
								$("#it3tiempo_"+id).val(val.tiempo);
							}
						);
					},
			});

		}
	});
	enumeralabor();
});

function add_ordpindi(){
	var htm = <?php echo $ordpindi_campos; ?>;
	can = ordpindi_cont.toString();
	con = (ordpindi_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__INPL__ordpindi").after(htm);
	$("#it1porcentaje_"+can).numeric(".");
	$("#it1codigo_"+can).focus();
	 autocodmgas(can);
	ordpindi_cont=ordpindi_cont+1;
}

function add_ordpitem(){
	var htm = <?php echo $ordpitem_campos; ?>;
	can = ordpitem_cont.toString();
	con = (ordpitem_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__INPL__ordpitem").after(htm);
	$("#it2cantidad_"+can).numeric();
	$("#it2merma_"+can).numeric(".");
	$("#it2merma_"+can).val('0');
	$("#it2costo_"+can).numeric(".");
	$("#it2codigo_"+can).focus();
	autocod(can);
	ordpitem_cont=ordpitem_cont+1;
}

function add_ordplabor(){
	var htm = <?php echo $ordplabor_campos; ?>;
	can = ordplabor_cont.toString();
	con = (ordplabor_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__INPL__ordplabor").before(htm);
    $("#it3tiempo_"+can).numeric(".");
    $("#it3estacion_"+can).focus();
    enumeralabor();
	ordplabor_cont=ordplabor_cont+1;
}

function ordlabor(id){
	var html=$('#tr_ordpindi_'+id).html();
}

function del_ordpindi(id){
	id = id.toString();
	$('#tr_ordpindi_'+id).remove();
}

function del_ordpitem(id){
	id = id.toString();
	$('#tr_ordpitem_'+id).remove();
}

function del_ordplabor(id){
	id = id.toString();
	$('#tr_ordplabor_'+id).remove();
	enumeralabor();
}
function truncate(){
	$('tr[id^="tr_ordplabor_"]').remove();
	$('tr[id^="tr_ordpitem_"]').remove();
	ordpitem_cont=ordplabor_cont=0;
}

function autocod(id){
	$('#it2codigo_'+id).autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscasinv'); ?>",
				type: "POST",
				dataType: "json",
				data: "q="+encodeURIComponent(req.term),
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
			$('#codigoa_'+id).val(ui.item.codigo);
			$('#it2descrip_'+id).val(ui.item.descrip);
			$('#it2merma_'+id).val('0');
			$('#it2descrip_'+id+'_val').text(ui.item.descrip);
			$('#it2costo_'+id).val(ui.item.ultimo);
			$("#it2cantidad_"+can).focus();
		}
	});
}

function autocodmgas(id){
	$('#it1codigo_'+id).autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/automgas'); ?>",
				type: "POST",
				dataType: "json",
				data: "q="+encodeURIComponent(req.term),
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
			$('#it1codigo_'+id).val(ui.item.codigo);
			$('#it1descrip_'+id).val(ui.item.descrip);
			$('#it1descrip_'+id+'_val').text(ui.item.descrip);
			$('#it1porcentaje_'+id).focus();
		}
	});
}

function updownlabor(id,direc){
	ind=id.toString();
	var actu=$('#tr_ordplabor_'+ind);
	var it3estacion  = $('#it3estacion_'+ind).val();
	var it3actividad = $('#it3actividad_'+ind).val();
	var it3minutos   = $('#it3minutos_'+ind).val();
	var it3segundos  = $('#it3segundos_'+ind).val();
	var htm = '<tr id="tr_ordplabor_'+ind+'">'+actu.html()+'<tr>';
	var enu = 0;
	var arr = $('[id^="tr_ordplabor_"]');
	var eoa = arr.length-1;
	var rt  = false;
	jQuery.each(arr, function() {
		if($(this).attr('id')=='tr_ordplabor_'+ind) return false;
		enu+=1;
	});
	if(enu>0)  { ante=arr[enu-1]; p_ante=1; } else {p_ante=-1; }
	if(enu<eoa){ prox=arr[enu+1]; p_prox=1; } else {p_prox=-1; }

	if(direc>0 && p_prox>0){      //si baja
		$('#tr_ordplabor_'+id).remove();
		$("#"+$(prox).attr('id')).after(htm);
		rt = true;
	}else if(direc<0 && p_ante>0){ //si sube
		$('#tr_ordplabor_'+id).remove();
		$("#"+$(ante).attr('id')).before(htm);
		rt = true;
	}

	if(rt){
		$('#it3estacion_'+ind).val(it3estacion);
		$('#it3actividad_'+ind).val(it3actividad);
		$('#it3minutos_'+ind).val(it3minutos);
		$('#it3segundos_'+ind).val(it3segundos);
		enumeralabor();
	}
	return rt;
}

function enumeralabor(){
	var enu=0;
	var arr=$('[id^="it3secuencia_"]').not('[id$="_val"]');
	jQuery.each(arr, function() {
		enu+=1;
		$(this).val(enu);
		$('#'+this.name+'_val').text(enu);
	});
}
</script>
<?php } ?>

<table width='100%' align='center'>

	<?php if($form->_status=='show'){ ?>
	<tr>
		<td valign="bottom">
			<a href="javascript:void(0);" onclick="window.open('<?php echo base_url(); ?>/formatos/verhtml/ORDP/<?php echo $form->get_from_dataobjetct('id'); ?>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');">
			<img src="<?php echo base_url(); ?>/images/reportes.gif" alt="Imprimir Documento" title="Imprimir Documento" border="0" height="30"></a>
		</td>
	</tr>
	<?php } ?>

	<tr>
		<td align=right>
			<?php echo $container_tr; ?>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%"  style="margin:0;width:100%;" cellspacing='2' cellpadding='2'>
				<tr>
					<td colspan=11 class="littletableheader"><?php echo ($anulado=='S')? '<b style=\'color:red;\'>Documento Anulado<b>' : 'Orden de producci&oacute;n '.$form->numero->value; ?></td>
				</tr>
				<tr>
					<td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;  min-height:70px;'>
						<table>
							<tr>
								<td class="littletablerowth"><?php echo $form->fecha->label  ?>*</td>
								<td class="littletablerow">  <?php echo $form->fecha->output?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->status->label  ?>*</td>
								<td class="littletablerow">  <?php echo $form->status->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->almacen->label  ?>*</td>
								<td class="littletablerow">  <?php echo $form->almacen->output ?></td>
							</tr>
						</table>
					</fieldset>
					</td><td>
					<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;  min-height:70px;'>
						<table>
							<tr>
								<td class="littletablerowth"><?php echo $form->cliente->label  ?>*</td>
								<td class="littletablerow">  <?php echo $form->cliente->output.$form->nombre->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->codigo->label   ?>*</td>
								<td class="littletablerow">  <?php echo $form->codigo->output.$form->desca->output ?></td>
							</tr><tr>
								<td class="littletablerowth"><?php echo $form->cana->label   ?>*</td>
								<td class="littletablerow">  <?php echo $form->cana->output  ?></td>
							</tr>
						</table>
					</fieldset>
					</td>
				</tr>
				<tr>
					<td colspan=2><?php echo $form->instrucciones->output ?></td>
				</tr>
			</table>

		</tr>
	<tr>
</table>

<h3>Insumos necesarios</h3>
<table width='100%'>
	<tr id='__INPL__ordpitem'>
		<th bgcolor='#7098D0'>C&oacute;digo</th>
		<th bgcolor='#7098D0'>Descripci&oacute;n</th>
		<th bgcolor='#7098D0'>Cantidad</th>
		<th bgcolor='#7098D0'>% Merma</th>
		<th bgcolor='#7098D0'>Costo</th>
		<?php if($form->_status!='show') {?>
			<th bgcolor='#7098D0'>&nbsp;</th>
		<?php } ?>
	</tr>
	<?php for($i=0;$i<$form->max_rel_count['ordpitem'];$i++) {
		$it2_codigo   = "it2_codigo_$i";
		$it2_descrip  = "it2_descrip_$i";
		$it2_cantidad = "it2_cantidad_$i";
		$it2_merma    = "it2_merma_$i";
		$it2_costo    = "it2_costo_$i";
	?>
	<tr id='tr_ordpitem_<?php echo $i; ?>'>
		<td class="littletablerow" align="left" ><?php echo $form->$it2_codigo->output;   ?></td>
		<td class="littletablerow" align="left" ><?php echo $form->$it2_descrip->output;  ?></td>
		<td class="littletablerow" align="right"><?php echo $form->$it2_cantidad->output; ?></td>
		<td class="littletablerow" align="right"><?php echo $form->$it2_merma->output;    ?>%</td>
		<td class="littletablerow" align="right"><?php echo $form->$it2_costo->output;    ?></td>
		<?php if($form->_status!='show') {?>
			<td class="littletablerow"><a href=# onclick="del_ordpitem(<?php echo $i ?>);return false;"><?php echo img("images/delete.jpg"); ?></a></td>
		<?php } ?>
	</tr>
	<?php } ?>
</table>
<?php
if($form->_status=='show') {
	echo anchor('/formatos/ver/ORDPITEM/'.$form->get_from_dataobjetct('id'),'Ver lista');
}
echo isset($form->_button_container['BL'][1])? $form->_button_container['BL'][1]: ''; ?>
<h3>Actividades o labores a realizar</h3>
<table width='100%'>
	<tr>
		<th bgcolor='#7098D0'>Secu.</th>
		<th bgcolor='#7098D0'>Estaci&oacute;n</th>
		<th bgcolor='#7098D0'>Actividad</th>
		<th bgcolor='#7098D0'>U. Tiempo</th>
		<th bgcolor='#7098D0'>Tiempo</th>
		<th bgcolor='#7098D0'>&nbsp;</th>
	</tr>
	<?php for($i=0;$i<$form->max_rel_count['ordplabor'];$i++) {
		$it3_id        = "it3_id_$i";
		$it3_secuencia = "it3_secuencia_$i";
		$it3_estacion  = "it3_estacion_$i";
		$it3_actividad = "it3_actividad_$i";
		$it3_tunidad   = "it3_tunidad_$i";
		$it3_tiempo    = "it3_tiempo_$i";
	?>
	<tr id='tr_ordplabor_<?php echo $i; ?>'>
		<td class='littletablerow' align="left" ><?php echo $form->$it3_id->output.$form->$it3_secuencia->output; ?></td>
		<td class='littletablerow' align="left" ><?php echo $form->$it3_estacion->output;  ?></td>
		<td class='littletablerow' align="left" ><?php echo $form->$it3_actividad->output; ?></td>
		<td class='littletablerow' align="center"><?php echo $form->$it3_tunidad->output;   ?></td>
		<td class='littletablerow' align="right"><?php echo $form->$it3_tiempo->output;    ?> </td>
		</td>
		<?php if($form->_status!='show') {?>
			<td class="littletablerow">
				<table>
					<td>
						<a href=# onclick="updownlabor(<?php echo $i ?>,-1);return false;"><span class="ui-icon ui-icon-triangle-1-n"/></a>
						<a href=# onclick="updownlabor(<?php echo $i ?>, 1);return false;"><span class="ui-icon ui-icon-triangle-1-s"/></a>
					</td><td>
						<a href=# onclick="del_ordplabor(<?php echo $i ?>);return false;"><?php echo img("images/delete.jpg"); ?></a>
					</td>
				</table>
			</td>
		<?php }else{
			$id = $form->get_from_dataobjetct('id');
			$id_rel = $form->get_from_dataobjetct_rel('ordplabor','id',$i);
			?>
		<td class="littletablerow">
			<table><tr>
				<?php
					$sel=array('a.status','fechahora','id');
					$this->db->select($sel);
					$this->db->from('ordpbita AS a');
					$this->db->where('a.id_ordplabor',$id_rel);
					$this->db->orderby('a.estampa','desc');
					$this->db->limit(1);
					$query=$this->db->get();

					$rt=array();
					if ($query->num_rows() > 0){
						$row = $query->row_array();

						if($row['status']=='I'){
							$rt['muestra']='T,P,H';
						}elseif($row['status']=='P'){
							$rt['muestra']='I,H';
						}else{
							$rt['muestra']='H';
						}
						$rt['ultimo'] = $row['fechahora'];
						$rt['id']    = $row['id'];
					}else{
						$rt['muestra']='I';
					}
				?>
				<?php if(strpos($rt['muestra'],'H')!==false){ ?><td><a href="<?php echo site_url('inventario/stra/dataeditordp/'.$form->numero->value.'/'.$form->$it3_estacion->value.'/create'); ?>"><span title='Mover insumos'  class="ui-icon ui-icon-cart"   /></a></td><?php } ?>
				<?php if(strpos($rt['muestra'],'I')!==false){ ?><td><a href="<?php echo site_url($this->url.'ordpbita/'.$id_rel.'/'.$id.'/I/create'); ?>"><span title='Iniciar actividad'  class="ui-icon ui-icon-play"   /></a></td><?php } ?>
				<?php if(strpos($rt['muestra'],'P')!==false){ ?><td><a href="<?php echo site_url($this->url.'ordpbita/'.$id_rel.'/'.$id.'/P/create'); ?>"><span title='Pausar actividad'   class="ui-icon ui-icon-pause"  /></a></td><?php } ?>
				<?php if(strpos($rt['muestra'],'T')!==false){ ?><td><a href="<?php echo site_url($this->url.'ordpbita/'.$id_rel.'/'.$id.'/T/create'); ?>"><span title='Terminar actividad' class="ui-icon ui-icon-check"  /></a></td><?php } ?>
				<?php if(strpos($rt['muestra'],'H')!==false){ ?><td><a href="<?php echo site_url($this->url.'ordpbitafilter/'.$id_rel.'/'.$id); ?>"><span title='Ver bitacora' class="ui-icon ui-icon-extlink"/></a></td><?php } ?>
				<?php if( isset($rt['ultimo'])){
					echo '<td>'.anchor('/formatos/ver/ORDPBITA/'.$rt['id'],dbdate_to_human($rt['ultimo'])).'</td>';
				} ?>
			</tr></table>
		</td>
		<?php } ?>
	</tr>
	<?php } ?>
	<tr id='__INPL__ordplabor'>
		<td colspan=5></td>
	</tr>
</table>
<?php echo isset($form->_button_container['BL'][2])? $form->_button_container['BL'][2]: ''; ?>

<h3>Gastos indirectos</h3>
<table width='100%'>
	<tr id='__INPL__ordpindi'>
		<th bgcolor='#7098D0'>C&oacute;digo</th>
		<th bgcolor='#7098D0'>Descripci&oacute;n</th>
		<th bgcolor='#7098D0'>% Participaci&oacute;n</th>
		<?php if($form->_status!='show') {?>
			<th bgcolor='#7098D0'>&nbsp;</th>
		<?php } ?>
	</tr>
	<?php for($i=0;$i<$form->max_rel_count['ordpindi'];$i++) {
		$it1_codigo    = "it1_codigo_$i";
		$it1_descrip   = "it1_descrip_$i";
		$it1_porcentaje= "it1_porcentaje_$i";
	?>
	<tr id='tr_ordpindi_<?php echo $i; ?>'>
		<td class="littletablerow" align="left" ><?php echo $form->$it1_codigo->output;     ?></td>
		<td class="littletablerow" align="left" ><?php echo $form->$it1_descrip->output;    ?></td>
		<td class="littletablerow" align="right"><?php echo $form->$it1_porcentaje->output; ?></td>
		<?php if($form->_status!='show') { ?>
			<td class="littletablerow"><a href=# onclick="del_ordpindi(<?php echo $i ?>);return false;"><?php echo img('images/delete.jpg'); ?></a></td>
		<?php } ?>
	</tr>
	<?php } ?>
</table>
<?php echo isset($form->_button_container['BL'][0])? $form->_button_container['BL'][0]: ''; ?>
<?php
echo $container_br;
echo $form_end
?>

<?php if($form->_status=='show'){
	$transac=$form->get_from_dataobjetct('transac');
?>

<?php  } ?>
<?php endif; ?>
