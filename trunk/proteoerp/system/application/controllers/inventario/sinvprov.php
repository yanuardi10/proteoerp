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
		
		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');
		
		$js='function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}else{
				$("#nom_depto").attr("disabled","");
			}
		}
		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}else{
				$("#nom_linea").attr("disabled","");
			}
		}
		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}else{
				$("#nom_grupo").attr("disabled","");
			}
		}';

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
	
		$select=array('a.proveed','a.codigo','a.codigop','c.nombre','b.descrip','b.marca');

		$filter = new DataFilter2('Filtro de promociones');
		$filter->script($js);
		$filter->db->select($select);
		$filter->db->from('sinvprov AS a');
//		$filter->db->from('sinv AS b');
//		$filter->db->from('sprv AS c');
		$filter->db->join('sinv AS b','a.codigo=b.codigo');
		$filter->db->join('sprv AS c','a.proveed=c.proveed');
		
		$filter->proveed = new inputField("C&oacute;digo de proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		$filter->proveed->db_name   ='a.proveed';
		$filter->proveed->size       =  15;
		$filter->proveed->maxlength  =  15;

		$filter->codigo = new inputField('C&oacute;digo de producto', 'codigo');
		$filter->codigo->db_name   ='a.codigo';
		$filter->codigo->size      = 15;
		$filter->codigo->maxlength = 15;
		$filter->codigo->append($bSINV);
		
		$filter->codigop = new inputField("C&oacute;digo", "codigop");
		$filter->codigop->db_name   ='a.codigop';
		$filter->codigop->size       =  15;
		$filter->codigop->maxlength  =  15;

//		$filter->proveed = new inputField('Proveedor', 'proveed');
//		$filter->proveed->append($bSPRV);
//		$filter->proveed->db_name='b.prov1';
//		$filter->proveed->size=25;

		$filter->depto = new dropdownField('Departamento','depto');
		$filter->depto->db_name='b.depto';
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="b.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$depto=$filter->getval('depto');
		if($depto!==FALSE){
		        $filter->linea2->options("SELECT linea, descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
		        $filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name='c.grupo';
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
		        $filter->grupo->options("SELECT grupo, nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
		        $filter->grupo->option('','Seleccione un Departamento primero');
		}

		$filter->marca = new dropdownField('Marca', 'marca');
		$filter->marca->db_name='b.marca';
		$filter->marca->option('','Todas');
		$filter->marca->options('SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca');
		$filter->marca->style='width:220px;';

		$filter->buttons("reset","search");
		$filter->build();

		$link=anchor('/inventario/sinvprov/dataedit/modify/<#proveed#>/<#codigop#>/<#codigo#>','<#proveed#>');
		$grid = new DataGrid("Lista de proveedores");
		$grid->order_by("proveed","asc");
		$grid->per_page = 15;

		$grid->use_function('str_replace');
		$grid->column_orderby("C&oacute;digo de proveedor",$link,"proveed");
		$grid->column_orderby("Proveedor",'nombre',"nombre");
		$grid->column_orderby("C&oacute;digo de producto",'codigo',"codigo");
		$grid->column_orderby("Descripci&oacute;n",'descrip',"proveed");
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