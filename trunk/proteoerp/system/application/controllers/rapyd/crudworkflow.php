<?php
require_once('basecontroller.php');


class Crudworkflow extends BaseController {


	function Crudworkflow()
	{
		parent::BaseController(); 

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
  $grid->column("comment","<htmlspecialchars><substr><#comment#>|0|50</substr></htmlspecialchars>..", 'width="350"');
  
  $baseuri = "rapyd/crudworkflow/gridedit/osp/".$this->uri->segment(5);
  $link_show = anchor("$baseuri/show/<#comment_id#>","Show");
  $link_edit = anchor("$baseuri/modify/<#comment_id#>","Modify");
  $link_delete = anchor("$baseuri/delete/<#comment_id#>","Delete");

  $grid->add("$baseuri/create");
  $grid->column("actions", "$link_show - $link_edit - $link_delete");
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
    
    $this->_render("rapyd/crudworkflow", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"gridedit"),
                    )
                  );
    
    
  }



}
?>