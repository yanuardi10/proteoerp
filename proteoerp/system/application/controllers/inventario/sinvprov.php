<?php
class sinvprov extends Controller {
	function sinvprov(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		//$this->datasis->modulo_id(312,1);
		redirect("inventario/sinvprov/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");

		$mSINV=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;odigo',
			'descrip'=>'Descripci&oacute;n',
			'descrip2'=>'Descripci&oacute;n 2'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar Codigo');
		$bSINV=$this->datasis->modbus($mSINV);
		
		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;odigo',
			'nombre'=>'Nombre',
			'rif'=>'Rif'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Codigo');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$filter = new DataFilter2("Filtro por Proveedor", 'sinvprov');
		
		$filter->proveed = new inputField("C&oacute;digo de proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		$filter->proveed->size       =  15;
		$filter->proveed->maxlength  =  15;
		
		$filter->codigo = new inputField("C&oacute;digo de producto", "codigo");
		$filter->codigo->append($bSINV);
		$filter->codigo->size       =  15;
		$filter->codigo->maxlength  =  15;
		
		$filter->codigop = new inputField("C&oacute;digo", "codigop");
		$filter->codigop->size       =  15;
		$filter->codigop->maxlength  =  15;

				
		$filter->buttons("reset","search");
		$filter->build();

		$link=anchor('/inventario/sinvprov/dataedit/modify/<#proveed#>/<#codigop#>/<#codigo#>','<#proveed#>');
		$grid = new DataGrid("Lista de proveedores");
		$grid->order_by("proveed","asc");
		$grid->per_page = 15;

		$grid->use_function('str_replace');
		$grid->column_orderby("C&oacute;digo de proveedor",$link,"proveed");
		$grid->column_orderby("C&oacute;digo de producto",'codigo',"codigo");
		$grid->column_orderby("C&oacute;digo","codigop","codigop");

		$grid->add("inventario/sinvprov/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Lista de Proveedores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit() {
		$this->rapyd->load('dataedit');
		
		$mSINV=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;odigo',
			'descrip'=>'Descripci&oacute;n',
			'descrip2'=>'Descripci&oacute;n 2'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar Codigo');
		$bSINV=$this->datasis->modbus($mSINV);
		
		$mSPRV=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;odigo',
			'nombre'=>'Nombre',
			'rif'=>'Rif'),
			'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Codigo');
		$bSPRV=$this->datasis->modbus($mSPRV);
		

		$edit = new DataEdit("Sinvprov", "sinvprov");
		$edit->back_url = site_url("inventario/sinvprov/filteredgrid/");
		
		$edit->proveed = new inputField("C&oacute;digo de proveedor", "proveed");
		$edit->proveed->size       =  15;
		$edit->proveed->maxlength  =  15;
		$edit->proveed->rule 			= "required";
		$edit->proveed->append($bSPRV);
		

		$edit->codigo = new inputField("C&oacute;digo de producto", "codigo");
		$edit->codigo->size      =  15;
		$edit->codigo->maxlength =  15;
		$edit->codigo->rule      =  "required";
		$edit->codigo->append($bSINV);
		
		$edit->codigop = new inputField("C&oacute;digo", "codigop");
		$edit->codigop->size      =  15;
		$edit->codigop->maxlength =  15;
		$edit->codigop->rule      =  "required";
		
		
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>C&oacute;digo Barras de Inventario</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function instala(){
		$mSQL="CREATE TABLE IF NOT EXISTS `sinvprov` (
			  `proveed` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `codigop` char(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `codigo` char(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  PRIMARY KEY (`proveed`,`codigop`,`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		";
		$this->db->query($mSQL);
		
	}
}
?>