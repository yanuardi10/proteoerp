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
		redirect('contabilidad/casi/filteredgrid');
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
		$do->rel_one_to_many('itcasi', 'itcasi', 'comprob');
		$do->rel_pointer('itcasi','cpla','itcasi.cuenta=cpla.codigo','cpla.ccosto AS cplaccosto,cpla.departa AS cpladeparta');

		$edit = new DataDetails('Asientos', $do);
		$edit->back_url = site_url('contabilidad/casi/filteredgrid');
		$edit->set_rel_title('itcasi','cuenta contables');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->comprob = new inputField('N&uacute;mero', 'comprob');
		$edit->comprob->size     = 12;
		$edit->comprob->maxlength= 8;
		$edit->comprob->rule     ='required';
		$edit->comprob->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->comprob->when=array('show','modify');
		$edit->comprob->mode='autohide';

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
		$edit->cuenta->readonly = true;
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

		$edit->haber = new inputField('Haber', 'haber');
		$edit->haber->css_class ='inputnum';
		$edit->haber->readonly  =true;
		$edit->haber->size      = 10;

		$edit->total = new inputField('Saldo', 'total');
		$edit->total->css_class ='inputnum';
		$edit->total->readonly  =true;
		$edit->total->size      = 10;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->origen = new autoUpdateField('origen'  ,'MANUAL','MANUAL');

		$edit->buttons('save', 'undo', 'delete','modify', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_casi', $conten,true);
		$data['title']   = heading('Asientos Contables');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function grid(){
		
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
			$this->validation->set_message('chrepetidos', 'El art&iacute;culo '.$cod.' esta repetido');
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
		$monto=$debe=$haber=0;
		//Hasta aca en costo trae el valor del ultimo de sinv, se opera para cambiarlo a:
		//costo=costo*(entrada o salida segun se el caso)
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

		$comprob=$this->datasis->fprox_numero('ncasi');
		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date('H:i:s');

		$do->set('debe' ,$debe);
		$do->set('haber',$haber);
		$do->set('total',$debe-$haber);
		$do->set('comprob',$comprob);
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


}