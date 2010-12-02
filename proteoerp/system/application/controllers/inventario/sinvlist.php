<?php

class sinvlist extends Controller {

	function sinvlist(){
		parent::Controller();
		$this->load->library("rapyd");
	}
	function index(){
		redirect("inventario/sinvlist/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");

		$filter = new DataFilter2("","sinvlist");

		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size=15;
		//$filter->numero->clause="likerigth";
		
		$filter->nombre = new inputField("Nombre","nombre");
		$filter->nombre->size = 25;
		
		$filter->fecha = new dateonlyField("Fecha", "fecha");
		$filter->fecha-> dbformat = "Y-m-d";
		$filter->fecha->size=12;

		$filter->concepto = new textareaField("Concepto", "concepto");
		$filter->concepto->rows=2;
		$filter->concepto->cols=20;

		$filter->usu = new inputField("Usuario", "usuario");
		$filter->usu->size=10;

		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = anchor('inventario/sinvlist/dataedit2/show/<#numero#>','<#numero#>');
		$importar = anchor('inventario/sinvlist/agregar/<#numero#>','Importar');
		$uri2=anchor("inventario/sinvlist/agregar","Agregar por Patron");
		
		$grid = new DataGrid("");
		$grid->order_by("numero","desc");
		$grid->per_page = 5;

		$grid->column_orderby("N&uacute;mero"  ,$uri      ,"numero"     );
		$grid->column_orderby("Nombre","nombre","nombre");
		$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>","fecha");
		$grid->column_orderby("Concepto"       ,"concepto","concepto"       );
		$grid->column_orderby("Usuario"        ,"usuario" ,"usuario"   );
		$grid->column_orderby("Importar"        ,$importar ,"numero"   );
		$grid->add("inventario/sinvlist/dataedit2/create");
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['tabla']='';
		$data['script'] = script("jquery.js")."\n";
		$data['title']   = "SinvList";
		$data["head"]    = script("jquery.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas_pru', $data);
	}

	function dataedit2(){
		$this->rapyd->load('dataobject','datadetails');
		
		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array('codigo'      =>'C&oacute;digo',
							  'grupo'     =>'Grupo',
							  'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'itdescrip_<#i#>'),//,'denominacion'=>'denomi_<#i#>'
			'p_uri'=>array(4=>'<#i#>'),
			'titulo'  =>'Busqueda de Productos');
		
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');			
		
		$user  = $this->session->userdata('usuario');
		
		$do = new DataObject("sinvlist");
		$do->rel_one_to_many('itsinvlist', 'itsinvlist', array('numero'=>'numero'));
		$do->rel_pointer('itsinvlist','sinv' ,'itsinvlist.codigo=sinv.codigo',"sinv.descrip AS itdescrip");

		$edit = new DataDetails("Lista", $do);
		$edit->back_url = site_url("inventario/sinvlist/filteredgrid");
		$edit->pre_process('update' ,'_pre_process');
		$edit->pre_process('delete' ,'_pre_process');
		$edit->set_rel_title('itsinvlis','Rubro <#o#>');

		$edit->pre_process('update'  ,'_valida');
		$edit->pre_process('insert'  ,'_valida');

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->when=array('show');
		
		$edit->fecha = new  dateonlyField("Fecha",  "fecha");
		$edit->fecha-> dbformat = "Y-m-d";
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size =12;

		$edit->usu = new inputField("Usuario", "usuario");
		$edit->usu->size =12;
		$edit->usu->insertValue = $user;
		$edit->usu->mode="readonly";

		$edit->concepto = new textareaField("Concepto", "concepto");
		$edit->concepto->cols=40;
		$edit->concepto->rows=4;

		$edit->nombre = new inputField("Nombre","nombre");
		$edit->nombre->size = 25;
		
		$edit->itcodigo = new inputField("(<#o#>)Codigo: ", "codigo_<#i#>");
		$edit->itcodigo->db_name  ='codigo';
		$edit->itcodigo->maxlength=15;
		$edit->itcodigo->size     =20;
		$edit->itcodigo->rule='callback_repetido|required';
		$edit->itcodigo->append($btn);
		//$edit->itcodigo->mode     =readonly;
		$edit->itcodigo->rel_id   ='itsinvlist';

		$edit->itdescrip = new inputField("(<#o#>)Descricion: ", "itdescrip_<#i#>");
		$edit->itdescrip->db_name  ='itdescrip';
		$edit->itdescrip->maxlength=45;
		$edit->itdescrip->size     =45;
		$edit->itdescrip->rel_id   ='itsinvlist';
		$edit->itdescrip->pointer  = true;
		//$edit->itdescrip->mode='autohide';

		$edit->buttons("modify","delete","save");
		$edit->buttons("undo" , "back","add_rel");
		$edit->build();
			
		//$data['estado']   = $edit->_status;;
		$conten["form"]  = & $edit ;
		$data['content'] = $this->load->view('view_sinvlist', $conten,true);//.$acti->output;
		$data['title']   = "Agregar Lista";
		$data["head"]    = $this->rapyd->get_head().script('jquery.js').script("plugins/jquery.numeric.pack.js");
		$this->load->view('view_ventanas', $data);
		
	}
	
	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		
		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'      =>'C&oacute;digo',
				'grupo'     =>'Grupo',
				'descrip'=>'Descripci&oacute;n'
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'itdescrip_<#i#>'),//,'denominacion'=>'denomi_<#i#>'
			'p_uri'=>array(4=>'<#i#>'),
				// AND saldo > 0 AND movimiento = "S"
			'titulo'  =>'Busqueda de Productos');
				$btn=$this->datasis->p_modbus($modbus,'<#i#>');
					
				$user  = $this->session->userdata('usuario');
				$do = new DataObject("sinvlist");
				$do->rel_one_to_many('itsinvlist', 'itsinvlist', array('numero'=>'numero'));
				$do->rel_pointer('itsinvlist','sinv' ,'itsinvlist.codigo=sinv.codigo',"sinv.descrip AS itdescrip");

				$edit = new DataDetails("Lista", $do);
				$edit->back_url = site_url("inventario/sinvlist/filteredgrid");
				$edit->pre_process('update' ,'_pre_process');
				$edit->pre_process('delete' ,'_pre_process');
				$edit->set_rel_title('itsinvlis','Rubro <#o#>');

				$edit->pre_process('update'  ,'_valida');
				$edit->pre_process('insert'  ,'_valida');

				$edit->numero = new inputField("N&uacute;mero", "numero");
				$edit->numero->mode="autohide";
				$edit->numero->when=array('show');

				$edit->fecha = new  dateonlyField("Fecha",  "fecha");
				$edit->fecha-> dbformat = "Y-m-d";
				$edit->fecha->insertValue = date('Y-m-d');
				$edit->fecha->size =12;
				
				$edit->nombre = new inputField("Nombre","nombre");
				$edit->nombre->size = 25;
				
				$edit->usu = new inputField("Usuario", "usuario");
				$edit->usu->size =12;
				$edit->usu->insertValue = $user;

				$edit->concepto = new textareaField("Concepto", "concepto");
				$edit->concepto->cols=40;
				$edit->concepto->rows=4;
				//$edit->concepto->rule='required';

				$edit->itcodigo = new inputField("(<#o#>)Codigo: ", "codigo_<#i#>");
				$edit->itcodigo->db_name  ='codigo';
				$edit->itcodigo->maxlength=15;
				$edit->itcodigo->size     =20;
				$edit->itcodigo->rule='callback_repetido|required';
				$edit->itcodigo->append($btn);
				//$edit->itcodigo->mode     =readonly;
				$edit->itcodigo->rel_id   ='itsinvlist';

				$edit->itdescrip = new inputField("(<#o#>)Descricion: ", "itdescrip_<#i#>");
				$edit->itdescrip->db_name  ='itdescrip';
				$edit->itdescrip->maxlength=45;
				$edit->itdescrip->size     =45;
				$edit->itdescrip->rel_id   ='itsinvlist';
				$edit->itdescrip->pointer  = true;
				//$edit->itdescrip->mode='autohide';

				$edit->buttons("modify","delete","save");
				$edit->buttons("undo" , "back","add_rel");
				$edit->build();
			
