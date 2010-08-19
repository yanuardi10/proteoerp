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
              <td colspan=13 class="littletablerowth">Producto</td>
           </tr>
           <tr>
            	<td width="112" class="littletableheader"><?=$form->barras->label ?></td>
              <td width="108" class="littletablerow"><?=$form->barras->output ?></td>
           </tr>
           <tr>
              <td width="109" class="littletableheader"><?=$form->codigo->label ?></td>
              <td width="143" class="littletablerow"><?=$form->codigo->output ?></td>
           </tr>
           <tr>
              <td width="112" class="littletableheader"><?=$form->descrip->label ?></td>
              <td width="108" class="littletablerow"><?=$form->descrip->output ?></td>
           <tr>
              <td width="112" class="littletableheader"><?=$form->descrip2->label ?></td>
              <td width="108" class="littletablerow"><?=$form->descrip2->output ?></td>
           </tr>
           <tr>
              <td width="112" class="littletableheader"><?=$form->precio1->label ?></td>
              <td width="108" class="littletablerow"><?=$form->precio1->output ?></td>
           </tr> 
				</table>

<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
	  <td>
	<tr>
<table>
<?php endif; ?>
