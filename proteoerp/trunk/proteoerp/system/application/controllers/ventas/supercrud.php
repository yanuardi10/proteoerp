<?php

class Supercrud extends Controller {

  var $data_type = null;   
  var $data = null;

	function Supercrud()
	{

		parent::Controller(); 

    //required helpers&libraries for samples
    $this->load->helper('url');
    $this->load->helper('text');
    $this->load->database();

		//rapyd library
		$this->load->library("rapyd");
    
    //I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
    define ("THISFILE",   APPPATH."controllers/rapyd/". $this->uri->segment(2).EXT);
	}



  ##### index #####
  function index()
  {
    redirect("rapyd/supercrud/dataedit/show/1");
  }


  ##### iframes & actions #####
  function loadiframe($data=null, $head="", $resize="")
  {
    $template['head'] = $head;
    $template['content'] = $data;
    $template['onload'] = "";
    if ($resize!=""){
      $template['onload'] = "autofit_iframe('$resize');";
    }
    $this->load->view('rapyd/iframe', $template);
  }


  function related()
  {
    //relatedlist//
    $this->rapyd->load("fields","datagrid");
    
    $artid = intval($this->uri->segment(4));
    
    $grid = new DataGrid("Related Articles");
    $grid->db->select("articles_related.*, articles.*, 
                      authors.firstname, authors.lastname");
    $grid->db->from("articles_related");
    $grid->db->join("articles","article_id = rel_id");
    $grid->db->join("authors","authors.author_id = articles.author_id", "LEFT");
    $grid->db->where("art_id",$artid);
    $grid->per_page = 5;
  
    $grid->column_orderby("article id","article_id","article_id",'width="100"');
    $grid->column_orderby("title","title","title");
    $grid->column_orderby("author", "<#firstname#> <#lastname#>", "lastname");
    
    $link = anchor("rapyd/supercrud/related_remove/$artid/to/<#article_id#>","delete");
    $grid->column("delete rel", $link);
    
    $grid->add("rapyd/supercrud/related_search/$artid/do/search");
    $grid->build();
    
    $head = $this->rapyd->get_head();    
    $this->loadiframe($grid->output, $head, "related");
    //endrelatedlist//  
  }




	//action	
  function related_remove()
	{
    //relatedremove//
    $art = intval($this->uri->segment(4));
    $rel = intval($this->uri->segment(6));
        
    $this->db->query("DELETE FROM articles_related 
       WHERE (art_id=$art AND rel_id=$rel) OR (art_id=$rel AND rel_id=$art)");
    
    redirect("rapyd/supercrud/related/$art/related");
    //endrelatedremove// 
	}

	//action
  function related_add()
	{
    //relatedadd//
    $art = intval($this->uri->segment(4));
    $rel = intval($this->uri->segment(6)); 
    
    $this->db->query("INSERT INTO articles_related SET art_id=$art, rel_id=$rel");
    $this->db->query("INSERT INTO articles_related SET art_id=$rel, rel_id=$art");
    
    redirect("rapyd/supercrud/related/$art/related");
    //endrelatedadd//
	}


  //iframe
  function related_search()
  {

    //relatedsearch//
    $this->rapyd->load("datafilter","datagrid");
    
    $artid = intval($this->uri->segment(4));

    $filter = new DataFilter("Search Associable Articles", "articles");  
    $filter->db->where("article_id<>",$artid);        
    
    ## subqueries works only for mysql4.1+
    //$filter->db->where("article_id NOT IN 
    // (SELECT rel_id FROM articles_related WHERE art_id = $artid)
    //"); 

    $subquery = $this->db->query("SELECT rel_id FROM articles_related WHERE art_id = $artid");
    if ($subquery->num_rows() > 0)
    {
      foreach($subquery->result_array() as $row){
        $rels[]= $row["rel_id"];
      }
      $not_in = join(",",$rels);
      $filter->db->where("article_id NOT IN ($not_in)"); 
    }

    $filter->title = new inputField("title", "title");
    $filter->artid = new inputField("article id", "article_id");
    $filter->artid->clause="where";    
    $filter->buttons("reset","search"); 
    
    $uri = site_url("rapyd/supercrud/related/$artid/related");
    $action = "javascript:window.location='".$uri."';";
    $filter->button_status("btn_undo", "Undo", $action, "TR", "create", "button");
    $filter->build();
        
    $grid = new DataGrid("List of associable articles");

    $grid->per_page = 6;
    $grid->column_orderby("article id","article_id","article_id",'width="100"');
    $grid->column_orderby("title","title","title");
    
    $link = anchor("rapyd/supercrud/related_add/$artid/to/<#article_id#>",
            "add rel");
    $grid->column("add rel", $link);
    $grid->build();

    $head = $this->rapyd->get_head();
    $this->loadiframe($filter->output.$grid->output, $head, "related");
    //endrelatedsearch//
  }




  ##### dataedit #####
  function dataedit()
  {  
  

    if (($this->uri->segment(5)==="1") && ($this->uri->segment(4)==="do_delete")){
      show_error("Please do not delete the first record, it's required by DataObject sample");
    }
  
    //dataedit//
    $this->rapyd->load("dataedit");

    $edit = new DataEdit("Article Detail", "articles");
    $edit->back_url = site_url("rapyd/supercrud/filteredgrid");

    $edit->title = new inputField("Title", "title");
    $edit->title->rule = "trim|required|max_length[20]";
    
    $edit->body = new textareaField("Body", "body");
    $edit->body->rule = "required";
    $edit->body->rows = 8;

    $r_uri = "rapyd/supercrud/related/<#article_id#>/related/show";
    $edit->related = new iframeField("related", $r_uri, "210");
    $edit->related->when = array("show","modify");

    $edit->author = new dropdownField("Author", "author_id");
    $edit->author->option("","");
    $edit->author->options("SELECT author_id, firstname FROM authors");

    $edit->checkbox = new checkboxField("Public", "public", "y","n");
    
    $edit->datefield = new dateField("Date", "datefield","d/m/Y"); 
    
    if ($this->uri->segment(4)==="1"){
      $edit->buttons("modify", "save", "undo");
    } else {
      $edit->buttons("modify", "save", "undo", "delete");
    }
    
    $edit->build();
    $data["edit"] = $edit->output;
     
    //enddataedit//

    $content["content"] = $this->load->view('rapyd/dataedit_complex', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    
    $content["code"]  =  highlight_code_file(THISFILE, "//dataedit//", "//enddataedit//");
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//related function<br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//relatedlist//", "//endrelatedlist//");
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//related_search function<br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//relatedsearch//", "//endrelatedsearch//");    
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//related_remove function<br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//relatedremove//", "//endrelatedremove//");
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//related_add function<br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//relatedadd//", "//endrelatedadd//");
    $this->load->view('rapyd/template', $content);
  }


}
?>