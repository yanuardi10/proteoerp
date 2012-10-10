<?php
class Pamarillas extends Controller {
	var $mModulo='PAMARILLAS';
	var $titp='Modulo PAMARILLAS';
	var $tits='Modulo PAMARILLAS';
	var $url ='ventas/pamarillas/';

	function Pamarillas(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('pamarillas','id') ) {
			$this->db->simple_query('ALTER TABLE pamarillas DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE pamarillas ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE pamarillas ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$bodyscript = '
<script type="text/javascript">
jQuery("#a1").click( function(){
	var id = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\''.base_url().'formatos/ver/PAMARILLAS/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"edocta",   "img"=>"images/pdf_logo.gif",  "alt" => "Formato PDF", "label"=>"Estado de Cuenta"));
		$WestPanel = $grid->deploywestp();

		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor("TITULO1"));

		$param['WestPanel']    = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados']   = $this->datasis->listados('PAMARILLAS', 'JQ');
		$param['otros']       = $this->datasis->otros('PAMARILLAS', 'JQ');
		$param['temas']       = array('proteo','darkness','anexos1');
		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = 'true';

		$grid  = new $this->jqdatagrid;

		//$grid->addField('id');
		//$grid->label('Id');
		//$grid->params(array(
		//	'align'         => "'center'",
		//	'frozen'        => 'true',
		//	'width'         => 40,
		//	'editable'      => 'false',
		//	'search'        => 'false'
		//));


		$grid->addField('estado');
		$grid->label('Estado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 100 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 100 }',
		));


		$grid->addField('rif');
		$grid->label('Rif');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:30, maxlength: 15 }',
		));


		$grid->addField('descrip');
		$grid->label('Descrip');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 200 }',
		));


		$grid->addField('ciudad');
		$grid->label('Ciudad');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 100 }',
		));


		$grid->addField('direc');
		$grid->label('Direc');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 200 }',
		));


		$grid->addField('telf');
		$grid->label('Telf');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 100 }',
		));


		//$grid->addField('file');
		//$grid->label('File');
		//$grid->params(array(
		//	'search'        => 'true',
		//	'editable'      => $editar,
		//	'width'         => 200,
		//	'edittype'      => "'text'",
		//	'editrules'     => '{ required:true}',
		//	'editoptions'   => '{ size:30, maxlength: 50 }',
		//));


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(true);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

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

	/**
	* Busca la data en el Servidor por json
	*/
	function getdata()
	{
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('pamarillas');

		$response   = $grid->getData('pamarillas', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$mcodp  = "id";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM pamarillas WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('pamarillas', $data);
					echo "Registro Agregado";

					logusu('PAMARILLAS',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			//$nuevo  = $data[$mcodp];
			//$anterior = $this->datasis->dameval("SELECT $mcodp FROM pamarillas WHERE id=$id");
			//if ( $nuevo <> $anterior ){
			//	//si no son iguales borra el que existe y cambia
			//	$this->db->query("DELETE FROM pamarillas WHERE $mcodp=?", array($mcodp));
			//	$this->db->query("UPDATE pamarillas SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
			//	$this->db->where("id", $id);
			//	$this->db->update("pamarillas", $data);
			//	logusu('PAMARILLAS',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
			//	echo "Grupo Cambiado/Fusionado en clientes";
			//} else {
				unset($data['id']);
				$this->db->where("id", $id);
				$this->db->update('pamarillas', $data);
				logusu('PAMARILLAS',"Grupo de Cliente  ".$id." MODIFICADO");
				echo "$mcodp Modificado";
			//}

		} elseif($oper == 'del') {
		$meco = $this->datasis->dameval("SELECT $mcodp FROM pamarillas WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM pamarillas WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM pamarillas WHERE id=$id ");
				logusu('PAMARILLAS',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}
}
