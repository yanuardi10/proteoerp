<?php
class maes extends Controller {

	function maes(){
		parent::Controller(); 
		$this->load->library("rapyd");
	}

	function index(){
		//$this->datasis->modulo_id(309,1);
		redirect("supermercado/maes/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load("datafilter2","datagrid");
				
		rapydlib("prototype");
		$ajax_onchange = '
			  function get_familias(){
			    var url = "'.site_url('supermercado/maes/maesfamilias').'";
			    var pars = "dpto="+$F("depto");
			    var myAjax = new Ajax.Updater("td_familia", url, { method: "post", parameters: pars });
			    
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var gmyAjax = new Ajax.Updater("td_grupo", url);
			  }
			  
			  function get_grupo(){
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var pars = "dpto="+$F("depto")+"&fami="+$F("familia");
			    var myAjax = new Ajax.Updater("td_grupo", url, { method: "post", parameters: pars });
			  }';

		$filter = new DataFilter2("Filtro por Producto");
		$select=array("a.descrip as descripcion","a.tipo","a.marca","a.codigo","a.familia","a.grupo","a.depto","b.nom_grup AS nom_grup","c.descrip AS nom_fami","d.descrip AS nom_depto");		
		$filter->db->select($select);
		$filter->db->from("maes AS a");
		$filter->db->join("grup AS b","a.grupo=b.grupo");
		$filter->db->join("fami AS c","a.familia=c.familia");
		$filter->db->join("dpto AS d","c.depto=d.depto");
		$filter->db->groupby("a.codigo");
		$filter->script($ajax_onchange);

		$filter->codigo = new inputField("C&oacute;digo", "a.codigo");
		$filter->codigo->size=20;
		$filter->codigo->maxlength=15;
				
		$filter->tipo = new dropdownField("Tipo", "a.tipo");
		$filter->tipo->option("","" );
		$filter->tipo->option("I","supermercado" );
		$filter->tipo->option("L","Licores"    );
		$filter->tipo->option("P","Por peso"   );
		$filter->tipo->option("K","Desposte"   );
		$filter->tipo->option("C","Combo"      );
		$filter->tipo->option("F","Farmaco"    );
		$filter->tipo->option("S","Servicio"   );
		$filter->tipo->option("R","Receta"     );
		$filter->tipo->option("D","Desactivado");
		$filter->tipo->style='width:110px;';
		
		$filter->marca = new dropdownField("Marca", "a.marca");
		$filter->marca->option("","");  
		$filter->marca->options("SELECT marca as codigo, marca FROM marc ORDER BY marca");  	
		$filter->marca->style='width:180px;';
					
		$filter->dpto = new dropdownField("Departamento", "depto");
		$filter->dpto->db_name="a.depto";
		$filter->dpto->option("","");
		$filter->dpto->options("SELECT depto,descrip FROM dpto WHERE tipo='I' ORDER BY descrip");
		$filter->dpto->onchange = "get_familias();";
		 
		$filter->familia = new dropdownField("Familia", "familia");
		$filter->familia->db_name="a.familia";
		$filter->familia->option("","Seleccione un departamento");
		$filter->familia->onchange = "get_grupo();";
    
		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="a.familia";
		$filter->grupo->option("","Seleccione una familia");
		
		$filter->buttons("reset","search");
		$filter->build();
					
		$grid = new DataGrid("Lista de Art&iacute;culos");
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;
		
		$link=anchor('/supermercado/maes/dataedit/show/<#codigo#>','<#codigo#>');
		$uri_2 = anchor('supermercado/maes/dataedit/create/<#codigo#>','Duplicar');
		
		$grid->column("c&oacute;digo",$link);
		$grid->column("Departamento","nom_depto");
		$grid->column("Familia","nom_fami");
		$grid->column("Grupo","nom_grup");
		$grid->column("Descripcion","descripcion");
		$grid->column("Duplicar",$uri_2     ,"align='center'");
										
		$grid->add("supermercado/maes/dataedit/create");
		$grid->build();
		
		$data["crud"] = $filter->output . $grid->output;
		$data["titulo"] = 'Lista de Art&iacute;culos';

		$content["content"]   = $this->load->view('rapyd/crud', $data, true);
		$content["rapyd_head"] = $this->rapyd->get_head();
		$content["code"] = '';
		$content["lista"] = "
			<h3>Editar o Agregar</h3>
			<div>Con esta pantalla se puede editar o agregar datos a los Departamentos del M&oacute;dulo de supermercado</div>
			<div class='line'></div>
			<a href='#' onclick='window.close()'>Cerrar</a>
			<div class='line'></div>\n<br><br><br>\n";
		$this->load->view('rapyd/tmpsolo', $content);
	}
	<?php
	function dataedit1($status='',$id='' ) {
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataedit2','dataobject');

		$link  =site_url('supermercado/common/add_marc');
		$link4 =site_url('supermercado/common/get_marca');
		$link5 =site_url('supermercado/common/add_unidad');
		$link6 =site_url('supermercado/common/get_unidad');
		$link7 =site_url('supermercado/maes/ultimo');
		$link8 =site_url('supermercado/maes/sugerir');
		$link9 =site_url('supermercado/common/add_depto');
		$link10=site_url('supermercado/common/get_depto');
		$link11=site_url('supermercado/common/add_familia');
		$link12=site_url('supermercado/common/get_familia');
		$link13=site_url('supermercado/common/add_grupo');
		$link14=site_url('supermercado/common/get_grupo');

		$script='
		function dpto_change(){
			$.post("'.$link12.'",{ depto:$("#depto").val() },function(data){$("#familia").html(data);})
			$.post("'.$link14.'",{ familia:"" },function(data){$("#grupo").html(data);})
		}
		$(function(){
			$("#depto").change(function(){dpto_change(); });
			$("#familia").change(function(){ $.post("'.$link14.'",{ familia:$(this).val() },function(data){$("#grupo").html(data);}) });

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
				alert("Debe seleccionar un Departamento al cual agregar la familia");
			}else{
				familia=prompt("Introduza el nombre de la familia a agregar al DEPARTAMENTO seleccionado");
				if(familia==null){
				}else{			
					$.ajax({
					 type: "POST",
					 processData:false,
						url: "'.$link11.'",
						data: "valor="+familia+"&&valor2="+deptoval,
						success: function(msg){
							if(msg=="Y.a-Existe"){
								alert("Ya existe una familia con esa Descripcion");
							}
							else{
								if(msg=="N.o-SeAgrego"){
									alert("Disculpe. En este momento no se ha podido agregar la familia, por favor intente mas tarde");
								}else{
									$.post("'.$link12.'",{ depto:deptoval },function(data){$("#familia").html(data);$("#familia").val(msg);})
								}
							}
						}
					});
				}
			}
		}

		function add_grupo(){
			lineaval=$("#familia").val();
			deptoval=$("#depto").val();
			if(lineaval==""){
				alert("Debe seleccionar una familia a la cual agregar el departamento");
			}else{
				grupo=prompt("Introduza el nombre del GRUPO a agregar a la familia seleccionada");
				if(grupo==null){
				}else{
					$.ajax({
					 type: "POST",
					 processData:false,
						url: "'.$link13.'",
						data: "valor="+grupo+"&&valor2="+lineaval+"&&valor3="+deptoval,
						success: function(msg){
							if(msg=="Y.a-Existe"){
								alert("Ya existe una familia con esa Descripcion");
							}
							else{
								if(msg=="N.o-SeAgrego"){
									alert("Disculpe. En este momento no se ha podido agregar la familia, por favor intente mas tarde");
								}else{
									$.post("'.$link14.'",{ familia:lineaval },function(data){$("#grupo").html(data);$("#grupo").val(msg);})
								}
							}
						}
					});
				}
			}
		}';

		$do = new DataObject("maes");
		if($status=="create" && !empty($id)){
			$do->load($id);
			$do->set('codigo', '');
		}

		$edit = new DataEdit2("Maestro de supermercado", $do);
		$edit->script($script,"create");
		$edit->script($script,"modify");
		$edit->back_url = site_url("supermercado/maes/filteredgrid");

		$ultimo='<a href="javascript:ultimo();" title="Consultar ultimo c&oacute;digo ingresado"> Consultar ultimo c&oacute;digo</a>';
		$sugerir='<a href="javascript:sugerir();" title="Sugerir un C&oacute;digo aleatorio">Sugerir C&oacute;digo </a>';
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size=20;
		$edit->codigo->maxlength=15;
		$edit->codigo->rule = "trim|required|strtoupper|callback_chexiste";
		$edit->codigo->mode="autohide";
		$edit->codigo->append($sugerir);
		$edit->codigo->append($ultimo);
		$edit->codigo->group = "Datos";
		
		$AddMarca='<a href="javascript:add_marca();" title="Haz clic para Agregar una marca nueva">Agregar Marca</a>';
		$edit->marca = new dropdownField("Marca", "marca");
		$edit->marca->style='width:180px;';
		$edit->marca->option("","");  
		$edit->marca->options("SELECT marca as codigo, marca FROM marc ORDER BY marca");
		$edit->marca->append($AddMarca);
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style='width:180px;';
		$edit->tipo->option("Articulo","Art&iacute;culo" );
		$edit->tipo->option("Servicio","Servicio");
		$edit->tipo->option("Descartar","Descartar");
		$edit->tipo->option("Consumo","Consumo");
		$edit->tipo->option("Fraccion","Fracci&oacute;n");
		$edit->tipo->option("Lote","Lote");
		$edit->tipo->group = "Datos";
				
		$AddDepto='<a href="javascript:add_depto();" title="Haz clic para Agregar un nuevo Departamento">Agregar Departamento</a>';
		$edit->depto = new dropdownField("Departamento", "depto");
		$edit->depto->rule ="required";
		//$edit->depto->onchange = "get_linea();";
		$edit->depto->option("","Seleccione un Departamento");
		$edit->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$edit->depto->append($AddDepto);
		$edit->depto->group = "Datos";

		$AddLinea='<a href="javascript:add_linea();" title="Haz clic para Agregar una nueva familia;">Agregar familia</a>';
		$edit->familia = new dropdownField("Familia","familia");
		$edit->familia->rule ="required";
		$edit->familia->append($AddLinea);
		$depto=$edit->getval('depto');
		if($depto!==FALSE){
			$edit->familia->options("SELECT familia, descrip FROM fami WHERE depto='$depto' ORDER BY descrip");
		}else{
			$edit->familia->option("","Seleccione un Departamento primero");
		}
		$edit->familia->group = "Datos";
		
		$AddGrupo='<a href="javascript:add_grupo();" title="Haz clic para Agregar un nuevo Grupo;">Agregar Grupo</a>';
		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->rule="required";
		$edit->grupo->append($AddGrupo);
		$familia=$edit->getval('familia');
		if($familia!==FALSE){
			$edit->grupo->options("SELECT grupo, nom_grup FROM grup WHERE familia='$familia' ORDER BY nom_grup");
		}else{
			$edit->grupo->option("","Seleccione un Departamento primero");
		}
		$edit->grupo->group = "Datos";			
		
		$edit->barras = new inputField("C&oacute;digo Barras", "barras");
		$edit->barras->size=20;
		$edit->barras->maxlength=15;
		$edit->barras->rule = "trim";
		$edit->barras->group = "Datos";
				
		$edit->referen = new inputField("S.N.M.", "referen");
		$edit->referen->size=17;
		$edit->referen->maxlength=15;
		$edit->referen->group = "Datos";
		
		$edit->barras = new inputField("Barras", "barras");
		$edit->barras->size=17;
		$edit->barras->maxlength=15;
		$edit->barras->group = "Datos";
		
		$edit->cu_inve = new inputField("Caja", "cu_inve");
		$edit->cu_inve->size=17;
		$edit->cu_inve->maxlength=15;
		$edit->cu_inve->group = "Datos";
		
		$edit->ensambla = new dropdownField("Ensamblado", "ensambla");
		$edit->ensambla->style='width:60px;';
		$edit->ensambla->option("N","No" );
		$edit->ensambla->option("S","Si" );
		$edit->ensambla->group = "Datos";
		
		$edit->empaque = new inputField("Des/Epq", "empaque");
		$edit->empaque->size=30;
		$edit->empaque->maxlength=27;
		$edit->empaque->group = "Datos";
		
		$edit->descrip = new inputField("Larga","descrip");
		$edit->descrip->size=48;
		$edit->descrip->maxlength=40;
		$edit->descrip->rule = "required";
		$edit->descrip->group = "Descripci&oacute;nes";
				
		$edit->corta = new inputField("Corta", "corta");
		$edit->corta->size=28;
		$edit->corta->maxlength=20;
		$edit->corta->group = "Descripci&oacute;nes";
		
		$edit->susti = new inputField("Clave", "susti");
		$edit->susti->size=15;
		$edit->susti->maxlength=10;
		$edit->susti->group = "Descripci&oacute;nes";
				
		$edit->serial = new dropdownField("Serializar", "serial");
		$edit->serial->style='width:60px;';
		$edit->serial->option("N","No" );
		$edit->serial->option("S","Si" );
		$edit->serial->when =array("show");
		$edit->serial->group = "Existencias";
				
		$edit->minimo = new inputField("Existencia Minima", "minimo");
		$edit->minimo->size=15;
		$edit->minimo->maxlength=11;
		$edit->minimo->when =array("show");
		$edit->minimo->group = "Existencias";
		$edit->minimo->rule='numeric|callback_positivo|trim';
		
		$edit->maximo = new inputField("Existencia Maxima", "maximo");
		$edit->maximo->size=15;
		$edit->maximo->maxlength=11;
		$edit->maximo->when =array("show");		
		$edit->maximo->group = "Existencias"; 
		$edit->maximo->rule='numeric|callback_positivo|trim';
		
		$edit->ordena = new inputField("Existencia Ordenada", "ordena");
		$edit->ordena->size=15;
		$edit->ordena->maxlength=11;
		$edit->ordena->when =array("show");	
		$edit->ordena->group = "Existencias";
		$edit->ordena->rule='numeric|callback_positivo|trim';	
		
		$edit->alcohol = new inputField("Licor G/I", "alcohol");
		$edit->alcohol->size=15;
		$edit->alcohol->maxlength=11;	
		$edit->alcohol->group = "Licores";	
		
		$edit->implic = new inputField("Impuesto por alcohol", "implic");
		$edit->implic->size=8;
		$edit->implic->maxlength=6;
		$edit->implic->group = "Licores";	
		
		$edit->tamano = new inputField("Tama&ntilde;o", "tamano");
		$edit->tamano->size=15;
		$edit->tamano->maxlength=11;
		$edit->tamano->when =array("show");
		$edit->tamano->group = "Licores";	
		
		$edit->medida = new inputField("Medida", "medida");
		$edit->medida->size=15;
		$edit->medida->maxlength=11;
		$edit->medida->when =array("show");
		$edit->medida->group = "Licores";		
		
		$edit->conjunto = new inputField("Conjunto de Articulo", "conjunto");
		$edit->conjunto->size=8;
		$edit->conjunto->maxlength=8;
		$edit->conjunto->group = "Licores";	
		
		$edit->ultimo = new inputField("Ultimo", "ultimo");
		$edit->ultimo->css_class='inputnum';
		$edit->ultimo->size=21;
		$edit->ultimo->maxlength=17;
		$edit->ultimo->group = "Costos";
		
		$edit->iva = new inputField("Iva", "iva");
		$edit->iva->css_class='inputnum';
		$edit->iva->onchange = "calculos('M');";
		$edit->iva->size=10;
		$edit->iva->maxlength=8;
		$edit->iva->group = "Costos";
	
		$edit->costo = new inputField("Promedio", "costo");
    $edit->costo->css_class='inputnum';
		$edit->costo->onchange = "calculos(costo);";
		$edit->costo->size=21;
		$edit->costo->maxlength=17;
		$edit->costo->group = "Costos";

		$edit->fcalc = new dropdownField("Base C&aacute;lculo", "fcalc");
		$edit->fcalc->style='width:150px;';
		$edit->fcalc->option("U","Ultimo" );
		$edit->fcalc->option("P","Promedio" );
		$edit->fcalc->onchange = "calculos('M');";
		$edit->fcalc->group = "Costos";
		
		$edit->redondeo = new dropdownField("Redondear", "redondeo");
		$edit->redondeo->style='width:150px;';
		$edit->redondeo->option("NO","No");
		$edit->redondeo->option("P0","Precio Decimales");
		$edit->redondeo->option("P1","Precio Unidades" );  
		$edit->redondeo->option("P2","Precio Decenas"  );
		$edit->redondeo->option("B0","Base Decimales"  );
		$edit->redondeo->option("B1","Base Unidades"   );
		$edit->redondeo->option("B2","Base Decenas"    );
    $edit->redondeo->onchange = "redonde('M');";
	  $edit->redondeo->group = "Costos";

		$edit->fracxuni = new inputField("Presenta", "fracxuni");
		$edit->fracxuni->size=5;
		$edit->fracxuni->maxlength=11;
		$edit->fracxuni->group = "Costos";
		$edit->fracxuni->rule='numeric|callback_positivo|trim';	
		
		$AddUnidad='<a href="javascript:add_unidad();" title="Haz clic para Agregar una unidad nueva">Agregar Unidad</a>';	
		$edit->dempaq = new dropdownField("Unidad", "dempaq");
		$edit->dempaq->style='width:110x;';
		$edit->dempaq->options("SELECT presenta label, presenta FROM mpre ORDER BY presenta");
		$edit->dempaq->in="fracxuni";
		$edit->dempaq->append($AddUnidad);
					
		$edit->mempaq = new dropdownField("Unidad", "mempaq");
		$edit->mempaq->style='width:110x;';
		$edit->mempaq->options("SELECT presenta label, presenta FROM mpre ORDER BY presenta");
		$edit->mempaq->in="fracxuni";
		$edit->mempaq->append($AddUnidad);
						
		for($i=1;$i<=5;$i++){
			$objeto="margen$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=6;
			$edit->$objeto->onchange = "calculos('I');";
			$edit->$objeto->rule="required";
			$edit->$objeto->group = "Precios";
			

			$objeto="Ebase$i";
			$edit->$objeto = new freeField("","","Precio $i");
			$edit->$objeto->in="margen$i";

			$objeto="base$i";
			$edit->$objeto = new inputField("Base $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onchange = "cambiobase('I');";
			$edit->$objeto->rule="required";

			$objeto="Eprecio$i";
			$edit->$objeto = new freeField("","","Precio + I.V.A. $i");
			$edit->$objeto->in="margen$i";

			$objeto="precio$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=6;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onchange = "cambioprecio('I');";
			$edit->$objeto->rule="required";
		}
		
		
		$codigo=$edit->_dataobject->get("codigo");
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array("show","modify");

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Maestro de supermercado</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function dataeditsan() {  
		$this->rapyd->load('dataedit'); 
		//rapydlib("prototype");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});	
		';
		
		$ajax_onchange = '
			  function get_familias(){
			    var url = "'.site_url('supermercado/maes/maesfamilias').'";
			    var pars = "dpto="+$F("depto");
			    var myAjax = new Ajax.Updater("td_familia", url, { method: "post", parameters: pars });
			    
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var gmyAjax = new Ajax.Updater("td_grupo", url);
			  }
			  
			  function get_grupo(){
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var pars = "dpto="+$F("depto")+"&fami="+$F("familia");
			    var myAjax = new Ajax.Updater("td_grupo", url, { method: "post", parameters: pars });
			  }';
		
		
		$edit = new DataEdit("Maestro de Supermercado", "maes");
		$edit->script($ajax_onchange);
		$edit->script($ajax_onchange,"modify");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->back_url = site_url("supermercado/maes/filteredgrid");
		
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size=20;
		$edit->codigo->maxlength=15;
		$edit->codigo->rule = "required";
		$edit->codigo->mode="autohide";
		$edit->codigo->group = "Datos";
		
		$edit->marca = new dropdownField("Marca", "marca");
		$edit->marca->style='width:110px;';
		$edit->marca->option("","");  
		$edit->marca->options("SELECT marca as codigo, marca FROM marc ORDER BY marca");  
		$edit->marca->group = "Datos";
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style='width:110px;';
		$edit->tipo->option("I","Inventario" );
		$edit->tipo->option("L","Licores"    );
		$edit->tipo->option("P","Por peso"   );
		$edit->tipo->option("K","Desposte"   );
		$edit->tipo->option("C","Combo"      );
		$edit->tipo->option("F","Farmaco"    );
		$edit->tipo->option("S","Servicio"   );
		$edit->tipo->option("R","Receta"     );
		$edit->tipo->option("D","Desactivado");
		$edit->tipo->group = "Datos";
		
		$edit->dpto = new dropdownField("Departamento", "depto");
		$edit->dpto->option("","");
		$edit->dpto->options("SELECT depto,descrip FROM dpto WHERE tipo='I' ORDER BY descrip");
		$edit->dpto->onchange = "get_familias();";
		$edit->dpto->group = "Datos";

		$edit->familia = new dropdownField("Familia", "familia");
		$edit->familia->onchange = "get_grupo();";
		$edit->familia->group = "Datos";

		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->group = "Datos";
		
		$edit->referen = new inputField("S.N.M.", "referen");
		$edit->referen->size=17;
		$edit->referen->maxlength=15;
		$edit->referen->group = "Datos";
		
		$edit->barras = new inputField("Barras", "barras");
		$edit->barras->size=17;
		$edit->barras->maxlength=15;
		$edit->barras->group = "Datos";
		
		$edit->cu_inve = new inputField("Caja", "cu_inve");
		$edit->cu_inve->size=17;
		$edit->cu_inve->maxlength=15;
		$edit->cu_inve->group = "Datos";
		
		$edit->ensambla = new dropdownField("Ensamblado", "ensambla");
		$edit->ensambla->style='width:60px;';
		$edit->ensambla->option("N","No" );
		$edit->ensambla->option("S","Si" );
		$edit->ensambla->group = "Datos";
		
		$edit->empaque = new inputField("Des/Epq", "empaque");
		$edit->empaque->size=30;
		$edit->empaque->maxlength=27;
		$edit->empaque->group = "Datos";
		
		$edit->descrip = new inputField("Larga","descrip");
		$edit->descrip->size=48;
		$edit->descrip->maxlength=40;
		$edit->descrip->rule = "required";
		$edit->descrip->group = "Descripci&oacute;nes";
				
		$edit->corta = new inputField("Corta", "corta");
		$edit->corta->size=28;
		$edit->corta->maxlength=20;
		$edit->corta->group = "Descripci&oacute;nes";
		
		$edit->susti = new inputField("Clave", "susti");
		$edit->susti->size=15;
		$edit->susti->maxlength=10;
		$edit->susti->group = "Descripci&oacute;nes";
				
		$edit->serial = new dropdownField("Serializar", "serial");
		$edit->serial->style='width:60px;';
		$edit->serial->option("N","No" );
		$edit->serial->option("S","Si" );
		$edit->serial->when =array("show");
		$edit->serial->group = "Existencias";
				
		$edit->minimo = new inputField("Existencia Minima", "minimo");
		$edit->minimo->size=15;
		$edit->minimo->maxlength=11;
		$edit->minimo->when =array("show");
		$edit->minimo->group = "Existencias";
		
		$edit->maximo = new inputField("Existencia Maxima", "maximo");
		$edit->maximo->size=15;
		$edit->maximo->maxlength=11;
		$edit->maximo->when =array("show");		
		$edit->maximo->group = "Existencias"; 
		
		$edit->ordena = new inputField("Existencia Ordenada", "ordena");
		$edit->ordena->size=15;
		$edit->ordena->maxlength=11;
		$edit->ordena->when =array("show");	
		$edit->ordena->group = "Existencias";	
		
		$edit->alcohol = new inputField("Licor G/I", "alcohol");
		$edit->alcohol->size=15;
		$edit->alcohol->maxlength=11;	
		$edit->alcohol->group = "Licores";	
		
		$edit->implic = new inputField("Impuesto por alcohol", "implic");
		$edit->implic->size=8;
		$edit->implic->maxlength=6;
		$edit->implic->group = "Licores";	
		
		$edit->tamano = new inputField("Tama&ntilde;o", "tamano");
		$edit->tamano->size=15;
		$edit->tamano->maxlength=11;
		$edit->tamano->when =array("show");
		$edit->tamano->group = "Licores";	
		
		$edit->medida = new inputField("Medida", "medida");
		$edit->medida->size=15;
		$edit->medida->maxlength=11;
		$edit->medida->when =array("show");
		$edit->medida->group = "Licores";		
		
		$edit->conjunto = new inputField("Conjunto de Articulo", "conjunto");
		$edit->conjunto->size=8;
		$edit->conjunto->maxlength=8;
		$edit->conjunto->group = "Licores";	
		
		$edit->ultimo = new inputField("Ultimo", "ultimo");
		$edit->ultimo->css_class='inputnum';
		$edit->ultimo->size=21;
		$edit->ultimo->maxlength=17;
		$edit->ultimo->group = "Costos";
		
		$edit->iva = new inputField("Iva", "iva");
		$edit->iva->css_class='inputnum';
		$edit->iva->onchange = "calculos('M');";
		$edit->iva->size=10;
		$edit->iva->maxlength=8;
		$edit->iva->group = "Costos";
	
		$edit->costo = new inputField("Promedio", "costo");
    $edit->costo->css_class='inputnum';
		$edit->costo->onchange = "calculos(costo);";
		$edit->costo->size=21;
		$edit->costo->maxlength=17;
		$edit->costo->group = "Costos";

		$edit->fcalc = new dropdownField("Base C&aacute;lculo", "fcalc");
		$edit->fcalc->style='width:150px;';
		$edit->fcalc->option("U","Ultimo" );
		$edit->fcalc->option("P","Promedio" );
		$edit->fcalc->onchange = "calculos('M');";
		$edit->fcalc->group = "Costos";
		
		$edit->redondeo = new dropdownField("Redondear", "redondeo");
		$edit->redondeo->style='width:150px;';
		$edit->redondeo->option("NO","No");
		$edit->redondeo->option("P0","Precio Decimales");
		$edit->redondeo->option("P1","Precio Unidades" );  
		$edit->redondeo->option("P2","Precio Decenas"  );
		$edit->redondeo->option("B0","Base Decimales"  );
		$edit->redondeo->option("B1","Base Unidades"   );
		$edit->redondeo->option("B2","Base Decenas"    );
    $edit->redondeo->onchange = "redonde('M');";
	  $edit->redondeo->group = "Costos";

		$edit->fracxuni = new inputField("Presenta", "fracxuni");
		$edit->fracxuni->size=5;
		$edit->fracxuni->maxlength=11;
		$edit->fracxuni->group = "Costos";
		
		$edit->dempaq = new dropdownField("Unidad", "dempaq");
		$edit->dempaq->style='width:110x;';
		$edit->dempaq->options("SELECT presenta label, presenta FROM mpre ORDER BY presenta");
		$edit->dempaq->in="fracxuni";
					
		$edit->mempaq = new dropdownField("Unidad", "mempaq");
		$edit->mempaq->style='width:110x;';
		$edit->mempaq->options("SELECT presenta label, presenta FROM mpre ORDER BY presenta");
		$edit->mempaq->in="fracxuni";
						
		for($i=1;$i<=5;$i++){
			$objeto="margen$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=6;
			$edit->$objeto->onchange = "calculos('I');";
			$edit->$objeto->rule="required";
			$edit->$objeto->group = "Precios";
			

			$objeto="Ebase$i";
			$edit->$objeto = new freeField("","","Precio $i");
			$edit->$objeto->in="margen$i";

			$objeto="base$i";
			$edit->$objeto = new inputField("Base $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onchange = "cambiobase('I');";
			$edit->$objeto->rule="required";

			$objeto="Eprecio$i";
			$edit->$objeto = new freeField("","","Precio + I.V.A. $i");
			$edit->$objeto->in="margen$i";

			$objeto="precio$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=6;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onchange = "cambioprecio('I');";
			$edit->$objeto->rule="required";
		}
			
		$codigo=$edit->_dataobject->get("codigo");
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array("show","modify");
		$edit->almacenes->group = "Precios";
		
		
		if($this->rapyd->uri->is_set("modify") or $this->rapyd->uri->is_set("show")){
			$codigo =$edit->_dataobject->get("codigo");
			$depto  =$edit->_dataobject->get("depto");
			$familia=$edit->_dataobject->get("familia");
			
			$edit->familia->options("SELECT familia,descrip FROM fami WHERE depto = '$depto' ORDER BY descrip");
			//$edit->grupo->options("SELECT grupo, nom_grup FROM grup WHERE depto='$depto' AND familia='$familia'");
		}else{
			$edit->familia->option("","Seleccione un departamento");
			$edit->grupo->option("","Seleccione una familia");
		}
		//$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();
				
		//echo $edit->codigo->value;
		$data['content'] = $edit->output;
		//$data['content'] = $this->load->view('view_maes', $conten,true);
		//$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("tabber.js").script("prototype.js").script("sinvmaes.js").$this->rapyd->get_head();
		$data["head"]      = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Maestro de Supermercado</h1>';
		$this->load->view('view_ventanas', $data);
	}
	function dataedit() {  
		$this->rapyd->load('dataedit'); 
		//rapydlib("prototype");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});	
		';
		
		$ajax_onchange = '
			  function get_familias(){
			    var url = "'.site_url('supermercado/maes/maesfamilias').'";
			    var pars = "dpto="+$F("depto");
			    var myAjax = new Ajax.Updater("td_familia", url, { method: "post", parameters: pars });
			    
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var gmyAjax = new Ajax.Updater("td_grupo", url);
			  }
			  
			  function get_grupo(){
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var pars = "dpto="+$F("depto")+"&fami="+$F("familia");
			    var myAjax = new Ajax.Updater("td_grupo", url, { method: "post", parameters: pars });
			  }';
		
		
		$edit = new DataEdit("Maestro de Supermercado", "maes");
		$edit->script($ajax_onchange);
		$edit->script($ajax_onchange,"modify");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->back_url = site_url("supermercado/maes/filteredgrid");
		
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size=20;
		$edit->codigo->maxlength=15;
		$edit->codigo->rule = "required";
		$edit->codigo->mode="autohide";
		$edit->codigo->group = "Datos";
		
		$edit->marca = new dropdownField("Marca", "marca");
		$edit->marca->style='width:110px;';
		$edit->marca->option("","");  
		$edit->marca->options("SELECT marca as codigo, marca FROM marc ORDER BY marca");  
		$edit->marca->group = "Datos";
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style='width:110px;';
		$edit->tipo->option("I","Inventario" );
		$edit->tipo->option("L","Licores"    );
		$edit->tipo->option("P","Por peso"   );
		$edit->tipo->option("K","Desposte"   );
		$edit->tipo->option("C","Combo"      );
		$edit->tipo->option("F","Farmaco"    );
		$edit->tipo->option("S","Servicio"   );
		$edit->tipo->option("R","Receta"     );
		$edit->tipo->option("D","Desactivado");
		$edit->tipo->group = "Datos";
		
		$edit->dpto = new dropdownField("Departamento", "depto");
		$edit->dpto->option("","");
		$edit->dpto->options("SELECT depto,descrip FROM dpto WHERE tipo='I' ORDER BY descrip");
		$edit->dpto->onchange = "get_familias();";
		$edit->dpto->group = "Datos";

		$edit->familia = new dropdownField("Familia", "familia");
		$edit->familia->onchange = "get_grupo();";
		$edit->familia->group = "Datos";

		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->group = "Datos";
		
		$edit->referen = new inputField("S.N.M.", "referen");
		$edit->referen->size=17;
		$edit->referen->maxlength=15;
		$edit->referen->group = "Datos";
		
		$edit->barras = new inputField("Barras", "barras");
		$edit->barras->size=17;
		$edit->barras->maxlength=15;
		$edit->barras->group = "Datos";
		
		$edit->cu_inve = new inputField("Caja", "cu_inve");
		$edit->cu_inve->size=17;
		$edit->cu_inve->maxlength=15;
		$edit->cu_inve->group = "Datos";
		
		$edit->ensambla = new dropdownField("Ensamblado", "ensambla");
		$edit->ensambla->style='width:60px;';
		$edit->ensambla->option("N","No" );
		$edit->ensambla->option("S","Si" );
		$edit->ensambla->group = "Datos";
		
		$edit->empaque = new inputField("Des/Epq", "empaque");
		$edit->empaque->size=30;
		$edit->empaque->maxlength=27;
		$edit->empaque->group = "Datos";
		
		$edit->descrip = new inputField("Larga","descrip");
		$edit->descrip->size=48;
		$edit->descrip->maxlength=40;
		$edit->descrip->rule = "required";
		$edit->descrip->group = "Descripci&oacute;nes";
				
		$edit->corta = new inputField("Corta", "corta");
		$edit->corta->size=28;
		$edit->corta->maxlength=20;
		$edit->corta->group = "Descripci&oacute;nes";
		
		$edit->susti = new inputField("Clave", "susti");
		$edit->susti->size=15;
		$edit->susti->maxlength=10;
		$edit->susti->group = "Descripci&oacute;nes";
				
		$edit->serial = new dropdownField("Serializar", "serial");
		$edit->serial->style='width:60px;';
		$edit->serial->option("N","No" );
		$edit->serial->option("S","Si" );
		$edit->serial->when =array("show");
		$edit->serial->group = "Existencias";
				
		$edit->minimo = new inputField("Existencia Minima", "minimo");
		$edit->minimo->size=15;
		$edit->minimo->maxlength=11;
		$edit->minimo->when =array("show");
		$edit->minimo->group = "Existencias";
		
		$edit->maximo = new inputField("Existencia Maxima", "maximo");
		$edit->maximo->size=15;
		$edit->maximo->maxlength=11;
		$edit->maximo->when =array("show");		
		$edit->maximo->group = "Existencias"; 
		
		$edit->ordena = new inputField("Existencia Ordenada", "ordena");
		$edit->ordena->size=15;
		$edit->ordena->maxlength=11;
		$edit->ordena->when =array("show");	
		$edit->ordena->group = "Existencias";	
		
		$edit->alcohol = new inputField("Licor G/I", "alcohol");
		$edit->alcohol->size=15;
		$edit->alcohol->maxlength=11;	
		$edit->alcohol->group = "Licores";	
		
		$edit->implic = new inputField("Impuesto por alcohol", "implic");
		$edit->implic->size=8;
		$edit->implic->maxlength=6;
		$edit->implic->group = "Licores";	
		
		$edit->tamano = new inputField("Tama&ntilde;o", "tamano");
		$edit->tamano->size=15;
		$edit->tamano->maxlength=11;
		$edit->tamano->when =array("show");
		$edit->tamano->group = "Licores";	
		
		$edit->medida = new inputField("Medida", "medida");
		$edit->medida->size=15;
		$edit->medida->maxlength=11;
		$edit->medida->when =array("show");
		$edit->medida->group = "Licores";		
		
		$edit->conjunto = new inputField("Conjunto de Articulo", "conjunto");
		$edit->conjunto->size=8;
		$edit->conjunto->maxlength=8;
		$edit->conjunto->group = "Licores";	
		
		$edit->ultimo = new inputField("Ultimo", "ultimo");
		$edit->ultimo->css_class='inputnum';
		$edit->ultimo->size=21;
		$edit->ultimo->maxlength=17;
		$edit->ultimo->group = "Costos";
		
		$edit->iva = new inputField("Iva", "iva");
		$edit->iva->css_class='inputnum';
		$edit->iva->onchange = "calculos('M');";
		$edit->iva->size=10;
		$edit->iva->maxlength=8;
		$edit->iva->group = "Costos";
	
		$edit->costo = new inputField("Promedio", "costo");
    $edit->costo->css_class='inputnum';
		$edit->costo->onchange = "calculos(costo);";
		$edit->costo->size=21;
		$edit->costo->maxlength=17;
		$edit->costo->group = "Costos";

		$edit->fcalc = new dropdownField("Base C&aacute;lculo", "fcalc");
		$edit->fcalc->style='width:150px;';
		$edit->fcalc->option("U","Ultimo" );
		$edit->fcalc->option("P","Promedio" );
		$edit->fcalc->onchange = "calculos('M');";
		$edit->fcalc->group = "Costos";
		
		$edit->redondeo = new dropdownField("Redondear", "redondeo");
		$edit->redondeo->style='width:150px;';
		$edit->redondeo->option("NO","No");
		$edit->redondeo->option("P0","Precio Decimales");
		$edit->redondeo->option("P1","Precio Unidades" );  
		$edit->redondeo->option("P2","Precio Decenas"  );
		$edit->redondeo->option("B0","Base Decimales"  );
		$edit->redondeo->option("B1","Base Unidades"   );
		$edit->redondeo->option("B2","Base Decenas"    );
    $edit->redondeo->onchange = "redonde('M');";
	  $edit->redondeo->group = "Costos";

		$edit->fracxuni = new inputField("Presenta", "fracxuni");
		$edit->fracxuni->size=5;
		$edit->fracxuni->maxlength=11;
		$edit->fracxuni->group = "Costos";
		
		$edit->dempaq = new dropdownField("Unidad", "dempaq");
		$edit->dempaq->style='width:110x;';
		$edit->dempaq->options("SELECT presenta label, presenta FROM mpre ORDER BY presenta");
		$edit->dempaq->in="fracxuni";
					
		$edit->mempaq = new dropdownField("Unidad", "mempaq");
		$edit->mempaq->style='width:110x;';
		$edit->mempaq->options("SELECT presenta label, presenta FROM mpre ORDER BY presenta");
		$edit->mempaq->in="fracxuni";
						
		for($i=1;$i<=5;$i++){
			$objeto="margen$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=6;
			$edit->$objeto->onchange = "calculos('I');";
			$edit->$objeto->rule="required";
			$edit->$objeto->group = "Precios";
			

			$objeto="Ebase$i";
			$edit->$objeto = new freeField("","","Precio $i");
			$edit->$objeto->in="margen$i";

			$objeto="base$i";
			$edit->$objeto = new inputField("Base $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=13;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onchange = "cambiobase('I');";
			$edit->$objeto->rule="required";

			$objeto="Eprecio$i";
			$edit->$objeto = new freeField("","","Precio + I.V.A. $i");
			$edit->$objeto->in="margen$i";

			$objeto="precio$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=10;
			$edit->$objeto->maxlength=6;
			$edit->$objeto->in="margen$i";
			$edit->$objeto->onchange = "cambioprecio('I');";
			$edit->$objeto->rule="required";
		}
			
		$codigo=$edit->_dataobject->get("codigo");
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array("show","modify");
		$edit->almacenes->group = "Precios";
		
		
		if($this->rapyd->uri->is_set("modify") or $this->rapyd->uri->is_set("show")){
			$codigo =$edit->_dataobject->get("codigo");
			$depto  =$edit->_dataobject->get("depto");
			$familia=$edit->_dataobject->get("familia");
			
			$edit->familia->options("SELECT familia,descrip FROM fami WHERE depto = '$depto' ORDER BY descrip");
			//$edit->grupo->options("SELECT grupo, nom_grup FROM grup WHERE depto='$depto' AND familia='$familia'");
		}else{
			$edit->familia->option("","Seleccione un departamento");
			$edit->grupo->option("","Seleccione una familia");
		}
		//$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();
				
		//echo $edit->codigo->value;
		$data['content'] = $edit->output;
		//$data['content'] = $this->load->view('view_maes', $conten,true);
		//$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("tabber.js").script("prototype.js").script("sinvmaes.js").$this->rapyd->get_head();
		$data["head"]      = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Maestro de Supermercado</h1>';
		$this->load->view('view_ventanas', $data);
	}  
	
	function _detalle($codigo){
  	$salida='hola';
  	if(!empty($codigo)){
  		$this->rapyd->load('dataedit','datagrid'); 
			
			$grid = new DataGrid('Cantidad por almac&eacute;n');
			
			$grid->db->select=array("ubica","locali","cantidad","fraccion");
			$grid->db->from('ubic');
			$grid->db->where('codigo',$codigo);
			
			$grid->column("Almacen"          ,"ubica" );
			$grid->column("Ubicaci&oacute;n" ,"locali");
			$grid->column("Cantidad"         ,"cantidad",'align="RIGHT"');
			$grid->column("Fracci&oacute;n"  ,"fraccion",'align="RIGHT"');
			
			$grid->build();
			$salida=$grid->output;
		}
		return $salida;
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
		$ultimo=$this->datasis->dameval("SELECT codigo FROM maes ORDER BY codigo DESC LIMIT 1");
		echo $ultimo;
	}

	function sugerir(){
		$ultimo=$this->datasis->dameval("SELECT LPAD(hexa,4,0) FROM serie LEFT JOIN maes ON LPAD(codigo,4,0)=LPAD(hexa,4,0) WHERE valor<65535 AND codigo IS NULL LIMIT 1");
		echo $ultimo;
	}

	function chexiste($codigo){
		//$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM maes WHERE codigo='$codigo'");
		if ($chek > 0){
			$descrip=$this->datasis->dameval("SELECT descrip FROM maes WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el producto $descrip");
			return FALSE;
		}else {
		 return TRUE;
		}
	}

	function chexiste2($alterno){
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM maes WHERE alterno='$alterno'");
		if ($chek > 0){
			$descrip=$this->datasis->dameval("SELECT descrip FROM maes WHERE alterno='$alterno'");
			$this->validation->set_message('chexiste',"El codigo alterno $alterno ya existe para el producto $descrip");
			return FALSE;
		}else {
			return TRUE;
		}
	}
	function maesfamilias(){  
		$this->rapyd->load("fields");
		$where = "";
		$sql = "SELECT familia,descrip FROM fami ";
		$linea = new dropdownField("Familia", "familia");
		$dpto=$this->input->post('dpto');
		
		if ($dpto){
		  $where = "WHERE depto = ".$this->db->escape($dpto);
		  $sql = "SELECT familia,descrip FROM fami $where ORDER BY descrip";
		  $linea->option("","");
			$linea->options($sql);
		}else{
			 $linea->option("","Seleccione Un Departamento");
		} 
		$linea->status   = "modify";
		$linea->onchange = "get_grupo();";
		$linea->build();
		echo $linea->output;
	}
	
	function maesgrupos(){
		$this->rapyd->load("fields");  
		$where = "";  
		$fami=$this->input->post('fami');
		$dpto=$this->input->post('dpto'); 
		
		$grupo = new dropdownField("Grupo", "grupo");
		if ($fami AND $dpto AND !(empty($fami) OR empty($dpto))) {
			$where .= "WHERE depto = ".$this->db->escape($dpto);
			$where .= "AND familia = ".$this->db->escape($fami);
			$sql = "SELECT grupo, nom_grup FROM grup $where";
			$grupo->option("","");
			$grupo->options($sql);
		}else{
			$grupo->option("","Seleccione una familia"); 
		} 
		$grupo->status = "modify";  
		$grupo->build();
		echo $grupo->output; 
	}
	function instalar(){
		$mSQL='ALTER TABLE `maes` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `maes` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE maes ADD id INT AUTO_INCREMENT PRIMARY KEY';
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