<?php
class Cargasarch extends Controller {

	function Cargasarch(){
		parent::Controller();
		$this->load->helper('string');
		$this->load->library("rapyd");
		$this->load->library('encrypt');
		$this->sucu = $this->datasis->traevalor('NROSUCU');

		$this->dir=reduce_double_slashes($this->config->item('uploads_dir').'/traspasos');
		//if(!file_exists('./uploads/traspasos')){
		//	mkdir('./uploads/traspasos');
		//}
	}

	function index(){

	}

	function cargaxml(){
		$this->rapyd->load('dataform');
		$this->load->library('xmlinex');

		$form = new DataForm("cargasxml/carga/process");

		$form->upl = new uploadField("Archivo Xml", "arch");
		$form->upl->upload_path = $this->dir;
		$form->upl->allowed_types = "xml";

		$form->submit("btnsubmit","Cargar");
		$form->build_form();

		if ($form->on_success()){
			set_time_limit(600);
			$nombre=$form->upl->upload_data['file_name'];
			$dir   ='./uploads/traspasos/'.$nombre;
			$this->xmlinex->import($dir);
			unlink($dir);
		}

		$data['content'] = $form->output;
		$data['title']   = '<h1>Cargas de XML</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function cargasql(){
		$this->rapyd->load('dataform');
		$this->load->library('Sqlinex');

		$form = new DataForm("cargasarch/cargasql/process");

		$form->upl = new uploadField("Archivo Sql", "arch");
		$form->upl->upload_path = $this->dir;
		$form->upl->allowed_types = "txt";

		$form->submit("btnsubmit","Cargar");
		$form->build_form();

		$msg='';
		if ($form->on_success()){
			set_time_limit(600);
			$nombre=$form->upl->upload_data['file_name'];
			$dir   ='./uploads/traspasos/'.$nombre;
			$this->sqlinex->import($dir);
			unlink($dir);
			$msg='Carga &Eacute;xitosa';
		}

		$data['content'] = $form->output.$msg;
		$data['title']   = '<h1>Cargas de SQL</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function cargazip(){
		set_time_limit(600);
		$this->rapyd->load('dataform');
		$this->load->library('Sqlinex');

		$form = new DataForm("cargasarch/cargazip/process");

		$form->upl = new uploadField("Archivo Zip", "arch");
		$form->upl->upload_path = $this->dir;
		$form->upl->max_size     =6000;
		$form->upl->allowed_types = "zip";

		$form->submit("btnsubmit","Cargar");
		$form->build_form();

		$msg='';
		if ($form->on_success()){

			$nombre=$form->upl->upload_data['file_name'];
			$rt=$this->_cargazip($nombre);
			if(!empty($rt)){
				$form->error_string=$rt;
				$form->build_form();
			}else{
				$msg='<p>Carga completada</p>';
			}
		}

		$data['content'] = $form->output.$msg;
		$data['title']   = '<h1>Cargas de Zip</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _cargazip($nombre=null){
		set_time_limit(600);
		if(empty($nombre)) return 'Nombre vacio';

		//$dir = $this->dir.'/';
		$dir   ='./uploads/traspasos/';
		$zip = new ZipArchive;

		$ban=false;
		$res = $zip->open($dir.$nombre);

		if ($res === TRUE) {
			if($zip->numFiles==2){
				for($i=0;$i<2;$i++){
					if($zip->getNameIndex($i)!='firma.txt'){
						$exp_nombre=$zip->getNameIndex($i);
					}else{
						$ban=true;
					}
				}
				if(!$ban){
					unlink($dir.$nombre);
					return 'Archivo zip no firmado';
				}
				$sucu = $this->sucu;
				$dir_nom=$dir.str_replace('.','_',$nombre).'tmp';

				mkdir($dir_nom,0777);
				$zip->extractTo($dir_nom);
				$zip->close();
				$firma=file_get_contents($dir_nom.'/firma.txt');
				$firma=$this->encrypt->decode($firma);
				$datas=explode($this->sqlinex->separador,$firma);

				if(count($datas)==2){
					$sucursal=$datas[0];
					if($sucu==$sucursal){
						unlink($dir_nom.'/firma.txt');
						unlink($dir_nom.'/'.$exp_nombre);
						unlink($dir.$nombre);
						rmdir($dir_nom);
						return 'No se puede cargar el archivo en la misma sucursal que fue generada';
					}
					$firma = $datas[1];
				}else{
					$firma = $datas[0];
				}
				$firma2=md5_file($dir_nom.'/'.$exp_nombre);
				if($firma!=$firma2){
					unlink($dir_nom.'/firma.txt');
					unlink($dir_nom.'/'.$exp_nombre);
					unlink($dir.$nombre);
					rmdir($dir_nom);
					return 'Firmas no concuerdan';
				}

				$this->sqlinex->import($dir_nom.'/'.$exp_nombre);

				unlink($dir_nom.'/firma.txt');
				unlink($dir_nom.'/'.$exp_nombre);
				unlink($dir.$nombre);
				rmdir($dir_nom);
			}else{
				unlink($dir.$nombre);
				return 'El archivo zip no parece ser de importacion';
			}
		} else {
			unlink($dir.$nombre);
			return 'Error con el archivo zip';
		}
		return '';
	}

//****************************
//Metodos publicos para traer
//****************************

