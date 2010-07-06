<?php
class Instalador extends Controller {
	function Instalador(){
		parent::Controller();
	}
	function index(){
		// accesos.php
		// basecontroller.php
		// bienvenido.php
		// buscar.php

		$mSQL="CREATE TABLE IF NOT EXISTS `modbus` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `uri` varchar(50) NOT NULL default '',
		  `idm` varchar(50) NOT NULL default '',
		  `parametros` text,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1745 DEFAULT CHARSET=latin1";

		$this->db->simple_query($mSQL);
	
		// cargasarch.php
		// chat.php
		// consulcajas.php
		// dashboard.php
		// demo.php
		// desarrollo.php
		// ejecutasql.php
		// ejemplo.php
		// formatos.php

		$mSQL="ALTER TABLE `formatos` ADD `proteo` TEXT NULL AFTER `forma`";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `formatos` ADD `harbour` TEXT NULL AFTER `proteo`";
		$this->db->simple_query($mSQL);
	
		// frames.php
		// lejemplo.php
		// linventario.php
		// lprueba2.php
		// lprueba.php
		// menu.php
		// mpru.php
		// prueba.php
		// recursos.php
		// reportes.php

		$mSQL="ALTER TABLE `reportes` ADD `proteo` TEXT NULL";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `reportes` ADD `harbour` TEXT NULL";
		$this->db->simple_query($mSQL);
	
		// supermercadosant.php
		// validaciones.php
		// welcome.php
		// xlsauto2.php
		// xlsauto.php
		// xmlrpc_client.php
		// xmlrpc_server.php
		// add.php

		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// agregaroc.php

		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// agregar.php

		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// analisis.php
		// a.php
		// gcompras.php
		// grpr.php
		// ordc.php
		// productos.php
		// proveedores.php
		// scst.php
		// sprm.php
		// sprv.php


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
	
		// gpartida.php
		// partida.php
		// casi.php

		$mSQL='ALTER TABLE itcasi ADD id INT AUTO_INCREMENT PRIMARY KEY';
                $this->db->simple_query($mSQL);
	
		// cierre.php
		// configurar.php
		// cpla.php
		// estadosf.php
		// generar.php
		// metodos.php
		// reglas.php
		// modulos.php
		// tablas.php
		// aumento.php
		// agregareg.php

		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// analisisbanc.php
		// analisisgastos.php
		// analisisvision.php
		// anuales.php
		// apan.php
		// banc.php
		// blibros.php
		// bman.php

		$mSQL="CREATE TABLE `bman` (`id` BIGINT AUTO_INCREMENT, `codbanc` VARCHAR (10), `tipo` VARCHAR (10), `numero` VARCHAR (12), `fecha` DATE, `beneficiario` VARCHAR (50), `monto` DECIMAL (17) , PRIMARY KEY(`id`))";
		$this->db->simple_query($mSQL);
	
		// bmov.php
		// bmovshow.php
		// civa.php
		// compras.php
		// conforch.php
		// cruc.php
		// cuenco.php

		$mSQL="CREATE TABLE `cuenco` (`id` BIGINT AUTO_INCREMENT, `cliente` VARCHAR (20), `tipo` VARCHAR (10), `numero` VARCHAR (12), `fecha` DATE, `vence` DATE, `monto` DECIMAL (17), PRIMARY KEY(`id`))";
		$this->db->simple_query($mSQL);
	
		// cuenpa.php

		$mSQL="CREATE TABLE `cuenpa` (`id` BIGINT AUTO_INCREMENT, `cliente` VARCHAR (20), `tipo` VARCHAR (10), `numero` VARCHAR (12), `fecha` DATE, `vence` DATE, `monto` DECIMAL (17), PRIMARY KEY(`id`))";
		$this->db->simple_query($mSQL);
	
		// ejemplo.php
		// exportar.php
		// ffactura.php

		$mSQL="CREATE TABLE `matbar`.`ffactura` (`id` TINYINT AUTO_INCREMENT, `estampa` TIMESTAMP, `fecha` DATE, `t1horae` VARCHAR (8),`t2horae` VARCHAR (8), `t1horas` VARCHAR (8),`t2horas` VARCHAR (8),`empresa` VARCHAR (50), `tecnico1` VARCHAR (50), `tecnico2` VARCHAR (50), `tecnico3` VARCHAR (50), `informe` TEXT,`observa` TEXT, `t1tipos` VARCHAR(10),`t2tipos` VARCHAR(10),`t1tipoe` VARCHAR(10),`t2tipoe` VARCHAR(10),`nombre` VARCHAR(60),PRIMARY KEY(`id`)) TYPE = MyISAM";
		$this->db->simple_query($mSQL);
	
		// gastos.php
		// ggastos.php
		// grga.php
		// gser.php
		// invgasto.php
		// lbancos.php
		// libros_25.php

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
	
		// librosan.php

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
	
		// libros_calore.php

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
		echo $uri = anchor('finanzas/libros/configurar','Configurar');
	
		// libroshotel.php
		// libros.php

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
		$data[]=array('metodo'=>'wlvexcel2'          ,'activo'=>'N','tipo'=>'D' ,'nombre' => 'Libro de Ventas no Agrupadas'          );
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
	
		// libros_respaldo.php

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
	
		// libros_samy.php

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
	
		// l.php
		// mgas.php
		// movproveedores.php
		// obco.php
		// oconceptos.php
		// ords.php
		// otrosconceptoscontable.php
		// prmo.php
		// provoca.php
		// resumendiario.php
		// rete.php
		// retetxt.php
		// siva.php
		// sprm.php
		// tardet.php
		// tban.php
		// wexcelpdv.php
		// wlvexcel1.php
		// consulcubo.php
		// dimensiones.php
		// agregarrec.php
		// anuales.php
		// carta.php

		$mSQL="ALTER TABLE `menu` ADD activard CHAR(5) DEFAULT '00:00'";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `menu` ADD activarh CHAR(5) DEFAULT '99:99'";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `menu` ADD activardia CHAR(7) DEFAULT '0123456'";
		$this->db->simple_query($mSQL);
	
		// clientes.php
		// clientesp.php
		// fromdesk.php
		// frontdesk.php
		// ggastos.php
		// gmesoneros.php
		// grupomenu.php
		// habita.php
		// huesped.php
		// interusr.php
		// mensuales.php
		// mesoneros.php
		// precios.php
		// rconsultas.php
		// recepcion.php
		// recetas.php
		// reserva.php
		// restaurante.php

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
	
		// restaurantesan.php

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
	
		// servus.php
		// taritele.php
		// ajaxsamples.php
		// crudsamples.php
		// crudworkflow.php
		// datam.php
		// importa.php
		// imtgasto.php
		// pagepersistence.php
		// samples.php
		// sessiontest.php
		// supercrud.php
		// tests.php
		// ajusinv.php
		// anticipos.php
		// barras.php
		// cambioprecio.php
		// cambiosinv.php
		// catalogo.php

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
	
		// catalogover.php
		// caub.php
		// common.php
		// comparativo.php

		/*$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);*/
	
		// concepto.php
		// conprecio.php
		// consultas.php
		// conversiones.php
		// crudsamples.php
		// dpto.php
		// fallas.php
		// fisicos.php
		// fotos.php

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
	
		// gproductos.php
		// grup.php
		// hacer.php
		// kardex.php
		// lfallasdeinventario.php
		// lhojadetrabajo.php
		// line.php
		// linventario.php
		// listacontodoslosprecios.php
		// listadepesos.php
		// listadepreciosdetal.php
		// listadepreciosgeneral.php
		// listadepreciosinterna.php
		// lnmov.php
		// lpersonal.php
		// marc.php
		// seriales.php
		// sinvactu.php

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
)
	';
$this->db->simple_query($mSQL);	
	
