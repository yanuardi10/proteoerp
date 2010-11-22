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
    <td width="20" colspan=2 class="littletableheader"><?=$form->tipo->label    ?></td>
		<td><?=$form->tipo->output   ?></td>
	</tr>

	<tr>
		<td class="littletableheader"><?=$form->descrip->label    ?></td>
		<td colspan=10 height=3><?=$form->descrip->output   ?></td>
	</tr>
		<td class="littletableheader"><?=$form->corta->label    ?></td>
		<td colspan=10><?=$form->corta->output   ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->marca->label ?></td>
		<td colspan=10><?=$form->marca->output?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->susti->label   ?></td>
		<td colspan=10><?=$form->susti->output  ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->dpto->label    ?></td>
		<td colspan=10><?=$form->dpto->output   ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->familia->label ?></td>
		<td colspan=10 id='td_familia'><?=$form->familia->output?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->grupo->label   ?></td>
		<td colspan=10 id='td_grupo'><?=$form->grupo->output  ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->referen->label  ?></td>
		<td colspan=10><?=$form->referen->output ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->barras->label  ?></td>
		<td colspan=10><?=$form->barras->output ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->cu_inve->label  ?></td>
		<td colspan=10><?=$form->cu_inve->output ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->empaque->label  ?></td>
		<td colspan=10><?=$form->empaque->output ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->ensambla->label  ?></td>
		<td colspan=10><?=$form->ensambla->output ?></td>
	</tr>
	<tr>
		<td class="littletableheader">Presentaci&oacute;n</td>
		<td colspan=10><?=$form->fracxuni->output ?> X <?=$form->dempaq->output ?> = <?=$form->mempaq->output ?></td>
	</tr>

	<tr>
		<td class="littletableheader"><?=$form->alcohol->label   ?></td>
		<td colspan=10><?=$form->alcohol->output  ?>X<?=$form->implic->output   ?></td>
	</tr>
	<tr>
		<td class="littletableheader"><?=$form->conjunto->label ?></td>
		<td colspan=10><?=$form->conjunto->output?></td>
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
		<table border=0 width="100%">
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td class="littletableheader">Margen</td>
				<td class="littletableheader">Base  </td>
				<td class="littletableheader"">Precio</td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->costo->label    ?></td>
				<td><?=$form->costo->output   ?></td>
				<td class="littletableheader">Precio 1</td>
				<td><?=$form->margen1->output ?></td>
				<td><?=$form->base1->output   ?></td>
				<td><?=$form->precio1->output ?></td>
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
				<td class="littletableheader"><?=$form->fcalc->label    ?></td>
				<td><?=$form->fcalc->output   ?></td>
				<td class="littletableheader">Precio 4</td>
				<td><?=$form->margen4->output ?></td>
				<td><?=$form->base4->output   ?></td>
				<td><?=$form->precio4->output ?></td>
			</tr>
			<tr>
				<td class="littletableheader"><?=$form->redondeo->label ?></td>
				<td><?=$form->redondeo->output?></td>
				<td class="littletableheader">Precio 5</td>
				<td><?=$form->margen5->output ?></td>
				<td><?=$form->base5->output   ?></td>
				<td><?=$form->precio5->output ?></td>
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
					<table  border=0 width="100%">
					  <tr>
							<td class="littletableheader"><?=$form->tamano->label  ?></td>
							<td ><?=$form->tamano->output ?></td>
					  </tr>
						<tr>
							<td class="littletableheader"><?=$form->medida->label  ?></td>
							<td ><?=$form->medida->output ?></td>
						</tr>
						<tr>
							<td class="littletableheader"><?=$form->serial->label  ?></td>
							<td ><?=$form->serial->output ?></td>
						</tr>
						<tr>
							<td class="littletableheader"><?=$form->minimo->label  ?></td>
							<td "><?=$form->minimo->output ?></td>
						</tr>
						<tr>
							<td class="littletableheader"><?=$form->maximo->label  ?></td>
							<td ><?=$form->maximo->output ?></td>
						</tr>
						<tr>
							<td class="littletableheader"><?=$form->ordena->label  ?></td>
							<td ><?=$form->ordena->output ?></td>
						</tr>
					</table>
		  </td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td valign="top"><?=$form->almacenes->output ?></td>
		</tr>
	</table>
		
	</p>
	</div>
	<div class="tabbertab">
	<h2>Proveedores</h2>
	<p>
		<table border=0 width="100%">
			<tr>
				<td class="littletableheader">Código</td>
				<td class="littletableheader"><p align="center">Nombre</p></td>
				<td class="littletableheader">Fecha</td>
				<td class="littletableheader">P/Unidad</td>
				<td class="littletableheader">P/Bulto</td>
			</tr>
			<tr>
				<td><?=$form->cprv1->output  ?></td>
				<td><?=$form->nprv1->output  ?></td>
				<td><?=$form->fprv1->output  ?></td>
				<td><?=$form->pprv1->output  ?></td>
				<td><?=$form->uprv1->output  ?></td>
			</tr>
			<tr>
				<td><?=$form->cprv2->output  ?></td>
				<td><?=$form->nprv2->output  ?></td>
				<td><?=$form->fprv2->output  ?></td>
				<td><?=$form->pprv2->output  ?></td>
				<td><?=$form->uprv2->output  ?></td>
			</tr>
			<tr>
				<td><?=$form->cprv3->output  ?></td>
				<td><?=$form->nprv3->output  ?></td>
				<td><?=$form->fprv3->output  ?></td>
				<td><?=$form->pprv3->output  ?></td>
				<td><?=$form->uprv3->output  ?></td>
			</tr>
			<tr>
				<td><?=$form->cprv4->output  ?></td>
				<td><?=$form->nprv4->output  ?></td>
				<td><?=$form->fprv4->output  ?></td>
				<td><?=$form->pprv4->output  ?></td>
				<td><?=$form->uprv4->output  ?></td>
			</tr>
			<tr>
				<td><?=$form->cprv5->output  ?></td>
				<td><?=$form->nprv5->output  ?></td>
				<td><?=$form->fprv5->output  ?></td>
				<td><?=$form->pprv5->output  ?></td>
				<td><?=$form->uprv5->output  ?></td>
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