<?php
class sucu extends Controller{

	function sucu(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index(){
		$this->datasis->modulo_id('90D',1);
		redirect('supervisor/sucu/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro de Sucursales", 'sucu');

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=10;

		$filter->sucursal= new inputField("Sucursal","sucursal");
		$filter->sucursal->size=20;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('supervisor/sucu/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Sucursales");
		$grid->order_by("codigo","asc");
		$grid->per_page=15;

		$grid->column("Sucursal",$uri);
		$grid->column("Nombre","sucursal");
		$grid->column("URL","url");
		$grid->column("Prefijo","prefijo");
		$grid->column("Proteo","proteo");
		$grid->add("supervisor/sucu/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Sucursal');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Sucursal','sucu');
		$edit->back_url = site_url('supervisor/sucu/filteredgrid');

		$edit->codigo = new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->rule = 'required';
		$edit->codigo->mode = 'autohide';
		$edit->codigo->size = 4;
		$edit->codigo->maxlength = 2;

		$edit->sucursal = new inputField('Nombre de la Sucursal','sucursal');
		$edit->sucursal->rule = 'strtoupper';
		$edit->sucursal->size = 60;
		$edit->sucursal->maxlength = 45;

		$edit->url = new inputField('Direcci&oacute;n URL','url');
		$edit->url->size =60;
		$edit->url->maxlength =200;
		$edit->url->append('Ej: www.example.com o www.example.com:8080');

		$edit->prefijo = new inputField('Prefijo','prefijo');
		$edit->prefijo->size = 5;
		$edit->prefijo->maxlength = 3;
		$edit->prefijo->rule='required';
		$edit->prefijo->append('Prefijo de las transacciones en la sucursal');

		$edit->proteo = new inputField('Direcctorio Proteo','proteo');
		$edit->proteo->maxlength =50;

		/*$edit->puerto = new inputField('Puerto http','puerto');
		$edit->puerto->insertValue='80';
		$edit->puerto->maxlength =5;
		$edit->puerto->size      =8;
		$edit->puerto->rule      ='required|numeric';*/

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = heading('Sucursal');
		$data['head']    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function sucubusca() {
		$start    = isset($_REQUEST['start'])  ? $_REQUEST['start']  :  0;
		$limit    = isset($_REQUEST['limit'])  ? $_REQUEST['limit']  : 25;
		$sucursal = isset($_REQUEST['sucursal']) ? $_REQUEST['sucursal'] : '';
		$semilla  = isset($_REQUEST['query'])  ? $_REQUEST['query']  : '';

		$semilla = trim($semilla);
		
		$mSQL = '';
	
		$mSQL = "SELECT codigo item, CONCAT(codigo, ' ', sucursal) valor FROM sucu WHERE codigo IS NOT NULL ";
		if ( strlen($semilla)>0 ){
			$mSQL .= " AND ( codigo LIKE '$semilla%' OR sucursal LIKE '%$semilla%' ) ";
		} else {
			if ( strlen($sucursal)>0 ) $mSQL .= " AND ( codigo LIKE '$sucursal%' OR sucursal LIKE '%$sucursal%' ) ";
		}
		$mSQL .= "ORDER BY sucursal ";
		$results = $this->db->count_all('sucu');

		if ( empty($mSQL)) {
			echo '{success:true, message:"mSQL vacio, Loaded data", results: 0, data:'.json_encode(array()).'}';
		} else {
			$mSQL .= " limit $start, $limit ";
			$query = $this->db->query($mSQL);
			/*
			$arr = array();
			foreach ($query->result_array() as $row)
			{
				$meco = array();
				foreach( $row as $idd=>$campo ) {
					$meco[$idd] = utf8_encode($campo);
				}
				$arr[] = $meco;
			}*/
			$arr = $this->datasis->codificautf8($query->result_array());
			echo '{success:true, message:"'.$mSQL.'", results:'. $results.', data:'.json_encode($arr).'}';
		}
	}

	function instalar(){
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
	}
}
