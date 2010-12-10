<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
class b2b extends validaciones {

	function b2b(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(135,1);
	}

	function index(){
		//Aqui va el filtered grid
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$mGRUP=array(
			'tabla'   =>'grup',
			'columnas'=>array(
				'grupo' =>'Grupo',
				'nom_grup'=>'Nombre'),
			'filtro'  =>array('grupo'=>'Grupo','nom_grup'=>'Nombre'),
			'retornar'=>array('grupo'=>'grupo'),
			'titulo'  =>'Buscar Grupo');
		$bGRUP=$this->datasis->modbus($mGRUP);

		$filter = new DataFilter('Filtro de b2b');
		$filter->db->select(array('a.id','a.proveed','a.usuario','a.depo AS depo','a.tipo','a.url','a.grupo','b.nombre','c.ubides'));
		$filter->db->from('b2b_config AS a');
		$filter->db->join('sprv AS b','b.proveed=a.proveed COLLATE latin1_swedish_ci','left');
		$filter->db->join('caub AS c','c.ubica=a.depo COLLATE latin1_swedish_ci','left');

		$filter->proveed = new inputField('Proveedor', 'proveed');
		$filter->proveed->append($bSPRV);
		$filter->proveed->size=25;

		$filter->depo = new dropdownField('Almac&eacute;n','depo');
		$filter->depo->option("","Seleccionar");
		$filter->depo->options("SELECT ubica, ubides FROM caub ORDER BY ubica");

		$filter->tipo = new dropdownField('Tipo','tipo');
		$filter->tipo->option('' ,'Selecione un tipo');
		$filter->tipo->option('I','Inventario');
		$filter->tipo->option('G','Gastos');

		$filter->grupo = new inputField('Grupo', 'grupo');
		$filter->grupo->append($bGRUP);
		$filter->grupo->size=25;
		
		$filter->buttons('reset','search');
		$filter->build();

		$link=anchor('/sincro/b2b/dataedit/show/<#id#>','<#id#>');
		$grid = new DataGrid('b2b');
		$grid->order_by('id','asc');
		$grid->per_page = 15;

		$grid->column_orderby('N&uacute;mero',$link    ,'id');
		$grid->column_orderby('Proveedor'    ,'nombre' ,'proveed');
		$grid->column_orderby('Url'          ,'url'    ,'url');
		$grid->column_orderby('Usuario'      ,'usuario','usuario');
		$grid->column_orderby('Clave'        ,'clave'  ,'clave');
		$grid->column_orderby('Tipo'         ,'tipo'   ,'tipo');
		$grid->column_orderby('Almacen'      ,'ubides' ,'depo');
		$grid->column_orderby('Grupo'        ,'grupo'  ,'grupo');

		$grid->add('sincro/b2b/dataedit/create');
		$grid->build();

		$data['content'] =$filter->output. $grid->output;
		$data['title']   = '<h1>B2B</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		//aqui va el dataedit
		$this->rapyd->load('dataedit');

		$mGRUP=array(
			'tabla'   =>'grup',
			'columnas'=>array(
				'grupo' =>'Grupo',
				'nom_grup'=>'Nombre'),
			'filtro'  =>array('grupo'=>'Grupo','nom_grup'=>'Nombre'),
			'retornar'=>array('grupo'=>'grupo'),
			'titulo'  =>'Buscar Grupo');
		$bGRUP=$this->datasis->modbus($mGRUP);

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$script='
		<script language="javascript" type="text/javascript">
		$(function(){
				$(".inputnum").numeric(".");
		});
		</script>';

		$edit = new DataEdit('B2B', 'b2b_config');
		$edit->back_url = site_url('sincro/b2b/index/');

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->size      =  15;
		$edit->proveed->maxlength =  15;
		$edit->proveed->rule      = 'required';
		$edit->proveed->append($bSPRV);

		$edit->url = new inputField('Direcci&oacute;n Url', 'url');
		$edit->url->size      =  50;
		$edit->url->maxlength =  50;
		$edit->url->rule      = 'required';

		$edit->usuario = new inputField('Usuario', 'usuario');
		$edit->usuario->size      =  20;
		$edit->usuario->maxlength =  20;
		$edit->usuario->rule      = 'required';

		$edit->clave = new inputField('Clave', 'clave');
		$edit->clave->size      =  10;
		$edit->clave->maxlength =  10;
		$edit->clave->rule      = 'required';

		$edit->tipo = new dropdownField('Tipo','tipo');
		$edit->tipo->option('' ,'Seleccione un tipo');
		$edit->tipo->option('I','Inventario');
		$edit->tipo->option('G','Gastos');
		$edit->tipo->style ='50px';
		
		$edit->tipo->rule  ='required';

		$edit->depo = new dropdownField('Almac&eacute;n','depo');
		$edit->depo->option('','Seleccionar');
		$edit->depo->options('SELECT ubica,ubides FROM caub');
		$edit->depo->style ='250px';
		$edit->depo->rule  ='required';

		for($i=1;$i<=5;$i++){
			$obj='margen'.$i;
			$edit->$obj = new inputField('Margen '.$i, $obj);
			$edit->$obj->size      = 15;
			$edit->$obj->maxlength = 15;
			$edit->$obj->css_class = 'inputnum';
			$edit->$obj->rule      = 'callback_chporcent';
			$edit->$obj->group = 'Margenes de ganancia';
			if($i==5) $edit->$obj->append('Solo aplica a supermercados');
		}

		$edit->grupo = new inputField('Grupo', 'grupo');
		$edit->grupo->size      =  10;
		$edit->grupo->maxlength =  6;
		$edit->grupo->rule      = 'required';
		$edit->grupo->append($bGRUP);

		$edit->buttons('modify', 'save','undo', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().$script;
		$data['title']   = '<h1>Editar b2b</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function traecompra($par){
		//Aqui va el GUI de la carga
		$this->_cargacompra($par); // esta linea de elimina
	}


	function trae_compra($id=null){
		if(is_null($id)) return false; else $id=$this->db->escape($id);

		$config=$this->datasis->damerow("SELECT proveed,puerto,proteo,url,usuario,clave,tipo,depo,margen1,margen2,margen3,margen4,margen5 FROM b2b_config WHERE id=$id");
		if(count($config)==0) return false;

		$this->load->helper('url');
		$server_url = reduce_double_slashes($config['url'].'/'.$config['proteo'].'/'.'rpcserver');

		$this->load->library('xmlrpc');
		$this->xmlrpc->xmlrpc_defencoding=$this->config->item('charset');
		$this->xmlrpc->set_debug(TRUE);
		$puerto= (empty($config['puerto'])) ? 80 : $config['puerto'];

		$this->xmlrpc->server($server_url , $puerto);
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
				//print_r($arr);

				foreach($arr['scst'] AS $in => $val)
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
				//$maestro=false;
				//$id_scst=0;

				$rt=$this->db->simple_query($mSQL);
				if(!$rt){
					memowrite($mSQL,'B2B');
					$maestro=false;
				}else{
					$id_scst=$this->db->insert_id();
					$maestro=true;
				}

				if($maestro){
					$itscst =& $arr['itscst'];
					foreach($itscst AS $in => $aarr){
						foreach($aarr AS $i=>$val)
							$arr[$in][$i]=base64_decode($val);

						$barras=trim($arr[$in]['barras']);
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
						$ddata['precio1']  = $arr[$in]['precio1'];
						$ddata['precio2']  = $arr[$in]['precio2'];
						$ddata['precio3']  = $arr[$in]['precio3'];
						$ddata['precio4']  = $arr[$in]['precio4'];
						$ddata['montoiva'] = $arr[$in]['tota']*($arr[$in]['iva']/100);
						$ddata['iva']      = $arr[$in]['iva'];
						$ddata['barras']   = $barras;

						//procedimiento de determinacion del codigo del articulo en sistema
						$codigolocal=false;
						if(!empty($barras)){
							$mSQL_p = 'SELECT codigo FROM sinv';
							$bbus   = array('codigo','barras','alterno');
							$query=$this->_gconsul($mSQL_p,$barras,$bbus);
							if($query){
								$row = $query->row();
								print_r($row);
								$codigolocal=$row['codigo'];
							}
						}
						if($codigolocal===false AND $this->db->table_exists('sinvprov')){
							$codigolocal=$this->datasis->dameval('SELECT codigo FROM sinvprov WHERE proveed='.$this->db->escape($proveed).' AND codigop='.$this->db->escape($arr[$in]['codigoa']));
						}

						//Si no existe lo crea
						if(empty($codigolocal)){
							$invent['codigo']   = $barras;
							$invent['grupo']    = $config['grupo'];
							$invent['descrip']  = $arr[$in]['descrip'];
							$invent['cantidad'] = $arr[$in]['cana'];
							$invent['pond']     = $arr[$in]['preca'];
							$invent['ultimo']   = $arr[$in]['preca'];
							$invent['unidad']   = $arr[$in]['unidad'];
							$invent['tipo']     = $arr[$in]['tipo'];
							$invent['redecen']  = 'N';
							$invent['formcal']  = 'U';
							$invent['clase']    = 'C';
							$invent['tipo']     = 'S';

							$base1 =($arr[$in]['precio1']*100)/(100+$arr[$in]['iva']);
							$base2 =($arr[$in]['precio2']*100)/(100+$arr[$in]['iva']);
							$base3 =($arr[$in]['precio3']*100)/(100+$arr[$in]['iva']);
							$base4 =($arr[$in]['precio4']*100)/(100+$arr[$in]['iva']);

							$invent['garantia'] = 0;
							$invent['ultimo']   = $arr[$in]['tota']*$arr[$in]['preca'];
							$invent['margen1']  = 100-($arr[$in]['preca']*100/$base1);
							$invent['margen2']  = 100-($arr[$in]['preca']*100/$base2);
							$invent['margen3']  = 100-($arr[$in]['preca']*100/$base3);
							$invent['margen4']  = 100-($arr[$in]['preca']*100/$base4);
							$invent['base1']    = $base1;
							$invent['base2']    = $base2;
							$invent['base3']    = $base3;
							$invent['base4']    = $base4;
							$invent['precio1']  = $arr[$in]['precio1'];
							$invent['precio2']  = $arr[$in]['precio2'];
							$invent['precio3']  = $arr[$in]['precio3'];
							$invent['precio4']  = $arr[$in]['precio4'];
							$invent['montoiva'] = $arr[$in]['tota']*($arr[$in]['iva']/100);
							$invent['iva']      = $arr[$in]['iva'];

							$mSQL=$this->db->insert_string('sinv',$invent);
							$rt=$this->db->simple_query($mSQL);
							if(!$rt){
								memowrite($mSQL,'B2B');
							}else{
								$codigolocal=$this->db->insert_id();
							}
						}
						$ddata['codigolocal'] = $codigolocal;

						//$ddata['precio1']  = ($arr[$in]['tota']*100/(100-$config['margen1']))*(1+$arr[$in]['iva']/100);
						//$ddata['precio2']  = ($arr[$in]['tota']*100/(100-$config['margen2']))*(1+$arr[$in]['iva']/100);
						//$ddata['precio3']  = ($arr[$in]['tota']*100/(100-$config['margen3']))*(1+$arr[$in]['iva']/100);
						//$ddata['precio4']  = ($arr[$in]['tota']*100/(100-$config['margen4']))*(1+$arr[$in]['iva']/100);

						$mSQL=$this->db->insert_string('b2b_itscst',$ddata);
						//echo $mSQL."\n";

						$rt=$this->db->simple_query($mSQL);
						if(!$rt){
							memowrite($mSQL,'B2B');
						}
					}

					//Carga el inventario
					$ddata=array();
					$sinv=&$arr['sinv'];
					foreach($sinv as $in => $aarr){
						foreach($aarr AS $i=>$val)
							$sinv[$in][$i]=base64_decode($val);
						$sinv[$in]['proveed']  = $proveed;
						$mSQL=$this->db->insert_string('b2b_sinv',$sinv[$in]);
						$mSQL.=' ON DUPLICATE KEY UPDATE precio1='.$sinv[$in]['precio1'].',precio2='.$sinv[$in]['precio2'].',precio3='.$sinv[$in]['precio3'].',precio4='.$sinv[$in]['precio4'];
						//echo $mSQL."\n";

						$rt=$this->db->simple_query($mSQL);
						if(!$rt){
							memowrite($mSQL,'B2B');
						}
					}
				}
			}
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
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1";
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
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1";
		var_dump($this->db->simple_query($mSQL));
	}

