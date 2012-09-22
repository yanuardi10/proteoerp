<?php
class agregarnd extends Controller {
	
	function agregarnd(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
	}
	
	function index() {	
		redirect('ventas/agregarnd/encab/create');
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
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre'),
		'titulo'  =>'Buscar Cliente');
		
		$boton1 =$this->datasis->modbus($mSCLId);
		
		$edit = new DataEdit("Ingresar Nota de Despacho","snot");

		//$edit->pre_process( 'insert','_pre_insert');
		$edit->post_process('insert','_post_insert');

		$edit->back_url = "ventas/ventas";
		
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
				
		$edit->fechaf = new DateonlyField("Fecha de Factura", "fechafa","d/m/Y");
		$edit->fechaf->insertValue = date("Y-m-d");
		$edit->fechaf->mode="autohide";
		$edit->fechaf->rule= "require";
		$edit->fechaf->size = 11;
		
		$edit->factura = new inputField("Factura","factura");
		$edit->factura->size = 15;
		$edit->factura->rule= "required";
		$edit->factura->mode="autohide";
		$edit->factura->maxlength=8;
		
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
		  
		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output; 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Nota de Despacho</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function detalles($numero=NULL){
		$this->rapyd->load("datagrid");
		
		$mSQL="SELECT DATE_FORMAT(fecha, '%d/%m/%Y')as fecha,factura,numero,cod_cli,nombre,DATE_FORMAT(fechafa, '%d/%m/%Y')as fechafa FROM snot WHERE numero='$numero'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$pdata = $query->row_array();
		}

		$grid = new DataGrid();
		$grid->db->select=array('codigo','descrip','cant','saldo','entrega');
		$grid->db->from('itsnot');
		$grid->db->where('numero',$numero);
		$grid->order_by("codigo","desc");
		//$grid->per_page = 15;

		$uri=anchor("/ventas/agregarnd/mdetalle/$numero/modify/<#id#>",'<#codigo#>');
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Cantidad","cant"  ,"align='right'");
		$grid->column("Precio"  ,"saldo"     ,"align='right'");
		$grid->column("Importe" ,"entrega"  ,"align='right'");
		
		$grid->add("ventas/agregarnd/mdetalle/$numero/create");
		$grid->build();
    
		$pdata['items']  = $grid->output;
		$data['content'] = $this->load->view('view_agnotadespacho', $pdata,true); 
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
			                  'ultimo' =>'saldo'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'script'=>array('dbuscar()')
			);
    
		$boton=$this->datasis->modbus($modbus);
		
		$edit = new DataEdit("Ingresar Art&iacute;culos","itsnot");
		$edit->pre_process( 'insert','_pre_minsert');
		$edit->post_process('insert','_post_insert');
		$edit->back_url = "ventas/agregarnd/detalles/$numero";
		
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
		
		$edit->cantidad = new inputField("Cantidad", "cant");
		$edit->cantidad->size = 10;
		$edit->cantidad->insertValue='0';
		$edit->cantidad->maxlength=15;
		$edit->cantidad->rule= "required|callback_ccana";
		$edit->cantidad->css_class='inputnum';

		$edit->saldo = new inputField("Saldo","saldo");
		$edit->saldo->size = 15;
		$edit->saldo->maxlength=20;
		$edit->saldo->rule= "required";
		$edit->saldo->insertValue='0.0';
		$edit->saldo->css_class='inputnum';
		
		$edit->entrega = new inputField("Entrega","entrega");
		$edit->entrega->size = 15;
		$edit->entrega->maxlength=20;
		$edit->entrega->rule= "required";
		$edit->entrega->insertValue='0.0';
		$edit->entrega->css_class='inputnum';
		
		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['script']  ='<script type="text/javascript">
		$(function() {
			$(".inputnum").numeric(".");
			$("#saldo").floatnumber(".",2);
			
			$("#saldo,#cant").keyup(function () {
    	  var precio  =parseFloat($("#saldo").val());
				var cantidad=parseFloat($("#cant").val());
				n = precio*cantidad;
        s = n.toFixed(2);
        $("#entrega").val(s); 
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
		$data['title']   = '<h1>Nota de Despacho</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function ccana($cana){
		if($cana>0) return TRUE;
		$this->validation->set_message('ccana', "Debe ingresar una cantidad positiva");
		return FALSE;
			
	}

		function _pre_minsert($do){
		$numero=$this->uri->segment(4);
    $do->set('numero', $numero);

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
	
	function repetido($numero){
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM snot WHERE numero='$numero'");
		if ($check > 0){
			$this->validation->set_message('repetido',"La Nota de Despacho ya existe");
			 return FALSE;
		}else {
  		return TRUE;
		}	
	}	
	
	function _post_insert($do){
		$numero=$this->input->post('numero');
		redirect("/ventas/agregarnd/detalles/$numero");
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
}
?>