		// sinv.php

		
		$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
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
	
		// sinvsant.php

		$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// sinvshow.php
		// straa.php
		// stra.php
		// test.php
		// transferencia.php
		// ubica.php

		$mSQL='ALTER TABLE sinv ADD id INT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id);';
	
		// unidad.php
		// accesos.php
		// asig.php

		$mSQL="ALTER TABLE asig ADD PRIMARY KEY (codigo);";
		$this->db->simple_query($mSQL);	
	
		// aumentoempleados.php
		// aumentosueldo.php

		$mSQL="ALTER TABLE ausu ADD PRIMARY KEY (codigo,fecha);";
		$this->db->simple_query($mSQL);	
	
		// aumentosueldos.php
		// calendario.php
		// carg.php

		$mSQL="ALTER TABLE carg ADD PRIMARY KEY (cargo);";
		$this->db->simple_query($mSQL);	
	
		// conc.php

		$mSQL="ALTER TABLE conc ADD PRIMARY KEY (concepto);";
		$this->db->simple_query($mSQL);	
	
		// depa.php
		// divi.php
		// gprestamos.php
		// horarios.php

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
		ROW_FORMAT=DEFAULT;
		ALTER TABLE `pers`  ADD COLUMN `horario` CHAR(4) NULL DEFAULT NULL
		";
		
