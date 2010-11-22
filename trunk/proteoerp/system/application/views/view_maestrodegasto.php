<?php echo $form_scripts?>
<?php echo $form_begin?>
<?php 
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
?>
<?php if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<table border=0 width="100%">
<tr><td align='right' ><?php echo $container_tr; ?></td></tr>
<tr><td>
<fieldset>
<legend class="subtitulotabla" >Producto </legend>
<table border=0 width="100%">
	<tr>
    <td width="80"class="littletableheader"><?=$form->nom_grup->label  ?></td>
		<td><?=$form->nom_grup->output ?>    
	</tr>
	<tr>
    <td width="80"class="littletableheader"><?=$form->codigo->label  ?></td>
		<td><?=$form->codigo->output ?>    
	</tr>
    <td class="littletableheader"><?=$form->descrip->label    ?></td>
		<td colspan=7 height=3><?=$form->descrip->output   ?></td>
	</tr>	
	<tr>
		<td class="littletableheader"><?=$form->tipo->label ?></td>
		<td colspan=7><?=$form->tipo->output?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->grupo->label    ?></td>
		<td colspan=7><?=$form->grupo->output?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->iva->label   ?></td>
		<td colspan=7><?=$form->iva->output  ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->cuenta->label ?></td>
		<td colspan=7 id='td_familia'><?=$form->cuenta->output?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->amorti->label   ?></td>
		<td colspan=7 id='td_grupo'><?=$form->amorti->output  ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->dacumu->label  ?></td>
		<td colspan=7><?=$form->dacumu->output ?></td>
	</tr>
</table>
</fieldset>
</tr>
</td>
</table>
<div class="tabber">
	<div class="tabbertab">
	<h2>Costos</h2>
	<p>
	  <table>
		  <tr>
			  <td width="17" class="littletableheader"><?=$form->ultimo->label    ?></td>
			  <td width="17"><?=$form->ultimo->output   ?></td>
		  </tr>
		  <tr>
			  <td class="littletableheader"><?=$form->promedio->label   ?></td>
			  <td><?=$form->promedio->output  ?></td>
		  </tr>
		</table>	
		</p>
  </div>
	
	<div class="tabbertab">
	<h2>Existencias</h2>
	<p>

	  <table>
	  	<tr>
	  		<td valign="top">
					<table table border=0 >
					  <tr>
							<td class="littletableheader"><?=$form->medida->label  ?></td>
							<td ><?=$form->medida->output ?></td>
					  </tr>
						<tr>
							<td class="littletableheader"><?=$form->fraxuni->label  ?></td>
							<td ><?=$form->fraxuni->output ?></td>
						</tr>
						<tr>
							<td class="littletableheader"><?=$form->minimo->label  ?></td>
							<td ><?=$form->minimo->output ?></td>
						</tr>
						<tr>
							<td class="littletableheader"><?=$form->maximo->label  ?></td>
							<td "><?=$form->maximo->output ?></td>
						</tr>
				</table>
		        </td><td valign="top">
				<?=$form->almacenes->output ?>
				</td>
		</tr>
	</table>
		
	</p>
	</div>

</div>
<?php echo $container_bl.$container_br; ?>
</div>     
<?php echo $form_end?>
<?php 
	//foreach (get_object_vars($form) as $label=>$tiene)
	//	echo "$label => $tiene <br>";
	//echo '<pre>';print_r($form->grupo->request);echo '</pre>'
?>