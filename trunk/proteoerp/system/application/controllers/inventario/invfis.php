<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Invfis extends validaciones {

	var $url = 'inventario/invfis/';
	
	function Invfis(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->datasis->modulo_id(3,1);
		////I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
		//define ("THISFILE",   APPPATH."controllers/compras/". $this->uri->segment(2).EXT);
	}
	function index(){
		redirect($this->url.'define');
	}
	
	function define($var1='',$var2='',$var3='',$var4=''){

		$this->rapyd->load("dataform");
				
		$form = new DataForm('inventario/invfis/define/process');

		$form->alma = new dropdownField("Almacen", "alma");
		$form->alma->options("SELECT ubica,ubides FROM caub WHERE gasto='N' AND invfis='N' ORDER BY ubides");
		$form->alma->rule='required';

		$form->fecha = new dateonlyField("Fecha", "fecha");
		$form->fecha->rule='required|chfecha';
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->size=12;

		$form->submit("btnsubmit","Generar");
		$form->build_form();

		if ($form->on_success()){
			$this->load->dbforge();
			
			$contrato2 = $form->alma->newValue;
			$contrato  = $this->db->escape($form->alma->newValue);
			$fecha     = $form->fecha->newValue;
						 
			
			$mSQL = "CREATE TABLE IF NOT EXISTS `INV$contrato2$fecha`
SELECT a.codigo,a.grupo,b.alma,b.existen,000000000.0 contado,000000000.0 agregar,000000000.0 quitar,000000000.0 sustituir, $fecha fecha,'NULLNULL' modificado,'NULLNULL' actualizado FROM sinv a JOIN itsinv b ON a.codigo= b.codigo";
			
			$this->db->query($mSQL);
			//$this->db->query();
			
		}
		
		$form1 = new DataForm('inventario/invfis/define/process/aa');
         
		$form1->inv = new dropdownField("Inventario Fisico", "inv");
		$mSQL=$this->db->query("SHOW TABLES LIKE 'INV%'");
		foreach($mSQL->result_array() AS $row){
			foreach($row AS $key=>$value)
				$form1->inv->option($value,substr($value,6,10));
  	}

		$form1->submit("btnsubmit","Inventariar");
		$form1->build_form();
		
		if ($form1->on_success() && $var2=='aa'){
			$inv=$form1->inv->newValue;
			redirect($this->url.'inven/'.$inv);
		}	
		 
		$data['content'] = $form->output.$form1->output;//$form1->output.
		$data['title']   = ' ';//.$this->titulo.' ';
		$data["head"]    = $this->rapyd->get_head().script('jquery.js').script("plugins/jquery.numeric.pack.js").script("plugins/jquery.json.min.js");
		$this->load->view('view_ventanas', $data);
	}
	
	function inven($tabla){
	
		$this->rapyd->load("datagrid","dataobject","fields","datafilter2");
		
		$mSPRV=array(
				'tabla'   =>'sprv',
				'columnas'=>array(
				'proveed' =>'C&oacute;odigo',
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
		}
		';

		//filter
		$filter = new DataFilter2("Filtro por Producto");
		
		$filter->db->select("e.existen,e.contado,e.agregar,e.quitar,e.sustituir,a.tipo AS tipo,id,e.codigo,a.descrip,precio1,precio2,precio3,precio4,b.nom_grup AS nom_grup,b.grupo AS grupoid,c.descrip AS nom_linea,c.linea AS linea,d.descrip AS nom_depto,d.depto AS depto");
		$filter->db->from("$tabla AS e");
		$filter->db->join("sinv AS a","a.codigo=e.codigo");
		$filter->db->join("grup AS b","a.grupo=b.grupo");
		$filter->db->join("line AS c","b.linea=c.linea");
		$filter->db->join("dpto AS d","c.depto=d.depto");
		$filter->db->where("activo","S");
		$filter->db->where("actualizado","NULLNULL");
		
		$filter->script($script);
  		
		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo ->db_name = "e.codigo";
		$filter->codigo -> size=25;
		
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
		
		$filter->activo = new dropdownField("Activo", "activo");
		$filter->activo->option("","");
		$filter->activo->option("S","Si");
		$filter->activo->option("N","No");
		$filter->activo ->style='width:220px;';

		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		$filter->proveed->clause ="in";
		$filter->proveed->db_name='( a.prov1, a.prov2, a.prov3 )';
		$filter->proveed -> size=25;
		
		$filter->depto2 = new inputField("Departamento", "nom_depto");
		$filter->depto2->db_name="d.descrip";
		$filter->depto2 -> size=10;
		
		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$filter->depto->in="depto2";
		
		$filter->linea = new inputField("Linea", "nom_linea");
		$filter->linea->db_name="c.descrip";
		$filter->linea -> size=10;
		
		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="c.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$filter->linea2->in="linea";
		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo2 = new inputField("Grupo", "nom_grupo");
		$filter->grupo2->db_name="b.nom_grup";
		$filter->grupo2 -> size=10;
		
		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$filter->grupo->in="grupo2";
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
				
		///////////////////////////////////////////////////
		///////// FIN FILTRO ////////////////////////////
		/////////////////////////////////////////////////
		
				
		function caja($campo2,$valor,$codigo,$readonly=false){
			$campo = new inputField2("Title", $campo2);
			$campo->status = "create";
			$campo->css_class='inputnum';
			$campo->size=10;
			$campo->insertValue=$valor;
			$campo->readonly = $readonly;
			$campo->name = $codigo;
			$campo->id   = 'I'.$campo2.'_'.$codigo;
			$campo->build();
			return $campo->output;
		}

		$grid = new DataGrid("Inventario Fisico");

		$grid->per_page = 10;
		$grid->db->limit = 10;
		$grid->use_function('caja');

		$grid->column_orderby("Codigo"             ,"codigo"        ,"codigo"   ,'align=center');
		$grid->column_orderby("Descripci&oacute;n" ,"descrip"       ,"descrip"          );
		$grid->column_orderby("Anterior"           ,"existen"       ,"existen"  ,'align=right'       );
		$grid->column_orderby("Contado"            ,"<caja>c|<#contado#>|<#codigo#>|true</caja>");		
		$grid->column("Agregar"            ,"<caja>a|<#agregar#>|<#codigo#></caja>");
		$grid->column("Quitar"             ,"<caja>q|<#quitar#>|<#codigo#></caja>");
		$grid->column("Sustituir"          ,"<caja>s|<#sustituir#>|<#codigo#></caja>");
		
		$grid->build();
		
		$data['script']  ='<script language="javascript" type="text/javascript">
			var data2;
			function traer(cod){
				$.post("'.site_url($this->url.'traer').'",{ codigo:cod,tabla:"'.$tabla.'" },function(data){
					$("#Ic_"+cod).val(data);
					data2=data;
				})
				return data2;
			}
		$(function() {
			$(".inputnum").numeric(".");
			
			$("input[id^=\'I\']").focus(function(){
				cod =$(this).attr("name");
				traer(cod);
				return false;
			});
			
			$("input[id^=\'Ia_\']").change(function(){
				cod =$(this).attr("name");
				val = $("#Ia_"+cod).val();
				con = $("#Ic_"+cod).val();
				$.post("'.site_url($this->url.'agregar').'",{ codigo:cod,valor:val,tabla:"'.$tabla.'",contado:con },function(data){
					traer(cod);
					$("#Ia_"+cod).val(0);
					if(data){
						alert(data);
					}
				})
			});
		
			$("input[id^=\'Iq_\']").change(function(){
					cod =$(this).attr("name");
					val = $("#Iq_"+cod).val();
					con = $("#Ic_"+cod).val();
					$.post("'.site_url($this->url.'quitar').'",{ codigo:cod,valor:val,tabla:"'.$tabla.'",contado:con },function(data){
						traer(cod);
						$("#Iq_"+cod).val(0);
						if(data){
							alert(data);
						}
					})
			});
			
			$("input[id^=\'Is_\']").change(function(){
					cod =$(this).attr("name");
					val = $("#Is_"+cod).val();
					con = $("#Ic_"+cod).val();
					$.post("'.site_url($this->url.'sustituir').'",{ codigo:cod,valor:val,tabla:"'.$tabla.'",contado:con },function(data){
						traer(cod);
						$("#Is_"+cod).val(0);
						if(data){
							alert(data);
						}
					})
			});
		});
		</script>';
		
		$salida=anchor($this->url,"Regresar");
		$data['content'] = $filter->output.$salida.$grid->output;
		//$data['title']   = "($codigoadm) $codigoadmdes $tipo";
		$data["head"]    = script("jquery.js").script("plugins/jquery.numeric.pack.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function agregar(){
		$codigo = $this->db->escape($this->input->post('codigo'));
		$valor  = $this->input->post('valor');
		$tabla  = $this->input->post('tabla');
		$contado= $this->input->post('contado');
		$condb  = $this->datasis->dameval("SELECT contado FROM $tabla WHERE codigo=$codigo");
		if(round($contado,2) != round($condb,2))
			echo "ERROR: El valor Contado ($contado) se modifico a ($condb) mientras modificaba el valor";
		else
			$this->db->simple_query("UPDATE $tabla SET contado=contado+$valor,modificado=now() WHERE codigo=$codigo");
	}
	
	function quitar(){
		$codigo = $this->db->escape($this->input->post('codigo'));
		$valor  = $this->input->post('valor');
		$tabla  = $this->input->post('tabla');
		$contado= $this->input->post('contado');
		$condb  = $this->datasis->dameval("SELECT contado FROM $tabla WHERE codigo=$codigo");
		if(round($contado,2) != round($condb,2))
			echo "ERROR: El valor Contado ($contado) se modifico a ($condb) mientras modificaba el valor";
		else 
			$this->db->simple_query("UPDATE $tabla SET contado=contado-$valor,modificado=now() WHERE codigo=$codigo");
	}
	
	function sustituir(){
		$codigo = $this->db->escape($this->input->post('codigo'));
		$valor  = $this->input->post('valor');
		$tabla  = $this->input->post('tabla');
		$contado= $this->input->post('contado');
		$condb  = $this->datasis->dameval("SELECT contado FROM $tabla WHERE codigo=$codigo");
		if(round($contado,2) != round($condb,2))
			echo "ERROR: El valor Contado ($contado) se modifico a ($condb) mientras modificaba el valor";
		else 
			$this->db->simple_query("UPDATE $tabla SET contado=$valor,modificado=now() WHERE codigo=$codigo");
	}
	
	function traer(){
		$codigo = $this->db->escape($this->input->post('codigo'));
		$tabla  = $this->input->post('tabla');
		echo $this->datasis->dameval("SELECT contado FROM $tabla WHERE codigo=$codigo");
	}
}