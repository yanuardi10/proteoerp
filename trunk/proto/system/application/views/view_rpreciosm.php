<p>
<table align='center' width='100%' border=0 background=/proteoerp/images/logopm.jpg>
	<table style="background-color:#FFDDDF;" width='100%'>
		<tr>
			<td>
				<table  align="center">
				<tr><td STYLE="font-size:24pt;text-align:center"><strong><?=$descrip ?></strong></td></tr>
				</table>
				
			  <table  align="center">  
				<tr>
					<td width="50%"> <strong>Departamento: </strong>    <?=$depto ?> </td>	
				   <td width="50%"><strong>Familia:      </strong>    <?=$familia?></td>
				</tr>			            
				<tr>               
					<td width="50%"> <strong>Grupo:        </strong>    <?=$grupo ?> </td>
					<td width="50%"> <strong>Marca:        </strong>    <?=$marca ?> </td>
				</tr>
				</table>
			
				<table  align="center" cellspan="2">
				<tr>
				  <td width="50%"><strong>C&oacute;digo:       </strong> <?=$codigo ?></td>
					<td width="50%"><strong>C&oacute;digo Barras:</strong> <?=$barras ?></td>
				</tr> 
				<tr>  
					<td width="50%"><strong>Referencia:          </strong> <?=$referen ?></td>
					<td width="50%"><strong>Existencia:          </strong> <?=$existen ?></td>
				</tr>	
				</table>
			</td>
		</tr>
	</table>
	
	<table width="100%" align="center" style="background-color:#FFFADA; font-size:18pt;">
		<tr align="center"><th>&nbsp;</th><th>Base </th><th>Precio  </th><th>Concepto</th></tr>
		<tr align="right"><td><strong>1:</strong></td> <td><?=$base1 ?>Bs.</td><td><?=$precio1 ?>Bs.</td><td align="left">* Precio Oferta</td></tr>
		<tr align="right"><td><strong>2:</strong></td> <td><?=$base2 ?>Bs.</td><td><?=$precio2 ?>Bs.</td><td align="left">* Cliente Preferencial</td></tr>
		<tr align="right"><td><strong>3:</strong></td> <td><?=$base3 ?>Bs.</td><td><?=$precio3 ?>Bs.</td><td align="left">* M&aacute;s de <?=$dvolum1 ?> unidades</b></td></tr>
		<tr align="right"><td><strong>4:</strong></td> <td><?=$base4 ?>Bs.</td><td><?=$precio4 ?>Bs.</td><td align="left">* M&aacute;s de <?=$dvolum2 ?> unidades</b></td></tr>	
	</table>

	<table width="100%">
		<tr>
			<td valign="top" align="right" width="30%">
				<table style="background-color:#E5E5E5" width="100%">
					<tr><th>Presentaci&oacute;n </th></tr>
					<tr><td><?=$fracxuni ?> <strong>X</strong> <?=$dempaq ?> <strong>=</strong> <?=$mempaq ?>               </td></tr>
					<tr><td><strong>Ensamblado:</strong> <?=($ensambla='S')?'SI':($ensambla='N')?'NO':'' ?></td></tr>
					<tr><td><strong>Des/Epq:   </strong>                                                   </td></tr>
					<tr><td>                              <?=$empaque ?>                                   </td></tr>
				</table>
			</td>
		
			<td valign="top" align="center" width="20%">
				<table  style="background-color:#E5E5E5" width="100%">
					<tr align="right"><strong>Tama&ntilde;o: </strong><td><?=$tamano ?></td></tr>
					<tr align="right"><strong>Medida:        </strong><td><?=$medida ?></td></tr>
					<tr align="right"><strong>Serial:        </strong><td><?=$serial ?></td></tr>
					<tr align="right"><strong>Exis. Minima   </strong><td><?=$minimo ?></td></tr>
					<tr align="right"><strong>Exis. Maxima   </strong><td><?=$maximo ?></td></tr>	
				</table>
			</td>
			
			<td valign="top" align="left" width="50%">
				<?
				$mSQL_2  = $this->db->query($almacenes);
				$detalle =$mSQL_2->result();
				?>
				<table width="100%" border='0' style="background-color:#E5DDE5">
				<tr>
				<th>Alma                 </th>
				<th>Sucursal             </th>
				<th><?=$mempaq  ?>       </th>
				<th><?=$dempaq ?>        </th>
				</tr>
				<?php 
				$i=0;
				foreach($detalle AS $items ){$i++;
						?>
				<tr>
				<td><?=$items->ubica?>   </td>
				<td align="center"><?=$items->sucursal?>  </td>
				<td align="right"><?=$items->cantidad?></td>
				<td align="right"><?=$items->fraccion?></td>
				</tr>
				<?php 
				}
				?>
				</table>
			</td>
		</tr>
	</table>