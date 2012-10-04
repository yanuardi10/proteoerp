<?php
class Traer extends Controller {

	function Traer(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
	function index(){
		
	}
	function cajas(){
		$this->rapyd->load("datatable",'dataform');

		$form = new DataForm("supermercado/traer/traercajas");

		$form->fecha = new dateField("Fecha", "fecha",'d/m/Y');
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->size=11;
		$form->fecha->rule = "required";

		$form->cajero = new dropdownField("Cajero", "cajero");
		$form->cajero->options("SELECT cajero, nombre FROM scaj ORDER BY cajero ");
		$form->cajero->rule = "required";

		$form->caja = new dropdownField("Caja", "ipcaj");
		$form->caja->rule = "required";
		$form->caja->options("SELECT CONCAT(ubica,'/',caja) AS value, caja FROM caja WHERE ubica REGEXP  '^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\\.([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])){3}$' ORDER BY caja");
		//$form->submit("btnsubmit","Subir!");
		$form->build_form();

		$link=site_url('supermercado/traer/traercajas/');
		$script=<<<script
		<script type='text/javascript'>

		function subir(){
			$("#resp").text('Subiendo...').show();
			var url = '$link';
			$.ajax({
				type: "POST",
				url: url,
				data: $("input,select").serialize(),
				success: function(msg){
				$("#resp").text(msg).show();//.fadeOut(1000);
				//if(msg=='1')
				//	$("#resp").text('Listo!!').show().fadeOut(1000);
				//else if(msg=='2')
				//	$("#resp").text('Error de parametros').show().fadeOut(1000);
				//else
				//	$("#resp").html('Error de conecci&oacute;n').show().fadeOut(5000);
				}
			});
		}
		</script>
script;
		$data['content'] =  '<center>'.$form->output."<input type=button value='Subir' onclick='subir()'>".'</center>';
		$data['content'] .= '<pre id="resp"></pre>';
		$data['title']   = "<h1>Traer ventas de cajas</h1>";
		$data['script']  = script("jquery.js").$script;
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function sucursales(){
		$this->rapyd->load("datatable",'dataform');

		$form = new DataForm("supermercado/traer/traersucursal/");

		$form->fecha = new dateField("Fecha", "fecha",'d/m/Y');
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->size=11;
		$form->fecha->rule = "required";

		$form->sucu = new dropdownField("Sucursales", "sucursal");
		$form->sucu->rule = "required";
		$form->sucu->options("SELECT codigo,sucursal FROM sucu WHERE codigo IS NOT NULL ORDER BY sucursal");
		//$form->submit("btnsubmit","Traer!");
		$form->build_form();

		$link=site_url('supermercado/traer/traesucursal/');
		$script=<<<script
		<script type='text/javascript'>

		function subir(){
			$("#resp").text('Subiendo...').show();
			var url = '$link';
			$.ajax({
				type: "POST",
				url: url,
				data: $("input,select").serialize(),
				success: function(msg){
				//alert(msg);
				if(msg=='1')
					$("#resp").text('Listo!!').show().fadeOut(1000);
				else if(msg=='2')
					$("#resp").text('Error de parametros').show().fadeOut(1000);
				else
					$("#resp").html('Error de conecci&oacute;n').show().fadeOut(5000);
				}
			});
		}
		</script>
script;
		$data['content'] =  '<center>'.$form->output."<input type=button value='Traer!' onclick='subir()'>".'</center>';
		$data['content'] .= '<div id="resp"></div>';
		$data['title']   = "<h1>Traer ventas de Sucursales</h1>";
		$data['script']  = $this->rapyd->get_head().script("jquery.js").$script;
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function traercajas(){
		$ipcaj  = $this->input->post('ipcaj');
		$fecha  = $this->input->post('fecha');
		$cajero = $this->input->post('cajero');

		if($ipcaj===false or $fecha===false or $cajero===false){ 
			echo 'Faltan parametros';
			return 0;
		}
		$ipcaj=explode('/',$ipcaj);
		$this->_traercajas($ipcaj[0],$ipcaj[1],$cajero,$fecha);
		return 1;
	}
	
	function _traercajas($ip=NULL,$caja=FALSE,$cajero=FALSE,$fecha=FALSE){
		error_reporting(0);
		$timestamp= timestampFromInputDate($fecha);
		$fecha    = inputDateFromTimestamp($timestamp, 'Ymd');

		if(empty($ip)) return false;
		$tot=0;
		ini_set('mysql.connect_timeout','2');
		$link = mysql_connect($ip, 'datasis') or die('Maquina fuera de linea');
		mysql_select_db('datasis') or die('Base de datos no seleccionable');

		$check=true;
		$data=array('viefac'=>'fecha','viepag'=>'f_factura','vieite'=>'fecha');
		foreach($data AS $tabla=>$fcampo){
			$this->db->simple_query("DELETE FROM $tabla WHERE cajero='$cajero' AND caja='$caja' and $fcampo='$fecha'");
			
			$mSQL="SELECT * FROM $tabla WHERE cajero='$cajero' AND caja='$caja' and $fcampo='$fecha'";
			$result = mysql_query($mSQL,$link);
			if (!$result){
				$check=false;
				Echo  'Consulta fallida'.mysql_error($link)."\n";
			}else{
				$num_rows = mysql_num_rows($result);
				$tot+=$num_rows;
				if ($num_rows > 0){
					while ($data = mysql_fetch_assoc($result)) {
						$insert = $this->db->insert_string($tabla, $data);
						if(!$this->db->simple_query($insert)){
							$check=false;
							echo "Problemas en la insercion local\n";
							memowrite($insert,'straer');
						}
						
					}
				}
			}
		}
		if($check) Echo "Fueron pasados $tot registros!";
	}
	
	function traesucursal(){
		$fecha  = $this->input->post('fecha');
		$sucursal = $this->input->post('sucursal');
		logusu('s/traesucu',"Trajo Ventas de Sucursal $sucursal de fecha $fecha");

		if($fecha===false or $sucursal===false){ 
			echo '2';
			return 0;
		}
		$prefijo=$this->datasis->dameval("SELECT prefijo FROM sucu WHERE codigo='$sucursal'");
		
		$this->_traesucursal($fecha ,$sucursal,$prefijo);
		$this->_traedine($fecha ,$prefijo);
		echo '1';
		return 1;
	}
	
	function _traesucursal($fecha=NULL,$sucu=NULL,$prefijo=null){

		if(empty($sucu) AND empty($fecha)){ echo 'Error de parametros'; return 0;}
		
		$timestamp= timestampFromInputDate($fecha);
		$fecha    = inputDateFromTimestamp($timestamp, 'Ymd');
		//$prefijo=$this->datasis->dameval("SELECT prefijo FROM sucu WHERE codigo='$sucu'");
		

		$url=$this->datasis->dameval("SELECT CONCAT_WS('',url,proteo) FROM sucu WHERE prefijo='$prefijo'");
		
		if(strlen($url)<3){ echo 'Sucursal sin direccion';return 0; }
		$sucu=intval($sucu);
		$ch = curl_init("http://$url/supermercado/traer/ventas/$fecha/ventas.zip");
		$fp = fopen("./uploads/ventas.zip", "w");
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		$zip = new ZipArchive;
		$res = $zip->open('./uploads/ventas.zip');
		if ($res === TRUE) {
			$zip->extractTo('./uploads/');
			$zip->close();
			$cant=strlen($prefijo);
		
			$data=array('viefac'=>'fecha','viepag'=>'f_factura','vieite'=>'fecha');
			foreach($data AS $tabla=>$fcampo){
				$mSQL="DELETE FROM $tabla WHERE MID(caja,1,$cant)=$prefijo and $fcampo='$fecha'";
				$this->db->simple_query($mSQL);
			}
			$lines = file('./uploads/ventas.sql');
			foreach ($lines as $line_num => $line) {
				if(substr($line,0,6)=='INSERT'){
					$mSQL=str_replace('TEMPvie','vie',$line);
					$this->db->simple_query($mSQL);
				}
			}
			unlink('./uploads/ventas.zip');
			unlink('./uploads/ventas.sql');

		} else {
			echo 'Error con el zip';
		}
	}
	
	function _traedine($fecha=NULL,$sucu=NULL){

		if(empty($sucu) AND empty($fecha)){ echo 'Error de parametros DINE '; return 0;}

		$sucux = $this->load->database('sucu'.$sucu, TRUE);

		$timestamp= timestampFromInputDate($fecha);
		$fecha    = inputDateFromTimestamp($timestamp, 'Ymd');

		$url=$this->datasis->dameval("SELECT CONCAT_WS('',url,proteo) FROM sucu WHERE prefijo='$sucu'");

		if(strlen($url)<3){ echo 'Sucursal sin direccion';return 0; }

		$sucu=intval($sucu);
		$ch = curl_init("http://$url/supermercado/traer/dine/$fecha/dine.zip");
		$fp = fopen("./uploads/dine.zip", "w");
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		$zip = new ZipArchive;
		$res = $zip->open('./uploads/dine.zip');
		if ($res === TRUE) {
			$zip->extractTo('./uploads/');
			$zip->close();

			$mSQL="DELETE itdine FROM itdine JOIN dine ON dine.numero=itdine.numero WHERE dine.fecha='$fecha'";
			$sucux->simple_query($mSQL);

			$mSQL="DELETE FROM dine WHERE fecha='$fecha'";
			$sucux->simple_query($mSQL);

			$lines = file('./uploads/dine.sql');
			foreach ($lines as $line_num => $line) {
				if(substr($line,0,6)=='INSERT'){
					$mSQL=str_replace('TEMPdine','dine',$line);
					$mSQL=str_replace('TEMPitdine','itdine',$mSQL);
					$sucux->simple_query($mSQL);
				}
			}
			unlink('./uploads/dine.zip');
			unlink('./uploads/dine.sql');

		} else {
			echo 'Error con el zip';
		}
	}
	function ventas($fecha=FALSE,$arch='ventas.zip'){
		//$fecha=20080809;
		if(empty($fecha)) return 0;
		$this->load->helper('file');
		$this->load->helper('download');
		$this->load->dbutil();

		$tables=array();
		$data=array('viefac'=>'fecha','viepag'=>'f_factura','vieite'=>'fecha');
		foreach($data AS $tabla=>$fcampo){
			$mSQL="CREATE TEMPORARY TABLE `TEMP$tabla`  SELECT * FROM $tabla WHERE $fcampo='$fecha'";
			$this->db->simple_query($mSQL);
			$tables[]="TEMP$tabla";
		}
		$prefs = array(
			'tables'      => $tables,           // Array of tables to backup.
			'ignore'      => array(),           // List of tables to omit from the backup
			'format'      => 'zip',             // gzip, zip, txt
			'filename'    => 'ventas.sql',      // File name - NEEDED ONLY WITH ZIP FILES
			'add_drop'    => FALSE,             // Whether to add DROP TABLE statements to backup file
			'add_insert'  => TRUE,              // Whether to add INSERT data to backup file
			'newline'     => "\n"               // Newline character used in backup file
		);

		$backup =& $this->dbutil->backup($prefs);
		write_file("/downloads/$arch", $backup);
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER))
			$_SERVER['HTTP_USER_AGENT']='curl';
		force_download($arch, $backup);
	}
	function dine($fecha=FALSE,$arch='dine.zip'){
		if(empty($fecha)) return 0;
		$this->load->helper('file');
		$this->load->helper('download');
		$this->load->dbutil();

		$mSQL="CREATE TEMPORARY TABLE `TEMPdine`  SELECT * FROM dine WHERE fecha='$fecha'";
		$this->db->simple_query($mSQL);
		$mSQL="CREATE TEMPORARY TABLE `TEMPitdine` SELECT b.* FROM dine AS a JOIN itdine AS b ON a.numero=b.numero WHERE fecha='$fecha'";
		$this->db->simple_query($mSQL);
		
		$tables=array('TEMPdine','TEMPitdine');

		$prefs = array(
			'tables'      => $tables,           // Array of tables to backup.
			'ignore'      => array(),           // List of tables to omit from the backup
			'format'      => 'zip',             // gzip, zip, txt
			'filename'    => 'dine.sql',      // File name - NEEDED ONLY WITH ZIP FILES
			'add_drop'    => FALSE,             // Whether to add DROP TABLE statements to backup file
			'add_insert'  => TRUE,              // Whether to add INSERT data to backup file
			'newline'     => "\n"               // Newline character used in backup file
		);

		$backup =& $this->dbutil->backup($prefs);
		write_file("/downloads/$arch", $backup);
		if(!array_key_exists('HTTP_USER_AGENT', $_SERVER))
			$_SERVER['HTTP_USER_AGENT']='curl';
		force_download($arch, $backup);
	}
	
	function instalar(){
		$mSQL='CREATE TABLE `nfiscales` (
		  `maquina` char(12) default NULL,
		  `factura` char(8) default NULL,
		  `numero` char(8) default NULL,
		  `caja` char(5) NOT NULL default ,
		  `fecha` timestamp NULL default CURRENT_TIMESTAMP,
		  `id` int(11) unsigned NOT NULL auto_increment,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `Index 2` (`maquina`,`factura`)
		) ENGINE=MyISAM AUTO_INCREMENT=172 DEFAULT CHARSET=latin1';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `url` VARCHAR(200) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `proteo` VARCHAR(50) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `prefijo` CHAR(1) NULL';
		$this->db->simple_query($mSQL);
	}
}
?>
