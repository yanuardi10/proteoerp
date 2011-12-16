<?php


class Tests extends Controller {

  function Tests()
  {
    parent::Controller(); 

    //required helpers for samples
    $this->load->helper('url');
    $this->load->helper('text');

    //rapyd library
    $this->load->library("rapyd");
    
    //I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
    define ("THISFILE",   APPPATH."controllers/rapyd/". $this->uri->segment(2).EXT);
  }


  ##### index #####
  function index()
  {
    redirect("rapyd/tests/multipk");
  }


  ##### dataedit #####
  function multipk()
  {  
  
    //dataedit//
    $this->rapyd->load("dataedit");

echo RAPYD_URI_SEARCH;

    $edit = new DataEdit("Relation Detail", "articles_related");

    $edit->article = new dropdownField("Article", "art_id");
    $edit->article->option("","");
    $edit->article->options("SELECT article_id, CONCAT('(',article_id,')') as article FROM articles");

    $edit->relation = new dropdownField("Rel Article", "rel_id");
    $edit->relation->option("","");
    $edit->relation->options("SELECT article_id, CONCAT('(',article_id,')') as article FROM articles");

    $edit->build();
    
    $data["content"] = $edit->output;
     
    //enddataedit//

    $content["content"] = $this->load->view('rapyd/tests', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"]  = highlight_code_file(THISFILE, "//dataedit//", "//enddataedit//");
    $this->load->view('rapyd/template', $content);
  }
  

}
?>