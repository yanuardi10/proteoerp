<?php
//banco
require_once(BASEPATH.'application/controllers/validaciones.php');
class Banc extends Validaciones {
    var $pre_functions = array("delete" => "banco_delete" );
    var $pos_functions = array();

    function banc() {
      parent::Controller();
      $this->load->helper('form');
      $this->load->helper('url');
      $this->load->helper('text');
      $this->load->library('rapyd');
      define("THISFILE", APPPATH."controllers/nomina".$this->uri->segment(2).EXT);
   }
   
   function index() {
      $this->datasis->modulo_id(512,1);
    	redirect("finanzas/banc/filteredgrid");
   }
   function setup()
   {
      $content["content"] = $this->load->view('rapyd/setup', null, true);
      $content["code"] = "";
      $content["rapyd_head"] = "";
      $this->load->view('rapyd/banco_template', $content);
   }
    
   function banco_delete($llave) {
      //echo   "ELIMINADO $llave";
      return false;
   }
   function filteredgrid() {
		
		$this->rapyd->load("datafilter","datagrid");
		$filter = new DataFilter("Filtro de Banco", "banc");
		
		$filter->codbanc = new inputField("C&oacute;digo", "codbanc");
		$filter->codbanc->size=12;
		
		$filter->banco = new dropdownField("Banco", "banco");                    
		$filter->banco->option("","");                            
		$filter->banco->options("SELECT codbanc,banco FROM banc where tbanco<>'CAJ' ORDER BY codbanc"); 
		$filter->banco->style ="width:190px;";
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('finanzas/banc/dataedit/show/<#codbanc#>','<#codbanc#>');

		$grid = new DataGrid("Lista de Bancos");
		$grid->use_function("number_format");
		$grid->per_page = 10;
		
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Banco","banco","banco");
		$grid->column("Tipo","tbanco");
		$grid->column("Nro Cuenta","numcuent");
		$grid->column("Saldo","<number_format><#saldo#>|2</number_format>","align=right ");
		
		$grid->add("finanzas/banc/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Bancos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
  function dataedit(){
		$this->rapyd->load("dataedit");
		
		$atts = array(
				'width'     =>'800',
				'height'    =>'600',
				'scrollbars'=>'yes',
				'status'    =>'yes',
				'resizable' =>'yes',
				'screenx'   =>'5',
				'screeny'   =>'5');
		
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
				
		$bcpla =$this->datasis->modbus($mCPLA);
		
		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'codprv'),
			'titulo'  =>'Buscar Proveedor');
		
		$boton=$this->datasis->modbus($modbus);
		
		$mTBAN=array(
			'tabla'   =>'tban',
			'columnas'=>array(
				'cod_banc' =>'C&oacute;digo',
				'nomb_banc'=>'Banco'),
			'filtro'  =>array('cod_banc'=>'C&oacute;digo','nomb_banc'=>'Banco'),
			'retornar'=>array('cod_banc'=>'tbanco','nomb_banc'=>'banco'),
			'titulo'  =>'Buscar Banco'
			);					
				
		$bTBAN =$this->datasis->modbus($mTBAN);
		
		$link=site_url('finanzas/banc/ubanc');
		$script ='
		function gasto(){			
			a=parseInt(dbporcen.value);
			if(a>0 && a<100){
				$("#tr_gastoidb").show();
			}else{
				$("#tr_gastoidb").hide();
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
			gasto();
			$(".inputnum").numeric(".");
		});	
		';
		
		$edit = new DataEdit("Banco", "banc");
		$edit->back_url = site_url("finanzas/banc/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$lultimo='<a href="javascript:ultimo();" title="Consultar ultimo codigo ingresado" onclick="">Consultar ultimo codigo</a>';		
		$edit->codbanc = new inputField("C&oacute;digo", "codbanc");
		$edit->codbanc->rule = "trim|required|callback_chexiste";
		$edit->codbanc->mode="autohide";
		$edit->codbanc->maxlength=2;
		$edit->codbanc->size =3;
		$edit->codbanc->append($lultimo);	
		
		$edit->tbanco = new inputField("Caja/Banco", "tbanco");		
		$edit->tbanco->size =12;		
		$edit->tbanco->maxlength =3;
		$edit->tbanco->rule="trim|required";
		$edit->tbanco->readonly=true;		
		$edit->tbanco->append($bTBAN);
		
		$edit->banco = new inputField("Descripci&oacute;n", "banco");
		$edit->banco->size =25;
		$edit->banco->maxlength=30;
		$edit->banco->readonly=true;	

		$edit->numcuent = new inputField("Nro. de Cuenta", "numcuent");
		$edit->numcuent->rule='trim';
		$edit->numcuent->size = 25;
		$edit->numcuent->maxlength=25;		
		
		$edit->dire1 = new inputField("Direcci&oacute;n", "dire1");
		$edit->dire1->rule='trim';
		$edit->dire1->size =50;
		$edit->dire1->maxlength=40;
		
		$edit->dire2 = new inputField("", "dire2");
		$edit->dire2->rule='trim';
		$edit->dire2->size =50;
		$edit->dire2->maxlength=40;
		
		$edit->telefono = new inputField("Tel&eacute;fono", "telefono");
		$edit->telefono->rule='trim';
		$edit->telefono->size =25;
		$edit->telefono->maxlength=40;
		
		$edit->nombre = new inputField("Nombre del Gerente", "nombre");
		$edit->nombre->rule='trim';
		$edit->nombre->size =25;
		$edit->nombre->maxlength=40;
		
		$edit->moneda = new dropdownField("Moneda","moneda");
		$edit->moneda->options("SELECT moneda, descrip FROM mone ORDER BY moneda");
		$edit->moneda->style ="width:100px;";
		
		$edit->tipocta = new dropdownField("Tipo de Cuenta", "tipocta");
		$edit->tipocta->style ="width:100px;";
		$edit->tipocta->option("C","Corriente");
		$edit->tipocta->options(array("K"=>"Caja","C"=>"Corriente","A" =>"Ahorros","P"=>"Plazo Fijo" ));
		
		$edit->proxch = new inputField("Proximo Cheque", "proxch");
		$edit->proxch->rule='trim';
		$edit->proxch->size =12;
		$edit->proxch->maxlength=12;
		
		$edit->saldo = new inputField("Saldo Actual", "saldo");		
		$edit->saldo->size =12;
		$edit->saldo->readonly=true;				
		
		$edit->dbporcen = new inputField("Porcentaje de debito", "dbporcen");
		$edit->dbporcen->rule='trim';
		$edit->dbporcen->size =12;
		$edit->dbporcen->maxlength=5;
		$edit->dbporcen->rule = "callback_chporcent";
		$edit->dbporcen->onchange="gasto()";
		
		$lcuent=anchor_popup("/contabilidad/cpla/dataedit/create","Agregar Cuenta Contable",$atts);		
		$edit->cuenta = new inputField("Cuenta. Contable", "cuenta");
		$edit->cuenta->rule='trim|callback_chcuentac';
		$edit->cuenta->size =12;		
		$edit->cuenta->readonly=true;
		$edit->cuenta->append($bcpla);
		$edit->cuenta->append($lcuent);
		
		$lsprv=anchor_popup("/compras/sprv/dataedit/create","Agregar Proveedor",$atts);
		$edit->codprv = new inputField("Proveedor", "codprv");
		$edit->codprv->size = 12;		
		$edit->codprv->readonly=1;
		$edit->codprv->rule= "required";
		$edit->codprv->append($boton);
		$edit->codprv->append($lsprv);
		
		$edit->depto = new dropdownField("Departamento", "depto");
		$edit->depto->option("","");
		$edit->depto->size =13;
		$edit->depto->options("SELECT depto, descrip FROM dpto ORDER BY descrip");
		$edit->depto->rule="required";
		$edit->depto->style ="width:225px;";
		
		$edit->sucur = new dropdownField("Sucursal", "sucur");
		$edit->sucur->option("","");
		$edit->sucur->size =13;
		$edit->sucur->options("SELECT codigo, sucursal FROM sucu ORDER BY sucursal");
		$edit->sucur->style ="width:225px;";
		
		$edit->gastoidb = new dropdownField("Gasto I.D.B.", "gastoidb");
		$edit->gastoidb->option("","");				
		$edit->gastoidb->options("SELECT codigo, descrip FROM mgas ORDER BY descrip");
		
		$edit->gastoidb->style ="width:350px;";
		
		$edit->gastocom = new dropdownField("Gasto Comisi&oacute;n", "gastocom");
		$edit->gastocom->option("","");		
		$edit->gastocom->options("SELECT codigo, descrip FROM mgas ORDER BY descrip");
		$edit->gastocom->rule="required";
		$edit->gastocom->style ="width:350px;";

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Bancos</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	function _post_insert($do){
		$codigo=$do->get('codbanc');
		$nombre=$do->get('banco');
		logusu('banc',"BANCO $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('codbanc');
		$nombre=$do->get('banco');
		logusu('banc',"BANCO $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('codbanc');
		$nombre=$do->get('banco');
		logusu('banc',"BANCO $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codbanc');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM banc WHERE codbanc='$codigo'");
		if ($chek > 0){
			$banco=$this->datasis->dameval("SELECT banco FROM grup WHERE codbanc='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el banco $banco");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	function ubanc(){
		$consul=$this->datasis->dameval("SELECT codbanc FROM banc ORDER BY codbanc DESC");
		echo $consul;
	}
}

/*
function dataset()
{
   
   $this->rapyd->load("dataset");
   
   $dataset = new DataSet($this->data);
   $dataset->base_url = site_url('rapydsamples/dataset');
   $dataset->uri_segment = 3;
   $dataset->per_page = 5;
   $dataset->build();
   
   $data["items"] = $dataset->data;
   $data["navigator"] = $dataset->navigator;
   
   $content["content"] = $this->load->view('rapyd/dataset', $data, true);
   $content["rapyd_head"] = "";
   $content["code"] = ''; //highlight_code_file(THISFILE, "//dataset//", "//enddataset//");
   $this->load->view('rapyd/banco_template', $content);
   
}

function datagrid()
{
  
   $this->rapyd->load("datagrid");
   
   $grid = new DataGrid("Lista de bancos", "banco");
   $grid->per_page = 5;
   $grid->use_function("substr","strtoupper");
   $grid->column_detail("Codigo","codbanc",site_url('rapydsamples/dataedit/show/<#nombre#>'));
   
   $grid->column("Valor",'valor', ' style="color:#ff0000" ');
   $grid->build();
   
   $data["grid"] = $grid->output;
   
   $content["content"] = $this->load->view('rapyd/datagrid', $data, true);
   $content["rapyd_head"] = $this->rapyd->get_head();
   $content["code"] = '';  //highlight_code_file(THISFILE, "//datagrid//", "//enddatagrid//");
   $this->load->view('rapyd/banco_template', $content);
}

function datatable()
{
   
   $this->rapyd->load("datatable");
   
   $table = new DataTable(null, $this->data);
   $table->base_url = site_url('rapydsamples/datatable');
   $table->uri_segment = 3;
   $table->per_row = 3;
   $table->per_page = 6;
   $table->use_function("substr","strtoupper");
   
   $table->cellTemplate = '
    <div style="padding:4px">
      <div style="color:#119911; font-weight:bold"><#title#></div>
      This is the body number <substr><#banc#>|5|100</substr>
    </div>'; 
   $table->build();
   
   $data["table"] = $table->output;
   
   $content["content"] = $this->load->view('rapyd/datatable', $data, true);
   $content["rapyd_head"] = $this->rapyd->get_head();
   $content["code"] = '';  //highlight_code_file(THISFILE, "//datatable//", "//enddatatable//");
   $this->load->view('rapyd/banco_template', $content);
}

function dataobject()
{
  
   $this->rapyd->load("dataobject");
   
   $do = new DataObject("banc");
   $do->load(1);
   $title = $do->get("banco");
   
   $data["title"] = $title;
   
   $content["content"] = $this->load->view('rapyd/dataobject', $data, true);
   $content["rapyd_head"] = "";
   $content["code"] = ''; //highlight_code_file(THISFILE, "//dataobject//", "//enddataobject//");
   $this->load->view('rapyd/banco_template', $content);
   
}

function dataform()
{
  
   $this->rapyd->load("dataform");
   
   $form = new DataForm("rapydsamples/dataform/process", null);
   
   $form->codbanc = new inputField("Banco", "codbanc");
   $form->codbanc->rule = "required|max_length[2]";
   $form->numcuent = new inputField("Nro. de Cuenta", "numcuent");
   $form->tbanco = new inputField("Tipo", "tbanco");
   $form->banco = new inputField("Banco", "banco");
   $form->dire1 = new inputField("Direccion", "dire1");
   $form->dire2 = new inputField("", "dire2");
   $form->telefono = new inputField("Telefono", "telefono");
     
   $form->submit("btnsubmit","SUBMIT");
   $form->build_form();
   
   if  ($form->on_show()) {
      $data["form_status"] = "Form displayed correctly";
   }
   
   if ($form->on_success()){
      $data["form_status"] = "Successful post:<br/>".nl2br(var_export($_POST,true));
   }
   
   if ($form->on_error()){
      $data["form_status"] = "Ocurrieron errores";
   }
   
   $data["form"] = $form->output;
  
   $content["content"] = $this->load->view('rapyd/dataform', $data, true);
   $content["rapyd_head"] = $this->rapyd->get_head();
   $content["code"] = ''; //highlight_code_file(THISFILE, "//dataform//", "//enddataform//");
   $this->load->view('rapyd/banco_template', $content);
   
   }
*/

	
?>
