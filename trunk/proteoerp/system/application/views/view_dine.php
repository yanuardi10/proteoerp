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
               <td class="littletablerowth"><?=$form->numero->label ?></td>
               <td class="littletablerow"><?=$form->numero->output ?></td>
            </tr>
            <tr>
               <td class="littletablerowth"><?=$form->fecha->label ?></td>
               <td class="littletablerow"><?=$form->fecha->output ?></td>
            </tr>
            <tr>
               <td class="littletablerowth"><?=$form->cajero->label ?></td>
               <td class="littletablerow"><?=$form->cajero->output ?></td>
             <tr>
               <td class="littletablerowth"><?=$form->caja->label ?></td>              
               <td class="littletablerow"><?=$form->caja->output ?></td>
            </tr>
        </table>
        <?php echo $form->detalle->output ?>
			 <table  width="100%" style="margin:0;width:100%;" > 
	       <tr>                                                           
	  	       <td colspan=10 class="littletableheader"><div align=""> Totales</div></td>      
	       </tr>                                                          
	       <tr>                                                 
             <td class="littletablerowth"><?=$form->monedas->label ?></td>
             <td class="littletablerow"><?=$form->monedas->output ?></td>
             <td class="littletablerowth"><?=$form->recibido->label ?></td>
             <td class="littletablerow"><?=$form->recibido->output ?></td>
             <td class="littletablerowth"><?=$form->computa->label ?></td>
             <td class="littletablerow"><?=$form->computa->output ?></td>
             <td class="littletablerowth"><?=$form->diferen->label ?></td> 
             <td class="littletablerow"><?=$form->diferen->output ?></td>
         </tr>
			</table>
<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
	  <td>
	<tr>
<table>
<?php endif; ?>