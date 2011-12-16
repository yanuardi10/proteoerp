<?php require_once(BASEPATH . 'application/controllers/validaciones.php');
class pfaclite extends validaciones{
	var $genesal=true;

	function pfaclite(){
		parent :: Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(120,1);
	}

	function index(){
		redirect('ventas/pfaclite/filteredgrid');
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

		$uri = anchor('ventas/pfaclite/dataedit/show/<#id#>', '<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/PFAC/<#numero#>', 'Ver HTML', $atts);
		$uri3 = anchor_popup('ventas/sfac/creadpfacf/<#numero#>', 'Facturar', $atts2);

		$grid = new DataGrid('');
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

		$grid->add('ventas/pfaclite/crea');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['filtro']  = '';

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Pedidos Clientes');
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject', 'datadetails');
		$this->load->helper('form');

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
		$do->rel_pointer('itpfac', 'sinv', 'itpfac.codigoa=sinv.codigo', 'sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo,sinv.precio1 As sinvprecio1,sinv.pond AS sinvpond,sinv.mmargen as sinvmmargen,sinv.ultimo sinvultimo,sinv.formcal sinvformcal,sinv.pm sinvpm,sinv.existen pexisten,sinv.marca pmarca');
		$do->order_by('itpfac','sinv.marca',' ');
		$do->order_by('itpfac','sinv.descrip',' ');
		

		$edit = new DataDetails('Pedidos', $do);
		$edit->back_url = site_url('ventas/pfaclite/filteredgrid');
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

		$edit->fecha = new inputField('Fecha', 'fecha');
		$edit->fecha->insertValue = date('Y-m-d');
		//$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$usr=$this->session->userdata('usuario');
		$vd=$this->datasis->dameval("SELECT vendedor FROM usuario WHERE us_codigo='$usr'");
		$edit->vd = new hiddenField ('Vendedor', 'vd');
		$edit->vd->value = $vd;

		$edit->mmargen = new inputField('mmargen', 'mmargen');

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode = 'autohide';
		$edit->numero->maxlength = 8;
		$edit->numero->apply_rules = false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when = array('show', 'modify');

		$edit->cliente = new dropdownField('CLIENTE', 'cod_cli');
		$edit->cliente->options("SELECT cliente, nombre FROM scli WHERE vendedor='$vd'");
		
		$edit->rifci = new inputField('RIF/CI', 'rifci');
		$edit->rifci->autocomplete = false;
		$edit->rifci->size = 15;
		$edit->rifci->type ='inputhidden';

		$edit->direc = new inputField('Direcci&oacute;n', 'direc');
		$edit->direc->size = 40;
		$edit->direc->type ='inputhidden';

		$edit->observa = new inputField('Observaciones', 'observa');
		$edit->observa->size = 25;

		$edit->observ1 = new inputField('Observaciones', 'observ1');
		$edit->observ1->size = 25;

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
		$edit->codigoa->rule = 'callback_chcodigoa';
		//$edit->codigoa->onkeyup = 'OnEnter(event,<#i#>)';
		$edit->codigoa->type='inputhidden';

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size = 32;
		$edit->desca->db_name = 'desca';
		$edit->desca->maxlength = 50;
		$edit->desca->readonly = true;
		$edit->desca->rel_id = 'itpfac';
		$edit->desca->type='inputhidden';
		
		$edit->pexisten = new inputField('Existencia <#o#>', 'pexisten_<#i#>');
		$edit->pexisten->size    = 10;
		$edit->pexisten->db_name = 'pexisten';
		$edit->pexisten->rel_id  = 'itpfac';
		$edit->pexisten->pointer =true;
		$edit->pexisten->type='inputhidden';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name = 'cana';
		$edit->cana->css_class = 'inputnum';
		$edit->cana->rel_id = 'itpfac';
		$edit->cana->maxlength = 10;
		$edit->cana->size = 3;
		//$edit->cana->rule = 'required|positive';
		$edit->cana->autocomplete = false;
		//$edit->cana->onkeyup = 'importe(<#i#>)';
		//$edit->cana->insertValue=1;
		$edit->cana->style ="height:30px;font-size:16";

		$edit->preca = new dropdownField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'itpfac';
		$edit->preca->size      = 10;
		$edit->preca->rule      = 'positive|callback_chpreca[<#i#>]';
		$edit->cana->style      ="height:30px;font-size:16";
//		$edit->preca->readonly = true;
		
		for($i = 1;$i <= 4;$i++){
			$obj = 'precio' . $i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj . '_<#i#>');
			$edit->$obj->db_name = 'sinv' . $obj;
			$edit->$obj->rel_id = 'itpfac';
			$edit->$obj->pointer = true;
		}
		
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
		
