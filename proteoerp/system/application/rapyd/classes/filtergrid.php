<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Rapyd Components
 *
 * An open source library for CodeIgniter application development framework for PHP 4.3.2 or newer
 *
 * @package		rapyd.components
 * @author		Andres Hocevar
 * @license		http://www.fsf.org/licensing/licenses/lgpl.txt LGPL
 * @version		0.9.6
 * @filesource
 */

/**
 * GridEdit - 
 * 
 *
 * @package    rapyd.components
 * @author     Andres Hocevar
 * @access     public
 */
class filterGrid {

	var $output='';
 /**
  * PHP4 constructor.
  *
  * @access   public
  * @param    array   $data   a multidimensional associative array of data
  * @return   void
  */
	function filterGrid($titulo,$data){

		$this->ci    =& get_instance();
		$this->rapyd =& $this->ci->rapyd;
		$this->uri   =& $this->ci->uri;
		$this->ci->load->helper('url');
		$this->titulo=$titulo;

		//AR preset or SQL query passed, so database lib needed
		if (!isset($data) || is_string($data)){
		//rapyd->db (active record??) If we use DS with DF we look if DF is loaded first and then have instatiate the Rapyd shared database AR
			if(isset($this->rapyd->db)){
				$this->db =& $this->rapyd->db;
			}else{
				//If dataset is instantiate first we set the rapyd shared	database AR	in case that DF will be loaded in second....
				$data_conn =(isset($this->rapyd->data_conn))?$this->rapyd->data_conn:'';
				$this->db = $this->ci->load->database($data_conn,TRUE);
				$this->rapyd->db =& $this->db;
			}
		}
		$this->type = 'query';

		//tablename
		if (is_string($data)){
			$this->db->select('*');
			$this->db->from($data);
			$this->table=$data;
		} elseif (is_array($data)){
			//array
			$this->type     = 'array';
			$this->arraySet = $data;
		}
	}


	function build(){
		$this->db->limit(1);
		$query=$this->db->get();
		$fields = $query->field_data();
		
		$colNames=array();
		foreach($fields AS $ind=>$field){
			$colNames[]=ucfirst($field->name);

			$colModel[$ind]['name'] =$field->name;
			$colModel[$ind]['index']=$field->name;
			$colModel[$ind]['width']=$field->max_length*15;
			$colModel[$ind]['align']='left';
		}

		$url=current_url();
		$script = "$(\"#list\").jqGrid({
			url:'$url',
			datatype: 'xml',
			height: 255,
			mtype: 'POST',
			colNames :".json_encode($colNames).",
			colModel :".json_encode($colModel).",
			pager: '#pager',
			rowNum:40,
			rowList:[40,80,120],
			sortname: 'barras',
			setGridWidth: 600,
			sortorder: 'asc',
			viewrecords: true,
			caption: '$this->titulo'
		});
		jQuery(\"#list\").jqGrid('navGrid','#pager',{del:false,add:false,edit:false},{},{},{},{multipleSearch:true});";

		$this->rapyd->jquery[]=$script;
		$this->output  = '<table id="list"></table>';
		$this->output .= '<div id="pager"></div>';
	}

	function request($tabla=''){
		$page  = $this->input->post('page');
		$limit = $this->input->post('rows'); // get how many rows we want to have into the grid - rowNum parameter in the grid 
		$sidx  = $this->input->post('sidx'); // get index row - i.e. user click to sort. At first time sortname parameter -after that the index from colModel 
		$sord  = $this->input->post('sord');

		$filtro=$this->input->post('filters');
		if($filtro !== FALSE){
			$filtro=json_decode($filtro);
			if ($filtro->groupOp=='AND') $glu=''; else $glu='or_';
			foreach($filtro->rules AS $rule){
				switch ($rule->op) {
					case "eq": //Igual a
						$metodo=$glu.'like';
						$this->db->$metodo($rule->field,$rule->data);
						break;
					case "ne": //No igual a
						$metodo=$glu.'where';
						$this->db->$metodo($rule->field.' <>',$rule->data);
						break;
					case "lt": //Menor que
						$metodo=$glu.'where';
						$this->db->$metodo($rule->field.' <',$rule->data);
						break;
					case "le": //Menor o igual
						$metodo=$glu.'where';
						$this->db->$metodo($rule->field.' <=',$rule->data);
						break; 
					case "gt": //Mayor que
						$metodo=$glu.'where';
						$this->db->$metodo($rule->field.' >',$rule->data);
						break;
					case "ge": //Mayor o igual que
						$metodo=$glu.'like';
						$this->db->$metodo($rule->field.' >=',$rule->data);
						break;
					case "bw": //Empieza por 
						$metodo=$glu.'like';
						$this->db->$metodo($rule->field,$rule->data,'after');
						break;
					case "bn": //No empieza por
						$metodo=$glu.'not_like';
						$this->db->$metodo($rule->field,$rule->data,'after');
						break;
					case "in": //Esta en
						$metodo=$glu.'where_in';
						$this->db->$metodo($rule->field,explode(',',$rule->data));
						break;
					case "ni": //No esta en
						$metodo=$glu.'where_not_in';
						$this->db->$metodo($rule->field,explode(',',$rule->data));
						break;
					case "ew": //Termina por
						$metodo=$glu.'like';
						$this->db->$metodo($rule->field,$rule->data,'before');
						break;
					case "en": //No termina por
						$metodo=$glu.'not_like';
						$this->db->$metodo($rule->field,$rule->data);
						break;
					case "cn"://Contiene
						$metodo=$glu.'like';
						$this->db->$metodo($rule->field,$rule->data);
						break;
					case "nc": //No contiene
						$metodo=$glu.'not_like';
						$this->db->$metodo($rule->field,$rule->data);
						break;
					default :
						$metodo=$glu.'where';
						$this->db->$metodo($rule->field.' <=',$rule->data);
				}
			}
		}

		/*ob_start();
		print_r($_REQUEST);
		$ddata=ob_get_contents();
		ob_end_clean();
		memowrite($ddata);*/
		memowrite(http_build_query($_REQUEST));

		$this->db->from($tabla);

		if(!$sidx) $sidx =1;// if we not pass at first time index use the first column for the index or what you want
		$mSQL=$this->db->_compile_select($this->db->_count_string . $this->db->_protect_identifiers('numrows'));
		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$row = $query->row();
			$count=$row->numrows;
		}else{
			$count=0;
		}
 
		if( $count > 0 && $limit > 0) { 
			$total_pages = ceil($count/$limit); 
		} else {
			$total_pages = 0; 
		}

		if ($page > $total_pages) $page=$total_pages;
		$start = $limit*$page - $limit;
		if($start <0) $start = 0;

		$this->load->helper('xml');
		header("Content-type: text/xml;charset=".$this->config->item('charset'));
		$s = "<?xml version='1.0' encoding='".$this->config->item('charset')."'?>";
		$s .=  "<rows>";
		$s .= "<page>".$page."</page>";
		$s .= "<total>".$total_pages."</total>";
		$s .= "<records>".$count."</records>";

		$this->db->orderby($sidx,$sord);
		$this->db->limit($limit,$start);
		$query = $this->db->get();

		$campos = $this->db->field_data();
		foreach ($query->result() as $row){
			$s .= "<row id='". $row->$id."'>";
			foreach($campos AS $campo){
				$s .= "<cell>".xml_convert($row->$campo->name)."</cell>";
			}
			$s .= "</row>";
		}
		$s .= "</rows>"; 
		echo $s;
	}
}
