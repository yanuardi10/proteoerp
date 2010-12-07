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
<table width="100%">
	<tr>
		<td align=right><?php echo $container_tr?></td>
	</tr>
	<tr>
		<td>
<fieldset>
	<legend class="subtitulotabla" >Datos del Trabajador </legend>
	<table border=0>
        <tr>
          <td class="littletablerowth"><?=$form->codigo->label  ?></td>
          <td class="littletablerow"><?=$form->codigo->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->nacional->label  ?></td>
          <td class="littletablerow"><?=$form->nacional->output ?><?=$form->cedula->label  ?><?=$form->cedula->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->rif->label  ?></td>
          <td class="littletablerow"><?=$form->rif->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->nombre->label  ?></td>
          <td class="littletablerow"><?=$form->nombre->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->apellido->label  ?></td>
          <td class="littletablerow"><?=$form->apellido->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->sexo->label  ?></td>
          <td class="littletablerow"><?=$form->sexo->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->direc1->label  ?></td>
          <td class="littletablerow"><?=$form->direc1->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->direc2->label  ?></td>
          <td class="littletablerow"><?=$form->direc2->output ?></td>
        </tr>                                                        
        <tr>
          <td class="littletablerowth"><?=$form->direc3->label  ?></td>
          <td class="littletablerow"><?=$form->direc3->output ?></td>
        </tr>   
        <tr>
          <td class="littletablerowth"><?=$form->telefono->label  ?></td>
          <td class="littletablerow"><?=$form->telefono->output ?></td>
        </tr>   
        <tr>
          <td class="littletablerowth"><?=$form->nacimi->label  ?></td>
          <td class="littletablerow"><?=$form->nacimi->output ?></td>
        </tr>
		 <tr>
          <td class="littletablerowth"><?=$form->email->label  ?></td>
          <td class="littletablerow"><?=$form->email->output ?></td>
        </tr> 
		 <tr>
          <td class="littletablerowth"><?=$form->posicion->label  ?></td>
          <td class="littletablerow"><?=$form->posicion->output ?></td>
        </tr>                                
  </table>
</fieldset>
<fieldset>
	<legend class="subtitulotabla" >Relaci&oacute;n Laboral </legend>
	  <table>
 				<tr>
          <td class="littletablerowth"><?=$form->sucursal->label  ?></td>
          <td class="littletablerow"><?=$form->sucursal->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->divi->label  ?></td>
          <td class="littletablerow"><?=$form->divi->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->depa->label  ?></td>
          <td class="littletablerow" id='td_depto'><?=$form->depa->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->contrato->label  ?></td>
          <td class="littletablerow"><?=$form->contrato->output ?></td>
        </tr>
		<tr>
          <td class="littletablerowth"><?//=$form->tipoe->label  ?></td>
          <td class="littletablerow"><?//=$form->tipoe->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->vencimiento->label  ?></td>
          <td class="littletablerow"><?=$form->vencimiento->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->cargo->label  ?></td>
          <td class="littletablerow"><?=$form->cargo->output ?></td>
        </tr>
         <tr>
          <td class="littletablerowth"><?=$form->enlace->label  ?></td>
          <td class="littletablerow"><?=$form->enlace->output ?></td>
        </tr>
 				<tr>
          <td class="littletablerowth"><?=$form->sso->label  ?></td>
          <td class="littletablerow"><?=$form->sso->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->ingreso->label  ?></td>
          <td class="littletablerow"><?=$form->ingreso->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->retiro->label  ?></td>
          <td class="littletablerow"><?=$form->retiro->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->tipo->label  ?></td>
          <td class="littletablerow"><?=$form->tipo->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->dialib->label  ?></td>
          <td class="littletablerow"><?=$form->dialib->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->dialab->label  ?></td>
          <td class="littletablerow"><?=$form->dialab->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->status->label  ?></td>
          <td class="littletablerow"><?=$form->status->output ?></td>
        </tr>
         <tr>
          <td class="littletablerowth"><?=$form->carnet->label  ?></td>
          <td class="littletablerow"><?=$form->carnet->output ?></td>
        </tr>
         <tr>
          <td class="littletablerowth"><?=$form->tipocuent->label  ?></td>
          <td class="littletablerow"><?=$form->tipocuent->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->cuentab->label  ?></td>
          <td class="littletablerow"><?=$form->cuentab->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->turno->label  ?></td>
          <td class="littletablerow"><?=$form->turno->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->horame->label.' Desde'  ?></td>
          <td class="littletablerow"><?=$form->horame->output ?></td>
          <td class="littletablerowth"><?='Hasta'  ?></td>
          <td class="littletablerow"><?=$form->horams->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->horate->label.' Desde'  ?></td>
          <td class="littletablerow"><?=$form->horate->output ?></td>
          <td class="littletablerowth"><?='Hasta' ?></td>
          <td class="littletablerow"><?=$form->horats->output ?></td>
        </tr>
        <tr>
          <td class="littletablerowth"><?=$form->sueldo->label ?></td>
          <td class="littletablerow"><?=$form->sueldo->output ?></td>
          <td class="littletablerowth">&nbsp;</td>
          <td class="littletablerow">&nbsp;</td>
        </tr>	     	
		</table>
</fieldset>		
</div>
<fieldset>
	<legend class="subtitulotabla" >Variables </legend>
	  <table border=0 >
		  <tr>
		    <td class="littletablerowth"><?=$form->vari1->label ?></td><td class="littletablerow"><?=$form->vari1->output ?></td><td class="littletablerowth"><?=$form->vari2->label ?></td><td class="littletablerow"><?=$form->vari2->output ?></td>
		  </tr>
		  <tr>
		    <td class="littletablerowth"><?=$form->vari3->label ?></td><td class="littletablerow"><?=$form->vari3->output ?></td><td class="littletablerowth"><?=$form->vari4->label ?></td><td class="littletablerow"><?=$form->vari4->output ?></td>
			</tr>
		  <tr>
		    <td class="littletablerowth"><?=$form->vari5->label ?></td><td class="littletablerow"><?=$form->vari5->output ?></td><td class="littletablerowth"><?=$form->vari6->label ?></td><td class="littletablerow"><?=$form->vari6->output ?></td>		    
		  </tr>
		</table>
</fieldset>
<?php echo $form_end?>
<?php echo $container_bl ?>
<?php echo $container_br ?>
		<td>
	<tr>
<table>
<?php endif; ?>
