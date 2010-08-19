<?php
	class recetas extends Controller {
		function recetas()
	{
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(103,1);
  	}
	function index() {		
		$this->rapyd->load("datagrid","datafilter");
			
		$filter = new DataFilter("Filtro de Recetas",'rece');
		
		$filter->codigo = new inputField("C&oacute;digo","codigo");
		$filter->codigo->size = 10;

    $filter->descri1 = new inputField("Descripci&oacute;n","descri1");
    $filter->descri1->size = 30;

		$filter->buttons("reset","search");
		$filter->build();
    
		$uri = anchor('hospitalidad/recetas/dataedit/show/<#codigo#>/','<#codigo#>');
    
		$grid = new DataGrid();
		$grid->order_by("fecha","desc");
		$grid->per_page = 15;  
		
		$grid->column("C&oacute;digo",$uri);
    $grid->column("Descripci&oacute;n","descri1");
    $grid->column("Precio","<number_format><#precio#>|2</number_format>","align=right");
    $grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
    $grid->column("Costo","<number_format><#costo#>|2</number_format>","align=right");
    $grid->column("Rela","rela","align='center'");
		
		$grid->add("hospitalidad/agregarrec");
		$grid->build();
		
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Recetas</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){
 		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
 		
 		$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1');
 		$qformato='%';
 		for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
 		$this->qformato=$qformato;
 		
 		 	$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			//'retornar'=>array('codigo'=>'codigo<#i#>','precio1'=>'precio1<#i#>','precio2'=>'precio2<#i#>','precio3'=>'precio3<#i#>','precio4'=>'precio4<#i#>','iva'=>'iva<#i#>','pond'=>'costo<#i#>'),
			'retornar'=>array('codigo'=>'codigo<#i#>','descrip'=>'descrip<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Buscar Articulo');
			
		$edit = new DataEdit("recetas","rece");

		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('insert','_pre_insert');
		
		$edit->back_url = "hospitalidad/recetas";
		
		$edit->codigo = new inputField("C&oacute;digo","codigo");
		$edit->codigo->size = 10;
		$edit->codigo->maxlength =8 ;
		$edit->codigo->rule= "trim|required";
		$edit->codigo->mode="autohide";
		
    $edit->descri1 = new inputField("Descripci&oacute;n","descri1");
		$edit->descri1->size = 55;
		$edit->descri1->maxlength=40;		
		$edit->descri1->rule= "trim|required";
		
		$edit->precio= new inputField("Precio","precio");
		$edit->precio->size = 20;		
		$edit->precio->css_class='inputnum';
		$edit->precio->rule='trim';
		
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;
		$edit->fecha->maxlength = 10;
		$edit->fecha->rule= "required"; 
			
		$edit->total = new inputField("Total","costo");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';
		$edit->total->rule='trim';
		                    		
		$edit->rela= new inputField("Relacion Costo/Precio","rela");
		$edit->rela->size = 20;
		$edit->rela->rule= "trim|required";
				
		$codigo=$edit->_dataobject->get('codigo');
		
		$detalle = new DataDetalle($edit->_status);
		
		//Campos para el detalle
		$detalle->db->select('codigo,descrip,cantidad,rendi,costo,monto');
		$detalle->db->from('itrece');
		$detalle->db->where("menu='$codigo'");
		
		$detalle->codigo = new inputField("C&oacute;digo", "codigo<#i#>");
		$detalle->codigo->size=10;
		$detalle->codigo->maxlength=15;
		$detalle->codigo->db_name='codigo';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;
		$detalle->codigo->rule="trim";		
		
		$detalle->descrip = new inputField("Descripci&oacute;n", "descrip<#i#>");
		$detalle->descrip->size=30;
		$detalle->descrip->maxlength=45;
		$detalle->descrip->db_name='descrip';
		$detalle->descrip->rule='trim';
		
		$detalle->cantidad = new inputField("Cantidad", "cantidad<#i#>");
		$detalle->cantidad->size=10;
		$detalle->cantidad->maxlength=13;
		$detalle->cantidad->db_name='cantidad';
		$detalle->cantidad->css_class='inputnum';
		$detalle->cantidad->rule='trim';
		
		$detalle->rendi = new inputField("Rendimiento","rendi<#i#>");
		$detalle->rendi->size=10;
		$detalle->rendi->maxlength=11;
		$detalle->rendi->db_name='rendi';
		$detalle->rendi->css_class='inputnum';
		$detalle->rendi->rule='trim';

		$detalle->costo = new inputField("Costo","costo<#i#>");
		$detalle->costo->css_class='inputnum';
		$detalle->costo->size=10;
		$detalle->costo->db_name='costo';
		$detalle->costo->rule='trim';
		
		
		$detalle->monto = new inputField2("Total","monto<#i#>");
		$detalle->monto->db_name='monto';
		$detalle->monto->size=10;
		$detalle->monto->css_class='inputnum';
		$detalle->monto->rule='trim';
		
		//fin de campos para detalle
		
		$detalle->onDelete('totalizar()');
		$detalle->onAdd('totalizar()');
		//$detalle->script($script);
		$detalle->style="width:110px";
		
		//Columnas del detalle
		$detalle->column("C&oacute;digo"   	  , "<#codigo#>");
		$detalle->column("Descripci&oacute;n" , "<#descrip#>");
		$detalle->column("Ctd"      	 				, "<#cantidad#>");
		$detalle->column("Rd%"      	 				, "<#rendi#>");
		$detalle->column("Costo"    	 				, "<#costo#>");
		$detalle->column("Total"    	 				, "<#monto#>");
	
		$detalle->build();	
		$conten["detalle"] = $detalle->output;
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons("save", "undo", "delete", "back","modify");
		$edit->build();
		
		//$smenu['link']=barra_menu('103');
		//$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_recetas', $conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Recetas</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
function _actualiza_detalle($do){
		$this->_borra_detalle($do);
		$this->_guarda_detalle($do);
	 }

  function _guarda_detalle($do) {
		$cant=$this->input->post('cant_0');
		$i=$o=0;
		while($o<$cant){
			if (isset($_POST["codigo$i"])){
				if($this->input->post("codigo$i")){
						
					$sql = "INSERT INTO itrece (menu,codigo,descrip,cantidad,costo,monto,cantidad,rendi)VALUES(?,?,?,?,?,?,?,?)";
					//$haber=($this->input->post("monto$i") < 0)? $this->input->post("monto$i")*(-1) : 0;
					
					$llena=array(
							0=> $this->input->post("menu$i"),       
							1=> $do->get('codigo'),
							2=> $this->input->post("descrip$i"),
							3=> $this->input->post("cantidad$i"),
							4=> $do->get('costo'),
							5=> $this->input->post("monto$i"),
							6=> $this->input->post("cantidad$i"),
							7=> $this->input->post("rendi$i"), 
							
							);
					$this->db->query($sql,$llena);
				}
				$o++;
			}
			$i++;
		}
	
	function _borra_detalle($do){
		$codigo=$do->get('codigo');
		$sql = "DELETE FROM itrece WHERE menu='$codigo'";
		$this->db->query($sql);
	}
 function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
      
		$sql    = 'INSERT INTO nsfac(usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db-> query($sql);
    $numero =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    
    $do->set('numero' , $numero);
		$do->set('transac', $transac);
		$do->set('estampa', 'CURDATE()', FALSE);
		$do->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->set('usuario', $this->session->userdata('usuario'));
	}
}
}
?>