<?php
/*
class Recep extends Controller {
	var $mModulo='RECEP';
	var $titp='Modulo RECEP';
	var $tits='Modulo RECEP';
	var $url ='inventario/recep/';

	function Recep(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		//if ( !$this->datasis->iscampo('recep','id') ) {
		//	$this->db->simple_query('ALTER TABLE recep DROP PRIMARY KEY');
		//	$this->db->simple_query('ALTER TABLE recep ADD UNIQUE INDEX numero (numero)');
		//	$this->db->simple_query('ALTER TABLE recep ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		//};
		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
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
jQuery("#a1").click( function(){
	var id = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\''.base_url().'formatos/ver/RECEP/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"edocta",   "img"=>"images/pdf_logo.gif",  "alt" => "Formato PDF", "label"=>"Estado de Cuenta"));
		$WestPanel = $grid->deploywestp();

		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor("TITULO1"));

		$param['WestPanel']    = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados']   = $this->datasis->listados('RECEP', 'JQ');
		$param['otros']       = $this->datasis->otros('RECEP', 'JQ');
		$param['temas']       = array('proteo','darkness','anexos1');
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
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('recep');
		$grid->label('Recep');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
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


		$grid->addField('clipro');
		$grid->label('Clipro');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('refe');
		$grid->label('Refe');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));


		$grid->addField('observa');
		$grid->label('Observa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:2, cols:60}'",
		));


		$grid->addField('status');
		$grid->label('Status');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));


		$grid->addField('user');
		$grid->label('User');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 50 }',
		));


		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('origen');
		$grid->label('Origen');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 20 }',
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(false);
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
	* /
	function getdata()
	{
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('recep');

		$response   = $grid->getData('recep', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	* /
	function setData()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$mcodp  = "??????";
		$check  = 0;
		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM recep WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('recep', $data);
					echo "Registro Agregado";

					logusu('RECEP',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";
		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM recep WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM recep WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE recep SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("recep", $data);
				logusu('RECEP',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('recep', $data);
				logusu('RECEP',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}
		} elseif($oper == 'del') {
		$meco = $this->datasis->dameval("SELECT $mcodp FROM recep WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM recep WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM recep WHERE id=$id ");
				logusu('RECEP',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}
*/

class Recep extends Controller {
	var $titp   = 'Registro de Seriales';
	var $tits   = 'Registro de Seriales';
	var $url    = 'inventario/recep/';

