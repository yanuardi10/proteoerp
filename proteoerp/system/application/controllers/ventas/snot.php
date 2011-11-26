<?php //require_once(BASEPATH.'application/controllers/validaciones.php');
class snot extends Controller {

	function snot(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->back_dataedit='ventas/snot/filteredgrid';
	}

	function index() {
		if ( !$this->datasis->iscampo('snot','id') ) {
			$this->db->simple_query('ALTER TABLE snot DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE snot ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE snot ADD UNIQUE INDEX numero (numero)');
		}
		$this->snotextjs();

	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Notas de entrega','snot');
		
		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->size=15;
		$filter->fecha->maxlength=15;
		$filter->fecha->rule='trim';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=15;

		$filter->factura = new inputField('Factura', 'factura');
		$filter->factura->size=15;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('ventas/snot/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid('Lista de Notas de Entrega');
		$grid->use_function('dbdate_to_human');
		$grid->order_by('numero','desc');
		$grid->per_page = 10;

		$grid->column_orderby('N&uacute;mero', $uri,'numero');
		$grid->column_orderby('Fecha','<dbdate_to_human><#fecha#></dbdate_to_human>','fecha');
		$grid->column_orderby('Factura','factura','factura');
		$grid->column_orderby('Fecha F.','<dbdate_to_human><#fechafa#></dbdate_to_human>','fechafa');
		$grid->column_orderby('Nombre', 'nombre','nombre');

		//$grid->add('ventas/ssnot/dataedit/create');
		$grid->build();

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		$data['title']   = heading('Notas de Entrega');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		
		$modbusSinv=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'descrip_<#i#>',
			),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Articulo',
			'where'   => '`activo` = "S" AND `tipo` = "Articulo"'
		);
		$boton=$this->datasis->p_modbus($modbusSinv,'<#i#>');

		$modbus=array(
			'tabla'   =>'sfac',
			'columnas'=>array(
				'numero'  =>'N&uacute;mero',
				'fecha'   =>'Fecha',
				'cod_cli' =>'Cliente',
				'rifci'   =>'Rif',
				'nombre'  =>'Nombre',
				'tipo_doc' =>'Tipo',
				),
			'filtro'  =>array('numero'=>'N&uacute;mero','cod_cli'=>'Cliente','rifci'=>'Rif','nombre'=>'Nombre'),
			'where'=>'tipo_doc = "F" and mid(numero,1,1) <> "_"',
			'retornar'=>array(
				'numero' =>'factura',
				'fecha'  =>'fechafa',
				'cod_cli'=>'cod_cli',
				'nombre' =>'nombre'
			),
			'titulo'  => 'Buscar Factura',
		);
		$btn=$this->datasis->modbus($modbus);

		$do = new DataObject('snot');
		$do->rel_one_to_many('itsnot', 'itsnot', 'numero');
		$do->rel_pointer('itsnot','sinv','itsnot.codigo=sinv.codigo','sinv.descrip AS sinvdescrip','sinv.ultimo AS sinvultimo');

		$edit = new DataDetails('Nota de Entrega', $do);
		$edit->back_url = $this->back_dataedit;
		$edit->set_rel_title('itsnot','Producto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');
		
		$edit->fechafa = new DateonlyField('Fecha Factura', 'fechafa','d/m/Y');
		$edit->fechafa->insertValue = date('Y-m-d');
		$edit->fechafa->rule = 'required';
		$edit->fechafa->mode = 'autohide';
		$edit->fechafa->size = 10;

		$edit->factura = new inputField('Factura', 'factura');
		$edit->factura->size = 10;
		$edit->factura->mode='autohide';
		$edit->factura->maxlength=8;
		
		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->append($btn);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';
		
		$edit->observa1 = new inputField('Observaciones', 'observ1');
		$edit->observa1->size      = 40;
		$edit->observa1->maxlength = 80;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itsnot';
		$edit->codigo->rule     = 'required|callback_chrepetidos';
		$edit->codigo->append($boton);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->readonly  = true;
		$edit->descrip->rel_id='itsnot';

		$edit->cant = new inputField('Cantidad <#o#>', 'cant_<#i#>');
		$edit->cant->db_name  = 'cant';
		$edit->cant->css_class= 'inputnum';
		$edit->cant->rel_id   = 'itsnot';
		$edit->cant->maxlength= 10;
		$edit->cant->size     = 6;
		$edit->cant->rule     = 'required|positive';
		$edit->cant->autocomplete=false;
		
