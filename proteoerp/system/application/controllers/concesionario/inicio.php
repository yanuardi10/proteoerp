<?php
require_once(BASEPATH.'application/controllers/formams.php');

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
		$mSQL="INSERT IGNORE INTO `sinv` (`codigo`, `descrip`, `unidad`, `tipo`, `comision`, `pond`, `ultimo`, `pvp_s`, `pvp_bs`, `iva`, `margen1`, `margen2`, `margen3`,`activo`) VALUES ('PLACA', 'PLACA', 'UNID.', 'Articulo', 0, 500, 500, 0, 0, 0, 0, 0, 0, 'S')";
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
		$url2.=br().anchor($this->url.'certifi/modify/<#id#>','Certificado');

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

		if($this->secu->puede('210')){
			$grid->add($this->urlext.'compra','Comprar Veh&iacute;culo');
		}
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function certifi(){
		$iid=$this->rapyd->uri->get('modify');
		$id = $iid[1];

		$sel=array(
		'a.nombre','a.casa','a.calle','a.urb','a.ciudad','a.municipio','a.estado','a.cpostal',
		'a.ctelefono1','a.telefono1','a.ctelefono2','a.telefono2','d.nombre AS sclinom','d.nomfis','d.telefono','d.telefon2',
		"CONCAT_WS(' ',d.dire11,d.dire12) AS direc",'b.ciudad');
		$this->db->select($sel);
		$this->db->from('sinvehiculo AS a');
		$this->db->join('sfac AS b','a.id_sfac=b.id');
		$this->db->join('scli AS d','b.cod_cli=d.cliente');
		$this->db->where('a.id' , $id);
		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$row = $query->row();

			$nombre     = (empty($row->nomfis))? $row->sclinom : $row->nomfis;

			//$casa       = $row->casa;
			//$calle      = $row->calle;
			//$urb        = $row->urb;
			//$ciudad     = $row->ciudad;
			//$municipio  = $row->municipio;
			//$estado     = $row->estado;
			//$cpostal    = $row->cpostal;
			//$ctelefono1 = $row->ctelefono1;
			//$telefono1  = $row->telefono1;
			//$ctelefono2 = $row->ctelefono2;
			//$telefono2  = $row->telefono2;

			$data=array();
			if(empty($row->nombre)){
				$data['nombre'] = $nombre;

				$where = 'id = '.$this->db->escape($id);
				$str = $this->db->update_string('sinvehiculo', $data, $where);
				$this->db->simple_query($str);

			}
		}

		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'sinvehiculo');

		$edit->back_url = site_url($this->url.'index');

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[200]|required';
		$edit->nombre->maxlength =200;

		$edit->casa = new inputField('Casa Quinta Edificio Apto','casa');
		$edit->casa->rule='max_length[100]|required';
		//$edit->casa->size =102;
		$edit->casa->maxlength =100;

		$edit->calle = new inputField('Avenida, calle, plaza, esquina','calle');
		$edit->calle->rule='max_length[100]|required';
		//$edit->calle->size =102;
		$edit->calle->maxlength =100;

		$edit->urb = new inputField('Urbanizaci&oacute;n, Bario, Residencia','urb');
		$edit->urb->rule='max_length[100]|required';
		//$edit->urb->size =102;
		$edit->urb->maxlength =100;

		$edit->ciudad = new inputField('Ciudad','ciudad');
		$edit->ciudad->rule='max_length[100]|required';
		//$edit->ciudad->size =102;
		$edit->ciudad->maxlength =100;

		$edit->municipio = new inputField('Parroquia, distrito, Municipio','municipio');
		$edit->municipio->rule='max_length[100]|required';
		//$edit->municipio->size =102;
		$edit->municipio->maxlength =100;

		$edit->estado = new inputField('Estado','estado');
		$edit->estado->rule='max_length[100]|required';
		//$edit->estado->size =102;
		$edit->estado->maxlength =100;

		$edit->cpostal = new inputField('C&oacute;digo Postal','cpostal');
		$edit->cpostal->rule='max_length[10]|numeric|required';
		$edit->cpostal->size =10;
		$edit->cpostal->maxlength =10;

		$edit->ctelefono1 = new inputField('Tel&eacute;fono de Habitaci&oacute;n','ctelefono1');
		$edit->ctelefono1->rule='max_length[100]|numeric|required';
		$edit->ctelefono1->size =6;
		$edit->ctelefono1->maxlength =100;

		$edit->telefono1 = new inputField('','telefono1');
		$edit->telefono1->rule='max_length[100]|numeric|required';
		$edit->telefono1->size =10;
		$edit->telefono1->maxlength =10;
		$edit->telefono1->in='ctelefono1';

		$edit->ctelefono2 = new inputField('Tel&eacute;fono de Trabajo','ctelefono2');
		$edit->ctelefono2->rule='max_length[100]|numeric|required';
		$edit->ctelefono2->size =6;
		$edit->ctelefono2->maxlength =4;

		$edit->telefono2 = new inputField('','telefono2');
		$edit->telefono2->rule='max_length[100]|numeric|required';
		$edit->telefono2->size =10;
		$edit->telefono2->maxlength =10;
		$edit->telefono2->in='ctelefono2';

		$edit->distrito = new inputField('Distrito','distrito');
		$edit->distrito->rule='max_length[100]';

		$edit->aseguradora = new inputField('Nombre de la aseguradora','aseguradora');
		$edit->aseguradora->rule = 'max_length[100]';
		$edit->aseguradora->group = 'Datos del seguro';

		$edit->poliza = new inputField('Poliza','poliza');
		$edit->poliza->rule = 'max_length[100]';
		$edit->poliza->group = 'Datos del seguro';

		$edit->vence = new dateonlyField('Vencimiento de la p&oacute;liza','vence');
		$edit->vence->rule = 'chfecha';
		$edit->vence->group = 'Datos del seguro';

		$edit->nomban = new inputField('Nombre  del banco','nomban');
		$edit->nomban->rule = 'max_length[100]';
		$edit->nomban->group='Reserva de dominio';

		$edit->banrif = new inputField('Rif del banco','banrif');
		$edit->banrif->rule = 'max_length[10]';
		$edit->banrif->group='Reserva de dominio';

		$edit->representante = new inputField('Representaci&oacute;n','representante');
		$edit->representante->rule = 'max_length[100]';

		$edit->concesionario = new inputField('Concesionario B','concesionario');
		$edit->concesionario->rule = 'max_length[100]';

		$edit->concesionariorif = new inputField('Concesionario B Rif','concesionariorif');
		$edit->concesionariorif->rule = 'max_length[10]';

		$accion="javascript:window.location='".site_url($this->url.'certificado'.$edit->pk_URI())."'";
		$edit->button_status('btn_imprime','Certificado',$accion,'BR','show');

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		//$edit->submit = new submitField("login","btn_submit");
		$edit->build();

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function certificado($id){

		$sel=array('b.numero','b.fecha','a.distrito','a.aseguradora','a.vence','a.nomban','a.banrif','a.representante','a.concesionario','a.concesionariorif',
		'a.nombre','a.casa','a.calle','a.urb','a.ciudad','a.municipio','a.estado','a.cpostal','a.poliza',
		'a.ctelefono1','a.telefono1','a.ctelefono2','a.telefono2','d.nombre AS sclinom','d.nomfis','d.telefono','d.telefon2',
		'b.ciudad','b.rifci');
		$this->db->select($sel);
		$this->db->from('sinvehiculo AS a');
		$this->db->join('sfac AS b','a.id_sfac=b.id');
		$this->db->join('scli AS d','b.cod_cli=d.cliente');
		$this->db->where('a.id' , $id);
		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$row = $query->row();

			$data['rifci']            = $row->rifci;
			$data['factura']          = $row->numero;
			$data['ffactura']         = dbdate_to_human($row->fecha);
			$data['nombre']           = $row->nombre;
			$data['casa']             = $row->casa;
			$data['calle']            = $row->calle;
			$data['ciudad']           = $row->ciudad;
			$data['urb']              = $row->urb;
			$data['ciudad']           = $row->ciudad;
			$data['municipio']        = $row->municipio;
			$data['estado']           = $row->estado;
			$data['cpostal']          = $row->cpostal;
			$data['ctelefono1']       = $row->ctelefono1;
			$data['telefono1']        = $row->telefono1;
			$data['ctelefono2']       = $row->ctelefono2;
			$data['telefono2']        = $row->telefono2;
			$data['titulo1']          = $this->datasis->traevalor('TITULO1');
			$data['distrito']         = $row->distrito;
			$data['aseguradora']      = $row->aseguradora;
			$data['vence']            = (!empty($row->vence))? dbdate_to_human($row->vence) : '';
			$data['nomban']           = $row->nomban;
			$data['banrif']           = $row->banrif;
			$data['representante']    = $row->representante;
			$data['concesionario']    = $row->concesionario;
			$data['concesionariorif'] = $row->concesionariorif;
			$data['poliza']           = $row->poliza;

			formams::_msxml('certificado',$data);
		}
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
				`nombre` VARCHAR(200) NULL DEFAULT NULL,
				`casa` VARCHAR(100) NULL DEFAULT NULL,
				`calle` VARCHAR(100) NULL DEFAULT NULL,
				`urb` VARCHAR(100) NULL DEFAULT NULL,
				`ciudad` VARCHAR(100) NULL DEFAULT NULL,
				`municipio` VARCHAR(100) NULL DEFAULT NULL,
				`estado` VARCHAR(100) NULL DEFAULT NULL,
				`cpostal` VARCHAR(10) NULL DEFAULT NULL,
				`ctelefono1` VARCHAR(4) NULL DEFAULT NULL,
				`telefono1` VARCHAR(8) NULL DEFAULT NULL,
				`ctelefono2` VARCHAR(4) NULL DEFAULT NULL,
				`telefono2` VARCHAR(8) NULL DEFAULT NULL,
				`distrito` VARCHAR(100) NULL DEFAULT NULL,
				`aseguradora` VARCHAR(200) NULL DEFAULT NULL,
				`vence` DATE NULL DEFAULT NULL,
				`nomban` VARCHAR(200) NULL DEFAULT NULL,
				`banrif` VARCHAR(20) NULL DEFAULT NULL,
				`representante` VARCHAR(100) NULL DEFAULT NULL,
				`concesionario` VARCHAR(100) NULL DEFAULT NULL,
				`concesionariorif` VARCHAR(20) NULL DEFAULT NULL,
				`poliza` VARCHAR(50) NULL DEFAULT NULL,
				`neumaticos` INT NULL DEFAULT NULL,
				`tipo_neumatico` VARCHAR(50) NULL DEFAULT NULL,
				`distanciaeje` FLOAT NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COMMENT='Vehiculos a la venta'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('neumaticos', 'sinvehiculo')){
			$mSQL = "ALTER TABLE `sinvehiculo`
			ADD COLUMN `neumaticos` INT NULL DEFAULT NULL AFTER `poliza`,
			ADD COLUMN `tipo_neumatico` VARCHAR(50) NULL DEFAULT NULL AFTER `neumaticos`,
			ADD COLUMN `distanciaeje` FLOAT NULL DEFAULT NULL AFTER `marca`;";
			$this->db->simple_query($mSQL);
		}

	}
}
