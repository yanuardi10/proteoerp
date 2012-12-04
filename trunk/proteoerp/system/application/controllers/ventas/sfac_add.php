<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
//require_once(BASEPATH.'application/controllers/ventas/sfac');
class sfac_add extends validaciones {

	var $titp='Facturaci&oacute;n';
	var $tits='Facturaci&oacute;n';
	var $url ='ventas/sfac_add/';
	var $_url=null;

	function sfac_add(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('103',1);
		$this->instalar();
		$this->genesal=true;
		$this->back_url='';
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$atts = array(
			'width'      => '800',
			'height'     => '600',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '0',
			'screeny'    => '0'
		);

		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'  =>'Nombre',
			'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');
		$boton=$this->datasis->modbus($scli);

		$filter = new DataFilter('Filtro de Facturas');
		$filter->db->select(array('fecha','numero','cod_cli','nombre','totals','totalg','iva','tipo_doc','exento', 'IF(referen="C","Credito",IF(referen="E","Contado","Pendiente")) referen','IF(tipo_doc="X","N","S") nulo','almacen','vd','usuario', 'hora', 'estampa','nfiscal','cajero', 'transac','maqfiscal', 'factura' ,'id'));
		$filter->db->from('sfac');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechad->clause  = 'where';
		$filter->fechad->db_name = 'fecha';
		//$filter->fechad->insertValue = date('Y-m-d');
		$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechad->group = '1';

		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechah->clause = 'where';
		$filter->fechah->db_name='fecha';
		//$filter->fechah->insertValue = date('Y-m-d');
		$filter->fechah->size=10;
		$filter->fechah->operator='<=';
		$filter->fechah->group = '1';

		$filter->referen = new  dropdownField ('Condici&oacute;n', 'referen');
		$filter->referen->option('' ,'Todos');
		$filter->referen->option('E','Contado');
		$filter->referen->option('C','Cr&eacute;dito');
		$filter->referen->style='width:150px;';
		$filter->referen->operator='=';
		$filter->referen->clause  = 'where';
		$filter->referen->group = '1';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 20;
		$filter->numero->group = '2';

		$filter->cliente = new inputField('Cliente', 'cod_cli');
		$filter->cliente->size = 20;
		$filter->cliente->append($boton);
		$filter->cliente->group = '2';

		$filter->buttons('reset','search');
		$filter->build("dataformfiltro");

