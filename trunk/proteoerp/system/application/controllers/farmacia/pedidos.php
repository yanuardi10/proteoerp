<?php require_once(APPPATH.'/controllers/inventario/consultas.php');
class Pedidos extends Controller {
	var $mModulo='FALLAPED';
	var $titp='Modulo FALLAPED';
	var $tits='Modulo FALLAPED';
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

		/*$atts = array(
				'width'      => '800',
				'height'     => '600',
				'scrollbars' => 'yes',
				'status'     => 'yes',
				'resizable'  => 'yes',
				'screenx'    => '0',
				'screeny'    => '0'
			);*/

		$filter = new DataFilter('Productos vendidos en el d&iacute;a');
		$filter->db->select(array('a.codigoa','TRIM(b.barras) AS barras','a.desca', 'SUM(a.cana) AS venta','d.exmax - IF(d.existen<0,0,d.existen) AS pedir','d.exmin','d.exmax','d.existen'));
		$filter->db->from('sitems AS a');
		$filter->db->join('farmaxasig AS b','a.codigoa=b.abarras');
		$filter->db->join('sinv AS d','a.codigoa=d.codigo');
		$filter->db->groupby('a.codigoa');
		$filter->db->where('d.existen <= d.exmin ');
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
		$grid->column_orderby('Venta','<nformat><#venta#></nformat>','venta','align=\'right\'');
		$grid->column_orderby('Existencia','<pinta><#existen#></pinta>','existen','align=\'right\'');
		$grid->column('Rango' ,'[<nformat><#exmin#></nformat>-<nformat><#exmax#></nformat>]' ,'align=\'center\'');
		$grid->column_orderby('Pedido','pedir','cana','align=\'right\'');

		$grid->build();

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
