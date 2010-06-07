<?php
class Importa extends Controller {

	function Importa(){
		parent::Controller(); 
		$this->load->library("rapyd");

	}

	function index()
	{
		redirect("import/importa/tabla");
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
		return $id*$const;
	}

	function tabla()
	{

		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();
	
		$filter = new DataFilter("Buscar Importaciones");
		$filter->db->select("import.*, sprv.nombre");
		$filter->db->from("import");
		$filter->db->join("sprv","sprv.proveed=import.proveed","LEFT");

		$filter->numero = new inputField("N&uacute;mero", "numero");
		
		$filter->expediente = new inputField("Expediente", "expediente");
		
		$filter->fecha  = new dateField("Fecha", "fecha","d/m/Y");
		
		$filter->cod_cli = new dropdownField("Proveedor", "proveed");
		$filter->cod_cli->option("","");
		$filter->cod_cli->options("SELECT proveed, nombre FROM sprv ORDER BY nombre");

		$filter->buttons("reset","search");
		$filter->build();

		$uri = anchor('import/importa/dataedit/show/<#numero#>','<#numero#>');

		$grid = new DataGrid("");
		$grid->order_by("numero","desc");
		$grid->per_page = 5;
		
		$grid->column_detail("N&uacute;mero","numero", $uri, "size=8");
		$grid->column_orderby("fecha","fecha","fecha");
		$grid->column("Expediente","expediente");
		$grid->column("Nombre","nombre");

/*
		$grid->column("stotal","<number_format><#stotal#>|2</number_format>","align=right");
		$grid->column("impuesto","<number_format><#impuesto#>|2</number_format>","align=right");
		$grid->column("gtotal","<number_format><#gtotal#>|2</number_format>","align=right");
		$grid->column("callback test","<callback_test><#numero#>|3</callback_test>");
*/
		$grid->add("import/importa/dataedit/create");
		$grid->build();

		$data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Importaciones</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
  }

	##### iframes & actions #####
	function loadiframe($data=null, $head="", $resize="")
	{
		$template['head'] = $head;
		$template['content'] = $data;
		$template['onload'] = "";
		if ($resize!="")
		{
			$template['onload'] = "autofit_iframe('$resize');";
		}
		$this->load->view('rapyd/iframe', $template);
	}

	// comments datagrid 
	function importitem_grid()
	{
		//commentsgrid//
		$this->rapyd->load("datagrid");

		$numero = $this->uri->segment(4);

		$grid = new DataGrid("Articulos Importados","importitem");
		$grid->db->where("numero", $numero);

		$modify = site_url("import/importa/importitem_edit/$numero/modify/<#codigo#>");
		$delete = anchor("import/importa/importitem_edit/$numero/do_delete/<#codigo#>","delete");

		$grid->order_by("c&oacute;digo","desc");
		$grid->per_page = 6;
		$grid->column_detail("C&oacute;digo","codigo", $modify,"align=left");
		$grid->column("Descripci&oacute;n","descrip");

		$grid->column("Cant.","cantidad","align=right");
		$grid->column("Precio","<number_format><#precio#>|2</number_format>","align=right");
		$grid->column("Importe","<number_format><#importe#>|2</number_format>","align=right");

		$grid->column("borrar", $delete);
		$grid->add("import/importa/importitem_edit/$numero/create");
		$grid->build();

    $data['content'] = $filter->output.$grid->output;
		$data['title']   = "<h1>Importaciones</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	

	}

	// comments dataedit 
	function importitem_edit()
	{
		//commentsedit//
		$this->rapyd->load("dataedit");

		$numero = $this->uri->segment(4);
		$codigo = $this->uri->segment(6);

		$edit = new DataEdit("Productos Importados", 'importitem');
		$edit->back_uri = "import/importa/importitem_grid/$numero/list";

		$edit->numero = new autoUpdateField("n&uacute;mero",   $numero);
		$edit->codigo = new inputField("C&oacute;digo", "codigo");

		$edit->sr = new  containerField("BuscaCod","<div id='search_results' class='autocomplete'></div>");
		
		$edit->cantidad = new inputField("Cant.", "cantidad");
		$edit->precio   = new inputField("Precio", "precio");
		$edit->importe  = new inputField("Importe", "importe");

		$edit->back_save = true;
		$edit->back_cancel_save = true;
		$edit->back_cancel_delete = true;

		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();
		
		$data['content'] = $edit->output;           
    $data['title']   = "<h1>Carta</h1>";        
    $data["head"]    = $this->rapyd->get_head();
    $this->load->view('view_ventanas', $data);  
    
		
		//Prototype
		rapydlib("prototype","scriptaculous");
		
		$this->rapyd->script[] = "
		Event.observe(window, 'load', init, false);

		function init() {
			new Ajax.Autocompleter('codigo','search_results','".site_url("import/importa/ajaxbus")."', {} );
			Event.observe('codigo', 'keyup', do_search, false );
			do_search();
		}
		
		function do_search()
		{
			var url  = '".site_url('import/importa/ajaxbus')."';
			var pars = 'codigo='+escape(\$F('codigo'));
			new Ajax.Updater('search_results', url, {method:'post', parameters:pars});
			new Effect.Appear('saerch_results');
		} ";

		$head = $this->rapyd->get_head();
		$this->loadiframe($edit->output, $head, "related");

	}

