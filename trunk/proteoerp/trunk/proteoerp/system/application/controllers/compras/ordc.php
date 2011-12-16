<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class ordc extends validaciones {

	function ordc(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index() {
		//redirect('compras/ordc/filteredgrid');
		redirect('compras/ordc/extgrid');
	}

	function extgrid(){
		//$this->datasis->modulo_id(201,1);
		$script = $this->ordcextjs();
		$data["script"] = $script;
		$data['title']  = heading('Orde de Compra');
		$this->load->view('extjs/ventana',$data);
	}


	function filteredgrid(){
		$this->rapyd->load('datagrid','datafilter');

		$atts = array(
              'width'      => '800',
              'height'     => '600',
              'scrollbars' => 'yes',
              'status'     => 'yes',
              'resizable'  => 'yes',
              'screenx'    => '0',
              'screeny'    => '0'
              );

        $modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de Orden de Compras','ordc');

		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d");
		$filter->fechah->insertValue = date("Y-m-d");
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=20;

		$filter->proveedor = new inputField('Proveedor', 'proveed');
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = 'proveed';
		$filter->proveedor->size=20;

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$uri   = anchor('compras/ordc/dataedit/show/<#numero#>','<#numero#>');
		$uri2  = anchor_popup('formatos/verhtml/ORDC/<#numero#>','Ver HTML',$atts);
		$uri_2 = anchor('compras/ordc/dataedit/show/<#numero#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12')));

		$grid = new DataGrid();
		$grid->order_by('numero','desc');
		$grid->per_page = 15;

		$grid->column('Acci&oacute;n',$uri_2,'align=center');
		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha'        ,'<dbdate_to_human><#fecha#></dbdate_to_human>'   ,'fecha' ,"align='center'");
		$grid->column_orderby('Fecha F.'     ,'<dbdate_to_human><#fechafac#></dbdate_to_human>','fecha' ,"align='center'");
		$grid->column_orderby('Proveedor'    ,'proveed'  ,'proveed');
		$grid->column_orderby('Nombre'       ,'nombre'   ,'nombre');
		$grid->column_orderby('Peso'         ,'peso'     ,'peso');
		$grid->column_orderby('IVA'          ,'montoiva' ,'montoiva' ,"align='right'");
		$grid->column_orderby('Monto'        ,'montonet' ,'montoner' ,"align='right'");
		$grid->column_orderby('Monto Total'  ,'montotot' ,'montotot' ,"align='right'");
		//$grid->column("Vista",$uri2,"align='center'");
		$grid->add('compras/ordc/dataedit/create');
		$grid->build('datagridST');

//************ SUPER TABLE ************* 
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

		$data['head']    = script('jquery.js').script('superTables.js'). $this->rapyd->get_head();
		$data['title']   ='<h1>Orden de Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		
		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
		),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'descrip_<#i#>',
				'base1'  =>'precio1_<#i#>',
				'base2'  =>'precio2_<#i#>',
				'base3'  =>'precio3_<#i#>',
				'base4'  =>'precio4_<#i#>',
				'iva'    =>'itiva_<#i#>',
				'peso'   =>'sinvpeso_<#i#>',
				'pond'   =>'pond_<#i#>',
				'ultimo' =>'ultimo_<#i#>',
				'precio1'=>'costo_<#i#>'
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
			'script'  => array('post_modbus_sinv(<#i#>)')
				);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');


		$do = new DataObject('ordc');
		$do->rel_one_to_many('itordc', 'itordc', 'numero');
		$do->rel_pointer('itordc','sinv','itordc.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Orden De Comnpra', $do);
		$edit->back_url = site_url('compras/ordc/filteredgrid');
		$edit->set_rel_title('itordc','Producto <#o#>');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->proveed = new inputField('Proveedor','proveed');
		$edit->proveed->size = 6;
		$edit->proveed->maxlength=5;
		$edit->proveed->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->status = new  dropdownField ('Estado', 'status');
		$edit->status->option('','');
		$edit->status->option('CE','Cerrado');
		$edit->status->option('PE','Pendiente');
		$edit->status->option('BA','BackOrde');
		$edit->status->style='width:200px;';

		$edit->arribo = new DateonlyField('Arribo', 'arribo','d/m/Y');
		$edit->arribo->insertValue = date('Y-m-d');
		$edit->arribo->rule = 'required';
		$edit->arribo->mode = 'autohide';
		$edit->arribo->size = 10;
		
		$edit->fechafac = new DateonlyField('Fecha Factura', 'fechafac','d/m/Y');
		$edit->fechafac->insertValue = date('Y-m-d');
		$edit->fechafac->rule = 'required';
		$edit->fechafac->mode = 'autohide';
		$edit->fechafac->size = 10;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itordc';
		$edit->codigo->rule     = 'required';
		$edit->codigo->append($btn);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->readonly  = true;
		$edit->descrip->rel_id='itordc';

		$edit->cantidad = new inputField('Cantidad <#o#>', 'cantidad_<#i#>');
		$edit->cantidad->db_name  = 'cantidad';
		$edit->cantidad->css_class= 'inputnum';
		$edit->cantidad->rel_id   = 'itordc';
		$edit->cantidad->maxlength= 10;
		$edit->cantidad->size     = 6;
		$edit->cantidad->rule     = 'required|positive';
		$edit->cantidad->autocomplete=false;
		$edit->cantidad->onkeyup  ='importe(<#i#>)';

		$edit->costo = new inputField('Precio <#o#>', 'costo_<#i#>');
		$edit->costo->db_name   = 'costo';
		$edit->costo->css_class = 'inputnum';
		$edit->costo->rel_id    = 'itordc';
		$edit->costo->size      = 10;
		$edit->costo->rule      = 'required|positive';
		$edit->costo->readonly  = true;

		$edit->importe = new inputField('Importe <#o#>', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=10;
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='itordc';

		for($i=1;$i<=4;$i++){
			$obj='precio'.$i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj.'_<#i#>');
			$edit->$obj->db_name   = 'sinv'.$obj;
			$edit->$obj->rel_id    = 'itordc';
			$edit->$obj->pointer   = true;
		}

		$edit->itiva = new hiddenField('', 'itiva_<#i#>');
		$edit->itiva->db_name  = 'iva';
		$edit->itiva->rel_id   = 'itordc';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name   = 'sinvpeso';
		$edit->sinvpeso->rel_id    = 'itordc';
		$edit->sinvpeso->pointer   = true;

		$edit->ultimo = new hiddenField('', 'ultimo_<#i#>');
		$edit->ultimo->db_name   = 'ultimo';
		$edit->ultimo->rel_id    = 'itordc';
		$edit->ultimo->pointer   = true;

		$edit->pond = new hiddenField('', "pond_<#i#>");
		$edit->pond->db_name='pond';
		$edit->pond->rel_id   ='itordc';
		$edit->pond->pointer   = true;
		//**************************
		//fin de campos para detalle
		//**************************

		$edit->montoiva = new inputField('Impuesto', 'montoiva');
		$edit->montoiva->css_class ='inputnum';
		$edit->montoiva->readonly  =true;
		$edit->montoiva->size      = 10;

		$edit->montotot = new inputField('Sub-Total', 'montotot');
		$edit->montotot->css_class ='inputnum';
		$edit->montotot->readonly  =true;
		$edit->montotot->size      = 10;

		$edit->montonet = new inputField('Monto Total', 'montonet');
		$edit->montonet->css_class ='inputnum';
		$edit->montonet->readonly  =true;
		$edit->montonet->size      = 10;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_ordc', $conten,true);
		$data['title']   = heading('Orden de Compra');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function bussug(){
		$this->rapyd->load('datagrid','datafilter');
		$uri   = anchor('compras/ordc/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid();

		$grid->db->select(array('codigo','descrip','exmax','exmin','existen','ultimo','exmax - existen AS sug'));
		$grid->db->from('sinv');
		$grid->db->where('existen <= exmin');
		$grid->db->where('activo','S');

		$grid->order_by('codigo','desc');
		$grid->per_page = 40;

		$grid->column_orderby('C&oacute;digo',$uri ,'codigo');
		$grid->column_orderby('Descripci&oacute;n' ,'descrip' ,'descrip');
		$grid->column_orderby('M&aacute;ximo'      ,'<nformat><#exmax#></nformat> -- <nformat><#exmin#></nformat>'   ,'exmax'   , "align='right'");
		$grid->column_orderby('Existencia'         ,'<nformat><#existen#></nformat>' ,'existen' , "align='right'");
		$grid->column_orderby('Sugerido'           ,'<nformat><#sug#></nformat>'     ,'exmin'   , "align='right'");
		$grid->column_orderby('&Uacute;ltimo costo','<nformat><#ultimo#></nformat>'  ,'ultimo'  , "align='right'");
		$grid->build();

		$data['content'] = $grid->output;
		$data['title']   = heading('Productos sugueridos');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		$iva=$totals=0;
		$cana=$do->count_rel('itordc');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itordc','cantidad',$i);
			$itpreca   = $do->get_rel('itordc','costo',$i);
			$itiva     = $do->get_rel('itordc','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itordc','importe' ,$itimporte,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;

		
		$do->set('montonet' ,round($totals ,2));
		$do->set('montotot' ,round($totalg ,2));
		$do->set('montoiva'    ,round($iva    ,2));

		$numero =$this->datasis->fprox_numero('nordc');
		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date("H:i:s");
			
		$do->set('estampa',$estampa);
		$do->set('hora'   ,$hora);
		$do->set('numero' ,$numero);
		$do->set('transac',$transac);
		
		for($i=0;$i<$cana;$i++){
			$do->set_rel('itordc','estampa' ,$estampa,$i);
			$do->set_rel('itordc','hora'    ,$hora   ,$i);
			$do->set_rel('itordc','transac' ,$transac,$i);
			$do->set_rel('itordc','usuario' ,$usuario,$i);;
		}

		return true;
	}

	function _pre_update($do){
		$iva=$totals=0;
		$cana=$do->count_rel('itordc');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itordc','cantidad',$i);
			$itpreca   = $do->get_rel('itordc','costo',$i);
			$itiva     = $do->get_rel('itordc','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itordc','importe' ,$itimporte,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;

		
		$do->set('montonet' ,round($totals ,2));
		$do->set('montotot' ,round($totalg ,2));
		$do->set('montoiva'    ,round($iva    ,2));

		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('ordc',"O.Compra $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('ordc',"O.Compra $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('ordc',"O.Compra $codigo ELIMINADO");
	}
	
	
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;


		$where = "";

		//Buscar posicion 0 Cero
		if (isset($_REQUEST['filter'])){
			$filter = json_decode($_REQUEST['filter'], true);
			if (is_array($filter)) {
				//Dummy Where. 
				$where = "numero IS NOT NULL ";
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
		$this->db->from('ordc');

		if (strlen($where)>1){
			$this->db->where($where);
		}

		if ( $sort == '') $this->db->order_by( 'numero', 'desc' );

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$results = $query->num_rows();

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

	function griditordc(){
		$numero   = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  0;
		if ($numero == 0 ) $numero = $this->datasis->dameval("SELECT MAX(numero) FROM ordc");

		$mSQL = "SELECT a.codigo, a.descrip, a.cantidad, a.costo, a.importe, a.iva, a.ultimo, a.precio1, a.precio2, a.precio3, a.precio4, b.id codid FROM itordc a JOIN sinv b ON a.codigo=b.codigo WHERE a.numero='$numero' ORDER BY a.codigo";
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

	function sprvbu(){
		$numero = $this->uri->segment(4);
		$id = $this->datasis->dameval("SELECT b.id FROM ordc a JOIN sprv b ON a.proveed=b.proveed WHERE numero='$numero'");
		redirect('compras/sprv/dataedit/show/'.$id);
	}

	function tabla() {
		$numero   = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  0;
		$transac = $this->datasis->dameval("SELECT transac FROM ordc WHERE numero='$numero'");
		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM sprm WHERE transac='$transac' ORDER BY cod_prv ";
		$query = $this->db->query($mSQL);
		$codprv = 'XXXXXXXXXXXXXXXX';
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			
			foreach ($query->result_array() as $row)
			{
				if ( $codprv != $row['cod_prv']){
					$codprv = $row['cod_prv'];
					$salida .= "<tr bgcolor='#c7d3c7'>";
					$salida .= "<td colspan=4>".trim($row['nombre']). "</td>";
					$salida .= "</tr>";	
				}
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		echo $salida;
	}

	function ordcextjs() {

		$encabeza='<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">ORDEN DE COMPRA</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';
		$listados= $this->datasis->listados('ordc');
		$otros=$this->datasis->otros('ordc', 'ordc');

		$script = "
<script type=\"text/javascript\">		
var BASE_URL   = '".base_url()."';
var BASE_PATH  = '".base_url()."';
var BASE_ICONS = '".base_url()."assets/icons/';
var BASE_UX    = '".base_url()."assets/js/ext/ux';
var modulo = 'ordc'

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

var urlApp = '".base_url()."';

Ext.require([
	'Ext.grid.*',
	'Ext.ux.grid.FiltersFeature',
	'Ext.data.*',
	'Ext.util.*',
	'Ext.state.*',
	'Ext.form.*',
	'Ext.window.MessageBox',
	'Ext.tip.*',
	'Ext.ux.CheckColumn',
	'Ext.toolbar.Paging'
]);

var mxs = ((screen.availWidth/2) -400);
var mys = ((screen.availHeight/2)-300);

//Column Model Presupuestos
var OrdcCol = 
	[
		{ header: 'Numero',           width:  60, sortable: true,  dataIndex: 'numero',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Fecha',            width:  70, sortable: false, dataIndex: 'fecha',    field: { type: 'date'      }, filter: { type: 'date'   }}, 
		{ header: 'Status.',          width:  50, sortable: true,  dataIndex: 'status',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Prov.',            width:  50, sortable: true,  dataIndex: 'proveed',  field: { type: 'textfield' }, filter: { type: 'string' }, renderer: renderSprv }, 
		{ header: 'Nombre Proveedor', width: 200, sortable: true,  dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'SubTotal',         width: 100, sortable: true,  dataIndex: 'montotot', field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'IVA',              width:  80, sortable: true,  dataIndex: 'montoiva', field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Total',            width: 100, sortable: true,  dataIndex: 'montonet', field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Condiciones',      width: 160, sortable: true,  dataIndex: 'condi',    field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Estampa',          width:  70, sortable: false, dataIndex: 'fecha',    field: { type: 'date'      }, filter: { type: 'date'   }}, 
		{ header: 'Hora',             width:  60, sortable: true,  dataIndex: 'hora',     field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Usuario',          width:  60, sortable: true,  dataIndex: 'usuario',  field: { type: 'textfield' }, filter: { type: 'string' }}
	];

//Column Model Detalle de Presupuesto
var ItOrdcCol = 
	[
		{ header: 'Codigo',      width:  90, sortable: true, dataIndex: 'codigo',   field: { type: 'textfield' }, filter: { type: 'string' }, renderer: renderSinv }, 
		{ header: 'codid',       dataIndex: 'codid',  hidden: true}, 
		{ header: 'Descripcion', width: 250, sortable: true, dataIndex: 'descrip',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Cant',        width:  60, sortable: true, dataIndex: 'cantidad', field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Precio',      width:  80, sortable: true, dataIndex: 'costo',    field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}, 
		{ header: 'Importe',     width: 100, sortable: true, dataIndex: 'importe',  field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')},
		{ header: 'IVA',         width:  60, sortable: true, dataIndex: 'iva',      field: { type: 'textfield' }, filter: { type: 'string' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')}
	];

function renderSprv(value, p, record) {
	var mreto='';
	if ( record.data.proveed == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'compras/ordc/sprvbu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.numero );
}

function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}

// application main entry point
Ext.onReady(function() {
	Ext.QuickTips.init();
	/////////////////////////////////////////////////
	// Define los data model
	// Presupuestos
	Ext.define('Ordc', {
		extend: 'Ext.data.Model',
		fields: ['id', 'tipo_doc', 'numero', 'fecha', 'status', 'proveed', 'nombre',  'montotot', 'montoiva', 'montonet',  'condi', 'estampa', 'hora', 'usuario'],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'compras/ordc/grid',
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
	var storeOrdc = Ext.create('Ext.data.Store', {
		model: 'Ordc',
		pageSize: 50,
		remoteSort: true,
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	//Filters
	var filters = {
		ftype: 'filters',
		encode: 'json', // json encode the filter query
		local: false
	};    

	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridOrdc = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeOrdc,
		title: 'Compras',
		iconCls: 'icon-grid',
		frame: true,
		columns: OrdcCol,
		dockedItems: [{
			xtype: 'toolbar',
			items: [
				{
					iconCls: 'icon-add',
					text: 'Agregar',
					scope: this,
					handler: function(){
						window.open(urlApp+'compras/ordc/dataedit/create', '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},
				{
					iconCls: 'icon-update',
					text: 'Modificar',
					disabled: true,
					itemId: 'update',
					scope: this,
					handler: function(selModel, selections){
						var selection = gridOrdc.getView().getSelectionModel().getSelection()[0];
						gridOrdc.down('#delete').setDisabled(selections.length === 0);
						window.open(urlApp+'compras/ordc/dataedit/show/'+selection.data.id, '_blank', 'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys);
					}
				},{
					iconCls: 'icon-delete',
					text: 'Eliminar',
					disabled: true,
					itemId: 'delete',
					scope: this,
					handler: function() {
						var selection = gridOrdc.getView().getSelectionModel().getSelection()[0];
						Ext.MessageBox.show({
							title: 'Confirme', 
							msg: 'Seguro que quiere eliminar la compra Nro. '+selection.data.numero, 
							buttons: Ext.MessageBox.YESNO, 
							fn: function(btn){ 
								if (btn == 'yes') { 
									if (selection) {
										//storeOrdc.remove(selection);
									}
									storeOrdc.load();
								} 
							}, 
							icon: Ext.MessageBox.QUESTION 
						});  
					}
				}
			]
		}],
		features: [filters],
		// paging bar on the bottom
		bbar: Ext.create('Ext.PagingToolbar', {
			store: storeOrdc,
			displayInfo: false,
			displayMsg: 'Pag No. {0} - Reg. {1} de {2}',
			emptyMsg: 'No se encontraron Registros.'
		}),
	});

//////************ MENU DE ADICIONALES /////////////////
".$listados."
//////************ FIN DE ADICIONALES /////////////////

	/////////////////////////////////////////////////
	// Define los data model
	// Compras
	Ext.define('ItOrdc', {
		extend: 'Ext.data.Model',
		fields: ['codigo', 'codid', 'descrip', 'cantidad', 'costo', 'importe', 'iva', 'ultimo','precio1', 'precio2','precio3', 'precio4' ],
		proxy: {
			type: 'ajax',
			noCache: false,
			api: {
				read   : urlApp + 'compras/ordc/griditordc',
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
	var storeItOrdc = Ext.create('Ext.data.Store', {
		model: 'ItOrdc',
		autoLoad: false,
		autoSync: true,
		method: 'POST'
	});

	//////////////////////////////////////////////////////////////////
	// create the grid and specify what field you want
	// to use for the editor at each column.
	var gridItOrdc = Ext.create('Ext.grid.Panel', {
		width: '100%',
		height: '100%',
		store: storeItOrdc,
		title: 'Articulos',
		iconCls: 'icon-grid',
		frame: true,
		columns: ItOrdcCol
	});

	var ordcTplMarkup = [
		'<table width=\'100%\' bgcolor=\"#F3F781\">',
		'<tr><td colspan=3 align=\'center\'><p style=\'font-size:14px;font-weight:bold\'>IMPRIMIR ORDEN</p></td></tr><tr>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/verhtml/ORDC/{numero}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/html_icon.gif', 'alt' => 'Formato HTML', 'title' => 'Formato HTML','border'=>'0'))."</a></td>',
		'<td align=\'center\'>{numero}</td>',
		'<td align=\'center\'><a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'formatos/ver/ORDC/{numero}\',     \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">".img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',   'title' => 'Formato PDF', 'border'=>'0'))."</a></td></tr>',
		'<tr><td colspan=3 align=\'center\' >--</td></tr>',		
		'</table>','nanai'
	];

	// Al cambiar seleccion
	gridOrdc.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
		if (selectedRecord.length) {
			gridOrdc.down('#delete').setDisabled(selectedRecord.length === 0);
			gridOrdc.down('#update').setDisabled(selectedRecord.length === 0);
			numero = selectedRecord[0].data.numero;
			gridItOrdc.setTitle(numero+' '+selectedRecord[0].data.nombre);
			storeItOrdc.load({ params: { numero: numero }});
			var meco1 = Ext.getCmp('imprimir');
			Ext.Ajax.request({
				url: urlApp +'compras/ordc/tabla',
				params: { numero: selectedRecord[0].data.numero },
				success: function(response) {
					var vaina = response.responseText;
					ordcTplMarkup.pop();
					ordcTplMarkup.push(vaina);
					var ordcTpl = Ext.create('Ext.Template', ordcTplMarkup );
					meco1.setTitle('Imprimir Compra');
					ordcTpl.overwrite(meco1.body, selectedRecord[0].data );
				}
			});
		}
	});

	var viewport = new Ext.Viewport({
		id:'simplevp',
		layout:'border',
		border:false,
		items:[{
			region: 'north',
			preventHeader: true,
			height: 40,
			minHeight: 40,
			html: '".$encabeza."'
		},{
			region:'west',
			width:200,
			border:false,
			autoScroll:true,
			title:'Lista de Opciones',
			collapsible:true,
			split:true,
			collapseMode:'mini',
			layoutConfig:{animate:true},
			layout: 'accordion',
			items: [
				{
					layout: 'fit',
					items:[
						{
							name: 'imprimir',
							id: 'imprimir',
							//preventHeader: true,
							border:false,
							html: 'Para imprimir seleccione una Compra '
						}
					]
				},
				{
					title:'Listados',
					border:false,
					layout: 'fit',
					items: gridListado
				},
				{
					title:'Otras Funciones',
					border:false,
					layout: 'fit',
					html: '".$otros."'
				}
			]
		},{
			cls: 'irm-column irm-center-column irm-master-detail',
			region: 'center',
			title:  'center-title',
			layout: 'border',
			preventHeader: true,
			border: false,
			items: [{
				itemId: 'viewport-center-master',
				cls: 'irm-master',
				region: 'center',
				items: gridOrdc
			},{
				itemId: 'viewport-center-detail',
				activeTab: 0,
				region: 'south',
				height: '40%',
				split: true,
				margins: '0 0 0 0',
				preventHeader: true,
				items: gridItOrdc
			}]	
		}]
	});
	storeOrdc.load();
	storeItOrdc.load();
});

</script>
";
		return $script;	
		
	}
	
	
}