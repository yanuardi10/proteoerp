<?php
if(count($parametros)==0) show_error('Faltan parametros ');
$id=$parametros[0];
$dbid=$this->db->escape($id);
$mSQL_1 = $this->db->query("SELECT fecha,anticipo,credito,numero,proveed,nombre,vence,orden,totpre,totiva,totbruto,reten,totneto,codb1,tipo1,cheque1,monto1,reducida,reteiva,monredu,tasa,sobretasa,montasa FROM gser WHERE id=$dbid");

if($mSQL_1->num_rows()==0) show_error('Registro no encontrado'); 
$row       = $mSQL_1->row();
$fecha     = dbdate_to_human($row->fecha);
$numero    = $row->numero;
$proveed   = $row->proveed;

$dbfecha   = $this->db->escape($row->fecha);
$dbnumero  = $this->db->escape($row->numero);
$dbproveed = $this->db->escape($row->proveed);

$mSQL_2 = $this->db->query("SELECT numero,fecha,proveed,codigo,descrip,precio,iva,importe FROM gitser WHERE numero=$dbnumero AND fecha=$dbfecha AND proveed=$dbproveed");

$nombre   =$row->nombre;
$vence    =dbdate_to_human($row->vence);
$totpre   =$row->totpre;
$totiva   =$row->totiva;
$totbruto =$row->totbruto;
$reten    =$row->reten;
$totneto  =$row->totneto;
$reteiva  =$row->reteiva;
$anticipo =$row->anticipo;
$credito  =$row->credito;
$monto1   =$row->monto1;
$cheque1  =$row->cheque1;
$codb1    =$row->codb1;
$orden    =$row->orden;
$detalle  =$mSQL_2->result();

