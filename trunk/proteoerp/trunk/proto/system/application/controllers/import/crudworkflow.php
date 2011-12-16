<?php


class Crudworkflow extends Controller {

  var $data_type = null;   
  var $data = null;

	function Crudworkflow()
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
    redirect("rapyd/crudworkflow/filteredgrid");
  }



  ##### DataGrid #####
  function gridedit()
  {
    //gridedit//
   
  $this->rapyd->load("datagrid", "dataedit");
    
  //grid
  $grid = new DataGrid("Comment List", "comments");
  $grid->order_by("comment_id","desc");
  $grid->per_page = 5;

  $grid->use_function("substr");
  $grid->column("comment","<substr><#comment#>|0|50</substr>..", 'width="350"');
  
  $baseuri = "rapyd/crudworkflow/gridedit/osp/".$this->uri->segment(5);
  $link_show = anchor("$baseuri/show/<#comment_id#>","Show");
  $link_edit = anchor("$baseuri/modify/<#comment_id#>","Modify");
  $link_delete = anchor("$baseuri/delete/<#comment_id#>","Delete");
  $link_do_delete = anchor("$baseuri/do_delete/<#comment_id#>","Do Delete");

  $grid->add("$baseuri/create");
  $grid->column("actions", "$link_show - $link_edit - $link_delete - $link_do_delete");
  $grid->build();


  //edit
  $edit = new DataEdit("Comment Detail", "comments");
  $edit->back_uri = $baseuri;
  
  //flow redirection (direct return to the back_uri after actions)
  $edit->back_save = true;
  $edit->back_delete = true;
  $edit->back_cancel_save = true;
  $edit->back_cancel_delete = true;
  
  $edit->aticle_id = new autoupdateField("article_id", 1);

  $edit->body = new textareaField("Comment", "comment");
  $edit->body->rule = "required";
  $edit->body->rows = 5;
  
  $edit->buttons("modify", "save", "undo", "delete");
  $edit->build();

  $data["crud"] = $grid->output . $edit->output;
    
    //endgridedit//
    
    $content["content"] = $this->load->view('rapyd/crudworkflow', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = highlight_code_file(THISFILE, "//gridedit//", "//endgridedit//");
    $this->load->view('rapyd/template', $content);
  }



}
?>