<script type="text/javascript">
var idtot=0;
$(document).ready(function() {
	$('#barras').focus(function() {
		$(this).val('');
	});

	$('#barras').focusout(function() {
		$(this).val('Introduzca un código de producto');
	});

	$('#barras').autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ventas/pos/buscasinv'); ?>",
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
			id=idtot.toString();
			precio=Number(ui.item.precio);
			html = "<tr>"
			html+= "<td><input type='hidden' name='codigo_"+id+"' id='codigo_"+id+"' value='"+ui.item.codigo+"'>"+ui.item.codigo+"</td>";
			html+= "<td>"+ui.item.descrip+"</td>";
			html+= "<td align='right'><input type='text' style='text-align: right;' onkeyup='cimporte(\""+id+"\")' name='cana_"+id+"' id='cana_"+id+"' size=5 class='ui-widget-content ui-corner-all' value='1' autocomplete='off'></td>";
			html+= "<td align='right'><input type='text' style='text-align: right;' name='precio_"+id+"' id='precio_"+id+"' size=7 class='ui-widget-content ui-corner-all' value='"+ui.item.precio+"' autocomplete='off' ><input type='hidden' name='iva_"+id+"' id='iva_"+id+"' value='"+ui.item.iva+"'></td>";
			html+= "<td align='right'>"+ui.item.precio+"<input type='hidden' name='importe_"+id+"' id='importe_"+id+"' value='"+ui.item.precio+"'></td>";
			html+= "</tr>";
			$("#_itemul").after(html);
			totaliza();
			idtot=idtot+1;
		},
		change: function(event, ui) { $('#barras').val('Introduzca un código de producto'); }
	});
})

function cimporte(id){
	var precio =Number($('#precio_'+id).val());
	var cana   =Number($('#cana_'+id).val());
	var importe=precio*cana;

	$('#importe_'+id).val(importe);
	totaliza();
}

function totaliza(){
	var arr=$('input[name^="importe_"]');
	var totals=0;
	jQuery.each(arr, function() {
		nom=this.name;
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind     = this.name.substring(pos+1);
			//cana    = Number($("#cana_"+ind).val());
			//itiva   = Number($("#itiva_"+ind).val());
			tota    = Number(this.value);

			//iva     = iva+tota*(itiva/100);
			totals  = totals+tota;
		}
	});
	$('#total').text(roundNumber(totals,2).toString());
}
</script>

<?php echo form_open('email/send'); ?>

<table class='ui-widget ui-widget-content:'>
<tr>
	<td>
		<input type='text' name='barras' id='barras' size=30 class='ui-button ui-widget ui-state-focus ui-corner-all ui-button-text-only' autocomplete='off'>
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
				<table class=" ui-widget-content ui-corner-all" width='100%'>
					<tr class=" ui-widget-content ui-widget-header ui-corner-top" id='_itemul'>
						<th>C&oacute;digo</th>
						<th>Descripci&oacute;n</th>
						<th>Cantidad</th>
						<th>Precio</th>
						<th>Importe</th>
					</tr>
					<tr>
						<td colspan=3></td>
						<td align='right' >Total:</td>
						<td align='right' ><b id='total'>0.00</b></td>
					</tr>
				</table>
			</p>
		</div>
	</div>
	</td>
</tr>
</table>
<?php echo form_close(); ?>