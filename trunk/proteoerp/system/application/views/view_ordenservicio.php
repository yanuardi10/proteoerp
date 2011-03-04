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
           <td colspan=11 class="littletableheader">Encabezado</td>
       </tr>
       <tr>
            <td width="83" class="littletablerowth"><?=$form->fecha->label ?></td>
            <td width="93" class="littletablerow"><?=$form->fecha->output ?></td>
            <td width="101" class="littletablerowth"><?=$form->numero->label ?></td>
            <td width="468" class="littletablerow"><?=$form->numero->output ?></td>
       </tr>
       <tr>
            <td class="littletablerowth"><?=$form->proveedor->label ?></td>
            <td class="littletablerow"><?=$form->proveedor->output ?></td>
            <td class="littletablerowth"><?=$form->nombre->label ?></td>
            <td class="littletablerow"><?=$form->nombre->output ?></td>
       </tr>
       </table>
       <?php echo $form->detalle->output ?>
       <?php //echo $detalle ?>
       <table  width="100%" style="margin:0;width:100%;" > 
	     <tr>                                                           
   	        <td colspan=10 class="littletableheader">Totales</td>      
       </tr>                                                          
       <tr>                                                 
  	        <td width="89" class="littletablerowth"><?=$form->banco->label ?> </td>
  	        <td width="89" class="littletablerow" ><?=$form->banco->output ?> </td>
  	        <td width="101" class="littletablerowth"><?=$form->numero1->label ?> </td>
  	        <td width="115" class="littletablerow"><?=$form->numero1->output ?> </td>
  	        <td width="87" class="littletablerowth" ><?=$form->subtotal->label ?> </td>
  	        <td width="256" class="littletablerow" ><?=$form->subtotal->output ?> </td>
       </tr>
       <tr>
            <td class="littletablerowth"><?=$form->tipo->label ?></td>
		        <td class="littletablerow" ><?=$form->tipo->output ?></td>
            <td class="littletablerowth"><?=$form->anticipo->label ?></td>
		        <td class="littletablerow" ><?=$form->anticipo->output ?></td>
		        <td class="littletablerowth"><?=$form->impuesto->label ?></td>
		        <td class="littletablerow" ><?=$form->impuesto->output ?></td>
        </tr>
        <tr>
            <td colspan="2" class="littletablerowth">&nbsp;</td>
		        <td class="littletablerowth"><?=$form->comprob->label ?></td>
		        <td class="littletablerow" ><?=$form->comprob->output ?></td>
		        <td class="littletablerowth"><?=$form->total->label ?></td>
		        <td class="littletablerow" ><?=$form->total->output ?></td>
        </tr>
       </table>
&nbsp;
      <table  width="100%" style="margin:0;width:100%;">
	    <tr>
            <td width="12%" class="littletablerowth"><?=$form->beneficiario->label ?></td>
            <td width="21%" class="littletablerow" ><?=$form->beneficiario->output ?></td>
            <td width="19%" class="littletablerowth"><?=$form->condiciones->label ?></td>
            <td width="48%" colspan="3" class="littletablerow" ><?=$form->condiciones->output ?></td>
       </tr>
      </table>

<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
	  <td>
	<tr>
<table>
<?php endif; ?>
<p>&nbsp;</p>