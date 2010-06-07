<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Grup extends validaciones {
	
	function grup(){
		parent::Controller();
		$this->load->library("rapyd");
	  //$this->datasis->modulo_id(304,1);
	}
	
	function index(){		
		redirect("supermercado/grup/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
		
		function blanco($num){
			if(empty($num)||$num==0){
			 return '';
			}else{
				return number_format($num,2,',','.');
			}
		}
		
		$filter = new DataFilter("Filtro de Grupo de Inventario");
		
		$filter->db->select("a.grupo AS grupo, a.nom_grup AS nom_grup, a.comision AS comision,b.familia AS fami, b.descrip AS familia,c.depto AS dpto,c.descrip AS depto, a.cu_inve AS cu_inve, a.cu_venta AS cu_venta, a.cu_cost AS cu_cost, a.cu_devo AS cu_devo");
		$filter->db->from("grup AS a");
		$filter->db->join("fami AS b","a.familia=b.familia");
		$filter->db->join("dpto AS c","b.depto=c.depto");
		
		$filter->grupo = new inputField("Grupo","grupo");
		$filter->grupo->size=20;
		
		$filter->nombre = new inputField("Descripci&oacute;n","nom_grup");
		$filter->nombre->size=20;
		
		$filter->comision = new inputField("Comisi&oacute;n","comision");
		$filter->comision->size=20;
		
		$filter->depto = new inputField("Departamento","c.descrip");
		$filter->depto->size=20;
		
		$filter->linea = new inputField("Familia","b.descrip");
		$filter->linea->size=20;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('supermercado/grup/dataedit/show/<raencode><#grupo#></raencode>/<raencode><#fami#></raencode>/<raencode><#dpto#></raencode>','<#grupo#>');
		$uri_2 = anchor('supermercado/grup/dataedit/create/<raencode><#grupo#></raencode>/<raencode><#fami#></raencode>/<raencode><#dpto#></raencode>','Duplicar');

		$grid = new DataGrid("Lista de Grupos de Inventario");
		$grid->order_by("grupo","asc");
		$grid->per_page = 20;
		$grid->use_function('blanco');

		$grid->column("Grupo"                       ,$uri                            ,"align='center'");
		$grid->column("Descripci&oacute;n"                 ,"nom_grup"                      ,"align='left'");
		$grid->column("Comisi&oacute;n"                    ,"<blanco><#comision#></blanco>" ,"align='right'");		
		$grid->column("Departamento"                ,"depto"                         ,"align='left'");
		$grid->column("Familia"                     ,"familia"                       ,"align='left'");
		$grid->column("Cuenta Inventario"           ,"cu_inve"                       ,"align='center'");
		$grid->column("Cuenta Costo"                ,"cu_cost"                       ,"align='center'");
		$grid->column("Cuenta Venta"                ,"cu_venta"                      ,"align='center'");
		$grid->column("Cuenta Devoluci&oacute;n"    ,"cu_devo"                       ,"align='center'");
		$grid->column("Duplicar"                    ,$uri_2                          ,"align='center'");

		$grid->add("supermercado/grup/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Grupos de Inventario</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit($status='',$grupo='',$familia='',$depto='')
 	{
		$this->rapyd->load("dataobject","dataedit");
		
		$qformato=$this->qformato=$this->datasis->formato_cpla();
		$link=site_url('supermercado/grup/ultimo');
		$link2=site_url('supermercado/grup/sugerir');
		
		$script ='
			$(function() {
				$(".inputnum").numeric(".");
			});
			
			function ultimo(){
				$.ajax({
					url: "'.$link.'",
					success: function(msg){
					  alert( "El ultimo codigo ingresado fue: " + msg );
					}
				});
			}
		
			function sugerir(){
				$.ajax({
						url: "'.$link2.'",
						success: function(msg){
							if(msg){
								$("#grupo").val(msg);
							}
							else{
								alert("No es posible generar otra sugerencia. Coloque el c&oacute;digo manualmente");
							}
						}
					});
			}
    	
			function get_familia(){
				$.ajax({
					type: "POST",
					url: "'.site_url('supermercado/grup/familia').'",
					data: $("#dpto").serialize(),
					success: function(msg){
						$("#td_familia").html(msg);
					},
					error: function(msg){
						alert("Error en la comunicaci&oacute;n");
					}
				});
			}';

		$modbus=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'<#i#>'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
			'p_uri'=>array(4=>'<#i#>')
		);

		$bcu_venta = $this->datasis->p_modbus($modbus,'cu_venta');
		$bcu_inve  = $this->datasis->p_modbus($modbus,'cu_inve' );
		$bcu_cost  = $this->datasis->p_modbus($modbus,'cu_cost' );
		$bcu_devo  = $this->datasis->p_modbus($modbus,'cu_devo' );

		$do = new DataObject("grup"); $do->set('tipo', 'I'); if($status=="create" && 
		!empty($grupo) && !empty($familia) && !empty($depto)){ $do-
		>load(array("familia"=> "$familia","grupo"=> "$grupo","depto"=> "$depto")); 
		$do->set('grupo', ''); }

		$edit = new DataEdit("Grupos de Inventario",$do);
		$edit->back_url = site_url("supermercado/grup/filteredgrid");
		$edit->script($script, "modify");
		$edit->script($script, "create");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		
		$edit->depto = new dropdownField("Departamento", "dpto");
		$edit->depto->db_name='depto';
		$edit->depto->rule ="required";
		$edit->depto->onchange = "get_familia();";
		$edit->depto->option("","Seleccionar");
		$edit->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");

		$edit->familia = new dropdownField("Familia","familia");
		$edit->familia->rule ="required";
		if($edit->_status=='modify' or $edit->_status=='show' or $edit->_status=='idle' or $edit->_status=='create'){
			$depto = ($this->input->post('dpto')===FALSE) ? $edit->_dataobject->get("depto") : $this->input->post('dpto');
			$edit->familia->option("","");
			$edit->familia->options("SELECT familia, descrip FROM fami WHERE depto='$depto' ORDER BY descrip");
		}else{
			$edit->familia->option("","Seleccione un Departamento");
		}

		$ultimo='<a href="javascript:ultimo();" title="Consultar ultimo c&oacute;digo ingresado"> Consultar ultimo c&oacute;digo</a>';
		$sugerir='<a href="javascript:sugerir();" title="Sugerir un C&oacute;digo aleatorio">Sugerir C&oacute;digo </a>';
		$edit->grupo =  new inputField("C&oacute;digo Grupo", "grupo");
		$edit->grupo->mode="autohide";
		$edit->grupo->maxlength=4;
		$edit->grupo->size =6;
		$edit->grupo->rule ="trim|strtoupper|required|callback_chexiste";
		$edit->grupo->append($sugerir);
		$edit->grupo->append($ultimo);
		
		$edit->nom_grup =  new inputField("Nombre del Grupo", "nom_grup");
		$edit->nom_grup->size = 35;
		$edit->nom_grup->maxlength=30;
		$edit->nom_grup->rule = "trim|strtoupper|required";
		
	  //$edit->tipo = new dropdownField("Tipo","tipo");
	  //$edit->tipo->style='width:100px;';
		//$edit->tipo->option("I","Inventario" );
		//$edit->tipo->option("G","Gasto"  );
		
		$edit->comision = new inputField("Comisi&oacute;n. %", "comision");
		$edit->comision->size = 18;
		$edit->comision->maxlength=10;
		$edit->comision->css_class='inputnum';
		$edit->comision->rule='trim|numeric|callback_positivo';
		
		for($i=1;$i<=5;$i++){
			$obj="margen$i";
			$edit->$obj = new inputField("Margen $i. %", $obj);
			$edit->$obj->size = 18;
			$edit->$obj->maxlength=7;
			$edit->$obj->css_class='inputnum';
			$edit->$obj->rule='trim|numeric|callback_positivo';
		}
		
		$edit->cu_inve =new inputField("Cuenta Inventario", "cu_inve");
		$edit->cu_inve->size = 18;
		$edit->cu_inve->maxlength=15;
		$edit->cu_inve->rule ="trim|callback_chcuentac";
		$edit->cu_inve->append($bcu_inve);
		$edit->cu_inve->group='Cuentas contables';
		
		$edit->cu_cost =new inputField("Cuenta Costo", "cu_cost");
		$edit->cu_cost->size = 18;
    $edit->cu_cost->maxlength=15;
    $edit->cu_cost->rule ="trim|callback_chcuentac";
    $edit->cu_cost->append($bcu_cost);
    $edit->cu_cost->group='Cuentas contables';
		
		$edit->cu_venta  =new inputField("Cuenta Venta", "cu_venta");
		$edit->cu_venta->size =18;
		$edit->cu_venta->maxlength=15;
		$edit->cu_venta->rule ="trim|callback_chcuentac";
		$edit->cu_venta->append($bcu_venta);
		$edit->cu_venta->group='Cuentas contables';
		
    $edit->cu_devo = new inputField("Cuenta Devoluci&oacute;n","cu_devo");
    $edit->cu_devo->size = 18;
    $edit->cu_devo->maxlength=15;
    $edit->cu_devo->rule ="trim|callback_chcuentac";
    $edit->cu_devo->append($bcu_devo);
    $edit->cu_devo->group='Cuentas contables';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$link=site_url('supermercado/grup/get_familia');

		$data['content'] = $edit->output;
    $data['title']   = "<h1>Grupos de Inventario</h1>";        
    $data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
	}
	
	function _post_insert($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grup',"GRUPO DE INVENTARIO $codigo NOMBRE  $nombre CREADO");
	}
	
	function _post_update($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grup',"GRUPO DE INVENTARIO $codigo NOMBRE  $nombre  MODIFICADO");
	}
	
	function _post_delete($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('grup',"GRUPO DE INVENTARIO $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	
	function chexiste($codigo){
		$codigo=$this->input->post('grupo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM grup WHERE grupo='$codigo'");
		if ($chek > 0){
			$grupo=$this->datasis->dameval("SELECT nom_grup FROM grup WHERE grupo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el grupo $grupo");
			return FALSE;
		}else {
  		return TRUE;
		}
	}

	function _pre_del($do) {
		$codigo=$do->get('grupo');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM sinv WHERE grupo='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='El grupo contiene productos, por ello no puede ser eliminado. Elimine primero todos los productos que pertenezcan a este grupo';
			return False;
		}
		return True;
	}
	
	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT grupo FROM grup ORDER BY grupo DESC");
		echo $ultimo;
	}
	
	function positivo($valor){
		if ($valor < 0){
			$this->validation->set_message('positivo',"El campo comisi&oacute;n debe ser positivo");
			return FALSE;
		}
  	return TRUE;
	}
	
	function sugerir(){
		$ultimo=$this->datasis->dameval("SELECT LPAD(hexa,4,0) FROM serie LEFT JOIN grup ON LPAD(grupo,4,0)=LPAD(hexa,4,0) WHERE valor<65535 AND grupo IS NULL LIMIT 1");
		echo $ultimo;
	}
	
	function familia(){
		if (!empty($_POST["dpto"])){ 
			$departamento=$_POST["dpto"];
		}elseif (!empty($_POST["depto"])){
 			$departamento=$_POST["depto"];
		}
		
		$this->rapyd->load("fields");  
		$where = "";  
		$sql = "SELECT familia, descrip FROM fami ";
		$familia = new dropdownField("Subcategoria", "familia");

		if (!empty($departamento)){
		  $where = "WHERE depto = ".$this->db->escape($departamento);
		  $sql = "SELECT familia, descrip FROM fami $where";
		  $familia->option("","");
			$familia->options($sql);
		}else{
			 $familia->option("","Seleccione Un Departamento"); 
		} 
		$familia->status   = "modify";
		$familia->build();
		echo $familia->output;
	}
}

?>