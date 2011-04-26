<?php 
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	$meco = $form->output;
	$meco = str_replace('class="tablerow"','class="tablerow" style="font-size:20px; align:center;" ',$meco);
	echo $meco."</td><td align='center'>".img("images/borrar.jpg");
else:
?>
<?php echo $form_scripts?>
<?php echo $form_begin?>

<?php if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<table border='0' width="100%" style='background: #EEEEEE'>
	<tr>
		<td width='40' align='center'>
			<?php if($form->_status=='show'){ ?>
			<a href='<?php echo base_url()."ventas/scli/consulta/".$form->_dataobject->get('id'); ?>'>
			<?php
				$propiedad = array('src' => 'images/ojos.png', 'alt' => 'Consultar Movimiento', 'title' => 'Consultar Detalles','border'=>'0','height'=>'25');
				echo img($propiedad);
			?>
			</a>
		</td>
		<td align='center' valign='middle' width='60'>
			<a href='<?php echo base_url()."ventas/scli/fusionar/".$form->_dataobject->get('id'); ?>'>
			<?php
				$propiedad = array('src' => 'images/fusionar.png', 'alt' => 'Fucionar o Cambiar', 'title' => 'Fusionar/Cambiar Codigo','border'=>'0','height'=>'20');
				echo img($propiedad);
			?>
			</a>
		</td>
		<td align='center' valign='middle' width='40'>
			<?php } ?>
			<a href='<?php echo base_url()."reportes/index/scli" ?>'>
			<?php
				$propiedad = array('src' => 'images/reportes.gif', 'alt' => 'Reportes', 'title' => 'Reportes','border'=>'0','height'=>'20');
				echo img($propiedad);
			?>
			</a>
		</td>
		<td align='center' valign='middle'>
			<?php  if ($form->tipo->value=='0') echo "<div style='font-size:14px;font-weight:bold;color: #B40404'>***DESACTIVADO***</div>"; ?>&nbsp;
		</td>
		<td align='right'><?php echo $container_tr; ?></td>
	</tr>
</table>
<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
<legend class="titulofieldset" style='color: #114411;'>Identificacion</legend>
<table border='0' width="100%">
	<tr>
		<td>
			<table border='0' width="100%">
				<tr>
					<td width="100" class="littletableheaderc"><?=$form->cliente->label  ?></td>
					<td width='150' class="littletablerow" ><?=$form->cliente->output ?></td>
					<td width="60"  class="littletableheaderc"><?=$form->rifci->label ?></td>
					<td  class="littletablerow"><?php echo $form->rifci->output ?>
					<?php if($form->_status=='show'){ ?>
					<a href="#" onclick="window.open('<?php echo trim($this->datasis->traevalor("CONSULRIF"))."?p_rif=".trim($form->rifci->value);?>','CONSULRIF','height=350,width=410')" title="SENIAT" style='color:red;font-size:9px;border:none;'>SENIAT</a>
					<?php } ?>
					</td>
				</tr>	
				<tr>
					<td class="littletableheaderc"><?=$form->nombre->label ?></td>
					<td colspan='3' class="littletablerow"><?=$form->nombre->output?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->nomfis->label ?></td>
					<td colspan='3' class="littletablerow"><?=$form->nomfis->output?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->contacto->label  ?></td>
					<td colspan='3' class="littletablerow"><?=$form->contacto->output ?></td>
				</tr>
			</table>
		</td>
		<td valign='top' width='25%'>
			<table border=0 width="100%">
				<tr>
					<td class="littletableheaderc"> <?=$form->tipo->label ?></td>
					<td class="littletablerow"> <?=$form->tipo->output ?></td>
				</tr>
				<tr>
					<td colspan='2'>
			<fieldset style='border: 1px dotted #8A0808;background: #FFFBE9;'>
			<legend class="titulofieldset" style='color: #114411;'>Credito</legend>
			<table width= '100%' >
				<tr>
					<td class="littletableheaderc"><?=$form->formap->label  ?></td>
					<td class="littletablerow"><?=$form->formap->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?php echo $form->limite->label  ?></td>
					
					<td class="littletablerow">    <?php echo $form->limite->output ?></td>
				</tr>				
			</table>
			</fieldset>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table width='100%' style='border: 1px dotted #8A0808;background: #FFFBE9;'>
	<tr>
		<td class="littletableheaderc"><?=$form->grupo->label ?></td>
		<td class="littletablerow"><?=$form->grupo->output?></td>
		<td class="littletableheaderc"><?=$form->tiva->label  ?></td>
		<td class="littletablerow"><?=$form->tiva->output ?></td>
		<td  width="70" class="littletableheaderc"><?=$form->zona->label  ?></td>
		<td class="littletablerow"><?=$form->zona->output ?></td>
	</tr>
