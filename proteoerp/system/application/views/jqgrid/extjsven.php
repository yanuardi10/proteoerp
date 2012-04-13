<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>ProteoERP <?php if(isset($title)) echo ': '.preg_replace('/<[^>]*>/', '', $title); ?></title>
<?php if (isset($head))   echo $head;   ?>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/normal.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/themes/redmond/jquery-ui-1.8.18.custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/themes/ui.jqgrid.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/themes/ui.multiselect.css" />

<style>
html, body {
	margin: 0;			/* Remove body margin/padding */
	padding: 0;
	overflow: hidden;	/* Remove scroll bars on browser window */	
    font-size: 75%;
}
/*Splitter style */


#LeftPane {
	/* optional, initial splitbar position */
	overflow: auto;
}
/*
 * Right-side element of the splitter.
*/

#RightPane {
	padding: 2px;
	overflow: auto;
}
.ui-tabs-nav li {position: relative;}
.ui-tabs-selected a span {padding-right: 10px;}
.ui-tabs-close {display: none;position: absolute;top: 3px;right: 0px;z-index: 800;width: 16px;height: 14px;font-size: 10px; font-style: normal;cursor: pointer;}
.ui-tabs-selected .ui-tabs-close {display: block;}
.ui-layout-west .ui-jqgrid tr.jqgrow td { border-bottom: 0px none;}
.ui-datepicker {z-index:1200;}
</style>


<?php
	$encabeza = '<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="80px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">'.$encabeza.'</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';
	if (!isset($dockedItems)){
		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";
	}
?>


<?php echo script('jquery-1.7.1.min.js') ?>
<?php echo script('jquery-ui-1.8.18.custom.min.js') ?>
<?php echo script('jquery.layout.js') ?>
<?php echo script('i18n/grid.locale-sp.js') ?>
<script type="text/javascript">
	$.jgrid.no_legacy_api = true;
	$.jgrid.useJSON = true;
</script>
<?php echo script('jquery.jqGrid.min.js')  ?>
<?php echo script('jquery.tablednd.js')    ?>
<?php echo script('jquery.contextmenu.js') ?>
<?php echo script('ui.multiselect.js')     ?>


<script type="text/javascript">
jQuery(document).ready(function(){
    //$('#switcher').themeswitcher();

	$('body').layout({
		resizerClass: 'ui-state-default',
        west__onresize: function (pane, $Pane) {
            jQuery("#west-grid").jqGrid('setGridWidth',$Pane.innerWidth()-2);
		}
	});
	$.jgrid.defaults = $.extend($.jgrid.defaults,{loadui:"enable"});
	var maintab =jQuery('#tabs','#RightPane').tabs({
        add: function(e, ui) {
            // append close thingy
            $(ui.tab).parents('li:first')
                .append('<span class="ui-tabs-close ui-icon ui-icon-close" title="Close Tab"></span>')
                .find('span.ui-tabs-close')
                .click(function() {
                    maintab.tabs('remove', $('li', maintab).index($(this).parents('li:first')[0]));
                });
            // select just added tab
            maintab.tabs('select', '#' + ui.panel.id);
        }
    });
    jQuery("#west-grid").jqGrid({
        url: "tree.xml",
        datatype: "xml",
        height: "auto",
        pager: false,
        loadui: "disable",
        colNames: ["id","Menu","url"],
        colModel: [
            {name: "id",width:1,hidden:true, key:true},
            {name: "menu", width:150, resizable: false, sortable:false},
            {name: "url",width:1,hidden:true}
        ],
        treeGrid: true,
		caption: "Opciones",
        ExpandColumn: "menu",
        autowidth: true,
        //width: 180,
        rowNum: 200,
        ExpandColClick: true,
        treeIcons: {leaf:'ui-icon-document-b'},
        onSelectRow: function(rowid) {
            var treedata = $("#west-grid").jqGrid('getRowData',rowid);
            if(treedata.isLeaf=="true") {
                //treedata.url
                var st = "#t"+treedata.id;
				if($(st).html() != null ) {
					maintab.tabs('select',st);
				} else {
					maintab.tabs('add',st, treedata.menu);
					$(st,"#tabs").load(treedata.url);
				}
            }
        }
    });
	
// end splitter

<?php if(isset($tabla1))   echo $tabla1;   ?>


});
</script>




<style type="text/css">
#divgrid1 {
	background: #e9e9e9;
	border: 1px solid #d3d3d3;
	margin: 20px;
	padding: 20px;
}

	.icon-user     { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user.png') !important;}
	.icon-user-add { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user_add.gif') !important;}
	.icon-save     { background-image: url('<?php echo base_url(); ?>assets/icons/fam/save.gif') !important;}
	.icon-reset    { background-image: url('<?php echo base_url(); ?>assets/icons/fam/stop.png') !important;}
	.icon-grid     { background-image: url('<?php echo base_url(); ?>assets/icons/fam/grid.png') !important;}
	.icon-add      { background-image: url('<?php echo base_url(); ?>assets/icons/fam/add.png') !important;}
	.icon-delete   { background-image: url('<?php echo base_url(); ?>assets/icons/fam/delete.png') !important;}
	.icon-update   { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user_gray.png') !important;}
	.icon-accept   { background-image: url('<?php echo base_url(); ?>assets/icons/fam/accept.png') !important;}
	.icon-cross    { background-image: url('<?php echo base_url(); ?>assets/icons/fam/cross.gif') !important;}
</style>Items

</head>
<body>
<div class="ui-layout-north" >
<?php echo $encabeza ?>
</div>
	
	
<?php if (isset($content)) echo $content; ?>
</body>

</html>
