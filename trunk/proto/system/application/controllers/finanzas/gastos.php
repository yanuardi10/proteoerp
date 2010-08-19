<?php
class Gastos extends Controller {
	
	  function Gastos(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(604,1);
	}
	  function index() {		
		redirect("finanzas/gastos/filteredgrid");
	}
		function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$filter = new DataFilter("Filtro de Gastos",'gser');
		$filter->db->select("numero,fecha,vence,nombre,totiva,totneto");
		$filter->db->from('gser');
		
		$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d"); 
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
		
		$filter->numero = new inputField("N&uacute;mero", "numero");
			
		$filter->proveed = new inputField("Proveedor", "proveed");
		//$filter->proveed->append($boton);
		$filter->proveed->db_name = "proveed";

		$filter->buttons("reset","search");
		$filter->build();
    
		$uri = anchor('finanzas/gastos/dataedit/show/<#fecha#>/<#numero#>/<#proveed#>','<#numero#>');
    
		$grid = new DataGrid();
		$grid->order_by("numero","desc");
		$grid->per_page = 15;
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Vence","<dbdate_to_human><#vence#></dbdate_to_human>","align='center'");
		$grid->column("Nombre","nombre");
		$grid->column("IVA"  ,"totiva"  ,"align='right'");
		$grid->column("monto" ,"totneto" ,"align='right'");
		
		$grid->add("finanzas/gastos/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Gastos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){
 		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
 		
 		$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1');
 		$qformato='%';
 		for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
 		$this->qformato=$qformato;
 		
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
 		
 		//Script necesario para totalizar los detalles
 		
		$fdepar = new dropdownField("ccosto", "ccosto");    
		$fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$fdepar->status='create';
		$fdepar->build();
		$dpto=$fdepar->output;
		
		$dpto=trim($dpto);
		$dpto=preg_replace('/\n/i', '', $dpto);
 		
 		$uri=site_url("/contabilidad/casi/dpto/");

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
				//alert("come una matina");
				departamen=window.open("'.$uri.'/"+i.toString(),"buscardeparta","width=500,height=200,scrollbars=Yes,status=Yes,resizable=Yes,screenx=5,screeny=5,top="+ ((screen.height - 200) / 2) + ",left=" + ((screen.width - 500) / 2)); 
				departamen.focus();
				//new Insertion.Before(\'departa\'+i.toString(), \''.$dpto.'\')
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
		
		$edit->back_url = "contabilidad/casi";
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;
		
		$edit->numero = new inputField("N&uacute;mero", "comprob");
		$edit->numero->size = 10;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		
		$edit->proveed = new inputField("C&oacute;digo", "proveed");
		$edit->proveed->size = 7;
		$edit->proveed->rule= "required";
		$edit->proveed->mode="autohide";
		$edit->proveed->maxlength=5;
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 20;
		$edit->nombre->maxlength=40;   
		
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
		$detalle->db->where("comprob='$comprob'");
		
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
		$conten["detalle"] = $detalle->output;
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
			
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_gastos', $conten,true); 
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
}
?>