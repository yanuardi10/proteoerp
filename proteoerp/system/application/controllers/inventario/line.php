<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//lineasinventario
class Line extends validaciones {
	
       function line(){
	      parent::Controller(); 
	      $this->load->library("rapyd");
	      $this->datasis->modulo_id(306,1);
       }

       function index(){
	      redirect("inventario/line/filteredgrid");
       }

       function filteredgrid(){
	      $this->rapyd->load("datafilter","datagrid");
	      $this->rapyd->uri->keep_persistence();

	      // tool bar		
	      $mtool  = "<table background='#554455'><tr>";
	      $mtool .= "<td>&nbsp;</td>";
	      $mtool .= "<td>&nbsp;<a href='".base_url()."inventario/line/dataedit/create'>";
	      $mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
	      $mtool .= "</a>&nbsp;</td>";
	      $mtool .= "</tr></table>";

	      $grid = new DataGrid("Lista de Lineas de Inventario");
	      $grid->db->select("linea, a.descrip AS descrip, b.descrip AS depto, a.cu_inve AS cu_inve, a.cu_venta AS cu_venta, a.cu_cost AS cu_cost, a.cu_devo AS cu_devo");
	      $grid->db->from("line AS a");
	      $grid->db->join("dpto AS b","a.depto=b.depto");

	      $grid->order_by("linea","asc");
	      $grid->per_page = 60;

	      $grid->column_sigma("Depto"              ,"depto",    "", "width: 40, align:'center', frozen: true");
	      $grid->column_sigma("Linea"              ,"linea",    "", "width: 40, align:'center', frozen: true, renderer: linever");
	      $grid->column_sigma("Descripci&oacute;n" ,"descrip",  "", "width:250, align:'left', editor: { type: 'text' }");
	      $grid->column_sigma("Cuenta Costo"       ,"cu_cost",  "", "align:'center'");
	      $grid->column_sigma("Cuenta Inventario"  ,"cu_inve",  "", "align:'center'");
	      $grid->column_sigma("Cuenta Venta"       ,"cu_venta", "", "align:'center'");
	      $grid->column("Cuenta Devoluci&oacute;n" ,"cu_devo",  "", "align:'center'");
		
	      $sigmaA     = $grid->sigmaDsConfig("line","linea","inventario/line/");
	      $dsOption   = $sigmaA["dsOption"];
	      $grupver    = "
function linever(value, record, columnObj, grid, colNo, rowNo){
       var url = '';
       url = '<a href=\"#\" onclick=\"window.open(\'".base_url()."inventario/line/dataedit/show/'+value+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
       url = url +value+'</a>';
       return url;	
}
";
	      $colsOption = $sigmaA["colsOption"];
	      $gridOption = $sigmaA["gridOption"];
	      $gridGuarda = $sigmaA["gridGuarda"];

	      $gridGo = "
var mygrid=new Sigma.Grid(gridOption);
Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );
";

		$SigmaCont = "<center><div id=\"grid1_container\" style=\"width:700px;height:500px;\"></div></center>";
		$grid->add("inventario/grup/dataedit/create");
		$grid->build('datagridSG');
		//echo $grid->db->last_query();

		$data['style']  = style("redmond/jquery-ui.css");
		$data['style'] .= style('gt_grid.css');

		$data["script"]  = script("jquery.js");
		$data['script'] .= script("gt_msg_es.js");
		$data['script'] .= script("gt_grid_all.js");

		$data['script'] .= "<script type=\"text/javascript\" >\n";
		$data['script'] .= $dsOption.$grupver."\n";
		$data['script'] .= $colsOption."\n";
		$data['script'] .= $gridOption;
		$data['script'] .= $gridGuarda;
		$data['script'] .= $gridGo;
		$data['script'] .= "\n</script>";

		$data['content'] = $mtool.$SigmaCont;  //$grid->output;

