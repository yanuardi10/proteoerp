<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Accesos extends validaciones{
	var $_direccion;

	function Accesos(){
		parent::Controller(); 
		$this->load->library('rapyd');
		$this->db->simple_query('UPDATE pers SET codigo=TRIM(codigo)');
	}

	function index(){
		$this->datasis->modulo_id(709,1);
		redirect('nomina/accesos/filteredgrid');
	}

	function filteredgrid(){
		$protocolo=explode('/',$_SERVER['SERVER_PROTOCOL']);
		$this->_direccion=$protocolo[0].'://'.$_SERVER['SERVER_NAME'].'/fnomina';

		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();
		$atts = array(
		    'width'      => '400',
		    'height'     => '320',
		    'scrollbars' => 'yes',
		    'status'     => 'yes',
		    'resizable'  => 'yes',
		    'screenx'    => '10',
		    'screeny'    => '10'
		);

		$script='$(".inputnum").numeric(".");';

		$filter = new DataFilter("Filtro por C&oacute;digo");

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->db_name='cacc.codigo';
		$filter->codigo->size = 15; 

		$filter->cedula = new inputField("Cedula","cedula");
		$filter->cedula->db_name='pers.cedula';
		$filter->cedula->size = 15;

		$filter->nombre = new inputField("Nombres","nombre");
		$filter->nombre->db_name='pers.nombre';
		$filter->nombre->size = 25;

		$filter->fechad = new dateonlyField("FECHA Desde-Hasta", "fechad",'d/m/Y');
		$filter->fechad->clause  ="where";
		$filter->fechad->db_name ="fecha";
		$filter->fechad->operator=">=";
		$filter->fechad->dbformat='Ymd';
		$filter->fechad->size    = 15;

		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechah->clause="where";
		$filter->fechah->db_name="fecha";
		$filter->fechah->operator="<=";
		$filter->fechah->group = "FECHA";
		$filter->fechah->dbformat='Ymd';
		$filter->fechah->size    = 15;
		$filter->fechah->in      = "fechad";

		$filter->horad = new inputField("HORA Desde - Hasta", "horad");
		$filter->horad->clause   = "where";
		$filter->horad->db_name  = "CONCAT(MID(cacc.hora,1,2),MID(cacc.hora,4,2))";
		$filter->horad->operator = ">=";
		//$filter->horad->group    = "HORA";
		$filter->horad->css_class='inputnum';
		$filter->horad->size     = 3;
		$filter->horad->maxlength= 4;

		$filter->horah = new inputField("Hasta", "horahd");
		$filter->horah->clause   = "where";
		$filter->horah->db_name  = "CONCAT(MID(cacc.hora,1,2),MID(cacc.hora,4,2))";
		$filter->horah->operator = "<=";
		$filter->horah->group    = "HORA";
		$filter->horah->css_class='inputnum';
		$filter->horah->size     = 3;
		$filter->horah->maxlength= 4;
		$filter->horah->in       = "horad";
		$filter->horah->append("Inserte la hora en formato HH  MM (18 00)");

		$filter->buttons("reset","search");
		$filter->build();

		$ima=$this->_direccion.'/<#archivo#>';

		$furi = site_url('/nomina/accesos/foto/<#archivo#>');
		$uri  = anchor('nomina/accesos/dataedit/show/<#codigo#>/<#fecha#>/<#hora#>','<#codigo#>');
		$grid = new DataGrid('Lista de Control de Accesos');

		$select=array("cacc.codigo","fecha","cacc.hora","cacc.cedula","CONCAT(cacc.codigo,DATE_FORMAT(fecha,'-%Y%m%d'),DATE_FORMAT(cacc.hora,'%H%i%s'),'.jpg') AS archivo",
		"CONCAT_WS('-',pers.nacional,pers.cedula) AS ci, nombre, apellido");
		$grid->db->select($select);
		$grid->db->from('cacc');
		$grid->db->join('pers','cacc.codigo = pers.codigo');

		$grid->db->orderby('fecha','desc');
		$grid->db->orderby('hora','asc');

		$grid->per_page = 7;
		$grid->column("C&oacute;digo",$uri);
		$grid->column("C&eacute;dula","cedula");
		$grid->column("Nombres","nombre");
		$grid->column("Apellidos","apellido");
		$grid->column("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'align="center"');
		$grid->column("Hora",'hora','align="center"');
		$grid->column("Foto",anchor_popup($ima,"<img src='$ima' width='100' border='0'/>",$atts),'align="center"');
		$grid->add("nomina/accesos/dataedit/create");
		$grid->build();

		//echo $grid->db->last_query();
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Control de Accesos</h1>";
		$data['head']    = $this->rapyd->get_head().script('jquery.js').script("plugins/jquery.numeric.pack.js").script("plugins/jquery.json.min.js");
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Accesos', 'cacc');
		$edit->back_url = site_url('nomina/accesos/filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->post_process('insert','_pre_insert');
		$edit->post_process('update','_pre_update');

		$pers=array(
		'tabla'   =>'pers',
		'columnas'=>array(
		'codigo'  =>'Codigo',
		'cedula'  =>'Cedula',
		'nombre'  =>'Nombre',
		'apellido' =>'Apellido'),
		'filtro'  =>array('codigo'=>'C&oacute;digo','cedula'=>'Cedula'),
		'retornar'=>array('codigo'=>'codigo'),
		'titulo'  =>'Buscar Personal');

		$boton=$this->datasis->modbus($pers);

		$edit->codigo   = new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->rule='trim';
		$edit->codigo->mode='autohide';
		$edit->codigo->maxlength =15;
		$edit->codigo->size =15;
		$edit->codigo->append($boton);
		$edit->codigo->rule = "required|callback_chexiste";

		$edit->nacional = new dropdownField("Nacionalidad", "nacional");
		$edit->nacional->style = "width:110px;";
		$edit->nacional->option("V","Venezolano");
		$edit->nacional->option("E","Extranjero");

		$edit->fecha    = new DateonlyField("Fecha","fecha");
		$edit->fecha->mode="autohide";
		$edit->fecha->size =12;
		$edit->fecha->rule = "required";

		$edit->hora  = new inputField('Hora', 'hora');
		$edit->hora->maxlength=8;
		$edit->hora->size=10;
		$edit->hora->mode='autohide';
		$edit->hora->rule='required|callback_chhora';
		$edit->hora->append('hh:mm:ss');

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Control de Accesos</h1>";
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function foto(){
		//$archivo=$this->uri->segment(4);
		//if (isset($archivo) and file_exists("/usr/samba/fnomina/$archivo")){
		//	Header("Content-type: image/jpeg");
		//	Header("Pragma: No-cache");
		//	readfile("/usr/samba/fnomina/$archivo");
		//}
		//Header("Content-type: image/gif");
		//Header("Pragma: No-cache");
		//$dir=dirname($_SERVER["SCRIPT_FILENAME"]);
		//readfile("$dir/images/ndisp.gif");
	}

	function traecedula($codigo){
		$this->db->escape($codigo);
		return $this->datasis->dameval("SELECT cedula FROM pers WHERE codigo=$codigo");
	}

	function traenacional($codigo){
		$this->db->escape($codigo);
		return $this->datasis->dameval("SELECT nacional FROM pers WHERE codigo=$codigo");
	}

	function _pre_insert($do){
		$codigo=$do->get('codigo');
		$do->set('cedula'  ,$this->traecedula($codigo));
		$do->set('nacional',$this->traenacional($codigo));
		$do->set('manual','S');
	}

	function _pre_update($do){
		$codigo=$do->get('codigo');
		$do->set('cedula',$this->traecedula($codigo));
		$do->set('nacional',$this->traenacional($codigo));
	}

	function _post_insert($do){
		$codigo=$do->get('codigo');
		logusu('cacc',"ACCESO PARA $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('codigo');
		logusu('cacc',"ACCESO PARA $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		logusu('cacc',"ACCESO PARA $codigo ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('codigo');
		$fecha=human_to_dbdate($this->input->post('fecha'));
		$hora=$this->input->post('hora');

		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM cacc WHERE codigo='$codigo' AND fecha='$fecha' AND hora='$hora'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT cedula FROM cacc WHERE codigo='$codigo' AND fecha='$fecha' AND hora='$hora'");
			$this->validation->set_message('chexiste',"Acceso para $nombre CODIGO $codigo FECHA $fecha HORA $hora ya existe");
			return false;
		}else {
			return true;
		}	
	}

	function instalar(){
		if (!$this->db->field_exists('manual', 'cacc')){
			$query="ALTER TABLE `cacc`  ADD COLUMN `manual` CHAR(1) NOT NULL DEFAULT 'N' AFTER `hora`";
			$this->db->simple_query($query);
		}
	}
}