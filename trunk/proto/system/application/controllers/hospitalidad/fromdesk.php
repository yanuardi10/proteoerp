<?php
class Fromdesk extends Controller {
	
	function Fromdesk(){
		parent::Controller();
		$this->load->library("rapyd");
		}
		
	function index(){
		
		redirect("hospitalidad/fromdesk/filteredgrid");
		}

	function _fdpanel($ancho=500,$alto=300){
		$mSQL='SELECT habit ';
		$fdtop='<div style="overflow-x:  hidden;width: '.($ancho-15).'px; align: left" id="fdtop"><table><tr>';
		for($i=0;$i<31;$i++){
			$mSQL.=", sum(IF(fecha_in=ADDDATE(DATE(NOW()), $i), 10, IF(fecha_ou=ADDDATE(DATE(NOW()),$i),30,IF(fecha_in<ADDDATE(DATE(NOW()),$i) AND fecha_ou>=ADDDATE(DATE(NOW()), $i), IF(checkin=0,1+(confirma='S'),50), 0)))) D$i ";
			$date=mktime(0,0,0,date("n"),date("j")+$i,date("Y"));
			$fdtop.= '<td class="mininegro" align=center>'.image('spacer.gif','',array('width'=>'20','height'=>'1')).'<br>'.strftime("%d<br>%m",$date)."</td>\n";
		}
		$fdtop.= '</tr></table></div>';
		$mSQL.=" FROM hres 
			WHERE fecha_in <= ADDDATE(DATE(NOW()), 30) AND fecha_ou >= ADDDATE(DATE(NOW()), 0) AND checkout=0
			GROUP BY habit";
		$frontdesk='<div style="overflow: auto;width: '.$ancho.'px; height: '.$alto.'px; align: left" id="frontdesk" name="frontdesk" OnScroll="hacer_scroll()"><table  bgcolor=#E4E4E4>';
		$fdleft='<div style="overflow:  hidden;width: 40px; height: '.($alto-15).'px; align: right" id="fdleft"><table>';
		$cursor = $this->db->query($mSQL);
		foreach( $cursor->result() as $row ){
			$frontdesk.='<tr>'; $i=0;
			foreach( $row  as $sal ){
				if ($i==0){
					$fdleft.="<tr><td>&nbsp;<b class='mininegro'>$sal</b></td></tr>\n";
				}else{
					$frontdesk.='<td bgcolor="';
					if     ($sal==0 ) $frontdesk.='#FFFFFF" ';//VACIO
					elseif ($sal==1 ) $frontdesk.='#808000" ';//RESERVADA
					elseif ($sal==2 ) $frontdesk.='#008000" ';//RESERVACION CON DEPOSITO
					elseif ($sal==10) $frontdesk.='#B5B547" ';//ENTRADA
					elseif ($sal==30) $frontdesk.='#B5B547" ';//SALIDA
					elseif ($sal==40) $frontdesk.='#800000" ';//ENTRADA SALIDA
					elseif ($sal==50) $frontdesk.='#000000" ';//OCUPADO
					$frontdesk.=">".image('spacer.gif','',array('width'=>'20','height'=>'1','align'=>'CENTER'))."</td>";
				}$i++;
			}$frontdesk.="</tr>\n";
		}$frontdesk.='</table></div>'; $fdleft.='</table></div>';

		$script="<SCRIPT LANGUAGE=JAVASCRIPT>
		function hacer_scroll() {
		document.getElementById('fdleft').scrollTop=document.getElementById('frontdesk').scrollTop;
		document.getElementById('fdtop').scrollLeft=document.getElementById('frontdesk').scrollLeft
		}</SCRIPT>";
		
		$panel="$script <table>
			<tr>
				<td>&nbsp; </td>
				<td valign=top align=left >$fdtop</td>
			</tr>
			<tr>
				<td valign=top align=right>$fdleft</td>
				<td valign=top align=left>$frontdesk</td>
			</tr>
		</table>";
		return($panel);
	}
}
?>
