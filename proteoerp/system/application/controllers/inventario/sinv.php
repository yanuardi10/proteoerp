<?php include('common.php');
class sinv extends Controller {

	function sinv(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index(){
		$this->datasis->modulo_id('301',1);
		$this->instalar();
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

		$mGRUP=array(
				'tabla'   =>'grup',
				'columnas'=>array(
				'grupo'   =>'Grupo',
				'nom_grup'=>'Nombre',
				'linea'=>'Linea',
				'depto'=>'Depto'),
				'filtro'  =>array('grupo'=>'Grupo','nom_grup'=>'Nombre'),
				'retornar'=>array('grupo'=>'popup_prompt'),
				'titulo'  =>'Buscar Grupo');

		$bGRUP=$this->datasis->modbus($mGRUP);

		$mMARC=array(
				'tabla'   =>'marc',
				'columnas'=>array(
				'marca'   =>'Marca'),
				'filtro'  =>array('marca'=>'Marca'),
				'retornar'=>array('marca'=>'popup_prompt'),
				'titulo'  =>'Buscar Marca');

		$bMARC=$this->datasis->modbus($mMARC);


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
		$filter->db->select("a.existen AS existen,a.marca marca,a.tipo AS tipo,id,codigo,a.descrip,precio1,precio2,precio3,precio4,b.nom_grup AS nom_grup,b.grupo AS grupoid,c.descrip AS nom_linea,c.linea AS linea,d.descrip AS nom_depto,d.depto AS depto, activo, mmargen ");
		$filter->db->from('sinv AS a');
		$filter->db->join('grup AS b','a.grupo=b.grupo','LEFT');
		$filter->db->join('line AS c','b.linea=c.linea', 'LEFT');
		$filter->db->join('dpto  d','c.depto=d.depto','LEFT');
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

		$mtool .= "<td>&nbsp;<a href='javascript:cambgrupo()'>";
		$mtool .= img(array('src' => 'images/grupo.jpg', 'alt' => 'Cambiar Grupo', 'title' => 'Cambiar Grupo','border'=>'0','height'=>'30'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:cambmarca()'>";
		$mtool .= img(array('src' => 'images/marca.jpg', 'alt' => 'Cambiar Marca', 'title' => 'Cambiar Marca','border'=>'0','height'=>'30'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."inventario/marc', '_blank', 'width=400, height=500, scrollbars=No, status=No, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" heigth="500"'.'>';
		$mtool .= img(array('src' => 'images/tux1.png', 'alt' => 'Gestion de Marcas', 'title' => 'Gestion de Marcas','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";

		$mtool .= "<td>&nbsp;<a href='javascript:void(0);' ";
		$mtool .= 'onclick="window.open(\''.base_url()."inventario/unidad', '_blank', 'width=340, height=430, scrollbars=No, status=No, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');".'" >';
		$mtool .= img(array('src' => 'images/unidad.gif', 'alt' => 'Gestion de Unidades', 'title' => 'Gestion de Unidades','border'=>'0','height'=>'32'));
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
		$uri_2 .= "<input type='checkbox' name='<#id#>' id='<#id#>' style='height: 10px;'> ";

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
		$grid->column_orderby("Mayor%","mmargen","mmargen");

		//$grid->add('inventario/sinv/dataedit/create');
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
		$link4  =site_url("inventario/sinv/sinvcamgrup/");
		$link5  =site_url("inventario/sinv/sinvcammarca/");

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

		function cambgrupo(){
			var yurl = "";
			var n = $("input:checked").length;
			var a = "";
			var mbusca = "'.addslashes($bGRUP).'";

			$("input:checked").each( function() { a += this.id+","; });

			if( n==0) {
				jAlert("No hay productos Seleccionados","Informacion");
			}else{
			jPrompt("Selecciono "+n+" Productos<br>Introduzca el Grupo "+mbusca,"" ,"Cambiar de Grupo", function(mgrupo){
				if( mgrupo==null ){
					jAlert("Cancelado por el usuario","Informacion");
				} else if( mgrupo=="" ) {
					jAlert("Cancelado,  Grupo vacio","Informacion");
				} else {
					yurl = encodeURIComponent(mgrupo);
					$.ajax({
						url: "'.$link4.'",
						global: false,
						type: "POST",
						data: ({ grupo : encodeURIComponent(mgrupo), productos : a }),
						dataType: "text",
						async: false,
						success: function(sino) {
						jAlert(sino,"Informacion");
						jConfirm( "Actualizar","Recargar Tabla y perder los checks?" , function(r){
							if(r) {
								location.reload();
							}
							});
						},
						error: function(h,t,e)  { jAlert("Error..codigo="+yurl+" ",e) }
					});
				}
			})
			}
		};


		function cambmarca(){
			var yurl = "";
			var n = $("input:checked").length;
			var a = "";
			var mbusca = "'.addslashes($bMARC).'";

			$("input:checked").each( function() { a += this.id+","; });

			if( n==0) {
				jAlert("No hay productos Seleccionados","Informacion");
			}else{
			jPrompt("Selecciono "+n+" Productos<br>Introduzca la Marca "+mbusca,"" ,"Cambiar Marca", function(mmarca){
				if( mmarca==null ){
					jAlert("Cancelado por el usuario","Informacion");
				} else if( mmarca=="" ) {
					jAlert("Cancelado, Marca vacia","Informacion");
				} else {
					yurl = encodeURIComponent(mmarca);
					$.ajax({
						url: "'.$link5.'",
						global: false,
						type: "POST",
						data: ({ marca : encodeURIComponent(mmarca), productos : a }),
						dataType: "text",
						async: false,
						success: function(sino) {
						jAlert(sino,"Informacion");
						location.reload();
						},
						error: function(h,t,e)  { jAlert("Error..codigo="+yurl+" ",e) }
					});
				}
			})
			}
		};
		</script>';

		// *************************************
		//
		//       Para usar SuperTable
		//
		// *************************************
		$extras = '<script type="text/javascript">
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
		</script>';

		$style ='<style type="text/css">
		.fakeContainer { /* The parent container */
		    margin: 5px;
		    padding: 0px;
		    border: none;
		    width: 740px; /* Required to set */
		    height: 320px; /* Required to set */
		    overflow: hidden; /* Required to set */
		}
		</style>';

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('jquery.alerts.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('superTables.js');
		$data['script'] .= $script;
		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['style']  .= style('jquery.alerts.css');
		$data['extras']  = $extras;
		$data['title']   = heading('Maestro de Inventario ');
		$data['head']   = $this->rapyd->get_head();

		$this->load->view('view_ventanas', $data);
	}

	// *********************************************************************************************************
	//
	//   DATAEDIT
	//
	// *********************************************************************************************************
	function dataedit($status='',$id='' ) {
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataedit','datadetails');

		$modbus = array(
			'tabla' => 'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro' => array('codigo' => 'C&oacute;digo'
			,'descrip' => 'Descripci&oacute;n')
			,'retornar'  => array(
				 array('codigo'  => 'itcodigo_<#i#>')
				,array('descrip' => 'itdescrip_<#i#>')
				,array('descrip' => 'itdescrip_<#i#>_val')
				,array('formcal' => 'itformcal_<#i#>')
				,array('ultimo'  => 'itultimo_<#i#>_val')
				,array('ultimo'  => 'itultimo_<#i#>')
				,array('pond'    => 'itpond_<#i#>')
				,array('pond'    => 'itpond_<#i#>_val')
				,array('base1'   => 'itprecio1_<#i#>')

			),
			'p_uri' => array(4 => '<#i#>'),
			'titulo' => 'Buscar Articulo',
			'where' => '`activo` = "S"',
			'script' => array('totalizar()')
		);
		$bSINV_C = $this->datasis->p_modbus($modbus, '<#i#>');

		$modbus = array(
			'tabla' => 'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro' => array('codigo' => 'C&oacute;digo'
			,'descrip' => 'Descripci&oacute;n')
			,'retornar'  => array(
				 array('codigo'  => 'it2codigo_<#i#>')
				,array('descrip' => 'it2descrip_<#i#>')
				,array('descrip' => 'it2descrip_<#i#>_val')
				,array('formcal' => 'it2formcal_<#i#>')
				,array('ultimo'  => 'it2ultimo_<#i#>')
				,array('pond'    => 'it2pond_<#i#>')
				,array('id'      => 'it2id_sinv_<#i#>')

			),
			'p_uri' => array(4 => '<#i#>'),
			'titulo' => 'Buscar Articulo',
			'where' => '`activo` = "S"',
			'script' => array('totalizarpitem()')
		);
		$bSINV_I = $this->datasis->p_modbus($modbus, '<#i#>',800,600,'sinv_i');

		$do = new DataObject('sinv');
		$do->rel_one_to_many('sinvcombo' , 'sinvcombo' , array('codigo' => 'combo'));
		$do->rel_one_to_many('sinvpitem' , 'sinvpitem' , array('codigo' => 'producto'));
		$do->rel_one_to_many('sinvplabor', 'sinvplabor', array('codigo' => 'producto'));
		$do->rel_pointer('sinvcombo'     , 'sinv p'    , 'p.codigo=sinvcombo.codigo', 'p.descrip AS sinvdescrip,p.pond AS sinvpond,p.ultimo sinvultimo,p.formcal sinvformcal,p.precio1 sinvprecio1');

		if($status=='create' && !empty($id)){
			$do->load($id);
			$do->set('codigo', '');
		}

		$edit = new DataDetails('Maestro de Inventario', $do);
		$edit->pre_process( 'insert','_pre_inserup');
		$edit->pre_process( 'update','_pre_inserup');
		$edit->pre_process( 'delete','_pre_del'    );
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->back_url = site_url('inventario/sinv/filteredgrid');

		$ultimo ='<a href="javascript:ultimo();" title="Consultar ultimo c&oacute;digo ingresado"> Consultar ultimo c&oacute;digo</a>';
		$sugerir='<a href="javascript:sugerir();" title="Sugerir un C&oacute;digo aleatorio">Sugerir C&oacute;digo </a>';

		$edit->codigo = new inputField('C&oacute;digo', 'codigo');
		$edit->codigo->size=15;
		$edit->codigo->maxlength=15;
		$edit->codigo->rule = 'trim|required|strtoupper|callback_chexiste';
		$edit->codigo->mode = 'autohide';
		$edit->codigo->append($sugerir);
		$edit->codigo->append($ultimo);

		$edit->alterno = new inputField('C&oacute;digo Alterno', 'alterno');
		$edit->alterno->size=15;
		$edit->alterno->maxlength=15;
		$edit->alterno->rule = 'trim|strtoupper|unique';

		$edit->enlace  = new inputField('C&oacute;digo Caja', 'enlace');
		$edit->enlace ->size=15;
		$edit->enlace->maxlength=15;
		$edit->enlace->rule = 'trim|strtoupper';

		$edit->barras = new inputField('C&oacute;digo Barras', 'barras');
		$edit->barras->size=15;
		$edit->barras->maxlength=15;
		$edit->barras->rule = 'trim';

		$edit->tipo = new dropdownField('Tipo', 'tipo');
		$edit->tipo->style='width:100px;';
		$edit->tipo->option('Articulo' ,'Art&iacute;culo');
		$edit->tipo->option('Servicio' ,'Servicio');
		$edit->tipo->option('Descartar','Descartar');
		$edit->tipo->option('Fraccion' ,'Fracci&oacute;n');
		$edit->tipo->option('Lote'     ,'Lote');
		$edit->tipo->option('Combo'    ,'Combo');
		//$edit->tipo->option('Consumo','Consumo');

		$AddUnidad='<a href="javascript:add_unidad();" title="Haz clic para Agregar una unidad nueva">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->unidad = new dropdownField('Unidad','unidad');
		$edit->unidad->style='width:100px;';
		$edit->unidad->option('','Seleccionar');
		$edit->unidad->options("SELECT unidades, unidades as valor FROM unidad ORDER BY unidades");
		$edit->unidad->append($AddUnidad);

		$edit->clave = new inputField('Clave', 'clave');
		$edit->clave->size=10;
		$edit->clave->maxlength=8;
		$edit->clave->rule = 'trim|strtoupper';

		$edit->ubica = new inputField('Ubicaci&oacute;n', 'ubica');
		$edit->ubica->size=10;
		$edit->ubica->maxlength=8;
		$edit->ubica->rule = 'trim|strtoupper';

		$AddDepto='<a href="javascript:add_depto();" title="Haz clic para Agregar un nuevo Departamento">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->depto = new dropdownField('Departamento', 'depto');
		$edit->depto->rule ='required';
		$edit->depto->style='width:300px;white-space:nowrap;';
		$edit->depto->option('','Seleccione un Departamento');
		$edit->depto->options("SELECT depto, CONCAT(depto,'-',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$edit->depto->append($AddDepto);

		$AddLinea='<a href="javascript:add_linea();" title="Haz clic para Agregar una nueva Linea;">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->linea = new dropdownField('L&iacute;nea','linea');
		$edit->linea->rule ='required';
		$edit->linea->style='width:300px;';
		$edit->linea->append($AddLinea);
		$depto=$edit->getval('depto');
		if($depto!==FALSE){
			$edit->linea->options("SELECT linea, CONCAT(LINEA,'-',descrip) descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$edit->linea->option('','Seleccione un Departamento primero');
		}

		$AddGrupo='<a href="javascript:add_grupo();" title="Haz clic para Agregar un nuevo Grupo;">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->grupo = new dropdownField('Grupo', 'grupo');
		$edit->grupo->rule ='required';
		$edit->grupo->style='width:300px;';
		$edit->grupo->append($AddGrupo);

		$linea=$edit->getval('linea');
		if($linea!==FALSE){
			$edit->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$edit->grupo->option('','Seleccione un Departamento primero');
		}

		$edit->comision  = new inputField('Comisi&oacute;n %', 'comision');
		$edit->comision ->size=7;
		$edit->comision->maxlength=5;
		$edit->comision->css_class='inputnum';
		$edit->comision->rule='numeric|callback_positivo|trim';

		$edit->fracci  = new inputField('Fracci&oacute;n x Unid.', 'fracci');
		$edit->fracci ->size=10;
		$edit->fracci->maxlength=4;
		$edit->fracci->css_class='inputnum';
		$edit->fracci->rule='numeric|callback_positivo|trim';

		$edit->activo = new dropdownField('Activo', 'activo');
		$edit->activo->style='width:50px;';
		$edit->activo->option('S','Si');
		$edit->activo->option('N','No');

		$edit->serial2 = new freeField('','free','Serial');
		$edit->serial2->in='activo';

		$edit->serial = new dropdownField ('Usa Seriales', 'serial');
		$edit->serial->style='width:50px;';
		$edit->serial->option('N','No');
		$edit->serial->option('S','Si');
		$edit->serial->in='activo';

		$edit->tdecimal2 = new freeField('','free','Usa Decimales');
		$edit->tdecimal2->in='activo';

		$edit->tdecimal = new dropdownField('Usa Decimales', 'tdecimal');
		$edit->tdecimal->style='width:50px;';
		$edit->tdecimal->option('N','No');
		$edit->tdecimal->option('S','Si');
		$edit->tdecimal->in='activo';

		$edit->descrip = new inputField('Descripci&oacute;n', 'descrip');
		$edit->descrip->size=45;
		$edit->descrip->maxlength=45;
		$edit->descrip->rule = 'trim|required|strtoupper';

		$edit->descrip2 = new inputField('Descripci&oacute;n adicional', 'descrip2');
		$edit->descrip2->size=45;
		$edit->descrip2->maxlength=45;
		$edit->descrip2->rule = 'trim|strtoupper';

		$edit->peso  = new inputField('Peso', 'peso');
		$edit->peso->size=10;
		$edit->peso->maxlength=12;
		$edit->peso->css_class='inputnum';
		$edit->peso->rule='numeric|callback_positivo|trim';

		$edit->alto = new inputField('Alto', 'alto');
		$edit->alto->size=10;
		$edit->alto->maxlength=12;
		$edit->alto->css_class='inputnum';
		$edit->alto->rule='numeric|callback_positivo|trim';

		$edit->ancho = new inputField('Ancho', 'ancho');
		$edit->ancho->size=10;
		$edit->ancho->maxlength=12;
		$edit->ancho->css_class='inputnum';
		$edit->ancho->rule='numeric|callback_positivo|trim';

		$edit->largo = new inputField('Largo', 'largo');
		$edit->largo->size=10;
		$edit->largo->maxlength=12;
		$edit->largo->css_class='inputnum';
		$edit->largo->rule='numeric|callback_positivo|trim';

		$edit->garantia = new inputField('Garantia', 'garantia');
		$edit->garantia->size=5;
		$edit->garantia->maxlength=3;
		$edit->garantia->css_class='inputonlynum';
		$edit->garantia->rule='numeric|callback_positivo|trim';

		$AddMarca='<a href="javascript:add_marca();" title="Haz clic para Agregar una marca nueva">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->marca = new dropdownField('Marca', 'marca');
		$edit->marca->rule = 'required';
		$edit->marca->style='width:180px;';
		$edit->marca->option('','Seleccionar');
		$edit->marca->options('SELECT marca AS codigo, marca FROM marc ORDER BY marca');
		$edit->marca->append($AddMarca);

		$edit->modelo  = new inputField('Modelo', 'modelo');
		$edit->modelo->size=24;
		$edit->modelo->maxlength=20;
		$edit->modelo->rule = 'trim|strtoupper';

		$edit->clase= new dropdownField('Clase', 'clase');
		$edit->clase->style='width:100px;';
		$edit->clase->option('A','Alta Rotacion');
		$edit->clase->option('B','Media Rotacion');
		$edit->clase->option('C','Baja Rotacion');
		$edit->clase->option('I','Importacion Propia');

		$ivas=$this->datasis->ivaplica();
		$edit->iva = new dropdownField('IVA %', 'iva');
		foreach($ivas as $tasa=>$ivamonto){
			$edit->iva->option($ivamonto,nformat($ivamonto));
		}
		$edit->iva->style='width:100px;';

		$edit->exento = new dropdownField('Vender Exento', 'exento');
		$edit->exento->style='width:50px;';
		$edit->exento->option('N','No' );
		$edit->exento->option('E','Si' );

		$edit->ultimo = new inputField('Ultimo', 'ultimo');
		$edit->ultimo->css_class='inputnum';
		$edit->ultimo->size=10;
		$edit->ultimo->maxlength=13;
		$edit->ultimo->autcomplete=false;
		$edit->ultimo->onkeyup = 'requeridos();';
		$edit->ultimo->rule='required|mayorcero';

		$edit->pond = new inputField('Promedio', 'pond');
		$edit->pond->css_class='inputnum';
		$edit->pond->size=10;
		$edit->pond->maxlength=13;
		$edit->pond->autcomplete=false;
		$edit->pond->onkeyup = 'requeridos();';
		$edit->pond->rule='required|mayorcero';

		$edit->standard = new inputField('Standard', 'standard');
		$edit->standard->css_class='inputnum';
		$edit->standard->autcomplete=false;
		$edit->standard->size=10;
		$edit->standard->maxlength=13;
		$edit->standard->insertValue=0;

		$edit->formcal = new dropdownField('Base C&aacute;lculo', 'formcal');
		$edit->formcal->style='width:110px;';
		$edit->formcal->rule='required|enum[U,P,M,S]';
		$edit->formcal->option('U','Ultimo');
		$edit->formcal->option('P','Promedio');
		$edit->formcal->option('M','Mayor');
		$edit->formcal->insertValue='U';
		$edit->formcal->onchange = 'requeridos();calculos(\'S\');';

		$edit->redecen = new dropdownField('Redondear', 'redecen');
		$edit->redecen->style='width:110px;';
		$edit->redecen->option('N','No Cambiar');
		$edit->redecen->option('M','Solo un Decimal');
		$edit->redecen->option('F','Sin Decimales');
		$edit->redecen->option('D','Decenas');
		$edit->redecen->option('C','Centenas');
		$edit->redecen->rule='enum[N,M,F,D,C]';

		for($i=1;$i<=4;$i++){
			$objeto="margen$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=6;
			$edit->$objeto->onkeyup = 'calculos(\'I\');';
			$edit->$objeto->autcomplete=false;
			$edit->$objeto->rule='required|mayorcero';

			$objeto="base$i";
			$edit->$objeto = new inputField("Base $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->autcomplete=false;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onkeyup = 'cambiobase(\'I\');';
			$edit->$objeto->rule='required|mayorcero';

			$objeto="precio$i";
			$edit->$objeto = new inputField("Precio $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->autcomplete=false;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onkeyup = 'cambioprecio(\'I\');';
			$edit->$objeto->rule='required|mayorcero';
		}

		$edit->existen = new inputField('Cantidad Actual','existen');
		$edit->existen->size=10;
		$edit->existen->readonly = true;
		$edit->existen->css_class='inputonlynum';
		$edit->existen->style='background:#F5F6CE;';

		$edit->exmin = new inputField('M&iacute;nimo', 'exmin');
		$edit->exmin->size=10;
		$edit->exmin->maxlength=12;
		$edit->exmin->css_class='inputonlynum';
		$edit->exmin->rule='numeric|callback_positivo|trim';

		$edit->exmax = new inputField('M&aacute;ximo', 'exmax');
		$edit->exmax->size=10;
		$edit->exmax->maxlength=12;
		$edit->exmax->css_class='inputonlynum';
		$edit->exmax->rule='numeric|callback_positivo|trim';

		$edit->exord = new inputField('Orden Proveedor','exord');
		$edit->exord->readonly = true;
		$edit->exord->size=10;
		$edit->exord->css_class='inputonlynum';
		$edit->exord->style='background:#F5F6CE;';

		$edit->exdes = new inputField('Pedidos Cliente','exdes');
		$edit->exdes->readonly = true;
		$edit->exdes->size=10;
		$edit->exdes->css_class='inputonlynum';
		$edit->exdes->style='background:#F5F6CE;';

		$edit->fechav = new dateField('Ultima Venta','fechav','d/m/Y');
		$edit->fechav->readonly = true;
		$edit->fechav->size=10;

		$edit->fdesde = new dateField('Desde','fdesde','d/m/Y');
		$edit->fdesde->size=10;

		$edit->fhasta = new dateField('Desde','fhasta','d/m/Y');
		$edit->fhasta->size=10;

		$edit->bonicant = new inputField('Cant. Bonifica', "bonicant");
		$edit->bonicant->size=10;
		$edit->bonicant->maxlength=12;
		$edit->bonicant->css_class='inputonlynum';
		$edit->bonicant->rule='numeric|callback_positivo|trim';

		$edit->bonifica = new inputField('Bonifica', 'bonifica');
		$edit->bonifica->size=10;
		$edit->bonifica->maxlength=12;
		$edit->bonifica->css_class='inputonlynum';
		$edit->bonifica->rule='numeric|callback_positivo|trim';

		//descuentos por escala
		for($i=1;$i<=3;$i++){
			$objeto="pescala$i";
			$edit->$objeto = new inputField('Descuento por escala '.$i,$objeto);
			$edit->$objeto->rule='numeric|callback_positivo|trim';
			$edit->$objeto->insertValue=0;
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=5;

			$objeto="escala$i";
			$edit->$objeto = new inputField('Cantidad m&iacute;nima para la escala '.$i,$objeto);
			$edit->$objeto->rule='numeric|callback_positivo|trim';
			$edit->$objeto->insertValue=0;
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
		}

		for($i=1;$i<=3;$i++){
			$objeto="pfecha$i";
			$edit->$objeto = new dateField("Fecha $i",$objeto,'d/m/Y');
			$edit->$objeto->when =array('show');
			$edit->$objeto->size=10;

			$objeto="Eprepro$i";
			$edit->$objeto = new freeField('','','Precio');
			$edit->$objeto->in="pfecha$i";
			$edit->$objeto->when =array('show');

			$objeto="prepro$i";
			$edit->$objeto = new inputField('',$objeto);
			$edit->$objeto->when =array('show');
			$edit->$objeto->size=10;
			$edit->$objeto->in="pfecha$i";

			$objeto="prov$i";
			$edit->$objeto = new inputField('',$objeto);
			$edit->$objeto->when =array('show');
			$edit->$objeto->size=10;

			$objeto="Eprov$i";
			$edit->$objeto = new freeField('','','Proveedor');
			$edit->$objeto->in="pfecha$i";
			$edit->$objeto->when =array('show');

			if($edit->_status=='show'){
				$prov=$edit->_dataobject->get('prov'.$i);
				$dbprov=$this->db->escape($prov);
				$proveed=$this->datasis->dameval("SELECT nombre FROM sprv WHERE proveed=$dbprov LIMIT 1");
				$objeto="proveed$i";
				$edit->$objeto= new freeField('','',$proveed);
				$edit->$objeto->in="pfecha$i";
			}
		}

		$codigo=$edit->_dataobject->get('codigo');
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array('show','modify');

		$edit->mmargen = new inputField('Margen al Mayor','mmargen');
		$edit->mmargen->css_class='inputnum';
		$edit->mmargen->size=10;
		$edit->mmargen->maxlength=10;

		$edit->mmargenplus = new inputField('Descuento +','mmargenplus');
		$edit->mmargenplus->css_class='inputnum';
		$edit->mmargenplus->size=10;
		$edit->mmargenplus->maxlength=10;

		$edit->pm = new inputField('Descuento al Mayor A','pm');
		$edit->pm->css_class='inputnum';
		$edit->pm->size=10;
		$edit->pm->maxlength=10;

		$edit->pmb = new inputField('Descuento al Mayor B','pmb');
		$edit->pmb->css_class='inputnum';
		$edit->pmb->size=10;
		$edit->pmb->maxlength=10;

		/*INICIO SINV COMBO*/
		$edit->itcodigo = new inputField('C&oacute;digo <#o#>', 'itcodigo_<#i#>');
		$edit->itcodigo->size    = 12;
		$edit->itcodigo->db_name = 'codigo';
		$edit->itcodigo->rel_id  = 'sinvcombo';
		$edit->itcodigo->append($bSINV_C);

		$edit->itdescrip = new inputField('Descripci&oacute;n <#o#>', 'itdescrip_<#i#>');
		$edit->itdescrip->size       = 32;
		$edit->itdescrip->db_name    = 'descrip';
		$edit->itdescrip->maxlength  = 50;
		$edit->itdescrip->readonly   = true;
		$edit->itdescrip->rel_id     = 'sinvcombo';
		$edit->itdescrip->type       = 'inputhidden';

		$edit->itcantidad = new inputField('Cantidad <#o#>', 'itcantidad_<#i#>');
		$edit->itcantidad->db_name      = 'cantidad';
		$edit->itcantidad->css_class    = 'inputnum';
		$edit->itcantidad->rel_id       = 'sinvcombo';
		$edit->itcantidad->maxlength    = 10;
		$edit->itcantidad->size         = 5;
		$edit->itcantidad->rule         = 'required|positive';
		$edit->itcantidad->autocomplete = false;
		$edit->itcantidad->onkeyup      = 'totalizar();';
		$edit->itcantidad->value        = '1';

		$edit->itultimo = new inputField('Ultimo <#o#>', 'itultimo_<#i#>');
		$edit->itultimo->size       = 32;
		$edit->itultimo->db_name    = 'ultimo';
		$edit->itultimo->maxlength  = 50;
		$edit->itultimo->readonly   = true;
		$edit->itultimo->rel_id     = 'sinvcombo';
		$edit->itultimo->type       = 'inputhidden';

		$edit->itpond = new inputField('Promedio <#o#>', 'itpond_<#i#>');
		$edit->itpond->size       = 32;
		$edit->itpond->db_name    = 'pond';
		$edit->itpond->maxlength  = 50;
		$edit->itpond->readonly   = true;
		$edit->itpond->rel_id     = 'sinvcombo';
		$edit->itpond->type       = 'inputhidden';

		$ocultos=array('precio1','formcal');
		foreach($ocultos as $obj){
			$obj2='it'.$obj;
			$edit->$obj2 = new hiddenField($obj.' <#o#>', $obj2 . '_<#i#>');
			$edit->$obj2->db_name = 'sinv'.$obj;
			$edit->$obj2->rel_id  = 'sinvcombo';
			$edit->$obj2->pointer = true;
		}

		$edit->itestampa = new autoUpdateField('itestampa' ,date('Ymd'), date('Ymd'));
		$edit->itestampa->db_name = 'estampa';
		$edit->itestampa->rel_id  = 'sinvcombo';

		$edit->ithora    = new autoUpdateField('ithora',date('H:i:s'), date('H:i:s'));
		$edit->ithora->db_name = 'hora';
		$edit->ithora->rel_id  = 'sinvcombo';

		$edit->itusuario = new autoUpdateField('itusuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->itusuario->db_name = 'usuario';
		$edit->itusuario->rel_id  = 'sinvcombo';

		/*INICIO SINV ITEM RECETAS*/
		$edit->it2codigo = new inputField('C&oacute;digo <#o#>', 'it2codigo_<#i#>');
		$edit->it2codigo->size    = 12;
		$edit->it2codigo->db_name = 'codigo';
		$edit->it2codigo->rel_id  = 'sinvpitem';
		$edit->it2codigo->append($bSINV_I);

		$edit->it2descrip = new inputField('Descripci&oacute;n <#o#>', 'it2descrip_<#i#>');
		$edit->it2descrip->size       = 32;
		$edit->it2descrip->db_name    = 'descrip';
		$edit->it2descrip->maxlength  = 50;
		$edit->it2descrip->readonly   = true;
		$edit->it2descrip->rel_id     = 'sinvpitem';
		$edit->it2descrip->type       = 'inputhidden';

		$edit->it2cantidad = new inputField('Cantidad <#o#>', 'it2cantidad_<#i#>');
		$edit->it2cantidad->db_name      = 'cantidad';
		$edit->it2cantidad->css_class    = 'inputnum';
		$edit->it2cantidad->rel_id       = 'sinvpitem';
		$edit->it2cantidad->maxlength    = 10;
		$edit->it2cantidad->size         = 5;
		$edit->it2cantidad->rule         = 'positive';
		$edit->it2cantidad->autocomplete = false;
		$edit->it2cantidad->onkeyup      = 'totalizarpitem(<#i#>)';
		$edit->it2cantidad->insertValue  = 1;

		$edit->it2merma = new inputField('Ultimo <#o#>', 'it2merma_<#i#>');
		$edit->it2merma->size       = 5;
		$edit->it2merma->db_name    = 'merma';
		$edit->it2merma->maxlength  = 15;
		$edit->it2merma->css_class  = 'inputnum';
		$edit->it2merma->rel_id     = 'sinvpitem';
		$edit->it2merma->insertValue= 0;

		$ocultos=array('ultimo','pond','formcal','id_sinv');
		foreach($ocultos as $obj){
			$obj2='it2'.$obj;
			$edit->$obj2 = new hiddenField($obj.' <#o#>', $obj2 . '_<#i#>');
			$edit->$obj2->db_name = $obj;
			$edit->$obj2->rel_id  = 'sinvpitem';
		}

		$edit->it2estampa = new autoUpdateField('it2estampa' ,date('Ymd'), date('Ymd'));
		$edit->it2estampa->db_name = 'estampa';
		$edit->it2estampa->rel_id = 'sinvpitem';

		$edit->it2hora    = new autoUpdateField('it2hora',date('H:i:s'), date('H:i:s'));
		$edit->it2hora->db_name = 'hora';
		$edit->it2hora->rel_id = 'sinvpitem';

		$edit->it2usuario = new autoUpdateField('it2usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->it2usuario->db_name = 'usuario';
		$edit->it2usuario->rel_id = 'sinvpitem';

		/*INICIO SINV LABOR  ESTACIONES*/
		$edit->it3estacion = new  dropdownField('Estacion <#o#>', 'it3estacion_<#i#>');
		$edit->it3estacion->option('','Seleccionar');
		$edit->it3estacion->options('SELECT estacion,CONCAT(estacion,\'-\',nombre) AS lab FROM esta ORDER BY estacion');
		$edit->it3estacion->style   = 'width:250px;';
		$edit->it3estacion->db_name = 'estacion';
		$edit->it3estacion->rel_id  = 'sinvplabor';

		$edit->it3actividad = new inputField('Actividad <#o#>', 'it3actividad_<#i#>');
		$edit->it3actividad->size       = 32;
		$edit->it3actividad->db_name    = 'actividad';
		$edit->it3actividad->maxlength  = 50;
		$edit->it3actividad->rel_id     = 'sinvplabor';

		$edit->it3minutos = new inputField('Minutos <#o#>', 'it3minutos_<#i#>');
		$edit->it3minutos->db_name      = 'minutos';
		$edit->it3minutos->css_class    = 'inputnum';
		$edit->it3minutos->rel_id       = 'sinvplabor';
		$edit->it3minutos->maxlength    = 10;
		$edit->it3minutos->size         = 5;
		$edit->it3minutos->rule         = 'positive';
		$edit->it3minutos->autocomplete = false;
		$edit->it3minutos->insertValue  = 0;

		$edit->it3segundos = new inputField('Segundos <#o#>', 'it3segundos_<#i#>');
		$edit->it3segundos->db_name      = 'segundos';
		$edit->it3segundos->css_class    = 'inputnum';
		$edit->it3segundos->rel_id       = 'sinvplabor';
		$edit->it3segundos->maxlength    = 10;
		$edit->it3segundos->size         = 5;
		$edit->it3segundos->rule         = 'positive';
		$edit->it3segundos->autocomplete = false;
		$edit->it3segundos->insertValue  = 1;

		$edit->it3estampa = new autoUpdateField('it3estampa' ,date('Ymd'), date('Ymd'));
		$edit->it3estampa->db_name = 'estampa';
		$edit->it3estampa->rel_id  = 'sinvpitem';

		$edit->it3hora    = new autoUpdateField('it3hora',date('H:i:s'), date('H:i:s'));
		$edit->it3hora->db_name = 'hora';
		$edit->it3hora->rel_id  = 'sinvpitem';

		$edit->it3usuario = new autoUpdateField('it3usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->it3usuario->db_name = 'usuario';
		$edit->it3usuario->rel_id  = 'sinvpitem';

		$inven=array();
		$query=$this->db->query('SELECT TRIM(codigo) AS codigo ,TRIM(descrip) AS descrip,tipo,base1,base2,base3,base4,iva,peso,precio1,pond,ultimo FROM sinv WHERE activo=\'S\' AND tipo=\'Articulo\'');
		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$ind='_'.$row->codigo;
				$inven[$ind]=array($row->descrip,$row->tipo,$row->base1,$row->base2,$row->base3,$row->base4,$row->iva,$row->peso,$row->precio1,$row->pond);
			}
		}

		$edit->button_status('btn_add_sinvcombo' ,'Agregar','javascript:add_sinvcombo()' ,'CO','modify','button_add_rel');
        $edit->button_status('btn_add_sinvcombo' ,'Agregar','javascript:add_sinvcombo()' ,'CO','create','button_add_rel');
        $edit->button_status('btn_add_sinvpitem' ,'Agregar','javascript:add_sinvpitem()' ,'IT','create','button_add_rel');
        $edit->button_status('btn_add_sinvpitem' ,'Agregar','javascript:add_sinvpitem()' ,'IT','modify','button_add_rel');
        $edit->button_status('btn_add_sinvplabor','Agregar','javascript:add_sinvplabor()','LA','create','button_add_rel');
        $edit->button_status('btn_add_sinvplabor','Agregar','javascript:add_sinvplabor()','LA','modify','button_add_rel');

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$mcodigo = $edit->codigo->value;
		$mfdesde = $this->datasis->dameval("SELECT ADDDATE(MAX(fecha),-30) FROM costos WHERE codigo='".addslashes($mcodigo)."'");
		$mfhasta = $this->datasis->dameval("SELECT MAX(fecha) FROM costos WHERE codigo='".addslashes($mcodigo)."'");

		$smenu['link']   = barra_menu('301');
		$conten['form']  =& $edit;

		$data['content'] = $this->load->view('view_sinv', $conten,true);
		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data['script'] .= script('jquery.alerts.js');
		$data['script'] .= script('plugins/jquery.blockUI.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('sinvmaes.js');
		$data['style']   = style('jquery.alerts.css');
		$data['style']  .= style('redmond/jquery-ui.css');
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading(substr($edit->descrip->value,0,30));
		$this->load->view('view_ventanas', $data);
	}

	function _pre_inserup($do){
		$tipo=$do->get('tipo');

		//SINVCOMBO
		foreach($do->data_rel['sinvcombo'] as $k=>$v){
			if(empty($v['codigo'])) $do->rel_rm('sinvcombo',$k);
		}
		if($tipo!='Combo' && count($do->data_rel['sinvcombo']) >0){
			$error='ERROR. el tipo de Art&acute;iculo debe ser Combo, debido a que tiene varios Art&iacute;culos relacionados';
			$do->error_message_ar['pre_upd']=$do->error_message_ar['pre_ins']=$error;
			return false;
		}
		if($tipo=='Combo' && count($do->data_rel['sinvcombo']) <=0){
			$error='ERROR. El Combo debe tener almenos un art&iacute;culo';
			$do->error_message_ar['pre_upd']=$do->error_message_ar['pre_ins']=$error;
			return false;
		}
		//SINVPITEM
		$borrar=array();
		foreach($do->data_rel['sinvpitem'] as $k=>$v){
			if(empty($v['codigo'])) $do->rel_rm('sinvpitem',$k);
		}
		//SINVPLABOR
		$borrar=array();
		foreach($do->data_rel['sinvplabor'] as $k=>$v){
			if(empty($v['estacion'])) $do->rel_rm('sinvplabor',$k);
		}

		//Valida los precios
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
		}else{
			$do->error_message_ar['pre_upd'] = 'Los precios deben cumplir con:<br> Precio 1 mayor o igual al Precio 2 mayor o igual al  Precio 3 mayor o igual al Precio 4';
			return false;
		}

		//valida las escalas
		for($i=1;$i<4;$i++){
			$esca='pescala'.$i;
			$$esca=$do->get($esca);
			$esca='escala'.$i;
			$$esca=$do->get($esca);
		}

		if(!($pescala3>=$pescala2 && $pescala2>=$pescala1 && $escala3>=$escala2 && $escala2>=$escala1)){
			$do->error_message_ar['pre_upd'] = 'Las escalas deben cumplir con:<br> Escala 3 mayor o igual a la Escala 2 mayor o igual a la Escala 3, en cantidades y descuentos';
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

		$from  = $data['data1'];
		$where = $data['data2'];

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
	//  Cambia el Grupo
	//
	function sinvcamgrup() {
		$productos  = $this->input->post('productos');
		$mgrupo     = rawurldecode($this->input->post('grupo'));

		if($this->datasis->dameval("SELECT COUNT(*) FROM grup WHERE grupo='$mgrupo'") == 0 ){
			echo "Grupo no existe $mgrupo";
		} else {
			//Busca el Depto y Linea del grupo
			$depto = $this->datasis->dameval("SELECT depto FROM grup WHERE grupo='$mgrupo'");
			$linea = $this->datasis->dameval("SELECT linea FROM grup WHERE grupo='$mgrupo'");
			$productos = substr(trim($productos),0,-1);
			//echo "$mgrupo $productos";
			$mSQL = "UPDATE sinv SET grupo='$mgrupo', linea='$linea', depto='$depto' WHERE id IN ($productos) ";
			$this->db->simple_query($mSQL);
			logusu("SINV","Cambio grupo ".$mgrupo."-->".$productos);
			echo "Cambiado a Depto $depto, linea $linea, grupo $mgrupo Exitosamente";
		}
	}

	//*****************************
	//
	//  Cambia el Marca
	//
	function sinvcammarca() {
		$productos  = $this->input->post('productos');
		$mmarca     = rawurldecode($this->input->post('marca'));

		if($this->datasis->dameval("SELECT COUNT(*) FROM marc WHERE TRIM(marca)='".addslashes($mmarca)."'") == 0 ){
			echo "Marca no existe $mmarca";
		} else {
			//Busca el Depto y Linea del grupo
			$productos = substr(trim($productos),0,-1);
			$mSQL = "UPDATE sinv SET marca='".addslashes($mmarca)."' WHERE id IN ($productos) ";
			$this->db->simple_query($mSQL);
			logusu("SINV","Cambio marca ".$mmarca."-->".$productos);
			echo "Cambiadas las  marcas $mmarca Exitosamente";
		}
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

		if($mexiste=='S'){
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
		}else{
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

	// Borra Codigo de proveedores
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

	// Agrega el codigo del producto segun el Proveedor
	function sinvsprv(){
		$codigo  = $this->uri->segment($this->uri->total_segments());
		$cod_prv = $this->uri->segment($this->uri->total_segments()-1);
		$id      = $this->uri->segment($this->uri->total_segments()-2);
		$mSQL = "REPLACE INTO sinvprov SELECT '$cod_prv' proveed, '$codigo' codigop, codigo FROM sinv WHERE id=$id ";
		$this->db->simple_query($mSQL);
		echo " codigo=$codigo guardado al prv $cod_prv " ;
	}

	//*************************
	//
	// Promociones
	//
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

	//***************************
	//
	// Promociones a clientes
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
			$mSQL = "DELETE FROM sinvpromo WHERE codigo='$htmlcod' AND cliente='$cod_cli'";
		} else {
			$mSQL = "UPDATE sinvpromo SET margen=$porcent, tipo='$tipo' WHERE codigo='$htmlcod' AND cliente='$cod_cli'";
		}
		$this->db->simple_query($mSQL);
		logusu("SINV","Promocion cliente $cod_cli codigo ".$htmlcod."-->".$porcent);

		echo "Descuento Guardado ";
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

	//Segun coicoi cambia los precios
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

				$cod=$this->datasis->dameval('SELECT codigo FROM sinv WHERE id='.$dbid);
				logusu('sinv',"Cambio de precios a $cod $p1; $p2; $p3; $p4");
			}else{
				$codigo=$this->datasis->dameval("SELECT codigo FROM sinv WHERE id=${dbid}");
				$msj.='En el art&iacute;culo '.TRIM($codigo).' no se actualizo porque los precios deben tener valores mayores que el costo y en forma decrecientes (Precio 1 >= Precio 2 >= Precio 3 >= Precio 4).'.br();
			}

		}
		if($error>0) $msj.='Hubo alg&uacute;n error, se gener&oacute; un centinela';
		return $msj;
	}

	// Sugiere proximo codigo de inventario
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

	// Busca el Ultimo codigo
	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT codigo FROM sinv ORDER BY codigo DESC LIMIT 1");
		echo $ultimo;
	}

	function sugerir(){
		$ultimo=$this->datasis->dameval("SELECT LPAD(hexa,4,0) FROM serie LEFT JOIN sinv ON LPAD(codigo,4,0)=LPAD(hexa,4,0) WHERE valor<65535 AND codigo IS NULL LIMIT 1");
		echo $ultimo;
	}

	function chexiste($codigo){
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE codigo='$codigo'");
		if ($chek > 0){
			$descrip=$this->datasis->dameval("SELECT descrip FROM sinv WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el producto $descrip");
			return FALSE;
		}else {
		 return TRUE;
		}
	}

	// Si exsite el codigo Alterno
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

	//
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
			</style>";
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

	// Trae la descripcion de una Barra
	function barratonombre(){
		if($this->input->post('barra')){
			$barra=$this->db->escape($this->input->post('barra'));
			echo $this->datasis->dameval("SELECT descrip FROM sinv WHERE barras=$barra");
		}
	}

	//Consulta rapida
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

		/*mes, cventa, mventa, mpvp, ccompra, mcompra,util, margen, promedio*/
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
		</script>";

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
		</table>";

		$data['title']    = '<h1>Consulta de Articulo de Inventario</h1>';

		$data['script']   = script("plugins/jquery.numeric.pack.js");
		$data['script']  .= script("plugins/jquery.floatnumber.js");
		$data['script']  .= script("gt_msg_en.js");
		$data['script']  .= script("gt_grid_all.js");
		$data['script']  .= $script;

		$data['style']    = style('gt_grid.css');
		$data["subtitle"] = "
			<div align='center' style='border: 2px outset #EFEFEF;background: #EFEFEF;font-size:18px'>
				<a href='javascript:javascript:history.go(-1)'>(".addslashes($mCodigo).") ".$descrip."</a>
			</div>";

		$data['head']  = $this->rapyd->get_head();
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
		GROUP BY MID( a.fecha ,1,7)  WITH ROLLUP LIMIT 24";

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

	function _post_insert($do){
		$codigo=$do->get('codigo');

		$precio1=$do->get('precio1');
		$precio2=$do->get('precio2');
		$precio3=$do->get('precio3');
		$precio4=$do->get('precio4');
		logusu('sinv',"Creo  $codigo precios: $precio1,$precio2,$precio3, $precio4");
	}

	function _post_update($do){
		$codigo=$do->get('codigo');

		$precio1=$do->get('precio1');
		$precio2=$do->get('precio2');
		$precio3=$do->get('precio3');
		$precio4=$do->get('precio4');
		logusu('sinv',"Modifico $codigo precios: $precio1,$precio2,$precio3, $precio4");
	}

	function _post_delete($do){
		$codigo=$do->get('codigo');
		logusu('sinv',"Elimino $codigo");
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
		//$mSQL="ALTER TABLE `sinvplabor` ALTER `actividad` DROP DEFAULT";
		//$mSQL="ALTER TABLE `sinvplabor` CHANGE COLUMN `actividad` `actividad` VARCHAR(100) NOT NULL AFTER `nombre`";

		$campos = $this->db->list_fields('sinv');
		if (!in_array('id',$campos)){
			$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
			$this->db->simple_query($mSQL);
			$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
			$this->db->simple_query($mSQL);
		}

		if (!in_array('alto'       ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD alto DECIMAL(10,2)");
		if (!in_array('alto'       ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD ancho DECIMAL(10,2)");
		if (!in_array('largo'      ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD largo DECIMAL(10,2)");
		if (!in_array('forma'      ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD forma VARCHAR(50)");
		if (!in_array('exento'     ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD exento CHAR(1) DEFAULT 'N'");
		if (!in_array('mmargen'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD mmargen DECIMAL(7,2) DEFAULT 0 COMMENT 'Margen al Mayor'");
		if (!in_array('pm'         ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pm` DECIMAL(19,2) NULL DEFAULT '0.00' COMMENT 'porcentaje mayor'");
		if (!in_array('pmb'        ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pmb` DECIMAL(19,2) NULL DEFAULT '0.00' COMMENT 'porcentaje mayor'");
		if (!in_array('mmargenplus',$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `mmargenplus` DECIMAL(7,2) NULL DEFAULT '0.00' COMMENT 'Margen al Mayor'");
		if (!in_array('escala1'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `escala1` DECIMAL(12,2) NULL DEFAULT '0.00'");
		if (!in_array('pescala1'   ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pescala1` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala1'");
		if (!in_array('escala2'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `escala2` DECIMAL(12,2) NULL DEFAULT '0.00'");
		if (!in_array('pescala2'   ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pescala2` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala2'");
		if (!in_array('escala3'    ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `escala3` DECIMAL(12,2) NULL DEFAULT '0.00'");
		if (!in_array('pescala3'   ,$campos)) $this->db->simple_query("ALTER TABLE `sinv` ADD COLUMN `pescala3` DECIMAL(5,2) NULL DEFAULT '0.00' COMMENT 'porcentaje descuento escala3'");

		if(!$this->db->table_exists('sinvcombo')){
			$mSQL="CREATE TABLE `sinvcombo` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`combo` CHAR(15) NOT NULL,
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				`descrip` CHAR(30) NULL DEFAULT NULL,
				`cantidad` DECIMAL(10,3) NULL DEFAULT NULL,
				`precio` DECIMAL(15,2) NULL DEFAULT NULL,
				`transac` CHAR(8) NULL DEFAULT NULL,
				`estampa` DATE NULL DEFAULT NULL,
				`hora` CHAR(8) NULL DEFAULT NULL,
				`usuario` CHAR(12) NULL DEFAULT NULL,
				`costo` DECIMAL(17,2) NULL DEFAULT '0.00',
				`ultimo` DECIMAL(19,2) NULL DEFAULT '0.00',
				`pond` DECIMAL(19,2) NULL DEFAULT '0.00',
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sinvpitem')){
			$mSQL="CREATE TABLE `sinvpitem` (
				`producto` VARCHAR(15) NULL DEFAULT NULL COMMENT 'codigo del prod terminado (sinv)',
				`codigo` VARCHAR(15) NULL DEFAULT NULL COMMENT 'codigo del Insumo (sinv)',
				`descrip` VARCHAR(40) NULL DEFAULT NULL,
				`cantidad` DECIMAL(14,3) NULL DEFAULT '0.000',
				`merma` DECIMAL(10,2) NULL DEFAULT '0.00' COMMENT 'Porcentaje de merma',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id_sinv` INT(11) NULL DEFAULT NULL,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`ultimo` DECIMAL(19,2) NOT NULL DEFAULT '0.00',
				`pond` DECIMAL(19,2) NOT NULL DEFAULT '0.00',
				`formcal` CHAR(1) NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`)
			)
			COMMENT='Insumos de un producto terminado'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DYNAMIC
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('sinvplabor')){
			$mSQL="CREATE TABLE `sinvplabor` (
				`producto` VARCHAR(15) NULL DEFAULT '' COMMENT 'Producto Terminado',
				`estacion` VARCHAR(5) NULL DEFAULT NULL,
				`nombre` VARCHAR(40) NULL DEFAULT NULL,
				`actividad` VARCHAR(100) NOT NULL,
				`minutos` INT(6) NULL DEFAULT '0',
				`segundos` INT(6) NULL DEFAULT '0',
				`estampa` DATE NULL DEFAULT NULL,
				`usuario` VARCHAR(12) NULL DEFAULT '',
				`hora` VARCHAR(8) NULL DEFAULT '',
				`modificado` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				INDEX `modificado` (`modificado`)
			)
			COMMENT='Acciones de la Orden de Produccion'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('esta')){
			$mSQL="CREATE TABLE `esta` (
				`estacion` VARCHAR(5) NOT NULL DEFAULT '',
				`nombre` VARCHAR(30) NULL DEFAULT NULL,
				`descrip` TEXT NULL,
				`ubica` TEXT NULL,
				`jefe` VARCHAR(5) NULL DEFAULT NULL COMMENT 'tecnico',
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `vendedor` (`estacion`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=1;";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('barraspos')){
			$query="CREATE TABLE `barraspos` (
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				`suplemen` CHAR(15) NOT NULL DEFAULT '',
				PRIMARY KEY (`codigo`, `suplemen`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
			$this->db->simple_query($query);
		}
		if(!$this->db->table_exists('invfelr')){
			$query="CREATE TABLE `invfelr` (
				`codigo` CHAR(15) NOT NULL DEFAULT '',
				`fecha` DATE NOT NULL DEFAULT '0000-00-00',
				`precio` DECIMAL(17,2) NOT NULL DEFAULT '0.00',
				`existen` DECIMAL(17,2) NULL DEFAULT NULL,
				`anterior` DECIMAL(17,2) NULL DEFAULT NULL,
				`parcial` DECIMAL(17,2) NULL DEFAULT NULL,
				`alma` CHAR(4) NOT NULL DEFAULT '',
				`tipo` CHAR(1) NULL DEFAULT NULL,
				`fhora` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
				`usuario` CHAR(12) NULL DEFAULT NULL,
				`ubica` CHAR(10) NOT NULL DEFAULT ''
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DEFAULT";
		}
	}
}
