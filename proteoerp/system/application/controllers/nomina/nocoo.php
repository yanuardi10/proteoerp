<?php
//contratos
class Nocoo extends Controller {
	
	var $qformato;
	
	function nocoo(){
		parent::Controller();
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/contabilidad/". $this->uri->segment(2).EXT);
		//$this->datasis->modulo_id(604,1);
	}
	
	function index() {		
		$this->rapyd->load("datagrid","datafilter");
		$this->datasis->modulo_id(715,1);
		
		$filter = new DataFilter("Filtro de noco");
		$filter->db->select("codigo,tipo,nombre,CONCAT_WS('',observa1,observa2 ) AS observa");
		$filter->db->from('noco');
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=40;
		
		//$filter->status = new dropdownField("Status", "tipo");  
		//$filter->status->option("S","Semanal");
		//$filter->status->option("Q","Quincenal");
		//$filter->status->option("M","Mensual");
		//$filter->status->option("O","Otro");
		//$filter->status->style='width:100px';
		
		$filter->buttons("reset","search");
		$filter->build();
    
		$uri = anchor('nomina/nocoo/dataedit/show/<#codigo#>','<#codigo#>');
    
		$grid = new DataGrid();
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;
		
		$grid->column("C&oacute;digo",$uri);
		//$grid->column("T&iacute;tulo","nombre");
		//$grid->column("Observaci&oacute;n","observa");
		//$grid->column("Tipo" ,"tipo" ,"align='center'");
		
		$grid->add("nomina/nocoo/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Contratos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit(){
 		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
 		$modbus=array(
			'tabla'   =>'conc',
			'columnas'=>array(
				'concepto' =>'Concepto',
				'tipo'=>'tipo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('concepto'=>'concepto<#i#>','descrip'=>'descrip<#i#>','tipo'=>'tipo<#i#>'),
			'titulo'  =>'Buscar Cconcepto',
			'p_uri'=>array(4=>'<#i#>')
			);
 		
 		
		$edit = new DataEdit("Contratos","noco");
		/*
		$edit->_dataobject->db->set('transac', 'MANUAL');
		$edit->_dataobject->db->set('origen' , 'MANUAL');
		$edit->_dataobject->db->set('usuario', $this->session->userdata('usuario'));
		$edit->_dataobject->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$edit->_dataobject->db->set('estampa', 'NOW()', FALSE);
		*/
		
		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('delete','_pre_del');
		
		$edit->back_url = "nomina/nocoo";
		
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size = 10;
		$edit->codigo->rule= "required";
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength=8;
		
		$edit->nombre  = new inputField("Nombre", "nombre");
		$edit->nombre->maxlength=60;

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style="width:110px";
		$edit->tipo->option("S","Semanal");
		$edit->tipo->option("Q","Quincenal");
		$edit->tipo->option("M","Mensual");
		$edit->tipo->option("O","Otro");
		
		$edit->observa1  = new inputField("Observaciones", "observa1");
		$edit->observa1->maxlength=60;
		
		$edit->observa2  = new inputField("Observaci&oacute;n", "observa2");
		$edit->observa2->maxlength=60;
		
		$codigo=$edit->_dataobject->get('codigo');
		
		$detalle = new DataDetalle($edit->_status);
		
		//Campos para el detalle
		$detalle->db->select('concepto,descrip,tipo,grupo');
		$detalle->db->from('itnoco');
		$detalle->db->where("codigo='$codigo'");
		
		$detalle->codigo = new inputField2("C&oacute;digo", "concepto<#i#>");
		$detalle->codigo->size=11;
		$detalle->codigo->db_name='concepto';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;
		
		$detalle->descrip = new inputField("Descripci&oacute;n", "descrip<#i#>");
		$detalle->descrip->size=45;
		$detalle->descrip->db_name='descrip';
		$detalle->descrip->maxlength=60;
		
		$detalle->tipo = new inputField("Tipo", "tipo<#i#>");
		$detalle->tipo->size=2;
		$detalle->tipo->db_name='tipo';
		
		$detalle->grupo = new inputField2("Grupo", "grupo<#i#>");
		$detalle->grupo->size=5;
		$detalle->grupo->db_name='grupo';

		//fin de campos para detalle
		/*		
		$detalle->onDelete('totalizar()');
		$detalle->onAdd('totalizar()');
		$detalle->script($script);
		*/
		//$detalle->style="width:110px";
		
		//Columnas del detalle
		$detalle->column("C&oacute;digo"      , "<#codigo#>");
		$detalle->column("Descripci&oacute;n" , "<#descrip#>");
		$detalle->column("Tipo"               , "<#tipo#>");
		$detalle->column("Grupo"              , "<#grupo#>");
		$detalle->build();	
		$conten["detalle"] = $detalle->output;
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_contratos', $conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Contratos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function dpto() {		
		$this->rapyd->load("dataform");
		$campo='ccosto'.$this->uri->segment(4);
 		$script='
 		function pasar(){
			if($F("departa")!="-!-"){
				window.opener.document.getElementById("'.$campo.'").value = $F("departa");
				window.close();
			}else{
				alert("Debe elegir un departamento");
			}
		}';
		
		$form = new DataForm('');
		$form->script($script);
		$form->fdepar = new dropdownField("Departamento", "departa");
		$form->fdepar->option('-!-','Selecci&oacute;n un departamento');
		$form->fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$form->fdepar->onchange='pasar()';
		$form->build_form();
		
		$data['content'] =$form->output;
		$data["head"]    =script('prototype.js').$this->rapyd->get_head();
		$data['title']   ='<h1>Seleccione un departamento</h1>';
		$this->load->view('view_detalle', $data);
	}
	
	function _guarda_detalle($do) {
		$cant=$this->input->post('cant_0');
		$i=$o=0;
		while($o<$cant){
			if (isset($_POST["concepto$i"])){
				if($this->input->post("concepto$i")){
					
					$sql = "INSERT INTO itnoco (codigo,concepto,descrip,tipo,grupo) VALUES(?,?,?,?,?)";
					$llena=array(
							0=>$do->get('codigo'),
							1=>$this->input->post("concepto$i"),
							2=>$this->input->post("descrip$i"),
							3=>$this->input->post("tipo$i"),
							4=>$this->input->post("grupo$i"),
							);
					$this->db->query($sql,$llena);
				}
				$o++;
			}
			$i++;
		}
	}
	
	function _actualiza_detalle($do){
		$this->_borra_detalle($do);
		$this->_guarda_detalle($do);
	}
	
	function _borra_detalle($do){
		$codigo=$do->get('codigo');
		$sql = "DELETE FROM itnoco WHERE codigo='$codigo'";
		$this->db->query($sql);
	}

	function _pre_del($do) {
		$codigo=$do->get('comprob');
		$chek =   $this->datasis->dameval("SELECT COUNT(*) FROM cpla WHERE codigo LIKE '$codigo.%'");
		$chek +=  $this->datasis->dameval("SELECT COUNT(*) FROM itcasi WHERE cuenta='$codigo'");
		
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Plan de Cuenta tiene derivados o movimientos';
			return False;
		}
		return True;
	}
}
?>