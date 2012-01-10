<?php
echo $scri;
echo $form_begin;

$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);

if ($form->_status=='modify'){
	$container_co=join('&nbsp;', $form->_button_status[$form->_status]['CO']);
	$container_it=join('&nbsp;', $form->_button_status[$form->_status]['IT']);
	$container_la=join('&nbsp;', $form->_button_status[$form->_status]['LA']);
}elseif ($form->_status=='create'){
	$container_co=join('&nbsp;', $form->_button_status[$form->_status]['CO']);
	$container_it=join('&nbsp;', $form->_button_status[$form->_status]['IT']);
	$container_la=join('&nbsp;', $form->_button_status[$form->_status]['LA']);
}else{
	$container_co = $container_it = $container_la = '';
}

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	$meco = $form->output;
	$meco = str_replace('class="tablerow"','class="tablerow" style="font-size:20px; align:center;" ',$meco);
	echo $meco."</td><td align='center'>".img("images/borrar.jpg");
else:

$campos=$form->template_details('sinvcombo');
$scampos  ='<tr id="tr_sinvcombo_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['itcodigo']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['itdescrip']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['itcantidad']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['itultimo']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['itpond']['field'];
$ocultos=array('precio1','formcal');
foreach($ocultos as $obj){
	$obj2='it'.$obj;
	$scampos.=$campos[$obj2]['field'];
}
$scampos .= '</td>';
$scampos .= '<td class="littletablerow"  align="center"><a href=# onclick="del_sinvcombo(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$campos=$form->js_escape($scampos);

$campos2   =$form->template_details('sinvpitem');
$scampos2  ='<tr id="tr_sinvpitem_<#i#>">';
$scampos2 .='<td class="littletablerow" align="left" >'.$campos2['it2codigo']['field'].'</td>';
$scampos2 .='<td class="littletablerow" align="left" >'.$campos2['it2descrip']['field'].'</td>';
$scampos2 .='<td class="littletablerow" align="right">'.$campos2['it2cantidad']['field'].'</td>';
$scampos2 .='<td class="littletablerow" align="right">'.$campos2['it2merma']['field'];
$ocultos2=array('ultimo','pond','formcal','id_sinv');
foreach($ocultos2 as $obj){
	$obj2='it2'.$obj;
	$scampos2.=$campos2[$obj2]['field'];
}
$scampos2 .='</td>';
$scampos2 .='<td class="littletablerow"  align="center"><a href=# onclick="del_sinvpitem(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$campos2=$form->js_escape($scampos2);


$campos3   =$form->template_details('sinvplabor');
$scampos3  ='<tr id="tr_sinvpitem_<#i#>">';
$scampos3 .='<td class="littletablerow" align="left" >'.$campos3['it3estacion']['field'].'</td>';
$scampos3 .='<td class="littletablerow" align="right">'.$campos3['it3actividad']['field'].'</td>';
$scampos3 .='<td class="littletablerow" align="right">'.$campos3['it3minutos']['field'].'</td>';
$scampos3 .='<td class="littletablerow" align="right">'.$campos3['it3segundos']['field'].'</td>';
$scampos3 .='<td class="littletablerow"  align="center"><a href=# onclick="del_sinvplabor(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$campos3=$form->js_escape($scampos3);

if($form->_status!='show'){ ?>
<style >
.ui-autocomplete {
	max-height: 150px;
	overflow-y: auto;
	max-width: 600px;
}
html.ui-autocomplete {
	height: 150px;
	width: 600px;
}
</style>
<script language="javascript" type="text/javascript">

sinvcombo_cont =<?php echo $form->max_rel_count['sinvcombo']; ?>;
sinvpitem_cont =<?php echo $form->max_rel_count['sinvpitem']; ?>;
sinvplabor_cont=<?php echo $form->max_rel_count['sinvplabor']; ?>;

function ocultatab(){
	tipo=$("#tipo").val();
	if(tipo=='Combo'){
		$("#litab7").show();
		$("#tab7").show();
	}else{
		$("#litab7").hide();
		$("#tab7").hide();
	}
}

$(function(){
	ocultatab();
	$(".inputnum").numeric(".");
	//totalizarcombo();
	for(var i=0;i < <?php echo $form->max_rel_count['sinvcombo']; ?>;i++){
		autocod(i.toString());
	}
	for(var i=0;i < <?php echo $form->max_rel_count['sinvpitem']; ?>;i++){
		autocodpitem(i.toString());
	}
	$('input[name^="itcantidad_"]').keypress(function(e) {
		if(e.keyCode == 13) {
		    add_sinvcombo();
			return false;
		}
	});

	$('input[name^="it2cantidad_"]').keypress(function(e) {
		if(e.keyCode == 13) {
		    add_sinvpitem();
			return false;
		}
	});

	$("#tipo").change(function(){
		ocultatab();
	});
});

function totalizarcombo(){
	var tota   =0;
	var arr=$('input[name^="itcantidad_"]');
	jQuery.each(arr, function() {
		nom=this.name;
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind     = this.name.substring(pos+1);
			cana    = Number($("#itcantidad_"+ind).val());
			pond    = Number($("#itpond_"+ind).val());
			ultimo  = Number($("#itultimo_"+ind).val());
			formcal = $("#itformcal_"+ind).val();
			tp      =Math.round(cana * pond  *100)/100;
			tu      =Math.round(cana * ultimo*100)/100;
			switch(formcal){
			case 'P': t=tp;
			break;
			case 'U': t=tu;
			break;
			case 'M':{if(tp>tu)
				t=tp
				else
				t=tu;}
			break;
			default: t=tu;
			}

			tota=tota+t;
		}
	});
	$("#pond").val(tota);
	$("#ultimo").val(tota);
	requeridos();
}

