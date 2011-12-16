<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Sprm extends validaciones {
	
	function sprm(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(206,1);
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/compras/". $this->uri->segment(2).EXT);
	}

	function index(){
		redirect("compras/sprm/filteredgrid");
	}

	function filteredgrid(){

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Proveedores");
		
		$filter->db->select(array('a.proveed','a.nombre','a.rif','SUM((b.tipo_doc IN ("FC","GI","ND"))*(b.monto-b.abonos)) AS sedebe','SUM((b.tipo_doc IN ("AN","NC"))*(b.monto-b.abonos)) AS sepaga'));
		$filter->db->from('sprv AS a');
		$filter->db->join('sprm AS b','a.proveed=b.cod_prv AND b.abonos<b.monto');
		$filter->db->having('sedebe>0');
		$filter->db->groupby("b.cod_prv");
		
		$filter->proveed = new inputField("C&oacute;digo", "proveed");
		$filter->proveed->size=13;
		$filter->proveed->maxlength=5;
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=13;
		$filter->nombre->maxlength=40;
		
		$filter->rif = new inputField("Rif", "rif");
		$filter->rif->size=13;
		$filter->rif->maxlength=12;
		
		$filter->buttons("reset","search");
		$filter->build();

		function resta($a,$b){ return number_format($a-$b,2,',','.'); }
		
		$uri = anchor('compras/sprm/crearpago/<str_replace>/|:slach:|<#proveed#></str_replace>','<#proveed#>');

		$grid = new DataGrid("Lista de Proveedores");
		$grid->use_function('resta','number_format','str_replace');
		$grid->order_by("proveed","asc");
		$grid->per_page = 10;
		
		$grid->column("C&oacute;digo"             ,$uri);
		$grid->column("Nombre"             ,"nombre","nombre");
		$grid->column("R.I.F."             ,"rif" );
		$grid->column("Efectos por pagar"  ,"<number_format><#sedebe#>|2|,|.</number_format>",'align="right"');
		$grid->column("Efectos por aplicar","<number_format><#sepaga#>|2|,|.</number_format>",'align="right"');
		$grid->column("Deuda"              ,"<resta><#sedebe#>|<#sepaga#></resta>" ,'align="right"');
		
		$grid->add("compras/sprv/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Pagos a Proveedores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function crearpago($sprv){
		$mSQL="SELECT tipo_doc,numero,fecha,monto,abonos FROM sprm WHERE cod_prv='$sprv' AND monto>abonos";
		$query = $this->db->query($mSQL);
		$pjson=array();
		
		$aplicables=$pagables=array();
		if ($query->num_rows() > 0){
			foreach ($query->result_array() as $row){
				if(eregi('^(FC|GI|ND)$',$row['tipo_doc'])){
					$row['fecha']=str_replace('-','',$row['fecha']);
					$pagables[]=$row;
					$id=$row['tipo_doc'].$row['numero'].$row['fecha'];
					$pjson[$id] = $row['monto']-$row['abonos'];
				}else
					$aplicables[]=$row;
			}
		}

		$ddata=array('pagables'=>$pagables, 'aplicables'=>$aplicables);
		$ddata['link'] =site_url('compras/sprm/procesapago');
		$ddata['sprv'] =$sprv;
		$ddata['pjson']=json_encode($pjson);
		
		$proveed=$this->datasis->dameval("SELECT nombre FROM sprv WHERE proveed='$sprv'");
		$data['title']   = "<h1>Realizar pago a ($sprv) $proveed</h1>";
		$data["head"]    = style('proteo/proteo.css').script('jquery.js').script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script('jquery-ui.js');
		$data['content'] = $this->load->view('view_sprm',$ddata,true);
		$this->load->view('view_ventanas', $data);
	}
	
	function procesapago(){
		$this->load->model('sprm_model');
		$proveed=$this->input->post('sprm');
		$paga=$this->input->post('paga');
		$efectos=array();
		foreach($paga AS $ind=>$monto){
			$pivot['fecha']   =substr($ind,-8);
			$pivot['tipo_doc']=substr($ind, 0,2);
			$pivot['numero']  =substr($ind, 2,strlen($ind)-8);
			$pivot['monto']   =$monto;
			$efectos[]=$pivot;
		}
		$this->sprm_model->insert($proveed,'AB',$efectos); 
		//print_r($_POST);
	}
}
?>