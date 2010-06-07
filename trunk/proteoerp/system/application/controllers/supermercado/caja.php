<?php
class Caja extends Controller {

	function Caja(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
	function index(){
		$this->rapyd->load("datatable",'dataform');

		$form = new DataForm("supermercado/caja/traer");

		$form->fecha = new dateField("Fecha", "fecha",'d/m/Y');
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->size=11;
		$form->fecha->rule = "required";

		$form->cajero = new dropdownField("Cajero", "cajero");
		$form->cajero->options("SELECT cajero, nombre FROM scaj ORDER BY cajero ");
		$form->cajero->rule = "required";

		$form->caja = new dropdownField("Caja", "ipcaj");
		$form->caja->rule = "required";
		$form->caja->options("SELECT CONCAT(ubica,'/',caja) AS value, caja FROM caja WHERE ubica REGEXP  '(\[0-9]{1,3})\.(\[0-9]{1,3})\.(\[0-9]{1,3})\.(\[0-9]{1,3})' ORDER BY caja");
		
		$form->submit("btnsubmit","Subir!");
		$form->build_form();

		$link=site_url('supermercado/caja/traer');
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
				//$("#envform").hide("slow");
				alert(msg);
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
		$data['content'] =  '<center>'.$form->output."<input type=button value='Subir' onclick='subir()'>".'</center>';
		$data['content'] .= '<div id="resp"></div>';
		$data['title']   = "<h1>Traer ventas de cajas</h1>";
		$data['script']  = $this->rapyd->get_head().script("jquery-1.2.6.js").$script;
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function traer(){
		$ipcaj  = $this->input->post('ipcaj');
		$fecha  = $this->input->post('fecha');
		$cajero = $this->input->post('cajero');

		if($ipcaj===false or $fecha===false or $cajero===false){ 
			echo '0';
			//redirect('supermercado/caja');
		}
		$ipcaj=explode('/',$ipcaj);
		$this->_traer($ipcaj[0],$ipcaj[1],$cajero,$fecha);
		echo '1';
	}
	function _traer($ip=NULL,$caja=FALSE,$cajero=FALSE,$fecha=FALSE){
		error_reporting(0);
		$fecha=human_to_dbdate($fecha);
		if(empty($ip)) return false;

		$config['hostname'] = $ip;
		$config['username'] = "datasis";
		$config['password'] = "";
		$config['database'] = "datasis";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "latin1";
		$config['dbcollat'] = "latin1_swedish_ci";

		$cajax=$this->load->database($config,TRUE);

		$data=array('viefac'=>'fecha','viepag'=>'f_factura','vieite'=>'fecha');
		foreach($data AS $tabla=>$fcampo){
			$this->db->simple_query("DELETE FROM $tabla WHERE cajero='$cajero' AND caja='$caja' and $fcampo='$fecha'");
			$query = $cajax->query("SELECT * FROM $tabla WHERE cajero='$cajero' AND caja='$caja' and $fcampo='$fecha'");
			if ($query->num_rows() > 0){
				foreach ($query->result_array() as $data){
					$insert = $this->db->insert_string($tabla, $data);
					$this->db->simple_query($insert);
				}
			}
		}
	}

	function ventas($fecha=FALSE,$arch='ventas.zip'){
		$fecha=20080809;
		if(empty($fecha)) return 0;
		$this->load->helper('file');
		$this->load->helper('download');
		$this->load->dbutil();

		$tables=array();
		$data=array('viefac'=>'fecha','viepag'=>'f_factura','vieite'=>'fecha');
		foreach($data AS $tabla=>$fcampo){
			$mSQL="CREATE TABLE `TEMP$tabla`  SELECT * FROM $tabla WHERE $fcampo='$fecha'";
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
	function bajar($fecha=NULL,$sucu=NULL){

		if(empty($sucu) AND empty($fecha)){ echo 'Error de parametros'; return 0;}
		$url=$this->datasis->dameval("SELECT url FROM sucu WHERE codigo='$sucu'");
		if(strlen($url)<3){ echo 'Sucursal sin direccion';return 0; }

		$sucu=intval($sucu);
		$ch = curl_init("http://$url/proteosuper/supermercado/caja/ventas/$fecha/ventas.zip");
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

			$data=array('viefac'=>'fecha','viepag'=>'f_factura','vieite'=>'fecha');
			foreach($data AS $tabla=>$fcampo){
				//$this->db->simple_query("DELETE FROM $tabla WHERE MID(caja,1,1)=$sucu and $fcampo='$fecha'");
			}
			$lines = file('./uploads/ventas.sql');
			foreach ($lines as $line_num => $line) {
				if(substr($line,0,6)=='INSERT')
					$mSQL=str_replace('TEMPvie','vie',$line);
					//$this->db->simple_query("DELETE FROM $tabla WHERE MID(caja,1,1)=$sucu and $fcampo='$fecha'");
			}
			unlink('./uploads/ventas.zip');
			unlink('./uploads/ventas.sql');

		} else {
			echo 'failed';
		}
	}
	function instalar(){
		$mSQL='ALTER TABLE `sucu` ADD `url` VARCHAR(200) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `prefijo` CHAR(1) NULL';
		$this->db->simple_query($mSQL);
	}
}
?>