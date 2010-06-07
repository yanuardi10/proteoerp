<?php


class Samples extends Controller {

  var $data_type = null;   
  var $data = null;

	function Samples()
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
    
    //language file inclusion
    //$this->lang->load('application', $this->curr_language);
    
    //required data for (some) samples
    $this->data = array(
      array('article_id' => '1', 'title' => 'Title 1', 'body' => 'Body 1'),
      array('article_id' => '2', 'title' => 'Title 2', 'body' => 'Body 2'),
      array('article_id' => '3', 'title' => 'Title 3', 'body' => 'Body 3'),
      array('article_id' => '4', 'title' => 'Title 4', 'body' => 'Body 4'),
      array('article_id' => '5', 'title' => 'Title 5', 'body' => 'Body 5'),
      array('article_id' => '6', 'title' => 'Title 6', 'body' => 'Body 6'),
      array('article_id' => '7', 'title' => 'Title 7', 'body' => 'Body 7'),
      array('article_id' => '8', 'title' => 'Title 8', 'body' => 'Body 8'),
      array('article_id' => '9', 'title' => 'Title 9', 'body' => 'Body 9'),
      array('article_id' => '10', 'title' => 'Title 10', 'body' => 'Body 10')
    );
    
    //I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
    define ("THISFILE",   APPPATH."controllers/rapyd/". $this->uri->segment(2).EXT);
	}



  ##### index #####
  function index()
  {
    $content["content"] = $this->load->view('rapyd/home', null, true);
    $content["code"] = "";
    $content["rapyd_head"] = "";
    $this->load->view('rapyd/template', $content);
  }



  ##### dataset #####
  function dataset()
  {
    
    //dataset//
    $this->rapyd->load("dataset");
    
    $dataset = new DataSet($this->data);
    $dataset->per_page = 5;
    $dataset->build();
  
    $data["items"] = $dataset->data;
    $data["navigator"] = $dataset->navigator;    
    
    //enddataset//
    
    $content["content"] = $this->load->view('rapyd/dataset', $data, true);
    $content["rapyd_head"] = "";
    $content["code"] = highlight_code_file(THISFILE, "//dataset//", "//enddataset//");
    $this->load->view('rapyd/template', $content);
    

  }


  ##### datatable #####
  function datatable()
  {
    //datatable//
    
    $this->rapyd->load("datatable");
    
    $table = new DataTable(null, $this->data);
    $table->per_row = 3; 
    $table->per_page = 6;
    $table->use_function("substr","strtoupper");
    
    $table->cell_template = '
    <div style="padding:4px">
      <div style="color:#119911; font-weight:bold"><#title#></div>
      This is the body number <substr><#body#>|5|100</substr>
    </div>'; 
    $table->build();
    
    $data["table"] = $table->output;
    
    //enddatatable//
    
    $content["content"] = $this->load->view('rapyd/datatable', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = highlight_code_file(THISFILE, "//datatable//", "//enddatatable//");
    $this->load->view('rapyd/template', $content);
  }


  ##### datagrid #####
  function datagrid()
  {
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
    $content["code"] = highlight_code_file(THISFILE, "//datagrid//", "//enddatagrid//");
    $this->load->view('rapyd/template', $content);
  }


  ##### datagrid #####
  function datagrid2()
  {
    //datagrid2//
    $this->rapyd->load("datagrid");
    $this->load->helpers("file");
    $file_names = get_filenames("uploads/banners/");
    
    foreach($file_names as $name)
    {
      $file = array();
      $file["name"] = $name;
      
      //you can build your filesize function.. 
      //preview function(if they are images)
      $file["details"] = "etc.."; 
      
      $files[] = $file;
    }

    $grid = new DataGrid("Fiel List", $files);
    $grid->per_page = 5;
    $grid->column("Name","name");
    $grid->column("details", "details");
    $grid->column("delete", anchor("rapyd/samples/delete_file/<#name#>","delete"));
    $grid->build();

    $data["grid"] = $grid->output;

    //enddatagrid2//
    
    $content["content"] = $this->load->view('rapyd/datagrid', $data, true);
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = highlight_code_file(THISFILE, "//datagrid2//", "//enddatagrid2//");
    $this->load->view('rapyd/template', $content);
  }

	function delete_file()
  {
    $this->load->helpers("file");
    $file = $_SERVER["DOCUMENT_ROOT"]."/uploads/banners/".$this->uri->segment(4);
    @unlink($file);
    redirect("rapyd/samples/datagrid2");
    
  }



  #############  language switch #############
	function lang(){

    if ($this->uri->segment(3)){

      $this->rapyd->language->clear_language(); 
      $this->rapyd->language->set_language($this->uri->segment(3));
      
    }
    redirect ("");
  }
  
}
?>