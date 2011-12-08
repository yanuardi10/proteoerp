<?php
class Ajax extends Controller {

	function Ajax(){
		parent::Controller();
	}

	function index(){
		
	}

	//***************************************
	//           Auto complete
	//***************************************
	function buscasprv(){
		$mid  = $this->input->post('q');
		$qdb  = $this->db->escape('%'.$mid.'%');
		$qmid = $this->db->escape($mid);

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();
			
			//Cheque si existe el codigo
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rif) AS rif, proveed, direc1 AS direc, reteiva
				FROM sprv WHERE proveed=${qmid} LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() == 1){
				$row = $query->row_array();
				$retArray['value']   = $row['proveed'];
				$retArray['label']   = '('.$row['rif'].') '.utf8_encode($row['nombre']);
				$retArray['rif']     = $row['rif'];
				$retArray['nombre']  = utf8_encode($row['nombre']);
				$retArray['proveed'] = $row['proveed'];
				$retArray['direc']   = utf8_encode($row['direc']);
				$retArray['reteiva'] = $row['reteiva'];
				array_push($retorno, $retArray);
				$ww=" AND proveed<>${qmid}";
			}else{
				$ww='';
			}
			
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rif) AS rif, proveed, direc1 AS direc, reteiva
				FROM sprv WHERE rif LIKE ${qdb} OR nombre LIKE ${qdb} ${ww}
				ORDER BY rif LIMIT 10";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['proveed'];
					$retArray['label']   = '('.$row['rif'].') '.utf8_encode($row['nombre']);
					$retArray['rif']     = $row['rif'];
					$retArray['nombre']  = utf8_encode($row['nombre']);
					$retArray['proveed'] = $row['proveed'];
					$retArray['direc']   = utf8_encode($row['direc']);
					$retArray['reteiva'] = $row['reteiva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}

	function buscascli(){
		$mid  = $this->input->post('q');
		$qmid = $this->db->escape($mid);
		$qdb  = $this->db->escape('%'.$mid.'%');

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();

			//Cheque si existe el codigo
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo, dire11 AS direc
				FROM scli WHERE cliente=${qmid} LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() == 1){
				$row = $query->row_array();

				$retArray['value']   = $row['cliente'];
				$retArray['label']   = '('.$row['rifci'].') '.utf8_encode($row['nombre']);
				$retArray['rifci']   = $row['rifci'];
				$retArray['nombre']  = utf8_encode($row['nombre']);
				$retArray['cod_cli'] = $row['cliente'];
				$retArray['tipo']    = $row['tipo'];
				$retArray['direc']   = utf8_encode($row['direc']);
				array_push($retorno, $retArray);
				$ww=" AND cliente<>${qmid}";
			}else{
				$ww='';
			}

			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo , dire11 AS direc
				FROM scli WHERE (cliente LIKE ${qdb} OR rifci LIKE ${qdb} OR nombre LIKE ${qdb}) $ww
				ORDER BY rifci LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['cliente'];
					$retArray['label']   = '('.$row['rifci'].') '.utf8_encode($row['nombre']);
					$retArray['rifci']   = $row['rifci'];
					$retArray['nombre']  = utf8_encode($row['nombre']);
					$retArray['cod_cli'] = $row['cliente'];
					$retArray['tipo']    = $row['tipo'];
					$retArray['direc']   = utf8_encode($row['direc']);
					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	function buscasinv(){
		$mid  = $this->input->post('q');
		$qdb  = $this->db->escape('%'.$mid.'%');
		$qba  = $this->db->escape($mid);

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo
				,a.peso, a.ultimo, a.pond FROM sinv AS a
				LEFT JOIN barraspos AS b ON a.codigo=b.codigo
				WHERE (a.codigo LIKE $qdb OR a.descrip LIKE  $qdb OR a.barras LIKE $qdb OR b.suplemen=$qba) AND a.activo='S'
				ORDER BY a.descrip LIMIT 10";
			$cana=1;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].') '.$row['descrip'].' '.$row['precio1'].' Bs. - '.$row['existen'];
					$retArray['value']   = $row['codigo'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['cana']    = $cana;
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['base1']   = $row['precio1']*100/(100+$row['iva']);
					$retArray['base2']   = $row['precio2']*100/(100+$row['iva']);
					$retArray['base3']   = $row['precio3']*100/(100+$row['iva']);
					$retArray['base4']   = $row['precio4']*100/(100+$row['iva']);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	//Busca sinv solo articulos
	function buscasinvart(){
		$mid  = $this->input->post('q');
		$qdb  = $this->db->escape('%'.$mid.'%');
		$qba  = $this->db->escape($mid);

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.precio1,precio2,precio3,precio4, a.iva,a.existen,a.tipo
				,a.peso, a.ultimo, a.pond FROM sinv AS a
				LEFT JOIN barraspos AS b ON a.codigo=b.codigo
				WHERE (a.codigo LIKE $qdb OR a.descrip LIKE  $qdb OR a.barras LIKE $qdb OR b.suplemen=$qba) AND a.activo='S' AND a.tipo='Articulo'
				ORDER BY a.descrip LIMIT 10";
			$cana=1;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].') '.$row['descrip'].' '.$row['precio1'].' Bs. - '.$row['existen'];
					$retArray['value']   = $row['codigo'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['cana']    = $cana;
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['base1']   = $row['precio1']*100/(100+$row['iva']);
					$retArray['base2']   = $row['precio2']*100/(100+$row['iva']);
					$retArray['base3']   = $row['precio3']*100/(100+$row['iva']);
					$retArray['base4']   = $row['precio4']*100/(100+$row['iva']);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}


	//Busca facturas para aplicarles devolucion
	function buscasfacdev(){
		$mid   = $this->input->post('q');
		$scli  = $this->input->post('scli');
		$qdb   = $this->db->escape('%'.$mid.'%');
		$sclidb= $this->db->escape($scli);

		$data = '{[ ]}';

		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT a.numero, a.totalg, a.cod_cli, a.nombre,b.rifci, TRIM(b.nombre) AS nombre, TRIM(b.rifci) AS rifci, b.tipo, b.dire11 AS direc
				FROM  sfac AS a
				JOIN scli AS b ON a.cod_cli=b.cliente
				WHERE a.numero LIKE $qdb AND a.tipo_doc='F'
				ORDER BY numero DESC LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = $row['numero'].'-'.$row['nombre'].' '.$row['totalg'].' Bs.';
					$retArray['value']   = $row['numero'];
					$retArray['cod_cli'] = $row['cod_cli'];
					$retArray['rifci']   = $row['rifci'];
					$retArray['tipo']    = $row['tipo'];
					$retArray['direc']   = utf8_encode($row['direc']);
					$retArray['nombre']  = utf8_encode($row['nombre']);

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}else{
				$retArray[0]['label']   = 'No se consiguieron facturas para aplicar';
				$retArray[0]['value']   = '';
				$retArray[0]['cod_cli'] = '';
				$retArray[0]['nombre']  = '';
				$data = json_encode($retArray);
			}
		}
		echo $data;
	}

	//Busca las formas de pago de una factura para devolverlos
	function buscasfpadev(){
		$mid = $this->input->post('q');

		$data = '{[ ]}';
		if($mid !== false){
			$dbfactura = $this->db->escape($mid);
			$retArray = $retorno = array();
			$mSQL="SELECT
					SUM(monto) AS monto
				FROM sfpa AS a
				WHERE a.tipo_doc='FC' AND numero=$dbfactura";
			$cana=1;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $id=>$row ) {
					$retArray['tipo']    = 'EF';
					$retArray['monto']   = $row['monto'];
					$retArray['num_ref'] = '';
					$retArray['banco']   = '';
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	//Busca los articulos de una factura para devolverlos
	function buscasinvdev(){
		$mid = $this->input->post('q');

		$data = '{[ ]}';
		if($mid !== false){
			$dbfactura = $this->db->escape($mid);
			$retArray = $retorno = array();
			$mSQL="SELECT TRIM(a.descrip) AS descrip,b.cana,TRIM(a.codigo) AS codigo, a.precio1,a.precio2,a.precio3,a.precio4,
				a.iva,a.existen,a.tipo,a.peso, a.ultimo, a.pond
				FROM sinv AS a
				JOIN sitems AS b ON a.codigo=b.codigoa AND b.tipoa='F' AND b.numa=$dbfactura
				ORDER BY a.descrip";
			$cana=1;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $id=>$row ) {
					$retArray['codigo']  = utf8_encode($row['codigo']);
					$retArray['cana']    = $row['cana'];
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['base1']   = $row['precio1']*100/(100+$row['iva']);
					$retArray['base2']   = $row['precio2']*100/(100+$row['iva']);
					$retArray['base3']   = $row['precio3']*100/(100+$row['iva']);
					$retArray['base4']   = $row['precio4']*100/(100+$row['iva']);
					$retArray['descrip'] = utf8_encode($row['descrip']);
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	function buscacpla(){
		$mid   = $this->input->post('q');
		$qdb   = $this->db->escape($mid.'%');

		$data = '{[ ]}';
		if($mid !== false){
			$qformato=$this->datasis->formato_cpla();
			$retArray = $retorno = array();

			$mSQL="SELECT codigo, descrip, departa, ccosto
			FROM cpla WHERE codigo LIKE $qdb AND codigo LIKE \"$qformato\"";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']    = $row['codigo'].'-'.utf8_encode($row['descrip']);
					$retArray['value']    = $row['codigo'];
					$retArray['descrip']  = utf8_encode($row['descrip']);
					$retArray['departa']  = $row['departa'];
					$retArray['ccosto']   = $row['ccosto'];

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}else{
				$retArray[0]['label']    = 'No se consiguieron cuentas';
				$retArray[0]['value']    = '';
				$retArray[0]['descrip']  = '';
				$retArray[0]['departa']  = '';
				$retArray[0]['ccosto']   = '';

				$data = json_encode($retArray);
			}
		}
		echo $data;
	}

	//Autocomplete para buscar las reservaciones
	function buscares(){
		$mid   = $this->input->post('q');
		$qdb   = $this->db->escape('%'.$mid.'%');
		$scli  = $this->input->post('scli');

		$data = '{[ ]}';
		if($mid !== false){
			$qformato=$this->datasis->formato_cpla();
			$retArray = $retorno = array();
			if(!empty($scli)) $ww='AND cliente='.$this->db->escape($scli); else $ww='';

			$mSQL="SELECT a.id,a.numero,a.fecha,a.cliente,a.edificacion,a.inmueble,a.reserva,b.nombre,b.rifci, b.tipo AS sclitipo,dire11 AS direc,c.uso
			FROM edres AS a
			JOIN scli AS b ON a.cliente=b.cliente
			JOIN edinmue AS c ON a.inmueble=c.id
			WHERE numero LIKE $qdb  $ww";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']    = $row['numero'].'-'.utf8_encode($row['nombre']);
					$retArray['value']    = $row['numero'];
					$retArray['nombre']   = utf8_encode($row['nombre']);
					$retArray['edifi']    = $row['edificacion'];
					$retArray['inmue']    = $row['inmueble'];
					$retArray['rifci']    = $row['rifci'];
					$retArray['cliente']  = $row['cliente'];
					$retArray['sclitipo'] = $row['sclitipo'];
					$retArray['direc']    = $row['direc'];
					$retArray['uso']      = $row['uso'];

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}else{
				$retArray['label']    = 'No se consiguieron reservaciones';
				$retArray['value']    = '';
				$retArray['nombre']   = '';
				$retArray['edifi']    = '';
				$retArray['inmue']    = '';
				$retArray['rifci']    = '';
				$retArray['cliente']  = '';
				$retArray['sclitipo'] = '';
				$retArray['direc']    = '';
				$retArray['uso']      = '';

				$data = json_encode($retArray);
			}
		}
		echo $data;
	}

	//Autocomplete para mgas
	function automgas(){
		$mid   = $this->db->escape('%'.$this->input->post('q').'%');
		//$proveed  = $this->input->post('sprv');
		$data = '{[ ]}';
		if(true){

			$mSQL  = "SELECT a.codigo, a.descrip
				FROM mgas AS a
			WHERE a.codigo LIKE ${mid} OR a.descrip LIKE ${mid} ORDER BY a.descrip LIMIT 10";

			$query = $this->db->query($mSQL);
			$retArray = array();
			$retorno = array();
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']      = $row['codigo'];
					$retArray['label']      = trim($row['codigo']).' - '.trim($row['descrip']);
					$retArray['codigo']     = trim($row['codigo']);
					$retArray['descrip']    = trim($row['descrip']);
					//$retArray['tari1']      = $row['tari1'];
					//$retArray['retecodigo'] = trim($row['retecodigo']);
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
	}

	//Para cerrar la ventana luego de una operacion exitosa
	function reccierraventana(){
		$script='
		<script language="javascript" type="text/javascript">
		$(function(){
			$(window).unload(function() { window.opener.location.reload(); });
			window.close();
		});
		</script>';

		$data['content'] = '<center>Operaci&oacute;n Exitosa</center>';
		$data['head']    = script('jquery.js').$script;
		$data['title']   = '';
		$this->load->view('view_ventanas', $data);
	}
}