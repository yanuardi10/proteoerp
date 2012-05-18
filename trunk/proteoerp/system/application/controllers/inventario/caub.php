<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
class Caub extends validaciones {
	var $data_type = null;
	var $data = null;
	var $titp='Almacenes';
	var $tits='Almacenes';
	var $url ='inventario/caub/';
	
	function caub(){
		parent::Controller(); 

		$this->load->helper('url');
		$this->load->helper('text');
		$this->datasis->modulo_id(307,1);

		$this->load->library("rapyd");
		$this->load->library('jqdatagrid');

		//define ("THISFILE",   APPPATH."controllers/nomina". $this->uri->segment(2).EXT);
	}
 
	function index(){
		$this->datasis->modulo_id(307,1);
		if ( !$this->datasis->iscampo('caub','id') ) {
			$this->db->simple_query('ALTER TABLE caub DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE caub ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE caub ADD UNIQUE INDEX ubica (ubica)');
		}

		if ( !$this->datasis->iscampo('caub','url') ) {
			$this->db->simple_query('ALTER TABLE caub ADD COLUMN url VARCHAR(100)');
		}

		if ( !$this->datasis->iscampo('caub','odbc') ) {
			$this->db->simple_query('ALTER TABLE caub ADD COLUMN odbc VARCHAR(100)');
		}

		$c=$this->datasis->dameval('SELECT COUNT(*) FROM caub WHERE ubica="AJUS"');
		if(!($c>0)) $this->db->simple_query('INSERT IGNORE INTO caub (ubica,ubides,gasto,invfis) VALUES ("AJUS","AJUSTES","S","N")');
		$this->db->simple_query('UPDATE caub SET ubides="AJUSTES", gasto="S",invfis="N" WHERE  ubica="AJUS" ');
		
		$c=$this->datasis->dameval("SELECT COUNT(*) FROM caub WHERE ubica='INFI'");
		if(!($c>0)) $this->db->simple_query("INSERT IGNORE INTO caub (ubica,ubides,gasto,invfis) VALUES ('INFI','INVENTARIO FISICO','S','S')");
		$this->db->simple_query("UPDATE caub SET ubides='INVENTARIO FISICO', gasto='S',invfis='S' WHERE ubica='INFI'");
		
		$c=$this->datasis->dameval("SELECT COUNT(*) FROM caub WHERE ubica='PEDI'");
		if(!($c>0))	$this->db->simple_query("INSERT IGNORE INTO caub (ubica,ubides,gasto,invfis) VALUES ('PEDI','PEDIDOS','N','N')");
		$this->db->simple_query("UPDATE caub SET ubides='PEDIDOS', gasto='N',invfis='N' WHERE ubica='PEDI'");
		
		$this->db->simple_query("ALTER TABLE `caub`  ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT FIRST,  DROP PRIMARY KEY,  ADD PRIMARY KEY ( `id`)");
		
		//redirect("inventario/caub/caubextjs");
		redirect('inventario/caub/jqdatag');

    }
 
 	function jqdatag(){

		$grid = $this->defgrid();
		$param['grid'] = $grid->deploy();

		$bodyscript = '
<script type="text/javascript">
</script>
';

		$funciones = 'jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid({ondblClickRow: function(id){ alert("id="+id); }});';

		$param['listados'] = $this->datasis->listados('CAUB', 'JQ');
		//$param['otros']    = $this->datasis->otros('CAUB', 'JQ');


		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));
		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
	<div class="otros">
	<table id="west-grid">
	<tr><td><div class="tema1">
		<table id="listados"></table> 
		</div>
	</td></tr>
	<tr><td>
		<table id="otros"></table> 
	</td></tr>
	</table>
	</div>
</div> <!-- #LeftPane -->
';

		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->
