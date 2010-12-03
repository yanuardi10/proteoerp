<p>
<table align='center' width='100%' border=0>
	<tr>
		<td width='100%'>
			<table width='100%' bgcolor='#FAFFFB'>
				<tr><td align='center'><b style="font-size:24pt"><?=$descrip ?></b></td></tr>
				<?php if(isset($corta)){ ?>
				<tr><td align='center'><b style="font-size:16pt;"><?=$corta ?> </b></td></tr>
				<?php }?>
			</table>
		</td>
	</tr>

	<tr>
		<td width='100%' >
			<table width='100%' border='0'>
				<tr><td align='center'><b STYLE="font-size:24pt;color:blue">Precio al p&uacute;blico: <?=$precio1 ?></b> <b><?=$moneda  ?></b></td></tr>
				<?php $d=(isset($descufijo)? $descufijo:0);if($d>0){ $descufijo=$d; ?>
				<tr><td align='center'><b STYLE="font-size:24pt;color:red">Descuento <?php echo $descufijo; ?>%:</b> &nbsp;<b STYLE="font-size:30pt;color:red"><?php echo $pdescu; ?></b><b STYLE="font-size:16pt;color:red"> <?php echo $moneda;?></b></td></tr>
				<tr><td align='center'><b STYLE="font-size:8pt;color:red"> <?php echo $descurazon;?></td></tr>
				<?php }else{ ?>
				<tr><td align='center'><b STYLE="font-size:32pt;color:red">Precio de venta : <?php echo $precio2; ?></b><b><?php echo $moneda;  ?></b></td></tr>
				<?php } ?>
				<?php if(isset($dvolum1)){ ?>
				<tr><td align='right' colspan=2 ><b STYLE="font-size:20pt;color:green">+ de <?=$dvolum1 ?> unidades: <?=$precio3 ?></b> <b><?=$moneda  ?></b></td></tr>
				<tr><td align='right' colspan=2 ><b STYLE="font-size:20pt;color:green">+ de <?=$dvolum2 ?> unidades: <?=$precio4 ?></b> <b><?=$moneda  ?></b></td></tr>
				<?php } ?>
			</table>
		</td>
	</tr>

	<tr>
		<td width='100%'>
			<table width='100%' border=0>
				<tr style="font-size:14pt;">
					<td><b>C&oacute;digo:</b> <?=$codigo ?> </td>
					<td style="font-size:14pt;" rowspan=1 ><b>Marca:</b> <?=$marca ?></td>
				</tr>
			</table>
		</td>

	<tr>
		<td>
			<table width='100%' border=0>
				<tr>
					<td style="font-size:14pt;"><b>C&oacute;digo Barras:</b> <?=$barras ?></td>
					<td style="font-size:14pt;"><b>Referencia:</b> <?=$referen ?></td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td style="font-size:18pt;"><b>Existencia:</b> <?=$existen ?></td>
	</tr>
<table>
</p>