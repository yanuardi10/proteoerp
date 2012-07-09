<?php
class Chgara extends Controller {
	var $mModulo='CHGARA';
	var $titp='Cheques en Garantia';
	var $tits='Cheques en Garantia';
	var $url ='finanzas/chgara/';

	function Chgara(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		if ( !$this->datasis->iscampo('chgara','enviado') ) {
			$this->db->simple_query('ALTER TABLE chgara ADD COLUMN enviado DATE NULL AFTER deposito');
		};
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
$(function() {
	$( "input:submit, a, button", ".otros" ).button();
});

jQuery("#listado").click( function(){
	window.open(\''.base_url().'reportes/ver/CHGARA/\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
});


$( "#depositar" ).click(function() {
	var grid = jQuery("#newapi'.$param['grids'][0]['gridname'].'");
	var s = grid.getGridParam(\'selarrrow\');
	if(s.length){
		meco = sumamonto(0);
		$.prompt( "<h1>Enviar a Depositar ?</h1>", {
			buttons: { Guardar: true, Cancelar: false },
			submit: function(e,v,m,f){
				if (v){
					$.get("'.base_url().$this->url.'chenvia/"+meco,
					function(data){
						alert(data);
						grid.trigger("reloadGrid");
					});
				}
			}
		});
	} else {
		$.prompt("<h1>Seleccione los Cheques</h1>");
	}
});


$( "#cobrados" ).click(function() {
	var grid = jQuery("#newapi'.$param['grids'][0]['gridname'].'");
	var s = grid.getGridParam(\'selarrrow\');
	if(s.length){
		meco = sumamonto(0);
		$.prompt( "<h1>Marcar como Cobrado?</h1>Marca solo los cheques que fueron previamente Enviados al Cobro", {
			buttons: { Guardar: true, Cancelar: false },
			submit: function(e,v,m,f){
				if (v){
					$.get("'.base_url().$this->url.'chcobrados/"+meco,
					function(data){
						alert(data);
						grid.trigger("reloadGrid");
					});
				}
			}
		});
	} else {
		$.prompt("<h1>Seleccione los Cheques</h1>");
	}
});


$( "#devueltos" ).click(function() {
	var grid = jQuery("#newapi'.$param['grids'][0]['gridname'].'");
	var s = grid.getGridParam(\'selarrrow\');
	if(s.length){
		meco = sumamonto(0);
		$.prompt( "<h1>Marcar los cheques Devueltos ?</h1>Marca solo los cheques que fueron previamente Enviados al Cobro", {
			buttons: { Guardar: true, Cancelar: false },
			submit: function(e,v,m,f){
				if (v){
					$.get("'.base_url().$this->url.'chdevueltos/"+meco,
					function(data){
						alert(data);
						grid.trigger("reloadGrid");
					});
				}
			}
		});
	} else {
		$.prompt("<h1>Seleccione los Cheques</h1>");
	}
});



function sumamonto(rowId){ 
	var grid = jQuery("#newapi'.$param['grids'][0]['gridname'].'"); 
	var s; 
	var total = 0; 
	var rowcells=new Array();
	var entirerow;
	var hoy   = new Date();
	var fecha ;
	var meco = "";

	if ( rowId > 0 ) {
		entirerow = grid.jqGrid(\'getRowData\',rowId);
		fecha = new Date(entirerow["fecha"].split("-").join("/"))
		if ( hoy < fecha ){
			alert( "Cheque no vencido" );
		} 
	}

	s = grid.getGridParam(\'selarrrow\'); 
	$("#totaldep").html("");
	if(s.length)
	{
		for(var i=0;i<s.length;i++)
		{
			entirerow = grid.jqGrid(\'getRowData\',s[i]);
			fecha = new Date(entirerow["fecha"].split("-").join("/"))
			if ( hoy >= fecha ){
				total += Number(entirerow["monto"]);
				meco = meco+entirerow["id"]+"-";
			} else {
				if ( rowId == 0 ) {
					grid.resetSelection(s[i]);
				}
			}
		}
	total = Math.round(total*100)/100;	
	$("#totaldep").html("Bs. "+nformat(total,2));
	$("#montoform").html("Monto: "+nformat(total,2));
	montotal = total;
	}
	return meco;
};
$(function(){$(".inputnum").numeric(".");});



</script>
';


		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
	<div class="otros">
	<table id="west-grid">
	<tr><td>
		<div class="tema1"><a style="width:190px" href="#" id="listado">Listado '.img(array('src' => 'assets/default/images/print.png', 'alt' => 'Listado',  'title' => 'Listado', 'border'=>'0')).'</a></div>
	<tr><td>
		<div class="tema1"><a style="width:190px" href="#" id="depositar">Enviar a Cobro '.img(array('src' => 'assets/default/images/cheque.png', 'alt' => 'Cheques',  'title' => 'Cheques', 'border'=>'0')).'</a></div>
	</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>
		<div class="tema1"><a style="width:190px" href="#" id="cobrados">Cheques Cobrados '.img(array('src' => 'assets/default/images/monedas.png', 'alt' => 'Cobrados',  'title' => 'Cobrados', 'border'=>'0')).'</a></div>
	<tr><td>
		<div class="tema1"><a style="width:190px" href="#" id="devueltos">Cheques Devueltos '.img(array('src' => 'images/N.gif', 'alt' => 'Devueltos',  'title' => 'Devueltos', 'border'=>'0')).'</a></div>
	</td></tr>
	</table>
	</div>
	<div id="totaldep" style="font-size:20px;text-align:center;"></div>
</div> <!-- #LeftPane -->
';

		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->
';

		$funciones = '
	function fstatus(el, val, opts){
		var meco=\'<div><img src="'.base_url().'images/S.gif" width="20" height="18" border="0" /></div>\';
		if ( el == "E" ){
			meco=\'<div><img src="'.base_url().'assets/default/images/cheque.png" width="20" height="18" border="0" /></div>\';
		} else if (el == "C") {
			meco=\'<div><img src="'.base_url().'assets/default/images/monedas.png" width="20" height="18" border="0" /></div>\';
		} else if (el == "D") {
			meco=\'<div><img src="'.base_url().'images/N.gif" width="20" height="20" border="0" /></div>\';
		}
		return meco;
	}
';

		$param['WestPanel']  = $WestPanel;
		$param['funciones']  = $funciones;

		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados'] = $this->datasis->listados('CHGARA', 'JQ');
		$param['otros']    = $this->datasis->otros('CHGARA', 'JQ');
		$param['temas']     = array('proteo','darkness','anexos1');
		$param['bodyscript'] = $bodyscript;
		$param['tabs'] = false;
		$param['encabeza'] = $this->titp;
		$this->load->view('jqgrid/crud2',$param);
	}

	//*********************************************
	// Guarda los que se enviaron a depositar
	//*********************************************
	function chenvia(){
		$ids = $this->uri->segment(4);
		$ids = str_replace("-",",", $ids);
		$ids = substr($ids,0,-1);
		$mSQL = "UPDATE chgara SET status='E', enviado=curdate() WHERE id IN ($ids) AND status='P' ";
		$this->db->simple_query($mSQL);
		echo "Cheques enviados ";
	}

	//*********************************************
	// Guarda los que se enviaron a depositar
	//*********************************************
	function chcobrados(){
		$ids = $this->uri->segment(4);
		$ids = str_replace("-",",", $ids);
		$ids = substr($ids,0,-1);
		$mSQL = "UPDATE chgara SET status='C' WHERE id IN ($ids) AND status='E' ";
		$this->db->simple_query($mSQL);
		echo "Cheques marcados como Cobrados ";
	}

	//*********************************************
	// Guarda los que se enviaron a depositar
	//*********************************************
	function chdevueltos(){
		$ids = $this->uri->segment(4);
		$ids = str_replace("-",",", $ids);
		$ids = substr($ids,0,-1);
		$mSQL = "UPDATE chgara SET status='P', deposito='DEVUELTO' WHERE id IN ($ids) AND status='E' ";
		$this->db->simple_query($mSQL);
		echo "Cheques marcados como Devueltos ";
	}


	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "true";

		$grid  = new $this->jqdatagrid;

		$link  = site_url('ajax/buscascli');
		$afterhtml = '<div id=\"aaaaaa\">Nombre <strong>"+ui.item.nombre+" </strong>RIF/CI <strong>"+ui.item.rifci+" </strong><br>Direccion <strong>"+ui.item.direc+"</strong></div>';
		$auto = $grid->autocomplete( $link, 'cod_cli', 'aaaaa', $afterhtml );


		$grid->addField('status');
		$grid->label('Status');
		$grid->params(array(
			'search'        => 'true',
			'align'         => "'center'",
			'editable'      => 'false',
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
			'formatter'     => 'fstatus'
		));


		$grid->addField('cod_cli');
		$grid->label('Cliente');
		$grid->params(array(
				'width'       => 60,
				'editable'    => $editar,
				'edittype'    => "'text'",
				'editrules'   => '{ edithidden:true, required:true }',
				'editoptions' => '{'.$auto.'}'
			)
		);

		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
				'width'       => 160,
				'editable'    => 'false',
				'edittype'    => "'text'",
			)
		);

		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 16 }',
		));

		$grid->addField('monto');
		$grid->label('Monto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('banco');
		$grid->label('Banco');
		$grid->params(array(
			'align'         => "'center'",
			'width'         => 40,
			'editable'      => $editar,
			'edittype'      => "'select'",
			'editrules'     => '{ edithidden:true, required:true }',
			'editoptions'   => '{ dataUrl: "'.base_url().'ajax/ddbanco"}',
			'stype'         => "'text'",
		));

		$grid->addField('cuentach');
		$grid->label('Cuenta Bancaria');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 170,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 22 }',
		));

