<?php
class formams extends Controller{

	function formams(){
		parent::Controller();
	}

	function index(){
		
	}

	function _msxml($fnombre,$data){
		$this->load->helper('download');
		$query = $this->db->query('SELECT word FROM formaesp WHERE nombre='.$this->db->escape($fnombre));
		if ($query->num_rows() > 0){
			$row = $query->row();
			$word=utf8_encode($row->word);
			preg_match_all('/\{(?<vars>[^\}]+)\}/', $word, $matches);
			$var=array();
			$tword=$word;
			if(count($matches['vars'])>0){
				foreach($matches['vars'] as $val){
					$pivot=preg_replace('/<[^>]*>/', '', $val);
					$var[$pivot]=$val;
					if(isset($data[$pivot])){
						$tword=str_replace('{'.$val.'}',$data[$pivot], $tword);
					}
				}
				$nom = 'contrato.xml';
				force_download($nom, $tword);
			}
		}else{
			echo 'Formato no existe';
		}
	}

	function instalar(){
		$mSQL="";
		$this->db->simple_query($mSQL);
	}
}