		echo $this->db->query($query);
	
	
		// lpersonal.php
		// minfra.php
		// noco.php
		// nomina.php
		// pers.php

		
		$mSQL="CREATE TABLE `tipoe` (`codigo` VARCHAR (10), `tipo` VARCHAR (50), PRIMARY KEY(`codigo`))";
		$this->db->query($mSQL);
		$mSQL1="ALTER TABLE `pers` ADD `email` VARCHAR(50)";
		$this->db->query($mSQL1);
		$mSQL2="CREATE TABLE `posicion` (`codigo` VARCHAR (10), `posicion` VARCHAR (30),PRIMARY KEY(`codigo`))";
		$this->db->query($mSQL2);
		$mSQL3="ALTER TABLE `pers` ADD `tipoe` VARCHAR(10)";
		$this->db->query($mSQL3);
		$mSQL4="ALTER TABLE `pers` ADD `escritura` VARCHAR(25)";
		$this->db->query($mSQL4);
	
		// prenom.php
		// pres.php
		// prestamos.php
		// prof.php
		// promediosueldos.php
		// prueba.php
		// rpers.php
		// ajaxsamples.php
		// auth.php
		// basecontroller.php
		// crudsamples.php
		// crudworkflow.php
		// datam.php
		// lang.php
		// samples.php
		// supercrud.php
		// uri_test.php
		// utils.php
		// dispmoviles.php
		// exportar.php
		// importar.php
		// analisis1.php
		// analisis.php
		// analisisvision.php
		// apagar.php
		// arqueo.php
		// bancos.php
		// base.php
		// buscafac.php
		// caja.php

		$mSQL='ALTER TABLE `sucu` ADD `url` VARCHAR(200) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `prefijo` CHAR(1) NULL';
		$this->db->simple_query($mSQL);
	
		// cajas.php
		// cierre.php
		// clientes.php
		// club.php
		// conciliacion.php
		// consulcajas.php
		// consulsucu.php
		// consultas2.php
		// consultasan.php
		// consultas.php
		// cupones.php
		// dias.php
		// efisico.php
		// envivo.php
		// exportar.php
		// fami.php
		// fisicos.php
		// ganancias.php
		// gproductos.php
		// grup.php
		// kardex.php
		// lfisico.php
		// linventario.php
		// lresumen.php
		// maes.php
		// poscuadre.php
		// posfact.php
		// precioclient.php
		// productos.php
		// restaurante.php
		// rifas.php
		// tpagos.php
		// traer.php

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
	
		// tventas.php
		// ventas.php
		// accesos.php

		//for($i=1;$i<=65535;$i++)
		//	$this->db->simple_query("INSERT INTO serie SET hexa=HEX($i)");
		
		//$mSQL='ALTER TABLE `intramenu` DROP PRIMARY KEY';
		//$this->db->simple_query($mSQL);
		//$mSQL='ALTER TABLE intramenu ADD id INT AUTO_INCREMENT PRIMARY KEY';
		//$this->db->simple_query($mSQL);
		//$mSQL='ALTER TABLE `intramenu` ADD `pertenece` VARCHAR(10) DEFAULT NULL NULL AFTER `visible`';
		//$this->db->simple_query($mSQL);
		//$mSQL='UPDATE intramenu SET pertenece=MID(modulo,1,1) WHERE MID(modulo,1,1)!= "0" AND modulo REGEXP  "[[:digit:]]" AND CHAR_LENGTH(modulo)>1';
		//$this->db->simple_query($mSQL);
		////ALTER TABLE `intramenu` ADD PRIMARY KEY (`modulo`)
	
		// acdatasis.php
		// bitacora.php

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
	
		// conec.php
		// directorio.php

