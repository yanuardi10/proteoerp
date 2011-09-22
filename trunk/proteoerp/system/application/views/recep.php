<?php

$container_bl=join("&nbsp;", $form->_button_container["BL"]);
$container_br=join("&nbsp;", $form->_button_container["BR"]);
$container_tr=join("&nbsp;", $form->_button_container["TR"]);

if ($form->_status=='delete' OR $form->_action=='delete'):
	echo $form->output;
else:
$link=site_url('presupuesto/requisicion/getadmin'); 

foreach($form->detail_fields['seri'] AS $ind=>$data)
	$campos[]=$data['field'];
$campos='<tr id="tr_seri_<#i#>"><td class="littletablerow">'.join('</td><td>',$campos).'</td>';
$campos=str_replace("\n",'',$campos);
$campos.=' <td class="littletablerow"><a href=# onclick="del_seri(<#i#>);return false;">'.image('process-stop32.png','#',array("border"=>0)).'</a></td></tr>';
$campos=$form->js_escape($campos);

if(isset($form->error_string))echo '<div class="alert">'.$form->error_string.'</div>';

//echo $form_scripts;

echo $form_begin; 
if($form->_status!='show'){
	?>

	<script language="javascript" type="text/javascript">
	seri_cont=<?=$form->max_rel_count['seri'] ?>;
	apuntador='';
        
        function leer(){
            campo=apuntador.substr(0,9);
            i=apuntador.substr(10,100);
            valor=$("#"+apuntador).val();
            
            if(campo=='it_barras'){
                $.post("<?=site_url('inventario/common/get_cant')?>",{ barras:valor },function(data){
			
                    if(data==1){
			
                        $.post("<?=site_url('inventario/common/get_codigo') ?>",{ barras:valor },function(data){
                            $("#it_codigo_"+i).val(data);
                        });
                        $.post("<?=site_url('inventario/common/get_descrip') ?>",{ barras:valor },function(data){
                            $("#it_descri_"+i).val(data);
                        });
                        $("#it_serial_"+i).focus();
                    }
                    
                    if(data==0){
                        a=0;
                        ii=parseFloat(i)-1;
                        if(ii>=0){
                            codigo=$("#it_codigo_"+ii).val();
                            a=codigo.length;
                        }
                        
                        if(a>0){
                            barras=$("#it_barras_"+ii).val();
                            descri=$("#it_descri_"+ii).val();
                            
                            $("#it_codigo_"+i).val(codigo);
                            $("#it_barras_"+i).val(barras);
                            $("#it_descri_"+i).val(descri);
                            $("#it_serial_"+i).val(valor);
                            
                            add_seri();
                        }else{
                            $("#it_barras_"+i).val('');
                            $("#it_barras_"+i).focus();
                        }
                        //i3=i+1;
                        //foc("it_barras_"+i);
                    }
                    
                    if(data>1){
                        
                    }
                });
            }
            
            if(campo=='it_serial'){
                
                add_seri();
                //ii=1+parseFloat(i);
                //foc("it_barras_"+ii);
            }
        }
        
        
	$(function(){
                $("input[name^='it_']").focus(function(){
                    apuntador=this.name;
                });
		com=false;
		$(document).keydown(function(e){
                if (13 == e.which) {
                        leer();
                        return false;
		}
		if (18 == e.which) {
			com=true;
			return false;
		}
		if(com && (e.which == 61 || e.which == 107)) {
		  add_seri();
		  a=itcasi_cont-1;
		  $("#barras_"+a).focus();
				com=false;
				return false;
			}else if (com && e.which != 16 && e.which == 17){
				com=false;
			}
			return true;
		});
		$(".inputnum").numeric(".");
	});
	
	$(document).ready(function() {
	
	});
	
	function add_seri(){
		var htm = <?=$campos ?>;
		can = seri_cont.toString();
		con = (seri_cont+1).toString();
		htm = htm.replace(/<#i#>/g,can);
		htm = htm.replace(/<#o#>/g,con);
		$("#__UTPL__").before(htm);
		seri_cont=seri_cont+1;
            
                $("input[name^='it_']").focus(function(){
                    apuntador=this.name;    
                });
                
                $("#it_barras_"+can).focus();
                
	}

	function del_seri(id){
		id = id.toString();
                seri_cont=seri_cont-1;
		$('#tr_seri_'+id).remove();
	}
	</script>
	<?php  
	} 
	?>
	<script language="javascript" type="text/javascript">
	
	</script>
<table align='center'width="98%" >
	<tr>
		<td>
			<table width="100%">
				<tr>
					<td align=left>
						&nbsp;
					</td>
					<td align=right>
						<?php echo $container_tr?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%"  style="margin:0;width:100%;">
			<tr>
				<td class="littletablerowth"><?=$form->tipo->label   ?>*&nbsp;</td>
				<td class="littletablerow"  ><?=$form->tipo->output  ?>&nbsp; </td>
				<td class="littletablerowth"><?=$form->fecha->label  ?>*&nbsp;</td>
				<td class="littletablerow"  ><?=$form->fecha->output ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletablerowth"><?=$form->refe->label   ?>&nbsp;</td>
				<td class="littletablerow"  ><?=$form->refe->output  ?>&nbsp; </td>
				<td class="littletablerowth"><?=$form->origen->label  ?>&nbsp;</td>
				<td class="littletablerow"  ><?=$form->origen->output ?>&nbsp;</td>
			</tr>
			<tr>
				<td class="littletablerowth"><?=$form->refe2->label   ?>&nbsp;</td>
				<td class="littletablerow"  ><?=$form->refe2->output  ?>&nbsp; </td>
				<td class="littletablerowth"><?//=$form->origen->label  ?>&nbsp;</td>
				<td class="littletablerow"  ><?//=$form->origen->output ?>&nbsp;</td>
			</tr>
			   <tr>
                                <td class="littletablerowth"><?=$form->clipro->label  ?>*&nbsp;</td>
                                <td class="littletablerow"  ><?=$form->clipro->output ?>&nbsp; </td>
                                <td class="littletablerowth">&nbsp;</td>
                                <td class="littletablerow"  >&nbsp;</td>
			    </tr>
			  <tr>
			    <td class="littletablerowth">         <?=$form->observa->label ?>&nbsp;</td>
			    <td class="littletablerow" colspan=3 ><?=$form->observa->output ?>&nbsp;</td>
			  </tr>

	    	</table >
		<table class="table_detalle" width="100%">
                    <tr>
                        <th bgcolor='#7098D0'>Barras            </th>
                        <th bgcolor='#7098D0'>C&oacute;digo     </th>
                        <th bgcolor='#7098D0'>Descripci&oacute;n</th>
                        <th bgcolor='#7098D0'>Serial            </th>
			<th bgcolor='#7098D0'>Cantidad          </th>
                        <?php if($form->_status!='show') {?>
                        <th class="littletableheaderb">&nbsp;</td>
                        <?php } ?>
                    </tr>
			  <?php 
			for($i=0;$i<$form->max_rel_count['seri'];$i++) {		  		
                            $obj0="itbarras_$i";
                            $obj1="itcodigo_$i";
                            $obj2="itdescri_$i";
                            $obj3="itserial_$i";
			    $obj4="itcant_$i";
                            ?>
                            <tr id='tr_seri_<?=$i ?>'>
                                <td class="littletablerow"              ><?=$form->$obj0->output ?></td>
                                <td class="littletablerow"              ><?=$form->$obj1->output ?></td>
                                <td class="littletablerow"              ><?=$form->$obj2->output ?></td>
                                <td class="littletablerow"              ><?=$form->$obj3->output ?></td>
				<td class="littletablerow" align="right"><?=$form->$obj4->output ?></td>
                                <?php if($form->_status!='show') {?>
                                <td class="littletablerow"><a href=# onclick='del_seri(<?=$i ?>);return false;'><?=image('process-stop32.png','#',array("border"=>0))?></a></td>
                                <?php } ?>
                            </tr>
			  <?php
                          } ?>
			  <tr id='__UTPL__'>
			    <td class="littletablefooterb" align='right' colspan="5">&nbsp;</td>
			    <?php if($form->_status!='show') {?>
			    <td class="littletablefooterb">&nbsp;</td>
			    <?php } ?>
			  </tr>
	    </table>
		<?php echo $form_end     ?>
		<?php echo $container_bl ?>
		<?php echo $container_br ?>
		<td>
	<tr>
<table>
<?php endif; ?>