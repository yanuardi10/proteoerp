<?php //datagrid para SuperTables  ?>
<table style="margin:0;width:100%;border-collapse:collapse;padding:0;">
	<tr>
		<td align='center'>
			<table style="margin:0;width:100%;border-collapse:collapse;padding:0;">
				<tr>
					<td class="mainheader"><?php echo $title?></td>
					<td class="mainheader" align="right"><?php echo $container_tr;?></td>
				</tr>
			</table>
			<div class="mainbackground" style="padding:2px;clear:both;">

			<div class="fakeContainer">
			<table id="demoTable" class="demoTable">
				<tr>
<?php foreach ($headers as $column)://table-header?>
<?php if (in_array($column["type"], array("orderby","detail"))):?>
					<td class="tableheader10">
						<div>
						<?php echo $column["label"]?>
						<a href="<?php echo $column["orderby_asc_url"]?>"><img src="<?php echo $this->rapyd->get_elements_path('orderbyasc.gif')?>" border="0"></a>
						<a href="<?php echo $column["orderby_desc_url"]?>"><img src="<?php echo $this->rapyd->get_elements_path('orderbydesc.gif')?>" border="0"></a>
						</div>
					</td>
<?php elseif ($column["type"] == "clean"):?>
					<td <?php echo $column["attributes"]?>><?php echo $column["label"]?></td>
<?php elseif (in_array($column["type"], array("normal"))):?>
					<td class="tableheader10" <?php echo $column["attributes"]?>><?php echo $column["label"]?></td>
<?php endif;?>
<?php endforeach;//table-header?>
				</tr>
<?php if (count($rows)>0)://table-rows?>
<?php $rowcount=0;?>
<?php foreach ($rows as $row):?>
<?php $rowcount++;?>
				<tr <?php if($rowcount % 2){ echo 'class="odd"';}else{ echo 'class="even"';} ?>>
<?php foreach ($row as $cell):?>
<?php if ($cell["type"] == "detail"):?>
					<td <?php echo $cell["attributes"]?> class="littletablerow10" ><a href="<?php echo $cell["link"]?>"><?php echo $cell["field"]?><img src="<?php echo $this->rapyd->get_elements_path('elenco.gif')?>" width="16" height="16" border="0" align="absmiddle" /></a></td>
<?php elseif ($cell["type"] == "clean"):?>
					<td <?php echo $cell["attributes"]?>><?php echo $cell["field"]?></td>
<?php else:?>
					<td <?php echo $cell["attributes"]?> class="littletablerow10"><?php echo $cell["field"]?>&nbsp;</td>
<?php endif;?>
<?php endforeach;?>
				</tr>
<?php endforeach;?>
<?php endif;//table-rows?>
			</table>
			</div>
			</div>
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