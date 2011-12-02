<?php
//retenciones
 class Rete extends Controller {

  var $data_type = null;
  var $data = null;

  function rete (){
    parent::Controller(); 
    $this->load->library('pi18n');
    $this->load->library("rapyd");
  }
   
  function index(){
    if ( !$this->datasis->iscampo('rete','id') ) {
      $this->db->simple_query('ALTER TABLE rete DROP PRIMARY KEY');
      $this->db->simple_query('ALTER TABLE rete ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
      $this->db->simple_query('ALTER TABLE rete ADD UNIQUE INDEX codigo (codigo)');
      $this->db->simple_query('ALTER TABLE rete ADD COLUMN concepto VARCHAR(5) NULL ');
    }

    $this->datasis->modulo_id(515,1);
    if($this->pi18n->pais=='COLOMBIA'){
      redirect('finanzas/retecol/filteredgrid');
    }else{ 
      //redirect("finanzas/rete/filteredgrid");
      $this->reteextjs();

    }
  }

  function filteredgrid(){
    $this->rapyd->load("datafilter","datagrid");
    $this->rapyd->uri->keep_persistence();
   
    $filter = new DataFilter("Filtro por C&oacute;digo", 'rete');
    $filter->codigo = new inputField("C&oacute;digo", "codigo");
    $filter->codigo->size=15;
	  
    $filter->buttons("reset","search");
    $filter->build();
   
    $uri = anchor('finanzas/rete/dataedit/show/<#codigo#>','<#codigo#>');
   
    $grid = new DataGrid("Lista de Retenciones");
    $grid->order_by("codigo","asc");
    $grid->per_page = 20;
   
    $grid->column("C&oacute;digo",$uri);
    $grid->column("Pago de","activida");
    $grid->column("Base Imponible","base1");
    $grid->column("Porcentaje de Retenci&oacute;n","tari1");
    $grid->column("Para pagos mayores a","pama1");
    $grid->column("Tipo","tipo");
	  
    $grid->add("finanzas/rete/dataedit/create");
    $grid->build();
	  
    $data['content'] = $filter->output.$grid->output;
    $data['title']   = "<h1>Retenciones</h1>";
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);	
  }
   
  function dataedit(){
    $this->rapyd->load("dataedit");
   
    $script ='
    $(function() {
      $(".inputnum").numeric(".");
    });
    ';

    $edit = new DataEdit("Retenciones", "rete");		
    $edit->back_url = site_url("finanzas/rete/filteredgrid");
    $edit->script($script, "create");
    $edit->script($script, "modify");

    $edit->post_process('insert','_post_insert');
    $edit->post_process('update','_post_update');
    $edit->post_process('delete','_post_delete');

    $edit->codigo = new inputField("C&oacute;digo", "codigo");
    $edit->codigo->mode="autohide";
    $edit->codigo->size =7;
    $edit->codigo->maxlength=4;
    $edit->codigo->rule ="required|callback_chexiste";

    $edit->activida = new inputField("Pago de", "activida");
    $edit->activida->size =55;
    $edit->activida->maxlength=45;
    $edit->activida->rule= "strtoupper|required";

    $edit->tipo =  new dropdownField("Tipo", "tipo");
    $edit->tipo->option("JD","JD");
    $edit->tipo->option("JN","JN");
    $edit->tipo->option("NN","NN");
    $edit->tipo->option("NR","NR");
    $edit->tipo->style='width:60px';

    $edit->base1 = new inputField("Base Imponible", "base1");
    $edit->base1->size =13;
    $edit->base1->maxlength=9;
    $edit->base1->css_class='inputnum';
    $edit->base1->rule='numeric';

    $edit->tari1 =new inputField("Porcentaje de Retenci&oacute;n", "tari1");
    $edit->tari1->size =13;
	  $edit->tari1->maxlength=10;
	  $edit->tari1->css_class='inputnum';
	  $edit->tari1->rule='numeric';
	  
	  $edit->pama1 = new inputField("Para pagos mayores a", "pama1");
	  $edit->pama1->size =13;
	  $edit->pama1->maxlength=13;
	  $edit->pama1->css_class='inputnum';
	  $edit->pama1->rule='numeric';
	  
	  $edit->concepto = new inputField("Código Concepto", "concepto");
	  $edit->concepto->size =5;
	  $edit->concepto->maxlength=5;
	  
	  $edit->buttons("modify", "save", "undo", "delete", "back");
	  $edit->build();
   
   
	  $smenu['link']=barra_menu('515');
	  $data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
	  $data['content'] = $edit->output;           
	  $data['title']   = "<h1>Retenciones</h1>";        
	  $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
	  $this->load->view('view_ventanas', $data);  
   }
   
   function _post_insert($do){
	  $codigo=$do->get('codigo');
	  $nombre=$do->get('activida');
	  logusu('rete',"RETENCION $codigo $nombre CREADO");
   }
   
   function _post_update($do){
	  $codigo=$do->get('codigo');
	  $nombre=$do->get('activida');
	  logusu('rete',"RETENCION $codigo $nombre MODIFICADO");
   }
   
   function _post_delete($do){
	  $codigo=$do->get('codigo');
	  $nombre=$do->get('activida');
	  logusu('rete',"RETENCION $codigo $nombre  ELIMINADO ");
   }
   
   function chexiste($codigo){
	  $codigo=$this->input->post('codigo');
	  //echo 'aquiii'.$fecha;
	  $chek=$this->datasis->dameval("SELECT COUNT(*) FROM rete WHERE codigo='$codigo'");
	  if ($chek > 0){
		 $activida=$this->datasis->dameval("SELECT activida FROM rete WHERE codigo='$codigo'");
		 $this->validation->set_message('chexiste',"La retencion $codigo ya existe para la actividad $activida");
		 return FALSE;
	  }else {
	  return TRUE;
	  }	
   }
   
   function instalar(){
	  if (!$this->db->field_exists('ut','rete')) {
		 $mSQL="ALTER TABLE rete CHANGE COLUMN tipocol tipocol CHAR(2) NULL DEFAULT '0.0' COLLATE 'utf8_unicode_ci' AFTER cuenta, ADD COLUMN ut DECIMAL(12,2) NULL DEFAULT NULL AFTER tipocol";
		 $this->db->simple_query($mSQL);
	  }
   }


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"tipo","direction":"ASC"},{"property":"codigo","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select("*, IF(tipo='JD','Juridico Domiciliado', IF(tipo='JN','Juridico No Domiciliado', IF(tipo='NR','Natural Residente','Natural No Residente'))) persona");
		$this->db->from('rete');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('rete');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$codigo = $campos['codigo'];

		if ( !empty($codigo) ) {
			unset($campos['id']);
			unset($campos['persona']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM rete WHERE codigo='$codigo'") == 0)
			{
				$mSQL = $this->db->insert_string("rete", $campos );
				$this->db->simple_query($mSQL);
				logusu('rete',"RETENCION $codigo CREADO");
				echo "{ success: true, message: 'Retencion Agregada'}";
			} else {
				echo "{ success: false, message: 'Ya existe una retencion con ese codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Falta el campo codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		unset($campos['codigo']);
		unset($campos['id']);
		unset($campos['persona']);

		$mSQL = $this->db->update_string("rete", $campos,"id=".$data['data']['id'] );
		$this->db->simple_query($mSQL);
		logusu('rete',"RETENCION $codigo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Retencion Modificada -> ".$data['data']['codigo']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM gser WHERE creten='$codigo'");

		if ($chek > 0){
			echo "{ success: false, message: 'rete no puede ser Borrada'}";
		} else {
			$this->db->simple_query("DELETE FROM rete WHERE codigo='$codigo'");
			logusu('rete',"rete $codigo ELIMINADO");
			echo "{ success: true, message: 'rete Eliminada'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function reteextjs(){
		$encabeza='RETENCION';
		$listados= $this->datasis->listados('rete');
		$otros=$this->datasis->otros('rete', 'finanzas/rete');

		$urlajax = 'finanzas/rete/';
		$variables = "var mcuenta='';";

		$tipos="['JD', 'Juridico Domiciliado'],['JN','Juridico No Domiciliado'],['NR','Natural Residente'],['NN','Natural No Res.']";


		$funciones = "";
		
		$valida = "
		{ type: 'length', field: 'codigo',   min: 1 },
		{ type: 'length', field: 'activida', min: 1 }
		";
		
		$columnas = "
		{ header: 'codigo',     width:  50, sortable: true, dataIndex: 'codigo',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Actividad',  width: 300, sortable: true, dataIndex: 'activida', field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Base Imp.',  width:  50, sortable: true, dataIndex: 'base1',    field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Ret.%',      width:  50, sortable: true, dataIndex: 'tari1',    field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Exencion',   width:  70, sortable: true, dataIndex: 'pama1',    field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Cuenta',     width:  80, sortable: true, dataIndex: 'cuenta',   field: { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Concepto',   width:  80, sortable: true, dataIndex: 'concepto', field: { type: 'textfield' }, filter: { type: 'string'  }},
	";

		$campos = "'id', 'codigo','activida','base1','tari1','pama1','tipo','cuenta', 'persona','concepto'";
		
		$camposforma = "
							{
							xtype:'fieldset',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield',   fieldLabel: 'Codigo',     name: 'codigo',   width:110, allowBlank: false, id: 'codigo' },
									{ xtype: 'combo',       fieldLabel: 'Tipo',       name: 'tipo',     width:270, store: [".$tipos."], labelWidth:70},
									{ xtype: 'textfield',   fieldLabel: 'Actividad',  name: 'activida', width:400, allowBlank: false },
									{ xtype: 'combo',       fieldLabel: 'C.Contable', name: 'cuenta',   width:400, store: cplaStore, id: 'cuenta', mode: 'remote', hideTrigger: true, typeAhead: true, forceSelection: true, valueField: 'item', displayField: 'valor'},
									{ xtype: 'textfield',   fieldLabel: 'Concepto',   name: 'concepto', width:100, allowBlank: true },
								]
							},{
							xtype:'fieldset',
							title: 'Valores',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:170 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'numberfield', fieldLabel: 'Base Imponible',  name: 'base1', width:260, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
									{ xtype: 'numberfield', fieldLabel: 'Retencion %',     name: 'tari1', width:260, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
									{ xtype: 'numberfield', fieldLabel: 'Pagos mayores a', name: 'pama1', width:260, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								]
							}
		";

		$titulow = 'Formas de Pago';

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 340,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						if (registro) {
							mcuenta  = registro.data.cuenta;
							cplaStore.proxy.extraParams.cuenta  = mcuenta  ;
							cplaStore.load({ params: { 'cuenta': registro.data.cuenta,  'origen': 'beforeform' } });
							form.loadRecord(registro);
							form.findField('codigo').setReadOnly(true);
						} else {
							form.findField('codigo').setReadOnly(false);
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
		reader: {type: 'json',totalProperty: 'results',root: 'data'}
	},
	method: 'POST'
});
		";

		$features = "features: [{ ftype: 'grouping', groupHeaderTpl: '{name}' },{ ftype: 'filters', encode: 'json', local: false }],";

		$agrupar = "		remoteSort: true,
		groupField: 'persona',";

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
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;
		$data['agrupar']     = $agrupar;
		
		$data['title']  = heading('Retenciones');
		$this->load->view('extjs/extjsven',$data);

	}

}
?>