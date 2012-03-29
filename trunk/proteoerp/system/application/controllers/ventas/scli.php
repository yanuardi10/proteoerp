<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Scli extends validaciones {
	var $genesal=true;

	function scli(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('pi18n');
		//$this->load->library("menues");
		$this->datasis->modulo_id(131,1);
		//$this->instalar();
	}

	function index(){
		//$data = '';
		//$this->load->view('jqui/ventanas',$data);
		if($this->pi18n->pais=='COLOMBIA'){
			redirect('ventas/sclicol/filteredgrid');
		}else{
			//redirect('ventas/scli/filteredgrid');
			$script = $this->scliextjs();
		}
	}

	function filteredgrid(){
		$this->pi18n->cargar('scli','filteredgrid');
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Clientes', 'scli');

		$filter->cliente = new inputField('C&oacute;digo', 'cliente');
		$filter->cliente->size=6;
		$filter->cliente->group = "CLIENTE";

		$filter->nombre= new inputField('Nombre','nombre');
		$filter->nombre->size=30;
		$filter->nombre->group = "CLIENTE";

		$filter->grupo = new dropdownField('Grupo', 'grupo');
		$filter->grupo->option('','Todos');
		$filter->grupo->options('SELECT grupo, gr_desc FROM grcl ORDER BY gr_desc');
		$filter->grupo->style = 'width:140px';
		$filter->grupo->group = "CLIENTE";

		$filter->rifci= new inputField('Rif/CI','rifci');
		$filter->rifci->size=15;
		$filter->rifci->group = "VALORES";

		$filter->cuenta= new inputField('Cuenta Contable','cuenta');
		$filter->cuenta->like_side='after';
		$filter->cuenta->size=15;
		$filter->cuenta->group = "VALORES";

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$uri = anchor('ventas/scli/dataedit/show/<#id#>','<#cliente#>');

		$grid = new DataGrid('Lista de Clientes');
		$grid->order_by('nombre','asc');
		$grid->per_page=50;

		$cclave=anchor('ventas/scli/claveedit/modify/<#id#>',img(array('src'=>'images/candado.jpg','border'=>'0','alt'=>'Clave','height'=>'12','title'=>'Clave')));

		$uri_2  = anchor('ventas/scli/dataedit/show/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12','title'=>'Editar')));
		$uri_2 .= anchor('ventas/scli/consulta/<#id#>',img(array('src'=>'images/estadistica.jpeg','border'=>'0','alt'=>'Consultar','height'=>'12','title'=>'Consultar')));
		$uri_2 .= $cclave;
		$uri_2 .= img(array('src'=>'images/<siinulo><#tipo#>|N|S</siinulo>.gif','border'=>'0','alt'=>'Estado','title'=>'Estado'));

		$grid->column('Acci&oacute;n',$uri_2);
		$grid->column_orderby('Cliente',$uri,'cliente');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby($this->pi18n->msj('rifci','Rif/CI'),'rifci','rifci');
		$grid->column_orderby($this->pi18n->msj('tiva','Tipo') ,'tiva','tiva','align=\'center\'');
		$grid->column_orderby('Telefono','telefono','telefono');
		$grid->column_orderby('Contacto','contacto','contacto');
		$grid->column_orderby('Nombre Fiscal','nomfis','nomfis');
		$grid->column_orderby('Grupo','grupo','grupo','align=\'center\'');
		$grid->column_orderby('Credito','limite','limite','align=\'right\'');
		$grid->column_orderby('Cuenta','cuenta','cuenta');

		$grid->add('ventas/scli/dataedit/create','Agregar');
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

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');

		$data['extras']  = $extras;

		$data['content'] = $grid->output;
		$data['content'].= $this->pi18n->fallas();
		$data['filtro']  = $filter->output;
		$data['title']   = heading('Modulo de Clientes');
		$data['script']  = script('jquery.js');
		$data["script"] .= script('superTables.js');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}


	// **************************************
	//     DATAEDIT
	//
	// **************************************
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

		$link20=site_url('inventario/scli/scliexiste');
		$link21=site_url('inventario/scli/sclicodigo');


		$script ='
<script type="text/javascript" >
$(function() {

	//Default Action
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
	});

	$("#socio").autocomplete("'.$lsocio.'",{
		delay:10,
		matchSubset:1,
		matchContains:1,
		cacheLength:10,
		formatItem:formato,
		width:350,
		autoFill:true
	});
	//$(":input").enter2tab();
	$("#maintabcontainer").tabs();

	$("#rifci").focusout(function() {
		rif=$(this).val();
		if(!chrif(rif)){
			alert("Al parecer el Rif colocado no es correcto, por favor verifique con el SENIAT.");
		}
	});
});
function formato(row) {
	return row[0] + "-" + row[1];
}

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

function chrif(rif){
	rif.toUpperCase();
	var patt=/[EJPGV][0-9]{9} */g;
	if(patt.test(rif)){
		var factor= new Array(4,3,2,7,6,5,4,3,2);
		var v=0;
		if(rif[0]=="V"){
			v=1;
		}else if(rif[0]=="E"){
			v=2;
		}else if(rif[0]=="J"){
			v=3;
		}else if(rif[0]=="P"){
			v=4;
		}else if(rif[0]=="G"){
			v=5;
		}
		acum=v*factor[0];
		for(i=1;i<9;i++){
			acum=acum+parseInt(rif[i])*factor[i];
		}
		acum=11-acum%11;
		return (acum==parseInt(rif[9]));
	}else{
		return true;
	}
}

function fusionar(mviejo){
	var yurl = "";
	//var mcodigo=jPrompt("Ingrese el Codigo a ");
	jPrompt("Codigo Nuevo","" ,"Codigo Nuevo", function(mcodigo){
		if( mcodigo==null ){
			jAlert("Cancelado por el usuario","Informacion");
		} else if( mcodigo=="" ) {
			jAlert("Cancelado,  Codigo vacio","Informacion");
		} else {
			//mcodigo=jQuery.trim(mcodig);
			//jAlert("Aceptado "+mcodigo);
			yurl = encodeURIComponent(mcodigo);
			$.ajax({
				url: "'.$link20.'",
				global: false,
				type: "POST",
				data: ({ codigo : encodeURIComponent(mcodigo) }),
				dataType: "text",
				async: false,
				success: function(sino) {
					if (sino.substring(0,1)=="S"){
						jConfirm(
							"Ya existe el codigo <div style=\"font-size: 200%;font-weight: bold \">"+mcodigo+"</"+"div>"+sino.substring(1)+"<p>si prosigue se eliminara el producto anterior y<br/> todo el movimiento de este, pasara al codigo "+mcodigo+"</"+"p> <p style=\"align: center;\">Desea <strong>Fusionarlos?</"+"strong></"+"p>",
							"Confirmar Fusion",
							function(r){
							if (r) { sclicambia("S", mviejo, mcodigo); }
							}
						);
					} else {
						jConfirm(
							"Sustitur el codigo actual  por: <center><h2 style=\"background: #ddeedd\">"+mcodigo+"</"+"h2></"+"center> <p>Al cambiar de codigo el producto, todos los<br/> movimientos y estadisticas se cambiaran<br/> correspondientemente.</"+"p> ",
							"Confirmar cambio de codigo",
							function(r) {
								if (r) { sclicambia("N", mviejo, mcodigo); }
							}
						)
					}
				},
				error: function(h,t,e) { jAlert("Error..codigo="+yurl+" ",e) }
			});
		}
	})
};

