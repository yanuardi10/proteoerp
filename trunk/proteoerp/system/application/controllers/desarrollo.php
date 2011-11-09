<?php
class Desarrollo extends Controller{

	function Desarrollo(){
		parent::Controller();
	}

	function index(){
		
	}

	function camposdb(){
		$db=$this->uri->segment(3);
		if($db===false){
			exit('Debe especificar en la uri la tabla');
		}
		$query = $this->db->query("DESCRIBE $db");

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$str='$data[\''.$row->Field."']";
				$str=str_pad($str,20);
				echo $str."='';\n";
			}
		}
	}

	function lcamposdb(){
		$db =$this->uri->segment(3);
		$pre=$this->uri->segment(4);
		if($pre!==FALSE)
			$ant="$pre.";
		else
			$ant='';
		if($db===false){
			exit('Debe especificar en la uri la tabla');
		}
		$query = $this->db->query("DESCRIBE $db");

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$str=$row->Field.",";
				echo $ant.$str;
			}
		}
	}

	function acamposdb(){
		$db =$this->uri->segment(3);
		$pre=$this->uri->segment(4);
		if($pre!==FALSE)
			$ant="$pre.";
		else
			$ant='';
		if($db===false){
			exit('Debe especificar en la uri la tabla');
		}
		$query = $this->db->query("DESCRIBE $db");

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$str=$row->Field.'","';
				echo $ant.$str;
			}
		}
	}

	function ccamposdb(){
		$db =$this->uri->segment(3);
		$pre=$this->uri->segment(4);
		if($pre!==FALSE)
			$ant="$pre.";
		else
			$ant='';
		if($db===false){
			exit('Debe especificar en la uri la tabla');
		}
		$query = $this->db->query("DESCRIBE $db");

		if ($query->num_rows() > 0){
			foreach ($query->result() as $row){
				$str="'$row->Field',";
				echo $ant.$str;
			}
		}
	}

	function genecrud($tabla=null,$s=true){
		if (empty($tabla) OR (!$this->db->table_exists($tabla))) show_error('Tabla no existe o faltan parametros');

		$crud ="\n\t".'function dataedit(){'."\n";
		$crud.="\t\t".'$this->rapyd->load(\'dataedit\');'."\n\n";
		$crud.="\t\t".'$edit = new DataEdit($this->tits, \''.$tabla.'\');'."\n\n";
		$crud.="\t\t".'$edit->back_url = site_url($this->url.\'filteredgrid\');'."\n\n";

		$crud.="\t\t".'$edit->post_process(\'insert\',\'_post_insert\');'."\n";
		$crud.="\t\t".'$edit->post_process(\'update\',\'_post_update\');'."\n";
		$crud.="\t\t".'$edit->post_process(\'delete\',\'_post_delete\');'."\n";
		$crud.="\t\t".'$edit->pre_process(\'insert\',\'_pre_insert\');'."\n";
		$crud.="\t\t".'$edit->pre_process(\'update\',\'_pre_update\');'."\n";
		$crud.="\t\t".'$edit->pre_process(\'delete\',\'_pre_delete\');'."\n";

		$crud.="\n";

		//$fields = $this->db->field_data($tabla);
		$mSQL="DESCRIBE $tabla";
		$query = $this->db->query("DESCRIBE $tabla");
		foreach ($query->result() as $field){

			if($field->Field=='usuario'){
				$crud.="\t\t".'$edit->usuario = new autoUpdateField(\'usuario\',$this->session->userdata(\'usuario\'),$this->session->userdata(\'usuario\'));'."\n\n";
			}elseif($field->Field=='estampa'){
				$crud.="\t\t".'$edit->estampa = new autoUpdateField(\'estampa\' ,date(\'Ymd\'), date(\'Ymd\'));'."\n\n";
			}elseif($field->Field=='hora'){
				$crud.="\t\t".'$edit->hora    = new autoUpdateField(\'hora\',date(\'H:i:s\'), date(\'H:i:s\'));'."\n\n";
			}elseif($field->Field=='id'){
				continue;
			}else{
				preg_match('/(?P<tipo>\w+)(\((?P<length>[0-9\,]+)\)){0,1}/', $field->Type, $matches);
				if(isset($matches['length'])){
					$def=explode(',',$matches['length']);
				}else{
					$def[0]=8;
				}

				if(strrpos($field->Type,'date')!==false){
					$input='date';
				}elseif(strrpos($field->Type,'text')!==false){
					$input= 'textarea';
				}else{
					$input='input';
				}

				$crud.="\t\t".'$edit->'.$field->Field.' = new '.$input."Field('".ucfirst($field->Field)."','$field->Field');\n";

				if(preg_match("/decimal/i",$field->Type)){
					$crud.="\t\t".'$edit->'.$field->Field."->rule='max_length[".$def[0]."]|numeric';\n";
					$crud.="\t\t".'$edit->'.$field->Field."->css_class='inputnum';\n";
				}elseif(preg_match("/integer|int/i",$field->Type)){
					$crud.="\t\t".'$edit->'.$field->Field."->rule='max_length[".$def[0]."]|integer';\n";
					$crud.="\t\t".'$edit->'.$field->Field."->css_class='inputonlynum';\n";
				}elseif(preg_match("/date/i",$field->Type)){
					$crud.="\t\t".'$edit->'.$field->Field."->rule='chfecha';\n";
				}else{
					$crud.="\t\t".'$edit->'.$field->Field."->rule='max_length[".$def[0]."]';\n";
				}

				if(strrpos($field->Type,'text')===false){
					$crud.="\t\t".'$edit->'.$field->Field.'->size ='.($def[0]+2).";\n";
					$crud.="\t\t".'$edit->'.$field->Field.'->maxlength ='.($def[0]).";\n";
				}else{
					$crud.="\t\t".'$edit->'.$field->Field."->cols = 70;\n";
					$crud.="\t\t".'$edit->'.$field->Field."->rows = 4;\n";
				}
				$crud.="\n";
			}
		}

		$crud.="\t\t".'$edit->buttons(\'modify\', \'save\', \'undo\', \'delete\', \'back\');'."\n";
		$crud.="\t\t".'$edit->build();'."\n\n";

		$crud.="\t\t".'$script= \'<script type="text/javascript" > '."\n";
		$crud.="\t\t".'$(function() {'."\n";
		$crud.="\t\t\t".'$(".inputnum").numeric(".");'."\n";
		$crud.="\t\t\t".'$(".inputonlynum").numeric();'."\n";
		$crud.="\t\t".'});'."\n";
		$crud.="\t\t".'</script>\';'."\n\n";

		$crud.="\t\t".'$data[\'content\'] = $edit->output;'."\n";
		$crud.="\t\t".'$data[\'head\']    = $this->rapyd->get_head();'."\n";
		$crud.="\t\t".'$data[\'script\']  = script(\'jquery.js\').script(\'plugins/jquery.numeric.pack.js\').script(\'plugins/jquery.floatnumber.js\');'."\n";
		$crud.="\t\t".'$data[\'script\'] .= $script;'."\n";
		$crud.="\t\t".'$data[\'title\']   = heading($this->tits);'."\n";
		$crud.="\t\t".'$this->load->view(\'view_ventanas\', $data);'."\n\n";
		$crud.="\t".'}'."\n";

		if($s){
			$data['content'] ='<pre>'.$crud.'</pre>';
			$data['head']    = '';
			$data['title']   =heading('Generador de crud');
			$this->load->view('view_ventanas_sola', $data);
		}else{
			return $crud;
		}
	}

	function genefilter($tabla=null,$s=true){
		if (empty($tabla) OR (!$this->db->table_exists($tabla))) show_error('Tabla no existe o faltan parametros');

		$crud ="\t".'function filteredgrid(){'."\n";
		$crud.="\t\t".'$this->rapyd->load(\'datafilter\',\'datagrid\');'."\n\n";
		$crud.="\t\t".'$filter = new DataFilter($this->titp, \''.$tabla.'\');'."\n\n";

		//$fields = $this->db->field_data($tabla);
		$mSQL="DESCRIBE $tabla";
		$query = $this->db->query("DESCRIBE $tabla");
		$key=array();
		foreach ($query->result() as $field){
				if($field->Key=='PRI')$key[]=$field->Field;

				if($field->Field=='id'){
					continue;
				}

				preg_match('/(?P<tipo>\w+)(\((?P<length>[0-9\,]+)\)){0,1}/', $field->Type, $matches);
				if(isset($matches['length'])){
					$def=explode(',',$matches['length']);
				}else{
					$def[0]=8;
				}

				if(strrpos($field->Type,'date')!==false){
					$input='date';
				}elseif(strrpos($field->Type,'text')!==false){
					$input= 'textarea';
				}else{
					$input='input';
				}

				$crud.="\t\t".'$filter->'.$field->Field.' = new '.$input."Field('".ucfirst($field->Field)."','$field->Field');\n";

				if(preg_match("/decimal|integer/i",$field->Type)){
					$crud.="\t\t".'$filter->'.$field->Field."->rule      ='max_length[".$def[0]."]|numeric';\n";
					$crud.="\t\t".'$filter->'.$field->Field."->css_class ='inputnum';\n";
				}elseif(preg_match("/date/i",$field->Type)){
					$crud.="\t\t".'$filter->'.$field->Field."->rule      ='chfecha';\n";
				}else{
					$crud.="\t\t".'$filter->'.$field->Field."->rule      ='max_length[".$def[0]."]';\n";
				}

				if(strrpos($field->Type,'text')===false){
					if($def[0]<80){
						$crud.="\t\t".'$filter->'.$field->Field.'->size      ='.($def[0]+2).";\n";
					}
					$crud.="\t\t".'$filter->'.$field->Field.'->maxlength ='.($def[0]).";\n";
				}else{
					$crud.="\t\t".'$filter->'.$field->Field."->cols = 70;\n";
					$crud.="\t\t".'$filter->'.$field->Field."->rows = 4;\n";
				}
				$crud.="\n";

		}

		$crud.="\t\t".'$filter->buttons(\'reset\', \'search\');'."\n";
		$crud.="\t\t".'$filter->build();'."\n\n";

		$a=$b='';
		foreach($key AS $val){
			$a.='<raencode><#'.$val.'#></raencode>';
			$b.='<#'.$val.'#>';
		}
		//$a=htmlentities($a);
		//$b=htmlentities($b);
		$crud.="\t\t".'$uri = anchor($this->url.\'dataedit/show/'.$a.'\',\''.$b.'\');'."\n\n";

		$crud.="\t\t".'$grid = new DataGrid(\'\');'."\n";
		$k=implode(',',$key);
		$crud.="\t\t".'$grid->order_by(\''.$k.'\');'."\n";
		$crud.="\t\t".'$grid->per_page = 40;'."\n\n";

		$c=0;
		foreach ($query->result() as $field){
			if($field->Key=='PRI') $key[]=$field->Field;

			$crud.="\t\t".'$grid->column_orderby(\''.ucfirst($field->Field).'\',';
			if($c==0){
				$crud.='$uri';
				$c++;
				$crud.=',\''.$field->Field.'\',\'align="left"\');'."\n";
			}else{
				$crud.='\'';
				if(strrpos($field->Type,'date')!==false){
					$crud.='<dbdate_to_human><#'.$field->Field.'#></dbdate_to_human>';
					$crud.='\',\''.$field->Field.'\',\'align="center"\');'."\n";
				}elseif(strrpos($field->Type,'double')!==false || strrpos($field->Type,'int')!==false || strrpos($field->Type,'decimal')!==false){
					$crud.='<nformat><#'.$field->Field.'#></nformat>';
					$crud.='\',\''.$field->Field.'\',\'align="right"\');'."\n";
				}else{
					$crud.=$field->Field;
					$crud.='\',\''.$field->Field.'\',\'align="left"\');'."\n";
				}
			}
		}

		$crud.="\n";
		$crud.="\t\t".'$grid->add($this->url.\'dataedit/create\');'."\n";
		$crud.="\t\t".'$grid->build();'."\n";
		$crud.="\n";

		$crud.="\t\t".'$data[\'filtro\']  = $filter->output;'."\n";
		$crud.="\t\t".'$data[\'content\'] = $grid->output;'."\n";
		$crud.="\t\t".'$data[\'head\']    = $this->rapyd->get_head().script(\'jquery.js\');'."\n";
		$crud.="\t\t".'$data[\'title\']   = heading($this->titp);'."\n";
		$crud.="\t\t".'$this->load->view(\'view_ventanas\', $data);'."\n\n";
		$crud.="\t".'}'."\n";

		if($s){
			$data['content'] ='<pre>'.$crud.'</pre>';
			$data['head']    = '';
			$data['title']   =heading('Generador de crud');
			$this->load->view('view_ventanas_sola', $data);
		}else{
			return $crud;
		}
	}

	function genepost($tabla=null,$s=true){
		if (empty($tabla) OR (!$this->db->table_exists($tabla))) show_error('Tabla no existe o faltan parametros');

		$crud="\n";
		$crud.="\t".'function _post_insert($do){'."\n";
		$crud.="\t\t".'$primary =implode(\',\',$do->pk);'."\n";
		$crud.="\t\t".'logusu($do->table,"Creo $this->tits $primary ");'."\n";
		$crud.="\t".'}'."\n\n";
		$crud.="\t".'function _post_update($do){'."\n";
		$crud.="\t\t".'$primary =implode(\',\',$do->pk);'."\n";
		$crud.="\t\t".'logusu($do->table,"Modifico $this->tits $primary ");'."\n";
		$crud.="\t".'}'."\n\n";
		$crud.="\t".'function _post_delete($do){'."\n";
		$crud.="\t\t".'$primary =implode(\',\',$do->pk);'."\n";
		$crud.="\t\t".'logusu($do->table,"Elimino $this->tits $primary ");'."\n";
		$crud.="\t".'}'."\n";

		if($s){
			$data['content'] ='<pre>'.$crud.'</pre>';
			$data['head']    = '';
			$data['title']   =heading('Generador de crud');
			$this->load->view('view_ventanas_sola', $data);
		}else{
			return $crud;
		}
	}

	function genepre($tabla=null,$s=true){
		if (empty($tabla) OR (!$this->db->table_exists($tabla))) show_error('Tabla no existe o faltan parametros');

		$crud="\n";
		$crud.="\t".'function _pre_insert($do){'."\n";
		$crud.="\t\t".'return true;'."\n";
		$crud.="\t".'}'."\n\n";
		$crud.="\t".'function _pre_update($do){'."\n";
		$crud.="\t\t".'return true;'."\n";
		$crud.="\t".'}'."\n\n";
		$crud.="\t".'function _pre_delete($do){'."\n";
		$crud.="\t\t".'return true;'."\n";
		$crud.="\t".'}'."\n";

		if($s){
			$data['content'] ='<pre>'.$crud.'</pre>';
			$data['head']    = '';
			$data['title']   =heading('Generador de crud');
			$this->load->view('view_ventanas_sola', $data);
		}else{
			return $crud;
		}
	}

	function geneinstalar($tabla=null,$s=true){
		if (empty($tabla) OR (!$this->db->table_exists($tabla))) show_error('Tabla no existe o faltan parametros');
		$row=$this->datasis->damerow("SHOW CREATE TABLE `$tabla`;");
		//Create Table

		$crud="\n";
		$crud.="\t".'function instalar(){'."\n";
		$crud.="\t\t".'if (!$this->db->table_exists(\''.$tabla.'\')) {'."\n";
		$crud.="\t\t\t".'$mSQL="'.str_replace("\n","\n\t\t\t",$row['Create Table']).'";'."\n";
		$crud.="\t\t\t".'$this->db->simple_query($mSQL);'."\n";
		$crud.="\t\t".'}'."\n";
		$crud.="\t".'}'."\n";

		if($s){
			$data['content'] ='<pre>'.$crud.'</pre>';
			$data['head']    = '';
			$data['title']   =heading('Generador de crud');
			$this->load->view('view_ventanas_sola', $data);
		}else{
			return $crud;
		}
	}

	function genehead($tabla=null,$s=true){
		if (empty($tabla) OR (!$this->db->table_exists($tabla))) show_error('Tabla no existe o faltan parametros');

		$crud="\n";
		$crud.='<?php'."\n";
		$crud.="class $tabla extends Controller {"."\n";
		$crud.="\t".'var $titp=\'Titulo Principal\';'."\n";
		$crud.="\t".'var $tits=\'Sub-titulo\';'."\n";
		$crud.="\t".'var $url =\''.$tabla.'/\';'."\n\n";
		$crud.="\t"."function $tabla(){"."\n";
		$crud.="\t\t".'parent::Controller();'."\n";
		$crud.="\t\t".'$this->load->library(\'rapyd\');'."\n";
		$crud.="\t\t".'//$this->datasis->modulo_id(216,1);'."\n";
		$crud.="\t\t".'$this->instalar();'."\n";
		$crud.="\t".'}'."\n\n";
		$crud.="\t".'function index(){'."\n";
		$crud.="\t\t".'redirect($this->url.\'filteredgrid\');'."\n";
		$crud.="\t".'}'."\n\n";

		if($s){
			$data['content'] ='<pre>'.$crud.'</pre>';
			$data['head']    = '';
			$data['title']   =heading('Generador de crud');
			$this->load->view('view_ventanas_sola', $data);
		}else{
			return $crud;
		}
	}

	function genefoot($tabla=null,$s=true){
		if (empty($tabla) OR (!$this->db->table_exists($tabla))) show_error('Tabla no existe o faltan parametros');

		$crud="\n";
		$crud.='}'."\n";
		$crud.='?>';

		if($s){
			$data['content'] ='<pre>'.$crud.'</pre>';
			$data['head']    = '';
			$data['title']   =heading('Generador de crud');
			$this->load->view('view_ventanas_sola', $data);
		}else{
			return $crud;
		}
	}

	function genetodo($tabla=null,$s=true){
		$crud='';
		$crud.=$this->genehead($tabla    ,false);
		$crud.=$this->genefilter($tabla  ,false);
		$crud.=$this->genecrud($tabla    ,false);
		$crud.=$this->genepre($tabla     ,false);
		$crud.=$this->genepost($tabla    ,false);
		$crud.=$this->geneinstalar($tabla,false);
		$crud.=$this->genefoot($tabla    ,false);

		$crud=htmlentities($crud);

		if($s){
			$data['content'] ='<pre>'.$crud.'</pre>';
			$data['head']    = '';
			$data['title']   =heading('Generador de crud');
			$this->load->view('view_ventanas_sola', $data);
		}else{
			return $crud;
		}
	}
}
?>