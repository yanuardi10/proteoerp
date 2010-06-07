<?php
//***************************************************************
//		OTROS CONCEPTOS
//***************************************************************
class Oconceptos extends Controller {
	
	var $data_type = null;
	var $data = null;

	function Oconceptos()
	{
		parent::Controller(); 

		//required helpers for samples
		$this->load->helper('url');
		$this->load->helper('text');

		//rapyd library
		$this->load->library("rapyd");

		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/finanzas/". $this->uri->segment(2).EXT);
	}

	function index()
	{
		redirect("finanzas/oconceptos/filteredgrid");
	}

	##### callback test (for DataFilter + DataGrid) #####
	function test($id,$const)
	{
		return $id*$const;
	}

	function filteredgrid()
	{

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Buscar", 'botr');
		
		$filter->codigo = new inputField("Codigo","codigo");
		$filter->codigo->size=15;
				
		$filter->nombre = new inputField("Descripci&oacute;n", "nombre");
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/oconceptos/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Otros Conceptos");
		$grid->order_by("nombre","asc");
		$grid->per_page = 7;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","nombre");
		$grid->column("Tipo", "tipo");
		$grid->column("Clase", "clase");
		$grid->column("Contabilidad","cuenta");
    $grid->column("I.V.A","iva");
    $grid->column("Usa Cantidad", "usacant");
		$grid->column("Precio Bs.","precio");

		$grid->add("finanzas/oconceptos/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Otros Conceptos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
    }
	function dataedit()
	{ 
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Oconceptos", "botr");
		$edit->back_url = site_url("finanzas/oconceptos/filteredgrid");
		
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size=15;
		
		$edit->nombre = new inputField("Nombre", "nombre");
		
		$edit->descrip = new textareafield("Descripci&oacute;n", "nombre");
		$edit->descrip->cols=70;
		$edit->descrip->rows=4;
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("","");
		$edit->tipo->options(array("C"=> "Cliente","P"=>"Proveedor","O"=> "Otros"));
		$edit->tipo->style="width:110px";
				
		$edit->clase = new dropdownField("Clase", "clase");
		$edit->clase->option("","");
		$edit->clase->options(array("E"=> "Entrada","S"=>"Salida","N"=> "Ninguno"));
		$edit->clase->style="width:110px";
		
		$edit->cuenta = new inputField("Contabilidad","cuenta");
	  $edit->cuenta->size=25;
    
    $edit->iva = new inputField("I.V.A","iva");
    $edit->iva->size=25;
    
    $edit->usacant = new dropdownField("Usa cantidad", "usacant");
		$edit->usacant->option("","");
		$edit->usacant->options(array("S"=> "Si","N"=>"No"));
		$edit->usacant->style="width:110px";		
		
		///
	///	$edit->status->option("","");
	///	$edit->status->options(array("C"=> "Cerrado","A"=>"Abierto"));	
		
	///	$edit->almacen = new dropdownField("Almacen", "almacen");
	///	$edit->almacen->option("","");
	///	$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");
		
		///$edit->directo = new captchaField("Directorio", "directo");
	///	$edit->directo = new inputField("Directorio", "directo");

	///	$edit->mesai = new inputField("Mesas", "mesai");
///		$edit->mesaf = new inputField("Mesas", "mesaf");
    ///    $edit->horai = new inputField("Hora feliz", "horai");
       /// $edit->horaf = new inputField("Hora feliz", "horaf");
      ///  $edit->fechaa = new datefield("Fecha apertura", "fechaa");
      ///  $edit->fechac = new datefield("Fecha cierre", "fechac");
      ///  $edit->horaa= new inputField("Hora apertura", "horaa"); 
      ///  $edit->horac = new inputField("Hora cierre", "horac");
       /// $edit->apertura=new inputField("Monto apertura", "apertura");
  ///      $edit->cierre=new inputField("Monto Cierre", "cierre"); 
/*	
	
		
		$edit->fechaa = new inputField("Fecha apertura", "fechaa");

		$edit->almacen = new dropdownField("Almacen", "almacen");
		$edit->almacen->option("","");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");

		$edit->clave = new passwordField("Contrasena", "clave");

		$edit->comive  = new inputField("Com. de Venta %", "comive");
		$edit->comicob = new inputField("Com. Cobranza %", "comicob");
		
*/
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Otros Conceptos</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
}
?>