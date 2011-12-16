<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//proveed
class notabu extends validaciones {

	function notabu(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		if ( !$this->datasis->iscampo('notabu','id') ) {
			$this->db->simple_query('ALTER TABLE notabu DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE notabu ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE notabu ADD UNIQUE INDEX princi (contrato, ano, mes, dia)');
		}
		$this->datasis->modulo_id(707,1);
		$this->notabuextjs();
		//redirect("nomina/notabu/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->datasis->modulo_id(707,1);


		$filter = new DataFilter("Filtro", 'notabu');
				
		$filter->contrato = new dropdownField("Contrato","contrato");
		$filter->contrato->style ="width:400px;";
		$filter->contrato->option("","");
		$filter->contrato->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
				
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('nomina/notabu/dataedit/show/<#contrato#>/<#ano#>/<#mes#>/<#dia#>','<#contrato#>');

		$grid = new DataGrid("Lista");
		$grid->order_by("contrato","asc");
		$grid->per_page = 20;

		$grid->column("Contrato",$uri);
		$grid->column("A&nacute;o","ano");
		$grid->column("Mes","mes");
		$grid->column("Dia","dia");
		$grid->column("Preaviso","preaviso","align=right");
		$grid->column("Vacaciones","vacacion","align=right");
		$grid->column("Bono Vacacional","bonovaca","align=right");
		$grid->column("Antiguedad","antiguedad","align=right");
		$grid->column("Utilidades","utilidades","align=right");
	
		//$grid->add("nomina/notabu/dataedit/create");
		$salida=anchor("nomina/notabu/calcautilidades","Cambiar utilidades en base a monto anual");
		$grid->build();
		
		$data['content'] = $filter->output.$salida.$grid->output;
		$data['title']   = "<h1>Definici&oacute;n de Utilidades</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	
	function dataedit(){
		
		$this->rapyd->load("dataedit2");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		
		';	
				
		$edit = new DataEdit2(" ", "notabu");
		$edit->back_url = site_url("nomina/notabu/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
					  
		$edit->contrato = new dropdownField("Contrato","contrato");
		$edit->contrato->style ="width:400px;";
		$edit->contrato->option("","");
		$edit->contrato->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
		$edit->contrato->group = "Relaci&oacute;n Laboral";
					
		$edit->ano = new inputField("A�o","ano");
		$edit->ano->size =3;
		$edit->ano->maxlength=2;
		$edit->ano->rule="trim|numeric";
		$edit->ano->css_class='inputnum';
		
		$edit->mes = new inputField("Mes","mes");
		$edit->mes->size =3;
		$edit->mes->maxlength=2;
		$edit->mes->rule="trim|numeric";
		$edit->mes-> css_class='inputnum';
		
		$edit->dia = new inputField("Dia","dia");
		$edit->dia->size =3;
		$edit->dia->maxlength=2;
		$edit->dia->rule="trim|numeric";
		$edit->dia-> css_class='inputnum';

		$edit->preaviso = new inputField("Preaviso","preaviso");
		$edit->preaviso->size =9;
		$edit->preaviso->maxlength=7;
		$edit->preaviso->rule="trim|numeric";
		$edit->preaviso-> css_class='inputnum';

		$edit->vacacion = new inputField("Vacaciones","vacacion");
		$edit->vacacion->size =9;
		$edit->vacacion->maxlength=7;
		$edit->vacacion->rule="trim|numeric";
		$edit->vacacion-> css_class='inputnum';
		
		$edit->bonovaca = new inputField("Bono Vacacional","bonovaca");
		$edit->bonovaca->size =9;
		$edit->bonovaca->maxlength=7;
		$edit->bonovaca->rule="trim|numeric";
		$edit->bonovaca-> css_class='inputnum';
		
		$edit->antiguedad = new inputField("Antiguedad","antiguedad");
		$edit->antiguedad->size =9;
		$edit->antiguedad->maxlength=7;
		$edit->antiguedad->rule="trim|numeric";
		$edit->antiguedad-> css_class='inputnum';

		$edit->utilidades = new inputField("Utilidades","utilidades");
		$edit->utilidades->size =9;
		$edit->utilidades->maxlength=7;
		$edit->utilidades->rule="trim|numeric";
		$edit->utilidades-> css_class='inputnum';
		
		$edit->buttons("modify","save", "undo","back");
		$edit->build();
		
		$data['content'] = $edit->output; 
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Definici�n de Utilidades</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function calcautilidades(){
		$this->rapyd->load("dataform");
		
		$script='
			$(".inputnum").numeric(".");
		';
		
		$form = new DataForm('nomina/notabu/calcautilidades/process');
		$form->back_url = site_url("nomina/notabu/filteredgrid");
		$form->script($script);
		
		 
		$form->contrato = new dropdownField("Contrato","contrato");
		$form->contrato->style ="width:400px;";
		$form->contrato->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
		$form->contrato->rule="required";
		 
		$form->monto = new inputField("Monto de dias anual para calcular utilidades","monto");
		$form->monto->style    ="width:400px;";
		$form->monto->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
		$form->monto->rule     ="required";
		$form->monto->size     = 10;
		$form->monto->css_class='inputnum';
		
		$form->submit("btnsubmit","Cambiar");
		$form->build_form();
		
		if ($form->on_success()){
			$contrato=$this->db->escape($form->contrato->newValue);
			$monto   =$form->monto->newValue;
			
			$query = "UPDATE notabu SET utilidades=IF(ano>=1,$monto,($monto/12)*mes+($monto/24)*IF(dia>=15,1,0)) WHERE contrato =$contrato";
			$this->db->query($query);
		}
		
		$salida=anchor("nomina/notabu/filteredgrid","Regresar al filtro");
		
		$data['content'] = $form->output.$salida;
		$data['title']   = 'Cambiar Utilidades basado a monto anual';
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function _post_insert($do){
		$contrato=$do->get('contrato');
		$anio=$do->get('anio');
		$mes=$do->get('mes');
		$dia=$do->get('dia');	
		logusu('notabu',"CONFIGURACION DE NOMINA $contrato $anio $mes $dia  REGISTRADA");
	}
	function _post_update($do){
		$contrato=$do->get('contrato');
		$anio=$do->get('anio');
		$mes=$do->get('mes');
		$dia=$do->get('dia');	
		logusu('notabu',"CONFIGURACION DE NOMINA $contrato $anio $mes $dia  MODIFICADA");
	}
	function _post_delete($do){
		$contrato=$do->get('contrato');
		$anio=$do->get('anio');
		$mes=$do->get('mes');
		$dia=$do->get('dia');	
		logusu('notabu',"CONFIGURACION DE NOMINA $contrato $anio $mes $dia  ELIMINADA");
	}

	
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']  :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']  : 30;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']   : '[{"property":"contrato","direction":"ASC"},{"property":"ano","direction":"ASC"},{"property":"mes","direction":"ASC"},{"property":"dia","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('notabu');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('notabu');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		
		$contrato = $campos['contrato'];
		$ano = $campos['ano'];
		$mes = $campos['mes'];
		$dia = $campos['dia'];

		if ( !empty($contrato) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM notabu WHERE contrato='$contrato' AND ano=$ano AND mes=$mes AND dia=$dia") == 0)
			{
				$mSQL = $this->db->insert_string("notabu", $campos );
				$this->db->simple_query($mSQL);
				logusu('notabu',"TABLA DE NOMINA $contrato CREADO");
				echo "{ success: true, message: 'Tabla de Nomina'}";
			} else {
				echo "{ success: false, message: 'Ya existe ese registro en la table!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe ese registro en la tabla!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$contrato = $campos['contrato'];
		$ano = $campos['ano'];
		$mes = $campos['mes'];
		$dia = $campos['dia'];
		unset($campos['contrato']);
		unset($campos['ano']);
		unset($campos['mes']);
		unset($campos['dia']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("notabu", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('notabu',"TABLA DE NOMINA $contrato ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Tabla de Nomina -> ".$data['data']['contrato']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$cajero = $campos['cajero'];
		$chek  =  $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE cajero='$cajero'");
		$chek +=  $this->datasis->dameval("SELECT COUNT(*) FROM sfpa WHERE cobrador='$cajero'");

		if ($chek > 0){
			echo "{ success: false, message: 'Cajero no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM notabu WHERE cajero='$cajero'");
			logusu('notabu',"CAJERO $cajero ELIMINADO");
			echo "{ success: true, message: 'Cajero Eliminado'}";
		}
	}

	function calcautil(){
		$contrato = isset($_REQUEST['contrato1'])  ? $_REQUEST['contrato1']  : '';
		$monto    = isset($_REQUEST['monto1'])     ? $_REQUEST['monto1']     :  0;

		if ( $contrato == '' or $monto == 0 ){
			echo "{ success: false, msg: 'Valores malos'}";
		} else {
			$query = "UPDATE notabu SET utilidades=IF(ano>=1,$monto,($monto/12)*mes+($monto/24)*IF(dia>=15,1,0)) WHERE contrato='$contrato'";
			$this->db->query($query);
			echo "{ success: true, msg: 'Todo Bien'}";
		}
	}
	



//0414 376 0149 juan picapiedras

//****************************************************************
//
//
//
//****************************************************************
	function notabuextjs(){
		$encabeza='TABLA DE UTILIDADES';
		$listados= $this->datasis->listados('notabu');
		$otros=$this->datasis->otros('notabu', 'nomina/notabu');

		$mSQL = "SELECT codigo, CONCAT(codigo,' ',nombre) nombre, tipo FROM noco WHERE tipo<>'O' ORDER BY codigo";
		$contratos = $this->datasis->llenacombo($mSQL);

		$urlajax = 'nomina/notabu/';
		$variables = "";
		$funciones = "";

		$valida = "
		{ type: 'length', field: 'cajero', min: 1 },
		{ type: 'length', field: 'nombre', min: 1 }
		";
		
		$columnas = "
		{ header: 'Contrato',   width:  50, sortable: true, dataIndex: 'contrato',   field: { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Ano',        width:  40, sortable: true, dataIndex: 'ano',        field: { type: 'numeric'   }, filter: { type: 'numeric' }}, 
		{ header: 'Mes',        width:  40, sortable: true, dataIndex: 'mes',        field: { type: 'numeric'   }, filter: { type: 'numeric' }},
		{ header: 'Dia',        width:  40, sortable: true, dataIndex: 'dia',        field: { type: 'numeric'   }, filter: { type: 'numeric' }}, 
		{ header: 'Preaviso',   width:  90, sortable: true, dataIndex: 'preaviso',   field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') }, 
		{ header: 'Vacacion',   width:  90, sortable: true, dataIndex: 'vacacion',   field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') }, 
		{ header: 'Bono V.',    width:  90, sortable: true, dataIndex: 'bonovaca',   field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') }, 
		{ header: 'Antiguedad', width:  90, sortable: true, dataIndex: 'antiguedad', field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') }, 
		{ header: 'Utilidades', width:  90, sortable: true, dataIndex: 'utilidades', field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') }, 
		{ header: 'Prima',      width:  90, sortable: true, dataIndex: 'prima',      field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
	";

		$campos = "'id', 'contrato', 'ano', 'mes', 'dia', 'preaviso', 'vacacion', 'bonovaca', 'antiguedad', 'utilidades', 'prima'";
		
		$camposforma = "
							{
							xtype:'fieldset',
							//title: 'REGISTRO',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
								{ xtype: 'combo',       fieldLabel: 'Contrato', name: 'contrato', width:400, labelWidth:60, store: [".$contratos."] },
								{ xtype: 'numberfield', fieldLabel: 'Ano',      name: 'ano',      width:120, labelWidth:60, hideTrigger: false, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Mes',      name: 'mes',      width:140, labelWidth:80, hideTrigger: false, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Dia',      name: 'dia',      width:140, labelWidth:80, hideTrigger: false, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
							]
							},{
							xtype:'fieldset',
							title: 'VALORES',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { labelWidth:60 },
							style:'padding:4px',
							layout: 'column',
							items: [
								{ xtype: 'numberfield', fieldLabel: 'Preaviso',   name: 'preaviso',   width:180, labelWidth: 90, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Vacacion',   name: 'vacacion',   width:200, labelWidth:110, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Bono Vac.',  name: 'bonovaca',   width:180, labelWidth: 90, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Antiguedad', name: 'antiguedad', width:200, labelWidth:110, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Utilidades', name: 'utilidades', width:180, labelWidth: 90, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'numberfield', fieldLabel: 'Prima',      name: 'prima',      width:200, labelWidth:110, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
							]
							}
		";

		$titulow = 'Cajeros';

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 300,
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

		$stores = "";
		$acordioni = "	{
						title:'Informacion',
						border:false,
						layout: 'fit',
						html: '<br><center>Cambiar Utilidades con base al monto anual <button id=\'cambio\'>Ejecutar</button> <center>'
					},";
		
		$features = "features: [{ftype: 'filters', encode: 'json', local: false }],";
		$filtros = "";

		$final = "
		var mcambio = Ext.create('Ext.form.Panel', {
			title: 'Cambio',
			closable: true,
			height: 190,
			width: 360,
			floating: true,
			layout: 'anchor',
			frame: true, 
			modal: true,
			url: '".base_url().$urlajax."calcautil',
			items:[{
				html: '<p style=\'background-color:#DFE9F6;text-align:center;\'>Recalcula las Utilidades de la Tabla por el monto introducido entre los 12 meses para el contrato asignado</p>'
				},{
				xtype:'fieldset',
				columnWidth: 0.5,
				title: '',
				collapsible: false,
				defaults: { labelWidth:70, labelAlign: 'top' },
				layout: 'column',
				items :[
					{ xtype: 'combo',        fieldLabel: 'Seleccione un Contrato', name: 'contrato1', width:320, store: [".$contratos."], id: 'contrato1' },
					{ xtype: 'numberfield',  fieldLabel: 'Monto Anual',    name: 'monto1',    width:160, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00'), id: 'monto1' },
				]
			}],
			buttons:[
				{text: 'Cerrar',
				iconCls: 'icon-cross',
				handler: function(){
					var mmonto1    = Ext.getCmp('monto1');
					var mcontrato1 = Ext.getCmp('contrato1');
					mcambio.hide();
				}
				},
				{text: 'Aplicar',
				iconCls: 'icon-accept',
				handler: function(){
					var form = this.up('form').getForm();
					form.submit({
						success: function(form, action){
							Ext.Msg.alert('Exito',action.result.msg);
							mcambio.hide();
						},
						failure: function(form, action){
							Ext.Msg.alert('Error',action.result.msg);
						}
					})
				}
				},
				
			],
		});
		Ext.get('cambio').on('click', function(e){ mcambio.show();});";
		
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
		$data['filtros']     = $filtros;
		$data['acordioni']   = $acordioni;
		$data['final']       = $final;
		
		$data['title']  = heading('Tabla de Utilidades');
		$this->load->view('extjs/extjsven',$data);
	}

}
?>