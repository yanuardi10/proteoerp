<?php //datagrid para Simple  ?>
			<div class="mainbackground" style="padding:2px;clear:both;">
			<table id="simpletabla" class="simpletabla">
				<tr>
<?php foreach ($headers as $column)://table-header?>
<?php if (in_array($column["type"], array("orderby","detail"))):?>
					<td><?php echo $column["label"]?></td>
<?php elseif ($column["type"] == "clean"):?>
					<td class='simplehead' <?php echo $column["attributes"]?>><?php echo $column["label"]?></td>
<?php elseif (in_array($column["type"], array("normal"))):?>
					<td class='simplehead' <?php echo $column["attributes"]?>><?php echo $column["label"]?></td>
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
					<td class='simplerow' <?php echo $cell["attributes"]?> >
					<a  href="<?php echo $cell["link"]?>"><?php echo $cell["field"] ?>
					<img src="<?php echo $this->rapyd->get_elements_path('elenco.gif')?>" width="16" height="16" border="0" align="absmiddle" /></a>
					</td>
<?php elseif ($cell["type"] == "clean"):?>
					<td class='simplerow' <?php echo $cell["attributes"]?>><?php echo $cell["field"]?></td>
<?php else:?>
					<td class='simplerow' <?php echo $cell["attributes"]?> ><?php echo $cell["field"]?>&nbsp;</td>
<?php endif;?>
<?php endforeach;?>
				</tr>
<?php endforeach;?>
<?php endif;//table-rows?>
			</table>
