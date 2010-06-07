<?php
class Repodupli extends Controller{
	
	function repodupli(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id('918',1);
	}

	function index(){		
		redirect("supervisor/repodupli/filteredgrid");
	}
	
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		
		$filter = new DataFilter("Filtro de Reportes Duplicados", 'repodupli');

		$filter->reporte1 = new inputField("Reporte Original","reporte1");
		$filter->reporte1->size=20;
		
		$filter->reporte2 = new inputField("Reporte Duplicado","reporte2");
		$filter->reporte2->size=20;
		
		$filter->comentario = new inputField("Comentario","comentario");
		$filter->comentario->size=30;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('supervisor/repodupli/dataedit/show/<#reporte1#>/<#reporte2#>','<#reporte1#>');

		$grid = new DataGrid("Lista de Reportes Duplicados");
		$grid->order_by("reporte1","asc");
		$grid->per_page = 20;

		$grid->column("Reporte Original",$uri);
		$grid->column("Reporte Duplicado","<#reporte2#>");
		$grid->column("Comentario","<#comentario#>");
		$grid->column("Status Original","<#status1#>","align='center'");
		$grid->column("Status Duplicado","<#status2#>","align='center'");

		$grid->add("supervisor/repodupli/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>An&aacute;lisis de Reportes</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit()
 	{
		$this->rapyd->load("dataedit");
		
		//$mREPORTES1=array(
		//	'tabla'   =>'reportes',
		//	'columnas'=>array(
		//		'nombre' =>'nombre',
		//		'descrip'=>'Descripci&oacute;n'),
		//	'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
		//	'retornar'=>array('codigo'=>'cuenta'),
		//	'titulo'  =>'Buscar Cuenta',
		//	'where'=>"codigo LIKE \"$qformato\"",
		//	);					
		//		
		//$bREPORTES1 =$this->datasis->modbus($mREPORTES1);

		$edit = new DataEdit("Reporte Duplicado", "repodupli");

		$edit->back_url = site_url("supervisor/repodupli/filteredgrid");
		
		//$edit->reporte1 =  new inputField("Reporte Original", "reporte1");
		//$edit->reporte1->size = 20;
		//$edit->reporte1->maxlength=20;
		//$edit->reporte1->rule = "trim|strtoupper|required";
		//$edit->reporte1->append($bREPORTES1);
		
		$edit->reporte1 = new dropdownField("Reporte Original", "reporte1");
		$edit->reporte1->option("","");
		$edit->reporte1->options("SELECT nombre, nombre as reporte FROM reportes ORDER BY nombre");
		$edit->reporte1->rule="required";
		$edit->reporte1->style ="width:150px;";
		
		$edit->reporte2 = new dropdownField("Reporte Duplicado", "reporte2");
		$edit->reporte2->option("","");
		$edit->reporte2->options("SELECT nombre, nombre as reporte FROM reportes ORDER BY nombre");
		$edit->reporte2->rule="required";
		$edit->reporte2->style ="width:150px;";
		
		//$edit->reporte2 =  new inputField("Reporte Duplicado", "reporte2");
		//$edit->reporte2->size = 20;
		//$edit->reporte2->maxlength=20;
		//$edit->reporte2->rule = "trim|strtoupper|required";
		
		$edit->comentario =  new textareaField("Comentario", "comentario");
		$edit->comentario->cols = 70;  
		$edit->comentario->rows = 4;   
		$edit->comentario->rule = "trim";
		
		$edit->status1 = new dropdownField("Status","status1");
		$edit->status1->option("","");
		$edit->status1->option("E","Se Elimina");
		$edit->status1->option("Q","Se Queda");
		$edit->status1->option("O","Otro");
		$edit->status1->style ="width:150px;";
		$edit->status1->in='reporte1';
		
		$edit->status2 = new dropdownField("Status","status2");
		$edit->status2->option("","");
		$edit->status2->option("E","Se Elimina");
		$edit->status2->option("Q","Se Queda");
		$edit->status2->option("O","Otro");
		$edit->status2->style ="width:150px;";
		$edit->status2->in='reporte2';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
    $data['title']   = "<h1>An&aacute;lisis de Reportes</h1>";
    $data['head']    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);
    
	}
	function instalar(){
		$mSQL="ALTER TABLE `repodupli` ADD `status` CHAR(2) NULL";
		$this->db->query($mSQL);
	}
	function copia(){
		$mSQL="ALTER TABLE `repodupli` ADD `status` CHAR(2) NULL";
		$this->db->query($mSQL);
	}
}
?>