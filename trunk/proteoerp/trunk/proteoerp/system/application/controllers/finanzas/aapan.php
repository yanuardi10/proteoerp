<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//anticipos
class Aapan extends validaciones {
		function aapan(){
		parent::Controller(); 
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
   }

	function index(){
		$this->datasis->modulo_id(505,1);
		redirect("finanzas/aapan/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por N&uacute;mero", 'apan');
		
		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size=12;
		$filter->numero->maxlength=8;		

		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=12;
		$filter->nombre->maxlength=30;
		
		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->option("","");
		$filter->tipo->option("C","C");
		$filter->tipo->option("P","P");
		$filter->tipo->style="width:100px";
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/aapan/dataedit/show/<#id#>','<#numero#>');

		$grid = new DataGrid("Lista de Aplicaci&oacute;n de Anticipos");
		$grid->order_by("numero","asc");
		$grid->per_page = 20;

		$grid->column_orderby("N&uacute;mero",$uri,'numero');
		$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
		$grid->column_orderby("Tipo","tipo",'tipo');
		$grid->column_orderby("Clipro","clipro",'clipro');
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column("Monto","<number_format><#monto#>|2|,|.</number_format>" ,'align=right');
								 
		$grid->add("finanzas/apan/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Aplicaci&oacute;n de Anticipos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	function dataedit()
 	{
		$this->rapyd->load("dataedit");		
		$link=site_url('finanzas/aapan/uanticipos');
		$script ='
		function sellupa(){
			nombreintes.value="";
			nombre.value="";
			vtipo=$("#tipo").val();			
				if(vtipo=="P"){
					$("#tr_clipro").show();
					$("#tr_clipro2").hide();
					$("#tr_reinte2").show();
					$("#tr_reinte").hide();
					clipro2.value="";
					reinte.value="";
				}
				if(vtipo=="C"){
					$("#tr_clipro2").show();
					$("#tr_clipro").hide();
					$("#tr_reinte").show();
					$("#tr_reinte2").hide();
					clipro.value="";
					reinte2.value="";
				}		
		}
					
		function ultimo(){			
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo codigo ingresado fue: " + msg );
				}
			});
		}
		$(function() {
			$(".inputnum").numeric(".");
			$("#tipo").change(function () { sellupa(); }).change();
		}		
		);
		';