function add_sinvcombo(){
	var htm = <?php echo $campos; ?>;
	can = sinvcombo_cont.toString();
	con = (sinvcombo_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__INPL_SINVCOMBO__").after(htm);
	$("#itcantidad_"+can).numeric(".");
	autocod(can);
	$('#itcodigo_'+can).focus();
	$("#itcantidad_"+can).keypress(function(e) {
		if(e.keyCode == 13) {
		    add_sinvcombo();
			return false;
		}
	});
	sinvcombo_cont=sinvcombo_cont+1;
}

function post_modbus_sinv(nind){
	ind=nind.toString();

	$("#itprecio_"+ind).empty();
	var arr=$('#itprecio_'+ind);

	descrip=$("#itdescrip_"+ind).val();
	$("#itdescrip_"+ind+'_val').text(descrip);

	descrip=$("#itultimo_"+ind).val();
	$("#itultimo_"+ind+'_val').text(descrip);

	descrip=$("#itpond_"+ind).val();
	$("#itpond_"+ind+'_val').text(descrip);

	totalizarcombo();
}

function del_sinvcombo(id){
	id = id.toString();
	$('#tr_sinvcombo_'+id).remove();
	totalizarcombo();
}

//Agrega el autocomplete
function autocod(id){
	$('#itcodigo_'+id).autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscasinv2'); ?>",
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
		autoFocus: true,
		select: function( event, ui ) {
			$('#itcodigo_'+id).val(ui.item.codigo);
			$('#itdescrip_'+id).val(ui.item.descrip);
			$('#itprecio1_'+id).val(ui.item.base1);
			$('#itpond_'+id).val(ui.item.pond);
			$('#itultimo_'+id).val(ui.item.ultimo);
			$('#itformcal_'+id).val(ui.item.formcal);
			$('#itcantidad_'+id).val('1');
			$('#itcantidad_'+id).focus();
			$('#itcantidad_'+id).select();
			var arr  = $('#itprecio_'+id);
			post_modbus_sinv(id);
			totalizarcombo();
		}
	});
}

function add_sinvpitem(){
	var htm = <?php echo $campos2; ?>;
	can = sinvpitem_cont.toString();
	con = (sinvpitem_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__INPL_SINVPITEM__").after(htm);
	$("#it2cantidad_"+can).numeric(".");
	$("#it2merma_"+can).numeric(".");
	autocodpitem(can);
	$('#it2codigo_'+can).focus();
	$("#it2cantidad_"+can).keypress(function(e) {
		if(e.keyCode == 13) {
		    add_sinvpitem();
			return false;
		}
	});
	sinvpitem_cont=sinvpitem_cont+1;
}
function del_sinvpitem(id){
	id = id.toString();
	$('#tr_sinvpitem_'+id).remove();
	totalizarpitem();
}

function totalizarpitem(){
	var tota   = 0;
	var arr=$('input[name^="it2cantidad_"]');
	jQuery.each(arr, function() {
		nom=this.name;
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind     = this.name.substring(pos+1);
			cana    = Number($("#it2cantidad_"+ind).val());
			pond    = Number($("#it2pond_"+ind).val());
			ultimo  = Number($("#it2ultimo_"+ind).val());
			formcal = $("#it2formcal_"+ind).val();
			tp      = Math.round(cana * pond  *100)/100;
			tu      = Math.round(cana * ultimo*100)/100;
			//alert(cana+':'+pond+':'+ultimo+':'+formcal);
			switch(formcal){
			case 'P': t=tp;
			break;
			case 'U': t=tu;
			break;
			case 'M':{if(tp>tu)
				t=tp;
				else
				t=tu;}
			break;
			default: t=tu;
			}

			tota=tota+t;
		}
	});
	tota=roundNumber(tota,2);
	$("#pond").val(tota);
	$("#ultimo").val(tota);
	calculos('S');
	//requeridos();
}

function autocodpitem(id){
	$('#it2codigo_'+id).autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscasinv2'); ?>",
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
		autoFocus: true,
		select: function( event, ui ){
			$('#it2codigo_'+id).val(ui.item.codigo);
			$('#it2descrip_'+id).val(ui.item.descrip);
			$('#it2pond_'+id).val(ui.item.pond);
			$('#it2ultimo_'+id).val(ui.item.ultimo);
			$('#it2formcal_'+id).val(ui.item.formcal);
			$('#it2id_sinv_'+id).val(ui.item.id);

			$('#it2cantidad_'+id).val('1');
			$('#it2cantidad_'+id).focus();
			$('#it2cantidad_'+id).select();
			post_modbus_sinvpitem(id);
			totalizarpitem();
		}
	});
}

