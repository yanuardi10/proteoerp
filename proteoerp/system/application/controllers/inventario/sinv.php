<?php include('common.php');
class sinv extends Controller {

	function sinv(){
		parent::Controller(); 
		$this->load->library('rapyd');
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

		$script='
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
		}';

		$filter = new DataFilter2('Filtro por Producto');

		$filter->db->select("a.existen AS existen,a.marca marca,a.tipo AS tipo,id,codigo,a.descrip,precio1,precio2,precio3,precio4,b.nom_grup AS nom_grup,b.grupo AS grupoid,c.descrip AS nom_linea,c.linea AS linea,d.descrip AS nom_depto,d.depto AS depto");
		$filter->db->from('sinv AS a');
		$filter->db->join('grup AS b','a.grupo=b.grupo');
		$filter->db->join('line AS c','b.linea=c.linea');
		$filter->db->join('dpto AS d','c.depto=d.depto');
		$filter->script($script);

		$filter->barras = new inputField("C&oacute;digo de barras", "barras");
		$filter->barras -> size=25;

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
		$filter->marca->style='width:220px;';
		$filter->marca->group = "Dos";

		$filter->buttons("reset","search");
		$filter->build("dataformfiltro");

		$uri = "inventario/sinv/dataedit/show/<#codigo#>";

		$grid = new DataGrid("Art&iacute;culos de Inventario");
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;
		$link=anchor('/inventario/sinv/dataedit/show/<#id#>','<#codigo#>');

		$uri_2  = anchor('inventario/sinv/dataedit/create/<#id#>',img(array('src'=>'images/duplicar.jpeg','border'=>'0','alt'=>'Duplicar','height'=>'12')));
		$uri_2 .= anchor('inventario/sinv/consulta/<#id#>',img(array('src'=>'images/estadistica.jpeg','border'=>'0','alt'=>'Consultar','height'=>'12')));


		$grid->column_orderby("C&oacute;digo",$link,"codigo");
		$grid->column_orderby("Descripci&oacute;n","descrip","descrip");
		$grid->column_orderby("Marca","marca","marca");
		$grid->column_orderby("Precio 1","<nformat><#precio1#></nformat>","precio1",'align=right');
		$grid->column_orderby("Precio 2","<nformat><#precio2#></nformat>","precio2",'align=right');
//		$grid->column_orderby("Precio 3","<nformat><#precio3#></nformat>","precio3",'align=right');
		$grid->column_orderby("Existencia","<nformat><#existen#></nformat>","existen",'align=right');
		$grid->column("Acci&oacute;n",$uri_2     ,"align='center'");

		$grid->add('inventario/sinv/dataedit/create');
		$grid->build();

		//echo $grid->db->last_query();
		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		$data['title']   = heading('Maestro de Inventario');
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes2.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

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

		$script='
		function dpto_change(){
			$.post("'.$link12.'",{ depto:$("#depto").val() },function(data){alert("sasa");$("#linea").html(data);})
			$.post("'.$link14.'",{ linea:"" },function(data){$("#grupo").html(data);})
		}
		
