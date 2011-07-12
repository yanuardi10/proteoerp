<?php
class Marc extends Controller{
	var $genesal=true;

	function marc(){
		parent::Controller(); 
		$this->load->library('rapyd');
		//$this->datasis->modulo_id('30B',1);
	}

	function index(){
		redirect('inventario/marc/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$grid = new DataGrid('Lista de Marcas', 'marc');
		$grid->order_by('marca','ASC');
		$grid->per_page = 60;
		$grid->column_sigma('Marca',"marca", "", "width:240, editor: { type: 'text'} ");
		$grid->column_sigma('Productos',"produ", "float", "align: 'right', width:90 ");
		$mtool = '';

		//$sigmaA     = $grid->sigmaDsConfig();
		$sigmaA     = $grid->sigmaDsConfig("marc","marca","inventario/marc/");
		$dsOption   = $sigmaA["dsOption"];
		$grupver    = "
function marcver(value, record, columnObj, grid, colNo, rowNo){
	var url = '';
	url = '<a href=\"#\" onclick=\"window.open(\'".base_url()."inventario/marc/dataedit/show/'+value+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
	url = url +value+'</a>';
	return url;	
}
";
		$colsOption = $sigmaA["colsOption"];
		$gridOption = $sigmaA["gridOption"];
		$gridGuarda = "
function guardar(value, oldValue, record, col, grid) {
	var murl='';
	murl = '".base_url()."/inventario/marc/modifica/'+encodeURIComponent(oldValue)+'/'+col.id+'/'+encodeURIComponent(value);
	if ( value != oldValue ) { $.ajax({url: murl,context: document.body}); }
};
";

	      $gridGo = "
var mygrid=new Sigma.Grid(gridOption);
mygrid.toolbarContent = 'nav | pagesize | reload | add del save | print | filter | state';
mygrid.saveURL = '".base_url()."inventario/marc/controlador',
mygrid.width = 360;
mygrid.height = 400;
mygrid.toolbarContent = 'nav | pagesize | reload | add del save | print | filter | state';
Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );
";		
		$SigmaCont = "<center><div id=\"grid1_container\" style=\"width:400px;height:400px;\"></div></center>";
		$grid->add("inventario/marc/dataedit/create");
		$grid->build('datagridSG');

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
		$data['title']   = heading('Marcas');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	// sigma grid
	function controlador(){
		if (isset($_POST["_gt_json"]) ) {
			$json=json_decode(stripslashes($_POST["_gt_json"]));
			if($json->{'action'} == 'load') {
				$pageNo   = $json->{'pageInfo'}->{'pageNum'};
				$pageSize = $json->{'pageInfo'}->{'pageSize'};
				$filter = '';

				if(isset($json->{'sortInfo'}[0]->{'columnId'})){
					$sortField = $json->{'sortInfo'}[0]->{'columnId'};
				} else {
					$sortField = "marca";
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
				$mSQL = "SELECT count(*) FROM marc WHERE $filter marca IS NOT NULL";
				$totalRec = $this->datasis->dameval($mSQL);
 
 
				//make sure pageNo is inbound
				if($pageNo<1||$pageNo>ceil(($totalRec/$pageSize))){
					$pageNo = 1;
				}
 
				$mSQL = "SELECT marca, (SELECT count(*) FROM sinv b WHERE b.marca=a.marca) produ ";
				$mSQL .= "FROM marc a WHERE $filter marca IS NOT NULL ORDER BY ".$sortField." ".$sortOrder." LIMIT ".($pageNo - 1)*$pageSize.", ".$pageSize;

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

			}else if($json->{'action'} == 'save'){
				
				for ($i = 0; $i < count($json->{'insertedRecords'}); $i++) {
					$marca = $json->{'insertedRecords'}[$i]->{'marca'};
					$mSQL = "INSERT IGNORE INTO marc SET marca='".addslashes($marca)."'";
					$this->db->simple_query($mSQL);
				}
				for ($i = 0; $i < count($json->{'deletedRecords'}); $i++) {
					$marca = $json->{'deletedRecords'}[$i]->{'marca'};
					$mSQL = "SELECT COUNT(*) FROM sinv WHERE marca='".addslashes($marca)."'";
					if ( $this->datasis->dameval($mSQL) == 0 ) {
						$mSQL = "DELETE FROM marc WHERE marca='".addslashes($marca)."'";
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
		
		// Si ya exsite se borra
		$mSQL = "SELECT COUNT(*) FROM marc WHERE marca='".addslashes($valor)."' ";
		if ( $this->datasis->dameval($mSQL) == 0 ) {
			$mSQL = "UPDATE marc SET ".$campo."='".addslashes($valor)."' WHERE marca='".addslashes($grupo)."' ";
			$this->db->simple_query($mSQL);
		};
		$mSQL = "UPDATE sinv SET marca='".addslashes($valor)."' WHERE marca='".addslashes($grupo)."' ";
		$this->db->simple_query($mSQL);
		
		echo "$valor $campo $grupo";
	}



	function dataedit(){
		$this->rapyd->load('dataedit');
		$edit = new DataEdit('Marcas','marc');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->back_url = site_url('inventario/marc/filteredgrid');

		$edit->marca =  new inputField('Marca', 'marca');
		$edit->marca->size = 15;
		$edit->marca->maxlength=30;
		$edit->marca->rule = 'trim|strtoupper|required|callback_test';

		$edit->buttons('modify','save','undo','delete','back');
		$edit->build();

		if($this->genesal){
			$data['content'] = $edit->output;
			$data['title']   = heading('Marca');
			$data['head']    = $this->rapyd->get_head();
			$this->load->view('view_ventanas', $data);
		}else{
			echo $edit->error_string;
		}
	}

	function test($marca){
		//$this->validation->set_message('test', "No quiero que pases");
		//return FALSE;
		return true;
	}

	function exterin(){
		$_POST['marca']='Algunamarca';
		$this->genesal=false;
		$this->dataedit();
	}

	function _post_insert($do){
		$marca=$do->get('marca');
		logusu('marc',"MARCA $marca CREADA");
	}

	function _post_update($do){
		$marca=$do->get('marca');
		logusu('marc',"MARCA $marca MODIFICADA");
	}

	function _post_delete($do){
		$marca=$do->get('marca');
		logusu('marc',"MARCA $marca ELIMINADA");
	}
}
