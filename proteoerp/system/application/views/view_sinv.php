<?php 
echo $form_scripts.$form_begin;
$container_tr=join("&nbsp;", $form->_button_container["TR"]);
$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	$meco = $form->output;
	$meco = str_replace('class="tablerow"','class="tablerow" style="font-size:20px; align:center;" ',$meco);
	echo $meco."</td><td align='center'>".img("images/borrar.jpg");
else:
?>

<?php if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>'; ?>
<table border='0' width="100%">
	<tr>
		<td>
			<?php if($form->_status=='show'){ ?>
			<a href='<?php echo base_url()."inventario/sinv/consulta/".$form->_dataobject->get('id'); ?>'>
			<?php
				$propiedad = array('src' => 'images/ojos.png', 'alt' => 'Consultar Movimiento', 'title' => 'Consultar Detalles','border'=>'0','height'=>'30');
				echo img($propiedad);
			?>
			</a>
			
			<a href='javascript:sinvcodigo("<?php echo $form->_dataobject->get('id'); ?>")'>
			<?php
				$propiedad = array('src' => 'images/cambiocodigo.jpg', 'alt' => 'Cambio de Codigo', 'title' => 'Cambio de codigo','border'=>'0','height'=>'30');
				echo img($propiedad);
			?>
			</a>
			
			<?php } else { ?>
			Agregar: 
			<a href="javascript:add_depto();" title="Agregar departamentos"><?php echo image('list_plus.png','Agregar Departamentos',array("border"=>"0","height"=>"12"));?>Deptos</a>
			<a href="javascript:add_linea();" title="Agregar una Linea"><?php echo image('list_plus.png','Agregar Lineas',array("border"=>"0","height"=>"12"));?>Lineas</a>
			<a href="javascript:add_grupo();" title="Agregar un grupo"><?php echo image('list_plus.png','Agregar Grupos',array("border"=>"0","height"=>"12"));?>Grupos</a>
			
			<?php } ?>
		</td>
		<td align='center' valign='middle'>
			<?php  if ($form->activo->value=='N') echo "<div style='font-size:14px;font-weight:bold;background: #B40404;color: #FFFFFF'>***DESACTIVADO***</div>"; ?>&nbsp;
		</td>
		<td align='right'><?php echo $container_tr; ?></td>
	</tr>
</table>
<fieldset style='border: 2px outset #9AC8DA;background: #FFFDE9;'>
<legend class="titulofieldset" >Identificacion del Producto </legend>
<table border='0' width="100%">
	<tr>
		<td colspan='2' valign='top'>
			<table border=0 width="100%">
				<tr>
					<td width="60" class="littletableheaderc"><? echo $form->codigo->label ?></td>
					<?php if( $form->_status == "modify" ) { ?>
					<td class="littletablerow">
					<input readonly value="<?=$form->codigo->output ?>" class='input' size='15' style='background: #F5F6CE;'  /></td>
					<?php } else { ?>
					<td class="littletablerow"><?=$form->codigo->output ?></td>
					<?php } ?>
				</tr>
				<tr>
					<td class='littletableheaderc'>Alterno</td>
					<td class="littletablerow"><?=$form->alterno->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'>Caja</td>
					<td class="littletablerow"><?=$form->enlace->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'>Barras</td>
					<td class="littletablerow"><?=$form->barras->output   ?></td>
				</tr>
			</table>
		</td>
		<td colspan='2' valign='top'>
			<table border=0 width="100%">
				<tr>
					<td class='littletableheaderc'>Descripcion</td>
					<td class="littletablerow"><?=$form->descrip->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'>Adicional</td>
					<td class="littletablerow"><?=$form->descrip2->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->marca->label ?></td>
					<td class="littletablerow"><?=$form->marca->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->modelo->label ?></td>
					<td class="littletablerow"><?=$form->modelo->output   ?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='4'>
			<table width="100%" border='0' style="border-collapse;border:1px dashed">
				<tr>
					<td  valign='top'  align='center'>
						<table border='0' >
							<tr>
								<td width="40" class="littletableheaderc"><?=$form->tipo->label ?></td>
								<td class="littletablerow"><?=$form->tipo->output   ?></td>
							</tr>
						</table>
					</td>
						<td valign='top' align='center'>
						<table border='0' >
							<tr>
								<td class='littletableheaderc'><?=$form->activo->label ?></td>
								<td class="littletablerow"><?=$form->activo->output   ?></td>
							</tr>
						</table>
					</td>
					<td valign='top'  align='center'>
						<table border='0'>
							<tr>
								<td width='50' class="littletableheaderc"><?=$form->iva->label   ?></td>
								<td class="littletablerow" ><?=$form->iva->output ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</fieldset>

<div class="maintabcontainer">
	<ul class="tabs">
		<li><a href="#tab1">Parametros</a></li>
		<li><a href="#tab2">Precios</a></li>
		<li><a href="#tab3">Existencias</a></li>
		<li><a href="#tab4">Movimientos</a></li>
	</ul>
	<div class="tab_container">
	<div id="tab1" class="tab_content" style='background:#eeffff'>