$cuenta=$this->db->query("SELECT numcuent,banco FROM banc WHERE  codbanc='$codb1'");
if($cuenta->num_rows()>0){
	$row2     = $cuenta->row();
	$numcuent = $row2->numcuent;
	$banco    = $row2->banco;
}else{
	$numcuent = '';
	$banco    = '';
}
?>
<html>
<head>
<title>Comprobante de Egresos <?php echo $numero ?></title>
<link rel="STYLESHEET" href="<?php echo $this->_direccion ?>/assets/default/css/formatos.css" type="text/css" />
</head>
<body>
<script type="text/php">
if ( isset($pdf) ) {

  $font = Font_Metrics::get_font("verdana");;
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
        <td><div id="section_header">
            <table style="width: 100%;" class="header">
              <tr>
                <td width=140 rowspan="2"><img src="<?php echo $this->_direccion ?>/images/logo.jpg" width="127"></td>
                <td><h1 style="text-align: right"><?php echo $this->datasis->traevalor('TITULO1'); ?></h1>
                  </td>
              </tr>
			  <tr>
			  	<td><div class="page" style="font-size: 7pt">
                    <?php echo $this->datasis->traevalor('TITULO2').' '.$this->datasis->traevalor('TITULO3'); ?> <br>
                    <b>RIF: <?php echo $this->datasis->traevalor('RIF'); ?></b>
                  </div></td>
			  </tr>
            </table>
            </div>
            <div class="page" style="font-size: 7pt">
            <table style="width: 100%;" class="header">
              <tr>
                <td><h1 style="text-align: left">Comprobante de Egresos</h1></td>
                <td><h1 style="text-align: right">Número: <?php echo $numero ?></h1></td>
              </tr>
            </table>
            <table style="width: 100%; font-size: 8pt;">
              <tr>
                <td>Proveedor: <strong><?php echo $nombre; ?> (<?php echo $proveed; ?>)</strong></td>
              </tr>
              <tr>
                <td>Factura de Gasto: <strong><?php echo $numero; ?></strong></td>
                <td>De Fecha: <strong><?php echo $fecha; ?></strong></td>
              </tr>
              <tr>
                <td>Segun O/C Nro(S): <strong><?php echo $orden; ?></strong></td>
                <td>F.vcmnto: <strong><?php echo $vence; ?></strong></td>
              </tr>
               <tr>
                <td>Pagado Asi: <strong></strong></td>
              </tr>
              <tr>
                <td collspan="2">Banco/Caja:<strong>(<?php echo $codb1;  ?>)<?php echo $banco;  ?></strong>
                  Cuenta:<strong><?php echo $numcuent;  ?></strong> 
                  Debito No:<strong><?php echo $cheque1;  ?></strong>
                  Monto Bs.F. :<strong><?php echo $monto1;  ?></strong>
                  </td>                       
              </tr>
              <tr>
                <td>Total pago: <strong><?php echo numletra($totbruto) ?></strong></td>
              </tr>
            </table>
          </div></td>
      </tr>
    </thead>
    <tr>
      <td><div id="content">
          <div class="page" style="font-size: 7pt">
            <table class="change_order_items">
              <thead>
           	<tr>
                  <th>Código</th>
                  <th>Descripción</th>
                  <th>Precio</th>
                  <th>Iva</th>
                  <th>Importe</th>
                </tr>
		</thead>
		<tbody>
                 <?php $tprecio=0; $tiva=0; $timporte=0; $mod=FALSE; $i=0; foreach ($detalle AS $items){ $i++;?>
                 <?php $tprecio+=$items->precio ?>
                 <?php $tiva+=$items->iva ?>
                 <?php $timporte+=$items->importe ?>
                <tr class="<?php if(!$mod) echo 'even_row'; else  echo 'odd_row'; ?>">
                  <td style="text-align: center"><?php echo $items->codigo ?></td>
                  <td><?php echo $items->descrip ?></td>
                  <td style="text-align: right"><?php echo $items->precio ?></td>
                  <td style="text-align: right"><?php echo $items->iva ?></td>
                  <td style="text-align: right"><?php echo $items->importe ?></td>
                </tr>
                <?php //if($i%10==0) echo "<p STYLE='page-break-after: always'></p>"; ?>
                <?php $mod = ! $mod; } ?>
              </tbody>
	       <tfoot>
	       <tr>
                <td   colspan="2" style="text-align: right;"></td>
                <td  style="text-align: right;"><strong><?php  echo $tprecio ?></strong></td>
                <td  style="text-align: right;"><strong><?php  echo $tiva ?></strong></td>
                <td  style="text-align: right;"><strong><?php  echo $timporte ?></strong></td>
              </tr>
	       <tr>
                <td colspan="2" style="text-align: right;"></td>
                <td colspan="2" style="text-align: right;"><strong>RETENCION DE I.S.L.R:</strong></td>
                <td class="change_order_total_col"><strong><?php echo $reten ?></strong></td>
              </tr>
              <tr>
                <td colspan="2" style="text-align: right;"></td>
                <td colspan="2" style="text-align: right;"><strong>RETENCION DE I.V.A:</strong></td>
                <td class="change_order_total_col"><strong><?php echo $reteiva ?></strong></td>
              </tr>
              <tr>
                <td colspan="2" style="text-align: right;"></td>
                <td colspan="2" style="text-align: right;"><strong>MONTO NETO:</strong></td>
                <td class="change_order_total_col"><strong><?php echo $totneto ?></strong></td>
              </tr>
              <tr>
                <td colspan="2" style="text-align: right;"></td>
                <td colspan="2" style="text-align: right;"><strong>ANTICIPOS RECIBIDOS:</strong></td>
                <td class="change_order_total_col"><strong><?php echo $anticipo ?></strong></td>
              </tr>
              <tr>
                <td colspan="2" style="text-align: right;"></td>
                <td colspan="2" style="text-align: right;"><strong>MONTO CANCELAD0:</strong></td>
                <td class="change_order_total_col"><strong><?php echo $totbruto ?></strong></td>
              </tr>
              <tr>
                <td colspan="2" style="text-align: right;"></td>
                <td colspan="2" style="text-align: right;"><strong>MONTO A CREDITO:</strong></td>
                <td class="change_order_total_col"><strong><?php echo $credito ?></strong></td>
              </tr>
			  </tfoot>
            </table>
          </div>
        </div></td>
    </tr>
</table>
<table  style="width: 100%;" class="header">
    <tr>
    	<td><strong><div align="center" style="font-size: 8pt">Elaborado por:</strong></div></td>
    	<td><strong><div align="center" style="font-size: 8pt">Auditoria:</strong></div></td>
    	<td><strong><div align="center" style="font-size: 8pt">Autorizado por:</strong></div></td>
    	<td><strong><div align="center" style="font-size: 8pt">Aprobado:</strong></div></td>
    </tr>
  </table>
</div>
</body>
</html>
