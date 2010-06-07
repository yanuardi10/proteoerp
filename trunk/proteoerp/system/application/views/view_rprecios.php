	<div class="mainbackground" style="padding:2px;clear:both">
		<div class="alert">
			<div align="center" style="color:#FF0000">
				<table align="center" width="100%"  >
					<tr>
					<td colspan="2" bgcolor="#F9F9F9">
						<table width="95%">                   	
								<tr>
									<td class="littletableheader" id="td_codigo">
										Descripci&oacute;n:
									</td>
									<td class="littletablerow" bgcolor="#FFFFFF">
									    <div align="center"><span style="font-size:36px"><? if(!empty($descrip))echo $descrip?></span></div></td>
								</tr>
								<tr>
									<td class="littletableheader" id="td_codigo">Precio:
									</td>
									<td class="littletablerow" bgcolor="#FFFFFF">
									    <div align="center"><span  style="font-size:68px; font-weight:bold"><? if(!empty($precio1)) echo $precio1." ".$moneda ?></span></div>
									</td>
                </tr>
              </table>
              
					</td>
					</tr>
					<tr>
						<?
							if(!empty($prin)){
						?>
						<td  width="340" bgcolor="#F9F9F9">
							</br>
							<table width="402" align="center">
								<tr>
									<td colspan="3" align="center">
										<a href="/proteoerp/uploads/inventario/Image/<? echo $prin?>" ><img src="/proteoerp/uploads/inventario/Image/<? echo $prin?>" width='150' height="150" /></a>										
									</td>
								</tr>
								<tr>
									<td width="83">&nbsp;
									</td>
									<td width="200" class="littletablerow" >
										<div style="size:10pt"><? echo $comment; ?></div>
									</td>
									<td width="82">&nbsp;
									</td>
								</tr>
							</table>
						</td>
						<?
							}
						?>
						<td width="340" valign="top" align="center" bgcolor="#F9F9F9">
							</br>
							<table width="50%">
								<tr>
									<td class="littletableheader" id="td_codigo">
										Marca:
									</td>
									<td class="littletablerow" bgcolor="#FFFFFF">
										<? if(!empty($marca)) echo $marca?>
									</td>
								</tr>
                                <tr>
									<td class="littletableheader" id="td_codigo">
										C&oacute;digo:
									</td>
									<td class="littletablerow" bgcolor="#FFFFFF">
										<? if(!empty($codigo)) echo $codigo?>
									</td>
								</tr>
                                <tr>
									<td class="littletableheader" id="td_alterno">
										Alterno:
									</td>
									<td class="littletablerow" bgcolor="#FFFFFF">
										<? if(!empty($alterno)) echo $alterno?>
									</td>
								</tr>
                                <tr>
									<td class="littletableheader" id="td_unidad">
										Unidad:
									</td>
									<td class="littletablerow" bgcolor="#FFFFFF">
										<? if(!empty($unidad)) echo $unidad?>
									</td>
								</tr>
								<tr>
									<td class="littletableheader" id="td_codigo">Base:
									</td>
									<td class="littletablerow" bgcolor="#FFFFFF">
										<? if(!empty($base1)) echo $base1." ".$moneda?>
									</td>
								</tr>
								<tr>
									<td class="littletableheader" id="td_codigo">
										Iva:
									</td>
									<td class="littletablerow" bgcolor="#FFFFFF">
										<?
											$p='';
											if(!empty($iva2)) $p= $iva2." ";
											if(!empty($moneda))$p.=$moneda." ";
											if(!empty($iva))$p.=$iva."%"; 
											echo $p;		
										?>
									</td>
								</tr>
                                <tr>
									<td class="littletableheader" id="td_codigo">
										Existencia
									</td>
									<td class="littletablerow" bgcolor="#FFFFFF">
										<? if(!empty($existen)) echo $existen; ?>
									</td>
								</tr>
							</table>
						</td>
						</tr>
						<tr>
						<td colspan="2" bgcolor="#F9F9F9"><?php if (isset($lista)) echo $lista; ?>
						<?php if (isset($content)) echo $content;?>	</td>
					</tr>
				</table>
			</div>
		</div>
	</div>