	function Recep(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->serial_repetidos=array();
		$this->datasis->modulo_id(135,1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter2','datagrid');

		$filter = new DataFilter2('');

		//$filter->db->select(array("b.cuenta","a.comprob","a.fecha","a.origen","a.debe","a.haber","a.status","a.descrip","a.total"));
		$filter->db->from('recep AS a');

		$filter->recep = new inputField('N&uacute;mero de referencia', 'recep');
		$filter->recep->size  =10;
		$filter->recep->db_name='a.recep';

		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ="where";
		$filter->fechad->db_name =$filter->fechah->db_name="a.fecha";
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">=";
		$filter->fechah->operator="<=";

		$filter->buttons('reset','search');

		$filter->build();
		$uri = anchor($this->url.'dataedit/show/<#recep#>','<#recep#>');

		function tipo($tipo){
			switch($tipo){
				case 'E':return 'Entrega';break;
				case 'R':return 'Recepci&oacute;n';break;
			}
		}

		function origen($origen){
			switch($origen){
				case 'scst':return 'Compra';break;
				case 'sfac':return 'Facturaci&oacute;n';break;
				default: return $origen;
			}
		}

		$grid = new DataGrid('');
		$grid->order_by('a.recep','desc');
		$grid->per_page = 20;
		$grid->use_function('substr','str_pad','origen','tipo');

		$grid->column_orderby('N&uacute;mero Recepci&oacute;n',$uri,'numero');
		$grid->column_orderby('Fecha'                  ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha'    ,'align=\'center\''      );
		$grid->column_orderby('Or&iacute;gen'          ,'<origen><#origen#></origen>'                 ,'origen'   ,'align=\'center\''      );
		$grid->column_orderby('Tipo'                   ,'<tipo><#tipo#></tipo>'                       ,'tipo'     ,'align=\'center\''      );
		$grid->column_orderby('Cod.Proveedor/Cliente'  ,'clipro'                                      ,'cod_prov' ,'align=\'center\''      );
		$grid->column_orderby('Observacion'            ,'observa'                                     ,'observa'  ,'align=\'left\'  NOWRAP');

		$grid->add($this->url.'dataedit/create');
		$grid->build();
		//echo $grid->db->last_query();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['script']  = script('jquery.js');
		$data['title']   = heading($this->titp);
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
*/

	function dataedit(){
		$alma=$this->secu->getalmacen();
		if(empty($alma)) show_error('El usuario no tiene un almac&eacute;n asignado');
		$this->rapyd->load('dataobject','datadetails');

		/*$mSPRV=array(
			'tabla'   =>'view_clipro',
			'columnas'=>array(
			    'codigo' =>'C&oacute;digo',
			    'tipo'    =>'Tipo',
			    'rif'     =>'RIF/CI',
			    'nombre'  =>'Nombre'),
			'filtro'  =>array(
			    'tipo'    =>'Tipo',
			    'codigo' =>'C&oacute;digo',
			    'rif'     =>'RIF/CI',
			    'nombre'  =>'Nombre'),
			'retornar'=>array('codigo'=>'clipro','nombre'=>'nombre'),
			'script'  =>array('human_traslate()'),
			'titulo'  =>'Buscar Proveedor / Cliente');
		$bSPRV=$this->datasis->p_modbus($mSPRV,'proveed');*/

		$sprvbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  => array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=> array('proveed'=>'clipro', 'nombre'=>'nombre'),
			'script'  => array('_post_modbus()'),
			'titulo'  =>'Buscar Proveedor');
		$bSPRV=$this->datasis->p_modbus($sprvbus,'proveed');

		$do = new DataObject('recep');
		$do->rel_one_to_many('seri', 'seri', array('recep'=>'recep'));

		$edit = new DataDetails($this->tits, $do);
		$edit->back_url = site_url($this->url.'filteredgrid');
		$edit->set_rel_title('itcasi','Rubro <#o#>');

		$edit->pre_process( 'insert','_pre_insert' );
		$edit->pre_process( 'update','_pre_update' );
		$edit->pre_process( 'delete','_pre_delete' );
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->recep = new inputField('N&uacute;mero', 'recep');
		$edit->recep->mode ='autohide';
		$edit->recep->when=array('show','modify');

		$edit->clipro = new inputField('Cliente/Proveedor', 'clipro');
		$edit->clipro->size=5;
		$edit->clipro->rule='callback_chclipro|required';
		$edit->clipro->type='inputhidden';
		$edit->clipro->readonly=true;
		$edit->clipro->append($bSPRV);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->type ='inputhidden';

		$tipo=array('E'=>'Entrega','R'=>utf8_encode('Recepción'));
		if($edit->_status=='show'){
			$edit->tipo = new dropdownField('Tipo','tipo');
			$edit->tipo->option('E','Entrega');
			$edit->tipo->option('R','Recepci&oacute;n');
		}else{
			$edit->tipo = new inputField('Tipo','tipo');
			$edit->tipo->rule = 'enum[R,E]';
			$edit->tipo->type ='inputhidden';
			$edit->tipo->insertValue='R';
		}

		$origen=array('scst'=>'Compra','sfac'=>'Factura');
		if($edit->_status=='show'){
			$edit->origen = new dropdownField('Or&iacute;gen', 'origen');
			$edit->origen->options($origen);
		}else{
			$edit->origen = new inputField('Or&iacute;gen', 'origen');
			$edit->origen->rule = 'enum[sfac,scst]';
			$edit->origen->insertValue='scst';
			$edit->origen->type ='inputhidden';
		}

		$tipo_ref=array('F'=>'F','FC'=>'F','NC'=>'D','D'=>'D');
		$edit->tipo_refe = new inputField('Tipo de referencia', 'tipo_refe');
		$edit->tipo_refe->rule = 'enum[FC,NC,F,D]';
		$edit->tipo_refe->type ='inputhidden';

		$edit->refe = new inputField('Referencia', 'refe');
		$edit->refe->rule='max_length[8]';
		$edit->refe->size=10;
		$edit->refe->maxlength=8;
		$edit->refe->append('N&uacute;mero de referencia cuando es ventas o n&uacute;mero de compra');

		$edit->fecha = new  dateonlyField('Fecha','fecha');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule= 'required|chfecha';
		$edit->fecha->size= 10;

		$edit->observa = new textAreaField('Observaci&oacute;n', 'observa');
		$edit->observa->cols = 60;
		$edit->observa->rows = 1;
		$edit->observa->style = 'width:100%;';

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		//******************************
		//     Inicio del detalle
		//******************************
		$edit->itbarras = new inputField('(<#o#>) Barras', 'it_barras_<#i#>');
		$edit->itbarras->rule         ='trim|required|callback_chbarras[<#i#>]|strtoupper';
		$edit->itbarras->size         =20;
		$edit->itbarras->db_name      ='barras';
		$edit->itbarras->rel_id       ='seri';
		$edit->itbarras->autocomplete =false;

		$edit->itcodigo = new inputField('(<#o#>) C&oacute;digo', 'it_codigo_<#i#>');
		$edit->itcodigo->rule         ='trim|required';
		$edit->itcodigo->size         =10;
		$edit->itcodigo->db_name      ='codigo';
		$edit->itcodigo->rel_id       ='seri';
		$edit->itcodigo->autocomplete =false;
		$edit->itcodigo->type         ='inputhidden';

		$edit->itdescri = new inputField('(<#o#>) Descripci&oacute;n', 'it_descri_<#i#>');
		$edit->itdescri->rule         ='trim|required';
		$edit->itdescri->size         =40;
		$edit->itdescri->db_name      ='descrip';
		$edit->itdescri->rel_id       ='seri';
		$edit->itdescri->autocomplete =false;
		$edit->itdescri->type         ='inputhidden';

		$edit->itserial = new inputField('(<#o#>) Serial', 'it_serial_<#i#>');
		$edit->itserial->rule         ='trim|callback_chrepetido[<#i#>]|callback_chserial[<#i#>]|required';
		$edit->itserial->size         =20;
		$edit->itserial->db_name      ='serial';
		$edit->itserial->rel_id       ='seri';
		$edit->itserial->autocomplete =false;

		$edit->itcant = new inputField('(<#o#>) Cantidad', 'it_cant_<#i#>');
		$edit->itcant->rule         = 'trim|numeric|required|positive';
		$edit->itcant->size         = 10;
		$edit->itcant->db_name      = 'cant';
		$edit->itcant->rel_id       = 'seri';
		$edit->itcant->autocomplete = false;
		$edit->itcant->css_class    = 'inputnum';
		$edit->itcant->insertValue  = 1;
		$edit->itcant->disable_paste= true;
		//******************************
		//      Fin del detalle
		//******************************

		$status=$edit->get_from_dataobjetct('status');
		$edit->buttons('modify','save','undo','back','add_rel','add');
		$edit->build();

		$smenu['link']       = barra_menu('322');
		$data['smenu']       = $this->load->view('view_sub_menu', $smenu,true);
		$conten['jtipo']     = json_encode($tipo);
		$conten['jorigen']   = json_encode($origen);
		$conten['jtipos_ref']= json_encode($tipo_ref);
		$conten['form']      =& $edit;

		$data['content'] = $this->load->view('recep', $conten,false);
//		$data['title']   = heading($this->tits.' Nro. '.$edit->recep->value);
//		$data['head']    = $this->rapyd->get_head(); //style('vino/jquery-ui.css');
//		$data['head']   .= script('jquery.js');
//		$data['head']   .= script('jquery-ui.js');
//		$data['head']   .= script('plugins/jquery.numeric.pack.js');
//		$data['head']   .= script('plugins/jquery.floatnumber.js');
//		$data['head']   .= script('plugins/jquery.meiomask.js');
//		$data['head']   .= phpscript('nformat.js');
//		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
//		$this->load->view('view_ventanas', $data, true );
	}

	//*****************************
	// Chequea que los productos
	//   esten en el documento
	//*****************************
	function chbarras($barras,$i){
		$cod_ind='it_codigo_'.$i;
		$can_ind='it_cant_'.$i;

		$codigo   = $this->input->post($cod_ind);
		$cana     = $this->input->post($can_ind);
		$tipo_ref = $this->input->post('tipo_refe');
		$refe     = $this->input->post('refe');
		$origen   = $this->input->post('origen');
		$clipro   = $this->input->post('clipro');

		if(empty($refe)) return true;

		if(!isset($this->it_detalle)){
			$this->it_detalle=array();
			if($origen=='scst'){
				$this->db->select(array('TRIM(b.codigo) AS codigo','SUM(b.cantidad) AS cana'));
				$this->db->from('scst AS a');
				$this->db->join('itscst AS b','a.control=b.control');
				$this->db->where('a.proveed' ,$clipro);
				$this->db->where('a.tipo_doc',$tipo_ref);
				$this->db->where('a.numero'  ,$refe);
				$this->db->group_by('b.codigo');
				$query = $this->db->get();
				$doc='compra';
			}elseif($origen=='sfac'){
				$this->db->select(array('TRIM(b.codigoa) AS codigo','SUM(b.cana) AS cana'));
				$this->db->from('sfac AS a');
				$this->db->join('sitems AS b','a.numero=b.numa AND a.tipo_doc=b.tipoa');
				$this->db->where('a.cod_cli' ,$clipro);
				$this->db->where('a.tipo_doc',$tipo_ref);
				$this->db->where('a.numero'  ,$refe);
				$this->db->group_by('b.codigoa');
				$query = $this->db->get();
				$doc='venta';
			}else{
				$query=false;
			}
			if ($query !== false){
				foreach ($query->result() as $row){
					$this->it_detalle[$row->codigo]=$row->cana;
				}
			}
		}
		if(isset($this->it_detalle[$codigo]) && $this->it_detalle[$codigo]>=$cana){
			$this->it_detalle[$codigo]-=$cana;
			return true;
		}else{
			$this->it_detalle[$codigo]=0;
			$this->validation->set_message('chbarras', 'El art&iacute;culo en \'%s\' no esta contenido en el documento o se exedio la cantidad facturada');
			return false;
		}
	}

	//*****************************
	// Chequea que los seriales de
	//  salida hayan entrado
	//*****************************
	function chserial($serial,$i){
		$origen   = $this->input->post('origen');
		$dbserial = $this->db->escape($serial);
		$codigo   = $this->input->post('it_codigo_'.$i);
		$dbcodigo = $this->db->escape($codigo);
		$mSQL="SELECT COUNT(*) FROM seri WHERE codigo=${dbcodigo} AND serial=${dbserial}";
		if($origen=='sfac'){
			$this->validation->set_message('chserial', "El serial $serial no esta registrado al sistema, debe ingresarlo primero para hacer la salida");
			$cana=$this->datasis->dameval($mSQL);
			if($cana==0 || empty($cana)){
				return false;
			}
		}elseif($origen=='scst'){
			$this->validation->set_message('chserial', "El serial $serial ya esta registrado en el sistema");
			$cana=$this->datasis->dameval($mSQL);
			if($cana==0 || empty($cana)){
				return true;
			}else{
				return false;
			}
		}
		return true;
	}

	//*****************************
	// Chequea que los proveedor
	//  o cliente sea correcto
	//*****************************
	function chclipro($clipro){
		$origen    = $this->input->post('origen');
		$tipo_ref  = $this->input->post('tipo_refe');
		$refe      = $this->input->post('refe');
		if(empty($refe)) return true;

		$dbclipro  = $this->db->escape($clipro);
		$dbtipo_ref= $this->db->escape($tipo_ref);
		$dbrefe    = $this->db->escape($refe);

		$rt=0;
		if($origen=='scst'){
			$mSQL="SELECT COUNT(*) FROM scst WHERE proveed=$dbclipro AND numero=$dbrefe AND tipo_doc=$dbtipo_ref";
			$rt=$this->datasis->dameval("SELECT COUNT(*) FROM scst WHERE proveed=$dbclipro AND numero=$dbrefe AND tipo_doc=$dbtipo_ref");
		}elseif($origen=='sfac'){
			$rt=$this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE cod_cli=$dbclipro AND numero=$dbrefe AND tipo_doc=$dbtipo_ref");
		}

		$this->validation->set_message('chclipro', 'El cliente o proveedor propuesto no es el mismo del documento referenciado');
		return ($rt>0)? true : false;
	}

	//*****************************
	// Chequea que no exista un
	//     serial repetido
	//*****************************
	function chrepetido($serial,$i){
		$this->validation->set_message('chrepetido', 'Hay un serial repetido para el mismo producto');
		$cod_ind='it_codigo_'.$i;
		$codigo = $this->input->post($cod_ind);

		if(isset($this->serial_repetidos[$codigo])){
			if(in_array($serial,$this->serial_repetidos[$codigo])){
				return false;
			}else{
				$this->serial_repetidos[$codigo][]=$serial;
				return true;
			}
		}else{
			$this->serial_repetidos[$codigo][]=$serial;
			return true;
		}
	}

	function _pre_update($do){
		unset($this->serial_repetidos); //Para ahorrar memoria
		unset($this->it_detalle);       //Para ahorrar memoria

		return true;
	}

	function _pre_insert($do){
		unset($this->serial_repetidos); //Para ahorrar memoria
		unset($this->it_detalle);       //Para ahorrar memoria

		$nrecep= $this->datasis->fprox_numero('nrecep');
		$do->set('recep',$nrecep);

		$alma = $this->secu->getalmacen();
		$rel  = 'seri';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$do->set_rel($rel, 'alma' ,$alma);
		}

		return true;
	}

