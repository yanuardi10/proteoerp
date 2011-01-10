<?php
class Rpcserver extends Controller {

	function index(){
		$this->load->library('xmlrpc');
		$this->xmlrpc->xmlrpc_defencoding=$this->config->item('charset');
		$this->load->library('xmlrpcs');
		$this->xmlrpcs->xmlrpc_defencoding=$this->config->item('charset');

		$config['functions']['sprecios'] = array('function' => 'Rpcserver.precio_supermer');
		$config['functions']['ttiket']   = array('function' => 'Rpcserver.traer_tiket');
		$config['functions']['cea']      = array('function' => 'Rpcserver.ComprasEmpresasAsociadas');

		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();
	}

	function precio_supermer($request){
		$parameters = $request->output_parameters();

		$codigo=$parameters['0'];
		$query = $this->db->query("SELECT precio1,precio2,precio3,precio4,precio5, descrip, barras FROM maes WHERE codigo=".$this->db->escape($codigo));

		if ($query->num_rows() > 0){
			$row = $query->row();
			$response = array(
					array(
							0 => $row->precio1,
							1 => $row->precio2,
							2 => $row->precio3,
							3 => $row->precio4,
							4 => $row->precio5,
							5 => $row->descrip,
							6 => $row->barras),
					'struct');
		}else{
			$response = array(
					array(),
					'struct');
		}
		return $this->xmlrpc->send_response($response);
	}


	function traer_tiket($request){
		$parameters = $request->output_parameters();
		$fechad=$parameters['0'];

		$query = $this->db->query("SELECT id,padre,pertenece,prioridad,usuario,contenido,estampa,actualizado,estado FROM tiket WHERE estampa>'$fechad' AND estampa<=NOW() AND usuario<>'TRANF'");
		//$query = $this->db->query("SELECT id,padre,pertenece,prioridad,usuario,contenido,estampa,actualizado,estado FROM tiket LIMIT 3");

		$tiket=array();
		if ($query->num_rows() > 0){ 
			foreach ($query->result_array() as $row){
				foreach($row AS $ind=>$val){
					$row[$ind]=base64_encode($val);
				}
				$tiket[] = serialize($row);
			}
		}else{
			$response = array(array(),'struct');
		}
		$response = array($tiket,'struct');
		return $this->xmlrpc->send_response($response);
	}

	function ComprasEmpresasAsociadas($request){
		$parameters = $request->output_parameters();

		$ult_ref=$parameters['0'];
		$cod_cli=$parameters['1'];
		$usr    =$parameters['2'];
		$pwd    =$parameters['3'];
		$cant   =5;

		$mSQL="SELECT numero,fecha,vence,TRIM(nfiscal) AS nfiscal,totals,totalg,iva FROM sfac WHERE cod_cli=? AND numero > ? AND tipo_doc='F' LIMIT $cant";
		$query = $this->db->query($mSQL,array($usr,$ult_ref));

		$compras=array();
		if ($query->num_rows() > 0){ 
			$pivot=array();
			foreach ($query->result_array() as $row){
				$numero=$row['numero'];
				//Prepara el encabezado
				foreach($row AS $ind=>$val){
					$row[$ind]=base64_encode($val);
				}
				$pivot['scst']=$row;
				//$compras[] = serialize($row);

				//Prepara los articulos
				$it=array();
				$mmSQL="SELECT TRIM(a.codigoa) AS codigoa,TRIM(a.desca) AS desca,SUM(a.cana) AS cana ,a.preca,SUM(a.tota) AS tota,a.iva,b.barras,b.precio1,b.precio1,b.precio1,b.precio1,b.unidad, b.tipo, b.tdecimal FROM sitems AS a JOIN sinv AS b ON a.codigoa=b.codigo WHERE numa=? AND tipoa='F' GROUP BY a.codigoa";
				$qquery = $this->db->query($mmSQL,array($numero));
				foreach ($qquery->result_array() as $rrow){
					foreach($rrow AS $ind=>$val){
						$rrow[$ind]=base64_encode($val);
					}
					$it[]=$rrow;
				}
				//$compras[] = serialize($it);
				$pivot['itscst']=$it;

				//Prepara el inventario
				/*$it=array();
				$mmSQL="SELECT TRIM(b.codigo) AS codigo,b.grupo,b.descrip,b.descrip2,b.unidad,b.ubica,b.tipo,b.clave,b.comision,b.enlace,b.pond,b.ultimo,b.existen,b.iva,b.fracci,b.codbar,b.barras,b.exmax,b.margen1,b.margen2,b.margen3,b.margen4,b.base1,b.base2,b.base3,b.base4,b.precio1,b.precio2,b.precio3,b.precio4,b.serial,b.tdecimal,b.redecen,b.formcal,b.fordeci,b.garantia,b.peso,b.pondcal,b.alterno,b.modelo,b.marca,clase,b.linea,b.depto,b.gasto,b.bonifica,b.bonicant,b.standard 
				FROM sitems AS a JOIN sinv AS b ON a.codigoa=b.codigo WHERE a.numa=? AND a.tipoa='F'";
				$qquery = $this->db->query($mmSQL,array($numero));
				foreach ($qquery->result_array() as $rrow){
					foreach($rrow AS $ind=>$val){
						$rrow[$ind]=base64_encode($val);
					}
					$it[]=$rrow;
				}
				$pivot['sinv']=$it;*/

				//$str = serialize($pivot);
				//$compras[]=gzcompress($str);
				$compras[]=serialize($pivot);
			}
		}

		$response = array($compras,'struct');
		//$str = serialize($compras);
		//$response = array($str,'string');
		return $this->xmlrpc->send_response($response);
	}

	function ProductosEmpresasAsociadas($request){
		/*$parameters = $request->output_parameters();

		$codigo =$parameters['0'];
		$usr    =$parameters['1'];
		$pwd    =$parameters['2'];

		$mSQL="SELECT codigo,grupo,descrip,descrip2,unidad,ubica,tipo,clave,comision,enlace,prov1,prepro1,pfecha1,prov2,prepro2,pfecha2,prov3,prepro3,pfecha3,pond,ultimo,pvp_s,pvp_bs,pvpprc,contbs,contprc,mayobs,mayoprc,exmin,exord,exdes,existen,fechav,fechac,iva,fracci,codbar,barras,exmax,margen1,margen2,margen3,margen4,base1,base2,base3,base4,precio1,precio2,precio3,precio4,serial,tdecimal,activo,dolar,redecen,formcal,fordeci,garantia,costotal,fechac2,peso,pondcal,alterno,aumento,modelo,marca,clase,oferta,fdesde,fhasta,derivado,cantderi,ppos1,ppos2,ppos3,ppos4,linea,depto,id,gasto,bonifica,bonicant,standard FROM sinv WHERE codigo=?";
		$query = $this->db->query($mSQL,array($codigo));

		$compras=array();
		if ($query->num_rows() > 0){ 
			foreach ($query->result_array() as $row){
				foreach($row AS $ind=>$val){
					$row[$ind]=base64_encode($val);
				}
				$sinv[] = serialize($row);
			}
		}

		$response = array($sinv,'struct');
		return $this->xmlrpc->send_response($response);*/
	}
}