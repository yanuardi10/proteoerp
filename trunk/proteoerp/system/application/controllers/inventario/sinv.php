<?php include('common.php');
class sinv extends Controller {

	function sinv(){
		parent::Controller(); 
		$this->load->library('rapyd');
		$esta = $this->datasis->dameval( "SHOW columns FROM sinv WHERE Field='alto'" );
		if ( empty($esta) ) $this->db->simple_query("ALTER TABLE sinv ADD alto DECIMAL(10,2) ");
		
		$esta = $this->datasis->dameval( "SHOW columns FROM sinv WHERE Field='ancho'" );
		if ( empty($esta) ) $this->db->simple_query("ALTER TABLE sinv ADD ancho DECIMAL(10,2) ");
		
		$esta = $this->datasis->dameval( "SHOW columns FROM sinv WHERE Field='largo'" );
		if ( empty($esta) ) $this->db->simple_query("ALTER TABLE sinv ADD largo DECIMAL(10,2) ");
		
		$esta = $this->datasis->dameval( "SHOW columns FROM sinv WHERE Field='forma'" );
		if ( empty($esta) ) $this->db->simple_query("ALTER TABLE sinv ADD forma VARCHAR(50) ");
		
		$esta = $this->datasis->dameval( "SHOW columns FROM sinv WHERE Field='exento'" );
		if ( empty($esta) ) $this->db->simple_query("ALTER TABLE sinv ADD exento CHAR(1) DEFAULT 'N' ");
	}

