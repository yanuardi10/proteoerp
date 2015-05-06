<?php
if(count($parametros)==0) show_error('Faltan parametros ');
$id=$parametros[0];
$dbid=$this->db->escape($id);
$dbid=$id;
$mSQL_1 = $this->db->query("SELECT LPAD(id,8,'0') numero, nrocomp, emision, periodo, fecha, cod_cli, nombre, rif, exento, tasa, general, geneimpu, tasaadic, adicional, adicimpu, tasaredu, reducida, reduimpu, stotal, impuesto, gtotal, reiva, estampa, hora, usuario, modificado, transac, sprmreinte, operacion,codbanc FROM rivc WHERE id=${dbid}");
if($mSQL_1->num_rows()==0) show_error('Registro no encontrado');
$mSQL_2 = $this->db->query("SELECT tipo_doc,fecha,numero,nfiscal,exento,tasa,general,geneimpu,tasaadic,adicional,adicimpu,tasaredu,reducida,reduimpu,stotal,impuesto,gtotal,reiva,transac,estampa,hora,usuario,ffactura,modificado FROM itrivc WHERE idrivc=${dbid}");
if($mSQL_2->num_rows()==0) show_error('Retencion sin facturas afectadas');

$row = $mSQL_1->row();
$fecha      = dbdate_to_human($row->fecha);
$emision    = dbdate_to_human($row->emision);
$nrocomp    = $row->nrocomp;
$nombre     = $this->us_ascii2html($row->nombre);
$cod_cli    = $this->us_ascii2html($row->cod_cli);
$codbanc    = $this->us_ascii2html($row->codbanc);
$rif        = $this->us_ascii2html($row->rif);
$periodo    = $this->us_ascii2html($row->periodo);
$stotal     = floatval($row->stotal);
$gtotal     = floatval($row->gtotal);
$reiva      = floatval($row->reiva);
$transac    = $row->transac;
$operacion  = strtoupper(trim($row->operacion));
$numero     = $row->numero;

$dbtransac  = $this->db->escape($transac);
$dbcodbanc  = $this->db->escape($codbanc);
$dbcod_cli  = $this->db->escape($cod_cli);

$negreso = $sbmov = $egreso = $codbanc = $usuario = $anticnum = $sprmreinte ='';
$monto=$anticipo=$reintemon= 0;
if($operacion=='R'){

	$mSQL="SELECT b.numero, b.monto, CONCAT('(',b.codbanc,') ', TRIM(d.banco),' ', d.numcuent) AS codbanc, b.usuario, c.us_nombre, b.negreso
	FROM bmov    AS b
	JOIN banc    AS d ON b.codbanc=d.codbanc
	LEFT JOIN usuario AS c ON b.usuario=c.us_codigo
	WHERE b.codbanc=${dbcodbanc} AND b.transac=${dbtransac}";

	$bmov=$this->datasis->damereg($mSQL);

	if(count($bmov) > 0){
		$negreso=$bmov['negreso'];
		if(!empty($bmov['numero'])){
			$sbmov   = 'Egreso numero '.$bmov['numero'].' de la Caja '.$bmov['codbanc'].' por Bs. '.nformat($bmov['monto']);
			$egreso  = $bmov['numero'];
			$codbanc = $bmov['codbanc'];
			$monto   = floatval($bmov['monto']);
			if(empty($bmov['us_nombre'])){
				$usuario = $bmov['usuario'];
			}else{
				$usuario = '('.$bmov['usuario'].') '.$bmov['us_nombre'];
			}
		}
	}

}elseif($operacion=='A'){
	$mSQL="SELECT SUM(monto*IF(tipo_doc IN ('AN','NC'),1,-1)) AS anticipo,GROUP_CONCAT(numero) AS numero
	FROM smov
	WHERE transac=${dbtransac} AND tipo_doc IN ('AN','ND') AND cod_cli=${dbcod_cli}";
	$mSQL_3 = $this->db->query($mSQL);
	$rrow = $mSQL_3->row();
	$anticipo = floatval($rrow->anticipo);
	$anticnum = $rrow->numero;
}elseif($operacion=='P'){
	$sprmreinte   = trim($row->sprmreinte);
	$dbsprmreinte = $this->db->escape($sprmreinte);

	$mSQL="SELECT monto
	FROM sprm
	WHERE transac=${dbtransac} AND tipo_doc='ND' AND numero=${dbsprmreinte}";
	$mSQL_3 = $this->db->query($mSQL);
	$rrow = $mSQL_3->row();
	$reintemon = floatval($rrow->monto);
}

