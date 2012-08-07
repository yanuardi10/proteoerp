<?php
class Dispmoviles extends Controller {

	function Dispmoviles(){
		parent::Controller();
		//$this->load->library("rapyd");
		//$this->sucu=$this->datasis->traevalor('NROSUCU');
	}

	function index(){

	}

//***********************
// Interfaces graficas
//***********************
	function ui($metodo=null){
		$obj='_'.$metodo; if(!method_exists($this,$obj)) show_404('page');
		$this->rapyd->load('dataform');

		$form = new DataForm("sincro/exportar/ui/$metodo/process");
		$form->fecha = new dateonlyField("Fecha","fecha");
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->rule ="required|chfecha";
		$form->fecha->size =12;
		$form->submit("btnsubmit","Descargar");
		$form->build_form();

		if ($form->on_success()){
			$fecha=$form->fecha->newValue;
			$this->$obj($fecha);
			return 0;
		}

		$data['content'] = $form->output;
		$data['title']   = '<h1>Exportar data a zip ('.$metodo.')</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function uig(){
		$this->rapyd->load('dataform');
		$this->datasis->modulo_id('91D',1);
		$sucu=$this->db->escape($this->sucu);

		$form = new DataForm("sincro/exportar/uig/process");

		$form->qtrae = new dropdownField("Que exportar?", "qtrae");
		$form->qtrae->rule ='required';
		$form->qtrae->option("","Selecionar");
		$form->qtrae->option("scli"  ,"Clientes");
		$form->qtrae->option("sinv"  ,"Inventario");
		$form->qtrae->option("maes"  ,"Inventario Supermercado");
		$form->qtrae->option("smov"  ,"Movimientos de clientes");
		$form->qtrae->option("transa","Facturas y transferencias");
		$form->qtrae->option("supertransa"  ,"Ventas Supermercado");


		$form->fecha = new dateonlyField("Fecha","fecha");
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->rule ="required|chfecha";
		$form->fecha->size =12;
		$form->submit("btnsubmit","Descargar");
		$form->build_form();

		$exito='';
		if ($form->on_success()){
			$fecha=$form->fecha->newValue;
			$obj='_'.str_replace('_','',$form->qtrae->newValue);
			if(method_exists($this,$obj))
				$rt=$this->$obj($fecha);
			else
				$rt='Metodo no definido ('.$form->qtrae->newValue.')';
			if(strlen($rt)>0){
				$form->error_string=$rt;
				$form->build_form();
			}else{
				$exito='Transferencia &Eacute;xitosa';
			}
		}

		$data['content'] = $form->output.$exito;
		$data['title']   = '<h1>Exportar data de Sucursal</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

//***********************
//  Interfaces uri
//***********************
	function uri($clave,$metodo,$vend,$cajero){
		$obj='_'.$metodo;
		if(!method_exists($this,$obj)) show_404('page');
		//if($clave!=sha1($this->config->item('encryption_key'))) return false;

		/*$usr=$this->db->escape($usr);
		$pws=$this->db->escape($pws);
		$cursor=$this->db->query("SELECT us_nombre FROM usuario WHERE us_codigo=$usr AND SHA(us_clave)=$pws");
		if($cursor->num_rows()==0) return false;
		$existe = $this->datasis->dameval("SELECT COUNT(*) FROM intrasida WHERE usuario=$usr AND modulo='$id'");
		if ($existe==0 ) return  false;*/


		//echo $obj;
		$this->$obj($vend);
	}


//***********************
// Metodos para exportar
//***********************
	function _data($vend){
		$metodos=array('sinv2','scli','tarjeta','tban');
		foreach($metodos AS $metodo){
			$obj='_'.$metodo;
			$this->$obj($vend);
			echo "{%%}\n";
		}
	}


	function _sinv($vend){
		set_time_limit(600);
		$query = $this->db->query("SELECT  codigo, descrip, precio1,precio2,precio3,precio4,margen1,margen2,margen3,margen4, base1,base2,base3,base4,ultimo AS costo, iva FROM sinv LIMIT 10");
		foreach ($query->result_array() as $row){
			$cadena=implode('{%}',$row)."\n";
			echo $cadena;
		}
	}

	function _sinv2($vend){
		set_time_limit(600);
		$query = $this->db->query("SELECT  codigo, descrip, base1,base2,base3,base4,ultimo AS costo, iva,1 AS bonifica,10 AS bonicant, UNIX_TIMESTAMP(fdesde),UNIX_TIMESTAMP(fhasta) FROM sinv WHERE activo='S' AND tipo='Articulo' AND base1>0 AND base2>0 AND base3>0 AND base3>0 AND ultimo>0  LIMIT 120");
		foreach ($query->result_array() as $row){
			$cadena=implode('{%}',$row)."\n";
			echo $cadena;
		}
	}

	function _scli($vend){
		set_time_limit(600);
		$query = $this->db->query("SELECT cliente, nombre,dire11,ciudad,telefono,rifci,email,repre,tipo, 0 AS vsaldo,0 AS csaldo,formap FROM scli ORDER BY nombre LIMIT 150");
		foreach ($query->result_array() as $row){
			$cadena=implode('{%}',$row)."\n";
			#$cadena=str_replace('','',$cadena);
		echo $cadena;
		}
	}

	function _tarjeta($vend){
		set_time_limit(600);
		$query = $this->db->query("SELECT tipo,nombre,tipo IN ('CH','DE') FROM tarjeta");
		foreach ($query->result_array() as $row){
			$cadena=implode('{%}',$row)."\n";
			echo $cadena;
		}
	}

	function _tban($vend){
		set_time_limit(600);
		$query = $this->db->query("SELECT cod_banc,nomb_banc FROM tban");
		foreach ($query->result_array() as $row){
			$cadena=implode('{%}',$row)."\n";
			echo $cadena;
		}
	}

	function gsqlite(){

		$cclientes="CREATE TABLE scli (
			id       INTEGER PRIMARY KEY,
			cliente  VARCHAR(5) UNIQUE,
			nombre   VARCHAR(45)   ,
			direc    TEXT          ,
			ciudad   VARCHAR(40)   ,
			telefono VARCHAR(30)   ,
			rifci    VARCHAR(13)   ,
			email    VARCHAR(100)  ,
			repre    VARCHAR(30)   ,
			tipo     CHAR(1)       ,
			vsaldo   DECIMAL(10,2)  ,
			csaldo   DECIMAL(10,2)  ,
			formap   INTEGER
		);";

		$csinv="CREATE TABLE sinv (
			id       INTEGER PRIMARY KEY,
			codigo   VARCHAR(15) UNIQUE,
			descrip  VARCHAR(45)  ,
			base1    DECIMAL(13,2) ,
			base2    DECIMAL(13,2) ,
			base3    DECIMAL(13,2) ,
			base4    DECIMAL(13,2) ,
			costo    DECIMAL(13,2) ,
			iva      DECIMAL(6,2)  ,
			bonifica INTEGER       ,
			bonicant INTEGER       ,
			fdesde   INTEGER       ,
			fhasta   INTEGER
		)";

