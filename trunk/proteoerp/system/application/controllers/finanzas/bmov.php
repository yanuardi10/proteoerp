<?php	//cheques
class Bmov extends Controller {
	function bmov(){
		parent::Controller(); 
		$this->load->library("rapyd");
	} 
	function index(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
            
		$filter = new DataFilter("Filtro de cheques", 'bmov');
		$select=array("fecha","numero","nombre","CONCAT_WS('',banco ,'(',numcuent,')')AS banco","tipo_op","codbanc","LEFT(concepto,20)AS concepto","anulado");
		$filter->db->select($select);
		$filter->db->from('bmov');
		$filter->db->where('tipo_op','CH');
		
		$filter->fecha = new dateonlyField("Fecha", "fecha");
		$filter->fecha->size=10;
		$filter->fecha->operator="=";
		
		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size=20;
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=40;

		$filter->banco = new dropdownField("Banco", "codbanc");                    
		$filter->banco->option("","");                            
		$filter->banco->options("SELECT codbanc,banco FROM banc where tbanco<>'CAJ' ORDER BY codbanc");  
		
		$filter->buttons("reset","search");
		$filter->build();
	  
	  $uri = anchor('finanzas/bmov/dataedit/show/<#codbanc#>/<#tipo_op#>/<#numero#>','<#numero#>');
	
		$grid = new DataGrid("Lista de cheques");
		$grid->order_by("numero","desc");                          
		$grid->per_page = 15;
	
		$grid->column("N&uacute;mero"       ,$uri );
		$grid->column("Nombre"       ,"nombre");
		$grid->column("Banco"        ,"banco");
		$grid->column("Monto"        ,"<number_format><#monto#>|2|,|.</number_format>" ,'align=right'); 
		$grid->column("Concepto"     ,"concepto");
		$grid->column("Anulado"      ,"anulado",'align=center');
		                                        		
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cheques</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){
		
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("Cheque", "bmov");
		$edit->back_url = site_url("finanzas/bmov/index");
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		
		$edit->numero   = new textareaField("N&uacute;mero", "numero");
		$edit->nombre   = new textareaField("Nombre", "nombre");
		$edit->banco    = new textareaField("Banco", "banco");
		$edit->numcuent = new textareaField("N&uacute;mero Cuenta", "numcuent");
		$edit->monto    = new textareaField("Monto", "monto");
		$edit->concepto = new textareaField("Concepto", "concepto");
	  	$edit->Benefi   = new textareaField("Beneficiario", "benefi");
		$edit->anulado  = new textareaField("Anulado", "anulado");
		
		$edit->buttons("back");
		$edit->build();
		
	  	$data['content'] = $edit->output;
	  	$data['title']   = "<h1>Consulta de Cheques</h1>";
	  	$data["head"]    = $this->rapyd->get_head();
	  	$this->load->view('view_ventanas', $data);  
	}  
  }
?>


