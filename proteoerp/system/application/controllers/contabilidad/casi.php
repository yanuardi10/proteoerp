<?php
//asientos
class Casi extends Controller {
	
	var $qformato;
	
	function casi(){
		parent::Controller();
		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/contabilidad/". $this->uri->segment(2).EXT);
	}
	
	function index() {		
		$this->rapyd->load("datagrid","datafilter");
		$this->datasis->modulo_id(607,1);
		
		$filter = new DataFilter("Filtro de Asientos");
		$filter->db->select=array("comprob","fecha","descrip","origen","debe","haber","total");
		$filter->db->from('casi');
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d"); 
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->comprob = new inputField("N&uacute;mero"     , "comprob");
		
		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name="descrip"; 
		
		$filter->origen = new dropdownField("Or&iacute;gen", "origen");  
		$filter->origen->option("","Todos");
		$filter->origen->options("SELECT modulo, modulo valor FROM reglascont GROUP BY modulo");
		
		$filter->status = new dropdownField("Status", "status");  
		$filter->status->option("","Todos");
		$filter->status->option("A","Actualizado");
		$filter->status->option("D","Diferido");
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('contabilidad/casi/dataedit/show/<#comprob#>','<#comprob#>');
    
		$grid = new DataGrid();
		$grid->order_by("comprob","asc");
		$grid->per_page = 15;
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Or&iacute;gen"  ,"origen"  ,"align='center'");
		$grid->column("Debe"  ,"debe"  ,"align='right'");
		$grid->column("Haber" ,"haber" ,"align='right'");
		$grid->column("Total" ,"total" ,"align='right'");
		$grid->add("contabilidad/casi/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Asientos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function dataedit(){
 		$this->rapyd->load("dataedit","datadetalle");
 		
 		//$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1');
 		//$qformato='%';
 		//for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
 		//$this->qformato=$qformato;
 		$this->qformato=$qformato=$this->datasis->formato_cpla();
 		
 		$modbus=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta<#i#>','descrip'=>'concepto<#i#>','departa'=>'ccosto<#i#>'),
			'titulo'  =>'Buscar Cuenta',
			'p_uri'=>array(4=>'<#i#>'),
			'where'=>"codigo LIKE \"$qformato\"",
			'script'=>array('departa(<#i#>)')
			);
 		 			
 		$uri="/contabilidad/casi/dpto/";

		//Script necesario para totalizar los detalles
 		$script='
 		function totalizar(){
 			monto=debe=haber=0;
 			amonto=$$(\'input[id^="monto"]\');
			for(var i=0; i<amonto.length; i++) {
    		valor=parseFloat(amonto[i].value);
    		if (isNaN(valor))
					valor=0.0;
				if (valor>0)
    			haber=haber+valor;
    		else{
    			valor=valor*(-1);
    			debe=debe+valor;
    		}
				$("haber").value=haber;
    		$("debe").value=debe;
				$("total").value=haber-debe;
			}
		}
		function departa(i){
			ccosto=$F(\'ccosto\'+i.toString())
			if (ccosto==\'S\'){
				departamen=window.open("'.$uri.'/"+i.toString(),"buscardeparta","width=500,height=200,scrollbars=Yes,status=Yes,resizable=Yes,screenx=5,screeny=5,top="+ ((screen.height - 200) / 2) + ",left=" + ((screen.width - 500) / 2)); 
				departamen.focus();
			}
		}
		';
 		
		$edit = new DataEdit("Asientos","casi");
		
		$edit->_dataobject->db->set('transac', 'MANUAL');
		$edit->_dataobject->db->set('origen' , 'MANUAL');
		$edit->_dataobject->db->set('usuario', $this->session->userdata('usuario'));
		$edit->_dataobject->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$edit->_dataobject->db->set('estampa', 'NOW()', FALSE);
		
		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('delete','_pre_del');
		
		$edit->back_url = site_url("contabilidad/casi/index");
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->size = 10;
		
		$edit->comprob = new inputField("N&uacute;mero", "comprob");
		$edit->comprob->size = 10;
		$edit->comprob->rule= "required";
		$edit->comprob->mode="autohide";
		$edit->comprob->maxlength=8;
		
		$edit->descrip  = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->maxlength=60;
		
		$edit->debe  = new inputField2("Debe", "debe");
		$edit->debe->size = 30;
		$edit->debe->css_class='inputnum';
		$edit->debe->readonly=TRUE;
		
		$edit->haber = new inputField2("Haber", "haber");
		$edit->haber->size = 30;
		$edit->haber->css_class='inputnum';
		$edit->haber->readonly=TRUE;
		
		$edit->total = new inputField("Saldo", "total");
		$edit->total->size = 25;
		$edit->total->css_class='inputnum';
		$edit->total->readonly=TRUE;

		$edit->status = new dropdownField("Status", "status");
		$edit->status->style="width:110px";
		$edit->status->option("A","Actualizado");
		$edit->status->option("D","Diferido");
		
		$comprob=$edit->_dataobject->get('comprob');
		
		$detalle = new DataDetalle($edit->_status);
		
			//Campos para el detalle
			$detalle->db->select('cuenta,referen,concepto,ccosto, debe-haber AS monto');
			$detalle->db->from('itcasi');
			$detalle->db->where('comprob',$comprob);
			
			$detalle->cuenta = new inputField2("Cuenta", "cuenta<#i#>");
			$detalle->cuenta->size=11;
			$detalle->cuenta->db_name='cuenta';
			$detalle->cuenta->append($this->datasis->p_modbus($modbus,'<#i#>'));
			$detalle->cuenta->readonly=TRUE;
			
			$detalle->referencia = new inputField("Referencia", "referen<#i#>");
			$detalle->referencia->size=15;
			$detalle->referencia->db_name='referen';
			$detalle->referencia->maxlength=12;
			
			$detalle->concepto = new inputField("Concepto", "concepto<#i#>");
			$detalle->concepto->size=30;
			$detalle->concepto->db_name='concepto';
			$detalle->concepto->maxlength=60;
			
			$detalle->monto = new inputField("Monto", "monto<#i#>");
			$detalle->monto->css_class='inputnum';
			$detalle->monto->onchange='totalizar()';
			$detalle->monto->size=20;
			$detalle->monto->db_name='monto';
			
			$detalle->departa = new inputField2("Centro Costo", "ccosto<#i#>");
			$detalle->departa->type='hidden';
			$detalle->departa->db_name='ccosto';
			$detalle->departa->onchange='departa(<#i#>)';
    	
			//fin de campos para detalle
			
			$detalle->onDelete('totalizar()');
			$detalle->onAdd('totalizar()');
			$detalle->script($script);
			$detalle->style="width:110px";
			
			//Columnas del detalle
			$detalle->column("Cuenta"    ,"<#cuenta#>");
			$detalle->column("Referencia","<#referencia#>");
			$detalle->column("Concepto"  ,"<#concepto#>");
			$detalle->column("Monto"     ,"<#monto#><#departa#>",'align=right');
			$detalle->build();
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_asiento', $conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Asientos Contables</h1>';
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
		$form->fdepar->option('-!-','Seleccion un departamento');
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
			if (isset($_POST["cuenta$i"])){
				if($this->input->post("cuenta$i")){
					$ccosto=$this->input->post("ccosto$i");
					if ($ccosto!='N')
						$ccosto="'$ccosto'";
					else
						$ccosto="NULL";
						
					$sql = "INSERT INTO itcasi (fecha,comprob,cuenta,referen,concepto,debe,haber,origen,ccosto) VALUES(?,?,?,?,?,?,?,'MANUAL',$ccosto)";
					$debe =($this->input->post("monto$i") > 0)? $this->input->post("monto$i") : 0;
					$haber=($this->input->post("monto$i") < 0)? $this->input->post("monto$i")*(-1) : 0;
					$llena=array(
							0=>$do->get('fecha'),
							1=>$do->get('comprob'),
							2=>$this->input->post("cuenta$i"),
							3=>$this->input->post("referen$i"),
							4=>$this->input->post("concepto$i"),
							5=>$debe,
							6=>$haber);
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
		$comprob=$do->get('comprob');
		$sql = "DELETE FROM itcasi WHERE comprob='$comprob'";
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
	function instalar(){
		$mSQL='ALTER TABLE itcasi ADD id INT AUTO_INCREMENT PRIMARY KEY';
                $this->db->simple_query($mSQL);
	}
}
?>
