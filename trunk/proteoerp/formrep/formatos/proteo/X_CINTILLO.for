<!-- CINTILLO -->
<div id="section_header">
	<table style="width: 100%;" >
		<tr>
			<td width="130" rowspan="3"><img src="<?php echo $this->_direccion.'/images/logo.jpg'; ?>" width="127" alt="Logo"></td>
			<td><span style="text-align:left;font-size:1.4em;font-style:italic;font-weight: bold;"><?php echo trim($this->datasis->traevalor("TITULO1")) ?></span></td>
		</tr><tr>
			<td style="font-size: 8pt"><b>RIF: <?php echo $this->datasis->traevalor("RIF") ?></b></td>
		</tr><tr>
			<td><div style="font-size: 8pt"> <?php echo trim($this->datasis->traevalor("TITULO2")).' '.trim($this->datasis->traevalor("TITULO3")) ?></div></td>
		</tr>
	</table>
</div>
<!-- FIN CINTILLO -->
