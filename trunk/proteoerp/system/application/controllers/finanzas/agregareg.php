<?php
class agregareg extends Controller {
	
	function agregareg(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
	}
	
	function index() {	
		redirect('finanzas/agregareg/encab/create');
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
		
		$edit = new DataEdit("Ingresar Egreso","gser ");
		$edit->pre_process( 'insert','_pre_insert');
		$edit->post_process('insert','_post_insert');

		$edit->back_url = "finanzas/gser";
			
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
		
		$edit->vence = new DateonlyField("Vencimiento","vence","d/m/Y");
		$edit->vence->insertValue = date("Y-m-d");
		$edit->vence->mode="autohide";
		$edit->vence->rule= "require";
		$edit->vence->size = 11;

		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output; 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Egresos</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function detalles($numero=NULL){
		$this->rapyd->load("datagrid");
		
		$mSQL="SELECT DATE_FORMAT(fecha, '%d/%m/%Y')as fecha,numero,proveed,nombre,vence,orden,totpre,totiva,totbruto,reten,totneto,credito,anticipo,codb1,tipo1 FROM gser WHERE numero='$numero'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$pdata = $query->row_array();
		}

		$grid = new DataGrid();
		$grid->db->select=array('codigo','descrip','precio','importe','iva');
		$grid->db->from('gitser');
		$grid->db->where('numero',$numero);
		$grid->order_by("codigo","desc");
		//$grid->per_page = 15;

		$uri=anchor("/finanzas/agregareg/mdetalle/$numero/modify/<#id#>",'<#codigo#>');
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Cantidad","cantidad"  ,"align='right'");
		$grid->column("Impuesto" ,"iva"     ,"align='right'");
		$grid->column("Importe" ,"importe"  ,"align='right'");
		
		$grid->add("finanzas/agregareg/mdetalle/$numero/create");
		$grid->build();
    
		$pdata['items']  = $grid->output;
		$data['content'] = $this->load->view('view_agegresos', $pdata,true); 
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
			                  'ultimo' =>'costo'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'script'=>array('dbuscar()')
			);
    
		$boton=$this->datasis->modbus($modbus);

		$boton=$this->datasis->modbus($modbus);
		
		$edit = new DataEdit("Ingresar Art&iacute;culos","itordc");
		$edit->pre_process( 'insert','_pre_minsert');
		$edit->post_process('insert','_post_insert');
		$edit->back_url = "finanzas/agregareg/detalles/$numero";
		
		//$edit->numero = new inputField("Numero","numero");
		//$edit->numero->size = 16;
		//$edit->numero->maxlength=10;
		//$edit->numero->rule= "required";
		//$edit->numero->insertValue=$numero;	
				
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
		
		$edit->precio = new inputField("Precio","precio");
		$edit->precio->size = 10;
		$edit->precio->insertValue='0';
		$edit->precio->maxlength=15;
		$edit->precio->rule= "required|callback_ccana";
		$edit->precio->css_class='inputnum';

		$edit->iva = new inputField("Iva","iva");
		$edit->iva->size = 15;
		$edit->iva->maxlength=20;
		$edit->iva->rule= "required";
		$edit->iva->insertValue='0.0';
		$edit->iva->css_class='inputnum';
		
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
			
			$("#costo,#cantidad").keyup(function () {
    	  var precio  =parseFloat($("#costo").val());
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
		$data['title']   = '<h1>Orden de finanzas</h1>';
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
		redirect("/finanzas/agregareg/detalles/$numero");
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
		var_dum($this->db->simple_query($mSQL));
	}
}
?>