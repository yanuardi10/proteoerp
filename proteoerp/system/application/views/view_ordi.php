<?php
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' OR $form->_action=='delete'):
	echo $form->output;
else:

if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';
//echo $form_scripts;
echo $form_begin; 

	$objs=array(
		'codigo'     =>'C&oacute;digo',
		'descrip'    =>'Descripci&oacute;n',
		'cantidad'   =>'Cantidad'   ,
		'costofob'   =>'Costo FOB $'  ,
		'importefob' =>'Importe FOB $',
		'codaran'    =>'C&oacute;digo arancelario',
		'arancel'    =>'% Arancel'  ,
	);

	$html='<tr id="tr_itordi_<#i#>">';
	$campos=$form->template_details('itordi');

	foreach($objs AS $nom=>$nan){
		$obj=$nom.'_0';
		$pivot=$campos[$nom]['field'];
		$align = (isset($form->$obj->css_class) AND $form->$obj->css_class=='inputnum') ? 'align="right"' : '';
		$html.='<td class="littletablerowth" '.$align.'>'.$pivot.'</td>';
	}
	if($form->_status!='show') {
		$html.='<td class="littletablerow"><a href=# onclick=\'del_itordi(<#i#>);return false;\'>Eliminar</a></td>';
	}
	$html.='</tr>';
