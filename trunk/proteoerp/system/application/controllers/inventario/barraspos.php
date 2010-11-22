<?php
class barraspos extends Controller {
	function barraspos(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		//$this->datasis->modulo_id(312,1);
		redirect("inventario/barraspos/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");

		$mSPRV=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;odigo',
			'descrip'=>'Descripci&oacute;n',
			'descrip2'=>'Descripci&oacute;n 2'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar Codigo');
		$bSPRV=$this->datasis->modbus($mSPRV);

		$filter = new DataFilter2("Filtro por Producto", 'barraspos');
		
		$filter->codigo = new inputField("C&oacute;digo de producto", "codigo");
		$filter->codigo->append($bSPRV);
		$filter->codigo->size       =  15;
		$filter->codigo->maxlength  =  15;

		$filter->suplemen = new inputField("C&oacute;digo de barras", "suplemen");
		$filter->suplemen->size       =  15;
		$filter->suplemen->maxlength  =  15;

		
		$filter->buttons("reset","search");
		$filter->build();

		$link=anchor('/inventario/barraspos/dataedit/modify/<#codigo#>/<#suplemen#>','<#codigo#>');
		$grid = new DataGrid("Lista de Art&iacute;culos");
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;

		$grid->use_function('str_replace');
		$grid->column("C&oacute;digo",$link);
		$grid->column("Barras","suplemen");

		$grid->add("inventario/barraspos/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Lista de Art&iacute;culos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit() {
		$this->rapyd->load('dataedit');
		
		$mSPRV=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;odigo',
			'descrip'=>'Descripci&oacute;n',
			'descrip2'=>'Descripci&oacute;n 2'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo'),
			'titulo'  =>'Buscar Codigo');
		$bSPRV=$this->datasis->modbus($mSPRV);
		

		$edit = new DataEdit("barras de Inventario", "barraspos");
		$edit->back_url = site_url("inventario/barraspos/filteredgrid/");
		
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size       =  15;
		$edit->codigo->maxlength  =  15;
		$edit->codigo->rule 			= "required";
		$edit->codigo->append($bSPRV);
		

		$edit->barras = new inputField("Barras", "suplemen");
		$edit->barras->size      =  15;
		$edit->barras->maxlength =  15;
		$edit->barras->rule      =  "required";
		
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>C&oacute;digo Barras de Inventario</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function instala(){
		$mSQL="CREATE TABLE IF NOT EXISTS `barraspos` (
  			`codigo` char(15) NOT NULL DEFAULT '',
  			`suplemen` char(15) NOT NULL DEFAULT '',
  		PRIMARY KEY (`codigo`,`suplemen`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1
		";
		$this->db->query($mSQL);
		
	}
}
?>