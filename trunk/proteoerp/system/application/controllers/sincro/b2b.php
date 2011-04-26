<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
class b2b extends validaciones {

	function b2b(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('921',1);
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$mGRUP=array(
			'tabla'   =>'grup',
			'columnas'=>array(
				'grupo' =>'Grupo',
				'nom_grup'=>'Nombre'),
			'filtro'  =>array('grupo'=>'Grupo','nom_grup'=>'Nombre'),
			'retornar'=>array('grupo'=>'grupo'),
			'titulo'  =>'Buscar Grupo');
		$bGRUP=$this->datasis->modbus($mGRUP);

		$atts = array(
				'width'      => '400',
				'height'     => '300',
				'scrollbars' => 'yes',
				'status'     => 'yes',
				'resizable'  => 'yes',
				'screenx'   => "'+((screen.availWidth/2)-200)+'",
				'screeny'   => "'+((screen.availHeight/2)-150)+'"
			);

		$filter = new DataFilter('Filtro de b2b');
		$filter->db->select(array('a.id','a.proveed','a.usuario','a.depo AS depo','a.tipo','a.url','a.grupo','b.nombre','c.ubides'));
		$filter->db->from('b2b_config AS a');
		$filter->db->join('sprv AS b','b.proveed=a.proveed','left');
		$filter->db->join('caub AS c','c.ubica=a.depo','left');

		$filter->proveed = new inputField('Proveedor', 'proveed');
		$filter->proveed->append($bSPRV);
		$filter->proveed->size=25;

		$filter->depo = new dropdownField('Almac&eacute;n','depo');
		$filter->depo->option("","Seleccionar");
		$filter->depo->options("SELECT ubica, ubides FROM caub ORDER BY ubica");

		$filter->tipo = new dropdownField('Tipo','tipo');
		$filter->tipo->option('' ,'Selecione un tipo');
		$filter->tipo->option('I','Inventario');
		$filter->tipo->option('G','Gastos');

		$filter->grupo = new inputField('Grupo', 'grupo');
		$filter->grupo->append($bGRUP);
		$filter->grupo->size=25;
		
		$filter->buttons('reset','search');
		$filter->build();

		$acti =anchor_popup('/sincro/b2b/traecompra/<#id#>'  ,'Compras'       ,$atts);
		$acti2=anchor_popup('/sincro/b2b/traeconsigna/<#id#>','Consignaciones',$atts);
		$link=anchor('/sincro/b2b/dataedit/show/<#id#>','<#id#>');
		$grid = new DataGrid('lista de conexiones definidas');
		$grid->order_by('id','asc');
		$grid->per_page = 15;

		$grid->column_orderby('N&uacute;mero' ,$link    ,'id');
		$grid->column_orderby('Proveedor'     ,'nombre' ,'proveed');
		$grid->column_orderby('Url'           ,'url'    ,'url');
		$grid->column_orderby('Usuario'       ,'usuario','usuario');
		$grid->column_orderby('Tipo'          ,'tipo'   ,'tipo');
		$grid->column_orderby('Almac&eacute;n','ubides' ,'depo');
		$grid->column_orderby('Grupo'         ,'grupo'  ,'grupo');
		$grid->column('Traer' ,$acti.' | '.$acti2);
		$grid->add('sincro/b2b/dataedit/create');
		$grid->build();

		$smenu['link']   =barra_menu('921');
		$data['content'] =$filter->output. $grid->output;
		$data['title']   = heading('B2B');
		$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$mGRUP=array(
			'tabla'   =>'grup',
			'columnas'=>array(
				'grupo' =>'Grupo',
				'nom_grup'=>'Nombre'),
			'filtro'  =>array('grupo'=>'Grupo','nom_grup'=>'Nombre'),
			'retornar'=>array('grupo'=>'grupo'),
			'titulo'  =>'Buscar Grupo');
		$bGRUP=$this->datasis->modbus($mGRUP);

		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$script='
		<script language="javascript" type="text/javascript">
			$(function(){ $(".inputnum").numeric("."); });
		</script>';

		$edit = new DataEdit('B2B', 'b2b_config');
		$edit->pre_process('insert','_pre_inup');
		$edit->pre_process('update','_pre_inup');
		$edit->back_url = site_url('sincro/b2b/index/');

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->size      =  15;
		$edit->proveed->maxlength =  15;
		$edit->proveed->rule      = 'required';
		$edit->proveed->append($bSPRV);

		$edit->url = new inputField('Direcci&oacute;n Url', 'url');
		$edit->url->insertValue='http://';
		$edit->url->size       =  50;
		$edit->url->maxlength  =  50;
		$edit->url->rule       = 'required';
		$edit->url->append('Ej: http://www.ejemplo.com');

		$edit->puerto = new inputField('Puerto', 'puerto');
		$edit->puerto->insertValue=  80;
		$edit->puerto->size       =  5;
		$edit->puerto->maxlength  =  20;
		$edit->puerto->rule       = 'required|numeric';

		$edit->proteo = new inputField('Ruta a proteo', 'proteo');
		$edit->proteo->insertValue='proteoerp';
		$edit->proteo->size       =  20;
		$edit->proteo->maxlength  =  20;

		$edit->usuario = new inputField('Usuario', 'usuario');
		$edit->usuario->size      =  20;
		$edit->usuario->maxlength =  20;
		$edit->usuario->rule      = 'required';

		$edit->clave = new inputField('Clave', 'clave');
		$edit->clave->size      =  10;
		$edit->clave->maxlength =  10;
		$edit->clave->rule      = 'required';

		$edit->tipo = new dropdownField('Tipo','tipo');
		$edit->tipo->option('' ,'Seleccione un tipo');
		$edit->tipo->option('I','Inventario');
		$edit->tipo->option('G','Gastos');
		$edit->tipo->style ='50px';
		$edit->tipo->rule  ='required';

		$edit->depo = new dropdownField('Almac&eacute;n','depo');
		$edit->depo->option('','Seleccionar');
		$edit->depo->options('SELECT ubica,ubides FROM caub');
		$edit->depo->style ='250px';
		$edit->depo->rule  ='required';

		for($i=1;$i<=5;$i++){
			$obj='margen'.$i;
			$edit->$obj = new inputField('Margen '.$i, $obj);
			$edit->$obj->size      = 15;
			$edit->$obj->maxlength = 15;
			$edit->$obj->css_class = 'inputnum';
			$edit->$obj->rule      = 'callback_chporcent';
			$edit->$obj->group = 'Margenes de ganancia';
			$edit->$obj->autocomplete = false;
			if($i==5) $edit->$obj->append('Solo aplica a supermercados');
		}

		$edit->grupo = new inputField('Grupo', 'grupo');
		$edit->grupo->size      =  10;
		$edit->grupo->maxlength =  6;
		$edit->grupo->rule      = 'required';
		$edit->grupo->append($bGRUP);

		$edit->buttons('modify', 'save','undo', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().$script;
		$data['title']   = heading('Editar b2b');
		$this->load->view('view_ventanas', $data);
	}

	//Para visualizar las transacciones descargadas
	function scstfilter(){
		$this->rapyd->load('datagrid','datafilter');
		$this->rapyd->uri->keep_persistence();

		$atts = array(
				'width'      => '800',
				'height'     => '600',
				'scrollbars' => 'yes',
				'status'     => 'yes',
				'resizable'  => 'yes',
				'screenx'   => "'+((screen.availWidth/2)-400)+'",
				'screeny'   => "'+((screen.availHeight/2)-300)+'"
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

		$filter = new DataFilter('Filtro de Compras');
		$filter->db->select=array('numero','fecha','vence','nombre','montoiva','montonet','proveed','control');
		$filter->db->from('b2b_scst');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";

		$filter->numero = new inputField('Factura', 'numero');
		$filter->numero->size=20;

		$filter->proveedor = new inputField('Proveedor','proveed');
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = 'proveed';
		$filter->proveedor->size=20;

		$action = "javascript:window.location='".site_url('sincro/b2b/index')."'";
		$filter->button('btn_regresa', 'Regresar', $action, 'TR');
		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('sincro/b2b/scstedit/show/<#id#>','<#numero#>');

		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 15;
		$grid->column_orderby('Factura',$uri,'control');
		$grid->column_orderby('Fecha'  ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha',"align='center'");
		$grid->column_orderby('Vence'  ,'<dbdate_to_human><#vence#></dbdate_to_human>','vence',"align='center'");
		$grid->column_orderby('Nombre' ,'nombre','nombre');
		$grid->column_orderby('IVA'    ,'montoiva' ,'montoiva',"align='right'");
		$grid->column_orderby('Monto'  ,'montonet' ,'montonet',"align='right'");
		$grid->column_orderby('Control','pcontrol' ,'pcontrol',"align='right'");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   =heading('Compras de B2B');
		$this->load->view('view_ventanas', $data);
	}

	function scstedit(){
		$this->rapyd->load('dataedit','datadetalle','fields','datagrid');
		$this->rapyd->uri->keep_persistence();

		function exissinv($cen,$id=0){
			if(empty($cen)){
				$id--;
				$rt =form_button('create' ,'Crear','onclick="pcrear('.$id.');"');
				$rt.=form_button('asignar','Asig.','onclick="pasig('.$id.');"');
			}else{
				$rt='--';
			}
			return $rt;
		}

		$edit = new DataEdit('Compras','b2b_scst');
		$edit->back_url = 'sincro/b2b/scstfilter/';

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->mode ='autohide';
		$edit->fecha->size = 10;

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 15;
		$edit->numero->rule= 'required';
		$edit->numero->mode= 'autohide';
		$edit->numero->maxlength=8;

		$edit->proveedor = new inputField("Proveedor", "proveed");
		$edit->proveedor->size = 10;
		$edit->proveedor->maxlength=5;

		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;

		$edit->almacen = new inputField("Almac&eacute;n", "depo");
		$edit->almacen->size = 15;
		$edit->almacen->maxlength=8;

		$edit->tipo = new dropdownField("Tipo", "tipo_doc");
		$edit->tipo->option("FC","FC");
		$edit->tipo->rule = "required";
		$edit->tipo->size = 20;
		$edit->tipo->style='width:150px;';

		$edit->subt  = new inputField("Sub-total", "montotot");
		$edit->subt->size = 20;
		$edit->subt->css_class='inputnum';

		$edit->iva  = new inputField("Impuesto", "montoiva");
		$edit->iva->size = 20;
		$edit->iva->css_class='inputnum';

		$edit->total  = new inputField("Total global", "montonet");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';

		$edit->pcontrol  = new inputField('Control', 'pcontrol');
		$edit->pcontrol->size = 12;

		//$numero =$edit->_dataobject->get('control');
		$id =$edit->_dataobject->get('id');
		$proveed=$this->db->escape($edit->_dataobject->get('proveed'));

		$atts = array(
			'width'     => '250',
			'height'    => '250',
			'scrollbars'=> 'no',
			'status'    => 'no',
			'resizable' => 'no',
			'screenx'   => "'+((screen.availWidth/2)-175)+'",
			'screeny'   => "'+((screen.availHeight/2)-175)+'"
		);
		$llink=anchor_popup('sincro/b2b/reasignaprecio/modify/<#id#>', '<b><#precio1#></b>', $atts);

		//Campos para el detalle
		$tabla=$this->db->database;
		$detalle = new DataGrid('');
		$select=array('a.*','b.descrip AS sinvdesc','a.codigo AS barras','a.costo AS pond','a.codigolocal  AS sinv','a.codigolocal');
		$detalle->db->select($select);
		$detalle->db->from('b2b_itscst AS a');
		$detalle->db->where('a.id_scst',$id);
		$detalle->db->join('sinv AS b','a.codigolocal=b.codigo','LEFT');
		$detalle->use_function('exissinv');
		$detalle->column('Codigo sistema'    ,'<sinulo><#codigolocal#>|No tiene</sinulo>' );
		$detalle->column('Codigo prov.'      ,'<#codigo#>'   );
		$detalle->column('Descrip. Proveedor','<#descrip#>'  );
		$detalle->column('Descrip. Sistema'  ,'<#sinvdesc#>' );
		$detalle->column('Cantidad'          ,'<#cantidad#>' ,"align='right'");
		$detalle->column('PVP'               ,$llink         ,"align='right'");
		$detalle->column('Costo'             ,'<#ultimo#>'   ,"align='right'");
		$detalle->column('Importe'           ,'<#importe#>'  ,"align='right'");
		$detalle->build();
		//echo $detalle->db->last_query();

		$script='
		function pcrear(id){
			var pasar=["barras","descrip","ultimo","iva","codigo","pond","precio1","precio2","precio3","precio4"];
			var url  = "'.site_url('farmacia/sinv/dataedit/create').'";
			form_virtual(pasar,id,url);
		}
		function pasig(id){
			var pasar=["barras","proveed","descrip"];
			var url  = "'.site_url('farmacia/scst/asignardataedit/create').'";
			form_virtual(pasar,id,url);
		}
		function form_virtual(pasar,id,url){
			var data='.json_encode($detalle->data).';
			var w = window.open("'.site_url('farmacia/scst/dummy').'","asignar","width=800,height=600,scrollbars=Yes,status=Yes,resizable=Yes,screenx="+((screen.availWidth/2)-400)+",screeny="+((screen.availHeight/2)-300)+"");
			var fform  = document.createElement("form");
			fform.setAttribute("target", "asignar");
			fform.setAttribute("action", url );
			fform.setAttribute("method", "post");
			for(i=0;i<pasar.length;i++){
				Val=eval("data[id]."+pasar[i]);
				iinput = document.createElement("input");
				iinput.setAttribute("type", "hidden");
				iinput.setAttribute("name", pasar[i]);
				iinput.setAttribute("value", Val);
				fform.appendChild(iinput);
			}
			var cuerpo = document.getElementsByTagName("body")[0];
			cuerpo.appendChild(fform);
			fform.submit();
			w.focus();
			cuerpo.removeChild(fform);
		}';

		$edit->detalle=new freeField('detalle', 'detalle',$detalle->output);
		$accion="javascript:window.location='".site_url('sincro/b2b/cargacompra'.$edit->pk_URI())."'";
		$pcontrol=$edit->_dataobject->get('pcontrol');
		if(is_null($pcontrol)) $edit->button_status('btn_cargar','Cargar',$accion,'TR','show');
		$edit->buttons('save','undo','back');

		$edit->script($script,'show');
		$edit->build();

		$this->rapyd->jquery[]='$("#dialog").dialog({
			autoOpen: false,
			show: "blind",
			hide: "explode"
		});
		$( "#opener" ).click(function() {
			$( "#dialog" ).dialog( "open" );
			return false;
		});';

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_b2b_compras', $conten,true); 
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Compras Descargadas');
		$this->load->view('view_ventanas', $data);
	}

	//Consignacion de inventario
	function psinvfilter(){
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
			'retornar'=>array('proveed'=>'clipro'),
			'titulo'  =>'Buscar Proveedor');
		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter('Filtro de Conseci&oacute;n de inventario');
		$filter->db->select('fecha,numero,clipro,nombre,stotal,gtotal,impuesto');
		$filter->db->from('b2b_psinv');

		$filter->fechad = new dateonlyField('Desde', 'fechad');
		$filter->fechah = new dateonlyField('Hasta', 'fechah');
		$filter->fechad->clause  =$filter->fechah->clause ='where';
		$filter->fechad->db_name =$filter->fechah->db_name='fecha';
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechah->operator='<=';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 30;

		$filter->factura = new inputField('Factura', 'factura');
		$filter->factura->size = 30;

		$filter->cliente = new inputField("Cliente",'clipro');
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('sincro/b2b/psinvedit/show/<#numero#>','<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/PSINV/<#numero#>',"Ver HTML",$atts);

		$grid = new DataGrid();
		$grid->order_by('numero','desc');
		$grid->per_page = 15;

		$grid->column_orderby('N&uacute;mero' ,$uri,'numero');
		$grid->column_orderby('Fecha'         ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha',"align='center'");
		$grid->column_orderby('Nombre'        ,'nombre','nombre');
		$grid->column_orderby('Sub.Total'     ,'<nformat><#stotal#></nformat>'  ,'stotal','align=\'right\'');
		$grid->column_orderby('IVA'           ,'<nformat><#impuesto#></nformat>','iva'   ,'align=\'right\'');
		$grid->column_orderby('Total'         ,'<nformat><#gtotal#></nformat>'  ,'gtotal','align=\'right\'');
		//$grid->column_orderby("Vista",$uri2,"align='center'");

		$grid->add('inventario/psinv/dataedit/create');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Consignaci&oacute;n de inventario');
		$this->load->view('view_ventanas', $data);
	}

	function psinvedit(){
		$this->rapyd->load('dataobject','datadetails');

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
		'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
		'retornar'=>array(
			'codigo' =>'codigo_<#i#>',
			'descrip'=>'desca_<#i#>',
			'base1'  =>'precio1_<#i#>',
			'base2'  =>'precio2_<#i#>',
			'base3'  =>'precio3_<#i#>',
			'base4'  =>'precio4_<#i#>',
			'iva'    =>'itiva_<#i#>',
			'peso'   =>'sinvpeso_<#i#>',
			'tipo'   =>'sinvtipo_<#i#>',
		),
		'p_uri'=>array(4=>'<#i#>'),
		'where'   => '`activo` = "S" AND `tipo` = "Articulo"',
		'script'  => array('post_modbus_sinv(<#i#>)'),
		'titulo'  =>'Buscar Art&iacute;culo');
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre', 
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n',
			'tipo'=>'Tipo'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre',
						  'dire11'=>'dir_cli','tipo'=>'sclitipo'),
		'titulo'  =>'Buscar Cliente',
		'script'  => array('post_modbus_scli()'));
		$btnc =$this->datasis->modbus($mSCLId);

		$do = new DataObject('b2b_psinv');
		$do->rel_one_to_many('itpsinv', 'b2b_itpsinv', 'numero');
		//$do->pointer('scli' ,'scli.cliente=psinv.cod_cli','scli.tipo AS sclitipo','left');
		$do->rel_pointer('itpsinv','sinv','b2b_itpsinv.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');
		
		$edit = new DataDetails('Pr&eacute;stamo de inventario', $do);
		$edit->back_url = site_url('inventario/psinv/filteredgrid');
		$edit->set_rel_title('itpsinv','Producto <#o#>');

		$edit->back_url = site_url('sincro/b2b/psinvfilter'); //$this->back_dataedit;

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;

		/*$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->option('R','Recibido');
		$edit->tipo->option('C','Cedido');
		//$edit->tipo->option('X','Anulado');
		$edit->tipo->style='width:160px';*/

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');
		$edit->numero->mode='autohide';

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;
		$edit->peso->mode='autohide';

		$edit->clipro = new inputField('Cliente','clipro');
		$edit->clipro->size = 6;
		$edit->clipro->maxlength=5;
		$edit->clipro->rule = 'required';
		$edit->clipro->append($btnc);
		$edit->clipro->mode='autohide';

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->mode='autohide';

		$edit->almacen = new  dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub ORDER BY ubica');
		$edit->almacen->rule = 'required';
		$edit->almacen->style='width:200px;';
		$edit->almacen->size = 5;
		$edit->almacen->mode='autohide';

		$edit->orden = new inputField("Orden", "orden");
		$edit->orden->mode='autohide';
		$edit->orden->size = 10;

		$edit->observa = new inputField("Observaci&oacute;n", "observa");
		$edit->observa->size = 37;

		//Campos para el detalle
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itpsinv';
		$edit->codigo->rule     = 'required';
		$edit->codigo->readonly=false;
		$edit->codigo->append($btn);

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=36;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=50;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='itpsinv';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'itpsinv';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive';
		$edit->cana->autocomplete=false;
		$edit->cana->readonly=false;
		$edit->cana->onkeyup  ='importe(<#i#>)';

		$edit->canareci = new inputField('Cantidad recibida <#o#>', 'canareci_<#i#>');
		$edit->canareci->db_name  = 'canareci';
		$edit->canareci->css_class= 'inputnum';
		$edit->canareci->rel_id   = 'itpsinv';
		$edit->canareci->maxlength= 10;
		$edit->canareci->size     = 6;
		$edit->canareci->rule     = 'required|positive';
		$edit->canareci->autocomplete=false;
		$edit->canareci->onkeyup  ='importe(<#i#>)';

		$edit->precio = new inputField('Precio <#o#>', 'precio_<#i#>');
		$edit->precio->db_name   = 'precio';
		$edit->precio->css_class = 'inputnum';
		$edit->precio->rel_id    = 'itpsinv';
		$edit->precio->size      = 10;
		$edit->precio->readonly=false;
		$edit->precio->rule      = 'required|positive|callback_chpreca[<#i#>]';
		$edit->precio->readonly  = true;

		$edit->importe = new inputField('Importe <#o#>', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=10;
		$edit->importe->readonly=false;
		$edit->importe->css_class='inputnum';
		$edit->importe->rel_id   ='itpsinv';

		for($i=1;$i<=4;$i++){
			$obj='precio'.$i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj.'_<#i#>');
			$edit->$obj->db_name   = 'sinv'.$obj;
			$edit->$obj->rel_id    = 'itpsinv';
			$edit->$obj->pointer   = true;
		}
		$edit->itiva = new hiddenField('', 'itiva_<#i#>');
		$edit->itiva->db_name  = 'iva';
		$edit->itiva->rel_id   = 'itpsinv';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name   = 'sinvpeso';
		$edit->sinvpeso->rel_id    = 'itpsinv';
		$edit->sinvpeso->pointer   = true;

		$edit->sinvtipo = new hiddenField('', 'sinvtipo_<#i#>');
		$edit->sinvtipo->db_name   = 'sinvtipo';
		$edit->sinvtipo->rel_id    = 'itpsinv';
		$edit->sinvtipo->pointer   = true;
		//fin de campos para detalle

		$edit->impuesto  = new inputField('Impuesto', 'impuesto');
		$edit->impuesto->size = 20;
		$edit->impuesto->mode='autohide';
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->readonly=false;

		$edit->stotal  = new inputField('Sub.Total', 'stotal');
		$edit->stotal->size = 20;
		$edit->stotal->mode='autohide';
		$edit->stotal->css_class='inputnum';

		$edit->gtotal  = new inputField('Total', 'gtotal');
		$edit->gtotal->size = 20;
		$edit->gtotal->mode='autohide';
		$edit->gtotal->css_class='inputnum';

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$action = "javascript:window.location='".site_url('sincro/b2b/cargarpsinv/'.$edit->_dataobject->pk['numero'])."'";
		$edit->button_status('btn_conci', 'Cargar Conciliacion', $action, 'TR','show');

		$edit->buttons('save','modify', 'undo', 'delete', 'back');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_b2b_psinv', $conten,true);
		$data['title']   = heading('Consiganci&oacute;n de inventario');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}