</table>
<table width= '100%' style='border: 1px dotted #8A0808;background: #FFFBE9;'>
	<tr>
		<td class="littletableheaderc">Cuenta Contable</td>
		<td class="littletablerow"    ><?=$form->cuenta->output; ?>
		<?php
		if ( $form->_status == 'show' ) {
			$mSQL = "SELECT descrip FROM cpla WHERE codigo='".trim($form->cuenta->output)."'";
			echo $this->datasis->dameval($mSQL);
		}
		?>
		</td>
		<td class="littletableheaderc">Cliente Asociado</td>
		<td class="littletablerow"><?=$form->socio->output ?></td>
<?php if (!empty($form->socio->value)) { ?>
		<td class="littletablerow"><?php echo $this->datasis->dameval("SELECT nombre FROM scli WHERE cliente='".$form->socio->value."'") ?></td>
<?php }; ?>
	</tr>
</table>

</fieldset>

<div class="maintabcontainer">
	<ul class="tabs">
		<li><a href="#tab1">Direcciones</a></li>
		<li><a href="#tab2">Valores</a></li>
		<li><a href="#tab3">Anexo</a></li>
		<li><a href="#tab4">Historia</a></li>
	</ul>
	<div class="tab_container">
	<div id="tab1" class="tab_content" style='background:#eeffff'>

<table border='0' width="100%">
	<tr>
		<td valign='top' width='50%' style='border-top: 1px dotted; border-right: 1px dotted'>
			<table border='0' width='100%' >
				<tr>
					<td colspan='2' class="littletableheaderc">Direccion de Oficina</td>
				</tr>
				<tr>
					<td colspan='2' class="littletablerow"><?=$form->dire11->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='2' class="littletablerow"><?=$form->dire12->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->ciudad1->label   ?></td>
					<td class="littletablerow" ><?=$form->ciudad1->output  ?>&nbsp;</td>
				</tr>
			</table>
		</td>
		<td valign='top' width='50%' style='border-top: 1px dotted; border-left: 1px dotted'>
			<table border='0'  width='100%'>
				<tr>
					<td colspan='2' class="littletableheaderc">Direccion de Envio</td>
				<tr>
				</tr>
					<td colspan='2' class="littletablerow"><?=$form->dire21->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='2' class="littletablerow"><?=$form->dire22->output ?>&nbsp;</td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->ciudad2->label ?></td>
					<td class="littletablerow" style='font-size:11;'><?=$form->ciudad2->output ?>&nbsp;</td>
				</tr>
			</table>
		</td>
	<tr>
</table>
<br>
<table style='height: 100%;width: 100%;border: 1px dotted;'>
	<tr>
		<td class="littletableheaderc"><?=$form->telefono->label  ?></td>
		<td class="littletablerow"    ><?=$form->telefono->output ?></td>
		<td class="littletableheaderc"><?=$form->telefon2->label  ?></td>
		<td class="littletablerow"    ><?=$form->telefon2->output ?></td>
	</tr>
	<tr>
		<td class="littletableheaderc"><?=$form->pais->label  ?> </td>
		<td class="littletablerow"    ><?=$form->pais->output ?> </td>
		<td class="littletableheaderc"><?=$form->email->label  ?></td>
		<td class="littletablerow"    ><?=$form->email->output ?></td>
	</tr>
</table>
        </div>
        <div id="tab2" class="tab_content">

<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
<table border='0' >
	<tr>
		<td class="littletableheaderc">Representante Legal</td>
		<td class="littletablerow"><?=$form->repre->output ?></td>
		<td class="littletableheaderc">C.I.</td>
		<td class="littletablerow"><?=$form->cirepre->output ?></td>
	</tr>
</table>
<br>
<table >
	<tr>
		<td class="littletableheaderc"><?=$form->vendedor->label  ?></td>
		<td class="littletablerow"><?=$form->vendedor->output ?></td>
		<td class="littletableheaderc">Comision %</td>
		<td class="littletablerow"><?=$form->porvend->output ?></td>
	</tr>				
	<tr>
		<td class="littletableheaderc"><?=$form->cobrador->label  ?></td>
		<td class="littletablerow"><?=$form->cobrador->output ?></td>
		<td class="littletableheaderc">Comision %</td>
		<td class="littletablerow"><?=$form->porcobr->output ?></td>
	</tr>				
</table>
<br>	

</fieldset>
		<?php if( $form->_status == 'show') {  ?>
		<?php } ?>
        </div>
        <div id="tab3" class="tab_content">
<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
<table width= '100%' >
	<tr>
		<td class="littletableheaderc"><?=$form->mensaje->label  ?></td>
		<td class="littletablerow"><?=$form->mensaje->output ?></td>
	</tr>				
	<tr>
		<td class="littletableheaderc"><?=$form->observa->label  ?></td>
		<td class="littletablerow"><?=$form->observa->output ?></td>
	</tr>				
</table>
</fieldset>
        </div>
        <div id="tab4" class="tab_content">
<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>