		$data['title']   = "<h1>Lineas de Inventario</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	// sigma grid
	function controlador() {
		//header('Content-type:text/javascript;charset=UTF-8');
//memowrite($_POST["_gt_json"],"jsonrecibido");
		if (isset($_POST["_gt_json"]) ) {
			$json=json_decode(stripslashes($_POST["_gt_json"]));
			if($json->{'action'} == 'load') {
				$pageNo   = $json->{'pageInfo'}->{'pageNum'};
				$pageSize = $json->{'pageInfo'}->{'pageSize'};
				$filter = '';

				if(isset($json->{'sortInfo'}[0]->{'columnId'})){
					$sortField = $json->{'sortInfo'}[0]->{'columnId'};
				} else {
					$sortField = "linea";
				}    
	 
				if(isset($json->{'sortInfo'}[0]->{'sortOrder'})){
					$sortOrder = $json->{'sortInfo'}[0]->{'sortOrder'};
				} else {
					$sortOrder = "ASC";
				}    
	
				for ($i = 0; $i < count($json->{'filterInfo'}); $i++) {
					if($json->{'filterInfo'}[$i]->{'logic'} == "equal"){
						$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . "='" . $json->{'filterInfo'}[$i]->{'value'} . "' ";
					}elseif($json->{'filterInfo'}[$i]->{'logic'} == "notEqual"){
						$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . "!='" . $json->{'filterInfo'}[$i]->{'value'} . "' ";    
					}elseif($json->{'filterInfo'}[$i]->{'logic'} == "less"){
						$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . "<" . $json->{'filterInfo'}[$i]->{'value'} . " ";
					}elseif($json->{'filterInfo'}[$i]->{'logic'} == "lessEqual"){
						$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . "<=" . $json->{'filterInfo'}[$i]->{'value'} . " ";    
					}elseif($json->{'filterInfo'}[$i]->{'logic'} == "great"){
							$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . ">" . $json->{'filterInfo'}[$i]->{'value'} . " ";
					}elseif($json->{'filterInfo'}[$i]->{'logic'} == "greatEqual"){
						$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . ">=" . $json->{'filterInfo'}[$i]->{'value'} . " ";        
					}elseif($json->{'filterInfo'}[$i]->{'logic'} == "like"){
						$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . " LIKE '%" . $json->{'filterInfo'}[$i]->{'value'} . "%' ";        
					}elseif($json->{'filterInfo'}[$i]->{'logic'} == "startWith"){
						$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . " LIKE '" . $json->{'filterInfo'}[$i]->{'value'} . "%' ";        
					}elseif($json->{'filterInfo'}[$i]->{'logic'} == "endWith"){
						$filter .= $json->{'filterInfo'}[$i]->{'columnId'} . " LIKE '%" . $json->{'filterInfo'}[$i]->{'value'} . "' ";                
					}
					$filter .= " AND ";
				}


				//to get how many total records.
				$mSQL = "SELECT count(*) FROM line WHERE $filter linea IS NOT NULL";
				$totalRec = $this->datasis->dameval($mSQL);
 
 
				//make sure pageNo is inbound
				if($pageNo<1||$pageNo>ceil(($totalRec/$pageSize))){
					$pageNo = 1;
				}

				$mSQL = "SELECT linea, descrip, depto, cu_inve, cu_cost, cu_venta, cu_devo ";
				$mSQL .= "FROM line WHERE $filter linea IS NOT NULL ORDER BY ".$sortField." ".$sortOrder." LIMIT ".($pageNo - 1)*$pageSize.", ".$pageSize;
//memowrite($mSQL,"mSQL");
				$query = $this->db->query($mSQL);
				if ($query->num_rows() > 0){
					$retArray = array();
					foreach( $query->result_array() as  $row ) {
						$retArray[] = $row;
					}
					$data = json_encode($retArray);
					$ret = "{data:" . $data .",\n";
					$ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
					$ret .= "recordType : 'object'}";
				} else {
					$ret = '{data : []}';
				}
				echo $ret;

			}else if($json->{'action'} == 'save'){	}
		} else {
			// no hay _gt_json
			echo '{data : []}';
		}
	}

