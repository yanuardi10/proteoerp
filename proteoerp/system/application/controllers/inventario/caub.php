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
		$grid->params(array(
				'align'    => "'center'",
				'frozen'   => 'true',
				'width'    => 50,
				'editable' => 'false',
				'search'   => 'false'
			)
		);

		$grid->addField('ubica');
		$grid->label('Codigo');
		$grid->params(array(
				'width'       => 60,
				'editable'    => 'true',
				'edittype'    => "'text'",
				'search'      => 'false',
				'editoptions' => '{ size:5, maxlength: 4 }'
			)
		);

		$grid->addField('ubides');
		$grid->label('Nombre');
		$grid->params(array(
				'width'       => 180,
				'editable'    => 'true',
				'edittype'    => "'text'",
				'search'      => 'false',
				'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('sucursal');
		$grid->label('Sucursal');
		$grid->params(array(
				'width'         => 100,
				'editable'      => 'true',
				'edittype'      => "'select'",
				'editoptions'   => '{ dataUrl: "'.base_url().'ajax/ddsucu"}',
				'stype'         => "'select'",
				'searchoptions' => '{ dataUrl: "'.base_url().'ajax/ddsucu", sopt: ["eq", "ne"]}',
				'search'        => 'false'
			)
		);

		$grid->addField('gasto');
		$grid->label('Gasto');
		$grid->params(array(
				'width'       => 40,
				'editable'    => 'true',
				'edittype'    => "'select'",
				'search'      => 'false',
				'editoptions' => '{value: {"S":"Si", "N":"No"} }'
			)
		);

		$grid->addField('invfis');
		$grid->label('Inv.F');
		$grid->params(array(
				'width'       => 40,
				'editable'    => 'true',
				'edittype'    => "'select'",
				'search'      => 'false',
				'editoptions' => '{value: {"S":"Si", "N":"No"} }'
			)
		);

		$grid->addField('url');
		$grid->label('URL');
		$grid->params(array(
				'width'       => 200,
				'editable'    => 'true',
				'edittype'    => "'text'",
				'search'      => 'false',
				'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('odbc');
		$grid->label('ODBC');
		$grid->params(array(
				'width'       => 200,
				'editable'    => 'true',
				'edittype'    => "'text'",
				'search'      => 'false',
				'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('odbc');
		$grid->label('ODBC');
		$grid->params(array(
				'width'       => 200,
				'editable'    => 'true',
				'edittype'    => "'text'",
				'search'      => 'false',
				'editoptions' => '{ size:30, maxlength: 50 }'
			)
		);

		$grid->addField('cu_cost');
		$grid->label('Cta.Costo');
		$grid->params(array(
				'width'       => 70,
				'frozen'      => 'true',
				'editable'    => 'true',
				'edittype'    => "'text'",
				'editoptions' => '{'.$grid->autocomplete($link, 'cu_cost','cucucu','<div id=\"cucucu\"><b>"+ui.item.descrip+"</b></div>').'}',
				'search'      => 'false'
			)
		);

		$grid->addField('cu_caja');
		$grid->label('Cta.Caja');
		$grid->params(array(
				'width'       => 70,
				'frozen'      => 'true',
				'editable'    => 'true',
				'edittype'    => "'text'",
				'editoptions' => '{'.$grid->autocomplete($link, 'cu_caja','cacaca','<div id=\"cacaca\"><b>"+ui.item.descrip+"</b></div>').'}',
				'search'      => 'false'
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
/*
	function ddsucu(){
		$mSQL = "SELECT codigo, CONCAT(codigo,' ',sucursal) sucursal  FROM sucu ORDER BY codigo";
		echo $this->datasis->llenaopciones($mSQL, true);
	}
*/
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
}
?>
