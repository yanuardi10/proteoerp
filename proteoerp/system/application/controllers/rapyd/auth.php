<?php
require_once('basecontroller.php');

class Auth extends BaseController {


	function Auth()
	{

		parent::BaseController(); 

	}



  ##### index #####
  function index()
  {
  
    $this->_render("rapyd/auth", null, 
                    array(
                      array("file"=>VIEWPATH."auth.php"),
                    )
                  );
                  
  }






  ##### login #####
	function login()
  {
  
    //authlogin//

    if ($this->rapyd->auth->is_logged())
    {
      redirect("rapyd/auth");
      
    } else {
  
      $this->rapyd->load('dataform'); 
      
      $form = new DataForm("rapyd/auth/login/process");
      
      $form->nick = new inputField("Username", "user_");
      $form->nick->rule = "required";
      
      $form->pass = new passwordField("Password", "pass_");
      $form->pass->type = "text"; //firefox sucks 
      $form->pass->rule = "required";
      
      $form->captcha = new captchaField("captcha", "captcha");
      
      $form->submit("btn_submit", "login");  

      $form->build_form(); 
      $data["form"] = $form->output;
      
      if ($form->on_show() || $form->on_error())
      {
        //do nothing
      }
      
      if ($form->on_success())
      {
        //is a valid user
        $valid_user = $this->rapyd->auth->trylogin(
           $this->input->post("user_"),
           $this->input->post("pass_"));

        //has needed minimum role/level
        if ($this->rapyd->auth->check_role(3))
        { 
          redirect(keep_lang("rapyd/auth"));
        
        //username/password error OR user has not needed role/level
        } else {
                
          if ($valid_user)
          {
            $form->error_string = "Your role does not grant access to the resource requested";
            $this->rapyd->auth->logout(); 
          }
          else
          {
            $form->error_string = "Wrong username or password";
          }
          $form->build_form(); 
          
          $data["form"] = $form->output;
          
        }
      
      }
      

    //endauthlogin//

    $content["content"] = $this->load->view('rapyd/auth_login', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();

    $content["code"] = highlight_code_file(THISFILE, "//authlogin//", "//endauthlogin//");
    //$content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//related function<br /></span><br/>';    
    $this->load->view('rapyd/template', $content);
    
    $this->_render("rapyd/auth_login", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"authlogin"),
                    )
                  );
    
    
    
    
    
    }
    
  }
  

  function logout()
  {
    $this->rapyd->auth->logout();
    redirect("rapyd/auth/login");
  }





}
?>