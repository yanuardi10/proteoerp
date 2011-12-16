<?php
class agregarrec extends Controller {
	
	function agregarrec(){
		parent::Controller();
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(201,1);
	}
	
	function index() {	
		redirect('hospitalidad/agregarrec/encab/create');
	}

	function encab(){
		$this->rapyd->load("dataedit");
			
		$edit = new DataEdit("Ingresar Receta","rece");
		
			$menu=array(
			'tabla'   =>'menu',
			'columnas'=>array(
			'codigo'  =>'C&oacute;digo',
			'descri1' =>'Descripciçon', 
			'precio'  =>'Precio'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','nombre'=>'Nombre'),
			'retornar'=>array('codigo'=>'codigo','descri1'=>'descri1','precio'=>'precio'),
			'titulo'  =>'Buscar Cliente');
		
		$boton =$this->datasis->modbus($menu);

		$edit->pre_process( 'insert','_pre_insert');
		$edit->post_process('insert','_post_insert');

		$edit->back_url = "hospitalidad/recetas";
					
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->rule= "require";
		$edit->fecha->size = 11;
			
		$edit->codigo = new inputField("C&oacute;digo","codigo");
		$edit->codigo->size = 10;
		$edit->codigo->rule= "required|callback_repetido";
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength=10;
		$edit->codigo->append($boton);
		
		$edit->descri1 = new inputField("Descripci&oacute;n","descri1");
		$edit->descri1->size =50;
		$edit->descri1->rule= "required";
		$edit->descri1->mode="autohide";
		$edit->descri1->maxlength=40;
		
		$edit->precio = new inputField("Precio","precio");
		$edit->precio->size =20;
		$edit->precio->rule= "required";
		$edit->precio->mode="autohide";
		$edit->precio->maxlength=8;
		  
		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output; 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Recetas</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function detalles($codigo=NULL){
		$this->rapyd->load("datagrid");
		
		$mSQL="SELECT DATE_FORMAT(fecha, '%d/%m/%Y')as fecha,codigo,descri1,precio,costo,rela FROM rece WHERE codigo='$codigo'";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$pdata = $query->row_array();
		}

		$grid = new DataGrid();
		$grid->db->select=array('codigo','descrip','cantidad','costo','monto','rendi');
		$grid->db->from('itrece');
		$grid->db->where('menu',$codigo);
		$grid->order_by("codigo","asc");
		//$grid->per_page = 15;

		$uri=anchor("/hospitalidad/agregarrec/mdetalle/$codigo/<#codigo#>/modify",'<#codigo#>');
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Cantidad"     ,"cantidad"  ,"align='right'");
		$grid->column("Rendimiento"  ,"rendi"     ,"align='center'");
		$grid->column("Costo"        ,"costo"     ,"align='right'");
		$grid->column("Monto"        ,"monto"     ,"align='right'");
		
		$grid->add("hospitalidad/agregarrec/mdetalle/$codigo/create");
		$grid->build();
    
		$pdata['items']  = $grid->output;
		$data['content'] = $this->load->view('view_agrecetas', $pdata,true); 
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = '<h1>Agregar Receta</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function mdetalle($codigo){
		$this->rapyd->load("dataedit");
		//$_POST['codigo']=$codigo;
		//echo $codigo;
		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
			'codigo' =>'C&oacute;digo',
			'descrip'=>'Descripcion',
			'ultimo'=>'Costo'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripcion'),
			'retornar'=>array('codigo'=>'codigo',
			                  'descrip'=>'descrip',
			                  'ultimo'=>'costo'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'script'=>array('dbuscar()')
			);

		$boton=$this->datasis->modbus($modbus);
		
		$edit = new DataEdit("Agregar Ingrediente","itrece");
		//$edit->pre_process( 'insert','_pre_minsert');
		$edit->post_process('insert','_post_insert2');
		$edit->back_url = "hospitalidad/agregarrec/detalles/$codigo";
				
		$edit->menu = new inputField("Menu","menu");
		$edit->menu->size = 16;
		$edit->menu->maxlength=10;
		$edit->menu->rule= "required";
		$edit->menu->insertValue=$codigo;
		//$edit->menu->type="hidden";
		
		$edit->codigo = new inputField("C&oacute;digo","codigo");
		$edit->codigo->size = 16;
		$edit->codigo->maxlength=10;
		$edit->codigo->rule= "required";
		$edit->codigo->append($boton);
		//$edit->codigo->rule= "required|callback_existe";
				
		$edit->descrip = new inputField("Decripci&oacute;n","descrip");
		$edit->descrip->maxlength=40;
		$edit->descrip->size = 41;
		$edit->descrip->rule= "required";
		$edit->descrip->mode="autohide";
		$edit->descrip->readonly=1;
		$edit->descrip->in='codigo';
		
		$edit->cantidad = new inputField("Cantidad", "cantidad");
		$edit->cantidad->size =5;
		$edit->cantidad->insertValue='0';
		$edit->cantidad->maxlength=5;
		$edit->cantidad->rule="required|callback_ccana";
		$edit->cantidad->css_class='inputnum';
		
		$edit->rendi = new inputField("Rendimiento", "rendi");
		$edit->rendi->size = 15;
		$edit->rendi->maxlength=20;
		$edit->rendi->rule= "required";
		$edit->rendi->insertValue='0';
		$edit->rendi->css_class='inputnum';
		
		$edit->costo = new inputField("Costo", "costo");
		$edit->costo->size = 15;
		$edit->costo->maxlength=15;
		$edit->costo->rule= "required";
		$edit->costo->insertValue='0.0';
		$edit->costo->css_class='inputnum';
		
		$edit->monto = new inputField("Total", "monto");
		$edit->monto->size = 15;
		$edit->monto->maxlength=20;
		$edit->monto->rule= "required";
		$edit->monto->insertValue='0.0';
		$edit->monto->css_class='inputnum';  

		$edit->buttons("save", "undo", "delete", "back");
		$edit->build();

		$data['script']  ='<script type="text/javascript">
		$(function() {
			$(".inputnum").numeric(".");
			$("#costo").floatnumber(".",2);
			
			$("#costo,#cantidad").keyup(function () {
    	  var precio  =parseFloat($("#costo").val());
				var cantidad=parseFloat($("#cantidad").val());
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
		$data['title']   = '<h1>Recetas</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function ccana($cana){
		if($cana>0) return TRUE;
		$this->validation->set_message('ccana', "Debe ingresar una cantidad positiva");
		return FALSE;
			
	}
	//function _pre_minsert($do){
	//	$menu=$this->uri->segment(4);
  //  $do->set('menu', $menu);
	//}

	//function _pre_del($do){
	//	$codigo=$do->get('comprob');
	//	$chek =   $this->datasis->dameval("SELECT COUNT(*) FROM cpla WHERE codigo LIKE '$codigo.%'");
	//	$chek +=  $this->datasis->dameval("SELECT COUNT(*) FROM itcasi WHERE cuenta='$codigo'");
  //
	//	if ($chek > 0){
	//		$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Plan de Cuenta tiene derivados o movimientos';
	//		return False;
	//	}
	//	return True;
	//}
	
	function _post_insert($do){
		$codigo=$this->input->post('codigo');
		redirect("/hospitalidad/agregarrec/detalles/$codigo");
	}
	
	function _post_insert2($do){
		$menu=$this->input->post('menu');
		redirect("/hospitalidad/agregarrec/detalles/$menu");
	}

	function repetido($codigo){
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM rece WHERE codigo='$codigo'");
		if ($chek > 0){
			$this->validation->set_message('repetido',"La Receta ya existe");
			 return FALSE;
		}else {
  		return TRUE;
		}	
	}	
	function existe($codigo){
		$menu=$this->input->post('menu');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM itrece WHERE codigo='$codigo'AND menu='$menu'");
		if ($chek > 0){
			$this->validation->set_message('existe',"El Ingrediente ya existe para esta receta");
			 return FALSE;
		}else {
  		return TRUE;
		}	
	}	
}
?>