		$edit->saldo = new inputField('Saldo <#o#>', 'saldo_<#i#>');
		$edit->saldo->db_name  = 'saldo';
		$edit->saldo->css_class= 'inputnum';
		$edit->saldo->rel_id   = 'itsnot';
		$edit->saldo->maxlength= 10;
		$edit->saldo->size     = 6;
		$edit->saldo->rule     = 'required|positive';
		$edit->saldo->autocomplete=false;
		
		$edit->entrega = new inputField('Entrega <#o#>', 'entrega_<#i#>');
		$edit->entrega->db_name  = 'entrega';
		$edit->entrega->css_class= 'inputnum';
		$edit->entrega->rel_id   = 'itsnot';
		$edit->entrega->maxlength= 10;
		$edit->entrega->size     = 6;
		$edit->entrega->rule     = 'required|positive';
		$edit->entrega->autocomplete=false;
		
		$edit->itfactura = new inputField('Factura <#o#>', 'itfactura_<#i#>');
		$edit->itfactura->size     = 12;
		$edit->itfactura->db_name  = 'factura';
		$edit->itfactura->readonly = true;
		$edit->itfactura->rel_id   = 'itsnot';
		$edit->itfactura->append($boton);
		//**************************
		//fin de campos para detalle
		//**************************
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_snot', $conten,true);
		$data['title']   = heading('Notas De Entrega');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		return false;
	}

	function _pre_update($do){
		return false;
	}

	function _post_insert($do){
		//trafrac ittrafrac
		$codigo=$do->get('numero');
		logusu('snot',"Nota Entrega $codigo CREADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('snot',"Nota Entrga $codigo ELIMINADO");
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'snot');
	
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('snot');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'numero', 'desc' );

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}
		$sql = $this->db->_compile_select($this->db->_count_string . $this->db->_protect_identifiers('numrows'));
		$results = $this->datasis->dameval($sql);
		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$arr = $this->datasis->codificautf8($query->result_array());

		echo '{success:true, message:"Loaded data" ,results:'. $results.', data:'.json_encode($arr).'}';
	}


	function tabla() {
		$id   = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;
		$cliente = $this->datasis->dameval("SELECT cod_cli FROM snot WHERE id='$id'");
		$mSQL = "SELECT cod_cli, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM smov WHERE cod_cli='$cliente' AND abonos<>monto AND tipo_doc<>'AB' ORDER BY fecha ";
		$query = $this->db->query($mSQL);
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Cuentas X Cobrar</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']-$row['abonos']).   "</td>";
				$salida .= "</tr>";
				if ( $row['tipo_doc'] == 'FC' or $row['tipo_doc'] == 'ND' or $row['tipo_doc'] == 'GI' )
					$saldo += $row['monto']-$row['abonos'];
				else
					$saldo -= $row['monto']-$row['abonos'];
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		$query->free_result();


/*
		// Revisa formas de pago sfpa
		$mSQL = "SELECT codbanc, numero, monto FROM bmov WHERE transac='$transac' ";
		$query = $this->db->query($mSQL);
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Caja o Banco</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Bco</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['codbanc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table>";
		}
*/
		echo $salida;
	}

	function griditsnot(){
		$numero   = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  0;
		if ($numero == 0 ) $numero = $this->datasis->dameval("SELECT MAX(numero) FROM snot")  ;

		$mSQL = "SELECT * FROM itsnot a JOIN sinv b ON a.codigo=b.codigo WHERE a.numero='$numero' ORDER BY a.codigo";
		$query = $this->db->query($mSQL);
		$results =  0; 
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
		$id = $this->datasis->dameval("SELECT b.id FROM snot a JOIN scli b ON a.cod_cli=b.cliente WHERE numero='$numero'");
		redirect('ventas/scli/dataedit/show/'.$id);
	}

	function snotextjs() {
		$encabeza='NOTAS DE DESPACHO';

		$modulo = 'snot';
		$urlajax = 'ventas/snot/';
		
		$listados= $this->datasis->listados($modulo);
		$otros=$this->datasis->otros($modulo, $urlajax);


		$columnas = "
		{ header: 'Numero',     width: 60, sortable: true, dataIndex: 'numero',     field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Fecha',      width: 70, sortable: true, dataIndex: 'fecha',      field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Cliente',    width: 60, sortable: true, dataIndex: 'cod_cli',    field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Nombre',     width:200, sortable: true, dataIndex: 'nombre',     field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Factura',    width: 60, sortable: true, dataIndex: 'factura',    field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'F.Factura',  width: 70, sortable: true, dataIndex: 'fechafa',    field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'peso',       width: 60, sortable: true, dataIndex: 'peso',       field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'observa1',   width: 60, sortable: true, dataIndex: 'observa1',   field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'observa2',   width: 60, sortable: true, dataIndex: 'observa2',   field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'modificado', width: 60, sortable: true, dataIndex: 'modificado', field: { type: 'date' }, filter: { type: 'date' }},
		{ header: 'Almacen',    width: 60, sortable: true, dataIndex: 'almaorg',    field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'almades',    width: 60, sortable: true, dataIndex: 'almades',    field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'id',         width: 60, sortable: true, dataIndex: 'id',         field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}";

		$coldeta = "
	var Deta1Col = [
		{ header: 'Codigo',      width: 90, sortable: true, dataIndex: 'codigo' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Descripcion', width:250, sortable: true, dataIndex: 'descrip' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cantidad',    width: 80, sortable: true, dataIndex: 'cant' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Saldo',       width: 80, sortable: true, dataIndex: 'saldo' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Entrega',     width: 80, sortable: true, dataIndex: 'entrega' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Factura',     width: 60, sortable: true, dataIndex: 'factura' , field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'id',          width: 60, sortable: true, dataIndex: 'id' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('000')},
		{ header: 'Modificado',  width: 60, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }}
		]";


		$variables='';
		
		$valida="		{ type: 'length', field: 'numero',  min:  1 }";
		

		$funciones = "
function renderScli(value, p, record) {
	var mreto='';
	if ( record.data.cod_cli == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlAjax+'sclibu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.numero );
}


function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}

	";

		$campos = $this->datasis->extjscampos($modulo);

		$stores = "
	Ext.define('It".$modulo."', {
		extend: 'Ext.data.Model',
		fields: [".$this->datasis->extjscampos("it".$modulo)."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlAjax + 'gridit".$modulo."',
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
	var storeIt".$modulo." = Ext.create('Ext.data.Store', {
		model: 'It".$modulo."',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});
	
	//////////////////////////////////////////////////////////
	//
	var gridDeta1 = Ext.create('Ext.grid.Panel', {
		width:   '100%',
		height:  '100%',
		store:   storeIt".$modulo.",
		title:   'Detalle de la NE',
		iconCls: 'icon-grid',
		frame:   true,
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		columns: Deta1Col
	});

	var ".$modulo."TplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR DESPACHO</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/verhtml/PRESUP/{numero}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/ver/PRESUP/{numero}\',     \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',   'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',		
		'</table>','nanai'
	];

	// Al cambiar seleccion
	gridMaest.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridMaest.down('#delete').setDisabled(selectedRecord.length === 0);
			gridMaest.down('#update').setDisabled(selectedRecord.length === 0);
			numero = selectedRecord[0].data.numero;
			gridDeta1.setTitle(selectedRecord[0].data.numero+' '+selectedRecord[0].data.nombre);
			storeIt".$modulo.".load({ params: { numero: numero }});
			var meco1 = Ext.getCmp('imprimir');
			Ext.Ajax.request({
				url: urlAjax +'tabla',
				params: { numero: numero, id: selectedRecord[0].data.id },
				success: function(response) {
					var vaina = response.responseText;
					".$modulo."TplMarkup.pop();
					".$modulo."TplMarkup.push(vaina);
					var ".$modulo."Tpl = Ext.create('Ext.Template', ".$modulo."TplMarkup );
					meco1.setTitle('Imprimir Compra');
					".$modulo."Tpl.overwrite(meco1.body, selectedRecord[0].data );
				}
			});
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
						window.open(urlAjax+'dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},
				{
					iconCls: 'icon-update',
					text: 'Modificar',
					disabled: true,
					itemId: 'update',
					scope: this,
					handler: function(selModel, selections){
						var selection = gridMaest.getView().getSelectionModel().getSelection()[0];
						gridMaest.down('#delete').setDisabled(selections.length === 0);
						window.open(urlAjax+'dataedit/modify/'+selection.data.id, '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
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


		$titulow = 'Compras';
		
		$filtros = "";
		$features = "
		features: [ { ftype: 'filters', encode: 'json', local: false } ],
		plugins: [Ext.create('Ext.grid.plugin.CellEditing', { clicksToEdit: 2 })],
";

		$final = "storeIt".$modulo.".load();";

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
		
		$data['title']  = heading('Notas de Despacho');
		$this->load->view('extjs/extjsvenmd',$data);
		
	}



}