	function _pre_delete($do){
		return false;
	}

	function _valida($do){
		return false;
		$error  ='';
		$recep  =$do->get('recep');
		$tipo   =$do->get('tipo');
		$refe   =$do->get('refe');
		$origen =$do->get('origen');
		$refee  =$this->db->escape($refe);

		if(empty($recep)){
			$ntransac = $this->datasis->fprox_numero('nrecep');
			$do->set('recep',$ntransac);
			$do->pk    =array('recep'=>$ntransac);
		}

		//se trae cliente y proveedor depende del numero
		//if($origen=='scst'){
		//	$clipro=$this->datasis->dameval("SELECT proveed FROM scst WHERE control=$refee");
		//}elseif($origen=='sfac'){
		//	$clipro=$this->datasis->dameval("SELECT cod_cli FROM sfac  WHERE numero=$refee AND tipo_doc='F'");
		//}
		//$do->set('clipro',$clipro);

		//INICIO VALIDA ORIGEN=SFAC Y ENTREGADO
		//se trae las cantidad disponibles a despachar por factura
		
		$sface=array();
		if($origen=='sfac' && $tipo=='E'){
			$query="SELECT codigo,SUM(cant) cant FROM (
				SELECT codigoa codigo,desca descrip, cana cant
				FROM sitems a WHERE numa=$refee
				UNION ALL
				SELECT b.codigo,b.descrip,-1*b.cant
				FROM seri b
				JOIN recep c ON b.recep=c.recep
				WHERE c.refe=$refee AND c.origen='sfac' AND c.recep<>'$recep'
			)t
			GROUP BY codigo";
			$sface=$this->datasis->consularray($query);
		}
		//FIN VALIDA ORIGEN=SFAC Y ENTREGADO

