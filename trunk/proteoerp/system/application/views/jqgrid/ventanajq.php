<?php
if (isset($content)) {
if (!empty($content)) {
 
?>
<div id='contenido'>
	<table width="100%" border="0" align="center">
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
<?php if (isset($extras)) echo $extras; ?>
<?php }} ?>