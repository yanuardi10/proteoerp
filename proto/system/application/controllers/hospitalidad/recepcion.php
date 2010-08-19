<?php
class recepcion extends Controller {
	function recepcion(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}
 
	function index(){
		//redirect("hospitalidad/recepcion/dataedit");
		$this->rapyd->load("datagrid","datafilter");
			
		$filter = new DataFilter("Filtro de Recepcion","hres");
				
		$filter->localizador = new inputField("Localiza", "localiza");
    $filter->localizador->size=20;
 
		$filter->buttons("reset","search");
		$filter->build();
    
		$uri = anchor('hospitalidad/recepcion/dataedit/show/<#localiza#>','<#localiza#>');
    
		$grid = new DataGrid();
		$grid->order_by("localiza","desc");
		$grid->per_page = 15;
		
		$grid->column("Localiza",$uri);
		$grid->column("Habitacion","habit");
		$grid->column("Entrada","<dbdate_to_human><#fecha_in#></dbdate_to_human>","align='center'");
		$grid->column("Salidad","<dbdate_to_human><#fecha_ou#></dbdate_to_human>","align='center'");
		$grid->column("Confirma","confirma");
		$grid->column("Cedula","cedula");
		$grid->column("nombre","nombre");

		$grid->add("hospitalidad/recepcion/dataedit/create");
		$grid->build();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Recepcion</h1>';
		$this->load->view('view_ventanas', $data);
		
	}
	
