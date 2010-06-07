<?php
class movproveedores extends Controller {
	var $data_type = null;
	var $data = null;
	 
	function movproveedores(){
		parent::Controller(); 
		//required helpers for samples
		$this->load->helper('url');
		$this->load->helper('text');
		//rapyd library
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
   }
  function index(){
    	$this->datasis->modulo_id(507,1);
    	redirect("finanzas/movproveedores/filteredgrid");
    }
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por C&oacute;digo de Proveedor", 'sprm');
		
		$filter->cod_prv = new inputField("C&oacute;digo", "cod_prv");
		$filter->cod_prv->size=15;
	
		$filter->nombre = new inputField(" Nombre", "nombre");
		$filter->nombre->size=15;	
		
		$filter->numero = new inputField("N&uacute;mero","numero");
		$filter->numero->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('finanzas/movproveedores/dataedit/show/<#cod_prv#>/<#tipo_doc#>/<#numero#>/<#fecha#>','<#cod_prv#>');
		
		$grid = new DataGrid("Lista de Movimiento de Proveedores");
		$grid->order_by("cod_prv","asc");
		$grid->per_page = 20;
		
		$grid->column("Proveedor",$uri);
		$grid->column("Nombre","nombre");
		$grid->column("Tipo","tipo_doc");
		$grid->column("Nro","numero");
		$grid->column("Fecha","fecha");
		$grid->column("Monto","monto");
			  						
