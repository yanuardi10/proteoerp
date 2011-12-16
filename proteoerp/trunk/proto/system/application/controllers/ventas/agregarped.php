<?php
class agregarped extends Controller {
	
	function agregarped(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
	}
	
	function index() {	
		redirect('ventas/agregarped/encab/create');
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
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','rifci'=>'rif','dire11'=>'direc'),
		'titulo'  =>'Buscar Cliente');
		$boton1 =$this->datasis->modbus($mSCLId);
		
		$edit = new DataEdit("Ingresar Pedido","pfac");
		$edit->pre_process( 'insert','_pre_insert');
		$edit->post_process('insert','_post_insert');

		$edit->back_url = "ventas/pfac";
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->rule= "require";
		$edit->fecha->size = 11;

		//$edit->presup = new inputField("Presupuesto","presup");
		//$edit->presup->size = 15;
		//$edit->presup->maxlength=8;
		
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
		$edit->rif->size      =15;
		$edit->rif->maxlength =15;
		$edit->rif->readonly  =1;
		
		$edit->direccion = new inputField("Direcci&oacute;n", "direc");
		$edit->direccion->size = 50;
		$edit->direccion->readonly=1;
				  
		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output; 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Pedido de Clientes</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function detalles($numero=NULL){
		$this->rapyd->load("datagrid");
		
		$mSQL="SELECT numero,DATE_FORMAT(fecha, '%d/%m/%Y')as fecha,vd,cod_cli,rifci,nombre,direc,dire1,presup, anticipo,iva,totals,referen,totalg,DATE_FORMAT(vence, '%d/%m/%Y')as vence  FROM  pfac WHERE numero='$numero'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$pdata = $query->row_array();
		}

		$grid = new DataGrid();
		$grid->db->select=array('codigoa','desca','cana','preca','tota');
		$grid->db->from('itpfac');
		$grid->db->where('numa',$numero);
		$grid->order_by("codigoa","desc");
		//$grid->per_page = 15;

		$uri=anchor("/ventas/agregarnd/mdetalle/<#id#>/modify/",'<#codigoa#>');
		
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","desca");
		$grid->column("Cantidad" ,"cana"    ,"align='right'");
		$grid->column("Precio"   ,"preca"   ,"align='right'");
		$grid->column("Importe"  ,"tota"    ,"align='right'");
		
		$grid->add("ventas/agregarped/mdetalle/$numero/create");
		$grid->build();
    
		$pdata['items']  = $grid->output;
		$data['content'] = $this->load->view('view_agpedidos', $pdata,true); 
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
			'codigo' =>'C&oacute;digo',
			'descrip'=>'Descripcion',
			'precio1'=>'Precio1'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripcion'),
			'retornar'=>array('codigo'=>'codigoa',
			                  'descrip'=>'desca',
			                  'precio1'=>'mostrado'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'script'=>array('dbuscar()')
			);

		$boton=$this->datasis->modbus($modbus);
		
		$edit = new DataEdit("Ingresar Art&iacute;culos","itpfac");
		//$edit->pre_process( 'insert','_pre_minsert');
		$edit->post_process('insert','_post_insert');
		$edit->back_url = "ventas/agregarped/detalles/$numero";
				
		$edit->numero = new inputField("Numero","numa");
		$edit->numero->size = 16;
		$edit->numero->maxlength=15;
		$edit->numero->readonly=1;
		$edit->numero->rule= "required";
		$edit->numero->insertValue=$numero;
	
		$edit->codigoa = new inputField("Codigo", "codigoa");
		$edit->codigoa->size = 16;
		$edit->codigoa->maxlength=15;
		$edit->codigoa->readonly=1;
		$edit->codigoa->rule= "required";
		$edit->codigoa->append($boton);
		
		$edit->desca = new inputField("Decripcion", "desca");
		$edit->desca->maxlength=40;
		$edit->desca->size = 41;
		$edit->desca->rule= "required";
		$edit->desca->readonly=1;
		$edit->desca->in='codigoa';
		
		$edit->cana = new inputField("Cantidad", "cana");
		$edit->cana->size = 10;
		$edit->cana->insertValue='0';
		$edit->cana->maxlength=15;
		$edit->cana->rule= "required|callback_ccana";
		$edit->cana->css_class='inputnum';

		$edit->mostrado = new inputField("Precio", "mostrado");
		$edit->mostrado->size = 15;
		$edit->mostrado->maxlength=20;
		$edit->mostrado->rule= "required";
		$edit->mostrado->insertValue='0.0';
		$edit->mostrado->css_class='inputnum';

		$edit->tota = new inputField("Importe", "tota");
		$edit->tota->size = 15;
		$edit->tota->maxlength=20;
		$edit->tota->readonly=1;
		$edit->tota->rule= "required";
		$edit->tota->insertValue='0.0';
		$edit->tota->css_class='inputnum';

		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['script']  ='<script type="text/javascript">
		$(function() {
			$(".inputnum").numeric(".");
			$("#mostrado").floatnumber(".",2);
			
			$("#mostrado,#cana").keyup(function () {
    	  var precio  =parseFloat($("#mostrado").val());
				var cantidad=parseFloat($("#cana").val());
				n = precio*cantidad;
        s = n.toFixed(2);
        $("#tota").val(s); 
    	});
		})

		function dbuscar(){
			return 0;
		}
		</script>';
		$data['content'] = $edit->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Pedido de Clientes</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function ccana($cana){
		if($cana>0) return TRUE;
		$this->validation->set_message('ccana', "Debe ingresar una cantidad positiva");
		return FALSE;
			
	}

	function _pre_minsert($do){
		$numero=$this->uri->segment(4);
    $codigo  =$do->get('codigoa') ;
    $mostrado=$do->get('mostrado');
    $cana=$do->get('cana');
    
    $fila=$this->datasis->damerow("SELECT iva FROM sinv WHERE codigo='$codigo'");
    $preca=100*$mostrado/(100+$fila['iva']);
    
    $do->set('numero', $numero);
    $do->set('preca' , $preca);
    $do->set('pvp'   , $preca);
    $do->set('tota'  , $preca*$cana);
    $do->set('iva'   , $fila['iva']);
    $do->set('usuario', $this->session->userdata('usuario'));
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
		redirect("/ventas/agregarped/detalles/$numero");
	}

	function _pre_insert($do){
		$transac=$this->datasis->prox_numero('ntransa');
		$numero =str_pad($this->datasis->prox_numero('npfac'),8, "0", STR_PAD_LEFT);
		$_POST['numero']=$numero;

		$do->db->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->db->set('estampa', 'CURDATE()', FALSE);
		$do->set('numero', $numero);
		$do->set('transac', $transac);
		$do->set('usuario', $this->session->userdata('usuario'));
	}

	function existe($codigo){
		$numero=$this->input->post('numa');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM itpfac WHERE codigo='$codigo'AND numa='$numero'");
		if ($chek > 0){
			$this->validation->set_message('existe',"El Articulo ya existe para este pedido");
			 return FALSE;
		}else {
  		return TRUE;
		}	
	}

	function instalar(){
		$mSQL='ALTER TABLE itpfac ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
	}
}
?>