<table width="100%" border='0'>
	<tr>
		<td colspan='2' valign='top'>
			<table border='0' width="100%" style='border-collapse;border: 1px dotted'>
				<tr>
					<td class='littletableheaderc'><?=$form->tdecimal->label ?></td>
					<td class="littletablerow"><?=$form->tdecimal->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->serial->label ?></td>
					<td class="littletablerow"><?=$form->serial->output   ?></td>
				</tr>
				<tr>
					<td width="100" class='littletableheaderc'><?=$form->clave->label ?></td>
					<td class="littletablerow"><?=$form->clave->output   ?></td>
				</tr>
			</table>
		</td>
		<td valign='top' align='center'>
			<table border='0' >
				<tr>
					<td class='littletableheaderc'><?=$form->peso->label ?></td>
					<td class="littletablerow"><?=$form->peso->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->unidad->label ?></td>
					<td class="littletablerow"><?=$form->unidad->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->fracci->label ?></td>
					<td class="littletablerow"><?=$form->fracci->output   ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table width="100%" border='0'>
	<tr>
		<td valign='top' align='left'>
			<table border='0' >
				<tr>
					<td width='100' class='littletableheaderc'><?=$form->depto->label ?></td>
					<td nowrap class="littletablerow"><?=$form->depto->output   ?></td>
				</tr>
				<tr style="height:14px">
					<td class='littletableheaderc'><?=$form->linea->label ?></td>
					<td class="littletablerow" id='td_linea'><?=$form->linea->output?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->grupo->label ?></td>
					<td nowrap class="littletablerow" id='td_grupo'><?=$form->grupo->output   ?></td>
				</tr>
			</table>
		</td>
		<td valign='top'  align='left'>
			<table border='0' >
				<tr>
					<td class='littletableheaderc'><?=$form->clase->label ?></td>
					<td class="littletablerow"><?=$form->clase->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->garantia->label ?></td>
					<td class="littletablerow"><?=$form->garantia->output   ?></td>
				</tr>
				<tr>
					<td class='littletableheaderc'><?=$form->comision->label ?></td>
					<td class="littletablerow"><?=$form->comision->output   ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
        </div>
        <div id="tab2" class="tab_content">
<table width='100%'>
	<tr>
		<td valign='top'>
			<fieldset style='border: 2px outset #B45F04;background: #F8ECE0;'>
			<legend class="titulofieldset" >Costos</legend>
			<table width='100%'>
				<tr>
					<td class="littletableheaderc"><?=$form->pond->label    ?></td>
					<td class="littletablerow" align='right'><?=$form->pond->output   ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->ultimo->label   ?></td>
					<td class="littletablerow" align='right'><?=$form->ultimo->output  ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->standard->label    ?></td>
					<td class="littletablerow" align='right'><?=$form->standard->output   ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->formcal->label ?></td>
					<td class="littletablerow"><?=$form->formcal->output?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->redecen->label ?></td>
					<td class="littletablerow"><?=$form->redecen->output?></td>
				</tr>
			</table>	
			</fieldset>
		</td>
		<td valign='top'>
			<fieldset style='border: 1px outset #B45F04;background: #F8ECE0;'>
			<legend class="titulofieldset" style='font-size:16' >Precios</legend>
			<table width='100%' cellspacing='0'>
				<tr>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Precio</td>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Margen</td>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Base  </td>
					<td class="littletableheader" style='background: #3B240B;color: #FFEEFF;font-weight: bold'>Precio</td>
				</tr>
			  	<tr>
					<td class="littletableheaderc">1</td>
					<td class="littletablerow" align='right'><?=$form->margen1->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base1->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio1->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc">2</td>
					<td class="littletablerow" align='right'><?=$form->margen2->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base2->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio2->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc">3</td>
					<td class="littletablerow" align='right'><?=$form->margen3->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base3->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio3->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc">4</td>
					<td class="littletablerow" align='right'><?=$form->margen4->output ?></td>
					<td class="littletablerow" align='right'><?=$form->base4->output   ?></td>
					<td class="littletablerow" align='right'><?=$form->precio4->output ?></td>
				</tr>
				<tr>
					<td class="littletablerow">&nbsp;</td>
					<td class="littletablerow">&nbsp;</td>
					<td class="littletablerow">&nbsp;</td>
					<td class="littletablerow">&nbsp;</td>
				</tr>
			</table>	
			</fieldset>
		</td>
	</tr>
</table>
        </div>
        <div id="tab3" class="tab_content">
<table width='100%'>
	<tr>
  		<td valign="top">
			<fieldset  style='border: 2px outset #AEB404;background: #FFFBE2;'>
			<legend class="titulofieldset" >Existencias</legend>
			<table width='100%' border=0 >
				<tr>
					<td width='120' class="littletableheaderc"><?=$form->existen->label  ?></td>
					<td class="littletablerow" align='right' ><?=$form->existen->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->exmin->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exmin->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->exmax->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exmax->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->exord->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exord->output ?></td>
				</tr>
				<tr>
					<td class="littletableheaderc"><?=$form->exdes->label  ?></td>
					<td class="littletablerow" align='right'><?=$form->exdes->output ?></td>
				</tr>
			</table>
			</fieldset>
		</td>
		<?php if( !empty($form->almacenes->output)) { ?>
		<td valign="top">
			<fieldset  style='border: 2px outset #AEB404;background: #FFFBE2;'>
			<legend class="titulofieldset" >Almacenes</legend>
			<?php echo $form->almacenes->output ?>
			</fieldset>
		</td>
		<?php } ?>
	</tr>
