<?php
class Recep extends Controller {
	var $titp='Receptores';
	var $tits='Receptor';
	var $url ='taller/recep/';

	function Recep(){
		parent::Controller();
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(216,1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'recep');

		$filter->recep = new inputField('C&oacute;digo','recep');
		$filter->recep->rule      ='max_length[8]';
		$filter->recep->size      =10;
		$filter->recep->maxlength =8;

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->clipro = new inputField('Cod. Cliente/Proveedor','clipro');
		$filter->clipro->rule      ='max_length[5]';
		$filter->clipro->size      =7;
		$filter->clipro->maxlength =5;

		$filter->refe = new inputField('Referencia','refe');
		$filter->refe->rule      ='max_length[8]';
		$filter->refe->size      =10;
		$filter->refe->maxlength =8;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#recep#></raencode>','<#recep#>');

		$grid = new DataGrid('');
		$grid->order_by('recep');
		$grid->per_page = 40;

		$grid->column_orderby('C&oacute;digo'         ,$uri                                          ,'recep'  ,'align="left"'  );
		$grid->column_orderby('Fecha'                 ,'<dbdate_to_human><#fecha#></dbdate_to_human>','fecha'  ,'align="center"');
		$grid->column_orderby('Cod. Cliente/Proveedor','clipro'                                      ,'clipro' ,'align="left"'  );
		$grid->column_orderby('Referencia'            ,'refe'                                        ,'refe'   ,'align="left"'  );
		$grid->column_orderby('Tipo'                  ,'tipo'                                        ,'tipo'   ,'align="left"'  );
		$grid->column_orderby('Observaci&oacute;n'    ,'observa'                                     ,'observa','align="left"'  );

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);

	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'recep');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->recep = new inputField('C&oacute;digo','recep');
		$edit->recep->rule      ='trim|required|unique';
		$edit->recep->size      =10;
		$edit->recep->maxlength =8;
		$edit->recep->mode      ='autohide';
		
		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule      ='max_length[8]';
		$edit->nombre->size      =40;
		$edit->nombre->maxlength =50;

		$edit->fecha = new dateonlyField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->clipro = new inputField('Cod. Cliente/Proveedor','clipro');
		$edit->clipro->rule='max_length[5]';
		$edit->clipro->size =7;
		$edit->clipro->maxlength =5;

		$edit->refe = new inputField('Referencia','refe');
		$edit->refe->rule='max_length[8]';
		$edit->refe->size =10;
		$edit->refe->maxlength =8;

		$edit->tipo = new inputField('Tipo','tipo');
		$edit->tipo->rule='max_length[2]';
		$edit->tipo->size =4;
		$edit->tipo->maxlength =2;

		$edit->observa = new textareaField('Observaci&oacute;n','observa');
		$edit->observa->rule='max_length[8]';
		$edit->observa->cols = 70;
		$edit->observa->rows = 4;

		$edit->status = new inputField('Estado','status');
		$edit->status->rule='max_length[2]';
		$edit->status->size =4;
		$edit->status->maxlength =2;

		$usr=$this->session->userdata('usuario');
		$edit->user = new autoUpdateField('user' ,$usr, $usr);
		
		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));

		$edit->origen = new inputField('Origen','origen');
		$edit->origen->rule='max_length[20]';
		$edit->origen->size =22;
		$edit->origen->maxlength =20;

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$script= '<script type="text/javascript" > 
		$(function() {
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
		});
		</script>';

		$data['content'] = $edit->output;
		$data['head']    = $this->rapyd->get_head();
		$data['script']  = script('jquery.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js');
		$data['script'] .= $script;
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);

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
		if (!$this->db->table_exists('recep')) {
			$mSQL="CREATE TABLE `recep` (
			  `recep` char(8) NOT NULL DEFAULT '',
			  `fecha` date DEFAULT NULL,
			  `clipro` varchar(5) DEFAULT NULL,
			  `refe` char(8) DEFAULT NULL,
			  `tipo` char(2) DEFAULT NULL,
			  `observa` text,
			  `status` char(2) DEFAULT NULL,
			  `user` varchar(50) DEFAULT NULL,
			  `estampa` timestamp NULL DEFAULT NULL,
			  `origen` varchar(20) DEFAULT NULL,
			  PRIMARY KEY (`recep`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}
		$this->db->simple_query("ALTER TABLE `recep`  ADD COLUMN `nombre` VARCHAR(50) NULL DEFAULT NULL");
	}
}
?>
