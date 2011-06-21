<script type="text/javascript">
$(document).ready(function() {
	$('#barras').focus(function() {
		$(this).val('');
	});
	$('#barras').focusout(function() {
		$(this).val('Introduzca un código de producto');
	});
	function log( message ) {
		$( "<div/>" ).text( message ).prependTo( "#log" );
		$( "#log" ).attr( "scrollTop", 0 );
	}

	$("#barras").autocomplete({
		source: "<?php echo site_url('ventas/pos/buscasinv'); ?>",
		minLength: 2,
		select: function( event, ui ) {
			
			//log( ui.item ?
			//	"Selected: " + ui.item.value + " aka " + ui.item.id :
			//	"Nothing selected, input was " + this.value );
		}
	});
})
</script>

<?php echo form_open('email/send'); ?>

<table class='ui-widget ui-widget-content:'>
<tr>
	<td>
		<input type='text' name='barras' id='barras' size=30 class='ui-button ui-widget ui-state-focus ui-corner-all ui-button-text-only' value='Introduzca un código de producto'>
	</td>
	<td rowspan=2>
		<table class=" ui-widget-content ui-corner-all" >
			<tr class=" ui-widget-content ui-widget-header ui-corner-top">
				<th>Tipo</th>
				<th>Descripci&oacute;n</th>
				<th>Monto</th>
			</tr>
			<tr>
				<td>EF</td>
				<td>Efectivo</td>
				<td><input type='text' name='tarjeta0' id='tarjeta0' size=5 class='ui-widget-content ui-corner-all' value='434.00'></td>
			</tr>
		</table>
		
		
	</td>
</tr>
<tr>
	<td>

	<div  class=" ui-widget-content ui-corner-all" >
		<div class="ui-widget-header ui-corner-top">
			Lista de productos
		</div>
		<div  class="ui-widget-content" id="dialog">
			<p>
				<table class=" ui-widget-content ui-corner-all" >
					<tr class=" ui-widget-content ui-widget-header ui-corner-top">
						<th>C&oacute;digo</th>
						<th>Descripci&oacute;n</th>
						<th>Cantidad</th>
						<th>Precio</th>
						<th>Importe</th>
					</tr>
					<tr>
						<td>asdf</td>
						<td>fds</td>
						<td><input type='text' name='cana0' id='cana0' size=5 class='ui-widget-content ui-corner-all' value='1'></td>
						<td><input type='text' name='precio0' id='precio0' size=7 class='ui-widget-content ui-corner-all' value='434'></td>
						<td>434</td>
					</tr>
				</table>
			</p>
		</div>
	</div>

	</td>
</tr>
</table>

<?php echo form_close(); ?>