<?php
class Invresu extends Controller {
	var $mModulo='INVRESU';
	var $titp='Libro de Inventario';
	var $tits='Libro de Inventario';
	var $url ='finanzas/invresu/';

	function Invresu(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		if ( !$this->datasis->iscampo('invresu','id') ) {
			$this->db->simple_query('ALTER TABLE invresu DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE invresu ADD UNIQUE INDEX mesco (mes, codigo)');
			$this->db->simple_query('ALTER TABLE invresu ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};
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
$(function() {
	$( "input:submit, a, button", ".otros" ).button();
});

jQuery("#genera").click( function(){
	window.open(\''.base_url().'finanzas/invresu/genelibro/\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
});
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">
<div class="otros">

<table id="west-grid" align="center">
	<tr>
		<td><div class="tema1"><table id="listados"></table></div></td>
	</tr>
	<tr>
		<td><div class="tema1"><table id="otros"></table></div></td>
	<tr><td>&nbsp;</td></tr>
	<tr><td>
		<div class="tema1"><a style="width:190px;text-align:left;" href="#" id="genera">'.img(array('src' => 'images/engrana.png', 'alt' => 'Generar',  'title' => 'Generar', 'border'=>'0')).'&nbsp;&nbsp;&nbsp;&nbsp;Generar Listado</a></div>
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
		$param['listados'] = $this->datasis->listados('INVRESU', 'JQ');
		$param['otros']    = $this->datasis->otros('INVRESU', 'JQ');
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
		$editar = "true";
		$linea   = 1;

		$grid  = new $this->jqdatagrid;
		$grid->addField('mes');
		$grid->label('Ano Mes');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10  }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));

		$linea = $linea + 1;
		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('descrip');
		$grid->label('Descripcion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ size:30, maxlength: 45 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('inicial');
		$grid->label('Inicial');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('compras');
		$grid->label('Compras');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('ventas');
		$grid->label('Ventas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('trans');
		$grid->label('Trans');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('fisico');
		$grid->label('Fisico');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('notas');
		$grid->label('Notas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:false }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('final');
		$grid->label('Final');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('minicial');
		$grid->label('Minicial');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('mcompras');
		$grid->label('Mcompras');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'

		));


		$grid->addField('mventas');
		$grid->label('Mventas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('mtrans');
		$grid->label('Mtrans');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('mfisico');
		$grid->label('Mfisico');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('mnotas');
		$grid->label('Mnotas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1 }'
		));


		$grid->addField('mfinal');
		$grid->label('Mfinal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:2 }'
		));


		$linea = $linea + 1;
		$grid->addField('mpventa');
		$grid->label('Mpventa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }, align:"rigth" }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }',
			'formoptions'   => '{ rowpos:'.$linea.', colpos:1, align:"right" }'
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'hidden'        => 'true',
			'align'         => "'center'",
			'hidden'        => "true",
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

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 600, height:350, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 600, height:350, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
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
		$mWHERE = $grid->geneTopWhere('invresu');

		$response   = $grid->getData('invresu', array(array()), array(), false, $mWHERE );
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
		$mcodp  = "mes";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM invresu WHERE $mcodp=".$this->db->escape($data[$mcodp])." AND codigo="+$this->db->escape($data['codigo'])  );
				if ( $check == 0 ){
					$this->db->insert('invresu', $data);
					echo "Registro Agregado";

					logusu('INVRESU',"Registro ".$data['mes']." ".$data['codigo']." INCLUIDO");
				} else
					echo "Ya existe un registro con ese mes y codigo";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM invresu WHERE id=$id");
			unset($data[$mcodp]);
			$this->db->where("id", $id);
			$this->db->update('invresu', $data);
			logusu('INVRESU',"Libro de Inventario  ".$nuevo." MODIFICADO");
			echo "$mcodp Modificado";

		} elseif($oper == 'del') {
			$codigo = $this->datasis->dameval("SELECT $mcodp FROM invresu WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM invresu WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM invresu WHERE id=$id ");
				logusu('INVRESU',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}


	function genelibro(){
		$this->rapyd->load('datafilter','datagrid','fields');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Libro de inventario','view_invresutotal');
		//$filter->error_string=$error; 
		
		// Genera automaticamente si no estan
		$mes  = date('Y');
		if ( $this->datasis->dameval("SELECT count(*) FROM sitems WHERE year(fecha)=$mes") == 0 ) {
			$mSQL = "INSERT IGNORE INTO invresu (mes, codigo) SELECT CONCAT(YEAR(fecha),MONTH(fecha)) mes, codigoa FROM sitems WHERE YEAR(ffecha)=YEAR(curdate()) GROUP BY YEAR(fecha), MONTH(fecha) ";
			$this->db->simple_query($mSQL);
		}
		
		$mes = $this->datasis->dameval("SELECT MID(MAX(mes),1,4) FROM invresu");
	
		$filter->fecha = new inputField('A&ntilde;o', 'anno');
		$filter->fecha->size     = 4;
		$filter->fecha->operator = '=';
		$filter->fecha->clause   = 'where';
		$filter->fecha->insertValue = $mes;

		$filter->buttons('reset','search');
		$filter->build();

		$monto = new inputField('Monto', 'monto');
		//$monto->db_name  ='final';
		$monto->grid_name='monto[<#anno#>][<#mes#>]';
		//$monto->status   ='modify';
		$monto->size     =14;
		$monto->css_class='inputnum';
		$monto->autocomplete=false;

		$grid = new DataGrid('Lista');
		$grid->per_page = 12;

		$uri2 = anchor('#',img(array('src'=>'images/engrana.png','border'=>'0','alt'=>'Calcula')),array('onclick'=>'bobo(\''.base_url().'finanzas/invresu/calcula/<#anno#><#mes#>\');return false;'));
		$uri2 .= "&nbsp;&nbsp;";
		$uri2 .= anchor('#',img(array('src'=>'images/refresh.png','border'=>'0','alt'=>'Rebaja')),array('onclick'=>'foo(\''.base_url().'finanzas/invresu/recalcula/<#anno#><#mes#>\');return false;'));

		$grid->column('A&ntilde;o','anno' ,'align="center"');
		$grid->column('Mes'       ,'mes'  ,'align="center"');
		$grid->column('Inicial'   ,'<nformat><#inicial#></nformat>'  ,'align=\'right\'');
		$grid->column('Compras'   ,'<nformat><#compras#></nformat>'  ,'align=\'right\'');
		$grid->column('Ventas'    ,'<nformat><#ventas#></nformat>'   ,'align=\'right\'');
		$grid->column('Retiros'   ,'<nformat><#retiros#></nformat>'  ,'align=\'right\'');
		$grid->column('Por Despachar' ,'<nformat><#despachar#></nformat>','align=\'right\'');
		$grid->column('Final'     ,'<nformat><#final#></nformat>'    ,'align=\'right\'');
		$grid->column('Accion',$uri2, 'align=\'center\'');
		
		$grid->build();

		$ggrid =form_open('finanzas/invresu/index/search');
		$ggrid.=form_hidden('fecha', $filter->fecha->newValue);
		$ggrid.=$grid->output;
		$ggrid.=form_close();

		$script ='
		<script type="text/javascript">
		$(function() {
			$(".inputnum").numeric(".");
		});
		function foo(url){
			valor=$("#porcent").val();
			uurl=url+"/"+valor;'."
			$.blockUI({
				message: $('#displayBox'), 
				css: { 
				top:  ($(window).height() - 400) /2 + 'px', 
				left: ($(window).width() - 400) /2 + 'px', 
				width: '400px' 
				}".' 			
			}); 
			$.get(uurl, function(data) {
				setTimeout($.unblockUI, 2); 
				alert(data);
			});
			return false;
		}
		function bobo(url){'."
			$.blockUI({
				message: $('#displayBox'), 
				css: { 
				top:  ($(window).height() - 400) /2 + 'px', 
				left: ($(window).width() - 400) /2 + 'px', 
				width: '400px' 
				}".' 			
			}); 
			$.get(url, function(data) {
				setTimeout($.unblockUI, 2); 
				alert(data);
			});
			return false;
		}
		</script>';
		$espera = '<div id="displayBox" style="display:none" ><p>Espere.....</p><img  src="'.base_url().'images/doggydig.gif" width="131px" height="79px"  /></div>';
		$porcent  = "<div align='left'><a href='".base_url()."reportes/ver/INVENTA/SINV'>Listado</a></div> ";
		$porcent .= "<div align='right'>Porcentaje de Variacion ";
		$porcent .= form_input(array('name'=>'porcent','id'=>'porcent','value'=>'0','size'=>'10','style'=>'text-align:right' ) );
		$porcent .= "</div>";

		$data['content'] = $filter->output.$porcent.$ggrid.$espera;
		
		$data['title']   = heading('Libro de inventario');
		$data['style']   = style('impromptu/default.css');
		
		$data['script']  = script("jquery.js");
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script('plugins/jquery.blockUI.js');
		$data['script'] .= script('jquery-impromptu.js');
		$data['script'] .= $script;

		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function calcula(){
		$meco = $this->uri->segment(4);
		$ano = substr($meco,0,4)*100;
		while ( $meco-$ano < 13 ) {
			$this->db->simple_query("CALL sp_invresu(".$meco.")");
			$meco++;
		}
		echo "Calculo Concluido";
	}

	function recalcula(){
		$meco = $this->uri->segment(4);
		$porcent = $this->uri->segment(5);
		$ano = substr($meco,0,4)*100;
		if ( abs($porcent) > 0  ) {
			$this->db->simple_query("CALL sp_invresufix(".$meco.",".$porcent.")");
			$meco++;
			// debe pasar los saldos a las siguientes meses
			while ( $meco-$ano < 13 ) {
				$this->db->simple_query("CALL sp_invresusum(".$meco.")");
				$meco++;
			};
			echo "Recalculo Concluido";
		} else {
			echo "Debe colocar un porcentaje";
		};

	}

}