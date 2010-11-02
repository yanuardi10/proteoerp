<?php

class etiqueta_maes extends Controller {

	function etiqueta_maes(){
		parent::Controller();
		$this->load->library("rapyd");
	}
	function index(){
		redirect("supermercado/etiqueta_maes/filteredgrid");
	}


	function filteredgrid($para=''){

		$this->rapyd->load("datafilter2","datagrid","dataobject","fields");

		$user  = $this->session->userdata('usuario');
		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');
		
		$link5=site_url();


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

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip -> size=25;
		
		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");

		$filter->fami = new dropdownField("Familia","fami");
		$filter->fami->db_name="c.familia";
		$filter->fami->option("","Seleccione un Departamento primero");

		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->fami->options("SELECT familia, descrip FROM fami WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->fami->option("","Seleccione un Departamento primero");
		}

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");

		$fami=$filter->getval('fami');
		if($fami!==FALSE){
			$filter->grupo->options("SELECT grupo, nom_grup FROM grup WHERE familia='$fami' ORDER BY nom_grup");
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
			$tabla=form_open("forma/ver/etiqueta_m");
			
			$grid = new DataGrid("Lista de Art&iacute;culos Para Etiquetas");
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
			$grid->db->select("COUNT(*) AS num,a.tipo AS tipo,a.codigo as codigo,a.descrip,a.precio1 as precio,a.barras,
			b.nom_grup AS nom_grup, b.grupo AS grupoid,
			c.descrip AS nom_familia, c.familia AS familia,
			d.descrip AS nom_depto, d.depto AS depto");

			$grid->db->from("maes AS a");
			$grid->db->join("grup AS b","a.grupo=b.grupo");
			$grid->db->join("fami AS c","b.familia=c.familia");
			$grid->db->join("dpto AS d","c.depto=d.depto");
//			$grid->db->join("sinvfot AS e","e.sinv_id=a.id");
			$grid->db->group_by("a.codigo");
			$grid->db->_escape_char='';
			$grid->db->_protect_identifiers=false;
			$grid->order_by("codigo","asc");
				
			$grid->use_function('asigna');
			$grid->column_orderby("c&oacute;digo"     ,"codigo","codigo");
			$grid->column_orderby("Departamento"      ,"<#nom_depto#>","nom_depto",'align=left');
			$grid->column_orderby("Familia"      ,"<#nom_familia#>","nom_familia",'align=left');
			$grid->column_orderby("Grupo"             ,"<#nom_grup#>" ,"nom_grup" ,'align=left');
			$grid->column_orderby("Descripci&oacute;n","descrip","descrip");
//			$grid->column("Nombre","nombre");
			$grid->build();
	
			$consul=$this->db->last_query();
//			$options = array(
//                  'D'  => 'DESCARGAR',
//                  'I'    => 'VER',
//                );

			$tabla.=form_hidden('consul', $consul);
			
			$data = array(
              'name'        => 'cant',
              'id'          => 'cant',
              'value'       => '1',
              'maxlength'   => '5',
              'size'        => '5',
              
            );
			
			//$tabla.=$grid->output.form_dropdown('opcion', $options, 'D').form_submit('mysubmit', 'Generar');
			$tabla.=$grid->output.form_label("Numero de etiquetas por producto:")."&nbsp&nbsp&nbsp".form_input($data).form_submit('mysubmit', 'Generar');
			$tabla.=form_close();
			
		}


		$back="<table width='100%'border='0'><tr><td width='80%'></td><td width='20%'><a href='javascript:atras()'><spam id='regresar'align='right'>REGRESAR</spam></a></td></tr></table>";
		$data['filtro']=$filter->output;
		$data['tabla']=$tabla;
		//$data['smenu'] = $back;//.$grid->output;
		$data['title']   = "Genera Catalogo";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes2.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas_pru', $data);
	}
	
	
	function instalar(){
		
	}
}

?>