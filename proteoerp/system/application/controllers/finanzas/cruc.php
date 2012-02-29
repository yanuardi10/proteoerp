<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//crucecuentas
class Cruc extends validaciones {
	var $data_type = null;
	var $data = null;
	 
	function cruc(){
		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
	}
	
	function index(){
		if ( !$this->datasis->iscampo('cruc','id') ) {
			$this->db->simple_query('ALTER TABLE cruc DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE cruc ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE cruc ADD UNIQUE INDEX numero (numero)');
			echo "Indice ID Creado";
		}
		//redirect($this->url.'filteredgrid');
		$this->datasis->modulo_id(506,1);
		$this->crucextjs();
		//redirect("finanzas/cruc/filteredgrid");
	}
	
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Cruce de Cuentas", 'cruc');
		
		$filter->tipo = new inputField("N&uacute;mero", "numero");
		$filter->tipo->size=15;
		
		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->size=15;
		
		$filter->cliente = new inputField("Cliente", "cliente");
		$filter->cliente->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/cruc/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid("Lista de Cruce de Cuentas");
		$grid->order_by("numero","asc");
		$grid->per_page = 20;

		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Tipo","tipo");
		$grid->column("Proveedor","proveed");
		$grid->column("Nombre","nombre");
		$grid->column("Cliente","cliente");
		$grid->column("Nombre del Cliente","nomcli");
		$grid->column("Concepto","concept1");
									
		$grid->add("finanzas/cruc/dataedit/create");
		$grid->build();
		
    $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cruce de Cuentas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		//proveed .value="";
		//proveed2.value="";
		//nombre  .value="";
		//saldoa  .value="";
		//cliente .value="";
		//cliente2.value="";
		//nomcli  .value="";
		//saldod  .value="";
 		
		$this->rapyd->load("dataedit");		
		$link=site_url('finanzas/cruc/ucruce');
		$script ='
		function sellupa(){			
			$("#tr_proveed ").hide();
			$("#tr_proveed2").hide();		
			$("#tr_cliente ").hide();
			$("#tr_cliente2").hide();
			
			vtipo=$("#tipo").val();
			
			if(vtipo.length>0){
				if(vtipo=="C-C"){					
					$("#tr_proveed2").show();
					$("#tr_cliente ").show();
				}                   
				if(vtipo=="C-P"){   
					$("#tr_proveed2").show();
					$("#tr_cliente2").show();
				}                   
				if(vtipo=="P-C"){   
					$("#tr_proveed ").show();
					$("#tr_cliente ").show();
				}                   
				if(vtipo=="P-P"){   
					$("#tr_proveed ").show();
					$("#tr_cliente2").show();
				}               				
			}else{

			}
		}
		
