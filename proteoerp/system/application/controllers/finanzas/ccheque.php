<?php
class ccheque extends Controller {
	var $titp='Cambio efectivo por otros Medios de Pago';
	var $tits='Cambio efectivo por otros Medios de Pago';
	var $url ='finanzas/ccheque/';

	function ccheque(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_id('A00',1);
		//$this->instalar();
	}

	function index(){
		//redirect($this->url.'filteredgrid');
		 if ( !$this->datasis->iscampo('sfpa','deposito') ) {
			$this->db->simple_query('ALTER TABLE sfpa ADD COLUMN deposito CHAR(12) NULL DEFAULT NULL ');
		};
		 if ( !$this->datasis->iscampo('sfpa','id') ) {
			$this->db->simple_query('ALTER TABLE sfpa ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};
		redirect($this->url.'jqdatag');
	}

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
		window.open(\''.base_url().'formatos/ver/CCHEQUE/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});


</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));
		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
	<div class="otros">
	<table id="west-grid">
	<tr><td>
			<a style="width:190px" href="#" id="a1">Imprimir Copia</a>
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

		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud',$param);
	}	
	
	function defgrid( $deployed = false ){
		$i = 1;

		$grid  = new $this->jqdatagrid;

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'    => "'center'",
			'frozen'   => 'true',
			'width'    => 60,
			'editable' => 'false',
			'search'   => 'false'
		));


		$link  = site_url('ajax/buscascli');
		$afterhtml = '<div id=\"aaaaaa\">Nombre <strong>"+ui.item.nombre+" </strong>RIF/CI <strong>"+ui.item.rifci+" </strong><br>Direccion <strong>"+ui.item.direc+"</strong></div>';
		$auto = $grid->autocomplete( $link, 'cod_cli', 'aaaaa', $afterhtml );

		$grid->addField('cod_cli');
		$grid->label('Cliente');
		$grid->params(array(	'width'       => 60,
					'hidden'      => 'true',
					'editable'    => 'true',
					'edittype'    => "'text'",
					'editrules'   => '{ edithidden:true, required:true }',
					'editoptions' => '{'.$auto.'}'
			)
		);
							
		$grid->addField('nombre');
		$grid->label('Nombre Cliente');
		$grid->params(array('width'    => 180,
							'editable' => 'false',
							'edittype' => "'text'"
			)
		);

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array('align'    => "'center'",
							'width'    => 70,
							'editable' => 'false',
							'edittype' => "'text'"
			)
		);

		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array('width'       => 80,
							'search'      => 'true',
							'editable'    => 'true',
							'edittype'    => "'text'",
							'editrules'   => '{ required:true,date:true}',
							'formoptions' => '{ label:"Fecha" }'
			)
		);

		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array('align'    => "'center'",
							'width'         => 30,
							'editable'      => 'true',
							'edittype'      => "'select'",
							'editrules'     => '{ required:true }',
							'editoptions'   => '{ dataUrl: "'.base_url().'ajax/ddtarjeta"}',
							'stype'         => "'text'"
							//'searchoptions' => '{ dataUrl: "ddtarjeta", sopt: ["eq", "ne"]}'
			)
		);

		$grid->addField('num_ref');
		$grid->label('Nro.Documento');
		$grid->params(array('width'       => 100,
							'editable'    => 'true',
							'edittype'    => "'text'",
							'editrules'    => '{required:true}',
							'editoptions' => '{ size:20, maxlength: 12 }',
			)
		);

		$grid->addField('monto');
		$grid->label('Monto');
		$grid->params(array('width'         => 100,
							'editable'      => 'true',
							'align'         => "'right'",
							'edittype'      => "'text'",
							'search'        => 'true',
							'editrules'     => '{ required:true }',
							'editoptions'   => '{ size:10, maxlength: 10 }',
							'formatter'     => "'number'",
							'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
			)
		);

		$grid->addField('banco');
		$grid->label('Banco');
		$grid->params(array('width'         => 40,
							'hidden'        => 'true',
							'editable'      => 'true',
							'edittype'      => "'select'",
							'editrules'     => '{ edithidden:true, required:true }',
							'editoptions'   => '{ dataUrl: "'.base_url().'ajax/ddbanco"}',
							'stype'         => "'tsxt'",
							//'searchoptions' => '{ dataUrl: "ddbanco", sopt: ["eq", "ne"]}'
			)
		);

		$grid->addField('nombanc');
		$grid->label('Nombre del Banco');
		$grid->params(array('width'         => 140,
							'editable'      => 'false',
							'edittype'      => "'text'",
							'search'        => 'true'
			)
		);

		$grid->addField('cajero');
		$grid->label('Cajero');
		$grid->params(array('width'         => 120,
							'hidden'        => 'true',
							'editable'      => 'true',
							'edittype'      => "'select'",
							'editrules'     => '{ edithidden: true, required:true }',
							'editoptions'   => '{ dataUrl: "'.base_url().'ajax/ddcajero"}',
							'stype'         => "'select'",
							'searchoptions' => '{ dataUrl: "'.base_url().'ajax/ddcajero", sopt: ["eq", "ne"]}'
			)
		);


		$grid->addField('nomcajero');
		$grid->label('Nombre Cajero');
		$grid->params(array('width'         => 120,
							'editable'      => 'false',
							'edittype'      => "'text'"
			)
		);

		$grid->addField('us_nombre');
		$grid->label('Nombre de Uusario');
		$grid->params(array('width'     => 140,
							'editable'  => 'false',
							'edittype'  => "'text'",
							'search'    => 'true'
			)
		);

		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array('width'    => 80,
							'search'   => 'false',
							'editable' => 'false',
							'edittype' => "'text'"
			)
		);

		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array('width'     => 60,
							'editable'  => 'false',
							'edittype'  => "'text'",
							'search'    => 'false'
			)
		);

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		//$grid->setToolbar('true, "top"');
		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) {var res = $.parseJSON(a.responseText);
					$.prompt(res.mensaje,{
						submit: function(e,v,m,f){
							window.open(\''.base_url().'formatos/ver/CCHEQUE/\'+res.id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
						}
					});
					return [true, a ];}}'
					);
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");


		#show/hide navigations buttons
		$grid->setAdd(true);                               
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
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



	/**
	* Get data result as json
	*/
	function getdata()
	{

		$filters = $this->input->get_post('filters');
		$mWHERE = array();

		$grid       = $this->jqdatagrid;
		
		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO

		$valor = $this->input->get_post('nombre');
		if ($valor) $mWHERE[] = array('like', 'nombre', $valor, 'both' );

		$valor = $this->input->get_post('numero');
		if( !empty($valor) ) $valor = str_pad($valor, 8, "0", STR_PAD_LEFT);
		if ($valor) $mWHERE[] = array('like', 'numero', $valor, 'after' );

		$valor = $this->input->get_post('fecha');
		if ($valor) $mWHERE[] = array('', 'fecha', $valor, '' );
			
		$valor = $this->input->get_post('tipo');
		if ($valor) $mWHERE[] = array('', 'tipo', $valor );

		$valor = $this->input->get_post('num_ref');
		if ($valor) $mWHERE[] = array('', 'num_ref', $valor );

		$valor = $this->input->get_post('monto');
		if ($valor) $mWHERE[] = array('', 'monto', $valor+0 );
			
		$valor = $this->input->get_post('nombanc');
		if ($valor) $mWHERE[] = array('like', 'nombanc', $valor, 'both' );

		$valor = $this->input->get_post('nomcajero');
		if ($valor) $mWHERE[] = array('like', 'nomcajero', $valor, 'both' );

		$valor = $this->input->get_post('us_nombre');
		if ($valor) $mWHERE[] = array('like', 'us_nombre', $valor, 'both' );

		$response   = $grid->getData('view_ccheque', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;

	}

	/**
	* Put information
	*/
	function setData()
	{
		//$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');

		$data = $_POST;
		unset($data['oper']);
		unset($data['id']);
		$data['cobrador']  = $data['cajero'];
		$data['f_factura'] = $data['fecha'];
		unset($data['cajero']);
		
		if($oper == 'add'){
			if(false == empty($data)){
				$data['tipo_doc'] = 'CC';
				$data['f_factura'] = $data['fecha'];
				$data['usuario'] = $this->secu->usuario();
				$data['estampa'] = date('Ymd');
				$data['hora']    = date('H:i:s');
				$data['numero'] = str_pad($this->datasis->prox_sql('nccheque'), 8, "0", STR_PAD_LEFT);
				$this->db->insert('sfpa', $data);
				$id = $this->db->insert_id();
			}
			logusu('SFPA',"Agrega Cambio de medio de pago por efectivo: id=$id, ".$data['numero'].", monto=".$data['monto']);
			echo "{\"id\":\"$id\",\"mensaje\":\"Registro Agregado\"}";
			return;

		} elseif($oper == 'edit') {
			$data['tipo_doc'] = 'CC';
			$data['f_factura'] = $data['fecha'];
			$data['usuario'] = $this->secu->usuario();
			$data['estampa'] = date('Ymd');
			$data['hora']    = date('H:i:s');
			$this->db->where('id', $id);
			$this->db->update('sfpa', $data);
			logusu('SFPA',"Edita Cambio de medio de pago por efectivo: id=$id, ".$data['numero'].", monto=".$data['monto']);
			echo 'Registro Guardado';
			return;

		} elseif($oper == 'del') {
			$this->db->simple_query("DELETE FROM sfpa WHERE id=$id ");
			logusu('sfpa',"Cambio de Cheque $id ELIMINADO");
			echo "Registro Eliminado";
			return;
		}
	}


	function instalar(){
	
	}

}
