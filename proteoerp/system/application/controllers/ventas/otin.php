<?php
	class Otin extends Controller {
	//otrosingresos
	function otin()
	{
		parent::Controller(); 
		$this->load->library("rapyd");
    $this->datasis->modulo_id(122,1);      
	}
	function index() {		
		$this->rapyd->load("datagrid","datafilter");
		
		$atts = array(
              'width'      => '800',
              'height'     => '600',
              'scrollbars' => 'yes',
              'status'     => 'yes',
              'resizable'  => 'yes',
              'screenx'    => '0',
              'screeny'    => '0'
            );		
		
    $scli=array(
	  'tabla'   =>'scli',
	  'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
	  'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
	  'retornar'=>array('cliente'=>'cod_cli'),
	  'titulo'  =>'Buscar Cliente');
			
		$boton=$this->datasis->modbus($scli);
		
		$filter = new DataFilter("Filtro de Otros Ingresos");
		$filter->db->select('fecha,numero,cod_cli,nombre,totals,totalg,iva,tipo_doc');
		$filter->db->from('otin');
		
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
		$filter->numero->size = 30;

    $filter->cliente = new inputField("Cliente", "cod_cli");
    $filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build();
    
		$uri = anchor('ventas/otin/dataedit/show/<#tipo_doc#>/<#numero#>','<#numero#>');
    $uri2 = anchor_popup('formatos/verhtml/OTINND/<#tipo_doc#>/<#numero#>',"Ver HTML",$atts);
		
		$grid = new DataGrid();
		$grid->order_by("fecha","desc");
		$grid->per_page = 15;  
		
		$grid->column("N&uacute;mero",$uri);
    $grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
    $grid->column("Nombre","nombre");
    $grid->column("Sub.Total","<number_format><#totals#>|2</number_format>","align=right");
    $grid->column("IVA","<number_format><#iva#>|2</number_format>","align=right");
    $grid->column("Total","<number_format><#totalg#>|2</number_format>","align=right");
    $grid->column("Vista",$uri2,"align='center'");
    

		$grid->add("ventas/agregaroi");
		$grid->build();
		
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1> Otros Ingresos</h1>';
		$this->load->view('view_ventanas', $data);
	}
		function dataedit(){
 		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
 		
 		$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1');
 		$qformato='%';
 		for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
 		$this->qformato=$qformato;
 	 	
 	 	$modbusp=array(
	  'tabla'   =>'scli',
	  'columnas'=> array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'dire11'  =>'Direcci&oacute;n',
		'rifci'   =>'Rif/CI'),
	  'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
	  'retornar'=>array('cliente'=>'cod_cli'),
  	'titulo'  =>'Buscar Cliente');
 
    $boton=$this->datasis->modbus($modbusp);
 		//Script necesario para totalizar los detalles
 		
 		 $modbus=array(
		'tabla'   =>'sinv',
		'columnas'=>array(
		'codigo' =>'C&oacute;digo',
    'descrip'=>'descrip'),
		'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
		//'retornar'=>array('codigo'=>'codigo<#i#>','precio1'=>'precio1<#i#>','precio2'=>'precio2<#i#>','precio3'=>'precio3<#i#>','precio4'=>'precio4<#i#>','iva'=>'iva<#i#>','pond'=>'costo<#i#>'),
		'retornar'=>array('codigo'=>'codigo<#i#>'),
		'p_uri'=>array(4=>'<#i#>'),
		'titulo'  =>'Buscar Articulo');
  		
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
 		
		$edit = new DataEdit(" otrosingresos","otin");
		
		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('insert','_pre_insert');
		
		$edit->back_url = "ventas/ otin";
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;
		
		$edit->vence = new DateonlyField("Vence", "vence","d/m/Y");
		$edit->vence->insertValue = date("Y-m-d");
		$edit->vence->size = 10;

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		
    $edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 55;
		$edit->nombre->maxlength=40;   
		
		$edit->iva  = new inputField("IVA", "iva");
		$edit->iva->size = 20;
		$edit->iva->css_class='inputnum';
		
		$edit->subtotal  = new inputField("Sub.Total", "totals");
		$edit->subtotal->size = 20;
		$edit->subtotal->css_class='inputnum';
		
		$edit->total  = new inputField("Total", "totalg");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';

		$edit->cliente = new inputField("Cliente"  , "cod_cli");
		$edit->cliente->size = 10;        
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton); 
		
		$edit->rifci   = new inputField("RIF/CI"  , "rifci");
		$edit->rifci->size = 20;        
		
		$edit->direc = new inputField("Direcci&oacute;n","direc");
		$edit->direc->size = 55;  
		
		$edit->dire1 = new inputField(" ","dire1");
		$edit->dire1->size = 55; 
		
		$edit->observaciones = new inputField("Observaciones"  , "observa1");
		$edit->observaciones->size = 40; 
		
		$edit->observaciones1 = new inputField("Observaciones"  , "observa2");
		$edit->observaciones1->size = 40;  	 		  
		
		$edit->orden  = new inputField("Orden","orden");
		$edit->orden->size = 20;
						
		$edit->tipo = new dropdownField("Tipo", "tipo_doc");  
		$edit->tipo->option("FC","FC");  
		$edit->tipo->option("ND","ND");
		$edit->tipo->option("OT","OT");
		$edit->tipo->size = 20;  
	  $edit->tipo->style='width:70px;';
		
		$numero=$edit->_dataobject->get('numero');
		$tipo  =$edit->_dataobject->get('tipo_doc');
				
		$detalle = new DataDetalle($edit->_status);
		
		//Campos para el detalle
		
		$detalle->db->select('codigo,descrip,precio,impuesto,importe');
		$detalle->db->from('itotin');
		$detalle->db->where("numero='$numero'");
		$detalle->db->where("tipo_doc='$tipo'");
		
		$detalle->codigo = new inputField("C&oacute;digo", "codigo<#i#>");
		$detalle->codigo->size=18;
		$detalle->codigo->db_name='codigo';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;
		
		$detalle->descripcion = new inputField("Descripci&oacute;n", "descrip<#i#>");
		$detalle->descripcion->size=30;
		$detalle->descripcion->db_name='descrip';
		$detalle->descripcion->maxlength=12;
			
		$detalle->precio = new inputField("Precio", "precio<#i#>");
		$detalle->precio->css_class='inputnum';
		$detalle->precio->size=20;
		$detalle->precio->db_name='precio';
		
		$detalle->impuesto = new inputField("Impuesto", "impuesto<#i#>");
		$detalle->impuesto->css_class='inputnum';
		$detalle->impuesto->size=20;
		$detalle->impuesto->db_name='impuesto';
		
		$detalle->total = new inputField2("Total", "importe<#i#>");
		$detalle->total->db_name='importe';
		$detalle->total->size=20;
		$detalle->total->css_class='inputnum';
		
		//fin de campos para detalle
		
		$detalle->onDelete('totalizar()');
		$detalle->onAdd('totalizar()');
		$detalle->script($script);
		$detalle->style="width:110px";
		
		//Columnas del detalle
		$detalle->column("C&oacute;digo"      , "<#codigo#>");
		$detalle->column("Descripci&oacute;n" , "<#descripcion#>");
		$detalle->column("Precio"      , "<#precio#>");
		$detalle->column("Impuesto"    , "<#impuesto#>");
		$detalle->column("Total"       , "<#total#>");
	
		$detalle->build();	
		$conten["detalle"] = $detalle->output;
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons( "save", "undo", "delete", "back");
		$edit->build();
		
		$smenu['link']=barra_menu('122');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_otrosingresos', $conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Otros Ingresos</h1>';
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
						
					$sql = "INSERT INTO itspre (tipo_doc,numero,codigo,descrip,precio,impuesto,importe,transac,usuario,estampa,hora,cantidad,larga)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
					//$haber=($this->input->post("monto$i") < 0)? $this->input->post("monto$i")*(-1) : 0;
					
					$llena=array(
							0=> $do->get('tipo_doc'),        
							1=> $do->get('numero'), 
							2=> $this->input->post("codigo$i"),
							3=> $this->input->post("descrip$i"),
							4=> $this->input->post("precio$i"),
							5=> $this->input->post("impuest$i"),
							6=> $this->input->post("importe$i"),
							7=> $do->get('transac'), 
							8=> $do->get('usuario'), 
							9=> $do->get('estampa'), 
						 10=> $do->get('hora'), 
						 11=> $this->input->post("cantidad$i"),
						 12=> $this->input->post("larga$i"),
					
							);
					$this->db->query($sql,$llena);
				}
				$o++;
			}
			$i++;
		}
	  function _borra_detalle($do){
		$numero=$do->get('numero');
		$sql = "DELETE FROM itotin WHERE numero='$numero'";
		$this->db->query($sql);
	}
	  function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
      
		$sql    = 'INSERT INTO notind (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db-> query($sql);
    $numero =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    
    $do->set('numero', $numero);
		$do->set('transac', $transac);
		$do->set('estampa', 'CURDATE()', FALSE);
		$do->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->set('usuario', $this->session->userdata('usuario'));
	}
}
}
?>

