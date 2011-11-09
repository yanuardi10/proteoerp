<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//concepto
class Conc extends validaciones{

	function conc(){
		parent::Controller(); 
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
	}

	function index(){
		$this->datasis->modulo_id(704,1);
		//redirect("nomina/conc/filteredgrid");
		redirect("nomina/conc/extgrid");
	}

	function extgrid(){
		$script = $this->concextjs();
		$data["script"] = $script;
		$data['title']  = heading('Nomina');
		$this->load->view('extjs/pers',$data);
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por Descripci&oacute;n", 'conc');
		
		$filter->concepto = new inputField("Concepto", "concepto");
		$filter->concepto->size = 5;
		
		$filter->descrip  = new inputField("Descripci&oacute;n", "descrip");
		
		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');
		
		$uri = anchor('nomina/conc/dataedit/show/<#concepto#>','<#concepto#>');
		$uri_2  = anchor('nomina/conc/dataedit/modify/<#concepto#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
		
		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."nomina/conc/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";

		$grid = new DataGrid($mtool);
		$grid->order_by("concepto","asc");
		$grid->per_page = 30;

		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("Concepto",$uri,'concepto');
		$grid->column_orderby("Tipo","tipo",'tipo');
		$grid->column_orderby("Descripci&oacute;n","descrip",'descrip');
		$grid->column_orderby("Tipoa","tipoa",'tipoa');
		$grid->column_orderby("Aplica","aplica",'aplica');
		$grid->column_orderby("Liquida","liquida",'liquida');
		$grid->column("F&oacute;rmula","formula");
		
		//$grid->add("nomina/conc/dataedit/create");
		$grid->build('datagridST');
		
				//************ SUPER TABLE ************* 
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
    width: 640px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************
	
		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['extras']  = $extras;
		
		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		
		$data['title']  = heading('Conceptos');
		$data['script'] = script('jquery.js');
		$data["script"].= script('superTables.js');
		$data['head']   = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function getctade($tipoa=NULL){
		$this->rapyd->load("fields");
		$uadministra = new dropdownField("ctade", "ctade");
		$uadministra->status = "modify";
		$uadministra->style ="width:400px;";
		//echo 'de nuevo:'.$tipoa;
		if ($tipoa!==false){
		if($tipoa=='P'){
					$uadministra->options("SELECT proveed,nombre FROM sprv ORDER BY proveed");
			}else{
				if($tipoa=='G'){
					$uadministra->options("SELECT codigo,descrip FROM mgas ORDER BY codigo");
				}else{
					$uadministra->options("SELECT cliente,nombre FROM sprv ORDER BY cliente");
				}
			}
		}else{
 				$uadministra->option("Seleccione un opcion");
		}
		$uadministra->build(); 
		echo $uadministra->output;
	}
	function dataedit(){
		$this->rapyd->load("dataobject","dataedit2");
			
		$qformato=$this->qformato=$this->datasis->formato_cpla();
		
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
		
		$bcuenta  =$this->datasis->p_modbus($modbus ,'cuenta');
		$bcontra  =$this->datasis->p_modbus($modbus ,'contra');
		
		$edit = new DataEdit2("Conceptos", "conc");
		$edit->back_url = site_url("nomina/conc/filteredgrid");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->concepto = new inputField("Concepto", "concepto");
		$edit->concepto->rule = "required|callback_chexiste";
		$edit->concepto->mode = "autohide";
		$edit->concepto->maxlength= 4;
		$edit->concepto->size = 7;
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style ="width:100px;";
		$edit->tipo->option("","");
		$edit->tipo->options(array("A"=> "Asignaci&oacute;n","O"=>"Otros","D"=> "Deducci&oacute;n"));
		
		$edit->descrip =  new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size =45;
		$edit->descrip->maxlength=35;
		$edit->descrip->rule = "strtoupper|required";
		
		$edit->grupo = new inputField("Grupo", "grupo");
		$edit->grupo->size =7;
		$edit->grupo->maxlength=4;
		
		$edit->encab1 = new inputField("Encabezado 1", "encab1");
		$edit->encab1->size = 22;
		$edit->encab1->maxlength=12;
		
		$edit->encab2 =   new inputField("Encabezado 2&nbsp;", "encab2");
		$edit->encab2->size = 22;
		$edit->encab2->maxlength=12;
				
		$edit->formula = new textareaField("F&oacute;rmula","formula");
		$edit->formula->rows = 4;
		$edit->formula->cols=90;
		
		$edit->cuenta = new inputField("Debe", "cuenta");
		$edit->cuenta->size =19;
		$edit->cuenta->maxlength=15;
		$edit->cuenta->group="Enlase Contable";
		$edit->cuenta->rule='callback_chcuentac';
		$edit->cuenta->append($bcuenta);
		
		$edit->contra =  new inputField("Haber", "contra"); 
		$edit->contra->size = 19;   
		$edit->contra->maxlength=15;
		$edit->contra->group="Enlase Contable";
		$edit->contra->rule='callback_chcuentac';
		$edit->contra->append($bcontra);
		
		$edit->tipod = new dropdownField ("Deudor", "tipod");
		$edit->tipod->style ="width:100px;";
		$edit->tipod->option(" "," "); 
		$edit->tipod->option("G","Gasto");
		$edit->tipod->option("C","Cliente");
		$edit->tipod->option("P","Proveedor");
		$edit->tipod->onchange = "get_ctaac();";
		$edit->tipod->group="Enlase Administrativo";

		$edit->ctade = new dropdownField("Cuenta Deudor", "ctade");
		$edit->ctade->style ="width:400px;";
		$edit->ctade->group="Enlase Administrativo";

		$tipod  =$edit->getval("tipod");
		if($tipod=='P'){
				$edit->ctade->options("SELECT proveed,CONCAT_WS(' ',proveed,nombre)a FROM sprv ORDER BY proveed");
		}else{
			if($tipod=='G'){
				$edit->ctade->options("SELECT codigo,CONCAT_WS(' ',codigo,descrip)a FROM mgas ORDER BY codigo");
			}else{
				$edit->ctade->options("SELECT cliente,CONCAT_WS(' ',proveed,nombre)a FROM sprv ORDER BY cliente");
			}
		}
		
		$edit->tipoa = new dropdownField ("Acreedor", "tipoa");  
		$edit->tipoa->style ="width:100px;";
		$edit->tipoa->option(" "," "); 
		$edit->tipoa->option("G","Gasto");    
		$edit->tipoa->option("C","Cliente");  
		$edit->tipoa->option("P","Proveedor");
		$edit->tipoa->group="Enlase Administrativo";
		$edit->tipoa->onchange = "get_ctade();";
		
		$edit->ctaac =   new dropdownField("Cuenta Acreedor", "ctaac"); 
		$edit->ctaac->style ="width:400px;";     
		$edit->ctaac->group="Enlase Administrativo";
		$tipod  =$edit->getval("tipoa");
		if($tipod=='P'){
				$edit->ctaac->options("SELECT proveed,CONCAT_WS(' ',proveed,nombre)a FROM sprv ORDER BY proveed");
		}else{
			if($tipod=='G'){
				$edit->ctaac->options("SELECT codigo,CONCAT_WS(' ',codigo,descrip)a FROM mgas ORDER BY codigo");
			}else{
				$edit->ctaac->options("SELECT cliente,CONCAT_WS(' ',proveed,nombre)a FROM sprv ORDER BY cliente");
			}
		}
			
		$edit->aplica =   new dropdownField("Aplica para liquidacion", "liquida"); 
		$edit->aplica->style ="width:50px;";     
		$edit->aplica->option("S","S");
		$edit->aplica->option("N","N"); 
    			
		$edit->buttons("modify", "save", "undo", "back","delete");
		$edit->build();
		
		$link=site_url('nomina/conc/getctade');
		$link2=site_url('nomina/conc/getctade');
	$data['script']  =<<<script
		<script type="text/javascript" charset="utf-8">
		function get_ctade(){
				var tipo=$("#tipoa").val();
				$.ajax({
					url: "$link"+'/'+tipo,
					success: function(msg){
						$("#td_ctade").html(msg);								
					}
				});
									//alert(tipo);
			} 
		function get_ctaac(){
				var tipo=$("#tipod").val();
				$.ajax({
					url: "$link2"+'/'+tipo,
					success: function(msg){
						$("#td_ctaac").html(msg);
					}
				});
			} 	
		</script>
script;
	
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Conceptos</h1>";        
		$data["head"]    = $this->rapyd->get_head();                                                                                         
		$data["head"]   .='<script src="'.base_url().'assets/default/script/jquery.js'.'" type="text/javascript" charset="utf-8"></script>';
		$this->load->view('view_ventanas', $data);  
	}

	function _post_insert($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('conc',"CONCEPTO $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('conc',"CONCEPTO $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('concepto');
		$nombre=$do->get('descrip');
		logusu('conc',"CONCEPTO $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('concepto');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM conc WHERE concepto='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT descrip FROM conc WHERE concepto='$codigo'");
			$this->validation->set_message('chexiste',"El concepto $codigo nombre $nombre ya existe");
			return FALSE;
		}else {
  			return TRUE;
		}
	}

	function instalar(){
		$mSQL="ALTER TABLE conc ADD PRIMARY KEY (concepto);";
		$this->db->simple_query($mSQL);	
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : 'contrato';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;
		
		$where = "concepto IS NOT NULL ";

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
				//Dummy Where. 
				$where = "concepto IS NOT NULL ";
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
		$this->db->select('concepto, tipo, descrip, aplica, grupo, encab1, encab2, formula, tipod, ctade, tipoa, ctaac, if(liquida="S", "true","false") liquida, id');
		//$this->db->select('*');

		$this->db->from('conc');
		//$this->db->join('noco', 'pers.contrato=noco.codigo');

		if (strlen($where)>1){
			$this->db->where($where);
		}
		
		$sort = json_decode($sort, true);

		if ( count($sort) == 0 ) $this->db->order_by( 'concepto', 'asc' );
		
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $this->db->count_all('conc');

		$arr = array();
		foreach ($query->result_array() as $row)
		{
			$meco = array();
			foreach( $row as $idd=>$campo ) {
				$meco[$idd] = utf8_encode($campo);
			}
			$arr[] = $meco;
		}
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}

	function conccta() {
		$start    = isset($_REQUEST['start'])  ? $_REQUEST['start']  :  0;
		$limit    = isset($_REQUEST['limit'])  ? $_REQUEST['limit']  : 25;
		$tipo     = isset($_REQUEST['tipo'])   ? $_REQUEST['tipo']   : 'P';
		$cuenta   = isset($_REQUEST['cuenta']) ? $_REQUEST['cuenta'] : '';
		$semilla  = isset($_REQUEST['query'])  ? $_REQUEST['query']  : '';

		$mSQL = '';
	
		if ( $tipo == 'G' ) {
			$mSQL = "SELECT codigo item, CONCAT(codigo, ' ', descrip) valor FROM mgas WHERE tipo='G' ";
			if ( strlen($semilla)>0 ){
				$mSQL .= " AND (codigo LIKE '$semilla%' OR descrip LIKE '%$semilla%') ";
			} else {
				if ( strlen($cuenta)>0 ) $mSQL .= " AND (codigo LIKE '$cuenta%' OR descrip LIKE '%$cuenta%') ";
			}
			$mSQL .= "ORDER BY descrip ";
			$results = $this->db->count_all('mgas');
		} elseif ( $tipo == 'P' ) {
			$mSQL = "SELECT proveed item, CONCAT(proveed, ' ', nombre) valor FROM sprv WHERE tipo<>'0' ";
			if ( strlen($semilla)>0 ){
				$mSQL .= " AND ( proveed LIKE '$semilla%' OR nombre LIKE '%$semilla%' OR  rif LIKE '%$semilla%') ";
			} else {
				if ( strlen($cuenta)>0 ) $mSQL .= " AND (proveed LIKE '$cuenta%' OR nombre LIKE '%$cuenta%' OR  rif LIKE '%$cuenta%') ";
			}
			$mSQL .= "ORDER BY nombre ";
			$results = $this->db->count_all('sprv');
		} elseif ( $tipo == 'C' ) {
			$mSQL = "SELECT cliente item, CONCAT(cliente, ' ', nombre) valor FROM scli WHERE tipo<>'0' ";
			if ( strlen($semilla)>0 ){
				$mSQL .= " AND (cliente LIKE '$semilla%' OR nombre LIKE '%$semilla%' OR rifci LIKE '%$semilla%') ";
			} else {
				if ( strlen($cuenta)>0 ) $mSQL .= " AND (cliente LIKE '$cuenta%' OR nombre LIKE '%$cuenta%' OR rifci LIKE '%$cuenta%') ";
			}
			$mSQL .= "ORDER BY nombre ";
			$results = $this->db->count_all('scli');
		} 

		if ( empty($mSQL)) {
			echo '{success:true, message:"mSQL vacio, Loaded data", results: 0, data:'.json_encode(array()).'}';
		} else {
			$mSQL .= " limit $start, $limit ";
			$query = $this->db->query($mSQL);
			$arr = array();
			foreach ($query->result_array() as $row)
			{
				$meco = array();
				foreach( $row as $idd=>$campo ) {
					$meco[$idd] = utf8_encode($campo);
				}
				$arr[] = $meco;
			}
			echo '{success:true, message:"'.$mSQL.'", results:'. $results.', data:'.json_encode($arr).'}';
		}
	}

	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$concepto = $campos['concepto'];
		$descrip  = $campos['descrip'];

		if ($campos['liquida'] ) {
			$campos['liquida'] = 'S';
		} else {
			$campos['liquida']='N';
		}

		if ( !empty($concepto) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM conc WHERE concepto='$concepto'") == 0)
			{
				$mSQL = $this->db->insert_string("conc", $campos );
				$this->db->simple_query($mSQL);
				logusu('conc',"CONCEPTOS DE NOMINA $concepto NOMBRE  $descrip CREADO");
				echo "{ success: true, message: 'Concepto Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un contrato con ese Codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe un contrato con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$concepto = $campos['concepto'];

		unset($campos['concepto']);
		$liquida = $campos['liquida'];

		if ($campos['liquida'] ) {
			$campos['liquida'] = 'S';
		} else {
			$campos['liquida']='N';
		}

		//print_r($campos);
		$mSQL = $this->db->update_string("conc", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('conc',"CONCEPTO DE NOMINA $concepto ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Trabajador Modificado $liquida -> '".$data['data']['liquida']."}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$concepto = $campos['concepto'];
		$descrip  = $campos['descrip'];

		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM nomina WHERE concepto='$concepto'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM asig   WHERE concepto='$concepto'");

		if ($chek > 0){
			echo "{ success: false, message: 'Concepto con Movimiento no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM conc WHERE concepto='$concepto'");
			logusu('conc',"CONCEPTO $concepto $descrip ELIMINADO");
			echo "{ success: true, message: 'Concepto Eliminado'}";
		}
	}



//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function concextjs(){

		$encabeza='<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">CONCEPTOS DE NOMINA</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';
		$listados= $this->datasis->listados('conc');
		$otros=$this->datasis->otros('spre', 'conc');


		$script = "
<script type=\"text/javascript\">
var BASE_URL   = '".base_url()."';
var BASE_PATH  = '".base_url()."';
var BASE_ICONS = '".base_url()."assets/icons/';
var BASE_UX    = '".base_url()."assets/js/ext/ux';

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

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

var registro;
var urlApp = '".base_url()."';

var mtipod ;
var mtipoa ;

var mctade ;
var mctaac ;

var mxs = ((screen.availWidth/2)-400);
var mys = ((screen.availHeight/2)-300);

//coloca un boton verde cuando la va para la liquidacion
function rliquida(val) {
	if ( val == 'true' ){
		return  '<img src=\"'+urlApp+'images/S.gif\">';
	} else {
		return  '<img src=\"'+urlApp+'images/N.gif\">';
	}
};

//Column Model
var colConc = 
	[
		{ header: 'Codigo',      width:  50, sortable: true, dataIndex: 'concepto', field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Tipo',        width:  30, sortable: true, dataIndex: 'tipo',     field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Descripcion', width: 200, sortable: true, dataIndex: 'descrip',  field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Aplica',      width:  50, sortable: true, dataIndex: 'aplica',   field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Grupo',       width:  50, sortable: true, dataIndex: 'grupo',    field: { type: 'textfield' }, filter: { type: 'string' } },  
		{ header: 'Titulo1',     width: 100, sortable: true, dataIndex: 'encab1',   field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Titulo2',     width: 100, sortable: true, dataIndex: 'encab2',   field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Formula',     width: 200, sortable: true, dataIndex: 'formula',  field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'TipoDB',      width:  70, sortable: true, dataIndex: 'tipod',    field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'CuentaDB',    width:  70, sortable: true, dataIndex: 'ctade',    field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'TipoHB',      width:  70, sortable: true, dataIndex: 'tipoa',    field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'CuentaHB',    width:  70, sortable: true, dataIndex: 'ctaac',    field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Liquida',     width:  60, sortable: true, dataIndex: 'liquida',  field: { type: 'textfield' }, filter: { type: 'string' }, renderer: rliquida }
	];


// Define our data model
var Conceptos = Ext.regModel('Conceptos', {
	fields: ['id', 'concepto', 'tipo', 'descrip', 'aplica', 'grupo', 'encab1', 'encab2', 'formula', 'tipod', 'ctade', 'tipoa', 'ctaac', 'liquida'],
	validations: [
		{ type: 'length', field: 'concepto',  min: 1 },
		{ type: 'length', field: 'descrip',   min: 1 }
	],
	proxy: {
		type: 'ajax',
		noCache: false,
		api: {
			read   : urlApp + 'nomina/conc/grid',
			create : urlApp + 'nomina/conc/crear',
			update : urlApp + 'nomina/conc/modificar' ,
			destroy: urlApp + 'nomina/conc/eliminar',
			method: 'POST'
		},
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data',
			messageProperty: 'message',
			totalProperty: 'results'
		},
		writer: {
			type: 'json',
			root: 'data',
			writeAllFields: true,
			callback: function( op, suc ) {
				Ext.Msg.Alert('CallBack 1');
			}
		},
		listeners: {
			exception: function( proxy, response, operation) {
				Ext.MessageBox.show({
					title: 'EXCEPCION REMOTA',
					msg: operation.getError(),
					icon: Ext.MessageBox.ERROR,
					buttons: Ext.Msg.OK
				});
			}
		}
	}
});

//Data Store
var storeConc = Ext.create('Ext.data.Store', {
	model: 'Conceptos',
	pageSize: 50,
	autoLoad: false,
	autoSync: true,
	method: 'POST',
	listeners: {
		write: function(mr,re, op) {
			Ext.Msg.alert('Aviso','Registro Guardado ')
		}
	}
});

var ctadeStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,
	autoSync: false,
	pageSize: 50,
	pruneModifiedRecords: true,
	totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'nomina/conc/conccta',
		extraParams: { 'tipo': mtipod, 'cuenta': mctade, 'origen': 'store' },
		reader: {
			type: 'json',
			totalProperty: 'results',
			root: 'data'
		}
	},
	method: 'POST'
});
     
var ctaacStore = new Ext.data.JsonStore({
	fields: [ 'item', 'valor'],
	autoLoad: false,
	pageSize: 50,
	autoSync: false,
	pruneModifiedRecords: true,
	proxy: {
		type: 'ajax',
		url : urlApp + 'nomina/conc/conccta',
		extraParams: { 'tipo': mtipoa, 'cuenta': mctaac, 'origen': 'store' },
		reader: {
			type: 'json',
			totalProperty: 'results',
			root: 'data'
		}
	},
	method: 'POST'
});

var win;
// Main 
Ext.onReady(function(){
	function showContactForm() {
		if (!win) {
			// Create Form
			var writeForm = Ext.define('Conc.Form', {
				extend: 'Ext.form.Panel',
				alias:  'widget.writerform',
				result: function(res){	alert('Resultado');},
				requires: ['Ext.form.field.Text'],
				initComponent: function(){
					Ext.apply(this, {
						iconCls: 'icon-user',
						frame: true, 
						title: 'Concepto', 
						bodyPadding: 3,
						fieldDefaults: { labelAlign: 'right' }, 
						items: [{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield',   fieldLabel: 'Codigo',      labelWidth:50, name: 'concepto', allowBlank: false, columnWidth : 0.18, id: 'concepto' },
									{ xtype: 'combo',       fieldLabel: 'Tipo.',       labelWidth:40, name: 'tipo',     store: [['A','Asignaciones'],['D','Deducciones'],['O','Otro']], columnWidth: 0.22 },
									{ xtype: 'textfield',   fieldLabel: 'Descripcion', labelWidth:70, name: 'descrip',  allowBlank: false, columnWidth : 0.60, id: 'descrip' },
									{ xtype: 'textfield',   fieldLabel: 'Aplica',      labelWidth:50, name: 'aplica',   allowBlank: false, columnWidth: 0.18  },
									{ xtype: 'textfield',   fieldLabel: 'Grupo',       labelWidth:40, name: 'grupo',    allowBlank: true, columnWidth: 0.22  },
									{ xtype: 'textfield',   fieldLabel: 'Titulos',     labelWidth:70, name: 'encab1',   allowBlank: false, columnWidth: 0.35  },
									{ xtype: 'textfield',   fieldLabel: '-',           labelWidth: 5, name: 'encab2',   allowBlank: true, columnWidth: 0.25  },
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset' },
								style:'padding:4px',
								items: [
									{ xtype: 'textareafield', fieldLabel: 'Formula', labelWidth:50, name: 'formula',   allowBlank: false, columnWidth: 1 }
								]
							},{
								layout: 'column',
								labelAlign: 'right',
								defaults: {xtype:'fieldset' },
								style:'padding:4px',
								items: [
									{
										xtype: 'combo',
										fieldLabel: 'Deudora',
										labelWidth:60,
										name: 'tipod',
										id: 'tipod',
										queryMode: 'local',
										triggerAction: 'all',
										forceSelection: true,
										store: [['G','Gasto'], ['P','Proveedor'], ['C', 'Cliente']],
										columnWidth: 0.30,
										listeners: { 'select' : function( field, nval, oval ) {
												mtipod = this.getValue();
												ctadeStore.proxy.extraParams.tipo   = mtipod ;
												ctadeStore.proxy.extraParams.cuenta = '' ;
												ctadeStore.load({ params: {'tipo': this.getValue(), 'origen': 'combotipod' } });
											}
										}
									},
									{
										xtype: 'combo',
										fieldLabel: 'Cuenta',
										labelWidth:60,
										name: 'ctade',
										id:   'ctade',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: ctadeStore,
										columnWidth: 0.70
									},
									{
										xtype: 'combo',
										fieldLabel: 'Acreedora',
										labelWidth:60,
										name: 'tipoa',
										id: 'tipoa',
										queryMode: 'local',
										triggerAction: 'all',
										forceSelection: true,
										store: [['G','Gasto'],['P','Proveedor'],['C', 'Cliente']],
										columnWidth: 0.30,
										listeners: { 'select' : function( field, nval, oval ) {
												mtipoa = this.getValue();
												ctaacStore.proxy.extraParams.tipo   = mtipoa ;
												ctaacStore.proxy.extraParams.cuenta = '' ;
												ctaacStore.load({ params: {'tipo': this.getValue(), 'origen': 'combotipoa' }});
											}
										}
									},
									{
										xtype: 'combo',
										fieldLabel: 'Cuenta',
										labelWidth:60,
										//pageSize: 50,
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										mode: 'remote',
										name: 'ctaac',
										id:   'ctaac',
										valueField: 'item',
										displayField: 'valor',
										store: ctaacStore,
										columnWidth: 0.70
									},
									{
										xtype: 'checkboxfield',
										fieldLabel: 'Aplica para calculo de las liquidaciones',
										labelWidth:300,
										name: 'liquida',
										id:   'liquida',
										columnWidth: 0.99
									}
								]
							}
						], 
						dockedItems: [
							{ xtype: 'toolbar', dock: 'bottom', ui: 'footer', 
							items: ['->', 
								{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
								{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
							]
						}]
					});
					this.callParent();
				},
				setActiveRecord: function(record){
					this.activeRecord = record;
				},
				onSave: function(){
					var form = this.getForm();
					if (!registro) {
						if (form.isValid()) {
							storeConc.insert(0, form.getValues());
							alert('meco 5');
						} else {
							Ext.Msg.alert('Forma Invalida','Algunos campos no pudieron ser validados<br>los mismos se indican con un cuadro rojo<br> corrijalos y vuelva a intentar');
							return;
						}
					} else {
						var active = win.activeRecord;
						if (!active) {
							Ext.Msg.Alert('Registro Inactivo ');
							return;
						}
						if (form.isValid()) {
							form.updateRecord(active);
						} else {
							Ext.Msg.alert('Forma Invalida','Algunos campos no pudieron ser validados<br>los mismos se indican con un cuadro rojo<br> corrijalos y vuelva a intentar');
							return;
						}
					}
					form.reset();
					this.onReset();
				},
				onReset: function(){
					this.setActiveRecord(null);
					storeConc.load();
					//Hide Windows 
					win.hide();
				},
				onClose: function(){
					var form = this.getForm();
					form.reset();
					this.onReset();
				}
			});

			win = Ext.widget('window', {
				title: '',
				losable: false,
				closeAction: 'destroy',
				width: 650,
				height: 370,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						
						if (registro) {
							mctade = registro.data.ctade;
							mctaac = registro.data.ctaac;
							mtipod = registro.data.tipod;
							mtipoa = registro.data.tipoa;
							ctaacStore.proxy.extraParams.tipo   = mtipoa ;
							ctaacStore.proxy.extraParams.cuenta = mctaac ;
							ctadeStore.proxy.extraParams.tipo   = mtipod ;
							ctadeStore.proxy.extraParams.cuenta = mctade ;
							ctadeStore.load({ params: {'tipo': registro.data.tipod, 'cuenta':registro.data.ctade, 'origen': 'beforeform' } });
							ctaacStore.load({ params: {'tipo': registro.data.tipoa, 'cuenta':registro.data.ctaac, 'origen': 'beforeform' } });
							form.loadRecord(registro);
							form.findField('concepto').setReadOnly(true);
						} else {
							form.findField('concepto').setReadOnly(false);
							mctade = '';
							mctaac = '';
							mtipod = '';
							mtipoa = '';
						}
					}
				}
			});
		}
		win.show();
	}

	//Filters
	/*
	var filters = {
		ftype: 'filters',
		// encode and local configuration options defined previously for easier reuse
		encode: 'json', // json encode the filter query
		local: false
	};
	*/

	// Create Grid 
	Ext.define('ConcGrid', {
		extend: 'Ext.grid.Panel',
		alias: 'widget.writergrid',
		store: storeConc,
		initComponent: function(){
			Ext.apply(this, {
				iconCls: 'icon-grid',
				frame: true,
				dockedItems: [{
					xtype: 'toolbar',
					items: [
						{iconCls: 'icon-add',    text: 'Agregar',                                     scope: this, handler: this.onAddClick   },
						{iconCls: 'icon-update', text: 'Modificar', disabled: true, itemId: 'update', scope: this, handler: this.onUpdateClick},
						{iconCls: 'icon-delete', text: 'Eliminar',  disabled: true, itemId: 'delete', scope: this, handler: this.onDeleteClick }
					]
				}],
				columns: colConc,
				// paging bar on the bottom
				bbar: Ext.create('Ext.PagingToolbar', {
					store: storeConc,
					displayInfo: true,
					displayMsg: 'Pag No. {0} - Registros {1} de {2}',
					emptyMsg: \"No se encontraron Registros.\"
				})
			});
			this.callParent();
			this.getSelectionModel().on('selectionchange', this.onSelectChange, this);
		},
		//features: [{ ftype: 'grouping', groupHeaderTpl: '{name} ' }, filters],
		onSelectChange: function(selModel, selections){
			this.down('#delete').setDisabled(selections.length === 0);
			this.down('#update').setDisabled(selections.length === 0);
			},
		
		onUpdateClick: function(){
			var selection = this.getView().getSelectionModel().getSelection()[0];
				if (selection) {
					registro = selection;
					showContactForm();
				}
			},
		onDeleteClick: function() {
			var selection = this.getView().getSelectionModel().getSelection()[0];
			Ext.MessageBox.show({
				title: 'Confirme', 
				msg: 'Esta seguro?', 
				buttons: Ext.MessageBox.YESNO, 
				fn: function(btn){ 
					if (btn == 'yes') { 
						if (selection) {
							storeConc.remove(selection);
						}
						storeConc.load();
					} 
				}, 
				icon: Ext.MessageBox.QUESTION 
			});  
		},
		onAddClick: function(){
			registro = null;
			showContactForm();
			storeConc.load();
		}
	});

//////************ MENU DE ADICIONALES /////////////////
".$listados."

".$otros."
//////************ FIN DE ADICIONALES /////////////////

	Ext.create('Ext.Viewport', {
		layout: {type: 'border',padding: 5},
		defaults: { split: true	},
		items: [
			{
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
						title:'Listados',
						border:false,
						layout: 'fit',
						items: gridListado
					},{
						title:'Otras Funciones',
						border:false,
						layout: 'fit',
						items: gridOtros
					}
				]
			},{
				region: 'center',
				itemId: 'grid',
				xtype: 'writergrid',
				title: 'Conceptos',
				width: '98%',
				align: 'center'
			}
		]
	});

	storeConc.load({ params: { start:0, limit: 30}});
});

</script>
";
		return $script;	
	}






}
?>