		$ctarjeta="CREATE TABLE tarjeta (
			id      INTEGER PRIMARY KEY,
			tipo    CHAR(2)  UNIQUE,
			nombre  VARCHAR(20),
			pideban INTEGER
		)";

		$ctban="CREATE TABLE tban (
			id      INTEGER PRIMARY KEY,
			cod_banc CHAR(3) UNIQUE,
			nomb_banc VARCHAR(30)
		)";

		$config['hostname'] = 'localhost';
		$config['username'] = 'myusername';
		$config['password'] = 'mypassword';
		$config['database'] = 'mobil';
		$config['dbdriver'] = 'sqlite';
		$config['dbprefix'] = '';
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = '';
		$config['char_set'] = 'utf8';
		$config['dbcollat'] = 'utf8_general_ci';
		$dblite = $this->load->database($config,true);


		$mSQL=array();
		//$mSQL['sinv'] = "SELECT
		//	codigo, descrip,
		//	base1,base2,base3,base4,
		//	ultimo AS costo, iva,1 AS bonifica,10 AS bonicant,
		//	UNIX_TIMESTAMP(fdesde) AS fdesde ,UNIX_TIMESTAMP(fhasta) AS fhasta
		//	FROM sinv
		//	WHERE activo='S' AND tipo='Articulo' AND base1>0 AND base2>0 AND base3>0 AND base3>0 AND ultimo>0";
		//$mSQL['tban']    = "SELECT cod_banc,nomb_banc FROM tban";
		//$mSQL['tarjeta'] = "SELECT tipo,nombre,tipo IN ('CH','DE') AS pideban FROM tarjeta";
		$mSQL['scli']="SELECT
			cliente, TRIM(nombre) AS nombre,CONCAT_WS('-',TRIM(dire11),TRIM(dire12)) AS direc,
			TRIM(ciudad) AS ciudad,TRIM(telefono) AS telefono,TRIM(rifci) AS rifci,TRIM(email) AS email,
			TRIM(repre) AS repre,tipo, 0 AS vsaldo,0 AS csaldo,formap
			FROM scli ORDER BY nombre";

		echo '<pre>';
		foreach($mSQL AS $table=>$sql){
			echo  $table."\n";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$mLITE=$dblite->insert_string($table, $row);
				//echo $mLITE;
				$rt=$dblite->simple_query($mLITE);
				if($rt==false){
					echo $mLITE."\n";
					exit();
				}
				//$dblite->simple_query('COMMIT');
			}
		}
		echo '</pre>';

	}
}
