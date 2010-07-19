<?php	//ordenservicio
class Ords extends Controller {

	function ords(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(522,1);
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
		
		$modbusp=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed'),
			'titulo'  =>'Buscar Proveedor');
		
		$boton=$this->datasis->modbus($modbusp);
		
		$filter = new DataFilter("Filtro de Orden de Servicio");
		$filter->db->select('numero,fecha,nombre,totiva,totbruto,proveed');
		$filter->db->from('ords');
		
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
    $filter->numero->size=20;

		$filter->proveedor = new inputField("Proveedor", "proveed");
		$filter->proveedor->append($boton);
		$filter->proveedor->db_name = "proveed";
    $filter->proveedor->size=20;

		$filter->buttons("reset","search");
		$filter->build();
    
		$uri = anchor('finanzas/ords/dataedit/show/<#numero#>','<#numero#>');
    $uri2 = anchor_popup('formatos/verhtml/ORDS/<#numero#>',"Ver HTML",$atts);
    
		$grid = new DataGrid();
		$grid->order_by("numero","desc");
		$grid->per_page = 15;
		
		$grid->column("N&uacute;mero",$uri);
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","align='center'");
		$grid->column("Nombre","nombre");
		$grid->column("IVA"  ,"totiva"  ,"align='right'");
		$grid->column("Monto" ,"totbruto" ,"align='right'");
		$grid->column("Vista",$uri2,"align='center'");	
		//$grid->add("finanzas/egresos/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Orden de Servicio</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){
 		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
 		
 		$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1');
 		$qformato='%';
 		for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
 		$this->qformato=$qformato;
 		
    $modbusp=array(
    	'tabla'   =>'sprv',
    	'columnas'=>array(
    	'proveed' =>'C&oacute;digo Proveedor',
    	'nombre'=>'Nombre',
    	'rif'=>'RIF'),
    	'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
    	'retornar'=>array('proveed'=>'proveed'),
    	'titulo'  =>'Buscar Proveedor');

    $boton=$this->datasis->modbus($modbusp);
    
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
 		
		$edit = new DataEdit("Orden de Servicio","ords");
		
		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('insert','_pre_insert');
		
		$edit->back_url = "finanzas/ords";
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 20;
			
		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";

		$edit->numero1 = new inputField("N&uacute;mero", "cheque");
		$edit->numero1->size = 10;
		$edit->numero1->rule= "required";
		$edit->numero1->mode="autohide";
		
		$edit->proveedor = new inputField("Proveedor", "proveed");
		$edit->proveedor->size = 10; 
		$edit->proveedor->append($boton);       
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=30;   
		
		$edit->banco = new dropdownField("Banco", "codban");
		$edit->banco->option("BM","BM");
		$edit->banco->option("BV","BV");
		$edit->banco->option("99","99");
	  $edit->banco->style='width:70px;';
    $edit->banco->size =10;
		
		$edit->tipo = new dropdownField("Tipo", "tipo_op");  
	  $edit->tipo->option("CH","CH");
	  $edit->tipo->option("ND","ND");
	  $edit->tipo->size = 10;  
	  $edit->tipo->style='width:70px;';
	
		$edit->comprob  = new inputField2("Comprobante", "comprob");
		$edit->comprob->size = 20;
		
		$edit->beneficiario  = new inputField("Beneficiario", "benefi");
		$edit->beneficiario->size = 30;
	
		$edit->condiciones  = new inputField("Condiciones", "condi");
		$edit->condiciones->size = 35;
		
	  $edit->anticipo  = new inputField("Anticipo", "anticipo");
		$edit->anticipo->size = 20;
		$edit->anticipo->css_class='inputnum';
				
		$edit->impuesto  = new inputField("Impuesto", "totiva");
		$edit->impuesto->size = 20;
		$edit->impuesto->css_class='inputnum';
		
		$edit->total  = new inputField("Total", "totbruto");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';
		
		$edit->subtotal  = new inputField("SubTotal", "totpre");
		$edit->subtotal->size = 20;
		$edit->subtotal->css_class='inputnum';
				
		$numero=$edit->_dataobject->get('numero');
		
		$detalle = new DataDetalle($edit->_status);
		
		//Campos para el detalle
		
		$detalle->db->select('codigo,descrip,precio,iva,importe');
		$detalle->db->from('itords');
		$detalle->db->where("numero='$numero'");
		
		$detalle->codigo = new inputField("C&oacute;digo", "codigo<#i#>");
		$detalle->codigo->size=10;
		$detalle->codigo->db_name='codigo';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;
		
		$detalle->descripcion = new inputField("Descripci&oacute;n", "descrip<#i#>");
		$detalle->descripcion->size=30;
		$detalle->descripcion->db_name='descrip';
		$detalle->descripcion->maxlength=12;
		
		$detalle->impuesto = new inputField("Impuesto", "iva<#i#>");
		$detalle->impuesto->size=20;
		$detalle->impuesto->db_name='iva';
		$detalle->impuesto->maxlength=60;
		$detalle->impuesto->css_class='inputnum';  
		
		$detalle->precio = new inputField("Precio", "precio<#i#>");
		$detalle->precio->css_class='inputnum';
		$detalle->precio->onchange='totalizar()';
		$detalle->precio->size=20;
		$detalle->precio->db_name='precio';
		
		$detalle->importe = new inputField2("Importe", "importe<#i#>");
		$detalle->importe->db_name='importe';
		$detalle->importe->css_class='inputnum';
		$detalle->importe->size=20;

		//fin de campos para detalle
		
		$detalle->onDelete('totalizar()');
		$detalle->onAdd('totalizar()');
		$detalle->script($script);
		$detalle->style="width:110px";
		
		//Columnas del detalle
		
		$detalle->column("C&oacute;digo"    ,  "<#codigo#>");
		$detalle->column("Descripci&oacute;n", "<#descripcion#>");
		$detalle->column("Precio"     , "<#precio#>");
		$detalle->column("Impuesto"  ,  "<#impuesto#>");
		$detalle->column("Importe"    , "<#importe#>");
	
		$detalle->build();	
		$conten["detalle"] = $detalle->output;
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons( "save", "undo","back");
		$edit->build();
		
		$smenu['link']=barra_menu('522');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_ordenservicio', $conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Orden de Servicios</h1>';
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
						
					$sql = "INSERT INTO itords (fecha,numero,proveed,codigo,descrip,precio,importe,unidades,fraccion,almacen,sucursal,departa) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";

					//$haber=($this->input->post("monto$i") < 0)? $this->input->post("monto$i")*(-1) : 0;
					
					$llena=array(
							0=>$do->get('fecha'),
							1=>$do->get('numero'),
							2=>$do->get('proveed'),
							3=>$this->input->post("codigo$i"),
							4=>$this->input->post("descrip$i"),
							5=>$this->input->post("precio$i"),
							6=>$this->input->post("importe$i"),
							7=>$this->input->post("unidades$i"),
							8=>$this->input->post("fraccion$i"),
							9=>$this->input->post("almacen$i"),
						 10=>$this->input->post("sucursal$i"),
						 11=>$this->input->post("departa$i"),
																																			
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
		$sql = "DELETE FROM itords WHERE numero='$numero'";
		$this->db->query($sql);
	}
	function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $transac=$this->db->insert_id();
    
		$sql    = 'INSERT INTO nrds (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $control =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    
    $do->set('numero', $numero);
		$do->set('transac', $transac);
		$do->set('estampa', 'CURDATE()', FALSE);
		$do->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->set('usuario', $this->session->userdata('usuario'));
	}
}
?>