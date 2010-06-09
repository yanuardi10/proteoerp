<?php
class notadespacho extends Controller {
	
	function notadespacho(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(109,1);
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
		
		$filter = new DataFilter("Filtro de Nota Despacho");
		$filter->db->select('fecha,numero,cod_cli,nombre,precio,fechafa');
		$filter->db->from('snot');

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

		$filter->factura = new inputField("Factura", "factura");
		$filter->factura->size = 30;

		$filter->cliente = new inputField("Cliente", "cod_cli");
		$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/notadespacho/dataedit/show/<#numero#>','<#numero#>');
		$uri2 = anchor_popup('formatos/verhtml/SNOT/<#numero#>',"Ver HTML",$atts);

		$grid = new DataGrid();
		$grid->order_by("fecha","desc");
		$grid->per_page = 15;  
		
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Nombre","nombre");
		$grid->column("Fecha Fact.","<dbdate_to_human><#fechafa#></dbdate_to_human>","align='center'");
		$grid->column("Precio","<number_format><#precio#>|2</number_format>","align=right");
		$grid->column("Vista",$uri2,"align='center'");

		$grid->add("ventas/agregarnd");
		$grid->build();
		
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Nota Despacho</h1>';
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

		$modbus=array(
		'tabla'   =>'sinv',
		'columnas'=>array(
		'codigo' =>'C&oacute;digo',
		'descrip'=>'descrip'),
		'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
		//'retornar'=>array('codigo'=>'codigo<#i#>','precio1'=>'precio1<#i#>','precio2'=>'precio2<#i#>','precio3'=>'precio3<#i#>','precio4'=>'precio4<#i#>','iva'=>'iva<#i#>','pond'=>'costo<#i#>'),
		'retornar'=>array('codigo'=>'codigoa<#i#>'),
		'p_uri'=>array(4=>'<#i#>'),
		'titulo'  =>'Buscar Articulo');
		
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
		
		$edit = new DataEdit("notadespacho","snot");
		
		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('insert','_pre_insert');
		
		$edit->back_url = "ventas/notadespacho/index";
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;
		
		$edit->fecha1 = new DateonlyField("Fecha", "fechafa","d/m/Y");
		$edit->fecha1->insertValue = date("Y-m-d");
		$edit->fecha1->size = 10;

		$edit->factura = new inputField("Factura", "factura");
		$edit->factura->size = 10;
			
		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 55;
		$edit->nombre->maxlength=40; 
		
		$edit->cliente = new inputField("Cliente"  , "cod_cli");
		$edit->cliente->size = 10;
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton); 
		
		$numero=$edit->_dataobject->get('numero');
				
		$detalle = new DataDetalle($edit->_status);
		
		//Campos para el detalle
		
		$detalle->db->select('codigo,descrip,cant,saldo,entrega');
		$detalle->db->from('itsnot');
		$detalle->db->where("numero='$numero'");
		
		$detalle->codigo = new inputField("C&oacute;digo", "codigoa<#i#>");
		$detalle->codigo->size=18;
		$detalle->codigo->db_name='codigo';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;
		
		$detalle->descripcion = new inputField("Descripci&oacute;n", "descrip<#i#>");
		$detalle->descripcion->size=30;
		$detalle->descripcion->db_name='descrip';
		$detalle->descripcion->maxlength=12;
		
		$detalle->cantidad = new inputField("Cantidad", "cant<#i#>");
		$detalle->cantidad->size=10;
		$detalle->cantidad->db_name='cant';
		$detalle->cantidad->maxlength=60;
		$detalle->cantidad->css_class='inputnum';

		$detalle->saldo = new inputField("Saldo", "saldo<#i#>");
		$detalle->saldo->size=20;
		$detalle->saldo->db_name='saldo';
		$detalle->saldo->maxlength=60;
		$detalle->saldo->css_class='inputnum';
		
		$detalle->entrega = new inputField("Entrega", "entrega<#i#>");
		$detalle->entrega->size=20;
		$detalle->entrega->db_name='entrega';
		$detalle->entrega->maxlength=60;
		$detalle->entrega->css_class='inputnum';
		
		//fin de campos para detalle
		
		$detalle->onDelete('totalizar()');
		$detalle->onAdd('totalizar()');
		$detalle->script($script);
		$detalle->style="width:110px";
		
		//Columnas del detalle
		$detalle->column("C&oacute;digo"    ,  "<#codigo#>");
		$detalle->column("Descripci&oacute;n", "<#descripcion#>");
		$detalle->column("Cantidad"  ,  "<#cantidad#>");
		$detalle->column("Saldo"     , "<#saldo#>");
		$detalle->column("Entrega"    , "<#entrega#>");
	
		$detalle->build();
		$conten["detalle"] = $detalle->output;
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();
		
		$smenu['link']=barra_menu('109');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_notadespacho', $conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Nota Despacho</h1>';
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
			if (isset($_POST["codigo$i"])){
				if($this->input->post("codigo$i")){
						
					$sql = "INSERT INTO itsnot (numero,codigo,descrip,cant,saldo,entrega,factura,precio) VALUES(?,?,?,?,?,?,?,?)";
					//$haber=($this->input->post("monto$i") < 0)? $this->input->post("monto$i")*(-1) : 0;
					
					$llena=array(
							0=>$do->get('numero'),
							1=>$this->input->post("codigo$i"),
							2=>$this->input->post("desca$i"),
							3=>$this->input->post("cant$i"),
							4=>$this->input->post("saldo$i"),
							5=>$this->input->post("entrega$i"),
							6=>$do->get('factura'),
							7=>$do->get('precio'),

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
		$numero=$do->get('numero');
		$sql = "DELETE FROM itsnot WHERE numero='$numero'";
		$this->db->query($sql);
	}
		
	function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
		$query  =$this->db->query($sql);
		$transac=$this->db->insert_id();

		$sql    = 'INSERT INTO nsnot (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
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