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
              <td width="98" class="littletablerowth"><?=$form->numero->label ?></td>
              <td width="114" class="littletablerow"><?=$form->numero->output ?></td>
              <td width="116" class="littletablerowth"><?=$form->cliente->label ?></td>
              <td width="79" class="littletablerow"><?=$form->cliente->output ?></td>
              <td width="74" class="littletablerowth"><?=$form->fecha1->label ?></td>
              <td width="272" class="littletablerow"><?=$form->fecha1->output ?></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->fecha->label ?></td>
              <td class="littletablerow"><?=$form->fecha->output ?></td>
              <td class="littletablerowth"><?=$form->nombre->label ?></td>
              <td colspan="7" class="littletablerow"><?=$form->nombre->output ?></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->factura->label ?></td>
              <td colspan="7" class="littletablerow"><?=$form->factura->output ?></td>
            </tr>
          </table>
          <?php echo $form->detalle->output ?>
          <?php //echo $detalle ?>
<table  width="100%" style="margin:0;width:100%;" > 
</table>
<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
	  <td>
	<tr>
<table>
<?php endif; ?>
