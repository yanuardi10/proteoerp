<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class apan extends validaciones {

	function apan(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		if ( !$this->datasis->iscampo('apan','id') ) {
			$this->db->simple_query('ALTER TABLE apan DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE apan ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE apan ADD UNIQUE INDEX numero (numero)');
			echo "Indice ID Creado";
		}
		$this->datasis->modulo_id(505,1);
		$this->apanextjs();
		//redirect("finanzas/apan/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por N&uacute;mero", 'apan');

		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size=12;
		$filter->numero->maxlength=8;

		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=12;
		$filter->nombre->maxlength=30;

		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->option("","");
		$filter->tipo->option("C","C");
		$filter->tipo->option("P","P");
		$filter->tipo->style="width:100px";

		$filter->buttons("reset","search");
		$filter->build('dataformfiltro');

		$uri = anchor('finanzas/apan/dataedit/<#tipo#>/show/<#id#>','<#numero#>');
		$uri_2 = anchor('finanzas/apan/dataedit/<#tipo#>/show/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));

		$grid = new DataGrid("Lista de Aplicaci&oacute;n de Anticipos");
		$grid->order_by("numero","asc");
		$grid->per_page = 50;

		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby("N&uacute;mero",$uri,'numero');
		$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
		$grid->column_orderby("Tipo","tipo",'tipo');
		$grid->column_orderby("Clipro","clipro",'clipro');
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column_orderby("Observaci&oacute;n 1","observa1",'observa1');
		$grid->column_orderby("Observaci&oacute;n 2","observa2",'observa2');
		$grid->column("Monto","<nformat><#monto#>|2|,|.</nformat>" ,'align=right');
		$grid->column("Reinte","<nformat><#reinte#>|2|,|.</nformat>" ,'align=right');
			
		//$grid->add("finanzas/apan/dataedit/create");
		$grid->build('datagridST');

		//********** SUPER TABLE *************
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
    width: 740px; /* Required to set */
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

		$data['title']  = heading('Aplicaci&oacute;n de Anticipos');
		$data['script']   = script('jquery.js');
		$data["script"]  .= script('superTables.js');
		$data['head']  = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit($tipo)	{
		$this->rapyd->load('dataobject','datadetails');
		$do = new DataObject("apan");
		$title="";
		if($tipo=='P'){
			$do->rel_one_to_many('itppro', 'itppro', array('transac'=>'transac'));
			$title='itppro';
		}
		else {
			$do->rel_one_to_many('itccli', 'itccli', array('transac'=>'transac'));
			$title='itccli';
		}


		$edit = new DataDetails('Aplicaci&oacute;n de Anticipos', $do);
		$edit->back_url = site_url('finanzas/apan/filteredgrid');
		$edit->set_rel_title($title,'Anticipo <#o#>');

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size =12;
		$edit->numero->rule="trim|required";
		$edit->numero->maxlength=8;

		$edit->fecha = new DateonlyField("Fecha", "fecha");
		$edit->fecha->size = 12;
		$edit->fecha->rule="required|chfecha";
		$edit->fecha->insertValue = date("Y-m-d");

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("C","Cliente");
		$edit->tipo->option("P","Proveedor");
		$edit->tipo->style="width:100px";
			
		$edit->clipro =new inputField("Codigo", "clipro");
		$edit->clipro->rule='trim|required';
		$edit->clipro->size =12;
		$edit->clipro->readonly=true;

		$edit->nombre =   new inputField("Nombre", "nombre");
		$edit->nombre->size =30;
		$edit->nombre->rule = "trim|strtoupper";
		$edit->nombre->readonly=true;

		$edit->monto =    new inputField("Monto", "monto");
		$edit->monto->size = 12;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric';
		$edit->monto->maxlengxlength=0;
		$edit->monto->rule='positive';

		$edit->reinte =   new inputField("Convertido", "reinte");
		$edit->reinte->rule='trim|required';
		$edit->reinte->size =12;
		$edit->reinte->readonly=true;

		$edit->nombreintes=new inputField("Nombre","nombreintes");
		$edit->nombreintes->size=30;
		$edit->nombreintes->readonly=true;

		$edit->observa1 = new inputField("Observaciones", "observa1");
		$edit->observa1->rule='trim';
		$edit->observa1->size =50;
		$edit->observa1->maxlength=50;

		$edit->observa2 = new inputField("", "observa2");
		$edit->observa2->rule='trim';
		$edit->observa2->size =50;
		$edit->observa2->maxlength=50;

		//Detalles itppro
		if($tipo=='P'){
			$edit->tipoppro = new inputField("Tipo <#o#>","tipoppro_<#i#>");
			$edit->tipoppro->db_name = "tipoppro";
			$edit->tipoppro->rel_id  = 'itppro';
			$edit->tipoppro->rule='trim|required';
			$edit->tipoppro->size =10;
			$edit->tipoppro->readonly=true;

			$edit->tipo_doc = new inputField("Tipo Documento <#o#>","tipo_doc_<#i#>");
			$edit->tipo_doc->db_name = "tipo_doc";
			$edit->tipo_doc->rel_id  = 'itppro';
			$edit->tipo_doc->rule='trim|required';
			$edit->tipo_doc->size =10;
			$edit->tipo_doc->readonly=true;

			$edit->itnumero = new inputField("N&uacute;mero <#o#>","itnumero_<#i#>");
			$edit->itnumero->db_name = "numero";
			$edit->itnumero->rel_id  = 'itppro';
			$edit->itnumero->rule='trim|required';
			$edit->itnumero->size =10;
			$edit->itnumero->readonly=true;

			$edit->itnumppro = new inputField("N&uacute;mero <#o#>","itnumppro_<#i#>");
			$edit->itnumppro->db_name = "numppro";
			$edit->itnumppro->rel_id  = 'itppro';
			$edit->itnumppro->rule='trim|required';
			$edit->itnumppro->size =10;
			$edit->itnumppro->readonly=true;

			$edit->itfechap = new DateonlyField("Fecha", "itfechap_<#i#>");
			$edit->itfechap->db_name = "fecha";
			$edit->itfechap->rel_id  = 'itppro';
			$edit->itfechap->size = 12;
			$edit->itfechap->rule="required|chfecha";
			$edit->itfechap->insertValue = date("Y-m-d");

			$edit->itmontop = new inputField("Monto <#o#>", "itmontop_<#i#>");
			$edit->itmontop->db_name='monto';
			$edit->itmontop->css_class='inputnum';
			$edit->itmontop->rel_id   ='itppro';
			$edit->itmontop->size=3;
			$edit->itmontop->rule='positive';

			$edit->itabonop = new inputField("Abono <#o#>", "itabonop_<#i#>");
			$edit->itabonop->db_name='abono';
			$edit->itabonop->css_class='inputnum';
			$edit->itabonop->rel_id   ='itppro';
			$edit->itabonop->size=3;
			$edit->itabonop->rule='positive';
		}
		//Detalles itccli
		if($tipo=='C'){
			$edit->tipoccli = new inputField("Tipo <#o#>","tipoccli_<#i#>");
			$edit->tipoccli->db_name = "tipoccli";
			$edit->tipoccli->rel_id  = 'itccli';
			$edit->tipoccli->rule='trim|required';
			$edit->tipoccli->size =10;
			$edit->tipoccli->readonly=true;

			$edit->tipo_doc_c = new inputField("Tipo Documento <#o#>","tipo_doc_C<#i#>");
			$edit->tipo_doc_c->db_name = "tipo_doc";
			$edit->tipo_doc_c->rel_id  = 'itccli';
			$edit->tipo_doc_c->rule='trim|required';
			$edit->tipo_doc_c->size =10;
			$edit->tipo_doc_c->readonly=true;

			$edit->itnumero_c = new inputField("N&uacute;mero <#o#>","itnumero_c_<#i#>");
			$edit->itnumero_c->db_name = "numero";
			$edit->itnumero_c->rel_id  = 'itccli';
			$edit->itnumero_c->rule='trim|required';
			$edit->itnumero_c->size =10;
			$edit->itnumero_c->readonly=true;

			$edit->numccli = new inputField("N&uacute;mero <#o#>","numccli_<#i#>");
			$edit->numccli->db_name = "numccli";
			$edit->numccli->rel_id  = 'itccli';
			$edit->numccli->rule='trim|required';
			$edit->numccli->size =10;
			$edit->numccli->readonly=true;

			$edit->itfechac = new DateonlyField("Fecha", "itfechac_<#i#>");
			$edit->itfechac->db_name = "fecha";
			$edit->itfechac->rel_id  = 'itccli';
			$edit->itfechac->size = 12;
			$edit->itfechac->rule="required|chfecha";
			$edit->itfechac->insertValue = date("Y-m-d");

			$edit->itmontoc = new inputField("Monto <#o#>", "itmontoc_<#i#>");
			$edit->itmontoc->db_name='monto';
			$edit->itmontoc->css_class='inputnum';
			$edit->itmontoc->rel_id   ='itccli';
			$edit->itmontoc->size=3;
			$edit->itmontoc->rule='positive';

			$edit->itabonoc = new inputField("Abono <#o#>", "itabonoc_<#i#>");
			$edit->itabonoc->db_name='abono';
			$edit->itabonoc->css_class='inputnum';
			$edit->itabonoc->rel_id   ='itccli';
			$edit->itabonoc->size=3;
			$edit->itabonoc->rule='positive';
		}
		///fin de detalles
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_apan', $conten,true);
		$data['title']   = "<h1>Aplicaci&oacute;n de Anticipos</h1>";
		$data["script"]  = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		//$sql="ALTER TABLE `apan`  DROP PRIMARY KEY";
		//$this->db->query($sql);
		$sql="ALTER TABLE `apan`  ADD COLUMN `id` INT(10) NULL AUTO_INCREMENT AFTER `usuario`,  ADD PRIMARY KEY (`id`)";
		$this->db->query($sql);
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'apan');

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('apan');

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
		//$transac = $this->datasis->dameval("SELECT transac FROM apan WHERE id='$id'");
	}

	function griditapan(){
		$numero   = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  '';
		if ($numero == '' ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM apan ")  ;
		} else
			$id = $this->datasis->dameval("SELECT id FROM apan WHERE numero='$numero' ")  ;

		$transac  =  $this->datasis->dameval("SELECT transac FROM apan WHERE id=$id ")  ;
		
	
		$mSQL = "
SELECT
'1' origen, cod_cli, fecha, tipo_doc, numero, monto, abono, ppago, reten, reteiva
FROM itccli WHERE transac='$transac' 
UNION ALL
SELECT
'2' origen, cod_prv, fecha, tipo_doc, numero, monto, abono, ppago, reten, reteiva
FROM itppro WHERE transac='$transac'
";


		$query = $this->db->query($mSQL);
		$results =  $query->num_rows();
		$mSQL = '';
		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data"'.$mSQL.' ,results:'. $results.', data:'.json_encode($arr).'}';

	}

	function apanextjs() {
		$encabeza='apanE DE CUENTAS';

		$modulo = 'apan';
		$urlajax = 'finanzas/apan/';
		$listados= $this->datasis->listados($modulo);
		$otros=$this->datasis->otros($modulo, $urlajax);

		$columnas = "
			{ header: 'Numero',    width: 60, sortable: true, dataIndex: 'numero' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Fecha',     width: 70, sortable: true, dataIndex: 'fecha' , field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Tipo',      width: 50, sortable: true, dataIndex: 'tipo' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Clipro',    width: 50, sortable: true, dataIndex: 'clipro' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Nombre',    width:180, sortable: true, dataIndex: 'nombre' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Monto',     width: 70, sortable: true, dataIndex: 'monto' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
			{ header: 'Reinte',    width: 60, sortable: true, dataIndex: 'reinte' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Observa1',  width:200, sortable: true, dataIndex: 'observa1' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Observa2',  width:200, sortable: true, dataIndex: 'observa2' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Transac',   width: 60, sortable: true, dataIndex: 'transac' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Estampa',   width: 70, sortable: true, dataIndex: 'estampa' , field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Hora',      width: 60, sortable: true, dataIndex: 'hora' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Usuario',   width: 60, sortable: true, dataIndex: 'usuario' , field: { type: 'textfield' }, filter: { type: 'string' }},
			{ header: 'Modificado',width: 70, sortable: true, dataIndex: 'modificado' , field: { type: 'date' }, filter: { type: 'date' }},
			{ header: 'Id',        width: 60, sortable: true, dataIndex: 'id' , field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}
";

		$coldeta = "
	var Deta1Col = [
		{ header: 'O',       width: 20, sortable: true, dataIndex: 'origen',   field: { type: 'textfield' },  filter: { type: 'string' }},
		//{ header: 'Cliente', width: 50, sortable: true, dataIndex: 'cod_prv',  field: { type: 'textfield' },  filter: { type: 'string' }},
		{ header: 'Tipo',    width: 40, sortable: true, dataIndex: 'tipo_doc', field: { type: 'textfield' },  filter: { type: 'string' }},
		{ header: 'Numero',  width: 70, sortable: true, dataIndex: 'numero',   field: { type: 'textfield' },  filter: { type: 'string' }},
		{ header: 'Fecha',   width: 80, sortable: true, dataIndex: 'fecha',    field: { type: 'date' },       filter: { type: 'date' }},
		{ header: 'Monto',   width: 80, sortable: true, dataIndex: 'monto',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Abono',   width: 80, sortable: true, dataIndex: 'abono',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'P.Pago',  width: 80, sortable: true, dataIndex: 'ppago',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Reten',   width: 80, sortable: true, dataIndex: 'reten',    field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Reteiva', width: 60, sortable: true, dataIndex: 'reteiva',  field: { type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Nroriva', width: 60, sortable: true, dataIndex: 'nroriva',  field: { type: 'textfield' },  filter: { type: 'string' }},
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
		fields: [".$this->datasis->extjscampos("itccli")."],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlAjax + 'griditapan',
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
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR apanE</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'finanzas/apan_add/dataprint/modify/{id}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'finanzas/apan_add/dataprint/modify/{id}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',  'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
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
						window.open(urlApp+'ventas/apan_add/dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
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
						window.open(urlApp+'ventas/apan_add/dataedit/modify/'+selection.data.id, '_blank', 'width=900,height=730,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
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


		$titulow = 'apanes';

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

		$data['title']  = heading('apane de Cuentas');
		$this->load->view('extjs/extjsvenmd',$data);

	}

}

?>