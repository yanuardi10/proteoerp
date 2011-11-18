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
			redirect('compras/sprv/extgrid');
		}
	}

	function extgrid(){
		$this->datasis->modulo_id(206,1);
		$script = $this->sprvextjs();
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


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"nombre","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'sprv');

		$this->db->_protect_identifiers=false;
		$this->db->select('sprv.*, CONCAT("(",sprv.grupo,") ",grpr.gr_desc) nomgrup');
		$this->db->from('sprv');
		$this->db->join('grpr', 'sprv.grupo=grpr.grupo');

		if (strlen($where)>1){ $this->db->where($where);}

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$mSQL = '';
		if ( $filters ) $mSQL = $this->db->last_query();
		$results = $this->db->count_all('sprv');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data " ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear() {
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$proveed = $data['data']['proveed'];

		unset($campos['nomgrup']);
		unset($campos['id']);
		
		$mHay = $this->datasis->dameval("SELECT count(*) FROM sprv WHERE codigo='".$proveed."'");
		if  ( $mHay > 0 ){
			echo "{ success: false, message: 'Ya existe ese codigo'}";
		} else {
			$mSQL = $this->db->insert_string("sprv", $campos );
			$this->db->simple_query($mSQL);
			logusu('sprv',"PROVEEDOR $proveed $nombre CREADO");
			echo "{ success: true, message: ".$data['data']['proveed']."}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$codigo = $campos['proveed'];
		unset($campos['nomgrup']);
		unset($campos['proveed']);
		unset($campos['id']);
		//print_r($campos);
		$mSQL = $this->db->update_string("sprv", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('sprv',"PROVEEDOR ".$data['data']['proveed']." MODIFICADO");
		echo "{ success: true, message: 'Proveedor Modificado '}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$proveed = $data['data']['proveed'];
		
		// VERIFICAR SI PUEDE
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM sprm WHERE cod_prv='$proveed'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM scst WHERE proveed='$proveed'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM gser WHERE proveed='$proveed'");
		$chek += $this->datasis->dameval("SELECT count(*) FROM ordc WHERE proveed='$proveed'");
		$chek += $this->datasis->dameval("SELECT count(*) FROM bmov WHERE clipro='P' AND codcp='$proveed'");
		$chek += $this->datasis->dameval("SELECT count(*) FROM ords WHERE proveed='$proveed'");
		//$chek += $this->datasis->dameval("SELECT count(*) FROM obco WHERE proveed='$proveed'");

		if ($chek > 0){
			echo "{ success: false, message: 'Proveedor con Movimiento no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM sprv WHERE proveed='$proveed'");
			logusu('sprv',"PROVEEDOR $proveed ELIMINADO");
			echo "{ success: true, message: 'Proveedor Eliminado'}";
		}
	}



//****************************************************************8
//
//
//
//****************************************************************8
	function sprvextjs(){

		$encabeza='PROVEEDORES';
		$listados= $this->datasis->listados('sprv');
		$otros=$this->datasis->otros('sprv', 'sprv');

		$mSQL = "SELECT cod_banc, CONCAT(cod_banc,' ',nomb_banc) nombre FROM tban ORDER BY cod_banc ";
		$bancos = $this->datasis->llenacombo($mSQL);

		$mSQL = "SELECT grupo, CONCAT(grupo,' ',gr_desc) descrip FROM grpr ORDER BY grupo ";
		$grupo = $this->datasis->llenacombo($mSQL);

		$consulrif=$this->datasis->traevalor('CONSULRIF');

		$urlajax = 'compras/sprv/';
		$variables = "var mcliente = '';var mcuenta  = '';";
		$funciones = "";
		$valida = "
		{ type: 'length', field: 'proveed',  min:  1 },
		{ type: 'length', field: 'rif',      min: 10 }, 
		{ type: 'length', field: 'nombre',   min:  3 }
		";
		
		$columnas = "
		{ header: 'Codigo',        width:  60, sortable: true, dataIndex: 'proveed',  field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Tipo',          width:  60, sortable: true, dataIndex: 'tipo',     field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Nombre',        width: 220, sortable: true, dataIndex: 'nombre',   field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'R.I.F.',        width:  80, sortable: true, dataIndex: 'rif',      field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Grupo',         width:  50, sortable: true, dataIndex: 'grupo',    field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Telefono',      width:  90, sortable: true, dataIndex: 'telefono', field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Contacto',      width: 120, sortable: true, dataIndex: 'contacto', field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Cliente',       width:  60, sortable: true, dataIndex: 'cliente',  field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Ret%',          width:  50, sortable: true, dataIndex: 'reteiva',  field:  { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('00.00') }, 
		{ header: 'Origen',        width:  40, sortable: true, dataIndex: 'tiva',     field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Direccion',     width: 150, sortable: true, dataIndex: 'direc1',   field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Email',         width: 150, sortable: true, dataIndex: 'email',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Url',           width: 150, sortable: true, dataIndex: 'url',      field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Nombre Fiscal', width: 220, sortable: true, dataIndex: 'nomfis',   field:  { type: 'textfield' }, filter: { type: 'string'  }}
	";

		$campos = "'id','proveed','tipo','nombre','rif','grupo','nomgrup','telefono','contacto', 'direc1', 'direc2', 'direc3','cliente', 'observa', 'nit', 'codigo','tiva', 'email', 'url', 'banco1', 'cuenta1', 'banco2', 'cuenta2', 'nomfis', 'reteiva' ";

		$stores = "
var scliStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false, autoSync: false, pageSize: 30, pruneModifiedRecords: true, totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'ventas/scli/sclibusca',
		extraParams: {  'cliente': mcliente, 'origen': 'store' },
		reader: { type: 'json', totalProperty: 'results', root: 'data' }
	},
	method: 'POST'
});

var cplaStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false, autoSync: false, pageSize: 30, pruneModifiedRecords: true, totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'contabilidad/cpla/cplabusca',
		extraParams: {  'cuenta': mcuenta, 'origen': 'store' },
		reader: { type: 'json', totalProperty: 'results', root: 'data' }
	},
	method: 'POST'
});
		";

		$camposforma = "
							{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield', fieldLabel: 'Codigo',   labelWidth:60, name: 'proveed',  allowBlank: false, columnWidth : 0.20, id: 'proveed' },
									{ xtype: 'textfield', fieldLabel: 'RIF',      labelWidth:40, name: 'rif',      allowBlank: false, columnWidth : 0.25 },
									{ xtype: 'combo',     fieldLabel: 'Grupo',    labelWidth:80, name: 'grupo',    store: [".$grupo."], columnWidth: 0.50 },
									{ xtype: 'textfield', fieldLabel: 'Nombre',   labelWidth:60, name: 'nombre',   allowBlank: false, columnWidth : 0.60 },
									{ xtype: 'combo',     fieldLabel: 'Origen',   labelWidth:65, name: 'tiva',     store: [['N','Nacional'],['I','Internacional'],['O','Otro']], columnWidth: 0.35 },
									{ xtype: 'textfield', fieldLabel: 'Contacto', labelWidth:60, name: 'contacto', allowBlank: true, columnWidth : 0.60 },
									{ xtype: 'combo',     fieldLabel: 'Tipo',     labelWidth:65, name: 'tipo',     store: [['1','1-Jur. Domiciliado'],['2','2-Residente'],['3','3-J. no Domiciliado'],['4','4-No Residente'], ['5','5-Excluido de Libros'], ['0','0-Inactivo']], columnWidth: 0.35 }
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield',   fieldLabel: 'Nombre Fiscal', labelWidth:120, name: 'nomfis', allowBlank: true, columnWidth : 0.90 },
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield',   fieldLabel: 'Direccion', labelWidth:60, name: 'direc1',   allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'numberfield', fieldLabel: 'Retencion', labelWidth:80, name: 'reteiva',  hideTrigger: true, fieldStyle: 'text-align: right', width:130,renderer : Ext.util.Format.numberRenderer('00.00') },
									{ xtype: 'textfield',   fieldLabel: '.',         labelWidth:60, name: 'direc2',   allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'textfield',   fieldLabel: '.',         labelWidth:60, name: 'direc3',   allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'combo',       fieldLabel: 'Banco 1',   labelWidth:60, name: 'banco1',   store: [".$bancos."], columnWidth: 0.45 },
									{ xtype: 'textfield',   fieldLabel: 'Telefono',  labelWidth:60, name: 'telefono', allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'textfield',   fieldLabel: 'Cuenta 1',  labelWidth:60, name: 'cuenta1',  allowBlank: true, columnWidth : 0.45 },
									{ xtype: 'textfield',   fieldLabel: 'Email',     labelWidth:60, name: 'email',    allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'combo',       fieldLabel: 'Banco 2',   labelWidth:60, name: 'banco2',   store: [".$bancos."], columnWidth: 0.45 },
									{ xtype: 'textfield',   fieldLabel: 'Url',       labelWidth:60, name: 'url',      allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'textfield',   fieldLabel: 'Cuenta 2',  labelWidth:60, name: 'cuenta2',  allowBlank: true, columnWidth : 0.45 },
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{
										xtype: 'combo',
										fieldLabel: 'Codigo como Cliente',
										labelWidth:140,
										name: 'cliente',
										id:   'cliente',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: scliStore,
										columnWidth: 0.80
									},{
										xtype: 'combo',
										fieldLabel: 'Cuenta Contable',
										labelWidth:140,
										name: 'cuenta',
										id:   'cuenta',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: cplaStore,
										columnWidth: 0.80
									}
								]
							}
		";

		$titulow = 'Proveedores';

		$dockedItems = "
				\t\t\t\t{ itemId: 'seniat', text: 'SENIAT',   scope: this, handler: this.onSeniat },
				\t\t\t\t{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				\t\t\t\t{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 650,
				height: 470,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						
						if (registro) {
							mcliente = registro.data.cliente;
							mcuenta  = registro.data.cuenta;
							cplaStore.proxy.extraParams.cuenta   = mcuenta ;
							scliStore.proxy.extraParams.cliente = mcliente ;
							cplaStore.load({ params: { 'cliente': registro.data.cliente, 'origen': 'beforeform' } });
							scliStore.load({ params: { 'cuenta':  registro.data.cuenta,  'origen': 'beforeform' } });
							form.loadRecord(registro);
							form.findField('proveed').setReadOnly(true);
						} else {
							form.findField('proveed').setReadOnly(false);
							mcliente = '';
							mcuenta  = '';
						}
					}
				}
