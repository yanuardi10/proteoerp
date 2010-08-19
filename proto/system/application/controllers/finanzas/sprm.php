<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//pagoproveed
class Sprm extends validaciones {
	
	function sprm(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(500,1);		
	}
	function index(){
		
		
		redirect("finanzas/sprm/filteredgrid");
	}
	function filteredgrid(){

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Pago a Proveedores", "sprm");
		
		$filter->numero = new inputField("Numero", "numero");
		$filter->numero->size=12;
		$filter->numero->maxlength=8;
		
		$filter->cod_prv = new inputField("C&oacute;digo Proveedor", "cod_prv");
		$filter->cod_prv->size=12;
		$filter->cod_prv->maxlength=5;		
		
		$filter->tipo_doc = new dropdownField("Tipo de Documento", "tipo_doc");
		$filter->tipo_doc->option("","");
		$filter->tipo_doc->option("AB","Abono");
		$filter->tipo_doc->option("AN","Anticipo");		
		$filter->tipo_doc->option("NC","Nota de Cr&eacute;dito");		
		$filter->tipo_doc->style ="width:100px";                             
		
		$filter->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$filter->fecha->size=12;

		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('finanzas/sprm/dataedit/show/<#cod_prv#>/<#tipo_doc#>/<#numero#>/<#fecha#>','<#numero#>');

		$grid = new DataGrid("Lista de Pago a Proveedores");
		$grid->order_by("numero","asc");
		$grid->per_page = 10;
		
		$grid->column("Numero",$uri);
		$grid->column("Cod. Proveedor","cod_prv");
		$grid->column("Tipo Documento","tipo_doc");
		$grid->column("Fecha","fecha");
		
		$grid->add("finanzas/sprm/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Pago a Proveedores</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);		
	}
	function dataedit(){
		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
		
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
		
				$script ='
		$(function() {
			$(".inputnum").numeric(".");			
		}	
		);	
		';
		
		$edit = new DataEdit("Pago Proveedores", "sprm");
		$edit->back_url = "finanzas/sprm";
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->pre_process("insert","_guarda");
		$edit->pre_process("update","_guarda");
		
		
		$edit->numero = new inputField("Numero", "numero");
		$edit->numero->size=12;
		$edit->numero->maxlength=8;
		$edit->numero->rule="trim|required";
		
		//$edit->numero = new dropdownField("Numero", "numero");
		//$edit->numero->size=30;
		//$edit->numero->option("","");
		//$edit->numero->options("SELECT codbanc, banco FROM bmov ORDER BY banco");
				
		$edit->cod_prv = new inputField("C&oacute;digo Proveedor", "cod_prv");
		$edit->cod_prv->size=12;
		$edit->cod_prv->maxlength=5;
		$edit->cod_prv->rule="trim|required";
		$edit->cod_prv->append($bsprv);
		$edit->cod_prv->readonly=true;
		
		$edit->nombre = new inputField("Nombre Proveedor", "nombre");
		$edit->nombre->size=30;
		$edit->nombre->maxlength=40;
		$edit->nombre->rule="trim";
		
		 
		$edit->tipo_doc = new dropdownField("Tipo de Documento", "tipo_doc");		
		$edit->tipo_doc->option("AB","Abono");
		$edit->tipo_doc->option("AN","Anticipo");
		//$edit->tipo_doc->option("FC","Factura");
		//$edit->tipo_doc->option("JD","JD");
		$edit->tipo_doc->option("NC","Nota de Cr&eacute;dito");
		//$edit->tipo_doc->option("ND","Nota de D&eacute;bito");
		$edit->tipo_doc->style ="width:100px";
		$edit->tipo_doc->rule="required";
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","Y/m/d");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->size=12;
		$edit->fecha->rule="required|chfecha";
		
		$edit->monto =new inputField("Monto","monto");
    $edit->monto->size = 12;
    $edit->monto->maxlength = 17;
    $edit->monto->rule = "trim|required|numeric";
    $edit->monto->css_class='inputnum';
    
		$edit->impuesto =new inputField("Impuesto","impuesto");
    $edit->impuesto->size = 12;
    $edit->impuesto->maxlength = 17;
    $edit->impuesto->rule = "trim|required|numeric";
    $edit->impuesto->css_class='inputnum';
    
		$edit->abonos =new inputField("Abonos","abonos");
    $edit->abonos->size = 12;
    $edit->abonos->maxlength = 17;
    $edit->abonos->rule = "trim|required|numeric";
    $edit->abonos->css_class='inputnum';
		
		$edit->vence = new DateonlyField("Vence", "vence","Y/m/d");		
		$edit->vence->size=12;
		
		$edit->tipo_ref = new dropdownField("Tipo de Referencia", "tipo_ref");
		$edit->tipo_ref->option("OS","OS");
		$edit->tipo_ref->option("AB","AB");//
		$edit->tipo_ref->option("AC","AC");//
		$edit->tipo_ref->option("AP","AP");//
		$edit->tipo_ref->option("CR","CR");//
		$edit->tipo_ref->style ="width:100px";
		
		$edit->num_ref = new inputField("Num. Referencia", "num_ref");
		$edit->num_ref->size=12;
		$edit->num_ref->maxlength=8;
		$edit->num_ref->rule="trim";
		
		$edit->observa1 = new inputField("Observaciones", "observa1");
		$edit->observa1->size=50;
		$edit->observa1->maxlength=50;
		$edit->observa1->rule="trim";
		
		$edit->observa2 = new inputField("", "observa2");
		$edit->observa2->size=50;
		$edit->observa2->maxlength=50;
		$edit->observa2->rule="trim";
		
		$edit->banco = new dropdownField("Caja/Banco", "banco");
		$edit->banco->size=30;
		$edit->banco->option("","");
		$edit->banco->options("SELECT codbanc, banco FROM bmov ORDER BY banco");
		
		$edit->tipo_op = new inputField("Tipo de Operacion", "tipo_op");
		$edit->tipo_op->size=12;
		$edit->tipo_op->maxlength=2;
		$edit->tipo_op->rule="trim";
		
		$edit->comprob = new dropdownField("Comprobante", "comprob");		
		$edit->comprob->option("AJUST","REGUALRIZAR F. MAL PROCESADA");   
		$edit->comprob->option("BONIP","BONIFICACION DE PROVEEDORES");    
		$edit->comprob->option("DECOM","DEVOLUCION EN COMPRAS");          
		$edit->comprob->option("DESOP","OTROS DESCUENTOS PROVEEDORES");   
		$edit->comprob->option("DESPP","DESCUENTO PRONTO PAGO PROVEEDO"); 
		$edit->comprob->option("DEVOP","DESCUENTO X VOLUMEN PROVEEDOR");  
		$edit->comprob->option("DFCAP","DIFERENCIA /CAMBIO PROVEEDOR");   
		$edit->comprob->option("DIFPP","DIFERENCIA /PRECIOS PROVEEDOR");  
		$edit->comprob->size=30;				
		
		$edit->numche = new inputField("Numche", "numche");
		$edit->numche->size=12;
		$edit->numche->maxlength=12;
		$edit->numche->rule="trim";
		
		$edit->codigo = new inputField("Codigo", "codigo");
		$edit->codigo->size=12;
		$edit->codigo->maxlength=50;
		$edit->codigo->rule="trim";
		
		$edit->descrip = new inputField("Descripcion", "descrip");
		$edit->descrip->size=30;
		$edit->descrip->maxlength=30;
		$edit->descrip->rule="trim";
		
		$edit->ppago =new inputField("Ppago","ppago");
    $edit->ppago->size = 12;
    $edit->ppago->maxlength = 17;
    $edit->ppago->rule = "trim|numeric";
    $edit->ppago->css_class='inputnum';
    
    $edit->nppago = new inputField("NPpago", "nppago");
		$edit->nppago->size=12;
		$edit->nppago->maxlength=8;
		$edit->nppago->rule="trim";
		
		$edit->reten =new inputField("Retencion","reten");
    $edit->reten->size = 12;
    $edit->reten->maxlength = 17;
    $edit->reten->rule = "trim|numeric";
    $edit->reten->css_class='inputnum';
    
    $edit->nreten = new inputField("Nreten", "nreten");
    $edit->nreten->size=12;
    $edit->nreten->maxlength=8;
		$edit->nreten->rule="trim";
		
		$edit->mora =new inputField("Mora","mora");
    $edit->mora->size = 12;
    $edit->mora->maxlength = 17;
    $edit->mora->rule = "trim|numeric";
    $edit->mora->css_class='inputnum';
    
    $edit->posdata = new DateonlyField("Posdata", "posdata","Y/m/d");
		$edit->posdata->size=12;	  
		
		$edit->benefi = new inputField("Beneficiario", "benefi");
		$edit->benefi->size=30;
		$edit->benefi->maxlength=40;
		$edit->benefi->rule="trim";
		
		$edit->control = new inputField("Control", "control");
    $edit->control->size=12;
    $edit->control->maxlength=8;
		$edit->control->rule="trim";
		
		//$edit->transac = new inputField("Transaccion", "transac");
    //$edit->transac->size=12;
    //$edit->transac->maxlength=8;
		//$edit->transac->rule="trim";
		
		$edit->cambio =new inputField("Cambio","cambio");
    $edit->cambio->size = 12;
    $edit->cambio->maxlength = 17;
    $edit->cambio->rule = "trim|numeric";
    $edit->cambio->css_class='inputnum';
    
    $edit->pmora =new inputField("Pmora","pmora");
    $edit->pmora->size = 12;
    $edit->pmora->maxlength = 6;
    $edit->pmora->rule = "trim|numeric";
    $edit->pmora->css_class='inputnum';
    
    $edit->reteiva =new inputField("Retencion de IVA","reteiva");
    $edit->reteiva->size = 12;
    $edit->reteiva->maxlength = 18;
    $edit->reteiva->rule = "trim|numeric";
    $edit->reteiva->css_class='inputnum';		
		
		$edit->id =new inputField("id","id");//entero
    $edit->id->size = 12;
    
    $edit->nfiscal = new inputField("Nfiscal", "nfiscal");
    $edit->nfiscal->size=12;
    $edit->nfiscal->maxlength=8;
		$edit->nfiscal->rule="trim";
		
		$edit->montasa =new inputField("montasa","montasa");
    $edit->montasa->size = 12;
    $edit->montasa->maxlength = 17;
    $edit->montasa->rule = "trim|numeric";
    $edit->montasa->css_class='inputnum';
    
    $edit->monredu =new inputField("monredu","monredu");
    $edit->monredu->size = 12;
    $edit->monredumonredu->maxlength = 17;
    $edit->monredu->rule = "trim|numeric";
    $edit->monredu->css_class='inputnum';
    
    $edit->monadic =new inputField("monadic","monadic");
    $edit->monadic->size = 12;
    $edit->monadic->maxlength = 17;
    $edit->monadic->rule = "trim|numeric";
    $edit->monadic->css_class='inputnum';
    
    $edit->tasa =new inputField("tasa","tasa");
    $edit->tasa->size = 12;
    $edit->tasa->maxlength = 17;
    $edit->tasa->rule = "trim|numeric";
    $edit->tasa->css_class='inputnum';
    
    $edit->reducida =new inputField("reducida","reducida");
    $edit->reducida->size = 12;
    $edit->reducida->maxlength = 17;
    $edit->reducida->rule = "trim|numeric";
    $edit->reducida->css_class='inputnum';
    
    $edit->sobretasa =new inputField("sobretasa","sobretasa");
    $edit->sobretasa->size = 12;
    $edit->sobretasa->maxlength = 17;
    $edit->sobretasa->rule = "trim|numeric";
    $edit->sobretasa->css_class='inputnum';
    
    $edit->exento =new inputField("exento","exento");
    $edit->exento->size = 12;
    $edit->exento->maxlength = 17;
    $edit->exento->rule = "trim|numeric";
    $edit->exento->css_class='inputnum';
		
		$edit->fecdoc = new DateonlyField("Fecha de Doc", "fecdoc","Y/m/d");
		$edit->fecdoc->size=12;
		
		$edit->afecta = new inputField("afecta", "afecta");
		$edit->afecta->size=12;
		$edit->afecta->maxlength=10;
		$edit->afecta->rule="trim";
		
		$edit->fecapl = new DateonlyField("Fecapl", "fecapl","Y/m/d");
		$edit->fecapl->size=12;
		
		$edit->serie = new inputField("serie", "serie");
		$edit->serie->size=12;
		$edit->serie->maxlength=8;
		$edit->serie->rule="trim";
		
		$edit->depto = new inputField("serie", "depto");
		$edit->depto->size=12;
		$edit->depto->maxlength=3;
		$edit->depto->rule="trim";
		
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();
		
		//$smenu['link']=barra_menu('230');
		$data['content'] = $edit->output;
		//$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
    $data['title']   = "<h1>Pago a Proveedores</h1>";
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);
	}
	function _guarda($do){	
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    
    //$transac=$do->get('transc');
    //$do->db->set('transac', $transac);
		$do->db->set('estampa', 'CURDATE()', FALSE);
		$do->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->db->set('usuario', $this->session->userdata('usuario'));
	}
}