		$clipro=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed' =>'C&oacute;digo Proveedor','nombre'=>'Nombre','rif'=>'RIF'),
		'retornar'=>array('proveed'=>'clipro','nombre'=>'nombre'),
		'titulo'  =>'Buscar Proveedor');
		$CLIPRO =$this->datasis->modbus($clipro,'clipro');

		$clipro2=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'rifci'=>'RIF'),
		'filtro'  =>array('cliente' =>'C&oacute;digo Cliente','nombre'=>'Nombre','rifci'=>'RIF'),
		'retornar'=>array('cliente'=>'clipro2','nombre'=>'nombre'),
		'titulo'  =>'Buscar Cliente');		
		$CLIPRO2 =$this->datasis->modbus($clipro2,'clipro2');

		$reinte=array(
		'tabla'   =>'sprv',
		'columnas'=>array(
		'proveed' =>'C&oacute;digo Proveedor',
		'nombre'=>'Nombre',
		'rif'=>'RIF'),
		'filtro'  =>array('proveed' =>'C&oacute;digo Proveedor','nombre'=>'Nombre','rif'=>'RIF'),
		'retornar'=>array('proveed'=>'reinte','nombre'=>'nombreintes'),
		'titulo'  =>'Buscar Proveedor');
		$REINTE =$this->datasis->modbus($reinte,'reinte');
		
		$reinte2=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'rifci'=>'RIF'),
		'filtro'  =>array('cliente' =>'C&oacute;digo Cliente','nombre'=>'Nombre','rifci'=>'RIF'),
		'retornar'=>array('cliente'=>'reinte2','nombre'=>'nombreintes'),
		'titulo'  =>'Buscar Cliente');
		$REINTE2 =$this->datasis->modbus($reinte2,'reinte2');

		$edit = new DataEdit("Aplicaci&oacute;n de Anticipos", "apan");
		$edit->back_url = site_url("finanzas/aapan/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');            		
		
		$lnum='<a href="javascript:ultimo();" title="Consultar ultimo codigo ingresado" onclick="">Consultar ultimo codigo</a>';
		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size =12;       
		$edit->numero->rule="trim|required|callback_chexiste";
		$edit->numero->maxlength=8;
		$edit->numero->append($lnum);
		
		$edit->fecha = new DateonlyField("Fecha", "fecha");
		$edit->fecha->size = 12;
		$edit->fecha->rule="required|chfecha";

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("C","Cliente");
		$edit->tipo->option("C","Cliente");
		$edit->tipo->option("P","Proveedor");
		$edit->tipo->style="width:100px";		
			
		$edit->clipro =new inputField("Codigo", "clipro");
		$edit->clipro->db->name="clipro";
		$edit->clipro->rule='trim|required';
		$edit->clipro->size =12;
		$edit->clipro->readonly=true;
		$edit->clipro->append($CLIPRO);		
		$edit->clipro->append("Proveedor");		
			
		$edit->clipro2 =   new inputField("Codigo", "clipro2");
		$edit->clipro2->db->name="clipro";
		$edit->clipro2->rule='trim|required';
		$edit->clipro2->size =12;
		$edit->clipro2->readonly=true;                     
		$edit->clipro2->append($CLIPRO2);
		$edit->clipro2->append("Cliente");
		
		$edit->nombre =   new inputField("Nombre", "nombre");		
		$edit->nombre->size =30;
		$edit->nombre->rule = "trim|strtoupper";
		$edit->nombre->readonly=true;
		
		$edit->monto =    new inputField("Monto", "monto");
		$edit->monto->size = 12;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric';
		$edit->monto->maxlengxlength=0;

		$edit->reinte =   new inputField("Convertido", "reinte");
		$edit->reinte->db->name="reinte";
		$edit->reinte->rule='trim|required';
		$edit->reinte->size =12;
		$edit->reinte->readonly=true;
		$edit->reinte->append($REINTE);
		$edit->reinte->append("Proveedor");		
		
		$edit->reinte2 =new inputField("Convertido", "reinte2");
		$edit->reinte2->db->name="reinte";
		$edit->reinte2->rule='trim|required';
		$edit->reinte2->size =12;
		$edit->reinte2->readonly=true;
		$edit->reinte2->append($REINTE2);
		$edit->reinte2->append("Cliente");
		
		$edit->nombreintes=new inputField("Nombre","nombreintes");
		$edit->nombreintes->size=30;
		$edit->nombreintes->readonly=true;
				
		$edit->observa1 = new inputField("Observaciones", "observa1");
		$edit->observa1->rule='trim';
		$edit->observa1->size =50;
		$edit->observa1->maxlength=50;
		
		$edit->observa2 = new inputField("", "observa2");
		$edit->observa2->rule='trim';
	  $edit->observa2->size =50;
		$edit->observa2->maxlength=50;
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
 
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Aplicaci&oacute;n de Anticipos</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('apan',"ANTICIPO $codigo CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('apan',"ANTICIPO $codigo  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('apan',"ANTICIPO $codigo  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('numero');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM apan WHERE numero='$codigo'");
		if ($chek > 0){
			$this->validation->set_message('chexiste',"El anticipo $codigo ya existe");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	function uanticipos(){
		$consul=$this->datasis->dameval("SELECT numero FROM apan ORDER BY numero DESC");
		echo $consul;
	}
	
	function instalar(){
		$sql="ALTER TABLE `apan`  ADD COLUMN `id` INT(10) NULL AUTO_INCREMENT AFTER `usuario`,  ADD PRIMARY KEY (`id`)";
		$this->db->query($sql);
		
	}
}
?>