<?php
class Partida extends Controller {
	
	function Partida(){
		parent::Controller(); 
		$this->load->helper('text');
		$this->load->library("rapyd");
	}

	function index(){
		$this->datasis->modulo_id(517,1);
		redirect("construccion/partida/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro de Partidas');
		$filter->db->from('obpa a');
		$filter->db->join('obgp b','a.grupo=b.grupo','LEFT');
		//$filter->db->orderby('a.codigo');

		$filter->codigo = new inputField('C&oacute;digo','codigo');
		$filter->codigo->size=15;

		$filter->descrip = new inputField('Descripci&oacute;n', 'descrip');
		$filter->descrip->size=25;

		$filter->buttons('reset','search');
		$filter->build();

		$uri  = anchor('construccion/partida/dataedit/show/<#codigo#>','<#codigo#>');
		$grid = new DataGrid('Listado de Partidas');
		$grid->per_page = 15;

		$grid->column('C&oacute;digo',$uri);
		$grid->column('Descripci&oacute;n','descrip');
		$grid->column('Grupo','nombre');
		$grid->column('Comisi&oacute;n','comision','align="right"');

		$grid->add('construccion/partida/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Partida</h1>';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		//$this->load->database('construc',TRUE);
		$this->rapyd->load("dataedit");
		$edit = new DataEdit("Partida", "obpa");
		$edit->back_url = site_url("construccion/partida/filteredgrid");

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->mode ="autohide";
		$edit->codigo->rule ="required|callback_chexiste";
		$edit->codigo->size =7;
		$edit->codigo->maxlength =4 ;

		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->rule ="strtoupper|required"; 
		$edit->descrip->size =50 ; 
		$edit->descrip->maxlength =40 ;
		
		$edit->grupo = new dropdownField("Grupo", "grupo");
		$edit->grupo->option("","");
		$edit->grupo->options('SELECT grupo, nombre FROM obgp order by nombre');
			
		$edit->comi = new inputField("Comisi&oacute;n", "comision");
		$edit->comi->size =8 ; 
		$edit->comi->maxlength =5 ;

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Partidas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_insert($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('nom_grup');
		logusu('obpa',"PARTIDA $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('descrip');
		logusu('obpa',"PARTIDA $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('grupo');
		$nombre=$do->get('descrip');
		logusu('obpa',"PARTIDA $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		$codigo=$this->input->post('grupo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM obpa WHERE grupo='$codigo'");
		if ($chek > 0){
			$grupo=$this->datasis->dameval("SELECT nombre FROM obpa WHERE grupo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el grupo $grupo");
			return FALSE;
		}else {
		return TRUE;
		}
	}

	function instalar(){
		$mSQL="CREATE TABLE `obpa` (
			 `codigo` char(4) NOT NULL DEFAULT '',
			`descrip` varchar(40) DEFAULT NULL,
			`grupo` char(4) DEFAULT NULL,
			`comision` decimal(5,2) DEFAULT NULL,
			`nomgrup` varchar(30) DEFAULT NULL,
			PRIMARY KEY (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);

		$mSQL="CREATE TABLE `obgp` (
			`grupo` char(4) NOT NULL DEFAULT '',
			`nombre` varchar(30) DEFAULT '0',
			PRIMARY KEY (`grupo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}
}
