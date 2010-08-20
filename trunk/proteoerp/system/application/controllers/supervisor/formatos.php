<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class formatos extends validaciones { 	
	function formatos(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(307,1);
	}
	
	function index(){
		redirect("supervisor/formatos/filteredgrid");
	}
	 
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
				
		$filter = new DataFilter("Filtro por Menu de Formatos","formatos");
						
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->db_name='nombre';
		$filter->nombre->size=20;
		
		$filter->proteo = new inputField("Contenido Proteo","proteo");
		$filter->proteo->size=40;
		$filter->proteo->db_name='proteo';
		
		$filter->reporte = new inputField("Contenido Datasis","forma");
		$filter->reporte->size=40;
		$filter->reporte->db_name='forma';
		
		$filter->harbourd = new inputField("Contenido Harbourd","harbour");
		$filter->harbourd->size=40;
		$filter->harbourd->db_name='harbour';
		
		$filter->buttons("reset","search");
		$filter->build();
		$uri  = anchor("supervisor/formatos/dataedit/show/<#nombre#>",'<#nombre#>');
		$uri1 = anchor('supervisor/formatos/reporte/modify/<#nombre#>/' ,'Editar');
		$uri2 = anchor('supervisor/formatos/rdatasis/modify/<#nombre#>/','Editar');
		$uri3 = anchor('supervisor/formatos/rharbour/modify/<#nombre#>/','Editar');
		$uri4 = anchor('supervisor/formatos/observa/modify/<#nombre#>/','Editar');
		$uri5 = anchor('supervisor/formatos/rtcpdf/modify/<#nombre#>/','Editar');

		
		$grid = new DataGrid("Lista de Menu de Formatos");
		$grid->order_by("nombre","asc");
		$grid->per_page = 15;
		
		$grid->column("Nombre",    $uri);
  		$grid->column("Proteo"   ,$uri1);
		$grid->column("DataSIS"  ,$uri2);
		$grid->column("Harbour"  ,$uri3);
		$grid->column("TCPDF"    ,$uri5);

		//$grid->column("Observa"  ,$uri4);
		//$grid->column("Ejecutar" ,$uri4);
								
		$grid->add("supervisor/formatos/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$url=site_url('supervisor/formatos/cactivo');
		$data['script']='<script type="text/javascript">
		$(document).ready(function() {
			$("form :checkbox").click(function () {
           $.ajax({
					  type: "POST",
					  url: "'.$url.'",
					  data: "codigo="+this.name,
					  success: function(msg){
					  	if (msg==0)
					    	alert("Ocurrio un problema");
					  }
					});
        }).change();
		});
		</script>';
		$data['content'] = $filter->output.'<form>'.$grid->output.'</form>';
		$data['title']   = "<h1>Menu de Formatos</h1>";
		$data["head"]    = script("jquery.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function observa($nombre){
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Agregar Observacion", "formatos");
		$edit->back_url = site_url("supervisor/formatos/filteredgrid");
		
		$edit->observa= new textareaField("", "observa");
		$edit->observa->rows =3;
		$edit->observa->cols=70;

		$edit->buttons("modify", "save", "undo","back");
		$edit->build();

		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Observaciones</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}

	function reporte(){
		$nombre=$this->uri->segment(5);
		$this->rapyd->load("dataedit");
		//$atts = array(
		//          'width'      => '800',
		//          'height'     => '600',
		//          'scrollbars' => 'yes',
		//          'status'     => 'yes',
		//          'resizable'  => 'yes',
		//          'screenx'    => '0',
		//          'screeny'    => '0'
		//        );
   //
		//$uri2=anchor_popup("reportes/ver/$nombre", 'Probar reporte', $atts);
		//$uri3=anchor_popup("supervisor/mantenimiento/centinelas", 'Centinela', $atts);
		
		$edit = new DataEdit("Proteo", "formatos");
		$edit->back_url = site_url("supervisor/formatos/filteredgrid");
		
		$edit->proteo= new textareaField("", "proteo");
		$edit->proteo->rows =30;
		$edit->proteo->cols=120;

		$edit->buttons("modify", "save", "undo","back");
		$edit->build();

		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Formato '$nombre'</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);  
	}
	
	function rtcpdf($status,$nombre){
                $this->rapyd->load("dataedit");

                $edit = new DataEdit('Editar TCPDF', "formatos");
                $edit->back_url = site_url("supervisor/formatos/filteredgrid");

                //highlight_string()
                $edit->tcpdf= new textareaField("", "tcpdf");
                $edit->tcpdf->rows =30;
                $edit->tcpdf->cols=120;

                $edit->buttons("modify", "save", "undo", "delete", "back");
                $edit->build();

                $data['content'] = $edit->output;
                $data['title']   = "<h1>Reporte TCPDF</h1>";
                $data["head"]    = $this->rapyd->get_head();
                $this->load->view('view_ventanas_sola', $data);
        }	


	function rdatasis(){
		$nombre=$this->uri->segment(5);
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("DataSIS", "formatos");
		$edit->back_url = site_url("supervisor/formatos/filteredgrid");
		
		$edit->reporte= new textareaField("", "forma");
		$edit->reporte->rows =30;
		$edit->reporte->cols=120;
		$edit->reporte->rule = "callback_eollw";

		$edit->buttons("modify", "save", "undo","back");
		$edit->build();

		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Formato '$nombre'</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);  
	}
	
	function rharbour(){
		$nombre=$this->uri->segment(5);
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Harbour", "formatos");
		$edit->back_url = site_url("supervisor/formatos/filteredgrid");
		
		$edit->reporte= new textareaField("", "harbour");
		$edit->reporte->rows =30;
		$edit->reporte->cols=120;
		$edit->reporte->rule = "callback_eollw";

		$edit->buttons("modify", "save", "undo","back");
		$edit->build();

		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Formato '$nombre'</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);  
	}
	function dataedit(){
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Formatos", "formatos");
		$edit->back_url = site_url("supervisor/formatos/filteredgrid");
		
		$edit->nombre = new inputField("Nombre","nombre");
		$edit->nombre->rule= "strtoupper|required";
		$edit->nombre->size = 20;
		
		//$edit->forma = new inputField("modulo","forma");
		//$edit->forma->size =20;
		//$edit->forma->rule= "strtoupper|required";
		//
  	//$edit->proteo = new inputField("modulo","proteo");
		//$edit->proteo->size =20;
		//$edit->proteo->rule= "strtoupper|required";
		//
		//$edit->habour = new inputField("modulo","habour");
		//$edit->habour->size =20;
		//$edit->habour->rule= "strtoupper|required";
		//
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Agregar Formatos</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}
	function cactivo(){
		$codigo=$this->input->post('codigo');
		if(!empty($codigo)){
			$pk=explode('|',$codigo);
			$mSQL="UPDATE intrarepo SET activo=IF(activo='S','N','S') WHERE nombre='$pk[0]' AND modulo='$pk[1]'";
			echo $this->db->simple_query($mSQL);
		}else{
			echo 0;
		}
	}

	function _post_insert($do){
		$nombre=$do->get('nombre');
		$mSQL="INSERT IGNORE INTO `reportes` (nombre) VALUES ('$nombre')";
		$this->db->simple_query($mSQL);
		logusu('formatos',"CREADO EL REPORTE $nombre");
	}
	
	function _post_delete($do){
		$nombre=$do->get('nombre');
		$mSQL="DELETE FROM `reportes` WHERE `nombre`='$nombre'";
		$this->db->simple_query($mSQL);
		logusu('formatos',"BORRADO EL REPORTE $nombre");
	}
	
	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `intrarepo` (
		  `nombre` varchar(71) NOT NULL default '',
		  `modulo` varchar(10) NOT NULL default '',
		  `titulo` varchar(20) default NULL,
		  `mensaje` varchar(60) default NULL,
		  `activo` char(1) default 'S',
		  PRIMARY KEY  (`nombre`,`modulo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
		
	}
}
?>
