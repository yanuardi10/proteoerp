<?php
require_once('basecontroller.php');

class Datam extends BaseController 
{

	function Datam()
	{
		parent::BaseController(); 
    
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
    
    $data["title"]  = htmlspecialchars($article_one["title"]);
    $data["author"] = $article_one["author"]["firstname"] . " " .
                      $article_one["author"]["lastname"];
    
    //enddataobject//
    
    $this->_render("rapyd/dataobject", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"dataobject"),
                    )
                  );
    
  }
  
  
  //post_process callback 
  function relate_article_one($sender)
  {
    //relatearticleone//
    $art = $sender->get("article_id");
    $rel = 1;
  
    $r1=$sender->db->query("INSERT INTO articles_related SET art_id=$art,rel_id=$rel");
    $r2=$sender->db->query("INSERT INTO articles_related SET art_id=$rel,rel_id=$art");

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
    $result = $sender->db->query("DELETE FROM articles_related 
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
    
    $this->_render("rapyd/prepostprocess", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"prepostprocess"),
                      array("file"=>THISFILE, "id"=>"relatearticleone", "title"=>"relate_article_one function"),
                      array("file"=>THISFILE, "id"=>"removerelarticle", "title"=>"emove_rel_toany_article function"),
                    )
                  );
    
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

    $this->_render("rapyd/dataform", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"dataform"),
                    )
                  );


  }
  
}
?>