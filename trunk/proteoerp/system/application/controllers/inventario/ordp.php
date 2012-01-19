<?php
class ordp extends Controller {
	var $titp='Orden de producci&oacute;n';
	var $tits='Orden de producci&oacute;n';
	var $url ='inventario/ordp/';

	function ordp(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('324',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'ordp');

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->numero = new inputField('Numero','numero');
		$filter->numero->rule      ='max_length[8]';
		$filter->numero->size      =10;
		$filter->numero->maxlength =8;

		$filter->status = new inputField('Status','status');
		$filter->status->rule      ='max_length[2]';
		$filter->status->size      =4;
		$filter->status->maxlength =2;

		$filter->cliente = new inputField('Cliente','cliente');
		$filter->cliente->rule      ='max_length[5]';
		$filter->cliente->size      =7;
		$filter->cliente->maxlength =5;

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->rule      ='max_length[40]';
		$filter->nombre->size      =42;
		$filter->nombre->maxlength =40;

		$filter->instrucciones = new textareaField('Instrucciones','instrucciones');
		$filter->instrucciones->rule      ='max_length[8]';
		$filter->instrucciones->cols = 70;
		$filter->instrucciones->rows = 4;

		$filter->estampa = new dateField('Estampa','estampa');
		$filter->estampa->rule      ='chfecha';
		$filter->estampa->size      =10;
		$filter->estampa->maxlength =8;

		$filter->usuario = new inputField('Usuario','usuario');
		$filter->usuario->rule      ='max_length[12]';
		$filter->usuario->size      =14;
		$filter->usuario->maxlength =12;

		$filter->hora = new inputField('Hora','hora');
		$filter->hora->rule      ='max_length[8]';
		$filter->hora->size      =10;
		$filter->hora->maxlength =8;

		$filter->modificado = new inputField('Modificado','modificado');
		$filter->modificado->rule      ='max_length[8]';
		$filter->modificado->size      =10;
		$filter->modificado->maxlength =8;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Fecha',$uri,'fecha','align="left"');
		$grid->column_orderby('Numero','numero','numero','align="left"');
		$grid->column_orderby('Status','status','status','align="left"');
		$grid->column_orderby('Cliente','cliente','cliente','align="left"');
		$grid->column_orderby('Nombre','nombre','nombre','align="left"');
		$grid->column_orderby('Instrucciones','instrucciones','instrucciones','align="left"');
		$grid->column_orderby('Estampa','<dbdate_to_human><#estampa#></dbdate_to_human>','estampa','align="center"');
		$grid->column_orderby('Usuario','usuario','usuario','align="left"');
		$grid->column_orderby('Hora','hora','hora','align="left"');
		$grid->column_orderby('Modificado','modificado','modificado','align="left"');
		$grid->column_orderby('Id','<nformat><#id#></nformat>','id','align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);

	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$do = new DataObject('ordp');
		$do->pointer('scli' ,'scli.cliente=ordp.cliente','scli.nombre AS sclinombre, scli.rifci AS sclirifci'  ,'left');
		$do->pointer('sinv' ,'sinv.codigo=ordp.codigo'  ,'sinv.descrip AS sinvdescrip','left');
		$do->rel_one_to_many('ordpindi' , 'ordpindi' , array('id'=>'id_ordp'));
		$do->rel_one_to_many('ordpitem' , 'ordpitem' , array('id'=>'id_ordp'));
		$do->rel_one_to_many('ordplabor', 'ordplabor', array('id'=>'id_ordp'));

		$edit = new DataDetails($this->tits, $do);
		$edit->back_url = site_url($this->url.'filteredgrid');
		$edit->set_rel_title('ordpindi' ,'Agregar Gasto');
		$edit->set_rel_title('ordpitem' ,'Agregar Art&iacute;culo');
		$edit->set_rel_title('ordplabor','Agregar Labor');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->insertValue=date('Y-m-d');
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;
		$edit->numero->mode='autohide';

		$edit->status = new dropdownField('Status','status');
		$edit->status->option('A','Abierto');
		$edit->status->option('C','Cerrado');
		$edit->status->rule='enum[A,C]';
		$edit->status->style='width:100px';

		$edit->cliente = new inputField('Cliente','cliente');
		$edit->cliente->rule='max_length[5]';
		$edit->cliente->size =7;
		$edit->cliente->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule ='max_length[40]';
		$edit->nombre->type ='inputhidden';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;

		$edit->instrucciones = new textareaField('Instrucciones','instrucciones');
		$edit->instrucciones->style='width:100%';
		$edit->instrucciones->cols = 70;
		$edit->instrucciones->rows = 4;

		$edit->codigo = new inputField('Art&iacute;culo','codigo');
		$edit->codigo->rule ='max_length[15]|existesinv';
		$edit->codigo->size =7;
		$edit->codigo->maxlength =15;

		$edit->cana = new inputField('Cantidad a producir','cana');
		$edit->cana->rule='max_length[10]|numeric|required';
		$edit->cana->css_class='inputnum';
		$edit->cana->size =5;
		$edit->cana->autocomplete=false;
		$edit->cana->maxlength =10;
		$edit->cana->insertValue='1';

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca');
		$edit->desca->db_name='sinvdescrip';
		$edit->desca->maxlength=50;
		$edit->desca->type ='inputhidden';
		$edit->desca->pointer=true;

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->usuario = new autoUpdateField('usuario',$this->secu->usuario(),$this->secu->usuario());
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));

