<?php
class agregaroi extends Controller {
	
	function agregaroi(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
	}
	
	function index() {	
		redirect('ventas/agregaroi/encab/create');
	}

	function encab(){
		$this->rapyd->load("dataedit");
		
		$modbus=array(
		'tabla'   =>'vend',
		'columnas'=>array(
		'vendedor' =>'C&oacute;digo Vendedor',
		'nombre'=>'Nombre'),
		'filtro'  =>array('vendedor'=>'C&oacute;digo Vendedor','nombre'=>'Nombre'),
		'retornar'=>array('vendedor'=>'vd','nombre'=>'nombre'),
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
		
		$edit = new DataEdit("Ingresar Otros Ingresos","otin");
		//$edit->pre_process( 'insert','_pre_insert');
		$edit->post_process('insert','_post_insert');

		$edit->back_url = "ventas/otin";

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
		$edit->numero->rule= "required|callback_repetido";
						
		$edit->tipo = new dropdownField("Tipo", "tipo_doc");  
		$edit->tipo->option("FC","Factura Credito");  
		$edit->tipo->option("NC","Nota Credito");
		$edit->tipo->option("NE","Nota Entrega");
		$edit->tipo->rule = "required";  
	  $edit->tipo->size = 20;  
	  $edit->tipo->style='width:150px;';
	  
	  $edit->orden = new inputField("Orden", "orden");
		$edit->orden->size = 15;
		$edit->orden->rule= "required";
		$edit->orden->mode="autohide";
		$edit->orden->maxlength=8;				

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
			  
		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output; 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Otros Ingresos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function detalles($numero=NULL){
		$this->rapyd->load("datagrid");
		
		$mSQL="SELECT tipo_doc, numero,DATE_FORMAT(fecha, '%d/%m/%Y')as fecha, cod_cli, rifci, nombre, direc, dire1,totals, iva, totalg,DATE_FORMAT(vence, '%d/%m/%Y')as vence, observa1, observa2,orden FROM otin WHERE numero='$numero'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$pdata = $query->row_array();
		}

		$grid = new DataGrid();
		$grid->db->select=array('codigo','descrip','cantidad','precio','importe');
		$grid->db->from('itotin');
		$grid->db->where('numero',$numero);
		$grid->order_by("codigo","desc");
		//$grid->per_page = 15;

		$uri=anchor("/ventas/agregaroi/mdetalle/$numero/modify/<#id#>",'<#codigo#>');
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Cantidad"   ,"cantidad"  ,"align='right'");
		$grid->column("Precio"     ,"precio"     ,"align='right'");
		$grid->column("Importe"    ,"importe"   ,"align='right'");
		
		$grid->add("ventas/agregaroi/mdetalle/$numero/create");
		$grid->build();
    
		$pdata['items']  = $grid->output;
		$data['content'] = $this->load->view('view_agotrosingresos', $pdata,true); 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Agregar Art&iacute;culos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function mdetalle($numero){
		$this->rapyd->load("dataedit");
		$_POST['numero']=$numero;
		
			$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo'  =>'C&oacute;digo',
			'descrip' =>'Descripcion',
			'ultimo'  =>'Costo'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripcion'),
			'retornar'=>array('codigo' =>'codigo',
			                  'descrip'=>'descrip',
			                  'ultimo' =>'precio'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'script'=>array('dbuscar()')
			);
    
		$boton=$this->datasis->modbus($modbus);
		
		$edit = new DataEdit("Ingresar Art&iacute;culos","itotin");
		$edit->pre_process( 'insert','_pre_minsert');
		$edit->post_process('insert','_post_insert');
		$edit->back_url = "ventas/agregaroi/detalles/$numero";
				
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
		
		$edit->precio = new inputField("Precio", "precio");
		$edit->precio->size = 15;
		$edit->precio->maxlength=20;
		$edit->precio->rule= "required";
		$edit->precio->insertValue='0.0';
		$edit->precio->css_class='inputnum';

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
		
		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['script']  ='<script type="text/javascript">
		$(function() {
			$(".inputnum").numeric(".");
			$("#precio").floatnumber(".",2);
			
			$("#precio,#cantidad").keyup(function () {
    	  var precio  =parseFloat($("#precio").val());
				var cantidad=parseFloat($("#cantidad").val());
				n = precio*cantidad;
        s = n.toFixed(2);
        $("#importe").val(s); 
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
		$data['title']   = '<h1>Otros Ingresos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function ccana($cana){
		if($cana>0) return TRUE;
		$this->validation->set_message('ccana', "Debe ingresar una cantidad positiva");
		return FALSE;
			
	}

	function _pre_del($do){
		$codigo=$do->get('comprob');
		$chek =   $this->datasis->dameval("SELECT COUNT(*) FROM cpla WHERE codigo LIKE '$codigo.%'");
		$chek +=  $this->datasis->dameval("SELECT COUNT(*) FROM itcasi WHERE cuenta='$codigo'");

		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Plan de Cuenta tiene derivados o movimientos';
			return False;
		}
		return True;
	}
	
	function _post_insert($do){
		$numero=$this->input->post('numero');
		$tipo_doc=$this->input->post('tipo_doc');
		redirect("/ventas/agregaroi/detalles/$numero/$tipo_doc");
	}
	function _pre_minsert($do){
		$numero=$this->uri->segment(4);
		$tipo_doc=$this->uri->segment(5);
    $do->set('numero', $numero);
    $do->set('tipo_doc', $tipo_doc);
	}	
	function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $transac=$this->db->insert_id();
    
		$sql    = 'INSERT INTO nscst (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
    $numero =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    $_POST['numero']=$numero;
    
    $do->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->db->set('estampa', 'CURDATE()', FALSE);
    $do->set('numero', $numero);
		$do->set('transac', $transac);
		$do->set('usuario', $this->session->userdata('usuario'));
	}

	function instalar(){
		$mSQL='ALTER TABLE itscst ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	}
	function repetido($numero){
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM otin WHERE numero='$numero'");
		if ($chek > 0){
			$this->validation->set_message('repetido',"Este ingreso ya existe");
			 return FALSE;
		}else {
  		return TRUE;
		}	
	}	
}
?>