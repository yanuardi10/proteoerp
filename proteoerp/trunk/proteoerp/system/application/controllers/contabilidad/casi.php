<?php //require_once(BASEPATH.'application/controllers/validaciones.php');
class casi extends Controller {
	var $qformato;
	var $chrepetidos=array();

	function casi(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->database();
		if ( !$this->db->field_exists('id','casi')) {
			echo "cambio";
			$mSQL='ALTER TABLE `casi` DROP PRIMARY KEY, ADD UNIQUE `comprob` (`comprob`)';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE casi ADD id INT AUTO_INCREMENT PRIMARY KEY';
			$this->db->simple_query($mSQL);
		}
	}

	function index() {
		//redirect('contabilidad/casi/filteredgrid');
		if ( !$this->datasis->iscampo('itcasi','idcasi') ) {
			$mSQL='ALTER TABLE itcasi ADD idcasi INT(11) ';
			$this->db->simple_query($mSQL);
			$this->db->simple_query('ALTER TABLE itcasi ADD INDEX idcasi (idcasi)');
			$mSQL = "UPDATE itcasi a JOIN casi b ON a.comprob=b.comprob SET a.idcasi=b.id";
			$this->db->simple_query($mSQL);
		}
		$this->casiextjs();
	}

	function filteredgrid(){
		$this->rapyd->load('datagrid','datafilter');

		$filter = new DataFilter('Filtro de Asientos');
		$filter->db->select=array("comprob","fecha","descrip","origen","debe","haber","total");
		$filter->db->from('casi');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->comprob = new inputField("N&uacute;mero"     , "comprob");
		$filter->comprob->size=15;

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name="descrip"; 

		$filter->origen = new dropdownField("Or&iacute;gen", "origen");  
		$filter->origen->option("","Todos");
		$filter->origen->options("SELECT modulo, modulo valor FROM reglascont GROUP BY modulo");

		$filter->status = new dropdownField("Status", "status");  
		$filter->status->option("","Todos");
		$filter->status->option("A","Actualizado");
		$filter->status->option("D","Diferido");

		$filter->vdes = new checkboxField("Ver solo asientos descuadrados","vdes",'S','N');
		$filter->vdes->insertValue='N';
		$filter->vdes->clause='';

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$uri = anchor('contabilidad/casi/dataedit/show/<#comprob#>','<#comprob#>');

		$grid = new DataGrid();
		$vdes = $this->input->post('vdes');
		if($vdes) $grid->db->where('(debe-haber) <>',0);
		$grid->order_by('comprob','asc');
		$grid->per_page = 15;
		$grid->column_orderby('N&uacute;mero',$uri,'comprob');
		$grid->column_orderby('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>','fecha',"align='center'");
		$grid->column_orderby('Descripci&oacute;n','descrip','descrip');
		$grid->column_orderby('Or&iacute;gen'  ,'origen'  ,'origen',"align='center'");
		$grid->column_orderby('Debe'  ,'<nformat><#debe#></nformat>' ,'debe' ,"align='right'");
		$grid->column_orderby('Haber' ,'<nformat><#haber#></nformat>','haber',"align='right'");
		$grid->column_orderby('Total' ,'<nformat><#total#></nformat>','total',"align='right'");
		$grid->add('contabilidad/casi/dataedit/create');
		$grid->build();

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;

		//$data['extras'] = $extras;

		//$data["style"]   = style('jquery-ui-1.8.2.custom.css');
		$data['style']   = style('themes/redmond/jquery-ui-1.8.2.custom.css');
		$data['style']  .= style('themes/ui.jqgrid.css');
		$data['style']  .= style('themes/ui.multiselect.css');
		//$data["style"]  .= style('datagrid.css');

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery.layout.js');

		$data['script'] .= script('i18n/grid.locale-sp.js');
		$data['script'] .= script('jquery-ui-custom.min.js');
		$data['script'] .= script('ui.multiselect.js');

		$data['script'] .= script('jquery.jqGrid.min.js');
		$data['script'] .= script('jquery.tablednd.js');
		$data['script'] .= script('jquery.contextmenu.js');
		//$data["script"] .= script("datagrid.js");

		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Asientos');
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		$this->qformato=$qformato=$this->datasis->formato_cpla();
 		$modbus=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'cuenta_<#i#>',
 				'departa'=>'ccosto<#i#>',
				'descrip'=>'concepto_<#i#>',
				'departa'=>'cpladeparta_<#i#>',
				'ccosto' =>'cplaccosto_<#i#>'
 			),
			'titulo'  =>'Buscar Cuenta',
			'p_uri'=>array(4=>'<#i#>'),
			'where'=>"codigo LIKE \"$qformato\"",
			'script'=>array('post_modbus(<#i#>)')
			);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

 		$uri="/contabilidad/casi/dpto/";

		$do = new DataObject('casi');
		$do->rel_one_to_many('itcasi', 'itcasi', array('id'=>'idcasi'));
		$do->rel_pointer('itcasi','cpla','itcasi.cuenta=cpla.codigo','cpla.ccosto AS cplaccosto,cpla.departa AS cpladeparta');

		$edit = new DataDetails('Asientos', $do);
		//$edit->back_save=true;
		$edit->back_url = site_url('contabilidad/casi/dataedit/create');
		$edit->set_rel_title('itcasi','cuenta contables');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('delete','_post_delete');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->comprob = new inputField('N&uacute;mero', 'comprob');
		$edit->comprob->size     = 12;
		$edit->comprob->maxlength= 8;
		$edit->comprob->rule     ='required|unique';
		//$edit->comprob->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario

		$edit->descrip = new inputField('Descripci&oacute;n', 'descrip');
		$edit->descrip->size      = 40;
		$edit->descrip->maxlength = 60;

		$edit->status = new  dropdownField ('Status', 'status');
		$edit->status->option('A','Actualizado');
		$edit->status->option('D','Diferido');
		$edit->status->style='width:110px;';
		$edit->status->size = 5;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->cuenta = new inputField('Cuenta <#o#>', 'cuenta_<#i#>');
		$edit->cuenta->size     = 8;
		$edit->cuenta->db_name  = 'cuenta';
		$edit->cuenta->rel_id   = 'itcasi';
		$edit->cuenta->rule     = 'required|callback_chrepetidos';
		$edit->cuenta->append($btn);

		$edit->referen = new inputField('Referencia <#o#>', 'referen_<#i#>');
		$edit->referen->size      = 12;
		$edit->referen->db_name   = 'referen';
		$edit->referen->maxlength = 12;
		$edit->referen->rel_id    = 'itcasi';

		$edit->concepto = new inputField('Concepto <#o#>', 'concepto_<#i#>');
		$edit->concepto->size      = 24;
		$edit->concepto->db_name   = 'concepto';
		$edit->concepto->maxlength = 50;
		$edit->concepto->readonly  = true;
		$edit->concepto->rel_id    = 'itcasi';
		$edit->concepto->type      ='inputhidden';

		$edit->itdebe = new inputField('Debe <#o#>', 'itdebe_<#i#>');
		$edit->itdebe->db_name      = 'debe';
		$edit->itdebe->css_class    = 'inputnum';
		$edit->itdebe->rel_id       = 'itcasi';
		$edit->itdebe->maxlength    = 10;
		$edit->itdebe->size         = 5;
		$edit->itdebe->rule         = 'required|positive';
		$edit->itdebe->autocomplete = false;
		$edit->itdebe->onkeyup      = 'validaDebe(<#i#>)';

		$edit->ithaber = new inputField('Haber <#o#>', 'ithaber_<#i#>');
		$edit->ithaber->db_name      = 'haber';
		$edit->ithaber->css_class    = 'inputnum';
		$edit->ithaber->rel_id       = 'itcasi';
		$edit->ithaber->maxlength    = 10;
		$edit->ithaber->size         = 5;
		$edit->ithaber->rule         = 'required|positive';
		$edit->ithaber->autocomplete = false;
		$edit->ithaber->onkeyup      = 'validaHaber(<#i#>)';

		$edit->itccosto = new dropdownField('Centro de costo', 'itccosto_<#i#>');
		$edit->itccosto->option('','Ninguno');
		$edit->itccosto->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$edit->itccosto->db_name   = 'ccosto';
		$edit->itccosto->rel_id    = 'itcasi';
		$edit->itccosto->rule      = 'condi_required|callback_chdepaccosto[<#i#>]';
		$edit->itccosto->style     = 'width:110px;';

		$edit->itsucursal =  new dropdownField('Sucursal', 'itsucursal_<#i#>');
		$edit->itsucursal->option('','Ninguno');
		$edit->itsucursal->options("SELECT codigo,CONCAT(codigo,'-', sucursal) AS sucursal FROM sucu ORDER BY codigo");
		$edit->itsucursal->db_name   = 'sucursal';
		$edit->itsucursal->rel_id    = 'itcasi';
		$edit->itsucursal->rule      = 'condi_required|callback_chdepaccosto[<#i#>]';
		$edit->itsucursal->style     = 'width:100px';

		$edit->cplaccosto = new hiddenField('', 'cplaccosto_<#i#>');
		$edit->cplaccosto->db_name   = 'cplaccosto';
		$edit->cplaccosto->rel_id    = 'itcasi';
		$edit->cplaccosto->pointer   = true;

		$edit->cpladeparta = new hiddenField('', 'cpladeparta_<#i#>');
		$edit->cpladeparta->db_name   = 'cpladeparta';
		$edit->cpladeparta->rel_id    = 'itcasi';
		$edit->cpladeparta->pointer   = true;
		//**************************
		//fin de campos para detalle
		//**************************

		$edit->debe = new inputField('Debe', 'debe');
		$edit->debe->css_class ='inputnum';
		$edit->debe->readonly  =true;
		$edit->debe->size      = 10;
		$edit->debe->type ='inputhidden';

		$edit->haber = new inputField('Haber', 'haber');
		$edit->haber->css_class ='inputnum';
		$edit->haber->readonly  =true;
		$edit->haber->size      = 10;
		$edit->haber->type ='inputhidden';

		$edit->total = new inputField('Saldo', 'total');
		$edit->total->css_class ='inputnum';
		$edit->total->readonly  =true;
		$edit->total->size      = 10;
		$edit->total->type ='inputhidden';

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->origen = new autoUpdateField('origen'  ,'MANUAL','MANUAL');

		$edit->buttons('save', 'delete','modify', 'exit','add_rel','add');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_casi', $conten,true);
		$data['title']   = heading('Asientos Contables');
		$data['style']   = style('redmond/jquery-ui.css');
		$data['style']  .= style('gt_grid.css');
		$data['style']  .= style('impromptu.css');
		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script("jquery-impromptu.js");
		$data['script'] .= script("plugins/jquery.blockUI.js");
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= phpscript('nformat.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function grid1(){
		
		$page  = 1;//$this->input->post('page');
		$limit = 50;//$this->input->post('rows'); // get how many rows we want to have into the grid - rowNum parameter in the grid 
		$sidx  = 1; //$this->input->post('sidx'); // get index row - i.e. user click to sort. At first time sortname parameter -after that the index from colModel 
		$sord  = 'DESC';//$this->input->post('sord');
		$tabla = "casi";

		$this->db->from($tabla);

		if(!$sidx) $sidx =1;// if we not pass at first time index use the first column for the index or what you want

		$mSQL=$this->db->_compile_select($this->db->_count_string . $this->db->_protect_identifiers('numrows'));

		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$row = $query->row();
			$count= $row->numrows;
		}else{
			$count=0;
		}
 
		if( $count > 0 && $limit > 0) { 
			$total_pages = ceil($count/$limit); 
		} else {
			$total_pages = 0; 
		}

		if ($page > $total_pages) $page=$total_pages;
		$start = $limit*$page - $limit;
		if($start <0) $start = 0;
		$sidx = 'comprob';

		$this->load->helper('xml');
		header("Content-type: text/xml;charset=".$this->config->item('charset'));
		$s = "<?xml version='1.0' encoding='".$this->config->item('charset')."'?>";
		$s .=  "<rows>";
		$s .= "<page>".$page."</page>";
		$s .= "<total>".$total_pages."</total>";
		$s .= "<records>".$count."</records>";

		$this->db->orderby($sidx,$sord);
		$this->db->limit($limit,$start);
		$query = $this->db->get();
