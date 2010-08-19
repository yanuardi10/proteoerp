<?php
class Ftpsincro{

	var $host;        // FTP HOST
	var $user;        // FTP USER
	var $password;    // FTP PASSWORD
	var $source;      // FILE SOURCE
	var $destination; // FILE DESTINATION
	var $mode;        // FTP MODE
	var $ftpdir;
	var $str;
	var $write;
	var $dirlocal;
	var $actualizado=array();
	var $error;

	//Ftpsincro($usuario,$clave,$host,$ftpdir)
	function Ftpsincro(){
		// $match[0] = ftp://username:password@sld.domain.tld/path1/path2/
		// $match[1] = username
		// $match[2] = password
		// $match[3] = sld.domain.tld
		// $match[4] = /path1/path2/
		$this->error=0;
		$this->dirlocal='./sincro';
		$data= func_get_args();
		$this->connection=0;
		$this->mascara='*';

		if(count($data)==1){
			preg_match("/ftp:\/\/(.*?):(.*?)@(.*?)(\/.*)/i", $data[0], $match);
			array_shift($match);
			if(count($match)!=4){
				$this->error("ERROR LA ENTRADA DEBE SER ftp://usuario:clave@www.dominio.com/path1/path2/");
			}
			$this->user     = (empty($match[0])) ? "anonymous"         : $match[0];
			$this->password = (empty($match[1])) ? "nobody@nobody.com" : $match[1];
			$this->host     = $match[2];
			$this->ftpdir   = (empty($match[3])) ? './'                : reduce_double_slashes('/'.$match[3].'/');

		}elseif(count($data)==4){
			$match=$data;
			$this->user     = (empty($match[0])) ? "anonymous"         : $match[0];
			$this->password = (empty($match[1])) ? "nobody@nobody.com" : $match[1];
			$this->host     = $match[2];
			$this->ftpdir   = (empty($match[3])) ? './'                : reduce_double_slashes('/'.$match[3].'/');

		}else{
			//$this->error("ERROR FALTAN PARAMETROS usuario,clave,dominio,ftpdir");
		}
	}

	function connect(){
		$this->connection = ftp_connect($this->host);
		if (!$this->connection){
			$this->error("ERROR FTP->CONNECT [$this->connection:$this->host]");
		}
	}

	function login(){
		if($this->error) return false;
		$this->logged = @ftp_login($this->connection, $this->user, $this->password);

		if (!$this->logged){
			$this->error("ERROR FTP->LOGIN [$this->connection:$this->user:$this->password]");
		}else{
			if(isset($this->ftpdir)){
				if($this->ftpdir[0]!='/') $this->ftpdir='/'.$this->ftpdir;
				if(!preg_match('/^.*\/$/',$this->ftpdir)) $this->ftpdir=$this->ftpdir.'/';
				if(!@ftp_chdir($this->connection, '.'.$this->ftpdir)){
					$this->error("ERROR FTP->CHDIR [$this->ftpdir]");
				}
			}
			
			$this->pasv = ftp_pasv($this->connection, true);
			if (!$this->pasv){
				$this->error("ERROR FTP->PASV [$this->connection]");
			}
		}

	}

	function mascara($masq){
		//if(ereg('[A-Z0-9a-z,\-\*]',$masq))
		$this->mascara=$masq;
		
	}

	function upload($source_file,$destination_file,$type=""){
		if($this->error) return false;
		$this->source = $source_file;
		$this->destination = $destination_file;
		$this->type = $type;
		
		switch($this->type) {// MODE = 'FTP_ASCII' or 'FTP_BINARY'{
			case"image/gif":
			case"image/png":
			case"image/jpeg":
				$this->mode = FTP_BINARY;
			break;
			default:
				$this->mode = FTP_ASCII;
		}
    
		$this->put = ftp_put($this->connection, $this->destination, $this->source, $this->mode);
		
		if (!$this->put){ 
			$this->error("ERROR FTP->PUT [$this->connection:$this->source:$this->mode]");
		}
	}

