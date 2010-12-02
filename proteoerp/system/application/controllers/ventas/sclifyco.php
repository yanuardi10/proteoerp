<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//Cliente
class sclifyco extends validaciones {

	function sclifyco(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->load->library("menues");
		//$this->datasis->modulo_id(131,1);
		$this->load->database();
	}

	function index(){
		redirect("ventas/sclifyco/filteredgrid");
	}
	function filteredgrid(){
		
		$atts = array(
              'width'      => '800',
              'height'     => '600',
              'scrollbars' => 'yes',
              'status'     => 'yes',
              'resizable'  => 'yes',
              'screenx'    => '0',
              'screeny'    => '0'
            );
		
		
		$this->rapyd->load("datafilter2","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		$link2=site_url('inventario/common/get_zona');
		$link3=site_url('inventario/common/get_estados');
				
		$filter = new DataFilter2("Filtro de Clientes", 'scli');
		
		$filter->cliente = new inputField("C&oacute;digo", "cliente");
		$filter->cliente->size=10;
		
		$filter->nombre= new inputField("Nombre"  , "nombre");
		$filter->nombre->size=30;
		
		$filter->contacto = new inputField("Contacto", "contacto");
		$filter->contacto->size=30;
				
		$filter->pais = new dropdownField("Pa&iacute;s","pais");
		$filter->pais->style = "width:150px";
		$filter->pais->option("","Seleccionar");
		$filter->pais->options("SELECT codigo, nombre FROM pais ORDER BY codigo");
		$filter->pais->group = "Ubicaci&oacute;n";
		$filter->pais->onchange = "get_zona();";
		
		$filter->zona = new dropdownField("Zona", "zona");
		$filter->zona->style = "width:150px";
		$filter->zona->option("","Seleccionar");
		$filter->zona->options("SELECT codigo, nombre FROM zona ORDER BY nombre");
		$filter->zona->group = "Ubicaci&oacute;n";
		$filter->zona->onchange = "get_estados();";
	
		$filter->estado = new dropdownField("Estado","estado");
		$filter->estado->style = "width:150px";
		$filter->estado->option("","Seleccione una Zona");
		$filter->estado->options("SELECT codigo, nombre FROM estado ORDER BY codigo");
		$filter->estado->group = "Ubicaci&oacute;n";
		$filter->estado->onchange = "get_municipios();";
	
		$filter->municipios = new dropdownField("Municipio","municipio");
		$filter->municipios->style = "width:180px";
		$filter->municipios->option("","Seleccione una Modelo");
		$filter->municipios->options("SELECT codigo, nombre FROM municipio ORDER BY codigo");
		$filter->municipios->group = "Ubicaci&oacute;n";
		
		$filter->grupo = new dropdownField("Grupo","grupo");
		$filter->grupo->style = "width:150px";
		$filter->grupo->option("","Seleccionar");
		$filter->grupo->options("SELECT grupo, gr_desc FROM grcl ORDER BY grupo");
		$filter->grupo->group = "Grupo";
					
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/sclifyco/dataedit/show/<#id#>','<#cliente#>');
		$uri2 = anchor_popup('ventas/sclifyco/carga/<#id#>',"Requisitos",$atts);

		$grid = new DataGrid("Lista de Clientes");
		$grid->order_by("nombre","asc");
		$grid->per_page=15;
		
		$grid->column("Cliente",$uri);
		$grid->column("Nombre","nombre","nombre");
		$grid->column("RIF/CI","rifci");
		$grid->column("Contribuyente","tiva","align='center'");
		$grid->column("Cargar",$uri2,"align='center'");
		$grid->add("ventas/sclifyco/dataedit/create");
		$grid->build();
		
		$link=site_url('ventas/sclifyco/get_municipios');
		$link1=site_url('ventas/sclifyco/get_estados');
		$link2=site_url('ventas/sclifyco/get_zona');
		$data['script']  =<<<script
		<script type="text/javascript" charset="utf-8">
		function get_zona(){
			var pais=$("#pais").val();
			$.ajax({
				url: "$link2"+'/'+pais,
				success: function(msg){
					$("#td_zona").html(msg);
					//alert(pais);
				}
			});
			get_estados();
		}
		function get_estados(){
			var zona=$("#zona").val();
			$.ajax({
				url: "$link1"+'/'+zona,
				success: function(msg){
					$("#td_estado").html(msg);
					//alert(zona);
				}
			});
			get_municipios();
		}
		function get_municipios(){
			var estado=$("#estado").val();
			$.ajax({
				url: "$link"+'/'+estado,
				success: function(msg){
					$("#td_municipio").html(msg);
				}
			});
		}
		</script>
script;
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Clientes</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["head"]   .= $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function get_zona($pais=null){
		$this->rapyd->load("fields");
		
		$zona = new dropdownField("Zona","zona");
		$zona->option("","Seleccione una Zona");
		$zona->status = "modify";
		$zona->onchange = "get_estados();";
		$zona->options("SELECT codigo, nombre FROM zona WHERE pais='$pais' ORDER BY codigo");
		$zona->style="width:350px";
		$zona->build();
		
		echo $zona->output;
	}
	function get_municipios($estado=null){
		$this->rapyd->load("fields");
		
		$municipios = new dropdownField("Municipio", "municipio");
		$municipios->option("","Seleccione un Municipio");
		$municipios->status = "modify";
		$municipios->options("SELECT codigo, nombre FROM municipio WHERE estado='$estado' ORDER BY codigo");
		$municipios->style="width:350px";
		$municipios->build();
		
		echo $municipios->output;
	}
	function get_estados($zona=null){
		$this->rapyd->load("fields");
		
		$estado = new dropdownField("Estados","estado");
		$estado->option("","Seleccione un Estado");
		$estado->status = "modify";
		$estado->onchange = "get_municipios();";
		$estado->options("SELECT codigo, nombre FROM estado WHERE zona='$zona' ORDER BY codigo");
		$estado->style="width:350px";
		$estado->build();
		
		echo $estado->output;
	}
	function dataedit(){ 
		$this->rapyd->load("dataedit");
		
		$msclifycod=array(
			'tabla'   =>'scli',
			'columnas'=>array(
				'cliente' =>'C&oacute;digo Socio',
				'nombre'=>'Nombre', 
				'cirepre'=>'Rif/Cedula',
				'dire11'=>'Direcci&oacute;n'),
			'filtro'  =>array('cliente'=>'C&oacute;digo Socio','nombre'=>'Nombre'),
			'retornar'=>array('cliente'=>'socio'),
			'titulo'  =>'Buscar Socio');
		
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
		
		$boton =$this->datasis->modbus($msclifycod);
		$bcpla =$this->datasis->modbus($mCPLA);
		
		$smenu['link']=barra_menu('131');
		$consulrif=trim($this->datasis->traevalor('CONSULRIF')); 
				
		$script ='
		$(document).ready(function(){
    
		$(function() {
			$(".inputnum").numeric(".");
			$("#tiva").change(function () { anomfis(); }).change();
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
		}
    
		
		';
		
		$edit = new DataEdit("Clientes", "scli");
		$edit->back_url = site_url("ventas/sclifyco/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		$edit->pre_process('delete','_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->cliente = new inputField("C&oacute;digo", "cliente");
		$edit->cliente->rule = "trim|strtoupper|required|callback_chexiste";
		$edit->cliente->mode = "autohide";
		$edit->cliente->size = 9;
		$edit->cliente->maxlength = 5;
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->rule = "trim|strtoupper|required";
		$edit->nombre->size = 60;
		$edit->nombre->maxlength = 45;
		
		$edit->contacto = new inputField("Contacto", "contacto");
		$edit->contacto->rule = "trim";
		$edit->contacto->size = 60;
		$edit->contacto->maxlength = 40;
		
		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->option("","Seleccione un grupo");
		$edit->grupo->options("SELECT grupo, gr_desc FROM grcl ORDER BY gr_desc");
		$edit->grupo->rule = "required";
		$edit->grupo->size = 6;
		$edit->grupo->maxlength = 4;
		
		$lriffis='<a href="javascript:consulrif(\'rifci\');" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->rifci = new inputField("Ruc", "rifci");
		$edit->rifci->rule = "trim|strtoupper|required";
		$edit->rifci->maxlength =50;
		//$edit->rifci->append($lriffis);
		$edit->rifci->size =30;
		
		$edit->dire11 = new inputField("Direcci&oacute;n","dire11");
		$edit->dire11->rule = "trim";
		$edit->dire11->size = 60;
		$edit->dire11->maxlength = 40;

		$edit->dire12 = new inputField('&nbsp;',"dire12");
		$edit->dire12->rule = "trim";
		$edit->dire12->maxlength = 40;
		$edit->dire12->size = 60;
			
		$edit->pais = new inputField("Pa&iacute;s", "pais");
		$edit->pais->rule = "trim";
		$edit->pais->size =40;
		$edit->pais->maxlength =30;
		$edit->pais->group = "Ubicaci&oacute;n";
		
		$edit->zona = new dropdownField("Zona", "zona");                        
		$edit->zona->rule = "trim";                                    
		$edit->zona->option("","Seleccionar");                                  
		$edit->zona->options("SELECT codigo, nombre FROM zona ORDER BY nombre");
		$edit->zona->group = "Ubicaci&oacute;n";
	
		//$edit->ciudad1 = new dropdownField("Ciudad","ciudad1");
		//$edit->ciudad1->rule = "trim";
		//$edit->ciudad1->option("","");
		//$edit->ciudad1->options("SELECT ciudad codigo, ciudad FROM ciud ORDER BY ciudad");
		//$edit->ciudad1->maxlength = 40;
		//$edit->ciudad1->size = 60;
						
		$edit->dire21 = new inputField("Direcci&oacute;n","dire21");
		$edit->dire21->rule = "trim";
		$edit->dire21->maxlength = 40;
		$edit->dire21->size = 60;
		
		//$edit->ciudad2 = new dropdownField("Ciudad", "ciudad2");
		//$edit->ciudad2->option("","Seleccionar");
		//$edit->ciudad2->options("SELECT ciudad codigo, ciudad FROM ciud ORDER BY ciudad");
		//$edit->ciudad2->style =$edit->ciudad1->style = "width:180px";
		//$edit->ciudad2->maxlength = 40;
		//$edit->ciudad2->size = 60;
    //
		//$edit->dire22 = new inputField('&nbsp;',"dire22");
		//$edit->dire22->rule = "trim";
		//$edit->dire22->maxlength = 40;
		//$edit->dire22->size = 60;
		
		$edit->pais = new dropdownField("Pa&iacute;s","pais");
		$edit->pais->style = "width:150px";
		$edit->pais->option("","Seleccionar");
		$edit->pais->options("SELECT codigo, nombre FROM pais ORDER BY codigo");
		$edit->pais->group = "Ubicaci&oacute;n";
		$edit->pais->onchange = "get_zona();";
		
		$edit->zona = new dropdownField("Zona", "zona");
		$edit->zona->style = "width:150px";
		if($edit->_status=='modify'){ 
			$pais =$edit->_dataobject->get("pais");
			$edit->zona->options("SELECT codigo, nombre FROM zona WHERE pais='$pais' ORDER BY codigo");
		}else{
			$edit->zona->option("","Seleccione una Zona");
		}	
		$edit->zona->option("","Seleccionar");
		$edit->zona->options("SELECT codigo, nombre FROM zona ORDER BY nombre");
		$edit->zona->group = "Ubicaci&oacute;n";
		$edit->zona->onchange = "get_estados();";
		
		$edit->estado = new dropdownField("Estado","estado");
		$edit->estado->style = "width:150px";
		if($edit->_status=='modify'){ 
			$zona =$edit->_dataobject->get("zona");
			$edit->estado->options("SELECT codigo, nombre FROM estado WHERE zona='$zona' ORDER BY codigo");
		}else{
			$edit->estado->option("","Seleccione una Zona");
		}	
		$edit->estado->group = "Ubicaci&oacute;n";
		$edit->estado->options("SELECT codigo, nombre FROM estado ORDER BY codigo");
		$edit->estado->onchange = "get_municipios();";
		
		
		$edit->municipios = new dropdownField("Municipio","municipio");
		$edit->municipios->style = "width:180px";
		if($edit->_status=='modify'){ 
			$estado   =$edit->_dataobject->get("estado");
			$edit->municipios->options("SELECT codigo, nombre FROM municipio WHERE estado='$estado' ORDER BY codigo");
		}else{
			$edit->municipios->option("","Seleccione una Modelo");
		}
		$edit->municipios->options("SELECT codigo, nombre FROM municipio ORDER BY codigo");
		$edit->municipios->group = "Ubicaci&oacute;n";
						
		$edit->repre  = new inputField("Representante Legal", "repre");
		$edit->repre->rule = "trim";                                     
		$edit->repre->maxlength =30;
		$edit->repre->size = 40;
		
		$edit->cirepre = new inputField("C&eacute;dula de Rep.", "cirepre");
		$edit->cirepre->rule = "trim|strtoupper|callback_chci";
		$edit->cirepre->maxlength =13;
		$edit->cirepre->size = 16;
		
		$edit->socio = new inputField("Socio", "socio");
		$edit->socio->rule = "trim";
		$edit->socio->size = 8;
		$edit->socio->maxlength =5;
		$edit->socio->append($boton);
		
		$edit->tiva = new dropdownField("Tipo Fiscal", "tiva");
		$edit->tiva->option("","Seleccionar");
		$edit->tiva->options(array("C"=>"Contribuyente","N"=>"No Contribuyente","E"=>"Especial","R"=>"Regimen Exento","O"=>"Otro"));
		$edit->tiva->style = "width:140px";
		$edit->tiva->rule='required|callback_chdfiscal';
		$edit->tiva->group = "Informaci&oacute;n fiscal";
		
		$edit->nomfis = new inputField("Nombre Fiscal", "nomfis");
		$edit->nomfis->rule = "trim|required";
		$edit->nomfis->size=70;     
		$edit->nomfis->maxlength =80; 
		$edit->nomfis->group = "Informaci&oacute;n fiscal";
		
		$lriffis='<a href="javascript:consulrif(\'riffis\');" title="Consultar RIF en el SENIAT" onclick="">Consultar RIF en el SENIAT</a>';
		$edit->riffis = new inputField("RIF Fiscal", "riffis");
		$edit->riffis->size = 13;                  
		$edit->riffis->maxlength =10; 
		$edit->riffis->rule = "trim|strtoupper|callback_chrif|required";
		//$edit->riffis->append($lriffis);
		$edit->riffis->group = "Informaci&oacute;n fiscal";
				
		$edit->cuenta = new inputField("Cuenta contable", "cuenta");
		$edit->cuenta->rule='trim|callback_chcuentac';
		$edit->cuenta->append($bcpla);
		$edit->cuenta->size=20;
		$edit->cuenta->maxlength =15; 
		
		$edit->telefono = new inputField("Telefonos", "telefono");
		$edit->telefono->rule = "trim";
		$edit->telefono->size=30;
		$edit->telefono->maxlength =30;
	
		$edit->telefon2 = new inputField("Celular", "telefon2");
		$edit->telefon2->rule = "trim";
		$edit->telefon2->size=25;
		$edit->telefon2->maxlength =25;

		$edit->email  = new inputField("E-mail", "email");
		$edit->email->rule = "trim";
		$edit->email->size =50;
		$edit->email->maxlength =50;
		
		$edit->msn = new inputField("Messenger","msn");
		$edit->msn->rule = "trim";
		$edit->msn->size=50;
		$edit->msn->maxlength =50;
		
		$edit->skype = new inputField("Skype","skype");
		$edit->skype->rule = "trim";
		$edit->skype->size=50;
		$edit->skype->maxlength =50;
		
		$edit->bbpin = new inputField("BB Pin","bbpin");
		$edit->bbpin->rule = "trim";
		$edit->bbpin->size=20;
		$edit->bbpin->maxlength =20;
		
		$edit->tipo = new dropdownField("Precio Asignado", "tipo");
		$edit->tipo->options(array("1"=> "Precio 1","2"=>"Precio 2", "3"=>"Precio 3","4"=>"Precio 4","0"=>"Inactivo"));
		$edit->tipo->style = "width:90px";
		$edit->tipo->group = "Informaci&oacute;n financiera";
		
		$edit->formap = new inputField("D&iacute;as de Cr&eacute;dito", "formap");
		$edit->formap->css_class='inputnum';
		$edit->formap->rule="trim|integer";
		$edit->formap->maxlength =10;
		$edit->formap->size =6;
		$edit->formap->group = "Informaci&oacute;n financiera";
		
		$edit->limite = new inputField("L&iacute;mite de Cr&eacute;dito", "limite");
		$edit->limite->css_class='inputnum';
		$edit->limite->rule='trim|numeric';
		$edit->limite->maxlength =15;
		$edit->limite->size = 20;
		$edit->limite->group = "Informaci&oacute;n financiera";
		
		$edit->cantdes = new inputField("Cant. Desp","cantdes");
		$edit->cantdes->css_class='inputnum';
		$edit->cantdes->rule='trim|numeric';
		$edit->cantdes->maxlength =15;
		$edit->cantdes->size = 20;
		$edit->cantdes->group = "Informaci&oacute;n financiera";
		
		$edit->vendedor = new dropdownField("Vendedor", "vendedor");
		$edit->vendedor->option("","Ninguno");
		$edit->vendedor->options("SELECT vendedor, nombre FROM vend WHERE tipo IN ('V','A') ORDER BY nombre");
		$edit->vendedor->group = "Informaci&oacute;n financiera";

		$edit->porvend = new inputField("% Comisi&oacute;n venta", "porvend");
		$edit->porvend->css_class='inputnum';
		$edit->porvend->rule='trim|numeric';
		$edit->porvend->size=8;      
		$edit->porvend->maxlength =5;
		$edit->porvend->group = "Informaci&oacute;n financiera";

		$edit->cobrador = new dropdownField("Cobrador", "cobrador");
		$edit->cobrador->option("","Ninguno");
		$edit->cobrador->options("SELECT vendedor, nombre FROM vend WHERE tipo IN ('C','A') ORDER BY nombre");
		$edit->cobrador->group = "Informaci&oacute;n financiera";

		$edit->porcobr = new inputField("% Comisi&oacute;n cobro", "porcobr");
		$edit->porcobr->css_class='inputnum';
		$edit->porcobr->rule='trim|numeric';
		$edit->porcobr->size=8;
		$edit->porcobr->maxlength =5;
		$edit->porcobr->group = "Informaci&oacute;n financiera";
		
		$edit->observa = new textareaField("Observaci&oacute;n", "observa");
		$edit->observa->rule = "trim";
		$edit->observa->cols = 70;
		$edit->observa->rows =3;
		
 		$edit->mensaje = new inputField("Mensaje", "mensaje");
 		$edit->mensaje->rule = "trim";
		$edit->mensaje->size = 50;
		$edit->mensaje->maxlength =40;
		   
		$edit->buttons("modify", "save","delete","undo","back");
		$edit->build();
		
		$link=site_url('ventas/sclifyco/get_municipios');
		$link1=site_url('ventas/sclifyco/get_estados');
		$link2=site_url('ventas/sclifyco/get_zona');
		$data['script']  =<<<script
		<script type="text/javascript" charset="utf-8">
		function get_zona(){
			var pais=$("#pais").val();
			$.ajax({
				url: "$link2"+'/'+pais,
				success: function(msg){
					$("#td_zona").html(msg);
					//alert(pais);
				}
			});
			get_estados();
		}
		function get_estados(){
			var zona=$("#zona").val();
			$.ajax({
				url: "$link1"+'/'+zona,
				success: function(msg){
					$("#td_estado").html(msg);
					//alert(zona);
				}
			});
			get_municipios();
		}
		function get_municipios(){
			var estado=$("#estado").val();
			$.ajax({
				url: "$link"+'/'+estado,
				success: function(msg){
					$("#td_municipio").html(msg);
				}
			});
		}
		</script>
script;
			
		$data['content'] = $edit->output;
		$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);
		$data['title']   = "<h1>Clientes</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["head"]   .= $this->rapyd->get_head();
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
		$codigo=$do->get('cliente');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE cod_cli='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM smov WHERE cod_cli='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM snot WHERE cod_cli='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM snte WHERE cod_cli='$codigo'");    
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM otin WHERE cod_cli='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM pfac WHERE cod_cli='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM pers WHERE enlace='$codigo'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM bmov WHERE clipro='C' AND codcp='$codigo'");
		
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
	function instalar(){
		$seniat='http://www.seniat.gov.ve/BuscaRif/BuscaRif.jsp';
		$mSQL="INSERT INTO valores (nombre,valor,descrip) VALUES ('CONSULRIF','$seniat','Pagina de consulta de rif del seniat') ON DUPLICATE KEY UPDATE valor='$seniat'";
		var_dum($this->db->simple_query($mSQL));
		$mSQL='ALTER TABLE `scli` ADD `modifi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL AFTER `mensaje`';
		var_dum($this->db->simple_query($mSQL));
		$mSQL='ALTER TABLE `scli` DROP PRIMARY KEY, ADD UNIQUE `cliente` (`cliente`)';
		var_dum($this->db->simple_query($mSQL));
		$mSQL='ALTER TABLE scli ADD id INT AUTO_INCREMENT PRIMARY KEY';
		var_dum($this->db->simple_query($mSQL));
                
	}	
	function carga($id=''){
		//echo 'Codigo: '.$id;
		$campo="<form action='/../../proteoerp/ventas/sclifyco/cargarequi/$id'; method='post'>
 		<input size='100' type='hidden' name='$id' value='$id'>
 		<input type='submit' value='Carga' name='boton'/>
 		</form>";
		
		$data['content'] =  $campo;
		$data['title']   =  "<h1>Cargar Requisitos</h1>";
		$data["head"]    =  script("jquery-1.2.6.pack.js");
		$data["head"]    .= script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function cargarequi($id=''){	

		$mSQL="SELECT codigo,descrip FROM requisitos";	
		$mSQL_1 =  $this->db->query($mSQL);
		$data['result']=$mSQL_1->result();
		
		foreach ($data['result'] AS $items){
          $codigo=$items->codigo;
          $descrip=$items->descrip;
          $insert =  $this->db->query("INSERT IGNORE INTO itrequisi (cliente,codigo,descrip)VALUES('$id','$codigo','$descrip')");
     }
     redirect("ventas/sclifyco/requisitos/$id");
	}
	function activar(){
		$cliente  = $this->db->escape($this->input->post('cliente'));
		$codigo   = $this->db->escape($this->input->post('codigo'));
		echo 'cliente'.$cliente;
		echo 'codigo'.$codigo;
		$mSQL="UPDATE itrequisi SET estado=if(estado='S','N','S') WHERE cliente=$cliente AND codigo=$codigo";
		//echo  'aqui'.$mSQL;
		$a= $this->db->query($mSQL);				
	}	
	function requisitos($cliente=''){
		$this->rapyd->load("datagrid");

		function descheck($estado,$cliente,$codigo){
		 $retorna= array(
    			'name'        => $cliente,
    			'id'          => $codigo,
    			'value'       => 'accept'
    			);
		 if($estado=='S'){
				$retorna['checked']= TRUE;
			}else{
				$retorna['checked']= FALSE;
			}
			return form_checkbox($retorna);
		}
						
		$grid = new DataGrid("Lista de Requisitos");
		$grid->db->select(array('cliente','codigo','descrip','estado'));
		$grid->db->from('itrequisi');
		$grid->db->where('cliente',$cliente);
		$grid->use_function('descheck');
						
		$grid->column("Codigo","codigo");
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Estado", "<descheck><#estado#>|<#cliente#>|<#codigo#></descheck>",'align="center"');
		$grid->build();
		//echo $grid->db->last_query();
		
		$script='';
		$url=site_url('ventas/sclifyco/activar');
		$data['script']='<script type="text/javascript">
			$(document).ready(function() {
				$("form :checkbox").click(function () {
    	       $.ajax({
						  type: "POST",
						  url: "'.$url.'",
						  data: "cliente="+this.name+"&codigo="+this.id,
						  success: function(msg){
						  //alert(msg);						  	
						  }
						});
    	    }).change();
			});
			</script>';
			
		$data['content'] =form_open('').$grid->output.form_close().$script;
		$data['title']   =  "<h1>Requisitos</h1>";
		$data["head"]    =  script("jquery-1.2.6.pack.js");
		$data["head"]    .= script("plugins/jquery.checkboxes.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}	
}
?>
