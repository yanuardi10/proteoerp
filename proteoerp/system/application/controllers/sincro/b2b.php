<?php
class b2b extends Controller {

	function b2b(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(135,1);
	}

	function index(){
		//Aqui va el filtered grid
	}

	function dataedit{
		//aqui va el dataedit
	}

	function cargar(){
		//Aqui va el GUI de la carga
		$this->_cargacompra(1); // esta linea de elimina
	}


	function trae_compra($id=null){
		if(is_null($id)) return false; else $id=$this->db->escape($id);

		$config=$this->datasis->damerow("SELECT proveed,url,usuario,clave,tipo,depo,margen1,margen2,margen3,margen4,margen5 FROM b2b_config WHERE id=$id");
		if(count($config)==0) return false;

		$this->load->helper('url');
		$server_url = $config['url'].'/'.'rpcserver';

		$this->load->library('xmlrpc');
		$this->xmlrpc->xmlrpc_defencoding=$this->config->item('charset');
		//$this->xmlrpc->set_debug(TRUE);

		$this->xmlrpc->server($server_url, 80);
		$this->xmlrpc->method('cea');

		$ufac=$this->datasis->dameval('SELECT MAX(numero) FROM b2b_scst WHERE proveed='.$this->db->escape($config['clave']));
		if(empty($ufac)) $ufac=0;

		$request = array($ufac,$config['proveed'],$config['usuario'],$config['clave']);
		$this->xmlrpc->request($request);

		if (!$this->xmlrpc->send_request()){
			echo $this->xmlrpc->display_error();
		}else{
			$res=$this->xmlrpc->display_response();
			foreach($res AS $ind=>$compra){
				$arr=unserialize($compra);
				if($ind % 2==1){
					if(!$maestro) continue;
					foreach($arr AS $in => $aarr){
						foreach($aarr AS $i=>$val)
							$arr[$in][$i]=base64_decode($val);
						$ddata['id_scst']  = $id_scst;
						$ddata['proveed']  = $proveed;
						$ddata['fecha']    = $data['fecha'];
						$ddata['numero']   = $data['numero'];
						$ddata['depo']     = $data['depo'];
						$ddata['codigo']   = $arr[$in]['codigoa'];
						$ddata['descrip']  = $arr[$in]['desca'];
						$ddata['cantidad'] = $arr[$in]['cana'];
						$ddata['costo']    = $arr[$in]['preca'];
						$ddata['importe']  = $arr[$in]['tota'];
						$ddata['garantia'] = 0;
						$ddata['ultimo']   = $arr[$in]['tota']*$arr[$in]['preca'];
						$ddata['precio1']  = ($arr[$in]['tota']*100/(100-$config['margen1']))*(1+$arr[$in]['iva']/100);
						$ddata['precio2']  = ($arr[$in]['tota']*100/(100-$config['margen2']))*(1+$arr[$in]['iva']/100);
						$ddata['precio3']  = ($arr[$in]['tota']*100/(100-$config['margen3']))*(1+$arr[$in]['iva']/100);
						$ddata['precio4']  = ($arr[$in]['tota']*100/(100-$config['margen4']))*(1+$arr[$in]['iva']/100);
						$ddata['montoiva'] = $arr[$in]['tota']*($arr[$in]['iva']/100);
						$ddata['iva']      = $arr[$in]['iva'];

						$mSQL=$this->db->insert_string('b2b_itscst',$ddata);
						//echo $mSQL."\n";
						$rt=$this->db->simple_query($mSQL);
						if(!$rt){
							memowrite($mSQL,'B2B');
						}
					}
					//print_r($arr);
				}else{
					foreach($arr AS $in => $val)
						$arr[$in]=base64_decode($val);

					//$control=$this->datasis->fprox_numero('nscst');
					//$transac=$this->datasis->fprox_numero('ntransac');
					$proveed=$config['proveed'];
					$pnombre=$this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($proveed));

					$data['proveed']  = $proveed;
					$data['nombre']   = $pnombre;
					$data['tipo_doc'] = 'FC';
					$data['depo']     = $config['depo'];
					$data['fecha']    = $arr['fecha'];
					$data['numero']   = $arr['numero'];
					$data['serie']    = $arr['nfiscal'];
					$data['montotot'] = 0;
					$data['montoiva'] = 0;
					$data['montonet'] = 0;
					$mSQL=$this->db->insert_string('b2b_scst',$data);
					//echo $mSQL."\n";
					$rt=$this->db->simple_query($mSQL);
					if(!$rt){
						memowrite($mSQL,'B2B');
						$maestro=false;
					}else{
						$id_scst=$this->db->insert_id();
						$maestro=true;
					}

					//print_r($arr);
				}
			}
			echo '</pre>';
		}
	}

	function _cargacompra($id){
		$query=$this->db->query("SELECT a.codigo FROM b2b_itscst AS a LEFT JOIN sinv AS b ON a.codigo=b.codigo WHERE a.numero IS NULL AND a.id_scst=?",array($id));
		if ($query->num_rows() > 0){
			$row = $query->row();
			//$row->codigo;
		}

		$cana=$this->datasis->dameval('SELECT COUNT(*) FROM b2b_itscst AS a LEFT JOIN sinv AS b ON a.codigo=b.codigo WHERE a.numero IS NULL AND id_scst='.$this->db->escape($id));
		if($cana==0){
			$control=$this->datasis->fprox_numero('nscst');
			$transac=$this->datasis->fprox_numero('ntransac');
			$tt['montotot']=$tt['montoiva']=$tt['montonet']=0;

			$query = $this->db->query('SELECT fecha,numero,proveed,depo,codigo,descrip,cantidad,devcant,devfrac,costo,importe,iva,montoiva,garantia,ultimo,precio1,precio2,precio3,precio4,licor FROM b2b_itscst WHERE id_scst=?',array($id));
			if ($query->num_rows() > 0){
				foreach ($query->result_array() as $itrow){
					$itrow['estampa'] = date('Y-m-d');
					$itrow['hora']    = date('h:m:s');
					$itrow['control'] = $control;
					$itrow['transac'] = $transac;

					$tt['montotot']+=$itrow['importe'];
					$tt['montoiva']+=$itrow['montoiva'];
					$tt['montonet']+=$itrow['importe']+$itrow['montoiva'];

					$mSQL=$this->db->insert_string('itscst',$itrow);
					//echo $mSQL;
					$rt=$this->db->simple_query($mSQL);
					if(!$rt){
						memowrite($mSQL,'B2B');
					}
				}
			}

			$query = $this->db->query('SELECT fecha,numero,depo,proveed,nombre,montotot,montoiva,montonet,vence,tipo_doc,peso,usuario,nfiscal,exento,sobretasa,reducida,tasa,montasa,monredu,monadic,serie FROM b2b_scst WHERE id=?',array($id));
			if ($query->num_rows() > 0){
				$row = $query->row_array();
				$row['estampa'] = date('Y-m-d');
				$row['hora']    = date('h:m:s');
				$row['control'] = $control;
				$row['transac'] = $transac;
				$row['usuario'] = $this->session->userdata('usuario');
				$row['montotot'] =$tt['montotot'];
				$row['montoiva'] =$tt['montoiva'];
				$row['montonet'] =$tt['montonet'];
				
				$mSQL=$this->db->insert_string('scst',$row);
				//echo $mSQL;
				$rt=$this->db->simple_query($mSQL);
				if(!$rt){
					memowrite($mSQL,'B2B');
				}
			}

			$mSQL="UPDATE b2b_scst SET control='$control' WHERE id=".$this->db->escape($id);
			$rt=$this->db->simple_query($mSQL);
			if(!$rt){
				memowrite($mSQL,'B2B');
			}
		
		}
	}

	function _cargagasto(){
		
		
	}

	function instala(){
		$mSQL="CREATE TABLE `b2b_config` (  `id` int(10) NOT NULL,  `proveed` char(5) COLLATE latin1_general_ci NOT NULL COMMENT 'Codigo del proveedor',  `url` varchar(100) COLLATE latin1_general_ci NOT NULL,  `usuario` varchar(100) COLLATE latin1_general_ci NOT NULL COMMENT 'Codigo de cliente en el proveedor',  `clave` varchar(100) COLLATE latin1_general_ci NOT NULL,  `tipo` char(1) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'I para inventario G para gasto',  `depo` varchar(4) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Almacen',  `margen1` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio1',  `margen2` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio 2',  `margen3` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio3',  `margen4` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio4',
		 `margen5` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio5 (solo supermercado)',
		 `grupo` varchar(5) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Grupo por defecto',
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Configuracion para los b2b'";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="CREATE TABLE `b2b_itscst` (  `id_scst` int(11) DEFAULT NULL,  `fecha` date DEFAULT NULL,  `numero` varchar(8) DEFAULT NULL,  `proveed` varchar(5) DEFAULT NULL,  `depo` varchar(4) DEFAULT NULL,  `codigo` varchar(15) DEFAULT NULL,  `descrip` varchar(45) DEFAULT NULL,  `cantidad` decimal(10,3) DEFAULT NULL,  `devcant` decimal(10,3) DEFAULT NULL,  `devfrac` int(4) DEFAULT NULL,  `costo` decimal(17,2) DEFAULT NULL,  `importe` decimal(17,2) DEFAULT NULL,  `iva` decimal(5,2) DEFAULT NULL,  `montoiva` decimal(17,2) DEFAULT NULL,  `garantia` int(3) DEFAULT NULL,  `ultimo` decimal(17,2) DEFAULT NULL,  `precio1` decimal(15,2) DEFAULT NULL,  `precio2` decimal(15,2) DEFAULT NULL,  `precio3` decimal(15,2) DEFAULT NULL,  `precio4` decimal(15,2) DEFAULT NULL,  `estampa` date DEFAULT NULL,  `hora` varchar(8) DEFAULT NULL,  `usuario` varchar(12) DEFAULT NULL,  `licor` decimal(10,2) DEFAULT '0.00',  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`id`),
		  KEY `id_scst` (`id_scst`),
		  KEY `fecha` (`fecha`),
		  KEY `codigo` (`codigo`),
		  KEY `proveedor` (`proveed`),
		  KEY `numero` (`numero`)
		) ENGINE=MyISAM AUTO_INCREMENT=209 DEFAULT CHARSET=latin1";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="CREATE TABLE `b2b_scst` (  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,  `fecha` date DEFAULT NULL,  `numero` varchar(8) DEFAULT NULL,  `proveed` varchar(5) DEFAULT NULL,  `nombre` varchar(30) DEFAULT NULL,  `depo` varchar(4) DEFAULT NULL,  `montotot` decimal(17,2) DEFAULT NULL,  `montoiva` decimal(17,2) DEFAULT NULL,  `montonet` decimal(17,2) DEFAULT NULL,  `vence` date DEFAULT NULL,  `tipo_doc` char(2) DEFAULT NULL,  `control` varchar(8) NOT NULL DEFAULT '',  `peso` decimal(12,2) DEFAULT NULL,  `estampa` date DEFAULT NULL,  `hora` varchar(8) DEFAULT NULL,  `usuario` varchar(12) DEFAULT NULL,  `nfiscal` varchar(12) DEFAULT NULL,  `exento` decimal(17,2) NOT NULL DEFAULT '0.00',  `sobretasa` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `reducida` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `tasa` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `montasa` decimal(17,2) DEFAULT NULL,
		  `monredu` decimal(17,2) DEFAULT NULL,
		  `monadic` decimal(17,2) DEFAULT NULL,
		  `serie` char(12) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `proveedor` (`proveed`)
		) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1";
		var_dump($this->db->simple_query($mSQL));
	}
}


