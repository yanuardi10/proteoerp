<?php
class Maes extends Controller {
	
	function Maes(){
		parent::Controller(); 
		$this->load->library("rapyd");
		//$this->rapyd->set_connection('supermer');
		//$this->load->database('supermer',TRUE);
	}
	
	function index()
	{
		//$this->datasis->modulo_id(309,1);
		redirect("supermercado/maes/filteredgrid");
	}
	
	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");
		//$this->rapyd->uri->keep_persistence();
		rapydlib("prototype");
		$ajax_onchange = '
			  function get_familias(){
			    var url = "'.site_url('supermercado/maes/maesfamilias').'";
			    var pars = "dpto="+$F("depto");
			    var myAjax = new Ajax.Updater("td_familia", url, { method: "post", parameters: pars });
			    
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var gmyAjax = new Ajax.Updater("td_grupo", url);
			  }
			  
			  function get_grupo(){
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var pars = "dpto="+$F("depto")+"&fami="+$F("familia");
			    var myAjax = new Ajax.Updater("td_grupo", url, { method: "post", parameters: pars });
			  }';

		$filter = new DataFilter2("Filtro por Producto", 'maes');
		$filter->script($ajax_onchange);

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->db_name='codigo';
		$filter->codigo->size=20;
		$filter->codigo->maxlength=15;
				
		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->option("","" );
		$filter->tipo->option("I","Inventario" );
		$filter->tipo->option("L","Licores"    );
		$filter->tipo->option("P","Por peso"   );
		$filter->tipo->option("K","Desposte"   );
		$filter->tipo->option("C","Combo"      );
		$filter->tipo->option("F","Farmaco"    );
		$filter->tipo->option("S","Servicio"   );
		$filter->tipo->option("R","Receta"     );
		$filter->tipo->option("D","Desactivado");
		$filter->tipo->style='width:110px;';
		
		$filter->marca = new dropdownField("Marca", "marca");
		$filter->marca->option("","");  
		$filter->marca->options("SELECT marca as codigo, marca FROM marc ORDER BY marca");  	
		$filter->marca->style='width:180px;';
			
		$filter->clave = new inputField("Clave", "clave");
		$filter->clave->size=15;      			
		$filter->clave->maxlength=10;
		
		$filter->dpto = new dropdownField("Departamento", "depto");
		$filter->dpto->option("","");
		$filter->dpto->options("SELECT depto,descrip FROM dpto WHERE tipo='I' ORDER BY descrip");
		$filter->dpto->onchange = "get_familias();";
		 
		$filter->familia = new dropdownField("Familia", "familia");
		$filter->familia->option("","Seleccione un departamento");
		$filter->familia->onchange = "get_grupo();";

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->option("","Seleccione una familia");
		
		$filter->buttons("reset","search");
		$filter->build();
		
		$uri = "supermercado/maes/dataedit/show/<#codigo#>";
		
		$grid = new DataGrid("Lista de Art&iacute;culos");
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;
		$link=anchor('/supermercado/maes/dataedit/show/<#codigo#>','<#codigo#>');
		
		$grid->column("c&oacute;digo",$link);
		$grid->column("Descripci&oacute;n","descrip");
		$grid->column("Marca","marca");
		$grid->column("Departamento","descrip");
		$grid->column("Proveedor","nprv1");
										
		$grid->add("supermercado/maes/dataedit/create");
		$grid->build();
		
		$data["crud"] = $filter->output . $grid->output;
		$data["titulo"] = 'Lista de Art&iacute;culos';

