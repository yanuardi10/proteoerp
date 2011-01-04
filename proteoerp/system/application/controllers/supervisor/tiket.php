<?php
class Tiket extends Controller {

	var $estado;
	var $prioridad;
	var $modulo;

	function Tiket(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->library("menues");
		$this->modulo=908;
		$this->estado=array(
		 "N"=>"Nuevo",
     "P"=>"Pendiente",
     "R"=>"Resueltos",
     "C"=>"Cerrado");

		$this->prioridad=array(
		 "1"=>"Muy Alta",
     "2"=>"Alta",
     "3"=>"Media",
     "4"=>"Baja",
     "5"=>"Muy baja");
	}

	function index(){
		redirect("supervisor/tiket/filteredgrid");
	}

	function filteredgrid(){
		$this->datasis->modulo_id($this->modulo,1);
		$this->rapyd->load("datafilter","datagrid");
 		$modbus=array(
			'tabla'   =>'usuario',
			'columnas'=>array(
				'us_codigo' =>'C&oacute;digo',
				'us_nombre'=>'Nombre'),
			'filtro'  =>array('us_nombre'=>'nombre'),
			'retornar'=>array('us_codigo'=>'usuario'),
			'titulo'  =>'Buscar Usuario');

		$filter = new DataFilter("Filtro de Tikets");

		$filter->id = new inputField("N&uacute;mero", "id");
		$filter->id->size=11;

		$filter->estampa = new dateonlyField("Fecha", "estampa");
		$filter->estampa->clause  ="where";
		$filter->estampa->operator="=";
		//$filter->estampa->insertValue = date("Y-m-d");

		$filter->estado = new dropdownField("Estado", "estado");
		$filter->estado->option("","Todos");
		$filter->estado->options($this->estado);

		$filter->prioridad = new dropdownField("Prioridad", "prioridad");
		$filter->prioridad->option("","Todos");
		$filter->prioridad->options($this->prioridad);

		$filter->usuario = new inputField("C&oacute;digo de usuario", "usuario");
		$filter->usuario->size=11;
		$filter->usuario->append($this->datasis->modbus($modbus));

		$filter->contenido = new inputField("Contenido", "contenido");
		//$filter->contenido->clause ="likesensitive";
		//$filter->contenido->append("Sencible a las Mayusc&uacute;las");

		$filter->buttons("reset","search");

		$select=array("usuario","contenido","prioridad","IF(estado='N','Nuevo',IF(estado='R','Resuelto',IF(estado='P','Pendiente','Cerrado')))as estado","estampa","id","actualizado");		
		$filter->db->select($select);
		$filter->db->from('tiket');
		$filter->db->orderby('estampa','desc');
		$filter->db->where('padre',"S");
		$filter->build();

		$grid = new DataGrid("Lista de Tikets");
		$grid->per_page = 10;
		$link=anchor("supervisor/tiket/ver/<#id#>", "<#id#>");

		$grid->column("N&uacute;mero",$link);
		$grid->column("Fecha de Ingreso","<dbdate_to_human><#estampa#>|d/m/Y h:m:s</dbdate_to_human>");
		$grid->column_orderby("Actualizado","<dbdate_to_human><#actualizado#>|d/m/Y h:m:s</dbdate_to_human>","actualizado");
		//$grid->column("Actualizado","<dbdate_to_human><#actualizado#>|d/m/Y h:m:s</dbdate_to_human>");
		$grid->column("Usuario","usuario");
		$grid->column("Contenido","contenido");
		$grid->column("Prioridad","prioridad");
		$grid->column("Estado","estado");

		$grid->add("supervisor/tiket/dataedit/create");
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Control de Tikets</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$parametros = $this->uri->uri_to_assoc(4);
		$this->datasis->modulo_id($this->modulo,1);
		$this->rapyd->load("dataedit");

		$edit = new DataEdit("Tiket", "tiket");
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('delete','_pre_del');
		$edit->post_process("insert","_post_insert");
		$edit->post_process("update","_post_update");
		$edit->post_process("delete","_post_del");

		$edit->contenido = new textareaField("Contenido", "contenido");
		$edit->contenido->rule = "required";
		$edit->contenido->rows = 6;
		$edit->contenido->cols = 90;

		$edit->padre = new inputField(" ", "padre");
		$edit->padre->style='display: none;';
		$edit->padre->type='hidden';
		$edit->padre->when= array("create");

		if(!array_key_exists('pertenece',$parametros)) {

			//$edit->back_url = site_url("supervisor/tiket/filteredgrid");
			$edit->back_uri="supervisor/tiket/filteredgrid";
			$edit->padre->insertValue='S';

			$edit->prioridad = new dropdownField("Prioridad", "prioridad");
			$edit->prioridad->options($this->prioridad);
			$edit->prioridad->insertValue=5;

			$edit->estado = new inputField(" ", "estado");
			$edit->estado->style='display: none;';
			$edit->estado->type='hidden';
			$edit->estado->when= array("create");
			$edit->estado->insertValue='N';
		}else{
			//$edit->back_url = site_url("supervisor/tiket/ver/").$parametros['pertenece'];
			$edit->back_uri="supervisor/tiket/ver/".$parametros['pertenece'];
			$edit->padre->insertValue='N';

			$edit->pertenece = new inputField(" ", "pertenece");
			$edit->pertenece->style='display: none;';
			$edit->pertenece->type='hidden';
			$edit->pertenece->when= array("create");
			$edit->pertenece->insertValue=$parametros['pertenece'];
		}

		$edit->buttons("modify", "save", "undo", "delete",'back');
		$edit->build();

		$data['content'] =$edit->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Crear Tiket</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function estapriori($status,$id=NULL){
		$this->rapyd->load("dataedit");
		$this->datasis->modulo_id($this->modulo,1);

		$edit = new DataEdit("Tiket", "tiket");
		$edit->post_process("update","_post_update");
		$edit->back_url = site_url("supervisor/tiket/ver/$id");

		$edit->prioridad = new dropdownField("Prioridad", "prioridad");
		$edit->prioridad->options($this->prioridad);

		$edit->estado = new dropdownField("Estado", "estado");
		$edit->estado->options($this->estado);

		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();

		$data['content'] =$edit->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1>Cambiar estado o prioridad</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function ver($id=NULL){
		$this->datasis->modulo_id($this->modulo,1);
		if(empty($id)) redirect("supervisor/tiket/filteredgrid");
		$this->rapyd->load("datatable");
		$query = $this->db->query("SELECT prioridad,estado FROM tiket WHERE $id=$id");
		$estado=$prioridad='';
		if ($query->num_rows() > 0){
			$row = $query->row();
			$prioridad = $row->prioridad;
			$estado    = $row->estado;
		}
		$link=($this->datasis->puede(908001))? anchor('/supervisor/tiket/dataedit/delete/<#id#>','borrar'):'';

		$table = new DataTable(null);
		$table->cell_attributes = 'style="vertical-align:middle;"';
		$select=array("usuario","contenido","prioridad","estado","estampa","id","padre","pertenece");

		$table->db->select($select);
		//$table->db->select("usuario,contenido,prioridad,estado,estampa,id,padre,pertenece");
		$table->db->from("tiket");
		//$table->db->where("id",$id or 'pertenece',$id);
		$table->db->where('id',$id);
		$table->db->or_where('pertenece',$id);
		$table->db->orderby("id");
		$this->db->_escape_char='';
		$this->db->_protect_identifiers=false;

		$table->per_row  = 1;
		$table->per_page = 50;
		$table->cell_template = "<div class='marco1' ><#contenido#><br><b class='mininegro'>&nbsp;<dbdate_to_human><#estampa#>|d/m/Y h:m:s</dbdate_to_human> Usuario: <#usuario#> $link</b></div><br>";
		$table->build();
		//echo $table->db->last_query();

		$prop=array('type'=>'button','value'=>'Agregar','name'=>'mas'  ,'onclick' => "javascript:window.location='".site_url("supervisor/tiket/dataedit/pertenece/$id/create")."'");
		$form=form_input($prop);

		$prop2=array('type'=>'button','value'=>'Cambiar estado o prioridad','name'=>'mas'  ,'onclick' => "javascript:window.location='".site_url("supervisor/tiket/estapriori/modify/$id")."'");
		$form2=form_input($prop2);

		$prop3=array('type'=>'button','value'=>'Regresar','name'=>'mas'  ,'onclick' => "javascript:window.location='".site_url("supervisor/tiket/filteredgrid")."'");
		$form3=form_input($prop3);

		$data['content'] = $table->output.$form.$form2.$form3;
		$data["head"]    = $this->rapyd->get_head().style('marcos.css').style('estilos.css');
		$data['title']   = "<h1>Tiket N&uacute;mero: $id</h1> Prioridad: <b>".$this->prioridad[$prioridad]."</b>, Estado: <b>".$this->estado[$estado]."</b><br>";
		$this->load->view('view_ventanas', $data);
	}

	function _post_insert($do) {
		$pertenece=$do->get('pertenece');
		$mSQL="UPDATE tiket SET estado='P', actualizado=NOW() WHERE id=$pertenece";
		$this->db->simple_query($mSQL);
	}

	function _post_update($do) {
		$pertenece=$do->get('pertenece');
		if(empty($pertenece)) $pertenece=$do->get('id');
		$mSQL="UPDATE tiket SET actualizado=NOW() WHERE id=$pertenece";
		$this->db->simple_query($mSQL);
	}

	function _pre_del($do) {
		$retorno=$this->datasis->puede(908001);
		return $retorno;
	}

	function _pre_insert($do) {
		$do->set('usuario', $this->session->userdata('usuario'));
	}

	function _post_del($do){
		$numero=$do->get('id');
		$sql = "DELETE FROM tiket WHERE pertenece=$numero";
		$this->db->query($sql);
	}

	function traertiket($codigoc=null){
		//$this->datasis->modulo_id($this->modulo,1);
		//$this->load->helper('url');
		if(empty($codigoc)){
			$where='';
		}else{
			$where="WHERE cliente=".$this->db->escape($codigoc);
		}
		$mSQL="SELECT cliente,url,sistema,id FROM tiketconec ".$where;
		$host=$this->db->query($mSQL);
		foreach($host->result() as  $row){
			
			if(!empty($row->sistema)) $ruta=trim_slashes($row->sistema.'/rpcserver'); else $ruta='rpcserver';
			if(!empty($row->phtml))   $url=trim_slashes($row->url).':'.$row->phtml ; else $url=trim_slashes($row->url);
			$sucursal=$row->id;
			$cliente=$row->cliente;
			
			$server_url =$url.'/'.reduce_double_slashes($ruta);

			//$server_url = site_url('rpcserver');
			echo '<pre>'."\n";
			echo '('.$row->cliente.')-'.$server_url."\n";

			$fechad=$this->datasis->dameval('SELECT MAX(a.estampa) FROM tiketc AS a JOIN tiketconec AS b  ON a.sucursal=b.id  WHERE b.cliente='.$this->db->escape($cliente));
			if(empty($fechad)) $fechad=date('Ymd');
			echo $this->_traeticketrpc($server_url,array($fechad),$row->id);
			echo '</pre>'."\n";
		}
			$link=anchor("supervisor/conec/filteredgrid", "Regresar a Información de Conexión");
			echo $link."\n";
	}


	function traer(){
		$this->datasis->modulo_id($this->modulo,1);
		//$this->datasis->modulo_id(11D,1);
		$this->rapyd->load("dataform","datatable",'datagrid');
		$this->load->library('table');

		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cliente'),
		'titulo'  =>'Buscar Cliente');

		$boton=$this->datasis->modbus($scli);

		$filter = new DataForm('supervisor/tiketrpc/tiket/process');
		$filter->title('Filtro de fecha');

		//$filter->fechad = new dateonlyField("Fecha Desde", "fechad",'Ymd');
		//$filter->fechad->insertValue = date("Y-m-d");
		//$filter->fechad->size=12;

		$filter->cliente = new inputField("Cliente", "cliente");
		$filter->cliente->size = 15;
		$filter->cliente->append($boton);

		//$filter->button("btnsubmit", "Consultar", '', $position="BL");
		$filter->button("btnsubmit", "Traer Tikets", form2uri(site_url("/supervisor/tiket/traertiket"),array('cliente')), $position="BL");//
		//$filter->button("btnsubmit", "Traer Tikets", form2uri(site_url("/supervisor/tiket/traertiket"), $position="BL");//
		$filter->build_form();

		$data=array();
		$mSQL="SELECT a.id,a.cliente,a.ubicacion,a.url,a.basededato,a.puerto,a.usuario,a.clave,a.observacion, b.nombre FROM tiketconec AS a JOIN scli AS b ON a.cliente=b.cliente WHERE url REGEXP '^([[:alnum:]]+\.{0,1})+$' ORDER BY id";

		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			foreach ($query->result_array() as $row){
				$data[]=$row;
			}
		}
		$grid = new DataGrid("Clientes",$data);

		$grid->column("Cliente"    , '<b><#nombre#></b>');
		$grid->column("URL"        , 'url');

		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Traer tikets de clientes</h1>";
		$data["head"]    = $this->rapyd->get_head().script("jquery.js").script("plugins/jquery.json.min.js");
		$this->load->view('view_ventanas', $data);
	}

