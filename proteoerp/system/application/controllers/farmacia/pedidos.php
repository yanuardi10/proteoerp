<?php require_once(APPPATH.'/controllers/inventario/consultas.php');
class Pedidos extends Controller {
	var $mModulo='FALLAPED';
	var $titp='Pedido de Fallas Diarias';
	var $tits='Pedido de Fallas Diarias';
	var $url ='farmacia/pedidos/';


	function Pedidos(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_id('20E',1);
	}

	function index() {
		redirect('farmacia/pedidos/datafilter');
	}

	function datafilter(){
		$this->rapyd->load('datagrid','datafilter');
		$this->rapyd->uri->keep_persistence();

		$columnas = array("a.codigoa", "d.barras", "b.descrip AS desca", "b.existen", "b.exmin", "b.exmax", "d.proveed", "sum(c.cantidad * (c.origen = '3I')) AS trimestral", "round((sum(c.cantidad * (c.origen = '3I'))/3),0) AS mensual", "round((sum(c.cantidad*(c.origen = '3I'))/6),0) AS quincenal", "round((sum(c.cantidad * (c.origen = '3I'))/12),0) AS semanal, exmax-if(existen<0,0,existen) AS pedir");
		$filter = new DataFilter('Productos vendidos en el d&iacute;a');

		$filter->db->select($columnas);

		$filter->db->from('sitems     AS a');
		$filter->db->join('sinv       AS b','a.codigoa=b.codigo');
		$filter->db->join('costos     AS c','a.codigoa=c.codigo');
		$filter->db->join('farmaxasig AS d','a.codigoa=d.abarras');

		$filter->db->where('b.existen <= b.exmin ');
		$filter->db->where('a.fecha = curdate() and c.fecha >= curdate() - 90');

		$filter->db->groupby('a.codigoa');
		$filter->db->having('pedir > 0');
		if(!$this->rapyd->uri->is_set('search')) $filter->db->where('a.fecha',date('Y-m-d'));

		$filter->fecha = new dateonlyField('Fecha', 'fecha');
		$filter->fecha->clause  ='where';
		$filter->fecha->db_name ='a.fecha';
		$filter->fecha->size    =10;
		$filter->fecha->operator='=';
		$filter->fecha->rule='required';
		$filter->fecha->insertValue=date('Y-m-d');

		$filter->buttons('reset','search');
		//$filter->submit('btn_cambio_2', 'Mandar pedido FarmaSIS', 'BR');
		$filter->build();

		function descheck($numero,$pedir){
			$data = array(
				'name'    => 'apedir[]',
				'id'      => $numero,
				'value'   => $numero.'#'.$pedir,
				'checked' => true);

			return form_checkbox($data);
		}

		function pinta($cana){
			$ncana=nformat($cana);
			return ($cana<0)? "<b style='color:red'>$ncana</b>": $ncana;
		}

		$seltod='Seleccionar <a id="todos" href=# >Todos</a> <a id="nada" href=# >Ninguno</a> <a id="alter" href=# >Invertir</a>';

		$grid = new DataGrid($seltod);
		$grid->use_function('descheck','pinta');
		$grid->order_by('desca','asc');
		$grid->per_page = 400;

		//$grid->column_orderby('C&oacute;digo','codigoa','control');
		$grid->column('Pedir' ,'<descheck><#barras#>|<#pedir#></descheck>');
		$grid->column_orderby('Barras'  ,'barras','barras');
		$grid->column_orderby('Descripci&oacute;n'   ,'desca','desca');
		$grid->column('Trimestral','<nformat><#trimestral#>|0</nformat>','align=\'right\'' );
		$grid->column('Mensual',   '<nformat><#mensual#>|0</nformat>',   'align=\'right\'' );
		$grid->column('Quincenal', '<nformat><#quincenal#>|0</nformat>', 'align=\'right\'' );
		$grid->column('Semanal',   '<nformat><#semanal#>|0</nformat>',   'align=\'right\'' );
		$grid->column('Actual',    '<pinta><#existen#>|0</pinta>',       'align=\'right\'' );
		$grid->column('Min' ,      '<nformat><#exmin#>|0</nformat>',     'align=\'center\'');
		$grid->column('Max' ,      '<nformat><#exmax#>|0</nformat>',     'align=\'center\'');
		$grid->column('Sugerido','pedir','align=\'right\'');

		$grid->build();

		//$grid->column('Rango' ,'[<nformat><#exmin#></nformat>-<nformat><#exmax#></nformat>]' ,'align=\'center\'');

		if($grid->recordCount>0){
			$tabla=$grid->output.form_submit('mysubmit', 'Mandar pedido a FarmaSIS');
			//echo $grid->db->last_query();
		}else{
			$tabla='';
		}

		$script ='<script type="text/javascript">
		$(document).ready(function() {
			$("#todos").click(function() { $("#apedir").checkCheckboxes();   });
			$("#nada").click(function()  { $("#apedir").unCheckCheckboxes(); });
			$("#alter").click(function() { $("#apedir").toggleCheckboxes();  });

			$(\'input[name="btn_cambio_2"]\').click(function() {
				var md = document.getElementById("apedir");
				md.submit();
			});
		});</script>';

