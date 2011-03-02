<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Scli extends validaciones {

	function scli(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('pi18n');
		//$this->load->library("menues");
		$this->datasis->modulo_id(131,1);
		$this->load->database();
	}

	function index(){
		redirect('ventas/scli/filteredgrid');
	}

	function filteredgrid(){
		$this->pi18n->cargar('scli','filteredgrid');
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Clientes', 'scli');

		$filter->cliente = new inputField('C&oacute;digo', 'cliente');
		$filter->cliente->size=10;
		$filter->cliente->group = "CLIENTE";

		$filter->nombre= new inputField('Nombre','nombre');
		$filter->nombre->size=30;
		$filter->nombre->group = "CLIENTE";

		$filter->rifci= new inputField('Rif/CI','rifci');
		$filter->rifci->size=15;
		$filter->rifci->group = "CLIENTE";

		$filter->cuenta= new inputField('Cuenta Contable','cuenta');
		$filter->cuenta->like_side='after';
		$filter->cuenta->size=15;
		$filter->cuenta->group = "VALORES";

		$filter->grupo = new dropdownField('Grupo', 'grupo');
		$filter->grupo->option('','Todos');
		$filter->grupo->options('SELECT grupo, gr_desc FROM grcl ORDER BY gr_desc');
		$filter->grupo->size=20;
		$filter->grupo->group = "VALORES";

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$uri = anchor('ventas/scli/dataedit/show/<#id#>','<#cliente#>');

		$grid = new DataGrid('Lista de Clientes');
		$grid->order_by('nombre','asc');
		$grid->per_page=15;

		$cclave=anchor('ventas/scli/claveedit/modify/<#id#>','Asignar clave');
		$grid->column_orderby('Cliente',$uri,'cliente');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby($this->pi18n->msj('rifci','Rif/CI'),'rifci','rifci');
		$grid->column_orderby($this->pi18n->msj('tiva','Tipo') ,'tiva','tiva','align=\'center\'');
		$grid->column_orderby('Cuenta','cuenta','cuenta');
		$grid->column('Acci&oacute;n',$cclave);
		$grid->add('ventas/scli/dataedit/create','Agregar un cliente');
		$grid->build();
		//echo $grid->db->last_query();

		$data['content'] = $grid->output;
		$data['content'].= $this->pi18n->fallas();
		$data['filtro']  = $filter->output;

		$data['title']   = heading('Modulo de Clientes');
		$data['script']  = script('jquery.js')."\n";
		$data['head']    = $this->rapyd->get_head();
		$data['extras']  = '';
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->pi18n->cargar('scli','dataedit');
		$this->rapyd->load('dataedit');

		$mSCLId=array(
			'tabla'   =>'scli',
			'columnas'=>array(
				'cliente' =>'C&oacute;digo Socio',
				'nombre'=>'Nombre',
				'cirepre'=>'Rif/Cedula',
				'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Socio','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'socio'),
			'titulo'  =>'Buscar Socio');

		$qformato=$this->datasis->formato_cpla();

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

		$boton =$this->datasis->modbus($mSCLId);
		$bcpla =$this->datasis->modbus($mCPLA);

		$smenu['link']=barra_menu('131');
		$consulrif=trim($this->datasis->traevalor('CONSULRIF'));
		$lcuenta=site_url('contabilidad/cpla/autocomplete/codigo');
		$lsocio =site_url('ventas/scli/autocomplete/cliente');
		$script ='
		function formato(row) {
			return row[0] + "-" + row[1];
		}

		$(function() {
			$(".inputnum").numeric(".");
			$("#tiva").change(function () { anomfis(); }).change();
			$("#cuenta").autocomplete("'.$lcuenta.'",{
				delay:10,
				//minChars:2,
				matchSubset:1,
				matchContains:1,
				cacheLength:10,
				formatItem:formato,
				width:350,
				autoFill:true
				}
			);

			$("#socio").autocomplete("'.$lsocio.'",{
				delay:10,
				matchSubset:1,
				matchContains:1,
				cacheLength:10,
				formatItem:formato,
				width:350,
				autoFill:true
				}
			);
			//$(":input").enter2tab();
		});

		function anomfis(){
				vtiva=$("#tiva").val();
				if(vtiva=="C" || vtiva=="E" || vtiva=="R"){
					$("#tr_nomfis").show();
					$("#tr_riffis").show();
				}else{
					$("#nomfis").val("");
					$("#riffis").val("");
					$("#tr_nomfis").hide();
					$("#tr_riffis").hide();
				}
		}

		function consulrif(campo){
				vrif=$("#"+campo).val();
				if(vrif.length==0){
					alert("Debe introducir primero un RIF");
				}else{
					vrif=vrif.toUpperCase();
					$("#riffis").val(vrif);
					window.open("'.$consulrif.'"+"?p_rif="+vrif,"CONSULRIF","height=350,width=410");
				}
		}';

		$edit = new DataEdit('Clientes', 'scli');
		$edit->back_url = site_url('ventas/scli/filteredgrid');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');

		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->cliente = new inputField('C&oacute;digo', 'cliente');
		$edit->cliente->rule = 'trim|strtoupper|required|callback_chexiste';
		$edit->cliente->mode = 'autohide';
		$edit->cliente->size = 9;
		$edit->cliente->maxlength = 5;

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rule = 'trim|strtoupper|required';
		$edit->nombre->size = 60;
		$edit->nombre->maxlength = 45;

		$edit->contacto = new inputField('Contacto', 'contacto');
		$edit->contacto->rule = 'trim';
		$edit->contacto->size = 60;
		$edit->contacto->maxlength = 40;

		$edit->grupo = new dropdownField('Grupo', 'grupo');
		$edit->grupo->option('','Seleccione un grupo');
		$edit->grupo->options('SELECT grupo, gr_desc FROM grcl ORDER BY gr_desc');
		$edit->grupo->rule = 'required';
		$edit->grupo->size = 6;
		$edit->grupo->maxlength = 4;

		$lriffis='<a href="javascript:consulrif(\'rifci\');" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->rifci = new inputField($this->pi18n->msj('rifci','RIF o Cedula de Identidad'), 'rifci');
		$edit->rifci->rule = 'trim|strtoupper|required|callback_chci';
		$edit->rifci->maxlength =13;
		$edit->rifci->append($lriffis);
		$edit->rifci->size =18;

		for($i=1;$i<=2;$i++){
			for($o=1;$o<=2;$o++){
				$obj  ="dire$i$o";
				$label= ($o%2!=0) ? 'Direcci&oacute;n': '&nbsp;&nbsp;Continuaci&oacute;n';
				$edit->$obj = new inputField($label,$obj);
				$edit->$obj->rule = 'trim';
				$edit->$obj->size      = 60;
				$edit->$obj->maxlength = 40;
				$edit->$obj->group = "Direcci&oacute;n ($i)";
			}
			$obj="ciudad$i";
			$edit->$obj = new dropdownField('Ciudad',$obj);
			$edit->$obj->rule = 'trim';
			$edit->$obj->option('','Seleccionar');
			$edit->$obj->options('SELECT ciudad codigo, ciudad FROM ciud ORDER BY ciudad');
			$edit->$obj->maxlength = 40;
			$edit->$obj->size      = 60;
			$edit->$obj->group = "Direcci&oacute;n ($i)";
		}

		$edit->repre  = new inputField('Representante Legal', 'repre');
		$edit->repre->rule = 'trim';
		$edit->repre->maxlength =30;
		$edit->repre->size = 40;

		$edit->cirepre = new inputField('C&eacute;dula de Rep.', 'cirepre');
		$edit->cirepre->rule = 'trim|strtoupper|callback_chci';
		$edit->cirepre->maxlength =13;
		$edit->cirepre->size = 16;

		$edit->socio = new inputField('Socio del cliente', 'socio');
		$edit->socio->rule = 'trim';
		$edit->socio->size = 8;
		$edit->socio->maxlength =5;
		$edit->socio->append($boton);

		$arr_tiva=$this->pi18n->arr_msj('tivaarr','C=Contribuyente,N=No Contribuyente,E=Especial,R=Regimen Exento,O=Otro');
		$edit->tiva = new dropdownField('Condici&oacute;n F&iacute;scal', 'tiva');
		$edit->tiva->option('','Seleccionar');
		$edit->tiva->options($arr_tiva);
		$edit->tiva->style = 'width:140px';
		$edit->tiva->rule='required|callback_chdfiscal';
		$edit->tiva->group = 'Informaci&oacute;n f&iacute;scal';

		$edit->nomfis = new inputField('Nombre F&iacute;scal', 'nomfis');
		$edit->nomfis->rule = 'trim';
		$edit->nomfis->size=70;
		$edit->nomfis->maxlength =80;
		$edit->nomfis->group = 'Informaci&oacute;n f&iacute;scal';

		$lriffis='<a href="javascript:consulrif(\'riffis\');" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->riffis = new inputField('RIF F&iacute;scal', 'riffis');
		$edit->riffis->size = 13;
		$edit->riffis->maxlength =10;
		$edit->riffis->rule = 'trim|callback_chrif';
		$edit->riffis->append($lriffis);
		$edit->riffis->group = 'Informaci&oacute;n f&iacute;scal';

		$edit->zona = new dropdownField('Zona', 'zona');
		$edit->zona->rule = 'trim|required';
		$edit->zona->option('','Seleccionar');
		$edit->zona->options('SELECT codigo, nombre FROM zona ORDER BY nombre');

		$edit->pais = new inputField('Pa&iacute;s','pais');
		$edit->pais->rule = 'trim';
		$edit->pais->size =40;
		$edit->pais->maxlength =30;

		$edit->email = new inputField('E-mail', 'email');
		$edit->email->rule = 'trim|valid_email';
		$edit->email->size =28;
		$edit->email->maxlength =100;

		$edit->cuenta = new inputField('Cuenta contable', 'cuenta');
		$edit->cuenta->rule='trim|callback_chcuentac';
		$edit->cuenta->append($bcpla);
		$edit->cuenta->size=20;
		$edit->cuenta->maxlength =15;

		$edit->telefono = new inputField('Tel&eacute;fonos', 'telefono');
		$edit->telefono->rule = 'trim';
		$edit->telefono->size=30;
		$edit->telefono->maxlength =30;

		$edit->telefon2 = new inputField('Fax', 'telefon2');
		$edit->telefon2->rule = 'trim';
		$edit->telefon2->size=25;
		$edit->telefon2->maxlength =25;

		$edit->tipo = new dropdownField('Precio Asignado', 'tipo');
		$edit->tipo->options(array('1'=> 'Precio 1','2'=>'Precio 2', '3'=>'Precio 3','4'=>'Precio 4','0'=>'Inactivo'));
		$edit->tipo->style = 'width:90px';
		$edit->tipo->group = 'Informaci&oacute;n financiera';

		$edit->formap = new inputField('D&iacute;as de Cr&eacute;dito', 'formap');
		$edit->formap->css_class='inputnum';
		$edit->formap->rule='trim|integer';
		$edit->formap->maxlength =10;
		$edit->formap->size =6;
		$edit->formap->group = 'Informaci&oacute;n financiera';

		$edit->limite = new inputField('L&iacute;mite de Cr&eacute;dito', 'limite');
		$edit->limite->css_class='inputnum';
		$edit->limite->rule='trim|numeric';
		$edit->limite->maxlength =15;
		$edit->limite->size = 20;
		$edit->limite->group = 'Informaci&oacute;n financiera';

		$edit->vendedor = new dropdownField('Vendedor', 'vendedor');
		$edit->vendedor->option('','Ninguno');
		$edit->vendedor->options("SELECT vendedor, CONCAT(vendedor,'-',nombre) AS nom FROM vend WHERE tipo IN ('V','A') ORDER BY vendedor");
		$edit->vendedor->group = 'Informaci&oacute;n financiera';

		$edit->porvend = new inputField('&nbsp;&nbsp;% Comisi&oacute;n venta', 'porvend');
		$edit->porvend->css_class='inputnum';
		$edit->porvend->rule='trim|numeric';
		$edit->porvend->size=8;
		$edit->porvend->maxlength =5;
		$edit->porvend->group = 'Informaci&oacute;n financiera';

		$edit->cobrador = new dropdownField('Cobrador', 'cobrador');
		$edit->cobrador->option('','Ninguno');
		$edit->cobrador->options("SELECT vendedor, nombre FROM vend WHERE tipo IN ('C','A') ORDER BY nombre");
		$edit->cobrador->group = 'Informaci&oacute;n financiera';

		$edit->porcobr = new inputField('&nbsp;&nbsp;% Comisi&oacute;n cobro', 'porcobr');
		$edit->porcobr->css_class='inputnum';
		$edit->porcobr->rule='trim|numeric';
		$edit->porcobr->size=8;
		$edit->porcobr->maxlength =5;
		$edit->porcobr->group = 'Informaci&oacute;n financiera';

		$edit->observa = new textareaField('Observaci&oacute;n', 'observa');
		$edit->observa->rule = 'trim';
		$edit->observa->cols = 70;
		$edit->observa->rows =3;

		$edit->mensaje = new inputField('Mensaje', 'mensaje');
		$edit->mensaje->rule = 'trim';
		$edit->mensaje->size = 50;
		$edit->mensaje->maxlength =40;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		//$data['script']  ='<script type="text/javascript">
		//function pelusa(){
		//	alert(screen.availWidth);
		//}
		//</script>';

		$data['content'] = $edit->output;
		$data['content'].= $this->pi18n->fallas();
		$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['title']   = heading('Clientes');
		$data['head']    = script('jquery.pack.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').script('plugins/jquery.autocomplete.js').style('jquery.autocomplete.css').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function claveedit(){
		//$this->pi18n->cargar('scli','dataedit');
		$this->rapyd->load('dataedit');
		$this->rapyd->uri->keep_persistence();
		$persistence = $this->rapyd->session->get_persistence('ventas/scli/filteredgrid', $this->rapyd->uri->gfid);
		$back= (isset($persistence['back_uri'])) ? $persistence['back_uri'] : site_url('ventas/scli/filteredgrid');

		$edit = new DataEdit('Clientes', 'scli');
		$id=$edit->_dataobject->pk['id'];
		$edit->back_url    = $back;
		$edit->back_save   = true;
		$edit->back_cancel = true;
		$edit->back_cancel_save=true;

		$edit->cliente = new inputField('Cliente', 'cliente');
		$edit->cliente->mode = 'autohide';
		$edit->cliente->when=array('show','modify');
		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->mode = 'autohide';
		$edit->nombre->in='cliente';
		$edit->nombre->when=array('show','modify');

		$edit->clave = new inputField('Clave', 'clave');
		$edit->clave->type = 'password';
		$edit->clave->rule = 'matches[clave1]';
		$edit->clave->when = array('modify');

		$edit->clave1 = new inputField('Confirmaci&oacute;n de clave', 'clave1');
		$edit->clave1->type    = 'password';
		$edit->clave1->db_name = 'clave';
		$edit->clave1->when    = array('modify');

		$edit->clave->size      = $edit->clave1->size = 8;
		$edit->clave->maxlength = $edit->clave1->maxlength = 12;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$this->rapyd->jquery[]="$('#df1').submit(function(){
			if( $('#clave').val() != '' ) {
				pwEncrypt = $().crypt( {
					method: 'md5',
					source: $('#clave').val()
				});
				$('#clave').val(pwEncrypt);

				pwEncrypt = $().crypt( {
					method: 'md5',
					source: $('#clave1').val()
				});
				$('#clave1').val(pwEncrypt);
			}
			return true;
		});";
		$data['content'] = $edit->output;
		$data['title']   = heading('Asignaci&oacute;n de contrase&ntilde;a a cliente');
		$data['head']    = $this->rapyd->get_head().script('plugins/jquery.crypt.js');
		$this->load->view('view_ventanas', $data);
	}

	function chdfiscal($tiva){
		$nomfis=$this->input->post('nomfis');
		$riffis=$this->input->post('riffis');
		if($tiva=='C' OR $tiva=='E' OR $tiva=='R')
			if(empty($nomfis) OR empty($riffis)){
				$this->validation->set_message('chdfiscal', "Debe introducir rif y nombre fiscal cuando el cliente es contribuyente, especial o regimen excento");
				return FALSE;
			}
		return TRUE;
	}

	function _pre_del($do) {
		$codigo=$this->db->escape($do->get('cliente'));
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE cod_cli=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM smov WHERE cod_cli=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM snot WHERE cod_cli=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM snte WHERE cod_cli=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM otin WHERE cod_cli=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM pfac WHERE cod_cli=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE enlace=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM bmov WHERE clipro='C' AND codcp=$codigo");

		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Cliente con Movimiento no puede ser Borrado';
			return False;
		}
		return True;
	}

	function _post_insert($do){
		$codigo=$do->get('cliente');
		$limite=$do->get('limite');
		logusu('scli',"CLIENTE $codigo CREADO, LIMITE $limite");
	}

	function _post_update($do){
		$codigo=$do->get('cliente');
		$limite=$do->get('limite');
		logusu('scli',"CLIENTE $codigo MODIFICADO, LIMITE $limite");
	}

	function _post_delete($do){
		$codigo=$do->get('cliente');
		$limite=$do->get('limite');
		logusu('scli',"CLIENTE $codigo ELIMINADO, LIMITE $limite");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('cliente');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM scli WHERE cliente='$codigo'");
		if ($chek > 0){
			$mSQL_1=$this->db->query("SELECT nombre, rifci FROM scli WHERE cliente='$codigo'");
			$row = $mSQL_1->row();
			$nombre =$row->nombre;
			$rifci  =$row->rifci;
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el cliente $nombre  $rifci ");
			return FALSE;
		}else {
		return TRUE;
		}
	}

	function autocomplete($campo,$cod=FALSE){
		if($cod!==false){
			$cod=$this->db->escape_like_str($cod);
			$qformato=$this->datasis->formato_cpla();
			$data['cliente']="SELECT cliente AS c1 ,nombre AS c2 FROM scli WHERE cliente LIKE '$cod%' ORDER BY cliente LIMIT 10";
			if(isset($data[$campo])){
				$query=$this->db->query($data[$campo]);
				if($query->num_rows() > 0){
					foreach($query->result() AS $row){
						echo $row->c1.'|'.$row->c2."\n";
					}
				}
			}
		}
	}

	function instalar(){
		$seniat='http://www.seniat.gov.ve/BuscaRif/BuscaRif.jsp';
		$mSQL="INSERT INTO valores (nombre,valor,descrip) VALUES ('CONSULRIF','$seniat','Pagina de consulta de rif del seniat') ON DUPLICATE KEY UPDATE valor='$seniat'";
		var_dump($this->db->simple_query($mSQL));
		$mSQL='ALTER TABLE `scli` ADD `modifi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL AFTER `mensaje`';
		var_dump($this->db->simple_query($mSQL));
		$mSQL='ALTER TABLE `scli` DROP PRIMARY KEY, ADD UNIQUE `cliente` (`cliente`)';
		var_dump($this->db->simple_query($mSQL));
		$mSQL='ALTER TABLE scli ADD id INT AUTO_INCREMENT PRIMARY KEY';
		var_dump($this->db->simple_query($mSQL));
		$mSQL='ALTER TABLE `scli`  CHANGE COLUMN `formap` `formap` INT(6) NULL DEFAULT 0';
		var_dump($this->db->simple_query($mSQL));
		$mSQL='ALTER TABLE `scli`  CHANGE COLUMN `email` `email` VARCHAR(100) NULL DEFAULT NULL';
		var_dump($this->db->simple_query($mSQL));
		$mSQL='ALTER TABLE `scli`  CHANGE COLUMN `clave` `clave` VARCHAR(50) NULL DEFAULT NULL AFTER `tiva`';
		var_dump($this->db->simple_query($mSQL));
	}
}