<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Qrcode Components
 *
 * @author   Andres Hocevar
 * @version  0.1
 * @filesource
**/

include('phpqrcode/qrlib.php');
class Qr {

	function Qr(){
		
	}

	function generar($data){
		QRcode::png($data, 'test.png', 'L', 4, 2);
	}

	function imgcode($data){
		QRcode::png($data,false,'L',5,2);
		
	}
}