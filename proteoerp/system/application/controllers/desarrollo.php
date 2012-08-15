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


	// Genera las columnas para Extjs
	function extjs(){
		$db =$this->uri->segment(3);
		if($db===false){
			exit('Debe especificar en la uri la tabla');
		}
		$query = $this->db->query("DESCRIBE $db");
		$i = 0;
		if ($query->num_rows() > 0){
			$fields  = '';
			$columna = '';
			$campos  = '';
			foreach ($query->result() as $row){
				if ($i == 0 ){
					$str="'".$row->Field."'";
					$i = 1;
				} else {
					$str=",'".$row->Field."'";
				}
				$fields .= $str;

				$str = "{ header: ".str_pad("'".$row->Field."'",20).",  width: 60, sortable: true,  dataIndex: ".str_pad("'".$row->Field."'",20).", field: ";

				if ( $row->Type == 'date' or $row->Type == 'timestamp' ) {
					$str .= "{ type: 'date'       }, filter: { type: 'date'    }";
				} elseif ( $row->Type == 'date' or $row->Type == 'timestamp' ) {
					$str = "{ type: 'date'       }, filter: { type: 'date'    }";
				} elseif ( substr($row->Type,0,7) == 'decimal' or substr($row->Type,0,3) == 'int'  ) {
					$str .= "{ type: 'numberfield'}, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00')";
				} else {
					$str .= "{ type: 'textfield'  }, filter: { type: 'string'  }";
				}
				$columna .= $str."},<br>";


				$str = "{ fieldLabel: ".$row->Field.",  name: ".$row->Field.", width:100, labelWidth:60, ";

				if ( $row->Type == 'date' or $row->Type == 'timestamp' ) {
					$str .= "{ type: 'date'       }, filter: { type: 'date'    }";
				} elseif ( $row->Type == 'date' or $row->Type == 'timestamp' ) {
					$str = "xtype: 'datefield', format: 'd/m/Y', submitFormat: 'Y-m-d' ";
				} elseif ( substr($row->Type,0,7) == 'decimal' or substr($row->Type,0,3) == 'int'  ) {
					$str .= "xtype: 'numberfield', , hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00')";
				} else {
					$str .= "xtype: 'textfield' ";
				}
				$campos .= $str."},<br>";

			}
			echo "$fields<br>";
			echo "<br>$columna";
			echo "<br>$campos";
		}
	}
	
	
	function jqgrid(){
		$db =$this->uri->segment(3);
		if($db===false){
			exit('Debe especificar en la uri la tabla y el directorio "/tabla/directorio"');
		}
		$contro =$this->uri->segment(4);
		if($contro===false){
			$contro = 'CONTROLADOR';
		}
		
		$query = $this->db->query("DESCRIBE $db");
		$i = 0;
		if ($query->num_rows() > 0){
			$fields  = '';
			$columna = '<pre>';
			$param   = '';
			$campos  = '';
			$str = '';
			$tab1 = $this->mtab(1);
			$tab2 = $this->mtab(2);
			$tab3 = $this->mtab(3);
			$tab4 = $this->mtab(4);
			$tab5 = $this->mtab(5);

			$str .= 'class '.ucfirst($db).' extends Controller {'."\n";
			$str .= $tab1.'var $mModulo=\''.strtoupper($db).'\';'."\n";
			$str .= $tab1.'var $titp=\'Modulo '.strtoupper($db).'\';'."\n";
			$str .= $tab1.'var $tits=\'Modulo '.strtoupper($db).'\';'."\n";
			$str .= $tab1.'var $url =\''.$contro.'/'.$db.'/\';'."\n\n";

			$str .= $tab1.'function '.ucfirst($db).'(){'."\n";
			$str .= $tab2.'parent::Controller();'."\n";
			$str .= $tab2.'$this->load->library(\'rapyd\');'."\n";
			$str .= $tab2.'$this->load->library(\'jqdatagrid\');'."\n";
			$str .= $tab2.'//$this->datasis->modulo_nombre( $modulo, $ventana=0 );'."\n";
			$str .= $tab1.'}'."\n\n";

			$str .= $tab1.'function index(){'."\n";
			$str .= $tab2.'/*if ( !$this->datasis->iscampo(\''.$db.'\',\'id\') ) {'."\n";
			$str .= $tab3.'$this->db->simple_query(\'ALTER TABLE '.$db.' DROP PRIMARY KEY\');'."\n";
			$str .= $tab3.'$this->db->simple_query(\'ALTER TABLE '.$db.' ADD UNIQUE INDEX numero (numero)\');'."\n";
			$str .= $tab3.'$this->db->simple_query(\'ALTER TABLE '.$db.' ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)\');'."\n";
			$str .= $tab2.'};*/'."\n";
			$str .= $tab2.'$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );'."\n";
			$str .= $tab2.'redirect($this->url.\'jqdatag\');'."\n";

			$str .= $tab1.'}'."\n\n";
			
			$str .= $tab1.'//***************************'."\n";
			$str .= $tab1.'//Layout en la Ventana'."\n";
			$str .= $tab1.'//'."\n";
			$str .= $tab1.'//***************************'."\n";
			$str .= $tab1.'function jqdatag(){'."\n\n";
			$str .= $tab2.'$grid = $this->defgrid();'."\n";
			$str .= $tab2.'$param[\'grids\'][] = $grid->deploy();'."\n\n";

			$str .= $tab2.'$bodyscript = \''."\n";
			$str .= '&lt;script type="text/javascript"&gt;'."\n";

			$str .= '$(function() {'."\n";
			$str .= '	$( "input:submit, a, button", ".otros" ).button();'."\n";
			$str .= '});'."\n\n";

			$str .= 'jQuery("#a1").click( function(){'."\n";
			$str .= '	var id = jQuery("#newapi\'. $param[\'grids\'][0][\'gridname\'].\'").jqGrid(\\\'getGridParam\\\',\\\'selrow\\\');'."\n";
			$str .= '	if (id)	{'."\n";
			$str .= '		var ret = jQuery("#newapi\'. $param[\'grids\'][0][\'gridname\'].\'").jqGrid(\\\'getRowData\\\',id);'."\n";
			$str .= '		window.open(\\\'\'.base_url().\'formatos/ver/'.strtoupper($db).'/\\\'+id, \\\'_blank\\\', \\\'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\\\');'."\n";
			$str .= '	} else { $.prompt("&lt;h1&gt;Por favor Seleccione un Movimiento&lt;/h1&gt;");}'."\n";
			$str .= '});'."\n";
			$str .= '&lt;/script&gt;'."\n";
			$str .= '\';'."\n\n";

			$str .= $tab2.'#Set url'."\n";
			$str .= $tab2.'$grid->setUrlput(site_url($this->url.\'setdata/\'));'."\n\n";


			$str .= $tab2.'$WestPanel = \''."\n";
			$str .= '&lt;div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content"&gt;'."\n";
			$str .= '&lt;div class="anexos"&gt;'."\n\n";
			$str .= '&lt;table id="west-grid" align="center"&gt;'."\n";
			$str .= '	&lt;tr&gt;'."\n";
			$str .= '		&lt;td&gt;&lt;div class="tema1"&gt;&lt;table id="listados"&gt;&lt;/table&gt;&lt;/div&gt;&lt;/td&gt;'."\n";
			$str .= '	&lt;/tr&gt;'."\n";
			$str .= '	&lt;tr&gt;'."\n";
			$str .= '		&lt;td&gt;&lt;div class="tema1"&gt;&lt;table id="otros"&gt;&lt;/table&gt;&lt;/div&gt;&lt;/td&gt;'."\n";
//			$str .= '		&lt;table id="otros"&gt;&lt;/table&gt;'."\n";
			$str .= '	&lt;/tr&gt;'."\n";
			$str .= '&lt;/table&gt;'."\n\n";

			$str .= '&lt;table id="west-grid" align="center"&gt;'."\n";
			$str .= '	&lt;tr&gt;'."\n";
			$str .= '		&lt;td&gt;&lt;/td&gt;'."\n";
			$str .= '	&lt;/tr&gt;'."\n";
			$str .= '&lt;/table&gt;'."\n";
			$str .= '&lt;/div&gt;'."\n";
			
			$str .= "'.\n".'//		&lt;td&gt;&lt;a style="width:190px" href="#" id="a1"&gt;Imprimir Copia&lt;/a&gt;&lt;/td&gt;'."\n'";
			
			$str .= '&lt;/div> &lt;!-- #LeftPane --&gt;'."\n";
			$str .= '\';'."\n\n";

			$str .= $tab2.'$SouthPanel = \''."\n";
			$str .= '&lt;div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content"&gt;'."\n";
			$str .= '&lt;p>\'.$this->datasis->traevalor(\'TITULO1\').\'&lt;/p&gt;'."\n";
			$str .= '&lt;/div> &lt;!-- #BottomPanel --&gt;'."\n";
			$str .= '\';'."\n";

			$str .= $tab2.'$param[\'WestPanel\']  = $WestPanel;'."\n";
			$str .= $tab2.'//$param[\'EastPanel\']  = $EastPanel;'."\n";
			$str .= $tab2.'$param[\'SouthPanel\'] = $SouthPanel;'."\n";

			$str .= $tab2.'$param[\'listados\'] = $this->datasis->listados(\''.strtoupper($db).'\', \'JQ\');'."\n";
			$str .= $tab2.'$param[\'otros\']    = $this->datasis->otros(\''.strtoupper($db).'\', \'JQ\');'."\n";

			$str .= $tab2.'$param[\'temas\']     = array(\'proteo\',\'darkness\',\'anexos1\');'."\n";
			//$str .= $tab2.'$param[\'anexos\']    = \'anexos1\';'."\n";

			$str .= $tab2.'$param[\'bodyscript\'] = $bodyscript;'."\n";
			$str .= $tab2.'$param[\'tabs\'] = false;'."\n";
			$str .= $tab2.'$param[\'encabeza\'] = $this->titp;'."\n";
			$str .= $tab2.'$this->load->view(\'jqgrid/crud2\',$param);'."\n";
			$str .= $tab1.'}'."\n\n";


			$str .= $tab1.'//***************************'."\n";
			$str .= $tab1.'//Definicion del Grid y la Forma'."\n";
			$str .= $tab1.'//***************************'."\n";
			$str .= $tab1.'function defgrid( $deployed = false ){'."\n";
			$str .= $tab2.'$i      = 1;'."\n";
			$str .= $tab2.'$editar = "false";'."\n\n";
			$str .= $tab2.'$grid  = new $this->jqdatagrid;'."\n\n";
			$columna .= $str;
			$str = '';


			foreach ($query->result() as $row){
				if ( $row->Field == 'id') {
					$str   = $tab2.'$grid->addField(\'id\');'."\n";
					$str  .= $tab2.'$grid->label(\'Id\');'."\n";
					$str  .= $tab2.'$grid->params(array('."\n";
					$str  .= $tab3.'\'align\'         => "\'center\'",'."\n";
					$str  .= $tab3.'\'frozen\'        => \'true\','."\n";
					$str  .= $tab3.'\'width\'         => 40,'."\n";
					$str  .= $tab3.'\'editable\'      => \'false\','."\n";
					$str  .= $tab3.'\'search\'        => \'false\''."\n";
				} else {
					$str  = $tab2.'$grid->addField(\''.$row->Field.'\');'."\n";
					$str .= $tab2.'$grid->label(\''.ucfirst($row->Field).'\');'."\n";

					$str .= $tab2.'$grid->params(array('."\n";
					$str .= $tab3.'\'search\'        => \'true\','."\n";
					$str .= $tab3.'\'editable\'      => $editar,'."\n";

					if ( $row->Type == 'date' or $row->Type == 'timestamp' ) {
						$str .= $tab3.'\'width\'         => 80,'."\n";
						$str .= $tab3.'\'align\'         => "\'center\'",'."\n";
						$str .= $tab3.'\'edittype\'      => "\'text\'",'."\n";
						$str .= $tab3.'\'editrules\'     => \'{ required:true,date:true}\','."\n";
						$str .= $tab3.'\'formoptions\'   => \'{ label:"Fecha" }\''."\n";

					} elseif ( substr($row->Type,0,7) == 'decimal' or substr($row->Type,0,3) == 'int'  ) {
						$str .= $tab3.'\'align\'         => "\'right\'",'."\n";
						$str .= $tab3.'\'edittype\'      => "\'text\'",'."\n";
						$str .= $tab3.'\'width\'         => 100,'."\n";
						$str .= $tab3.'\'editrules\'     => \'{ required:true }\','."\n";
						$str .= $tab3.'\'editoptions\'   => \'{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }\','."\n";
						$str .= $tab3.'\'formatter\'     => "\'number\'",'."\n";
						if (substr($row->Type,0,3) == 'int'){
							$str .= $tab3.'\'formatoptions\' => \'{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 0 }\''."\n";
						} else {
							$str .= $tab3.'\'formatoptions\' => \'{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }\''."\n";
						}

					} elseif ( substr($row->Type,0,7) == 'varchar' or substr($row->Type,0,4) == 'char'  ) {
						$long = str_replace(array('varchar(','char(',')'),"", $row->Type)*10;
						$maxlong = $long/10;
						if ( $long > 200 ) $long = 200;
						if ( $long < 40 ) $long = 40;
						
						$str .= $tab3.'\'width\'         => '.$long.','."\n";
						$str .= $tab3.'\'edittype\'      => "\'text\'",'."\n";
						$str .= $tab3.'\'editrules\'     => \'{ required:true}\','."\n";
						$str .= $tab3.'\'editoptions\'   => \'{ size:30, maxlength: '.$maxlong.' }\','."\n";

					} elseif ( $row->Type == 'text' ) {
						$long = 250;
						$str .= $tab3.'\'width\'         => '.$long.','."\n";
						$str .= $tab3.'\'edittype\'      => "\'textarea\'",'."\n";
						$str .= $tab3.'\'editoptions\'   => "\'{rows:2, cols:60}\'",'."\n";

						//$str .= $tab3.'\'formoptions\'   => "\'{rows:"2", cols:"60"}\'",'."\n";
						

					} else {
						$str .= $tab3.'\'width\'         => 140,'."\n";
						$str .= $tab3.'\'edittype\'      => "\'text\'",'."\n";
					}
				}
				$str .= $tab2.'));'."\n\n";
				$columna .= $str."\n";
			}
			
			$str  = $tab2.'$grid->showpager(true);'."\n";
			$str .= $tab2.'$grid->setWidth(\'\');'."\n";
			$str .= $tab2.'$grid->setHeight(\'290\');'."\n";
			$str .= $tab2.'$grid->setTitle($this->titp);'."\n";
			$str .= $tab2.'$grid->setfilterToolbar(true);'."\n";
			$str .= $tab2.'$grid->setToolbar(\'false\', \'"top"\');'."\n\n";
		
			$str .= $tab2.'$grid->setFormOptionsE(\'closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} \');'."\n";
			$str .= $tab2.'$grid->setFormOptionsA(\'closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} \');'."\n";

			$str .= $tab2.'$grid->setAfterSubmit("$.prompt(\'Respuesta:\'+a.responseText); return [true, a ];");'."\n\n";

			$str .= $tab2.'#show/hide navigations buttons'."\n";
			$str .= $tab2.'$grid->setAdd(true);'."\n";
			$str .= $tab2.'$grid->setEdit(true);'."\n";
			$str .= $tab2.'$grid->setDelete(true);'."\n";
			$str .= $tab2.'$grid->setSearch(true);'."\n";
			$str .= $tab2.'$grid->setRowNum(30);'."\n";
            
			$str .= $tab2.'$grid->setShrinkToFit(\'false\');'."\n\n";

			$str .= $tab2.'#Set url'."\n";
			$str .= $tab2.'$grid->setUrlput(site_url($this->url.\'setdata/\'));'."\n\n";

			$str .= $tab2.'#GET url'."\n";
			$str .= $tab2.'$grid->setUrlget(site_url($this->url.\'getdata/\'));'."\n\n";

			$str .= $tab2.'if ($deployed) {'."\n";
			$str .= $tab2.'	return $grid->deploy();'."\n";
			$str .= $tab2.'} else {'."\n";
			$str .= $tab2.'	return $grid;'."\n";
			$str .= $tab2.'}'."\n";
			$str .= $tab1.'}'."\n\n";

			$str .= $tab1.'/**'."\n";
			$str .= $tab1.'* Busca la data en el Servidor por json'."\n";
			$str .= $tab1.'*/'."\n";
			$str .= $tab1.'function getdata()'."\n";
			$str .= $tab1.'{'."\n";

			//$str .= $tab2.'$filters = $this->input->get_post(\'filters\');'."\n";

			$str .= $tab2.'$grid       = $this->jqdatagrid;'."\n\n";

			$str .= $tab2.'// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO'."\n";
			$str .= $tab2.'$mWHERE = $grid->geneTopWhere(\''.$db.'\');'."\n\n";

			$str .= $tab2.'$response   = $grid->getData(\''.$db.'\', array(array()), array(), false, $mWHERE );'."\n";
			$str .= $tab2.'$rs = $grid->jsonresult( $response);'."\n";
			$str .= $tab2.'echo $rs;'."\n";

			$str .= $tab1.'}'."\n\n";

			$str .= $tab1.'/**'."\n";
			$str .= $tab1.'* Guarda la Informacion'."\n";
			$str .= $tab1.'*/'."\n";
			$str .= $tab1.'function setData()'."\n";
			$str .= $tab1.'{'."\n";
			$str .= $tab2.'$this->load->library(\'jqdatagrid\');'."\n";
			$str .= $tab2.'$oper   = $this->input->post(\'oper\');'."\n";
			$str .= $tab2.'$id     = $this->input->post(\'id\');'."\n";
			$str .= $tab2.'$data   = $_POST;'."\n";
			$str .= $tab2.'$mcodp  = "??????";'."\n";
			$str .= $tab2.'$check  = 0;'."\n\n";

			$str .= $tab2.'unset($data[\'oper\']);'."\n";
			$str .= $tab2.'unset($data[\'id\']);'."\n";

			$str .= $tab2.'if($oper == \'add\'){'."\n";
			$str .= $tab3.'if(false == empty($data)){'."\n";
			$str .= $tab4.'$check = $this->datasis->dameval("SELECT count(*) FROM '.$db.' WHERE $mcodp=".$this->db->escape($data[$mcodp]));'."\n";
			$str .= $tab4.'if ( $check == 0 ){'."\n";
			$str .= $tab5.'$this->db->insert(\''.$db.'\', $data);'."\n";
			$str .= $tab5.'echo "Registro Agregado";'."\n\n";
			$str .= $tab5.'logusu(\''.strtoupper($db).'\',"Registro ????? INCLUIDO");'."\n";
			$str .= $tab4.'} else'."\n";
			$str .= $tab5.'echo "Ya existe un registro con ese $mcodp";'."\n";

			$str .= $tab3.'} else'."\n";
			//$str .= $tab2.'echo \'\';'."\n";
			$str .= $tab4.'echo "Fallo Agregado!!!";'."\n\n";

			$str .= $tab2.'} elseif($oper == \'edit\') {'."\n";
			$str .= $tab3.'$nuevo  = $data[$mcodp];'."\n";
			$str .= $tab3.'$anterior = $this->datasis->dameval("SELECT $mcodp FROM '.$db.' WHERE id=$id");'."\n";
			$str .= $tab3.'if ( $nuevo <> $anterior ){'."\n";
			$str .= $tab4.'//si no son iguales borra el que existe y cambia'."\n";
			$str .= $tab4.'$this->db->query("DELETE FROM '.$db.' WHERE $mcodp=?", array($mcodp));'."\n";
			$str .= $tab4.'$this->db->query("UPDATE '.$db.' SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));'."\n";
			$str .= $tab4.'$this->db->where("id", $id);'."\n";
			$str .= $tab4.'$this->db->update("'.$db.'", $data);'."\n";	
			$str .= $tab4.'logusu(\''.strtoupper($db).'\',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");'."\n";
			$str .= $tab4.'echo "Grupo Cambiado/Fusionado en clientes";'."\n";

			$str .= $tab3.'} else {'."\n";
			$str .= $tab4.'unset($data[$mcodp]);'."\n";
			$str .= $tab4.'$this->db->where("id", $id);'."\n";
			$str .= $tab4.'$this->db->update(\''.$db.'\', $data);'."\n";
			$str .= $tab4.'logusu(\''.strtoupper($db).'\',"Grupo de Cliente  ".$nuevo." MODIFICADO");'."\n";
			$str .= $tab4.'echo "$mcodp Modificado";'."\n";
			$str .= $tab3.'}'."\n\n";
		
			$str .= $tab2.'} elseif($oper == \'del\') {'."\n";
			$str .= $tab2.'$meco = $this->datasis->dameval("SELECT $mcodp FROM '.$db.' WHERE id=$id");'."\n";

			$str .= $tab3.'//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM '.$db.' WHERE id=\'$id\' ");'."\n";
			$str .= $tab3.'if ($check > 0){'."\n";
			$str .= $tab4.'echo " El registro no puede ser eliminado; tiene movimiento ";'."\n";
			$str .= $tab3.'} else {'."\n";
			$str .= $tab4.'$this->db->simple_query("DELETE FROM '.$db.' WHERE id=$id ");'."\n";
			$str .= $tab4.'logusu(\''.strtoupper($db).'\',"Registro ????? ELIMINADO");'."\n";
			$str .= $tab4.'echo "Registro Eliminado";'."\n";
			$str .= $tab3.'}'."\n";
			$str .= $tab2.'};'."\n";
			$str .= $tab1.'}'."\n";
			$str .= '}'."\n";
			
			$columna .= $str."\n";
			
			//echo "<b>Campos de la Tabla:</b><br> $fields <br>";
			echo "$columna</pre>";
			//echo "<br>$campos";
		}
		
	}
	
	function mtab($n = 1){ return str_repeat("\t",$n); }
	
}
?>