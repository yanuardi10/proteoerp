<?php
class stal extends Controller {
	var $titp='Taller';
	var $tits='Taller';
	var $url ='taller/stal/';

	function stal(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(216,1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'stal');

		$filter->numero = new inputField('Numero','numero');
		$filter->numero->rule      ='max_length[8]';
		$filter->numero->size      =10;
		$filter->numero->maxlength =8;

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;
		
		$filter->factura = new inputField('Factura','factura');
		$filter->factura->rule      ='max_length[8]';
		$filter->factura->size      =10;
		$filter->factura->maxlength =8;

		$filter->cod_cli = new inputField('Cliente','cod_cli');
		$filter->cod_cli->rule      ='max_length[5]';
		$filter->cod_cli->size      =7;
		$filter->cod_cli->maxlength =5;

		$filter->codigo = new inputField('Codigo','codigo');
		$filter->codigo->rule      ='max_length[15]';
		$filter->codigo->size      =17;
		$filter->codigo->maxlength =15;
		
		$filter->serial = new inputField('Serial','serial');
		$filter->serial->rule      ='max_length[15]';
		$filter->serial->size      =17;
		$filter->serial->maxlength =15;

		$filter->falla = new textareaField('Falla','falla');
		$filter->falla->rule      ='max_length[8]';
		$filter->falla->cols = 70;
		$filter->falla->rows = 4;

		$filter->observa = new textareaField('Observacion','observa');
		$filter->observa->rule      ='max_length[8]';
		$filter->observa->cols = 70;
		$filter->observa->rows = 4;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Id'       ,$uri                                          ,'id'         ,'align="left"');
		$grid->column_orderby('Numero'   ,'numero'                                      ,'numero'     ,'align="left"');
		$grid->column_orderby('Fecha'    ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha'      ,'align="center"');
		$grid->column_orderby('Factura'  ,'factura'                                     ,'factura'    ,'align="left"');
		$grid->column_orderby('Nombre'   ,'nombre'                                      ,'nombre'     ,'align="left"');
		$grid->column_orderby('Descripcion'  ,'descrip'                                     ,'descrip'    ,'align="left"');
		$grid->column_orderby('Almacen'    ,'ubica'                                       ,'ubica'      ,'align="left"');
		$grid->column_orderby('Recibio'  ,'nom_rec'                                     ,'nom_rec'    ,'align="left"');
		$grid->column_orderby('Tecnico'  ,'nom_tec'                                     ,'nom_tec'    ,'align="left"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);

	}

	function dataedit(){
		$this->rapyd->load('dataedit','dataobject');
		
			
		$modbus=array(
			'tabla'   =>'tecn',
			'columnas'=>array(
				'codigo'   =>'C&oacute;digo',
				'nombre'   =>'Nombre',
				'clave'    =>'Clave',
				'telefono' =>'Tel&eacute;fono',
				),
			'filtro'  =>array(
				'codigo'   =>'C&oacute;digo',
				'nombre'   =>'Nombre',
				'clave'    =>'Clave',
				'telefono' =>'Tel&eacute;fono',
			),
			'retornar'=>array(
				'codigo'  =>'tecnico',
				'nombre' =>'nom_tec',
				),
			'titulo'  => 'Buscar T&eacute;cnico',
			//'where'   => '',
		);
		$bTECN=$this->datasis->modbus($modbus);

		$modbus=array(
			'tabla'   =>'recep',
			'columnas'=>array(
				'sfac.numero'  =>'Factura',
				'DATE_FORMAT(sfac.fecha,"%d/%m/%Y")'   =>'Fecha',
				'scli.nombre'  =>'Nombre',
				'sinv.descrip' =>'Descripci&oacute;n',
				'sinv.barras'  =>'Barras',
				'sinv.marca'   =>'Marca',
				'sinv.modelo'  =>'Modelo',
				'seri.serial'  =>'Serial',
			),
			'filtro'  =>array(
				'sfac.numero'  =>'N&uacute;mero',
				'scli.nombre'  =>'Nombre',
				'sinv.descrip' =>'Descripci&oacute;n',
				'sinv.barras'  =>'Barras',
				'sinv.marca'   =>'Marca',
				'sinv.modelo'  =>'Modelo',
				'seri.serial'  =>'Serial',
			),
			'retornar'=>array(
				array('sfac.numero'  =>'factura'                           ),
				array('sfac.numero'  =>'factura_val'                       ),
				array('DATE_FORMAT(sfac.fecha,"%d/%m/%Y")'   =>'fecha_fac'    ),
				array('DATE_FORMAT(sfac.fecha,"%d/%m/%Y")'   =>'fecha_fac_val'),
				array('sfac.cod_cli' =>'cod_cli'                          ),
				array('sfac.cod_cli' =>'cod_cli_val'                      ),
				array('scli.nombre'  =>'nombre'                           ),
				array('scli.nombre'  =>'nombre_val'                       ),
				array('sinv.codigo'  =>'codigo'                           ),
				array('sinv.codigo'  =>'codigo_val'                       ),
				array('sinv.descrip' =>'descrip'                          ),
				array('sinv.descrip' =>'descrip_val'                      ),
				array('sinv.ubica'   =>'ubica'                            ),
				array('sinv.ubica'   =>'ubica_val'                        ),
				array('sinv.marca'   =>'marca'                            ),
				array('sinv.marca'   =>'marca_val'                        ),
				array('sinv.modelo'  =>'modelo'                           ),
				array('sinv.modelo'  =>'modelo_val'                       ),
				array('sinv.precio1' =>'valor'                            ),
				array('sinv.precio1' =>'valor_val'                        ),
				array('seri.serial'  =>'serial'                           ),
			),
			'titulo'  => 'Buscar Factura',
			'join'    =>array(
			array('seri','recep.recep=seri.recep',''),
			array('sitems','seri.codigo=sitems.codigoa',''),
			array('sinv','sitems.codigoa=sinv.codigo',''),
			array('sfac','sitems.numa=sfac.numero AND sitems.tipoa=sfac.tipo_doc  AND recep.clipro=sfac.cod_cli',''),
			array('scli','sfac.cod_cli=scli.cliente',''),
			),
			'where'   =>'recep.tipo ="E"',
			'groupby' =>'recep.recep,seri.codigo,seri.serial',
			'order_by'=>'max(fecha)'
		);
		$bSFACS=$this->datasis->modbus($modbus);

		$do = new DataObject('stal');
		//$do->rel_one_to_many('itstal', 'itstal', array('id' => 'id_stal'));
		//$do->pointer('scli' , 'scli.cliente=pfac.cod_cli', 'scli.tipo AS sclitipo', 'left');
		//$do->rel_pointer('itpfac', 'sinv', 'itpfac.codigoa=sinv.codigo', 'sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo,sinv.precio1 As sinvprecio1,sinv.pond AS sinvpond,sinv.mmargen as sinvmmargen,sinv.ultimo sinvultimo,sinv.formcal sinvformcal,sinv.pm sinvpm,itpfac.preca precat');

		$edit = new DataEdit($this->tits, $do);

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->id = new inputField('Id','id');
		$edit->id->mode ='autohide';
		$edit->id->when =array('show');

		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;
		$edit->numero->when  =array('show','modify');

		$edit->fecha = new dateonlyField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;
		$edit->fecha->insertValue=date('Ymd');
		

		$edit->falla = new textareaField('Falla','falla');
		$edit->falla->rule='max_length[8]';
		$edit->falla->cols = 60;
		$edit->falla->rows = 1;
		$edit->falla->rule ='required';
		
		$edit->observa = new textareaField('Observaci&oacute;n','observa');
		$edit->observa->rule='max_length[8]';
		$edit->observa->cols = 60;
		$edit->observa->rows = 1;

		$usr=$this->session->userdata('usuario');
		$edit->receptor = new inputField('Receptor','receptor');
		$edit->receptor->rule ='max_length[5]';
		$edit->receptor->size =7;
		$edit->receptor->maxlength =5;
		$edit->receptor->type='inputhidden';
		$edit->receptor->readonly=true;
		$edit->receptor->value=$usr;

		$nombre=$this->datasis->dameval("SELECT us_nombre FROM usuario WHERE us_codigo='$usr'");
		$edit->nom_rec = new inputField('Nombre','nom_rec');
		$edit->nom_rec->rule='max_length[28]';
		$edit->nom_rec->size =30;
		$edit->nom_rec->maxlength =28;
		$edit->nom_rec->readonly=true;
		$edit->nom_rec->in='receptor';
		$edit->nom_rec->type='inputhidden';
		$edit->nom_rec->value=$nombre;
		

		$edit->tecnico = new inputField('T&eacute;cnico','tecnico');
		$edit->tecnico->rule='max_length[5]';
		$edit->tecnico->size =7;
		$edit->tecnico->maxlength =5;
		$edit->tecnico->append($bTECN);
		$edit->tecnico->readonly=true;
		$edit->tecnico->rule    ='required';

		$edit->nom_tec = new inputField('Nombre','nom_tec');
		$edit->nom_tec->rule='max_length[28]';
		$edit->nom_tec->size =30;
		$edit->nom_tec->maxlength =28;
		$edit->nom_tec->readonly=true;
		$edit->nom_tec->type='inputhidden';

		$edit->factura = new inputField('Factura','factura');
		$edit->factura->rule='max_length[8]';
		$edit->factura->size =10;
		$edit->factura->maxlength =8;
		//$edit->factura->append($bSFAC);
		$edit->factura->readonly=true;
		$edit->factura->type='inputhidden';

		$edit->fecha_fac = new dateonlyField('Fecha Factura','fecha_fac');
		$edit->fecha_fac->rule='chfecha';
		$edit->fecha_fac->size =10;
		$edit->fecha_fac->maxlength =8;
		$edit->fecha_fac->readonly=true;
		$edit->fecha_fac->type='inputhidden';

		$edit->cod_cli = new inputField('Cliente','cod_cli');
		$edit->cod_cli->rule='max_length[5]';
		$edit->cod_cli->size =7;
		$edit->cod_cli->maxlength =5;
		$edit->cod_cli->readonly=true;
		$edit->cod_cli->type='inputhidden';

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->size =27;
		$edit->nombre->readonly=true;
		$edit->nombre->type='inputhidden';

		$edit->codigo = new inputField('C&oacute;digo','codigo');
		$edit->codigo->rule       ='max_length[15]';
		$edit->codigo->size       =10;
		$edit->codigo->maxlength  =15;
		$edit->codigo->readonly   =true;
		//$edit->codigo->append($bSINV);
		$edit->codigo->type='inputhidden';

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->size      =30;
		$edit->descrip->maxlength =38;
		$edit->descrip->readonly=true;
		$edit->descrip->type='inputhidden';

		$edit->marca = new inputField('Marca','marca');
		$edit->marca->rule='max_length[38]';
		$edit->marca->size =20;
		$edit->marca->maxlength =38;
		$edit->marca->readonly=true;
		$edit->marca->type='inputhidden';

		$edit->ubica = new inputField('Almacen','ubica');
		$edit->ubica->rule='max_length[10]';
		$edit->ubica->size =12;
		$edit->ubica->maxlength =10;
		$edit->ubica->readonly=true;
		$edit->ubica->type='inputhidden';

		$edit->modelo = new inputField('Modelo','modelo');
		$edit->modelo->rule='max_length[15]';
		$edit->modelo->size =17;
		$edit->modelo->maxlength =15;
		$edit->modelo->readonly=true;
		$edit->modelo->type='inputhidden';

		$edit->serial = new inputField('Serial','serial');
		$edit->serial->rule='max_length[15]';
		$edit->serial->size =17;
		$edit->serial->maxlength =15;
		$edit->serial->readonly=true;
		$edit->serial->append($bSFACS);

		$edit->valor = new inputField('Valor','valor');
		$edit->valor->rule='max_length[17]|numeric';
		$edit->valor->css_class='inputnum';
		$edit->valor->size =19;
		$edit->valor->maxlength =17;
		$edit->valor->readonly  =true;
		$edit->valor->type      ='inputhidden';
		
		$edit->salida = new dateonlyField('Salida','salida');
		$edit->salida->rule='chfecha';
		$edit->salida->size =10;
		$edit->salida->maxlength =8;

		$edit->estatus = new inputField('Estatus','estatus');
		$edit->estatus->rule='max_length[1]';
		$edit->estatus->size =3;
		$edit->estatus->maxlength =1;

		$edit->tipo = new inputField('Tipo','tipo');
		$edit->tipo->rule='max_length[1]';
		$edit->tipo->size =3;
		$edit->tipo->maxlength =1;

		$edit->codnue = new inputField('Codnue','codnue');
		$edit->codnue->rule='max_length[15]';
		$edit->codnue->size =17;
		$edit->codnue->maxlength =15;

		$edit->serinu = new inputField('Serinu','serinu');
		$edit->serinu->rule='max_length[14]';
		$edit->serinu->size =16;
		$edit->serinu->maxlength =14;

		$edit->horas = new inputField('Horas','horas');
		$edit->horas->rule='max_length[10]|numeric';
		$edit->horas->css_class='inputnum';
		$edit->horas->size =12;
		$edit->horas->maxlength =10;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add');
		$edit->build();

		$script= '<script type="text/javascript" > 
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});
		</script>';

		$conten['form']  = & $edit;
		$data['content'] = $this->load->view('view_stal', $conten, true);
		$data['title']   = heading('Orden de Taller Nro '.$edit->numero->value);
		$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$data['script'] .= $script;
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		exit('_._._._|_:_:_:_:');
		return false;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}