<?php if($form->_status=='show'){ ?>
<table  width='100%'>
	<tr>
		<td width='60%'>
			<table border='0' width='100%'>
				<tr>
					<td width='70%' class="littletableheaderc">Primera Compra</td>
					<td class='littletablerow'>
					<?php
					$query = $this->db->query("SELECT fecha, tipo_doc,numero, totalg FROM sfac WHERE cod_cli='".$form->cliente->value."' AND tipo_doc='F'ORDER BY fecha LIMIT 1 ");
					if($query->num_rows() == 0) {
						echo "No tiene compras registradas";
					} else {
						$row = $query->row_array();
						echo '<table><tr>';
					        echo "<td class='littletablerow'>".dbdate_to_human($row['fecha'])."</td>";
					        echo "<td class='littletablerow'>".$row['tipo_doc'].$row['numero']."</td>";
					        echo "<td class='littletablerow'>Bs.</td>";
					        echo "<td width='70' class='littletablerow' align='right'>".nformat($row['totalg'])."</td>";
						echo '</tr></table>';
					}
					?>
					</td>
				</tr>				
				<tr>
					<td class="littletableheaderc">Ultima Compra</td>
					<td class='littletablerow'>
						<?php
					$query = $this->db->query("SELECT fecha, tipo_doc,numero, totalg FROM sfac WHERE cod_cli='".$form->cliente->value."' AND tipo_doc='F'ORDER BY fecha DESC LIMIT 1 ");
					if($query->num_rows() == 0) {
						echo "No tiene compras registradas";
					} else {
						$row = $query->row_array();
						echo '<table><tr>';
					        echo "<td class='littletablerow'>".dbdate_to_human($row['fecha'])."</td>";
					        echo "<td class='littletablerow'>".$row['tipo_doc'].$row['numero']."</td>";
					        echo "<td class='littletablerow'>Bs.</td>";
					        echo "<td width='70' class='littletablerow' align='right'>".nformat($row['totalg'])."</td>";
						echo '</tr></table>';
					}
					?>
					</td>
				</tr>				
				<tr>
					<?php
					$mSQL = "
					SELECT sum(monto-abonos) saldo
					FROM smov
					WHERE cod_cli='".$form->cliente->value."' AND tipo_doc IN ('FC','ND','GI') AND monto>abonos AND vence>curdate()";
					$query = $this->db->query($mSQL);
					if($query->num_rows() == 0) {
						echo "No tiene efectos pendientes";
					} else {
						$row = $query->row_array();
						echo '<td class="littletableheaderc">Monto Vencido</td>';
						echo "<td width='100' class='littletablerow' align='left'>".nformat($row['saldo'])."</td>";
					}
					?>
				</tr>
				<tr>
					<?php
					$mSQL = "
					SELECT sum(monto-abonos) saldo
					FROM smov
					WHERE cod_cli='".$form->cliente->value."' AND tipo_doc IN ('AN','NC') AND monto>abonos ";
					$query = $this->db->query($mSQL);
					if($query->num_rows() == 0) {
						echo "No tiene Anticipos";
					} else {
						$row = $query->row_array();
						echo '<td class="littletableheaderc">Anticipos pendientes</td>';
						echo "<td width='100' class='littletablerow' align='left'>".nformat($row['saldo'])."</td>";
					}
					?>
				</tr>					
			</table>
		</td>
		<td>
			<table border='0' width='100%'>
				<tr>
					<td>
					<?php
					$query = $this->db->query("SELECT sum(monto*IF(tipo_doc IN ('AB','NC','AN'),0,1)) debe, sum(monto*IF(tipo_doc IN ('AB','NC','AN'),1,0)) haber, sum(monto*IF(tipo_doc IN ('AB','NC','AN'),-1,1)) saldo FROM smov WHERE cod_cli='".$form->cliente->value."'");
					if($query->num_rows() == 0) {
						echo "No tiene creditos registradas";
					} else {
						$row = $query->row_array();
						echo '<table width="100%"><tr>';
						echo '<td class="littletableheaderc">Total Comprado</td>';
						echo "<td width='100' class='littletablerow' align='right'>".nformat($row['debe'])."</td>";
						echo "<tr></tr>";
						echo '<td class="littletableheaderc">Total Pagado</td>';
						echo "<td width='100' class='littletablerow' align='right'>".nformat($row['haber'])."</td>";
						echo "<tr></tr>";
						echo '<td class="littletableheaderc">Saldo Actual</td>';
						echo "<td width='100' class='littletablerow' align='right'>".nformat($row['saldo'])."</td>";
						echo "<tr></tr>";
						echo '<td class="littletableheaderc">Credito Disponible</td>';
						echo "<td width='100' class='littletablerow' align='right'>".nformat($form->limite->value-$row['saldo'])."</td>";
						echo '</tr></table>';
					}
					?>
					</td>
				</tr>				
			</table>
		</td>
	</tr>				
</table>
<?php } else {
echo "<center><h1>Informacion no Disponible en este momento </h1><center>";
}; ?>
</fieldset>
        </div>
    </div>
</div>


<?php echo $container_bl.$container_br; ?>
<?php echo $form_end?>
<?php endif; ?>