<?php
class acdatasis extends Controller {
	var $mModulo='VIEW_TMENUSACC';
	var $titp='Accesos a los Modulo';
	var $tits='Accesos a los Modulo';
	var $url ='supervisor/acdatasis/';

	function acdatasis(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
	}

	function index(){
		//redirect('supervisor/acdatasis/filteredgrid');
		redirect($this->url.'jqdatag');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		function ractivo($acceso,$codigo){
			if($acceso=='S'){
				$retorna = form_checkbox($codigo, 'accept', TRUE);
			}else{
				$retorna = form_checkbox($codigo, 'accept',FALSE);
			}
			return $retorna ;
		}

		$filter = new DataFilter('Seleccione el usuario');
		$filter->db->select(array('b.modulo','b.codigo','a.usuario','a.usuario as value','a.acceso','b.titulo','b.ejecutar'));
		$filter->db->from('sida AS a');
		$filter->db->join('tmenus AS b','a.modulo=b.codigo');
		$filter->db->orderby('b.modulo');

		$filter->usuario = new  dropdownField('Usuario','usuario');
		$filter->usuario->options("SELECT us_codigo as value,CONCAT_WS(' - ', us_codigo, us_nombre) as codigo FROM usuario WHERE supervisor='N' ORDER BY us_nombre");
		$filter->usuario->style='width:250px;';
		$filter->usuario->rule='required';

		$filter->buttons('reset','search');
		$filter->build();

		if($this->rapyd->uri->is_set('search') AND $filter->is_valid()){

			$usr  =$filter->usuario->newValue;
			$dbusr=$this->db->escape($usr);
			$mSQL="INSERT IGNORE INTO sida SELECT '$usr',b.codigo,'N'  FROM sida AS a RIGHT JOIN tmenus AS b ON a.modulo=b.codigo AND a.usuario=$dbusr WHERE a.modulo IS NULL";
			$this->db->simple_query($mSQL);

			$grid = new Datagrid('Lista de accesos');
			$action = "javascript:window.location='".site_url("supervisor/acdatasis/copia/$usr/")."'";
			$grid->button('btn_copy', 'Copiar de otro usuario', $action, 'TR');

			$grid->use_function('ractivo');
			$link=site_url('/supervisor/acdatasis/activar');
			//$grid->per_page = 15;

			$grid->column('M&oacute;dulo','modulo');
			$grid->column('Nombre'  ,'titulo');
			$grid->column('Acceso'  ,'<ractivo><#acceso#>|<#codigo#>|</ractivo>','align="center"');
			$grid->column('Ejecutar','ejecutar');
			$grid->build();
			$tabla=$grid->output;
			//echo $grid->db->last_query();

			$url=site_url('supervisor/acdatasis/activar');
			$data['script']='<script type="text/javascript">
			$(document).ready(function() {
				$("form :checkbox").click(function () {
				usr=$("#usuario").attr("value");
				$.ajax({
						type: "POST",
						url: "'.$url.'",
						data: "codigo="+this.name+"&usuario="+usr,
						success: function(msg){
							if (msg==0)
							alert("Ocurrio un problema");
						}
					});
				}).change();
			});
			</script>';
		}else{
			$tabla='';
		}

		$data['content'] = $filter->output.form_open('').$tabla.form_close();
		$data['title']   = heading('Acceso de Usuario en DataSIS');
		$data['head']    = script('jquery.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function activar(){
		$usuario=$this->input->post('usuario');
		$codigo=$this->input->post('codigo');
		$mSQL = "UPDATE sida SET acceso=IF(acceso='S','N','S') WHERE modulo=$codigo AND usuario = '$usuario'";
		echo $this->db->simple_query($mSQL);
	}

	function copia($usua=null){
		$this->rapyd->load('datafilter','datagrid');
		$usuario=$usua;

		function ractivo($acceso,$codigo){
			if($acceso=='S'){
				$retorna = form_checkbox($codigo, 'accept', TRUE);
			}else{
				$retorna = form_checkbox($codigo, 'accept',FALSE);
			}
			return $retorna ;
		}

		$filter = new DataFilter('Seleccione el usuario del que se van a copiar los accesos');
		$filter->db->select(array('b.modulo','b.codigo',"a.usuario","a.usuario as value","a.acceso","b.titulo")); 
		$filter->db->from('sida AS a');
		$filter->db->join('tmenus AS b','a.modulo=b.codigo');
		$filter->db->orderby('b.modulo');

		$filter->usuario = new  dropdownField("Colocar a $usuario los mismos accesos de ",'usuario');
		$filter->usuario->options("SELECT us_codigo AS value,CONCAT_WS('  - ', us_codigo, us_nombre) AS codigo FROM usuario WHERE supervisor='N' ORDER BY us_codigo");
		$filter->usuario->style='width:250px;';
		$filter->usuario->rule='required';

		$filter->buttons('reset','search');
		$filter->build();

		if($this->rapyd->uri->is_set('search') AND $filter->is_valid()){
			$usr=$filter->usuario->newValue;
			$dbusr=$this->db->escape($usr);
			$mSQL="INSERT IGNORE INTO sida SELECT $dbusr,b.codigo,'N'  FROM sida AS a RIGHT JOIN tmenus AS b ON a.modulo=b.codigo AND a.usuario=$dbusr WHERE a.modulo IS NULL";
			$this->db->simple_query($mSQL);

			$grid = new Datagrid('Lista de accesos a copiar');
			$action = "javascript:window.location='".site_url("supervisor/acdatasis/copiar/$usr/$usuario")."'";
			$grid->button('btn_copy', 'Guardar', $action, 'TR');

			$grid->use_function('ractivo');
			$link=site_url('/supervisor/acdatasis/activar');
			//$grid->per_page = 15;

			$grid->column('M&oacute;dulo','modulo');
			$grid->column('Nombre','titulo');
			$grid->column('Acceso', "<ractivo><#acceso#>|<#codigo#>|</ractivo>",'align="center"');
			$grid->build();
			$tabla=$grid->output;
			//echo $grid->db->last_query();

			$url=site_url('supervisor/acdatasis/activar');
			$data['script']='<script type="text/javascript">
			$(document).ready(function() {
			$("form :checkbox").click(function () {
				usr=$("#usuario").attr("value");
				$.ajax({type: "POST",
				url: "'.$url.'",
				data: "codigo="+this.name+"&usuario="+usr,
				success: function(msg){
					if (msg==0)
						alert("Ocurrio un problema");
					}
			});
		}).change();
			});
			</script>';
		}else{
			$tabla='';
		}

		$data['content'] = $filter->output.form_open('').$tabla.form_close();
		$data['title']   = heading('Copiar Accesos de Usuario en DataSIS');
		$data['head']    = script('jquery.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function copiar($usr,$usuario){
		$mSQL_1 = "DELETE FROM sida WHERE usuario = '$usuario'";
		$this->db->simple_query($mSQL_1);
		$mSQL_2 = "INSERT INTO `sida` (`usuario`,`modulo`,`acceso`) SELECT usuario='OTRO', modulo, acceso from sida WHERE usuario = '$usr'";
		$this->db->simple_query($mSQL_2);
		$mSQL_3 = "UPDATE sida SET usuario='$usuario' WHERE usuario='0'";
		$this->db->simple_query($mSQL_3);
		redirect('supervisor/acdatasis/filteredgrid/');
	}




	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grid'] = $grid->deploy();

		$grid1Id = '#newapi'. $param['grid']['gridname'];

		$bodyscript = '
<script type="text/javascript">
$(function() {
	$( "input:submit, a, button", ".otros" ).button();
});

jQuery("#a1").click( function(){
	var id = jQuery("'. $grid1Id.'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("'. $grid1Id.'").jqGrid(\'getRowData\',id);
		window.open(\'/proteoerp/formatos/ver/VIEW_TMENUSACC/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
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
	<td>
		<table width="100%"><tr>
			<td style="font-size:16px;font-weight:bold;">Usuario:</td><td>&nbsp;'.
				$this->datasis->llenaopciones("SELECT us_codigo, CONCAT(us_codigo,' ', us_nombre) FROM usuario WHERE supervisor='N' ORDER BY us_codigo", false, $id='usuario' )
			.'</td>
		</tr></tr>
			<td style="font-size:16px;font-weight:bold;">Copiar de:</td><td>&nbsp;'.
				$this->datasis->llenaopciones("SELECT us_codigo, CONCAT(us_codigo,' ', us_nombre) FROM usuario WHERE supervisor='N' ORDER BY us_codigo", true, $id='copia' )
			.'</td>
		</tr></table>
	</td>
	</tr>
	<tr>
		<td><div class="tema1"><table id="titulos"></table></div></td>
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

		$funciones = '
	jQuery("#titulos").jqGrid({
		datatype: "local",
		height: \'360\',
		colNames:[\'Id\',\'Modulo\',\'Nombre\'],
		colModel:[
			{name:\'id\',    index:\'id\',     hidden:true},
			{name:\'modulo\',index:\'modulo\', width:60},
			{name:\'nombre\',index:\'nombre\', width:200}
		],
		multiselect: false,
		hiddengrid: false,
		width: 375,
		caption: "Reportes",
		ondblClickRow: function(id, row, col, e){ 
			var ret = $("#titulos").getRowData(id);
			jQuery("'.$grid1Id.'").jqGrid(\'setGridParam\',{url:"'.site_url($this->url.'getdata/').'/"+ret.modulo+"/"+$("#usuario").val(), page:1});
			jQuery("'.$grid1Id.'").trigger("reloadGrid");
		}
	});
	'.$this->datasis->menuMod().'

	for(var i=0;i<=datamenu.length;i++) jQuery("#titulos").jqGrid(\'addRowData\',i+1,datamenu[i]);
';


		$onclick1 = '
			,ondblClickRow: function(id){
				grid.editRow(id, {mtype:\'POST\'});
				return;
			}
		';

		$param['WestPanel']  = $WestPanel;
		$param['onclick1']   = $onclick1;
		$param['WestSize']   = 390;
		$param['funciones']  = $funciones;
		//$param['EastPanel']  = $EastPanel;
		$param['SouthPanel'] = $SouthPanel;
		$param['listados']   = $this->datasis->listados('VIEW_TMENUSACC', 'JQ');
		$param['otros']      = $this->datasis->otros('VIEW_TMENUSACC', 'JQ');
		$param['tema1']      = 'darkness';
		$param['anexos']     = 'anexos1';
		$param['bodyscript'] = $bodyscript;
		$param['tabs']       = false;
		$param['encabeza']   = $this->titp;
		
		$this->load->view('jqgrid/crud',$param);
	}

	//***********************************
	//Definicion del Grid y la Forma
	//***********************************
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
			'search'   => 'false',
			'hidden'   => 'true'
		));

		$grid->addField('modulo');
		$grid->label('Modulo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 10 }',
		));

		$grid->addField('secu');
		$grid->label('Sec.');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'false',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
		));

		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 60 }',
		));


		$grid->addField('acceso');
		$grid->label('Acceso');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'false',
			'editable'      => 'true',
			'width'         => 40,
			'edittype'      => "'checkbox'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{  value: "S:N" }',
		));

		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 40,
			'edittype'      => "'text'",
			'hidden'        => 'true',
			'editoptions'   => "{ readonly: 'readonly'}"
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('390');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('-');
		$grid->setFormOptionsA('-');
		$grid->setAfterSubmit("-");

		$grid->setonSelectRow('
				function(id){
					if(id && id !== lastsel2){
						jQuery(gridId1).jqGrid(\'saveRow\',lastsel2);
						jQuery(gridId1).jqGrid(\'editRow\',id,true);
						lastsel2=id;
					}
				      
				}
		');

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(false);
		$grid->setSearch(false);
		$grid->setRowNum(100);
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

	/*********************************************
	* Busca la data en el Servidor por json
	*/
	function getdata()
	{
		$grid       = $this->jqdatagrid;
		$modulo = $this->uri->segment(4);
		$rs = '';
		if ( $modulo) {
			$usuario = $this->uri->segment(5);
			if ( $modulo == 'MENUDTS') {
				$mSQL  = "select a.codigo id, a.modulo, a.secu, a.titulo nombre, b.acceso, b.usuario ";
				$mSQL .= "from tmenus a left join sida b on a.codigo = b.modulo ";
				$mSQL .= "where a.modulo <> 'MENUINT' and a.modulo regexp '[0-9]'  ";
				$mSQL .= "and b.usuario=".$this->db->escape($usuario)." ";
				//$mSQL .= "and a.modulo LIKE ".$this->db->escape($modulo."%");
				$mSQL .= "order by modulo,secu";
			} else {
				$mSQL  = "select a.codigo id, a.modulo, a.secu, a.titulo nombre, b.acceso, b.usuario ";
				$mSQL .= "from tmenus a left join sida b on a.codigo = b.modulo ";
				$mSQL .= "where a.modulo <> 'MENUINT' and not a.modulo regexp '[0-9]' and a.titulo not in ('Prox','Ante','Busca','Tabla') ";
				$mSQL .= "and b.usuario=".$this->db->escape($usuario)." ";
				$mSQL .= "and a.modulo LIKE ".$this->db->escape($modulo."%");
				$mSQL .= "order by modulo,secu";
			}
			$response   = $grid->getDataSimple($mSQL);
			$rs = $grid->jsonresult( $response);
		}
		echo $rs;
	}

	/***********************************************
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
//			if(false == empty($data)){
//				$this->db->insert('view_tmenusacc', $data);
//				echo "Registro Agregado";
//				logusu('VIEW_TMENUSACC',"Registro ????? INCLUIDO");
//			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$id       = $this->input->post('id');
			$usuario  = $this->input->post('usuario');
			unset($data['usuario']);
			$this->db->where('modulo',  $id);
			$this->db->where('usuario', $usuario);
			$this->db->update('sida', $data);
			logusu('SIDA',"Registro $id, $usuario  MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM view_tmenusacc WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
//				$this->db->simple_query("DELETE FROM view_tmenusacc WHERE id=$id ");
//				logusu('VIEW_TMENUSACC',"Registro ????? ELIMINADO");
//				echo "Registro Eliminado";
			}
		};
	}



}
?>