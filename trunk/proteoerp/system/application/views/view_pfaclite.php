<?php
ob_start('comprimir_pagina');

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:
$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);
$container_tl=join('&nbsp;', $form->_button_container['TL']);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_begin;
if($form->_status!='show'){ ?>
<script language="javascript" type="text/javascript">

function print_r(theObj){
	var a='';
   if(theObj.constructor == Array || theObj.constructor == Object){
      a=a+"<ul>";
      for(var p in theObj){
         if(theObj [p] .constructor == Array || theObj [p] .constructor == Object){
            a=a+"<li> ["+p+"]  => "+typeof(theObj)+"</li>";
            a=a+"<ul>";
            print_r(theObj [p] );
            a=a+"</ul>";
         } else {
            a=a+"<li> ["+p+"]  => "+theObj [p] +"</li>";
         }
      }
      a=a+"</ul>";
   }
   alert(a);
}

var importes= new Array();
function total(id){
	cana=Number(document.getElementById("cana_"+id).value);
	var e= document.getElementById("preca_"+id);
	var preca = Number(e.value);
	importes[id]=Math.round(cana*preca*100);
	totalizar();
}

function totalizar(){
	var totalg=0;
	var ivas  =0;
	for(var i in importes){
		iva    = document.getElementById("iva_"+i).value;
		totalg+= importes[i]+(Math.round(importes[i]*iva/100));
	}
	document.getElementById("totalg_value").innerHTML=totalg/100;
}
</script>
<?php } ?>
<table align='center' width="95%">
	<tr>
		<td align=left ><?php echo $container_tl?></td>
		<td align=right><?php echo $container_tr?></td>
	</tr>
</table>
<table align='center' width="95%">
	<tr>
		<td>
			<b><?php echo $form->cliente->label ?></b>
			<?php echo $form->cliente->output.$form->nombre->output; ?>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%' <?=($form->_status!='show'?' border="0" cellpadding="0" cellspacing="0"':'')?>>
			<tr id='__INPL__' bgcolor='#7098D0'>
				<td><b>C&oacute;digo</b></td>
				<td><b>Descripci&oacute;n</b></td>
				<td><b>Exis.</b></td>
				<td><b>Cant.</b></td>
				<td><b>Precio</b></td>
			</tr>
			<?php
			$pmarcat='';
			$i=0;

			$arreglo=$it=$a=array();

			if($form->_status!='create'){
				$a=$form->_dataobject->get_all();
				if(isset($a['itpfac'])){
					foreach($a['itpfac'] as $k=>$v){
						$it[$v['codigoa']]=$v;
					}
				}
			}

			if($form->_status=='show' ){
				if(isset($a['itpfac'])){
					foreach($a['itpfac'] as $k=>$v){
						if(array_key_exists($v['codigoa'],$sinv)){
							$arreglo[$k]         =$sinv[$v['codigoa']];
							$arreglo[$k]['preca']=$v['preca'];
							$arreglo[$k]['cana'] =$v['cana'];
						}
					}
				}
			}else{
				foreach($sinv as $k=>$v){
					$arreglo[$k]=$v;
					if(array_key_exists($k,$it)){
						$arreglo[$k]['preca']=$it[$k]['preca'];
						$arreglo[$k]['cana'] =$it[$k]['cana'];
					}else{
						$arreglo[$k]['preca']=null;
						$arreglo[$k]['cana'] =0;
					}
				}
			}

			foreach($arreglo as $row) {
					$pmarca  = $row['marca'];
					$pexisten= $row['existen'];
					$peso    = $row['peso'];
					$pdesca  = $row['descrip'].' '.nformat($peso).' KG';
					$codigoa = $row['codigo'];
					$preca   = $row['preca'];
					$cana    = $row['cana'];
					$precio1 = $row['precio1'];
					$precio2 = $row['precio2'];
					$precio1 = $row['precio1'];
					if($form->_status!='show'){
						$f_cana   ='<input id="cana_'.$i.'" onkeyup="total(\''.$i.'\')" class="inputnum" type="text" autocomplete="off" size="4" value="'.($cana>0?$cana:'').'" name="cana_'.$i.'" style="height:30px;font-size:18px">';
					}else{
						$f_cana =nformat($cana);
					}

					$f_codigoa='<input id="codigoa_'.$i.'" class="input" type="hidden" style="height:30px;font-size:16" size="12" value='.$this->db->escape($row['codigo']).' name="codigoa_'.$i.'"> <span id="codigoa_'.$i.'_val">'.$row['codigo'].'</span>';

				if($pmarcat!=$pmarca){
					$pmarcat=$pmarca;
			 ?>
				<tr style="background:#DD3333; font-weight:bold;color:#FFFFFF">
					<td colspan="5"><?php echo $pmarca; ?></td>
				</tr>
				<?php
				}
			?>
			<tr id='tr_itpfac_<?php echo $i; ?>' <?=($i%2 == 0 ?'style="background:#FFFFFF;"':'style="background:#DDDDDD;"')?>>
				<td><?php echo $f_codigoa ?></td>
				<td><?php echo $pdesca    ?></td>
				<td align="right"><?php echo nformat($pexisten)  ?>  </td>
				<td align="right"><?php echo $f_cana;   ?></td>
				<td align="right"><?php
					if($form->_status=='show'){
						echo nformat($preca);
					}else{
						$data = array(
							'name'  => 'iva_'.$i,
							'id'    => 'iva_'.$i,
							'value' => $sinv[$codigoa]['iva'],
							'type'  => 'hidden'
						);
						echo form_input($data);

						$data = array(
							'name'  => 'preca_'.$i,
							'id'    => 'preca_'.$i,
							'value' => $sinv[$codigoa]['precio'.$tiposcli],
							'type'  => 'hidden'
						);
						echo form_input($data).nformat($sinv[$codigoa]['precio'.$tiposcli]);
					}
				?></td>
			</tr>
			<?php $i++;
			} ?>
		</table>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr bgcolor='#7098D0'>
				<td align='right'><b><?php echo $form->totalg->label;  ?></b></td>
				<td align='right'><b id='totalg_value'><?php echo nformat($form->totalg->value); ?></b><?php echo $form->totalg->output; ?></td>
			</tr>
			<tr bgcolor='#FFFDE9'>
				<td><b><?php echo $form->observa->label;   ?></b></td>
				<td><?php echo $form->observa->output;   ?></td>
			</tr>
		</table>
		</td>
	</tr>
</table>
<?php echo $form_end; ?>
<?php endif;
ob_end_flush();

// Función para eliminar todos los espacios en blanco
function comprimir_pagina($buffer) {
    $busca = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
    $reemplaza = array('>','<','\\1');
    return preg_replace($busca, $reemplaza, $buffer);
    //return $buffer;
}
?>