$detalle =$mSQL_2->result();
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?php echo $this->config->item('charset'); ?>" >
<title>Comprobante <?php echo $nrocomp ?></title>
<link rel="stylesheet" href="<?php echo $this->_direccion ?>/assets/default/css/formatos.css" type="text/css" >
</head>
<body>
<script type="text/php">
if ( isset($pdf) ) {

	$font = Font_Metrics::get_font('verdana');
	$size = 6;
	$color = array(0,0,0);
	$text_height = Font_Metrics::get_font_height($font, $size);

	$foot = $pdf->open_object();

	$w = $pdf->get_width();
	$h = $pdf->get_height();

	// Draw a line along the bottom
	$y = $h - $text_height - 24;
	$pdf->line(16, $y, $w - 16, $y, $color, 0.5);

	$pdf->close_object();
	$pdf->add_object($foot, 'all');

	$text = "PP {PAGE_NUM} de {PAGE_COUNT}";

	// Center the text
	$width = Font_Metrics::get_text_width("PP 1 de 2", $font, $size);
	$pdf->page_text($w / 2 - $width / 2, $y, $text, $font, $size, $color);
}
</script>
<div id="body">
<table style="width: 100%;">
	<thead>
	<tr>
		<td><?php $this->incluir('X_CINTILLO'); ?></td>
	</tr>
	<tr>
		<td><div id="section_header">
			<div class="page" style="font-size: 7pt">
			<table style="width: 100%;" class="header">
			<tr>
				<td valign='bottom'><h1 style="text-align: left" >Consignaci&oacute;n de Retenci&oacute;n de IVA</h1></td>
				<td valign='bottom'><h1 style="text-align: right">N&uacute;mero: <?php echo $numero ?></h1></td>
			</tr>
			</table>
			</div>
		</div></td>
	</tr>
	<tr>
		<td style="text-align:center;">Hemos recibido del Agente de Retenci&oacute;n de IVA el documento especificado a continuaci&oacute;n</td>
	</tr>
	<tr>
		<td>
			<table style="width: 100%; font-size: 10pt;" border='0'>
			<tr>
				<td width='60'>Comprobante: </td><td width='60' ><b><?php echo $periodo.$nrocomp; ?></b></td>
				<td width='50'>Agente: </td><td><b><?php echo htmlspecialchars($nombre); ?></b></td>
				<td width='90'>Fecha de Recepci&oacute;n: </td><td width='60'><b><?php echo $fecha; ?></b></td>
			</tr>
			<tr>
				<td>RIF: </td><td><b><?php echo $rif; ?></b></td>
				<td></td><td></td>
				<td>Fecha de Emisi&oacute;n: </td><td><b><?php echo $emision; ?></b></td>
			</tr>
			</table>
		</td>
	</tr>
	</thead>
	<tr>
		<td><div id="content">
		<div class="page" style="font-size: 10pt">
		<table class="change_order_items">
		<thead>
		<tr>
			<th style='border-style:solid;border-width:1px;'>N&uacute;mero</th>
			<th style='border-style:solid;border-width:1px;'>Fecha</th>
			<th style='border-style:solid;border-width:1px;'>Monto</th>
			<th style='border-style:solid;border-width:1px;'>Impuesto</th>
			<th style='border-style:solid;border-width:1px;'>Retenido</th>
		</tr>
		</thead>
		<tbody>
			<?php $treiva=0;  $mod=false; $i=0; foreach ($detalle as $items){ $i++;?>
			<?php $treiva+=floatval($items->reiva); ?>
		<tr class="<?php if(!$mod) echo 'even_row'; else  echo 'odd_row'; ?>">
			<td style="text-align: center"><?php echo $items->tipo_doc.$items->numero ?></td>
			<td style="text-align: center"><?php echo dbdate_to_human($items->fecha); ?></td>
			<td style="text-align: right" ><?php echo nformat($items->gtotal);        ?></td>
			<td style="text-align: right" ><?php echo nformat($items->impuesto);      ?></td>
			<td style="text-align: right" ><?php echo nformat($items->reiva);         ?></td>
		</tr>
			<?php $mod = ! $mod; } ?>
		</tbody>
		<tfoot>
		<tr>
			<td colspan="4" style='font-size:14pt;border-style:solid; border-width:1px;text-align: right;'><b>TOTAL MONTO RETENIDO:</b></td>
			<td class="change_order_total_col" style="font-size:14pt;border-style:solid;"><b><?php echo nformat($reiva) ?></b></td>
		</tr>
		<tr>
			<td colspan="5" style='font-size:10pt;border-style:solid; border-width:0.5px;text-align: center;'><b>
			<?php if($anticipo>0){ ?>
				SALDO REINTEGRABLE O APLICABLE A OTRA COMPRA: <?php echo nformat($anticipo) ?>
			<?php }else{ echo '&nbsp;'; } ?>
			</b></td>
		</tr>
		</tfoot>
		</table>
		</div>
		</div></td>
	</tr>
	</table>