";

		$filtros = "var filters = { ftype: 'filters', encode: 'json', local: false }; ";
		
		$winmethod = "
				onSeniat: function(){
					var form = this.getForm();
					var vrif = form.findField('rif').value;
					if(vrif.length==0){
						alert('Debe introducir primero un RIF');
					}else{
						vrif = vrif.toUpperCase();
						window.open(\"".$consulrif."\"+\"?p_rif=\"+vrif,\"CONSULRIF\",\"height=350,width=410\");
					}
				}
";

		$features = "features: [{ ftype: 'grouping', groupHeaderTpl: '{name} ' }, filters],";


		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['stores']      = $stores;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['features']    = $features;
		$data['winwidget']   = $winwidget;
		$data['filtros']     = $filtros;
		$data['winmethod']   = $winmethod;
		
		$data['title']  = heading('Aranceles');
		$this->load->view('extjs/extjsven',$data);
	}

function meco() {
		$encabeza='<table width="100%" bgcolor="#2067B5"><tr><td align="left" width="100px"><img src="'.base_url().'assets/default/css/templete_01.jpg" width="120"></td><td align="center"><h1 style="font-size: 20px; color: rgb(255, 255, 255);" onclick="history.back()">PROVEEDORES</h1></td><td align="right" width="100px"><img src="'.base_url().'assets/default/images/cerrar.png" alt="Cerrar Ventana" title="Cerrar Ventana" onclick="parent.window.close()" width="25"></td></tr></table>';

		$mSQL = "SELECT cod_banc, CONCAT(cod_banc,' ',nomb_banc) nombre FROM tban ORDER BY cod_banc ";
		$bancos = $this->datasis->llenacombo($mSQL);

		$mSQL = "SELECT grupo, CONCAT(grupo,' ',gr_desc) descrip FROM grpr ORDER BY grupo ";
		$grupo = $this->datasis->llenacombo($mSQL);

		$consulrif=$this->datasis->traevalor('CONSULRIF');

		$listados= $this->datasis->listados('sprv');
		$otros=$this->datasis->otros('sprv', 'sprv');

		$script = "
<script type=\"text/javascript\">
var BASE_URL   = '".base_url()."';
var BASE_PATH  = '".base_url()."';
var BASE_ICONS = '".base_url()."assets/icons/';
var BASE_UX    = '".base_url()."assets/js/ext/ux';

Ext.Loader.setConfig({ enabled: true });
Ext.Loader.setPath('Ext.ux', BASE_UX);

Ext.require([
	'Ext.grid.*',
	'Ext.ux.grid.FiltersFeature',
	'Ext.data.*',
	'Ext.util.*',
	'Ext.state.*',
	'Ext.form.*',
	'Ext.window.MessageBox',
	'Ext.tip.*',
	'Ext.ux.CheckColumn',
	'Ext.toolbar.Paging'
]);

var registro;
var mxs = ((screen.availWidth/2)-400);
var mys = ((screen.availHeight/2)-300);

var urlApp = '".base_url()."';

var mcliente = '';
var mcuenta  = '';


//Column Model
var colSprv = [
		{ header: 'Codigo',        width:  60, sortable: true, dataIndex: 'proveed',  field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Tipo',          width:  60, sortable: true, dataIndex: 'tipo',     field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Nombre',        width: 220, sortable: true, dataIndex: 'nombre',   field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'R.I.F.',        width:  80, sortable: true, dataIndex: 'rif',      field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Grupo',         width:  50, sortable: true, dataIndex: 'grupo',    field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Telefono',      width:  90, sortable: true, dataIndex: 'telefono', field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Contacto',      width: 120, sortable: true, dataIndex: 'contacto', field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Cliente',       width:  60, sortable: true, dataIndex: 'cliente',  field:  { type: 'textfield' }, filter: { type: 'string'  }}, 
		{ header: 'Ret%',          width:  50, sortable: true, dataIndex: 'reteiva',  field:  { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('00.00') }, 
		{ header: 'Origen',        width:  40, sortable: true, dataIndex: 'tiva',     field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Direccion',     width: 150, sortable: true, dataIndex: 'direc1',   field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Email',         width: 150, sortable: true, dataIndex: 'email',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Url',           width: 150, sortable: true, dataIndex: 'url',      field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Nombre Fiscal', width: 220, sortable: true, dataIndex: 'nomfis',   field:  { type: 'textfield' }, filter: { type: 'string'  }}
	];



// Define our data model
var Proveedores = Ext.regModel('Proveedores', {
	fields: ['id','proveed','tipo','nombre','rif','grupo','nomgrup','telefono','contacto', 'direc1', 'direc2', 'direc3','cliente', 'observa', 'nit', 'codigo','tiva', 'email', 'url', 'banco1', 'cuenta1', 'banco2', 'cuenta2', 'nomfis', 'reteiva' ],
	validations: [
		{ type: 'length', field: 'proveed',  min: 1 },
		{ type: 'length', field: 'rif',      min: 12 }, 
		{ type: 'length', field: 'nombre',   min: 3 }
	],
	proxy: {
		type: 'ajax',
		noCache: false,
		api: {
			read   : urlApp + 'compras/sprv/grid',
			create : urlApp + 'compras/sprv/crear',
			update : urlApp + 'compras/sprv/modificar' ,
			destroy: urlApp + 'compras/sprv/eliminar',
			method: 'POST'
			},
		reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data',
			messageProperty: 'message',
			totalProperty: 'results'
			},
		writer: {
			type: 'json',
			root: 'data',
			writeAllFields: true,
			callback: function( op, suc ) {
				Ext.Msg.Alert('que paso');
				}
			},
		listeners: {
			exception: function( proxy, response, operation) {
				Ext.MessageBox.show({
					title: 'EXCEPCION REMOTA',
					msg: operation.getError(),
					icon: Ext.MessageBox.ERROR,
					buttons: Ext.Msg.OK
				});
			}
		}
	}
});

//Data Store
var storeSprv = Ext.create('Ext.data.Store', {
	model: 'Proveedores',
	pageSize: 50,
	remoteSort: true,
	autoLoad: false,
	autoSync: true,
	groupField: 'nomgrup',
	method: 'POST',
	listeners: {
		write: function(mr,re, op) {
			Ext.Msg.alert('Aviso','Registro Guardado ')
		}
	}
});

var scliStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,
	autoSync: false,
	pageSize: 50,
	pruneModifiedRecords: true,
	totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'ventas/scli/sclibusca',
		extraParams: {  'cliente': mcliente, 'origen': 'store' },
		reader: {
			type: 'json',
			totalProperty: 'results',
			root: 'data'
		}
	},
	method: 'POST'
});

var cplaStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false,
	autoSync: false,
	pageSize: 50,
	pruneModifiedRecords: true,
	totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'contabilidad/cpla/cplabusca',
		extraParams: {  'cuenta': mcuenta, 'origen': 'store' },
		reader: {
			type: 'json',
			totalProperty: 'results',
			root: 'data'
		}
	},
	method: 'POST'
});


var win;
// Main 
Ext.onReady(function(){
	function showContactForm() {
		if (!win) {
			// Create Form
			var writeForm = Ext.define('Sprv.Form', {
				extend: 'Ext.form.Panel',
				alias:  'widget.writerform',
				result: function(res){
					alert('Resultado');
				},
				requires: ['Ext.form.field.Text'],
				initComponent: function(){
					Ext.apply(this, {
						iconCls: 'icon-user',
						frame: true, 
						title: 'Proveedores', 
						bodyPadding: 3,
						fieldDefaults: { labelAlign: 'right' }, 
						items: [
							{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield', fieldLabel: 'Codigo',   labelWidth:60, name: 'proveed',  allowBlank: false, columnWidth : 0.20, id: 'proveed' },
									{ xtype: 'textfield', fieldLabel: 'RIF',      labelWidth:40, name: 'rif',      allowBlank: false, columnWidth : 0.25 },
									{ xtype: 'combo',     fieldLabel: 'Grupo',    labelWidth:80, name: 'grupo',    store: [".$grupo."], columnWidth: 0.50 },
									{ xtype: 'textfield', fieldLabel: 'Nombre',   labelWidth:60, name: 'nombre',   allowBlank: false, columnWidth : 0.60 },
									{ xtype: 'combo',     fieldLabel: 'Origen',   labelWidth:65, name: 'tiva',     store: [['N','Nacional'],['I','Internacional'],['O','Otro']], columnWidth: 0.35 },
									{ xtype: 'textfield', fieldLabel: 'Contacto', labelWidth:60, name: 'contacto', allowBlank: true, columnWidth : 0.60 },
									{ xtype: 'combo',     fieldLabel: 'Tipo',     labelWidth:65, name: 'tipo',     store: [['1','1-Jur. Domiciliado'],['2','2-Residente'],['3','3-J. no Domiciliado'],['4','4-No Residente'], ['5','5-Excluido de Libros'], ['0','0-Inactivo']], columnWidth: 0.35 }
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield',   fieldLabel: 'Nombre Fiscal', labelWidth:120, name: 'nomfis', allowBlank: true, columnWidth : 0.90 },
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield',   fieldLabel: 'Direccion', labelWidth:60, name: 'direc1',   allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'numberfield', fieldLabel: 'Retencion', labelWidth:80, name: 'reteiva',  hideTrigger: true, fieldStyle: 'text-align: right', width:130,renderer : Ext.util.Format.numberRenderer('00.00') },
									{ xtype: 'textfield',   fieldLabel: '.',         labelWidth:60, name: 'direc2',   allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'textfield',   fieldLabel: '.',         labelWidth:60, name: 'direc3',   allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'combo',       fieldLabel: 'Banco 1',   labelWidth:60, name: 'banco1',   store: [".$bancos."], columnWidth: 0.45 },
									{ xtype: 'textfield',   fieldLabel: 'Telefono',  labelWidth:60, name: 'telefono', allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'textfield',   fieldLabel: 'Cuenta 1',  labelWidth:60, name: 'cuenta1',  allowBlank: true, columnWidth : 0.45 },
									{ xtype: 'textfield',   fieldLabel: 'Email',     labelWidth:60, name: 'email',    allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'combo',       fieldLabel: 'Banco 2',   labelWidth:60, name: 'banco2',   store: [".$bancos."], columnWidth: 0.45 },
									{ xtype: 'textfield',   fieldLabel: 'Url',       labelWidth:60, name: 'url',      allowBlank: true, columnWidth : 0.75 },
									{ xtype: 'textfield',   fieldLabel: 'Cuenta 2',  labelWidth:60, name: 'cuenta2',  allowBlank: true, columnWidth : 0.45 },
								]
							},{
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {xtype:'fieldset'  },
								style:'padding:4px',
								items: [
									{
										xtype: 'combo',
										fieldLabel: 'Codigo como Cliente',
										labelWidth:140,
										name: 'cliente',
										id:   'cliente',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: scliStore,
										columnWidth: 0.80
									},{
										xtype: 'combo',
										fieldLabel: 'Cuenta Contable',
										labelWidth:140,
										name: 'cuenta',
										id:   'cuenta',
										mode: 'remote',
										hideTrigger: true,
										typeAhead: true,
										forceSelection: true,										valueField: 'item',
										displayField: 'valor',
										store: cplaStore,
										columnWidth: 0.80
									}
								]
							}
						], 
						dockedItems: [
							{ xtype: 'toolbar', dock: 'bottom', ui: 'footer', 
							items: ['->', 
								{ itemId: 'seniat', text: 'SENIAT',   scope: this, handler: this.onSeniat },
								{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
								{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
							]
						}]
					});
					this.callParent();
				},
				setActiveRecord: function(record){
					this.activeRecord = record;
				},
				onSave: function(){
					var form = this.getForm();
					if (!registro) {
						if (form.isValid()) {
							storeSprv.insert(0, form.getValues());
							alert('meco 5');
						} else {
							Ext.Msg.alert('Forma Invalida','Algunos campos no pudieron ser validados<br>los mismos se indican con un cuadro rojo<br> corrijalos y vuelva a intentar');
							return;
						}
					} else {
						var active = win.activeRecord;
						if (!active) {
							Ext.Msg.Alert('Registro Inactivo ');
							return;
						}
						if (form.isValid()) {
							form.updateRecord(active);
						} else {
							Ext.Msg.alert('Forma Invalida','Algunos campos no pudieron ser validados<br>los mismos se indican con un cuadro rojo<br> corrijalos y vuelva a intentar');
							return;
						}
					}
					form.reset();
					this.onReset();
				},
				onReset: function(){
					this.setActiveRecord(null);
					storeSprv.load();
					//Hide Windows 
					win.hide();
				},
				onClose: function(){
					var form = this.getForm();
					form.reset();
					this.onReset();
				},
				onSeniat: function(){
					var form = this.getForm();
					var vrif = form.findField('rif').value;
					if(vrif.length==0){
						alert('Debe introducir primero un RIF');
					}else{
						vrif = vrif.toUpperCase();
						window.open(\"".$consulrif."\"+\"?p_rif=\"+vrif,\"CONSULRIF\",\"height=350,width=410\");
				}


				}
			
			});

			win = Ext.widget('window', {
				closable: false,
				closeAction: 'destroy',
				width: 650,
				height: 470,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						
						if (registro) {
							mcliente = registro.data.cliente;
							mcuenta  = registro.data.cuenta;
							cplaStore.proxy.extraParams.cuenta   = mcuenta ;
							scliStore.proxy.extraParams.cliente = mcliente ;
							cplaStore.load({ params: { 'cliente': registro.data.cliente, 'origen': 'beforeform' } });
							scliStore.load({ params: { 'cuenta':  registro.data.cuenta,  'origen': 'beforeform' } });
							form.loadRecord(registro);
							form.findField('proveed').setReadOnly(true);
						} else {
							form.findField('proveed').setReadOnly(false);
							mcliente = '';
							mcuenta  = '';
						}
					}
				}
			});
		}
		win.show();
	}

	//Filters
	var filters = {
		ftype: 'filters',
		// encode and local configuration options defined previously for easier reuse
		encode: 'json', 
		local: false
	};    

	// Create Grid 
	Ext.define('SprvGrid', {
		extend: 'Ext.grid.Panel',
		alias: 'widget.writergrid',
		store: storeSprv,
		initComponent: function(){
			Ext.apply(this, {
				iconCls: 'icon-grid',
				frame: true,
				dockedItems: [{
					xtype: 'toolbar',
					items: [
						{iconCls: 'icon-add',    text: 'Agregar',                                     scope: this, handler: this.onAddClick   },
						{iconCls: 'icon-update', text: 'Modificar', disabled: true, itemId: 'update', scope: this, handler: this.onUpdateClick},
						{iconCls: 'icon-delete', text: 'Eliminar',  disabled: true, itemId: 'delete', scope: this, handler: this.onDeleteClick }
					]
				}],
				columns: colSprv,
				// paging bar on the bottom
				bbar: Ext.create('Ext.PagingToolbar', {
					store: storeSprv,
					displayInfo: true,
					displayMsg: 'Pag No. {0} - Registros {1} de {2}',
					emptyMsg: \"No se encontraron Registros.\"
				})
			});
			this.callParent();
			this.getSelectionModel().on('selectionchange', this.onSelectChange, this);
		},
		features: [{ ftype: 'grouping', groupHeaderTpl: '{name} ' }, filters],
		onSelectChange: function(selModel, selections){
			this.down('#delete').setDisabled(selections.length === 0);
			this.down('#update').setDisabled(selections.length === 0);
			},
		
		onUpdateClick: function(){
			var selection = this.getView().getSelectionModel().getSelection()[0];
				if (selection) {
					registro = selection;
					showContactForm();
				}
			},
		onDeleteClick: function() {
			var selection = this.getView().getSelectionModel().getSelection()[0];
			Ext.MessageBox.show({
				title: 'Confirme', 
				msg: 'Esta seguro?', 
				buttons: Ext.MessageBox.YESNO, 
				fn: function(btn){ 
					if (btn == 'yes') { 
						if (selection) {
							storeSprv.remove(selection);
						}
						storeSprv.load();
					} 
				}, 
				icon: Ext.MessageBox.QUESTION 
			});  
		},
		onAddClick: function(){
			registro = null;
			showContactForm();
			storeSprv.load();
		}
	});

//////************ MENU DE ADICIONALES /////////////////
".$listados."

".$otros."
//////************ FIN DE ADICIONALES /////////////////


	Ext.create('Ext.Viewport', {
		layout: {type: 'border',padding: 5},
		defaults: { split: true	},
		items: [
			{
				region: 'north',
				preventHeader: true,
				height: 40,
				minHeight: 40,
				html: '".$encabeza."'
			},{
				region:'west',
				width:200,
				border:false,
				autoScroll:true,
				title:'Lista de Opciones',
				collapsible:true,
				split:true,
				collapseMode:'mini',
				layoutConfig:{animate:true},
				layout: 'accordion',
				items: [
					{
						title:'Listados',
						border:false,
						layout: 'fit',
						items: gridListado
					},{
						title:'Otras Funciones',
						border:false,
						layout: 'fit',
						items: gridOtros
					}
				]
			},{
				region: 'center',
				itemId: 'grid',
				xtype: 'writergrid',
				title: 'Proveedores',
				width: '98%',
				align: 'center',
			}
		]
	});
	storeSprv.load({ params: { start:0, limit: 30}});
});

