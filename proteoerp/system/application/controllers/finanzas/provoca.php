<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Provoca extends Controller {
	var $mModulo='PROVOCA';
	var $titp='Proveedores Eventuales';
	var $tits='Proveedores Eventuales';
	var $url ='finanzas/provoca/';

	function Provoca(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_id('NNN',1);
	}

	function index(){
		if ( !$this->datasis->iscampo('provoca','id') ) {
			$this->db->simple_query('ALTER TABLE provoca DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE provoca DROP INDEX rif');
			$this->db->simple_query('ALTER TABLE provoca ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE provoca ADD UNIQUE INDEX rif (rif)');
		}
		$this->db->simple_query('UPDATE provoca SET rif=TRIM(rif)');
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grid'] = $grid->deploy();

		$bodyscript = '
<script type="text/javascript">
$(function() {
	$( "input:submit, a, button", ".otros" ).button();
});

jQuery("#a1").click( function(){
	var id = jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\'/proteoerp/formatos/ver/PROVOCA/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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

		$centerpanel = '
<div id="RightPane" class="ui-layout-center">
	<div class="centro-centro">
		<table id="newapi'.$param['grid']['gridname'].'"></table>
		<div id="pnewapi'.$param['grid']['gridname'].'"></div>
	</div>
	<div class="centro-sur" id="adicional" style="overflow:auto;">
	</div>
</div> <!-- #RightPane -->
';

		$readyLayout = '
	$(\'body\').layout({
		minSize: 30,
		north__size: 60,
		resizerClass: \'ui-state-default\',
		west__size: 212,
		west__onresize: function (pane, $Pane){jQuery("#west-grid").jqGrid(\'setGridWidth\',$Pane.innerWidth()-2);},
	});
	
	$(\'div.ui-layout-center\').layout({
		minSize: 30,
		resizerClass: "ui-state-default",
		center__paneSelector: ".centro-centro",
		south__paneSelector:  ".centro-sur",
		south__size: 120,
		center__onresize: function (pane, $Pane) {
			jQuery("#newapi'.$param['grid']['gridname'].'").jqGrid(\'setGridWidth\',$Pane.innerWidth()-6);
			jQuery("#newapi'.$param['grid']['gridname'].'").jqGrid(\'setGridHeight\',$Pane.innerHeight()-110);
		}
	});
	';

		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('PROVOCA', 'JQ');
		$param['otros']       = $this->datasis->otros('PROVOCA', 'JQ');
		$param['tema1']       = 'darkness';

		$param['readyLayout']  = $readyLayout;
		$param['centerpanel']  = $centerpanel;
		$param['anexos']      = 'anexos1';
		$param['bodyscript']  = $bodyscript;
		$param['tabs']        = false;
		$param['encabeza']    = $this->titp;
		$this->load->view('jqgrid/crud',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "true";

		$grid  = new $this->jqdatagrid;

		$grid->addField('rif');
		$grid->label('R.I.F.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:13, maxlength: 13 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 300,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 80 }',
		));


		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'    => "'center'",
			'frozen'   => 'true',
			'width'    => 60,
			'editable' => 'false',
			'search'   => 'false'
		));


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('260');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 400, height:180, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 400, height:180, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(true);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setonSelectRow('
			function(id){
				$.ajax({
					url: "'.base_url().$this->url.'tabla/"+id,
					success: function(msg){$("#adicional").html(msg);}
				});
			}
		');


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
		$mWHERE = $grid->geneTopWhere('provoca');

		$response   = $grid->getData('provoca', array(array()), array(), false, $mWHERE, 'id', 'desc' );
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
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$rif = $this->input->post('rif');
				$this->db->insert('provoca', $data);
				echo "Registro Agregado";
				logusu('PROVOCA',"Registro ".$rif." INCLUIDO");
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$rifn = $this->input->post('rif');
			$rifo = $this->datasis->dameval("SELECT rif FROM provoca WHERE id=$id");
			if ( $rifn == $rifo ){
				// No cambio el RIF
				unset($data['rif']);
				$this->db->where('id', $id);
				$this->db->update('provoca', $data);
			} else {
				// Cambio el RIF debe cambiar en gitser
				$this->db->query("UPDATE gitser SET rif=? WHERE rif=?",array($rifn,$rifo));
				// Si el Rif ya existe se Borra
				$this->db->query("DELETE FROM provoca WHERE rif=? AND id<>$id", array($rifn,$rifo));
				$this->db->where('id', $id);
				$this->db->update('provoca', $data);
			}
			logusu('PROVOCA',"Registro ".$rifn." MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			$rif   = $this->datasis->dameval("SELECT rif FROM provoca WHERE id=$id");
			$check = $this->datasis->dameval("SELECT COUNT(*) FROM gitser WHERE rif=".$this->db->escape($rif));
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM provoca WHERE id=$id");
				logusu('PROVOCA',"Registro ".$rif." ELIMINADO");
				echo "Registro Eliminado ".$rif;
			}
		};
	}

	function tabla() {
		$id = $this->uri->segment($this->uri->total_segments());
		
		$rif = $this->datasis->dameval("SELECT rif FROM provoca WHERE id=$id");

		$td1  = "<td style='border-style:solid;border-width:1px;border-color:#78FFFF;' valign='top' align='center'>\n";
		$td1 .= "<table width='98%'>\n<caption style='background-color:#5E352B;color:#FFFFFF;font-style:bold'>";

		// Movimientos Relacionados en Proveedores GASTOS
		$mSQL = "SELECT proveed, numero, fecha, descrip, numfac, iva, importe FROM gitser WHERE rif=? ORDER BY id DESC LIMIT 5";
		$query = $this->db->query($mSQL, array($rif));
		$salida = '<table width="100%"><tr>';
		$saldo  = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= $td1;
			$salida .= "Movimientos Recientes (5)</caption>";
			$salida .= "<tr bgcolor='#E7E3E7'><td>Prov.</td><td>Fecha</td><td>Numero</td><td>Factura</td><td align='center'>Descripcion</td><td align='center'>IVA</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['proveed']."</td>";
				$salida .= "<td>".$row['fecha']."</td>";
				$salida .= "<td>".$row['numero']."</td>";
				$salida .= "<td>".$row['numfac']."</td>";
				$salida .= "<td>".$row['descrip']."</td>";
				$salida .= "<td align='right'>".nformat($row['iva']).   "</td>";
				$salida .= "<td align='right'>".nformat($row['importe']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table></td>";
		}
		echo $salida.'</tr></table>';
	}

}


/*
class Provoca extends validaciones {

	function Provoca(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(206,1);
		define ("THISFILE",   APPPATH."controllers/finanzas". $this->uri->segment(2).EXT);
	}

	function index(){
		if ( !$this->datasis->iscampo('provoca','id') ) {
			$this->db->simple_query('ALTER TABLE provoca DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE provoca DROP INDEX rif');
			$this->db->simple_query('ALTER TABLE provoca ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE provoca ADD UNIQUE INDEX rif (rif)');
		}
		$this->db->simple_query('UPDATE provoca SET rif=TRIM(rif)');
		$this->datasis->modulo_id(206,1);
		$this->provocaextjs();

	}


	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de proveedores ocasionales', 'provoca');
		
		$filter->rif = new inputField('RIF', 'rif');
		$filter->rif->maxlength=13;
		$filter->rif->size = 14;
		
		$filter->nombre = new inputField('Nombre', 'nombre');
		
		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/provoca/dataedit/show/<#rif#>','<#rif#>');

		$grid = new DataGrid("Filtro de Proveedores Ocasionales");
		//$grid->order_by("nombre","asc");
		$grid->per_page = 10;
		
		$grid->column_orderby('RIF'   ,$uri,'rif');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Fecha' ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha',"align='center'");
		
		$grid->add('finanzas/provoca/dataedit/create','Agregar un proveedor ocasional');
		$grid->build();
	
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Proveedores Ocasionales</h1>";
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load("dataedit");

		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
				'cliente' =>'C&oacute;digo Socio',
				'nombre'=>'Nombre', 
				'cirepre'=>'Rif/Cedula',
				'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Socio','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'socio'),
			'titulo'  =>'Buscar Socio');

		$qformato=$this->qformato=$this->datasis->formato_cpla();
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

		$boton =$this->datasis->modbus($mSCLId);
		$bcpla =$this->datasis->modbus($mCPLA);
		
		$smenu['link']=barra_menu('131');
		$consulrif=$this->datasis->traevalor('CONSULRIF');
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		
		function anomfis(){
				vtiva=$("#tiva").val();
				if(vtiva=="C" || vtiva=="E" || vtiva=="R"){
					$("#tr_nomfis").show();
					$("#tr_riff").show();
				}else{
					$("#nomfis").val("");
					$("#rif").val("");
					$("#tr_nomfis").hide();
					$("#tr_rif").hide();
				}
		}
		
		function consulrif(){
				vrif=$("#rif").val();
				if(vrif.length==0){
					alert("Debe introducir primero un RIF");
				}else{
					vrif=vrif.toUpperCase();
					$("#rif").val(vrif);
					window.open("'.$consulrif.'"+"?p_rif="+vrif,"CONSULRIF","height=350,width=410");
				}
		}
		
		';
		$edit = new DataEdit("Proveedor ocasional", "provoca");
		$edit->back_url = site_url("finanzas/provoca/filteredgrid");
		$edit->script($script, 'create');
		$edit->script($script, 'modify');
		
		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->rif =  new inputField('RIF', 'rif');
		$edit->rif->mode='autohide';
		$edit->rif->rule = 'strtoupper|required|callback_chrif';
		$edit->rif->append($lriffis);
		$edit->rif->maxlength=10;
		$edit->rif->size = 14;
		
		$edit->nombre =  new inputField('Nombre', 'nombre');
		$edit->nombre->rule = 'strtoupper|required';
		$edit->nombre->size = 80;
		$edit->nombre->maxlength=80;

		$edit->fecha =  new dateField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue=date('Y-m-d');
		$edit->fecha->size = 10;

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;
		$data['title']   = '<h1>Proveedores Ocasionales</h1>';
		$data['head']    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _pre_del($do) {
		$codigo=$this->db->escape($do->get('rif'));
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM gitser WHERE rif=$codigo");
		
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='No se puede borrar el proveedor porque contiene movimientos';
			return False;
		}
		return True;
	}
	function _post_insert($do){
		$codigo=$do->get('rif');
		$nombre=$do->get('nombre');
		logusu('provoca',"PROVEEDOR OCASIONAL $codigo NOMBRE $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('rif');
		$nombre=$do->get('nombre');
		logusu('provoca',"PROVEEDOR OCASIONAL $codigo NOMBRE $nombre MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('rif');
		$nombre=$do->get('nombre');
		logusu('provoca',"PROVEEDOR OCASIONAL $codigo NOMBRE $nombre ELIMINADO");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM importtgas WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM importtgas WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el gasto $nombre");
			return FALSE;
		}else {
		return TRUE;
		}
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"nombre","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('provoca');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
	
		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();

		$results = $this->db->count_all('provoca');
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear() {
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$rif = $data['data']['rif'];
		unset($campos['id']);
		$mHay = $this->datasis->dameval("SELECT count(*) FROM provoca WHERE rif='".$rif."'");
		if  ( $mHay > 0 ){
			echo "{ success: false, message: 'Ya existe ese proveedor'}";
		} else {
			$mSQL = $this->db->insert_string("sprv", $campos );
			$this->db->simple_query($mSQL);
			logusu('provoca',"PROVEEDOR OCASIONAL $rif CREADO");
			echo "{ success: true, message: ".$data['data']['rif']."}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$rif = $campos['rif'];
		unset($campos['rif']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("provoca", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('provoca',"PROVEEDOR OCASIONAL ".$data['data']['rif']." MODIFICADO");
		echo "{ success: true, message: 'Proveedor ocasional Modificado '}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$rif = $data['data']['rif'];
		
		// VERIFICAR SI PUEDE
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM gitser WHERE rif='$rif'");

		if ($chek > 0){
			echo "{ success: false, message: 'Proveedor con Movimiento no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM provoca WHERE rif='$rif'");
			logusu('provoca',"PROVEEDOR OCASIONAL $rif ELIMINADO");
			echo "{ success: true, message: 'Proveedor Eliminado'}";
		}
	}



//****************************************************************
//
//
//
//****************************************************************

	function provocaextjs(){
		$encabeza='PROVEEDORES OCASIONALES';
		$listados= $this->datasis->listados('provoca');
		$otros=$this->datasis->otros('provoca', 'provoca');

		$consulrif=$this->datasis->traevalor('CONSULRIF');

		$urlajax = 'finanzas/provoca/';
		$variables = "";
		$funciones = "";
		$valida = "
		{ type: 'length', field: 'nombre', min:  3 }
		";
		

		$columnas = "
		{ header: 'Nro',    width:  50, sortable: true,  dataIndex: 'id',     field: { type: 'numberfield' }}, 
		{ header: 'R.I.F.', width: 100, sortable: true,  dataIndex: 'rif',    field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Nombre', width: 300, sortable: true,  dataIndex: 'nombre', field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Fecha',  width:  90, sortable: false, dataIndex: 'fecha',  field: { type: 'date'      }, filter: { type: 'date'   }} 
	";

		$campos = "'id', 'rif', 'nombre', 'fecha'";
		
		$camposforma = "
			{
			frame: false,
			border: false,
			labelAlign: 'right',
			tdefaults: { xtype:'fieldset', labelWidth:70 },
			style:'padding:4px',
			items:[	
				{ xtype: 'textfield', fieldLabel: 'RIF',    name: 'rif',    allowBlank: false, width: 200 },
				{ xtype: 'textfield', fieldLabel: 'Nombre', name: 'nombre', allowBlank: false, width: 400 },
				{ xtype: 'datefield', fieldLabel: 'Fecha',  name: 'fecha',  format: 'd/m/Y', submitFormat: 'Y-m-d', value: new Date(), }
			]}
		";

		$titulow = 'Proveedores Ocasionales';

		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
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
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		
		$data['title']  = heading('Proveedores Ocacionales');
		$this->load->view('extjs/extjsven',$data);
		
	}
}
 */
?>