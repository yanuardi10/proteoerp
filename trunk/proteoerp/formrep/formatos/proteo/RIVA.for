<?php if(count($parametros) < 1) show_error('Faltan parametros');
$id = $parametros[0];

$sel=array('a.emision','a.periodo','a.tipo_doc','a.fecha','a.numero','a.nfiscal','a.afecta'
,'a.clipro','a.nombre','a.rif','a.exento','CONCAT_WS(\' \',b.direc1,b.direc2) AS direc'
,'a.tasa'    ,'a.general'  ,'a.geneimpu'
,'a.tasaadic','a.adicional','adicimpu'
,'a.tasaredu','a.reducida' ,'a.reduimpu'
,'a.stotal','a.impuesto','a.gtotal','a.reiva','a.nrocomp');
$this->db->select($sel);
$this->db->from('riva AS a');
$this->db->join('sprv AS b','a.clipro=b.proveed');
$this->db->where('a.id' , $id);
$mSQL_1 = $this->db->get();

if ($mSQL_1->num_rows() == 0){ show_error('Retención no encontrada');}

$row = $mSQL_1->row();

$nrocomp   = $row->nrocomp  ;
$emision   = dbdate_to_human($row->emision);
$periodo   = $row->periodo  ;
$tipo_doc  = $row->tipo_doc ;
$fecha     = dbdate_to_human($row->fecha);
$numero    = $row->numero   ;
$nfiscal   = $row->nfiscal  ;
$afecta    = $row->afecta   ;
$clipro    = $row->clipro   ;
$nombre    = $row->nombre   ;
$rif       = $row->rif      ;
$exento    = $row->exento   ;
$tasa      = $row->tasa     ;
$general   = $row->general  ;
$geneimpu  = $row->geneimpu ;
$tasaadic  = $row->tasaadic ;
$adicional = $row->adicional;
$adicimpu  = $row->adicimpu ;
$tasaredu  = $row->tasaredu ;
$reducida  = $row->reducida ;
$reduimpu  = $row->reduimpu ;
$stotal    = $row->stotal   ;
$impuesto  = $row->impuesto ;
$gtotal    = $row->gtotal   ;
$reiva     = $row->reiva    ;
?><html>
<head>
<title>Comprobante de retenci&oacute;n <?php echo $numero ?></title>
<link rel="STYLESHEET" href="<?php echo $this->_direccion ?>/assets/default/css/formatos.css" type="text/css" />
</head>
<body>
<script type="text/php">
if ( isset($pdf) ) {

	$font = Font_Metrics::get_font("verdana");
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
	$pdf->add_object($foot, "all");

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
				<td>
					<div class="page" style="font-size: 7pt">
						<table style="width: 100%;" class="header">
							<tr>
								<td colspan='2'><h1 style="text-align: center">COMPROBANTE DE RETENCION DEL IMPUESTO AL VALOR AGREGADO</h1></td>
							</tr>
							<tr>
								<td align='center' colspan='2'>(Ley IVA - Art. 11: Seran responsables del pago del Impuesto en calidad de agentes de retenci&oacute;n
								los compradores o adquirientes de determinados bienes muebles y los receptores de
								ciertos servicios, a quienes la administraci&oacute;n designe como tal o CUAL.)</td>
							</tr>
						</table>
						<br />
						<table style="width: 100%;">
							<tr>
								<td colspan='2' style="text-align: right">Comprobante N&uacute;mero: <b><?php echo str_replace('-','',$periodo).$nrocomp ?></b></td>
							</tr>
							<tr>
								<td colspan='2' style="text-align: right">Fecha de Ingreso: <b><?php echo $emision; ?></b></td>
							</tr>
							<tr>
								<td colspan='2' style="text-align: right">Per&iacute;odo F&iacute;scal: <b><?php echo $periodo; ?></b></td>
							</tr>
						</table
						</div>

						<h2 style="text-align: left">Agente de Retenci&oacute;n</h2>
						<div class="page" style="font-size: 7pt">
						<p>
							<?php echo $this->datasis->traevalor('TITULO1'); ?>
						</p>
						<p>
							<?php echo $this->datasis->traevalor('TITULO2').' '.$this->datasis->traevalor('TITULO3'); ?> <br>
							<b>RIF: <?php echo str_replace ('-','',$this->datasis->traevalor('RIF')); ?></b>
						</p>
						</div>

						<h2 style="text-align: left">Sujeto Retenido</h2>
						<div class="page" style="font-size: 7pt">
						<p>
							<?php echo $nombre;  ?>
						</p>
						<p>
							<?php echo 'Direc' ?> <br>
							<b>RIF: <?php echo $rif; ?></b>
						</p>

						<table align='center' font-size: 7pt;">
							<tr>
								<td><b>Fecha del Documento:</b></td>
								<td><?php echo $fecha; ?></td>
							</tr>
							<tr>
								<td><b>N&uacute;mero de Factura:</b> </b></td>
								<td><?php echo ($tipo_doc=='FC')? $numero:' '; ?></td>
							</tr>
							<tr>
								<td><b>N&uacute;mero de control:</b></td>
								<td><?php echo $nfiscal; ?></td>
							</tr>
							<tr>
								<td><b>N&uacute;mero de Nota de D&eacute;bito:</b></td>
								<td><?php echo ($tipo_doc=='ND')? $numero:' '; ?></td>
							</tr>
							<tr>
								<td><b>N&uacute;mero de Nota de Cr&eacute;dito:</b></td>
								<td><?php echo ($tipo_doc=='NC')? $numero:' '; ?></td>
							</tr>
							<tr>
								<td><b>Tipo de Transacci&oacute;n:</b></td>
								<td><?php echo ($tipo_doc=='FC')? '01': ($tipo_doc=='ND')? '02': '03'; ?></td>
							</tr>
							<tr>
								<td><b>N&uacute;mero de Documento Afectado:</b></td>
								<td><?php echo $afecta; ?></td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</thead>
		<tr>
			<td>
				<div id="content">
					<div class="page" style="font-size: 7pt">
						<table class="change_order_items">
							<thead>
								<tr>
									<th>Total Compra con I.V.A.</th>
									<th>Total Compra Exenta</th>
									<th>Base Imponible</th>
									<th>Alicuota %</th>
									<th>Impuesto I.V.A.</th>
								</tr>
							</thead>
							<tbody>
								<tr class="even_row">
									<td style="text-align: right" rowspan="3"><?php echo nformat($gtotal); ?></td>
									<td style="text-align: right" rowspan="3"><?php echo nformat($exento); ?></td>
									<td style="text-align: right"><?php echo nformat($reducida);  ?></td>
									<td style="text-align: right"><?php echo nformat($tasaredu);  ?></td>
									<td style="text-align: right"><?php echo nformat($reduimpu);  ?></td>
								</tr>
								<tr class="odd_row">
									<td style="text-align: right"><?php echo nformat($general);   ?></td>
									<td style="text-align: right"><?php echo nformat($tasa);      ?></td>
									<td style="text-align: right"><?php echo nformat($geneimpu);  ?></td>
								</tr>
								<tr class="even_row">
									<td style="text-align: right"><?php echo nformat($adicional); ?></td>
									<td style="text-align: right"><?php echo nformat($tasaadic);  ?></td>
									<td style="text-align: right"><?php echo nformat($adicimpu);  ?></td>
								</tr>
								</tbody><tfoot>
								<tr>
									<td colspan='4' style="text-align: right">Total I.V.A. CAUSADO Bs:</td>
									<td style="text-align: right"><?php echo nformat($impuesto);  ?></td>
								</tr>
								<tr>
									<td colspan='4' style="text-align: right" >Total I.V.A. RETENIDO Bs:</td>
									<td style="text-align: right"><?php echo nformat($reiva);  ?></td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</td>
		</tr>
</table>
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<hr>
<table  style="width: 100%;" class="header">
		<tr>
			<td><b><div align="center" style="font-size: 8pt">Firma Y Sello<br>Agente de Retenci&oacute;n</div></b></td>
			<td><b><div align="center" style="font-size: 8pt">Firma Y Sello<br>Del Beneficiario</div></b></td>
		</tr>
		<tr>
			<td style="text-align: right" colspan='2'><b><div style="font-size: 8pt;text-align: right">Fecha de Entrega <?php echo $emision; ?></div></b></td>
		</tr>
</table>
</div>
</body>
</html>
