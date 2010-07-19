<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//proveed
class pers extends validaciones {

	function pers(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(707,1);
		redirect("nomina/pers/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		$this->datasis->modulo_id(707,1);

		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';

		$filter = new DataFilter("Filtro de Personal", 'pers');
		$filter->script($script, "create");
		$filter->script($script, "modify");
		$filter->script->css_class='inputnum';
		
		$filter->cedula = new inputField("C&eacute;dula", "cedula");
		$filter->cedula->size=10;
		$filter->cedula->css_class='inputnum';
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=30;
		
		$filter->apellido = new inputField("Apellido", "apellido");
		$filter->apellido->size=30;
		
		$filter->contrato = new dropdownField("Contrato","contrato");
		$filter->contrato->style ="width:400px;";
		$filter->contrato->option("","");
		$filter->contrato->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
		
		$filter->divi = new dropdownField("Divisi&oacute;n", "divi");
		$filter->divi->style ="width:250px;";
		$filter->divi->option("","");
		$filter->divi->options("SELECT division,descrip FROM divi ORDER BY division");
				
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('nomina/pers/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Personal");
		$grid->order_by("codigo","asc");
		$grid->per_page = 20;

		$grid->column("C&oacute;digo",$uri);
		$grid->column("C&eacute;dula","cedula");
		$grid->column("Nombre","nombre");
		$grid->column("Apellidos","apellido");
		$grid->add("nomina/pers/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Personal</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}
	
	function dataedit(){
		
		$this->rapyd->load("dataedit2");
		$consulrif=$this->datasis->traevalor('CONSULRIF');
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		
		function consulrif(){
				vrif=$("#rif").val();
				if(vrif.length==0){
					alert("Debe introducir primero un RIF");
				}else{
					vrif=vrif.toUpperCase();
					$("#rif").val(vrif);
					window.open("'.$consulrif.'"+"?p_rif="+vrif,"CONSULRIF","height=350,width=410");
				}
		}
		
		';	
				
		$edit = new DataEdit2("Personal", "pers");
		$edit->back_url = site_url("nomina/pers/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");

		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
					  
		$sucu=array(
		'tabla'   =>'sucu',
		'columnas'=>array(
		'codigo'  =>'C&oacute;digo de Sucursal',
		'sucursal'=>'Sucursal'),
		'filtro'  =>array('codigo'=>'C&oacute;digo de Sucursal','sucursal'=>'Sucursal'),
		'retornar'=>array('codigo'=>'sucursal'),
		'titulo'  =>'Buscar Sucursal');
		
		$boton=$this->datasis->modbus($sucu);
		
		$cargo=array(
		'tabla'   =>'carg',
		'columnas'=>array(
		'cargo'  =>'C&oacute;digo de Cargo',
		'descrip'=>'Descripcion'),
		'filtro'  =>array('codigo'=>'C&oacute;digo de Cargo','descrip'=>'Descripcion'),
		'retornar'=>array('cargo'=>'cargo'),
		'titulo'  =>'Buscar Cargo');
		
		$boton1=$this->datasis->modbus($cargo);
		
		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'enlace'),
		'titulo'  =>'Buscar Empleado');
		
		$cboton=$this->datasis->modbus($scli);
		
		$edit->codigo =  new inputField("C&oacute;digo", "codigo");
		$edit->codigo->rule="trim|required|callback_chexiste";
		$edit->codigo->mode="autohide";
		$edit->codigo->maxlength=15;
		$edit->codigo->size=16;
		
		$edit->nacional = new dropdownField("C&eacute;dula", "nacional");
		$edit->nacional->style = "width:110px;";
		$edit->nacional->option("V","Venezolano");
		$edit->nacional->option("E","Extranjero");
		$edit->nacional->group = "Datos del Trabajador";
		 
		$edit->cedula =  new inputField("", "cedula");
		$edit->cedula->size = 14;
		$edit->cedula->maxlength= 8;
		$edit->cedula->in = "nacional";
		$edit->cedula->rule="trim|numeric|required";
		$edit->cedula->css_class='inputnum';
			
		$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick=""> Consultar RIF en el SENIAT</a>';
		$edit->rif =  new inputField("RIF", "rif");
		//$edit->rif->mode="autohide";
		$edit->rif->rule = "trim|strtoupper|callback_chrif";
		$edit->rif->append($lriffis);
		$edit->rif->maxlength=10;
		$edit->rif->size = 13;
		$edit->rif->group = "Datos del Trabajador";
		
		$edit->nombre =  new inputField("Nombre", "nombre");
		$edit->nombre->group = "Datos del Trabajador";
		$edit->nombre->size = 40;
		$edit->nombre->maxlength=30;
		$edit->nombre->rule="trim|required|strtoupper";
		
		$edit->apellido = new inputField("Apellidos", "apellido");
		$edit->apellido->group = "Datos del Trabajador";
		$edit->apellido->size = 40;
		$edit->apellido->maxlength=30;
		//$edit->apellido->in = "nombre";
		$edit->apellido->rule="trim|required|strtoupper";
		
		$edit->sexo = new dropdownField("Sexo", "sexo");
		$edit->sexo->style = "width:60px;";
		$edit->sexo->option("F","F");
		$edit->sexo->option("M","M");
		$edit->sexo->group = "Datos del Trabajador";
		
		//$edit->label1 = new freeField("EC","EC","<id class='littletableheader'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Estado Civil&nbsp;&nbsp; </id>");
		//$edit->label1->in = "sexo";
		
		$edit->civil = new dropdownField("Estado Civil", "civil");
		$edit->civil->style = "width:100px;";
		$edit->civil->option("S","Soltero");
		$edit->civil->option("C","Casado");
		$edit->civil->option("D","Divorciado");
		$edit->civil->option("V","Viudo");
		$edit->civil->group = "Datos del Trabajador";
		//$edit->civil->in = "sexo";
		
		$edit->direc1 = new inputField("Direcci&oacute;n", "direc1");
		$edit->direc1->group = "Datos del Trabajador";
		$edit->direc1->size =40;
		$edit->direc1->maxlength=30;
		$edit->direc1->rule="trim|strtoupper";
		
		$edit->direc2 = new inputField("&nbsp;", "direc2");
		$edit->direc2->size =40;
		$edit->direc2->group = "Datos del Trabajador";
		$edit->direc2->maxlength=30; 
		$edit->direc2->rule="trim|strtoupper";
		
		$edit->direc3 = new inputField("&nbsp;", "direc3");
		$edit->direc3->size =40;
		$edit->direc3->group = "Datos del Trabajador";
		$edit->direc3->maxlength=30;
		$edit->direc3->rule="trim|strtoupper";
		
		$edit->telefono = new inputField("Tel&eacute;fono", "telefono");
		$edit->telefono->size =40;
		$edit->telefono->group = "Datos del Trabajador";
		$edit->telefono->maxlength=30;
		$edit->telefono->rule="trim|strtoupper";
		
		$edit->email = new inputField("Email","email");
		$edit->email->size =50;
		$edit->email->group = "Datos del Trabajador";
		$edit->email->maxlength=50;
		$edit->email->rule="trim";
		
		$edit->posicion = new dropdownField("Tipo de Escritura" ,"escritura");
		$edit->posicion->option("","");                                                 
		$edit->posicion->options("SELECT codigo,posicion FROM posicion  ORDER BY codigo");
		$edit->posicion->group = "Datos del Trabajador";
		$edit->posicion->rule="trim|strtoupper";
		$edit->posicion->style ="width:170px;";
		
		$edit->nacimi = new DateField("Fecha de Nacimiento", "nacimi","d/m/Y");
		$edit->nacimi->size = 12;
		$edit->nacimi->group = "Datos del Trabajador"; 
		$edit->nacimi->rule="trim|chfecha";
		
		$edit->sucursal = new inputField("Sucursal", "sucursal");
		$edit->sucursal->size =4;
		$edit->sucursal->maxlength=2;
		$edit->sucursal->group = "Relaci&oacute;n Laboral";
		$edit->sucursal->append($boton);
		$edit->sucursal->rule="trim|strtoupper";
				
		$edit->divi = new dropdownField("Divisi&oacute;n", "divi");
		$edit->divi->style ="width:250px;";
		$edit->divi->option("","");
		$edit->divi->options("SELECT division,descrip FROM divi ORDER BY division");
		$edit->divi->onchange = "get_depto();";
		$edit->divi->group = "Relaci&oacute;n Laboral";
		
		$edit->depa = new dropdownField("Departamento", "depto");
		$edit->depa->style ="width:250px;";
		$edit->depa->option("","");
		if($edit->_status=='modify'){
		$divi=$edit->getval('divi');
			if($divi!==FALSE){
				$edit->depa->options("SELECT departa,depadesc FROM depa where division='$divi' ORDER BY division");
			}else{
				$edit->depa->option("Seleccione un Division");
			}
		}
		$edit->depa->group = "Relaci&oacute;n Laboral";		
		
		$edit->contrato = new dropdownField("Contrato","contrato");
		$edit->contrato->style ="width:400px;";
		$edit->contrato->option("","");
		$edit->contrato->options("SELECT codigo,CONCAT('',codigo,nombre)as nombre FROM noco ORDER BY codigo");
		$edit->contrato->group = "Relaci&oacute;n Laboral";
		
		$edit->vencimiento = new DateField("Vencimiento", "vence","d/m/Y");
		$edit->vencimiento->size = 12;
		$edit->vencimiento->group = "Relaci&oacute;n Laboral";
		$edit->vencimiento->rule="trim|chfecha";
		
		$edit->cargo = new inputField("Cargo", "cargo");
		$edit->cargo->group = "Relaci&oacute;n Laboral";
		$edit->cargo->size =11;
		$edit->cargo->maxlength=8;
		$edit->cargo->append($boton1);
		$edit->cargo->rule="trim";                                   
		
		$edit->enlace = new inputField("Enlace","enlace");
		$edit->enlace->size =11;
		$edit->enlace->maxlength=5;
		$edit->enlace->group = "Relaci&oacute;n Laboral";
		$edit->enlace->append($cboton); 
		$edit->enlace->rule="trim|strtoupper";
						
		$edit->sso = new inputField("Nro. Seguro Social", "sso");
		$edit->sso->size =13;
		$edit->sso->maxlength=11;
		$edit->sso->group = "Relaci&oacute;n Laboral"; 
    //$edit->sso->rule="trim|numeric"; 
		$edit->sso->css_class='inputnum';
		
		$edit->observa = new textareaField("Observaci&oacute;n", "observa");
		$edit->observa->rule = "trim";
		$edit->observa->cols = 70;
		$edit->observa->rows =3;
		$edit->observa->group = "Relaci&oacute;n Laboral"; 
		
		$edit->ingreso = new DateField("Fecha de Ingreso", "ingreso","d/m/Y");
		$edit->ingreso->size = 12;
		$edit->ingreso->group = "Relaci&oacute;n Laboral";
		$edit->ingreso->rule="trim|chfecha";
		
		$edit->label2 = new freeField("Edo. C","edoci","<id class='littletableheader'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha de Retiro&nbsp;&nbsp; </id>");
		$edit->label2->in = "ingreso";
		
		$edit->retiro =  new DateField("Fecha de Retiro", "retiro","d/m/Y");    
		$edit->retiro->size = 12;
		$edit->retiro->in = "ingreso";
		$edit->retiro->rule="trim|chfecha";
		
		/*$edit->trabaja = new dropdownField("Tipo de Trabajador","tipot");
		$edit->trabaja->option("","");
		$edit->trabaja->options("SELECT codigo,tipo  FROM tipot ORDER BY codigo");
		$edit->trabaja->group = "Relaci&oacute;n Laboral";
		$edit->trabaja->style = "width:200px;";*/
		
		$edit->tipo = new dropdownField("Tipo de N&oacute;mina","tipo");
		$edit->tipo->option("","");
		$edit->tipo->options(array("Q"=> "Quincenal","M"=>"Mensual","S"=>"Semanal"));
		$edit->tipo->group = "Relaci&oacute;n Laboral";
		$edit->tipo->style = "width:100px;";
		
		$edit->dialib = new inputField("Dias libres", "dialib");
		$edit->dialib->group = "Relaci&oacute;n Laboral";
		$edit->dialib->size =4;
		$edit->dialib->maxlength=2;
		$edit->dialib->rule="trim|numeric";
		$edit->dialib->css_class='inputnum';
		
		$edit->label3 = new freeField("DL","DL","<id class='littletableheader'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dias Laborables&nbsp;&nbsp; </id>");
		$edit->label3->in = "dialib";
		
		$edit->dialab =  new inputField("Dias laborables", "dialab");
		$edit->dialab->group = "Relaci&oacute;n Laboral";
		$edit->dialab->size =4;
		$edit->dialab->maxlength=2;
		$edit->dialab->in = "dialib";
		//$edit->dialab->rule="trim|numeric";
		//$edit->dialab->css_class='inputnum';
		
		$edit->status = new dropdownField("Estatus", "status");
		$edit->status->option("","");
		$edit->status->options(array("A"=> "Activo","V"=>"Vacaciones","R"=>"Retirado","I"=>"Inactivo","P"=>"Permiso"));
		$edit->status->group = "Relaci&oacute;n Laboral";
		$edit->status->style = "width:100px;";
		
		$edit->carnet =  new inputField("Nro. Carnet", "carnet");
		$edit->carnet->size = 13;
		$edit->carnet->maxlength=10;
		$edit->carnet->group = "Relaci&oacute;n Laboral";
		$edit->carnet->rule="trim"; 
			
		$edit->tipocuent = new dropdownField("Tipo Cuenta", "tipoe");
		$edit->tipocuent->option("","");
		$edit->tipocuent->options(array("A"=> "Ahorro","C"=>"Corriente"));
		$edit->tipocuent->group = "Datos Cuenta Bancaria";
		$edit->tipocuent->style = "width:100px;";
		
		$edit->cuentab = new inputField("Nro. Cuenta", "cuentab");
		$edit->cuentab->group = "Datos Cuenta Bancaria";
		$edit->cuentab->size =40;
		$edit->cuentab->maxlength=40;
		//$edit->cuentab->rule="trim|numeric";
		//$edit->cuentab->css_class='inputnum';
		
		$edit->vari1 = new inputField("Retenci&oacute;n SSO", "vari1");
		$edit->vari1->group = "Variables";
		$edit->vari1->size =16;
		$edit->vari1->maxlength=14;
		$edit->vari1->rule="trim|numeric";
		$edit->vari1->css_class='inputnum';
		
		$edit->vari2 = new inputField("Retenci&oacute;n FAOV", "vari2");
		$edit->vari2->group = "Variables";
		$edit->vari2->size =16;
		$edit->vari2->maxlength=14;
		$edit->vari2->rule="trim|numeric";
		$edit->vari2->css_class='inputnum';
		
		$edit->vari3 = new inputField("Retenci&oacute;n ISLR", "vari3");
		$edit->vari3->group = "Variables";
		$edit->vari3->size =16;
		$edit->vari3->maxlength=14;
		$edit->vari3->rule="trim|numeric";
		$edit->vari3->css_class='inputnum';
		        
		$edit->vari4 = new inputField("Variable 4", "vari4");
		$edit->vari4->group = "Variables";
		$edit->vari4->size =12;
		$edit->vari4->maxlength=11;
		$edit->vari4->rule="trim|numeric";
		$edit->vari4->css_class='inputnum';
		      
		$edit->vari5 = new DateField("Variable 5", "vari5");
		$edit->vari5->group = "Variables";
		$edit->vari5->size =12;
		$edit->vari5->maxlength=12;
		$edit->vari5->rule="trim|chfecha";
		
		$edit->vari6 = new inputField("Variable 6", "vari6");
		$edit->vari6->group = "Variables";
		$edit->vari6->size =16;
		$edit->vari6->maxlength=14;
		$edit->vari6->rule="trim|numeric";
		$edit->vari6->css_class='inputnum';
		    
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$link=site_url('nomina/pers/depto');
	$data['script']  =<<<script
		<script type="text/javascript" charset="utf-8">
		function get_depto(){
				var divi=$("#divi").val();
				$.ajax({
					url: "$link"+'/'+divi,
					success: function(msg){
						$("#td_depto").html(msg);								
					}
				});
									//alert(divi);
			} 
		</script>
script;

		$conten["form"]  =& $edit;
		$data['content'] = $this->load->view('view_pers', $conten,true);
		//$data['content'] = $edit->output; 
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Personal</h1>';
		$this->load->view('view_ventanas', $data);
	}
	
	function depto($divi=NULL){
		$this->rapyd->load("fields");
		$depa = new dropdownField("Departamento", "depto");
		$depa->status = "modify";
		$depa->style ="width:400px;";
		//echo 'de nuevo:'.$tipoa;
		if ($divi!==false){
			$depa->options("SELECT departa,depadesc FROM depa where division='$divi' ORDER BY division");			
		}else{
			$depa->option("Seleccione un Division");
		}
		$depa->build(); 
		echo $depa->output;
		}
	function _pre_del($do) {
		$codigo=$do->get('codigo');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM nomina WHERE codigo='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM asig   WHERE codigo='$codigo'");
	
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Trabajador con Movimiento no puede ser Borrado';
			return False;
		}
		return True;
	}
	function _post_insert($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('pers',"PERSONAL $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('pers',"PERSONAL $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('codigo');
		$nombre=$do->get('nombre');
		logusu('pers',"PERSONAL $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM pers WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"Personal con el codigo $codigo nombre $nombre ya existe");
			return FALSE;
		}else {
			return TRUE;
		}	
	} 
	function instalar(){
		
		$mSQL="CREATE TABLE `tipoe` (`codigo` VARCHAR (10), `tipo` VARCHAR (50), PRIMARY KEY(`codigo`))";
		$this->db->query($mSQL);
		$mSQL1="ALTER TABLE `pers` ADD `email` VARCHAR(50) NULL";
		$this->db->query($mSQL1);
		$mSQL2="CREATE TABLE `posicion` (`codigo` VARCHAR (10), `posicion` VARCHAR (30),PRIMARY KEY(`codigo`))";
		$this->db->query($mSQL2);
		$mSQL3="ALTER TABLE `pers` CHANGE `tipoe` VARCHAR(10)";
		$this->db->query($mSQL3);
		$mSQL4="ALTER TABLE `pers` CHANGE `retiro` `escritura` VARCHAR(25)";
		$this->db->query($mSQL4);
		$mSQL5="ALTER TABLE `pers` ADD `observa` TEXT ";
		$this->db->query($mSQL5);
	}
}
?>