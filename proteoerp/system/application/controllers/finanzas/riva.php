<?php
class riva extends Controller {
	var $titp='';
	var $tits='Retenciones';
	var $url ='finanzas/riva/';

	function riva(){
		parent::Controller();
		$this->load->library('rapyd');
//		$this->datasis->modulo_id('523',1);
	}

	function index(){
		redirect($this->url."filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'riva');

		$filter->nrocomp = new inputField('Comprobante','nrocomp');
		$filter->nrocomp->rule      ='max_length[8]';
		$filter->nrocomp->size      =10;
		$filter->nrocomp->maxlength =8;

		$filter->emision = new dateField('Fecha de Emisi&oacute;n','emision');
		$filter->emision->rule      ='chfecha';
		$filter->emision->size      =10;
		$filter->emision->maxlength =8;

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#nrocomp#></raencode>','<#nrocomp#>');

		$grid = new DataGrid('');
		$grid->order_by('nrocomp','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Comprobante',"$uri",'nrocomp','align="left"');
		$grid->column_orderby('Emisi&oacute;n'    ,"<dbdate_to_human><#emision#></dbdate_to_human>",'emision','align="center"');
		$grid->column_orderby('Tipo Doc.'  ,"tipo_doc",'tipo_doc','align="left"');
		$grid->column_orderby('Fecha'      ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha','align="center"');
		$grid->column_orderby('N&uacute;mero',"numero",'numero','align="left"');
		$grid->column_orderby('Num. f&iacute;scal',"nfiscal",'nfiscal','align="left"');
		$grid->column_orderby('Nombre'     ,"(<#clipro#>) <#nombre#>",'nombre','align="left"');
		$grid->column_orderby('RIF'        ,"rif",'rif','align="left"');
		$grid->column_orderby('Monto Retenido',"<nformat><#reiva#></nformat>",'reiva','align="right"');
		$grid->column_orderby('Transaci&oacute;n',"transac",'transac','align="left"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = $this->titp;
		$this->load->view('view_ventanas', $data);

	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'riva');
		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->pre_process('delete' ,'_pre_delete');

		$edit->nrocomp = new inputField('Nro. Comprobante','nrocomp');
		$edit->nrocomp->rule='max_length[8]';
		$edit->nrocomp->size =10;
		$edit->nrocomp->maxlength =8;

		$edit->emision = new dateField('Emisi&oacute;n','emision');
		$edit->emision->rule='chfecha';
		$edit->emision->size =10;
		$edit->emision->maxlength =8;

		$edit->periodo = new inputField('Per&iacute;odo','periodo');
		$edit->periodo->rule='max_length[8]';
		$edit->periodo->size =10;
		$edit->periodo->maxlength =8;

		$edit->tipo_doc = new inputField('Tipo Doc.','tipo_doc');
		$edit->tipo_doc->rule='max_length[2]';
		$edit->tipo_doc->size =4;
		$edit->tipo_doc->maxlength =2;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->rule='max_length[12]';
		$edit->numero->size =14;
		$edit->numero->maxlength =12;

		$edit->nfiscal = new inputField('N&uacute;mero F&iacute;scal','nfiscal');
		$edit->nfiscal->rule='max_length[12]';
		$edit->nfiscal->size =14;
		$edit->nfiscal->maxlength =12;

		$edit->afecta = new inputField('Doc. Afectado','afecta');
		$edit->afecta->rule='max_length[8]';
		$edit->afecta->size =10;
		$edit->afecta->maxlength =8;

		$edit->clipro = new inputField('Cliente/Proveedor','clipro');
		$edit->clipro->rule='max_length[5]';
		$edit->clipro->size =7;
		$edit->clipro->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[40]';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;
		$edit->nombre->in='clipro';

		$edit->rif = new inputField('RIF','rif');
		$edit->rif->rule='max_length[14]';
		$edit->rif->size =16;
		$edit->rif->maxlength =14;

		$edit->exento = new inputField('Monto exento','exento');
		$edit->exento->rule='max_length[15]|numeric';
		$edit->exento->css_class='inputnum';
		$edit->exento->size =17;
		$edit->exento->maxlength =15;

		$edit->tasa = new inputField('Tasa General','tasa');
		$edit->tasa->rule='max_length[5]|numeric';
		$edit->tasa->css_class='inputnum';
		$edit->tasa->size =7;
		$edit->tasa->maxlength =5;
		$edit->tasa->group='Alicuota General';

		$edit->general = new inputField('Base general','general');
		$edit->general->rule='max_length[15]|numeric';
		$edit->general->css_class='inputnum';
		$edit->general->size =17;
		$edit->general->maxlength =15;
		$edit->general->group='Alicuota General';

		$edit->geneimpu = new inputField('Impuesto general','geneimpu');
		$edit->geneimpu->rule='max_length[15]|numeric';
		$edit->geneimpu->css_class='inputnum';
		$edit->geneimpu->size =17;
		$edit->geneimpu->maxlength =15;
		$edit->geneimpu->group='Alicuota General';

		$edit->tasaadic = new inputField('Tasa adicional','tasaadic');
		$edit->tasaadic->rule='max_length[5]|numeric';
		$edit->tasaadic->css_class='inputnum';
		$edit->tasaadic->size =7;
		$edit->tasaadic->maxlength =5;
		$edit->tasaadic->group='Alicuota Adicional';

		$edit->adicional = new inputField('Base adicional','adicional');
		$edit->adicional->rule='max_length[15]|numeric';
		$edit->adicional->css_class='inputnum';
		$edit->adicional->size =17;
		$edit->adicional->maxlength =15;
		$edit->adicional->group='Alicuota Adicional';

		$edit->adicimpu = new inputField('Impuesto adicional','adicimpu');
		$edit->adicimpu->rule='max_length[15]|numeric';
		$edit->adicimpu->css_class='inputnum';
		$edit->adicimpu->size =17;
		$edit->adicimpu->maxlength =15;
		$edit->adicimpu->group='Alicuota Adicional';

		$edit->tasaredu = new inputField('Tasa reducida','tasaredu');
		$edit->tasaredu->rule='max_length[5]|numeric';
		$edit->tasaredu->css_class='inputnum';
		$edit->tasaredu->size =7;
		$edit->tasaredu->maxlength =5;
		$edit->tasaredu->group='Alicuota Reducida';

		$edit->reducida = new inputField('Base reducida','reducida');
		$edit->reducida->rule='max_length[15]|numeric';
		$edit->reducida->css_class='inputnum';
		$edit->reducida->size =17;
		$edit->reducida->maxlength =15;
		$edit->reducida->group='Alicuota Reducida';

		$edit->reduimpu = new inputField('Impuesto reducido','reduimpu');
		$edit->reduimpu->rule='max_length[15]|numeric';
		$edit->reduimpu->css_class='inputnum';
		$edit->reduimpu->size =17;
		$edit->reduimpu->maxlength =15;
		$edit->reduimpu->group='Alicuota Reducida';

		$edit->stotal = new inputField('Sub-total','stotal');
		$edit->stotal->rule='max_length[15]|numeric';
		$edit->stotal->css_class='inputnum';
		$edit->stotal->size =17;
		$edit->stotal->maxlength =15;
		$edit->stotal->group='Res&uacute;men';

		$edit->impuesto = new inputField('Impuesto total','impuesto');
		$edit->impuesto->rule='max_length[15]|numeric';
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->size =17;
		$edit->impuesto->maxlength =15;
		$edit->impuesto->group='Res&uacute;men';

		$edit->gtotal = new inputField('Total','gtotal');
		$edit->gtotal->rule='max_length[15]|numeric';
		$edit->gtotal->css_class='inputnum';
		$edit->gtotal->size =17;
		$edit->gtotal->maxlength =15;
		$edit->gtotal->group='Res&uacute;men';

		$edit->reiva = new inputField('Monto retenido','reiva');
		$edit->reiva->rule='max_length[15]|numeric';
		$edit->reiva->css_class='inputnum';
		$edit->reiva->size =17;
		$edit->reiva->maxlength =15;
		$edit->reiva->group='Res&uacute;men';

		$edit->transac = new inputField('Transaci&oacute;n','transac');
		$edit->transac->rule='max_length[8]';
		$edit->transac->size =10;
		$edit->transac->maxlength =8;

		//$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		//$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));
		//$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->ffactura = new dateField('Fecha de factura','ffactura');
		$edit->ffactura->rule='chfecha';
		$edit->ffactura->size =10;
		$edit->ffactura->maxlength =8;

		$edit->buttons('back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = $this->tits;
		$this->load->view('view_ventanas', $data);

	}

	function _pre_insert($do){
		return false;
	}

	function _pre_delete($do){
		return false;
	}

	function _pre_update($do){
		return false;
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
		$mSQL="CREATE TABLE `riva` (
		  `nrocomp` char(8) NOT NULL DEFAULT '',
		  `emision` date DEFAULT NULL,
		  `periodo` char(8) DEFAULT NULL,
		  `tipo_doc` char(2) DEFAULT NULL,
		  `fecha` date DEFAULT NULL,
		  `numero` char(12) DEFAULT NULL,
		  `nfiscal` char(12) DEFAULT NULL,
		  `afecta` char(8) DEFAULT NULL,
		  `clipro` char(5) DEFAULT NULL,
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
		  `transac` char(8) DEFAULT NULL,
		  `estampa` date DEFAULT NULL,
		  `hora` char(8) DEFAULT NULL,
		  `usuario` char(12) DEFAULT NULL,
		  `ffactura` date DEFAULT '0000-00-00',
		  PRIMARY KEY (`nrocomp`),
		  UNIQUE KEY `rivatra` (`transac`),
		  KEY `Numero` (`numero`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}
}
?>