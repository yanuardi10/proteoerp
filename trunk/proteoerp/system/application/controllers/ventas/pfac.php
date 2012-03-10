<?php require_once(BASEPATH . 'application/controllers/validaciones.php');
class pfac extends validaciones{
	var $genesal=true;

	function pfac(){
		parent :: Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(120,1);
		$this->instalar();
	}

	function index(){
		//redirect('ventas/pfac/filteredgrid');
		if(!$this->db->field_exists('fenvia','pfac'))
		$this->db->query("ALTER TABLE `pfac`  ADD COLUMN `fenvia` DATE NULL DEFAULT '0000-00-00' COMMENT 'fecha en que el vendedor termino el pedido'");

		if(!$this->db->field_exists('faplica','pfac'))
		$this->db->query("ALTER TABLE `pfac`  ADD COLUMN `faplica` DATE NULL DEFAULT '0000-00-00' COMMENT 'fecha en que se aplicaron los descuentos'");

		if(!$this->db->field_exists('faplica','pfac'))
		$this->db->query("ALTER TABLE `pfac`  ADD COLUMN `reserva` CHAR(1) NOT NULL DEFAULT 'N'");

		if ( !$this->datasis->iscampo('pfac','id') ) {
			$this->db->simple_query('ALTER TABLE pfac DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE pfac ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE pfac ADD UNIQUE INDEX numero (numero)');
		}
		$this->pfacextjs();
	}

	function filteredgrid(){
		$this->rapyd->load('datagrid', 'datafilter');

		$atts = array(
			'width'      => '800',
			'height'     => '600',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '0',
			'screeny'    => '0'
		);

		$atts2 = array(
			'width'      => '480',
			'height'     => '240',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '980',
			'screeny'    => '760'
		);

		$scli = array(
			'tabla' => 'scli',
			'columnas' => array(
				'cliente' => 'C&oacute;digo Cliente',
				'nombre' => 'Nombre',
				'contacto' => 'Contacto'),
			'filtro' => array('cliente' => 'C&oacute;digo Cliente', 'nombre' => 'Nombre'),
			'retornar' => array('cliente' => 'cod_cli'),
			'titulo' => 'Buscar Cliente');

		$boton = $this->datasis->modbus($scli);

		$filter = new DataFilter('Filtro de Pedidos Clientes', 'pfac');

		$filter->fechad = new dateonlyField('Desde', 'fechad');
		$filter->fechah = new dateonlyField('Hasta', 'fechah');
		$filter->fechad->clause = $filter->fechah->clause   = 'where';
		$filter->fechad->db_name = $filter->fechah->db_name = 'fecha';
		//$filter->fechad->insertValue = date('Y-m-d');
		//$filter->fechah->insertValue = date('Y-m-d');
		$filter->fechah->size = $filter->fechad->size = 10;
		$filter->fechad->operator = '>=';
		$filter->fechah->operator = '<=';
		$filter->fechad->group = "uno";
		$filter->fechah->group = "uno";

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 10;
		$filter->numero->group = "dos";

		$filter->cliente = new inputField('Cliente', 'cod_cli');
		$filter->cliente->size = 8;
		$filter->cliente->append($boton);
		$filter->cliente->group = "dos";

		$filter->buttons('reset', 'search');
		$filter->build('dataformfiltro');

		$uri = anchor('ventas/pfac/dataedit/show/<#id#>', '<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/PFAC/<#numero#>', 'Ver HTML', $atts);
		$uri3 = anchor_popup('ventas/sfac/creadpfacf/<#numero#>', 'Facturar', $atts2);

		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."ventas/pfac/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."reportes/index/pfac', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600" width="900" '.'>';
		$mtool .= img(array('src' => 'images/reportes.gif', 'alt' => 'Reportes', 'title' => 'Reportes','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";


		$grid = new DataGrid($mtool);
		$grid->order_by('numero', 'desc');
		$grid->per_page = 50;

		//$grid->column('Vista'    , $uri2, "align='center'");
		$grid->column_orderby('N&uacute;mero', $uri ,'numero');
		$grid->column_orderby('Facturar'     , $uri3,'numero');
		$grid->column_orderby("Fecha"        , '<dbdate_to_human><#fecha#></dbdate_to_human>','fecha', "align='center'");
		$grid->column_orderby("Cliente"      , 'cod_cli','cod_cli');
		$grid->column_orderby("Nombre"       , 'nombre','nombre');
		$grid->column_orderby('Sub.Total'    , '<nformat><#totals#></nformat>', "totals", "align=right");
		$grid->column_orderby('IVA'          , '<nformat><#iva#></nformat>'   , "iva",    "align=right");
		$grid->column_orderby('Total'        , '<nformat><#totalg#></nformat>', "totalg", "align=right");
		$grid->column_orderby("Referencia"   , 'referen','referen');
		$grid->column_orderby("Factura"      , 'factura','factura');
		$grid->column_orderby("Status"       , 'status', 'status');

		//$grid->add('ventas/pfac/dataedit/create');
		$grid->build('datagridST');

// *************************************
//
//       Para usar SuperTable
//
// *************************************
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
	headerRows : 1,
	onStart : function () {	this.start = new Date();},
	onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
	});
})();
//]]>
</script>
';
		$style ='
