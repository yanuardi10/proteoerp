<?php
class rivc extends Controller {
	var $titp='Retenciones de Clientes';
	var $tits='Retenciones de Clientes';
	var $url ='finanzas/rivc/';

	function rivc(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('511',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'rivc');

		$filter->nrocomp = new inputField('Comprobante','nrocomp');
		$filter->nrocomp->rule      ='max_length[8]';
		$filter->nrocomp->size      =10;
		$filter->nrocomp->maxlength =8;

		$filter->emision = new dateField('Emisi&oacute;n','emision');
		$filter->emision->rule      ='chfecha';
		$filter->emision->size      =10;
		$filter->emision->maxlength =8;

		$filter->periodo = new inputField('Per&iacute;odo','periodo');
		$filter->periodo->rule      ='max_length[8]';
		$filter->periodo->size      =10;
		$filter->periodo->maxlength =8;

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->rif = new inputField('RIF','rif');
		$filter->rif->rule      ='max_length[14]';
		$filter->rif->size      =16;
		$filter->rif->maxlength =14;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#nrocomp#></raencode>','<#nrocomp#>');

		$grid = new DataGrid('');
		$grid->order_by('nrocomp','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Comprobante'   ,$uri,'nrocomp','align="left"');
		$grid->column_orderby('Emisi&oacute;n','<dbdate_to_human><#emision#></dbdate_to_human>','emision','align="center"');
		$grid->column_orderby('fecha'         ,'<dbdate_to_human><#fecha#></dbdate_to_human>'  ,'fecha','align="center"');
		$grid->column_orderby('Cliente'       ,'clipro','clipro','align="left"');
		$grid->column_orderby('Nombre'        ,'nombre','nombre','align="left"');
		$grid->column_orderby('RIF'           ,'rif'   ,'rif'   ,'align="left"');
		$grid->column_orderby('Impuesto'      ,'<nformat><#impuesto#></nformat>','impuesto','align="right"');
		$grid->column_orderby('Total'         ,'<nformat><#gtotal#></nformat>'  ,'gtotal','align="right"');
		$grid->column_orderby('Monto Ret.'    ,'<nformat><#reiva#></nformat>'   ,'reiva','align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = $this->titp;
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('datadetails','dataobject');

		$do = new DataObject('rivc');
		//$do->pointer('scli' ,'scli.cliente=rivc.cod_cli','sprv.tipo AS sprvtipo, sprv.reteiva AS sprvreteiva','left');
		$do->rel_one_to_many('itrivc' ,'itrivc' ,array('id'=>'idrivc'));

		$edit = new DataDetails($this->tits, $do);

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->nrocomp = new inputField('Comprobante','nrocomp');
		$edit->nrocomp->rule='max_length[8]|required';
		$edit->nrocomp->size =10;
		$edit->nrocomp->maxlength =8;
		$edit->nrocomp->autocomplete = false;

		$edit->emision = new dateField('Fecha de Emisi&oacute;n','emision');
		$edit->emision->rule='chfecha|required';
		$edit->emision->size =10;
		$edit->emision->maxlength =8;

		$edit->periodo = new inputField('Per&iacute;odo','periodo');
		$edit->periodo->rule='max_length[8]';
		$edit->periodo->size =10;
		$edit->periodo->maxlength =8;

		$edit->fecha = new dateField('Fecha de Recepci&oacute;n','fecha');
		$edit->fecha->rule='chfecha|required';
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->cod_cli = new hiddenField('Cliente','cod_cli');
		$edit->cod_cli->rule='max_length[5]|required';
		$edit->cod_cli->size =7;
		$edit->cod_cli->maxlength =5;
		$edit->cod_cli->readonly=true;

		$edit->nombre = new hiddenField('Nombre','nombre');
		$edit->nombre->rule='max_length[40]';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;

		$edit->rif = new inputField('RIF','rif');
		$edit->rif->rule='max_length[14]';
		$edit->rif->size =16;
		$edit->rif->maxlength =14;
		$edit->rif->autocomplete = false;

		$edit->exento = new inputField('Monto Exento','exento');
		$edit->exento->rule='max_length[15]|numeric';
		$edit->exento->css_class='inputnum';
		$edit->exento->size =17;
		$edit->exento->maxlength =15;

		$edit->tasa = new inputField('tasa','tasa');
		$edit->tasa->rule='max_length[5]|numeric';
		$edit->tasa->css_class='inputnum';
		$edit->tasa->size =7;
		$edit->tasa->maxlength =5;

		$edit->general = new inputField('general','general');
		$edit->general->rule='max_length[15]|numeric';
		$edit->general->css_class='inputnum';
		$edit->general->size =17;
		$edit->general->maxlength =15;

		$edit->geneimpu = new inputField('geneimpu','geneimpu');
		$edit->geneimpu->rule='max_length[15]|numeric';
		$edit->geneimpu->css_class='inputnum';
		$edit->geneimpu->size =17;
		$edit->geneimpu->maxlength =15;

		$edit->tasaadic = new inputField('tasaadic','tasaadic');
		$edit->tasaadic->rule='max_length[5]|numeric';
		$edit->tasaadic->css_class='inputnum';
		$edit->tasaadic->size =7;
		$edit->tasaadic->maxlength =5;

		$edit->adicional = new inputField('adicional','adicional');
		$edit->adicional->rule='max_length[15]|numeric';
		$edit->adicional->css_class='inputnum';
		$edit->adicional->size =17;
		$edit->adicional->maxlength =15;

		$edit->adicimpu = new inputField('adicimpu','adicimpu');
		$edit->adicimpu->rule='max_length[15]|numeric';
		$edit->adicimpu->css_class='inputnum';
		$edit->adicimpu->size =17;
		$edit->adicimpu->maxlength =15;

		$edit->tasaredu = new inputField('tasaredu','tasaredu');
		$edit->tasaredu->rule='max_length[5]|numeric';
		$edit->tasaredu->css_class='inputnum';
		$edit->tasaredu->size =7;
		$edit->tasaredu->maxlength =5;

		$edit->reducida = new inputField('reducida','reducida');
		$edit->reducida->rule='max_length[15]|numeric';
		$edit->reducida->css_class='inputnum';
		$edit->reducida->size =17;
		$edit->reducida->maxlength =15;

		$edit->reduimpu = new inputField('reduimpu','reduimpu');
		$edit->reduimpu->rule='max_length[15]|numeric';
		$edit->reduimpu->css_class='inputnum';
		$edit->reduimpu->size =17;
		$edit->reduimpu->maxlength =15;

		$edit->stotal = new hiddenField('Sub-total','stotal');
		$edit->stotal->rule='max_length[15]|numeric';
		$edit->stotal->css_class='inputnum';
		$edit->stotal->size =17;
		$edit->stotal->maxlength =15;

		$edit->impuesto = new hiddenField('Impuesto','impuesto');
		$edit->impuesto->rule='max_length[15]|numeric';
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->size =17;
		$edit->impuesto->maxlength =15;

		$edit->gtotal = new hiddenField('Total','gtotal');
		$edit->gtotal->rule='max_length[15]|numeric';
		$edit->gtotal->css_class='inputnum';
		$edit->gtotal->size =17;
		$edit->gtotal->maxlength =15;

		$edit->reiva = new hiddenField('Monto Retenido','reiva');
		$edit->reiva->rule='max_length[15]|numeric';
		$edit->reiva->css_class='inputnum';
		$edit->reiva->size =17;
		$edit->reiva->maxlength =15;

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->modificado = new inputField('modificado','modificado');
		$edit->modificado->rule='max_length[8]';
		$edit->modificado->size =10;
		$edit->modificado->maxlength =8;

		//****************************
		//Inicio del Detalle
		//****************************
		$edit->it_tipo_doc = new hiddenField('tipo_doc','tipo_doc_<#i#>');
		$edit->it_tipo_doc->db_name='tipo_doc';
		$edit->it_tipo_doc->rule='max_length[2]|required';
		$edit->it_tipo_doc->size =4;
		$edit->it_tipo_doc->maxlength =1;
		$edit->it_tipo_doc->rel_id ='itrivc';

		$edit->it_numero = new inputField('numero','numero_<#i#>');
		$edit->it_numero->db_name='numero';
		$edit->it_numero->rule='max_length[12]|required';
		$edit->it_numero->size =14;
		$edit->it_numero->maxlength =12;
		$edit->it_numero->rel_id ='itrivc';
		$edit->it_numero->autocomplete = false;

		$edit->it_exento = new inputField('exento','exento_<#i#>');
		$edit->it_exento->db_name='exento';
		$edit->it_exento->rule='max_length[15]|numeric';
		$edit->it_exento->css_class='inputnum';
		$edit->it_exento->size =17;
		$edit->it_exento->maxlength =15;
		$edit->it_excento->rel_id ='itrivc';

		$edit->it_tasa = new inputField('tasa','tasa_<#i#>');
		$edit->it_tasa->db_name='tasa';
		$edit->it_tasa->rule='max_length[5]|numeric';
		$edit->it_tasa->css_class='inputnum';
		$edit->it_tasa->size =7;
		$edit->it_tasa->maxlength =5;
		$edit->it_tasa->rel_id ='itrivc';

		$edit->it_general = new inputField('general','general_<#i#>');
		$edit->it_general->db_name='general';
		$edit->it_general->rule='max_length[15]|numeric';
		$edit->it_general->css_class='inputnum';
		$edit->it_general->size =17;
		$edit->it_general->maxlength =15;
		$edit->it_general->rel_id ='itrivc';

		$edit->it_geneimpu = new inputField('geneimpu','geneimpu_<#i#>');
		$edit->it_geneimpu->db_name='geneimpu';
		$edit->it_geneimpu->rule='max_length[15]|numeric';
		$edit->it_geneimpu->css_class='inputnum';
		$edit->it_geneimpu->size =17;
		$edit->it_geneimpu->maxlength =15;
		$edit->it_geneimpu->rel_id ='itrivc';

		$edit->it_tasaadic = new inputField('tasaadic','tasaadic_<#i#>');
		$edit->it_tasaadic->db_name='tasaadic';
		$edit->it_tasaadic->rule='max_length[5]|numeric';
		$edit->it_tasaadic->css_class='inputnum';
		$edit->it_tasaadic->size =7;
		$edit->it_tasaadic->maxlength =5;
		$edit->it_tasaasic->rel_id ='itrivc';

		$edit->it_adicional = new inputField('adicional','adicional_<#i#>');
		$edit->it_adicional->db_name='adicional';
		$edit->it_adicional->rule='max_length[15]|numeric';
		$edit->it_adicional->css_class='inputnum';
		$edit->it_adicional->size =17;
		$edit->it_adicional->maxlength =15;
		$edit->it_adicional->rel_id ='itrivc';

		$edit->it_adicimpu = new inputField('adicimpu','adicimpu_<#i#>');
		$edit->it_adicimpu->db_name='adicimpu';
		$edit->it_adicimpu->rule='max_length[15]|numeric';
		$edit->it_adicimpu->css_class='inputnum';
		$edit->it_adicimpu->size =17;
		$edit->it_adicimpu->maxlength =15;
		$edit->it_adicimpu->rel_id ='itrivc';

		$edit->it_tasaredu = new inputField('tasaredu','tasaredu_<#i#>');
		$edit->it_tasaredu->db_name='tasaredu';
		$edit->it_tasaredu->rule='max_length[5]|numeric';
		$edit->it_tasaredu->css_class='inputnum';
		$edit->it_tasaredu->size =7;
		$edit->it_tasaredu->maxlength =5;
		$edit->it_tasaredu->rel_id ='itrivc';

		$edit->it_reducida = new inputField('reducida','reducida_<#i#>');
		$edit->it_reducida->db_name='reducida';
		$edit->it_reducida->rule='max_length[15]|numeric';
		$edit->it_reducida->css_class='inputnum';
		$edit->it_reducida->size =17;
		$edit->it_reducida->maxlength =15;
		$edit->it_reducida->rel_id ='itrivc';

		$edit->it_reduimpu = new inputField('reduimpu','reduimpu_<#i#>');
		$edit->it_reduimpu->db_name='reduimpu';
		$edit->it_reduimpu->rule='max_length[15]|numeric';
		$edit->it_reduimpu->css_class='inputnum';
		$edit->it_reduimpu->size =17;
		$edit->it_reduimpu->maxlength =15;
		$edit->it_reduimpu->rel_id ='itrivc';

		$edit->it_stotal = new inputField('stotal','stotal_<#i#>');
		$edit->it_stotal->db_name='stotal';
		$edit->it_stotal->rule='max_length[15]|numeric';
		$edit->it_stotal->css_class='inputnum';
		$edit->it_stotal->size =17;
		$edit->it_stotal->maxlength =15;
		$edit->it_stotal->rel_id ='itrivc';

		$edit->it_impuesto = new hiddenField('impuesto','impuesto_<#i#>');
		$edit->it_impuesto->db_name='impuesto';
		$edit->it_impuesto->rule='max_length[15]|numeric';
		$edit->it_impuesto->css_class='inputnum';
		$edit->it_impuesto->size =17;
		$edit->it_impuesto->maxlength =15;
		$edit->it_impuesto->rel_id ='itrivc';

		$edit->it_gtotal = new hiddenField('gtotal','gtotal_<#i#>');
		$edit->it_gtotal->db_name='gtotal';
		$edit->it_gtotal->rule='max_length[15]|numeric';
		$edit->it_gtotal->css_class='inputnum';
		$edit->it_gtotal->size =17;
		$edit->it_gtotal->maxlength =15;
		$edit->it_gtotal->rel_id ='itrivc';
		$edit->it_gtotal->autocomplete = false;

		$edit->it_reiva = new inputField('reiva','reiva_<#i#>');
		$edit->it_reiva->db_name='reiva';
		$edit->it_reiva->rule='max_length[15]|numeric';
		$edit->it_reiva->css_class='inputnum';
		$edit->it_reiva->size =17;
		$edit->it_reiva->maxlength =15;
		$edit->it_reiva->rel_id ='itrivc';
		$edit->it_reiva->onkeyup ='totalizar()';
		$edit->it_reiva->autocomplete = false;
		//****************************
		//Fin del Detalle
		//****************************

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		//$data['content'] = $edit->output;
		$conten['form'] =& $edit;
		$data['content'] = $this->load->view('view_rivc', $conten,true);
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');
		$data['head']   .= phpscript('nformat.js');
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
		$data['title']   = $this->tits;
		$this->load->view('view_ventanas', $data);
	}

	function buscasfac(){
		$mid   = $this->input->post('q');
		$scli  = $this->input->post('scli');
		$qdb   = $this->db->escape('%'.$mid.'%');
		$sclidb= $this->db->escape($scli);
		
		$rete=0.75;
		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT a.tipo_doc, a.numero, a.totalg, a.iva, a.iva*$rete AS reiva
				FROM sfac AS a
				WHERE a.cod_cli=$sclidb AND a.numero LIKE $qdb
				ORDER BY numero LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = $row['tipo_doc'].'-'.$row['numero'].' '.$row['totalg'].' Bs.';
					$retArray['value']   = $row['numero'];
					$retArray['gtotal']  = $row['totalg'];
					$retArray['reiva']   = round($row['reiva'],2);
					$retArray['impuesto']= $row['iva'];
					$retArray['tipo_doc']= $row['tipo_doc'];
					
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	function buscascli(){
		$mid  = $this->input->post('q');
		$qdb  = $this->db->escape('%'.$mid.'%');

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo
				FROM scli WHERE cliente LIKE ${qdb} OR rifci LIKE ${qdb}
				ORDER BY rifci LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['rifci'];
					$retArray['label']   = '('.$row['rifci'].') '.$row['nombre'];
					$retArray['nombre']  = $row['nombre'];
					$retArray['cod_cli'] = $row['cliente'];
					$retArray['tipo']    = $row['tipo'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}

	function instalar(){
		if (!$this->db->table_exists('rivc')) {
			$mSQL="CREATE TABLE `rivc` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`nrocomp` char(8) NOT NULL DEFAULT '',
			`emision` date DEFAULT NULL,
			`periodo` char(8) DEFAULT NULL,
			`fecha` date DEFAULT NULL,
			`cod_cli` char(5) DEFAULT NULL,
			`nombre` char(40) DEFAULT NULL,
			`rif` char(14) DEFAULT NULL,
			`exento` decimal(15,2) DEFAULT NULL,
			`tasa` decimal(5,2) DEFAULT NULL,
			`general` decimal(15,2) DEFAULT NULL,
			`geneimpu` decimal(15,2) DEFAULT NULL,
			`tasaadic` decimal(5,2) DEFAULT NULL,
			`adicional` decimal(15,2) DEFAULT NULL,
			`adicimpu` decimal(15,2) DEFAULT NULL,
			`tasaredu` decimal(5,2) DEFAULT NULL,
			`reducida` decimal(15,2) DEFAULT NULL,
			`reduimpu` decimal(15,2) DEFAULT NULL,
			`stotal` decimal(15,2) DEFAULT NULL,
			`impuesto` decimal(15,2) DEFAULT NULL,
			`gtotal` decimal(15,2) DEFAULT NULL,
			`reiva` decimal(15,2) DEFAULT NULL,
			`estampa` date DEFAULT NULL,
			`hora` char(8) DEFAULT NULL,
			`usuario` char(12) DEFAULT NULL,
			`modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `nrocomp_clipro` (`nrocomp`,`cod_cli`),
			KEY `modificado` (`modificado`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('itrivc')) {
			$mSQL="CREATE TABLE `itrivc` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`idrivc` int(6) DEFAULT NULL,
			`tipo_doc` char(2) DEFAULT NULL,
			`fecha` date DEFAULT NULL,
			`numero` varchar(8) DEFAULT NULL,
			`nfiscal` char(12) DEFAULT NULL,
			`exento` decimal(15,2) DEFAULT NULL,
			`tasa` decimal(5,2) DEFAULT NULL,
			`general` decimal(15,2) DEFAULT NULL,
			`geneimpu` decimal(15,2) DEFAULT NULL,
			`tasaadic` decimal(5,2) DEFAULT NULL,
			`adicional` decimal(15,2) DEFAULT NULL,
			`adicimpu` decimal(15,2) DEFAULT NULL,
			`tasaredu` decimal(5,2) DEFAULT NULL,
			`reducida` decimal(15,2) DEFAULT NULL,
			`reduimpu` decimal(15,2) DEFAULT NULL,
			`stotal` decimal(15,2) DEFAULT NULL,
			`impuesto` decimal(15,2) DEFAULT NULL,
			`gtotal` decimal(15,2) DEFAULT NULL,
			`reiva` decimal(15,2) DEFAULT NULL,
			`transac` char(8) DEFAULT NULL,
			`estampa` date DEFAULT NULL,
			`hora` char(8) DEFAULT NULL,
			`usuario` char(12) DEFAULT NULL,
			`ffactura` date DEFAULT '0000-00-00',
			`modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `rivatra` (`transac`),
			UNIQUE KEY `tipo_doc_numero` (`tipo_doc`,`numero`),
			KEY `Numero` (`numero`),
			KEY `modificado` (`modificado`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}
	}
}
