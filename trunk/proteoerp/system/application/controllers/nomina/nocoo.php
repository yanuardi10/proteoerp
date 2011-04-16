<?php
class Nocoo extends Controller {
	
	function nocoo(){
		parent::Controller();
		$this->load->library("rapyd");
	}
	
	function index() {		
		$this->rapyd->load("datagrid","datafilter");
		$this->datasis->modulo_id(715,1);
		
		$filter = new DataFilter("Filtro de Contrato de Nomina",'noco');
		
		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=10;
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=40;
		
		$filter->buttons("reset","search");
		$filter->build();
    
		$uri = anchor('nomina/nocoo/dataedit/show/<#codigo#>','<#codigo#>');
    
		$grid = new DataGrid();
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;
		
		$grid->column_orderby("C&oacute;digo",$uri,'codigo');
		$grid->column_orderby("Nombre","nombre",'nombre');
		$grid->column_orderby("Observaci&oacute;n","observa1",'observa1');
		
		$grid->add("nomina/nocoo/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Contratos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit(){
 		$this->rapyd->load('dataobject','datadetails');
 		$modbus=array(
			'tabla'   =>'conc',
			'columnas'=>array(
				'concepto' =>'Concepto',
				'tipo'=>'tipo',
				'descrip'=>'Descripci&oacute;n',
 				'grupo'=>'Grupo'),
			'filtro'  =>array('concepto'=>'C&ocaute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('concepto'=>'concepto_<#i#>','descrip'=>'descrip_<#i#>',
								'tipo'=>'it_tipo_<#i#>','grupo'=>'grupo_<#i#>'),
			'titulo'  =>'Buscar Cconcepto',
			'p_uri'=>array(4=>'<#i#>')
			);
 		$btn=$this->datasis->p_modbus($modbus,'<#i#>');
 		
		$do = new DataObject("noco");
		$do->rel_one_to_many('itnoco', 'itnoco', array('codigo'));
		
		$edit = new DataDetails('Contratos', $do);
		$edit->back_url = site_url('nomina/nocoo/index');
		$edit->set_rel_title('itnoco','Contratos <#o#>');

		//$edit->pre_process('insert' ,'_pre_insert');
		//$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size = 10;
		$edit->codigo->rule= "required";
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength=8;
		
		$edit->nombre  = new inputField("Nombre", "nombre");
		$edit->nombre->maxlength=40;
		$edit->nombre->rule="required";
		$edit->codigo->size = 30;

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style="width:110px";
		$edit->tipo->option("S","Semanal");
		$edit->tipo->option("Q","Quincenal");
		$edit->tipo->option("M","Mensual");
		$edit->tipo->option("O","Otro");
		
		$edit->observa1  = new inputField("Observaciones", "observa1");
		$edit->observa1->maxlength=60;
		$edit->observa1->size = 60;
		
		$edit->observa2  = new inputField("Observaci&oacute;n", "observa2");
		$edit->observa2->maxlength=60;
		$edit->observa2->size = 60;
		
		
		//Campos para el detalle
		
		$edit->concepto = new inputField("C&oacute;ncepto <#o#>", "concepto_<#i#>");
		$edit->concepto->size=11;
		$edit->concepto->db_name='concepto';
		$edit->concepto->append($btn);
		$edit->concepto->readonly=TRUE;
		$edit->concepto->rel_id = 'itnoco';
		
		$edit->descrip = new inputField("Descripci&oacute;n <#o#>", "descrip_<#i#>");
		$edit->descrip->size=45;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=60;
		$edit->descrip->rel_id = 'itnoco';
		$edit->descrip->readonly=TRUE;
		
		$edit->it_tipo = new inputField("Tipo <#o#>", "it_tipo_<#i#>");
		$edit->it_tipo->size=2;
		$edit->it_tipo->db_name='tipo';
		$edit->it_tipo->rel_id = 'itnoco';
		$edit->it_tipo->readonly=TRUE;
		
		$edit->grupo = new inputField("Grupo <#o#>", "grupo_<#i#>");
		$edit->grupo->size=5;
		$edit->grupo->db_name='grupo';
		$edit->grupo->rel_id = 'itnoco';
		$edit->grupo->readonly=TRUE;

		//fin de campos para detalle

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();
		
		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_noco', $conten,true);
		$data['title']   = heading('Contratos de Nomina');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}
	
	function _post_insert($do){
		$codigo=$do->get('codigo');
		logusu('noco',"Contrato de Nomina $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('codigo');
		logusu('noco',"Contrato de Nomina $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		logusu('noco',"Contrato de Nomina $codigo ELIMINADO");
	}
}
?>