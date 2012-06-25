<?php
class Ppro extends Controller {
	var $mModulo='PPRO';
	var $titp='Pago a Proveedor';
	var $tits='Pago a Proveedor';
	var $url ='finanzas/ppro/';

	function Ppro(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('sprv','id') ) {
			$this->db->simple_query('ALTER TABLE sprv DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE sprv ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE sprv ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
		$this->datasis->modintramenu( 900, 600, $url );
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
		window.open(\''.base_url().'reportes/ver/SPRMECU/SPRM/\'+ret.cod_prv, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Proveedor</h1>");}
});

function probar( o, n ) {
	if ( o.val().length < 1 ) {
		o.addClass( "ui-state-error" );
		updateTips( "Seleccion un " + n + "." );
		return false;
	} else {
		return true;
	}
};

$(function() {
	$("#dialog:ui-dialog").dialog( "destroy" );
	var mId = 0;
	var montotal = 0;
	var ffecha = $("#ffecha");
	var grid = jQuery("#newapi'.$param['grids'][0]['gridname'].'");
	var s;
	var allFields = $( [] ).add( ffecha );
	
	var tips = $( ".validateTips" );

	s = grid.getGridParam(\'selarrrow\'); 
	$( "input:submit, a, button", ".otros" ).button();

	$( "#abono" ).click(function() {
		var id     = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
		if (id)	{
			var ret    = $("#newapi'. $param['grids'][0]['gridname'].'").getRowData(id);  
			mId = id;
			$.post("'.base_url().'finanzas/ppro/formaabono/"+id, function(data){
				$("#fabono").html(data);
			});
			$( "#fabono" ).dialog( "open" );
			
		} else { $.prompt("<h1>Por favor Seleccione un Proveedor</h1>");}
	});
	
	$( "#borrar" ).click(function() {
		var id = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
		var m = "";
		if (id)	{
			$.prompt( "Eliminar Registro? ",{
					buttons: { Borrar:true, Cancelar:false},
					callback: function(e,v,m,f){
						if (v == true) {
							$.get("'.base_url().$this->url.'bcajborra/"+id,
							function(data){
								alert(data);
							});
						}
					}
				}
			);
		} else { $.prompt("<h2>Por favor Seleccione un Movimiento</h2>");}
	});
	
	
	$( "#fabono" ).dialog({
		autoOpen: false,
		height: 470,
		width: 790,
		modal: true,
		buttons: {
			"Pagar": function() {
				var bValid = true;
				allFields.removeClass( "ui-state-error" );
				if ( bValid ) {
					$.ajax({
						type: "POST",
						dataType: "html",
						url:"'.site_url("finanzas/ppro/abono").'",
						async: false,
						data: $("#abonoforma").serialize(),
						success: function(r,s,x){
							var res = $.parseJSON(r);
							if ( res.status == "E"){
								alert("Error: "+res.mensaje);
							} else {
								alert(res.mensaje);
								grid.trigger("reloadGrid");
								return [true, a ];
							}
						}
					});
				}
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});

});

</script>
';

		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
<div class="otros">

<table id="west-grid" align="center">
	<tr><td>
		<div class="anexos"><table id="listados"></table></div></td>
	</tr><tr>
		<td><table id="otros"></table></td>
	</tr>
</table>

<table id="west-grid" align="center">
	<tr>
		<td><div class="tema1"><a style="width:190px" href="#" id="a1">Estado de Cuenta '.img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',  'title' => 'Formato PDF', 'border'=>'0')).'</a></div></td>
	</tr><tr>
		<td><div class="tema1"><a style="width:190px" href="#" id="abono">Pagar y/o Abonar '.img(array('src' => 'images/candado.jpg', 'alt' => 'Abonar',  'title' => 'Abonar', 'border'=>'0')).'</a></div></td>
	</tr>

</table>
</div>
</div> <!-- #LeftPane -->
';


//	</tr><tr>
//		<td><div class="tema1"><a style="width:190px" href="#" id="borrar">Eliminar Movimiento '.img(array('src' => 'images/delete.jpg', 'alt' => 'Eliminar',  'title' => 'Eliminar', 'border'=>'0')).'</a></div></td>



		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->

<div id="fabono" title="Pagos y Abonos"></div>

';




		$param['WestPanel']  = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados'] = $this->datasis->listados('PPRO', 'JQ');
		$param['otros']    = $this->datasis->otros('PPRO', 'JQ');
		$param['temas']     = array('proteo','darkness','anexos1');
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
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('cod_prv');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('rif');
		$grid->label('RIF');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 90,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('nombre');
		$grid->label('Proveedor');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));


		$grid->addField('saldo');
		$grid->label('Saldo');
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