	// Busca el Articulo por Ajax
	function ajaxbus() 
	{
		$digitado = $this->input->post('codigo');
		if ( !empty($digitado)) 
		{
			$salida = 'No hay resultados';
			$cueri = $this->db->query("SELECT CONCAT( codigo, ' - ',descrip) busca FROM sinv WHERE descrip like '$digitado%' limit 10");
			if ( $cueri->num_rows() > 0)
			{
				$salida = '<ul>';
				foreach ($cueri->result() as $row )
				{
					$salida .= "<li><b>$row->busca</b></li>";
				}		
				$salida .= '</ul>';
			}
			echo $salida;
		}
	}
	
	// comments datagrid 
	function importgext_grid()
	{
		//commentsgrid//
		$this->rapyd->load("datagrid");

		$numero = $this->uri->segment(4);

		$grid = new DataGrid("Gastos en el Exterior","importgext");
		$grid->db->where("numero", $numero);

		$modify = site_url("import/importa/importgext_edit/$numero/modify/<#codigo#>");
		$delete = anchor("import/importa/importgext_edit/$numero/do_delete/<#codigo#>","delete");

		$grid->order_by("factura","desc");
		$grid->per_page = 6;
		$grid->column_detail("Factura","factura", $modify,"align=left");
		$grid->column("Fecha","fecha");
		$grid->column("Descripci&oacute;n","descrip");
		
		$grid->column("Precio","<number_format><#precio#>|2</number_format>","align=right");
		$grid->column("Importe","<number_format><#importe#>|2</number_format>","align=right");

		$grid->column("borrar", $delete);
		$grid->add("import/importa/importgext_edit/$numero/create");
		$grid->build();

		$head = $this->rapyd->get_head();
		$this->loadiframe($grid->output, $head, "related");
		//endcommentsgrid//
	}

	function dataedit()
	{ 
		if (($this->uri->segment(5)==="1") && ($this->uri->segment(4)==="do_delete"))
		{
			show_error("Please do not delete the first record, it's required by DataObject sample");
		}

		$this->rapyd->load("dataedit");


		$edit = new DataEdit("IMPORTACIONES", "import");
		$edit->back_url = site_url("import/importa/tabla");

		$edit->numero = new autoUpdateField("N&uacute;mero", "numero");
		$edit->expediente = new inputField("Expediente", "expediente");
    
		$edit->fecha = new dateField("Fecha", "fecha");

		$edit->proveed = new dropdownField("Proveedor", "proveed");
		$edit->proveed->option("","");
		$edit->proveed->options("SELECT proveed, nombre FROM sprv ORDER BY nombre");

		$edit->moneda = new dropdownField("Moneda", "moneda");
		$edit->moneda->option("","");
		$edit->moneda->options("SELECT moneda, descrip FROM mone ORDER BY descrip");
    
		$edit->cambio = new inputField("Tasa de Cambio", "cambio");

		$edit->comenta = new editorField("Comentario", "comenta");
		$edit->comente->rows = 10;
		$edit->comente->cols = 50;
    
//    $edit->title->rule = "trim|required|max_length[20]";
/*
    $edit->body = new editorField("Body", "body");
    $edit->body->rule = "required";
    $edit->body->rows = 10;

    $edit->author = new dropdownField("Author", "author_id");
    $edit->author->option("","");
    $edit->author->options("SELECT author_id, firstname FROM authors");
*/
    $r_uri = "import/importa/importitem_grid/<#numero#>/list";
    $edit->related = new iframeField("related", $r_uri, "210");
    $edit->related->when = array("show","modify");

    $r_uri = "import/importa/importgext_grid/<#numero#>/list";
    $edit->related2 = new iframeField("related2", $r_uri, "210");
    $edit->related2->when = array("show","modify");
    
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
//    $edit->test = new freeField("Test", "test", "<callback_test><#article_id#>|3</callback_test>");


    $edit->build();
    $data["edit"] = $edit->output;
    $data["modulo"]  = "IMPORTACIONES";

    //$this->_session_dump();
    
	$content["lista"] = "
      <h3>Importaciones</h3>
      <div></div>
      <div class='line'></div>
      <a href='#' onclick='window.close()'>Cerrar</a>
      <div class='line'></div>\n<br><br><br>\n";
    

    $content["content"] = $this->load->view('rapyd/dataedit', $data, true);    
    $content["rapyd_head"] = $this->rapyd->get_head();
    $content["code"]  = "";
    $this->load->view('rapyd/tmpsolo', $content);
  }
}
?>