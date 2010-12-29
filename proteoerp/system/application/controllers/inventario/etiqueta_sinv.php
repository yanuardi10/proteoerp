<?php
/*****
 * Realizado por Judelvis A. Rivas
 * Modulo para generar etiquetas de productos de la tabla sinv
 * Uso:
 * 1)La función filteredgrid(): genera etiquetas por medio de un filtro de productos por departamento,linea,grupo,marca
 * 2)La función num_control():genera etiquetas a traves de un filtro de numero de control de compra...
 * 3)La función lee_barras():genera etiquetas por medio de insercion de codigo de barras por el teclado
 */

class etiqueta_sinv extends Controller {

	function etiqueta_sinv(){
		parent::Controller();
		$this->load->library("rapyd");
	}
	function index(){
		redirect("inventario/etiqueta_sinv/menu");
	}

	function menu(){
		$thtml='<b>Seleccione metodo para generar etiquetas</b>';

		$html[]=anchor('inventario/etiqueta_sinv/num_compra'  ,'Por n&uacute;mero compra'   ).': generar etiquetas con todos los productos pertenecientes a una compra';
		$html[]=anchor('inventario/etiqueta_sinv/lee_barras'  ,'Por c&oacute;digo de barras').': permite generar por productos pasados';
		$html[]=anchor('inventario/etiqueta_sinv/filteredgrid','Por filtro de productos'    ).': permite generar las etiquetas por cacteristicas comunes';

		$data['title']  = '<h1>Men&uacute; Etiquetas</h1>';
		$data['content']=$thtml.ul($html);
		$this->load->view('view_ventanas', $data);
	}

	function filteredgrid($para=''){
		$this->rapyd->load('datafilter2','datagrid','dataobject','fields');

		$user  = $this->session->userdata('usuario');
		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');
		//$link4=site_url('inventario/etiqueta_sinv/tabla');
		$link1=site_url('inventario/etiqueta_sinv/menu');

		$script='
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
		}';

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

		$filter->buttons('reset','search');
		$filter->build();

		$tabla='';

		if($this->rapyd->uri->is_set("search")  AND $filter->is_valid()){
			$tabla=form_open("forma/ver/etiqueta1");
			
			$grid = new DataGrid("Lista de Art&iacute;culos Para Etiquetas");
			$grid->db->select("COUNT(*) AS num,a.tipo AS tipo,a.id as id,a.codigo as codigo,a.descrip,a.precio1 as precio,a.barras,
			b.nom_grup AS nom_grup, b.grupo AS grupoid,
			c.descrip AS nom_linea, c.linea AS linea,
			d.descrip AS nom_depto, d.depto AS depto");

			$grid->db->from("sinv AS a");
			$grid->db->join("grup AS b","a.grupo=b.grupo");
			$grid->db->join("line AS c","b.linea=c.linea");
			$grid->db->join("dpto AS d","c.depto=d.depto");

			$grid->db->group_by("a.codigo");
			$grid->db->_escape_char='';
			$grid->db->_protect_identifiers=false;
			$grid->order_by("codigo","asc");

			$grid->use_function('asigna');
			$grid->column_orderby("c&oacute;digo"     ,"codigo","codigo");
			$grid->column_orderby("Departamento"      ,"<#nom_depto#>","nom_depto",'align=left');
			$grid->column_orderby("L&iacute;nea"      ,"<#nom_linea#>","nom_linea",'align=left');
			$grid->column_orderby("Grupo"             ,"<#nom_grup#>" ,"nom_grup" ,'align=left');
			$grid->column_orderby("Descripci&oacute;n","descrip","descrip");

			$grid->build();
			$consul=$this->db->last_query();
			$tabla.=form_hidden('consul', $consul);

			$data = array(
				'name'      => 'cant',
				'id'        => 'cant',
				'value'     => '1',
				'maxlength' => '5',
				'size'      => '5',
			);

			$tabla.=$grid->output.form_label("Numero de etiquetas por producto:")."&nbsp&nbsp&nbsp".form_input($data).form_submit('mysubmit', 'Generar');
			$tabla.=form_close();
		}

		$back="<table width='100%'border='0'><tr><td width='80%'></td><td width='20%'><a href='javascript:atras()'><spam id='regresar'align='right'>REGRESAR</spam></a></td></tr></table>";
		$data['filtro']=$filter->output;
		$data['tabla']=$tabla;
		$data['smenu']="<a href='".$link1."' >Atras</a>";
		$data['title']   = "Genera Etiqueta";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes2.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas_pru', $data);
	}

