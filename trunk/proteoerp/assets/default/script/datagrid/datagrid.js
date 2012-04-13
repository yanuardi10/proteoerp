/* 
 * CRUD datagrid
 * @author Victor Manuel Agudelo
 * @since 13-Aug-2010
 * @version 1.1
 */

function dtgLoadButton()
{

    //$(".ui-icon-extlink").button({icons: {primary: "dtgExcelIcon"}});
    // Dialog
	$('#dtg_dialog').dialog({
		autoOpen: false,
		width: 250,
		buttons: {
			
			"Cancel": function() {
				$(this).dialog("close");
			}
		}
	});


}

/**
 * open exportation options
 */
function dtgOpenExportdata()
{
    $('#dtg_dialog').dialog('open');
    return false;

}

/**
 * Export data
 * @param String format export format
 * @param Bool export like querystring or not
 * @return void
 */
function dtgExport(format,querystring)
{

   var url = document.URL;
   if(querystring == 'true'){
      url += '&_exportto=' + format;
   }else{
      url += '/_exportto/' + format;
   }

   window.open(url,'_Blank');
}