	function index(){
		$this->datasis->modulo_id('301',1);
		redirect("inventario/sinv/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load("datafilter2","datagrid");
		$mSPRV=array(
				'tabla'   =>'sprv',
				'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
				'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
				'retornar'=>array('proveed'=>'proveed'),
				'titulo'  =>'Buscar Proveedor');

		$bSPRV=$this->datasis->modbus($mSPRV);

		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');

		$DepoScript='
		$(document).ready(function(){
			$("#depto").change(function(){
				depto();
				$.post("'.$link2.'",{ depto:$(this).val() },function(data){$("#linea").html(data);})
				$.post("'.$link3.'",{ linea:"" },function(data){$("#grupo").html(data);})
			});
			$("#linea").change(function(){
				linea();
				$.post("'.$link3.'",{ linea:$(this).val() },function(data){$("#grupo").html(data);})
			});

			$("#grupo").change(function(){
				grupo();
			});
			depto();
			linea();
			grupo();
		});

		function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}
			else{
				$("#nom_depto").attr("disabled","");
			}
		}

		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}
			else{
				$("#nom_linea").attr("disabled","");
			}
		}

		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}
			else{
				$("#nom_grupo").attr("disabled","");
			}
		}
		';

		$filter = new DataFilter2('Filtro por Producto');

		$filter->db->select("a.existen AS existen,a.marca marca,a.tipo AS tipo,id,codigo,a.descrip,precio1,precio2,precio3,precio4,b.nom_grup AS nom_grup,b.grupo AS grupoid,c.descrip AS nom_linea,c.linea AS linea,d.descrip AS nom_depto,d.depto AS depto, activo ");
		$filter->db->from('sinv AS a');
		$filter->db->join('grup AS b','a.grupo=b.grupo','LEFT');
		$filter->db->join('line AS c','b.linea=c.linea', 'LEFT');
		$filter->db->join('dpto  d','c.depto=d.depto','LECT');
		//$filter->db->join('sinvfoto  e','e.codigo=a.codigo','LEFT');
		$filter->script($DepoScript);


		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo-> size=15;
		$filter->codigo->group = "Uno";

		$filter->barras = new inputField("C&oacute;digo de barras", "barras");
		$filter->barras -> size=25;
		$filter->barras->group = "Uno";

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip-> size=30;
		$filter->descrip->group = "Uno";

		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->db_name=("a.tipo");
		$filter->tipo->option("","Todos");
		$filter->tipo->option("Articulo","Art&iacute;culo");
		$filter->tipo->option("Servicio","Servicio");
		$filter->tipo->option("Descartar","Descartar");
		$filter->tipo->option("Consumo","Consumo");
		$filter->tipo->option("Fraccion","Fracci&oacute;n");
		$filter->tipo ->style='width:120px;';
		$filter->tipo->group = "Uno";

		$filter->clave = new inputField("Clave", "clave");
		$filter->clave -> size=15;
		$filter->clave->group = "Uno";

		$filter->activo = new dropdownField("Activo", "activo");
		$filter->activo->option('','Todos');
		$filter->activo->option('S','Si');
		$filter->activo->option('N','No');
		$filter->activo ->style='width:120px;';
		$filter->activo->group = "Uno";

		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		//$filter->proveed->clause ="in";
		$filter->proveed->db_name='CONCAT_WS("-",`a`.`prov1`, `a`.`prov2`, `a`.`prov3`)';
		//$filter->proveed->db_name='( a.prov1, a.prov2, a.prov3 )';
		$filter->proveed -> size=10;
		$filter->proveed->group = "Dos";

		$filter->depto2 = new inputField("Departamento", "nom_depto");
		$filter->depto2->db_name="d.descrip";
		$filter->depto2 -> size=5;
		$filter->depto2->group = "Dos";

		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, CONCAT(depto,'-',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$filter->depto->in="depto2";
		$filter->depto->group = "Dos";
		$filter->depto->style='width:190px;';

		$filter->linea = new inputField("Linea", "nom_linea");
		$filter->linea->db_name="c.descrip";
		$filter->linea -> size=5;
		$filter->linea->group = "Dos";

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="c.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$filter->linea2->in="linea";
		$filter->linea2->group = "Dos";
		$filter->linea2->style='width:190px;';

		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, CONCAT(linea,'-',descrip) descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo2 = new inputField("Grupo", "nom_grupo");
		$filter->grupo2->db_name="b.nom_grup";
		$filter->grupo2 -> size=5;
		$filter->grupo2->group = "Dos";

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$filter->grupo->in="grupo2";
		$filter->grupo->group = "Dos";
		$filter->grupo->style='width:190px;';

		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option("","Seleccione un Departamento primero");
		}

		$filter->marca = new dropdownField("Marca", "marca");
		$filter->marca->option('','Todas');
		$filter->marca->options("SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca"); 
		$filter->marca->style='width:190px;';
		$filter->marca->group = "Dos";

		$filter->buttons("reset","search");
		$filter->build("dataformfiltro");

		$uri = "inventario/sinv/dataedit/show/<#codigo#>";

		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='".base_url()."inventario/sinv/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:recalcular(\"P\")'>";
		$mtool .= img(array('src' => 'images/recalcular.jpg', 'alt' => 'Recalcular Precios', 'title' => 'Recalcular Precios','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:recalcular(\"M\")'>";
		$mtool .= img(array('src' => 'images/recalcular.png', 'alt' => 'Recalcular Margenes', 'title' => 'Recalcular Margenes','border'=>'0','height'=>'28'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:redondear()'>";
		$mtool .= img(array('src' => 'images/redondear.jpg', 'alt' => 'Redondear Precios', 'title' => 'Redondear Precios','border'=>'0','height'=>'30'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:auprec()'>";
		$mtool .= img(array('src' => 'images/aprecios.gif', 'alt' => 'Aumento de Precios', 'title' => 'Aumento de Precios','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."inventario/etiqueta_sinv/menu', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600"'.'>';
		$mtool .= img(array('src' => 'images/etiquetas.jpg', 'alt' => 'Etiquetas', 'title' => 'Etiquetas','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."reportes/index/sinv', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600" width="900" '.'>';
		$mtool .= img(array('src' => 'images/reportes.gif', 'alt' => 'Reportes', 'title' => 'Reportes','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "</tr></table>";

		$grid = new DataGrid($mtool);
		$grid->order_by("codigo","asc");
		$grid->per_page = 50;
		$link=anchor('/inventario/sinv/dataedit/show/<#id#>','<#codigo#>');

		$uri_2  = anchor('inventario/sinv/dataedit/modify/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar','height'=>'12','title'=>'Editar')));

		$uri_2 .= "<a href='javascript:void(0);' ";
		$uri_2 .= 'onclick="window.open(\''.base_url()."inventario/sinv/consulta/<#id#>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600"'.'>';
		$uri_2 .= img(array('src'=>'images/estadistica.jpeg','border'=>'0','alt'=>'Consultar','height'=>'12','title'=>'Consultar'));
		$uri_2 .= "</a>";

		$uri_2 .= "<a href='javascript:void(0);' ";
		$uri_2 .= 'onclick="window.open(\''.base_url()."inventario/fotos/dataedit/<#id#>/create', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="600"'.'>';
		$uri_2 .= img(array('src' => 'images/foto.gif', 'alt' => 'Foto', 'title' => 'Foto','border'=>'0','height'=>'12'));
		$uri_2 .= "</a>";

		$uri_2 .= img(array('src'=>'images/<#activo#>.gif','border'=>'0','alt'=>'Estado','title'=>'Estado Activo/Inactivo'));

		$grid->column("Acci&oacute;n",$uri_2     ,"align='center'");
		$grid->column_orderby("C&oacute;digo",$link,"codigo");
		$grid->column_orderby("Descripci&oacute;n","descrip","descrip");
		$grid->column_orderby("Precio 1","<nformat><#precio1#></nformat>","precio1",'align=right');
		$grid->column_orderby("Precio 2","<nformat><#precio2#></nformat>","precio2",'align=right');
		$grid->column_orderby("Existencia","<nformat><#existen#></nformat>","existen",'align=right');
		$grid->column_orderby("Tipo","tipo","tipo");
		$grid->column_orderby("Grupo","grupoid","grupoid");
		$grid->column_orderby("Grupo","nom_grup","nom_grup");
		$grid->column_orderby("Linea","nom_linea","nom_linea");
		$grid->column_orderby("Depto","nom_depto","nom_depto");
		$grid->column_orderby("Precio 3","<nformat><#precio3#></nformat>","precio3",'align=right');
		$grid->column_orderby("Marca","marca","marca");

		$grid->add('inventario/sinv/dataedit/create');
		$grid->build('datagridST');

		$lastq = $this->db->last_query();
		$where = substr($lastq,stripos($lastq,"WHERE" ));
		$where = substr($where,0,stripos($where,"ORDER BY" ));

		$from = substr($lastq,stripos($lastq,"FROM" ));
		$from = substr($from,4,stripos($from,"WHERE" )-4);
		//echo $from;

		$id = $this->datasis->guardasesion(array("data1"=>$from,"data2"=>$where));
		
		$mSQL = "UPDATE $from SET a.precio1=a.precio1*, a.precio2=a.precio2*, a.precio3=a.precio3*, a.precio4=a.precio4* $where";
		//echo $from." id=$id  sesion:".$this->session->userdata('session_id');
		$link1  =site_url('inventario/sinv/redondear');
		$link2  =site_url('inventario/sinv/recalcular');
		$link3  =site_url("inventario/sinv/auprec/$id"); 
		
		$script = '
<script type="text/javascript">
function isNumeric(value) {
  if (value == null || !value.toString().match(/^[-]?\d*\.?\d*$/)) return false;
  return true;
};

function redondear(){
	var mayor=prompt("Redondear precios Mayores a");
	if( mayor==null){
		alert("Cancelado");
	} else {
		if( isNumeric(mayor) ){
			$.ajax({ url: "'.$link1.'/"+mayor,
			complete: function(){ alert(("Redondeo Finalizado")) }
			});
		} else {
			alert("Entrada no numerica");
		}
	}
};

function recalcular(mtipo){
	var seguro = true;
	if(mtipo == "P"){
		seguro = confirm("Recalcular margenes dejando fijos los precios ");
	} else {
		seguro = confirm("Recalcular margenes, dejando fijos los precios ");
	}
	if( seguro){
		$.ajax({ url: "'.$link2.'/"+mtipo,
			complete: function(){ alert(("Recalculo Finalizado")) }
		})
	}
};

function auprec(){
	var porcen=prompt("Porcentaje de Aumento?");
	if( porcen ==null){
		alert("Cancelado");
	} else {
		if( isNumeric(porcen) ){
			$.ajax({ url: "'.$link3.'/"+porcen,
			complete: function(){ alert(("Aumento Finalizado")) }
			});
		} else {
			alert("Entrada no numerica");
		}
	}
};
</script>
';

// *************************************
//
//       Para usar SuperTable
//
// *************************************
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


		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;

		$data["script"]  = script("jquery.js");
		$data["script"] .= script("plugins/jquery.numeric.pack.js");
		$data["script"] .= script("plugins/jquery.floatnumber.js");
		$data["script"] .= script('superTables.js');
		$data['script'] .= $script;

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');

		$data['extras']  = $extras;
		$data['title']   = heading('Maestro de Inventario ');

		$data["head"]   = $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}


	// *********************************************************************************************************
	//
	//   DATAEDIT
	//
	// *********************************************************************************************************
	function dataedit($status='',$id='' ) {
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataedit','dataobject');

		$link  =site_url('inventario/common/add_marc');
		$link4 =site_url('inventario/common/get_marca');
		$link5 =site_url('inventario/common/add_unidad');
		$link6 =site_url('inventario/common/get_unidad');
		$link7 =site_url('inventario/sinv/ultimo');
		$link8 =site_url('inventario/sinv/sugerir');
		$link9 =site_url('inventario/common/add_depto');
		$link10=site_url('inventario/common/get_depto');
		$link11=site_url('inventario/common/add_linea');
		$link12=site_url('inventario/common/get_linea');
		$link13=site_url('inventario/common/add_grupo');
		$link14=site_url('inventario/common/get_grupo');

		$link20=site_url('inventario/sinv/sinvcodigoexiste');
		$link21=site_url('inventario/sinv/sinvcodigo');
		$link25=site_url('inventario/sinv/sinvbarras');
		$link27=site_url('inventario/sinv/sinvpromo');

		$link28=site_url('inventario/sinv/sinvproveed/');
		$link29=site_url('inventario/sinv/sinvsprv/'.$id);

		$link30=site_url('inventario/sinv/sinvborrasuple/');
		$link35=site_url('inventario/sinv/sinvborraprv/');

		$link40=site_url('inventario/sinv/sinvdescu/'.$id);
		$link41=site_url('inventario/sinv/sinvcliente/');

		$script='
<script type="text/javascript">
function isNumeric(value) {
  if (value == null || !value.toString().match(/^[-]?\d*\.?\d*$/)) return false;
  return true;
};

$(document).ready(function() {

	$("#depto").change(function(){dpto_change(); });
	$("#linea").change(function(){ $.post("'.$link14.'",{ linea:$(this).val() },function(data){$("#grupo").html(data);}) });

	$("#tdecimal").change(function(){
		var clase;
		if($(this).attr("value")=="S") clase="inputnum"; else clase="inputonlynum";	
		$("#exmin").unbind();$("#exmin").removeClass(); $("#exmin").addClass(clase);
		$("#exmax").unbind();$("#exmax").removeClass(); $("#exmax").addClass(clase);
		$("#exord").unbind();$("#exord").removeClass(); $("#exord").addClass(clase);
		$("#exdes").unbind();$("#exdes").removeClass(); $("#exdes").addClass(clase);
		$(".inputnum").numeric(".");
		$(".inputonlynum").numeric("0");
	});
	requeridos(true);

	$( "#dialog:ui-dialog" ).dialog( "destroy" );

	var proveedor = $( "#proveedor" ),
		cod_prv = $( "#cod_prv" ),
		codigo = $( "#codigo" ),
		cod_cli = $( "#cod_cli" ),
		descuento = $( "#descuento" ),
		tipo = $( "#tipo" ),
		allFields = $( [] ).add( proveedor ).add( codigo ).add( cod_prv ),
		tips = $( ".validateTips" );

	$( "#sinvprv" ).dialog({
		autoOpen: false,
		height: 300,
		width: 350,
		modal: true,
		buttons: {
			"Guardar Codigo": function() {
				var bValid = true;
				allFields.removeClass( "ui-state-error" );

				bValid = bValid && checkLength( proveedor, "proveedor", 3, 50 );
				bValid = bValid && checkLength( cod_prv, "cod_prv", 1, 5 );
				bValid = bValid && checkLength( codigo, "codigo", 6, 15 );

				//bValid = bValid && checkRegexp( proveedor, /^[a-z]([0-9a-z_])+$/i, "Username may consist of a-z, 0-9, underscores, begin with a letter." );
				// From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
				//bValid = bValid ;
				if ( bValid ) {
					/*
					$( "#users tbody" ).append( "<tr>" +
						"<td>" + cod_prv.val() + "</"+"td>" + 
						"<td>" + proveedor.val() + "</"+"td>" + 
						"<td>" + codigo.val() + "</"+"td>" + 
					"</"+"tr>" );
					*/
					$.ajax({
						  url: "'.$link29.'/"+cod_prv.val()+"/"+codigo.val(),
						  //context: document.body,
						  success: function(msg){
						    alert("Terminado: "+msg);
						  }
					});					
					
					
					$( this ).dialog( "close" );
				}
			},
			Cancelar: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});
	$( "#sinvdescu" ).dialog({
		autoOpen: false,
		height: 350,
		width: 350,
		modal: true,
		buttons: {
			"Guardar Descuento": function() {
				var bValid = true;
				allFields.removeClass( "ui-state-error" );

				//bValid = bValid && checkLength( cliente,   "cliente",   3, 50 );
				//bValid = bValid && checkLength( cod_cli,   "cod_cli",   1,  5 );
				//bValid = bValid && checkLength( descuento, "descuento", 1, 15 );
				//bValid = bValid && checkLength( tipo, "tipo", 1, 1 );
				if ( bValid ) {
					$.ajax({
						url: "'.$link40.'/"+cod_cli.val()+"/"+descuento.val()+"/"+tipo.val(),
						success: function(msg){
							alert("Terminado: "+msg);
						}
					});
					$( this ).dialog( "close" );
				}
			},
			Cancelar: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});

	$( "#proveedor" ).autocomplete({
		source: function( req, add){
			$.ajax({
				url: "'.$link28.'",
				type: "POST",
				dataType: "json",
				data: "tecla="+req.term,
				success:
					function(data) {
						var sugiere = [];
						$.each(data,
							function(i, val){
								sugiere.push( val );
							}
						);
						add(sugiere);
					},
			})
		},
		minLength: 3,
		select: function(evento, ui){
			//$("#proveedor").val(ui.item.value.substr(0,ui.item.value.length-6));
			//$("#cod_prv").val(ui.item.value.substr(ui.item.value.length-6, 5));
			$("#cod_prv").val(ui.item.codigo);
		}
	});
	$( "#cliente" ).autocomplete({
		source: function( req, add){
			$.ajax({
				url: "'.$link41.'",
				type: "POST",
				dataType: "json",
				data: "tecla="+req.term,
				success:
					function(data) {
						var sugiere = [];
						$.each(data,
							function(i, val){
								sugiere.push( val );
							}
						);
						add(sugiere);
					},
			})
		},
		minLength: 3,
		select: function(evento, ui){
			$("#cod_cli").val(ui.item.codigo);
		}
	});
	$( "#maintabcontainer" ).tabs();
});

function updateTips( t ) {
	tips
		.text( t )
		.addClass( "ui-state-highlight" );
	setTimeout(function() {
		tips.removeClass( "ui-state-highlight", 1500 );
	}, 500 );
}

function checkLength( o, n, min, max ) {
	if ( o.val().length > max || o.val().length < min ) {
		o.addClass( "ui-state-error" );
		updateTips( "Length of " + n + " must be between " +
			min + " and " + max + "." );
		return false;
	} else {
		return true;
	}
}

function checkRegexp( o, regexp, n ) {
	if ( !( regexp.test( o.val() ) ) ) {
		o.addClass( "ui-state-error" );
		updateTips( n );
		return false;
	} else {
		return true;
	}
}
		
function dpto_change(){
	$.post("'.$link12.'",{ depto:$("#depto").val() },function(data){$("#linea").html(data);})
	$.post("'.$link14.'",{ linea:"" },function(data){$("#grupo").html(data);})
}

function ultimo(){
	$.ajax({
		url: "'.$link7.'",
		success: function(msg){
			alert( "El &uacute;ltimo c&oacute;digo ingresado fue: " + msg );
		}
	});
}

function sugerir(){
	$.ajax({
		url: "'.$link8.'",
		success: function(msg){
			if(msg){
				$("#codigo").val(msg);
			} else {
				alert("No es posible generar otra sugerencia. Coloque el c&oacute;digo manualmente");
			}
		}
	});
}

function add_marca(){
	marca=prompt("Introduza el nombre de la MARCA a agregar");
	if(marca==null){
	} else {
		$.ajax({
			type: "POST",
			processData:false,
			url: "'.$link.'",
			data: "valor="+marca,
			success: function(msg){
				if(msg=="s.i"){
					marca=marca.substr(0,30);
					$.post("'.$link4.'",{ x:"" },function(data){$("#marca").html(data);$("#marca").val(marca);})
				} else {
					alert("Disculpe. En este momento no se ha podido agregar la marca, por favor intente mas tarde");
				}
			}
		});
	}
}

function add_unidad(){
	unidad=prompt("Introduza el nombre de la UNIDAD a agregar");
	if(unidad==null){
	}else{
		$.ajax({
		 type: "POST",
		 processData:false,
			url: "'.$link5.'",
			data: "valor="+unidad,
			success: function(msg){
				if(msg=="s.i"){
					unidad=unidad.substr(0,8);					
					$.post("'.$link6.'",{ x:"" },function(data){$("#unidad").html(data);$("#unidad").val(unidad);})
				}
				else{
					alert("Disculpe. En este momento no se ha podido agregar la unidad, por favor intente mas tarde");
				}
			}
		});
	}
}

function add_depto(){
	depto=prompt("Introduza el nombre del DEPARTAMENTO a agregar");
	if(depto==null){
	}else{
		$.ajax({
		 type: "POST",
		 processData:false,
			url: "'.$link9.'",
			data: "valor="+depto,
			success: function(msg){
				if(msg=="Y.a-Existe"){
					alert("Ya existe un Departamento con esa Descripcion");
				}
				else{
					if(msg=="N.o-SeAgrego"){
						alert("Disculpe. En este momento no se ha podido agregar el departamento, por favor intente mas tarde");
					}else{
						$.post("'.$link10.'",{ x:"" },function(data){$("#depto").html(data);$("#depto").val(msg);})
					}
				}
			}
		});
	}
}

function add_linea(){
	deptoval=$("#depto").val();
	if(deptoval==""){
		alert("Debe seleccionar un Departamento al cual agregar la linea");
		}else{
		linea=prompt("Introduza el nombre de la LINEA a agregar al DEPARTAMENTO seleccionado");
		if(linea==null){
		}else{			
			$.ajax({
			 type: "POST",
			 processData:false,
				url: "'.$link11.'",
				data: "valor="+linea+"&&valor2="+deptoval,
				success: function(msg){
					if(msg=="Y.a-Existe"){
						alert("Ya existe una Linea con esa Descripcion");
					}
					else{
						if(msg=="N.o-SeAgrego"){
							alert("Disculpe. En este momento no se ha podido agregar la linea, por favor intente mas tarde");
						}else{
							$.post("'.$link12.'",{ depto:deptoval },function(data){$("#linea").html(data);$("#linea").val(msg);})
						}
					}
				}
			});
		}
	}
}

function add_grupo(){
	lineaval=$("#linea").val();
	deptoval=$("#depto").val();
	if(lineaval==""){
		alert("Debe seleccionar una Linea a la cual agregar el departamento");
	}else{
	grupo=prompt("Introduza el nombre del GRUPO a agregar a la LINEA seleccionada");
		if(grupo==null){
		}else{
			$.ajax({
			 type: "POST",
		 processData:false,
				url: "'.$link13.'",
				data: "valor="+grupo+"&&valor2="+lineaval+"&&valor3="+deptoval,
				success: function(msg){
					if(msg=="Y.a-Existe"){
						alert("Ya existe una Linea con esa Descripcion");
					}
					else{
						if(msg=="N.o-SeAgrego"){
							alert("Disculpe. En este momento no se ha podido agregar la linea, por favor intente mas tarde");
						}else{
							$.post("'.$link14.'",{ linea:lineaval },function(data){$("#grupo").html(data);$("#grupo").val(msg);})
						}
					}
				}
			});
		}
	}
};

function sinvcodigo(mviejo){
	var yurl = "";
	//var mcodigo=jPrompt("Ingrese el Codigo a cambiar ");
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
							if (r) { sinvcodigocambia("S", mviejo, mcodigo); }
							}
						);
					} else {
						jConfirm(
							"Sustitur el codigo actual  por: <center><h2 style=\"background: #ddeedd\">"+mcodigo+"</"+"h2></"+"center> <p>Al cambiar de codigo el producto, todos los<br/> movimientos y estadisticas se cambiaran<br/> correspondientemente.</"+"p> ",
							"Confirmar cambio de codigo",
							function(r) {
								if (r) { sinvcodigocambia("N", mviejo, mcodigo); }
							}
						)
					}
				},
				error: function(h,t,e) { jAlert("Error..codigo="+yurl+" ",e) } 
			});
		}
	})
};

function sinvcodigocambia( mtipo, mviejo, mcodigo ) {
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

function sinvbarras(mcodigo){
	var yurl = "";
	jPrompt("Nuevo Codigo de Barras","" ,"Codigo Barras", function(mbarras){
		if( mbarras==null ){
			jAlert("Cancelado por el usuario","Informacion");
		} else if( mbarras=="" ) {
			jAlert("Cancelado,  Codigo vacio","Informacion");
		} else {
			$.ajax({
				url: "'.$link25.'",
				global: false,
				type: "POST",
				data: ({ id : mcodigo, codigo : encodeURIComponent(mbarras) }),
				dataType: "text",
				async: false,
				success: function(sino)  { jAlert( sino,"Informacion")},
				error:   function(h,t,e) { jAlert("Error..codigo="+mbarras+" <p>"+e+"</"+"p>","Error") }
			});
		}
	})
};
function sinvpromo(mcodigo){
	jPrompt("Descuento Promocional","" ,"Descuento", function(margen){
		if( margen==null ){
			jAlert("Cancelado por el usuario","Informacion");
		} else if( margen=="" ) {
			jAlert("Cancelado,  Codigo vacio","Informacion");
		} else {
			if (isNumeric(margen)) {
				$.ajax({
					url: "'.$link27.'",
					global: false,
					type: "POST",
					data: ({ id : mcodigo, margen : margen }),
					dataType: "text",
					async: false,
					success: function(sino)  { jAlert( sino,"Informacion")},
					error:   function(h,t,e) { jAlert("Error..codigo="+margen+" <p>"+e+"</"+"p>","Error") }
				});
			} else { jAlert("Entrada no numerica","Alerta") }
		}
	})
};
// Descuento por Cliente
function sinvdescu(mcodigo){
	$( "#sinvdescu" ).dialog( "open" );
};
// Codigo de producto en el Proveedor
function sinvproveed(mcodigo){
	$( "#sinvprv" ).dialog( "open" );
};

function sinvborrasuple(mcodigo){
	jConfirm(
		"Desea eliminar este codigo suplementario?<p><strong>"+mcodigo+"</"+"strong></"+"p>",
		"Confirmar Borrado",
		function(r){
			if (r) {
			$.ajax({
				url: "'.$link30.'",
				global: false,
				type: "POST",
				data: ({ codigo : mcodigo }),
				dataType: "text",
				async: false,
				success: function(sino)  { jAlert( sino,"Informacion")},
				error:   function(h,t,e) { jAlert("Error..codigo="+mcodigo+" <p>"+e+"</"+"p>","Error") }
			});
			}
		}
	);
};


function sinvborraprv(mproveed, mcodigo){
	jConfirm(
		"Desea eliminar este codigo de proveedor?<p><strong>"+mcodigo+"</"+"strong></"+"p>",
		"Confirmar Borrado",
		function(r){
			if (r) {
			$.ajax({
				url: "'.$link35.'",
				global: false,
				type: "POST",
				data: ({ proveed : mproveed, codigo : mcodigo }),
				dataType: "text",
				async: false,
				success: function(sino)  { jAlert( sino,"Informacion")},
				error:   function(h,t,e) { jAlert("Error..codigo="+mcodigo+" <p>"+e+"</"+"p>","Error") }
			});
			}
		}
	);
};


</script>
';

		$do = new DataObject("sinv");
		if($status=="create" && !empty($id)){
			$do->load($id);
			$do->set('codigo', '');
		}

		$edit = new DataEdit("Maestro de Inventario", $do);
		$edit->pre_process('insert','_pre_inserup');
		$edit->pre_process('update','_pre_inserup');
		$edit->pre_process('delete','_pre_del');

		$edit->script($script,"create");
		$edit->script($script,"modify");
		$edit->back_url = site_url("inventario/sinv/filteredgrid");

		$ultimo='<a href="javascript:ultimo();" title="Consultar ultimo c&oacute;digo ingresado"> Consultar ultimo c&oacute;digo</a>';
		$sugerir='<a href="javascript:sugerir();" title="Sugerir un C&oacute;digo aleatorio">Sugerir C&oacute;digo </a>';

		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size=15;
		$edit->codigo->maxlength=15;
		$edit->codigo->rule = "trim|required|strtoupper|callback_chexiste";
		$edit->codigo->mode="autohide";
		$edit->codigo->append($sugerir);
		$edit->codigo->append($ultimo);

		$edit->alterno = new inputField("C&oacute;digo Alterno", "alterno");
		$edit->alterno->size=15;  
		$edit->alterno->maxlength=15;
		$edit->alterno->rule = "trim|strtoupper|unique";
		
		$edit->enlace  = new inputField("C&oacute;digo Caja", "enlace");
		$edit->enlace ->size=15;
		$edit->enlace->maxlength=15;
		$edit->enlace->rule = "trim|strtoupper";
				
		$edit->barras = new inputField("C&oacute;digo Barras", "barras");
		$edit->barras->size=15;
		$edit->barras->maxlength=15;
		$edit->barras->rule = "trim";
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style='width:100px;';
		$edit->tipo->option("Articulo","Art&iacute;culo" );
		$edit->tipo->option("Servicio","Servicio");
		$edit->tipo->option("Descartar","Descartar");
		$edit->tipo->option("Consumo","Consumo");
		$edit->tipo->option("Fraccion","Fracci&oacute;n");
		$edit->tipo->option("Lote","Lote");
		
		$AddUnidad='<a href="javascript:add_unidad();" title="Haz clic para Agregar una unidad nueva">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->unidad = new dropdownField("Unidad","unidad");
		$edit->unidad->style='width:100px;';
		$edit->unidad->option("","");
		$edit->unidad->options("SELECT unidades, unidades as valor FROM unidad ORDER BY unidades");
		//$edit->unidad->append($AddUnidad);

		$edit->clave = new inputField("Clave", "clave");
		$edit->clave->size=10;
		$edit->clave->maxlength=8;
		$edit->clave->rule = "trim|strtoupper";

		$AddDepto='<a href="javascript:add_depto();" title="Haz clic para Agregar un nuevo Departamento">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->depto = new dropdownField("Departamento", "depto");
		$edit->depto->rule ="required";
		$edit->depto->style='width:300px;white-space:nowrap;';
		$edit->depto->option("","Seleccione un Departamento");
		$edit->depto->options("SELECT depto, CONCAT(depto,'-',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		//$edit->depto->append($AddDepto);

		$AddLinea='<a href="javascript:add_linea();" title="Haz clic para Agregar una nueva Linea;">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->linea = new dropdownField("L&iacute;nea","linea");
		$edit->linea->rule ="required";
		$edit->linea->style='width:300px;';
		//$edit->linea->append($AddLinea);
		$depto=$edit->getval('depto');
		if($depto!==FALSE){
			$edit->linea->options("SELECT linea, CONCAT(LINEA,'-',descrip) descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$edit->linea->option("","Seleccione un Departamento primero");
		}

		$AddGrupo='<a href="javascript:add_grupo();" title="Haz clic para Agregar un nuevo Grupo;">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->rule="required";
		$edit->grupo->style='width:300px;';

		//$edit->grupo->append($AddGrupo);
		$linea=$edit->getval('linea');
		if($linea!==FALSE){
			$edit->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$edit->grupo->option("","Seleccione un Departamento primero");
		}

		$edit->comision  = new inputField("Comisi&oacute;n %", "comision");
		$edit->comision ->size=7;
		$edit->comision->maxlength=5;
		$edit->comision->css_class='inputnum';
		$edit->comision->rule='numeric|callback_positivo|trim';

		$edit->fracci  = new inputField("Fraccion x Unid.", "fracci");
		$edit->fracci ->size=10;
		$edit->fracci->maxlength=4;
		$edit->fracci->css_class='inputnum';
		$edit->fracci->rule='numeric|callback_positivo|trim';

		$edit->activo = new dropdownField("Activo", "activo");
		$edit->activo->style='width:50px;';
		$edit->activo->option("S","Si" );
		$edit->activo->option("N","No" );

		$edit->serial2 = new freeField("","free","Serial");
		$edit->serial2->in="activo"; 

		$edit->serial = new dropdownField ('Usa Seriales', 'serial');
		$edit->serial->style='width:50px;';
		$edit->serial->option("N","No" );
		$edit->serial->option("S","Si" );
		$edit->serial->in="activo";

		$edit->tdecimal2 = new freeField("","free","Usa Decimales");
		$edit->tdecimal2->in="activo"; 

		$edit->tdecimal = new dropdownField("Usa Decimales", "tdecimal");
		$edit->tdecimal->style='width:50px;';
		$edit->tdecimal->option("N","No" );
		$edit->tdecimal->option("S","Si" );
		$edit->tdecimal->in="activo"; 

		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size=45;
		$edit->descrip->maxlength=45;
		$edit->descrip->rule = "trim|required|strtoupper";

		$edit->descrip2 = new inputField("Descripci&oacute;n 2", "descrip2");
		$edit->descrip2->size=45;
		$edit->descrip2->maxlength=45;
		$edit->descrip2->rule = "trim|strtoupper";

		$edit->peso  = new inputField("Peso", "peso");
		$edit->peso->size=10;
		$edit->peso->maxlength=12;
		$edit->peso->css_class='inputnum';
		$edit->peso->rule='numeric|callback_positivo|trim';


		$edit->alto = new inputField("Alto", "alto");
		$edit->alto->size=10;
		$edit->alto->maxlength=12;
		$edit->alto->css_class='inputnum';
		$edit->alto->rule='numeric|callback_positivo|trim';

		$edit->ancho = new inputField("Ancho", "ancho");
		$edit->ancho->size=10;
		$edit->ancho->maxlength=12;
		$edit->ancho->css_class='inputnum';
		$edit->ancho->rule='numeric|callback_positivo|trim';

		$edit->largo = new inputField("Largo", "largo");
		$edit->largo->size=10;
		$edit->largo->maxlength=12;
		$edit->largo->css_class='inputnum';
		$edit->largo->rule='numeric|callback_positivo|trim';



		$edit->garantia = new inputField("Garantia", "garantia");
		$edit->garantia->size=5;
		$edit->garantia->maxlength=3;
		$edit->garantia->css_class='inputonlynum';
		$edit->garantia->rule='numeric|callback_positivo|trim';

		$AddMarca='<a href="javascript:add_marca();" title="Haz clic para Agregar una marca nueva">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->marca = new dropdownField("Marca", "marca");
		$edit->marca->rule = 'required';
		$edit->marca->style='width:180px;';
		$edit->marca->option("","");  
		$edit->marca->options("SELECT marca as codigo, marca FROM marc ORDER BY marca");
		$edit->marca->append($AddMarca);

		$edit->modelo  = new inputField("Modelo", "modelo");
		$edit->modelo->size=24;  
		$edit->modelo->maxlength=20;
		$edit->modelo->rule = "trim|strtoupper";

		$edit->clase= new dropdownField("Clase", "clase");
		$edit->clase->style='width:100px;';
		$edit->clase->option('A',"Alta Rotacion");
		$edit->clase->option('B',"Media Rotacion");
		$edit->clase->option('C',"Baja Rotacion");
		$edit->clase->option('I',"Importacion Propia");

		$ivas=$this->datasis->ivaplica();
		$edit->iva = new dropdownField('IVA %', 'iva');
		foreach($ivas as $tasa=>$ivamonto){
			$edit->iva->option($ivamonto,nformat($ivamonto));
		}
		$edit->iva->style='width:100px;';
		
		$edit->exento = new dropdownField("Puede Exento", "exento");
		$edit->exento->style='width:50px;';
		$edit->exento->option("N","No" );
		$edit->exento->option("S","Si" );
		

		$edit->ultimo = new inputField("Ultimo", "ultimo");
		$edit->ultimo->css_class='inputnum';
		$edit->ultimo->size=10;
		$edit->ultimo->maxlength=13;
		$edit->ultimo->autcomplete=false;
		$edit->ultimo->onkeyup = "requeridos();";
		$edit->ultimo->rule="required";

		$edit->pond = new inputField("Promedio", "pond");
		$edit->pond->css_class='inputnum';
		$edit->pond->size=10;
		$edit->pond->maxlength=13;
		$edit->pond->autcomplete=false;
		$edit->pond->onkeyup = "requeridos();";
		$edit->pond->rule="required";

		$edit->standard = new inputField("Standard", "standard");
		$edit->standard->css_class='inputnum';
		$edit->standard->autcomplete=false;
		$edit->standard->size=10;
		$edit->standard->maxlength=13;

		$edit->formcal = new dropdownField("Base C&aacute;lculo", "formcal");
		$edit->formcal->style='width:110px;';
		//$edit->formcal->rule="required";
		//$edit->formcal->option("","Seleccione" );
		$edit->formcal->option("U","Ultimo" );
		$edit->formcal->option("P","Promedio" );
		$edit->formcal->option("M","Mayor" );
		$edit->formcal->onchange = "requeridos();calculos('I');";

		$edit->redecen = new dropdownField("Redondear", "redecen");
		$edit->redecen->style='width:110px;';
		$edit->redecen->option("N","No Cambiar");
		$edit->redecen->option("M","Solo un Decimal "  );
		$edit->redecen->option("F","Sin Decimales");
		$edit->redecen->option("D","Decenas" );  
		$edit->redecen->option("C","Centenas"  );
		
		//$edit->redecen->onchange = "redon();";

		for($i=1;$i<=4;$i++){
			$objeto="margen$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=6;
			$edit->$objeto->onkeyup = "calculos('I');";
			$edit->$objeto->autcomplete=false;
			$edit->$objeto->rule="required";

			//$objeto="Ebase$i";
			//$edit->$objeto = new freeField("","","Precio $i");
			//$edit->$objeto->in="margen$i";

			$objeto="base$i";
			$edit->$objeto = new inputField("Base $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->autcomplete=false;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onkeyup = "cambiobase('I');";
			$edit->$objeto->rule="required";

			//$objeto="Eprecio$i";
			//$edit->$objeto = new freeField("","","Precio + I.V.A. $i");
			//$edit->$objeto->in="margen$i";

			$objeto="precio$i";
			$edit->$objeto = new inputField("Precio $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->autcomplete=false;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onkeyup = "cambioprecio('I');";
			$edit->$objeto->rule="required";
		}

		$edit->existen = new inputField("Cantidad Actual","existen");
		$edit->existen->size=10;
		$edit->existen->readonly = true;
		$edit->existen->css_class='inputonlynum';
		$edit->existen->style='background:#F5F6CE;';

		$edit->exmin = new inputField("Minimo", "exmin");
		$edit->exmin->size=10;
		$edit->exmin->maxlength=12;
		$edit->exmin->css_class='inputonlynum';
		$edit->exmin->rule='numeric|callback_positivo|trim';

		$edit->exmax = new inputField("Maximo", "exmax");
		$edit->exmax->size=10;
		$edit->exmax->maxlength=12;
		$edit->exmax->css_class='inputonlynum';
		$edit->exmax->rule='numeric|callback_positivo|trim';

		$edit->exord = new inputField("Orden Proveedor","exord");
		$edit->exord->readonly = true;
		$edit->exord->size=10;
		$edit->exord->css_class='inputonlynum';
		$edit->exord->style='background:#F5F6CE;';

		$edit->exdes = new inputField("Pedidos Cliente","exdes");
		$edit->exdes->readonly = true;
		$edit->exdes->size=10;
		$edit->exdes->css_class='inputonlynum';
		$edit->exdes->style='background:#F5F6CE;';

		$edit->fechav = new dateField("Ultima Venta",'fechav','d/m/Y');
		$edit->fechav->readonly = true;
		//$edit->fechav->when =array("show");
		$edit->fechav->size=10;

		for($i=1;$i<=3;$i++){
			$objeto="pfecha$i";
			$edit->$objeto = new dateField("Fecha $i",$objeto,'d/m/Y');
			$edit->$objeto->when =array("show");
			$edit->$objeto->size=10;

			$objeto="Eprepro$i";
			$edit->$objeto = new freeField("","","Precio");
			$edit->$objeto->in="pfecha$i";
			$edit->$objeto->when =array("show");

			$objeto="prepro$i";
			$edit->$objeto = new inputField("",$objeto);
			$edit->$objeto->when =array("show");
			$edit->$objeto->size=10;
			$edit->$objeto->in="pfecha$i";

			$objeto="prov$i";
			$edit->$objeto = new inputField("",$objeto);
			$edit->$objeto->when =array("show");
			$edit->$objeto->size=10;
			//$edit->$objeto->in="pfecha$i";

			$objeto="Eprov$i";
			$edit->$objeto = new freeField("","","Proveedor");
			$edit->$objeto->in="pfecha$i";
			$edit->$objeto->when =array("show");

			if($edit->_status=="show"){
				$prov=$edit->_dataobject->get("prov".$i);
				$proveed=$this->datasis->dameval("SELECT nombre FROM sprv WHERE proveed='$prov' LIMIT 1");
				$objeto="proveed$i";
				$edit->$objeto= new freeField("","",$proveed);
				$edit->$objeto->in="pfecha$i";
			}
		}

		$codigo=$edit->_dataobject->get("codigo");
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array("show","modify");

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$style = '
<style type="text/css">
.maintabcontainer {width: 780px; margin: 5px auto;}
div#sinvprv label { display:block; }
div#sinvprv input { display:block; }
div#sinvprv input.text { margin-bottom:12px; width:95%; padding: .4em; }
div#sinvprv fieldset { padding:0; border:0; margin-top:20px; }
div#sinvprv h1 { font-size: 1.2em; margin: .6em 0; }
div#sinvdescu label { display:block; }
div#sinvdescu input { display:block; }
div#sinvdescu input.text { margin-bottom:12px; width:95%; padding: .4em; }
div#sinvdescu select { display:block; }
div#sinvdescu select.text { margin-bottom:12px; width:95%; padding: .4em; }
div#sinvdescu fieldset { padding:0; border:0; margin-top:20px; }
div#sinvdescu h1 { font-size: 1.2em; margin: .6em 0; }
.ui-dialog .ui-state-error { padding: .3em; }
.validateTips { border: 1px solid transparent; padding: 0.3em; }
</style>
';
		$mcodigo = $edit->codigo->value;
		$mfdesde = $this->datasis->dameval("SELECT ADDDATE(MAX(fecha),-30) FROM costos WHERE codigo='".addslashes($mcodigo)."'");
		$mfhasta  = $this->datasis->dameval("SELECT MAX(fecha) FROM costos WHERE codigo='".addslashes($mcodigo)."'");

		$extras = '
<div style="display: none">
	<form action="'.base_url().'/inventario/kardex/filteredgrid/search/osp" method="post" id="kardex" name="kardex" target="kpopup">
		<input type="text" name="codigo" value="'.$mcodigo.'" />
		<input type="text" name="ubica"  value="" />
		<input type="text" name="fecha"  value="'.dbdate_to_human($mfdesde).'" />
		<input type="text" name="fechah" value="'.dbdate_to_human($mfhasta).'" />
		<input type="submit" />
	</form>
</div>
<div id="sinvprv" title="Agregar codigo de Proveedor">
	<p class="validateTips">Codigo del proveedor para este producto</p>
	<form>
	<fieldset>
		<label for="proveedor">Proveedor</label>
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td>
					<input type="text" size="80" name="proveedor" id="proveedor" class="text ui-widget-content ui-corner-all" />
				</td>
				<td>
					<input type="text" readonly="readonly" size="8" name="cod_prv" id="cod_prv" class="text ui-widget-content ui-corner-all" />
				</td>
			</tr>
		</table>
		<label for="codigo">Codigo</label>
		<input type="text" name="codigo" id="codigo" value="" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	</form>
</div>
<div id="sinvdescu" title="Agregar Descuento">
	<p class="validateTips">Descuento para este producto</p>
	<form>
	<fieldset>
		<label for="cliente">Cliente</label>
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td>
					<input type="text" size="80" name="cliente" id="cliente" class="text ui-widget-content ui-corner-all" />
				</td>
				<td>
					<input type="text" readonly="readonly" size="8" name="cod_cli" id="cod_cli" class="text ui-widget-content ui-corner-all" />
				</td>
			</tr>
		</table>
		<label for="descuento">Porcentaje %</label>
		<input type="text" name="descuento" id="descuento" value="" class="text ui-widget-content ui-corner-all" />
		<label for="descuento">Aplicacion del Porcentaje</label>
		<select name="tipo" id="tipo" value="D" class="text ui-widget-content ui-corner-all" >
			<option value="D">Descuento: Precio1 - Porcentaje</option>
			<option value="A">Aumento: Costo + Porcentaje</option>
		</select>
	</fieldset>
	</form>
</div>
<script type="text/javascript">
function submitkardex() {
	window.open("", "kpopup", "width=800,height=600,resizeable,scrollbars");
	document.kardex.submit();
}
</script>
';

		$smenu['link']   = barra_menu('301');

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_sinv', $conten,true);

		$data["script"]   = script("jquery.js");
		$data["script"]  .= script("jquery-ui.js");
		$data["script"]  .= script("jquery.alerts.js");
		$data["script"]  .= script("plugins/jquery.blockUI.js");
		$data["script"]  .= script("plugins/jquery.numeric.pack.js");
		$data["script"]  .= script("plugins/jquery.floatnumber.js");
		$data["script"]  .= script("sinvmaes.js");
		$data["script"]  .= $script;

		$data['style']	 = style("jquery.alerts.css");
		$data['style']	.= style("redmond/jquery-ui.css");
		$data['style']	.= $style;
		
		$data['extras']  = $extras;

		$data["head"]   = $this->rapyd->get_head();

		$data['title']   = heading( substr($edit->descrip->value,0,30) );

		$this->load->view('view_ventanas', $data);
	}

	function _pre_inserup($do){
		for($i=1;$i<5;$i++){
			$prec='precio'.$i;
			$$prec=round($do->get($prec),2); //optenemos el precio
		}

		if($precio1>=$precio2 && $precio2>=$precio3 && $precio3>=$precio4){
			$formcal= $do->get('formcal');
			$iva= $do->get('iva');
			$costo=($formcal=='U')? $do->get('ultimo'):($formcal=='P')? $do->get('pond'):($do->get('pond')>$do->get('ultimo'))? $do->get('pond') : $do->get('ultimo');

			for($i=1;$i<5;$i++){
				$prec='precio'.$i;
				$base='base'.$i;
				$marg='margen'.$i;

				$$base=$$prec*100/(100+$iva);   //calculamos la base
				$$marg=100-($costo*100/$$base); //calculamos el margen

				$do->set($prec,round($$prec,2));
				$do->set($base,round($$base,2));
				$do->set($marg,round($$marg,2));
			}
			return true;
		}else{
			$do->error_message_ar['pre_upd'] = 'Los precios deben cumplir con:<br> Precio 1 mayor o igual al Precio 2 mayor o igual al  Precio 3 mayor o igual al Precio 4';
			return false;
		}
	}

	/* REDONDEA LOS PRECIOS DE TODOS LOS PRODUCTOS */
	function redondear($maximo) {
		$maximo = $this->uri->segment($this->uri->total_segments());
		$manterior = $this->datasis->traevalor("SINVREDONDEO");
		if (!empty($manterior)) {
			if ($manterior > $maximo ) {
				$this->db->simple_query("UPDATE sinv SET redecen='F' WHERE precio1<=$anterior");
			}
		}
		$this->datasis->ponevalor("SINVREDONDEO",$maximo);
		$this->db->update_string("sinv", array("redecen"=>'N'), "precio1<=$maximo");
		$this->db->call_function("sp_sinv_redondea");
		logusu('SINV',"Redondea Precios $maximo");
	}

	/* RECALCULA LOS PRECIOS DE TODOS LOS PRODUCTOS */
	function recalcular() {
		$mtipo = $this->uri->segment($this->uri->total_segments());
		$this->db->call_function("sp_sinv_recalcular", $mtipo );
		$this->db->call_function("sp_sinv_redondea");
		logusu('SINV',"Recalcula Precios $mtipo");
	}


	// **************************************
	//
	// -- Aumento de Precios -- //
	//
	// **************************************
	function auprec() {
		$porcent = $this->uri->segment($this->uri->total_segments());
		$id = $this->uri->segment($this->uri->total_segments()-1);
		$data = $this->datasis->damesesion($id);
		
		$from = $data['data1'];
		$where  = $data['data2'];

		// Respalda los precios anteriores
		$mN = $this->datasis->prox_sql('nsinvplog');
		$ms_codigo = $this->session->userdata('usuario');
		$mSQL = "INSERT INTO sinvplog ";
		$mSQL .= "SELECT '".$mN."', '".addslashes($ms_codigo)."', now(), curtime(), a.codigo, a.precio1, a.precio2, a.precio3, a.precio4 ";
		$mSQL .= "FROM $from"." ".$where;
		$this->db->simple_query($mSQL);

		$mSQL = "SET 
			a.precio1=ROUND(a.precio1*(100+$porcent)/100,2), 
			a.precio2=ROUND(a.precio2*(100+$porcent)/100,2), 
			a.precio3=ROUND(a.precio3*(100+$porcent)/100,2), 
			a.precio4=ROUND(a.precio4*(100+$porcent)/100,2)";
		//echo "UPDATE ".$from." ".$mSQL." ".$where;
		$this->db->simple_query("UPDATE ".$from." ".$mSQL." ".$where);
		$this->db->call_function("sp_sinv_recalcular", "M" );
		$this->db->call_function("sp_sinv_redondea");


	}

		


	//*****************************
	//
	//  Cambia el Codigo
	//
	function sinvcodigoexiste(){
		$id = rawurldecode($this->input->post('codigo'));
		//$id = $this->uri->segment($this->uri->total_segments());
		$existe = $this->datasis->dameval("SELECT count(*) FROM sinv WHERE codigo='".addslashes($id)."'");
		$devo = 'N '.$id;
		if ($existe > 0 ) {
			$devo  ='S';
			$devo .= $this->datasis->dameval("SELECT descrip FROM sinv WHERE codigo='".addslashes($id)."'");
		}
		echo $devo;
	}

	// Cambia el codigo	
	function sinvcodigo() {
		$mexiste  = $this->input->post('tipo');
		$mcodigo  = rawurldecode($this->input->post('codigo'));
		$mviejoid = $this->input->post('viejo');

		$mviejo  = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$mviejoid ");
		//echo "$mexiste  $mcodigo  $mviejo ";
		
		if ( $mexiste=='S' ) {
			$mSQL = "DELETE FROM sinv WHERE codigo='".addslashes($mviejo)."'";
			$this->db->simple_query($mSQL);
		} else {
			$mSQL = "UPDATE sinv SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."'";
			$this->db->simple_query($mSQL);
		}

		if ( $mexiste=='S' ) {
			$mSQL  = "SELECT * FROM itsinv WHERE codigo='".addslashes($mviejo)."'";
			$query = $this->db->query($mSQL);
			$mexisten = 0;
			if ($query->num_rows() > 0 ) {
				foreach ($query->result() as $row ) {
					$mSQL = "UPDATE itsinv SET existen=existen+".$row->existen."
						WHERE codigo='".addslashes($mcodigo)."' AND alma='".addslashes($row->alma)."'";
					$this->db->simple_query($mSQL);
					$mexisten += $row->existen;
				}				
			}
			//Actualiza sinv
			$mSQL = "UPDATE sinv SET existen=exiten+".$mexisten." WHERE codigo='".addslashes($mcodigo)."'";
			// Borra los items
			$mSQL = "DELETE FROM itsinv WHERE codigo='".addslashes($mviejo)."'";
			$this->db->simple_query($mSQL);
		} else { 
			$mSQL = "UPDATE itsinv SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
			$this->db->simple_query($mSQL);
		}

		$mSQL = "UPDATE itstra SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."'";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itscst SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."'";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE sitems SET codigoa='".addslashes($mcodigo)."' WHERE codigoa='".addslashes($mviejo)."'";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itsnot SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itsnte SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itspre SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itssal SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itconv SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE seri SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itpfac SET codigoa='".addslashes($mcodigo)."' WHERE codigoa='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE itordc SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE invresu SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);
		
		$mSQL = "UPDATE IGNORE invresu SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);
		
		$mSQL = "UPDATE IGNORE barraspos SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE sinvfot SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		$mSQL = "UPDATE IGNORE sinvpromo SET codigo='".addslashes($mcodigo)."' WHERE codigo='".addslashes($mviejo)."' ";
		$this->db->simple_query($mSQL);

		logusu("SINV","Cambio codigo ".$mviejo."-->".$mcodigo);
	}

	// Codigos de barra suplementarios
	function sinvbarras() {
		$mid      = $this->input->post('id');
		$mbarras  = rawurldecode($this->input->post('codigo'));
		$mcodigo  = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$mid");
		$htmlcod  = addslashes($mcodigo);
		//echo "SELECT codigo FROM sinv WHERE id=$mid";
		
		//Busca si ya esta
		$check = $this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE codigo='$mbarras' OR barras='$mbarras' OR alterno='$mbarras' ");
		if ($check > 0 ) {
			echo "Codigo ya existen en Inventario";
		} else {
			$check = $this->datasis->dameval("SELECT COUNT(*) FROM barraspos WHERE suplemen='$mbarras' ");
			if ($check > 0 ) {
				echo "Codigo ya existen en codigos suplementarios";
			} else {
				$mSQL = "INSERT INTO barraspos SET codigo='$htmlcod', suplemen='$mbarras'";
				$this->db->simple_query($mSQL);
				logusu("SINV","Codigo de Barras Agregado".$mcodigo."-->".$mbarras);
				echo "Registro de Codigo Exitoso";
			}
		}
	}

	// Borra Codigo de barras suplementarios
	function sinvborrasuple() {
		$codigo   = $this->input->post('codigo');
		$mSQL = "DELETE FROM barraspos WHERE suplemen='$codigo'";
		$this->db->simple_query($mSQL);
		logusu("SINV","Eliminado Codigo Suplementario ".$codigo);
		echo "Codigo Eliminado";
	}

	// Borra Codigo de barras suplementarios
	function sinvborraprv() {
		$codigo   = $this->input->post('codigo');
		$proveed  = $this->input->post('proveed');
		
		$mSQL = "DELETE FROM sinvprov WHERE codigop='$codigo' AND proveed='$proveed'";
		$this->db->simple_query($mSQL);
		logusu("SINV","Eliminado Codigo de proveedor $codigo => $proveed");
		echo "Codigo Eliminado";
	}

	// Busca Proveedor por autocomplete
	function sinvproveed(){
		$mid   = $this->input->post('tecla');
		if (empty($mid)) $mid='AN';
		$mSQL  = "SELECT CONCAT(TRIM(nombre),' (',RPAD(proveed,5,' '),')') nombre, proveed codigo FROM sprv WHERE nombre LIKE '%".$mid."%' ORDER BY nombre LIMIT 10";
		$data = "[]";
		$query = $this->db->query($mSQL);
		$retArray = array();
		$retorno = array();
		if ($query->num_rows() > 0){
			foreach( $query->result_array() as  $row ) {
				$retArray['label'] = $row['nombre'];
				$retArray['codigo'] = $row['codigo'];
				array_push($retorno, $retArray);
			}
			$data = json_encode($retorno);
			//$ret = "{data:" . $data .",\n";
			//$ret .= "recordType : 'array'}";
		} else {
			$ret = '{data : []}';
		}
		echo $data;
	}

	// Busca Cliente por autocomplete
	function sinvcliente(){
		$mid   = $this->input->post('tecla');
		if (empty($mid)) $mid='AN';
		$mSQL  = "SELECT CONCAT(TRIM(nombre),' (',RPAD(cliente,5,' '),')') nombre, cliente codigo FROM scli WHERE nombre LIKE '%".$mid."%' ORDER BY nombre LIMIT 10";
		$data = "[]";
		$query = $this->db->query($mSQL);
		$retArray = array();
		$retorno = array();
		if ($query->num_rows() > 0){
			foreach( $query->result_array() as  $row ) {
				$retArray['label'] = $row['nombre'];
				$retArray['codigo'] = $row['codigo'];
				array_push($retorno, $retArray);
			}
			$data = json_encode($retorno);
		} else {
			$ret = '{data : []}';
		}
		echo $data;
	}

	
	// Crea el codigo segun el Proveedor
	function sinvsprv(){
		$codigo  = $this->uri->segment($this->uri->total_segments());
		$cod_prv = $this->uri->segment($this->uri->total_segments()-1);
		$id      = $this->uri->segment($this->uri->total_segments()-2);
		$mSQL = "REPLACE INTO sinvprov SELECT '$cod_prv' proveed, '$codigo' codigop, codigo FROM sinv WHERE id=$id ";
		$this->db->simple_query($mSQL);
		echo " codigo=$codigo guardado al prv $cod_prv " ;
		
	}

	// Promociones
	function sinvpromo() {
		$mid     = $this->input->post('id');
		$margen  = $this->input->post('margen');
		$mcodigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$mid");
		$htmlcod = addslashes($mcodigo);
		
		//Busca si ya esta
		$check = $this->datasis->dameval("SELECT count(*) FROM sinvpromo WHERE codigo='".$htmlcod."'");

		if ($check == 0 ) {
			$this->db->simple_query("INSERT INTO sinvpromo SET codigo='"+$htmlcod+"'");
		}
		
		if ( $margen == 0 ) {
			$mSQL = "DELETE FROM sinvpromo WHERE WHERE codigo='$htmlcod' ";
		} else {
			$mSQL = "UPDATE sinvpromo SET margen=$margen WHERE codigo='$htmlcod' ";
		}
		$this->db->simple_query($mSQL);
		logusu("SINV","Promocion ".$htmlcod."-->".$margen);
		echo "Cambio Exitoso";
	}


	// Promociones
	function sinvdescu() {
		$tipo     = $this->uri->segment($this->uri->total_segments());
		$porcent  = $this->uri->segment($this->uri->total_segments()-1);
		$cod_cli  = $this->uri->segment($this->uri->total_segments()-2);
		$id       = $this->uri->segment($this->uri->total_segments()-3);
		
		$codigo   = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$id");
		$htmlcod = addslashes($codigo);
		
		//Busca si ya esta
		$check = $this->datasis->dameval("SELECT count(*) FROM sinvpromo a JOIN sinv b ON a.codigo=b.codigo WHERE b.id=$id AND cliente='".$cod_cli."'");

		if ($check == 0 ) {
			$this->db->simple_query("INSERT INTO sinvpromo SET codigo='".$htmlcod."', cliente='$cod_cli'");
		}
		
		if ( $porcent == 0 ) {
			$mSQL = "DELETE FROM sinvpromo WHERE WHERE codigo='$htmlcod' AND cliente='$cod_cli'";
		} else {
			$mSQL = "UPDATE sinvpromo SET margen=$porcent, tipo='$tipo' WHERE codigo='$htmlcod' AND cliente='$cod_cli'";
		}
		$this->db->simple_query($mSQL);
		logusu("SINV","Promocion cliente $cod_cli codigo ".$htmlcod."-->".$porcent);
		
		echo "Descuento Guardado ";
		//porcent=$porcent, tipo=$tipo, cod_cli=$cod_cli, codigo=$htmlcod, check=$check\n";
		//echo "SELECT count(*) FROM sinvpromo a JOIN sinv b ON a.codigo=b.codigo WHERE b.id=$id AND cliente='".$cod_cli."'\n";
		//echo "INSERT INTO sinvpromo SET codigo='".$htmlcod."', cliente='$cod_cli'\n";
	}


	function cprecios(){
		$this->rapyd->uri->keep_persistence();
		
		$cpre=$this->input->post('pros');
		if($cpre!==false){
			$msj=$this->_cprecios();
		}else{
			$msj='';
		}
		
		$this->rapyd->load("datafilter2","datagrid");
		$mSPRV=array(
				'tabla'   =>'sprv',
				'columnas'=>array(
				'proveed' =>'C&oacute;digo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
				'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
				'retornar'=>array('proveed'=>'proveed'),
				'titulo'  =>'Buscar Proveedor');

		$bSPRV=$this->datasis->modbus($mSPRV);

		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');

		$script='
		$(document).ready(function(){
			$(".inputnum").numeric(".");
			$("#depto").change(function(){
				depto();
				$.post("'.$link2.'",{ depto:$(this).val() },function(data){$("#linea").html(data);})
				$.post("'.$link3.'",{ linea:"" },function(data){$("#grupo").html(data);})
			});
			$("#linea").change(function(){
				linea();
				$.post("'.$link3.'",{ linea:$(this).val() },function(data){$("#grupo").html(data);})
			});
			$("#grupo").change(function(){
				grupo();
			});
			$("#sinvprecioc").submit(function() {
				return confirm("Se van a actualizar todos los precios en pantalla \nEstas seguro de que quieres seguir??");
			});
			depto();
			linea();
			grupo();
		});

		function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}
			else{
				$("#nom_depto").attr("disabled","");
			}
		}

		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}
			else{
				$("#nom_linea").attr("disabled","");
			}
		}

		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}
			else{
				$("#nom_grupo").attr("disabled","");
			}
		}';

		$filter = new DataFilter2('Filtro por Producto');

		$select=array(
			'IF(formcal=\'U\',ultimo,IF(formcal=\'P\',pond,IF(formcal=\'S\',standard,GREATEST(ultimo,pond)))) AS costo',
			'a.existen','a.marca','a.tipo','a.id',
			'TRIM(codigo) AS codigo',
			'a.descrip','precio1','precio2','precio3','precio4','b.nom_grup','b.grupo',
			'c.descrip AS nom_linea','c.linea','d.descrip AS nom_depto','d.depto AS depto',
			'a.base1','a.base2','a.base3','a.base4'
		);

		$filter->db->select($select);
		$filter->db->from('sinv AS a');
		$filter->db->join('grup AS b','a.grupo=b.grupo');
		$filter->db->join('line AS c','b.linea=c.linea');
		$filter->db->join('dpto AS d','c.depto=d.depto');
		$filter->db->where('a.activo','S');
		$filter->script($script);

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo-> size=15;
		$filter->codigo->group = "Uno";

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip-> size=30;
		$filter->descrip->group = "Uno";

		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->db_name=("a.tipo");
		$filter->tipo->option("","Todos");
		$filter->tipo->option("Articulo","Art&iacute;culo");
		$filter->tipo->option("Servicio","Servicio");
		$filter->tipo->option("Descartar","Descartar");
		$filter->tipo->option("Consumo","Consumo");
		$filter->tipo->option("Fraccion","Fracci&oacute;n");
		$filter->tipo->style='width:120px;';
		$filter->tipo->group = "Uno";

		$filter->clave = new inputField("Clave", "clave");
		$filter->clave -> size=15;
		$filter->clave->group = "Uno";

		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		$filter->proveed->db_name='CONCAT_WS("-",`a`.`prov1`, `a`.`prov2`, `a`.`prov3`)';
		$filter->proveed -> size=10;
		$filter->proveed->group = "Dos";

		$filter->depto2 = new inputField("Departamento", "nom_depto");
		$filter->depto2->db_name="d.descrip";
		$filter->depto2 -> size=5;
		$filter->depto2->group = "Dos";

		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, CONCAT(depto,'-',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$filter->depto->in="depto2";
		$filter->depto->group = "Dos";
		$filter->depto->style='width:190px;';

		$filter->linea = new inputField("Linea", "nom_linea");
		$filter->linea->db_name="c.descrip";
		$filter->linea -> size=5;
		$filter->linea->group = "Dos";

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="c.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$filter->linea2->in="linea";
		$filter->linea2->group = "Dos";
		$filter->linea2->style='width:190px;';

		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, CONCAT(linea,'-',descrip) descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo2 = new inputField("Grupo", "nom_grupo");
		$filter->grupo2->db_name="b.nom_grup";
		$filter->grupo2 -> size=5;
		$filter->grupo2->group = "Dos";

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$filter->grupo->in="grupo2";
		$filter->grupo->group = "Dos";
		$filter->grupo->style='width:190px;';

		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option("","Seleccione un Departamento primero");
		}

		$filter->marca = new dropdownField("Marca", "marca");
		$filter->marca->option('','Todas');
		$filter->marca->options("SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca"); 
		$filter->marca->style='width:220px;';
		$filter->marca->group = "Dos";

		$filter->buttons("reset","search");
		$filter->build("dataformfiltro");

		$ggrid='';
		if($filter->is_valid()){
			$attr=array('id'=>'sinvprecioc');
			$ggrid =form_open(uri_string(),$attr);
			foreach ($filter->_fields as $field_name => $field_copy){
				$ggrid.= form_hidden($field_copy->id, $field_copy->value);
			}

			$grid = new DataGrid("Art&iacute;culos de Inventario");
			$grid->order_by("codigo","asc");
			$grid->per_page = 15;
			$link  = anchor('inventario/sinv/dataedit/show/<#id#>','<#codigo#>');
			$uri_2 = anchor('inventario/sinv/dataedit/create/<#id#>','Duplicar');

			$grid->column_orderby('C&oacute;digo','codigo','codigo');
			$grid->column_orderby('Descripci&oacute;n','descrip','descrip');
			$grid->column_orderby('Marca','marca','marca');
			for($i=1;$i<5;$i++){
				$obj='precio'.$i;
				$$obj = new inputField($obj, $obj);
				$$obj->grid_name=$obj.'[<#id#>]';
				$$obj->status   ='modify';
				$$obj->size     =8;
				$$obj->css_class='inputnum';
				$$obj->autocomplete=false;

				$grid->column("Precio $i",$$obj,'align=right');
			};
			$grid->column('Costo'     ,'<nformat><#costo#></nformat>'  ,'align=right');
			$grid->column('Existencia','<nformat><#existen#></nformat>','align=right');

			$grid->submit('pros', 'Cambiar','BR');
			$grid->build();
			$ggrid.=$grid->output;
			$ggrid.=form_close();
			//echo $this->db->last_query();
		}

		$data['content'] = '<div class="alert">'.$msj.'</div>';
		$data['content'].= $ggrid;
		$data['filtro']  = $filter->output;
		$data['title']   = heading('Cambio de precios');
		$data['head']    = $this->rapyd->get_head().script('jquery.pack.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$this->load->view('view_ventanas', $data);
	}

	function _cprecios(){
		$precio1=$this->input->post('precio1');
		$precio2=$this->input->post('precio2');
		$precio3=$this->input->post('precio3');
		$precio4=$this->input->post('precio4');

		$msj=''; $error=0;
		foreach($precio1 as $id => $p1){
			$dbid=$this->db->escape($id);
			$p2=floatval($precio2[$id]);
			$p3=floatval($precio3[$id]);
			$p4=floatval($precio4[$id]);
			$dbcosto=$this->datasis->dameval("SELECT IF(formcal='U',ultimo,IF(formcal='P',pond,IF(formcal='S',standard,GREATEST(ultimo,pond)))) AS costo FROM sinv WHERE id=${dbid}");

			if($p1>=$p2 && $p2>=$p3 && $p4>=$p4 && $p1*$p2*$p3*$p4>0 && $p1>=$dbcosto && $p2>=$dbcosto && $p3>=$dbcosto && $p4>=$dbcosto){
				$sql=array();
				for($i=1;$i<5;$i++){
					$pprecio='p'.$i;
					$precio=round($$pprecio,2);
					$base  = "${precio}*100/(100+iva)";
					$costo = "IF(formcal='U',ultimo,IF(formcal='P',pond,IF(formcal='S',standard,GREATEST(ultimo,pond))))";

					$sql[]="precio${i}=${precio}";
					$sql[]="base${i}  =ROUND(${base},2)";
					$sql[]="margen${i}=ROUND(100-((${costo})*100/(${base})),2)";

				}
				$campos=implode(',',$sql);

				$mSQL="UPDATE `sinv` SET ${campos} WHERE id=${dbid}";
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'sinv'); $error++; }
			}else{
				$codigo=$this->datasis->dameval("SELECT codigo FROM sinv WHERE id=${dbid}");
				$msj.='En el art&iacute;culo '.TRIM($codigo).' no se actualizo porque los precios deben tener valores mayores que el costo y en forma decrecientes (Precio 1 >= Precio 2 >= Precio 3 >= Precio 4).'.br();
			}
		}
		if($error>0) $msj.='Hubo alg&uacute;n error, se gener&oacute; un centinela';
		return $msj;
	}

	function sug($tabla=''){
		if($tabla=='dpto'){
			$valor=$this->datasis->dameval("SELECT LPAD(hexa,2,0) FROM serie LEFT JOIN dpto ON LPAD(depto,2,0)=LPAD(hexa,2,0) WHERE valor<255 AND depto IS NULL LIMIT 1");
		}elseif($tabla=='line'){
				$valor=$this->datasis->dameval("SELECT LPAD(hexa,2,0) FROM serie LEFT JOIN line ON LPAD(linea,2,0)=LPAD(hexa,2,0) WHERE valor<255 AND linea IS NULL LIMIT 1");
		}elseif($tabla=='grup'){
				$valor=$this->datasis->dameval("SELECT LPAD(hexa,4,0) FROM serie LEFT JOIN grup ON LPAD(grupo,4,0)=LPAD(hexa,4,0) WHERE valor<65535 AND grupo IS NULL LIMIT 1");
		}
		return $valor;
	}

	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT codigo FROM sinv ORDER BY codigo DESC LIMIT 1");
		echo $ultimo;
	}

	function sugerir(){
		$ultimo=$this->datasis->dameval("SELECT LPAD(hexa,4,0) FROM serie LEFT JOIN sinv ON LPAD(codigo,4,0)=LPAD(hexa,4,0) WHERE valor<65535 AND codigo IS NULL LIMIT 1");
		echo $ultimo;
	}

	function chexiste($codigo){
		//$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE codigo='$codigo'");
		if ($chek > 0){
			$descrip=$this->datasis->dameval("SELECT descrip FROM sinv WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el producto $descrip");
			return FALSE;
		}else {
		 return TRUE;
		}
	}

	function chexiste2($alterno){
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE alterno='$alterno'");
		if ($chek > 0){
			$descrip=$this->datasis->dameval("SELECT descrip FROM sinv WHERE alterno='$alterno'");
			$this->validation->set_message('chexiste2',"El codigo alterno $alterno ya existe para el producto $descrip");
			return FALSE;
		}else {
			return TRUE;
		}
	}

	function _detalle($codigo){
		$salida='';
		$estilo='';
		if(!empty($codigo)){
			$this->rapyd->load('dataedit','datagrid');
			$grid = new DataGrid('Existencias por Almacen');
			$grid->db->select(array('b.ubides','a.codigo','a.alma','a.existen',"IF(b.ubides IS NULL,'SIN ALMACEN',b.ubides) AS nombre"));
			$grid->db->from('itsinv AS a');
			$grid->db->join('caub as b','a.alma=b.ubica','LEFT');
			$grid->db->where('codigo',$codigo);

			//$link=anchor('/inventario/caub/dataedit/show/<#alma#>','<#alma#>');
			
			$link  = "<a href=\"javascript:void(0);\" onclick=\"window.open('".base_url();
			$link .= "inventario/caub', '_blank', 'width=800,height=600,scrollbars=Yes,status=Yes,resizable=Yes,screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');\" heigth=\"600\"><#alma#></a>";
			
			$grid->column('Almac&eacute;n' ,$link, "style='font-size:12px;font-weight:bold;'");
			$grid->column('Nombre'         ,'nombre',"style='font-size: 10px'");
			$grid->column('Cantidad'       ,'existen','align="right" '."style='font-size: 10px'");
		
			$grid->build('datagridsimple');
			
			if($grid->recordCount>0) $salida=$grid->output;
			$salida = html_entity_decode($salida);
			$estilo="
<style type='text/css'>
.simplerow  { color: #153D51;border-bottom: 1px solid #ECECEC; font-family: Lucida Grande, Verdana, Geneva, Sans-serif;	font-size: 12px; font-weight: bold;}
.simplehead { background: #382408; border-bottom: 1px solid #ECECEC;color: #EEFFEE;font-family: Lucida Grande, Verdana, Geneva, Sans-serif; font-size: 12px;padding-left:5px;}
.simpletabla { width:100%;colspacing:0px; colpadding:0px}
</style>
";
		}
		return $estilo.$salida;
	}

	function _pre_del($do) {
		$codigo=$this->db->escape($do->get('codigo'));
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM sitems WHERE codigoa=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM itscst WHERE codigo=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM itstra WHERE codigo=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM itspre WHERE codigo=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM itsnot WHERE codigo=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM itsnte WHERE codigo=$codigo");
		$chek += $this->datasis->dameval("SELECT COUNT(*) FROM itsinv WHERE codigo=$codigo");

		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Producto con Movimiento no puede ser Borrado, solo se puede inactivar';
			return false;
		}
		return true;
	}
	
	function barratonombre(){
		if($this->input->post('barra')){
			$barra=$this->db->escape($this->input->post('barra'));
			echo $this->datasis->dameval("SELECT descrip FROM sinv WHERE barras=$barra");
		}
	}

	function consulta(){  
		$this->load->helper('openflash');
		$this->rapyd->load("datagrid");
		$fields = $this->db->field_data('sinv');
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
		$id = $claves['id'];

		$mCodigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=".$claves['id']."");

		$mSQL  = 'SELECT a.tipoa, MID(a.fecha,1,7) mes, sum(a.cana*(a.tipoa="F")) cventa, sum(a.cana*(a.tipoa="D")) cdevol, sum(a.cana*if(a.tipoa="D",-1,1)) cana, sum(a.tota*(a.tipoa="F")) mventa, sum(a.tota*(a.tipoa="D")) mdevol, sum(a.tota*if(a.tipoa="D",-1,1)) tota ';
		$mSQL .= "FROM sitems a WHERE a.codigoa='".addslashes($mCodigo)."' ";
		$mSQL .= "AND a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01') ";
		$mSQL .= "GROUP BY MID( a.fecha ,1,7)  WITH ROLLUP LIMIT 24";
		$mGrid1 = '';

			
		$mSQL  = 'SELECT a.usuario, a.fecha, MID(a.hora,1,5) hora, MID(REPLACE(a.comenta,"ARTICULO DE INVENTARIO",""),1,30) comenta, a.modulo ';
		$mSQL .= 'FROM logusu a WHERE a.comenta LIKE "%'.addslashes($mCodigo).'%" ';
		$mSQL .= "ORDER BY a.fecha DESC LIMIT 30";

		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$mGrid2 = '
			<div id="tableDiv_Logusu" class="tableDiv">
			<table id="Open_text_Logusu" class="FixedTables" >
			<thead>
			<tr>
				<th>Fecha</th>
				<th>Usuario</th>
				<th>Hora</th>
				<th>Modulo</th>
				<th>Accion</th>
			</tr>
			</thead>
			<tbody>';

			$m = 1;
			foreach ($query->result() as $row){
				if($m == 1) { $mGrid2.='<tr id="firstTr">'; } else { $mGrid2.='<tr>'; };
				$mGrid2.="
				<tr>
					<td>".$row->fecha."</td>
					<td>".$row->usuario."</td>
					<td>".$row->hora."</td>
					<td>".$row->modulo."</td>
					<td>".$row->comenta."</td>
				</tr>";
				$m++;
			}
			$mGrid2 .= "
			</tbody>
			</table>
			</div>";
		} else {
			
			$mGrid2 = "NO SE ENCONTRO MOVIMIENTO";
		}

		$descrip = $this->datasis->dameval("SELECT descrip FROM sinv WHERE id=".$claves['id']." ");

/*
mes, 
cventa, 
mventa, 
mpvp, 
ccompra, 
mcompra,
util, 
margen, 
promedio
*/

		$script = "
<script type=\"text/javascript\" >  

<!-- All the scripts will go here  --> 
var dsOption= {
	fields :[
		{name : 'mes'},
		{name : 'cventa',   type: 'float' },
		{name : 'mventa',   type: 'float' },
		{name : 'mpvp' ,    type: 'float' },
		{name : 'ccompra',  type: 'float' },
		{name : 'mcompra',  type: 'float' },
		{name : 'util',     type: 'float' },
		{name : 'margen',   type: 'float' },
		{name : 'promedio', type: 'float' }    
	],
	recordType : 'object'
} 

var colsOption = [
	{id: 'mes',      header: 'Mes',          width :60, frozen: true   },
	{id: 'cventa' ,  header: 'Cant. Venta',  width :80, align: 'right' },
	{id: 'mventa' ,  header: 'Costo Venta',  width :80, align: 'right' },
	{id: 'mpvp' ,    header: 'Precio Venta', width :80, align: 'right' },
	{id: 'ccompra' , header: 'Cant Compra',  width :80, align: 'right' },
	{id: 'mcompra' , header: 'Monto Compra', width :80, align: 'right' },
	{id: 'util' ,    header: 'Utilidad',     width :80, align: 'right' },
	{id: 'margen' ,  header: 'Margen %',     width :80, align: 'right' },
	{id: 'promedio', header: 'Costo Prom.',  width :80, align: 'right' }
];

var gridOption={
	id : 'grid1',
	loadURL : '/proteoerp/inventario/sinv/consulta_ventas/".$id."',
	container : 'grid1_container', 
	dataset : dsOption ,
	columns : colsOption,
	allowCustomSkin: true,
	skin: 'vista',
	toolbarContent: 'pdf'	
};
 

var dsOption1= {
	fields :[
		{name : 'fecha'   },
		{name : 'usuario' },
		{name : 'hora'    },
		{name : 'modulo'  },
		{name : 'comenta' }
	],
	recordType : 'object'
} 

var colsOption1 = [
	{id: 'fecha',   header: 'Fecha',      width :70, frozen: true },
	{id: 'usuario', header: 'Usuario',    width :60 },
	{id: 'hora' ,   header: 'Hora',       width :60 },
	{id: 'modulo' , header: 'Modulo',     width :60 },
	{id: 'comenta', header: 'Comentario', width :200 }
];

var gridOption1={
	id : 'grid2',
	loadURL : '/proteoerp/inventario/sinv/consulta_logusu/".$id."',
	container : 'grid2_container', 
	dataset : dsOption1 ,
	columns : colsOption1,
	toolbarContent: 'pdf',
	allowCustomSkin: true,
	skin: 'vista'
};
 
var mygrid=new Sigma.Grid(gridOption);
Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );

var mygrid1=new Sigma.Grid(gridOption1);
Sigma.Util.onLoad( Sigma.Grid.render(mygrid1) );

</script>  
";

		$style = '';

		$data['content'] = "
		<table align='center' border='0' cellspacing='2' cellpadding='2' width='98%'>
			<tr>
				<td valign='top'>
					<div style='border: 3px outset #EFEFEF;background: #EFEFFF '>
					<div id='grid1_container' style='width:500px;height:250px'></div>
					</div>
				</td>
				<td>".
				open_flash_chart_object( 250,180, site_url("inventario/sinv/ventas/$id"))."
				</td>
			</tr>
			<tr>
				<td>
					<div style='border: 3px outset #EFEFEF;background: #EFEFFF '>
					<div id='grid2_container' style='width:500px;height:250px'></div>
					</div>
					
				</td>
				<td>".
				open_flash_chart_object( 250,180, site_url("inventario/sinv/compras/$id"))."
				</td>
			</tr>
		</table>
		  
";

		$data['title']    = '<h1>Consulta de Articulo de Inventario</h1>';

		//$data['script']   = script("plugins/jquery.fixedtable.js");
		$data['script']   = script("plugins/jquery.numeric.pack.js");
		$data['script']  .= script("plugins/jquery.floatnumber.js");
		$data['script']  .= script("gt_msg_en.js");
		$data['script']  .= script("gt_grid_all.js");
		$data['script']  .= $script;
		
		$data['style']    = style('gt_grid.css');
		//$data['style'] .= $style;
		$data["subtitle"] = "
			<div align='center' style='border: 2px outset #EFEFEF;background: #EFEFEF;font-size:18px'>
				<a href='javascript:javascript:history.go(-1)'>(".addslashes($mCodigo).") ".$descrip."</a>
			</div>";

		$data["head"]  = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function consulta_ventas() {
		$id = $this->uri->segment($this->uri->total_segments());
		$mCodigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=".$id."");

		$mSQL  = 'SELECT MID(a.fecha,1,7) mes, sum(a.cana*(a.tipoa="F")) cventa, sum(a.cana*(a.tipoa="D")) cdevol, sum(a.cana*if(a.tipoa="D",-1,1)) cana, sum(a.tota*(a.tipoa="F")) mventa, sum(a.tota*(a.tipoa="D")) mdevol, sum(a.tota*if(a.tipoa="D",-1,1)) tota ';
		$mSQL .= "FROM sitems a WHERE a.codigoa='".addslashes($mCodigo)."' ";
		$mSQL .= "AND a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01') ";
		$mSQL .= "GROUP BY MID( a.fecha ,1,7)  WITH ROLLUP LIMIT 60";
		
		$mSQL  = "
SELECT 
	MID(a.fecha,1,7) mes, 
	sum(a.cantidad*(a.origen='3I')) cventa, 
	ROUND(sum(a.promedio*a.cantidad*(a.origen='3I')),2) mventa, 
	ROUND(sum(a.venta*(a.origen='3I')),2) mpvp, 
	sum(a.cantidad*(a.origen='2C')) ccompra, 
	sum(a.monto*(a.origen='2C')) mcompra,
	ROUND(sum((a.venta-a.cantidad*a.promedio)*(a.origen='3I')),2) util, 
	100- ROUND( sum(a.cantidad*a.promedio*(a.origen='3I'))*100/SUM(a.venta), 2) margen, 
	round(avg(promedio),2) promedio
FROM costos a WHERE a.codigo='".addslashes($mCodigo)."' AND a.origen IN ('3I','2C')
	AND a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01') 
GROUP BY MID( a.fecha ,1,7)  WITH ROLLUP LIMIT 24
";

		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$retArray = array();
			foreach( $query->result_array() as  $row ) {
				$retArray[] = $row;
			}
			$data = json_encode($retArray);
			$ret = "{data:" . $data .",\n";
			$ret .= "recordType : 'array'}";
		} else {
			$ret = '{data : []}';
		}
		echo $ret;
	}


	function consulta_logusu() {
		$id = $this->uri->segment($this->uri->total_segments());
		$mCodigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=".$id."");

		$mSQL  = 'SELECT a.fecha, a.usuario,  MID(a.hora,1,5) hora, a.modulo, MID(REPLACE(a.comenta,"ARTICULO DE INVENTARIO",""),1,30) comenta ';
		$mSQL .= 'FROM logusu a WHERE a.comenta LIKE "%'.addslashes($mCodigo).'%" ';
		$mSQL .= "ORDER BY a.fecha DESC LIMIT 60";
		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$retArray = array();
			foreach( $query->result_array() as  $row ) {
				$retArray[] = $row;
			}
			$data = json_encode($retArray);
			$ret = "{data:" . $data .",\n";
			$ret .= "recordType : 'array'}";
			//$ret .= $mSQL;
		} else {
			$ret = '{data : []}';
		}
		echo $ret;
	}


	function ventas($id=''){
		if (empty($id)) return;
		$this->load->library('Graph');
		
		$codigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$id");                           	                            
		$mSQL = "SELECT	a.tipoa,MID(a.fecha,1,7) mes,
			sum(a.cana*(a.tipoa='F')) cventa,
			sum(a.cana*(a.tipoa='D')) cdevol,
			sum(a.cana*if(a.tipoa='D',-1,1)) cana,
			sum(a.tota*(a.tipoa='F')) mventa,
			sum(a.tota*(a.tipoa='D')) mdevol,
			sum(a.tota*if(a.tipoa='D',-1,1)) tota
		FROM sitems a 
		WHERE a.codigoa='$codigo' AND a.tipoa IN ('F','D') AND a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01')
		GROUP BY MID( a.fecha, 1,7 )  LIMIT 7";
		
		$maxval = 0;
		$query = $this->db->query($mSQL);
		$data_1=$data_2=$meses=array(); 
		foreach($query->result() as $row ){
			if ($row->cana>$maxval) $maxval=$row->cana;
			$meses[]   = $row->mes;
			$data_1[]  = $row->cana;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar(75, '#0053A4');
		
		$bar_1->key('Venta',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_1->links[]= site_url("/ventas/clientes/mensuales/$codigo/".$meses[$i]);
		} 			 
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval > 0 ) {
			$g->title( 'Ventas por Mes ','{font-size: 16px; color:#0F3054}' ); 
			$g->data_sets[] = $bar_1;
		
			$g->set_x_labels($meses);
			$g->set_x_label_style( 10, '#000000', 2, 1 );
			$g->set_x_axis_steps( 10 );
			$g->set_x_legend( 'Meses ', 14,'#004381' );        
		
			$g->bg_colour = '#FFFFFF';
			$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Cantidad: #tip#' );
			$g->set_y_max(ceil($maxval/$om));
			$g->y_label_steps(5);
			$g->set_y_legend('Ventas x '.number_format($om,0,'','.'), 16, '#004381' );
		} else                                                                                           
			$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	}

	function compras($id=''){
		if (empty($id)) return; 
		$this->load->library('Graph');
		                           	                            
		$codigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=$id");                           	                            
		$mSQL = "SELECT	MID(a.fecha,1,7) mes,
			sum(a.cantidad*(b.tipo_doc='FC')) cventa,
			sum(a.cantidad*(b.tipo_doc='NC')) cdevol,
			sum(a.cantidad*if(b.tipo_doc='NC',-1,1)) cana,
			sum(a.importe*(b.tipo_doc='FC')) mventa,
			sum(a.importe*(b.tipo_doc='NC')) mdevol,
			sum(a.importe*if(b.tipo_doc='NC',-1,1)) tota
		FROM itscst a JOIN scst b ON a.control=b.control 
		WHERE a.codigo='$codigo' AND b.tipo_doc IN ('FC','NC') AND b.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01')
				AND  a.fecha <= b.actuali 
		GROUP BY MID( b.fecha, 1,7 ) LIMIT 7  ";
		
		$maxval = 0;
		$query = $this->db->query($mSQL);
		$data_1=$data_2=$meses=array(); 
		foreach($query->result() as $row ){
			if ($row->cana>$maxval) $maxval=$row->cana;
			$meses[]   = $row->mes;
			$data_1[]  = $row->cana;
		}
		$om=1;while($maxval/$om>100) $om=$om*10;
		
		$bar_1 = new bar(75, '#9053A4');
		
		$bar_1->key('Compra',10);
		
		for($i=0;$i<count($data_1);$i++ ){
			$bar_1->add_data_tip($data_1[$i]/$om, graph::esc( number_format($data_1[$i],2,',','.')));
			$bar_1->links[]= site_url("/ventas/clientes/mensuales/$codigo/".$meses[$i]);
		} 			 
		$g = new graph();
		$g->set_is_decimal_separator_comma(1);
		if($maxval > 0 ) {
			$g->title( 'Compras por Mes ','{font-size: 16px; color:#0F3054}' ); 
			$g->data_sets[] = $bar_1;
		
			$g->set_x_labels($meses);
			$g->set_x_label_style( 10, '#000000', 2, 1 );
			$g->set_x_axis_steps( 10 );
			$g->set_x_legend( 'Meses ', 14,'#004381' );        
		
			$g->bg_colour = '#FFFFFF';
			$g->set_tool_tip( '#key#<br>Mes: #x_label# <br>Cantidad: #tip#' );
			$g->set_y_max(ceil($maxval/$om));
			$g->y_label_steps(5);
			$g->set_y_legend('Compras x '.number_format($om,0,'','.'), 16, '#004381' );
		} else                                                                                           
			$g->title( 'No existen ventas en el a&ntilde;o seleccionado','{font-size:18px; color: #d01f3c}');
		$g->bg_colour='#FFFFFF';                                                                 
		echo utf8_encode($g->render());
	}

	function instalar(){
		$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);

		$mSQL="CREATE TABLE IF NOT EXISTS `sinvcombo` (
		`combo` char(15) NOT NULL,
		`codigo` char(15) NOT NULL default '',
		`descrip` char(30) default NULL,
		`cantidad` decimal(10,3) default NULL,
		`precio` decimal(15,2) default NULL,
		`transac` char(8) default NULL,
		`estampa` date default NULL,
		`hora` char(8) default NULL,
		`usuario` char(12) default NULL,
		`costo` decimal(17,2) default '0.00',
		PRIMARY KEY  (`combo`,`codigo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
		
	}
}