       function modifica(){
	      $valor = $this->uri->segment($this->uri->total_segments());
	      $campo = $this->uri->segment($this->uri->total_segments()-1);
	      $grupo = $this->uri->segment($this->uri->total_segments()-2);
	      $mSQL = "UPDATE line SET ".$campo."='".addslashes($valor)."' WHERE linea='".$grupo."' ";
	      $this->db->simple_query($mSQL);
	      echo "$valor $campo $grupo";
       }

	
       function dataedit($status='',$id='')
       {
	      $this->rapyd->load("dataobject","dataedit");

	      $qformato=$this->qformato=$this->datasis->formato_cpla();
	      $link=site_url('inventario/line/ultimo');
	      $link2=site_url('inventario/common/sugerir_line');

	      $script='
	      function ultimo(){ $.ajax({ url: "'.$link.'", success: function(msg){ alert( "El ultimo codigo ingresado fue: " + msg );}});}
		
	      function sugerir(){
		     $.ajax({
		     url: "'.$link2.'",
		     success: function(msg){
					 if(msg){
						 $("#linea").val(msg);
					  } else {
						 alert("No es posible generar otra sugerencia. Coloque el c&oacute;digo manualmente");
					  }
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
		
	      $mdepto=array(
			    'tabla'   =>'dept',
			    'columnas'=>array(
			    'codigo' =>'C&oacute;odigo',
			    'departam'=>'Nombre'),
			    'filtro'  =>array('codigo'=>'C&oacute;digo','nombre'=>'Nombre'),
			    'retornar'=>array('codigo'=>'depto'),
			    'titulo'  =>'Buscar Departamento');
			
	      $boton=$this->datasis->modbus($mdepto);
		
	      $do = new DataObject("line");
	      if($status=="create" && !empty($id)){
		     $do->load($id);
		     $do->set('linea', '');
	      }

	      $edit = new DataEdit("Linea de Inventario", $do);
	      $edit->back_url = site_url("inventario/line/filteredgrid");
	      $edit->script($script, "create");
	      $edit->script($script, "modify");
		
	      $edit->pre_process('delete','_pre_del');
	      $edit->post_process('insert','_post_insert');
	      $edit->post_process('update','_post_update');
	      $edit->post_process('delete','_post_delete');
		
	      $edit->dpto = new dropdownField("Departamento", "depto");
	      $edit->dpto->option("","");
	      $edit->dpto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");
	      $edit->dpto->rule ="required";
	      $edit->dpto->style='width:250px;';
		
	      $ultimo='<a href="javascript:ultimo();" title="Consultar ultimo c&oacute;digo ingresado"> Consultar ultimo c&oacute;digo</a>';
	      $sugerir='<a href="javascript:sugerir();" title="Sugerir un C&oacute;digo aleatorio">Sugerir C&oacute;digo </a>';
	      $edit->linea =  new inputField("C&oacute;digo Linea", "linea");
	      $edit->linea->mode="autohide";
	      $edit->linea->size =4;
	      $edit->linea->rule ="trim|strtoupper|required|callback_chexiste";
	      $edit->linea->maxlength=2;
	      $edit->linea->append($sugerir);
	      $edit->linea->append($ultimo);
				
	      $edit->descrip =  new inputField("Descripci&oacute;n", "descrip");
	      $edit->descrip->size = 35;
	      $edit->descrip->rule= "trim|strtoupper|required";
	      $edit->descrip->maxlength=30;
		
	      $edit->cu_inve =new inputField("Cuenta Inventario", "cu_inve");
	      $edit->cu_inve->size = 18;
	      $edit->cu_inve->maxlength=15;
	      $edit->cu_inve->rule ="trim|callback_chcuentac";
	      $edit->cu_inve->append($bcu_inve);
		
	      $edit->cu_cost =new inputField("Cuenta Costo", "cu_cost");
	      $edit->cu_cost->size = 18;
	      $edit->cu_cost->maxlength=15;
	      $edit->cu_cost->rule ="trim|callback_chcuentac";
	      $edit->cu_cost->append($bcu_cost);
		
	      $edit->cu_venta  =new inputField("Cuenta Venta", "cu_venta");
	      $edit->cu_venta->size =18;
	      $edit->cu_venta->maxlength=15;
	      $edit->cu_venta->rule ="trim|callback_chcuentac";
	      $edit->cu_venta->append($bcu_venta);
		
	      $edit->cu_devo = new inputField("Cuenta Devoluci&oacute;n","cu_devo");
	      $edit->cu_devo->size = 18;
	      $edit->cu_devo->maxlength=15;
	      $edit->cu_devo->rule ="trim|callback_chcuentac";
	      $edit->cu_devo->append($bcu_devo);
    	 		   	
	      $edit->buttons("modify", "save", "undo", "delete", "back");
	      $edit->build();
 
	      $data['content'] = $edit->output;           
	      $data['title']   = "<h1>Lineas de Inventario</h1>";        
	      $data["head"]    = script("jquery.pack.js").$this->rapyd->get_head();//.script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").
	      $this->load->view('view_ventanas', $data);  
       }
       function _post_insert($do){
	      $codigo=$do->get('linea');
	      $nombre=$do->get('descrip');
	      logusu('line',"LINEA DE INVENTARIO $codigo NOMBRE  $nombre CREADO");
       }
       function _post_update($do){
		$codigo=$do->get('linea');
		$nombre=$do->get('descrip');
		logusu('line',"LINEA DE INVENTARIO $codigo NOMBRE  $nombre  MODIFICADO");
       }
       function _post_delete($do){
		$codigo=$do->get('linea');
		$nombre=$do->get('descrip');
		logusu('line',"LINEA DE INVENTARIO $codigo NOMBRE  $nombre  ELIMINADO ");
       }
	
       function chexiste($codigo){
		$codigo=$this->input->post('linea');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM line WHERE linea='$codigo'");
		if ($chek > 0){
			$linea=$this->datasis->dameval("SELECT descrip FROM line WHERE linea='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para la linea $linea");
			return FALSE;
		}else {
  		return TRUE;
		}	
       }
	
       function _pre_del($do) {
		$codigo=$do->get('line');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM grup WHERE linea='$codigo'");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='La l&iacute;nea contiene grupos, por ello no puede ser eliminada. Elimine primero todos los grupos que pertenezcan a esta l&iacute;nea';
			return False;
		}
		return True;
       }
	
       function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT linea FROM line ORDER BY linea DESC");
		echo $ultimo;
       }
}
?>