		$data['content'] = $filter->output;
		$data['content'].= form_open('farmacia/pedidos/guardapedido',array('id'=>'apedir')).$tabla.form_close();
		$data['script']  = $script;
		$data['head']    = script('jquery-1.2.6.pack.js');
		$data['head']   .= script('plugins/jquery.checkboxes.pack.js');
		$data['head']   .= $this->rapyd->get_head();
		$data['title']   = heading('Compras a droguerias');
		$this->load->view('view_ventanas', $data);
	}

	function guardapedido(){
		$error=$cana=0;
		$farmaxDB=$this->load->database('farmax',true);
		$farmaxDB->simple_query('INSERT INTO npfac VALUES(null, now())');
		$num=$farmaxDB->insert_id();

		$apedir=$this->input->post('apedir');
		foreach($apedir as $pedir){
			$arr=explode('#',$pedir);
			$data= array('barras'=>trim($arr[0]),'numero'=>$num,'cana'=>$arr[1]);
			$sql = $farmaxDB->insert_string('apedir', $data);

			$ban = $farmaxDB->simple_query($sql);
			if($ban==false){ memowrite($sql,'farmaciapedidos'); $error++; }
			$cana++;
		}

		if($cana>0){
			$mSQL='UPDATE inventarios AS a JOIN apedir AS b ON a.barras=b.barras SET b.descrip=a.descrip WHERE b.numero='.$num;
			$ban = $farmaxDB->simple_query($mSQL);
			if($ban==false){ memowrite($mSQL,'farmaciapedidos'); $error++; }
		}

		if($error==0){
			$msj='El pedido ha sido guardado bajo el n&uacute;mero <b>'.$num.'</b> Dirijase al '.anchor($this->_farmaurl(),'FarmaSIS').' para completar el proceso'.br();
		}else{
			$msj='Hubo un error guardando el pedido, se generar&oacute;n centinelas';
		}

		$data['content'] = $msj;
		$data['title']   = heading('Enviar pedido a la drogueria');
		$this->load->view('view_ventanas', $data);
	}

	function pedidofalla(){
		$tema = 'proteo';
		$data['head']  = style('themes/'.$tema.'/'.$tema.'.css');
		$data['head'] .= phpscript('nformat.js');
		$data['head'] .= script('jquery-min.js');
		$data['head'] .= script('plugins/jquery.numeric.pack.js');
		$data['head'] .= script('jquery-ui.custom.min.js');
		$data['head'] .= script('jquery-impromptu.js');
		$data['head'] .= style('impromptu/default.css');
		$data['head'] .= style('themes/ui.jqgrid.css');
		$data['head'] .= script('i18n/grid.locale-sp.js');
		$data['head'] .= script('jquery.jqGrid.min.js');
		$data['head'] .= script('datagrid/datagrid.js');
		$data['head'] .= script('jquery.layout.js');

		$script  = '';
		$content = array();

		$data['content'] = $this->load->view('view_farmax_pedido', $content,true);
		$data['content'] = $this->defgrid();
		$data['script']  = $script;
		$data['title']   = heading('Compras a droguerias');
		$this->load->view('view_ventanas', $data);

	}

	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'    => "'center'",
			'frozen'   => 'true',
			'width'    => 60,
			'editable' => $editar,
			'search'   => 'false'
		));


		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
		));


		$grid->addField('barras');
		$grid->label('Barras');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
		));


		$grid->addField('descrip');
		$grid->label('Descripcion');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
		));


		$grid->addField('cana');
		$grid->label('Cana');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 0 }'
		));


		$grid->addField('ventas');
		$grid->label('Ventas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 0 }'
		));


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];} ');
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

	function especial(){
		$this->rapyd->load('datafilter2','datagrid','fields');

		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');

		$script='
		$(document).ready(function(){
			$(".inputnum").numeric(".");
			$("#depto").change(function(){
				depto();
				$.post("'.$link2.'",{ depto:$(this).val() },function(data){$("#linea").html(data);})
				$.post("'.$link3.'",{ linea:"" },function(data){$("#grupo").html(data);})
			});
			$("#linea").change(function(){
				linea();
				$.post("'.$link3.'",{ linea:$(this).val() },function(data){$("#grupo").html(data);})
			});
			$("#grupo").change(function(){
				grupo();
			});
			$("#sinvprecioc").submit(function() {
				return confirm("Se van a actualizar todos los precios en pantalla \nEstas seguro de que quieres seguir??");
			});
			depto();
			linea();
			grupo();

			$(\'input[name^="monto"]\').keyup(function(){
				var val=$(this).val();
				alert(val);

			});
		});

		function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}
			else{
				$("#nom_depto").attr("disabled","");
			}
		}

		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}
			else{
				$("#nom_linea").attr("disabled","");
			}
		}

		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}
			else{
				$("#nom_grupo").attr("disabled","");
			}
		}';

		$filter = new DataFilter2($this->titp);

		$select=array(
			'IF(formcal=\'U\',ultimo,IF(a.formcal=\'P\',pond,IF(formcal=\'S\',standard,GREATEST(ultimo,pond)))) AS costo',
			'a.existen','a.marca','a.tipo','a.id',
			'TRIM(codigo) AS codigo',
			'a.descrip','precio1','precio2','precio3','precio4','b.nom_grup','b.grupo','a.barras',
			'c.descrip AS nom_linea','c.linea','d.descrip AS nom_depto','d.depto AS depto',
			'a.base1','a.base2','a.base3','a.base4','e.cobeca','e.dronena','e.drolanca','e.mafarta'
		);

		$filter->db->select($select);
		$filter->db->from('sinv AS a');
		$filter->db->join('grup AS b','a.grupo=b.grupo');
		$filter->db->join('line AS c','b.linea=c.linea');
		$filter->db->join('dpto AS d','c.depto=d.depto');
		$filter->db->join('droguerias.inventarios AS e','a.barras=e.barras','left');
		$filter->db->where('a.activo','S');
		$filter->script($script);

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo-> size=15;
		$filter->codigo->group = "Uno";

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip-> size=30;
		$filter->descrip->group = "Uno";

		$filter->depto2 = new inputField("Departamento", "nom_depto");
		$filter->depto2->db_name="d.descrip";
		$filter->depto2 -> size=5;
		$filter->depto2->group = "Dos";

		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, CONCAT(depto,'-',descrip) descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$filter->depto->in="depto2";
		$filter->depto->group = "Dos";
		$filter->depto->style='width:190px;';

		$filter->linea = new inputField("Linea", "nom_linea");
		$filter->linea->db_name="c.descrip";
		$filter->linea -> size=5;
		$filter->linea->group = "Dos";

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="c.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$filter->linea2->in="linea";
		$filter->linea2->group = "Dos";
		$filter->linea2->style='width:190px;';

		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, CONCAT(linea,'-',descrip) descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo2 = new inputField("Grupo", "nom_grupo");
		$filter->grupo2->db_name="b.nom_grup";
		$filter->grupo2 -> size=5;
		$filter->grupo2->group = "Dos";

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$filter->grupo->in="grupo2";
		$filter->grupo->group = "Dos";
		$filter->grupo->style='width:190px;';

		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, CONCAT(grupo,'-',nom_grup) nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option("","Seleccione un Departamento primero");
		}

		$filter->marca = new dropdownField('Laboratorio', "marca");
		$filter->marca->option('','Todas');
		$filter->marca->options("SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca");
		$filter->marca->style='width:220px;';
		$filter->marca->group = "Dos";

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#id#></raencode>','<#id#>');

		$grid = new DataGrid('');
		$grid->order_by('id');
		$grid->per_page = 40;

		$monto = new inputField('Monto', 'monto');
		$monto->grid_name='monto[<#barras#>]';
		$monto->status   ='modify';
		$monto->size     =8;
		$monto->css_class='inputnum';

		$grid->column_orderby('C&oacute;digo','barras','barras');
		$grid->column_orderby('Descripci&oacute;n','descrip','descrip');
		$grid->column_orderby('Laboratorio','marca','marca');
		$grid->column('Monto' , $monto  ,'align=\'right\'');
		$grid->column('Dias'  , '<span id=dias_<#barras#>></span>','align=\'right\'');
		$grid->column('Costo'     ,'<nformat><#costo#></nformat>'   ,'align=right');
		$grid->column('Existencia','<nformat><#existen#></nformat>' ,'align=right');
		$grid->column('Merida'    ,'<nformat><#cobeca#></nformat>'  ,'align=right');
		$grid->column('Dronena'   ,'<nformat><#dronena#></nformat>' ,'align=right');
		$grid->column('Drolanca'  ,'<nformat><#drolanca#></nformat>','align=right');
		$grid->column('Mafarta'   ,'<nformat><#mafarta#></nformat>' ,'align=right');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);

	}

	function _farmaurl($opt='farmax'){
		$uri='drogueria/pedidos';
		$url=reduce_double_slashes($_SERVER['HTTP_HOST'].'/'.$opt.'/'.$uri);
		$url=prep_url($url);
		return $url;
	}

	function instalar(){
		if(!$this->db->table_exists('fallaped')){
			$mSQL="CREATE TABLE `fallaped` (
				`id` INT(10) NULL AUTO_INCREMENT,
				`codigo` VARCHAR(15) NULL,
				`barras` VARCHAR(15) NULL,
				`descrip` VARCHAR(45) NULL,
				`cana` INT(11) NULL,
				`ventas` INT(11) NULL,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `codigo` (`codigo`)
			)
			COMMENT='pedidos a droguerias por fallas'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}
	}
}