	function scstfilter(){
		$this->rapyd->load('datagrid','datafilter');
		$this->rapyd->uri->keep_persistence();

		$atts = array(
				'width'      => '800',
				'height'     => '600',
				'scrollbars' => 'yes',
				'status'     => 'yes',
				'resizable'  => 'yes',
				'screenx'    => '0',
				'screeny'    => '0'
			);

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de Compras');
		$filter->db->select=array('numero','fecha','vence','nombre','montoiva','montonet','proveed','control');
		$filter->db->from('b2b_scst');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";

		$filter->numero = new inputField('Factura', 'numero');
		$filter->numero->size=20;

		$filter->proveedor = new inputField('Proveedor','proveed');
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = 'proveed';
		$filter->proveedor->size=20;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('sincro/b2b/scstedit/show/<#id#>','<#numero#>');

		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 15;

		$grid->column_orderby('Factura',$uri,'control');
		$grid->column_orderby('Fecha'  ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha',"align='center'");
		$grid->column_orderby('Vence'  ,'<dbdate_to_human><#vence#></dbdate_to_human>','vence',"align='center'");
		$grid->column_orderby('Nombre' ,'nombre','nombre');
		$grid->column_orderby('IVA'    ,'montoiva' ,'montoiva',"align='right'");
		$grid->column_orderby('Monto'  ,'montonet' ,'montonet',"align='right'");
		$grid->column_orderby('Control','pcontrol' ,'pcontrol',"align='right'");

		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='<h1>Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function scstedit(){
		$this->rapyd->load('dataedit','datadetalle','fields','datagrid');
		$this->rapyd->uri->keep_persistence();

		function exissinv($cen,$id=0){
			if(empty($cen)){
				$id--;
				$rt =form_button('create' ,'Crear','onclick="pcrear('.$id.');"');
				$rt.=form_button('asignar','Asig.','onclick="pasig('.$id.');"');
			}else{
				$rt='--';
			}
			return $rt;
		}

		$edit = new DataEdit('Compras','b2b_scst');
		$edit->back_url = 'farmacia/scst/datafilter/';

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->mode ='autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 15;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;

		$edit->proveedor = new inputField("Proveedor", "proveed");
		$edit->proveedor->size = 10;
		$edit->proveedor->maxlength=5;

		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;

		$edit->almacen = new inputField("Almac&eacute;n", "depo");
		$edit->almacen->size = 15;
		$edit->almacen->maxlength=8;

		$edit->tipo = new dropdownField("Tipo", "tipo_doc");
		$edit->tipo->option("FC","FC");
		$edit->tipo->rule = "required";
		$edit->tipo->size = 20;
		$edit->tipo->style='width:150px;';

		$edit->subt  = new inputField("Sub-total", "montotot");
		$edit->subt->size = 20;
		$edit->subt->css_class='inputnum';

		$edit->iva  = new inputField("Impuesto", "montoiva");
		$edit->iva->size = 20;
		$edit->iva->css_class='inputnum';

		$edit->total  = new inputField("Total global", "montonet");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';

		$edit->pcontrol  = new inputField('Control', 'pcontrol');
		$edit->pcontrol->size = 12;

		//$numero =$edit->_dataobject->get('control');
		$id =$edit->_dataobject->get('id');
		$proveed=$this->db->escape($edit->_dataobject->get('proveed'));

		$atts = array(
			'width'     => '250',
			'height'    => '250',
			'scrollbars'=> 'no',
			'status'    => 'no',
			'resizable' => 'no',
			'screenx'   => "'+((screen.availWidth/2)-175)+'",
			'screeny'   => "'+((screen.availHeight/2)-175)+'"
		);
		$llink=anchor_popup('farmacia/scst/reasignaprecio/modify/<#id#>', '<b><#precio1#></b>', $atts);

		//Campos para el detalle
		$tabla=$this->db->database;
		$detalle = new DataGrid('');
		$select=array('a.*','a.codigo AS barras','a.costo AS pond','COALESCE( b.codigo , c.abarras) AS sinv','a.codigolocal');
		$detalle->db->select($select);
		$detalle->db->from('b2b_itscst AS a');
		$detalle->db->where('a.id_scst',$id);
		$detalle->db->join('sinv AS b','a.codigo=b.codigo','LEFT');
		$detalle->db->join('farmaxasig AS c',"a.codigo=c.barras AND c.proveed=$proveed",'LEFT');
		$detalle->use_function('exissinv');
		$detalle->column('Codigo sistema'     ,'<#codigolocal#>' );
		$detalle->column('Barras'            ,'<#codigo#>' );
		$detalle->column('Descripci&oacute;n','<#descrip#>');
		$detalle->column('Cantidad'          ,'<#cantidad#>',"align='right'");
		$detalle->column('PVP'               ,$llink  ,"align='right'");
		$detalle->column('Costo'             ,'<#ultimo#>'  ,"align='right'");
		$detalle->column('Importe'           ,'<#importe#>' ,"align='right'");
		$detalle->column('Acciones'          ,"<exissinv><#sinv#>|<#dg_row_id#></exissinv>","bgcolor='#D7F7D7' align='center'");
		$detalle->build();
		//echo $detalle->db->last_query();

		$script='
		function pcrear(id){
			var pasar=["barras","descrip","ultimo","iva","codigo","pond","precio1","precio2","precio3","precio4"];
			var url  = "'.site_url('farmacia/sinv/dataedit/create').'";
			form_virtual(pasar,id,url);
		}

		function pasig(id){
			var pasar=["barras","proveed","descrip"];
			var url  = "'.site_url('farmacia/scst/asignardataedit/create').'";
			form_virtual(pasar,id,url);
		}

		function form_virtual(pasar,id,url){
			var data='.json_encode($detalle->data).';
			var w = window.open("'.site_url('farmacia/scst/dummy').'","asignar","width=800,height=600,scrollbars=Yes,status=Yes,resizable=Yes,screenx="+((screen.availWidth/2)-400)+",screeny="+((screen.availHeight/2)-300)+"");

			var fform  = document.createElement("form");
			fform.setAttribute("target", "asignar");
			fform.setAttribute("action", url );
			fform.setAttribute("method", "post");

			for(i=0;i<pasar.length;i++){
				Val=eval("data[id]."+pasar[i]);
				iinput = document.createElement("input");
				iinput.setAttribute("type", "hidden");
				iinput.setAttribute("name", pasar[i]);
				iinput.setAttribute("value", Val);
				fform.appendChild(iinput);
			}

			var cuerpo = document.getElementsByTagName("body")[0];
			cuerpo.appendChild(fform);
			fform.submit();
			w.focus();
			cuerpo.removeChild(fform);
		}';

		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);
		$accion="javascript:window.location='".site_url('farmacia/scst/cargar'.$edit->pk_URI())."'";
		$pcontrol=$edit->_dataobject->get('pcontrol');
		if(is_null($pcontrol)) $edit->button_status('btn_cargar','Cargar',$accion,'TR','show');
		$edit->buttons('save','undo','back');

		$edit->script($script,'show');
		$edit->build();

		$this->rapyd->jquery[]='$("#dialog").dialog({
			autoOpen: false,
			show: "blind",
			hide: "explode"
		});

		$( "#opener" ).click(function() {
			$( "#dialog" ).dialog( "open" );
			return false;
		});';

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_b2b_compras', $conten,true); 
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = '<h1>Compras Descargadas</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function reasignaprecio(){
		$this->rapyd->set_connection('farmax');
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Cambios de precios','itscst');
		$edit->descrip  = new inputField('Descripci&oacute;n', 'descrip');
		$edit->descrip->mode = 'autohide';

		for($i=1;$i<5;$i++){
			$obj='precio'.$i;
			$edit->$obj = new inputField('Precio '.$i, $obj);
			$edit->$obj->css_class='inputnum';
			$edit->$obj->rule ='numeric';
			$edit->$obj->size = 10;
		}

		$edit->buttons('modify','save');
		$edit->build();
		$this->rapyd->jquery[]='$(window).unload(function() { window.opener.location.reload(); });';
		$data['content'] =$edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='';
		$this->load->view('view_ventanas_sola', $data);
	}


	function asignarfiltro(){
		$this->rapyd->load("datagrid","datafilter");
		$this->rapyd->uri->keep_persistence();

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de asignaci&oacute;n de productos','farmaxasig');

		$filter->proveedor = new inputField("Proveedor", "proveed");
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = "proveed";
		$filter->proveedor->size=20;

		$filter->buttons("reset","search");
		$filter->build();
 
		$grid = new DataGrid();
		$grid->order_by("id","desc");
		$grid->per_page = 15;

		$uri=anchor('farmacia/scst/asignardataedit/show/<#id#>','<#id#>');
		$grid->column_orderby('Id'       ,$uri     ,'id'     );
		$grid->column_orderby('Proveedor','proveed','proveed');
		$grid->column_orderby('Barras'   ,'barras' ,'barras' );
		$grid->column_orderby('Mapeado a','abarras','abarras');

		$grid->add("farmacia/scst/asignardataedit/create");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='<h1>Reasignar C&oacute;digo</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function asignardataedit(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataedit','datagrid');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'barras' =>'C&oacute;digo barras',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo' =>'abarras'),
			//'where'   =>'LENGTH(barras)>0',
			'titulo'  =>'Buscar Art&iacute;culo');
		$boton=$this->datasis->modbus($modbus);

		$js='function pasacod(val) { $("#abarras").val(val) }';
		$edit = new DataEdit('Reasignaciones de c&oacute;digo','farmaxasig');
		$edit->back_url = 'farmacia/scst/asignarfiltro';

		$edit->proveedor = new inputField('Proveedor','proveed');
		$edit->proveedor->rule = 'trim|callback_sprvexits|required';
		$edit->proveedor->mode = 'autohide';
		$edit->proveedor->size = 10;
		$edit->proveedor->maxlength=50;

		$edit->barras = new inputField('Barras en el proveedor','barras');
		$edit->barras->rule = 'required|trim|callback_fueasignado|callback_noexiste';
		$edit->barras->mode = 'autohide';
		$edit->barras->size = 50;
		$edit->barras->maxlength=250;

		$edit->abarras = new inputField('Barras en sistema','abarras');
		$edit->abarras->rule = 'required|trim|callback_siexiste';
		$edit->abarras->size = 50;
		$edit->abarras->maxlength=250;
		$edit->abarras->append($boton);

		$edit->buttons('modify','save','delete','undo','back');

		$describus=$this->input->post('descrip');
		if($describus!==false){
			//print_r($patrones);
			$grid = new DataGrid('Productos similares a <b>'.$describus.'</b>');
			$grid->per_page = 10;
			$grid->db->select(array('codigo','descrip','precio1'));
			$grid->db->from('sinv');
			$grid->paged=false;

			$patrones = preg_split("/[\s,\-]+/", $describus);
			foreach($patrones AS $pat){
				if(strlen($pat)>3){
					$grid->db->like('descrip',$pat);
				}
			}
			$grid->db->limit(10);
			$url='<a onclick=\'pasacod("<#codigo#>")\'  href=\'#\'><#codigo#></a>';

			$grid->column('C&oacute;digo'     ,$url);
			$grid->column('Descripci&oacute;n','descrip');
			$grid->column('Precio 1'          ,'precio1' ,"align='right'");

			$grid->build();
			$tabla=($grid->recordCount>0)? $grid->output : 'No existe descripci&oacute;n semejante a <b>'.$describus.'</b>';

			$edit->script($js,'create');
			$edit->script($js,'modify');
		}else{
			$tabla='';
		}
		$edit->build();

		$this->rapyd->jquery[]='$(window).unload(function() { window.opener.location.reload(); });';
		$data['content'] =$edit->output.$tabla;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='<h1>Reasignar c&oacute;digo</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function sprvexits($proveed){
		$mSQL='SELECT COUNT(*) FROM sprv WHERE proveed='.$this->db->escape($proveed);
		$cana=$this->datasis->dameval($mSQL);
		if($cana==0){
			$error="El proveedor dado no exite";
			$this->validation->set_message('sprvexits',$error);
			return false;
		}
		return true;
	}

	function noexiste($barras){
		$mSQL='SELECT COUNT(*) FROM sinv WHERE codigo='.$this->db->escape($barras);
		$cana=$this->datasis->dameval($mSQL);
		if($cana!=0){
			$error="El c&oacute;digo de barras '$barras' existe en el iventario, la equivalencia se debe aplicar en un producto que no exista";
			$this->validation->set_message('noexiste',$error);
			return false;
		}
		return true;
	}

	function siexiste($barras){
		$mSQL='SELECT COUNT(*) FROM sinv WHERE codigo='.$this->db->escape($barras);
		$cana=$this->datasis->dameval($mSQL);
		if($cana==0){
			$error="El c&oacute;digo de barras '$barras' no existe en el iventario";
			$this->validation->set_message('siexiste',$error);
			return false;
		}
		return true;
	}

	function fueasignado($barras){
		$proveed=$this->db->escape($this->input->post('proveed'));
		$mSQL='SELECT COUNT(*) FROM farmaxasig WHERE barras='.$this->db->escape($barras).' AND proveed='.$proveed;
		$cana=$this->datasis->dameval($mSQL);
		if($cana>0){
			$error="El c&oacute;digo de barras '$barras' ya fue asignado a otro producto";
			$this->validation->set_message('fueasignado',$error);
			return false;
		}
		return true;
	}


	function cargar($control){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataform');

		$form = new DataForm("farmacia/scst/cargar/$control/process");

		$form->nfiscal = new inputField('Control F&iacute;scal', 'nfiscal');
		$form->nfiscal->rule = 'required|strtoupper';
		$form->nfiscal->rows = 10;

		$form->almacen = new  dropdownField ("Almac&eacute;n", "almacen");
		$form->almacen->option('','Seleccionar');
		$form->almacen->options("SELECT ubica,CONCAT_WS('-',ubica,ubides) AS val FROM caub WHERE gasto='N' and invfis='N' ORDER BY ubides");
		$form->almacen->rule = 'required';

		$form->dias = new inputField('D&iacute;as de cr&eacute;dito', 'dias','d/m/Y');
		$form->dias->insertValue = 21;
		$form->dias->rule = 'required|integer';
		$form->dias->size = 5;

		$form->submit('btnsubmit','Guardar');
		$form->build_form();

		if ($form->on_success()){
			$nfiscal= $form->nfiscal->newValue;
			$almacen= $form->almacen->newValue;
			$dias   = $form->dias->newValue;

			$data['content'] = $this->_cargar($control,$nfiscal,$almacen,$dias).br().anchor('farmacia/scst/dataedit/show/'.$control,'Regresar');
		}else{
			$data['content'] = $form->output;
		}

		$data['head']    = $this->rapyd->get_head();
		$data['title']   = '<h1>Cargar compra '.$control.'</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function _cargar($control,$nfiscal,$almacen,$dias){
		$control =$this->db->escape($control);
		$farmaxDB=$this->load->database('farmax',TRUE);
		$farmaxdb=$farmaxDB->database;
		$localdb =$this->db->database;
		$retorna ='';

		$sql ="SELECT COUNT(*) AS cana 
		  FROM ${farmaxdb}.itscst AS a 
		  LEFT JOIN ${localdb}.sinv AS b ON a.codigo=b.codigo 
		  LEFT JOIN ${localdb}.farmaxasig AS c ON a.codigo=c.barras AND c.proveed=a.proveed 
		WHERE a.control=$control AND b.codigo IS NULL AND c.abarras IS NULL";
		$query=$this->db->query($sql);
		if($query->num_rows()>0){
			$row=$query->row_array();
			if($row['cana']==0){
				$query=$farmaxDB->query("SELECT * FROM scst WHERE control=$control AND pcontrol IS NULL");

				if ($query->num_rows()==1){
					$lcontrol=$this->datasis->fprox_numero('nscst');
					$transac =$this->datasis->fprox_numero('ntransac');

					$row=$query->row_array();
					$numero=$row['numero'];
					$row['serie']  =$numero;
					$row['numero'] =substr($numero,-8);
					$row['control']=$lcontrol;
					$row['transac']=$transac;
					$row['nfiscal']=$nfiscal;
					$row['depo']   =$almacen;
					$cd            =strtotime($row['fecha']);
					$row['vence']  =date('Y-m-d', mktime(0,0,0,date('m',$cd),date('d',$cd)+$dias,date('Y',$cd)));
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
				}
			}else{
				$retorna="No se puede pasar porque hay productos que no existen en inventario";
			}
		}else{
			$retorna="Error en la consulta";
		}
		return $retorna;
	}

	function dummy(){
		echo "<p aling='center'>Redirigiendo la p&aacute;gina</p>";
	}

	function _gconsul($mSQL_p,$cod_bar,$busca,$suple=null){
		if(!empty($suple) AND $this->db->table_exists('suple')){
			$mSQL  ="SELECT codigo FROM suple WHERE suplemen='${cod_bar}' LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() != 0){
				$row = $query->row();
				$busca  =array($suple);
				$cod_bar=$row->codigo;
			}
		}

		foreach($busca AS $b){
			$mSQL  =$mSQL_p." WHERE ${b}='${cod_bar}' LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() != 0){
				return $query;
			}
		}

		if ($this->db->table_exists('barraspos')) {
			$mSQL  ="SELECT codigo FROM barraspos WHERE suplemen=".$this->db->escape($cod_bar)." LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() != 0){
				$row = $query->row();
				$cod_bar=$row->codigo;

				$mSQL  =$mSQL_p." WHERE codigo='${cod_bar}' LIMIT 1";
				$query = $this->db->query($mSQL);
				if($query->num_rows() == 0)
					return false;
			}else{
				return false;
			}
		}else{
			return false;
		}
		return $query;
	}
}