//****************************************************
// Metodos para gestionar transacciones como compras
//****************************************************
	function traecompra($par,$ultimo=null){
		$rt=$this->_trae_compra($par,$ultimo);
		if($rt==0){
			$str=$this->comprasCargadas.' transacciones descargadas';
		}else{
			$str='Hubo problemas durante la  descarga, se generar&oacute;n centinelas';
		}
		$data['content'] = $str;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Compras Descargadas');
		$this->load->view('view_ventanas_sola', $data);
	}

	function _trae_compra($id=null,$ultimo=null){
		$this->comprasCargadas=0;
		if(is_null($id)) return false; else $id=$this->db->escape($id);
		$contribu=$this->datasis->traevalor('CONTRIBUYENTE');
		$rif     =$this->datasis->traevalor('RIF');

		$config=$this->datasis->damerow("SELECT proveed,grupo,puerto,proteo,url,usuario,clave,tipo,depo,margen1,margen2,margen3,margen4,margen5 FROM b2b_config WHERE id=$id");
		if(count($config)==0) return false;

		$er=0;
		$this->load->helper('url');
		$server_url = reduce_double_slashes($config['url'].'/'.$config['proteo'].'/'.'rpcserver');

		$this->load->library('xmlrpc');
		$this->xmlrpc->xmlrpc_defencoding=$this->config->item('charset');
		//$this->xmlrpc->set_debug(TRUE);
		$puerto= (empty($config['puerto'])) ? 80 : $config['puerto'];

		$this->xmlrpc->server($server_url , $puerto);
		$this->xmlrpc->method('cea');

		if(is_null($ultimo)){
			$ufac=$this->datasis->dameval('SELECT MAX(numero) FROM b2b_scst WHERE proveed='.$this->db->escape($config['proveed']));
			if(empty($ufac)) $ufac=0;
		}elseif(is_numeric($ultimo)){
			$ufac=$ultimo;
		}else{
			$ufac=0;
		}

		$request = array($ufac,$config['proveed'],$config['usuario'],md5($config['clave']));
		$this->xmlrpc->request($request);

		if (!$this->xmlrpc->send_request()){
			memowrite($this->xmlrpc->display_error(),'B2B');
			return true;
		}else{
			$res=$this->xmlrpc->display_response();
			foreach($res AS $ind=>$compra){
				$this->comprasCargadas++;
				$arr=unserialize($compra);
				foreach($arr['scst'] AS $in => $val) $arr[$in]=base64_decode($val);

				$proveed=$config['proveed'];
				$pnombre=$this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($proveed));

				$data['proveed']  = $proveed;
				$data['nombre']   = $pnombre;
				$data['tipo_doc'] = 'FC';
				$data['depo']     = $config['depo'];
				$data['fecha']    = $arr['fecha'];
				$data['vence']    = $arr['vence'];
				$data['numero']   = $arr['numero'];
				$data['serie']    = $arr['nfiscal'];
				$data['montotot'] = $arr['totals'];
				$data['montoiva'] = $arr['iva'];
				$data['montonet'] = $arr['totalg'];

				if($contribu=='ESPECIAL' and strtoupper($rif[0])!='V'){
					$por_rete=$this->datasis->dameval('SELECT reteiva FROM sprv WHERE proveed='.$this->db->escape($proveed));
					if($por_rete!=100){
						$por_rete=0.75;
					}else{
						$por_rete=$por_rete/100;}
					$data['reteiva']=round($data['montoiva']*$por_rete,2);
				}

				$data['reducida'] =$arr['monredu'];
				$data['tasa']     =$arr['montasa'];
				$data['sobretasa']=$arr['monadic'];
				$data['monredu']  =$arr['monredu'];
				$data['montasa']  =$arr['montasa'];
				$data['monadic']  =$arr['monadic'];
    
				$data['ctotal']   =$arr['totalg'];
				$data['cstotal']  =$arr['totals'];
				$data['cexento']  =$arr['exento'];
				$data['cimpuesto']=$arr['iva'];
				$data['cgenera']  =$arr['montasa'];
				$data['civagen']  =$arr['tasa'];
				$data['cadicio']  =$arr['monadic'];
				$data['civaadi']  =$arr['sobretasa'];
				$data['creduci']  =$arr['monredu'];
				$data['civared']  =$arr['reducida'];

				$mSQL=$this->db->insert_string('b2b_scst',$data);

				$rt=$this->db->simple_query($mSQL);
				if(!$rt){
					memowrite($mSQL,'B2B');
					$maestro=false;
					$er++;
				}else{
					$id_scst=$this->db->insert_id();
					$maestro=true;
				}

				if($maestro){
					$itscst =& $arr['itscst'];
					foreach($itscst AS $in => $aarr){
						foreach($aarr AS $i=>$val) $arr[$in][$i]=base64_decode($val);

						//Arregla los precios en caso de llegar malos
						for($j=1;$j<5;$j++){
							$ind='precio'.$j;
							if($arr[$in][$ind]<0 && $j>1){
								$ind2='precio'.$i-1;
								$arr[$in][$ind]=$arr[$in][$ind];
							}elseif($arr[$in][$ind]<0 && $j==1){
								$arr[$in][$ind]=round(($arr[$in]['preca']*100/(100-$config['margen1'])),2);
							}
						}

						$barras=trim($arr[$in]['barras']);
						$ddata['id_scst']  = $id_scst;
						$ddata['proveed']  = $proveed;
						$ddata['fecha']    = $data['fecha'];
						$ddata['numero']   = $data['numero'];
						$ddata['depo']     = $data['depo'];
						$ddata['codigo']   = trim($arr[$in]['codigoa']);
						$ddata['descrip']  = $arr[$in]['desca'];
						$ddata['cantidad'] = $arr[$in]['cana'];
						$ddata['costo']    = $arr[$in]['preca'];
						$ddata['importe']  = $arr[$in]['tota'];
						$ddata['garantia'] = 0;
						$ddata['ultimo']   = $arr[$in]['preca'];
						$ddata['precio1']  = ($config['margen1']==0)? $arr[$in]['precio1'] : round(($arr[$in]['preca']*100/(100-$config['margen1'])),2);
						$ddata['precio2']  = ($config['margen2']==0)? $arr[$in]['precio2'] : round(($arr[$in]['preca']*100/(100-$config['margen2'])),2);
						$ddata['precio3']  = ($config['margen3']==0)? $arr[$in]['precio3'] : round(($arr[$in]['preca']*100/(100-$config['margen3'])),2);
						$ddata['precio4']  = ($config['margen4']==0)? $arr[$in]['precio4'] : round(($arr[$in]['preca']*100/(100-$config['margen4'])),2);
						$ddata['montoiva'] = $arr[$in]['tota']*($arr[$in]['iva']/100);
						$ddata['iva']      = $arr[$in]['iva'];
						$ddata['barras']   = $barras;

						//procedimiento de determinacion del codigo del articulo en sistema local
						$codigolocal=false;
						if(!empty($barras)){
							$mSQL_p = 'SELECT codigo FROM sinv';
							$bbus   = array('codigo','barras','alterno');
							$query=$this->_gconsul($mSQL_p,$barras,$bbus);
							if($query){
								$row = $query->row();
								$codigolocal=$row->codigo;
							}
						}elseif(!empty($ddata['codigo'])){
							$mSQL_p = 'SELECT codigo FROM sinv';
							$bbus   = array('codigo','barras','alterno');
							$query=$this->_gconsul($mSQL_p,$ddata['codigo'],$bbus);
							if($query){
								$row = $query->row();
								$codigolocal=$row->codigo;
								$barras     =$codigolocal;
							}
						}
						//if($codigolocal===false AND $this->db->table_exists('sinvprov')){
						//	$codigolocal=$this->datasis->dameval('SELECT codigo FROM sinvprov WHERE proveed='.$this->db->escape($proveed).' AND codigop='.$this->db->escape($arr[$in]['codigoa']));
						//}

						//Si no existe lo crea
						if(empty($codigolocal)){
							$base1 = ($arr[$in]['precio1']*100)/(100+$arr[$in]['iva']);
							$base2 = ($arr[$in]['precio2']*100)/(100+$arr[$in]['iva']);
							$base3 = ($arr[$in]['precio3']*100)/(100+$arr[$in]['iva']);
							$base4 = ($arr[$in]['precio4']*100)/(100+$arr[$in]['iva']);
							$invent['codigo']   = $barras;
							$invent['grupo']    = $config['grupo'];
							$invent['prov1']    = $proveed;
							$invent['descrip']  = $arr[$in]['desca'];
							$invent['existen']  = $arr[$in]['cana'];
							$invent['pond']     = $arr[$in]['preca'];
							$invent['ultimo']   = $arr[$in]['preca'];
							$invent['unidad']   = $arr[$in]['unidad'];
							$invent['tipo']     = $arr[$in]['tipo'];
							$invent['tdecimal'] = $arr[$in]['tdecimal'];
							$invent['margen1']  = round(100-($arr[$in]['preca']*100/$base1),2);
							$invent['margen2']  = round(100-($arr[$in]['preca']*100/$base2),2);
							$invent['margen3']  = round(100-($arr[$in]['preca']*100/$base3),2);
							$invent['margen4']  = round(100-($arr[$in]['preca']*100/$base4),2);
							$invent['base1']    = round($base1,2);
							$invent['base2']    = round($base2,2);
							$invent['base3']    = round($base3,2);
							$invent['base4']    = round($base4,2);
							$invent['precio1']  = ($config['margen1']==0)? $arr[$in]['precio1'] : round(($arr[$in]['preca']*100/(100-$config['margen1'])),2);
							$invent['precio2']  = ($config['margen2']==0)? $arr[$in]['precio2'] : round(($arr[$in]['preca']*100/(100-$config['margen2'])),2);
							$invent['precio3']  = ($config['margen3']==0)? $arr[$in]['precio3'] : round(($arr[$in]['preca']*100/(100-$config['margen3'])),2);
							$invent['precio4']  = ($config['margen4']==0)? $arr[$in]['precio4'] : round(($arr[$in]['preca']*100/(100-$config['margen4'])),2);
							$invent['iva']      = $arr[$in]['iva'];
							$invent['redecen']  = 'N';
							$invent['activo']   = 'S';
							$invent['formcal']  = 'U';
							$invent['clase']    = 'C';
							$invent['garantia'] = 0;

							$mSQL=$this->db->insert_string('sinv',$invent);
							$rt=$this->db->simple_query($mSQL);
							if(!$rt){
								memowrite($mSQL,'B2B');
								$er++;
							}else{
								$codigolocal=$barras;
							}
						}
						$ddata['codigolocal'] = $codigolocal;

						$mSQL=$this->db->insert_string('b2b_itscst',$ddata);
						$rt=$this->db->simple_query($mSQL);
						if(!$rt){
							memowrite($mSQL,'B2B');
							$er++;
						}
					}
					if(!$this->_cargacompra($id_scst)) $er=false;

					//Carga el inventario
					/*$ddata=array();
					$sinv=&$arr['sinv'];
					foreach($sinv as $in => $aarr){
						foreach($aarr AS $i=>$val)
							$sinv[$in][$i]=base64_decode($val);
						$sinv[$in]['proveed']  = $proveed;
						$mSQL=$this->db->insert_string('b2b_sinv',$sinv[$in]);
						$mSQL.=' ON DUPLICATE KEY UPDATE precio1='.$sinv[$in]['precio1'].',precio2='.$sinv[$in]['precio2'].',precio3='.$sinv[$in]['precio3'].',precio4='.$sinv[$in]['precio4'];
						$rt=$this->db->simple_query($mSQL);
						if(!$rt) memowrite($mSQL,'B2B');
					}*/
				}
			}
		}
		return $er;
	}

	function cargacompra($id){
		$rt=$this->_cargacompra($id);
		redirect('sincro/b2b/scstedit/show/'.$id);
	}

	function _cargacompra($id){
		$error   =0;
		$pcontrol=$this->datasis->dameval('SELECT pcontrol FROM b2b_scst WHERE  id='.$this->db->escape($id));

		if(empty($pcontrol)){
			$eexiste = 0;
		}else{
			$eexiste = $this->datasis->dameval('SELECT COUNT(*) FROM scst WHERE  control='.$this->db->escape($pcontrol));
			if(empty($eexiste)){
				$eexisten = 0;
			}
		}

		$cana=$this->datasis->dameval('SELECT COUNT(*) FROM b2b_itscst AS a LEFT JOIN sinv AS b ON a.codigolocal=b.codigo WHERE a.numero IS NULL AND id_scst='.$this->db->escape($id));
		if($cana==0 AND $eexiste==0){
			$control=$this->datasis->fprox_numero('nscst');
			$transac=$this->datasis->fprox_numero('ntransa');
			//$tt['montotot']=$tt['montoiva']=$tt['montonet']=0;
			$estampa=date('Y-m-d');
			$hora   =date('h:m:s');

			$query = $this->db->query('SELECT fecha,numero,proveed,depo,codigolocal AS codigo,descrip,cantidad,devcant,devfrac,costo,importe,iva,montoiva,garantia,ultimo,precio1,precio2,precio3,precio4,licor FROM b2b_itscst WHERE id_scst=?',array($id));
			if ($query->num_rows() > 0){
				foreach ($query->result_array() as $itrow){
					$itrow['estampa'] = $estampa;
					$itrow['hora']    = $hora;
					$itrow['control'] = $control;
					$itrow['transac'] = $transac;

					//$tt['montotot']+=$itrow['importe'];
					//$tt['montoiva']+=$itrow['montoiva'];
					//$tt['montonet']+=$itrow['importe']+$itrow['montoiva'];
					$mSQL=$this->db->insert_string('itscst',$itrow);
					$rt=$this->db->simple_query($mSQL);
					if(!$rt){ memowrite($mSQL,'B2B'); $error++;}
				}
			}

			$query = $this->db->query('SELECT fecha,numero,depo,proveed,nombre,montotot,montoiva,montonet,vence,tipo_doc,
				peso,usuario,nfiscal,exento,sobretasa,reducida,tasa,montasa,monredu,monadic,TRIM(serie) AS serie,
				cimpuesto,ctotal,cstotal,civaadi,cadicio,civared,creduci,civagen,cgenera,cexento,reteiva
				FROM b2b_scst WHERE id=?',array($id));
			if ($query->num_rows() > 0){

				$row = $query->row_array();
				$row['estampa'] = $estampa;
				$row['hora']    = $hora;
				$row['control'] = $control;
				$row['transac'] = $transac;
				$row['usuario'] = $this->session->userdata('usuario');
				if(empty($row['serie'])){
					$row['serie'] = $row['numero'];
				}else{
					$row['numero']  = substr($row['serie'],-8);
				}

				//$row['montotot'] =$tt['montotot'];
				//$row['montoiva'] =$tt['montoiva'];
				//$row['montonet'] =$tt['montonet'];

				$mSQL=$this->db->insert_string('scst',$row);
				$rt=$this->db->simple_query($mSQL);
				if(!$rt){memowrite($mSQL,'B2B'); $error++;}
			}

			$mSQL="UPDATE b2b_scst SET pcontrol='$control' WHERE id=".$this->db->escape($id);
			$rt=$this->db->simple_query($mSQL);
			if(!$rt){memowrite($mSQL,'B2B'); $error++; }
		}
		return ($error==0) ? true : false;
	}
//****************************************************
// Metodos para gestionar las consignaciones
//****************************************************
	function traeconsigna($par,$ultimo=null){
		$rt=$this->_trae_consigna($par,$ultimo);
		if($rt==0){
			$str=$this->consignaCargadas.' transacciones descargadas';
		}else{
			$str='Hubo problemas durante la  descarga, se generar&oacute;n centinelas';
		}
		$data['content'] = $str;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Consginaciones Descargadas');
		$this->load->view('view_ventanas_sola', $data);
	}

	function _trae_consigna($id=null,$ultimo=null){
		$this->consignaCargadas=0;
		if(is_null($id)) return false; else $id=$this->db->escape($id);
		$contribu=$this->datasis->traevalor('CONTRIBUYENTE');
		$rif     =$this->datasis->traevalor('RIF');

		$config=$this->datasis->damerow("SELECT proveed,grupo,puerto,proteo,url,usuario,clave,tipo,depo,margen1,margen2,margen3,margen4,margen5 FROM b2b_config WHERE id=$id");
		if(count($config)==0) return false;

		$er=0;
		$this->load->helper('url');
		$server_url = reduce_double_slashes($config['url'].'/'.$config['proteo'].'/'.'rpcserver');

		$this->load->library('xmlrpc');
		$this->xmlrpc->xmlrpc_defencoding=$this->config->item('charset');
		//$this->xmlrpc->set_debug(TRUE);
		$puerto= (empty($config['puerto'])) ? 80 : $config['puerto'];

		$this->xmlrpc->server($server_url , $puerto);
		$this->xmlrpc->method('consiea');

		if(is_null($ultimo)){
			$ufac=$this->datasis->dameval('SELECT MAX(orden) FROM b2b_psinv WHERE clipro='.$this->db->escape($config['proveed']));
			if(empty($ufac)) $ufac=0;
		}elseif(is_numeric($ultimo)){
			$ufac=$ultimo;
		}else{
			$ufac=0;
		}

		$request = array($ufac,$config['proveed'],$config['usuario'],md5($config['clave']));
		$this->xmlrpc->request($request);

		if (!$this->xmlrpc->send_request()){
			memowrite($this->xmlrpc->display_error(),'B2B');
			return true;
		}else{
			$res=$this->xmlrpc->display_response();
			foreach($res AS $ind=>$exdata){
				$this->consignaCargadas++;
				$arr=unserialize($exdata);
				foreach($arr['psinv'] AS $in => $val) $arr[$in]=base64_decode($val);

				$proveed=$config['proveed'];
				$pnombre=$this->datasis->dameval('SELECT nombre FROM sprv WHERE proveed='.$this->db->escape($proveed));

				$data=array();
				$data['fecha']      = $arr['fecha'];
				$data['status']     = 'T';
				$data['clipro']     = $proveed;
				$data['almacen']    = $config['depo'];
				$data['nombre']     = $pnombre;
				$data['orden']      = $arr['numero'];
				$data['observa']    = $arr['observa'];
				$data['stotal']     = $arr['stotal'];
				$data['impuesto']   = $arr['impuesto'];
				$data['gtotal']     = $arr['gtotal'];
				$data['tipo']       = 'R';
				$data['peso']       = $arr['peso'];
				$data['estampa']    = date('Ymd');

				$mSQL=$this->db->insert_string('b2b_psinv',$data);

				$rt=$this->db->simple_query($mSQL);
				if(!$rt){
					memowrite($mSQL,'B2B');
					$maestro=false;
					$er++;
				}else{
					$id_psinv=$this->db->insert_id();
					$maestro=true;
				}

				if($maestro){
					$itscst =& $arr['itpsinv'];
					foreach($itscst AS $in => $aarr){
						foreach($aarr AS $i=>$val) $arr[$in][$i]=base64_decode($val);

						//Arregla los precios en caso de llegar malos
						for($j=1;$j<5;$j++){
							$ind='precio'.$j;
							if($arr[$in][$ind]<0 && $j>1){
								$ind2='precio'.$i-1;
								$arr[$in][$ind]=$arr[$in][$ind];
							}elseif($arr[$in][$ind]<0 && $j==1){
								$arr[$in][$ind]=round(($arr[$in]['precio']*100/(100-$config['margen1'])),2);
							}
						}
						$ddata=array();

						$ddata['numero']     =$id_psinv;
						$ddata['codigo']     =trim($arr[$in]['codigo']);
						$ddata['desca']      =$arr[$in]['desca'];
						$ddata['cana']       =$arr[$in]['cana'];
						$ddata['canareci']   =$arr[$in]['cana'];
						$ddata['precio']     =$arr[$in]['precio'];
						$ddata['importe']    =$arr[$in]['importe'];
						$ddata['iva']        =$arr[$in]['iva'];
						//$ddata['mostrado']   =$arr[$in]['mostrado'];
						//$ddata['entregado']  ='';
						$ddata['tipo']       ='R';

						$barras=trim($arr[$in]['barras']);

						//procedimiento de determinacion del codigo del articulo en sistema local
						$codigolocal=false;
						if(!empty($barras)){
							$mSQL_p = 'SELECT codigo FROM sinv';
							$bbus   = array('codigo','barras','alterno');
							$query=$this->_gconsul($mSQL_p,$barras,$bbus);
							if($query){
								$row = $query->row();
								$codigolocal=$row->codigo;
							}
						}elseif(!empty($ddata['codigo'])){
							$mSQL_p = 'SELECT codigo FROM sinv';
							$bbus   = array('codigo','barras','alterno');
							$query=$this->_gconsul($mSQL_p,$ddata['codigo'],$bbus);
							if($query){
								$row = $query->row();
								$codigolocal=$row->codigo;
								$barras=$codigolocal;
							}
						}

						//Si no existe lo crea
						if(empty($codigolocal)){
							$base1 = ($arr[$in]['precio1']*100)/(100+$arr[$in]['iva']);
							$base2 = ($arr[$in]['precio2']*100)/(100+$arr[$in]['iva']);
							$base3 = ($arr[$in]['precio3']*100)/(100+$arr[$in]['iva']);
							$base4 = ($arr[$in]['precio4']*100)/(100+$arr[$in]['iva']);
							$invent['codigo']   = $barras;
							$invent['grupo']    = $config['grupo'];
							$invent['prov1']    = $proveed;
							$invent['descrip']  = $arr[$in]['desca'];
							$invent['existen']  = $arr[$in]['cana'];
							$invent['pond']     = $arr[$in]['precio'];
							$invent['ultimo']   = $arr[$in]['precio'];
							$invent['unidad']   = $arr[$in]['unidad'];
							$invent['tipo']     = $arr[$in]['tipo'];
							$invent['tdecimal'] = $arr[$in]['tdecimal'];
							$invent['margen1']  = round(100-($arr[$in]['precio']*100/$base1),2);
							$invent['margen2']  = round(100-($arr[$in]['precio']*100/$base2),2);
							$invent['margen3']  = round(100-($arr[$in]['precio']*100/$base3),2);
							$invent['margen4']  = round(100-($arr[$in]['precio']*100/$base4),2);
							$invent['base1']    = round($base1,2);
							$invent['base2']    = round($base2,2);
							$invent['base3']    = round($base3,2);
							$invent['base4']    = round($base4,2);
							$invent['precio1']  = ($config['margen1']==0)? $arr[$in]['precio1'] : round(($arr[$in]['preca']*100/(100-$config['margen1'])),2);
							$invent['precio2']  = ($config['margen2']==0)? $arr[$in]['precio2'] : round(($arr[$in]['preca']*100/(100-$config['margen2'])),2);
							$invent['precio3']  = ($config['margen3']==0)? $arr[$in]['precio3'] : round(($arr[$in]['preca']*100/(100-$config['margen3'])),2);
							$invent['precio4']  = ($config['margen4']==0)? $arr[$in]['precio4'] : round(($arr[$in]['preca']*100/(100-$config['margen4'])),2);
							$invent['iva']      = $arr[$in]['iva'];
							$invent['redecen']  = 'N';
							$invent['activo']   = 'S';
							$invent['formcal']  = 'U';
							$invent['clase']    = 'C';
							$invent['garantia'] = 0;

							$mSQL=$this->db->insert_string('sinv',$invent);
							$rt=$this->db->simple_query($mSQL);
							if(!$rt){
								memowrite($mSQL,'B2B');
								$er++;
							}else{
								$codigolocal=$barras;
							}
						}
						$ddata['codigolocal'] = $codigolocal;

						$mSQL=$this->db->insert_string('b2b_itpsinv',$ddata);
						$rt=$this->db->simple_query($mSQL);
						if(!$rt){
							memowrite($mSQL,'B2B');
							$er++;
						}
					}
				}
			}
		}
		return $er;
	}

	function cargarpsinv($id){
		$rt=$this->_cargarpsinv($id);
		//redirect('sincro/b2b/psinvedit/show/'.$id);
	}

	function _cargarpsinv($id){
		$er=0;

		$query=$this->db->query('SELECT numero,fecha,vende,status,factura,clipro,almacen,nombre,orden,observa,stotal,impuesto,gtotal,tipo,peso,estampa,pcontrol FROM b2b_psinv WHERE numero = ?',$id);

		if ($query->num_rows() > 0){
			$row = $query->row();

			$transac= $this->datasis->fprox_numero('ntransa');
			$estampa=date('Ymd');
			$hora   =date('H:m:s');
			$data['fecha']      =$row->fecha;
			$data['vende']      ='';
			$data['status']     ='R';
			$data['factura']    ='';
			$data['clipro']     =$row->clipro;
			$data['almacen']    =$row->almacen;
			$data['nombre']     =$row->nombre;
			$data['dir_clipro'] ='';
			$data['orden']      =$row->orden;
			$data['observa']    =$row->observa;
			$data['stotal']     =$row->stotal;
			$data['impuesto']   =$row->impuesto;
			$data['gtotal']     =$row->gtotal;
			$data['tipo']       ='R';
			$data['peso']       =$row->peso;
			$data['agente']     ='sprv';
			$data['estampa']    =$estampa;
			$data['usuario']    =$this->session->userdata('usuario');
			$data['hora']       =$estampa;
			$data['transac']    =$transac;

			$mSQL=$this->db->insert_string('psinv',$data);
			$rt=$this->db->simple_query($mSQL);
			if(!$rt){
				memowrite($mSQL,'B2B');
				$maestro=false;
				$er++;
			}else{
				$id_psinv=$this->db->insert_id();
				$maestro=true;
			}

			if($maestro){
				$data=array();
				$importe=0;
				$qquery=$this->db->query('SELECT numero,codigo,codigolocal,desca,cana,canareci,precio,importe,iva,mostrado,entregado,tipo,id,modificado FROM b2b_itpsinv WHERE numero = ?',$id);
				foreach ($qquery->result() as $rrow){
					$data['numero']   = $id_psinv;
					$data['codigo']   = $rrow->codigolocal;
					$data['desca']    = $rrow->desca;
					$data['cana']     = $rrow->canareci;
					$data['canareci'] = $rrow->canareci;
					$data['precio']   = $rrow->precio;
					$data['importe']  = $rrow->canareci*$rrow->precio;
					$data['iva']      = $rrow->iva;
					$data['mostrado'] = $rrow->precio*(100+$rrow->iva)/100;
					$data['tipo']     = 'R';
					$importe += $data['importe'];

					$mSQL=$this->db->insert_string('itpsinv',$data);
					$rt  =$this->db->simple_query($mSQL);
					if(!$rt){
						memowrite($mSQL,'B2B');
						$er++;
					}
				}
			}
		}
		return $er;
	}
//****************************************************
// Metodos para gestionar transacciones como gasto
//****************************************************

	function cargagasto(){
		
	}

	function _cargagasto(){
		
	}

	function reasignaprecio(){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Cambios de precios','b2b_itscst');
		$edit->descrip  = new inputField('Descripci&oacute;n', 'descrip');
		$edit->descrip->mode = 'autohide';

		for($i=1;$i<5;$i++){
			$obj='precio'.$i;
			$edit->$obj = new inputField('Precio '.$i, $obj);
			$edit->$obj->css_class='inputnum';
			$edit->$obj->rule ='numeric';
			$edit->$obj->size = 10;
		}

		$edit->buttons('modify','save');
		$edit->build();
		$this->rapyd->jquery[]='$(window).unload(function() { window.opener.location.reload(); });';
		$data['content'] =$edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   ='';
		$this->load->view('view_ventanas_sola', $data);
	}

	function sprvexits($proveed){
		$mSQL='SELECT COUNT(*) FROM sprv WHERE proveed='.$this->db->escape($proveed);
		$cana=$this->datasis->dameval($mSQL);
		if($cana==0){
			$error="El proveedor dado no exite";
			$this->validation->set_message('sprvexits',$error);
			return false;
		}
		return true;
	}

	function noexiste($barras){
		$mSQL='SELECT COUNT(*) FROM sinv WHERE codigo='.$this->db->escape($barras);
		$cana=$this->datasis->dameval($mSQL);
		if($cana!=0){
			$error="El c&oacute;digo de barras '$barras' existe en el iventario, la equivalencia se debe aplicar en un producto que no exista";
			$this->validation->set_message('noexiste',$error);
			return false;
		}
		return true;
	}

	function siexiste($barras){
		$mSQL='SELECT COUNT(*) FROM sinv WHERE codigo='.$this->db->escape($barras);
		$cana=$this->datasis->dameval($mSQL);
		if($cana==0){
			$error="El c&oacute;digo de barras '$barras' no existe en el iventario";
			$this->validation->set_message('siexiste',$error);
			return false;
		}
		return true;
	}


	function dummy(){
		echo "<p aling='center'>Redirigiendo la p&aacute;gina</p>";
	}

	function _gconsul($mSQL_p,$cod_bar,$busca,$suple=null){
		if(!empty($suple) AND $this->db->table_exists('suple')){
			$mSQL  ="SELECT codigo FROM suple WHERE suplemen='${cod_bar}' LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() != 0){
				$row = $query->row();
				$busca  =array($suple);
				$cod_bar=$row->codigo;
			}
		}
		foreach($busca AS $b){
			$mSQL  =$mSQL_p." WHERE ${b}='${cod_bar}' LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() != 0){
				return $query;
			}
		}
		if ($this->db->table_exists('barraspos')) {
			$mSQL  ="SELECT codigo FROM barraspos WHERE suplemen=".$this->db->escape($cod_bar)." LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() != 0){
				$row = $query->row();
				$cod_bar=$row->codigo;

				$mSQL  =$mSQL_p." WHERE codigo='${cod_bar}' LIMIT 1";
				$query = $this->db->query($mSQL);
				if($query->num_rows() == 0)
					return false;
			}else{
				return false;
			}
		}else{
			return false;
		}
		return $query;
	}

	function _pre_inup($do){
		for($i=1;$i<6;$i++){
			$mar='margen'.$i;
			$$mar=round($do->get($mar),2); //optenemos el margen
		}

		if($margen1>=$margen2 && $margen2>=$margen3 && $margen3>=$margen4 && $margen4>=$margen5){
			return true;
		}else{
			$do->error_message_ar['pre_upd'] = 'Los margenes deben cumplir con:<br> Margen 1 mayor o igual al Margen 2 mayor o igual al  Margen 3 mayor o igual al Margen 4 mayor o igual al Margen 5';
			return false;
		}
	}

	function instalar(){
		$mSQL="CREATE TABLE `b2b_config` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `proveed` char(5)   NOT NULL COMMENT 'Codigo del proveedor',
		  `url` varchar(100)  NOT NULL,
		  `puerto` int(5) NOT NULL DEFAULT '80',
		  `proteo` varchar(20) NOT NULL DEFAULT 'proteoerp',
		  `usuario` varchar(100) NOT NULL COMMENT 'Codigo de cliente en el proveedor',
		  `clave` varchar(100) NOT NULL,
		  `tipo` char(1) DEFAULT NULL COMMENT 'I para inventario G para gasto',
		  `depo` varchar(4) DEFAULT NULL COMMENT 'Almacen',
		  `margen1` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio1',
		  `margen2` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio 2',
		  `margen3` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio3',
		  `margen4` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio4',
		  `margen5` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio5 (solo supermercado)',
		  `grupo` varchar(5) DEFAULT NULL COMMENT 'Grupo por defecto',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 COMMENT='Configuracion para los b2b'";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="CREATE TABLE `b2b_scst` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `fecha` date DEFAULT NULL,
		  `numero` varchar(8) DEFAULT NULL,
		  `proveed` varchar(5) DEFAULT NULL,
		  `nombre` varchar(30) DEFAULT NULL,
		  `depo` varchar(4) DEFAULT NULL,
		  `montotot` decimal(17,2) DEFAULT NULL,
		  `montoiva` decimal(17,2) DEFAULT NULL,
		  `montonet` decimal(17,2) DEFAULT NULL,
		  `vence` date DEFAULT NULL,
		  `tipo_doc` char(2) DEFAULT NULL,
		  `control` varchar(8) NOT NULL DEFAULT '',
		  `peso` decimal(12,2) DEFAULT NULL,
		  `estampa` date DEFAULT NULL,
		  `hora` varchar(8) DEFAULT NULL,
		  `usuario` varchar(12) DEFAULT NULL,
		  `nfiscal` varchar(12) DEFAULT NULL,
		  `exento` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `sobretasa` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `reducida` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `tasa` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `montasa` decimal(17,2) DEFAULT NULL,
		  `monredu` decimal(17,2) DEFAULT NULL,
		  `monadic` decimal(17,2) DEFAULT NULL,
		  `serie` char(12) DEFAULT NULL,
		  `pcontrol` char(8) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `proveednum` (`proveed`,`numero`),
		  KEY `proveedor` (`proveed`)
		) ENGINE=MyISAM AUTO_INCREMENT=1";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="CREATE TABLE `b2b_itscst` (
		  `id_scst` int(11) DEFAULT NULL,
		  `fecha` date DEFAULT NULL,
		  `numero` varchar(8) DEFAULT NULL,
		  `proveed` varchar(5) DEFAULT NULL,
		  `depo` varchar(4) DEFAULT NULL,
		  `codigo` varchar(15) DEFAULT NULL,
		  `descrip` varchar(45) DEFAULT NULL,
		  `cantidad` decimal(10,3) DEFAULT NULL,
		  `devcant` decimal(10,3) DEFAULT NULL,
		  `devfrac` int(4) DEFAULT NULL,
		  `costo` decimal(17,2) DEFAULT NULL,
		  `importe` decimal(17,2) DEFAULT NULL,
		  `iva` decimal(5,2) DEFAULT NULL,
		  `montoiva` decimal(17,2) DEFAULT NULL,
		  `garantia` int(3) DEFAULT NULL,
		  `ultimo` decimal(17,2) DEFAULT NULL,
		  `precio1` decimal(15,2) DEFAULT NULL,
		  `precio2` decimal(15,2) DEFAULT NULL,
		  `precio3` decimal(15,2) DEFAULT NULL,
		  `precio4` decimal(15,2) DEFAULT NULL,
		  `estampa` date DEFAULT NULL,
		  `hora` varchar(8) DEFAULT NULL,
		  `usuario` varchar(12) DEFAULT NULL,
		  `licor` decimal(10,2) DEFAULT '0.00',
		  `barras` varchar(15) DEFAULT NULL,
		  `codigolocal` varchar(15) DEFAULT NULL,
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  PRIMARY KEY (`id`),
		  KEY `id_scst` (`id_scst`),
		  KEY `fecha` (`fecha`),
		  KEY `codigo` (`codigo`),
		  KEY `proveedor` (`proveed`),
		  KEY `numero` (`numero`)
		) ENGINE=MyISAM AUTO_INCREMENT=1";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN `reteiva` DECIMAL(17,2) NULL DEFAULT '0.00' AFTER `reducida`;";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN  `cexento` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `cgenera` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `civagen` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `creduci` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `civared` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `cadicio` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `civaadi` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `cstotal` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `ctotal` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `cimpuesto` decimal(17,2) DEFAULT NULL AFTER `reducida`";
		var_dump($this->db->simple_query($mSQL));
		
		$mSQL="CREATE TABLE `b2b_psinv` (
			`numero` INT(12) NOT NULL AUTO_INCREMENT,
			`fecha` DATE NULL DEFAULT NULL,
			`vende` VARCHAR(5) NULL DEFAULT NULL,
			`status` CHAR(1) NULL DEFAULT NULL COMMENT 'T=en trancito C=conciliado',
			`factura` VARCHAR(8) NULL DEFAULT NULL,
			`cod_cli` VARCHAR(5) NULL DEFAULT NULL,
			`almacen` VARCHAR(4) NULL DEFAULT NULL,
			`nombre` VARCHAR(40) NULL DEFAULT NULL,
			`orden` VARCHAR(12) NULL DEFAULT NULL,
			`observa` VARCHAR(105) NULL DEFAULT NULL,
			`stotal` DECIMAL(12,2) NULL DEFAULT NULL,
			`impuesto` DECIMAL(12,2) NULL DEFAULT NULL,
			`gtotal` DECIMAL(12,2) NULL DEFAULT NULL,
			`tipo` CHAR(1) NULL DEFAULT NULL,
			`peso` DECIMAL(15,3) NULL DEFAULT NULL,
			`estampa` DATE NULL DEFAULT NULL,
			PRIMARY KEY (`numero`),
			INDEX `factura` (`factura`)
		)
		COLLATE='latin1_swedish_ci'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="CREATE TABLE `b2b_itpsinv` (
			`numero` INT(12) NULL DEFAULT NULL,
			`codigo` VARCHAR(15) NULL DEFAULT NULL,
			`desca` VARCHAR(28) NULL DEFAULT NULL,
			`cana` DECIMAL(12,3) NULL DEFAULT '0.000',
			`canareci` DECIMAL(12,3) NULL DEFAULT '0.000',
			`precio` DECIMAL(12,2) NULL DEFAULT NULL,
			`importe` DECIMAL(12,2) NULL DEFAULT NULL,
			`iva` DECIMAL(6,2) NULL DEFAULT NULL,
			`mostrado` DECIMAL(17,2) NULL DEFAULT NULL,
			`entregado` DECIMAL(12,3) NULL DEFAULT NULL,
			`tipo` CHAR(1) NULL DEFAULT NULL,
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`modificado` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE INDEX `numero_codigo` (`numero`, `codigo`),
			INDEX `numero` (`numero`),
			INDEX `codigo` (`codigo`),
			INDEX `modificado` (`modificado`)
		)
		COLLATE='latin1_swedish_ci'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";
		var_dump($this->db->simple_query($mSQL));
	}
}