//echo $this->db->last_query();
		$campos = $this->db->field_data('casi');
//print_r($campos);
		foreach ($query->result() as $row){
			$s .= "<row id='". $row->id."'>";
			$s .= "<cell>".xml_convert($row->comprob)."</cell>";
			$s .= "<cell>".xml_convert($row->fecha)."</cell>";
			$s .= "<cell>".xml_convert($row->descrip)."</cell>";
			$s .= "<cell>".xml_convert($row->debe)."</cell>";
			$s .= "<cell>".xml_convert($row->haber)."</cell>";
			$s .= "<cell>".xml_convert($row->total)."</cell>";
/*
			foreach($campos AS $campo){
				$a = $campo->name;
				$s .= "<cell>".xml_convert($row->$a)."</cell>";
			}
*/
			$s .= "</row>";
		}
		$s .= "</rows>"; 
		echo $s;
	}



	function chrepetidos($cod){
		if(array_search($cod, $this->chrepetidos)===false){
			$this->chrepetidos[]=$cod;
			return true;
		}else{
			$this->validation->set_message('chrepetidos', 'La cuenta '.$cod.' esta repetido');
			return false;
		}
	}

	function chdepaccosto($val,$ind){;
		$codigo   = $this->input->post('cuenta_'.$ind);
		$dbcodigo = $this->db->escape($codigo);
		$departa  = $this->datasis->dameval('SELECT departa FROM cpla WHERE codigo='.$dbcodigo);
		if($departa=='S' && empty($val)){
			$this->validation->set_message('chdepaccosto', 'El campo %s es requerido para la cuenta contable '.$codigo);
			return false;
		}
		return true;
	}

	function auditoria(){

		$data['content'] = anchor('contabilidad/casi/auditcasi','Auditoria en Asientos'   ).br();
		$data['content'].= anchor('contabilidad/casi/auditsprv','Auditoria en Proveedores').br();
		$data['content'].= anchor('contabilidad/casi/auditscli','Auditoria en Clientes'   ).br();
		$data['content'].= anchor('contabilidad/casi/auditbotr','Auditoria en Conceptos'  ).br();
		$data['head']    = '';
		$data['title']   = heading('Auditorita de Contable');
		$this->load->view('view_ventanas', $data);
	}

	function auditcasi(){
		$this->rapyd->load('datagrid','datafilter');

		$filter = new DataFilter('Auditoria de Asientos');
		$filter->db->select(array('a.comprob','a.fecha','a.concepto','a.origen','a.debe','a.haber','a.cuenta'));
		$filter->db->from('itcasi AS a');
		$filter->db->join('cpla AS b' ,'a.cuenta=b.codigo','LEFT');
		$filter->db->where('b.codigo IS NULL');
		//$filter->db->where('cuenta NOT REGEXP \'^([0-9]+\.)+[0-9]+\' OR cuenta IS NULL');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechah->size=$filter->fechad->size=12;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->comprob = new inputField('N&uacute;mero'     , 'comprob');
		$filter->comprob->size=15;

		$filter->origen = new dropdownField('Or&iacute;gen', 'origen');
		$filter->origen->option('','Todos');
		$filter->origen->options('SELECT modulo, modulo valor FROM reglascont GROUP BY modulo');

		$filter->buttons('reset','search');
		$filter->build();

		function regla($origen){
			if(preg_match('/(?P<regla>[A-Za-z]+)(?P<numero>\d+)/', $origen, $match)>0){
				$regla  =$match['regla'];
				$numero =$match['numero'];

				$atts = array(
					'width'      => '800',
					'height'     => '600',
					'scrollbars' => 'yes',
					'status'     => 'yes',
					'resizable'  => 'yes',
					'screenx'    => '0',
					'screeny'    => '0'
				);

				$rt = anchor_popup('contabilidad/reglas/dataedit/'.$regla.'/show/'.$regla.'/'.$numero,$origen,$atts);
			}else{
				$rt=$origen;
			}
			return $rt;
		}

		$grid = new DataGrid();
		$grid->use_function('regla');
		$grid->order_by('fecha','asc');
		$grid->per_page = 40;
		$grid->column_orderby('N&uacute;mero','comprob','comprob');
		$grid->column_orderby('Cuenta','cuenta','cuenta');
		$grid->column_orderby('Fecha'   ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha',"align='center'");
		$grid->column_orderby('Concepto','concepto','concepto');
		$grid->column_orderby('Or&iacute;gen','<regla><#origen#></regla>','origen',"align='center'");
		$grid->column_orderby('Debe'    ,'<nformat><#debe#></nformat>'   ,'debe'  ,"align='right'" );
		$grid->column_orderby('Haber'   ,'<nformat><#haber#></nformat>'  ,'haber' ,"align='right'" );
		$action = "javascript:window.location='".site_url('contabilidad/casi/auditoria')."'";
		$grid->button('btn_regresa', 'Regresar', $action, 'TR');
		$grid->build();

		$data['content'] =$filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   =heading('Auditorita de Asientos');
		$this->load->view('view_ventanas', $data);
	}

	function auditscli(){
		$this->rapyd->load('datagrid','dataform');

		$qformato=$this->datasis->formato_cpla();
		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
			        'codigo' =>'C&oacute;digo',
			        'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
		);
		$bcpla =$this->datasis->modbus($mCPLA);

		$grid = new DataGrid();
		$grid->db->select(array('a.cliente','a.rifci','a.nombre','a.cuenta'));
		$grid->db->from('scli AS a');
		$grid->db->join('cpla AS b','a.cuenta=b.codigo','LEFT');
		$grid->db->where('b.codigo IS NULL');
		$grid->per_page = 40;
		$grid->column_orderby('C&oacute;digo','cliente','cliente');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Rif/CI','rifci' ,'rifci');
		$grid->column_orderby('Cuenta','cuenta','cuenta');
		$action = "javascript:window.location='".site_url('contabilidad/casi/auditoria')."'";
		$grid->button('btn_regresa', 'Regresar', $action, 'TR');
		$grid->build();

		$form = new DataForm('contabilidad/casi/auditscli/process');
		$form->cuenta = new inputField('Cuenta', 'cuenta');
		$form->cuenta->rule = 'trim|required|callback_chcuentac';
		$form->cuenta->size =15;
                $form->cuenta->append($bcpla);

		$form->submit('btnsubmit','Cambiar');
		$form->build_form();

		if ($form->on_success()){
			$cuenta= $this->db->escape($form->cuenta->newValue);
			$mSQL='UPDATE scli AS a LEFT JOIN cpla AS b ON a.cuenta=b.codigo SET a.cuenta='.$cuenta.' WHERE b.codigo IS NULL';
			$this->db->simple_query($mSQL);
			redirect('contabilidad/casi/auditscli');
		}

		$data['content'] = ($grid->recordCount > 0) ? $form->output : '';
		$data['content'].= $grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Auditorita de cuentas en clientes');
		$this->load->view('view_ventanas', $data);
	}

	function auditbotr(){
		$this->rapyd->load('datagrid','dataform');

		$qformato=$this->datasis->formato_cpla();
		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
			        'codigo' =>'C&oacute;digo',
			        'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
		);
		$bcpla =$this->datasis->modbus($mCPLA);

		$grid = new DataGrid();
		$grid->db->select(array('a.codigo','a.nombre','a.cuenta'));
		$grid->db->from('botr AS a');
		$grid->db->join('cpla AS b','a.cuenta=b.codigo','LEFT');
		$grid->db->where('b.codigo IS NULL');
		$grid->per_page = 40;
		$grid->column_orderby('C&oacute;digo','codigo','codigo');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Cuenta','cuenta','cuenta');
		$action = "javascript:window.location='".site_url('contabilidad/casi/auditoria')."'";
		$grid->button('btn_regresa', 'Regresar', $action, 'TR');
		$grid->build();

		$form = new DataForm('contabilidad/casi/auditbotr/process');
		$form->cuenta = new inputField('Cuenta', 'cuenta');
		$form->cuenta->rule = 'trim|required|callback_chcuentac';
		$form->cuenta->size =15;
		$form->cuenta->append($bcpla);

		$form->submit('btnsubmit','Cambiar');
		$form->build_form();

		if ($form->on_success()){
			$cuenta= $this->db->escape($form->cuenta->newValue);
			$mSQL='UPDATE botr AS a LEFT JOIN cpla AS b ON a.cuenta=b.codigo SET a.cuenta='.$cuenta.' WHERE b.codigo IS NULL';
			$this->db->simple_query($mSQL);
			redirect('contabilidad/casi/auditscli');
		}

		$data['content'] = ($grid->recordCount > 0) ? $form->output : '';
		$data['content'].= $grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Auditorita de cuentas en clientes');
		$this->load->view('view_ventanas', $data);
	}

	function auditsprv(){
		$this->rapyd->load('datagrid','dataform');

		$qformato=$this->datasis->formato_cpla();
		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
			        'codigo' =>'C&oacute;digo',
			        'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
		);
		$bcpla =$this->datasis->modbus($mCPLA);

		$grid = new DataGrid();
		$grid->db->select(array('a.proveed','a.rif','a.nombre','a.cuenta'));
		$grid->db->from('sprv AS a');
		$grid->db->join('cpla AS b','a.cuenta=b.codigo','LEFT');
		$grid->db->where('b.codigo IS NULL');
		$grid->per_page = 40;
		$grid->column_orderby('C&oacute;digo','proveed','proveed');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Rif'   ,'rif'   ,'rif');
		$grid->column_orderby('Cuenta','cuenta','cuenta');
		$action = "javascript:window.location='".site_url('contabilidad/casi/auditoria')."'";
		$grid->button('btn_regresa', 'Regresar', $action, 'TR');
		$grid->build();

		$form = new DataForm('contabilidad/casi/auditsprv/process');
		$form->cuenta = new inputField('Cuenta', 'cuenta');
		$form->cuenta->rule = 'trim|required|callback_chcuentac';
		$form->cuenta->size =15;
		$form->cuenta->append($bcpla);

		$form->submit('btnsubmit','Cambiar');
		$form->build_form();

		if ($form->on_success()){
			$cuenta= $this->db->escape($form->cuenta->newValue);
			$mSQL='UPDATE sprv AS a LEFT JOIN cpla AS b ON a.cuenta=b.codigo SET a.cuenta='.$cuenta.' WHERE b.codigo IS NULL';
			$this->db->simple_query($mSQL);
			redirect('contabilidad/casi/auditsprv');
		}

		$data['content'] = ($grid->recordCount > 0) ? $form->output : '';
		$data['content'].= $grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Auditorita de cuentas en proveedores');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		$cana=$do->count_rel('itcasi');
		$comprob=$do->get('comprob');
		$fecha  =$do->get('fecha');
		$monto=$debe=$haber=0;

		for($i=0;$i<$cana;$i++){ $o=$i+1;
			$adebe =$do->get_rel('itcasi','debe',$i);
			$ahaber=$do->get_rel('itcasi','haber' ,$i);
			$do->set_rel('itcasi','comprob',$comprob,$i);
			$do->set_rel('itcasi','fecha'  ,$fecha  ,$i);
			
			if ($adebe!=0 && $ahaber!=0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='No puede tener debe y haber en el asiento '.$o;
				return false;	
			}
			if ($adebe==0 && $ahaber==0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe tener debe o haber en el asiento '.$o;
				return false;	
			}
			if($adebe != 0){
				$debe+=$adebe;
			}
			if($ahaber != 0){
				$haber+=$ahaber;
			}
		}
		if ($debe == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos un monto en la columna de debe.';
			return false;	
		}
		if ($haber == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos un monto en la columna de haber.';
			return false;	
		}
		if($debe-$haber != 0){ $do->set('status' ,'D'); }

		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date('H:i:s');

		$do->set('debe' ,$debe);
		$do->set('haber',$haber);
		$do->set('total',$debe-$haber);
		$do->set('estampa',$estampa);
		$do->set('hora'   ,$hora);
		$do->set('transac',$transac);

		return true;
	}

	function _pre_update($do){
		$cana=$do->count_rel('itcasi');
		$monto=$debe=$haber=0;

		for($i=0;$i<$cana;$i++){ $o=$i+1;
			$adebe=$do->get_rel('itcasi','debe',$i);
			$ahaber=$do->get_rel('itcasi','haber' ,$i);
			if ($adebe!=0 && $ahaber!=0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='No puede tener debe y haber en el asiento '.$o;
				return false;	
			}
			if ($adebe==0 && $ahaber==0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe tener debe o haber en el asiento '.$o;
				return false;	
			}
			if($adebe != 0){
				$debe+=$adebe;
			}
			if($ahaber != 0){
				$haber+=$ahaber;
			}
		}
		if ($debe == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos un monto en la columna de debe.';
			return false;	
		}
		if ($haber == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos un monto en la columna de haber.';
			return false;	
		}
		if($debe-$haber != 0){ $do->set('status' ,'D'); }

		$do->set('debe' ,$debe);
		$do->set('haber',$haber);
		$do->set('total',$debe-$haber);

		return true;
	}

	function _post_update($do){
		//trafrac ittrafrac
		$codigo=$do->get('comprob');
		logusu('casi',"Asiento $codigo MODIFICADO");
	}

	function _post_insert($do){
		//trafrac ittrafrac
		$codigo=$do->get('comprob');
		logusu('casi',"Asiento $codigo CREADO");
	}

	function _post_delete($do){
		$codigo=$do->get('comprob');
		logusu('casi',"Asiento $codigo ELIMINADO");
	}

	function getData()
	{
		memowrite("datajqgridget","datajqgrid");

		$this->load->library('datajqgrid');
		$grid             = $this->datajqgrid;
		$response         = $grid->getData('casi', array(array('table' => 'casi')),array(),false);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}


	 #Put information

	function setData()
	{
	    $this->load->library('datajqgrid');
	    $grid             = $this->datajqgrid;
	    $response         = $grid->operations('casi','id');
	}
	
	// Postea la tabla principal a Extjs
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 30;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"fecha","direction":"DESC"},{"property":"comprob","direction":"DESC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'casi');

		$this->db->_protect_identifiers=false;
		
		$this->db->select('*');
		$this->db->from('casi');
		if (strlen($where)>1){
			$this->db->where($where);
		}

		$sql = $this->db->_compile_select($this->db->_count_string . $this->db->_protect_identifiers('numrows'));
		$results = $this->datasis->dameval($sql);
		
		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}



		$this->db->limit($limit, $start);
		$query = $this->db->get();



		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function griditcasi(){
		$comprob   = isset($_REQUEST['comprob'])  ? $_REQUEST['comprob']   :  '';
		if ($comprob == '' ) $comprob = $this->datasis->dameval("SELECT MAX(comprob) FROM casi") ;

		$mSQL = "SELECT * FROM itcasi a WHERE a.comprob='$comprob' ORDER BY a.cuenta";
		$query = $this->db->query($mSQL);
		$results =  0; 
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function sprvbu(){
		$control = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM casi a JOIN sprv b ON a.proveed=b.proveed WHERE control='$control'");
		redirect('compras/sprv/dataedit/show/'.$id);
	}

	function tabla() {
		$comprob   = isset($_REQUEST['control'])  ? $_REQUEST['control']   :  0;
		$transac = $this->datasis->dameval("SELECT transac FROM casi WHERE control='$control'");
		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM sprm WHERE transac='$transac' ORDER BY cod_prv ";
		$query = $this->db->query($mSQL);
		$codprv = 'XXXXXXXXXXXXXXXX';
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			
			foreach ($query->result_array() as $row)
			{
				if ( $codprv != $row['cod_prv']){
					$codprv = $row['cod_prv'];
					$salida .= "<tr bgcolor='#c7d3c7'>";
					$salida .= "<td colspan=4>".trim($row['nombre']). "</td>";
					$salida .= "</tr>";	
				}
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		echo $salida;
	}

	function casiextjs() {
		$encabeza='ASIENTOS CONTABLES';
		$listados= $this->datasis->listados('casi');
		$otros=$this->datasis->otros('casi', 'contabilidad/casi');

		$urlajax = 'contabilidad/casi/';

		$columnas = "
		{ header: 'Comprobante',  width: 80, sortable: true, dataIndex: 'comprob' , field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Fecha',        width: 70, sortable: true, dataIndex: 'fecha' ,   field: { type: 'date'       }, filter: { type: 'date'   }},
		{ header: 'Descripcion',  width:250, sortable: true, dataIndex: 'descrip' , field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Debe',         width: 80, sortable: true, dataIndex: 'debe' ,    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Haber',        width: 80, sortable: true, dataIndex: 'haber' ,   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Total',        width: 60, sortable: true, dataIndex: 'total' ,   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Status',       width: 40, sortable: true, dataIndex: 'status' ,  field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Tipo',         width: 60, sortable: true, dataIndex: 'tipo' ,    field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Origen',       width: 60, sortable: true, dataIndex: 'origen' ,  field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Transac',      width: 60, sortable: true, dataIndex: 'transac' , field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Usuario',      width: 60, sortable: true, dataIndex: 'usuario' , field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'Estampa',      width: 60, sortable: true, dataIndex: 'estampa' , field: { type: 'date'       }, filter: { type: 'date'   }},
		{ header: 'Hora',         width: 60, sortable: true, dataIndex: 'hora' ,    field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'id',           width: 80, sortable: true, dataIndex: 'id' ,      field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0000')},
		";

		$coldeta = "
	var Deta1Col = [
		{ header: 'cuenta',   width: 90, sortable: true, dataIndex: 'cuenta',  field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'referen',  width: 90, sortable: true, dataIndex: 'referen', field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'concepto', width:190, sortable: true, dataIndex: 'concepto',field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'debe',     width: 80, sortable: true, dataIndex: 'debe',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'haber',    width: 80, sortable: true, dataIndex: 'haber',   field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'origen',   width: 60, sortable: true, dataIndex: 'origen',  field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'ccosto',   width: 60, sortable: true, dataIndex: 'ccosto',  field: { type: 'textfield'  }, filter: { type: 'string' }},
		{ header: 'sucursal', width: 60, sortable: true, dataIndex: 'sucursal',field: { type: 'textfield'  }, filter: { type: 'string' }},
	]";

		$variables='';
		
		$valida="		{ type: 'length', field: 'cliente',  min:  1 }";
		

		$funciones = "
function renderSprv(value, p, record) {
	var mreto='';
	if ( record.data.proveed == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'compras/casi/sprvbu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.control );
}

function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}
	";

		$campos = $this->datasis->extjscampos('casi');

		$stores = "
	Ext.define('Itcasi', {
		extend: 'Ext.data.Model',
		fields: [".$this->datasis->extjscampos('itcasi')."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlAjax + 'griditcasi',
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
	var storeItCasi = Ext.create('Ext.data.Store', {
		model: 'Itcasi',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});
	
	//////////////////////////////////////////////////////////
	//
	var gridDeta1 = Ext.create('Ext.grid.Panel', {
		width:   '100%',
		height:  '100%',
		store:   storeItCasi,
		title:   'Articulos',
		iconCls: 'icon-grid',
		frame:   true,
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		columns: Deta1Col
	});

	var casiTplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR ASIENTO</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/verhtml/COMPRA/{comprob}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{comprob}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/ver/COMPRA/{comprob}\',     \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',   'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',		
		'</table>'
	];

	// Al cambiar seleccion
	gridMaest.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridMaest.down('#delete').setDisabled(selectedRecord.length === 0);
			gridMaest.down('#update').setDisabled(selectedRecord.length === 0);
			comprob = selectedRecord[0].data.comprob;
			gridDeta1.setTitle(comprob+' '+selectedRecord[0].data.descrip);
			storeItCasi.load({ params: { comprob: comprob }});
			var meco1 = Ext.getCmp('imprimir');
			
			var casiTpl = Ext.create('Ext.Template', casiTplMarkup );
			meco1.setTitle('Imprimir Asiento');
			casiTpl.overwrite(meco1.body, selectedRecord[0].data );

			/*Ext.Ajax.request({
				url: urlAjax +'tabla',
				params: { comprob: selectedRecord[0].data.comprob, id: selectedRecord[0].data.id },
				success: function(response) {
					var vaina = response.responseText;
					casiTplMarkup.pop();
					casiTplMarkup.push(vaina);
					var casiTpl = Ext.create('Ext.Template', casiTplMarkup );
					meco1.setTitle('Imprimir Asiento');
					casiTpl.overwrite(meco1.body, selectedRecord[0].data );
				}
			});*/
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
						window.open(urlApp+'contabilidad/casi/dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},{
					iconCls: 'icon-update',
					text: 'Modificar',
					disabled: true,
					itemId: 'update',
					scope: this,
					handler: function(selModel, selections){
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						gridMaest.down('#delete').setDisabled(selections.length === 0);
						window.open(urlApp+'contabilidad/casi/dataedit/modify/'+selection.data.id, '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
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


		$titulow = 'Asientos';
		
		$filtros = "";
		$features = "
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		plugins: [Ext.create('Ext.grid.plugin.CellEditing', { clicksToEdit: 2 })],
";

		$final = "storeItCasi.load();";

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
		
		$data['title']  = heading('Asientos');
		$this->load->view('extjs/extjsvenmd',$data);

	}
/*
diles que te envien nun taxi al 5140967
que tu eres el papa de andrea la hija de cira y que la lleven a la direccion
cira elena rodriguez nava: unidad vecinal casa 12 detras del hospital

*/

}