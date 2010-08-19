<?php


class Crudsamples extends Controller {

  var $data_type = null;   
  var $data = null;

	function Crudsamples()
	{
		parent::Controller(); 

    //required helpers for samples
    $this->load->helper('url');
    $this->load->helper('text');

		//rapyd library
		$this->load->library("rapyd");
    
    //I use THISFILE, instead __FILE__ to prevent some documented php-bugs with higlight_syntax()&__FILE__
    define ("THISFILE",   APPPATH."controllers/import/". $this->uri->segment(2).EXT);
	}



  ##### index #####
  function index()
  {
    redirect("import/crudsamples/filteredgrid");
  }

  ##### utility, show you $_SESSION status #####
  function _session_dump()
  {
    echo '<div style="height:200px; background-color:#fdfdfd; overflow:auto;">';
    echo '<pre style="font: 11px Courier New,Verdana">';
    var_export($_SESSION);
    echo '</pre>';
    echo '</div>';
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
    $filter = new DataFilter("Filtro de Facturas");
    $filter->db->select("fmay.*, scli.*");
    $filter->db->from("fmay");
    $filter->db->join("scli","scli.cliente=fmay.cod_cli","LEFT");

    $filter->numero = new inputField("Numero", "numero");
    $filter->fecha  = new dateField("Fecha", "fecha","d/m/Y");
//    $filter->cod_cli = new inputField("Numero", "numero");
//    $filter->title = new inputField("Numero", "numero");
    $filter->cod_cli = new dropdownField("Cliente", "cod_cli");
    $filter->cod_cli->option("","");
    $filter->cod_cli->options("SELECT cliente, nombre FROM scli ORDER BY nombre");

    $filter->buttons("reset","search");
    $filter->build();


    $uri = "import/crudsamples/dataedit/show/<#numero#>";

    //grid
    $grid = new DataGrid("Article List");
    $grid->use_function("callback_test");
    $grid->order_by("numero","desc");
    $grid->per_page = 5;
    $grid->use_function("substr");
    $grid->column_detail("Numero","numero", $uri, "size=14");
    $grid->column_orderby("fecha","fecha","fecha");
    $grid->column("Nombre","nombre");
    $grid->column("stotal","<number_format><#stotal#>|2</number_format>","align=right");

    $grid->column("impuesto","<number_format><#impuesto#>|2</number_format>","align=right");
    $grid->column("gtotal","<number_format><#gtotal#>|2</number_format>","align=right");

    
    $grid->column("callback test","<callback_test><#numero#>|3</callback_test>");

    $grid->add("import/crudsamples/dataedit/create");
    $grid->build();

    $data["crud"] = $filter->output . $grid->output;

    //endfilteredgrid//

    //$this->_session_dump();

    $content["content"] = $this->load->view('rapyd/crud', $data, true);
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"] = '';
/*
    $content["code"] = highlight_code_file(THISFILE, "//filteredgrid//", "//endfilteredgrid//");
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//callback test function<br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//callbacktest//", "//endcallbacktest//");
*/
    $this->load->view('rapyd/template', $content);
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

  // comments datagrid 
  function comments_grid()
  {
    //commentsgrid//
    $this->rapyd->load("datagrid");

    $numero = $this->uri->segment(4);

    $grid = new DataGrid("Comments","itfmay");
    $grid->db->where("numero", $numero);

    $modify = site_url("import/crudsamples/comments_edit/$numero/modify/<#codigo#>");
    $delete = anchor("import/crudsamples/comments_edit/$numero/do_delete/<#codigo#>","delete");

    $grid->order_by("codigo","desc");
    $grid->per_page = 6;
    $grid->column_detail("Codigo","codigo", $modify,"align=left");
    $grid->column("Descripcion","descrip");

    $grid->column("Cant.","cantidad","align=right");
    $grid->column("Frac.","fraccion","align=right");
    $grid->column("Precio","<number_format><#precio#>|2</number_format>","align=right");
    $grid->column("Importe","<number_format><#importe#>|2</number_format>","align=right");

    $grid->column("borrar", $delete);
    $grid->add("import/crudsamples/comments_edit/$numero/create");
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

    $art_id = $this->uri->segment(4);
    $codigo = $this->uri->segment(6);

//    $regi = new DataObject("itfmay");
//    $sol = $regi->load(array("numero"=>"$art_id","codigo"=>"$codigo"));
    
    $edit = new DataEdit("Productos", 'itfmay');
    $edit->back_uri = "import/crudsamples/comments_grid/$art_id/list";

/*
    $edit->aticle_id = new autoUpdateField("article_id",   $art_id);
    $edit->body = new textareaField("Comment", "comment");
    $edit->body->rule = "required";
    $edit->body->rows = 5;
*/
#echo "numero $art_id  codigo $codigo  sol $sol";

    $edit->numero = new autoUpdateField("numero",   $art_id);
    $edit->codigo = new inputField("Codigo", "codigo");

    $edit->descrip = new inputField("Descripcion", "descrip");

    $edit->cantidad = new inputField("Cant.", "cantidad");
    $edit->fraccion = new inputField("Frac.", "fraccion");


    $edit->back_save = true;
    $edit->back_cancel_save = true;
    $edit->back_cancel_delete = true;

    $edit->buttons("modify", "save", "undo", "delete", "back");
    $edit->build();
//    $data["edit"] = $edit->output;

    $head = $this->rapyd->get_head();

//echo "out ".$edit->output;
    $this->loadiframe($edit->output, $head, "related");
    //endcommentsedit//

//    $content["content"] = $this->load->view('rapyd/dataedit', $data, true);    
//    $content["rapyd_head"] = $head;
//    $content["code"]  = "";
//    $this->load->view('rapyd/template', $content);

  }



  ##### dataedit #####
  function dataedit()
  { 
    if (($this->uri->segment(5)==="1") && ($this->uri->segment(4)==="do_delete")){
      show_error("Please do not delete the first record, it's required by DataObject sample");
    }

    //dataedit//
    $this->rapyd->load("dataedit");


    $edit = new DataEdit("Facturas", "fmay");
    $edit->back_url = site_url("import/crudsamples/filteredgrid");

    $edit->title = new inputField("Numero", "numero");
    $edit->fecha = new dateField("Fecha", "fecha");
    $edit->nombre = new inputField("Nombre", "nombre");


//    $edit->title->rule = "trim|required|max_length[20]";
/*
    $edit->body = new editorField("Body", "body");
    $edit->body->rule = "required";
    $edit->body->rows = 10;

    $edit->author = new dropdownField("Author", "author_id");
    $edit->author->option("","");
    $edit->author->options("SELECT author_id, firstname FROM authors");
*/
    $r_uri = "import/crudsamples/comments_grid/<#numero#>/list";
    $edit->related = new iframeField("related", $r_uri, "210");
    $edit->related->when = array("show","modify");
/*
    $edit->checkbox = new checkboxField("Public", "public", "y","n");

    $edit->datefield = new dateField("Date", "datefield","eu"); 
*/
    if ($this->uri->segment(4)==="1"){
      $edit->buttons("modify", "save", "undo", "back");
    } else {
      $edit->buttons("modify", "save", "undo", "delete", "back");
    }

    $edit->use_function("callback_test");
    $edit->test = new freeField("Test", "test", "<callback_test><#article_id#>|3</callback_test>");


    $edit->build();
    $data["edit"] = $edit->output;

    //enddataedit//

    //$this->_session_dump();

    $content["content"] = $this->load->view('rapyd/dataedit', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"]  = "";
/*
    $content["code"]  = highlight_code_file(THISFILE, "//dataedit//", "//enddataedit//");
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//comments grid <br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//commentsgrid//", "//endcommentsgrid//");
    $content["code"] .= '<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//comments edit <br /></span><br/>';
    $content["code"] .= highlight_code_file(THISFILE, "//commentsedit//", "//endcommentsedit//");
*/
    $this->load->view('rapyd/template', $content);
  }
}
?>