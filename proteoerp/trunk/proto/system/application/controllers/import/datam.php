<?php


class Datam extends Controller {

  var $data_type = null;   
  var $data = null;

	function Datam()
	{
		parent::Controller(); 

    //required helpers&libs for samples
    $this->load->helper('url');
    $this->load->helper('text');
    $this->load->database();

		//rapyd library
		$this->load->library("rapyd");

		//language setting
		$this->curr_language =	$this->rapyd->language->language;
    $this->short_lang = ($this->curr_language=="italian")?"it":"en";
    
    //save language in session
    $this->rapyd->language->save_language();
    
    
    //I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
    define ("THISFILE",   APPPATH."controllers/rapyd/". $this->uri->segment(2).EXT);
	}



  ##### index #####
  function index()
  {
    redirect("rapyd/supercrud/dataedit/show/1");
  }


  ##### dataobject #####
  function dataobject()
  {
    //dataobject//
  
    $this->rapyd->load("dataobject");
    
    $do = new DataObject("articles");
    $do->rel_one_to_one("author", "authors","author_id");
    
    $do->load(1);
    $article_one = $do->get_all();
    
    $data["title"]  = $article_one["title"];
    $data["author"] = $article_one["author"]["firstname"] . " " .
                      $article_one["author"]["lastname"];
    
    //enddataobject//
    
    $content["content"] = $this->load->view('rapyd/dataobject', $data, true);    
    $content["rapyd_head"] = "";
    $content["code"] = highlight_code_file(THISFILE, "//dataobject//", "//enddataobject//");
    $this->load->view('rapyd/template', $content);
    
  }
  
  
  //post_process callback 
  function relate_article_one($sender)
  {
    //relatearticleone//
    $art = $sender->get("article_id");
    $rel = 1;
  
    $r1=$this->db->query("INSERT INTO articles_related SET art_id=$art,rel_id=$rel");
    $r2=$this->db->query("INSERT INTO articles_related SET art_id=$rel,rel_id=$art");
    
    $message = "<strong>relate_article_one()</strong> post_process func.<hr/>";
    
    if ($r1 && $r2) {
      return $message."Ok, article $art is now related with article $rel<hr/>";
    } else {
      return $message."Sorry, no relation built for article $art<hr/>";
    }
    //endrelatearticleone//
  }
  

  //pre_process callback 
  function remove_rel_toany_article($sender)
  {
    //removerelarticle//
    $art = $sender->get("article_id");
    $result = $this->db->query("DELETE FROM articles_related 
                                WHERE art_id=$art OR rel_id=$art");
    return $result;
    //endremoverelarticle//
  }
  

  ##### dataobject #####
  function prepostprocess()
  {
    $message = "";

    //prepostprocess//
    $this->rapyd->load("dataobject");
    
    $do = new DataObject("articles");
    $do->set("title","New Post");
    $do->set("body","New body");
    $do->set("public","n");
    
    $do->post_process("insert","relate_article_one");
    $saved = $do->save();
    
    $message = '<strong>$do->save()</strong><hr/>';

    if ($saved)
    {
      $art_id = $do->get("article_id");
      
      $message .= "record <strong>$art_id</strong> created.<br/>";
      $message .= nl2br(var_export($do->get_all(),true))."<hr/>";
      $message .= $do->post_process_result;
      
      $do->pre_process("delete","remove_rel_toany_article");
      
      $deleted = $do->delete();      
      $message .= '</div><br/><div class="note">';
      $message .= '<strong>$do->delete()</strong><hr/>';
      $message .= "<strong>remove_rel_toany_article()</strong> pre_process func.<hr/>";
  
      if ($deleted)
      {
        $message .= "OK, article $art_id is now not related with any article<hr/>";
        $message .= "record of article $art_id deleted<hr/>";
      } else {
        $message .= "pre_process fails, so article $art_id not deleted<hr/>";
      }
    } 
    else {
      $message  = "creation fails<br/>";
    }
    
    $data["content"] = $message;
    
    //endprepostprocess//
    
    $content["content"] = $this->load->view('rapyd/prepostprocess', $data, true);    
    $content["rapyd_head"] = "";
    $content["code"]  = highlight_code_file(THISFILE, "//prepostprocess//", "//endprepostprocess//");
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//relate_article_one function<br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//relatearticleone//", "//endrelatearticleone//");
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//remove_rel_toany_article function<br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//removerelarticle//", "//endremoverelarticle//");
    $this->load->view('rapyd/template', $content);
    
  }
  


  ##### dataform #####
  function dataform()
  {
    
    //dataform//
  
    $this->rapyd->load("dataform");

    $form = new DataForm("rapyd/datam/dataform/process", null);

    $form->title = new inputField("Title", "title");
    $form->title->rule = "trim|required|max_length[20]";
    
    $form->body = new editorField("Body", "body");
    $form->body->rule = "required";
    $form->body->rows = 10;    

    $form->checkbox = new checkboxField("Public", "public", "y","n");
    $form->submit("btnsubmit","SUBMIT");
    $form->build_form();

    if  ($form->on_show()) {
      $data["form_status"] = "Form displayed correctly";
    }

    if ($form->on_success()){
      $posted_data = nl2br(var_export($_POST,true));
      $data["form_status"] = "Successful post:<br/>".$posted_data;
    }
    
    if ($form->on_error()){
      $data["form_status"] = "There are errors";
    }

    $data["form"] = $form->output;

    //enddataform//

    $content["content"] = $this->load->view('rapyd/dataform', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = highlight_code_file(THISFILE, "//dataform//", "//enddataform//");
    $this->load->view('rapyd/template', $content);

  }
  
}
?>