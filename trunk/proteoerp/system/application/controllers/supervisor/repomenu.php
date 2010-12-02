<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class repomenu extends validaciones  { 	
	function repomenu(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(307,1);
	}
	
	function index(){
		redirect("supervisor/repomenu/filteredgrid");
	}
	 
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
		function llink($nombre,$alternativo,$modulo){
			if(!empty($nombre))
				$uri  = anchor("supervisor/repomenu/dataedit/show/$nombre/$modulo",$nombre);
			else
				$uri  = anchor("supervisor/repomenu/dataedit/$alternativo/create",$alternativo);	
			return $uri;
		}
		
		function ractivo($nombre,$activo,$modulo){
			if(!empty($activo)){
				$bandera= ($activo=='S') ? 1: 0;
				$retorna = form_checkbox("$nombre|$modulo", 'accept', $bandera);
			}else{
				$retorna  = "NI";
			}
			return $retorna ;
		}
		
		$filter = new DataFilter("Filtro por Menu de Reportes");
		
		$select=array('b.nombre AS alternativo','a.nombre','a.modulo','a.titulo','a.mensaje','a.activo','b.reporte','b.proteo','b.harbour');
		$filter->db->select($select);
		$filter->db->from('intrarepo AS a');
		$filter->db->join("reportes AS b","a.nombre=b.nombre",'RIGHT');
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->db_name='b.nombre';
		$filter->nombre->size=20;
		
		$filter->modulo = new dropdownField("modulo","modulo");
		$filter->modulo->option("","Todos");
		$filter->modulo->options("SELECT modulo,modulo as value FROM intrarepo GROUP BY modulo");
		$filter->modulo->style='width:130px';
		
		$filter->titulo = new inputField("Titulo","titulo");
		$filter->titulo->size=30;
		
		$filter->activo = new dropdownField("Activo","activo");
		$filter->activo->option("","Todos");
		$filter->activo->option("S","Si");
		$filter->activo->option("N","No");
		$filter->activo->style='width:80px';
		
		$filter->proteo = new inputField("Contenido Proteo","proteo");
		$filter->proteo->size=40;
		$filter->proteo->db_name='b.proteo';
		
		$filter->reporte = new inputField("Contenido Datasis","reporte");
		$filter->reporte->size=40;
		$filter->reporte->db_name='b.reporte';
		
		$filter->harbourd = new inputField("Contenido Harbourd","harbourd");
		$filter->harbourd->size=40;
		$filter->harbourd->db_name='b.harbour';
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri1 = anchor('supervisor/repomenu/reporte/modify/<#alternativo#>/' ,'Editar');
		$uri2 = anchor('supervisor/repomenu/rdatasis/modify/<#alternativo#>/','Editar');
		$uri3 = anchor('supervisor/repomenu/rharbour/modify/<#alternativo#>/','Editar');
		$uri5 = anchor('supervisor/repomenu/rtcpdf/modify/<#alternativo#>/','Editar');
		
		$atts = array(
		  'width'      => '800',
		  'height'     => '600',
		  'scrollbars' => 'yes',
		  'status'     => 'yes',
		  'resizable'  => 'yes',
		  'screenx'    => '0',
		  'screeny'    => '0'
		);
		
		$uri4=anchor_popup('reportes/ver/<#alternativo#>/<#modulo#>', 'Probar', $atts);
		
		$grid = new DataGrid("Lista de Menu de Reportes");
		$grid->use_function('llink','ractivo');
		$grid->order_by("nombre","asc");
		$grid->per_page = 15;
		
		$grid->column("Nombre",'<llink><#nombre#>|<#alternativo#>|<#modulo#></llink>');
		$grid->column("Modulo","modulo");
		$grid->column("Titulo","titulo");
		$grid->column("Mensaje","mensaje");
		$grid->column("Activo","<ractivo><#alternativo#>|<#activo#>|<#modulo#></ractivo>","align='center'");
		$grid->column("Proteo"   ,$uri1);
		$grid->column("DataSIS"  ,$uri2);
		$grid->column("Harbour"  ,$uri3);
		$grid->column("TCPDF"    ,$uri5);
		$grid->column("Ejecutar" ,$uri4);
								
		$grid->add("supervisor/repomenu/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$url=site_url('supervisor/repomenu/cactivo');
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
		$data['title']   = "<h1>Menu de Reportes</h1>";
		$data["head"]    = script("jquery.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	
	function dataedit($nombre){
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Menu de Reportes", "intrarepo");
		$edit->back_url = site_url("supervisor/repomenu/filteredgrid");
		$edit->post_process('insert','_post_insert');
		//$edit->post_process('delete','_post_delete');
		
		$edit->nombre = new inputField("Nombre","nombre");
		$edit->nombre->mode="autohide";
		$edit->nombre->rule= "strtoupper|required";
		$edit->nombre->size = 20;
		if($nombre!='create') $edit->nombre->insertValue = $nombre;  
		
		$edit->modulo = new inputField("modulo","modulo");
		$edit->modulo->size =20;
		$edit->modulo->rule= "strtoupper|required";
		
		$edit->titulo=new inputField("Titulo","titulo");
		$edit->titulo->size =40;
		
		$edit->mensaje =new inputField("Mensaje", "mensaje");
		$edit->mensaje->size = 50;
		
		$edit->activo = new dropdownField("Activo","activo");
		$edit->activo->option("S","Si");
		$edit->activo->option("N","No");
		$edit->activo->style='width:60px';    
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Repomenu</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}

	function reporte($status,$nombre){
		$this->rapyd->load("dataedit");
		$atts = array(
		          'width'      => '800',
		          'height'     => '600',
		          'scrollbars' => 'yes',
		          'status'     => 'yes',
		          'resizable'  => 'yes',
		          'screenx'    => '0',
		          'screeny'    => '0'
		        );

		$uri2=anchor_popup("reportes/ver/$nombre", 'Probar reporte', $atts);
		$uri3=anchor_popup("supervisor/mantenimiento/centinelas", 'Centinela', $atts);
		
		$edit = new DataEdit($uri2.' '.$uri3, "reportes");
		$edit->back_url = site_url("supervisor/repomenu/filteredgrid");
		
		//highlight_string()
		$edit->proteo= new textareaField("", "proteo");
		$edit->proteo->rows =30;
		$edit->proteo->cols=120;

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Reporte Proteo</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);  
	}

	function rtcpdf($status,$nombre){
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit('Editar TCPDF', "reportes");
		$edit->back_url = site_url("supervisor/repomenu/filteredgrid");
		
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
	
	function rdatasis($status,$nombre){
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Reporte DataSIS", "reportes");
		$edit->back_url = site_url("supervisor/repomenu/filteredgrid");
		
		$edit->reporte= new textareaField("", "reporte");
		$edit->reporte->rows =30;
		$edit->reporte->cols=120;
		$edit->reporte->rule = "callback_eollw";

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Reporte Datasis</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);  
	}
	
	function rharbour($status,$nombre){
		$this->rapyd->load("dataedit");
		
		$edit = new DataEdit("Reporte DataSIS", "reportes");
		$edit->back_url = site_url("supervisor/repomenu/filteredgrid");
		
		$edit->reporte= new textareaField("", "harbour");
		$edit->reporte->rows =30;
		$edit->reporte->cols=120;
		$edit->reporte->rule = "callback_eollw";

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Reporte Harbour</h1>";        
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);  
	}
	
	function cactivo(){
		$codigo=$this->input->post('codigo');
		if(!empty($codigo)){
			$pk=explode('|',$codigo);
			$mSQL="UPDATE intrarepo SET activo=IF(activo='S','N','S') WHERE nombre='$pk[0]' AND modulo='$pk[1]'";
			echo var_dum($this->db->simple_query($mSQL));
		}else{
			echo 0;
		}
	}

	function _post_insert($do){
		$nombre=$do->get('nombre');
		$mSQL="INSERT IGNORE INTO `reportes` (nombre) VALUES ('$nombre')";
		var_dum($this->db->simple_query($mSQL));
		logusu('REPOMENU',"CREADO EL REPORTE $nombre");
	}
	
	function _post_delete($do){
		$nombre=$do->get('nombre');
		$mSQL="DELETE FROM `reportes` WHERE `nombre`='$nombre'";
		var_dum($this->db->simple_query($mSQL));
		logusu('REPOMENU',"BORRADO EL REPORTE $nombre");
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
		var_dum($this->db->simple_query($mSQL));
	}
}
?>