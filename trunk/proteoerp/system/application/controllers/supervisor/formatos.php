<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class formatos extends validaciones {
	var $genesal=true;

	function formatos(){
		parent::Controller(); 
		$this->load->library('rapyd');
		//$this->datasis->modulo_id(307,1);
	}

	function index(){
		redirect('supervisor/formatos/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter('Filtro por Menu de Formatos','formatos');

		$filter->nombre = new inputField('Nombre', 'nombre');
		$filter->nombre->db_name='nombre';
		$filter->nombre->size=20;

		$filter->proteo = new inputField('Contenido Proteo','proteo');
		$filter->proteo->size=40;
		$filter->proteo->db_name='proteo';

		$filter->reporte = new inputField('Contenido Datasis','forma');
		$filter->reporte->size=40;
		$filter->reporte->db_name='forma';

		$filter->harbourd = new inputField('Contenido Harbourd','harbour');
		$filter->harbourd->size=40;
		$filter->harbourd->db_name='harbour';

		$filter->tcpdf = new inputField('Contenido TCPDF','tcpdf');
		$filter->tcpdf->size=40;
		$filter->tcpdf->db_name='tcpdf';

		$filter->buttons('reset','search');
		$filter->build();
		$uri  = anchor('supervisor/formatos/dataedit/show/<#nombre#>'   ,'<#nombre#>');
		$uri1 = anchor('supervisor/formatos/reporte/modify/<#nombre#>/' ,'Editar');
		$uri2 = anchor('supervisor/formatos/rdatasis/modify/<#nombre#>/','Editar');
		$uri3 = anchor('supervisor/formatos/rharbour/modify/<#nombre#>/','Editar');
		$uri4 = anchor('supervisor/formatos/observa/modify/<#nombre#>/' ,'Editar');
		$uri5 = anchor('supervisor/formatos/rtcpdf/modify/<#nombre#>/'  ,'Editar');

		$grid = new DataGrid('Lista de Menu de Formatos');
		$grid->order_by('nombre','asc');
		$grid->per_page = 15;

		$grid->column('Nombre',    $uri);
		$grid->column('Proteo'   ,$uri1);
		$grid->column('DataSIS'  ,$uri2);
		$grid->column('Harbour'  ,$uri3);
		$grid->column('TCPDF'    ,$uri5);

		$grid->add('supervisor/formatos/dataedit/create');
		$grid->build();
		//echo $grid->db->last_query();

		$url=site_url('supervisor/formatos/cactivo');
		$data['script']='<script type="text/javascript">
		$(document).ready(function() {
			$("form :checkbox").click(function () {
				$.ajax({
					type: "POST",
					url: "'.$url.'",
					data: "codigo="+this.name,
					success: function(msg){
					if (msg==0)
						alert("Ocurrio un problema");
					}
				});
		}).change();
		});
		</script>';
		$data['content'] = $filter->output.'<form>'.$grid->output.'</form>';
		$data['title']   = '<h1>Menu de Formatos</h1>';
		$data['head']    = script('jquery.pack.js').$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function observa($nombre){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Agregar Observacion','formatos');
		$edit->back_url = site_url('supervisor/formatos/filteredgrid');

		$edit->observa= new textareaField('', 'observa');
		$edit->observa->rows =3;
		$edit->observa->cols=70;

		$edit->buttons("modify", "save", "undo","back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = '<h1>Observaciones</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function reporte(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Proteo', 'formatos');
		$id=$edit->_dataobject->pk['nombre'];
		$script='$("#df1").submit(function(){
		$.post("'.site_url('supervisor/formatos/gajax_proteo/update/'.$id).'", {nombre: "'.$id.'", proteo: proteo.getCode()},
			function(data){
				alert("Reporte guardado" + data);
			},
			"application/x-www-form-urlencoded;charset='.$this->config->item('charset').'");
			return false;
		});';

		$edit->script($script,'modify');
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save=true;
		$edit->back_url = site_url('supervisor/formatos/filteredgrid');

		$edit->proteo= new htmlField('', 'proteo');
		$edit->proteo->rows =30;
		$edit->proteo->cols=130;
		$edit->proteo->css_class='codepress php linenumbers-on readonly-off';

		$edit->buttons('modify', 'save', 'undo','back');
		$edit->build();

		if($this->genesal){
			$data['content'] = $edit->output;
			$data['title']   = "<h1>Formato '$id'</h1>";
			$data['head']    = $this->rapyd->get_head().script('jquery.js');
			$data['head']   .= script('codepress/codepress.js');

			$this->load->view('view_ventanas_sola', $data);
		}else{
			echo $edit->error_string;
		}
	}

	function gajax_proteo(){
		header('Content-Type: text/html; '.$this->config->item('charset'));
		$this->genesal=false;
		$nombre=$this->input->post('nombre');
		$proteo=$this->input->post('proteo');

		if($proteo!==false and $nombre!==false){
			if(stripos($this->config->item('charset'), 'utf')===false){
				$_POST['nombre']=utf8_decode($nombre);
				$_POST['proteo']=utf8_decode($proteo);
			}
			$this->reporte();
		}
	}

	function rtcpdf(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Editar TCPDF', 'formatos');
		$id=$edit->_dataobject->pk['nombre'];
		$script='$("#df1").submit(function(){
		$.post("'.site_url('supervisor/formatos/gajax_rtcpdf/update/'.$id).'", {nombre: "'.$id.'", tcpdf: tcpdf.getCode()},
			function(data){
				alert("Reporte guardado" + data);
			},
			"application/x-www-form-urlencoded;charset='.$this->config->item('charset').'");
			return false;
		});';

		$edit->script($script,'modify');
		$edit->back_save  =true;
		$edit->back_cancel=true;
		$edit->back_cancel_save=true;
		$edit->back_url = site_url('supervisor/formatos/filteredgrid');

		$edit->tcpdf= new textareaField('', 'tcpdf');
		$edit->tcpdf->rows =30;
		$edit->tcpdf->cols=130;
		$edit->tcpdf->css_class='codepress php linenumbers-on readonly-off';
		$edit->tcpdf->when = array('create','modify');

		$edit->ttcpdf = new freeField('','free',$this->phpCode('<?php '.$edit->_dataobject->get('tcpdf').' ?>'));
		$edit->ttcpdf->when = array('show');

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		if($this->genesal){
			$data['content'] = $edit->output;
			$data['title']   = '<h1>Reporte TCPDF</h1>';
			$data['head']    = $this->rapyd->get_head().script('jquery.js');
			$data['head']   .= script('codepress/codepress.js');
			$this->load->view('view_ventanas_sola', $data);
		}else{
			echo $edit->error_string;
		}
	}

	function gajax_rtcpdf(){
		header('Content-Type: text/html; '.$this->config->item('charset'));
		$this->genesal=false;
		$nombre=$this->input->post('nombre');
		$tcpdf=$this->input->post('tcpdf');

		if($tcpdf!==false and $nombre!==false){
			if(stripos($this->config->item('charset'), 'utf')===false){
				$_POST['nombre']=utf8_decode($nombre);
				$_POST['tcpdf']=utf8_decode($tcpdf);
			}
			$this->rtcpdf();
		}
	}

	function rdatasis(){
		$nombre=$this->uri->segment(5);
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('DataSIS', 'formatos');
		$edit->back_url = site_url('supervisor/formatos/filteredgrid');

		$edit->reporte= new textareaField('', 'forma');
		$edit->reporte->rows =30;
		$edit->reporte->cols=130;
		$edit->reporte->rule = 'callback_eollw';

		$edit->buttons("modify", "save", "undo","back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Formato '$nombre'</h1>";
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);
	}

	function rharbour(){
		$nombre=$this->uri->segment(5);
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Harbour', 'formatos');
		$edit->back_url = site_url('supervisor/formatos/filteredgrid');

		$edit->reporte= new textareaField('', 'harbour');
		$edit->reporte->rows =30;
		$edit->reporte->cols=130;
		$edit->reporte->rule = 'callback_eollw';

		$edit->buttons('modify','save','undo','back');
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Formato '$nombre'</h1>";
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas_sola', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Formatos','formatos');
		$edit->back_url = site_url("supervisor/formatos/filteredgrid");

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule= 'strtoupper|required';
		$edit->nombre->size = 20;

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$data['content'] = $edit->output;
		$data['title']   = "<h1>Agregar Formatos</h1>";
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);  
	}

	function cactivo(){
		$codigo=$this->input->post('codigo');
		if(!empty($codigo)){
			$pk=explode('|',$codigo);
			$mSQL="UPDATE intrarepo SET activo=IF(activo='S','N','S') WHERE nombre='$pk[0]' AND modulo='$pk[1]'";
			echo $this->db->simple_query($mSQL);
		}else{
			echo 0;
		}
	}

	function _post_insert($do){
		$nombre=$do->get('nombre');
		$mSQL="INSERT IGNORE INTO `reportes` (nombre) VALUES ('$nombre')";
		$this->db->simple_query($mSQL);
		logusu('formatos',"CREADO EL REPORTE $nombre");
	}

	function _post_delete($do){
		$nombre=$do->get('nombre');
		$mSQL="DELETE FROM `reportes` WHERE `nombre`='$nombre'";
		$this->db->simple_query($mSQL);
		logusu('formatos',"BORRADO EL REPORTE $nombre");
	}

	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `intrarepo` (
		  `nombre` varchar(71) NOT NULL default '',
		  `modulo` varchar(10) NOT NULL default '',
		  `titulo` varchar(20) default NULL,
		  `mensaje` varchar(60) default NULL,
		  `activo` char(1) default 'S',
		  PRIMARY KEY  (`nombre`,`modulo`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}
}