<?php echo $form_begin;
if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';
?>
<script>
$(function() {
	$( "#tabs" ).tabs();
	$( "#fecha1" ).datepicker({ dateFormat: "dd/mm/yy" });
	$( "#fecha2" ).datepicker({ dateFormat: "dd/mm/yy" });
	$( "#fecha3" ).datepicker({ dateFormat: "dd/mm/yy" });
	
	$('#cliente').autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscascli'); ?>",
				type: "POST",
				dataType: "json",
				data: "q="+req.term,
				success:
					function(data){
						var sugiere = [];
						$.each(data,
							function(i, val){
								sugiere.push( val );
							}
						);
						add(sugiere);
					},
			})
		},
		minLength: 2,
		select: function( event, ui ) {
			//$('#nomcli').val(ui.item.nombre);
			$('#nomcli').text(ui.item.nombre);
			$('#cliente').val(ui.item.cliente);
		}
	});

	//Autocomplete de los codigos
	$('#barras1').autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ajax/buscasinv'); ?>",
				type: "POST",
				dataType: "json",
				data: "q="+req.term,
				success:
					function(data){
						var sugiere = [];

						if(data.length==0){
							$('#barras1').val("");
							$('#descrip1').val("");
						}else{
							$.each(data,
								function(i, val){
									sugiere.push( val );
								}
							);
						}
						add(sugiere);
					},
			})
		},
		minLength: 2,
		select: function( event, ui ) {
			$('#barras1').attr("readonly", "readonly");
			$('#barras1').val(ui.item.codigo);
			$('#descrip1').val(ui.item.descrip);
			setTimeout(function() {  $('#codigo1').removeAttr("readonly"); }, 1500);
		}
	});

});
</script>

<table width='100%' align='center'>
	<tr>
		<td>
			<fieldset  style='border: 1px outset #FEB404;background: #FFFCE8;'>
			<table width="100%"  style="margin:0;width:100%;" cellspacing='2' cellpadding='2'>
				<tr>
					<td  style='border: 1px solid grey;'>
						<table width="100%">
							<tr>
								<td class="littletablerowth" width='40'>               <?php echo $form->cedula->label  ?></td>
								<td class="littletablerow"   align='left'  width='150'><?php echo $form->cedula->output ?></td>
								<td class="littletablerowth" align='right' width='100'><?php echo $form->nombre->label  ?></td>
								<td class="littletablerow">                            <?php echo $form->nombre->output ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style='border: 1px solid grey;'>
						<table width='100%'>
							<tr>
								<td class="littletablerowth"><?php echo $form->tcelular->label; ?></td>
								<td class="littletablerow">  <?php echo $form->tcelular->output;?></td>
								
								<td class="littletablerowth"><?php echo $form->toficina->label; ?></td>
								<td class="littletablerow">  <?php echo $form->toficina->output;?></td>
								
								<td class="littletablerowth"><?php echo $form->tcasa->label;    ?></td>
								<td class="littletablerow">  <?php echo $form->tcasa->output;   ?></td>
							</tr>
							<tr>
								<td class="littletablerowth"><?php echo $form->email->label;    ?></td>
								<td colspan="3" class="littletablerow">  <?php echo $form->email->output;   ?></td>
								<td class="littletablerowth"><?php echo $form->pin->label;    ?></td>
								<td class="littletablerow">  <?php echo $form->pin->output;   ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td  style='border: 1px solid grey;'>
						<table width="100%">
							<tr>
								<td class="littletablerowth" width='40'>  <?php echo $form->cliente->label  ?></td>
								<td class="littletablerow"   width='70'><?php echo $form->cliente->output ?></td>
								<td class="littletablerow"   align='left'><b id='nomcli'></b>&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td>
	<div id="tabs">
    <ul>
        <li><a href="#tabs-1">Medicamentos</a></li>
        <li><a href="#tabs-2">Direccion</a></li>
        <li><a href="#tabs-3">Redes Sociales</a></li>
        <li><a href="#tabs-4">Observaciones</a></li>
    </ul>
    <div id="tabs-1">
		<table width='100%' cellpadding="0" cellspacing="0">
			<tr>
				<th class="littletableheaderdet">Codigo Barras</th>
				<th class="littletableheaderdet">Descripcion</th>
				<th class="littletableheaderdet">Fecha</th>
				<th class="littletableheaderdet">Cant.</th>
				<th class="littletableheaderdet">Dias</th>
			</tr>
			<tr>
				<td class="littletablerowth"><?php echo $form->barras1->output; ?></td>
				<td class="littletablerowth"><?php echo $form->descrip1->output;?></td>
				<td class="littletablerowth"><?php echo $form->fecha1->output;  ?></td>
				<td class="littletablerowth"><?php echo $form->cant1->output;   ?></td>
				<td class="littletablerowth"><?php echo $form->dias1->output;   ?></td>
			</tr>
			<tr>
				<td class="littletablerowth"><?php echo $form->barras2->output; ?></td>
				<td class="littletablerowth"><?php echo $form->descrip2->output;?></td>
				<td class="littletablerowth"><?php echo $form->fecha2->output;  ?></td>
				<td class="littletablerowth"><?php echo $form->cant2->output;   ?></td>
				<td class="littletablerowth"><?php echo $form->dias2->output;   ?></td>
			</tr>
			<tr>
				<td class="littletablerowth"><?php echo $form->barras3->output; ?></td>
				<td class="littletablerowth"><?php echo $form->descrip3->output;?></td>
				<td class="littletablerowth"><?php echo $form->fecha3->output;  ?></td>
				<td class="littletablerowth"><?php echo $form->cant3->output;   ?></td>
				<td class="littletablerowth"><?php echo $form->dias3->output;   ?></td>
			</tr>
		</table>
    </div>
    <div id="tabs-2">
		<table width='100%' cellspacing="0" cellpadding="0">
			<tr>
				<td class="littletablerowth"><?php echo $form->direccion->label  ?></td>
			</tr><tr>
				<td  class="littletablerow">  <?php echo $form->direccion->output ?></td>
			</tr>
		</table>
    </div>
    <div id="tabs-3">
		<table width='100%'>
			<tr>
				<td class="littletablerowth"><?php echo $form->fb->label ?></td>
				<td class="littletablerow">  <?php echo $form->fb->output ?></td>
			</tr><tr>
				<td class="littletablerowth"><?php echo $form->twitter->label  ?></td>
				<td class="littletablerow">  <?php echo $form->twitter->output ?></td>
			</tr><tr>
				<td class="littletablerowth"><?php echo $form->chat->label  ?></td>
				<td class="littletablerow">  <?php echo $form->chat->output ?></td>
			</tr>
		</table>
    </div>
    <div id="tabs-4">
		<table width='100%' cellspacing="0" cellpadding="0">
			<tr>
				<td class="littletablerowth"><?php echo $form->observa->label  ?></td>
			</tr><tr>
				<td  class="littletablerow">  <?php echo $form->observa->output ?></td>
			</tr>
		</table>
    </div>




</div>
		<td>
	</tr>
	
	
	
</table>


<?php echo $form_end?>

<?php
/*
<div id='contenido'>
	<table width="100%" border=0 align="center">
		<tr>
			<td>
<?php 
if (isset($content)) { 
	echo $content."</td>";
} else { 
	echo "<td colspan='2'>";
	if (isset($content)) 
		echo $content."</td>";
};?>
		</tr>
	</table>
</div>
<div class="footer">
	<p>Tiempo de la consulta {elapsed_time} seg | Proteo ERP </p>
</div>
<?php if (isset($extras)) echo $extras; ?>
*/
?>
