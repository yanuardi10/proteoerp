<?php //datagrid para SuperTables 

//var_dump($headers);
//var_dump($columnas);
//var_dump($rows);

$data1="
<script type=\"text/javascript\" >  
var data1 = [
";

$rowcount = 0;
$retArray = array();
foreach ($rows as $row):
	$rowcount++;
	$data1 .= "{";
	$registro = array();
	foreach ($row as $cell):
		$data1 .= $cell['link'].':"'.trim($cell["field"]).'",';
		$registro[] = $cell["field"];
	endforeach;
	$retArray[] = $registro;
	$data1 = substr($data1,0,-1);
	$data1 .= "},\n";
endforeach;
$data1 = substr($data1,0,-2);
$data1 .= "\n];\n</script>\n";

if ($rowcount > 0) {
	$data = json_encode($retArray);
	$ret = "{data:" . $data .",\n";
	$ret .= "recordType : 'array'}";
} else {
	$ret = '{data : []}';
}


$dsOption = "
var dsOption= {
	fields :[
";

//echo $data1;

echo $ret;





/*
<center>
<div id="grid1_container" style="width:700px;height:300px;"></div>
</center>
<table style="margin:0;width:100%;border-collapse:collapse;padding:0;">
	<tr>
		<td align='center'>
			<div class="mainbackground">
				<div class="pagenav"><?php echo $pager;?></div></div>
				<div class="mainfooter"><?php echo "Cantidad de Registros ".nformat($total_rows,0) ?>
					<div>
						<div style="float:left"><?php echo $container_bl?></div>
						<div style="float:right"><?php echo $container_br?></div>
					</div>
				<div style="clear:both;"></div>
			</div>
		</td>
	</tr>
</table>
*/
?>