<style type="text/css">
.fakeContainer { /* The parent container */
    margin: 5px;
    padding: 0px;
    border: none;
    width: 740px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>';

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('jquery.alerts.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('superTables.js');

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['style']	.= style("jquery.alerts.css");

		$data['extras']  = $extras;

		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Pedidos Clientes');
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject', 'datadetails');

		$modbus = array(
			'tabla' => 'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro' => array('codigo' => 'C&oacute;digo', 'descrip' => 'Descripci&oacute;n'),
			'retornar'  => array(
				'codigo'  => 'codigoa_<#i#>',
				'descrip' => 'desca_<#i#>',
				'base1'   => 'precio1_<#i#>',
				'base2'   => 'precio2_<#i#>',
				'base3'   => 'precio3_<#i#>',
				'base4'   => 'precio4_<#i#>',
				'iva'     => 'itiva_<#i#>',
				'tipo'    => 'sinvtipo_<#i#>',
				'peso'    => 'sinvpeso_<#i#>',
				'precio1' => 'itpvp_<#i#>',
				'pond'    => 'itcosto_<#i#>',
				'pond'    => 'pond_<#i#>',
				'mmargen' => 'mmargen_<#i#>',
				'formcal' => 'formcal_<#i#>',
				'ultimo'  => 'ultimo_<#i#>',
				'pm'      => 'pm_<#i#>',
			),
			'p_uri' => array(4 => '<#i#>'),
			'titulo' => 'Buscar Articulo',
			'where' => '`activo` = "S"',
			'script' => array('post_modbus_sinv(<#i#>)')
		);
		$btn = $this->datasis->p_modbus($modbus, '<#i#>');

		$mSCLId = array(
			'tabla'    => 'scli',
			'columnas' => array(
				'cliente' => 'C&oacute;digo Cliente',
				'nombre'  => 'Nombre',
				'cirepre' => 'Rif/Cedula',
				'dire11'  => 'Direcci&oacute;n',
				'tipo' => 'Tipo'),
			'filtro'   => array('cliente' => 'C&oacute;digo Cliente', 'nombre' => 'Nombre'),
			'retornar' => array('cliente' => 'cod_cli', 'nombre' => 'nombre', 'rifci' => 'rifci',
				'dire11' => 'direc', 'tipo' => 'sclitipo','mmargen'=>'mmargen'),
			'titulo' => 'Buscar Cliente',
			'script' => array('post_modbus_scli()'));
		$boton = $this->datasis->modbus($mSCLId);

		$inven=array();
		$query=$this->db->query('SELECT TRIM(codigo) AS codigo ,TRIM(descrip) AS descrip,tipo,base1,base2,base3,base4,iva,peso,precio1,pond FROM sinv WHERE activo=\'S\'');
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$ind='_'.$row->codigo;
				$inven[$ind]=array($row->descrip,$row->tipo,$row->base1,$row->base2,$row->base3,$row->base4,$row->iva,$row->peso,$row->precio1,$row->pond);
			}
		}
		$jinven=json_encode($inven);

		$do = new DataObject('pfac');
		$do->rel_one_to_many('itpfac', 'itpfac', array('numero' => 'numa'));
		$do->pointer('scli' , 'scli.cliente=pfac.cod_cli', 'scli.tipo AS sclitipo', 'left');
		$do->rel_pointer('itpfac', 'sinv', 'itpfac.codigoa=sinv.codigo', 'sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo,sinv.precio1 As sinvprecio1,sinv.pond AS sinvpond,sinv.mmargen as sinvmmargen,sinv.ultimo sinvultimo,sinv.formcal sinvformcal,sinv.pm sinvpm,itpfac.preca precat');

		$edit = new DataDetails('Pedidos', $do);
		$edit->back_url = site_url('ventas/pfac/filteredgrid');
		$edit->set_rel_title('itpfac', 'Producto <#o#>');

		$edit->pre_process('insert' , '_pre_insert');
		$edit->pre_process('update' , '_pre_update');
		$edit->pre_process('delete' , '_pre_delete');
		$edit->post_process('insert', '_post_insert');
		$edit->post_process('update', '_post_update');
		$edit->post_process('delete', '_post_delete');

		$fenvia  =strtotime($edit->get_from_dataobjetct('fenvia'));
		$faplica =strtotime($edit->get_from_dataobjetct('faplica'));
		$hoy     =strtotime(date('Y-m-d'));

		$edit->fecha = new DateonlyField('Fecha', 'fecha', 'd/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->vd = new dropdownField ('Vendedor', 'vd');
		$edit->vd->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vd->style = 'width:200px;';
		$edit->vd->size = 5;

		$edit->mmargen = new inputField('mmargen', 'mmargen');

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode = 'autohide';
		$edit->numero->maxlength = 8;
		$edit->numero->apply_rules = false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when = array('show', 'modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly = true;
		$edit->peso->size = 10;
		$edit->peso->type ='inputhidden';

		$edit->cliente = new inputField('Cliente', 'cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->rule = 'required';
		$edit->cliente->maxlength = 5;
		if(!($faplica < $fenvia)) $edit->cliente->append($boton);
		$edit->cliente->autocomplete=false;

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 30;
		$edit->nombre->maxlength = 40;
		$edit->nombre->rule = 'required';
		$edit->nombre->type ='inputhidden';

		$edit->rifci = new inputField('RIF/CI', 'rifci');
		$edit->rifci->autocomplete = false;
		$edit->rifci->size = 15;
		$edit->rifci->type ='inputhidden';

		$edit->direc = new inputField('Direcci&oacute;n', 'direc');
		$edit->direc->size = 40;
		$edit->direc->type ='inputhidden';

		$edit->observa = new inputField('Observaciones', 'observa');
		$edit->observa->size = 50;

		$edit->observ1 = new inputField('Observaciones', 'observ1');
		$edit->observ1->size = 50;

		// Para saber que precio se le va a dar al cliente
		$edit->sclitipo = new hiddenField('', 'sclitipo');
		$edit->sclitipo->db_name = 'sclitipo';
		$edit->sclitipo->pointer = true;
		$edit->sclitipo->insertValue = 1;

		// Campos para el detalle
		$edit->codigoa = new inputField('C&oacute;digo <#o#>', 'codigoa_<#i#>');
		$edit->codigoa->size = 12;
		$edit->codigoa->db_name = 'codigoa';
		$edit->codigoa->rel_id = 'itpfac';
		$edit->codigoa->rule = 'required|callback_chcodigoa';
		$edit->codigoa->onkeyup = 'OnEnter(event,<#i#>)';
		if(!($faplica < $fenvia))
		$edit->codigoa->append($btn);

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size = 32;
		$edit->desca->db_name = 'desca';
		$edit->desca->maxlength = 50;
		$edit->desca->readonly = true;
		$edit->desca->rel_id = 'itpfac';
		$edit->desca->type='inputhidden';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name = 'cana';
		$edit->cana->css_class = 'inputnum';
		$edit->cana->rel_id = 'itpfac';
		$edit->cana->maxlength = 10;
		$edit->cana->size = 5;
		$edit->cana->rule = 'required|positive';
		$edit->cana->autocomplete = false;
		$edit->cana->onkeyup = 'importe(<#i#>)';
		//$edit->cana->insertValue=1;

		$edit->preca = new inputField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id = 'itpfac';
		$edit->preca->size = 10;
		$edit->preca->rule = 'required|positive|callback_chpreca[<#i#>]';
		$edit->preca->readonly = true;

		$edit->dxapli = new inputField('Precio <#o#>', 'dxapli_<#i#>');
		$edit->dxapli->db_name = 'dxapli';
		$edit->dxapli->rel_id = 'itpfac';
		$edit->dxapli->size = 1;
		$edit->dxapli->rule = 'trim';
		$edit->dxapli->onchange="cal_dxapli(<#i#>)";

		$edit->tota = new inputField('importe <#o#>', 'tota_<#i#>');
		$edit->tota->db_name = 'tota';
		$edit->tota->size = 8;
		$edit->tota->css_class = 'inputnum';
		$edit->tota->rel_id = 'itpfac';
		$edit->tota->type='inputhidden';

		for($i = 1;$i <= 4;$i++){
			$obj = 'precio' . $i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj . '_<#i#>');
			$edit->$obj->db_name = 'sinv' . $obj;
			$edit->$obj->rel_id = 'itpfac';
			$edit->$obj->pointer = true;
		}

		$edit->itiva = new hiddenField('', 'itiva_<#i#>');
		$edit->itiva->db_name = 'iva';
		$edit->itiva->rel_id = 'itpfac';

		$edit->itpvp = new hiddenField('', 'itpvp_<#i#>');
		$edit->itpvp->db_name = 'pvp';
		$edit->itpvp->rel_id = 'itpfac';

		$edit->itcosto = new hiddenField('', 'itcosto_<#i#>');
		$edit->itcosto->db_name = 'costo';
		$edit->itcosto->rel_id = 'itpfac';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name = 'sinvpeso';
		$edit->sinvpeso->rel_id = 'itpfac';
		$edit->sinvpeso->pointer = true;

		$edit->sinvtipo = new hiddenField('', 'sinvtipo_<#i#>');
		$edit->sinvtipo->db_name = 'sinvtipo';
		$edit->sinvtipo->rel_id = 'itpfac';
		$edit->sinvtipo->pointer = true;

		$edit->itmmargen = new hiddenField('', 'mmargen_<#i#>');
		$edit->itmmargen->db_name = 'sinvmmargen';
		$edit->itmmargen->rel_id = 'itpfac';
		$edit->itmmargen->pointer = true;

		$edit->itpond = new hiddenField('', 'pond_<#i#>');
		$edit->itpond->db_name = 'sinvpond';
		$edit->itpond->rel_id  = 'itpfac';
		$edit->itpond->pointer = true;

		$edit->itultimo = new hiddenField('', 'ultimo_<#i#>');
		$edit->itultimo->db_name = 'sinvultimo';
		$edit->itultimo->rel_id  = 'itpfac';
		$edit->itultimo->pointer = true;

		$edit->itformcal = new hiddenField('', 'formcal_<#i#>');
		$edit->itformcal->db_name = 'sinvformcal';
		$edit->itformcal->rel_id  = 'itpfac';
		$edit->itformcal->pointer = true;

		$edit->itpm = new hiddenField('', 'pm_<#i#>');
		$edit->itpm->db_name = 'sinvpm';
		$edit->itpm->rel_id  = 'itpfac';
		$edit->itpm->pointer = true;

		$edit->precat = new hiddenField('', 'precat_<#i#>');
		$edit->precat->db_name = 'precat';
		$edit->precat->rel_id  = 'itpfac';
		$edit->precat->pointer = true;
		// fin de campos para detalle

		$edit->ivat = new hiddenField('Impuesto', 'iva');
		$edit->ivat->css_class = 'inputnum';
		$edit->ivat->readonly = true;
		$edit->ivat->size = 10;

		$edit->totals = new hiddenField('Sub-Total', 'totals');
		$edit->totals->css_class = 'inputnum';
		$edit->totals->readonly = true;
		$edit->totals->size = 10;

		$edit->totalg = new hiddenField('Monto Total', 'totalg');
		$edit->totalg->css_class = 'inputnum';
		$edit->totalg->readonly = true;
		$edit->totalg->size = 10;

		$edit->usuario = new autoUpdateField('usuario', $this->session->userdata('usuario'), $this->session->userdata('usuario'));

		$control=$this->rapyd->uri->get_edited_id();

		if($fenvia < $hoy){
			$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');

			$accion="javascript:window.location='".site_url('ventas/pfaclite/enviar/'.$control)."'";
			$edit->button_status('btn_envia'  ,'Enviar Pedido'         ,$accion,'TR','show');
		}elseif($faplica < $fenvia){
			$hide=array('vd','peso','cliente','nombre','rifci','direc','observa','observ1','codigoa','desca','cana');
			foreach($hide as $value)
			$edit->$value->type="inputhidden";

			$accion="javascript:window.location='".site_url('ventas/pfac/dataedit/modify/'.$control)."'";
			$edit->button_status('btn_envia'  ,'Aplicar Descuentos'         ,$accion,'TR','show');

			$edit->buttons( 'save', 'undo', 'delete', 'back');
		}else{
			$edit->buttons('save', 'undo', 'delete', 'back', 'add_rel');
		}

		if($this->genesal){
			$edit->build();

			$conten['inven']   = $jinven;
			$conten['form']    = & $edit;
			$conten['hoy']     = $hoy;
			$conten['fenvia']  = $fenvia;
			$conten['faplica'] = $faplica;
			$data['content'] = $this->load->view('view_pfac', $conten, true);
			$data['title']   = heading('Pedidos No. '.$edit->numero->value);

			$data['style']  = style('redmond/jquery-ui.css');
			$data['head']   = script('jquery.js');
			$data['head']  .= script('jquery-ui.js');
			$data['head']  .= script('plugins/jquery.numeric.pack.js');
			$data['head']  .= script('plugins/jquery.floatnumber.js');
			$data['head']  .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
			$data['head']  .= phpscript('nformat.js');
			$data['head']  .= $this->rapyd->get_head();

			$this->load->view('view_ventanas', $data);
		}else{
			$edit->on_save_redirect=false;
			$edit->build();

			if($edit->on_success()){
				echo 'Pedido Guardado';
			}elseif($edit->on_error()){
				echo html_entity_decode(preg_replace('/<[^>]*>/', '', $edit->error_string));
			}
		}
	}

	function pos(){
		$this->rapyd->load('dataobject','datadetails');

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
					'dire11'=>'direc'),
		'titulo'  =>'Buscar Cliente',
		'script'  => array('post_modbus_scli()'));
		$boton =$this->datasis->modbus($mSCLId);

		$query = $this->db->query("SELECT tipo,nombre FROM tarjeta ORDER BY tipo");
		foreach ($query->result() as $row){
			$sfpa[$row->tipo]=$row->nombre;
		}

		$tban['']='Banco';
		$query = $this->db->query("SELECT cod_banc,nomb_banc FROM tban WHERE cod_banc<>'CAJ' ORDER BY nomb_banc");
		foreach ($query->result() as $row){
			$tban[$row->cod_banc]=$row->nomb_banc;
		}

		$conten=array();
		$conten['sfpa']  = $sfpa;
		$conten['tban']  = $tban;
		$data['content'] = $this->load->view('view_pos_pfac', $conten,true);
		$data['title']   = '';
		$data['head']    = style('redmond/jquery-ui-1.8.1.custom.css');
		$data['head']   .= style('ui.jqgrid.css');
		$data['head']   .= style('ui.multiselect.css');
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('interface.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$data['head']   .= script('jquery.layout.js');
		$data['head']   .= script('i18n/grid.locale-sp.js');
		$data['head']   .= script('ui.multiselect.js');
		$data['head']   .= script('jquery.jqGrid.min.js');
		$data['head']   .= script('jquery.tablednd.js');
		$data['head']   .= script('jquery.contextmenu.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');
		$data['head']   .= phpscript('nformat.js');

		$this->load->view('view_ventanas_sola', $data);
	}

	function posmayor(){
		$this->rapyd->load('dataobject','datadetails');

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
					'dire11'=>'direc'),
		'titulo'  =>'Buscar Cliente',
		'script'  => array('post_modbus_scli()'));
		$boton =$this->datasis->modbus($mSCLId);

		$query = $this->db->query("SELECT tipo,nombre FROM tarjeta ORDER BY tipo");
		foreach ($query->result() as $row){
			$sfpa[$row->tipo]=$row->nombre;
		}

		$tban['']='Banco';
		$query = $this->db->query("SELECT cod_banc,nomb_banc FROM tban WHERE cod_banc<>'CAJ' ORDER BY nomb_banc");
		foreach ($query->result() as $row){
			$tban[$row->cod_banc]=$row->nomb_banc;
		}

		$conten=array();
		$conten['sfpa']  = $sfpa;
		$conten['tban']  = $tban;
		$data['content'] = $this->load->view('view_pos_pfac_mayor', $conten,true);
		$data['title']   = '';
		$data['head']    = style('redmond/jquery-ui-1.8.1.custom.css');
		$data['head']   .= style('ui.jqgrid.css');
		$data['head']   .= style('ui.multiselect.css');
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('interface.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$data['head']   .= script('jquery.layout.js');
		$data['head']   .= script('i18n/grid.locale-sp.js');
		$data['head']   .= script('ui.multiselect.js');
		$data['head']   .= script('jquery.jqGrid.min.js');
		$data['head']   .= script('jquery.tablednd.js');
		$data['head']   .= script('jquery.contextmenu.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');
		$data['head']   .= phpscript('nformat.js');

		$this->load->view('view_ventanas_sola', $data);
	}

	// Busca Productos para autocomplete
	function buscasinv(){
		$data = '{[ ]}';
		$mid  = $this->input->post('q');
		$cod  = $this->input->post('codigo');
		$scli = $this->input->post('cod_cli');
		if(strlen($scli)==0){ echo $data; return; }

		$sql='SELECT mmargen FROM scli WHERE cliente='.$this->db->escape($scli);
		$scli_margen=$this->datasis->dameval($sql);
		$scli_margen=$scli_margen/100;

		$qdb  = $this->db->escape('%'.$mid.'%');
		$qba  = $this->db->escape($mid);
		$coddb= $this->db->escape($cod);

		$pp='precio1*(1-(mmargen/100))*(1-'.$scli_margen.')';

		if($mid !== false){
			$retArray = $retorno = array();

			if(preg_match('/\+(?P<cana>\d+)/', $mid, $matches)>0 && $cod!==false){
				$mSQL="SELECT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.$pp AS precio, a.iva,a.existen
				FROM sinv AS  a
				WHERE a.codigo=$coddb LIMIT 1";
				$cana=$matches['cana'];
			}else{
				$mSQL="SELECT DISTINCT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.$pp AS precio, a.iva,a.existen
				FROM sinv AS a
				LEFT JOIN barraspos AS b ON a.codigo=b.codigo
				WHERE (a.codigo LIKE $qdb OR a.descrip LIKE  $qdb OR a.barras LIKE $qdb OR b.suplemen=$qba) AND a.activo='S'
				ORDER BY a.descrip LIMIT 10";
				$cana=1;
			}

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].') '.$row['descrip'].' '.$row['precio'].' Bs. - '.$row['existen'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['cana']    = $cana;
					$retArray['precio']  = round($row['precio'],2);
					$retArray['descrip'] = $row['descrip'];
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
		}
		}
		echo $data;
	}

	function creapfac(){
		foreach($_POST as $ind=>$val){
			$matches=array();
			$_POST['fecha']=date('d/m/Y');

			if(preg_match('/codigoa_(?P<id>\d+)/', $ind, $matches) > 0){
				$id     = $matches['id'];
				$precio = $_POST['precio_'.$id];
				$iva    = $_POST['itiva_'.$id];
				$_POST['preca_'.$id] = round($precio*100/(100+$iva),2);
			}
		}
		//print_r($_POST);
		$this->genesal=false;
		$rt=$this->dataedit();
		echo $rt;
	}



	function _pre_insert($do){
		$numero = $this->datasis->fprox_numero('npfac');
		$do->set('numero', $numero);
		//$transac = $this->datasis->fprox_numero('ntransa');
		//$do->set('transac', $transac);
		$fecha = $do->get('fecha');
		$vd    = $do->get('vd');

		$iva = $totals = 0;
		$cana = $do->count_rel('itpfac');
		for($i = 0;$i < $cana;$i++){
			$itcana  = $do->get_rel('itpfac', 'cana', $i);
			$itpreca = $do->get_rel('itpfac', 'preca', $i);
			$itiva   = $do->get_rel('itpfac', 'iva', $i);
			$ittota  = $itpreca * $itcana;
			$do->set_rel('itpfac', 'tota'    , $ittota, $i);
			$do->set_rel('itpfac', 'fecha'   , $fecha , $i);
			$do->set_rel('itpfac', 'vendedor', $vd , $i);

			$iva    += $ittota * ($itiva / 100);
			$totals += $ittota;
			$do->set_rel('itpfac', 'mostrado', $iva + $ittota, $i);
		}
		$totalg = $totals + $iva;

		$do->set('totals' , round($totals , 2));
		$do->set('totalg' , round($totalg , 2));
		$do->set('iva'    , round($iva    , 2));
		$do->set('status' , 'P');
		return true;
	}

	function _pre_update($do){
		$error='';
		$codigo = $do->get('numero');
		$fecha  = $do->get('fecha');
		$vd     = $do->get('vd');
		$fenvia = $do->get('fenvia');
		$faplica= $do->get('faplica');

		$iva = $totals = 0;
		$cana = $do->count_rel('itpfac');
		for($i = 0;$i < $cana;$i++){
			$codigoa = $do->get_rel('itpfac', 'codigoa', $i);
			$itcana  = $do->get_rel('itpfac', 'cana'   , $i);
			$itpreca = $do->get_rel('itpfac', 'preca'  , $i);
			$itiva   = $do->get_rel('itpfac', 'iva'    , $i);

			if(($faplica < $fenvia)){
				$itdxapli = $do->get_rel('itpfac', 'dxapli', $i);
				$itprecat = $this->input->post("precat_$i");
				if(!$itdxapli)
				$itdxapli=' ';

				$itpreca  = $this->cal_dxapli($itprecat,$itdxapli);
				if(1*$itpreca>0){
					$do->set_rel('itpfac', 'preca'  , $itpreca, $i);
					$do->set('faplica',date('Y-m-d'));
				}else{
					$error.="Error. El descuento por aplicar es incorrecto para el codigo $codigoa</br>";
				}
			}

			$ittota  = $itpreca * $itcana;
			$do->set_rel('itpfac', 'tota'    , $ittota, $i);
			$do->set_rel('itpfac', 'fecha'   , $fecha , $i);
			$do->set_rel('itpfac', 'vendedor', $vd    , $i);

			$iva    += $ittota*$itiva/100;
			$totals += $ittota;
			$do->set_rel('itpfac', 'mostrado', $iva + $ittota, $i);
		}
		$totalg = $totals + $iva;

		$do->set('totals' , round($totals , 2));
		$do->set('totalg' , round($totalg , 2));
		$do->set('iva'    , round($iva    , 2));

		$dbnuma=$this->db->escape($codigo);
		$mSQL  ="UPDATE itpfac AS c JOIN sinv   AS d ON d.codigo=c.codigoa
			SET d.exord=IF(d.exord>c.cana,d.exord-c.cana,0)
			WHERE c.numa = $dbnuma";

		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'pfac'); }

		if(!empty($error)){
			$do->error_message_ar['pre_ins']=$error;
			$do->error_message_ar['pre_upd']=$error;
			return false;
		}
		return true;
	}

	// Busca Clientes para autocomplete
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

	function chcodigoa($codigo){
		$cana=$this->datasis->dameval('SELECT COUNT(*) FROM sinv WHERE activo=\'S\' AND codigo='.$this->db->escape($codigo));
		if(empty($cana) || $cana==0){
			$this->validation->set_message('chcodigoa', 'El campo %s contiene un codigo no v&aacute;lido o inactivo');
			return false;
		}
		return true;
	}

	function _post_insert($do){
		$cana = $do->count_rel('itpfac');
		for($i = 0;$i < $cana;$i++){
			$itcodigo= $do->get_rel('itpfac', 'codigoa', $i);
			$itcana  = $do->get_rel('itpfac', 'cana', $i);
			$mSQL = "UPDATE sinv SET exdes=exdes+$itcana WHERE codigo=".$this->db->escape($itcodigo);

			$ban=$this->db->simple_query($mSQL);
			if($ban==false){ memowrite($mSQL,'pfac'); }
		}

		$codigo = $do->get('numero');
		logusu('pfac', "Pedido $codigo CREADO");
	}

	function chpreca($preca, $ind){
		$codigo = $this->input->post('codigoa_' . $ind);
		$precio4 = $this->datasis->dameval('SELECT base4 FROM sinv WHERE codigo=' . $this->db->escape($codigo));
		if($precio4 < 0) $precio4 = 0;

		if($preca < $precio4){
			$this->validation->set_message('chpreca', 'El art&iacute;culo ' . $codigo . ' debe contener un precio de al menos ' . nformat($precio4));
			return false;
		}else{
			return true;
		}
	}


	function enviar($id,$dir='pfac'){
		$ide=$this->db->escape($id);
		$this->db->query("UPDATE pfac SET fenvia=CURDATE() WHERE id=$ide");
		redirect("ventas/$dir/dataedit/show/$id");
	}

	function aplicar($numero){

	}

	function _post_update($do){
		$cana = $do->count_rel('itpfac');
		for($i = 0;$i < $cana;$i++){
			$itcodigo= $do->get_rel('itpfac', 'codigoa', $i);
			$itcana  = $do->get_rel('itpfac', 'cana', $i);
			$mSQL = "UPDATE sinv SET exdes=exdes+$itcana WHERE codigo=".$this->db->escape($itcodigo);

			$ban=$this->db->simple_query($mSQL);
			if($ban==false){ memowrite($mSQL,'pfac'); }
		}
		$codigo = $do->get('numero');
		logusu('pfac', "Pedido $codigo MODIFICADO");
	}

	function cal_dxapli($preca=null,$dxapli=null){
		$p=null;
		if(!($preca && $dxapli)){
			$preca =$this->input->post('preca');
			$dxapli=$this->input->post('dxapli');
			$p=true;
		}

		$desc  =explode('+',$dxapli);
		$error='';

		$precio=$preca;
		foreach($desc as $value){
			if(strlen(trim($value))>0){

				if( $value>0)
				$precio=$precio-($precio*$value/100);
				else
				$error='_||_';
			}
		}

		if($p){
			if(empty($error) && 1*$precio>0)
				echo round($precio);
			else
				echo '_||_';
		}else{
			if(empty($error) && 1*$precio>0)
			return $precio;
		}

	}

	function _pre_delete($do){
		$codigo = $do->get('numero');
		$mSQL='UPDATE sinv JOIN itpfac ON sinv.codigo=itpfac.codigoa SET sinv.exdes=sinv.exdes-itpfac.cana WHERE itpfac.numa='.$this->db->escape($codigo);
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'pfac'); }
		return true;
	}

	function _post_delete($do){
		$codigo = $do->get('numero');
		logusu('pfac', "Pedido $codigo ELIMINADO");
	}

	function instalar(){
		if (!$this->db->field_exists('dxapli','itpfac'))
		$this->db->query("ALTER TABLE `itpfac`  ADD COLUMN `dxapli` VARCHAR(20) NOT NULL COMMENT 'descuento por aplicar'");
		$this->db->query("ALTER TABLE `itpfac`  CHANGE COLUMN `dxapli` `dxapli` VARCHAR(20) NULL COMMENT 'descuento por aplicar'");

	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'pfac');

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('pfac');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'numero', 'desc' );

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}
		$sql = $this->db->_compile_select($this->db->_count_string . $this->db->_protect_identifiers('numrows'));
		$results = $this->datasis->dameval($sql);
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$arr = $this->datasis->codificautf8($query->result_array());

		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function tabla() {
		$id   = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;
		$cliente = $this->datasis->dameval("SELECT cod_cli FROM pfac WHERE id='$id'");
		$mSQL = "SELECT cod_cli, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM smov WHERE cod_cli='$cliente' AND abonos<>monto AND tipo_doc<>'AB' ORDER BY fecha ";
		$query = $this->db->query($mSQL);
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Cuentas X Cobrar</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";

			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']-$row['abonos']).   "</td>";
				$salida .= "</tr>";
				if ( $row['tipo_doc'] == 'FC' or $row['tipo_doc'] == 'ND' or $row['tipo_doc'] == 'GI' )
					$saldo += $row['monto']-$row['abonos'];
				else
					$saldo -= $row['monto']-$row['abonos'];
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		$query->free_result();


/*
		// Revisa formas de pago sfpa
		$mSQL = "SELECT codbanc, numero, monto FROM bmov WHERE transac='$transac' ";
		$query = $this->db->query($mSQL);
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Caja o Banco</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Bco</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['codbanc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table>";
		}
*/
		echo $salida;
	}

	function griditpfac(){
		$numero   = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  0;
		if ($numero == 0 ) $numero = $this->datasis->dameval("SELECT MAX(numero) FROM pfac")  ;

		$mSQL = "SELECT * FROM itpfac a JOIN sinv b ON a.codigoa=b.codigo WHERE a.numa='$numero' ORDER BY a.codigoa";
		$query = $this->db->query($mSQL);
		$results =  0;
		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function sclibu(){
		$numero = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM pfac a JOIN scli b ON a.cod_cli=b.cliente WHERE numero='$numero'");
		redirect('ventas/scli/dataedit/show/'.$id);
	}

	function pfacextjs() {
		$encabeza='PEDIDO DE CLIENTES';

		$modulo = 'pfac';
		$urlajax = 'ventas/pfac/';

		$listados= $this->datasis->listados($modulo);
		$otros=$this->datasis->otros($modulo, $urlajax);


		$columnas = "
		{ header: 'Numero',      width: 60, sortable: true, dataIndex: 'numero' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fecha',       width: 70, sortable: true, dataIndex: 'fecha' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Vende',       width: 40, sortable: true, dataIndex: 'vd' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cliente',     width: 60, sortable: true, dataIndex: 'cod_cli' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'RIF/CI',      width: 90, sortable: true, dataIndex: 'rifci' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Nombre',      width:200, sortable: true, dataIndex: 'nombre' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Base',        width: 90, sortable: true, dataIndex: 'totals' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',         width: 90, sortable: true, dataIndex: 'iva' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Total',       width: 90, sortable: true, dataIndex: 'totalg' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Ref.',        width: 70, sortable: true, dataIndex: 'referen' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Status',      width: 60, sortable: true, dataIndex: 'status' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Observa',     width: 60, sortable: true, dataIndex: 'observa' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Observ1',     width: 60, sortable: true, dataIndex: 'observ1' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cajero',      width: 60, sortable: true, dataIndex: 'cajero' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Peso',        width: 60, sortable: true, dataIndex: 'peso' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Factura',     width: 60, sortable: true, dataIndex: 'factura' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Usuario',     width: 60, sortable: true, dataIndex: 'usuario' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Estampa',     width: 60, sortable: true, dataIndex: 'estampa' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Hora',        width: 60, sortable: true, dataIndex: 'hora' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Transac',     width: 60, sortable: true, dataIndex: 'transac' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Zona',        width: 60, sortable: true, dataIndex: 'zona' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Ciudad',      width: 60, sortable: true, dataIndex: 'ciudad' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Presup',      width: 60, sortable: true, dataIndex: 'presup' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Anticipo',    width: 60, sortable: true, dataIndex: 'anticipo' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Entregar',    width: 60, sortable: true, dataIndex: 'entregar' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Numant',      width: 60, sortable: true, dataIndex: 'numant' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Ftoma',       width: 60, sortable: true, dataIndex: 'ftoma' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Modificado',  width: 60, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Direccion 1', width: 60, sortable: true, dataIndex: 'direc' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Direccion 2', width: 60, sortable: true, dataIndex: 'dire1' , field: { type: 'textfield' }, filter: { type: 'string' }},
";

		$coldeta = "
	var Deta1Col = [
		{ header: 'Codigo',       width:100, sortable: true, dataIndex: 'codigoa' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Descripcion',  width:250, sortable: true, dataIndex: 'desca' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cantidad',     width: 70, sortable: true, dataIndex: 'cana' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Precio',       width: 80, sortable: true, dataIndex: 'preca' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Total',        width: 80, sortable: true, dataIndex: 'tota' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',          width: 60, sortable: true, dataIndex: 'iva' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Entregado',    width: 60, sortable: true, dataIndex: 'entregado' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'PVP',          width: 60, sortable: true, dataIndex: 'pvp' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Comision',     width: 60, sortable: true, dataIndex: 'comision' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'id',           width: 60, sortable: true, dataIndex: 'id' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'modificado',   width: 60, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'dxapli',       width: 60, sortable: true, dataIndex: 'dxapli' , field: { type: 'textfield' }, filter: { type: 'string' }}
	]";


		$variables='';

		$valida="		{ type: 'length', field: 'numero',  min:  1 }";


		$funciones = "
function renderScli(value, p, record) {
	var mreto='';
	if ( record.data.cod_cli == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlAjax+'sclibu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.numero );
}


function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}

	";

		$campos = $this->datasis->extjscampos($modulo);

		$stores = "
	Ext.define('It".$modulo."', {
		extend: 'Ext.data.Model',
		fields: [".$this->datasis->extjscampos("it".$modulo)."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlAjax + 'gridit".$modulo."',
				method: 'POST'
			},
			reader: {
				type: 'json',
				root: 'data',
				successProperty: 'success',
				messageProperty: 'message',
				totalProperty: 'results'
			}
		}
	});

	//////////////////////////////////////////////////////////
	// create the Data Store
	var storeIt".$modulo." = Ext.create('Ext.data.Store', {
		model: 'It".$modulo."',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	//////////////////////////////////////////////////////////
	//
	var gridDeta1 = Ext.create('Ext.grid.Panel', {
		width:   '100%',
		height:  '100%',
		store:   storeIt".$modulo.",
		title:   'Detalle de la NE',
		iconCls: 'icon-grid',
		frame:   true,
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		columns: Deta1Col
	});

	var ".$modulo."TplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR DESPACHO</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/verhtml/PRESUP/{numero}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/ver/PRESUP/{numero}\',     \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',   'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',
		'</table>','nanai'
	];

	// Al cambiar seleccion
	gridMaest.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridMaest.down('#delete').setDisabled(selectedRecord.length === 0);
			gridMaest.down('#update').setDisabled(selectedRecord.length === 0);
			numero = selectedRecord[0].data.numero;
			gridDeta1.setTitle(selectedRecord[0].data.numero+' '+selectedRecord[0].data.nombre);
			storeIt".$modulo.".load({ params: { numero: numero }});
			var meco1 = Ext.getCmp('imprimir');
			Ext.Ajax.request({
				url: urlAjax +'tabla',
				params: { numero: numero, id: selectedRecord[0].data.id },
				success: function(response) {
					var vaina = response.responseText;
					".$modulo."TplMarkup.pop();
					".$modulo."TplMarkup.push(vaina);
					var ".$modulo."Tpl = Ext.create('Ext.Template', ".$modulo."TplMarkup );
					meco1.setTitle('Imprimir Compra');
					".$modulo."Tpl.overwrite(meco1.body, selectedRecord[0].data );
				}
			});
		}
	});
