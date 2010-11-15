<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//proveed
class notabu extends validaciones {

	function notabu(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(707,1);
		redirect("nomina/notabu/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->datasis->modulo_id(707,1);


		$filter = new DataFilter("Filtro", 'notabu');
				
		$filter->contrato = new dropdownField("Contrato","contrato");
		$filter->contrato->style ="width:400px;";
		$filter->contrato->option("","");
		$filter->contrato->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
				
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('nomina/notabu/dataedit/show/<#contrato#>/<#ano#>/<#mes#>/<#dia#>','<#contrato#>');

		$grid = new DataGrid("Lista");
		$grid->order_by("contrato","asc");
		$grid->per_page = 20;

		$grid->column("Contrato",$uri);
		$grid->column("Año","ano");
		$grid->column("Mes","mes");
		$grid->column("Dia","dia");
		$grid->column("Preaviso","preaviso","align=right");
		$grid->column("Vacaciones","vacacion","align=right");
		$grid->column("Bono Vacacional","bonovaca","align=right");
		$grid->column("Antiguedad","antiguedad","align=right");
		$grid->column("Utilidades","utilidades","align=right");
	
		//$grid->add("nomina/notabu/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Definición de Utilidades</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	
	function dataedit(){
		
		$this->rapyd->load("dataedit2");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		
		';	
				
		$edit = new DataEdit2(" ", "notabu");
		$edit->back_url = site_url("nomina/notabu/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
					  
		$edit->contrato = new dropdownField("Contrato","contrato");
		$edit->contrato->style ="width:400px;";
		$edit->contrato->option("","");
		$edit->contrato->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
		$edit->contrato->group = "Relaci&oacute;n Laboral";
					
		$edit->ano = new inputField("Año","ano");
		$edit->ano->size =3;
		$edit->ano->maxlength=2;
		$edit->ano->rule="trim|numeric";
		$edit->ano->css_class='inputnum';
		
		$edit->mes = new inputField("Mes","mes");
		$edit->mes->size =3;
		$edit->mes->maxlength=2;
		$edit->mes->rule="trim|numeric";
		$edit->mes-> css_class='inputnum';
		
		$edit->dia = new inputField("Dia","dia");
		$edit->dia->size =3;
		$edit->dia->maxlength=2;
		$edit->dia->rule="trim|numeric";
		$edit->dia-> css_class='inputnum';

		$edit->preaviso = new inputField("Preaviso","preaviso");
		$edit->preaviso->size =9;
		$edit->preaviso->maxlength=7;
		$edit->preaviso->rule="trim|numeric";
		$edit->preaviso-> css_class='inputnum';

		$edit->vacacion = new inputField("Vacaciones","vacacion");
		$edit->vacacion->size =9;
		$edit->vacacion->maxlength=7;
		$edit->vacacion->rule="trim|numeric";
		$edit->vacacion-> css_class='inputnum';
		
		$edit->bonovaca = new inputField("Bono Vacacional","bonovaca");
		$edit->bonovaca->size =9;
		$edit->bonovaca->maxlength=7;
		$edit->bonovaca->rule="trim|numeric";
		$edit->bonovaca-> css_class='inputnum';
		
		$edit->antiguedad = new inputField("Antiguedad","antiguedad");
		$edit->antiguedad->size =9;
		$edit->antiguedad->maxlength=7;
		$edit->antiguedad->rule="trim|numeric";
		$edit->antiguedad-> css_class='inputnum';

		$edit->utilidades = new inputField("Utilidades","utilidades");
		$edit->utilidades->size =9;
		$edit->utilidades->maxlength=7;
		$edit->utilidades->rule="trim|numeric";
		$edit->utilidades-> css_class='inputnum';
		
		$edit->buttons("modify","save", "undo","back");
		$edit->build();
		
		$data['content'] = $edit->output; 
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Definición de Utilidades</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function _post_insert($do){
		$contrato=$do->get('contrato');
		$anio=$do->get('anio');
		$mes=$do->get('mes');
		$dia=$do->get('dia');	
		logusu('notabu',"CONFIGURACION DE NOMINA $contrato $anio $mes $dia  REGISTRADA");
	}
	function _post_update($do){
		$contrato=$do->get('contrato');
		$anio=$do->get('anio');
		$mes=$do->get('mes');
		$dia=$do->get('dia');	
		logusu('notabu',"CONFIGURACION DE NOMINA $contrato $anio $mes $dia  MODIFICADA");
	}
	function _post_delete($do){
		$contrato=$do->get('contrato');
		$anio=$do->get('anio');
		$mes=$do->get('mes');
		$dia=$do->get('dia');	
		logusu('notabu',"CONFIGURACION DE NOMINA $contrato $anio $mes $dia  ELIMINADA");
	}
}
?>