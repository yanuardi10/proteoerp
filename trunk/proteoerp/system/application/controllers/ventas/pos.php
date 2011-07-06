<?php
class Pos extends Controller {

	function Pos(){
		parent::Controller(); 
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(122,1);-
	}

	function index(){
		$this->rapyd->load('dataobject','datadetails');

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

		$query = $this->db->query("SELECT tipo,nombre FROM tarjeta ORDER BY tipo");
		foreach ($query->result() as $row){
			$sfpa[$row->tipo]=$row->nombre;
		}

		$tban['']='Banco';
		$query = $this->db->query("SELECT cod_banc,nomb_banc FROM tban WHERE cod_banc<>'CAJ' ORDER BY nomb_banc");
		foreach ($query->result() as $row){
			$tban[$row->cod_banc]=$row->nomb_banc;
		}

		$conten=array();
		$conten['sfpa']  = $sfpa;
		$conten['tban']  = $tban;
		$data['content'] = $this->load->view('view_pos', $conten,true);
		$data['title']   = '';
		$data['head']    = style('redmond/jquery-ui-1.8.1.custom.css');
		$data['head']   .= style('ui.jqgrid.css');
		$data['head']   .= style('ui.multiselect.css');
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('interface.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$data['head']   .= script('jquery.layout.js');
		$data['head']   .= script('i18n/grid.locale-sp.js');
		$data['head']   .= script('ui.multiselect.js');
		$data['head']   .= script('jquery.jqGrid.min.js');
		$data['head']   .= script('jquery.tablednd.js');
		$data['head']   .= script('jquery.contextmenu.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');
		$data['head']   .= phpscript('nformat.js');

		$this->load->view('view_ventanas_sola', $data);
	}

	// Busca Productos para autocomplete
	function buscasinv(){
		$mid = $this->input->post('q');
		$qdb=$this->db->escape('%'.$mid.'%');

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="SELECT TRIM(descrip) AS descrip, TRIM(codigo) AS codigo, precio1 AS precio, iva,existen
			FROM sinv WHERE codigo LIKE $qdb OR descrip LIKE  $qdb OR barras LIKE $qdb
			ORDER BY descrip LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = '('.$row['codigo'].') '.$row['descrip'].' '.$row['precio'].' Bs. - '.$row['existen'];
					$retArray['codigo']  = $row['codigo'];
					$retArray['precio']  = $row['precio'];
					$retArray['descrip'] = $row['descrip'];
					//$retArray['descrip'] = wordwrap($row['descrip'], 25, '<br />');
					$retArray['iva']     = $row['iva'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}

	// Busca Clientes para autocomplete
	function buscascli(){
		$mid = $this->input->post('q');
		$qdb=$this->db->escape('%'.$mid.'%');

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci
				FROM scli WHERE rifci LIKE $qdb
				ORDER BY rifci LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['rifci'];
					$retArray['label']   = '('.$row['rifci'].') '.$row['nombre'];
					$retArray['nombre']  = $row['nombre'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
	}

	//Crea un cliente
	function creascli(){
		$nombre=$this->input->post('sclinombre');
		$rifci =$this->input->post('sclirifci');

		$data = array('nombre' => $nombre,
					  'fiscal' => $nombre,
					  'rifci'  => $rifci);
		$str = $this->db->insert_string('scli', $data);
	}
}