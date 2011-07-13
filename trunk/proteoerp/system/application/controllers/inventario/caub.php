<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
//almacenes
class Caub extends validaciones {
	 
	var $data_type = null;
	var $data = null;
	
	function caub(){
		parent::Controller(); 

		$this->load->helper('url');
		$this->load->helper('text');
		$this->datasis->modulo_id(307,1);

		$this->load->library("rapyd");
		define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
	}
 
	function index(){
		$this->datasis->modulo_id(307,1);
		$ajus=$this->db->simple_query("INSERT IGNORE INTO caub (ubica,ubides,gasto,invfis) VALUES ('AJUS','AJUSTES','S','N')ON DUPLICATE KEY UPDATE ubides='AJUSTES', gasto='S',invfis='N'");
		$infi=$this->db->simple_query("INSERT IGNORE INTO caub (ubica,ubides,gasto,invfis) VALUES ('INFI','INVENTARIO FISICO','S','S')ON DUPLICATE KEY UPDATE ubides='INVENTARIO FISICO', gasto='S',invfis='S'");
		redirect("inventario/caub/filteredgrid");
    }
  
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$mtool  = "<table background='#554455'><tr>";
		$mtool .= "<td>&nbsp;</td>";
		$mtool .= "<td>&nbsp;<a href='".base_url()."inventario/caub/dataedit/create'>";
		$mtool .= img(array('src' => 'images/agregar.jpg', 'alt' => 'Agregar Registro', 'title' => 'Agregar Registro','border'=>'0','height'=>'32'));
		$mtool .= "</a>&nbsp;</td>";
		$mtool .= "</tr></table>";
		$mtool = '';

		$sucu = $this->datasis->dameval("SELECT GROUP_CONCAT( CONCAT(\" '\",codigo,\"' : '\", sucursal,\"'\") ) FROM sucu ");
		//$uri = anchor('inventario/caub/dataedit/show/<#ubica#>','<#ubica#>');
		//$uri_2 = anchor('inventario/departamentos/dataedit/create/<raencode><#ubica#></raencode>','Duplicar');

		$grid = new DataGrid("Almacenes");
		
		$grid->db->select("ubica, ubides, gasto, invfis, sucursal");
		$grid->db->from("caub");

		$grid->order_by("ubica","ASC");
		$grid->per_page = 20;
		$grid->use_function('si_no');

		$grid->column_sigma("C&oacute;digo",         "ubica",    "", "align:'center', width: 60, editor: { type: 'text'} ");
		$grid->column_sigma("Descripci&oacute;n",    "ubides",   "", "align:'left',   width:210, editor: { type: 'text'} ");
		$grid->column_sigma("Gasto",                 "gasto",    "", "align:'center', width: 50, editor: { type: 'select', options: {'N':'No', 'S':'Si'}} ");
		$grid->column_sigma("Inv/F&iacute;sico",     "invfis",   "", "align:'center', width: 70, editor: { type: 'select', options: {'N':'No', 'S':'Si'}} ");
		$grid->column_sigma("Sucursal",              "sucursal", "", "align:'left', editor : { type: 'select', options: {".$sucu."} }, renderer:colsucu " );
								
		$sigmaA     = $grid->sigmaDsConfig("caub","ubica","inventario/caub/");
		$dsOption   = $sigmaA["dsOption"];
		$grupver    = "
function ver(value, record, columnObj, grid, colNo, rowNo){
       var url = '';
       url = '<a href=\"#\" onclick=\"window.open(\'".base_url()."inventario/caub/dataedit/show/'+value+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
       url = url +value+'</a>';
       return url;	
}

function colsucu(value, record, columnObj, grid, colNo, rowNo) {
	var options = { ".$sucu." };
	var ret = options[value];
	if(ret==null){ ret = value; }
	return ret;
}
";
	      $colsOption = $sigmaA["colsOption"];
	      $gridOption = $sigmaA["gridOption"];
	      $gridGuarda = $sigmaA["gridGuarda"];

