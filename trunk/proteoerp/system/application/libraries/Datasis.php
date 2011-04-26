<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * DataSIS Components
 *
 * @author		Andres Hocevar
 * @version		0.1
 * @filesource
 **/

class Datasis {
	// TRAE EL PRIMER CAMPO DEL PRIMER REGISTRO DE LA CONSULTA
	function dameval($mpara,$data=array()){
		$CI =& get_instance();
		$qq = $CI->db->query($mpara,$data);
		$rr = $qq->row_array();
		$aa = each($rr);
		return $aa[1];
	}

	function damerow($mSQL,$data=array()){
		$CI =& get_instance();
		$query = $CI->db->query($mSQL,$data);
		$row=array();
		if ($query->num_rows() > 0)
			$row = $query->row_array();
		return $row;
	}

	// Tae valor de la table VALORES
	function traevalor($nombre){
		$CI =& get_instance();
		$CI->db->query("INSERT IGNORE INTO valores SET nombre='$nombre'");
		$qq = $CI->db->query("SELECT valor FROM valores WHERE nombre='$nombre'");
		$rr = $qq->row_array();
		$aa = each($rr);
		return $aa[1];
	}

	// Pone un valor en la tabla Valores
	function ponevalor($nombre, $mvalor){
		$CI =& get_instance();
		$CI->db->simple_query("REPLACE INTO valores SET nombre='$nombre', valor=".$CI->db->escape($mvalor));
	}


	function prox_sql($mcontador){
		$aa=$this->prox_numero($mcontador,'caja');
		return $aa;
	}

	function existetabla($tabla){
		$CI =& get_instance();
		return $CI->db->table_exists($tabla);
	}

	function adia(){
		$dias = array();
		for($i=1;$i<=31;$i++) {
			$ind=str_pad($i, 2, '0', STR_PAD_LEFT);
			$dias[$ind]=$ind;
		}
		return $dias;
	}

	function ames(){
		$mes = array();
		for($i=1;$i<=31;$i++){
			$ind=str_pad($i, 2, '0', STR_PAD_LEFT);
			$mes[$ind]=$ind;
		}
		return $mes;
	}

	function aano(){
		$ano  = array('2004'=>'2004','2005'=>'2005','2006'=>'2006','2007'=>'2007','2008'=>'2008','2009'=>'2009','2010'=>'2010');
		return $ano;
	}

	function agregacol($tabla,$columna,$tipo){
		$CI =& get_instance();
		$existe  = $CI->db->query("DESCRIBE $tabla $columna");
		if ( $existe->num_rows() == 0  ) 
			$CI->db->query("ALTER TABLE $tabla ADD COLUMN $columna $tipo");
	}

	function login(){
		$CI =& get_instance();
		return $CI->session->userdata('logged_in');
	}

	function essuper(){
		$CI =& get_instance();
		$CI->load->database('default',TRUE);
		if ($CI->session->userdata('logged_in')){
			$usuario = $CI->session->userdata['usuario'];
			// Prueba si es supervisor
			$existe = $CI->datasis->dameval("SELECT COUNT(*) FROM usuario WHERE us_codigo='$usuario' AND supervisor='S'");
			if ($existe > 0)
				return  true;
		}
		return false;
	}

	function puede($id){
		$CI =& get_instance();
		$CI->load->database('default',TRUE);
		if ($CI->session->userdata('logged_in')){
			$usuario = $CI->session->userdata['usuario'];
			//$existe = $CI->datasis->dameval("SELECT COUNT(*) FROM intrasida WHERE usuario='$usuario' AND id='$id'");   //Tortuga
			$existe = $CI->datasis->dameval("SELECT COUNT(*) FROM intrasida WHERE usuario='$usuario' AND modulo='$id'"); //Proteo
			if ($existe  > 0 )
				return  true;
		}
		return false;
	}

	function calendario($forma,$nombre){
		return "<input type=\"text\" name=\"$nombre\" /><a href=\"#\" onclick=\"return getCalendar(document.$forma.$nombre);\"/><img src='calendar.png' border='0' /></a>";
	}

	function jscalendario(){
		return "<script language=\"Javascript\" src=\"calendar.js\"></script>";
	}

	//Identifica el modulo y controla el acceso
	function modulo_id($modulo,$ventana=0){
		if ($this->essuper()) return true;
		$CI =& get_instance();
		$CI->load->database('default',TRUE);
		$CI->session->set_userdata('last_activity', time());
		if($CI->session->userdata('logged_in')){
			$usr=$CI->session->userdata('usuario');
			//$mSQL   = "SELECT COUNT(*) FROM intrasida WHERE id = '$modulo' AND  usuario='$usr' AND acceso='S'"; //Tortuga
			$mSQL   = "SELECT COUNT(*) FROM intrasida WHERE modulo = '$modulo' AND  usuario='$usr' AND acceso='S'";   //Proteo
			$cursor = $CI->db->query($mSQL);
			$rr    = $cursor->row_array();
			$sal   = each($rr);
			if ($sal[1] > 0)
				return true;
		}
		$CI->session->set_userdata('estaba', $CI->uri->uri_string());
		if($ventana)
			redirect('/bienvenido/ingresarVentana');
		else
			redirect('/bienvenido/ingresar');
	}