		//$grid->add("finanzas/movproveedores/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Movimiento de Proveedores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		$this->rapyd->load("dataedit");		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});		
		}
		';
		
		$mSPRV=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed' =>'C&oacute;digo Proveedor','nombre'=>'Nombre','rif'=>'RIF'),
		'retornar'=>array('proveed'=>'cod_prv','nombre'=>'nombre'),
		'titulo'  =>'Buscar Proveedor');
		$bsprv =$this->datasis->modbus($mSPRV);
		
		$edit = new DataEdit("Movimiento de Proveedores", "sprm");
		$edit->back_url = site_url("finanzas/movproveedores/filteredgrid");
		//$edit->script($script, "create");
		//$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->cod_prv =new inputField("C&oacute;digo de Proveedor", "cod_prv");
		$edit->cod_prv->mode="autohide";
		$edit->cod_prv->size=12;
		$edit->cod_prv->rule= "trim|required";//|callback_chexiste		
		$edit->cod_prv->append($bsprv);
		$edit->cod_prv->readonly=true;
		
		$edit->nombre =new inputField("Nombre", "nombre");
		$edit->nombre->size=30;
		$edit->nombre->rule= "trim|strtoupper|required";
		$edit->nombre->maxlength=40;
		
		$edit->tipo_doc = new dropdownField("Tipo", "tipo_doc");
		//$edit->tipo_doc->option("JD","JD");
		//$edit->tipo_doc->option("JN","JN");
		//$edit->tipo_doc->option("NN","NN");
		//$edit->tipo_doc->option("NR","NR");
		$edit->tipo_doc->option("ND","Debito");
		$edit->tipo_doc->option("FC","Factura");
		$edit->tipo_doc->option("AN","Anticipos");
		$edit->tipo_doc->option("GI","Giros");
		$edit->tipo_doc->style="width:100px";
		$edit->tipo_doc->rule= "required";
		
		$edit->numero =   new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size =12;       
		$edit->numero->maxlength=8;
		$edit->numero->rule= "trim|required";
		
		$edit->fecha =new DateField("Fecha", "fecha");
		$edit->fecha->size = 12;
		$edit->fecha->rule= "trim|required";
		
		$edit->monto =new inputField("Monto", "monto");
		$edit->monto->size = 12;
		$edit->monto->maxlength=17;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric';
		
		$edit->impuesto = new inputField("IVA", "impuesto");
		$edit->impuesto->size = 12;
		$edit->impuesto->maxlength=17;
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->rule='trim|numeric';      
		
		$edit->vence = new DateField("Vence", "vence");
		$edit->vence->size =12;
		
		$edit->tipo_ref = new dropdownField("Tipo de Referencia", "tipo_ref");
		$edit->tipo_ref->option("OS","OS");
		$edit->tipo_ref->option("AB","AB");
		$edit->tipo_ref->option("AC","AC");
		$edit->tipo_ref->option("AP","AP");
		$edit->tipo_ref->option("CR","CR");
		$edit->tipo_ref->style ="width:100px";
		
		$edit->num_ref =  new inputField("N&uacute;mero de Referencia","num_ref");
		$edit->num_ref->size =12;
		$edit->num_ref->maxlength=8;
		$edit->num_ref->rule='trim';
		
		$edit->observa1 = new inputField("Observaciones","observa1");
		$edit->observa1->size =50;
		$edit->observa1->maxlength=50;
		$edit->observa1->rule='trim';
		
		$edit->observa2 = new inputField(".","observa2");
	  $edit->observa2->size =50;
		$edit->observa2->maxlength=50;
		$edit->observa2->rule='trim';
				
		$edit->banco =  new dropdownField("Banco", "banco");
		$edit->banco->option("","");		
		$edit->banco->options("select codbanc,banco from banc order by codbanc");
		$edit->banco->style ="width:185px";
		
		$edit->tipo_op = new dropdownField("Tipo de Operaci&oacute;n","tipo_op");
		$edit->tipo_op->option("CH","CH");
		$edit->tipo_op->option("DE","DE");
		$edit->tipo_op->option("NC","NC");
		$edit->tipo_op->option("ND","ND");
		$edit->tipo_op->style="width:100px";
		
		$edit->numche = new inputField("N&uacute;mero de Cheque","numche");
    $edit->numche->size =12;
    $edit->numche->maxlength=12;
    $edit->numche->rule='trim';
    
    //$edit->abonos = new inputField("Abonos", "abonos");
		//$edit->abonos->size =12;
		//$edit->abonos->maxlength=17;
		//$edit->abonos->css_class='inputnum';
		//$edit->abonos->rule='trim|numeric';
		//  
    //$edit->codigo = new inputField("C&oacute;digo","codigo");
    //$edit->codigo->size =12;
    //$edit->codigo->maxlength=5;
    //$edit->codigo->rule='trim';
    //
    //$edit->descrip = new inputField("Descripci&oacute;n","descrip");
    //$edit->descrip->size =30;
    //$edit->descrip->maxlength=30;
    //$edit->descrip->rule='trim';
    //
    //$edit->comprob = new inputField("Comprob","comprob");
		//$edit->comprob->size =12;
    //$edit->comprob->maxlength=6;
    //$edit->comprob->rule='trim';
    //
    //$edit->ppago = new inputField("Ppago","ppago");
    //$edit->ppago->size = 12;
    //$edit->ppago->maxlength=17;
 		//$edit->ppago->css_class='inputnum';
		//$edit->ppago->rule='trim|numeric';
    //
    //$edit->nppago = new inputField("Nppago","nppago");
    //$edit->nppago->size =12;
    //$edit->nppago->maxlength=8;
    //$edit->nppago->css_class='inputnum';
    //$edit->nppago->rule='trim|numeric';      
    //
    //$edit->reten = new inputField("Reten","reten");
    //$edit->reten->size = 12;
    //$edit->reten->maxlength=17;
    //$edit->reten->css_class='inputnum';
    //$edit->reten->rule='trim|numeric';      
    //
    //$edit->nreten = new inputField("Nreten","nreten");
    //$edit->nreten->size =12;
    //$edit->nreten->maxlength=8;
    //$edit->nreten->rule='trim';
    //
    //$edit->mora = new inputField("Mora","mora");
    //$edit->mora->size = 12;
    //$edit->mora->maxlength=17;
    //$edit->mora->css_class='inputnum';
    //$edit->mora->rule='trim|numeric';      
    //
    $edit->posdata =new DateonlyField("Posdata","posdata");
    $edit->posdata->size =12;
    //
    
    $edit->benefi = new inputField("Beneficiario","benefi");
    $edit->benefi->size =50;
    $edit->benefi->maxlength=40;
    $edit->benefi->rule='trim';
    
    //$edit->cambio = new inputField("Cambio","cambio");
    //$edit->cambio->size = 12;
    //$edit->cambio->maxlength=17;
    //$edit->cambio->css_class='inputnum';
    //$edit->cambio->rule='trim|numeric';      
    //
    //$edit->pmora =    new InputField("Pmora","pmora");
    //$edit->pmora->size =12;
    //$edit->pmora->maxlength=6;
		//$edit->pmora->css_class='inputnum';
		//$edit->pmora->rule='trim|numeric';
		//
    //$edit->reteiva =  new InputField("Retenci&oacute;n Iva","reteiva");
    //$edit->reteiva->size =12;
    //$edit->reteiva->maxlength=18;
    //$edit->reteiva->css_class='inputnum';
		//$edit->reteiva->rule='trim|numeric';
    //
    //$edit->nfiscal =  new InputField("Nfiscal","nfiscal");
    //$edit->nfiscal->size =12;
    //$edit->nfiscal->maxlength=8;
    //$edit->nfiscal->rule='trim';
    //
    //$edit->montasa =  new InputField("Montasa","montasa");
    //$edit->montasa->size = 12;
    //$edit->montasa->maxlength=17;
		//$edit->montasa->css_class='inputnum';
		//$edit->montasa->rule='trim|numeric';          
    //
    //$edit->monredu =  new InputField("Monto Reducido","monredu");
    //$edit->monredu->size = 12;
    //$edit->monredu->maxlength=17;
		//$edit->monredu->css_class='inputnum';
		//$edit->monredu->rule='trim|numeric';          
    //
    //$edit->monadic =  new InputField("Monto Adicional","monadic");
    //$edit->monadic->size = 12;
    //$edit->monadic->maxlength=17;
		//$edit->monadic->css_class='inputnum';
		//$edit->monadic->rule='trim|numeric';       
    //
    //$edit->tasa =     new InputField("Tasa","tasa");
    //$edit->tasa->size = 12;
    //$edit->tasa->maxlength=17;
		//$edit->tasa->css_class='inputnum';
		//$edit->tasa->rule='trim|numeric';     
    //
    //$edit->reducida = new InputField("Tasa Reducida","reducida");
    //$edit->reducida->size = 12;
    //$edit->reducida->maxlength=17;
 		//$edit->reducida->css_class='inputnum';
		//$edit->reducida->rule='trim|numeric';
		//
    //$edit->sobretasa =new InputField("Tasa Adicional","sobretasa");
    //$edit->sobretasa->size = 12;
  	//$edit->sobretasa->maxlength=17;
 		//$edit->sobretasa->css_class='inputnum';
		//$edit->sobretasa->rule='trim|numeric';
    //
    //$edit->exento =new InputField("Exento","exento");
    //$edit->exento->size = 12;
    //$edit->exento->maxlength=17;
 		//$edit->exento->css_class='inputnum';
		//$edit->exento->rule='trim|numeric';
    //    
    //$edit->fecdoc =   new DateonlyField("Fecha de Documento","fecdoc");
    //$edit->fecdoc->size =12;
    
		$edit->buttons("undo","back");//"delete","save", "modify",
		$edit->build();
 
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Movimiento de Proveedores</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data); 
	}
	function _post_insert($do){
		$codigo=$do->get('cod_prv');
		$nombre=$do->get('nombre');
		logusu('sprm',"MOVIMIENTO DE PROVEEDOR $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('cod_prv');
		$nombre=$do->get('nombre');
		logusu('sprm',"MOVIMIENTO DE PROVEEDOR $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('cod_prv');
		$nombre=$do->get('nombre');
		logusu('sprm',"MOVIMIENTO DE PROVEEDOR $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('cod_prv');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM sprm WHERE cod_prv='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM sprm  WHERE cod_prv='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el movimiento de proveedor $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}	
}
?>