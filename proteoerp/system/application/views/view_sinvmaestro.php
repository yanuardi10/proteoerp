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
    <td width="80"class="littletableheader"><?=$form->codigo->label  ?></td>
		<td width="80"><?=$form->codigo->output ?>
	</tr>
		<tr>
			<td class='littletableheader'><?=$form->alterno->label ?></td>
		  <td colspan=10 height=3><?=$form->alterno->output   ?></td>
	</tr>
	 	<tr>
			<td class='littletableheader'><?=$form->enlase->label ?></td>
		  <td colspan=10 height=3><?=$form->enlase->output   ?></td>
	</tr>
	 	<tr>
			<td class='littletableheader'><?=$form->barras->label ?></td>
		  <td colspan=10 height=3><?=$form->barras->output   ?></td>
	</tr>
	<tr>
			<td class='littletableheader'><?=$form->descrip->label ?></td>
		  <td colspan=10 height=3><?=$form->descrip->output   ?></td>
	</tr>
	<tr>
			<td class='littletableheader'><?=$form->marca->label ?></td>
		  <td colspan=10 height=3><?=$form->marca->output   ?></td>
	</tr>
	 <tr>
			<td class='littletableheader'><?=$form->modelo->label ?></td>
		  <td colspan=10 height=3><?=$form->modelo->output   ?></td>
	</tr>
	<tr>
		 <td width="20" class="littletableheader"><?=$form->tipo->label    ?></td>
		<td><?=$form->tipo->output   ?></td>
	</tr>
		<tr>
			<td class='littletableheader'><?=$form->unidad->label ?></td>
		  <td colspan=10 height=3><?=$form->unidad->output   ?></td>
	</tr>
	 	<tr>
			<td class='littletableheader'><?=$form->tdecimal->label ?></td>
		  <td colspan=10 height=3><?=$form->tdecimal->output   ?></td>
	</tr>
			<tr>
			<td class='littletableheader'><?=$form->activo->label ?></td>
		  <td colspan=10 height=3><?=$form->activo->output   ?></td>
	</tr>
	<tr>
			<td class='littletableheader'><?=$form->serial->label ?></td>
		  <td colspan=10 height=3><?=$form->serial->output   ?></td>
	</tr>
  <tr>
			<td class='littletableheader'><?=$form->clave->label ?></td>
		  <td colspan=10 height=3><?=$form->clave->output   ?></td>
	</tr>
	  <?php  if(isset($form->dpto)) {?>
  <tr>
			<td class='littletableheader'><?=$form->dpto->label ?></td>
		  <td colspan=10 height=3><?=$form->dpto->output   ?></td>
	</tr>
  <tr>
			<td class='littletableheader'><?=$form->linea->label ?></td>
		  <td colspan=10 height=3 id='td_linea'><?=$form->linea->output   ?></td>
	</tr>
	<?php  }?>
	<tr>
			<td class='littletableheader'><?=$form->grupo->label ?></td>
		  <td colspan=10 height=3 id='td_grupo'><?=$form->grupo->output   ?></td>
	</tr>
	 	<tr>
			<td class='littletableheader'><?=$form->fracci->label ?></td>
		  <td colspan=10 height=3><?=$form->fracci->output   ?></td>
	</tr>
 	<tr>
			<td class='littletableheader'><?=$form->peso->label ?></td>
		  <td colspan=10 height=3><?=$form->peso->output   ?></td>
	</tr>
		<tr>
			<td class='littletableheader'><?=$form->clase->label ?></td>
		  <td colspan=10 height=3><?=$form->clase->output   ?></td>
	</tr>
  <tr>
			<td class='littletableheader'><?=$form->garantia->label ?></td>
		  <td colspan=10 height=3><?=$form->garantia->output   ?></td>
	</tr>
		<tr>
			<td class='littletableheader'><?=$form->comision->label ?></td>
		  <td colspan=10 height=3><?=$form->comision->output   ?></td>
	</tr>
