<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class spre extends validaciones {

	function spre(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(104,1);
	}

	function index() {
		//redirect('ventas/spre/filteredgrid');
		redirect("ventas/spre/extgrid");
	}

	function extgrid(){
		$this->datasis->modulo_id(707,1);
		$script = $this->spreextjs();
		$data["script"] = $script;
		$data['title']  = heading('Presupuestos');
		$this->load->view('extjs/pers',$data);
	}

/*
	function filteredgrid(){
		$this->rapyd->load('datagrid','datafilter');

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
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');
		$boton=$this->datasis->modbus($scli);

		$filter = new DataFilter('Filtro de Presupuestos','spre');
		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechad->insertValue = date('Y-m-d');
		$filter->fechah->insertValue = date('Y-m-d');
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 30;

		$filter->cliente = new inputField('Cliente', 'cod_cli');
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons('reset','search');
		$filter->build("dataformfiltro");

		$uri  = anchor('ventas/spre/dataedit/<#numero#>','<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/PRESUP/<#numero#>','Ver HTML',$atts);

		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."ventas/spre/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."reportes/index/spre', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600" width="900" '.'>';
		$mtool .= img(array('src' => 'images/reportes.gif', 'alt' => 'Reportes', 'title' => 'Reportes','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";

		$grid = new DataGrid();
		$grid->order_by('id','desc');
		$grid->per_page = 50;

		$grid->column_sigma('Accion',       'accion',  '',      'width: 50, frozen: true, renderer:imprimir');
		$grid->column_sigma('N&uacute;mero','numero',  '',      'width: 60, frozen: true, renderer:sprever');
		$grid->column_sigma('Fecha',        'fecha',   'date',  'width: 70');
		$grid->column_sigma('Codigo',       'cod_cli', '',      'width: 50');
		$grid->column_sigma('Nombre',       'nombre',  '',      'width: 300');
		$grid->column_sigma('Sub.Total',    'totals',  'float', "width: 80, align: 'right'");
		$grid->column_sigma('IVA',          'iva'   ,  'float', "width: 80, align: 'right'");
		$grid->column_sigma('Total',        'totalg',  'float', "width: 80, align: 'right'");
		$grid->column_sigma('Vendedor',     'vd',      '',      'width: 50');
		$grid->column_sigma('Id',           'id',      'float', "width: 80, align: 'right'");

		$sigmaA     = $grid->sigmaDsConfig();
		$dsOption   = $sigmaA["dsOption"];

		$sprever    = "
function sprever(value, record, columnObj, grid, colNo, rowNo){
	var url = '';
	url = '<a href=\"#\" onclick=\"window.open(\'".base_url()."ventas/spre/dataedit/show/'+grid.getCellValue(9,rowNo)+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
	url = url +value+'</a>';
	return url;	
}

function imprimir(value, record, columnObj, grid, colNo, rowNo){
	var url = '';
	url = '&nbsp;<a href=\"#\" onclick=\"window.open(\'".base_url()."formatos/verhtml/PRESUP/'+grid.getCellValue(1,rowNo)+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
	url = url +'<img src=\'".base_url()."images/html_icon.gif\'/>'+'</a>';
	url = url+'&nbsp;<a href=\"#\" onclick=\"window.open(\'".base_url()."formatos/ver/PRESUP/'+grid.getCellValue(1,rowNo)+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
	url = url +'<img src=\'".base_url()."images/pdf_logo.gif\'/>'+'&nbsp;</a>';

	return url;	
}
";
		$colsOption = $sigmaA["colsOption"];

		$gridOption = "
var gridOption={
	id : 'grid1',
	loadURL : '".base_url()."ventas/spre/controlador',
	width: 700,
	height: 500,
	container : 'grid1_container',
	replaceContainer: true,
	dataset : dsOption ,
	columns : colsOption,
	allowCustomSkin: true,
	skin: 'vista',
	pageSize: ".$grid->per_page.",
	pageSizeList: [30,50,70, 100],
	toolbarPosition : 'bottom',
	toolbarContent: 'nav | pagesize | reload print excel pdf filter state',
	//showGridMenu : true,
	remotePaging: true,
	remoteSort: true,
	remoteFilter: true,
	autoload: true
};

var mygrid=new Sigma.Grid(gridOption);
Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );		
";



		$SigmaCont = "<center><div id=\"grid1_container\" style=\"width:750px;height:500px;\"></div></center>";
		$grid->add('ventas/spre/dataedit/create');
		$grid->build('datagridSG');

		$data['style']  = style("redmond/jquery-ui.css");
		$data['style'] .= style('gt_grid.css');
		$data['style'] .= style('skin/vista/skinstyle.css');

		$data["script"]  = script("jquery.js");
		$data['script'] .= script("gt_msg_es.js");
		$data['script'] .= script("gt_grid_all.js");
		$data['script'] .= "<script type=\"text/javascript\" >\n".$dsOption.$sprever."\n".$colsOption."\n".$gridOption."</script>";

		$data['content']  = $mtool;
		$data['content'] .= $SigmaCont;
		
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Presupuesto');
		$this->load->view('view_ventanas', $data);
	}


	function controlador(){
		//header('Content-type:text/javascript;charset=UTF-8');
		if (isset($_POST["_gt_json"]) ) {
			$json=json_decode(stripslashes($_POST["_gt_json"]));
			$pageNo   = $json->{'pageInfo'}->{'pageNum'};
			$pageSize = $json->{'pageInfo'}->{'pageSize'};
			$filter = '';

			if(isset($json->{'sortInfo'}[0]->{'columnId'})){
				$sortField = $json->{'sortInfo'}[0]->{'columnId'};
			} else {
				$sortField = "numero";
			}    
 
			if(isset($json->{'sortInfo'}[0]->{'sortOrder'})){
				$sortOrder = $json->{'sortInfo'}[0]->{'sortOrder'};
			} else {
				$sortOrder = "DESC";
			}    

			for ($i = 0; $i < count($json->{'filterInfo'}); $i++) {
				if($json->{'filterInfo'}[$i]->{'logic'} == "equal"){
					$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . "='" . $json->{'filterInfo'}[$i]->{'value'} . "' ";
				}elseif($json->{'filterInfo'}[$i]->{'logic'} == "notEqual"){
					$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . "!='" . $json->{'filterInfo'}[$i]->{'value'} . "' ";    
				}elseif($json->{'filterInfo'}[$i]->{'logic'} == "less"){
					$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . "<" . $json->{'filterInfo'}[$i]->{'value'} . " ";
				}elseif($json->{'filterInfo'}[$i]->{'logic'} == "lessEqual"){
					$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . "<=" . $json->{'filterInfo'}[$i]->{'value'} . " ";    
				}elseif($json->{'filterInfo'}[$i]->{'logic'} == "great"){
					$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . ">" . $json->{'filterInfo'}[$i]->{'value'} . " ";
				}elseif($json->{'filterInfo'}[$i]->{'logic'} == "greatEqual"){
					$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . ">=" . $json->{'filterInfo'}[$i]->{'value'} . " ";        
				}elseif($json->{'filterInfo'}[$i]->{'logic'} == "like"){
					$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . " LIKE '%" . $json->{'filterInfo'}[$i]->{'value'} . "%' ";        
				}elseif($json->{'filterInfo'}[$i]->{'logic'} == "startWith"){
					$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . " LIKE '" . $json->{'filterInfo'}[$i]->{'value'} . "%' ";        
				}elseif($json->{'filterInfo'}[$i]->{'logic'} == "endWith"){
					$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . " LIKE '%" . $json->{'filterInfo'}[$i]->{'value'} . "' ";                
				}
				$filter .= " AND ";
			}

			if($json->{'action'} == 'load'){

				//to get how many total records.
				$mSQL = "SELECT count(*) FROM spre WHERE $filter numero>0";
				$totalRec = $this->datasis->dameval($mSQL);
 
 
				//make sure pageNo is inbound
				if($pageNo<1||$pageNo>ceil(($totalRec/$pageSize))){
					$pageNo = 1;
				}
 
				$mSQL = "SELECT numero, fecha, cod_cli, nombre, totals, iva, totalg, vd, id FROM spre WHERE $filter numero>0 ORDER BY ".$sortField." ".$sortOrder." LIMIT ".($pageNo - 1)*$pageSize.", ".$pageSize;
				$query = $this->db->query($mSQL);
				if ($query->num_rows() > 0){
					$retArray = array();
					foreach( $query->result_array() as  $row ) {
						$meco = array();
						foreach( $row as $idd=>$campo ) {
							$meco[$idd] = utf8_encode($campo);
						}
						$retArray[] = $meco;
					}
					$data = json_encode($retArray);
					$ret = "{data:" . $data .",\n";
					$ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
					$ret .= "recordType : 'object'}";
				} else {
					$ret = '{data : []}';
				}

				echo $ret;

			}else if($json->{'action'} == 'save'){
				$sql = "";
				$params = array();
				$errors = "";
  
				//deal with those deleted
				$deletedRecords = $json->{'deletedRecords'};
				foreach ($deletedRecords as $value){
					$params[] = $value->id;
				}
				$sql = "delete from dbtable where id in (" . join(",", $params) . ")";
				if(mysql_query($sql)==FALSE){
					$errors .= mysql_error();
				}
				//deal with those updated
				$sql = "";
				$updatedRecords = $json->{'updatedRecords'};
				foreach ($updatedRecords as $value){
					$sql = "update `dbtable` set ".
					//fill out fields to be updated here
					"where `id`=".$value->id;
					if(mysql_query($sql)==FALSE){
						$errors .= mysql_error();
					}
				}
				//deal with those inserted
				$sql = "";
				$insertedRecords = $json->{'insertedRecords'};
				foreach ($insertedRecords as $value){
					$sql = "insert into dbtable (//fields to be inserted)";
					if(mysql_query($sql)==FALSE){
						$errors .= mysql_error();
					}
				}
				$ret = "{success : true,exception:''}";
				echo $ret;
			}
		} else {
			$pageNo = 1;
			$sortField = "numero";
			$sortOrder = "DESC";
			$pageSize = 50;//10 rows per page

			//to get how many records totally.
			$sql = "select count(*) as cnt from spre";
			$totalRec = $this->datasis->dameval($sql);

			//make sure pageNo is inbound
			if($pageNo<1||$pageNo>ceil(($totalRec/$pageSize))){
				$pageNo = 1;
			}

			//pageno starts with 1 instead of 0
			$mSQL = "SELECT numero, fecha, cod_cli, nombre, totals, iva, totalg FROM spre ORDER BY numero DESC LIMIT ".($pageNo - 1)*$pageSize.", ".$pageSize;
			$query = $this->db->query($mSQL);
	
			if ($query->num_rows() > 0){
				$retArray = array();
				foreach( $query->result_array() as  $row ) {
					$retArray[] = utf8_encode($row);
				}
				$data = json_encode($retArray);
				$ret = "{data:" . $data .",\n";
				$ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
				$ret .= "recordType : 'object'}";
			} else {
				$ret = '{data : []}';
			}
			echo $ret;
		}
	}
*/

	//**********************************************************************************************************
	//
	//**********************************************************************************************************
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
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'desca_<#i#>',
				'base1'  =>'precio1_<#i#>',
				'base2'  =>'precio2_<#i#>',
				'base3'  =>'precio3_<#i#>',
				'base4'  =>'precio4_<#i#>',
				'iva'    =>'itiva_<#i#>',
				'tipo'   =>'sinvtipo_<#i#>',
				'peso'   =>'sinvpeso_<#i#>',
				'pond'   =>'pond_<#i#>',
				'ultimo' =>'ultimo_<#i#>'
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
			'script'  => array('post_modbus_sinv(<#i#>)')
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
		'script'  => array('post_modbus_scli()'));
		$boton =$this->datasis->modbus($mSCLId);

		$do = new DataObject('spre');
		$do->rel_one_to_many('itspre', 'itspre', 'numero');
		$do->pointer('scli' ,'scli.cliente=spre.cod_cli','scli.tipo AS sclitipo','left');
		$do->rel_pointer('itspre','sinv','itspre.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Presupuestos', $do);
		$edit->back_url = site_url('ventas/spre/filteredgrid');
		$edit->set_rel_title('itspre','Producto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->vd = new  dropdownField ('Vendedor', 'vd');
		$edit->vd->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vd->style='width:200px;';
		$edit->vd->size = 5;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->rifci   = new inputField('RIF/CI','rifci');
		$edit->rifci->autocomplete=false;
		$edit->rifci->size = 15;

		$edit->direc = new inputField('Direcci&oacute;n','direc');
		$edit->direc->size = 40;

		//Para saber que precio se le va a dar al cliente
		$edit->sclitipo = new hiddenField('', 'sclitipo');
		$edit->sclitipo->db_name     = 'sclitipo';
		$edit->sclitipo->pointer     = true;
		$edit->sclitipo->insertValue = 1;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rel_id   = 'itspre';
		$edit->codigo->rule     = 'required';
		$edit->codigo->style    = 'width:80%;';
		$edit->codigo->autocomplete=false;
		$edit->codigo->append($btn);

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=40;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=40;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='itspre';
		$edit->desca->style    = 'width:99%';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'itspre';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive';
		$edit->cana->autocomplete=false;
		$edit->cana->onkeyup  ='importe(<#i#>)';
		$edit->cana->style    = 'width:98%';

		$edit->preca = new inputField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'itspre';
		//$edit->preca->size      = 10;
		$edit->preca->rule      = 'required|positive|callback_chpreca[<#i#>]';
		$edit->preca->readonly  = true;
		$edit->preca->style    = 'width:98%';

		$edit->importe = new inputField('Importe <#o#>', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=10;
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='itspre';
		$edit->importe->style    = 'width:98%';

		for($i=1;$i<4;$i++){
			$obj='precio'.$i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj.'_<#i#>');
			$edit->$obj->db_name   = 'sinv'.$obj;
			$edit->$obj->rel_id    = 'itspre';
			$edit->$obj->pointer   = true;
		}

		$edit->precio4 = new hiddenField('', 'precio4_<#i#>');
		$edit->precio4->db_name   = 'precio4';
		$edit->precio4->rel_id    = 'itspre';

		$edit->detalle = new hiddenField('', 'detalle_<#i#>');
		$edit->detalle->db_name  = 'detalle';
		$edit->detalle->rel_id   = 'itspre';

		$edit->itiva = new hiddenField('', 'itiva_<#i#>');
		$edit->itiva->db_name  = 'iva';
		$edit->itiva->rel_id   = 'itspre';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name   = 'sinvpeso';
		$edit->sinvpeso->rel_id    = 'itspre';
		$edit->sinvpeso->pointer   = true;

		$edit->sinvtipo = new hiddenField('', 'sinvtipo_<#i#>');
		$edit->sinvtipo->db_name   = 'sinvtipo';
		$edit->sinvtipo->rel_id    = 'itspre';
		$edit->sinvtipo->pointer   = true;

		$edit->ultimo = new hiddenField('', 'ultimo_<#i#>');
		$edit->ultimo->db_name   = 'ultimo';
		$edit->ultimo->rel_id    = 'itspre';

		$edit->pond = new hiddenField('', "pond_<#i#>");
		$edit->pond->db_name='pond';
		$edit->pond->rel_id   ='itspre';
		//**************************
		//fin de campos para detalle
		//**************************

		$edit->ivat = new hiddenField('Impuesto', 'iva');
		$edit->ivat->css_class ='inputnum';
		$edit->ivat->readonly  =true;
		$edit->ivat->size      = 10;

		$edit->totals = new hiddenField('Sub-Total', 'totals');
		$edit->totals->css_class ='inputnum';
		$edit->totals->readonly  =true;
		$edit->totals->size      = 10;

		$edit->totalg = new hiddenField('Monto Total', 'totalg');
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->readonly  =true;
		$edit->totalg->size      = 10;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->condi1 = new inputField('Condiciones', 'condi1');
		$edit->condi1->size = 40;
		$edit->condi1->maxlength=25;
		$edit->condi1->autocomplete=false;

		$edit->condi2 = new inputField('Condiciones', 'condi2');
		$edit->condi2->size = 40;
		$edit->condi2->maxlength=25;
		$edit->condi2->autocomplete=false;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_spre', $conten,true);
		$data['title']   = heading('Presupuesto No.'.$edit->numero->value);

		//$data['style']  = style('vino/jquery-ui.css');
		$data['style']  = style('redmond/jquery-ui-1.8.1.custom.css');

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		//$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		//$data['script'] .= script('plugins/jquery.autocomplete.js');
		$data['script'] .= phpscript('nformat.js');

		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	// Busca Clientes para autocomplete
	function buscascli($tipo='rifci'){
		$mid  = $this->input->post('q');
		$qdb  = $this->db->escape('%'.$mid.'%');

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo
				FROM scli WHERE rifci LIKE ${qdb} OR cliente LIKE ${qdb}
				ORDER BY rifci LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row[$tipo];
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

	// Busca Productos para autocomplete
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

	function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('nspre');
		$do->set('numero',$numero);
		$fecha =$do->get('fecha');
		$vd    =$do->get('vd');

		$iva=$totals=0;
		$cana=$do->count_rel('itspre');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itspre','cana',$i);
			$itpreca   = $do->get_rel('itspre','preca',$i);
			$itiva     = $do->get_rel('itspre','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itspre','importe' ,$itimporte,$i);
			$do->set_rel('itspre','totaorg' ,$itimporte*(1+($itiva/100)),$i);
			$do->set_rel('itspre','fecha'   ,$fecha  ,$i);
			$do->set_rel('itspre','vendedor',$vd     ,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;

		$do->set('inicial',0 );
		$do->set('totals' ,round($totals ,2));
		$do->set('totalg' ,round($totalg ,2));
		$do->set('iva'    ,round($iva    ,2));
		//print_r($do->get_all()); return false;

		return true;
	}

	function _pre_update($do){
		$fecha =$do->get('fecha');
		$vd    =$do->get('vd');

		$iva=$totals=0;
		$cana=$do->count_rel('itspre');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itspre','cana',$i);
			$itpreca   = $do->get_rel('itspre','preca',$i);
			$itiva     = $do->get_rel('itspre','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itspre','importe' ,$itimporte,$i);
			$do->set_rel('itspre','totaorg' ,$itimporte,$i);
			$do->set_rel('itspre','fecha'   ,$fecha  ,$i);
			$do->set_rel('itspre','vendedor',$vd     ,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;

		$do->set('inicial',0 );
		$do->set('totals' ,round($totals ,2));
		$do->set('totalg' ,round($totalg ,2));
		$do->set('iva'    ,round($iva    ,2));
		
		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('spre',"PRESUPUESTO $codigo CREADO");
	}

	function chpreca($preca,$ind){
		$codigo  = $this->input->post('codigo_'.$ind);
		$precio4 = $this->datasis->dameval('SELECT base4 FROM sinv WHERE codigo='.$this->db->escape($codigo));
		$preca   = round($preca,2);
		if($precio4<0) $precio4=0;

		if($preca<$precio4){
			$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' debe contener un precio de al menos '.nformat($precio4).'' .$preca);
			return false;
		}else{
			return true;
		}
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('spre',"PRESUPUESTO $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('spre',"PRESUPUESTO $codigo ELIMINADO");
	}



	//*************************************
	//
	//           ANULAR FACTURA
	//
	//
	//*************************************
	function sfacanu( $tipo_doc, $numero ){
		//LOCAL i, mRAPIDA := .F., mLLAMA := .F., mDESCU := .T.
		//LOCAL mMIENT   := {0,0,0}
		//LOCAL mALMACEN := '0001'
		//LOCAL mTRANSAC := ''
		//LOCAL mPEDIDO, mTIPO
		
		$query=$this->db->query("SELECT * FROM sfac WHERE tipo_doc='$tipo_doc' AND numero'$numero'");
		$sfac     = $query->row_array();

		$referen =  $this->datasis->dameval();


		// SI YA SE BORRO PUES NI MODO
		if ($tipo_doc  == 'X'){
			echo 'Ya fue Borrada';
			return;
			/*
			if XREFEREN = 'C'
				IF SINO("Recuperar Anulacion?",2) = 1
					mSQL := "UPDATE sfac SET tipo_doc='F' WHERE tipo_doc='"+XTIPO_DOC+"' AND numero='"+XNUMERO+"'"
					EJECUTASQL(mSQL)
					mSQL := "UPDATE sitems SET tipoa='F' WHERE tipoa='"+XTIPO_DOC+"' AND numa='"+XNUMERO+"'"
					EJECUTASQL(mSQL)
					XTIPO_DOC='F'
					CMNJ('FACTURA RECUPERADA')
				ENDIF
			ENDIF*/
		}


		// PENDIENTE LA BORRA SIN PELIGRO
		if ( $referen == 'P' and SUBSTR($numero,0,1)=='P' ) {
			$mSQL = "DELETE FROM sfac WHERE numero='$numero' AND tipo_doc='$tipo_doc' ";
			$this->db->simple_query($mSQL);
			$mSQL = "DELETE FROM sitems WHERE numa='$numero' AND tipoa='$tipo_doc' ";
			$this->db->simple_query($mSQL);
			$mSQL = "UPDATE seri SET venta='', fechav=0 WHERE venta='$numero";
			$this->db->simple_query($mSQL);
			echo "Documento Anulado";
			logusu("FACTURA ANULADA "+XTIPO_DOC+" "+XNUMERO);
			return;
		}

		if ( $tipo_doc != 'D' AND $tipo_doc != 'F') {
			echo "Documento no Anulable";
			return;
		}

		// REVISAR SI TIENE ABONOS
		$mPEDIDO = ("pedido");
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;


		$where = "";

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
				//Dummy Where. 
				$where = "numero IS NOT NULL ";
				$qs = "";
				for ($i=0;$i<count($filter);$i++){
					switch($filter[$i]['type']){
					case 'string' : $qs .= " AND ".$filter[$i]['field']." LIKE '%".$filter[$i]['value']."%'"; 
						Break;
					case 'list' :
						if (strstr($filter[$i]['value'],',')){
							$fi = explode(',',$filter[$i]['value']);
							for ($q=0;$q<count($fi);$q++){
								$fi[$q] = "'".$fi[$q]."'";
							}
							$filter[$i]['value'] = implode(',',$fi);
								$qs .= " AND ".$filter[$i]['field']." IN (".$filter[$i]['value'].")";
						}else{
							$qs .= " AND ".$filter[$i]['field']." = '".$filter[$i]['value']."'";
						}
						Break;
					case 'boolean' : $qs .= " AND ".$filter[$i]['field']." = ".($filter[$i]['value']); 
						Break;
					case 'numeric' :
						switch ($filter[$i]['comparison']) {
							case 'ne' : $qs .= " AND ".$filter[$i]['field']." != ".$filter[$i]['value']; 
								Break;
							case 'eq' : $qs .= " AND ".$filter[$i]['field']." = ".$filter[$i]['value']; 
								Break;
							case 'lt' : $qs .= " AND ".$filter[$i]['field']." < ".$filter[$i]['value']; 
								Break;
							case 'gt' : $qs .= " AND ".$filter[$i]['field']." > ".$filter[$i]['value']; 
								Break;
						}
						Break;
					case 'date' :
						switch ($filter[$i]['comparison']) {
							case 'ne' : $qs .= " AND ".$filter[$i]['field']." != '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'eq' : $qs .= " AND ".$filter[$i]['field']." = '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'lt' : $qs .= " AND ".$filter[$i]['field']." < '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
							case 'gt' : $qs .= " AND ".$filter[$i]['field']." > '".date('Y-m-d',strtotime($filter[$i]['value']))."'"; 
								Break;
						}
						Break;
					}
				}
				$where .= $qs;
			}
		}
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('spre');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'numero', 'desc' );

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $query->num_rows();

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

	function griditspre(){
		$numero   = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  0;
		if ($numero == 0 ) $numero = $this->datasis->dameval("SELECT MAX(numero) FROM spre")  ;

		$mSQL = "SELECT a.codigo, a.desca, a.cana, a.preca, a.importe, a.iva, round(a.precio4*100/(100+a.iva),2) precio4, b.id codid FROM itspre a JOIN sinv b ON a.codigo=b.codigo WHERE a.numero='$numero' ORDER BY a.codigo";
		$query = $this->db->query($mSQL);
		$results =  0; //$this->datasis->dameval("SELECT COUNT(*) FROM spre");
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
		$id = $this->datasis->dameval("SELECT b.id FROM spre a JOIN scli b ON a.cod_cli=b.cliente WHERE numero='$numero'");
		redirect('ventas/scli/dataedit/show/'.$id);
	}

	function spreextjs() {

		$encabeza='<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">PRESUPUESTOS A CLIENTES</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';
		$listados= $this->datasis->listados('spre');
		
		$otros=$this->datasis->otros('spre', 'spre');
		//$otros = '';

		$script = "
<script type=\"text/javascript\">		
var BASE_URL   = '".base_url()."';
var BASE_PATH  = '".base_url()."';
var BASE_ICONS = '".base_url()."assets/icons/';
var BASE_UX    = '".base_url()."assets/js/ext/ux';
var modulo = 'spre'

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

var urlApp = '".base_url()."';

Ext.require([
	'Ext.grid.*',
	'Ext.ux.grid.FiltersFeature',
	'Ext.data.*',
	'Ext.util.*',
	'Ext.state.*',
	'Ext.form.*',
	'Ext.window.MessageBox',
	'Ext.tip.*',
	'Ext.ux.CheckColumn',
	'Ext.toolbar.Paging'
]);

var mxs = ((screen.availWidth/2)-400);
var mys = ((screen.availHeight/2)-300);

/*
//coloca link al numero de presupuesto
function rpresupuesto(value, p, record) {
return Ext.String.format(
		'<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'ventas/spre/dataedit/show/{2}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>',
		value,
		record.data.nombre,
		record.getId(),
		record.data.id
        );
};
*/

//Column Model Presupuestos
var SpreCol = 
	[
		{ header: 'Numero',     width:  60, sortable: true,  dataIndex: 'numero',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Fecha',      width:  70, sortable: false, dataIndex: 'fecha',   field: { type: 'date'      }, filter: { type: 'date'   }}, 
		{ header: 'Cliente',    width:  50, sortable: true,  dataIndex: 'cod_cli', field: { type: 'textfield' }, filter: { type: 'string' }, renderer: renderScli }, 
		{ header: 'Nombre',     width: 200, sortable: true,  dataIndex: 'nombre',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'SubTotal',   width: 100, sortable: true,  dataIndex: 'totals',  field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'IVA',        width:  80, sortable: true,  dataIndex: 'iva',     field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Total',      width: 100, sortable: true,  dataIndex: 'totalg',  field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Peso',       width:  60, sortable: true,  dataIndex: 'peso',    field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Condiciones',width: 160, sortable: true,  dataIndex: 'condi1',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Vende',      width:  40, sortable: true,  dataIndex: 'vd',      field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Usuario',    width:  60, sortable: true,  dataIndex: 'usuario', field: { type: 'textfield' }, filter: { type: 'string' }}
/*
		field: { xtype: 'combobox', triggerAction: 'all', valueField:'abre', displayField:'todo', store: tipos, listClass: 'x-combo-list-small'}, filter: { type: 'string' }, editor: { allowBlank: false }}, 
*/
	];

//Column Model Detalle de Presupuesto
var ItSpreCol = 
	[
		{ header: 'Codigo',      width:  90, sortable: true, dataIndex: 'codigo', field: { type: 'textfield' }, filter: { type: 'string' }, renderer: renderSinv }, 
		{ header: 'codid',       dataIndex: 'codid',  hidden: true}, 
		{ header: 'Descripcion', width: 250, sortable: true, dataIndex: 'desca',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Cant',        width:  60, sortable: true, dataIndex: 'cana',   field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Precio',      width: 100, sortable: true, dataIndex: 'preca',  field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Importe',     width: 100, sortable: true, dataIndex: 'importe',field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',         width:  60, sortable: true, dataIndex: 'iva',    field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Precio 4',    width:  60, sortable: true, dataIndex: 'precio4',field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}
	];


function renderScli(value, p, record) {
	var mreto='';
	if ( record.data.cod_cli == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'ventas/spre/sclibu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.numero );
}


function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}



// application main entry point
Ext.onReady(function() {
	Ext.QuickTips.init();
	/////////////////////////////////////////////////
	// Define los data model
	// Presupuestos
	Ext.define('Spre', {
		extend: 'Ext.data.Model',
		fields: ['id', 'numero', 'fecha', 'vd', 'cod_cli', 'nombre',  'totals',	'iva', 'totalg', 'peso', 'totals', 'usuario'],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'ventas/spre/grid',
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
	var storeSpre = Ext.create('Ext.data.Store', {
		model: 'Spre',
		pageSize: 50,
		remoteSort: true,
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	//Filters
	var filters = {
		ftype: 'filters',
		// encode and local configuration options defined previously for easier reuse
		encode: 'json', // json encode the filter query
		local: false
	};    


	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridSpre = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeSpre,
		title: 'Presupuestos',
		iconCls: 'icon-grid',
		frame: true,
		columns: SpreCol,
		dockedItems: [{
			xtype: 'toolbar',
			items: [
				{
					iconCls: 'icon-add',
					text: 'Agregar',
					scope: this,
					handler: function(){
						window.open(urlApp+'ventas/spre/dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},
				{
					iconCls: 'icon-update',
					text: 'Modificar',
					disabled: true,
					itemId: 'update',
					scope: this,
					handler: function(selModel, selections){
						var selection = gridSpre.getView().getSelectionModel().getSelection()[0];
						gridSpre.down('#delete').setDisabled(selections.length === 0);
						window.open(urlApp+'ventas/spre/dataedit/modify/'+selection.data.id, '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},
				{
					iconCls: 'icon-delete',
					text: 'Eliminar',
					disabled: true,
					itemId: 'delete',
					scope: this,
					handler: function() {
						var selection = gridSpre.getView().getSelectionModel().getSelection()[0];
						Ext.MessageBox.show({
							title: 'Confirme', 
							msg: 'Seguro que quiere eliminar el presupuesto Nro. '+selection.data.numero, 
							buttons: Ext.MessageBox.YESNO, 
							fn: function(btn){ 
								if (btn == 'yes') { 
									if (selection) {
										//storeSpre.remove(selection);
									}
									storeSpre.load();
								} 
							}, 
							icon: Ext.MessageBox.QUESTION 
						});  
					}
				}
			]
		}],
		features: [filters],
		// paging bar on the bottom
		bbar: Ext.create('Ext.PagingToolbar', {
			store: storeSpre,
			displayInfo: false,
			displayMsg: 'Pag No. {0} - Reg. {1} de {2}',
			emptyMsg: 'No se encontraron Registros.'
		}),
	});

//////************ MENU DE ADICIONALES /////////////////
".$listados."

".$otros."
//////************ FIN DE ADICIONALES /////////////////


	/////////////////////////////////////////////////
	// Define los data model
	// Contratos
	Ext.define('ItSpre', {
		extend: 'Ext.data.Model',
		fields: ['codigo', 'codid', 'desca', 'cana', 'preca', 'importe', 'iva', 'precio4' ],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'ventas/spre/griditspre',
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
	var storeItSpre = Ext.create('Ext.data.Store', {
		model: 'ItSpre',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});


	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridItSpre = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeItSpre,
		title: 'Articulos',
		iconCls: 'icon-grid',
		frame: true,
		columns: ItSpreCol
	});

	// define a template to use for the detail view
	var spreTplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR PRESUPUESTO</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/verhtml/PRESUP/{numero}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/ver/PRESUP/{numero}\',     \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',   'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',		
		'</table>'
	];
	var spreTpl = Ext.create('Ext.Template', spreTplMarkup);

	// Al cambiar seleccion de Nomina
	gridSpre.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridSpre.down('#delete').setDisabled(selectedRecord.length === 0);
			gridSpre.down('#update').setDisabled(selectedRecord.length === 0);
			numero = selectedRecord[0].data.numero;
			gridItSpre.setTitle(numero+' '+selectedRecord[0].data.nombre);
			storeItSpre.load({ params: { numero: numero }});
			var meco1 = Ext.getCmp('imprimir');
			meco1.setTitle('Imprimir Presupuesto');
			spreTpl.overwrite(meco1.body, selectedRecord[0].data);
		}
	});

	var viewport = new Ext.Viewport({
		id:'simplevp',
		layout:'border',
		border:false,
		items:[{
			region: 'north',
			preventHeader: true,
			height: 40,
			minHeight: 40,
			html: '".$encabeza."'
		},{
			region:'west',
			width:200,
			border:false,
			autoScroll:true,
			title:'Lista de Opciones',
			collapsible:true,
			split:true,
			collapseMode:'mini',
			layoutConfig:{animate:true},
			layout: 'accordion',
			items: [
				{
					title:'Imprimir',
					defaults:{border:false},
					layout: 'fit',
					items:[{
						name: 'imprimir',
						id: 'imprimir',
						preventHeader: true,
						border:false,
						html: 'Para imprimir seleccione un Presupuesto '
					}]
				},
				{
					title:'Listados',
					border:false,
					layout: 'fit',
					items: gridListado

				},
				{
					title:'Otras Funciones',
					border:false,
					layout: 'fit',
					items: gridOtros
				}
			]
		},{
			region:'south',
			id: 'sur',
			height:50,
			html:'Sur',
			border:false,
			title:'Sur',
			collapsible:true
		},
		{
			cls: 'irm-column irm-center-column irm-master-detail',
			region: 'center',
			title:  'center-title',
			layout: 'border',
			preventHeader: true,
			border: false,
			items: [{
				itemId: 'viewport-center-master',
				cls: 'irm-master',
				region: 'center',
				items: gridSpre
			},{
				itemId: 'viewport-center-detail',
				preventHeader: true,
				region: 'south',
				height: '40%',
				split: true,
				//collapsible: true,
				title: 'center-detail-title',
				margins: '0 0 0 0',
				items: gridItSpre
			}]	
		}]
	});
	storeSpre.load();
	storeItSpre.load();
});

</script>
";
		return $script;	
		
	}


}