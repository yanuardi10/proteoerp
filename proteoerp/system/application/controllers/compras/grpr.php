<?php
class Grpr extends Controller {
	var $mModulo='GRPR';
	var $titp='Grupo de Proveedores';
	var $tits='Grupo de Proveedores';
	var $url ='compras/grpr/';

	function Grpr(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		if ( !$this->datasis->iscampo('grpr','id') ) {
			$this->db->simple_query('ALTER TABLE grpr DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE grpr ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE grpr ADD UNIQUE INDEX grupo (grupo)');
		}
		$this->datasis->modintramenu( 700, 500, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$bodyscript = '
<script type="text/javascript">
$(function() {
	$( "input:submit, a, button", ".otros" ).button();
});

jQuery("#a1").click( function(){
	var id = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\''.base_url().'formatos/ver/GRPR/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
<div class="anexos">

<table id="west-grid" align="center">
	<tr>
		<td><div class="tema1"><table id="listados"></table></div></td>
	</tr>
	<tr>
		<td><div class="tema1"><table id="otros"></table></div></td>
	</tr>
</table>

<table id="west-grid" align="center">
	<tr>
		<td></td>
	</tr>
</table>
</div>
'.
//		<td><a style="width:190px" href="#" id="a1">Imprimir Copia</a></td>
'</div> <!-- #LeftPane -->
';

		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->
';
		$param['WestPanel']  = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados'] = $this->datasis->listados('GRPR', 'JQ');
		$param['otros']    = $this->datasis->otros('GRPR', 'JQ');
		$param['temas']     = array('proteo','darkness','anexos1');
		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "true";
		$link   = site_url('ajax/buscacpla');

		$grid  = new $this->jqdatagrid;

		$grid->addField('grupo');
		$grid->label('Grupo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));

		$grid->addField('gr_desc');
		$grid->label('Descripcion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 25 }',
		));

		$grid->addField('cuenta');
		$grid->label('Cta. Contable');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{'.$grid->autocomplete($link, 'cuenta','cucucu','<div id=\"cucucu\"><b>"+ui.item.descrip+"</b></div>').'}'
		));


		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 400, height:200, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 400, height:200, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(true);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdata()
	{
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('grpr');

		$response   = $grid->getData('grpr', array(array()), array(), false, $mWHERE, 'grupo' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$mcodp  = "grupo";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM grpr WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('grpr', $data);
					echo "Registro ".$data[$mcodp]." Agregado";
					logusu('GRPR',"Registro ".$data[$mcodp]." INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM grpr WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM grpr WHERE grupo=?", array($anterior));
				$this->db->query("UPDATE sprv SET grupo=? WHERE grupo=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("grpr", $data);
				logusu('GRPR',"Grupo Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en proveedores ".$anterior."=>".$nuevo;
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('grpr', $data);
				logusu('GRPR',"Grupo de Proveedor ".$nuevo." MODIFICADO");
				echo "Grupo ".$nuevo." Modificado";
			}

		} elseif($oper == 'del') {
		$meco = $this->datasis->dameval("SELECT $mcodp FROM grpr WHERE id=$id");
			$grupo = $this->datasis->dameval("SELECT grupo FROM grpr WHERE id=$id");
			$check = $this->datasis->dameval("SELECT count(*) FROM sprv WHERE grupo=".$this->db->escape($grupo));
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM grpr WHERE id=$id ");
				logusu('GRPR',"Grupo ".$grupo." ELIMINADO");
				echo "Grupo ".$grupo." Eliminado";
			}
		};
	}
}


/*

		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM grcl WHERE grupo=".$this->db->escape($data['grupo']));
				if ( $check == 0 ){
					$this->db->insert('grcl', $data);
					echo "Registro Agregado";
					logusu('GRCL',"Grupo de Cliente  ".$data['grupo']." INCLUIDO");
				} else
					echo "Ya existe un grupo con ese Codigo";
					
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$grupo  = $data['grupo'];
			$grupov = $this->datasis->dameval("SELECT grupo FROM grcl WHERE id=$id");
			if ( $grupo <> $grupov ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM grcl WHERE grupo=?", array($grupo));
				$this->db->query("UPDATE scli SET grupo=? WHERE grupo=?", array( $grupo, $grupov ));
				$this->db->where('id', $id);
				$this->db->update('grcl', $data);	
				logusu('GRCL',"Grupo Cambiado/Fusionado Nuevo:".$grupo." Anterior: ".$grupov." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data['grupo']);
				$this->db->where('id', $id);
				$this->db->update('grcl', $data);	
				logusu('GRCL',"Grupo de Cliente  ".$grupo." MODIFICADO");
				echo "Grupo Modificado";
			}

		} elseif($oper == 'del') {
			$grupo = $this->datasis->dameval("SELECT grupo FROM grcl WHERE id=$id");
			$check = $this->datasis->dameval("SELECT count(*) FROM scli WHERE grupo=".$this->db->escape($grupo));
			if ($check > 0){
				echo " El grupo no puede ser eliminado; tiene clientes asociados ";
			} else {
				$this->db->simple_query("DELETE FROM grcl WHERE id=$id ");
				logusu('GRCL',"Grupo de Cliente ".$grupo." ELIMINADO");
				echo "Grupo Eliminado";
			}
		};






require_once(BASEPATH.'application/controllers/validaciones.php');
class Grpr extends validaciones {

	function grpr(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(231,1);
	}

	function index(){
		$this->db->simple_query('UPDATE grpr SET grupo=TRIM(grupo)');
		if ( !$this->datasis->iscampo('grpr','id') ) {
			$this->db->simple_query('ALTER TABLE grpr DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE grpr ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE grpr ADD UNIQUE INDEX grupo (grupo)');
		}
		
		//redirect("compras/grpr/filteredgrid");
		redirect('compras/grpr/extgrid');
	}

	function extgrid(){
		$this->datasis->modulo_id(206,1);
		$this->grprextjs();
	}

	function filteredgrid(){
		
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Grupos de Provedores', 'grpr');
		
		$filter->grupo = new inputField('Grupo','grupo');
		$filter->grupo->size=5;
	
		$filter->nombre = new inputField('Nombre', 'gr_desc');
		$filter->nombre->size=25;
		$filter->nombre->maxlength=25;
		
		$filter->buttons('reset','search');
		$filter->build();
		
		$uri = anchor('compras/grpr/dataedit/show/<#grupo#>','<#grupo#>');

		$grid = new DataGrid('Lista de Grupos de Provedores');
		$grid->order_by('gr_desc','asc');
		$grid->per_page = 7;
		
		$grid->column_orderby('Grupo',$uri,'grupo');
		$grid->column_orderby('Nombre','gr_desc','gr_desc');
		$grid->column_orderby('Cuenta','cuenta','cuenta');
		
		$grid->add('compras/grpr/dataedit/create'.'Agregar un grupo');
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Grupos de Proveedores</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit(){
		$this->rapyd->load("dataedit");
		$link=site_url('compras/grpr/ugrupoprv');
		$script ='
		function ultimo(){
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo numero ingresado fue: " + msg );
				}
			});
		}';

		$edit = new DataEdit("Grupo de Provedores", "grpr");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->back_url = site_url("compras/grpr/filteredgrid");
		
		$edit->pre_process('delete','_pre_delete');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$lgrup='<a href="javascript:ultimo();" title="Consultar &uacute;ltimo grupo ingresado" onclick="">Consultar &uacute;ltimo grupo</a>';
		$edit->grupo = new inputField('Grupo', 'grupo');
		$edit->grupo->mode ='autohide';
		$edit->grupo->size=7;
		$edit->grupo->maxlength =4;
		$edit->grupo->rule = 'trim|required|callback_chexiste';
		$edit->grupo->append($lgrup);
		
		$edit->gr_desc = new inputField('Descripcion','gr_desc');
		$edit->gr_desc->size=35;
		$edit->gr_desc->maxlength =25;
		$edit->gr_desc->rule = 'trim|strtoupper|required';
		
		$edit->cuenta = new inputField('Cta. Contable','cuenta');
		$edit->cuenta->size=18;
		$edit->cuenta->maxlength =15;
		$edit->cuenta->rule ='trim|callback_chcuentac';
		
		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();
		
		$data['content'] = $edit->output;
		$data['title']   = '<h1>Grupos de Proveedores</h1>';
		$data['head']    = script("jquery.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _pre_delete($do) {
		$grupo=$this->db->escape($do->data['grupo']);
		$resulta=$this->datasis->dameval("SELECT count(*) FROM sprv WHERE grupo=$grupo");
		if ($resulta==0){
			return True;
		}else{
			$do->error_message_ar['pre_del']="No se puede borrar el registro ya que hay proveedores que pertenecen a este grupo";
			return False;
		}
	}
	function _post_insert($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('gr_desc');
		logusu('grpr',"GRUPO $codigo NOMBRE $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('gr_desc');
		logusu('grpr',"GRUPO $codigo NOMBRE $nombre MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('gr_desc');
		logusu('grpr',"GRUPO $codigo NOMBRE $nombre ELIMINADO");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('grupo');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM grpr WHERE grupo='$codigo'");
		if ($check > 0){
			$nombre=$this->datasis->dameval("SELECT gr_desc FROM grpr WHERE grupo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el grupo $nombre");
			return FALSE;
		}else {
			return TRUE;
		}
	}
	function ugrupoprv(){
		$consulgrupo=$this->datasis->dameval('SELECT MAX(grupo) FROM grpr');
		echo $consulgrupo;
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"grupo","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;
		
		$where = $this->datasis->extjsfiltro($filters);
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('grpr');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		$sort = json_decode($sort, true);

		if ( count($sort) == 0 ) $this->db->order_by( 'grupo', 'asc' );
		
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('grpr');
		$arr = $this->datasis->codificautf8($query->result_array());

		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$grupo  = $campos['grupo'];


		if ( !empty($grupo) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM grpr WHERE grupo='$grupo'") == 0)
			{
				$mSQL = $this->db->insert_string("grpr", $campos );
				$this->db->simple_query($mSQL);
				logusu('grpr',"GRUPO DE PROVEEDOR $grupo CREADO");
				echo "{ success: true, message: 'Grupo Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un grupo con ese Codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe un Grupo de Proveedor con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$grupo = $campos['grupo'];
		unset($campos['grupo']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("grpr", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('grpr',"GRUPO DE PROVEEDORES $grupo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Grupo Modificado  -> ".$data['data']['grupo']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$grupo = $campos['grupo'];


		$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE grupo='$grupo'");


		if ($check > 0){
			echo "{ success: false, message: 'Grupo de Proveedor no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM grpr WHERE grupo='$grupo'");
			logusu('grpr',"CONCEPTO $grupo ELIMINADO");
			echo "{ success: true, message: 'Grupo Eliminado'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function grprextjs(){
		$encabeza='GRUPOS DE PROVEEDORES';
		$listados= $this->datasis->listados('grpr');
		$otros=$this->datasis->otros('grpr', 'grpr');

		$urlajax = 'compras/grpr/';
		$variables = "var mcuenta = ''";
		$funciones = "";
		$valida = "
		{ type: 'length', field: 'grupo',   min: 1 },
		{ type: 'length', field: 'gr_desc', min: 1 }
		";
		
		$columnas = "
		{ header: 'id',          width:  30, sortable: true, dataIndex: 'id' }, 
		{ header: 'Grupo',       width:  50, sortable: true, dataIndex: 'grupo',   field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Descripcion', width: 200, sortable: true, dataIndex: 'gr_desc', field: { type: 'textfield' }, filter: { type: 'string' } }, 
		{ header: 'Cuenta',      width:  90, sortable: true, dataIndex: 'cuenta',  field: { type: 'textfield' }, filter: { type: 'string' } }
	";

		$campos = "'id', 'grupo', 'gr_desc', 'cuenta'";
		
		$camposforma = "
							{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							items: [
									{ xtype: 'textfield',   fieldLabel: 'Grupo',       name: 'grupo',   allowBlank: false, width: 120, id: 'grupo' },
									{ xtype: 'textfield',   fieldLabel: 'Descripcion', name: 'gr_desc', allowBlank: false, width: 400, },
								]
							},{
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [{
										xtype: 'combo',
										fieldLabel: 'Cuenta Contable',
										labelWidth:100,
										name: 'cuenta',
										id:   'cuenta',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: cplaStore,
										width: 400
									}
								]
							}
		";

		$titulow = 'Grupo de Proveedores';

		$dockedItems = "
				\t\t\t\t{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				\t\t\t\t{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 210,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						
						if (registro) {
							mcuenta  = registro.data.cuenta;
							cplaStore.proxy.extraParams.cuenta   = mcuenta ;
							cplaStore.load({ params: { 'cuenta': registro.data.cuenta, 'origen': 'beforeform' } });
							form.loadRecord(registro);
						} else {
							mcuenta  = '';
						}
					}
				}
";

		$stores = "
var cplaStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,
	autoSync: false,
	pageSize: 50,
	pruneModifiedRecords: true,
	totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'contabilidad/cpla/cplabusca',
		extraParams: {  'cuenta': mcuenta, 'origen': 'store' },
		reader: {
			type: 'json',
			totalProperty: 'results',
			root: 'data'
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
		
		$data['title']  = heading('Aranceles');
		$this->load->view('extjs/extjsven',$data);
		
	}

}
*/
?>