</table>
        </div>
        <div id="tab4" class="tab_content">
<?php if($form->_status=='show'){ ?>
<table width='100%'>
	<tr>
		<td valign='top'>
			<fieldset  style='border: 2px outset #AEB404;background: #F5F6CE;'>
			<legend class="titulofieldset" >&Uacute;ltimos Movimientos</legend>
			<table width='100%' >
				<tr>
					<td class="littletableheader" style='background:#F5F6CE;color:#112211' >Compras</td>
					<td class="littletableheader" style='background:#F5F6CE;color:#112211' align='right'><?=$form->fechav->label?></td>
					<td class="littletablerow"><?=$form->fechav->output   ?></td>
					
				</tr>
				<tr>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Fecha</td>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Proveedor</td>
					<td class="littletableheader" align='center' style='background: #393B0B;color: #FFEEFF;font-weight: bold'>Precio</td>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:10px'><?=$form->pfecha1->output?></td>
					<td class="littletablerow" style='font-size:10px'><?=$form->proveed1->output?></td>
					<td class="littletablerow" style='font-size:10px' align='right'><?=$form->prepro1->output?></td>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:10px'><?=$form->pfecha2->output?></td>
					<td class="littletablerow" style='font-size:10px'><?=$form->proveed2->output?></td>
					<td class="littletablerow" style='font-size:10px' align='right'><?=$form->prepro2->output?></td>
				</tr>
				<tr>
					<td class="littletablerow" style='font-size:10px;'><?=$form->pfecha3->output?></td>
					<td class="littletablerow" style='font-size:10px;'><?=$form->proveed3->output?></td>
					<td class="littletablerow" style='font-size:10px;' align='right'><?=$form->prepro3->output?></td>
				</tr>
			</table>
			</fieldset>
		</td>
	</tr>
</table>
<?php };?>

<?php if($form->_status=='show'){ ?>
<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
<legend class="titulofieldset" >Promociones</legend>
<table border=0 width='100%'>
	<tr>
		<td valign="top"><?php 
			$margen =  $this->datasis->dameval("SELECT margen FROM grup WHERE grupo='".$form->_dataobject->get('grupo')."'");
			if ($margen > 0 ) {
				echo "Descuento por Grupo ";
				echo $margen."% ";
				echo "Precio ".nformat($form->precio1->value * (100-$margen)/100); 
			} else echo "No tiene descuento por grupo";
			?>
		</td>
		<td valign="top"><?php
	
			$margen =  $this->datasis->dameval("SELECT margen FROM sinvpromo WHERE codigo='".addslashes($form->_dataobject->get('codigo'))."'");
			if ($margen > 0 ) {
			   echo "Descuento por Promocion ".$margen."% ";
			   echo "Precio ".nformat($form->precio1->value * (100-$margen)/100);
			} else echo "No tiene descuento promocional";
			
			?>
		</td>
	</tr>
</table>
</fieldset>
<br/>
<?php
$query = $this->db->query("SELECT suplemen FROM barraspos WHERE codigo='".addslashes($form->_dataobject->get('codigo'))."'");
if ($query->num_rows()>0 ) {
?>

<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
<legend class="titulofieldset" >Codigos de Barras Asociados</legend>
<table width='100%'>
	<tr>
		<?php 
			$m = 1;
			foreach($query->result() as $row ){
				if ( $m > 5 ) {
					echo "</tr><tr>";
					$m = 1;
				}
				echo "<td class='littletablerow'>".$row->suplemen."</td>";
				
				$m += 1; 
			}
			?>
	</tr>
</table>
</fieldset>
<?php }  // rows>0 ?>

<?php
$query = $this->db->query("SELECT CONCAT(codigo,' ', descrip,' ',fracci) producto FROM sinv WHERE MID(tipo,1,1)='F' AND enlace='".addslashes($form->_dataobject->get('codigo'))."'");
if ($query->num_rows()>0 ) {
?>
</fieldset>
<fieldset style='border: 2px outset #8A0808;background: #FFFBE9;'>
<legend class="titulofieldset" >Fracciones</legend>
<table width='100%'>
	<tr>
		<?php 
			$m = 1;
			foreach($query->result() as $row ){
				if ( $m > 5 ) {
					echo "</tr><tr>";
					$m = 1;
				}
				echo "<td class='littletablerow'>".$row->producto."</td>";
				
				$m += 1; 
			}
			?>
	</tr>
</table>
</fieldset>
<?php }  // rows>0 ?>
        </div>
    </div>
</div>

<?php }  //show    ?>
<?php echo $container_bl.$container_br; ?>
<?php echo $form_end?>
<?php endif; ?>