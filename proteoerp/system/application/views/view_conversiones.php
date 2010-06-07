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
            <td colspan=10 class="littletableheader">Encabezado</td>
            </tr>
            <tr>
              <td width="126" class="littletablerowth">&nbsp;</td>
              <td width="211" class="littletablerow">&nbsp;</td>
              <td width="306" class="littletablerowth"><?=$form->observ1->label ?></td>
              <td width="312" class="littletablerowth">&nbsp;</td>
            </tr>
            <tr>
              <td width="126" class="littletablerowth"><?=$form->numero->label ?></td>
              <td width="211" class="littletablerow"><?=$form->numero->output ?></td>
              <td class="littletablerow"><?=$form->observ1->output ?></td>
              <td class="littletablerowth"><?=$form->almacen->label ?></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->fecha->label ?></td>
              <td class="littletablerow"><?=$form->fecha->output ?></td>
              <td class="littletablerow"><?=$form->observ2->output ?></td>
              <td colspan="2" class="littletablerow"><?=$form->almacen->output ?></td>
            </tr>
        </table>
        <?php echo $form->detalle->output ?>
        <?php //echo $detalle ?>
<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
	  <td>
	<tr>
<table>
<?php endif; ?>
