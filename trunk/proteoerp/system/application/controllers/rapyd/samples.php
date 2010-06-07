<?php
require_once('basecontroller.php');

class Samples extends BaseController {

	function Samples()
	{
		parent::BaseController(); 

	}



  ##### index #####
  function index()
  {
    
    $this->_render("rapyd/home", null);
    
  }



  ##### dataset #####
  function dataset()
  {
    echo THISFILE;
    //dataset//
    $this->rapyd->load("dataset");
    
    $dataset = new DataSet($this->data);
    $dataset->per_page = 5;
    $dataset->build();
  
    $data["items"] = $dataset->data;
    $data["navigator"] = $dataset->navigator;    
    
    //enddataset//
    
    $this->_render("rapyd/dataset", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"dataset"),
                    )
                  );

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
    
    
    $this->_render("rapyd/datatable", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"datatable"),
                    )
                  );
    
  }


  ##### datagrid #####
  function datagrid($theme = '')
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
    
    $this->_render("rapyd/datagrid", $data, 
                    array(
                      array("file"=>THISFILE, "id"=>"datagrid"),
                    )
                  );
    
  }


  
}
?>