	//Convierte una consulta a un array
	function consularray($mSQL){
		$bote = array();
		$ncampo = array();
		$CI =& get_instance();
		$mc = $CI->db->query($mSQL);
		foreach ($mc->list_fields() as $field)
	 		array_push($ncampo, $field);
		if ($mc->num_rows() > 0){
			foreach( $mc->result_array() as $row )
				$bote[$row[$ncampo[0]]]=$row[$ncampo[1]];
		}
		return $bote;
	}

	function form2uri($clase,$metodo,$parametros){
		$out='';
		if (is_array($parametros)){
			foreach ($parametros as $value) {
	  		$out .= "+this.form.$value.value+'/'";
			}
		}else
			$out="+this.form.$parametros.value+'/'";
		$out="'".base_url()."$clase/$metodo/'$out";
		return (" location.href=$out;");
	}

	function ivaplica($mfecha=NULL){
		if(empty($mfecha)) $mfecha=date('Ymd');
		$CI =& get_instance();
		$qq = $CI->db->query("SELECT tasa, redutasa, sobretasa FROM civa WHERE fecha < '$mfecha' ORDER BY fecha DESC LIMIT 1");
		$rr = $qq->row_array();
		//$aa = each($rr);
		return $rr;
	}

	function get_uri(){
		$CI =& get_instance();
		$arr=array('formatos','reportes');
		if(in_array($CI->router->fetch_class(),$arr))
			$uri=$CI->router->fetch_directory().$CI->router->fetch_class().'/'.$CI->router->fetch_method().'/'.$CI->uri->segment(3);
		else
			$uri=$CI->router->fetch_directory().$CI->router->fetch_class().'/'.$CI->router->fetch_method();
		return $uri;
	}