function sclicambia( mtipo, mviejo, mcodigo ) {
	$.ajax({
		url: "'.$link21.'",
		global: false,
		type: "POST",
		data: ({ tipo:  mtipo,
			 viejo: mviejo,
			 codigo: encodeURIComponent(mcodigo) }),
		dataType: "text",
		async: false,
		success: function(sino) {
			jAlert("Cambio finalizado "+sino,"Finalizado Exitosamente")
		},
		error: function(h,t,e) {jAlert("Error..","Finalizado con Error" )
		}
	});

	if( mtipo=="N" ) {
		location.reload(true);
	} else {
		location.replace("'.site_url("inventario/sinv/filteredgrid").'");
	}
}

</script>
';

		$edit = new DataEdit('Clientes', 'scli');
		$edit->back_url = site_url('ventas/scli/filteredgrid');
//		$edit->script($script, 'create');
//		$edit->script($script, 'modify');

		$edit->pre_process('delete','_pre_del');
		$edit->pre_process('insert','_pre_ins');
		$edit->pre_process('update','_pre_udp');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->cliente = new inputField('C&oacute;digo', 'cliente');
		$edit->cliente->rule = 'trim|strtoupper|callback_chexiste';
		$edit->cliente->mode = 'autohide';
		$edit->cliente->size = 9;
		$edit->cliente->maxlength = 5;

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->rule = 'trim|strtoupper|required';
		$edit->nombre->size = 55;
		$edit->nombre->maxlength = 45;
		$edit->nombre->style = 'width:100%;';

		$edit->nomfis = new textareaField('Nombre F&iacute;scal', 'nomfis');
		$edit->nomfis->rule = 'trim';
		$edit->nomfis->cols = 53;
		$edit->nomfis->rows =  2;
		$edit->nomfis->maxlength =200;
		$edit->nomfis->style = 'width:100%;';

		$edit->contacto = new inputField('Contacto', 'contacto');
		$edit->contacto->rule = 'trim';
		$edit->contacto->size = 55;
		$edit->contacto->maxlength = 40;
		$edit->contacto->style = 'width:100%;';

		$edit->grupo = new dropdownField('Grupo', 'grupo');
		$edit->grupo->option('','Seleccione un grupo');
		$edit->grupo->options('SELECT grupo, CONCAT(grupo," ",gr_desc) gr_desc FROM grcl ORDER BY gr_desc');
		$edit->grupo->rule = 'required';
		$edit->grupo->size = 6;
		$edit->grupo->maxlength = 4;
		$edit->grupo->style = 'width:160px';
		$edit->grupo->insertValue = $this->datasis->dameval('SELECT grupo FROM grcl WHERE gr_desc like "CONSUMIDOR FINAL%"');

		$lriffis='<a href="javascript:consulrif(\'rifci\');" title="SENIAT" onclick="" style="color:red;font-size:9px;border:none; "> SENIAT</a>';
		$edit->rifci = new inputField($this->pi18n->msj('rifci','RIF o C.I.'), 'rifci');
		$edit->rifci->rule = 'trim|strtoupper|required|callback_chci';
		$edit->rifci->maxlength =13;
		$edit->rifci->append($lriffis);
		$edit->rifci->size =14;

		$obj  ="dire11";
		$edit->$obj = new inputField('Oficina',$obj);
		$edit->$obj->rule = 'trim';
		$edit->$obj->size      = 45;
		$edit->$obj->maxlength = 40;
		$edit->$obj->style = 'width:95%;';

		$obj  ="dire12";
		$edit->$obj = new inputField('',$obj);
		$edit->$obj->rule = 'trim';
		$edit->$obj->size      = 45;
		$edit->$obj->maxlength = 40;
		$edit->$obj->style = 'width:95%;';

		$obj="ciudad1";
		$edit->$obj = new dropdownField('Ciudad',$obj);
		$edit->$obj->rule = 'trim';
		$edit->$obj->option('','Seleccionar');
		$edit->$obj->options('SELECT ciudad codigo, ciudad FROM ciud ORDER BY ciudad');
		$edit->$obj->style = 'width:200px';
		$edit->$obj->insertValue = $this->datasis->traevalor("CIUDAD");

		$obj  ="dire21";
		$edit->$obj = new inputField('Envio',$obj);
		$edit->$obj->rule = 'trim';
		$edit->$obj->size      = 45;
		$edit->$obj->maxlength = 40;
		$edit->$obj->style = 'width:95%;';

		$obj  ="dire22";
		$edit->$obj = new inputField('',$obj);
		$edit->$obj->rule = 'trim';
		$edit->$obj->size      = 45;
		$edit->$obj->maxlength = 40;
		$edit->$obj->style = 'width:95%;';

		$obj="ciudad2";
		$edit->$obj = new dropdownField('Ciudad',$obj);
		$edit->$obj->rule = 'trim';
		$edit->$obj->option('','Seleccionar');
		$edit->$obj->options('SELECT ciudad codigo, ciudad FROM ciud ORDER BY ciudad');
		$edit->$obj->style = 'width:200px';

		$edit->repre  = new inputField('Representante', 'repre');
		$edit->repre->rule = 'trim';
		$edit->repre->maxlength =30;
		$edit->repre->size = 30;

		$edit->cirepre = new inputField('C&eacute;dula de Rep.', 'cirepre');
		$edit->cirepre->rule = 'trim|strtoupper|callback_chci';
		$edit->cirepre->maxlength =13;
		$edit->cirepre->size = 14;

		$edit->socio = new inputField('Socio del cliente', 'socio');
		$edit->socio->rule = 'trim';
		$edit->socio->size = 8;
		$edit->socio->maxlength =5;
		$edit->socio->append($boton);

		$arr_tiva=$this->pi18n->arr_msj('tivaarr','C=Contribuyente,N=No Contribuyente,E=Especial,R=Regimen Exento,O=Otro');
		$edit->tiva = new dropdownField('Tipo F&iacute;scal', 'tiva');
		$edit->tiva->option('','Seleccionar');
		$edit->tiva->options($arr_tiva);
		$edit->tiva->style = 'width:160px';
		$edit->tiva->insertValue = 'N';

		//$edit->tiva->rule='required|callback_chdfiscal';

		$lriffis='<a href="javascript:consulrif(\'riffis\');" title="Consultar RIF en el SENIAT" onclick=""> SENIAT</a>';
		$edit->riffis = new inputField('RIF F&iacute;scal', 'riffis');
		$edit->riffis->size = 13;
		$edit->riffis->maxlength =10;
		$edit->riffis->append($lriffis);

		$edit->zona = new dropdownField('Zona', 'zona');
		$edit->zona->rule = 'trim|required';
		$edit->zona->option('','Seleccionar');
		$edit->zona->options('SELECT codigo, CONCAT(codigo," ", nombre) nombre FROM zona ORDER BY nombre');
		$edit->zona->style = 'width:166px';
		$edit->zona->insertValue = $this->datasis->traevalor("ZONAXDEFECTO");

		$edit->pais = new inputField('Pa&iacute;s','pais');
		$edit->pais->rule = 'trim';
		$edit->pais->size =20;
		$edit->pais->maxlength =30;

		$edit->email = new inputField('E-mail', 'email');
		$edit->email->rule = 'trim|valid_email';
		$edit->email->size =20;
		$edit->email->maxlength =100;

		$edit->cuenta = new inputField('Contable', 'cuenta');
		$edit->cuenta->rule='trim|callback_chcuentac';
		$edit->cuenta->append($bcpla);
		$edit->cuenta->size=15;
		$edit->cuenta->maxlength =15;
		$edit->cuenta->insertValue = $this->datasis->dameval('SELECT cuenta FROM grcl WHERE gr_desc like "CONSUMIDOR FINAL%"');

		$edit->telefono = new inputField('Tel&eacute;fonos', 'telefono');
		$edit->telefono->rule = 'trim';
		$edit->telefono->size=20;
		$edit->telefono->maxlength =30;

		$edit->telefon2 = new inputField('Fax', 'telefon2');
		$edit->telefon2->rule = 'trim';
		$edit->telefon2->size=20;
		$edit->telefon2->maxlength =25;

		$edit->tipo = new dropdownField('Tipo ', 'tipo');
		$edit->tipo->options(array('1'=> 'Precio 1','2'=>'Precio 2', '3'=>'Precio 3','4'=>'Precio 4','5'=>'Mayor','0'=>'Inactivo'));
		$edit->tipo->style = 'width:90px';

		$edit->formap = new inputField('D&iacute;as', 'formap');
		$edit->formap->css_class='inputnum';
		$edit->formap->rule='trim|integer';
		$edit->formap->maxlength =10;
		$edit->formap->size =6;

		$edit->limite = new inputField('L&iacute;mite', 'limite');
		$edit->limite->css_class='inputnum';
		$edit->limite->rule='trim|numeric';
		$edit->limite->maxlength =12;
		$edit->limite->size = 10;

		$edit->vendedor = new dropdownField('Vendedor', 'vendedor');
		$edit->vendedor->option('','Ninguno');
		$edit->vendedor->options("SELECT vendedor, CONCAT(vendedor,'-',nombre) AS nom FROM vend WHERE tipo IN ('V','A') ORDER BY vendedor");
		$edit->vendedor->style = 'width:250px';

		$edit->porvend = new inputField('Comisi&oacute;n%', 'porvend');
		$edit->porvend->css_class='inputnum';
		$edit->porvend->rule='trim|numeric';
		$edit->porvend->size=4;
		$edit->porvend->maxlength =5;

		$edit->cobrador = new dropdownField('Cobrador', 'cobrador');
		$edit->cobrador->option('','Ninguno');
		$edit->cobrador->options("SELECT vendedor, CONCAT(vendedor,'-',nombre) nombre FROM vend WHERE tipo IN ('C','A') ORDER BY vendedor");
		$edit->cobrador->style = 'width:250px';

		$edit->porcobr = new inputField('Comisi&oacute;n%', 'porcobr');
		$edit->porcobr->css_class='inputnum';
		$edit->porcobr->rule='trim|numeric';
		$edit->porcobr->size=4;
		$edit->porcobr->maxlength =5;

		$edit->observa = new textareaField('Observaci&oacute;n', 'observa');
		$edit->observa->rule = 'trim';
		$edit->observa->cols = 70;
		$edit->observa->rows =3;

		$edit->mensaje = new inputField('Mensaje', 'mensaje');
		$edit->mensaje->rule = 'trim';
		$edit->mensaje->size = 50;
		$edit->mensaje->maxlength =40;

		$edit->mmargen = new inputField("Margen al Mayor",'mmargen');
		$edit->mmargen->css_class='inputnum';
		$edit->mmargen->size=10;
		$edit->mmargen->maxlength=10;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');


		if($this->genesal){
			$edit->build();
			$style = '
<style type="text/css">
.maintabcontainer {width: 780px; margin: 5px auto;}
</style>';

			$conten["form"]  =&  $edit;
			$data['content'] = $this->load->view('view_scli', $conten,true);

			$data['content'].= $this->pi18n->fallas();
			$data['smenu']   = $this->load->view('view_sub_menu', $smenu,true);

			$data['title']   = heading('('.$edit->cliente->value.') '.substr($edit->nombre->value,0,30));

			$data['script']   = script('jquery.js');
			$data["script"]  .= script("jquery-ui.js");
			$data["script"]  .= script("jquery.alerts.js");
			$data['script']  .= script('plugins/jquery.numeric.pack.js');
			$data['script']  .= script('plugins/jquery.floatnumber.js');
			$data['script']  .= script('plugins/jquery.autocomplete.js');
			$data["script"]  .= script("plugins/jquery.blockUI.js");
			//$data["script"]  .= script("sinvmaes.js");
			$data["script"]  .= $script;

			$data['style']	 = style("jquery.alerts.css");
			$data['style']	.= style("redmond/jquery-ui.css");
			$data['style']  .= style('jquery.autocomplete.css');
			$data['style']	.= $style;

			$data['head']    = $this->rapyd->get_head();
			$this->load->view('view_ventanas', $data);
		}else{
			$edit->on_save_redirect=false;
			$edit->build();

			if($edit->on_success()){
				$rt= 'Cliente Guardado';
			}elseif($edit->on_error()){
				$rt= html_entity_decode(preg_replace('/<[^>]*>/', '', $edit->error_string));
			}
			return $rt;
		}
	}

	function filtergridcredi(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter('Gesti&oacute;n de l&iacute;mites de cr&eacute;dito');
		$sel=array('a.formap','a.limite' ,'a.tolera','a.maxtole','a.cliente','a.nombre','a.credito','b.motivo','a.id');
		$filter->db->select($sel);
		$filter->db->from('scli AS a');
		$filter->db->join('sclibitalimit AS b','a.cliente=b.cliente','left');
		$filter->db->group_by('a.cliente');

		$filter->cliente = new inputField('C&oacute;digo', 'cliente');
		$filter->cliente->db_name=  'a.cliente';
		$filter->cliente->size=6;

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->db_name=  'a.nombre';
		$filter->nombre->rule      ='max_length[45]';
		$filter->nombre->maxlength =45;

		$filter->limited = new inputField('L&iacute;mite','limited');
		$filter->limiteh = new inputField('L&iacute;mite','limiteh');
		$filter->limited->size    = $filter->limiteh->size =8;
		$filter->limited->clause  = $filter->limiteh->clause ='where';
		$filter->limited->db_name = $filter->limiteh->db_name='a.limite';
		$filter->limited->operator= '>=';
		$filter->limiteh->operator= '<=';
		$filter->limiteh->in      = 'limited';
		$filter->limited->css_class = 'inputonlynum';
		$filter->limiteh->css_class = 'inputonlynum';

		$filter->credito = new dropdownField('Cr&eacute;dito','credito');
		$filter->credito->db_name = 'a.credito';
		$filter->credito->option('' ,'Todos');
		$filter->credito->option('S','Activo');
		$filter->credito->option('N','Inactivo');
		$filter->credito->title = 'Si el cliente puede o no optar por cr&eacute;dito en la empresa';
		$filter->credito->style = 'width: 145px;';

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor('ventas/scli/creditoedit/modify/<#id#>','<#cliente#>');

		$grid = new DataGrid('');
		$grid->order_by('cliente');
		$grid->per_page = 20;

		$grid->column_orderby('Cliente',$uri   ,'cliente','align="left"');
		$grid->column_orderby('Nombre','nombre','nombre','align="left"');
		$grid->column_orderby('Cr&eacute;dito' ,'<#credito#>' ,'credito','align="center"');
		$grid->column_orderby('D&iacute;as'    ,'<nformat><#formap#></nformat>'  ,'formap' ,'align="right"');
		$grid->column_orderby('L&iacute;mite'  ,'<nformat><#limite#></nformat>'  ,'limite' ,'align="right"');
		$grid->column_orderby('Tolera'         ,'<nformat><#tolera#></nformat>%' ,'tolera' ,'align="right"');
		$grid->column_orderby('T.M&aacute;xima','<nformat><#maxtole#></nformat>%','maxtole','align="right"');
		$grid->column('Motivo','motivo');

		$grid->build();

		$script= '<script type="text/javascript" >
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});
		</script>';

		//$data['script']  = $script;
		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading('Gesti&oacute;n de l&iacute;mites de cr&eacute;dito');
		$this->load->view('view_ventanas', $data);
	}

	function creditoedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('L&iacute;mite de cr&eacute;dito', 'scli');
		$edit->back_save   = true;
		$edit->back_cancel = true;
		$edit->back_cancel_save   = true;
		$edit->back_cancel_delete = true;
		$edit->back_url = site_url('ventas/scli/filtergridcredi');

		$edit->post_process('insert','_pos_credi_insert');
		$edit->post_process('update','_pos_credi_update');
		$edit->post_process('delete','_pos_credi_delete');
		$edit->pre_process( 'insert','_pre_credi_insert');
		$edit->pre_process( 'update','_pre_credi_update');
		$edit->pre_process( 'delete','_pre_credi_delete');

		$edit->cliente = new inputField('Cliente','cliente');
		$edit->cliente->rule='max_length[5]';
		$edit->cliente->size =7;
		$edit->cliente->maxlength =5;
		$edit->cliente->mode= 'autohide';

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[45]';
		$edit->nombre->in = 'cliente';
		$edit->nombre->mode = 'autohide';
		$edit->nombre->size =47;

		$edit->credito = new dropdownField('Cr&eacute;dito','credito');
		$edit->credito->rule = 'required|enum[S,N]';
		$edit->credito->option('S','Activo');
		$edit->credito->option('N','Inactivo');
		$edit->credito->title = 'Si el cliente puede o no optar por cr&eacute;dito en la empresa';
		$edit->credito->style = 'width: 145px;';

		$edit->formap = new inputField('D&iacute;as de cr&eacute;dito','formap');
		$edit->formap->rule      = 'max_length[6]|numeric|positive|required';
		$edit->formap->title     = 'Plazo m&aacute;ximo de endeudamiento';
		$edit->formap->autocomplete  = false;
		$edit->formap->css_class = 'inputonlynum';
		$edit->formap->size      = 15;
		$edit->formap->maxlength = 6;
		$edit->formap->append('Al ser cero automaticamente se anulara el cr&eacute;dito');

		$edit->limite = new inputField('L&iacute;mite de cr&eacute;dito','limite');
		$edit->limite->rule='max_length[20]|integer|positive|required';
		$edit->limite->css_class='inputonlynum';
		$edit->limite->title = 'Monto al cual se puede endeudar el cliente';
		$edit->limite->size  = 15;
		$edit->limite->autocomplete  = false;
		$edit->limite->maxlength =20;
		$edit->limite->append('Al ser cero automaticamente se anulara el cr&eacute;dito');

		$edit->tolera = new inputField('% Tolerancia/M&aacute;ximo','tolera');
		$edit->tolera->rule='max_length[9]|numeric|porcent|callback_chtolera|required';
		$edit->tolera->css_class='inputnum';
		$edit->tolera->title = 'Tolerancia porcentual de endeudamiento';
		$edit->tolera->autocomplete  = false;
		$edit->tolera->size =5;
		$edit->tolera->maxlength =9;

		$edit->maxtole = new inputField('Maxtole','maxtole');
		$edit->maxtole->rule='max_length[9]|numeric|porcent|required';
		$edit->maxtole->css_class='inputnum';
		$edit->maxtole->autocomplete  = false;
		$edit->maxtole->title = 'Punto m&aacute;ximo de tolerancia';
		$edit->maxtole->size =5;
		$edit->maxtole->in='tolera';
		$edit->maxtole->maxlength =9;

		$edit->motivo = new textareaField('Motivo', 'motivo');
		$edit->motivo->title = 'Motivo o raz&oacute;n del cambio en la pol&iacute;tica de cr&eacute;dito';
		$edit->motivo->cols = 50;
		$edit->motivo->rows = 4;
		$edit->motivo->rule = 'required';

		$plim=$this->secu->puede('1310'); //Limite de Credito
		$paxt=$this->secu->puede('1313'); //Asigna Extra credito
		$pext=$this->secu->puede('1314'); //Extra credito

		if(!$plim){
			$edit->credito->mode = 'autohide';
			$edit->formap->mode  = 'autohide';
			$edit->limite->mode  = 'autohide';
			$edit->motivo->mode  = 'autohide';
		}
		if(!$pext) $edit->tolera->mode  = 'autohide';
		if(!$paxt) $edit->maxtole->mode = 'autohide';

		if($plim || $paxt || $pext){
			$edit->buttons('modify', 'save');
		}
		$edit->buttons('undo', 'back');
		$edit->build();

		$script= '<script type="text/javascript" >
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});
		</script>';

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$data['script'] .= $script;
		$data['title']   = heading('Cr&eacute;dito a cliente');
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

	//Permite crear un clientes desde otras interfaces
	function creascli(){
		//print_r($_POST);
		$rifci=$this->input->post('rifci');
		if(preg_match('/[VEJG][0-9]{9}$/',$rifci)>0){
			$_POST['tiva']='C';
		}else{
			$_POST['tiva']='N';
		}
		$_POST['tipo']='1';
		$this->genesal=false;
		$rt=$this->dataedit();
		echo $rt;
	}

	// Revisa si existe el codigo
	function scliexiste(){
		$cliente = rawurldecode($this->input->post('codigo'));
		$existe  = $this->datasis->dameval("SELECT count(*) FROM scli WHERE cliente='".addslashes($cliente)."'");
		$devo    = 'N '.$id;
		if ($existe > 0 ) {
			$devo  ='S';
			$devo .= $this->datasis->dameval("SELECT descrip FROM sinv WHERE codigo='".addslashes($id)."'");
		}
		echo $devo;
	}

	function chtolera($monto){
		$paxt=$this->secu->puede('1313');
		if($paxt){
			$maxtole=$this->input->post('maxtole');
		}else{
			$maxtole=$this->datasis->dameval('SELECT maxtole FROM scli WHERE id='.$this->rapyd->uri->get_edited_id());
		}

		if($monto>$maxtole){
			$this->validation->set_message('chtolera', 'La tolerancia no puede ser mayor que el margen m&aacute;ximo pautado');
			return false;
		}
		return true;
	}

	function chdfiscal($tiva){
		$nomfis=$this->input->post('nomfis');
		$riffis=$this->input->post('riffis');
		if($tiva=='C' OR $tiva=='E' OR $tiva=='R')
			if(empty($nomfis)){
				$this->validation->set_message('chdfiscal', "Debe introducir el nombre fiscal cuando el cliente es contribuyente");
				return false;
			}
			//elseif (empty($riffis)) {
			//	$this->validation->set_message('chdfiscal', "Debe introducir rif fiscal");
			//	return FALSE;
			//}
		return TRUE;
	}

	function _pre_credi_update($do){
		$cliente   = $do->get('cliente');
		$limite    = $do->get('limite');
		$dias      = $do->get('formap');
		$this->credi_motivo=$this->input->post('motivo');

		if(empty($limite) || empty($dias)){
			$do->set('tolera' ,'0');
			$do->set('maxtole','0');
			$do->set('limite' ,'0');
			$do->set('formap' ,'0');
			//$do->set('credito','N');
		}
		$do->rm_get('motivo');
		$dbcliente = $this->db->escape($cliente);
		$this->limitsant = $this->datasis->dameval('SELECT limite FROM scli WHERE cliente='.$dbcliente);
	}

	function _pos_credi_update($do){
		$codigo=$do->get('cliente');
		$limite=$do->get('limite');

		$data = array(
			'cliente'   => $codigo,
			'credito'   => $do->get('credito'),
			'limite'    => $limite,
			'limiteant' => $this->limitsant,
			'tolera'    => $do->get('tolera'),
			'motivo'    => $this->credi_motivo,
			'maxtol'    => $do->get('maxtole'),
			'estampa'   => date('Y-m-d H:i:s'),
			'usuario'   => $this->secu->usuario()
		);

		$this->db->insert('sclibitalimit', $data);
		logusu('scli',"CLIENTE $codigo MODIFICADO, LIMITE ".$this->limitsant.'-->'.$limite);
	}

	function _pre_credi_insert($do){ return false; }
	function _pre_credi_delete($do){ return false; }

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

	function _pre_udp($do){
		$do->set('riffis',trim($do->get('rifci')));
		$nomfis = $do->get('nomfis');
		if ( empty( $nomfis ) ) {
			$do->set('nomfis',trim($do->get('nombre')));
		}

		$cliente   = $do->get('cliente');
		$dbcliente = $this->db->escape($cliente);
		$this->limitsant = $this->datasis->dameval('SELECT limite FROM scli WHERE cliente='.$dbcliente);
	}

	function _pre_ins($do) {
		$do->set('riffis',trim($do->get('rifci')));
		$nomfis = $do->get('nomfis');
		if ( empty( $nomfis ) ) {
			$do->set('nomfis',trim($do->get('nombre')));
		}

		$cliente = $do->get('cliente');
		if(empty($cliente)){
			$do->set('cliente',$this->_numatri());
		}
		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('cliente');
		$limite=$do->get('limite');
		logusu('scli',"CLIENTE $codigo CREADO, LIMITE $limite");
	}

	function _post_update($do){
		$codigo=$do->get('cliente');
		$limite=$do->get('limite');
		logusu('scli',"CLIENTE $codigo MODIFICADO, LIMITE ".$this->limitsant.'-->'.$limite);
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

	function consulta(){
		$this->load->helper('openflash');
		$this->rapyd->load("datagrid");
		$fields = $this->db->field_data('scli');
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

		$mCodigo = $this->datasis->dameval("SELECT cliente FROM scli WHERE id=".$claves['id']."");

		$grid = new DataGrid('Ventas por Mes');
		$grid->db->_protect_identifiers=false;
		$grid->db->select( array('a.tipo_doc','a.fecha', 'a.numero', 'a.monto', 'a.abonos', 'a.monto-a.abonos saldo' ) );
		$grid->db->from('smov a');
		$grid->db->where('a.cod_cli', $mCodigo );
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

		$nombre = $this->datasis->dameval("SELECT nombre FROM scli WHERE id=".$claves['id']." ");

		/*
		$descrip = $this->datasis->dameval("SELECT descrip FROM sinv WHERE id=".$claves['id']." ");
		$data['content'] = "
		<table width='100%'>
			<tr>
				<td valign='top' colspan='2'>
					<div style='border: 2px outset #EFEFEF;background: #EFEFFF '>
					".$grid->output."
					</div>
				</td>
			</tr>
			<tr>
				<td valign='top'>
				".open_flash_chart_object( 300,200, site_url("inventario/sinv/ventas/$mCodigo"))."
				</td>
				<td valign='top'>".
				open_flash_chart_object( 300,200, site_url("inventario/sinv/compras/".raencode($mCodigo)))."
				</td>
			</tr>

		</table>";
		*/
		$data['content'] = $grid->output;
		$data["head"]     = script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = '<h1>Consulta de Clientes</h1>';
		$data["subtitle"] = "
			<div align='center' style='border: 2px outset #EFEFEF;background: #EFEFEF;font-size:18px'>
				<a href='javascript:javascript:history.go(-1)'>(".$mCodigo.") ".$nombre."</a>
			</div>";
		$this->load->view('view_ventanas', $data);

	}

	function _numatri(){
		$numero = $this->datasis->prox_numero('ncodcli');
		$residuo= $numero;
		$mbase  = 36;
		$conve='';
		while($residuo > $mbase-1){
			$mtempo  = $residuo % $mbase;
			$residuo = intval($residuo/$mbase);
			if($mtempo >9 ){
				$conve .= chr($mtempo+55);
			}else{
				$conve .= $mtempo;
			}
		}
		if($mtempo >9 ){
			$conve .= chr($mtempo+55);
		}else{
			$conve .= $mtempo;
		}
		return $conve;
	}


	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"nombre","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters,'scli');

		$this->db->_protect_identifiers=false;
		$this->db->select('scli.*, CONCAT("(",scli.grupo,") ",grcl.gr_desc) nomgrup');
		$this->db->from('scli');
		$this->db->join('grcl', 'scli.grupo=grcl.grupo');

		if (strlen($where)>1){ $this->db->where($where);}

		$sort = json_decode($sort, true);
		for ($i=0;$i<count($sort);$i++) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);

		$query = $this->db->get();
		$mSQL = '';
		if ( $filters ) $mSQL = $this->db->last_query();
		$results = $this->db->count_all('scli');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data " ,results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear() {
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos  = $data['data'];
		$cliente = $data['data']['cliente'];
		$nombre  = $data['data']['nombre'];

		unset($campos['nomgrup']);
		unset($campos['id']);
		unset($campos['modificado']);

		if(empty($cliente)){
			$cliente = $this->_numatri();
		}

		$mHay = $this->datasis->dameval("SELECT count(*) FROM scli WHERE cliente='".$cliente."'");
		if  ( $mHay > 0 ){
			echo "{ success: false, message: 'Ya existe un cliente con ese codigo'}";
		} else {
			$mSQL = $this->db->insert_string("scli", $campos );
			$this->db->simple_query($mSQL);
			logusu('scli',"CLIENTE $cliente $nombre CREADO");
			echo "{ success: true, message: codigo ".$data['data']['cliente'].' '.$nombre."}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$codigo = $campos['cliente'];
		unset($campos['nomgrup']);
		unset($campos['cliente']);
		unset($campos['modificado']);
		unset($campos['id']);
		//print_r($campos);
		$mSQL = $this->db->update_string("scli", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('scli',"CLIENTE ".$data['data']['cliente']." MODIFICADO");
		echo "{ success: true, message: 'Cliente Modificado '}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$cliente = $data['data']['cliente'];

		// VERIFICAR SI PUEDE
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM smov WHERE cod_cli='$cliente'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE cod_cli='$cliente'");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM spre WHERE cod_cli='$cliente'");
		$chek += $this->datasis->dameval("SELECT count(*) FROM pfac WHERE cod_cli='$cliente'");
		$chek += $this->datasis->dameval("SELECT count(*) FROM bmov WHERE clipro='C' AND codcp='$cliente'");

		if ($chek > 0){
			echo "{ success: false, message: 'Cliente con Movimiento no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM scli WHERE cliente='$cliente'");
			logusu('scli',"CLIENTE $cliente ELIMINADO");
			echo "{ success: true, message: 'Cliente Eliminado'}";
		}
	}



