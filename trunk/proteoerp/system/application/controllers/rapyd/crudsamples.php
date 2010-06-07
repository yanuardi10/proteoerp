<?php
require_once('basecontroller.php');

class Crudsamples extends BaseController {

  var $data_type = null;   
  var $data = null;

	function Crudsamples()
	{
		parent::BaseController(); 

	}



  ##### index #####
  function index()
  {
    redirect("rapyd/crudsamples/filteredgrid");
  }



  ##### callback test (for DataFilter + DataGrid) #####
  function test($id,$const)
  {
    //callbacktest//
    return $id*$const;
    //endcallbacktest//
  }



  ##### DataFilter + DataGrid #####
  function filteredgrid()
  {
        
    //filteredgrid//
    
    $this->rapyd->load("datafilter","datagrid");
    
    $this->rapyd->uri->keep_persistence();
    
    //filter
    $filter = new DataFilter("Article Filter");
    $filter->db->select("articles.*, authors.*");
    $filter->db->from("articles");
    $filter->db->join("authors","authors.author_id=articles.author_id","LEFT");

    $filter->title = new inputField("Title", "title");
    $filter->ispublic = new dropdownField("Public", "public");
    $filter->ispublic->option("","");
    $filter->ispublic->options(array("y"=>"Yes","n"=>"No"));
    
    $filter->buttons("reset","search");    
    $filter->build();
    

    $uri = "rapyd/crudsamples/dataedit/show/<#article_id#>";
    
    //grid
    $grid = new DataGrid("Article List");
    $grid->use_function("callback_test");
    $grid->order_by("article_id","desc");
    $grid->per_page = 5;
    $grid->use_function("substr","htmlspecialchars");
    $grid->column_detail("ID","article_id", $uri);
    $grid->column_orderby("title","title","title");
    $grid->column("body","<htmlspecialchars><substr><#body#>|0|4</substr></htmlspecialchars>....");
    $grid->column("Author","<#firstname#> <#lastname#>");
    $grid->column("callback test","<callback_test><#article_id#>|3</callback_test>");
    
    $grid->add("rapyd/crudsamples/dataedit/create");
    $grid->build();
    
    $data["crud"] = $filter->output . $grid->output;
    
    //endfilteredgrid//
    
    
    
    $this->_render("rapyd/crud", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"filteredgrid", "title"=>"Filtered Grid"),
                      array("file"=>THISFILE, "id"=>"callbacktest", "title"=>"Callback Test"),
                    )
                  );
    
  }
  
  
  

  // comments datagrid 
  function comments_grid()
  {
    //commentsgrid//
    $this->rapyd->load("datagrid");
    
    $art_id = intval($this->uri->segment(4));
    
    $grid = new DataGrid("Comments","comments");
    $grid->db->where("article_id", $art_id);

    $modify = site_url("rapyd/crudsamples/comments_edit/$art_id/modify/<#comment_id#>");
    $delete = anchor("rapyd/crudsamples/comments_edit/$art_id/do_delete/<#comment_id#>","delete");
    
    $grid->order_by("comment_id","desc");
    $grid->per_page = 6;
    $grid->column_detail("ID","comment_id", $modify);
    $grid->column("comment","<htmlspecialchars><substr><#comment#>|0|100</substr></htmlspecialchars>...");
    $grid->column("delete", $delete);
    $grid->add("rapyd/crudsamples/comments_edit/$art_id/create");
    $grid->build();
    
    $head = $this->rapyd->get_head();    
    $this->loadiframe($grid->output, $head, "related");
    //endcommentsgrid//
  }


  // comments dataedit 
  function comments_edit()
  {  
    //commentsedit//
    $this->rapyd->load("dataedit");

    $art_id = intval($this->uri->segment(4));
    
    $edit = new DataEdit("Comment Detail", "comments");
    $edit->back_uri = "rapyd/crudsamples/comments_grid/$art_id/list";

    $edit->aticle_id = new autoUpdateField("article_id",   $art_id);
    
    $edit->body = new textareaField("Comment", "comment");
    $edit->body->rule = "required";
    $edit->body->rows = 5;    
        
    $edit->back_save = true;
    $edit->back_cancel_save = true;
    $edit->back_cancel_delete = true;
    
    if ($art_id==1) {
      $edit->buttons("modify", "save", "undo", "back");
    } else {
      $edit->buttons("modify", "save", "undo", "delete", "back");
    }
    $edit->build();
    
    $head = $this->rapyd->get_head();
    $this->loadiframe($edit->output, $head, "related");
    //endcommentsedit//
  }



  ##### dataedit #####
  function dataedit()
  {  
    if ($this->rapyd->uri->get("do_delete",1)==1){
      show_error("Please do not delete the first record, it's required by DataObject sample");
    }
		
    //dataedit//
    $this->rapyd->load("dataedit");


    $edit = new DataEdit("Article Detail", "articles");
    $edit->back_uri = "rapyd/crudsamples/filteredgrid";

    $edit->title = new inputField("Title", "title");
    $edit->title->rule = "trim|required|max_length[20]";
    
    $edit->body = new editorField("Body", "body");
    $edit->body->rule = "required";
    $edit->body->rows = 10;    

    $edit->author = new dropdownField("Author", "author_id");
    $edit->author->option("","");
    $edit->author->options("SELECT author_id, firstname FROM authors");

    $r_uri = "rapyd/crudsamples/comments_grid/<#article_id#>/list";
    $edit->related = new iframeField("related", $r_uri, "210");
    $edit->related->when = array("show","modify");

    $edit->checkbox = new checkboxField("Public", "public", "y","n");
    
    $edit->datefield = new dateField("Date", "datefield","eu"); 
    
    if ($this->rapyd->uri->get("show",1)==1){
      $edit->buttons("modify", "save", "undo", "back");
    } else {
      $edit->buttons("modify", "save", "undo", "delete", "back");
    }

    $edit->use_function("callback_test");
    $edit->test = new freeField("Test", "test", "<callback_test><#article_id#>|3</callback_test>");
    
    
    $edit->build();
    $data["edit"] = $edit->output;
     
    //enddataedit//


    $this->_render("rapyd/dataedit", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"dataedit", "title"=>"dataedit"),
                      array("file"=>THISFILE, "id"=>"commentsgrid", "title"=>"comments grid"),
                      array("file"=>THISFILE, "id"=>"commentsedit", "title"=>"comments edit"),
                    )
                  );

  }
  

}
?>