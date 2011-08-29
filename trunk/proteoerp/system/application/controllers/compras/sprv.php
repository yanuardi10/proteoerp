<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Sprv extends validaciones {

	function sprv(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('pi18n');
		$this->datasis->modulo_id(206,1);
	}

	function index(){
		if($this->pi18n->pais=='COLOMBIA'){
			redirect('compras/sprvcol/filteredgrid');
		}else{ 
			redirect('compras/sprv/filteredgrid');
		}
	}

	function filteredgrid(){

		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Proveedores', 'sprv');

		$filter->proveed = new inputField('C&oacute;digo','proveed');
		$filter->proveed->size=13;
		$filter->proveed->group = "UNO";

		$filter->nombre = new inputField('Nombre', 'nombre');
		$filter->nombre->maxlength=30;
		$filter->nombre->group = "UNO";

		$filter->tipo = new dropdownField('Tipo', 'tipo');
		$filter->tipo->option('','Todos');
		$filter->tipo->options(array('1'=> 'Jur&iacute;dico Domiciliado','2'=>'Residente', '3'=>'Jur&iacute;dico No Domiciliado','4'=>'No Residente','5'=>'Excluido del Libro de Compras','0'=>'Inactivo'));
		$filter->tipo->style = 'width:200px';
		$filter->tipo->group = "UNO";

		$filter->rif = new inputField('R.I.F.', 'rif');
		$filter->rif->size=18;
		$filter->rif->maxlength=30;
		$filter->rif->group = "DOS";

		$filter->cuenta = new inputField('Cuenta contable', 'cuenta');
		$filter->cuenta->size=13;
		$filter->cuenta->like_side='after';
		$filter->cuenta->group = "DOS";

		$filter->telefono = new inputField('Telefono', 'telefono');
		$filter->telefono->size=18;
		$filter->telefono->like_side='after';
		$filter->telefono->group = "DOS";

		$filter->cuenta = new inputField('Cuenta contable', 'cuenta');
		$filter->cuenta->size=13;
		$filter->cuenta->like_side='after';
		$filter->cuenta->group = "DOS";

		$filter->buttons('reset','search');
		$filter->build("dataformfiltro");

		$uri = anchor('compras/sprv/dataedit/show/<#id#>','<#proveed#>');

		$grid = new DataGrid('Lista de Proveedores');
		$grid->order_by('proveed','asc');
		$grid->per_page = 50;

		$uri2  = anchor('compras/sprv/dataedit/show/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12px')));
		$uri2 .= img(array('src'=>'images/<siinulo><#tipo#>|N|S</siinulo>.gif','border'=>'0','alt'=>'Estado','title'=>'Estado'));


		$grid->column('Acciones',$uri2,'align=\'center\'');
		$grid->column_orderby('C&oacute;digo',$uri,'codigo');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('R.I.F.','rif','rif');
		$grid->column_orderby('Telefonos','telefono','telefono');
		$grid->column_orderby('Contacto','contacto','contacto');
		$grid->column_orderby('% Ret.','reteiva','reteiva','align=\'right\'');
		$grid->column_orderby('Cuenta','cuenta','cuenta','align=\'right\'');

		$grid->add('compras/sprv/dataedit/create','Agregar un proveedor');
		$grid->build('datagridST');


//************ SUPER TABLE ************* 
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
	headerRows : 1,
	onStart : function () {	this.start = new Date();},
	onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
	});
})();
//]]>
</script>
';
		$style ='
