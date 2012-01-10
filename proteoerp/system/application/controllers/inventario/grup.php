<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Grup extends validaciones {

	function grup(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(305,1);
		$esta = $this->datasis->dameval( "SHOW columns FROM grup WHERE Field='status'" );
		if ( empty($esta) ) $this->db->simple_query("ALTER TABLE grup ADD status CHAR(1) DEFAULT='A' ");
		$this->db->simple_query("UPDATE grup SET status='A' WHERE status IS NULL ");
	
	
	}

	function index(){
		redirect("inventario/grup/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		function blanco($num){
			if(empty($num)||$num==0){
			 return '';
			}else{
				return number_format($num,2,',','.');
			}
		}

		$filter = new DataFilter("Filtro de Grupo de Inventario");

		$filter->db->select("a.grupo AS grupo, a.nom_grup AS nom_grup, a.comision AS comision,a.margen AS margen,a.margenc AS margenc, b.descrip AS linea,c.descrip AS depto, a.cu_inve AS cu_inve, a.cu_venta AS cu_venta, a.cu_cost AS cu_cost, a.cu_devo AS cu_devo");
		$filter->db->from("grup AS a");
		$filter->db->join("line AS b","a.linea=b.linea");
		$filter->db->join("dpto AS c","b.depto=c.depto");

		$filter->grupo = new inputField("Grupo","grupo");
		$filter->grupo->size=20;
		$filter->grupo->group = 'UNO';

		$filter->nombre = new inputField("Descripci&oacute;n","nom_grup");
		$filter->nombre->size=20;
		$filter->nombre->group = 'UNO';

		$filter->linea = new inputField("L&iacute;nea","b.descrip");
		$filter->linea->size=20;
		$filter->linea->group = 'DOS';

		$filter->depto = new inputField("Departamento","c.descrip");
		$filter->depto->size=20;
		$filter->depto->group = 'DOS';

		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');


		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";
		$mtool .= "<td>&nbsp;<a href='".base_url()."inventario/grup/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";
		$mtool .= "</tr></table>";

		$uri = anchor('inventario/grup/dataedit/show/<raencode><#grupo#></raencode>','<#grupo#>');
		$uri_2 = anchor('inventario/grup/dataedit/create/<raencode><#grupo#></raencode>','Duplicar');

		$grid = new DataGrid("Lista de Grupos de Inventario");
		$grid->order_by("grupo","asc");
		$grid->per_page = 60;

		$grid->column_sigma("Depto",           		"depto",     "", "width: 40, frozen: true");
		$grid->column_sigma("Linea",                    "linea",     '',      "width: 40, frozen: true");
		$grid->column_sigma("Grupo", 		        "grupo",     '',      'width: 50,  frozen: true, renderer: grupver ' );
		$grid->column_sigma("Descripci&oacute;n",       "nom_grup",  '',      "width: 200, editor: { type: 'text' }" );
		$grid->column_sigma("Comisi&oacute;n",          "comision",  'float', "width: 80,  align: 'right', editor: { type: 'text' }");
		$grid->column_sigma("Mrgn/Venta",             "margen" ,   'float', "width: 80,  align: 'right', editor: { type: 'text' }");
		$grid->column_sigma("Mrgn/Compra",            "margenc" ,  'float', "width: 80,  align: 'right', editor: { type: 'text' }");
		$grid->column_sigma("Cuenta Inventario",        "cu_inve",   '',      "align: 'left'");
		$grid->column_sigma("Cuenta Costo",             "cu_cost",   '',      "align: 'left'");
		$grid->column_sigma("Cuenta Venta",             "cu_venta",  '',      "align: 'left'");
		$grid->column_sigma("Cuenta Devoluci&oacute;n", "cu_devo",   '',      "align: 'left'");

		$sigmaA     = $grid->sigmaDsConfig();
		$dsOption   = $sigmaA["dsOption"];
		$grupver    = "
function grupver(value, record, columnObj, grid, colNo, rowNo){
	var url = '';
	url = '<a href=\"#\" onclick=\"window.open(\'".base_url()."inventario/grup/dataedit/show/'+value+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
	url = url +value+'</a>';
	return url;	
}

";
		$colsOption = $sigmaA["colsOption"];
		$gridOption = "
var gridOption={
	id : 'grid1',
	loadURL : '".base_url()."inventario/grup/controlador',
	width: 550,
	height: 400,
	container : 'grid1_container',
	replaceContainer: true,
	dataset : dsOption ,
	columns : colsOption,
	allowCustomSkin: true,
	skin: 'vista',
	pageSize: ".$grid->per_page.",
	pageSizeList: [30,60,90,120],
	toolbarPosition : 'bottom',
	toolbarContent: 'nav | pagesize | reload print excel pdf filter state',
	afterEdit: guardar,
	//showGridMenu : true,
	clickStartEdit: false,
	remotePaging: true,
	remoteSorting: true,
	remoteFilter: true,
	autoload: true
};

function guardar(value, oldValue, record, col, grid) {
	var murl='';
	murl = '".base_url()."/inventario/grup/grupmodi/'+record['grupo']+'/'+col.id+'/'+encodeURIComponent(value);
	if ( value != oldValue ) {
		$.ajax({
			url: murl,
			context: document.body,
			//success: function(m){ alert('Guardado '+m);}
		});

	}
};

var mygrid=new Sigma.Grid(gridOption);
Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );
";		
		$SigmaCont = "<center><div id=\"grid1_container\" style=\"width:550px;height:400px;\"></div></center>";
		$grid->add("inventario/grup/dataedit/create");
		$grid->build('datagridSG');
		//echo $grid->db->last_query();


		$data['style']  = style("redmond/jquery-ui.css");
		$data['style'] .= style('gt_grid.css');
		$data['style'] .= style('skin/vista/skinstyle.css');

		$data["script"]  = script("jquery.js");
		$data['script'] .= script("gt_msg_es.js");
		$data['script'] .= script("gt_grid_all.js");
		$data['script'] .= "<script type=\"text/javascript\" >\n";
		$data['script'] .= $dsOption.$grupver."\n";
		$data['script'] .= $colsOption."\n";
		$data['script'] .= $gridOption;

		//$data['script'] .= "$(function() { $(\"p\").text(\"Meco\") } );";

		$data['script'] .= "\n</script>";


		$data['content'] = $mtool.$SigmaCont;  //$grid->output;
		
		//$data['filtro']  = ''; //$filter->output;
		$data['title']   = "<h1>Grupos de Inventario</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	// sigma grid
	function controlador(){
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
					$sortField = "grupo";
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
				$mSQL = "SELECT count(*) FROM grup WHERE $filter grupo IS NOT NULL";
				$totalRec = $this->datasis->dameval($mSQL);
 
 
				//make sure pageNo is inbound
				if($pageNo<1||$pageNo>ceil(($totalRec/$pageSize))){
					$pageNo = 1;
				}
 
				$mSQL = "SELECT grupo, nom_grup, comision, margen, margenc, depto, linea, cu_inve, cu_cost, cu_venta, cu_devo ";
				$mSQL .= "FROM grup WHERE $filter grupo IS NOT NULL ORDER BY ".$sortField." ".$sortOrder." LIMIT ".($pageNo - 1)*$pageSize.", ".$pageSize;
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
			// no hay _gt_json
			/*
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
			$mSQL = "SELECT grupo, nom_grup, comision, margen, margenc, depto, linea, cu_inve, cu_cost, cu_venta, cu_devo";
			$mSQL .=" FROM grup ORDER BY grupo ASC LIMIT ".($pageNo - 1)*$pageSize.", ".$pageSize;

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
			*/
			echo '{data : []}';
		}
	}

	function grupmodi(){
		$valor = $this->uri->segment($this->uri->total_segments());
		$campo = $this->uri->segment($this->uri->total_segments()-1);
		$grupo = $this->uri->segment($this->uri->total_segments()-2);
		$mSQL = "UPDATE grup SET ".$campo."='".addslashes($valor)."' WHERE grupo='".$grupo."' ";
		$this->db->simple_query($mSQL);
		echo "$valor $campo $grupo";
	}

	function dataedit($status='',$id=''){
		$this->rapyd->load("dataobject","dataedit");

		$qformato=$this->qformato=$this->datasis->formato_cpla();
		$link=site_url('inventario/grup/ultimo');
		$link2=site_url('inventario/common/sugerir_grup');

		$script ='
			$(function() {
				$(".inputnum").numeric(".");
			});
			
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
								$("#grupo").val(msg);
							}
							else{
								alert("No es posible generar otra sugerencia. Coloque el c&oacute;digo manualmente");
							}
						}
					});
			}
	
			function get_linea(){
				$.ajax({
					type: "POST",
					url: "'.site_url('reportes/sinvlineas').'",
					data: $("#dpto").serialize(),
					success: function(msg){
						$("#td_linea").html(msg);
					},
					error: function(msg){
						alert("Error en la comunicaci&oacute;n");
					}
				});
			}';

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

		$do = new DataObject("grup");
		$do->set('tipo', 'I');
		if($status=="create" && !empty($id)){
			$do->load($id);
			$do->set('grupo', '');
		}

		$edit = new DataEdit("Grupos de Inventario",$do);
		$edit->back_url = site_url("inventario/grup/filteredgrid");
		$edit->script($script, "modify");
		$edit->script($script, "create");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->depto = new dropdownField("Departamento", "dpto");
		$edit->depto->db_name='depto';
		$edit->depto->rule ="required";
		$edit->depto->onchange = "get_linea();";
		$edit->depto->option("","Seleccionar");
		$edit->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");

		$edit->linea = new dropdownField("L&iacute;nea","linea");
		$edit->linea->rule ="required";
		if($edit->_status=='modify' or $edit->_status=='show' or $edit->_status=='idle' or $edit->_status=='create'){
			$depto = ($this->input->post('dpto')===FALSE) ? $edit->_dataobject->get("depto") : $this->input->post('dpto');
			$edit->linea->options("SELECT linea, descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$edit->linea->option("","Seleccione un Departamento");
		}

		$ultimo='<a href="javascript:ultimo();" title="Consultar ultimo c&oacute;digo ingresado"> Consultar ultimo c&oacute;digo</a>';
		$sugerir='<a href="javascript:sugerir();" title="Sugerir un C&oacute;digo aleatorio">Sugerir C&oacute;digo </a>';
		$edit->grupo =  new inputField("C&oacute;digo Grupo", "grupo");
		$edit->grupo->mode="autohide";
		$edit->grupo->maxlength=4;
		$edit->grupo->size =6;
		$edit->grupo->rule ="trim|strtoupper|required|callback_chexiste";
		$edit->grupo->append($sugerir);
		$edit->grupo->append($ultimo);

		$edit->nom_grup =  new inputField("Nombre del Grupo", "nom_grup");
		$edit->nom_grup->size = 40;
		$edit->nom_grup->maxlength=40;
		$edit->nom_grup->rule = "trim|strtoupper|required";

		//$edit->tipo = new dropdownField("Tipo","tipo");
		//$edit->tipo->style='width:100px;';
		//$edit->tipo->option("I","Inventario" );
		//$edit->tipo->option("G","Gasto"  );

		$edit->comision = new inputField("Comisi&oacute;n. %", "comision");
		$edit->comision->size = 18;
		$edit->comision->maxlength=10;
		$edit->comision->css_class='inputnum';
		$edit->comision->rule='trim|callback_chporcent|numeric|callback_positivo';

		$edit->status = new dropdownField("Status", "status");
		$edit->status->db_name=("status");
		$edit->status->option("A","Activo");
		$edit->status->option("B","Bloqueado");
		$edit->status ->style='width:120px;';


		$edit->margen = new inputField("Margen de Venta", "margen");
		$edit->margen->size = 18;
		$edit->margen->maxlength=10;
		$edit->margen->css_class='inputnum';
		$edit->margen->group='Margenes';
		$edit->margen->rule='trim|callback_chporcent|callback_positivo';

		$edit->margenc = new inputField("Margen de Compra", "margenc");
		$edit->margenc->size = 18;
		$edit->margenc->maxlength=10;
		$edit->margenc->css_class='inputnum';
		$edit->margenc->group='Margenes';
		$edit->margenc->rule='trim|callback_chporcent|numeric|callback_positivo';


		$edit->cu_inve =new inputField("Cuenta Inventario", "cu_inve");
		$edit->cu_inve->size = 18;
		$edit->cu_inve->maxlength=15;
		$edit->cu_inve->rule ="trim|callback_chcuentac";
		$edit->cu_inve->append($bcu_inve);
		$edit->cu_inve->group='Cuentas contables';

		$edit->cu_cost =new inputField("Cuenta Costo", "cu_cost");
		$edit->cu_cost->size = 18;
		$edit->cu_cost->maxlength=15;
		$edit->cu_cost->rule ="trim|callback_chcuentac";
		$edit->cu_cost->append($bcu_cost);
		$edit->cu_cost->group='Cuentas contables';

		$edit->cu_venta  =new inputField("Cuenta Venta", "cu_venta");
		$edit->cu_venta->size =18;
		$edit->cu_venta->maxlength=15;
		$edit->cu_venta->rule ="trim|callback_chcuentac";
		$edit->cu_venta->append($bcu_venta);
		$edit->cu_venta->group='Cuentas contables';

		$edit->cu_devo = new inputField("Cuenta Devoluci&oacute;n","cu_devo");
		$edit->cu_devo->size = 18;
		$edit->cu_devo->maxlength=15;
		$edit->cu_devo->rule ="trim|callback_chcuentac";
		$edit->cu_devo->append($bcu_devo);
		$edit->cu_devo->group='Cuentas contables';

		$edit->status = new dropdownField("Status", "status");
		$edit->status->db_name=("status");
		$edit->status->option("A","Activo");
		$edit->status->option("B","Bloqueado");
		$edit->status ->style='width:120px;';

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$link=site_url('inventario/grup/get_linea');


		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";
		$mtool .= "<td>&nbsp;<a href='javascript:exento(\"".$edit->grupo->value."\")'>";
		$mtool .= img(array('src' => 'images/casa.png', 'alt' => 'Exonerar Productos', 'title' => 'Exonerar Productos','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";
		$mtool .= "</tr></table>";

		$script = '
<script type="text/javascript">
function exento(mgrupo){
	$.prompt("Exonerar Productos del Grupo "+mgrupo, {
		callback: function(v,m,f){
			if ( v == 1 ) {
				$.ajax({
					url: "'.base_url().'inventario/grup/sinvexento/"+mgrupo+"/E",
					global: false,
					async: false,
					success: function(sino)  { $.prompt( "Marcaje exitoso "); }
				});

			} else if ( v == 2 ) {
				$.ajax({
					url: "'.base_url().'inventario/grup/sinvexento/"+mgrupo+"/N",
					global: false,
					async: false,
					success: function(sino)  { $.prompt( "Finalizado el desmarcaje "); }
				});
			};
		 },
		buttons:{ Marcar: 1, Desmarcar: 2, Cancelar: 3 }
	});
/*
			if ( v == 1 ) {
				$.ajax({
					url: "'.base_url().'inventario/grup/exento/"+mgrupo+"/S",
					global: false,
					async: false,
					success: function(sino)  { .$prompt( "Respuesta:"+sino); }
				});
			} else if ( v == 2 ) {
				$.ajax({
					url: "'.base_url().'inventario/grup/exento/"+mgrupo+"/N",
					global: false,
					async: false,
					success: function(sino)  { .$prompt( "Respuesta:"+sino); }
				});
			};

*/

};

</script>
';

		$data['content'] = $mtool.$edit->output;

		$data["script"]  = script("jquery.js");
		$data["script"] .= script("jquery.alerts.js");
		$data["script"] .= script("jquery-impromptu.js");
		$data['script'] .= $script;

		$data['style']	 = style("jquery.alerts.css");
		$data['style']	.= style("impromptu.css");

		
		$data['title']   = "<h1>Grupos de Inventario</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_insert($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grup',"GRUPO DE INVENTARIO $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grup',"GRUPO DE INVENTARIO $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grup',"GRUPO DE INVENTARIO $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('grupo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM grup WHERE grupo='$codigo'");
		if ($chek > 0){
			$grupo=$this->datasis->dameval("SELECT nom_grup FROM grup WHERE grupo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el grupo $grupo");
			return FALSE;
		}else {
			return TRUE;
		}
	}

	function _pre_del($do) {
		$codigo=$do->get('grupo');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE grupo='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='El grupo contiene productos, por ello no puede ser eliminado. Elimine primero todos los productos que pertenezcan a este grupo';
			return False;
		}
		return True;
	}

	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT grupo FROM grup ORDER BY grupo DESC");
		echo $ultimo;
	}

	function positivo($valor){
		if ($valor < 0){
			$this->validation->set_message('positivo',"El campo comisi&oacute;n debe ser positivo");
			return FALSE;
		}
		return TRUE;
	}

	function instala(){
		$mSQL="ALTER TABLE `grup`  
				ADD COLUMN `margen` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `comision`,
				ADD COLUMN `margenc` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `margen`";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `grup` ADD COLUMN `status` CHAR(1) NULL DEFAULT NULL";
		$this->db->simple_query($mSQL);
	}

	//***************************
	//
	// Marca Productos que se pueden vender sin iva
	//
	function sinvexento() {
		$grupo  = $this->uri->segment($this->uri->total_segments()-1);
		$sino   = $this->uri->segment($this->uri->total_segments());
		$this->db->simple_query("UPDATE sinv SET exento='".$sino."' WHERE grupo='$grupo'");
		memowrite("UPDATE sinv SET exento='".$sino."' WHERE grupo='$grupo'","marcagrup");
		logusu("SINV","Productos marcados por Grupos $grupo ");
		echo "Productos Marcados '$sino'";
	}
}
?>
