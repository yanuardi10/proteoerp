<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class rica extends Validaciones {

	var $titulo = 'Retenciones ICA';

	function rica() {
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index() {
		//$this->datasis->modulo_id(512,1);
		redirect("finanzas/rica/filteredgrid");
	}

	function filteredgrid() {
		$this->rapyd->load("datafilter","datagrid");
		$filter = new DataFilter("Filtro de Retenciones ICA", "rica");
		
		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=5;
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('finanzas/rica/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Listado");
		$grid->per_page = 10;

		$grid->column_orderby('C&oacute;digo',$uri,'codigo');
		$grid->column_orderby('Actividad','activi','activi');
		$grid->column_orderby('Aplicaci&oacute;n','aplica');
		$grid->column_orderby('Tasa','tasa','tasa');

		$grid->add('finanzas/rica/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>'.$this->titulo.'</h1';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){

		$script='$(function() {
			$(".inputnum").numeric(".");
			});';

		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Retenciones ICA", "rica");
		$edit->back_url = site_url("finanzas/rica/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');


		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->rule = "trim|strtoupper|required";
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength=5;
		$edit->codigo->size =8;

		$edit->activi = new inputField('Actividad', 'activi');
		$edit->activi->size =16;
		$edit->activi->maxlength =14;
		$edit->activi->rule='trim|required';

		$edit->aplica = new inputField('Aplicaci&oacute;n', 'aplica');
		$edit->aplica->rule = "trim|required";
		$edit->aplica->size =40;
		$edit->aplica->maxlength=45;

		$edit->tasa = new inputField('Tasa', 'tasa');
		$edit->tasa->rule = "trim|required";
		$edit->tasa->size =25;
		$edit->tasa->css_class='inputnum';
		$edit->tasa->maxlength=8;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>'.$this->titulo.'</h1>';
		$data["head"]    = script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('activi');
		logusu('banc',"RET.ICA $codigo ACTIVIDAD  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('activi');
		logusu('banc',"RET.ICA $codigo ACTIVIDAD  $nombre MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('activi');
		logusu('banc',"RET.ICA $codigo ACTIVIDAD  $nombre ELIMINADO");
	}

}
?>
