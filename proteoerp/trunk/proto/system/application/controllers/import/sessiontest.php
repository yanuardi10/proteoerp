<?php


class Sessiontest extends Controller {

  var $data_type = null;   
  var $data = null;

	function Sessiontest()
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
    redirect("rapyd/sessiontest/filteredgrid");
  }

  ##### DataGrid #####
  function grid()
  {
    //filteredgrid//
  
    $this->rapyd->load("datagrid");

    $grid = new DataGrid("Article List");
    
    $linkSTDshow = site_url("rapyd/sessiontest/dataedit/gfid/$grid->gfid/show/<#article_id#>");
        
    $grid->db->select("articles.*, authors.*");
    $grid->db->from("articles");
    $grid->db->join("authors","authors.author_id=articles.author_id","LEFT");
    
    $grid->order_by("article_id","asc");
    $grid->per_page = 5;
    $grid->use_function("substr");
    $grid->column_detail("ID","article_id", $linkSTDshow);
    $grid->column_orderby("title","title","title");
    $grid->column("body","<substr><#body#>|0|4</substr>....");
    $grid->column("Author","<#firstname#> <#lastname#>");
    
    
    $linkshow = anchor("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/show/<#article_id#>","Show");   
    $linkedit = anchor("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/modify/<#article_id#>","Modify");
    $linkdelete = anchor("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/delete/<#article_id#>","Delete");
    $linkcreate = anchor("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/create","Create");

    $grid->column("Actions", $linkcreate." - ".$linkshow." - ".$linkedit." - ".$linkdelete);

    $grid->add("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/create");
    
    $grid->build();
    
    $test= "GFID current Value => ".$grid->gfid."<br>";
    $data["crud"] = $test. $grid->output;
    
    //endfilteredgrid//
    
    $content["content"] = $this->load->view('rapyd/crud', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = highlight_code_file(THISFILE, "//filteredgrid//", "//endfilteredgrid//");
    $this->load->view('rapyd/template_test', $content);
  }
  
  ##### DataFilter + DataGrid #####
  function filteredgrid()
  {
    //filteredgrid//
  
    $this->rapyd->load("datafilter","datagrid");
    
    //filter
    $filter = new DataFilter("Article Filter");
    //****************************************************************************
    //* NOTES 1:
    //* After the new DataFilter() instruction the DF constructor call the DF    * 
    //* sniff_action() methode witch it gives the GFID if it is not set.         *
    //* so $filter->gfid is knowed directly after this instantiation....           *
    //****************************************************************************      
    $filter->db->select("articles.*, authors.*");
    $filter->db->from("articles");
    $filter->db->join("authors","authors.author_id=articles.author_id","LEFT");

    $filter->title = new inputField("Title", "title");
    $filter->ispublic = new dropdownField("Public", "public");
    $filter->ispublic->option("","");
    $filter->ispublic->options(array("y"=>"Yes","n"=>"No"));
    
    $filter->buttons("reset","search");    
    $filter->build();
    

    //grid
    
    //****************************************************************************
    //* NOTES 2:                                                                 *
    //* The Standard acces link to DataEdit, affected to the colum_detail col    * 
    //* (No support for back session because GFID in not into the URI).          *
    //* Don't use it for the test....                                            * 
    //****************************************************************************    
    $linkSTDshow = site_url('rapyd/sessiontest/dataedit/show/<#article_id#>');

    $grid = new DataGrid("Article List",null,$filter->uri_array);
    $grid->order_by("article_id","asc");
    $grid->per_page = 5;
    $grid->use_function("substr");
    $grid->column_detail("ID","article_id", $linkSTDshow);
    $grid->column_orderby("title","title","title");
    $grid->column("body","<substr><#body#>|0|4</substr>....");
    $grid->column("Author","<#firstname#> <#lastname#>");
    
    //*********************************************************************************
    //* NOTES 3:                                                                      *
    //* The differents acces link to DataEdit in diffenrent states, with GFID in URI  *
    //* DE is now compliance with GFID in URI, to generate right back_url             *
    //*********************************************************************************
    
    $linkshow = anchor("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/show/<#article_id#>","Show");   
    $linkedit = anchor("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/modify/<#article_id#>","Modify");
    $linkdelete = anchor("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/delete/<#article_id#>","Delete");
    $linkcreate = anchor("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/create","Create");
		//*********************************************************************
		//* NOTES 4:                                                          *
		//* URI to DataEdit Change:                                           *
		//* For me it is the most problematic thing of this solution          *
		//* the URI to DE need now a DF property value for that back session  *
		//* Work. The uri IS MORE COMPLICATE;                                 *
		//*                                                                   
		//*********************************************************************
    $grid->column("Actions", $linkcreate." - ".$linkshow." - ".$linkedit." - ".$linkdelete);
    
   	//****************************************************************
   	//* NOTES 5:                                                     *   
   	//* We also need to add the GFID in the 'add' URI                *
   	//****************************************************************
    $grid->add("rapyd/sessiontest/directdataedit/gfid/$grid->gfid/create");
    
    $grid->build();
    
    $test= "GFID current Value => ".$filter->gfid."<br>";
    $data["crud"] = $test.$filter->output . $grid->output;
    
    //endfilteredgrid//
    
    $content["content"] = $this->load->view('rapyd/crud', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = highlight_code_file(THISFILE, "//filteredgrid//", "//endfilteredgrid//");
    $this->load->view('rapyd/template_test', $content);
  }

  ##### Standard dataedit #####
  function dataedit()
  {  
    if (($this->uri->segment(5)==="1") && ($this->uri->segment(4)==="do_delete")){
      show_error("Please do not delete the first record, it's required by DataObject sample");
    }
  
    //dataedit//
    $this->rapyd->load("dataedit");

    $edit = new DataEdit("Article Detail", "articles");

    $edit->back_url = site_url("rapyd/sessiontest/filteredgrid");

    $edit->title = new inputField("Title", "title");
    $edit->title->rule = "trim|required|max_length[20]";
    
    $edit->body = new editorField("Body", "body");
    $edit->body->rule = "required";
    $edit->body->rows = 10;    

    $edit->author = new dropdownField("Author", "author_id");
    $edit->author->option("","");
    $edit->author->options("SELECT author_id, firstname FROM authors");


    $edit->checkbox = new checkboxField("Public", "public", "y","n");
    
    $edit->datefield = new dateField("Date", "datefield","eu"); 
    
    if ($this->uri->segment(4)==="1"){
      $edit->buttons("modify", "save", "undo", "back");
    } else {
      $edit->buttons("modify", "save", "undo", "delete", "back");
    }
    
    $edit->build();
    $data["edit"] = $edit->output;
     
    //enddataedit//

    $content["content"] = $this->load->view('rapyd/dataedit', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"]  = highlight_code_file(THISFILE, "//dataedit//", "//enddataedit//");
    $this->load->view('rapyd/template_test', $content);
  }
  
  ##### Direct dataedit #####
  function directdataedit()
  {  
    if (($this->uri->segment(5)==="1") && ($this->uri->segment(4)==="do_delete")){
      show_error("Please do not delete the first record, it's required by DataObject sample");
    }
  
    //directdataedit//
    
    $this->rapyd->load("dataedit");

    $edit = new DataEdit("Article Detail", "articles");
 		
		//$edit->gfid = $GFID;
     /********************************************************************************************
     * NOTES 6:                                                                                  *
     * Now the controller doesn't need extra code to read the GFID.DE component read it directly.*
     * DE->back_url doesn't need '/back' URI clause to work with back session, if DE find a GFID *
     * it automaticaly rewrite back_url and add GFID and '/back'                                 *
     * Theres is 2 ways to give the GFID to DE by the URI or by "$edit->gfid = $GFID;" if we use *
     * it befor DE->build(); (see NOTE 1 in dataedit class)                                      *
     *********************************************************************************************/    

    $edit->back_url = site_url("rapyd/sessiontest/grid");
    
    //Seting for direct return to the Grid
    $edit->back_save = true;
    $edit->back_cancel_save = true;
    $edit->back_cancel_delete = true;
	
    $edit->title = new inputField("Title", "title");
    $edit->title->rule = "trim|required|max_length[20]";
    
    $edit->body = new editorField("Body", "body");
    $edit->body->rule = "required";
    $edit->body->rows = 10;    

    $edit->author = new dropdownField("Author", "author_id");
    $edit->author->option("","");
    $edit->author->options("SELECT author_id, firstname FROM authors");


    $edit->checkbox = new checkboxField("Public", "public", "y","n");
    
    $edit->datefield = new dateField("Date", "datefield","eu"); 
    
    if ($this->uri->segment(4)==="1"){
      $edit->buttons("modify", "save", "undo", "back");
    } else {
      $edit->buttons("modify", "save", "undo", "delete", "back");
    }
    
    $edit->build();
    $test = "Current GFID =".(string)$edit->gfid."<br> Current back url = ".$edit->back_url;
    $data["edit"] = $edit->output;
     
    //enddirectdataedit//

    $content["content"] =$test. $this->load->view('rapyd/dataedit', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"]  = highlight_code_file(THISFILE, "//directdataedit//", "//enddirectdataedit//");

    $this->load->view('rapyd/template_test', $content);
  }
}
?>