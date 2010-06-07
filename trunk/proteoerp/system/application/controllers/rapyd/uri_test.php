<?php
require_once('basecontroller.php');

class Uri_test extends BaseController 
{


	function Uri_test()
	{
		parent::BaseController(); 

	}

  ##### datagrid #####
  function index()
  {
  	$traces = "<b><u>let's Follow the standard SEGMENTS VS the routed RSEGMENTS:</u></b><br /><br />";
  	$traces.= " segments => ". implode('/',$this->uri->segment_array())."<br />";
  	$traces.= " Rsegments => ". implode('/',$this->uri->rsegment_array())."<br />";
  	$traces.= "<br /><b><u>let's Follow the Rapyd URIarray:</u></b><br /><br />";
  	$traces.= "<pre>".print_r($this->rapyd->uri->uri_array,true)."</pre>";
  	$traces.= "<b><u>And the Rapyd implode_uri(URIarray):</u></b><br /><br />";
  	$traces.= $this->rapyd->uri->implode_uri()."<br />";
    //datagrid//
    $this->rapyd->load("datagrid");

    $link = site_url('rapyd/crudsamples/dataedit/show/<#article_id#>');

    $grid = new DataGrid("Article List", "articles");
    $grid->per_page = 5;
    $grid->use_function("substr","strtoupper");
    $grid->column_detail("ID","article_id", $link); 
    $grid->column("Title",'title', ' style="color:#ff0000" ');
    $grid->column("Body", "<substr><#body#>|0|7</substr>..");
    $grid->build();
    
    $data["grid"] = $grid->output;

    //enddatagrid//
    
    
    $content["content"] = $this->load->view('rapyd/datagrid', $data, true);
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = $traces;
    $this->load->view('rapyd/template', $content);
  }
}
?>