function post_modbus_sinvpitem(nind){
	ind=nind.toString();

	$("#it2precio_"+ind).empty();
	var arr=$('#it2precio_'+ind);

	descrip=$("#it2descrip_"+ind).val();
	$("#it2descrip_"+ind+'_val').text(descrip);

	descrip=$("#it2ultimo_"+ind).val();
	$("#it2ultimo_"+ind+'_val').text(descrip);

	descrip=$("#it2pond_"+ind).val();
	$("#it2pond_"+ind+'_val').text(descrip);

	descrip=$("#it2formcal_"+ind).val();
	$("#it2formcal_"+ind+'_val').text(descrip);

	totalizarpitem();
}

function add_sinvplabor(){
	var htm = <?php echo $campos3; ?>;
	can = sinvplabor_cont.toString();
	con = (sinvplabor_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__INPL_SINVPLABOR__").after(htm);
	$("#it3minutos_"+can).numeric("0");
	$("#it3segundos_"+can).numeric("0");
	$('#it3estacion_'+can).focus();
	$("#it3segundos_"+can).keypress(function(e) {
		if(e.keyCode == 13) {
		    add_sinvplabor();
			return false;
		}
	});
	sinvplabor_cont=sinvplabor_cont+1;
}
function del_sinvplabor(id){
	id = id.toString();
	$('#tr_sinvplabor_'+id).remove();
}
</script>
<?php }


 if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<table border='0' width="100%">
	<tr>
		<td>
			<?php if($form->_status=='show'){ ?>
			<table>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;
						<a href='javascript:void(0);' onclick="window.open('<?php echo base_url();?>inventario/sinv/consulta/<?php echo $form->_dataobject->get('id'); ?>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+', screeny='+((screen.availHeight/2)-300)+'');">
						<?php
							$propiedad = array('src' => 'images/ojos.png', 'alt' => 'Consultar Movimiento', 'title' => 'Consultar Detalles','border'=>'0','height'=>'30');
							echo img($propiedad);
						?>
						</a>
					</td>
					<td>&nbsp;
						<a href='javascript:sinvcodigo("<?php echo $form->_dataobject->get('id'); ?>")'>
						<?php
							$propiedad = array('src' => 'images/cambiocodigo.jpg', 'alt' => 'Cambio de Codigo', 'title' => 'Cambio de codigo','border'=>'0','height'=>'30');
							echo img($propiedad);
						?>
						</a>
					</td>
					<td>&nbsp;
						<a href='javascript:void(0);' onclick='javascript:submitkardex()'>
						<?php
							$propiedad = array('src' => 'images/kardex.jpg', 'alt' => 'Kardex de Inventario', 'title' => 'Kardex de Inventario','border'=>'0','height'=>'30');
							echo img($propiedad);
						?>
					</a>
					<td>&nbsp;
						<a href='javascript:void(0);' onclick='javascript:sinvbarras("<?php echo $form->_dataobject->get('id'); ?>")'>
						<?php
							$propiedad = array('src' => 'images/addcode.png', 'alt' => 'Codigo Suplementarios', 'title' => 'Codigo de Barras Suplementarios','border'=>'0','height'=>'30');
							echo img($propiedad);
						?>
						</a>
					</td>
					<td>&nbsp;
						<a href='javascript:void(0);' onclick='javascript:sinvproveed("<?php echo $form->_dataobject->get('id'); ?>")'>
						<?php
							$propiedad = array('src' => 'images/camion.png', 'alt' => 'Codigo en el proveedor', 'title' => 'Codigo en el proveedor','border'=>'0','height'=>'30');
							echo img($propiedad);
						?>
					</a>
					</td>
					<td>&nbsp;
						<a href='javascript:void(0);' onclick='javascript:sinvpromo("<?php echo $form->_dataobject->get('id'); ?>")'>
						<?php
							$propiedad = array('src' => 'images/descuento.jpg', 'alt' => 'Descuentos y Promociones', 'title' => 'Descuentos y Promociones','border'=>'0','height'=>'30');
							echo img($propiedad);
						?>
						</a>
					</td>
					<td>&nbsp;
						<a href='javascript:void(0);' onclick='javascript:sinvdescu("<?php echo $form->_dataobject->get('id'); ?>")'>
						<?php
							$propiedad = array('src' => 'images/cliente.jpg', 'alt' => 'Descuentos por Cliente', 'title' => 'Descuentos por Cliente','border'=>'0','height'=>'30');
							echo img($propiedad);
						?>
						</a>
					</td>
					<td>&nbsp;
						<a href='javascript:void(0);'
						onclick="window.open('<?php echo base_url(); ?>inventario/fotos/dataedit/<?php echo $form->_dataobject->get('id'); ?>/create', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');" >
						<?php
							$propiedad = array('src' => 'images/camara.jpg', 'alt' => 'Imagenes', 'title' => 'Imagenes','border'=>'0','height'=>'30');
							echo img($propiedad);
						?>
						</a>
					</td>
				</tr>
			</table>
			<?php } // show ?>
		</td>
		<td align='center' valign='middle'>
			<?php  if ($form->activo->value=='N') echo "<div style='font-size:14px;font-weight:bold;background: #B40404;color: #FFFFFF'>***DESACTIVADO***</div>"; ?>&nbsp;
		</td>
		<td align='right'><?php echo $container_tr; ?></td>
	</tr>
