<?php
class Pos extends Controller {

	function Pos(){
		parent::Controller(); 
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(122,1);-
	}

	function index(){
		$this->rapyd->load('dataobject','datadetails');

		//print_r(get_defined_constants());

		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre', 
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n',
			'tipo'=>'Tipo'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','rifci'=>'rifci',
					'dire11'=>'direc'),
		'titulo'  =>'Buscar Cliente',
		'script'  => array('post_modbus_scli()'));
		$boton =$this->datasis->modbus($mSCLId);


		$conten=array();
		$data['content'] = $this->load->view('view_pos', $conten,true);
		//$data['title']   = heading('Punto de ventas');
		$data['head']    = style('redmond/jquery-ui-1.8.1.custom.css');
		$data['head']   .= style('ui.jqgrid.css');
		$data['head']   .= style('ui.multiselect.css');
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('interface.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('jquery.layout.js');
		$data['head']   .= script('i18n/grid.locale-sp.js');
		$data['head']   .= script('ui.multiselect.js');
		$data['head']   .= script('jquery.jqGrid.min.js');
		$data['head']   .= script('jquery.tablednd.js');
		$data['head']   .= script('jquery.contextmenu.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');

		$this->load->view('view_ventanas_sola', $data);
	}

	function buscasinv(){
		$mSQL='SELECT codigo,descrip,precio1 FROM sinv LIMIT 5';
		$query = $this->db->query($mSQL);
		echo json_encode($query->result_array());
	}
}