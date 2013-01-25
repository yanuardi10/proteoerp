<?php if(count($parametros) < 0) show_error('Faltan parametros');
$id=$parametros[0];

$sel=array('a.tipo_doc','a.numero','a.cod_cli','a.fecha','a.monto','a.abonos','a.tipo_doc'
,'b.nombre','CONCAT_WS(\' \',dire1,dire2) AS direc','b.rifci'
,'CONCAT_WS(\' \',observa1,observa2) AS observa','b.rifci','a.transac');
$this->db->select($sel);
$this->db->from('smov AS a');
$this->db->join('scli AS b'  ,'a.cod_cli=b.cliente');

$this->db->where('a.id'   , $id);

$mSQL_1 = $this->db->get();

$row = $mSQL_1->row();
$tipo_doc = $row->tipo_doc;
$numero   = $row->numero;
$cliente  = $row->cod_cli;
$tipo_doc = $row->tipo_doc;
$fecha    = $row->fecha;
$hfecha   = dbdate_to_human($row->fecha);
$monto    = $row->monto;
$montole  = numletra($row->monto);
$abonos   = $row->abonos;
$nombre   = $row->nombre;
$rifci    = $row->rifci;
$direc    = $row->direc;
$observa  = $row->observa;
$transac  = $row->transac;

$sel=array('b.tipo_doc','b.numero','b.fecha','b.monto','b.abono','b.reten','b.ppago','b.cambio','b.mora','b.reteiva');
$this->db->select($sel);
$this->db->from('smov AS a');
$this->db->join('itccli AS b','a.tipo_doc = b.tipoccli AND a.numero=b.numccli AND a.cod_cli=b.cod_cli');
$this->db->where('a.cod_cli' ,$cliente);
$this->db->where('a.tipo_doc',$tipo_doc);
$this->db->where('a.numero'  ,$numero);
$this->db->where('a.fecha'   ,$row->fecha);
$mSQL_2 = $this->db->get();
$detalle  = $mSQL_2->result();

$sel=array('a.tipo','a.monto','a.num_ref','a.fecha','a.cambio','b.nomb_banc AS banco');
$this->db->select($sel);
$this->db->from('sfpa AS a');
$this->db->join('tban AS b','a.banco=b.cod_banc','left');
$this->db->where('a.transac',$transac);
$mSQL_3 = $this->db->get();
$detalle2 = $mSQL_3->result();

