<?php
class Publicidad extends Controller {

	function Publicidad(){
		parent::Controller();
		$this->id_modulo='91A';
		$this->load->library('rapyd');
		$this->load->library('path');
		$path=new Path();
		$path->setPath($this->config->item('uploads_dir'));
		$path->append('publicidad');
		$this->upload_path =$path->getPath().'/';
		$this->rel_path=reduce_double_slashes(str_replace($this->config->item('base_url'),'',$this->upload_path));
		$this->write=is_writable($this->rel_path);
		if(!is_writable($this->rel_path)){
			show_error('No se puede escribir en el directorio '.$this->rel_path.', debe ajustar los permisos');
		}
	}

	function index(){
		redirect('supervisor/publicidad/filteredgrid');
	}

	function filteredgrid(){
		$this->datasis->modulo_id($this->id_modulo,1);
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter('Filtro de Publicidades', 'publicidad');

		$filter->descrip= new inputField('Descripci&oacute;n','descrip');

		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor('supervisor/publicidad/dataedit/show/<#id#>','<#archivo#>');

		$grid = new DataGrid('Lista de publicidades guardadas');
		$grid->order_by('id','asc');
		$grid->per_page=15;

		$grid->column_orderby('Archivo'            ,$uri     ,'archivo');
		$grid->column_orderby('Color de Fondo'     ,'bgcolor','bgcolor');
		$grid->column_orderby('Descripci&oacute;n' ,'descrip','descrip');
		$grid->column_orderby('Probabilidad'       ,'prob'   ,'prob','align=\'center\'');

		$grid->add('supervisor/publicidad/dataedit/create');
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Publicidad</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->datasis->modulo_id($this->id_modulo,1);
		$this->rapyd->load('dataedit');

		$edit = new DataEdit('Carga de Publicidad','publicidad');
		$edit->back_url = site_url('supervisor/publicidad/filteredgrid');

		$edit->archivo  = new uploadField('Adjunto', 'archivo');
		$edit->archivo->upload_path = $this->upload_path;
		$edit->archivo->allowed_types = 'jpg|gif|swf|png';
		$edit->archivo->append('Puede adjuntar jpg,gif,png y swf');

		$edit->bgcolor = new colorpickerField('Color de Fondo', 'bgcolor');
		//$edit->bgcolor = new inputField("Color de Fondo", "bgcolor");
		$edit->bgcolor->maxlength =7;
		$edit->bgcolor->size = 9;

		$edit->prob = new inputField('Probabilidad de aparicion', 'prob');
		$edit->prob->css_class='inputnum';
		$edit->prob->maxlength =8;
		$edit->prob->size = 6;
		//$edit->prob->rule="";

		$edit->descrip = new textareaField('Descripci&oacute;n', 'descrip');
		$edit->descrip->rows = 5;

		$edit->buttons('modify','save','undo','back','delete');
		$edit->build();

		$data['content'] = $edit->output; 
		$data['title']   = '<h1>Registro de publicidad</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function obtener($habia=null){
		$this->load->helper('file');

		if(!empty($habia))
			$where=" WHERE archivo<>'$habia'";
		else
			$where='';
		$tot=$this->datasis->dameval('SELECT SUM(prob) FROM publicidad $where');
		if(empty($tot) or is_null($tot)) $tot=1;
		$mSQL="SELECT archivo AS nombre,prob/$tot AS rang FROM publicidad $where ORDER BY id";

		$query = $this->db->query($mSQL);
		if ($query->num_rows() > 0){
			$aleatorio=rand(0,100)/100;
			if ($query->num_rows() > 0){
				$init=0;
				foreach ($query->result() as $row){
					$init+=$row->rang;
					if ($aleatorio<=$init){
						break;
					}
				}
				$arch=$row->nombre;
			}
			if(file_exists($this->rel_path.$arch)){
				$extension = substr(strrchr($arch, '.'), 1);
				switch ($extension){
				case 'swf':
					$retval= $this->_swf($this->upload_path.$arch);
					break;
				default:
					$retval= $this->_image($this->upload_path.$arch);
				}
			}else{
				$arch=site_url('supervisor/logo/traer/logo.jpg');
				$retval= $this->_image($arch);
			}
		}else{
			$arch=site_url('supervisor/logo/traer/logo.jpg');
			$retval= $this->_image($arch);
		}
		echo $retval;
	}

	function _swf($location){
		if (empty($location)) return;
		$retval ='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="32" height="32">';
		$retval.='<param name="movie" value="'.$location.'" />';
		$retval.='<param name="quality" value="high" />';
		$retval.='<embed id="_ppro" src="'.$location.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
		$retval.='</object>';
		return $retval;
	}

	function _image($file, $alt = '#', $attributes = null){
		$retval = '<img id="_ppro" src="'.$file.'" '. (isset($attr) ? $attr : null) .' alt="'. $alt .'" title="'. $alt .'" ';
		if (is_array($attributes)){
			foreach ($attributes as $key => $value) $retval .= "$key=\"$value\" ";
		}
		$retval .= "/>";
		return $retval;
	}

	function instalar(){
		$mSQL="CREATE TABLE `publicidad` (
		  `id` bigint(20) unsigned NOT NULL auto_increment,
		  `archivo` varchar(100) default NULL,
		  `bgcolor` varchar(7) default NULL,
		  `prob` float unsigned default NULL,
		  `estampa` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		  `descrip` varchar(200) default NULL,
		  PRIMARY KEY  (`id`),
		  UNIQUE KEY `id` (`id`,`archivo`),
		  KEY `id_2` (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}
}