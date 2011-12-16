<style type="text/css">
.wrap{
	width:600px;
	position:relative;
	padding:10px;
}
.sourcearea{
	width:200px;
	float:left;
}
.items {
	z-index: 100;
}
.droparea {
	float:right;
	background-color: #EFEFE0;
	border: 1px solid #EFEFE0;
	width: 250px;
	min-height: 200px;
}
.droparea img{
	margin:5px;
}
.dropareahover {
	background-color:#EFD2A4;
	border-color:#DFA853;
}
.summary{
	padding:10px;
}
.summary span{
	font-weight:bold;
}

</style>


<script>
	var itemTotalCtr = 0;
	var pagables     = <?php echo $pjson ?>;

	$(document).ready(function(){
		var ddragable="div[id^='FC'],div[id^='GI'],div[id^='ND']";
		$('#_total').numeric(".");
		$('#_total').floatnumber(".",2);
		
		$(ddragable).draggable({helper: 'original', revert: true });
		
		$("#a_pagar").droppable({
			accept: ddragable,
			hoverClass: 'dropareahover',
			tolerance: 'pointer',
			drop: function(ev, ui) {
				itemTotalCtr =itemTotalCtr +1;
				var dropElemId = ui.draggable.attr("id");
				var dropElem   = ui.draggable.html();

				monto=eval("pagables."+dropElemId);
				nombre=dropElemId.substr(0,dropElemId.length-8);
				
				fnombre='paga['+dropElemId+']';
				html='<div class="ui-widget-header ui-corner-all ui-helper-clearfix" id="pp'+dropElemId+'"><a href="#" onclick="sacar(\''+dropElemId+'\')"><span class="ui-icon ui-icon-closethick" >borrar</span></a>'+nombre+'<input class="ui-state-default ui-corner-all" type="text" name="'+fnombre+'"  id="idd'+dropElemId+'" size=10 style="text-align: right;" value="'+monto.toString()+'"  />'+'</div>';
				
				$("#dform").append(html);
				totalizar();
				$("input[name='"+fnombre+"']").numeric(".");
				$("input[name='"+fnombre+"']").keyup(function(event){
					valor=parseFloat(this.value);
					obj  =this.id.substr(3);
					mmonto=eval("pagables."+obj);
					if (valor>mmonto){
						alert('El maximo valor que puede colocar es '+mmonto.toString());
						this.value=mmonto.toString();
					}
					totalizar();
				});
				$("#"+dropElemId).hide();
			}
		});
	});

	function totalizar(){
		prop=$("input[name^='paga']");
		tota=0;
		jQuery.each(prop, function() {
    	tota=tota+parseFloat(this.value);
    });
    tota=tota.toFixed(2);
		$('#_total').attr('value',tota.toString());
	}
	
	function sacar(id){
		itemTotalCtr =itemTotalCtr -1;
		
		$("#pp"+id).remove();
		$("#"+id).show();
		totalizar();
	}

</script>


<div class="wrap">
	<table>
		<tr><td valign="top" width="300">

			<div class="ui-dialog ui-widget ui-widget-content ui-corner-all">
				<div class="ui-widget-header ui-corner-all ui-helper-clearfix">
					Efectos a pagar:
					<!--<a class="ui-dialog-titlebar-close ui-corner-all" href="#"><span class="ui-icon ui-icon-closethick">close</span></a>-->
				</div>
				<div style="height: 200px; min-height: 109px; width: auto;" class="ui-dialog-content" id="a_pagar">
					<form id='dform' action="<?php echo $link ?>" method="post">
					
					<input type="hidden" name="sprm" value='<?php echo $sprv ?>' />
					</form>
				</div>
				<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
					<table width=100%>
						<tr><td>
							Total: 
						</td><td align='right'>
							<input  class="ui-state-default ui-corner-all" type="text" name="_total"  id="_total" value="0.00"  size=10 style="text-align: right;"/>
						</td></tr>
					</table>
					<button class="ui-state-default ui-corner-all" type="button" onclick="$('#dform').submit()" >Pagar</button>
				</div>
			</div>

		</td><td valign="top">
		
		<?php foreach($pagables AS $p){ ?>
			<div class="ui-widget-content ui-corner-all" style="height: 60px; min-height: 60px; width: 180px;" id="<?php echo $p['tipo_doc'].$p['numero'].$p['fecha']; ?>">
				<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" ><span class="ui-dialog-title"><?php echo $p['tipo_doc'].$p['numero']; ?></span></div>
				<div>
					Monto:<?php echo $p['monto']?><br>Abonado: <?php echo $p['abonos']; ?>
				</div>
			</div><br/>
		<?php  } ?>
	  
	  </td></tr>
	</table>
</div>