	function modbus($modbus,$id='',$width=800,$height=600,$puri=''){
		$CI =& get_instance();
		$uri=$this->get_uri();
		//$uri  =$CI->uri->uri_string();
		$tabla=$modbus['tabla'];
		$parametros=serialize($modbus);

		$data=array();
		if (empty($id)) $id=$modbus['tabla'];

		$idt=$this->dameval("SELECT id FROM modbus WHERE idm='$id' AND uri='$uri'");
		if (!empty($idt)){
			$mSQL="UPDATE modbus SET parametros = '$parametros' WHERE idm='$id' AND uri='$uri'";
			$CI->db->query($mSQL);
		}else{
			$CI->db->set('uri', $uri);
			$CI->db->set('idm', $id);
			$CI->db->set('parametros', serialize($modbus));
			$CI->db->insert('modbus');
			$idt=$CI->db->insert_id();
		}

		return(
"<a href='javascript:void(0);'
onclick=\"vent=window.open(
	'".site_url("buscar/index/$idt/$puri")."',
	'ventbuscar$id',
	'width=$width, height=$height,	scrollbars=Yes,	status=Yes,	resizable=Yes,	screenx=5,	screeny=5'
	);
	vent.focus();
document.body.setAttribute(
	'onUnload',
	'vent.close();'
);\">".image('system-search.png',$modbus['titulo'],array('border'=>'0','height'=>'16px')).'</a>');
	}

	function p_modbus($modbus,$puri='',$width=800,$height=600){
		$CI =& get_instance();
		//$uri  =$CI->uri->uri_string();
		$uri=$this->get_uri();
		$tabla=$modbus['tabla'];
		$parametros=serialize($modbus);

		$data=array();
		$id=$modbus['tabla'];

		$idt=$this->dameval("SELECT id FROM modbus WHERE idm='$id' AND uri='$uri'");
		if (!empty($idt)){
			$mSQL="UPDATE modbus SET parametros = '$parametros' WHERE idm='$id' AND uri='$uri'";
			$CI->db->query($mSQL);
		}else{
			$CI->db->set('uri', $uri);
			$CI->db->set('idm', $id);
			$CI->db->set('parametros', serialize($modbus));
			$CI->db->insert('modbus');
			$idt=$CI->db->insert_id();
		}
		return(
"<a
	href='javascript:void(0);'
	onclick=\"
		vent=window.open(
			'".site_url("buscar/index/$idt/$puri")."',
			'ventbuscar$id',
			'width=$width,height=$height,scrollbars=Yes,status=Yes,resizable=Yes,screenx=5,screeny=5'
		);
		vent.focus();
		document.body.setAttribute(
			'onUnload',
			'if(typeof(vent)==\'object\') vent.close();'
		);
		
	\"
>".image('system-search.png',$modbus['titulo'],array('border'=>'0')).'</a>');
		//return("<a href='javascript:void(0);' onclick=\"vent=window.open('".site_url("buscar/index/$idt/$puri")."','ventbuscar$id','width=$width,height=$height,scrollbars=Yes,status=Yes,resizable=Yes,screenx=5,screeny=5'); vent.focus();\">".image('system-search.png',$modbus['titulo'],array('border'=>'0')).'</a>');
	}

	function periodo($mTIPO, $mFECHA ) {
		$perido=array(1 =>$mFECHA);
		$mFECHA=explode('-',$mFECHA);

		switch ($mTIPO) {
			case 'S':
				$perido[0]=date("Y-m-d",mktime(0, 0, 0, $mFECHA[1], $mFECHA[2]-7, $mFECHA[0]));
				break;
			case 'B':
				$perido[0]=date("Y-m-d",mktime(0, 0, 0, $mFECHA[1], $mFECHA[2]-13, $mFECHA[0]));
				break;
			case 'Q':
				if ($mFECHA[1]>15)
					$perido[0]=date("Y-m-d",mktime(0, 0, 0, $mFECHA[1], 16, $mFECHA[0]));
				else
					$perido[0]=date("Y-m-d",mktime(0, 0, 0, $mFECHA[1], 1, $mFECHA[0]));
				break;
			case 'M':
				$perido[0]=date("Y-m-d",mktime(0, 0, 0, date("m"), 1, date("Y")));
				break;
			default:
				$perido[0]=$perido[1];
		}
		return $perido;
	}

	//niveles de cpla
	function nivel(){
		$formato=$this->dameval('SELECT formato FROM cemp LIMIT 1');
		$formato=explode('.',$formato);
		return count($formato);
	}

	function formato_cpla(){
		$formato=$this->dameval('SELECT formato FROM cemp LIMIT 0,1');
		$qformato='%';
		for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
		return $qformato;
	}

	function prox_numero($mcontador,$usr=NULL){
		$CI =& get_instance();
		if (empty($usr))
			$usr=$CI->session->userdata('usuario');
		if(!$CI->db->table_exists($mcontador))
			$CI->db->simple_query("CREATE TABLE $mcontador (
			`numero` INT(11) NOT NULL AUTO_INCREMENT,
			`usuario` CHAR(10) NULL DEFAULT NULL,
			`fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`numero`))");

		$CI->db->query("INSERT INTO $mcontador VALUES(null, '$usr', now() )");
		$aa = $CI->db->insert_id();
		return $aa;
	}

	function fprox_numero($mcontador,$long=8){
		$numero=$this->prox_numero($mcontador);
		return str_pad($numero, $long, "0", STR_PAD_LEFT);
	}

	function banprox($codban){
		$CI =& get_instance();
		$dbcodban=$CI->db->escape($codban);
		$tipo=$this->dameval("SELECT tbanco FROM banc WHERE codbanc=$dbcodban");
		if($tipo=='CAJ'){
			$nom='nBAN'.$codban;
			while(1){
				$numero=$this->fprox_numero($nom,12);
				$dbnumero=$CI->db->escape($numero);
				$mSQL = "SELECT COUNT(*) AS n FROM bmov WHERE numero=$dbnumero";
				$query= $CI->db->query($mSQL);
				$row  = $query->first_row('array');
				if($row['n']==0) break;
			}
			return $numero;
		}
		return false;
	}

	// GUARDA DATOS DE SESION EN MYSQL
	function guardasesion($datos){
		$CI =& get_instance();

		$mSQL = "CREATE TABLE IF NOT EXISTS data_sesion (
			id INT(11) NULL AUTO_INCREMENT,
			sesionid VARCHAR(40) NULL,
			data1 TEXT NULL, data2 TEXT NULL, data3 TEXT NULL, data4 TEXT NULL,
			fecha TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id), UNIQUE INDEX sesion (sesionid)
			)COLLATE='lotin1' ENGINE=MyISAM ";
		$CI->db->simple_query($mSQL);
		
		$id = $CI->session->userdata('session_id');
		
		$mSQL = $CI->db->insert_string("data_sesion", array("sesionid"=>$id));
		$CI->db->simple_query($mSQL);

		$mSQL = $CI->db->update_string('data_sesion', $datos, "sesionid='$id'");
		$CI->db->simple_query($mSQL);
		return $this->dameval("SELECT id FROM data_sesion WHERE sesionid='$id'");
		
		
	}

	// GUARDA DATOS DE SESION EN MYSQL
	function damesesion($id){
		$CI =& get_instance();

		$mSQL = "CREATE TABLE IF NOT EXISTS data_sesion (
			id INT(11) NULL AUTO_INCREMENT,
			sesionid VARCHAR(40) NULL,
			data1 TEXT NULL, data2 TEXT NULL, data3 TEXT NULL, data4 TEXT NULL,
			fecha TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id), UNIQUE INDEX sesion (sesionid)
			)COLLATE='lotin1' ENGINE=MyISAM ";
			
		$CI->db->simple_query($mSQL);
		
		//$id = $CI->session->userdata('session_id');
		$mSQL = "SELECT data1, data2, data3, data4 FROM data_sesion WHERE id='$id'";
		$query = $CI->db->query($mSQL);
		return $query->row_array();
	
		
	}



}