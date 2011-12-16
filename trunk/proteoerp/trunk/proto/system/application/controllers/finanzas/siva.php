<?php
//obligacionesiva
 class Siva extends Controller {
	
	var $data_type = null;
	var $data = null;
	 
	function siva(){
		parent::Controller(); 
		//required helpers for samples
		$this->load->helper('url');
		$this->load->helper('text');
		//rapyd library
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
   }
  function index(){
    	$this->datasis->modulo_id(508,1);
    	redirect("finanzas/siva/filteredgrid");
    }
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por Obligaciones IVA", 'siva');
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d"); 
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->id = new inputField("Id", "id");
		$filter->id->size=15;
		
		$filter->numero = new inputField("Numero", "numero");
		$filter->numero->size=15;
		
		$filter->tipo =  new dropdownField("Tipo", "tipo");
		$filter->tipo->option(" ","Todos");
		$filter->tipo->option("FC","Factura");
		$filter->tipo->option("DE","Devolucion");
		$filter->tipo->option("NC","Nota Credito");
		$filter->tipo->option("ND","Nota Debito");
		$filter->tipo->option("RI","Ret/IVA");
		$filter->tipo->option("RE","Resumen Caja");
		$filter->tipo->option("CR","Comprobante Retencion");
		$filter->tipo->option("Rm","Maquina Fiscal");
		$filter->tipo->style='width:160px';
		$filter->tipo->rule= "required";
		
		$filter->libro =  new dropdownField("Libro", "libro");
		$filter->libro->option("C","Compra");
		$filter->libro->option("V","Venta");
		$filter->libro->style='width:100px';
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/siva/dataedit/show/<#id#>','<#id#>');

		$grid = new DataGrid("Lista de Obligaciones IVA");
		$grid->order_by("id","asc");
		$grid->per_page = 20;

		$grid->column("Id",$uri);
		$grid->column("Libro","libro");
		$grid->column("Tipo","tipo");
		$grid->column("Fuente","fuente");
		$grid->column("Sucursal","sucursal");
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("N&uacute;mero","numero");
		$grid->column("Codigo","clipro");
		$grid->column("Nombre","nombre");
		$grid->column("Rif","rif");
		$grid->column("Gran Total","gtotal");
		$grid->column("Impuesto","impuesto");
		$grid->column("geneimpu","geneimpu");
		$grid->column("Comprobante","comprobante");
	  	  						
		$grid->add("finanzas/siva/dataedit/create");
		$grid->build();
	
	  $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Obligaciones IVA</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		$this->rapyd->load("dataedit");
		
		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre', 
		'cirepre'=>'Rif/Cedula',
		'dire11'=>'Direcci&oacute;n'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'clipro','nombre'=>'nombre','cirepre'=>'rif'),
		'titulo'  =>'Buscar Cliente');
		
		$boton =$this->datasis->modbus($mSCLId);
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");			
		}	
		);	
		';
		
		$edit = new DataEdit("Obligaciones IVA", "siva");
		$edit->back_url = site_url("finanzas/siva/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		//$edit->id = new inputField("Id", "id");
		//$edit->id->mode="autohide";
		//$edit->id->size =12;
		//$edit->id->rule= "trim";
		//$edit->id->readonly=true;
		//$edit->id->maxlength =11;
		//$edit->id->css_class='inputnum';
		
		$edit->libro =  new dropdownField("Libro", "libro");
		$edit->libro->option("C","Compra");
		$edit->libro->option("V","Venta");
		$edit->libro->style='width:100px';
		$edit->libro->rule= "required";
		
		$edit->tipo =  new dropdownField("Tipo", "tipo");
		$edit->tipo->option("FC","Factura");
		$edit->tipo->option("DE","Devolucion");
		$edit->tipo->option("NC","Nota Credito");
		$edit->tipo->option("ND","Nota Debito");
		$edit->tipo->option("RI","Ret/IVA");
		$edit->tipo->option("RE","Resumen Caja");
		$edit->tipo->option("Rm","Maquina Fiscal");
		$edit->tipo->style='width:100px';
		$edit->tipo->rule= "required";
		
		$edit->fuente =new dropdownField("Fuente", "fuente");
		$edit->fuente->option("CP","CP");
		$edit->fuente->option("FA","FA");
		$edit->fuente->option("GS","GS");
		$edit->fuente->option("MC","MC");
		$edit->fuente->option("MP","MP");
		$edit->fuente->option("MP","MP");
		$edit->fuente->style='width:100px';
		
		$edit->sucursal =     new inputField("Sucursal", "sucursal");
		$edit->sucursal->size = 12;
		$edit->sucursal->maxlength=2;
		$edit->sucursal->rule="trim";
		
		$edit->fecha =        new DateField("Fecha", "fecha");
		$edit->fecha->size = 12;
		$edit->fecha->rule="required";
		
		$edit->numero =       new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 12;
		$edit->numero->maxlength=15;
		$edit->numero->rule= "trim|required";
		
		$edit->numhasta =     new inputField("N&uacute;mero hasta", "numhasta");
		$edit->numhasta->size = 12;
		$edit->numhasta->maxlength=8;
		$edit->numhasta->rule="trim";
		
		$edit->caja =         new inputField("Caja /M", "caja");
		$edit->caja->size = 12;
		$edit->caja->maxlength=8;
		$edit->caja->rule="trim";
		
		$edit->nfiscal =      new inputField("Nfiscal", "nfiscal");
	  $edit->nfiscal->size =12;
	  $edit->nfiscal->maxlength=8;
	  $edit->nfiscal->rule="trim";
		
		$edit->nhfiscal =     new inputField("Nhfiscal","nhfiscal");
		$edit->nhfiscal->size =12;
		$edit->nhfiscal->maxlength = 8;
		$edit->nhfiscal->rule="trim";
		
		$edit->referen =      new inputField("Referencia","referen");
		$edit->referen->size =12;
		$edit->referen->maxlength =8;
		$edit->referen->rule="trim";
		
		$edit->planilla =     new inputField("Planilla","planilla");
		$edit->planilla->size =12;
		$edit->planilla->maxlength =8;
		$edit->planilla->rule="trim";
		
		$edit->clipro =new inputField("Codigo","clipro");
		$edit->clipro->size =12;
		//$edit->clipro->maxlength =5;
		$edit->clipro->rule="trim";
		$edit->clipro->readonly=true;
		$edit->clipro->append($boton);
		
		$edit->nombre = new inputField("Nombre","nombre");
		$edit->nombre->size =30;
		$edit->nombre->maxlength =40;
		$edit->nombre->rule="trim";
		
		$edit->contribu =new dropdownField("Tipo", "contribu");
		$edit->contribu->option("CO","CO");
		$edit->contribu->option("NO","NO");
		$edit->contribu->option("SR","SR");	
		$edit->contribu->style='width:100px';
		
		//$edit->contribu = new inputField("Tipo","contribu");
		//$edit->contribu->size = 12;
		//$edit->contribu->maxlength =2;
		//$edit->contribu->rule="trim";
    
    $edit->rif = new inputField("Rif","rif");
    $edit->rif->size = 12;
    $edit->rif->maxlength = 14;
    $edit->rif->rule = "trim";
    
    $edit->registro =     new inputField("Registro","registro");
    $edit->registro->size = 12;
    $edit->registro->maxlength = 2;
    $edit->registro->rule = "trim";
    
    $edit->nacional =     new inputField("Nacional","nacional");
    $edit->nacional->size = 12;
    $edit->nacional->maxlength = 1;
    $edit->nacional->rule = "trim";
    
    $edit->exento =       new inputField("Exento","exento");
    $edit->exento->size = 12;
    $edit->exento->maxlength = 17;
    $edit->exento->rule = "trim|numeric";
    $edit->exento->css_class='inputnum';
    
    $edit->general =      new inputField("General","general");
    $edit->general->size = 12;
    $edit->general->maxlength = 17;
    $edit->general->rule = "trim|numeric";
    $edit->general->css_class='inputnum';
    
    $edit->geneimpu =     new inputField("Geneimpu","geneimpu");
    $edit->geneimpu->size = 12;
    $edit->geneimpu->maxlength = 17;
    $edit->geneimpu->rule = "trim|numeric";
    $edit->geneimpu->css_class='inputnum';
    
    $edit->adicional =    new inputField("Adicional","adicional");
  	$edit->adicional->size = 12;
    $edit->adicional->maxlength = 17;
    $edit->adicional->rule = "trim|numeric";
    $edit->adicional->css_class='inputnum';
    
    $edit->adicimpu =     new inputField("Adicimpu","adicimpu");
    $edit->adicimpu->size = 12;
    $edit->adicimpu->maxlength = 17;
    $edit->adicimpu->rule = "trim";
    $edit->adicimpu->css_class='inputnum';
    
    $edit->reducida =     new inputField("Reducida","reducida");
    $edit->reducida->size = 12;
    $edit->reducida->maxlength = 17;
    $edit->reducida->rule = "trim|numeric";
    $edit->reducida->css_class='inputnum';
    
    $edit->reduimpu =     new inputField("Reduimpu","reduimpu");
    $edit->reduimpu->size = 12;
    $edit->reduimpu->maxlength = 17;
    $edit->reduimpu->rule = "trim|numeric";
    $edit->reduimpu->css_class='inputnum';
    
    $edit->stotal =       new inputField("Sub-total","stotal");
		$edit->stotal->size = 12;
    $edit->stotal->maxlength = 17;
    $edit->stotal->rule = "trim|numeric";
    $edit->stotal->css_class='inputnum';
    
    $edit->impuesto =     new inputField("Impuesto","impuesto");
    $edit->impuesto->size = 12;
    $edit->impuesto->maxlength = 17;
    $edit->impuesto->rule = "trim|numeric";
    $edit->impuesto->css_class='inputnum';
    
    $edit->gtotal =       new inputField("Gran total","gtotal");
    $edit->gtotal->size = 12;
    $edit->gtotal->maxlength = 17;
    $edit->gtotal->rule = "trim|numeric";
    $edit->gtotal->css_class='inputnum';
    
    $edit->reiva =        new inputField("Retenci&oacute;n Iva","reiva");
    $edit->reiva->size = 12;
    $edit->reiva->maxlength = 17;
    $edit->reiva->rule = "trim|numeric";
    $edit->reiva->css_class='inputnum';
    
    $edit->fechal =       new DateField("Fechal","fechal");
    $edit->fechal->size = 12;
    $edit->fechal->rule="required";
    
    $edit->fafecta =      new DateField("Fafecta","fafecta");
    $edit->fafecta->size =12;
    
    $edit->comprobante =  new InputField("Comprobante","comprobante");
    $edit->comprobante->size = 12;
    
    $edit->fecharece =  new InputField("Fecharece","fecharece");
    $edit->fecharece->size = 12;
    
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
 
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Obligaciones IVA</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('id');		
		logusu('siva',"OBLIGACION DE IVA $codigo CREADA");
	}
	function _post_update($do){
		$codigo=$do->get('proveed');
		$nombre=$do->get('nombre');
		logusu('siva',"OBLIGACION DE IVA $codigo MODIFICADA");
	}
	function _post_delete($do){
		$codigo=$do->get('proveed');
		$nombre=$do->get('nombre');
		logusu('siva',"OBLIGACION DE IVA $codigo ELIMINADA");
	}
}
?>