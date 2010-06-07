<?php
require_once('basecontroller.php');

class Lang extends BaseController 
{


	function Lang()
	{

		parent::BaseController(); 

	}



  ##### index #####
  function index()
  {
    //language//
  
    #### the controller 
    
    // in your /config/rapyd.php
    // enable rapyd language:
    // $rpd['rapyd_lang_ON']= True;
    // then default language is auto-detected by uri (and/or by a session var) by rapyd
    
    $this->lang->load('date');
    
    //endlanguage//
    
    $this->_render("rapyd/lang", null, 
                    array(
                      array("file"=>THISFILE, "id"=>"language"),
                      array("file"=>VIEWPATH."lang.php", "title"=>"the view"),
                    )
                  );
    
  }










}
?>