		$mSQL="CREATE TABLE `datasis`.`directorio` (`id` INT AUTO_INCREMENT, `cedula` VARCHAR (13), `cliente` VARCHAR (30), `proveed` VARCHAR (30),`empleado` VARCHAR (30), `nombres` VARCHAR (50), `apellidos` VARCHAR (50), `edad` VARCHAR (2), `sexo` VARCHAR (1), `telefono1` VARCHAR (20), `telefono2` VARCHAR (20), `telefono3` VARCHAR (20),`direc1` VARCHAR (70), `direc2` VARCHAR (70), `profesion` VARCHAR (30), `cargo` VARCHAR (30), `fnacimiento` VARCHAR (20),`email` VARCHAR (50),`email2` VARCHAR (50),`email3` VARCHAR (50), PRIMARY KEY(`id`)) TYPE = MyISAM"; 
		$this->db->simple_query($mSQL);
	
		// docu.php
		// formatos.php

		$mSQL="CREATE TABLE IF NOT EXISTS `intrarepo` (
		  `nombre` varchar(71) NOT NULL default '',
		  `modulo` varchar(10) NOT NULL default '',
		  `titulo` varchar(20) default NULL,
		  `mensaje` varchar(60) default NULL,
		  `activo` char(1) default 'S',
		  PRIMARY KEY  (`nombre`,`modulo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	
		// internet.php

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
	
		// lacceso.php
		// logo.php
		// logusu.php
		// lusuarios.php
		// mantenimiento.php
		// menu.php

		$mSQL="ALTER TABLE `intramenu` ADD COLUMN `orden` TINYINT(4) NULL DEFAULT NULL AFTER `pertenece`";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `intramenu` ADD COLUMN `ancho` INT(10) UNSIGNED NULL DEFAULT '800' AFTER `orden`";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `intramenu` ADD COLUMN `alto`  INT(10) UNSIGNED NULL DEFAULT '600' AFTER `ancho`";
		$this->db->simple_query($mSQL);
	
		// muro.php

		$mSQL="CREATE TABLE IF NOT EXISTS `muro` (
		  `codigo` int(11) NOT NULL auto_increment,
		  `envia` varchar(15) default NULL,
		  `recibe` varchar(15) default NULL,
		  `mensaje` text,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		  PRIMARY KEY  (`codigo`)
		) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	
		// noticias.php
		// prueba.php
		// publicidad.php

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
	
		// puertosdir.php
		// repodupli.php

		$mSQL="ALTER TABLE `repodupli` ADD `status` CHAR(2) NULL";
		$this->db->query($mSQL);
	
		// repomenu.php

		$mSQL="CREATE TABLE IF NOT EXISTS `intrarepo` (
		  `nombre` varchar(71) NOT NULL default '',
		  `modulo` varchar(10) NOT NULL default '',
		  `titulo` varchar(20) default NULL,
		  `mensaje` varchar(60) default NULL,
		  `activo` char(1) default 'S',
		  PRIMARY KEY  (`nombre`,`modulo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	
		// repotra.php

		$mSQL="CREATE TABLE `matbar`.`repotra` (`id` TINYINT AUTO_INCREMENT, `estampa` TIMESTAMP, `fecha` DATE, `t1horae` VARCHAR (8),`t2horae` VARCHAR (8), `t1horas` VARCHAR (8),`t2horas` VARCHAR (8),`empresa` VARCHAR (50), `tecnico1` VARCHAR (50), `tecnico2` VARCHAR (50), `tecnico3` VARCHAR (50), `informe` TEXT,`observa` TEXT, `t1tipos` VARCHAR(10),`t2tipos` VARCHAR(10),`t1tipoe` VARCHAR(10),`t2tipoe` VARCHAR(10),`nombre` VARCHAR(60),PRIMARY KEY(`id`)) TYPE = MyISAM";
		$this->db->simple_query($mSQL);
	
		// sitemslog.php
		// sopor.php
		// sucu.php

		$mSQL="ALTER TABLE `sucu` ADD `url` VARCHAR(200) NULL";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `sucu` ADD `prefijo` VARCHAR(3) NULL";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `sucu` ADD `proteo` VARCHAR(50) NULL";
		$this->db->simple_query($mSQL);
		$mSQL="ALTER TABLE `sucu` ADD PRIMARY KEY (`codigo`)";
		$this->db->simple_query($mSQL);
	
		// tiketc.php

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
	
		// tiketimp.php
		// tiket.php

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
	
		// tiketp.php

		$mSQL="CREATE TABLE IF NOT EXISTS `tiketp` (`id` TINYINT AUTO_INCREMENT, `estampa` TIMESTAMP, `fecha` DATE, `codigo` VARCHAR (20),`empresa` VARCHAR (100), `tiket` TEXT,`usuario` VARCHAR (20),`status` VARCHAR (20), `asignacion` VARCHAR (20),`nombre` VARCHAR (50),PRIMARY KEY(`id`)) TYPE = MyISAM";
		$this->db->simple_query($mSQL);
	
		// tiketservi.php
		// tmenus.php
		// trabajo.php
		// usuarios.php

		$mSQL="ALTER TABLE `usuario` ADD `us_clave1` CHAR(12) NULL AFTER `us_clave`";
		$this->db->simple_query($mSQL);
		$mSQL="UPDATE usuario SET us_clave1=us_clave";
		$this->db->simple_query($mSQL);
	
		// valores.php
		// xml.php
		// agregarfac.php

		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// agregarnd.php

		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// agregarne.php
		// agregaroc.php

		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// agregaroi.php

		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// agregarped.php

		$mSQL='ALTER TABLE itpfac ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// agregarpre.php

		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	
		// analisis.php
		// analisisventas.php
		// anuales.php
		// autoservicio.php
		// caja.php
		// calcomi.php
		// clientes.php
		// cobrocli.php

		$seniat='http://www.seniat.gov.ve/BuscaRif/BuscaRif.jsp';
		$mSQL="INSERT INTO valores (nombre,valor,descrip) VALUES ('CONSULRIF','$seniat','Pagina de consulta de rif del seniat') ON DUPLICATE KEY UPDATE valor='$seniat'";
		$this->db->simple_query($mSQL);
	
		// dine.php
		// exportar.php
		// factura.php
		// fiscalz.php

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
	
		// ganancias.php
		// grcl.php
		// lsfacdesp.php
		// lsitemsdesp.php
		// mensuales.php
		// metas.php

		$mSQL="CREATE TABLE IF NOT EXISTS `metas` (
		  `codigo` varchar(15),
		  `cantidad` decimal(12,3),
		  `fecha` int(10)test,
		  `vendedor` varchar(5),
		  `tipo` CHAR(2),
		  PRIMARY KEY  (`fecha`,`codigo`,`vendedor`)
		);
		";
		$this->db->simple_query($mSQL);
	
