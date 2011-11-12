<?php
class ejecutasql extends Controller {
	var $titp='Scripts para ejecutar por usuarios';
	var $tits='Scripts para ejecutar por usuarios';
	var $url ='supervisor/ejecutasql/';

	function ejecutasql(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(216,1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		if(!$this->secu->es_logeado()){
			show_error('Debe loguearse para usar este modulo');
		}
		$this->rapyd->load('datafilter','datagrid');
		$this->load->helper('text');

		$filter = new DataFilter($this->titp, 'ejecutasql');
		$filter->db->where('usuario',$this->secu->usuario());

		$filter->tipo = new dropdownField('Tipo','tipo');
		$filter->tipo->option('','Todos');
		$filter->tipo->option('R','Restringido');
		$filter->tipo->option('A','Abierto');
		$filter->tipo->rule='max_length[1]';

		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->rule      ='max_length[100]';
		$filter->nombre->maxlength =100;

		$filter->script = new inputField('Script','script');

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'ejecutar/<raencode><#id#></raencode>','<#id#>');

		function pscript($script){
			$script=str_replace("\n",'',$script);
			return character_limiter($script,40);
		}

		$grid = new DataGrid('');
		$grid->use_function('pscript');
		$grid->order_by('id');
		$grid->per_page = 40;

		$grid->column_orderby('Id',$uri,'id','align="left"');
		//$grid->column_orderby('Usuario','usuario','usuario','align="left"');
		$grid->column_orderby('Tipo','tipo','tipo','align="left"');
		$grid->column_orderby('Nombre','nombre','nombre','align="left"');
		$grid->column_orderby('Script','<pscript><#script#></pscript>','script','align="left"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function  ejecutar($id=nul){
		$url='supervisor/ejecutasql/filteredgrid';
		$this->rapyd->uri->keep_persistence();
		$persistence = $this->rapyd->session->get_persistence($url, $this->rapyd->uri->gfid);
		$back= (isset($persistence['back_uri'])) ?$persistence['back_uri'] : $url;

		if(empty($id)){
			redirect($back);
		}else{
			$mSQL='SELECT script FROM ejecutasql WHERE id='.$this->db->escape($id);
			$mSQL=$this->datasis->dameval($mSQL);
			$salida=$this->_ejecuta($mSQL);

			$data['content'] = '<p>'.$salida.'</p>'.anchor($back,'Regresar');
			$data['head']    = $this->rapyd->get_head().script('jquery.pack.js');
			$data['title']   = heading($this->titp);
			$this->load->view('view_ventanas', $data);
		}
	}

	function _ejecuta($mSQL){
		$this->rapyd->load('datagrid');
		$link = @mysql_connect($this->db->hostname, $this->db->username, $this->db->password) or die('Error de coneccion');
		mysql_select_db($this->db->database,$link) or die('Base de datos no seleccionable');
		$result = mysql_query($mSQL,$link);

		if (!$result) {
			$salida=mysql_errno($link) . ": " . mysql_error($link);
		}else{
			if (is_resource($result)){
				$num_rows  = mysql_num_rows($result);
				$afectados = 0;
			}elseif(is_bool($result)){
				$num_rows  = 0;
				$afectados = mysql_affected_rows();
			}else{
				$num_rows  = 0;
				$afectados = 0;
			}

			if ($num_rows>0){
				$colunas   =mysql_num_fields($result);
				while ($row = mysql_fetch_assoc($result)){
 					$data[]=$row;
				}
				$grid = new DataGrid("Filas : $num_rows, Columnas : $colunas ,Afectados :$afectados",$data);
				$grid->per_page=100000;
				foreach ($data[0] as $campos=>$value)
					$grid->column($campos, $campos);
				$grid->build();
				$salida=$grid->output;

				if (stristr($mSQL, 'SELECT')){
					$mSQL2 = $this->encrypt->encode($mSQL);

					$salida.="<form action='/../../proteoerp/xlsauto/repoauto2/'; method='post'>
					<input size='100' type='hidden' name='mSQL' value='$mSQL2'>
					<input type='submit' value='Descargar a Excel' name='boton'/>
					</form>";

				}
			}elseif($afectados>0){
				$salida="Filas afectadas $afectados";
			}else{
				$salida='Esta consulta no genero resultados';
			}
		}
		return $salida;
	}

	function _pre_insert($do){
		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}

	function instalar(){
		if (!$this->db->table_exists('ejecutasql')) {
			$mSQL="CREATE TABLE `ejecutasql` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `usuario` varchar(12) DEFAULT NULL,
			  `tipo` varchar(1) DEFAULT NULL COMMENT 'Restringido, Abierto',
			  `nombre` varchar(100) DEFAULT NULL COMMENT 'Restringido, Abierto',
			  `script` text COMMENT 'Restringido, Abierto',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Escripts para ejecutar por usuario'";
			$this->db->simple_query($mSQL);
		}
	}

}
