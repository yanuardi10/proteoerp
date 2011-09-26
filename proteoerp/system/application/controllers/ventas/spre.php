<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class spre extends validaciones {

	function spre(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(104,1);
	}

	function index() {
		redirect('ventas/spre/filteredgrid');
	}

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

//memowrite($_POST["_gt_json"],"jsonrecibido");
   

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
//memowrite($mSQL,"mSQL");
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
//memowrite(serialize($ret),"jsonretret");
				echo $ret;

			}else if($json->{'action'} == 'save'){
/*				$sql = "";
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
				echo $ret;*/
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
		$edit->codigo->style    = 'width:80%';
		$edit->codigo->autocomplete=false;
		$edit->codigo->append($btn);

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=40;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=40;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='itspre';
		$edit->desca->style    = 'width:100%';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'itspre';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive';
		$edit->cana->autocomplete=false;
		$edit->cana->onkeyup  ='importe(<#i#>)';
		$edit->cana->style    = 'width:90%';

		$edit->preca = new inputField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'itspre';
		//$edit->preca->size      = 10;
		$edit->preca->rule      = 'required|positive|callback_chpreca[<#i#>]';
		$edit->preca->readonly  = true;
		$edit->preca->style    = 'width:100%';

		$edit->importe = new inputField('Importe <#o#>', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=10;
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='itspre';
		$edit->importe->style    = 'width:90%';

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
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
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
/*
IF XFECHA <> DATE()
   IF XREFEREN = 'C'
      mSQL := "SELECT COUNT(*) FROM smov WHERE cod_cli='"+XCOD_CLI+"' "
      mSQL += "AND tipo_doc='FC' AND numero='"+XNUMERO+"' "
      IF DAMEVAL(mSQL,,'N') >  0 //XINICIAL
         mSQL := "SELECT abonos FROM smov WHERE cod_cli='"+XCOD_CLI+"' "
         mSQL += "AND tipo_doc='FC' AND numero='"+XNUMERO+"' "
         IF DAMEVAL(mSQL,,'N') <> XINICIAL
            CMNJ("Esta factura ya tiene Pagos; Anule el Pago primero!!")
            RETURN .T.
         ENDIF
      ENDIF

      IF MONTH(XFECHA) <> MONTH(DATE())
         CMNJ("Factura de mes anterior si prosigue puede alterar los libros fiscales")
      ELSE
         CMNJ("Factura de fecha anterior;asegurese de saber lo que hace")
      ENDIF
   ELSE
      CMNJ("No se pueden anular facturas de contado de ; dias anteriores")
      RETURN .T.
   ENDIF
ELSE
   IF XREFEREN = 'C'
      mSQL := "SELECT COUNT(*) FROM smov WHERE cod_cli='"+XCOD_CLI+"' "
      mSQL += "AND tipo_doc='FC' AND numero='"+XNUMERO+"' "
      IF DAMEVAL(mSQL,,'N') > 0
         mSQL := "SELECT abonos FROM smov WHERE cod_cli='"+XCOD_CLI+"' "
         mSQL += "AND tipo_doc='FC' AND numero='"+XNUMERO+"' "
         IF DAMEVAL(mSQL,,'N') <> XINICIAL
            CMNJ("Esta factura ya tiene Pagos; Anule el Pago primero!!")
            RETURN .T.
         ENDIF
      ENDIF
   ENDIF
ENDIF

// REVISAR INVENTARIO FISICO
mSQL := "SELECT count(*) FROM costos AS a JOIN sitems AS b "
mSQL += "ON a.codigo=b.codigoa WHERE a.fecha>"+DTOS(XFECHA)
mSQL += " and b.numa='"+XNUMERO+"' AND a.origen='0F' "
IF DAMEVAL(mSQL,,'N') > 0
   CMNJ("Tiene productos inventariados recientement; debe hacer una devolucion.")
   RETURN .T.
ENDIF

IF SINO("Seguro que desea Anularla",1) = 1
   mSQL := "SELECT almacen,transac, factura FROM sfac WHERE tipo_doc='"+XTIPO_DOC+"' "
   mSQL += "AND numero='"+XNUMERO+"' "
   mREG := DAMEREG(mSQL,8)
   mTRANSAC := mREG[2]
   mALMACEN := mREG[1]
   mFACTURA := mREG[3]

   IF EMPTY(mTRANSAC)
      CMNJ("Movimiento sin transaccion")
      RETURN .T.
   ENDIF

   IF XTIPO_DOC = 'F'
      mSQL := "UPDATE sfac SET tipo_doc='X'  WHERE transac='"+mTRANSAC+"' "
      mSQL += " AND numero='"+XNUMERO+"' AND tipo_doc='"+XTIPO_DOC+"' "
      EJECUTASQL(mSQL)

      mSQL := "UPDATE seri SET venta='', fechav=0 WHERE venta='"+XNUMERO+"'"
      EJECUTASQL(mSQL)

      // BUSCA A VER SI TIENE NOTA DE ENTREGA
      mSQL   := "SELECT COUNT(*) FROM snte WHERE factura='"+XNUMERO+"' "
      mDESCU := IF(DAMEVAL(mSQL,,'N')=0,.T.,.F.)

      mSQL := "UPDATE snte SET factura='', fechafac=0 "
      mSQL += "WHERE factura='"+XNUMERO+"' "
      EJECUTASQL(mSQL)

      SFACCARGAD()
      mSQL := "UPDATE sitems SET tipoa='X' WHERE numa='"+XNUMERO+"' AND tipoa='"+XTIPO_DOC+"'"
      EJECUTASQL(mSQL)
      // DESCUENTA INVENTARIO
      IF mDESCU
         FOR i := 1 TO LEN(mm_DETA)
            mTIPO := DAMEVAL("SELECT MID(tipo,1,1) FROM sinv WHERE codigo='"+mm_DETA[i,1]+"'")
            SINVCARGA(mm_DETA[i,1], mALMACEN ,mm_DETA[i,3])
            IF mTIPO = 'L'
               SINVLOTCARGA( mm_DETA[i,1], mALMACEN, mm_DETA[i,8], mm_DETA[i,3] )
            ENDIF
         NEXT
      ENDIF

      mSQL := "DELETE FROM sfpa WHERE transac='"+mTRANSAC+"'"
      EJECUTASQL(mSQL)

      // QUITA DE CXC
      mSQL := "DELETE FROM smov WHERE transac='"+mTRANSAC+"' "
      EJECUTASQL(mSQL)
      
      // LIBERA LOS SERIALES
      mSQL := "UPDATE seri SET venta='' WHERE venta='"+XNUMERO+"' "
      EJECUTASQL(mSQL)

      // Regresa Pedido
      IF !EMPTY(mPEDIDO)
         mSQL := "UPDATE itpfac SET entregado=entregado-? WHERE numa=? AND codigoa=? "
         FOR i := 1 TO LEN(mm_DETA)
            EJECUTASQL(mSQL,{ mm_DETA[i,3], mPEDIDO, mm_DETA[i,1] })
         NEXT
         mSQL := "UPDATE itpfac SET entregado=0 WHERE numa=? AND entregado<0 "
         EJECUTASQL(mSQL,{ mPEDIDO })
         mSQL := "SELECT sum(entregado) FROM itpfac WHERE numa='"+mPEDIDO+"' GROUP BY numa "
         mCAN := DAMEVAL(mSQL,,'N')
         IF mCAN  = 0
            mSQL := "UPDATE pfac SET status='P' WHERE numero='"+mPEDIDO+"' "
         ELSE
            mSQL := "UPDATE pfac SET status='B' WHERE numero='"+mPEDIDO+"' "
         ENDIF
         EJECUTASQL(mSQL)
         EJECUTASQL("UPDATE sfac SET pedido='' WHERE tipo_doc='X' AND numero='"+XNUMERO+"' ")
      ENDIF

   // ANULA DEVOLUCIONES
   ELSEIF XTIPO_DOC = 'D'
      mSQL := "UPDATE sfac SET tipo_doc='X', numero='D"+SUBSTR(XNUMERO,2,7)+"' "
      mSQL += "WHERE transac='"+mTRANSAC+"' "
      EJECUTASQL(mSQL)
      mDESCU := .T.
      
      // DEVUELVE LOS SERIALES
      mREG := DAMEREG("SELECT factura, fecha FROM sfac WHERE transac='"+mTRANSAC+"' ")
      mSQL := "UPDATE seri SET venta=?, fechav=? WHERE devolu='"+XNUMERO+"'"
      EJECUTASQL(mSQL, mREG )

      SFACCARGAD()
      mSQL := "UPDATE sitems SET tipoa='X', numa='D"+SUBSTR(XNUMERO,2,7)+"' " 
      mSQL += "WHERE numa='"+XNUMERO+"' AND tipoa='"+XTIPO_DOC+"'"
      EJECUTASQL(mSQL)
      // DESCUENTA INVENTARIO
      FOR i := 1 TO LEN(mm_DETA)
         mTIPO := DAMEVAL("SELECT MID(tipo,1,1) FROM sinv WHERE codigo='"+mm_DETA[i,1]+"'")
         SINVCARGA(mm_DETA[i,1], mALMACEN ,-mm_DETA[i,3])
         IF mTIPO = 'L'
            SINVLOTCARGA( mm_DETA[i,1], mALMACEN, mm_DETA[i,8], -mm_DETA[i,3] )
         ENDIF
      NEXT

      mSQL := "UPDATE sitems SET tipoa='X' WHERE numa='"+XNUMERO+"' AND tipoa='"+XTIPO_DOC+"'"
      EJECUTASQL(mSQL)

      // GUARDA FORMA DE PAGO
      mSQL := "DELETE FROM sfpa WHERE transac='"+mTRANSAC+"'"
      EJECUTASQL(mSQL)

      // GUARDA EN CXC
      IF XREFEREN = 'C'
         mSQL := "DELETE FROM smov WHERE transac='"+mTRANSAC+"'"
         EJECUTASQL(mSQL)

         // REVERSA ABONADO
         mSQL := "UPDATE smov SET abonos=abonos-"+ALLTRIM(STR(XTOTALG))
         mSQL += " WHERE cod_cli='"+XCOD_CLI+"' AND tipo_doc='FC' AND "
         mSQL += "numero='"+mFACTURA+"' "
         EJECUTASQL(mSQL)
      ENDIF
   ENDIF

   CMNJ("Documento Anulado")
   LOGUSU("FACTURA ANULADA "+XTIPO_DOC+" "+XNUMERO+' PEDIDO '+mPEDIDO)
   XTIPO_DOC := 'X'
   oCursor:FieldPut('tipo_doc',XTIPO_DOC)
   oCursor:Refresh()

ENDIF
RETURN("")
*/
	}


}