<?php

ob_start('comprimir_pagina');
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_tl=join("&nbsp;", $form->_button_container["TL"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';


echo $form_begin;
if($form->_status!='show'){ ?>
<script language="javascript" type="text/javascript">
	var importes= new Array();
	function total(id){
		cana=Number(document.getElementById("cana_"+id).value);
		var e= document.getElementById("preca_"+id);
		var preca = Number(e.options[e.selectedIndex].value);
		importes[id]=cana*preca;
		totalizar();
	}
	
	function totalizar(){
		var totalg=0;
		for(var i in importes){
			totalg=totalg+importes[i];
		}
		document.getElementById("totalg_value").innerHTML=totalg;
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
		<table width='100%'><tr><td>
			<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td><strong><?php echo $form->cliente->label.'</strong>'.$form->cliente->output; ?>&nbsp;</td>
			</tr>
			</table>
		</td>
		</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%' <?=($form->_status!='show'?' border="0" cellpadding="0" cellspacing="0"':'')?>>
			<tr id='__INPL__' bgcolor='#7098D0'>
				<td><strong>C&oacute;digo</strong></td>
				<td><strong>Descripci&oacute;n</strong></td>
				<td><strong>Exis</strong></td>
				<td><strong>Cant</strong></td>
				<td><strong>Precio</strong></td>
			</tr>
			<?php 
			$pmarcat='';
			$i=0;
			foreach($sinv as $row) {
				
				if($form->_status!='create'){
					$it_codigoa  = "codigoa_$i";
					$it_cana     = "cana_$i";
					$it_preca    = "preca_$i";
					$it_precat   = "precat_$i";
					$it_pexisten = "pexisten_$i";
					$it_pmarca   = "pmarca_$i";
				
					$pmarca  =$form->_dataobject->get_rel_pointer('itpfac','pmarca'  ,$i);
					$pexisten=$form->_dataobject->get_rel_pointer('itpfac','pexisten',$i);
					$pdesca  =$form->_dataobject->get_rel_pointer('itpfac','pdesca'  ,$i);
					$codigoa =$form->_dataobject->get_rel('itpfac','codigoa',$i);
					$preca   =$form->_dataobject->get_rel('itpfac','preca',$i);
					$cana    =$form->_dataobject->get_rel('itpfac','cana',$i);
					$f_codigoa=$form->$it_codigoa->output;
					$f_cana   =$form->$it_cana->output;
					if($form->_status!='show')
					$f_cana   ='<input id="cana_'.$i.'" onkeyup="total(\''.$i.'\')" class="inputnum" type="text" autocomplete="off" size="2" value="'.($cana>0?$cana:'').'" name="cana_'.$i.'">';
				}else{
					$pmarca  =$row['marca'];
					$pexisten=$row['existen'];
					$pdesca  =$row['descrip'];
					$codigoa =$row['codigo'];
					$precio1 =$row['precio1'];
					$precio2 =$row['precio2'];
					$precio1 =$row['precio1'];
					
					$f_codigoa='<input id="codigoa_'.$i.'" class="input" type="hidden" size="12" value='.$this->db->escape($row['codigo']).' name="codigoa_'.$i.'">
<span id="codigoa_'.$i.'_val">'.$row['codigo'].'</span>';
					$f_cana ='<input id="cana_'.$i.'" onkeyup="total(\''.$i.'\')" class="inputnum" type="text" autocomplete="off" size="2" value="" name="cana_'.$i.'">';
				}
				
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
				<td><?php echo $pdesca  ?>  </td>
				<td align="right"><?php echo nformat($pexisten)   ?></td>
				<td align="right"><?php echo $f_cana;   ?></td>
				<td align="right">
					<?php 
					if($form->_status=='show'){
							echo nformat($preca);
						}else{
							$options = array(
							$sinv[$codigoa]['precio1']=> nformat($sinv[$codigoa]['precio1']),
							$sinv[$codigoa]['precio2']=> nformat($sinv[$codigoa]['precio2']),
							);
							$sel=array();
							if($form->_status!='create' ){
								$options[$preca]=$preca;
								$sel=array($preca=>$preca);
							}
							echo form_dropdown('preca_'.$i, $options,$sel,'style="height:100%;width:60px" id="preca_'.$i.'"');
						}
					?>
					&nbsp;
				</td>
			</tr>
			<?php 
			$i++;
			} ?>
			<tr id='__UTPL__'>
				<td id='cueca'></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%'  >
			<tr bgcolor='7098D0'>
				<td align='right'><strong><?php echo $form->totalg->label;  ?></strong></td>
				<td align='right'><b id='totalg_value'><?php echo nformat($form->totalg->value); ?></b><?php echo $form->totalg->output; ?></td>
			</tr>
			<tr bgcolor="FFFDE9">
				<td ><strong><?php echo $form->observa->label;   ?></strong></td>
				<td ><?php echo $form->observa->output;   ?></td>
			</tr>
		</table>
		<?php echo $form_end; ?>
		</td>
	</tr>
</table>
<?php endif;
ob_end_flush();
// Función para eliminar todos los espacios en blanco
function comprimir_pagina($buffer) {
    $busca = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
    $reemplaza = array('>','<','\\1');
    return preg_replace($busca, $reemplaza, $buffer);
} 
?>