		//**************************************
		//Inicio detalle ordpindi
		//**************************************
		$edit->it1_codigo = new inputField('Codigo','it1codigo_<#i#>');
		$edit->it1_codigo->db_name='codigo';
		$edit->it1_codigo->rule='max_length[6]';
		$edit->it1_codigo->size =8;
		$edit->it1_codigo->maxlength =6;
		$edit->it1_codigo->rel_id = 'ordpindi';

		$edit->it1_descrip = new inputField('Descrip','it1descrip_<#i#>');
		$edit->it1_descrip->db_name='descrip';
		$edit->it1_descrip->rule='max_length[40]';
		$edit->it1_descrip->type ='inputhidden';
		$edit->it1_descrip->size =42;
		$edit->it1_descrip->maxlength =40;
		$edit->it1_descrip->rel_id = 'ordpindi';

		$edit->it1_porcentaje = new inputField('Porcentaje','it1porcentaje_<#i#>');
		$edit->it1_porcentaje->db_name='porcentaje';
		$edit->it1_porcentaje->rule='max_length[14]|numeric';
		$edit->it1_porcentaje->css_class='inputnum';
		$edit->it1_porcentaje->size =5;
		$edit->it1_porcentaje->autocomplete=false;
		$edit->it1_porcentaje->maxlength =14;
		$edit->it1_porcentaje->rel_id = 'ordpindi';

		//**************************************
		//fin detalle ordpindi inicio ordpitem
		//**************************************

		$edit->it2_codigo = new inputField('Codigo','it2codigo_<#i#>');
		$edit->it2_codigo->db_name='codigo';
		$edit->it2_codigo->rule='max_length[15]';
		$edit->it2_codigo->size =17;
		$edit->it2_codigo->maxlength =15;
		$edit->it2_codigo->rel_id = 'ordpitem';

		$edit->it2_descrip = new inputField('Descripci&oacute;n','it2descrip_<#i#>');
		$edit->it2_descrip->db_name='descrip';
		$edit->it2_descrip->rule='max_length[40]';
		$edit->it2_descrip->size =42;
		$edit->it2_descrip->type ='inputhidden';
		$edit->it2_descrip->maxlength =40;
		$edit->it2_descrip->rel_id = 'ordpitem';

		$edit->it2_cantidad = new inputField('Cantidad','it2cantidad_<#i#>');
		$edit->it2_cantidad->db_name='cantidad';
		$edit->it2_cantidad->rule='max_length[14]|numeric';
		$edit->it2_cantidad->css_class='inputnum';
		$edit->it2_cantidad->size =8;
		$edit->it2_cantidad->maxlength =14;
		$edit->it2_cantidad->rel_id = 'ordpitem';