</table>

<fieldset style='border: 1px outset #9AC8DA;background: #FFFFF9;'>
<legend class="titulofieldset" >Identificacion del Producto </legend>
<table border='0' width="100%">
	<tr>
		<td colspan='2' valign='top'>
			<table border=0 width="100%">
				<tr>
					<td width="60" class="littletableheaderc"><? echo $form->codigo->label ?></td>
					<?php if( $form->_status == "modify" ) { ?>
					<td class="littletablerow">
					<input readonly value="<?=$form->codigo->output ?>" class='input' size='15' style='background: #F5F6CE;'  /></td>
					<?php } else { ?>
					<td class="littletablerow"><?=$form->codigo->output ?></td>
					<?php } ?>
				</tr>
				<tr>
					<td class='littletableheaderc'>Alterno</td>
					<td class="littletablerow"><?=$form->alterno->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'>Caja</td>
					<td class="littletablerow">
						<?php
						if($form->_status=='show'){
							if( !empty($form->enlace->output))
							{
								$mID = $this->datasis->dameval("SELECT id FROM sinv WHERE codigo='".addslashes(trim($form->enlace->output))."'");
								echo anchor('inventario/sinv/dataedit/show/'.$mID,$form->enlace->output);
							}
						} else { echo $form->enlace->output; }
						?>
					</td>
				</tr>
				<tr>
					<td class='littletableheaderc'>Barras</td>
					<td class="littletablerow"><?=$form->barras->output   ?></td>
				</tr>
			</table>
		</td>
		<td colspan='2' valign='top'>
			<table border=0 width="100%">
				<tr>
					<td class='littletableheaderc'>Descripcion</td>
					<td class="littletablerow"><?=$form->descrip->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'>Adicional</td>
					<td class="littletablerow"><?=$form->descrip2->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->marca->label ?></td>
					<td class="littletablerow"><?=$form->marca->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->modelo->label ?></td>
					<td class="littletablerow"><?=$form->modelo->output   ?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='4'>
			<table width="100%" border='0' style="border-collapse;border:1px dashed">
				<tr>
					<td  valign='top'  align='center'>
						<table border='0' >
							<tr>
								<td width="40" class="littletableheaderc"><?=$form->tipo->label ?></td>
								<td class="littletablerow"><?=$form->tipo->output   ?></td>
							</tr>
						</table>
					</td>
						<td valign='top' align='center'>
						<table border='0' >
							<tr>
								<td class='littletableheaderc'><?=$form->activo->label ?></td>
								<td class="littletablerow"><?=$form->activo->output   ?></td>
							</tr>
						</table>
					</td>
					<td valign='top'  align='center'>
						<table border='0'>
							<tr>
								<td width='50' class="littletableheaderc"><?=$form->iva->label   ?></td>
								<td class="littletablerow" ><?=$form->iva->output ?></td>
							</tr>
						</table>
					</td>
					<td valign='top'  align='center'>
						<table border='0'>
							<tr>
								<td width='90' class="littletableheaderc"><?=$form->exento->label   ?></td>
								<td class="littletablerow" ><?=$form->exento->output ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</fieldset>

<div id="maintabcontainer">
	<ul>
		<li><a href="#tab1">Parametros</a></li>
		<li><a href="#tab2">Precios</a></li>
		<li><a href="#tab3">Existencias</a></li>
		<li><a href="#tab4">Movimientos</a></li>
		<li><a href="#tab5">Promociones</a></li>
		<li><a href="#tab6">Precio al Mayor</a></li>
		<?php if(($form->_dataobject->get('tipo')=='Combo' && $form->_status=='show') || $form->_status!='show'){?>
		<li id="litab7"><a href="#tab7">Articulos del Combo</a></li>
		<?php }?>
		<li><a href="#tab8">Ingredientes</a></li>
		<li><a href="#tab9">Labores     </a></li>
	</ul>
	<div id="tab1" style='background:#EFEFFF'>
	<table width="100%" border='0'>
	<tr>
		<td colspan='2' valign='top'>
			<table border='0' width="100%" style='border-collapse;border: 1px dotted'>
				<tr>
					<td class='littletableheaderc'><?=$form->tdecimal->label ?></td>
					<td class="littletablerow"><?=$form->tdecimal->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->serial->label ?></td>
					<td class="littletablerow"><?=$form->serial->output   ?></td>
				</tr>
				<tr>
					<td width="100" class='littletableheaderc'><?=$form->clave->label ?></td>
					<td class="littletablerow"><?=$form->clave->output   ?></td>
				</tr>
			</table>
		</td>
		<td valign='top' align='center'>
			<table border='0'  width='100%'>
				<tr>
					<td class='littletableheaderc'><?=$form->peso->label ?></td>
					<td class="littletablerow"><?=$form->peso->output   ?></td>
					<td class="littletablerow"><?php echo $this->datasis->traevalor('SINVPESO','Kg.') ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->unidad->label ?></td>
					<td class="littletablerow"><?=$form->unidad->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->fracci->label ?></td>
					<td class="littletablerow"><?=$form->fracci->output   ?></td>
				</tr>
			</table>
		</td>
		<td valign='top' align='center'>
			<table border='0' width='100%' style='border-collapse;border: 1px dotted'>
				<tr>
					<td width='50' class='littletableheaderc'><?=$form->alto->label ?></td>
					<td class="littletablerow"><?=$form->alto->output?></td>
					<td class="littletablerow" align='left'><?php echo $this->datasis->traevalor('SINVDIMENCIONES','cm.') ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->ancho->label ?></td>
					<td class="littletablerow"><?=$form->ancho->output   ?></td>
					<td class="littletablerow"><?php echo $this->datasis->traevalor('SINVDIMENCIONES','cm.') ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?php echo $form->largo->label; ?></td>
					<td class="littletablerow"><?php echo $form->largo->output;   ?></td>
					<td class="littletablerow"><?php echo $this->datasis->traevalor('SINVDIMENCIONES','cm.') ?></td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	<table width="100%" border='0'>
	<tr>
		<td valign='top' align='left'>
			<table border='0' >
				<tr>
					<td width='100' class='littletableheaderc'><?=$form->depto->label ?></td>
					<td nowrap class="littletablerow"><?=$form->depto->output   ?></td>
				</tr>
				<tr style="height:14px">
					<td class='littletableheaderc'><?=$form->linea->label ?></td>
					<td class="littletablerow" id='td_linea'><?=$form->linea->output?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->grupo->label ?></td>
					<td nowrap class="littletablerow" id='td_grupo'><?=$form->grupo->output   ?></td>
				</tr>
			</table>
		</td>
		<td valign='top'  align='left'>
			<table border='0' >
				<tr>
					<td class='littletableheaderc'><?=$form->clase->label ?></td>
					<td class="littletablerow"><?=$form->clase->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->garantia->label ?></td>
					<td class="littletablerow"><?=$form->garantia->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->comision->label ?></td>
					<td class="littletablerow"><?=$form->comision->output   ?></td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
