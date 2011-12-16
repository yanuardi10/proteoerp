/*
* jQuery UI Autocomplete Auto Select On one item
*
* Copyright 2011 Andres Hocevar
* Licensed under GPL Version 2 licenses.
*
*/
(function( $ ) {
	$.ui.autocomplete.prototype.options.autoSelectOne = true;
	$( ".ui-autocomplete-input" ).live( "autocompleteopen", function( event ) {
		var autocomplete = $( this ).data( "autocomplete" );
		if ( !autocomplete.options.autoSelectOne || autocomplete.selectedItem ) { return; }

		var items=autocomplete.widget().children( ".ui-menu-item" );
		if(items.length==1){
			autocomplete.selectedItem = items.first().data( "item.autocomplete" );
		}

		if (autocomplete.selectedItem){
			autocomplete.menu.element.hide();
			autocomplete.menu.deactivate();
			autocomplete._trigger("select", event, { item: autocomplete.selectedItem } );
			autocomplete._trigger("close" , event);
		}
	});
}( jQuery ));