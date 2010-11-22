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
              <td colspan=11 class="littletableheader">Encabezado</td>
            </tr>
            <tr>
              <td width="100" class="littletablerowth"><?=$form->fecha->label ?></td>
              <td width="100" class="littletablerow"><?=$form->fecha->output ?></td>
              <td width="119" class="littletablerowth"><?=$form->proveedor->label ?></td>
              <td colspan="3" class="littletablerow"><?=$form->proveedor->output ?></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->numero->label ?></td>
              <td class="littletablerow"><?=$form->numero->output ?></td>
              <td class="littletablerowth"><?=$form->nombre->label ?></td>
              <td colspan="3" class="littletablerow"><?=$form->nombre->output ?></td>
            </tr>
            <tr>
              <td class="littletablerowth"><?=$form->status->label ?></td>
              <td class="littletablerow"><?=$form->status->output ?></td>
              <td class="littletablerowth"><?=$form->arribo->label ?></td>
              <td class="littletablerow"><?=$form->arribo->output ?></td>
              </tr>
          </table>
          <?php echo $form->detalle->output ?>
          <?php //echo $detalle ?>
         <table  width="100%" style="margin:0;width:100%;" > 
	    <tr>                                                           
	  	  <td colspan=10 class="littletableheader">Totales</td>      
	    </tr>                                                          
	    <tr>                                                 
          <td width="125" class="littletablerowth"><?=$form->anticipo->label ?> </td>
		  <td width="125" class="littletablerow"><?=$form->anticipo->output ?> </td>
	      <td width="131" class="littletablerowth"><?=$form->monto->label ?> </td>
		  <td width="122" class="littletablerow" ><?=$form->monto->output ?> </td>
		  <td width="111" class="littletablerowth" ><?=$form->subtotal->label ?> </td>
		 <td width="139" class="littletablerow" ><?=$form->subtotal->output ?> </td>
       </tr>
       <tr>
         <td class="littletablerowth"><?=$form->bancocj->label ?></td>
	 	 <td class="littletablerow" ><?=$form->bancocj->output ?></td>
         <td class="littletablerowth"><?=$form->tipo_op->label ?></td>
		 <td class="littletablerow" ><?=$form->tipo_op->output ?></td>
		 <td class="littletablerowth"><?=$form->impuesto->label ?></td>
		 <td class="littletablerow" ><?=$form->impuesto->output ?></td>
       </tr>
	   <tr>
         <td class="littletablerowth"><?=$form->numero->label ?></td>
		 <td class="littletablerow" ><?=$form->numero->output ?></td>
         <td class="littletablerowth"><?=$form->peso->label ?></td>
		 <td class="littletablerow" ><?=$form->peso->output ?></td>
		 <td class="littletablerowth"><?=$form->total->label ?></td>
		 <td class="littletablerow" ><?=$form->total->output ?></td>
      </tr>
</table>

<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
	  <td>
	<tr>
<table>
<?php endif; ?>
