<script type="text/javascript">
var idtot=0;
var idsfpa=1;
$(document).ready(function() {
	$('#pfacsubmit').submit(function() { return false; });
	$("mysubmit").button();
	$('#barras').focus();
	$('#barras').focus(function() { $(this).val(''); });
	$('#barras').focusout(function() {
		$(this).val('Introduzca un código de producto');
	});

	function sinv_data(req){
		campo=$('input[name^="codigoa_"]').first();
		scli =$('#cod_cli').val();
		if(campo.length==0){
			return jQuery.param({ q: req.term , tipo_doc: $('input:radio[name=tipo_doc]:checked').val(),cod_cli: scli });
		}else{
			return jQuery.param({ q: req.term , tipo_doc: $('input:radio[name=tipo_doc]:checked').val(),'codigo' : campo.val(), cod_cli: scli });
		}
	}

	$('#barras').autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ventas/pfac/buscasinv'); ?>",
				type: "POST",
				dataType: "json",
				data: sinv_data(req),
				success:
					function(data){
						var cana=0;
						var sugiere = [];
						$.each(data,
							function(i, val){
								cana=cana+1;
								sugiere.push( val );
							}
						);
						add(sugiere);
					},
			});
		},
		autoSelectFist: true,
		autoFocus: true,
		delay: 10,
		minLength: 2,
		select: function( event, ui ) {
			id=idtot.toString();
			var crea=true;
			var arr =$('input[name^="codigoa_"]');
			jQuery.each(arr, function() {
				nom=this.name;
				pos=this.name.lastIndexOf('_');
				if(pos>0){
					if(ui.item.codigo==this.value){
						ind = this.name.substring(pos+1);
						cc  = Number($('#cana_'+ind).val());
						$('#cana_'+ind).val(cc+Number(ui.item.cana));
						crea=false;
						cimporte(Number(ind));
						totaliza();
					}
				}
			});

			if(crea){
				precio = Number(ui.item.precio);
				importe= roundNumber(precio*Number(ui.item.cana),2);
				html = "<tr id='sitems_"+id+"'>";
				html+= "<td><span title='Eliminar' id='sitems_del_"+id+"' class='ui-icon ui-icon-circle-close' onclick='eliminasitems(\""+id+"\")'></span></td>";
				html+= "<td><input type='hidden' name='codigoa_"+id+"' id='codigo_"+id+"' value='"+ui.item.codigo+"'><span style='font-size:10px'>"+ui.item.codigo+"</span></td>";
				html+= "<td align='right'><input type='text' style='text-align: right;' onkeyup='cimporte(\""+id+"\")' name='cana_"+id+"' id='cana_"+id+"' size=6 class='ui-widget-content ui-corner-all' value='"+ui.item.cana+"' autocomplete='off'></td>";
				html+= "<td align='right'><input type='text' style='text-align: right;' name='precio_"+id+"' id='precio_"+id+"' size=8 class='ui-widget-content ui-corner-all' value='"+ui.item.precio+"' autocomplete='off' ><input type='hidden' name='itiva_"+id+"' id='itiva_"+id+"' value='"+ui.item.iva+"'></td>";
				html+= "<td align='right'><div id='vimporte_"+id+"'>"+importe.toString()+"</div><input type='hidden' name='importe_"+id+"' id='importe_"+id+"' value='"+importe.toString()+"'></td>";

				html+= "</tr>";
				html+= "<tr id='ssitems_"+id+"'>";
				html+= "<td colspan='5' style='font-size:12px'><input type='hidden' name='desca_"+id+"' id='desca_"+id+"' value='"+ui.item.descrip+"'>"+ui.item.descrip+"</td>";
				html+= "</tr>";
				$("#_itemul").after(html);
				$("#precio_"+id).numeric(".");
				$("#cana_"+id).numeric(".");
				//$('#sitems_del_'+id).hover(
				//	function() { $(this).addClass('ui-state-hover');    }, 
				//	function() { $(this).removeClass('ui-state-hover'); }
				//);				
				//$("#cana_"+id).focus();
				totaliza();
				idtot=idtot+1;
			}
		},
		close: function(event, ui) { $('#barras').val(''); }
	}).keydown( function( event ) {
		var isOpen = $(this).autocomplete( "widget" ).is( ":visible" );
		var keyCode = $.ui.keyCode;

		if ( !isOpen && $(this).val()=='' && ( event.keyCode == 107 || event.keyCode == 61)){
			
			//alert(event.keyCode);
			$(this).autocomplete( "disable" );
			event.stopImmediatePropagation();
		}
		if($(this).autocomplete( "option", "disabled")==true && event.keyCode==keyCode.ENTER){
			$(this).autocomplete( "enable" );
			$(this).autocomplete( "search")
		}
	});

	$('#rifci').autocomplete({
		source: function( req, add){
			$.ajax({
				url:  "<?php echo site_url('ventas/pos/buscascli'); ?>",
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
			$('#nombre').val(ui.item.nombre);
			$('#rifci').val(ui.item.rifci);
			$('#cod_cli').val(ui.item.cod_cli);
			$('#sclitipo').val(ui.item.tipo);
		}
	});

	$( "#dialog-scli" ).dialog({
		autoOpen: false,
		height: 300,
		width : 350,
		modal : true,
		buttons: {
			"Crear cliente": function() {
				var bValid = true;
				$("#sclirifci").removeClass( "ui-state-error");
				$("#sclinombre").removeClass("ui-state-error");

				bValid = bValid && checkLength( $("#sclirifci") ,"Rif o cedula" ,2, 9 );
				bValid = bValid && checkLength( $("#sclinombre"),"Nombre",6, 80);
				bValid = bValid && checkRegexp( $("#sclirifci") , /((^[VEJG][0-9]+$)|(^[P][A-Z0-9]+$))/i, "Este campo debe tener el siguiente formato V=Venezolano(a), E=Extranjero(a), G=Gobierno, P=Pasaporte o J=Juridico Como primer caracter seguido del numero de documento. Ej: V123456, J5555555, P56H454" );

				if ( bValid ) {
					$.ajax({
						type: "POST",
						url: "<?php echo site_url('ventas/scli/creascli/insert'); ?>",
						data: $('#sclisubmit').serialize(),
						success: function(msg){
							alert(msg);
							if(msg=='Cliente Guardado'){
								$('#rifci').val($("#sclirifci").val());
								$('#nombre').val($("#sclinombre").val());
								$( "#dialog-scli" ).dialog( "close" );
							}
						}
					});	
				}
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			$("#sclirifci").removeClass( "ui-state-error" );
			$("#sclinombre").removeClass( "ui-state-error" );
		}
	});

	$( "#create-scli" ).click(function() {
		$( "#dialog-scli" ).dialog( "open" );
	});
});


function updateTips( t ) {
	$( ".validateTips" )
		.text( t )
		.addClass( "ui-state-highlight" );
	setTimeout(function() {
		$( ".validateTips" ).removeClass( "ui-state-highlight", 1500 );
	}, 500 );
}

function enviasubmit(){
	$('#todo').fadeTo('slow', 0.5, function() {
      // Animation complete.
    });
	$("mysubmit").attr("disabled","disabled");
	$.ajax({
		type: "POST",
		url: "<?php echo site_url('ventas/pfac/creapfac/insert'); ?>",
		data: $('#pfacsubmit').serialize(),
		success: function(msg){
			$('#todo').fadeTo('slow', 1.0);
			$("mysubmit").removeAttr("disabled");
			
			alert(msg);

			if(msg=='Pedido Guardado'){
				window.location.reload();
			}
		}
	});
}

function checkLength( o, n, min, max ) {
	if ( o.val().length > max || o.val().length < min ) {
		o.addClass( "ui-state-error" );
		updateTips( "La longitud " + n + " debe estar entre " +
			min + " y " + max + "." );
		return false;
	} else {
		return true;
	}
}

function checkRegexp( o, regexp, n ) {
	if ( !( regexp.test( o.val() ) ) ) {
		o.addClass( "ui-state-error" );
		updateTips( n );
		return false;
	} else {
		return true;
	}
}

function eliminasitems(id){
	$('#sitems_'+id).remove();
	$('#ssitems_'+id).remove();
}

function cimporte(id){
	var precio =Number($('#precio_'+id).val());
	var cana   =Number($('#cana_'+id).val());
	var importe=precio*cana;

	if(cana==0){
		eliminasitems(id);
	}else{
		$('#importe_'+id).val(roundNumber(importe,2));
		$('#vimporte_'+id).text(roundNumber(importe,2).toString());
	}

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
	$('#ftotal').val(totals);
}

</script>
<style>
		label, input { display:block; }
		input.text { margin-bottom:12px; width:95%; padding: .4em; }
		fieldset { padding:0; border:0; margin-top:25px; }
</style>

<?php
$attributes = array('id' => 'pfacsubmit');
echo form_open('',$attributes);
?>
		<!--<div class="ui-widget" style=''s>
			<div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;"> 
					<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span>
					
					</p>
			</div>
		</div>-->
<div id='todo' style='text-align:center; width:100%'>
<table class='ui-widget ui-widget-content:' style='margin-left:auto; margin-right:auto;'>
<tr>
	<td>
		<input type='text' name='barras' id='barras' size=30 class='ui-button ui-widget ui-state-focus ui-corner-all ui-button-text-only' autocomplete='off'>

	</td>
</tr>
<tr>
	<td>
	<div class=" ui-widget-content ui-corner-all" >
		<div class="ui-widget-header ui-corner-top" style='text-align:center;'>
			Pedido de clientes
		</div>

		<div  class="ui-widget-content" id="dialog">
			<p>
				<table class=" ui-widget-content ui-corner-all" width='100%'>
					<tr class=" ui-widget-content ui-widget-header ui-corner-top" id='_itemul'>
						<th colspan='2'>C&oacute;digo</th>
						<th>Cantidad</th>
						<th>Precio</th>
						<th>Importe</th>
					</tr>
					<tr>
						<td colspan='3'></td>
						<td align='right' >Total:</td>
						<td align='right' ><b id='total'>0.00</b><input type='hidden' name='ftotal' id='ftotal' value=0></td>
					</tr>
				</table>
			</p>
			<p>
			<table class=" ui-widget-content ui-corner-all" width='100%' >
				<tr class=" ui-widget-content ui-widget-header ui-corner-top">
					<th colspan='3'>Cliente</th>
				</tr>
				<tr>
					<td>Rif o C&eacute;dula</td>
					<td>
						<input type='text' name='rifci' id='rifci' size=20 class='ui-widget-content ui-corner-all' autocomplete='off'>
						<input type='hidden' name='cod_cli' id='cod_cli'>
						<input type='hidden' name='sclitipo' id='sclitipo'>
					</td>
					<td>
						<span id="create-scli" title='Agregar clientes' class="ui-icon ui-icon-plusthick"></span>
					</td>
				</tr>
				<tr>
					<td colspan='3'><input type='text' name='nombre' id='nombre' size=40 class='ui-widget-content ui-corner-all' autocomplete='off'></td>
				</tr>
			</table>
			</p>
			<center>
			<input type='button' name='mysubmit' value="guardar" onclick='enviasubmit()' class='ui-widget-content ui-corner-all'>
			</center>
		</div>
	</div>
	</td>
</tr>
</table>


<?php echo form_close(); ?>
<div id="dialog-scli" title="Crear nuevo cliente">
	<p class="validateTips"></p>
	<?php
	$attr=array('id'=>'sclisubmit');
	echo form_open('ventas/scli/dataedit/insert',$attr);
	?>
	<fieldset>
		<label for="sclirifci">Rif/CI*</label>
		<input type="text" name="rifci" id="sclirifci"   class="text ui-widget-content ui-corner-all" autocomplete='off' />
		<label for="sclinombre">Nombre* </label>
		<input type="text" name="nombre" id="sclinombre" class="text ui-widget-content ui-corner-all" autocomplete='off' />
		<label for="sclidire11">Direcci&oacute;n*</label>
		<input type="text" name="dire11" id="sclidire11"  class="text ui-widget-content ui-corner-all" autocomplete='off' />
		<label for="zona">Zona*</label>
		<?php
		$mSQL='SELECT codigo, CONCAT(codigo," ", nombre) nombre FROM zona ORDER BY nombre';
		$query = $this->db->query($mSQL);

		$options=array();
		foreach ($query->result() as $row){
			$options[$row->codigo]=$row->nombre;
		}
		$atts= array( 'class','text ui-widget-content ui-corner-all');
		echo form_dropdown('zona', $options,'','class=\'text ui-widget-content ui-corner-all\'');
		?>

		<label for="grupo">Grupo*</label>
		<?php
		$mSQL='SELECT grupo, CONCAT(grupo," ",gr_desc) gr_desc FROM grcl ORDER BY gr_desc';
		$query = $this->db->query($mSQL);

		$options=array();
		foreach ($query->result() as $row){
			$options[$row->grupo]=$row->gr_desc;
		}
		$atts= array( 'class','text ui-widget-content ui-corner-all');
		echo form_dropdown('grupo', $options,'','class=\'text ui-widget-content ui-corner-all\'');
		?>
	</fieldset>
	<?php echo form_close(); ?>
</div>

</div>