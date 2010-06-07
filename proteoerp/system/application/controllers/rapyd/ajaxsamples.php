<?php
require_once('basecontroller.php');

class Ajaxsamples extends BaseController {


	function Ajaxsamples()
	{

		parent::BaseController(); 

	}


  ##### DataGrid #####
  function ajaxgrid()
  {
    //ajaxgrid//
    $this->rapyd->load("datagrid");
    
    $grid = new DataGrid("Results", "articles");
    
    if(($this->rapyd->session->get("title")) != ($this->input->post("title"))){
      $this->rapyd->uri->un_set("osp");
    }
    $this->rapyd->session->save("title", $this->input->post("title"));

    $grid->db->like("title", $this->rapyd->session->get("title"));
    $grid->per_page = 5;
    $grid->base_url = site_url("rapyd/ajaxsamples/ajaxsearch/osp");
    $grid->order_by("article_id","desc");
    $grid->use_function("substr","htmlspecialchars");
    $grid->column("title","title","width=200");
    $grid->column("body","<substr><htmlspecialchars><#body#></htmlspecialchars>|0|100</substr>");

    $grid->build();

    if (count($grid->data)<1) $grid->output = "no matchs";
   
    echo $grid->output;

    //endajaxgrid// 
  }


  
  ##### DataFilter + DataGrid #####
  function ajaxsearch()
  {
    //filteredgrid//
    $this->rapyd->load("datafilter");

    //filter
    $filter = new DataFilter("Article Search","articles");    
    $filter->title = new inputField("Title", "title");
    $filter->title->insertValue = $this->rapyd->session->get("title");

    $filter->build();

    //offset
    $osp  = "/osp";
    $osp .= ($this->rapyd->uri->is_set("osp")) ? "/".$this->rapyd->uri->get("osp",1) : "0";
        
    //prototype
    rapydlib("prototype");
    
    $this->rapyd->script[] = "
    Event.observe(window, 'load', init, false);

    function init(){
      Event.observe('title', 'keyup', do_search, false);
      do_search();
    }

    function do_search() 
    { 
      var url = '".site_url('rapyd/ajaxsamples/ajaxgrid'.$osp)."'; 
      var pars = 'title='+escape(\$F('title'));  
      var myUdater = new Ajax.Updater('search_results', url,{method:'post',parameters:pars});
    }
    ";
    
    $data["content"] = $filter->output.'<div id="search_results" style="height:200px"></div>';
    
    //endfilteredgrid//
    
    $this->_render("rapyd/ajaxsearch", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"filteredgrid"),
                      array("file"=>THISFILE, "id"=>"ajaxgrid", "title"=>"ajax callback"),
                    )
                  );
    
    
    
    $content["content"] = $this->load->view('rapyd/ajaxsearch', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = highlight_code_file(THISFILE, "//filteredgrid//", "//endfilteredgrid//");
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//ajax callback <br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//ajaxgrid//", "//endajaxgrid//");
    
    $this->load->view('rapyd/template', $content);
  }




}
?>