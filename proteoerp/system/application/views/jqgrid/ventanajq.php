<div id='contenido'>
	<table width="100%" border=0 align="center">
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
<div class="footer">
	<p>Tiempo de la consulta {elapsed_time} seg | Proteo ERP </p>
</div>
<?php if (isset($extras)) echo $extras; ?>
