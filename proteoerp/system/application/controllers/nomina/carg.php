<?php
//cargos
class Carg extends Controller {
	
	function carg(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		if ( !$this->datasis->iscampo('carg','id') ) {
			$this->db->simple_query('ALTER TABLE carg DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE carg ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE carg ADD UNIQUE INDEX cargo (cargo)');
		}
		$this->datasis->modulo_id(701,1);
		redirect("nomina/carg/extgrid");
	}

	function extgrid(){
		$this->datasis->modulo_id(701,1);
		$this->cargextjs();
	}


	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter2("Filtro por Cargo",'carg');
		
		$filter->cargo   = new inputField("C&oacute;digo", "cargo");
		$filter->cargo->size=3;
		$filter->cargo->clause = "likerigth";
		
		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->cargo->clause = "likerigth";
		
		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');
 
		$uri = anchor('nomina/carg/dataedit/show/<#cargo#>','<#cargo#>');
		$uri_2  = anchor('nomina/carg/dataedit/modify/<#cargo#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));
		
		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."nomina/carg/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";

		$grid = new DataGrid($mtool);
		$grid->order_by("cargo","asc");
		$grid->per_page = 50;
		
		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("Cargo",$uri,'cargo');
		$grid->column_orderby("Descripci&oacute;n","descrip","descrip");
		$grid->column_orderby("Sueldo"               ,"<number_format><#sueldo#>|2|,|.</number_format>",'sueldo',"align='right'");
		
		//$grid->add("nomina/carg/dataedit/create");
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
    width: 290px; /* Required to set */
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
		
		$data['title']  = heading('Cargos');
		$data['script'] = script('jquery.js');
		$data["script"].= script('superTables.js');
		$data['head']   = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	

	}
		function dataedit(){
 		$this->rapyd->load("dataedit");
  	
  	$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
  	
		$edit = new DataEdit("Cargos","carg");
		$edit->back_url = "nomina/carg/filteredgrid";
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->cargo = new inputField("Cargo", "cargo");
		$edit->cargo->rule= "required|callback_chexiste";
		$edit->cargo->mode="autohide";
		$edit->cargo->maxlength=8;
		$edit->cargo->size=10;
		
		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size=40;
		$edit->descrip->maxlength=30;
		$edit->descrip->rule= "strtoupper|required";
		
		$edit->sueldo  = new inputField("Sueldo", "sueldo");
		$edit->sueldo->size=20;
		$edit->sueldo->rule= "required|callback_positivo";
		$edit->sueldo->css_class='inputnum';
		
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Cargos</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	
	function _pre_del($do) {
		$codigo=$do->get('cargo');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE cargo='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}
		return True;
	}
	function _post_insert($do){
		$codigo=$do->get('cargo');
		$nombre=$do->get('descrip');
		logusu('carg',"CARGO $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('cargo');
		$nombre=$do->get('descrip');
		logusu('carg',"CARGO $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('cargo');
		$nombre=$do->get('descrip');
		logusu('carg',"CARGO $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('cargo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM carg WHERE cargo='$codigo'");
		if ($chek > 0){
			$cargo=$this->datasis->dameval("SELECT descrip FROM carg WHERE cargo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el cargo $cargo");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	function instalar(){
		$mSQL="ALTER TABLE carg ADD PRIMARY KEY (cargo);";
		$this->db->simple_query($mSQL);	
	}
	
	function positivo($valor){
		if ($valor <= 0){
			$this->validation->set_message('positivo',"El campo Sueldo debe ser positivo");
			return FALSE;
		}
  	return TRUE;
	}
	
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"cargo","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = "";

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('carg');

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
				//$where = " rif != '' ";
				$qs = "";
				for ($i=0;$i<count($filter);$i++){
					switch($filter[$i]['type']){
					case 'string' : $qs .= " ".$filter[$i]['field']." LIKE '%".$filter[$i]['value']."%'"; 
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
				$this->db->where($where,null, false);
				
			}
		}
		
		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();

		$results = $this->db->count_all('carg');
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

	function crear() {
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$cargo = $data['data']['cargo'];
		unset($campos['id']);
		$mHay = $this->datasis->dameval("SELECT count(*) FROM carg WHERE cargo='".$cargo."'");
		if  ( $mHay > 0 ){
			echo "{ success: false, message: 'Ya existe ese proveedor'}";
		} else {
			$mSQL = $this->db->insert_string("carg", $campos );
			$this->db->simple_query($mSQL);
			logusu('provoca',"CARGO DE NOMINA $cargo CREADO");
			echo "{ success: true, message: ".$data['data']['cargo']."}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$cargo = $campos['cargo'];
		unset($campos['cargo']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("carg", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('carg',"CARGO DE NOMINA ".$data['data']['cargo']." MODIFICADO");
		echo "{ success: true, message: 'Proveedor ocacional Modificado '}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$cargo = $data['data']['cargo'];
		
		// VERIFICAR SI PUEDE
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE cargo='$cargo'");

		if ($chek > 0){
			echo "{ success: false, message: 'Cargo asignado a personal, no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM carg WHERE cargo='$cargo'");
			logusu('carg',"CARGO DE NOMINA $cargo ELIMINADO");
			echo "{ success: true, message: 'Cargo de nomina Eliminado'}";
		}
	}



//****************************************************************
//
//
//
//****************************************************************

	function cargextjs(){
		$encabeza='CARGOS DE NOMINA';
		$listados= $this->datasis->listados('carg');
		$otros=$this->datasis->otros('carg', 'carg');

		$urlajax = 'nomina/carg/';
		$variables = "";
		
		$funciones = "";
		
		$valida = "
		{ type: 'length', field: 'cargo',   min:  1 },
		{ type: 'length', field: 'descrip', min:  1 }
		";
		

		$columnas = "
		{ header: 'Cargo',        width:  50, sortable: true, dataIndex: 'cargo',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Descripcion.', width: 300, sortable: true, dataIndex: 'descrip', field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Sueldo',       width: 120, sortable: true, dataIndex: 'sueldo',  field: { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') }, 
	";

		$campos = "'id', 'cargo', 'descrip', 'sueldo'";
		
		$camposforma = "
			{
			frame: false,
			tborder: false,
			labelAlign: 'right',
			defaults: { xtype:'fieldset', labelWidth:70 },
			style:'padding:4px',
			items:[	
				{ xtype: 'textfield',   fieldLabel: 'Cargo',       name: 'cargo',   allowBlank: false, width: 200 },
				{ xtype: 'textfield',   fieldLabel: 'Descripcion', name: 'descrip', allowBlank: false, width: 400 },
				{ xtype: 'numberfield', fieldLabel: 'Sueldo ',     name: 'sueldo',  hideTrigger: true, fieldStyle: 'text-align: right', width:230,renderer : Ext.util.Format.numberRenderer('0,000.00') },
			]}
		";



		$titulow = 'Cargos de Nomina';

		$dockedItems = "
				\t\t\t\t{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				\t\t\t\t{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 250,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						if (registro) {
							form.loadRecord(registro);
						}
					}
				}
";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['winwidget']   = $winwidget;
		
		$data['title']  = heading('Cargos de Nomina');
		$this->load->view('extjs/extjsven',$data);
		
	}


}
?>