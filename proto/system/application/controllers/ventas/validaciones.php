<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
//*************************************
// Definiciones de reglas para validar
//*************************************


class validaciones extends Controller {
	
	//Validar la cedula
	function chci($rifci){
		$l=strlen($rifci)-1;
		$re="(^[VEJG][0-9]{".$l."})|(^[P][A-Z0-9]{".$l."})";
		if (preg_match($re, $rifci)>0){
			return TRUE;
		}else {
			$this->validation->set_message('chci', "Debe introducir V=Venezolano(a), E=Extranjero(a), G=Gobierno, P=Pasaporte o J=Juridico Como primer cararter seguido del numero de documento. Ej: V123456, J5555555, P56H454");
			return FALSE;
		}
	}
}
?>