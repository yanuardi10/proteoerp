<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' OR $form->_action=='delete'):
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
              <td colspan=15 class="littletableheader">Encabezado</td>
            </tr>
            <tr>
              <td width="91" class="littletablerowth"><?=$form->numero->label ?></td>
              <td width="155" class="littletablerow"><?=$form->numero->output ?></td>
              <td width="76" class="littletablerowth"><?=$form->cliente->label ?></td>
              <td width="118" class="littletablerow"><?=$form->cliente->output ?></td>
              <td width="52" class="littletablerow"><span class="littletablerowth">
                <?=$form->almacen->label ?>
              </span></td>
              <td width="245" colspan="2" class="littletablerow"><span class="littletablerow">
                <?=$form->almacen->output ?>
              </span><span class="littletablerow">              </span></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->fecha->label ?></td>
              <td class="littletablerow"><?=$form->fecha->output ?></td>
              <td class="littletablerowth"><?=$form->nombre->label ?></td>
              <td colspan="7" class="littletablerow"><?=$form->nombre->output ?></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->vendedor->label ?></td>
              <td class="littletablerow"><?=$form->vendedor->output ?></td>
              <td class="littletablerowth"><?=$form->dir_cli->label ?></td>
              <td colspan="5" class="littletablerow"><?=$form->dir_cli->output ?></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->factura->label ?></td>
              <td class="littletablerow"><?=$form->factura->output ?></td>
              <td class="littletablerowth"><?=$form->dir_cli1->label ?></td>
              <td colspan="3" class="littletablerow"><?=$form->dir_cli1->output ?></td>
            </tr>
          </table>
          <?php echo $form->detalle->output ?>
          <?php //echo $detalle ?>
<table  width="100%" style="margin:0;width:100%;" > 
	    <tr>                                                           
	  	  <td colspan=10 class="littletableheader"><div align=""> Totales</div></td>      
	    </tr>                                                          
	    <tr>                                                 
          <td width="97" class="littletablerowth"><?=$form->orden->label ?></td>
          <td width="137" class="littletablerow"><?=$form->orden->output ?></td>
          <td width="50" class="littletablerowth"><?=$form->iva->label ?></td>
          <td width="147" class="littletablerow"><span class="littletablerow">
            <?=$form->iva->output ?>
          </span></td>
          <td width="77" class="littletablerowth"><?=$form->subtotal->label ?></td>
          <td width="245" class="littletablerow"><?=$form->subtotal->output ?></td>
       </tr>
       <tr>
         <td width="97" class="littletablerowth"><?=$form->observacion->label ?></td>
          <td class="littletablerow"><?=$form->observacion->output ?></td>
         <td colspan="2" class="littletablerow">&nbsp;</td>
          <td width="77" class="littletablerowth"><?=$form->total->label ?></td>
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
