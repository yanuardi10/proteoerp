<?php
require_once(BASEPATH . 'application/controllers/ventas/pfac.php');
class Dispmoviles extends Controller {

	function Dispmoviles(){
		parent::Controller();
		//$this->load->library("rapyd");
		//$this->sucu=$this->datasis->traevalor('NROSUCU');
	}

	function index(){

	}

//***********************
//  Interfaces uri
//***********************
	function uri($clave,$metodo,$vend,$cajero){
		$obj='_'.$metodo;
		if(!method_exists($this,$obj)) show_404('page');
		//$this->$obj($vend);
	}

	function sincro($tabla,$uuid,$matriz){
		session_write_close();
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$vend=$this->datasis->dameval("SELECT vendedor FROM usuario WHERE uuid=".$this->db->escape($uuid));

		if(empty($vend)){
			echo '[]';
			return '';
		}
		$dbvend = $this->db->escape($vend);

		$escape = function($val){
			if(is_numeric($val)){
				$val=$val+0;
				if(is_infinite($val))
					return 0;
				else
					return $val;
			}elseif(is_null($val)){
				return '';
			}else{
				return $val;
			}
		};

		$mSQL = array();

		$mSQL['sinv'] = "SELECT id,
			TRIM(codigo) AS codigo, TRIM(descrip) AS descrip,
			base1,
			base2,
			base3,
			base4,
			ultimo AS costo, iva,1 AS bonifica,10 AS bonicant,
			UNIX_TIMESTAMP(fdesde) AS fdesde ,UNIX_TIMESTAMP(fhasta) AS fhasta,
			existen,TRIM(clave) AS clave
			FROM sinv
			WHERE activo='S' AND tipo='Articulo' AND base1>0 AND base2>0 AND base3>0 AND base3>0 AND ultimo>0";
		$mSQL['scli'] = "SELECT a.id,
			TRIM(a.cliente) AS cliente, TRIM(a.nombre) AS nombre,CONCAT_WS('-',TRIM(a.dire11),TRIM(a.dire12)) AS direc,
			TRIM(a.ciudad) AS ciudad,TRIM(a.telefono) AS telefono,TRIM(a.rifci) AS rifci,TRIM(a.email) AS email,
			TRIM(a.repre) AS repre,TRIM(a.tipo) AS tipo,
			COALESCE(SUM((b.monto-b.abonos)*(b.vence<=CURDATE())),0) AS vsaldo,
			0 AS csaldo,formap
			FROM scli AS a
			LEFT JOIN smov AS b ON a.cliente=b.cod_cli AND b.tipo_doc NOT IN ('AB','NC','AN') AND b.monto>b.abonos
			WHERE a.vendedor=$dbvend
			GROUP BY a.cliente
			ORDER BY a.nombre LIMIT 1000";
		$mSQL['tarjeta'] = "SELECT id, TRIM(tipo) AS tipo,TRIM(nombre) AS nombre,tipo IN ('CH','DE') AS pideban FROM tarjeta";
		$mSQL['tban']    = "SELECT a.id,TRIM(cod_banc) AS cod_banc,TRIM(nomb_banc) AS nom_banc FROM tban";

		$sqlite['sinv']    = 'INSERT OR REPLACE INTO sinv_'.$matriz.'    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);';
		$sqlite['scli']    = 'INSERT OR REPLACE INTO scli_'.$matriz.'    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?);';
		$sqlite['tarjeta'] = 'INSERT OR REPLACE INTO tarjeta_'.$matriz.' VALUES (?,?,?,?);';
		$sqlite['tban']    = 'INSERT OR REPLACE INTO tban_'.$matriz.'    VALUES (?,?,?,?);';

		$sql  = $mSQL[$tabla];
		$data = $itdata = array();

		$query = $this->db->query($sql);

		$itdata['sql'] = $sqlite[$tabla];
		foreach ($query->result_array() as $row){
			$itdata['data']    = array_map($escape,array_values($row));

			$data[] = $itdata;
		}

		echo json_encode($data);
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

}