		//INICIO VALIDA ORIGEN=SFAC Y DEVUELVE
		//se trae todos los item de la factura con las cantidades entregadas
		
		$sfac=array();$sfacs=array();
		if($origen=='sfac' && $tipo=='R'){
			$query="
			SELECT codigo,SUM(cant) cant FROM (
			SELECT codigoa codigo,desca descrip, 0 cant
			FROM sitems a WHERE numa=$refee
			UNION ALL
			SELECT b.codigo,b.descrip,b.cant
			FROM seri b
			JOIN recep c ON b.recep=c.recep
			WHERE c.refe=$refee AND c.origen='sfac' AND c.recep<>'$recep'
			)t
			GROUP BY codigo";
			$sfac=$this->datasis->consularray($query);

			$query="
			SELECT b.codigo,b.serial
			FROM seri b
			JOIN recep c ON b.recep=c.recep
			WHERE c.refe=$refee AND c.origen='sfac' AND c.recep<>'$recep'";
			$sfacs=$this->datasis->consularray($query);
		}
		//FIN VALIDA ORIGEN=SFAC Y DEVUELVE

		//INICIO VALIDA ORIGEN=SCST Y DEVUELVE
		//se trae todos los Las cantidades recibidas
		$scst=array();$scsts=array();
		if($origen=='scst' && $tipo=='E'){
			$query="
			SELECT b.codigo,SUM(b.cant) cant
			FROM seri b
			JOIN recep c ON b.recep=c.recep
			WHERE c.refe=$refee AND c.origen='scst' AND c.recep<>'$recep'
			GROUP BY codigo";
			$scst=$this->datasis->consularray($query);

			//se trae los seriales recibidos para la recepcion
			$query="
			SELECT b.codigo,b.serial
			FROM seri b
			JOIN recep c ON b.recep=c.recep
			WHERE c.refe=$refee AND c.origen='scst' AND c.recep<>'$recep'";
			$scst=$this->datasis->consularray($query);
		}
		// FIN VALIDA ORIGEN=Scst Y DEVUELVE

