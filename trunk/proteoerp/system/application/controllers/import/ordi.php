<?php
class Ordi extends Controller {

	function Ordi(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('205',1);
	}

	function index(){
		redirect('import/ordi/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&acute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de Transferencias','ordi');

		$filter->numero = new inputField('N&uacute;mero','numero');
		$filter->numero->size=15;

		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->size=12;

		$filter->proveed = new inputField('Proveedor', 'proveed');
		$filter->proveed->size=12;
		$filter->proveed->append($boton);

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('import/ordi/dataedit/show/<#numero#>','<str_pad><#numero#>|8|0|0</str_pad>');

		$grid = new DataGrid('Lista');
		$grid->use_function('str_pad');
		$grid->order_by('numero','desc');
		$grid->per_page = 5;

		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha'        ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Proveedor'    ,'proveed','proveed');
		$grid->column_orderby('Nombre'       ,'nombre','nombre');
		$grid->column_orderby('Monto Fob.'   ,'montofob','montofob','align=\'right\'');
		$grid->column_orderby('Monto total'  ,'montotot','montotot','align=\'right\'');

		$grid->add('import/ordi/dataedit/create','Agregar orden');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Orden de importaci&oacute;n</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Producto en inventario');
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$sprv=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor');
		$boton=$this->datasis->modbus($sprv);

		$aran=array(
			'tabla'   =>'aran',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n',
				'tarifa'=>'Tarifas'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codaran_<#i#>','tarifa'=>'arancel_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Aranceles',
			'script'  =>array('calcula()'));
		$aran=$this->datasis->p_modbus($aran,'<#i#>');

