<?php
class agregarne extends Controller {
	
	function agregarne(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
	}
	
	function index() {	
		redirect('ventas/agregarne/encab/create');
	}

	function encab(){
		$this->rapyd->load("dataedit");
		
		$modbus=array(
		'tabla'   =>'vend',
		'columnas'=>array(
		'vendedor' =>'C&oacute;digo Vendedor',
		'nombre'=>'Nombre'),
		'filtro'  =>array('vendedor'=>'C&oacute;digo Vendedor','nombre'=>'Nombre'),
		'retornar'=>array('vendedor'=>'vende'),
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
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','cirepre'=>'rif','dire11'=>'dir_cli'),
		'titulo'  =>'Buscar Cliente');
		
		$boton1 =$this->datasis->modbus($mSCLId);
		
		$edit = new DataEdit("Ingresar Nota de Entrega","snte");

		//$edit->pre_process( 'insert','_pre_insert');
		$edit->post_process('insert','_post_insert');

		$edit->back_url = "ventas/snte";
		
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
		
		$edit->vendedor = new inputField("Vendedor", "vende");
		$edit->vendedor->size = 10;
		$edit->vendedor->maxlength=5;
		$edit->vendedor->rule= "required";
		$edit->vendedor->append($boton);
		
		$edit->factura = new inputField("Factura","factura");
		$edit->factura->size = 15;
		$edit->factura->rule= "required";
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
		
		$edit->rif = new inputField("Rif/Cedula","rif");
		$edit->rif->size = 15;
		$edit->rif->maxlength=15;
		$edit->rif->readonly=1;
		
		$edit->direccion = new inputField("Direcci&oacute;n", "dir_cli");
		$edit->direccion->size = 50;
		$edit->direccion->readonly=1;
		
		$edit->almacen = new dropdownField("Almacen","almacen");
		$edit->almacen->size = 15;
		$edit->almacen->maxlength=8;
		$edit->almacen->option("","Seleccionar");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N'");
		$edit->almacen->rule= "required";
		$edit->almacen->style='width:150px;';
				  
		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output; 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Nota de Entrega</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function detalles($numero=NULL){
		$this->rapyd->load("datagrid");
		
		$mSQL="SELECT numero,DATE_FORMAT(fecha, '%d/%m/%Y')as fecha,vende,factura,cod_cli,almacen,nombre,dir_cli,dir_cl1,orden,stotal,impuesto,observa,gtotal FROM snte WHERE numero='$numero'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$pdata = $query->row_array();
		}

		$grid = new DataGrid();
		$grid->db->select=array('codigo','desca','cana','precio','importe');
		$grid->db->from('itsnte');
		$grid->db->where('numero',$numero);
		$grid->order_by("codigo","desc");

		$uri=anchor("/ventas/agregarne/mdetalle/$numero/<#codigo#>/modify/",'<#codigo#>');
		
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","desca");
		$grid->column("Cantidad"   ,"cana"  ,"align='right'");
		$grid->column("Precio"     ,"precio"     ,"align='right'");
		$grid->column("Total"    	 ,"importe"   ,"align='right'");
		
		$grid->add("ventas/agregarne/mdetalle/$numero/create");
		$grid->build();
    
		$pdata['items']  = $grid->output;
		$data['content'] = $this->load->view('view_agnotaentrega',$pdata,true); 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1> Agregar Art&iacute;culos</h1>';
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
			                  'descrip'=>'desca',
			                  'ultimo' =>'precio'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'script'=>array('dbuscar()')
			);
    
		$boton=$this->datasis->modbus($modbus);
		
		$edit = new DataEdit("Ingresar Art&iacute;culos","itsnte");
		//$edit->pre_process('insert','_pre_minsert');
		$edit->post_process('insert','_post_insert');
		$edit->back_url = "ventas/agregarne/detalles/$numero";
				
		$edit->numero = new inputField("Numero","numero");
		$edit->numero->size = 16;
		$edit->numero->maxlength=10;
		$edit->numero->rule= "required";
		$edit->numero->insertValue=$numero;
		
		$edit->codigo = new inputField("C&oacute;digo","codigo");
		$edit->codigo->size = 16;
		$edit->codigo->maxlength=10;
		$edit->codigo->rule= "required";
		//$edit->codigo->rule= "required|callback_existe";
		$edit->codigo->append($boton);
				
		$edit->descrip = new inputField("Decripci&oacute;n","desca");
		$edit->descrip->maxlength=40;
		$edit->descrip->size = 41;
		$edit->descrip->rule= "required";
		$edit->descrip->mode="autohide";
		$edit->descrip->readonly=1;
		$edit->descrip->in='codigo';
		
		$edit->cantidad = new inputField("Cantidad", "cana");
		$edit->cantidad->size =5;
		$edit->cantidad->insertValue='0';
		$edit->cantidad->maxlength=5;
		$edit->cantidad->rule="required|callback_ccana";
		$edit->cantidad->css_class='inputnum';
		
		$edit->costo = new inputField("Precio","precio");
		$edit->costo->size = 15;
		$edit->costo->maxlength=20;
		$edit->costo->rule= "required";
		$edit->costo->insertValue='0.0';
		$edit->costo->css_class='inputnum'; 
		
		$edit->importe = new inputField("Total","importe");
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
			
			$("#precio,#cana").keyup(function () {
    	  var precio  =parseFloat($("#precio").val());
				var cantidad=parseFloat($("#cana").val());
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
		$data['title']   = '<h1>Nota de Entrega</h1>';
		$this->load->view('view_ventanas', $data);
	} 
	  
	function ccana($cana){
		if($cana>0) return TRUE;
		$this->validation->set_message('ccana', "Debe ingresar una cantidad positiva");
		return FALSE;
			
	}
	
	//function _pre_minsert($do){
	//	$numero=$this->uri->segment(4);
  //  $do->set('numero', $numero);
	//}	
	
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
		redirect("/ventas/agregarne/detalles/$numero");
	}
	
	function repetido($numero){
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM snte WHERE numero='$numero'");
		if ($check > 0){
			$this->validation->set_message('repetido',"La Nota de Entrega ya existe");
			 return FALSE;
		}else {
  		return TRUE;
		}	
	}	
	function existe($codigo){
		$numero=$this->input->post('numero');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM itsnte WHERE codigo='$codigo'AND numero='$numero'");
		if ($check > 0){
			$this->validation->set_message('existe',"El Art&iacute;culo ya existe para esta Nota de Entrega ");
			 return FALSE;
		}else {
  		return TRUE;
		}	
	}	
}
?>