?>
<script language="javascript" type="text/javascript">
	itordi_cont=<?php echo $form->max_rel_count['itordi'];?>;

	function post_add_itordi(id){
		$('#cantidad_'+id).numeric('.');
		$('#costofob_'+id).numeric('.');
		$('#importefob_'+id).numeric('.');
		$('#arancel_'+id).numeric('.');
		<?php
		foreach($objs AS $nom=>$nan){
			echo "$('#${nom}_'+id).bind(\"keyup\",function() { calcula(); });";
		}
		?>
		return true;
	}

	function add_itordi(){
		var can = itordi_cont.toString();
		var rt=true;
		if(typeof window.pre_add_itordi == 'function') {
			rt=pre_add_itordi(can);
		}
		if(rt){
			var htm = <?php echo $form->js_escape($html); ?>;
			var con = (itordi_cont+1).toString();
			htm = htm.replace(/<#i#>/g,can);
			htm = htm.replace(/<#o#>/g,con);
			$("#__UTPL__").before(htm);
			itordi_cont=itordi_cont+1;

			post_add_itordi(can);
		}
	}

	function del_itordi(id){
		var rt=true;
		if(typeof window.pre_del_itordi == 'function') {
			rt=pre_del_itordi(id);
		}
		if(rt){
			id = id.toString();
			$('#tr_itordi_'+id).remove();
			if(typeof window.post_del_itordi == 'function') {
				post_del_itordi(id);
			}
		}
	}

	$(document).ready(function(){
		$('#gastosi').bind("keyup",function() { calcula(); });
		$('#gastosn').bind("keyup",function() { calcula(); });
		$('#cambioofi').bind("keyup",function() { calcula(); });
		$('#cambioreal').bind("keyup",function() { calcula(); });
		<?php
		foreach($objs AS $nom=>$nan){
			echo "$('input[name^=\"${nom}\"]').bind(\"keyup\",function() { calcula(); });";
		}
		?>
		calcula();
	});

	function totaliza(){
		if($("#gastosi").val().length>0)   gastosi=parseFloat($("#gastosi").val());     else gastosi  =0;
		if($("#gastosn").val().length>0)   gastosn=parseFloat($("#gastosn").val());     else gastosn  =0;
		if($("#cambioofi").val().length>0) cambioofi=parseFloat($("#cambioofi").val()); else cambioofi=0;
		montofob=0;
		arr=$('input[name^="importefob_"]');
		jQuery.each(arr, function() {
			if(this.value.length>0)
				montofob = montofob + parseFloat(this.value);
		});
		montocif = montofob + gastosi;
		$("#montocif").val(roundNumber(montocif,2));
		$("#montofob").val(roundNumber(montofob,2));
		$("#montotot").val(roundNumber((montocif*cambioofi)+gastosn,2));
	}

	function calcula(){
		cana=$('input[name^="cantidad"]');
		jQuery.each(cana, function() {
			nom=this.name
			pos=this.name.lastIndexOf('_');
			if(pos>0){
				id = this.name.substring(pos+1);
				canti=costo=0;
				if(this.value.length>0) canti=parseFloat(this.value);

				nn='costofob_'+id;
				if($("#"+nn).val().length>0) costo=parseFloat($("#"+nn).val());
				$("#"+'importefob_'+id).val(roundNumber(costo*canti,2))
			}
		});
		totaliza();
	}
</script>

<table align='center'>
	<tr>
		<td align=right><?php echo $container_tr?></td>
	</tr><tr>
		<td>

<table style="margin:0;width:98%;">
	<tr>
		<td colspan=6 class="littletableheader">Orden de importaci&oacute;n <b><?php if($form->_status=='show' or $form->_status=='modify' ) echo str_pad($form->numero->output,8,0,0); ?></b></td>
	</tr>
	<tr>
		<td class="littletablerowth" ><?php echo $form->status->label; ?></td>
		<td class="littletablerow"   nowrap><?php echo $form->status->output; ?>&nbsp;</td>
		<td class="littletablerowth" align='right' ><?php echo $form->proveed->label; ?></td>
		<td class="littletablerow"  nowrap><?php echo $form->proveed->output; ?>&nbsp;</td>
		<td class="littletablerow"  nowrap colspan=2><?php echo $form->nombre->output;?>&nbsp;</td>
	</tr>
	<tr>
		<td class="littletablerowth"><?php echo $form->fecha->label;  ?></td>
		<td class="littletablerow"  ><?php echo $form->fecha->output; ?>&nbsp;</td>
		<td class="littletablerowth" align='right'><?php echo $form->agente->label;  ?></td>
		<td class="littletablerow"  ><?php echo $form->agente->output; ?>&nbsp;</td>
		<td class="littletablerow"  nowrap colspan=2><?php echo $form->nomage->output;?>&nbsp;</td>
	</tr>
	<tr>
		<td class="littletablerowth"><?php echo $form->arribo->label;  ?></td>
		<td class="littletablerow"  ><?php echo $form->arribo->output; ?>&nbsp;</td>
		<td class="littletablerowth" align='right'><?php echo $form->factura->label; ?></td>
		<td class="littletablerow"  ><?php echo $form->factura->output;?>&nbsp;</td>
		<td class="littletablerowth" colspan=2 align='center'><?php echo $form->condicion->label;  ?></td>

	</tr>
	<tr>
		<td class="littletablerowth"><?php echo $form->cambioofi->label;  ?>*</td>
		<td class="littletablerow"  ><?php echo $form->cambioofi->output;  ?>&nbsp;</td>
		<td class="littletablerowth" align='right'><?php echo $form->cambioreal->label;  ?>*</td>
		<td class="littletablerow"  ><?php echo $form->cambioreal->output; ?>&nbsp;</td>
		<td class="littletablerow" colspan=2 rowspan=3><?php echo $form->condicion->output;  ?></td>
	</tr>
		<tr>
		<td class="littletablerowth"><?php echo $form->peso->label;  ?></td>
		<td class="littletablerow"  ><?php echo $form->peso->output;  ?>&nbsp;</td>
		<td class="littletablerowth" align='right'><?php echo $form->montoiva->label;  ?></td>
		<td class="littletablerow"  ><?php echo $form->montoiva->output; ?>&nbsp;</td>
	</tr>
	<tr>
		<td class="littletablerowth"><?php echo $form->gastosi->label;  ?></td>
		<td class="littletablerow"  ><?php echo $form->gastosi->output; ?>&nbsp;</td>
		<td class="littletablerowth" align='right'><?php echo $form->gastosn->label;  ?></td>
		<td class="littletablerow"  ><?php echo $form->gastosn->output; ?>&nbsp;</td>
	</tr>
</table>
<p>
<table style="margin:0;width:98%;">
	<tr>
		<?php
			foreach($objs AS $nom=>$nan){
				echo '<th class="littletableheader">'.$nan.'</th>';
			}
			if($form->_status!='show') {
				echo '<th class="littletableheader">&nbsp;</th>';
			}
		?>
	</tr>

<?php 
for($i=0;$i<$form->max_rel_count['itordi'];$i++) {
	echo '<tr id="tr_itordi_'.$i.'">';
	foreach($objs AS $nom=>$nan){
		$obj=$nom.'_'.$i;
		$align=($form->$obj->css_class=='inputnum') ? "align='right'" : '' ;
		echo '<td class="littletablerowth" '.$align.' nowrap>'.$form->$obj->output.'</td>';
	}
	if($form->_status!='show') {
		echo '<td class="littletablerow"><a href=# onclick=\'del_itordi('.$i.');return false;\'>Eliminar</a></td>';
	}
	echo '</tr>';
}
?>
	<tr id='__UTPL__'></tr>
</table>
</p>
<?php echo $container_bl ?>
<?php echo $container_br ?>

<table style="margin:0;width:98%;">
	<tr>
		<td class="littletablerowth"><?php echo $form->montotot->label; ?></td>
		<td class="littletablerow"  ><?php echo $form->montotot->output;?></td>
		<td class="littletablerowth" align='right'><?php echo $form->montocif->label;  ?></td>
		<td class="littletablerow"  ><?php echo $form->montocif->output; ?></td>
		<td class="littletablerowth" align='right'><?php echo $form->montofob->label; ?></td>
		<td class="littletablerow"  ><?php echo $form->montofob->output;?></td>
	</tr>
</table>

		<td>
	<tr>
	<tr>
		<td>
<?php 
if($form->_status=='show'){
	echo $gseri;
	echo $gser;
	echo $ordiva;
}
?>
		</td>
	</tr>
<table>
<?php echo $form_end ?>



<?php endif; ?>