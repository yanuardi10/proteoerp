<?php
include('metodos.php');

class Generar extends Metodos {

	function Generar(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->modulo=602;
	}

	function index() {
		$this->datasis->modulo_id($this->modulo,1);
		$this->rapyd->load("datagrid","dataform","fields");

		$control=$this->uri->segment(4);
		$checkbox =  '<input type="checkbox" name="genera[]" value="<#modulo#>" CHECKED>';

		$grid = new DataGrid('Seleccione los m&oacute;dulos que desea generar');
		$grid->db->select('modulo, descripcion');
		$grid->db->from('`reglascont`');
		$grid->db->groupby('modulo');
		$grid->db->orderby('modulo,regla');

		$grid->column("M&oacute;dulo"     , "modulo"     );
		$grid->column("Descripci&oacute;n", "descripcion");
		$grid->column('Generar'    , $checkbox,'align="center"');
		$grid->build();

		$form = new DataForm('contabilidad/generar/procesar');
		$form->title('Rango de fecha para la Generaci&oacute;n');
		$form->fechai = new dateonlyField("Fecha Desde", "fechai","d/m/Y");
		$form->fechaf = new dateonlyField("Fecha Hasta", "fechaf","d/m/Y");
		$form->fechaf->size = $form->fechai->size=10;

		$form->fechai->insertValue =($this->input->post('fechai') ? $this->input->post('fechai') : date("Ymd"));
		$form->fechaf->insertValue =($this->input->post('fechaf') ? $this->input->post('fechaf') : date("Ymd"));
		$form->tabla= new containerField('tabla',$grid->output);
		if ($control) $form->control= new containerField('control','Contabilidad Generada');
		//$form->submit("btn_submit","Generar Depurado");
		$form->build_form();

		$data['script']="<script type='text/javascript'>
		var handlerFunc = function(t) {
			document.getElementById('preloader').style.display='none';
			new Effect.Opacity('contenido', {duration:0.5, from:0.3, to:1.0});
			alert(t.responseText);
		}

		var errFunc = function(t) {
			document.getElementById('preloader').style.display='none';
			new Effect.Opacity('contenido', {duration:0.5, from:0.3, to:1.0});
			alert('Error ' + t.status + ' -- ' + t.statusText);
		}

		function generar() {
			new Effect.toggle('preloader', 'appear');
			new Effect.Opacity('contenido', {duration:0.5, from:1.0, to:0.3});
			new Ajax.Request('".site_url('contabilidad/generar/procesar')."',{
			 method: 'post',
			 parameters : Form.serialize('df1'),
			 onSuccess:handlerFunc,
			 onFailure:errFunc});
		}
		</script>";

		$data['extras']="<div id='preloader' style='display: none;	position:absolute; left:40%; top:40%; font-family:Verdana, Arial, Helvetica, sans-serif;'>
			<center>".image("loading4.gif")."<br>".image("loadingBarra.gif")."<br>
			<b>Generando . . . </b>
			</center>
		</div>";
		$data['content'] = $form->output."<input type=button value='Generar' onclick='generar()'>";
		$data["head"]    = $this->rapyd->get_head().script("prototype.js").script("scriptaculous.js").script("effects.js");
		$data['title']   ="<h1>Generar Contabilidad</h1>";
		$this->load->view('view_ventanas', $data);
	}


	function procesar(){
		$this->datasis->modulo_id($this->modulo,1);
		$this->load->library('validation');
		$fechai=$this->input->post('fechai');
		$fechaf=$this->input->post('fechaf');

		$ban=0;
		$ban+=$this->validation->chfecha($fechai);
		$ban+=$this->validation->chfecha($fechaf);
		
		if($ban!=2) {echo 'Error: Fechas erroneas'; return false;}
		
		$qfechai=date("Ymd",timestampFromInputDate($fechai, 'd/m/Y'));
		$qfechaf=date("Ymd",timestampFromInputDate($fechaf, 'd/m/Y'));
		$generar=$this->input->post('genera');

		$salida=$this->_procesar($qfechai,$qfechaf,$generar);
		echo $salida;
		return true;
		//redirect('contabilidad/generar/index/completo');
	}
	
