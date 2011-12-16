<?php require_once(BASEPATH.'application/controllers/validaciones.php'); 
class botr extends validaciones {

	function botr(){
		parent::Controller(); 
		$this->load->library('rapyd');
	}

	function index(){
		$this->datasis->modulo_id(600,1);
		redirect('finanzas/botr/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de conceptos', 'botr');

		$filter->codigo = new inputField('C&oacute;digo', 'codigo');
		$filter->codigo->size=15;

		$filter->cuenta  = new inputField('Cuenta','cuenta');
		$filter->cuenta->like_side='after';
		$filter->cuenta->size=15;

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('finanzas/botr/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid('Lista de Otros Conceptos Contable');
		$grid->order_by('codigo','asc');
		$grid->per_page = 20;

		$grid->column_orderby('C&oacute;digo',$uri,'codigo');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Cuenta','cuenta','cuenta');
		$grid->column_orderby('Tipo' ,'tipo'  ,'tipo');
		$grid->column_orderby('Clase','clase' ,'clase');

		$grid->add('finanzas/botr/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Otros Conceptos Contables</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$lcuenta=site_url('contabilidad/cpla/autocomplete/codigo');
		$script='
			function formato(row) {
				return row[0] + "-" + row[1];
			}

			$(function() {
				$(".inputnum").numeric(".");
				$("#cuenta").autocomplete("'.$lcuenta.'",{
					delay:10,
					//minChars:2,
					matchSubset:1,
					matchContains:1,
					cacheLength:10,
					formatItem:formato,
					width:350,
					autoFill:true
					}
				);
			});';

		$edit = new DataEdit('Otro Concepto Contable', 'botr');
		$edit->back_url = site_url('finanzas/botr/filteredgrid');
		$edit->script($script,'create');
		$edit->script($script,'modify');

		$edit->codigo = new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->rules='required|trim';
		$edit->codigo->mode='autohide';

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rules='required|trim';
		$edit->nombre->size = 40;

		$edit->cuenta = new inputField('Cuenta', 'cuenta');
		$edit->cuenta->rule='trim|callback_chcuentac';
		$edit->cuenta->size = 25;

		$edit->precio = new inputField('Precio', 'precio');
		$edit->precio->size = 10;
		$edit->precio->rule='trim|numeric';
		$edit->precio->css_class='inputnum';

		$edit->iva = new inputField('Iva', 'iva');
		$edit->iva->rule='trim|numeric';
		$edit->iva->css_class='inputnum';
		$edit->iva->size = 10;

		$edit->tipo = new dropdownField('Tipo','tipo');
		$edit->tipo->rules='required';
		$edit->tipo->options(array('C'=>'Cliente','P'=>'Proveedor','O'=>'Otro'));

		//$edit->intocable = new inputField('Intocable', 'intocable');
		//$edit->intocable->size = 5;

		$edit->clase = new dropdownField("Clase", "clase");
		$edit->clase->rules='required';
		$edit->clase->options(array('E'=>'Entrada','S'=>'Salida','N'=>'Ninguno'));

		$edit->usacant = new dropdownField('Usa cantidad', 'usacant');
		$edit->usacant->options(array('N'=>'No','S'=>'Si'));
		$edit->usacant->rules='required';

		$edit->buttons('modify','save','undo','delete','back');
		$edit->build();
 
		$data['content'] = $edit->output;
		$data['title']   = '<h1>Otros Conceptos Contables</h1>';
		$data["head"]    = script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').script('plugins/jquery.autocomplete.js').style('jquery.autocomplete.css').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}
}
?>
