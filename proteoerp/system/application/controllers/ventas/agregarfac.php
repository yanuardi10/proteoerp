<?php
class agregarfac extends Controller {
	
	function agregarfac(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
	}
	
	function index() {	
		redirect('ventas/agregarfac/encab/create');
	}

	function encab(){
		$this->rapyd->load("dataedit");
		
		$modbus=array(
		'tabla'   =>'vend',
		'columnas'=>array(
		'vendedor' =>'C&oacute;digo Vendedor',
		'nombre'=>'Nombre'),
		'filtro'  =>array('vendedor'=>'C&oacute;digo Vendedor','nombre'=>'Nombre'),
		'retornar'=>array('vendedor'=>'vd'),
		'titulo'  =>'Buscar Vendedor');
		
		$boton=$this->datasis->modbus($modbus);
		
		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre', 
		'cirepre'=>'Rif/Cedula',
		'dire11'=>'Direcci&oacute;n'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','cirepre'=>'rif','dire11'=>'direc'),
		'titulo'  =>'Buscar Cliente');
		
		$boton1 =$this->datasis->modbus($mSCLId);
		
		$edit = new DataEdit("Ingresar Ventas","sfac");

		$edit->pre_process( 'insert','_pre_insert');
		$edit->post_process('insert','_post_insert');

		$edit->back_url = "ventas/ventas";
		
		$edit->vd = new inputField("Vendedor", "vd");
		$edit->vd->size = 10;
		$edit->vd->maxlength=5;
		$edit->vd->readonly=1;
		$edit->vd->rule= "required";
		$edit->vd->append($boton);
		
		$edit->cliente = new inputField("Cliente", "cod_cli");
		$edit->cliente->size = 10;
		$edit->cliente->maxlength=5;
		$edit->cliente->readonly=1;
		$edit->cliente->rule= "required";
		$edit->cliente->append($boton1);
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;
		$edit->nombre->readonly=1;
		$edit->nombre->in='cliente';
		
		$edit->rif = new inputField("Rif/Cedula","rif");
		$edit->rif->size = 15;
		$edit->rif->maxlength=15;
		$edit->rif->readonly=1;
		
		$edit->direccion = new inputField("Direcci&oacute;n", "direc");
		$edit->direccion->size = 50;
		$edit->direccion->readonly=1;
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->rule= "require";
		$edit->fecha->size = 11;
			
		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 15;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		
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
		$data['title']   = '<h1>Facturaci&oacute;n</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function detalles($numero=NULL, $tipo_doc=NULL){
		$this->rapyd->load("datagrid");
		