//****************************************************************8
//
//
//
//****************************************************************8
	function scliextjs(){

		$encabeza='CLIENTES';
		$listados= $this->datasis->listados('scli');
		$otros=$this->datasis->otros('scli', 'ventas/scli');

		$mSQL = "SELECT cod_banc, CONCAT(cod_banc,' ',nomb_banc) nombre FROM tban ORDER BY cod_banc ";
		$bancos = $this->datasis->llenacombo($mSQL);

		$mSQL = "SELECT grupo, CONCAT(grupo,' ',gr_desc) descrip FROM grcl ORDER BY grupo ";
		$grupo = $this->datasis->llenacombo($mSQL);

		$mSQL = "SELECT codigo, CONCAT(codigo,' ',nombre) nombre FROM zona ORDER BY codigo ";
		$zona = $this->datasis->llenacombo($mSQL);

		$mSQL   = "SELECT ciudad, ciudad nombre FROM ciud ORDER BY ciudad ";
		$ciudad = $this->datasis->llenacombo($mSQL);

		$mSQL = "SELECT vendedor, CONCAT(vendedor,'-',nombre) AS nom FROM vend WHERE tipo IN ('V','A') ORDER BY vendedor";
		$vende = $this->datasis->llenacombo($mSQL);

		$mSQL  = "SELECT vendedor, CONCAT(vendedor,'-',nombre) AS nom FROM vend WHERE tipo IN ('C','A') ORDER BY vendedor";
		$cobra = $this->datasis->llenacombo($mSQL);

		$tiva = "['C','Contribuyente'],['N','No Contribuyente'],['E','Especial'],['R','Regimen Exento'],['O','Otro']";

		$tipo = "['1','Precio 1'],['2','Precio 2'],['3','Precio 3'],['4','Precio 4'],['5','Mayor'],['0','Inactivo']";

		$consulrif=$this->datasis->traevalor('CONSULRIF');

		$urlajax = 'ventas/scli/';
		$variables = "var msocio = '';var mcuenta  = '';";

		$funciones = "
function ftiva(val){
	if ( val == 'C'){
		return 'Contribuyente';
	} else if ( val == 'N'){
		return  'No Contribu.';
	} else if ( val == 'E'){
		return  'Especial';
	} else if ( val == 'R'){
		return  'Exento';
	} else if ( val == 'O'){
		return  'Otros';
	}
};

function ftipo(val){
	if ( val == '1'){
		return 'Precio 1';
	} else if ( val == '2'){
		return  'Precio 2';
	} else if ( val == '3'){
		return  'Precio 3';
	} else if ( val == '4'){
		return  'Precio 4';
	} else if ( val == '5'){
		return  'Mayor';
	} else if ( val == '0'){
		return  'Inactivo';
	}
}

		";

		$valida = "";
		//{ type: 'length', field: 'cliente',  min:  1 },
		//{ type: 'length', field: 'rifci',    min: 10 },
		//{ type: 'length', field: 'nombre',   min:  3 }
		//";

		$columnas = "
		{ header: 'Codigo',        width:  60, sortable: true, dataIndex: 'cliente',  field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Nombre',        width: 250, sortable: true, dataIndex: 'nombre',   field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'R.I.F.',        width:  90, sortable: true, dataIndex: 'rifci',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Tipo Iva',      width:  80, sortable: true, dataIndex: 'tiva',     field:  { type: 'textfield' }, filter: { type: 'string'  }, renderer: ftiva },
		{ header: 'Grupo',         width:  50, sortable: true, dataIndex: 'grupo',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Precio',        width:  60, sortable: true, dataIndex: 'tipo',     field:  { type: 'textfield' }, filter: { type: 'string'  }, renderer: ftipo },
		{ header: 'Telefono',      width:  90, sortable: true, dataIndex: 'telefono', field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Fax',           width:  90, sortable: true, dataIndex: 'telefon2', field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Contacto',      width: 120, sortable: true, dataIndex: 'contacto', field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Asociado',      width:  60, sortable: true, dataIndex: 'socio',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Limite',        width:  70, sortable: true, dataIndex: 'limite',   field:  { type: 'numeroc'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('00.00') },
		{ header: 'Zona',          width:  40, sortable: true, dataIndex: 'zona',     field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Direccion',     width: 150, sortable: true, dataIndex: 'dire11',   field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Ciudad',        width:  70, sortable: true, dataIndex: 'ciudad1',  field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Email',         width: 150, sortable: true, dataIndex: 'email',    field:  { type: 'textfield' }, filter: { type: 'string'  }},
		//{ header: 'Url',           width: 150, sortable: true, dataIndex: 'url',      field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Nombre Fiscal', width: 220, sortable: true, dataIndex: 'nomfis',   field:  { type: 'textfield' }, filter: { type: 'string'  }},
		{ header: 'Mensaje',       width: 220, sortable: true, dataIndex: 'mensaje',  field:  { type: 'textfield' }, filter: { type: 'string'  }}
	";

		$campos = "'id','cliente','tipo','nombre','grupo','gr_desc','nit','formap','cuenta','limite','socio','contacto','dire11','dire12','ciudad1','dire21','dire22','ciudad2','telefono','telefon2','zona','pais','email','vendedor','porvend','cobrador','porcobr','repre','cirepre','ciudad','separa','copias','regimen','comisio','porcomi','rifci','observa','fecha1','fecha2','tiva','clave','nomfis','riffis','mensaje','modificado','sucursal','mmargen','tolera','maxtole', 'credito' ";

		$stores = "
var scliStore = new Ext.data.Store({
	fields: [ 'item', 'valor'],
	autoLoad: false, autoSync: false, pageSize: 30, pruneModifiedRecords: true, totalProperty: 'results',
	proxy: {
		type: 'ajax',
		url : urlApp + 'ventas/scli/sclibusca',
		extraParams: {  'cliente': msocio, 'origen': 'store' },
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
								xtype:'fieldset',
								layout: 'column',
								frame: false,
								border: false,
								labelAlign: 'right',
								defaults: {  },
								style:'padding:4px',
								items: [
									{ xtype: 'textfield', fieldLabel: 'Codigo',        labelWidth: 50, name: 'cliente',  allowBlank: false, columnWidth: 0.20, id: 'cliente', maxLength: 5, enforceMaxLength: true },
									{ xtype: 'textfield', fieldLabel: 'RIF/CI',        labelWidth:100, name: 'rifci',    allowBlank: false, columnWidth: 0.40, regex: /((^[VEJG][0-9])|(^[P][A-Z0-9]))/, regexText: 'Debe colocar una letra JVGE y 10 digitos' },
									{ xtype: 'combo',     fieldLabel: 'Grupo',         labelWidth: 60, name: 'grupo',    allowBlank: false, columnWidth: 0.40, store: [".$grupo."] },
									{ xtype: 'textfield', fieldLabel: 'Nombre',        labelWidth: 50, name: 'nombre',   allowBlank: false, columnWidth: 0.60, invalidText: 'Debe colocar el nombre'  },
									{ xtype: 'combo',     fieldLabel: 'Tipo',          labelWidth: 60, name: 'tiva',     allowBlank: false, columnWidth: 0.30, store: [".$tiva."] },
									{ xtype: 'textfield', fieldLabel: 'Contacto',      labelWidth: 50, name: 'contacto', allowBlank: true,  columnWidth: 0.60 },
									{ xtype: 'combo',     fieldLabel: 'Precio',        labelWidth: 60, name: 'tipo',     allowBlank: false, columnWidth: 0.30, store: [".$tipo."] },
									{ xtype: 'textfield', fieldLabel: 'Nombre Fiscal', labelWidth: 90, name: 'nomfis',   allowBlank: true,  columnWidth: 0.98, invalidText: 'Debe colocar el nombre fiscal'  },
								]
							},{
								xtype:'tabpanel',
								activeItem: 0,border: false,deferredRender: false,
								Height: 200,
								defaults: {bodyStyle:'padding:5px',hideMode:'offsets'},
								items:[{
									frame: true,border: false,autoScroll:true,title: 'Ubicacion',
									items:[{
										layout: 'column',border: false,	frame: true,autoHeight:true,style:'padding:4px',
										defaults: {xtype:'fieldset', columnWidth : 0.49  },
										items: [{
											title:'Direccion Principal',
											columnWidth : 0.50,
											layout: 'column',
											defaults:{labelWidth:60,  allowBlank: true, columnWidth : 0.99 },
											items: [
												{ xtype: 'textfield', fieldLabel: '',       name: 'dire11'  },
												{ xtype: 'textfield', fieldLabel: '',       name: 'dire12'  },
												{ xtype: 'combo',     fieldLabel: 'Ciudad', name: 'ciudad1', store: [".$ciudad."] },
											]
										},{
											title:'Direccion de Envio',
											columnWidth : 0.50,
											layout: 'column',
											defaults:{labelWidth:60,  allowBlank: true, columnWidth : 0.99 },
											items: [
												{ xtype: 'textfield', fieldLabel: '',       name: 'dire21'  },
												{ xtype: 'textfield', fieldLabel: '',       name: 'dire22'  },
												{ xtype: 'combo',     fieldLabel: 'Ciudad', name: 'ciudad2', store: [".$ciudad."] },
											]
										}]
									},{

										xtype:'fieldset',
										layout: 'column',
										frame: false,
										border: false,
										labelAlign: 'right',
										defaults: {  },
										style:'padding:4px',
										items: [
											{ xtype: 'textfield',  fieldLabel: 'Telefono', labelWidth: 50, name: 'telefono', allowBlank: true,   columnWidth: 0.60 },
											{ xtype: 'textfield',  fieldLabel: 'Pais',     labelWidth: 50, name: 'pais',     allowBlank: true,   columnWidth: 0.40 },
											{ xtype: 'textfield',  fieldLabel: 'Fax',      labelWidth: 50, name: 'telefon2', allowBlank: true,   columnWidth: 0.60 },
											{ xtype: 'combo',      fieldLabel: 'Zona',     labelWidth: 50, name: 'zona',forceSelection: true,valueField: 'item',store: [".$zona."], columnWidth: 0.40 },
										]
									}]
								},
								{
									frame: true,border: false,autoScroll:true,title: 'Condiciones',
									items:[
									{
										xtype:'fieldset',
										layout: 'column',
										frame: true,
										border: false,
										labelAlign: 'right',
										defaults: {xtype:'fieldset', labelWidth: 130, fieldStyle: 'text-align: right' },
										style:'padding:4px',
										items: [
";

		//REVISA SI TIENE AUTORIZACION

		$mLimite = $this->datasis->dameval("SELECT codigo FROM tmenus WHERE ejecutar like 'SCLILIMITE%'");
		$mTolera = $this->datasis->dameval("SELECT codigo FROM tmenus WHERE ejecutar like 'SCLITOLERA%'");
		$mMaxTol = $this->datasis->dameval("SELECT codigo FROM tmenus WHERE ejecutar like 'SCLIMAXTOLE%'");
		$mUsuario = $this->db->escape($this->secu->usuario());

		$mALimite = 'N';
		$mATolera = 'N';
		$mAMaxTol = 'N';

		if ($mLimite) $mALimite = $this->datasis->dameval("SELECT acceso FROM sida WHERE modulo=$mLimite AND usuario=$mUsuario ");
		if ($mTolera) $mATolera = $this->datasis->dameval("SELECT acceso FROM sida WHERE modulo=$mTolera AND usuario=$mUsuario ");
		if ($mMaxTol) $mAMaxTol = $this->datasis->dameval("SELECT acceso FROM sida WHERE modulo=$mMaxTol AND usuario=$mUsuario ");

		if ($mALimite == 'S') $camposforma .= "{ xtype: 'combo',       fieldLabel: 'Forma de Pago ',     name: 'credito', store: [['S','Credito Activo'],['N','Credito Suspendido']], columnWidth: 0.45, fieldStyle: 'text-align: left' },";
		$camposforma .= "\n{ xtype: 'numberfield', fieldLabel: 'Descuento al Mayor', name: 'mmargen', hideTrigger: true, fieldStyle: 'text-align: right', renderer: Ext.util.Format.numberRenderer('0,000.00'), columnWidth:0.45 },";
		if ($mALimite == 'S'){
			if ($mAMaxTol == 'S'){
			$camposforma .= "
											{ xtype: 'numberfield', fieldLabel: 'Dias de credito',    name: 'formap',  hideTrigger: false, renderer: Ext.util.Format.numberRenderer('0,000'),    columnWidth:0.45},
											{ xtype: 'numberfield', fieldLabel: 'Monto limite ',      name: 'limite',  hideTrigger: true,  renderer: Ext.util.Format.numberRenderer('0,000.00'), columnWidth:0.45},
											{ xtype: 'numberfield', fieldLabel: 'Tolerancia %',       name: 'tolera',  hideTrigger: false, renderer: Ext.util.Format.numberRenderer('0,000.00'), columnWidth:0.45},
											{ xtype: 'numberfield', fieldLabel: 'Maxima Tolera.',     name: 'maxtole', hideTrigger: false, renderer: Ext.util.Format.numberRenderer('0,000.00'), columnWidth:0.45},
";
			} elseif ($mATolera == 'S') {
			$camposforma .= "
											{ xtype: 'numberfield', fieldLabel: 'Dias de credito',    name: 'formap',  hideTrigger: false, renderer: Ext.util.Format.numberRenderer('0,000'),    columnWidth:0.45},
											{ xtype: 'numberfield', fieldLabel: 'Tolerancia %',       name: 'tolera',  hideTrigger: false, renderer: Ext.util.Format.numberRenderer('0,000.00'), columnWidth:0.45},
";
			}
		}

		$camposforma .= "
										]
									},{
										xtype:'fieldset',
										layout: 'column',
										frame: true,
										border: false,
										labelAlign: 'right',
										defaults: {xtype:'fieldset'  },
										style:'padding:4px',
										items: [
											{ xtype: 'combo',     fieldLabel: 'Cuenta Contable',labelWidth:140,name: 'cuenta',id:  'cuenta',mode: 'remote',hideTrigger: true,typeAhead: true,forceSelection: true,valueField: 'item',displayField: 'valor',store: cplaStore,columnWidth: 0.80},
										]
									},{
										xtype:'fieldset',
										layout: 'column',
										frame: true,
										border: false,
										labelAlign: 'right',
										defaults: {xtype:'fieldset'  },
										style:'padding:4px',
										items: [
											{ xtype: 'combo',     fieldLabel: 'Cliente Asociado',labelWidth:140,name: 'socio',id:  'socio',mode: 'remote',hideTrigger: true,typeAhead: true,forceSelection: true,valueField: 'item',displayField: 'valor',store: scliStore,columnWidth: 0.80},
										]
									}]
								},{
									frame: true,border: false,autoScroll:true,title: 'Otros',
									items:[{
										xtype:'fieldset',
										layout: 'column',
										frame: true,
										border: false,
										labelAlign: 'right',
										defaults: {xtype:'fieldset'  },
										style:'padding:4px',
										items: [
											{ xtype: 'combo',       fieldLabel: 'Vendedor', labelWidth: 90, name: 'vendedor', forceSelection: true,valueField: 'item',store: [".$vende."], columnWidth: 0.70 },
											{ xtype: 'numberfield', fieldLabel: 'Comision', labelWidth: 80, name: 'porvend',  hideTrigger: true, fieldStyle: 'text-align: right', columnWidth:0.30, renderer: Ext.util.Format.numberRenderer('0,000.00') },
											{ xtype: 'combo',       fieldLabel: 'Cobrador', labelWidth: 90, name: 'cobrador', forceSelection: true,valueField: 'item',store: [".$vende."], columnWidth: 0.70 },
											{ xtype: 'numberfield', fieldLabel: 'Comision', labelWidth: 80, name: 'porcobr',  hideTrigger: true, fieldStyle: 'text-align: right', columnWidth:0.30, renderer: Ext.util.Format.numberRenderer('0,000.00') },
										]
									},{
										xtype:'fieldset',
										layout: 'column',
										frame: true,
										border: false,
										labelAlign: 'right',
										defaults: {xtype:'fieldset'  },
										style:'padding:4px',
										items: [
											{ xtype: 'textfield', fieldLabel: 'Representante',  labelWidth: 90, name: 'repre',    allowBlank: true,  columnWidth: 0.70 },
											{ xtype: 'textfield', fieldLabel: 'C.I.',           labelWidth: 40, name: 'cirepre',  allowBlank: true,  columnWidth: 0.30 },
										]
									}]
								},{
									frame: true,border: false,autoScroll:true,title: 'Anexos',
									items:[{
										xtype:'fieldset',
										layout: 'column',
										frame: true,
										border: false,
										labelAlign: 'right',
										defaults: { labelWidth: 90, allowBlank: true },
										style:'padding:4px',
										items: [
											{ xtype: 'textfield',     fieldLabel: 'Mensaje',       name: 'mensaje', columnWidth: 0.99 },
											{ xtype: 'textareafield', fieldLabel: 'Observaciones', name: 'observa', columnWidth: 0.99 },
											{ xtype: 'textfield',     fieldLabel: 'Email',         name: 'email',   columnWidth: 0.99 },
											{ xtype: 'textfield',     fieldLabel: 'url',           name: 'url',     columnWidth: 0.99 },
										]
									}]
								}]
							}
		";

		$titulow = 'Clientes';

		$dockedItems = "
				{ itemId: 'seniat', text: 'SENIAT',   scope: this, handler: this.onSeniat },
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 650,
				height: 480,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;

						if (registro) {
							msocio   = registro.data.socio;
							mcuenta  = registro.data.cuenta;
							cplaStore.proxy.extraParams.cuenta   = mcuenta ;
							scliStore.proxy.extraParams.cliente = msocio ;
							cplaStore.load({ params: { 'cliente': registro.data.cliente, 'origen': 'beforeform' } });
							scliStore.load({ params: { 'cuenta':  registro.data.socio,  'origen': 'beforeform' } });
							form.loadRecord(registro);
							form.findField('cliente').setReadOnly(true);
						} else {
							form.findField('cliente').setReadOnly(false);
							//mcliente = '';
							mcuenta  = '';
						}
					}
				}
";

		$filtros = "var filters = { ftype: 'filters', encode: 'json', local: false }; ";

		$winmethod = "
				onSeniat: function(){
					var form = this.getForm();
					var vrif = form.findField('rifci').value;
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

		$data['title']  = heading('Clientes');
		$this->load->view('extjs/extjsven',$data);
		//$this->load->view('jqui/ventanas',$data);
	}

	function sclibusca() {
		$start    = isset($_REQUEST['start'])   ? $_REQUEST['start']  :  0;
		$limit    = isset($_REQUEST['limit'])   ? $_REQUEST['limit']  : 25;
		$cliente  = isset($_REQUEST['cliente']) ? $_REQUEST['cliente']: '';
		$semilla  = isset($_REQUEST['query'])   ? $_REQUEST['query']  : '';

		$semilla = trim($semilla);

		$mSQL = "SELECT cliente item, CONCAT(cliente, ' ', nombre) valor FROM scli WHERE tipo<>'0' ";
		if ( strlen($semilla)>0 ){
			$mSQL .= " AND ( cliente LIKE '$semilla%' OR nombre LIKE '%$semilla%' OR  rifci LIKE '%$semilla%') ";
		} else {
			if ( strlen($cliente)>0 ) $mSQL .= " AND cliente = '$cliente' ";
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
			echo '{success:true, message:"'.$mSQL.'", results:'. $results.', data:'.json_encode($arr).'}';
		}
	}

	function instalar(){
		$seniat=$this->db->escape('http://contribuyente.seniat.gob.ve/BuscaRif/BuscaRif.jsp');
		$mSQL  ="REPLACE INTO valores (nombre,valor,descrip) VALUES ('CONSULRIF',$seniat,'Pagina de consulta de rif del seniat') ON DUPLICATE KEY UPDATE valor=$seniat";
		$this->db->simple_query($mSQL);

		$campos = array();
		$fields = $this->db->field_data('scli');
		foreach ($fields as $field){
			if    ($field->name=='formap' && $field->type!='int')     $this->db->simple_query('ALTER TABLE `scli`  CHANGE COLUMN `formap` `formap` INT(6) NULL DEFAULT 0');
			elseif($field->name=='email'  && $field->max_length!=100) $this->db->simple_query('ALTER TABLE `scli`  CHANGE COLUMN `email` `email` VARCHAR(100) NULL DEFAULT NULL');
			elseif($field->name=='clave'  && $field->max_length!=50)  $this->db->simple_query('ALTER TABLE `scli`  CHANGE COLUMN `clave` `clave` VARCHAR(50) NULL DEFAULT NULL');
			$campos[]=$field->name;
		}

		if (!in_array('id',$campos)){
			$mSQL='ALTER TABLE `scli` DROP PRIMARY KEY, ADD UNIQUE `cliente` (`cliente`)';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE `scli` ADD `id` INT AUTO_INCREMENT PRIMARY KEY';
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sclibitalimit')){
			$mSQL="CREATE TABLE `sclibitalimit` (
				`id` INT(11) NULL AUTO_INCREMENT,
				`cliente` CHAR(5) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`credito` CHAR(1) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`limite` BIGINT(20) NULL DEFAULT NULL,
				`limiteant` BIGINT(20) NULL DEFAULT NULL,
				`tolera` DECIMAL(9,2) NULL DEFAULT NULL,
				`maxtol` DECIMAL(9,2) NULL DEFAULT NULL,
				`motivo` TEXT NULL DEFAULT NULL,
				`estampa` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				`usuario` VARCHAR(12) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX `cliente` (`cliente`)
			)
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('modifi'  ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `modifi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL AFTER `mensaje`");
		if(!in_array('credito' ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `credito` CHAR(1) NOT NULL DEFAULT 'N' AFTER `limite`");
		if(!in_array('sucursal',$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `sucursal` CHAR(2) NULL DEFAULT NULL");
		if(!in_array('mmargen' ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `mmargen` DECIMAL(7,2) NULL DEFAULT 0 COMMENT 'Margen al Mayor'");
		if(!in_array('tolera'  ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `tolera` DECIMAL(9,2) NULL DEFAULT '0' AFTER `credito`");
		if(!in_array('maxtole' ,$campos)) $this->db->simple_query("ALTER TABLE `scli` ADD COLUMN `maxtole` DECIMAL(9,2) NULL DEFAULT '0' AFTER `tolera`");
	}
}
