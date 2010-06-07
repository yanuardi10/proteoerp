<?php
require_once('basecontroller.php');

class Utils extends BaseController {

  var $get_theme = false;

	function Utils()
	{
		parent::BaseController(); 

	}



  function theme()
  {
    //themeswitch//

    $theme = $this->uri->segment(4);
    
    $this->rapyd->session->save("current_theme", $theme);
    $this->rapyd->config->set_item("theme",$theme);

    $data["message"] = "New theme is $theme!";

    //endthemeswitch//
    
    
    $this->_render("rapyd/utils", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"themeswitch"),
                    )
                  );

  }



}
?>