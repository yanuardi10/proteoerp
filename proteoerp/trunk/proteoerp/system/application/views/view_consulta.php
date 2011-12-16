<html>
<head>
<title>Sistemas DataSIS</title>
<?=style("ventanas.css");?>
<?php if (isset($head))   echo $head;   ?>
<?php if (isset($script)) echo $script; ?>
</head>
<body background='<?=base_url()?>images/fondo1.jpg'>
<div id='contenido'>
<table width="95%"><tr>
    <td><?php echo $logo;?></td>
    <td><?php if (isset($title)) echo $title; ?></td></tr></tr>
	<table width="95%" border=0 align="center">
		<tr>
			<td valign=top><?php if (isset($lista)) echo $lista; ?></td>
			<td><?php if (isset($content)) echo $content; ?></td>
		</tr>
	</table>
</div>
<?php if (isset($extras)) echo $extras; ?>
</body>
</html>