<?php
class psinv extends Controller {

	function psinv(){
		parent::Controller(); 
		$this->load->library('rapyd');
		$this->datasis->modulo_id('320',1);
		$this->back_dataedit='inventario/psinv';
	}

	function index() {
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

		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');

		$boton=$this->datasis->modbus($scli);

		$filter = new DataFilter('Filtro de Prestamos de inventario');
		$filter->db->select('fecha,numero,cod_cli,nombre,stotal,gtotal,impuesto');
		$filter->db->from('psinv');

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

		$filter->cliente = new inputField("Cliente","cod_cli");
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('inventario/psinv/dataedit/show/<#numero#>','<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/PSINV/<#numero#>',"Ver HTML",$atts);

		$grid = new DataGrid();
		$grid->order_by("numero","desc");
		$grid->per_page = 15;  

		$grid->column_orderby("N&uacute;mero" ,$uri,'numero');
		$grid->column_orderby('Fecha'         ,"<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
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

	function dataedit(){
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

		$do = new DataObject('psinv');
		$do->rel_one_to_many('itpsinv', 'itpsinv', 'numero');
		$do->pointer('scli' ,'scli.cliente=psinv.cod_cli','scli.tipo AS sclitipo','left');
		$do->rel_pointer('itpsinv','sinv','itpsinv.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');
		
		$edit = new DataDetails('Pr&eacute;stamo de inventario', $do);
		$edit->back_url = site_url('inventario/psinv/filteredgrid');
		$edit->set_rel_title('itpsinv','Producto <#o#>');

		$edit->back_url = $this->back_dataedit;

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

		$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->option('R','Recibido');
		$edit->tipo->option('C','Cedido');
		//$edit->tipo->option('X','Anulado');
		$edit->tipo->style='width:160px';

		$edit->vende = new  dropdownField ('Vendedor', 'vende');
		$edit->vende->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vende->style='width:200px;';
		$edit->vende->size = 5;

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

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->rule = 'required';
		$edit->cliente->append($btnc);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;

		$edit->factura = new inputField('Factura', 'factura');
		$edit->factura->size = 10;
		$edit->factura->when=array('show');

		$edit->almacen = new  dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub ORDER BY ubica');
		$edit->almacen->rule = 'required';
		$edit->almacen->style='width:200px;';
		$edit->almacen->size = 5;

		$edit->orden = new inputField("Orden", "orden");
		$edit->orden->size = 10;

		$edit->observa = new inputField("Observaci&oacute;n", "observa");
		$edit->observa->size = 37;

		$edit->dir_cli = new inputField("Direcci&oacute;n","dir_cli");
		$edit->dir_cli->size = 37;

		//$edit->dir_cl1 = new inputField(" ","dir_cl1");
		//$edit->dir_cl1->size = 55; 

		//Para saber que precio se le va a dar al cliente
		$edit->sclitipo = new hiddenField('', 'sclitipo');
		$edit->sclitipo->db_name     = 'sclitipo';
		$edit->sclitipo->pointer     = true;
		$edit->sclitipo->insertValue = 1;

		//Campos para el detalle
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->readonly = true;
		$edit->codigo->rel_id   = 'itpsinv';
		$edit->codigo->rule     = 'required';
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
		$edit->cana->onkeyup  ='importe(<#i#>)';

		$edit->precio = new inputField('Precio <#o#>', 'precio_<#i#>');
		$edit->precio->db_name   = 'precio';
		$edit->precio->css_class = 'inputnum';
		$edit->precio->rel_id    = 'itpsinv';
		$edit->precio->size      = 10;
		$edit->precio->rule      = 'required|positive|callback_chpreca[<#i#>]';
		$edit->precio->readonly  = true;

		$edit->importe = new inputField('Importe <#o#>', 'importe_<#i#>');
		$edit->importe->db_name='importe';
		$edit->importe->size=10;
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
		$edit->impuesto->css_class='inputnum';

		$edit->stotal  = new inputField('Sub.Total', 'stotal');
		$edit->stotal->size = 20;
		$edit->stotal->css_class='inputnum';

		$edit->gtotal  = new inputField('Total', 'gtotal');
		$edit->gtotal->size = 20;
		$edit->gtotal->css_class='inputnum';

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_psinv', $conten,true);
		$data['title']   = heading('Consiganci&oacute;n de inventario');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		//$numero = $this->datasis->fprox_numero('npsinv');
		$transac= $this->datasis->fprox_numero('ntransa');
		$fecha  = $do->get('fecha');
		$vende  = $do->get('vende');
		$usuario= $do->get('usuario');
		$estampa= date('Ymd');
		$hora   = date("H:i:s");

		$iva=$stotal=0;
		$cana=$do->count_rel('itpsinv');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itpsinv','cana',$i);
			$itprecio  = $do->get_rel('itpsinv','precio',$i);
			$itiva     = $do->get_rel('itpsinv','iva',$i);
			$itimporte = $itprecio*$itcana;
			$iiva      = $itimporte*($itiva/100);

			$do->set_rel('itpsinv','importe'  ,$itimporte,$i);
			$do->set_rel('itpsinv','mostrado' ,$itimporte+$iiva,$i);

			$iva    +=$iiva ;
			$stotal +=$itimporte;
		}

		$gtotal=$stotal+$iva;
		$do->set('estampa' ,$estampa);
		$do->set('hora'    ,$hora);
		$do->set('transac' ,$transac);
		$do->set('stotal'  ,round($stotal,2));
		$do->set('gtotal'  ,round($gtotal,2));
		$do->set('impuesto',round($iva   ,2));

		return true;
	}

	function _post_insert($do){
		$codigo = $do->get('numero');
		$almacen= $do->get('almacen');
		$tipo   = $do->get('tipo');
		$fact = ($tipo=='R') ? 1 : -1;

		$mSQL='UPDATE sinv JOIN itpsinv ON sinv.codigo=itpsinv.codigo SET sinv.existen=sinv.existen+('.$fact.')*(itpsinv.cana) WHERE itpsinv.numero='.$this->db->escape($codigo);
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'psinv'); }

