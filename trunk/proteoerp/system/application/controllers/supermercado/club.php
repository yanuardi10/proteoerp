<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Club extends validaciones {
 
	function Club(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->load->library("menues");
		$this->datasis->modulo_id('121',1);
	}
	function index(){
		redirect("supermercado/club/filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");

		$filter = new DataFilter("Filtro de Club de Compras");
		
		$select=array("cedula","cod_tar","CONCAT_WS(' ',nombres,apellidos) AS nombre","direc1");
		$filter->db->select($select);
		$filter->db->from('club');
			
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=30;
		$filter->nombre->db_name="CONCAT_WS(' ',nombres,apellidos)";

		$filter->cod_tar = new inputField("Tarjeta", "cod_tar");
		$filter->cod_tar->size=30;

		$filter->cedula = new inputField("C&eacute;dula", "cedula");
		$filter->cedula->size=15;

		$filter->buttons("reset","search");
		$filter->build();
		$link = anchor("supermercado/club/dataedit/show/<#cod_tar#>",'<#nombre#>');

		$grid = new DataGrid("Lista de Usuarios");
		$grid->order_by("nombre","asc");
		$grid->per_page = 10;

		$grid->column_orderby("Nombres",$link,"nombres");
		$grid->column("C&eacute;dula","cedula");
		$grid->column("Tarjeta","cod_tar");
		$grid->column("Direcci&oacute;n","direc1");
		
		$grid->add("supermercado/club/dataedit/create");
		$grid->build();

		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Modulo de Club de Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$modbus=array(
			'tabla'   =>'scli',
			'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'contacto'=>'Contacto'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'cod_cli'),
			'titulo'  =>'Buscar Cliente');
		$boton=$this->datasis->modbus($modbus);
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
			$("#banco1").change(function () { acuenta(); }).change();
			$("#banco2").change(function () { acuenta(); }).change();
		});
		
		function acuenta(){
			for(i=1;i<=2;i++){
				vbanco=$("#banco"+i).val();
				if(vbanco.length>0){
					$("#tr_cuenta"+i).show();
				}else{
					$("#cuenta"+i).val("");
					$("#tr_cuenta"+i).hide();
				}
			}		
		}
		';

		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Club de Compras", "club");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->back_url = site_url("supermercado/club/filteredgrid");
		
		$edit->pre_process('delete','_pre_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_insert');	
		$edit->post_process('insert','_post_insert');
		$edit->post_process("update","_post_update");
		$edit->post_process("delete","_post_delete");
	
		$edit->cod_tar = new inputField("C&oacute;digo de Tarjeta", "cod_tar");
		$edit->cod_tar->rule = "required|callback_chexiste";
		$edit->cod_tar->mode = "autohide";
		$edit->cod_tar->size = 15;
		$edit->cod_tar->maxlength = 12;
		
		$edit->fec_nac = new DateonlyField("Fecha de Nacimiento", "fec_nac");
		$edit->fec_nac->size = 11;
		$edit->fec_nac->group = "Datos del Cliente";

		$edit->fec_ing = new DateonlyField("Fecha de Ingreso", "fec_ing");
		$edit->fec_ing->size = 11;
		$edit->fec_ing->group = "Datos del Cliente";
		
		$edit->cedula =  new inputField("C&eacute;dula", "cedula");
		$edit->cedula->rule = "required|callback_chci";
		$edit->cedula->size = 12;
		$edit->cedula->group = "Datos del Cliente";
		
		$edit->nombre = new inputField("Nombres", "nombres");
		$edit->nombre->rule = "strtoupper|required";
		$edit->nombre->size = 45;
		$edit->nombre->maxlength = 25;
		$edit->nombre->group = "Datos del Cliente";

		$edit->apellidos = new inputField("Apellidos", "apellidos");
		$edit->apellidos->rule = "strtoupper|required";
		$edit->apellidos->size = 45;
		$edit->apellidos->maxlength = 25;
		$edit->apellidos->group = "Datos del Cliente";

		$edit->direc1 = new inputField("Direcci&oacute;n", "direc1");
		$edit->direc1->group = "Datos del Cliente";
		$edit->direc1->maxlength = 30;
		$edit->direc1->size = 50;
		
		$edit->direc2 = new inputField("&nbsp;", "direc2");
		$edit->direc2->size = 50;
		$edit->direc2->maxlength = 30;
		$edit->direc2->group = "Datos del Cliente";
		
		$edit->zona = new dropdownField("Zona", "zona");
		$edit->zona->option("","No Asignado");
		$edit->zona->options("SELECT codigo, nombre FROM zona ORDER BY nombre");
		$edit->zona->group = "Datos del Cliente";

		for($i=1;$i<=4;$i++){
			$obj="telefono$i";
			$edit->$obj = new inputField("Tel&eacute;fono $i",$obj);
			$edit->$obj->size = 31;
			$edit->$obj->maxlength = 11;
			$edit->$obj->group = "Datos del Cliente";
		}
		for($i=1;$i<=2;$i++){
			$obj="banco$i";
			$edit->$obj = new dropdownField("Banco $i", $obj);
			$edit->$obj->clause="where";
			$edit->$obj->option("","Ninguno");  
			$edit->$obj->options("SELECT cod_banc,nomb_banc FROM tban ORDER BY nomb_banc"); 
			$edit->$obj->operator="=";
			$edit->$obj->group = "Informaci&oacute;n financiera";
			
			$obj="cuenta$i";
			$edit->$obj = new inputField("Cuenta $i",$obj);
			$edit->$obj->size = 31;
			$edit->$obj->maxlength = 15;
			$edit->$obj->group = "Informaci&oacute;n financiera";
			//$edit->$obj->in="banco$i";
		}

		$edit->tipo = new dropdownField("Precio", 'tipo');
		$edit->tipo->option("1","Precio 1");
		$edit->tipo->option("2","Precio 2");
		$edit->tipo->option("3","Precio 3");
		$edit->tipo->option("4","Precio 4");
		$edit->tipo->style="width: 150px;";
		$edit->tipo->group = "Informaci&oacute;n financiera";

		$edit->status = new dropdownField("Status", 'status');
		$edit->status->option("","Ninguno");
		$edit->status->rule = "required";
		$edit->status->option("C","Conformar");
		$edit->status->option("N","No conformar");
		$edit->status->option("R","Retirado");
		$edit->status->group="Informaci&oacute;n financiera";
		$edit->status->style="width: 150px;";

		$edit->ing_mes =  new inputField("Ingreso Mensual", "ing_mes");
		$edit->ing_mes->css_class='inputnum';
		$edit->ing_mes->rule='numeric';
		$edit->ing_mes->size = 12;
		$edit->ing_mes->maxlength = 11;
		$edit->ing_mes->group = "Informaci&oacute;n financiera";

		$edit->empresa =  new inputField("Empresa", "empresa");
		$edit->empresa->size = 35;
		$edit->empresa->maxlength = 30;
		$edit->empresa->group = "Informaci&oacute;n financiera";
		
		$edit->cod_cli = new inputField("Enlace finaciero", "cod_cli");
		$edit->cod_cli->append($boton);
		$edit->cod_cli->group = "Informaci&oacute;n financiera";

		//$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->buttons("modify","save","undo","back");
		$edit->build();
		
		$data['content'] = $edit->output;
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   ='<h1>Modulo de Club de Compras</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function _pre_delete($do){
		return FALSE;
	}
	function _pre_insert($do){
		$do->set('modifi', date('Ymd'));
	}
	function _post_insert($do){
		$codigo=$do->get('cedula');
		$cod_tar=$do->get('cod_tar');
		logusu('club',"CLUB DE COMPRAS $cod_tar $codigo CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('cedula');
		$cod_tar=$do->get('cod_tar');
		logusu('club',"CLUB DE COMPRAS $cod_tar $codigo  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('cedula');
		$cod_tar=$do->get('cod_tar');
		logusu('club',"CLUB DE COMPRAS $cod_tar $codigo ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('cod_tar');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM club WHERE cod_tar='$codigo'");
		if ($check > 0){
			$mSQL_1=$this->db->query("SELECT cedula, nombres, apellidos FROM club WHERE cod_tar='$codigo'");
			$row = $mSQL_1->row();
			$nombre =$row->nombres;
			$apellido =$row->apellidos;
			$cedula =$row->cedula;
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el cliente $nombre $apellido  $cedula ");
			return FALSE;
		}else {
  		return TRUE;
		}
	}
}
?>