		// notadespacho.php
		// otin.php
		// pagepersistence.php
		// pfac.php
		// presup.php
		// presupuestos.php
		// productos.php
		// prueba.php
		// rcaj.php
		// scaj.php

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
	
		// scli.php

		$seniat='http://www.seniat.gov.ve/BuscaRif/BuscaRif.jsp';
		$mSQL="INSERT INTO valores (nombre,valor,descrip) VALUES ('CONSULRIF','$seniat','Pagina de consulta de rif del seniat') ON DUPLICATE KEY UPDATE valor='$seniat'";
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `scli` ADD `modifi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL AFTER `mensaje`';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `scli` DROP PRIMARY KEY, ADD UNIQUE `cliente` (`cliente`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE scli ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
                
	
		// sfacdespfyco.php

		$mSQL="ALTER TABLE `sitems` ADD `cdespacha` DECIMAL NULL";
		$mSQL1="ALTER TABLE `sitems` ADD `ultidespachado` DECIMAL NULL";
		$this->db->simple_query($mSQL);
		$this->db->simple_query($mSQL1);
		echo 'Instalado';
	
		// sfacdesp.php
		// sfacpaga.php
		// snte.php
		// supercrud.php
		// tarjeta.php
		// terminal.php
		// traer.php

		$mSQL='ALTER TABLE `sucu` ADD `url` VARCHAR(200) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `proteo` VARCHAR(50) NULL';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sucu` ADD `prefijo` CHAR(1) NULL';
		$this->db->simple_query($mSQL);
	
		// validaciones.php
		// vendedores.php
		// vend.php
		// ventas.php
		// vpresupuesto.php
		// xlspresupuesto.php
		// zona.php
	}

	function hostname(){
		echo $this->db->hostname; 
	}

	function username(){
		echo $this->db->username; 
	}

	function password(){
		echo $this->db->password;
	}

	function database(){
		echo $this->db->database;
	}}
?>
