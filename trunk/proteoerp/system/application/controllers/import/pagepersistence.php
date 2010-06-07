<?php


class Pagepersistence extends Controller {

  var $data_type = null;   
  var $data = null;

	function Pagepersistence()
	{
		parent::Controller(); 

    //required helpers for samples
    $this->load->helper('url');
    $this->load->helper('text');

		//rapyd library
		$this->load->library("rapyd");

		//language setting
		$this->curr_language =	$this->rapyd->language->language;
    $this->short_lang = ($this->curr_language=="italian")?"it":"en";
    
    //save language in session
    $this->rapyd->language->save_language();
    
  }



  function listing()
  {
    //listing//
    
    $back_from = array_search("back_from",$this->uri->segment_array());
    if ($back_from!==false){
      $key =	$this->uri->segment($back_from+1);
    } else {
      $key =  $this->rapyd->session->save_persistence("rapyd/pagepersistence");
    }
    $data["link"] = anchor("controller/detail/forward_from/$key", "go to next page &gt;&gt;");
 
    //endlisting//
    
    $content["content"] = $this->load->view('rapyd/persistence', $data, true);
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = highlight_code_file(THISFILE, "//listing//", "//endlisting//");
    $this->load->view('rapyd/template', $content);
  }


  function detail()
  {
    //detail//
    
    $forward_from = array_search("forward_from",$this->uri->segment_array());
    if ($forward_from!==false){
      $key =	$this->uri->segment($forward_from+1);
      $data["link"] = anchor("controller/listing/back_to/$key", "&gt;&gt; go to prev page");
    } else {
      $data["link"] = anchor("controller/listing", "go to prev page");
    }
 
    //enddetail//
    
    $content["content"] = $this->load->view('rapyd/persistence', $data, true);
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = highlight_code_file(THISFILE, "//detail//", "//enddetail//");
    $this->load->view('rapyd/template', $content);
  }

  
}
?>