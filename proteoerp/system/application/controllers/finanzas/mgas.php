<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
require_once(APPPATH.'/controllers/finanzas/gser.php');
class Mgas extends validaciones {

	function mgas(){
		parent::Controller(); 
		$this->load->library('rapyd');
		$this->load->library('pi18n');
		//gser::instalar();
		//$this->instalar();
	}

	function index(){
		if ( !$this->datasis->iscampo('mgas','id') ) {
			$this->db->simple_query('ALTER TABLE mgas DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE mgas ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE mgas ADD UNIQUE INDEX codigo (codigo)');
		}

		$this->datasis->modulo_id(501,1);
		if($this->pi18n->pais=='COLOMBIA'){
			redirect('finanzas/mgascol/filteredgrid');
		}else{
			//redirect('finanzas/mgas/filteredgrid');
			$this->mgasextjs();
		}
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$modbus=array(
		  'tabla'   =>'grga',
		  'columnas'=>array(
					'grupo' =>'Codigo',
					'nom_grup'=>'Descripci&oacute;n'
				   ),
		  'filtro'  =>array('grupo'=>'C&oacute;digo','nom_grup'=>'Descripci&oacute;n'),
		  'retornar'=>array('grupo'=>'grupo'),
		'titulo'  =>'Buscar Grupo de Gastos');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter("Filtro Maestro de Gastos", 'mgas');

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=10;
		$filter->codigo->group = 'UNO';

		$filter->cuenta = new inputField('Cuenta contable', 'cuenta');
		$filter->cuenta->size=15;
		$filter->cuenta->like_side='after';
		$filter->cuenta->group = 'UNO';

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->size=20;
		$filter->descrip->group = 'DOS';
		
		$filter->grupo = new inputField("Grupo", "grupo");
		$filter->grupo->size=10;
		$filter->grupo->append($boton);
		$filter->grupo->group = 'DOS';

		$filter->buttons("reset","search");
		$filter->build("dataformfiltro");

		$uri = anchor('finanzas/mgas/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Maestro de Gastos");
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;

		$grid->column_orderby("C&oacute;digo",$uri ,'codigo');
		$grid->column_orderby("Tipo","tipo","tipo");
		$grid->column_orderby("Descripci&oacute;n","descrip",'descrip');
		$grid->column_orderby("Grupo","grupo",'grupo');
		$grid->column_orderby('Nombre del Grupo','nom_grup','nom_grup');

		$grid->add("finanzas/mgas/dataedit/create");
		$grid->build();

		$data['filtro'] = $filter->output;
		$data['content'] = $grid->output;
		$data['title']   = heading('Maestro de Gastos');
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){
		$this->rapyd->load("dataedit");
		$link=site_url('finanzas/mgas/ultimo');

		$script ='
		function ultimo(){
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo codigo ingresado fue: " + msg );
			}
		});
		}
		$(function() {
			$(".inputnum").numeric(".");
			$("#grupo").change(function(){grupo();}).change();
		});
		function grupo(){
			t=$("#grupo").val();
			a=$("#grupo :selected").text();
			$("#nom_grup").val(a);
		}';

		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
		);
		$bcpla =$this->datasis->modbus($mCPLA);

		$atts = array(
				'width'     =>'800',
				'height'    =>'600',
				'scrollbars'=>'yes',
				'status'    =>'yes',
				'resizable' =>'yes',
				'screenx'   =>'5',
				'screeny'   =>'5');

		$edit = new DataEdit("Maestro de Gastos", "mgas");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->back_url = site_url("finanzas/mgas/filteredgrid");

		$ultimo='<a href="javascript:ultimo();" title="Consultar ultimo codigo ingresado" onclick="">Consultar ultimo codigo</a>';
		$edit->codigo= new inputField("C&oacute;digo", "codigo");
		$edit->codigo->mode="autohide";
		$edit->codigo->size = 12;
		$edit->codigo->maxlength = 6;
		$edit->codigo->rule = "trim|required|callback_chexiste";
		$edit->codigo->append($ultimo);

		$edit->descrip= new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size = 35;
		//$edit->descrip->readonly=true;

		$edit->tipo= new dropdownField("Tipo", "tipo");
		$edit->tipo->style ="width:100px;";
		$edit->tipo->option("G","Gasto");
		//$edit->tipo->option("G","Gasto");
		$edit->tipo->option("I","Inventario");
		$edit->tipo->option("S","Suministro");
		$edit->tipo->option("A","Activo Fijo");

		$edit->grupo= new dropdownField("Grupo", "grupo");
		$edit->grupo->options('SELECT grupo, CONCAT(grupo," - ",nom_grup) nom_grup from grga order by nom_grup');
		$edit->grupo->style ="width:250px;";
		$edit->grupo->onchange ="grupo();";

		//$edit->nom_grup  = new inputField("nom_grup", "nom_grup");

		$lcuent=anchor_popup("/contabilidad/cpla/dataedit/create","Agregar Cuenta Contable",$atts);
		$edit->cuenta    = new inputField("Cuenta Contable", "cuenta");
		$edit->cuenta->size = 12;
		$edit->cuenta->maxlength = 15;
		$edit->cuenta->rule = "trim|callback_chcuentac";
		$edit->cuenta->append($bcpla);
		$edit->cuenta->append($lcuent);
		$edit->cuenta->readonly=true;

		$edit->iva = new inputField("Iva", "iva");
		$edit->iva->css_class='inputnum';
		$edit->iva->size =12;
		$edit->iva->maxlength =5;
		$edit->iva->rule ="trim";

		$edit->medida    = new inputField("Unidad Medida", "medida");
		$edit->medida->size = 10;  

		$edit->fraxuni   = new inputField("Cantidad por Caja", "fraxuni");
		$edit->fraxuni->css_class='inputnum';//no sirve
		$edit->fraxuni->group = 'Existencias';
		$edit->fraxuni->size = 10;

		$edit->ultimo    = new inputField("Ultimo Costo", "ultimo");
		$edit->ultimo->css_class='inputnum';//no sirve
		$edit->ultimo->size = 15;

		$edit->promedio  = new inputField("Costo Promedio", "promedio");
		$edit->promedio->css_class='inputnum';//no sirve
		$edit->promedio->size = 15;

		$edit->minimo    = new inputField("Existencia M&iacute;nima", "minimo");
		$edit->minimo->css_class='inputnum';//no sirve
		$edit->minimo->group = 'Existencias';
		$edit->minimo->size = 10;

		$edit->maximo    = new inputField("Existencia M&aacute;xima", "maximo");
		$edit->maximo->css_class='inputnum';//no sirve
		$edit->maximo->group = 'Existencias';
		$edit->maximo->size = 10;

		$edit->unidades  = new inputField("Existencia Actual en Unidades o Cajas", "unidades");
		$edit->unidades->css_class='inputnum';//no sirve
		$edit->unidades->group = 'Existencias';
		$edit->unidades->size = 5;

		$edit->fraccion  = new inputField("Existencia Actual en Fracci&oacute;nes", "fraccion");
		$edit->fraccion->css_class='inputnum';//no sirve
		$edit->fraccion->group = 'Existencias';
		$edit->fraccion->size = 5;

		$edit->reten= new dropdownField("Retenci&oacute;n Persona Natural.", "reten");
		$edit->reten->option('','Ninguno');
		$edit->reten->options('SELECT codigo, CONCAT(codigo," - ",activida) val FROM rete WHERE tipo="NR" ORDER BY codigo');
		$edit->reten->style ="width:250px;";

		$edit->retej= new dropdownField("Retenci&oacute;n Persona Jur&iacute;dica.", "retej");
		$edit->retej->option('','Ninguno');
		$edit->retej->options('SELECT codigo, CONCAT(codigo," - ",activida) val FROM rete WHERE tipo="JD" ORDER BY codigo');
		$edit->retej->style ="width:250px;";

		$codigo=$edit->_dataobject->get("codigo");
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array("show","modify");

		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_mgas', $conten,true);
		$data["head"]    =script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		
		//$data["head"]    =script("tabber.js").script("prototype.js").script("sinvmaes.js").script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Maestro de Gasto</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function chexiste(){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM mgas WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT descrip FROM mgas WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el gasto $nombre");
			return FALSE;
		}
	}

	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT codigo FROM mgas ORDER BY codigo DESC");
		echo $ultimo;
	}

	function _detalle($codigo){
		$salida='';
		/*
		if(!empty($codigo)){
			$this->rapyd->load('dataedit','datagrid'); 

			$grid = new DataGrid('Cantidad por almac&eacute;n');
			$grid->db->select('ubica,locali,cantidad,fraccion');
			$grid->db->from('ubic');
			$grid->db->where('codigo',$codigo);
			
			$grid->column("Almacen"          ,"ubica" );
			$grid->column("Ubicaci&oacute;n" ,"locali");
			$grid->column("Cantidad"         ,"cantidad",'align="RIGHT"');
			$grid->column("Fracci&oacute;n"  ,"fraccion",'align="RIGHT"');
			
			$grid->build();
			$salida=$grid->output;
		}*/
		return $salida;
	}
  
	function consulta(){  
		$this->rapyd->load("datagrid");
		$fields = $this->db->field_data('mgas');
		$url_pk = $this->uri->segment_array();
		$coun=0; $pk=array();
		foreach ($fields as $field){
			if($field->primary_key==1){
				$coun++;
				$pk[]=$field->name;
			}
		}
		
		$values=array_slice($url_pk,-$coun);
		$claves=array_combine (array_reverse($pk) ,$values );

		$grid = new DataGrid('Ultimos Movimientos');
		$grid->db->select( array('a.fecha', 'a.numero','a.descrip', 'a.proveed', 'b.nombre', 'a.precio', 'a.iva', 'a.importe') );
		$grid->db->from('gitser a');
		$grid->db->join('sprv b','a.proveed=b.proveed');
		$grid->db->where('a.codigo', $claves['codigo'] );
		$grid->db->where('a.fecha >', "curdate()-365" );
		$grid->db->orderby('fecha DESC');
		$grid->db->limit(6);
			
		$grid->column("Fecha"   ,"fecha" );
		$grid->column("Descripcion"   ,"descrip" );
		$grid->column("Proveed" ,"proveed");
		//$grid->column("Nombre"  ,"nombre");
		$grid->column("Monto"   ,"<nformat><#precio#></nformat>",'align="RIGHT"');
		$grid->build();

		$grid1 = new DataGrid('Totales por Mes');
		$grid1->db->select( array('a.fecha', 'a.descrip', 'a.proveed', 'b.nombre', 'sum(a.precio) monto', 'a.iva', 'a.importe') );
		$grid1->db->from('gitser a');
		$grid1->db->join('sprv b','a.proveed=b.proveed');
		$grid1->db->where('a.codigo', $claves['codigo'] );
		$grid1->db->where('a.fecha >', "curdate()-365" );
		$grid1->db->groupby('fecha DESC ');
		$grid1->db->limit(6);
			
		$grid1->column("Fecha"   ,"fecha" );
		$grid1->column("Monto"   ,"<nformat><#monto#></nformat>",'align="RIGHT"');
			
		$grid1->build();

		$grid2 = new DataGrid('Totales por Proveedor');
		$grid2->db->select( array('a.fecha', 'a.proveed', 'b.nombre', 'sum(a.precio) monto') );
		$grid2->db->from('gitser a');
		$grid2->db->join('sprv b','a.proveed=b.proveed');
		$grid2->db->where('a.codigo', $claves['codigo'] );
		$grid2->db->where('a.fecha >', "curdate()-365" );
		$grid2->db->groupby('a.proveed');
		$grid2->db->orderby('monto DESC ');
		$grid2->db->limit(6);
			
		$grid2->column("Proveed" ,"proveed");
		$grid2->column("Nombre"  ,"nombre");
		$grid2->column("Monto"   ,"<nformat><#monto#></nformat>",'align="RIGHT"');
		
		$grid2->build();

		$descrip = $this->datasis->dameval("SELECT descrip FROM mgas WHERE codigo='".$claves['codigo']."'");
		$data['content'] = "
		<table width='100%'>
			<tr>
				<td valign='top'>
					<div style='border: 2px outset #EFEFEF;background: #EFEFFF '>".
					$grid1->output."
					</div>".
				"</td>
				<td valign='top'>
					<div style='border: 2px outset #EFEFEF;background: #EFFFEF '>".
					$grid2->output."
					</div>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<div style='border: 2px outset #EFEFEF;background: #FFFDE9 '>".
					$grid->output."
					</div>
				</td>
			</tr>
		</table>";
		$data["head"]     = script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = '<h1>Consulta de Maestro de Gasto</h1>';
		$data["subtitle"] = "<div align='center' style='border: 2px outset #EFEFEF;background: #EFEFEF '><a href='javascript:javascript:history.go(-1)'>(".$claves['codigo'].") ".$descrip."</a></div>";
		$this->load->view('view_ventanas', $data);
		
	}

	function instalar(){
		if (!$this->db->field_exists('reten','mgas')) {
			$mSQL="ALTER TABLE mgas ADD COLUMN reten VARCHAR(4) NULL DEFAULT NULL AFTER rica, ADD COLUMN retej VARCHAR(4) NULL DEFAULT NULL AFTER reten";
			$this->db->simple_query($mSQL);
		}
	}

	/*function sinvgrupos(){
		$this->rapyd->load("fields");  
		$where = "";  
		$line=$this->input->post('line');
		$dpto=$this->input->post('dpto'); 
		
		$grupo = new dropdownField("Grupo", "grupo");
		if ($line AND $dpto AND !(empty($line) OR empty($dpto))) {
			$where .= "WHERE depto = ".$this->db->escape($dpto);
			$where .= "AND linea = ".$this->db->escape($line);
			$sql = "SELECT grupo, nom_grup FROM grup $where";
			$grupo->option("","");
			$grupo->options($sql);
		}else{
			$grupo->option("","Seleccione una linea"); 
		} 
		$grupo->status = "modify";  
		$grupo->build();
		echo $grupo->output; 
	}*/


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"grupo","direction":"ASC"},{"property":"descrip","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select('*, CONCAt(grupo, " ", nom_grup ) nomgrup');
		$this->db->from('mgas');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('mgas');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$codigo = $campos['codigo'];

		$campos['nom_grup'] = $this->datasis->dameval("SELECT nom_grup FROM grga WHERE grupo='".$campos['grupo']."'");

		if ( !empty($codigo) ) {
			unset($campos['id']);
			unset($campos['nomgrup']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM mgas WHERE codigo='$codigo'") == 0)
			{
				$mSQL = $this->db->insert_string("mgas", $campos );
				$this->db->simple_query($mSQL);
				logusu('mgas',"GASTO $codigo CREADO");
				echo "{ success: true, message: 'Gasto Agregada'}";
			} else {
				echo "{ success: false, message: 'Ya existe una gasto con ese codigo!!'}";
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
		$id     = $campos['id'];
		
		unset($campos['codigo']);
		unset($campos['id']);
		unset($campos['nomgrup']);
		
		$campos['nom_grup'] = $this->datasis->dameval("SELECT nom_grup FROM grga WHERE grupo='".$campos['grupo']."'");

		$mSQL = $this->db->update_string("mgas", $campos,"id=".$id );
		$this->db->simple_query($mSQL);
		logusu('mgas',"mgas $codigo ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'mgas Modificada -> ".$codigo."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['codigo'];

		// VER SI PUEDE BORRAR GITSER
		$chek =  $this->datasis->dameval("SELECT count(*) FROM gitser WHERE codigo='$codigo'");
		// VER SI PUEDE BORRAR ORDENES DE SERVICIO
		$chek +=  $this->datasis->dameval("SELECT count(*) FROM itords WHERE codigo='$codigo'");
		// VER SI PUEDE BORRAR NOMINAS
		$chek +=  $this->datasis->dameval("SELECT count(*) FROM conc WHERE ctade='$codigo' AND tipod='G' ");
		// VER SI PUEDE BORRAR NOMINAS
		$chek +=  $this->datasis->dameval("SELECT count(*) FROM conc WHERE ctaac='$codigo' AND tipoa='G' ");

		if ($chek > 0){
			echo "{ success: false, message: 'Gasto no puede ser Borrada'}";
		} else {
			$this->db->simple_query("DELETE FROM mgas WHERE codigo='$codigo'");
			logusu('mgas',"GASTO $codigo ELIMINADO");
			echo "{ success: true, message: 'Gasto Eliminado'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************
//
//
//
//****************************************************************
	function mgasextjs(){
		$encabeza='MAESTRO DE GASTOS';
		$listados= $this->datasis->listados('mgas');
		$otros=$this->datasis->otros('mgas', 'finanzas/mgas');

		$mSQL = "SELECT grupo, CONCAT(grupo,' ',nom_grup) descrip FROM grga ORDER BY grupo";
		$grupo = $this->datasis->llenacombo($mSQL);

		$mSQL = 'SELECT codigo, CONCAT(codigo," - ",activida) val FROM rete WHERE tipo="NR" ORDER BY codigo';
		$reten = $this->datasis->llenacombo($mSQL);

		$mSQL = 'SELECT codigo, CONCAT(codigo," - ",activida) val FROM rete WHERE tipo="JD" ORDER BY codigo';
		$retej = $this->datasis->llenacombo($mSQL);


		$urlajax = 'finanzas/mgas/';
		$variables = "var mcuenta='';";
		
		$funciones = "
function rtipo(val){
	if ( val == 'G') {
		return 'Gasto';
	} else if ( val == 'I') {
		return  'Inventario';
	} else if ( val == 'S') {
		return  'Suministro';
	} else if ( val == 'A') {
		return  'Activo';
	}
}";
		
		$valida = "
		{ type: 'length', field: 'codigo',   min: 1 },
		{ type: 'length', field: 'descrip',  min: 1 }
		";
		
		$columnas = "
		{ header: 'Codigo',      width:  70, sortable: true, dataIndex: 'codigo',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Tipo',        width:  60, sortable: true, dataIndex: 'tipo',     field: { type: 'textfield' }, filter: { type: 'string' }, renderer: rtipo },
		{ header: 'Descripcion', width: 200, sortable: true, dataIndex: 'descrip',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Grupo',       width:  40, sortable: true, dataIndex: 'grupo',    field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Cuenta',      width:  70, sortable: true, dataIndex: 'cuenta',   field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Reten/Nat',   width:  70, sortable: true, dataIndex: 'reten',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Reten/Jur',   width:  70, sortable: true, dataIndex: 'retej',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		
		//{ header: 'Impuesto',    width:  70, sortable: true, dataIndex: 'impuesto', field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		";

		$agrupar = "		remoteSort: true,
		groupField: 'nomgrup',";


		$campos = "'id', 'codigo','tipo','descrip','grupo','nom_grup','iva','medida','fraxuni','minimo','maximo','ultimo','promedio','unidades','fraccion','cuenta','tasa1','base1','desde1','tasa2','base2','desde2','tasa3','base3','desde3','tasa4','base4','desde4','amorti','dacumu','rica','reten','retej','nomgrup'";
		
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
									{ xtype: 'textfield',     fieldLabel: 'Codigo',      name: 'codigo',  width:140, allowBlank: false, id: 'codigo' },
									{ xtype: 'combo',         fieldLabel: 'Tipo',        name: 'tipo',    width:260, store: [['G','Gasto'],['I','Inventario'],['S','Suministro'],['A','Activo Fijo']], labelWidth:160},
									{ xtype: 'textfield',     fieldLabel: 'Descripcion', name: 'descrip', width:400, allowBlank: false },
									{ xtype: 'combo',         fieldLabel: 'Grupo',       name: 'grupo',   width:400, store: [".$grupo."] },
									{ xtype: 'combo',         fieldLabel: 'C.Contable',  name: 'cuenta',  width:400, store: cplaStore, id: 'cuenta', mode: 'remote', hideTrigger: true, typeAhead: true, forceSelection: true, valueField: 'item', displayField: 'valor'},
								]
							},{
							xtype:'fieldset',
							title: 'Retenciones',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'combo', fieldLabel: 'Persona Natural',  name: 'reten',  width:400, store: [".$reten."]},
									{ xtype: 'combo', fieldLabel: 'Persona Juridica', name: 'retej',  width:400, store: [".$retej."] },
								]
							}
		";

		$titulow = 'Maestro de Gastos';

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 350,
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
							cplaStore.load({ params: { 'proveed': registro.data.cliente,  'origen': 'beforeform' } });

							form.loadRecord(registro);
							form.findField('codigo').setReadOnly(true);
						} else {
							mcuenta  = '';
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
		//$data['filtros']     = $filtros;
		$data['agrupar']     = $agrupar;

		$data['title']  = heading('Maestro de Gastos');
		$this->load->view('extjs/extjsven',$data);
		
	}



	
	function mgasbusca() {
		$start    = isset($_REQUEST['start'])  ? $_REQUEST['start']  :  0;
		$limit    = isset($_REQUEST['limit'])  ? $_REQUEST['limit']  : 25;
		$codigo   = isset($_REQUEST['codigo']) ? $_REQUEST['codigo']  : '';
		$tipo     = isset($_REQUEST['tipo'])   ? $_REQUEST['tipo']  : 'G';
		$semilla  = isset($_REQUEST['query'])  ? $_REQUEST['query']  : '';

		$semilla = trim($semilla);
	
		$mSQL = "SELECT codigo item, CONCAT(codigo, ' ', descrip) valor FROM mgas WHERE tipo='$tipo' ";
		if ( strlen($semilla)>0 ){
			$mSQL .= " AND ( codigo LIKE '$semilla%' OR descrip LIKE '%$semilla%' ) ";
		} else {
			if ( strlen($codigo)>0 ) $mSQL .= " AND codigo = '$codigo' ";
		}
		$mSQL .= "ORDER BY descrip ";
		$results = $this->db->count_all('mgas');

		if ( empty($mSQL)) {
			echo '{success:true, message:"mSQL vacio, Loaded data", results: 0, data:'.json_encode(array()).'}';
		} else {
			$mSQL .= " limit $start, $limit ";
			$query = $this->db->query($mSQL);
			$arr = $this->datasis->codificautf8($query->result_array());
			echo '{success:true, message:"maestro de gastos", results:'. $results.', data:'.json_encode($arr).'}';
		}
	}

}