<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class aran extends validaciones {

	function aran(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(204,1);
	}

	function index(){
		if ( !$this->datasis->iscampo('aran','id') ) {
			$this->db->simple_query('ALTER TABLE aran DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE aran ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE aran ADD UNIQUE INDEX codigo (codigo)');
		}
		//redirect('import/aran/filteredgrid');
		redirect('import/aran/extgrid');
	}

	function extgrid(){
		$this->datasis->modulo_id(204,1);
		$this->aranextjs();
	}


	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro','aran');

		$filter->linea = new inputField('Descripci&oacute;n','descrip');
		$filter->linea->size=20;

		$filter->unidad = new dropdownField('Unidad','unidad');
		$filter->unidad->style='width:180px;';
		$filter->unidad->option('','Seleccionar');
		$filter->unidad->options('SELECT unidades, unidades as valor FROM unidad ORDER BY unidades');

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('import/aran/dataedit/show/<raencode><#codigo#></raencode>','<#codigo#>');

		$grid = new DataGrid('Lista de Arancenles');
		$grid->order_by('codigo','asc');
		$grid->per_page = 20;

		$grid->column_orderby('C&oacute;digo'     ,$uri     ,'codigo' ,'align=\'center\'');
		$grid->column_orderby('Descripci&oacute;n','descrip','descrip','align=\'left\''  );
		$grid->column_orderby('Tarifa'            ,'<nformat><#tarifa#></nformat>' ,'tarifa' ,'align=\'right\'' );
		$grid->column_orderby('Unidad'            ,'unidad' ,'unidad' ,'align=\'right\'' );
		$grid->column_orderby('D&oacute;lar'      ,'<nformat><#dolar#></nformat>' ,'dolar' ,'align=\'right\'' );

		$grid->add('import/aran/dataedit/create','Agregar nuevo arancel');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Aranceles</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','dataedit');

		$edit = new DataEdit('Lista de aranceles','aran');
		$edit->back_url = site_url('import/aran/filteredgrid');

		//$edit->post_process('insert','_post_insert');
		//$edit->post_process('update','_post_update');
		$edit->pre_process( 'delete','_pre_del'    );
		$edit->post_process('delete','_post_delete');

		$edit->codigo =  new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->mode='autohide';
		$edit->codigo->rule ='trim|strtoupper|required';
		$edit->codigo->size = '20';
		$edit->codigo->maxlength=15;

		$edit->descrip =  new inputField('Descripci&oacute;n', 'descrip');
		$edit->descrip->rule ='trim|strtoupper|required';

		$edit->unidad = new dropdownField('Unidad','unidad');
		$edit->unidad->rule ='required';
		$edit->unidad->style='width:180px;';
		$edit->unidad->option('','Seleccionar');
		$edit->unidad->options('SELECT unidades, unidades as valor FROM unidad ORDER BY unidades');

		$edit->tarifa = new inputField('Tarifa', 'tarifa');
		$edit->tarifa->size = 10;
		$edit->tarifa->maxlength=10;
		$edit->tarifa->css_class='inputnum';
		$edit->tarifa->rule='callback_positivo|numeric|required';

		$edit->dolar = new inputField('D&oacute;lar', 'dolar');
		$edit->dolar->size = 10;
		$edit->dolar->maxlength=10;
		$edit->dolar->css_class='inputnum';
		$edit->dolar->rule='callback_positivo|numeric';

		$edit->buttons('modify','save','undo','delete','back');
		$edit->build();

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $edit->output;
		$data['title']   = '<h1>Aranceles</h1>';
		$data['head']    = script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		logusu('grup',"ARANCEL $codigo ELIMINADO");
	}

	function _pre_del($do) {
		$codigo=$this->db->escape($do->get('codigo'));
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM itordi WHERE codaran=$codigo");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='El arancel a borra contiene productos relacionados, por ello no puede ser eliminado.';
			return false;
		}
		return true;
	}

	function instalar(){
		$mSQL="CREATE TABLE `aran` (
		 `codigo` varchar(15) NOT NULL DEFAULT '',
		 `descrip` text,
		 `tarifa` decimal(8,2) DEFAULT '0.00',
		 `unidad` varchar(20) DEFAULT NULL,
		 PRIMARY KEY (`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
		$this->db->simple_query($mSQL);

		$mSQL="ALTER TABLE `aran`  ADD COLUMN `dolar` DECIMAL(8,2) NULL AFTER `unidad`";
		$this->db->simple_query($mSQL);
	}

	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : 'codigo';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = "codigo IS NOT NULL ";

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
				//Dummy Where.
				$where = " codigo IS NOT NULL ";
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
		$this->db->select('*');
		$this->db->from('aran');

		if (strlen($where)>1){
			$this->db->where($where, NULL, FALSE);
		}

		$sort = json_decode($sort, true);

		if ( count($sort) == 0 ) $this->db->order_by( 'codigo', 'asc' );

		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $this->db->count_all('aran');

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


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$codigo = $campos['codigo'];

		if ( !empty($codigo) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM aran WHERE codigo='$codigo'") == 0)
			{
				$mSQL = $this->db->insert_string("aran", $campos );
				$this->db->simple_query($mSQL);
				logusu('aran',"ARANCEL $codigo CREADO");
				echo "{ success: true, message: 'Arancel Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un Arancel con ese Codigo!!'}";
			}

		} else {
			echo "{ success: false, message: 'Ya existe un Arancel con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		unset($campos['codigo']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("aran", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('aran',"ARANCEL $codigo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Arancel Modificado -> ".$data['data']['codigo']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM itordi WHERE arancel='$codigo'");

		if ($chek > 0){
			echo "{ success: false, message: 'Arancel no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM aran WHERE codigo='$codigo'");
			logusu('aran',"ARANCEL $codigo ELIMINADO");
			echo "{ success: true, message: 'Arancel Eliminado'}";
		}
	}

//****************************************************************
//
//
//
//****************************************************************
	function aranextjs(){

		$encabeza='ARANCELES ADUANEROS';
		$listados= $this->datasis->listados('aran');
		$otros=$this->datasis->otros('aran', 'aran');

		$mSQL     = "SELECT unidades, unidades descrip FROM unidad ORDER BY unidades";
		$unidades = $this->datasis->llenacombo($mSQL);;

		$urlajax = 'import/aran/';
		$variables = "";
		$funciones = "";
		$valida = "
		{ type: 'length', field: 'codigo', min: 1 },
		{ type: 'length', field: 'descrip', min: 1 }
		";


		$columnas = "
		{ header: 'id',          width:  30, sortable: true, dataIndex: 'id' },
		{ header: 'Codigo',      width:  90, sortable: true, dataIndex: 'codigo',  field: { type: 'textfield' }, filter: { type: 'string' } },
		{ header: 'Descripcion', width: 200, sortable: true, dataIndex: 'descrip', field: { type: 'textfield' }, filter: { type: 'string' } },
		{ header: 'Unidad',      width:  90, sortable: true, dataIndex: 'unidad',  field: { type: 'textfield' }, filter: { type: 'string' } },
		{ header: 'Tarifa',      width:  90, sortable: true, dataIndex: 'tarifa',  field: { type: 'numberfield'}, filter: { type: 'number' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'Cambio',      width:  90, sortable: true, dataIndex: 'dolar',   field: { type: 'numberfield'}, filter: { type: 'number' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}
	";

		$campos = "'id', 'codigo', 'descrip', 'unidad', 'tarifa', 'dolar'";

		$camposforma = "
							{
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							items:[								{ xtype: 'textfield',   fieldLabel: 'Codigo',      name: 'codigo',  allowBlank: false, width: 200, id: 'codigo' },
								{ xtype: 'textfield',   fieldLabel: 'Descripcion', name: 'descrip', allowBlank: false, width: 400, },
								{ xtype: 'combo',       fieldLabel: 'Empaque',     name: 'unidad',  store: [".$unidades."], width: 180 },
								{ xtype: 'numberfield', fieldLabel: 'Tarifa ',     name: 'tarifa',  hideTrigger: true, fieldStyle: 'text-align: right', width:160,renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Cambio ',     name: 'dolar',   hideTrigger: true, fieldStyle: 'text-align: right', width:160,renderer : Ext.util.Format.numberRenderer('0,000.00') },
							]
							}
		";

		$titulow = 'Aranceles';

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

		$data['title']  = heading('Aranceles');
		$this->load->view('extjs/extjsven',$data);
	}

}