		$content["content"]   = $this->load->view('rapyd/crud', $data, true);
		$content["rapyd_head"] = $this->rapyd->get_head();
		$content["code"] = '';
		$content["lista"] = "
			<h3>Editar o Agregar</h3>
			<div>Con esta pantalla se puede editar o agregar datos a los Departamentos del M&oacute;dulo de Inventario</div>
			<div class='line'></div>
			<a href='#' onclick='window.close()'>Cerrar</a>
			<div class='line'></div>\n<br><br><br>\n";
		$this->load->view('rapyd/tmpsolo', $content);
	}

	function dataedit() {  
		$this->rapyd->load('dataedit'); 
		//rapydlib("prototype");
		$ajax_onchange = '
			  function get_familias(){
			    var url = "'.site_url('supermercado/maes/maesfamilias').'";
			    var pars = "dpto="+$F("depto");
			    var myAjax = new Ajax.Updater("td_familia", url, { method: "post", parameters: pars });
			    
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var gmyAjax = new Ajax.Updater("td_grupo", url);
			  }
			  
			  function get_grupo(){
			    var url = "'.site_url('supermercado/maes/maesgrupos').'";
			    var pars = "dpto="+$F("depto")+"&fami="+$F("familia");
			    var myAjax = new Ajax.Updater("td_grupo", url, { method: "post", parameters: pars });
			  }';
		
		
		$edit = new DataEdit("Maestro de Inventario", "maes");
		$edit->script($ajax_onchange);
		$edit->script($ajax_onchange,"modify");
		$edit->back_url = site_url("supermercado/maes/filteredgrid");
		
		$edit->codigo = new inputField("C&oacute;digo", "codigo");
		$edit->codigo->size=20;
		$edit->codigo->maxlength=15;
		$edit->codigo->rule = "required";
		$edit->codigo->mode="autohide";
		
		$edit->marca = new dropdownField("Marca", "marca");
		$edit->marca->style='width:110px;';
		$edit->marca->option("","");  
		$edit->marca->options("SELECT marca as codigo, marca FROM marc ORDER BY marca");  
		
		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->style='width:110px;';
		$edit->tipo->option("I","Inventario" );
		$edit->tipo->option("L","Licores"    );
		$edit->tipo->option("P","Por peso"   );
		$edit->tipo->option("K","Desposte"   );
		$edit->tipo->option("C","Combo"      );
		$edit->tipo->option("F","Farmaco"    );
		$edit->tipo->option("S","Servicio"   );
		$edit->tipo->option("R","Receta"     );
		$edit->tipo->option("D","Desactivado");
		
		$edit->dpto = new dropdownField("Departamento", "depto");
		$edit->dpto->option("","");
		$edit->dpto->options("SELECT depto,descrip FROM dpto WHERE tipo='I' ORDER BY descrip");
		$edit->dpto->onchange = "get_familias();";

		$edit->familia = new dropdownField("Familia", "familia");
		$edit->familia->onchange = "get_grupo();";

		$edit->grupo = new dropdownField("Grupo", "grupo");
		
		$edit->descrip = new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size=48;
		$edit->descrip->maxlength=40;
		$edit->descrip->rule = "required";
		
		$edit->corta = new inputField("Descripci&oacute;n Corta", "corta");
		$edit->corta->size=28;
		$edit->corta->maxlength=20;
		
		$edit->susti = new inputField("Clave", "susti");
		$edit->susti->size=15;
		$edit->susti->maxlength=10;
		
		$edit->serial = new dropdownField("Serializar", "serial");
		$edit->serial->style='width:60px;';
		$edit->serial->option("N","No" );
		$edit->serial->option("S","Si" );
		$edit->serial->when =array("show");
		
		$edit->tamano = new inputField("Tama&ntilde;o", "tamano");
		$edit->tamano->size=15;
		$edit->tamano->maxlength=11;
		$edit->tamano->when =array("show");
		
		$edit->medida = new inputField("Medida", "medida");
		$edit->medida->size=15;
		$edit->medida->maxlength=11;
		$edit->medida->when =array("show");		
		
		$edit->minimo = new inputField("Existencia Minima", "minimo");
		$edit->minimo->size=15;
		$edit->minimo->maxlength=11;
		$edit->minimo->when =array("show");
		
		$edit->maximo = new inputField("Existencia Maxima", "maximo");
		$edit->maximo->size=15;
		$edit->maximo->maxlength=11;
		$edit->maximo->when =array("show");		
		
		$edit->ordena = new inputField("Existencia Ordenada", "ordena");
		$edit->ordena->size=15;
		$edit->ordena->maxlength=11;
		$edit->ordena->when =array("show");		
		
		$edit->alcohol = new inputField("Licor G/I", "alcohol");
		$edit->alcohol->size=15;
		$edit->alcohol->maxlength=11;		
		
		$edit->implic = new inputField("Impuesto por alcohol", "implic");
		$edit->implic->size=8;
		$edit->implic->maxlength=6;
		
		$edit->conjunto = new inputField("Conjunto de Articulo", "conjunto");
		$edit->conjunto->size=8;
		$edit->conjunto->maxlength=8;
		
		$edit->ultimo = new inputField("Ultimo", "ultimo");
		$edit->ultimo->css_class='inputnum';
		$edit->ultimo->size=21;
		$edit->ultimo->maxlength=17;
		
		$edit->iva = new inputField("Iva", "iva");
		$edit->iva->css_class='inputnum';
		$edit->iva->onchange = "calculos('M');";
		$edit->iva->size=10;
		$edit->iva->maxlength=8;
		
		$edit->costo = new inputField("Promedio", "costo");
    $edit->costo->css_class='inputnum';
		$edit->costo->onchange = "calculos(costo);";
		$edit->costo->size=21;
		$edit->costo->maxlength=17;
		
		$edit->fcalc = new dropdownField("Base C&aacute;lculo", "fcalc");
		$edit->fcalc->style='width:150px;';
		$edit->fcalc->option("U","Ultimo" );
		$edit->fcalc->option("P","Promedio" );
		$edit->fcalc->onchange = "calculos('M');";
		
		$edit->redondeo = new dropdownField("Redondear", "redondeo");
		$edit->redondeo->style='width:150px;';
		$edit->redondeo->option("NO","No");
		$edit->redondeo->option("P0","Precio Decimales");
		$edit->redondeo->option("P1","Precio Unidades" );  
		$edit->redondeo->option("P2","Precio Decenas"  );
		$edit->redondeo->option("B0","Base Decimales"  );
		$edit->redondeo->option("B1","Base Unidades"   );
		$edit->redondeo->option("B2","Base Decenas"    );
    $edit->redondeo->onchange = "redonde('M');";
		
		$edit->referen = new inputField("S.N.M.", "referen");
		$edit->referen->size=17;
		$edit->referen->maxlength=15;
		
		$edit->barras = new inputField("Barras", "barras");
		$edit->barras->size=17;
		$edit->barras->maxlength=15;
		
		$edit->fracxuni = new inputField("Fracciones", "fracxuni");
		$edit->fracxuni->size=13;
		$edit->fracxuni->maxlength=11;
		
		$edit->dempaq = new dropdownField("Unidad", "dempaq");
		$edit->dempaq->style='width:110x;';
		$edit->dempaq->options("SELECT presenta label, presenta FROM mpre ORDER BY presenta");
		
		$edit->mempaq = new dropdownField("Unidad", "mempaq");
		$edit->mempaq->style='width:110x;';
		$edit->mempaq->options("SELECT presenta label, presenta FROM mpre ORDER BY presenta");
		
		$edit->ensambla = new dropdownField("Ensamblado", "ensambla");
		$edit->ensambla->style='width:60px;';
		$edit->ensambla->option("N","No" );
		$edit->ensambla->option("S","Si" );
		
		$edit->empaque = new inputField("Des/Epq", "empaque");
		$edit->empaque->size=30;
		$edit->empaque->maxlength=27;
		
		$edit->cu_inve = new inputField("Caja", "cu_inve");
		$edit->cu_inve->size=40;
		$edit->cu_inve->maxlength=15;
		
		for($i=1;$i<=5;$i++){
			$objeto="margen$i";
			$edit->$objeto = new inputField("Margen $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=17;     
			$edit->$objeto->maxlength=17;
			$edit->$objeto->onchange = "calculos('M');";
			
			$objeto="base$i";
			$edit->$objeto = new inputField("Base $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=17;
			$edit->$objeto->maxlength=17;
			$edit->$objeto->onchange = "cambiobase('M');";
			
			$objeto="precio$i";
			$edit->$objeto = new inputField("Precio $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=17;     
			$edit->$objeto->maxlength=17;
			$edit->$objeto->onchange = "cambioprecio('M');";
		}
		
		for($i=1;$i<=5;$i++){
			$objeto="cprv$i";
			$edit->$objeto = new inputField("Proveedor $i", $objeto);
			$edit->$objeto->size=7;
			$edit->$objeto->maxlength=5;
			$edit->$objeto->when =array("show");
			
			$objeto="nprv$i";
			$edit->$objeto = new inputField("Nombre Prv $i", $objeto);
			$edit->$objeto->size=35;
			$edit->$objeto->maxlength=30;
			$edit->$objeto->when =array("show");
			
			$objeto="fprv$i";
			$edit->$objeto = new dateonlyField("Fecha Prv $i", $objeto);
			$edit->$objeto->size=10;
			$edit->$objeto->when =array("show");
			       
			$objeto="pprv$i";
			$edit->$objeto = new inputField("Precio Prv $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=20;
			$edit->$objeto->maxlength=17;
			$edit->$objeto->when =array("show");
			
			$objeto="uprv$i";
			$edit->$objeto = new inputField("Bulto Prv $i", $objeto);
			$edit->$objeto->css_class='inputnum';
			$edit->$objeto->size=20;
			$edit->$objeto->maxlength=17;
			$edit->$objeto->when =array("show");
		}
		
		$codigo=$edit->_dataobject->get("codigo");
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array("show","modify");
		
		if($this->rapyd->uri->is_set("modify") or $this->rapyd->uri->is_set("show")){
			$codigo =$edit->_dataobject->get("codigo");
			$depto  =$edit->_dataobject->get("depto");
			$familia=$edit->_dataobject->get("familia");
			
			$edit->familia->options("SELECT familia,descrip FROM fami WHERE depto = '$depto' ORDER BY descrip");
			//$edit->grupo->options("SELECT grupo, nom_grup FROM grup WHERE depto='$depto' AND familia='$familia'");
		}else{
			$edit->familia->option("","Seleccione un departamento");
			$edit->grupo->option("","Seleccione una familia");
		}
		//$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();
				
		//echo $edit->codigo->value;
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_maes', $conten,true);
		$data["head"]    = script("tabber.js").script("prototype.js").script("sinvmaes.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Maestro de Inventario</h1>';
		$this->load->view('view_ventanas', $data);
	}  
  function _detalle($codigo){
  	$salida='hola';
  	if(!empty($codigo)){
  		$this->rapyd->load('dataedit','datagrid'); 
			
			$grid = new DataGrid('Cantidad por almac&eacute;n');
			
			$grid->db->select=array("ubica","locali","cantidad","fraccion");
			$grid->db->from('ubic');
			$grid->db->where('codigo',$codigo);
			
			$grid->column("Almacen"          ,"ubica" );
			$grid->column("Ubicaci&oacute;n" ,"locali");
			$grid->column("Cantidad"         ,"cantidad",'align="RIGHT"');
			$grid->column("Fracci&oacute;n"  ,"fraccion",'align="RIGHT"');
			
			$grid->build();
			$salida=$grid->output;
		}
		return $salida;
  }
     
	function maesfamilias(){  
		$this->rapyd->load("fields");
		$where = "";
		$sql = "SELECT familia,descrip FROM fami ";
		$linea = new dropdownField("Familia", "familia");
		$dpto=$this->input->post('dpto');
		
		if ($dpto){
		  $where = "WHERE depto = ".$this->db->escape($dpto);
		  $sql = "SELECT familia,descrip FROM fami $where ORDER BY descrip";
		  $linea->option("","");
			$linea->options($sql);
		}else{
			 $linea->option("","Seleccione Un Departamento");
		} 
		$linea->status   = "modify";
		$linea->onchange = "get_grupo();";
		$linea->build();
		echo $linea->output;
	}
	
	function maesgrupos(){
		$this->rapyd->load("fields");  
		$where = "";  
		$fami=$this->input->post('fami');
		$dpto=$this->input->post('dpto'); 
		
		$grupo = new dropdownField("Grupo", "grupo");
		if ($fami AND $dpto AND !(empty($fami) OR empty($dpto))) {
			$where .= "WHERE depto = ".$this->db->escape($dpto);
			$where .= "AND familia = ".$this->db->escape($fami);
			$sql = "SELECT grupo, nom_grup FROM grup $where";
			$grupo->option("","");
			$grupo->options($sql);
		}else{
			$grupo->option("","Seleccione una familia"); 
		} 
		$grupo->status = "modify";  
		$grupo->build();
		echo $grupo->output; 
	}
}
?>