<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Conforch extends Validaciones {
	function Conforch() {
		parent::Controller();
		$this->load->library('rapyd');
		define("THISFILE", APPPATH."controllers/nomina".$this->uri->segment(2).EXT);
	}
	
	function index() {
		$this->datasis->modulo_id('51B',1);
		redirect("finanzas/conforch/filteredgrid");
	}

	function filteredgrid() {
		$this->rapyd->load("datafilter","datagrid");
		$filter = new DataFilter("Filtro de Cheques", "bmov");
		$filter->db->where('tipo_op','CH');
		$filter->db->orderby('fecha','desc');
		
		
		$filter->numero = new inputField("N&uacute;mero de cheque", "numero");
		$filter->numero->size=15;
		$filter->numero->maxsize=12;
		
		$filter->benefi = new inputField("Beneficiario", "benefi");
		$filter->benefi->maxsize=40;
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$grid = new DataGrid("Cheques emitidos");
		$grid->per_page = 10;
		
		$grid->column("Fecha"       ,'<dbdate_to_human><#fecha#></dbdate_to_human>'  );
		$grid->column("Numero"      ,'numero'  );
		//$grid->column("Nombre"      ,'nombre'  );
		$grid->column("Beneficiario",'benefi'  );
		$grid->column("Monto"       ,'<number_format><#monto#>|2</number_format>','align=right');
		$grid->column("Banco"       ,'banco'   );
		$grid->column("Concepto"    ,'concepto');
		
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Conformaci&oacute;n de cheques</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
}
?>