		$se=array();$sinv=0;
		for($i=0;$i < $do->count_rel('seri');$i++){
			$codigo =$do->get_rel('seri','codigo',$i);
			$barras =$do->get_rel('seri','barras',$i);
			$serial =$do->get_rel('seri','serial',$i);
			$descrip=$do->get_rel('seri','descrip',$i);
			$cant   =$do->get_rel('seri','cant',$i);
			$codigoe=$this->db->escape($codigo);
			$barrase=$this->db->escape($barras);
			$seriale=$this->db->escape($serial);

			$where='';

			if(!empty($recep)){
				$recepe=$this->db->escape($recep);
				$where=" AND a.recep<>$recepe ";
			}

			if(!($cant>0))
			$error.=" La cantidad debe ser positiva para el codigo $codigo y barras $barras</br>";

			$t=$this->datasis->dameval("SELECT a.tipo FROM recep a JOIN seri b ON a.recep=b.recep WHERE codigo=$codigoe AND serial=$seriale $where ORDER BY a.fecha,a.recep desc LIMIT 1");

			if($tipo=='R'){
				if($t=='E' && empty($t))
				$error.="No se puede recibir debido a que esta recibido</br>";
			}elseif($tipo=='E' ){
				if($t!='R')
				$error.="No se puede entegar debido a que fue entregado o no ha sido recibido</br>";
			}else{
				$error.="ERROR. el tipo no es Entregar, ni Recibir</br>";
			}

			if(empty($error) && $tipo=='E'){
				$t=$this->datasis->dameval("SELECT SUM(IF(tipo='R',b.cant,-1*b.cant)) FROM recep a JOIN seri b ON a.recep=b.recep WHERE codigo=$codigoe AND serial=$seriale $where ORDER BY a.fecha desc LIMIT 1");
				if($cant>$t)
				$error.="La cantidad a entregar es mayor a la existente</br>";
			}

			if(empty($error))
			$sinv=$this->datasis->damerow("SELECT descrip,modelo,marca,clave,unidad,serial FROM sinv WHERE codigo=$codigoe AND barras=$barrase");

			if(count($sinv)>0){
				if($sinv['serial']=='S' && empty($serial)){
					$error.="El serial es obligatorio para el codigo $codigo y barras $barras</br>";
				}else{
					if(strlen($serial)>0)
					$do->set_rel('seri','cant',1,$i);

					if(in_array($codigo.$barras.$serial.$cant,$se)){
						$error.="El Serial $serial ya existe para el codigo $codigo y barras $barras</br>";
					}else{
						$se[]=$codigo.$barras.$serial;
					}
				}
			}else{
				$error.="El Codigo $codigo y barras $barras no existe.</br>";
			}

			/*INICIO VALIDA ORIGEN=SFAC Y ENTREGADO*/

			if($origen=='sfac' && $tipo=='E'){
				echo "aqui";
				if(array_key_exists($codigo,$sface)){

					if($cant>$sface[$codigo])
						$error.="ERROR. la cantidad a despachar del producto $codigo es mayor a la disponible ".nformat($sface[$codigo])." por despachar ";
				}else{
					$error.="ERROR. el producto ($codigo) $descrip no pertenece  la factura $refe</br>";
				}
			}
			/*FIN VALIDA ORIGEN=SFAC Y ENTREGADO*/

			/*INICIO VALIDA ORIGEN=SFAC Y DEVUELVE
			chequea que cada codigo ingresado pertenzca a la factura a devolver*/

			if($origen=='sfac' && $tipo=='R'){
			//print_r($sfac);
				if(array_key_exists($codigo,$sfac)){
					if($cant>$sfac[$codigo])
					$error.="ERROR. la cantidad a devolver del producto $codigo es mayor a la entregada para la factura $refee ".nformat($sface[$codigo])." por despachar ";
				}else{
					$error.="ERROR. el producto ($codigo) $descrip no pertenece  la factura $refe</br>";
				}
				//chequea que el serial ingresado pertenezca a la factura a devolver
				if(!(in_array($serial,$sfacs))){
					$error.="ERROR. el producto ($codigo) $descrip  serial $serial no pertenece  la factura $refe</br>";
				}
			}

			/*FIN VALIDA ORIGEN=SFAC Y DEVUELVE*/

			/*INICIO VALIDA ORIGEN=SCST Y DEVUELVE
			chequea que cada codigo ingresado pertenzca a la factura a devolver*/
			if($origen=='scst' && $tipo=='E'){
				if(array_key_exists($codigo,$scst)){
					if($cant>$scst[$codigo])
					$error.="ERROR. la cantidad a devolver del producto $codigo es mayor a la recibida para la factura $refee ".nformat($scst[$codigo])." ";
				}else{
					$error.="ERROR. el producto ($codigo) $descrip no pertenece a la recepcion de la factura $refe</br>";
				}
				//chequea que el serial ingresado pertenezca a la factura a devolver
				if(!(in_array($serial,$scst))){
					$error.="ERROR. el producto ($codigo) $descrip  serial $serial no pertenece  la factura $refe recibida</br>";
				}
			}
			/*FIN VALIDA ORIGEN=SCST Y DEVUELVE*/
		}