		$mSQL="SELECT DATE_FORMAT(fecha, '%d/%m/%Y')as fecha,tipo_doc,cod_cli,nombre,iva,totals,totalg,fpago,vd,dire1,direc,rifci,orden,numero,inicial FROM sfac WHERE numero='$numero' AND tipo_doc='$tipo_doc'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$pdata = $query->row_array();
		}

		$grid = new DataGrid();
		$grid->db->select=array('codigoa','desca','cana','mostrado');
		$grid->db->from('sitems');
		$grid->db->where('numa',$numero);
		$grid->db->where('tipoa',$tipo_doc);
		//$grid->order_by("codigo","desc");
		//$grid->per_page = 15;

		$uri=anchor("/compras/agregarfac/mdetalle/$numero/$tipo_doc/modify/<#codigoa#>",'<#codigoa#>');
		
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Cantidad" ,"cantidad"  ,"align='right'");
		$grid->column("Precio"   ,"costo"     ,"align='right'");
		$grid->column("Importe"  ,"importe"   ,"align='right'");
		
		$grid->add("ventas/agregarfac/mdetalle/$numero/$tipo_doc/create");
		$grid->build();
    
		$pdata['items']  = $grid->output;
		$data['content'] = $this->load->view('view_agfactura',$pdata,true); 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Agregar Articulos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function mdetalle($numero='',$tipo_doc=''){
		$this->rapyd->load("dataedit");
		$_POST['numero']=$numero;
		$_POST['tipo_doc']=$tipo_doc;
		
		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'Descripcion',
			'ultimo'=>'Costo'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripcion'),
			'retornar'=>array('codigo'=>'codigoa',
			                  'descrip'=>'desca',
			                  'ultimo'=>'mostrado'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'script'=>array('dbuscar()')
			);

		$boton=$this->datasis->modbus($modbus);
		
		$edit = new DataEdit("Ingresar Art&iacute;culos","sitems");
		$edit->pre_process( 'insert','_pre_minsert');
		$edit->post_process('insert','_post_insert');
		$edit->back_url = "ventas/agregarfac/detalles/$numero/$tipo_doc/";
		
		//if($edit->_status=='modify'){
		//	$codigo=$edit->_dataobject->get('codigo');
		//	$mSQL="SELECT margen1,margen2,margen3,margen4,precio1,precio2,precio3,precio4, ultimo, pond,formcal,existen,tipo,iva FROM sinv WHERE codigo='$codigo'";
		//	//echo $mSQL;
		//	$query = $this->db->query($mSQL);
		//	if ($query->num_rows() > 0){
		//		$row = $query->row_array();
		//		$data = array(
		//		     'imargen1' => $row['margen1'],
		//		     'imargen2' => $row['margen2'],
		//		     'imargen3' => $row['margen3'],
		//		     'imargen4' => $row['margen4'],
		//		     'iprecio1' => $row['precio1'],
		//		     'iprecio2' => $row['precio2'],
		//		     'iprecio3' => $row['precio3'],
		//		     'iprecio4' => $row['precio4'],
		//		     'iprecio4' => $row['precio4'],
		//		     'iultimo'  => $row['ultimo' ],
		//		     'ipond'    => $row['pond'   ],
		//		     'iiva'     => $row['iva'    ],
		//		     'formcal'  => $row['formcal'],
		//		     'iexisten' => $row['existen'],
		//		     'itipo'    => $row['tipo'   ],
		//		   );
		//		   $iva    =$row['iva'];
		//		   $formcal=$row['formcal'];
		//		   $ultimo =$row['ultimo' ];
		//		   $pond   =$row['pond'];
		//		   $cana=$edit->_dataobject->get('cantidad');
		//		   $precio=$edit->_dataobject->get('costo');
		//		   $costo= ($formcal=='P') ? round(($row['pond']*$row['existen']+$precio*$cana)/($row['existen']+$cana) ,2) : $precio;
		//	}
		//}else{
		//	$data = array(
    //	     'imargen1' => '',
    //	     'imargen2' => '',
    //	     'imargen3' => '',
    //	     'imargen4' => '',
    //	     'iprecio1' => '',
    //	     'iprecio2' => '',
    //	     'iprecio3' => '',
    //	     'iprecio4' => '',
    //	     'iprecio4' => '',
    //	     'iultimo'  => '',
    //	     'ipond'    => '',
    //	     'iiva'     => '',
    //	     'formcal'  => '',
    //	     'iexisten' => '',
    //	     'itipo'    => '',
    //	   );
		//}
    //
		//$mp=form_hidden($data);
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;
		$edit->fecha->rule= "required";
		
    $edit->vende = new  dropdownField ("Vendedor", "vd");
		$edit->vende->options("SELECT vendedor, CONCAT(vendedor,' ',nombre) nombre FROM vend ORDER BY vendedor");  
		$edit->vende->size = 5;
		$edit->vende->rule= "required";		
		
		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		
		$edit->tipo = new dropdownField("Tipo", "tipo_doc");  
		$edit->tipo->option("D","D");
	  $edit->tipo->option("F","F");
	  $edit->tipo->option("X","X");
	  $edit->tipo->style='width:60px';
		
    $edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 55;
		$edit->nombre->maxlength=40;
		$edit->nombre->rule= "required";  
		
    $edit->vendedor = new inputField("Vendedor", "vendedor");
		$edit->vendedor->size = 10;        
		$edit->vendedor->maxlength=5;
		$edit->vendedor->rule= "required";
	
		$edit->iva  = new inputField("IVA", "iva");
		$edit->iva->size = 20;
		$edit->iva->css_class='inputnum';
		
		$edit->subtotal  = new inputField("Sub.Total", "totals");
		$edit->subtotal->size = 20;
		$edit->subtotal->css_class='inputnum';
		
		$edit->total  = new inputField("Total", "totalg");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';

		$edit->inicial  = new inputField("Inicial", "inicial"); 
		$edit->inicial->size = 20;                           
		$edit->inicial->css_class='inputnum';  
		 
		$edit->orden  = new inputField("Orden", "orden");           
		$edit->orden->size = 20;                                                      
    $edit->orden->css_class='inputnum';   
    
    $edit->formapago  = new dropdownField("Forma de Pago", "referen");                           
		$edit->formapago->option("C","C");  
		$edit->formapago->option("E","E");
		$edit->formapago->option("M","M");
	  $edit->formapago->size = 20;  
	  $edit->formapago->style='width:50px;';                        
               		
		$edit->cliente = new inputField("Cliente"  , "cod_cli");
		$edit->cliente->size = 10;        
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton);  
		$edit->cliente->rule= "required";
		
		$edit->rifci   = new inputField("RIF/CI","rifci");
		$edit->rifci->size = 20;        
		$edit->rifci->rule= "required";
		
		$edit->direc = new inputField("Direcci&oacute;n","direc");
		$edit->direc->size = 55;  
		
		$edit->dire1 = new inputField(" ","dire1");
		$edit->dire1->size = 

		//$edit->cpre = new containerField("cpre",'<a href="javascript:pajuste()">Modificar M&aacute;genes</a>, <a href="javascript:majuste()">Modificar Precios</a>');  		
    //
		//for($i=1;$i<=4;$i++){
		//	$obj="margen$i";
		//	$data = array(
    //     'name'      => $obj,
    //     'id'        => $obj,
    //     'maxlength' => '6',
    //     'size'      => '6',
    //     'class'     => 'inputnum');
    //  
    //  if($edit->_status=='modify'){
    //  	$base =$edit->_dataobject->get('precio'.$i);
    //  	$data['value']=round(100-($costo*100/$base),2);
    //  }
		//	if(isset($_POST[$obj]))$data['value']=$_POST[$obj];
		//	$edit->$obj = new freeField("Ajuste $i",$obj,'Costo + '.form_input($data).'% = ');
		//	
		//	$obj="base$i";
		//	$edit->$obj = new inputField("Base $i", "base$i");
		//	$edit->$obj->size = 15;
		//	$edit->$obj->db_name = "precio$i";
		//	$edit->$obj->maxlength=20;
		//	$edit->$obj->rule= "required";
		//	$edit->$obj->css_class='inputnum';
		//	$edit->$obj->in="margen$i";
		//	
		//	$obj="precio$i";
		//	$data = array(
    //  	'name'      => $obj,
    //  	'id'        => $obj,
    //  	'maxlength' => '15',
    //  	'size'      => '15',
    //  	'class'     => 'inputnum');
		//	if($edit->_status=='modify'){
		//		$base=$edit->_dataobject->get('precio'.$i);
		//		$iva =$edit->_dataobject->get('iva');
		//		$data['value']=round($base*(1+($iva/100)),2);
		//	}
    //  if(isset($_POST[$obj]))$data['value']=$_POST[$obj];
		//	$edit->$obj = new freeField("Precio $i",$obj,'+ IVA = '.form_input($data));
		//	$edit->$obj->in="margen$i";
		//}
		//
		//$edit->container = new containerField("mp",$mp);  

		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['script']  ='<script type="text/javascript">
		$(function() {
			$(".inputnum").numeric(".");
			$("#costo").floatnumber(".",2);
			
			$("#costo,#cantidad").keyup(function () {
    	  var precio  =parseFloat($("#mostrado").val());
				var cantidad=parseFloat($("#cana").val());
				n = precio*cantidad;
        s = n.toFixed(2);
        $("#monto").val(s); 
    	});
		})
		
		function dbuscar(){
			tipo=$("#itipo").val();
			if(tipo[0]=="L")
				$("#tr_flote").show();
			majuste();
		}
		</script>';
		$data['content'] = $edit->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Facturaci&oacute;n</h1>';
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
		$numero=$this->input->post('numero');
		$tipo_doc=$this->input->post('tipo_doc');
		redirect("/ventas/agregarfac/detalles/$numero/$tipo_doc");
	}

	function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $transac=$this->db->insert_id();
    
		$sql    = 'INSERT INTO nscst (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $numero =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    $tipo_doc =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    $_POST['numero']=$numero;
    $_POST['tipo_doc']=$tipo_doc;
    
    $do->db->set('hora', 'CURRENT_TIME()', FALSE);
		$do->db->set('estampa', 'CURDATE()', FALSE);
    $do->set('numero',$numero);
    $do->set('tipo_doc',$tipo_doc);
		$do->set('transac', $transac);
		$do->set('usuario', $this->session->userdata('usuario'));
	}

	function instalar(){
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	}
}
?>