	function procesarshell($qfechai=null,$qfechaf=null,$modulos='APAN|BCAJ|CRUC|GSER|NOMI|OTIN|PRMO|RCAJ|SCST|SFAC|SMOV|SPRM'){
		if(isset($_SERVER['argv']) && !isset($_SERVER['SERVER_NAME'])){ //asegura que se ejecute desde shell
			if(empty($qfechai) OR empty($qfechai) OR strlen($qfechai)+strlen($qfechaf)<16){
				echo "USO: php index.php contabilidad generar procesarshell fecha_inicial fecha_final [modulos]\n";
				echo "  fecha_inicial YYYYDDMM\n";
				echo "  fecha_final   YYYYDDMM\n";
				echo "  modulos APAN|BCAJ|CRUC|GSER|NOMI|OTIN|PRMO|RCAJ|SCST|SFAC|SMOV|SPRM \n";
				return TRUE;
			}

			//$modulos='APAN|BCAJ|CRUC|GSER|NOMI|OTIN|PRMO|RCAJ|SCST|SFAC|SMOV|SPRM';
			preg_match('/(?P<anio>\d{4})(?P<mes>\d{2})(?P<dia>\d{2})/', $qfechai, $matches);
			if(!checkdate($matches['mes'],$matches['dia'],$matches['anio'])) { echo 'Error: fecha inicial invalida'."\n"; return TRUE; }
			preg_match('/(?P<anio>\d{4})(?P<mes>\d{2})(?P<dia>\d{2})/', $qfechaf, $matches);
			if(!checkdate($matches['mes'],$matches['dia'],$matches['anio'])) { echo 'Error: fecha final invalida'."\n"; return TRUE; }
			
			$generar=explode('|',$modulos);
			$salida=$this->_procesar($qfechai,$qfechaf,$generar);
			echo $salida."\n";
			return FALSE;	
		}else{
			show_404();
		}
		return TRUE;
	}


