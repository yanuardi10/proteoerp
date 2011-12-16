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

}
?>