	function dataedit(){
	$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
 		
 		$edit = new DataEdit("Recepci&oacute;n","hres");
 		$edit->back_url = "hospitalidad/recepcion";
 		
 		$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1');
 		$qformato='%';
 		for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
 		$this->qformato=$qformato;
 			
		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('insert','_pre_insert');
		
 		$modbus=array(
		'tabla'   =>'hgas',
		'columnas'=>array(
		'cod_gas' =>'C&oacute;digo',
		'descrip'=>'Descripci&oacute;n'),
		'filtro'  =>array('cod_gas' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
		'retornar'=>array('cod_gas'=>'codigoa<#i#>'),
		'p_uri'=>array(4=>'<#i#>'),
		'titulo'  =>'Buscar Cargo');
		
		$edit->huesped = new inputField("Huesped","nombre");
		$edit->huesped->size = 50;
		$edit->huesped->rule= "trim|required";
		$edit->huesped->maxlength=8;
				
		$edit->fecha_in = new DateonlyField("Ingreso", "fecha_in","d/m/Y");
		$edit->fecha_in->insertValue = date("Y-m-d");
		$edit->fecha_in->size = 10;
		
		$edit->fecha_ou = new DateonlyField("Salidad", "fecha_ou","d/m/Y");
		$edit->fecha_ou->insertValue = date("Y-m-d");
		$edit->fecha_ou->size = 10;
			
		$edit->cuenta = new inputField("Cuenta","localiza");
		$edit->cuenta->size = 10;
		$edit->cuenta->rule= "trim|required";
		$edit->cuenta->maxlength=20;
		
		$edit->habit = new inputField("Habitaci&oacute;n", "habit");
		$edit->habit->size = 5;        
		$edit->habit->maxlength=5;
		$edit->habit->rule="trim";
		
		$edit->ocupantes = new inputField("Nombre", "nombre");
		$edit->ocupantes->size =  50;
		$edit->ocupantes->maxlength=40;
		$edit->ocupantes->rule="trim";
		
		$edit->folio  = new inputField("Folio", "folio");
		$edit->folio->size = 20;
		$edit->folio->css_class='inputnum';
		$edit->folio->rule='trim';

		$edit->otro  = new inputField("Otro", "otro");
		$edit->otro->size = 20;
		$edit->otro->css_class='inputnum';
		$edit->otro->rule='trim';

		$edit->total  = new inputField("Total", "total");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';
		$edit->total->rule='trim';

		$edit->saldo = new inputField("Saldo", "saldo");
		$edit->saldo->size = 20;
		$edit->saldo->css_class='inputnum';
		$edit->saldo->rule='trim';
		
		$localiza=$edit->_dataobject->get('localiza');
		$detalle = new DataDetalle($edit->_status);
		
		//Campos para el detalle	
		$detalle->db->select('codigoa,fecha,refe,desca,tota');
		$detalle->db->from('hcon');
		$detalle->db->where("numa='$localiza'");
    
		$detalle->codigo = new inputField("Codigo", "codigoa<#i#>");
		$detalle->codigo->size=10;
		$detalle->codigo->db_name='codigoa';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;
		$detalle->codigo->rule="trim";
		
		
		$detalle->fecha = new inputField("Fecha", "fecha<#i#>");
		$detalle->fecha->insertValue = date("Y-m-d");
		$detalle->fecha->size = 10;
		$detalle->fecha->db_name='fecha';
		$detalle->codigo->rule="trim";
		
		$detalle->referent = new inputField("Referent", "refe<#i#>");
		$detalle->referent->size=10;
		$detalle->referent->db_name='refe';
		$detalle->referent->maxlength=12;
		$detalle->referent->rule="trim";
		
		$detalle->descrip = new inputField("Descripci&oacute;n", "desca<#i#>");
		$detalle->descrip->size=30;
		$detalle->descrip->db_name='desca';
		$detalle->descrip->maxlength=12;
		$detalle->descrip->rule="trim";
		
		$detalle->monto = new inputField("Monto","tota<#i#>");
		$detalle->monto->css_class='inputnum';
		$detalle->monto->size=20;
		$detalle->monto->db_name='tota';
		$detalle->monto->rule="trim";
	
		//fin de campos para detalle
		//$detalle->onDelete("totalizar('I');");
		//$detalle->onAdd('totalizar('I')');
		//$detalle->script($script);
		$detalle->style="width:110px";
		
		//Columnas del detalle
		$detalle->column("Codigo"      ,  "<#codigo#>");
		$detalle->column("Fecha"       ,"fecha");
		$detalle->column("Referent"    ,  "<#referent#>");
		$detalle->column("Descripci&oacute;n" ,  "<#descrip#>");
		$detalle->column("Monto"       , "<number_format><#monto#>|2|,|.</number_format>",'align=right');

		
		$detalle->build();	
		$conten["detalle"] = $detalle->output;
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons("save", "undo", "delete", "back","modify");
		$edit->build();
		
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_recepcion',$conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Recepci&oacute;n</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function _guarda_detalle($do) {
		$cant=$this->input->post('cant_0');
		$i=$o=0;
		while($o<$cant){
			if (isset($_POST["codigoa$i"])){
				if($this->input->post("codigoa$i")){
						
					$sql = "INSERT INTO hcon (numa,codigoa,desca,tota,fecha,refe,iva,dollar,precio,cajero,habit,hora,fijo,imptur,fturno,anulado) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
					//$haber=($this->input->post("monto$i") < 0)? $this->input->post("monto$i")*(-1) : 0;
					
					$llena=array(
							0=>  $this->input->post("numa$i"),
							1=>  $this->input->post("codigoa$i"),
							2=>  $this->input->post("desca$i"),
							3=>  $this->input->post("tota$i"),
							4=>  $do->get("fecha$i"),
							5=>  $this->input->post("refe$i"),
							6=>  $this->input->post("iva$i"),
							7=>  $this->input->post("dollar$i"),
							8=>  $do->get("precio$i"),
							9=>  $do->get("cajero$i"),
							10=> $do->get("habit$i"),
							11=> $do->get("hora$i"),
							12=> $this->input->post("fijo$i"),
							13=> $do->get("imptur$i"),
							14=> $this->input->post("fturno$i"),
							15=> $this->input->post("anulado$i"),
      
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
		$numero=$do->get('numa');
		$sql = "DELETE FROM hcon WHERE numa='$localiza'";
		$this->db->query($sql);
	}
	
	function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $transac=$this->db->insert_id();
    
		$sql    = 'INSERT INTO nsnte (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $numero =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    
    $do->set('numero', $numero);
		$do->set('transac', $transac);
		$do->set('estampa', 'CURDATE()', FALSE);
		$do->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->set('usuario', $this->session->userdata('usuario'));
  
	}
}
?>