<?php
$maxlin=33; //Maximo de lineas de items.

if(count($parametros) < 0) show_error('Faltan parametros');
$id=$parametros[0];

$sel=array('a.tipop','a.numero','a.codban','a.clipro','a.fecha','a.vence','a.monto','a.cuotas'
,'b.banco','a.numche','a.nombre','CONCAT_WS(\' \',TRIM(a.observa1),a.observa2) AS observa'
,'a.transac','b.tbanco','b.numcuent','a.docum','a.banco AS prmobanco');
$this->db->select($sel);
$this->db->from('prmo AS a');
$this->db->join('banc AS b','a.codban=b.codbanc');
$this->db->where('a.tipop' , '3');
$this->db->where('a.id'  , $id);

$mSQL_1 = $this->db->get();
if($mSQL_1->num_rows()==0) show_error('Registro no encontrado');

$row = $mSQL_1->row();
$tipo_doc = trim($row->tipop);
$numero   = $row->numero;
$clipro   = $this->us_ascii2html(trim($row->clipro));
$numcuent = $this->us_ascii2html(trim($row->numcuent));
$numche   = $this->us_ascii2html(trim($row->numche));
$docum    = $this->us_ascii2html(trim($row->docum));
$tipop    = trim($row->tipop);
$fecha    = dbdate_to_human($row->fecha);
$vence    = dbdate_to_human($row->vence);
$hfecha   = dbdate_to_human($row->fecha);
$monto    = nformat($row->monto);
$cuotas   = $row->cuotas;
$banco    = $this->us_ascii2html(trim($row->banco));
$nombre   = $this->us_ascii2html(trim($row->nombre));
$observa  = wordwrap(trim(str_replace(',',', ',$row->observa)), 90, '<br>');
$transac  = $row->transac;
$prmobanco= trim($row->prmobanco);

if(empty($prmobanco)){
	$dbnumche = intval(preg_replace('/^0+/', '',trim($row->numche)));
	$dbclipro = $this->db->escape($clipro);

	$mSQL="SELECT b.nomb_banc
	FROM sfpa AS a
	JOIN tban AS b ON b.cod_banc=a.banco
	WHERE a.cod_cli=${dbclipro} AND a.tipo='CH' AND
	a.num_ref REGEXP '^0*${dbnumche} *$'";
	$nbanco = $this->datasis->dameval($mSQL);
	if(!empty($nbanco)){
		$tprmobanco= '<b>del banco: </b> '.$this->us_ascii2html($nbanco);
	}else{
		$tprmobanco='';
	}
}else{
	$tprmobanco= '<b>del banco: </b> '.$this->us_ascii2html($prmobanco);
}

$lineas=0;
?><html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?php echo $this->config->item('charset'); ?>" >
<title>Registro de cheque devuelto por cliente y otros<?php echo $numero ?></title>
<link rel="stylesheet" href="<?php echo $this->_direccion ?>/assets/default/css/formatos.css" type="text/css" >
</head>
<body style="margin-left: 30px; margin-right: 30px;">

<?php $this->incluir('X_CINTILLO'); ?>
<div style="width: 100%; text-align:center;">
	<table  class="header" style="width: 100%;">
		<tr>
			<td valign='bottom'><h1 style="text-align: left">REGISTRO DE CHEQUE DEVUELTO POR CLIENTES Y OTROS No. <?php echo $numero; ?></h1></td>
			<td valign='bottom'><h1 style="text-align: right">Fecha: <?php echo $hfecha; ?></h1></td>
		</tr>
	</table>
</div>
<div style="width: 100%; text-align:center;">
	<table width='90%' align='center' style="font-size: 8pt;">
		<tr>
			<td style="text-align: right;"><b>Con cargo a:</b></td>
			<td style="text-align:  left;" ><?php echo "(${clipro}) ${nombre}"; ?></td>
		</tr>
		<tr>
			<td style="text-align: right;"><b>Concepto:</b></td>
			<td style="text-align:  left;" ><?php echo $observa; ?></td>
		</tr>
		<tr>
			<td style="text-align: right;"><b>Vencimiento:</b></td>
			<td style="text-align:  left;" ><?php echo $vence; ?></td>
		</tr>
		<tr>
			<td style="text-align: center;font-size:2.2em; border-bottom-style:solid; border-top-style:solid;border-width:1px;" colspan='2'>
				<b>Monto del cheque devuelto: <?php echo $monto; ?> Bs.</b>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table width="100%">
					<tr>
						<td style="text-align: center;" ><b>Devuelto del banco: </b><br><?php echo $banco; ?></td>
						<td style="text-align: center;" ><b>Cuenta: </b><br><?php echo $numcuent; ?></td>
						<td style="text-align: center;" ><b>Nota: </b><br><?php echo $numche; ?></td>
					</tr>
					<tr>
						<td style="text-align: center;" colspan='3'><b>Cheque devuelto nro.: </b> <?php echo $numche; ?> <?php echo $tprmobanco; ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>
<br>
<div>
	<table width="100%" style="border-collapse: collapse;" >
		<tr>
			<td style="text-align:center; border-style:solid; border-width:2px;font-size:8pt" valign="bottom"><br><br><br>Recibido por:</td>
			<td style="text-align:center; border-style:solid; border-width:2px;font-size:8pt" valign="bottom">C.I.:        </td>
			<td style="text-align:center; border-style:solid; border-width:2px;font-size:8pt" valign="bottom">Fecha:       </td>
		</tr>
		<tr>
			<td style="text-align:center; border-style:solid; border-width:2px;font-size:8pt" valign="bottom"><br><br><br>Elaborado por:</td>
			<td style="text-align:center; border-style:solid; border-width:2px;font-size:8pt" valign="bottom">Autor&iacute;a:</td>
			<td style="text-align:center; border-style:solid; border-width:2px;font-size:8pt" valign="bottom">Autorizado por:</td>
		</tr>
	</table>
</div>
</body>
</html>