/*if ($query->num_rows()==1){
	$lcontrol=$this->datasis->fprox_numero('nscst');
	$transac =$this->datasis->fprox_numero('ntransac');

	$row=$query->row_array();
	$row['control']=$lcontrol;
	$row['transac']=$transac;
	$row['nfiscal']=$nfiscal;
	$row['depo']   =$almacen;
	$row['vence']  =$vence;
	unset($row['pcontrol']);

	$mSQL[]=$this->db->insert_string('scst', $row);

	$itquery = $farmaxDB->query("SELECT * FROM itscst WHERE control=$control");
	foreach ($itquery->result_array() as $itrow){
		$itrow['control']=$lcontrol;
		unset($itrow['id']);
		$mSQL[]=$this->db->insert_string('itscst', $itrow);
	}
	foreach($mSQL AS $sql){
		$rt=$this->db->simple_query($sql);
		if(!$rt){ memowrite('scstfarma',$sql);}
	}
	$sql="UPDATE scst SET pcontrol='${lcontrol}' WHERE control=$control";
	$rt=$farmaxDB->simple_query($sql);
	if(!$rt) memowrite('farmaejec',$sql);

	$mSQL="UPDATE 
	  ${localdb}.itscst AS a
	  JOIN ${localdb}.farmaxasig AS b ON a.codigo=b.barras AND a.proveed=b.proveed
	  SET a.codigo=b.abarras
	WHERE a.control='$lcontrol'";
	$rt=$this->db->simple_query($mSQL);
	if(!$rt){ memowrite('farmaejec1',$sql);}

	$retorna='Compra guardada con el control '.anchor("compras/scst/dataedit/show/$lcontrol",$lcontrol);
}else{
	$retorna="Al parecer la factura fue ya pasada";
}*/