	      $gridGo = "
var mygrid=new Sigma.Grid(gridOption);
mygrid.width  = 530;
mygrid.height = 250;
mygrid.toolbarContent = 'nav | reload | add del save | print |';
//mygrid.defaultRecord = ['','','N','N','00'];
Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );
";

		$SigmaCont = "<center><div id=\"grid1_container\" style=\"width:540px;height:250px;\"></div></center>";
		$grid->add("inventario/caub/dataedit/create");
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

		$data['title']   = "<h1>Almacenes</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	// sigma grid
	function controlador() {
		//header('Content-type:text/javascript;charset=UTF-8');
		if (isset($_POST["_gt_json"]) ) {
			memowrite($_POST["_gt_json"],"caubjson");
			$json=json_decode(stripslashes($_POST["_gt_json"]));
			if($json->{'action'} == 'load') {
				$pageNo   = $json->{'pageInfo'}->{'pageNum'};
				$pageSize = $json->{'pageInfo'}->{'pageSize'};
				$filter = '';

				if(isset($json->{'sortInfo'}[0]->{'columnId'})){
					$sortField = $json->{'sortInfo'}[0]->{'columnId'};
				} else {
					$sortField = "ubica";
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
				$mSQL = "SELECT count(*) FROM caub WHERE $filter ubica IS NOT NULL";
				$totalRec = $this->datasis->dameval($mSQL);
  
				//make sure pageNo is inbound
				if($pageNo<1||$pageNo>ceil(($totalRec/$pageSize))){
					$pageNo = 1;
				}

				$mSQL  = "SELECT ubica, ubides, gasto, invfis, sucursal ";
				$mSQL .= "FROM caub  " ;
				$mSQL .= "WHERE $filter ubica IS NOT NULL ORDER BY ".$sortField." ".$sortOrder." LIMIT ".($pageNo - 1)*$pageSize.", ".$pageSize;
				//memowrite($mSQL,"caubsql1");
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

			} else if($json->{'action'} == 'save'){	
				for ($i = 0; $i < count($json->{'insertedRecords'}); $i++) {
					$mSQL = "INSERT IGNORE INTO caub (ubica, ubides, gasto, invfis, sucursal ) values ( ";
					$mSQL .= "'".addslashes($json->{'insertedRecords'}[$i]->{'ubica'})."',";
					$mSQL .= "'".addslashes($json->{'insertedRecords'}[$i]->{'ubides'})."',";
					$mSQL .= "'".addslashes($json->{'insertedRecords'}[$i]->{'gasto'})."',";
					$mSQL .= "'".addslashes($json->{'insertedRecords'}[$i]->{'invfis'})."',";
					$mSQL .= "'".addslashes($json->{'insertedRecords'}[$i]->{'sucursal'})."' )";
					memowrite($mSQL,'caubadd');
					$this->db->simple_query($mSQL);
				}
				for ($i = 0; $i < count($json->{'deletedRecords'}); $i++) {
					$ubica = $json->{'deletedRecords'}[$i]->{'ubica'};
					$mSQL = "SELECT COUNT(*) FROM costos WHERE ubica='".addslashes($ubica)."'";
					if ( $this->datasis->dameval($mSQL) == 0  && $ubica != 'INFI' && $ubica != 'AJUS' ) {
						$mSQL = "DELETE FROM caub WHERE ubica='".addslashes($ubica)."'";
						$this->db->simple_query($mSQL);
					}
				}
			}
		} else {
			// no hay _gt_json
			echo '{data : []}';
		}
	}

       function modifica(){
	      $valor = $this->uri->segment($this->uri->total_segments());
	      $campo = $this->uri->segment($this->uri->total_segments()-1);
	      $grupo = $this->uri->segment($this->uri->total_segments()-2);
	      $mSQL = "UPDATE caub SET ".$campo."='".addslashes($valor)."' WHERE ubica='".$grupo."' ";
	      $this->db->simple_query($mSQL);
	      echo "$valor $campo $grupo";
       }







	function dataedit($status='',$id='')
 	{
		$this->rapyd->load("dataobject","dataedit");
		
		$qformato=$this->qformato=$this->datasis->formato_cpla();
		$link=site_url('inventario/caub/ultimo');
		$link2=site_url('inventario/caub/sugerir');
		
		$script='
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
							$("#ubica").val(msg);
						}
						else{
							alert("No es posible generar otra sugerencia. Coloque el c&oacute;digo manualmente");
						}
					}
				});
		}		
		';
		
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
		
		$bcu_cost  =$this->datasis->modbus($modbus ,'cu_cost');
		$bcu_caja  =$this->datasis->modbus($modbus ,'cu_caja');
		
		$do = new DataObject("caub");
		if($status=="create" && !empty($id)){
			$do->load($id);
			$do->set('ubica', '');
		}

		$edit = new DataEdit("Almacenes", $do);
		$edit->back_url = site_url("inventario/caub/filteredgrid");
		$edit->script($script,"create");
		$edit->script($script,"modify");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('delete','_pre_del');
		
		$ultimo='<a href="javascript:ultimo();" title="Consultar ultimo c&oacute;digo ingresado"> Consultar ultimo c&oacute;digo </a>';
		$sugerir='<a href="javascript:sugerir();" title="Sugerir un C&oacute;digo aleatorio">Sugerir C&oacute;digo </a>';
		$edit->ubica = new inputField("Almacen", "ubica");
		$edit->ubica->mode="autohide";
		$edit->ubica->size = 6;
		$edit->ubica->maxlength=4;
		$edit->ubica->rule ="trim|required|callback_chexiste";
		$edit->ubica->append($sugerir);
		$edit->ubica->append($ultimo);
		
		$edit->ubides = new inputField("Nombre", "ubides");
		$edit->ubides->size =35;
		$edit->ubides->maxlength=30;
		$edit->ubides->rule= "trim|strtoupper|required";
		
		$edit->gasto = new dropdownField("Gasto", "gasto");
		$edit->gasto->option("N","No");
		$edit->gasto->option("S","Si");		
		$edit->gasto->style='width:60px';
		
		$edit->invfis=new dropdownField("Inventario F&iacute;sico", "invfis");
		$edit->invfis->option("N","No");
		$edit->invfis->option("S","Si");		
		$edit->invfis->style='width:60px';
		
		$edit->sucursal = new dropdownField("Sucursal","sucursal");
		$edit->sucursal->option("","");
		$edit->sucursal->options("SELECT codigo, sucursal FROM sucu ORDER BY sucursal");
		$edit->sucursal->style='width:135px;';
		
		$edit->cu_cost=new inputField("Cuenta Almacen", "cu_cost");
		$edit->cu_cost->size = 18;
		$edit->cu_cost->maxlength=15;
		$edit->cu_cost->rule="trim|callback_chcuentac";
		$edit->cu_cost->append($bcu_cost);
		
		$edit->cu_caja =new inputField("Cuenta Caja", "cu_caja");
		$edit->cu_caja->size = 18;
		$edit->cu_caja->maxlength=15;
		$edit->cu_caja->rule="trim|callback_chcuentac";
		$edit->cu_caja->append($bcu_caja);
    
    //$edit->sucursal=new inputField("Sucursal","sucursal");
    //$edit->sucursal->size =4;
    //$edit->sucursal->maxlength=2;
    //$edit->sucursal->rule="trim";
    
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
 
		$data['content'] = $edit->output;
    $data['title']   = "<h1>Almacenes</h1>";
    $data["head"]    = script("jquery.pack.js").$this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);
	}
	function _post_insert($do){
		$codigo=$do->get('ubica');
		$nombre=$do->get('ubides');
		logusu('caub',"ALMACEN  $codigo NOMBRE  $nombre CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('ubica');
		$nombre=$do->get('ubides');
		logusu('caub',"ALMACEN $codigo NOMBRE  $nombre  MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('ubica');
		$nombre=$do->get('ubides');
		logusu('caub',"ALMACEN $codigo NOMBRE  $nombre  ELIMINADO ");
	}
	function chexiste($codigo){
		$codigo=$this->input->post('ubica');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM caub WHERE ubica='$codigo'");
		if ($chek > 0){
			$almacen=$this->datasis->dameval("SELECT ubides FROM caub WHERE ubica='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el almacen $almacen");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT ubica FROM caub WHERE ubica!='AJUS' AND ubica!='INFI' ORDER BY ubica DESC");
		echo $ultimo;
	}
	
	function _pre_del($do) {
		$codigo=$do->get('ubica');
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM itsinv WHERE alma='$codigo' AND existen>0");
		if ($chek > 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='El almac&eacute;n contiene pruductos, por ello no puede ser eliminado. Transfiera primero todos los productos de este almac&eacute;n a otro';
			return False;
		}
		return True;
	}
	
	function sugerir(){
		$ultimo=$this->datasis->dameval("SELECT LPAD(hexa,2,0) FROM serie LEFT JOIN caub ON LPAD(ubica,2,0)=LPAD(hexa,2,0) WHERE valor<255 AND ubica IS NULL LIMIT 1");
		echo $ultimo;
	}
}
?>