';


		$param['WestPanel']  = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['funciones'] = $funciones;
		//$param['bodyscript'] = $bodyscript;
		$param['tema1'] = 'darkness';
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud',$param);
	
	}	

	function defgrid( $deployed = false ){
		$i = 1;
		$link  = site_url('ajax/buscacpla');

		$grid  = new $this->jqdatagrid;

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array('align' => "'center'",
							'frozen' => 'true',
							'width' => 50,
							'editable' => 'false',
							'search' => 'false'
			)
		);

		$grid->addField('ubica');
		$grid->label('Codigo');
		$grid->params(array('width' => 60,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:5, maxlength: 4 }'
			)
		);

		$grid->addField('ubides');
		$grid->label('Nombre');
		$grid->params(array('width' => 180,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('sucursal');
		$grid->label('Sucursal');
		$grid->params(array('width' => 100,
							'editable' => 'true',
							'edittype' => "'select'",
							'editoptions'   => '{ dataUrl: "ddsucu"}',
							'stype' => "'select'",
							'searchoptions' => '{ dataUrl: "ddsucu", sopt: ["eq", "ne"]}',
							'search' => 'false'
			)
		);

		$grid->addField('gasto');
		$grid->label('Gasto');
		$grid->params(array('width' => 40,
							'editable' => 'true',
							'edittype' => "'select'",
							'search' => 'false',
							'editoptions' => '{value: {"S":"Si", "N":"No"} }'
			)
		);

		$grid->addField('invfis');
		$grid->label('Inv.F');
		$grid->params(array('width' => 40,
							'editable' => 'true',
							'edittype' => "'select'",
							'search' => 'false',
							'editoptions' => '{value: {"S":"Si", "N":"No"} }'
			)
		);

		$grid->addField('url');
		$grid->label('URL');
		$grid->params(array('width' => 200,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('odbc');
		$grid->label('ODBC');
		$grid->params(array('width' => 200,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('odbc');
		$grid->label('ODBC');
		$grid->params(array('width' => 200,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('cu_cost');
		$grid->label('Cta.Costo');
		$grid->params(array('width' => 70,
							'frozen' => 'true',
							'editable' => 'true',
							'edittype' => "'text'",
							'editoptions' => '{'.$grid->autocomplete($link, 'cu_cost','cucucu','<div id=\"cucucu\"><b>"+ui.item.descrip+"</b></div>').'}',
							'search' => 'false'
			)
		);

		$grid->addField('cu_caja');
		$grid->label('Cta.Caja');
		$grid->params(array('width' => 70,
							'frozen' => 'true',
							'editable' => 'true',
							'edittype' => "'text'",
							'editoptions' => '{'.$grid->autocomplete($link, 'cu_caja','cacaca','<div id=\"cacaca\"><b>"+ui.item.descrip+"</b></div>').'}',
							'search' => 'false'
			)
		);


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('310');
		$grid->setTitle('Almacenes');
		$grid->setfilterToolbar(false);
		//$grid->setToolbar('true, "top"');
		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:350, closeOnEscape: true, top: 50, left:20, recreateForm:true ');
		$grid->setFormOptionsA('closeAfterAdd: true, mtype: "POST", width: 520, height:350, closeOnEscape: true, top: 50, left:20, recreateForm:true ');


		#show/hide navigations buttons
		$grid->setAdd(true);                               
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(false);
		$grid->setRowNum(30);
            
		$grid->setShrinkToFit('false');
            
		#export buttons
		//$grid->setPdf(true,array('title' => 'Test pdf'));

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}


	function ddsucu(){
		$mSQL = "SELECT codigo, CONCAT(codigo,' ',sucursal) sucursal  FROM sucu ORDER BY codigo";
		echo $this->datasis->llenaopciones($mSQL, true);
	}


	/**
	* Get data result as json
	*/
	function getData()
	{
		$grid       = $this->jqdatagrid;
		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('bcaj');
		$response   = $grid->getData('caub', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}
	

	/**
	* Put information
	*/
	function setData()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$codigo = $this->input->post('ubica');
		
		$data = $_POST;

		unset($data['oper']);
		unset($data['id']);

		if($oper == 'add'){
			if(false == empty($data)){
				$this->db->insert('caub', $data);
			}
			echo '';
			return;

		} elseif($oper == 'edit') {
			unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('caub', $data);
			return;
		} elseif($oper == 'del') {
			$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM itsinv WHERE alma='$codigo' AND existen>0");
			if ($chek > 0){
				echo " El almacen no fuede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM caub WHERE id=$id ");
				logusu('caub',"Almacen $codigo ELIMINADO");
				echo "{ success: true, message: 'Almacen Eliminado'}";
			}
		};
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
			//memowrite($_POST["_gt_json"],"caubjson");
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
					//memowrite($_POST["_gt_json"],"caubjsondel");
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
			memowrite($_POST["_gt_json"],"caubjsonelse");
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


	function gridlis( $modulo ){
		$i = 1;

		$grid  = new $this->jqdatagrid;

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array('align' => "'center'",
							'frozen' => 'true',
							'width' => 50,
							'editable' => 'false',
							'search' => 'false'
			)
		);

		$grid->addField('ubica');
		$grid->label('Codigo');
		$grid->params(array('width' => 60,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:5, maxlength: 4 }'
			)
		);

		$grid->addField('ubides');
		$grid->label('Nombre');
		$grid->params(array('width' => 180,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('sucursal');
		$grid->label('Sucursal');
		$grid->params(array('width' => 100,
							'editable' => 'true',
							'edittype' => "'select'",
							'editoptions'   => '{ dataUrl: "ddsucu"}',
							'stype' => "'select'",
							'searchoptions' => '{ dataUrl: "ddsucu", sopt: ["eq", "ne"]}',
							'search' => 'false'
			)
		);

		$grid->addField('gasto');
		$grid->label('Gasto');
		$grid->params(array('width' => 40,
							'editable' => 'true',
							'edittype' => "'select'",
							'search' => 'false',
							'editoptions' => '{value: {"S":"Si", "N":"No"} }'
			)
		);

		$grid->addField('invfis');
		$grid->label('Inv.F');
		$grid->params(array('width' => 40,
							'editable' => 'true',
							'edittype' => "'select'",
							'search' => 'false',
							'editoptions' => '{value: {"S":"Si", "N":"No"} }'
			)
		);

		$grid->addField('url');
		$grid->label('URL');
		$grid->params(array('width' => 200,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('odbc');
		$grid->label('ODBC');
		$grid->params(array('width' => 200,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('odbc');
		$grid->label('ODBC');
		$grid->params(array('width' => 200,
							'editable' => 'true',
							'edittype' => "'text'",
							'search' => 'false',
							'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('cu_cost');
		$grid->label('Cta.Costo');
		$grid->params(array('width' => 70,
							'frozen' => 'true',
							'editable' => 'true',
							'edittype' => "'text'",
							'editoptions' => '{
						"dataInit":function(el){
							setTimeout(function(){
								if(jQuery.ui) { 
									if(jQuery.ui.autocomplete){
										jQuery(el).autocomplete({
											"appendTo":"body",
											"disabled":false,
											"delay":300,
											"minLength":1,
											"select": function(event, ui) { 
												$("#aaaaaa").remove();
												$("#cu_cost").after("<div id=\"aaaaaa\"><strong>"+ui.item.descrip+"</strong></div>"); 
											},
											"source":function (request, response){
												request.acelem = "cu_cost";
												request.oper = "autocomplete";
												$.ajax({
													url: "'.$link.'",
													dataType: "json",
													data: request,
													type: "POST",
													error: function(res, status) { alert(res.status+" : "+res.statusText+". Status: "+status);},
													success: function( data ) { response( data );	}
												});
											}
										});
										jQuery(el).autocomplete("widget").css("font-size","11px");
									} 
								} else { alert("Falta jQuery UI") }
							},200);
						}}',
							'search' => 'false'
			)
		);

		$grid->addField('cu_caja');
		$grid->label('Cta.Caja');
		$grid->params(array('width' => 70,
							'frozen' => 'true',
							'editable' => 'true',
							'edittype' => "'text'",
							'editoptions' => '{
						"dataInit":function(el){
							setTimeout(function(){
								if(jQuery.ui) { 
									if(jQuery.ui.autocomplete){
										jQuery(el).autocomplete({
											"appendTo":"body",
											"disabled":false,
											"delay":300,
											"minLength":1,
											"select": function(event, ui) { 
												$("#bbbbbb").remove();
												$("#cu_caja").after("<div id=\"bbbbbb\"><strong>"+ui.item.descrip+"</strong></div>"); 
											},
											"source":function (request, response){
												request.acelem = "cu_caja";
												request.oper = "autocomplete";
												$.ajax({
													url: "'.$link.'",
													dataType: "json",
													data: request,
													type: "POST",
													error: function(res, status) { alert(res.status+" : "+res.statusText+". Status: "+status);},
													success: function( data ) { response( data );	}
												});
											}
										});
										jQuery(el).autocomplete("widget").css("font-size","11px");
									} 
								} else { alert("Falta jQuery UI") }
							},200);
						}}',
							'search' => 'false'
			)
		);


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('340');
		$grid->setTitle('Almacenes');
		$grid->setfilterToolbar(true);
		//$grid->setToolbar('true, "top"');
		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:350, closeOnEscape: true, top: 50, left:20, recreateForm:true ');
		$grid->setFormOptionsA('closeAfterAdd: true, mtype: "POST", width: 520, height:350, closeOnEscape: true, top: 50, left:20, recreateForm:true ');


		#show/hide navigations buttons
		$grid->setAdd(true);                               
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(false);
		$grid->setRowNum(30);
            
		$grid->setShrinkToFit('false');
            
		#export buttons
		//$grid->setPdf(true,array('title' => 'Test pdf'));

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}






	
	/*
	 * INICIO EXTJS
	 * 
	 * */
	 
	 function grid(){
		$this->tabla='caub';
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"ubides","direction":"ASC"},{"property":"ubides","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);
		
		$this->db->_protect_identifiers=false;
		$this->db->select(array("id","ubica","ubides","IF(invfis='S','SI',IF(invfis='N','NO',invfis)) invfis","IF(gasto='S','SI',IF(gasto='N','NO',gasto)) gasto","sucursal","cu_cost","cu_caja","url","odbc"));
		$this->db->from($this->tabla);
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 
		
		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all($this->tabla);

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}

	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];
		$codigo = $campos['ubica'];

		if ( !empty($codigo) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM caub WHERE ubica='$codigo'") == 0)
			{
				$mSQL = $this->db->insert_string("caub", $campos );
				$this->db->query($mSQL);
				$id=$this->db->insert_id();
				logusu('caub',"ALMACEN $codigo CREADO id $id");
				echo "{ success: true, message: 'Almacen Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe una almacen con ese codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Falta el campo codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$ubica = $campos['ubica'];
		$id    = $campos['id'];
		unset($campos['ubica']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("caub", $campos,"id=".$id );
		$this->db->simple_query($mSQL);
		logusu('caub',"ALMACEN $ubica ID ".$id." MODIFICADO");
		echo "{ success: true, message: 'Almacen Modificado -> ".$ubica."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$codigo = $campos['ubica'];
		$chek =  $this->datasis->dameval("SELECT COUNT(*) FROM itsinv WHERE alma='$codigo' AND existen>0");

		if ($chek > 0){
			echo "{ success: false, message: 'El almacen no fuede ser eliminado'}";
		} else {
			$this->db->simple_query("DELETE FROM caub WHERE ubica='$codigo'");
			logusu('caub',"Almacen $codigo ELIMINADO");
			echo "{ success: true, message: 'Almacen Eliminado'}";
		}
	}
	 
	 function caubextjs(){
		$encabeza='ALMACENES';
		$listados= $this->datasis->listados('caub');
		$otros='';

		$titulow='ALMACENES';
		$urlajax = 'inventario/caub/';
		$variables = "
		var mcuenta='';
		var mcucaja='';
		var msucursal='';
		";

		$sn="['S', 'SI'],['N','NO']";

		$funciones = "";
		
		$valida = "
		{ type: 'length', field: 'ubica',   min: 1 },
		{ type: 'length', field: 'ubides', min: 1 }
		";
		
		$columnas = "
		{ header: 'C&oacute;digo',  width:  50, sortable: true, dataIndex: 'ubica',   field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Nombre',         width: 200, sortable: true, dataIndex: 'ubides',  field: { type: 'textfield' }, filter: { type: 'string' }}, 
		{ header: 'Gasto',          width:  80, sortable: true, dataIndex: 'gasto',   field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Inv. Fisico',    width:  80, sortable: true, dataIndex: 'invfis',  field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Sucursal',       width: 100, sortable: true, dataIndex: 'sucursal',field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'URL',            width: 120, sortable: true, dataIndex: 'url',     field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'ODBC',           width:  90, sortable: true, dataIndex: 'odbc',    field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cuenta Almacen', width:  90, sortable: true, dataIndex: 'cu_cost', field: { type: 'textfield' }, filter: { type: 'string' }},
		{ header: 'Cuenta Caja',    width:  90, sortable: true, dataIndex: 'cu_caja', field: { type: 'textfield' }, filter: { type: 'string' }},
		";

		$campos = "'id', 'ubica', 'ubides', 'gasto', 'invfis', 'sucursal', 'sucursal', 'cu_cost', 'cu_caja', 'url', 'odbc'";
		
		$camposforma = "
							{
							xtype:'fieldset',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield'  ,fieldLabel: 'C&oacute;digo',           name: 'ubica'   ,width:120, allowBlank: false ,id: 'ubica' },
									{ xtype: 'textfield'  ,fieldLabel: 'Nombre',                  name: 'ubides'  ,width:280, allowBlank: false  },
									{ xtype: 'combo'      ,fieldLabel: 'Gasto',                   name: 'gasto'   ,width:130, allowBlank: false ,store: [".$sn."] },
									{ xtype: 'combo'      ,fieldLabel: 'Inventario F&itilde;sico',name: 'invfis'  ,width:270, allowBlank: false ,store: [".$sn."], labelWidth:210 },
									{ xtype: 'combo'      ,fieldLabel: 'Sucursal',                name: 'sucursal',width:400, allowBlank: true  ,store: sucuStore, id: 'sucursal', mode: 'remote', hideTrigger: true, typeAhead: true, forceSelection: true, valueField: 'item', displayField: 'valor'},
								]
							},{
							xtype:'fieldset',
							title: 'Conexiones Remotas',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield'  ,fieldLabel: 'Sitio WEB', name: 'url',  width:400, allowBlank: false },
									{ xtype: 'textfield'  ,fieldLabel: 'ODBC',      name: 'odbc', width:400, allowBlank: false },
								]
							},{
							xtype:'fieldset',
							title: 'Cuentas Contables',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype:'fieldset', labelWidth:120 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'combo'      ,fieldLabel: 'Cuenta Almacen'   ,name: 'cu_cost',width:400, allowBlank: true  ,store: cplaStore, id: 'cu_cost', mode: 'remote', hideTrigger: true, typeAhead: true, forceSelection: true, valueField: 'item', displayField: 'valor'},
									{ xtype: 'combo'      ,fieldLabel: 'Cuenta Caja'      ,name: 'cu_caja' ,width:400, allowBlank: true  ,store: cplaStore, id: 'cu_caja', mode: 'remote', hideTrigger: true, typeAhead: true, forceSelection: true, valueField: 'item', displayField: 'valor'},
								]
							}
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 380,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						if (registro) {
							mcuenta  = registro.data.cuenta;
							cplaStore.proxy.extraParams.cuenta  = mcuenta  ;
							cplaStore.load({ params: { 'cuenta': registro.data.cuenta,  'origen': 'beforeform' } });
							msucursal  = registro.data.sucursal;
							sucuStore.proxy.extraParams.sucursal  = msucursal  ;
							sucuStore.load({ params: { 'sucursal': registro.data.sucursal,  'origen': 'beforeform' } });
							form.loadRecord(registro);
							form.findField('ubica').setReadOnly(true);
						} else {
							form.findField('ubica').setReadOnly(false);
						}
					}
				}
";

		$stores = "
		var cplaStore = new Ext.data.Store({
			fields: [ 'item', 'valor'],
			autoLoad: false,
			autoSync: false,
			pageSize: 50,
			pruneModifiedRecords: true,
			totalProperty: 'results',
			proxy: {
				type: 'ajax',
				url : urlApp + 'contabilidad/cpla/cplabusca',
				extraParams: {  'cuenta': mcuenta, 'origen': 'store' },
				reader: {type: 'json',totalProperty: 'results',root: 'data'}
			},
			method: 'POST'
		});
		
		var sucuStore = new Ext.data.Store({
			fields: [ 'item', 'valor'],
			autoLoad: false,
			autoSync: false,
			pageSize: 50,
			pruneModifiedRecords: true,
			totalProperty: 'results',
			proxy: {
				type: 'ajax',
				url : urlApp + 'supervisor/sucu/sucubusca',
				extraParams: {  'cuenta': msucursal, 'origen': 'store' },
				reader: {type: 'json',totalProperty: 'results',root: 'data'}
			},
			method: 'POST'
		});
		";

		$features = "features: [{ ftype: 'filters', encode: 'json', local: false }],";

		$agrupar = "remoteSort: true,";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['stores']      = $stores;
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;
		$data['agrupar']     = $agrupar;
		
		$data['title']  = heading($encabeza);
		$this->load->view('extjs/extjsven',$data);

	}
}
?>
