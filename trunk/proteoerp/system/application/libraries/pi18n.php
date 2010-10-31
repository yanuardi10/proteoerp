<?php
class pi18n{

	function pi18n(){
		$this->ci  =& get_instance();
		$this->pais=$this->ci->datasis->traevalor('pais');
		$this->i18n=$this->ci->datasis->traevalor('i18n');
		$this->fallas=array();
		//$this->msjs=array();

		if(!$this->ci->db->table_exists('i18n')){
			$mSQL="CREATE TABLE `i18n` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `modulo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `metodo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `pais` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `campo` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `mensaje` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `Index 2` (`modulo`,`metodo`,`pais`,`campo`)
			) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
			$this->ci->db->simple_query($mSQL);
		}
	}

	function cargar($modulo,$metodo){
		$this->modulo=$modulo;
		$this->metodo=$metodo;
		if(empty($this->pais)) return;
		$mSQL='SELECT campo, mensaje FROM i18n WHERE modulo='.$this->ci->db->escape($modulo).' AND metodo='.$this->ci->db->escape($metodo).' AND pais='.$this->ci->db->escape($this->pais);
		$query=$this->ci->db->query($mSQL);
		if($query->num_rows()>0){
			foreach($query->result() AS $row){
				$constante='MSG_'.$row->campo;
				define($constante,$row->mensaje);
			}
		}
	}

	function msj($campo,$msj=null){
		if($this->i18n!='S'){
			if(!empty($this->pais) AND (!empty($msj))){
				$this->_guardar($campo,$msj);
			}
			return $msj;
			
		}
		$constante='MSG_'.$campo;
		if(defined($constante)){
			return constant($constante);
		}else{
			$nlink=(empty($msj)) ? 'Falta definir': $msj;
			$this->fallas[]=anchor('supervisor/i18n/arapido/'.$this->modulo.'/'.$this->metodo.'/'.$campo.'/create',$nlink);
			return $nlink;
		}
	}

	function arr_msj($campo,$msj=null){
		$arr=$this->msj($campo,$msj);
		$pivot=explode(',',$arr);
		$rt=array();
		foreach($pivot AS $val){
			$a=explode('=',$val);
			$ind=$a[0];
			$rt[$ind]=$a[1];
		}
		return $rt;
	}

	function db2arr($par){
		$pivot=explode(';',$arr);
		$rt=array();

	}

	function arr2db($par){
		
	}

	function fallas(){
		
		if(count($this->fallas)>0){
			$rt ='<h3>Faltas en los mensajes de la iterface</h3>';
			$rt.='Es posible que algunos de los mensajes no este ajustados su pa&iacute;s, puede cambiar esto haciendo click en los enlaces siguientes'.br();
			$rt.=implode(br(),$this->fallas).br();
			return $rt;
		}else{
			return '';
		}
	
	}

	function _guardar($campo,$msj){
		$data = array('metodo' => $this->metodo, 'modulo' => $this->modulo, 'pais' =>$this->pais, 'campo'=>$campo,'mensaje'=>$msj);
		$mSQL = $this->ci->db->insert_string('i18n', $data);
		$this->ci->db->simple_query($mSQL);
	}

}