	function _traeticketrpc($server_url,$parametros,$sucursal='N/A'){
		$this->load->library('xmlrpc');
		$this->xmlrpc->xmlrpc_defencoding=$this->config->item('charset');

		$this->xmlrpc->server($server_url, 80);
		$this->xmlrpc->method('ttiket');

		$request = $parametros;
		$this->xmlrpc->request($request);

		$error=0;
		if (!$this->xmlrpc->send_request()){
			$rt=$this->xmlrpc->display_error();
		}else{
			$respuesta=$this->xmlrpc->display_response();
			foreach($respuesta AS $res){
				$arr=unserialize($res);
				foreach($arr AS $i=>$val)
				    $arr[$i]=base64_decode($val);
				$arr['idt']       =$arr['id'];
				$arr['sucursal']  =$sucursal;
				//$arr['asignacion']='KATHI';
				unset($arr['id']);

				$mSQL = $this->db->insert_string('tiketc', $arr);
				$rt=$this->db->simple_query($mSQL);
				if($rt===FALSE){ $error++; memowrite($mSQL,'tiketc');}
			}
			if($error==0) $rt='Transferencia Correcta'; else $rt='Hubo algunos problemas en la insercion se genero un centinela';
		}
		return $rt;
	}

	function instalar(){
		$mSQL="CREATE TABLE `tiket` (
		  `id` bigint(20) unsigned NOT NULL auto_increment,
		  `padre` char(1) default NULL,
		  `pertenece` bigint(20) unsigned default NULL,
		  `prioridad` smallint(5) unsigned default NULL,
		  `usuario` varchar(50) default NULL,
		  `contenido` text,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		  `actualizado` timestamp NULL default NULL,
		  `estado` char(1) default 'N',
		  PRIMARY KEY  (`id`),
		  KEY `id` (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}
}
?>
