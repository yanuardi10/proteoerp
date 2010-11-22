<?php
class sinvpromo extends Controller {
	function sinvpromo(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		//$this->datasis->modulo_id(312,1);
		redirect("inventario/sinvpromo/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");

		$filter = new DataFilter2("Filtro", 'sinvpromo');
		
		$filter->id = new inputField("Id", "id");
		$filter->id->size       =  15;
		$filter->id->maxlength  =  15;
		
		$filter->codigo = new inputField("C&oacute;digo de producto", "codigo");
		$filter->codigo->size       =  15;
		$filter->codigo->maxlength  =  15;

		$filter->buttons("reset","search");
		$filter->build();

		$link=anchor('/inventario/sinvpromo/dataedit/modify/<#id#>','<#codigo#>');
		$grid = new DataGrid("Lista de Art&iacute;culos");
		$grid->order_by("id","asc");
		$grid->per_page = 15;

		$grid->use_function('str_replace');
		$grid->column("C&oacute;digo",$link);
		$grid->column("Margen","margen");
		$grid->column("Cantidad","cantidad");

		$grid->add("inventario/sinvpromo/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit() {
		$this->rapyd->load('dataedit');
		
		$script='
		<script language="javascript" type="text/javascript">
		$(function(){
				$(".inputnum").numeric(".");
		});
		</script>';
		
		$edit = new DataEdit("Promo", "sinvpromo");
		$edit->back_url = site_url("inventario/sinvpromo/filteredgrid/");
		
		
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size       =  15;
		$edit->codigo->css_class ='inputnum';
		$edit->codigo->maxlength  =  15;
		$edit->codigo->rule 			= "required";
		
		$edit->margen = new inputField("Margen", "margen");
		$edit->margen->size      =  15;
		$edit->margen->maxlength =  15;
		$edit->margen->css_class ='inputnum';
		$edit->margen->rule      =  "required";
		
		$edit->cantidad = new inputField("Cantidad", "cantidad");
		$edit->cantidad->size      =  15;
		$edit->cantidad->maxlength =  15;
		$edit->cantidad->css_class ='inputnum';
		$edit->cantidad->rule      =  "required";
		
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data["head"]    = script('jquery.js').script('jquery-ui.js').script("plugins/jquery.numeric.pack.js").script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().$script;
		$data['title']   = "<h1>C&oacute;digo Barras de Inventario</h1>";
		//$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function instala(){
		$mSQL="CREATE TABLE IF NOT EXISTS `sinvpromo` (
				  `id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `codigo` bigint(20) DEFAULT NULL,
				  `margen` decimal(18,2) DEFAULT NULL,
				  `cantidad` decimal(18,3) DEFAULT NULL,
				  PRIMARY KEY (`id`),
				  KEY `codigo` (`codigo`)
				) ENGINE=MyISAM AUTO_INCREMENT=1177 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		";
		$this->db->query($mSQL);
		
	}
}
?>