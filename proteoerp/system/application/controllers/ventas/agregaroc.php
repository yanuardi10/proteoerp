<?php
class agregaroc extends Controller {
	
	function agregaroc(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
	}
	
	function index() {	
		redirect('compras/agregaroc/encab/create');
	}

	function encab(){
		$this->rapyd->load("dataedit");
		
		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor');
		
		$boton=$this->datasis->modbus($modbus);
		
		$edit = new DataEdit("Ingresar Compras","scst");

		$edit->pre_process( 'insert','_pre_insert');
		$edit->post_process('insert','_post_insert');

		$edit->back_url = "compras/scst";
		
		$edit->proveedor = new inputField("Proveedor", "proveed");
		$edit->proveedor->size = 10;
		$edit->proveedor->maxlength=5;
		$edit->proveedor->readonly=1;
		$edit->proveedor->rule= "required";
		$edit->proveedor->append($boton);
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;
		$edit->nombre->readonly=1;
		$edit->nombre->in='proveedor';
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->rule= "require";
		$edit->fecha->size = 11;
		
		$edit->status = new dropdownField("Estatus","status");  
		$edit->status->option("PE","PE");  
		$edit->status->option("CA","CA");
		$edit->status->option("BA","BA");
		$edit->status->size = 20;  
	  $edit->status->style='width:70px;';
			
		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 15;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		
		$edit->cfis = new inputField("Control fiscal", "nfiscal");
		$edit->cfis->size = 15;
		$edit->cfis->maxlength=12;
		$edit->cfis->rule= "required";
		
		$edit->almacen = new dropdownField("Almacen", "depo");
		$edit->almacen->size = 15;
		$edit->almacen->maxlength=8;
		$edit->almacen->option("","Seleccionar");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N'");
		$edit->almacen->rule= "required";
		$edit->almacen->style='width:150px;';

		$edit->tipo = new dropdownField("Tipo", "tipo_doc");  
		$edit->tipo->option("FC","Factura Credito");  
		$edit->tipo->option("NC","Nota Credito");
		$edit->tipo->option("NE","Nota Entrega");
		$edit->tipo->rule = "required";  
	  $edit->tipo->size = 20;  
	  $edit->tipo->style='width:150px;';

		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output; 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function detalles($control=NULL){
		$this->rapyd->load("datagrid");
		
		$mSQL="SELECT * FROM scst WHERE control='$control'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$pdata = $query->row_array();
		}

		$grid = new DataGrid();
		$grid->db->select=array('codigo','descrip','cantidad','costo','importe');
		$grid->db->from('itscst');
		$grid->db->where('control',$control);
		$grid->order_by("codigo","desc");
		//$grid->per_page = 15;

		$uri=anchor("/compras/agregaroc/mdetalle/$control/modify/<#id#>",'<#codigo#>');
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Cantidad","cantidad"  ,"align='right'");
		$grid->column("Precio"  ,"costo"     ,"align='right'");
		$grid->column("Importe"  ,"importe"  ,"align='right'");
		
		$grid->add("compras/agregaroc/mdetalle/$control/create");
		$grid->build();
    
		$pdata['items']  = $grid->output;
		$data['content'] = $this->load->view('view_agcompras', $pdata,true); 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>agregaroc Art&iacute;culos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function mdetalle($control){
		$this->rapyd->load("dataedit");
		$_POST['control']=$control;
		
		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'descrip'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
			'retornar'=>array('codigo'=>'codigo',
			                  'descrip'=>'descrip',
			                  'precio1'=>'iprecio1',
			                  'precio2'=>'iprecio2',
			                  'precio3'=>'iprecio3',
			                  'precio4'=>'iprecio4',
			                  'margen1'=>'imargen1',
			                  'margen2'=>'imargen2',
			                  'margen3'=>'imargen3',
			                  'margen4'=>'imargen4',
			                  'existen'=>'iexisten',
			                  'pond'=>'ipond',
			                  'formcal'=>'formcal',
			                  'iva'=>'iiva',
			                  'tipo'=>'itipo'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'script'=>array('dbuscar()')
			);

		$boton=$this->datasis->modbus($modbus);
		
		$edit = new DataEdit("Ingresar Art&iacute;culos","itscst");
		$edit->pre_process( 'insert','_pre_minsert');
		$edit->post_process('insert','_post_insert');
		$edit->back_url = "compras/agregaroc/detalles/$control";
		
		if($edit->_status=='modify'){
			$codigo=$edit->_dataobject->get('codigo');
			$mSQL="SELECT margen1,margen2,margen3,margen4,precio1,precio2,precio3,precio4, ultimo, pond,formcal,existen,tipo,iva FROM sinv WHERE codigo='$codigo'";
			//echo $mSQL;
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$row = $query->row_array();
				$data = array(
				     'imargen1' => $row['margen1'],
				     'imargen2' => $row['margen2'],
				     'imargen3' => $row['margen3'],
				     'imargen4' => $row['margen4'],
				     'iprecio1' => $row['precio1'],
				     'iprecio2' => $row['precio2'],
				     'iprecio3' => $row['precio3'],
				     'iprecio4' => $row['precio4'],
				     'iprecio4' => $row['precio4'],
				     'iultimo'  => $row['ultimo' ],
				     'ipond'    => $row['pond'   ],
				     'iiva'     => $row['iva'    ],
				     'formcal'  => $row['formcal'],
				     'iexisten' => $row['existen'],
				     'itipo'    => $row['tipo'   ],
				   );
				   $iva    =$row['iva'];
				   $formcal=$row['formcal'];
				   $ultimo =$row['ultimo' ];
				   $pond   =$row['pond'];
				   $cana=$edit->_dataobject->get('cantidad');
				   $precio=$edit->_dataobject->get('costo');
				   $costo= ($formcal=='P') ? round(($row['pond']*$row['existen']+$precio*$cana)/($row['existen']+$cana) ,2) : $precio;
			}
		}else{
			$data = array(
    	     'imargen1' => '',
    	     'imargen2' => '',
    	     'imargen3' => '',
    	     'imargen4' => '',
    	     'iprecio1' => '',
    	     'iprecio2' => '',
    	     'iprecio3' => '',
    	     'iprecio4' => '',
    	     'iprecio4' => '',
    	     'iultimo'  => '',
    	     'ipond'    => '',
    	     'iiva'     => '',
    	     'formcal'  => '',
    	     'iexisten' => '',
    	     'itipo'    => '',
    	   );
		}

		$mp=form_hidden($data);
		
		$edit->codigo = new inputField("Codigo", "codigo");
		$edit->codigo->size = 16;
		$edit->codigo->maxlength=15;
		$edit->codigo->readonly=1;
		$edit->codigo->rule= "required";
		$edit->codigo->append($boton);
		
		$edit->descrip = new inputField("Decripcion", "descrip");
		$edit->descrip->maxlength=40;
		$edit->descrip->size = 41;
		$edit->descrip->rule= "required";
		$edit->descrip->mode="autohide";
		$edit->descrip->readonly=1;
		$edit->descrip->in='codigo';
		
		$edit->costo = new inputField("Costo", "costo");
		$edit->costo->size = 15;
		$edit->costo->maxlength=20;
		$edit->costo->rule= "required";
		$edit->costo->insertValue='0.0';
		$edit->costo->css_class='inputnum';

		$edit->cantidad = new inputField("Cantidad", "cantidad");
		$edit->cantidad->size = 10;
		$edit->cantidad->insertValue='0';
		$edit->cantidad->maxlength=15;
		$edit->cantidad->rule= "required|callback_ccana";
		$edit->cantidad->css_class='inputnum';

		$edit->importe = new inputField("Importe", "importe");
		$edit->importe->size = 15;
		$edit->importe->maxlength=20;
		$edit->importe->rule= "required";
		$edit->importe->insertValue='0.0';
		$edit->importe->css_class='inputnum';
		
		$edit->flote = new DateonlyField("Fecha de lote", "flote","d/m/Y");
		$edit->flote->insertValue = date("Y-m-d");
		$edit->flote->size = 11;
		//$edit->fecha->rule= "require";

		$edit->cpre = new containerField("cpre",'<a href="javascript:pajuste()">Modificar M&aacute;rgenes</a>, <a href="javascript:majuste()">Modificar Precios</a>');  		

		for($i=1;$i<=4;$i++){
			$obj="margen$i";
			$data = array(
         'name'      => $obj,
         'id'        => $obj,
         'maxlength' => '6',
         'size'      => '6',
         'class'     => 'inputnum');
      
      if($edit->_status=='modify'){
      	$base =$edit->_dataobject->get('precio'.$i);
      	$data['value']=round(100-($costo*100/$base),2);
      }
			if(isset($_POST[$obj]))$data['value']=$_POST[$obj];
			$edit->$obj = new freeField("Ajuste $i",$obj,'Costo + '.form_input($data).'% = ');
			
			$obj="base$i";
			$edit->$obj = new inputField("Base $i", "base$i");
			$edit->$obj->size = 15;
			$edit->$obj->db_name = "precio$i";
			$edit->$obj->maxlength=20;
			$edit->$obj->rule= "required";
			$edit->$obj->css_class='inputnum';
			$edit->$obj->in="margen$i";
			
			$obj="precio$i";
			$data = array(
      	'name'      => $obj,
      	'id'        => $obj,
      	'maxlength' => '15',
      	'size'      => '15',
      	'class'     => 'inputnum');
			if($edit->_status=='modify'){
				$base=$edit->_dataobject->get('precio'.$i);
				$iva =$edit->_dataobject->get('iva');
				$data['value']=round($base*(1+($iva/100)),2);
			}
      if(isset($_POST[$obj]))$data['value']=$_POST[$obj];
			$edit->$obj = new freeField("Precio $i",$obj,'+ IVA = '.form_input($data));
			$edit->$obj->in="margen$i";
		}
		
		$edit->container = new containerField("mp",$mp);  

		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['script']  ='<script type="text/javascript">
		$(function() {
			$("#tr_flote").hide();
			$(".inputnum").numeric(".");
			$("input[id^=\'precio\'],input[id^=\'margen\'],input[id^=\'base\']").floatnumber(".",2);
			$("#costo").floatnumber(".",2);
			
			$("#costo,#cantidad").keyup(function () {
    	  var precio  =parseFloat($("#costo").val());
				var cantidad=parseFloat($("#cantidad").val());
				n = precio*cantidad;
        s = n.toFixed(2);
        $("#importe").val(s); 
        cmargen();
    	});
    	
    	$("#importe").keyup(function () {
    	  var importe =parseFloat($("#importe").val());
				var cantidad=parseFloat($("#cantidad").val());
				n = importe/cantidad;
        s = n.toFixed(2);
        $("#costo").val(s); 
        cmargen();
    	});
    	
    	$("input[@id^=\'precio\']").keyup(function () { cprecio(); });
    	$("input[@id^=\'base\']").keyup(function () { cbase(); });
    	$("input[@id^=\'margen\']").keyup(function () { cmargen();});
		})
		
		function cprecio(){
			iva  = parseFloat($("#iiva").val());
			costo= ocosto();
			for(var i=1;i<=4;i++){
				nbase="base"+i;
				nmarg="margen"+i;
				nprec="precio"+i;

				precio=parseFloat($("#"+nprec).val());
				base  =precio*100/(100+iva);
				margen=100-(costo*100/base);
				
				base  =base.toFixed(2);
				margen=margen.toFixed(2);
				precio=precio.toFixed(2);
				
				$("#"+nbase).val(base);
				$("#"+nmarg).val(margen);
			}
		}
		
		function cbase(){
			iva  = parseFloat($("#iiva").val());
			costo= ocosto();
			for(var i=1;i<=4;i++){
				nbase="base"+i;
				nmarg="margen"+i;
				nprec="precio"+i;
				
				base  =parseFloat($("#"+nbase).val());
				precio=base*(1+iva/100);
				margen=100-(costo*100/base);
				
				margen=margen.toFixed(2);
				precio=precio.toFixed(2);
				
				$("#"+nprec).val(precio);
				$("#"+nmarg).val(margen);
			}
		}
		
		function cmargen(){
			iva  = parseFloat($("#iiva").val());
			costo= ocosto();
			for(var i=1;i<=4;i++){
				nbase="base"+i;
				nmarg="margen"+i;
				nprec="precio"+i;
				
				margen=parseFloat($("#"+nmarg).val());
				base  =costo*100/(100-margen);
				precio=base*(1+iva/100);
				
				base  =base.toFixed(2);
				precio=precio.toFixed(2);
				
				$("#"+nbase).val(base);
				$("#"+nprec).val(precio);
			}
		}
		
		function ocosto(){
			costo   = parseFloat($("#costo").val());
			formcal = $("#formcal").val();
			if(formcal == "P"){
				cantidad= parseFloat($("#cantidad").val());
				pond    = parseFloat($("#ipond").val());
				existen = parseFloat($("#iexisten").val());
				costo   = (costo*cantidad+pond*existen)/(existen+cantidad);
			}
			return costo;
		}
		
		//Ajusta conservando margenes
		function majuste(){
			for(var i=1;i<=4;i++){
				nmarg="margen"+i;
				margen=parseFloat($("#i"+nmarg).val());
				$("#"+nmarg).val(margen);
			}
			cmargen();
		}
		
		//Ajusta conservando precios
		function pajuste(){
			for(var i=1;i<=4;i++){
				nprec="precio"+i;
				precio=parseFloat($("#i"+nprec).val());
				$("#"+nprec).val(precio.toFixed(2));
			}
			cprecio();
		}
		
		function dbuscar(){
			tipo=$("#itipo").val();
			if(tipo[0]=="L")
				$("#tr_flote").show();
			majuste();
		}
		</script>';
		$data['content'] = $edit->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function ccana($cana){
		if($cana>0) return TRUE;
		$this->validation->set_message('ccana', "Debe ingresar una cantidad positiva");
		return FALSE;
			
	}

	function _pre_minsert($do){
		$control=$this->uri->segment(4);
		$codigo=$do->get('codigo');
		$sql = "SELECT iva,ultimo  FROM sinv WHERE codigo='$codigo'";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0){
			$row = $query->row_array();
			$iva =$row['iva'];
			$do->set('iva'   , $iva);
			$do->set('ultimo',$row['ultimo']);
		}else{
			return false;
		}
		$costo=$do->get('costo');
    $sql = "SELECT fecha,numero,proveed,depo,transac  FROM scst WHERE control='$control'";
    $query = $this->db->query($sql);
		if ($query->num_rows() > 0){
		   $row = $query->row_array();
		   $do->set('fecha'  , $row['fecha']);
		   $do->set('numero' , $row['numero']);
		   $do->set('proveed', $row['proveed']);
		   $do->set('depo'   , $row['depo']);
		   $do->set('transac', $row['transac']);
		} else{
			return false;	
		}
    
    $do->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->db->set('estampa', 'CURDATE()', FALSE);
    $do->set('control', $control);
    $do->set('montoiva', $iva*$costo/100);
		$do->set('usuario' , $this->session->userdata('usuario'));
	}

	function _pre_del($do){
		$codigo=$do->get('comprob');
		$check =   $this->datasis->dameval("SELECT COUNT(*) FROM cpla WHERE codigo LIKE '$codigo.%'");
		$check +=  $this->datasis->dameval("SELECT COUNT(*) FROM itcasi WHERE cuenta='$codigo'");

		if ($check > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Plan de Cuenta tiene derivados o movimientos';
			return False;
		}
		return True;
	}
	
	function _post_insert($do){
		$codigo=$this->input->post('control');
		redirect("/compras/agregaroc/detalles/$codigo");
	}

	function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $transac=$this->db->insert_id();
    
		$sql    = 'INSERT INTO nscst (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $control =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    $_POST['control']=$control;
    
    $do->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->db->set('estampa', 'CURDATE()', FALSE);
    $do->set('control', $control);
		$do->set('transac', $transac);
		$do->set('usuario', $this->session->userdata('usuario'));
	}

	function instalar(){
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	}
}
?>