		$mSQL='UPDATE itsinv JOIN itpsinv ON itsinv.codigo=itpsinv.codigo SET itsinv.existen=itsinv.existen+('.$fact.')(itpsinv.cana) WHERE itpsinv.numero='.$this->db->escape($codigo).' AND itsinv.alma='.$this->db->escape($almacen);
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'psinv'); }

		$codigo=$do->get('numero');
		logusu('psinv',"Prestamo de inventario $codigo CREADO");
	}

	function chpreca($preca,$ind){
		$codigo  = $this->input->post('codigo_'.$ind);
		$precio4 = $this->datasis->dameval('SELECT base4 FROM sinv WHERE codigo='.$this->db->escape($codigo));
		if($precio4<0) $precio4=0;

		if($preca<$precio4){
			$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' debe contener un precio de al menos '.nformat($precio4));
			return false;
		}else{
			return true;
		}
	}

	function _pre_update($do){
		return false;
	}

	function _post_delete($do){
		/*$codigo=$do->get('numero');

		$cana=$do->count_rel('itpsinv');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itpsinv','cana',$i);
			$itprecio  = $do->get_rel('itpsinv','precio',$i);
			$itiva     = $do->get_rel('itpsinv','iva',$i);
			$itimporte = $itprecio*$itcana;
			$iiva      = $itimporte*($itiva/100);

			$do->set_rel('itpsinv','importe'  ,$itimporte,$i);
			$do->set_rel('itpsinv','mostrado' ,$itimporte+$iiva,$i);

			$iva    +=$iiva ;
			$stotal +=$itimporte;
		}
		
		
		logusu('psinv',"Nota Entrega $codigo ELIMINADO");*/
	}

	function instalar(){
		$mSQL="CREATE TABLE  IF NOT EXISTS `psinv` (
				`numero` INT(12) NOT NULL AUTO_INCREMENT,
				`fecha` DATE NULL DEFAULT NULL,
				`vende` VARCHAR(5) NULL DEFAULT NULL,
				`factura` VARCHAR(8) NULL DEFAULT NULL,
				`cod_cli` VARCHAR(5) NULL DEFAULT NULL,
				`almacen` VARCHAR(4) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL,
				`dir_cli` VARCHAR(40) NULL DEFAULT NULL,
				`dir_cl1` VARCHAR(40) NULL DEFAULT NULL,
				`orden` VARCHAR(12) NULL DEFAULT NULL,
				`observa` VARCHAR(105) NULL DEFAULT NULL,
				`stotal` DECIMAL(12,2) NULL DEFAULT NULL,
				`impuesto` DECIMAL(12,2) NULL DEFAULT NULL,
				`gtotal` DECIMAL(12,2) NULL DEFAULT NULL,
				`cajero` VARCHAR(5) NULL DEFAULT NULL,
				`fechafac` DATE NULL DEFAULT NULL,
				`tipo` CHAR(1) NULL DEFAULT NULL,
				`peso` DECIMAL(15,3) NULL DEFAULT NULL,
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT NULL,
				`hora` VARCHAR(4) NULL DEFAULT NULL,
				`transac` VARCHAR(8) NULL DEFAULT NULL,
				`modificado` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`numero`),
				INDEX `factura` (`factura`),
				INDEX `modificado` (`modificado`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="CREATE TABLE IF NOT EXISTS `itpsinv` (
				`numero` INT(12) NULL DEFAULT NULL,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`desca` VARCHAR(28) NULL DEFAULT NULL,
				`cana` DECIMAL(12,3) NULL DEFAULT '0.000',
				`precio` DECIMAL(12,2) NULL DEFAULT NULL,
				`importe` DECIMAL(12,2) NULL DEFAULT NULL,
				`iva` DECIMAL(6,2) NULL DEFAULT NULL,
				`mostrado` DECIMAL(17,2) NULL DEFAULT NULL,
				`entregado` DECIMAL(12,3) NULL DEFAULT NULL,
				`tipo` CHAR(1) NULL DEFAULT NULL,
				`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				`modificado` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				INDEX `numero` (`numero`),
				INDEX `codigo` (`codigo`),
				INDEX `modificado` (`modificado`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="ALTER TABLE `itpsinv`  ADD COLUMN `canareci` DECIMAL(12,3) NULL DEFAULT '0.000' AFTER `cana`";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="ALTER TABLE `itpsinv`  ADD UNIQUE INDEX `numero_codigo` (`numero`, `codigo`)";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="ALTER TABLE `psinv`  ADD COLUMN `status` CHAR(1) NULL DEFAULT NULL COMMENT 'T=en trancito C=conciliado' AFTER `vende`";
		var_dump($this->db->simple_query($mSQL));
	}
}