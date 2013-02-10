<?php
//tbanco
class Tban extends Controller {
	var $mModulo='TBAN';
	var $titp='Tabla de Bancos';
	var $tits='Tabla de Bancos';
	var $url ='finanzas/tban/';

	function Tban(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre('TBAN', $ventana=0 );
	}

	function index(){
		$this->instalar();
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grid'] = $grid->deploy();

		$bodyscript = '
<script type="text/javascript">
$(function() {
	$( "input:submit, a, button", ".otros" ).button();
});

jQuery("#a1").click( function(){
	var id = jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grid']['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\'/proteoerp/formatos/ver/TBAN/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
<div class="anexos">

<table id="west-grid" align="center">
	<tr>
		<td><div class="tema1"><table id="listados"></table></div></td>
	</tr>
	<tr>
		<td><div class="tema1"><table id="otros"></table></div></td>
	</tr>
</table>

<table id="west-grid" align="center">
	<tr>
		<td></td>
	</tr>
</table>
</div>
'.
//		<td><a style="width:190px" href="#" id="a1">Imprimir Copia</a></td>
'</div> <!-- #LeftPane -->
';

		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->
';
		$param['WestPanel']  = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados'] = $this->datasis->listados('TBAN', 'JQ');
		$param['otros']    = $this->datasis->otros('TBAN', 'JQ');
		$param['tema1']     = 'darkness';
		$param['anexos']    = 'anexos1';
		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "true";

		$grid  = new $this->jqdatagrid;

		$grid->addField('cod_banc');
		$grid->label('Cod');
		$grid->params(array(
			'align'       => "'center'",
			'search'      => 'true',
			'editable'    => $editar,
			'width'       => 40,
			'edittype'    => "'text'",
			'editoptions' => '{ size:4, maxlength: 3 }',
			'formoptions' => '{ label:"Codigo" }'
		));

		$grid->addField('nomb_banc');
		$grid->label('Banco');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editoptions' => '{ size:30, maxlength: 30 }'
		));

		$grid->addField('tipotra');
		$grid->label('Tipo');
		$grid->params(array(
			'align'       => "'center'",
			'width'       => 40,
			'editable'    => 'true',
			'edittype'    => "'select'",
			'search'      => 'false',
			'editoptions' => '{value: {"NC":"Nota de Credito", "DE":"DEPOSITO"} }'
		));

		$grid->addField('formaca');
		$grid->label('Abono');
		$grid->params(array(
			'width'       => 40,
			'editable'    => 'true',
			'edittype'    => "'select'",
			'search'      => 'false',
			'editoptions' => '{value: {"BRUTO":"BRUTO", "NETO":"NETO"} }'
		));

		$grid->addField('comitd');
		$grid->label('Com. TD');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions' => '{ label:"Comision TD" }'
		));

		$grid->addField('comitc');
		$grid->label('Com. TC');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2, align:"right" }',
			'formoptions' => '{ label:"Comision TC", align:"right" }'
		));

		$grid->addField('impuesto');
		$grid->label('I.S.L.R.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('debito');
		$grid->label('I.D.B.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('url');
		$grid->label('Url');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 180,
			'edittype'      => "'text'",
			'editoptions'   => '{ size:30, maxlength: 60 }'
		));

		$grid->addField('formato');
		$grid->label('Formato de Cheque');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 180,
			'edittype'      => "'text'",
			'editoptions'   => '{ size:30, maxlength: 60 }'
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'       => "'center'",
			'frozen'      => 'true',
			'width'       => 30,
			'editable'    => 'false',
			'search'      => 'false',
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 440, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 440, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
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
	function getdata(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('tban');
		$response   = $grid->getData('tban', array(array()), array(), false, $mWHERE, 'cod_banc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');

		$cod_banc = "";
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$this->db->insert('tban', $data);
			}
			echo "Registro Agregado";

		} elseif($oper == 'edit') {
			$cod_banc = $this->datasis->dameval("SELECT cod_banc FROM tban WHERE id=$id");
			unset($data['cod_banc']);
			$this->db->where('id', $id);
			$this->db->update('tban', $data);
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			$cod_banc = $this->datasis->dameval("SELECT cod_banc FROM tban WHERE id=$id");
			$check  =  $this->datasis->dameval("SELECT COUNT(*) FROM banc WHERE tbanco='$cod_banc' ");
			$check +=  $this->datasis->dameval("SELECT COUNT(*) FROM sfpa WHERE banco='$cod_banc' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM tban WHERE id=$id ");
				logusu('tban',"Registro tabla de bancos ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	function instalar(){
		$campos=$this->db->list_fields('tban');

		if(!in_array('formato',$campos)){
			$mSQL="ALTER TABLE `tban` ADD COLUMN `formato` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Formato de cheque';";
			$this->db->simple_query($mSQL);
			$mSQL="UPDATE tban SET formato=CONCAT('CHEQUE',cod_banc) WHERE formato IS NULL";
			$this->db->simple_query($mSQL);
		}

		if(!in_array('id',$campos)){
			$this->db->simple_query('ALTER TABLE tban DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE tban ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE tban ADD UNIQUE INDEX cod_banc (cod_banc)');
		}

	}
}
