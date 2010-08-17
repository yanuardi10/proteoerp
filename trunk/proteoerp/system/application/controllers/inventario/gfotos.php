<?php

class gfotos extends Controller {

	function gfotos(){
		parent::Controller();
		$this->load->library("rapyd");
	}
	function index(){
		redirect("inventario/gfotos/filteredgrid");
	}


	function filteredgrid($para=''){

		$this->rapyd->load("datafilter2","datagrid","dataobject","fields");

		$user  = $this->session->userdata('usuario');
		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');
		$link4=site_url('inventario/gfotos/tabla');
		$link5=site_url('inventario/gfotos/');


		$script='
		function atras(){
			window.location="'.$link5.'/";
		}
		
		$(document).ready(function(){
				
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

		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->db_name=("a.tipo");
		$filter->tipo->option("","Todos");
		$filter->tipo->option("Articulo","Art&iacute;culo");
		$filter->tipo->option("Servicio","Servicio");
		$filter->tipo->option("Descartar","Descartar");
		$filter->tipo->option("Consumo","Consumo");
		$filter->tipo->option("Fraccion","Fracci&oacute;n");
		$filter->tipo ->style='width:220px;';


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


		$filter->buttons("reset","search");
		$filter->build();

		$tabla="";

		if($this->rapyd->uri->is_set("search")  AND $filter->is_valid()){
			$tabla=form_open("forma/ver/catalogo");
			
			$grid = new DataGrid("Lista de Art&iacute;culos Para el Catalogo");
//				$grid->db->select("a.tipo AS tipo,a.id as id,a.codigo as codigo,a.descrip,precio1,
//									precio2,precio3,precio4,a.prov1,
//									b.nom_grup AS nom_grup,b.grupo AS grupoid,
//									c.descrip AS nom_linea,c.linea AS linea,
//									d.descrip AS nom_depto,d.depto AS depto,a.prov1,a.prov2,a.prov3,
//									e.sinv_id as sinv_id");
//				$grid->db->from("sinv AS a");
//				$grid->db->join("grup AS b","a.grupo=b.grupo");
//				$grid->db->join("line AS c","b.linea=c.linea");
//				$grid->db->join("dpto AS d","c.depto=d.depto");
//				$grid->db->join("sinvfot AS e","e.sinv_id=a.id");
			$grid->db->select("COUNT(*) AS num,GROUP_CONCAT(e.codigo) as articulos,
			e.sinv_id as sinv_id,e.nombre AS nombre,e.ruta AS ruta,
			e.comentario AS comentario,e.principal AS principal,
			a.tipo AS tipo,a.id as id,a.codigo as codigo,a.descrip,
			b.nom_grup AS nom_grup, b.grupo AS grupoid,
			c.descrip AS nom_linea, c.linea AS linea,
			d.descrip AS nom_depto, d.depto AS depto");

			$grid->db->from("sinv AS a");
			$grid->db->join("grup AS b","a.grupo=b.grupo");
			$grid->db->join("line AS c","b.linea=c.linea");
			$grid->db->join("dpto AS d","c.depto=d.depto");
			$grid->db->join("sinvfot AS e","e.sinv_id=a.id");
			$grid->db->group_by("e.nombre");
			$grid->db->_escape_char='';
			$grid->db->_protect_identifiers=false;
			$grid->order_by("codigo","asc");
				
			$grid->use_function('asigna');
			$grid->column_orderby("c&oacute;digo"     ,"codigo","codigo");
			$grid->column_orderby("Departamento"      ,"<#nom_depto#>","nom_depto",'align=left');
			$grid->column_orderby("L&iacute;nea"      ,"<#nom_linea#>","nom_linea",'align=left');
			$grid->column_orderby("Grupo"             ,"<#nom_grup#>" ,"nom_grup" ,'align=left');
			$grid->column_orderby("Descripci&oacute;n","descrip","descrip");
//			$grid->column("Nombre","nombre");
			$grid->build();
	
			$consul=$this->db->last_query();
			$options = array(
                  'D'  => 'DESCARGAR',
                  'I'    => 'VER',
                );

			$shirts_on_sale = array('small', 'large');

			
			
			$tabla.=form_hidden('consul', $consul);
			$tabla.=$grid->output.form_dropdown('opcion', $options, 'D').form_submit('mysubmit', 'Generar');
			$tabla.=form_close();
			
		}


		$back="<table width='100%'border='0'><tr><td width='80%'></td><td width='20%'><a href='javascript:atras()'><spam id='regresar'align='right'>REGRESAR</spam></a></td></tr></table>";
		$data['filtro']=$filter->output;
		$data['tabla']=$tabla;
		$data['smenu'] = $back;//.$grid->output;
		$data['title']   = "<big>Genera Catalogo</big>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes2.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas_pru', $data);
	}
	
	
	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `itsinvlist` (
		`id` INT(8) NOT NULL AUTO_INCREMENT,
		`numero` INT(8) NULL DEFAULT NULL,
		`codigo` CHAR(15) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',PRIMARY KEY (`id`))
		COLLATE='utf8_unicode_ci'
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";


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

?>