<?php
$tabla = '';
if($monto>0){
	// reintegrado por caja
	$tabla = "
	<table width='100%'>
		<tr>
			<td><h3>SALDO REINTEGRADO AL AGENTE DESDE CAJA</h3></td>
			<td align='right'><h3>EGRESO ${negreso}</h3></td>
		</tr>
	</table>
	<p>Segun el recibo de caja siguiente:</p>
	<table class='change_order_items'>
	<thead>
	<tr>
		<th style='border-style:solid; border-width:1px;' align='center'>D&Eacute;BIDO No.</th>
		<th style='border-style:solid; border-width:1px;' align='center'>CAJA</th>
		<th style='border-style:solid; border-width:1px;' align='center'>MONTO</th>
		<th style='border-style:solid; border-width:1px;' align='center'>USUARIO</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td align='center'>${egreso}</td>
		<td align='center'>${codbanc}</td>
		<td align='center'>Bs.".nformat($monto)."</td>
		<td align='center'>${usuario}</td>
	</tr>
	</tbody>";

	$tabla .="
	</table>
	<p style=\"font-size: 12pt;text-align: center;\">El Agente de Retenci&oacute;n recibe a su entera conformidad el monto reintegrable producto de las retenciones de IVA consignadas.</p>
";
}else{
	$tabla  = '<br><br><p style="font-size: 12pt;text-align: center;">';
	if($reintemon>0){
		$tabla .= 'El monto sobrante de las retenciones consignadas estan por pagar con el Nro. '.$sprmreinte;
	}elseif($anticipo>0){
		$tabla .= 'Con el monto sobrante de las retenciones consignadas se creo el anticipo Nro. '.$anticnum.'.';
	}elseif($monto>0){
		$tabla .= 'El monto sobrante de las retenciones consignadas fueron reintegradas al cliente.';
	}else{
		$tabla .= 'El monto de las retenciones consignadas fueron cruzadas con la deuda cliente.';
	}
	$tabla .= '</p><br><br>';
}

echo $tabla;
?>
	<table width='100%' border="0" cellspacing="0" cellpadding="0" class="header">
	<tr>
		<td width='40%'><b><div align="left" style="font-size:8pt;border-style:solid; border-width:1px;"><br><br>Recibido por:</div></b></td>
		<td width='30%'><b><div align="left" style="font-size:8pt;border-style:solid; border-width:1px;"><br><br>CI:</div></b></td>
		<td width='20%'><b><div align="left" style="font-size:8pt;border-style:solid; border-width:1px;"><br><br>Fecha:</div></b></td>
	</tr>
	</table>

	<br><br>
	<table width='100%' border="0" cellspacing="0" cellpadding="0" class="header">
	<tr>
		<td width='33%'><b><div align="center" style="font-size:8pt;border-style:solid; border-width:1px;"><br><br><br><br>&nbsp;</div></b></td>
		<td width='33%'><b><div align="center" style="font-size:8pt;border-style:solid; border-width:1px;"><br><br><br><br>&nbsp;</div></b></td>
		<td width='33%'><b><div align="center" style="font-size:8pt;border-style:solid; border-width:1px;"><br><br><br><br>&nbsp;</div></b></td>
	</tr><tr>
		<td><b><div align="left" style="font-size:8pt;border-style:solid; border-width:1px;">Elaborado por:</div></b></td>
		<td><b><div align="left" style="font-size:8pt;border-style:solid; border-width:1px;">Autorizado por:</div></b></td>
		<td><b><div align="left" style="font-size:8pt;border-style:solid; border-width:1px;">Aprobado por:</div></b></td>
	</tr>
	</table>
</div>
</body>
</html>