</div>
<div id="tab2" style='background:#EFEFFF'>
	<table width='100%'>
	<tr>
		<td valign='top'>
			<fieldset style='border: 1px outset #B45F04;background: #FFEFFF;'>
			<legend class="titulofieldset" >Costos</legend>
			<table width='100%'>
				<tr>
					<td class="littletableheaderc"><?=$form->ultimo->label   ?></td>
					<td class="littletablerow" align='right'><?=$form->ultimo->output  ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->pond->label    ?></td>
					<td class="littletablerow" align='right'><?=$form->pond->output   ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->standard->label    ?></td>
					<td class="littletablerow" align='right'><?=$form->standard->output   ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->formcal->label ?></td>
					<td class="littletablerow"><?=$form->formcal->output?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->redecen->label ?></td>
					<td class="littletablerow"><?=$form->redecen->output?></td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td valign='top'>
			<fieldset style='border: 1px outset #B45F04;background: #FFEFFF;'>
			<legend class="titulofieldset" style='font-size:16' >Precios</legend>
			<table width='100%' cellspacing='0'>
				<tr>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Precio</td>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Margen</td>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Base  </td>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Precio</td>
				</tr>
			  	<tr>
					<td class="littletableheaderc">1</td>
					<td class="littletablerow" align='right'><?=$form->margen1->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base1->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio1->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc">2</td>
					<td class="littletablerow" align='right'><?=$form->margen2->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base2->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio2->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc">3</td>
					<td class="littletablerow" align='right'><?=$form->margen3->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base3->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio3->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc">4</td>
					<td class="littletablerow" align='right'><?=$form->margen4->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base4->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio4->output ?></td>
				</tr>
				<tr>
					<td class="littletablerow" align='right'><?=$form->pm->label  ?>%</td>
					<td class="littletablerow" align='right'><?=$form->pm->output ?></td>
					<td class="littletableheaderc">&nbsp;</td>
					<td class="littletableheaderc">&nbsp;</td>
				</tr>
				<tr>
					<td class="littletablerow" align='right'><?=$form->mmargen->label   ?>%</td>
					<td class="littletablerow" align='right'><?=$form->mmargen->output ?></td>
					<td class="littletableheaderc">&nbsp;</td>
					<td class="littletableheaderc">&nbsp;</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
	</table>
