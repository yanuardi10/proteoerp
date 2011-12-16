<?php
ob_start('comprimir_pagina'); 

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

$campos=$form->template_details('itpfac');
$scampos  ='<tr id="tr_itpfac_<#i#>">';
$scampos .='<td class="littletablerow" align="left" >'.$campos['codigoa']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['desca']['field'].'</td>';
$scampos .='<td class="littletablerow" align="left" >'.$campos['pexisten']['field'].'</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['cana']['field'].  '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['preca']['field'].$campos['dxapli']['field']. '</td>';
$scampos .='<td class="littletablerow" align="right">'.$campos['tota']['field'];
for($o=1;$o<5;$o++){
	$it_obj   = "precio${o}";
	$scampos .= $campos[$it_obj]['field'];
}
$scampos .= $campos['itiva']['field'];
$scampos .= $campos['sinvtipo']['field'];
$scampos .= $campos['itpvp']['field'];
$scampos .= $campos['itcosto']['field'];
$scampos .= $campos['sinvpeso']['field'];
$scampos .= $campos['itmmargen']['field'];
$scampos .= $campos['itformcal']['field'];
$scampos .= $campos['itultimo']['field'];
$scampos .= $campos['itpond']['field'];
$scampos .= $campos['precat']['field'];
$scampos .= $campos['itpm']['field'].'</td>';
$scampos .= '<td class="littletablerow"  align="center"><a href=# onclick="del_itpfac(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
$campos=$form->js_escape($scampos);

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_scripts;
echo $form_begin;
if($form->_status!='show'){ ?>

<script language="javascript" type="text/javascript">
</script>
<?php } ?>
<table align='center' width="95%">
	<tr>
<?php if ($form->_status=='show') { ?>
		<td>
		<a href="#" onclick="window.open('<?php echo base_url() ?>formatos/verhtml/PFAC/<?php echo $form->numero->value ?>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');" heigth="600" >
		<img src='<?php echo base_url() ?>images/html_icon.gif'></a>
		</td>
<?php } ?>
		<td align=right><?php echo $container_tr?></td>
	</tr>
</table>
<table align='center' width="95%">
	<tr>
		<td>
		<table width='100%'><tr><td>
			<table width="100%" style="margin: 0; width: 100%;">
			<tr>
				<td class="littletableheader"><strong><?php echo $form->cliente->label;    ?></strong>*&nbsp;</td>
				<td class="littletablerow">   <?php echo $form->cliente->output;   ?>&nbsp;</td>
			</tr>
			</table>
		</td>
		</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td>
		<table width='100%'>
			<tr id='__INPL__'>
				<td bgcolor='#7098D0'><strong>C&oacute;digo</strong></td>
				<td bgcolor='#7098D0'><strong>Descripci&oacute;n</strong></td>
				<td bgcolor='#7098D0'><strong>Existencia</strong></td>
				<td bgcolor='#7098D0'><strong>Cantidad</strong></td>
				<td bgcolor='#7098D0'><strong>Precio</strong></td>
			</tr>

			<?php for($i=0;$i<$form->max_rel_count['itpfac'];$i++) {
				$it_codigoa  = "codigoa_$i";
				$it_desca    = "desca_$i";
				$it_cana     = "cana_$i";
				$it_preca    = "preca_$i";
				$it_tota     = "tota_$i";
				$it_iva      = "itiva_$i";
				$it_peso     = "sinvpeso_$i";
				$it_tipo     = "sinvtipo_$i";
				$it_costo    = "itcosto_$i";
				$it_pvp      = "itpvp_$i";
				$it_mmargen  = "itmmargen_$i";
				$it_dxapli   = "dxapli_$i";
				$it_pond     = "itpond_$i";
				$it_ultimo   = "itultimo_$i";
				$it_formcal  = "itformcal_$i";
				$it_pm       = "itpm_$i";
				$it_precat   = "precat_$i";
				$it_pexisten = "pexisten_$i";
			?>
			<tr id='tr_itpfac_<?php echo $i; ?>'>
				<td class="littletablerow" align="left" nowrap><?php echo $form->$it_codigoa->output; ?></td>
				<td class="littletablerow" align="left" ><?php echo $form->$it_desca->output;  ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_pexisten->output;   ?></td>
				<td class="littletablerow" align="right"><?php echo $form->$it_cana->output;   ?></td>
				<td class="littletablerow" align="right">
				<?php 
				if ($form->_status=='show') { 
						echo $form->$it_preca->output;  
					}else{
						$codigoa=$form->_dataobject->get_rel('itpfac','codigoa',$i);
						$row=$this->datasis->damerow("SELECT precio1,precio2,precio3,precio4 FROM sinv WHERE codigo='$codigoa'");
						$options = array(
						$row['precio1']=> $row['precio1'],
						$row['precio2']=> $row['precio2'],
						$row['precio3']=> $row['precio3'],
						$row['precio4']=> $row['precio4'],
						);
						echo form_dropdown('preca_'.$i, $options);
					}
				?>
				</td>

			</tr>
			<?php } ?>
			<tr id='__UTPL__'>
				<td id='cueca'></td>
			</tr>
		</table>
		
		</td>
	</tr>
	<tr>
		<td>
		
		<table width='100%'  background='#FFFDE9'>
			<tr>
				<td class="littletablerow"    width='350'><?php echo $form->observa->label;   ?></td>
				<td class="littletableheader">           <?php echo $form->totals->label;  ?></td>
				<td class="littletablerow" align='right'><b id='totals_val'><?php echo nformat($form->totals->value); ?></b><?php echo $form->totals->output; ?></td>
			<tr></tr>
				<td class="littletablerow"   ><?php echo $form->observa->output;   ?></td>
				<td class="littletableheader"><?php echo $form->ivat->label;    ?></td>
				<td class="littletablerow" align='right'><b id='ivat_val'><?php echo nformat($form->ivat->value); ?></b><?php echo $form->ivat->output; ?></td>
			<tr></tr>
				<td><?php echo $form->observ1->output;    ?></td>
				<td class="littletableheader">           <?php echo $form->totalg->label;  ?></td>
				<td class="littletablerow" align='right' style='font-size:18px;font-weight: bold'><b id='totalg_val'><?php echo nformat($form->totalg->value); ?></b><?php echo $form->totalg->output; ?></td>
			</tr>
		</table>
		
		<?php echo $form_end; ?>
		</td>
	</tr>
</table>
<?php endif; ?>
<?php
ob_end_flush();
// Función para eliminar todos los espacios en blanco
function comprimir_pagina($buffer) {
    $busca = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
    $reemplaza = array('>','<','\\1');
    return preg_replace($busca, $reemplaza, $buffer);
} 
?>