		$edit->it2_merma = new inputField('Merma','it2merma_<#i#>');
		$edit->it2_merma->db_name='merma';
		$edit->it2_merma->rule='max_length[10]|numeric';
		$edit->it2_merma->css_class='inputnum';
		$edit->it2_merma->size =5;
		$edit->it2_merma->maxlength =10;
		$edit->it2_merma->rel_id = 'ordpitem';

		$edit->it2_costo = new inputField('Costo','it2costo_<#i#>');
		$edit->it2_costo->db_name='costo';
		$edit->it2_costo->rule='max_length[17]|numeric';
		$edit->it2_costo->css_class='inputnum';
		$edit->it2_costo->size =10;
		$edit->it2_costo->maxlength =17;
		$edit->it2_costo->rel_id = 'ordpitem';

		//**************************************
		//fin detalle ordpitem inicio ordplabor
		//**************************************

		$edit->it3_secuencia = new inputField('Secuencia','it3secuencia_<#i#>');
		$edit->it3_secuencia->db_name='secuencia';
		$edit->it3_secuencia->rule='max_length[6]|integer';
		$edit->it3_secuencia->css_class='inputonlynum';
		$edit->it3_secuencia->type = 'inputhidden';
		$edit->it3_secuencia->size = 5;
		$edit->it3_secuencia->maxlength =6;
		$edit->it3_secuencia->rel_id = 'ordplabor';

		$edit->it3_estacion = new  dropdownField('Estacion <#o#>', 'it3estacion_<#i#>');
		$edit->it3_estacion->option('','Seleccionar');
		$edit->it3_estacion->options('SELECT estacion,CONCAT(estacion,\'-\',nombre) AS lab FROM esta ORDER BY estacion');
		$edit->it3_estacion->style  = 'width:250px;';
		$edit->it3_estacion->db_name= 'estacion';
		$edit->it3_estacion->rel_id = 'ordplabor';

		$edit->it3_actividad = new inputField('Actividad','it3actividad_<#i#>');
		$edit->it3_actividad->db_name='actividad';
		$edit->it3_actividad->rule='max_length[100]';
		$edit->it3_actividad->size =40;
		$edit->it3_actividad->maxlength =100;
		$edit->it3_actividad->rel_id = 'ordplabor';

		$edit->it3_minutos = new inputField('Minutos','it3minutos_<#i#>');
		$edit->it3_minutos->db_name='minutos';
		$edit->it3_minutos->rule='max_length[6]|integer';
		$edit->it3_minutos->css_class='inputonlynum';
		$edit->it3_minutos->size =8;
		$edit->it3_minutos->maxlength =6;
		$edit->it3_minutos->autocomplete=false;
		$edit->it3_minutos->rel_id = 'ordplabor';

		$edit->it3_segundos = new inputField('Segundos','it3segundos_<#i#>');
		$edit->it3_segundos->db_name='segundos';
		$edit->it3_segundos->rule='max_length[6]|integer';
		$edit->it3_segundos->css_class='inputonlynum';
		$edit->it1_segundos->autocomplete=false;
		$edit->it3_segundos->size =3;
		$edit->it3_segundos->maxlength =6;
		$edit->it3_segundos->rel_id = 'ordplabor';
		//**************************************
		//fin ordppedi
		//**************************************

		$edit->buttons('modify','save','undo','delete','add','back','add_rel');
		$edit->build();

