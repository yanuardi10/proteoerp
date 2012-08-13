<?php
class inicio extends Controller {

	var $titp  = 'Compra y Ventas de Veh&iacute;culos';
	var $tits  = 'Compra y Ventas de Veh&iacute;culos';
	var $url   = 'concesionario/inicio/';
	var $urlext= 'concesionario/';

	function inicio(){
		parent::Controller();
		$this->back_dataedit='compras/scst/datafilter';
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(216,1);
		$this->instalar();
		$mSQL="INSERT IGNORE INTO `sinv` (`codigo`, `descrip`, `unidad`, `tipo`, `comision`, `pond`, `ultimo`, `pvp_s`, `pvp_bs`, `iva`, `margen1`, `margen2`, `margen3`) VALUES ('PLACA', 'PLACA', 'UNID.', 'Servicio', 0, 500, 500, 0, 0, 0, 0, 0, 0)";
		$this->db->simple_query($mSQL);
	}

	function index(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp);

		$sel=array('a.id','a.id_sfac','a.id_scst','a.codigo_sinv','a.modelo','a.color','a.motor','a.carroceria'
			,'a.uso','a.anio','a.peso','a.transmision','a.placa','a.precioplaca','a.tasa','b.fecha AS venta','c.fecha AS compra');
		$filter->db->select($sel);
		$filter->db->from('sinvehiculo AS a');
		$filter->db->join('scst AS c','a.id_scst=c.id');
		$filter->db->join('sfac AS b','a.id_sfac=b.id','left');

		$filter->modelo = new inputField('Modelo','modelo');
		$filter->modelo->rule      ='max_length[50]';
		$filter->modelo->size      =52;
		$filter->modelo->maxlength =50;

		$filter->motor = new inputField('Serial de Motor','motor');
		$filter->motor->rule      ='max_length[50]';
		$filter->motor->size      =52;
		$filter->motor->maxlength =50;

		$filter->carroceria = new inputField('Serial de Carrocer&iacute;a','carroceria');
		$filter->carroceria->rule      ='max_length[50]';
		$filter->carroceria->size      =52;
		$filter->carroceria->maxlength =50;

		$filter->uso = new  dropdownField('Tipo de uso','vh_uso');
		$filter->uso->option('P','Particular');
		$filter->uso->option('T','Trabajo');
		$filter->uso->style='width:200px;';
		$filter->uso->size = 6;

		$filter->anio = new inputField('Anio','anio');
		$filter->anio->rule      ='max_length[50]';
		$filter->anio->size      =6;
		$filter->anio->maxlength =4;

		$filter->buttons('reset', 'search');
		$filter->build();

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$url =anchor($this->urlext.'venta/index/<#id#>','Vender');
		$url2=anchor($this->urlext.'venta/dataprint/modify/<#id_sfac#>','<dbdate_to_human><#venta#></dbdate_to_human>');

		$grid->column_orderby('Compra','<dbdate_to_human><#compra#></dbdate_to_human>','compra','align="right"');
		$grid->column_orderby('Venta' ,"<siinulo><#id_sfac#>|$url|$url2</siinulo>",'id_sfac','align="center"');
		$grid->column_orderby('C&oacute;digo','codigo_sinv','codigo_sinv','align="left"');
		$grid->column_orderby('Modelo','modelo','modelo','align="left"');
		$grid->column_orderby('Color','color','color','align="left"');
		$grid->column_orderby('Motor','motor','motor','align="left"');
		$grid->column_orderby('Carroceria' ,'carroceria','carroceria','align="left"');
		$grid->column_orderby('Uso','uso'  ,'uso','align="left"');
		$grid->column_orderby('A&ntilde;o' ,'anio','anio','align="center"');
		$grid->column_orderby('Peso','<nformat><#peso#></nformat>','peso','align="right"');

		$grid->add($this->urlext.'compra','Comprar Veh&iacute;culo');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		if (!$this->db->table_exists('sinvehiculo')) {
			$mSQL="CREATE TABLE `sinvehiculo` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`id_sfac` INT(10) NULL DEFAULT NULL,
				`id_scst` INT(10) NULL DEFAULT NULL,
				`codigo_sinv` VARCHAR(15) NULL DEFAULT '0',
				`modelo` VARCHAR(50) NULL DEFAULT '0',
				`color` VARCHAR(50) NULL DEFAULT '0',
				`motor` VARCHAR(50) NULL DEFAULT '0',
				`carroceria` VARCHAR(50) NULL DEFAULT '0',
				`uso` VARCHAR(50) NULL DEFAULT '0',
				`tipo` VARCHAR(50) NULL DEFAULT '0',
				`clase` VARCHAR(50) NULL DEFAULT '0',
				`anio` VARCHAR(50) NULL DEFAULT '0',
				`peso` DECIMAL(10,2) NULL DEFAULT '0.00',
				`transmision` VARCHAR(50) NULL DEFAULT '0.00',
				`placa` VARCHAR(10) NULL DEFAULT NULL,
				`precioplaca` DECIMAL(10,2) NULL DEFAULT NULL,
				`tasa` DECIMAL(10,2) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COMMENT='Vehiculos a la venta'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}
	}
}