		$uri = anchor($this->url.'dataedit/show/<#id#>','<#tipo_doc#><#numero#>');
		$uri2  = anchor($this->url.'dataedit/show/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar')));
		$uri2 .= "&nbsp;";
		$uri2 .= anchor('formatos/ver2/FACTURA/<#tipo_doc#>/<#numero#>',img(array('src'=>'images/pdf_logo.gif','border'=>'0','alt'=>'PDF')));
		$uri2 .= "&nbsp;";
		$uri2 .= anchor('formatos/verhtml/FACTURA/<#tipo_doc#>/<#numero#>',img(array('src'=>'images/html_icon.gif','border'=>'0','alt'=>'HTML')));
		$uri2 .= "&nbsp;";
		$uri2 .= img(array('src'=>'images/<#nulo#>.gif','border'=>'0','alt'=>'Estado','title'=>'Estado'));

		$uri_3  = "<a href='javascript:void(0);' onclick='javascript:nfiscal(\"<#id#>\")'>";
		$propiedad = array('src' => 'images/engrana.png', 'alt' => 'Modifica Nro de Control', 'title' => 'Modifica Nro. de Control','border'=>'0','height'=>'12');
		$uri_3 .= img($propiedad);
		$uri_3 .= "</a>";

		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url().$this->url."dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."reportes/index/sfac', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600" width="900" '.'>';
		$mtool .= img(array('src' => 'images/reportes.gif', 'alt' => 'Reportes', 'title' => 'Reportes','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";

		$grid = new DataGrid($mtool);
		$grid->order_by('fecha','desc');
		$grid->per_page = 50;

		$grid->column('Acciones',$uri2);
		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha',    '<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Cliente',  'cod_cli',  'cod_cli');
		$grid->column_orderby('Nombre',   'nombre',   'nombre');
		$grid->column_orderby('Almacen',  'almacen',  'almacen');
		$grid->column_orderby('Sub.Total','<nformat><#totals#></nformat>','totals','align=\'right\'');
		$grid->column_orderby('IVA',      '<nformat><#iva#></nformat>'   ,'iva',   'align=\'right\'');
		$grid->column_orderby('Total',    '<nformat><#totalg#></nformat>','totalg','align=\'right\'');
		$grid->column_orderby('Exento',   '<nformat><#exento#></nformat>','totalg','align=\'right\'');
		$grid->column_orderby('Tipo',     'referen',  'referen','align=\'left\'');
		$grid->column_orderby('N.Fiscal',  $uri_3.'<#nfiscal#>', 'nfiscal' );
		$grid->column_orderby('M.Fiscal', 'maqfiscal','maqfiscal','align=\'left\'');
		$grid->column_orderby('Vende',    'vd',       'vd');
		$grid->column_orderby('Cajero',   'cajero',   'cajero');
		$grid->column_orderby('Usuario',  'usuario',  'nfiscal','align=\'left\'');
		$grid->column_orderby('Hora',     'hora',     'hora',   'align=\'center\'');
		$grid->column_orderby('Transac',  'transac',  'transac','align=\'left\'');
		$grid->column_orderby('Afecta',   'factura',  'factura','align=\'left\'');
		$grid->column_orderby('I.D.',     'id',       'id',     'align=\'right\'');

		$grid->build('datagridST');
		//echo $grid->db->last_query();

		// Para usar SuperTable
		$extras = '
		<script type="text/javascript">
		//<![CDATA[
		(function() {
			var mySt = new superTable("demoTable", {
			cssSkin : "sSky",
			fixedCols : 1,
				headerRows : 1,
				onStart : function () {
				this.start = new Date();
				},
				onFinish : function () {
				document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";
				}
			});
		})();
		//]]>
		</script>';

		$style ='<style type="text/css">
		.fakeContainer { /* The parent container */
			margin: 5px;
			padding: 0px;
			border: none;
			width: 640px; /* Required to set */
			height: 320px; /* Required to set */
			overflow: hidden; /* Required to set */
		}
		</style>';

		$script ='
		<script type="text/javascript">
		function nfiscal(mid){
			jPrompt("Numero de Serie","" ,"Cambio de Nro.Fiscal", function(mserie){
				if( mserie==null){
					jAlert("Cancelado","Informacion");
				} else {
					$.ajax({ url: "'.site_url().'ventas/sfac/nfiscal/"+mid+"/"+mserie,
						success: function(msg){
							jAlert("Cambio Finalizado "+msg,"Informacion");
							location.reload();
							}
					});
				}
			})
		}
		</script>';

		$sigma = "";

		//$data['content']  = $mtool;
		$data['content'] = $grid->output;

		$data['filtro']  = $filter->output;

		$data['script']  = script('jquery.js');
		$data['script'] .= script("jquery.alerts.js");
		$data['script'] .= script('superTables.js');
		$data['script'] .= $script;

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['style']	.= style("jquery.alerts.css");

		$data['extras']  = $extras;

		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);

	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigoa_<#i#>',
				'descrip'=>'desca_<#i#>',
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n',
			'tipo'=>'Tipo'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','rifci'=>'rifci',
						  'dire11'=>'direc','tipo'=>'sclitipo'),
		'titulo'  =>'Buscar Cliente',
		);
		$boton =$this->datasis->modbus($mSCLId);

		$do = new DataObject('sfac');
		$do->rel_one_to_many('sitems', 'sitems', array('id'=>'id_sfac'));
		$do->rel_one_to_many('sfpa'  , 'sfpa'  , array('numero','transac'));
		$do->pointer('scli' ,'scli.cliente=sfac.cod_cli','scli.tipo AS sclitipo','left');
		$do->rel_pointer('sitems','sinv','sitems.codigoa=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Facturas', $do);

		$edit->back_url = (!empty($this->back_url))? $this->back_url : site_url('ventas/sfac/index');
		$edit->set_rel_title('sitems','Producto <#o#>');
		$edit->set_rel_title('sfpa','Forma de pago <#o#>');

		$edit->pre_process( 'insert','_pre_insert' );
		$edit->pre_process( 'update','_pre_update' );
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->sclitipo = new hiddenField('', 'sclitipo');
		$edit->sclitipo->db_name     = 'sclitipo';
		$edit->sclitipo->pointer     = true;
		$edit->sclitipo->insertValue = 1;

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->tipo_doc = new  dropdownField('Documento', 'tipo_doc');
		$edit->tipo_doc->option('F','Factura');
		$edit->tipo_doc->option('D','Devoluci&oacute;n');
		$edit->tipo_doc->style='width:200px;';
		$edit->tipo_doc->size = 5;
		$edit->tipo_doc->rule='required';

		$edit->vd = new  dropdownField ('Vendedor', 'vd');
		$edit->vd->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vd->style='width:200px;';
		$edit->vd->insertValue=$this->secu->getvendedor();

		$edit->almacen= new dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->options('SELECT ubica,ubides FROM caub WHERE gasto="N" ORDER BY ubides');
		$edit->almacen->rule='required';
		$alma = $this->secu->getalmacen();
		if(strlen($alma)<=0){
			$alma = $this->datasis->traevalor('ALMACEN');
		}
		$edit->almacen->insertValue=$alma;
		$edit->almacen->style='width:200px;';

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->factura = new inputField('Factura', 'factura');
		$edit->factura->size = 10;
		$edit->factura->mode='autohide';
		$edit->factura->maxlength=8;
		$edit->factura->rule='condi_required|callback_chfactura';

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->autocomplete=false;
		$edit->cliente->rule='required|existescli';
		//$edit->cliente->append($boton);

		$edit->nombre = new hiddenField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->readonly =true;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->rifci   = new hiddenField('RIF/CI','rifci');
		$edit->rifci->autocomplete=false;
		$edit->rifci->readonly =true;
		$edit->rifci->size = 15;

		$edit->direc = new hiddenField('Direcci&oacute;n','direc');
		$edit->direc->readonly =true;
		$edit->direc->size = 40;

		$edit->cajero= new dropdownField('Cajero', 'cajero');
		$edit->cajero->options('SELECT cajero,nombre FROM scaj ORDER BY nombre');
		$edit->cajero->rule ='required|cajerostatus';
		$edit->cajero->style='width:200px;';
		$edit->cajero->insertValue=$this->secu->getcajero();

		//***********************************
		//  Campos para el detalle 1 sitems
		//***********************************
		$edit->codigoa = new inputField('C&oacute;digo <#o#>', 'codigoa_<#i#>');
		$edit->codigoa->size     = 12;
		$edit->codigoa->db_name  = 'codigoa';
		$edit->codigoa->rel_id   = 'sitems';
		$edit->codigoa->rule     = 'required';

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=36;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=50;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='sitems';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'sitems';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive|callback_chcanadev[<#i#>]';
		$edit->cana->autocomplete=false;
		$edit->cana->onkeyup  ='importe(<#i#>)';
		$edit->cana->showformat ='decimal';
		$edit->cana->disable_paste=true;

		$edit->preca = new inputField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'sitems';
		$edit->preca->size      = 10;
		$edit->preca->rule      = 'required|positive|callback_chpreca[<#i#>]';
		$edit->preca->readonly  = true;
		$edit->preca->showformat ='decimal';

		$edit->detalle = new hiddenField('', 'detalle_<#i#>');
		$edit->detalle->db_name  = 'detalle';
		$edit->detalle->rel_id   = 'sitems';

		$edit->tota = new inputField('Importe <#o#>', 'tota_<#i#>');
		$edit->tota->db_name='tota';
		$edit->tota->type      ='inputhidden';
		$edit->tota->size=10;
		$edit->tota->css_class='inputnum';
		$edit->tota->rel_id   ='sitems';
		$edit->tota->showformat ='decimal';

		for($i=1;$i<4;$i++){
			$obj='precio'.$i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj.'_<#i#>');
			$edit->$obj->db_name   = 'sinv'.$obj;
			$edit->$obj->rel_id    = 'sitems';
			$edit->$obj->pointer   = true;
		}

		$edit->precio4 = new hiddenField('', 'precio4_<#i#>');
		$edit->precio4->db_name   = 'precio4';
		$edit->precio4->rel_id    = 'sitems';

		$edit->itiva = new hiddenField('', 'itiva_<#i#>');
		$edit->itiva->db_name  = 'iva';
		$edit->itiva->rel_id   = 'sitems';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name   = 'sinvpeso';
		$edit->sinvpeso->rel_id    = 'sitems';
		$edit->sinvpeso->pointer   = true;

		$edit->sinvtipo = new hiddenField('', 'sinvtipo_<#i#>');
		$edit->sinvtipo->db_name   = 'sinvtipo';
		$edit->sinvtipo->rel_id    = 'sitems';
		$edit->sinvtipo->pointer   = true;

		//************************************************
		//fin de campos para detalle,inicio detalle2 sfpa
		//************************************************
		$edit->tipo = new  dropdownField('Tipo <#o#>', 'tipo_<#i#>');
		$edit->tipo->option('','CREDITO');
		$edit->tipo->options('SELECT tipo, nombre FROM tarjeta WHERE activo=\'S\' ORDER BY nombre');
		$edit->tipo->db_name    = 'tipo';
		$edit->tipo->rel_id     = 'sfpa';
		$edit->tipo->insertValue= 'EF';
		$edit->tipo->style      = 'width:150px;';
		$edit->tipo->onchange   = 'sfpatipo(<#i#>)';
		//$edit->tipo->rule     = 'required';

		$edit->sfpafecha = new dateonlyField('Fecha','sfpafecha_<#i#>');
		$edit->sfpafecha->rel_id   = 'sfpa';
		$edit->sfpafecha->db_name  = 'fecha';
		$edit->sfpafecha->size     = 10;
		$edit->sfpafecha->maxlength= 8;
		$edit->sfpafecha->rule ='condi_required|callback_chtipo[<#i#>]';

		$edit->numref = new inputField('Numero <#o#>', 'num_ref_<#i#>');
		$edit->numref->size     = 12;
		$edit->numref->db_name  = 'num_ref';
		$edit->numref->rel_id   = 'sfpa';
		$edit->numref->rule     = 'condi_required|callback_chtipo[<#i#>]';

		$edit->banco = new dropdownField('Banco <#o#>', 'banco_<#i#>');
		$edit->banco->option('','Ninguno');
		$edit->banco->options('SELECT cod_banc,nomb_banc
			FROM tban
			WHERE cod_banc<>\'CAJ\'
		UNION ALL
			SELECT codbanc,CONCAT_WS(\' \',TRIM(banco),numcuent)
			FROM banc
			WHERE tbanco <> \'CAJ\' ORDER BY nomb_banc');
		$edit->banco->db_name='banco';
		$edit->banco->rel_id ='sfpa';
		$edit->banco->style  ='width:180px;';
		$edit->banco->rule   ='condi_required|callback_chtipo[<#i#>]';

		$edit->monto = new inputField('Monto <#o#>', 'monto_<#i#>');
		$edit->monto->db_name   = 'monto';
		$edit->monto->css_class = 'inputnum';
		$edit->monto->rel_id    = 'sfpa';
		$edit->monto->size      = 10;
		$edit->monto->rule      = 'required|mayorcero';
		$edit->monto->showformat ='decimal';
		//************************************************
		// Fin detalle 2 (sfpa)
		//************************************************

		$edit->ivat = new hiddenField('I.V.A', 'iva');
		$edit->ivat->css_class ='inputnum';
		$edit->ivat->readonly  =true;
		$edit->ivat->size      = 10;

		$edit->totals = new hiddenField('Sub-Total', 'totals');
		$edit->totals->css_class ='inputnum';
		$edit->totals->readonly  =true;
		$edit->totals->size      = 10;

		$edit->totalg = new hiddenField('Total', 'totalg');
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->readonly  =true;
		$edit->totalg->size      = 10;

		$edit->observa   = new inputField('Observacion', 'observa');
		$edit->nfiscal   = new inputField('No.Fiscal', 'nfiscal');
		$edit->observ1   = new inputField('Observacion', 'observ1');
		$edit->zona      = new inputField('Zona', 'zona');
		$edit->ciudad    = new inputField('Ciudad', 'ciudad');
		$edit->exento    = new inputField('Exento', 'exento');
		$edit->maqfiscal = new inputField('Mq.Fiscal', 'maqfiscal');
		$edit->referen   = new inputField('Referencia', 'referen');
		$edit->pfac      = new hiddenField('Presupuesto', 'pfac');

		$edit->reiva     = new inputField('Retencion de IVA', 'reiva');
		$edit->creiva    = new inputField('Comprobante', 'creiva');
		$edit->freiva    = new inputField('Fecha', 'freiva');
		$edit->ereiva    = new inputField('Emision', 'ereiva');

		$edit->usuario   = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->estampa   = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora      = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));

		if ($edit->_status == 'show'){
			$print_url=site_url('ventas/sfac_add/dataprint/modify').$edit->pk_URI();
			$action   = "javascript:window.location='${print_url}'";
			$edit->button('btn_print', 'Imprimir', $action, 'TR');
		}

		$edit->buttons('save', 'back','add_rel','add');
		if(!empty($this->_url)) $edit->_process_uri=$this->_url;
		if(!empty($this->back_url)) $edit->buttons('undo');

		if($this->genesal){
			$edit->build();
			$conten['form']  =&  $edit;

			$data['style']   = style('redmond/jquery-ui.css');
			//$data['style']  .= style('gt_grid.css');
			//$data['style']  .= style('impromptu.css');

			$data['script']  = script('jquery.js');
			$data['script'] .= script('jquery-ui.js');
			$data['script'] .= script("jquery-impromptu.js");
			$data['script'] .= script("plugins/jquery.blockUI.js");
			$data['script'] .= script('plugins/jquery.numeric.pack.js');
			$data['script'] .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
			$data['script'] .= phpscript('nformat.js');
			$data['script'] .= script('plugins/jquery.floatnumber.js');
			$data['script'] .= script('gt_msg_en.js');
			$data['script'] .= script('gt_grid_all.js');
			$data['content'] = $this->load->view('view_sfac_add', $conten,true);
			$data['head']    = $this->rapyd->get_head();
			$data['title']   = heading($this->titp);
			$this->load->view('view_ventanas', $data);
		}else{
			$edit->on_save_redirect=false;
			$edit->build();

			if($edit->on_success()){
				$this->claves=$edit->_dataobject->pk;
				$this->claves['numero']  = $edit->_dataobject->get('numero');
				$this->claves['tipo_doc']= $edit->_dataobject->get('tipo_doc');
				$rt= 'Venta Guardada';
			}elseif($edit->on_error()){
				$rt= html_entity_decode(preg_replace('/<[^>]*>/', '', $edit->error_string));
			}
			return $rt;
		}
	}

	function dataprint($st,$uid){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Imprimir factura', 'sfac');
		$id=$edit->get_from_dataobjetct('id');
		$urlid=$edit->pk_URI();
		$url=site_url('formatos/descargar/FACTURA'.$urlid);
		if(isset($this->back_url))
			$edit->back_url = site_url($this->back_url);
		else
			$edit->back_url = site_url('ajax/reccierraventana');
			//$edit->back_url = site_url($this->url.'dataedit/show/'.$uid);

		$edit->back_save   = true;
		$edit->back_delete = true;
		$edit->back_cancel = true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		//$edit->on_save_redirect   = false;

		//$edit->post_process('update','_post_print_update');
		$edit->pre_process('insert' ,'_pre_print_insert');
		//$edit->pre_process('update' ,'_pre_print_update');
		$edit->pre_process('delete' ,'_pre_print_delete');

		$edit->container = new containerField('impresion','La descarga se realizara en 5 segundos, en caso de no hacerlo haga click '.anchor('formatos/descargar/FACTURA'.$urlid,'aqui'));

		$edit->nfiscal = new inputField('N&uacute;mero f&iacute;scal','nfiscal');
		$edit->nfiscal->rule='max_length[12]|required';
		$edit->nfiscal->size =14;
		$edit->nfiscal->maxlength =12;
		$edit->nfiscal->autocomplete=false;

		$edit->tipo_doc = new inputField('Factura','tipo_doc');
		$edit->tipo_doc->rule='max_length[1]';
		$edit->tipo_doc->size =3;
		$edit->tipo_doc->mode='autohide';
		$edit->tipo_doc->maxlength =1;

		$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->mode='autohide';
		$edit->numero->size =10;
		$edit->numero->in='tipo_doc';
		$edit->numero->maxlength =8;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule = 'chfecha';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;
		$edit->fecha->maxlength =8;

		$edit->cod_cli = new inputField('Cliente','cod_cli');
		$edit->cod_cli->rule='max_length[5]';
		$edit->cod_cli->size =7;
		$edit->cod_cli->mode='autohide';
		$edit->cod_cli->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[40]';
		$edit->nombre->size =42;
		$edit->nombre->mode='autohide';
		$edit->nombre->in='cod_cli';
		$edit->nombre->maxlength =40;

		$edit->rifci = new inputField('Rif/Ci','rifci');
		$edit->rifci->rule='max_length[13]';
		$edit->rifci->size =15;
		$edit->rifci->mode='autohide';
		$edit->rifci->maxlength =13;

		$edit->totalg = new inputField('Monto','totalg');
		$edit->totalg->rule='max_length[12]|numeric';
		$edit->totalg->css_class='inputnum';
		$edit->totalg->size =14;
		$edit->totalg->showformat='decimal';
		$edit->totalg->mode='autohide';
		$edit->totalg->maxlength =12;

		$edit->buttons('save', 'undo','back');
		$edit->build();

		$script= '<script type="text/javascript" >
		$(function() {
			setTimeout(\'window.location="'.$url.'"\',5000);
		});
		</script>';

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$data['script'] .= $script;
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);
	}

	//Chequea que el precio de los articulos de la devolucion sean los facturados
	function chpreca($val,$i){
		$tipo_doc = $this->input->post('tipo_doc');
		$codigo   = $this->input->post('codigoa_'.$i);

		if($tipo_doc == 'D'){
			$factura  = $this->input->post('factura');
			$dbfactura= $this->db->escape($factura);

			if(!isset($this->devperca)){
				$this->devpreca=array();
				$mSQL="SELECT b.codigoa,b.preca
				FROM sitems AS b
				WHERE b.numa=$dbfactura AND b.tipoa='F'";
				$query = $this->db->query($mSQL);
				foreach ($query->result() as $row){
					$ind=trim($row->codigoa);
					$this->devpreca[$ind]=$row->preca;
				}
			}

			if(isset($this->devpreca[$codigo])){
				$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' se esta devolviendo por un monto distinto al facturado que fue de '.nformat($this->devpreca[$codigo]));
				if($this->devpreca[$codigo]-$val==0){
					return true;
				}
			}else{
				$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' no fue facturado');
				return false;
			}
		}elseif($tipo_doc == 'F'){
			if(!isset($this->sclitipo)){
				$cliente  = $this->input->post('cod_cli');
				$this->sclitipo = $this->datasis->dameval('SELECT tipo FROM scli WHERE cliente='.$this->db->escape($cliente));
			}
			if($this->sclitipo=='5'){
				$precio4 = $this->datasis->dameval('SELECT ultimo FROM sinv WHERE codigo='.$this->db->escape($codigo));
			}else{
				$precio4 = $this->datasis->dameval('SELECT precio4*100/(100+iva) FROM sinv WHERE codigo='.$this->db->escape($codigo));
			}
			$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' debe contener un precio de al menos '.nformat($precio4));
			if(empty($precio4)) $precio4=0; else $precio4=round($precio4,2);
			if($val>=$precio4){
				return true;
			}
		}
		return false;
	}

	function anular($id){
		$this->rapyd->load('dataobject','datadetails');

		$do = new DataObject('sfac');
		$do->rel_one_to_many('sitems', 'sitems', array('id'=>'id_sfac'));
		$do->rel_one_to_many('sfpa'  , 'sfpa'  , array('numero','transac'));

		$do->load($id);

		$tipo_doc = $do->get('tipo_doc');
		$numero   = $do->get('numero');
		$fecha    = $do->get('fecha');
		$referen  = $do->get('referen');
		$cajero   = $do->get('cajero');
		$inicial  = $do->get('inicial');

		$dbtipo_doc = $this->db->escape($tipo_doc);
		$dbnumero   = $this->db->escape($numero);
		$dbfecha    = $this->db->escape($fecha);
		$hoy        = date('Y-m-d');

		if($tipo_doc=='F'){
			if($refere=='C' && $inicial==0){
				$mSQL ="SELECT abono FROM smov WHERE tipo_doc=$dbtipo_doc AND numero=$dbnumero AND fecha=$dbfecha";
				$abono=$this->datasis->dameval($mSQL);
				if($abono==0){
					//Anula la factura
				}
			}elseif($fecha == $hoy){
				//Anula la factura
			}
		}
	}

	//Chequea que la cantidad devuelta no sea mayor que la facturada
	function chcanadev($val,$i){
		$tipo_doc = $this->input->post('tipo_doc');
		$factura  = $this->input->post('factura');
		$codigo   = $this->input->post('codigoa_'.$i);

		if($tipo_doc=='D'){
			$dbfactura=$this->db->escape($factura);

			if(!isset($this->devitems)){
				$this->devitems=array();
				$mSQL="SELECT b.codigoa,b.cana,SUM(d.cana) AS dev
				FROM sitems AS b
				LEFT JOIN sfac AS c  ON b.numa=c.factura AND c.tipo_doc='D'
				LEFT JOIN sitems AS d ON c.numero=d.numa AND c.tipo_doc=d.tipoa AND b.codigoa=d.codigoa
				WHERE b.numa=$dbfactura AND b.tipoa='F'
				GROUP BY b.codigoa";
				$query = $this->db->query($mSQL);
				foreach ($query->result() as $row){
					$ind=trim($row->codigoa);
					$c=(empty($row->cana))? 0 : $row->cana;
					$d=(empty($row->dev))?  0 : $row->dev;
					$this->devitems[$ind]=$c-$d;
				}
			}
			if(isset($this->devitems[$codigo])){
				if($val <= $this->devitems[$codigo]){
					return true;
				}
				$this->validation->set_message('chcanadev', 'Esta devolviendo m&aacute;s de lo que se facturo del art&iacute;culo '.$codigo.' puede devolver m&aacute;ximo '.$this->devitems[$codigo]);
			}else{
				$this->validation->set_message('chcanadev', 'El art&iacute;culo '.$codigo.' no se puede devolver, nunca fue facturado o ya esta devuelto');
			}
			return false;
		}
		return true;
	}

	function chtipo($val,$i){
		$tipo=$this->input->post('tipo_'.$i);
		if(empty($tipo)) return true;
		$this->validation->set_message('chtipo', 'El campo %s es obligatorio');

		if(empty($val) && ($tipo=='NC' || $tipo=='DP'))
			return false;
		else
			return true;
	}

	function chfactura($factura){
		$tipo_doc=$this->input->post('tipo_doc');
		$this->validation->set_message('chfactura', 'El campo %s debe contener un numero de factura v&aacute;lido');
		if($tipo_doc=='D' && empty($factura)){
			return false;
		}
		return true;
	}

	function _pre_insert($do){
		$cliente= $do->get('cod_cli');
		$tipoa  = $do->get('tipo_doc');
		$con=$this->db->query("SELECT tasa,redutasa,sobretasa FROM civa ORDER BY fecha desc LIMIT 1");
		if($con->num_rows() > 0){
			$t=$con->row('tasa');$rt=$con->row('redutasa');$st=$con->row('sobretasa');
		}else{
			$do->error_message_ar['pre_ins']='Debe cargar la tabla de IVA.';
			return false;
		}

		//Validaciones del pago
		//Totaliza los pagos
		$sfpa=$credito=0;
		$cana=$do->count_rel('sfpa');
		for($i=0;$i<$cana;$i++){
			$sfpa_tipo = $do->get_rel('sfpa','tipo',$i);
			$sfpa_monto= $do->get_rel('sfpa','monto',$i);
			$sfpa+=$sfpa_monto;
			if(empty($sfpa_tipo)) $credito+=$sfpa_monto;
		}
		$sfpa=round($sfpa,2);

		//Totaliza la factura
		$totalg=0;
		$tasa=$montasa=$reducida=$monredu=$sobretasa=$monadic=$exento=0;
		$cana=$do->count_rel('sitems');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('sitems','cana' ,$i);
			$itpreca   = $do->get_rel('sitems','preca',$i);
			$itiva     = $do->get_rel('sitems','iva'  ,$i);
			$itimporte = $itpreca*$itcana;
			$iva       = $itimporte*($itiva/100);

			if($itiva-$t==0) {
				$tasa   +=$iva;
				$montasa+=$itimporte;
			}elseif($itiva-$rt==0) {
				$reducida+=$iva;
				$monredu +=$itimporte;
			}elseif($itiva-$st==0) {
				$sobretasa+=$iva;
				$monadic  +=$itimporte;
			}else{
				$exento+=$itimporte;
			}

			$totalg    +=$itimporte+$iva;
		}
		$totalg = round($totalg,2);
		if(abs($sfpa-$totalg)>0.01){
			$do->error_message_ar['pre_ins']='El monto del pago no coincide con el monto de la factura';
			return false;
		}

		$do->set('exento'   ,$exento   );
		$do->set('tasa'     ,$tasa     );
		$do->set('reducida' ,$reducida );
		$do->set('sobretasa',$sobretasa);
		$do->set('montasa'  ,$montasa  );
		$do->set('monredu'  ,$monredu  );
		$do->set('monadic'  ,$monadic  );

		$fecha  = $do->get('fecha');
		//Validacion del limite de credito del cliente
		if($credito>0 && $tipoa=='F'){
			$dbcliente=$this->db->escape($cliente);
			$rrow    = $this->datasis->damerow("SELECT limite,formap,credito,tolera,TRIM(socio) AS socio FROM scli WHERE cliente=$dbcliente");
			if($rrow!=false){
				$cdias   = $rrow['formap'];
				$pcredito= $rrow['credito'];
				$tolera  = (100+$rrow['tolera'])/100;
				$socio   = $rrow['socio'];
				$limite  = $rrow['limite']*$tolera;
			}else{
				$limite  = $cdias  = $tolera = 0;
				$pcredito= 'N';
				$socio   = null;
			}

			//Chequea la cuenta propia
			$mSQL="SELECT SUM(monto*(tipo_doc IN ('FC','GI','ND'))) AS debe, SUM(monto*(tipo_doc IN ('NC','AB','AN'))) AS haber FROM smov WHERE cod_cli=$dbcliente";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$row = $query->row();
				$saldo=$row->debe-$row->haber;
			}else{
				$saldo=0;
			}

			if($saldo >0 && ($credito > ($limite-$saldo) || $cdias<=0 || $pcredito=='N')){
				$do->error_message_ar['pre_ins']='El cliente no tiene suficiente cr&eacute;dito propio';
				return false;
			}

			//Chequea la cuenta de sus asociados (si es responsables de otros clientes)
			$mSQL="SELECT SUM(a.monto*(a.tipo_doc IN ('FC','GI','ND'))) AS debe, SUM(a.monto*(a.tipo_doc IN ('NC','AB','AN'))) AS haber
				FROM smov AS a
				JOIN scli AS b ON a.cod_cli=b.socio
				WHERE b.socio=$dbcliente";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$row = $query->row();
				$asaldo=$row->debe-$row->haber;
			}else{
				$asaldo=0;
			}

			if($saldo >0 && ($credito > ($limite-$saldo-$asaldo) || $cdias<=0 || $pcredito=='N')){
				$do->error_message_ar['pre_ins']='El cliente no tiene suficiente cr&eacute;dito de grupo';
				return false;
			}

			//Chequea el credito de su maestro (si es subordinado)
			if(!empty($socio)){
				$dbsocio= $this->db->escape($socio);
				$rrow   = $this->datasis->damerow("SELECT limite,formap,credito,tolera,socio FROM scli WHERE cliente=$dbsocio");
				if($rrow!=false){
					$mastercdias   = $rrow['formap'];
					$mastercredito = $rrow['credito'];
					$mastertolera  = (100+$rrow['tolera'])/100;
					$mastersocio   = $rrow['socio'];
					$masterlimite  = $rrow['limite']*$tolera;
				}else{
					$masterlimite = $mastercdias = $mastertolera = 0;
					$mastercredito= 'N';
					$mastersocio  = null;
				}

				$mSQL="SELECT SUM(a.monto*(a.tipo_doc IN ('FC','GI','ND'))) AS debe, SUM(a.monto*(a.tipo_doc IN ('NC','AB','AN'))) AS haber
				FROM smov AS a
				JOIN scli AS b ON a.cod_cli=b.socio
				WHERE b.socio=$dbsocio";
				$query = $this->db->query($mSQL);
				if ($query->num_rows() > 0){
					$row = $query->row();
					$mastersaldo=$row->debe-$row->haber;
				}else{
					$mastersaldo=0;
				}

				if($credito > ($masterlimite-$saldo-$mastersaldo) || $mastercdias<=0 || $mastercredito=='N'){
					$do->error_message_ar['pre_ins']='El fiador del cliente no tiene suficiente saldo';
					return false;
				}
			}
			$objdate = date_create($fecha);
			$objdate->add(new DateInterval('P'.$cdias.'D'));
			$vence   = date_format($objdate, 'Y-m-d');
			$do->set('vence',$vence);
		}else{
			$do->set('vence',$fecha);
		}
		//Fin de las validaciones

		$rrow    = $this->datasis->damerow('SELECT nombre,rifci,dire11,dire12 FROM scli WHERE cliente='.$this->db->escape($cliente));
		if($rrow!=false){
			$do->set('nombre',$rrow['nombre']);
			$do->set('direc' ,$rrow['dire11']);
			$do->set('dire1' ,$rrow['dire12']);
		}

		if($tipoa=='F'){
			$numero  = $this->datasis->fprox_numero('nsfac');
		}else{
			$numero  = $this->datasis->fprox_numero('nccli');
		}
		$transac = $this->datasis->fprox_numero('ntransa');
		$do->set('numero' ,$numero);
		$do->set('transac',$transac);
		$do->set('referen',($credito>0)? 'C': 'E');
		$vd     = $do->get('vd');
		$cajero = $do->get('cajero');
		$almacen= $do->get('almacen');
		$estampa= $do->get('estampa');
		$usuario= $do->get('usuario');
		$hora   = $do->get('hora');

		$iva=$totals=0;
		$cana=$do->count_rel('sitems');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('sitems','cana',$i);
			$itpreca   = $do->get_rel('sitems','preca',$i);
			$itiva     = $do->get_rel('sitems','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('sitems','tota'    ,$itimporte,$i);
			//$do->set_rel('sitems','mostrado',$itimporte*(1+($itiva/100)),$i);
			$do->set_rel('sitems','mostrado',0,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;

			$do->set_rel('sitems','numa'    ,$numero ,$i);
			$do->set_rel('sitems','tipoa'   ,$tipoa  ,$i);
			$do->set_rel('sitems','transac' ,$transac,$i);
			$do->set_rel('sitems','fecha'   ,$fecha  ,$i);
			$do->set_rel('sitems','vendedor',$vd     ,$i);
			$do->set_rel('sitems','usuario' ,$usuario,$i);
			$do->set_rel('sitems','estampa' ,$estampa,$i);
			$do->set_rel('sitems','hora'    ,$hora   ,$i);
		}
		$totalg = $totals+$iva;

		$cana=$do->count_rel('sfpa');
		for($i=0;$i<$cana;$i++){
			$sfpatip   = $do->get_rel('sfpa', 'tipo',$i);
			if(!empty($sfpatip)){
				$sfpatipo  = $do->get_rel('sfpa', 'tipo_doc',$i);
				$sfpa_monto= $do->get_rel('sfpa','monto'    ,$i);
				if($tipoa=='D'){
					$sfpa_monto *= -1;
				}

				if($sfpatipo=='EF') $do->set_rel('sfpa', 'fecha' , $fecha , $i);
				$do->set_rel('sfpa','tipo_doc' ,($tipoa=='F')? 'FC':'DE',$i);
				$do->set_rel('sfpa','transac'  ,$transac   ,$i);
				$do->set_rel('sfpa','vendedor' ,$vd        ,$i);
				$do->set_rel('sfpa','cod_cli'  ,$cliente   ,$i);
				$do->set_rel('sfpa','f_factura',$fecha     ,$i);
				$do->set_rel('sfpa','fecha'    ,$fecha     ,$i);
				$do->set_rel('sfpa','cobrador' ,$cajero    ,$i);
				$do->set_rel('sfpa','numero'   ,$numero    ,$i);
				$do->set_rel('sfpa','almacen'  ,$almacen   ,$i);
				$do->set_rel('sfpa','usuario'  ,$usuario   ,$i);
				$do->set_rel('sfpa','estampa'  ,$estampa   ,$i);
				$do->set_rel('sfpa','hora'     ,$hora      ,$i);
				$do->set_rel('sfpa','monto'    ,$sfpa_monto,$i);
			}else{
				$do->rel_rm('sfpa',$i);
			}
		}

		$do->set('inicial',($credito>0)? $totalg-$credito : 0);
		$do->set('totals' ,round($totals ,2));
		$do->set('totalg' ,round($totalg ,2));
		$do->set('iva'    ,round($iva    ,2));

		$this->pfac = $_POST['pfac'];
		$do->rm_get('pfac');

		return true;
	}

	function _pre_update($do){
		$do->error_message_ar['pre_upd']='No se pueden modificar facturas';
		return false;
	}

	function _pre_delete($do){
		return false;
	}

	function _post_insert($do){
		$numero  = $do->get('numero');
		$fecha   = $do->get('fecha');
		$vence   = $do->get('vence');
		$totneto = $do->get('totalg');
		$hora    = $do->get('hora');
		$usuario = $do->get('usuario');
		$transac = $do->get('transac');
		$nombre  = $do->get('nombre');
		$cod_cli = $do->get('cod_cli');
		$estampa = $do->get('estampa');
		$anticipo= round(floatval($do->get('inicial')),2);
		$referen = $do->get('referen');
		$tipo_doc= $do->get('tipo_doc');
		$iva     = $do->get('iva');
		$direc   = $do->get('direc');
		$dire1   = $do->get('dire1');

		if($referen=='C'){
			$error   = 0;

			if($tipo_doc=='F'){
				//Inserta en smov
				$data=array();
				$data['cod_cli']  = $cod_cli;
				$data['nombre']   = $nombre;
				$data['dire1']    = $direc;
				$data['dire2']    = $dire1;
				$data['tipo_doc'] = 'FC';
				$data['numero']   = $numero;
				$data['fecha']    = $fecha;
				$data['monto']    = $totneto;
				$data['impuesto'] = $iva;
				$data['abonos']   = $anticipo;
				$data['vence']    = $vence;
				$data['tipo_ref'] = '';
				$data['num_ref']  = '';
				$data['observa1'] = 'FACTURA DE CREDITO';
				$data['estampa']  = $estampa;
				$data['hora']     = $hora;
				$data['transac']  = $transac;
				$data['usuario']  = $usuario;
				$data['codigo']   = 'NOCON';
				$data['descrip']  = 'NOTA DE CONTABILIDAD';

				$sql= $this->db->insert_string('smov', $data);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'sfac'); $error++;}

				//Chequea si debe crear el abono

				if($anticipo>0){
					$mnumab = $this->datasis->fprox_numero('nabcli');

					$data=array();
					$data['cod_cli']  = $cod_cli;
					$data['nombre']   = $nombre;
					$data['dire1']    = $direc;
					$data['dire2']    = $dire1;
					$data['tipo_doc'] = 'AB';
					$data['numero']   = $mnumab;
					$data['fecha']    = $fecha;
					$data['monto']    = $anticipo;
					$data['impuesto'] = 0;
					$data['vence']    = $fecha;
					$data['tipo_ref'] = 'FC';
					$data['num_ref']  = $numero;
					$data['observa1'] = 'ABONO POR INCIAL DE FACTURA '.$numero;
					$data['usuario']  = $usuario;
					$data['estampa']  = $estampa;
					$data['hora']     = $hora;
					$data['transac']  = $transac;
					$data['fecdoc']   = $fecha;

					$mSQL = $this->db->insert_string('smov', $data);
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'sfac'); }

					//Aplica la AB a la FC
					$data=array();
					$data['numccli']    = $mnumab; //numero abono
					$data['tipoccli']   = 'AB';
					$data['cod_cli']    = $cod_cli;
					$data['tipo_doc']   = ($tipo_doc=='F')? 'FC' : 'DV';
					$data['numero']     = $numero;
					$data['fecha']      = $fecha;
					$data['monto']      = $totneto;
					$data['abono']      = $anticipo;
					$data['ppago']      = 0;
					$data['reten']      = 0;
					$data['cambio']     = 0;
					$data['mora']       = 0;
					$data['transac']    = $transac;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['usuario']    = $usuario;
					$data['reteiva']    = 0;
					$data['nroriva']    = '';
					$data['emiriva']    = '';
					$data['recriva']    = '';

					$mSQL = $this->db->insert_string('itccli', $data);
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'sfac');}
				}
			}else{ //Si es devolucion
				$factura   = $do->get('factura');
				$dbfactura = $factura;
				$debe      = $this->datasis->dameval("SELECT monto-abonos FROM smov WHERE tipo_doc='FC' AND numero=$dbfactura");
				$haber     = $totneto;
				if(empty($debe)) $debe=0;

				$saldo  = $debe-$haber;

				//Si se le debe hace un anticipo
				if($saldo<0){
					$mnumant = $this->datasis->fprox_numero('nancli');

					$data=array();
					$data['cod_cli']    = $cod_cli;
					$data['nombre']     = $nombre;
					$data['dire1']      = $direc;
					$data['dire2']      = $dire1;
					$data['tipo_doc']   = 'AN';
					$data['numero']     = $mnumant;
					$data['fecha']      = $estampa;
					$data['monto']      = abs($saldo);
					$data['impuesto']   = 0;
					$data['abonos']     = 0;
					$data['vence']      = $fecha;
					$data['tipo_ref']   = 'NC';
					$data['num_ref']    = $numero;
					$data['observa1']   = 'POR DEVOLUCION DE FACTURA '.$factura;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['transac']    = $transac;
					$data['usuario']    = $usuario;
					$data['codigo']     = 'NOCON';
					$data['descrip']    = 'NOTA DE CONTABILIDAD';

					$mSQL = $this->db->insert_string('smov', $data);
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'sfac');}
				}

				if($debe>0){
					$data=array();
					$data['cod_cli']    = $cod_cli;
					$data['nombre']     = $nombre;
					$data['dire1']      = $direc;
					$data['dire2']      = $dire1;
					$data['tipo_doc']   = 'NC';
					$data['numero']     = $numero;
					$data['fecha']      = $fecha;
					$data['monto']      = $debe;
					$data['impuesto']   = $iva;
					$data['abonos']     = 0;
					$data['vence']      = $fecha;
					$data['tipo_ref']   = 'DV';
					$data['num_ref']    = $numero;
					$data['observa1']   = 'POR DEVOLUCION DE '.$factura ;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['transac']    = $transac;
					$data['usuario']    = $usuario;
					$data['codigo']     = 'NOCON';
					$data['descrip']    = 'NOTA DE CONTABILIDAD';

					$mSQL = $this->db->insert_string('smov', $data);
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'sfac');}

					//Aplica la NC a la FC
					$data=array();
					$data['numccli']    = $numero; //numero abono
					$data['tipoccli']   = 'NC';
					$data['cod_cli']    = $cod_cli;
					$data['tipo_doc']   = 'FC';
					$data['numero']     = $factura;
					$data['fecha']      = $fecha;
					$data['monto']      = $debe;
					$data['abono']      = $debe;
					$data['ppago']      = 0;
					$data['reten']      = 0;
					$data['cambio']     = 0;
					$data['mora']       = 0;
					$data['transac']    = $transac;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['usuario']    = $usuario;
					$data['reteiva']    = 0;
					$data['nroriva']    = '';
					$data['emiriva']    = '';
					$data['recriva']    = '';

					$sql= $this->db->insert_string('itccli', $data);
					$ban=$this->db->simple_query($sql);
					if($ban==false){ memowrite($sql,'sfac'); $error++;}
				}
			}
		}

		//Descuento del inventario
		$almacen=$do->get('almacen');
		$dbalma = $this->db->escape($almacen);
		$cana=$do->count_rel('sitems');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('sitems','cana',$i);
			$itcodigoa = $do->get_rel('sitems','codigoa',$i);
			$dbcodigoa = $this->db->escape($itcodigoa);

			$factor=($tipo_doc=='F')? -1:1;
			$sql="INSERT IGNORE INTO itsinv (alma,codigo,existen) VALUES ($dbalma,$dbcodigoa,0)";
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'sfac'); $error++;}

			$sql="UPDATE itsinv SET existen=existen+$factor*$itcana WHERE codigo=$dbcodigoa AND alma=$dbalma";
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'sfac'); $error++;}

			$sql="UPDATE sinv   SET existen=existen+$factor*$itcana WHERE codigo=$dbcodigoa";
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'sfac'); $error++;}

		}

		//Si viene de pfac
		if(strlen($this->pfac)>7 && $tipo_doc == 'F'){
			$this->db->where('numero', $this->pfac);
			$this->db->update('pfac', array('factura' => $numero,'status' => 'C'));
			$dbpfac=$this->db->escape($this->pfac);

			$sql="UPDATE itpfac AS c JOIN sinv   AS d ON d.codigo=c.codigoa
			SET d.exord=IF(d.exord>c.cana,d.exord-c.cana,0)
			WHERE c.numa = $dbpfac";
			$ban=$this->db->simple_query($sql);
			if($ban==false){ memowrite($sql,'sfac'); $error++;}
		}

		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits ${tipo_doc}${numero}");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$numero   = $do->get('numero');
		$tipo_doc = $do->get('tipo_doc');

		$primary =implode(',',$do->pk);
		logusu($do->table,"Anulo ${tipo_doc}${numero} $this->tits $primary ");
	}

	function creafrompfac($numero,$status=null){
		$this->_url= $this->url.'dataedit/insert';

		$sel=array('a.cod_cli','b.nombre','b.tipo','b.rifci','b.dire11 AS direc'
		,'a.totals','a.iva','a.totalg','TRIM(a.factura) AS factura');
		$this->db->select($sel);
		$this->db->from('pfac AS a');
		$this->db->join('scli AS b','a.cod_cli=b.cliente');
		$this->db->where('a.numero',$numero);
		$this->db->where('a.status','P');
		$query = $this->db->get();

		if ($query->num_rows() > 0 && $status=='create'){
			$row = $query->row();
			if(empty($row->factura)){
				$_POST=array(
					'btn_submit' => 'Guardar',
					'fecha'      => inputDateFromTimestamp(mktime(0,0,0)),
					'cajero'     => $this->secu->getcajero(),
					'vd'         => $this->secu->getvendedor(),
					'almacen'    => $this->secu->getalmacen(),
					'tipo_doc'   => 'F',
					'factura'    => '',
					'cod_cli'    => $row->cod_cli,
					'sclitipo'   => $row->tipo,
					'nombre'     => rtrim($row->nombre),
					'rifci'      => $row->rifci,
					'direc'      => rtrim($row->direc),
					'totals'     => $row->totals,
					'iva'        => $row->iva,
					'totalg'     => $row->totalg,
					'pfac'       => $numero,
				);

				$itsel=array('a.codigoa','b.descrip AS desca','a.cana','a.preca','a.tota','b.iva',
				'b.precio1','b.precio2','b.precio3','b.precio4','b.tipo','b.peso');
				$this->db->select($itsel);
				$this->db->from('itpfac AS a');
				$this->db->join('sinv AS b','b.codigo=a.codigoa');
				$this->db->where('a.numa',$numero);
				$this->db->where('a.cana >','0');
				$qquery = $this->db->get();
				$i=0;

				foreach ($qquery->result() as $itrow){
					$_POST["codigoa_$i"]  = rtrim($itrow->codigoa);
					$_POST["desca_$i"]    = rtrim($itrow->desca);
					$_POST["cana_$i"]     = $itrow->cana;
					$_POST["preca_$i"]    = $itrow->preca;
					$_POST["tota_$i"]     = $itrow->tota;
					$_POST["precio1_$i"]  = $itrow->precio1;
					$_POST["precio2_$i"]  = $itrow->precio2;
					$_POST["precio3_$i"]  = $itrow->precio3;
					$_POST["precio4_$i"]  = $itrow->precio4;
					$_POST["itiva_$i"]    = $itrow->iva;
					$_POST["sinvpeso_$i"] = $itrow->peso;
					$_POST["sinvtipo_$i"] = $itrow->tipo;
					$_POST["detalle_$i"]  = '';
					$i++;
				}

				//sfpa
				$i=0;
				$_POST["tipo_$i"]      = '';
				$_POST["sfpafecha_$i"] = '';
				$_POST["num_ref_$i"]   = '';
				$_POST["banco_$i"]     = '';
				$_POST["monto_$i"]     = 0;

				$this->dataedit();
			}else{
				$url='ventas/pfaclitemayor/filteredgrid';
				$this->rapyd->uri->keep_persistence();
				$persistence = $this->rapyd->session->get_persistence($url, $this->rapyd->uri->gfid);
				$back= (isset($persistence['back_uri'])) ?$persistence['back_uri'] : $url;

				$data['content'] = 'Pedido ya fue facturado'.br();
				$data['content'].= anchor($back,'Regresar');
				$data['head']    = $this->rapyd->get_head();
				$data['title']   = heading('Actualizar compra');
				$this->load->view('view_ventanas', $data);
			}
		}
	}

	function _pre_print_insert($do){ return false;}
	function _pre_print_delete($do){ return false;}

	function instalar(){

	}
}