</table>
</fieldset>
</tr>
</td>
</table>
<div class="tabber">
	<div class="tabbertab">
	<h2>Costos y Precios</h2>
	<p>
		<table border=0>
			<tr>
				<td class="littletableheader"><?=$form->fechav->label    ?></td>
        <td><?=$form->fechav->output   ?></td>
        <td class="littletableheader"></td>
				<td class="littletableheader">Margen</td>
				<td class="littletableheader">Base  </td>
				<td class="littletableheader">Precio</td>
			</tr>
  		<tr>
				<td class="littletableheader"><?=$form->pond->label    ?></td>
				<td ><?=$form->pond->output   ?></td>
				<td class="littletableheader">Precio 1</td>
				<td ><?=$form->margen1->output ?></td>
				<td ><?=$form->base1->output   ?></td>
				<td ><?=$form->precio1->output ?></td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->ultimo->label   ?></td>
				<td><?=$form->ultimo->output  ?></td>
				<td class="littletableheader">Precio 2</td>
				<td><?=$form->margen2->output ?></td>
				<td><?=$form->base2->output   ?></td>
				<td><?=$form->precio2->output ?></td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->iva->label   ?></td>
				<td><?=$form->iva->output  ?>%</td>
				<td class="littletableheader">Precio 3</td>
				<td><?=$form->margen3->output ?></td>
				<td><?=$form->base3->output   ?></td>
				<td><?=$form->precio3->output ?></td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->us->label    ?></td>
				<td><?=$form->us->output   ?></td>
				<td class="littletableheader">Precio 4</td>
				<td><?=$form->margen4->output ?></td>
				<td><?=$form->base4->output   ?></td>
				<td><?=$form->precio4->output ?></td>
			</tr>
				<td class="littletableheader"><?=$form->formcal->label ?></td>
				<td><?=$form->formcal->output?></td>
				<td class="littletableheader"><?=$form->redecen->label ?></td>
				<td><?=$form->redecen->output?></td>
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
							<td class="littletableheader"><?=$form->exdes->label  ?></td>
							<td ><?=$form->exdes->output ?></td>
					  </tr>
						<tr>
							<td class="littletableheader"><?=$form->existen->label  ?></td>
							<td ><?=$form->existen->output ?></td>
						</tr>
						<tr>
							<td class="littletableheader"><?=$form->exmin->label  ?></td>
							<td "><?=$form->exmin->output ?></td>
						</tr>
						<tr>
							<td class="littletableheader"><?=$form->exmax->label  ?></td>
							<td ><?=$form->exmax->output ?></td>
						</tr>
						<tr>
							<td class="littletableheader"><?=$form->exord->label  ?></td>
							<td ><?=$form->exord->output ?></td>
						</tr>
					</table>
		  </td>
				<td valign="top">
					<?=$form->almacenes->output ?>
				</td>
		</tr>
	</table>
		
	</p>
	</div>
	<div class="tabbertab">
	<h2>Proveedores</h2>
	<p>
		<table border=0 >
			<tr>
				<td class="littletableheader">Fecha</td>
				<td class="littletableheader">Proveedor</td>
				<td class="littletableheader">Precio</td>
			</tr>
			<tr>
			  <td><?=$form->pfecha1->output?></td>
				<td><?=$form->prov1->output?>
				<td><?=$form->prepro1->output?>
			</tr>
			<tr>
				<td><?=$form->pfecha2->output?></td>
				<td><?=$form->prov2->output?>
				<td><?=$form->prepro2->output?>
			</tr>
			<tr>
				<td><?=$form->pfecha3->output?></td>
				<td><?=$form->prov3->output?>
				<td><?=$form->prepro3->output?>
			</tr>
		</table>
	</p>
</div>
<?php echo $container_bl.$container_br; ?>
</div>     
<?php echo $form_end?>
<?php 
	//foreach (get_object_vars($form) as $label=>$tiene)
	//	echo "$label => $tiene <br>";
	//echo '<pre>';print_r($form->grupo->request);echo '</pre>'
?>