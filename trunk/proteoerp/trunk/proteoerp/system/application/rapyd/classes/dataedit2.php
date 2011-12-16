<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("dataedit.php");

/**
 * DataEdit base class.
 *
 * @package    rapyd.components
 * @author     Andres Hocevar
 * @access     public
 */
class DataEdit2 extends DataEdit{

	function DataEdit2($title, $table){
		parent::DataEdit($title, $table);
	}

}
?>