		$grid->addField('cantidad');
		$grid->label('Cant.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));


		$grid->addField('nueva');
		$grid->label('Nueva');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('vieja');
		$grid->label('Vieja');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('dias');
		$grid->label('Dias');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
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

		//$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		//$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		//$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(false);
		$grid->setSearch(false);
		$grid->setOndblClickRow('');
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		#Set url
		//$grid->setUrlput(site_url($this->url.'setdata/'));

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
		$grid = $this->jqdatagrid;
		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('view_ppro');

		$response   = $grid->getData('view_ppro', array(array()), array(), false, $mWHERE );
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
				$this->db->insert('sprv', $data);
				echo "Registro Agregado";

				logusu('SPRV',"Registro ????? INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			//unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('sprv', $data);
			logusu('SPRV',"Registro ????? MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM sprv WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM sprv WHERE id=$id ");
				logusu('SPRV',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}


	// forma de cierre de deposito
	function formaabono(){
		$id      = $this->uri->segment($this->uri->total_segments());
		$proveed = $this->datasis->dameval("SELECT proveed FROM sprv WHERE id=$id");

		$reg = $this->datasis->damereg("SELECT proveed, nombre, rif FROM sprv WHERE id=$id");

		$salida = '
<script type="text/javascript">
	var lastcell = 0;
	jQuery("#aceptados").jqGrid({
		datatype: "local",
		height: 190,
		colNames:["id","Tipo","Numero","Fecha","Vence","Monto","Saldo", "Faltante","Abonar","P.Pago"],
		colModel:[
			{name:"id",       index:"id",       width:10, hidden:true},
			{name:"tipo_doc", index:"tipo_doc", width:40},
			{name:"numero",   index:"numero",   width:90},
			{name:"fecha",    index:"fecha",    width:90},
			{name:"vence",    index:"vence",    width:90},
			{name:"monto",    index:"monto",    width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"saldo",    index:"saldo",    width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"faltan",   index:"faltan",   width:80, align:"right", edittype:"text", editable:false, formatter: "number", formatoptions: {label:"Monto adeudado",decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 } },
			{name:"abonar",   index:"abonar",   width:80, align:"right", edittype:"text", editable:true },
			{name:"ppago",    index:"ppago",    width:80, align:"right", edittype:"text", editable:true },
		],
		onSelectRow: function(id){
			var grid1 = jQuery("#aceptados");
			var row;
			if(id && id !== lastcell){
				grid1.jqGrid("restoreRow",lastcell);
				row = grid1.jqGrid(\'getRowData\', id );
				//alert("paso 1 "+row["abonar"]);
				if (row["abonar"] == "" ){
					grid1.jqGrid("setCell",id,"abonar", row["saldo"]);
				}
				grid1.jqGrid("editRow",id,true);
				lastcell=id;
			}
			sumatot();
		},
		editurl: "'.base_url().'finanzas/ppro/abonoguarda",
	});
	
	var mefectos = [
';



		$mSQL = "SELECT id, tipo_doc, numero, fecha, vence, monto, monto-abonos saldo, '' faltan, '' abonar, '' ppago FROM sprm WHERE monto>abonos AND tipo_doc IN ('FC','ND','GI') AND cod_prv=".$this->db->escape($reg['proveed']);
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0 ){
			foreach( $query->result() as $row ){
				$salida .= "\t\t".'{id:"'.$row->id.'",';
				$salida .= 'tipo_doc:"'.$row->tipo_doc.'",';
				$salida .= 'numero:"'.  $row->numero.'",';
				$salida .= 'fecha:"'.   $row->fecha.'",';
				$salida .= 'vence:"'.   $row->vence.'",';
				$salida .= 'monto:"'.   $row->monto.'",';
				$salida .= 'saldo:"'.   $row->saldo.'",';
				$salida .= 'faltan:"'.  $row->faltan.'",';
				$salida .= 'abonar:"'.  $row->abonar.'",';
				$salida .= 'ppago:"'.   $row->ppago.'"},'."\n";
			}
		}
		$salida .= '
	];
	for(var i=0;i<=mefectos.length;i++) jQuery("#aceptados").jqGrid(\'addRowData\',i+1,mefectos[i]);
	
	$("#ffecha").datepicker({dateFormat:"dd/mm/yy"});

	function sumatot()
        { 
		var grid = jQuery("#aceptados");
		var s;
		var total = 0;
		var meco = "";
		var rowcells = new Array();
		var entirerow;
		s = grid.getGridParam(\'selarrrow\'); 
		$("#fsele").html("");
		if(s.length)
		{
			for(var i=0; i<s.length; i++)
			{
				entirerow = grid.jqGrid(\'getRowData\',s[i]);
				if (Number(entirerow["abonar"]) == 0) {
					entirerow["abonar"] = entirerow["saldo"];
				}
				total += Number(entirerow["abonar"]);
				meco = meco+entirerow["id"]+",";
			}
			total = Math.round(total*100)/100;	
			$("#grantotal").html(nformat(total,2));
			$("input#fsele").val(meco);
			$("input#fmonto").val(total);
			montotal = total;
		} else {
			total = 0;
			$("#grantotal").html(" "+nformat(total,2));
			$("input#fsele").val("");
			$("input#fmonto").val(total);
			montotal = total;	
		}
	};

</script>
	<p class="validateTips"></p>
	<form id="abonoforma">	
	<table width="80%" align="center"><tr>
		<td  class="CaptionTD"  align="right">Fecha</td>
		<td>&nbsp;<input name="ffecha" id="ffecha" type="text" value="'.date('d/m/Y').'" maxlengh="10" size="10"  /></td>
		<td  class="CaptionTD"  align="right">Comprobante</td>
		<td>&nbsp;<input name="fcomprob" id="fcomprob" type="text" value="" maxlengh="10" size="10"  /></td>
	</tr></table>
	<input id="fmonto"   name="fmonto"   type="hidden">
	<input id="fsele"    name="fsele"    type="hidden">
	<input id="fid"      name="fid"      type="hidden" value="'.$id.'">
	</form>
	<br>
	<center><table id="aceptados"><table></center>
	<table width="80%">
	<td><div id="grantotal" style="font-size:20px;font-weight:bold">Monto a pagar: 0.00</div>
	</td></table>

	';
	
		echo $salida;

	}
	

	function abonoguarda(){
		echo 'a' ;
	}


}



/*
class psprv extends Controller {
	var $titp='Pago a proveedores';
	var $tits='Pago a proveedor';
	var $url ='finanzas/psprv/';

	function psprv(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id('524',1);
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp);
		$sel=array('TRIM(a.cod_prv) AS cod_prv','b.nombre','SUM(a.monto-a.abonos) AS saldo');
		$filter->db->select($sel);
		$filter->db->from('sprm AS a');
		$filter->db->join('sprv AS b','a.cod_prv=b.proveed');
		$filter->db->where('a.monto > a.abonos');
		$filter->db->where_in('a.tipo_doc',array('FC','ND','GI'));
		$filter->db->groupby('a.cod_prv');

		$filter->cod_prv = new inputField('Proveedor','cod_prv');
		$filter->cod_prv->rule      = 'max_length[5]';
		$filter->cod_prv->size      = 7;
		$filter->cod_prv->db_name   = 'a.cod_prv';
		$filter->cod_prv->maxlength = 5;

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->rule      = 'max_length[8]';
		$filter->nombre->size      = 10;
		$filter->nombre->db_name   = 'a.nombre';
		$filter->nombre->maxlength = 8;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/<raencode><#cod_prv#></raencode>/create','<#cod_prv#>');

		$grid = new DataGrid('Seleccione el cliente');
		$grid->order_by('fecha','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Proveedor'  ,$uri,'cod_prv','align="left"');
		$grid->column_orderby('Nombre'     ,'nombre','nombre','align="left"');
		$grid->column_orderby('Saldo'      ,'<nformat><#saldo#></nformat>','saldo','align="right"');

		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function dataedit($proveed){
		if(!$this->_exitesprv($proveed)) redirect($this->url.'filteredgrid');
		$cajero=$this->secu->getcajero();
		if(empty($cajero)) show_error('El usuario debe tener registrado un cajero para poder usar este modulo');

		$this->rapyd->load('dataobject','datadetails');
		$this->rapyd->uri->keep_persistence();

		$do = new DataObject('sprm');
		$do->rel_one_to_many('itppro', 'itppro', array(
			'tipo_doc'=>'tipoppro',
			'numero'  =>'numppro',
			'cod_prv' =>'cod_prv',
			'fecha'   =>'fecha')
		);
		$do->rel_one_to_many('bmov' , 'bmov' , array(
			'transac' =>'transac',
			'numero'  =>'numero',
			'tipo_op'=>'tipo_op',
			'fecha'   =>'fecha')
		);
		$do->order_by('itppro','itppro.fecha');

		$edit = new DataDetails('Pago a Proveedor', $do);
		$edit->back_url = site_url('finanzas/psprv/filteredgrid');
		$edit->set_rel_title('itppro', 'Efectos <#o#>');
		$edit->set_rel_title('bmov'  , 'Forma de pago <#o#>');

		$edit->pre_process( 'insert', '_pre_insert');
		$edit->pre_process( 'update', '_pre_update');
		$edit->pre_process( 'delete', '_pre_delete');
		$edit->post_process('insert', '_post_insert');
		$edit->post_process('update', '_post_update');
		$edit->post_process('delete', '_post_delete');

		$edit->cod_prv = new hiddenField('Proveedor','cod_prv');
		$edit->cod_prv->rule ='max_length[5]';
		$edit->cod_prv->size =7;
		$edit->cod_prv->insertValue=$proveed;
		$edit->cod_prv->maxlength =5;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[40]';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;

		$edit->tipo_doc = new  dropdownField('Tipo doc.', 'tipo_doc');
		$edit->tipo_doc->option('AB','Abono');
		$edit->tipo_doc->option('NC','Nota de credito');
		$edit->tipo_doc->option('AN','Anticipo');
		$edit->tipo_doc->style='width:140px;';
		$edit->tipo_doc->rule ='enum[AB,NC,AN]|required';

		$edit->codigo = new  dropdownField('Motivo', 'codigo');
		$edit->codigo->option('','Ninguno');
		$edit->codigo->options('SELECT TRIM(codigo) AS cod, nombre FROM botr WHERE tipo=\'C\' ORDER BY nombre');
		$edit->codigo->style='width:200px;';
		$edit->codigo->rule ='';

		$edit->numero = new inputField('N&uacute;mero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;

		$edit->fecdoc = new dateonlyField('Fecha','fecdoc');
		$edit->fecdoc->size =10;
		$edit->fecdoc->maxlength =8;
		$edit->fecdoc->insertValue=date('Y-m-d');
		$edit->fecdoc->rule ='chfecha|required';

		$edit->monto = new inputField('Total','monto');
		$edit->monto->rule='max_length[17]|numeric';
		$edit->monto->css_class='inputnum';
		$edit->monto->size =19;
		$edit->monto->maxlength =17;
		$edit->monto->type='inputhidden';

		$edit->usuario = new autoUpdateField('usuario' ,$this->secu->usuario(),$this->secu->usuario());
		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora'    ,date('H:i:s'), date('H:i:s'));
		$edit->fecha   = new autoUpdateField('fecha'   ,date('Ymd'), date('Ymd'));

		//************************************************
		//inicio detalle itppro
		//************************************************
		$i=0;
		$edit->detail_expand_except('itppro');
		$sel=array('a.tipo_doc','a.numero','a.fecha','a.monto','a.abonos','a.monto - a.abonos AS saldo', 'round(if(sum(d.devcant*d.costo) is null,0.00,sum(d.devcant*d.costo)),2) AS falta');
		$this->db->select($sel);
		$this->db->from('sprm AS a');
		$this->db->where('a.cod_prv',$proveed);
		$transac=$edit->get_from_dataobjetct('transac');
		if($transac!==false){
			$tipo_doc =$edit->get_from_dataobjetct('tipo_doc');
			$dbtransac=$this->db->escape($transac);
			$this->db->join('itppro AS b','a.tipo_doc = b.tipopsprv AND a.numero=b.numpsprv AND a.transac='.$dbtransac);
			$this->db->where('a.tipo_doc',$tipo_doc);
		}else{
			$this->db->where('a.monto > a.abonos');
			$this->db->where_in('a.tipo_doc',array('FC','ND','GI'));
		}
		$this->db->join('scst   AS c', 'a.transac=c.transac AND a.tipo_doc=c.tipo_doc AND a.cod_prv=c.proveed','LEFT');
		$this->db->join('itscst AS d', 'c.control=d.control AND d.devcant is not null','LEFT');
		$this->db->group_by('a.cod_prv, a.tipo_doc, a.numero');
		$this->db->order_by('a.fecha');
		$query = $this->db->get();
		//echo $this->db->last_query();
		foreach ($query->result() as $row){
			$obj='cod_prv_'.$i;
			$edit->$obj = new autoUpdateField('cod_prv',$proveed,$proveed);
			$edit->$obj->rel_id  = 'itpsprv';
			$edit->$obj->ind     = $i;

			$obj='tipo_doc_'.$i;
			$edit->$obj = new inputField('Tipo_doc',$obj);
			$edit->$obj->db_name='tipo_doc';
			$edit->$obj->rel_id = 'itpsprv';
			$edit->$obj->rule='max_length[2]';
			$edit->$obj->insertValue=$row->tipo_doc;
			$edit->$obj->size =4;
			$edit->$obj->maxlength =2;
			$edit->$obj->ind       = $i;
			$edit->$obj->type='inputhidden';

			$obj='numero_'.$i;
			$edit->$obj = new inputField('Numero',$obj);
			$edit->$obj->db_name='numero';
			$edit->$obj->rel_id = 'itpsprv';
			$edit->$obj->rule='max_length[8]';
			$edit->$obj->insertValue=$row->numero;
			$edit->$obj->size =10;
			$edit->$obj->maxlength =8;
			$edit->$obj->ind       = $i;
			$edit->$obj->type='inputhidden';

			$obj='fecha_'.$i;
			$edit->$obj = new dateonlyField('Fecha',$obj);
			$edit->$obj->db_name='fecha';
			$edit->$obj->rel_id = 'itpsprv';
			$edit->$obj->rule='chfecha';
			$edit->$obj->insertValue=$row->fecha;
			$edit->$obj->size =10;
			$edit->$obj->maxlength =8;
			$edit->$obj->ind       = $i;
			$edit->$obj->type='inputhidden';

			$obj='monto_'.$i;
			$edit->$obj = new inputField('Monto',$obj);
			$edit->$obj->db_name='monto';
			$edit->$obj->rel_id = 'itpsprv';
			$edit->$obj->rule='max_length[18]|numeric';
			$edit->$obj->css_class='inputnum';
			$edit->$obj->size =20;
			$edit->$obj->insertValue=$row->monto;
			$edit->$obj->maxlength =18;
			$edit->$obj->ind       = $i;
			$edit->$obj->showformat='decimal';
			$edit->$obj->type='inputhidden';

			$obj='saldo_'.$i;
			$edit->$obj = new freeField($obj,$obj,nformat($row->saldo));
			$edit->$obj->ind = $i;

			$obj='falta_'.$i;
			$edit->$obj = new freeField($obj,$obj,nformat($row->falta));
			$edit->$obj->ind = $i;


	        $obj='abono_'.$i;
			$edit->$obj = new inputField('Abono',$obj);
			$edit->$obj->db_name      = 'abono';
			$edit->$obj->rel_id       = 'itpsprv';
			$edit->$obj->rule         = "max_length[18]|numeric|positive|callback_chabono[$i]";
			$edit->$obj->css_class    = 'inputnum';
			$edit->$obj->showformat   = 'decimal';
			$edit->$obj->autocomplete = false;
			$edit->$obj->disable_paste= true;
			$edit->$obj->size         = 15;
			$edit->$obj->maxlength    = 18;
			$edit->$obj->ind          = $i;
			$edit->$obj->onfocus      = 'itsaldo(this,'.round($row->saldo,2).');';

	        $obj='ppago_'.$i;
			$edit->$obj = new inputField('Pronto Pago',$obj);
			$edit->$obj->db_name      = 'ppago';
			$edit->$obj->rel_id       = 'itpsprv';
			$edit->$obj->rule         = "max_length[18]|numeric|positive|callback_chppago[$i]";
			$edit->$obj->css_class    = 'inputnum';
			$edit->$obj->showformat   = 'decimal';
			$edit->$obj->autocomplete = false;
			$edit->$obj->disable_paste= true;
			$edit->$obj->size         = 15;
			$edit->$obj->maxlength    = 18;
			$edit->$obj->ind          = $i;
			$edit->$obj->onchange     = "itppago(this,'$i');";

			$i++;
		}
		//************************************************
		//fin de campos para detalle,inicio detalle2 bmov
		//************************************************
		$edit->banco = new dropdownField('Banco <#o#>', 'banco_<#i#>');
		$edit->banco->option('','Seleccionar');
		$edit->banco->options('SELECT "**" codbanc,"** PENDIENTE" banco FROM banc LIMIT 1 UNION ALL SELECT codbanc, CONCAT(codbanc," ",banco) banco  FROM banc ORDER BY codbanc');
		$edit->banco->db_name='banco';
		$edit->banco->rel_id ='bmov';
		$edit->banco->style  ='width:200px;';
		$edit->banco->rule   ='required';

		$edit->tipo_op = new  dropdownField('Tipo Operacion <#o#>', 'tipo_op_<#i#>');
		$edit->tipo_op->option('','Seleccionar');
		$edit->tipo_op->option('CH','Cheque');
		$edit->tipo_op->option('ND','Nota de Debito');
		$edit->tipo_op->db_name  = 'tipo';
		$edit->tipo_op->rel_id   = 'bmov';
		$edit->tipo_op->style    = 'width:160px;';
		$edit->tipo_op->rule     = 'required|enum[CH,ND]';
		$edit->tipo_op->insertValue='CH';

		$edit->bmovfecha = new dateonlyField('Fecha','bmovfecha_<#i#>');
		$edit->bmovfecha->rel_id   = 'bmov';
		$edit->bmovfecha->db_name  = 'fecha';
		$edit->bmovfecha->size     = 10;
		$edit->bmovfecha->maxlength= 8;
		$edit->bmovfecha->rule ='condi_required|chsitfecha|callback_chtipo[<#i#>]';

		$edit->numref = new inputField('Numero <#o#>', 'num_ref_<#i#>');
		$edit->numref->size     = 12;
		$edit->numref->db_name  = 'num_ref';
		$edit->numref->rel_id   = 'bmov';
		$edit->numref->rule     = 'condi_required|callback_chtipo[<#i#>]';

		$edit->itmonto = new inputField('Monto <#o#>', 'itmonto_<#i#>');
		$edit->itmonto->db_name     = 'monto';
		$edit->itmonto->css_class   = 'inputnum';
		$edit->itmonto->rel_id      = 'bmov';
		$edit->itmonto->size        = 10;
		$edit->itmonto->rule        = 'condi_required|positive|callback_chmontosfpa[<#i#>]';
		$edit->itmonto->showformat  = 'decimal';
		$edit->itmonto->autocomplete= false;
		//************************************************
		// Fin detalle 2 (bmov)
		//************************************************

		$edit->buttons('save','undo','back','add');
		$edit->build();

		$conten['cana']  = $i;
		$conten['form']  = & $edit;
		$conten['title'] = heading('');
		$data['head']    = style('estilo.css');
		$data['head']   .= $this->rapyd->get_head();
		$data['script']  = script('jquery.js');
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= phpscript('nformat.js');
		$data['content'] = $this->load->view('view_psprv.php', $conten,true);
		$data['title']   = '';
		$this->load->view('view_ventanas', $data);
	}

	function chsfpatipo($val){
		$tipo=$this->input->post('tipo_doc');
		if($tipo=='NC') {
			return true;
		}
		$this->validation->set_message('chsfpatipo', 'El campo %s es obligatorio');
		if(empty($val)){
			return false;
		}else{
			return true;
		}
	}

	function chfuturo($fecha){
		$fdoc=timestampFromInputDate($fecha);
		$fact=mktime();

		if($fdoc > $fact){
			$this->validation->set_message('chfuturo', 'No puede meter un efecto a futuro');
			return false;
		}
		return true;
	}

	function chtipo($val,$i){
		$tipo=$this->input->post('tipo_'.$i);
		if(empty($tipo)) return true;
		$this->validation->set_message('chtipo', 'El campo %s es obligatorio');

		if(empty($val) && ($tipo!='EF'))
			return false;
		else
			return true;
	}

	function chmontosfpa($monto){
		$tipo   = $this->input->post('tipo_doc');
		if($tipo=='NC'){
			return true;
		}
		if(empty($monto) || $monto==0){
			$this->validation->set_message('chmontosfpa', "El campo %s es obligatorio");
			return false;
		}
		return true;
	}

	function chppago($monto,$i){
		$tipo   = $this->input->post('tipo_doc');
		if($tipo=='NC' && $monto>0){
			$this->validation->set_message('chppago', "No se puede hacer pronto pago cuando el tipo de documento es una nota de cr&eacute;dito");
			return false;
		}
		return true;
	}

	function chabono($monto,$i){
		$tipo   = $this->input->post('tipo_doc_'.$i);
		$ppago  = $this->input->post('ppago_'.$i);
		$numero = $this->input->post('numero_'.$i);
		$cod_prv= $this->input->post('cod_prv');
		$fecha  = human_to_dbdate($this->input->post('fecha_'.$i));

		$this->db->select(array('monto - abonos AS saldo'));
		$this->db->from('sprm');
		$this->db->where('tipo_doc',$tipo);
		$this->db->where('numero'  ,$numero);
		$this->db->where('fecha'   ,$fecha);
		$this->db->where('cod_prv' ,$cod_prv);

		$query = $this->db->get();
		$row   = $query->row();

		if ($query->num_rows() == 0) return false;
		$saldo = $row->saldo;

		if(($monto+$ppago)<=$saldo){
			return true;
		}else{
			$this->validation->set_message('chabono', "No se le puede abonar al efecto $tipo-$numero un monto mayor al saldo");
			return false;
		}
	}

	function cuenta($cliente){
		if(!$this->_exitescli($cliente)) redirect($this->url.'filterscli');

		$do = new DataObject('smov');
		$r1 = array('tipo_doc' => 'numpsprv' ,'numero'=>'numpsprv');
		$r2 = array('tipo_doc' => 'tipo_doc','numero'=>'numero' );

		$do->rel_many_to_many('smov', 'smov','itpsprv',$r1,$r2);
	}

	function _exitesprv($proveed){
		$dbsprv= $this->db->escape($proveed);
		$mSQL  = "SELECT COUNT(*) AS cana FROM sprv WHERE proveed=$dbsprv";
		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$row = $query->row();
			if( $row->cana>0) return true; else return false;
		}else{
			return false;
		}
	}

	function _pre_insert($do){
		$proveed =$do->get('cod_prv');
		$estampa = $do->get('estampa');
		$hora    = $do->get('hora');
		$usuario = $do->get('usuario');
		$cod_prv = $do->get('cod_prv');
		$tipo_doc= $do->get('tipo_doc');
		$fecha   = $do->get('fecha');
		$itabono=$sfpamonto=$ppagomonto=0;

		$rrow    = $this->datasis->damerow('SELECT nombre,rif,direc1,direc2 FROM sprv WHERE proveed='.$this->db->escape($proveed));
		if($rrow!=false){
			$do->set('nombre',$rrow['nombre']);
			$do->set('dire1' ,$rrow['dire1']);
			$do->set('dire2' ,$rrow['dire2']);
		}

		//Totaliza el abonado
		$rel='itppro';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$itabono += $do->get_rel($rel, 'abono', $i);
			$pppago   = $do->get_rel($rel, 'ppago', $i);
			if(empty($pppago)){
				$do->set_rel($rel,'ppago',0,$i);
			}else{
				$ppagomonto += $do->get_rel($rel, 'ppago', $i);
			}
		}
		$itabono=round($itabono,2);

		//Totaliza lo pagado
		$rel='sfpa';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$sfpamonto+=$do->get_rel($rel, 'monto', $i);
		}
		$sfpamonto=round($sfpamonto,2);

		//Realiza las validaciones
		$cajero=$this->secu->getcajero();
		$this->load->library('validation');
		$rt=$this->validation->cajerostatus($cajero);
		if(!$rt){
			$do->error_message_ar['pre_ins']='El cajero usado ('.$cajero.') esta cerrado para esta fecha';
			return false;
		}

		if($tipo_doc=='NC'){
			$do->truncate_rel('bmov');
			if($itabono==0){
				$do->error_message_ar['pre_ins']='Si crea una nota de credito debe relacionarla con algun movimiento';
				return false;
			}
		}elseif($tipo_doc=='AN'){
			$do->truncate_rel('itppro');
			if($itabono!=0){
				$do->error_message_ar['pre_ins']='Un anticipo no puede estar relacionado con algun efecto, en tal caso seria un abono';
				return false;
			}else{
				$itabono=$sfpamonto;
			}
		}else{
			if(abs($sfpamonto-$itabono)>0.01){
				$do->error_message_ar['pre_ins']='El monto cobrado no coincide con el monto de la la transacci&oacute;n';
				return false;
			}
		}
		//fin de las validaciones
		$do->set('monto',$itabono);

		$dbcliente= $this->db->escape($cliente);
		$rowscli  = $this->datasis->damerow('SELECT nombre,dire11,dire12 FROM scli WHERE cliente='.$dbcliente);
		$do->set('nombre', $rowscli['nombre']);
		$do->set('dire1' , $rowscli['dire11']);
		$do->set('dire2' , $rowscli['dire12']);

		$transac  = $this->datasis->fprox_numero('ntransa');

		if($tipo_doc=='AB'){
			$mnum = $this->datasis->fprox_numero('nabcli');
		}elseif($tipo_doc=='GI'){
			$mnum = $this->datasis->fprox_numero('ngicli');
		}elseif($tipo_doc=='NC'){
			$mnum = $this->datasis->fprox_numero('npsprv');
		}else{
			$mnum = $this->datasis->fprox_numero('nancli');
		}
		$do->set('vence'  , $fecha);
		$do->set('numero' , $mnum);
		$do->set('transac', $transac);

		$rel='itpsprv';
		$observa=array();
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$itabono = $do->get_rel($rel, 'abono'   , $i);
			$ittipo  = $do->get_rel($rel, 'tipo_doc', $i);
			$itnumero= $do->get_rel($rel, 'numero'  , $i);
			if(empty($itabono) || $itabono==0){
				$do->rel_rm($rel,$i);
			}else{
				$observa[]=$ittipo.$itnumero;
				$do->set_rel($rel, 'tipopsprv', $tipo_doc, $i);
				$do->set_rel($rel, 'cod_prv' , $cod_prv , $i);
				$do->set_rel($rel, 'estampa' , $estampa , $i);
				$do->set_rel($rel, 'hora'    , $hora    , $i);
				$do->set_rel($rel, 'usuario' , $usuario , $i);
				$do->set_rel($rel, 'transac' , $transac , $i);
				$do->set_rel($rel, 'mora'    , 0, $i);
				$do->set_rel($rel, 'reten'   , 0, $i);
				$do->set_rel($rel, 'cambio'  , 0, $i);
				$do->set_rel($rel, 'reteiva' , 0, $i);
			}
		}
		if(count($observa)>0){
			$observa='PAGA '.implode(',',$observa);
			$do->set('observa1' , substr($observa,0,50));
			if(strlen($observa)>50) $do->set('observa2' , substr($observa,50));
		}

		$rel='sfpa';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$sfpatipo=$do->get_rel($rel, 'tipo_doc', $i);
			if($sfpatipo=='EF') $do->set_rel($rel, 'fecha' , $fecha , $i);

			$do->set_rel($rel,'estampa'  , $estampa , $i);
			$do->set_rel($rel,'hora'     , $hora    , $i);
			$do->set_rel($rel,'usuario'  , $usuario , $i);
			$do->set_rel($rel,'transac'  , $transac , $i);
			$do->set_rel($rel,'f_factura', $fecha   , $i);
			$do->set_rel($rel,'cod_prv'  ,$cliente  , $i);
			$do->set_rel($rel,'cobro'    ,$fecha    , $i);
			$do->set_rel($rel,'vendedor' ,$this->secu->getvendedor(),$i);
			$do->set_rel($rel,'cobrador' ,$this->secu->getcajero()  ,$i);
			$do->set_rel($rel,'almacen'  ,$this->secu->getalmacen() ,$i);
		}
		$this->ppagomonto=$ppagomonto;

		$do->set('mora'    ,0);
		$do->set('reten'   ,0);
		$do->set('cambio'  ,0);
		$do->set('reteiva' ,0);
		$do->set('ppago'   ,$ppagomonto);
		$do->set('codigo'  ,'NOCON');
		$do->set('descrip' ,'NOTA DE CONTABILIDAD');
		$do->set('vendedor', $this->secu->getvendedor());
		return true;
	}

	function _post_insert($do){
		$cliente  =$do->get('cod_prv');
		$dbcliente=$this->db->escape($cliente);

		$rel_id='itpsprv';
		$cana = $do->count_rel($rel_id);
		if($cana>0){
			if($this->ppagomonto>0){
				//Crea la NC por Pronto pago
				$mnumnc = $this->datasis->fprox_numero('npsprv');

				$dbdata=array();
				$dbdata['cod_prv']    = $cliente;
				$dbdata['nombre']     = $do->get('nombre');
				$dbdata['dire1']      = $do->get('dire1');
				$dbdata['dire2']      = $do->get('dire2');
				$dbdata['tipo_doc']   = 'NC';
				$dbdata['numero']     = $mnumnc;
				$dbdata['fecha']      = $do->get('fecha');
				$dbdata['monto']      = $this->ppagomonto;
				$dbdata['impuesto']   = 0;
				$dbdata['abonos']     = $this->ppagomonto;
				$dbdata['vence']      = $do->get('fecha');
				$dbdata['tipo_ref']   = 'AB';
				$dbdata['num_ref']    = $do->get('numero');
				$dbdata['observa1']   = 'DESCUENTO POR PRONTO PAGO';
				$dbdata['estampa']    = $do->get('estampa');
				$dbdata['hora']       = $do->get('hora');
				$dbdata['transac']    = $do->get('transac');
				$dbdata['usuario']    = $do->get('usuario');
				$dbdata['codigo']     = 'NOCON';
				$dbdata['descrip']    = 'NOTA DE CONTABILIDAD';
				$dbdata['fecdoc']     = $do->get('fecha');
				$dbdata['nroriva']    = '';
				$dbdata['emiriva']    = '';
				$dbdata['reten']      = 0;
				$dbdata['cambio']     = 0;
				$dbdata['mora']       = 0;

				$mSQL = $this->db->insert_string('smov', $dbdata);
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'psprv'); }

				$itdbdata=array();
				$itdbdata['cod_prv']  = $cliente;
				$itdbdata['numpsprv']  = $mnumnc;
				$itdbdata['tipopsprv'] = 'NC';
				$itdbdata['estampa']  = $do->get('estampa');
				$itdbdata['hora']     = $do->get('hora');
				$itdbdata['transac']  = $do->get('transac');
				$itdbdata['usuario']  = $do->get('usuario');
				$itdbdata['fecha']    = $do->get('fecha');
				$itdbdata['monto']    = $this->ppagomonto;
				$itdbdata['reten']    = 0;
				$itdbdata['cambio']   = 0;
				$itdbdata['mora']     = 0;

				unset($dbdata);
			}

			foreach($do->data_rel[$rel_id] AS $i=>$data){
				$tipo_doc = $data['tipo_doc'];
				$numero   = $data['numero'];
				$fecha    = $data['fecha'];
				$monto    = $data['abono'];
				$ppago    = (empty($data['ppago']))? 0: $data['ppago'];

				$dbtipo_doc = $this->db->escape($tipo_doc);
				$dbnumero   = $this->db->escape($numero  );
				$dbfecha    = $this->db->escape($fecha   );
				$dbmonto    = $monto+$ppago;

				$mSQL="UPDATE smov SET abonos=abonos+$dbmonto WHERE tipo_doc=$dbtipo_doc AND fecha=$dbfecha AND numero=$dbnumero AND cod_prv=$dbcliente";
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'psprv'); }

				if($ppago > 0 ){
					$itdbdata['tipo_doc'] = $tipo_doc;
					$itdbdata['numero']   = $numero;
					$itdbdata['abono']    = $ppago;

					$mSQL = $this->db->insert_string('itpsprv', $itdbdata);
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'psprv'); }
				}
			}
		}
	}

	function _pre_update($do){
		return false;
	}

	function _pre_delete($do){
		return false;
	}

}
*/
?>