</div>
<?php if(($form->_dataobject->get('tipo')=='Combo' && $form->_status=='show') || $form->_status!='show'){?>
<div id="tab7" style='background:#EFEFFF'>
	<div style='overflow:auto;border: 1px solid #9AC8DA;background: #FAFAFA;height:200px'>
		<table width='100%'>
			<tr id='__INPL_SINVCOMBO__'>
				<td bgcolor='#7098D0'><b>C&oacute;digo</b></td>
				<td bgcolor='#7098D0'><b>Descripci&oacute;n</b></td>
				<td bgcolor='#7098D0'><b>Cantidad</b></td>
				<td bgcolor='#7098D0'><b>Ultimo</b></td>
				<td bgcolor='#7098D0'><b>Ponderado</b></td>
				<?php if($form->_status!='show') {?>
				<td  bgcolor='#7098D0' align='center'><b>&nbsp;</b></td>
				<?php } ?>
			</tr>
			<?php
			for($i=0;$i<$form->max_rel_count['sinvcombo'];$i++) {
				$itcodigo   = "itcodigo_$i";
				$itdescrip  = "itdescrip_$i";
				$itcantidad = "itcantidad_$i";
				$itultimo   = "itultimo_$i";
				$itpond     = "itpond_$i";

				$oculto='';
				foreach($ocultos as $obj){
					$obj2='it'.$obj.'_'.$i;
					$oculto.=$form->$obj2->output;
				}
			?>
			<tr id='tr_sinvcombo_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" nowrap><?php echo $form->$itcodigo->output;       ?></td>
				<td class="littletablerow" align="left"       ><?php echo $form->$itdescrip->output;      ?></td>
				<td class="littletablerow" align="right"      ><?php echo $form->$itcantidad->output;     ?></td>
				<td class="littletablerow" align="right"      ><?php echo $form->$itultimo->output;       ?></td>
				<td class="littletablerow" align="right"      ><?php echo $form->$itpond->output.$oculto; ?></td>

				<?php if($form->_status!='show') {?>
				<td class="littletablerow" align="center">
					<a href='#' onclick='del_sinvcombo(<?=$i ?>);return false;'><?php echo img("images/delete.jpg") ?></a>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
			<tr id='__UTPL_SINVCOMBO__'>
			</tr>
		</table>
		</div>
		<?php echo $container_co ?>
</div>
<?php } ?>
<div id="tab8" style='background:#EFEFFF'>
	<div style='overflow:auto;border: 1px solid #9AC8DA;background: #FAFAFA;height:200px'>
		<table width='100%'>
			<tr id='__INPL_SINVPITEM__'>
				<td bgcolor='#7098D0'            ><b>C&oacute;digo     </b></td>
				<td bgcolor='#7098D0'            ><b>Descripci&oacute;n</b></td>
				<td bgcolor='#7098D0' align=right><b>Cantidad          </b></td>
				<td bgcolor='#7098D0' align=right><b>Merma &#37;       </b></td>
				<?php if($form->_status!='show') {?>
					<td  bgcolor='#7098D0' align='center'><b>&nbsp;</b></td>
				<?php } ?>
			</tr>
			<?php
			for($i=0;$i<$form->max_rel_count['sinvpitem'];$i++){
				$it2codigo   = "it2codigo_$i";
				$it2descrip  = "it2descrip_$i";
				$it2cantidad = "it2cantidad_$i";
				$it2merma    = "it2merma_$i";
				$it2formcal  = "it2formcal_$i";
				$it2pond     = "it2pond_$i";
				$it2ultimo   = "it2ultimo_$i";
				$it2id_sinv  = "it2id_sinv_$i";
			?>
			<tr id='tr_sinvpitem_<?=$i;?>'>
				<td class="littletablerow" align="left" nowrap><?php echo $form->$it2codigo->output;   ?></td>
				<td class="littletablerow" align="left"       ><?php echo $form->$it2descrip->output;  ?></td>
				<td class="littletablerow" align="right"      ><?php echo $form->$it2cantidad->output; ?></td>
				<td class="littletablerow" align="right"      ><?php echo $form->$it2merma->output.$form->$it2pond->output.$form->$it2ultimo->output.$form->$it2formcal->output.$form->$it2id_sinv->output; ?></td>
				<?php if($form->_status!='show'){?>
				<td class="littletablerow" align="center">
					<a href='#' onclick='del_sinvpitem(<?=$i ?>);return false;'><?php echo img("images/delete.jpg") ?></a>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
			<tr id='__UTPL_SINVPITEM__'>
			</tr>
		</table>
	</div>
	<?php echo $container_it ?>
</div>
<div id="tab9" style='background:#EFEFFF'>
	<div style='overflow:auto;border: 1px solid #9AC8DA;background: #FAFAFA;height:200px'>
		<table width='100%'>
			<tr id='__INPL_SINVPLABOR__'>
				<td bgcolor='#7098D0' align='left' ><b>Estaci&oacute;n</b></td>
				<td bgcolor='#7098D0' align='left' ><b>Actividad      </b></td>
				<td bgcolor='#7098D0' align='right'><b>Minutos        </b></td>
				<td bgcolor='#7098D0' align='right'><b>Segundos       </b></td>
				<?php if($form->_status!='show') {?>
					<td  bgcolor='#7098D0' align='center'><b>&nbsp;</b></td>
				<?php } ?>
			</tr>
			<?php
			for($i=0;$i<$form->max_rel_count['sinvplabor'];$i++){
				$it3estacion = "it3estacion_$i";
				$it3nombre   = "it3nombre_$i";
				$it3actividad= "it3actividad_$i";
				$it3minutos  = "it3minutos_$i";
				$it3segundos = "it3segundos_$i";
			?>
			<tr id='tr_sinvpitem_<?=$i;?>'>
				<td class="littletablerow" align="left" nowrap><?php echo $form->$it3estacion->output;  ?></td>
				<td class="littletablerow" align="right"      ><?php echo $form->$it3actividad->output; ?></td>
				<td class="littletablerow" align="right"      ><?php echo $form->$it3minutos->output;   ?></td>
				<td class="littletablerow" align="right"      ><?php echo $form->$it3segundos->output;  ?></td>
				<?php if($form->_status!='show'){?>
				<td class="littletablerow" align="center">
					<a href='#' onclick='del_sinvplabor(<?=$i ?>);return false;'><?php echo img('images/delete.jpg') ?></a>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
			<tr id='__UTPL_SINVPLABOR__'>
			</tr>
		</table>
	</div>
	<?php echo $container_la ?>
</div>

<div id="tab3" style='background:#EFEFFF'>
	<table width='100%'>
	<tr>
  		<td valign="top">
			<fieldset  style='border: 2px outset #FEB404;background: #FFFCE8;'>
			<legend class="titulofieldset" >Existencias</legend>
			<table width='100%' border=0 >
				<tr>
					<td width='120' class="littletableheaderc"><?=$form->existen->label  ?></td>
					<td class="littletablerow" align='right'  ><?=$form->existen->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->exmin->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exmin->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->exmax->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exmax->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->exord->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exord->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->exdes->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exdes->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->ubica->label ?></td>
					<td class="littletablerow" align='right'><?=$form->ubica->output   ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
		<?php if( !empty($form->almacenes->output)) { ?>
		<td valign="top">
			<fieldset style='border: 1px outset #FEB404;background: #FFFCE8;'>
			<legend class="titulofieldset" >Almacenes</legend>
			<?php echo $form->almacenes->output ?>
			</fieldset>
		</td>
		<?php } ?>
	</tr>
	</table>
</div>
<div id="tab4" style='background:#EFEFFF'>

	<table width='100%'>
	<?php if($form->_status=='show'){ ?>
	<tr>
		<td valign='top'>
			<fieldset  style='border: 2px outset #FEB404;background: #FFFCE8;'>
			<legend class="titulofieldset" >Ventas</legend>
			<table width='100%' >
				<tr>
					<td class="littletableheader" ><?=$form->fechav->label?></td>
				</tr>
				<tr>
					<td class="littletablerow"><?=$form->fechav->output   ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
		<td valign='top'>
			<fieldset  style='border: 2px outset #FEB404;background: #FFFCE8;'>
			<legend class="titulofieldset" >&Uacute;ltimos Movimientos</legend>
			<table width='100%' >
				<tr>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Fecha</td>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Codigo</td>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Proveedor</td>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Precio</td>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:10px'><?=$form->pfecha1->output?></td>
					<td class="littletablerow" style='font-size:10px'>
					<?php
						$mID = $this->datasis->dameval("SELECT id FROM sprv WHERE proveed='".addslashes(trim($form->prov1->output))."'");
						echo "<a href=\"javascript:void(0);\" onclick=\"window.open('".base_url();
						echo "compras/sprv/dataedit/show/$mID', '_blank', 'width=800,height=600,scrollbars=Yes,status=Yes,resizable=Yes,screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');\" heigth=\"600\">";
						echo $form->prov1->output;
						echo "</a>";
					?>
					</td>
					<td class="littletablerow" style='font-size:10px'><?=$form->proveed1->output?></td>
					<td class="littletablerow" style='font-size:10px' align='right'><?=$form->prepro1->output?></td>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:10px'><?=$form->pfecha2->output?></td>
					<td class="littletablerow" style='font-size:10px'>
					<?php
						$mID = $this->datasis->dameval("SELECT id FROM sprv WHERE proveed='".addslashes(trim($form->prov2->output))."'");
						echo "<a href=\"javascript:void(0);\" onclick=\"window.open('".base_url();
						echo "compras/sprv/dataedit/show/$mID', '_blank', 'width=800,height=600,scrollbars=Yes,status=Yes,resizable=Yes,screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');\" heigth=\"600\">";
						echo $form->prov2->output;
						echo "</a>";
					?>
					</td>
					<td class="littletablerow" style='font-size:10px'><?=$form->proveed2->output?></td>
					<td class="littletablerow" style='font-size:10px' align='right'><?=$form->prepro2->output?></td>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:10px;'><?=$form->pfecha3->output?></td>
					<td class="littletablerow" style='font-size:10px'>
					<?php
						$mID = $this->datasis->dameval("SELECT id FROM sprv WHERE proveed='".addslashes(trim($form->prov3->output))."'");
						echo "<a href=\"javascript:void(0);\" onclick=\"window.open('".base_url();
						echo "compras/sprv/dataedit/show/$mID', '_blank', 'width=800,height=600,scrollbars=Yes,status=Yes,resizable=Yes,screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');\" heigth=\"600\">";
						echo $form->prov3->output;
						echo "</a>";
					?>
					</td>
					<td class="littletablerow" style='font-size:10px;'><?=$form->proveed3->output?></td>
					<td class="littletablerow" style='font-size:10px;' align='right'><?=$form->prepro3->output?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
<?php };?>
	<tr>
		<td>
			<?php
			$query = $this->db->query("SELECT a.proveed, MID(b.nombre,1,25) nombre, a.codigop FROM sinvprov a JOIN sprv b ON a.proveed=b.proveed WHERE a.codigo='".addslashes($form->_dataobject->get('codigo'))."'");
			if ($query->num_rows()>0 ) {
			?>
				<fieldset style='border: 2px outset #FEB404;background: #FFFCE8;'>
				<legend class="titulofieldset" >Codigo del proveedor</legend>
				<table width='50%' border='0'>
					<?php
						foreach($query->result() as $row ){
							echo "
							<tr>
								<td style='font-size: 12px;font-weight: normal'>".$row->proveed."</td>
								<td style='font-size: 12px;font-weight: normal'>".$row->nombre."</td>
								<td style='font-size: 12px;font-weight: bold'>".$row->codigop."</td>
								<td valign='top' style='height: 18px;'>
									<a href='javascript:sinvborraprv(\"$row->proveed\",\"$row->codigop\")'>
									".img(array('src' => 'images/delete.jpg', 'alt' => 'Eliminar', 'title' => 'Eliminar','border'=>'0','height'=>'16'))."
									</a>
								</td>
							</tr>";
						}
						echo "</table>";
						?>
				</fieldset>
			<?php }  // rows>0 ?>
		</td>
	</tr>
	</table>
</div>

<div id="tab5" style='background:#EFEFFF'>
	<table width='100%'>
		<tr>
			<td>
				<?php if($form->_status=='show'){ ?>
				<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
				<legend class="titulofieldset" >Descuentos</legend>
				<table border=0 width='100%'>
				<tr>
					<td valign="top"><?php
						$margen =  $this->datasis->dameval("SELECT margen FROM grup WHERE grupo='".$form->_dataobject->get('grupo')."'");
						if ($margen > 0 ) {
							echo "Descuento por Grupo ";
							echo $margen."% ";
							echo "Precio ".nformat($form->precio1->value * (100-$margen)/100);
						} else echo "No tiene descuento por grupo";
						?>
					</td>
				</tr>
				<tr>
					<td valign="top"><?php
						$margen =  $this->datasis->dameval("SELECT margen FROM sinvpromo WHERE codigo='".addslashes($form->_dataobject->get('codigo'))."'");
						if ($margen > 0 ) {
							echo "Descuento por Promocion ".$margen."% ";
							echo "Precio ".nformat($form->precio1->value * (100-$margen)/100);
						} else echo "No tiene descuento promocional";

						?>
					</td>
				</tr>
				</table>
				</fieldset>
				<?php } ?>
			</td>
		</tr>
	</table>
	<br/>
<?php
$query = $this->db->query("SELECT suplemen FROM barraspos WHERE codigo='".addslashes($form->_dataobject->get('codigo'))."'");
if ($query->num_rows()>0 ) {
?>

	<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
	<legend class="titulofieldset" >Codigos de Barras Asociados</legend>
	<table width='100%' border=0>
		<?php
			$m = 1;
			foreach($query->result() as $row ){
				if ( $m > 3 ) { ?>
	<tr>
				<?php	$m = 1;
				}
				echo "
		<td style='font-size: 16px;font-weight: bold'>
			<table cellpadding='0' cellspacing='0'><tr>
				<td style='height: 18px;'>
					".$row->suplemen."
				</td><td valign='top' style='height: 18px;'>
					<a href='javascript:sinvborrasuple(\"$row->suplemen\")'>
					".img(array('src' => 'images/delete.jpg', 'alt' => 'Eliminar', 'title' => 'Eliminar','border'=>'0','height'=>'16'))."
					</a>
				</td>
			</tr></table>
		</td>";

				$m += 1;
			}
			?>
	</tr>
	</table>
	</fieldset>
<?php }  // rows>0 ?>

<?php
$query = $this->db->query("SELECT CONCAT(codigo,' ', descrip,' ',fracci) producto, id FROM sinv WHERE MID(tipo,1,1)='F' AND enlace='".addslashes($form->_dataobject->get('codigo'))."'");
if ($query->num_rows()>0 ) {
?>
	<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
	<legend class="titulofieldset" >Productos Derivados</legend>
	<table width='100%'>
	<tr>
		<?php
			$m = 1;
			foreach($query->result() as $row ){
				if ( $m > 4 ) {
					echo "</tr><tr>";
					$m = 1;
				}
				echo "<td class='littletablerow'>";
				echo anchor('inventario/sinv/dataedit/show/'.$row->id,$row->producto);
				echo "</td>";
				$m += 1;
			}
			?>
	</tr>
	</table>
	</fieldset>
<?php }  // rows>0  </div> ?>
</div>

<div id="tab6" style='background:#EFEFFF'>
	<table width='100%'>
		<tr>
			<td>
				<fieldset style='border: 1px outset #8A0808;background: #FFFBE9;'>
				<legend class="titulofieldset" >Bonos por volumen</legend>
				<table width='100%'>
				<tr>
						<td class="littletableheaderc" width='50'>Desde</td>
						<td class="littletablerow" align='right'><?=$form->fdesde->output ?></td>
						<td class="littletableheaderc">Por la compra de </td>
						<td class="littletablerow" align='right'><?=$form->bonicant->output ?></td>
				</tr>
				<tr>
						<td class="littletableheaderc">Hasta</td>
						<td class="littletablerow" align='right'><?=$form->fhasta->output ?></td>
						<td class="littletableheaderc">Se lleva adicional </td>
						<td class="littletablerow" align='right'><?=$form->bonifica->output ?></td>
				</tr>
				</table>
				</fieldset>
			</td>
		</tr>
	</table>
</div>
<?php echo $container_bl.$container_br; ?>
<?php echo $form_end?>
<?php endif; ?>
