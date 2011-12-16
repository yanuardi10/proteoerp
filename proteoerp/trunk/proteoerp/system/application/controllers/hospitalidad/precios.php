<?php
class Precios extends Controller {
	
	function Precios(){
		parent::Controller(); 
		$this->load->library("rapyd");

	}
	
	function index(){
	  $this->datasis->modulo_id(810,1);
		redirect("hospitalidad/precios/filteredgrid");
	}
	
	function filteredgrid(){

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Precios", 'prec');
		
		$filter->codigo = new inputField("Codigo","codigo");
		$filter->codigo->size=15;
		
		$filter->aplica = new inputField("Descripcion","aplica");
		$filter->aplica->size=30;
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('hospitalidad/precios/dataedit/show/<#codigo#>','<#codigo#>');
		
		$grid = new DataGrid("Lista de Precios");
		$grid->order_by("codigo","asc");
		$grid->per_page = 10;
		
		$grid->column("C&oacute;digo", $uri);
		$grid->column("Descripci&oacute;n","aplica","aplica");
		$grid->column("Tipo","tipo");
		$grid->column("Moneda","moneda");
		$grid->column("Precio","precio");
		$grid->column("Temporada Alta","alta");
		$grid->column("IVA","iva");
		
		$grid->add("hospitalidad/precios/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Precios</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	
	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';
		
		$edit = new DataEdit("Precios", "prec");
		$edit->back_url = site_url("hospitalidad/precios/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->codigo = new inputField("Codigo","codigo");
		$edit->codigo->rule = "required|callback_chexiste";
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength =3;
		$edit->codigo->size =5;	
		
		$edit->aplica = new inputField("Descripci&oacute;n", "aplica");
		$edit->aplica->size=40;     
		$edit->aplica->maxlength=30;
		$edit->aplica->rule = "trim|strtoupper|required";
		
		$edit->tipo = new inputField("Tipo", "tipo");
		$edit->tipo->rule = "trim";
		$edit->tipo->size=7;
		$edit->tipo->maxlength=5;		
		
		$edit->moneda = new dropdownField("Moneda", "moneda");
		$edit->moneda->option("","");
		$edit->moneda->options("SELECT moneda,descrip FROM mone where moneda>='' ORDER BY descrip ");
		$edit->moneda->style="width:120px";
		
		$edit->precio = new inputField("Precio", "precio");
		$edit->precio->size=14;
		$edit->precio->maxlength=11;
		$edit->precio->css_class='inputnum';
		$edit->precio->rule='trim|numeric';
		
		$edit->alta = new inputField("Alta", "alta");
		$edit->alta->size=14;
		$edit->alta->maxlength=11;
		$edit->alta->css_class='inputnum';
		$edit->alta->rule='trim|numeric';
		
		$edit->iva = new inputField("IVA", "iva");
		$edit->iva->size=9;
		$edit->iva->maxlength=5;
		$edit->iva->css_class='inputnum';
		$edit->iva->rule='trim|numeric';
		
		$edit->desgas1 = new dropdownField("Gasto1", "desgas1");
		$edit->desgas1->option("","");
		$edit->desgas1->options("SELECT cod_gas,descrip FROM hgas where cod_gas>='' ORDER BY descrip  ");
		
		$edit->mongas1 = new inputField("Monto", "mongas1");
		$edit->mongas1->in = "desgas1";
		$edit->mongas1->size=14;
		$edit->mongas1->maxlength=11;
		$edit->mongas1->css_class='inputnum';
		$edit->mongas1->rule='trim|numeric';
		
		$edit->desgas2 = new dropdownField("Gasto2", "desgas2");
		$edit->desgas2->option("","");
		$edit->desgas2->options("SELECT cod_gas,descrip FROM hgas where cod_gas>='' ORDER BY descrip  ");
		
		$edit->mongas2 = new inputField("Monto", "mongas2");
		$edit->mongas2->in = "desgas2";
		$edit->mongas2->size=14;	
		$edit->mongas2->maxlength=11;
		$edit->mongas2->css_class='inputnum';
		$edit->mongas2->rule='trim|numeric';
		
		$edit->desgas3 = new dropdownField("Gasto3", "desgas3");
		$edit->desgas3->option("","");
		$edit->desgas3->options("SELECT cod_gas,descrip FROM hgas where cod_gas>='' ORDER BY descrip  ");
		
		$edit->mongas3 = new inputField("Monto", "mongas3");
		$edit->mongas3->in = "desgas3";
		$edit->mongas3->size=14;  
		$edit->mongas3->maxlength=11;
		$edit->mongas3->css_class='inputnum';
		$edit->mongas3->rule='trim|numeric';
		
		$edit->desgas4 = new dropdownField("Gasto4", "desgas4");
		$edit->desgas4->option("","");
		$edit->desgas4->options("SELECT cod_gas,descrip FROM hgas where cod_gas>='' ORDER BY descrip  ");
		
		$edit->mongas4 = new inputField("Monto", "mongas4");
		$edit->mongas4->in = "desgas4";
		$edit->mongas4->size=14;
		$edit->mongas4->maxlength=11;
		$edit->mongas4->css_class='inputnum';	
		$edit->mongas4->rule='trim|numeric';
				
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
		$data['title']   = "<h1>Precios</h1>";        
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data); 
	}
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('aplica');
		logusu('prec',"PRECIO $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('aplica');
		logusu('prec',"PRECIO $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('aplica');
		logusu('prec',"PRECIO $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM prec WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT aplica FROM prec WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el precio $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
}
?>