	function traertransaczip($fecha=null){
		if(empty($fecha)){
			$this->_traertransaczip();
		}elseif(is_numeric($fecha) AND $fecha>10000000){
			$anio=substr($fecha,0,4);
			$mes =substr($fecha,4,2);
			$dia =substr($fecha,6,2);
			if(checkdate($mes,$dia,$anio)){
				if(!array_key_exists('HTTP_USER_AGENT', $_SERVER))
					$_SERVER['HTTP_USER_AGENT']='curl';
				$this->_traertransaczip($fecha);
			}
		}
	}

	function traersinvzip($sucu){
		$ssucu=$this->db->escape($sucu);
		$val=$this->datasis->dameval("SELECT COUNT(*) FROM sucu WHERE codigo=$ssucu");
		if($val==1){
			$this->_traersinvzip($sucu);
		}
	}
	
	function vendambul($sucu=null){
		set_time_limit(600);
		if(empty($sucu)) $sucu='01';
		$ssucu=$this->db->escape($sucu);
		
		$cant=$this->datasis->dameval("SELECT * FROM sucu WHERE codigo=$ssucu");
		if($cant>0){
			$rt=$this->_traerzip($sucu,'finanzas/exportar/vendambul');
			if ($rt)
				$msg='Hubo un error en la trasnferencia, se genero un ticket '.anchor('supervisor/tiket','ir a tickets');
			else
				$msg='Tranferencia &eacute;xitosa';
				
		}else{
			$msg='Error, la sucursal '.$sucu.' no existe, revise la configuracion aqui: '.anchor('supervisor/sucu','sucursales');
		}
		$data['content'] = $msg.'<p>'.anchor('inventario/fotos/traerfotos/'.$sucu,'Traer fotos de invetario').'</p>';
		$data['title']   = '<h1>Descarga de informaci&oacute;n para vendedores ambulantes</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


//**************************
//Metodos para traer data
//**************************

	function _traertransaczip($fecha=null){
		set_time_limit(600);
		$this->load->library('Sqlinex');
		$sucu  = $this->db->escape($this->sucu);
		$query = $this->db->query("SELECT * FROM sucu WHERE codigo<>$sucu");
		if(empty($fecha)) $fecha = date('Ymd');
		$dir   ='./uploads/traspasos/';

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$url=$row->url;
				$url=$row->url.'/'.$row->proteo.'/ventas/exportar/transacciones/'.$fecha;
				$url=reduce_double_slashes($url);
				//echo $url;
				$ch = curl_init('http://'.$url);
				$tmpfname = tempnam($dir, "ve");

				$fp = fopen($tmpfname, "w");
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
				$nombre=basename($tmpfname);
				$error=$this->_cargazip($nombre);
				if(!empty($error)){
					$data['padre']      ='S';
					$data['prioridad']  ='5';
					$data['usuario']    ='TRANF';
					$data['contenido']  ="Error en transferencia: Sucursal: $this->sucu, Url: $url, Fecha: $fecha, Mensaje: $error";
					$data['estado']     ='N';
					$mSQL = $this->db->insert_string('tiket', $data);

					$this->db->simple_query($mSQL);
				}
			}
		}
	}

	function _traersinvzip($sucu=null){
		set_time_limit(600);
		$this->load->library('Sqlinex');
		$sucu  = $this->db->escape($sucu);
		$query = $this->db->query("SELECT * FROM sucu WHERE codigo=$sucu");
		if(empty($fecha)) $fecha = date('Ymd');
		$dir   ='./uploads/traspasos/';

		if ($query->num_rows() > 0){
			 $row = $query->row();
			$url=$row->url;
			$url=$row->url.'/'.$row->proteo.'/ventas/exportar/exsinv';
			$url=reduce_double_slashes($url);

			$ch = curl_init('http://'.$url);
			$tmpfname = tempnam($dir, "sinv");

			$fp = fopen($tmpfname, "w");
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			$nombre=basename($tmpfname);
			$error=$this->_cargazip($nombre);
			if(!empty($error)){
				$data['padre']      ='S';
				$data['prioridad']  ='5';
				$data['usuario']    ='TRANF';
				$data['contenido']  ="Error en transferencia: Sucursal: $this->sucu, Url: $url, Fecha: $fecha, Mensaje: $error";
				$data['estado']     ='N';
				$mSQL = $this->db->insert_string('tiket', $data);

				$this->db->simple_query($mSQL);
				
				return 1;
			}
		}
		return 0;
	}
	
	function _traerzip($sucu,$dir_url){
		set_time_limit(600);
		$this->load->library('Sqlinex');
		$sucu  = $this->db->escape($sucu);
		$query = $this->db->query("SELECT * FROM sucu WHERE codigo=$sucu");
		$fecha = date('Ymd');

		$dir   ='./uploads/traspasos/';

		if ($query->num_rows() > 0){
			$row = $query->row();
			$url=$row->url;
			$url=$row->url.'/'.$row->proteo.'/'.$dir_url;
			$url=reduce_double_slashes($url);

			$ch = curl_init('http://'.$url);
			$tmpfname = tempnam($dir, "cargagen");

			$fp = fopen($tmpfname, "w");
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			$nombre=basename($tmpfname);
			$error=$this->_cargazip($nombre);

			if(!empty($error)){
				$data['padre']      ='S';
				$data['prioridad']  ='5';
				$data['usuario']    ='TRANF';
				$data['contenido']  ="Error en transferencia: Sucursal: $this->sucu, Url: $url, Fecha: $fecha, Mensaje: $error";
				$data['estado']     ='N';
				$mSQL = $this->db->insert_string('tiket', $data);

				$this->db->simple_query($mSQL);
			}
		}
	}
	
}
?>