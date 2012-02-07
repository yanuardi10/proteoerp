<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class dpto extends validaciones{
	 
	function Dpto(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(309,1);
	}

	function index(){
		$this->db->simple_query("INSERT IGNORE INTO dpto (depto,tipo,descrip) VALUES ('99','G','INVERSION EN ACTIVOS')ON DUPLICATE KEY UPDATE depto='99', tipo='G',descrip='INVERSION EN ACTIVOS'");
		$this->db->simple_query("INSERT IGNORE INTO dpto (depto,tipo,descrip) VALUES ('98','G','GASTOS FINANCIEROS')ON DUPLICATE KEY UPDATE depto='98', tipo='G',descrip='GASTOS FINANCIEROS'");
		$this->db->simple_query("INSERT IGNORE INTO dpto (depto,tipo,descrip) VALUES ('97','G','GASTOS DE ADMINISTRACION')ON DUPLICATE KEY UPDATE depto='97', tipo='G',descrip='GASTOS DE ADMINISTRACION'");
		$this->db->simple_query("INSERT IGNORE INTO dpto (depto,tipo,descrip) VALUES ('96','G','GASTOS DE VENTA')ON DUPLICATE KEY UPDATE depto='96', tipo='G',descrip='GASTOS DE VENTA'");
		$this->db->simple_query("INSERT IGNORE INTO dpto (depto,tipo,descrip) VALUES ('95','G','GASTOS DE COMPRA')ON DUPLICATE KEY UPDATE depto='95', tipo='G',descrip='GASTOS DE COMPRA'");
		if ( !$this->datasis->iscampo('dpto','id') ) {
			$this->db->simple_query('ALTER TABLE dpto DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE dpto ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE dpto ADD UNIQUE INDEX depto (depto)');
		}
		$this->datasis->modulo_id(309,1);
		$this->dptoextjs();
		//redirect("inventario/dpto/filteredgrid");
	}
 
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		// tool bar		
		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";
		$mtool .= "<td>&nbsp;<a href='".base_url()."inventario/dpto/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";
		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."inventario/line', '_blank', 'width=600, height=500, scrollbars=No, status=No, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="500"'.'>';
		$mtool .= img(array('src' => 'images/lineas.png', 'alt' => 'Gestion de Lineas', 'title' => 'Gestion de Lineas','border'=>'0','height'=>'34'));
		$mtool .= "</a>&nbsp;</td>";
		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."inventario/grup', '_blank', 'width=600, height=500, scrollbars=No, status=No, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="500"'.'>';
		$mtool .= img(array('src' => 'images/grupo.jpg', 'alt' => 'Gestion de Grupos', 'title' => 'Gestion de Grupos','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";
		$mtool .= "</tr></table>";

		$uri = anchor('inventario/dpto/dataedit/show/<raencode><#depto#></raencode>','<#depto#>');
		$uri_2 = anchor('inventario/dpto/dataedit/create/<raencode><#depto#></raencode>','Duplicar');

		$grid = new DataGrid("Lista de Departamentos");
		$grid->db->select("tipo,depto,descrip,cu_venta,cu_inve,cu_devo,cu_cost");
		$grid->db->from("dpto");
		$grid->order_by("depto","asc");
		$grid->per_page = 40;

		$grid->column_sigma("C&oacute;digo"            ,'depto',    '', "align:'center', width:50, frozen: true, renderer: ver");
		$grid->column_sigma("Tipo"                     ,'tipo',     '', "width:80, editor: { type: 'select', options: {'I':'Inventario', 'G':'Gastos'}}, renderer: coltipo");
		$grid->column_sigma("Descripci&oacute;n"       ,"descrip",  '', "align:'left',   width:200, editor: { type: 'text' }");
		$grid->column_sigma("Cuenta Venta"             ,"cu_venta", '', "align:'center', width:100");
		$grid->column_sigma("Cuenta Inventario"        ,"cu_inve",  '', "align:'center', width:100");
		$grid->column_sigma("Cuenta Costo"             ,"cu_cost",  '', "align:'center', width:100");
		$grid->column_sigma("Cuenta Devoluci&oacute;n" ,"cu_devo",  '', "align:'center', width:100");

		$sigmaA     = $grid->sigmaDsConfig("dpto","depto","inventario/dpto/");
		$dsOption   = $sigmaA["dsOption"];
		$grupver    = "
function ver(value, record, columnObj, grid, colNo, rowNo){
       var url = '';
       url = '<a href=\"#\" onclick=\"window.open(\'".base_url()."inventario/dpto/dataedit/show/'+value+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
       url = url +value+'</a>';
       return url;	
}

function coltipo(value, record, columnObj, grid, colNo, rowNo) {
	var options = {'I':'Inventario', 'G':'Gastos'};
	var ret = options[value];
	if(ret==null){ ret = value; }
	return ret;
}
";
	      $colsOption = $sigmaA["colsOption"];
	      $gridOption = $sigmaA["gridOption"];
	      $gridGuarda = $sigmaA["gridGuarda"];

	      $gridGo = "
var mygrid=new Sigma.Grid(gridOption);
mygrid.width  = 550;
mygrid.height = 400;
Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );
";

		$SigmaCont = "<center><div id=\"grid1_container\" style=\"width:550px;height:400px;\"></div></center>";
		$grid->add("inventario/dpto/dataedit/create");
		$grid->build('datagridSG');
		//echo $grid->db->last_query();

		$data['style']  = style("redmond/jquery-ui.css");
		$data['style'] .= style('gt_grid.css');

		$data["script"]  = script("jquery.js");
		$data['script'] .= script("gt_msg_es.js");
		$data['script'] .= script("gt_grid_all.js");

		$data['script'] .= "<script type=\"text/javascript\" >\n";
		$data['script'] .= $dsOption.$grupver."\n";
		$data['script'] .= $colsOption."\n";
		$data['script'] .= $gridOption;
		$data['script'] .= $gridGuarda;
		$data['script'] .= $gridGo;
		$data['script'] .= "\n</script>";

		$data['content'] = $mtool.$SigmaCont;  //$grid->output;
	
		//$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Departamentos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}


	// sigma grid
	function controlador() {
		//header('Content-type:text/javascript;charset=UTF-8');
		if (isset($_POST["_gt_json"]) ) {
			$json=json_decode(stripslashes($_POST["_gt_json"]));
			if($json->{'action'} == 'load') {
				$pageNo   = $json->{'pageInfo'}->{'pageNum'};
				$pageSize = $json->{'pageInfo'}->{'pageSize'};
				$filter = '';

				if(isset($json->{'sortInfo'}[0]->{'columnId'})){
					$sortField = $json->{'sortInfo'}[0]->{'columnId'};
				} else {
					$sortField = "tipo DESC, depto";
				}    
	 
				if(isset($json->{'sortInfo'}[0]->{'sortOrder'})){
					$sortOrder = $json->{'sortInfo'}[0]->{'sortOrder'};
				} else {
					$sortOrder = "ASC";
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

				//to get how many total records.
				$mSQL = "SELECT count(*) FROM dpto WHERE $filter depto IS NOT NULL";
				$totalRec = $this->datasis->dameval($mSQL);
  
				//make sure pageNo is inbound
				if($pageNo<1||$pageNo>ceil(($totalRec/$pageSize))){
					$pageNo = 1;
				}

				$mSQL = "SELECT depto, tipo, descrip,  cu_inve, cu_cost, cu_venta, cu_devo ";
				$mSQL .= "FROM dpto WHERE $filter depto IS NOT NULL ORDER BY ".$sortField." ".$sortOrder." LIMIT ".($pageNo - 1)*$pageSize.", ".$pageSize;
				$query = $this->db->query($mSQL);
				if ($query->num_rows() > 0){
					$retArray = array();
					foreach( $query->result_array() as  $row ) {
						$retArray[] = $row;
					}
					$data = json_encode($retArray);
					$ret = "{data:" . $data .",\n";
					$ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
					$ret .= "recordType : 'object'}";
				} else {
					$ret = '{data : []}';
				}
				echo $ret;

			}else if($json->{'action'} == 'save'){	}
		} else {
			// no hay _gt_json
			echo '{data : []}';
		}
	}

       function modifica(){
	      $valor = $this->uri->segment($this->uri->total_segments());
	      $campo = $this->uri->segment($this->uri->total_segments()-1);
	      $grupo = $this->uri->segment($this->uri->total_segments()-2);
	      $mSQL = "UPDATE dpto SET ".$campo."='".addslashes($valor)."' WHERE depto='".$grupo."' ";
	      $this->db->simple_query($mSQL);
	      echo "$valor $campo $grupo";
       }


	function dataedit($status='',$id=''){
		$this->rapyd->load("dataobject","dataedit");

		$qformato=$this->qformato=$this->datasis->formato_cpla();
		$link=site_url('inventario/dpto/ultimo');
		$link2=site_url('inventario/common/sugerir_dpto');
		
		$script='
		function ultimo(){
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo codigo ingresado fue: " + msg );
				}
			});
		}
		
		function sugerir(){
			$.ajax({
					url: "'.$link2.'",
					success: function(msg){
						if(msg){
							$("#depto").val(msg);
						}
						else{
							alert("No es posible generar otra sugerencia. Coloque el c&oacute;digo manualmente");
						}
					}
				});
		}		
		';
		
		$modbus=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'<#i#>'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
			'p_uri'=>array(4=>'<#i#>')
		);

		$bcu_venta = $this->datasis->p_modbus($modbus,'cu_venta');
		$bcu_inve  = $this->datasis->p_modbus($modbus,'cu_inve' );
		$bcu_cost  = $this->datasis->p_modbus($modbus,'cu_cost' );
		$bcu_devo  = $this->datasis->p_modbus($modbus,'cu_devo' );
		
		$do = new DataObject("dpto");
		$do->set('tipo', 'I');
		if($status=="create" && !empty($id)){
			$do->load($id);
			$do->set('depto', '');
		}
		
		$edit = new DataEdit("Departamento", $do);
		$edit->back_url = site_url("inventario/dpto/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('delete','_pre_del');
		
		$ultimo='<a href="javascript:ultimo();" title="Consultar ultimo c&oacute;digo ingresado"> Consultar ultimo c&oacute;digo</a>';
		$sugerir='<a href="javascript:sugerir();" title="Sugerir un C&oacute;digo aleatorio">Sugerir C&oacute;digo </a>';
		$edit->depto = new inputField("C&oacute;digo Departamento", "depto");
		$edit->depto->mode="autohide";
		$edit->depto->size=5;
		$edit->depto->maxlength=2;
		$edit->depto->rule ="trim|strtoupper|required|callback_chexiste";
		$edit->depto->append($sugerir);
		$edit->depto->append($ultimo);

		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size =35;
		$edit->descrip->maxlength=30;
		$edit->descrip->rule ="trim|required|strtoupper";
		
		$edit->tipo = new dropdownField("Tipo","tipo");
		$edit->tipo->style='width:140px;';
		$edit->tipo->option("I","Inventario" );
		$edit->tipo->option("G","Gasto"  );
		
		$edit->cu_inve =new inputField("Cuenta Inventario", "cu_inve");
		$edit->cu_inve->size = 18;
		$edit->cu_inve->maxlength=15;
		$edit->cu_inve->rule ="trim|callback_chcuentac";
		$edit->cu_inve->append($bcu_inve);
		
		$edit->cu_cost =new inputField("Cuenta Costo", "cu_cost");
		$edit->cu_cost->size = 18;
		$edit->cu_cost->maxlength=15;
		$edit->cu_cost->rule ="trim|callback_chcuentac";
		$edit->cu_cost->append($bcu_cost);
		
		$edit->cu_venta  =new inputField("Cuenta Venta", "cu_venta");
		$edit->cu_venta->size =18;
		$edit->cu_venta->maxlength=15;
		$edit->cu_venta->rule ="trim|callback_chcuentac";
		$edit->cu_venta->append($bcu_venta);
		
		$edit->cu_devo = new inputField("Cuenta Devoluci&oacute;n","cu_devo");
		$edit->cu_devo->size = 18;
		$edit->cu_devo->maxlength=15;
		$edit->cu_devo->rule ="trim|callback_chcuentac";
		$edit->cu_devo->append($bcu_devo);
    
		$edit->buttons("modify","delete", "save", "undo", "back");
		$edit->build();
 
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Departamentos</h1>";        
		$data["head"]    = script("jquery.pack.js").$this->rapyd->get_head();//script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").
		$this->load->view('view_ventanas', $data);  
	}
	
	function _post_insert($do){
		$codigo=$do->get('depto');
		$nombre=$do->get('descrip');
		logusu('dpto',"DEPARTAMENTO $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('depto');
		$nombre=$do->get('descrip');
		logusu('dpto',"DEPARTAMENTO $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('depto');
		$nombre=$do->get('descrip');
		logusu('dpto',"DEPARTAMENTO $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('depto');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM dpto WHERE depto='$codigo'");
		if ($chek > 0){
			$depto=$this->datasis->dameval("SELECT descrip FROM dpto WHERE depto='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el departamento $depto");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	
	function _pre_del($do) {
		$codigo=$do->get('depto');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM line WHERE depto='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='El departamento contiene lineas, por ello no puede ser eliminado. Elimine primero todas las l&iacute;neas que pertenezcan a este departamento';
			return False;
		}
		return True;
	}

	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT depto FROM dpto WHERE depto<95 ORDER BY depto DESC");
		echo $ultimo;
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"depto","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('dpto');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		if ( count($sort) == 0 ) $this->db->order_by( 'depto', 'asc' );
		
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $this->db->count_all('dpto');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$depto = $campos['depto'];

		if ( !empty($depto) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM dpto WHERE depto='$depto'") == 0)
			{
				$mSQL = $this->db->insert_string("dpto", $campos );
				$this->db->simple_query($mSQL);
				logusu('dpto',"DEPARTAMENTO $depto CREADO");
				echo "{ success: true, message: 'Departamento Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un Departamento con ese Codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe un Departamento con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$depto = $campos['depto'];
		unset($campos['depto']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("dpto", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('dpto',"DEPARTAMENTOS DE INVENTARIO $depto ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Grupo Modificado -> ".$data['data']['depto']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$depto = $campos['depto'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM line WHERE depto='$depto'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM gitser WHERE departa='$depto'");

		if ($chek > 0){
			echo "{ success: false, message: 'Departamento, con movimiento, no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM dpto WHERE depto='$depto'");
			logusu('dpto',"DEPARTAMENTO $depto ELIMINADO");
			echo "{ success: true, message: 'Departamento Eliminado'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function dptoextjs(){
		$encabeza='DEPARTAMENTOS DE INVENTARIO';
		$listados= $this->datasis->listados('dpto');
		$otros=$this->datasis->otros('dpto', 'dpto');

		$urlajax = 'inventario/dpto/';
		$variables = "
		var mcuentaV = ''
		var mcuentaI = ''
		var mcuentaC = ''
		var mcuentaD = ''
		";
		$funciones = "
function ftipo(val){
	if ( val == 'I'){
		return 'Inventario';
	} else if ( val == 'G'){
		return  'Gasto';
	}
}
		";

		$valida = "
		{ type: 'length', field: 'depto',   min: 1 },
		{ type: 'length', field: 'descrip', min: 1 }
		";
		
		$columnas = "
			{ header: 'Depto',       width: 50, sortable: true, dataIndex: 'depto',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Descripcion', width:200, sortable: true, dataIndex: 'descrip',  field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Tipo',        width: 90, sortable: true, dataIndex: 'tipo',     field: { type: 'textfield' }, filter: { type: 'string' }, renderer: ftipo },
			{ header: 'Cta. Venta',  width:100, sortable: true, dataIndex: 'cu_venta', field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Cta. Inve.',  width:100, sortable: true, dataIndex: 'cu_inve',  field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Cta. Costo',  width:100, sortable: true, dataIndex: 'cu_cost',  field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Cta. Devo.',  width:100, sortable: true, dataIndex: 'cu_devo',  field: { type: 'textfield' }, filter: { type: 'string' }},
	";

		$campos = "'id','tipo','depto','descrip','cu_venta','cu_inve','cu_cost','cu_devo'";
		
		$camposforma = "
							{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							items: [
									{ fieldLabel: 'Depto',       name: 'depto',   width:120, labelWidth: 70, xtype: 'textfield', id: 'depto' },
									{ fieldLabel: 'Descripcion', name: 'descrip', width:400, labelWidth: 70, xtype: 'textfield' },
									{ fieldLabel: 'Tipo',        name: 'tipo',    width:200, xtype: 'combo', store: [['G','Gastos'],['I','Inventario']] },
								]
							},{
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{
										xtype: 'combo',
										fieldLabel: 'Cuenta Ventas ',
										labelWidth:100,
										name: 'cu_venta',
										id:   'cuenta1',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: cplaStoreV,
										width: 400
									},
									{
										xtype: 'combo',
										fieldLabel: 'Cuenta Inventario',
										labelWidth:100,
										name: 'cu_inve',
										id:   'cuenta2',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: cplaStoreI,
										width: 400
									},
									{
										xtype: 'combo',
										fieldLabel: 'Cuenta de Costo',
										labelWidth:100,
										name: 'cu_cost',
										id:   'cuenta3',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: cplaStoreC,
										width: 400
									},
									{
										xtype: 'combo',
										fieldLabel: 'Cta.de Devolucion',
										labelWidth:100,
										name: 'cu_devo',
										id:   'cuenta4',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: cplaStoreD,
										width: 400
									}
								]
							}
		";

		$titulow = 'Departamentos';

		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 330,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						
						if (registro) {
							mcuentaV  = registro.data.cu_venta;
							cplaStoreV.proxy.extraParams.cu_venta   = mcuentaV ;
							cplaStoreV.load({ params: { 'cuenta': registro.data.cu_venta, 'origen': 'beforeform' } });
							
							mcuentaI  = registro.data.cu_inve;
							cplaStoreI.proxy.extraParams.cu_inve   = mcuentaI ;
							cplaStoreI.load({ params: { 'cuenta': registro.data.cu_inve, 'origen': 'beforeform' } });
							
							mcuentaC  = registro.data.cu_cost;
							cplaStoreC.proxy.extraParams.cu_cost   = mcuentaC ;
							cplaStoreC.load({ params: { 'cuenta': registro.data.cu_cost, 'origen': 'beforeform' } });

							mcuentaD  = registro.data.cu_devo;
							cplaStoreD.proxy.extraParams.cu_devo   = mcuentaD ;
							cplaStoreD.load({ params: { 'cuenta': registro.data.cu_devo, 'origen': 'beforeform' } });

							form.loadRecord(registro);
						} else {
							mcuentaV  = '';
							mcuentaI  = '';
							mcuentaC  = '';
							mcuentaD  = '';
						}
					}
				}
";

		$stores = "
var cplaStoreV = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,autoSync: false,pageSize: 50,
	pruneModifiedRecords: true,totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'contabilidad/cpla/cplabusca',
		extraParams: {  'cuenta': mcuentaV, 'origen': 'store' },
		reader: {type: 'json',	totalProperty: 'results',root: 'data'
		}
	},
	method: 'POST'
});

var cplaStoreI = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,autoSync: false,pageSize: 50,
	pruneModifiedRecords: true,totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'contabilidad/cpla/cplabusca',
		extraParams: {  'cuenta': mcuentaI, 'origen': 'store' },
		reader: {type: 'json',	totalProperty: 'results',root: 'data'
		}
	},
	method: 'POST'
});

var cplaStoreC = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,autoSync: false,pageSize: 50,
	pruneModifiedRecords: true,totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'contabilidad/cpla/cplabusca',
		extraParams: {  'cuenta': mcuentaC, 'origen': 'store' },
		reader: {type: 'json',	totalProperty: 'results',root: 'data'
		}
	},
	method: 'POST'
});

var cplaStoreD = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,autoSync: false,pageSize: 50,
	pruneModifiedRecords: true,totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'contabilidad/cpla/cplabusca',
		extraParams: {  'cuenta': mcuentaD, 'origen': 'store' },
		reader: {type: 'json',	totalProperty: 'results',root: 'data'
		}
	},
	method: 'POST'
});


		";

		$features = "features: [ filters],";
		$filtros = "var filters = { ftype: 'filters', encode: 'json', local: false }; ";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['stores']      = $stores;
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		
		$data['title']  = heading('Departamentos');
		$this->load->view('extjs/extjsven',$data);
	}
}
?>