	function num_compra(){
		$this->rapyd->load("datafilter2","datagrid","dataobject","fields");
		$link1=site_url('inventario/etiqueta_sinv/menu');
		$mSCST=array(
			'tabla'   =>'scst',
			'columnas'=>array(
			'control'=>'Control',
			'nombre'=>'Nombre'),
			'filtro'  =>array('control'=>'Control','nombre'=>'Nombre'),
			'retornar'=>array('control'=>'control'),
			'titulo'  =>'Buscar Codigo');
		$bSCST=$this->datasis->modbus($mSCST);

		$filter = new DataFilter2('N&uacute;mero de control de la compra');
		$filter->db->_escape_char='';
		$filter->db->_protect_identifiers=false;
		$filter->db->select(array("a.barras AS  barras","a.codigo AS codigo","a.descrip AS descrip","a.precio1 AS precio","b.control AS control"));
		$filter->db->from('sinv AS a');
		$filter->db->join('itscst AS b','a.codigo=b.codigo');

		$filter->codigo=new inputField("C&oacute;digo","control");
		$filter->codigo-> size=15;
		$filter->codigo->append($bSCST);

		$filter->buttons('reset','search');
		$filter->build();

		$tabla='';
		if($this->rapyd->uri->is_set("search")  AND $filter->is_valid()){
			$tabla=form_open('forma/ver/etiqueta1');

			$grid = new DataGrid('Art&iacute;culos a imprimir');
			//$grid->order_by('control','asc');
			//$grid->per_page = 15;

			$grid->use_function('str_replace');
			$grid->column_orderby("C&oacute;digo","codigo","codigo");
			$grid->column_orderby("Descripci&oacute;n",'descrip',"descrip");
			$grid->column_orderby("Precio","precio","precio");

			$grid->build();

			if($grid->recordCount>0){
				$consul=$this->db->last_query();
				$tabla.=form_hidden('consul', $consul);

				$data = array(
					'name'      => 'cant',
					'id'        => 'cant',
					'value'     => '1',
					'maxlength' => '5',
					'size'      => '5',
				);

				$tabla.=$grid->output.form_label("Numero de etiquetas por producto:")."&nbsp&nbsp&nbsp".form_input($data).form_submit('mysubmit', 'Generar');
				$tabla.=form_close();
			}else{
				$tabla='No se consiguieron productos asociados a esa compra';
			}
		}

		$data['filtro']= $filter->output;
		$data['tabla'] = $tabla;
		$data['smenu'] = "<a href='".$link1."' >Atras</a>";
		$data['title'] = 'Genera Etiqueta';
		$data['head']  = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes2.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas_pru', $data);
	}

	function lee_barras($para=''){
		$this->rapyd->load("datafilter2","datagrid","dataobject","fields");
		$link1=site_url('inventario/etiqueta_sinv/menu');
		$link2=site_url('inventario/sinv/barratonombre');
		$script=script('jquery.js').script("jquery-ui.js").style("le-frog/jquery-ui-1.7.2.custom.css").
		'<script type="text/javascript">
		var propa=false;

		$(document).ready(function() {
			c="";c1="";a="";
			$("#barras").hide();
			$(document).keydown(function(e){
				if (32 <= e.which && e.which <= 176) {
				  c = c+String.fromCharCode(e.which);
				} else if (e.which == 13) {
					$("#ent").show();
					if(c.length>0)
					c1=c+","+c1;
					
					$("#barras").val(c1);
					$.post("'.$link2.'",{barra:c},function (data){
						a=data;
					})
					$("#ent").html("Se agrego:"+c+"</br>"+a);
					if (propa!==false)
						clearTimeout(propa);
					propa=setTimeout(function() { $("#ent").fadeOut("slow"); },5000);
					c="";
				}
				return false;
			});
		});
		</script>';

		$data = array(
			'name'     => 'barras',
			'id'       => 'barras',
			'maxlength'=> '5',
			'size'     => '5',
		);

		$tabla=form_open("inventario/etiqueta_sinv/cant");
		$tabla.=form_input($data).form_submit('mysubmit', 'Generar');
		$tabla.=form_close();

		$data['smenu']="<a href='".$link1."' >Atras</a>";
		$data['content']=$tabla.'<div style="position: absolute;display: none; width: 40%; height: 30%; left: 15%; top: 30%; padding: 10px;" class="ui-widget ui-widget-content ui-corner-all" id="ent">';
		$data['title']   = "Genera Etiquetas";
		$data["head"]    =$script;
		$this->load->view('view_ventanas', $data);
	}

	function cant(){
		$tabla=form_open("forma/ver/etiqueta1");
		if($this->input->post("barras") !="" && $this->input->post("barras") !=null){
			$barras= explode(",",$this->input->post("barras"),-1);
			$campos=implode("','",$barras);
			
			$consul="SELECT codigo,barras,descrip,precio1 as precio from sinv WHERE barras IN ('$campos')";
			
			//memowrite($consul,'eti');
			$msql=$this->db->query($consul);
			$row=$msql->result();
			if (count($row)==0){
				$tabla.="<h1>Los c&oacute;digos de barras insertados no exiten</h1><br><a href='".site_url('inventario/etiqueta_sinv/lee_barras')."' >atras</a>";
			}else{
			
				$data = array(
		              'name'        => 'cant',
		              'id'          => 'cant',
		              'value'       => '1',
		              'maxlength'   => '5',
		              'size'        => '5',
		
				);
		
				$tabla.=form_hidden('consul', $consul);
				$tabla.=form_label("Numero de etiquetas por producto:")."&nbsp&nbsp&nbsp";
				$tabla.=form_input($data).'<br>';
				$tabla.=form_submit('mysubmit', 'Generar');
				$tabla.=form_close();

			}
		}else{
			$tabla.="<h1>Debe ingresar algun c&oacute;digo de barras</h1><br><a href='".site_url('inventario/etiqueta_sinv/lee_barras')."' >atras</a>";
		}
		
		$link1=site_url('inventario/etiqueta_sinv/lee_barras');
		$data['smenu']="<a href='".$link1."' >Atras</a>";
		$data['title']   = "Genera Etiquetas";
		$data['content']=$tabla;
		$this->load->view('view_ventanas', $data);
	}
}