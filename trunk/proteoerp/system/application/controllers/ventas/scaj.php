<?php require_once(BASEPATH.'application/controllers/validaciones.php');
//Cajeros
class Scaj extends validaciones {

	function scaj(){
		parent::Controller(); 
		$this->load->library("rapyd");
		$this->datasis->modulo_id(135,1);
	}
	function index(){
		redirect("ventas/scaj/filteredgrid");
	}
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Buscar", 'scaj');
		
		$filter->cajero = new inputField("Cajero","cajero");
		$filter->cajero->size=10;
		
		$filter->nombre = new inputField("Nombre", "nombre");
		$filter->nombre->size=30;
		
		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor("ventas/scaj/dataedit/show/<#cajero#>",'<#cajero#>');

		$grid = new DataGrid("Lista de Cajeros");
		$grid->order_by("nombre","asc");
		$grid->per_page = 10;

		//$grid->column_detail("C&oacute;digo","cajero", $uri, "size=14");
		$grid->column("C&oacute;digo",$uri);
		$grid->column("Nombre","nombre","nombre");
		$grid->column("Status","status");

		$grid->column("Almacen","almacen");
		$grid->column("Caja","caja");

		$grid->add("ventas/scaj/dataedit/create");
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Cajeros</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function dataedit(){
		$this->rapyd->load("dataedit");
		
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});
		';

		$edit = new DataEdit("Cajeros", "scaj");
		$edit->pre_process('delete','_pre_del');
		$edit->back_url = site_url("ventas/scaj/filteredgrid");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		
		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->codigo = new inputField("C&oacute;digo", "cajero");
		$edit->codigo->rule = "trim|strtoupper|required|callback_chexiste";
		$edit->codigo->mode = "autohide";
		$edit->codigo->maxlength=5;
		$edit->codigo->size = 8;
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->maxlength=30;
		$edit->nombre->rule="trim|strtoupper|required";	
		$edit->nombre->size =40;

		$edit->clave = new inputField("Clave", "clave");
		$edit->clave->maxlength=6;
		$edit->clave->rule="trim";
		$edit->clave->size = 7;

		$edit->status = new dropdownField("Status", "status");
		$edit->status->rule = "required";
		$edit->status->options(array("C"=> "Cerrado","A"=>"Abierto"));
		$edit->status->style="width:110px";	
		
		$edit->almacen = new dropdownField("Almacen", "almacen");
		$edit->almacen->option("","Seleccionar");
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");
		$edit->almacen->rule='required';
		$edit->almacen->style="width:150px";
		
		$edit->caja = new inputField("Caja", "caja");
		$edit->caja->size=4;
		$edit->caja->maxlength=2;
		$edit->caja->rule='trim|callback_ccaja';
		
		$edit->directo = new inputField("Directorio", "directo");
		$edit->directo->size=70;
		$edit->directo->rule="trim";
		$edit->directo->maxlength=60;
		
		$edit->mesai = new inputField("Mesa desde", "mesai");
		$edit->mesai->maxlength=4;
		$edit->mesai->size=6;
		$edit->mesai->rule="trim";
		$edit->mesai->group="Mesas";
		
		$edit->mesaf  = new inputField("Mesa hasta", "mesaf");
		$edit->mesaf->maxlength=4;
		$edit->mesaf->size=6;
		$edit->mesaf->rule="trim";
		$edit->mesaf->group="Mesas";
		
		$edit->horai  = new inputField("Desde", "horai");
		$edit->horai->maxlength=8;
		$edit->horai->size=10;
		$edit->horai->rule='trim|callback_chhora';
		$edit->horai->append('hh:mm:ss');
		$edit->horai->group="Hora feliz";
		
		$edit->horaf  = new inputField("Hasta", "horaf");
		$edit->horaf->maxlength=8;
		$edit->horaf->size=10;
		$edit->horaf->rule='trim|callback_chhora';
		$edit->horaf->append('hh:mm:ss');
		$edit->horaf->group="Hora feliz";	
		
		$edit->fechaa = new dateonlyfield("Fecha apertura", "fechaa");
		$edit->fechaa->maxlength=12;
		$edit->fechaa->size=15;		
		$edit->fechaa->group="Apertura";		
		
		$edit->horaa  = new inputField("Hora apertura", "horaa");
		$edit->horaa->maxlength=12;
		$edit->horaa->size=15;
		$edit->horaa->rule='trim|callback_chhora';
		$edit->horaa->append('hh:mm:ss');
		$edit->horaa->group="Apertura";
		
		$edit->apertura =new inputField("Monto apertura", "apertura");
		$edit->apertura->maxlength=12;
		$edit->apertura->size=14;		
		$edit->apertura->group="Apertura";
		$edit->apertura->css_class='inputnum';
		$edit->apertura->rule='trim|numeric';

		$edit->fechac = new dateonlyfield("Fecha cierre", "fechac");
		$edit->fechac->maxlength=12;
		$edit->fechac->size=14;
		$edit->fechac->group="Apertura";			
		
		$edit->horac  = new inputField("Hora cierre", "horac");
		$edit->horac->maxlength=8;
		$edit->horac->size=10;
		$edit->horac->rule='trim|callback_chhora';
		$edit->horac->append('hh:mm:ss');
		$edit->horac->group="Apertura";	
		
		$edit->cierre   =new inputField("Monto Cierre", "cierre");
		$edit->cierre->maxlength=12;
		$edit->cierre->size=15;
		$edit->cierre->group="Apertura";
		$edit->cierre->css_class='inputnum';
		$edit->cierre->rule='trim|numeric';
		
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;
		$data['title']   = "<h1>Cajeros</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
	function _pre_del($do) {
		$codigo=$do->get('codigo');

		$sum= $this->datasis->dameval("SELECT COUNT(*) FROM vieite WHERE cajero='$codigo'");
		$sum+=$this->datasis->dameval("SELECT COUNT(*) FROM fmay   WHERE cajero='$codigo'");
		$sum+=$this->datasis->dameval("SELECT COUNT(*) FROM sfac   WHERE cajero='$codigo'");

		if($sum==0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='No se puede borrar un cajero con ventas';
			return False;
		}else
			return True;
	}
	function _post_insert($do){
		$codigo=$do->get('cajero');
		$nombre=$do->get('nombre');
		$status=$do->get('status');
		logusu('scaj',"CAJERO $codigo NOMBRE $nombre STATUS $status CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('cajero');
		$nombre=$do->get('nombre');
		$status=$do->get('status');
		logusu('scaj',"CAJERO $codigo NOMBRE $nombre STATUS $status MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('cajero');
		$nombre=$do->get('nombre');
		$status=$do->get('status');
		logusu('scaj',"CAJERO $codigo NOMBRE $nombre STATUS $status ELIMINADO");
	}
	
	//VALIDACIONES
	function chexiste($codigo){
		$codigo=$this->input->post('cajero');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM scaj WHERE cajero='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM scaj WHERE cajero='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el cajero $nombre");
			return FALSE;
		}else {
  		return TRUE;
		}	
	}
	
	function ccaja($caja){
		$cant=$this->datasis->dameval("SELECT COUNT(*) FROM banc WHERE codbanc='$caja'");
		//$link=anchor('','aqui');
		if($cant==0){
			$this->validation->set_message('ccaja',"El codigo de caja '$caja' no existe");
			return FALSE;
		}
		return TRUE;
	}
	
	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `vieite` (
			`numero` char(8) default NULL,
			`fecha` date default '0000-00-00',
			`codigo` char(15) default NULL,
			`precio` decimal(10,2) default '0.00',
			`monto` decimal(18,2) default '0.00',
			`cantidad` decimal(12,3) default NULL,
			`impuesto` decimal(6,2) default '0.00',
			`costo` decimal(18,2) default '0.00',
			`almacen` char(4) default NULL,
			`cajero` char(5) default NULL,
			`caja` char(5) NOT NULL default '',
			`referen` char(15) default NULL,
			KEY `fecha` (`fecha`),
			KEY `codigo` (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='ventas por articulo'";
		$this->db->simple_query($mSQL);
		$mSQL="CREATE TABLE IF NOT EXISTS `fmay` (
			`fecha` date default NULL,
			`numero` varchar(8) NOT NULL default '',
			`presup` varchar(8) default NULL,
			`almacen` varchar(4) default NULL,
			`cod_cli` varchar(5) default NULL,
			`nombre` varchar(40) default NULL,
			`vence` date default NULL,
			`vende` varchar(5) default NULL,
			`stotal` decimal(17,2) default '0.00',
			`impuesto` decimal(17,2) default '0.00',
			`gtotal` decimal(17,2) default '0.00',
			`tipo` char(1) default NULL,
			`observa1` varchar(40) default NULL,
			`observa2` varchar(40) default NULL,
			`observa3` varchar(40) default NULL,
			`porcenta` decimal(17,2) default '0.00',
			`descuento` decimal(17,2) default '0.00',
			`cajero` varchar(5) default NULL,
			`dire1` varchar(30) default NULL,
			`dire2` varchar(30) default NULL,
			`rif` varchar(15) default NULL,
			`nit` varchar(15) default NULL,
			`exento` decimal(17,2) default '0.00',
			`transac` varchar(8) default NULL,
			`estampa` date default NULL,
			`hora` varchar(5) default NULL,
			`usuario` varchar(12) default NULL,
			`nfiscal` varchar(12) NOT NULL default '0',
			`tasa` decimal(19,2) default NULL,
			`reducida` decimal(19,2) default NULL,
			`sobretasa` decimal(17,2) default NULL,
			`montasa` decimal(17,2) default NULL,
			`monredu` decimal(17,2) default NULL,
			`monadic` decimal(17,2) default NULL,
			`cedula` varchar(13) default NULL,
			`dirent1` varchar(40) default NULL,
			`dirent2` varchar(40) default NULL,
			`dirent3` varchar(40) default NULL,
			PRIMARY KEY  (`numero`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}
}
?>