</script>
";
		return $script;	
	}

	function instalar(){

		if (!$this->datasis->iscampo('sprv','id')) {
			$this->db->simple_query('ALTER TABLE `sprv` DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE `sprv` ADD id INT AUTO_INCREMENT PRIMARY KEY');
		}
		if (!$this->datasis->iscampo('sprv','copre')) 
			$this->db->simple_query('ALTER TABLE sprv ADD copre VARCHAR(11) DEFAULT NULL NULL AFTER cuenta ');
		
		if (!$this->datasis->iscampo('sprv','ocompra')) 
			$this->db->simple_query('ALTER TABLE sprv ADD ocompra CHAR(1) DEFAULT NULL NULL AFTER copre ');
		
		if (!$this->datasis->iscampo('sprv','dcredito')) 
			$this->db->simple_query('ALTER TABLE sprv ADD dcredito DECIMAL(3,0) DEFAULT "0" NULL AFTER ocompra ');
			
		if (!$this->datasis->iscampo('sprv','despacho')) 
			$this->db->simple_query('ALTER TABLE sprv ADD despacho DECIMAL(3,0) DEFAULT NULL NULL AFTER dcredito ');

		if (!$this->datasis->iscampo('sprv','visita')) 
			$this->db->simple_query('ALTER TABLE sprv ADD visita VARCHAR(9) DEFAULT NULL NULL AFTER despacho ');

		if (!$this->datasis->iscampo('sprv','cate')) 
			$this->db->simple_query('ALTER TABLE sprv ADD cate VARCHAR(20) NULL AFTER visita ');

		if (!$this->datasis->iscampo('sprv','reteiva')) 
			$this->db->simple_query('ALTER TABLE sprv ADD reteiva DECIMAL(7,2) DEFAULT "0.00" NULL AFTER cate ');

		if (!$this->datasis->iscampo('sprv','ncorto')) 
			$this->db->simple_query('ALTER TABLE sprv ADD ncorto VARCHAR(20) DEFAULT NULL NULL AFTER nombre ');

		$this->db->simple_query('ALTER TABLE sprv CHANGE direc1 direc1 VARCHAR(105) DEFAULT NULL NULL');
		$this->db->simple_query('ALTER TABLE sprv CHANGE direc2 direc2 VARCHAR(105) DEFAULT NULL NULL ');
		$this->db->simple_query('ALTER TABLE sprv CHANGE direc3 direc3 VARCHAR(105) DEFAULT NULL NULL ');
		$this->db->simple_query('ALTER TABLE sprv CHANGE nombre nombre VARCHAR(60) DEFAULT NULL NULL ');
		$this->db->simple_query('ALTER TABLE sprv CHANGE nomfis nomfis VARCHAR(200) DEFAULT NULL NULL  ');
	}

	function sprvbusca() {
		$start    = isset($_REQUEST['start'])   ? $_REQUEST['start']  :  0;
		$limit    = isset($_REQUEST['limit'])   ? $_REQUEST['limit']  : 25;
		$proveed  = isset($_REQUEST['proveed']) ? $_REQUEST['proveed']: '';
		$semilla  = isset($_REQUEST['query'])   ? $_REQUEST['query']  : '';

		$semilla = trim($semilla);
	
		$mSQL = "SELECT proveed item, CONCAT(proveed, ' ', nombre) valor FROM sprv WHERE tipo<>'0' ";
		if ( strlen($semilla)>0 ){
			$mSQL .= " AND ( proveed LIKE '$semilla%' OR nombre LIKE '%$semilla%' OR  rif LIKE '%$semilla%') ";
		} else {
			if ( strlen($proveed)>0 ) $mSQL .= " AND (proveed LIKE '$proveed%' OR nombre LIKE '%$proveed%' OR  rifci LIKE '%$proveed%') ";
		}
		$mSQL .= "ORDER BY nombre ";
		$results = $this->db->count_all('scli');

		if ( empty($mSQL)) {
			echo '{success:true, message:"mSQL vacio, Loaded data", results: 0, data:'.json_encode(array()).'}';
		} else {
			$mSQL .= " limit $start, $limit ";
			$query = $this->db->query($mSQL);
			$arr = array();
			foreach ($query->result_array() as $row)
			{
				$meco = array();
				foreach( $row as $idd=>$campo ) {
					$meco[$idd] = utf8_encode($campo);
				}
				$arr[] = $meco;
			}
			echo '{success:true, message:"proveedores", results:'. $results.', data:'.json_encode($arr).'}';
		}
	}

}
?>