		$script= '<script type="text/javascript" >
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});
		</script>';

		$conten['form']  =& $edit;
		$data['content'] =  $this->load->view('view_ordp', $conten,true);

		$data['style']   = style('redmond/jquery-ui.css');

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('jquery-impromptu.js');
		$data['script'] .= script('plugins/jquery.blockUI.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$data['script'] .= phpscript('nformat.js');
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

	}

	function _pre_insert($do){
		$numero  = $this->datasis->fprox_numero('nordp');
		$do->set('numero',$numero);
		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$primary = implode(',',$do->pk);
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
		if (!$this->db->table_exists('ordp')) {
			$mSQL="CREATE TABLE `ordc` (
				`fecha` DATE NULL DEFAULT NULL,
				`numero` VARCHAR(8) NOT NULL DEFAULT '',
				`status` CHAR(2) NOT NULL DEFAULT '',
				`almacen` VARCHAR(4) NULL DEFAULT NULL,
				`proveed` VARCHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL,
				`montotot` DECIMAL(14,2) NULL DEFAULT NULL,
				`montoiva` DECIMAL(14,2) NULL DEFAULT NULL,
				`montonet` DECIMAL(14,2) NULL DEFAULT NULL,
				`montofac` DECIMAL(14,2) NULL DEFAULT NULL,
				`condi` VARCHAR(30) NULL DEFAULT NULL,
				`codban` CHAR(2) NULL DEFAULT NULL,
				`tipo_op` CHAR(2) NULL DEFAULT NULL,
				`cheque` VARCHAR(12) NULL DEFAULT NULL,
				`comprob` VARCHAR(6) NULL DEFAULT NULL,
				`anticipo` DECIMAL(12,2) NULL DEFAULT NULL,
				`fechafac` DATE NULL DEFAULT '0000-00-00',
				`arribo` DATE NULL DEFAULT NULL,
				`factura` VARCHAR(8) NULL DEFAULT NULL,
				`mdolar` DECIMAL(17,2) NOT NULL DEFAULT '0.00',
				`peso` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
				`benefi` VARCHAR(40) NULL DEFAULT NULL,
				`condi1` VARCHAR(40) NULL DEFAULT NULL,
				`condi2` VARCHAR(40) NULL DEFAULT NULL,
				`condi3` VARCHAR(40) NULL DEFAULT NULL,
				`transac` VARCHAR(8) NOT NULL DEFAULT '',
				`estampa` DATE NOT NULL DEFAULT '0000-00-00',
				`usuario` VARCHAR(12) NOT NULL DEFAULT '',
				`hora` VARCHAR(8) NOT NULL DEFAULT '',
				PRIMARY KEY (`numero`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}
		if (!$this->db->table_exists('ordpindi')) {
			$mSQL="CREATE TABLE `ordpindi` (
				`numero` VARCHAR(8) NULL DEFAULT '',
				`codigo` VARCHAR(6) NULL DEFAULT NULL COMMENT 'Codigo de Gasto',
				`descrip` VARCHAR(40) NULL DEFAULT NULL,
				`porcentaje` DECIMAL(14,3) NULL DEFAULT '0.000',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id_ordp` INT(11) NULL DEFAULT NULL,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`),
				INDEX `numero` (`numero`)
			)
			COMMENT='Costos indirectos Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('ordpitem')) {
			$mSQL="CREATE TABLE `ordpitem` (
				`numero` VARCHAR(8) NULL DEFAULT '',
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`descrip` VARCHAR(40) NULL DEFAULT NULL,
				`cantidad` DECIMAL(14,3) NULL DEFAULT '0.000',
				`merma` DECIMAL(10,2) NULL DEFAULT '0.00',
				`costo` DECIMAL(17,2) NULL DEFAULT '0.00',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id_ordp` INT(11) NULL DEFAULT NULL,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`),
				INDEX `numero` (`numero`)
			)
			COMMENT='Insumos de la Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('ordplabor')) {
			$mSQL="CREATE TABLE `ordplabor` (
				`producto` VARCHAR(15) NULL DEFAULT '' COMMENT 'Producto Terminado',
				`secuencia` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'Secuencia de las actividades',
				`estacion` VARCHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL,
				`actividad` VARCHAR(100) NOT NULL,
				`minutos` INT(6) NULL DEFAULT '0',
				`segundos` INT(6) NULL DEFAULT '0',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id_ordp` INT(11) NULL DEFAULT NULL,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`)
			)
			COMMENT='Acciones de la Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}
	}

}
