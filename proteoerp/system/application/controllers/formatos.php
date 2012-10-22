<?php
class Formatos extends Controller{

	var $_direccion;
	function Formatos(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->plugin('numletra');
		$this->load->helper('string');
	}

	function index(){
		
	}

	function ver1(){
		$parametros= func_get_args();
		$this->_direccion='http://localhost/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_arch_nombre=implode('-',$parametros);
			$_fnombre=array_shift($parametros);
			$_dbfnombre=$this->db->escape($_fnombre);
			$this->load->plugin('html2pdf');
			$query = $this->db->query('SELECT proteo FROM formatos WHERE nombre='.$_dbfnombre);
			if ($query->num_rows() > 0){
				$row = $query->row();
				ob_start();
					echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
					$_html=ob_get_contents();
				@ob_end_clean();
				if(strlen($_html)>0)
					pdf_create($_html, $_arch_nombre);
				else
					echo 'Formato no definido';
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function ver(){
		$parametros= func_get_args();
		$this->_direccion='http://localhost/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_arch_nombre=implode('-',$parametros);
			$_fnombre=array_shift($parametros);
			$_dbfnombre=$this->db->escape($_fnombre);
			$this->load->library('dompdf/cidompdf');
			$query = $this->db->query('SELECT proteo FROM formatos WHERE nombre='.$_dbfnombre);
			if ($query->num_rows() > 0){
				$row = $query->row();
				ob_start();
					echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
					$_html=ob_get_contents();
				@ob_end_clean();
				if(strlen($_html)>0)
					$this->cidompdf->html2pdf($_html,$_arch_nombre);
				else
					echo 'Formato no definido';
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function descargar(){
		$parametros= func_get_args();
		$this->_direccion='http://localhost/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_arch_nombre=implode('-',$parametros);
			$_fnombre=array_shift($parametros);
			$_dbfnombre=$this->db->escape($_fnombre);
			$this->load->library('dompdf/cidompdf');
			$query = $this->db->query('SELECT proteo FROM formatos WHERE nombre='.$_dbfnombre);
			if ($query->num_rows() > 0){
				$row = $query->row();
				ob_start();
					echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
					$_html=ob_get_contents();
				@ob_end_clean();
				if(strlen($_html)>0)
					$this->cidompdf->html2pdf($_html,$_arch_nombre,true);
				else
					echo 'Formato no definido';
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function verhtml(){
		$parametros= func_get_args();
		$this->_direccion='/'.trim_slashes($this->config->item('base_url'));
		if (count($parametros)>0){
			$_fnombre  = array_shift($parametros);
			$_dbfnombre= $this->db->escape($_fnombre);
			$query = $this->db->query('SELECT proteo FROM formatos WHERE nombre='.$_dbfnombre);
			if ($query->num_rows() > 0){
				$row = $query->row();
				eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', $row->proteo)).'<?php ');
			}else{
				echo 'Formato no existe';
			}
		}else{
			echo 'Faltan parametros';
		}
	}

	function cintillo($cinti = 'CINTILLO'){
		// Cintillo por defecto si no existe 
		$mhtml = "\n";
		$mhtml .= '			<!-- CINTILLO -->'."\n";
		$mhtml .= '			<div id="section_header">'."\n";
		$mhtml .= '				<table style="width: 100%;" >'."\n";
		$mhtml .= '					<tr>'."\n";
		$mhtml .= '						<td width=140 rowspan="3"><img src="\'.$this->_direccion.\'/images/logo.jpg" width="127"></td>'."\n";
		$mhtml .= '						<td><span style="text-align:left;font-size:1.4em;font-style:italic;font-weight: bold;">\'.trim($this->datasis->traevalor("TITULO1")).\'</span></td>'."\n";
		$mhtml .= '					</tr><tr>'."\n";
		$mhtml .= '						<td style="font-size: 8pt"><b>RIF: \'.$this->datasis->traevalor("RIF").\'</b></td>'."\n";
		$mhtml .= '					</tr><tr>'."\n";
		$mhtml .= '						<td><div style="font-size: 8pt">\'.trim($this->datasis->traevalor("TITULO2")).\' \'.trim($this->datasis->traevalor("TITULO3")).\'<div></td>'."\n";
		$mhtml .= '					</tr>'."\n";
		$mhtml .= '				</table>'."\n";
		$mhtml .= '			</div>'."\n";
		$mhtml .= '			<!-- FIN CINTILLO -->'."\n";

		if ( $this->datasis->dameval('SELECT COUNT(*) FROM formatos WHERE nombre="X_'.$cinti.'"') == 0 ){
			$this->db->query('INSERT INTO formatos SET proteo=?, nombre="X_'.$cinti.'"', array($mhtml));
		}
		return $this->traeyeva($cinti);
		
	}

	function scriptphp($script = 'SCRIPTP'){
		$mhtml  = "\n";
		$mhtml .= '<!-- SCRIPT PARA CONTROL DE FORMATO -->'."\n";
		$mhtml .= '<script type="text/php">'."\n";
		$mhtml .= 'if ( isset($pdf) ) {'."\n";
		$mhtml .= '	$font = Font_Metrics::get_font("verdana");;'."\n";
		$mhtml .= '	$size = 6;'."\n";
		$mhtml .= '	$color = array(0,0,0);'."\n";
		$mhtml .= '	$text_height = Font_Metrics::get_font_height($font, $size);'."\n";
		$mhtml .= '	$foot = $pdf->open_object();'."\n";
		$mhtml .= '	$w = $pdf->get_width();'."\n";
		$mhtml .= '	$h = $pdf->get_height();'."\n";
		$mhtml .= '	// Draw a line along the bottom'."\n";
		$mhtml .= '	$y = $h - $text_height - 24;'."\n";
		$mhtml .= '	$pdf->line(16, $y, $w - 16, $y, $color, 0.5);'."\n";
		$mhtml .= '	$pdf->close_object();'."\n";
		$mhtml .= '	$pdf->add_object($foot, "all");'."\n";
		$mhtml .= '	$text = "PP {PAGE_NUM} de {PAGE_COUNT}";  '."\n";
		$mhtml .= '	// Center the text'."\n";
		$mhtml .= '	$width = Font_Metrics::get_text_width("PP 1 de 2", $font, $size);'."\n";
		$mhtml .= '	$pdf->page_text($w / 2 - $width / 2, $y, $text, $font, $size, $color);'."\n";
		$mhtml .= '}'."\n";
		$mhtml .= '</script>'."\n";
		$mhtml .= '<!-- FIN SCRIPTPHP -->'."\n";

		if ( $this->datasis->dameval('SELECT COUNT(*) FROM formatos WHERE nombre="X_'.$script.'"') == 0 ){
			$this->db->query('INSERT INTO formatos SET proteo=?, nombre="X_'.$script.'"', array($mhtml));
		}
		return $this->traeyeva($script);
	}

	function rephead($rhead = 'REPHEAD', $titulo){
		$mhtml  = "\n";
		$mhtml .= '<html>'."\n";
		$mhtml .= '<head>'."\n";
		$mhtml .= '<title>'.$titulo.'</title>'."\n";
		$mhtml .= '<link rel="STYLESHEET" href="\'.$this->_direccion.\'/assets/default/css/formatos.css" type="text/css" />'."\n";
		$mhtml .= '</head>'."\n";

		if ( $this->datasis->dameval('SELECT COUNT(*) FROM formatos WHERE nombre="X_'.$rhead.'"') == 0 ){
			$this->db->query('INSERT INTO formatos SET proteo=?, nombre="X_'.$rhead.'"', array($mhtml));
		}
		return $this->traeyeva($rhead);
	}


	function traeyeva($parte){
		$mhtml =  $this->datasis->dameval('SELECT proteo FROM formatos WHERE nombre="X_'.$parte.'"');
		$salida = '';
		eval("\$salida = '". $mhtml."';");
		return $salida;
	}


	function instalar(){
		$campos=$this->db->list_fields('formatos');
		if(!in_array('proteo'  ,$campos)) $this->db->simple_query("ALTER TABLE `formatos` ADD `proteo` TEXT NULL AFTER `forma`");
		if(!in_array('harbour' ,$campos)) $this->db->simple_query("ALTER TABLE `formatos` ADD `harbour` TEXT NULL AFTER `proteo`");
		if(!in_array('tcpdf'   ,$campos)) $this->db->simple_query("ALTER TABLE `formatos`  ADD COLUMN `tcpdf` TEXT NULL AFTER `forma`");
	}
}
/////////////////////////////////////////////////////////////////
?>