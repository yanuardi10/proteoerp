<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:
 
if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_scripts;
echo $form_begin?>
<table align='center'>
	<tr>
		<td align=right>
			<?php echo $container_tr?>
		</td>
	</tr>
	<tr>
		<td>
          <table width="100%"  style="margin:0;width:100%;">
            <tr>
              <td colspan=14 class="littletableheader">Encabezado</td>
            </tr>
            <tr>
              <td width="141" class="littletablerowth"><?=$form->presupuesto->label ?></td>
              <td width="179" class="littletablerow"><?=$form->presupuesto->output ?></td>
              <td width="131" class="littletablerowth"><?=$form->cliente->label ?></td>
              <td width="226" class="littletablerow"><?=$form->cliente->output ?></td>
              <td width="106" class="littletablerowth"><?=$form->rifci->label ?></td>
              <td width="168" class="littletablerow"> <?=$form->rifci->output ?></td>
              <td width="6" colspan="3" class="littletablerow"><span class="littletablerowth"></span>
              <span class="littletablerowth"> </span></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->numero->label ?></td>
              <td class="littletablerow"><?=$form->numero->output ?></td>
              <td class="littletablerowth"><?=$form->nombre->label ?></td>
              <td colspan="6" class="littletablerow"><?=$form->nombre->output ?></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->fecha->label ?></td>
              <td class="littletablerow"><?=$form->fecha->output ?></td>
              <td class="littletablerowth"><?=$form->direc->label ?></td>
              <td colspan="4" class="littletablerow"><?=$form->direc->output ?></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->vende->label ?></td>
              <td class="littletablerow"><?=$form->vende->output ?></td>
              <td class="littletablerow"><?=$form->dire1->label ?></td>
              <td colspan="3" class="littletablerow"><?=$form->dire1->output ?></td>
            </tr>
          </table>
          <?php echo $form->detalle->output ?>
          <?php //echo $detalle ?>
          <table  width="100%" style="margin:0;width:100%;" > 
	         <tr>                                                           
	  	      <td colspan=10 class="littletableheader"><div align=""> Totales</div></td>      
          </tr>                                                          
	        <tr>                                                 
             <td width="112" class="littletablerowth"><?=$form->anticipo->label ?></td>
             <td width="114" class="littletablerow"><?=$form->anticipo->output ?></td>
             <td width="118" class="littletablerowth"><?=$form->iva->label ?></td>
             <td width="110" class="littletablerow"><?=$form->iva->output ?></td>
             <td width="102" class="littletablerowth"><?=$form->subtotal->label ?></td>
             <td width="197" class="littletablerow"><?=$form->subtotal->output ?></td>
         </tr>
         <tr>
             <td width="112" class="littletablerowth"><?=$form->referencia->label ?></td>
             <td class="littletablerow"><?=$form->referencia->output ?></td>
             <td width="118" class="littletablerowth"><?=$form->vence->label ?></td>
             <td class="littletablerow"><?=$form->vence->output ?></td>
             <td width="102" class="littletablerowth"><?=$form->total->label ?></td>
             <td class="littletablerow"><?=$form->total->output ?></td>
         </tr>
         </table>

<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
	  <td>
	<tr>
<table>
<?php endif; ?>