$ittot['monto']=$ittot['reten']=$ittot['ppago']=$ittot['cambio']=$ittot['mora']=$ittot['reteiva']=$ittot['abono']=0;
?><html>
<head>
<title>Abono <?php echo $numero ?></title>
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
					<div id="section_header">
						<table style="width: 100%;" class="header">
							<tr>
								<td width=140 rowspan="2"><img src="<?php echo $this->_direccion ?>/images/logo.jpg" width="127"></td>
								<td><h1 style="text-align: right"><?php echo $this->datasis->traevalor('TITULO1'); ?></h1></td>
							</tr>
							<tr>
								<td>
									<div class="page" style="font-size: 7pt">
									<?php echo $this->datasis->traevalor('TITULO2').' '.$this->datasis->traevalor('TITULO3'); ?> <br>
									<b>RIF: <?php echo $this->datasis->traevalor('RIF'); ?></b>
									</div>
								</td>
							</tr>
						</table>
					</div>
					<div class="page" style="font-size: 7pt">
						<table style="width: 100%;" class="header">
							<tr>
								<td><h1 style="text-align: left">RECIBO DE INGRESO No. <?php echo $numero ?></h1></td>
								<td><h1 style="text-align: right">Fecha:<?php echo $hfecha ?></h1></td>
							</tr>
						</table>
						<table align='center' font-size: 8pt;">
							<tr>
								<td><b>Hemos recibido de:</b></td>
								<td>(<?php echo $cliente; ?>) <?php echo $nombre; ?></td>
							</tr>
							<tr>
								<td><b>Con RIF:</b> </b></td>
								<td><?php echo $rifci; ?></td>
							</tr>
							<tr>
								<td><b>Dirección:</b></td>
								<td><?php echo $direc; ?></td>
							</tr>
							<tr>
								<td><b>La cantidad de:</b></td>
								<td><?php echo strtoupper($montole); ?> Bs.</td>
							</tr>
							<tr>
								<td><b>Por concepto de:</b></td>
								<td><?php echo $observa; ?></td>
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
						<h2>Efectos afectados</h2>
						<table class="change_order_items">
							<thead>
								<tr>
									<th>Documento  </th>
									<th>Fecha      </th>
									<th>Monto/abono</th>
									<th>Ret/islr   </th>
									<th>Desc/P.Pago</th>
									<th>Dif/Cambio </th>
									<th>Int/Mora   </th>
									<th>Ret/IVA    </th>
									<th>Abono neto </th>
								</tr>
							</thead>
							<tbody>
								<?php $i=0; $mod=false; foreach ($detalle AS $items){ $i++;?>
								<tr class="<?php if(!$mod) echo 'even_row'; else  echo 'odd_row'; ?>">
									<td style="text-align: left" ><?php echo $items->tipo_doc.$items->numero; ?></td>
									<td style="text-align: left" ><?php echo dbdate_to_human($items->fecha);  ?></td>
									<td style="text-align: right"><?php $ittot['monto']   += $items->monto  ; echo nformat($items->monto  ,2); ?></td>
									<td style="text-align: right"><?php $ittot['reten']   += $items->reten  ; echo nformat($items->reten  ,2); ?></td>
									<td style="text-align: right"><?php $ittot['ppago']   += $items->ppago  ; echo nformat($items->ppago  ,2); ?></td>
									<td style="text-align: right"><?php $ittot['cambio']  += $items->cambio ; echo nformat($items->cambio ,2); ?></td>
									<td style="text-align: right"><?php $ittot['mora']    += $items->mora   ; echo nformat($items->mora   ,2); ?></td>
									<td style="text-align: right"><?php $ittot['reteiva'] += $items->reteiva; echo nformat($items->reteiva,2); ?></td>
									<td style="text-align: right"><?php $ittot['abono']   += $items->abono  ; echo nformat($items->abono  ,2); ?></td>
								</tr>
								<?php $mod = ! $mod; } ?>
								<tr>
									<td colspan='2' ></td>
									<td style="text-align: right"><?php echo nformat($ittot['monto']  ,2); ?></td>
									<td style="text-align: right"><?php echo nformat($ittot['reten']  ,2); ?></td>
									<td style="text-align: right"><?php echo nformat($ittot['ppago']  ,2); ?></td>
									<td style="text-align: right"><?php echo nformat($ittot['cambio'] ,2); ?></td>
									<td style="text-align: right"><?php echo nformat($ittot['mora']   ,2); ?></td>
									<td style="text-align: right"><?php echo nformat($ittot['reteiva'],2); ?></td>
									<td style="text-align: right"><?php echo nformat($ittot['abono']  ,2); ?></td>
								</tr>
							</tbody>
							</table>
							<h2>Forma de pago</h2>
							<table class="change_order_items">
								<thead>
								<tr>
									<th>Tipo</th>
									<th>Fecha</th>
									<th>Banco</th>
									<th>Referencia</th>
									<th>Monto</th>
								</tr>
								</thead>
								<tbody>
								<?php $i=0; $mod=false; foreach ($detalle2 AS $items2){ $i++;?>
								<tr class="<?php if(!$mod) echo 'even_row'; else  echo 'odd_row'; ?>">
									<td style="text-align: right" ><?php echo $items2->tipo;            ?></td>
									<td style="text-align: center"><?php echo dbdate_to_human($items2->fecha); ?></td>
									<td style="text-align: left"  ><?php echo $items2->banco;           ?></td>
									<td style="text-align: left"  ><?php echo $items2->num_ref;         ?></td>
									<td style="text-align: right"><?php echo nformat($items2->monto,2); ?></td>
								</tr>
								<?php $mod = ! $mod; } ?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan='5'></td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</td>
		</tr>
</table>
<table  style="width: 100%;" class="header">
		<tr>
			<td><b><div align="center" style="font-size: 8pt">Firma Autorizada:</div></b></td>
			<td><b><div align="center" style="font-size: 8pt">Recibe Conforme:</div></b></td>
		</tr>
	</table>
</div>
</body>
</html>
