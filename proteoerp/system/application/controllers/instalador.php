<?php
class Instalador extends Controller {
	function Instalador(){
		parent::Controller();
	}
	function index(){


		//./compras/sprv.php 

		$campos=$this->db->list_fields('sprv');
		if (!in_array('id',$campos)){
			$this->db->simple_query('ALTER TABLE `sprv` DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE `sprv` ADD id INT AUTO_INCREMENT PRIMARY KEY');
		}

		if (!in_array('copre'   ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD copre VARCHAR(11) DEFAULT NULL NULL AFTER cuenta');
		if (!in_array('ocompra' ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD ocompra CHAR(1) DEFAULT NULL NULL AFTER copre');
		if (!in_array('dcredito',$campos)) $this->db->simple_query('ALTER TABLE sprv ADD dcredito DECIMAL(3,0) DEFAULT "0" NULL AFTER ocompra');
		if (!in_array('despacho',$campos)) $this->db->simple_query('ALTER TABLE sprv ADD despacho DECIMAL(3,0) DEFAULT NULL NULL AFTER dcredito');
		if (!in_array('visita'  ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD visita VARCHAR(9) DEFAULT NULL NULL AFTER despacho');
		if (!in_array('cate'    ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD cate VARCHAR(20) NULL AFTER visita');
		if (!in_array('reteiva' ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD reteiva DECIMAL(7,2) DEFAULT "0.00" NULL AFTER cate');
		if (!in_array('ncorto'  ,$campos)) $this->db->simple_query('ALTER TABLE sprv ADD ncorto VARCHAR(20) DEFAULT NULL NULL AFTER nombre');

		$this->db->simple_query('ALTER TABLE sprv CHANGE direc1 direc1 VARCHAR(105) DEFAULT NULL NULL');
		$this->db->simple_query('ALTER TABLE sprv CHANGE direc2 direc2 VARCHAR(105) DEFAULT NULL NULL');
		$this->db->simple_query('ALTER TABLE sprv CHANGE direc3 direc3 VARCHAR(105) DEFAULT NULL NULL');
		$this->db->simple_query('ALTER TABLE sprv CHANGE nombre nombre VARCHAR(60) DEFAULT NULL NULL');
		$this->db->simple_query('ALTER TABLE sprv CHANGE nomfis nomfis VARCHAR(200) DEFAULT NULL NULL');


		//./compras/scst.php 
		if (!$this->db->table_exists('sinvehiculo')) {
			$mSQL="CREATE TABLE `sinvehiculo` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`id_sfac` INT(10) NULL DEFAULT NULL,
				`id_scst` INT(10) NULL DEFAULT NULL,
				`codigo_sinv` VARCHAR(15) NULL DEFAULT NULL,
				`modelo` VARCHAR(50) NULL DEFAULT NULL,
				`color` VARCHAR(50) NULL DEFAULT NULL,
				`motor` VARCHAR(50) NULL DEFAULT NULL,
				`carroceria` VARCHAR(50) NULL DEFAULT NULL,
				`uso` VARCHAR(50) NULL DEFAULT NULL,
				`tipo` VARCHAR(50) NULL DEFAULT NULL,
				`clase` VARCHAR(50) NULL DEFAULT NULL,
				`anio` VARCHAR(50) NULL DEFAULT NULL,
				`peso` DECIMAL(10,2) NULL DEFAULT '0.00',
				`transmision` VARCHAR(50) NULL DEFAULT NULL,
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
				`neumaticos` INT(11) NULL DEFAULT NULL,
				`tipo_neumatico` VARCHAR(50) NULL DEFAULT NULL,
				`distanciaeje` FLOAT NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COMMENT='Vehiculos a la venta'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if ( !$this->datasis->iscampo('scst','id') ) {
			$this->db->simple_query('ALTER TABLE scst DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE scst ADD UNIQUE INDEX control (control)');
			$this->db->simple_query('ALTER TABLE scst ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');

			$this->db->simple_query("update tmenus set secu=1 where titulo='Incluye'");
			$this->db->simple_query("update tmenus set secu=2 where titulo='Modifica'");
			$this->db->simple_query("update tmenus set secu=3 where titulo='Prox'");
			$this->db->simple_query("update tmenus set secu=4 where titulo='Ante'");
			$this->db->simple_query("update tmenus set secu=5 where titulo='Elimina'");
			$this->db->simple_query("update tmenus set secu=6 where titulo='Busca'");
			$this->db->simple_query("update tmenus set secu=7 where titulo='Tabla'");
			$this->db->simple_query("update tmenus set secu=8 where titulo='Lista'");
			$this->db->simple_query("update tmenus set secu=9 where titulo='Otros'");
		};



		//./compras/sprvcol.php 

		$mSQL='ALTER TABLE `sprv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
		//$mSQL='ALTER TABLE `sprv` ADD UNIQUE `id` (`id`)';
		//$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD PRIMARY KEY `id` (`id`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `copre` VARCHAR(11) DEFAULT NULL NULL AFTER `cuenta` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `ocompra` CHAR(1) DEFAULT NULL NULL AFTER `copre` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `dcredito` DECIMAL(3,0) DEFAULT "0" NULL AFTER `ocompra` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `despacho` DECIMAL(3,0) DEFAULT NULL NULL AFTER `dcredito` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `visita` VARCHAR(9) DEFAULT NULL NULL AFTER `despacho` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `cate` VARCHAR(20) NULL AFTER `visita` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `reteiva` DECIMAL(7,2) DEFAULT "0.00" NULL AFTER `cate` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `ncorto` VARCHAR(20) DEFAULT NULL NULL AFTER `nombre` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `direc1` `direc1` VARCHAR(105) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `direc2` `direc2` VARCHAR(105) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `direc3` `direc3` VARCHAR(105) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `nombre` `nombre` VARCHAR(60) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `nomfis` `nomfis` VARCHAR(200) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);

		if (!$this->db->field_exists('nombre1','sprv')) {
			$mSQL="ALTER TABLE `sprv`  ADD COLUMN `nombre1` VARCHAR(100) NULL AFTER `id`, ADD COLUMN `nombre2` VARCHAR(100) NULL AFTER `nombre1`, ADD COLUMN `apellido1` VARCHAR(100) NULL AFTER `nombre2`,  ADD COLUMN `apellido2` VARCHAR(100) NULL AFTER `apellido1`";
			var_dump($this->db->simple_query($mSQL));
		} 


		//./compras/agregaroc.php 
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./compras/add.php 
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./import/ordi.php 
		if(!$this->db->field_exists('ordeni', 'gser')){
			$mSQL='ALTER TABLE `gser`  ADD COLUMN `ordeni` INT(15) UNSIGNED NULL DEFAULT NULL AFTER `compra`';
			var_dump($this->db->simple_query($mSQL));
		}

		if (!$this->db->table_exists('itordi')) {
			$mSQL="CREATE TABLE `itordi` (
				`numero` INT(15) UNSIGNED NOT NULL,
				`fecha` DATE NULL DEFAULT NULL,
				`codigo` CHAR(15) NULL DEFAULT NULL,
				`descrip` CHAR(45) NULL DEFAULT NULL,
				`cantidad` DECIMAL(10,3) NULL DEFAULT NULL,
				`costofob` DECIMAL(17,2) NULL DEFAULT NULL,
				`importefob` DECIMAL(17,2) NULL DEFAULT NULL,
				`gastosi` DECIMAL(17,2) NULL DEFAULT NULL,
				`costocif` DECIMAL(17,2) NULL DEFAULT NULL,
				`importecif` DECIMAL(19,4) NULL DEFAULT NULL,
				`importeciflocal` DECIMAL(17,2) NULL DEFAULT NULL COMMENT 'importe cif en moneda local',
				`codaran` CHAR(15) NULL DEFAULT NULL,
				`arancel` DECIMAL(7,2) NULL DEFAULT NULL,
				`montoaran` DECIMAL(17,2) NULL DEFAULT NULL,
				`gastosn` DECIMAL(17,2) NULL DEFAULT NULL,
				`costofinal` DECIMAL(17,2) NULL DEFAULT NULL,
				`importefinal` DECIMAL(17,2) NULL DEFAULT NULL,
				`participam` DOUBLE NULL DEFAULT NULL,
				`participao` DOUBLE NULL DEFAULT NULL,
				`arancif` DECIMAL(17,4) NULL DEFAULT '0.0000' COMMENT 'Monto del valor en base al cual se calcula el motoaran',
				`iva` DECIMAL(17,2) NULL DEFAULT NULL,
				`precio1` DECIMAL(15,2) NULL DEFAULT NULL,
				`precio2` DECIMAL(15,2) NULL DEFAULT NULL,
				`precio3` DECIMAL(15,2) NULL DEFAULT NULL,
				`precio4` DECIMAL(15,2) NULL DEFAULT NULL,
				`estampa` DATE NULL DEFAULT NULL,
				`hora` CHAR(8) NULL DEFAULT NULL,
				`usuario` CHAR(12) NULL DEFAULT NULL,
				`id` INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `numero` (`numero`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=FIXED
			AUTO_INCREMENT=0";
			var_dump($this->db->simple_query($mSQL));
		}

		if (!$this->db->table_exists('ordi')) {
			$mSQL="CREATE TABLE `ordi` (
				`numero` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
				`fecha` DATE NULL DEFAULT NULL,
				`status` CHAR(1) NOT NULL DEFAULT '' COMMENT 'Estatus de la Compra Abierto, Eliminado y Cerrado',
				`proveed` VARCHAR(5) NULL DEFAULT NULL COMMENT 'Proveedor',
				`nombre` VARCHAR(40) NULL DEFAULT NULL COMMENT 'Nombre del Proveedor',
				`agente` CHAR(5) NULL DEFAULT NULL COMMENT 'Agente Aduanal (Proveedor)',
				`nomage` VARCHAR(40) NULL DEFAULT NULL COMMENT 'Agente Aduanal (Proveedor)',
				`montofob` DECIMAL(19,2) NULL DEFAULT NULL COMMENT 'Total de la Factura extranjera',
				`gastosi` DECIMAL(19,2) NULL DEFAULT NULL COMMENT 'Gastos Internacionales (Fletes, Seguros, etc)',
				`montocif` DECIMAL(19,2) NULL DEFAULT NULL COMMENT 'Monto FOB+gastos Internacionales',
				`aranceles` DECIMAL(19,2) NULL DEFAULT NULL COMMENT 'Suma del Impuesto Arancelario',
				`gastosn` DECIMAL(19,2) NULL DEFAULT NULL COMMENT 'Gastos Nacionales',
				`montotot` DECIMAL(19,2) NULL DEFAULT NULL COMMENT 'Monto CIF + Gastos Nacionales',
				`montoiva` DECIMAL(19,2) NULL DEFAULT NULL COMMENT 'Monto del IVA pagado',
				`montoexc` DECIMAL(12,2) NULL DEFAULT NULL,
				`arribo` DATE NULL DEFAULT NULL COMMENT 'Fecha de Llegada',
				`factura` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Nro de Factura',
				`cambioofi` DECIMAL(17,2) NOT NULL DEFAULT '0.00' COMMENT 'Cambio Fiscal US$ X Bs.',
				`cambioreal` DECIMAL(17,2) NOT NULL DEFAULT '0.00' COMMENT 'Cambio Efectivamente Aplicado',
				`peso` DECIMAL(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Peso total',
				`condicion` TEXT NULL,
				`transac` VARCHAR(8) NOT NULL DEFAULT '',
				`estampa` DATE NOT NULL DEFAULT '0000-00-00',
				`usuario` VARCHAR(12) NOT NULL DEFAULT '',
				`hora` VARCHAR(8) NOT NULL DEFAULT '',
				`dua` CHAR(30) NULL DEFAULT NULL COMMENT 'DECLARACION UNICA ADUANAS',
				`cargoval` DECIMAL(19,2) NULL DEFAULT NULL COMMENT 'Diferencia Cambiara $ oficial y aplicado',
				`control` VARCHAR(8) NULL DEFAULT NULL COMMENT 'Apuntador a la factura con la que se relaciono',
				`crm` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'Apuntador al conetendor',
				`estimadif` DECIMAL(10,2) NULL DEFAULT '0' COMMENT 'Diferencia en la estimacion',
				PRIMARY KEY (`numero`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DYNAMIC
			AUTO_INCREMENT=0";
			var_dump($this->db->simple_query($mSQL));
		}

		if (!$this->db->table_exists('ordiva')) {
			$mSQL="CREATE TABLE `ordiva` (
				`id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
				`ordeni` INT(15) UNSIGNED NULL DEFAULT NULL,
				`tasa` DECIMAL(7,2) NULL DEFAULT NULL,
				`base` DECIMAL(10,2) NULL DEFAULT NULL,
				`montoiva` DECIMAL(10,2) NULL DEFAULT NULL,
				`concepto` VARCHAR(100) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `ordi` (`ordeni`, `tasa`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT
			AUTO_INCREMENT=0";
			var_dump($this->db->simple_query($mSQL));
		}

		if (!$this->db->table_exists('gseri')) {
			$mSQL="CREATE TABLE `gseri` (
			`ordeni` INT(15) UNSIGNED NOT NULL,
			`fecha` DATE NOT NULL DEFAULT '0000-00-00',
			`numero` VARCHAR(8) NOT NULL DEFAULT '',
			`concepto` VARCHAR(40) NULL DEFAULT NULL,
			`monto` DECIMAL(19,2) NULL DEFAULT NULL,
				`proveed` VARCHAR(5) NULL DEFAULT '',
				`nombre` VARCHAR(30) NULL DEFAULT '',
				`usuario` VARCHAR(12) NULL DEFAULT NULL,
				`estampa` DATE NULL DEFAULT NULL,
				`hora` VARCHAR(8) NULL DEFAULT NULL,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DYNAMIC
			AUTO_INCREMENT=0";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->db->field_exists('importecifreal', 'itordi')){
			$mSQL="ALTER TABLE `itordi`  ADD COLUMN `importecifreal` DECIMAL(17,2) NULL DEFAULT NULL COMMENT 'importe cif en moneda local al cambio real' AFTER `importeciflocal`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->db->field_exists('costoreal', 'itordi')){
			$mSQL="ALTER TABLE `itordi`  ADD COLUMN `costoreal` DECIMAL(17,2) NULL DEFAULT NULL COMMENT 'costo unitario al dolar real' AFTER `importefinal`,  ADD COLUMN `importereal` DECIMAL(17,2) NULL DEFAULT NULL COMMENT 'importe al dolar real' AFTER `costoreal`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->db->field_exists('estimadif', 'ordi')){
			$mSQL="ALTER TABLE `ordi`ADD COLUMN `estimadif` DECIMAL(10,2) NULL DEFAULT '0' COMMENT 'Diferencia en la estimacion' AFTER `crm`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->db->table_exists('ordiestima')){
			$mSQL="CREATE TABLE `ordiestima` (
				`id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
				`ordeni` INT(15) UNSIGNED NULL DEFAULT NULL,
				`monto` DECIMAL(10,2) NULL DEFAULT NULL,
				`concepto` VARCHAR(100) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX `ordi` (`ordeni`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			var_dump($this->db->simple_query($mSQL));
		}

		$this->prefijo='crm_';
		contenedor::instalar();


		//./import/aran.php 
		$mSQL="CREATE TABLE `aran` (
		 `codigo` varchar(15) NOT NULL DEFAULT '',
		 `descrip` text,
		 `tarifa` decimal(8,2) DEFAULT '0.00',
		 `unidad` varchar(20) DEFAULT NULL,
		 PRIMARY KEY (`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
		$this->db->simple_query($mSQL);

		$mSQL="ALTER TABLE `aran`  ADD COLUMN `dolar` DECIMAL(8,2) NULL AFTER `unidad`";
		$this->db->simple_query($mSQL);


		//./sincro/extimpor.php 
		$mSQL="CREATE TABLE IF NOT EXIST `impor_data` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`id_tabla` int(10) DEFAULT '0',
			`fila` int(10) DEFAULT NULL,
			`columna` int(10) DEFAULT NULL,
			`valor` varchar(200) DEFAULT NULL,
			`tipo` varchar(20) DEFAULT NULL,
			`destino` varchar(100) DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `id_tabla` (`id_tabla`,`fila`,`columna`)
		) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COMMENT='Contenido de las tablas a importar'";

		var_dump($this->db->simple_query($mSQL));


		//./sincro/b2b.php 
		if (!$this->db->table_exists('b2b_config')) {
			$mSQL="CREATE TABLE `b2b_config` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`proveed` CHAR(5) NOT NULL COMMENT 'Codigo del proveedor',
				`prefijo` VARCHAR(50) NOT NULL COMMENT 'Prefijo para los codigos de productos',
				`url` VARCHAR(100) NOT NULL,
				`puerto` INT(5) NOT NULL DEFAULT '80',
				`proteo` VARCHAR(20) NOT NULL DEFAULT 'proteoerp',
				`usuario` VARCHAR(100) NOT NULL COMMENT 'Codigo de cliente en el proveedor',
				`clave` VARCHAR(100) NOT NULL,
				`tipo` CHAR(1) NULL DEFAULT NULL COMMENT 'I para inventario G para gasto',
				`depo` VARCHAR(4) NULL DEFAULT NULL COMMENT 'Almacen',
				`margen1` DECIMAL(6,2) NULL DEFAULT NULL COMMENT 'Margen para el precio1',
				`margen2` DECIMAL(6,2) NULL DEFAULT NULL COMMENT 'Margen para el precio 2',
				`margen3` DECIMAL(6,2) NULL DEFAULT NULL COMMENT 'Margen para el precio3',
				`margen4` DECIMAL(6,2) NULL DEFAULT NULL COMMENT 'Margen para el precio4',
				`margen5` DECIMAL(6,2) NULL DEFAULT NULL COMMENT 'Margen para el precio5 (solo supermercado)',
				`grupo` VARCHAR(5) NULL DEFAULT NULL COMMENT 'Grupo por defecto',
				PRIMARY KEY (`id`)
			)
			COMMENT='Configuracion para los b2b'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1;";
			var_dump($this->db->simple_query($mSQL));
		}

		if (!$this->db->table_exists('b2b_scst')) {
			$mSQL="CREATE TABLE IF NOT EXISTS `b2b_scst` (
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
		}

		if (!$this->db->table_exists('b2b_itscst')) {
			$mSQL="CREATE TABLE IF NOT EXISTS `b2b_itscst` (
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
		}

		if(!$this->datasis->iscampo('scon','origen')){
			$mSQL="ALTER TABLE scon ADD COLUMN origen CHAR(1) NOT NULL DEFAULT 'L' COMMENT 'L= Local, R=Remoto' AFTER peso;";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','reteiva')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN `reteiva` DECIMAL(17,2) NULL DEFAULT '0.00' AFTER `reducida`;";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','cexento')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN  `cexento` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','cgenera')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `cgenera` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','civagen')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `civagen` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','creduci')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `creduci` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','civared')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `civared` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','cadicio')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `cadicio` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','civaadi')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `civaadi` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','cstotal')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `cstotal` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','ctotal')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `ctotal` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('b2b_scst','cimpuesto')){
			$mSQL="ALTER TABLE `b2b_scst`  ADD COLUMN   `cimpuesto` decimal(17,2) DEFAULT NULL AFTER `reducida`";
			var_dump($this->db->simple_query($mSQL));
		}

		if (!$this->db->table_exists('b2b_scon')) {
			$mSQL="CREATE TABLE IF NOT EXISTS `b2b_scon` (
				`numero` CHAR(8) NULL DEFAULT NULL,
				`fecha` DATE NULL DEFAULT NULL,
				`tipo` CHAR(1) NULL DEFAULT NULL,
				`tipod` CHAR(1) NULL DEFAULT NULL,
				`status` CHAR(1) NULL DEFAULT 'T',
				`asociado` CHAR(8) NULL DEFAULT NULL,
				`clipro` CHAR(5) NULL DEFAULT NULL,
				`almacen` CHAR(4) NULL DEFAULT NULL,
				`nombre` CHAR(40) NULL DEFAULT NULL,
				`direc1` CHAR(40) NULL DEFAULT NULL,
				`direc2` CHAR(40) NULL DEFAULT NULL,
				`observ1` CHAR(33) NULL DEFAULT NULL,
				`observ2` CHAR(33) NULL DEFAULT NULL,
				`stotal` DECIMAL(12,2) NULL DEFAULT NULL,
				`impuesto` DECIMAL(12,2) NULL DEFAULT NULL,
				`gtotal` DECIMAL(12,2) NULL DEFAULT NULL,
				`peso` DECIMAL(10,3) NULL DEFAULT NULL,
				`pid` INT(15) NULL DEFAULT NULL,
				`id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `numero` (`numero`, `tipo`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			var_dump($this->db->simple_query($mSQL));
		}

		if (!$this->db->table_exists('b2b_itscon')) {
			$mSQL="CREATE TABLE IF NOT EXISTS `b2b_itscon` (
				`numero` CHAR(8) NULL DEFAULT NULL,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`codigolocal` VARCHAR(15) NULL DEFAULT NULL,
				`desca` VARCHAR(40) NULL DEFAULT NULL,
				`cana` DECIMAL(5,0) NULL DEFAULT NULL,
				`recibido` DECIMAL(5,0) NULL DEFAULT NULL,
				`precio` DECIMAL(12,2) NULL DEFAULT NULL,
				`importe` DECIMAL(12,2) NULL DEFAULT NULL,
				`iva` DECIMAL(8,2) NULL DEFAULT NULL,
				`id_scon` INT(15) UNSIGNED NOT NULL,
				`id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `id_scon` (`id_scon`),
				INDEX `numero_codigo` (`numero`, `codigo`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			var_dump($this->db->simple_query($mSQL));
		}


		//./sincro/b2bsan.php 
		$mSQL="CREATE TABLE `b2b_config` (  `id` int(10) NOT NULL,  `proveed` char(5) COLLATE latin1_general_ci NOT NULL COMMENT 'Codigo del proveedor',  `url` varchar(100) COLLATE latin1_general_ci NOT NULL,  `usuario` varchar(100) COLLATE latin1_general_ci NOT NULL COMMENT 'Codigo de cliente en el proveedor',  `clave` varchar(100) COLLATE latin1_general_ci NOT NULL,  `tipo` char(1) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'I para inventario G para gasto',  `depo` varchar(4) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Almacen',  `margen1` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio1',  `margen2` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio 2',  `margen3` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio3',  `margen4` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio4',
		 `margen5` decimal(6,2) DEFAULT NULL COMMENT 'Margen para el precio5 (solo supermercado)',
		 `grupo` varchar(5) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Grupo por defecto',
		 PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Configuracion para los b2b'";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="CREATE TABLE `b2b_itscst` (  `id_scst` int(11) DEFAULT NULL,  `fecha` date DEFAULT NULL,  `numero` varchar(8) DEFAULT NULL,  `proveed` varchar(5) DEFAULT NULL,  `depo` varchar(4) DEFAULT NULL,  `codigo` varchar(15) DEFAULT NULL,  `descrip` varchar(45) DEFAULT NULL,  `cantidad` decimal(10,3) DEFAULT NULL,  `devcant` decimal(10,3) DEFAULT NULL,  `devfrac` int(4) DEFAULT NULL,  `costo` decimal(17,2) DEFAULT NULL,  `importe` decimal(17,2) DEFAULT NULL,  `iva` decimal(5,2) DEFAULT NULL,  `montoiva` decimal(17,2) DEFAULT NULL,  `garantia` int(3) DEFAULT NULL,  `ultimo` decimal(17,2) DEFAULT NULL,  `precio1` decimal(15,2) DEFAULT NULL,  `precio2` decimal(15,2) DEFAULT NULL,  `precio3` decimal(15,2) DEFAULT NULL,  `precio4` decimal(15,2) DEFAULT NULL,  `estampa` date DEFAULT NULL,  `hora` varchar(8) DEFAULT NULL,  `usuario` varchar(12) DEFAULT NULL,  `licor` decimal(10,2) DEFAULT '0.00',  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`id`),
		  KEY `id_scst` (`id_scst`),
		  KEY `fecha` (`fecha`),
		  KEY `codigo` (`codigo`),
		  KEY `proveedor` (`proveed`),
		  KEY `numero` (`numero`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="CREATE TABLE `b2b_scst` (  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,  `fecha` date DEFAULT NULL,  `numero` varchar(8) DEFAULT NULL,  `proveed` varchar(5) DEFAULT NULL,  `nombre` varchar(30) DEFAULT NULL,  `depo` varchar(4) DEFAULT NULL,  `montotot` decimal(17,2) DEFAULT NULL,  `montoiva` decimal(17,2) DEFAULT NULL,  `montonet` decimal(17,2) DEFAULT NULL,  `vence` date DEFAULT NULL,  `tipo_doc` char(2) DEFAULT NULL,  `control` varchar(8) NOT NULL DEFAULT '',  `peso` decimal(12,2) DEFAULT NULL,  `estampa` date DEFAULT NULL,  `hora` varchar(8) DEFAULT NULL,  `usuario` varchar(12) DEFAULT NULL,  `nfiscal` varchar(12) DEFAULT NULL,  `exento` decimal(17,2) NOT NULL DEFAULT '0.00',  `sobretasa` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `reducida` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `tasa` decimal(17,2) NOT NULL DEFAULT '0.00',
		  `montasa` decimal(17,2) DEFAULT NULL,
		  `monredu` decimal(17,2) DEFAULT NULL,
		  `monadic` decimal(17,2) DEFAULT NULL,
		  `serie` char(12) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `proveedor` (`proveed`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1";
		var_dump($this->db->simple_query($mSQL));


		//./sincro/notifica.php 
		if (!$this->db->table_exists('cacatua')) {
			$mSQL="CREATE TABLE `cacatua` (
			`nombre` VARCHAR(50) NULL,
			`indices` VARCHAR(100) NULL,
			`fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`nombre`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('eventos')) {
			$mSQL="CREATE TABLE `eventos` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`nombre` varchar(100) NOT NULL,
			`comenta` varchar(100) NOT NULL COMMENT 'Comentario del evento',
			`fechahora` datetime NOT NULL,
			`activador` text NOT NULL COMMENT 'Funcion a evaluar, si devuelve verdadero se dispara',
			`concurrencia` char(1) NOT NULL COMMENT 'S semanal, D diario, H cada hora,',
			`para` tinytext NOT NULL COMMENT 'a quienes se les notifica',
			`accion` text NOT NULL,
			`disparo` datetime NOT NULL,
			`activo` char(1) NOT NULL DEFAULT 'N',
			`usuario` varbinary(10) NOT NULL,
			`estampa` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `nombre` (`nombre`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Tabla que guarda las acciones por eventos'";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('postaccion', 'eventos')){
			$mSQL="ALTER TABLE eventos ADD COLUMN postaccion TEXT NOT NULL AFTER accion";
			$this->db->simple_query($mSQL);
		}


		//./sincro/cargasinv.php 
//		$mSQL="DROP TABLE sinvactu";
//		$this->db->query($mSQL);
		$mSQL='
			CREATE TABLE `sinvactu` (
  `codigo` varchar(15) NOT NULL default "",
  `descrip` varchar(45) default NULL,
  `clave` varchar(8) default NULL,
  `descrip2` varchar(45) default NULL,
  `antdescrip2` varchar(45) default NULL,
  `grupo` varchar(4) default NULL,
  `costo` decimal(13,2) unsigned default NULL,
  `precio1` decimal(13,2) unsigned default NULL,
  `antcosto` decimal(13,2) unsigned default NULL,
  `antprecio1` decimal(13,2) unsigned default NULL,
  `iva` decimal(6,2) unsigned default NULL,
  `antiva` decimal(6,2) unsigned default NULL,
  `precio2` decimal(13,2) default NULL,
  `precio3` decimal(13,2) default NULL,
  `precio4` decimal(13,2) unsigned default NULL,
  `base1` decimal(13,2) unsigned default NULL,
  `base2` decimal(13,2) default NULL,
  `base3` decimal(13,2) unsigned default NULL,
  `base4` decimal(13,2) unsigned default NULL,
  `margen1` decimal(13,2) unsigned default NULL,
  `margen2` decimal(13,2) unsigned default NULL,
  `margen3` decimal(13,2) unsigned default NULL,
  `margen4` decimal(13,2) unsigned default NULL,
  `antdescrip` varchar(45) default NULL,
  `antclave` varchar(8) default NULL,
  `antgrupo` varchar(4) default NULL,
  `antprecio2` decimal(13,2) unsigned default NULL,
  `antprecio3` decimal(13,2) unsigned default NULL,
  `antprecio4` decimal(13,2) unsigned default NULL,
  `antbase1` decimal(13,2) unsigned default NULL,
  `antbase2` decimal(13,2) unsigned default NULL,
  `antbase3` decimal(13,2) unsigned default NULL,
  `antbase4` decimal(13,2) unsigned default NULL,
  `antmargen1` decimal(13,2) unsigned default NULL,
  `antmargen2` decimal(13,2) unsigned default NULL,
  `antmargen3` decimal(13,2) unsigned default NULL,
  `antmargen4` decimal(13,2) unsigned default NULL,
  PRIMARY KEY  (`codigo`)
)
	';
		$this->db->query($mSQL);


		//./sincro/sinvcontrol.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `sinvcontrol` (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`sucursal` VARCHAR(2) NOT NULL,
			`codigo` VARCHAR(15) NOT NULL,
			`precio` CHAR(1) NOT NULL COMMENT 'S modifica el precio N no modifica el precio',
			PRIMARY KEY (`id`),
			UNIQUE INDEX `sucursal_codigo` (`sucursal`, `codigo`)
		)
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";
		var_dump($this->db->simple_query($mSQL));


		//./contabilidad/cierre.php 
			$mSQL="CREATE TABLE IF NOT EXISTS `cplacierre` (
			  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
			  `anno` int(10) DEFAULT NULL,
			  `cuenta` varchar(250) DEFAULT NULL,
			  `descrip` varchar(250) DEFAULT NULL,
			  `monto` decimal(15,2) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `ac` (`anno`,`cuenta`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Cierres contables'";
			$this->db->simple_query($mSQL);


		//./contabilidad/generar.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `cplacierre` (
		  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
		  `anno` int(10) DEFAULT NULL,
		  `cuenta` varchar(250) DEFAULT NULL,
		  `descrip` varchar(250) DEFAULT NULL,
		  `monto` decimal(15,2) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `ac` (`anno`,`cuenta`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Cierres contables'";
		$this->db->simple_query($mSQL);


		//./contabilidad/cplacierre.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `cplacierre` (
		  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
		  `anno` int(10) DEFAULT NULL,
		  `cuenta` varchar(250) DEFAULT NULL,
		  `descrip` varchar(250) DEFAULT NULL,
		  `monto` decimal(15,2) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `ac` (`anno`,`cuenta`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Cierres contables'";
		$this->db->simple_query($mSQL);


		//./inventario/catalogo.php 
		$mSQL='CREATE TABLE `catalogo` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `codigo` varchar(200) default NULL,
		  `nombre` varchar(50) default NULL,
		  `contenido` text,
		  `estampa` timestamp NULL default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `id` (`id`),
		  KEY `id_2` (`id`,`codigo`)
		) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1';
		$this->db->simple_query($mSQL);


		//./inventario/sinvsant.php 
		$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./inventario/sinvprov.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `sinvprov` (
			  `proveed` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `codigop` char(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `codigo` char(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  PRIMARY KEY (`proveed`,`codigop`,`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		";
		$this->db->query($mSQL);
		


		//./inventario/ubica.php 
		$mSQL='ALTER TABLE sinv ADD id INT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id);';


		//./inventario/psinv.php 
		$mSQL="CREATE TABLE  IF NOT EXISTS `psinv` (
				`numero` INT(12) NOT NULL AUTO_INCREMENT,
				`fecha` DATE NULL DEFAULT NULL,
				`vende` VARCHAR(5) NULL DEFAULT NULL,
				`factura` VARCHAR(8) NULL DEFAULT NULL,
				`clipro` VARCHAR(5) NULL DEFAULT NULL,
				`almacen` VARCHAR(4) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL,
				`dir_clipro` VARCHAR(40) NULL DEFAULT NULL,
				`dir_cl1pro` VARCHAR(40) NULL DEFAULT NULL,
				`orden` VARCHAR(12) NULL DEFAULT NULL,
				`observa` VARCHAR(105) NULL DEFAULT NULL,
				`stotal` DECIMAL(12,2) NULL DEFAULT NULL,
				`impuesto` DECIMAL(12,2) NULL DEFAULT NULL,
				`gtotal` DECIMAL(12,2) NULL DEFAULT NULL,
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

		$mSQL="ALTER TABLE `psinv`  ADD COLUMN `agente` VARCHAR(5) NULL DEFAULT NULL AFTER `peso`;";
		var_dump($this->db->simple_query($mSQL));


		//./inventario/sinv.php 

		$campos = $this->db->list_fields('sinv');
		if (!in_array('id',$campos)){
			$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
			$this->db->simple_query($mSQL);
		}

		if (!in_array('alto'       ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN alto          DECIMAL(10,2)");
		if (!in_array('ancho'      ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN ancho         DECIMAL(10,2)");
		if (!in_array('largo'      ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN largo         DECIMAL(10,2)");
		if (!in_array('forma'      ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN forma         VARCHAR(50)");
		if (!in_array('exento'     ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN exento        CHAR(1) DEFAULT 'N'");
		if (!in_array('mmargen'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN mmargen       DECIMAL(7,2) DEFAULT 0 COMMENT 'Margen al Mayor'");
		if (!in_array('pm'         ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pm`          DECIMAL(19,2) NULL DEFAULT '0.00' COMMENT 'porcentaje mayor'");
		if (!in_array('pmb'        ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pmb`         DECIMAL(19,2) NULL DEFAULT '0.00' COMMENT 'porcentaje mayor'");
		if (!in_array('mmargenplus',$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `mmargenplus` DECIMAL(7,2) NULL DEFAULT '0.00' COMMENT 'Margen al Mayor'");
		if (!in_array('escala1'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `escala1`     DECIMAL(12,2) NULL DEFAULT '0.00'");
		if (!in_array('pescala1'   ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pescala1`    DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala1'");
		if (!in_array('escala2'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `escala2`     DECIMAL(12,2) NULL DEFAULT '0.00'");
		if (!in_array('pescala2'   ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pescala2`    DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala2'");
		if (!in_array('escala3'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `escala3`     DECIMAL(12,2) NULL DEFAULT '0.00'");
		if (!in_array('pescala3'   ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pescala3`    DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala3'");
		if (!in_array('mpps',       $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `mpps`        VARCHAR(20) NULL  COMMENT 'Numero de Ministerior de Salud'");
		if (!in_array('cpe',        $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `cpe`         VARCHAR(20) NULL  COMMENT 'Registro de CPE'");
		if (!in_array('tasa',       $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `cpe`         VARCHAR(20) NULL  COMMENT 'Tasa asociada'");

		if ( $this->datasis->traevalor('SUNDECOP') == 'S') {
			if (!in_array('dcomercial', $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `dcomercial`  INT(6)     NULL  COMMENT 'Destino Comercial'");
			if (!in_array('rubro',      $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `rubro`       INT(6)     NULL  COMMENT 'Rubro'");
			if (!in_array('subrubro',   $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `subrubro`    INT(6)     NULL  COMMENT 'Sub Rubro'");
			if (!in_array('cunidad',    $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `cunidad`     INT(6)     NULL  COMMENT 'Unidad de Medida'");
			if (!in_array('cmarca',     $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `cmarca`      INT(6)     NULL  COMMENT 'Marca'");
			if (!in_array('cmaterial',  $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `cmaterial`   INT(6)     NULL  COMMENT 'Material'");
			if (!in_array('cpresenta',  $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `cforma`      INT(6)     NULL  COMMENT 'Forma o Presentacion'");
			if (!in_array('cpactivo',   $campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `cpactivo`    INT(6)     NULL  COMMENT 'Principio Activo'");
		}

		if(!$this->db->table_exists('sinvcombo')){
			$mSQL="CREATE TABLE `sinvcombo` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`combo` CHAR(15) NOT NULL,
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				`descrip` CHAR(30) NULL DEFAULT NULL,
				`cantidad` DECIMAL(10,3) NULL DEFAULT NULL,
				`precio` DECIMAL(15,2) NULL DEFAULT NULL,
				`transac` CHAR(8) NULL DEFAULT NULL,
				`estampa` DATE NULL DEFAULT NULL,
				`hora` CHAR(8) NULL DEFAULT NULL,
				`usuario` CHAR(12) NULL DEFAULT NULL,
				`costo` DECIMAL(17,2) NULL DEFAULT '0.00',
				`ultimo` DECIMAL(19,2) NULL DEFAULT '0.00',
				`pond` DECIMAL(19,2) NULL DEFAULT '0.00',
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sinvpitem')){
			$mSQL="CREATE TABLE `sinvpitem` (
				`producto` VARCHAR(15) NULL DEFAULT NULL COMMENT 'codigo del prod terminado (sinv)',
				`codigo` VARCHAR(15) NULL DEFAULT NULL COMMENT 'codigo del Insumo (sinv)',
				`descrip` VARCHAR(40) NULL DEFAULT NULL,
				`cantidad` DECIMAL(14,3) NULL DEFAULT '0.000',
				`merma` DECIMAL(10,2) NULL DEFAULT '0.00' COMMENT 'Porcentaje de merma',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id_sinv` INT(11) NULL DEFAULT NULL,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`ultimo` DECIMAL(19,2) NOT NULL DEFAULT '0.00',
				`pond` DECIMAL(19,2) NOT NULL DEFAULT '0.00',
				`formcal` CHAR(1) NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`)
			)
			COMMENT='Insumos de un producto terminado'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DYNAMIC
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sinvplabor')){
			$mSQL="CREATE TABLE `sinvplabor` (
				`producto` VARCHAR(15) NULL DEFAULT '' COMMENT 'Producto Terminado',
				`estacion` VARCHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL,
				`actividad` VARCHAR(100) NOT NULL,
				`minutos` INT(6) NULL DEFAULT '0',
				`segundos` INT(6) NULL DEFAULT '0',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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

		if ($this->db->field_exists('minutos', 'sinvplabor')){
			$mSQL="ALTER TABLE `sinvplabor`
			ADD COLUMN `tiempo` DECIMAL(10,2) NULL DEFAULT '0' AFTER `actividad`,
			ADD COLUMN `tunidad` CHAR(1) NULL DEFAULT 'H' COMMENT 'Unidad de tiempo Horas Dias Semanas' AFTER `tiempo`,
			DROP COLUMN `minutos`,
			DROP COLUMN `segundos`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('esta')){
			$mSQL="CREATE TABLE `esta` (
				`estacion` VARCHAR(5) NOT NULL DEFAULT '',
				`nombre` VARCHAR(30) NULL DEFAULT NULL,
				`descrip` TEXT NULL,
				`ubica` TEXT NULL,
				`jefe` VARCHAR(5) NULL DEFAULT NULL COMMENT 'tecnico',
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `vendedor` (`estacion`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1;";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sinvprov')){
			$mSQL="CREATE TABLE `sinvprov` (
				`proveed` CHAR(5) NOT NULL DEFAULT '',
				`codigop` CHAR(15) NOT NULL DEFAULT '',
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				PRIMARY KEY (`proveed`, `codigop`, `codigo`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
		}

		if(!$this->db->table_exists('barraspos')){
			$query="CREATE TABLE `barraspos` (
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				`suplemen` CHAR(15) NOT NULL DEFAULT '',
				PRIMARY KEY (`codigo`, `suplemen`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($query);
		}
		if(!$this->db->table_exists('invfelr')){
			$query="CREATE TABLE `invfelr` (
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				`fecha` DATE NOT NULL DEFAULT '0000-00-00',
				`precio` DECIMAL(17,2) NOT NULL DEFAULT '0.00',
				`existen` DECIMAL(17,2) NULL DEFAULT NULL,
				`anterior` DECIMAL(17,2) NULL DEFAULT NULL,
				`parcial` DECIMAL(17,2) NULL DEFAULT NULL,
				`alma` CHAR(4) NOT NULL DEFAULT '',
				`tipo` CHAR(1) NULL DEFAULT NULL,
				`fhora` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
				`usuario` CHAR(12) NULL DEFAULT NULL,
				`ubica` CHAR(10) NOT NULL DEFAULT ''
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
		}


		//./inventario/grup.php 
		$mSQL="ALTER TABLE `grup`  
				ADD COLUMN `margen` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `comision`,
				ADD COLUMN `margenc` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `margen`";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `grup` ADD COLUMN `status` CHAR(1) NULL DEFAULT NULL";
		$this->db->simple_query($mSQL);


		//./inventario/marc.php 
			$query="ALTER TABLE `marc` ADD COLUMN `margen` DOUBLE(5,2) UNSIGNED NOT NULL DEFAULT '0.00'";
			$this->db->simple_query();


		//./inventario/gfotos.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `itsinvlist` (
		`id` INT(8) NOT NULL AUTO_INCREMENT,
		`numero` INT(8) NULL DEFAULT NULL,
		`codigo` CHAR(15) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',PRIMARY KEY (`id`))
		COLLATE='utf8_unicode_ci'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";

		$mSQL2="CREATE TABLE IF NOT EXISTS`sinvlist` (
		`numero` INT(8) NOT NULL AUTO_INCREMENT,
		`nombre` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
		`fecha` DATE NOT NULL,
		`concepto` TEXT NULL COLLATE 'utf8_unicode_ci',
		`usuario` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		PRIMARY KEY (`numero`)
		)
		COLLATE='utf8_unicode_ci'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT
		";
		$this->db->simple_query($mSQL);
		$this->db->simple_query($mSQL2);


		//./inventario/estajefe.php 
		if (!$this->db->table_exists('estajefe')) {
			$mSQL="CREATE TABLE `estajefe` (
				`codigo` VARCHAR(5) NOT NULL DEFAULT '',
				`nombre` VARCHAR(30) NULL DEFAULT NULL,
				`cedula` VARCHAR(12) NULL DEFAULT NULL,
				`direc1` VARCHAR(35) NULL DEFAULT NULL,
				`direc2` VARCHAR(35) NULL DEFAULT NULL,
				`telefono` VARCHAR(13) NULL DEFAULT NULL,
				`correo` VARCHAR(250) NULL DEFAULT NULL,
				PRIMARY KEY (`codigo`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('cedula', 'estajefe')){
			$mSQL="ALTER TABLE `estajefe` ADD COLUMN `cedula` VARCHAR(12) NULL DEFAULT NULL AFTER `nombre`";
			$this->db->simple_query($mSQL);
		}


		//./inventario/conv.php 
		$mSQL = "ALTER TABLE conv ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ";;
		$this->db->simple_query($mSQL);


		//./inventario/ordpsa.php 
		if (!$this->db->table_exists('ordp')) {
			$mSQL="CREATE TABLE `ordp` (
				`numero` VARCHAR(8) NOT NULL DEFAULT '',
				`fecha` DATE NULL DEFAULT NULL,
				`codigo` VARCHAR(15) NULL DEFAULT NULL COMMENT 'Codigo de inventario',
				`almacen` VARCHAR(4) NULL DEFAULT NULL COMMENT 'Almacen de descuento',
				`cana` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Cantidad a producir',
				`status` CHAR(2) NULL DEFAULT '0' COMMENT 'Activa, Pausada, Finalizada',
				`cliente` CHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL COMMENT 'Nombre del Cliente',
				`instrucciones` TEXT NULL,
				`reserva` CHAR(1) NULL COMMENT 'Si ya se resevo el inventario',
				`estampa` DATE NOT NULL DEFAULT '0000-00-00',
				`usuario` VARCHAR(12) NOT NULL DEFAULT '',
				`hora` VARCHAR(8) NOT NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `numero` (`numero`),
				INDEX `modificado` (`modificado`)
			)
			COMMENT='Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('reserva', 'ordp')){
			$mSQL="ALTER TABLE `ordp` ADD COLUMN `reserva` CHAR(1) NULL COMMENT 'Si ya se resevo el inventario' AFTER `instrucciones`;";
			$this->db->simple_query($mSQL);
		}

		if ($this->db->field_exists('almacen', 'ordp')){
			$mSQL="ALTER TABLE `ordp` ADD COLUMN `almacen` VARCHAR(4) NULL DEFAULT NULL COMMENT 'Almacen de descuento' AFTER `codigo`";
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
				`tipo` CHAR(1) NULL DEFAULT 'P' COMMENT 'P porcentual, M monetario',
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
				`fijo` CHAR(1) NULL DEFAULT 'N' COMMENT 'N si depende de la cantidad, S si no depende de la cantidad',
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

		if (!$this->db->table_exists('ordpbita')) {
			$mSQL="CREATE TABLE `ordpbita` (
				`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				`id_ordplabor` INT(11) UNSIGNED NOT NULL,
				`id_ordp` INT(11) UNSIGNED NULL DEFAULT NULL,
				`fechahora` DATETIME NOT NULL,
				`status` CHAR(1) NOT NULL COMMENT 'I iniciado, P pausado, T terminado',
				`observacion` TEXT NOT NULL,
				`estampa` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`hora` VARCHAR(8) NOT NULL,
				`usuario` VARCHAR(50) NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `id_ordplabor` (`id_ordplabor`)
			)
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
				`tunidad` CHAR(1) NULL DEFAULT 'H',
				`tiempo` DECIMAL(10,2) UNSIGNED NULL DEFAULT '0',
				`tiemporeal` INT(6) UNSIGNED NULL DEFAULT '0',
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

		if ($this->db->field_exists('minutos', 'ordplabor')){
			$mSQL="ALTER TABLE `ordplabor`
			CHANGE COLUMN `minutos` `tunidad` CHAR(1) NULL DEFAULT 'H' AFTER `actividad`,
			CHANGE COLUMN `segundos` `tiempo` DECIMAL(10,2) UNSIGNED NULL DEFAULT '0' AFTER `tunidad`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('fijo', 'ordpitem')){
			$mSQL="ALTER TABLE `ordpitem` ADD COLUMN `fijo` CHAR(1) NULL DEFAULT 'N' COMMENT 'N si depende de la cantidad, S si no depende de la cantidad' AFTER `modificado`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('tipo', 'ordpindi')){
			$mSQL="ALTER TABLE `ordpindi` ADD COLUMN `tipo` CHAR(1) NULL DEFAULT 'P' COMMENT 'P porcentual, M monetario' AFTER `hora`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('ordp', 'stra')){
			$mSQL="ALTER TABLE `stra`
			ADD COLUMN `ordp` VARCHAR(8) NULL DEFAULT NULL AFTER `numere`,
			ADD COLUMN `esta` VARCHAR(5) NULL DEFAULT NULL AFTER `ordp`";
			$this->db->simple_query($mSQL);
		}


		//./inventario/fotos.php 
		$mSQL='CREATE TABLE IF NOT EXISTS `sinvfot` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `codigo` varchar(15) default NULL,
		  `nombre` varchar(50) default NULL,
		  `alto_px` smallint(5) unsigned default NULL,
		  `ancho_px` smallint(6) default NULL,
		  `ruta` varchar(100) default NULL,
		  `comentario` text,
		  `estampa` timestamp NULL default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `id` (`id`),
		  UNIQUE KEY `foto` (`codigo`,`nombre`),
		  KEY `id_2` (`id`,`codigo`)
		) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinvfot` ADD `sinv_id` INT UNSIGNED NOT NULL AFTER `id`';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinvfot` ADD INDEX `sinv_id` (`sinv_id`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinvfot` CHANGE `estampa` `estampa` TIMESTAMP NOT NULL';
		$this->db->simple_query($mSQL);
		$mSQL='UPDATE sinvfot AS a JOIN sinv AS b ON a.codigo=b.codigo SET a.sinv_id=b.id';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinvfot` ADD `principal` VARCHAR(3) NULL';
		$this->db->simple_query($mSQL);


		//./inventario/sinvlist.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `itsinvlist` (
		`id` INT(8) NOT NULL AUTO_INCREMENT,
		`numero` INT(8) NULL DEFAULT NULL,
		`codigo` CHAR(15) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',PRIMARY KEY (`id`))
		COLLATE='utf8_unicode_ci'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";
//		$mSQL2="CREATE TABLE IF NOT EXISTS`sinvlist` (
//		`numero` INT(8) NOT NULL AUTO_INCREMENT,
//		`concepto` TEXT NULL COLLATE 'utf8_unicode_ci',
//		`usuario` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
//		PRIMARY KEY (`numero`)
//		)
//		COLLATE='utf8_unicode_ci'
//		ENGINE=MyISAM
//		ROW_FORMAT=DEFAULT
//		 ";
		$mSQL2="CREATE TABLE IF NOT EXISTS`sinvlist` (
		`numero` INT(8) NOT NULL AUTO_INCREMENT,
		`nombre` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
		`fecha` DATE NOT NULL,
		`concepto` TEXT NULL COLLATE 'utf8_unicode_ci',
		`usuario` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		PRIMARY KEY (`numero`)
		)
		COLLATE='utf8_unicode_ci'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT
		";
		$this->db->simple_query($mSQL);
		$this->db->simple_query($mSQL2);


		//./inventario/sinvpromo.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `sinvpromo` (
				`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`margen` DECIMAL(18,2) NULL DEFAULT NULL,
				`cantidad` DECIMAL(18,3) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX `codigo` (`codigo`)
			)
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
		$this->db->query($mSQL);


		//./inventario/esta.php 
		if (!$this->db->table_exists('esta')) {
			$mSQL="CREATE TABLE `esta` (
				`estacion` VARCHAR(5) NOT NULL DEFAULT '',
				`nombre` VARCHAR(30) NULL DEFAULT NULL,
				`descrip` TEXT NULL,
				`ubica` TEXT NULL,
				`jefe` VARCHAR(5) NULL DEFAULT NULL COMMENT 'tecnico',
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `vendedor` (`estacion`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}


		//./inventario/sinvactu.php 
		$mSQL='
			CREATE TABLE /*!32312 IF NOT EXISTS*/ `sinvactu` (
		  `codigo` varchar(15) NOT NULL default "",
		  `descrip` varchar(45) default NULL,
		  `clave` varchar(8) default NULL,
		  `descrip2` varchar(45) default NULL,
		  `antdescrip2` varchar(45) default NULL,
		  `grupo` varchar(4) default NULL,
		  `costo` decimal(13,2) unsigned default NULL,
		  `precio1` decimal(13,2) unsigned default NULL,
		  `antcosto` decimal(13,2) unsigned default NULL,
		  `antprecio1` decimal(13,2) unsigned default NULL,
		  `iva` decimal(6,2) unsigned default NULL,
		  `antiva` decimal(6,2) unsigned default NULL,
		  `precio2` decimal(13,2) default NULL,
		  `precio3` decimal(13,2) default NULL,
		  `precio4` decimal(13,2) unsigned default NULL,
		  `base1` decimal(13,2) unsigned default NULL,
		  `base2` decimal(13,2) default NULL,
		  `base3` decimal(13,2) unsigned default NULL,
		  `base4` decimal(13,2) unsigned default NULL,
		  `margen1` decimal(13,2) unsigned default NULL,
		  `margen2` decimal(13,2) unsigned default NULL,
		  `margen3` decimal(13,2) unsigned default NULL,
		  `margen4` decimal(13,2) unsigned default NULL,
		  `antdescrip` varchar(45) default NULL,
		  `antclave` varchar(8) default NULL,
		  `antgrupo` varchar(4) default NULL,
		  `antprecio2` decimal(13,2) unsigned default NULL,
		  `antprecio3` decimal(13,2) unsigned default NULL,
		  `antprecio4` decimal(13,2) unsigned default NULL,
		  `antbase1` decimal(13,2) unsigned default NULL,
		  `antbase2` decimal(13,2) unsigned default NULL,
		  `antbase3` decimal(13,2) unsigned default NULL,
		  `antbase4` decimal(13,2) unsigned default NULL,
		  `antmargen1` decimal(13,2) unsigned default NULL,
		  `antmargen2` decimal(13,2) unsigned default NULL,
		  `antmargen3` decimal(13,2) unsigned default NULL,
		  `antmargen4` decimal(13,2) unsigned default NULL,
		  PRIMARY KEY  (`codigo`)
		)';
		$this->db->simple_query($mSQL);	


		//./inventario/stra.php 
		if(!$this->db->field_exists('ordp', 'stra')){
			$mSQL="ALTER TABLE `stra`
			ADD COLUMN `ordp` VARCHAR(8) NULL DEFAULT NULL AFTER `numere`,
			ADD COLUMN `esta` VARCHAR(5) NULL DEFAULT NULL AFTER `ordp`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('tipoordp', 'stra')){
			$mSQL="ALTER TABLE `stra` ADD COLUMN `tipoordp` CHAR(1) NULL DEFAULT NULL COMMENT 'Si es entrega a estacion o retiro de estacion' AFTER `esta`";
			$this->db->simple_query($mSQL);
		}


		//./inventario/scon.php 
		if (!$this->db->table_exists('scon')) {
			$mSQL="CREATE TABLE IF NOT EXISTS `scon` (
				`numero` CHAR(8) NULL DEFAULT NULL,
				`fecha` DATE NULL DEFAULT NULL,
				`tipo` CHAR(1) NULL DEFAULT NULL,
				`tipod` CHAR(1) NULL DEFAULT NULL,
				`status` CHAR(1) NULL DEFAULT 'T',
				`asociado` CHAR(8) NULL DEFAULT NULL,
				`clipro` CHAR(5) NULL DEFAULT NULL,
				`almacen` CHAR(4) NULL DEFAULT NULL,
				`nombre` CHAR(40) NULL DEFAULT NULL,
				`direc1` CHAR(40) NULL DEFAULT NULL,
				`direc2` CHAR(40) NULL DEFAULT NULL,
				`observ1` CHAR(33) NULL DEFAULT NULL,
				`observ2` CHAR(33) NULL DEFAULT NULL,
				`stotal` DECIMAL(12,2) NULL DEFAULT NULL,
				`impuesto` DECIMAL(12,2) NULL DEFAULT NULL,
				`gtotal` DECIMAL(12,2) NULL DEFAULT NULL,
				`peso` DECIMAL(10,3) NULL DEFAULT NULL,
				`id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `numero` (`numero`, `tipo`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			var_dump($this->db->simple_query($mSQL));
		}

		if (!$this->db->table_exists('itscon')) {
			$mSQL="CREATE TABLE IF NOT EXISTS `itscon` (
				`numero` CHAR(8) NULL DEFAULT NULL,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`desca` VARCHAR(40) NULL DEFAULT NULL,
				`cana` DECIMAL(5,0) NULL DEFAULT NULL,
				`recibido` DECIMAL(5,0) NULL DEFAULT NULL,
				`precio` DECIMAL(12,2) NULL DEFAULT NULL,
				`importe` DECIMAL(12,2) NULL DEFAULT NULL,
				`iva` DECIMAL(8,2) NULL DEFAULT NULL,
				`id_scon` INT(15) UNSIGNED NOT NULL,
				`id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `id_scon` (`id_scon`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			var_dump($this->db->simple_query($mSQL));
		}

		if(!$this->datasis->iscampo('scon','origen')){
			$mSQL="ALTER TABLE scon ADD COLUMN origen CHAR(1) NOT NULL DEFAULT 'L' AFTER peso";
			var_dump($this->db->simple_query($mSQL));
		}


		//./inventario/ordp.php 
		if (!$this->db->table_exists('ordp')) {
			$mSQL="CREATE TABLE `ordp` (
				`numero` VARCHAR(8) NOT NULL DEFAULT '',
				`fecha` DATE NULL DEFAULT NULL,
				`codigo` VARCHAR(15) NULL DEFAULT NULL COMMENT 'Codigo de inventario',
				`almacen` VARCHAR(4) NULL DEFAULT NULL COMMENT 'Almacen de descuento',
				`cana` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Cantidad a producir',
				`status` CHAR(2) NULL DEFAULT '0' COMMENT 'Activa, Pausada, Finalizada',
				`cliente` CHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL COMMENT 'Nombre del Cliente',
				`instrucciones` TEXT NULL,
				`reserva` CHAR(1) NULL COMMENT 'Si ya se resevo el inventario',
				`estampa` DATE NOT NULL DEFAULT '0000-00-00',
				`usuario` VARCHAR(12) NOT NULL DEFAULT '',
				`hora` VARCHAR(8) NOT NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `numero` (`numero`),
				INDEX `modificado` (`modificado`)
			)
			COMMENT='Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('reserva', 'ordp')){
			$mSQL="ALTER TABLE `ordp` ADD COLUMN `reserva` CHAR(1) NULL COMMENT 'Si ya se resevo el inventario' AFTER `instrucciones`;";
			$this->db->simple_query($mSQL);
		}

		if ($this->db->field_exists('almacen', 'ordp')){
			$mSQL="ALTER TABLE `ordp` ADD COLUMN `almacen` VARCHAR(4) NULL DEFAULT NULL COMMENT 'Almacen de descuento' AFTER `codigo`";
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
				`tipo` CHAR(1) NULL DEFAULT 'P' COMMENT 'P porcentual, M monetario',
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
				`fijo` CHAR(1) NULL DEFAULT 'N' COMMENT 'N si depende de la cantidad, S si no depende de la cantidad',
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

		if (!$this->db->table_exists('ordpbita')) {
			$mSQL="CREATE TABLE `ordpbita` (
				`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				`id_ordplabor` INT(11) UNSIGNED NOT NULL,
				`id_ordp` INT(11) UNSIGNED NULL DEFAULT NULL,
				`fechahora` DATETIME NOT NULL,
				`status` CHAR(1) NOT NULL COMMENT 'I iniciado, P pausado, T terminado',
				`observacion` TEXT NOT NULL,
				`estampa` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`hora` VARCHAR(8) NOT NULL,
				`usuario` VARCHAR(50) NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `id_ordplabor` (`id_ordplabor`)
			)
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
				`tunidad` CHAR(1) NULL DEFAULT 'H',
				`tiempo` DECIMAL(10,2) UNSIGNED NULL DEFAULT '0',
				`tiemporeal` INT(6) UNSIGNED NULL DEFAULT '0',
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

		if ($this->db->field_exists('minutos', 'ordplabor')){
			$mSQL="ALTER TABLE `ordplabor`
			CHANGE COLUMN `minutos` `tunidad` CHAR(1) NULL DEFAULT 'H' AFTER `actividad`,
			CHANGE COLUMN `segundos` `tiempo` DECIMAL(10,2) UNSIGNED NULL DEFAULT '0' AFTER `tunidad`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('fijo', 'ordpitem')){
			$mSQL="ALTER TABLE `ordpitem` ADD COLUMN `fijo` CHAR(1) NULL DEFAULT 'N' COMMENT 'N si depende de la cantidad, S si no depende de la cantidad' AFTER `modificado`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('tipo', 'ordpindi')){
			$mSQL="ALTER TABLE `ordpindi` ADD COLUMN `tipo` CHAR(1) NULL DEFAULT 'P' COMMENT 'P porcentual, M monetario' AFTER `hora`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('ordp', 'stra')){
			$mSQL="ALTER TABLE `stra`
			ADD COLUMN `ordp` VARCHAR(8) NULL DEFAULT NULL AFTER `numere`,
			ADD COLUMN `esta` VARCHAR(5) NULL DEFAULT NULL AFTER `ordp`";
			$this->db->simple_query($mSQL);
		}


		//./inventario/barraspos.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `barraspos` (
  			`codigo` char(15) NOT NULL DEFAULT '',
  			`suplemen` char(15) NOT NULL DEFAULT '',
  		PRIMARY KEY (`codigo`,`suplemen`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1
		";
		$this->db->simple_query($mSQL);



		//./inventario/comparativo.php 
		/*$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);*/


		//./inventario/recep.php 
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

		//if (!$this->db->table_exists('view_clipro')) {
		//	$user=$this->db->username;
		//	$host=$this->db->hostname;
        //
		//	$mSQL = "CREATE ALGORITHM = UNDEFINED DEFINER= `$user`@`$host` VIEW view_clipro AS
		//	SELECT 'Proveedor' tipo, b.proveed codigo, b.nombre, b.rif, concat_ws(' ', b.direc1, b.direc2, b.direc3) direc FROM `sprv` b
		//	UNION ALL
		//	SELECT 'Cliente' Cliente, a.cliente, a.nombre, a.rifci, concat_ws(' ',a.dire11, a.dire12, a.dire21, a.dire22) direc FROM `scli` a";
		//	$this->db->simple_query($mSQL);
		//}

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


		//./concesionario/comprasan.php 
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


		//./concesionario/compra.php 
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


		//./concesionario/inicio.php 
		//if(!$this->db->field_exists('neumaticos', 'sinvehiculo')){
		//	$mSQL = "ALTER TABLE `sinvehiculo`
		//	ADD COLUMN `neumaticos` INT NULL DEFAULT NULL AFTER `poliza`,
		//	ADD COLUMN `tipo_neumatico` VARCHAR(50) NULL DEFAULT NULL AFTER `neumaticos`,
		//	ADD COLUMN `distanciaeje` FLOAT NULL DEFAULT NULL AFTER `marca`;";
		//	$this->db->simple_query($mSQL);
		//}



		//./forma1.php 
		$this->db->simple_query("ALTER TABLE `formatos`  ADD COLUMN `forma1` TEXT NULL AFTER `forma`");


		//./iasan.php 
		if (!$this->db->table_exists('IA')) {
			$mSQL="CREATE TABLE `IA` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `nombre` varchar(100) NOT NULL,
			  `w` float NOT NULL DEFAULT '1',
			  `pos` int(10) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Pesos de las neuronas'";
			$this->db->simple_query($mSQL);
		}


		//./crm/definiciones.php 
	


		//./crm/eventos.php 
	


		//./crm/callcenter.php 
		if(!$this->db->field_exists('crm', 'scli')){
			$mSQL='ALTER TABLE `scli`  ADD COLUMN `crm` INT(15) UNSIGNED NULL DEFAULT NULL';
			var_dump($this->db->simple_query($mSQL));
		}
		$this->prefijo='crm_';
		contenedor::instalar();


		//./crm/contenedor.php 
		$prefijo=$this->prefijo;

		if (!$this->db->table_exists("${prefijo}comentarios")) {
			$mSQL="CREATE TABLE `${prefijo}comentarios` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`usuario` varchar(50) DEFAULT NULL,
			`contenedor` int(11) NOT NULL DEFAULT '0',
			`fecha` date DEFAULT NULL,
			`motivo` varchar(200) DEFAULT NULL,
			`cuerpo` text,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${prefijo}definiciones")) {
			$mSQL="CREATE TABLE `${prefijo}definiciones` (
			`id` int(7) NOT NULL AUTO_INCREMENT,
			`nombre` varchar(50) DEFAULT '0',
			`estructura` text,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
			$this->db->simple_query($mSQL);
		}


		if (!$this->db->table_exists("${prefijo}contenedor")) {
			$mSQL="CREATE TABLE `${prefijo}contenedor` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`usuario` varchar(50) DEFAULT NULL,
			`derivado` int(11) DEFAULT '0',
			`definicion` int(7) DEFAULT '0',
			`tipo` int(7) DEFAULT '0',
			`status` int(7) DEFAULT '0',
			`fecha` date DEFAULT NULL,
			`cierre` date DEFAULT NULL,
			`resumen` varchar(200) DEFAULT NULL,
			`titulo` varchar(200) DEFAULT NULL,
			`cliente` varchar(5) DEFAULT NULL,
			`proveed` varchar(5) DEFAULT NULL,
			`descripcion` text,
			`condiciones` text,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set." COMMENT='contenedor'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${prefijo}eventos")) {
			$mSQL="CREATE TABLE `${prefijo}eventos` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`usuario` varchar(50) DEFAULT NULL,
			`contenedor` int(11) NOT NULL DEFAULT '0',
			`fecha` date DEFAULT NULL,
			`evento` varchar(200) DEFAULT NULL,
			`vence` date DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${prefijo}imagenes")) {
			$mSQL="CREATE TABLE `${prefijo}imagenes` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`usuario` varchar(50) DEFAULT NULL,
			`contenedor` int(11) NOT NULL DEFAULT '0',
			`fecha` date DEFAULT NULL,
			`nombre` varchar(200) DEFAULT NULL,
			`descripcion` text,
			`url` varchar(200) DEFAULT NULL,
			`imagen` blob,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${prefijo}status")) {
			$mSQL="CREATE TABLE `${prefijo}status` (
			`id` int(7) NOT NULL AUTO_INCREMENT,
			`usuario` varchar(50) DEFAULT NULL,
			`definicion` int(7) DEFAULT '0',
			`descrip` varchar(50) DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${prefijo}tipos")) {
			$mSQL="CREATE TABLE `${prefijo}tipos` (
			`id` int(7) NOT NULL AUTO_INCREMENT,
			`usuario` varchar(50) DEFAULT NULL,
			`definicion` int(7) DEFAULT '0',
			`descrip` varchar(50) DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${prefijo}adjuntos")) {
			$mSQL="CREATE TABLE `${prefijo}adjuntos` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`usuario` varchar(50) DEFAULT NULL,
			`contenedor` int(11) NOT NULL DEFAULT '0',
			`fecha` date DEFAULT NULL,
			`nombre` varchar(200) DEFAULT NULL,
			`descripcion` text,
			`url` varchar(200) DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${prefijo}montos")) {
			$mSQL="CREATE TABLE `${prefijo}montos` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`usuario` varchar(50) DEFAULT NULL,
			`contenedor` int(11) NOT NULL DEFAULT '0',
			`fecha` date DEFAULT NULL,
			`partida` varchar(15) DEFAULT NULL,
			`descripcion` varchar(200) DEFAULT NULL,
			`debe` decimal(19,0) DEFAULT '0',
			`haber` decimal(19,0) DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists("${prefijo}partidas")) {
			$mSQL="CREATE TABLE `${prefijo}partidas` (
			`codigo` varchar(15) NOT NULL DEFAULT '',
			`contenedor` int(7) NOT NULL,
			`descripcion` varchar(100) DEFAULT NULL,
			`enlace` varchar(6) DEFAULT NULL,
			`iva` decimal(5,2) DEFAULT NULL,
			`medida` varchar(5) DEFAULT NULL,
			`dacumu` varchar(5) DEFAULT NULL,
			PRIMARY KEY (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$this->db->char_set;
			$this->db->simple_query($mSQL);
		}



		//./formatos.php 
		$campos=$this->db->list_fields('formatos');
		if(!in_array('proteo'  ,$campos)) $this->db->simple_query("ALTER TABLE `formatos` ADD `proteo` TEXT NULL AFTER `forma`");
		if(!in_array('harbour' ,$campos)) $this->db->simple_query("ALTER TABLE `formatos` ADD `harbour` TEXT NULL AFTER `proteo`");
		if(!in_array('tcpdf'   ,$campos)) $this->db->simple_query("ALTER TABLE `formatos`  ADD COLUMN `tcpdf` TEXT NULL AFTER `forma`");
		if(!in_array('txt'     ,$campos)) $this->db->simple_query("ALTER TABLE `formatos` ADD COLUMN `txt` TEXT NULL AFTER `harbour`");


		//./reportes.php 
		$mSQL="ALTER TABLE `reportes` ADD `proteo` TEXT NULL";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `reportes` ADD `harbour` TEXT NULL";
		$this->db->simple_query($mSQL);
		$mSQL="UPDATE tmenus SET ejecutar=REPLACE(ejecutar,"."'".'" )'."','".'")'."') ";
		$this->db->simple_query($mSQL);


		//./ventas/traer.php 
		$mSQL='ALTER TABLE `sucu` ADD `url` VARCHAR(200) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `proteo` VARCHAR(50) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `prefijo` CHAR(1) NULL';
		$this->db->simple_query($mSQL);


		//./ventas/tcestam.php 
		$mSQL="CREATE TABLE `tcestam` (
		`empre` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		`fecha` date NOT NULL DEFAULT '0000-00-00',
		`cod_prov` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
		`descrip` text COLLATE utf8_unicode_ci,
		`cant` decimal(19,2) DEFAULT '0.00',
		`monto` decimal(19,2) DEFAULT '0.00',
		`total` decimal(19,2) DEFAULT '0.00',
		PRIMARY KEY (`empre`,`fecha`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$this->db->simple_query($mSQL);


		//./ventas/agregarpre.php 
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./ventas/scli.php 
		$seniat=$this->db->escape('http://contribuyente.seniat.gob.ve/BuscaRif/BuscaRif.jsp');
		$mSQL  ="REPLACE INTO valores (nombre,valor,descrip) VALUES ('CONSULRIF',$seniat,'Pagina de consulta de rif del seniat') ON DUPLICATE KEY UPDATE valor=$seniat";
		$this->db->simple_query($mSQL);

		$campos = array();
		$fields = $this->db->field_data('scli');
		foreach ($fields as $field){
			if    ($field->name=='formap' && $field->type!='int')     $this->db->simple_query('ALTER TABLE `scli`  CHANGE COLUMN `formap` `formap` INT(6) NULL DEFAULT 0');
			elseif($field->name=='email'  && $field->max_length!=100) $this->db->simple_query('ALTER TABLE `scli`  CHANGE COLUMN `email` `email` VARCHAR(100) NULL DEFAULT NULL');
			elseif($field->name=='clave'  && $field->max_length!=50)  $this->db->simple_query('ALTER TABLE `scli`  CHANGE COLUMN `clave` `clave` VARCHAR(50) NULL DEFAULT NULL');
			$campos[]=$field->name;
		}

		if (!in_array('id',$campos)){
			$mSQL='ALTER TABLE `scli` DROP PRIMARY KEY, ADD UNIQUE `cliente` (`cliente`)';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE `scli` ADD `id` INT AUTO_INCREMENT PRIMARY KEY';
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sclibitalimit')){
			$mSQL="CREATE TABLE `sclibitalimit` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`cliente`    CHAR(5) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`credito`    CHAR(1) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`creditoant` CHAR(1) NULL DEFAULT NULL,
				`limite`     BIGINT(20) NULL DEFAULT NULL,
				`limiteant`  BIGINT(20) NULL DEFAULT NULL,
				`tolera`     DECIMAL(9,2) NULL DEFAULT NULL,
				`toleraant`  DECIMAL(9,2) NULL DEFAULT NULL,
				`maxtol`     DECIMAL(9,2) NULL DEFAULT NULL,
				`maxtolant`  DECIMAL(9,2) NULL DEFAULT NULL,
				`motivo`     TEXT NULL,
				`formap`     DECIMAL(9,0) NULL DEFAULT NULL,
				`formapsant` DECIMAL(9,0) NULL DEFAULT NULL,
				`estampa`    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				`usuario`    VARCHAR(12) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX `cliente` (`cliente`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('creditoant', 'sclibitalimit')){
			$mSQL="ALTER TABLE `sclibitalimit`
			ADD COLUMN `creditoant` CHAR(1) NULL DEFAULT NULL AFTER `credito`,
			ADD COLUMN `toleraant` DECIMAL(9,2) NULL DEFAULT NULL AFTER `tolera`,
			ADD COLUMN `maxtolant` DECIMAL(9,2) NULL DEFAULT NULL AFTER `maxtol`,
			ADD COLUMN `formap` INT(6) NULL DEFAULT NULL AFTER `maxtol`,
			ADD COLUMN `formapsant` INT(6) NULL DEFAULT NULL AFTER `formap`";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('modifi'  ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `modifi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL AFTER `mensaje`");
		if(!in_array('credito' ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `credito` CHAR(1) NOT NULL DEFAULT 'N' AFTER `limite`");
		if(!in_array('sucursal',$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `sucursal` CHAR(2) NULL DEFAULT NULL");
		if(!in_array('mmargen' ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `mmargen` DECIMAL(7,2) NULL DEFAULT 0 COMMENT 'Margen al Mayor'");
		if(!in_array('tolera'  ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `tolera` DECIMAL(9,2) NULL DEFAULT '0' AFTER `credito`");
		if(!in_array('maxtole' ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `maxtole` DECIMAL(9,2) NULL DEFAULT '0' AFTER `tolera`");


		//./ventas/pfac.php 
		if (!$this->db->field_exists('dxapli','itpfac'))
		$this->db->query("ALTER TABLE `itpfac`  ADD COLUMN `dxapli` VARCHAR(20) NOT NULL COMMENT 'descuento por aplicar'");
		$this->db->query("ALTER TABLE `itpfac`  CHANGE COLUMN `dxapli` `dxapli` VARCHAR(20) NULL COMMENT 'descuento por aplicar'");



		//./ventas/pfacdespfyco.php 
		$mSQL="ALTER TABLE `sitems` ADD `cdespacha` DECIMAL NULL";
		$mSQL1="ALTER TABLE `sitems` ADD `ultidespachado` DECIMAL NULL";
		$this->db->simple_query($mSQL);
		$this->db->simple_query($mSQL1);
		echo 'Instalado';


		//./ventas/rcaj.php 
		if(!$this->db->table_exists('itrcaj')){
			$mSQL="CREATE TABLE `itrcaj` (
				`numero` VARCHAR(8) NOT NULL DEFAULT '' COLLATE 'latin1_swedish_ci',
				`tipo` VARCHAR(15) NOT NULL DEFAULT '' COLLATE 'latin1_swedish_ci',
				`cierre` CHAR(1) NOT NULL DEFAULT 'N' COLLATE 'latin1_swedish_ci',
				`recibido` DECIMAL(17,2) NULL DEFAULT NULL,
				`sistema` DECIMAL(17,2) NULL DEFAULT NULL,
				`diferencia` DECIMAL(17,2) NULL DEFAULT NULL,
				PRIMARY KEY (`numero`, `tipo`, `cierre`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}

		if($this->db->field_exists('cierre', 'itrcaj')){
			$mSQL="ALTER TABLE `itrcaj`  ADD COLUMN `cierre` CHAR(1) NOT NULL DEFAULT 'N' AFTER `tipo`";
			$this->db->simple_query($mSQL);
		}
		$mSQL="ALTER TABLE `itrcaj`  DROP PRIMARY KEY,  ADD PRIMARY KEY (`numero`, `tipo`, `cierre`)";
		$this->db->simple_query($mSQL);

		if($this->db->field_exists('cierre', 'sfpa')){
			$mSQL="ALTER TABLE `sfpa`  ADD COLUMN `cierre` CHAR(8) DEFAULT '' AFTER `hora`";
			$this->db->simple_query($mSQL);
		}

		$mSQL="ALTER TABLE `rcaj` CHANGE COLUMN `estampa` `estampa` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
		$this->db->simple_query($mSQL);



		//./ventas/sfacter.php 
		if(!$this->datasis->iscampo('sfac','sprv')){
			$mSQL="ALTER TABLE sfac ADD COLUMN sprv VARCHAR(5) NULL DEFAULT NULL COMMENT ''";
			$ban=$this->db->simple_query($mSQL);
		}


		//./ventas/metas.php 
		if(!$this->db->field_exists('pmargen', 'vend')){
			$mSQL="ALTER TABLE `vend` ADD COLUMN `pmargen` DECIMAL(5,2) UNSIGNED NULL DEFAULT '0' AFTER `almacen`";
			$rt=$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('metas')){
			$mSQL="CREATE TABLE `metas` (
				`id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
				`codigo` VARCHAR(15) NOT NULL DEFAULT '',
				`cantidad` DECIMAL(12,3) NULL DEFAULT NULL,
				`peso` DECIMAL(12,3) NULL DEFAULT NULL,
				`fecha` INT(10) NOT NULL DEFAULT '0',
				`tipo` CHAR(1) NOT NULL DEFAULT 'T' COMMENT 'Unidad de medida T=Tonelada',
				PRIMARY KEY (`id`),
				UNIQUE INDEX `codfec` (`fecha`, `codigo`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}


		//./ventas/sfacman.php 
		if(!$this->datasis->iscampo('sfac','mandatario')){
			$mSQL="ALTER TABLE sfac ADD COLUMN mandatario VARCHAR(5) NULL DEFAULT NULL COMMENT ''";
			$ban=$this->db->simple_query($mSQL);
		}


		//./ventas/vehiculos.php 
		if (!$this->db->table_exists('vehiculos')) {
			$mSQL="CREATE TABLE `vehiculos` (
			  `id` int(10) unsigned NOT NULL DEFAULT '0',
			  `placa` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `marca` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `modelo` int(10) DEFAULT NULL,
			  `cliente` char(5) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `kilometraje` int(11) unsigned DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='vehiculos a los que se hace servicio'";
			$this->db->simple_query($mSQL);
		}


		//./ventas/agregarfac.php 
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./ventas/pfactransac.php 
		if(!$this->db->field_exists('dxm', 'itpfac')){
			$mSQL="ALTER TABLE `itpfac`
			CHANGE COLUMN `dxapli` `dxapli` VARCHAR(20) NULL COMMENT 'descuento por aplicar' AFTER `id`,
			ADD COLUMN `dxm` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por marca' AFTER `dxapli`,
			ADD COLUMN `dxg` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por grupo' AFTER `dxm`,
			ADD COLUMN `dxz` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por zona' AFTER `dxg`,
			ADD COLUMN `dxc` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por cliente' AFTER `dxz`,
			ADD COLUMN `dxe` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por escala' AFTER `dxc`,
			ADD COLUMN `dxp` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento plus' AFTER `dxe`,
			ADD COLUMN `escala` INT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'cantidad para la escala' AFTER `dxe`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('margen', 'zona')){
			$mSQL="ALTER TABLE `zona` ADD COLUMN `margen` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' AFTER `descrip`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('margen', 'marc')){
			$mSQL="ALTER TABLE `marc` ADD COLUMN `margen` DOUBLE(5,2) UNSIGNED NOT NULL DEFAULT '0' AFTER `marca`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('escala1', 'sinv')){
			$mSQL="ALTER TABLE `sinv`
			ADD COLUMN `escala1` DECIMAL(12,2) NULL DEFAULT '0.00' AFTER `pmb`,
			ADD COLUMN `pescala1` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala1' AFTER `escala1`,
			ADD COLUMN `escala2` DECIMAL(12,2) NULL DEFAULT '0.00' AFTER `pescala1`,
			ADD COLUMN `pescala2` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala2' AFTER `escala2`,
			ADD COLUMN `escala3` DECIMAL(12,2) NULL DEFAULT '0.00' AFTER `pescala2`,
			ADD COLUMN `pescala3` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala3' AFTER `escala3`";
			$this->db->simple_query($mSQL);

			$mSQL="ALTER TABLE `sinv` ADD COLUMN `mmargenplus` DECIMAL(7,2) NULL DEFAULT '0.00' COMMENT 'Margen al Mayor' AFTER `mmargen`";
			$this->db->simple_query($mSQL);
		}



		//./ventas/scaj.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `vieite` (
			`numero` char(8) default NULL,
			`fecha` date default '0000-00-00',
			`codigo` char(15) default NULL,
			`precio` decimal(10,2) default '0.00',
			`monto` decimal(18,2) default '0.00',
			`cantidad` decimal(12,3) default NULL,
			`impuesto` decimal(6,2) default '0.00',
			`costo` decimal(18,2) default '0.00',
			`almacen` char(4) default NULL,
			`cajero` char(5) default NULL,
			`caja` char(5) NOT NULL default '',
			`referen` char(15) default NULL,
			KEY `fecha` (`fecha`),
			KEY `codigo` (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='ventas por articulo'";
		$this->db->simple_query($mSQL);
		$mSQL="CREATE TABLE IF NOT EXISTS `fmay` (
			`fecha` date default NULL,
			`numero` varchar(8) NOT NULL default '',
			`presup` varchar(8) default NULL,
			`almacen` varchar(4) default NULL,
			`cod_cli` varchar(5) default NULL,
			`nombre` varchar(40) default NULL,
			`vence` date default NULL,
			`vende` varchar(5) default NULL,
			`stotal` decimal(17,2) default '0.00',
			`impuesto` decimal(17,2) default '0.00',
			`gtotal` decimal(17,2) default '0.00',
			`tipo` char(1) default NULL,
			`observa1` varchar(40) default NULL,
			`observa2` varchar(40) default NULL,
			`observa3` varchar(40) default NULL,
			`porcenta` decimal(17,2) default '0.00',
			`descuento` decimal(17,2) default '0.00',
			`cajero` varchar(5) default NULL,
			`dire1` varchar(30) default NULL,
			`dire2` varchar(30) default NULL,
			`rif` varchar(15) default NULL,
			`nit` varchar(15) default NULL,
			`exento` decimal(17,2) default '0.00',
			`transac` varchar(8) default NULL,
			`estampa` date default NULL,
			`hora` varchar(5) default NULL,
			`usuario` varchar(12) default NULL,
			`nfiscal` varchar(12) NOT NULL default '0',
			`tasa` decimal(19,2) default NULL,
			`reducida` decimal(19,2) default NULL,
			`sobretasa` decimal(17,2) default NULL,
			`montasa` decimal(17,2) default NULL,
			`monredu` decimal(17,2) default NULL,
			`monadic` decimal(17,2) default NULL,
			`cedula` varchar(13) default NULL,
			`dirent1` varchar(40) default NULL,
			`dirent2` varchar(40) default NULL,
			`dirent3` varchar(40) default NULL,
			PRIMARY KEY  (`numero`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./ventas/agregaroc.php 
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./ventas/fallaped.php 
		if (!$this->db->table_exists('fallaped')) {
			$mSQL="CREATE TABLE `fallaped` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `codigo` varchar(15) DEFAULT NULL,
			  `barras` varchar(15) DEFAULT NULL,
			  `descrip` varchar(45) DEFAULT NULL,
			  `cana` int(11) DEFAULT NULL,
			  `ventas` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `codigo` (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='pedidos a droguerias por fallas'";
			$this->db->simple_query($mSQL);
		}


		//./ventas/requisitos.php 
		$mSQL="CREATE TABLE `requisitos` (`codigo` TINYINT UNSIGNED AUTO_INCREMENT, `descrip` VARCHAR (150), PRIMARY KEY(`codigo`))";
	  $this->db->simple_query($mSQL);	


		//./ventas/tsprv.php 
		$mSQL="CREATE TABLE `tsprv` (
		`codigo` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		`nombre` text COLLATE utf8_unicode_ci,
		PRIMARY KEY (`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$this->db->simple_query($mSQL);


		//./ventas/agregaroi.php 
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./ventas/sfacdespfyco.php 
		$mSQL="ALTER TABLE `sitems` ADD `cdespacha` DECIMAL NULL";
		$mSQL1="ALTER TABLE `sitems` ADD `ultidespachado` DECIMAL NULL";
		$this->db->simple_query($mSQL);
		$this->db->simple_query($mSQL1);
		echo 'Instalado';


		//./ventas/rret.php 
		$mSQL="CREATE TABLE `rret` (
		  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
		  `cierre` varchar(8) DEFAULT NULL,
		  `cajero` varchar(5) DEFAULT NULL,
		  `tipo` char(2) DEFAULT NULL,
		  `monto` decimal(12,2) DEFAULT NULL,
		  `fecha` date DEFAULT NULL,
		  `estampa` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `Index 2` (`tipo`,`cajero`)
		) ENGINE=MyISAM COMMENT='Retiros de caja'";

		$this->db->simple_query($mSQL);


		//./ventas/pfaclitemayor.php 
		if(!$this->db->field_exists('dxm', 'itpfac')){
			$mSQL="ALTER TABLE `itpfac`
			CHANGE COLUMN `dxapli` `dxapli` VARCHAR(20) NULL COMMENT 'descuento por aplicar' AFTER `id`,
			ADD COLUMN `dxm` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por marca' AFTER `dxapli`,
			ADD COLUMN `dxg` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por grupo' AFTER `dxm`,
			ADD COLUMN `dxz` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por zona' AFTER `dxg`,
			ADD COLUMN `dxc` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por cliente' AFTER `dxz`,
			ADD COLUMN `dxe` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento por escala' AFTER `dxc`,
			ADD COLUMN `dxp` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'descuento plus' AFTER `dxe`,
			ADD COLUMN `escala` INT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'cantidad para la escala' AFTER `dxe`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('margen', 'zona')){
			$mSQL="ALTER TABLE `zona` ADD COLUMN `margen` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0' AFTER `descrip`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('margen', 'marc')){
			$mSQL="ALTER TABLE `marc` ADD COLUMN `margen` DOUBLE(5,2) UNSIGNED NOT NULL DEFAULT '0' AFTER `marca`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('escala1', 'sinv')){
			$mSQL="ALTER TABLE `sinv`
			ADD COLUMN `escala1` DECIMAL(12,2) NULL DEFAULT '0.00' AFTER `pmb`,
			ADD COLUMN `pescala1` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala1' AFTER `escala1`,
			ADD COLUMN `escala2` DECIMAL(12,2) NULL DEFAULT '0.00' AFTER `pescala1`,
			ADD COLUMN `pescala2` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala2' AFTER `escala2`,
			ADD COLUMN `escala3` DECIMAL(12,2) NULL DEFAULT '0.00' AFTER `pescala2`,
			ADD COLUMN `pescala3` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala3' AFTER `escala3`";
			$this->db->simple_query($mSQL);

			$mSQL="ALTER TABLE `sinv` ADD COLUMN `mmargenplus` DECIMAL(7,2) NULL DEFAULT '0.00' COMMENT 'Margen al Mayor' AFTER `mmargen`";
			$this->db->simple_query($mSQL);
		}



		//./ventas/pfaclite.php 
		$query="ALTER TABLE `pfac`  ADD COLUMN `id` INT NULL AUTO_INCREMENT AFTER `fenvia`,
		ADD PRIMARY KEY (`id`),  ADD UNIQUE INDEX `numero` (`numero`)";
		$this->db->simple_query($query);


		//./ventas/otal.php 
		$sql="CREATE TABLE `otal` (
			`numero` INT(10) NULL DEFAULT NULL,
			`fecha` DATE NULL DEFAULT NULL,
			`cod_cli` CHAR(5) NULL DEFAULT NULL,
			`nombre` VARCHAR(50) NULL DEFAULT NULL,
			`tipo` CHAR(1) NULL DEFAULT NULL,
			`factura` CHAR(8) NULL DEFAULT NULL,
			`codigo` CHAR(15) NULL DEFAULT NULL,
			`descrip` VARCHAR(50) NULL DEFAULT NULL,
			`falla` TEXT NULL,
			`ofrecido` DATE NULL DEFAULT NULL
			)
			COMMENT='Orden de Taller'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT
		";
		$this->db->query($sql);	
		$sql="ALTER TABLE `otal` CHANGE COLUMN `numero` `numero` INT(10) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`numero`)";
		$this->db->query($sql);	
		


		//./ventas/sinvehiculo.php 
		if (!$this->db->table_exists('sinvehiculo')) {
			$mSQL="CREATE TABLE `sinvehiculo` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `id_sfac` int(10) DEFAULT '0',
			  `id_scst` int(10) DEFAULT '0',
			  `marca` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
			  `modelo` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
			  `color` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
			  `motor` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
			  `carroceria` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
			  `uso` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
			  `anio` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
			  `peso` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Vehiculos a la venta'";
			$this->db->simple_query($mSQL);
		}


		//./ventas/sfac.php 
		$campos = $this->db->list_fields('sfac');

		if(!in_array('ereiva'  ,$campos)){
			$this->db->simple_query("ALTER TABLE sfac ADD ereiva DATE AFTER freiva");
		}
		if(!in_array('entregado'  ,$campos)){
			$this->db->simple_query("ALTER TABLE sfac ADD entregado DATE ");
			$this->db->simple_query("UPDATE sfac SET entregado=fecha");
		}

		if(!in_array('comiadi'  ,$campos)){
			$this->db->simple_query("ALTER TABLE sfac ADD comiadi DECIMAL(10,2) DEFAULT 0 ");
		}


		if(!in_array('upago'  ,$campos)){
			$this->db->query("ALTER TABLE sfac ADD upago INT(10)");
		}


		//./ventas/sclifyco.php 
		$seniat='http://www.seniat.gov.ve/BuscaRif/BuscaRif.jsp';
		$mSQL="INSERT INTO valores (nombre,valor,descrip) VALUES ('CONSULRIF','$seniat','Pagina de consulta de rif del seniat') ON DUPLICATE KEY UPDATE valor='$seniat'";
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `scli` ADD `modifi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL AFTER `mensaje`';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `scli` DROP PRIMARY KEY, ADD UNIQUE `cliente` (`cliente`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE scli ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
                


		//./ventas/tarjeta.php 
		$mSQL="ALTER TABLE `tarjeta` 
			ADD COLUMN `activo` 
			CHAR(1) NULL DEFAULT NULL AFTER `mensaje`";
		$this->db->query($mSQL);


		//./ventas/fiscalz.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `fiscalz` (
		  `caja` char(4) default NULL,
		  `serial` char(12) NOT NULL default '',
		  `numero` char(4) NOT NULL default '',
		  `fecha` date default NULL,
		  `factura` char(8) default NULL,
		  `fecha1` date default NULL,
		  `hora` time default NULL,
		  `exento` decimal(12,2) unsigned default NULL,
		  `base` decimal(12,2) unsigned default NULL,
		  `iva` decimal(12,2) unsigned default NULL,
		  `base1` decimal(12,2) unsigned default NULL,
		  `iva1` decimal(12,2) unsigned default NULL,
		  `base2` decimal(12,2) unsigned default NULL,
		  `iva2` decimal(12,2) unsigned default NULL,
		  `ncexento` decimal(12,2) unsigned default NULL,
		  `ncbase` decimal(12,2) unsigned default NULL,
		  `nciva` decimal(12,2) unsigned default NULL,
		  `ncbase1` decimal(12,2) unsigned default NULL,
		  `nciva1` decimal(12,2) unsigned default NULL,
		  `ncbase2` decimal(12,2) unsigned default NULL,
		  `nciva2` decimal(12,2) unsigned default NULL,
		  `ncnumero` char(8) default NULL,
		  PRIMARY KEY  (`serial`,`numero`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `fiscalz` ADD `manual` CHAR(1)DEFAULT 'N' NULL";
    $this->db->simple_query($mSQL);


		//./ventas/agregarped.php 
		$mSQL='ALTER TABLE itpfac ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./ventas/sclicol.php 
		$seniat='http://www.seniat.gov.ve/BuscaRif/BuscaRif.jsp';
		$mSQL="REPLACE INTO valores (nombre,valor,descrip) VALUES ('CONSULRIF','$seniat','Pagina de consulta de rif del seniat') ON DUPLICATE KEY UPDATE valor='$seniat'";
		$this->db->simple_query($mSQL);
		
		if (!$this->db->field_exists('modifi','scli')) {
			$mSQL='ALTER TABLE `scli` ADD `modifi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL AFTER `mensaje`';
			$this->db->simple_query($mSQL);
		}
		
		if (!$this->db->field_exists('id','scli')) {
			$mSQL='ALTER TABLE `scli` DROP PRIMARY KEY, ADD UNIQUE `cliente` (`cliente`)';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE scli ADD id INT AUTO_INCREMENT PRIMARY KEY';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE `scli`  CHANGE COLUMN `formap` `formap` INT(6) NULL DEFAULT 0';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE `scli`  CHANGE COLUMN `email` `email` VARCHAR(100) NULL DEFAULT NULL';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE `scli`  CHANGE COLUMN `clave` `clave` VARCHAR(50) NULL DEFAULT NULL AFTER `tiva`';
			$this->db->simple_query($mSQL);
		}
		if (!$this->db->field_exists('nombre1','scli')) {
			$mSQL="ALTER TABLE `scli`  ADD COLUMN `nombre1` VARCHAR(100) NULL AFTER `mmargen`,  ADD COLUMN `nombre2` VARCHAR(100) NULL AFTER `nombre1`,  ADD COLUMN `apellido1` VARCHAR(100) NULL AFTER `nombre2`,  ADD COLUMN `apellido2` VARCHAR(100) NULL AFTER `apellido1`";
			$this->db->simple_query($mSQL);
		}
		if (!$this->db->field_exists('crc' ,'scli')) {
			$mSQL="ALTER TABLE `scli`  ADD COLUMN `crc` INT(1) NULL AFTER `mmargen`";
			$this->db->simple_query($mSQL);
		}
		if (!$this->db->field_exists('docui','scli')) {
			$mSQL="ALTER TABLE `scli` ADD COLUMN `docui` CHAR(1) NULL AFTER `crc`";
			$this->db->simple_query($mSQL);
		}
		if (!$this->db->field_exists('tipocol','rete')) {
			$mSQL="ALTER TABLE rete ADD COLUMN tipocol CHAR(1) NULL DEFAULT NULL AFTER cuenta;";
			$this->db->simple_query($mSQL);
		}


		//./ventas/cobrocli.php 
		$seniat='http://www.seniat.gov.ve/BuscaRif/BuscaRif.jsp';
		$mSQL="INSERT INTO valores (nombre,valor,descrip) VALUES ('CONSULRIF','$seniat','Pagina de consulta de rif del seniat') ON DUPLICATE KEY UPDATE valor='$seniat'";
		$this->db->simple_query($mSQL);


		//./ventas/agregarnd.php 
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./ventas/zona.php 
		 $campos = $this->db->list_fields('zona');

		if (!in_array('id',$campos)){
			$this->db->simple_query('ALTER TABLE zona DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE zona ADD UNIQUE INDEX codigo (codigo)');
			$this->db->simple_query('ALTER TABLE zona ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		}

		if (!in_array('margen',$campos)){
			$query="ALTER TABLE `zona` ADD COLUMN `margen` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT '0.00'";
			$this->db->simple_query();
		}


		//./supervisor/menu.php 
		$mSQL="ALTER TABLE `intramenu` ADD COLUMN `orden` TINYINT(4) NULL DEFAULT NULL AFTER `pertenece`";
		echo $this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `intramenu` ADD COLUMN `ancho` INT(10) UNSIGNED NULL DEFAULT '800' AFTER `orden`";
		echo $this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `intramenu` ADD COLUMN `alto`  INT(10) UNSIGNED NULL DEFAULT '600' AFTER `ancho`";
		echo $this->db->simple_query($mSQL);


		//./supervisor/tmenus.php 
		// Revisar los ejecutar
		$mSQL = "UPDATE intramenu SET ejecutar=MID(ejecutar,2,200) WHERE MID(ejecutar,1,1)='/' ";
		$this->db->simple_query($mSQL);

		// Crea la opcion en el menu
		if ($this->datasis->dameval('SELECT COUNT(*) FROM intramenu WHERE ejecutar="supervisor/tmenus"')==0 ) {
			//Trae el Siguiente Modulo
			$ultimo = $this->datasis->prox_imenu('9');
			$data = array(
				      'modulo'    => $ultimo,
				      'mensaje'   => 'Menus de DataSIS',
				      'titulo'    => 'Menu de DataSIS',
				      'panel'     => 'CONFIGURACION',
				      'ejecutar'  => 'supervisor/tmenus',
				      'target'    => 'popu',
				      'visible'   => 'S',
				      'pertenece' => '9',
				      'ancho'     => '800',
				      'alto'      => '600'
			);
			$this->db->insert('tmenus', $data);
			echo "Creado $ultimo";
		}
		redirect($this->url.'jqdatag');


		//./supervisor/i18n.php 
		$mSQL="";
		$this->db->simple_query($mSQL);


		//./supervisor/repodupli.php 
		$mSQL="ALTER TABLE `repodupli` ADD `status` CHAR(2) NULL";
		$this->db->simple_query($mSQL);


		//./supervisor/formatos.php 
		if ($this->db->table_exists('intrarepo')){
			$mSQL="CREATE TABLE IF NOT EXISTS `intrarepo` (
			`nombre` varchar(71) NOT NULL default '',
			`modulo` varchar(10) NOT NULL default '',
			`titulo` varchar(20) default NULL,
			`mensaje` varchar(60) default NULL,
			`activo` char(1) default 'S',
			PRIMARY KEY  (`nombre`,`modulo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}
		if ($this->db->field_exists('tcpdf2', 'formatos')) $this->db->simple_query("ALTER TABLE `formatos`  ADD COLUMN `tcpdf2` TEXT NULL");


		//./supervisor/conec.php 
		$mSQL="CREATE TABLE `tiketconec` (
		  `id` int(11) NOT NULL auto_increment,
		  `cliente` char(5) default NULL,
		  `phtml` int(20) default NULL,
		  `url` varchar(100) default NULL,
		  `sistema` varchar(50) default NULL,
		  `basededato` varchar(20) default NULL,
		  `puerto` int(3) default NULL,
		  `usuario` varchar(20) default NULL,
		  `clave` varchar(20) default NULL,
		  `observacion` text,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./supervisor/sitemslog.php 
		if(!$this->db->table_exists('sitemsest')){
			$mSQL="CREATE TABLE `sitemsest` (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`usuario` VARCHAR(50) NOT NULL DEFAULT '0',
			`periodo` VARCHAR(6) NOT NULL DEFAULT '0',
			`agrega` INT(10) NOT NULL DEFAULT '0',
			`eliminado` INT(10) NOT NULL DEFAULT '0',
			`modifica` INT(10) NOT NULL DEFAULT '0',
			`facturado` INT(10) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			UNIQUE INDEX `usuario_periodo` (`usuario`, `periodo`)
			)
			COMMENT='Resumen de sitems log'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}


		//./supervisor/sucu.php 
		$mSQL="ALTER TABLE `sucu` ADD `url` VARCHAR(200) NULL";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `sucu` ADD `prefijo` VARCHAR(3) NULL";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `sucu` ADD `proteo` VARCHAR(50) NULL";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `sucu` ADD PRIMARY KEY (`codigo`)";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `sucu`  ADD COLUMN `db_nombre` VARCHAR(50) NULL DEFAULT NULL AFTER `proteo`";
		$this->db->simple_query($mSQL);


		//./supervisor/internet.php 
		$mSQL="CREATE TABLE `internet` (
		  `nombre` varchar(20) NOT NULL default '',
		  `lista` text,
		  `descrip` varchar(100) default NULL,
		  PRIMARY KEY  (`nombre`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
		$mSQL="INSERT INTO internet (nombre) VALUES ('IPACEPTADOS')";                        
		$this->db->simple_query($mSQL);
		$mSQL="INSERT INTO internet (nombre) VALUES ('PAGINASNEGADAS')";
		$this->db->simple_query($mSQL);


		//./supervisor/repomenu.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `intrarepo` (
		  `nombre` varchar(71) NOT NULL default '',
		  `modulo` varchar(10) NOT NULL default '',
		  `titulo` varchar(20) default NULL,
		  `mensaje` varchar(60) default NULL,
		  `activo` char(1) default 'S',
		  PRIMARY KEY  (`nombre`,`modulo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./supervisor/bitacorafyco.php 
		$mSQL="CREATE TABLE `bitacora` (
		  `id` bigint(20) unsigned NOT NULL auto_increment,
		  `usuario` varchar(50) default NULL,
		  `nombre` varchar(100) default NULL,
		  `fecha` date default NULL,
		  `hora` time default NULL,
		  `actividad` text,
		  `comentario` text,
		  `revisado` char(1) default 'P',
		  `evaluacion` text,
		  `asignacion` varchar(10),
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=524 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./supervisor/directorio.php 
		$mSQL="CREATE TABLE `datasis`.`directorio` (`id` INT AUTO_INCREMENT, `cedula` VARCHAR (13), `cliente` VARCHAR (30), `proveed` VARCHAR (30),`empleado` VARCHAR (30), `nombres` VARCHAR (50), `apellidos` VARCHAR (50), `edad` VARCHAR (2), `sexo` VARCHAR (1), `telefono1` VARCHAR (20), `telefono2` VARCHAR (20), `telefono3` VARCHAR (20),`direc1` VARCHAR (70), `direc2` VARCHAR (70), `profesion` VARCHAR (30), `cargo` VARCHAR (30), `fnacimiento` VARCHAR (20),`email` VARCHAR (50),`email2` VARCHAR (50),`email3` VARCHAR (50), PRIMARY KEY(`id`)) TYPE = MyISAM"; 
		$this->db->simple_query($mSQL);


		//./supervisor/accesos.php 
		for($i=1;$i<=65535;$i++)
			$this->db->simple_query("INSERT INTO serie SET hexa=HEX($i)");
		echo "hola mundo";
		//$mSQL='ALTER TABLE `intramenu` DROP PRIMARY KEY';
		//$this->db->simple_query($mSQL);
		//$mSQL='ALTER TABLE intramenu ADD id INT AUTO_INCREMENT PRIMARY KEY';
		//$this->db->simple_query($mSQL);
		//$mSQL='ALTER TABLE `intramenu` ADD `pertenece` VARCHAR(10) DEFAULT NULL NULL AFTER `visible`';
		//$this->db->simple_query($mSQL);
		//$mSQL='UPDATE intramenu SET pertenece=MID(modulo,1,1) WHERE MID(modulo,1,1)!= "0" AND modulo REGEXP  "[[:digit:]]" AND CHAR_LENGTH(modulo)>1';
		//$this->db->simple_query($mSQL);
		////ALTER TABLE `intramenu` ADD PRIMARY KEY (`modulo`)


		//./supervisor/publicidad.php 
		$mSQL="CREATE TABLE `publicidad` (
		  `id` bigint(20) unsigned NOT NULL auto_increment,
		  `archivo` varchar(100) default NULL,
		  `bgcolor` varchar(7) default NULL,
		  `prob` float unsigned default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		  `descrip` varchar(200) default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `id` (`id`,`archivo`),
		  KEY `id_2` (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./supervisor/ejecutasql.php 
		if (!$this->db->table_exists('ejecutasql')) {
			$mSQL="CREATE TABLE `ejecutasql` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `usuario` varchar(12) DEFAULT NULL,
			  `tipo` varchar(1) DEFAULT NULL COMMENT 'Restringido, Abierto',
			  `nombre` varchar(100) DEFAULT NULL COMMENT 'Restringido, Abierto',
			  `script` text COMMENT 'Restringido, Abierto',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Escripts para ejecutar por usuario'";
			$this->db->simple_query($mSQL);
		}


		//./supervisor/repotra.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `repotra` (`id` TINYINT AUTO_INCREMENT, `estampa` TIMESTAMP, `fecha` DATE, `t1horae` VARCHAR (8),`t2horae` VARCHAR (8), `t1horas` VARCHAR (8),`t2horas` VARCHAR (8),`empresa` VARCHAR (50), `tecnico1` VARCHAR (50), `tecnico2` VARCHAR (50), `tecnico3` VARCHAR (50), `informe` TEXT,`observa` TEXT, `t1tipos` VARCHAR(10),`t2tipos` VARCHAR(10),`t1tipoe` VARCHAR(10),`t2tipoe` VARCHAR(10),`nombre` VARCHAR(60),PRIMARY KEY(`id`)) TYPE = MyISAM";
		var_dump($this->db->simple_query($mSQL));


		//./supervisor/tiketp.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `tiketp` (`id` TINYINT AUTO_INCREMENT, `estampa` TIMESTAMP, `fecha` DATE, `codigo` VARCHAR (20),`empresa` VARCHAR (100), `tiket` TEXT,`usuario` VARCHAR (20),`status` VARCHAR (20), `asignacion` VARCHAR (20),`nombre` VARCHAR (50),PRIMARY KEY(`id`)) TYPE = MyISAM";
		$this->db->simple_query($mSQL);


		//./supervisor/tiket.php 
		$mSQL="CREATE TABLE `tiket` (
		  `id` bigint(20) unsigned NOT NULL auto_increment,
		  `padre` char(1) default NULL,
		  `pertenece` bigint(20) unsigned default NULL,
		  `prioridad` smallint(5) unsigned default NULL,
		  `usuario` varchar(50) default NULL,
		  `contenido` text,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		  `actualizado` timestamp NULL default NULL,
		  `estado` char(1) default 'N',
		  PRIMARY KEY  (`id`),
		  KEY `id` (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./supervisor/tiketc.php 
		if(!$this->db->table_exists('tiketc')) {
			$mSQL="CREATE TABLE `tiketc` (
			  `id` bigint(20) unsigned NOT NULL auto_increment,
			  `padre` char(1) default NULL,
			  `pertenece` bigint(20) unsigned default NULL,
			  `prioridad` smallint(5) unsigned default NULL,
			  `usuario` varchar(50) default NULL,
			  `contenido` text,
			  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  `actualizado` timestamp NULL default NULL,
			  `estado` char(1) default 'N',
			  PRIMARY KEY  (`id`),
			  KEY `id` (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1"; 
			$mSQL2="CREATE TABLE `tiempo` (`hora` INT, `minutos` INT, `id` INT AUTO_INCREMENT, PRIMARY KEY(`id`), INDEX(`id`))";
			$this->db->simple_query($mSQL);
		}


		//./supervisor/muro.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `muro` (
		  `codigo` int(11) NOT NULL auto_increment,
		  `envia` varchar(15) default NULL,
		  `recibe` varchar(15) default NULL,
		  `mensaje` text,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		  PRIMARY KEY  (`codigo`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./supervisor/bitacora.php 
		$mSQL="CREATE TABLE `bitacora` (
		  `id` bigint(20) unsigned NOT NULL auto_increment,
		  `usuario` varchar(50) default NULL,
		  `nombre` varchar(100) default NULL,
		  `fecha` date default NULL,
		  `hora` time default NULL,
		  `actividad` text,
		  `comentario` text,
		  `revisado` char(1) default 'P',
		  `evaluacion` text,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=524 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./supervisor/usuarios.php 
		if ( !$this->datasis->iscampo('usuario','almacen') ) {
			$mSQL="ALTER TABLE `usuario`  ADD COLUMN `almacen` CHAR(4) NULL";
			$this->db->simple_query($mSQL);
			echo "Agregado campo almacen";
		}
		if ( !$this->datasis->iscampo('usuario','sucursal') ) {
			$mSQL="ALTER TABLE `usuario`  ADD COLUMN `sucursal` CHAR(2) NULL";
			$this->db->simple_query($mSQL);
			echo "Agregado campo sucursal";
		}
		if ( !$this->datasis->iscampo('usuario','activo') ) {
			$mSQL="ALTER TABLE `usuario`  ADD COLUMN `activo` CHAR(1) NULL";
			$this->db->simple_query($mSQL);
			echo "Agregado campo activo";
		}


		//./leche/lrece.php 
		if(!$this->db->table_exists('lrece')){
			$mSQL="CREATE TABLE `lrece` (
				`numero` CHAR(8) NULL DEFAULT NULL,
				`fecha` DATE NULL DEFAULT NULL,
				`transporte` INT(11) NULL DEFAULT NULL COMMENT 'Transporte',
				`ruta` CHAR(4) NULL DEFAULT NULL COMMENT 'Ruta Grupo de Proveedor',
				`flete` CHAR(5) NULL DEFAULT NULL COMMENT 'Proveedor Flete',
				`nombre` CHAR(45) NULL DEFAULT NULL COMMENT 'Nombre Chofer ',
				`lleno` DECIMAL(16,3) NULL DEFAULT NULL COMMENT 'Peso de la Unidad llena',
				`vacio` DECIMAL(16,3) NULL DEFAULT NULL COMMENT 'Peso de la Unidad Vacia',
				`neto` DECIMAL(16,3) NULL DEFAULT NULL COMMENT 'Neto lleno-vacio',
				`densidad` DECIMAL(10,5) NULL DEFAULT NULL COMMENT 'Densidad',
				`litros` DECIMAL(16,3) NULL DEFAULT NULL COMMENT 'Total Litros neto*densidad',
				`lista` DECIMAL(16,3) NULL DEFAULT NULL COMMENT 'Segun Lista',
				`diferen` DECIMAL(16,3) NULL DEFAULT NULL COMMENT 'Diferencia Neto/Lista',
				`animal` CHAR(1) NULL DEFAULT NULL COMMENT 'Vaca o Bufala',
				`crios` DECIMAL(10,3) NULL DEFAULT NULL COMMENT 'Crioscopia',
				`h2o` DECIMAL(10,3) NULL DEFAULT NULL COMMENT '% de Agua',
				`temp` DECIMAL(10,3) NULL DEFAULT NULL COMMENT 'Temperatura',
				`brix` DECIMAL(10,3) NULL DEFAULT NULL COMMENT 'Grados Brix',
				`grasa` DECIMAL(10,3) NULL DEFAULT NULL COMMENT '% Grasa',
				`acidez` DECIMAL(10,3) NULL DEFAULT NULL COMMENT 'Acidez',
				`cloruros` DECIMAL(10,3) NULL DEFAULT NULL COMMENT 'Cloruros',
				`dtoagua` DECIMAL(10,3) NULL DEFAULT NULL COMMENT 'Dto. Agua',
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `numero` (`numero`),
				INDEX `fecha` (`fecha`),
				INDEX `transporte` (`transporte`)
			)
			COMMENT='Recepcion de Leche'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('transporte', 'lrece')){
			$mSQL="ALTER TABLE `lrece` ADD COLUMN `transporte` INT(11) NULL DEFAULT NULL COMMENT 'Transporte' AFTER `fecha`, ADD INDEX `transporte` (`transporte`)";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('alcohol', 'itlrece')){
			$mSQL = "ALTER TABLE itlrece ADD COLUMN `alcohol` DECIMAL(10,3) NULL DEFAULT NULL COMMENT 'alcohol' AFTER `dtoagua`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('alcohol', 'lanal')){
			$mSQL = "ALTER TABLE lanal ADD COLUMN `alcohol` DECIMAL(10,3) NULL DEFAULT NULL COMMENT 'alcohol' AFTER `dtoagua`";
			$this->db->simple_query($mSQL);
		}


		if(!$this->db->table_exists('itlrece')){
			$mSQL="CREATE TABLE `itlrece` (
			`vaquera`  VARCHAR(5)    NULL DEFAULT NULL COMMENT 'Vaquera',
			`nombre`   VARCHAR(45)   NULL DEFAULT NULL COMMENT 'Productor ',
			`densidad` decimal(10,5) DEFAULT 1.032 COMMENT 'Densidad',
			`lista`    decimal(16,3) DEFAULT NULL COMMENT 'Segun Lista',
			`animal`   char(1)       DEFAULT NULL COMMENT 'Vaca o Bufala',
			`crios`    decimal(10,3) DEFAULT NULL COMMENT 'Crioscopia',
			`h2o`      decimal(10,3) DEFAULT NULL COMMENT '% de Agua',
			`temp`     decimal(10,3) DEFAULT NULL COMMENT 'Temperatura',
			`brix`     decimal(10,3) DEFAULT NULL COMMENT 'Grados Brix',
			`grasa`    decimal(10,3) DEFAULT NULL COMMENT '% Grasa',
			`acidez`   decimal(10,3) DEFAULT NULL COMMENT 'Acidez',
			`cloruros` decimal(10,3) DEFAULT NULL COMMENT 'Cloruros',
			`dtoagua`  decimal(10,3) DEFAULT NULL COMMENT 'Dto. Agua',
			`id_lrece` int(11)       DEFAULT NULL,
			`id_lvaca` int(11)       DEFAULT NULL,
			`id`       int(11)       NOT NULL AUTO_INCREMENT,
			PRIMARY KEY (`id`),
			KEY `id_lrece` (`id_lrece`),
			KEY `id_lvaca` (`id_lvaca`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Detalle Recepcion de Leche'";
			$this->db->simple_query($mSQL);
		}


		//./leche/lprod.php 
		if(!$this->db->table_exists('lprod')){
			$mSQL = "
			CREATE TABLE `lprod` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`descrip` VARCHAR(45) NULL DEFAULT NULL,
				`fecha` DATE NULL DEFAULT NULL,
				`peso` DECIMAL(12,2) NULL DEFAULT NULL,
				`litros` DECIMAL(12,2) NULL DEFAULT NULL,
				`inventario` DECIMAL(12,2) NULL DEFAULT NULL,
				`estampa` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				`usuario` VARCHAR(15) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COMMENT='Control de produccion de lacteos'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}


		if(!$this->db->table_exists('itlprod')){
			$mSQL = "
			CREATE TABLE `itlprod` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`id_lprod` INT(10) NOT NULL DEFAULT '0',
				`codrut` CHAR(4) NOT NULL DEFAULT '0',
				`nombre` VARCHAR(50) NOT NULL DEFAULT '0',
				`litros` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=0;";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('lcierre')){
			$mSQL = "CREATE TABLE `lcierre` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`fecha` DATE NULL DEFAULT NULL,
				`dia` VARCHAR(50) NULL DEFAULT NULL,
				`recepcion` DECIMAL(12,2) NULL DEFAULT NULL,
				`enfriamiento` DECIMAL(12,2) NULL DEFAULT NULL,
				`requeson` DECIMAL(12,2) NULL DEFAULT NULL,
				`requesonteorico` DECIMAL(12,2) NULL DEFAULT NULL,
				`requesonreal` DECIMAL(12,2) NULL DEFAULT NULL,
				`usuario` VARCHAR(50) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COMMENT='Cierre de produccion de lacteos'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('itlcierre')){
			$mSQL = "CREATE TABLE `itlcierre` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`id_lcierre` INT(10) NULL DEFAULT NULL,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`descrip` VARCHAR(45) NULL DEFAULT NULL,
				`unidades` DECIMAL(10,2) NULL DEFAULT NULL,
				`cestas` DECIMAL(10,2) NULL DEFAULT NULL,
				`peso` DECIMAL(10,2) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX `id_lcierre` (`id_lcierre`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}


		//./pos/zona.php 
		if (!$this->db->table_exists('zona')) {
			$mSQL="CREATE TABLE `zona` (
			  `codigo` varchar(8) NOT NULL DEFAULT '',
			  `nombre` varchar(30) DEFAULT NULL,
			  `descrip` varchar(90) DEFAULT NULL,
			  `margen` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
			  PRIMARY KEY (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}


		//./ia.php 
		if (!$this->db->table_exists('IA')) {
			$mSQL="CREATE TABLE `IA` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `nombre` varchar(100) NOT NULL,
			  `w` float NOT NULL DEFAULT '1',
			  `pos` int(10) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Pesos de las neuronas'";
			$this->db->simple_query($mSQL);
		}


		//./accesos.php 
		$fields = $this->db->field_data('intrasida','modulo');
		if($fields[1]->type!='string'){
			$mSQL="ALTER TABLE `intrasida`  CHANGE COLUMN `modulo` `modulo` VARCHAR(11) NOT NULL DEFAULT '0' AFTER `usuario`";
			$this->db->simple_query($mSQL);
		}


		//./supermercado/traer.php 
		$mSQL='CREATE TABLE `nfiscales` (
		  `maquina` char(12) default NULL,
		  `factura` char(8) default NULL,
		  `numero` char(8) default NULL,
		  `caja` char(5) NOT NULL default ,
		  `fecha` timestamp NULL default CURRENT_TIMESTAMP,
		  `id` int(11) unsigned NOT NULL auto_increment,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `Index 2` (`maquina`,`factura`)
		) ENGINE=MyISAM AUTO_INCREMENT=172 DEFAULT CHARSET=latin1';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `url` VARCHAR(200) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `proteo` VARCHAR(50) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `prefijo` CHAR(1) NULL';
		$this->db->simple_query($mSQL);


		//./supermercado/maes.php 
		$mSQL='ALTER TABLE `maes` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `maes` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE maes ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);

		$mSQL="CREATE TABLE IF NOT EXISTS `sinvcombo` (
		`combo` char(15) NOT NULL,
		`codigo` char(15) NOT NULL default '',
		`descrip` char(30) default NULL,
		`cantidad` decimal(10,3) default NULL,
		`precio` decimal(15,2) default NULL,
		`transac` char(8) default NULL,
		`estampa` date default NULL,
		`hora` char(8) default NULL,
		`usuario` char(12) default NULL,
		`costo` decimal(17,2) default '0.00',
		PRIMARY KEY  (`combo`,`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./supermercado/caja.php 
		$mSQL='ALTER TABLE `sucu` ADD `url` VARCHAR(200) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `prefijo` CHAR(1) NULL';
		$this->db->simple_query($mSQL);


		//./supermercado/efisico.php 
	    $mSQL="ALTER TABLE `maesfisico` ADD COLUMN `id` BIGINT NOT NULL AUTO_INCREMENT  FIRST , ADD PRIMARY KEY (`id`) ";
	    $this->db->simple_query($mSQL);


		//./supermercado/rivcdetal.php 
		if (!$this->db->table_exists('rivcdetal')) {
			$mSQL="CREATE TABLE `rivcdetal` (
				`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				`numero` CHAR(8) NULL DEFAULT NULL COMMENT 'Numero de la factura',
				`fecha` DATE NULL DEFAULT NULL COMMENT 'Fecha de la factura',
				`caja` VARCHAR(5) NULL DEFAULT NULL COMMENT 'Cajar de la factura',
				`cliente` VARCHAR(12) NULL DEFAULT NULL COMMENT 'Codigo de club',
				`nombre` VARCHAR(200) NULL DEFAULT NULL COMMENT 'Nombre del cliente',
				`emision` DATE NULL DEFAULT NULL COMMENT 'Emision del comprobante',
				`recepcion` DATE NULL DEFAULT NULL COMMENT 'Recepcion del comprobante',
				`monto` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Monto de la factura',
				`impuesto` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Impuesto de la factura',
				`reiva` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Cantidad retenida',
				`comprob` VARCHAR(8) NULL DEFAULT NULL COMMENT 'Numero de comprobante',
				`periodo` CHAR(8) NULL DEFAULT NULL,
				`transac` VARCHAR(8) NULL DEFAULT NULL,
				`usuario` VARCHAR(15) NULL DEFAULT NULL,
				`estampa` DATETIME NULL DEFAULT NULL,
				`hora` CHAR(8) NULL DEFAULT NULL,
				`origen` CHAR(1) NULL DEFAULT NULL,
				`codbanc` CHAR(2) NULL DEFAULT NULL,
				`numeroch` VARCHAR(12) NULL DEFAULT NULL,
				`anulado` CHAR(1) NULL DEFAULT 'N',
				PRIMARY KEY (`id`),
				INDEX `numero_fecha_caja` (`numero`, `fecha`, `caja`),
				INDEX `transac` (`transac`)
			)
			COMMENT='rivc de supermercado'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}


		//./supermercado/actlocali.php 
		$mSQL="
		CREATE PROCEDURE `sp_maes_actlocali`(IN `Numero` VARCHAR(50), IN `Locali` VARCHAR(50), IN `Oper` INT)  LANGUAGE SQL  NOT DETERMINISTIC  CONTAINS SQL  SQL SECURITY DEFINER  COMMENT '' BEGIN UPDATE maesfisico a JOIN ubic b ON a.codigo = b.codigo AND a.ubica=b.ubica SET b.locali=Locali WHERE a.numero=Numero AND ((a.fraccion>0)*(Oper=1)+(a.fraccion=0)*(Oper=0)+(a.fraccion>=0)*(Oper=2)); END;
		";
		var_dump($this->db->simple_query($mSQL));
		
		$mSQL="
		BEGIN
		DECLARE mALMA CHAR(4) ;
		DECLARE mFECHA DATE ;
		
		SELECT ubica FROM maesfisico WHERE numero=mNUMERO LIMIT 1  INTO mALMA;
		SELECT fecha FROM maesfisico WHERE numero=mNUMERO LIMIT 1 INTO mFECHA;
		
		INSERT INTO maesfisico 
		SELECT 0 id,codigo,mALMA,'2011',0,0,0,0,mFECHA, mNUMERO, 'BLANCO',CURDATE(),CURTIME() 
		FROM maes a WHERE (SELECT COUNT(*) FROM maesfisico b WHERE a.codigo=b.codigo AND b.numero=mNUMERO)=0;
		
		END
		";
		
		var_dump($this->db->simple_query($mSQL));
		


		//./hospitalidad/restaurantesan.php 
		$mSQL='ALTER TABLE `meso` DROP `clave`';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `meso` ADD `usuario` CHAR(12) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `meso` ADD UNIQUE `usuario` (`usuario`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `meso` ADD PRIMARY KEY (`mesonero`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `rfac` CHANGE `mesonero` `mesonero` CHAR(5) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `ritems` ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `ritems` ADD `impstatus` CHAR(1) DEFAULT "E" NULL AFTER `id`';
		$this->db->simple_query($mSQL);


		//./hospitalidad/restaurante.php 
		$mSQL='ALTER TABLE `meso` DROP `clave`';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `meso` ADD `usuario` CHAR(12) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `meso` ADD UNIQUE `usuario` (`usuario`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `meso` ADD PRIMARY KEY (`mesonero`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `rfac` CHANGE `mesonero` `mesonero` CHAR(5) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `ritems` ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `ritems` ADD `impstatus` CHAR(1) DEFAULT "E" NULL AFTER `id`';
		$this->db->simple_query($mSQL);


		//./hospitalidad/carta.php 
		$mSQL="ALTER TABLE `menu` ADD activard CHAR(5) DEFAULT '00:00'";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `menu` ADD activarh CHAR(5) DEFAULT '99:99'";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `menu` ADD activardia CHAR(7) DEFAULT '0123456'";
		$this->db->simple_query($mSQL);


		//./nomina/horarios.php 
		$query = "
			CREATE TABLE IF NOT EXISTS `horarios` (
			`id` INT(10) NOT NULL AUTO_INCREMENT,
			`codigo` CHAR(4) NULL DEFAULT NULL,
			`denomi` VARCHAR(50) NULL DEFAULT NULL,
			`turno` CHAR(1) NULL DEFAULT NULL,
			`entrada` CHAR(4) NULL DEFAULT NULL,
			`salida` CHAR(4) NULL DEFAULT NULL,
			`temporal` CHAR(4) NULL DEFAULT NULL,
			PRIMARY KEY (`id`)
		)
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT		
		";
		
		echo $this->db->simple_query($query);
		$query="ALTER TABLE `pers`  ADD COLUMN `horario` CHAR(4) NULL DEFAULT NULL";
		echo $this->db->simple_query($query);
	


		//./nomina/asig.php 
		$mSQL="ALTER TABLE asig ADD PRIMARY KEY (codigo);";
		$this->db->simple_query($mSQL);	


		//./nomina/ccarg.php 
		$mSQL = "ALTER TABLE carg ADD PRIMARY KEY (cargo);";
		$this->db->simple_query($mSQL);


		//./nomina/accesos.php 
		if (!$this->db->field_exists('manual', 'cacc')){
			$query="ALTER TABLE `cacc`  ADD COLUMN `manual` CHAR(1) NOT NULL DEFAULT 'N' AFTER `hora`";
			$this->db->simple_query($query);
		}

		if (!$this->db->table_exists('cerberus')){
			$mSQL="CREATE TABLE `cerberus` (
			`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			`ids` INT(10) UNSIGNED NOT NULL DEFAULT '0',
			`ip` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
			`usr` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
			`pwd` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
			`sid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
			`activo` CHAR(1) NOT NULL DEFAULT 'S',
			PRIMARY KEY (`id`)
			)
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}


		//./nomina/aumentosueldo.php 
		$mSQL="ALTER TABLE ausu ADD PRIMARY KEY (codigo,fecha);";
		$this->db->simple_query($mSQL);	


		//./nomina/noco.php 
		
		$sql="ALTER TABLE `noco`   ADD COLUMN 'id' INT(11) UNSIGNED NULL AUTO_INCREMENT AFTER observa2, DROP PRIMARY KEY, ADD UNIQUE INDEX codigo (codigo), ADD PRIMARY KEY (`id`)";
		$this->db->query($sql);	
		$sql="ALTER TABLE `itnoco` ADD COLUMN 'id' INT(11) UNSIGNED NULL AUTO_INCREMENT AFTER grupo, DROP PRIMARY KEY, ADD UNIQUE INDEX codigo (codigo, concepto), ADD PRIMARY KEY (`id`)";
		$this->db->query($sql);	



		//./nomina/pers.php 
		if ( !$this->datasis->iscampo('pers','email') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `email` VARCHAR(100) NULL");

		if ( !$this->datasis->iscampo('pers','tipoe') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `tipoe` VARCHAR(10)");

		if ( !$this->datasis->iscampo('pers','escritura') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `escritura` VARCHAR(25)");

		if ( !$this->datasis->iscampo('pers','rif') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `rif` VARCHAR(15)");
		
		if ( !$this->datasis->iscampo('pers','observa') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `observa` TEXT ");

		if ( !$this->datasis->iscampo('pers','turno') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `turno` CHAR(2) NULL");
			
		if ( !$this->datasis->iscampo('pers','horame') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `horame` VARCHAR(10)");

		if ( !$this->datasis->iscampo('pers','horams') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `horams` VARCHAR(10)");

		if ( !$this->datasis->iscampo('pers','horate') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `horate` VARCHAR(10)");

		if ( !$this->datasis->iscampo('pers','horats') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `horats` VARCHAR(10)");

		if ( !$this->datasis->iscampo('pers','modificado') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER vence");

		if ( !$this->datasis->iscampo('pers','id') )
			$this->db->simple_query("ALTER TABLE pers ADD COLUMN `id` INT(11) NULL AUTO_INCREMENT AFTER modificado, DROP PRIMARY KEY, ADD PRIMARY (id), ADD UNIQUE INDEX codigo (codigo)");

		if ( !$this->datasis->istabla('tipot') )
			$this->db->simple_query("CREATE TABLE tipot (codigo int(10) unsigned NOT NULL AUTO_INCREMENT,tipo varchar(50) DEFAULT NULL,PRIMARY KEY (codigo) )");
			
		if ( !$this->datasis->istabla('posicion') )
			$this->db->simple_query("CREATE TABLE `posicion`(`codigo` varchar(10) NOT NULL,`posicion` varchar(30) DEFAULT NULL,PRIMARY KEY (`codigo`))");
			
		if ( !$this->datasis->istabla('posicion') )
			$this->db->simple_query("CREATE TABLE tipoe (codigo varchar(10) NOT NULL DEFAULT '', tipo varchar(50) DEFAULT NULL, PRIMARY KEY (codigo))"); 

		if ( !$this->datasis->istabla('nedu') ){
			$this->db->simple_query("CREATE TABLE IF NOT EXISTS nedu (codigo varchar(4) NOT NULL, nivel varchar(40) DEFAULT NULL, PRIMARY KEY (`codigo`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC");
			$this->db->simple_query("INSERT INTO nedu (codigo, nivel) VALUES ('00', 'Sin Educacion Formal'),('01', 'Primaria'),('02', 'Secundaria'),('03', 'Tecnico'),	('04', 'T.S.U.'),('05', 'Universitario'),('06', 'Post Universitario'),('07', 'Doctor'),('08', 'Guru')");
		}


		//./nomina/conc.php 
		$mSQL="ALTER TABLE conc ADD PRIMARY KEY (concepto);";
		$this->db->simple_query($mSQL);	


		//./nomina/ausu.php 
		$mSQL="ALTER TABLE ausu ADD PRIMARY KEY (codigo,fecha);";
		$this->db->simple_query($mSQL);	


		//./farmacia/sinv.php 
		$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `sinv`  ADD COLUMN `descufijo` DECIMAL(6,3) NULL DEFAULT '0.000' AFTER `id`";
		$this->db->simple_query($mSQL);

		$mSQL="CREATE TABLE IF NOT EXISTS `sinvcombo` (
		`combo` char(15) NOT NULL,
		`codigo` char(15) NOT NULL default '',
		`descrip` char(30) default NULL,
		`cantidad` decimal(10,3) default NULL,
		`precio` decimal(15,2) default NULL,
		`transac` char(8) default NULL,
		`estampa` date default NULL,
		`hora` char(8) default NULL,
		`usuario` char(12) default NULL,
		`costo` decimal(17,2) default '0.00',
		PRIMARY KEY  (`combo`,`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./farmacia/scst.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `farmaxasig` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`proveed` VARCHAR(5) NOT NULL,
		`barras` VARCHAR(20) NOT NULL,
		`abarras` VARCHAR(12) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE INDEX `proveed` (`proveed`, `barras`)
		)
		COMMENT='Tabla de equivalencias de productos'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";
		var_dump($this->db->simple_query($mSQL));

		$mSQL="ALTER TABLE `farmaxasig`  CHANGE COLUMN `barras` `barras` VARCHAR(20) NOT NULL COLLATE 'latin1_general_ci' AFTER `proveed`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `farmaxasig`  CHANGE COLUMN `abarras` `abarras` VARCHAR(20) NOT NULL COLLATE 'latin1_general_ci' AFTER `barras`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `farmaxasig`  CHANGE COLUMN `proveed` `proveed` VARCHAR(5) NOT NULL COLLATE 'utf8_unicode_ci' AFTER `id`";
		var_dump($this->db->simple_query($mSQL));
		$mSQL="ALTER TABLE `farmaxasig`  COLLATE='latin1_general_ci',  CONVERT TO CHARSET latin1";
		var_dump($this->db->simple_query($mSQL));


		//./farmacia/fallaped.php 
		if (!$this->db->table_exists('fallaped')) {
			$mSQL="CREATE TABLE `fallaped` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `codigo` varchar(15) DEFAULT NULL,
			  `barras` varchar(15) DEFAULT NULL,
			  `descrip` varchar(45) DEFAULT NULL,
			  `cana` int(11) DEFAULT NULL,
			  `ventas` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `codigo` (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='pedidos a droguerias por fallas'";
			$this->db->simple_query($mSQL);
		}


		//./farmacia/pedidos.php 
		if(!$this->db->table_exists('fallaped')){
			$mSQL="CREATE TABLE `fallaped` (
				`id` INT(10) NULL AUTO_INCREMENT,
				`codigo` VARCHAR(15) NULL,
				`barras` VARCHAR(15) NULL,
				`descrip` VARCHAR(45) NULL,
				`cana` INT(11) NULL,
				`ventas` INT(11) NULL,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `codigo` (`codigo`)
			)
			COMMENT='pedidos a droguerias por fallas'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}


		//./farmacia/sscst.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `farmaxasig` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`proveed` VARCHAR(50) NOT NULL,
		`barras` VARCHAR(250) NOT NULL,
		`abarras` VARCHAR(250) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE INDEX `proveed` (`proveed`, `barras`)
		)
		COMMENT='Tabla de equivalencias de productos'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";

		$this->db->simple_query($mSQL);


		//./farmacia/gpt_pro.php 
		if (!$this->db->table_exists('gpt_pro')) {
			$mSQL="CREATE TABLE `gpt_pro` (`id_pro` INT(10) NOT NULL DEFAULT '0',`n1_pro` VARCHAR(2) NULL DEFAULT NULL,`n2_pro` VARCHAR(6) NULL DEFAULT NULL,`n3_pro` VARCHAR(8) NULL DEFAULT NULL,`n4_pro` VARCHAR(12) NULL DEFAULT NULL,`n5_pro` VARCHAR(16) NULL DEFAULT NULL,`nom_pro` VARCHAR(200) NULL DEFAULT NULL,`pres_pro` VARCHAR(200) NULL DEFAULT NULL,`lab_pro` VARCHAR(200) NULL DEFAULT NULL,`cod_pro` VARCHAR(200) NULL DEFAULT NULL,
			`gen_pro` VARCHAR(300) NULL DEFAULT NULL,
			`mono_pro` LONGTEXT NULL,
			`logo_pro` VARCHAR(100) NULL DEFAULT NULL,
			PRIMARY KEY (`id_pro`),
			FULLTEXT INDEX `nom_pro_pres_pro_lab_pro_gen_pro` (`nom_pro`, `pres_pro`, `lab_pro`, `gen_pro`)
			)
			COMMENT='Guia Medica de Productos'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}

		//$mSQL="ALTER TABLE `gpt_pro`  DROP INDEX `nom_pro_pres_pro_lab_pro_gen_pro`,  ADD FULLTEXT INDEX `nom_pro_pres_pro_lab_pro_gen_pro_mono_pro` (`nom_pro`, `pres_pro`, `lab_pro`, `gen_pro`, `mono_pro`)";
		//$this->db->simple_query($mSQL);


		//./taller/stal.php 
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


		//./taller/tecn.php 
		if (!$this->db->table_exists('tecn')) {
			$mSQL="CREATE TABLE `tecn` (
			  `codigo` varchar(5) NOT NULL DEFAULT '',
			  `clave` varchar(5) DEFAULT NULL,
			  `nombre` varchar(30) DEFAULT NULL,
			  `direc1` varchar(35) DEFAULT NULL,
			  `direc2` varchar(35) DEFAULT NULL,
			  `telefono` varchar(13) DEFAULT NULL,
			  `comive` decimal(5,2) DEFAULT NULL,
			  `comicob` decimal(5,2) DEFAULT NULL,
			  `recargo` decimal(5,2) DEFAULT NULL,
			  `tipo` char(1) DEFAULT NULL,
			  `almacen` varchar(4) DEFAULT NULL,
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `codigo` (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}


		//./taller/recep.php 
		if (!$this->db->table_exists('recep')) {
			$mSQL="CREATE TABLE `recep` (
			  `recep` char(8) NOT NULL DEFAULT '',
			  `fecha` date DEFAULT NULL,
			  `clipro` varchar(5) DEFAULT NULL,
			  `refe` char(8) DEFAULT NULL,
			  `tipo` char(2) DEFAULT NULL,
			  `observa` text,
			  `status` char(2) DEFAULT NULL,
			  `user` varchar(50) DEFAULT NULL,
			  `estampa` timestamp NULL DEFAULT NULL,
			  `origen` varchar(20) DEFAULT NULL,
			  PRIMARY KEY (`recep`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}
		$this->db->simple_query("ALTER TABLE `recep`  ADD COLUMN `nombre` VARCHAR(50) NULL DEFAULT NULL");


		//./desarrollo.php 
		$crud.="\t\t".'if (!$this->db->table_exists(\''.$tabla.'\')) {'."\n";
		$crud.="\t\t\t".'$mSQL="'.str_replace("\n","\n\t\t\t",$row['Create Table']).'";'."\n";
		$crud.="\t\t\t".'$this->db->simple_query($mSQL);'."\n";
		$crud.="\t\t".'}'."\n";


		//./finanzas/libros_samy.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `libros` (
		  `metodo` varchar(50) NOT NULL default '',
		  `nombre` varchar(150) default NULL,
		  `activo` char(1) default NULL,
		  `tipo` char(1) default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `fgenera` char(6) default NULL,
		  PRIMARY KEY  (`metodo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";   
		$this->db->simple_query($mSQL);
		
		$mSQL="TRUNCATE libros";
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` ADD `hora` TIME DEFAULT '0' NULL";
		$this->db->simple_query($mSQL);
		
		
		
		$mSQL="ALTER TABLE `siva` CHANGE `clipro` `clipro` VARCHAR(12) NULL";
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` ADD `serial` CHAR(12) NULL";
		$this->db->simple_query($mSQL);
		
		$data[]=array('metodo'=>'wlvexcelpdv'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV'      );
		$data[]=array('metodo'=>'wlvexcelpdvq1'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1');
		$data[]=array('metodo'=>'wlvexcelpdvq2'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal'  ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal2' ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal V2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq1','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quinceta 1 Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq2','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quinceta 2 Fiscal');
		$data[]=array('metodo'=>'wlvexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas'          );
		$data[]=array('metodo'=>'wlvexcelsucu'       ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas por Sucursal');
		$data[]=array('metodo'=>'wlcexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras'         );
		$data[]=array('metodo'=>'wlcsexcel'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras Supermercado');
		$data[]=array('metodo'=>'wlvexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas  ESPECIAL');
		$data[]=array('metodo'=>'wlcexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras ESPECIAL');
		$data[]=array('metodo'=>'prorrata'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Prorrata'                 );
		$data[]=array('metodo'=>'invresu'            ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Inventario'      );
		
		$data[]=array('metodo'=>'genecompras'        ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras COMPRAS' );
		$data[]=array('metodo'=>'genegastos'         ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras GASTOS'  );
		$data[]=array('metodo'=>'genecxp'            ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras CXP'     );
		$data[]=array('metodo'=>'genesfac'           ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas' );
		$data[]=array('metodo'=>'geneventasfiscalpdv','activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Fiscal PDV'   );
		$data[]=array('metodo'=>'genesfmay'          ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas al mayor' );
		$data[]=array('metodo'=>'genesmov'           ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas CXC'      );
		$data[]=array('metodo'=>'geneotin'           ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas O.Ingresos');
		$data[]=array('metodo'=>'generest'           ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Restaurante');
		$data[]=array('metodo'=>'genehotel'          ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Hotel');
		
		foreach($data AS $algo){
			$mSQL = $this->db->insert_string('libros', $algo);
			$this->db->simple_query($mSQL);
		}
		$this->db->simple_query($mSQL);
		echo $uri = anchor('finanzas/libros/configurar','Configurar');


		//./finanzas/gestion.php 
		if (!$this->db->table_exists('gestion_indicador')) {
			$mSQL="CREATE TABLE `gestion_indicador` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `id_gestion_grupo` int(10) DEFAULT '0',
			  `unidad` char(8) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `descrip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `indicador` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `puntos` int(11) DEFAULT NULL,
			  `objetivo` decimal(12,2) DEFAULT NULL,
			  `ejecuta` longtext COLLATE utf8_unicode_ci,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Guarda los indicadores de gestion'";
			$this->db->simple_query($mSQL);
		}


		//./finanzas/libros_calore.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `libros` (
		  `metodo` varchar(50) NOT NULL default '',
		  `nombre` varchar(150) default NULL,
		  `activo` char(1) default NULL,
		  `tipo` char(1) default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `fgenera` char(6) default NULL,
		  PRIMARY KEY  (`metodo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";   
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` ADD `hora` TIME DEFAULT '0' NULL";
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` CHANGE `clipro` `clipro` VARCHAR(12) NULL";
		$this->db->simple_query($mSQL);
		
		$data[]=array('metodo'=>'wlvexcelpdv'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV'      );
		$data[]=array('metodo'=>'wlvexcelpdvq1'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1');
		$data[]=array('metodo'=>'wlvexcelpdvq2'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal'  ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq1','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1 Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq2','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2 Fiscal');
		$data[]=array('metodo'=>'wlvexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas'          );
		$data[]=array('metodo'=>'wlvexcelsucu'       ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas por Sucursal');
		$data[]=array('metodo'=>'wlcexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras'         );
		$data[]=array('metodo'=>'wlcsexcel'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras Supermercado');
		$data[]=array('metodo'=>'wlvexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas  ESPECIAL');
		$data[]=array('metodo'=>'wlcexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras ESPECIAL');
		$data[]=array('metodo'=>'prorrata'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Prorrata'                 );
		$data[]=array('metodo'=>'invresu'            ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Inventario'      );
		
		$data[]=array('metodo'=>'genecompras'     ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras COMPRAS' );
		$data[]=array('metodo'=>'genegastos'      ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras GASTOS'  );
		$data[]=array('metodo'=>'genecxp'         ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras CXP'     );
		$data[]=array('metodo'=>'genesfac'        ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas' );
		$data[]=array('metodo'=>'geneventasfiscal','activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Fiscal PDV'   );
		$data[]=array('metodo'=>'genesfmay'       ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas al mayor' );
		$data[]=array('metodo'=>'genesmov'        ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas CXC'      );
		$data[]=array('metodo'=>'geneotin'        ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas O.Ingresos');
		$data[]=array('metodo'=>'generest'        ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Restaurante');
		$data[]=array('metodo'=>'genehotel'       ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Hotel');
		
		foreach($data AS $algo){
			$mSQL = $this->db->insert_string('libros', $algo);
			$this->db->simple_query($mSQL);
		}
		$this->db->simple_query($mSQL);
		echo $uri = anchor('finanzas/libros_calore/configurar','Configurar');


		//./finanzas/gsercolsan.php 
		$query="SHOW INDEX FROM gser";
		$resul=$this->db->query($query);
		$existe=0;
		foreach($resul->result() as $ind){
			$nom= $ind->Column_name;
			if ($nom == 'id'){
				$existe=1;
				break;
			}
		}

		if($existe != 1) {
			$query="ALTER TABLE `gser` DROP PRIMARY KEY";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD UNIQUE INDEX `gser` (`fecha`, `numero`, `proveed`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `ncausado`,  ADD PRIMARY KEY (`id`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gitser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `reteica`,  ADD PRIMARY KEY (`id`);";
			$this->db->simple_query($query);
			$query="ALTER TABLE `gitser` ADD COLUMN `idgser` INT(15) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`, ADD INDEX `idgser` (`idgser`)";
			$this->db->simple_query($query);

			$query="UPDATE gitser AS a
				JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
				SET a.idgser=b.id";
			$this->db->simple_query($query);
		}

		$query="UPDATE gitser AS a
			JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
			SET a.idgser=b.id";
		$this->db->simple_query($query);


		if (!$this->db->table_exists('gserchi')) {
			$query="CREATE TABLE IF NOT EXISTS `gserchi` (
				`codbanc` varchar(5) NOT NULL DEFAULT '', 
				`fechafac` date DEFAULT NULL, 
				`numfac` varchar(8) DEFAULT NULL, 
				`nfiscal` varchar(12) DEFAULT NULL, 
				`rif` varchar(13) DEFAULT NULL, 
				`proveedor` varchar(40) DEFAULT NULL, 
				`codigo` varchar(6) DEFAULT NULL, 
				`descrip` varchar(50) DEFAULT NULL, 
				`moneda` char(2) DEFAULT NULL, 
				`montasa` decimal(17,2) DEFAULT '0.00', 
				`tasa` decimal(17,2) DEFAULT NULL, 
				`monredu` decimal(17,2) DEFAULT '0.00', 
				`reducida` decimal(17,2) DEFAULT NULL, 
				`monadic` decimal(17,2) DEFAULT '0.00', 
				`sobretasa` decimal(17,2) DEFAULT NULL, 
				`exento` decimal(17,2) DEFAULT '0.00', 
				`importe` decimal(12,2) DEFAULT NULL, 
				`sucursal` char(2) DEFAULT NULL, 
				`departa` char(2) DEFAULT NULL, 
				`usuario` varchar(12) DEFAULT NULL, 
				`estampa` date DEFAULT NULL, 
				`hora` varchar(8) DEFAULT NULL, 
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
				PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($query);
		}

		if (!$this->db->table_exists('rica')) {
			$query="CREATE TABLE `rica` (
				`codigo` CHAR(5)    NOT  NULL,
				`activi` CHAR(14)   NULL DEFAULT NULL,
				`aplica` CHAR(100)  NULL DEFAULT NULL,
				`tasa` DECIMAL(8,2) NULL DEFAULT NULL,
				PRIMARY KEY (`codigo`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($query);
		}

		
		if (!$this->db->field_exists('ngasto','gserchi')) {
			$query="ALTER TABLE `gserchi` ADD COLUMN `ngasto` VARCHAR(8) NULL DEFAULT NULL AFTER `departa`";
			$this->db->simple_query($query);
		}

		if (!$this->db->field_exists('aceptado','gserchi')) {
			$query="ALTER TABLE gserchi ADD COLUMN aceptado CHAR(1) NULL DEFAULT NULL";
			$this->db->simple_query($query);
		}

		
		if (!$this->db->table_exists('gereten')) {
			$query="CREATE TABLE `gereten` (
				`id` INT(10) NOT NULL DEFAULT '0',
				`idd` INT(11) NULL DEFAULT NULL,
				`origen` CHAR(4) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`numero` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`codigorete` VARCHAR(4) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`actividad` VARCHAR(45) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`base` DECIMAL(10,2) NULL DEFAULT NULL,
				`porcen` DECIMAL(5,2) NULL DEFAULT NULL,
				`monto` DECIMAL(10,2) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($query);
		}
		


		//./finanzas/cuenco.php 
		$mSQL="CREATE TABLE `cuenco` (`id` BIGINT AUTO_INCREMENT, `cliente` VARCHAR (20), `tipo` VARCHAR (10), `numero` VARCHAR (12), `fecha` DATE, `vence` DATE, `monto` DECIMAL (17), PRIMARY KEY(`id`))";
		$this->db->simple_query($mSQL);


		//./finanzas/agregareg.php 
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);


		//./finanzas/smov.php 
		$campos=$this->db->list_fields('smov');
		if (!in_array('id',$campos)){
			$mSQL="ALTER TABLE `smov`
				ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT,
				DROP PRIMARY KEY,
				ADD UNIQUE INDEX `unic` (`cod_cli`, `tipo_doc`, `numero`, `fecha`),
				ADD PRIMARY KEY (`id`)";
			$this->db->simple_query();
		}


		//./finanzas/retecol.php 
		if (!$this->db->field_exists('tipocol','mgas')) {
			$mSQL='';
			$this->db->simple_query($mSQL);
		}


		//./finanzas/libros_respaldo.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `libros` (
		  `metodo` varchar(50) NOT NULL default '',
		  `nombre` varchar(150) default NULL,
		  `activo` char(1) default NULL,
		  `tipo` char(1) default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `fgenera` char(6) default NULL,
		  PRIMARY KEY  (`metodo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";   
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` ADD `hora` TIME DEFAULT '0' NULL";
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` CHANGE `clipro` `clipro` VARCHAR(12) NULL";
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` ADD `serial` CHAR(12) NULL";
		$this->db->simple_query($mSQL);
		
		$data[]=array('metodo'=>'wlvexcelpdv'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV'      );
		$data[]=array('metodo'=>'wlvexcelpdvq1'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1');
		$data[]=array('metodo'=>'wlvexcelpdvq2'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal'  ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal2' ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal V2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq1','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1 Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq2','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2 Fiscal');
		$data[]=array('metodo'=>'wlvexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas'          );
		$data[]=array('metodo'=>'wlvexcelsucu'       ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas por Sucursal');
		$data[]=array('metodo'=>'wlcexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras'         );
		$data[]=array('metodo'=>'wlcsexcel'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras Supermercado');
		$data[]=array('metodo'=>'wlvexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas  ESPECIAL');
		$data[]=array('metodo'=>'wlcexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras ESPECIAL');
		$data[]=array('metodo'=>'prorrata'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Prorrata'                 );
		$data[]=array('metodo'=>'invresu'            ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Inventario'      );
		
		$data[]=array('metodo'=>'genecompras'        ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras COMPRAS' );
		$data[]=array('metodo'=>'genegastos'         ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras GASTOS'  );
		$data[]=array('metodo'=>'genecxp'            ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras CXP'     );
		$data[]=array('metodo'=>'genesfac'           ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas' );
		$data[]=array('metodo'=>'geneventasfiscalpdv','activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Fiscal PDV'   );
		$data[]=array('metodo'=>'genesfmay'          ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas al mayor' );
		$data[]=array('metodo'=>'genesmov'           ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas CXC'      );
		$data[]=array('metodo'=>'geneotin'           ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas O.Ingresos');
		$data[]=array('metodo'=>'generest'           ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Restaurante');
		$data[]=array('metodo'=>'genehotel'          ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Hotel');
		
		foreach($data AS $algo){
			$mSQL = $this->db->insert_string('libros', $algo);
			$this->db->simple_query($mSQL);
		}
		$this->db->simple_query($mSQL);
		echo $uri = anchor('finanzas/libros/configurar','Configurar');


		//./finanzas/tban.php 
		$query="ALTER TABLE `tban`  ADD COLUMN `formacheque` VARCHAR(50) NULL DEFAULT 'CHEQUE'	";
		$this->db->simple_query($query);


		//./finanzas/riva.php 
		$mSQL="CREATE TABLE `riva` (
		  `nrocomp` char(8) NOT NULL DEFAULT '',
		  `emision` date DEFAULT NULL,
		  `periodo` char(8) DEFAULT NULL,
		  `tipo_doc` char(2) DEFAULT NULL,
		  `fecha` date DEFAULT NULL,
		  `numero` char(12) DEFAULT NULL,
		  `nfiscal` char(12) DEFAULT NULL,
		  `afecta` char(8) DEFAULT NULL,
		  `clipro` char(5) DEFAULT NULL,
		  `nombre` char(40) DEFAULT NULL,
		  `rif` char(14) DEFAULT NULL,
		  `exento` decimal(15,2) DEFAULT NULL,
		  `tasa` decimal(5,2) DEFAULT NULL,
		  `general` decimal(15,2) DEFAULT NULL,
		  `geneimpu` decimal(15,2) DEFAULT NULL,
		  `tasaadic` decimal(5,2) DEFAULT NULL,
		  `adicional` decimal(15,2) DEFAULT NULL,
		  `adicimpu` decimal(15,2) DEFAULT NULL,
		  `tasaredu` decimal(5,2) DEFAULT NULL,
		  `reducida` decimal(15,2) DEFAULT NULL,
		  `reduimpu` decimal(15,2) DEFAULT NULL,
		  `stotal` decimal(15,2) DEFAULT NULL,
		  `impuesto` decimal(15,2) DEFAULT NULL,
		  `gtotal` decimal(15,2) DEFAULT NULL,
		  `reiva` decimal(15,2) DEFAULT NULL,
		  `transac` char(8) DEFAULT NULL,
		  `estampa` date DEFAULT NULL,
		  `hora` char(8) DEFAULT NULL,
		  `usuario` char(12) DEFAULT NULL,
		  `ffactura` date DEFAULT '0000-00-00',
		  PRIMARY KEY (`nrocomp`),
		  UNIQUE KEY `rivatra` (`transac`),
		  KEY `Numero` (`numero`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./finanzas/ggser.php 
		$query="SHOW INDEX FROM gser";
		$resul=$this->db->query($query);
		$existe=0;
		foreach($resul->result() as $ind){
			$nom= $ind->Column_name;
			if ($nom == 'id'){
				$existe=1;
				break;
			}
		}
		if($existe != 1){
			$query="ALTER TABLE `gser` DROP PRIMARY KEY";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD UNIQUE INDEX `gser` (`fecha`, `numero`, `proveed`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `ncausado`,  ADD PRIMARY KEY (`id`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gitser` ADD COLUMN `idgser` INT(15) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`, ADD INDEX `idgser` (`idgser`)";
			var_dump($this->db->simple_query($query));

			$query="UPDATE gitser AS a
					JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
					SET a.idgser=b.id";
			var_dump($this->db->simple_query($query));

			//$query="ALTER TABLE `gitser`  ADD COLUMN `tasaiva` DECIMAL(7,2) UNSIGNED NOT NULL DEFAULT '0' AFTER `idgser`;";
			//$this->db->simple_query($query);
		}



		//./finanzas/sfpach.php 
	


		//./finanzas/mgascol.php 
		if (!$this->db->field_exists('reten','mgas')) {
			$mSQL="ALTER TABLE mgas ADD COLUMN reten VARCHAR(4) NULL DEFAULT NULL AFTER rica, ADD COLUMN retej VARCHAR(4) NULL DEFAULT NULL AFTER reten";
			$this->db->simple_query($mSQL);
		}


		//./finanzas/rivc.php 
		if (!$this->db->table_exists('rivc')) {
			$mSQL="CREATE TABLE `rivc` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`nrocomp` varchar(8) NOT NULL DEFAULT '',
			`emision` date DEFAULT NULL,
			`periodo` char(8) DEFAULT NULL,
			`fecha` date DEFAULT NULL,
			`cod_cli` varchar(5) DEFAULT NULL,
			`nombre` varchar(200) DEFAULT NULL,
			`rif` varchar(14) DEFAULT NULL,
			`exento` decimal(15,2) DEFAULT NULL,
			`tasa` decimal(5,2) DEFAULT NULL,
			`general` decimal(15,2) DEFAULT NULL,
			`geneimpu` decimal(15,2) DEFAULT NULL,
			`tasaadic` decimal(5,2) DEFAULT NULL,
			`adicional` decimal(15,2) DEFAULT NULL,
			`adicimpu` decimal(15,2) DEFAULT NULL,
			`tasaredu` decimal(5,2) DEFAULT NULL,
			`reducida` decimal(15,2) DEFAULT NULL,
			`reduimpu` decimal(15,2) DEFAULT NULL,
			`stotal` decimal(15,2) DEFAULT NULL,
			`impuesto` decimal(15,2) DEFAULT NULL,
			`gtotal` decimal(15,2) DEFAULT NULL,
			`reiva` decimal(15,2) DEFAULT NULL,
			`estampa` date DEFAULT NULL,
			`hora` char(8) DEFAULT NULL,
			`usuario` varchar(12) DEFAULT NULL,
			`modificado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`transac` varchar(8) DEFAULT NULL,
			`origen` char(1) DEFAULT NULL,
			`codbanc` char(2) DEFAULT NULL,
			`tipo_op` char(2) DEFAULT NULL,
			`numche` varchar(12) DEFAULT NULL,
			`sprmreinte` varchar(8) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `modificado` (`modificado`),
			KEY `nrocomp_cod_cli` (`nrocomp`,`cod_cli`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('operacion', 'rivc')){
			$mSQL="ALTER TABLE rivc ADD COLUMN operacion CHAR(1) NOT NULL AFTER sprmreinte";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('anulado', 'rivc')){
			$mSQL="ALTER TABLE `rivc`  ADD COLUMN `anulado` CHAR(1) NULL DEFAULT 'N' AFTER `operacion`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('cajero', 'rivc')){
			$mSQL="ALTER TABLE rivc ADD COLUMN cajero VARCHAR(5) NULL DEFAULT NULL AFTER sprmreinte";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('itrivc')) {
			$mSQL="CREATE TABLE `itrivc` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`idrivc` int(6) DEFAULT NULL,
			`tipo_doc` char(2) DEFAULT NULL,
			`fecha` date DEFAULT NULL,
			`numero` varchar(8) DEFAULT NULL,
			`nfiscal` char(12) DEFAULT NULL,
			`exento` decimal(15,2) DEFAULT NULL,
			`tasa` decimal(5,2) DEFAULT NULL,
			`general` decimal(15,2) DEFAULT NULL,
			`geneimpu` decimal(15,2) DEFAULT NULL,
			`tasaadic` decimal(5,2) DEFAULT NULL,
			`adicional` decimal(15,2) DEFAULT NULL,
			`adicimpu` decimal(15,2) DEFAULT NULL,
			`tasaredu` decimal(5,2) DEFAULT NULL,
			`reducida` decimal(15,2) DEFAULT NULL,
			`reduimpu` decimal(15,2) DEFAULT NULL,
			`stotal` decimal(15,2) DEFAULT NULL,
			`impuesto` decimal(15,2) DEFAULT NULL,
			`gtotal` decimal(15,2) DEFAULT NULL,
			`reiva` decimal(15,2) DEFAULT NULL,
			`transac` char(8) DEFAULT NULL,
			`estampa` date DEFAULT NULL,
			`hora` char(8) DEFAULT NULL,
			`usuario` char(12) DEFAULT NULL,
			`ffactura` date DEFAULT '0000-00-00',
			`modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `tipo_doc_numero` (`tipo_doc`,`numero`),
			KEY `Numero` (`numero`),
			KEY `modificado` (`modificado`),
			KEY `rivatra` (`transac`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}


		//./finanzas/libros_25.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `libros` (
		  `metodo` varchar(50) NOT NULL default '',
		  `nombre` varchar(150) default NULL,
		  `activo` char(1) default NULL,
		  `tipo` char(1) default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `fgenera` char(6) default NULL,
		  PRIMARY KEY  (`metodo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";   
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` ADD `hora` TIME DEFAULT '0' NULL";
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` CHANGE `clipro` `clipro` VARCHAR(12) NULL";
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` ADD `serial` CHAR(12) NULL";
		$this->db->simple_query($mSQL);
		
		$data[]=array('metodo'=>'wlvexcelpdv'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV'      );
		$data[]=array('metodo'=>'wlvexcelpdvq1'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1');
		$data[]=array('metodo'=>'wlvexcelpdvq2'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal'  ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal2' ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal V2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq1','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1 Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq2','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2 Fiscal');
		$data[]=array('metodo'=>'wlvexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas Agrupados'          );
		$data[]=array('metodo'=>'wlvexcelnogroup'    ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas No Agrupados'          );
		$data[]=array('metodo'=>'wlvexcelsucu'       ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas por Sucursal');
		$data[]=array('metodo'=>'wlcexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras'         );
		$data[]=array('metodo'=>'wlcsexcel'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras Supermercado');
		$data[]=array('metodo'=>'wlvexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas  ESPECIAL');
		$data[]=array('metodo'=>'wlcexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras ESPECIAL');
		$data[]=array('metodo'=>'prorrata'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Prorrata'                 );
		$data[]=array('metodo'=>'invresu'            ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Inventario'      );
		
		$data[]=array('metodo'=>'genecompras'        ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras COMPRAS' );
		$data[]=array('metodo'=>'genegastos'         ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras GASTOS'  );
		$data[]=array('metodo'=>'genecxp'            ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras CXP'     );
		$data[]=array('metodo'=>'genesfac'           ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas' );
		$data[]=array('metodo'=>'geneventasfiscalpdv','activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Fiscal PDV'   );
		$data[]=array('metodo'=>'genesfmay'          ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas al mayor' );
		$data[]=array('metodo'=>'genesmov'           ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas CXC'      );
		$data[]=array('metodo'=>'geneotin'           ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas O.Ingresos');
		$data[]=array('metodo'=>'generest'           ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Restaurante');
		$data[]=array('metodo'=>'genehotel'          ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Hotel');
		
		foreach($data AS $algo){
			$mSQL = $this->db->insert_string('libros', $algo);
			$this->db->simple_query($mSQL);
		}
		$this->db->simple_query($mSQL);
		echo $uri = anchor('finanzas/libros/configurar','Configurar');


		//./finanzas/gser.php 
		$query="SHOW INDEX FROM gser";
		$resul=$this->db->query($query);
		$existe=0;
		foreach($resul->result() as $ind){
			$nom= $ind->Column_name;
			if ($nom == 'id'){
				$existe=1;
				break;
			}
		}

		if($existe != 1) {
			$query="ALTER TABLE `gser` DROP PRIMARY KEY";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD UNIQUE INDEX `gser` (`fecha`, `numero`, `proveed`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `ncausado`,  ADD PRIMARY KEY (`id`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gitser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `reteica`,  ADD PRIMARY KEY (`id`);";
			$this->db->simple_query($query);
			$query="ALTER TABLE `gitser` ADD COLUMN `idgser` INT(15) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`, ADD INDEX `idgser` (`idgser`)";
			$this->db->simple_query($query);

			$query="UPDATE gitser AS a
				JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
				SET a.idgser=b.id";
			$this->db->simple_query($query);
		}

		$query="UPDATE gitser AS a
			JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
			SET a.idgser=b.id";
		$this->db->simple_query($query);


		if (!$this->db->table_exists('gserchi')) {
			$query="CREATE TABLE IF NOT EXISTS `gserchi` (
				`codbanc` varchar(5) NOT NULL DEFAULT '',
				`fechafac` date DEFAULT NULL,
				`numfac` varchar(8) DEFAULT NULL,
				`nfiscal` varchar(12) DEFAULT NULL,
				`rif` varchar(13) DEFAULT NULL,
				`proveedor` varchar(40) DEFAULT NULL,
				`codigo` varchar(6) DEFAULT NULL,
				`descrip` varchar(50) DEFAULT NULL,
				`moneda` char(2) DEFAULT NULL,
				`montasa` decimal(17,2) DEFAULT '0.00',
				`tasa` decimal(17,2) DEFAULT NULL,
				`monredu` decimal(17,2) DEFAULT '0.00',
				`reducida` decimal(17,2) DEFAULT NULL,
				`monadic` decimal(17,2) DEFAULT '0.00',
				`sobretasa` decimal(17,2) DEFAULT NULL,
				`exento` decimal(17,2) DEFAULT '0.00',
				`importe` decimal(12,2) DEFAULT NULL,
				`sucursal` char(2) DEFAULT NULL,
				`departa` char(2) DEFAULT NULL,
				`usuario` varchar(12) DEFAULT NULL,
				`estampa` date DEFAULT NULL,
				`hora` varchar(8) DEFAULT NULL,
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($query);
		}

		if (!$this->db->table_exists('rica')) {
			$query="CREATE TABLE `rica` (
				`codigo` CHAR(5)    NOT  NULL,
				`activi` CHAR(14)   NULL DEFAULT NULL,
				`aplica` CHAR(100)  NULL DEFAULT NULL,
				`tasa` DECIMAL(8,2) NULL DEFAULT NULL,
				PRIMARY KEY (`codigo`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($query);
		}


		if (!$this->db->field_exists('ngasto','gserchi')) {
			$query="ALTER TABLE `gserchi` ADD COLUMN `ngasto` VARCHAR(8) NULL DEFAULT NULL AFTER `departa`";
			$this->db->simple_query($query);
		}

		if (!$this->db->field_exists('aceptado','gserchi')) {
			$query="ALTER TABLE gserchi ADD COLUMN aceptado CHAR(1) NULL DEFAULT NULL";
			$this->db->simple_query($query);
		}


		if (!$this->db->table_exists('gereten')) {
			$query="CREATE TABLE `gereten` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`idd` INT(11) NULL DEFAULT NULL,
				`origen` CHAR(4) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`numero` VARCHAR(25) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`codigorete` VARCHAR(4) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`actividad` VARCHAR(45) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				`base` DECIMAL(10,2) NULL DEFAULT NULL,
				`porcen` DECIMAL(5,2) NULL DEFAULT NULL,
				`monto` DECIMAL(10,2) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1;";
			$this->db->simple_query($query);
		}

		$mSQL="ALTER TABLE gereten CHANGE COLUMN id id INT(10) NOT NULL AUTO_INCREMENT FIRST";


		//./finanzas/caudi.php 
		if (!$this->db->table_exists('caudi')) {
			$mSQL="CREATE TABLE `caudi` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `caja` char(2) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `uscaja` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `status` char(1) COLLATE utf8_unicode_ci DEFAULT 'P' COMMENT 'Anulad, Pendiente, Cerrada',
			  `saldo` decimal(12,2) DEFAULT '0.00',
			  `monto` decimal(12,2) DEFAULT '0.00',
			  `diferencia` decimal(12,2) DEFAULT '0.00',
			  `observa` text COLLATE utf8_unicode_ci,
			  `transac` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `estampa` date DEFAULT NULL,
			  `hora` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `usuario` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `momento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('itcaudi')) {
			$mSQL="CREATE TABLE `itcaudi` (
			  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
			  `id_caudi` int(10) unsigned DEFAULT NULL,
			  `caja` char(2) DEFAULT NULL,
			  `tipo` char(2) DEFAULT NULL,
			  `monto` decimal(12,2) DEFAULT NULL,
			  `estampa` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`),
			  KEY `Index 2` (`tipo`,`caja`),
			  KEY `id_caudi` (`id_caudi`)
			) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COMMENT='Retiros de caja'";
			$this->db->simple_query($mSQL);
		}


		//./finanzas/gsercol.php 
		$query="SHOW INDEX FROM gser";
		$resul=$this->db->query($query);
		$existe=0;
		foreach($resul->result() as $ind){
			$nom= $ind->Column_name;
			if ($nom == 'id'){
				$existe=1;
				break;
			}
		}

		if($existe != 1) {
			$query="ALTER TABLE `gser` DROP PRIMARY KEY";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD UNIQUE INDEX `gser` (`fecha`, `numero`, `proveed`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `ncausado`,  ADD PRIMARY KEY (`id`)";
			var_dump($this->db->simple_query($query));
			$query="ALTER TABLE `gitser` ADD COLUMN `id` INT(15) UNSIGNED NULL AUTO_INCREMENT AFTER `reteica`,  ADD PRIMARY KEY (`id`);";
			$this->db->simple_query($query);
			$query="ALTER TABLE `gitser` ADD COLUMN `idgser` INT(15) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`, ADD INDEX `idgser` (`idgser`)";
			$this->db->simple_query($query);

			$query="UPDATE gitser AS a
				JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
				SET a.idgser=b.id";
			$this->db->simple_query($query);
		}

		$query="UPDATE gitser AS a
			JOIN gser AS b on a.numero=b.numero and a.fecha = b.fecha and a.proveed = b.proveed
			SET a.idgser=b.id";
		$this->db->simple_query($query);

		if (!$this->db->table_exists('gserchi')) {
			$query="CREATE TABLE IF NOT EXISTS `gserchi` (
				`codbanc` varchar(5) NOT NULL DEFAULT '', 
				`fechafac` date DEFAULT NULL, 
				`numfac` varchar(8) DEFAULT NULL, 
				`nfiscal` varchar(12) DEFAULT NULL, 
				`rif` varchar(13) DEFAULT NULL, 
				`proveedor` varchar(40) DEFAULT NULL, 
				`codigo` varchar(6) DEFAULT NULL, 
				`descrip` varchar(50) DEFAULT NULL, 
				`moneda` char(2) DEFAULT NULL, 
				`montasa` decimal(17,2) DEFAULT '0.00', 
				`tasa` decimal(17,2) DEFAULT NULL, 
				`monredu` decimal(17,2) DEFAULT '0.00', 
				`reducida` decimal(17,2) DEFAULT NULL, 
				`monadic` decimal(17,2) DEFAULT '0.00', 
				`sobretasa` decimal(17,2) DEFAULT NULL, 
				`exento` decimal(17,2) DEFAULT '0.00', 
				`importe` decimal(12,2) DEFAULT NULL, 
				`sucursal` char(2) DEFAULT NULL, 
				`departa` char(2) DEFAULT NULL, 
				`usuario` varchar(12) DEFAULT NULL, 
				`estampa` date DEFAULT NULL, 
				`hora` varchar(8) DEFAULT NULL, 
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
				PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($query);
		}

		if (!$this->db->table_exists('rica')) {
			$query="CREATE TABLE `rica` (
				`codigo` CHAR(5)    NOT  NULL,
				`activi` CHAR(14)   NULL DEFAULT NULL,
				`aplica` CHAR(100)  NULL DEFAULT NULL,
				`tasa` DECIMAL(8,2) NULL DEFAULT NULL,
				PRIMARY KEY (`codigo`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
			$this->db->simple_query($query);
		}

		if (!$this->db->field_exists('ngasto','gserchi')) {
			$query="ALTER TABLE `gserchi` ADD COLUMN `ngasto` VARCHAR(8) NULL DEFAULT NULL AFTER `departa`";
			$this->db->simple_query($query);
		}

		if (!$this->db->field_exists('aceptado','gserchi')) {
			$query="ALTER TABLE gserchi ADD COLUMN aceptado CHAR(1) NULL DEFAULT NULL";
			$this->db->simple_query($query);
		}

		if (!$this->db->table_exists('gereten')) {
			$query="CREATE TABLE `gereten` (
				`id` INT(10) NOT NULL DEFAULT '0',
				`idd` INT(11) NULL DEFAULT NULL,
				`origen` CHAR(4) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`numero` VARCHAR(25) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`codigorete` VARCHAR(4) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`actividad` VARCHAR(45) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`base` DECIMAL(10,2) NULL DEFAULT NULL,
				`porcen` DECIMAL(5,2) NULL DEFAULT NULL,
				`monto` DECIMAL(10,2) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($query);
		}


		//./finanzas/ccheque.php 
	


		//./finanzas/mgas.php 
		if (!$this->db->field_exists('reten','mgas')) {
			$mSQL="ALTER TABLE mgas ADD COLUMN reten VARCHAR(4) NULL DEFAULT NULL AFTER rica, ADD COLUMN retej VARCHAR(4) NULL DEFAULT NULL AFTER reten";
			$this->db->simple_query($mSQL);
		}


		//./finanzas/arqueo.php 



		//./finanzas/libros.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `libros` (
		  `metodo` varchar(50) NOT NULL default '',
		  `nombre` varchar(150) default NULL,
		  `activo` char(1) default NULL,
		  `tipo` char(1) default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `fgenera` char(6) default NULL,
		  PRIMARY KEY  (`metodo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);

		$data[]=array('metodo'=>'wlvexcelpdv'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV'      );
		$data[]=array('metodo'=>'wlvexcelpdvq1'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1');
		$data[]=array('metodo'=>'wlvexcelpdvq2'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal'  ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal2' ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal V2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq1','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1 Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq2','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2 Fiscal');
		$data[]=array('metodo'=>'wlvexcel'           ,'activo'=>'S','tipo'=>'D' ,'nombre' => 'Libro de Ventas'          );
		$data[]=array('metodo'=>'wlvexcel2'          ,'activo'=>'S','tipo'=>'D' ,'nombre' => 'Libro de Ventas no Agrupadas');
		$data[]=array('metodo'=>'wlvexcelfiscal'     ,'activo'=>'S','tipo'=>'D' ,'nombre' => 'Libro de Ventas Agrupadas Fiscal');
		$data[]=array('metodo'=>'wlvcierrez'         ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas basado en cierre Z');
		$data[]=array('metodo'=>'wlvexcelsucu'       ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas por Sucursal');
		$data[]=array('metodo'=>'wlvpersonal'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas Personalizado'   );
		$data[]=array('metodo'=>'wlcexcel'           ,'activo'=>'S','tipo'=>'D' ,'nombre' => 'Libro de Compras'         );
		$data[]=array('metodo'=>'wlcpersonal'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras Personalizado'   );
		$data[]=array('metodo'=>'wlcsexcel'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras Supermercado');
		$data[]=array('metodo'=>'wlvexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas  ESPECIAL');
		$data[]=array('metodo'=>'wlcexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras ESPECIAL');
		$data[]=array('metodo'=>'prorrata'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Prorrata'                 );
		$data[]=array('metodo'=>'invresu'            ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Inventario'      );

		$data[]=array('metodo'=>'genecompras'         ,'activo'=>'S','tipo'=>'G' ,'nombre' => 'Generar Libro de compras COMPRAS' );
		$data[]=array('metodo'=>'genesfaccierrez'     ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas basado en cierre Z' );
		$data[]=array('metodo'=>'genegastos'          ,'activo'=>'S','tipo'=>'G' ,'nombre' => 'Generar Libro de compras GASTOS'  );
		$data[]=array('metodo'=>'genecxp'             ,'activo'=>'S','tipo'=>'G' ,'nombre' => 'Generar Libro de compras CXP'     );
		$data[]=array('metodo'=>'genesfac'            ,'activo'=>'S','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas' );
		$data[]=array('metodo'=>'genesfacfiscal'      ,'activo'=>'S','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas Fiscal' );
		$data[]=array('metodo'=>'geneventasfiscalpdv' ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Fiscal PDV'   );
		$data[]=array('metodo'=>'genesfmay'           ,'activo'=>'S','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas al mayor' );
		$data[]=array('metodo'=>'genesmov'            ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas CXC'      );
		$data[]=array('metodo'=>'geneotin'            ,'activo'=>'S','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas O.Ingresos');
		$data[]=array('metodo'=>'generest'            ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Restaurante');
		$data[]=array('metodo'=>'genehotel'           ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Hotel');
		$data[]=array('metodo'=>'geneventassfacfiscal','activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas PDV de auditorias');


		foreach($data AS $algo){
			$mSQL = $this->db->insert_string('libros', $algo);
			$this->db->simple_query($mSQL);
		}

		$campos = $this->db->list_fields('siva');
		if (!in_array('serie',$campos)){
			$mSQL="ALTER TABLE `siva`  ADD COLUMN `serie` VARCHAR(20) NULL DEFAULT NULL AFTER `serial`;";
			$this->db->simple_query($mSQL);
		}

		if (!in_array('afecta',$campos)){
			$mSQL="ALTER TABLE `siva`  ADD COLUMN `afecta` VARCHAR(10) NULL DEFAULT NULL AFTER `fafecta`";
			$this->db->simple_query($mSQL);
		}

		if (!in_array('cierrez',$campos)){
			$mSQL="ALTER TABLE `siva` ADD COLUMN `cierrez` VARCHAR(15) NULL DEFAULT NULL AFTER `serial`";
			$this->db->simple_query($mSQL);
		}

		if (!in_array('hora',$campos)){
			$mSQL="ALTER TABLE `siva` ADD `hora` TIME DEFAULT '0' NULL";
			$this->db->simple_query($mSQL);
		}

		$mSQL="ALTER TABLE `siva` CHANGE `numero` `numero` VARCHAR(20) NULL";
		$this->db->simple_query($mSQL);

		$mSQL="ALTER TABLE `siva` ADD `serial` CHAR(20) NULL";
		$this->db->simple_query($mSQL);

		$mSQL="ALTER TABLE `siva`  CHANGE COLUMN `nombre` `nombre` VARCHAR(200) NULL DEFAULT NULL AFTER `clipro`";
		$this->db->simple_query($mSQL);

		//$mSQL="ALTER TABLE `siva`  CHANGE COLUMN `numero` `numero` VARCHAR(20) NOT NULL DEFAULT '' AFTER `fecha`";
		//$this->db->simple_query($mSQL);

		echo $uri = anchor('finanzas/libros/configurar','Configurar');


		//./finanzas/aapan.php 
		$sql="ALTER TABLE `apan`  ADD COLUMN `id` INT(10) NULL AUTO_INCREMENT AFTER `usuario`,  ADD PRIMARY KEY (`id`)";
		$this->db->query($sql);
		


		//./finanzas/ffactura.php 
		$mSQL="CREATE TABLE `matbar`.`ffactura` (`id` TINYINT AUTO_INCREMENT, `estampa` TIMESTAMP, `fecha` DATE, `t1horae` VARCHAR (8),`t2horae` VARCHAR (8), `t1horas` VARCHAR (8),`t2horas` VARCHAR (8),`empresa` VARCHAR (50), `tecnico1` VARCHAR (50), `tecnico2` VARCHAR (50), `tecnico3` VARCHAR (50), `informe` TEXT,`observa` TEXT, `t1tipos` VARCHAR(10),`t2tipos` VARCHAR(10),`t1tipoe` VARCHAR(10),`t2tipoe` VARCHAR(10),`nombre` VARCHAR(60),PRIMARY KEY(`id`)) TYPE = MyISAM";
		$this->db->simple_query($mSQL);


		//./finanzas/rete.php 
		if (!$this->db->field_exists('ut','rete')) {
		 $mSQL="ALTER TABLE rete CHANGE COLUMN tipocol tipocol CHAR(2) NULL DEFAULT '0.0' COLLATE 'utf8_unicode_ci' AFTER cuenta, ADD COLUMN ut DECIMAL(12,2) NULL DEFAULT NULL AFTER tipocol";
		 $this->db->simple_query($mSQL);
		}


		//./finanzas/librosan.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `libros` (
		  `metodo` varchar(50) NOT NULL default '',
		  `nombre` varchar(150) default NULL,
		  `activo` char(1) default NULL,
		  `tipo` char(1) default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `fgenera` char(6) default NULL,
		  PRIMARY KEY  (`metodo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";   
		$this->db->simple_query($mSQL);
		
		$data[]=array('metodo'=>'wlvexcelpdv'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV'      );
		$data[]=array('metodo'=>'wlvexcelpdvq1'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1');
		$data[]=array('metodo'=>'wlvexcelpdvq2'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal'  ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq1','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1 Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq2','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2 Fiscal');
		$data[]=array('metodo'=>'wlvexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas'          );
		$data[]=array('metodo'=>'wlvexcelsucu'       ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas por Sucursal');
		$data[]=array('metodo'=>'wlcexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras'         );
		$data[]=array('metodo'=>'wlcsexcel'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras Supermercado');
		$data[]=array('metodo'=>'wlvexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas  ESPECIAL');
		$data[]=array('metodo'=>'wlcexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras ESPECIAL');
		$data[]=array('metodo'=>'prorrata'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Prorrata'                 );
		$data[]=array('metodo'=>'invresu'            ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Inventario'      );
		
		$data[]=array('metodo'=>'genecompras' ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras COMPRAS' );
		$data[]=array('metodo'=>'genegastos'  ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras GASTOS'  );
		$data[]=array('metodo'=>'genecxp'     ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras CXP'     );
		$data[]=array('metodo'=>'genesfac'    ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas' );
		$data[]=array('metodo'=>'genesfmay'   ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas al mayor' );
		$data[]=array('metodo'=>'genesmov'    ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas CXC'      );
		$data[]=array('metodo'=>'geneotin'    ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas O.Ingresos');
		$data[]=array('metodo'=>'generest'    ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Restaurante');
		$data[]=array('metodo'=>'genehotel'   ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Hotel');
		
		foreach($data AS $algo){
			$mSQL = $this->db->insert_string('libros', $algo);
			$this->db->simple_query($mSQL);
		}
		$this->db->simple_query($mSQL);
		echo $uri = anchor('finanzas/libros/configurar','Configurar');


		//./finanzas/apan.php 
		//$sql="ALTER TABLE `apan`  DROP PRIMARY KEY";
		//$this->db->query($sql);
		$sql="ALTER TABLE `apan`  ADD COLUMN `id` INT(10) NULL AUTO_INCREMENT AFTER `usuario`,  ADD PRIMARY KEY (`id`)";
		$this->db->query($sql);


		//./finanzas/bman.php 
		$mSQL="CREATE TABLE `bman` (`id` BIGINT AUTO_INCREMENT, `codbanc` VARCHAR (10), `tipo` VARCHAR (10), `numero` VARCHAR (12), `fecha` DATE, `beneficiario` VARCHAR (50), `monto` DECIMAL (17) , PRIMARY KEY(`id`))";
		$this->db->simple_query($mSQL);


		//./finanzas/cuenpa.php 
		$mSQL="CREATE TABLE `cuenpa` (`id` BIGINT AUTO_INCREMENT, `cliente` VARCHAR (20), `tipo` VARCHAR (10), `numero` VARCHAR (12), `fecha` DATE, `vence` DATE, `monto` DECIMAL (17), PRIMARY KEY(`id`))";
		$this->db->simple_query($mSQL);


		//./finanzas/libros_calore3.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `libros` (
		  `metodo` varchar(50) NOT NULL default '',
		  `nombre` varchar(150) default NULL,
		  `activo` char(1) default NULL,
		  `tipo` char(1) default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `fgenera` char(6) default NULL,
		  PRIMARY KEY  (`metodo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";   
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` ADD `hora` TIME DEFAULT '0' NULL";
		$this->db->simple_query($mSQL);
		
		$mSQL="ALTER TABLE `siva` CHANGE `clipro` `clipro` VARCHAR(12) NULL";
		$this->db->simple_query($mSQL);
		
		$data[]=array('metodo'=>'wlvexcelpdv'        ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV'      );
		$data[]=array('metodo'=>'wlvexcelpdvq1'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1');
		$data[]=array('metodo'=>'wlvexcelpdvq2'      ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2');
		$data[]=array('metodo'=>'wlvexcelpdvfiscal'  ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq1','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 1 Fiscal');
		$data[]=array('metodo'=>'wlvexcelpdvfiscalq2','activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas PDV Quincenta 2 Fiscal');
		$data[]=array('metodo'=>'wlvexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas'          );
		$data[]=array('metodo'=>'wlvexcelsucu'       ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas por Sucursal');
		$data[]=array('metodo'=>'wlcexcel'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras'         );
		$data[]=array('metodo'=>'wlcsexcel'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras Supermercado');
		$data[]=array('metodo'=>'wlvexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas  ESPECIAL');
		$data[]=array('metodo'=>'wlcexcele'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Compras ESPECIAL');
		$data[]=array('metodo'=>'prorrata'           ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Prorrata'                 );
		$data[]=array('metodo'=>'invresu'            ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Inventario'      );
		
		$data[]=array('metodo'=>'genecompras'     ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras COMPRAS' );
		$data[]=array('metodo'=>'genegastos'      ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras GASTOS'  );
		$data[]=array('metodo'=>'genecxp'         ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de compras CXP'     );
		$data[]=array('metodo'=>'genesfac'        ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas' );
		$data[]=array('metodo'=>'geneventasfiscal','activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Fiscal PDV'   );
		$data[]=array('metodo'=>'genesfmay'       ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas Facturas al mayor' );
		$data[]=array('metodo'=>'genesmov'        ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas CXC'      );
		$data[]=array('metodo'=>'geneotin'        ,'activo'=>'N','tipo'=>'G' ,'nombre' => 'Generar Libro de ventas O.Ingresos');
		$data[]=array('metodo'=>'generest'        ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Restaurante');
		$data[]=array('metodo'=>'genehotel'       ,'activo'=>'N','tipo'=>'G' ,'nombre'  =>'Generar Libro de ventas Hotel');
		
		foreach($data AS $algo){
			$mSQL = $this->db->insert_string('libros', $algo);
			$this->db->simple_query($mSQL);
		}
		$this->db->simple_query($mSQL);
		echo $uri = anchor('finanzas/libros_calore3/configurar','Configurar');


		//./formams.php 
		if (!$this->db->table_exists('formaesp')) {
			$mSQL="CREATE TABLE `formaesp` (  `nombre` varchar(20) NOT NULL DEFAULT '',  `descrip` varchar(200) DEFAULT NULL,  `word` longtext,  PRIMARY KEY (`nombre`),  UNIQUE KEY `nombre` (`nombre`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Formatos especiales'";
			$this->db->simple_query($mSQL);
		}


		//./buscar.php 
		$mSQL="CREATE TABLE IF NOT EXISTS `modbus` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `uri` varchar(50) NOT NULL default '',
		  `idm` varchar(50) NOT NULL default '',
		  `parametros` text,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1745 DEFAULT CHARSET=latin1";

		$this->db->simple_query($mSQL);
		$query="ALTER TABLE `stal`  CHANGE COLUMN `nombre` `nombre` TEXT NULL DEFAULT NULL";
		$this->db->simple_query($query);


		//./construccion/edif.php 
		ediftipo::instalar();
		if (!$this->db->table_exists('edif')) {
			$mSQL="CREATE TABLE `edif` (
				  `id` INT(11) NOT NULL AUTO_INCREMENT,
				  `nombre` CHAR(120) NULL DEFAULT NULL,
				  `tipo` INT(10) NULL DEFAULT NULL,
				  `direccion` TEXT NULL,
				  `descripcion` TEXT NULL,
				  `promotora` CHAR(5) NULL DEFAULT NULL,
				  PRIMARY KEY (`id`)
				  )
				  COMMENT='Edificaciones'
				  COLLATE='latin1_swedish_ci'
				  ENGINE=MyISAM
				  ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}


		//./construccion/edcont.php 
		if (!$this->db->table_exists('edcont')) {
			$mSQL="CREATE TABLE `edcont` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`id_edres` int(11) DEFAULT '0',
				`numero_edres` char(8) DEFAULT NULL,
				`numero` char(8) DEFAULT NULL,
				`fecha` date DEFAULT NULL,
				`cliente` char(5) DEFAULT NULL,
				`edificacion` int(11) DEFAULT '0',
				`inmueble` int(11) DEFAULT '0',
				`inicial` decimal(17,2) DEFAULT '0.00',
				`financiable` decimal(17,2) DEFAULT '0.00',
				`firma` decimal(17,2) DEFAULT '0.00',
				`precioxmt2` decimal(17,2) DEFAULT '0.00',
				`mt2` decimal(17,2) DEFAULT '0.00',
				`monto` decimal(17,2) DEFAULT '0.00',
				`notas` text,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Reserva de Inmuebles'";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('uso', 'edcont')){
			$mSQL="ALTER TABLE `edcont` ADD COLUMN `uso` INT(11) NOT NULL AFTER `notas`";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('status', 'edcont')){
			$mSQL="ALTER TABLE `edcont` ADD COLUMN `status` CHAR(1) NOT NULL DEFAULT 'P' AFTER `numero`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('itedcont')) {
			$mSQL="CREATE TABLE `itedcont` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `id_edcont` int(11) NOT NULL,
			  `vencimiento` date NOT NULL,
			  `monto` decimal(10,2) NOT NULL,
			  PRIMARY KEY (`id`),
			 KEY `id_edcont` (`id_edcont`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->field_exists('especial', 'itedcont')){
			$mSQL= "ALTER TABLE `itedcont` ADD COLUMN `especial` CHAR(1) NOT NULL DEFAULT 'N' AFTER `id_edcont`";
		}



		//./construccion/edifubica.php 
		if (!$this->db->table_exists('edifubica')) {
			$mSQL="CREATE TABLE `edifubica` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `id_edif` INT(11) NULL DEFAULT NULL,
			  `descripcion` CHAR(50) NULL DEFAULT NULL,
			PRIMARY KEY (`id`)
			)
			COMMENT='Ubicaciones dentro de una edificacion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}


		//./construccion/edinmue.php 
		if (!$this->db->table_exists('edinmue')) {
			$mSQL="CREATE TABLE `edinmue` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `codigo` CHAR(15) NULL DEFAULT NULL,
			  `descripcion` CHAR(100) NULL DEFAULT NULL,
			  `edificacion` INT(11) NULL DEFAULT NULL,
			  `uso` INT(11) NULL DEFAULT NULL,
			  `usoalter` INT(11) NULL DEFAULT NULL,
			  `ubicacion` INT(11) NULL DEFAULT NULL,
			  `caracteristicas` TEXT NULL,
			  `area` DECIMAL(15,2) NULL DEFAULT NULL,
			  `estaciona` INT(10) NULL DEFAULT NULL,
			  `deposito` INT(11) NULL DEFAULT NULL,
			  `preciomt2` DECIMAL(15,2) NULL DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `codigo` (`codigo`)
			)
			COMMENT='Inmuebles'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('preciomt2e', 'edinmue')) {
			$mSQL="ALTER TABLE `edinmue`  CHANGE COLUMN `preciomt2` `preciomt2e` DECIMAL(15,2) NULL AFTER `deposito`,  ADD COLUMN `preciomt2c` DECIMAL(15,2) NULL AFTER `preciomt2e`,  ADD COLUMN `preciomt2a` DECIMAL(15,2) NULL AFTER `preciomt2c`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('objeto', 'edinmue')) {
			$mSQL="ALTER TABLE `edinmue`  ADD COLUMN `objeto` CHAR(1) NOT NULL AFTER `preciomt2a`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('status', 'edinmue')) {
			$mSQL="ALTER TABLE `edinmue`  ADD COLUMN `status` CHAR(1) NOT NULL COMMENT 'Alquilado, Vendido, Reservado,Disponible, Otro' AFTER `objeto`;";
			$this->db->simple_query($mSQL);
		}



		//./construccion/edres.php 
		if (!$this->db->table_exists('edres')) {
			$mSQL="CREATE TABLE `edres` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `numero` char(8) DEFAULT NULL,
			  `fecha` date DEFAULT NULL,
			  `cliente` char(5) DEFAULT NULL,
			  `edificacion` int(11) DEFAULT '0',
			  `inmueble` int(11) DEFAULT '0',
			  `reserva` decimal(17,2) DEFAULT '0.00',
			  `formap1` char(2) DEFAULT '0',
			  `banco1` char(3) DEFAULT '0',
			  `nummp1` varchar(20) DEFAULT '0',
			  `monto1` decimal(17,2) DEFAULT '0.00',
			  `formap2` char(2) DEFAULT '0',
			  `banco2` char(3) DEFAULT '0',
			  `nummp2` varchar(20) DEFAULT '0',
			  `monto2` decimal(17,2) DEFAULT '0.00',
			  `formap3` char(2) DEFAULT '0',
			  `banco3` char(3) DEFAULT '0',
			  `nummp3` varchar(20) DEFAULT '0',
			  `monto3` decimal(17,2) DEFAULT '0.00',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Reserva de Inmuebles'";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('notas', 'edres')){
			$mSQL="ALTER TABLE `edres`  ADD COLUMN `notas` TEXT NULL DEFAULT NULL AFTER `monto3`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('pfecha1', 'edres')){
			$mSQL="ALTER TABLE `edres`  ADD COLUMN `pfecha1` DATE NULL AFTER `monto1`,  ADD COLUMN `pfecha2` DATE NULL AFTER `monto2`,  ADD COLUMN `pfecha3` DATE NULL AFTER `monto3`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('transac', 'edres')){
			$mSQL="ALTER TABLE `edres` ADD COLUMN `transac` VARCHAR(8) NULL AFTER `notas`";
			$this->db->simple_query($mSQL);
		}


		//./construccion/partida.php 
		$mSQL="CREATE TABLE `obpa` (
			 `codigo` char(4) NOT NULL DEFAULT '',
			`descrip` varchar(40) DEFAULT NULL,
			`grupo` char(4) DEFAULT NULL,
			`comision` decimal(5,2) DEFAULT NULL,
			`nomgrup` varchar(30) DEFAULT NULL,
			PRIMARY KEY (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);

		$mSQL="CREATE TABLE `obgp` (
			`grupo` char(4) NOT NULL DEFAULT '',
			`nombre` varchar(30) DEFAULT '0',
			PRIMARY KEY (`grupo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);


		//./construccion/eduso.php 
		if (!$this->db->table_exists('eduso')) {
			$mSQL="CREATE TABLE `eduso` (
				  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  `uso` CHAR(80) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
				  PRIMARY KEY (`id`)
				  )
				  COMMENT='Usos de los inmuebles'
				  COLLATE='latin1_swedish_ci'
				  ENGINE=MyISAM
				  ROW_FORMAT=DEFAULT
				  AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}


		//./construccion/ediftipo.php 
		if (!$this->db->table_exists('ediftipo')) {
			$mSQL="CREATE TABLE `ediftipo` (
				  `id` INT(11) NOT NULL AUTO_INCREMENT,
				  `descrip` CHAR(50) NULL DEFAULT NULL,
				  PRIMARY KEY (`id`)
				  )
				  COMMENT='Tipos de Edificaciones'
				  COLLATE='latin1_swedish_ci'
				  ENGINE=MyISAM
				  ROW_FORMAT=DEFAULT
				  AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}
	}
}