		$asprv=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'agente'),
			'titulo'  =>'Buscar Proveedor');
		$aboton=$this->datasis->modbus($asprv,'agsprv');

		$script="
		function post_add_itstra(id){
			$('#cantidad_'+id).numeric(".");
			return true;
		}";

		$do = new DataObject('ordi');
		$do->rel_one_to_many('itordi', 'itordi', 'numero');

		$edit = new DataDetails('ordi', $do);
		$edit->back_url = site_url('import/ordi/filteredgrid');
		$edit->set_rel_title('itstra','Producto <#o#>');
		$edit->script($script,'create');
		$edit->script($script,'modify');

		$edit->pre_process( 'insert','_pre_insert');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->numero= new inputField('N&uacute;mero', 'numero');
		$edit->numero->mode='autohide';
		$edit->numero->size=10;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->fecha = new  dateonlyField('Fecha','fecha');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->maxlength=8;
		$edit->fecha->size =10;

		$edit->status = new dropdownField('Estatus', 'status');
		$edit->status->option('A','Abierto');
		$edit->status->option('C','Cerrado');
		$edit->status->option('E','Eliminado');
		$edit->status->rule  = 'required';
		$edit->status->style = 'width:120px';

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->rule     ='trim|required';
		$edit->proveed->maxlength=5;
		$edit->proveed->size     =7;
		$edit->proveed->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rule     ='trim';
		$edit->nombre->maxlength=40;
		$edit->nombre->size     =40;

		$edit->agente = new inputField('Agente aduanal', 'agente');
		$edit->agente->rule     ='trim';
		$edit->agente->maxlength=5;
		$edit->agente->size     =7;
		$edit->agente->append($aboton);

		$arr=array(
			'montofob' =>'Total factura extrangera $',
			'gastosi'  =>'Gastos Internacionales $',
			'montocif' =>'Monto FOB+gastos Internacionales $',
			//'aranceles'=>'Suma del Impuesto Arancelario Bs',
			'gastosn'  =>'Gastos Nacionales Bs',
			'montotot' =>'Monto CIF + Gastos Nacionales Bs');

		foreach($arr as $obj => $etiq){
			$edit->$obj = new inputField($etiq, $obj);
			$edit->$obj->rule     ='trim';
			$edit->$obj->maxlength=20;
			$edit->$obj->size     =20;
			$edit->$obj->css_class= 'inputnum';
		}

		$edit->arribo = new dateonlyField('Fecha de Llegada', 'arribo');
		$edit->arribo->rule     ='chfecha';
		$edit->arribo->maxlength=8;
		$edit->arribo->size     =10;

		$edit->factura = new inputField('Nro. Factura', 'factura');
		$edit->factura->rule     ='trim';
		$edit->factura->maxlength=20;
		$edit->factura->size     =20;

		$edit->cambioofi = new inputField('Cambio Oficial', 'cambioofi');
		$edit->cambioofi->css_class= 'inputnum';
		$edit->cambioofi->rule     ='trim|required';
		$edit->cambioofi->maxlength=17;
		$edit->cambioofi->size     =10;

		$edit->cambioreal = new inputField('Cambio Real', 'cambioreal');
		$edit->cambioreal->css_class= 'inputnum';
		$edit->cambioreal->rule     ='trim';
		$edit->cambioreal->maxlength=17;
		$edit->cambioreal->size     =10;

		$edit->peso = new inputField('Peso Total', 'peso');
		$edit->peso->css_class= 'inputnum';
		$edit->peso->rule     ='trim';
		$edit->peso->maxlength=12;
		$edit->peso->size     =12;

		$edit->condicion = new textareaField('Condici&oacute;n', 'condicion');
		$edit->condicion->rule ='trim';
		$edit->condicion->cols = 35;
		$edit->condicion->rows = 2;  

		//comienza el detalle
		$edit->codigo = new inputField('C&oacute;digo <#o#>','codigo_<#i#>');
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rule     = 'trim|required';
		$edit->codigo->rel_id   = 'itordi';
		$edit->codigo->maxlength= 15;
		$edit->codigo->size     = 10;
		$edit->codigo->append($btn);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>','descrip_<#i#>');
		$edit->descrip->db_name  ='descrip';
		$edit->descrip->rel_id   ='itordi';
		$edit->descrip->maxlength=35;
		$edit->descrip->size     =30;

		$edit->cantidad = new inputField('Cantidad <#o#>','cantidad_<#i#>');
		$edit->cantidad->db_name  = 'cantidad';
		$edit->cantidad->css_class= 'inputnum';
		$edit->cantidad->rel_id   = 'itordi';
		$edit->cantidad->rule     = 'numeric';
		$edit->cantidad->maxlength= 10;
		$edit->cantidad->size     = 7;

		$arr=array(
			'costofob'    =>'costofob'    ,
			'importefob'  =>'importefob'  ,
			//'relgastosi'  =>'gastosi'     ,
			//'costocif'    =>'costocif'    ,
			//'importecif'  =>'importecif'  ,
			//'montoaran'   =>'montoaran'   ,
			//'relgastosn'  =>'gastosn'     ,
			//'costofinal'  =>'costofinal'  ,
			//'importefinal'=>'importefinal',
			//'iva'           =>'iva'    ,
			);
		foreach($arr as $obj=>$db){
			$edit->$obj = new inputField(ucfirst("$obj <#o#>"), "${obj}_<#i#>");
			$edit->$obj->db_name  = $db;
			$edit->$obj->css_class= 'inputnum';
			$edit->$obj->rel_id   = 'itordi';
			$edit->$obj->rule     ='trim';
			$edit->$obj->maxlength=20;
			$edit->$obj->size     =10;
		}

		/*$edit->iva = new inputField('IVA <#o#>', 'iva_<#i#>');
		$edit->iva->db_name  = 'iva';
		$edit->iva->rel_id   = 'itordi';
		$edit->iva->rule     ='trim';
		$edit->iva->maxlength=7;
		$edit->iva->size     =6;*/

		$edit->codaran = new inputField('Codaran <#o#>', 'codaran_<#i#>');
		$edit->codaran->db_name  = 'codaran';
		$edit->codaran->rel_id   = 'itordi';
		$edit->codaran->rule     ='trim';
		$edit->codaran->maxlength=15;
		$edit->codaran->size     =10;
		$edit->codaran->append($aran);

		$arr=array(
			'arancel',
			//'participam',
			//'participao'
		);
		foreach($arr as $obj){
			$edit->$obj = new inputField(ucfirst("$obj <#o#>"), "${obj}_<#i#>");
			$edit->$obj->db_name  = $obj;
			$edit->$obj->css_class= 'inputnum';
			$edit->$obj->rel_id   = 'itordi';
			$edit->$obj->rule     ='trim';
			$edit->$obj->maxlength= 7;
			$edit->$obj->size     = 5;
		}

		/*$arr=array('precio1','precio2','precio3','precio4');
		foreach($arr as $obj){
			$edit->$obj = new inputField(ucfirst("$obj <#o#>"), "${obj}_<#i#>");
			$edit->$obj->db_name  = $obj;
			$edit->$obj->css_class= 'inputnum';
			$edit->$obj->rel_id   = 'itordi';
			$edit->$obj->rule     ='trim';
			$edit->$obj->maxlength=15;
			$edit->$obj->size     =10;
		}*/
		//Termina el detalle

		if($edit->_status=='show'){
			$action = "javascript:window.location='".site_url('import/ordi/calcula/'.$edit->_dataobject->pk['numero'])."'";
			$edit->button('btn_recalculo', 'Calcular valores', $action, 'TR');
		}

		$edit->buttons('modify','save','undo','delete','back','add_rel');
		$edit->build();

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		//$data['content'] = $edit->output;
		//$data['smenu']   = $this->load->view('view_sub_menu','205',true);

		$conten['gseri'] =  ($edit->_status!='create') ? $this->_showgeri($edit->_dataobject->pk['numero']) : '';
		$conten['gser']  =  ($edit->_status!='create') ? $this->_showgeser($edit->_dataobject->pk['numero']): '';
		$conten['form']  =& $edit;
		$data['content'] =  $this->load->view('view_ordi',$conten,true);
		$data['title']   =  '<h1>Orden de importaci&oacute;n</h1>';
		$data['head']    =  $this->rapyd->get_head().phpscript('nformat.js');
		$this->load->view('view_ventanas', $data); 
	}

	function _showgeri($id){
		$this->rapyd->load('datagrid');

		$grid = new DataGrid('Lista','gseri');
		$grid->db->where('ordeni',$id);
		$grid->use_function('str_pad');
		$grid->order_by('numero','desc');
		//$grid->per_page = 5;

		$grid = new DataGrid();
		$grid->order_by('numero','desc');
		$grid->per_page = 15;
		$uri=anchor('import/ordi/gseri/'.$id.'/modify/<#id#>','<str_pad><#id#>|8|0|0</str_pad>');

		$grid->column('N&uacute;mero',$uri);
		$grid->column('N. Factura','numero');
		$grid->column('Fecha'    ,'<dbdate_to_human><#fecha#></dbdate_to_human>',"align='center'");
		$grid->column('Concepto' ,'concepto');
		$grid->column('Monto'    ,'<nformat><#monto#></nformat>',"align='right'");

		$grid->add('import/ordi/gseri/'.$id.'/create','Agregar gasto internacional');
		$grid->build();

		return $grid->output;
	}

	function _showgeser($id){
		$this->rapyd->load('datagrid');

		$grid = new DataGrid('Lista','gser');
		$grid->db->where('ordeni',$id);
		$grid->use_function('str_pad');
		$grid->order_by('numero','desc');
		$grid->per_page = 5;

		$grid = new DataGrid();
		$grid->order_by('numero','desc');
		//$grid->per_page = 15;
		$uri=anchor('import/ordi/gseri/'.$id.'/modify/<#fecha#>/<#numero#>/<raencode><#proveed#></raencode>','<#numero#>');

		$grid->column('N&uacute;mero','numero');
		$grid->column('N. Factura','numero','numero');
		$grid->column('Proveedor','proveed');
		$grid->column('Nombre','nombre');
		$grid->column('Fecha'    ,'<dbdate_to_human><#fecha#></dbdate_to_human>','align=\'center\'');
		$grid->column('Concepto' ,'concepto');
		$grid->column('Monto'    ,'<nformat><#totneto#></nformat>','align=\'right\'');

		$grid->add('import/ordi/gser/'.$id,'Agregar gasto nacional');
		$grid->build();

		return $grid->output;
	}

	function gseri($ordi){
		$this->rapyd->load('dataobject','dataedit');

		$edit = new DataEdit('Gseri', 'gseri');
		$edit->back_url = site_url('import/ordi/dataedit/show/'.$ordi);
		$edit->post_process('insert','_post_gseri_insert');
		$edit->post_process('update','_post_gseri_insert');

		$edit->fecha = new DateonlyField('Fecha','fecha','d/m/Y');
		$edit->fecha->rule= 'required';
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size = 10;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size     = 10;
		$edit->numero->maxlength=8;

		$edit->concepto = new inputField('Concepto', 'concepto');
		$edit->concepto->size     = 35;
		$edit->concepto->maxlength= 40;

		$edit->monto = new inputField2('Monto $','monto');
		$edit->monto->rule= 'required|numeric';
		$edit->monto->size = 20;
		$edit->monto->css_class='inputnum';

		$edit->ordeni  = new autoUpdateField('ordeni',$ordi,$ordi);
		$edit->usuario = new autoUpdateField('usuario', $this->session->userdata('usuario'), $this->session->userdata('usuario'));
		$edit->hora    = new autoUpdateField('hora',date('h:i:s'),date('h:i:s'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $edit->output;
		$data['title']   = '<h1>Gasto de importaci&oacute;n</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function gser($ordi){
		$this->rapyd->load('datagrid','datafilter');

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

		$atts = array('width'      => '800', 'height'     => '600', 'scrollbars' => 'yes', 'status'     => 'yes','resizable'  => 'yes', 'screenx'    => '0','screeny'    => '0');

		$filter = new DataFilter('Filtro de Egresos');
		$filter->db->select('numero,fecha,vence,nombre,totiva,totneto,proveed,ordeni');
		$filter->db->from('gser');
		$filter->db->where('ordeni IS NULL');
		//$filter->db->orwhere('ordeni',$ordi);

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		//$filter->fechad->insertValue = date('Y-m-d'); 
		//$filter->fechah->insertValue = date('Y-m-d'); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>='; 
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size=20;

		$filter->proveedor = new inputField('Proveedor','proveed');
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = 'proveed';
		$filter->proveedor->size=20;

		/*$filter->monto  = new inputField2('Monto ','totneto');
		$filter->monto->clause='where';
		$filter->monto->operator='=';
		$filter->monto->size = 20;
		$filter->monto->css_class='inputnum';*/

		$action = "javascript:window.location='".site_url('import/ordi/dataedit/show/'.$ordi)."'";
		$filter->button("btn_regresa", 'Regresar', $action, "BL");

		$filter->buttons('reset','search');
		$filter->build();

		$uri  = anchor('finanzas/gser/dataedit/show/<#fecha#>/<#numero#>/<#proveed#>','<#numero#>');

		$grid = new DataGrid();
		$grid->order_by('numero','desc');
		$grid->use_function('checker');
		$grid->per_page = 15;

		function checker($conci,$proveed,$fecha,$numero,$ordi){
			$arr=array($fecha,$numero,$proveed,$ordi);
			if(empty($conci)){
				return form_checkbox($proveed.$fecha.$numero, serialize($arr));
			}else{
				return form_checkbox($proveed.$fecha.$numero, serialize($arr),TRUE);
			}
		}

		$uri2 = anchor_popup('formatos/verhtml/ORDI/<#numero#>',"Ver HTML",$atts);

		$grid->column('N&uacute;mero','numero');
		$grid->column('Fecha'   ,'<dbdate_to_human><#fecha#></dbdate_to_human>','align=\'center\'');
		$grid->column('Vence'   ,'<dbdate_to_human><#vence#></dbdate_to_human>','align=\'center\'');
		$grid->column('Nombre'  ,'nombre');
		$grid->column('IVA'     ,'<nformat><#totiva#></nformat>' ,'align=\'right\'');
		$grid->column('Monto'   ,'<nformat><#totneto#></nformat>','align=\'right\'');
		$grid->column('Vista'   ,'<checker><#ordeni#>|<#proveed#>|<#fecha#>|<#numero#>|'.$ordi.'</checker>','align=\'center\'');
		$grid->build();
		//echo $grid->db->last_query();

		$this->rapyd->jquery[]='$(":checkbox").change(function(){
			name=$(this).attr("name");
			$.post("'.site_url('import/ordi/agordi').'",{ data: $(this).val()},
			function(data){
					if(data=="1"){
					return true;
				}else{
					$("input[name=\'"+name+"\']").removeAttr("checked");
					alert("Hubo un error, comuniquese con soporte tecnico"+data);
					return false;
				}
			});
		});';

		$data['content'] =$filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='<h1>Relacion de gastos nacionales</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function calcula($id){
		$modo='m';
		$dbid=$this->db->escape($id);

		$mSQL="SELECT SUM(a.importefob) AS montofob, SUM(b.peso) AS pesotota
			FROM itordi AS a
			JOIN sinv AS b ON a.codigo=b.codigo
			WHERE numero=$dbid";
		$row=$this->datasis->damerow($mSQL);

		$pesotota=$row['pesotota'];
		$montofob=$row['montofob'];
		$gastosi =$this->datasis->dameval("SELECT SUM(monto)   AS gastosi FROM gseri WHERE ordeni=$dbid");
		$gastosn =$this->datasis->dameval("SELECT SUM(totneto) AS gastosn FROM gser  WHERE ordeni=$dbid");

		$mSQL="SELECT cambioofi, cambioreal FROM ordi WHERE numero=$dbid";
		$row=$this->datasis->damerow($mSQL);

		$cambioofi =$row['cambioofi'];
		$cambioreal=$row['cambioreal'];

		if($modo=='m'){
			$participa='participam'; //m para el monto;
		}else{
			$participa='participao'; //o para el peso;
		}

		//Calcula las participaciones
		$mSQL="UPDATE itordi AS a JOIN sinv AS b ON a.codigo=b.codigo SET a.participao=b.peso/$pesotota, a.iva=b.iva WHERE a.numero=$dbid";
		$this->db->simple_query($mSQL);
		$mSQL="UPDATE itordi SET participam=importefob/$montofob WHERE numero=$dbid";
		$this->db->simple_query($mSQL);

		//Gastos
		$mSQL="UPDATE itordi SET gastosi=$participa*$gastosi WHERE numero=$dbid";
		$this->db->simple_query($mSQL);
		$mSQL="UPDATE itordi SET gastosn=$participa*$gastosn WHERE numero=$dbid";
		$this->db->simple_query($mSQL);

		//CIF costo,seguro y flete
		$mSQL="UPDATE itordi SET importecif=(($participa*$gastosi)+importefob)*$cambioofi WHERE numero=$dbid";
		$this->db->simple_query($mSQL);
		$mSQL="UPDATE itordi SET costocif=importecif/cantidad WHERE numero=$dbid";
		$this->db->simple_query($mSQL);

		//Monto del arancel
		$mSQL="UPDATE itordi SET montoaran=importecif*(arancel/100) WHERE numero=$dbid";
		$this->db->simple_query($mSQL);

		//CIF total
		$mSQL="UPDATE itordi SET importefinal=importecif+montoaran+gastosn WHERE numero=$dbid";
		$this->db->simple_query($mSQL);
		$mSQL="UPDATE itordi SET costofinal=importefinal/cantidad WHERE numero=$dbid";
		$this->db->simple_query($mSQL);
		$tas=$cambioreal/$cambioofi;

		$mmSQL ='SELECT ';
		$mmSQL.="codigo,cantidad, descrip, $participa*100 AS participa,";
		$mmSQL.="costofob,ROUND(costofob*$cambioofi,2) AS fobbs, ";                                                //valor unidad fob
		$mmSQL.="importefob,ROUND(importefob*$cambioofi,2) AS totfobbs,ROUND(gastosi*$cambioofi,2) AS gastosibs,"; //Valores totales
		$mmSQL.='costocif,importecif, ';                                                                           //Valores CIF en BS
		$mmSQL.='arancel,montoaran,gastosn, ';                                                                     //Arancel1
		$mmSQL.='costofinal,importefinal, ';                                                                       //calculo al oficial
		$mmSQL.="ROUND((montoaran+gastosn+((importecif/$cambioofi)*$cambioreal))/cantidad, 2)AS costofinal2,";     //calculo al real
		$mmSQL.="ROUND(montoaran+gastosn+((importecif/$cambioofi)*$cambioreal),2) AS importefinal2 ";              //calculo real
		$mmSQL.='FROM (itordi)';
		$mmSQL.="WHERE numero = $dbid";

		//echo "$mmSQL \n";

		$data['content'] = 'Recalculo concluido '.anchor("import/ordi/dataedit/show/$id",'regresar');
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='<h1>Recalculo de la relaci&oacute;n de gastos nacionales</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function agordi(){
		$data=$this->input->post('data');

		if($data!==false){
			$pk=unserialize($data);

			$ddata = array('ordeni' => $pk[3]);
			$where  =       ' fecha = '.$this->db->escape($pk[0]);
			$where .= ' AND numero  = '.$this->db->escape($pk[1]);
			$where .= ' AND proveed = '.$this->db->escape($pk[2]);

			$mSQL = $this->db->update_string('gser', $ddata, $where);
			//echo $mSQL;
			if($this->db->simple_query($mSQL)){
				$dbnum=$this->db->escape($pk[3]);
				$gastosn=$this->datasis->dameval('SELECT SUM(totneto) FROM gser WHERE ordeni='.$dbnum);
				$mSQL="UPDATE ordi SET gastosn=$gastosn WHERE numero=$dbnum";
				$this->db->simple_query($mSQL);
				echo '1';
			}else{
				echo '0';
			}
		}
	}

	function _post_gseri_insert($do){
		$ordeni=$do->get('ordeni');
		$monto =$this->datasis->dameval("SELECT SUM(monto) FROM gseri WHERE ordeni=$ordeni");

		$data  = array('gastosi' => $monto);
		$where = "numero= $ordeni";
		$str = $this->db->update_string('ordi', $data, $where);
		$this->db->simple_query($str);
	}

	function _pre_insert($do){
		//$numero =$this->datasis->fprox_numero('nordi');
		$transac=$this->datasis->fprox_numero('transac');
		$usuario=$this->session->userdata('usuario');

		//$do->set('numero' ,$numero );
		$do->set('usuario',$usuario);
		$do->set('transac',$transac);
		$do->set('estampa',date('ymd'));
		$do->set('hora'   ,date('H:i:s'));

		//$do->pk['numero'] = $numero; //Necesario cuando la clave primara se calcula por secuencia
		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('stra',"ORDI $codigo CREADO");

		$peso=$this->datasis->dameval("SELECT SUM(b.peso) AS peso FROM itordi AS a JOIN sinv AS b ON a.codigo=b.codigo AND a.numero=$codigo");
		$data  = array('peso' => $peso);
		$where = "numero= $codigo";
		$str = $this->db->update_string('ordi', $data, $where);
		$this->db->simple_query($str);
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('ordi',"ORDI $codigo MODIFICADO");

		$peso=$this->datasis->dameval("SELECT SUM(b.peso) AS peso FROM itordi AS a JOIN sinv AS b ON a.codigo=b.codigo AND a.numero=$codigo");
		$data  = array('peso' => $peso);
		$where = "numero= $codigo";
		$str = $this->db->update_string('ordi', $data, $where);
		$this->db->simple_query($str);
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('ordi',"ORDI $codigo ELIMINADO");
	}

	function instala(){
		$mSQL='ALTER TABLE `gser`  ADD COLUMN `ordeni` INT(15) UNSIGNED NULL DEFAULT NULL AFTER `compra`';
		$this->db->simple_query($mSQL);
	}
}