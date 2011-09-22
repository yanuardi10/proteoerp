<?php
/**
 * textField - is common input field (type=text)
 *
 * @package rapyd.components.fields
 * @author Felice Ostuni
 * @license http://www.fsf.org/licensing/licenses/lgpl.txt LGPL
 * @version 0.9.6
 */
 
 
 /**
 * textField
 *
 * @package    rapyd.components.fields
 * @author     Felice Ostuni
 * @access     public
 */
class inputField extends objField{

  var $type = "text";
  var $readonly=FALSE;
  var $autocomplete=TRUE;
  var $css_class = "input";

  function _getValue(){
    parent::_getValue();
  }
  
  function _getNewValue(){
    parent::_getNewValue();
  }

  function build(){
    if(!isset($this->size)){
      $this->size = 45;
    }
    $this->_getValue();
    
    $output = "";
    
    switch ($this->status){
    
      case "disabled":
      case "show":
        if ( (!isset($this->value)) ){
          $output = RAPYD_FIELD_SYMBOL_NULL;
        } elseif ($this->value == ""){
          $output = "";
        } else { //echo $this->showformat;
          if(substr_count($this->showformat,'decimal')>0){
            $output = nformat($this->value);
          }else{
            $output =  nl2br(htmlspecialchars($this->value));
          }
        }
        break;

      case "create":
      case "modify":
	if($this->type=='inputhidden')
	$t='hidden';
	else
	$t=$this->type;
	
        $value = ($this->type == "password")? "": $this->value;

        $attributes = array(
          'name'        => $this->name,
          'id'          => $this->name,
          'type'        => $t,
          'value'       => $value,
          'maxlength'   => $this->maxlength,
          'title'       => $this->title,
          'size'        => $this->size,
          'onclick'     => $this->onclick,
          'onchange'    => $this->onchange,
          'class'       => $this->css_class,
          'style'       => $this->style
          );
	if(isset($this->onkeyup)) $attributes['onkeyup']     =$this->onkeyup;
	if($this->readonly)       $attributes['readonly']    ='readonly';
	if(!$this->autocomplete)  $attributes['autocomplete']='off';
        
	$output = form_input($attributes) . $this->extra_output;
	
	if($this->type=='inputhidden')
	$output.="<span id='".$this->name."_val'  >$this->value</span>";
        
	break;

      case "hidden":
        $attributes = array(
          'name'        => $this->name,
          'id'          => $this->name,
          'type'        => "hidden",
          'value'       => $this->value);
        $output = form_input($attributes) . $this->extra_output;

        break;
        
      default:
    }
    $this->output = "\n".$output."\n";
  }
}
?>