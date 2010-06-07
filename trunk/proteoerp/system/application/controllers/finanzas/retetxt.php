<?php
class retetxt extends Controller {
	
	
	function retetxt(){
		parent::Controller();
		$this->load->library("rapyd");
		$this->load->helper('download');
		$this->load->helper('file');
		
	}
	function index(){
		redirect ('reportes/ver/RETETXT');	
	}
	function crear($fechad='',$fechah=''){
	
		$mSQL="SELECT REPLACE(periodo,'-','') as periodo, nrocomp, emision, 'C' as ticom, if(tipo_doc='FC','01',IF(tipo_doc='ND','02',IF(tipo_doc='NC','03',0))) as tipo, nombre, rif, numero, IF(nfiscal<>'',nfiscal,'0')AS nfiscal, general+geneimpu+exento as total, general as base, (reiva/impuesto)*geneimpu as rete, if(tipo_doc='FC','0',afecta) as afecta  , CONCAT(REPLACE(periodo,'-',''), nrocomp) as comprobante, exento, ROUND(((reiva/impuesto)*geneimpu)*100/(general+geneimpu+exento)) as tasa, '0' as impor, fecha
		FROM riva 
		WHERE 
		emision>= '$fechad' AND emision<= '$fechah'  AND MID(transac,1,1)<>'_'  AND tipo_doc<>'AN'
		and general <> 0 
		UNION
		SELECT REPLACE(periodo,'-','') as periodo,nrocomp,emision,'C' as ticom, if(tipo_doc='FC','01',IF(tipo_doc='ND','02',IF(tipo_doc='NC','03',0))) as tipo, nombre,rif, numero, IF(nfiscal<>'',nfiscal,'0')AS nfiscal, reducida+reduimpu as total, reducida as base, (reiva/impuesto)*reduimpu as rete, if(tipo_doc='FC','0',afecta) as afecta , CONCAT(REPLACE(periodo,'-',''), nrocomp) as comprobante, exento,ROUND(((reiva/impuesto)*geneimpu)*100/(general+geneimpu+exento)) as tasa, '0' as impor, fecha
		FROM riva
		WHERE 
		emision>= '$fechad' AND emision<= '$fechah' AND MID(transac,1,1)<>'_'  AND tipo_doc<>'AN'
		AND reducida <>0   ORDER BY nrocomp";

		$query=$this->db->query($mSQL);
		$line='';
		//$line="\r\n";
		foreach($query->result_array() as $row){
				
			$line.=($this->datasis->dameval("SELECT REPLACE(valor,'-','') FROM valores WHERE nombre='RIF'"))."\t"; 		
			$line.=$row["periodo"]."\t";
			$line.=$row["fecha"]."\t";
			$line.='C'."\t";
			//$line.='01'."\t";
			$line.=$row["tipo"]."\t";
			$line.=$row["rif"]."\t";
			$line.=$row["numero"]."\t";
			$line.=$row["nfiscal"]."\t";
			$line.=number_format($row["total"],2,'.','')."\t";
			$line.=number_format($row["base"],2,'.','')."\t";
      $line.=number_format($row["rete"],2,'.','')."\t";
      $line.=number_format($row["afecta"],2,'.','')."\t";
      $line.=$row["comprobante"]."\t";
      $line.=number_format($row["exento"],2,'.','')."\t";
      $line.=number_format($row["tasa"],2,'.','')."\t";
      $line.=number_format($row["impor"],0,'','');                 
			$line.="\r\n";

		}	
		//echo $line;
		$name = 'Retentxt.txt';		
		force_download($name,$line);
		}
}
?>