		function ultimo(){						
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo numero ingresado fue: " + msg );
				}
			});			
		}
		$(function() {
			$(".inputnum").numeric(".");
			$("#tipo").change(function () { sellupa(); }).change();	
		}		
		);
		';
		
		$modbus=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
		'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
		'titulo'  =>'Buscar Proveedor');
		$boton1=$this->datasis->modbus($modbus,'modbus');
		
		$modbus2=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre', 
		'cirepre'=>'Rif/Cedula',
		'dire11'=>'Direcci&oacute;n'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'proveed2','nombre'=>'nombre'),
		'titulo'  =>'Buscar Cliente');
		$boton2=$this->datasis->modbus($modbus2,'modbus2');
		
		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre', 
		'cirepre'=>'Rif/Cedula',
		'dire11'=>'Direcci&oacute;n'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cliente','nombre'=>'nomcli'),
		'titulo'  =>'Buscar Cliente');
		$boton3 =$this->datasis->modbus($mSCLId,'mSCLId');
		
		$mSCLId2=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
		'retornar'=>array('proveed'=>'cliente2','nombre'=>'nomcli'),
		'titulo'  =>'Buscar Proveedor');
		$boton4 =$this->datasis->modbus($mSCLId2,'mSCLId2'); 		
		
		$edit = new DataEdit("Cruce de Cuentas","cruc");
		$edit->back_url = site_url("finanzas/cruc/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$lnum='<a href="javascript:ultimo();" title="Consultar ultimo cruce de cuentas ingresado" onclick="">Consultar ultimo cruce de cuentas</a>';	
		$edit->numero =   new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size = 12;
		$edit->numero->maxlength=8;
		$edit->numero->rule="trim|required|callback_chexiste";
		$edit->numero->append($lnum);
		$edit->numero->group="Datos de cruce";
		
		$edit->fecha =new DateField("Fecha", "fecha");
		$edit->fecha->size = 12;
		$edit->fecha->group="Datos de cruce";
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("C-C","Clientes");
		$edit->tipo->option("C-P","Cliente  - Proveedor");
		$edit->tipo->option("P-C","Proveedor - Cliente");
		$edit->tipo->option("P-P","Proveedores");
		$edit->tipo->style="width:185px";
		$edit->tipo->group="Datos de cruce";
		
		$edit->proveed =  new inputField("Proveedor", "proveed");
		$edit->proveed->db->name="proveed";
		$edit->proveed->size =12;		
		$edit->proveed->rule="trim";
		$edit->proveed->readonly=true;
		$edit->proveed->append($boton1);
		$edit->proveed->group="cuenta 1";
				
		$edit->proveed2 =  new inputField("Cliente", "proveed2");
		$edit->proveed2->db->name="proveed";
		$edit->proveed2->size =12;		
		$edit->proveed2->rule="trim";
		$edit->proveed2->readonly=true;
		$edit->proveed2->append($boton2);
		$edit->proveed2->group="cuenta 1";
		
		$edit->nombre =   new inputField("Nombre", "nombre");
		$edit->nombre->rule="trim";
		$edit->nombre->size =25;
		$edit->nombre->maxlength=40;
		$edit->nombre->group="cuenta 1";
		
		$edit->saldoa =   new inputField("Saldo Anterior", "saldoa");
		$edit->saldoa->size=25;
		$edit->saldoa->maxlength=16;
		$edit->saldoa->css_class='inputnum';
		$edit->saldoa->rule='trim|numeric';
		$edit->saldoa->group="cuenta 1";
		
		$edit->cliente =  new inputField("Cliente", "cliente");
		$edit->cliente->db->name="cliente";
		$edit->cliente->rule="trim";
		$edit->cliente->size =12;
		$edit->cliente->readonly=true;
		$edit->cliente->append($boton3);
		$edit->cliente->group="cuenta 2";
		
		$edit->cliente2 =  new inputField("Proveedor", "cliente2");
		$edit->cliente2->db->name="cliente";
		$edit->cliente2->rule="trim";
		$edit->cliente2->size =12;
		$edit->cliente2->readonly=true;
		$edit->cliente2->append($boton4);
		$edit->cliente2->group="cuenta 2";
		
		$edit->nomcli =   new inputField("Nombre", "nomcli");
		//$edit->nomcli->db->name="nomcli";		
		$edit->nomcli->rule="trim";
	  $edit->nomcli->size =25;
	  $edit->nomcli->maxlength=40;
	  $edit->nomcli->group="cuenta 2";
	  
		$edit->saldod =   new inputField("Saldo Deudor", "saldod");
		$edit->saldod->size =25;
		$edit->saldod->maxlength=16;
		$edit->saldod->css_class='inputnum';
		$edit->saldod->rule='trim|numeric';
		$edit->saldod->group="cuenta 2";
		
		$edit->codbanc =  new dropdownField("C&oacute;digo de banco", "codbanc");		
		$edit->codbanc->options("select codbanc,banco from banc order by codbanc");
		$edit->codbanc->style="width:185px";
		$edit->codbanc->group="Datos de banco";
		
		$edit->monto =    new inputField("Monto","monto");
		$edit->monto->size =25;
		$edit->monto->maxlength= 16;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric'; 
		$edit->monto->group="Datos de banco";
		
		$edit->concept1 = new inputField("Concepto","concept1");
		$edit->concept1->size =41;
		$edit->concept1->maxlength=40;
		$edit->concept1->rule="trim";
		$edit->concept1->group="Datos de banco";
		
		$edit->concept2 = new inputField(".","concept2");
		$edit->concept2->rule="trim";
		$edit->concept2->size =41;
		$edit->concept2->maxlength=40;
		$edit->concept2->group="Datos de banco";
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$smenu['link']=barra_menu('506');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
    $data['content'] = $edit->output;           
    $data['title']   = "<h1>Cruce de Cuentas</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('cruc',"CRUCE DE CUENTA $codigo CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('cruc',"CRUCE DE CUENTA $codigo MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('cruc',"CRUCE DE CUENTA $codigo ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('numero');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM cruc WHERE numero='$codigo'");
		if ($chek > 0){
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	function ucruce(){
		$consulcruce=$this->datasis->dameval("SELECT numero FROM cruc ORDER BY numero DESC");
		echo $consulcruce;
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'cruc');

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('cruc');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'id', 'desc' );

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
		//$transac = $this->datasis->dameval("SELECT transac FROM cruc WHERE id='$id'");
	}

	function griditcruc(){
		$numero   = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  '';
		
		if ($numero == '' ) $numero = $this->datasis->dameval("SELECT MAX(numero) FROM cruc")  ;
		$mSQL = "SELECT * FROM itcruc WHERE numero='$numero' ORDER BY tipo";
		$query = $this->db->query($mSQL);
		$results = $query->num_rows();
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data '.$numero.'" ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crucextjs() {
		$encabeza='CRUCE DE CUENTAS';

		$modulo = 'cruc';
		$urlajax = 'finanzas/cruc/';
		$listados= $this->datasis->listados($modulo);
		$otros=$this->datasis->otros($modulo, $urlajax);

		$columnas = "
			{ header: 'Numero',     width: 70, sortable: true, dataIndex: 'numero',     field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Fecha',      width: 70, sortable: true, dataIndex: 'fecha',      field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Tipo',       width: 40, sortable: true, dataIndex: 'tipo',       field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Proveed',    width: 50, sortable: true, dataIndex: 'proveed',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Nombre',     width:150, sortable: true, dataIndex: 'nombre',     field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Cliente',    width: 50, sortable: true, dataIndex: 'cliente',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Nomcli',     width:150, sortable: true, dataIndex: 'nomcli',     field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Monto',      width: 70, sortable: true, dataIndex: 'monto',      field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Concept1',   width:100, sortable: true, dataIndex: 'concept1',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Concept2',   width:100, sortable: true, dataIndex: 'concept2',   field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Codbanc',    width: 40, sortable: true, dataIndex: 'codbanc',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Banco',      width: 70, sortable: true, dataIndex: 'banco',      field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Transac',    width: 60, sortable: true, dataIndex: 'transac',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Estampa',    width: 70, sortable: true, dataIndex: 'estampa',    field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Hora',       width: 60, sortable: true, dataIndex: 'hora',       field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Usuario',    width: 60, sortable: true, dataIndex: 'usuario',    field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Modificado', width: 60, sortable: true, dataIndex: 'modificado', field: { type: 'date' }, filter: { type: 'date' }},
";

		$coldeta = "
	var Deta1Col = [
			{ header: 'Numero',     width: 80, sortable: true, dataIndex: 'numero' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Tipo',       width: 40, sortable: true, dataIndex: 'tipo' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Numero',     width: 90, sortable: true, dataIndex: 'onumero' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Fecha',      width: 70, sortable: true, dataIndex: 'ofecha' , field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Monto',      width: 80, sortable: true, dataIndex: 'monto' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Modificado', width: 80, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }},
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
		fields: [".$this->datasis->extjscampos("itcruc")."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlAjax + 'griditcruc',
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
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR CRUCE</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'finanzas/cruc_add/dataprint/modify/{id}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'finanzas/cruc_add/dataprint/modify/{id}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',  'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
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
						window.open(urlApp+'ventas/cruc_add/dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
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
						window.open(urlApp+'ventas/cruc_add/dataedit/modify/'+selection.data.id, '_blank', 'width=900,height=730,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
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


		$titulow = 'Cruces';

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

		$data['title']  = heading('Cruce de Cuentas');
		$this->load->view('extjs/extjsvenmd',$data);

	}


}
?>