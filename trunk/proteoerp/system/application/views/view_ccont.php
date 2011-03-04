<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:
//$link=site_url('presupuesto/requisicion/getadmin');

foreach($form->detail_fields['itccont'] AS $ind=>$data)
$campos[]=$data['field'];
$campos='<tr id="tr_itccont_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';
$campos=str_replace("\n",'',$campos);
$campos.=' <td class="littletablerow"><a href=# onclick="del_itccont(<#i#>);return false;">Eliminar</a></td></tr>';
$campos=$form->js_escape($campos);
if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){
	?>

<script language="javascript" type="text/javascript">
itccont_cont=<?=$form->max_rel_count['itccont'] ?>;

function add_itccont(){
	var htm = <?=$campos ?>;
	can = itccont_cont.toString();
	con = (itccont_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__UTPL__").before(htm);
	itccont_cont=itccont_cont+1;
	
}
					
function del_itccont(id){
	id = id.toString();
	$('#tr_itccont_'+id).remove();
}
function cal_monto(i){
	id=i.toString();
	cantidad=parseFloat($("#cantidad_"+id).val()); 
	precio=parseFloat($("#precio_"+id).val());  
	monto=cantidad*precio;
	//alert(monto);
	if(!isNaN(monto))
	$("#monto_"+id).val(monto);
	cal_total();
}
$(function() {
	$(".inputnum").numeric(".");
	cal_total();
});
function cal_total(){
	tot=can=0;
	for(i=0;i<itccont_cont;i++){
		id=i.toString();
		valor=parseFloat($("#monto_"+id).val());
		if(!isNaN(valor))
			tot=tot+valor;		
		$("#base").val(tot);	
	}
	base=parseFloat($("#base").val()); 
	impuesto=base*12/100;
	//alert('hola');
	if(!isNaN(impuesto))
	$("#impuesto").val(impuesto);
	$("#tota").val(base+impuesto);
}
</script>
	<?php } ?>
<table align='center' style="margin:0;width:100%;">
	<tr>
		<td align=right><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td>
<table style="margin:0;width:100%;">
	<tr>
		<td colspan=6 class="littletableheader">Encabezado</td>
	</tr>
	<tr>
		<td  class="littletablerowth" width="25%"><?=$form->obrap->label ?></td>
		<td  class="littletablerow" width="20%"><?=$form->obrap->output ?></td>
	  </tr>
	<tr>
		<td class="littletablerowth"><?=$form->numero->label ?></td>
		<td class="littletablerow"><?=$form->numero->output ?></td>
	  </tr>
	<tr>
		<td class="littletablerowth"><?=$form->fecha->label ?></td>
		<td class="littletablerow"><?=$form->fecha->output ?></td>
	  </tr>
	<tr>
		<td  class="littletablerowth"><?=$form->tipo->label ?></td>
		<td  class="littletablerow"><?=$form->tipo->output ?></td>
	  </tr>
	<tr>
		<td colspan="6" class="littletableheader" align="left" >Datos del Contratista</td>
	  </tr>
	<tr>
	  <td class="littletablerowth"><?=$form->cod_prv->label ?></td>
	  <td class="littletablerow"><?=$form->cod_prv->output  ?></td>
	  <td class="littletablerowth"><?=$form->rif->label ?></td>
	  <td colspan="3" class="littletablerow"><?=$form->rif->output ?></td>
	  </tr>
	<tr>
	  <td class="littletablerowth"><?=$form->nombre->label ?></td>
	  <td class="littletablerow"><?=$form->nombre->output  ?></td>
	  <td class="littletablerowth"><?=$form->telefono->label ?></td>
	  <td class="littletablerow"><?=$form->telefono->output ?></td>
	  <td class="littletablerowth"><?=$form->email->label ?></td>
	  <td class="littletablerow"><?=$form->email->output ?></td>
	</tr>
	<tr>
	  <td colspan="6" class="littletablerowth"><?=$form->direccion->label  ?></td>
	 </tr>
	<tr>
	  <td colspan="6" class="littletablerow"><?=$form->direccion->output ?></td>
	  </tr>
	  <tr>
	  <td colspan="6" class="littletablerowth"><?=$form->detalles->label ?></td>
	  </tr>
	  <tr>
	  	  <td colspan="6" class="littletablerow"><?=$form->detalles->output ?></td>
  	  </tr>
	  	<tr>
		<td colspan="6" class="littletableheader" align="left" >Condiciones Comerciales </td>
	  </tr>
    	<tr>
	  	  <td class="littletablerowth"><?=$form->fecha_inicio->label ?></td>
  	      <td class="littletablerow"><?=$form->fecha_inicio->output ?></td>
        <td colspan="4" class="littletablerowth"><?=$form->retencion->label ?></td>
      	</tr>
    	<tr>
	  	  <td class="littletablerowth"><?=$form->fecha_final->label ?></td>
  	    <td class="littletablerow"><?=$form->fecha_final->output ?></td>
	  	  <td colspan="4" class="littletablerow"><?=$form->retencion->output ?></td>
      </tr>
	</table>		
	<table width='100%'>
		<tr>                                                           
	  	  <td colspan=7 class="littletableheader">Artículos, Trabajos o Servicios Contratados</td>      
	    </tr>  
			<tr>
				<td class="littletableheader" align="center">Partida</td>
				<td class="littletableheader" align="center">Descripci&oacute;n</td>
				<td class="littletableheader" align="center">Unidad de Medida</td>
				<td class="littletableheader" align="right">Cantidad</td>
				<td class="littletableheader" align="right">Precio Unitario</td>
				<td class="littletableheader" align="right">Importe Total Bs</td>
				
				<?php if($form->_status!='show') {?>
				<td class="littletableheader">&nbsp;</td>
				<?php } ?>
			</tr>
			<?php for($i=0;$i<$form->max_rel_count['itccont'];$i++) {
				$obj1="partida_$i";
				$obj2="descrip_$i";
				$obj3="unidad_$i";
				$obj4="cantidad_$i";
				$obj5="precio_$i";
				$obj6="monto_$i";
			?>
			<tr id='tr_itccont_<?=$i ?>'>
				<td class="littletablerow" align="left"><?=$form->$obj1->output ?></td>
				<td class="littletablerow" align="left"><?=$form->$obj2->output ?></td>
				<td class="littletablerow" align="center"><?=$form->$obj3->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj4->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj5->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj6->output ?></td>
				
				<?php if($form->_status!='show') {?>
				<td class="littletablerow"><a href=#
					onclick='del_itccont(<?=$i ?>);return false;'>Eliminar</a></td>
					<?php } ?>
			</tr>
			<?php } ?>

			<tr id='__UTPL__'>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				
				<?php if($form->_status!='show') {?>
				<td class="littletablefooterb" align="right">&nbsp;</td>
				<?php } ?>
			</tr>
		</table>
<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
		<td>
	<tr>
<table>
<table style="margin:0;width:100%;" > 
	    <tr>                                                           
	  	  <td colspan=6 class="littletableheader">Totales</td>      
	    </tr>                                                         
	    <tr>                                                 
		  <td  class="littletablerowth"  width="90%" align="right"><?=$form->base->label ?> </td>
		  <td  class="littletablerow"    width="10%" align="right"><?=$form->base->output ?> </td>
       </tr>
       <tr><?php $iva=$this->datasis->dameval('SELECT tasa FROM civa ORDER BY fecha DESC');?>
	 	 <td class="littletablerowth" align="right"><?=$form->impuesto->label.' '.$iva.'%' ?></td>
		 <td class="littletablerow"   align="right"><?=$form->impuesto->output ?></td>
       </tr>
	   <tr>
		 <td class="littletablerowth" align="right"><?=$form->tota->label ?></td>
		 <td class="littletablerow"   align="right"><?=$form->tota->output ?></td>
      </tr>
	   <tr>
	     <td class="littletablerowth" align="right" colspan="2"><?=$form->itccont->output ?></span></td>
    </tr>
</table>
<?php endif; ?>