//				$acti=new myiframeField('acti_repo', '/inventario/sinvlist/agregar',true,"300","auto","0");
//				$acti->status='show';
//				$acti->build();
				//$smenu['link']   =barra_menu('330');
				$data['estado']   = $edit->_status;;
				$conten["form"]  = & $edit ;
				$data['content'] = $this->load->view('view_sinvlist', $conten,true);//.$acti->output;
//				$data['filtra'] = $this->agregar();
				$data['title']   = "Listas";
				$data["head"]    = $this->rapyd->get_head().script('jquery.js').script("plugins/jquery.numeric.pack.js");
				$this->load->view('view_ventana_sinvlist', $data);
	}

	function agregar($para=""){
//		echo $para;
		$this->rapyd->load("datafilter2","datagrid","dataobject","fields");
		$mod=array(
				'tabla'   =>'sinvlist',
				'columnas'=>array(
				'numero' =>'C&oacute;odigo',
				'concepto'=>'Concepto',
				'usuario'=>'Usuario'),
				'filtro'  =>array('numero'=>'N&uacute;mero'),
				'retornar'=>array('numero'=>'nume'),
				'titulo'  =>'Buscar Proveedor');
			
		$modnume=$this->datasis->modbus($mod);

		$mSPRV=array(
				'tabla'   =>'sprv',
				'columnas'=>array(
				'proveed' =>'C&oacute;odigo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
				'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
				'retornar'=>array('proveed'=>'proveed'),
				'script'=>array('limpia2()'),
				'titulo'  =>'Buscar Proveedor');
			
		$bSPRV=$this->datasis->modbus($mSPRV);

		$mSPRV1=array(
				'tabla'   =>'snot',
				'columnas'=>array(
				'numero'=>'N&uacute;mero'),
				'filtro'  =>array('numero'=>'N&uacute;mero'),
				'retornar'=>array('numero'=>'objnumero'),
				'script'=>array('limpia()'),
				'titulo'  =>'Buscar N&uacute;mero');
			
		$bSPRV1=$this->datasis->modbus($mSPRV1);

		$mSPRV2=array(
				'tabla'   =>'spre',
				'columnas'=>array(
				'numero'=>'N&uacute;mero'),
				'filtro'  =>array('numero'=>'N&uacute;mero'),
				'retornar'=>array('numero'=>'objnumero'),
				'script'=>array('limpia()'),
				'titulo'  =>'Buscar N&uacute;mero');
			
		$bSPRV2=$this->datasis->modbus($mSPRV2);

		$mSPRV3=array(
				'tabla'   =>'scst',
				'columnas'=>array(
				'numero'=>'N&uacute;mero'),
				'filtro'  =>array('numero'=>'N&uacute;mero'),
				'retornar'=>array('numero'=>'objnumero'),
				'script'=>array('limpia()'),
				'titulo'  =>'Buscar N&uacute;mero');
			
		$bSPRV3=$this->datasis->modbus($mSPRV3);

		$mSPRV4=array(
				'tabla'   =>'snte',
				'columnas'=>array(
				'numero'=>'N&uacute;mero'),
				'filtro'  =>array('numero'=>'N&uacute;mero'),
				'retornar'=>array('numero'=>'objnumero'),
				'script'=>array('limpia()'),
				'titulo'  =>'Buscar N&uacute;mero');
			
		$bSPRV4=$this->datasis->modbus($mSPRV4);

		$mSPRV5=array(
				'tabla'   =>'sfac',
				'columnas'=>array(
				'numero'=>'N&uacute;mero','tipo_doc'=>'Tipo De Documento'),
				'filtro'  =>array('numero'=>'N&uacute;mero'),
				'retornar'=>array('numero'=>'objnumero'),
				'script'=>array('limpia()'),
				'titulo'  =>'Buscar N&uacute;mero');
			
		$bSPRV5=$this->datasis->modbus($mSPRV5);

		$user  = $this->session->userdata('usuario');
		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');
		$link4=site_url('inventario/sinvlist/tabla');
		$link5=site_url('inventario/sinvlist/');


		$script='
		function limpia(){
			$("#codigo").val("");
			$("#descrip").val("");
			$("#marca").val("");
			$("#proveed").val("");
			$("#tipo").val("");
			$("#clave").val("");
			$("#depto").val("");
			$("#linea").val("");
			$("#grupo").val("");
		}
		function limpia2(){
			$("#objnumero").val("");
		}
				
		function envia(){
			nume = $("#nume").val();
			tlist = $("#tlist").val();
			objnumero = $("#objnumero").val();
			msj="Nueva lista";
			if(nume != ""){
				msj="lista numero "+nume;
			}
			if(objnumero != ""){
				alert("Se creare lista de productos de la tabla "+tlist+" con codigo "+objnumero+" en "+msj);
				window.location="'.$link4.'/"+tlist+"/"+objnumero+"/"+nume;
			}else{
				alert("El campo Numero no contine valor");
			}
		}
		
		function atras(){
			window.location="'.$link5.'/";
		}
		
		$(document).ready(function(){
				$("#marca").change(function(){$("#objnumero").val("");});
				$("#clave").focus(function(){$("#objnumero").val("");});
				$("#codigo").focus(function(){$("#objnumero").val("");});
				$("#descrip").focus(function(){$("#objnumero").val("");});
				$("#tipo").change(function(){$("#objnumero").val("");});
				//$("#proveed").change(function(){$("#objnumero").val("");});
				if(!$("#proveed").val()){
					$("#objnumero").val("");
				}
				$("#tlist").change(function(){
				vallist=$("#tlist").val();
				//alert(vallist);
				switch(vallist){
					case "sitems":	$("#modo1").hide();$("#modo2").hide();
									$("#modo3").hide();$("#modo4").hide();
									$("#modo5").show();
									break
					case "itsnot":	$("#modo5").hide();$("#modo2").hide();
									$("#modo3").hide();$("#modo4").hide();
									$("#modo1").show();
									break
					case "itsnte":	$("#modo1").hide();$("#modo2").hide();
									$("#modo3").hide();$("#modo5").hide();
									$("#modo4").show();
									break
					case "itspre":	$("#modo1").hide();$("#modo5").hide();
									$("#modo3").hide();$("#modo4").hide();
									$("#modo2").show();
									break
					case "itscst":	$("#modo1").hide();$("#modo2").hide();
									$("#modo5").hide();$("#modo4").hide();
									$("#modo3").show();
									break
					default:
				}
				//$("#modo1").css("display", "none");
			});
			
			
			$("#depto").change(function(){
				$("#objnumero").val("");
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


		$filter = new DataFilter2("Filtro por Producto");
		$filter->db->_escape_char='';
		$filter->db->_protect_identifiers=false;

		$filter->script($script);

		$filter->nume = new inputField("Nro. Lista", "nume");
		$filter->nume -> size=25;
		$filter->nume -> db_name="";
		$filter->nume ->clause ="";
		if($para !=""  ) {
			if($para != "search" || $para != "reset"){
				$filter->nume->mode="readonly";
				$filter->nume->insertValue=$para;
			}else $filter->nume ->append($modnume);
		}else $filter->nume ->append($modnume);

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo -> size=25;
		//$filter->codigo->rule='callback_repetido|required';

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip -> size=25;

		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->db_name=("a.tipo");
		$filter->tipo->option("","Todos");
		$filter->tipo->option("Articulo","Art&iacute;culo");
		$filter->tipo->option("Servicio","Servicio");
		$filter->tipo->option("Descartar","Descartar");
		$filter->tipo->option("Consumo","Consumo");
		$filter->tipo->option("Fraccion","Fracci&oacute;n");
		$filter->tipo ->style='width:220px;';

		$filter->clave = new inputField("Clave", "clave");
		$filter->clave -> size=25;

		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		$filter->proveed->db_name='a.prov1';
		$filter->proveed -> size=25;

		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="c.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");

		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");

		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option("","Seleccione un Departamento primero");
		}

		$filter->marca = new dropdownField("Marca", "marca");
		$filter->marca->option("","");
		$filter->marca->options("SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca");
		$filter->marca -> style='width:220px;';

		$filter->tlist = new dropdownField("Extraer de:", "tlist");
		$filter->tlist->option("sitems","Facturas");
		$filter->tlist->option("itspre","Presupuesto");
		$filter->tlist->option("itsnot","N.Despacho");
		$filter->tlist->option("itsnte","N.Entrega");
		$filter->tlist->option("itscst","N.Compra");
		$filter->tlist-> style='width:220px;';
		$filter->tlist->db_name="";
		$filter->tlist->clause="";
		$filter->tlist->in="objnumero";

		$salida="<a href='javascript:envia()'>Crear Lista en Base a Tabla</a>";
		$filter->objnumero = new inputField("Numero","objnumero");
		$filter->objnumero -> size=10;
		$filter->objnumero -> db_name="";
		$filter->objnumero ->clause ="";
		$filter->objnumero ->append("<spam id='modo1'style='display:none;'>$bSPRV1</espam><spam id='modo2'style='display:none;>$bSPRV2</spam><spam id='modo3'style='display:none;>$bSPRV3</spam><spam id='modo4'style='display:none;>$bSPRV4</spam><spam id='modo5'>$bSPRV5</spam>");
		$filter->buttons("reset","search");
		$filter->build();

		$tabla="";

		if($this->rapyd->uri->is_set("search")  AND $filter->is_valid()){
			//echo "=>".$this->input->post("objnumero")."epale";
			function asigna($numero){

				$data = array(
			  'name'    => "sepago[]",
			  'id'      => $numero,
			  'value'   => $numero,
			  'checked' => TRUE,
				);
				return form_checkbox($data);
			}
			if($para==""|| $para =="search") $para=$this->input->post("nume");
			
			$tabla=form_open("inventario/sinvlist/inserta/$para");

			$ddata = array(
              'nume'  => $this->input->post("nume")
			);
			if(!$this->input->post("objnumero")!==false){
				//echo "aqui tambien";
				$grid = new DataGrid("Lista de Art&iacute;culos");
				$grid->db->select("a.tipo AS tipo,id,codigo,a.descrip,precio1,
									precio2,precio3,precio4,a.prov1,
									b.nom_grup AS nom_grup,b.grupo AS grupoid,
									c.descrip AS nom_linea,c.linea AS linea,
									d.descrip AS nom_depto,d.depto AS depto,a.prov1,a.prov2,a.prov3");
				$grid->db->from("sinv AS a");
				$grid->db->join("grup AS b","a.grupo=b.grupo");
				$grid->db->join("line AS c","b.linea=c.linea");
				$grid->db->join("dpto AS d","c.depto=d.depto");
				$grid->db->_escape_char='';
				$grid->db->_protect_identifiers=false;

				$grid->order_by("codigo","asc");
				$grid->per_page = 50;
				//$link=anchor('/inventario/sinvlist/dataedit/show/<#id#>','<#codigo#>');
				$uri_2 = anchor('inventario/sinv/dataedit/create/<#id#>','Duplicar');
				$grid->use_function('asigna');
				$grid->column("c&oacute;digo","codigo");
				$grid->column("Departamento","<#nom_depto#>"   ,'align=left');
				$grid->column("L&iacute;nea","<#nom_linea#>"   ,'align=left');
				$grid->column("Grupo","<#nom_grup#>",'align=left');
				$grid->column("Descripci&oacute;n","descrip");
				$grid->column("Accio&oacute;n"   ,"<asigna><#codigo#></asigna>"                ,"align='right'" );
				$grid->build();
				//echo $grid->db->last_query();
				$tabla.=$grid->output.form_submit('mysubmit', 'Guardar');
				$tabla.=form_close();
			}else{
				//echo "aqui";
				//$_POST['depto']='';
				$tabla1=$this->input->post("tlist");
				$cod=$this->input->post("objnumero");
				$num=$this->input->post("nume");
				$campo="";
				$campo1="codigo";
				$campo2="descrip";
				$cod2=$this->db->escape($cod);
				$cod3=str_pad($cod,8,'0',STR_PAD_LEFT);

				switch ($tabla1){
					case "sitems":	$campo="numa";$campo1="codigoa";$campo2='desca';
					break;
					case "itsnot":	$campo="numero";
					break;
					case "itsnte":	$campo="numero";$campo2="desca";
					break;
					case "itspre":	$campo="numero";$campo2="desca";
					break;
					case "itscst":	$campo="numero";
					break;
					default:		echo "Tabla no valida.........";
					break;
				}

				$grid2 = new DataGrid("Lista de Art&iacute;culos Por Tabla");
				$grid2->db->from("$tabla1");
				$grid2->db->select(array("$campo1","$campo2"));
				$grid2->db->where("$campo","$cod3");
				$grid2->per_page = 50;
				//$link=anchor('/inventario/sinvlist/dataedit/show/<#id#>','<#codigo#>');
					
				$grid2->use_function('asigna');
				$grid2->column("c&oacute;digo","$campo1");
				$grid2->column("Descripci&oacute;n","$campo2");
				$grid2->column("Accio&oacute;n"   ,"<asigna><#$campo1#></asigna>"                ,"align='right'" );
				$grid2->build();
				$grid2->db->last_query();

				$tabla.=$grid2->output.form_submit('mysubmit', 'Guardar');
				$tabla.=form_close();
			}
		}


		$back="<table width='100%'border='0'><tr><td width='80%'></td><td width='20%'><a href='javascript:atras()'><spam id='regresar'align='right'>REGRESAR</spam></a></td></tr></table>";
		$data['filtro']=$filter->output;
		$data['tabla']=$tabla;
		$data['smenu'] = $back;//.$grid->output;
		$data['title']   = "Agregar Listado Por Patron";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes2.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas_pru', $data);
	}
	
	function inserta($para=''){

		$data=$this->input->post('sepago');
		$user  = $this->session->userdata('usuario');

		$nume = $para;
		$repetido=0;
		if($nume!=""){
			//echo $nume."<br>";
			$enume=0;
			$sinvlist=$this->db->query("select count(*) from sinvlist where numero='$nume'");
			$result1=$sinvlist->result();
			foreach($result1[0] as $valor){
				$enume=$valor;
				//echo $valor."<br>";
			}
			if($enume==0){
				//echo "El Codigo de Lista ".$nume." no existe";
				$link5=site_url('inventario/sinvlist/agregar');
				$script='
				<script language="javascript">
				function atras(){
					//alert("llega");
					window.location="'.$link5.'/";
				}
				</script>';
				$back="<table width='100%'border='0'><tr><td width='80%'></td><td width='20%'><a href='javascript:atras()'><spam id='regresar'align='right'>REGRESAR</spam></a></td></tr></table>";
					
				//$this->db->query("INSERT INTO IGNORE sinvlist (concepto,usuario) values('Consulta','$user')");
			}else{
				foreach ($data as $val){
					//echo $val." veces:";
					$con=$this->db->query("select count(*) from itsinvlist where codigo='$val' AND numero='$nume'");
					//$this->db->query("INSERT IGNORE INTO itsinvlist (numero,codigo) values('$nume','$val->codigo') ");
					$re=$con->result();
					foreach($re[0] as $valor){
						$repetido=$valor;
						//echo $valor."<br>";
					}
					if($repetido ==0){
						$this->db->query("INSERT IGNORE INTO itsinvlist (numero,codigo) values('$nume','$val') ");
					}
				}
				redirect("inventario/sinvlist/dataedit2/modify/$nume");
			}

		}else{
			$crea=$this->db->query("INSERT IGNORE INTO sinvlist (concepto,usuario) VALUES('Filtro','$user')");
			$inser=$this->db->insert_id();
			//echo $inser;
			foreach ($data as $val){
				$this->db->query("INSERT IGNORE INTO itsinvlist (numero,codigo) values('$inser','$val') ");
			}
			redirect("inventario/sinvlist/dataedit2/modify/$inser");
		}

		$data['content'] = $back;//.$grid->output;
		$data['title']   = "<h1>El codigo de lista ".$nume." no existe</h1>";
		$data["head"]    = $script.script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes2.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}
	
	function tabla($tabla1="",$cod="",$num=""){
		$this->rapyd->load("datafilter2","datagrid","dataobject","fields");


		$campo="";
		$campo1="codigo";
		$campo2="descrip";
		$cod2=$this->db->escape($cod);
		$cod3=str_pad($cod,8,'0',STR_PAD_LEFT);

		switch ($tabla1){
			case "sitems":	$campo="numa";$campo1="codigoa";$campo2='desca';
			break;
			case "itsnot":	$campo="numero";
			break;
			case "itsnte":	$campo="numero";
			break;
			case "itspre":	$campo="numero";$campo2="desca";
			break;
			case "itscst":	$campo="numero";
			break;
			default:		echo "Tabla no valida.........";
			break;
		}

		function asigna($numero){

			$data = array(
			  'name'    => "sepago[]",
			  'id'      => $numero,
			  'value'   => $numero,
			  'checked' => TRUE,
			);
			return form_checkbox($data);
		}

		$para=$this->input->post("nume");
		$tabla=form_open("inventario/sinvlist/inserta/$para");

		$ddata = array(
              'nume'  => $this->input->post("nume")
		);

		$grid = new DataGrid("Lista de Art&iacute;culos");
		$grid->order_by("$campo1","asc");
		$grid->db->select("$campo1");
		$grid->db->from("$tabla1");
		$grid->db->where("$campo","$cod3");
		$grid->per_page = 50;
		//$link=anchor('/inventario/sinvlist/dataedit/show/<#id#>','<#codigo#>');
		$uri_2 = anchor('inventario/sinv/dataedit/create/<#id#>','Duplicar');
		$grid->use_function('asigna');
		$grid->column("c&oacute;digo","$campo1");
		//		$grid->column("Departamento","<#nom_depto#>"   ,'align=left');
		//		$grid->column("L&iacute;nea","<#nom_linea#>"   ,'align=left');
		//		$grid->column("Grupo","<#nom_grup#>",'align=left');
		//$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Accio&oacute;n"   ,"<asigna><#$campo1#></asigna>"                ,"align='right'" );
		$grid->build();

		$tabla.=$grid->output.form_submit('mysubmit', 'Guardar');
		$tabla.=form_close();
		//echo $tabla."->".$num."->".$cod."->".$campo."<br>";
		/*$mSQL="";
		$nume = $num;
		$cod2=$this->db->escape($cod);
		$cod3=str_pad($cod,8,'0',STR_PAD_LEFT);
		$resul = $this->db->query("SELECT $campo1 FROM $tabla WHERE $campo=$cod3 ");
		//echo "SELECT $campo1 FROM $tabla WHERE $campo='$cod3' <br>";
		$repetido=0;
		if($nume!=""){
		//echo $nume."<br>";
		$enume=0;
		$sinvlist=$this->db->query("select count(*) from sinvlist where numero='$nume'");
		$result1=$sinvlist->result();
		foreach($result1[0] as $valor){
		$enume=$valor;
		//echo $valor."<br>";
		}
		if($enume==0){
		echo "El Codigo de Lista ".$nume." no existe";
		//$this->db->query("INSERT INTO IGNORE sinvlist (concepto,usuario) values('Consulta','$user')");
		}else{
		//				echo "repetia<br>";
		//				print ("<pre>");
		//				print_r($resul);
		foreach ($resul->result() as $val){
		//echo $val->codigo ." veces:";
		$a = $val->$campo1;
		$con=$this->db->query("select count(*) from itsinvlist where codigo='$a' AND numero='$nume'");
		//$this->db->query("INSERT IGNORE INTO itsinvlist (numero,codigo) values('$nume','$val->codigo') ");
		$re=$con->result();
		foreach($re[0] as $valor){
		$repetido=$valor;
		//echo $valor."<br>";
		}
		if($repetido ==0){
		$this->db->query("INSERT IGNORE INTO itsinvlist (numero,codigo) values('$nume','$a') ");
		//echo "aqui->".$val->$campo1."<->";
		}

		}
		redirect("inventario/sinvlist/dataedit/show/$nume");
		}
		}else{
		$crea=$this->db->query("INSERT IGNORE INTO sinvlist (concepto,usuario) VALUES('Filtro','$user')");
		$inser=$this->db->insert_id();
		//echo $inser;
		foreach ($resul->result() as $val){
		$a=$val->$campo1;
		$this->db->query("INSERT IGNORE INTO itsinvlist (numero,codigo) values('$inser','$a') ");
		}
		redirect("inventario/sinvlist/dataedit/show/$inser");
		}*/
		//$back="<table width='100%'border='0'><tr><td width='80%'></td><td width='20%'><a href='javascript:atras()'><spam id='regresar'align='right'>REGRESAR</spam></a></td></tr></table>";
		$data['content'] = $tabla;//.$grid->output;
		$data['title']   = "<h1>Agregar Listado Por Patron</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes2.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _pre_process($do){
		$error='Documento ya procesado, no se puede modificar';
		$do->error_message_ar['pre_upd']=$error;
		$do->error_message_ar['pre_del']=$error;
		$status=$do->get('status');
		if($status!='P'){
			return false;
		}
	}



	function actualizar($id){
		$this->rapyd->load('dataobject');

		$do = new DataObject("audis");
		$do->rel_one_to_many('itaudis', 'itaudis', array('numero'=>'numero'));
		$do->load($id);

		$sta=$do->get('status');
		if($sta=='P'){
			$tipo     =$do->get('tipo');
			$campo    =($tipo=='AUMENTO') ? 'aumento':'disminucion';
			$factor   = 1;

			$error='';
			if($tipo!='AUMENTO'){
				for($i=0;$i < $do->count_rel('itaudis');$i++){
					$codigopres       = $do->get_rel('itaudis','codigopres' ,$i);
					$monto            = $do->get_rel('itaudis','monto'      ,$i);
					$ordinal          = $do->get_rel('itaudis','ordinal'    ,$i);
					$codigoadm        = $do->get_rel('itaudis','codigoadm'  ,$i);
					$fondo            = $do->get_rel('itaudis','fondo'      ,$i);

					$error.=$this->chequeapresup($codigoadm,$fondo,$codigopres,$ordinal,$monto,0,'round($monto,2) > round(($presupuesto-$comprometido),2)',"El Monto ($monto) es mayor al disponible para la partida ($codigoadm) ($fondo) ($codigopres)");
				}
				if(empty($error))
				$factor = -1;
			}

			if(empty($error)){
				for($i=0;$i < $do->count_rel('itaudis');$i++){
					$codigopres       = $do->get_rel('itaudis','codigopres' ,$i);
					$monto            = $do->get_rel('itaudis','monto'      ,$i);
					$ordinal          = $do->get_rel('itaudis','ordinal'    ,$i);
					$codigoadm        = $do->get_rel('itaudis','codigoadm'  ,$i);
					$fondo            = $do->get_rel('itaudis','fondo'      ,$i);

					$error.=$this->afectapresup($codigoadm,$fondo,$codigopres,$ordinal,$monto,0, (1*$factor) ,array($campo));
				}
				if(empty($error)){
					$do->set('status','C');
					$do->set('faudis',date('Ymd'));
					$do->save();
					$this->sp_presucalc($codigoadm);
				}
			}
		}else{
			$error.="<div class='alert'><p>No se puede realizar la operacion para este aumento � disminucion</p></div>";
		}

		if(empty($error)){
			logusu('audis',"actualizo $campo numero $id");
			redirect("presupuesto/audis/dataedit/show/$id");
		}else{
			logusu('audis',"actualizo $campo numero $id con error $error");
			$data['content'] = $error.anchor("presupuesto/audis/dataedit/show/$id",'Regresar');
			$data['title']   = " Aumentos y Disminuciones ";
			$data["head"]    = $this->rapyd->get_head();
			$this->load->view('view_ventanas', $data);
		}
	}

	function reversar($id){
		$this->rapyd->load('dataobject');

		$do = new DataObject("audis");
		$do->rel_one_to_many('itaudis', 'itaudis', array('numero'=>'numero'));
		$do->load($id);

		$sta=$do->get('status');
		if($sta=='C'){
			$tipo     =$do->get('tipo');
			$campo    =($tipo=='AUMENTO') ? 'aumento':'disminucion';
			$factor   = 1;

			$error='';
			if($tipo=='AUMENTO'){
				for($i=0;$i < $do->count_rel('itaudis');$i++){
					$codigopres       = $do->get_rel('itaudis','codigopres' ,$i);
					$monto            = $do->get_rel('itaudis','monto'      ,$i);
					$ordinal          = $do->get_rel('itaudis','ordinal'    ,$i);
					$codigoadm        = $do->get_rel('itaudis','codigoadm'  ,$i);
					$fondo            = $do->get_rel('itaudis','fondo'      ,$i);

					$error.=$this->chequeapresup($codigoadm,$fondo,$codigopres,$ordinal,$monto,0,'$monto > ($presupuesto-$comprometido)',"El Monto ($monto) es mayor al disponible para la partida ($codigoadm) ($fondo) ($codigopres)");
				}
				if(empty($error))
				$factor = -1;
			}

			if(empty($error)){
				for($i=0;$i < $do->count_rel('itaudis');$i++){
					$codigopres       = $do->get_rel('itaudis','codigopres' ,$i);
					$monto            = $do->get_rel('itaudis','monto'      ,$i);
					$ordinal          = $do->get_rel('itaudis','ordinal'    ,$i);
					$codigoadm        = $do->get_rel('itaudis','codigoadm'  ,$i);
					$fondo            = $do->get_rel('itaudis','fondo'      ,$i);

					$error.=$this->afectapresup($codigoadm,$fondo,$codigopres,$ordinal,$monto,0, (1*$factor) ,array($campo));
				}
				if(empty($error)){
					$do->set('status','A');
					$do->save();
					$this->sp_presucalc($codigoadm);
				}
			}
		}else{
			$error.="<div class='alert'><p>No se puede realizar la operacion para este aumento � disminucion</p></div>";
		}

		if(empty($error)){
			logusu('audis',"reverso $campo numero $id");
			redirect("presupuesto/audis/dataedit/show/$id");
		}else{
			logusu('audis',"reverso $campo numero $id con error $error");
			$data['content'] = $error.anchor("presupuesto/audis/dataedit/show/$id",'Regresar');
			$data['title']   = " Aumentos y Disminuciones ";
			$data["head"]    = $this->rapyd->get_head();
			$this->load->view('view_ventanas', $data);
		}
	}

	function _valida($do){

		$__rpartida = array();

		$error='';
		for($i=0;$i < $do->count_rel('itaudis');$i++){
			$ordinal          = '';
			$codigopres       = $do->get_rel('itaudis','codigopres' ,$i);
			$monto            = $do->get_rel('itaudis','monto'      ,$i);
			$ordinal          = $do->get_rel('itaudis','ordinal'    ,$i);
			$codigoadm        = $do->get_rel('itaudis','codigoadm'  ,$i);
			$fondo            = $do->get_rel('itaudis','fondo'      ,$i);

			if(empty($ordinal)){
				$cana=$this->datasis->dameval("SELECT COUNT(*) FROM presupuesto WHERE codigoadm='$codigoadm' AND codigopres='$codigopres' AND tipo='$fondo'");
				if($cana <= 0)
				$error.="La partida ($codigopres) ($fondo) ($codigoadm) No ha sido creada";
			}else{
				$cana=$this->datasis->dameval("SELECT COUNT(*) FROM presupuesto a JOIN ordinal b ON a.codigoadm=b.codigoadm AND a.tipo = b.fondo AND a.codigopres = b.codigopres
					WHERE  b.codigoadm='$codigoadm' AND b.fondo = '$fondo' AND b.codigopres = '$codigopres' AND b.ordinal = '$ordinal'");
				if($cana <= 0)
				$error.="La partida ($codigopres) ($fondo) ($codigoadm) ($ordinal) No ha sido creada";
			}

			if(in_array($codigopres.$fondo.$codigopres.$ordinal, $__rpartida)){
				$error.="La partida ($codigopres) ($fondo) ($codigoadm) ($ordinal) Esta repetida";
			}

			$this->__rpartida[]=$codigopres.$fondo.$codigopres.$ordinal;
		}

		if(!empty($error)){
			$do->error_message_ar['pre_ins']=$error;
			$do->error_message_ar['pre_upd']=$error;
			return false;
		}
	}

	function positivo($valor){
		if ($valor < 0){
			$this->validation->set_message('positivo',"El campo monto debe ser positivo");
			return FALSE;
		}
		return TRUE;
	}

	function chstatus($status){
		$this->validation->set_message('chstatus',"No lo puedes cambiar pq no quiero");
		return false;
	}

	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `itsinvlist` (
		`id` INT(8) NOT NULL AUTO_INCREMENT,
		`numero` INT(8) NULL DEFAULT NULL,
		`codigo` CHAR(15) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',PRIMARY KEY (`id`))
		COLLATE='utf8_unicode_ci'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";
//		$mSQL2="CREATE TABLE IF NOT EXISTS`sinvlist` (
//		`numero` INT(8) NOT NULL AUTO_INCREMENT,
//		`concepto` TEXT NULL COLLATE 'utf8_unicode_ci',
//		`usuario` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
//		PRIMARY KEY (`numero`)
//		)
//		COLLATE='utf8_unicode_ci'
//		ENGINE=MyISAM
//		ROW_FORMAT=DEFAULT
//		 ";
		$mSQL2="CREATE TABLE IF NOT EXISTS`sinvlist` (
		`numero` INT(8) NOT NULL AUTO_INCREMENT,
		`nombre` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
		`fecha` DATE NOT NULL,
		`concepto` TEXT NULL COLLATE 'utf8_unicode_ci',
		`usuario` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		PRIMARY KEY (`numero`)
		)
		COLLATE='utf8_unicode_ci'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT
		";
		$this->db->simple_query($mSQL);
		$this->db->simple_query($mSQL2);
	}
}
/**codigo de insercion
 * $mSQL="";
 if($this->rapyd->uri->is_set("search")  AND $filter->is_valid()){
 $nume = $this->input->post("nume");
 $mSQL=$this->rapyd->db->_compile_select();
 $resul = $this->db->query($mSQL);
 $repetido=0;
 if($nume!=""){
 echo $nume."<br>";
 $enume=0;
 $sinvlist=$this->db->query("select count(*) from sinvlist where numero='$nume'");
 $result1=$sinvlist->result();
 foreach($result1[0] as $valor){
 $enume=$valor;
 //echo $valor."<br>";
 }
 if($enume==0){
 echo "El Codigo de Lista ".$nume." no existe";
 //$this->db->query("INSERT INTO IGNORE sinvlist (concepto,usuario) values('Consulta','$user')");
 }else{
 foreach ($resul->result() as $val){
 //echo $val->codigo ." veces:";
 $con=$this->db->query("select count(*) from itsinvlist where codigo='$val->codigo' AND numero='$nume'");
 //$this->db->query("INSERT IGNORE INTO itsinvlist (numero,codigo) values('$nume','$val->codigo') ");
 $re=$con->result();
 foreach($re[0] as $valor){
 $repetido=$valor;
 //echo $valor."<br>";
 }
 if($repetido ==0){
 $this->db->query("INSERT IGNORE INTO itsinvlist (numero,codigo) values('$nume','$val->codigo') ");
 }
 }
 redirect("inventario/sinvlist/dataedit/show/$nume");
 }

 }else{
 $crea=$this->db->query("INSERT IGNORE INTO sinvlist (concepto,usuario) VALUES('Filtro','$user')");
 $inser=$this->db->insert_id();
 //echo $inser;
 foreach ($resul->result() as $val){
 $this->db->query("INSERT IGNORE INTO itsinvlist (numero,codigo) values('$inser','$val->codigo') ");
 }
 redirect("inventario/sinvlist/dataedit/show/$inser");
 }

 }
 */
?>