	function downloads(){
		if($this->error) return false;
		$arch   =$this->filelist();
		if(count($arch)>0){
			$destino=$this->dirlocal.'/'.$this->host.$this->ftpdir;
			$escri= @file_get_contents($destino.'_sincro.dat');
			$estan= ($escri) ? unserialize($escri):array();
			
			$ddest=explode('/',$destino);
			$buff=array_shift($ddest);
			foreach($ddest AS $dir){
				if(!empty($dir)){
					$buff.='/'.$dir;
					$flag=true;
					if(!file_exists($buff)){
						if(!mkdir($buff,0777)){
							$flag=false;
							break;
						}
					}
				}
			}
			if($flag){ 
				foreach($arch AS $nombre=>$attr){ 
					$dnombre=$this->clean_filename($nombre);
					if(isset($estan[$nombre])){
						if($attr['md5']!=$estan[$nombre]['md5'] or (!file_exists($destino.$dnombre))){
							if (!ftp_get($this->connection, $destino.$dnombre, $nombre, FTP_BINARY))
								$this->error("ERROR FTP->DOWLOADS [$nombre:$destino.$nombre]");
							//echo "actualizado $nombre\n";
							$this->actualizado[]=str_replace('/./','/',$destino.$dnombre);
						}
					}else{
						if (!ftp_get($this->connection, $destino.$dnombre, $nombre, FTP_BINARY))
							$this->error("ERROR FTP->DOWLOADS [$nombre:$destino.$nombre]");
						//echo "creado $nombre\n";
						//$search=array('/./',' '); $remp=array('/','_');
						$this->actualizado[]=str_replace('/./','/',$destino.$dnombre);
					}
				}
			}
			$escri=serialize($arch);
			file_put_contents  ($destino.'_sincro.dat',$escri); 
		}
	}

	function delete($source_file){
		if($this->error) return '';
		$this->source = $source_file;
		$this->deleted = ftp_delete($this->connection, $this->source);
		
		if (!$this->deleted){
			$this->error("ERROR FTP->DELETE [$this->connection:$this->source]");
		}
	}

	function clean_filename($source_file){
		$search[] = " ";
		$search[] = "&";
		$search[] = "$";
		$search[] = ",";
		$search[] = "!";
		$search[] = "@";
		$search[] = "#";
		$search[] = "^";
		$search[] = "(";
		$search[] = ")";
		$search[] = "+";
		$search[] = "=";
		$search[] = "[";
		$search[] = "]";

		$replace[] = "_";
		$replace[] = "and";
		$replace[] = "S";
		$replace[] = "_";
		$replace[] = "";
		$replace[] = "";
		$replace[] = "";
		$replace[] = "";
		$replace[] = "";
		$replace[] = "";
		$replace[] = "";
		$replace[] = "";
		$replace[] = "";
		$replace[] = "";

		return str_replace($search,$replace,$source_file);
	}

	function filelist(){
		if($this->error) return array();
		$ftp_rawlist = ftp_rawlist($this->connection, '.');
		$ftp_nlist   = ftp_nlist($this->connection, '.');
		//var_dump($ftp_rawlist);
		if($ftp_rawlist===FALSE ) { $this->error('Error en la opteniendo contenidos'); return array();}
		//print_r($ftp_rawlist);
		
		/*$search[]  = ',';$replace[] = '$|^';
		$search[]  = '.';$replace[] = '\\.';
		$search[]  = '*';$replace[] = '.*';
		
		$reg=str_replace($search,$replace,$this->mascara);
		$reg='/^'.$reg.'$/';
		*/
		$this->mascara=(empty($this->mascara)) ? '.*' : $this->mascara ;
		$reg='/'.$this->mascara.'/';
		
		$rawlist=array(); $i=0;
 		foreach ($ftp_rawlist as $v) {
 			if(!preg_match('/<DIR>/',$v)){
				$info   = array();
				$vinfo  = preg_split("/[\s]+/", $v, 9);
				//$nombre = chop(array_pop($vinfo));
				$nombre=$ftp_nlist[$i];
				if(preg_match($reg,$nombre)){
					$info['md5']      = md5($v); 
					$rawlist[$ftp_nlist[$i]] = $info;
				}
			}$i++;
		}
		//print_r($rawlist);
		return $rawlist;
	}


	function check_file($source_file){
		$this->source = $source_file;  
		return ftp_size($this->connection, $this->source);
	}

	function error($str,$write=0){
		//error_log($str, $write);
		$this->error=TRUE;
		$this->erazon=$str;
		if($this->connection) $this->close();
		//die($str);
	}

	function warning($str){
		$this->warning="ADVERTENCIA: $str";
	}

	function close(){
		if (!$this->connection)
			ftp_close($this->connection);
	}

	function cd($directorio){
		if(!ftp_chdir ($this->connection,$directorio)){
			$this->error("ERROR FTP->cd $directorio");
		}
	}

	function start(){
		$this->connect();
		$this->login();
	}

	function end(){
		$this->close();
	}
}
?>