		$grid->addField('vendedor');
		$grid->label('Vendedor');
		$grid->params(array(
			'width'         => 40,
			'hidden'        => 'true',
			'editable'      => 'true',
			'edittype'      => "'select'",
			'editrules'     => '{ edithidden:true, required:true }',
			'editoptions'   => '{ dataUrl: "'.base_url().'ajax/ddvende"}',
		));

		$grid->addField('observa');
		$grid->label('Observacion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editoptions'   => '{ size:30, maxlength: 250 }',
		));

		$grid->addField('enviado');
		$grid->label('Enviado Cobro');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}'
		));


		$grid->addField('deposito');
		$grid->label('Deposito');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));

		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));

		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Estampa" }'
		));

		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

		$grid->addField('modificado');
		$grid->label('Modificado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'false',
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');
		$grid->setMultiSelect(true);

		$grid->setonSelectRow('sumamonto');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:350, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:350, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
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
		$mWHERE = $grid->geneTopWhere('chgara');

		$response   = $grid->getData('chgara', array(array('table'=>'scli', 'join'=>'chgara.cod_cli=scli.cliente', 'fields'=>array('nombre'))), array(), false, $mWHERE, 'status desc,fecha' );
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
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$data['status']    = 'P';
				$data['usuario']   = $this->secu->usuario();
				$data['estampa']   = date('Ymd');
				$data['hora']      = date('H:i:s');
				$this->db->insert('chgara', $data);
				echo "Registro Agregado";
				logusu('CHGARA',"Registro  INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$this->db->where('id', $id);
			$this->db->update('chgara', $data);
			logusu('CHGARA',"Registro $id MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM chgara WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM chgara WHERE id=$id ");
				logusu('CHGARA',"Registro $id  ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}
}

?>