	function instalar(){
		if (!$this->db->table_exists('stal')) {
			$mSQL="CREATE TABLE `stal` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `numero` char(8) DEFAULT NULL,
			  `fecha` date DEFAULT NULL,
			  `factura` char(8) DEFAULT NULL,
			  `fecha_fac` date DEFAULT NULL,
			  `cod_cli` char(5) DEFAULT NULL,
			  `nombre` char(25) DEFAULT NULL,
			  `codigo` char(15) DEFAULT NULL,
			  `descrip` char(38) DEFAULT NULL,
			  `marca` char(38) DEFAULT NULL,
			  `ubica` char(10) DEFAULT NULL,
			  `modelo` char(15) DEFAULT NULL,
			  `serial` char(15) DEFAULT NULL,
			  `valor` decimal(17,2) DEFAULT NULL,
			  `falla` text,
			  `observa` text,
			  `receptor` char(5) DEFAULT NULL,
			  `nom_rec` char(28) DEFAULT NULL,
			  `tecnico` char(5) DEFAULT NULL,
			  `nom_tec` char(28) DEFAULT NULL,
			  `salida` date DEFAULT NULL,
			  `estatus` char(1) DEFAULT NULL,
			  `tipo` char(1) DEFAULT NULL,
			  `codnue` char(15) DEFAULT NULL,
			  `serinu` char(14) DEFAULT NULL,
			  `horas` decimal(10,2) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}
		if (!$this->db->table_exists('itstal')) {
			$mSQL="CREATE TABLE `itstal` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `numero` char(8) DEFAULT NULL,
			  `fecha` date DEFAULT NULL,
			  `descrip` text,
			  `cantidad` decimal(10,3) DEFAULT NULL,
			  `monto` decimal(17,2) DEFAULT NULL,
			  `tecnico` char(5) DEFAULT NULL,
			  `tipo` char(1) DEFAULT NULL,
			  `horas` decimal(10,2) DEFAULT NULL,
			  `id_stal` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($mSQL);
		}
	}
	
	function prueba(){
	}
}
?>
