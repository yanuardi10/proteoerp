<?php
class Straa extends Controller {


	function straa(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(302,1);
    	define ("THISFILE",   APPPATH."controllers/inventario/". $this->uri->segment(2).EXT);
	}
  function index(){
    redirect("inventario/stra/filteredgrid");
  }


	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
		$filter = new DataFilter("Filtro de Transferencias","stra");

		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size=15;
		
		$filter->fecha = new dateonlyField("Fecha", "fecha");
		$filter->fecha->size=12;
		
		$filter->envia = new inputField("Envia", "envia");
		$filter->envia->size=12;
		
		$filter->recibe = new inputField("Recibe", "recibe");		
		$filter->recibe->size=12;
		
		$filter->buttons("reset","search");    
		$filter->build();
		
		$uri = anchor('inventario/stra/dataedit/show/<#numero#>','<#numero#>');
		
		$grid = new DataGrid("Lista de transferencias");
		$grid->order_by("numero","desc");
		$grid->per_page = 5;
		$grid->use_function("substr");
		
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Env&iacute;a","envia","envia");
		$grid->column("Recibe","recibe");
		$grid->column("Observaci&oacute;n","observ1");
		//echo $grid->db->last_query();
		$grid->add("inventario/stra/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Transferencias</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){
		$this->rapyd->load('dataobject','dataedit');  
		$do = new DataObject("stra");  
		$do->rel_one_to_many('itstra', 'itstra', 'numero');
		
		$edit = new DataEdit("Transferencia", $do);
		$edit->back_url = site_url("inventario/stra/filteredgrid");
		
		$edit->numero   = new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size =10;
		$edit->numero->rule = "required";
		
		$edit->fecha    = new  dateonlyField("Fecha",  "fecha");
		$edit->fecha->size =12;
		
		$edit->envia    = new inputField("Env&iacute;a", "envia");
		$edit->envia->size =4;
		
		$edit->recibe   = new inputField("Recibe", "recibe");
		$edit->recibe->size = 4;
		
		$edit->observ1  = new inputField("Observaci&oacute;n 1", "observ1");
		$edit->observ1->size = 35;
		
		$edit->observ2  = new inputField("..", "observ2");
		$edit->observ2->size = 35;
		
		$edit->totalg   = new inputField("Total gr.", "totalg");
		$edit->totalg->size = 17;
		
		$ittota=$edit->_dataobject->count_rel('itstra');
		for($i=1;$i<$ittota;$i++){
			$obj='codigo_'.$i;
			$edit->$obj = new inputField("Codigo ($i)", "codigo_$i");
			$edit->$obj->db_name='codigo';
			$edit->$obj->ind=$i-1;
			$edit->$obj->rel_id='itstra';

			$obj='descrip_'.$i;			
			$edit->$obj = new inputField("Descripcion ($i)", "descrip_$i");
			$edit->$obj->db_name='descrip';
			$edit->$obj->ind=$i-1;
			$edit->$obj->rel_id='itstra';
			
			$obj='cantidad_'.$i;
			$edit->$obj = new inputField("Cantidad ($i)", "cantidad_$i");
			$edit->$obj->db_name='cantidad';
			$edit->$obj->ind=$i-1;
			$edit->$obj->rel_id='itstra';
			
			for($o=1;$o<5;$o++){
				$nombre='precio'.$o;
				$obj=$nombre.'_'.$i;
				
				$edit->$obj = new inputField("Precio $o ($i)", $nombre);
				$edit->$obj->db_name=$nombre;
				$edit->$obj->ind=$i-1;
				$edit->$obj->rel_id='itstra';
			}
		}
		
		$edit->buttons("modify", "save", "undo", "delete", "back"); 
		$edit->build();
		
		//$art_one = $do->get_all();
		//print_r($do->data_rel);
		
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Transferencias</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data); 
  }
}
?>