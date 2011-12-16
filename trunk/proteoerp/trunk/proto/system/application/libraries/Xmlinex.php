<?php
class Xmlinex{

	var $ci;
	var $dir;

	function Xmlinex(){
		$this->ci =& get_instance();
		$this->ci->load->helper('xml');
		$this->ci->load->helper('string');
		//$this->dir='./'.$this->ci->config->item('uploads_dir').'/traspasos';
		$this->dir='./uploads/traspasos';
		$this->dir=reduce_double_slashes($this->dir);
	}

	function import($arch){
		$dir=$this->dir.'/'.$arch;
		if(file_exists($dir)){
			$objDOM = new DOMDocument();
			$objDOM->load($dir);
			$traspaso = $objDOM->getElementsByTagName('traspaso');
			$esquemas = $traspaso->item(0)->getElementsByTagName('esquema');

			foreach($esquemas as $esquema){
				$tabla   = $esquema->getElementsByTagName("tabla")->item(0)->nodeValue;
				$data    = $esquema->getElementsByTagName("data");
				$filas   = $data->item(0)->getElementsByTagName("fila");
				$where   = $esquema->getElementsByTagName("where")->item(0)->nodeValue;

				$this->ci->db->where($where,NULL,FALSE);
				$this->ci->db->delete($tabla);
				foreach($filas AS $fila){
					$campos=$fila->getElementsByTagName("campo");
					$idata=array();
					foreach($campos AS $campo){
						$ind  = $campo->getAttribute('nombre');
						$valor= $campo->nodeValue;
						$idata[$ind]=$valor;
						$this->ci->db->set($ind,$valor);
					}
					$this->ci->db->insert($tabla);
				}
			}
		}else{
			show_error('Archivo no existe '.$dir);
		}
	}

/*$data[]=array('select'=>array('articulo','nombre','precio'),
									'distinc'=>false,
									'table'  =>'tabla1',
									'where'  =>'precio = 100',*/

	function export($datas){
		//tempname('FCPATH')
		$out="<traspaso>\n";
		foreach($datas AS $data){
			if(isset($data['select']) AND count($data['select'])>0 )$this->ci->db->select($data['select']);
			if(isset($data['where']))  $this->ci->db->where($data['where'],NULL,FALSE); else $data['where']='';
			if(isset($data['distinc']) AND $data['distinc']) $this->db->distinct();
			
			$this->ci->db->from($data['table']);
			
			$query = $this->ci->db->get();
			
			if ($query->num_rows() > 0){
				$out.= "<esquema>";
				$out.= "<tabla>$data[table]</tabla>";
				$out.= "<where>$data[where]</where>";
				$out.= "<data>";
				foreach ($query->result_array() as $row){
					$out.= "    <fila>\n";
					foreach($row AS $campo=>$valor){
						$out.= "<campo nombre='$campo'>".xml_convert($valor)."</campo>";
					}
					$out.= "</fila>";
				}
				$out.= "</data>";
				$out.= "</esquema>\n";
			}
		}
		$out.="</traspaso>";
		return $out;
	}
}
?>