		$(function(){
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
		});

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
					}
					else{
						alert("No es posible generar otra sugerencia. Coloque el c&oacute;digo manualmente");
					}
				}
			});
		}

		function add_marca(){
			marca=prompt("Introduza el nombre de la MARCA a agregar");
			if(marca==null){
			}else{
				$.ajax({
				 type: "POST",
				 processData:false,
					url: "'.$link.'",
					data: "valor="+marca,
					success: function(msg){
						if(msg=="s.i"){
							marca=marca.substr(0,30);
							$.post("'.$link4.'",{ x:"" },function(data){$("#marca").html(data);$("#marca").val(marca);})
						}
						else{
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
		}';

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
		$edit->codigo->size=20;
		$edit->codigo->maxlength=15;
		$edit->codigo->rule = "trim|required|strtoupper|callback_chexiste";
		$edit->codigo->mode="autohide";
		$edit->codigo->append($sugerir);
		$edit->codigo->append($ultimo);

		$edit->alterno = new inputField("C&oacute;digo Alterno", "alterno");
		$edit->alterno->size=20;  
		$edit->alterno->maxlength=15;
		$edit->alterno->rule = "trim|strtoupper|unique";
		
		$edit->enlace  = new inputField("C&oacute;digo Caja", "enlace");
		$edit->enlace ->size=20;
		$edit->enlace->maxlength=15;
		$edit->enlace->rule = "trim|strtoupper";
				
		$edit->barras = new inputField("C&oacute;digo Barras", "barras");
		$edit->barras->size=20;
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
		$edit->unidad->append($AddUnidad);

		$edit->clave = new inputField("Clave", "clave");
		$edit->clave->size=10;
		$edit->clave->maxlength=8;
		$edit->clave->rule = "trim|strtoupper";

		$AddDepto='<a href="javascript:add_depto();" title="Haz clic para Agregar un nuevo Departamento">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->depto = new dropdownField("Depto.", "depto");
		$edit->depto->rule ="required";
		$edit->depto->style='width:180px;';
		$edit->depto->option("","Seleccione un Departamento");
		$edit->depto->options("SELECT depto, CONCAT(depto,'-',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$edit->depto->append($AddDepto);

		$AddLinea='<a href="javascript:add_linea();" title="Haz clic para Agregar una nueva Linea;">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->linea = new dropdownField("L&iacute;nea","linea");
		$edit->linea->rule ="required";
		$edit->linea->style='width:180px;';
		$edit->linea->append($AddLinea);
		$depto=$edit->getval('depto');
		if($depto!==FALSE){
			$edit->linea->options("SELECT linea, CONCAT(LINEA,'-',descrip) descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$edit->linea->option("","Seleccione un Departamento primero");
		}

		$AddGrupo='<a href="javascript:add_grupo();" title="Haz clic para Agregar un nuevo Grupo;">'.image('list_plus.png','Agregar',array("border"=>"0")).'</a>';
		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->rule="required";
		$edit->grupo->style='width:180px;';

		$edit->grupo->append($AddGrupo);
		$linea=$edit->getval('linea');
		if($linea!==FALSE){
			$edit->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$edit->grupo->option("","Seleccione un Departamento primero");
		}

		$edit->comision  = new inputField("Comisi&oacute;n %", "comision");
		$edit->comision ->size=10;
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
		$edit->peso ->size=10;
		$edit->peso->maxlength=12;
		$edit->peso->css_class='inputnum';
		$edit->peso->rule='numeric|callback_positivo|trim';

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
		$edit->modelo->size=20;  
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
		$edit->formcal->style='width:80px;';
		//$edit->formcal->rule="required";
		//$edit->formcal->option("","Seleccione" );
		$edit->formcal->option("U","Ultimo" );
		$edit->formcal->option("P","Promedio" );
		$edit->formcal->option("M","Mayor" );
		$edit->formcal->onchange = "requeridos();calculos('I');";

		$edit->redecen = new dropdownField("Redondear", "redecen");
		$edit->redecen->style='width:80px;';
		$edit->redecen->option("NO","No");
		$edit->redecen->option("F","Fracci&oacute;n");
		$edit->redecen->option("D","Decena" );  
		$edit->redecen->option("C","Centena"  );
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

			$objeto="Ebase$i";
			$edit->$objeto = new freeField("","","Precio $i");
			$edit->$objeto->in="margen$i";

			$objeto="base$i";
			$edit->$objeto = new inputField("Base $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->autcomplete=false;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onkeyup = "cambiobase('I');";
			$edit->$objeto->rule="required";

			$objeto="Eprecio$i";
			$edit->$objeto = new freeField("","","Precio + I.V.A. $i");
			$edit->$objeto->in="margen$i";

			$objeto="precio$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->autcomplete=false;
			$edit->$objeto->maxlength=6;
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

		$edit->exord = new inputField("Orden a Prv..","exord");
		$edit->exord->readonly = true;
		$edit->exord->size=10;
		$edit->exord->css_class='inputonlynum';
		$edit->exord->style='background:#F5F6CE;';

		$edit->exdes = new inputField("Pedidos Cliente","exdes");
		$edit->exdes->readonly = true;
		$edit->exdes->size=10;
		$edit->exdes->css_class='inputonlynum';
		$edit->exdes->style='background:#F5F6CE;';
		//$edit->exdes->when =array("show");

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

		if($edit->_status=="show"){

		}

		$smenu['link']   = barra_menu('301');

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_sinv', $conten,true);
		$data["head"]    = script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes.js").$this->rapyd->get_head();

		//$data['content'] = $edit->output;
		$data['title']   = heading('Maestro de Inventario');
		//$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes.js").$this->rapyd->get_head();
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
		if(!empty($codigo)){
			$this->rapyd->load('dataedit','datagrid');
			$grid = new DataGrid('Existencias por Almacen');
			//$grid->db->select(array('b.ubides','a.codigo','a.alma','a.existen','a.precio1','a.precio2','a.precio3','a.precio4',"IF(b.ubides IS NULL,'ALMACEN INCONSISTENTE',b.ubides) AS nombre"));
			$grid->db->select(array('b.ubides','a.codigo','a.alma','a.existen',"IF(b.ubides IS NULL,'ALMACEN INCONSISTENTE',b.ubides) AS nombre"));			$grid->db->from('itsinv AS a');
			$grid->db->join('caub as b','a.alma=b.ubica','LEFT');
			$grid->db->where('codigo',$codigo);
			
			$grid->column('Almac&eacute;n','alma');
			$grid->column('Nombre'       ,'<#nombre#>');	
			$grid->column('Cantidad'      ,'existen','align="RIGHT"');
			//$grid->column('Precio1'      ,'precio1','align="RIGHT"');
			//$grid->column('Precio2'      ,'precio2','align="RIGHT"');
			//$grid->column('Precio3'      ,'precio3','align="RIGHT"');
			//$grid->column('Precio4'      ,'precio3','align="RIGHT"');
			
			$grid->build();
			if($grid->recordCount>0) $salida=$grid->output;
		}
		return $salida;
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

		$mCodigo = $this->datasis->dameval("SELECT codigo FROM sinv WHERE id=".$claves['id']."");
		
		$grid = new DataGrid('Ventas por Mes');
		$grid->db->_protect_identifiers=false;
		$grid->db->select( array('a.tipoa','MID(a.fecha,1,7) mes', 'sum(a.cana*(a.tipoa="F")) cventa', 'sum(a.cana*(a.tipoa="D")) cdevol', 'sum(a.cana*if(a.tipoa="D",-1,1)) cana', 'sum(a.tota*(a.tipoa="F")) mventa','sum(a.tota*(a.tipoa="D")) mdevol','sum(a.tota*if(a.tipoa="D",-1,1)) tota') );
		$grid->db->from('sitems a');
		$grid->db->where('a.codigoa', $mCodigo );
		$grid->db->where('a.tipoa IN ("F","D")');
		$grid->db->where('a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),"01")' );
		$grid->db->groupby('MID( `a`.`fecha` , 1 , 7 )  WITH ROLLUP');
			
		$grid->column("Mes"   ,"mes" );
		$grid->column("Cant. Venta", "<nformat><#cventa#></nformat>",'align="RIGHT"');
		$grid->column("Cant. Dev.",  "<nformat><#cdevol#></nformat>",'align="RIGHT"');
		$grid->column("Cantidad",    "<nformat><#cana#></nformat>",  'align="RIGHT"');
		$grid->column("Total Vent",  "<nformat><#mventa#></nformat>",'align="RIGHT"');
		$grid->column("Total Dev.",  "<nformat><#mdevol#></nformat>",'align="RIGHT"');
		$grid->column("Total",       "<nformat><#tota#></nformat>",'  align="RIGHT"');
		$grid->build();

		$grid2 = new DataGrid('Ultimos Cambios');
		$grid2->db->_protect_identifiers=false;
		$grid2->db->select( array( 'a.usuario','a.fecha', 'MID(a.hora,1,5) hora', 'a.comenta' ) );
		$grid2->db->from('logusu a');
		$grid2->db->where('a.comenta LIKE "%$mCodigo%"' );
		$grid2->db->orderby('a.fecha DESC');
		$grid2->db->limit(10);
			
		$grid2->column("Fecha"   ,   "fecha"     );
		$grid2->column("Usuario",    "usuario"   );
		$grid2->column("hora",       "hora"      );
		$grid2->column("Comentario", "comentario");
		$grid2->build();



		$descrip = $this->datasis->dameval("SELECT descrip FROM sinv WHERE id=".$claves['id']." ");
		$data['content'] = "
		<table width='100%' border='1'>
			<tr>
				<td rowspan='2' valign='top'>
					<div style='border: 2px outset #EFEFEF;background: #EFEFFF '>
					".$grid->output."
					</div>
					<div style='border: 2px outset #EFEFEF;background: #EFEFFF '>
					".$grid2->output."
					</div>
					
				</td>
				<td>".
				open_flash_chart_object( 250,180, site_url("inventario/sinv/ventas/$mCodigo"))."
				</td>
			</tr>
			<tr>
				<td>".
				open_flash_chart_object( 250,180, site_url("inventario/sinv/compras/".raencode($mCodigo)))."
				</td>
			</tr>
		</table>";
		$data["head"]     = script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = '<h1>Consulta de Articulo de Inventario</h1>';
		$data["subtitle"] = "
			<div align='center' style='border: 2px outset #EFEFEF;background: #EFEFEF;font-size:18px'>
				<a href='javascript:javascript:history.go(-1)'>(".$mCodigo.") ".$descrip."</a>
			</div>";
		$this->load->view('view_ventanas', $data);
		
	}

	function ventas($codigo=''){
		if (empty($codigo)) return; 
		$this->load->library('Graph');
		                           	                            
		$mSQL = "SELECT	a.tipoa,MID(a.fecha,1,7) mes,
			sum(a.cana*(a.tipoa='F')) cventa,
			sum(a.cana*(a.tipoa='D')) cdevol,
			sum(a.cana*if(a.tipoa='D',-1,1)) cana,
			sum(a.tota*(a.tipoa='F')) mventa,
			sum(a.tota*(a.tipoa='D')) mdevol,
			sum(a.tota*if(a.tipoa='D',-1,1)) tota
		FROM sitems a 
		WHERE a.codigoa='$codigo' AND a.tipoa IN ('F','D') AND a.fecha >= CONCAT(MID(SUBDATE(curdate(),365),1,8),'01')
		GROUP BY MID( a.fecha, 1,7 )  ";
		
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

	function compras($codigo=''){
		if (empty($codigo)) return; 
		$this->load->library('Graph');
		                           	                            
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
		GROUP BY MID( b.fecha, 1,7 )  ";
		
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