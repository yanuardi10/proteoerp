<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>ProteoERP ttt <?php if(isset($title)) echo ':'.preg_replace('/<[^>]*>/', '', $title); ?></title>
<?php if (isset($head))   echo $head;   ?>


<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/normal.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>js/ext-4.0.2a/resources/css/ext-all.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>js/ext-4.0.2a/ux/css/CheckHeader.css"/>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>js/ext-4.0.2a/ux/grid/css/GridFilters.css"/> 
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>js/ext-4.0.2a/ux/grid/css/RangeMenu.css" /> 
    
<script type="text/javascript" src="<?php echo base_url(); ?>js/ext-4.0.2a/bootstrap.js"></script>

<?php if (isset($script)) echo $script; ?>

<style type="text/css">
    #divgrid {
        background: #e9e9e9;
        border: 1px solid #d3d3d3;
        margin: 20px;
        padding: 20px;
    }

    .employee-add {background-image: url('<?php echo base_url(); ?>assets/icons/fam/add.gif') !important; }
    .employee-remove {background-image: url('<?php echo base_url(); ?>assets/icons/fam/delete.gif') !important; }
    .icon-user { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user.png') !important;}
		.icon-user-add { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user_add.gif') !important;}
		.icon-save { background-image: url('<?php echo base_url(); ?>assets/icons/fam/save.gif') !important;}
		.icon-reset { background-image: url('<?php echo base_url(); ?>assets/icons/fam/stop.png') !important;}
		.icon-grid { background-image: url('<?php echo base_url(); ?>assets/icons/fam/grid.png') !important;}
		.icon-add { background-image: url('<?php echo base_url(); ?>assets/icons/fam/add.png') !important;}
		.icon-delete { background-image: url('<?php echo base_url(); ?>assets/icons/fam/delete.png') !important;}
		.icon-update { background-image: url('<?php echo base_url(); ?>assets/icons/fam/user_gray.png') !important;}
</style>

</head>
<body>
    <div id="divgrid"></div>
</body>

</html>
