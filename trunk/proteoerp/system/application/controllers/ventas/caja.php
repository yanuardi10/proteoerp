<?php require_once(BASEPATH.'application/controllers/validaciones.php');

class Caja extends validaciones {

	function Caja(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(136,1);
	}

	function index(){
		redirect('ventas/caja/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter('Filtro Caja', 'caja');

		$filter->caja = new inputField('Caja', 'caja');
		$filter->caja->size=5;

		$filter->ubica = new inputField("Ubicaci&oacute;n", "ubica");
		$filter->ubica->size=35;

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('ventas/caja/dataedit/show/<#caja#>','<#caja#>');
		
		$grid = new DataGrid('Lista de Cajas');
		$grid->order_by('caja','asc');
		$grid->per_page = 7;

		$grid->column('Caja',$uri);
		$grid->column('Ubicaci&oacute;n','ubica');
		$grid->column('Status', 'status');
		$grid->column('Descontar de almac&eacute;n', 'almacen');
		$grid->column('Impresora Puerto','impre');
		$grid->column('Lector &Oacute;ptico Puerto','lector');
		$grid->column('Gaveta Puerto','gaveta');
		$grid->column('Display Puerto','display');

		$grid->add("ventas/caja/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cajas</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){ 
		$this->rapyd->load("dataedit");
		$this->rapyd->uri->keep_persistence();
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit('Edici&oacute;n de caja','caja');
		$edit->back_url = site_url('ventas/caja/filteredgrid');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');

		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->caja = new inputField('N&uacute;mero de caja', 'caja');
		$edit->caja->rule = 'trim|required|callback_chexiste';
		$edit->caja->mode = 'autohide';
		$edit->caja->maxlength=3;
		$edit->caja->size = 4;
		$edit->caja->css_class='inputnum';

		$edit->ubica = new inputField('Ubicaci&oacute;n', 'ubica');
		$edit->ubica->append('Puede colocar la direcci&oacute;n IP de la caja');
		$edit->ubica->maxlength=30;
		$edit->ubica->rule='trim|strtoupper|callback_ip_caja';

		$edit->status = new dropdownField("Status", "status");
		$edit->status->option("C","Cerrado");
		$edit->status->option("A","Abierto");
		$edit->status->rule='required';
		$edit->status->style="width:150";

		$edit->factura = new inputField("Pr&oacute;xima Factura","factura");
		$edit->factura->rule = "trim";
		$edit->factura->maxlength=6;
		$edit->factura->size = 7;

		$edit->egreso  = new inputField("Pr&oacute;ximo Retiro en caja","egreso");
		$edit->egreso->rule = "trim";
		$edit->egreso->maxlength=6;
		$edit->egreso->size = 7;

		$edit->ingreso = new inputField("Pr&oacute;ximo egreso en caja","ingreso");
		$edit->ingreso->rule = "trim";
		$edit->ingreso->maxlength=6;
		$edit->ingreso->size = 7;

		$edit->almacen = new dropdownField("Almacen", "almacen");
		$edit->almacen->option("","");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides"); 
		$edit->almacen->style="width:150px";
		
		$opt=array('impre'=>'Impresora','lector'=>'Lector','gaveta'=>'Gaveta','display'=>'Display');
		foreach($opt AS $qu=>$grupo){
			$obj=$qu;
			$edit->$obj = new dropdownField("Puerto", $obj);
			$edit->$obj->options(array("NO/C"=>"NO CONECTADO","LP1"=> "LPT1","LP2"=>"LPT2","COM1"=>"COM1","COM2"=>"COM2"));
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'baud';
			$edit->$obj = new inputField("Baud Rate",$obj);
			$edit->$obj->size = 6;
			$edit->$obj->maxlength=5;
			$edit->$obj->css_class='inputnum';
			$edit->$obj->rule = "trim";
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'parid';
			$edit->$obj = new dropdownField("Pariedad", $obj);
			$edit->$obj->options(array("N"=>"NONE","E"=> "EVEN","O"=>"ODD"));
			$edit->$obj->maxlength=1;
			$edit->$obj->size = 2;
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'long';
			$edit->$obj = new dropdownField("Longitud", $obj);
			$edit->$obj->options(array("8"=> "8 BITS","7"=>"7 BITS"));
			$edit->$obj->maxlength=1;
			$edit->$obj->size = 2;
			$edit->$obj->group=$grupo;

			$obj=$qu{0}.'stop';
			$edit->$obj = new dropdownField("Bit de parada", $obj);
			$edit->$obj->options(array("1"=> "1 BIT","2"=>"2 BIT"));
			$edit->$obj->maxlength=1;
			$edit->$obj->size = 2;
			$edit->$obj->group=$grupo;
		}
		for ($i=1;$i<=5;$i++){
			$obj='cont'.$i;
			$edit->$obj = new inputField("Codigo ASCII",$obj);
			$edit->$obj->css_class='inputnum';
			$edit->$obj->maxlength=3;
			$edit->$obj->size=4;
			if ($i!=1) $edit->$obj->in='cont1';
		}
		$edit->almacen = new dropdownField("Almac&eacute;n", "almacen");
		$edit->almacen->option("","");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Cajas</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	
	function _post_insert($do){
		$codigo=$do->get('caja');
		$ubica=$do->get('ubica');
		$status=$do->get('status');
		logusu('caja',"CAJA $codigo UBICACION $ubica STATUS $status CREADA");
	}
	function _post_update($do){
		$codigo =$do->get('caja');
		$ubica  =$do->get('ubica');
		$status =$do->get('status');
		logusu('caja',"CAJA $codigo UBICACION $ubica STATUS $status MODIFICADA");
	}
	function _post_delete($do){
		$codigo=$do->get('caja');
		$ubica=$do->get('ubica');
		$status=$do->get('status');
		logusu('caja',"CAJA $codigo UBICACION $ubica STATUS $status ELIMINADA");
	}
	
	//VALIDACIONES
	function chexiste($codigo){
		$codigo=$this->input->post('caja');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM caja WHERE caja='$codigo'");
		if ($chek > 0){
			$ubica=$this->datasis->dameval("SELECT ubica FROM caja WHERE caja='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para la caja $ubica");
			return FALSE;
		}else {
			return TRUE;
		}
	}

	function ip_caja($ubica){
		$numero=$this->rapyd->uri->get_edited_id();
		if($this->rapyd->uri->is_set('update'))
			return $this->_ipval($ubica,$numero);
		else
			return $this->_ipval($ubica);
	}

	function _ipval($ubica,$numero=null){
		if(preg_match("/^([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\\.([0-9]{1,2}|1[0-9]{2}|2[0-4][0-9]|25[0-5])){3}$/", $ubica)>0){
			if(!empty($numero)) $where = " AND caja!=".$this->db->escape($numero); else $where='';
			$mSQL="SELECT COUNT(*) FROM caja WHERE ubica='$ubica' $where";
			$cant=$this->datasis->dameval($mSQL);
			if($cant>0){
				$this->validation->set_message('ip_caja', "La ip dada en el campo <b>%s</b> ya fue asignada a otro registro");
				return FALSE;
			}
		}
		return TRUE;
	}

	function limpia_ip($ip){
		$ip=trim($ip);
		$ip=preg_replace('/\.0+/','.',$ip);
		return str_replace('..','.0.',$ip);
	}

}
?>