	function _procesar($qfechai,$qfechaf,$generar=FALSE){
		$error=FALSE;
		$DBbig = $this->load->database('default', TRUE);
		//$query=mysql_unbuffered_query($mSQL,$DBbig->conn_id);
		//while ($row = mysql_fetch_assoc($query)) {}

		if($generar){
			foreach ($generar as $modulo){ 
				$query=$this->db->simple_query("DELETE FROM casi   WHERE fecha BETWEEN $qfechai AND $qfechaf AND origen='$modulo'");
				$query=$this->db->simple_query("DELETE FROM itcasi WHERE fecha BETWEEN $qfechai AND $qfechaf AND origen LIKE '$modulo%'");

				$mTABLA  =$this->datasis->dameval("SELECT origen  FROM reglascont WHERE modulo='$modulo' AND regla=1 ");
				$mCONTROL=$this->datasis->dameval("SELECT control FROM reglascont WHERE modulo='$modulo' AND regla=1 ");
				if ($modulo == 'SCST' ) {
					$mSQL="SELECT a.$mCONTROL mgrupo FROM $mTABLA WHERE a.recep BETWEEN $qfechai AND $qfechaf GROUP BY a.$mCONTROL";
				} else {
					$mSQL="SELECT a.$mCONTROL mgrupo FROM $mTABLA WHERE a.fecha BETWEEN $qfechai AND $qfechaf GROUP BY a.$mCONTROL";
				}

				$query=mysql_unbuffered_query($mSQL,$DBbig->conn_id);
				while ($fila = mysql_fetch_assoc($query)) {

					$aregla = $this->_hace_regla($modulo, $mCONTROL, $fila['mgrupo']);
					foreach ($aregla['casi'] as $casi){
						$ejecasi='INSERT IGNORE INTO casi ( comprob, fecha, descrip, origen ) '.$casi;
						$ejec=$this->db->simple_query($ejecasi);
						if($ejec==FALSE){ memowrite($ejecasi,'generar'); $error=true; }
					}
					foreach ($aregla['itcasi'] as $itcasi){
						$ejeitcasi ='INSERT INTO itcasi (fecha, comprob, origen,  cuenta, referen, concepto, debe,  haber, sucursal, ccosto) '.$itcasi;
						$ejec=$this->db->simple_query($ejeitcasi);
						if($ejec==FALSE){ memowrite($ejeitcasi,'generar'); $error=true; }
					}
				}
				$this->_borra_huerfano();
				
				//Redondeo
				//$query=$this->db->query("SELECT comprob,sum(debe)-sum(haber) total, origen FROM itcasi WHERE (MID(comprob,1,2) IN ('VD','DV') OR MID(origen,1,4) IN ('SCOP','SCST')) AND fecha BETWEEN $qfechai AND $qfechaf GROUP BY comprob HAVING abs(total)>0 AND abs(total)<0.5");
				//foreach ($query->result_array() as $row){
				$mSQL="SELECT comprob,sum(debe)-sum(haber) total, origen FROM itcasi WHERE (MID(comprob,1,2) IN ('VD','DV') OR MID(origen,1,4) IN ('SCOP','SCST')) AND fecha BETWEEN $qfechai AND $qfechaf GROUP BY comprob HAVING abs(total)>0 AND abs(total)<0.5";
				$query=mysql_unbuffered_query($mSQL,$DBbig->conn_id);
				while ($row = mysql_fetch_assoc($query)) {
					//print_r($row);
					$mCOMPROB=$row['comprob'];
					$mORIGEN =$row['origen'] ;
					$mTOTAL  =$row['total']  ;
					$this->db->simple_query("UPDATE itcasi SET haber=haber+$mTOTAL WHERE comprob='$mCOMPROB' AND origen='$mORIGEN' ORDER BY haber DESC LIMIT 1 ");
					$this->db->simple_query("UPDATE casi SET debe=(SELECT sum(itcasi.debe) FROM itcasi WHERE casi.comprob=itcasi.comprob AND casi.comprob='$mCOMPROB' AND origen='$mORIGEN' GROUP BY itcasi.comprob) WHERE comprob='$mCOMPROB'");
					$this->db->simple_query("UPDATE casi SET haber=(SELECT sum(itcasi.haber) FROM itcasi WHERE casi.comprob=itcasi.comprob AND casi.comprob='$mCOMPROB' AND origen='$mORIGEN' GROUP BY itcasi.comprob) WHERE comprob='$mCOMPROB' ");
					$this->db->simple_query("UPDATE casi SET total=debe-haber WHERE comprob='$mCOMPROB' AND origen='$mORIGEN'");
				}
			}

			$lgenerar="'".implode("','",$generar)."'";
			$mSQL="SELECT comprob FROM itcasi WHERE fecha BETWEEN $qfechai AND $qfechaf GROUP BY comprob";
			//$query=$this->db->query($mSQL);

			//TOTALIZA EN ITCASI
			$query=mysql_unbuffered_query($mSQL,$DBbig->conn_id);
			while ($row = mysql_fetch_assoc($query)) {
				$usr  =$this->session->userdata('usuario');
				$comprob=$this->db->escape($row['comprob']);
				$sql="UPDATE casi
					SET debe=(SELECT sum(debe) FROM itcasi WHERE itcasi.comprob=casi.comprob),
					haber=(SELECT sum(haber) FROM itcasi WHERE itcasi.comprob=casi.comprob),
					total=(SELECT sum(debe)-sum(haber) FROM itcasi WHERE itcasi.comprob=casi.comprob),
					estampa=NOW(),
					usuario='$usr',
					hora=DATE_FORMAT(NOW(),'%H:%i:%s')
					WHERE comprob=$comprob ";
				//$ejec=$this->db->simple_query($sql,array($row['comprob']));
				$ejec=$this->db->simple_query($sql);
				if($ejec==FALSE){ memowrite($sql,'generar'); $error=true; }
			}

			if($error)
				$salida=utf8_encode('Hubo algunos errores en el proceso, se genero un centinela');
			else{
				$descuadre=$this->datasis->dameval("SELECT COUNT(*) FROM casi WHERE fecha BETWEEN $qfechai AND $qfechaf AND total<>0");
				$salida=utf8_encode('Listo! Asientos descuadrados: '.$descuadre);
			}
		}else{
			$salida=utf8_encode('Seleccione al menos un Modulo');
		}
		return $salida;
	}


/*
	function _procesar(){
		$fechai=$this->input->post('fechai');
		$fechaf=$this->input->post('fechaf');
		$qfechai=date("Ymd",timestampFromInputDate($fechai, 'd/m/Y'));
		$qfechaf=date("Ymd",timestampFromInputDate($fechaf, 'd/m/Y'));
		$generar=$this->input->post('genera');
		$error=FALSE;

		//$DBbig = $this->load->database('default', TRUE);
		//$query=mysql_unbuffered_query($mSQL,$DBbig->conn_id);
		//while ($row = mysql_fetch_assoc($query)) {}

		if($generar){
			foreach ($generar as $modulo){
				$query=$this->db->simple_query("DELETE FROM casi   WHERE fecha BETWEEN $qfechai AND $qfechaf AND origen='$modulo'");
				$query=$this->db->simple_query("DELETE FROM itcasi WHERE fecha BETWEEN $qfechai AND $qfechaf AND origen LIKE '$modulo%'");

				$mTABLA  =$this->datasis->dameval("SELECT origen  FROM reglascont WHERE modulo='$modulo' AND regla=1 ");
				$mCONTROL=$this->datasis->dameval("SELECT control FROM reglascont WHERE modulo='$modulo' AND regla=1 ");
				if ($modulo == 'SCST' ) {
					$mSQL="SELECT a.$mCONTROL mgrupo FROM $mTABLA WHERE a.recep BETWEEN $qfechai AND $qfechaf GROUP BY a.$mCONTROL";
				} else {
					$mSQL="SELECT a.$mCONTROL mgrupo FROM $mTABLA WHERE a.fecha BETWEEN $qfechai AND $qfechaf GROUP BY a.$mCONTROL";
				}
				$query=$this->db->query($mSQL);
				foreach ($query->result_array() as $fila){
					$aregla = $this->_hace_regla($modulo, $mCONTROL, $fila['mgrupo']);
					foreach ($aregla['casi'] as $casi){
						$ejecasi='INSERT IGNORE INTO casi ( comprob, fecha, descrip, origen ) '.$casi;
						$ejec=$this->db->simple_query($ejecasi);
						if($ejec==FALSE){ memowrite($ejecasi,'generar'); $error=true; }
					}
					foreach ($aregla['itcasi'] as $itcasi){
						$ejeitcasi ='INSERT INTO itcasi (fecha, comprob, origen,  cuenta, referen, concepto, debe,  haber, sucursal, ccosto) '.$itcasi;
						$ejec=$this->db->simple_query($ejeitcasi);
						if($ejec==FALSE){ memowrite($ejeitcasi,'generar'); $error=true; }
					}
				}
				$this->_borra_huerfano();

				//Redondeo
				$query=$this->db->query("SELECT comprob,sum(debe)-sum(haber) total, origen FROM itcasi WHERE (MID(comprob,1,2) IN ('VD','DV') OR MID(origen,1,4) IN ('SCOP','SCST')) AND fecha BETWEEN $qfechai AND $qfechaf GROUP BY comprob HAVING abs(total)>0 AND abs(total)<0.5");
				foreach ($query->result_array() as $row){
					$mCOMPROB=$row['comprob'];
					$mORIGEN =$row['origen'] ;
					$mTOTAL  =$row['total']  ;
					$this->db->simple_query("UPDATE itcasi SET haber=haber+$mTOTAL WHERE comprob='$mCOMPROB' AND origen='$mORIGEN' ORDER BY haber DESC LIMIT 1 ");
					$this->db->simple_query("UPDATE casi SET debe=(SELECT sum(itcasi.debe) FROM itcasi WHERE casi.comprob=itcasi.comprob AND casi.comprob='$mCOMPROB' AND origen='$mORIGEN' GROUP BY itcasi.comprob) WHERE comprob='$mCOMPROB'");
					$this->db->simple_query("UPDATE casi SET haber=(SELECT sum(itcasi.haber) FROM itcasi WHERE casi.comprob=itcasi.comprob AND casi.comprob='$mCOMPROB' AND origen='$mORIGEN' GROUP BY itcasi.comprob) WHERE comprob='$mCOMPROB' ");
					$this->db->simple_query("UPDATE casi SET total=debe-haber WHERE comprob='$mCOMPROB' AND origen='$mORIGEN'");
				}
			}

			$lgenerar="'".implode("','",$generar)."'";
			$mSQL="SELECT comprob FROM itcasi WHERE fecha BETWEEN $qfechai AND $qfechaf GROUP BY comprob";
			$query=$this->db->query($mSQL);

			//TOTALIZA EN ITCASI
			foreach ($query->result_array() as $row){
				$usr  =$this->session->userdata('usuario');
				$sql=" UPDATE casi
					SET debe=(SELECT sum(debe) FROM itcasi WHERE itcasi.comprob=casi.comprob),
					haber=(SELECT sum(haber) FROM itcasi WHERE itcasi.comprob=casi.comprob),
					total=(SELECT sum(debe)-sum(haber) FROM itcasi WHERE itcasi.comprob=casi.comprob),
					estampa=NOW(),
					usuario='$usr',
					hora=DATE_FORMAT(NOW(),'%H:%i:%s')
					WHERE comprob=? ";
				$ejec=$this->db->simple_query($sql,array($row['comprob']));
				if($ejec==FALSE){ memowrite($sql,'generar'); $error=true; }
			}

			$salida=utf8_encode('Listo!');
		}else{
			$salida=utf8_encode('Seleccione al menos un Modulo');
		}
		return $salida;
	}
*/

	function _borra_huerfano(){
		$guery=$this->db->simple_query("DELETE FROM casi   WHERE comprob='' OR comprob IS NULL");
		$guery=$this->db->simple_query("DELETE FROM itcasi WHERE comprob='' OR comprob IS NULL");
		$guery=$this->db->simple_query("DELETE FROM itcasi USING itcasi LEFT JOIN casi ON itcasi.comprob=casi.comprob AND itcasi.origen like CONCAT(casi.origen,'%') WHERE casi.comprob IS NULL");
		$guery=$this->db->simple_query("DELETE FROM casi   USING casi LEFT JOIN itcasi ON itcasi.comprob=casi.comprob AND itcasi.origen like CONCAT(casi.origen,'%') WHERE itcasi.comprob IS NULL");
	}
}
?>
