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


	function damereg($mSQL,$data=array()){
		return $this->damerow($mSQL, $data );
	}


	// Tae valor de la table VALORES
	function traevalor($nombre,$descrip=''){
		$CI =& get_instance();
		$dbnombre=$CI->db->escape($nombre);
		$dbdescri=$CI->db->escape($descrip);
		$CI->db->query("INSERT IGNORE INTO valores SET nombre=$dbnombre, descrip=$dbdescri");
		$qq = $CI->db->query("SELECT valor FROM valores WHERE nombre=$dbnombre");
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
		$aa=$this->prox_numero($mcontador);
		return $aa;
	}

	function existetabla($tabla){
		$CI =& get_instance();
		return $CI->db->table_exists($tabla);
	}

	function istabla($tabla){
		$CI =& get_instance();
		return $CI->db->table_exists($tabla);
	}

	function iscampo($tabla,$campo){
		$CI =& get_instance();
		$aa = $this->dameval("SHOW FIELDS FROM $tabla WHERE Field ='$campo'");
		if ($aa==$campo) return true ;
		else return false;
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
		$qq = $CI->db->query("SELECT 0 exento, tasa, redutasa, sobretasa FROM civa WHERE fecha < '$mfecha' ORDER BY fecha DESC LIMIT 1");
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

		return("<a href='javascript:void(0);'onclick=\"vent=window.open('".site_url("buscar/index/$idt/$puri")."','ventbuscar$id','width=$width,height=$height,scrollbars=Yes,	status=Yes,resizable=Yes,screenx=5,screeny=5');vent.focus();document.body.setAttribute('onUnload','vent.close();');\">".image('system-search.png',$modbus['titulo'],array('border'=>'0')).'</a>');
	}

	function p_modbus($modbus,$puri='',$width=800,$height=600,$id=''){
		$CI =& get_instance();
		//$uri  =$CI->uri->uri_string();
		$uri=$this->get_uri();
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
			)DEFAULT CHARSET 'latin1' ENGINE=MyISAM ";
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
			)DEFAULT CHARSET 'latin1' ENGINE=MyISAM ";

		$CI->db->simple_query($mSQL);

		//$id = $CI->session->userdata('session_id');
		$mSQL = "SELECT data1, data2, data3, data4 FROM data_sesion WHERE id='$id'";
		$query = $CI->db->query($mSQL);
		return $query->row_array();
	}

	function llenacombo($mSQL){
		$CI =& get_instance();
		$query = $CI->db->query($mSQL);
		$coma = '';
		$opciones = '';
		if ($query->num_rows() > 0){
			$colu = array();
			foreach( $query->list_fields() as $campo ) {
				$colu[] = $campo;
			}
			foreach ($query->result_array() as $row){
				$opciones .= $coma."['".trim($row[$colu[0]])."','".trim($row[$colu[1]])."']";
				$coma = ', ';
			}
		}
		$query->free_result();
		return $opciones;
	}

	//****************************************************
	//
	//  CARGA CANTIDAD ACTUALIZANDO MAESTRO Y DETALLE
	//
	//****************************************************
	function sinvcarga( $mCODIGO, $mALMA, $mCANTIDAD){
		$CI =& get_instance();
		if (empty($mALMA)) $mALMA = $this->traevalor('ALMACEN');
		if (empty($mALMA)) $mALMA = $this->dameval("SELECT ubica FROM caub WHERE gasto='N' ORDER BY ubica");
		$mGASTO  = $this->dameval("SELECT gasto FROM caub WHERE ubica='$mALMA'",1);
		if ($mGASTO == 'S') {
			$mSQL = "DELETE  FROM itsinv WHERE alma='"+mALMA+"'";
			$CI->db->simple_query($mSQL);
			return;
		};

		$codigoesc = $CI->db->escape($mCODIGO);

		// VERIFICA SI EL ARTICULO ES SERVICIO
		$mSQL = "SELECT SUBSTRING(tipo,1,1) tipo, enlace, fracci, derivado FROM sinv WHERE codigo=".$codigoesc." ";
		$query     = $CI->db->query($mSQL);
		$mREG      = $query->row_array();
		$mTIPO     = $mREG['tipo'];
		$mENLACE   = $mREG['enlace'];
		$mFRACCI   = $mREG['fracci'];
		$mDERIVADO = $mREG['derivado'];

		// SERVICIO NO DESCUENTA
		if ($mTIPO == "S" ) return;

		$mSQL = "UPDATE sinv SET existen=existen+$mCANTIDAD WHERE codigo=".$codigoesc."";
		$CI->db->simple_query($mSQL);

		// REVISA SI EXISTE EN ITSINV
		$mHAY = $this->dameval("SELECT COUNT(*) FROM itsinv WHERE codigo=".$codigoesc." AND alma='$mALMA'");
		if ( $mHAY == 0 ){
			$mSQL = $this->db->query("INSERT INTO itsinv SET codigo=".$codigoesc.", alma='$mALMA', existen=0");
			$CI->db->simple_query($mSQL);
		}

		// ACTUALIZA ITSINV
		$mSQL = "UPDATE itsinv SET existen=existen+$mCANTIDAD WHERE codigo=".$codigoesc." AND alma='$mALMA'";
		$CI->db->simple_query($mSQL);
		//echo $mSQL;

		// VERIFICA SI ES MENOR QUE 0
		if ( $mTIPO == 'F' and !empty($mENLACE) and $mCANTIDAD < 0 ){
			// SI EXISTE EL ENLACE
			if ($this->dameval("SELECT COUNT(*) FROM sinv WHERE codigo='$mENLACE'") == 1) {
				$mSQL = "SELECT existen FROM itsinv WHERE codigo=".$codigoesc." AND alma='$mALMA'";
				$mEXISTEN = $this->dameval($mSQL);
				// SI ES MENOR QUE 0 CALCULA LA NECESIDAD
				if ( $mEXISTEN < 0 ){
					// Cuantas necesita?
					if ( $mFRACCI > 0 ){
						$mNECE = round(abs($mEXISTEN)/$mFRACCI,0);
						if ( $mNECE*$mFRACCI < abs( $mEXISTEN) ) $mNECE += 1;
					} else {
						$mNECE = round(abs($mEXISTEN)*abs($mFRACCI),0);
					}

					// SUMA AL DETALLE
					$mSQL = "UPDATE itsinv SET existen=existen+$mCANTIDAD WHERE codigo=".$codigoesc." AND alma='$mALMA'";
					//CMNJ(STR(mNECE)+STR(mEXISTEN)+STR(mFRACCI))
					if ( $mFRACCI > 0 ){
						$descu = $mNECE*$mFRACCI;
						$mSQL = "UPDATE itsinv SET existen=existen+$descu WHERE codigo=".$codigoesc." AND alma='$mALMA'";
						$CI->db->simple_query($mSQL);
						//EJECUTASQL(mSQL,{ mNECE*mFRACCI, mCODIGO, mALMA })
					} else {
						$descu = $mNECE/abs($mFRACCI);
						$mSQL = "UPDATE itsinv SET existen=existen+$descu WHERE codigo=".$codigoesc." AND alma='$mALMA'";
						$CI->db->simple_query($mSQL);
						//EJECUTASQL(mSQL,{ mNECE/ABS(mFRACCI), mCODIGO, mALMA })
					}

					// DESCUENTA DEL MAYOR
					$mSQL = "UPDATE itsinv SET existen=existen-$mNECE WHERE codigo=".$codigoesc." AND alma='$mALMA'";
					$CI->db->simple_query($mSQL);

					// FALTA ACTUALIZAR LOS MAESTROS
					if ( $mFRACCI > 0 ){
						$descu = $mNECE*$mFRACCI;
						$mSQL = "UPDATE sinv SET existen=existen+$descu WHERE codigo=".$codigoesc." ";
						$CI->db->simple_query($mSQL);
					} else {
						$descu = $mNECE/ABS($mFRACCI);
						$mSQL = "UPDATE sinv SET existen=existen+$descu WHERE codigo=".$codigoesc."";
						$CI->db->simple_query($mSQL);
					}
					$mSQL = "UPDATE sinv SET existen=existen+$mNECE WHERE codigo=$mENLACE";
					$CI->db->simple_query($mSQL);

					// GUARDA EL MOVIMIENTO
					if ( $mFRACCI > 0 ) {
						$descu = $mNECE*$mFRACCI;
						$mSQL = "INSERT INTO trafrac SET id=0, fecha=now(), codigo=".$codigoesc.", enlace='$mENLACE', cantidad=$mNECE, fraccion=$descu, alma='$mALMA' ";
						$CI->db->simple_query($mSQL);
						//EJECUTASQL(mSQL,{ mCODIGO, mENLACE, mNECE, mNECE*mFRACCI, mALMA })
					} else {
						$descu = $mNECE/abs($mFRACCI);
						$mSQL = "INSERT INTO trafrac SET id=0, fecha=now(), codigo=".$codigoesc.", enlace='$mENLACE', cantidad=$mNECE, fraccion=$descu, alma='$mALMA' ";
						//EJECUTASQL(mSQL,{ mCODIGO, mENLACE, mNECE, mNECE/ABS(mFRACCI), mALMA })
						$CI->db->simple_query($mSQL);
					}
				}
			}
		}
	}

	//*******************************
	//
	//      Manda los Reportes
	//
	//*******************************
	function listados($modulo){
		$CI =& get_instance();

		$mSQL="UPDATE tmenus SET ejecutar=REPLACE(ejecutar,"."'".'( "'."','".'("'."') WHERE modulo LIKE '%LIS'";
		$CI->db->simple_query($mSQL);

		$mSQL="UPDATE tmenus SET ejecutar=REPLACE(ejecutar,"."'".'" )'."','".'")'."') WHERE modulo LIKE '%LIS'";
		$CI->db->simple_query($mSQL);

		$listados = '';

		if($modulo){
			$modulo=strtoupper($modulo);

			$CI->db->_escape_char='';
			$CI->db->_protect_identifiers=false;

			$CI->db->select("a.secu, a.titulo, a.mensaje, REPLACE(MID(a.ejecutar,10,30),"."'".'")'."','')  nombre");
			$CI->db->from("tmenus    a" );
			$CI->db->join("sida      b","a.codigo=b.modulo");
			$CI->db->join("reportes  d","REPLACE(MID(a.ejecutar,10,30),"."'".'")'."','')=d.nombre");
			$CI->db->where('b.acceso','S');
			$CI->db->where('b.usuario',$CI->session->userdata('usuario') );
			$CI->db->like("a.ejecutar","REPOSQL", "after");
			$CI->db->where('a.modulo',$modulo."LIS");
			$CI->db->orderby("a.secu");

			$query = $CI->db->get();

			if ($query->num_rows() > 0) {
				foreach ($query->result_array() as $row)
				{
					$listados .= "[ '".$row['secu']."', '".$row['titulo']."', '".$row['nombre']."' ],";
				}
			} else {
				//$listados .= "<tr><td>No se encontraron listados</td><tr>";
				$listados .= "['-','No tiene listados','' ],";
			}

			$query->free_result();

			//$CI->db->_escape_char='';
			$CI->db->_protect_identifiers=false;

			$CI->db->select("a.titulo, a.mensaje, a.nombre");
			$CI->db->from("intrarepo a" );
			$CI->db->join("tmenus    b","CONCAT(a.modulo,'LIS')=b.modulo AND b.ejecutar LIKE CONCAT('%',a.nombre,'%') ","left");
			$CI->db->where("b.codigo IS NULL");
			$CI->db->where("a.modulo",$modulo );
			$CI->db->where("a.activo","S");
			$CI->db->orderby("a.titulo");
			$query = $CI->db->get();


			if ($query->num_rows() > 0) {
				foreach ($query->result_array() as $row)
				{
					$listados .= "[ '*', '".$row['titulo']."', '".$row['nombre']."' ],";
				}
			} else {
				//$listados .= "[ '-', 'No hay listados Proteo', '' ]";
			}
			$query->free_result();

			$reposcript = "
	var storeListado = Ext.create('Ext.data.ArrayStore', {
		autoDestroy: true,
		storeId: 'listadoStore',
		idIndex: 0,
		fields: [ 'numero', 'nombre', 'reporte' ],
		data: [".$listados."]
	});

	function renderRepo(value, p, record) {
		var mreto='';
		if ( record.data.numero == '-' ){
			mreto = '<div style=\'background-color:#BCEFBC;text-weight:bold;align:center;\'>{0}</div>';
		} else {
			mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'reportes/ver/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
		}
		return Ext.String.format(
		mreto,
		value,
		record.data.reporte
		);
	}

	var gridListado = Ext.create('Ext.grid.Panel', {
		title: 'Listados',
		store: storeListado,
		width: '199',
		columns: [
			{ header: 'Nro.',   dataIndex: 'numero', width:  30 },
			{ header: 'Nombre de los Reportes', dataIndex: 'nombre', width: 169, renderer: renderRepo },
			{ header: 'Rep.',   dataIndex: 'reporte', hidden:  true }
		]
	});
";

		//'<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'reportes/ver/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>',


		}

		return $reposcript;

	}


	//*******************************
	//
	//      Manda los Reportes
	//
	//*******************************
	function otros($modulo, $url, $param = ''){
		$CI =& get_instance();

		if ( ! $this->iscampo('tmenus','proteo') ) {
			$CI->db->simple_query('ALTER TABLE tmenus ADD COLUMN proteo TEXT NULL');
		}

		$mSQL="UPDATE tmenus SET ejecutar=REPLACE(ejecutar,"."'".'( "'."','".'("'."') WHERE modulo LIKE '%OTR'";
		$CI->db->simple_query($mSQL);

		$mSQL="UPDATE tmenus SET ejecutar=REPLACE(ejecutar,"."'".'" )'."','".'")'."') WHERE modulo LIKE '%OTR'";
		$CI->db->simple_query($mSQL);

		$Otros = '';
		$Otros1 = '';

		if($modulo){
			$modulo=strtoupper($modulo);
			$Otros1 = '<table>';

			$CI->db->_escape_char='';
			$CI->db->_protect_identifiers=false;

			$mSQL  = "SELECT a.secu, a.titulo, a.mensaje, a.proteo ";
			$mSQL .= "FROM tmenus a JOIN sida b ON a.codigo=b.modulo ";
			$mSQL .= "WHERE b.acceso='S' AND b.usuario='".$CI->session->userdata('usuario')."' ";
			$mSQL .= "AND a.modulo='".$modulo."OTR' ORDER BY a.secu";
			$query = $CI->db->query($mSQL);

			if ($query->num_rows() > 0) {
				foreach ($query->result_array() as $row)
				{
					$Otros .= "[ '".$row['secu']."', '".trim($row['titulo'])."', '".trim($row['proteo'])."' ],";
					if ( $row['proteo'] != 'N/A'){
						$Otros1 .= "<tr><td>";
						if ( empty($row['proteo'])) {
							$Otros1 .= trim($row['titulo']);
						} else {
							$Otros1 .= trim($row['proteo']);
						}
						$Otros1 .="</td></tr>";
					}
				}
			} else {
				$Otros .= "['-','No tiene Funciones','' ]";
				$Otros1 .= "<tr><td>No hay Opciones</td></tr>";
			}
			$query->free_result();
			$Otros1 .= "</table>";



			$otroscript = "
	var storeOtros = Ext.create('Ext.data.ArrayStore', {
		autoDestroy: true,
		storeId: 'OtrosStore',
		autoload: true,
		idIndex: 0,
		fields: [ 'numero', 'nombre', 'proteo' ],
		data: [".$Otros."]
	});

	function renderOtro(value, p, record) {
		var mreto='';
		if ( record.data.numero == '-' ){
			mreto = '{0}';
		} else {
			//mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'".$url."/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
			mreto = '{1}';
		}
		return Ext.String.format(
		mreto,
		value,
		record.data.proteo
		);
	}

	var gridOtros = Ext.create('Ext.grid.Panel', {
		title: 'Otras Funciones',
		store: storeOtros,
		width: '199',
		columns: [
			//{ header: 'Nro.',   dataIndex: 'numero', width:  30 },
			{ header: 'Funcion que Ejecuta', dataIndex: 'nombre', width: 196, renderer: renderOtro },
			{ header: 'Otro',   dataIndex: 'ejecutar', hidden:  true }
		]
	});
";

		}

		//return $otroscript;
		return $Otros1;

	}


	function extjsfiltro($filtros, $tabla = ''){
		if ( !empty($tabla)) $tabla = trim($tabla).".";
		$where = "";
		//Buscar posicion 0 Cero
		$filter = json_decode($filtros, true);
		if (is_array($filter)) {
			$where = "";
			//Dummy Where.
			$qs = "";
			for ($i=0;$i<count($filter);$i++){
				switch($filter[$i]['type']){
				case 'string' : $qs .= " AND  $tabla".$filter[$i]['field']." LIKE '%".$filter[$i]['value']."%'";
					Break;
				case 'list' :
					if (strstr($filter[$i]['value'],',')){
						$fi = explode(',',$filter[$i]['value']);
						for ($q=0;$q<count($fi);$q++){
							$fi[$q] = "'".$fi[$q]."'";
						}
						$filter[$i]['value'] = implode(',',$fi);
							$qs .= " AND  $tabla".$filter[$i]['field']." IN (".$filter[$i]['value'].")";
					}else{
						$qs .= " AND $tabla".$filter[$i]['field']." = '".$filter[$i]['value']."'";
					}
					Break;
				case 'boolean' : $qs .= " AND $tabla".$filter[$i]['field']." = ".($filter[$i]['value']);
					Break;
				case 'numeric' :
					switch ($filter[$i]['comparison']) {
						case 'ne' : $qs .= " AND $tabla".$filter[$i]['field']." != ".$filter[$i]['value'];
							Break;
						case 'eq' : $qs .= " AND $tabla".$filter[$i]['field']." = ".$filter[$i]['value'];
							Break;
						case 'lt' : $qs .= " AND $tabla".$filter[$i]['field']." < ".$filter[$i]['value'];
							Break;
						case 'gt' : $qs .= " AND $tabla".$filter[$i]['field']." > ".$filter[$i]['value'];
							Break;
					}
					Break;
				case 'date' :
					switch ($filter[$i]['comparison']) {
						case 'ne' : $qs .= " AND $tabla".$filter[$i]['field']." != '".date('Y-m-d',strtotime($filter[$i]['value']))."'";
							Break;
						case 'eq' : $qs .= " AND $tabla".$filter[$i]['field']." = '".date('Y-m-d',strtotime($filter[$i]['value']))."'";
							Break;
						case 'lt' : $qs .= " AND $tabla".$filter[$i]['field']." < '".date('Y-m-d',strtotime($filter[$i]['value']))."'";
							Break;
						case 'gt' : $qs .= " AND $tabla".$filter[$i]['field']." > '".date('Y-m-d',strtotime($filter[$i]['value']))."'";
							Break;
					}
					Break;
				}
			}
			$where .= $qs;
		}
		return LTRIM(substr($where,4,1000));

	}

	function codificautf8($query){
		$arr = array();
		foreach ( $query as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		return $arr;
	}

	function extjscampos($tabla){
		$CI =& get_instance();
		$query = $CI->db->query("DESCRIBE $tabla");
		$i = 0;
		$campos = '';
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				if ( $i == 0 ) {
					$campos = "'".$row->Field."'";
					$i = 1;
				} else {
					$campos .= ",'".$row->Field."'";
				}

			}
		}
		return $campos;
	}

}
