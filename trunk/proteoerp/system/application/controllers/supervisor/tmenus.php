<?php
class tmenus extends Controller { 	
	function tmenus(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(307,1);
	}
	
	function index(){
		redirect("supervisor/tmenus/filteredgrid");
	}
	 
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
		$atts = array(
              'width'      => '800',
              'height'     => '600',
              'scrollbars' => 'yes',
              'status'     => 'yes',
              'resizable'  => 'yes',
              'screenx'    => '0',
              'screeny'    => '0'
            );
				
		$filter = new DataFilter("Filtro de Menu de Datasis","tmenus");
						
		$filter->modulo = new inputField("Modulo", "modulo");
		$filter->modulo->db_name='modulo';
		$filter->modulo->size=20;
		
		$filter->titulo = new inputField("Titulo","titulo");
		$filter->titulo->size=30;
		$filter->titulo->db_name='titulo';
		
		$filter->ejecutar = new inputField("Ejecutar","ejecutar");
		$filter->ejecutar->size=20;
		$filter->ejecutar->db_name='ejecutar';
			
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('supervisor/tmenus/dataedit/show/<#codigo#>','<#modulo#>');
		$export = anchor('supervisor/tmenus/xmlexport','Exportar Data');
		$import = anchor_popup('cargasarch/cargaxml','Importar Data',$atts);
		
		$grid = new DataGrid("Lista de Menu de Datasis");
		$grid->order_by("modulo","asc");
		$grid->per_page = 15;
		
		$grid->column("Modulo",$uri);
		$grid->column("Titulo","titulo" );
		$grid->column("Ejecutar","ejecutar" );
								
		$grid->add("supervisor/tmenus/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$data['content'] = $filter->output.$export.'  ---->  '.$import.'<form>'.$grid->output.'</form>';
		$data['title']   = "<h1>Menu del Sistema</h1>";
		$data["head"]    = script("jquery.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function DataEdit(){
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Agregar Menu", "tmenus");
		$edit->back_url = site_url("supervisor/tmenus/filteredgrid");
	
		$edit->modulo = new inputField("Modulo","modulo");
		$edit->modulo->size=12;
		$edit->modulo->maxlength=10;
		
		$edit->secu = new inputField("Secuencia","secu");
		$edit->secu->size=6;
		$edit->secu->maxlength=5;

		$edit->titulo = new inputField("Titulo","titulo");
		$edit->titulo->size=25;
		$edit->titulo->maxlength=20;   
		
		$edit->mensaje = new textareaField("Mensaje","mensaje");
		$edit->mensaje->rows = 4;
		$edit->mensaje->cols=90;
		
		$edit->ejecutar = new inputField("Ejecutar","ejecutar");
		$edit->ejecutar->size=80;
		$edit->ejecutar->maxlength=80; 

		$edit->buttons("modify", "save", "undo","back");
		$edit->build();

		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Menu de Datasis</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}
	function xmlexport(){
		$this->load->helper('download');

		$this->load->library("xmlinex");
		$data[]=array('table'  =>'tmenus');
		$data=$this->xmlinex->export($data);
		$name = 'tmenus.xml';
		force_download($name, $data); 
	}
}
?>