";

		$acordioni = "{
					layout: 'fit',
					items:[
						{
							name: 'imprimir',
							id: 'imprimir',
							border:false,
							html: 'Para imprimir seleccione una Compra '
						}
					]
				},
";


		$dockedItems = "{
			xtype: 'toolbar',
			items: [
				{
					iconCls: 'icon-add',
					text: 'Agregar',
					scope: this,
					handler: function(){
						window.open(urlAjax+'dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},
				{
					iconCls: 'icon-update',
					text: 'Modificar',
					disabled: true,
					itemId: 'update',
					scope: this,
					handler: function(selModel, selections){
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						gridMaest.down('#delete').setDisabled(selections.length === 0);
						window.open(urlAjax+'dataedit/modify/'+selection.data.id, '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},{
					iconCls: 'icon-delete',
					text: 'Eliminar',
					disabled: true,
					itemId: 'delete',
					scope: this,
					handler: function() {
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						Ext.MessageBox.show({
							title: 'Confirme',
							msg: 'Seguro que quiere eliminar la compra Nro. '+selection.data.numero,
							buttons: Ext.MessageBox.YESNO,
							fn: function(btn){
								if (btn == 'yes') {
									if (selection) {
										//storeMaest.remove(selection);
									}
									storeMaest.load();
								}
							},
							icon: Ext.MessageBox.QUESTION
						});
					}
				}
			]
		}
		";

		$grid2 = ",{
				itemId: 'viewport-center-detail',
				activeTab: 0,
				region: 'south',
				height: '40%',
				split: true,
				margins: '0 0 0 0',
				preventHeader: true,
				items: gridDeta1
			}";


		$titulow = 'Compras';

		$filtros = "";
		$features = "
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		plugins: [Ext.create('Ext.grid.plugin.CellEditing', { clicksToEdit: 2 })],
";

		$final = "storeIt".$modulo.".load();";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['stores']      = $stores;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		$data['grid2']       = $grid2;
		$data['coldeta']     = $coldeta;
		$data['acordioni']   = $acordioni;
		$data['final']       = $final;

		$data['title']  = heading('Pedido de Clientes');
		$this->load->view('extjs/extjsvenmd',$data);

	}


}