		$edit->pmarca = new inputField('', 'pmarca_<#i#>');
		$edit->pmarca->db_name = 'pmarca';
		$edit->pmarca->rel_id  = 'itpfac';
		$edit->pmarca->pointer = true;
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

			$accion="javascript:window.location='".site_url('ventas/pfaclite/dataedit/modify/'.$control)."'";
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
			$data['content'] = $this->load->view('view_pfaclite', $conten, true);
			$data['title']   = heading('Pedidos No. '.$edit->numero->value);

			//$data['head']   = script('jquery.js');
			//$data['head']  .= script('jquery-ui.js');
			//$data['head']  .= script('plugins/jquery.numeric.pack.js');
			//$data['head']  .= script('plugins/jquery.floatnumber.js');
			//$data['head']  .= phpscript('nformat.js');
			$data['head']   = $this->rapyd->get_head();

			$this->load->view('view_ventanas_sola', $data);
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
	
	function crea(){
		$npfactemp=$this->datasis->fprox_numero('npfactemp');
		$npfactemp=substr($npfactemp,1);
		$query="INSERT INTO itpfac(`numa`,`codigoa`,`desca`,`cana`,`preca`,`tota`,`iva`)
		SELECT '_".$npfactemp."',codigo,descrip            ,0     ,precio1,0     ,iva FROM sinv WHERE activo='S'";
		$this->db->query($query);
		$query="INSERT INTO pfac(`numero`,`fecha`) VALUES('_".$npfactemp."',CURDATE())";
		$this->db->query($query);
		$id=$this->db->insert_id();
		redirect('ventas/pfaclite/dataedit/modify/'.$id);
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
		$vd = $do->get('vd');

		$iva = $totals = 0;
		$cana = $do->count_rel('itpfac');
		for($i = 0;$i < $cana;$i++){
			$itcana  = $do->get_rel('itpfac', 'cana', $i);
			$itpreca = $do->get_rel('itpfac', 'preca', $i);
			$itiva   = $do->get_rel('itpfac', 'iva', $i);
			$ittota  = $itpreca * $itcana;
			$do->set_rel('itpfac', 'tota' , $ittota, $i);
			$do->set_rel('itpfac', 'fecha' , $fecha , $i);
			$do->set_rel('itpfac', 'vendedor', $vd , $i);

			$iva    += $ittota * ($itiva / 100);
			$totals += $ittota;
			$do->set_rel('itpfac', 'mostrado', $iva + $ittota, $i);
		}
		$totalg = $totals + $iva;

		$do->set('totals' , round($totals , 2));
		$do->set('totalg' , round($totalg , 2));
		$do->set('iva'    , round($iva    , 2));
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

		$mSQL='UPDATE sinv JOIN itpfac ON sinv.codigo=itpfac.codigoa SET sinv.exdes=sinv.exdes-itpfac.cana WHERE itpfac.numa='.$this->db->escape($codigo);
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

	function enviar($id){
		$ide=$this->db->escape($id);
		$this->db->query("UPDATE pfac SET fenvia=CURDATE() WHERE id=$ide");
		redirect("ventas/pfaclite/dataedit/show/$id");
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
}
