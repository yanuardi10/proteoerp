<?php
//retenciones
 class Retecol extends Controller {

	function retecol(){
		parent::Controller(); 
		//required helpers for samples
		$this->load->helper('url');
		$this->load->helper('text');
		//rapyd library
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(515,1);
		redirect("finanzas/retecol/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Filtro por C&oacute;digo", 'rete');
		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=15;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('finanzas/retecol/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Retenciones");
		$grid->order_by("codigo","asc");
		$grid->per_page = 20;

		$grid->column_orderby('C&oacute;digo' ,$uri        ,'codigo');
		$grid->column_orderby('Pago de'       ,'activida'  ,'actividad');
		$grid->column_orderby('Base Imponible','base1'     ,'base1');
		$grid->column_orderby('Porcentaje de Retenci&oacute;n','tari1','tari1');
		$grid->column_orderby('Para pagos mayores a','pama1','pama1');
		$grid->column_orderby('Tipo'   ,'tipo'      ,'tipo' );
		$grid->column_orderby('Renta'  ,'tipocol'   ,'tipocol');

		$grid->add('finanzas/retecol/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Retenciones');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';

		$edit = new DataEdit("Retenciones", "rete");
		$edit->back_url = site_url("finanzas/retecol/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->mode="autohide";
		$edit->codigo->size =7;
		$edit->codigo->maxlength=4;
		$edit->codigo->rule ="required|callback_chexiste";

		$edit->activida = new inputField("Pago de", "activida");
		$edit->activida->size =55;
		$edit->activida->maxlength=45;
		$edit->activida->rule= "strtoupper|required";

		$edit->tipo =  new dropdownField("Tipo", "tipo");
		$edit->tipo->option("JD","Juridico");
		$edit->tipo->option("NR","Natural");
		$edit->tipo->style='width:160px';

		$edit->tipocol =  new dropdownField("Renta", "tipocol");
		$edit->tipocol->option('D','Declarante');
		$edit->tipocol->option('N','No declarante');
		$edit->tipocol->style='width:160px';

		$edit->base1 = new inputField("Base Imponible", "base1");
		$edit->base1->size =13;
		$edit->base1->maxlength=9;
		$edit->base1->css_class='inputnum';
		$edit->base1->rule='numeric';

		$edit->tari1 =new inputField("Porcentaje de Retenci&oacute;n", "tari1");
		$edit->tari1->size =13;
		$edit->tari1->maxlength=10;
		$edit->tari1->css_class='inputnum';
		$edit->tari1->rule='numeric';

		$edit->pama1 = new inputField("Para pagos mayores a", "pama1");
		$edit->pama1->size =13;
		$edit->pama1->maxlength=13;
		$edit->pama1->css_class='inputnum';
		$edit->pama1->rule='numeric';

		$edit->la1 = new containerField('alert','Unidades Tributarias');
		$edit->la1->in='pama1';

		$edit->ut = new inputField("Unidades Tributarias", 'ut');
		$edit->ut->size =13;
		$edit->ut->maxlength=13;
		$edit->ut->css_class='inputnum';
		$edit->ut->rule='numeric';
		$edit->ut->in  ='pama1';


		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
 

		$smenu['link']=barra_menu('515');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$data['content'] = $edit->output;
		$data['title']   = "<h1>Retenciones</h1>";
		$data['head']    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}

	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('activida');
		logusu('rete',"RETENCION $codigo $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('activida');
		logusu('rete',"RETENCION $codigo $nombre MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('activida');
		logusu('rete',"RETENCION $codigo $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		 if (!$this->db->field_exists('ut','rete')) {
			$mSQL="ALTER TABLE rete CHANGE COLUMN tipocol tipocol CHAR(2) NULL DEFAULT '0.0' COLLATE 'utf8_unicode_ci' AFTER cuenta, ADD COLUMN ut DECIMAL(12,2) NULL DEFAULT NULL AFTER tipocol";
			$this->db->simple_query($mSQL);
		 }

		 if (!$this->db->field_exists('tipocol','rete')) {
			$mSQL="ALTER TABLE rete ADD COLUMN tipocol CHAR(1) NULL DEFAULT NULL AFTER cuenta";
			$chek=$this->datasis->dameval("SELECT COUNT(*) FROM rete WHERE codigo='$codigo'");
		 }

		if ($chek > 0){
			$activida=$this->datasis->dameval("SELECT activida FROM rete WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"La retencion $codigo ya existe para la actividad $activida");
			return FALSE;
		}else {
			return TRUE;
		}
	}

	function instalar(){
		if (!$this->db->field_exists('tipocol','mgas')) {
			$mSQL='';
			$this->db->simple_query($mSQL);
		}
	}
}
?>