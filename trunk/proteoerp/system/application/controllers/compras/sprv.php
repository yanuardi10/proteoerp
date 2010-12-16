<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Sprv extends validaciones {

	function sprv(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->datasis->modulo_id(206,1);
		//I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		define ("THISFILE",   APPPATH."controllers/compras/". $this->uri->segment(2).EXT);
	}

	function index(){
		redirect("compras/sprv/filteredgrid");
	}

	function filteredgrid(){

		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Proveedores', 'sprv');

		$filter->proveed = new inputField('C&oacute;digo','proveed');
		$filter->proveed->size=13;

		$filter->nombre = new inputField('Nombre', 'nombre');
		$filter->nombre->maxlength=40;

		$filter->rif = new inputField('Rif', 'rif');
		$filter->rif->size=18;
		$filter->rif->maxlength=30;

		$filter->cuenta = new inputField('Cuenta contable', 'cuenta');
		$filter->cuenta->size=13;
		$filter->cuenta->like_side='after';

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('compras/sprv/dataedit/show/<#id#>','<#proveed#>');

		$grid = new DataGrid('Lista de Proveedores');
		$grid->order_by('proveed','asc');
		$grid->per_page = 10;

		$grid->column_orderby('C&oacute;digo',$uri,'codigo');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('R.I.F.','rif','rif');
		$grid->column_orderby('% Ret.','reteiva','reteiva','align=\'right\'');
		$grid->column_orderby('Cuenta','cuenta','cuenta','align=\'right\'');

		$grid->add('compras/sprv/dataedit/create','Agregar un proveedor');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Proveedores</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load("dataedit");

		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto',
		'nomfis'=>'Nom. Fiscal'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cliente','nomfis'=>'nomfis'),
		'titulo'  =>'Buscar Cliente');

		$qformato=$this->qformato=$this->datasis->formato_cpla();

		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
			);

		$bsclid =$this->datasis->modbus($mSCLId);
		$bcpla  =$this->datasis->modbus($mCPLA);


		$smenu['link']=barra_menu('131');
		$consulrif=$this->datasis->traevalor('CONSULRIF');
		$link=site_url('compras/sprv/uproveed');
		$script ='
		$(function() {
			$("#tr_gr_desc").hide();
			$("#grupo").change(function(){grupo();}).change();
			$(".inputnum").numeric(".");
			$("#banco1").change(function () { acuenta(); }).change();
			$("#banco2").change(function () { acuenta(); }).change();
		});
		function grupo(){
			t=$("#grupo").val();
			a=$("#grupo :selected").text();
			$("#gr_desc").val(a);
		}
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
		function anomfis(){
				vtiva=$("#tiva").val();
				if(vtiva=="C" || vtiva=="E" || vtiva=="R"){
					$("#tr_nomfis").show();
					$("#tr_riff").show();
				}else{
					$("#nomfis").val("");
					$("#rif").val("");
					$("#tr_nomfis").hide();
					$("#tr_rif").hide();
				}
		}

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
		function ultimo(){
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo codigo ingresado fue: " + msg );
				}
			});
		}';

		$edit = new DataEdit('Proveedores', 'sprv');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');
		$edit->back_url = site_url('compras/sprv/filteredgrid');

		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$lproveed='<a href="javascript:ultimo();" title="Consultar ultimo codigo ingresado" onclick="">Consultar &uacute;ltimo c&oacute;digo ingresado</a>';
		$edit->proveed  = new inputField('C&oacute;digo', 'proveed');
		$edit->proveed->rule = 'trim|required|callback_chexiste';
		$edit->proveed->mode = 'autohide';
		$edit->proveed->size = 13;
		$edit->proveed->maxlength =5;
		$edit->proveed->append($lproveed);
		$edit->proveed->group = 'Datos del Proveedor';

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rule = 'trim|strtoupper|required';
		$edit->nombre->size = 41;
		$edit->nombre->maxlength =40;
		$edit->nombre->group = "Datos del Proveedor";

		$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->rif =  new inputField("Rif", "rif");
		//$edit->rif->mode="autohide";
		$edit->rif->rule = "trim|strtoupper|required|callback_chci";
		$edit->rif->append($lriffis);
		$edit->rif->maxlength=20;
		$edit->rif->size =18;
		$edit->rif->group = "Datos del Proveedor";

		//$edit->nit = new inputField("NIT", "nit");
		//$edit->nit->size =15;
		//$edit->nit->maxlength =12;
		//$edit->nit->group = "Datos del Proveedor";

		$edit->contacto = new inputField("Persona de contacto", "contacto");
		$edit->contacto->size =41;
		$edit->contacto->rule ="trim";
		$edit->contacto->maxlength =40;
		$edit->contacto->group = "Datos del Proveedor";

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("","Seleccionar");
		$edit->tipo->options(array("1"=> "Jur&iacute;dico Domiciliado","2"=>"Residente", "3"=>"Jur&iacute;dico No Domiciliado","4"=>"No Residente","5"=>"Excluido del Libro de Compras","0"=>"Inactivo"));
		$edit->tipo->style = "width:290px";
		$edit->tipo->rule = "required";
		$edit->tipo->group = "Datos del Proveedor";

		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->option("","Seleccionar");
		$edit->grupo->options("SELECT grupo,gr_desc,grupo FROM grpr ORDER BY gr_desc");
		$edit->grupo->style = "width:290px";
		//$edit->grupo->rule = "required";
		$edit->grupo->group = "Datos del Proveedor";

		$edit->gr_desc = new inputField("gr_desc", "gr_desc");

		for($i=1;$i<=3;$i++){
			$obj="direc$i";
			$edit->$obj = new inputField("Direcci&oacute;n $i",$obj);
			$edit->$obj->size =41;
			$edit->$obj->rule ="trim";
			$edit->$obj->maxlength =40;
			$edit->$obj->group = "Datos del Proveedor";
		}

		$edit->telefono = new inputField("Tel&eacute;fono", "telefono");
		$edit->telefono->size = 41;
		$edit->telefono->rule = "trim";
		$edit->telefono->group = "Datos del Proveedor";
		$edit->telefono->maxlength =40;

		$atts = array(
				'width'     =>'800',
				'height'    =>'600',
				'scrollbars'=>'yes',
				'status'    =>'yes',
				'resizable' =>'yes',
				'screenx'   =>'5',
				'screeny'   =>'5');

		$lcli=anchor_popup("/ventas/scli/dataedit/create","Agregar Cliente",$atts);

		$edit->observa  = new inputField("Observaci&oacute;n", "observa");
		$edit->observa->group = "Datos del Proveedor";
		$edit->observa->rule = "trim";
		$edit->observa->size = 41;

		$edit->email  = new inputField("Email", "email");
		$edit->email->rule = "trim|valid_email";
		$edit->email->size =41;
		$edit->email->maxlength =30;
		$edit->email->group = "Datos del Proveedor";

		$edit->url   = new inputField("URL", "url");
		$edit->url->group = "Datos del Proveedor";
		$edit->url->rule = "trim";
		$edit->url->size =41;
		$edit->url->maxlength =30;

		for($i=1;$i<=2;$i++){
			$obj="banco$i";
			$edit->$obj = new dropdownField("Cuenta en bco. ($i)", $obj);
			$edit->$obj->clause="where";
			$edit->$obj->option("","Ninguno");
			$edit->$obj->options("SELECT cod_banc,nomb_banc FROM tban ORDER BY nomb_banc");
			$edit->$obj->operator="=";
			$edit->$obj->group = "Cuentas Bancarias";
			$edit->$obj->style='width:290px;';

			$obj="cuenta$i";
			$edit->$obj = new inputField("&nbsp;&nbsp;N&uacute;mero ($i)",$obj);
			$edit->$obj->size = 41;
			$edit->$obj->rule = "trim";
			$edit->$obj->maxlength = 15;
			$edit->$obj->group = "Cuentas Bancarias";
			//$edit->$obj->in="banco$i";
		}

		$edit->tiva  = new dropdownField("Contribuyente T.", "tiva");
		$edit->tiva->option("N","Nacional");
		$edit->tiva->options(array("N"=>"Nacional","I"=>"Internacional","O"=>"Otros"));
		$edit->tiva->style='width:290px;';

		$edit->cliente  = new inputField("Cliente", "cliente");
		$edit->cliente->size =13;
		$edit->cliente->rule ="trim";
		$edit->cliente->readonly=true;
		$edit->cliente->append($bsclid);
		$edit->cliente->append($lcli);
		//$edit->cliente->group = "Datos del Proveedor";

		$edit->nomfis = new inputField("Nombre Fiscal", "nomfis");
		$edit->nomfis->size =41;
		$edit->nomfis->rule ="rule";
		$edit->nomfis->readonly =true;

		$lcuent=anchor_popup("/contabilidad/cpla/dataedit/create","Agregar Cuenta Contable",$atts);
		$edit->cuenta = new inputField("Cuenta. Contable", "cuenta");
		$edit->cuenta->rule='trim|callback_chcuentac';
		$edit->cuenta->size =13;
		//$edit->cuenta->readonly=true;
		$edit->cuenta->append($bcpla);
		$edit->cuenta->append($lcuent);

		$edit->reteiva  = new inputField("% de Retenci&oacute;n","reteiva");
		$edit->reteiva->size = 6;
		$edit->reteiva->css_class='inputnum';

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		//$smenu['link']=barra_menu('230');
		$data['content'] = $edit->output;
		//$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['title']   = "<h1>Proveedores</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _pre_del($do) {
		$codigo=$do->get('proveed');
		$chek =  $this->datasis->dameval("SELECT count(*) FROM sprm WHERE cod_prv='$codigo'");
		$chek += $this->datasis->dameval("SELECT count(*) FROM scst WHERE proveed='$codigo'");
		$chek += $this->datasis->dameval("SELECT count(*) FROM gser WHERE proveed='$codigo'");
		$chek += $this->datasis->dameval("SELECT count(*) FROM ords WHERE proveed='$codigo'");
		$chek += $this->datasis->dameval("SELECT count(*) FROM bmov WHERE clipro='P' AND codcp='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}
		return True;
	}
	function _post_insert($do){
		$codigo=$do->get('proveed');
		$nombre=$do->get('nombre');
		logusu('sprv',"PROVEEDOR $codigo NOMBRE $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('proveed');
		$nombre=$do->get('nombre');
		logusu('sprv',"PROVEEDOR $codigo NOMBRE $nombre MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('proveed');
		$nombre=$do->get('nombre');
		logusu('sprv',"PROVEEDOR $codigo NOMBRE $nombre ELIMINADO");
	}
	function chexiste(){
		$codigo=$this->input->post('proveed');
		$rif=$this->input->post('rif');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE proveed='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM sprv WHERE proveed='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el proveedor $nombre");
			return FALSE;
		}elseif(strlen($rif)>0){
			$chek=$this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE rif='$rif'");
			if ($chek > 0){
				$nombre=$this->datasis->dameval("SELECT nombre FROM sprv WHERE rif='$rif'");
				$this->validation->set_message('chexiste',"El rif $rif ya existe para el proveedor $nombre");
				return FALSE;
			}else {
				return TRUE;
			}
		}else{
			return TRUE;
		}
	}

	function _pre_insert($do){
		$rif=$do->get('rif');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE rif='$rif'");
		if($chek > 0){
			//$nombre=$this->datasis->dameval("SELECT nombre FROM sprv WHERE rif='$rif'");
			$do->error_message_ar['pre_insert'] = $do->error_message_ar['insert']='bobo';
			return FALSE;
		}else {
		return TRUE;
		}
	}

	function update(){
		$mSQL=$this->db->query('UPDATE sprv SET reteiva=75 WHERE reteiva<>100');
	}

	function uproveed(){
		$consulproveed=$this->datasis->dameval('SELECT MAX(proveed) FROM sprv');
		echo $consulproveed;
	}

	function instalar(){

		$mSQL='ALTER TABLE `sprv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);
		//$mSQL='ALTER TABLE `sprv` ADD UNIQUE `id` (`id`)';
		//$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD PRIMARY KEY `id` (`id`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `copre` VARCHAR(11) DEFAULT NULL NULL AFTER `cuenta` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `ocompra` CHAR(1) DEFAULT NULL NULL AFTER `copre` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `dcredito` DECIMAL(3,0) DEFAULT "0" NULL AFTER `ocompra` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `despacho` DECIMAL(3,0) DEFAULT NULL NULL AFTER `dcredito` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `visita` VARCHAR(9) DEFAULT NULL NULL AFTER `despacho` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `cate` VARCHAR(20) NULL AFTER `visita` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `reteiva` DECIMAL(7,2) DEFAULT "0.00" NULL AFTER `cate` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` ADD `ncorto` VARCHAR(20) DEFAULT NULL NULL AFTER `nombre` ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `direc1` `direc1` VARCHAR(105) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `direc2` `direc2` VARCHAR(105) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `direc3` `direc3` VARCHAR(105) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `nombre` `nombre` VARCHAR(60) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sprv` CHANGE `nomfis` `nomfis` VARCHAR(200) DEFAULT NULL NULL  ';
		$this->db->simple_query($mSQL);
	}
}
?>
