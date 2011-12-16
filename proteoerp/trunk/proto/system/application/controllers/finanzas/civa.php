<?php
//cambioiva
class Civa extends Controller {
	var $data_type = null;
	var $data = null;
	 
	function civa(){
		parent::Controller(); 
		$this->load->helper('url');
		$this->load->helper('text');
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
   }
  function index(){
    $this->datasis->modulo_id(509,1);
    	redirect("finanzas/civa/filteredgrid");
   }
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de Cambio de IVA", 'civa');
		
		$filter->fecha = new dateonlyField("Fecha", "fecha",'d/m/Y');
		$filter->fecha->clause  =$filter->fecha->clause="where";
		$filter->fecha->size=12;
		
		$filter->tasa= new inputField("Tasa","Tasa");
		$filter->tasa->size=12;
		$filter->tasa->maxlength=6;
	 	
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/civa/dataedit/show/<#fecha#>','<dbdate_to_human><#fecha#></dbdate_to_human>');

		$grid = new DataGrid("Lista de Cambio de IVA");
		$grid->order_by("fecha","asc");
		$grid->per_page = 20;

		$grid->column("Fecha",$uri);
		$grid->column("Tasa","tasa");
		$grid->column("Tasa Reducida","redutasa");
		$grid->column("Tasa Adicional","sobretasa");
			  	  						
		$grid->add("finanzas/civa/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cambio de Iva</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		$this->rapyd->load("dataedit");

		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';

		$edit = new DataEdit("Cambio de IVA", "civa");
		$edit->back_url = site_url("finanzas/civa/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateField("Fecha", "fecha");
		$edit->fecha->mode="autohide";
		$edit->fecha->rule= 'required';
		//$edit->fecha->rule= 'required|callback_chexiste';
		$edit->fecha->size = 12;
		
		$edit->tasa= new inputField("Tasa", "tasa");
		$edit->tasa->size =8;
		$edit->tasa->maxlength=6;
		$edit->tasa->rule= "trim|required|numeric";
		$edit->tasa->css_class='inputnum';
		
		$edit->redutasa = new inputField("Tasa Reducida", "redutasa");
		$edit->redutasa->size =8;
		$edit->redutasa->maxlength=6;
		$edit->redutasa->css_class='inputnum';
		$edit->redutasa->rule='trim|numeric';
		
		$edit->sobretasa =new inputField("Tasa Adicional", "sobretasa");
		$edit->sobretasa->size =8;
		$edit->sobretasa->maxlength=6;
		$edit->sobretasa->css_class='inputnum';
		$edit->sobretasa->rule='trim|numeric';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
 
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Cambio de Iva</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$fecha=$do->get('fecha');
		$tasa=$do->get('tasa');
		logusu('civa',"CAMBIO DE IVA $fecha TASA $tasa CREADO");
	}
	function _post_update($do){
		$fecha=$do->get('fecha');
		$tasa=$do->get('tasa');
		logusu('civa',"CAMBIO DE IVA $fecha TASA $tasa MODIFICADO");
	}
	function _post_delete($do){
		$fecha=$do->get('fecha');
		$tasa=$do->get('tasa');
		logusu('civa',"CAMBIO DE IVA $fecha TASA $tasa  ELIMINADO ");
	}
	function chexiste($fecha){
		$fecha=$this->input->post('fecha');
		//echo 'aquiii'.$fecha;
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM civa WHERE fecha='$fecha'");
		if ($chek > 0){
			$tasa=$this->datasis->dameval("SELECT tasa FROM civa WHERE fecha='$fecha'");
			$this->validation->set_message('chexiste',"La fecha $fecha ya existe para la tasa $tasa");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>