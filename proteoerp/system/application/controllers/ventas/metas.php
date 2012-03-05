<?php
class metas extends Controller{

	function metas(){
		parent::Controller();
		$this->load->library('rapyd');
	}

	function index(){
		redirect('ventas/metas/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter('Metas');
		$filter->db->select(array('a.*','b.nombre'));
		$filter->db->from('metas AS a');
		$filter->db->join('vend AS b','a.vendedor=b.vendedor');
		//$filter->db->join('sinv AS c','a.codigo=c.codigo');

		$filter->fecha = new dateonlyField('Fecha', 'fecha','m/Y');
		$filter->fecha->clause  ='where';
		$filter->fecha->db_name ='fecha';
		//$filter->fecha->insertValue = date('Y-m-d');
		$filter->fecha->operator = '=';
		$filter->fecha->dbformat = 'Ym';
		$filter->fecha->size     = 7;
		$filter->fecha->append(' mes/año');
		$filter->fecha->rule = 'required';

		$filter->codigo = new dropdownField('C&oacute;digo', 'codigo');
		$filter->codigo->option('','Todos');
		$filter->codigo->options('SELECT TRIM(clave),TRIM(clave) as valor FROM sinv GROUP BY clave');
		$filter->codigo->style = 'width:150px';

		$filter->vendedor = new dropdownField('Vendedor', 'vendedor');
		$filter->vendedor->db_name='a.vendedor';
		$filter->vendedor->option('','Todos');
		$filter->vendedor->options("SELECT vendedor, CONCAT(vendedor,'-',nombre) AS nom FROM vend WHERE tipo IN ('V','A') ORDER BY vendedor");

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('ventas/metas/dataedit/show/<#codigo#>/<#fecha#>/<#vendedor#>','<#fecha#>');

		$grid = new DataGrid('Lista de Metas');
		$grid->per_page=15;

		$grid->column_orderby('Fecha'   ,$uri      ,'fecha'   );
		$grid->column_orderby('Clave'  ,'codigo'  ,'codigo'  );
		//$grid->column_orderby('Descripci&oacute;n' ,'descrip'  ,'descrip'  );
		$grid->column_orderby('Cantidad','cantidad','cantidad','align="right"');
		$grid->column_orderby('Vendedor','<#vendedor#>-<#nombre#>','vendedor');

		$action = "javascript:window.location='" . site_url('ventas/metas/compara') . "'";
		$grid->button('btn_compa', 'Comparativo', $action, 'BL');

		$grid->add('ventas/metas/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = heading('Metas');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
		$this->rapyd->load('dataedit');

		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit('Metas','metas');

		$edit->back_url = site_url('ventas/metas/filteredgrid');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');

		$edit->fecha = new dateonlyField('Fecha', 'fecha','m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->dbformat='Ym';
		$edit->fecha->size=7;
		$edit->fecha->append('mes/año');
		$edit->fecha->rule = 'required';

		$edit->codigo = new dropdownField('Clave de producto', 'codigo');
		$edit->codigo->option('','');
		$edit->codigo->rule='required';
		$edit->codigo->options('SELECT TRIM(clave),TRIM(clave) AS valor FROM sinv GROUP BY clave');
		$edit->codigo->style = 'width:150px';
		$edit->codigo->rule  = 'required';

		$edit->cantidad = new inputField('Cantidad','cantidad');
		$edit->cantidad->size =12;
		$edit->cantidad->maxlength =12;
		$edit->cantidad->rule ='trim|required';
		$edit->cantidad->css_class='inputnum';

		$edit->vendedor = new dropdownField('Vendedor', 'vendedor');
		$edit->vendedor->option('','Seleccionar');
		$edit->vendedor->options("SELECT vendedor, CONCAT(vendedor,'-',nombre) AS nom FROM vend WHERE tipo IN ('V','A') ORDER BY vendedor");

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$data['content'] = $edit->output; 
		$data['title']   = heading('Metas');
		$data['head']    = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function compara(){
		$this->rapyd->load('datafilter','datagrid');
		$this->db->_escape_char='';
		$this->db->_protect_identifiers=false;

		function colum($diferen){
			if ($diferen<0)
				return ('<b style="color:red;">'.$diferen.'</b>');
			else
				return ('<b style="color:green;">'.$diferen.'</b>');
		}

		function dif($a,$b){
			return nformat($a-$b);
		}

		$base_process_uri= $this->rapyd->uri->implode_uri('base_uri','gfid','orderby');
		$filter = new DataForm($this->rapyd->uri->add_clause($base_process_uri, 'search'));
		$filter->title('Filtro');
		//$filter->attributes=array('onsubmit'=>'is_loaded()');

		$filter->fecha = new dateonlyField('Fecha', 'd.fecha','m/Y');
		$filter->fecha->clause  ='where';
		$filter->fecha->insertValue = date('mY');
		$filter->fecha->operator= '=';
		$filter->fecha->dbformat= 'Ym';
		$filter->fecha->size    =7;
		$filter->fecha->append('mes/año');
		$filter->fecha->rule = 'required';

		$filter->public = new checkboxField('Agrupado por Vendedor', 'public', '1','0');

		$filter->vendedor = new dropdownField('Vendedor', 'vendedor');
		$filter->vendedor->option('','Todos');
		$filter->vendedor->options("SELECT vendedor, CONCAT(vendedor,'-',nombre) AS nom FROM vend WHERE tipo IN ('V','A') ORDER BY vendedor");

		$filter->submit('btnsubmit','Descargar');
		$filter->build_form();

		if($this->rapyd->uri->is_set('search') AND $filter->is_valid()){

			$fecha    = $filter->fecha->newValue;
			$vendedor = $filter->vendedor->newValue;
			$agrupar  = $filter->public->newValue;
			$udia=days_in_month(substr($fecha,4),substr($fecha,0,4));

			$fechai=$fecha.'01';
			$fechaf=$fecha.$udia;

			$mSQL="(SELECT
			d.cantidad AS metasv, b.clave, c.vd AS vendedor, e.nombre as nombrev, 
			SUM(a.cana*b.peso*(d.tipo='P'))+ a.cana*(d.tipo='C')+ a.cana*(d.tipo='P')*(b.peso=0 OR b.peso IS NULL) AS ventas 
			FROM sitems AS a JOIN sinv AS b ON a.codigoa=b.codigo 
			JOIN sfac AS c ON a.tipoa=c.tipo_doc AND a.numa=c.numero 
			JOIN metas AS d ON b.clave=d.codigo AND d.fecha=$fecha 
			AND c.vd=d.vendedor JOIN vend AS e ON d.vendedor=e.vendedor 
			WHERE a.fecha >= $fechai AND a.fecha <= $fechaf 
			GROUP BY b.clave,c.vd) AS h";

			$grid = new DataGrid('Resultados');
			$grid->use_function('colum');
			$select=array('h.clave AS codigo','SUM(h.metasv) AS metas','SUM(h.ventas) AS ventas','SUM(h.ventas)-SUM(h.metasv) AS diferen','h.vendedor','h.nombrev');
			$grid->db->select($select);
			$grid->db->from($mSQL);

			if(!empty($vendedor)){
				$grid->db->where('h.vendedor',$vendedor);
			}else{
				$grid->db->orderby('h.vendedor');
			}

			if($agrupar=='0'){
				$grid->db->groupby('h.clave');
			}else{
				$grid->db->groupby('h.clave,h.vendedor');
			}

			if($agrupar=='0'){
				$grid->column('Codigo'     ,'codigo' );
				$grid->column('Venta'      ,'<nformat><#ventas#></nformat>'      ,"align='right'");
				$grid->column('Metas'      ,'<#metas#>',"align='right'");
				$grid->column('Diferencia' ,'<colum><nformat><#diferen#></nformat></colum>' ,"align='right'");
			}else{
				$grid->column('Codigo'     ,'codigo' );
				$grid->column('Vendedor'   ,'<#vendedor#>',"align='center'");
				$grid->column('Nombre'     ,'<#nombrev#>' ,"align='left'");
				$grid->column('Venta'      ,'<nformat><#ventas#></nformat>',"align='right'");
				$grid->column('Metas'      ,'<nformat><#metas#></nformat>' ,"align='right'");
				$grid->column('Diferencia' ,'<colum><nformat><#diferen#></nformat></colum>' ,"align='right'");
			}

			$grid->build();
			$tabla=$grid->output;
			//echo $grid->db->last_query();
		}else{
			$tabla='';
		}

		$data['content'] = $filter->output.$tabla;
		$data['title']   = heading('Estado de Metas');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		$mSQL="CREATE  TABLE IF NOT EXISTS `metas` (
		  `codigo` varchar(15),
		  `cantidad` decimal(12,3),
		  `fecha` int(10),
		  `vendedor` varchar(5),
		  `tipo` CHAR(2),
		  PRIMARY KEY  (`fecha`,`codigo`,`vendedor`)
		)";
		$rt=$this->db->simple_query($mSQL);
		var_dump($rt);
	}
}