		if(!empty($error)){
			$do->error_message_ar['pre_ins']="<div class='alert'>".$error."</div>";
			$do->error_message_ar['pre_upd']="<div class='alert'>".$error."</div>";
			return false;
		}else{
			$do->set('estampa', 'CURDATE()', FALSE);
			$do->set('user', $this->session->userdata('usuario'));
			//GUARDA EN SNOT E ITSNOT
		}
	}

	function crea_snot($do){
		$refe2   = $do->get('refe2');
		$refe    = $do->get('refe');
		$fecha   = $do->get('fecha');
		$clipro  = $do->get('clipro');
		$origen  = $do->get('origen');
		$recep   = $do->get('recep');
		$tipo    = $do->get('tipo');
		$refee   = $this->db->escape($refe);
		$fechae  = $this->db->escape($fecha);
		$cliproe = $this->db->escape($clipro);

		/*CREA SNOT E ITSNOT CUANDO ES ENTREGA DE FACTURA*/
		if($origen=='sfac' && $tipo=='E'){
			$sfac  =$this->datasis->damerow("SELECT fecha,almacen,nombre FROM sfac WHERE numero=$refee AND tipo_doc='F'");
			if(empty($refe2)){
				$refe2 = $this->datasis->fprox_numero('nsnot');
				$query="INSERT INTO snot (`precio`,`numero`,`fecha`,`factura`,`cod_cli`,`fechafa`,`nombre`,`almaorg`,`almades`)
				VALUES (0,'$refe2',$fechae,$refee,$cliproe,'".$sfac['fecha']."','".$sfac['nombre']."','".$sfac['almacen']."','".$sfac['almacen']."')";
				$this->db->query($query);
			}else{
				$query="UPDATE snot  SET
				fecha=$fechae,
				factura=$refee,
				cod_cli=$cliproe,
				fechafa='".$sfac['fecha']."',
				nombre='".$sfac['nombre']."',
				almaorg='".$sfac['almacen']."',
				almades='".$sfac['almacen']."'
				";
				$this->db->query($query);
			}
			$this->db->query("DELETE FROM itsnot WHERE numero='$refe2'");
			$query="
			INSERT INTO itsnot (`numero`,`codigo`,`descrip`,`cant`,`saldo`,`entrega`,`factura`)
			SELECT '$refe2' numero,codigo,a.descrip,b.cana cant,(b.cana-SUM(a.cant)) saldo,SUM(a.cant) entrega,$refee
			FROM recep c
			JOIN seri a ON a.recep=c.recep
			JOIN sitems b ON a.codigo=b.codigoa AND c.refe=b.numa
			WHERE c.recep='$recep' AND b.tipoa='F' AND b.numa=$refee
			GROUP BY codigo";
			$this->db->query($query);
		}
	}

	function _post_insert($do){
		$this->crea_snot($do);
		$numero = $do->get('recep');
		logusu('recep',"Creo recepcion  $numero");
		//redirect($this->url."actualizar/$numero");
	}

	function _post_update($do){
		$this->crea_snot($do);
		$numero = $do->get('recep');
		logusu('recep'," Modifico recepcion $numero");
	}

	function _post_delete($do){
		$numero = $do->get('recep');
		logusu('recep'," Elimino recepcion $numero");
	}

	function instalar(){
		if (!$this->db->table_exists('recep')) {
			$mSQL = "CREATE TABLE `recep` (
				`recep` CHAR(8) NOT NULL DEFAULT '',
				`fecha` DATE NULL DEFAULT NULL,
				`clipro` VARCHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(100) NULL DEFAULT NULL,
				`refe` CHAR(8) NULL DEFAULT NULL,
				`tipo_refe` CHAR(2) NULL DEFAULT NULL,
				`tipo` CHAR(2) NULL DEFAULT NULL,
				`observa` TEXT NULL,
				`status` CHAR(2) NULL DEFAULT NULL,
				`user` VARCHAR(50) NULL DEFAULT NULL,
				`estampa` TIMESTAMP NULL DEFAULT NULL,
				`origen` VARCHAR(20) NULL DEFAULT NULL,
				PRIMARY KEY (`recep`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('view_clipro')) {
			$user=$this->db->username;
			$host=$this->db->hostname;

			$mSQL = "CREATE ALGORITHM = UNDEFINED DEFINER= `$user`@`$host` VIEW view_clipro AS
			SELECT 'Proveedor' tipo, b.proveed codigo, b.nombre, b.rif, concat_ws(' ', b.direc1, b.direc2, b.direc3) direc FROM `sprv` b
			UNION ALL
			SELECT 'Cliente' Cliente, a.cliente, a.nombre, a.rifci, concat_ws(' ',a.dire11, a.dire12, a.dire21, a.dire22) direc FROM `scli` a";
			$this->db->simple_query($mSQL);
		}

		$fields = $this->db->list_fields('seri');
		if(!in_array('cant',$fields)){
			$query="ALTER TABLE `seri`  ADD COLUMN `cant` DECIMAL(19,2) NOT NULL DEFAULT '1'";
			$this->db->simple_query($query);
		}
		if(!in_array('recep',$fields)){
			$query="ALTER TABLE `seri` ADD COLUMN `recep` CHAR(8) NOT NULL";
			$this->db->simple_query($query);
		}
		if(!in_array('frecep',$fields)){
			$query="ALTER TABLE `seri` ADD COLUMN `frecep` DATE NOT NULL";
			$this->db->simple_query($query);
		}
		if(!in_array('barras',$fields)){
			$query="ALTER TABLE `seri`  ADD COLUMN `barras` VARCHAR(50) NOT NULL";
			$this->db->simple_query($query);
		}
	}

}