<style type="text/css">
.fakeContainer { /* The parent container */
    margin: 5px;
    padding: 0px;
    border: none;
    width: 740px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************


		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		
		$data['title']   = '<h1>Proveedores</h1>';

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');

		$data['extras']  = $extras;		

		$data['head']    = script('jquery.js');
		$data["head"]   .= script('superTables.js');
		$data['head']   .= $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

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
		//$edit->proveed->group = 'Datos del Proveedor';

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rule = 'trim|strtoupper|required';
		$edit->nombre->size = 41;
		$edit->nombre->maxlength =40;

		$lriffis='<a href="javascript:consulrif();" title="Consultar RIF en el SENIAT" onclick="" style="color:red;font-size:9px;border:none;">SENIAT</a>';
		$edit->rif =  new inputField('Rif', 'rif');
		$edit->rif->rule = "trim|strtoupper|required|callback_chci";
		$edit->rif->append($lriffis);
		$edit->rif->maxlength=13;
		$edit->rif->size =12;

		$edit->contacto = new inputField("Contacto", "contacto");
		$edit->contacto->size =41;
		$edit->contacto->rule ="trim";
		$edit->contacto->maxlength =40;
		//$edit->contacto->group = "Datos del Proveedor";

		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->option("","Seleccionar");
		$edit->grupo->options("SELECT grupo,gr_desc,grupo FROM grpr ORDER BY gr_desc");
		$edit->grupo->style = "width:190px";
		//$edit->grupo->rule = "required";
		$edit->grupo->group = "Datos del Proveedor";
		$edit->gr_desc = new inputField("gr_desc", "gr_desc");

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("","Seleccionar");
		$edit->tipo->options(array("1"=> "Jur&iacute;dico Domiciliado","2"=>"Residente", "3"=>"Jur&iacute;dico No Domiciliado","4"=>"No Residente","5"=>"Excluido del Libro de Compras","0"=>"Inactivo"));
		$edit->tipo->style = "width:190px";
		$edit->tipo->rule = "required";
		$edit->tipo->group = "Datos del Proveedor";

		$edit->tiva  = new dropdownField("Origen", "tiva");
		$edit->tiva->option("N","Nacional");
		$edit->tiva->options(array("N"=>"Nacional","I"=>"Internacional","O"=>"Otros"));
		$edit->tiva->style='width:190px;';

		$edit->direc1 = new inputField("Direcci&oacute;n ",'direc1');
		$edit->direc1->size =40;
		$edit->direc1->rule ="trim";
		$edit->direc1->maxlength =40;

		$edit->direc2 = new inputField(" ",'direc2');
		$edit->direc2->size =40;
		$edit->direc2->rule ="trim";
		$edit->direc2->maxlength =40;

		$edit->direc3 = new inputField(" ",'direc3');
		$edit->direc3->size =40;
		$edit->direc3->rule ="trim";
		$edit->direc3->maxlength =40;

		$edit->telefono = new inputField("Tel&eacute;fono", "telefono");
		$edit->telefono->size = 30;
		$edit->telefono->rule = "trim";
		$edit->telefono->group = "Datos del Proveedor";
		$edit->telefono->maxlength =40;

		$edit->email  = new inputField("Email", "email");
		$edit->email->rule = "trim|valid_email";
		$edit->email->size =30;
		$edit->email->maxlength =30;
		//$edit->email->group = "Datos del Proveedor";

		$edit->url   = new inputField("URL", "url");
		$edit->url->group = "Datos del Proveedor";
		$edit->url->rule = "trim";
		$edit->url->size =30;
		$edit->url->maxlength =30;

		$atts = array(
				'width'     =>'800',
				'height'    =>'600',
				'scrollbars'=>'yes',
				'status'    =>'yes',
				'resizable' =>'yes',
				'screenx'   =>'5',
				'screeny'   =>'5');

		$lcli=anchor_popup("/ventas/scli/dataedit/create",image('list_plus.png','Agregar',array("border"=>"0")),$atts);
		//$AddUnidad='<a href="javascript:add_unidad();" title="Haz clic para Agregar una unidad nueva">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';

		$edit->observa  = new inputField("Observaci&oacute;n", "observa");
		$edit->observa->group = "Datos del Proveedor";
		$edit->observa->rule = "trim";
		$edit->observa->size = 41;

		$obj="banco1";
		$edit->$obj = new dropdownField("Cuenta en bco. (1)", $obj);
		$edit->$obj->clause="where";
		$edit->$obj->option("","Ninguno");
		$edit->$obj->options("SELECT cod_banc,nomb_banc FROM tban ORDER BY nomb_banc");
		$edit->$obj->operator="=";
		$edit->$obj->group = "Cuentas Bancarias";
		$edit->$obj->style='width:150px;';

		$obj="cuenta1";
		$edit->$obj = new inputField("&nbsp;&nbsp;N&uacute;mero (1)",$obj);
		$edit->$obj->size = 41;
		$edit->$obj->rule = "trim";
		$edit->$obj->maxlength = 15;
		$edit->$obj->group = "Cuentas Bancarias";
		//$edit->$obj->in="banco$i";

		$obj="banco2";
		$edit->$obj = new dropdownField("Cuenta en bco. (2)", $obj);
		$edit->$obj->clause="where";
		$edit->$obj->option("","Ninguno");
		$edit->$obj->options("SELECT cod_banc,nomb_banc FROM tban ORDER BY nomb_banc");
		$edit->$obj->operator="=";
		$edit->$obj->group = "Cuentas Bancarias";
		$edit->$obj->style='width:150px;';

		$obj="cuenta2";
		$edit->$obj = new inputField("&nbsp;&nbsp;N&uacute;mero (2)",$obj);
		$edit->$obj->size = 41;
		$edit->$obj->rule = "trim";
		$edit->$obj->maxlength = 15;
		$edit->$obj->group = "Cuentas Bancarias";


		$edit->cliente  = new inputField("Cliente", "cliente");
		$edit->cliente->size =13;
		$edit->cliente->rule ="trim";
		$edit->cliente->readonly=true;
		$edit->cliente->append($bsclid);
		$edit->cliente->append($lcli);
		//$edit->cliente->group = "Datos del Proveedor";

		$edit->nomfis = new inputField("Nombre", "nomfis");
		$edit->nomfis->size =80;
		$edit->nomfis->rule ="rule";
		//$edit->nomfis->readonly =true;

		$lcuent=anchor_popup("/contabilidad/cpla/dataedit/create","Agregar Cuenta Contable",$atts);
		$edit->cuenta = new inputField("Cuenta. Contable", "cuenta");
		$edit->cuenta->rule='trim|callback_chcuentac';
		$edit->cuenta->size =13;
		$edit->cuenta->append($bcpla);
		$edit->cuenta->append($lcuent);

		$edit->reteiva  = new inputField("% de Retenci&oacute;n","reteiva");
		$edit->reteiva->size = 6;
		$edit->reteiva->css_class='inputnum';

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_sprv', $conten,true);



		//$smenu['link']=barra_menu('230');
		//$data['content'] = $edit->output;
		//$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['title']   = "<h1>Proveedores</h1>";


		$data["head"]    = script("jquery.js");
		$data["head"]   .= script("plugins/jquery.numeric.pack.js");
		$data["head"]   .= script("plugins/jquery.floatnumber.js");
		$data["head"]   .= $this->rapyd->get_head();
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

	function consulta(){  
		$this->load->helper('openflash');
		$this->rapyd->load("datagrid");
		$fields = $this->db->field_data('sprv');
		$url_pk = $this->uri->segment_array();
		$coun=0; $pk=array();
		foreach ($fields as $field){
			if($field->primary_key==1){
				$coun++;
				$pk[]=$field->name;
			}
		}
		$values=array_slice($url_pk,-$coun);
		$claves=array_combine (array_reverse($pk) ,$values );

		$mCodigo = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=".$claves['id']."");
		
		$grid = new DataGrid('Ventas por Mes');
		$grid->db->_protect_identifiers=false;
		$grid->db->select( array('a.tipo_doc','a.fecha', 'a.numero', 'a.monto', 'a.abonos', 'a.monto-a.abonos saldo' ) );
		$grid->db->from('sprm a');
		$grid->db->where('a.cod_prv', $mCodigo );
		$grid->db->where('a.monto <> a.abonos');
		$grid->db->where('a.tipo_doc IN ("FC","ND","GI") ' );
		$grid->db->orderby('a.fecha');
			
		$grid->column("Fecha"   ,"fecha" );
		$grid->column("Tipo", "tipo_doc",'align="CENTER"');
		$grid->column("Numero",  "numero",'align="LEFT"');
		$grid->column("Monto",    "<nformat><#monto#></nformat>",  'align="RIGHT"');
		$grid->column("Abonos",  "<nformat><#abonos#></nformat>",'align="RIGHT"');
		$grid->column("Saldo",  "<nformat><#saldo#></nformat>",'align="RIGHT"');
		$grid->build();

		$nombre = $this->datasis->dameval("SELECT nombre FROM sprv WHERE id=".$claves['id']." ");

		$data['content'] = $grid->output;
		$data["head"]     = script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = '<h1>Consulta de Proveedor</h1>';
		$data["subtitle"] = "
			<div align='center' style='border: 2px outset #EFEFEF;background: #EFEFEF;font-size:18px'>
				<a href='javascript:javascript:history.go(-1)'>(".$mCodigo.") ".$nombre."</a>
			</div>";
		$this->load->view('view_ventanas', $data);
		
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
