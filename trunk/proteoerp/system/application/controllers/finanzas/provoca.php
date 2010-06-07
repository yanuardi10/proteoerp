<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Provoca extends validaciones {
	
	function Provoca(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(206,1);
		define ("THISFILE",   APPPATH."controllers/finanzas". $this->uri->segment(2).EXT);
   }

	function index(){
		redirect("finanzas/provoca/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro de proveedores ocasionales", 'provoca');
		
		$filter->rif = new inputField("RIF", "rif");
		$filter->rif->maxlength=13;
		$filter->rif->size = 14;
		
		$filter->nombre = new inputField("Nombre", "nombre");
				
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/provoca/dataedit/show/<#rif#>','<#rif#>');

		$grid = new DataGrid("Filtro de Proveedores Ocasionales");
		$grid->order_by("nombre","asc");
		$grid->per_page = 10;
		
		$grid->column("RIF",$uri);
		$grid->column("Nombre","nombre");
		$grid->column("Fecha"   ,"<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		
		$grid->add("finanzas/provoca/dataedit/create");
		$grid->build();
	
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Proveedores Ocasionales</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){
		$this->rapyd->load("dataedit");
				
		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
				'cliente' =>'C&oacute;digo Socio',
				'nombre'=>'Nombre', 
				'cirepre'=>'Rif/Cedula',
				'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Socio','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'socio'),
			'titulo'  =>'Buscar Socio');
		
		$qformato=$this->qformato=$this->datasis->formato_cpla();
		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
			);
		
		$boton =$this->datasis->modbus($mSCLId);
		$bcpla =$this->datasis->modbus($mCPLA);
		
		$smenu['link']=barra_menu('131');
		$consulrif=$this->datasis->traevalor('CONSULRIF');
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		
		function anomfis(){
				vtiva=$("#tiva").val();
				if(vtiva=="C" || vtiva=="E" || vtiva=="R"){
					$("#tr_nomfis").show();
					$("#tr_riff").show();
				}else{
					$("#nomfis").val("");
					$("#rif").val("");
					$("#tr_nomfis").hide();
					$("#tr_rif").hide();
				}
		}
		
		function consulrif(){
				vrif=$("#rif").val();
				if(vrif.length==0){
					alert("Debe introducir primero un RIF");
				}else{
					vrif=vrif.toUpperCase();
					$("#rif").val(vrif);
					window.open("'.$consulrif.'"+"?p_rif="+vrif,"CONSULRIF","height=350,width=410");
				}
		}
		
		';
		$edit = new DataEdit("proveedor", "provoca");
		$edit->back_url = site_url("finanzas/provoca/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->rif =  new inputField("RIF", "rif");
		$edit->rif->mode="autohide";
		$edit->rif->rule = "strtoupper|required|callback_chrif";
		$edit->rif->append($lriffis);
		$edit->rif->maxlength=10;
		$edit->rif->size = 14;
		
		$edit->nombre =  new inputField("Nombre", "nombre");
		$edit->nombre->rule = "strtoupper|required";
		$edit->nombre->size = 80;
		$edit->nombre->maxlength=80;
		
		$edit->fecha =  new dateField("Fecha", "fecha","d/m/Y");
		$edit->fecha->size = 10;
				
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Proveedores Ocasionales</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _pre_del($do) {
		$codigo=$do->get('rif');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM gitser WHERE rif='$codigo'");
		
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='no se puede borrar';
			return False;
		}
		return True;
	}
	function _post_insert($do){
		$codigo=$do->get('rif');
		$nombre=$do->get('nombre');
		logusu('provoca',"PROVEEDOR OCASIONAL $codigo NOMBRE $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('rif');
		$nombre=$do->get('nombre');
		logusu('provoca',"PROVEEDOR OCASIONAL $codigo NOMBRE $nombre MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('rif');
		$nombre=$do->get('nombre');
		logusu('provoca',"PROVEEDOR OCASIONAL $codigo NOMBRE $nombre ELIMINADO");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM importtgas WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM importtgas WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el gasto $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>